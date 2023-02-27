<?php
//////////////////////////////////////////////////////////////////////////////
// ����������Ͽ                                                           //
// Copyright (C) 2003-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/15 Created   metarialCost_entry.php                              //
// 2003/12/17 ��ץ쥳���ɿ� ������˥쥳�����ɲû�����������å��ɲ�     //
//            �����칩��η�����Ψ�� define ����� �����ѹ������б�         //
//            ��Ω�����Ϥ���Ƥ��ʤ��ȴ�λ�򲡤��Ƥ⥨�顼�ˤ���������    //
// 2003/12/18 ������Ͽ�λ�������(�Ǹ�)����Ͽ�ǡ����򥳥ԡ����뵡ǽ���ɲ�    //
//            �������ʤ����ʾ�οƤ���Ĺ���������Τǥե������߷פ��ѹ�    //
//                  key(plan_no,assy_no,parts_no,pro_no) ��                 //
//                                      (plan_no,parts_no,pro_no,par_parts) //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/05/13 ���ԡ��������줿���Υ��ԡ����Υ����Ƚ��ѹ� regdate ASC        //
//            �ѹ����Ⱥ�������Ǥ�ݻ����롣�ޥ�����̤��Ͽ�λ���alertɽ��   //
// 2004/11/08 ��λ�ܥ���ǥإå�������Ͽ����ʤ��Ȼפ����Զ���к� $uniq  //
// 2005/02/08 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/02/25 ��å�������ݥåץ��åפȥ����ȥ�å����������Ƥˤ�ä�ʬ����//
// 2005/03/02 set_focus()�ǥ������뤬ɽ�����ʤ���������Τ�select();���ɲ�  //
// 2005/05/23 ���ԡ��ܥ���Σ��󲡤��ˤ��duplicate key�����å��Τ���       //
//            if ($rows == 0) �� if ($rows == 0 && $offset == 0) link��ɽ�� //
// 2005/05/27 Query failed: ERROR:  duplicate key �б��Τ��� debugʸ�����  //
// 2005/06/01 Query failed: ERROR:  duplicate key �б��Τ��� debugʸ�����  //
// �嵭��copy�� order by plan_no DESC �� order by assy_no DESC, regdate DESC//
//copy���κǿ��ǡ�����ײ��ֹ�礫����Ͽ����ѹ�(��Ͽ�����֤ˤ���Ƥ��뤿��)//
// 2005/06/02 last_user �Υȥꥬ�������'{$_SESSION['User_ID']}'����Ͽ��  //
// 2005/06/07 ̤��Ͽ�Ȳ񤫤�θƽ��б��Τ���set_retGET('page_keep','On')����//
//            ��� ��� ��ǽ �� �ɲ�                                        //
// 2005/06/14 SQL���顼���Υ�������ˡ�򥷥�����������ե�����ϥ�ɥ��   //
// 2005/06/29 material_cost_history�Υ��ԡ����˴���Ͽ�ѤΥ����å����ɲ�     //
//                     ���ʤ���ν�ǣ��ť���å��ǥ��顼�ˤʤ뤳�Ȥ���ä� //
// 2005/09/09 $menu->out_RetUrl() . $menu->out_retGET()��������ɲ�(��λ��) //
// 2006/02/23 ���ԡ��Υ�󥯤������줿��  &&���ɲ� Undefined index�б�      //
// 2006/02/27 PostgreSQL8.1.3�������Υ����Ƚ礬�Ѥ�ä�����regdate��� ��   //
//            �����ֹ桦�����ֹ����ѹ��ڤ������ɲû��˺Ǹ�Υڡ������ݻ���//
// 2006/02/28 �嵭�Υ����Ȥ򹹤˾Ȳ���̤�Ʊ�������Ȥ��ѹ�                  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 21);                    // site_index=30(������˥塼) site_id=21(����������Ͽ)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� Ͽ (��������)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('���������Ͽ',   INDUST . 'material/materialCost_entry.php');
//////////// ����ؤ�GET�ǡ�������
$menu->set_retGET('page_keep', 'On');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// ���ǤιԿ�
define('PAGE', '10');

//////////// ��å��������ϥե饰
$msg_flg = 'site';

//////////// ���顼���ν�����
$error_log_name = '/tmp/materialCost_entry_error.log';

//////////// �ײ��ֹ桦�����ֹ�򥻥å���󤫤����
if (isset($_REQUEST['plan_no'])) {
    $plan_no = $_REQUEST['plan_no'];
    $_SESSION['material_plan_no'] = $plan_no;
    $_SESSION['plan_no']          = $plan_no;
} elseif (isset($_SESSION['plan_no'])) {
    $plan_no = $_SESSION['plan_no'];
} else {
    $_SESSION['s_sysmsg'] .= '�ײ��ֹ椬���ꤵ��Ƥʤ���';      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}
if (isset($_REQUEST['assy_no'])) {
    $assy_no = $_REQUEST['assy_no'];
    $_SESSION['assy_no'] = $assy_no;
} elseif (isset($_SESSION['assy_no'])) {
    $assy_no = $_SESSION['assy_no'];
} else {
    $_SESSION['s_sysmsg'] .= '�����ֹ椬���ꤵ��Ƥʤ���';      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

//////////// �졼�Ȥ�ײ��ֹ椫�����(�����ޥ����������ѹ�ͽ��)
if (substr($plan_no, 0, 1) == 'C') {
    define('RATE', 25.60);  // ���ץ�
} else {
    define('RATE', 37.00);  // ��˥�(����ʳ��ϸ��ߤʤ�)
}

//////////// ����̾�μ���
$query = "select midsc from miitem where mipn='{$assy_no}'";
if ( getUniResult($query, $assy_name) <= 0) {           // ����̾�μ���
    $_SESSION['s_sysmsg'] .= "����̾�μ����˼���";      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

//////////// ɽ�������
// $menu->set_caption('���ߤη�����Ψ��' . number_format(RATE, 2));
$menu->set_caption("�ײ��ֹ桧{$plan_no}&nbsp;&nbsp;�����ֹ桧{$assy_no}&nbsp;&nbsp;����̾��{$assy_name}");


//////////// ����ǡ����Υ��ԡ��ܥ��󤬲����줿���
if (isset($_GET['pre_copy'])) {
    $query = "select plan_no from material_cost_header where assy_no='{$assy_no}'
                order by assy_no DESC, regdate DESC limit 1
    ";
    $chk_sql = "SELECT plan_no FROM material_cost_history
                WHERE
                    plan_no='{$plan_no}' and assy_no='{$assy_no}'
                LIMIT 1
    ";
    if (getUniResult($query, $pre_plan_no) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$assy_name} �Ϸ��򤬤���ޤ���";    // .= �����
    } elseif (getUniResult($chk_sql, $tmp_plan) > 0) {
        $_SESSION['s_sysmsg'] .= "{$assy_name} �ϴ��˹�������Ͽ����Ƥ��ޤ���";    // .= �����
        $msg_flg = 'alert';
    } else {
        $query = "insert into material_cost_history (
                        plan_no, assy_no, parts_no, pro_no, pro_mark,
                        par_parts, pro_price, pro_num, intext, last_date, last_user)
                  select
                        '{$plan_no}', '{$assy_no}', parts_no, pro_no, pro_mark,
                        par_parts, pro_price, pro_num, intext, CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}'
                  from material_cost_history
                  where plan_no='{$pre_plan_no}' and assy_no='{$assy_no}'
                  ORDER BY par_parts ASC, parts_no ASC, pro_no ASC
        ";
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$assy_name} ��COPY�˼��ԡ� ô���Ԥ�Ϣ���Ʋ�������<br>COPY���ηײ��ֹ桧{$pre_plan_no}";    // .= �����
            $msg_flg = 'alert';
            ///////////////////////////////////// debug ADD 2005/06/01
            $fp_error = fopen($error_log_name, 'a');   // ���顼���ؤν���ߤǥ����ץ�
            $log_msg  = date('Y-m-d H:i:s');
            $log_msg .= " ���顼�λ��� SQL ʸ�ϰʲ� \n";
            fwrite($fp_error, $log_msg);
            fwrite($fp_error, $query);
            fclose($fp_error);
            ///////////////////////////////////// debug END
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$assy_name} ��COPY���ޤ���<br>COPY���ηײ��ֹ桧{$pre_plan_no}</font>";    // .= �����
        }
    }
}

////////////// ��Ͽ���ѹ����å���������
if (isset($_POST['entry'])) {
    $query = "select midsc from miitem where mipn='{$_POST['parts_no']}'";
    if (getResult2($query, $res_chk) <= 0) {
        $_SESSION['s_sysmsg'] .= "�����ֹ桧{$_POST['parts_no']} �ϥޥ�����̤��Ͽ�Ǥ���";    // .= �����
        $msg_flg = 'alert';
        unset($_POST['entry']);
        // $unreg_msg = 1;     // JavaScript��alert �طѤ����᥻�åȤ��� 2005/02/08 alert()����
    }
}

////////////// ��Ͽ���ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
if (isset($_POST['entry'])) {
    $parts_no = $_POST['parts_no'];
    $pro_no   = $_POST['pro_no'];
    $pro_mark = $_POST['pro_mark'];
    $par_parts = $_POST['par_parts'];
    $pro_price = $_POST['pro_price'];
    // if ($pro_price == '') $pro_price = 0;
    $pro_num   = $_POST['pro_num'];
    $intext    = $_POST['intext'];
    $query = sprintf("select parts_no from material_cost_history where plan_no='%s' and parts_no='%s' and pro_no=%d and par_parts='%s'",
                        $plan_no, $parts_no, $pro_no, $par_parts);
    $res_chk = array();
    if ( getResult2($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE �ѹ�
        $query = sprintf("update material_cost_history set plan_no='%s', assy_no='%s', parts_no='%s',
                            pro_no=%d, pro_mark='%s', par_parts='%s', pro_price=%01.2f, pro_num=%01.4f,
                            intext=%01d, last_date=CURRENT_TIMESTAMP, last_user='%s'",
                          $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price, $pro_num,
                          $intext, $_SESSION['User_ID']);
        $query .= sprintf(" where plan_no='%s' and parts_no='%s' and pro_no=%d and par_parts='%s'",
                        $plan_no, $parts_no, $pro_no, $par_parts);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}������{$pro_no}���ѹ��˼��ԡ�";    // .= �����
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}������{$pro_no}���ѹ����ޤ�����</font>";    // .= �����
        }
        unset($_POST['entry']);   // UPDATE�ξ��ϥڡ�����ݻ����뤿�� entry ��������
        $_GET['page_keep'] = '1';   // �ڡ�����ݻ����뤿�� page_keep�����
    } else {                                    //////// ��Ͽ�ʤ� INSERT ����
        $query = sprintf("insert into material_cost_history (plan_no, assy_no, parts_no, pro_no, pro_mark,
                            par_parts, pro_price, pro_num, intext, last_date, last_user)
                          values ('%s', '%s', '%s', %d, '%s', '%s', %01.2f, %01.4f, %01d, CURRENT_TIMESTAMP, '%s')",
                            $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price,
                            $pro_num, $intext, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}������{$pro_no}���ɲä˼��ԡ�";    // .= �����
            ///////////////////////////////////// debug ADD 2005/05/27
            $fp_error = fopen($error_log_name, 'a');   // ���顼���ؤν���ߤǥ����ץ�
            $log_msg  = date('Y-m-d H:i:s');
            $log_msg .= " ���顼�λ��� SQL ʸ�ϰʲ� \n";
            fwrite($fp_error, $log_msg);
            fwrite($fp_error, $query);
            fclose($fp_error);
            ///////////////////////////////////// debug END
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}������{$pro_no}���ɲä��ޤ�����</font>";    // .= �����
        }
        unset($_POST['entry']);   // INSERT�ξ���(2006/02/27)�ڡ�����ݻ����뤿�� entry ��������
        $_GET['page_keep'] = '1';   // �ڡ�����ݻ����뤿�� page_keep�����
    }
}

//////////// ����ܥ��󤬲����줿��
if (isset($_REQUEST['del'])) {
    $parts_no = $_POST['parts_no'];
    $pro_no   = $_POST['pro_no'];
    $par_parts = $_POST['par_parts'];
    $query = "select parts_no, pro_no from material_cost_history ";
    $search_del = " where plan_no='{$plan_no}' and parts_no='{$parts_no}' and pro_no={$pro_no} and par_parts='{$par_parts}'";
    $query .= $search_del;
    $res_chk = array();
    if ( getResult2($query, $res_chk) <= 0 ) {
        $_SESSION['s_sysmsg'] .= "{$parts_no}������ {$pro_no}������Ͽ����Ƥ��ޤ���";    // .= �����
        $msg_flg = 'alert';
    } else {
        $query = "delete from material_cost_history ";
        $query .= $search_del;
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}������ {$pro_no}���κ���˼��ԡ�";    // .= �����
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}������ {$pro_no}���������ޤ�����</font>";
        }
        $_GET['page_keep'] = '1';   // ����ξ��ϥڡ�����ݻ����뤿�� page_keep�����
    }
}


//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf("where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// ��ץ쥳���ɿ����������μ���     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("select count(*), sum(Uround(pro_price * pro_num, 2)) from material_cost_history %s", $search);
$res_sum = array();
if ( getResult2($query, $res_sum) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
}
$maxrows = $res_sum[0][0];
$sum_kin = $res_sum[0][1];

$query = sprintf("select sum(Uround(pro_num * pro_price, 2)) from material_cost_history
                    %s and intext='0'", $search);
if ( getUniResult($query, $ext_kin) <= 0) {  // �����������
    $_SESSION['s_sysmsg'] .= "�����������μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
}
$query = sprintf("select sum(Uround(pro_num * pro_price, 2)) from material_cost_history
                    %s and intext='1'", $search);
if ( getUniResult($query, $int_kin) <= 0) {  // ������������
    $_SESSION['s_sysmsg'] .= "����������μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
}


//////////// ��λ�ܥ��󤬲����줿��
if (isset($_REQUEST['final'])) {
    $query = "select assy_time from material_cost_header where plan_no='{$plan_no}'";
    if ( getResult2($query, $res_chk) > 0 ) {
        ///// ��Ͽ�� UPDATE
        $query = sprintf("update material_cost_header set
                        plan_no='{$plan_no}', assy_no='{$assy_no}',
                        sum_price=%01.2f, ext_price=%01.2f, int_price=%01.2f,
                        last_date=CURRENT_TIMESTAMP, last_user='{$_SESSION['User_ID']}'
                        where plan_no='{$plan_no}'",
                    $sum_kin, $ext_kin, $int_kin
        );
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��ץ��ޥ꡼�ηײ��ֹ桧{$plan_no} ���ѹ��˼��ԡ�";   // .= �����
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>�ײ��ֹ桧{$plan_no} => ����������Ͽ��λ���ޤ�����</font>";
            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . $menu->out_retGET());  // ľ���θƽи��ص���
            exit();
        }
    } else {
        $_SESSION['s_sysmsg'] .= "�ײ��ֹ桧{$plan_no} ����Ω��̤��Ͽ�Ǥ��������Ͽ���Ʋ�������";    // .= �����
        $msg_flg = 'alert';
    }
}

//////////// ������ܥ��󤬲����줿��
if (isset($_REQUEST['all_del'])) {
    while (1) {
        if ( !($con = funcConnect()) ) {
            $_SESSION['s_sysmsg'] .= "�ǡ����١�������³�Ǥ��ޤ��� ô���Ԥ�Ϣ���Ʋ�������";   // .= �����
            $msg_flg = 'alert';
            break;
        }
        query_affected_trans($con, 'begin');    // �ȥ�󥶥�����󥹥�����
        /******** �إå��� header�κ�� *********/
        $query = "DELETE FROM material_cost_header where plan_no='{$plan_no}'";
        if (query_affected_trans($con, $query) < 0) {   // 0������OK�ˤ�������
            query_affected_trans($con, 'rollback');     // ����Хå�
            $_SESSION['s_sysmsg'] .= "�إå����ե�����κ���ǥ��顼��ȯ�����ޤ����� ô���Ԥ�Ϣ���Ʋ�������";   // .= �����
            $msg_flg = 'alert';
            break;
        }
        /******** ���� history�κ�� *********/
        $query = "DELETE FROM material_cost_history WHERE plan_no='{$plan_no}'";
        if ( ($del_rec = query_affected_trans($con, $query)) < 0) {   // 0������OK�ˤ�������
            query_affected_trans($con, 'rollback');     // ����Хå�
            $_SESSION['s_sysmsg'] .= "���٥ե�����κ���ǥ��顼��ȯ�����ޤ����� ô���Ԥ�Ϣ���Ʋ�������";   // .= �����
            $msg_flg = 'alert';
            break;
        }
        query_affected_trans($con, 'commit');     // ���ߥå�
        $_SESSION['s_sysmsg'] .= "{$del_rec}�������ʤ��������ޤ�����";   // .= �����
        $msg_flg = 'alert';
        break;
    }
}

//////////// �ڡ������ե��å�����
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
        $msg_flg = 'alert';
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
        $msg_flg = 'alert';
    }
} elseif ( isset($_GET['page_keep']) || isset($_GET['number']) ) {   // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['offset'];

//////////// �ɲû���������(�Ǹ���Ǥذ�ư������) ��ץ쥳���ɿ� ������ν���
if (isset($_POST['entry'])) {
    while (1) {
        $_SESSION['offset'] += PAGE;
        if ($_SESSION['offset'] >= $maxrows) {
            $_SESSION['offset'] -= PAGE;
            break;
        }
    }
}
$offset = $_SESSION['offset'];

//////////// �ײ��ֹ�ñ�̤ι������٤κ�ɽ
$query = sprintf("
        SELECT
            parts_no    as �����ֹ�,                    -- 0
            midsc       as ����̾,                      -- 1
            pro_num     as ���ѿ�,                      -- 2
            pro_no      as ����,                        -- 3
            pro_mark    as ����̾,                      -- 4
            pro_price   as ����ñ��,                    -- 5
            Uround(pro_num * pro_price, 2)
                        as �������,                    -- 6
            CASE
                WHEN intext = '0' THEN '����'
                WHEN intext = '1' THEN '���'
                ELSE intext
            END         as �⳰��,                      -- 7
            par_parts   as ���ֹ�                       -- 8
        FROM
            material_cost_history
        LEFT OUTER JOIN
             miitem ON parts_no=mipn
        %s 
        ORDER BY par_parts ASC, parts_no ASC, pro_no ASC OFFSET %d LIMIT %d
        
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'><br>����̤��Ͽ�Ǥ���</font>";
    // header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    // exit();
    $num = count($field);       // �ե�����ɿ�����
    $final_flg = 0;             // ��λ�ե饰 0=NG
} else {
    $num = count($field);       // �ե�����ɿ�����
    $final_flg = 1;             // ��λ�ե饰 1=OK
}

////////////// ���ԡ��Υ�󥯤������줿��  &&���ɲ� Undefined index�б�
if (isset($_GET['number']) && isset($res[$_GET['number']][0]) ) {
    $r = $_GET['number'];
    $parts_no  = $res[$r][0];
    $pro_num   = $res[$r][2];
    $pro_no    = $res[$r][3];
    $pro_mark  = $res[$r][4];
    $pro_price = $res[$r][5];
    $par_parts = $res[$r][8];
    if ($res[$r][7] == '����') $intext = '0'; else $intext = '1';
} else {
    $parts_no  = '';
    $pro_num   = '';
    $pro_no    = '';
    $pro_mark  = '';
    $pro_price = '';
    $par_parts = '';
    $intext    = '0';
}

////////////// ��Ω�����Ͽ���ѹ����å� (���å����֤λ���Ϥʤ�)
if (isset($_POST['assy_reg'])) {
    $m_time = $_POST['m_time'];
    $m_rate = $_POST['m_rate'];
    $a_time = $_POST['a_time'];
    $a_rate = $_POST['a_rate'];
    $g_time = $_POST['g_time'];
    $g_rate = $_POST['g_rate'];
    $assy_time = ($m_time + $a_time + $g_time);
    ////////// ��Ͽ�ѤߤΥ����å�
    $query = "select plan_no from material_cost_header where plan_no='{$plan_no}'";
    if ( getResult2($query, $res_chk) > 0 ) {      ///// ��Ͽ�� UPDATE
        $query = sprintf("update material_cost_header set
                            m_time=%01.3f, m_rate=%01.2f,
                            a_time=%01.3f, a_rate=%01.2f,
                            g_time=%01.3f, g_rate=%01.2f,
                            assy_time=%01.3f, assy_rate=%01.2f,
                            last_date=CURRENT_TIMESTAMP, last_user='{$_SESSION['User_ID']}'
                            where plan_no='{$plan_no}'",
                    $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, RATE);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��Ω�񢪷ײ��ֹ桧{$plan_no} ���ѹ��˼��ԡ�";    // .= �����
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>��Ω�񢪷ײ��ֹ桧{$plan_no} ���ѹ����ޤ���</font>";
        }
    } else {                                        ///// ̤��Ͽ INSERT
        $query = sprintf("insert into material_cost_header
                            (plan_no, m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate, last_date, last_user)
                            values ('{$plan_no}', %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}')",
                    $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, RATE);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "�ײ��ֹ桧{$plan_no} ����Ω����ɲä˼��ԡ�";    // .= �����
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>�ײ��ֹ桧{$plan_no} ����Ω����ɲä��ޤ���</font>";
        }
    }
}

/////////// ��Ω��μ���
$query = "select m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate
            from material_cost_header where plan_no='{$plan_no}'";
$res_time = array();
if ( getResult2($query, $res_time) > 0 ) {
    $m_time = $res_time[0][0];
    $m_rate = $res_time[0][1];
    $a_time = $res_time[0][2];
    $a_rate = $res_time[0][3];
    $g_time = $res_time[0][4];
    $g_rate = $res_time[0][5];
    ///// ��� ��Ω��(������)
    $assy_int_price = ( (Uround($m_time * $m_rate, 2)) + 
                        (Uround($a_time * $a_rate, 2)) + 
                        (Uround($g_time * $g_rate, 2)) );
    ///// �����칩�� ������Ψ����Ω��
    $assy_time  = $res_time[0][6];
    $assy_rate  = $res_time[0][7];
    $assy_price = Uround($assy_time * $assy_rate, 2);
} else {
    $m_time = 0;
    $m_rate = 0;
    $a_time = 0;
    $a_rate = 0;
    $g_time = 0;
    $g_rate = 0;
    $assy_int_price = 0;
    $assy_time  = 0;
    $assy_rate  = RATE;
    $assy_price = 0;
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
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

function chk_cost_entry(obj) {
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    obj.par_parts.value = obj.par_parts.value.toUpperCase();
    if (obj.parts_no.value.length != 0) {
        if (obj.parts_no.value.length != 9) {
            alert('�����ֹ�η���ϣ���Ǥ���');
            obj.parts_no.focus();
            obj.parts_no.select();
            return false;
        }
    } else {
        alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    
    if ( !(isDigitDot(obj.pro_num.value)) ) {
        alert('���ѿ��Ͽ����ʳ����Ͻ���ޤ���');
        obj.pro_num.focus();
        obj.pro_num.select();
        return false;
    } else {
        if (obj.pro_num.value <= 0) {
            alert('���ѿ��ϣ�����礭�����������Ϥ��Ʋ�������');
            obj.pro_num.focus();
            obj.pro_num.select();
            return false;
        }
        if (obj.pro_num.value > 999.9999) {
            alert('���ѿ��� 0.0001��999.9999 �ޤǤ����Ϥ��Ʋ�������');
            obj.pro_num.focus();
            obj.pro_num.select();
            return false;
        }
    }
    
    if ( !(isDigit(obj.pro_no.value)) ) {
        alert('�����ֹ�Ͽ����ʳ����Ͻ���ޤ���');
        obj.pro_no.focus();
        obj.pro_no.select();
        return false;
    } else {
        if (obj.pro_no.value <= 0) {
            alert('�����ֹ�ϣ�����Ϥޤ�ޤ���');
            obj.pro_no.focus();
            obj.pro_no.select();
            return false;
        }
    }
    
    obj.pro_mark.value = obj.pro_mark.value.toUpperCase();
    if (obj.pro_mark.value.length != 0) {
        /*****      ///// ��������˿��������뤿�ᥳ����
        if ( !(isABC(obj.pro_mark.value)) ) {
            alert('��������ϥ���ե��٥åȤǤ���');
            obj.pro_mark.focus();
            obj.pro_mark.select();
            return false;
        }
        *****/
    } else {
        alert('�������椬���Ϥ���Ƥ��ޤ���');
        obj.pro_mark.focus();
        obj.pro_mark.select();
        return false;
    }
    
    if (!( (obj.intext.value == '0') || (obj.intext.value == '1') )) {
        alert('����=0 ���=1 �Τɤ��餫�����Ϥ��Ʋ�������');
        obj.intext.focus();
        obj.intext.select();
        return false;
    }
    
    return true;
}

function chk_assy_entry(obj) {
    /* ���Υ����å��Υե饰 */
    var flg = false;
        /* ���Ƥι��ܤο������ϥ����å� */
    if ( !(isDigitDot(obj.m_time.value)) ) {
        alert('���� �����Ͽ����ʳ����Ͻ���ޤ���');
        obj.m_time.focus();
        obj.m_time.select();
        return false;
    }
    if ( !(isDigitDot(obj.m_rate.value)) ) {
        alert('���� ��Ψ�Ͽ����ʳ����Ͻ���ޤ���');
        obj.m_rate.focus();
        obj.m_rate.select();
        return false;
    }
    if ( !(isDigitDot(obj.a_time.value)) ) {
        alert('��ư�� �����Ͽ����ʳ����Ͻ���ޤ���');
        obj.a_time.focus();
        obj.a_time.select();
        return false;
    }
    if ( !(isDigitDot(obj.a_rate.value)) ) {
        alert('��ư�� ��Ψ�Ͽ����ʳ����Ͻ���ޤ���');
        obj.a_rate.focus();
        obj.a_rate.select();
        return false;
    }
    if ( !(isDigitDot(obj.g_time.value)) ) {
        alert('���� �����Ͽ����ʳ����Ͻ���ޤ���');
        obj.g_time.focus();
        obj.g_time.select();
        return false;
    }
    if ( !(isDigitDot(obj.g_rate.value)) ) {
        alert('���� ��Ψ�Ͽ����ʳ����Ͻ���ޤ���');
        obj.g_rate.focus();
        obj.g_rate.select();
        return false;
    }
        /* ���ȤΥڥ������ϥ����å� */
    if (obj.m_time.value > 0) {
        if (obj.m_rate.value > 0) {
            if (obj.m_time.value > 999.999) {
                alert('���� ������ 0.001��999.999 �ޤǤ����Ϥ��Ʋ�������');
                obj.m_time.focus();
                obj.m_time.select();
                return false;
            }
            if (obj.m_rate.value > 999.99) {
                alert('���� ��Ψ�� 0.01��999.99 �ޤǤ����Ϥ��Ʋ�������');
                obj.m_rate.focus();
                obj.m_rate.select();
                return false;
            }
            flg = true;
        } else {
            alert("���� ���������Ϥ���Ƥ���Τ�\n���� ��Ψ�����Ϥ���Ƥ��ޤ���");
            obj.m_rate.focus();
            obj.m_rate.select();
            return false;
        }
    } else {
        if (obj.m_rate.value > 0) {
            alert("���� ��Ψ�����Ϥ���Ƥ���Τ�\n���� ���������Ϥ���Ƥ��ޤ���");
            obj.m_time.focus();
            obj.m_time.select();
            return false;
        }
    }
        /* ��ư���Υڥ������ϥ����å� */
    if (obj.a_time.value > 0) {
        if (obj.a_rate.value > 0) {
            if (obj.a_time.value > 999.999) {
                alert('��ư�� ������ 0.001��999.999 �ޤǤ����Ϥ��Ʋ�������');
                obj.a_time.focus();
                obj.a_time.select();
                return false;
            }
            if (obj.a_rate.value > 999.99) {
                alert('��ư�� ��Ψ�� 0.01��999.99 �ޤǤ����Ϥ��Ʋ�������');
                obj.a_rate.focus();
                obj.a_rate.select();
                return false;
            }
            flg = true;
        } else {
            alert("��ư�� ���������Ϥ���Ƥ���Τ�\n���� ��Ψ�����Ϥ���Ƥ��ޤ���");
            obj.a_rate.focus();
            obj.a_rate.select();
            return false;
        }
    } else {
        if (obj.a_rate.value > 0) {
            alert("��ư�� ��Ψ�����Ϥ���Ƥ���Τ�\n���� ���������Ϥ���Ƥ��ޤ���");
            obj.a_time.focus();
            obj.a_time.select();
            return false;
        }
    }
        /* ����Υڥ������ϥ����å� */
    if (obj.g_time.value > 0) {
        if (obj.g_rate.value > 0) {
            if (obj.g_time.value > 999.999) {
                alert('���� ������ 0.001��999.999 �ޤǤ����Ϥ��Ʋ�������');
                obj.g_time.focus();
                obj.g_time.select();
                return false;
            }
            if (obj.g_rate.value > 999.99) {
                alert('���� ��Ψ�� 0.01��999.99 �ޤǤ����Ϥ��Ʋ�������');
                obj.g_rate.focus();
                obj.g_rate.select();
                return false;
            }
            flg = true;
        } else {
            alert("���� ���������Ϥ���Ƥ���Τ�\n���� ��Ψ�����Ϥ���Ƥ��ޤ���");
            obj.g_rate.focus();
            obj.g_rate.select();
            return false;
        }
    } else {
        if (obj.g_rate.value > 0) {
            alert("���� ��Ψ�����Ϥ���Ƥ���Τ�\n���� ���������Ϥ���Ƥ��ޤ���");
            obj.g_time.focus();
            obj.g_time.select();
            return false;
        }
    }
        /* ���ΤΥե饰�����ϥ����å� */
    if (!flg) {
        alert('���ȡ���ư��������Τɤ줫�����åȰʾ塢���Ϥ��Ʋ�������');
        obj.m_time.focus();
        obj.m_time.select();
        return false;
    } else {
        return true;
    }
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    document.entry_form.parts_no.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    document.entry_form.parts_no.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font:normal     9pt;
    font-family:    monospace;
}
.pt10 {
    font:normal     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.caption_font {
    font-size:      11pt;
    color:          blue;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-wieght:        bold;
    font-family:        monospace;
}
.parts_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     left;
}
.pro_num_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     center;
}
.price_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     right;
}
.entry_font {
    font-size:      11pt;
    font-weight:    normal;
    color:          red;
}
a:hover {
    background-color: gold;
}
a:active {
    background-color: yellow;
}
a {
    font-size:   10pt;
    font-weight: bold;
    color:       blue;
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <div class='pt10' style='color:gray;'>���ߤη�����Ψ��<?= number_format(RATE, 2) ?></div>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?= $menu->out_caption(), "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                        <!--  bgcolor='#ffffc6' �������� -->
                        <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'>
                            <div class='pt10b'>
                            <a href='<?= $menu->out_self() .'?number='. ($r) ?>' target='application' style='text-decoration:none;'>
                                <?= ($r + $offset + 1) ?>
                            </a>
                            </div>
                        </td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 1:
                            echo "<td class='winbox' nowrap width='300 align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case  2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 4), "</div></td>\n";
                            break;
                        case  5:
                        case  6:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        default:
                            if ($res[$r][$i] != '') {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            } else {    // ���ֹ椬�ʤ��������� $i=8
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                            }
                        }
                    }
                    ?>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class='winbox' nowrap colspan='<?= $num+1 ?>' align='right'>
                        <div class='pt10'>
                        ��������<?= number_format($int_kin, 2) ."\n" ?>  
                        ���������<?= number_format($ext_kin, 2) ."\n" ?>  
                        ��׺�����<?= number_format($sum_kin, 2) ."\n" ?>
                        <br>
                        ��׹�����<?= number_format($assy_time, 3) ."\n" ?>
                        ������Ψ��<?= number_format($assy_rate, 2) ."\n" ?>
                        ������Ω��<?= number_format($assy_price, 2) ."\n" ?>
                        ���������<?= number_format($sum_kin + $assy_price, 2) ."\n" ?>
                        <br>
                        (���͡�����μº���Ψ)
                        ��Ω��<?= number_format($assy_int_price, 2) ."\n" ?>
                        ���������<?= number_format($sum_kin + $assy_int_price, 2) ."\n" ?>
                        </div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <br>
        
        <form name='entry_form' method='post' action='<?= $menu->out_self() ?>' onSubmit='return chk_cost_entry(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>�����ֹ�</th>
                    <th class='winbox' nowrap>���ѿ�</th>
                    <th class='winbox' nowrap>�����ֹ�</th>
                    <th class='winbox' nowrap>����̾</th>
                    <th class='winbox' nowrap>����ñ��</th>
                    <th class='winbox' nowrap>0����/1���</th>
                    <th class='winbox' nowrap>�������ֹ�</th>
                </tr>
                <tr>
                    <a name='entry_point'>
                        <td class='winbox' align='center'>
                            <input type='text' class='parts_font' name='parts_no' value='<?= $parts_no ?>' size='9' maxlength='9'>
                        </td>
                    </a>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='pro_num' value='<?= $pro_num ?>' size='7' maxlength='8'></td>
                    <td class='winbox' align='center'><input type='text' class='pro_num_font' name='pro_no' value='<?= $pro_no ?>' size='1' maxlength='1'></td>
                    <td class='winbox' align='center'><input type='text' class='pro_num_font' name='pro_mark' value='<?= $pro_mark ?>' size='2' maxlength='2'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='pro_price' value='<?= $pro_price ?>' size='11' maxlength='11'></td>
                    <td class='winbox' align='center'><input type='text' class='pro_num_font' name='intext' value='<?= $intext ?>' size='1' maxlength='1'></td>
                    <td class='winbox' align='center'><input type='text' class='parts_font' name='par_parts' value='<?= $par_parts ?>' size='9' maxlength='9'></td>
                </tr>
                <tr>
                    <td class='winbox' colspan='7' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='�ɲ��ѹ�'>
                        <input type='submit' class='entry_font' name='del' value='���'>
                        <?php
                        if ($rows == 0 && $offset == 0) {
                            echo "<a href='". $menu->out_self() ."?pre_copy=1' target='application' style='text-decoration:none;'>
                                    ����Υǡ����򥳥ԡ�
                                  </a>";
                        }
                        ?>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        
        <br>
        
        <form name='assy_form' method='post' action='<?= $menu->out_self() ?>' onSubmit='return chk_assy_entry(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>���ȹ���</th>
                    <th class='winbox' nowrap>������Ψ</th>
                    <th class='winbox' nowrap>��ư������</th>
                    <th class='winbox' nowrap>��ư����Ψ</th>
                    <th class='winbox' nowrap>������</th>
                    <th class='winbox' nowrap>������Ψ</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='m_time' value='<?= $m_time ?>' size='6' maxlength='7'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='m_rate' value='<?= $m_rate ?>' size='5' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='a_time' value='<?= $a_time ?>' size='6' maxlength='7'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='a_rate' value='<?= $a_rate ?>' size='5' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='g_time' value='<?= $g_time ?>' size='6' maxlength='7'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='g_rate' value='<?= $g_rate ?>' size='5' maxlength='6'></td>
                </tr>
                <tr>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='assy_reg' value='�ɲ��ѹ�'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        
        <br>
        
        <form name='final_form' method='post' action='<?= $menu->out_self(), "?id={$uniq}" ?>'>
            <?php if ($final_flg == 1) { ?>
            <input type='submit' class='entry_font' name='final' value='��λ'>
            <input type='submit' class='entry_font' name='all_del' value='�����'
                onClick="return confirm('�������¹Ԥ��ޤ���\n\n���ν����ϸ��ˤ��᤻�ޤ���\n\n�¹Ԥ��Ƥ⵹�����Ǥ��礦����')"
            >
            <?php } ?>
        </form>
    </center>
</body>
<?php if ($msg_flg == 'alert') echo $menu->out_alert_java(); ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
