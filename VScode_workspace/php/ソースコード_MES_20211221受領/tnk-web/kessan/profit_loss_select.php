<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� �������Ȳ� ����ե�����                                     //
// Copyright (C) 2003-2021      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2003/01/16 Created   profit_loss_select.php                              //
// 2003/01/27 AS/400 �Ȥθ��̥ǡ�����󥯥�˥塼���ɲ�                     //
// 2003/02/06 �ǡ����Υ�󥯻��˳�ǧ�����������ɲ� JavaScript             //
// 2003/02/07 ��������������˳�ǧ���������ȼ��̤Τ��������֤��ѹ�        //
//     �ǡ��������ν�����ȥե������ɲ� ��ȼ���ɲ� 1 AS 2 ���� 3 �¹�     //
// 2003/02/19 ʸ����������֥饦�������ѹ��Ǥ��ʤ����� title-font ��        //
// 2003/02/22 ê����Ĵ����������(���������)Ĵ����˥塼�ɲ�                //
// 2003/02/23 date("Y/m/d H:m:s") �� H:i:s �Υߥ�����                       //
// 2003/02/24 ��帶��Ĵ�����ϤΥ�˥塼���ɲ�                              //
// 2003/02/26 ��̳�������������Ϥ��˥塼���ɲ�                            //
// 2003/03/04 AS/400 �Ȥθ��̥ǡ�����󥯤� AS/400 �Υ�˥塼�������ɲ�     //
// 2003/03/10 �����Ĵ�����ϥ�˥塼���ɲ�                                //
// 2003/09/27 � ���ê��ɽ�� �ǡ�������� �� �Ȳ� ���ɲ�                 //
// 2003/10/10 ��ǡ�����軻�ǡ������ִ����뤿��ǡ������ꥢ�����ɲ�      //
// 2003/12/15 �����ȥ�˥塼��ɽ�� On / Off ��ǽ���ɲ�                      //
// 2005/05/30 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/08/29 ����Ψ�׻�ɽ�� $menu->set_action()���ɲ�                      //
// 2005/10/26 �ã̾�����»�פ�嵭Ʊ���ɲ� E_ALL��E_STRICT ���ѹ�           //
// 2007/10/09 set_focus()�򥳥��� php�Υ���åȥ��åȤ�ɸ�ॿ���� ����¾  //
// 2007/10/10 ���������� »�׷׻���Υǡ��������˥塼���ɲ�             //
// 2009/08/18 ʪή���»����Ͽ���ɲ�                                 ��ë //
// 2009/08/19 ʪή�򾦴ɤ��ѹ�                                         ��ë //
//            ��ã̾�����»�׾Ȳ���ɲ�                               ��ë //
// 2009/08/20 ��ã̷������ɽ�Ȳ���ɲ�                                    //
//            ��˥塼�ɲäΰ١��쥤�����Ȥ�Ĵ��                       ��ë //
// 2009/08/21 �£̡������ ������»�׾Ȳ���ɲ�                      ��ë //
// 2009/12/09 ������ʣã̡˾�����»�׾Ȳ���ɲ�                     ��ë //
// 2010/01/14 BL������»�פΰ��֤��ѹ���»�����������ɽ���ɲ�         ��ë //
// 2010/01/15 ������»�׺����ƥ����ѥ��������ɽ���ʥ����Ȳ���       ��ë //
// 2010/01/27 �����̥ƥ����ѥ�󥯤�����ʥƥ��ȴ�λ�女���Ȳ���     ��ë //
// 2010/02/05 BL�»�׷׻���̤���ѤΤ����󥯲��                   ��ë //
// 2013/03/05 2013/02��03��Ĵ����������Ϥ���١����դ�ɽ�����ѹ�      ��ë //
// 2015/06/04 BL��LT���ѹ�                                             ��ë //
// 2016/07/13 CLT�ξ�����»�פ��ɲ�(����������»�פ�)                ��ë //
// 2016/07/22 �����������»�פ�ã̤����ѵס��������ѹ�             ��ë //
// 2017/11/08 LT�ξ�����»�פ����������(����������»�פ��б�)     ��ë //
// 2017/11/09 ����»�׽�����10��ǰ��ǹԤä�»�׾Ȳ���ɲ�           ��ë //
// 2020/01/27 ��Ⱦ����θ�������������ɽ�Υǡ��������ߤ��ɲ�         ��ë //
// 2021/08/02 $_SESSION['2ki_ym']�Υ��顼���б�                        ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(10,  7);                    // site_index=10(»�ץ�˥塼) site_id= 7(»�׺�����˥塼)

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�»�״ط� ����ե�����');
//////////// ɽ�������
$menu->set_caption('�о�ǯ��λ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('����Ψ�׻�ɽ',           PL . 'profit_loss_cost_rate.php');
$menu->set_action('����������»��',         PL . 'profit_loss_pl_act.php');
$menu->set_action('����������»��10����',         PL . 'profit_loss_pl_act10.php');
$menu->set_action('�ã̡����� �������ɽ',         PL . 'profit_loss_cl_keihi.php');
$menu->set_action('�����������',           PL . 'profit_loss_keihi.php');
$menu->set_action('�߼��о�ɽ',             PL . 'profit_loss_bs_act.php');
$menu->set_action('���ê��ɽ���',         PL . 'invent_comp/invent_comp_get_form.php');
$menu->set_action('���ê��ɽ',             PL . 'invent_comp/invent_comp_view.php');
$menu->set_action('���ɡ��»����Ͽ',     PL . 'profit_loss_nkb_input.php');
$menu->set_action('����������',           PL . 'pl_segment/pl_segment_get_form.php');
$menu->set_action('�£̡�� ������»��',         PL . 'profit_loss_pl_act_bls.php');
$menu->set_action('����ɸ�� ������»��',         PL . 'profit_loss_pl_act_ctoku.php');
$menu->set_action('������� ������»��',         PL . 'profit_loss_pl_act_ss.php');
$menu->set_action('��ã̾�����»��',      PL . 'profit_loss_pl_act_old.php');
$menu->set_action('��ã̷������ɽ',       PL . 'profit_loss_cl_keihi_old.php');
$menu->set_action('�̿Ͱ���Ψ�׻�',     PL . 'profit_loss_bls_input.php');
$menu->set_action('�ÿͰ���Ψ�׻�',     PL . 'profit_loss_ctoku_input.php');
$menu->set_action('�£� ������»��',         PL . 'profit_loss_pl_act_bl.php');
$menu->set_action('»�����������ɽ',         PL . 'profit_loss_pl_act_compare.php');
$menu->set_action('�ã̷��񺹳����ɽ',         PL . 'profit_loss_cl_keihi_compare.php');
$menu->set_action('�����̥ƥ���',         PL . 'profit_loss_cl_keihi_compare.php');
$menu->set_action('���ҿͰ���Ψ�׻�',     PL . 'profit_loss_staff_input.php');
$menu->set_action('�̣� ������»��',         PL . 'profit_loss_pl_act_lt.php');
$menu->set_action('�ạ̃ԡ�������� ������»��',         PL . 'profit_loss_pl_act_all.php');
$menu->set_action('��������������ɽ���',         PL . 'depreciation_statement/depreciation_statement_get_form.php');
$menu->set_action('��������������ɽ',         PL . 'depreciation_statement/depreciation_statement_view.php');

///////////// ��������ץ�̾�����
$current_script = $menu->out_self();

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
if ($pl_ym >= 202104) {
    $menu->set_action('����������»��',         PL . 'profit_loss_pl_act.php');
} else {
    $menu->set_action('����������»��',         PL . 'profit_loss_pl_act_t.php');
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
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<script language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ��ᡢ��������ѹ���NN�б�
}
function as_ftp_click(obj) {
    return confirm("AS/400�Ȥΰ��ǡ�����󥯤�¹Ԥ��ޤ���\n���˥ǡ�����������Ͼ�񤭤���ޤ���\n���ˤ��᤻�ޤ���");
}
function act_allo_click(obj) {
    return confirm("����Σã�����Ψ��׻����ޤ���\n������Ǥ�����");
}
function act_save_click(obj) {
    return confirm("��������������¹Ԥ��ޤ���\n������Ǥ�����");
}
function cl_pl_click(obj) {
    return confirm("�ã̾����� »�׷׻���¹Ԥ��ޤ���\n������Ǥ�����");
}
function data_update_submit(obj){
    var YM = obj.pl_ym.value;
    if (confirm("�ǯ��� �� " + YM + "�� �Ǥ����ְ㤤����ޤ��󤫡�")) {
        return confirm("�����˼¹Ԥ��Ƥ����Ǥ��͡�");
    }
    return false
}
function as_ftp_submit(obj){
    var YM = obj.pl_ym.value;
    if (confirm(YM+" � ��AS/400�ȤΥǡ�����󥯤�¹Ԥ��ޤ���\n���˥ǡ�����������Ͼ�񤭤���ޤ���\n���ˤ��᤻�ޤ���")) {
        return confirm("�����˼¹Ԥ��Ƥ����Ǥ��͡�");
    }
    return false
}
function monthly_clear(obj){
    var YM = document.clear.pl_ym.value;
    var name = obj.value;
    if (confirm("[ "+YM+" ] ��� "+name+" ��¹Ԥ��ޤ���\n�������Ǥ�����")) {
        return confirm("�����˼¹Ԥ��Ƥ����Ǥ��͡�");
    }
    return false
}
// -->
</script>
<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
    font:bold 11pt;
}
/** font-weight: normal;        **/
/** font-weight: 400;    ��Ʊ�� **/
/** font-weight: bold;          **/
/** font-weight: 700;    ��Ʊ�� **/
/**         100��900�ޤ�100��� **/
.pt11 {
    font-size:11pt;
}
.pt11b {
    font-size: 10pt;
}
.pt12b {
    font-size:   11pt;
    font-weight: bold;
}
.explain_font {
    font-size: 8.5pt;
    font-family: monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <form action='profit_loss_submit.php' method='post' onSubmit='return data_update_submit(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='4' class='winbox'>
                        <span class='pt12b'>
                        ��ǡ��������κ�ȡ�<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='1 AS/400��TNK' onClick='return as_ftp_click(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='2 CL����Ψ�׻�' onClick='return act_allo_click(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='3 ��������¹�' onClick='return act_save_click(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='4 ê��������'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='5 ê����Ĵ��'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='6 ������Ĵ��'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='7 ��帶��Ĵ��'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='8 ��̳��������'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='9 ����Ĵ��'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='10 �ã�»�׷׻�' onClick='return cl_pl_click(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        ��
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        ��
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='11 ���ɡ��»����Ͽ'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='12 �̿Ͱ���Ψ�׻�'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='13 �ÿͰ���Ψ�׻�'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='14 ���ҿͰ���Ψ�׻�'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        
        <br>
        
        <form action='profit_loss_submit.php' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='4' class='winbox'>
                        <span class='pt12b'>
                        �����»���ס��ء������ȡ��񡡤Ρ�<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�����������'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�ã̡����� �������ɽ'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='����������»��'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�߼��о�ɽ'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' bgcolor='#ffffc6' align='center'>
                        <input class='pt11b' type='submit' name='pl_name' value='�ã̷��񺹳����ɽ'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='����Ψ�׻�ɽ'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�ã�ͽ����»��'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='��������ɽ'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='����������»��10����'>
                    </td>
                    <!--
                    <td class='winbox' align='center'>��</td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�̣� ������»��'>
                    </td>
                    -->
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='����ɸ�� ������»��'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='������� ������»��'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='»�����������ɽ'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                    <input class='pt11b' type='submit' name='pl_name' value='��ã̷������ɽ'>
                    </td>
                    <td class='winbox' align='center'>
                        <input class='pt11b' type='submit' name='pl_name' value='��ã̾�����»��'>
                    </td>
                    <!--
                    <td class='winbox' align='center'>��</td>
                    -->
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�����̥ƥ���'>
                    </td>
                    <td class='winbox' align='center'>
                        <input class='pt11b' type='submit' name='pl_name' value='�£� ������»��'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        
        <br>
        
        <form action='profit_loss_submit.php' method='post' onSubmit='return as_ftp_submit(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='3' class='winbox'>
                        <span class='pt12b'>
                        ���ӡ��������Ȥθ��̥ǡ�����󥯡���<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#7fbeff' class='winbox'> <!-- ������ -->
                        <input class='pt11b' type='submit' name='pl_name' value='���������ǡ���' >
                    </td>
                    <td align='center' bgcolor='#7fbeff' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�ã̷���ǡ���' >
                    </td>
                    <td align='center' bgcolor='#7fbeff' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�������������' >
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>AS/400 02��23��21 D</span>
                    </td>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>AS/400 77��77��31��04 B</span>
                    </td>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>AS/400 77��77��31��04 B1</span>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#7fbeff' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='������ݥǡ���' >
                    </td>
                    <td align='center' bgcolor='#7fbeff' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�ã�»�ץǡ���' >
                    </td>
                    <td align='center' bgcolor='#7fbeff' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�߼��оȥǡ���' >
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>AS/400 02��26��37 E</span>
                    </td>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>02��33��23��10��06 AC</span>
                    </td>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>02��33��23��10��02 F</span>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    
    <br>
    
        <form name='clear' action='profit_loss_submit.php' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='3' class='winbox'>
                        <span class='pt12b'>
                        ��ǡ����ִ���ȤΤ�������򥯥ꥢ������<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='CL����Clear' onClick='return monthly_clear(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='��������Clear' onClick='return monthly_clear(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='CL»��Clear' onClick='return monthly_clear(this)'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    
    <br>
    
        <form action='profit_loss_submit.php' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='2' class='winbox'>
                        <span class='pt12b'>
                        ����ê��ɽ��<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�ǡ��������' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='���ê��ɽ' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    
    <br>
    
    <br>
    
        <form action='profit_loss_submit.php' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='2' class='winbox'>
                        <span class='pt12b'>
                        ��Ⱦ����������������ɽ��<?php echo $menu->out_caption()?>
                        </span>
                        <select name='2ki_ym' class='pt12b'>
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='��������������ɽ���' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='��������������ɽ' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    
    <br>
    
    <!----------------------------------------------------------- �����Ȥ��ѹ�
        <form action='profit_loss_submit.php' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='5' class='winbox'>
                        <span class='pt12b'>
                        �����硡�̡�»���ס��ء������Ρ�<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='�Х�������' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='��˥���Ω' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='���ץ���Ω' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='����Ω����' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='����Ωɸ��' >
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='��¤������' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='��¤��1NC' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='��¤��6��' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='��¤��4NC' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='��¤��PF' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table>
        </form>
    �����ޤ� ------------------------------------------>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
