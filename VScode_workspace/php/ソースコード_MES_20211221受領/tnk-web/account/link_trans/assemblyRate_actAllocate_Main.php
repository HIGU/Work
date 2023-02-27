<?php
//////////////////////////////////////////////////////////////////////////////
// ����������Ψ �Ȳ� main�� assemblyRate_actAllocate_Main.php               //
//                          (�� indirect_cost_allocate.php)                 //
// Copyright (C) 2007-2014 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/06 Created  assemblyRate_reference_Main.php                      //
// 2007/12/13 ;ʬ��font�����κ�� �����Ȥΰ���Ĵ��                       //
// 2007/12/29 ���դν���ͤ�������ɲ�                                      //
//            �����̤������軻�������о�ǯ������̤����򤷤����դ��֤�  //
//            �褦���ѹ�                                                    //
// 2008/01/10 ɽ������;ʬ�ʥ����κ��                                      //
// 2008/05/09 ɽ�����ܡ�����������Ĵ��                                      //
// 2009/04/10 ��������˥����������559�ˤ��ɲ�                             //
// 2010/02/04 ��¤����μ����ߤȥ����ӥ�������¤����������Ԥ�ʤ���  //
//            ����������ʤ��褦���ѹ�                                      //
// 2010/03/03 ��ξ�郎�������������ä��Τ�Ĵ��                            //
//            ��ǯ���ɽ����Ĵ����substr�θ��+1-1���ƿ����ˤ���0��ä�     //
// 2010/12/09 ��̳Ĵ����Ŧ�ˤ�ꡢ��˥�����(559)���� 2010/12��           //
// 2011/06/22 format_date�Ϥ�tnk_func�˰�ư�Τ��ᤳ�������               //
// 2014/04/11 2014/04����ȿ��ѹ��ΰ١�������Ĵ��                           //
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
    $menu = new MenuHeader(0);                       // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
       
    ////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('����������Ψ�ξȲ�');
    
    $request = new Request;
    $result  = new Result;
    
    if ($request->get('end_ym') !== '') {
        ////// �꥿���󥢥ɥ쥹����
        $menu->set_RetUrl($_SESSION['wage_referer'] . '?wage_ym=' . $request->get('end_ym'));
    } else {
        ////// �꥿���󥢥ɥ쥹����
        $menu->set_RetUrl($_SESSION['wage_referer'] . '?wage_ym=' . $request->get('wage_ym'));
    }
    
    request_check($request, $result, $menu);         // ������ʬ�������å�
    
    calculation_branch($request, $result, $menu);    // ����Ψ�׻���ʬ��
    
    display($menu, $request, $result);               // ����ɽ��
}

////////////// ����ɽ��
function display($menu, $request, $result)
{       
    ////////// �֥饦�����Υ���å����к���
    $uniq = 'id=' . $menu->set_useNotCache('target');
    
    ////////// ��å��������ϥե饰
    $msg_flg = 'site';

    ob_start('ob_gzhandler');                        // ���ϥХåե���gzip����
    
    ////////// HTML Header ����Ϥ��ƥ���å��������
    $menu->out_html_header();
 
    ////////// View�ν���
    require_once ('assemblyRate_actAllocate_View.php');

    ob_end_flush(); 
}

////////////// ������ʬ����Ԥ�
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('delete') != '') $ok = actAllocate_delete($request);
    if ($request->get('input') != '')  $ok = actAllocate_input($request, $result);
    if ($ok) {
        ////// �ǡ����ν����
        $str_ym = '';                   // ������
        $end_ym = '';                   // ��λ��
        $tan_str_ym = '';               // ��ͳ�����λ��γ�����
        $tan_end_ym ='';                // ��ͳ�����λ��ν�λ��
        $request->add('delete', '');    // �����
        $request->add('input', '');     // �����
        if ($request->get('wage_ym') !== '') {
            $request->add('end_ym', $request->get('wage_ym'));    // ����ͤν�λǯ�������
            $nen   = substr($request->get('wage_ym'), 0, 4);
            $tsuki = substr($request->get('wage_ym'), 4, 2);
            if (($tsuki < 10) && (3 < $tsuki)) {                    // ����ͤγ���ǯ�������
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

////////////// ����Ψ�׻���ʬ��
function calculation_branch($request, $result, $menu)
{
    $request->add('view_flg', '');                                     // �Ȳ����ɽ���Υե饰�����
    if ($request->get('tangetu') != '') {
        $request->add('rate_register', '��Ͽ');                        // ñ��ξ������׻���Ԥ���
        $request->add('kessan', '');
    }
    if ($request->get('kessan') != '') {
        $request->add('tangetu', '');
    }
    if ($request->get('kessan') != '' || $request->get('tangetu') != '') {
        if (!registered_data_check($request, $result)) {
            return;
        } else {
            if(!get_registered_data($request, $result)) {              // ��Ͽ�Ѥߥǡ����μ���
                assembly_actAllocate_cal($request, $result, $menu);    // ��Ψ�׻��ؿ��θƽ�
            }
            outViewListHTML($request, $menu, $result);                 // ��Ψ�Ȳ���̤�HTML�����
        }
    }
}

////////////// ɬ�פʥǡ�������Ͽ����Ƥ��뤫�����å�
function registered_data_check($request, $result)    //����Ψ�׻���ʬ��
{
    if ($request->get('kessan') != '') {
        $chk_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $chk_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    for ($chk_ym; $end_ym >= $chk_ym; $chk_ym++) {
        $chk_nen   = substr($chk_ym, 0, 4);             // �����å���ǯ
        $chk_tsuki = substr($chk_ym, 4, 2);             // �����å��ѷ�
        if ($chk_tsuki == 13) {                         // �13�ˤʤä���ǯ������夬�äƷ�򣰣���
            $chk_nen   = $chk_nen + 1;
            $chk_tsuki = '01';
            $chk_ym = $chk_nen . $chk_tsuki;
        }
        $chk_ym4 = substr($end_ym, 2, 4);
        $query = sprintf("SELECT * FROM act_summary WHERE act_yymm=%d", $chk_ym4);
        if ( ($rows=getResult($query, $res)) > 0) {    // ��Ͽ�ѤߤΥ����å�
        } else {
            $_SESSION['s_sysmsg'] .= "���ν����������¤����μ����ߤ�ԤäƤ���¹Ԥ��Ƥ���������";    // .= �����
            $msg_flg = 'alert';
            return false;
        }
        $query = sprintf("SELECT external_price FROM indirect_cost_allocate WHERE total_date=%d AND external_price >= 0", $chk_ym);
        if ( ($rows=getResult($query, $res)) >= 2) {    // ��Ͽ�ѤߤΥ����å�
        } else {
            $_SESSION['s_sysmsg'] .= "���ν�������˳Ƽ�ǡ������Ϥ������Ψ�׻��ǡ��������Ϥ�ԤäƤ���������";    // .= �����
            $msg_flg = 'alert';
            return false;
        }
        $query = sprintf("SELECT * FROM service_percent_factory_expenses WHERE total_date=%d", $chk_ym);
        if ( ($rows=getResult($query, $res)) > 0) {    // ��Ͽ�ѤߤΥ����å�
        } else {
            $_SESSION['s_sysmsg'] .= "���ν�������˥����ӥ��������Ϥ�����¤����������ԤäƤ���������";    // .= �����
            $msg_flg = 'alert';
            return false;
        }
    }
    return true;
}

////////////// ���������Υǡ����κ�����å�
function actAllocate_delete ($request)
{
    $end_ym = $request->get('end_ym');
    $format_ym = '';
    $format_ym = format_date6_kan($end_ym);
    $query = sprintf("UPDATE indirect_cost_allocate SET indirect_cost=NULL WHERE total_date=%d", $end_ym);
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$format_ym}�γ������˼��ԡ�";        // .= �����
        $msg_flg = 'alert';
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "{$format_ym}�γ���������ޤ�����";    // .= �����
        return true;
    }
}

////////////// ������Υǡ�������Ͽ���å�
function actAllocate_input ($request, $result)
{
    if (getCheckAuthority(22)) {                    // ǧ�ڥ����å�
        $end_ym = $request->get('end_ym');
        $format_ym = '';
        $format_ym = format_date6_kan($end_ym);      // ɽ����ǯ���٥ե����ޥå�
        $acte_ym = substr($end_ym, 2, 4);           // ǯ��ǡ����Υե����ޥå�
        $c_indirect_cost        = number_format($request->get('c_indirect_cost'), 1);       // ���ץ鹩�����������Ψ
        $c_suppli_section_cost  = number_format($request->get('c_suppli_section_cost'), 1); // ���ץ�Ĵã����������Ψ
        $l_indirect_cost        = number_format($request->get('l_indirect_cost'), 1);       // ��˥��������������Ψ
        $l_suppli_section_cost  = number_format($request->get('l_suppli_section_cost'), 1); // ��˥�Ĵã����������Ψ 
        if($end_ym < 201012) { 
            $act_id       = array(518, 519, 520, 526, 527, 528, 556, 176, 510, 522, 523, 525, 551, 175, 560, 571, 572, 559);    //���祳����
        } elseif($end_ym < 201403) {
            $act_id       = array(518, 519, 520, 526, 527, 528, 556, 176, 510, 522, 523, 525, 551, 175, 560, 571, 572);    //���祳����
        } else {
            $act_id       = array(518, 519, 520, 527, 528, 547, 556, 176, 522, 523, 525, 551, 175, 560, 572);    //���祳����
        }
        $rows_act_id  = count($act_id); //�����
        $total_item   = array('����¤', '��������', '����Ω', '��������', '����Ω', '�Х����', '�ó���', '�ó�����', '�̳���', '����Х�');    //�������祰�롼��
        $rows_item    = count($total_item); //���������
        $query = sprintf("SELECT * FROM indirect_cost_allocate WHERE item='���ץ�' AND total_date=%d", $end_ym);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE����
            $query = sprintf("UPDATE indirect_cost_allocate SET indirect_cost='%s', suppli_section_cost='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='���ץ�' AND total_date=%d", $c_indirect_cost, $c_suppli_section_cost, $_SESSION['User_ID'], $end_ym);
            if (query_affected($query) <= 0) {
            }
        }
        $query = sprintf("SELECT * FROM indirect_cost_allocate WHERE item='��˥�' AND total_date=%d", $end_ym);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE����
            $query = sprintf("UPDATE indirect_cost_allocate SET indirect_cost='%s', suppli_section_cost='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='��˥�' AND total_date=%d", $l_indirect_cost, $l_suppli_section_cost, $_SESSION['User_ID'], $end_ym);
            if (query_affected($query) <= 0) {
            }
        }
        //��¤����������ñ����Ͽ�ѡ�
        for ($i=0; $i<$rows_act_id; $i++) {
            $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d AND act_id=%d", $acte_ym, $act_id[$i]);
            $res_exp = array();
            $rows_exp = getResult($query_exp, $res_exp);
            $tan_expense[$i] = $res_exp[0][0];
        }
        //������������ñ����Ͽ�ѡ�
        for ($i=0; $i<$rows_item; $i++) {
            $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date=%d AND total_item='%s'", $end_ym, $total_item[$i]);
            $res_ser = array();
            $rows_ser = getResult($query_ser, $res_ser);
            $tan_indirect[$i] = $res_ser[0][0];
        }
        for ($i=0; $i<$rows_act_id; $i++) {
            $query = sprintf("SELECT * FROM assyrate_section_expense WHERE act_id=%d AND total_date=%d", $act_id[$i], $end_ym);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE����
                $query = sprintf("UPDATE assyrate_section_expense SET section_expense=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE act_id=%d AND total_date=%d", $tan_expense[$i], $_SESSION['User_ID'], $act_id[$i], $end_ym);
                if (query_affected($query) <= 0) {
                }
            } else {    //��Ͽ�ʤ� INSERT ����
                $query = sprintf("INSERT INTO assyrate_section_expense (total_date, act_id, section_expense, last_date, last_user)
                         VALUES (%d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                         $end_ym, $act_id[$i], $tan_expense[$i], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                }
            }
        } 
        for ($i=0; $i<$rows_item; $i++) {
            $query = sprintf("SELECT * FROM assyrate_indirect_expense WHERE item='%s' AND total_date=%d", $total_item[$i], $end_ym);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE����
                $query = sprintf("UPDATE assyrate_indirect_expense SET indirect_expense=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='%s' AND total_date=%d", $tan_indirect[$i], $_SESSION['User_ID'], $total_item[$i], $end_ym);
                if (query_affected($query) <= 0) {
                }
            } else {    //��Ͽ�ʤ� INSERT ����
                $query = sprintf("INSERT INTO assyrate_indirect_expense (total_date, item, indirect_expense, last_date, last_user)
                         VALUES (%d, '%s', %d, CURRENT_TIMESTAMP, '%s')",
                         $end_ym, $total_item[$i], $tan_indirect[$i], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                }
            }
        }
        $_SESSION['s_sysmsg'] .= "{$format_ym}������Ψ����Ͽ���ޤ�����</font>";
    } else {    //ǧ�ڤʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�̵���١�DB�ι���������ޤ���Ǥ�����";
        return false;
    }
}

////////////// ��Ͽ�Ѥߥǡ����μ���
function get_registered_data($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $query = sprintf("SELECT * FROM indirect_cost_allocate WHERE total_date=%d", $end_ym);
    $res = array();
    $rows = getResult($query, $res);
    if ($res[0]['indirect_cost'] == '') {                                    // ��Ψ����Ͽ�Ѥߤ������å�
        ////// ���� ̤��Ͽ�ξ�����Ψ�׻��Υץ�����
        $request->add('rate_register', '��Ͽ');
        return false;
    } else if ($request->get('rate_register') == '��Ͽ') {                   // ñ��ɤ��������å�
        ////// ñ��ξ�����Ψ�׻��Υץ�����
        $request->add('rate_register', '��Ͽ');
        return false;
    } else {
        ////// ���򤢤�ξ�����Ͽ�ѤߤΥǡ��������������ǡ�����׻�����
        $request->add('rate_register', '�Ȳ�');
        get_various_data($request, $result);         // �Ƽ�ǡ�������
        act_expenses_cal($request, $result);     // ���������׻�
        act_indirect_cal($request, $result);     // �����ӥ����ǡ����׻�
        //get_act_expenses ($request, $result);        // ������������
        //get_act_indirect($request, $result);         // �����ӥ����ǡ�������
        get_indirect_cost($result, $request);        // �������������Ψ����
    }
    return true;
}

////////////// ������������
function get_act_expenses($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $manu_expenses = 0;
    $c_assembly_expense = 0;
    $l_assembly_expense = 0;
    $c_expense = 0;
    $total_direct_section = 0;
    ////////// ��¤���������
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=518", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_518', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=519", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_519', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=520", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_520', $res_exp[0][0]);
    if ($end_ym < 201404) {
        $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=526", $str_ym, $end_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_526', $res_exp[0][0]);
    } else {
        $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=547", $str_ym, $end_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_547', $res_exp[0][0]);
    }
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=527", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_527', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=528", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_528', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=556", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_556', $res_exp[0][0]);
    
    if ($end_ym < 201404) {
        $manu_expenses = $result->get('expenses_518') + $result->get('expenses_519') + $result->get('expenses_520') + $result->get('expenses_526') + $result->get('expenses_527') + $result->get('expenses_528') + $result->get('expenses_556'); //��¤���������
    } else {
        $manu_expenses = $result->get('expenses_518') + $result->get('expenses_519') + $result->get('expenses_520') + $result->get('expenses_547') + $result->get('expenses_527') + $result->get('expenses_528') + $result->get('expenses_556'); //��¤���������
    }
    $result->add('manu_expenses', $manu_expenses);
    ////////// C��Ω�������
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=176", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_176', $res_exp[0][0]);
    if ($end_ym < 201404) {
        $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=510", $str_ym, $end_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_510', $res_exp[0][0]);
    }
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=522", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_522', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=523", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_523', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=525", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_525', $res_exp[0][0]);
    if ($end_ym < 201404) {
        $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=571", $str_ym, $end_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_571', $res_exp[0][0]);
    }
    if ($end_ym < 201404) {
        $c_assembly_expense = $result->get('expenses_176') + $result->get('expenses_510') + $result->get('expenses_522') + $result->get('expenses_523') + $result->get('expenses_525') + $result->get('expenses_571'); //C��Ω�������
    } else {
        $c_assembly_expense = $result->get('expenses_176') + $result->get('expenses_522') + $result->get('expenses_523') + $result->get('expenses_525'); //C��Ω�������
    }
    $result->add('c_assembly_expense', $c_assembly_expense);
    $c_expense = $result->get('manu_expenses') + $result->get('c_assembly_expense');       // ���ץ����������
    $result->add('c_expense', $c_expense);
    ////////// L��Ω�������
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=551", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_551', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=175", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_175', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=560", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_560', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=572", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_572', $res_exp[0][0]);
    if($end_ym < 201012) { 
        $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=559", $str_ym, $end_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_559', $res_exp[0][0]);
    }
    if($end_ym < 201012) { 
        $l_assembly_expense = $result->get('expenses_551') + $result->get('expenses_175') + $result->get('expenses_560') + $result->get('expenses_572') + $result->get('expenses_559'); //L��Ω�������
    } else {
        $l_assembly_expense = $result->get('expenses_551') + $result->get('expenses_175') + $result->get('expenses_560') + $result->get('expenses_572'); //L��Ω�������
    }
    $result->add('l_assembly_expense', $l_assembly_expense);
    $total_direct_section = $result->get('c_expense') + $result->get('l_assembly_expense'); // ľ�����������
    $result->add('total_direct_section', $total_direct_section);
}        

////////////// ���������׻�
function act_expenses_cal($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $acts_ym = substr($str_ym, 2, 4);    // ��������񡦥����ӥ��������ΰ٤γ���ǯ��
    $acte_ym = substr($end_ym, 2, 4);    // ��������񡦥����ӥ����ʷ軻�ʤΤ����Ϥ��줿���Ͻ�λǯ�����
    $manu_expenses = 0;
    $c_assembly_expense = 0;
    $l_assembly_expense = 0;
    $c_expense = 0;
    $total_direct_section = 0;
    ////////// ��¤�����������߷׷׻���
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=518", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_518', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=519", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_519', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=520", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_520', $res_exp[0][0]);
    if($end_ym < 201404) { 
        $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=526", $acts_ym, $acte_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_526', $res_exp[0][0]);
    } else {
        $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=547", $acts_ym, $acte_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_547', $res_exp[0][0]);
    }
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=527", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_527', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=528", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_528', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=556", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_556', $res_exp[0][0]);
    if($end_ym < 201404) { 
        $manu_expenses = $result->get('expenses_518') + $result->get('expenses_519') + $result->get('expenses_520') + $result->get('expenses_526') + $result->get('expenses_527') + $result->get('expenses_528') + $result->get('expenses_556'); //��¤���������
    } else {
        $manu_expenses = $result->get('expenses_518') + $result->get('expenses_519') + $result->get('expenses_520') + $result->get('expenses_547') + $result->get('expenses_527') + $result->get('expenses_528') + $result->get('expenses_556'); //��¤���������
    }
    $result->add('manu_expenses', $manu_expenses);
    ////////// C��Ω���������߷׷׻���
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=176", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_176', $res_exp[0][0]);
    if($end_ym < 201404) { 
        $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=510", $acts_ym, $acte_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_510', $res_exp[0][0]);
    }
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=522", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_522', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=523", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_523', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=525", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_525', $res_exp[0][0]);
    if($end_ym < 201404) { 
        $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=571", $acts_ym, $acte_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_571', $res_exp[0][0]);
    }
    if($end_ym < 201404) { 
        $c_assembly_expense = $result->get('expenses_176') + $result->get('expenses_510') + $result->get('expenses_522') + $result->get('expenses_523') + $result->get('expenses_525') + $result->get('expenses_571'); //C��Ω�������
    } else {
        $c_assembly_expense = $result->get('expenses_176') + $result->get('expenses_522') + $result->get('expenses_523') + $result->get('expenses_525'); //C��Ω�������
    }
    $result->add('c_assembly_expense', $c_assembly_expense);
    $c_expense = $result->get('manu_expenses') + $result->get('c_assembly_expense');       // ���ץ����������
    $result->add('c_expense', $c_expense);
    ////////// L��Ω���������߷׷׻���
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=551", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_551', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=175", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_175', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=560", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_560', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=572", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_572', $res_exp[0][0]);
    if($end_ym < 201012) { 
        $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=559", $acts_ym, $acte_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_559', $res_exp[0][0]);
    }
    if($end_ym < 201012) { 
        $l_assembly_expense = $result->get('expenses_551') + $result->get('expenses_175') + $result->get('expenses_560') + $result->get('expenses_572') + $result->get('expenses_559'); //L��Ω�������
    } else {
        $l_assembly_expense = $result->get('expenses_551') + $result->get('expenses_175') + $result->get('expenses_560') + $result->get('expenses_572'); //L��Ω�������
    }
    $result->add('l_assembly_expense', $l_assembly_expense);
    $total_direct_section = $result->get('c_expense') + $result->get('l_assembly_expense'); // ľ�����������
    $result->add('total_direct_section', $total_direct_section);             
}

////////////// �����ӥ����ǡ�������
function get_act_indirect($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $manu_service           = 0;      // �����������¤�ݡʥ����ӥ������͡�
    $c_assembly_service     = 0;      // ���������C��Ω�ݡʥ����ӥ������͡�
    $factory_indirect       = 0;      // �����������
    $fact_indirect_l        = 0;      // ��˥��������������������ʥ����ӥ������͡�
    $suppli_indirect_c      = 0;      // ���ץ�Ĵã����������������ʥ����ӥ������͡�
    $suppli_indirect_l      = 0;      // ��˥�Ĵã����������������ʥ����ӥ������͡�
    $suppli_indirect_t      = 0;      // Ĵã������������������
    ////////// �����������¤��
    ////////// C��¤
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='����¤'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $manu_service += $res_ser[0][0];
    ////////// C������
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='��������'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $manu_service += $res_ser[0][0];
    $result->add('manu_service', $manu_service);
    ////////// ���������C��Ω��
    ////////// C��Ω
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='����Ω'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $c_assembly_service += $res_ser[0][0];
    ////////// C������
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='��������'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $c_assembly_service += $res_ser[0][0];
    $result->add('c_assembly_service', $c_assembly_service);
    ////////// ��˥��������������������
    ////////// L��Ω
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='����Ω'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $fact_indirect_l += $res_ser[0][0];
    ////////// �Х����
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='�Х����'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $fact_indirect_l += $res_ser[0][0];
    $result->add('fact_indirect_l', $fact_indirect_l);
    ////////// ���ץ�Ĵã����������������
    ////////// C����
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='�ó���'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_c += $res_ser[0][0];
    ////////// C������
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='�ó�����'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_c += $res_ser[0][0];
    $result->add('suppli_indirect_c', $suppli_indirect_c);
    ////////// ��˥�Ĵã����������������
    ////////// L����
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='�̳���'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_l += $res_ser[0][0];
    ////////// ����Х�
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='����Х�'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_l += $res_ser[0][0];
    $result->add('suppli_indirect_l', $suppli_indirect_l);
    $suppli_indirect_t = $suppli_indirect_c + $suppli_indirect_l; // Ĵã������������������
    $result->add('suppli_indirect_t', $suppli_indirect_t);
}

////////////// �����ӥ����ǡ����׻�
function act_indirect_cal($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $manu_service           = 0;      // �����������¤�ݡʥ����ӥ������͡�
    $c_assembly_service     = 0;      // ���������C��Ω�ݡʥ����ӥ������͡�
    $factory_indirect       = 0;      // �����������
    $fact_indirect_l        = 0;      // ��˥��������������������ʥ����ӥ������͡�
    $suppli_indirect_c      = 0;      // ���ץ�Ĵã����������������ʥ����ӥ������͡�
    $suppli_indirect_l      = 0;      // ��˥�Ĵã����������������ʥ����ӥ������͡�
    $suppli_indirect_t      = 0;      // Ĵã������������������
    ////////// �����������¤�ݡ��߷׷׻���
    ////////// C��¤
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='����¤'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $manu_service += $res_ser[0][0];
    ////////// C������
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='��������'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $manu_service += $res_ser[0][0];
    $result->add('manu_service', $manu_service);
    ////////// ���������C��Ω�ݡ��߷׷׻���
    ////////// C��Ω
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='����Ω'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $c_assembly_service += $res_ser[0][0];
    ////////// C������
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='��������'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $c_assembly_service += $res_ser[0][0];
    $result->add('c_assembly_service', $c_assembly_service);
    ////////// ��˥����������������������߷׷׻���
    ////////// L��Ω
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='����Ω'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $fact_indirect_l += $res_ser[0][0];
    ////////// �Х����
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='�Х����'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $fact_indirect_l += $res_ser[0][0];
    $result->add('fact_indirect_l', $fact_indirect_l);
    ////////// ���ץ�Ĵã������������������߷׷׻���
    ////////// C����
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='�ó���'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_c += $res_ser[0][0];
    ////////// C������
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='�ó�����'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_c += $res_ser[0][0];
    $result->add('suppli_indirect_c', $suppli_indirect_c);
    ////////// ��˥�Ĵã������������������߷׷׻���
    ////////// L����
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='�̳���'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_l += $res_ser[0][0];
    ////////// ����Х�
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='����Х�'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_l += $res_ser[0][0];
    $result->add('suppli_indirect_l', $suppli_indirect_l);
    $suppli_indirect_t = $suppli_indirect_c + $suppli_indirect_l; // Ĵã������������������
    $result->add('suppli_indirect_t', $suppli_indirect_t);
}

////////////// �Ƽ�ǡ�������
function get_various_data($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $item                = array();         // ����Ψ�оݥ��롼��
    $external_price      = array();         // ������
    $external_assy_price = array();         // ����ASSY��
    $direct_expenses     = 0;               // ľ����
    $query_in = sprintf("SELECT * FROM indirect_cost_allocate WHERE total_date=%d", $end_ym);
    $res_in = array();
    $rows_in = getResult($query_in, $res_in);
    $result->add_array2('res_in', $res_in);
    $result->add('rows_in', $rows_in);
    for ($i=0; $i<$rows_in; $i++) {
        $external_price[$i] = 0;
    }
    for ($i=0; $i<$rows_in; $i++) {
        $external_assy_price[$i] = 0;
    }
    for ($i=0; $i<$rows_in; $i++) {
        $item[$i] = $res_in[$i]['item'];    // ��ȥ��롼��
    }
    for ($i=0; $i<$rows_in; $i++) {
        $query = sprintf("SELECT sum(external_price) FROM indirect_cost_allocate WHERE total_date>=%d AND total_date<=%d AND item='%s'", $str_ym, $end_ym, $item[$i]);
        $res_sum = array();
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $external_price[$i] = 0;        // �����񽸷�
        } else {
            $external_price[$i] += $res_sum[0]['sum'];
        }
        $query = sprintf("SELECT sum(external_assy_price) FROM indirect_cost_allocate WHERE total_date>=%d AND total_date<=%d AND item='%s'", $str_ym, $end_ym, $item[$i]);
        $res_sum = array();
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $external_assy_price[$i] = 0;   // ����ASSY�񽸷�
        } else {
            $external_assy_price[$i] += $res_sum[0]['sum'];
        }
        $query = sprintf("SELECT sum(direct_expense) FROM indirect_cost_allocate WHERE total_date>=%d AND total_date<=%d AND item='%s'", $str_ym, $end_ym, $item[$i]);
        $res_sum = array();
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $direct_expenses = 0;           // ľ���񽸷�
        } else {
            $direct_expenses += $res_sum[0]['sum'];
        }
    }
    $result->add_array2('item', $item);
    $result->add_array2('external_price', $external_price);
    $result->add_array2('external_assy_price', $external_assy_price);
    $result->add('direct_expenses', $direct_expenses);
}

////////////// �������������Ψ����
function get_indirect_cost($result, $request)
{
    $item                = $result->get_array2('item');
    $external_price      = $result->get_array2('external_price');
    $external_assy_price = $result->get_array2('external_assy_price');
    $res_in              = $result->get_array2('res_in');
    $indirect_cost       = array();                                 // �������������Ψ
    $suppli_section_cost = array();                                 // Ĵã����������Ψ
    $total_indirect_section = 0;                                    // ������������
    $fact_indirect_c        = 0;                                    // ���ץ鹩�����������������
    $exp_ext_assy_c         = 0;                                    // ���ץ����ܳ���ASSY��
    $exp_ext_assy_l         = 0;                                    // ��˥�����ܳ���ASSY��
    $fact_indirect_t        = 0;                                    // ���������������������
    $c_indirect_cost        = 0;                                    // ���ץ鹩�����������Ψ
    $c_suppli_section_cost  = 0;                                    // ���ץ�Ĵã����������Ψ
    $l_indirect_cost        = 0;                                    // ��˥��������������Ψ
    $l_suppli_section_cost  = 0;                                    // ��˥�Ĵã����������Ψ
    $external_price_c       = 0;                                    // ���ץ鳰�����ɽ���ѡ�
    $external_price_l       = 0;                                    // ��˥��������ɽ���ѡ�
    $external_assy_price_c  = 0;                                    // ���ץ鳰��ASSY���ɽ���ѡ�
    $external_assy_price_l  = 0;                                    // ���ץ鳰��ASSY���ɽ���ѡ�
    $fact_indirect_c = $result->get('manu_service') + $result->get('c_assembly_service') - $result->get('direct_expenses'); // ���ץ鹩�����������������
    $fact_indirect_t = $fact_indirect_c + $result->get('fact_indirect_l') + $result->get('direct_expenses'); // ���������������
    $total_indirect_section = $fact_indirect_t + $result->get('suppli_indirect_t'); // ������������
    $exp_ext_assy_c = $result->get('c_expense');                    // ���ץ����ܳ���ASSY��׻���
    $exp_ext_assy_l = $result->get('l_assembly_expense');           // ��˥�����ܳ���ASSY��׻���
    for ($i=0; $i<$result->get('rows_in'); $i++) {
        switch ($item[$i]) {
            case '���ץ�':    //�оݥ��롼�פ����ץ�λ�
                $external_price_c       = $external_price[$i];      // ���ץ鳰�����ɽ���ѡ�
                $external_assy_price_c  = $external_assy_price[$i]; // ���ץ鳰��ASSY���ɽ���ѡ�
                $exp_ext_assy_c += $external_assy_price[$i];        // ���ץ����ܳ���ASSY��׻���
                $indirect_cost[$i] = $res_in[$i]['indirect_cost'];  // �������������Ψ�μ���
                $c_indirect_cost = $indirect_cost[$i];
                break;
            case '��˥�':    //�оݥ��롼�פ���˥��λ�
                $external_price_l       = $external_price[$i];      // ��˥��������ɽ���ѡ�
                $external_assy_price_l  = $external_assy_price[$i]; // ���ץ鳰��ASSY���ɽ���ѡ�
                $exp_ext_assy_l += $external_assy_price[$i];        // ��˥�����ܳ���ASSY��׻���
                $indirect_cost[$i] = $res_in[$i]['indirect_cost'];  // �������������Ψ�μ���
                $l_indirect_cost = $indirect_cost[$i];
                break;
            default:
                break;
        }
    }
    ////////// Ĵã����������Ψ����
    for ($i=0; $i<$result->get('rows_in'); $i++) {
        switch ($item[$i]) {
            case '���ץ�':                                                     // �оݥ��롼�פ����ץ�λ�
                $suppli_section_cost[$i] = $res_in[$i]['suppli_section_cost']; // Ĵã����������Ψ�μ���
                $c_suppli_section_cost = $suppli_section_cost[$i];
                break;
            case '��˥�':                                                     // �оݥ��롼�פ���˥��λ�
                $suppli_section_cost[$i] = $res_in[$i]['suppli_section_cost']; // Ĵã����������Ψ�μ���
                $l_suppli_section_cost = $suppli_section_cost[$i];
                break;
            default:
                break;
        }
    }
    $result->add('fact_indirect_c', $fact_indirect_c);
    $result->add('fact_indirect_t', $fact_indirect_t);
    $result->add('total_indirect_section', $total_indirect_section);
    $result->add('exp_ext_assy_c', $exp_ext_assy_c);
    $result->add('exp_ext_assy_l', $exp_ext_assy_l);
    $result->add('external_price_c', $external_price_c);
    $result->add('external_price_l', $external_price_l);
    $result->add('external_assy_price_c', $external_assy_price_c);
    $result->add('external_assy_price_l', $external_assy_price_l);
    $result->add('c_indirect_cost', $c_indirect_cost);
    $result->add('l_indirect_cost', $l_indirect_cost);
    $result->add('c_suppli_section_cost', $c_suppli_section_cost);
    $result->add('l_suppli_section_cost', $l_suppli_section_cost);
}

////////////// �������������Ψ�׻�
function indirect_cost_cal($result, $request)
{
    $item                = $result->get_array2('item');
    $external_price      = $result->get_array2('external_price');
    $external_assy_price = $result->get_array2('external_assy_price');
    $res_in              = $result->get_array2('res_in');
    $indirect_cost       = array();                                 // �������������Ψ
    $suppli_section_cost = array();                                 // Ĵã����������Ψ
    $total_indirect_section = 0;                                    // ������������
    $fact_indirect_c        = 0;                                    // ���ץ鹩�����������������
    $exp_ext_assy_c         = 0;                                    // ���ץ����ܳ���ASSY��
    $exp_ext_assy_l         = 0;                                    // ��˥�����ܳ���ASSY��
    $fact_indirect_t        = 0;                                    // ���������������������
    $c_indirect_cost        = 0;                                    // ���ץ鹩�����������Ψ
    $c_suppli_section_cost  = 0;                                    // ���ץ�Ĵã����������Ψ
    $l_indirect_cost        = 0;                                    // ��˥��������������Ψ
    $l_suppli_section_cost  = 0;                                    // ��˥�Ĵã����������Ψ
    $external_price_c       = 0;                                    // ���ץ鳰�����ɽ���ѡ�
    $external_price_l       = 0;                                    // ��˥��������ɽ���ѡ�
    $external_assy_price_c  = 0;                                    // ���ץ鳰��ASSY���ɽ���ѡ�
    $external_assy_price_l  = 0;                                    // ���ץ鳰��ASSY���ɽ���ѡ�
    $fact_indirect_c = $result->get('manu_service') + $result->get('c_assembly_service') - $result->get('direct_expenses');      //���ץ鹩�����������������
    $fact_indirect_t = $fact_indirect_c + $result->get('fact_indirect_l') + $result->get('direct_expenses'); // ���������������
    $total_indirect_section = $fact_indirect_t + $result->get('suppli_indirect_t'); // ������������
    $exp_ext_assy_c = $result->get('c_expense');                    // ���ץ����ܳ���ASSY��׻���
    $exp_ext_assy_l = $result->get('l_assembly_expense');           // ��˥�����ܳ���ASSY��׻���
    for ($i=0; $i<$result->get('rows_in'); $i++) {
        switch ($item[$i]) {
            case '���ץ�':                                          // �оݥ��롼�פ����ץ�λ�
                $external_price_c       = $external_price[$i];      // ���ץ鳰�����ɽ���ѡ�
                $external_assy_price_c  = $external_assy_price[$i]; // ���ץ鳰��ASSY���ɽ���ѡ�
                $exp_ext_assy_c += $external_assy_price[$i];        // ���ץ����ܳ���ASSY��׻���
                $indirect_cost[$i] = $fact_indirect_c / $exp_ext_assy_c * 100;
                $c_indirect_cost = $indirect_cost[$i];
                break;
            case '��˥�':                                          // �оݥ��롼�פ���˥��λ�
                $external_price_l       = $external_price[$i];      // ��˥��������ɽ���ѡ�
                $external_assy_price_l  = $external_assy_price[$i]; // ���ץ鳰��ASSY���ɽ���ѡ�
                $exp_ext_assy_l += $external_assy_price[$i];        // ��˥�����ܳ���ASSY��׻���
                $indirect_cost[$i] = $result->get('fact_indirect_l') / $exp_ext_assy_l * 100;
                $l_indirect_cost = $indirect_cost[$i];
                break;
            default:
                break;
        }
    }
    ////////// Ĵã����������Ψ�׻�
    for ($i=0; $i<$result->get('rows_in'); $i++) {
        switch ($item[$i]) {
            case '���ץ�':                                          // �оݥ��롼�פ����ץ�λ�
                $suppli_section_cost[$i] = $result->get('suppli_indirect_c') / $external_price[$i] * 100;
                $c_suppli_section_cost = $suppli_section_cost[$i];
                break;
            case '��˥�':                                          // �оݥ��롼�פ���˥��λ�
                $suppli_section_cost[$i] = $result->get('suppli_indirect_l') / $external_price[$i] * 100;
                $l_suppli_section_cost = $suppli_section_cost[$i];
                break;
            default:
                break;
        }
    }
    $result->add('fact_indirect_c', $fact_indirect_c);
    $result->add('fact_indirect_t', $fact_indirect_t);
    $result->add('total_indirect_section', $total_indirect_section);
    $result->add('exp_ext_assy_c', $exp_ext_assy_c);
    $result->add('exp_ext_assy_l', $exp_ext_assy_l);
    $result->add('external_price_c', $external_price_c);
    $result->add('external_price_l', $external_price_l);
    $result->add('external_assy_price_c', $external_assy_price_c);
    $result->add('external_assy_price_l', $external_assy_price_l);
    $result->add('c_indirect_cost', $c_indirect_cost);
    $result->add('l_indirect_cost', $l_indirect_cost);
    $result->add('c_suppli_section_cost', $c_suppli_section_cost);
    $result->add('l_suppli_section_cost', $l_suppli_section_cost);
}

////////////// ��Ψ�׻�
function assembly_actAllocate_cal ($request, $result, $menu)
{    
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    get_various_data($request, $result);     // �Ƽ�ǡ�������
    act_expenses_cal($request, $result);     // ���������׻�
    act_indirect_cal($request, $result);     // �����ӥ����ǡ����׻�
    indirect_cost_cal($result, $request);    // �������������Ψ�׻�
}

////////////// �����Ǥ�դ����դ������'����'�ե����ޥåȤ����֤���
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
        $ki    = substr($nen, 3, 1);
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

////////////// ���롼���ֹ�򥰥롼��̾���Ѵ�
function format_number_name($number, $res_nn, $rows_nn)
{
    for ($n=0; $n<$rows_nn; $n++) {
        if ($res_nn[$n][0] == $number) {
            $group_name = $res_nn[$n][1];
            return $group_name;
        }
    }
}

function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<script type='text/javascript' src='../assemblyRate_actAllocate.js'></script>\n";
    $listTable .= "<link rel='stylesheet' href='../assemblyRate_actAllocate.css' type='text/css'>\n";
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
    $expenses_175 = $result->get('expenses_175');
    $expenses_176 = $result->get('expenses_176');
    if($end_ym < 201404) { 
        $expenses_510 = $result->get('expenses_510');
    }
    $expenses_518 = $result->get('expenses_518');
    $expenses_519 = $result->get('expenses_519');
    $expenses_520 = $result->get('expenses_520');
    $expenses_522 = $result->get('expenses_522');
    $expenses_523 = $result->get('expenses_523');
    $expenses_525 = $result->get('expenses_525');
    if($end_ym < 201404) { 
        $expenses_526 = $result->get('expenses_526');
    } else {
        $expenses_547 = $result->get('expenses_547');
    }
    $expenses_527 = $result->get('expenses_527');
    $expenses_528 = $result->get('expenses_528');
    $expenses_551 = $result->get('expenses_551');
    $expenses_556 = $result->get('expenses_556');
    $expenses_560 = $result->get('expenses_560');
    if($end_ym < 201404) { 
        $expenses_571 = $result->get('expenses_571');
    }
    $expenses_572 = $result->get('expenses_572');
    if($end_ym < 201012) { 
        $expenses_559 = $result->get('expenses_559');
    } 
    $c_suppli_section_cost = $result->get('c_suppli_section_cost');
    $l_suppli_section_cost = $result->get('l_suppli_section_cost');
    $total_indirect_section = $result->get('total_indirect_section');
    $total_direct_section = $result->get('total_direct_section');
    $external_assy_price_c = $result->get('external_assy_price_c');
    $external_assy_price_l = $result->get('external_assy_price_l');
    $fact_indirect_c = $result->get('fact_indirect_c');
    $fact_indirect_l = $result->get('fact_indirect_l');
    $fact_indirect_t = $result->get('fact_indirect_t');
    $manu_service = $result->get('manu_service');
    $manu_expenses = $result->get('manu_expenses');
    $c_assembly_service = $result->get('c_assembly_service');
    $direct_expenses = $result->get('direct_expenses');
    $exp_ext_assy_c = $result->get('exp_ext_assy_c');
    $exp_ext_assy_l = $result->get('exp_ext_assy_l');
    $c_indirect_cost = $result->get('c_indirect_cost');
    $l_indirect_cost = $result->get('l_indirect_cost');
    $c_assembly_expense = $result->get('c_assembly_expense');
    $l_assembly_expense = $result->get('l_assembly_expense');
    $c_expense = $result->get('c_expense');
    $suppli_indirect_c = $result->get('suppli_indirect_c');
    $suppli_indirect_l = $result->get('suppli_indirect_l');
    $suppli_indirect_t = $result->get('suppli_indirect_t');
    $external_price_c = $result->get('external_price_c');
    $external_price_l = $result->get('external_price_l');
    $listTable .= "    <table class=border-none border=0 cellpadding=0 cellspacing=0 nowrap>\n";
    $listTable .= "        <col class=border-none nowrap width=108 style='mso-width-source:userset;mso-width-alt:3456;width:81pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=107 style='mso-width-source:userset;mso-width-alt:3424;width:80pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=73 style='mso-width-source:userset;mso-width-alt:2336;width:55pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=125 style='mso-width-source:userset;mso-width-alt:4000;width:94pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=18 style='mso-width-source:userset;mso-width-alt:576;width:14pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=53 style='mso-width-source:userset;mso-width-alt:1696;width:40pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=100 style='mso-width-source:userset;mso-width-alt:3200;width:75pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=72 style='mso-width-source:userset;mso-width-alt:2304;width:54pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=125 style='mso-width-source:userset;mso-width-alt:4000;width:94pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=25 style='mso-width-source:userset;mso-width-alt:800;width:19pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=140 style='mso-width-source:userset;mso-width-alt:4480;width:105pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=88 span=4>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none colspan=2></td>\n";
    $listTable .= "            <td colspan=3 nowrap class=16pt>����������Ψ</td>\n";
    $listTable .= "            <td class=16pt colspan=6 nowrap>". format_date6_ki($end_ym). "�����ӡ�". $str_m . "���" . $end_m . "���</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td colspan=3 class=bold-11pt align=left>�ù�����Ω��μº�ȯ����</td>\n";
    $listTable .= "            <td colspan=7 class=border-none></td>\n";
    $listTable .= "            <td class=border-none>(ñ�̡����)</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>��¤�������</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext'>��¤1��(518)</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext'>". number_format($expenses_518/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td colspan=4 class=bold-11pt nowrap>����Ψ�η׻�(����������񡿼º�ȯ����)</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=back_orange style='border-bottom:none'>����������e+f</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>��1 NC(519)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_519/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td colspan=6 class=border-none></td>\n";
    $listTable .= "            <td class=back_orange style='border-top:none;border-bottom:none'>". number_format($total_indirect_section/1000, 0) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>��4 NC(520)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_520/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td colspan=3 class=border-none-left style='border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>���˹��������</td>\n";
    $listTable .= "            <td colspan=2 class=border-none style='border-top:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    if($end_ym < 201404) { 
        $listTable .= "            <td class=border-none>��1 6��(526)</td>\n";
        $listTable .= "            <td class=border-none>". number_format($expenses_526/1000, 0) ."</td>\n";
    } else {
        $listTable .= "            <td class=border-none>��¤2��(547)</td>\n";
        $listTable .= "            <td class=border-none>". number_format($expenses_547/1000, 0) ."</td>\n";
    
    }
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>��¤</td>\n";
    $listTable .= "            <td class=border-none>". number_format($manu_service/1000, 0) ."</td>\n";
    $listTable .= "            <td colspan=2 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>��5 PF(527)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_527/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>+��C��Ω</td>\n";
    $listTable .= "            <td class=border-none>". number_format($c_assembly_service/1000, 0) ."</td>\n";
    $listTable .= "            <td colspan=2 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>2���ù�(528)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_528/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>- ľ����</td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>". number_format($direct_expenses/1000, 0) ."</td>\n";
    $listTable .= "            <td colspan=2 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>����(556)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_556/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>���ץ���</td>\n";
    $listTable .= "            <td class=back_blue style='border:none'>". number_format($fact_indirect_c/1000, 0) ."</td>\n";
    $listTable .= "            <td colspan=2 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>���</td>\n";
    $listTable .= "            <td class=back_green style='border-left:none;border-right:none'>". number_format($manu_expenses/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-right:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>(a)</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>��˥���Ω</td>\n";
    $listTable .= "            <td class=back_blue style='border:none'>". number_format($fact_indirect_l/1000, 0) ."</td>\n";
    $listTable .= "            <td colspan=2 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td colspan=5 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td colspan=4 class=border-none></td>\n";
    $listTable .= "            <td class=bold-11pt align=center style='border-right:.5pt solid windowtext;border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>". format_date6_ki($end_ym) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-left:.5pt solid windowtext' nowrap>C��Ω�������</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext' nowrap>��Ω1��(176)</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext'>". number_format($expenses_176/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>�����������</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=border-none nowrap>����+����ASSY��</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=bold-11pt style='border-right:.5pt solid windowtext;border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext' nowrap>�������������Ψ</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    if($end_ym < 201404) { 
        $listTable .= "            <td class=border-none>������C(510)</td>\n";
        $listTable .= "            <td class=border-none>". number_format($expenses_510/1000, 0) ."</td>\n";
    } else {
        $listTable .= "            <td class=border-none>��</td>\n";
        $listTable .= "            <td class=border-none>��</td>\n";
    }
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>���ץ�</td>\n";
    $listTable .= "            <td class=border-none>". number_format($fact_indirect_c/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=border-none>". number_format($exp_ext_assy_c/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=back_blue_red>". number_format($c_indirect_cost, 1) ."%</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>�ͣ���(522)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_522/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��˥�</td>\n";
    $listTable .= "            <td class=border-none>". number_format($fact_indirect_l/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=border-none>". number_format($exp_ext_assy_l/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=back_blue_red style='border-bottom:.5pt solid windowtext'>". number_format($l_indirect_cost, 1) ."%</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>�ȣ���(523)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_523/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=back_orange style='border-top:none;border-right:none;border-left:none'>". number_format($fact_indirect_t/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-bottom:.5pt solid windowtext'>(e)</td>\n";
    $listTable .= "            <td colspan=2 class=border-none style='border-bottom:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext;border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>C����(525)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_525/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td colspan=6 class=border-none-left style='border-top:none;border-left:.5pt solid windowtext;border-right:.5pt solid windowtext' nowrap>����Ĵã������ʴ��������������ۡ��������</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    if($end_ym < 201404) { 
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>��4��ΩC(571)</td>\n";
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>". number_format($expenses_571/1000, 0) ."</td>\n";
    } else {
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>��</td>\n";
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>��</td>\n";
    }
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td colspan=4 class=border-none></td>\n";
    $listTable .= "            <td class=bold-11pt align=center style='border-right:.5pt solid windowtext;border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>". format_date6_ki($end_ym) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>���</td>\n";
    $listTable .= "            <td class=back_green style='border:none'>". number_format($c_assembly_expense/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-right:.5pt solid windowtext'>(b)</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none nowrap>�����������</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=border-none>������</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=bold-11pt style='border-right:.5pt solid windowtext;border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext'>Ĵã����������Ψ</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=class=border-none style='border-bottom:.5pt solid windowtext'>C����</td>\n";
    $listTable .= "            <td class=back_green style='border-top:none;border-right:none;border-left:none'>". number_format($c_expense/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-bottom:.5pt solid windowtext;border-right:.5pt solid windowtext'>(c)=(a+b)</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext' nowrap>���ץ�</td>\n";
    $listTable .= "            <td class=border-none>". number_format($suppli_indirect_c/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=border-none>". number_format($external_price_c/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=back_blue_red>". number_format($c_suppli_section_cost, 1) ."%</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td colspan=5 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��˥�</td>\n";
    $listTable .= "            <td class=border-none>". number_format($suppli_indirect_l/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=border-none>". number_format($external_price_l/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>��</td>\n";
    $listTable .= "            <td class=back_blue_red style='border-bottom:.5pt solid windowtext'>". number_format($l_suppli_section_cost, 1) ."%</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>L��Ω�������</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext'>L��Ω��(551)</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext'>". number_format($expenses_551/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=back_orange style='border-top:none;border-right:none;border-left:none'>". number_format($suppli_indirect_t/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-bottom:.5pt solid windowtext'>(f)</td>\n";
    $listTable .= "            <td colspan=2 class=border-none style='border-bottom:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>L��Ω(175)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_175/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>�Х����(560)</td>\n";
    $listTable .= "            <td class=border-none'>". number_format($expenses_560/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none>��4��ΩL(572)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_572/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>��</td>\n";
    if($end_ym < 201012) { 
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>L����(559)</td>\n";
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>". number_format($expenses_559/1000, 0) ."</td>\n";
    } else {
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>��</td>\n";
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>��</td>\n";
    }
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>��</td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>��ס�L�����</td>\n";
    $listTable .= "            <td class=back_green style='border-right:none;border-left:none;border-top:none'>". number_format($l_assembly_expense/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-right:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>(d)</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=bold-11pt>������μ���</td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>C������</td>\n";
    $listTable .= "            <td class=back_yellow>". number_format($external_price_c/1000, 0) ."</td>\n";
    $listTable .= "            <td class=back_green style='border-bottom:none;border-top:none;border-left:none' nowrap>ľ��������c+d</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext'>L������</td>\n";
    $listTable .= "            <td class=back_yellow style='border-bottom:.5pt solid windowtext'>". number_format($external_price_l/1000, 0) ."</td>\n";
    $listTable .= "            <td class=back_green style='border-top:none;border-left:none'>". number_format($total_direct_section/1000, 0) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none>��</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=bold-11pt>����ASSY��</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>C����ASSY</td>\n";
    $listTable .= "            <td class=light_blue style='border-top:.5pt solid windowtext'>". number_format($external_assy_price_c/1000, 0) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext'>L����ASSY</td>\n";
    $listTable .= "            <td class=light_blue style='border-bottom:.5pt solid windowtext'>". number_format($external_assy_price_l/1000, 0) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "    </table>\n";
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
    $file_name = "list/assemblyRate_actAllocate_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // file������rw�⡼�ɤˤ���
}
