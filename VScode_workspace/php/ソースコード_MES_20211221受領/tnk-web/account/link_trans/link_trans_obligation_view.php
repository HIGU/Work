<?php
//////////////////////////////////////////////////////////////////////////////
// 連結取引総括表 債権債務 明細 照会                                        //
// Copyright (C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/10/24 Created   link_trans_obligation_view.php                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
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
$request = new Request;
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
//$menu->set_site( 1, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////////// リターンアドレス設定
//$menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
//$menu->set_title('売 上 明 細 照 会');
//////////// 呼出先のaction名とアドレス設定
//$_SESSION['link_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存
$menu->set_RetUrl($_SESSION['link_referer']);             // 通常は指定する必要はない

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 対象年月のセッションデータ取得
if ($request->get('wage_ym') != '') {
    $wage_ym = $request->get('wage_ym'); 
} elseif(isset($_POST['wage_ym'])) {
    $wage_ym = $_POST['wage_ym'];
} elseif(isset($_SESSION['wage_ym'])) {
    $wage_ym = $_SESSION['wage_ym'];
} else {
    $wage_ym = date('Ym');           // セッションデータがない場合の初期値(当月)
}

//////////// 対象年月のセッションデータ取得
if ($request->get('customer') != '') {
    $customer = $request->get('customer');
} elseif(isset($_POST['customer'])) {
    $customer = $_POST['customer'];
} elseif(isset($_SESSION['customer'])) {
    $customer = $_SESSION['customer'];
} else {
    $customer = '00001';           // セッションデータがない場合の初期値(00001:NK)
}

// テスト用エラー防止
$_SESSION['2ki_ym'] = $wage_ym;
//$_SESSION['2ki_ym'] = 201709;

// 対象月を取得
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$yyyy     = substr($yyyymm, 0,4);
$mm       = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}

if($customer=='00001') {
    $cus_name = '日東工器';
} elseif ($customer=='00004') {
    $cus_name = 'メドテック';
} elseif ($customer=='00005') {
    $cus_name = '白河日東工器';
} elseif ($customer=='00101') {
    $cus_name = 'ＮＫＩＴ';
}

$end_ym = $ki * 100 + 200003;
$str_ym = $end_ym - 99;


//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$cus_name}　債　権　債　務　明　細");

///////////// 各種経歴を取得
// 売掛金経歴を取得
$query = "select
                sales_ym   as 年月,
                sales_kuri as 月初繰越,
                sales_kei  as 売掛金計上,
                sales_kai  as 売掛金回収額,
                sales_zan  as 月末残高
          from
                link_trans_sales";
$search    = "where sales_code='$customer' and sales_ym>=$str_ym and sales_ym<=$end_ym ORDER BY sales_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query 文の完成
$res_urikake = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_urikake) <= 0) {
    $urikake_num = 0;
} else {
    $urikake_num = count($res_urikake);
}
// 買掛金経歴を取得
$query = "select
                expense_ym   as 年月,
                expense_kuri as 月初繰越,
                expense_kei  as 買掛金計上,
                expense_kai  as 買掛金回収額,
                expense_zan  as 月末残高
          from
                link_trans_expense_history";
$search    = "where expense_code='$customer' and expense_kamoku='買掛金' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query 文の完成
$res_kaikake = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_kaikake) <= 0) {
    $kaikake_num = 0;
} else {
    $kaikake_num = count($res_kaikake);
}

// 未収入金経歴を取得
$query = "select
                expense_ym   as 年月,
                expense_kuri as 繰越高,
                expense_kei  as 当月発生高,
                expense_kai  as 当月解消高,
                expense_zan  as 残高
          from
                link_trans_expense_history";
$search    = "where expense_code='$customer' and expense_kamoku='未収入金' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query 文の完成
$res_mishu = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_mishu) <= 0) {
    $mishu_num = 0;
} else {
    $mishu_num = count($res_mishu);
}
// 立替金経歴を取得
$query = "select
                expense_ym   as 年月,
                expense_kuri as 繰越高,
                expense_kei  as 当月発生高,
                expense_kai  as 当月解消高,
                expense_zan  as 残高
          from
                link_trans_expense_history";
$search    = "where expense_code='$customer' and expense_kamoku='立替金' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query 文の完成
$res_tatekae = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_tatekae) <= 0) {
    $tatekae_num = 0;
} else {
    $tatekae_num = count($res_tatekae);
}
// 未払金経歴を取得
$query = "select
                expense_ym   as 年月,
                expense_kuri as 繰越高,
                expense_kei  as 当月発生高,
                expense_kai  as 当月解消高,
                expense_zan  as 残高
          from
                link_trans_expense_history";
$search    = "where expense_code='$customer' and expense_kamoku='未払金' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query 文の完成
$res_mihakin = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_mihakin) <= 0) {
    $mihakin_num = 0;
} else {
    $mihakin_num = count($res_mihakin);
}
// 未払費用経歴を取得
$query = "select
                expense_ym   as 年月,
                expense_kuri as 繰越高,
                expense_kei  as 当月発生高,
                expense_kai  as 当月解消高,
                expense_zan  as 残高
          from
                link_trans_expense_history";
$search    = "where expense_code='$customer' and expense_kamoku='未払費用' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query 文の完成
$res_mihahiyo = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_mihahiyo) <= 0) {
    $mihahiyo_num = 0;
} else {
    $mihahiyo_num = count($res_mihahiyo);
}
// NKITのみ有償支給未収入金経歴を取得
if ($customer == '00101') {
    $query = "select
                    expense_ym   as 年月,
                    expense_kuri as 繰越高,
                    expense_kei  as 当月発生高,
                    expense_kai  as 当月解消高,
                    expense_zan  as 残高
              from
                    link_trans_expense_history";
    $search    = "where expense_code='$customer' and expense_kamoku='有償支給未収入金' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
    $query_s   = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_yumishu = array();
    $field = array();
    if ($rows=getResultWithField2($query_s, $field, $res_yumishu) <= 0) {
        $yumishu_num = 0;
    } else {
        $yumishu_num = count($res_yumishu);
    }
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>月</th>
                    <th class='winbox' nowrap colspan='4'>売掛金</th>
                    <th class='winbox' nowrap colspan='4'>買掛金</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>月初繰越</th>
                    <th class='winbox' nowrap>売掛金計上</th>
                    <th class='winbox' nowrap>売掛金回収額</th>
                    <th class='winbox' nowrap>月末残高</th>
                    <th class='winbox' nowrap>月初繰越</th>
                    <th class='winbox' nowrap>買掛金計上</th>
                    <th class='winbox' nowrap>買掛金相殺金額</th>
                    <th class='winbox' nowrap>月末残高</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            <?php
            $mm = 4;
            for ($r=0; $r<12; $r++) {   // １年間を強制表示
                echo "<tr>\n";
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>{$mm}月</span></td>\n";
                // 売掛金 繰越 計上 回収 残高の順
                if ($r >= $urikake_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_urikake[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_urikake[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_urikake[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_urikake[$r][4]) ."</span></td>\n";
                }
                // 買掛金 繰越 計上 回収 残高の順
                if ($r >= $kaikake_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_kaikake[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_kaikake[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_kaikake[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_kaikake[$r][4]) ."</span></td>\n";
                }
                echo "</tr>\n";
                if($mm == 12){
                    $mm = 1;
                } else {
                    $mm += 1;
                }
            }
            ?>
            
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>月</th>
                    <th class='winbox' nowrap colspan='4'>未収入金</th>
                    <th class='winbox' nowrap colspan='4'>立替金</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>繰越高</th>
                    <th class='winbox' nowrap>当月発生高</th>
                    <th class='winbox' nowrap>当月解消高</th>
                    <th class='winbox' nowrap>残高</th>
                    <th class='winbox' nowrap>繰越高</th>
                    <th class='winbox' nowrap>当月発生高</th>
                    <th class='winbox' nowrap>当月解消高</th>
                    <th class='winbox' nowrap>残高</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            <?php
            $mm = 4;
            for ($r=0; $r<12; $r++) {   // １年間を強制表示
                echo "<tr>\n";
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>{$mm}月</span></td>\n";
                // 未収入金 繰越 計上 回収 残高の順
                if ($r >= $mishu_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mishu[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mishu[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mishu[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mishu[$r][4]) ."</span></td>\n";
                }
                // 立替金 繰越 計上 回収 残高の順
                if ($r >= $tatekae_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_tatekae[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_tatekae[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_tatekae[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_tatekae[$r][4]) ."</span></td>\n";
                }
                echo "</tr>\n";
                if($mm == 12){
                    $mm = 1;
                } else {
                    $mm += 1;
                }
            }
            ?>
            
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>月</th>
                    <th class='winbox' nowrap colspan='4'>未払金</th>
                    <th class='winbox' nowrap colspan='4'>未払費用</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>繰越高</th>
                    <th class='winbox' nowrap>当月発生高</th>
                    <th class='winbox' nowrap>当月解消高</th>
                    <th class='winbox' nowrap>残高</th>
                    <th class='winbox' nowrap>繰越高</th>
                    <th class='winbox' nowrap>当月発生高</th>
                    <th class='winbox' nowrap>当月解消高</th>
                    <th class='winbox' nowrap>残高</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            <?php
            $mm = 4;
            for ($r=0; $r<12; $r++) {   // １年間を強制表示
                echo "<tr>\n";
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>{$mm}月</span></td>\n";
                // 未払金 繰越 計上 回収 残高の順
                if ($r >= $mihakin_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihakin[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihakin[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihakin[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihakin[$r][4]) ."</span></td>\n";
                }
                // 未払費用 繰越 計上 回収 残高の順
                if ($r >= $mihahiyo_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihahiyo[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihahiyo[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihahiyo[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihahiyo[$r][4]) ."</span></td>\n";
                }
                echo "</tr>\n";
                if($mm == 12){
                    $mm = 1;
                } else {
                    $mm += 1;
                }
            }
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <?php
        if ($customer == '00101') {
        ?>
        <BR>
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>月</th>
                    <th class='winbox' nowrap colspan='4'>有償支給未収入金</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>繰越高</th>
                    <th class='winbox' nowrap>当月発生高</th>
                    <th class='winbox' nowrap>当月解消高</th>
                    <th class='winbox' nowrap>残高</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            <?php
            $mm = 4;
            for ($r=0; $r<12; $r++) {   // １年間を強制表示
                echo "<tr>\n";
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>{$mm}月</span></td>\n";
                // 有償支給未収入金 繰越 計上 回収 残高の順
                if ($r >= $yumishu_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_yumishu[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_yumishu[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_yumishu[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_yumishu[$r][4]) ."</span></td>\n";
                }
                echo "</tr>\n";
                if($mm == 12){
                    $mm = 1;
                } else {
                    $mm += 1;
                }
            }
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <?php
        }
        ?>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
