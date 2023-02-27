<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ���೫ȯ���� ��˥塼                                              //
// Copyright(C) 2002-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2002/02/12 Created  dev_req_menu.php                                     //
// 2002/08/09 register_globals = Off �б�                                   //
// 2002/08/27 �ե졼���б� & �ե졼�ॵ���ȥ�˥塼                         //
// 2002/12/25 function menu_bar() �ˤ���˥塼������ư����                //
// 2003/02/14 ��˥塼�Υե���Ȥ�style�ǻ��ꡣ�֥饦�����ˤ���ѹ����Բ�   //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼̾����Ѥ���          //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/02/13 index1.php��index.php���ѹ�(index1��authenticate���ѹ��Τ���) //
// 2004/07/17 MenuHeader()���饹�򿷵��������ǥ�����ǧ�����Υ��å�����  //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2005/01/14 F2/F12������ͭ���������б��Τ��� document.body.focus()���ɲ�  //
// 2005/08/02 �ƥ�˥塼�֤�<br>�쥤�����Ȥ�<div>&nbsp;</div>���ѹ�NN�б�   //
// 2010/06/18 ê���ƥ��ȤθƤӽФ����                                 ��ë //
// 2010/06/21 �ե����빹�����������ƥ���                               ��ë //
// 2010/09/30 �񻺴�����˥塼�������˰�ư�ΰ٥�󥯲��               ��ë //
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
$menu = new MenuHeader(0, TOP_MENU);    // ǧ�ڥ�٥�=0, �꥿���󥢥ɥ쥹, �����ȥ�λ���ʤ�

////////////// ����������
$menu->set_site(4, 999);                // site_index=4(�ץ���೫ȯ) site_id=999(�ҥ�˥塼����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);            // ������ꤷ�Ƥ���
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ץ���೫ȯ����');
//////////// ɽ�������
$menu->set_caption('�ץ���೫ȯ���� ��˥塼');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��ȯ����Ȳ�', DEV . 'dev_req_select.php');
$menu->set_action('��ȯ��������', DEV . 'dev_req_submit.php');
$menu->set_action('��ȯ���ӥ����', DEV . 'dev_req_graph_jisseki.php');
$menu->set_action('��ȯ̤��λ�����', DEV . 'dev_req_graph2.php');
$menu->set_action('color',    DEV . 'color_check_input.php');
$menu->set_action('���������ƥ���',    DEV . '/test/get_chg_ym.php');
//$menu->set_action('�񻺴�����˥塼',    DEV . '/test/smallsum_assets/assets_menu.php');

$menu->set_action('�ץ���������˥塼', DEV . '/prog_master/prog_menu.php');
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
                    <form method="post" action="<?= $menu->out_action('��ȯ��������') ?>"><?php echo "\n"; // ��ե�����../img/menu_item_dev_req.gif ?>
                        <input type='image' alt='��������������' border=0 src='<?php echo menu_bar("menu_tmp/develop_submit.png","��ȯ��������/����",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <?php
                if (getCheckAuthority(32)) {
                ?>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('�ץ���������˥塼') ?>">
                        <input type='image' alt='�ץ���������˥塼��ɽ��' border=0 src='<?php echo menu_bar("menu_tmp/prog_menu.png","�ץ�������",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <?php
                } else {
                ?>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <?php
                }
                ?>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('��ȯ����Ȳ�') ?>"><?php echo "\n"; // ��ե�����../img/menu_item_dev_qry.gif ?>
                        <input type='image' alt='��ȯ�����Ȳ�' border=0 src='<?php echo menu_bar("menu_tmp/develop_query.png","�ץ���೫ȯ����",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="/processing_msg.php?script=<?= DEV ?>dev_req_graph_jisseki.php"><?php echo "\n"; // ��ե�����../img/menu_item_dev_qry_graph.gif ?>
                        <input type='image' alt='��ȯ���ӾȲ񥰥��' border=0 src='<?php echo menu_bar("menu_tmp/graph_jisseki.png","��ȯ ���/���������",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">         <!-- ��ȯ ���ա���λ��̤��λ �������� -->
                    <form method="post" action="/processing_msg.php?script=<?= DEV ?>dev_req_graph2.php"><?php echo "\n"; // ��ե�����../img/menu_item_dev_req_graph2.gif ?>
                        <input type='image' alt='��ȯ ���ա���λ��̤��λ ��������' border=0 src='<?php echo menu_bar("menu_tmp/graph_uketuke.png","����/��λ/̤��λ���ގ׎�",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('���������ƥ���') ?>">
                        <input type='image' alt='�������������ƥ���' border=0 src='<?php echo menu_bar("menu_tmp/test_chgym.png","�������������ƥ���",13)."?".uniqid("menu") ?>'>
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
