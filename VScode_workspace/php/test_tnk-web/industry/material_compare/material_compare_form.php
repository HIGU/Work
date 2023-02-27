<?php
//////////////////////////////////////////////////////////////////////////////
// 新旧総材料費 差額の照会 条件選択フォーム                                 //
// Copyright(C) 2011      Noriisa.Ohya norihisa_ooya@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2011/05/16 Created   material_compare_form.php                           //
// 2011/05/17 表示順序に掛率順を追加                                        //
// 2011/05/26 年月の初期値を空欄に変更、製品グループ順を追加                //
// 2011/05/30 総材料費の比較を別メニューにまとめた為require_onceのリンク変更//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
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
$menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=21(総材料費の登録 計画番号)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('新旧総材料費 差額明細照会');
//////////// 表題の設定
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費明細',   INDUST . 'material_compare/material_compare_view.php');

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
if ( isset($_SESSION['s_first_ym']) ) {
    $first_ym = $_SESSION['s_first_ym'];
} else {
    if ( isset($_POST['first_ym']) ) {
        $first_ym = $_POST['first_ym'];
    } else {
        //$first_ym = date_offset(1);
        $first_ym = '';
    }
}
if ( isset($_SESSION['s_second_ym']) ) {
    $second_ym = $_SESSION['s_second_ym'];
} else {
    if ( isset($_POST['second_ym']) ) {
        $second_ym = $_POST['second_ym'];
    } else {
        //$second_ym = date_offset(1);
        $second_ym = '';
    }
}
if ( isset($_SESSION['uri_assy_no']) ) {
    $assy_no = $_SESSION['uri_assy_no'];
} else {
    $assy_no = '';      // 初期化
}
if ( isset($_SESSION['s_order']) ) {
    $order = $_SESSION['s_order'];
} else {
    $order = 'assy';      // 初期化
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
<script type='text/javascript' src='./material_compare_form.js?<?php echo $uniq ?>'>
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
        
        <form name='uri_form' action='<?php echo $menu->out_action('総材料費明細')?>' method='post' onSubmit='return chk_sales_form(this)'>
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
                        製品グループを選択して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <select name="div">
                            <option value="C"<?php if($div=="C") echo("selected"); ?>>カプラ</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>リニア</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        比較する日付を指定して下さい(必須)
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="first_ym" size="7" value="<?php echo($first_ym); ?>" maxlength="6">
                        と
                        <input type="text" name="second_ym" size="7" value="<?php echo($second_ym); ?>" maxlength="6">
                    </td>
                </tr>
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
                    <td class='winbox' align='right'>
                        表示順序を指定してください。
                    </td>
                    <td class='winbox' align='center'>
                        <select name="order">
                            <option value="assy"<?php if($order=="assy") echo("selected"); ?>>製品番号順</option>
                            <option value="diff"<?php if($order=="diff") echo("selected"); ?>>増減額順</option>
                            <option value="per"<?php if($order=="per") echo("selected"); ?>>増減率順</option>
                            <option value="power"<?php if($order=="power") echo("selected"); ?>>掛率順</option>
                            <option value="sorder"<?php if($order=="sorder") echo("selected"); ?>>製品グループ順</option>
                        </select>
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
