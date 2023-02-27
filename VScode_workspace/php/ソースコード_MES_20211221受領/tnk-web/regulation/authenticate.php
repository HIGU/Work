<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 規程メニュー専用 認証フォーム authenticate.php              //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/17 Created  regulation/authenticate.php                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047(php4) debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047(php4) debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共通ファンクッション
access_log();                               // Script Name は自動取得

if ( !isset($_SESSION['r_addr']) ) {        // URLにauthenticate.phpをダイレクトに指定した場合の対策。
    header('Location: http:' . WEB_HOST . 'regulation/index.php?' . SID);  // SIDの付加はクッキー無効の対策
    exit();
}
if ( isset($_SESSION['s_sysmsg']) ) {
    $sysmsg = $_SESSION['s_sysmsg'];
    $_SESSION['s_sysmsg'] = '';
} else {
    if (isset($_GET['PHPSESSID'])) {
        $sysmsg = "<span style='color:yellow;'>クッキーが無効になっています。有効にしてからログインして下さい。</span>";
    } else {
        $sysmsg = '';
        $_SESSION['s_sysmsg'] = '';         // セッションにs_sysmsgを登録する
    }
}
///// ログインチェック
if (isset($_REQUEST['userid']) && isset($_REQUEST['passwd'])) {
    $_SESSION['REGU_login_time'] = Date('m-d H:i');
    $_SESSION['REGU_User_ID']    = strtoupper(mb_convert_kana($_REQUEST['userid'], 'r'));
    $_SESSION['REGU_Password']   = md5($_REQUEST['passwd']);
    $_SESSION['REGU_web_file']   = $_SERVER['SCRIPT_NAME'];
    
    if ($_SESSION['REGU_User_ID'] == 'NKB' && $_SESSION['REGU_Password'] == '182b93aad1e00a1243e28523543d9a8d') {
        $_SESSION['REGU_Auth'] = 0;
        header('Location: http:' . WEB_HOST . 'regulation/regulation_menu.php');
    } else {
        $_SESSION['s_sysmsg'] = '認証に失敗しました。入力ミスです。';
        header('Location: http:' . WEB_HOST . 'regulation/authenticate.php');
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/php;charset=euc-jp">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>規程メニュー</title>
<script type='text/javascript' language='JavaScript' src='authenticate.js?=<?php echo time() ?>'>
</script>
<style type="text/css">
<!--
body {
    margin:         10%;
}
form {
    margin:         0%;
}
.auth_font {
    font-size:      14.0pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          blue;
}
.pass_font {
    font-size:      11.0pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          blue;
}
.sysmsg_title {
    font-size:      8.7pt;
    font-weight:    normal;
    color:          #000000;
}
.sysmsg_body {
    font-size:      11.0pt;
    font-weight:    bold;
    font-family:    monospace;
    /* color:          #ff7e50; */
    color:          teal;
}
.winbox_field {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:    #d6d3ce; */
    background-image:       url(<?php echo IMG ?>blind_silver.gif);
    background-repeat:      repeat;
}
-->
</style>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo time() ?>'>
</head>

<body onLoad='ini_focus();' onFocus='ini_focus()'>
    <center>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='10' width='400' height='300'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' height='100%' border='0'>
            <tr>
                <td align='center' colspan='2'>
                    <img src='<?=IMG?>t_nitto_logo3.gif' border=0>
                </td>
            </tr>
            <tr>
                <td align='center' colspan='2' class='auth_font'>
                    規程メニュー専用 認証フォーム
                </td>
            </tr>
            <form name='login_form' method='get' action='authenticate.php' onSubmit='return inpConf(this)'>
            <tr>
                <td width='40%' align='right'>
                    <font class='auth_font'>ユーザーID</font>
                </td>
                <td align='left' class='auth_font'>
                    <input class='auth_font' type='text' name='userid' size='6' maxlength='6' tabindex='2' onChange='next_focus()'>
                </td>
            </tr>
            <tr>
                <td width='40%' align='right'>
                    <font class='auth_font'>パスワード</font>
                </td>
                <td align='left'>
                    <input class='pass_font' type='password' name='passwd' size='15' maxlength='8' tabindex='3'>
                </td>
            </tr>
            <tr>
                <td colspan='2' align='center'>
                    <input type='submit' name='log_in' value='ログイン' tabindex='4' onFocus='ini_focus()'>
                </td>
            </form>
            </tr>
            <tr>
                <td colspan='2'>
                    <font class='sysmsg_title' tabindex='1' onFocus='ini_focus()'>[ システムメッセージ ]</font><br>
                    <font class='sysmsg_body'><?=$sysmsg?></font>
                    <noscript>
                        <font class='sysmsg_body' color='#ff7e00'>JavaScriptが無効になっています。有効にしてからログインして下さい。</font>
                    </noscript>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?php
if ($sysmsg != '') {
    echo "<script language='JavaScript'>\n";
    echo "<!--\n";
    echo "var count = 0;\n";
    echo "var ax = new Array(-6,-0, 6, 0);\n";
    echo "var ay = new Array(0, -6, 0, 6);\n";
    echo "function shake ( ) {\n";
    echo "    window.moveBy(ax[count % 4], ay[count % 4]);\n";
    echo "    count++;\n";
    echo "    if (count < 40) setTimeout('shake();', 10);\n";
    echo "}\n";
    echo "shake();\n";
    echo "// -->\n";
    echo "</script>\n";
}
?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
