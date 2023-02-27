<?php
//////////////////////////////////////////////////////////////////////////////
// �ϽС��������˥塼�ʷ�����                                             //
// Copyright(C) 2014-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2014/09/19 Created  act_appli_menu.php                                   //
// 2014/09/22 caption�����Ƥ��ѹ�                                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');               // ���ϥХåե���gzip����

require_once ('../function.php');       // TNK ������ function
require_once ('../MenuHeader.php');     // TNK ������ menu class
require_once ('../tnk_func.php');
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);    // ǧ�ڥ�٥�=0, �꥿���󥢥ɥ쥹, �����ȥ�λ���ʤ�

////////////// ����������
$menu->set_site(98, 999);                // site_index=4(�ץ���೫ȯ) site_id=999(�ҥ�˥塼����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);            // ������ꤷ�Ƥ���
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ϽС��������˥塼�ʼҳ���');
//////////// ɽ�������
$menu->set_caption('�ҳ��ؤγƼ��Ͻ��ѻ���������ɽ���ޤ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����

// ���⡦�ҳ��̥�˥塼
$menu->set_action('�ͻ��˴ؤ����Ͻ�',    PER_APPLI . 'out_personnel_appli/out_personnel_appli_menu.php');
$menu->set_action('�����˴ؤ����Ͻ�',      PER_APPLI . 'out_account_appli/out_account_appli_menu.php');
$menu->set_action('��̳�˴ؤ����Ͻ�',  PER_APPLI . 'out_affairs_appli/out_affairs_appli_menu.php');
$menu->set_action('�����˴ؤ����Ͻ�',  PER_APPLI . 'out_repair_appli/out_repair_appli_menu.php');
$menu->set_action('����¾���Ͻ�',  PER_APPLI . 'out_other_appli/out_other_appli_menu.php');

// �ʲ��ϵ��˥塼
// �����ط�
$menu->set_action('��ĥ�˴ؤ����Ͻ�',    ACT_APPLI . 'trip_appli/trip_appli_menu.php');
// ����¾
$menu->set_action('����˴ؤ����Ͻ�',      ACT_APPLI . 'child_care_appli/child_care_appli_menu.php');
$menu->set_action('���˴ؤ����Ͻ�',  ACT_APPLI . 'nurse_appli/nurse_appli_menu.php');
$menu->set_action('��̳���Ѥ��˴ؤ����Ͻ�',  ACT_APPLI . 'transfer_appli/transfer_appli_menu.php');
$menu->set_action('ͻ�����դ˴ؤ����Ͻ�',  ACT_APPLI . 'loan_appli/loan_appli_menu.php');


/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=euc-jp">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<style type="text/css">
<!--
.test_font {
    font-size: 12pt;
    font-weight: bold;
    font-family: monospace;
}
-->
</style>
<?= $menu->out_css() ?>
<script type='text/javascript'>
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>
<body style='overflow:hidden;' onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <table border='0'>
            <tr>
                <td>
                    <p><img src='<?php echo IMG ?>t_nitto_logo3.gif' width='348' height='83'></p>
                </td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        <table border='0'>
            <tr>
                <td align='center'>
                    <img src='<?php echo IMG ?>tnk-turbine.gif' width='68' height='72'>
                </td>
            </tr>
        </table>
        <table width='80%' border='0'>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('��ĥ�˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='��ĥ�˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/trip_appli_menu.png","��ĥ�˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('����˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='����˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/child_care_appli_menu.png","����˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('���˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='���˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/nurse_appli_menu.png","���˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('��̳���Ѥ��˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='��̳���Ѥ��˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/transfer_appli_menu.png","��̳���Ѥ��˴ؤ����Ͻ�",12)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('ͻ�����դ˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='ͻ�����դ˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/loan_appli_menu.png","ͻ�����դ˴ؤ����Ͻ�",12)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
            </tr>
        </table>
    </center>
</body>
<?= $menu->out_site_java() ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
