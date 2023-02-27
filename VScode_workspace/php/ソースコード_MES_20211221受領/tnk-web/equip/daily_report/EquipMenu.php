<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ�ε�����ž���� �ᥤ���˥塼                        //
// Copyright (C) 2004-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2004/07/15 Created  EquipMenu.php                                        //
// 2004/08/01 MenuHeader Class ���ɲ�                                       //
// 2004/09/27 �����̱�ž������б� ���å�����ѿ��ˤ�빩���ʬ�б�         //
// 2005/02/02 �ƽ����action̾�ȥ��ɥ쥹����                                //
// 2005/06/24 F2/F12��������뤿����б��� JavaScript�� set_focus()���ɲ�   //
// 2006/04/13 �إ�ץ�����ɥ��Υ������ѹ����ǽ�� resizable=yes            //
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
require_once ('../../function.php');        // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('com/define.php');
require_once ('com/function.php');

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0, EQUIP_MENU2);     // ǧ�ڥ�٥�=0, �꥿���󥢥ɥ쥹, �����ȥ�λ���ʤ�
access_log();                               // Script Name �ϼ�ư����

// ������ž����δ����Ը��¥桼��
$AccountAdmin = AdminUser( FNC_ACCOUNT );

////////////// ����������
$menu->set_site(40, 7);                // site_index=40(������˥塼2) site_id=7(������ž����)
////////////// �꥿���󥢥ɥ쥹���� (���󥹥����������˻��ꤷ�ʤ���Ф���������)
// $menu->set_RetUrl(EQUIP_MENU2);
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('������ž���󥵡���',   EQUIP2 . 'daily_report/business/ReportMain.php');
$menu->set_action('ü�����ɽ',   EQUIP2 . 'daily_report/business/Abandonment.php');
$menu->set_action('���ʥޥ�����',   EQUIP2 . 'daily_report/master/Parts.php');
$menu->set_action('�����ޥ�����',   EQUIP2 . 'daily_report/master/Materials.php');
$menu->set_action('���¥ޥ�����',   EQUIP2 . 'daily_report/master/Account/Account.php');

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
//////////// ɽ�������
if (isset($_SESSION['factory'])) $factory = $_SESSION['factory']; else $factory = '';
switch ($factory) {
case 1:
    $menu->set_title('���� ��ž ���� ���������ƥ� ������');
    $menu->set_caption('��ž���� ������ �ᥤ�� ��˥塼');
    break;
case 2:
    $menu->set_title('���� ��ž ���� ���������ƥ� ������');
    $menu->set_caption('��ž���� ������ �ᥤ�� ��˥塼');
    break;
case 3:
    $menu->set_title('���� ��ž ���� ���������ƥ� ������');
    $menu->set_caption('��ž���� ������ �ᥤ�� ��˥塼');
    break;
case 4:
    $menu->set_title('���� ��ž ���� ���������ƥ� ������');
    $menu->set_caption('��ž���� ������ �ᥤ�� ��˥塼');
    break;
case 5:
    $menu->set_title('���� ��ž ���� ���������ƥ� ������');
    $menu->set_caption('��ž���� ������ �ᥤ�� ��˥塼');
    break;
case 6:
    $menu->set_title('���� ��ž ���� ���������ƥ� ������');
    $menu->set_caption('��ž���� ������ �ᥤ�� ��˥塼');
    break;
case 7:
    $menu->set_title('���� ��ž ���� ���������ƥ� ������(���)');
    $menu->set_caption('��ž���� ������(���) �ᥤ�� ��˥塼');
    break;
case 8:
    $menu->set_title('���� ��ž ���� ���������ƥ� ������(SUS)');
    $menu->set_caption('��ž���� ������(SUS) �ᥤ�� ��˥塼');
    break;
default:
    $menu->set_title('���� ��ž ���� ���������ƥ� ������');
    $menu->set_caption('��ž���� ������ �ᥤ�� ��˥塼');
    break;
}

/////////// HTML Header ����Ϥ��ƥ���å��������
// $menu->out_html_header();    // �ʲ��ǹԤäƤ��뤿�ᥳ����
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Language" content="ja">
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="Expires" content="0">
<title><?= $menu->out_title() ?></title>
<script Language='JavaScript'>
<!--
function doSubmit(url) {
    document.MainForm.action = url;
    document.MainForm.submit();
}
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'help_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<style>
.TITLE {
        font-size : 24px;
        background-color : blue;
        color : white;
        text-align: center;
        width : 100%;
}
</style>
</head>
<body onLoad='set_focus()' link='#0000FF' vlink='#0000FF' style='overflow-y:hidden;'>
<form name='MainForm' method='post' action=''>
<input type='hidden' name='RetUrl' value='<?=$_SERVER{'PHP_SELF'}?>'>
</form>
<!--
<div class='TITLE'><?= $menu->out_title() ?></div>
<br>
-->
<center>
<?= $menu->out_title_border() ?>
        <table border='0'>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
    
    <table border='0' cellpadding='30'>
        <tr>
            <td align='center' valign='top'>
                <table border='1' bordercolor='#0000FF' bgcolor='#CCFFFF' cellpadding='10' cellspacing=='0' width='200'>
                    <tr>
                        <td>
                            ������ž�������<br>
                            <br>
                            <br>
                            <a href="JavaScript:doSubmit('<?=BUSINESS_PATH?>Report.php')">������ž����<br>
                            <a href="JavaScript:doSubmit('<?=BUSINESS_PATH?>Abandonment.php')">ü�����ɽ<br>
                            <br>
                            <input style='font-size:10pt; font-weight:bold; color:blue;' type='button' name='work_mnt_help' value='HELP' onClick='win_open("help/EquipMenu_help.html")'>
                        </td>
                     </tr>
                 </table>
             </td>
            <td align='center' valign='top'>
                <table border='1' bordercolor='#FF00FF' bgcolor='#FFCCFF' cellpadding='10' cellspacing=='0' width='200'>
                    <tr>
                        <td>
                            �ޥ���������<br>
                            <br>
                            <br>
                            <a href="JavaScript:doSubmit('<?=MASTER_PATH?>Parts.php')">���ʥޥ���<br>
                            <a href="JavaScript:doSubmit('<?=MASTER_PATH?>Materials.php')">�����ޥ���<br>
                            <br>
                            <?php if ($AccountAdmin) { ?>
                            <a href="JavaScript:doSubmit('<?=$menu->out_action('���¥ޥ�����')?>')">���¥ޥ���<br>
                            <?php } ?>
                        </td>
                     </tr>
                 </table>
             </td>
         <tr>
    </table>
</center>
</body>
</html>
