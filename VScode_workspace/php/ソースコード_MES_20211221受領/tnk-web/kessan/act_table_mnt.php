<?php
//////////////////////////////////////////////////////////////////////////
//  �������祳���ɡ�����Ψ���ݼ�                                        //
//  2002/09/17   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp         //
//  �ѹ�����                                                            //
//  2002/09/17 ��������                                                 //
//  2002/09/20 �����ȥ�˥塼���ɲ�                                     //
//  2002/09/28 ����Ψ�ơ��֥�� act_allocation & allocation_item ���ѹ� //
//  2002/10/05 ���ߤϻ��Ѥ��Ƥ��ʤ� act_table_mnt_new.php ���ѹ�        //
//  2003/02/26 body �� onLoad ���ɲä�������ϸĽ�� focus() ������     //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
access_log();       // Script Name �ϼ�ư����
$_SESSION["site_index"] = 10;       // �Ǹ�Υ�˥塼�ˤ��뤿�� 10 �����
$_SESSION["site_id"] = 1;       // �Ȥꤢ�������̥�˥塼̵�� (0 < �Ǥ���)
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
// if($_SESSION["Auth"] <= 2){
    $_SESSION["s_sysmsg"] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�Ƥ��ޤ���<br>ǧ�ڤ�����ľ���Ʋ�������";
    header("Location: http:" . WEB_HOST . "index.php");
    exit();
}
if (!isset($_POST['act_sel'])) {        // ���ꤵ��Ƥ��ʤ����ν����
    $_POST['act_sel'] = "";
}
if (!isset($_POST['act_id'])) {         // ���ꤵ��Ƥ��ʤ����ν����
    $_POST['act_id'] = "";
}
$today = date("Y-m-d");
$query = "select count(*) from act_table";
$res = array();
if ( ($rows=getResult($query,$res)) >= 1) {
    $maxrows = $res[0][0];
}
define("PAGE","4");
if ( isset($_POST['forward']) ) {
    $_SESSION['act_offset'] += PAGE;
    if ($_SESSION['act_offset'] >= $maxrows) {
        $_SESSION['act_offset'] = ($maxrows - 1);
    }
} elseif (isset($_POST['backward'])) {
    $_SESSION['act_offset'] -= PAGE;
    if ($_SESSION['act_offset'] < 0) {
        $_SESSION['act_offset'] = 0;
    }
} else {
    if ( !isset($_SESSION['act_offset']) ) {
        $_SESSION['act_offset'] = 0;
    }
}
$offset = $_SESSION['act_offset'];
///////////////////////////////////////// act_add �ɲ� ����
if ( isset($_POST['act_add']) ) {
    $query = "insert into act_table values (";
    $query .= $_POST['act_id'] . ",'" . $_POST['act_name'] . "','" . $_POST['s_name'] . "','$today',NULL,'t',";
    if ($_POST['s_g_exp'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_g_exp'] . ",";
    if($_POST['c_exp'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['c_exp'] . ",";
    if($_POST['l_exp'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['l_exp'] . ",";
    if($_POST['shoukan'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['shoukan'] . ",";
    if($_POST['c_assy'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['c_assy'] . ",";
    if($_POST['s_toku'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_toku'] . ",";
    if($_POST['s_1_nc'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_1_nc'] . ",";
    if($_POST['s_1_6'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_1_6'] . ",";
    if($_POST['s_4_nc'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_4_nc'] . ",";
    if($_POST['s_5_pf'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_5_pf'] . ",";
    if($_POST['s_5_2'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['s_5_2'] . ",";
    if($_POST['shape'] == "")
        $query .= "NULL)";
    else
        $query .= $_POST['shape'] . ")";
    
    $res = array();
    if(($rows=getResult($query,$res))>=0)
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . " : " . $_POST['act_name'] . "����Ͽ���ޤ�����";
    else
        $_SESSION['s_sysmsg'] = $res[0][0];
    // ----------------------------------- offset �ͤ�SET
    $query = "select act_id from act_table order by act_id ASC";
    $res = array();
    if(($rows=getResult($query,$res)) >= 1){
        for($i=0;$i<$rows;$i++){
            if($res[$i][0] == $_POST['act_id']){
                $_SESSION['act_offset'] = $i;
                $offset = $i;
            }
        }
    }
    // ----------------------------------- offset �� END
}
///////////////////////////////////////// act_chg �ѹ� ����
if ( isset($_POST['act_chg']) ) {
    $query = "update act_table set act_name='" . $_POST['act_name'] . "', s_name='" . $_POST['s_name'] . "',date_chg='$today', s_g_exp=";
    if($_POST['s_g_exp'] == "")
        $query .= "NULL, c_exp=";
    else
        $query .= $_POST['s_g_exp'] . ", c_exp=";
    if($_POST['c_exp'] == "")
        $query .= "NULL, l_exp=";
    else
        $query .= $_POST['c_exp'] . ", l_exp=";
    if($_POST['l_exp'] == "")
        $query .= "NULL, shoukan=";
    else
        $query .= $_POST['l_exp'] . ", shoukan=";
    if($_POST['shoukan'] == "")
        $query .= "NULL, c_assy=";
    else
        $query .= $_POST['shoukan'] . ", c_assy=";
    if($_POST['c_assy'] == "")
        $query .= "NULL, s_toku=";
    else
        $query .= $_POST['c_assy'] . ", s_toku=";
    if($_POST['s_toku'] == "")
        $query .= "NULL, s_1_nc=";
    else
        $query .= $_POST['s_toku'] . ", s_1_nc=";
    if($_POST['s_1_nc'] == "")
        $query .= "NULL, s_1_6=";
    else
        $query .= $_POST['s_1_nc'] . ", s_1_6=";
    if($_POST['s_1_6'] == "")
        $query .= "NULL, s_4_nc=";
    else
        $query .= $_POST['s_1_6'] . ", s_4_nc=";
    if($_POST['s_4_nc'] == "")
        $query .= "NULL, s_5_pf=";
    else
        $query .= $_POST['s_4_nc'] . ", s_5_pf=";
    if($_POST['s_5_pf'] == "")
        $query .= "NULL, s_5_2=";
    else
        $query .= $_POST['s_5_pf'] . ", s_5_2=";
    if($_POST['s_5_2'] == "")
        $query .= "NULL, shape=";
    else
        $query .= $_POST['s_5_2'] . ", shape=";
    if($_POST['shape'] == "")
        $query .= "NULL ";
    else
        $query .= $_POST['shape'] . " ";
    
    $query .= "where act_id=" . $_POST['act_id'];
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 0)
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . " : " . $_POST['act_name'] . "���ѹ����ޤ�����";
    else
        $_SESSION['s_sysmsg'] = $res[0][0];
    // ----------------------------------- offset �ͤ�SET
    $query = "select act_id from act_table order by act_id ASC";
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 1) {
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] == $_POST['act_id']) {
                $_SESSION['act_offset'] = $i;
                $offset = $i;
            }
        }
    }
    // ----------------------------------- offset �� END
}
///////////////////////////////////////// act_del �ѹ� ����
if ( isset($_POST['act_del']) ) {
    $query = "delete from act_table where act_id=";
    $query .= $_POST['act_id'] ;
    $res=array();
    if(($rows=getResult($query,$res))>=0)
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . "�������ޤ�����";
    else
        $_SESSION['s_sysmsg'] = $res[0][0];
/*  // ----------------------------------- offset �ͤ�SET
    $query = "select act_id from act_table order by act_id ASC";
    $res = array();
    if(($rows=getResult($query,$res)) >= 1){
        for($i=0;$i<$rows;$i++){
            if($res[$i][0] == $_POST['act_id']){
                $_SESSION['act_offset'] = $i;
                $offset = $i;
            }
        }
    }
*/      // ----------------------------------- offset �� END
}
///////////////////////////////////////// act_flg �ѹ� ����(ľ�ܡ���������)
if ( isset($_POST['act_flg']) ) {
    $res = array();
    if ($_POST['act_flg'] == "ľ������") {
        $query = "update act_table set act_flg='f' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "�����������ѹ����ޤ�����";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } else {
        $query = "update act_table set act_flg='t' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "��ľ��������ѹ����ޤ�����";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    }
}
///////////////////////////////////////// rate_flg �ѹ� ����(������Ψ�оݳ�(0)or(NULL) �о�(1) ��������(2))
if ( isset($_POST['rate_flg']) ) {
    $res = array();
    if ($_POST['rate_flg'] == "������Ψ����") {
        $query = "update act_table set rate_flg='1' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "�򵡳���Ψ�оݤ��ѹ����ޤ�����";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } elseif ($_POST['rate_flg'] == "������Ψ�о�") {
        $query = "update act_table set rate_flg='2' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "����Ψ����������ѹ����ޤ�����";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } else {
        $query = "update act_table set rate_flg='0' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "�򵡳���Ψ�������ѹ����ޤ�����";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    }
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>�������祳���ɡ�����Ψ���ݼ�</TITLE>
<script language='JavaScript'>
    <!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
    // -->
</script>
<script language='JavaScript' src='act_table_mnt.js'>
</script>
<style type="text/css">
    <!--
    select      {background-color:teal; color:white;}
    textarea        {background-color:black; color:white;}
    input.sousin    {background-color:red;}
    input.text      {background-color:black; color:white;}
    .pt11           {font-size:11pt;}
    .margin1        {margin:1%;}
    .margin0        {margin:0%;}
    .pt12b          {font:bold 12pt;}
    .y_b    {background-color:yellow; color:blue;}
    .r_b    {background-color:red; color:black;}
    .r_w    {background-color:red; color:white;}
    .b_w    {background-color:blue; color:white;}
    .fsp    {font-size:8pt;}
    .fmp    {font-size:10pt;}
    .flp    {font-size:12pt;}
    .fllbp  {font-size:16pt;font-weight:bold;}
    input.blue      {color:blue;}
    input.red       {color:red;}
    -->
</style>
</HEAD>
<BODY class='margin0' onLoad='document.select_form.act_id.focus()'>
    <center>
        <table width=100% border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <form method='post' action='kessan_menu.php'>
                    <td width='60' bgcolor='blue' align='center' valign='center'>
                        <input class='pt12b' type='submit' name='return' value='���'>
                    </td>
                </form>
                <td bgcolor='#d6d3ce' align='center' class='fllbp'>
                    �������祳���ɡ�����Ψ�Υ��ƥʥ�
                </td>
                <td bgcolor='#d6d3ce' align='center' width='80' nowrap>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
        <form name='select_form' method='post' action='act_table_mnt.php' onSubmit='return act_id_chk(this)'>
            <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
                <tr>
                    <td align='left' nowrap>
                        ��ʬ�����򤷤����祳���ɤ����Ϥ��¹ԥܥ���򲡤��Ʋ�������<br>
                        <table width='100%' align='center' border='1' cellspacing='0' cellpadding='2'>
                            <tr align='center'>
                                <td nowrap <?php if($_POST['act_sel']=="add") echo "class='b_w'" ?>><input type='radio' name='act_sel' value='add' id='add'
                                    <?php if($_POST['act_sel']=="add") echo(" checked") ?>><label for='add'>�ɲ�</label>
                                </td>
                                <td nowrap <?php if($_POST['act_sel']=="chg") echo "class='b_w'" ?>><input type='radio' name='act_sel' value='chg' id='chg'
                                    <?php if($_POST['act_sel']=="chg") echo(" checked") ?>><label for='chg'>�ѹ�</label>
                                </td>
                                <td nowrap <?php if($_POST['act_sel']=="del") echo "class='b_w'" ?>><input type='radio' name='act_sel' value='del' id='del'
                                    <?php if($_POST['act_sel']=="del") echo(" checked") ?>><label for='del'>���</label>
                                </td>
                        </table>
                        <div align='center'>
                            ���祳����<input type='text' name='act_id' size='7' maxlength='6' value='<?php echo $_POST['act_id'] ?>'>
                            <input type='submit' name='edit' value='�¹�' >
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    <?php
        if($_POST['act_sel'] == "add"){
            $query = "select act_id from act_table where act_id=" . $_POST['act_id'];
            $res = array();
            if(($rows=getResult($query,$res))>=1){      // ��Ͽ�ѤߤΥ����å�
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b' nowrap>\n";
                echo "          ���祳���ɤ�������Ͽ�ѤߤǤ����ѹ����Ϻ����¹Ԥ��Ʋ�������\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }else{
                echo "<form method='post' action='act_table_mnt.php' onSubmit='return act_chk(this)'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>������</th><th nowrap>�� �� ̾</th><th nowrap>û��̾</th><th nowrap>���ץ�</th><th nowrap>��˥�</th>
                        <th nowrap>�δ���</th><th nowrap>����</th><th colspan='5' align='left'>��ʬ��</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8'></td>\n";
                echo "      <td><input type='text' name='c_exp' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='l_exp' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_g_exp' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='shoukan' size='4' maxlength='3'></td>\n";
                echo "      <td colspan='5' align='left'>���Τ�100%�ˤʤ�褦��</td>\n";
                echo "  </tr>\n";
                echo "  <th colspan='3' align='right'>���ץ������ ����Ψ</th><th nowrap>����Ω</th><th nowrap>��¤��</th><th nowrap>��1-NC</th>
                        <th nowrap>��1-6</th><th nowrap>��4-NC</th><th nowrap>��5-PF</th><th nowrap>��5-2</th><th nowrap>������</th><th class='b_w'>��Ͽ</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td colspan='3' align='right'>���Τ�100%�ˤʤ�褦��</td>\n";
                echo "      <td><input type='text' name='c_assy' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_toku' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_1_nc' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_1_6' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_4_nc' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_5_pf' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='s_5_2' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='text' name='shape' size='4' maxlength='3'></td>\n";
                echo "      <td><input type='submit' name='act_add' value='�¹�' >\n";
                echo "  </tr>\n";
                echo "</table>\n";
                echo "</form>\n";
            }
        }
        if($_POST['act_sel'] == "chg"){
            $query = "select act_id,act_name,s_name,c_exp,l_exp,s_g_exp,shoukan,c_assy,s_toku,s_1_nc,s_1_6,s_4_nc,s_5_pf,s_5_2,shape from act_table where act_id=" . $_POST['act_id'];
            $res = array();
            if(($rows=getResult($query,$res))>=1){      // �ѹ��оݥǡ�������
                echo "<form method='post' action='act_table_mnt.php' onSubmit='return act_chk(this)'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>������</th><th nowrap>�� �� ̾</th><th nowrap>û��̾</th><th nowrap>���ץ�</th><th nowrap>��˥�</th>
                        <th nowrap>�δ���</th><th nowrap>����</th><th colspan='5' align='left'>��ʬ��</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20' value='" . $res[0]['act_name'] . "'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8' value='" . $res[0]['s_name'] . "'></td>\n";
                echo "      <td><input type='text' name='c_exp' size='4' maxlength='3' value='" . $res[0]['c_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='l_exp' size='4' maxlength='3' value='" . $res[0]['l_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='s_g_exp' size='4' maxlength='3' value='" . $res[0]['s_g_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='shoukan' size='4' maxlength='3' value='" . $res[0]['shoukan'] . "'></td>\n";
                echo "      <td colspan='5' align='left'>���Τ�100%�ˤʤ�褦��</td>\n";
                echo "  </tr>\n";
                echo "  <th colspan='3' align='right'>���ץ������ ����Ψ</th><th nowrap>����Ω</th><th nowrap>��¤��</th><th nowrap>��1-NC</th>
                        <th nowrap>��1-6</th><th nowrap>��4-NC</th><th nowrap>��5-PF</th><th nowrap>��5-2</th><th nowrap>������</th><th class='b_w'>�ѹ�</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td colspan='3' align='right'>���Τ�100%�ˤʤ�褦��</td>\n";
                echo "      <td><input type='text' name='c_assy' size='4' maxlength='3' value='" . $res[0]['c_assy'] . "'></td>\n";
                echo "      <td><input type='text' name='s_toku' size='4' maxlength='3' value='" . $res[0]['s_toku'] . "'></td>\n";
                echo "      <td><input type='text' name='s_1_nc' size='4' maxlength='3' value='" . $res[0]['s_1_nc'] . "'></td>\n";
                echo "      <td><input type='text' name='s_1_6' size='4' maxlength='3' value='" . $res[0]['s_1_6'] . "'></td>\n";
                echo "      <td><input type='text' name='s_4_nc' size='4' maxlength='3' value='" . $res[0]['s_4_nc'] . "'></td>\n";
                echo "      <td><input type='text' name='s_5_pf' size='4' maxlength='3' value='" . $res[0]['s_5_pf'] . "'></td>\n";
                echo "      <td><input type='text' name='s_5_2' size='4' maxlength='3' value='" . $res[0]['s_5_2'] . "'></td>\n";
                echo "      <td><input type='text' name='shape' size='4' maxlength='3' value='" . $res[0]['shape'] . "'></td>\n";
                echo "      <td><input type='submit' name='act_chg' value='�¹�' >\n";
                echo "  </tr>\n";
                echo "</table>\n";
                echo "</form>\n";
            }else{
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b' nowrap>\n";
                echo "          ���祳���ɤ���Ͽ����Ƥ��ޤ��� ����ɲä�¹Ԥ��Ʋ�������\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }
        }
        if($_POST['act_sel'] == "del"){
            $query = "select * from act_table where act_id=" . $_POST['act_id'];
            $res = array();
            if(($rows=getResult($query,$res))>=1){      // �ѹ��оݥǡ�������
                echo "<form method='post' action='act_table_mnt.php'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>������</th><th nowrap>�� �� ̾</th><th nowrap>û��̾</th><th nowrap>���ץ�</th><th nowrap>��˥�</th>
                        <th nowrap>�δ���</th><th nowrap>����</th><th colspan='5' align='left'>��ʬ��</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20' value='" . $res[0]['act_name'] . "'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8' value='" . $res[0]['s_name'] . "'></td>\n";
                echo "      <td><input type='text' name='c_exp' size='4' maxlength='3' value='" . $res[0]['c_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='l_exp' size='4' maxlength='3' value='" . $res[0]['l_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='s_g_exp' size='4' maxlength='3' value='" . $res[0]['s_g_exp'] . "'></td>\n";
                echo "      <td><input type='text' name='shoukan' size='4' maxlength='3' value='" . $res[0]['shoukan'] . "'></td>\n";
                echo "      <td colspan='5' align='left'>���Τ�100%�ˤʤ�褦��</td>\n";
                echo "  </tr>\n";
                echo "  <th colspan='3' align='right'>���ץ������ ����Ψ</th><th nowrap>����Ω</th><th nowrap>��¤��</th><th nowrap>��1-NC</th>
                        <th nowrap>��1-6</th><th nowrap>��4-NC</th><th nowrap>��5-PF</th><th nowrap>��5-2</th><th nowrap>������</th><th class='r_w'>���</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td colspan='3' align='right'>���Τ�100%�ˤʤ�褦��</td>\n";
                echo "      <td><input type='text' name='c_assy' size='4' maxlength='3' value='" . $res[0]['c_assy'] . "'></td>\n";
                echo "      <td><input type='text' name='s_toku' size='4' maxlength='3' value='" . $res[0]['s_toku'] . "'></td>\n";
                echo "      <td><input type='text' name='s_1_nc' size='4' maxlength='3' value='" . $res[0]['s_1_nc'] . "'></td>\n";
                echo "      <td><input type='text' name='s_1_6' size='4' maxlength='3' value='" . $res[0]['s_1_6'] . "'></td>\n";
                echo "      <td><input type='text' name='s_4_nc' size='4' maxlength='3' value='" . $res[0]['s_4_nc'] . "'></td>\n";
                echo "      <td><input type='text' name='s_5_pf' size='4' maxlength='3' value='" . $res[0]['s_5_pf'] . "'></td>\n";
                echo "      <td><input type='text' name='s_5_2' size='4' maxlength='3' value='" . $res[0]['s_5_2'] . "'></td>\n";
                echo "      <td><input type='text' name='shape' size='4' maxlength='3' value='" . $res[0]['shape'] . "'></td>\n";
                echo "      <td><input type='submit' name='act_del' value='�¹�' >\n";
                echo "  </tr>\n";
                echo "</table>\n";
                echo "</form>\n";
            }else{
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b' nowrap>\n";
                echo "          ���祳���ɤ��ޥ���������Ͽ����Ƥ��ޤ���\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }
        }
        // view ����ɽ��
        $query = "select act_id,act_name,s_name,c_exp,l_exp,s_g_exp,shoukan,c_assy,s_toku,s_1_nc,s_1_6,s_4_nc,s_5_pf,s_5_2,shape ";
        $query .= "from act_table order by act_id ASC offset $offset limit " . PAGE;
        $res = array();
        if(($rows=getResult($query,$res))>=1){      // 
            echo "<hr>\n";
            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
            echo "  <form method='post' action='act_table_mnt.php'>\n";
            echo "  <caption>�������祳���ɡ�����Ψ����\n";
            echo "      <input type='submit' name='backward' value='����'>\n";
            echo "      <input type='submit' name='forward' value='����'>\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            $num = count($res[0]);
            for($r=0;$r<$rows;$r++){
                echo "  <th nowrap class='y_b'>No</th><th nowrap class='y_b'>������</th><th nowrap class='y_b'>�� �� ̾</th><th nowrap class='y_b'>û��̾</th><th nowrap class='y_b'>���ץ�</th><th nowrap class='y_b'>��˥�</th>
                        <th nowrap class='y_b'>�δ���</th><th nowrap class='y_b'>����</th><th colspan='5' align='center'>-</th>\n";
                print("<tr>\n");
                echo "  <form method='post' action='act_table_mnt.php'>\n";
                print(" <td align='center'><input type='submit' name='copy' value='" . ($r + $offset + 1) . "'></td>\n");
                echo "      <input type='hidden' name='act_sel' value='chg'>\n";
                echo "      <input type='hidden' name='act_id' value='" . $res[$r][0] . "'>\n";
                echo "  </form>\n";
                for($n=0;$n<7;$n++){
                    if($res[$r][$n] == "")
                        echo("<td nowrap align='center'>---</td>\n");
                    else
                        if($n >= 1 && $n <= 2)
                            echo("<td nowrap align='left' class='fmp'>" . $res[$r][$n] . "</td>\n");
                        else
                            echo("<td nowrap align='center' class='flp'>" . $res[$r][$n] . "</td>\n");
                }
                echo "<td colspan='3' align='center'>-</td>\n";
                print("</tr>\n");
                echo "  <form method='post' action='act_table_mnt.php'>\n";
                $query = "select act_flg from act_table where act_id=" . $res[$r]['act_id'];
                $res_flg = array();
                if(($rows_flg=getResult($query,$res_flg))>=1){      // ľ�ܡ������������
                    if($res_flg[0]['act_flg'] == 't')
                        echo "  <th colspan='3'><input type='submit' name='act_flg' value='ľ������' class='blue'></th>\n";
                    else
                        echo "  <th colspan='3'><input type='submit' name='act_flg' value='��������' class='red'></th>\n";
                }
                echo "<input type='hidden' name='act_id' value='" . $res[$r]['act_id'] . "'>\n";
                echo "  </form>\n";
                echo "<th nowrap class='y_b'>����Ω</th><th nowrap class='y_b'>��¤��</th><th nowrap class='y_b'>��1-NC</th>
                        <th nowrap class='y_b'>��1-6</th><th nowrap class='y_b'>��4-NC</th><th nowrap class='y_b'>��5-PF</th><th nowrap class='y_b'>��5-2</th><th nowrap class='y_b'>������</th>\n";
                print("<tr>\n");
                for($n=7;$n<15;$n++){
                    if($n == 7){
                        echo "  <form method='post' action='act_table_mnt.php'>\n";
                        $query = "select rate_flg from act_table where act_id=" . $res[$r]['act_id'];
                        $res_rate = array();
                        if(($rows_rate=getResult($query,$res_rate))>=1){    // ������Ψ�������оݡ���Ψ��������μ���
                            if($res_rate[0]['rate_flg'] == '1')
                                echo "  <td colspan='3' align='center'><input type='submit' name='rate_flg' value='������Ψ�о�' class='blue'></td>\n";
                            else if($res_rate[0]['rate_flg'] == '2')
                                echo "  <td colspan='3' align='center'><input type='submit' name='rate_flg' value='��Ψ��������' class='red'></td>\n";
                            else
                                echo "  <td colspan='3' align='center'><input type='submit' name='rate_flg' value='������Ψ����'></td>\n";
                        }
                        echo "<input type='hidden' name='act_id' value='" . $res[$r]['act_id'] . "'>\n";
                        echo "  </form>\n";
                    }
                    if($res[$r][$n] == "")
                        echo("<td nowrap align='center'>---</td>\n");
                    else
                        echo("<td nowrap align='center' class='flp'>" . $res[$r][$n] . "</td>\n");
                }
                print("</tr>\n");
            }
            print("</table>\n");
        }
    ?>
    </center>
</BODY>
</HTML>
