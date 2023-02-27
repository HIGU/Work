<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ȱ��γƼ拾���ɥơ��֥���ݼ� �������ȿ����ͻ�������                  //
// Copyright (C) 2002-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/09/12 Created   cd_table_mnt.php                                    //
// 2002/09/20 �����ȥ�˥塼���ɲ�                                          //
// 2003/02/26 body �� onLoad ���ɲä�������ϸĽ�� focus() ������          //
// 2003/04/24 �ɲä�ɬ�פʰ����Ⱥ����ɬ�פʰ��� ���å����ɲ�             //
// 2004/04/20 �Ұ��ֹ�Υ����å����������å��� JavaScript���ɲ�           //
// 2004/10/28 user_check()function���ɲä��Խ������桼���������          //
// 2005/10/13 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2006/06/27 �ɲä�ɬ�פʰ���ɽ�ξ��� u.uid NOT LIKE '99%' ���ɲ�        //
// 2008/09/17 ǧ�ڤ�getCheckAuthority()���ѹ�                               //
//            26�������ɥơ��֥���ݼ餬�Ԥ���Ұ��ֹ�                 ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // �����̥ե��󥯥å����
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(10, 2);                     // site_index=10(»�ץ�˥塼) site_id=2(�����ɥơ��֥�)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
// $menu->set_RetUrl(PL_MENU);                 // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�����ɥơ��֥�Υ��ƥʥ�');
//////////// ɽ�������
$menu->set_caption('��ʬ�����򤷼Ұ��ֹ�����Ϥ��¹ԥܥ���򲡤��Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

/////////// ��Ͽ���ѹ� �桼�����Υ����å�
$uid = $_SESSION['User_ID'];            // �桼����
function user_check($uid)
{
    if(getCheckAuthority(26)) {
        return TRUE;
    } else {
        $query = "SELECT trim(name) FROM user_detailes WHERE uid = '{$uid}' LIMIT 1";
        if (getUniResult($query, $name) <= 0) $name = '';
        $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥϥ����ɥơ��֥���ݼ�Ͻ���ޤ��� ����ô���Ԥ�Ϣ���Ʋ�������";
        return FALSE;
    }
}

///// Controller ��
if (isset($_POST['uid'])) {     // edit��NG IE�ǥƥ����ȥե������(uid)��enter��������uid����POST����ʤ���
    if (!isset($_POST['cd_sel'])) {
        $_SESSION['s_sysmsg'] = '�ɲá��ѹ�������ζ�ʬ�����򤷤Ʋ�������';
    }
}
if (!isset($_POST['cd_sel'])) {
    $_POST['cd_sel'] = '';          // ���åȤ���Ƥ��ʤ����ν����
}
if (!isset($_POST['uid'])) {        // ���åȤ���Ƥ��ʤ����ν����
    $_POST['uid'] = '';
}

///// Model ��
$today = date('Y-m-d');
$query = 'SELECT count(*) FROM cd_table';
$res = array();
if (($rows=getResult($query,$res))>=1) {
    $maxrows = $res[0][0];
}
define('PAGE', '15');
if (isset($_POST['forward'])) {
    $_SESSION['cd_offset'] += PAGE;
    if ($_SESSION['cd_offset'] >= $maxrows)
        $_SESSION['cd_offset'] = ($maxrows - 1);
} elseif (isset($_POST['backward'])) {
    $_SESSION['cd_offset'] -= PAGE;
    if($_SESSION['cd_offset'] < 0)
        $_SESSION['cd_offset'] = 0;
} else {
    if (!isset($_SESSION['cd_offset']))
        $_SESSION['cd_offset'] = 0;
}
$offset = $_SESSION['cd_offset'];
///////////////////////////////////////// cd_add �ɲ� ����
while (isset($_POST['cd_add'])) {
    if (!user_check($uid)) break;
    $query = "INSERT INTO cd_table VALUES ('";
    $query .= $_POST['uid'] . "',";
    if ($_POST['act_id'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['act_id'] . ",";
    if ($_POST['orga_id'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['orga_id'] . ",";
    if($_POST['pers_id'] == "")
        $query .= "NULL,";
    else
        $query .= $_POST['pers_id'] . ",";
    $query .= "'$today',NULL,'t')";
    $res=array();
    if (($rows=getResult($query,$res))>=0)
        $_SESSION['s_sysmsg'] = $_POST['name'] . "����Ͽ���ޤ�����";
    else
        $_SESSION['s_sysmsg'] = $res[0][0];
    // ----------------------------------- offset �ͤ�SET
    $query = "SELECT uid FROM cd_table AS c LEFT OUTER JOIN user_detailes AS u USING(uid) ORDER BY act_id ASC, pid DESC, uid ASC";
    $res = array();
    if(($rows=getResult($query,$res)) >= 1){
        for($i=0;$i<$rows;$i++){
            if($res[$i][0] == $_POST['uid']){
                $_SESSION['cd_offset'] = $i;
                $offset = $i;
            }
        }
    }
    // ----------------------------------- offset �� END
    break;
}
///////////////////////////////////////// cd_chg �ѹ� ����
while (isset($_POST['cd_chg'])) {
    if (!user_check($uid)) break;
    $query = "UPDATE cd_table SET act_id=";
    if($_POST['act_id'] == "")
        $query .= "NULL, orga_id=";
    else
        $query .= $_POST['act_id'] . ", orga_id=";
    if($_POST['orga_id'] == "")
        $query .= "NULL, pers_id=";
    else
        $query .= $_POST['orga_id'] . ", pers_id=";
    if($_POST['pers_id'] == "")
        $query .= "NULL, date_chg=";
    else
        $query .= $_POST['pers_id'] . ", date_chg=";
    $query .= "'$today' where uid='";
    $query .= $_POST['uid'] . "'";
    $res=array();
    if(($rows=getResult($query,$res))>=0)
        $_SESSION['s_sysmsg'] = $_POST['name'] . "���ѹ����ޤ�����";
    else
        $_SESSION['s_sysmsg'] = $res[0][0];
    // ----------------------------------- offset �ͤ�SET
    $query = "SELECT uid FROM cd_table AS c LEFT OUTER JOIN user_detailes AS u USING(uid) ORDER BY act_id ASC, pid DESC, uid ASC";
    $res = array();
    if(($rows=getResult($query,$res)) >= 1){
        for($i=0;$i<$rows;$i++){
            if($res[$i][0] == $_POST['uid']){
                $_SESSION['cd_offset'] = $i;
                $offset = $i;
            }
        }
    }
    // ----------------------------------- offset �� END
    break;
}
///////////////////////////////////////// cd_del �ѹ� ����
while (isset($_POST['cd_del'])) {
    if (!user_check($uid)) break;
    $query = "DELETE FROM cd_table WHERE uid='";
    $query .= $_POST['uid'] . "'";
    $res=array();
    if ( ($rows=getResult($query,$res)) >= 0) {
        $_SESSION['s_sysmsg'] = $_POST['name'] . "�������ޤ�����";
    } else {
        $_SESSION['s_sysmsg'] = $res[0][0];
    }
/*  // ----------------------------------- offset �ͤ�SET
    $query = "SELECT uid FROM cd_table AS c LEFT OUTER JOIN user_detailes AS u USING(uid) ORDER BY act_id ASC, pid DESC, uid ASC";
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 1) {
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] == $_POST['uid']) {
                $_SESSION['cd_offset'] = $i;
                $offset = $i;
            }
        }
    }
*/  // ----------------------------------- offset �� END
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
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* ����ʸ�����������ɤ��������å� */
/* false=�����Ǥʤ�  ture=�����Ǥ��� */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++){
        c = str.charAt(i);
        if ( ("0" > c) || ("9" < c) ) {
            return false;
        }
    }
    return true;
}
/* �Ұ��ֹ�Υ����å������� */
function uid_chk(obj) {
    if (!obj.uid.value.length) {
        alert("[�Ұ��ֹ�]�������󤬶���Ǥ���");
        obj.uid.focus();
        return false;
    }
    if (!isDigit(obj.uid.value)) {
        alert("[�Ұ��ֹ�]�������Ǥ���ޤ���");
        obj.uid.focus();
        return false;
    }
    if (obj.uid.value.length != 6) {
        switch (obj.uid.value.length) {
        case 1:
            obj.uid.value = ('00000' + obj.uid.value);
            break;
        case 2:
            obj.uid.value = ('0000' + obj.uid.value);
            break;
        case 3:
            obj.uid.value = ('000' + obj.uid.value);
            break;
        case 4:
            obj.uid.value = ('00' + obj.uid.value);
            break;
        case 5:
            obj.uid.value = ('0' + obj.uid.value);
            break;
        }
    }
    return true;
}
// -->
</script>
<style type="text/css">
<!--
.title-font {
    font:bold 13.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:none;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:1.0pt solid windowtext;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
form {
    margin: 0%;
}
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
.b_w    {background-color:blue; color:white;}
-->
</style>
</head>
<body class='margin0' onLoad='document.select_form.uid.focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='select_form' method='post' action='cd_table_mnt.php' onSubmit='return uid_chk(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox caption_font' align='left' nowrap>
                        <?php $menu->out_caption() . "\n" ?>
                        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                            <tr align='center'>
                                <td class='winbox caption_font' nowrap <?php if($_POST['cd_sel']=="add") echo "class='y_b'" ?>><input type='radio' name='cd_sel' value='add' id='add'
                                    <?php if($_POST['cd_sel']=="add") echo(" checked") ?>><label for='add'>�ɲ�</label>
                                </td>
                                <td class='winbox caption_font' nowrap <?php if($_POST['cd_sel']=="chg") echo "class='y_b'" ?>><input type='radio' name='cd_sel' value='chg' id='chg'
                                    <?php if($_POST['cd_sel']=="chg") echo(" checked") ?>><label for='chg'>�ѹ�</label>
                                </td>
                                <td class='winbox caption_font' nowrap <?php if($_POST['cd_sel']=="del") echo "class='y_b'" ?>><input type='radio' name='cd_sel' value='del' id='del'
                                    <?php if($_POST['cd_sel']=="del") echo(" checked") ?>><label for='del'>���</label>
                                </td>
                            </tr>
                        </table>
                        <div align='center'>
                            �Ұ��ֹ�<input type='text' name='uid' size='7' maxlength='6' value='<?php echo $_POST['uid'] ?>'>
                            <input type='submit' name='edit' value='�¹�' >
                        </div>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    <?php
        if($_POST['cd_sel'] == "add"){
            $query = "SELECT name FROM user_detailes WHERE uid='" . $_POST['uid'] . "'";
            $res=array();
            if (($rows=getResult($query,$res))>=1){      // �Ұ��ޥ������Υ����å�
                $name = $res[0][0];
                $query = "SELECT uid FROM cd_table WHERE uid='" . $_POST['uid'] . "'";
                $res=array();
                if (($rows=getResult($query,$res))>=1){      // ��Ͽ�ѤߤΥ����å�
                    echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
                    echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                    echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                    // echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                    echo "  <tr>\n";
                    echo "      <td class='r_b winbox' nowrap>\n";
                    echo "          �����ɥơ��֥�˴�����Ͽ�ѤߤǤ����ѹ����Ϻ����¹Ԥ��Ʋ�������\n";
                    echo "      </td>\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                    echo "    </td></tr>\n";
                    echo "</table> <!----------------- ���ߡ�End ------------------>\n";
                } else {
                    echo "<form method='post' action='cd_table_mnt.php'>\n";
                    echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
                    echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                    echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                    // echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                    echo "  <th class='winbox'>�Ұ�No</th><th class='winbox'>��̾</th><th class='winbox'>����</th><th class='winbox'>�ȿ�</th><th class='winbox'>�ͻ�</th>\n";
                    echo "  <tr align='center'>\n";
                    echo "      <td class='winbox'>" . $_POST['uid'] . "</td>\n";
                    echo "      <td class='winbox'>" . $name . "</td>\n";
                    echo "          <input type='hidden' name='uid' value='" . $_POST['uid'] . "'>\n";
                    echo "          <input type='hidden' name='name' value='" . $name . "'>\n";
                    echo "      <td class='winbox'><input type='text' name='act_id' size='6' maxlength='5'></td>\n";
                    echo "      <td class='winbox'><input type='text' name='orga_id' size='7' maxlength='6'></td>\n";
                    echo "      <td class='winbox'><input type='text' name='pers_id' size='6' maxlength='5'></td>\n";
                    echo "      <td class='winbox'><input type='submit' name='cd_add' value='�¹�' >\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                    echo "    </td></tr>\n";
                    echo "</table> <!----------------- ���ߡ�End ------------------>\n";
                    echo "</form>\n";
                }
            }else{
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b winbox' nowrap>\n";
                echo "          �Ұ��ֹ椬��Ͽ����Ƥ��ޤ���\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }
        }
        if($_POST['cd_sel'] == "chg"){
            $query = "SELECT name FROM user_detailes WHERE uid='" . $_POST['uid'] . "'";
            $res=array();
            if(($rows=getResult($query,$res))>=1){      // ��̾�θ���
                $name = $res[0][0];
                $query = "SELECT act_id,orga_id,pers_id FROM cd_table WHERE uid='" . $_POST['uid'] . "'";
                $res = array();
                if(($rows=getResult($query,$res))>=1){      // �ѹ��оݥǡ�������
                    echo "<form method='post' action='cd_table_mnt.php'>\n";
                    echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
                    echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                    echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                    // echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                    echo "  <th class='winbox'>�Ұ�No</th><th class='winbox'>��̾</th><th class='winbox'>����</th><th class='winbox'>�ȿ�</th><th class='winbox'>�ͻ�</th><th class='winbox' align='center'>�ѹ�</th>\n";
                    echo "  <tr align='center'>\n";
                    echo "      <td class='winbox'>" . $_POST['uid'] . "</td>\n";
                    echo "      <td class='winbox'>" . $name . "</td>\n";
                    echo "          <input type='hidden' name='uid' value='" . $_POST['uid'] . "'>\n";
                    echo "          <input type='hidden' name='name' value='" . $name . "'>\n";
                    echo "      <td class='winbox'><input type='text' name='act_id' size='6' maxlength='5' value='" . $res[0]['act_id'] . "'></td>\n";
                    echo "      <td class='winbox'><input type='text' name='orga_id' size='7' maxlength='6' value='" . $res[0]['orga_id'] . "'></td>\n";
                    echo "      <td class='winbox'><input type='text' name='pers_id' size='6' maxlength='5' value='" . $res[0]['pers_id'] . "'></td>\n";
                    echo "      <td class='winbox'><input type='submit' name='cd_chg' value='�¹�' >\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                    echo "    </td></tr>\n";
                    echo "</table> <!----------------- ���ߡ�End ------------------>\n";
                    echo "</form>\n";
                }else{
                    echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                    echo "  <tr>\n";
                    echo "      <td class='r_b winbox' nowrap>\n";
                    echo "          �Ұ��ֹ椬�����ɥơ��֥����Ͽ����Ƥ��ޤ��� ����ɲä�¹Ԥ��Ʋ�������\n";
                    echo "      </td>\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                }
            }else{
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b winbox' nowrap>\n";
                echo "          �Ұ��ֹ椬���Ȱ��ޥ���������Ͽ����Ƥ��ޤ���\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }
        }
        if($_POST['cd_sel'] == "del"){
            $query = "SELECT act_id,orga_id,pers_id FROM cd_table WHERE uid='" . $_POST['uid'] . "'";
            $res=array();
            if(($rows=getResult($query,$res))>=1){      // ��̾�θ���
                $query = "SELECT name FROM user_detailes WHERE uid='" . $_POST['uid'] . "'";
                $res_name = array();
                if(($rows=getResult($query,$res_name))>=1){     // �ѹ��оݥǡ�������
                    $name = $res_name[0][0];
                    echo "<form method='post' action='cd_table_mnt.php'>\n";
                    echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
                    echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                    echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                    // echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
                    echo "  <th class='winbox'>�Ұ�No</th><th class='winbox'>��̾</th><th class='winbox'>����</th><th class='winbox'>�ȿ�</th><th class='winbox'>�ͻ�</th><th align='center' class='r_b winbox'>���</th>\n";
                    echo "  <tr align='center'>\n";
                    echo "      <td class='winbox'>" . $_POST['uid'] . "</td>\n";
                    echo "      <td class='winbox'>" . $name . "</td>\n";
                    echo "          <input type='hidden' name='uid' value='" . $_POST['uid'] . "'>\n";
                    echo "          <input type='hidden' name='name' value='" . $name . "'>\n";
                    echo "      <td class='winbox'>" . $res[0]['act_id'] . "</td>\n";
                    echo "      <td class='winbox'>" . $res[0]['orga_id'] . "</td>\n";
                    echo "      <td class='winbox'>" . $res[0]['pers_id'] . "</td>\n";
                    echo "      <td class='winbox'><input type='submit' name='cd_del' value='�¹�' >\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                    echo "    </td></tr>\n";
                    echo "</table> <!----------------- ���ߡ�End ------------------>\n";
                    echo "</form>\n";
                }else{
                    echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                    echo "  <tr>\n";
                    echo "      <td class='r_b winbox' nowrap>\n";
                    echo "          �Ұ��ֹ椬���Ȱ��ޥ���������Ͽ����Ƥ��ޤ���\n";
                    echo "      </td>\n";
                    echo "  </tr>\n";
                    echo "</table>\n";
                }
            }else{
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='2' cellpadding='3' border='1'>\n";
                echo "  <tr>\n";
                echo "      <td class='r_b winbox' nowrap>\n";
                echo "          �Ұ��ֹ椬�����ɥơ��֥����Ͽ����Ƥ��ޤ���\n";
                echo "      </td>\n";
                echo "  </tr>\n";
                echo "</table>\n";
            }
        }
        // view �����ɬ�פʰ���ɽ��
        $query = "SELECT c.uid, u.name, c.act_id, c.orga_id, c.pers_id, c.date_add, c.date_chg, c.cd_flg 
            FROM cd_table AS c LEFT JOIN user_detailes AS u USING(uid) 
            WHERE retire_date IS NOT NULL OR u.sid = 31";
        $res=array();
        if (($rows=getResult2($query,$res)) >= 1) {     // foreach�Ѥ�query�¹�
            echo "<hr>\n";
            echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            // echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
            echo "  <form method='post' action='cd_table_mnt.php'>\n";
            echo "  <caption>�����ɥơ��֥뤫�������ʤ���Фʤ�ʤ�����\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            print(" <th nowrap class='r_b'>No</th><th nowrap class='r_b'>�Ұ�No</th><th nowrap class='r_b'>��̾</th><th nowrap class='r_b'>����</th><th nowrap class='r_b'>�ȿ�</th><th nowrap class='r_b'>�ͻ�</th><th nowrap class='r_b'>��Ͽ��</th><th nowrap class='r_b'>�ѹ���</th><th nowrap class='r_b'>ͭ��</th>\n");
            $num = count($res[0]);
            for($r=0;$r<$rows;$r++){
                print("<tr>\n");
                echo "  <form method='post' action='cd_table_mnt.php'>\n";
                print(" <td class='winbox' align='center'><input type='submit' name='copy' value='" . ($r + 1) . "'></td>\n");
                echo "      <input type='hidden' name='cd_sel' value='del'>\n";
                echo "      <input type='hidden' name='uid' value='" . $res[$r][0] . "'>\n";
                echo "  </form>\n";
                for ($n=0; $n<$num; $n++) {
                    if ($res[$r][$n] == "")
                        echo("<td class='winbox' nowrap align='center'>---</td>\n");
                    else
                        echo("<td class='winbox' nowrap align='center'>" . $res[$r][$n] . "</td>\n");
                }
                echo "</tr>\n";
            }
            echo "</table>\n";
            echo "    </td></tr>\n";
            echo "</table> <!----------------- ���ߡ�End ------------------>\n";
        }

        // view �ɲä�ɬ�פʰ���ɽ��
        $query = "
            SELECT u.uid, u.name, c.act_id, c.orga_id, c.pers_id, c.date_add, c.date_chg, c.cd_flg 
            FROM user_detailes AS u LEFT OUTER JOIN cd_table AS c USING(uid) 
            WHERE c.uid IS NULL AND retire_date IS NULL AND u.sid != 31 AND u.pid != 120 AND u.uid NOT LIKE '99%'
        ";
        $res=array();
        if (($rows=getResult2($query,$res)) >= 1) {     // foreach�Ѥ�query�¹�
            echo "<hr>\n";
            echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            // echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
            echo "  <form method='post' action='cd_table_mnt.php'>\n";
            echo "  <caption>�����ɥơ��֥���ɲä��ʤ���Фʤ�ʤ�����\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            echo "  <th nowrap class='y_b winbox'>No</th><th nowrap class='y_b winbox'>�Ұ�No</th><th nowrap class='y_b winbox'>��̾</th><th nowrap class='y_b winbox'>����</th><th nowrap class='y_b winbox'>�ȿ�</th><th nowrap class='y_b winbox'>�ͻ�</th><th nowrap class='y_b winbox'>��Ͽ��</th><th nowrap class='y_b winbox'>�ѹ���</th><th nowrap class='y_b winbox'>ͭ��</th>\n";
            $num = count($res[0]);
            for ($r=0; $r<$rows; $r++) {
                echo "<tr>\n";
                echo "  <form method='post' action='cd_table_mnt.php'>\n";
                echo " <td class='winbox' align='center'><input type='submit' name='copy' value='" . ($r + 1) . "'></td>\n";
                echo "      <input type='hidden' name='cd_sel' value='add'>\n";
                echo "      <input type='hidden' name='uid' value='" . $res[$r][0] . "'>\n";
                echo "  </form>\n";
                for ($n=0; $n<$num; $n++) {
                    if ($res[$r][$n] == "")
                        echo "<td class='winbox' nowrap align='center'>---</td>\n";
                    else
                        echo "<td class='winbox' nowrap align='center'>" . $res[$r][$n] . "</td>\n";
                }
                echo "</tr>\n";
            }
            echo "</table>\n";
            echo "    </td></tr>\n";
            echo "</table> <!----------------- ���ߡ�End ------------------>\n";
        }

        // view ����ɽ��
        $query = "SELECT c.uid, u.name, c.act_id, a.s_name, c.orga_id, c.pers_id, c.date_add, c.date_chg, c.cd_flg 
            FROM cd_table AS c LEFT JOIN act_table AS a USING(act_id) LEFT OUTER JOIN user_detailes AS u 
            USING(uid) ORDER BY u.sid ASC, c.act_id ASC, pid DESC, uid ASC OFFSET $offset LIMIT " . PAGE;
        $res=array();
        if (($rows=getResult2($query,$res)) >= 1) {     // 
            echo "<hr>\n";
            echo "<table bgcolor='#d6d3ce'  cellspacing='0' cellpadding='3' border='1'>\n";
            echo "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            echo "<table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            // echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
            echo "  <form method='post' action='cd_table_mnt.php'>\n";
            echo "  <caption>�Ұ��ֹ桦�������ȿ����ͻ������ɥơ��֥����\n";
            echo "      <input type='submit' name='backward' value='����'>\n";
            echo "      <input type='submit' name='forward' value='����'>\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            print(" <th nowrap class='b_w winbox'>No</th><th nowrap class='b_w winbox'>�Ұ�No</th><th nowrap class='b_w winbox'>��̾</th><th nowrap class='b_w winbox'>����</th><th nowrap class='b_w winbox'>��������̾</th><th nowrap class='b_w winbox'>�ȿ�</th><th nowrap class='b_w winbox'>�ͻ�</th><th nowrap class='b_w winbox'>��Ͽ��</th><th nowrap class='b_w winbox'>�ѹ���</th><th nowrap class='b_w winbox'>ͭ��</th>\n");
            $num = count($res[0]);
            for($r=0;$r<$rows;$r++){
                print("<tr>\n");
                echo "  <form method='post' action='cd_table_mnt.php'>\n";
                print(" <td class='winbox' align='center'><input type='submit' name='copy' value='" . ($r + $offset + 1) . "'></td>\n");
                echo "      <input type='hidden' name='cd_sel' value='chg'>\n";
                echo "      <input type='hidden' name='uid' value='" . $res[$r][0] . "'>\n";
                echo "  </form>\n";
                for ($n=0; $n<$num; $n++) {
                    if ($res[$r][$n] == "")
                        echo("<td class='winbox' nowrap align='center'>---</td>\n");
                    else
                        echo("<td class='winbox' nowrap align='center'>" . $res[$r][$n] . "</td>\n");
                }
                echo "</tr>\n";
            }
            echo "</table>\n";
            echo "    </td></tr>\n";
            echo "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
    ?>
    </center>
</body>
</html>
<?php
    // �ɲä�ɬ�פʥꥹ��
    ///// �Ұ��ǡ����ˤ��äƷ��������ɥơ��֥��̵���ԤΥꥹ�� (�����࿦�ԤȽи���)
    $query = "SELECT u.uid, u.name FROM user_detailes AS u LEFT OUTER JOIN cd_table AS c USING(uid) 
            WHERE c.uid IS NULL AND retire_date IS NULL AND u.sid != 31";
    // �����ɬ�פʥꥹ��
    ///// ���������ɥơ��֥�ˤ��äƼҰ��ǡ����Ǥ��࿦���Ͻи����Ƥ���ԤΥꥹ��
    $query = "SELECT c.uid, u.name FROM cd_table AS c LEFT JOIN user_detailes AS u USING(uid) 
            WHERE retire_date IS NOT NULL OR u.sid = 31";
?>

