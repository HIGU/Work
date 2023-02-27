<?php
//////////////////////////////////////////////////////////////////////////////
// ��¤���������˥塼                                                     //
// Copyright(C) 2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2011/09/28 Created  press_tool_menu.php                                  //
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
$menu->set_site(82, 999);                // site_index=4(�ץ���೫ȯ) site_id=999(�ҥ�˥塼����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);            // ������ꤷ�Ƥ���
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��¤���������˥塼');
//////////// ɽ�������
$menu->set_caption('��¤������� ��˥塼');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��������',    ASSET . 'press_tool/pressTool_input_Main.php');
$menu->set_action('���پȲ�',    ASSET . 'press_tool/pressTool_details_view_Main.php');
$menu->set_action('���׾Ȳ�',    ASSET . 'press_tool/pressTool_total_view_Main.php');
$menu->set_action('�ǡ�������',    ASSET . 'press_tool/pressTool_total_create_Main.php');
$menu->set_action('����ޥ�����',    ASSET . 'press_tool/pressTool_master_Main.php');
$menu->set_action('�����ޥ�����',    ASSET . 'press_tool/pressTool_machine_master_Main.php');

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
                    <form method="post" action="<?= $menu->out_action('��������') ?>">
                        <input type='image' alt='��¤����ι���/���Ѳ���' border=0 src='<?php echo menu_bar("../menu_tmp/press_tool_input.png","��¤�������/����",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('���پȲ�') ?>">
                        <input type='image' alt='��¤����߸����٤ξȲ�' border=0 src='<?php echo menu_bar("../menu_tmp/press_tool_details_view.png","��¤����߸����پȲ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="">
                        <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('���׾Ȳ�') ?>">
                        <input type='image' alt='��¤����߸˽��פξȲ�' border=0 src='<?php echo menu_bar("../menu_tmp/press_tool_total_view.png","��¤����߸˽��׾Ȳ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�ǡ�������') ?>">
                        <input type='image' alt='��¤����κ߸˽��ץǡ�������Ͽ���ޤ�' border=0 src='<?php echo menu_bar("../menu_tmp/press_tool_total_create.png","�߸˽��ץǡ�������",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="">
                        <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('����ޥ�����') ?>">
                        <input type='image' alt='��¤����Υޥ�������Ͽ' border=0 src='<?php echo menu_bar("../menu_tmp/press_tool_master_input.png","��¤����ޥ�������Ͽ",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�����ޥ�����') ?>">
                        <input type='image' alt='��¤�������Ѥ��뵡���Υޥ�������Ͽ' border=0 src='<?php echo menu_bar("../menu_tmp/machine_master_input.png","�����ޥ�������Ͽ",13)."?".uniqid("menu") ?>'>
                    </form>
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
