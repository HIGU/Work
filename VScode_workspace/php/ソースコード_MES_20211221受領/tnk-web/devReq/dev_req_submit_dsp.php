<?php
//////////////////////////////////////////////////////////////////////////
// �ץ���೫ȯ����� ������̥ե�����                                //
// 2002/02/12 Copyright(C)2002-2003 Kobayashi tnksys@nitto-kohki.co.jp  //
// �ѹ�����                                                             //
// 2002/08/09   register_globals = Off �б�                             //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼̾����Ѥ���      //
//////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
// require("../define.php");
require_once ("../tnk_func.php");
$sysmsg = $_SESSION["s_sysmsg"];
$_SESSION["s_sysmsg"] = NULL;
access_log();                       // Script Name �ϼ�ư����
// $_SESSION["dev_req_submit_dsp"] = date("H:i");
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
    $_SESSION["s_sysmsg"] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ�����Login ��ľ���Ʋ�������";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>TNK ��ȯ����� ���� ��λ</TITLE>
<style type="text/css">
<!--
select      {background-color:teal; color:white;}
textarea        {background-color:black; color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
.pt         {font-size:11pt;}
-->
</style>
</HEAD>
<BODY>
<table width=100%>
    <tr><td bgcolor="#003e7c" align="center">
        <font color="#ffffff" size="4">��ȯ����� ���� ��λ</font>
    </td></tr>
</table>
<table width=100%>
    <hr color="navy">
</table>
<table width=100%>
    <tr>
    <form action='<?php echo DEV_MENU ?>' method='post'>
        <td align='center'><input type="submit" name="dev_chk_submit" value="���" ></td>
    </form>
    </tr>
</table>
<table width='100%' cellspacing='0' cellpadding='2' border='1' bgcolor='#e6e6fa'>
        <tr>
            <td align='center' width='20'>��</td>
            <td align='left' width='80'>�����</td>
            <td align='left'>
                <?php echo "<font color='red'><font size='6'><b>" . $_SESSION["s_dev_touroku"] 
                . "</b></font>�֤���������ޤ������Ȳ���̤��ֹ�����Ϥ��Ƴ�ǧ���Ʋ�������</font>\n" ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>��</td>
            <td align='left'>������</td>
            <td align='left'>
                <?php $iraibi=date("Y-m-d");echo $iraibi; ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>��</td>
            <td align='left'>��������</td>
            <td align="left">
                <?php
                    $query_section = "select * from section_master where sid = " . $_SESSION["s_dev_iraibusho"];
                    $res_section=array();
                    if($rows_section=getResult($query_section,$res_section))
                        print(rtrim($res_section[0][section_name]));
                    else
                        print($_SESSION["s_dev_iraibusho"]);
                ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>��</td>
            <td align='left'>�����</td>
            <td align="left">
                ����ԤμҰ���
                <?php
                    print $_SESSION["s_dev_iraisya"] . "\n";
                    $query_user = "select name from user_detailes where uid='" . $_SESSION["s_dev_iraisya"] . "'";
                    $res_user=array();
                    if($rows_user=getResult($query_user,$res_user))
                        print("<font size='3'>" . rtrim($res_user[0][name]) . "</font></td>\n");
                    else
                        print("--------\n");
                ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>��</td>
            <td align='left' width='80'>��Ū���ϥ����ȥ�</td>
            <td align='left'>
                <?php
                    print $_SESSION["s_dev_mokuteki"] . "\n";
                ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>��</td>
            <td align='left' width='80'>��  ��</td>
            <td align='left'>
                <?php
                    print $_SESSION["s_dev_naiyou"] . "\n";
                ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>��</td>
            <td align='left' nowrap>ͽ�۸���</td>
            <td align='left'>
                <?php
                    if($_SESSION["s_dev_yosoukouka"] == "")
                        print("-----\n");
                    else
                        print $_SESSION["s_dev_yosoukouka"] . " ʬ��ǯ\n";
                ?>
            </td>
        </tr>
        <tr>
            <td align='center' width='20'>��</td>
            <td align='left'>�׻�����������</td>
            <td align='left'>
                <?php
                    if($_SESSION["s_dev_bikou"] == "")
                        print("-----\n");
                    else
                        print($_SESSION["s_dev_bikou"] . "\n");
                ?>
            </td>
        </tr>
    </form>
</table>
<table width=100%>
    <form action='<?php echo DEV_MENU ?>' method='post'>
        <tr><td align='center'><input type="submit" name="dev_chk_submit" value="���" ></td></tr>
    </form>
</table>
</BODY>
</HTML>
