<?php
//////////////////////////////////////////////////////////////////////////
//  �»�״ط� �����Ĵ�����ϵڤ���Ͽ                               //
//  2003/03/10   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp         //
//  �ѹ�����                                                            //
//  2003/03/10 ��������  profit_loss_adjust_uriage.php                  //
//  2003/03/10 Excel ���ԡ��� �����ɲ�(Ĵ���ѥǡ���) ñ���ѹ��б�     //
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
///// ɽ��ñ�̤��������
if (isset($_POST['uriage_tani'])) {
    $_SESSION['uriage_tani'] = $_POST['uriage_tani'];
    $tani = $_SESSION['uriage_tani'];
} elseif (isset($_SESSION['uriage_tani'])) {
    $tani = $_SESSION['uriage_tani'];
} else {
    $tani = 1000;        // ����� ɽ��ñ�� ���
    $_SESSION['uriage_tani'] = $tani;
}
///// ɽ�� ��������� �������
if (isset($_POST['uriage_keta'])) {
    $_SESSION['uriage_keta'] = $_POST['uriage_keta'];
    $keta = $_SESSION['uriage_keta'];
} elseif (isset($_SESSION['uriage_keta'])) {
    $keta = $_SESSION['uriage_keta'];
} else {
    $keta = 3;          // ����� �������ʲ����
    $_SESSION['uriage_keta'] = $keta;
}

/********** �ǡ����μ����ڤ���Ͽ���� **********/
if (!isset($_POST['touroku'])) {     // �ǡ�������
    ////////// ��Ͽ�Ѥߤʤ��Ĵ����ۡ�Ĵ����ͳ ����
    $query = sprintf("select kin, reason from act_adjust_history where pl_bs_ym=%d and note='���ץ�����Ĵ��'", $yyyymm);
    $res = array();
    if (getResult($query,$res) > 0) {
        $c_kin    = $res[0]['kin'];
        $reason_c = $res[0]['reason'];
        if ($c_kin > 0) {
            $c_kin = ('+' . $c_kin);
        }
    } else {
        $c_kin    = "";
        $reason_c = "";
    }
    $query = sprintf("select kin, reason from act_adjust_history where pl_bs_ym=%d and note='��˥�����Ĵ��'", $yyyymm);
    $res = array();
    if (getResult($query,$res) > 0) {
        $l_kin    = $res[0]['kin'];
        $reason_l = $res[0]['reason'];
        if ($l_kin > 0) {
            $l_kin = ('+' . $l_kin);
        }
    } else {
        $l_kin    = "";
        $reason_l = "";
    }
} else {                            // ��Ͽ����
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='���ץ�����Ĵ��'", $yyyymm);
    if (getUniResult($query,$c_kin) <= 0) {
        $query = sprintf("insert into act_adjust_history (pl_bs_ym, kin, note, reason) values (%d, %d, '���ץ�����Ĵ��', '%s')", $yyyymm, $_POST['adjust_c'], $_POST['reason_c']);
        query_affected($query);
    } else {
        $query = sprintf("update act_adjust_history set kin=%d, reason='%s' where pl_bs_ym=%d and note='���ץ�����Ĵ��'", $_POST['adjust_c'], $_POST['reason_c'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='��˥�����Ĵ��'", $yyyymm);
    if (getUniResult($query,$c_kin) <= 0) {
        $query = sprintf("insert into act_adjust_history (pl_bs_ym, kin, note, reason) values (%d, %d, '��˥�����Ĵ��', '%s')", $yyyymm, $_POST['adjust_l'], $_POST['reason_l']);
        query_affected($query);
    } else {
        $query = sprintf("update act_adjust_history set kin=%d, reason='%s' where pl_bs_ym=%d and note='��˥�����Ĵ��'", $_POST['adjust_l'], $_POST['reason_l'], $yyyymm);
        query_affected($query);
    }
    $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>���� Ĵ�����ϴ�λ<br>�� %d�� %d��</font>",$ki,$tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/***** ��    ��    �� *****/
$res = array();                     ///// ���η�����Ǻ��줿�ǡ��������
$query = sprintf("select ����, ���ץ�, ��˥� from wrk_uriage where ǯ��=%d", $yyyymm);
if ((getResult($query,$res)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    $uri   = $res[0]['����'];
    $uri_c = $res[0]['���ץ�'];
    $uri_l = $res[0]['��˥�'];
        ///// Ĵ���ǡ����μ���
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note like '%%����Ĵ��'", $yyyymm); // ����
    getUniResult($query, $adjust_all);
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='���ץ�����Ĵ��'", $yyyymm); // ���ץ�
    getUniResult($query, $adjust_c);
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='��˥�����Ĵ��'", $yyyymm); // ��˥�
    getUniResult($query, $adjust_l);
        ///// Ĵ�����å� END
    $uriage   = number_format($uri);
    $uriage_c = number_format($uri_c);
    $uriage_l = number_format($uri_l);
    $view_uriage = number_format((($uri + ($adjust_all)) / $tani), $keta);    // �ޥ��ʥ����θ����()����Ѥ���
    $view_data_c = number_format((($uri_c + ($adjust_c)) / $tani), $keta);
    $view_data_l = number_format((($uri_l + ($adjust_l)) / $tani), $keta);
} else {
    $uriage   = "̤��Ͽ";
    $uriage_c = "̤��Ͽ";
    $uriage_l = "̤��Ͽ";
    $view_data_c = "------";
    $view_data_l = "------";
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
<TITLE>�����Ĵ������</TITLE>
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
    if(!obj.adjust_c.value.length){
        alert("���ץ��Ĵ���ۤ�����Ǥ���");
        obj.adjust_c.focus();
        obj.adjust_c.select();
        return false;
    }
    if(!isPlusMinus(obj.adjust_c.value)){
        alert("Ƭ�ˡܡݤ������դ��Ʋ�������\nĴ�����ʤ����ϣ�������Ʋ�������");
        obj.adjust_c.focus();
        obj.adjust_c.select();
        return false;
    }
    if(isDigit(obj.adjust_c.value)){
        alert("���Ͱʳ������Ͻ���ޤ���");
        obj.adjust_c.focus();
        obj.adjust_c.select();
        return false;
    }
    if(!obj.adjust_l.value.length){
        alert("��˥���Ĵ���ۤ�����Ǥ���");
        obj.adjust_l.focus();
        obj.adjust_l.select();
        return false;
    }
    if(!isPlusMinus(obj.adjust_l.value)){
        alert("Ƭ�ˡܡݤ������դ��Ʋ�������\nĴ�����ʤ����ϣ�������Ʋ�������");
        obj.adjust_l.focus();
        obj.adjust_l.select();
        return false;
    }
    if(isDigit(obj.adjust_l.value)){
        alert("���Ͱʳ������Ͻ���ޤ���");
        obj.adjust_l.focus();
        obj.adjust_l.select();
        return false;
    }
    return true;
}
function set_focus(){
    document.adjust.adjust_c.focus();
    document.adjust.adjust_c.select();
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
.pt9 {
    font-size: 9pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt11 {
    font-size: 11pt;
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
                        printf("��%d����%d���١��� �� �� Ĵ���ۤ�����\n",$ki,$tuki);
                    ?>
                </td>
                <td bgcolor='#d6d3ce' align='center' width='140' class='today-font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
        <form name='adjust' action='profit_loss_adjust_uriage.php' method='post' onSubmit='return adjust_input(this)'>
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
                        ���ץ�����Ĵ����<input type='text' name='adjust_c' size='15' maxlength='11' value='<?php echo $c_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='pt12b'>
                        ��˥�����Ĵ����<input type='text' name='adjust_l' size='15' maxlength='11' value='<?php echo $l_kin ?>' class='right'>
                    </td>
                    <td align='center'>��</td> <!-- ;�� -->
                </tr>
                <tr>
                    <td align='left' class='pt11'>
                        ����ͳ<input type='text' name='reason_c' size='50' maxlength='50' value='<?php echo $reason_c ?>' class='pt9'>
                    </td>
                    <td align='left' class='pt11'>
                        ����ͳ<input type='text' name='reason_l' size='50' maxlength='50' value='<?php echo $reason_l ?>' class='pt9'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4'>
                        <input type='submit' name='touroku' value='�¹�' class='pt11b'>
                    </td>
                </tr>
            </table>
        </form>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <caption class='pt12b'>����˥塼�η�ǡ����������� �Ȳ�</caption>
            <th bgcolor='#ffff94' width='160' class='pt11b'>�ࡡ����</th> <!-- �������� -->
            <th bgcolor='#ffff94' width='160' class='pt11b'>���ץ���</th>
            <th bgcolor='#ffff94' width='160' class='pt11b'>��˥����</th>
            <th bgcolor='#ffff94' width='160' class='pt11b'>��׶��</th>
            <tr>
                <td align='center' class='pt11b'>�䡡�塡��</td>
                <td align='right' class='pt11b'><?php echo $uriage_c ?></td>
                <td align='right' class='pt11b'><?php echo $uriage_l ?></td>
                <td align='right' class='pt11b'><?php echo $uriage ?></td>
            </tr>
        </table>
        <form method='post' action='profit_loss_adjust_uriage.php'>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <caption class='pt12b'>���� Excel ���ԡ��� (Ĵ����)</caption>
                <th bgcolor='#ffff94' width='160' class='pt11b'>�ࡡ����</th> <!-- �������� -->
                <th bgcolor='#ffff94' width='160' class='pt11b'>���ץ���</th>
                <th bgcolor='#ffff94' width='160' class='pt11b'>��˥����</th>
                <th bgcolor='#ffff94' class='pt11b'>ñ�� ��� �ѹ�</th>
                <tr>
                    <td align='center' class='pt11b'>����(���ԡ���)</td>
                    <td align='right' class='pt11b'><?php echo $view_data_c ?></td>
                    <td align='right' class='pt11b'><?php echo $view_data_l ?></td>
                    <td colspan='13' nowrap bgcolor='#d6d3ce' align='right' class='pt10'>
                        ñ��
                        <select name='uriage_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>�����</option>\n";
                            else
                                echo "<option value='1000'>�����</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>������</option>\n";
                            else
                                echo "<option value='1'>������</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>ɴ����</option>\n";
                            else
                                echo "<option value='1000000'>ɴ����</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>������</option>\n";
                            else
                                echo "<option value='10000'>������</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>������</option>\n";
                            else
                                echo "<option value='100000'>������</option>\n";
                        ?>
                        </select>
                        ������
                        <select name='uriage_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>����</option>\n";
                            else
                                echo "<option value='0'>����</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>����</option>\n";
                            else
                                echo "<option value='3'>����</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>����</option>\n";
                            else
                                echo "<option value='6'>����</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>����</option>\n";
                            else
                                echo "<option value='1'>����</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>����</option>\n";
                            else
                                echo "<option value='2'>����</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>����</option>\n";
                            else
                                echo "<option value='4'>����</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>����</option>\n";
                            else
                                echo "<option value='5'>����</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='ñ���ѹ�'>
                    </td>
                </tr>
            </table>
        </form>
    </center>
</BODY>
</HTML>
