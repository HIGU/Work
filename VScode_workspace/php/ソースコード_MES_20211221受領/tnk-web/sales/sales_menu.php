<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� ��� ��˥塼       ���� uriage_menu.php                    //
// Copyright(C) 2001-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created sales_menu.php                                        //
// 2002/08/07 ���å����������ɲ�                                          //
// 2002/08/27 �ե졼���б� & �ե졼��ˤ�륵���ȥ�˥塼                   //
// 2002/10/05 processing_msg.php ���ɲ�(�׻���)                             //
// 2003/02/14 ���ط��˥塼 �Υե���Ȥ� style �ǻ�����ѹ�                //
//                              �֥饦�����ˤ���ѹ�������ʤ��ͤˤ���      //
// 2003/03/27 �»�״ط��ξȲ���ɲ�  �ؿ� menu_bar() �����              //
// 2003/11/28 ������پȲ�(���ץ������ñ��Ψ�б�)���ɲ� sales_menu.php     //
// 2003/12/10 menu_bar() png ̾���ְ�äƤ���Τ�����  $uniq����Ѥ���      //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/01/27 ���������������ɽ���˥塼���ɲ�                        //
// 2004/02/13 index1.php��index.php���ѹ�(index1��authenticate���ѹ��Τ���) //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/06/10 view_user($_SESSION['User_ID']) ���˥塼�إå����β����ɲ�  //
// 2004/09/21 MenuHeader Class ��Ƴ��                                       //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2005/01/14 F2/F12������ͭ���������б��Τ��� document.body.focus()���ɲ�  //
// 2005/06/02 ɸ���ʤξ���� ���Ȳ���ɲ�                                 //
// 2005/06/09 �嵭��̾���ѹ� ɸ���� ����� ��� �� ����Ψʬ��(��ࡦ����)   //
// 2005/08/02 �ƥ�˥塼�֤�<br>�쥤�����Ȥ�<div>&nbsp;</div>���ѹ�NN�б�   //
// 2006/02/20 ����������2 �� ������������ ���ѹ�����������������ɲ�  //
// 2006/09/21 ������پȲ�S ��奷�ߥ�졼������ɲ�                        //
// 2007/04/18 2007/04/02�������ñ�����åפΥ��ߥ�졼������ɲ� simulate3  //
// 2007/04/21 php�Υ��硼�ȥ��åȥ�����ɸ�ॿ�����ѹ�(�侩�ͤ�)             //
// 2007/05/23 ��Ω����Ǽ��ʬ�����칩��̤�����Ȳ���ɲ�(industry/�إ��)   //
// 2007/09/20 ɸ���ʤκǿ���������ɽ���Ѥ� sales_form_simulate4.php���ɲ� //
// 2007/10/08 ����պ�����˥塼���ɲá�E_ALL|E_STRICT��                    //
// 2008/05/13 ������Ψ�ѹ��Ѥ� sales_form_simulate7.php���ɲ�        ��ë //
// 2011/04/05 2011/04/01���ڲ��ʲ���αƶ��ۤ�ɽ��������ѹ�           ��ë //
// 2011/11/21 ���ͽ��Ȳ���˥塼���ɲ�                             ��ë //
// 2013/01/29 ��˥塼�̤������Ƥ����Τǥ����ɥС�����ɽ������       ��ë //
// 2013/05/13 ���ڲ��꺹�ۤ�����������ѹ��ΰ١������ȥ���ѹ�       ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');
require_once ('../tnk_func.php');
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(1, 999);                    // site_index=40(����˥塼) site_id=999(�����ȥ�˥塼�򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� ��˥塼');
//////////// ɽ�������
$menu->set_caption('���ط��Ȳ� ��˥塼');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
    /************ left view *************/
$menu->set_action('������پȲ�',       SALES . 'details/sales_form.php');
$menu->set_action('�������̤����',     SALES . 'sales_miken/sales_miken_Main.php');
$menu->set_action('���Ȳ������ץ�', SALES . 'custom/sales_custom_form.php');
$menu->set_action('����Ψʬ��',         SALES . 'sales_material/sales_standard_form.php');
$menu->set_action('����������',       SALES . 'materialCost_sales_comp.php');
$menu->set_action('������������',     SALES . 'materialCost_sales_comp2.php');    // �嵭�����ٲ��������
$menu->set_action('������������',     SALES . 'parts_material/parts_material_show_Main.php');
$menu->set_action('�»�׾Ȳ�'      , SALES . 'profit_loss_query_menu.php');
$menu->set_action('����պ�����˥塼', PL . 'graphCreate/graphCreate_Form.php');
    /************ right view *************/
$menu->set_action('����������奰���', SALES . 'view_all_hiritu.php');
$menu->set_action('CL��奰���'      , SALES . 'view_cl_graph.php');
$menu->set_action('����ɸ����奰���', SALES . 'uriage_graph_sp_std.php');
$menu->set_action('����ɸ��ºݥ����', SALES . 'uriage_graph_sp_std_jissai.php');
$menu->set_action('������ץ����',     SALES . 'uriage_graph_daily_select.php');
$menu->set_action('����ץ����',     SALES . 'uriage_graph_all_tuki.php');
$menu->set_action('������پȲ�S1',     SALES . 'details/sales_form_simulate1.php');    // ��奷�ߥ�졼�����1�ɲ�
$menu->set_action('������پȲ�S2',     SALES . 'details/sales_form_simulate2.php');    // ��奷�ߥ�졼�����2�ɲ�
$menu->set_action('������پȲ�S3',     SALES . 'details/sales_form_simulate3.php');    // 2007/04/02�������ñ�����åפ���奷�ߥ�졼�����3�ɲ�
$menu->set_action('������پȲ�S4',     SALES . 'details/sales_form_simulate4.php');    // ɸ���ʤκǿ���������ɽ���Ѥ���奷�ߥ�졼�����4�ɲ�
$menu->set_action('������پȲ�S5',     SALES . 'details/sales_form_simulate8.php');    // ���ڲ��ʲ���κ������ٰ���ɽ
$menu->set_action('������پȲ�S7',     SALES . 'details/sales_form_simulate7.php');    // 2008/05/13��Ψ�ѹ���ǽ���������Υ���ߥ졼�����
$menu->set_action('���ʥ��롼����������پȲ�',     SALES . 'details/sales_form_product.php');      // ���Ȳ� ���ʥ��롼���̡����١�
$menu->set_action('���ʥ��롼������彸�׾Ȳ�',     SALES . 'details/sales_form_product_all.php');      // ���Ȳ� ���ʥ��롼���̡ʽ��ס�
$menu->set_action('���ͽ��Ȳ�',       SALES . 'sales_plan/sales_plan_form.php');
$menu->set_action('�����ӾȲ�',       SALES . 'actual/sales_actual_form.php');

//////////////// �ƥ��󥫡����ѿ��ǥ��åȤ��� �ؿ�������Υ����С��إåɤ򣱲�ǺѤޤ��뤿��
$uniq = uniqid('menu');

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
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� 
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<!-- ���ߤϥ�����
<script type='text/javascript' src='../sales.js'></script>
-->
<script type='text/javascript'>
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>

<style type='text/css'>
<!--
body {
    background-image:       url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
    /* overflow-y:             hidden; */
}
-->
</style>

</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- width�Ǵֳ֤�Ĵ�� -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0' cellspacing='0' cellpadding='5'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������پȲ�') ?>'>
                        <td align='center'>
                            <input type='image' alt='������پȲ�(���ץ�����ñ��Ψ�б�)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form.png', '�� �� �� �� �� ��', 14) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('�������̤����') ?>'>
                        <td align='center'>
                            <input type='image' alt='��Ω����Ǽ��ʬ�����칩��¦��̤�������٤ξȲ��Ԥ��ޤ���' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_miken.png', '�� �� ̤ �� �� �� ��', 14) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���Ȳ������ץ�') ?>'>
                        <td align='center'>
                            <input type='image' alt='�����ץ�ξ���� �����ɽ �ڤ� ����ɽ�ξȲ�' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_custom_form.png', '�����ץ��������', 13, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('����Ψʬ��') ?>'>
                        <td align='center'>
                            <input type='image' alt='�������Ψ������ñ��Ψ��ʬ�ϥ�˥塼�ʾ���� ����Ψ�ι��ɽ �ڤ� ����ɽ������դξȲ��' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_standard_form.png', '����Ψʬ��(��ࡦ����)', 13, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('����������') ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='���������������ɽ' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_materialCost_sales_comp.png', '����������� ���', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������������') ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='�������ȳƺ���������ɽ(�ܺ����٤���)' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_materialCost_sales_comp2.png', '�������κ�����Ȳ�', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������������') ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='�������γƺ������Ȳ񤷤ޤ���' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_materialCost_sales_parts.png', '�������κ�����Ȳ�', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('�»�׾Ȳ�') ?>'>
                        <td align='center'>
                            <input type='image' alt='�»�״ط��ξȲ�' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_pl_query_menu.png', '�»�״ط��ξȲ�', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('����պ�����˥塼')?>'>
                        <td align='center'>
                            <input type='image' alt='»�״ط��Υ���պ�����˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_graphCreate.png', '»�ץ���պ�����˥塼', 13, 0) . "?id=$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������پȲ�S5')?>'>
                        <td align='center'>
                            <input type='image' alt='���ڲ���ˤ����庹�� ���ٰ���' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_form_s8-4.png', '���ڲ�����庹��', 13, 0) . "?id=$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������پȲ�S7') ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='������Ψ�ѹ�����ߥ졼�����' border=0 src=<?php echo menu_bar('menu_tmp/menu_item_form_s7.png', '������Ψ�ѹ�����ߥ졼�����', 10, 0) . "?id=$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <!--
                <tr>
                    <form method='post' action='<?php echo SALES_MENU ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                        </td>
                    </form>
                </tr>
                -->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0' cellspacing='0' cellpadding='5'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���ʥ��롼����������پȲ�') ?>'>
                        <td align='center'>
                            <input type='image' alt='���ʥ��롼���̤�������٤�Ȳ�' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_product.png', '���ʥ��롼����������پȲ�', 11) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���ʥ��롼������彸�׾Ȳ�') ?>'>
                        <td align='center'>
                            <input type='image' alt='���ʥ��롼���̤����򽸷�ɽ�����ǾȲ�' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_product_all.png', '���ʥ��롼������彸�׾Ȳ�', 11) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���ͽ��Ȳ�') ?>'>
                        <td align='center'>
                            <input type='image' alt='���ͽ��Ȳ�(��Ω�ײ�Τ�)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_plan_form.png', '�� �� ͽ �� �� ��', 14) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method="post" action='/processing_msg.php?script=<?php echo $menu->out_action('����������奰���') ?>'>
                        <td align='center'>
                            <input type='image' value='���ʡ����ʤ���奰���' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_hiritu_graph.png', '���ʡ����ʤ���奰���', 13) . "?$uniq" ?>'>
                            <!-- <input type='image' value='���ʡ����ʤ���奰���' border=0 src='<?php echo IMG ?>menu_item_uriage_hiritu.gif'> -->
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='/processing_msg.php?script=<?php echo $menu->out_action('CL��奰���') ?>'>
                        <td align='center'>
                            <input type='image' alt='���ץ顦��˥���奰���' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_cl_graph.png', '���ץ顦��˥���奰���', 11, 0) . "?$uniq" ?>'>
                            <!-- <input type='image' value='���ץ顦��˥���奰���' border=0 src='<?php echo IMG ?>menu_item_uriage_cl.gif'> -->
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='/processing_msg.php?script=<?php echo $menu->out_action('����ɸ����奰���') ?>'>
                        <td align='center'>
                            <input type='image' alt='���ץ������ʡ�ɸ���� ����ܥ����' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_sp_std_graph.png', '���ץ������ʡ�ɸ���ʥ����', 11) . "?$uniq" ?>'>
                            <!-- <input type='image' value='���ץ�����ɸ�॰���' border=0 src='<?php echo IMG ?>menu_item_uriage_sp_std.gif'> -->
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='/processing_msg.php?script=<?php echo $menu->out_action('����ɸ��ºݥ����') ?>'>
                        <td align='center'>
                            <input type='image' alt='���ץ������ʡ�ɸ���� �ºݸ��� ��ӥ����' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_sp_std_jissai_graph.png', '���ץ�����ɸ�� �ºݸ���', 11) . "?$uniq" ?>'>
                            <!-- <input type='image' value='���ץ�����ɸ��ºݸ���' border=0 src='<?php echo IMG ?>menu_item_uriage_sp_std_jissai.gif'> -->
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������ץ����') ?>'>
                        <td align='center' class='margin1'>
                            <input type='image' alt='���Ρ����ץ顦��˥����פ���奰���' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_daily_form.png', '�� �� �� �� �����', 14) . "?$uniq" ?>'>
                            <!-- <input type='image' name='uriage_graph_niti' border=0 src='<?php echo IMG ?>menu_item_niti_graph.gif'> -->
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method="post" action='/processing_msg.php?script=<?php echo $menu->out_action('����ץ����') ?>'>
                        <td align='center' class='margin1'>
                            <input type='image' alt='���Ρ����ץ顦��˥���פ���奰���' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_monthly_graph.png', '�� �� �� �� �����', 14) . "?$uniq" ?>'>
                            <!-- <input type='image' name='uriage_graph_tuki' border=0 src='<?php echo IMG ?>menu_item_tuki_graph.gif'> -->
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method="post" action='/processing_msg.php?script=<?php echo $menu->out_action('�����ӾȲ�') ?>'>
                        <td align='center' class='margin1'>
                            <input type='image' alt='�����ӾȲ�(��Ω�ײ�Τ�)' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_sales_actual_form.png', '�� �� �� �� �� ��', 14) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?php echo SALES_MENU ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                        </td>
                    </form>
                </tr>
                <!--
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������پȲ�S1') ?>'>
                        <td align='center'>
                            <input type='image' alt='������پȲ�(2006ǯ4��λ��ڥ��å����ǥ��ߥ�졼����󤹤�)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_s.png', '�䣱���ߥ�졼�����', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������پȲ�S2') ?>'>
                        <td align='center'>
                            <input type='image' alt='������پȲ�(�ǽ�λ���ñ���Τޤޤǥ��ߥ�졼����󤹤�)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_s2.png', '�䣲���ߥ�졼�����', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������پȲ�S3') ?>'>
                        <td align='center'>
                            <input type='image' alt='������پȲ�(2007ǯ4��2���������ñ���Υ��ߥ�졼�����򤹤�)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_s3.png', '2007/4/2�ޤǤ������', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������پȲ�S4') ?>'>
                        <td align='center'>
                            <input type='image' alt='������پȲ�(2007ǯ10���������ñ���Υ��ߥ�졼�����򤹤뤿��ǿ���������ɸ���ʤΤ�ɽ��)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_s4.png', 'ɸ���ʤκǿ��������', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                -->
                
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
