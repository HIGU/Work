<?php
//////////////////////////////////////////////////////////////////////////////
// �ϽС��������˥塼�ʼ����                                             //
// Copyright(C) 2013-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2013/11/11 Created  per_appli_menu.php                                   //
// 2014/09/18 �ƥ�˥塼�Υ�����ե�������ѹ�                          //
// 2014/09/22 caption�����Ƥ��ѹ�                                           //
// 2020/09/25 ����Ϥο������Ȳ񡦾�ǧ���ޥ��������ɲ�                 ���� //
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
$menu->set_site(97, 999);                // site_index=4(�ץ���೫ȯ) site_id=999(�ҥ�˥塼����)
////////////// �꥿���󥢥ɥ쥹����
$menu->set_RetUrl(TOP_MENU);            // ������ꤷ�Ƥ���
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ϽС��������˥塼�ʼ����');
//////////// ɽ�������
$menu->set_caption('����γƼ��Ͻ��ѻ���������ɽ���ޤ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// ���⡦�ҳ��̥�˥塼
$menu->set_action('�ͻ��˴ؤ����Ͻ�',    PER_APPLI . 'in_personnel_appli/in_personnel_appli_menu.php');
$menu->set_action('�����˴ؤ����Ͻ�',      PER_APPLI . 'in_account_appli/in_account_appli_menu.php');
$menu->set_action('��̳�˴ؤ����Ͻ�',  PER_APPLI . 'in_affairs_appli/in_affairs_appli_menu.php');
//$menu->set_action('�����˴ؤ����Ͻ�',  PER_APPLI . 'in_repair_appli/in_repair_appli_menu.php');
$menu->set_action('����¾���Ͻ�',  PER_APPLI . 'in_other_appli/in_other_appli_menu.php');
$menu->set_action('����ϡʿ�����',  PER_APPLI . 'in_sougou/sougou_Main.php');
$menu->set_action('����ϡʾ�ǧ��',  PER_APPLI . 'in_sougou_admit/sougou_admit_Main.php');
$menu->set_action('����ϡʥޥ�������',  PER_APPLI . 'in_sougou_master/sougou_master_Main.php');
$menu->set_action('����ϡʾȲ��',  PER_APPLI . 'in_sougou_query/sougou_query_Main.php');

$menu->set_action('����ֳ���ȿ���',  PER_APPLI . 'over_time_work_report/over_time_work_report_Main.php');

// �ʲ��ϵ��˥塼
$menu->set_action('���դ˴ؤ����Ͻ�',    PER_APPLI . 'service_appli/service_appli_menu.php');
$menu->set_action('��Ϳ�˴ؤ����Ͻ�',      PER_APPLI . 'supply_appli/supply_appli_menu.php');
$menu->set_action('�����ѹ��˴ؤ����Ͻ�',  PER_APPLI . 'address_appli/address_appli_menu.php');
$menu->set_action('�뺧�˴ؤ����Ͻ�',  PER_APPLI . 'marriage_appli/marriage_appli_menu.php');
$menu->set_action('�л��˴ؤ����Ͻ�',  PER_APPLI . 'childbirth_appli/childbirth_appli_menu.php');
$menu->set_action('���ܼԤ����ä˴ؤ����Ͻ�',  PER_APPLI . 'support_inc_appli/support_inc_appli_menu.php');
$menu->set_action('���ܼԤθ����˴ؤ����Ͻ�',  PER_APPLI . 'support_dec_appli/support_dec_appli_menu.php');
$menu->set_action('Ĥ�֤˴ؤ����Ͻ�',  PER_APPLI . 'condol_appli/condol_appli_menu.php');
$menu->set_action('�ޥ������ѹ��˴ؤ����Ͻ�',  PER_APPLI . 'mycar_appli/mycar_appli_menu.php');
$menu->set_action('��Ū��ʼ����˴ؤ����Ͻ�',  PER_APPLI . 'capacity_appli/capacity_appli_menu.php');
$menu->set_action('���鷱���˴ؤ����Ͻ�',  PER_APPLI . 'training_appli/training_appli_menu.php');


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
            <!-- ���⡦�ҳ��̥�˥塼 -->
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�ͻ��˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='�ͻ��˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/in_personnel_appli.png","�ͻ��˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�����˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='�����˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/in_account_appli.png","�����˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('��̳�˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='��̳�˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/in_affairs_appli.png","��̳�˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('����¾���Ͻ�') ?>">
                        <input type='image' alt='����¾���ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/in_other_appli.png","����¾���Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <!--
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�����˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='�����˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/in_repair_appli.png","�����˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                -->
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('����ϡʿ�����') ?>">
                        <input type='image' alt='����ϡʿ�����' border=0 src='<?php echo menu_bar("menu_tmp/image_sinsei.png","����ϡʿ�����",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('����ϡʾȲ��') ?>">
                        <input type='image' alt='����ϡʾȲ��' border=0 src='<?php echo menu_bar("menu_tmp/image_syoukai.png","����ϡʾȲ��",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>

            <tr>
                <td align='center'>
                    <?php
                    if(getCheckAuthority(64)) { // 64:��ǧ��ǽ
                    ?>
                    <form method="post" action="<?= $menu->out_action('����ϡʾ�ǧ��') ?>">
                        <input type='image' alt='����ϡʾ�ǧ��' border=0 src='<?php echo menu_bar("menu_tmp/image_syounin.png","����ϡʾ�ǧ��",13)."?".uniqid("menu") ?>'>
                    </form>
                    <?php
                    } else {
                    ?>
                        <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <?php
                    }
                    ?>
                    <div>&nbsp;</div>
                </td>

                <td align='center'>
                    <?php
                    if(getCheckAuthority(65)) { // 65:�ޥ������Խ���ǽ
                    ?>
                    <form method="post" action="<?= $menu->out_action('����ϡʥޥ�������') ?>">
                        <input type='image' alt='����ϡʥޥ�������' border=0 src='<?php echo menu_bar("menu_tmp/image_master.png","����ϡʥޥ�������",13)."?".uniqid("menu") ?>'>
                    </form>
                    <?php
                    } else {
                    ?>
                        <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <?php
                    }
                    ?>
                    <div>&nbsp;</div>
                </td>
            </tr>

            <tr>
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('����ֳ���ȿ���') . '?showMenu=Appli' ?>">
                        <input type='image' alt='����ֳ���ȿ�������ϡ�' border=0 src='<?php echo menu_bar("menu_tmp/over_time_input.png","����ֳ���ȿ�������ϡ�",12)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('����ֳ���ȿ���') . '?showMenu=Quiry' ?>">
                        <input type='image' alt='����ֳ���ȿ���ʾȲ��' border=0 src='<?php echo menu_bar("menu_tmp/over_time_quiry.png","����ֳ���ȿ���ʾȲ��",12)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>

            <tr>
                <td align='center'>
                    <form method="post" action="<?= $menu->out_action('����ֳ���ȿ���') . '?showMenu=Judge' ?>">
                        <input type='image' alt='����ֳ���ȿ���ʾ�ǧ��' border=0 src='<?php echo menu_bar("menu_tmp/over_time_admit.png","����ֳ���ȿ���ʾ�ǧ��",12)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            
            <!-- ���˥塼 
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('���դ˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='���դ˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/service_appli_menu.png","���դ˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('��Ϳ�˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='��Ϳ�˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/supply_appli_menu.png","��Ϳ�˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�����ѹ��˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='�����ѹ��˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/address_appli_menu.png","�����ѹ��˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�뺧�˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='�뺧�˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/marriage_appli_menu.png","�뺧�˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�л��˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='�л��˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/childbirth_appli_menu.png","�л��˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('���ܼԤ����ä˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='���ܼԤ����ä˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/support_inc_appli_menu.png","���ܼԤ����ä˴ؤ����Ͻ�",11)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('���ܼԤθ����˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='���ܼԤθ����˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/support_dec_appli_menu.png","���ܼԤθ����˴ؤ����Ͻ�",11)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('Ĥ�֤˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='Ĥ�֤˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/condol_appli_menu.png","Ĥ�֤˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�ޥ������ѹ��˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='�ޥ������ѹ��˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/mycar_appli_menu.png","�ޥ������ѹ��˴ؤ����Ͻ�",11)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('��Ū��ʼ����˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='��Ū��ʼ����˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/capacity_appli_menu.png","��Ū��ʼ����˴ؤ����Ͻ�",11)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('���鷱���˴ؤ����Ͻ�') ?>">
                        <input type='image' alt='���鷱���˴ؤ����ϽХ�˥塼' border=0 src='<?php echo menu_bar("menu_tmp/training_appli_menu.png","���鷱���˴ؤ����Ͻ�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                </td>
            </tr>
            -->
        </table>
    </center>
</body>
<?= $menu->out_site_java() ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
