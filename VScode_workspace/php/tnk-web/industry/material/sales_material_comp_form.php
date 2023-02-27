<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 売上 明細 照会 条件選択フォーム                             //
// Copyright(C) 2001-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created   sales_form.php → sales_material_comp_form.php      //
// 2002/08/07 セッション管理を追加                                          //
// 2002/08/27 フレーム対応                                                  //
// 2003/02/14 売上関係ニュー のフォントを style で指定に変更                //
//                              ブラウザーによる変更が出来ない様にした      //
// 2003/02/26 body に onLoad を追加し初期入力個所に focus() させた          //
// 2003/06/16 事業部にバイモルを追加し１ページの表示行数の設定を追加        //
// 2003/09/05 error_reporting = E_ALL 対応のため 配列変数の初期化追加       //
// 2003/10/31 製品番号指定とカプラ特注を条件に追加 <td>のfont-size 11pt     //
// 2003/11/26 デザインとロジックを一新 uriage.php → sales_form.php へ      //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
// 2003/12/23 JavaScriptのuriage.js→sales_form.jsへ変更 sales_pageに対応   //
// 2004/11/09 部門を全グループ・カプラ全体・特注・標準・リニア全体等に分けた//
//            売上区分を売上メニューと共有していたのを独立 indust_s_kubun   //
// 2005/01/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2006/03/18 $_SESSION['indust_s_kubun']→$_SESSION['s_kubun'] へ変更      //
// 2006/03/23 売上区分の初期値を '1'=完成 → ' '=全てへ変更                 //
// 2009/07/09 総材料費計算に使用する賃率を契約賃率か社内賃率か              //
//            選択できるように変更                                     大谷 //
// 2013/01/29 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 25);                    // site_index=30(生産メニュー) site_id=25(仕切と総材料)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('仕切単価と総材料費の比較表 条件選択');
//////////// 表題の設定
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('仕切と総材料',   INDUST . 'material/sales_material_comp_view.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

/////////////// 受け渡し変数の初期化
if ( isset($_SESSION['s_div']) ) {
    $div = $_SESSION['s_div'];
} else {
    $div = '';
}
if ( isset($_SESSION['s_d_start']) ) {
    $d_start = $_SESSION['s_d_start'];
} else {
    if ( isset($_POST['d_start']) ) {
        $d_start = $_POST['d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_SESSION['s_d_end']) ) {
    $d_end = $_SESSION['s_d_end'];
} else {
    if ( isset($_POST['d_end']) ) {
        $d_end = $_POST['d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
if ( isset($_SESSION['s_kubun']) ) {
    $kubun = $_SESSION['s_kubun'];
} else {
    $kubun = ' ';           // 初期値は 1=完成 → ' '=全てへ変更
}
if ( isset($_SESSION['s_uri_ritu']) ) {
    $uri_ritu = $_SESSION['s_uri_ritu'];
    $uri_ritu = '52.0';     // 初期値
} else {
    $uri_ritu = '52.0';     // 初期値
}
if ( isset($_SESSION['uri_assy_no']) ) {
    $assy_no = $_SESSION['uri_assy_no'];
} else {
    $assy_no = '';      // 初期化
}
if ( isset($_SESSION['s_rate']) ) {
    $rate = $_SESSION['s_rate'];
} else {
    $rate = 'c';      // 初期化 初期値社内賃率
}

// $_SESSION['s_rec_No'] = 0;  // 表示用レコード№を0にする。

if ( isset($_SESSION['s_sales_page']) ) {   // １ページ表示行数設定
    $sales_page = $_SESSION['s_sales_page'];     // 常に Default 25 になるようにコメント解除
    // $sales_page = 25;             // Default 25
} else {
    $sales_page = 25;             // Default 25
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>
<!--    ファイル指定の場合 -->
<script language='JavaScript' src='./sales_material_comp_form.js?<?= $uniq ?>'>
</script>

<script language="JavaScript">
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
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
td {
    font-size: 10pt;
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
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
</style>
<body  onLoad='document.uri_form.div.focus()' style='overflow:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <form name='uri_form' action='<?=$menu->out_action('仕切と総材料')?>' method='post' onSubmit='return chk_sales_form(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                        <div class='caption_font'><?=$menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製品グループを選択して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <select name="div">
                            <option value=" "<?php if($div==" ") echo("selected"); ?>>全グループ</option>
                            <option value="C"<?php if($div=="C") echo("selected"); ?>>カプラ全体</option>
                            <option value="S"<?php if($div=="S") echo("selected"); ?>>カプラ特注</option>
                            <option value="D"<?php if($div=="D") echo("selected"); ?>>カプラ標準</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>リニア全体</option>
                            <option value="N"<?php if($div=="N") echo("selected"); ?>>リニアのみ</option>
                            <option value="B"<?php if($div=="B") echo("selected"); ?>>液体ポンプ</option>
                            <option value="T"<?php if($div=="T") echo("selected"); ?>>ツール</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        日付を指定して下さい(必須)
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="d_start" size="9" value="<?php echo($d_start); ?>" maxlength="8">
                        ～
                        <input type="text" name="d_end" size="9" value="<?php echo($d_end); ?>" maxlength="8">
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製品番号の指定
                        (指定しない場合は空白)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='assy_no' size='11' value='<?= $assy_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' width='400'>
                        売上区分=
                        １：完成 ２：個別(通常) ３：手打 ４：調整 ５：移動 ６：直納 ７：売上 
                        ８：自動振替 ９：部品受注
                    </td>
                    <td class='winbox' align='center'>
                        <select name="kubun">
                            <option value=" "<?php if($kubun==" ") echo("selected"); ?>>全て</option>
                            <option value="1"<?php if($kubun=="1") echo("selected"); ?>>1完成</option>
                            <option value="2"<?php if($kubun=="2") echo("selected"); ?>>2個別</option>
                            <option value="3"<?php if($kubun=="3") echo("selected"); ?>>3手打</option>
                            <option value="4"<?php if($kubun=="4") echo("selected"); ?>>4調整</option>
                            <option value="5"<?php if($kubun=="5") echo("selected"); ?>>5移動</option>
                            <option value="6"<?php if($kubun=="6") echo("selected"); ?>>6直納</option>
                            <option value="7"<?php if($kubun=="7") echo("selected"); ?>>7売上</option>
                            <option value="8"<?php if($kubun=="8") echo("selected"); ?>>8振替</option>
                            <option value="9"<?php if($kubun=="9") echo("selected"); ?>>9受注</option>
                        <select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        販売価格に対する仕切単価の率を指定して下さい。(例：52)<br>
                        <font color='red'>(カプラ特注の場合)</font> 指定した率 未満だと<font color='red'>赤色</font>で表示
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="uri_ritu" size="4" value="<?php echo("$uri_ritu"); ?>" maxlength="4">
                        ％ 未満
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        集計に使用する賃率を指定して下さい。
                    </td>
                    <td class='winbox' colspan='1'>
                        <input type='radio' name='rate' value='c' id='rateContract'<?php if($rate!='i')echo' checked' ?>><label for='rateContract'>契約</label>
                        <input type='radio' name='rate' value='i' id='rateInhouse'<?php if($rate=='i')echo' checked' ?>><label for='rateInhouse'>社内</label>
                    </td>
                </tr>
            <!-- 現在はコメント
                <input type='hidden' name='uri_ritu' value=''>
            -->
                <tr>
                    <td class='winbox' align='right'>
                        １ページの表示行数を指定して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="sales_page" size="4" value="<?php echo("$sales_page"); ?>" maxlength="3">
                        初期値：25
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type="submit" name="照会" value="実行" >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
