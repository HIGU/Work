<?php
//////////////////////////////////////////////////////////////////////////////
// 買掛ヒストリの照会 ＆ チェック用  更新元 UKWLIB/W#HIBCTR                 //
// 生管依頼 協力工場毎の合計金額照会からのリンク                            //
// Copyright (C) 2013-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/04/09 Created   act_payable2_view.php                               //
// 2018/01/29 カプラ特注・標準を追加                                   大谷 //
// 2018/06/29 多部門のT部品購入に対応                                  大谷 //
// 2020/12/21 多部門のT部品購入に対応終了                              大谷 //
//////////////////////////////////////////////////////////////////////////////
 ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 10);                    // site_index=40(生産メニュー) site_id=10(買掛実績)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('買 掛 実 績 の 照 会');
//////////// 表題の設定     下のロジックで処理するためここでは使用しない
// $menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('単価経歴表示',   INDUST . 'parts/parts_cost_view.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

$uke_no = '';
$current_script = $menu->out_self();
if($_SESSION['paya_code'] == 'on') {
    if (isset($_SESSION['payable_code'])) {
        $_REQUEST['payable_code'] = $_SESSION['payable_code'];
        $payable_code = $_SESSION['payable_code'];
    }
    if (isset($_SESSION['payable_s_ym'])) {
        $s_ym = $_SESSION['payable_s_ym'];
        $_SESSION['payable_s_ym'] = $s_ym;
    } else {
        $s_ym = date('Ym');
    }
    if (isset($_SESSION['payable_e_ym'])) {
        $e_ym = $_SESSION['payable_e_ym'];
        $_SESSION['payable_e_ym'] = $e_ym;
    } else {
        $e_ym = date('Ym');
    }
    if (isset($_SESSION['payable_div'])) {
        if ($_SESSION['payable_div']=='A') {
            $_SESSION['payable_div'] = ' ';
        }
        $sum_div = $_SESSION['payable_div'];
        $_SESSION['payable_div'] = $sum_div;
    } else {
        if (isset($_SESSION['payable_div'])) {
            if ($_SESSION['payable_div']=='A') {
                $_SESSION['payable_div'] = ' ';
            }
            $sum_div = $_SESSION['payable_div'];
        } else {
            $sum_div = ' ';
        }
    }
    if (isset($_SESSION['payable_vendor'])) {
        $sum_vendor = $_SESSION['payable_vendor'];
        $_SESSION['payable_vendor'] = $sum_vendor;
    } else {
        if (isset($_SESSION['payable_vendor'])) {
            $sum_vendor = $_SESSION['payable_vendor'];
        } else {
            $sum_vendor = '';
        }
    }
    if (isset($_SESSION['paya_page'])) {
        $paya_page = $_SESSION['paya_page'];
        $_SESSION['payable_page'] = $paya_page;
    } else {
        if (isset($_SESSION['payable_page'])) {
            $paya_page = $_SESSION['payable_page'];
        } else {
            $paya_page = 25;
        }
    }
} elseif($_REQUEST['payable_code'] == 'summary1') {
    $payable_code = $_REQUEST['payable_code'];
    /////////// summary_view で追加されたパラメーター
    if (isset($_REQUEST['payable_s_ym'])) {
        $s_ym = $_REQUEST['payable_s_ym'];
        $_SESSION['payable_s_ym'] = $s_ym;
    } else {
        $s_ym = date('Ym');
    }
    if (isset($_REQUEST['payable_e_ym'])) {
        $e_ym = $_REQUEST['payable_e_ym'];
        $_SESSION['payable_e_ym'] = $e_ym;
    } else {
        $e_ym = date('Ym');
    }
    if (isset($_REQUEST['payable_div'])) {
        if ($_REQUEST['payable_div']=='A') {
            $_REQUEST['payable_div'] = ' ';
        }
        $sum_div = $_REQUEST['payable_div'];
        $_SESSION['payable_div'] = $sum_div;
    } else {
        if (isset($_SESSION['payable_div'])) {
            if ($_SESSION['payable_div']=='A') {
                $_SESSION['payable_div'] = ' ';
            }
            $sum_div = $_SESSION['payable_div'];
        } else {
            $sum_div = ' ';
        }
    }
    if (isset($_REQUEST['payable_vendor'])) {
        $sum_vendor = $_REQUEST['payable_vendor'];
        $_SESSION['payable_vendor'] = $sum_vendor;
    } else {
        if (isset($_SESSION['payable_vendor'])) {
            $sum_vendor = $_SESSION['payable_vendor'];
        } else {
            $sum_vendor = '';
        }
    }
    if (isset($_REQUEST['paya_page'])) {
        $paya_page = $_REQUEST['paya_page'];
        $_SESSION['payable_page'] = $paya_page;
    } else {
        if (isset($_SESSION['payable_page'])) {
            $paya_page = $_SESSION['payable_page'];
        } else {
            $paya_page = 25;
        }
    }
} elseif($_REQUEST['payable_code'] == 'summary2') {
    $payable_code = $_REQUEST['payable_code'];
    /////////// summary2_view で追加されたパラメーター
    if (isset($_REQUEST['payable_s2_ym'])) {
        $s_ym = $_REQUEST['payable_s2_ym'];
        $_SESSION['payable_s2_ym'] = $s_ym;
    } else {
        $s_ym = date('Ym');
    }
    if (isset($_REQUEST['payable_e2_ym'])) {
        $e_ym = $_REQUEST['payable_e2_ym'];
        $_SESSION['payable_e2_ym'] = $e_ym;
    } else {
        $e_ym = date('Ym');
    }
    if (isset($_REQUEST['payable2_div'])) {
        if ($_REQUEST['payable2_div']=='A') {
            $_REQUEST['payable2_div'] = ' ';
        }
        $sum_div = $_REQUEST['payable2_div'];
        $_SESSION['payable2_div'] = $sum_div;
    } else {
        if (isset($_SESSION['payable2_div'])) {
            if ($_SESSION['payable2_div']=='A') {
                $_SESSION['payable2_div'] = ' ';
            }
           $sum_div = $_SESSION['payable2_div'];
        } else {
           $sum_div = ' ';
        }
    }
    if (isset($_REQUEST['payable2_vendor'])) {
        $sum_vendor = $_REQUEST['payable2_vendor'];
        $_SESSION['payable2_vendor'] = $sum_vendor;
    } else {
        if (isset($_SESSION['payable2_vendor'])) {
            $sum_vendor = $_SESSION['payable2_vendor'];
        } else {
            $sum_vendor = '';
        }
    }
    if (isset($_REQUEST['paya_page'])) {
        $paya_page = $_REQUEST['paya_page'];
        $_SESSION['payable_page'] = $paya_page;
    } else {
        if (isset($_SESSION['payable_page'])) {
            $paya_page = $_SESSION['payable_page'];
        } else {
            $paya_page = 25;
        }
    }
}
//////////// 日東工器支給品の対応
if (isset($_REQUEST['kei_ym'])) {
    $kei_ym = $_REQUEST['kei_ym'];
    $kei_ym = format_date8($kei_ym);
    $_SESSION['kei_ym'] = $kei_ym;
} else {
    $kei_ym = @$_SESSION['kei_ym'];     // @単価経歴の戻り時の対応(逆の場合は無視する)
}

$s_ymd = $s_ym . '01';   // 開始日
$e_ymd = $e_ym + 1;   // 次の月を求める
$Y4 = substr($e_ymd, 0, 4);
$M2 = substr($e_ymd, 4, 2);
if ($M2 > 12) {
    $Y4 += 1;
    $M2  = 1;
}
$e_ymd = date('Ymd', (mktime(0, 0, 0, $M2, 1, $Y4) - 1));   // 終了年月日
//////////// 一頁の行数
define('PAGE', $paya_page);


$query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$sum_vendor}' limit 1";
if (getUniResult($query, $name) <= 0) {
    $name = 'マスター未登録';
}
//////////// SQL 文の where 句を 共用する
if($payable_code == 'summary1') {
    $search = sprintf("where act_date>=%d and act_date<=%d", $s_ymd, $e_ymd);
    switch ($sum_div) {
    case ' ';    // 全体
        $search_kin = sprintf("%s and kamoku<=5", $search);
        $caption_div = '全体(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：全体　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'C';    // カプラ 全体
        $search_kin = sprintf("%s and kamoku<=5 and a.div='C'", $search);
        $caption_div = 'カプラ全体(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：C全体　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'D';    // カプラ 標準
        $search_kin = sprintf("%s and kamoku<=5 and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $search);
        $caption_div = 'カプラ標準品(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：C標準　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'S';    // カプラ 特注
        $search_kin = sprintf("%s and kamoku<=5 and a.div='C' and kouji_no like 'SC%%'", $search);
        $caption_div = 'カプラ特注品(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：C特注　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'L';    // リニア 全体
        //$search_kin = sprintf("%s and kamoku<=5 and a.div='L' and a.parts_no not like '%s'", $search, 'T%');
        $search_kin = sprintf("%s and kamoku<=5 and a.div='L'", $search);
        $caption_div = 'リニア全体(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：L全体　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'T';    // ツール 全体
        //$search_kin = sprintf("%s and kamoku<=5 and (a.div='T' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s'))", $search, 'T%');
        $search_kin = sprintf("%s and kamoku<=5 and a.div='T'", $search);
        $caption_div = 'ツール全体(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：T全体　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'NKCT';    // NKCT
        $search_kin = sprintf("%s and kamoku<=5 and a.div='C' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $search, '8%', 'Z%', 'H%');
        $caption_div = 'ＮＫＣＴ(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：{$sum_div}　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'NKT';    // NKT
        $search_kin = sprintf("%s and kamoku<=5 and a.div='L' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $search, '8%', 'Z%', 'H%');
        $caption_div = 'ＮＫＴ(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：{$sum_div}　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    }
    $search = sprintf("%s and vendor='%s'", $search_kin,$sum_vendor);
} elseif($payable_code == 'summary2') {
    $search = sprintf("where act_date>=%d and act_date<=%d and order_no !='0000000' and sei_no !=0", $s_ymd, $e_ymd);
    switch ($sum_div) {
    case ' ';    // 全体
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3", $search);
        $caption_div = '全体(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：全体　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'C';    // カプラ 全体
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='C'", $search);
        $caption_div = 'カプラ全体(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：C全体　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'D';    // カプラ 標準
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $search);
        $caption_div = 'カプラ標準品(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：C標準　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'S';    // カプラ 特注
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='C' and kouji_no like 'SC%%'", $search);
        $caption_div = 'カプラ特注品(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：C特注　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'L';    // リニア 全体
        //$search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='L' and a.parts_no not like '%s'", $search, 'T%');
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='L'", $search);
        $caption_div = 'リニア全体(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：L全体　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'T';    // ツール 全体
        //$search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and (a.div='T' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s'))", $search, 'T%');
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='T'", $search);
        $caption_div = 'ツール全体(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：T全体　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'NKCT';    // NKCT
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='C' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $search, '8%', 'Z%', 'H%');
        $caption_div = 'ＮＫＣＴ(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：{$sum_div}　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    case 'NKT';    // NKT
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='L' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $search, '8%', 'Z%', 'H%');
        $caption_div = 'ＮＫＴ(外注別)　内作及び諸口を含む';
        $caption_title = "事業部：{$sum_div}　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($s_ymd) . '〜' . format_date($e_ymd);
        break;
    }
    $search = sprintf("%s and vendor='%s'", $search_kin,$sum_vendor);
}
//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf('select count(*), sum(Uround(order_price * siharai,0)) from act_payable as a LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=a.parts_no) left outer join order_plan using(sei_no) %s', $search);
$res_max = array();
if ( getResult2($query, $res_max) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $maxrows = $res_max[0][0];                  // 合計レコード数の取得
    // $sum_kin = $res_max[0][1];                  // 合計買掛金額の取得
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
        SELECT
            -- act_date    as 処理日,
            -- type_no     as \"T\",
            uke_no      as 受付番,          -- 00
            uke_date    as 受付日,          -- 01
            ken_date    as 検収日,          -- 02
            substr(trim(name), 1, 8)
                        as 発注先名,        -- 03
            a.parts_no    as 部品番号,        -- 04
            substr(midsc, 1, 12)
                        AS 部品名,          -- 05
            substr(mepnt, 1, 10)
                        AS 親機種,          -- 06
            koutei      as 工程,            -- 07
            mtl_cond    as 条,      -- 条件    08
            order_price as 発注単価,        -- 09
            genpin      as 現品数,          -- 10
            siharai     as 支払数,          -- 11
            Uround(order_price * siharai,0)
                        as 買掛金額,        -- 12
            sei_no      as 製造番号,        -- 13
            a.div       as 事,              -- 14
            kamoku      as 科,              -- 15
            order_no    as 注文番号,        -- 16
            vendor      as 発注先           -- 17
        FROM
            act_payable AS a
        LEFT OUTER JOIN
            vendor_master USING(vendor)
        left outer join
            order_plan
        using(sei_no)
        LEFT OUTER JOIN
            miitem ON (a.parts_no = mipn)
        LEFT OUTER JOIN
            parts_stock_master AS m ON (m.parts_no=a.parts_no)
        %s 
        ORDER BY act_date DESC
        OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '買掛データがありません。';
    if (isset($_REQUEST['material'])) {
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?material=' . $_REQUEST['material']);    // 直前の呼出元へ戻る
    } else {
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    }
    exit();
} else {
    $num = count($field);       // フィールド数取得
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

$paya_code = 'on';
if ($sum_div ==' ') {
    $sum_div = 'A';
}
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                        <!--  bgcolor='#ffffc6' 薄い黄色 -->
                        <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                    if ($uke_no == $res[$r][0]) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else if ($res[$r][17] == '91111' && $kei_ym == $res[$r][2]){  //日東工器購入品への色付け
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><span class='pt10b'>", ($r + $offset + 1), "</span></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case  5:        // 部品名
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            $res[$r][$i] = mb_substr($res[$r][$i], 0, 12);
                        case  3:        // 発注先名
                            if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case  4:        // 部品番号
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='center'>&nbsp;</td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('単価経歴表示'), "?parts_no=", urlencode("{$res[$r][$i]}"), "&lot_cost=", urlencode("{$res[$r][9]}"), "&uke_date={$res[$r][1]}&vendor={$res[$r][17]}&paya_code={$paya_code}&payable_code={$payable_code}&payable_s_ym={$s_ym}&payable_e_ym={$e_ym}&payable_div={$sum_div}&payable_vendor={$sum_vendor}&material=1#mark'>{$res[$r][$i]}</a></span></td>\n";
                            }
                            break;
                        case  6:        // 親機種
                            if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case  9:        // 発注単価
                        case 10:        // 現品数
                        case 11:        // 支払数
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 2) . "</span></td>\n";
                            break;
                        case 12:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i]) . "</span></td>\n";
                            break;
                        default:
                            if (trim($res[$r][$i]) == '') $res[$r][$i] = '&nbsp;';
                            echo "<td class='winbox' nowrap align='center'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                        }
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
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
