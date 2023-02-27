<?php 
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ��ü���������                   Client interface �� //
//                                                  MVC View �� Header ��   //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   AbandonmentSearch.php                               //
// 2006/04/12 MenuHeader ���饹�б�                                         //
// 2006/04/17 = �� php echo ���ѹ�                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../../../function.php');     // access_log()���ǻ���
require_once ('../com/define.php');
require_once ('../com/function.php');
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ü���������');
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����_parent��ɬ��

// �����ԥ⡼��
$AdminUser = AdminUser( FNC_REPORT );
// ���̥إå�����
SetHttpHeader();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeaderOnly.php'); ?>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<LINK rel='stylesheet' href='<?php echo CONTEXT_PATH?>com/cssConversion.css' type='text/css'>
<SCRIPT language='JavaScript' SRC='<?php echo SEARCH_JS?>'></SCRIPT>
<Script Language='JavaScript'>
<?php if ($_REQUEST['RetUrl'] != '') { ?>
function doBack() {
    document.MainForm.action = '<?=$_REQUEST['RetUrl']?>';
    document.MainForm.target = '_parent';
    document.MainForm.submit();
}
<?php } ?>
function ViewList() {
    document.MainForm.ProcCode.value = 'VIEW';
    document.MainForm.action = 'AbandonmentList.php';
    document.MainForm.submit();
}
</Script>
</head>
<body style='overflow-y:hidden;'>
<center>
<?php echo $menu->out_title_border() ?>

<form name='MainForm' method='post' target='ListFream'>
<input type='hidden' name='ProcCode' value=''>
    <table class='Conversion' border='1'>
        <tr class='Conversion'>
            <td style='width:100;' class='HED Conversion'>����������</td>
            <td style='width:500;' colspan='3' class='Conversion'>
                <input type='button' value='����' onClick='SearchMaterials(FromCode)'>
                <input type='text' name='FromCode' value='' size='7'class='CODE'> ��
                <input type='button' value='����' onClick='SearchMaterials(ToCode)'>
                <input type='text' name='ToCode'   value='' size='7'class='CODE'>
            </td>
        </tr>
        <tr class='Conversion'>
            <td style='width:100;' class='HED Conversion'>������</td>
            <td style='width:300;' class='Conversion'>
                <input type='radio' name='Type' value='A' ID='ALL' disabled><label for='ALL'>ξ��</label>
                <input type='radio' name='Type' value='B' ID='BAR' checked><label for='BAR'>�С���</label>
                <input type='radio' name='Type' value='C' ID='CUT' disabled><label for='CUT'>���Ǻ�</label>
            </td>
            <td style='width:100;' class='HED Conversion'>ɽ����</td>
            <td style='width:100;' align='center' class='Conversion'>
                <select name='ListNum'><?=SelectPageListNumOptions()?></select>
            </td>
        </tr>
    </table>
    <br>
    <input type='button' value='����ɽ��' style='width:80;' onClick='ViewList()'>
    <?php if ($_REQUEST['RetUrl'] != '') { ?>
    <input type='button' value='�ᡡ��' style='width:80;' onClick='doBack()'>
    <?php } ?>
</form>
</center>
</body>
</html>
<?php ob_end_flush(); ?>
