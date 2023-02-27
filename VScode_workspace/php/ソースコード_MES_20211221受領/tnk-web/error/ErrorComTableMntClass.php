<?php
//////////////////////////////////////////////////////////////////////////////
// EquipGraph Class��ǤΥ��顼�����ڡ��� status �ˤ�äƾ��ʬ��           //
// Copyright(C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed History                                                          //
// 2005/07/12 Created   ErrorComTableMntClass.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // TNK ������ function
require_once ('../MenuHeader.php');     // TNK ������ menu class
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0, TOP_MENU);    // ǧ�ڥ����å�1=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(0, 0);                  // site_index=0(̤����) site_id=0(̤����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ComTableMntClass��Class �������顼');

$status = $_REQUEST['status'];
//////////// ɽ�������
switch ($status) {
case 1:
    $menu->set_caption("���ե�����򥪡��ץ�Ǥ��ޤ���");
    break;
case 2:
    $menu->set_caption($_SESSION['s_sysmsg']);
    break;
case 3:
case 4:
default:
    $menu->set_caption('ComTableMntClass ��� ����¾�Υ��顼������ޤ�����<br>����ô���Ԥ�Ϣ���Ʋ�������');
}
/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
}
.pt10 {
    font-size:      10pt;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table align='center' width ='80%' height='30%' border='0'>
            <tr>
                <td valign='middle' align='center' class='caption_font'>
                    <hr>
                    <?= $menu->out_caption(), "\n" ?>
                    <hr>
                    <form action='<?= $menu->out_RetUrl() ?>' method='post' name='error_ret_form'>
                        <input type='submit' name='error_ret' value='���' class='ret_font'>
                    </form>
                </td>
            </tr>
        </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
