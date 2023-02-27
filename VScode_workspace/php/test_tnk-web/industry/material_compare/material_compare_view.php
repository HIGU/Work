<?php
//////////////////////////////////////////////////////////////////////////////
// 新旧総材料費 差額の照会 画面表示                                         //
// Copyright(C) 2011      Noriisa.Ohya norihisa_ooya@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2011/05/16 Created   material_compare_view.php                           //
// 2011/05/17 最新仕切・掛率の追加、表示順序に掛率順を追加。セルの幅の調整。//
// 2011/05/26 大分類・小分類の追加、CSV出力の追加                           //
//            総材料費の履歴へのリンクを追加                                //
// 2011/05/30 総材料費の比較を別メニューにまとめた為require_onceのリンク変更//
// 2011/05/31 グループコード変更に伴いSQL文を変更                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');            // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=21(総材料費の登録 計画番号)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('新旧総材料費 差額明細照会');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費照会',   INDUST . 'material/materialCost_view.php');
$menu->set_action('単価登録照会',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('総材料費の履歴',     INDUST . 'material/materialCost_view_assy.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 初回時のセッションデータ保存   次頁・前頁を軽くするため
if (! (isset($_POST['forward']) || isset($_POST['backward']) || isset($_GET['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    $_SESSION['s_uri_passwd'] = $_POST['uri_passwd'];
    $_SESSION['s_div']        = $_POST['div'];
    $_SESSION['s_first_ym']    = $_POST['first_ym'];
    $_SESSION['s_second_ym']      = $_POST['second_ym'];
    $_SESSION['uri_assy_no']  = $_POST['assy_no'];
    $_SESSION['s_order']  = $_POST['order'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $first_ym    = $_SESSION['s_first_ym'];
    $second_ym      = $_SESSION['s_second_ym'];
    $assy_no    = $_SESSION['uri_assy_no'];
    $order      = $_SESSION['s_order'];
    ////////////// パスワードチェック
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>パスワードが違います！</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
} 
$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$first_ym    = $_SESSION['s_first_ym'];
$second_ym      = $_SESSION['s_second_ym'];
$assy_no    = $_SESSION['uri_assy_no'];
$order      = $_SESSION['s_order'];

$cost1_ym = $first_ym;
$cost2_ym = $second_ym;

$nen        = substr($cost1_ym, 0, 4);
$tsuki      = substr($cost1_ym, 4, 2);
$cost1_name = $nen . "/" . $tsuki;

$nen        = substr($cost2_ym, 0, 4);
$tsuki      = substr($cost2_ym, 4, 2);
$cost2_name = $nen . "/" . $tsuki;

if (substr($cost1_ym,4,2)!=12) {
    $cost1_ymd = $cost1_ym + 1;
    $cost1_ymd = $cost1_ymd . '10';
} else {
    $cost1_ymd = $cost1_ym + 100;
    $cost1_ymd = $cost1_ymd - 11;
    $cost1_ymd = $cost1_ymd . '10';
}
if (substr($cost2_ym,4,2)!=12) {
    $cost2_ymd = $cost2_ym + 1;
    $cost2_ymd = $cost2_ymd . '10';
} else {
    $cost2_ymd = $cost2_ym + 100;
    $cost2_ymd = $cost2_ymd - 11;
    $cost2_ymd = $cost2_ymd . '10';
}

$str_ymd = $second_ym - 300;
$str_ymd = $str_ymd . '01';
$end_ymd = $second_ym . '31';

if ($div == "C") {
    if ($second_ym < 200710) {
        $rate = 25.60;  // カプラ標準 2007/10/01価格改定以前
    } elseif ($second_ym < 201104) {
        $rate = 57.00;  // カプラ標準 2007/10/01価格改定以降
    } else {
        $rate = 45.00;  // カプラ標準 2011/04/01価格改定以降
    }
} elseif ($div == "L") {
    if ($second_ym < 200710) {
        $rate = 37.00;  // リニア 2008/10/01価格改定以前
    } elseif ($second_ym < 201104) {
        $rate = 44.00;  // リニア 2008/10/01価格改定以降
    } else {
        $rate = 53.00;  // リニア 2011/04/01価格改定以降
    }
} else {
    $rate = 65.00;
}

///////// 掛率判定値
///////// 掛率が一定ではなくなったら表示部のロジックも変更する。
$power_rate = 1.13;      // 2011/04/01移行

if ($order == 'assy') {
    $order_name = 'ORDER BY 製品番号 ASC';
} elseif ($order == 'diff') {
    $order_name = 'ORDER BY 材料費増減 DESC, 率％ DESC, 照会順 ASC, 中分類名 ASC, 製品番号 ASC';
} elseif ($order == 'per') {
    $order_name = 'ORDER BY 率％ DESC, 材料費増減 DESC, 照会順 ASC, 中分類名 ASC, 製品番号 ASC';
} elseif ($order == 'power') {
    $order_name = 'ORDER BY 掛率 DESC, 率％ DESC, 材料費増減 DESC, 照会順 ASC, 中分類名 ASC, 製品番号 ASC';
} elseif ($order == 'sorder') {
    $order_name = 'ORDER BY 照会順 ASC, 中分類名 ASC, 製品番号 ASC';
} else {
    $order_name = 'ORDER BY 製品番号 ASC';
}

//////////// 表題の設定
//////////// 対象年月の表示データ編集
$end_y = substr($second_ym,0,4);
$end_m = substr($second_ym,4,2);
$str_y = substr($second_ym,0,4) - 3;
$str_m = substr($second_ym,4,2);

if ($div == "C") {
    $cap_div= "カプラ標準品"; 
} elseif ($div == "L") {
    $cap_div= "リニア"; 
}
$cap_set= $cap_div . "で{$str_y}年{$str_m}月から{$end_y}年{$end_m}月までの売上製品が対象。{$cost1_name}の賃率は、{$cost2_name}の賃率{$rate}円を使用。<br>率％は、材料費増減÷{$cost2_name}総材料費。掛率は、最新仕切÷{$cost2_name}の総材料費。<BR>掛率が{$power_rate}より大きければ、<font color='blue'>青字</font>。小さければ<font color='red'>赤字</font>。"; 
$menu->set_caption($cap_set);

//////////// 対象データの取得
$query = "
    SELECT
        u.assyno                    AS 製品番号 --- 0
        ,
        trim(substr(m.midsc,1,40))  AS 製品名   --- 1
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END                         AS 総材料費 --- 2
        ,
        CASE
            WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
            ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END
                                    AS 登録日 --- 3
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END                         AS 総材料費 --- 4
        ,
        CASE
            WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
            ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END
                                    AS 登録日 --- 5
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                          - (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                END
        END                         AS 材料費増減   --- 6
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                 CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE Uround(((SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                          - (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) 
                          / (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 4) * 100
                 END
        END                         AS 率％         --- 7
        ,
        CASE
            WHEN (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1)
        END                         AS 最新仕切     --- 8
        ,
        CASE
            WHEN (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                 CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1)
                          /(SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)
                 END
        END                         AS 掛率         --- 9
        ,
        CASE
            WHEN tgrp.top_name IS NULL THEN '------'
            ELSE tgrp.top_name
        END                         AS 大分類名     --- 10
        ,
        CASE
            WHEN mgrp.group_name IS NULL THEN '------'
            ELSE mgrp.group_name               
        END                         AS 中分類名     --- 11
        ---------------- リスト外 -----------------
        ,
        (SELECT plan_no FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS 第１総材料計画 --- 12
        ,
        (SELECT plan_no FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS 第２総材料計画 --- 13
        ,
        (SELECT a_rate FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 第１自動機賃率,      -- 14
        (SELECT a_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 第１自動機工数,      -- 15
        (SELECT m_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 第１手作業工数,      -- 16
        (SELECT g_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 第１外注工数,        -- 17
        (SELECT a_rate FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 第２自動機賃率,      -- 18
        (SELECT a_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 第２自動機工数,      -- 19
        (SELECT m_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 第２手作業工数,      -- 20
        (SELECT g_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 第２外注工数,        -- 21
        tgrp.s_order                AS 照会順         -- 22
    FROM
          hiuuri AS u
    LEFT OUTER JOIN
          assembly_schedule AS a
    ON (u.計画番号 = a.plan_no)
    LEFT OUTER JOIN
          miitem AS m
    ON (u.assyno = m.mipn)
    LEFT OUTER JOIN
          material_old_product AS mate
    ON (u.assyno = mate.assy_no)
    LEFT OUTER JOIN
          mshmas AS mas
    ON (u.assyno = mas.mipn)
    LEFT OUTER JOIN
          mshmas AS hmas
    ON (u.assyno = hmas.mipn)
    LEFT OUTER JOIN
          -- mshgnm AS gnm
          msshg3 AS gnm
    -- ON (hmas.mhjcd = gnm.mhgcd)
    ON (hmas.mhshc = gnm.mhgcd)
    LEFT OUTER JOIN
          product_serchGroup AS mgrp
    ON (gnm.mhggp = mgrp.group_no)
    LEFT OUTER JOIN
          product_top_serchgroup AS tgrp
    ON (mgrp.top_code = tgrp.top_no)
    WHERE 計上日 >= {$str_ymd} AND 計上日 <= {$end_ymd} AND 事業部 = '{$div}' AND (note15 NOT LIKE 'SC%%' OR note15 IS NULL) AND datatype='1'
        AND mate.assy_no IS NULL
        -- これを追加すれば自動機の登録があるもの AND (SELECT a_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
    GROUP BY u.assyno, m.midsc, tgrp.top_name, mgrp.group_name, tgrp.s_order
    {$order_name}
    OFFSET 0 LIMIT 10000
";
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($first_ym), format_date($second_ym) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();                          // F2/F12キーで戻るための対応
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt9b {
    font-size:      9pt;
    font-family:    monospace;
    color:          blue;
}
.pt9r {
    font-size:      9pt;
    font-family:    monospace;
    color:          red;
}
.pt10 {
    font-size:l     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
    <center>
<?php echo $menu->out_title_border()?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <!------
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    -------->
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                        <a href='material_compare_csv.php?csv_div=<?php echo $div ?>&csv_first_ym=<?php echo $first_ym ?>&csv_second_ym=<?php echo $second_ym ?>&csv_assy_no=<?php echo $assy_no ?>&csv_order=<?php echo $order ?>'>
                        CSV出力
                        </a>
                    </td>
                    <!------
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                    </td>
                    -------->
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
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                    //if ($i == 5) continue;
                    if ($i >= 12) continue;
                    if ($i == 2) {
                ?>
                    <th class='winbox' nowrap><?php echo $cost1_name ?><BR><?php echo $field[$i] ?></th>
                <?php
                    } elseif ($i == 4) {
                ?>
                    <th class='winbox' nowrap><?php echo $cost2_name ?><BR><?php echo $field[$i] ?></th>
                <?php
                    } elseif ($i == 6) {
                ?>
                    <th class='winbox' nowrap>材料費<BR>増減</th>
                <?php
                    } elseif ($i == 8) {
                ?>
                    <th class='winbox' nowrap>最新<BR>仕切</th>
                <?php
                    } else {
                ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                    }
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    $recNo = $r;
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + 1) . "</div></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        if ($i >= 12) continue;
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                        case 0:     // 製品番号
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費の履歴'), "?assy=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>"
                                                , $res[$r][$i], "</a></div></td>\n";
                            break;
                        case 1:     // 製品名
                            echo "<td class='winbox' nowrap width='230' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            break;
                        case 2:     // 第１総材料費
                            if ($res[$r][$i] != 0) {
                                echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費照会'), "?plan_no={$res[$r][12]}&assy_no={$res[$r][0]}\")' target='application' style='text-decoration:none;'>"
                                                , number_format($res[$r][$i], 2), "</a></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            }
                            break;
                        case 4:     // 第２総材料費
                            if ($res[$r][$i] != 0) {
                                echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費照会'), "?plan_no={$res[$r][13]}&assy_no={$res[$r][0]}\")' target='application' style='text-decoration:none;'>"
                                                , number_format($res[$r][$i], 2), "</a></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            }
                            break;
                        case 6:    // 総材料費増減
                            echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            break;
                        case 7:    // 率％
                            echo "<td class='winbox' nowrap width='50' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            break;
                        case 8:    // 最新仕切
                            echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            break;
                        case 9:   // 掛率
                            if ($res[$r][$i] < $power_rate) {
                                echo "<td class='winbox' nowrap width='30' align='right'><div class='pt9r'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            } elseif ($res[$r][$i] > $power_rate) {
                                echo "<td class='winbox' nowrap width='30' align='right'><div class='pt9b'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            } elseif ($res[$r][$i] == $power_rate) {
                                echo "<td class='winbox' nowrap width='30' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            }
                            break;
                        default:    // その他
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                        }
                        // <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
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
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
