<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 認証フォーム authenticate.php                               //
// Copyright (C) 2001-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  index1.php --> authenticate.php                      //
// 2002/08/01 セッション管理を追加 & register_globals off 対応              //
// 2002/08/07 下のheader の後に exit() を追加                               //
//                           s_sysmsg = "" まで実行してしまうため           //
// 2002/09/20 社員№は６桁全て入力 & right view の説明文を変更              //
// 2003/02/20 起動時に社員№欄に select するように変更 focus() は NG        //
//          login 後にも Window が前面に出てしまうため next_focus()で対応   //
// 2003/02/26 文字サイズをブラウザーで変更できなくした title-font 等        //
// 2003/12/15 ob_start('ob_gzhandler') を追加 confirm.js→login.jsへ変更    //
// 2004/01/28 [社員No]を６桁未満なら自動０詰するように変更したため          //
//            6桁全て入力 → 頭の0は省略可能 及び style sheetの誤り修正     //
// 2004/02/02 index1.php → authenticate.php へ名前を変更                   //
// 2004/03/10 クライアントのクッキー無効の対策ロジックを追加                //
// 2005/01/14 登録されていない場合セッションにs_sysmsgを登録する            //
// 2005/01/24 デザインを一新(ブラインドイメージへ)旧をauthenticate_bak1.php //
//            useridでonChange='next_focus()'やめてREQUESTで取得して制御する//
//            onLoad='window.blur(); と ini_focus(); の順番を守ること       //
// 2005/09/21 E_ALL → E_STRICT     bodyにonFocus='ini_focus()'を追加       //
// 2005/10/13 パスワードのフォントをauto_font → pass_font へ変更           //
// 2005/11/24 <link rel='shortcut icon' href='/favicon.ico'>追加            //
// 2006/07/07 ショートカットタグとJSP/ASPタグを標準タグへ変更               //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047(php4) debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047(php4) debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('function.php');              // 共通ファンクッション
access_log();                               // Script Name は自動取得
if ( !isset($_SESSION['r_addr']) ) {        // URLにauthenticate.phpをダイレクトに指定した場合の対策。
    header('Location: http:' . WEB_HOST . 'index.php?' . SID);  // SIDの付加はクッキー無効の対策
    exit();
}
if ( isset($_SESSION['s_sysmsg']) ) {
    $sysmsg = $_SESSION['s_sysmsg'];
    $_SESSION['s_sysmsg'] = '';
} else {
    if (isset($_GET['PHPSESSID'])) {
        $sysmsg = "<font color='yellow'>クッキーが無効になっています。有効にしてからログインして下さい。</font>";
    } else {
        $sysmsg = '';
        $_SESSION['s_sysmsg'] = '';         // セッションにs_sysmsgを登録する
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/php;charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>栃木日東工器</title>
<script language='JavaScript' src='<?php echo ROOT ?>login.js'>
</script>
<script language='JavaScript'>
<!--
function ini_focus(){
    <?php if (!isset($_REQUEST['background'])) { ?>
    document.login_form.userid.focus();
    <?php } ?>
    document.login_form.userid.select();
}
function next_focus(){
    document.login_form.passwd.focus();
    document.login_form.passwd.select();
    //  onChange='next_focus()'
}
//-->
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

<body onLoad='<?php if (isset($_REQUEST['background'])) echo 'window.blur(); ' ?>ini_focus();' onFocus='ini_focus()'>
    <center>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='10' width='400' height='300'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' height='100%' border='0'>
            <tr>
                <td align='center' colspan='2'>
                    <img src='<?php echo IMG ?>t_nitto_logo3.gif' border=0>
                </td>
            </tr>
            <form name='login_form' method='post' action='login.php' onSubmit='return inpConf(this)'>
            <tr>
                <td width='40%' align='right'>
                    <font class='auth_font'>社員No.</font>
                </td>
                <td align='left' class='auth_font'>
                    <?php if (!isset($_REQUEST['background'])) { ?>
                    <input class='auth_font' type='text' name='userid' size='6' maxlength='6' tabindex='2'>
                    <?php } else { ?>
                    <input class='auth_font' type='text' name='userid' size='6' maxlength='6' tabindex='2' onChange='next_focus()'>
                    <?php } ?>
                    <br>
                    頭の0は省略可能
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
                    <input type='submit' value='ログイン' tabindex='4' onFocus='ini_focus()'>
                </td>
            </form>
            </tr>
            <tr>
                <td colspan='2'>
                    <font class='sysmsg_title' tabindex='1' onFocus='ini_focus()'>[ システムメッセージ ]</font><br>
                    <font class='sysmsg_body'><?php echo $sysmsg ?></font>
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
