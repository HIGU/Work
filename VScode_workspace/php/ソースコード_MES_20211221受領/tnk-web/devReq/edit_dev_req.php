<?php
//////////////////////////////////////////////////////////////////////////
// �ץ���೫ȯ����� �Ȳ�&�Խ�                                       //
// 2002/02/12 Copyright(C)2002-2003 Kobayashi tnksys@nitto-kohki.co.jp  //
// �ѹ�����                                                             //
// 2002/08/09 register_globals = Off �б�                               //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼̾����Ѥ���      //
//////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
// require("../define.php");
require_once ("../tnk_func.php");
// $sysmsg = $_SESSION["s_sysmsg"];
// $_SESSION["s_sysmsg"] = NULL;
access_log();                       // Script Name �ϼ�ư����
// $_SESSION["edit_dev_req"] = date("H:i");
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
    $_SESSION["s_sysmsg"] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ�����Login ��ľ���Ʋ�������";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}
// session_register("s_dev_req_No","s_dev_req_sdate","s_dev_req_edate","s_dev_req_section","s_dev_req_client");
// session_register("s_rec_No","s_dev_current_rec");        //ɽ���ѥ쥳���ɭ�
if ($_POST["view_dev_req"]) {                       //dev_req_select�Υܥ���view_dev_req
    $_SESSION["s_dev_req_No"]      = $_POST["dev_req_No"];  //�������ѿ�����¸�ϸƽи����֤�����
    $_SESSION["s_dev_req_sdate"]   = $_POST["dev_req_sdate"];
    $_SESSION["s_dev_req_edate"]   = $_POST["dev_req_edate"];
    $_SESSION["s_dev_req_section"] = $_POST["s_dev_req_section"];
    $_SESSION["s_dev_req_client"]  = $_POST["dev_req_client"];
    $_SESSION["s_dev_req_sort"]    = $_POST["dev_req_sort"];
    $_SESSION["s_dev_req_kan"]     = $_POST["dev_req_kan"];
    $dev_req_No      = $_POST["dev_req_No"];                //�������ѿ�������
    $dev_req_sdate   = $_POST["dev_req_sdate"];
    $dev_req_edate   = $_POST["dev_req_edate"];
    $dev_req_section = $_POST["s_dev_req_section"];
    $dev_req_client  = $_POST["dev_req_client"];
    $dev_req_sort    = $_POST["dev_req_sort"];
    $dev_req_kan     = $_POST["dev_req_kan"];
} else {
    $dev_req_No      = $_SESSION["s_dev_req_No"];       //�������ѿ�����¸�ϼ��ؤ����ؤΥܥ��������Τ���
    $dev_req_sdate   = $_SESSION["s_dev_req_sdate"];    //�ޤ�UPDATE�����ѿ������Τ���dev_req_update
    $dev_req_edate   = $_SESSION["s_dev_req_edate"];
    $dev_req_client  = $_SESSION["s_dev_req_client"];
    $dev_req_sort    = $_SESSION["s_dev_req_sort"];
    $dev_req_kan     = $_SESSION["s_dev_req_kan"];
}
$Auth = $_SESSION["Auth"];

if ($Auth>=3) {
    define("DISP_ROWS",5);
} else {
    define("DISP_ROWS",20);
}
if ($_POST["backward"] == "����") {
    $_SESSION["s_rec_No"] -= (DISP_ROWS+DISP_ROWS);
    if ($_SESSION["s_rec_No"] < 0) {
        $_SESSION["s_rec_No"] = 0;
    }
}
$s_rec_No = $_SESSION["s_rec_No"];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>TNK ��ȯ�����Ȳ��Խ�</TITLE>
<style type="text/css">
<!--
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
.pt         {font-size:11pt;}
.fontred        {color:red;}
.textright      {text-align:right;}
-->
</style>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>
</HEAD>
<BODY>
<table width=100%>
    <tr><td bgcolor="#003e7c" align="center">
        <font color="#ffffff" size="5">�ץ���೫ȯ�������</font></td></tr>
</table>
<?php
    //              0    1      2        3      4    5    6        7    8      9      10      11        12     13
    $query="select �ֹ�,������,��������,�����,��Ū,����,ͽ�۸���,����,ͥ����,��ȶ�,��Ľ����,��ȯ����,��λ��,ô���� from dev_req ";
    if($dev_req_kan=="����"){
        $query .= "where ��λ��!='2050-01-01' ";                // ���Ƥ�����
        $dsp_kan = "��λ��ʬ������";
    }elseif($dev_req_kan=="̤��λ"){
        $query .= "where (��λ��='1970-01-01' or ��λ��=NULL) and ͥ����!='X' ";    // ̤��λʬ(Linux�Υ���������)
        $dsp_kan = "��λ��ʬ��̤��λ";
    }elseif($dev_req_kan=="��α¾"){
        $query .= "where ͥ����='X' ";                  // ��α������¾ʬ
        $dsp_kan = "��λ��ʬ����α¾";
    }else{
        $query .= "where ��λ��>'1998-01-01' ";                 // ��λʬ(�����ʴ�λ���դΤ��)
        $dsp_kan = "��λ��ʬ�ᴰλʬ";
    }
    if($dev_req_No!=""){
        $query .= "and �ֹ�=$dev_req_No ";
    }elseif($dev_req_client!="" || $dev_req_sdate!="" || $dev_req_edate!=""){
        $query .= "and ";
        if($dev_req_client!=""){
            $query .= "�����='$dev_req_client' ";
            if($dev_req_sdate!=""){
                $query .= "and ������>='$dev_req_sdate' ";
                if($dev_req_edate!=""){
                    $query .= "and ������<='$dev_req_edate' ";
                }
            }
        }elseif($dev_req_sdate!=""){
            $query .= "������>='$dev_req_sdate' ";
            if($dev_req_edate!=""){
                $query .= "and ������<='$dev_req_edate' ";
            }
        }elseif($dev_req_edate!=""){
            $query .= "������<='$dev_req_edate' ";
        }
    }
    $query .= "and del_flag<>TRUE ";        // ����ե饰�����Ǥʤ����
    
    if($dev_req_sort=="������"){
        $query .= "order by ������";
        $dsp_sort = "��������";
    }elseif($dev_req_sort=="��������"){
        $query .= "order by ��������";
        $dsp_sort = "���������";
    }elseif($dev_req_sort=="�����"){
        $query .= "order by �����";
        $dsp_sort = "����Խ�";
    }elseif($dev_req_sort=="��λ��"){
        $query .= "order by ��λ�� desc";
        $dsp_sort = "��λ����";
    }elseif($dev_req_sort=="��ȯ����"){
        $query .= "order by ��ȯ���� desc";
        $dsp_sort = "��ȯ������";
    }elseif($dev_req_sort=="�ֹ�"){
        $query .= "order by �ֹ�";
        $dsp_sort = "�����ֹ��";
    }
?>

<table width="100%">
    <hr color="navy">
    <script language="JavaScript" src="./dev_req.js">
    </script>
<?php
    $field=array();
    $res=array();
    if(($rows=getResultWithField($query,$field,$res))>=0){
        $num=count($field);
        for($r=0;$r<$rows;$r++){                // �ƥ쥳���ɤι�׹����򻻽�
            for($n=0;$n<$num;$n++){
                if($n==11){
                    $t_kousuu  += $res[$r][$n]; // ��׹���
                }
            }
        }
        $ft_kousuu = number_format($t_kousuu);                    // ���头�ȤΥ���ޤ��ղ�
        $f_rows = number_format($rows);
        if($dev_req_sdate)
            $f_d_start = format_date($dev_req_sdate);
        else
            $f_d_start = "�ǽ�";
        if($dev_req_edate)
            $f_d_end = format_date($dev_req_edate);    // ���դ� / �ǥե����ޥå�
        else
            $f_d_end = "�Ǹ�";
        print "<tr>\n";
        print "<td align=\"center\" nowrap><b><u>$dsp_sort �� $dsp_kan �� �������� $f_d_start �� $f_d_end 
            �� ��׷����$f_rows �� ��׹�����$ft_kousuu <u><b></td>\n";
        print "</tr></table>\n";
        
        print "<div align='center'><table><tr>\n";
        print("<form method='post' action='dev_req_select.php'>\n");
            print("<td><input type='submit' value='���' name='return'></td>\n");
        print("</form>\n");
        if($rows >= ($s_rec_No + DISP_ROWS) )
            $limits = ($s_rec_No + DISP_ROWS);           // ����ɽ����DISP_ROWS�ԤޤǤ�����
        else
            $limits = $rows;
        if($s_rec_No >= 1){
            $back_logic = TRUE;
            print("<form method='post' action='edit_dev_req.php'>\n");
                print("<td><input type='submit' value='����' name='backward'></td>\n");
            print("</form>\n");
        }
        if( ($s_rec_No + DISP_ROWS) < $rows){
            $for_logic = TRUE;
            print("<form method='post' action='edit_dev_req.php'>\n");
                print("<td><input type='submit' value='����' name='forward'></td>\n");
            print("</form>\n");
        }
        print("</tr></table></div>\n");
        
/*  debug   echo("<tr><td>�¹ԥ����꡼  " . $query . "</td></tr>"); */
        echo("<table border=\"1\" bgcolor=\"#e6e6fa\" cellspacing=\"0\" cellpadding=\"2\">");
        echo("\n<tr bgcolor=\"add8e6\" align=\"center\">\n");       // �ե������̾ ��Գ���
        print "<th nowrap>No.</th>\n";                              // �쥳�����ֹ��ɲ�
        for($n=0;$n<$num;$n++)
            echo("<th nowrap>" . $field[$n] . "</th>\n");
        echo("</tr>\n");                                            // �ե������̾ ��Խ�λ
        for($r=$s_rec_No;$r<$limits;$r++){
            if($Auth>=3)        //���¤�Administrator�ξ��
                print("\n<form method='post' action='dev_req_update.php' onSubmit='return chk_dev_req_edit(this)'>\n");
            echo("<tr>\n");
            $No = $r + 1;
            print("<td align='right' nowrap> $No </td>\n");    // ɽ�˥쥳�����ֹ��ɲ�
            for($n=0;$n<$num;$n++){
                if($Auth>=3){   // ���¤�Administrator�ξ��
                    if($n==0)
                        print("<td align='right'><input type='submit' value='" . $res[$r][$n] . "' name='update_No'></td>\n");
                    if($n==1)
                        print("<td align='center'><input type='text' name='iraibi' size='12' maxlength='10' value='" . $res[$r][$n] . "'></td>\n");
                    if($n==2){
                        print("<td align='center'><select name='iraibusho'>\n");
                        $query_section="select * from section_master order by sid asc";
                        $res_section=array();
                        if($rows_section=getResult($query_section,$res_section)){
                            for($i=0;$i<$rows_section;$i++){
                                echo("<option ");
                                if($res[$r][$n]==$res_section[$i][0])    // �ʤ��� sid ���Ȥ��������� 0 �ˤ�����
                                    echo("selected ");
                                echo("value='" . $res_section[$i][0] . "'>" . rtrim($res_section[$i][section_name]) . "</option>\n");
                            }
                        }
                        print("</select></td>\n");
                    }
                    if($n==3){
                        print("<td align='center'><input type='text' name='iraisya' size='7' maxlength='6' value='" . ltrim($res[$r][$n]) . "'>\n");
                        $query_user="select name from user_detailes where uid='" . $res[$r][$n] . "'";
                        $res_user=array();
                        if($rows_user=getResult($query_user,$res_user))
                            print("<font size='1'>" . rtrim($res_user[0][name]) . "</font></td>\n");
                        else
                            print("-</td>\n");
                    }
                    if($n==4)
                        echo("<td align='left'><textarea name='mokuteki' 
                        cols='20' rows='3' wrap='soft'>" . $res[$r][$n] . "</textarea></td>\n");
                    if($n==5)
                        echo("<td align='left'><textarea name='naiyou' 
                        cols='30' rows='3' wrap='soft'>" . $res[$r][$n] . "</textarea></td>\n");
                    if($n==6)
                        print("<td align='center'><input type='text' class='textright' name='yosoukouka' size='12' maxlength='9' value='" . $res[$r][$n] . "'></td>\n");
                    if($n==7)
                        echo("<td align='left'><textarea name='bikou' 
                        cols='20' rows='3' wrap='soft'>" . $res[$r][$n] . "</textarea></td>\n");
                    
                    
                    
                    
                    
                    if($n==8){
                        print("<td align='center'><select name='yuusendo'>\n");
                        
                        print("<option value='A'");
                        if($res[$r][$n]=="A")
                            print("selected>Aͥ��</option>\n");
                        else
                            print(">Aͥ��</option>\n");
                        
                        print("<option value='B'");
                        if($res[$r][$n]=="B")
                            print("selected>B�̾�</option>\n");
                        else
                            print(">B�̾�</option>\n");
                        
                        print("<option value='X'");
                        if($res[$r][$n]=="X")
                            print("selected>X���</option>\n");
                        else
                            print(">X���</option>\n");
                        
                        print("<option value=' ' class='fontred'");
                        if(ltrim($res[$r][$n])=="")
                            print("selected><font color='red'>---</font></option>\n");
                        else
                            print(">---</option>\n");
                    }
                    if($n==9){
                        print("<td align='center'><select name='sagyouku'>\n");
                        
                        print("<option value='1'");
                        if($res[$r][$n]=="1")
                            print("selected>1��ȯ</option>\n");
                        else
                            print(">1��ȯ</option>\n");
                        
                        print("<option value='2'");
                        if($res[$r][$n]=="2")
                            print("selected>2¾</option>\n");
                        else
                            print(">2¾</option>\n");
                        
                        print("<option value=' ' class='fontred'");
                        if(ltrim($res[$r][$n])=="")
                            print("selected>̤��</option>\n");
                        else
                            print(">̤��</option>\n");
                    }
                    if($n==10)
                        echo("<td align='left'><textarea name='sintyoku' 
                        cols='30' rows='3' wrap='soft'>" . $res[$r][$n] . "</textarea></td>\n");
                    if($n==11)
                        print("<td align='center'><input type='text' class='textright' name='kousuu' size='7' maxlength='6' value='" . $res[$r][$n] . "'></td>\n");
                    if($n==12)
                        print("<td align='center'><input type='text' name='kanryou' size='12' maxlength='10' value='" . $res[$r][$n] . "'></td>\n");
                    if($n==13){
                        print("<td align='center'><select name='tantou'>\n");
                        print("<option value='010561'");
                        if($res[$r][$n]=="010561")
                            print("selected>����</option>");
                        else
                            print(">����</option>");
                        print("<option value='016713'");
                        if($res[$r][$n]=="016713")
                            print("selected>�滳</option>");
                        else
                            print(">�滳</option>");
                        print("<option value=''");
                        if($res[$r][$n]=="" || $res[$r][$n]=="      ") //�����""�������������"      "�ˤʤ뤿��
                            print("selected>Blank</option>");
                        else
                            print(">Blank</option>");
                        print("</select></td>\n");
                    }
                }else{                  // ¾�Υ桼�����ξ��
                    if(ltrim($res[$r][$n])==""){
                        echo("<td align=\"center\" nowrap>-</td>\n");
                    }else{
                        if($n==2){
                            $query_section="select * from section_master where sid=" . $res[$r][$n] ;
                            $res_section=array();
                            if($rows_section=getResult($query_section,$res_section))
                                print("<td align='center'><font size='2'>" . rtrim($res_section[0][section_name]) . "</font></td>\n");
                            else
                                print("<td align='center' nowrap>-</td>\n");
                        }elseif($n==3){
                            $query_user="select name from user_detailes where uid='" . $res[$r][$n] . "'";
                            $res_user=array();
                            if($rows_user=getResult($query_user,$res_user))
                                print("<td align='center'><font size='1'>" . rtrim($res_user[0][name]) . "</font></td>\n");
                            else
                                print("<td align='center' nowrap>-</td>\n");
                        }elseif(($n==6 || $n==11) && $res[$r][$n]==0)
                            echo("<td align='center' nowrap>-</td>\n");
                        elseif($n==4 || $n==5 || $n==7 || $n==10)
                            echo("<td align='left'><textarea name='mokuteki' 
                            cols='30' rows='3' wrap='soft'>" . $res[$r][$n] . "</textarea></td>\n");
                        elseif($n==8){
                            if($res[$r][$n]=="A")
                                echo("<td align='center' nowrap>ͥ��</td>\n");
                            elseif($res[$r][$n]=="B")
                                echo("<td align='center' nowrap>�̾�</td>\n");
                            elseif($res[$r][$n]=="X")
                                echo("<td align='center' nowrap>���</td>\n");
                            else
                                echo("<td align='center' nowrap><font color='red'>̤����</font></td>\n");
                        }
                        elseif($n==9){
                            if($res[$r][$n]=="1")
                                echo("<td align='center' nowrap>��ȯ</td>\n");
                            else
                                echo("<td align='center' nowrap>����¾</td>\n");
                        }
                        elseif($n==12)
                            if($res[$r][$n]=="1970-01-01")  //Date���Υ���������1970�ʤ�̤��λ
                                echo("<td align='center'>-</td>\n");
                            else
                                echo("<td align='right'>" . $res[$r][$n] . "</td>\n");
                        elseif($n==13){
                            $query_user="select name from user_detailes where uid='" . $res[$r][$n] . "'";
                            $res_user=array();
                            if($rows_user=getResult($query_user,$res_user))
                                print("<td align='right'><font size='1'>" . rtrim($res_user[0][name]) . "</font></td>\n");
                            else
                                print("<td align='center'>-</td>\n");
                        }else
                            echo("<td align='right'>" . $res[$r][$n] . "</td>\n");
                    }
                }
            }
            echo("</tr>\n");
            if($Auth>=3)        //���¤�Administrator�ξ��
                print("</form>\n");
        }
        $_SESSION["s_dev_current_rec"] = $s_rec_No;
        $_SESSION["s_rec_No"] = $No;        // ���Υ쥳���ɭ�˥��å�
        echo("</table>\n");
    }else{
        echo("<tr><td>�¹ԥ����꡼  " . $query);
        echo("<font size=-1 color='#ff7e00'><br>�ǡ����١����ؤ��䤤��碌�˼��Ԥ��ޤ�����");
        echo("<br>��³�Υץ�ѥƥ����ǧ���Ʋ�������</font></td></tr>");
    }
    print "<div align='center'><table>\n";
    print "<tr>\n";
        print("<form method='post' action='dev_req_select.php'>\n");
            print("<td><input type='submit' value='���' name='return'></td>\n");
        print("</form>\n");
        if($back_logic){
            print("<form method='post' action='edit_dev_req.php'>\n");
                print("<td><input type='submit' value='����' name='backward'></td>\n");
            print("</form>\n");
        }
        if($for_logic){
            print("<form method='post' action='edit_dev_req.php'>\n");
                print("<td><input type='submit' value='����' name='forward'></td>\n");
            print("</form>\n");
        }
    print "</tr>\n";
    print("</table></div>\n");
?>
</BODY>
</HTML>
