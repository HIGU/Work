<?php
//////////////////////////////////////////////////////////////////////////////
// 納期遅れ部品の照会 ＆ チェック用                                         //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/09 Created   delivery_late_view.php                              //
// 2011/11/10 データ受け渡しでエラーが発生したのを修正                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 52);                    // site_index=30(生産メニュー) site_id=52(納期遅れ部品の照会)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('納期遅れ部品 の 照 会');
//////////// 表題の設定     下のロジックで処理するためここでは使用しない
// $menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('単価経歴表示',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('在庫予定',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');

if (isset($_REQUEST['material'])) {
    $menu->set_retGET('material', $_REQUEST['material']);
    if (isset($_REQUEST['uke_no'])) {
        $uke_no = $_REQUEST['uke_no'];
        $_SESSION['uke_no'] = $uke_no;
    } else {
        $uke_no = @$_SESSION['uke_no'];     // @在庫経歴の表題等から指定された場合の対応(uke_noなし)
    }
    $current_script = $menu->out_self() . '?material=1';
    $_SESSION['paya_strdate'] = '20001001';     // 分社化時点
    $_SESSION['paya_enddate'] = '99999999';     // 最新まで
} elseif (isset($_REQUEST['uke_no'])) {     // 在庫経歴(単体から)呼出時の対応
    $uke_no = $_REQUEST['uke_no'];
    $current_script = $menu->out_self();
    $_SESSION['paya_strdate'] = '20001001';     // 分社化時点
    $_SESSION['paya_enddate'] = '99999999';     // 最新まで
} else {                                    // フォーム(単体から)呼出時の対応
    $uke_no = '';
    $current_script = $menu->out_self();
}

//////////// 日東工器支給品の対応
if (isset($_REQUEST['kei_ym'])) {
    $kei_ym = $_REQUEST['kei_ym'];
    $kei_ym = format_date8($kei_ym);
    $_SESSION['kei_ym'] = $kei_ym;
} else {
    $kei_ym = @$_SESSION['kei_ym'];     // @単価経歴の戻り時の対応(逆の場合は無視する)
}

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 条件選択フォームからのPOSTデータ取得
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];
    $_SESSION['paya_parts_no'] = $parts_no;
} else {
    $parts_no = '';
    $_SESSION['paya_parts_no'] = $parts_no;
    ///// 部品番号は必須
}
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];
    $_SESSION['payable_div'] = $div;
} else {
    if (isset($_SESSION['payable_div'])) {
        $div = $_SESSION['payable_div'];
    } else {
        $div = ' ';
        $_SESSION['payable_div'] = $div;
    }
}

if (isset($_REQUEST['vendor'])) {
    $vendor = $_REQUEST['vendor'];
    $_SESSION['paya_vendor'] = $vendor;
} else {
    if (isset($_SESSION['paya_vendor'])) {
        $vendor = $_SESSION['paya_vendor'];
    } else {
        $vendor = '';
        $_SESSION['paya_vendor'] = $vendor;
    }
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['paya_strdate'] = $str_date;
} elseif (isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    $str_date = '';     // 初期化
    $_SESSION['paya_strdate'] = $str_date;
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['paya_enddate'] = $end_date;
} elseif (isset($_SESSION['paya_enddate'])) {
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '';     // 初期化
    $_SESSION['paya_enddate'] = $end_date;
}
if (isset($_REQUEST['paya_page'])) {
    $paya_page = $_REQUEST['paya_page'];
    $_SESSION['payable_page'] = $paya_page;
} else {
    if (isset($_SESSION['payable_page'])) {
        $paya_page = $_SESSION['payable_page'];
    } else {
        $paya_page = 23;
    }
}

//////////// 一頁の行数
define('PAGE', $paya_page);

switch ($div) {
case ' ';    // 全体
    $caption_title = '部門：全体　';
    break;
case 'C';    // カプラ 全体
    $caption_title = '部門：カプラ全体　';
    break;
case 'SC';   // カプラ 特注
    $caption_title = '部門：カプラ特注　';
    break;
case 'CS';   // カプラ 標準
    $caption_title = '部門：カプラ標準　';
    break;
case 'L';    // リニア 全体
    $caption_title = '部門：リニア全体　';
    break;
case 'LN';   // リニア のみ
    $caption_title = '部門：リニアのみ　';
    break;
case 'B';    // バイモル
    $caption_title = '部門：バイモル　';
    break;
case 'T';    // ツール他
    $caption_title = '部門：ツール他　';
    break;
}

$caption_title .= '年月：' . format_date($str_date) . '～' . format_date($end_date);

$search = "where proc.delivery <= {$end_date} and proc.delivery >= {$str_date} and uke_date >= 0 and uke_date > proc.delivery and data.sei_no > 0 and (data.order_q - data.cut_genpin) > 0";
//////// 事業部から共通な where句を設定
if ($parts_no != '') {
    $search .= sprintf(" and data.parts_no='%s'", $parts_no);
    $query = "select trim(substr(midsc,1,30)) from miitem where mipn='{$parts_no}' limit 1";
    if (getUniResult($query, $name) <= 0) {
        $name = 'マスター未登録';
    }
    $caption_title = "部品番号：{$parts_no}　<font color='blue'>部品名：{$name}</font><BR>年月：" . format_date($str_date) . '～' . format_date($end_date);
} elseif ($div != ' ') {
    if ($vendor != '') {
        switch ($div) {
        case ' ';    // 全体
            $div_name = '部門：全体　';
        case 'C':       // C全体
            $div_name = '部門：カプラ全体　';
            $search .= " and data.parts_no like 'C%' and proc.locate != '52   '";
            break;
        case 'SC':      // C特注
            $div_name = '部門：カプラ特注　';
            $search .= " and data.parts_no like 'C%' and data.kouji_no like '%SC%' and proc.locate != '52   '";
            break;
        case 'CS':      // C標準
            $div_name = '部門：カプラ標準　';
            $search .= " and data.parts_no like 'C%' and data.kouji_no not like '%SC%' and proc.locate != '52   '";
            break;
        case 'L':       // L全体
            $div_name = '部門：リニア全体　';
            $search .= " and data.parts_no like 'L%' and proc.locate != '52   '";
            break;
        case 'LN';  // リニア のみ
            $div_name = '部門：リニアのみ　';
            $search .= " and (data.parts_no like 'L%' and data.parts_no NOT like 'LC%%' AND data.parts_no NOT like 'LR%%') and proc.locate != '52   '";
            break;
        case 'B';   // バイモル
            $div_name = '部門：バイモル　';
            $search .= " and (data.parts_no like 'LC%%' OR data.parts_no like 'LR%%') and proc.locate != '52   '";
            break;
        case 'T';   // ツール他
            $div_name = '部門：ツール他　';
            $search .= " and (data.parts_no NOT like 'C%%' AND data.parts_no NOT like 'L%%') and proc.locate != '52   '";
            break;
        }
        $search .= sprintf(" and data.vendor = '%s'", $vendor);
        $query = "select trim(substr(name, 1, 30)) from vendor_master where vendor='{$vendor}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = 'マスター未登録';
        }
        $caption_title = "<font color='blue'>協力工場：{$name}</font>　" . $div_name ."年月：" . format_date($str_date) . '～' . format_date($end_date) . "<BR>";
    }
    switch ($div) {
    case ' ';    // 全体
        $div_name = '部門：全体　';
    case 'C':       // C全体
        $div_name = '部門：カプラ全体　';
        $search .= " and data.parts_no like 'C%' and proc.locate != '52   '";
        break;
    case 'SC':      // C特注
        $div_name = '部門：カプラ特注　';
        $search .= " and data.parts_no like 'C%' and data.kouji_no like '%SC%' and proc.locate != '52   '";
        break;
    case 'CS':      // C標準
        $div_name = '部門：カプラ標準　';
        $search .= " and data.parts_no like 'C%' and data.kouji_no not like '%SC%' and proc.locate != '52   '";
        break;
    case 'L':       // L全体
        $div_name = '部門：リニア全体　';
        $search .= " and data.parts_no like 'L%' and proc.locate != '52   '";
        break;
    case 'LN';  // リニア のみ
        $div_name = '部門：リニアのみ　';
        $search .= " and (data.parts_no like 'L%' and data.parts_no NOT like 'LC%%' AND data.parts_no NOT like 'LR%%') and proc.locate != '52   '";
        break;
    case 'B';   // バイモル
        $div_name = '部門：バイモル　';
        $search .= " and (data.parts_no like 'LC%%' OR data.parts_no like 'LR%%') and proc.locate != '52   '";
        break;
    case 'T';   // ツール他
        $div_name = '部門：ツール他　';
        $search .= " and (data.parts_no NOT like 'C%%' AND data.parts_no NOT like 'L%%') and proc.locate != '52   '";
        break;
    }
} elseif ($vendor != '') {
    $search .= sprintf(" and data.vendor = '%s'", $vendor);
    $query = "select trim(substr(name, 1, 30)) from vendor_master where vendor='{$vendor}' limit 1";
    if (getUniResult($query, $name) <= 0) {
        $name = 'マスター未登録';
    }
    switch ($div) {
    case ' ';    // 全体
        $div_name = '部門：全体　';
        break;
    case 'C';    // カプラ 全体
        $div_name = '部門：カプラ全体　';
        break;
    case 'SC';    // カプラ 特注
        $div_name = '部門：カプラ特注　';
        break;
    case 'CS';    // カプラ 標準
        $div_name = '部門：カプラ標準　';
        break;
    case 'L';    // リニア 全体
        $div_name = '部門：リニア全体　';
        break;
    case 'LN';   // リニア のみ
        $caption_title = '部門：リニアのみ　';
        break;
    case 'B';    // バイモル
        $caption_title = '部門：バイモル　';
        break;
    case 'T';    // ツール他
        $caption_title = '部門：ツール他　';
        break;
    }
    $caption_title = "<font color='blue'>協力工場：{$name}</font>　" . $div_name ."年月：" . format_date($str_date) . '～' . format_date($end_date) . "<BR>";
}
//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf('select count(*), sum(Uround(data.order_q * data.order_price,0)) from
                order_data      AS data
            left outer join
                order_process   AS proc
                                        using(sei_no, order_no, vendor)
            LEFT OUTER JOIN
                order_plan      AS plan     USING (sei_no)
            left outer join
                vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(data.parts_no = item.mipn) %s', $search);
$res_max = array();
if ( getResult2($query, $res_max) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $maxrows = $res_max[0][0];                  // 合計レコード数の取得
    $caption_title .= '　合計金額：' . number_format($res_max[0][1]);   // 合計買掛金額をキャプションタイトルにセット
    $caption_title .= '　合計件数：' . number_format($res_max[0][0]);   // 合計買掛件数をキャプションタイトルにセット
}

//////////// ページオフセット設定
if ( isset($_REQUEST['forward']) ) {                       // 次頁が押された
    $_SESSION['paya_offset'] += PAGE;
    if ($_SESSION['paya_offset'] >= $maxrows) {
        $_SESSION['paya_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // 次頁が押された
    $_SESSION['paya_offset'] -= PAGE;
    if ($_SESSION['paya_offset'] < 0) {
        $_SESSION['paya_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {               // 現在のページを維持する
    $offset = $_SESSION['paya_offset'];
} else {
    $_SESSION['paya_offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['paya_offset'];

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$query = sprintf("
        select    data.order_seq          AS 発行連番
                  , substr(to_char(data.date_issue, 'FM9999/99/99'), 6, 5)          AS 発行日
                  , data.pre_seq            AS 前の連番
                  , to_char(data.sei_no,'FM0000000')        AS 製造番号
                  , data.order_no           AS 注文番号
                  , data.parts_no           AS 部品番号
                  , data.vendor             AS 発注先コード
                  , data.order_q            AS 注文数
                  , data.order_price        AS 単価
                  , substr(to_char(proc.delivery, 'FM9999/99/99'), 0, 11)            AS 納期
                  , substr(to_char(data.uke_date, 'FM9999/99/99'), 0, 11)            AS 納入日
                  , data.kouji_no           AS 工事番号
                  , proc.pro_mark           AS 工程
                  , proc.mtl_cond           AS 材料条件
                  , proc.pro_kubun          AS 工程単価区分
                  , proc.order_date         AS 発注日
                  , proc.order_q            AS 元注文数
                  , proc.locate             AS 納入場所
                  , proc.kamoku             AS 科目
                  , proc.order_ku           AS 発注区分
                  , proc.plan_cond          AS 発注計画区分
                  , proc.next_pro           AS 次工程
                  , trim(substr(mast.name, 1, 10))           AS 発注先名
                  , trim(mast.name)                         AS vendor_name
                  , trim(substr(item.midsc, 1, 18))         AS 部品名
                  , CASE
                          WHEN trim(item.mzist) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                          ELSE substr(item.mzist, 1, 8)
                    END                     AS 材質
                  , CASE
                          WHEN trim(item.mepnt) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                          ELSE substr(item.mepnt, 1, 8)
                    END                     AS 親機種
            from
                order_data      AS data
            left outer join
                order_process   AS proc
                                        using(sei_no, order_no, vendor)
            LEFT OUTER JOIN
                order_plan      AS plan     USING (sei_no)
            left outer join
                vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(data.parts_no = item.mipn)
            %s
            order by proc.delivery ASC, data.uke_date ASC, data.parts_no ASC
            OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // 共用 $search で検索
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] = '納期遅れデータがありません！';
    $view = 'NG';
} else {
    $num_res = count($res);
}

// コメントの登録ロジック
if(isset($_POST['comment_input'])) {
    $comment  = array();                // コメント
    $sei_no   = array();                // 製造No.
    $parts_no = array();                // 部品No.
    $comment  = $_POST['comment'];
    $sei_no   = $_POST['sei_no'];
    $parts_no = $_POST['c_parts_no'];
    $num = count($sei_no) + 1;
    for($r=1; $r<$num; $r++) {
        $query = sprintf("SELECT comment FROM order_details_comment WHERE sei_no='%s' AND parts_no='%s'", $sei_no[$r], $parts_no[$r]);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            $query = sprintf("UPDATE order_details_comment SET comment='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE sei_no='%s' AND parts_no='%s'", $comment[$r], $_SESSION['User_ID'], $sei_no[$r], $parts_no[$r]);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの変更失敗！";      // .= に注意
                $msg_flg = 'alert';
            } else {
                $_SESSION['s_sysmsg'] = "コメントを登録しました"; // .= に注意
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO order_details_comment (sei_no, parts_no, comment, last_date, last_user)
                              VALUES ('%s', '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $sei_no[$r], $parts_no[$r], $comment[$r], $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの追加に失敗！";      // .= に注意
                $msg_flg = 'alert';
            } else {
                $_SESSION['s_sysmsg'] = "コメントを追加しました！";    // .= に注意
            }
        }
    }
    
}

// 'YY/MM/DD'フォーマットの８桁の日付をYYYYMMDDの８桁にフォーマットして返す。
function format_date8($date8)
{
    if (0 == $date8) {
        $date8 = '--------';    
    }
    if (8 == strlen($date8)) {
        $nen   = substr($date8,0,2);
        $tsuki = substr($date8,3,2);
        $hi    = substr($date8,6,2);
        return '20' . $nen . $tsuki . $hi;
    } else {
        return FALSE;
    }
}
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();   // F2/F12キーを有効化する対応
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.form_name.element_name.select();
}
function init() {
}
function winActiveChk() {
    window.focus();
    return;
    /***** 以下の処理はsetInterval()を使用した場合に使う *****/
    if (document.all) {     // IEなら
        if (document.hasFocus() == false) {     // IE5.5以上で使える
            window.focus();
            return;
        }
        return;
    } else {                // NN ならとワリキッテ
        window.focus();
        return;
    }
    // 使用法 <body onLoad="setInterval('winActiveChk()',100)">
    // <input type='button' value='TEST' onClick="window.opener.location.reload()">
    // parent.Header.関数名() or オブジェクト;
}
function inspection_recourse(order_seq, parts_no, parts_name) {
    if (confirm('部品番号：' + parts_no + '\n\n部品名称：' + parts_name + " の\n\n緊急部品 検査依頼の予約をします。\n\n宜しいですか？")) {
        // 実行します。
        document.inspection_form.order_seq.value = order_seq;
        document.inspection_form.submit();
    } else {
        alert('取消しました。');
    }
}
function vendor_code_view(vendor, vendor_name) {
    alert('発注先コード：' + vendor + '\n\n発注先名：' + vendor_name + '\n\n');
}
function input_details(comment) {
        alert('テスト' + comment + '\n\n');
}
function win_open(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:   8pt;
    font-weight: normal;
    font-family: monospace;
}
.pt9 {
    font-size:   9pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10 {
    font-size:   10pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
th {
    background-color: yellow;
    color:            blue;
    font-size:        10pt;
    font-weight:      bold;
    font-family:      monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:              blue;
    text-decoration:    none;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?= $current_script ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?= $caption_title . "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='comment_form' action="" method="post">
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width=' 4%'>No</th>
                    <th class='winbox' nowrap width=' 8%' style='font-size:9.5pt;'>納 期</th>
                    <th class='winbox' nowrap width=' 8%' style='font-size:9.5pt;'>納入日</th>
                    <th class='winbox' nowrap width=' 7%' style='font-size:9.5pt;'>製造<BR>番号</th>
                    <th class='winbox' nowrap width='12%'>部品番号</th>
                    <th class='winbox' nowrap width='15%'>部品名</th>
                    <th class='winbox' nowrap width=' 6%'>注文数</th>
                    <th class='winbox' nowrap width=' 3%' style='font-size:10.5pt;'>工<br>程</th>
                    <th class='winbox' nowrap width='18%'>発注先名</th>
                    <th class='winbox' nowrap width='19%'>コメント</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                    // 製造No.と部品番号よりコメントを取得
                    $query_c = sprintf("SELECT comment FROM order_details_comment WHERE sei_no='%s' AND parts_no='%s'", $rec['製造番号'], $rec['部品番号']);
                    $res_chk_c = array();
                    if ( $rows_c = getResult($query_c, $res_c) < 1 ) {    // 登録なし
                        $comment = "";
                    } else {
                        $comment = $res_c[0][0];
                    }
                    echo "<td class='winbox' align='right'  width=' 4%'  bgcolor='#d6d3ce'><span class='pt10b'>", ($i + $offset), "</span></td>\n";
                    echo "<td class='winbox' align='center' width=' 8%'  bgcolor='#d6d3ce'><span class='pt9'>{$rec['納期']}</span></td>\n";
                    echo "<td class='winbox' align='center' width=' 8%'  bgcolor='#d6d3ce'><span class='pt9'>{$rec['納入日']}</span></td>\n";
                    echo "<td class='winbox' align='center' width=' 7%'  bgcolor='#d6d3ce'><span class='pt9'>{$rec['製造番号']}</span></td>\n";
                    echo "<td class='winbox' nowrap align='center' width='12%'  bgcolor='#d6d3ce' onClick='win_open(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&noMenu=yes&targetPartsNo=" . urlencode($rec['部品番号']) . "\");'>\n";
                    echo "    <a class='link' href='javascript:void(0);' target='_self' style='text-decoration:none;'><span class='pt11b'>{$rec['部品番号']}</a></td>\n";
                    echo "<td class='winbox' nowrap align='left'   width='15%' bgcolor='#d6d3ce'><span class='pt9'>" . mb_convert_kana($rec['部品名'], 'k') . "</span></td>\n";
                    echo "<td class='winbox' align='right'  width=' 6%'  bgcolor='#d6d3ce'><span class='pt9'>" . number_format($rec['注文数'], 0) . "</span></td>\n";
                    echo "<td class='winbox' align='center' width=' 3%'  bgcolor='#d6d3ce' style='font-size:9.5pt;'><span class='pt9'>{$rec['工程']}</span></td>\n";
                    echo "<td class='winbox' nowrap align='left' width='18%' bgcolor='#d6d3ce' onClick='vendor_code_view(\"{$rec['発注先コード']}\",\"{$rec['vendor_name']}\")'><span class='pt9'>{$rec['発注先名']}</span></td>\n";
                    if ($comment=="") {                                 // コメントの登録がない場合(入力フォームを表示)
                        echo "<td class='winbox' align='left'   width='19%' bgcolor='#d6d3ce'><span class='pt11'>
                                    <input type='text' name='comment[". $i ."]' size='20' maxlength='10' value='". $comment . "' style='text-align:left; font-size:12pt; font-weight:bold;'>
                                    <input type='hidden' name='sei_no[". $i ."]' value='{$rec['製造番号']}'>
                                    <input type='hidden' name='c_parts_no[". $i ."]' value='{$rec['部品番号']}'>
                            </span></td>\n";
                        echo "</tr>\n";
                    } else if (isset($_POST['comment_change'])){        // コメントの修正ボタンが押された時(入力フォームを表示)
                        echo "<td class='winbox' align='left'   width='19%' bgcolor='#d6d3ce'><span class='pt11'>
                                    <input type='text' name='comment[". $i ."]' size='20' maxlength='10' value='". $comment . "' style='text-align:left; font-size:12pt; font-weight:bold;'>
                                    <input type='hidden' name='sei_no[". $i ."]' value='{$rec['製造番号']}'>
                                    <input type='hidden' name='c_parts_no[". $i ."]' value='{$rec['部品番号']}'>
                            </span></td>\n";
                        echo "</tr>\n";
                    } else {                                            // それ以外(コメントがすでに登録されている場合)(入力できないようにする)
                        echo "<td class='winbox' align='left' width='19%' bgcolor='#d6d3ce'><span class='pt11'>{$comment}</span></td>
                                    <input type='hidden' name='comment[". $i ."]' value='{$comment}'>
                                    <input type='hidden' name='sei_no[". $i ."]' value='{$rec['製造番号']}'>
                                    <input type='hidden' name='c_parts_no[". $i ."]' value='{$rec['部品番号']}'>";
                        echo "</tr>\n";
                    }
            }
        ?>
        <td colspan='9'>　</td>
        <td>
            <input type='submit' class='entry_font' name='comment_input' value='コメント登録'>
            <input type='submit' class='entry_font' name='comment_change' value='コメント修正'>
        </td>
            </tbody>
        </form>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
