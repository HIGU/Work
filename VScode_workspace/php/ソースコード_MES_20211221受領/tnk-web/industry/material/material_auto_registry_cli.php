#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の自動登録(標準品)半年以内の最新手動登録品をチェックしコピーする //
// Copyright (C) 2005-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/05/31 Created  material_auto_registry_cli.php                       //
// 2005/06/01 last_user にコピー元の計画番号を登録する。自動登録品は00:00:00//
// 2005/06/02 未登録のチェックをheader→historyへ変更(明細だけ登録に対応)   //
//            登録日を 当日→売上日→コピー元の登録日 に変更(分かりやすく)  //
// 2005/06/08 コピー元の日付条件を登録日から売上計上日へ変更                //
// 2005/06/09 処理速度を速めるため計上日から半年前の日付を取得を事前に行う  //
// 2005/12/20 ループ内での登録済みはng->okへ変更しメールのログを$fpc→$fpaへ//
// 2006/09/13 一時的に半年前からを４ヶ月前に変更 '6 month' → '4 month'     //
// 2006/09/21 半年→４ヶ月へコード部の置換え実行                            //
// 2006/11/30 ４ヶ月前を３ヶ月前に変更 '4 month' → '3 month' メッセージ変更//
// 2007/08/07 ３ヶ月前を１ヶ月前に変更 '3 month' → '1 month' メッセージ変更//
// 2009/07/10 メール送信先にnorihisa_ooya@nitto-kohki.co.jpを追加      大谷 //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2015/02/16 １ヶ月前を２ヶ月前に変更 '1 month' → '2 month' メッセージ変更//
// 2019/04/24 ２ヶ月前を３ヶ月前に変更 '2 month' → '3 month' メッセージ変更//
// 2020/01/06 ３ヶ月前を２ヶ月前に変更 '3 month' → '2 month' メッセージ変更//
// 2020/03/03 送信先にryota_waki@nitto-kohki.co.jpを追加               大谷 //
// 2020/03/17 ２ヶ月前を１ヶ月前に変更 '2 month' → '1 month' メッセージ変更//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分(CLI版以外)
require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // TNK に依存する部分の関数を require_once している

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
// $regdate  = date('Y-m-d') . ' 00:00:00';    // 自動登録日
$log_name_a = '/tmp/material_auto_registry.log';
$log_name_b = '/tmp/material_auto_unregist.log';
$log_name_c = '/tmp/material_auto_registok.log';
$fpa = fopen($log_name_a, 'a+');    // 全てのログ
$fpb = fopen($log_name_b, 'a+');    // 登録出来なかったログ
$fpc = fopen($log_name_c, 'a+');    // 登録出来たログ
$fpd = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpd, "総材料費の自動登録\n");
fwrite($fpd, "/home/www/html/tnk-web/industry/material/material_auto_registry_cli.php\n");

/////////// データベースとコネクション確立
if ( !($con = funcConnect()) ) {
    fwrite($fpa, "$log_date funcConnect() error \n");
    fwrite($fpd, "$log_date funcConnect() error \n");
    fclose($fpa);      ////// 日報用ログ書込み終了
    fwrite($fpd, "------------------------------------------------------------------------\n");
    fclose($fpd);      ////// 日報データ再取得用ログ書込み終了
    exit;
}

///// ここで未登録のリストをDBより取得して配列に格納する
///// 配列のフィールドは assy_no, plan_no, 売上日
$str_date = date_offset(1);
$end_date = date_offset(1);
// $str_date = '20001001';  // 初回時のデータ
// $end_date = '20050602';
$query = "
    SELECT  uri.assyno                      as 製品番号         -- 0
        ,   trim(substr(item.midsc, 1, 16)) as 製品名           -- 1
        ,   uri.計画番号                    as 計画番号         -- 2
        ,   uri.計上日                      as 売上日           -- 3
        ,   uri.数量                        as 売上数           -- 4
    FROM
        hiuuri as uri
    LEFT OUTER JOIN
        miitem as item
    ON (uri.assyno = item.mipn)
    LEFT OUTER JOIN
        material_cost_history as mate    -- 半期で絞り込まない(計画番号が絶対の条件) header→historyへ
    ON (uri.計画番号 = mate.plan_no)
    LEFT OUTER JOIN
          assembly_schedule as sch
    ON (uri.計画番号=sch.plan_no)
    WHERE
        uri.計上日>={$str_date}
        and uri.計上日<={$end_date}
        and uri.datatype='1'
        and mate.plan_no IS NULL
        and sch.note15 not like 'SC%'   -- C特注で無いもの
    ORDER BY uri.assyno ASC
    -- OFFSET 0 LIMIT 2000
";
fwrite($fpa, "$log_date 売上日 $str_date 〜 $end_date \n");
fwrite($fpb, "$log_date 売上日 $str_date 〜 $end_date \n");
fwrite($fpc, "$log_date 売上日 $str_date 〜 $end_date \n");
$res = array();
if ( ($rows=getResult($query, $res)) <= 0) {
    $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
    fwrite($fpa, "$log_date 総材料費 未登録の売上(完成)データがありません。\n");
    fwrite($fpb, "$log_date 総材料費 未登録の売上(完成)データがありません。\n");
    fwrite($fpc, "$log_date 総材料費 未登録の売上(完成)データがありません。\n");
    fclose($fpa);      ////// 日報用ログ書込み終了
    fclose($fpb);      ////// 日報用ログ書込み終了
    fclose($fpc);      ////// 日報用ログ書込み終了
    exit;
}

///// 配列データから INSERT INTO table SELECT 条件 を実行
$rec_ok = 0;
$rec_ng = 0;
$dupli  = 0;
for ($i=0; $i<$rows; $i++) {
    /////////// begin トランザクション開始
    query_affected_trans($con, 'begin');
    /////////// 未登録品の計上日から１ヶ月前の日付を取得(処理速度を速めるため事前に行う)
    //$query = "SELECT to_char(date '{$res[$i]['売上日']}' - interval '3 month', 'YYYYMMDD')";
    //$query = "SELECT to_char(date '{$res[$i]['売上日']}' - interval '2 month', 'YYYYMMDD')";
    $query = "SELECT to_char(date '{$res[$i]['売上日']}' - interval '1 month', 'YYYYMMDD')";
    if (getUniResTrs($con, $query, $pre_date) <= 0) {    // トランザクション内での Unique照会専用クエリー
        $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
        fwrite($fpa, "{$log_date} {$res[$i]['計画番号']} {$res[$i]['製品番号']} {$res[$i]['売上日']} １ヶ月前の日付の取得に失敗 {$res[$i]['製品名']}\n");
        query_affected_trans($con, 'rollback');         // トランザクションのロールバック
        exit;
    }
    /////////// ３ヶ月以内の最新経歴の取得 (2005/06/08 regdateでなく売上日に変更)
    $query = "SELECT plan_no, to_char(regdate, 'YYYYMMDD')
                FROM
                    material_cost_header
                LEFT OUTER JOIN
                    hiuuri
                ON (計画番号 = plan_no)
                WHERE
                    assy_no='{$res[$i]['製品番号']}'
                and
                    計上日 >= {$pre_date}           -- １ヶ月前から
                and
                    計上日 <= {$res[$i]['売上日']}  -- 売上日(完成日)まで
                and
                    CAST(regdate AS time(0)) != (time '00:00:00')   -- 自動登録を除外
                ORDER BY assy_no DESC, 計上日 DESC LIMIT 1
    ";
    $pre = array();
    if (getResultTrs($con, $query, $pre) <= 0) {    // トランザクション内での 照会専用クエリー
        $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
        fwrite($fpa, "{$log_date} {$res[$i]['計画番号']} {$res[$i]['製品番号']} １ヶ月以内に経歴が無いので登録出来ません！ {$res[$i]['製品名']}\n");
        fwrite($fpb, "{$log_date} {$res[$i]['計画番号']} {$res[$i]['製品番号']} １ヶ月以内に経歴が無いので登録出来ません！ {$res[$i]['製品名']}\n");
        $rec_ng++;
    } else {
        ////////// 登録済みのチェック(ループ内で既に登録した場合)
        $query = "SELECT plan_no FROM material_cost_header WHERE plan_no='{$res[$i]['計画番号']}'";
        if (getUniResTrs($con, $query, $tmp_plan_no) >= 1) {    // トランザクション内での 照会専用クエリー
            $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
            fwrite($fpa, "{$log_date} {$res[$i]['計画番号']} {$res[$i]['製品番号']} {$res[$i]['製品名']} 既に登録済み\n");
            /////////// トランザクションのロールバック
            query_affected_trans($con, 'rollback');
            $rec_ok++;
            $dupli++;
            continue;
        }
        $pre_plan_no = $pre[0][0];                      // コピー元の計画番号
        /////////// 自動登録日の設定
        $regdate     = "{$pre[0][1]} 00:00:00";         // コピー元の登録日を自動登録日にする
        // $regdate  = "{$res[$i]['売上日']} 00:00:00";    // 自動登録日
        /////////// 明細テーブルから更新
        $query = "INSERT INTO material_cost_history (
                plan_no, assy_no, parts_no, pro_no, pro_mark,
                par_parts, pro_price, pro_num, intext,
                regdate, last_date, last_user
            )
            SELECT
                  '{$res[$i]['計画番号']}', '{$res[$i]['製品番号']}', parts_no, pro_no, pro_mark,
                  par_parts, pro_price, pro_num, intext, '{$regdate}', CURRENT_TIMESTAMP, '{$pre_plan_no}'
            FROM material_cost_history
            WHERE
                plan_no='{$pre_plan_no}'
            and
                assy_no='{$res[$i]['製品番号']}'
            ORDER BY regdate ASC
        ";
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
            fwrite($fpa, "{$log_date} 明細の INSERT INTO table SELECT に失敗！\n{$query}\n");
            fwrite($fpb, "{$log_date} {$res[$i]['計画番号']} {$res[$i]['製品番号']} 明細の INSERT INTO table SELECT に失敗！ {$res[$i]['製品名']}\n");
        } else {
            $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
            fwrite($fpa, "{$log_date} {$res[$i]['計画番号']}←{$pre_plan_no} {$res[$i]['製品番号']} 明細data登録完了 {$res[$i]['製品名']}\n");
            /////////// 明細が更新できたらヘッダーテーブルの更新
            $query = "INSERT INTO material_cost_header (
                    plan_no, assy_no, sum_price, ext_price, int_price,
                    m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate,
                    regdate, last_date, last_user
                )
                SELECT
                      '{$res[$i]['計画番号']}', '{$res[$i]['製品番号']}', sum_price, ext_price, int_price,
                      m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate,
                      '{$regdate}', CURRENT_TIMESTAMP, '{$pre_plan_no}'
                FROM material_cost_header
                WHERE
                    plan_no='{$pre_plan_no}'
                and
                    assy_no='{$res[$i]['製品番号']}' -- これは必要ないが一応
                ORDER BY plan_no ASC
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
                fwrite($fpa, "{$log_date} ヘッダーの INSERT INTO table SELECT に失敗！\n{$query}\n");
                fwrite($fpb, "{$log_date} {$res[$i]['計画番号']} {$res[$i]['製品番号']} ヘッダーの INSERT INTO table SELECT に失敗！ {$res[$i]['製品名']}\n");
                /////////// トランザクションのロールバック
                query_affected_trans($con, 'rollback');
                $rec_ng++;
                continue;
            } else {
                $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
                fwrite($fpa, "{$log_date} {$res[$i]['計画番号']}←{$pre_plan_no} {$res[$i]['製品番号']} ヘッダー登録完了 {$res[$i]['製品名']}\n");
                fwrite($fpc, "{$log_date} {$res[$i]['計画番号']}←{$pre_plan_no} {$res[$i]['製品番号']} 明細・ヘッダー登録完了 {$res[$i]['製品名']}\n");
            }
        }
        $rec_ok++;
    }
    /////////// commit トランザクションのコミット
    query_affected_trans($con, 'commit');
}

$log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
fwrite($fpa, "{$log_date} {$rec_ok}/{$rows} 自動登録しました。 {$rec_ng}/{$rows} 登録出来ませんでした。(重複計画：{$dupli}件)\n");
fwrite($fpb, "{$log_date} {$rec_ok}/{$rows} 自動登録しました。 {$rec_ng}/{$rows} 登録出来ませんでした。(重複計画：{$dupli}件)\n");
fwrite($fpc, "{$log_date} {$rec_ok}/{$rows} 自動登録しました。 {$rec_ng}/{$rows} 登録出来ませんでした。(重複計画：{$dupli}件)\n");

if (rewind($fpa)) {
    $to = 'tnksys@nitto-kohki.co.jp, usoumu@nitto-kohki.co.jp, norihisa_ooya@nitto-kohki.co.jp, ryota_waki@nitto-kohki.co.jp';
    $subject = "総材料費の自動登録結果 {$log_date}";
    $msg = fread($fpa, filesize($log_name_a));
    $header = "From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp\r\n";
    mb_send_mail($to, $subject, $msg, $header);
}

fclose($fpa);      ////// 日報用ログ書込み終了
fclose($fpb);      ////// 日報用ログ書込み終了
fclose($fpc);      ////// 日報用ログ書込み終了
fwrite($fpd, "------------------------------------------------------------------------\n");
fclose($fpd);      ////// 日報データ再取得用ログ書込み終了
exit();
?>
