<?php
//////////////////////////////////////////////////////////////////////////////
// �����ٱ��Ϣ ��˥塼                                                    //
// Copyright(C) 2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2011/12/21 Created  product_support_menu.php                             //
// 2011/12/27 ʿ�ФΤߤλ�����Ȳ���ɲ�                                    //
//            ���̺߸˶�ۤξȲ���ɲ�                                      //
// 2011/12/28 ������Ȳ�Ⱥ߸˶�۾Ȳ��ɽ��������                        //
///////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');               // ���ϥХåե���gzip����

require_once ('../../function.php');       // TNK ������ function
require_once ('../../MenuHeader.php');     // TNK ������ menu class
require_once ('../../tnk_func.php');
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);    // ǧ�ڥ�٥�=0, �꥿���󥢥ɥ쥹, �����ȥ�λ���ʤ�

////////////// ����������
$menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(�����ȥ�˥塼�򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);            // ������ꤷ�Ƥ���
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�����ٱ��Ϣ ��˥塼');
//////////// ɽ�������
$menu->set_caption('�����ٱ��Ϣ ��˥塼');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('ʿ�л�����Ȳ�(����)', INDUST . 'product_support/hiraide_act_payable_form.php');
$menu->set_action('ʿ��ê����Ȳ�(����)', INDUST . 'product_support/hiraide_invent_form.php');
$menu->set_action('ʿ��ê���ѥǡ����Ȳ�', INDUST . 'product_support/hiraide_stocktaking_view.php');
$menu->set_action('ʿ�кǿ�ñ����۾Ȳ�', INDUST . 'product_support/hiraide_stocktaking_saishin_view.php');
$menu->set_action('�����ٱ��ʥޥ�����',   INDUST . 'product_support/product_support_master/product_support_master_menu.php');
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
                    <form method="post" action="<?= $menu->out_action('ʿ�л�����Ȳ�(����)') ?>">
                        <input type='image' alt='ʿ�й���λ������Ȳ�Ǥ��ޤ���' border=0 src='<?php echo menu_bar("../../menu_tmp/hiraide_act_payable.png","ʿ�л�����Ȳ�(����)",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('ʿ��ê����Ȳ�(����)') ?>">
                        <input type='image' alt='ʿ�й����ê����Ȳ�Ǥ��ޤ���' border=0 src='<?php echo menu_bar("../../menu_tmp/hiraide_invent.png","ʿ��ê����Ȳ�(����)",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('ʿ��ê���ѥǡ����Ȳ�') ?>">
                        <input type='image' alt='ʿ�й����ê���ѥǡ����Ȳ��Ԥ��ޤ���' border=0 src='<?php echo menu_bar("../../menu_tmp/hiraide_stocktaking.png","ʿ��ê���ѥǡ����Ȳ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('ʿ�кǿ�ñ����۾Ȳ�') ?>">
                        <input type='image' alt='ʿ�й���κǿ�ñ����۾Ȳ��Ԥ��ޤ���' border=0 src='<?php echo menu_bar("../../menu_tmp/hiraide_stocktaking_saishin.png","ʿ�кǿ�ñ����۾Ȳ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�����ٱ��ʥޥ�����') ?>">
                        <input type='image' alt='�����ٱ��ʤΥޥ��������Խ����ޤ���' border=0 src='<?php echo menu_bar("../../menu_tmp/product_support_master.png","�����ٱ��ʥޥ�����",13)."?".uniqid("menu") ?>'>
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
