<?php
//////////////////////////////////////////////////////////////////////////////
// �»�� �Ȳ� ��˥塼                                                   //
// Copyright (C) 2003-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/01/16 Created   profit_loss_query_menu.php                          //
// 2003/03/04 profit_loss_select.php ���� �Ȳ�ε�ǽ�Τߤ�ȴ���Ф���        //
//            kessan/profit_loss_submit.php��ƽФ�(���Ѥ��Ƥ���)           //
// 2003/10/15 ����ê��ɽ���ɲ�                                          //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2003/12/15 �����ȥ�˥塼��ɽ�� On / Off ��ǽ���ɲ�                      //
// 2005/02/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/06/15 ����Ψ�׻�ɽ�� $menu->set_action()���ɲ�                      //
// 2005/11/02 kessan/ �Υե�������ѹ������� ��������ѹ����Ƥ��ʤ��Τǽ��� //
// 2013/01/29 kessan/ �Υե�������ѹ������� ��������ѹ����Ƥ��ʤ��Τǽ��� //
//            ������ˤϣ������ɽ����Ƥ��ʤ�                         ��ë //
// 2015/07/08 BL»�פ�LT»�פ��ѹ�                                     ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1,  8);                    // site_index=1(����˥塼) site_id=60(»�׾Ȳ��˥塼)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SALES_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�»�� �Ȳ� ��˥塼');
//////////// ɽ�������
$menu->set_caption('�� �� » �� �� �� ��  �о�ǯ��λ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('����Ψ�׻�ɽ',   PL . 'profit_loss_cost_rate.php');
$menu->set_action('�ã̡�������� ������»��', PL . 'profit_loss_pl_act.php');
$menu->set_action('�ã̡����� �������ɽ', PL . 'profit_loss_cl_keihi.php');
$menu->set_action('�����������',   PL . 'profit_loss_keihi.php');
$menu->set_action('�߼��о�ɽ',     PL . 'profit_loss_bs_act.php');
$menu->set_action('���ê��ɽ',     PL . 'invent_comp/invent_comp_view.php');
$menu->set_action('��ʿ��ê������', PL . 'profit_loss_invent_gross_average.php');
$menu->set_action('�ã̷��񺹳����ɽ', PL . 'profit_loss_cl_keihi_compare.php');
$menu->set_action('��ã̾�����»��', PL . 'profit_loss_pl_act_old.php');
$menu->set_action('��ã̷������ɽ', PL . 'profit_loss_cl_keihi_old.php');
$menu->set_action('�£� ������»��',         PL . 'profit_loss_pl_act_bl.php');
$menu->set_action('����ɸ�� ������»��',         PL . 'profit_loss_pl_act_ctoku.php');
$menu->set_action('»�����������ɽ',         PL . 'profit_loss_pl_act_compare.php');
$menu->set_action('�̣� ������»��',         PL . 'profit_loss_pl_act_lt.php');

//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['pl_ym'])) {
    $pl_ym = $_SESSION['pl_ym']; 
} else {
    $pl_ym = date('Ym');        // ���å����ǡ������ʤ����ν����(����)
    if (substr($pl_ym,4,2) != 01) {
        $pl_ym--;
    } else {
        $pl_ym = $pl_ym - 100;
        $pl_ym = $pl_ym + 11;   // ��ǯ��12��˥��å�
    }
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
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
/** font-weight: normal;        **/
/** font-weight: 400;    ��Ʊ�� **/
/** font-weight: bold;          **/
/** font-weight: 700;    ��Ʊ�� **/
/**         100��900�ޤ�100��� **/
.pt10b {
    font-size:   10.5pt;
    font-weight: bold;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
-->
</style>
<script language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    document.pl_form.pl_ym.focus();
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <form name='pl_form' action='/kessan/profit_loss_submit.php' method='post'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='6'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' align='center' colspan='5'>
                    <span class='pt12b'>
                        �����»���ס��ȡ��񡡤Ρ��о�ǯ��λ���
                        <select name='pl_ym' class='pt11b'>
                            <?php
                            $ym = date("Ym");
                            while(1) {
                                if (substr($ym,4,2)!=01) {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($pl_ym == $ym) {
                                    printf("<option value='%d' selected>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200010)
                                    break;
                            }
                            ?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='�����������'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='�ã̡����� �������ɽ'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='�ã̡�������� ������»��'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='�߼��о�ɽ'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='�ã̷��񺹳����ɽ'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='����Ψ�׻�ɽ'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='���ê��ɽ'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='»�����������ɽ'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>��</td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='�̣� ������»��'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='����ɸ�� ������»��'>
                </td>
                <td class='winbox' align='center'>��</td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
