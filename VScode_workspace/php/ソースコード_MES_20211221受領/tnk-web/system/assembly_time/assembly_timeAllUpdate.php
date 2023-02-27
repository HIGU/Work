<?php
//////////////////////////////////////////////////////////////////////////////
// AS/400¤ÎÁÈÎ©¹©¿ô¤òDB¥µ¡¼¥Ð¡¼¤Ø¹¹¿· ¥Ø¥Ã¥À¡¼¡¦ÌÀºÙ¡¦¥Þ¥¹¥¿¡¼¤Î£³¥Õ¥¡¥¤¥ë  //
//   ÅÐÏ¿¹©¿ô¤ÎÏ¢·È                                                         //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/07 Created  assembly_timeAllUpdate.php                           //
// 2007/05/16 ¥Ç¥£¥ì¥¯¥È¥êÊÑ¹¹ daily/ ¢ª assembly_time/ ¥í¥¸¥Ã¥¯¤Çdir¼èÆÀ   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ÍÑ
ini_set('implicit_flush', '0');             // echo print ¤Ç flush ¤µ¤»¤Ê¤¤(ÃÙ¤¯¤Ê¤ë¤¿¤á)
ini_set('max_execution_time', 60);          // ºÇÂç¼Â¹Ô»þ´Ö = 60ÉÃ 
require_once ('../../function.php');        // define.php ¤È pgsql.php ¤ò require_once ¤·¤Æ¤¤¤ë
require_once ('../../MenuHeader.php');      // TNK Á´¶¦ÄÌ menu class
access_log();                               // Script Name ¤Ï¼«Æ°¼èÆÀ

$currentFullPathName = realpath(dirname(__FILE__));

///// TNK ¶¦ÍÑ¥á¥Ë¥å¡¼¥¯¥é¥¹¤Î¥¤¥ó¥¹¥¿¥ó¥¹¤òºîÀ®
$menu = new MenuHeader(3);                  // Ç§¾Ú¥Á¥§¥Ã¥¯3=administrator°Ê¾å Ìá¤êÀè=TOP_MENU ¥¿¥¤¥È¥ëÌ¤ÀßÄê

//////////// ¥¿¥¤¥È¥ëÌ¾(¥½¡¼¥¹¤Î¥¿¥¤¥È¥ëÌ¾¤È¥Õ¥©¡¼¥à¤Î¥¿¥¤¥È¥ëÌ¾)
$menu->set_title('AS/400¢ªDB¥µ¡¼¥Ð¡¼ ÁÈÎ© ÅÐÏ¿¹©¿ô ¹¹¿·½èÍý¼Â¹Ô');
//////////// ¥ê¥¿¡¼¥ó¥¢¥É¥ì¥¹ÀßÄê(ÀäÂÐ»ØÄê¤¹¤ë¾ì¹ç)
$menu->set_RetUrl(SYS_MENU);                // ÄÌ¾ï¤Ï»ØÄê¤¹¤ëÉ¬Í×¤Ï¤Ê¤¤

$Message  = "¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²\n\n";

/******** ÁÈÎ©¹©¿ô ¹©Äøµ­¹æ¥Þ¥¹¥¿¡¼¤Î¹¹¿· *********/
$Message .= `{$currentFullPathName}/assembly_process_master.php`;
$Message .= "¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²\n\n";

/******** ÁÈÎ©¹©¿ô ¹©ÄøÌÀºÙ¤Î¹¹¿· *********/
$Message .= `{$currentFullPathName}/assembly_standard_time.php`;
$Message .= "¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²\n\n";

/******** ÁÈÎ©¹©¿ô ¥Ø¥Ã¥À¡¼¥Õ¥¡¥¤¥ë¤Î¹¹¿· *********/
$Message .= `{$currentFullPathName}/assembly_time_header.php`;
$Message .= "¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²¡²\n";

///// alert()½ÐÎÏÍÑ¤Ë¥á¥Ã¥»¡¼¥¸¤òÊÑ´¹
$Message = str_replace("\n", '\\n', $Message);  // "\n"¤ËÃí°Õ

/////////// HTML Header ¤ò½ÐÎÏ¤·¤Æ¥­¥ã¥Ã¥·¥å¤òÀ©¸æ
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<script type='text/javascript'>
function resultMessage()
{
    alert("<?php echo $Message ?>");
    location.replace("<?php echo SYS_MENU ?>");
}
</script>
<body   onLoad='
            resultMessage();
        '
</body>
<html>
