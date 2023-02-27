<?php
//////////////////////////////////////////////////////////////////////////////
// ���ƥ���(��ʬ�����)�ޥ��������ݼ� (category_item cate_allocation)       //
// Copyright (C) 2002-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/10/02 Created   category_mnt.php                                    //
// 2002/11/26 UPDATE �κݤ� dest_id �Τߤξ������ cate_id ���ɲ�         //
// 2003/05/15 $_SESSION['cate_id'] = ""; �򥳥��� ���å��ߥ�            //
//            if($_POST['cate_rate'] == "" �ξ���ɲ� �֥�󥯻����б�      //
// 2004/10/28 user_check()function���ɲä��Խ������桼���������          //
// 2005/11/02 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2007/04/18 �桼������300144��ë���ɲ� ��ë                               //
// 2009/03/12 �桼������014737����ɲ� ��                                   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

////////////// ����������
$menu->set_site(INDEX_PL, 11);              // site_index=INDEX_PL(»�ץ�˥塼) site_id=11(��ʬ������Ψ)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��ʬ����ܥޥ�����������Υ��ƥʥ�');

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
        $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥϥ��ƥ��꡼�ޥ��������ݼ�Ͻ���ޤ��� ����ô���Ԥ�Ϣ���Ʋ�������";
        return FALSE;
    }
}

$today = date("Y-m-d");
if (isset($_POST['cate_id1'])) {
    $_SESSION['cate_id'] = $_POST['cate_id1'];
} elseif (isset($_POST['cate_id2'])) {
    $_SESSION['cate_id'] = $_POST['cate_id2'];
} elseif (isset($_POST['cate_id'])) {
    $_SESSION['cate_id'] = $_POST['cate_id'];
} else {
    //  $_SESSION['cate_id'] = "";
    $current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
    $url_referer = $_SERVER["HTTP_REFERER"];        // �ƽФ�Ȥ�URL����¸
    if (!eregi($current_script, $url_referer)) {    // ��ʬ���ȤǸƤӽФ��Ƥ��ʤ����
        $_SESSION['cate_id'] = "";                  // �֥�󥯤˽����
    }
}

////////////////////////// cate_allocation �� �ѹ� UPDATE
while ( isset($_POST['dest_update']) ) {
    if (!user_check($uid)) break;
    $query = "select dest_id from cate_allocation where dest_id=" . $_POST['dest_update'];
    $res_chg = array();
    if((getResult($query,$res_chg))==0) /////////// ���¾�Υ桼�����˺�����줿���Υ����å�
        $_SESSION['s_sysmsg'] = "���¾�Υ桼�����ˤ�äƺ������ޤ����Τ��ѹ�����ޤ���!";
    else{
        if ($_POST['cate_rate'] == "") {
            $_POST['cate_rate'] = 0;
        }
        $query = "update cate_allocation set cate_rate=" . $_POST['cate_rate'] . ",";
        $query .= "group_id=" . $_POST['group_id'] . " ";
        $query .= "where dest_id=" . $_POST['dest_update'];
        $query .= " and cate_id=" . $_POST['cate_id'];
        if((getResult($query,$res_chg))>=0)
            $_SESSION['s_sysmsg'] = $_POST['dest_update'] . " : ���ѹ����ޤ���!";
        else
            $_SESSION['s_sysmsg'] = $_POST['dest_update'] . " : ���ѹ��˼��Ԥ��ޤ���!";
    }
    break;
}
////////////////////////// cate_allocation �� ������ ��� ��Ͽ
while ( isset($_POST['all_add']) ) {
    if (!user_check($uid)) break;
    $res_add = array();
    $query = "select act_id,s_name from act_table order by act_id ASC";
    $res = array();
    $rows_act = getResult($query,$res);
    for ($a=0; $a<$rows_act; $a++) {
        $query = "select cate_id from cate_allocation where cate_id=" . $_SESSION['cate_id'] . " and dest_id=" . $res[$a]['act_id'];
        if ( (getResult($query,$res_add)) == 0) { /////////// ��Ͽ�ѤΥ����å�
            $query = "insert into cate_allocation (orign_id,cate_id,group_id,dest_id) values(";
            $query .= $_SESSION['cate_id'] . ",";
            $query .= $_SESSION['cate_id'] . ",";
            $query .= $_SESSION['cate_id'] . ",";
            $query .= $res[$a]['act_id'] . ")";
            if ( (getResult($query,$res_add)) >= 0)
                $_SESSION['s_sysmsg'] .= $res[$a]['act_id'] . "��" . $res[$a]['s_name'] . "����Ͽ<br>";
            else
                $_SESSION['s_sysmsg'] .= $res[$a]['act_id'] . "��" . $res[$a]['s_name'] . "����Ͽ����<br>";
        }
    }
    break;
}
////////////////////////// cate_allocation �� ������ ��� ���
while ( isset($_POST['all_del']) ) {
    if (!user_check($uid)) break;
    $query = "select cate_item from category_item where cate_id=" . $_SESSION['cate_id'];
    $res = array();
    $rows = getResult($query,$res);
    $query = "select cate_id from cate_allocation where cate_id=" . $_SESSION['cate_id'];
    $res_del = array();
    if ( (getResult($query,$res_del)) >= 1) { /////////// ����ѤΥ����å�
        $query = "delete from cate_allocation where cate_id=" . $_SESSION['cate_id'];
        if ( (getResult($query,$res_del)) >= 0)
            $_SESSION['s_sysmsg'] = $_SESSION['cate_id'] . "��" . $res[0]['cate_item'] . "���������ƺ�����ޤ���";
        else
            $_SESSION['s_sysmsg'] = $_SESSION['cate_id'] . "��" . $res[0]['cate_item'] . "������ ��� ����";
    }
    break;
}
////////////////////////// cate_allocation �� ���� �ɲ�
while (isset($_POST['allo_add'])) {
    if (!user_check($uid)) break;
    $res_add = array();
    $query = "select cate_id from cate_allocation where cate_id=" . $_SESSION['cate_id'] . " and dest_id=" . $_POST['act_id'];
    if((getResult($query,$res_add))==0){ /////////// ��Ͽ�ѤΥ����å�
        $query = "insert into cate_allocation (orign_id,cate_id,group_id,dest_id) values(";
        $query .= $_SESSION['cate_id'] . ",";
        $query .= $_SESSION['cate_id'] . ",";
        $query .= $_SESSION['cate_id'] . ",";
        $query .= $_POST['act_id'] . ")";
        if((getResult($query,$res_add))>=0)
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "����Ͽ���ޤ���";
        else
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "����Ͽ����";
    }else
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . "����¾�Υ桼��������Ͽ����ޤ���!";
    break;
}
////////////////////////// cate_allocation �� ���� ���
while (isset($_POST['allo_del'])) {
    if (!user_check($uid)) break;
    $res_del = array();
    $query = "select cate_id from cate_allocation where cate_id=" . $_SESSION['cate_id'] . " and dest_id=" . $_POST['dest_id'];
    if((getResult($query,$res_del))>=1){ /////////// ��Ͽ�ѤΥ����å�
        $query = "delete from cate_allocation where cate_id=" . $_SESSION['cate_id'] . " and dest_id=" . $_POST['dest_id'];
        if((getResult($query,$res_add))>=0)
            $_SESSION['s_sysmsg'] .= $_POST['dest_id'] . "�������ޤ���";
        else
            $_SESSION['s_sysmsg'] .= $_POST['dest_id'] . "��������";
    }else
        $_SESSION['s_sysmsg'] = $_POST['dest_id'] . "����¾�Υ桼�����˺������ޤ���!";
    break;
}
////////////////////////// allocation_item �� �ɲ�
while (isset($_POST['register'])) {
    if (!user_check($uid)) break;
    $query = "select cate_id from category_item where cate_id=" . $_SESSION['cate_id'];
    $res_add = array();
    if((getResult($query,$res_add))>=1) /////////// ���¾�Υ桼��������Ͽ���줿���Υ����å�
        $_SESSION['s_sysmsg'] = "���¾�Υ桼��������Ͽ����ޤ����Τ� ���ľ���Ʋ�����!";
    else{
        $query = "insert into category_item (cate_id,cate_item,cate_note,cate_group) values(";
        $query .= $_SESSION['cate_id'] . ",";
        $query .= "'" . $_POST['cate_item'] . "',";
        $query .= "'" . $_POST['cate_note'] . "',";
        $query .= $_POST['cate_group'] . ")";
        if((getResult($query,$res_add))>=0)
            $_SESSION['s_sysmsg'] = $_POST['cate_item'] . "��" . $_SESSION['cate_id'] . "�֤���Ͽ���ޤ�����";
        else
            $_SESSION['s_sysmsg'] = $_POST['cate_item'] . " : ����Ͽ�˼��Ԥ��ޤ���!";
    }
    break;
}
////////////////////////// allocation_item �� �ѹ�
while (isset($_POST['change'])) {
    if (!user_check($uid)) break;
    $query = "select cate_id from category_item where cate_id=" . $_SESSION['cate_id'];
    $res_chg = array();
    if((getResult($query,$res_chg))==0) /////////// ���¾�Υ桼�����˺�����줿���Υ����å�
        $_SESSION['s_sysmsg'] = "���¾�Υ桼�����ˤ�äƺ������ޤ����Τ��ѹ�����ޤ���!";
    else{
        $query = "update category_item set cate_item='" . $_POST['cate_item'] . "',";
        $query .= "cate_note='" . $_POST['cate_note'] . "',";
        $query .= "cate_group=" . $_POST['cate_group'] . " ";
        $query .= "where cate_id=" . $_SESSION['cate_id'];
        if((getResult($query,$res_chg))>=0)
            $_SESSION['s_sysmsg'] = $_SESSION['cate_id'] . "��" . $_POST['cate_item'] . " : ���ѹ����ޤ�����";
        else
            $_SESSION['s_sysmsg'] = $_POST['cate_item'] . " : ���ѹ��˼��Ԥ��ޤ���!";
    }
    break;
}
////////////////////////// category_item �� ���
while (isset($_POST['delete'])) {
    if (!user_check($uid)) break;
    $query = "select cate_id from category_item where cate_id=" . $_SESSION['cate_id'];
    $res_del = array();
    if((getResult($query,$res_del))==0) /////////// ���¾�Υ桼�����˺�����줿���Υ����å�
        $_SESSION['s_sysmsg'] = "���¾�Υ桼�����ˤ�äƺ������ޤ����ΤǺ������ޤ���!";
    else{
        $query = "delete from category_item where cate_id=" . $_SESSION['cate_id'];
        if((getResult($query,$res_del))>=0)
            $_SESSION['s_sysmsg'] = $_SESSION['cate_id'] . "��" . $_POST['cate_item'] . " : �������ޤ�����";
        else
            $_SESSION['s_sysmsg'] = $_POST['cate_item'] . " : �����˼��Ԥ��ޤ���!";
    }
    break;
}
///////////////////////////////////////////////////////////// ����ɽ(��¸�Υǡ���ɽ��)
///////// ���ܥ��롼��
$query = "select cate_id,cate_item from category_item where cate_id<=100 order by cate_group ASC, cate_id ASC";
$res_item = array();
$cate_id1 = array();
$cate_item1 = array();
if($rows_item1 = getResult($query,$res_item)){
    for($i=0;$i<$rows_item1;$i++){
        $cate_id1[$i] = $res_item[$i]['cate_id'];
        $cate_item1[$i] = $res_item[$i]['cate_item'];
    }
}
///////// �桼�������ꥰ�롼��
$query = "select cate_id,cate_item from category_item where cate_id>=101 order by cate_group ASC, cate_id ASC";
$res_item = array();
$cate_id2 = array();
$cate_item2 = array();
if($rows_item2 = getResult($query,$res_item)){
    for($i=0;$i<$rows_item2;$i++){
        $cate_id2[$i] = $res_item[$i]['cate_id'];
        $cate_item2[$i] = $res_item[$i]['cate_item'];
    }
}
/////////////////////////// ���ܥ��롼�פο�����Ͽ��ID ����
$query = "select max(cate_id) from category_item where cate_id<=100";
$res_max = array();
if($rows_max = getResult($query,$res_max)){
    if($res_max[0]['max'] < 100) ////////////// ��100 �ޤǤϹ��ܥ��롼�פ�ͽ��Ѥ�
        $cate_id_max1 = ($res_max[0]['max'] + 1);
    else
        $_SESSION['s_sysmsg'] = "ͽ��Ѥߤ�ID�����ƻȤ��ޤ���!�����Ԥ�Ϣ���Ʋ�����!";
}
/////////////////////////// �桼�������ꥰ�롼�פο�����Ͽ��ID ����
$query = "select max(cate_id) from category_item";
$res_max = array();
if($rows_max = getResult($query,$res_max)){
    if($res_max[0]['max'] >= 101) ////////////// 101�� �桼��������ID
        $cate_id_max2 = ($res_max[0]['max'] + 1);
    else
        $cate_id_max2 = 101;
}
////////////////////////// �ɲá��ѹ������������
if(isset($_POST['select'])){
    $query = "select cate_item,cate_note,cate_group from category_item where cate_id=" . $_SESSION['cate_id'];
    $res_chk = array();
    if($rows_chk = getResult($query,$res_chk)){
        $chg_del = 1; ////// �ѹ������ (�ǡ�������)
        $cate_id = $_SESSION['cate_id'];
        $cate_item = $res_chk[0]['cate_item'];
        $cate_note = $res_chk[0]['cate_note'];
        $cate_group = $res_chk[0]['cate_group'];
    }else
        $add = 1; ////////// �ɲ� (�ǡ���̵��)
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

<link rel='stylesheet' href='allocation.css' type='text/css'>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <tr>
                <form action='category_mnt.php' method='post'>
                    <td align='center'>
                        ���ܥ��롼��
                        <select name='cate_id1' class='pt11b'>
                            <?php
                            for($i=0;$i<$rows_item1;$i++){
                                if($_SESSION['cate_id'] == $cate_id1[$i])
                                    echo "<option value='" . $cate_id1[$i] . "' selected>" . $cate_item1[$i] . "</option>\n";
                                else
                                    echo "<option value='" . $cate_id1[$i] . "'>" . $cate_item1[$i] . "</option>\n";
                            }
                            if($_SESSION['cate_id'] == $cate_id_max1)
                                echo "<option value='$cate_id_max1' class='fc_red' selected>���� �ɲ�</option>\n";
                            else
                                echo "<option value='$cate_id_max1' class='fc_red'>���� �ɲ�</option>\n";
                            ?>
                        </select>
                    </td>
                    <td align='center'>
                        <input type='submit' name='select' value='�¹�' >
                    </td>
                </form>
                <form action='category_mnt.php' method='post'>
                    <td align='center'>
                        ��ͳ���ꥰ�롼��
                        <select name='cate_id2' class='pt11b'>
                            <?php
                            for($i=0;$i<$rows_item2;$i++){
                                if($_SESSION['cate_id'] == $cate_id2[$i])
                                    echo "<option value='" . $cate_id2[$i] . "' selected>" . $cate_item2[$i] . "</option>\n";
                                else
                                    echo "<option value='" . $cate_id2[$i] . "'>" . $cate_item2[$i] . "</option>\n";
                            }
                            if($_SESSION['cate_id'] == $cate_id_max2)
                                echo "<option value='$cate_id_max2' class='fc_yellow' selected>���� �ɲ�</option>\n";
                            else
                                echo "<option value='$cate_id_max2' class='fc_yellow'>���� �ɲ�</option>\n";
                            ?>
                        </select>
                    </td>
                    <td align='center'>
                        <input type='submit' name='select' value='�¹�' >
                    </td>
                </form>
            </tr>
        </table>
        <?php
        if(isset($add)){ ////////////////////////////// �ɲäΥ֥饦����ɽ��
        ?>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='category_mnt.php' method='post'>
                <caption>���������С�Ͽ</caption>
                <th>ʬ�����̾</th><th>������</th><th colspan='2'>���롼��</th>
                <tr>
                    <td><input type='text' name='cate_item' value='' size='16' maxlength='10'></td>
                    <td><input type='text' name='cate_note' value='' size='80' maxlength='50'></td>
                    <?php
                    echo "<td><input type='text' name='cate_group' value='" . $_SESSION['cate_id'] . "' size='7' maxlength='6' class='right'></td>\n";
                    echo "<td><input type='submit' name='register' value='��Ͽ' ></td>\n";
                    ?>
                </tr>
            </form>
        </table>
        <?php
        }
        if(isset($chg_del)){ ////////////////////////////// �ѹ�������Υ֥饦����ɽ��
        ?>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='category_mnt.php' method='post'>
                <caption>�ѹ������</caption>
                <th>ʬ�����̾</th><th>������</th><th>Group</th><th><input type='submit' name='delete' value='���' class='fc_red'></th>
                <tr>
                    <td><input type='text' name='cate_item' value='<?php echo $cate_item ?>' size='16' maxlength='10'></td>
                    <td><input type='text' name='cate_note' value='<?php echo $cate_note ?>' size='80' maxlength='50'></td>
                    <td><input type='text' name='cate_group' value='<?php echo $cate_group ?>' size='7' maxlength='6' class='right'></td>
                    <td><input type='submit' name='change' value='�ѹ�' class='fc_blue'></td>
                </tr>
            </form>
        </table>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='category_mnt.php' method='post'>
                <?php /////////////////////////////////////////////// ����������ɲá��ѹ������
                echo "<input type='hidden' name='select' value='" . $_POST['select'] . "'>\n"; //// ��ɽ���Τ���
                $query = "select s_name,act_id,orign_id,cate_id,dest_id,group_id,cate_rate from act_table left outer join cate_allocation on act_id=dest_id and cate_id=" . $_SESSION['cate_id'] . " order by group_id ASC, act_id ASC";    
                $res_allo = array();
                if(($rows_allo=getResult($query,$res_allo))==0){
                    echo "<div>���礬��Ͽ����Ƥ��ޤ��󡣲��Υܥ�������������Ͽ����ɬ�פʤ�����ϸ夫�������Ʋ�������</div>\n";
                    echo "<input type='submit' name='all_add' value='��������Ͽ'>\n";
                }else{
                    echo "<caption>����Υ��ƥʥ󥹡�<input type='submit' name='all_del' value='�����' class='fc_red'>\n";
                    echo "<input type='submit' name='all_add' value='�����Ͽ' class='fc_blue'></caption>\n";
                    echo "<th>������</th><th>ʬ��</th><th>��Ͽ�����</th><th>��������̾</th><th>����Ψ/������</th><th>Group</th><th>����/����</th>\n";
                    echo "</form>\n";
                    for($a=0;$a<$rows_allo;$a++){
                        echo "<form action='category_mnt.php' method='post'>\n";
                        echo "<tr>\n";
                        if($res_allo[$a]['dest_id'] == ""){
                            echo "  <td align='center'>---</td>\n";
                            echo "  <td align='center'>---</td>\n";
                            echo "  <td align='center'><input type='submit' name='allo_add' value='��Ͽ' class='fc_blue'></td>\n";
                            echo "  <td>" . $res_allo[$a]['s_name'] . "</td>\n";
                            echo "  <td align='center'>---</td>\n";
                            echo "  <td align='center'>---</td>\n";
                            echo "  <td align='center'>" . $res_allo[$a]['act_id'] . "</td>\n";
                            echo "  <input type='hidden' name='act_id' value='" . $res_allo[$a]['act_id'] . "'>\n";
                            echo "  <input type='hidden' name='select' value='" . $_POST['select'] . "'>\n"; //// ��ɽ���Τ���
                        }else{
                            echo "  <td align='right'>" . $res_allo[$a]['orign_id'] . "</td>\n";
                            echo "  <td align='right'>" . $res_allo[$a]['cate_id'] . "</td>\n";
                            echo "  <td align='center'><input type='submit' name='allo_del' value='���' class='fc_red'></td>\n";
                            echo "  <td>" . $res_allo[$a]['s_name'] . "</td>\n";
                            echo "  <td align='center'><input type='text' name='cate_rate' value='" . $res_allo[$a]['cate_rate'] . "' size='5' maxlength='3' class='right'></td>\n";
                            echo "  <td align='right'><input type='text' name='group_id' value='" . $res_allo[$a]['group_id'] . "' size='5' maxlength='4' class='right'></td>\n";
                            echo "  <td align='center'><input type='submit' name='dest_update' value='" . $res_allo[$a]['dest_id'] . "' class='fc_blue'></td>\n";
                            echo "  <input type='hidden' name='dest_id' value='" . $res_allo[$a]['dest_id'] . "'>\n";
                            echo "  <input type='hidden' name='cate_id' value='" . $res_allo[$a]['cate_id'] . "'>\n";
                            echo "  <input type='hidden' name='select' value='" . $_POST['select'] . "'>\n"; //// ��ɽ���Τ���
                        }
                        echo "</tr>\n";
                        echo "</form>\n";
                    }
                }
                ?>
        </table>
        <?php
        }
        ?>
    </center>
</body>
</html>
