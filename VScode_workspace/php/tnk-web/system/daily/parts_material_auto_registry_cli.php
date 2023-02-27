#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 部品売上の材料費の自動登録処理 (単価登録番号・外作費・内作費・合計単価)  //
// Copyright (C) 2006-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/10 Created  parts_material_auto_registry_cli.php                 //
// 2006/02/12 登録番号がなければデータ無しと判断を追加                      //
// 2006/12/18 前日分のみ登録だったのを14日前から前日までに変更(長期休暇対応)//
// 2007/08/07 売上計上日より前の登録を使用→以前へ変更。それでもない場合は  //
//            後の登録を使用するへ変更。それでもない場合に無しのメッセージ  //
// 2009/07/10 メール送信先にnorihisa_ooya@nitto-kohki.co.jp追加        大谷 //
// 2009/09/24 自動登録対象より試修・商管売上を外すように変更           大谷 //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2020/03/03 メール送信先にryota_waki@nitto-kohki.co.jpを追加         大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分(CLI版以外)
require_once ('/var/www/html/function.php');
require_once ('/var/www/html/tnk_func.php');   // TNK に依存する部分の関数を require_once している

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$log_name_a = '/tmp/parts_material_auto_registry.log';
$fpa = fopen($log_name_a, 'w+');    // 全てのログ w=過去のログを消す
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "部品売上の材料費の自動登録\n");
fwrite($fpb, "/var/www/html/system/daily/parts_material_auto_registry_cli.php\n");

/////////// データベースとコネクション確立
if ( !($con = funcConnect()) ) {
    fwrite($fpa, "$log_date funcConnect() error \n");
    fclose($fpa);      ////// 日報用ログ書込み終了
    exit;
}

///// ここで前日分(又は指定範囲)のリストをDBより取得して配列に格納する
///// 配列のフィールドは assy_no, 売上日
$str_date = date_offset(14);
$end_date = date_offset(1);
// $str_date = '20001001';  // 初回時のデータ
// $end_date = '20060210';
$query = "
    SELECT  uri.assyno                      as 部品番号         -- 0
        ,   trim(substr(item.midsc, 1, 16)) as 部品名           -- 1
        ,   uri.計上日                      as 計上日           -- 3
    FROM
        hiuuri AS uri
    LEFT OUTER JOIN
        miitem AS item
    ON (uri.assyno = item.mipn)
    WHERE
        uri.計上日>={$str_date}
        and uri.計上日<={$end_date}
        and uri.datatype >= '5'
        and trim(uri.assyno) != ''
        and uri.assyno IS NOT NULL
        and uri.assyno not like 'SS%'   -- 試験・修理で無いもの
        and uri.assyno not like 'NKB%'  -- 商管で無いもの
    ORDER BY uri.計上日 ASC, uri.assyno ASC
";
fwrite($fpa, "$log_date 売上日 $str_date ～ $end_date \n");
$res = array();
if ( ($rows=getResult($query, $res)) <= 0) {
    $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
    fwrite($fpa, "$log_date 部品売上の材料費 未登録の売上データがありません。\n");
    fclose($fpa);      ////// 日報用ログ書込み終了
    exit;
}

///// 配列データから INSERT INTO table SELECT 条件 を実行
$rec_ok = 0;
$rec_ng = 0;
$dupli  = 0;
for ($i=0; $i<$rows; $i++) {
    /////////// begin トランザクション開始
    query_affected_trans($con, 'begin');
    ////////// 登録済みのチェック(ループ内で既に登録した場合)
    $query = "
        SELECT cost_reg FROM sales_parts_material_history
        WHERE parts_no='{$res[$i]['部品番号']}' AND sales_date={$res[$i]['計上日']}
    ";
    if (getUniResTrs($con, $query, $tmp_plan_no) >= 1) {    // トランザクション内での 照会専用クエリー
        // $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
        // fwrite($fpa, "{$log_date} {$res[$i]['部品番号']} 計上日 {$res[$i]['計上日']} で既に登録済み {$res[$i]['部品名']}\n");
        /////////// トランザクションのロールバック
        query_affected_trans($con, 'ROLLBACK');
        $rec_ok++;
        $dupli++;
        continue;
    }
    /////////// 部品単価経歴から単価登録番号・外作費・内作費・合計単価を取得
    $query = "
        SELECT
            (   SELECT reg_no FROM parts_cost_history
                WHERE parts_no='{$res[$i]['部品番号']}' AND as_regdate <= {$res[$i]['計上日']}
                AND lot_no=1 AND vendor!='88888'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC LIMIT 1
            ) AS cost_reg   -- 0
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['部品番号']}' AND as_regdate <= {$res[$i]['計上日']}
                AND lot_no=1 AND vendor!='88888' AND vendor!='01111' AND vendor!='00222'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC LIMIT 1
            ) AS ext_cost   -- 1
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['部品番号']}' AND as_regdate <= {$res[$i]['計上日']}
                AND lot_no=1 AND vendor!='88888' AND (vendor='01111' OR vendor='00222')
                GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC LIMIT 1
            ) AS int_cost   -- 2
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['部品番号']}' AND as_regdate <= {$res[$i]['計上日']}
                AND lot_no=1 AND vendor!='88888'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC LIMIT 1
            ) AS unit_cost  -- 3
            ,   ----------------- 以下は売上計上日以前で見つからない場合に使用 -----------------
            (   SELECT reg_no FROM parts_cost_history
                WHERE parts_no='{$res[$i]['部品番号']}' AND as_regdate > {$res[$i]['計上日']}
                AND lot_no=1 AND vendor!='88888'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate ASC, reg_no DESC LIMIT 1
            ) AS cost_reg2  -- 4
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['部品番号']}' AND as_regdate > {$res[$i]['計上日']}
                AND lot_no=1 AND vendor!='88888' AND vendor!='01111' AND vendor!='00222'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate ASC, reg_no ASC LIMIT 1
            ) AS ext_cost2  -- 5
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['部品番号']}' AND as_regdate > {$res[$i]['計上日']}
                AND lot_no=1 AND vendor!='88888' AND (vendor='01111' OR vendor='00222')
                GROUP BY as_regdate, reg_no ORDER BY as_regdate ASC, reg_no ASC LIMIT 1
            ) AS int_cost2  -- 6
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['部品番号']}' AND as_regdate > {$res[$i]['計上日']}
                AND lot_no=1 AND vendor!='88888'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate ASC, reg_no ASC LIMIT 1
            ) AS unit_cost2 -- 7
    ";
    if (getResultTrs($con, $query, $resCost) <= 0) {    // トランザクション内での 照会専用クエリー
        $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
        fwrite($fpa, "{$log_date} {$res[$i]['部品番号']} は計上日 {$res[$i]['計上日']} 単価登録が無い {$res[$i]['部品名']}\n");
        /////////// トランザクションのロールバック
        query_affected_trans($con, 'ROLLBACK');
        $rec_ng++;
        continue;
    }
    /////////// データの有り無しチェック
    if ( (!$resCost[0][0]) && (!$resCost[0][4]) ) {  // 登録番号があるか？
        $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
        fwrite($fpa, "{$log_date} {$res[$i]['部品番号']} は計上日 {$res[$i]['計上日']} 単価登録が無い {$res[$i]['部品名']}\n");
        /////////// トランザクションのロールバック
        query_affected_trans($con, 'ROLLBACK');
        $rec_ng++;
        continue;
    }
    if (!$resCost[0][1]) $resCost[0][1] = '0';
    if (!$resCost[0][2]) $resCost[0][2] = '0';
    if (!$resCost[0][3]) $resCost[0][3] = '0';
    if (!$resCost[0][5]) $resCost[0][5] = '0';
    if (!$resCost[0][6]) $resCost[0][6] = '0';
    if (!$resCost[0][7]) $resCost[0][7] = '0';
    /////////// sales_parts_material_history に登録実行
    if ($resCost[0][0]) {   // 以前で登録番号があるか？
        $query = "
            INSERT INTO sales_parts_material_history (parts_no, sales_date, cost_reg, ext_cost, int_cost, unit_cost)
            VALUES ('{$res[$i]['部品番号']}', {$res[$i]['計上日']}, {$resCost[0][0]}, {$resCost[0][1]}, {$resCost[0][2]}, {$resCost[0][3]})
        ";
    } else {                // 後の登録番号のものを使用する
        $query = "
            INSERT INTO sales_parts_material_history (parts_no, sales_date, cost_reg, ext_cost, int_cost, unit_cost)
            VALUES ('{$res[$i]['部品番号']}', {$res[$i]['計上日']}, {$resCost[0][4]}, {$resCost[0][5]}, {$resCost[0][6]}, {$resCost[0][7]})
        ";
    }
    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
        $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
        fwrite($fpa, "{$log_date} {$res[$i]['部品番号']} 計上日 {$res[$i]['計上日']} 登録に失敗！ {$res[$i]['部品名']}\n");
        query_affected_trans($con, 'ROLLBACK');
        $rec_ng++;
        continue;
    }
    $rec_ok++;
    /////////// COMMIT トランザクションのコミット
    query_affected_trans($con, 'COMMIT');
}

$log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
fwrite($fpa, "{$log_date} {$rec_ok}/{$rows} 自動登録しました。 {$rec_ng}/{$rows} 登録出来ませんでした。(重複件数：{$dupli}件)\n");

if (rewind($fpa)) {
    $to = 'tnksys@nitto-kohki.co.jp, usoumu@nitto-kohki.co.jp, norihisa_ooya@nitto-kohki.co.jp, ryota_waki@nitto-kohki.co.jp';
    $subject = "部品売上の材料費の自動登録結果 {$log_date}";
    $msg = fread($fpa, filesize($log_name_a));
    $header = "From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp\r\n";
    mb_send_mail($to, $subject, $msg, $header);
}

fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
exit();
?>
