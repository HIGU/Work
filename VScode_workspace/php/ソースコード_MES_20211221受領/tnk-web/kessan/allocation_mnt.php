<?php
//////////////////////////////////////////////////////////////////////////////
// �����������(��ʬ��)����ޥ��������ݼ�                                 //
//            (allocation_item act_allocation �Σ��ĤΥơ��֥�����)       //
// Copyright (C) 2002-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/11/12 Created   allocation.mnt.php                                  //
// 2002/11/12               �������륷���Ȥ��̥ե������allocation.css      //
// 2002/11/26 category_mnt.php ��Ʊ���褦�� UPDATE ���˥�ˡ�����           //
//             �ʤ�褦���ѹ� orign_id ���Խ��Ǥ���褦���ѹ������۸�       //
// 2003/05/15 $_SESSION['allo_id'] = ""; �򥳥��ȥ����� ���å��ߥ�      //
// 2003/12/04 ����Ψ�����Ϥ��֥�󥯤ξ�� SQLʸ�� 0 ����Ū�������       //
// 2004/10/28 user_check()function���ɲä��Խ������桼���������          //
// 2005/11/02 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2007/04/18 �桼������300144��ë���ɲ� ��ë                               //
// 2009/03/11 �桼������014737����ɲ� ��                                   //
// 2016/06/09 ������Ψ���������դ��ɲ�                                    //
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
$menu->set_site(INDEX_PL, 12);              // site_index=INDEX_PL(»�ץ�˥塼) site_id=12(��ʬ������Ψ)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��ʬ��(����)���ܥޥ�����������Ψ�Υ��ƥʥ�');

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
        $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥϥ����������ޥ��������ݼ�Ͻ���ޤ��� ����ô���Ԥ�Ϣ���Ʋ�������";
        return FALSE;
    }
}

$today = date("Y-m-d");
if (isset($_POST['allo_id'])) {
    $_SESSION['allo_id'] = $_POST['allo_id'];
} else {
//    $_SESSION['allo_id'] = "";
    $current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
    $url_referer = $_SERVER["HTTP_REFERER"];        // �ƽФ�Ȥ�URL����¸
    if (!eregi($current_script, $url_referer)) {    // ��ʬ���ȤǸƤӽФ��Ƥ��ʤ����
        $_SESSION['allo_id'] = "";                  // �֥�󥯤˽����
    }
}

////////////////////////// act_allocation �� �ѹ� UPDATE
while ( isset($_POST['dest_update']) ) {
    if (!user_check($uid)) break;
    $query = "select dest_id from act_allocation where dest_id=" . $_POST['dest_update'];
    $res_chg = array();
    if ( (getResult($query,$res_chg)) == 0) {/////////// ���¾�Υ桼�����˺�����줿���Υ����å�
        $_SESSION['s_sysmsg'] = "���¾�Υ桼�����ˤ�äƺ������ޤ����Τ��ѹ�����ޤ���";
    } else {
        if ($_POST['allo_rate'] == '') {
            $_POST['allo_rate'] = '0';      // �֥�󥯤ξ�綯��Ū�� 0 �������
        }
        $query = "update act_allocation set allo_rate=" . $_POST['allo_rate'] . ",";
        $query .= "group_id=" . $_POST['group_id'] . ",orign_id=" . $_POST['orign_id'] . " ";
        $query .= "where dest_id=" . $_POST['dest_update'] . "and allo_id=" . $_POST['allo_id'];
        if ( (getResult($query,$res_chg)) >= 0) {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>" . $_POST['dest_update'] . " : ���ѹ����ޤ�����</font>";
        } else {
            $_SESSION['s_sysmsg'] = $_POST['dest_update'] . " : ���ѹ��˼��Ԥ��ޤ�����";
        }
    }
    break;
}
////////////////////////// act_allocation �� ������ ��� ��Ͽ
while ( isset($_POST['all_add']) ) {
    if (!user_check($uid)) break;
    $res_add = array();
    $query = "select act_id,s_name from act_table order by act_id ASC";
    $res = array();
    $rows_act = getResult($query,$res);
    for ($a=0; $a<$rows_act; $a++) {
        $query = "select allo_id from act_allocation where allo_id=" . $_SESSION['allo_id'] . " and dest_id=" . $res[$a]['act_id'];
        if ( (getResult($query,$res_add)) == 0) { /////////// ��Ͽ�ѤΥ����å�
            $query = "insert into act_allocation (orign_id,allo_id,group_id,dest_id) values(";
            $query .= $_SESSION['allo_id'] . ",";
            $query .= $_SESSION['allo_id'] . ",";
            $query .= $_SESSION['allo_id'] . ",";
            $query .= $res[$a]['act_id'] . ")";
            if ( (getResult($query,$res_add)) >= 0) {
                $_SESSION['s_sysmsg'] .= "<font color='yellow'>" . $res[$a]['act_id'] . "��" . $res[$a]['s_name'] . "����Ͽ</font><br>";
            } else {
                $_SESSION['s_sysmsg'] .= $res[$a]['act_id'] . "��" . $res[$a]['s_name'] . "����Ͽ����<br>";
            }
        }
    }
    break;
}
////////////////////////// act_allocation �� ������ ��� ���
while ( isset($_POST['all_del']) ) {
    if (!user_check($uid)) break;
    $query = "select allo_item from allocation_item where allo_id=" . $_SESSION['allo_id'];
    $res = array();
    $rows = getResult($query,$res);
    $query = "select allo_id from act_allocation where allo_id=" . $_SESSION['allo_id'];
    $res_del = array();
    if ( (getResult($query,$res_del)) >= 1) { /////////// ����ѤΥ����å�
        $query = "delete from act_allocation where allo_id=" . $_SESSION['allo_id'];
        if ( (getResult($query,$res_del)) >= 0) {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>" . $_SESSION['allo_id'] . "��" . $res[0]['allo_item'] . "���������ƺ�����ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] = $_SESSION['allo_id'] . "��" . $res[0]['allo_item'] . "������ ��� ����";
        }
    }
    break;
}
////////////////////////// act_allocation �� ���� �ɲ�
while ( isset($_POST['allo_add']) ) {
    if (!user_check($uid)) break;
    $res_add = array();
    $query = "select allo_id from act_allocation where allo_id=" . $_SESSION['allo_id'] . " and dest_id=" . $_POST['act_id'];
    if ( (getResult($query,$res_add)) == 0) { /////////// ��Ͽ�ѤΥ����å�
        $query = "insert into act_allocation (orign_id,allo_id,group_id,dest_id) values(";
        $query .= $_SESSION['allo_id'] . ",";
        $query .= $_SESSION['allo_id'] . ",";
        $query .= $_SESSION['allo_id'] . ",";
        $query .= $_POST['act_id'] . ")";
        if ( (getResult($query,$res_add)) >= 0) {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>" . $_POST['act_id'] . "����Ͽ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] = $_POST['act_id'] . "����Ͽ����";
        }
    } else {
        $_SESSION['s_sysmsg'] = $_POST['act_id'] . "����¾�Υ桼��������Ͽ����ޤ�����";
    }
    break;
}
////////////////////////// act_allocation �� ���� ���
while ( isset($_POST['allo_del']) ) {
    if (!user_check($uid)) break;
    $res_del = array();
    $query = "select allo_id from act_allocation where allo_id=" . $_SESSION['allo_id'] . " and dest_id=" . $_POST['dest_id'];
    if ( (getResult($query,$res_del)) >= 1) { /////////// ��Ͽ�ѤΥ����å�
        $query = "delete from act_allocation where allo_id=" . $_SESSION['allo_id'] . " and dest_id=" . $_POST['dest_id'];
        if ( (getResult($query,$res_add)) >= 0) {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>" . $_POST['dest_id'] . "�������ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= $_POST['dest_id'] . "��������";
        }
    } else {
        $_SESSION['s_sysmsg'] = $_POST['dest_id'] . "����¾�Υ桼�����˺������ޤ�����";
    }
    break;
}
////////////////////////// allocation_item �� �ɲ�
while ( isset($_POST['register']) ) {
    if (!user_check($uid)) break;
    $query = "select allo_id from allocation_item where allo_id=" . $_SESSION['allo_id'];
    $res_add = array();
    if ( (getResult($query,$res_add)) >= 1) { /////////// ���¾�Υ桼��������Ͽ���줿���Υ����å�
        $_SESSION['s_sysmsg'] = "���¾�Υ桼��������Ͽ����ޤ����Τ� ���ľ���Ʋ�������";
    } else {
        $query = "insert into allocation_item (allo_id,allo_item,allo_method,allo_group) values(";
        $query .= $_SESSION['allo_id'] . ",";
        $query .= "'" . $_POST['allo_item'] . "',";
        $query .= "'" . $_POST['allo_method'] . "',";
        $query .= $_POST['allo_group'] . ")";
        if ( (getResult($query,$res_add)) >= 0) {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>" . $_POST['allo_item'] . "��" . $_SESSION['allo_id'] . "�֤���Ͽ���ޤ�����</font>";
        } else {
            $_SESSION['s_sysmsg'] = $_POST['allo_item'] . " : ����Ͽ�˼��Ԥ��ޤ�����";
        }
    }
    break;
}
////////////////////////// allocation_item �� �ѹ�
while ( isset($_POST['change']) ) {
    if (!user_check($uid)) break;
    $query = "select allo_id from allocation_item where allo_id=" . $_SESSION['allo_id'];
    $res_chg = array();
    if ( (getResult($query,$res_chg)) == 0) { /////////// ���¾�Υ桼�����˺�����줿���Υ����å�
        $_SESSION['s_sysmsg'] = "���¾�Υ桼�����ˤ�äƺ������ޤ����Τ��ѹ�����ޤ���";
    } else {
        $query = "update allocation_item set allo_item='" . $_POST['allo_item'] . "',";
        $query .= "allo_method='" . $_POST['allo_method'] . "',";
        $query .= "allo_group=" . $_POST['allo_group'] . " ";
        $query .= "where allo_id=" . $_SESSION['allo_id'];
        if ( (getResult($query,$res_chg)) >= 0) {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>" . $_SESSION['allo_id'] . "��" . $_POST['allo_item'] . " : ���ѹ����ޤ�����</font>";
        } else {
            $_SESSION['s_sysmsg'] = $_POST['allo_item'] . " : ���ѹ��˼��Ԥ��ޤ�����";
        }
    }
    break;
}
////////////////////////// allocation_item �� ���
while ( isset($_POST['delete']) ) {
    if (!user_check($uid)) break;
    $query = "select allo_id from allocation_item where allo_id=" . $_SESSION['allo_id'];
    $res_del = array();
    if ( (getResult($query,$res_del)) == 0) { /////////// ���¾�Υ桼�����˺�����줿���Υ����å�
        $_SESSION['s_sysmsg'] = "���¾�Υ桼�����ˤ�äƺ������ޤ����ΤǺ������ޤ���";
    } else {
        $query = "delete from allocation_item where allo_id=" . $_SESSION['allo_id'];
        if ( (getResult($query,$res_del)) >= 0) {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>" . $_SESSION['allo_id'] . "��" . $_POST['allo_item'] . " : �������ޤ�����</font>";
        } else {
            $_SESSION['s_sysmsg'] = $_POST['allo_item'] . " : �����˼��Ԥ��ޤ�����";
        }
    }
    break;
}
///////////////////////////////////////////////////////////// ����ɽ(��¸�Υǡ���ɽ��)
///////// ������ܥޥ�����
$query = "select allo_id,allo_item from allocation_item order by allo_group ASC, allo_id ASC";
$res_item = array();
$allo_id_view = array();
$allo_item_view = array();
if ( $rows_item = getResult($query,$res_item) ) {
    for ($i=0; $i<$rows_item; $i++) {
        $allo_id_view[$i] = $res_item[$i]['allo_id'];
        $allo_item_view[$i] = $res_item[$i]['allo_item'];
    }
}
/////////////////////////// ������ܥޥ������ο�����Ͽ��ID ����
$query = "select max(allo_id) from allocation_item";
$res_max = array();
if ( $rows_max = getResult($query,$res_max) ) {
    if ($res_max[0]['max'] < 32768) { ////////////// ��32767 �ޤ�smallint ���ϰ� 15�ӥå�
        $allo_id_max = ($res_max[0]['max'] + 1);
    } else {
        $_SESSION['s_sysmsg'] = "ID�����ƻȤ��ޤ����������Ԥ�Ϣ���Ʋ�������";
    }
}
////////////////////////// �ɲá��ѹ������������
if ( isset($_POST['select']) ) {
    $query = "select allo_item,allo_method,allo_group from allocation_item where allo_id=" . $_SESSION['allo_id'];
    $res_chk = array();
    if ($rows_chk = getResult($query,$res_chk)) {
        $chg_del = 1; ////// �ѹ������ (�ǡ�������)
        $allo_id = $_SESSION['allo_id'];
        $allo_item = $res_chk[0]['allo_item'];
        $allo_method = $res_chk[0]['allo_method'];
        $allo_group = $res_chk[0]['allo_group'];
    } else {
        $add = 1; ////////// �ɲ� (�ǡ���̵��)
    }
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
                <form action='allocation_mnt.php' method='post'>
                    <td align='center'>
                        ������ܥޥ�����
                        <select name='allo_id' class='pt11b'>
                            <?php
                            for($i=0;$i<$rows_item;$i++){
                                if($_SESSION['allo_id'] == $allo_id_view[$i])
                                    echo "<option value='" . $allo_id_view[$i] . "' selected>" . $allo_item_view[$i] . "</option>\n";
                                else
                                    echo "<option value='" . $allo_id_view[$i] . "'>" . $allo_item_view[$i] . "</option>\n";
                            }
                            if($_SESSION['allo_id'] == $allo_id_max)
                                echo "<option value='$allo_id_max' class='fc_red' selected>���� �ɲ�</option>\n";
                            else
                                echo "<option value='$allo_id_max' class='fc_red'>���� �ɲ�</option>\n";
                            ?>
                        </select>
                    </td>
                    <td align='center'>
                        <input type='submit' name='select' value='�¹�' >
                    </td>
                </form>
            </tr>
        </table>
        �� ������Ψ������ˤ�ƱΨ�϶ػߡ��ʤ�٤�10�ΰ̤�����Ǥ���褦�˴ݤ�롣<BR>
           �����ɲû�������򵡳���Ψ�������ˤ��٤��ɲä��뤳�ȡ�
        <?php
        if(isset($add)){ ////////////////////////////// �ɲäΥ֥饦����ɽ��
        ?>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='allocation_mnt.php' method='post'>
                <caption>���������С�Ͽ</caption>
                <th>ʬ�����̾</th><th>������ˡ �� ������</th><th colspan='2'>���롼��</th>
                <tr>
                    <td><input type='text' name='allo_item' value='' size='40' maxlength='20'></td>
                    <td><input type='text' name='allo_method' value='' size='80' maxlength='50'></td>
                    <?php
                    echo "<td><input type='text' name='allo_group' value='" . $_SESSION['allo_id'] . "' size='7' maxlength='6' class='right'></td>\n";
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
            <form action='allocation_mnt.php' method='post'>
                <caption>�ѹ������</caption>
                <th>ʬ�����̾</th><th>������ˡ �� ������</th><th>Group</th><th><input type='submit' name='delete' value='���' class='fc_red'></th>
                <tr>
                    <td><input type='text' name='allo_item' value='<?php echo $allo_item ?>' size='40' maxlength='20'></td>
                    <td><input type='text' name='allo_method' value='<?php echo $allo_method ?>' size='80' maxlength='50'></td>
                    <td><input type='text' name='allo_group' value='<?php echo $allo_group ?>' size='7' maxlength='6' class='right'></td>
                    <td><input type='submit' name='change' value='�ѹ�' class='fc_blue'></td>
                </tr>
            </form>
        </table>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='allocation_mnt.php' method='post'>
                <?php /////////////////////////////////////////////// ����������ɲá��ѹ������
                echo "<input type='hidden' name='select' value='" . $_POST['select'] . "'>\n"; //// ��ɽ���Τ���
                $query = "select s_name,act_id,orign_id,allo_id,dest_id,group_id,allo_rate from act_table left outer join act_allocation on act_id=dest_id and allo_id=" . $_SESSION['allo_id'] . " order by group_id ASC, act_id ASC"; 
                $res_allo = array();
                if(($rows_allo=getResult($query,$res_allo))==0){
                    echo "<div>���礬��Ͽ����Ƥ��ޤ��󡣲��Υܥ�������������Ͽ����ɬ�פʤ�����ϸ夫�������Ʋ�������</div>\n";
                    echo "<input type='submit' name='all_add' value='��������Ͽ'>\n";
                }else{
                    echo "<caption>����Υ��ƥʥ󥹡�<input type='submit' name='all_del' value='�����' class='fc_red'>\n";
                    echo "<input type='submit' name='all_add' value='�����Ͽ' class='fc_blue'></caption>\n";
                    echo "<th>���긵</th><th>ʬ��</th><th>��Ͽ�����</th><th>��������̾</th><th>����Ψ</th><th>Group</th><th>����/����</th>\n";
                    echo "</form>\n";
                    for($a=0;$a<$rows_allo;$a++){
                        echo "<form action='allocation_mnt.php' method='post'>\n";
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
                            echo "  <td align='right'><input type='text' name='orign_id' value='" . $res_allo[$a]['orign_id'] . "' size='5' maxlength='4' class='right'></td>\n";
                            echo "  <td align='right'>" . $res_allo[$a]['allo_id'] . "</td>\n";
                            echo "  <td align='center'><input type='submit' name='allo_del' value='���' class='fc_red'></td>\n";
                            echo "  <td>" . $res_allo[$a]['s_name'] . "</td>\n";
                            echo "  <td align='right'><input type='text' name='allo_rate' value='" . $res_allo[$a]['allo_rate'] . "' size='5' maxlength='3' class='right'></td>\n";
                            echo "  <td align='right'><input type='text' name='group_id' value='" . $res_allo[$a]['group_id'] . "' size='5' maxlength='4' class='right'></td>\n";
                            echo "  <td align='center'><input type='submit' name='dest_update' value='" . $res_allo[$a]['dest_id'] . "' class='fc_blue'></td>\n";
                            echo "  <input type='hidden' name='dest_id' value='" . $res_allo[$a]['dest_id'] . "'>\n";
                            echo "  <input type='hidden' name='allo_id' value='" . $res_allo[$a]['allo_id'] . "'>\n";
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
