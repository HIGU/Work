<?php
//////////////////////////////////////////////////////////////////////////////
// �»�� �Ȳ� ��˥塼                                                   //
// Copyright (C) 2003-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/01/16 Created   profit_loss_query_menu.php                          //
// 2003/03/04 profit_loss_select.php ���� �Ȳ�ε�ǽ�Τߤ�ȴ���Ф���        //
//            kessan/profit_loss_submit.php��ƽФ�(���Ѥ��Ƥ���)           //
// 2003/10/15 ����ê��ɽ���ɲ�                                          //
// 2003/12/15 �����ȥ�˥塼��ɽ�� On / Off ��ǽ���ɲ�                      //
// 2005/02/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/06/15 ����Ψ�׻�ɽ�� $menu->set_action()���ɲ�                      //
// 2005/10/26 E_ALL��ESTRICT ���ѹ� <body style='overflow-y:hidden;' ���ɲ� //
// 2007/10/10 getsuji_comp_invent.php �� invent_comp/invent_comp_view.php�� //
// 2008/10/07 CL���񺹳����ɽ���ɲ�                                   ��ë //
// 2009/08/19 ��ã̾�����»�׾Ȳ���ɲ�                               ��ë //
// 2009/08/20 ��ã̷������ɽ�Ȳ���ɲ�                                    //
//            ��˥塼�ɲäΰ١��쥤�����Ȥ�Ĵ��                       ��ë //
// 2010/01/15 »�����������ɽ���ɲ�                                   ��ë //
// 2012/01/16 �������ɽ�ξȲ���ɲáʥƥ��ȡ�                         ��ë //
// 2012/02/13 �������ɽ�ξȲ��ϰϤ�2011ǯ������ѹ�                        //
//            �������ɽ�ξȲ�����                                   ��ë //
// 2015/06/04 BL��LT���ѹ�                                             ��ë //
// 2016/07/13 CLT������»�פ��ɲ�                                      ��ë //
// 2017/06/08 focus��JavaScript���顼����                            ��ë //
// 2017/09/08 ��¤�����׻����ɲ�                                       ��ë //
// 2017/11/09 ����»�׽�����10��ǰ��ǹԤä�»�׾Ȳ���ɲ�           ��ë //
// 2018/01/12 ��¤�����׻������                                       ��ë //
// 2018/05/29 �軻������ɲáʼ�ʬ�Τߡ�                             ��ë //
// 2018/06/12 �����������ɽ���ɲáʼ�ʬ�Τߡ�                         ��ë //
// 2018/07/05 �����������ɽ�ȷ軻��������                         ��ë //
// 2018/12/06 ��������פ����Τ˸���                                 ��ë //
// 2019/05/16 ��Ⱦ����ɽ�����Ⱦ�����η����ѹ�                   ��ë //
// 2020/01/27 ��������������ɽ���ɲ�                                   ��ë //
// 2020/06/12 �����������ɽ���ɲáʼ�ʬ�Τߡ�                         ��ë //
// 2021/05/31 ������»�פ�2021/04�ʹߥġ���ʤ��ˤ�������                   //
//            ����������»��10����򵡹������                          //
//            (�������դ�ʬ�����뤬ǰ�Τ���)                           ��ë //
// 2021/08/02 $_SESSION['2ki_ym']�Υ��顼���б�                        ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
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
$menu->set_site(10, 13);                    // site_index=10(»�ץ�˥塼) site_id=13(�»�׾Ȳ��˥塼)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SALES_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�»�� �Ȳ� ��˥塼');
//////////// ɽ�������
$menu->set_caption('�� �� » �� �� �� ��  �о�ǯ��λ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('����Ψ�׻�ɽ',                   PL . 'profit_loss_cost_rate.php');
$menu->set_action('����������»��',               PL . 'profit_loss_pl_act.php');
$menu->set_action('����������»��10����',       PL . 'profit_loss_pl_act10.php');
$menu->set_action('����������»�׵���',           PL . 'profit_loss_pl_act_t-bk.php');
$menu->set_action('�ã̡����� �������ɽ',          PL . 'profit_loss_cl_keihi.php');
$menu->set_action('�����������',                   PL . 'profit_loss_keihi.php');
$menu->set_action('�߼��о�ɽ',                     PL . 'profit_loss_bs_act.php');
$menu->set_action('���ê��ɽ',                     PL . 'invent_comp/invent_comp_view.php');
$menu->set_action('��ʿ��ê������',                 PL . 'profit_loss_invent_gross_average.php');
$menu->set_action('�ã̷��񺹳����ɽ',             PL . 'profit_loss_cl_keihi_compare.php');
$menu->set_action('��ã̾�����»��',               PL . 'profit_loss_pl_act_old.php');
$menu->set_action('��ã̷������ɽ',               PL . 'profit_loss_cl_keihi_old.php');
$menu->set_action('�£� ������»��',                PL . 'profit_loss_pl_act_bl.php');
$menu->set_action('����ɸ�� ������»��',          PL . 'profit_loss_pl_act_ctoku.php');
$menu->set_action('»�����������ɽ',               PL . 'profit_loss_pl_act_compare.php');
$menu->set_action('�̣� ������»��',                PL . 'profit_loss_pl_act_lt.php');
$menu->set_action('�ạ̃ԡ�������� ������»��',  PL . 'profit_loss_pl_act_all.php');
$menu->set_action('������� ������»��',          PL . 'profit_loss_pl_act_ss.php');
$menu->set_action('�������Ȳ�',                   PL . 'profit_loss_sales_view.php');

// �������ɽ
$menu->set_action('���� �ܷ軻»��ɽ',      PL . 'profit_loss_pl_act_2ki.php');
$menu->set_action('���� �߼��о�ɽ',        PL . 'profit_loss_bs_act_2ki.php');
$menu->set_action('���� �ã̾�����»��',    PL . 'profit_loss_pl_act_2ki_cl.php');
$menu->set_action('���� �����������',      PL . 'profit_loss_keihi_2ki.php');
$menu->set_action('��¤�����׻�',           PL . 'manufacture_cost_total.php');
$menu->set_action('�軻����',             PL . 'financial_report_view.php');
$menu->set_action('�����������ɽ',         PL . 'account_transfer_view.php');
$menu->set_action('���������',           PL . 'machine_production_view.php');
$menu->set_action('��������������ɽ',           PL . 'depreciation_statement/depreciation_statement_view.php');
$menu->set_action('��������������ٽ�',         PL . 'account_statement_view.php');

// �����ǿ����
$menu->set_action('̤ʧ��׾������',      PL . 'sales_tax_miharai_view.php');
$menu->set_action('���Ǽ�ճ�ǧ',          PL . 'sales_tax_chukan_view.php');
$menu->set_action('�����ǽ���ɽ',          PL . 'sales_tax_zeishukei_view.php');
$menu->set_action('�����ǳ۷׻�ɽ',        PL . 'sales_tax_koujyo_view.php');
$menu->set_action('���������׻�ɽ',        PL . 'sales_tax_syozei_allo_view.php');
$menu->set_action('�����ǿ������',        PL . 'sales_tax_syozei_shinkoku_view.php');
$menu->set_action('���꿽�����1ɽ',       PL . 'print/sales_tax_kakutei_shinkoku1_pdf.php');
$menu->set_action('��2ɽ',                 PL . 'print/sales_tax_kakutei_shinkoku2_pdf.php');
$menu->set_action('��ɽ1-1',               PL . 'print/sales_tax_kakutei_fuhyo1-1_pdf.php');
$menu->set_action('��ɽ1-2',               PL . 'print/sales_tax_kakutei_fuhyo1-2_pdf.php');
$menu->set_action('��ɽ2-1',               PL . 'print/sales_tax_kakutei_fuhyo2-1_pdf.php');
$menu->set_action('��ɽ2-2',               PL . 'print/sales_tax_kakutei_fuhyo2-2_pdf.php');

//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['pl_ym'])) {
    $pl_ym = $_SESSION['pl_ym']; 
} else {
    $pl_ym = date("Ym");        // ���å����ǡ������ʤ����ν����(����)
    if (substr($pl_ym,4,2) != 01) {
        $pl_ym--;
    } else {
        $pl_ym = $pl_ym - 100;
        $pl_ym = $pl_ym + 11;   // ��ǯ��12��˥��å�
    }
}

//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['2ki_ym'])) {
    $pl_ym_2ki = $_SESSION['2ki_ym']; 
} else {
    $pl_ym_2ki = date("Ym");        // ���å����ǡ������ʤ����ν����(����)
    if (substr($pl_ym_2ki,4,2) != 01) {
        $pl_ym_2ki--;
    } else {
        $pl_ym_2ki = $pl_ym_2ki - 100;
        $pl_ym_2ki = $pl_ym_2ki + 11;   // ��ǯ��12��˥��å�
    }
    $_SESSION['2ki_ym'] = $pl_ym_2ki;
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
    // document.pl_form.pl_ym.focus();
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
                    <input class='pt10b' type='submit' name='pl_name' value='����������»��'>
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
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='����������»�׵���'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='������� ������»��'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='����ɸ�� ������»��'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='�������Ȳ�'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>
                    <input class='pt11b' type='submit' name='pl_name' value='��ʿ��ê������'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt11b' type='submit' name='pl_name' value='�£� ������»��'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt11b' type='submit' name='pl_name' value='��ã̷������ɽ'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt11b' type='submit' name='pl_name' value='��ã̾�����»��'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
        <BR>
        <form name='pl_form' action='/kessan/profit_loss_submit.php' method='post'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='6'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' align='center' colspan='5'>
                    <span class='pt12b'>
                        ���������桡�ӡ�ɽ���Ρ��о�ǯ��λ���
                        <select name='2ki_ym' class='pt11b'>
                            <?php
                            $ym_2ki = date("Ym");
                            while(1) {
                                if (substr($ym_2ki,4,2)!=01) {
                                    $ym_2ki--;
                                } else {
                                    $ym_2ki = $ym_2ki - 100;
                                    $ym_2ki = $ym_2ki + 11;
                                }
                                if ($pl_ym_2ki == $ym_2ki) {                                    
                                    $ki = Ym_to_tnk($ym_2ki);
                                    $tuki_chk = substr($ym_2ki,4,2);
                                    if ($tuki_chk == 3 || $tuki_chk == 6 || $tuki_chk == 9 || $tuki_chk == 12) {
                                        if ($tuki_chk >= 1 && $tuki_chk <= 3) {
                                            printf("<option value='%d' selected>��%s�� �裴��Ⱦ��</option>\n",$ym_2ki,$ki);
                                            $init_flg = 0;
                                        } elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {
                                            printf("<option value='%d' selected>��%s�� �裱��Ⱦ��</option>\n",$ym_2ki,$ki);
                                            $init_flg = 0;
                                        } elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {
                                            printf("<option value='%d' selected>��%s�� �裲��Ⱦ��</option>\n",$ym_2ki,$ki);
                                            $init_flg = 0;
                                        } elseif ($tuki_chk >= 10) {
                                            printf("<option value='%d' selected>��%s�� �裳��Ⱦ��</option>\n",$ym_2ki,$ki);
                                            $init_flg = 0;
                                        }
                                    }
                                } else {
                                    $ki = Ym_to_tnk($ym_2ki);
                                    $tuki_chk = substr($ym_2ki,4,2);
                                    if ($tuki_chk == 3 || $tuki_chk == 6 || $tuki_chk == 9 || $tuki_chk == 12) {
                                        if ($tuki_chk >= 1 && $tuki_chk <= 3) {
                                            printf("<option value='%d'>��%s�� �裴��Ⱦ��</option>\n",$ym_2ki,$ki);
                                        } elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {
                                            printf("<option value='%d'>��%s�� �裱��Ⱦ��</option>\n",$ym_2ki,$ki);
                                        } elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {
                                            printf("<option value='%d'>��%s�� �裲��Ⱦ��</option>\n",$ym_2ki,$ki);
                                        } elseif ($tuki_chk >= 10) {
                                            printf("<option value='%d'>��%s�� �裳��Ⱦ��</option>\n",$ym_2ki,$ki);
                                        }
                                    }
                                }
                                if ($ym_2ki <= 201006)
                                    break;
                            }
                            ?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='���� �ܷ軻»��ɽ'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='���� �߼��о�ɽ'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='���� �ã̾�����»��'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='���� �����������'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>��</td>
                
                <td class='winbox' align='center'>��</td>
                
                <td class='winbox' align='center'>��</td>
                
                <td class='winbox' align='center'>��</td>
            </tr>
            <tr>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='��¤�����׻�'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='�軻����'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='�����������ɽ'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='��������������ɽ'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>��</td>
                
                <td class='winbox' align='center'>��</td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='��������������ٽ�'>
                </td>
                <?php if ($_SESSION['User_ID'] == '300144') { ?>
                <?php } else { ?>
                <td class='winbox' align='center'>��</td>
                <?php } ?>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='���������'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
        <BR>
        <form name='pl_form' action='/kessan/profit_loss_submit.php' method='post'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='6'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' align='center' colspan='5'>
                    <span class='pt12b'>
                        �á����ǡ������𡡽񡡤Ρ��о�ǯ��λ���
                        <select name='2ki_ym' class='pt11b'>
                            <?php
                            $ym_2ki = date("Ym");
                            while(1) {
                                if (substr($ym_2ki,4,2)!=01) {
                                    $ym_2ki--;
                                } else {
                                    $ym_2ki = $ym_2ki - 100;
                                    $ym_2ki = $ym_2ki + 11;
                                }
                                if ($pl_ym_2ki == $ym_2ki) {                                    
                                    $ki = Ym_to_tnk($ym_2ki);
                                    $tuki_chk = substr($ym_2ki,4,2);
                                    if ($tuki_chk == 3 || $tuki_chk == 6 || $tuki_chk == 9 || $tuki_chk == 12) {
                                        if ($tuki_chk >= 1 && $tuki_chk <= 3) {
                                            printf("<option value='%d' selected>��%s�� �裴��Ⱦ��</option>\n",$ym_2ki,$ki);
                                            $init_flg = 0;
                                        }
                                    }
                                } else {
                                    $ki = Ym_to_tnk($ym_2ki);
                                    $tuki_chk = substr($ym_2ki,4,2);
                                    if ($tuki_chk == 3 || $tuki_chk == 6 || $tuki_chk == 9 || $tuki_chk == 12) {
                                        if ($tuki_chk >= 1 && $tuki_chk <= 3) {
                                            printf("<option value='%d'>��%s�� �裴��Ⱦ��</option>\n",$ym_2ki,$ki);
                                        }
                                    }
                                }
                                if ($ym_2ki <= 202102)
                                    break;
                            }
                            ?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='̤ʧ��׾������'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='���Ǽ�ճ�ǧ'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='�����ǽ���ɽ'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='�����ǳ۷׻�ɽ'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='���������׻�ɽ'>
                </td>
                
                <td class='winbox' align='center'>��</td>
                
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='�����ǿ������'>
                </td>
                
                <td class='winbox' align='center'>��</td>
            </tr>
            <tr>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='���꿽�����1ɽ'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='��2ɽ'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='��ɽ1-1'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='��ɽ1-2'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='��ɽ2-1'>
                </td>
                
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='��ɽ2-2'>
                </td>
                <td class='winbox' align='center'>��</td>
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
