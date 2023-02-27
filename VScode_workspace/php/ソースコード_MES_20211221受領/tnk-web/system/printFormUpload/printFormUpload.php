<?php
//////////////////////////////////////////////////////////////////////////////
// OpenOffice Draw �ǽ��Ϥ���SVG�ե������UPLOAD���ƥ�ץ졼�Ȥ����        //
// �ƥ�ץ졼�ȥ��󥸥��simplate, ���饤����Ȱ�����PXDoc �����           //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/06 Created  printFormUpload.php                                  //
//////////////////////////////////////////////////////////////////////////////
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_cache_limiter('public');            // PXDoc����Ѥ�����Τ��ޤ��ʤ�(���ϥե�����򥭥�å��夵����)
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
// access_log();                               // Script Name �ϼ�ư����
define('START_TIME', microtime(true));

//////////// �ꥯ�����ȤΥ��󥹥��󥹤����
$request = new Request();
///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�����ե�����(SVG)�򥢥åץ��ɤ��ƥƥ�ץ졼�Ȥ˥���С��Ƚ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('svgUpload', '/pxd/svgUpload.php');
$menu->set_action('verUP',     '/pxd/downloadPXDoc.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>

<!-- JavaScript�Υե���������body�κǸ�ˤ��롣 HTML�����Υ����Ȥ�����Ҥ˽���ʤ��������  -->
<script type='text/javascript' src='/pxd/checkPXD.js?<?php echo $uniq ?>'></script>

<!-- �������륷���ȤΥե��������򥳥��� HTML�����Υ����Ȥ�����Ҥ˽���ʤ��������  -->
<!-- <link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'> -->

<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>

<style type='text/css'><!-- --></style>
</head>

<script type='text/javascript'>
function checkTemplateFile(obj)
{
    if (obj.svgFile.value) {
        return true;
    } else {
        alert('SVG(�������顼�֥롦�٥�����������ե��å���)�ե����뤬���ꤵ��Ƥ��ޤ���');
        return false;
    }
}
</script>
<body style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        <br><br>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form enctype='multipart/form-data' method='post' name='svgUploadForm' action='<?php echo $menu->out_action('svgUpload')?>' onSubmit='return checkTemplateFile(this)'>
            <tr>
                <td class='winbox' nowrap colspan='1' align='left'>
                    <input type='hidden' name='MAX_FILE_SIZE' value='1000000' />
                    Scalable Vector Graphics (SVG) �� �ƥ�ץ졼�ȥե�����<br>
                    <input type='file' name='svgFile' size='60' maxlength='256' />
                    <input type='submit' name='svgUPload' style='width:110px; font-size:0.9em; font-weight:bold;' value='����С��ȼ¹�' />
                </td>
            </tr>
            </form>
            <tr>
                <td class='winbox' nowrap colspan='1' align='center'>
                    <input type='button' name='verUP' style='width:210px;' value='�����ץ����ΥС�����󥢥å�' onClick='window.open("<?php echo $menu->out_action('verUP')?>", "down_win", "width=1,height=1");'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
