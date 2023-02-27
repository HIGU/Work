<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� �����ץ����������� ��˥塼                           //
// Copyright(C) 2013-2013 Norihisa.Ohya  norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/30 Created custom_attention_menu.php                             //
// 2013/01/31 ���������Υ�˥塼���ɲ�                                    //
// 2013/02/12 ���������Υ�˥塼�����                                    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 999);                    // site_index=40(����˥塼) site_id=999(�����ȥ�˥塼�򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�����ץ�������������˥塼');
//////////// ɽ�������
$menu->set_caption('�����ץ�������������˥塼');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
    /************ left view *************/
$menu->set_action('��Ŭ�����Ϣ���Ȳ�',       INDUST . 'custom_attention/claim_disposal_form.php');
$menu->set_action('���������Ȳ�',             INDUST . 'custom_attention/attention_point_form.php');
$menu->set_action('���������Խ�',             INDUST . 'custom_attention/attention_point_add_form.php');

    /************ right view *************/
//$menu->set_action('����������奰���', SALES . 'view_all_hiritu.php');
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
    overflow-y:             hidden;
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
                    <form method='post' action='<?php echo $menu->out_action('��Ŭ�����Ϣ���Ȳ�') ?>'>
                        <td align='center'>
                            <input type='image' alt='��Ŭ�����Ϣ���ξȲ��Ԥ��ޤ���' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_custom_claim.png', '��Ŭ�����Ϣ��� �Ȳ�', 12) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_custom_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_custom_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
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
                    <form method='post' action='<?php echo $menu->out_action('���������Ȳ�') ?>'>
                        <td align='center'>
                            <input type='image' alt='�����ץ���񡦺��������ξȲ��Ԥ��ޤ���' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_custom_attention_point_view.png', '�������� �Ȳ�', 12) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���������Խ�') ?>'>
                        <td align='center'>
                            <input type='image' alt='�����ץ���񡦺�����������Ͽ�������Ԥ��ޤ���' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_custom_attention_point_add.png', '�������� �Խ�', 12) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_custom_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
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
        </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
