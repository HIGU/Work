<?php
//////////////////////////////////////////////////////////////////////////
// �»�״ط� ����ê�������ϵڤ���Ͽ                                  //
// Copyright(C) 2003-2015 K.Kobayashi tnksys@nitto-kohki.co.jp          //
// �ѹ�����                                                             //
// 2003/02/19 ��������  profit_loss_inventory_put.php                   //
// 2003/02/19 ʸ����������֥饦�������ѹ��Ǥ��ʤ����� title-font ��    //
// 2003/02/23 date("Y/m/d H:m:s") �� H:i:s �Υߥ�����                   //
// 2013/12/02 ����ê��������Ϥ��ɲ�                               ��ë //
// 2013/12/04 �����CC������������ɲ�                             ��ë //
// 2014/01/15 ����ê�����פ����Ϥ��ʤ��Τ��طʤ򥰥졼���ѹ�     ��ë //
// 2015/06/02 �ġ���ê��������Ϥ��ɲ�                             ��ë //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name �ϼ�ư����
$_SESSION["site_index"] = 10;       // �»�״ط�=10 �Ǹ�Υ�˥塼�� 99 �����
$_SESSION["site_id"]    =  7;       // ���̥�˥塼̵�� (0 <=)
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
}
//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}

if (!isset($_POST['touroku'])) {     // �ǡ�������
    ////////// ��Ͽ�Ѥߤʤ��ê����ۼ���
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='���ץ�'", $yyyymm);
    getUniResult($query,$c_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='��˥�'", $yyyymm);
    getUniResult($query,$l_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�������'", $yyyymm);
    getUniResult($query,$zai_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='��������'", $yyyymm);
    getUniResult($query,$buhin_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������'", $yyyymm);
    getUniResult($query,$gai_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������'", $yyyymm);
    getUniResult($query,$kou_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������Ω'", $yyyymm);
    getUniResult($query,$kumi_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������'", $yyyymm);
    getUniResult($query,$ken_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='����ã�'", $yyyymm);
    getUniResult($query,$cc_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������'", $yyyymm);
    getUniResult($query,$ctokut_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ������'", $yyyymm);
    getUniResult($query,$tzai_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ�������'", $yyyymm);
    getUniResult($query,$tbuhin_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ��볰��'", $yyyymm);
    getUniResult($query,$tgai_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ��빩��'", $yyyymm);
    getUniResult($query,$tkou_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ�����Ω'", $yyyymm);
    getUniResult($query,$tkumi_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ��븡��'", $yyyymm);
    getUniResult($query,$tken_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ���ã�'", $yyyymm);
    getUniResult($query,$tcc_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ�����'", $yyyymm);
    getUniResult($query,$toolt_kin);
} else {                            // ��Ͽ����
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='���ץ�'", $yyyymm);
    if (getUniResult($query,$c_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�')", $yyyymm, $_POST['invent_c']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='���ץ�'", $_POST['invent_c'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='��˥�'", $yyyymm);
    if (getUniResult($query,$l_kin) <= 0) { //$c_kin����ľ����
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�')", $yyyymm, $_POST['invent_l']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='��˥�'", $_POST['invent_l'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�������'", $yyyymm);
    if (getUniResult($query,$zai_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '�������')", $yyyymm, $_POST['invent_zai']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='�������'", $_POST['invent_zai'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='��������'", $yyyymm);
    if (getUniResult($query,$buhin_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '��������')", $yyyymm, $_POST['invent_buhin']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='��������'", $_POST['invent_buhin'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������'", $yyyymm);
    if (getUniResult($query,$gai_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '������')", $yyyymm, $_POST['invent_gai']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='������'", $_POST['invent_gai'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������'", $yyyymm);
    if (getUniResult($query,$kou_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '������')", $yyyymm, $_POST['invent_kou']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='������'", $_POST['invent_kou'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������Ω'", $yyyymm);
    if (getUniResult($query,$kumi_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '������Ω')", $yyyymm, $_POST['invent_kumi']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='������Ω'", $_POST['invent_kumi'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������'", $yyyymm);
    if (getUniResult($query,$ken_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '������')", $yyyymm, $_POST['invent_ken']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='������'", $_POST['invent_ken'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='����ã�'", $yyyymm);
    if (getUniResult($query,$cc_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '����ã�')", $yyyymm, $_POST['invent_cc']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='����ã�'", $_POST['invent_cc'], $yyyymm);
        query_affected($query);
    }
    // ����ê����ι�פ�׻�
    $ctoku_kin = $_POST['invent_zai'] + $_POST['invent_buhin'] + $_POST['invent_gai'] + $_POST['invent_kou'] + $_POST['invent_kumi'] + $_POST['invent_ken'] + $_POST['invent_cc'];
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������'", $yyyymm);
    if (getUniResult($query,$ctoku_total) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '������')", $yyyymm, $ctoku_kin);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='������'", $ctoku_kin, $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ������'", $yyyymm);
    if (getUniResult($query,$tzai_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '�ġ������')", $yyyymm, $_POST['invent_tzai']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='�ġ������'", $_POST['invent_tzai'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ�������'", $yyyymm);
    if (getUniResult($query,$tbuhin_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '�ġ�������')", $yyyymm, $_POST['invent_tbuhin']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='�ġ�������'", $_POST['invent_tbuhin'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ��볰��'", $yyyymm);
    if (getUniResult($query,$tgai_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '�ġ��볰��')", $yyyymm, $_POST['invent_tgai']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='�ġ��볰��'", $_POST['invent_tgai'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ��빩��'", $yyyymm);
    if (getUniResult($query,$tkou_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '�ġ��빩��')", $yyyymm, $_POST['invent_tkou']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='�ġ��빩��'", $_POST['invent_tkou'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ�����Ω'", $yyyymm);
    if (getUniResult($query,$tkumi_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '�ġ�����Ω')", $yyyymm, $_POST['invent_tkumi']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='�ġ�����Ω'", $_POST['invent_tkumi'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ��븡��'", $yyyymm);
    if (getUniResult($query,$tken_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '�ġ��븡��')", $yyyymm, $_POST['invent_tken']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='�ġ��븡��'", $_POST['invent_tken'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ���ã�'", $yyyymm);
    if (getUniResult($query,$tcc_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '�ġ���ã�')", $yyyymm, $_POST['invent_tcc']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='�ġ���ã�'", $_POST['invent_tcc'], $yyyymm);
        query_affected($query);
    }
    // �ġ���ê����ι�פ�׻�
    $tool_kin = $_POST['invent_tzai'] + $_POST['invent_tbuhin'] + $_POST['invent_tgai'] + $_POST['invent_tkou'] + $_POST['invent_tkumi'] + $_POST['invent_tken'] + $_POST['invent_tcc'];
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ�����'", $yyyymm);
    if (getUniResult($query,$tool_total) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '�ġ�����')", $yyyymm, $tool_kin);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='�ġ�����'", $tool_kin, $yyyymm);
        query_affected($query);
    }
    $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>ê������Ͽ��λ<br>�� %d�� %d��</font>",$ki,$tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // ���դ����
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // ��˽�������Ƥ���
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>�ê��������</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';

/* ����ʸ�����������ɤ��������å� */
function isDigit(str){
    var len=str.length;
    var c;
    for(i=0;i<len;i++){
        c=str.charAt(i);
        if("0">c||c>"9")
            return true;
        }
    return false;
}
function invent_input(obj){
    if(!obj.invent_c.value.length){
        alert("���ץ�ê����������󤬶���Ǥ���");
        obj.invent_c.focus();
        obj.invent_c.select();
        return false;
    }
    if(isDigit(obj.invent_c.value)){
        alert("���Ͱʳ������Ͻ���ޤ���");
        obj.invent_c.focus();
        obj.invent_c.select();
        return false;
    }
    if(!obj.invent_l.value.length){
        alert("��˥�ê����������󤬶���Ǥ���");
        obj.invent_l.focus();
        obj.invent_l.select();
        return false;
    }
    if(isDigit(obj.invent_l.value)){
        alert("���Ͱʳ������Ͻ���ޤ���");
        obj.invent_l.focus();
        obj.invent_l.select();
        return false;
    }
    return true;
}
function set_focus(){
    document.invent.invent_c.focus();
    document.invent.invent_c.select();
}
// -->
</script>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt11 {
    font-size:11pt;
}
.pt11b {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
}
.title-font {
    font:bold 16.5pt;
    font-family: monospace;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
.rightg{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color:LightGrey;
}
.margin0 {
    margin:0%;
}
-->
</style>
</HEAD>
<BODY class='margin0' onLoad="set_focus()">
    <center>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <form method='post' action='profit_loss_select.php'>
                    <td width='60' bgcolor='blue' align='center' valign='center'>
                        <input class='pt12b' type='submit' name='return' value='���'>
                    </td>
                </form>
                <td bgcolor='#d6d3ce' align='center' class='title-font'>
                    <?php
                        printf("��%d����%d���١�ê����(��̳���ɾ����)����Ͽ\n",$ki,$tuki);
                    ?>
                </td>
                <td bgcolor='#d6d3ce' align='center' width='140' class='today-font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
        <form name='invent' action='profit_loss_inventory_put.php' method='post' onSubmit='return invent_input(this)'>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffa4a4'>
                        ���ץ�ê����<input type='text' name='invent_c' size='15' maxlength='11' value='<?php echo $c_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4'>
                        ��˥�ê����<input type='text' name='invent_l' size='15' maxlength='11' value='<?php echo $l_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <BR><BR>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        �������ê����<input type='text' name='invent_zai' size='15' maxlength='11' value='<?php echo $zai_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        ��������ê����<input type='text' name='invent_buhin' size='15' maxlength='11' value='<?php echo $buhin_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        ������ê����<input type='text' name='invent_kou' size='15' maxlength='11' value='<?php echo $kou_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        ������ê����<input type='text' name='invent_gai' size='15' maxlength='11' value='<?php echo $gai_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        ������ê����<input type='text' name='invent_ken' size='15' maxlength='11' value='<?php echo $ken_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        ����ã�ê����<input type='text' name='invent_cc' size='15' maxlength='11' value='<?php echo $cc_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        ������Ωê����<input type='text' name='invent_kumi' size='15' maxlength='11' value='<?php echo $kumi_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        ����ê������<input type='text' name='invent_ctokut' size='15' maxlength='11' value='<?php echo $ctokut_kin ?>' class='rightg' readonly>
                    </td>
                </tr>
            </table>
            <BR><BR>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        �ġ������ê����<input type='text' name='invent_tzai' size='15' maxlength='11' value='<?php echo $tzai_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        �ġ�������ê����<input type='text' name='invent_tbuhin' size='15' maxlength='11' value='<?php echo $tbuhin_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        �ġ��빩��ê����<input type='text' name='invent_tkou' size='15' maxlength='11' value='<?php echo $tkou_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        �ġ��볰��ê����<input type='text' name='invent_tgai' size='15' maxlength='11' value='<?php echo $tgai_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        �ġ��븡��ê����<input type='text' name='invent_tken' size='15' maxlength='11' value='<?php echo $tken_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        �ġ���ã�ê����<input type='text' name='invent_tcc' size='15' maxlength='11' value='<?php echo $tcc_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        �ġ�����Ωê����<input type='text' name='invent_tkumi' size='15' maxlength='11' value='<?php echo $tkumi_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        �ġ���ê������<input type='text' name='invent_toolt' size='15' maxlength='11' value='<?php echo $toolt_kin ?>' class='rightg' readonly>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        <input type='submit' name='touroku' value='�¹�' >
                    </td>
                </tr>
            </table>
        </form>
    </center>
</BODY>
</HTML>
