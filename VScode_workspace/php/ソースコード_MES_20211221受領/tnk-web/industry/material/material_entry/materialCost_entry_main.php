<?php
//////////////////////////////////////////////////////////////////////////////
// ����������Ͽ materialCost_entry_main.php                               //
// Copyright (C) 2007-2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2007/05/23 Created   metarialCost_entry_main.php                         //
// 2007/06/21 php���硼�ȥ�����ɸ�ॿ���ء� �ܥǥ������л����%����ؾ���   //
// 2007/06/22 $uniq �� id= �������ߡ�����                                 //
// 2007/09/18 E_ALL | E_STRICT ���ѹ� ����                                  //
// 2007/09/27 �����ZZ�ηײ�������դ����ѹ����å��ɲ� ����             //
// 2007/09/29 �嵭�λ���򥳥��ȥ����Ȥ��Ƹ����᤹�� ����                 //
// 2010/11/12 AS�ؤΥ��åץ��ɥե�����ؤν���ߤ��ɲ�               ��ë //
// 2020/06/01 �ְ������ʹ���ɽ�ξȲ�פ����褿������Ͽ�ǡ������ʤ����      //
//            ������̤���Ͽ���̤إ��ԡ�����Ω���������Ͽ�ǡ�����     ���� //
// 2020/06/11 �������ư��Ͽ������ä�����ɽ����ݻ������ �ɲ�      ���� //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');     // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
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
$menu->set_action('�����������Ͽ',   INDUST . 'material/materialCost_entry_old.php');

$menu->set_frame('��Ͽ�إå���',     INDUST . 'material/material_entry/materialCost_entry_ViewHeader.php');
$menu->set_frame('��Ͽ�ܥǥ�',       INDUST . 'material/material_entry/materialCost_entry_ViewBody.php');
$menu->set_frame('��Ͽ�եå���',     INDUST . 'material/material_entry/materialCost_entry_ViewFooter.php');

//////////// ����ؤ�GET�ǡ�������
$menu->set_retGET('page_keep', 'On');
$menu->set_retGET('material', '1');   // �������ư��Ͽ������ä�����ɽ����ݻ������

$request = new Request;
$session = new Session;
//////////// �֥饦�����Υ���å����к���
$uniq = 'id=' . $menu->set_useNotCache('target');

//////////// ��å��������ϥե饰
$msg_flg = 'site';

//////////// �ײ��ֹ桦�����ֹ�򥻥å���󤫤����
if ($request->get('plan_no') != '') {
    $plan_no = $request->get('plan_no');
    $session->add('material_plan_no', $plan_no);
    $session->add('plan_no', $plan_no);
} elseif ($session->get('plan_no') != '') {
    $plan_no = $session->get('plan_no');
} else {
    $_SESSION['s_sysmsg'] .= '�ײ��ֹ椬���ꤵ��Ƥʤ���';      // .= ��å��������ɲä���
    $msg_flg = 'alert';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}
if ($request->get('assy_no') != '') {
    $assy_no = $request->get('assy_no');
    $session->add('assy_no', $assy_no);
} elseif ($session->get('assy_no') != '') {
    $assy_no = $session->get('assy_no');
} else {
    $_SESSION['s_sysmsg'] .= '�����ֹ椬���ꤵ��Ƥʤ���';      // .= ��å��������ɲä���
    $msg_flg = 'alert';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

/* �ְ������ʹ���ɽ�ξȲ�פ��餿������Ͽ�ǡ������ʤ���С��Ȳ��̤򥳥ԡ����� -> */
if( $request->get('data_copy') != '' ) {   // �������ʹ���ɽ�����褿
    if( isset($_SESSION['entry_data']) ) { // ��Ͽ�Ǥ���Ȳ�ǡ���������ʰ������ʹ���ɽ����Ͽ��
        // ��������Ͽ
        $query = "SELECT parts_no FROM material_cost_history WHERE plan_no='{$plan_no}' AND assy_no='{$assy_no}'";
        if( getResult2($query, $res_chk) <= 0 ) { // �ޤ�����Ͽ����Ƥ��ʤ�
            $res = $_SESSION['entry_data'];
            // ���쥳���ɤ�����Ͽ����١��쥳����ʬ�����֤�
            for( $r=0; !empty($res[$r]); $r++ ) {
                $query = sprintf("INSERT INTO material_cost_history (plan_no, assy_no, parts_no, pro_no, pro_mark, par_parts, pro_price, pro_num, intext, last_date, last_user)
                                  VALUES ('%s', '%s', '%s', %d, '%s', '%s', %01.2f, %01.4f, %01d, CURRENT_TIMESTAMP, '%s')",
                                  $plan_no, $assy_no, $res[$r][0], $res[$r][1], $res[$r][2], $res[$r][3], $res[$r][4], $res[$r][5], $res[$r][6], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "{$res[$r][0]}������{$res[$r][1]}���ɲä˼��ԡ�";    // .= �����
                }
            }

            if( isset($_SESSION['assy_reg_data']) ) { // ��Ͽ�Ǥ�����Ω�񤬤���ʰ������ʹ���ɽ����Ͽ��
                // ��Ω�����Ͽ��������Ͽ���ι�������Ψ����ѡ�
                $query = "SELECT plan_no FROM material_cost_header WHERE plan_no='{$plan_no}'";
                if( getResult2($query, $res_chk) <= 0 ) {
                    $assy_reg_data = $_SESSION['assy_reg_data']; // �������ʹ���ɽ����Ͽ
                    $m_time = $assy_reg_data[0];
                    $m_rate = $assy_reg_data[1];
                    $a_time = $assy_reg_data[2];
                    $a_rate = $assy_reg_data[3];
                    $g_time = $assy_reg_data[4];
                    $g_rate = $assy_reg_data[5];
                    $s_rate = 0; // materialCost_entry_ViewFooter.php�ǡ�RATE���ͤ���Ͽ
                    $assy_time = ($m_time + $a_time + $g_time);

                    $query = sprintf("INSERT INTO material_cost_header
                                    (plan_no, m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate, last_date, last_user)
                                    VALUES ('{$plan_no}', %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}')",
                                    $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, $s_rate);
                    if (query_affected($query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "�ײ��ֹ桧{$plan_no} ����Ω����ɲä˼��ԡ�";    // .= �����
                        $msg_flg = 'alert';
                    } else {
                        $_SESSION['s_sysmsg'] .= "<font color='yellow'>�ײ��ֹ桧{$plan_no} ����Ω����ɲä��ޤ���</font>";
                    }

                }
            }
        }
        unset( $_SESSION['entry_data'] );    // ��Ͽ��ȴ���뤿�����
        unset( $_SESSION['assy_reg_data'] ); // ��Ͽ��ȴ���뤿�����
    }
}
/* <------------------------------------------------------------------------------ */

//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf("where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// ��ץ쥳���ɿ����������μ���     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("SELECT count(*), sum(Uround(pro_price * pro_num, 2)) FROM material_cost_history %s", $search);
$res_sum = array();
if ( getResult2($query, $res_sum) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
}
$maxrows = $res_sum[0][0];
$sum_kin = $res_sum[0][1];

$query = sprintf("SELECT sum(Uround(pro_num * pro_price, 2)) FROM material_cost_history
                    %s and intext='0'", $search);
if ( getUniResult($query, $ext_kin) <= 0) {  // �����������
    $_SESSION['s_sysmsg'] .= "�����������μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
}
$query = sprintf("SELECT sum(Uround(pro_num * pro_price, 2)) FROM material_cost_history
                    %s and intext='1'", $search);
if ( getUniResult($query, $int_kin) <= 0) {  // ������������
    $_SESSION['s_sysmsg'] .= "����������μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
}

////////////// ��Ͽ���ѹ����å���������
if ($request->get('entry') != '') {
    $parts_no = $request->get('parts_no');
    $par_parts = $request->get('par_parts');
    $query = "SELECT midsc FROM miitem WHERE mipn='{$parts_no}'";
    if (getResult2($query, $res_chk) <= 0) {
        $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} �ϥޥ�����̤��Ͽ�Ǥ���";    // .= �����
        $msg_flg = 'alert';
        $request->del('entry');
        // $unreg_msg = 1;     // JavaScript��alert �طѤ����᥻�åȤ��� 2005/02/08 alert()����
    } else {
        if ($request->get('par_parts') != '') {
            $query = "SELECT parts_no FROM material_cost_history WHERE plan_no='{$plan_no}' AND parts_no='{$par_parts}'";
            if (getResult2($query, $res_chk) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "���ֹ桧{$par_parts} �����Ĥ���ޤ��� �����Ͽ���Ʋ�������";    // .= �����
                $msg_flg = 'alert';
                $request->del('entry');
                $request->add('page_keep', 1);
            }
        }
    }
}

////////////// ��Ͽ���ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
if ($request->get('entry') != '') {
    $parts_no = $request->get('parts_no');
    $pro_no   = $request->get('pro_no');
    $pro_mark = $request->get('pro_mark');
    $par_parts = $request->get('par_parts');
    $pro_price = $request->get('pro_price');
    // if ($pro_price == '') $pro_price = 0;
    $pro_num   = $request->get('pro_num');
    $intext    = $request->get('intext');
    $query = sprintf("SELECT parts_no FROM material_cost_history WHERE plan_no='%s' and parts_no='%s' and pro_no=%d and par_parts='%s'",
                        $plan_no, $parts_no, $pro_no, $par_parts);
    $res_chk = array();
    if ( getResult2($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE �ѹ�
        $query = sprintf("UPDATE material_cost_history SET plan_no='%s', assy_no='%s', parts_no='%s',
                            pro_no=%d, pro_mark='%s', par_parts='%s', pro_price=%01.2f, pro_num=%01.4f,
                            intext=%01d, last_date=CURRENT_TIMESTAMP, last_user='%s'",
                          $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price, $pro_num,
                          $intext, $_SESSION['User_ID']);
        $query .= sprintf(" WHERE plan_no='%s' and parts_no='%s' and pro_no=%d and par_parts='%s'",
                        $plan_no, $parts_no, $pro_no, $par_parts);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}������{$pro_no}���ѹ��˼��ԡ�";    // .= �����
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}������{$pro_no}���ѹ����ޤ�����</font>";    // .= �����
        }
        //$request->del('entry');   // UPDATE�ξ��ϥڡ�����ݻ����뤿�� entry ��������
    } else {                                    //////// ��Ͽ�ʤ� INSERT ����
        /*****
        if (substr($plan_no, 0, 2) == 'ZZ') {
            $query = sprintf("INSERT INTO material_cost_history (plan_no, assy_no, parts_no, pro_no, pro_mark,
                            par_parts, pro_price, pro_num, intext, regdate, last_date, last_user)
                          VALUES ('%s', '%s', '%s', %d, '%s', '%s', %01.2f, %01.4f, %01d, '2007-10-06 00:00:00', CURRENT_TIMESTAMP, '%s')",
                            $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price,
                            $pro_num, $intext, $_SESSION['User_ID']);
        } else {
        }
        *****/
        $query = sprintf("INSERT INTO material_cost_history (plan_no, assy_no, parts_no, pro_no, pro_mark,
                        par_parts, pro_price, pro_num, intext, last_date, last_user)
                      VALUES ('%s', '%s', '%s', %d, '%s', '%s', %01.2f, %01.4f, %01d, CURRENT_TIMESTAMP, '%s')",
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
        //$request->del('entry');   // INSERT�ξ���(2006/02/27)�ڡ�����ݻ����뤿�� entry ��������
    }
}

//////////// ����ܥ��󤬲����줿��
if ($request->get('del') != '') {
    $parts_no = $request->get('parts_no');
    $pro_no   = $request->get('pro_no');
    $par_parts = $request->get('par_parts');
    $c_number = $request->get('c_number');
    $query = "SELECT parts_no, pro_no FROM material_cost_history ";
    $search_del = " WHERE plan_no='{$plan_no}' and parts_no='{$parts_no}' and pro_no={$pro_no} and par_parts='{$par_parts}'";
    $query .= $search_del;
    $res_chk = array();
    if ( getResult2($query, $res_chk) <= 0 ) {
        $_SESSION['s_sysmsg'] .= "{$parts_no}������ {$pro_no}������Ͽ����Ƥ��ޤ���";    // .= �����
        $msg_flg = 'alert';
    } else {
        $query = "SELECT parts_no FROM material_cost_history WHERE plan_no='{$plan_no}' AND par_parts='{$parts_no}'";
        if (getResult2($query, $res_chk) > 0 ) {
            $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} �ϴ��˻����ʤ���Ͽ����Ƥ��ޤ��� ��˻����ʤ������Ʋ�������";    // .= �����
            $msg_flg = 'alert';
            $request->del('del');
            $request->add('no_del', 1);   // ����ξ��ϥڡ�����ݻ����뤿�� page_keep�����
            $no_del_num = $c_number;
            $request->add('page_keep', 1);   // ����ξ��ϥڡ�����ݻ����뤿�� page_keep�����
        } else {
            $query = "delete FROM material_cost_history ";
            $query .= $search_del;
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$parts_no}������ {$pro_no}���κ���˼��ԡ�";    // .= �����
                $msg_flg = 'alert';
            } else {
                $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}������ {$pro_no}���������ޤ�����</font>";
            }
            $request->add('page_keep', 1);   // ����ξ��ϥڡ�����ݻ����뤿�� page_keep�����
        }
    }
}

//////////// ��λ�ܥ��󤬲����줿��
if ($request->get('final') != '') {
    $query = "SELECT assy_time FROM material_cost_header WHERE plan_no='{$plan_no}'";
    if ( getResult2($query, $res_chk) > 0 ) {
        ///// ��Ͽ�� UPDATE
        $query = sprintf("UPDATE material_cost_header SET
                        plan_no='{$plan_no}', assy_no='{$assy_no}',
                        sum_price=%01.2f, ext_price=%01.2f, int_price=%01.2f,
                        last_date=CURRENT_TIMESTAMP, last_user='{$_SESSION['User_ID']}'
                        WHERE plan_no='{$plan_no}'",
                    $sum_kin, $ext_kin, $int_kin
        );
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��ץ��ޥ꡼�ηײ��ֹ桧{$plan_no} ���ѹ��˼��ԡ�";   // .= �����
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>�ײ��ֹ桧{$plan_no} => ����������Ͽ��λ���ޤ�����</font>";
            // AS400��Ͽ�ѥ��ޥ꡼��Ͽ
            $sql2 = "
                SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
            ";
            $kan = '';
            getUniResult($sql2, $kan);
            if ($kan != '') {
                $hg_date = substr($kan, 2, 4);                  // ����ǯ��ʸ������ա�
                $hg_ym   = substr($kan, 0, 2);                  // ����ǯ��YY
            } else {
                $hg_date = '';
                $hg_ym   = '';
            }
            $today      = date('Ymd');
            $entry_date = substr($today, 2, 6);                 // ��Ͽ��
            $entry_year = substr($today, 0, 2);                 // ��ϿǯYY
            $sum_price  = $sum_kin;                             // ���������
            $query = "SELECT m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate
                        FROM material_cost_header WHERE plan_no='{$plan_no}'";
            $res_time = array();
            if ( getResult2($query, $res_time) > 0 ) {
                $m_time     = $res_time[0][0];
                $m_rate     = $res_time[0][1];
                $a_time     = $res_time[0][2];
                $a_rate     = $res_time[0][3];
                $g_time     = $res_time[0][4];    
                $g_rate     = $res_time[0][5];
                $assy_rate  = $res_time[0][7];
            } else {
                $m_time     = 0;
                $m_rate     = 0;
                $a_time     = 0;
                $a_rate     = 0;
                $g_time     = 0;
                $g_rate     = 0;
                $assy_rate  = 0;
            }
            $m_price    = Uround($m_time * $assy_rate, 2);      // ������Ω��
            $a_price    = Uround($a_time * $a_rate, 2);         // ��ư����Ω��
            $g_price    = Uround($g_time * $assy_rate, 2);      // ������Ω��
            $m_place    = '01111';                              // ��Ω����01111�����
            $hgkkk      = 'W';                                  // �軻��ʬ��W���� Web�ΰա�
            $query = sprintf("SELECT plan_no FROM material_cost_summary WHERE plan_no='%s' and assy_no='%s'",
                        $plan_no, $assy_no);
            $res_chk = array(); 
            if ( getResult2($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE �ѹ�
                $query = sprintf("UPDATE material_cost_summary SET assy_no='%s', plan_no='%s', sum_price=%01.2f, m_time=%01.3f,
                                a_time=%01.3f, g_time=%01.3f, m_price=%01.2f, a_price=%01.2f, g_price=%01.2f,
                                m_place='%s', hgkkk='%s', hg_date='%s', entry_date='%s', hg_ym=%d, entry_year=%d",
                                $assy_no, $plan_no, $sum_price, $m_time, $a_time, $g_time, $m_price, $a_price,
                                $g_price, $m_place, $hgkkk, $hg_date, $entry_date, $hg_ym, $entry_year);
                $query .= sprintf(" WHERE plan_no='%s' and assy_no='%s'",
                                    $plan_no, $assy_no);
                if (query_affected($query) <= 0) {
                    //$_SESSION['s_sysmsg'] .= "{$parts_no}������{$pro_no}���ѹ��˼��ԡ�";    // .= �����
                    //$msg_flg = 'alert';
                } else {
                    //$_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}������{$pro_no}���ѹ����ޤ�����</font>";    // .= �����
                }
                //$request->del('entry');   // UPDATE�ξ��ϥڡ�����ݻ����뤿�� entry ��������
            } else {                                    //////// ��Ͽ�ʤ� INSERT ����
                /*****
                if (substr($plan_no, 0, 2) == 'ZZ') {
                    $query = sprintf("INSERT INTO material_cost_history (plan_no, assy_no, parts_no, pro_no, pro_mark,
                                    par_parts, pro_price, pro_num, intext, regdate, last_date, last_user)
                                  VALUES ('%s', '%s', '%s', %d, '%s', '%s', %01.2f, %01.4f, %01d, '2007-10-06 00:00:00', CURRENT_TIMESTAMP, '%s')",
                                    $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price,
                                    $pro_num, $intext, $_SESSION['User_ID']);
                } else {
                }
                *****/
                $query = sprintf("INSERT INTO material_cost_summary (assy_no, plan_no, sum_price, m_time, a_time, g_time,
                                m_price, a_price, g_price, m_place, hgkkk, hg_date, entry_date, hg_ym, entry_year)
                            VALUES ('%s', '%s', %01.2f, %01.3f, %01.3f, %01.3f, %01.2f, %01.2f, %01.2f, '%s', '%s', '%s', '%s', %d, %d)",
                                $assy_no, $plan_no, $sum_price, $m_time, $a_time, $g_time, $m_price, $a_price,
                                $g_price, $m_place, $hgkkk, $hg_date, $entry_date, $hg_ym, $entry_year);
                if (query_affected($query) <= 0) {
                    //$_SESSION['s_sysmsg'] .= "{$parts_no}������{$pro_no}���ɲä˼��ԡ�";    // .= �����
                    ///////////////////////////////////// debug ADD 2005/05/27
                    //$fp_error = fopen($error_log_name, 'a');   // ���顼���ؤν���ߤǥ����ץ�
                    //$log_msg  = date('Y-m-d H:i:s');
                    //$log_msg .= " ���顼�λ��� SQL ʸ�ϰʲ� \n";
                    //fwrite($fp_error, $log_msg);
                    //fwrite($fp_error, $query);
                    //fclose($fp_error);
                    ///////////////////////////////////// debug END
                    //$msg_flg = 'alert';
                } else {
                    //$_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}������{$pro_no}���ɲä��ޤ�����</font>";    // .= �����
                }
                    //$request->del('entry');   // INSERT�ξ���(2006/02/27)�ڡ�����ݻ����뤿�� entry ��������
            }
            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . $menu->out_retGET());  // ľ���θƽи��ص���
            exit();
        }
    } else {
        $_SESSION['s_sysmsg'] .= "�ײ��ֹ桧{$plan_no} ����Ω��̤��Ͽ�Ǥ��������Ͽ���Ʋ�������";    // .= �����
        $msg_flg = 'alert';
    }
}

//////////// ������ܥ��󤬲����줿��
if ($request->get('all_del') != '') {
    while (1) {
        if ( !($con = funcConnect()) ) {
            $_SESSION['s_sysmsg'] .= "�ǡ����١�������³�Ǥ��ޤ��� ô���Ԥ�Ϣ���Ʋ�������";   // .= �����
            $msg_flg = 'alert';
            break;
        }
        query_affected_trans($con, 'begin');    // �ȥ�󥶥�����󥹥�����
        /******** �إå��� header�κ�� *********/
        $query = "DELETE FROM material_cost_header WHERE plan_no='{$plan_no}'";
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

////////////// ��Ω�����Ͽ���ѹ����å� (���å����֤λ���Ϥʤ�)
if (isset($_POST['assy_reg'])) {
    $m_time = $request->get('m_time');
    $m_rate = $request->get('m_rate');
    $a_time = $request->get('a_time');
    $a_rate = $request->get('a_rate');
    $g_time = $request->get('g_time');
    $g_rate = $request->get('g_rate');
    $s_rate = $request->get('s_rate');
    $assy_time = ($m_time + $a_time + $g_time);
    ////////// ��Ͽ�ѤߤΥ����å�
    $query = "SELECT plan_no FROM material_cost_header WHERE plan_no='{$plan_no}'";
    if ( getResult2($query, $res_chk) > 0 ) {      ///// ��Ͽ�� UPDATE
        $query = sprintf("UPDATE material_cost_header SET
                            m_time=%01.3f, m_rate=%01.2f,
                            a_time=%01.3f, a_rate=%01.2f,
                            g_time=%01.3f, g_rate=%01.2f,
                            assy_time=%01.3f, assy_rate=%01.2f,
                            last_date=CURRENT_TIMESTAMP, last_user='{$_SESSION['User_ID']}'
                            WHERE plan_no='{$plan_no}'",
                    $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, $s_rate);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��Ω�񢪷ײ��ֹ桧{$plan_no} ���ѹ��˼��ԡ�";    // .= �����
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>��Ω�񢪷ײ��ֹ桧{$plan_no} ���ѹ����ޤ���</font>";
        }
    } else {                                        ///// ̤��Ͽ INSERT
        /*****
        if (substr($plan_no, 0, 2) == 'ZZ') {
            $query = sprintf("INSERT INTO material_cost_header
                            (plan_no, m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate, regdate, last_date, last_user)
                            VALUES ('{$plan_no}', %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, '2007-10-06 00:00:00', CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}')",
                    $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, $s_rate);
        } else {
        }
        *****/
        $query = sprintf("INSERT INTO material_cost_header
                        (plan_no, m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate, last_date, last_user)
                        VALUES ('{$plan_no}', %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}')",
                $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, $s_rate);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "�ײ��ֹ桧{$plan_no} ����Ω����ɲä˼��ԡ�";    // .= �����
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>�ײ��ֹ桧{$plan_no} ����Ω����ɲä��ޤ���</font>";
        }
    }
}
/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css()?>
</head>
<body style='overflow-y:hidden;'>
<?php
if ($msg_flg == 'alert') {
    echo "<iframe hspace='0' tabindex='21' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewHeader.php?msg_flg=1&{$uniq}' name='header' align='center' width='100%' height='114' title='����'>\n";
} else {
    echo "<iframe hspace='0' tabindex='21' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewHeader.php?{$uniq}' name='header' align='center' width='100%' height='114' title='����'>\n";
}
echo "    ���ܤ�ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
if ($request->get('entry') != '') {    //��Ͽ���ѹ��ξ��ޡ������Ѥ������ֹ�ȹ����ֹ�ȿ����ʤ�
    echo "<iframe hspace='0' tabindex='19' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewBody.php?mark=1", $_SERVER['QUERY_STRING'], "&parts_no=", $parts_no ,"&pro_mark=", $pro_mark ,"&par_parts=", $par_parts ,"&par_parts=", $par_parts , "&{$uniq}#mark' name='list' align='center' width='100%' height='40%' title='����'>\n";
} else if ($request->get('del') != '') {    //����ξ��ޡ������Ѥ˺������No��
    echo "<iframe hspace='0' tabindex='19' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewBody.php?c_mark=1", $_SERVER['QUERY_STRING'], "&c_number=", $c_number, "&{$uniq}&{$msg_flg}#mark' name='list' align='center' width='100%' height='40%' title='����'>\n";
} else if ($request->get('no_del') != '') {
    echo "<iframe hspace='0' tabindex='19' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewBody.php?no_del_mark=1", $_SERVER['QUERY_STRING'], "&no_del_num=", $no_del_num, "&{$uniq}&{$msg_flg}#mark' name='list' align='center' width='100%' height='40%' title='����'>\n";
} else {
    echo "<iframe hspace='0' tabindex='19' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewBody.php?", $_SERVER['QUERY_STRING'], "&{$uniq}&{$msg_flg}#mark' name='list' align='center' width='100%' height='40%' title='����'>\n";
}
echo "    ������ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
echo "<iframe hspace='0' tabindex='20' vspace='0' frameborder='0' scrolling='yes' src='". $menu->out_frame('��Ͽ�եå���') ."?{$uniq}' name='footer' align='center' width='100%' height='43%' title='�եå���'>\n";
echo "    �եå�����ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
?>
        
</center>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END    
?>