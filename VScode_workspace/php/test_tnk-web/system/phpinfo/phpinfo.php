<?php
//////////////////////////////////////////////////////////////////////////////
// PHP インフォメーション システム情報                                      //
// Copyright(C) 2001-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2001/10/01 新規作成 phpinfo.php                                          //
// 2002/12/03 access_log と権限の追加（サイトメニューに入れたため）         //
// 2004/05/27 phpinfo(options) オプションパラメータ追加と補足説明を追加     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);  // E_ALL='2047' debug 用
ini_set('display_errors', '1');     // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');
access_log();                       // Script Name 自動設定

///////////// サイトメニューの設定
$_SESSION['site_index'] = 99;       // 最後のメニューにするため 99 を使用
$_SESSION['site_id']    = 51;       // とりあえず下位メニュー無し (0 < であり)


header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>PHPINFO</title>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt14 {
    font-size:14pt;
}
.pt12b {
    font:bold 12pt;
}
.margin1 {
    margin: 1%;
}
-->
</style>
</head>
<body class='margin1'>
<table align='center' with=100% border='3' cellspacing='0' cellpadding='0'>
    <form action='system_menu.php' method='post'>
        <td><input class='pt12b' type="submit" name="free_chk" value="戻る" ></td>
    </form>
</table>

<?php
    phpinfo();    // Default
    /*******************************************************************************************************
    以下にあるconstantsビット値をひとつまたは 複数個を加算して、オプションのwhat引数に渡すことによって
        出力をカスタマイズできます。 それぞれの定数やビット値をor演算子で結んで渡すこともできます。
    phpinfo() options
    名前(定数)         値 説明 
    INFO_GENERAL        1 The configuration line, php.ini location, build date, Web Server, System and more.  
    INFO_CREDITS        2 PHP 4 Credits. See also phpcredits().  
    INFO_CONFIGURATION  4 Current Local and Master values for php directives. See also ini_get().  
    INFO_MODULES        8 Loaded modules and their respective settings. See also get_loaded_modules().  
    INFO_ENVIRONMENT   16 Environment Variable information that's also available in $_ENV.  
    INFO_VARIABLES     32 Shows all predefined variables from EGPCS (Environment, GET, POST, Cookie, Server).  
    INFO_LICENSE       64 PHP License information. See also the license faq.  
    INFO_ALL           -1 Shows all of the above. This is the default value.  
    
    <example>
    phpinfo(32);        EGPCS順の変数と値の表示 (debug用)
    phpinfo(32 | 64);   EGPCS順の変数と値の表示 と ライセンス表示
    phpinfo(3);         1+2=3 GENERAL と CREDITS を表示
    *******************************************************************************************************/
?>

</body>
</html>