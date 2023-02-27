<?php
//////////////////////////////////////////////////////////////////////////////
// �����ٱ��� �ޥ������ξȲ���Ͽ ��˥塼                                 //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/10 Created   product_support_master_menu.php                     //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();
require_once ('../../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
////////////// ����������
//$menu->set_site(10, 4);                     // site_index=10(»�ץ�˥塼) site_id=4(��Ω��Ψ�׻�ɽ)
////////////// �꥿���󥢥ɥ쥹����
//$menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�����ٱ��� �ޥ������ξȲ���Ͽ ��˥塼');
//////////// ɽ�������
$menu->set_caption('�����ٱ��� �ޥ������ξȲ���Ͽ ���� ��˥塼');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�����ٱ��ʥޥ���������Ͽ',  'product_supportMaster_Main.php');
//$menu->set_action('���ʥ��롼�ץ����ɤ��Խ�',  'product_groupMaster_Main.php');
$menu->set_action('�ٱ������Ͽ',  'product_support_groupMaster_Main.php');
$menu->set_action('���ʥ��롼�ץ����ɤ��Խ�',  'product_groupMaster_Main2.php');
$request = new Request;
$session = new Session;

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// ��å��������ϥե饰
$msg_flg = 'site';

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<script type='text/javascript'>
<!--
function monthly_send(script_name)
{
    document.monthly_form.action = 'product_support_master_branch.php?product_master_name=' + script_name;
    document.monthly_form.submit();
}
// -->
</script>

<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
    font-size:      14pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
}
-->
</style>
</head>
<body>
    <center>
    <?php echo $menu->out_title_border() ?>
        <form name='product_master' action='product_support_master_branch.php' method='post'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td align='center' colspan='5' class='winbox'>
                    <span class='caption_font'>
                        <?php echo $menu->out_caption() . "\n" ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='�����ٱ��ʥޥ���������Ͽ'>
                </td>
                <?php if ($_SESSION['User_ID'] == '300144') { ?>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='�ٱ������Ͽ'>
                </td>
                <?php } ?>
                <!--
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='���ʥ��롼�ץ����ɤ��Խ�'>
                </td>
                -->
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>

