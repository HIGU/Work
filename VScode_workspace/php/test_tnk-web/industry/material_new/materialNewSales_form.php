<?php
//////////////////////////////////////////////////////////////////////////////
// 仕切単価影響額の照会 条件選択フォーム                                    //
// Copyright (C) 2010-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/05/13 Created   materialNewSales_form.php                           //
// 2019/09/24 Created   ツールの追加                                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
//$menu->set_site( 1, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////////// リターンアドレス設定
$menu->set_RetUrl('materialNew_menu.php');                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('仕切単価影響額の照会 条件設定');
//////////// 表題の設定
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('売上明細',   'materialNewSales_view.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

/////////////// 受け渡し変数の初期化
if ( isset($_SESSION['s_uri_passwd']) ) {
    $uri_passwd = $_SESSION['s_uri_passwd'];
} else {
    $uri_passwd = '';
}
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
    $kubun = '';
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

// $_SESSION['s_rec_No'] = 0;  // 表示用レコード№を0にする。

if ( isset($_SESSION['s_sales_page']) ) {   // １ページ表示行数設定
    $sales_page = $_SESSION['s_sales_page'];     // 常に Default 25 になるようにコメント解除
    // $sales_page = 25;             // Default 25
} else {
    $sales_page = 25;             // Default 25
}
if ( isset($_SESSION['target_ym']) ) {
    $target_ym = $_SESSION['target_ym'];
} else {
    $target_ym = '';      // 初期化
}

///// 対象部門コードのHTML <select> option の出力
function getTarget_ymValues($target_ym)
{
    $query = "
        SELECT
            cost_ym      AS 改定仕切年月
        FROM sales_price_new
        GROUP BY cost_ym
        ORDER BY cost_ym DESC
    ";
    $res = array();
    $rows = getResult2($query, $res);
    // 初期化
    $option = "\n";
    $all_rows = $rows;
    for ($i=0; $i<$rows; $i++) {
        if ($target_ym == $res[$i][0]) {
            $v_ym    = substr($res[$i][0],0,4) . '/' . substr($res[$i][0],4,2);
            $option .= "<option value='{$res[$i][0]}' selected>{$v_ym}</option>\n";
        } else {
            $v_ym    = substr($res[$i][0],0,4) . '/' . substr($res[$i][0],4,2);
            $option .= "<option value='{$res[$i][0]}'>{$v_ym}</option>\n";
        }
    }
    return $option;
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
<script type='text/javascript' src='materialNewSales_form.js?<?php echo $uniq ?>'>
</script>

<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
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
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
</style>
<body onLoad='document.uri_form.uri_passwd.focus(); document.uri_form.uri_passwd.select()' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <form name='uri_form' action='<?php echo $menu->out_action('売上明細')?>' method='post' onSubmit='return chk_sales_form(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        パスワードを入れて下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type='password' name='uri_passwd' size='12' value='<?php echo("$uri_passwd"); ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製品グループを選択して下さい<BR>(リニアのみにはリニア修理が含まれています)
                    </td>
                    <td class='winbox' align='center'>
                        <select name="div">
                            <!-- <option value=" "<?php if($div==" ") echo("selected"); ?>>全グループ</option> -->
                            <!-- <option value="C"<?php if($div=="C") echo("selected"); ?>>カプラ全体</option> -->
                            <!-- <option value="S"<?php if($div=="S") echo("selected"); ?>>カプラ特注</option> -->
                            <option value="D"<?php if($div=="D") echo("selected"); ?>>カプラ標準</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>リニア全体</option>
                            <option value="N"<?php if($div=="N") echo("selected"); ?>>リニアのみ</option>
                            <option value="B"<?php if($div=="B") echo("selected"); ?>>バイモルのみ</option>
                            <!-- <option value="SSC"<?php if($div=="SSC") echo("selected"); ?>>カプラ試修</option> -->
                            <!-- <option value="SSL"<?php if($div=="SSL") echo("selected"); ?>>リニア試修</option> -->
                            <!-- <option value="NKB"<?php if($div=="NKB") echo("selected"); ?>>商品管理</option> -->
                            <option value="T"<?php if($div=="T") echo("selected"); ?>>ツール</option>
                            <!-- <option value="_"<?php if($div=="_") echo("selected"); ?>>なし</option> -->
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
                    <td class='winbox' align='right'>
                        改定仕切年月を指定して下さい(必須)
                    </td>
                    <td class='winbox' align='center'>
                        <select name='target_ym' class='pt14b'>
                            <option value='年月選択' style='color:red;' selected>年月選択</option>
                            <?php echo getTarget_ymValues($target_ym) ?>
                        </select>
                    </td>
                <tr>
                    <td class='winbox' align='right'>
                        製品番号の指定
                        (指定しない場合は空白)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='assy_no' size='11' value='<?php echo $assy_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' width='400'>
                        売上区分=
                        １：完成 のみ
                    </td>
                    <td class='winbox' align='center'>
                        <select name="kubun">
                            <!-- <option value=" "<?php if($kubun==" ") echo("selected"); ?>>全て</option> -->
                            <option value="1"<?php if($kubun=="1") echo("selected"); ?>>1完成</option>
                            <!-- <option value="2"<?php if($kubun=="2") echo("selected"); ?>>2個別</option> -->
                            <!-- <option value="3"<?php if($kubun=="3") echo("selected"); ?>>3手打</option> -->
                            <!-- <option value="4"<?php if($kubun=="4") echo("selected"); ?>>4調整</option> -->
                            <!-- <option value="5"<?php if($kubun=="5") echo("selected"); ?>>5移動</option> -->
                            <!-- <option value="6"<?php if($kubun=="6") echo("selected"); ?>>6直納</option> -->
                            <!-- <option value="7"<?php if($kubun=="7") echo("selected"); ?>>7売上</option> -->
                            <!-- <option value="8"<?php if($kubun=="8") echo("selected"); ?>>8振替</option> -->
                            <!-- <option value="9"<?php if($kubun=="9") echo("selected"); ?>>9受注</option> -->
                        <select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        販売価格に対する仕切単価の率を指定して下さい。(例：52)<br>
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="uri_ritu" size="4" value="<?php echo("$uri_ritu"); ?>" maxlength="4">
                        ％ 未満
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
                        <input type='text' name='sales_page' size='4' value="<?php echo("$sales_page"); ?>" maxlength='4'>
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
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
