<?php
//////////////////////////////////////////////////////////////////////////////
// �������祳���ɡ�����Ψ���ݼ� act_table.php �� act_table_new.php          //
// Copyright (C) 2002-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/09/17 Created  act_table_mnt_new.php                                //
// 2002/09/20 �����ȥ�˥塼���ɲ�                                          //
// 2002/09/28 ����Ψ�ơ��֥�� act_allocation & allocation_item ���ѹ�      //
//            ���Τ���ץ������������ѹ� �ե�����̾�ѹ�(ɽ��)           //
// 2002/11/26 »�ץ��롼�פ�����Ψ�Ȳ񤬤ܴۤ��� 1���ܥ����꡼              //
//                         ���Τ�100��Υ����å�̤������                    //
// 2003/02/26 body �� onLoad ���ɲä�������ϸĽ�� focus() ������          //
// 2003/05/15 ���ȿ��ηϤ��б� ��°�����ɤ��ɲ� ��ʬ������ݼ�ǥ���      //
// 2004/10/28 user_check()function���ɲä��Խ������桼���������          //
// 2005/10/27 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2007/04/17 ���¤�300144���ɲ� ��ë                                       //
// 2009/03/12 ���¤�014737���ɲ� ��                                         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

////////////// ����������
$menu->set_site(INDEX_PL, 10);                    // site_index=INDEX_PL(»�ץ�˥塼) site_id=10(���祳����)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�������祳���ɤΥ��ƥʥ�');

/////////// �桼�����Υ����å�
$uid = $_SESSION['User_ID'];            // �桼����
function user_check($uid)
{
    switch ($uid) {
    case '017850':      // ����
    case '300055':      // ��ƣ
    case '300101':      // ��ë
    case '300144':      // ��ë
    case '010561':      // ����
    case '014737':      // ��
        return TRUE;
        break;
    default:
        $query = "select trim(name) from user_detailes where uid = '{$uid}' limit 1";
        if (getUniResult($query, $name) <= 0) $name = '';
        $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����ǤϷ������祳���ɤ��ݼ�Ͻ���ޤ��� ����ô���Ԥ�Ϣ���Ʋ�������";
        return FALSE;
    }
}

if (!isset($_POST['act_sel'])) {        // ���ꤵ��Ƥ��ʤ����ν����
    $_POST['act_sel'] = "";
}
if (!isset($_POST['act_id'])) {        // ���ꤵ��Ƥ��ʤ����ν����
    $_POST['act_id'] = "";
}
$today = date("Y-m-d");
$query = "select count(*) from act_table";
$res = array();
if ( ($rows=getResult($query,$res)) >= 1) {
    $maxrows = $res[0][0];
}
define("PAGE","15");
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
///////////////////////////////////////// act_add �ɲ� ����(act_table �˹��ܤ��ɲäΤ�)
while (isset($_POST['act_add'])) {
    if (!user_check($uid)) break;
    $query = "insert into act_table (act_id, act_name, s_name, date_add, date_chg, act_flg) values (";
    $query .= $_POST['act_id'] . ",'" . $_POST['act_name'] . "','" . $_POST['s_name'] . "','$today',NULL,'t')";
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 0) {
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . " : " . $_POST['act_name'] . "����Ͽ���ޤ�����";
    } else {
        $_SESSION['s_sysmsg'] = $res[0][0];
    }
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
    break;
}
///////////////////////////////////////// act_chg �ѹ� ����
while ( isset($_POST['act_chg']) ) {
    if (!user_check($uid)) break;
    $query = "update act_table set act_name='" . $_POST['act_name'] . "', s_name='" . $_POST['s_name'] . "',date_chg='$today', s_g_exp=";
    if ($_POST['s_g_exp'] == "") {
        $query .= "NULL, c_exp=";
    } else {
        $query .= $_POST['s_g_exp'] . ", c_exp=";
    }
    if ($_POST['c_exp'] == "") {
        $query .= "NULL, l_exp=";
    } else {
        $query .= $_POST['c_exp'] . ", l_exp=";
    }
    if ($_POST['l_exp'] == "") {
        $query .= "NULL, shoukan=";
    } else {
        $query .= $_POST['l_exp'] . ", shoukan=";
    }
    if ($_POST['shoukan'] == "") {
        $query .= "NULL, c_assy=";
    } else {
        $query .= $_POST['shoukan'] . ", c_assy=";
    }
    if ($_POST['c_assy'] == "") {
        $query .= "NULL, s_toku=";
    } else {
        $query .= $_POST['c_assy'] . ", s_toku=";
    }
    if ($_POST['s_toku'] == "") {
        $query .= "NULL, s_1_nc=";
    } else {
        $query .= $_POST['s_toku'] . ", s_1_nc=";
    }
    if ($_POST['s_1_nc'] == "") {
        $query .= "NULL, s_1_6=";
    } else {
        $query .= $_POST['s_1_nc'] . ", s_1_6=";
    }
    if ($_POST['s_1_6'] == "") {
        $query .= "NULL, s_4_nc=";
    } else {
        $query .= $_POST['s_1_6'] . ", s_4_nc=";
    }
    if ($_POST['s_4_nc'] == "") {
        $query .= "NULL, s_5_pf=";
    } else {
        $query .= $_POST['s_4_nc'] . ", s_5_pf=";
    }
    if ($_POST['s_5_pf'] == "") {
        $query .= "NULL, s_5_2=";
    } else {
        $query .= $_POST['s_5_pf'] . ", s_5_2=";
    }
    if ($_POST['s_5_2'] == "") {
        $query .= "NULL, shape=";
    } else {
        $query .= $_POST['s_5_2'] . ", shape=";
    }
    if ($_POST['shape'] == "") {
        $query .= "NULL ";
    } else {
        $query .= $_POST['shape'] . " ";
    }
    $query .= "where act_id=" . $_POST['act_id'];
    $res = array();
    if ( ($rows=getResult($query,$res)) >=0 ) {
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . " : " . $_POST['act_name'] . "���ѹ����ޤ�����";
    } else {
        $_SESSION['s_sysmsg'] = $res[0][0];
    }
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
    break;
}
///////////////////////////////////////// act_del �ѹ� ����
while ( isset($_POST['act_del']) ) {
    if (!user_check($uid)) break;
    $query = "delete from act_table where act_id=";
    $query .= $_POST['act_id'] ;
    $res=array();
    if ( ($rows=getResult($query,$res)) >= 0) {
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . "�������ޤ�����";
    } else {
        $_SESSION['s_sysmsg'] = $res[0][0];
    }
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
*/  // ----------------------------------- offset �� END
    break;
}
///////////////////////////////////////// act_flg �ѹ� ����(ľ�ܡ��������硦�δ���) ����
while ( isset($_POST['act_flg']) ) {
    if (!user_check($uid)) break;
    $res = array();
    if ($_POST['act_flg'] == "ľ������") {
        $query = "update act_table set act_flg='f' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "�����������ѹ����ޤ�����";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } elseif ($_POST['act_flg'] == "��������") {
        $query = "update act_table set act_flg='h' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "���δ�������ѹ����ޤ�����";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    } else { /////////////////////// �δ�������
        $query = "update act_table set act_flg='t' where act_id=" . $_POST['act_id'];
        if ( ($rows=getResult($query,$res)) >= 0) {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "��ľ��������ѹ����ޤ�����";
        } else {
            $_SESSION['s_sysmsg'] = $res[0][0];
        }
    }
    break;
}
///////////////////////////////////////// rate_flg �ѹ� ����(������Ψ�оݳ�(0)or(NULL) �о�(1) ��������(2))
while ( isset($_POST['rate_flg']) ) {
    if (!user_check($uid)) break;
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
    break;
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
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript' src='act_table_mnt.js'></script>
<style type='text/css'>
    <!--
    select{
        background-color:teal;
        color:white;
    }
    textarea{
        background-color:black;
        color:white;
    }
    input.sousin{
        background-color:red;
    }
    input.text{
        background-color:black;
        color:white;
    }
    .pt11{
        font-size:11pt;
    }
    .margin1{
        margin:1%;
    }
    .margin0{
        margin:0%;
    }
    .pt12b{
        font:bold 12pt;
    }
    .y_b{
        background-color:yellow;
        color:blue;
    }
    .r_b{
        background-color:red;
        color:black;
    }
    .r_w{
        background-color:red;
        color:white;
    }
    .b_w{
        background-color:blue;
        color:white;
    }
    .fsp{
        font-size:8pt;
    }
    .fmp{
        font-size:10pt;
    }
    .flp{
        font-size:12pt;
    }
    .fllbp{
        font-size:16pt;
        font-weight:bold;
    }
    .fmp-n{
        background-color:yellow;
        color:blue;
        font-size:10pt;
        font-weight:normal;
    }
    input.blue{
        color:blue;
    }
    input.red{
        color:red;
    }
    input.green{
        color:green;
    }
    -->
</style>
</head>
<body onLoad='document.select_form.act_id.focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='select_form' method='post' action='<?=$menu->out_self()?>' onSubmit='return act_id_chk(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='left' nowrap>
                        ��ʬ�����򤷤����祳���ɤ����Ϥ��¹ԥܥ���򲡤��Ʋ�������<br>
                        <table class='winbox_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='2'>
                            <tr align='center'>
                                <td class='winbox' nowrap <?php if($_POST['act_sel']=='add') echo "class='b_w'" ?>>
                                    <input type='radio' name='act_sel' value='add' id='add'
                                        <?php if($_POST['act_sel']=='add') echo 'checked' ?>
                                    >
                                    <label for='add'>�ɲ�</label>
                                </td>
                                <td class='winbox' nowrap <?php if($_POST['act_sel']=='chg') echo "class='b_w'" ?>>
                                    <input type='radio' name='act_sel' value='chg' id='chg'
                                        <?php if($_POST['act_sel']=='chg') echo'checked' ?>
                                    >
                                    <label for='chg'>�ѹ�</label>
                                </td>
                                <td class='winbox' nowrap <?php if($_POST['act_sel']=='del') echo "class='b_w'" ?>>
                                    <input type='radio' name='act_sel' value='del' id='del'
                                    <?php if($_POST['act_sel']=='del') echo 'checked' ?>
                                    >
                                    <label for='del'>���</label>
                                </td>
                        </table>
                        <div align='center'>
                            ���祳����<input type='text' name='act_id' size='7' maxlength='6' value='<?php echo $_POST['act_id'] ?>'>
                            <input type='submit' name='edit' value='�¹�' >
                        </div>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    <?php
        if($_POST['act_sel'] == 'add'){
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
                echo "<form method='post' action='act_table_mnt_new.php' onSubmit='return act_add_chk(this)'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>������</th><th nowrap>�� �� ̾</th><th nowrap>û��̾</th><th class='b_w'>��Ͽ</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8'></td>\n";
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
                echo "<form method='post' action='act_table_mnt_new.php' onSubmit='return act_chk_name(this)'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>������</th><th nowrap>�� �� ̾</th><th nowrap>û��̾</th><th class='b_w'>�ѹ�</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20' value='" . trim($res[0]['act_name']) . "'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8' value='" . trim($res[0]['s_name']) . "'></td>\n";
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
                echo "<form method='post' action='act_table_mnt_new.php'>\n";
                echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                echo "  <th nowrap>������</th><th nowrap>�� �� ̾</th><th nowrap>û��̾</th><th class='r_w'>���</th>\n";
                echo "  <tr align='center'>\n";
                echo "      <td>" . $_POST['act_id'] . "</td>\n";
                echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
                echo "      <td><input type='text' name='act_name' size='23' maxlength='20' value='" . $res[0]['act_name'] . "'></td>\n";
                echo "      <td><input type='text' name='s_name' size='9' maxlength='8' value='" . $res[0]['s_name'] . "'></td>\n";
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
        $query = "select act_id,act_name,s_name,act_flg,rate_flg ";
        $query .= "from act_table left outer join cate_allocation on(act_id=dest_id and cate_id=0) order by cate_rate ASC offset $offset limit " . PAGE;
        $res = array();
        if(($rows=getResult($query,$res))>=1){ ///////////// ���祳���ɥޥ������μ���
//          echo "<hr>\n";
            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
            echo "  <form action='act_table_mnt_new.php' method='post'>\n";
            echo "  <caption>�������祳���ɡ�����Ψ����\n";
            echo "      <input type='submit' name='backward' value='����'>\n";
            echo "      <input type='submit' name='forward' value='����'>\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            ////////////////////////////////////////////// ��ʬ�������Ψ category_item cate_allocation
            $query = "select cate_item,cate_id from category_item where cate_id<=100 order by cate_group";
            $res_cate = array();
            $rows_cate=getResult($query,$res_cate);
                /***** �ե������̾���� *****/
            echo "  <th nowrap class='fmp-n'>No</th><th nowrap class='fmp-n'>������</th><th nowrap class='fmp-n'>�� �� ̾</th><th nowrap class='fmp-n'>û��̾</th>\n";
            for($i=0;$i<$rows_cate;$i++){
                echo "<th nowrap class='fmp-n'>" . $res_cate[$i]['cate_item'] . "</th>\n";
            }
            echo "<th colspan='2' align='center' class='fmp-n'>ľ��/����/�δ���/����¾</th>\n";
                /***** �ե������̾ End *****/
            for($r=0;$r<$rows;$r++){
                print("<tr>\n");
                echo "  <form method='post' action='act_table_mnt_new.php'>\n";
                print(" <td align='center'><input type='submit' name='copy' value='" . ($r + $offset + 1) . "'></td>\n");
                echo "      <input type='hidden' name='act_sel' value='chg'>\n";
                echo "      <input type='hidden' name='act_id' value='" . $res[$r]['act_id'] . "'>\n";
                echo("<td nowrap align='left' class='flp'>" . $res[$r]['act_id'] . "</td>\n");
                echo("<td nowrap align='left' class='fmp'>" . $res[$r]['act_name'] . "</td>\n");
                echo("<td nowrap align='left' class='fmp'>" . $res[$r]['s_name'] . "</td>\n");
                for($i=0;$i<$rows_cate;$i++){
                    //////////////////////////// �����ܥ����꡼ ��ˡ���������Ψ�μ���
                    $query = "select cate_rate from cate_allocation where dest_id=" . $res[$r]['act_id'] . "and cate_id=" . $res_cate[$i]['cate_id'];
                    $res_cate_allo = array();
                    if (($rows_cate_allo = getResult($query,$res_cate_allo)) < 1)
                        echo("<td nowrap align='center'>---</td>\n");
                    elseif ($res_cate_allo[0]['cate_rate'] == "")
                        echo("<td nowrap align='center'>---</td>\n");
                    else
                        echo("<td nowrap align='right' class='flp'>" . $res_cate_allo[0]['cate_rate'] . "</td>\n");
                }
                echo "  </form>\n";
                echo "  <form method='post' action='act_table_mnt_new.php'>\n";
                if($res[$r]['act_flg'] == 't')
                    echo "  <td><input type='submit' name='act_flg' value='ľ������' class='blue'></td>\n";
                else if($res[$r]['act_flg'] == 'f')
                    echo "  <td><input type='submit' name='act_flg' value='��������' class='red'></td>\n";
                else
                    echo "  <td><input type='submit' name='act_flg' value='�δ�����' class='green'></td>\n";
                if($res[$r]['rate_flg'] == '1')
                    echo "  <td align='center'><input type='submit' name='rate_flg' value='������Ψ�о�' class='blue'></td>\n";
                else if($res[$r]['rate_flg'] == '2')
                    echo "  <td align='center'><input type='submit' name='rate_flg' value='��Ψ��������' class='red'></td>\n";
                else
                    echo "  <td align='center'><input type='submit' name='rate_flg' value='������Ψ����'></td>\n";
                echo "      <input type='hidden' name='act_id' value='" . $res[$r]['act_id'] . "'>\n";
                echo "  </form>\n";
                print("</tr>\n");
            }
            print("</table>\n");
            //////////////////////////////////////////////// ��ʬ��γ�����Ψ allocation_item act_allocation
            for($r=0;$r<$rows;$r++){ ////////////// PAGE �� ʬ��
                $query = "select allo_item,allo_id from allocation_item order by allo_id ASC";
                $res_allo = array();
                if(($rows_allo=getResult($query,$res_allo))>=1){ ///////////// ���۹��ܤμ���
                    for($i=0;$i<$rows_allo;$i++){
                        $query = "select dest_id,allo_rate from act_allocation where allo_id="  . $res_allo[$i]['allo_id'] . 
                            " and orign_id=" . $res[$r]['act_id'] . " order by dest_id ASC";
                        $res_act = array();
                        if(($rows_act=getResult($query,$res_act))>=1){ //////// �����衦����Ψ�μ���
                            echo "<hr>\n";
                            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
                            echo "  <form action='act_table_mnt_new.php' method='post'>\n";
                            echo "  <caption>" . $res_allo[$i]['allo_item'] . "\n";
                            echo "      <input type='submit' name='backward' value='����'>\n";
                            echo "      <input type='submit' name='forward' value='����'>\n";
                            echo "  </caption>\n";
                            echo "  </form>\n";
                            echo "  <th nowrap class='fmp-n'>������</th><th nowrap class='fmp-n'>�� �� ̾</th><th nowrap class='fmp-n'>û��̾</th>\n";
                            for($j=0;$j<$rows_act;$j++){
                                $query = "select s_name from act_table where act_id=" . $res_act[$j]['dest_id'] . " limit 1\n";
                                $res_name = array();
                                if(($rows_name=getResult($query,$res_name))>=1){ //////// ������̾�Τμ���
                                    echo "<th nowrap class='y_b'>" . $res_name[0]['s_name'] . "</th>\n";
                                }
                            }
                            echo "<tr>\n";
                            echo("<td nowrap align='left' class='flp'>" . $res[$r]['act_id'] . "</td>\n");
                            echo("<td nowrap align='left' class='fmp'>" . $res[$r]['act_name'] . "</td>\n");
                            echo("<td nowrap align='left' class='fmp'>" . $res[$r]['s_name'] . "</td>\n");
                            for($j=0;$j<$rows_act;$j++){
                                if($res_act[$j]['allo_rate'] == "")
                                    echo("<td nowrap align='center'>---</td>\n");
                                else
                                    echo("<td nowrap align='right' class='flp'>" . $res_act[$j]['allo_rate'] . "</td>\n");
                            }
                            echo "</tr>\n";
                            echo "</table>\n";
                        }
                    }
                }
            }
        }
    ?>
    </center>
</body>
</html>
