<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω��Ψ �Ȳ���� main�� assemblyRate_reference_Main.php(��wage_rate.php)//
// Copyright (C) 2007-2020 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/11/14 Created  assemblyRate_reference_Main.php                      //
// 2007/12/11 ;ʬ��<font>�����κ���������Ȥ��ɲá������Ȱ��֤�Ĵ��    //
//            ���롼�ץޥ����������ؿ�����ѿ���_g����                    //
//            ��ȼԤ�ɸ����Ψ����Ͽ���å����sql��ʬ���̴ؿ���ʬ��       //
// 2007/12/12 ��Ͽ�Ѥߥǡ����μ������������Ψ�Ƽ�ǡ�����������¤����    //
//            �׻���ؿ��Ȥ���ʬ��                                          //
// 2007/12/29 ���դν���ͤ�������ɲ�                                      //
//            �����̤������軻�������о�ǯ������̤����򤷤����դ��֤�  //
//            �褦���ѹ�                                                    //
// 2008/01/10 ������Ψɽ���ȼ�����Ψɽ����ʸ����������                    //
//            css(machine_rate/labor_rate)��11����12���ѹ�                  //
//            �����ȥ�����machine_rate_title/labor_ratetitle���ѹ���        //
//            ʸ����������11�Τޤ�                                          //
// 2009/04/10 ��������˥����������559�ˤ��ɲ�                             //
// 2010/02/04 ��¤���������ޤʤ��ȷ軻����������ʤ��褦���ѹ�          //
// 2010/03/03 ��ǯ���ɽ����Ĵ����substr�θ��+1-1���ƿ����ˤ���0��ä�     //
// 2010/12/09 ��̳Ĵ����Ŧ�ˤ���˥�����(559)���� 2010/12��             //
// 2011/06/22 format_date�Ϥ�tnk_func�˰�ư�Τ��ᤳ�������               //
// 2012/01/10 ��Ͽ�Ѥߥǡ����Υ����å���ˡ�γ�ǧ                            //
// 2013/09/05 571,510��¸�ߤ��ʤ�����ʤΤǽ���                             //
// 2015/11/05 ��ư����Ψ��4�����number_format�ǥ���ޤ����äƤ��ޤ��Τ�    //
//            round�ؿ����ѹ�                                               //
// 2020/07/02 ���������֤�ʬ����ˤ����ΤǺ��                            //
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
    ////////// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                      // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
       
    ////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('��Ω��Ψ�ξȲ�');
    
    $request = new Request;
    $result  = new Result;
    
    if ($request->get('end_ym') !== '') {
        ////// �꥿���󥢥ɥ쥹����
        $menu->set_RetUrl($_SESSION['wage_referer'] . '?wage_ym=' . $request->get('end_ym'));
    } else {
        ////// �꥿���󥢥ɥ쥹����
        $menu->set_RetUrl($_SESSION['wage_referer'] . '?wage_ym=' . $request->get('wage_ym'));
    }
    
    get_group_master($result, $request);            // �Ƽ�ǡ����μ���
    
    request_check($request, $result, $menu);        // ������ʬ�������å�
    
    calculation_branch($request, $result, $menu);   // ��Ψ�׻���ʬ��
    
    display($menu, $request, $result);              // ����ɽ��
}

////////////// ����ɽ��
function display($menu, $request, $result)
{       
    /////////// �֥饦�����Υ���å����к���
    $uniq = 'id=' . $menu->set_useNotCache('target');
    
    /////////// ��å��������ϥե饰
    $msg_flg = 'site';

    ob_start('ob_gzhandler');                       // ���ϥХåե���gzip����
    
    ////////// HTML Header ����Ϥ��ƥ���å��������
    $menu->out_html_header();
 
    ////////// View�ν���
    require_once ('assemblyRate_reference_View.php');

    ob_end_flush(); 
}

////////////// ɽ����(����ɽ)�Υ��롼�ץޥ������ǡ�����SQL�Ǽ���
function get_group_master ($result, $request)
{
    $query = "
        SELECT  groupm.group_no                AS ���롼���ֹ�     -- 0
            ,   groupm.group_name              AS ���롼��̾       -- 1
        FROM
            assembly_machine_group_master AS groupm
        ORDER BY
            group_no
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "���롼�פ���Ͽ������ޤ���";
        $result->add_array2('res_g', '');
        $result->add('num_g', '');
        $result->add('rows_g', '');
    } else {
        $num = count($field);
        $result->add_array2('res_g', $res);
        $result->add('num_g', $num);
        $result->add('rows_g', $rows);
    }
}

////////////// ɽ����(����ɽ)�Υ��롼�ץޥ������ǡ�����SQL�Ǽ���
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('delete') != '') $ok = wageRate_delete($request);
    if ($request->get('entry') != '')  $ok = wageRate_workerEntry($request, $result);
    if ($request->get('input') != '')  $ok = wageRate_input($request, $result);
    if ($ok) {
        ////// �ǡ����ν����
        $request->add('delete', '');     // �����
        $request->add('entry', '');      // �����
        $request->add('input', '');      // �����
        if ($request->get('wage_ym') !== '') {
            $request->add('end_ym', $request->get('wage_ym'));    // ����ͤν�λǯ�������
            $nen   = substr($request->get('wage_ym'), 0, 4);
            $tsuki = substr($request->get('wage_ym'), 4, 2);
            if (($tsuki < 10) && (3 < $tsuki)) {                  // ����ͤγ���ǯ�������
                $str_tsuki = '04';
                $str_ym = $nen . $str_tsuki;
                $request->add('str_ym', $str_ym);
            } else if ( 9 < $tsuki) {
                $str_tsuki = '10';
                $str_ym = $nen . $str_tsuki;
                $request->add('str_ym', $str_ym);
            } else {
                $str_nen = $nen - 1;
                $str_tsuki = '10';
                $str_ym = $str_nen . $str_tsuki;
                $request->add('str_ym', $str_ym);
            }
            $request->add('wage_ym', '');                         // ����ͤ����եǡ����ν����
        }
    }
}

////////////// ��Ψ�׻���ʬ��
function calculation_branch($request, $result, $menu)
{
    $request->add('view_flg', '');                               // �Ȳ����ɽ���Υե饰�����
    if ($request->get('tangetu') != '') {                        // ���դΥǡ�������
        $request->add('rate_register', '��Ͽ');                  // ñ��ξ������׻���Ԥ���
        $request->add('kessan', '');
    }
    if ($request->get('kessan') != '') {
        $request->add('tangetu', '');
    }
    if ($request->get('kessan') != '' || $request->get('tangetu') != '') {
        if (!registered_data_check($request, $result)) {
            if ($request->get('data_check') == 4) {
                if ($request->get('tangetu') != '') {
                    $_SESSION['s_sysmsg'] .= "�оݴ��֤ǥǡ�������Ͽ����Ƥ��ʤ������ޤ���ô���Ԥ˳�ǧ���Ƥ���������";    // .= �����
                    $msg_flg = 'alert';
                    return;
                } elseif ($request->get('kessan') != '') {
                    if (getCheckAuthority(22)) {
                        before_date ($request);                  // ��������ɽ���ΰ٤�����׻�
                        get_before_figure ($request, $result);
                        outInputHTML($request, $menu, $result);  // ��ȼԿ���ɸ����Ψ�����ϲ��̤����
                        return;
                    } else {
                        $_SESSION['s_sysmsg'] .= "�оݷ�Υǡ�������Ͽ����Ƥ��ޤ���ô���Ԥ˳�ǧ���Ƥ���������";    // .= �����
                        $msg_flg = 'alert';
                        return;
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] .= "�оݷ�Υǡ�������Ͽ����Ƥ��ޤ���ô���Ԥ˳�ǧ���Ƥ���������";    // .= �����
                $msg_flg = 'alert';
                return;
            }
        } else {
            before_date ($request);                              // ��������ɽ���ΰ٤�����׻�
            if(!get_registered_data($request, $result)) {        // ��Ͽ�Ѥߥǡ����μ���
                assembly_rate_cal ($request, $result, $menu);    // ��Ψ�׻��ؿ��θƽ�
            }
            outViewListHTML($request, $menu, $result);           // ��Ψ�Ȳ���̤�HTML�����
        }
    }
}
////////////// ���������Υǡ����κ�����å�
function wageRate_delete ($request)
{
    $end_ym = $request->get('end_ym');
    $format_ym = '';
    $format_ym = format_date6_kan($end_ym);
    $query = sprintf("DELETE FROM assembly_machine_group_rate WHERE total_date=%d", $end_ym);
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$format_ym}�γ������˼��ԡ�";                            // .= �����
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "{$format_ym}�γ���������ޤ�����";                        // .= �����
        $query = sprintf("DELETE FROM worker_figure_master WHERE total_date=%d", $end_ym);    // ��ȼԤ���Ͽ����
        query_affected($query);
    }
    return true;
}

////////////// ��ȼԤ�ɸ����Ψ����Ͽ���å�
function wageRate_workerEntry($request, $result)
{
    $format_ym = '';
    $format_ym = format_date6_kan($request->get('end_ym'));
    if (wageRate_workerCheck($request)) {
        if (!wageRate_stRateEntryBody($request, $result)) {
            return false;
        }
        if (!wageRate_workerEntryBody($request, $result)) {
            return false;
        }
        $_SESSION['s_sysmsg'] .= "{$format_ym}�κ�ȼԿ���ɸ����Ψ���ɲä��ޤ�����";    // .= �����
        return true;
    } else {
        return false;
    }
}

////////////// ��ȼԤ�ɸ����Ψ ɸ����Ψ��Ͽ ���� ���å�
function wageRate_stRateEntryBody($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $standard_rate = $request->get('standard_rate');
    for ($i=0; $i<$request->get('rows_g'); $i++) {
        $query = sprintf("SELECT standard_rate FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                            $res_g[$i][0], $request->get('end_ym'));
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE����
            $query = sprintf("UPDATE assembly_machine_group_rate SET standard_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $standard_rate[$i], $_SESSION['User_ID'], $res_g[$i][0], $request->get('end_ym'));                
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}�����롼��{$res_g[$i][0]}�κ�ȼԿ���ɸ����Ψ����Ͽ���ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            }
        } else {                                    // ��Ͽ�ʤ� INSERT ����   
            $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, standard_rate, last_date, last_user)
                                VALUES (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')",
                                $res_g[$i][0], $request->get('end_ym'), $standard_rate[$i], $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}�����롼��{$res_g[$i][0]}�κ�ȼԿ���ɸ����Ψ����Ͽ���ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            }
        }
    }
    return true;
}

////////////// ��ȼԤ�ɸ����Ψ ��ȼ���Ͽ ���� ���å�
function wageRate_workerEntryBody($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $worker_figure_s = $request->get('worker_figure_s');
    $worker_figure_p = $request->get('worker_figure_p');
    for ($i=0; $i<$request->get('rows_g'); $i++) {
        $query = sprintf("SELECT worker_figure FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=1",
                            $res_g[$i][0], $request->get('end_ym'));
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE����
            $query = sprintf("UPDATE worker_figure_master SET worker_type=1, worker_figure='%s', worker_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $worker_figure_s[$i], $request->get('worker_rate_s'), $_SESSION['User_ID'], $res_g[$i][0], $request->get('end_ym'));
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}�����롼��{$res_g[$i][0]}�κ�ȼԿ���ɸ����Ψ����Ͽ���ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            }
        } else {                                    // ��Ͽ�ʤ� INSERT ����   
            $query = sprintf("INSERT INTO worker_figure_master (group_no, total_date, worker_type, worker_figure, worker_rate, last_date, last_user)
                                VALUES (%d, %d, %d, '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $res_g[$i][0], $request->get('end_ym'), '1', $worker_figure_s[$i], $request->get('worker_rate_s'), $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}�����롼��{$res_g[$i][0]}�κ�ȼԿ���ɸ����Ψ����Ͽ���ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            }
        }
        $query = sprintf("SELECT worker_figure FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=2",
                            $res_g[$i][0], $request->get('end_ym'));
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE����
            $query = sprintf("UPDATE worker_figure_master SET worker_type=2, worker_figure='%s', worker_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $worker_figure_p[$i], $request->get('worker_rate_p'), $_SESSION['User_ID'], $res_g[$i][0], $request->get('end_ym'));
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}�����롼��{$res_g[$i][0]}�κ�ȼԿ���ɸ����Ψ����Ͽ���ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            }
        } else {                                    // ��Ͽ�ʤ� INSERT ����   
            $query = sprintf("INSERT INTO worker_figure_master (group_no, total_date, worker_type, worker_figure, worker_rate, last_date, last_user)
                                VALUES (%d, %d, %d, '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $res_g[$i][0], $request->get('end_ym'), '2', $worker_figure_p[$i], $request->get('worker_rate_p'), $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}�����롼��{$res_g[$i][0]}�κ�ȼԿ���ɸ����Ψ����Ͽ���ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            }
        }
    }
    return true;
}

////////////// ��Ω��Ψ��Ͽ���å� �׻������ǡ�����DB�ع��� ��Ͽ�Ϸ軻��������Ͽ�ܥ���򲡤����Ȥ��Τ�
function wageRate_input($request, $result)
{
    if (getCheckAuthority(22)) {                                  //ǧ�ڥ����å�
        $format_ym = '';
        $format_ym = format_date6_kan($request->get('end_ym'));
        if (!wageRate_inputAll($request)) {                       // ���μ�����Ψ��Ͽ
            return false;
        }
        if (!wageRate_inputCupla($request)) {                     // ���ץ������Ψ��Ͽ
            return false;
        }
        if (!wageRate_inputLinear($request)) {                    // ��˥�������Ψ��Ͽ
            return false;
        }
        if (!wageRate_inputMachine($request)) {                   // ��Ω��ư����Ψ��Ͽ
            return false;
        }
        $_SESSION['s_sysmsg'] .= "{$format_ym}����Ψ����Ͽ���ޤ�����";
        return true;
    } else {                                                      // ǧ�ڤʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�̵���١�DB�ι���������ޤ���Ǥ�����";
        return false;
    }
}

////////////// ��Ω��Ψ��Ͽ���� ���μ�����Ψ
function wageRate_inputAll($request)
{
    $labor_rate = number_format($request->get('labor_rate'), 2);
    $query = sprintf("SELECT * FROM assembly_man_labor_rate WHERE item='����' AND total_date=%d", $request->get('end_ym'));
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {                  // ��Ͽ���� UPDATE����
        $query = sprintf("UPDATE assembly_man_labor_rate SET cut_expense=%d, expense=%d, assistance_time=%d, worker_time=%d, labor_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='����' AND total_date='%d'", $request->get('total_cut_expense'), $request->get('total_expense'), $request->get('total_assistance_time'), $request->get('total_worker_time'), $labor_rate, $_SESSION['User_ID'], $request->get('end_ym'));
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��Ψ����Ͽ���ԡ�";      // .= �����
            $msg_flg = 'alert';
            return false;
        }
    } else {                                                  // ��Ͽ�ʤ� INSERT ����   
        $query = sprintf("INSERT INTO assembly_man_labor_rate (cut_expense, expense, assistance_time, worker_time, item, total_date, labor_rate, last_date, last_user)
                          VALUES (%d, %d, %d, %d, '����', %d, '%s',CURRENT_TIMESTAMP, '%s')",
                            $request->get('total_cut_expense'), $request->get('total_expense'), $request->get('total_assistance_time'), $request->get('total_worker_time'), $request->get('end_ym'), $labor_rate, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��Ψ����Ͽ���ԡ�";      // .= �����
            $msg_flg = 'alert';
            return false;
        }
    }
    return true;
}

////////////// ��Ω��Ψ��Ͽ���� ���ץ������Ψ
function wageRate_inputCupla($request)
{
    $labor_rate_c = number_format($request->get('labor_rate_c'), 2);
    $query = sprintf("SELECT * FROM assembly_man_labor_rate WHERE item='���ץ�' AND total_date=%d", $request->get('end_ym'));
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {                  // ��Ͽ���� UPDATE����
        $query = sprintf("UPDATE assembly_man_labor_rate SET cut_expense=%d, expense=%d, labor_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='���ץ�' AND total_date=%d", $request->get('cut_expense_c'), $request->get('expense_c'), $labor_rate_c, $_SESSION['User_ID'], $request->get('end_ym'));
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��Ψ����Ͽ���ԡ�";      // .= �����
            $msg_flg = 'alert';
            return false;
        }
    } else {                                                  // ��Ͽ�ʤ� INSERT ����   
        $query = sprintf("INSERT INTO assembly_man_labor_rate (cut_expense, expense, item, total_date, labor_rate, last_date, last_user)
                          VALUES (%d, %d, '���ץ�', %d, '%s',CURRENT_TIMESTAMP, '%s')",
                            $request->get('cut_expense_c'), $request->get('expense_c'), $request->get('end_ym'), $labor_rate_c, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��Ψ����Ͽ���ԡ�";      // .= �����
            $msg_flg = 'alert';
            return false;
        }
    }
    return true;
}

////////////// ��Ω��Ψ��Ͽ���� ��˥�������Ψ
function wageRate_inputLinear($request)
{
    $labor_rate_l = number_format($request->get('labor_rate_l'), 2);
    $query = sprintf("SELECT * FROM assembly_man_labor_rate WHERE item='��˥�' AND total_date=%d", $request->get('end_ym'));
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {                  // ��Ͽ���� UPDATE����
        $query = sprintf("UPDATE assembly_man_labor_rate SET cut_expense=%d, expense=%d, labor_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='��˥�' AND total_date='%d'", $request->get('cut_expense_l'), $request->get('expense_l'), $labor_rate_l, $_SESSION['User_ID'], $request->get('end_ym'));
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��Ψ����Ͽ���ԡ�";      // .= �����
            $msg_flg = 'alert';
            return false;
        }
    } else {                                                  // ��Ͽ�ʤ� INSERT ����   
        $query = sprintf("INSERT INTO assembly_man_labor_rate (cut_expense, expense, item, total_date, labor_rate, last_date, last_user)
                          VALUES (%d, %d, '��˥�', %d, '%s',CURRENT_TIMESTAMP, '%s')",
                            $request->get('cut_expense_l'), $request->get('expense_l'), $request->get('end_ym'), $labor_rate_l, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��Ψ����Ͽ���ԡ�";      // .= �����
            $msg_flg = 'alert';
            return false;
        }
    }
    return true;
}

////////////// ��Ω��Ψ��Ͽ���� ��Ω��ư����Ψ
function wageRate_inputMachine($request)
{
    $group_machine_rate = $request->get('group_machine_rate');
    $res_g              = $request->get('res_g');
    for ($i=0; $i<$request->get('rows_g'); $i++) {
        $group_machine_rate[$i] = round($group_machine_rate[$i], 2);
        $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                            $res_g[$i], $request->get('end_ym'));
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {              // ��Ͽ���� UPDATE����
            $query = sprintf("UPDATE assembly_machine_group_rate SET group_machine_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $group_machine_rate[$i], $_SESSION['User_ID'], $res_g[$i], $request->get('end_ym'));
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "��Ψ����Ͽ���ԡ�";  // .= �����
                $msg_flg = 'alert';
                return false;
            }
        } else {                                              // ��Ͽ�ʤ� INSERT ����   
            $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_machine_rate, last_date, last_user)
                              VALUES (%d, %d, '%s',CURRENT_TIMESTAMP, '%s')",
                                $res_g[$i], $request->get('end_ym'), $group_machine_rate[$i], $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "��Ψ����Ͽ���ԡ�";  // .= �����
                $msg_flg = 'alert';
                return false;
            }
        }
    }
    return true;
}

////////////// ��ȼԡ�ɸ����Ψ�����ϥ����å�
function wageRate_workerCheck ($request)
{
    $worker_figure_s = $request->get('worker_figure_s');
    $worker_figure_p = $request->get('worker_figure_p');
    $worker_rate_s   = $request->get('worker_rate_s');
    $worker_rate_p   = $request->get('worker_rate_p');
    $standard_rate   = $request->get('standard_rate');
    for ($i=0; $i<$request->get('rows_g'); $i++) {    // ̤���ϤΥǡ�����¸�ߤ��ʤ��������å�
        if ($worker_figure_s[$i] == '') {
            $_SESSION['s_sysmsg'] .= "��ȼԿ�(�Ұ�)�����Ϥ���Ƥ��ޤ���";
            return false;
        }
        if ($worker_figure_p[$i] == '') {
            $_SESSION['s_sysmsg'] .= "��ȼԿ�(�ѡ���)�����Ϥ���Ƥ��ޤ���";
            return false;
        }
        if ($worker_rate_s == '') {
            $_SESSION['s_sysmsg'] .= "��ȼ���Ψ(�Ұ�)�����Ϥ���Ƥ��ޤ���";
            return false;
        }
        if ($worker_rate_p == '') {
            $_SESSION['s_sysmsg'] .= "��ȼ���Ψ(�ѡ���)�����Ϥ���Ƥ��ޤ���";
            return false;
        }
        if ($standard_rate[$i] == '') {
            $_SESSION['s_sysmsg'] .= "ɸ����Ψ�����Ϥ���Ƥ��ޤ���";
            return false;
        }
    }
    for ($i=0; $i<$request->get('rows_g'); $i++) {    // ���Ͱʳ���ʸ�������Ϥ���Ƥ��ʤ��������å�
        if (!is_numeric($worker_figure_s[$i])) {
            $_SESSION['s_sysmsg'] .= "��ȼԿ�(�Ұ�)�ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���";
            return false;
        }
        if (!is_numeric($worker_figure_p[$i])) {
            $_SESSION['s_sysmsg'] .= "��ȼԿ�(�ѡ���)�ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���";
            return false;
        }
        if (!is_numeric($worker_rate_s)) {
            $_SESSION['s_sysmsg'] .= "��ȼ���Ψ(�Ұ�)�ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���";
            return false;
        }
        if (!is_numeric($worker_rate_p)) {
            $_SESSION['s_sysmsg'] .= "��ȼ���Ψ(�ѡ���)�ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���";
            return false;
        }
        if (!is_numeric($standard_rate[$i])) {
            $_SESSION['s_sysmsg'] .= "ɸ����Ψ�ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���";
            return false;
        }
    }
    return true;
}


////////////// �ǡ�����Ͽ�Υ����å�
function registered_data_check($request, $result)
{
    if ($request->get('kessan') != '') {
        $chk_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $chk_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    for ($chk_ym; $end_ym >= $chk_ym; $chk_ym++) {
        $chk_nen   = substr($chk_ym, 0, 4);                                               // �����å���ǯ
        $chk_tsuki = substr($chk_ym, 4, 2);                                               // �����å��ѷ�
        if ($chk_tsuki == 13) {                                                           // �13�ˤʤä���ǯ������夬�äƷ�򣰣���
            $chk_nen   = $chk_nen + 1;
            $chk_tsuki = '01';
            $chk_ym = $chk_nen . $chk_tsuki;
        }
        $query = sprintf("SELECT group_capital FROM assembly_machine_group_rate WHERE total_date=%d AND group_capital >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= $result->get('rows_g')) return false;    // ��Ͽ�ѤߤΥ����å�
        $query = sprintf("SELECT group_lease FROM assembly_machine_group_rate WHERE total_date=%d AND group_lease >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= $result->get('rows_g')) return false;    // ��Ͽ�ѤߤΥ����å�
        $query = sprintf("SELECT group_repair FROM assembly_machine_group_rate WHERE total_date=%d AND group_repair >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= $result->get('rows_g')) return false;    // ��Ͽ�ѤߤΥ����å�
        $query = sprintf("SELECT group_time FROM assembly_machine_group_rate WHERE total_date=%d AND group_time >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= $result->get('rows_g')) return false;    // ��Ͽ�ѤߤΥ����å�
        $query = sprintf("SELECT worker_time FROM assembly_man_labor_rate WHERE total_date=%d AND worker_time >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= 2) return false;                         // ��Ͽ�ѤߤΥ����å�
        $query = sprintf("SELECT assistance_time FROM assembly_man_labor_rate WHERE total_date=%d AND assistance_time >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= 2) return false;                         // ��Ͽ�ѤߤΥ����å�
        $query = sprintf("SELECT worker_figure FROM worker_figure_master WHERE total_date=%d AND worker_figure >=0", $chk_ym);
        if ( ($rows=getResult($query, $res)) >= $result->get('rows_g')) {                 // ��Ͽ�ѤߤΥ����å�
            $request->add('data_check', 1);                                               // ��Ͽ�Ѥ�
        } else {
            $request->add('data_check', 4);                                               // ��ȼ�̤��Ͽ ���ɤ��ǥ��顼�ˤʤä�����Ƚ�Ǥ���Τ�
                                                                                          // data_check�ο������Ѥ��Ƥ��������ߤ�̤����
            return false;
        }
    }
    if ( ($rows=getResult($query, $res)) > 0) {    // ��Ͽ�ѤߤΥ����å�
    } else {
        $_SESSION['s_sysmsg'] .= "���ν����������¤����μ����ߤ�ԤäƤ���¹Ԥ��Ƥ���������";    // .= �����
        $msg_flg = 'alert';
        return false;
    }
    if ($request->get('data_check') == 1) {
        return true;
    }
}

////////////// ��������ɽ���ΰ٤�����׻�
function before_date ($request)
{
    $before_ym = '';
    if ($request->get('kessan') != '') {
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $end_ym = $request->get('tan_end_ym');
    }
    $nen   = substr($end_ym, 0, 4);
    $tsuki = substr($end_ym, 4, 2);
    if (1 == $tsuki) {
        $nen   = $nen - 1;
        $tsuki = 12;
    } else {
        $tsuki = $tsuki - 1;
        if ($tsuki < 10) {
            $tsuki = 0 . $tsuki;
        }
    }
    $before_ym = $nen . $tsuki;
    $request->add('before_ym', $before_ym);
}

////////////// ����Υǡ����μ���
function get_before_date ($request, $result)
{
    $before_standard_rate = array();                             // �����ɸ���ͽ¬����Ψ
    $before_machine_rate = array();                              // �����ɸ���ͽ¬����Ψ
    $be_worker_figure_s = array();                               // �����ȼԿ��ʼҰ���
    $be_worker_figure_p = array();                               // �����ȼԿ��ʥѡ��ȡ�
    $be_worker_rate_s = '';                                      // �����ȼ���Ψ�ʼҰ���
    $be_worker_rate_p = '';                                      // �����ȼ���Ψ�ʥѡ��ȡ�
    $group_no_be = array();                                      // ����Υ��롼���ֹ�
    $before_labor_rate_t = 0;                                    // ����μ�����Ψ(���)
    $before_labor_rate_c = 0;                                    // ����μ�����Ψ(���ץ�)
    $before_labor_rate_l = 0;                                    // ����μ�����Ψ(��˥�)
    $res_g    = $result->get_array2('res_g');
    $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d order by group_no", $request->get('before_ym'));
    $res = array();
    $rows_act = getResult($query, $res);
    for ($i=0; $i<$rows_act; $i++) {
        $group_no_be[$i] = $res[$i]['group_no'];
    }
    for ($i=0; $i<$result->get('rows_g'); $i++) {                // �����ɸ����Ψ�ν���
        $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d AND group_no=%d", $request->get('before_ym'), $group_no_be[$i]);
        $res = array();
        $rows_act = getResult($query, $res);
        for ($r=0; $r<$rows_act; $r++) {
            $before_standard_rate[$i] = $res[$r]['standard_rate'];
        }
    }
    for ($i=0; $i<$result->get('rows_g'); $i++) {                // ����μ�ư����Ψ�ν���
        $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d AND group_no=%d", $request->get('before_ym'), $group_no_be[$i]);
        $res = array();
        $rows_act = getResult($query, $res);
        for ($r=0; $r<$rows_act; $r++) {
            $before_machine_rate[$i] = $res[$r]['group_machine_rate'];
        }
    }
    $query = sprintf("SELECT * FROM assembly_man_labor_rate WHERE total_date=%d", $request->get('before_ym'));
    $res_be = array();
    $rows_be = getResult($query, $res_be);
    for ($i=0; $i<$rows_be; $i++) {
        if ($res_be[$i]['item'] == '����') {
            $before_labor_rate_t = $res_be[$i]['labor_rate'];    // ����μ�����Ψ�ʹ�ס�
        } else if ($res_be[$i]['item'] == '���ץ�') {
            $before_labor_rate_c = $res_be[$i]['labor_rate'];    // ����μ�����Ψ�ʥ��ץ��
        } else if ($res_be[$i]['item'] == '��˥�') {
            $before_labor_rate_l = $res_be[$i]['labor_rate'];    // ����μ�����Ψ�ʥ�˥���
        }
    }
    $result->add('before_labor_rate_t', $before_labor_rate_t);
    $result->add('before_labor_rate_c', $before_labor_rate_c);
    $result->add('before_labor_rate_l', $before_labor_rate_l);
    $result->add_array2('before_machine_rate', $before_machine_rate);
    $result->add_array2('before_standard_rate', $before_standard_rate);
}

////////////// ��������Ӻ�ȼԥǡ����μ���
function get_before_figure ($request, $result)
{
    $before_standard_rate = array();                 // �����ɸ���ͽ¬����Ψ
    $before_machine_rate = array();                  // �����ɸ���ͽ¬����Ψ
    $be_worker_figure_s = array();                   // �����ȼԿ��ʼҰ���
    $be_worker_figure_p = array();                   // �����ȼԿ��ʥѡ��ȡ�
    $be_worker_rate_s = '';                          // �����ȼ���Ψ�ʼҰ���
    $be_worker_rate_p = '';                          // �����ȼ���Ψ�ʥѡ��ȡ�
    $group_no_be = array();                          // ����Υ��롼���ֹ�
    $before_labor_rate_t = 0;                        // ����μ�����Ψ(���)
    $before_labor_rate_c = 0;                        // ����μ�����Ψ(���ץ�)
    $before_labor_rate_l = 0;                        // ����μ�����Ψ(��˥�)
    $res_g    = $result->get_array2('res_g');
    $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d order by group_no", $request->get('before_ym'));
    $res = array();
    $rows_act = getResult($query, $res);
    for ($i=0; $i<$rows_act; $i++) {
        $group_no_be[$i] = $res[$i]['group_no'];
    }
    for ($i=0; $i<$result->get('rows_g'); $i++) {    // �����ɸ����Ψ�ν���
        $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d AND group_no=%d", $request->get('before_ym'), $group_no_be[$i]);
        $res = array();
        $rows_act = getResult($query, $res);
        for ($r=0; $r<$rows_act; $r++) {
            $before_standard_rate[$i] = $res[$r]['standard_rate'];
        }
    }
    for ($i=0; $i<$result->get('rows_g'); $i++) {    // ��������Ӻ�ȼԿ��ν���
        $query = sprintf("SELECT * FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=1", $group_no_be[$i], $request->get('before_ym'));
        $res_wo = array();
        $rows_wo = getResult($query, $res_wo);
        for ($r=0; $r<$rows_wo; $r++) {
            $be_worker_figure_s[$i] = $res_wo[$r]['worker_figure'];
            $be_worker_rate_s = $res_wo[$r]['worker_rate'];
        }
    }
    for ($i=0; $i<$result->get('rows_g'); $i++) {    // ��������Ӻ�ȼԿ��ν���
        $query = sprintf("SELECT * FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=2", $group_no_be[$i], $request->get('before_ym'));
        $res_wo = array();
        $rows_wo = getResult($query, $res_wo);
        for ($r=0; $r<$rows_wo; $r++) {
            $be_worker_figure_p[$i] = $res_wo[$r]['worker_figure'];
            $be_worker_rate_p = $res_wo[$r]['worker_rate'];
        }
    }
    $result->add_array2('be_worker_figure_s', $be_worker_figure_s);
    $result->add_array2('be_worker_figure_p', $be_worker_figure_p);
    $result->add_array2('before_standard_rate', $before_standard_rate);
    $result->add('be_worker_rate_s', $be_worker_rate_s);
    $result->add('be_worker_rate_p', $be_worker_rate_p);
}

////////////// ɽ���ѥǡ����׻�
function show_data_cal($result) 
{
    $total_expense_sen = $result->get('total_expense') / 1000;                                                // ����ľ�ܷ���ס���ߡ�
    $expense_c_sen     = $result->get('expense_c') / 1000;                                                    // ���ץ�ľ�ܷ���ס���ߡ�
    //$expense_c_sen     = $result->get('expense_c');                                                    // ���ץ�ľ�ܷ���ס���ߡ�
    $expense_l_sen     = $result->get('expense_l') / 1000;                                                    // ��˥�ľ�ܷ���ס���ߡ�
    //$expense_l_sen     = $result->get('expense_l');                                                    // ��˥�ľ�ܷ���ס���ߡ�
    //$assist_expense    = $result->get('total_assistance_time') / 60 * 1090 / 1000;                            // �������������Ρ���ߡ�
    $assist_expense_c  = $result->get('assist_c') / 60 * 1090 / 1000;                                         // ���������񥫥ץ����ߡ�
    //$assist_expense_c  = $result->get('assist_c') / 60 * 1090;                                         // ���������񥫥ץ����ߡ�
    $assist_expense_l  = $result->get('assist_l') / 60 * 1090 / 1000;                                         // �����������˥�����ߡ�
    //$assist_expense_l  = $result->get('assist_l') / 60 * 1090;                                         // �����������˥�����ߡ�
    $total_keihi       = $total_expense_sen + $assist_expense;                                                // ���Τη�����ʬ�ι��
    $total_keihi_c     = $expense_c_sen + $assist_expense_c;                                                  // ���ץ�η�����ʬ�ι��
    $total_keihi_l     = $expense_l_sen + $assist_expense_l;                                                  // ��˥�������ʬ�ι��
    $total_keihi_cut   = $total_keihi - $result->get('direct_expenses') - $result->get('total_man_expenses'); // ���θ���ʬ��ȴ��������ι��
    $keihi_cut_c       = $total_keihi_c - $result->get('direct_expenses_c') - $result->get('man_expenses_c'); // ���ץ鸺��ʬ��ȴ��������ι��
    $keihi_cut_l       = $total_keihi_l - $result->get('direct_expenses_l') - $result->get('man_expenses_l'); // ��˥�����ʬ��ȴ��������ι��
    $total_assemble    = $result->get('total_worker_time') - $result->get('total_assistance_time');           // ������Ω��Ȼ��ַ�
    $assemble_c        = $result->get('worker_time_c') - $result->get('assist_c');                            // ���ץ���Ω��Ȼ��ַ�
    $assemble_l        = $result->get('worker_time_l') - $result->get('assist_l');                            // ��˥���Ω��Ȼ��ַ�

    $result->add('expense_c_sen', $expense_c_sen);
    $result->add('expense_l_sen', $expense_l_sen);
    $result->add('assist_expense', $assist_expense);
    $result->add('assist_expense_c', $assist_expense_c);
    $result->add('assist_expense_l', $assist_expense_l);
    $result->add('total_keihi', $total_keihi);
    $result->add('total_keihi_c', $total_keihi_c);
    $result->add('total_keihi_l', $total_keihi_l);
    $result->add('total_keihi_cut', $total_keihi_cut);
    $result->add('keihi_cut_c', $keihi_cut_c);
    $result->add('keihi_cut_l', $keihi_cut_l);
    $result->add('total_assemble', $total_assemble);
    $result->add('assemble_c', $assemble_c);
    $result->add('assemble_l', $assemble_l);
}

////////////// ���ȷ�����Ϥ��ǡ����η׻�(����)
function laborRate_data_all($result, $request)
{
    $total_capital      = 0;                                            // ���Τθ���������
    $total_lease        = 0;                                            // ���ΤΥ꡼����
    $total_repair       = 0;                                            // ���Τν�����
    $total_time         = 0;                                            // ���Τα�ž����
    $total_man_expenses = 0;                                            // ���Τ����Ӻ�ȼԷ���
    $total_cut_expense  = 0;                                            // ����������
    $group_capital_sen = $result->get_array2('group_capital_sen');
    $group_lease_sen = $result->get_array2('group_lease_sen');
    $group_repair_sen = $result->get_array2('group_repair_sen');
    $group_time = $result->get_array2('group_time');
    $man_expenses = $result->get_array2('man_expenses');
    $group_expenses = $result->get_array2('group_expenses');
    for ($i=0; $i<$result->get('rows'); $i++) {
        $total_capital      += $group_capital_sen[$i];                  // ����������ι��
        $total_lease        += $group_lease_sen[$i];                    // �꡼�����ι��
        $total_repair       += $group_repair_sen[$i];                   // ������ι��
        $total_time         += $group_time[$i];                         // ��Ư���֤ι��
        $total_man_expenses += $man_expenses[$i];                       // ���Ӻ�ȼԷ���ι��
        if ($group_time[$i] > 0 ) {
            $total_rate = $group_expenses[$i] / $group_time[$i] * 1000; // ��Ψ �� ľ�ܷ�����Ư���֡�1000��ñ�̱ߡ�
        } else {
            $total_rate = 0;
        }
            $total_cut_expense  += $group_expenses[$i];                 // ���������פη׻�
            $total_cut_expense  += $man_expenses[$i];                   // ���Τμ�����Ψ�˻���
    }
    $request->add('total_lease', $total_lease);
    $request->add('total_capital', $total_capital);
    $result->add('total_man_expenses', $total_man_expenses);
    $request->add('total_repair', $total_repair);
    $request->add('total_time', $total_time);
    $direct_expenses = $total_capital + $total_lease + $total_repair;   // ľ�ܷ�����
    if ($direct_expenses[$i] > 0 ) {
        if ($total_time[$i] > 0 ) {
            $total_rate = $direct_expenses / $total_time * 1000;        // ��Ψ �� ľ�ܷ�����Ư���֡�1000��ñ�̱ߡ�
        } else {
            $total_rate = 0;
        }
    } else {
        $total_rate = 0;
    }
    $result->add('direct_expenses', $direct_expenses);
    $result->add('total_rate', $total_rate);
    $result->add('total_cut_expense', $total_cut_expense);
}

////////////// ���ȷ�����Ϥ��ǡ����η׻�(����)
function laborRate_data_cl($result, $request)
{
    $cut_expense_c      = 0;                                  // ���ץ������¤����
    $cut_expense_l      = 0;                                  // ��˥�������¤����
    $direct_expenses_c  = 0;                                  // ���ץ�ľ�ܷ���
    $direct_expenses_l  = 0;                                  // ��˥�ľ�ܷ���
    $man_expenses_c     = 0;                                  // ���ץ��ȷ���
    $man_expenses_l     = 0;                                  // ��˥���ȷ���
    $group_expenses = $result->get_array2('group_expenses');
    $man_expenses = $result->get_array2('man_expenses');
    $group_no = $result->get_array2('group_no');
    $res_g    = $result->get_array2('res_g');
    for ($i=0; $i<$result->get('rows'); $i++) {
        ////// ���ȷ�����Ϥ��ǡ����η׻�CL
        switch (format_number_name($group_no[$i], $res_g, $result->get('rows_g'))) {
            case '�ԥ��ȥ�':                                  // ���ߥ�˥��ϥԥ��ȥ�Τ�
                $cut_expense_l     += $group_expenses[$i];    // ��˥�������¤����
                $direct_expenses_l += $group_expenses[$i];    // ��˥�ľ�ܷ���
                $cut_expense_l     += $man_expenses[$i];      
                $man_expenses_l    += $man_expenses[$i];      // ��˥���ȷ���
                break;
            default:                                          // �ԥ��ȥ�ʳ��ϥ��ץ�
                $cut_expense_c     += $group_expenses[$i];    // ���ץ������¤����
                $direct_expenses_c += $group_expenses[$i];    // ���ץ�ľ�ܷ���
                $cut_expense_c     += $man_expenses[$i];
                $man_expenses_c    += $man_expenses[$i];      // ���ץ��ȷ���
                break;
        }                    
    }
    $result->add('direct_expenses_c', $direct_expenses_c);
    $result->add('direct_expenses_l', $direct_expenses_l);
    $result->add('man_expenses_c', $man_expenses_c);
    $result->add('man_expenses_l', $man_expenses_l);
    $result->add('cut_expense_c', $cut_expense_c);
    $result->add('cut_expense_l', $cut_expense_l);
}

////////////// �Ƽ�ǡ����μ���
function get_various_data($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d  order by group_no", $end_ym);
    $res = array();
    $rows = getResult($query, $res);
    for ($i=0; $i<$rows; $i++) {
        $group_no[$i]           = $res[$i]['group_no'];
        $group_capital[$i]      = $res[$i]['group_capital'];
        $group_lease[$i]        = $res[$i]['group_lease'];
        $group_machine_rate[$i] = $res[$i]['group_machine_rate'];
        $standard_rate[$i]      = $res[$i]['standard_rate'];
    }
    $result->add_array2('group_no', $group_no);
    $result->add_array2('group_capital', $group_capital);
    $result->add_array2('group_lease', $group_lease);
    $result->add_array2('group_machine_rate', $group_machine_rate);
    $result->add_array2('standard_rate', $standard_rate);
    for ($i=0; $i<$rows; $i++) {    // ���Ӻ�ȼ�(�Ұ�)����
        $query = sprintf("SELECT * FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=1", $group_no[$i], $end_ym);
        $res_wf = array();
        $rows_wf = getResult($query, $res_wf);
        for ($r=0; $r<$rows_wf; $r++) {
            $worker_figure_s[$i] = $res_wf[$r]['worker_figure'];
            $worker_rate_s[$i] = $res_wf[$r]['worker_rate'];
        }
    }
    for ($i=0; $i<$rows; $i++) {    // ���Ӻ�ȼ�(�ѡ���)����
        $query = sprintf("SELECT * FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=2", $group_no[$i], $end_ym);
        $res_wf = array();
        $rows_wf = getResult($query, $res_wf);
        for ($r=0; $r<$rows_wf; $r++) {
            $worker_figure_p[$i] = $res_wf[$r]['worker_figure'];
            $worker_rate_p[$i] = $res_wf[$r]['worker_rate'];
        }
    }
    $result->add_array2('worker_figure_s', $worker_figure_s);
    $result->add_array2('worker_figure_p', $worker_figure_p);
    $result->add_array2('worker_rate_s', $worker_rate_s);
    $result->add_array2('worker_rate_p', $worker_rate_p);
}

////////////// ��Ͽ�Ѥߥǡ����μ���
function get_registered_data($request, $result)
{
    $res_g = $result->get_array2('res_g');
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d", $end_ym);
    $res = array();
    $rows = getResult($query, $res);
    $result->add('rows', $rows);
    if ($res[0]['group_machine_rate'] == '') {                // ��Ψ����Ͽ�Ѥߤ������å�
        ////// ���� ̤��Ͽ�ξ�����Ψ�׻��Υץ�����
        $request->add('rate_register', '��Ͽ');
        return false;
    } else if ($request->get('rate_register') == '��Ͽ') {    // ñ��ɤ��������å�
        ////// ñ��ξ�����Ψ�׻��Υץ�����
        $request->add('rate_register', '��Ͽ');
        return false;
    } else {
        ////// ���򤢤�ξ�����Ͽ�ѤߤΥǡ��������������ǡ�����׻�����
        $request->add('rate_register', '�Ȳ�');
        get_various_data ($request, $result);                 // �Ƽ�ǡ����μ���
        get_group_data ($result, $request);                   // ���롼���̥ǡ����μ���
        get_manRate_data ($result, $request);                 // ������Ψ�Ƽ�ǡ�������
        cal_registered_tempData ($result, $request);          // ��Ͽ�Ѥ߰���ǡ����׻�
        laborRate_data_all($result, $request);                // ���ȷ�����Ϥ��ǡ����η׻�(����)
        laborRate_data_cl($result, $request);                 // ���ȷ�����Ϥ��ǡ����η׻�(CL)
        get_expense_data ($result, $request);                 // ��¤�������
        show_data_cal($result);                               // ɽ���ѥǡ����׻�
        get_before_date ($request, $result);                  // ����Υǡ����μ���
        return true;
    }
}
////////////////// ��Ͽ�Ѥ߰���ǡ����׻�
function cal_registered_tempData ($result, $request)
{
    $group_capital   = $result->get_array2('group_capital');
    $group_lease     = $result->get_array2('group_lease');
    $group_repair    = $result->get_array2('group_repair');
    $group_time      = $result->get_array2('group_time');
    $worker_figure_s = $result->get_array2('worker_figure_s');
    $worker_figure_p = $result->get_array2('worker_figure_p');
    $worker_rate_s   = $result->get_array2('worker_rate_s');
    $worker_rate_p   = $result->get_array2('worker_rate_p');            
    for ($i=0; $i<$result->get('rows'); $i++) {
        $group_capital_sen[$i] = $group_capital[$i] / 1000;              // ���롼���̸���������(ñ�����)
        $group_lease_sen[$i]   = $group_lease[$i] / 1000;                // ���롼���̥꡼����(ñ�����)
        $group_repair_sen[$i]  = $group_repair[$i] / 1000;               // ���롼���̽�����(ñ�����)
        ////////// ľ�ܷ����ñ����ߡ�
        $group_expenses[$i] = ($group_capital[$i] + $group_lease[$i] + $group_repair[$i]) / 1000;
        if ($group_time[$i] <= 0) {                                      // ��Ư���֤����ʲ��ʤ���Ψ�⣰
            $rate[$i] = 0;
        } else {
            $rate[$i] = $group_expenses[$i] / $group_time[$i] * 1000;    // ��Ψ �� ľ�ܷ�����Ư���֡�1000��ñ�̱ߡ�
        }
            $man_expenses[$i] = $group_time[$i] * $worker_figure_s[$i] * $worker_rate_s[$i] / 1000 + $group_time[$i] * $worker_figure_p[$i] * $worker_rate_p[$i] / 1000; //���Ӻ�ȼԷ��� �� ��Ư���֡ߺ�ȼԿ���ɸ����Ψ��1000��ñ����ߡ�
    }
    $result->add_array2('group_capital_sen', $group_capital_sen);
    $result->add_array2('group_lease_sen', $group_lease_sen);
    $result->add_array2('group_repair_sen', $group_repair_sen);
    $result->add_array2('group_time', $group_time);
    $result->add_array2('man_expenses', $man_expenses);
    $result->add_array2('group_expenses', $group_expenses);
    $result->add_array2('rate', $rate);
}
////////////////// ������Ψ�Ƽ�ǡ�������
function get_manRate_data ($result, $request)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $query_man = sprintf("SELECT * FROM assembly_man_labor_rate WHERE total_date=%d", $end_ym);
    $res_man = array();
    $rows_man = getResult($query_man, $res_man);
    $result->add('rows_man', $rows_man);
    for ($i=0; $i<$rows_man; $i++) {                                     // ������Ψ�Ƽ�ǡ�������
        $item[$i]            = $res_man[$i]['item'];
        $worker_time[$i]     = $res_man[$i]['worker_time'];
        $assistance_time[$i] = $res_man[$i]['assistance_time'];
        $expense[$i]         = $res_man[$i]['expense'];
        $man_labor_rate[$i]  = $res_man[$i]['labor_rate'];
    }
    $result->add_array2('item', $item);
    $result->add_array2('worker_time', $worker_time);
    $result->add_array2('assistance_time', $assistance_time);
    $result->add_array2('expense', $expense);
    for ($i=0; $i<$rows_man; $i++) {                                     // ������Ψ�������ؿ�ʬ
        if ($item[$i] == '���ץ�') {
            $labor_rate_c = $man_labor_rate[$i];
        } else if ($item[$i] == '��˥�') {
            $labor_rate_l = $man_labor_rate[$i];
        } else {
            $labor_rate = $man_labor_rate[$i];
        }
    }
    $result->add('labor_rate', $labor_rate);
    $result->add('labor_rate_c', $labor_rate_c);
    $result->add('labor_rate_l', $labor_rate_l);   
}

////////////////// ���롼���̥ǡ����μ���
function get_group_data ($result, $request)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $group_no = $result->get_array2('group_no');
    for ($i=0; $i<$result->get('rows_g'); $i++) {
        $query = sprintf("SELECT sum(group_repair) FROM assembly_machine_group_rate WHERE total_date>=%d AND total_date<=%d AND group_no=%d", $str_ym, $end_ym, $group_no[$i]);
        $res_sum = array();                        // ���롼���̤ν�����׻�
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $group_repair[$i] = 0;
        } else {
            $group_repair[$i] = $res_sum[0]['sum'];
        }
        $query = sprintf("SELECT sum(group_time) FROM assembly_machine_group_rate WHERE total_date>=%d AND total_date<=%d AND group_no=%d", $str_ym, $end_ym, $group_no[$i]);
        $res_sum = array();                        // ���롼���̲�Ư���ַ׻�
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $group_time[$i] = 0;
        } else {
            $group_time[$i] = $res_sum[0]['sum'];
        }
    }
    for ($i=0; $i<$result->get('rows'); $i++) {    // ���롼����ε����ֹ�μ���
        $query = sprintf("SELECT * FROM assembly_machine_group_work WHERE group_no=%d AND total_date=%d", $group_no[$i], $end_ym);
        $res_mac = array();
        $rows_mac = getResult($query, $res_mac);
        for ($r=0; $r<$rows_mac; $r++) {
            $group_mac_no[$i][$r] = $res_mac[$r]['mac_no'];
        }
    }
    $result->add_array2('group_repair', $group_repair);
    $result->add_array2('group_time', $group_time);
    $result->add_array2('group_mac_no', $group_mac_no);
}

////////////////// ��¤�������
function get_expense_data ($result, $request)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $item            = $result->get_array2('item');
    $worker_time     = $result->get_array2('worker_time');
    $assistance_time = $result->get_array2('assistance_time');
    $expense         = $result->get_array2('expense');
    for ($i=0; $i<$result->get('rows_man'); $i++) {
        switch ($item[$i]) {
            case '����':
                $total_assistance_time = $assistance_time[$i];           // ��ױ�����ַ׻�
                $total_worker_time = $worker_time[$i];                   // ��׺�Ȼ��ַ׻�
                break;
            case '���ץ�':                                               // �оݥ��롼�פ����ץ�λ�
                $query = sprintf("SELECT sum(worker_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='���ץ�'", $str_ym, $end_ym);
                $res_sum = array();
                $rows_sum = getResult($query, $res_sum);
                if ($res_sum[0]['sum'] == "") {
                    $worker_time_c = 0;                                  // ��Ω���ַ׻�
                } else {
                    $worker_time_c = $res_sum[0]['sum'];
                }
                $query = sprintf("SELECT sum(assistance_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='���ץ�'", $str_ym, $end_ym);
                $res_sum = array();
                $rows_sum = getResult($query, $res_sum);
                if ($res_sum[0]['sum'] == "") {
                    $assist_c = 0;                                       // ������ַ׻�
                } else {
                    $assist_c = $res_sum[0]['sum'];
                }
                $expense_c = $expense[$i];
                break;
            case '��˥�':                                               // �оݥ��롼�פ���˥��λ�
                $query = sprintf("SELECT sum(worker_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='��˥�'", $str_ym, $end_ym);
                $res_sum = array();
                $rows_sum = getResult($query, $res_sum);
                if ($res_sum[0]['sum'] == "") {
                    $worker_time_l = 0;                                  // ��Ω���ַ׻�
                } else {
                    $worker_time_l = $res_sum[0]['sum'];
                }
                $query = sprintf("SELECT sum(assistance_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='��˥�'", $str_ym, $end_ym);
                $res_sum = array();
                $rows_sum = getResult($query, $res_sum);
                if ($res_sum[0]['sum'] == "") {
                    $assist_l = 0;                                       // ������ַ׻�
                } else {
                    $assist_l = $res_sum[0]['sum'];
                }
                $expense_l = $expense[$i];
                break;
            default:
                break;
        }
    }
    $total_expense = $expense_c + $expense_l;                            // �����¤����׻�
    $result->add('total_assistance_time', $total_assistance_time);
    $result->add('total_worker_time', $total_worker_time);
    $result->add('worker_time_c', $worker_time_c);
    $result->add('worker_time_l', $worker_time_l);    
    $result->add('assist_c', $assist_c);
    $result->add('assist_l', $assist_l);
    $result->add('total_expense', $total_expense);
    $result->add('expense_c', $expense_c);
    $result->add('expense_l', $expense_l);
}

////////////////// ��Ψ�׻�
function assembly_rate_cal ($request, $result, $menu)
{
    $res_g = $result->get_array2('res_g');
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    get_various_data($request, $result);                                       // �Ƽ�ǡ����μ���
    get_group_data ($result, $request);                                        // ���롼���̥ǡ����μ���
    cal_temp_data ($result, $request);                                         // ����ǡ����η׻�
    laborRate_data_all($result, $request);                                     // ���ȷ�����Ϥ��ǡ����η׻�
    laborRate_data_cl($result, $request);
    cal_manRate_data ($result, $request);                                      // ������Ψ�Ƽ�ǡ����׻�
    cal_expense_data ($result, $request);                                      // ��¤����׻�
    show_data_cal($result);                                                    // ɽ���ѥǡ����׻�
    cal_labor_rate ($result);                                                  // ������Ψ�׻�
    get_before_date ($request, $result);                                       // ����Υǡ����μ���
}

////////////////// ������Ψ�׻�
function cal_labor_rate ($result)
{
    $labor_rate    = 0;                          // ���μ�����Ψ
    $labor_rate_c  = 0;                          // ���ץ������Ψ
    $labor_rate_l  = 0;                          // ��˥�������Ψ
    if ($result->get('total_expense') == 0) {    // ��פ���¤���񤬣����ä���������Ψ�Ϸ׻��Բ�
        $labor_rate = '----';
    } else {                                                                   
        ////////// ���Ρ����ץ顦��˥��μ�����Ψ�׻� ��ľ�ܷ���סܲ��������񡼽��������ľ�ܡ���ȷ��񸺳�ʬ�ˡˡ���Ω��Ȼ���
        $labor_rate   = ( $result->get('total_expense') + ( $result->get('total_assistance_time') / 60 * 1090) - ($result->get('total_cut_expense') * 1000) ) / $result->get('total_worker_time');
        $labor_rate_c = ( $result->get('expense_c') + ( $result->get('assist_c') / 60 * 1090) - ($result->get('cut_expense_c') * 1000) ) / $result->get('worker_time_c');
        $labor_rate_l = ( $result->get('expense_l') + ( $result->get('assist_l') / 60 * 1090) - ($result->get('cut_expense_l') * 1000) ) / $result->get('worker_time_l');
    }
    $result->add('labor_rate', $labor_rate);
    $result->add('labor_rate_c', $labor_rate_c);
    $result->add('labor_rate_l', $labor_rate_l);
}

////////////////// ����ǡ����׻�
function cal_temp_data ($result, $request)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $group_capital = $result->get_array2('group_capital');
    $group_repair = $result->get_array2('group_repair');
    $group_lease = $result->get_array2('group_lease');
    $group_time = $result->get_array2('group_time');
    $worker_figure_s = $result->get_array2('worker_figure_s');
    $worker_figure_p = $result->get_array2('worker_figure_p');
    $worker_rate_s = $result->get_array2('worker_rate_s');
    $worker_rate_p = $result->get_array2('worker_rate_p');
    $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d  order by group_no", $end_ym);
    $res = array();
    $rows_act = getResult($query, $res);
    $result->add('rows', $rows_act);
    for ($i=0; $i<$rows_act; $i++) {
        $group_capital_sen[$i] = $group_capital[$i] / 1000;              // ���롼���̸���������(ñ�����)
        $group_lease_sen[$i]   = $group_lease[$i] / 1000;                // ���롼���̥꡼����(ñ�����)
        $group_repair_sen[$i]  = $group_repair[$i] / 1000;               // ���롼���̽�����(ñ�����)
        /*
        $group_capital_sen[$i] = $group_capital[$i];              // ���롼���̸���������(ñ�����)
        $group_lease_sen[$i]   = $group_lease[$i];                // ���롼���̥꡼����(ñ�����)
        $group_repair_sen[$i]  = $group_repair[$i];               // ���롼���̽�����(ñ�����)
        */
        ////////// ľ�ܷ����ñ����ߡ�
        $group_expenses[$i] = ($group_capital[$i] + $group_lease[$i] + $group_repair[$i]) / 1000;
        //$group_expenses[$i] = ($group_capital[$i] + $group_lease[$i] + $group_repair[$i]);
        if ($group_time[$i] <= 0) {                                      // ��Ư���֤����ʲ��ʤ���Ψ�⣰
            $rate[$i] = 0;
        } else {
            $rate[$i] = $group_expenses[$i] / $group_time[$i] * 1000;    // ��Ψ �� ľ�ܷ�����Ư���֡�1000��ñ�̱ߡ�
        }
        $man_expenses[$i] = $group_time[$i] * $worker_figure_s[$i] * $worker_rate_s[$i] / 1000 + $group_time[$i] * $worker_figure_p[$i] * $worker_rate_p[$i] / 1000; //���Ӻ�ȼԷ��� �� ��Ư���֡ߺ�ȼԿ���ɸ����Ψ��1000��ñ����ߡ�
        //$man_expenses[$i] = $group_time[$i] * $worker_figure_s[$i] * $worker_rate_s[$i] + $group_time[$i] * $worker_figure_p[$i] * $worker_rate_p[$i]; //���Ӻ�ȼԷ��� �� ��Ư���֡ߺ�ȼԿ���ɸ����Ψ��1000��ñ����ߡ�
    }
    for ($i=0; $i<$rows_act; $i++) {
        ////////// ��ư����Ψ�׻�
        $group_machine_rate[$i] = $rate[$i] + $worker_figure_s[$i] * $worker_rate_s[$i] + $worker_figure_p[$i] * $worker_rate_p[$i]; //��ư����Ψ ��Ψ��ɸ����Ψ�ߺ�ȼԿ�
        if ($group_time[$i]==0 & $man_expenses[$i]==0) {                 // ���Ӻ�ȼԷ���Ȳ�Ư���֤�0�ʤ鼫ư����Ψ�⣰
            $group_machine_rate[$i] = 0;
        }
    }
    $result->add_array2('group_machine_rate', $group_machine_rate);
    $result->add_array2('group_capital_sen', $group_capital_sen);
    $result->add_array2('group_lease_sen', $group_lease_sen);
    $result->add_array2('group_repair_sen', $group_repair_sen);
    $result->add_array2('group_time', $group_time);
    $result->add_array2('man_expenses', $man_expenses);
    $result->add_array2('group_expenses', $group_expenses);
    $result->add_array2('rate', $rate);
}

////////////////// ������Ψ�Ƽ�ǡ����׻�
function cal_manRate_data ($result, $request)
{
    $total_worker_time = 0;                  // ��Ȼ��ֹ��
    $total_assistance_time = 0;              // ������ֹ��
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $query_man = sprintf("SELECT * FROM assembly_man_labor_rate WHERE total_date=%d", $end_ym);
    $res_man = array();
    $rows_man = getResult($query_man, $res_man);
    for ($i=0; $i<$rows_man; $i++) {
        $item[$i] = $res_man[$i]['item'];    // ��ȥ��롼��
    }
    for ($i=0; $i<$rows_man; $i++) {
        $query = sprintf("SELECT sum(worker_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='%s'", $str_ym, $end_ym, $item[$i]);
        $res_sum = array();
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $worker_time[$i] = 0;            // ��Ω���ַ׻�
        } else {
            $worker_time[$i] = $res_sum[0]['sum'];
        }
        $query = sprintf("SELECT sum(assistance_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='%s'", $str_ym, $end_ym, $item[$i]);
        $res_sum = array();
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $assistance_time[$i] = 0;        // ������ַ׻�
        } else {
            $assistance_time[$i] = $res_sum[0]['sum'];
        }
    }
    $result->add_array2('item', $item);
    $result->add_array2('worker_time', $worker_time);
    $result->add_array2('assistance_time', $assistance_time);
}


////////////////// ��¤����׻�
function cal_expense_data ($result, $request)
{
    $total_worker_time = 0;                                                    // ��Ȼ��ֹ��
    $total_assistance_time = 0;                                                // ������ֹ��
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $item            = $result->get_array2('item');
    $worker_time     = $result->get_array2('worker_time');
    $assistance_time = $result->get_array2('assistance_time');
    $expense         = $result->get_array2('expense');
    $query_man = sprintf("SELECT * FROM assembly_man_labor_rate WHERE total_date=%d", $end_ym);
    $res_man = array();
    $rows_man = getResult($query_man, $res_man);
    $acts_ym = substr($str_ym, 2, 4);                                          // ��¤��������ΰ٤γ���ǯ��
    $acte_ym = substr($end_ym, 2, 4);                                          // ��¤��������ΰ٤ν�λǯ��ʷ軻�ʤΤ����Ϥ��줿���Ͻ�λǯ�����                
    for ($i=0; $i<$rows_man; $i++) {
        switch ($item[$i]) {
            case '���ץ�':                                                     // �оݥ��롼�פ����ץ�λ�
                $assist_c = $assistance_time[$i];
                $worker_time_c = $worker_time[$i];
                $total_worker_time += $worker_time[$i];                        // ��׺�Ȼ��ַ׻�
                $total_assistance_time += $assistance_time[$i];                // ��ױ�����ַ׻�
                // 176 �������μ���
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=176", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_c = $res_exp[0][0];
                // 176 Ǯ���ŵ�����
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=176 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_c = $expense_c - $res_exp2[0][0];
                }
                // 522 �������μ���
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=522", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_c += $res_exp[0][0];
                // 522 Ǯ���ŵ�����
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=522 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_c = $expense_c - $res_exp2[0][0];
                }
                // 523 �������μ���
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=523", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_c += $res_exp[0][0];
                // 523 Ǯ���ŵ�����
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=523 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_c = $expense_c - $res_exp2[0][0];
                }
                // 525 �������μ���
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=525", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_c += $res_exp[0][0];
                // 525 Ǯ���ŵ�����
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=525 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_c = $expense_c - $res_exp2[0][0];
                }
                // 510 �������μ���
                //$query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=510", $acts_ym, $acte_ym);
                //$res_exp = array();
                //$rows_exp = getResult($query_exp, $res_exp);
                //$expense_c += $res_exp[0][0];
                // 510 Ǯ���ŵ�����
                //$query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=510 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                //$res_exp2 = array();
                //$rows_exp2 = getResult($query_exp2, $res_exp2);
                //if ($res_exp2[0][0] != '') {
                //    $expense_c = $expense_c - $res_exp2[0][0];
                //}
                // 571 �������μ���
                //$query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=571", $acts_ym, $acte_ym);
                //$res_exp = array();
                //$rows_exp = getResult($query_exp, $res_exp);
                //$expense_c += $res_exp[0][0];
                // 571 Ǯ���ŵ�����
                //$query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=571 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                //$res_exp2 = array();
                //$rows_exp2 = getResult($query_exp2, $res_exp2);
                //if ($res_exp2[0][0] != '') {
                //    $expense_c = $expense_c - $res_exp2[0][0];
                //}
                break;
            case '��˥�':                                                     //�оݥ��롼�פ���˥��λ�
                $assist_l = $assistance_time[$i];
                $worker_time_l = $worker_time[$i];
                $total_worker_time += $worker_time[$i];                        // ��׺�Ȼ��ַ׻�
                $total_assistance_time += $assistance_time[$i];                // ��ױ�����ַ׻�
                // 175 �������μ���
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=175", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_l = $res_exp[0][0];
                // 175 Ǯ���ŵ�����
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=175 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_l = $expense_l - $res_exp2[0][0];
                }
                // 560 �������μ���
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=560", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_l += $res_exp[0][0];
                // 560 Ǯ���ŵ�����
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=560 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_l = $expense_l - $res_exp2[0][0];
                }
                // 551 �������μ���
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=551", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_l += $res_exp[0][0];
                // 551 Ǯ���ŵ�����
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=551 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_l = $expense_l - $res_exp2[0][0];
                }
                // 572 �������μ���
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=572", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_l += $res_exp[0][0];
                // 572 Ǯ���ŵ�����
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=572 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_l = $expense_l - $res_exp2[0][0];
                }
                
                //if ($end_ym < 201012) {
                //    $query_exp = sprintf("SELECT sum(act_monthly)-(SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=559 AND actcod=7531 AND aucod=10) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=559", $acts_ym, $acte_ym, $acts_ym, $acte_ym);
                //    $res_exp = array();
                //    $rows_exp = getResult($query_exp, $res_exp);
                //    $expense_l += $res_exp[0][0];
                //}
                break;
            default:
                break;
        }
    }
    $total_expense = $expense_c + $expense_l;                                  // �����¤����׻�
    
    $result->add('total_assistance_time', $total_assistance_time);
    $result->add('total_worker_time', $total_worker_time);
    $result->add('worker_time_c', $worker_time_c);
    $result->add('worker_time_l', $worker_time_l);    
    $result->add('assist_c', $assist_c);
    $result->add('assist_l', $assist_l);
    $result->add('total_expense', $total_expense);
    $result->add('expense_c', $expense_c);
    $result->add('expense_l', $expense_l);
}

////////////////// ��Ψ�Ȳ���̤�HTML�κ���
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<script type='text/javascript' src='../assemblyRate_reference.js'></script>\n";
    $listTable .= "<link rel='stylesheet' href='../assemblyRate_reference.css' type='text/css'>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
        $end_m  = substr($end_ym, 4, 2);
        $end_m  = $end_m + 1 - 1;
        $str_m  = substr($str_ym, 4, 2);
        $str_m  = $str_m + 1 - 1;
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
        $end_m  = substr($end_ym, 4, 2);
        $end_m  = $end_m + 1 - 1;
        $str_m  = substr($str_ym, 4, 2);
        $str_m  = $str_m + 1 - 1;
    }
    $res_g                 = $result->get_array2('res_g');
    $group_no              = $result->get_array2('group_no');
    $group_lease_sen       = $result->get_array2('group_lease_sen');
    $group_capital_sen     = $result->get_array2('group_capital_sen');
    $group_repair_sen      = $result->get_array2('group_repair_sen');
    $group_expenses        = $result->get_array2('group_expenses');
    $group_time            = $result->get_array2('group_time');
    $group_machine_rate    = $result->get_array2('group_machine_rate');
    $before_machine_rate   = $result->get_array2('before_machine_rate');
    $rate                  = $result->get_array2('rate');
    $worker_figure_s       = $result->get_array2('worker_figure_s');
    $worker_figure_p       = $result->get_array2('worker_figure_p');
    $man_expenses          = $result->get_array2('man_expenses');
    $standard_rate         = $result->get_array2('standard_rate');
    $group_mac_no          = $result->get_array2('group_mac_no');
    
    $direct_expenses       = $result->get('direct_expenses');
    $total_rate            = $result->get('total_rate');
    $expense_c_sen         = $result->get('expense_c_sen');
    $expense_l_sen         = $result->get('expense_l_sen');
    $assist_expense        = $result->get('assist_expense');
    $total_keihi           = $result->get('total_keihi');
    $total_keihi_cut       = $result->get('total_keihi_cut');
    $total_keihi_c         = $result->get('total_keihi_c');
    $total_keihi_l         = $result->get('total_keihi_l');
    $labor_rate            = $result->get('labor_rate');
    $labor_rate_c          = $result->get('labor_rate_c');
    $labor_rate_l          = $result->get('labor_rate_l');
    $total_assemble        = $result->get('total_assemble');
    
    $total_assistance_time = $result->get('total_assistance_time');
    $total_worker_time     = $result->get('total_worker_time');
    $assist_expense_c      = $result->get('assist_expense_c');
    $assist_expense_l      = $result->get('assist_expense_l');
    $direct_expenses_c     = $result->get('direct_expenses_c');
    $direct_expenses_l     = $result->get('direct_expenses_l');
    $man_expenses_c        = $result->get('man_expenses_c');
    $man_expenses_l        = $result->get('man_expenses_l');
    $keihi_cut_c           = $result->get('keihi_cut_c');
    $keihi_cut_l           = $result->get('keihi_cut_l');
    $assemble_c            = $result->get('assemble_c');
    $assemble_l            = $result->get('assemble_l');
    $assist_c              = $result->get('assist_c');
    $assist_l              = $result->get('assist_l');
    $before_labor_rate_t   = $result->get('before_labor_rate_t');
    $before_labor_rate_c   = $result->get('before_labor_rate_c');
    $before_labor_rate_l   = $result->get('before_labor_rate_l');
    $worker_time_c         = $result->get('worker_time_c');
    $worker_time_l         = $result->get('worker_time_l');
    
    $listTable .= "    <table x:str border=0 cellpadding=0 cellspacing=0 width=664 class=border-none style='border-collapse:collapse;table-layout:fixed;width:559pt'>\n";
    $listTable .= "        <col class=border-none width=16 style='mso-width-source:userset;mso-width-alt:512;width:0pt'>\n";
    $listTable .= "        <col class=border-none width=16 style='mso-width-source:userset;mso-width-alt:512;width:49pt'>\n";
    $listTable .= "        <col class=border-none width=88 style='mso-width-source:userset;mso-width-alt:2816;width:50pt'>\n";
    $listTable .= "        <col class=border-none width=32 style='mso-width-source:userset;mso-width-alt:1024;width:35pt'>\n";
    $listTable .= "        <col class=border-none width=69 style='mso-width-source:userset;mso-width-alt:2208;width:50pt'>\n";
    $listTable .= "        <col class=border-none width=81 style='mso-width-source:userset;mso-width-alt:2592;width:50pt'>\n";
    $listTable .= "        <col class=border-none width=68 style='mso-width-source:userset;mso-width-alt:2176;width:38pt'>\n";
    $listTable .= "        <col class=border-none width=43 style='mso-width-source:userset;mso-width-alt:1376;width:50pt'>\n";
    $listTable .= "        <col class=border-none width=76 style='mso-width-source:userset;mso-width-alt:2432;width:47pt'>\n";
    $listTable .= "        <col class=border-none width=89 style='mso-width-source:userset;mso-width-alt:2848;width:68pt'>\n";
    $listTable .= "        <col class=border-none width=89 style='mso-width-source:userset;mso-width-alt:2848;width:62pt'>\n";
    $listTable .= "        <col class=border-none width=102 style='mso-width-source:userset;mso-width-alt:3264;width:63pt'>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none width=16 style='height:15.0pt;width:12pt'></td>\n";
    $listTable .= "            <br>\n";
    $listTable .= "            <td colspan=4 class=border-none width=273><font size = 4><B>��Ω��ư������Ψ�׻�</B></font></td>\n";
    $listTable .= "            <td colspan=7 class=border-none><B><font size = 4>". format_date6_ki($end_ym) . "�����ӡ�{$str_m} ��� {$end_m} ���</font></B></td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td class=border-none colspan=12 align=right>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td colspan=2 class=border-on align=center style='border-bottom:none'>���롼��̾</td>\n";
    $listTable .= "            <td class=border-on rowspan=2 style='border-bottom:none'>��</td>\n";
    $listTable .= "            <td class=border-on align=right style='border-bottom:none'>���</td>\n";
    $listTable .= "            <td class=border-on style='border-bottom:none'>���ӡ�ʬ</td>\n";
    $listTable .= "            <td class=border-on align=right style='border-bottom:none'>��</td>\n";
    $listTable .= "            <td class=pt9 colspan=2>���Ӻ�ȼԷ���(���)</td>\n";
    $listTable .= "            <td class=machine_rate_title align=center style='border-top:1.0pt solid windowtext'><B>". format_date6_ki($end_ym) . "</B></td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none'>". format_ki_before($end_ym) . "</td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none'>". format_date6_term($end_ym) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td colspan=2 class=border-on align=center style='border-bottom:none;border-top:none'>�����ֹ�</td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none;border-top:none'>ľ�ܷ���</td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none;border-top:none'>��Ư����</td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none;border-top:none'>��Ψ</td>\n";
    $listTable .= "            <td class=pt9 colspan=2 style='border-top:none'>��ȼԿ�(�Ұ�/�ѡ���)</td>\n";
    $listTable .= "            <td class=machine_rate_title align=center><B>�º���Ψ</B></td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none;border-top:none'>�º���Ψ</td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none;border-top:none'>ɸ����Ψ</td>\n";
    $listTable .= "        </tr>\n";
    for ($r=0; $r<$result->get('rows_g'); $r++) {
        $g_num = $res_g[$r][0];
        $listTable .= "    <tr>\n";
        for ($i=0; $i<$result->get('rows_g'); $i++) {         // �쥳���ɿ�ʬ���֤�
            if ($g_num == $group_no[$i]) {
                $listTable .= "<tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
                $listTable .= "    <td height=20 class=border-none style='height:15.0pt'></td>\n";
                $listTable .= "    <td rowspan=1 colspan=2 class=border-on align=center valign=middle style='border-bottom:none'>". format_number_name($group_no[$i], $res_g, $result->get('rows_g')) . "</td>\n";
                $listTable .= "    <td class=border-on style='border-bottom:none'><center>��</center></td>\n";
                $listTable .= "    <td class=border-on align=right style='border-bottom:none'>". number_format($group_lease_sen[$i], 0) . "</td>\n";
                $listTable .= "    <td class=border-on rowspan=3 style='border-bottom:none'>��</td>\n";
                $listTable .= "    <td class=border-on rowspan=3 style='border-bottom:none'>��</td>\n";
                $listTable .= "    <td class=border-on rowspan=3 style='border-right:none;border-bottom:none'>��</td>\n";
                $listTable .= "    <td class=border-on align=right style='border-left:none;border-bottom:none'>". number_format($man_expenses[$i], 0) . "</td>\n";
                $listTable .= "    <td class=machine_rate rowspan=3 style='border-top:1.0pt solid windowtext'>��</td>\n";
                $listTable .= "    <td class=border-on rowspan=3 style='border-bottom:none'>��</td>\n";
                $listTable .= "    <td class=border-on rowspan=3 style='border-bottom:none'>��</td>\n";
                $listTable .= "</tr>\n";
                $listTable .= "<tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
                $listTable .= "    <td height=20 class=border-none style='height:15.0pt'></td>\n";
                if (isset($group_mac_no[$i][0])) {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'>{$group_mac_no[$i][0]}</td>\n";
                } else {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'></td>\n";    
                }
                $listTable .= "    <td class=border-none><center>��</center></td>\n";
                $listTable .= "    <td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($group_capital_sen[$i], 0) . "</td>\n";
                $listTable .= "    <td class=border-none style='border-right:1.0pt solid windowtext'></td>\n";
                $listTable .= "</tr>\n";
                $listTable .= "<tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
                $listTable .= "    <td height=20 class=border-none style='height:15.0pt'></td>\n";
                if (isset($group_mac_no[$i][1])) {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'>{$group_mac_no[$i][1]}</td>\n";
                } else {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'></td>\n";    
                }
                    $listTable .= "<td class=border-none><center>��</center></td>\n";
                    $listTable .= "<td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($group_repair_sen[$i], 0) . "</td>\n";
                    $listTable .= "<td class=border-none style='border-right:1.0pt solid windowtext'></td>\n";
                $listTable .= "</tr>\n";
                $listTable .= "<tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
                $listTable .= "    <td height=20 class=border-none style='height:15.0pt'></td>\n";
                if (isset($group_mac_no[$i][2])) {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'>{$group_mac_no[$i][2]}</td>\n";
                } else {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'></td>\n";    
                }
                $listTable .= "    <td class=border-on style='border-top:.5pt dotted windowtext;border-bottom:none'><center>��</center></td>\n";
                $listTable .= "    <td class=border-on align=right style='border-top:.5pt dotted windowtext;border-bottom:none'>". number_format($group_expenses[$i], 0) . "</td>\n";
                $listTable .= "    <td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($group_time[$i], 0) . "</td>\n";
                $listTable .= "    <td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($rate[$i], 2) . "</td>\n";
                $listTable .= "    <td class=border-none>". number_format($worker_figure_s[$i], 2) . "�� /</td>\n";
                $listTable .= "    <td class=border-none>". number_format($worker_figure_p[$i], 2) . "��</td>\n";
                if ($group_machine_rate[$i] == 0) {
                    $listTable .= "<td class=machine_rate align=right><B>---</B></td>\n";
                } else {
                    $listTable .= "<td class=machine_rate align=right><B>". number_format($group_machine_rate[$i], 2) . "</B></td>\n";
                }
                if ($before_machine_rate[$r] == 0 || $before_machine_rate[$r] == "") {
                    $listTable .= "<td class=border-on align=right style='border-top:none;border-bottom:none'>---</td>\n";
                } else {
                    $listTable .= "<td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($before_machine_rate[$r], 2) . "</td>\n";
                }
                if ($standard_rate[$i] == 0) {
                    $listTable .= "<td class=border-on align=right style='border-top:none;border-bottom:none'>---</td>\n";
                } else {
                    $listTable .= "<td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($standard_rate[$i], 2) . "</td>\n";
                }
                $listTable .= "</tr>\n";
            }
        }
            $listTable .= "</tr>\n";
    }
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td rowspan=4 colspan=2 class=border-on align=center valign=middle>���</td>\n";
    $listTable .= "            <td class=border-on style='border-bottom:none'><center>��</center></td>\n";
    $listTable .= "            <td class=border-on align=right style='border-bottom:none'>". number_format($request->get('total_lease'), 0) . "</td>\n";
    $listTable .= "            <td class=border-on rowspan=3 style='border-bottom:none'>��</td>\n";
    $listTable .= "            <td class=border-on rowspan=3 style='border-bottom:none'>��</td>\n";
    $listTable .= "            <td class=border-on rowspan=4 style='border-right:none'>��</td>\n";
    $listTable .= "            <td class=man_expense align=right style='border-left:none;border-top:1.0pt solid windowtext;border-right:1.0pt solid windowtext'>". number_format($result->get('total_man_expenses'), 0) . "</td>\n";
    $listTable .= "            <td class=border-on rowspan=4 colspan=3>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none><center>��</center></td>\n";
    $listTable .= "            <td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($request->get('total_capital'), 0) . "</td>\n";
    $listTable .= "            <td class=border-none rowspan=2 style='border-right:1.0pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none><center>��</center></td>\n";
    $listTable .= "            <td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($request->get('total_repair'), 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-on align=center style='border-top:.5pt dotted windowtext'>��</td>\n";
    $listTable .= "            <td class=direct_expense align=right align=right style='border-top:.5pt dotted black;border-bottom:1.0pt solid windowtext;border-left:1.0pt solid windowtext'>". number_format($direct_expenses, 0) . "</td>\n";
    $listTable .= "            <td class=border-on align=right style='border-top:none'>". number_format($request->get('total_time'), 0) . "</td>\n";
    $listTable .= "            <td class=border-on align=right style='border-top:none'>". number_format($total_rate, 2) . "</td>\n";
    $listTable .= "            <td class=border-on style='border-top:none;border-left:none'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr class='pagebreak'></tr>\n";
    $listTable .= "        <tr height=30 style='height:20.0pt'>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none width=16 style='height:15.0pt;width:12pt'></td>\n";
    $listTable .= "            <td colspan=4 class=border-none width=273><font size = 4><B>��Ω���Ȥ���Ψ�׻�</B></font></td>\n";
    $listTable .= "            <td colspan=7 class=border-none><B><font size = 4>". format_date6_ki($end_ym) . "�����ӡ�{$str_m} ��� {$end_m} ���</font></B></td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=machine_rate_title style='border-left:none'>����</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>����Ω����(���)</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($expense_c_sen, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>����Ω����</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($expense_l_sen, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>����������û�</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($assist_expense, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3 align=right style='border-top:1.0pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($total_keihi, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>��ư��Ω��ľ�ܷ��񸺳�</td>\n";
    $listTable .= "            <td class=direct_expense align=right>". number_format(-$direct_expenses, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>��ư��Ω����ȷ��񸺳�</td>\n";
    $listTable .= "            <td class=man_expense align=right>". number_format(-$result->get('total_man_expenses'), 0) . "</td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=labor_rate_title align=center><B>". format_date6_ki($end_ym) . "�º���Ψ</B></td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>���</td>\n";
    $listTable .= "            <td class=border-none align=right style='border-top:1.0pt solid windowtext'>". number_format($total_keihi_cut, 0) . "</td>\n";
    $listTable .= "            <td class=border-none align=center>��</td>\n";
    $listTable .= "            <td class=border-none>����</td>\n";
    $listTable .= "            <td class=labor_rate align=right>". number_format($labor_rate, 2) . "</td>\n";
    $listTable .= "            <td class=border-none>�ߡ�ʬ</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>��Ω��Ȼ��ַ�</td>\n";
    $listTable .= "            <td class=assemble align=right>". number_format($total_worker_time, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>��</td>\n";
    /*
    $listTable .= "            <td class=assemble align=right>". number_format($total_assemble, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>ʬ</td>\n";
    */
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=border-on align=center>". format_date6_ki($request->get('before_ym')) . "�º���Ψ</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    /*
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>���������ֲû�</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($total_assistance_time, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>ʬ</td>\n";
    $listTable .= "            <td></td>\n";
    */
    $listTable .= "            <td height=20 style='height:15.0pt'></td>\n";
    $listTable .= "            <td align=right colspan=3 style='border-top:1.0pt solid windowtext'>��</td>\n";
    $listTable .= "            <td align=right style='border-top:1.0pt solid windowtext'>��</td>\n";
    $listTable .= "            <td>��</td>\n";
    $listTable .= "            <td></td>\n";
    if ($before_labor_rate_t == "") {
        $listTable .= "        <td class=border-on align=right>---</td>\n";
    } else {
        $listTable .= "        <td class=border-on align=right>". number_format($before_labor_rate_t, 2) . "</td>\n";
    }
    $listTable .= "            <td class=border-on style='border-right:none;border-bottom:none'>�ߡ�ʬ</td>\n";
    $listTable .= "        </tr>\n";
    /*
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>���</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($total_worker_time, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>��</td>\n";
    $listTable .= "        </tr>\n";
    */
    $listTable .= "        <tr height=20 style='height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "           <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=machine_rate_title style='border-left:none'>���ץ�</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>����Ω����(���)</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($expense_c_sen, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=2>����Ω����</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>����������û�</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($assist_expense_c, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($total_keihi_c, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>��ư��Ω��ľ�ܷ��񸺳�</td>\n";
    $listTable .= "            <td class=direct_expense align=right>". number_format(-$direct_expenses_c, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>��ư��Ω����ȷ��񸺳�</td>\n";
    $listTable .= "            <td class=man_expense align=right>". number_format(-$man_expenses_c, 0) . "</td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=labor_rate_title align=center><B>". format_date6_ki($end_ym) . "�º���Ψ</B></td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>���</td>\n";
    $listTable .= "            <td class=border-none align=right style='border-top:1.0pt solid windowtext'>". number_format($keihi_cut_c, 0) . "</td>\n";
    $listTable .= "            <td class=border-none align=center>��</td>\n";
    $listTable .= "            <td class=border-none>�¡�</td>\n";
    $listTable .= "            <td class=labor_rate align=right>". number_format($labor_rate_c, 2) . "</td>\n";
    $listTable .= "            <td class=border-none>�ߡ�ʬ</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>��Ω��Ȼ��ַ�</td>\n";
    $listTable .= "            <td class=assemble align=right>". number_format($worker_time_c, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>��</td>\n";
    /*
    $listTable .= "            <td class=assemble align=right>". number_format($assemble_c, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>ʬ</td>\n";
    */
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=border-on align=center>". format_date6_ki($request->get('before_ym')) . "�º���Ψ</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 style='height:15.0pt'></td>\n";
    $listTable .= "            <td colspan=3 style='border-top:1.0pt solid windowtext'>��</td>\n";
    $listTable .= "            <td align=right style='border-top:1.0pt solid windowtext'>��</td>\n";
    $listTable .= "            <td>��</td>\n";
    $listTable .= "            <td></td>\n";
    /*
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>���������ֲû�</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($assist_c, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>ʬ</td>\n";
    $listTable .= "            <td></td>\n";
    */
    if ($before_labor_rate_c == "") {
        $listTable .= "        <td class=border-on align=right>---</td>\n";
    } else {
        $listTable .= "        <td class=border-on align=right>". number_format($before_labor_rate_c, 2) . "</td>\n";
    }
    $listTable .= "            <td class=border-on style='border-right:none;border-bottom:none'>�ߡ�ʬ</td>\n";
    $listTable .= "        </tr>\n";
    /*
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>���</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($worker_time_c, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>��</td>\n";
    $listTable .= "        </tr>\n";
    */
    $listTable .= "        <tr height=20 style='height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=machine_rate_title style='border-left:none'>��˥�</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>����Ω����(���)</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>����Ω����</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($expense_l_sen, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>����������û�</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($assist_expense_l, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($total_keihi_l, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>��ư��Ω��ľ�ܷ��񸺳�</td>\n";
    $listTable .= "            <td class=direct_expense align=right>". number_format(-$direct_expenses_l, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>��ư��Ω����ȷ��񸺳�</td>\n";
    $listTable .= "            <td class=man_expense align=right>". number_format(-$man_expenses_l, 0) . "</td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=labor_rate_title align=center><B>". format_date6_ki($end_ym) . "�º���Ψ</B></td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>���</td>\n";
    $listTable .= "            <td class=border-none align=right style='border-top:1.0pt solid windowtext'>". number_format($keihi_cut_l, 0) . "</td>\n";
    $listTable .= "            <td class=border-none align=center>��</td>\n";
    $listTable .= "            <td class=border-none>�á�</td>\n";
    $listTable .= "            <td class=labor_rate align=right>". number_format($labor_rate_l, 2) . "</td>\n";
    $listTable .= "            <td class=border-none>�ߡ�ʬ</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>��Ω��Ȼ��ַ�</td>\n";
    $listTable .= "            <td class=assemble align=right>". number_format($worker_time_l, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>��</td>\n";
    /*
    $listTable .= "            <td class=assemble align=right>". number_format($assemble_l, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>ʬ</td>\n";
    */
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=border-on align=center>". format_date6_ki($request->get('before_ym')) . "�º���Ψ</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td colspan=3 style='border-top:1.0pt solid windowtext'>��</td>\n";
    $listTable .= "            <td align=right style='border-top:1.0pt solid windowtext'>��</td>\n";
    $listTable .= "            <td>��</td>\n";
    /*
    $listTable .= "            <td class=border-none colspan=3>���������ֲû�</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($assist_l, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>ʬ</td>\n";
    */
    $listTable .= "            <td></td>\n";
    if ($before_labor_rate_l == "") {
        $listTable .= "        <td class=border-on align=right>---</td>\n";
    } else {
        $listTable .= "        <td class=border-on align=right>". number_format($before_labor_rate_l, 2) . "</td>\n";
    }
    $listTable .= "            <td class=border-on style='border-right:none;border-bottom:none'>�ߡ�ʬ</td>\n";
    $listTable .= "        </tr>\n";
    /*
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>���</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($worker_time_l, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>��</td>\n";
    $listTable .= "        </tr>\n";
    */
    $listTable .= "    </table>\n";
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

////////////////// ��ȼԿ���ɸ����Ψ�����ϲ��̤κ���
function getInputHTMLbody($request, $menu, $result)
{
    $res_g                = $result->get_array2('res_g');
    $be_worker_figure_s   = $result->get_array2('be_worker_figure_s');
    $be_worker_figure_p   = $result->get_array2('be_worker_figure_p');
    $before_standard_rate = $result->get_array2('before_standard_rate');
    $be_worker_rate_s     = $result->get('be_worker_rate_s');
    $be_worker_rate_p     = $result->get('be_worker_rate_p');
    
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<script type='text/javascript' src='../assemblyRate_reference.js'></script>\n";
    $listTable .= "<link rel='stylesheet' href='../assemblyRate_reference.css' type='text/css'>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <form name='entry_form' action='../assemblyRate_reference_Main.php' method='post' onSubmit='return chk_entry(this)' target='_parent'>\n";
    $listTable .= "    <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td colspan=4 rowspan=3 bgcolor='#ffffc6' align='center'>\n";
    $listTable .= "                    ��ȼԿ���ɸ����Ψ����Ͽ<BR>\n";
    $listTable .= "                    <font color='red'>\n";
    $listTable .= "                    ������ͤ�����Υǡ�������Ͽ���򤬤���Ф��Υǡ�����ɽ�����ޤ���<BR>\n";
    $listTable .= "                    ������̤��Ͽ������ʤ��ξ��϶����\n";
    $listTable .= "                    </font>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr></tr>\n";
    $listTable .= "            <tr></tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th rowspan=2 nowrap>���롼��̾</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>��ȼԿ�<BR>(�Ұ�)</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>��ȼԿ�<BR>(�ѡ���)</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>ɸ����Ψ<BR>(��)</th>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <tr>\n";
    for ($i=0; $i<$result->get('rows_g'); $i++) {
        $listTable .= "            <tr>\n";
        $listTable .= "                <td class='winbox' nowrap align='left'><div class='pt9'>{$res_g[$i][1]}</div></td>\n";
        $listTable .= "                <td class='winbox' align='center'><input type='text' class='price_font' name='worker_figure_s[". $i . "]' value='{$be_worker_figure_s[$i]}' size='15'></td>\n";
        $listTable .= "                <td class='winbox' align='center'><input type='text' class='price_font' name='worker_figure_p[". $i . "]' value='{$be_worker_figure_p[$i]}' size='15'></td>\n";
        $listTable .= "                <td class='winbox' align='center'><input type='text' class='price_font' name='standard_rate[". $i ."]' value='{$before_standard_rate[$i]}' size='15'></td>\n";
        $listTable .= "           </tr>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th rowspan=2 nowrap>��</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>��ȼ���Ψ<BR>(�Ұ�)</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>��ȼ���Ψ<BR>(�ѡ���)</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>��</th>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr></tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td>��</td>\n";
    $listTable .= "                    <td class='winbox' align='center'><input type='text' class='price_font' name='worker_rate_s' value='{$be_worker_rate_s}' size='15'></td>\n";
    $listTable .= "                    <td class='winbox' align='center'><input type='text' class='price_font' name='worker_rate_p' value='{$be_worker_rate_p}' size='15'></td>\n";
    $listTable .= "                <td>��</td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td colspan=4 class='winbox' align='center'>\n";
    $listTable .= "                    <input type='submit' class='entry_font' name='entry' value='��Ͽ'>\n";
    $listTable .= "                    <input type='hidden' name='rows_g' value='". $result->get('rows_g') . "'>\n";
    $listTable .= "                    <input type='hidden' name='end_ym' value='". $request->get('end_ym') . "'>\n";
    $listTable .= "                    <input type='hidden' name='str_ym' value='". $request->get('str_ym') . "'>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "        </TBODY>\n";
    $listTable .= "        </table>\n";
    $listTable .= "        </td></tr>\n";
    $listTable .= "    </table>\n";
    $listTable .= "    </form>\n";
    $listTable .= "    </center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

////////////////// ��Ψ�Ȳ���̤�HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '�Ȳ�');
    ////////////// HTML�ե��������
    $file_name = "list/assemblyRate_reference_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}

////////////////// ��ȼԿ���ɸ����Ψ�����ϲ��̤����
function outInputHTML($request, $menu, $result)
{
    $listHTML = getInputHTMLbody($request, $menu, $result);
    $request->add('view_flg', '����');
    ////////////// HTML�ե��������
    $file_name = "list/assemblyRate_workerInput_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}

////////////////// �����Ǥ�դ����դ������'����'�ե����ޥåȤ����֤���
function format_ki_before($date6)
{
    if (0 == $date6) {
        $date6 = '--------';    
    }
    if ($date6 < 200000) {
        $date6 = '--------';    
    }
    if (6 == strlen($date6)) {
        $nen   = substr($date6, 0, 4);
        $tsuki = substr($date6, 4, 2);
        if (1 == $tsuki) {
            $nen   = $nen - 1;
            $tsuki = 12;
        } else {
            $tsuki = $tsuki - 1;
        }
    }
    if (6 == strlen($date6)) {
        $ki    = substr($nen, 2, 2);
        if (0 < $tsuki && $tsuki < 4) {
            return "��" . $ki . "��" . $tsuki . "��";
        } else {
            $ki = $ki + 1;
            return "��" . $ki . "��" . $tsuki . "��";
        }
    } else {
        return FALSE;
    }
}

////////////////// ���롼���ֹ�򥰥롼��̾���Ѵ�
function format_number_name($number, $res_nn, $rows_nn)
{
    for ($n=0; $n<$rows_nn; $n++) {
        if ($res_nn[$n][0] == $number) {
            $group_name = $res_nn[$n][1];
            return $group_name;
        }
    }
}

?>
