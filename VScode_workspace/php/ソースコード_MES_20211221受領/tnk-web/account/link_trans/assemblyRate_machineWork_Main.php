<?php
//////////////////////////////////////////////////////////////////////////////
// ��������ǡ����Խ� �ᥤ�� assemblyRate_machineWork_Main.php            //
//                             (�� machine_group_work_main.php)             //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/13 Created  assemblyRate_machineWork_Main.php                    //
//            ��ե�������ƽ�����ؿ��� �����Ȥΰ��֤�Ĵ��             //
// 2007/12/14 �ץ����κǸ�˲��Ԥ��ɲ�                                  //
// 2007/12/21 ���롼����η׻��ǡ����ι�����ؿ��� machineWork_groupEntry   //
// 2007/12/29 ���եǡ���������ͤ�����                                      //
// 2011/06/22 format_date�Ϥ�tnk_func�˰�ư�Τ��ᤳ�������               //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
session_start();                                 // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');             // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');             // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');           // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
access_log();                                    // Script Name �ϼ�ư����

main();

function main()
{
    ////////////// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                          // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    ////////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('��������ǡ����Խ�');
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    before_date($request, $result);                     // ��������ɽ���ΰ٤�����׻�
    get_group_master($result, $request);                // ���롼�ץޥ������μ���
    get_machineWork_master($result, $request);          // ��������ǡ����ޥ������μ���
    get_machineWorkBefore_master($result, $request);    // ����ʬ��������ǡ����ޥ������μ���
    
    request_check($request, $result, $menu);            // ������ʬ�������å�
    
    outViewListHTML($request, $menu, $result);          // HTML����
    
    display($menu, $request, $result);                  // ����ɽ��
}

////////////// ����ɽ��
function display($menu, $request, $result)
{       
    ////////// �֥饦�����Υ���å����к���
    $uniq = 'id=' . $menu->set_useNotCache('target');
    
    ////////// ��å��������ϥե饰
    $msg_flg = 'site';

    ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
    
    ////////// HTML Header ����Ϥ��ƥ���å��������
    $menu->out_html_header();
 
    ////////// View�ν���
    require_once ('assemblyRate_machineWork_View.php');

    ob_end_flush(); 
}

////////////// ��������ɽ���ΰ٤�����׻�
function before_date($request, $result)
{
    $wage_ym = $request->get('wage_ym');
    $nen   = substr($wage_ym, 0, 4);
    $tsuki = substr($wage_ym, 4, 2);
    if (1 == $tsuki) {
        $nen   = $nen - 1;
        $tsuki = 12;
    } else {
        $tsuki = $tsuki - 1;
        if ($tsuki < 10) {
            $tsuki = 0 . $tsuki;
        }
    }
    $wage_ym_b = $nen . $tsuki;
    $result->add('wage_ym_b', $wage_ym_b);
}

////////////// ������ʬ����Ԥ�
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = machineWork_copy($request, $result);
    if ($request->get('del') != '') $ok = machineWork_del($request);
    if ($request->get('entry') != '')  $ok = machineWork_entry($request, $result);
    if ($ok) {
        ////// �ǡ����ν����
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('total_date', '');
        $request->add('mac_no', '');
        $request->add('setup_time', '');
        $request->add('operation_time', '');
        $request->add('repairing_expenses', '');
        get_group_master($result, $request);       // ���롼�ץޥ������μ���
        get_machineWork_master($result, $request); // ��������ǡ����ޥ������μ���
        machineWork_groupEntry($request, $result); // ���롼����ν��׷�̤ι���
    }
}

////////////// �ɲá��ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
function machineWork_entry($request, $result)
{
    if (getCheckAuthority(22)) {                             // ǧ�ڥ����å�
        $total_date = $request->get('wage_ym');
        $query = sprintf("SELECT group_machine_rate FROM assembly_machine_group_rate WHERE total_date=%d", $total_date);
        $res_check = array();
        $rows_check = getResult($query, $res_check);
        if ($rows_check <= 0) {                              // ��Ψ����Ͽ�Ѥߤ������å���Ͽ�Ѥߤξ��ϥ���ǡ����ι����Ϥ��ʤ�
            $group_no = $request->get('group_no');
            $mac_no = $request->get('mac_no');
            $setup_time = $request->get('setup_time');
            $operation_time = $request->get('operation_time');
            $repairing_expenses = $request->get('repairing_expenses');
            $query = sprintf("SELECT total_date, mac_no FROM assembly_machine_group_work WHERE total_date=%d AND mac_no=%d", $total_date, $mac_no);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {         // ��Ͽ���� UPDATE ����
                $query = sprintf("UPDATE assembly_machine_group_work SET group_no=%d, total_date=%d, mac_no=%d, setup_time=%d, operation_time=%d, repairing_expenses=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                    WHERE total_date=%d AND mac_no=%d", $group_no, $total_date, $mac_no, $setup_time, $operation_time, $repairing_expenses, $_SESSION['User_ID'], $total_date, $mac_no);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} ����No.��{$mac_no}�Υǡ����ѹ����ԡ�";    // .= �����
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} ����No.��{$mac_no}�Υǡ������ѹ����ޤ�����";    // .= �����
                    return true;
                }
            } else {                                         // ��Ͽ�ʤ� INSERT ����   
                $query = sprintf("INSERT INTO assembly_machine_group_work (group_no, total_date, mac_no, setup_time, operation_time, repairing_expenses, last_date, last_user)
                             VALUES (%d, %d, %d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                             $group_no, $total_date, $mac_no, $setup_time, $operation_time, $repairing_expenses, $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} ����No.��{$mac_no}�Υǡ�����Ͽ�˼��ԡ�";    // .= �����
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} ����No.��{$mac_no}�Υǡ�����Ͽ���ɲä��ޤ�����";    // .= �����
                    return true;
                }
            }
        } else {
            if ($res_check[0]['group_machine_rate'] == '') { // ��Ψ����Ͽ�Ѥߤ������å���Ͽ�Ѥߤξ��ϥ���ǡ����ι����Ϥ��ʤ�
                $group_no = $request->get('group_no');
                $mac_no = $request->get('mac_no');
                $setup_time = $request->get('setup_time');
                $operation_time = $request->get('operation_time');
                $repairing_expenses = $request->get('repairing_expenses');
                $query = sprintf("SELECT total_date, mac_no FROM assembly_machine_group_work WHERE total_date='%d' AND mac_no='%d'", $total_date, $mac_no);
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {     // ��Ͽ���� UPDATE ����
                    $query = sprintf("UPDATE assembly_machine_group_work SET group_no=%d, total_date=%d, mac_no=%d, setup_time=%d, operation_time=%d, repairing_expenses=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                        WHERE total_date=%d AND mac_no=%d", $group_no, $total_date, $mac_no, $setup_time, $operation_time, $repairing_expenses, $_SESSION['User_ID'], $total_date, $mac_no);
                    if (query_affected($query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} ����No.��{$mac_no}�Υǡ����ѹ����ԡ�";    // .= �����
                        $msg_flg = 'alert';
                        return false;
                    } else {
                        $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} ����No.��{$mac_no}�Υǡ������ѹ����ޤ�����";    // .= �����
                        return true;
                    }
                } else {                                     // ��Ͽ�ʤ� INSERT ����   
                    $query = sprintf("INSERT INTO assembly_machine_group_work (group_no, total_date, mac_no, setup_time, operation_time, repairing_expenses, last_date, last_user)
                                 VALUES (%d, %d, %d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                 $group_no, $total_date, $mac_no, $setup_time, $operation_time, $repairing_expenses, $_SESSION['User_ID']);
                    if (query_affected($query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} ����No.��{$mac_no}�Υǡ�����Ͽ�˼��ԡ�";    // .= �����
                        $msg_flg = 'alert';
                        return false;
                    } else {
                        $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} ����No.��{$mac_no}�Υǡ�����Ͽ���ɲä��ޤ�����";    // .= �����
                        return true;
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] .= "������Ψ�����Ǥ˳��ꤵ��Ƥ��ޤ���";
                return false;
            }
        }
    } else {                                                 // ǧ�ڤʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������";
        return false;
    }
}

////////////// ������å� (��ץ쥳���ɿ��������˹Ԥ�)
function machineWork_del($request)
{
    if (getCheckAuthority(22)) {     // ǧ�ڥ����å�
        $total_date = $request->get('wage_ym');
        $query = sprintf("SELECT group_machine_rate FROM assembly_machine_group_rate WHERE total_date=%d", $total_date);
        $res_check = array();
        $rows_check = getResult($query, $res_check);
        if ($rows_check <= 0) {      // ��Ψ����Ͽ�Ѥߤ������å���Ͽ�Ѥߤξ��ϥ���ǡ����ι����Ϥ��ʤ�
            $mac_no = $request->get('mac_no');
            $query = sprintf("DELETE FROM assembly_machine_group_work WHERE total_date=%d AND mac_no=%d", $total_date, $mac_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} ����No.��{$mac_no}�κ���˼��ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} ����No.��{$mac_no}�������ޤ�����";    // .= �����
                return true;
            }
        } else {
            $_SESSION['s_sysmsg'] .= "������Ψ�����Ǥ˳��ꤵ��Ƥ��ޤ���";
            return false;
        }
    } else {                         // ǧ�ڤʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������";
        return false;
    }
}

////////////// ���롼���̤ν��׷�̤���Ͽ
function machineWork_groupEntry($request, $result)
{
    $res = $result->get_array2('res_m');
    $res_g = $result->get_array2('res_g');
    $query = sprintf("SELECT group_machine_rate FROM assembly_machine_group_rate WHERE total_date=%d", $request->get('wage_ym'));
    $res_check = array();
    $rows_check = getResult($query, $res_check);
    if ($rows_check <= 0) {      // ��Ψ����Ͽ�Ѥߤ������å�
        ///////////////////////////// ��Ͽ�ʤ��ξ��Τ߽��׷�̤ι�����Ԥ�
        $group_time = array();    //���롼���̱�ž���֤η׻�
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            $group_time[$i] = 0;
        }
        $group_repair[$i] = array();    //���롼���̽�����η׻�
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            $group_repair[$i] = 0;
        }

        for ($r=0; $r<$result->get('rows_m'); $r++) {    //���ѳۤ򥰥롼���̤˿�ʬ
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                if($res[$r][0] == $res_g[$i][0]) {
                    $group_time[$i]   = $group_time[$i] + $res[$r][3] + $res[$r][4];    //���롼���̱�ž���֤η׻�
                    $group_repair[$i] = $group_repair[$i] + $res[$r][5];    //���롼���̽�����η׻�
                }
            }
        }
        ////////////////////////////////// ���׷�̤ι���
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                $res_g[$i][0], $request->get('wage_ym'));
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE����
                $query = sprintf("UPDATE assembly_machine_group_rate SET group_time=%d, group_repair=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no='%d' AND total_date='%d'", $group_time[$i], $group_repair[$i], $_SESSION['User_ID'], $res_g[$i][0], $request->get('wage_ym'));
                if (query_affected($query) <= 0) {
                }
            } else {                                    //////// ��Ͽ�ʤ� INSERT ����   
                $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_time, group_repair, last_date, last_user)
                                    VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                    $res_g[$i][0], $request->get('wage_ym'), $group_time[$i], $group_repair[$i], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                }
            }
        }
    } else {
        if ($res_check[0]['group_machine_rate'] == '') {      // ��Ψ����Ͽ�Ѥߤ������å�
            ///////////////////////////// ��Ͽ�ʤ��ξ��Τ߽��׷�̤ι�����Ԥ�
            $group_time = array();    //���롼���̱�ž���֤η׻�
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                $group_time[$i] = 0;
            }
            $group_repair[$i] = array();    //���롼���̽�����η׻�
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                $group_repair[$i] = 0;
            }

            for ($r=0; $r<$result->get('rows_m'); $r++) {    //���ѳۤ򥰥롼���̤˿�ʬ
                for ($i=0; $i<$result->get('rows_g'); $i++) {
                    if($res[$r][0] == $res_g[$i][0]) {
                        $group_time[$i]   = $group_time[$i] + $res[$r][3] + $res[$r][4];    //���롼���̱�ž���֤η׻�
                        $group_repair[$i] = $group_repair[$i] + $res[$r][5];    //���롼���̽�����η׻�
                    }
                }
            }
            ////////////////////////////////// ���׷�̤ι���
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                    $res_g[$i][0], $request->get('wage_ym'));
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE����
                    $query = sprintf("UPDATE assembly_machine_group_rate SET group_time=%d, group_repair=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no='%d' AND total_date='%d'", $group_time[$i], $group_repair[$i], $_SESSION['User_ID'], $res_g[$i][0], $request->get('wage_ym'));
                    if (query_affected($query) <= 0) {
                    }
                } else {                                    //////// ��Ͽ�ʤ� INSERT ����   
                    $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_time, group_repair, last_date, last_user)
                                        VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                        $res_g[$i][0], $request->get('wage_ym'), $group_time[$i], $group_repair[$i], $_SESSION['User_ID']);
                    if (query_affected($query) <= 0) {
                    }
                }
            }
        }
    }
}
////////////// ɽ����(����ɽ)�Υ��롼�ץޥ������ǡ�����SQL�Ǽ���
function get_group_master ($result, $request)
{
    $query_g = "
        SELECT  groupm.group_no                AS ���롼���ֹ�     -- 0
            ,   groupm.group_name              AS ���롼��̾       -- 1
        FROM
            assembly_machine_group_master AS groupm
        ORDER BY
            group_no
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "���롼�פ���Ͽ������ޤ���";
        $field[0]   = "���롼���ֹ�";
        $field[1]   = "���롼��̾";
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', '');
        $result->add('num_g', 2);
        $result->add('rows_g', '');
    } else {
        $num_g = count($field_g);
        $result->add_array2('res_g', $res_g);
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', $num_g);
        $result->add('rows_g', $rows_g);
    }
}

////////////// ɽ����(����ɽ)�ε�������ǡ�����SQL�Ǽ���
function get_machineWork_master ($result, $request)
{
    $wage_ym = $request->get('wage_ym');
    $query = "
        SELECT  mwork.group_no              AS ���롼��̾    -- 0
            ,   mwork.total_date            AS ����ǯ��      -- 1
            ,   mwork.mac_no                AS �����ֹ�      -- 2
            ,   mwork.setup_time            AS �ʼ����      -- 3
            ,   mwork.operation_time        AS �ܲ�Ư����    -- 4
            ,   mwork.repairing_expenses    AS ������        -- 5
        FROM
            assembly_machine_group_work AS mwork
        WHERE
            mwork.total_date = $wage_ym
        ORDER BY
            group_no, mac_no
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
    } else {
        $num = count($field);
        $result->add_array2('res_m', $res);
        $result->add_array2('field_m', $field);
        $result->add('num_m', $num);
        $result->add('rows_m', $rows);
        $res_g = $result->get_array2('res_g');
        for ($r=0; $r<$rows; $r++) {    // ���롼���ֹ�ȥ��롼��̾���֤�����(����񻺡�
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                if($res[$r][0] == $res_g[$i][0]) {
                    $group_name[$r] = $res_g[$i][1];
                }
            }
        }
        $result->add_array2('group_name', $group_name);
    }
}

////////////// ɽ����(����ʬ)�ε�������ǡ�����SQL�Ǽ���
function get_machineWorkBefore_master($result, $request)
{
    $wage_ym_b = $result->get('wage_ym_b');
    $query = "
        SELECT  mworkb.group_no              AS ���롼��̾    -- 0
            ,   mworkb.total_date            AS ����ǯ��      -- 1
            ,   mworkb.mac_no                AS �����ֹ�      -- 2
            ,   mworkb.setup_time            AS �ʼ����      -- 3
            ,   mworkb.operation_time        AS �ܲ�Ư����    -- 4
            ,   mworkb.repairing_expenses    AS ������        -- 5
        FROM
            assembly_machine_group_work AS mworkb
        WHERE
            mworkb.total_date = $wage_ym_b
        ORDER BY
            group_no, mac_no
    ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
    } else {
        $num = count($field);
        $result->add_array2('res_b', $res);
        $result->add_array2('field_b', $field);
        $result->add('num_b', $num);
        $result->add('rows_b', $rows);
        $res_g = $result->get_array2('res_g');
        for ($r=0; $r<$rows; $r++) {    // ���롼���ֹ�ȥ��롼��̾���֤�����(����񻺡�
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                if($res[$r][0] == $res_g[$i][0]) {
                    $group_name[$r] = $res_g[$i][1];
                }
            }
        }
        $result->add_array2('group_name_b', $group_name);
    }
}

////////////// ���ԡ��Υ�󥯤������줿��
function machineWork_copy($request, $result)
{
    $r = $request->get('number');
    $res = $result->get_array2('res_m');
    $group_no              = $res[$r][0];
    $total_date            = $res[$r][1];
    $mac_no                = $res[$r][2];
    $setup_time            = $res[$r][3];
    $operation_time        = $res[$r][4];
    $repairing_expenses    = $res[$r][5];
    
    $request->add('group_no', $group_no);
    $request->add('total_date', $total_date);
    $request->add('mac_no', $mac_no);
    $request->add('setup_time', $setup_time);
    $request->add('operation_time', $operation_time);
    $request->add('repairing_expenses', $repairing_expenses);
}

////////////// �����ǡ������̤�HTML�κ���
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<style type='text/css'>\n";
    $listTable .= "<!--\n";
    $listTable .= "th {\n";
    $listTable .= "    background-color:   blue;\n";
    $listTable .= "    color:              yellow;\n";
    $listTable .= "    font-size:          10pt;\n";
    $listTable .= "    font-weight:        bold;\n";
    $listTable .= "    font-family:        monospace;\n";
    $listTable .= "}\n";
    $listTable .= "a:hover {\n";
    $listTable .= "    background-color:   blue;\n";
    $listTable .= "    color:              white;\n";
    $listTable .= "}\n";
    $listTable .= "a:active {\n";
    $listTable .= "    background-color:   gold;\n";
    $listTable .= "    color:              black;\n";
    $listTable .= "}\n";
    $listTable .= "a {\n";
    $listTable .= "    color:   blue;\n";
    $listTable .= "}\n";
    $listTable .= "-->\n";
    $listTable .= "</style>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <form name='entry_form' action='assemblyRate_machineWork_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "               <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $wage_ym = $request->get('wage_ym');
    $listTable .= "                   ". format_date6_kan($wage_ym) ."\n";
    $listTable .= "                   ��������ǡ���\n";
    $listTable .= "                   <font size=2>\n";
    $listTable .= "                   (ñ��:ʬ����)\n";
    $listTable .= "                   </font>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    if ($result->get('num_m') > 0) {
        $field = $result->get_array2('field_m');
        for ($i=0; $i<$result->get('num_m'); $i++) {    // �ե�����ɿ�ʬ���֤�\n";
            if ($i == 1) {
            } else {
                $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
            }
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>���롼��̾</th>\n";
        $listTable .= "            <th class='winbox' nowrap>�����ֹ�</th>\n";
        $listTable .= "            <th class='winbox' nowrap>�ʼ����</th>\n";
        $listTable .= "            <th class='winbox' nowrap>�ܲ�Ư����</th>\n";
        $listTable .= "            <th class='winbox' nowrap>������</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' �������� -->\n";
    $listTable .= "            <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = $result->get_array2('res_m');
    $group_name = $result->get_array2('group_name');
    for ($r=0; $r<$result->get('rows_m'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $listTable .= "                <a href='../assemblyRate_machineWork_Main.php?number=". $r ."&wage_ym=". $request->get('wage_ym') ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>    <!-- �ԥʥ�С���ɽ�� -->\n";
        for ($i=0; $i<$result->get('num_m'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // ���롼��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
                break;
                case 1:                                 // ����ǯ��
                    break;
                case 2:                                 // �����ֹ�
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 3:                                 // �ʼ����
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:                                 // �ܲ�Ư����
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 5:                                 // ������
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                default:
                    break;
            }
        }
        $listTable .= "        </tr>\n";
    }
    $listTable .= "        </TBODY>\n";
    $listTable .= "        </table>\n";
    $listTable .= "            </td></tr>\n";
    $listTable .= "        </table> <!----------------- ���ߡ�End ------------------>\n";
    $listTable .= "    </form>\n";
    $listTable .= "    <form name='entry_form' action='assemblyRate_machineWork_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "               <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $wage_ym_b = $result->get('wage_ym_b');
    $listTable .= "                   ������\n";
    $listTable .= "                   ". format_date6_kan($wage_ym_b) ."\n";
    $listTable .= "                   ��������ǡ���\n";
    $listTable .= "                   <font size=2>\n";
    $listTable .= "                   (ñ��:ʬ����)\n";
    $listTable .= "                   </font>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    if ($result->get('num_b') > 0) {
        $field = $result->get_array2('field_b');
        for ($i=0; $i<$result->get('num_b'); $i++) {    // �ե�����ɿ�ʬ���֤�\n";
            if ($i == 1) {
            } else {
                $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
            }
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>���롼��̾</th>\n";
        $listTable .= "            <th class='winbox' nowrap>�����ֹ�</th>\n";
        $listTable .= "            <th class='winbox' nowrap>�ʼ����</th>\n";
        $listTable .= "            <th class='winbox' nowrap>�ܲ�Ư����</th>\n";
        $listTable .= "            <th class='winbox' nowrap>������</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' �������� -->\n";
    $listTable .= "            <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = $result->get_array2('res_b');
    $group_name = $result->get_array2('group_name_b');
    for ($r=0; $r<$result->get('rows_b'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $cnum = $r + 1;
        $listTable .= "            ". $cnum ."\n";
        $listTable .= "            </td>    <!-- �ԥʥ�С���ɽ�� -->\n";
        for ($i=0; $i<$result->get('num_b'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // ���롼��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
                break;
                case 1:                                 // ����ǯ��
                    break;
                case 2:                                 // �����ֹ�
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 3:                                 // �ʼ����
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:                                 // �ܲ�Ư����
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 5:                                 // ������
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                default:
                    break;
            }
        }
        $listTable .= "        </tr>\n";
    }
    $listTable .= "        </TBODY>\n";
    $listTable .= "        </table>\n";
    $listTable .= "            </td></tr>\n";
    $listTable .= "        </table> <!----------------- ���ߡ�End ------------------>\n";
    $listTable .= "    </form>\n";
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

////////////// ��Ψ�Ȳ���̤�HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '�Ȳ�');
    ////////// HTML�ե��������
    $file_name = "list/assemblyRate_machineWork_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}
