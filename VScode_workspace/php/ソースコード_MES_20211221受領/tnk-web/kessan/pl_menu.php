<?php
//////////////////////////////////////////////////////////////////////////////
// »�� ��˥塼   (�� �����֡��軻 ��˥塼)                           //
// Copyright (C) 2002-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/03/22 Created  pl_menu.php                                          //
// 2003/12/10 kessan_menu.php �� pl_menu.php �ؿ�������                     //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/02/13 index1.php��index.php���ѹ�(index1��authenticate���ѹ��Τ���) //
// 2004/06/10 view_user($_SESSION['User_ID']) ���˥塼�إå����β����ɲ�  //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2005/01/18 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//            onLoad='document.mhForm.backwardStack.focus()'���ѹ���NN�б�  //
// 2005/08/02 �ƥ�˥塼�֤�<br>�쥤�����Ȥ�<div>&nbsp;</div>���ѹ�NN�б�   //
// 2006/09/28 ��Ω��ư����Ψ��˥塼(����)�򸽺ߤޤǤΤ�Τ��ִ���          //
// 2007/10/05 ��Ω��ư����Ψ��˥塼������ѹ�ooya���� ��ë             //
// 2007/10/06 ����պ�����˥塼���ɲá�E_ALL|E_STRICT�� ���硼�ȥ��å��ѻ� //
// 2016/03/08 »��ͽ¬��˥塼����߷��Ĺ������Ȳ�Ǥ���褦�ѹ�            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ��� menu_bar()�ǻ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(10, 999);                   // site_index=10(»�ץ�˥塼) site_id=999(�����Ȥ򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(MENU);                 // �̾�ϻ��ꤹ��ɬ�פϤʤ�(�ȥåץ�˥塼)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('» �� ��˥塼');
//////////// ɽ�������
$menu->set_caption('»�״ط��ξȲ񡦹���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('»�׾Ȳ��˥塼',   PL . 'profit_loss_query_menu.php');
$menu->set_action('»�׺�����˥塼',   PL . 'profit_loss_select.php');
$menu->set_action('�������祳�����ݼ�', PL . 'act_table_mnt_new.php');
$menu->set_action('��ʬ������Ψ�ݼ�',   PL . 'category_mnt.php');
$menu->set_action('��ʬ������Ψ�ݼ�',   PL . 'allocation_mnt.php');
$menu->set_action('�����ɥơ��֥��ݼ�', PL . 'cd_table_mnt.php');
$menu->set_action('������Ψ�Ȳ񹹿�',   PL . 'machine_labor_rate_mnt.php');
// $menu->set_action('��Ω��Ψ��ư����Ψ', PL . 'wage_rate.php');
$menu->set_action('��Ω��Ψ��ư����Ψ', PL . 'wage_rate/wage_rate_menu.php');
$menu->set_action('�����ӥ�����˥塼', PL . 'service/service_percentage_menu.php');
// $menu->set_action('��ȱ����������',   PL . '');
$menu->set_action('����պ�����˥塼', PL . 'graphCreate/graphCreate_Form.php');
$menu->set_action('»��ͽ¬��˥塼',   PL . '/pl_estimate/profit_loss_estimate_menu.php');

//////////////// �ƥ��󥫡����ѿ��ǥ��åȤ��� �ؿ�������Υ����С��إåɤ򣱲�ǺѤޤ��뤿��
$uniq = uniqid('menu');

unset($_SESSION['act_offset']);     // ���祳���ɥơ��֥�ǻ��Ѥ���offset�ͤ���
unset($_SESSION['cd_offset']);      // �����ɥơ��֥�ǻ��Ѥ���offset�ͤ���

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� 
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
-->
</style>
</head>
<body onLoad='document.mhForm.backwardStack.focus()' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- width�Ǵֳ֤�Ĵ�� -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('»�׾Ȳ��˥塼')?>'>
                        <td align='center'>
                            <input type='image' alt='�»�פξȲ��˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_pl_query_menu.png', ' »�� �Ȳ� ��˥塼', 14) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('»�׺�����˥塼')?>'>
                        <td align='center'>
                            <input type='image' alt='� »�� ���� ��˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_pl_update_menu.png', ' »�� ���� ��˥塼', 14) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('�������祳�����ݼ�')?>'>
                        <td align='center'>
                            <input type='image' alt='�������祳���ɤ��ݼ��˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_act_table_menu.png', ' �������祳�����ݼ�', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('��ʬ������Ψ�ݼ�')?>'>
                        <td align='center'>
                            <input type='image' alt='»�״ط�����ʬ������Ψ�����ݼ��˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_category_mnt.png', ' ��ʬ�� ����Ψ �ݼ�', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('��ʬ������Ψ�ݼ�')?>'>
                        <td align='center'>
                            <input type='image' alt='»�״ط��ξ�ʬ������Ψ�ݼ��˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_allocation_mnt.png', ' ��ʬ�� ����Ψ �ݼ�', 14, 0) . "?id=$uniq" ?>'>
                            <!-- <input type='image' value='���Υ����ƥ�' border=0 src='./img/menu_item.gif'> -->
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('����պ�����˥塼')?>'>
                        <td align='center'>
                            <input type='image' alt='»�״ط��Υ���պ�����˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_graphCreate.png', '»�ץ���պ�����˥塼', 13, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <!--
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                -->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <?php if (($_SESSION['User_ID'] == '300144') || ($_SESSION['User_ID'] == '015806')) { ?>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('»��ͽ¬��˥塼')?>'>
                        <td align='center'>
                             <input type='image' alt='�»�פ�ͽ¬��˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_pl_estimate_menu.png', ' »�� ͽ¬ ��˥塼', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php } else { ?>
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php } ?>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('�����ɥơ��֥��ݼ�')?>'>
                        <td align='center'>
                             <input type='image' alt='�������ȿ����ͻ������ɥơ��֥��ݼ�' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_cd_table_mnt.png', ' �����ɥơ��֥��ݼ�', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������Ψ�Ȳ񹹿�')?>'>
                        <td align='center'>
                             <input type='image' alt='��¤�ݤε��� ��Ψ �׻�ɽ ������˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_machine_rate.png', ' ������Ψ �Ȳ� ����', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('��Ω��Ψ��ư����Ψ')?>'>
                        <td align='center'>
                            <input type='image' alt='��Ω��Ψ����ư����Ψ�ξȲ�' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_wage_rate.png', '��Ω��Ψ����ư����Ψ', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('�����ӥ�����˥塼')?>'>
                        <td align='center'>
                            <input type='image' alt='�����ӥ��������ϡ��Ȳ����� ������˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_service_menu.png', '�����ӥ�����˥塼', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='��ȱ����������ϥ�˥塼�������Ǥ�' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_logout.png', '��ȱ�����������', 14, 0) . "?id=$uniq" ?>'>
                            <!-- <input type='image' value='���Υ����ƥ�' border=0 src='./img/menu_item.gif'> -->
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
