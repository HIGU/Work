<?php
//////////////////////////////////////////////////////////////////////////
//  �»�״ط� ��帶����Ĵ�����ϵڤ���Ͽ                             //
//  2003/02/24   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp         //
//  �ѹ�����                                                            //
//  2003/02/24 ��������  profit_loss_adjust_ugenka.php                  //
//  2003/02/24 ��Ͽ�Ѥߤ�AS/400 ��帶���򻲹ͤȤ���ɽ��(�Ȳ�)������    //
//  2003/03/10 Ĵ�������帶�� �Ȳ���ɲ�                              //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name �ϼ�ư����
$_SESSION["site_index"] = 10;       // �»�״ط�=10 �Ǹ�Υ�˥塼�� 99 �����
$_SESSION["site_id"] = 7;           // ���̥�˥塼̵�� (0 <=)
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
}
//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:m:s");

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
    ////////// ��Ͽ�Ѥߤʤ��Ĵ����ۡ�Ĵ����ͳ ����
    $query = sprintf("select kin, reason from act_adjust_history where pl_bs_ym=%d and note='��帶��Ĵ��'", $yyyymm);
    $res = array();
    if (getResult($query,$res) > 0) {
        $adjust = $res[0]['kin'];
        $reason = $res[0]['reason'];
        if ($adjust > 0) {
            $adjust = ('+' . $adjust);
        }
    } else {
        $adjust = "";
        $reason = "";
    }
} else {                            // ��Ͽ����
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='��帶��Ĵ��'", $yyyymm);
    if (getUniResult($query,$res) <= 0) {
        $query = sprintf("insert into act_adjust_history (pl_bs_ym, kin, note, reason) values (%d, %d, '��帶��Ĵ��', '%s')", $yyyymm, $_POST['adjust'], $_POST['reason']);
        query_affected($query);
    } else {
        $query = sprintf("update act_adjust_history set kin=%d, reason='%s' where pl_bs_ym=%d and note='��帶��Ĵ��'", $_POST['adjust'], $_POST['reason'], $yyyymm);
        query_affected($query);
    }
    $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>��帶�� Ĵ�����ϴ�λ<br>�� %d�� %d��</font>",$ki,$tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// pl_bs_summary(AS/400) �����帶�� ����
$query = sprintf("select kin1 from pl_bs_summary where t_id='A' and t_row=2 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query,$data) > 0){
    $uri_genka = number_format($data);              // ������帶��
        ///// Ĵ���ǡ����μ���
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='��帶��Ĵ��'", $yyyymm); // ����
    getUniResult($query, $adjust);
        ///// Ĵ�����å� END
    $view_data = number_format($data + ($adjust));  // �ޥ��ʥ����θ����()�����
} else {
    $uri_genka = "̤��Ͽ";
    $view_data = "̤��Ͽ";
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
<TITLE>�ê����Ĵ������</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';

/* ����ʸ��Ƭ����� '+' '-' '0' �����å� */
function isPlusMinus(str) {
    var c = str.charAt(0);
    if ((c == '+') || (c == '-') || (c == '0')) {
        return true;
    }
    return false;
}
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
function adjust_input(obj){
    if(!obj.adjust.value.length){
        alert("��帶����Ĵ���ۤ�����Ǥ���");
        obj.adjust.focus();
        obj.adjust.select();
        return false;
    }
    if(!isPlusMinus(obj.adjust.value)){
        alert("Ƭ�ˡܡݤ������դ��Ʋ�������\nĴ�����ʤ����ϣ�������Ʋ�������");
        obj.adjust.focus();
        obj.adjust.select();
        return false;
    }
    if(isDigit(obj.adjust.value)){
        alert("���Ͱʳ������Ͻ���ޤ���");
        obj.adjust.focus();
        obj.adjust.select();
        return false;
    }
    return true;
}
function set_focus(){
    document.adjust_form.adjust.focus();
    document.adjust_form.adjust.select();
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
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
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
                        printf("��%d����%d���١�����ê���� Ĵ���ۤ�����\n",$ki,$tuki);
                    ?>
                </td>
                <td bgcolor='#d6d3ce' align='center' width='140' class='today-font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
        <form name='adjust_form' action='profit_loss_adjust_ugenka.php' method='post' onSubmit='return adjust_input(this)'>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td colspan='3' align='center' class='pt11'>
                        Ĵ����ۤ�Ƭ�ˡܡݤ�Ĥ������Ϥ��Ʋ�������
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt11'>
                        Ĵ�����ʤ����ϣ������Ϥ��Ʋ�������
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='pt12b'>
                        ���Τ���帶�� Ĵ����<input type='text' name='adjust' size='15' maxlength='11' value='<?php echo $adjust ?>' class='right'>
                    </td>
                    <td align='center'>��</td> <!-- ;�� -->
                </tr>
                <tr>
                    <td align='left' class='pt11'>
                        Ĵ����ͳ<input type='text' name='reason' size='100' maxlength='100' value='<?php echo $reason ?>' class='pt9'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4'>
                        <input type='submit' name='touroku' value='�¹�' class='pt11b'>
                    </td>
                </tr>
            </table>
        </form>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <caption class='pt12b'>AS/400 ��帶�� �Ȳ�</caption>
            <tr>
                <td bgcolor='#ffff94' width='300' align='center' class='pt12b'>���� ��帶��</td><!-- �������� -->
                <td  width='300' align='right' class='pt12b'><?php echo $uri_genka ?></td>
            </tr>
        </table>
        <br>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <caption class='pt12b'>Ĵ�������帶�� �Ȳ�</caption>
            <tr>
                <td bgcolor='#ffff94' width='300' align='center' class='pt12b'>���� ��帶��</td><!-- �������� -->
                <td  width='300' align='right' class='pt12b'><?php echo $view_data ?></td>
            </tr>
        </table>
    </center>
</BODY>
</HTML>
