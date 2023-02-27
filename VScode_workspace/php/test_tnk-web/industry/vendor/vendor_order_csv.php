<?php
//////////////////////////////////////////////////////////////////////////////
// 協力工場別注残リスト CSV出力                                             //
// Copyright (C) 2011-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/05/20 Created   vendor_order_csv.php                                //
// 2103/10/12 協力工場名取得の際に余計なスペースを削除                      //
// 2015/10/19 製品グループにT=ツールを追加（部品No.１文字目がT）            //
//            生管小松依頼により、LからはTを除外しない(T部品もL事業部)      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// リターンアドレス設定
$menu->set_RetUrl('/industry/vendor/vendor_order_list_form.php');             // 通常は指定する必要はない

// ファイル名とSQLのサーチ部を受け取る
//$outputFile = $_GET['csvname'];
//$csv_search = $_GET['csvsearch'];
// SQLのサーチ部で一時変更した部分を元に戻す
//$search     = str_replace('keidate','計上日',$csv_search);
//$search     = str_replace('jigyou','事業部',$search);
//$search     = str_replace('denban','伝票番号',$search);
//$search     = str_replace('/','\'',$search);
// サーチ部の文字コードをEUCに変更する（念のため）
//$search     = mb_convert_encoding($search, 'UTF-8', 'auto');   // CSV用にEUCからSJISへ文字コード変換

// ファイル名で一時変更した部分を元に戻す
//$outputFile     = str_replace('ALL','全体',$outputFile);
//$outputFile     = str_replace('C-all','カプラ全体',$outputFile);
//$outputFile     = str_replace('C-hyou','カプラ標準',$outputFile);
//$outputFile     = str_replace('L-all','リニア全体',$outputFile);
//$outputFile     = str_replace('L-hyou','リニアのみ',$outputFile);
//$outputFile     = str_replace('L-bimor','バイモル',$outputFile);
//$outputFile     = str_replace('C-shuri','カプラ試修',$outputFile);
//$outputFile     = str_replace('L-shuri','リニア試修',$outputFile);
//$outputFile     = str_replace('NKB','商品管理',$outputFile);
//$outputFile     = str_replace('TOOL','ツール',$outputFile);
//$outputFile     = str_replace('NONE','なし',$outputFile);
//$outputFile     = str_replace('SHISAKU','試作',$outputFile);
//$outputFile     = str_replace('NONE','なし',$outputFile);


if (isset($_REQUEST['vendor'])) {
    $vendor = $_REQUEST['vendor'];
} else {
    $vendor = '00485';                           // Default(全て)ありえないが
    // $view = 'NG';
}
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];
} else {
    $div = 'C';                              // Default(全て)
}
if (isset($_REQUEST['plan_cond'])) {
    $plan_cond = $_REQUEST['plan_cond'];
} else {
    $plan_cond = '';                        // Default(全て)
}
//////// 協力工場名の取得
$query = "select trim(name) from vendor_master where vendor='{$vendor}'";
if (getUniResult($query, $vendor_name) < 1) {
    $_SESSION['s_sysmsg'] = "発注先コードが無効です！";
    $vendor_name = '未登録';
    $view = 'NG';
}

//////// 表題の設定
//if ($div == '') $div_name = '全て'; else $div_name = $div;
//if ($plan_cond == '') $cond_name = '全て'; else $cond_name = $plan_cond;
//$menu->set_caption("コード：{$vendor}　ベンダー名：{$vendor_name}　製品グループ：{$div_name}　発注区分：{$cond_name}");

////////// 日付で共通の where句を生成
// 過去は200日前から153(５ヶ月)→184日(６ヶ月)先まで→200日へ変更
//$where_date = 'proc.delivery <= ' . date('Ymd', mktime() + (86400*200)) . ' and proc.delivery >= ' . date('Ymd', mktime() - (86400*200));
$where_date = 'proc.delivery <= ' . date('Ymd', time() + (86400*200)) . ' and proc.delivery >= ' . date('Ymd', time() - (86400*200));

//////// 事業部から共通な where句を設定
switch ($div) {
case 'C':       // C全体
    $div_name  = "カプラ";
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and proc.locate != '52   '";
    break;
case 'SC':      // C特注
    $div_name  = "C特注";
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and plan.kouji_no like '%SC%' and proc.locate != '52   '";
    break;
case 'CS':      // C標準
    $div_name  = "C標準";
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and plan.kouji_no not like '%SC%' and proc.locate != '52   '";
    break;
case 'L':       // L全体
    $div_name  = "リニア";
    $where_div = "proc.vendor='{$vendor}' and plan.div='L' and proc.locate != '52   '";
    break;
case 'T':       // T全体
    $div_name  = "T全体";
    $where_div = "proc.vendor='{$vendor}' and proc.parts_no like 'T%' and proc.locate != '52   '";
    break;
case 'F':       // F全体
    $div_name  = "F全体";
    $where_div = "proc.vendor='{$vendor}' and plan.div='F' and proc.locate != '52   '";
    break;
case 'A':       // TNK全体
    $div_name  = "TNK全体";
    $where_div = "(proc.vendor='{$vendor}' and plan.div='C' or plan.div='L' or plan.div='T' or plan.div='F') and proc.locate != '52   '";
    break;
case 'N':       // NKカプラ
    $div_name  = "NKカプラ";
    $where_div = "(proc.vendor='{$vendor}' and plan.div='C' or plan.div='L' or plan.div='T' or plan.div='F') and proc.locate = '52   '";
    break;
default:        // 全製品グループ '' ' ' の違いがあったため default へ変更
    $div_name  = "全て";
    $where_div = "proc.vendor='{$vendor}' and proc.locate != '52   '";
    break;
}
//////// 発注計画区分から共通な where句を設定
switch ($plan_cond) {
case 'P':       // 予定
case 'R':       // 内示中(リリース)
case 'O':       // 注文書発行済み
    $where_cond = "proc.plan_cond='{$plan_cond}'";
    break;
default:
    $where_cond = "proc.plan_cond != '{$plan_cond}'";
    break;
}

switch ($plan_cond) {
case 'P':       // 予定
    $cond_name  = "予定";
    break;
case 'R':       // 内示中(リリース)
    $cond_name  = "内示中";
    break;
case 'O':       // 注文書発行済み
    $cond_name  = "注文書発行済";
    break;
default:
    $cond_name  = "全て";
    break;
}
// ファイル名とSQLのサーチ部を受け取る
$vendor_name = trim($vendor_name);
$vendor_name = rtrim($vendor_name, "　");
$outputFile = "注残リスト-" . $vendor_name . "-" . $div_name . "-" . $cond_name . ".csv";

// 実行者のパソコンにCSVを保存する為、ファイル名の文字コードをSJISに変換
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換

////////// 共通SQL文を生成
$query_csv = "select    
                    substr(to_char(proc.delivery, 'FM9999/99/99'), 6, 5)
                                                            AS 納期
                  , to_char(proc.sei_no,'FM0000000')        AS 製造番号
                  , proc.parts_no                           AS 部品番号
                  , trim(item.midsc)                         AS 部品名
                  , CASE
                          WHEN trim(item.mzist) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                          ELSE item.mzist
                    END                                     AS 材質
                  , CASE
                          WHEN trim(item.mepnt) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                          ELSE item.mepnt
                    END                                     AS 親機種
                  , CASE
                        WHEN proc.order_q = 0 THEN trim(to_char((plan.order_q - plan.utikiri - plan.nyuko), '9,999,999'))
                        ELSE trim(to_char((proc.order_q - proc.siharai - proc.cut_siharai), '9,999,999'))
                    END                                     AS 注残数
                  , (select CASE
                                WHEN (sum(uke_q)-sum(siharai)) IS NULL THEN '0'
                                ELSE trim(to_char(sum(uke_q)-sum(siharai), '9,999,999'))    --検中
                            END
                        from
                            order_data
                        where sei_no=proc.sei_no and order_no=proc.order_no and vendor=proc.vendor and ken_date<=0
                    )                                       AS 検査中
                  , proc.pro_mark                           AS 工程
                  , CASE
                        WHEN proc.plan_cond = 'P' THEN '予　定'
                        WHEN proc.plan_cond = 'O' THEN '注文書'
                        WHEN proc.plan_cond = 'R' THEN '内示中'
                        ELSE proc.plan_cond
                    END                                     AS 発注計画区分
                  , CASE
                        WHEN proc.next_pro != 'END..' THEN
                            (select name from vendor_master where vendor=proc.next_pro limit 1)
                        ELSE proc.next_pro
                    END                                     AS 次工程名
            from
                order_process   AS proc
            left outer join
                order_plan      AS plan
                                        using(sei_no)
            left outer join
                vendor_master   AS mast
                                        on(proc.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(proc.parts_no = item.mipn)
            where
                {$where_date}
                and
                {$where_div}
                and
                (plan.order_q - plan.utikiri - plan.nyuko) > 0
                    -- ヘッダーに注残がある物で
                and
                ( (proc.order_q = 0) OR ((proc.order_q - proc.siharai - proc.cut_siharai > 0)) )
                    -- 次工程か？ 又は自分の工程に注残がある物
                and
                {$where_cond}
            order by 発注計画区分 ASC, proc.delivery ASC, proc.parts_no ASC
            offset 0
            limit 1000
";

$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    $_SESSION['s_sysmsg'] .= '注残データがありません！';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // 直前の呼出元へ戻る
    exit();
} else {
    $num_csv = count($field_csv);       // フィールド数取得
    for ($r=0; $r<$rows_csv; $r++) {
        //$res_csv[$r][4] = mb_convert_kana($res_csv[$r][4], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
        $res_csv[$r][3]  = str_replace(',',' ',$res_csv[$r][3]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][4]  = str_replace(',',' ',$res_csv[$r][4]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][5]  = str_replace(',',' ',$res_csv[$r][5]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][8]  = str_replace(',',' ',$res_csv[$r][8]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][9]  = str_replace(',',' ',$res_csv[$r][9]);                   // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        $res_csv[$r][10] = str_replace(',',' ',$res_csv[$r][10]);                  // 製品名に,が入っているとCSVで桁がずれるので半角スペースに
        //$res_csv[$r][4] = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'EUC');   // CSV用にEUCからSJISへ文字コード変換(EUC指定じゃないと直納・調整が化ける)
        $res_csv[$r][3]  = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][4]  = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][5]  = mb_convert_encoding($res_csv[$r][5], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][8]  = mb_convert_encoding($res_csv[$r][8], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][9]  = mb_convert_encoding($res_csv[$r][9], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
        $res_csv[$r][10] = mb_convert_encoding($res_csv[$r][10], 'SJIS', 'auto');  // CSV用にEUCからSJISへ文字コード変換
    }
    //$_SESSION['SALES_TEST'] = sprintf("order by 計上日 offset %d limit %d", $offset, PAGE);
    $i = 1;                             // CSV書き出し用カウント（フィールド名が0に入るので１から）
    $csv_data = array();                // CSV書き出し用配列
    for ($s=0; $s<$num_csv; $s++) {     // フィールド名をCSV書き出し用配列に出力
        $field_csv[$s]   = mb_convert_encoding($field_csv[$s], 'SJIS', 'auto');
        $csv_data[0][$s] = $field_csv[$s];
    }
    for ($r=0; $r<$rows_csv; $r++) {    // データをCSV書き出し用配列に出力
        for ($s=0; $s<$num_csv; $s++) {
            $csv_data[$i][$s]  = $res_csv[$r][$s];
        }
        $i++;
    }
}

// ここからがCSVファイルの作成（一時ファイルをサーバーに作成）
//$outputFile = 'csv/' . $d_start . '-' . $d_end . '.csv';
//$outputFile = 'csv/' . $d_start . '-' . $d_end . '-' . $act_name . '.csv';
//$outputFile = "test.csv";
touch($outputFile);
$fp = fopen($outputFile, "w");

foreach($csv_data as $line){
    fputcsv($fp,$line);         // ここでCSVファイルに書き出し
}
fclose($fp);
//$outputFile = $d_start . '-' . $d_end . '.csv';
//$outputFile = $d_start . '-' . $d_end . '-' . $act_name . '.csv';

// ここからがCSVファイルのダウンロード（サーバー→クライアント）
touch($outputFile);
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=".$outputFile);
header("Content-Length:".filesize($outputFile));
readfile($outputFile);
unlink("{$outputFile}");         // ダウンロード後ファイルを削除
?>