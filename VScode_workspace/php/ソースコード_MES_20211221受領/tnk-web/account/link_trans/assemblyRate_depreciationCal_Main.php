<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω��Ψ ����������ɽ����˥塼 assemblyRate_depreciationCal_Main.php    //
//                                 (�� wage_depreciation_cal.php)           //
// Copyright (C) 2007-2013 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/14 Created  assemblyRate_depreciationCal_Main.php                //
//            ��ե�������ƽ�����ؿ��� �����Ȥΰ��֤�Ĵ��             //
//            ;ʬ��<font>�����κ��                                        //
// 2007/12/29 ���եǡ���������ͤ�����                                      //
// 2008/01/09 ����񻺤��¤ӽ�˸����No�ǤΥ����Ȥ��ɲ�                  //
// 2011/06/22 format_date�Ϥ�tnk_func�˰�ư�Τ��ᤳ�������               //
// 2012/10/05 �������ѤΥ��å����ѹ�                                      //
// 2013/01/10 ����ץ饤���󥹼���Ŧ�ˤ�ꡢ�����ѹ�                        //
//            �������������㤬�����Τ�DB final_book_value���������Ͽ�� //
//            ��������˷׻�����褦�ˤ���(2012/03�����)                 //
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
    $menu = new MenuHeader(0);                          // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    ////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('����������λ���');

    $request = new Request;
    $result  = new Result;
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl($_SESSION['wage_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    set_date($request, $result);                        // ���եǡ����μ���
    get_group_master($result, $request);                // ���롼�ץޥ������μ���
    get_leased_master($result, $request);               // �꡼���񻺥ޥ������μ���
    get_capital_master($result, $request);              // ����񻺥ޥ������μ���
    
    depreciationCal_main ($result);                     // ����������׻��ᥤ��
    
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
    require_once ("assemblyRate_depreciationCal_View.php");

    ob_end_flush(); 
}

////////////// ���եǡ����μ���
function set_date($request, $result)
{
    if ($request->get('wage_ym') == '') {
        $wage_ym = date('Ym');           // �ǡ������ʤ����ν����(����)
        if (substr($wage_ym, 4, 2) != 01) {
            $wage_ym--;
        } else {
            $wage_ym = $wage_ym - 100;
            $wage_ym = $wage_ym + 11;    // ��ǯ��12��˥��å�
        }
    } else {
        $wage_ym = $request->get('wage_ym');
    }
    $result->add('wage_ym', $wage_ym);
    date_cal($result);                   // ���եǡ����η׻�
}

////////////// ���եǡ����η׻�
function date_cal($result)
{
    ////////// �оݤ�ǯ�ȷ�����
    $wage_y = substr($result->get('wage_ym'), 0, 4);   // �о�ǯ��ȴ��
    $wage_m = substr($result->get('wage_ym'), 4, 2);   // �оݷ��ȴ��
    ////////// ���������η׻�
    $tsuki = substr($result->get('wage_ym'), 4, 2);
    if($tsuki > 3) {
        if($tsuki < 10) {
            $provisional_month = $tsuki - 3;
        } else {
            $provisional_month = $tsuki - 9;
        }
    } else {
        $provisional_month = $tsuki + 3;
    }
    $result->add('wage_y', $wage_y);
    $result->add('wage_m', $wage_m);
    $result->add('provisional_month', $provisional_month);
}

////////////// ɽ����(����ɽ)�Υ��롼�ץޥ������ǡ�����SQL�Ǽ���
function get_group_master ($result, $request)
{
    $query = "
        SELECT  groupm.group_no                AS ���롼���ֹ�     -- 0
            ,   groupm.group_name              AS ���롼��̾       -- 1
        FROM
            assembly_machine_group_master AS groupm
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "���롼�פ���Ͽ������ޤ���";
        $field[0]   = "���롼���ֹ�";
        $field[1]   = "���롼��̾";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', '');
        $result->add('num_g', 2);
        $result->add('rows_g', '');
    } else {
        $num = count($field);
        $result->add_array2('res_g', $res);
        $result->add_array2('field_g', $field);
        $result->add('num_g', $num);
        $result->add('rows_g', $rows);
    }
}

////////////// �����������SQL�Ǽ���
function get_book_value ($result, $this_asset_no)
{
    $before_book_ym = 0;
    $before_book_ym = $result->get('wage_ym');
    $query = "
        SELECT  book_value                AS ����������     -- 0
            ,   book_ym                   AS ����ǯ��         -- 1
        FROM
            final_book_value
        WHERE 
            asset_no = '{$this_asset_no}'
            AND book_ym  < $before_book_ym
        ORDER BY
            book_ym DESC
        LIMIT 1
    ";

    //$res_v = array();
    //if (getResult($query, $res_v) > 0) {   //////// ��Ͽ����
    //    $this_book_value = $res_v[0][0];
    //}
    //return $this_book_value;
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $result->add('before_book_value', 0);
        $result->add('before_book_ym', 0);
    } else {
        $result->add('before_book_value', $res[0][0]);
        $result->add('before_book_ym', $res[0][1]);
    }
}

////////////// ɽ����(����ɽ)�θ���񻺥ǡ�����SQL�Ǽ���
function get_capital_master ($result, $request)
{
    $wage_ym = $result->get('wage_ym');
    $query = "
        SELECT  groupc.group_no                AS ���롼���ֹ�     -- 0
            ,   groupc.asset_no                AS �����No       -- 1
            ,   cmaster.asset_name             AS ��̾��         -- 2
            ,   cmaster.acquisition_money      AS �������         -- 3
            ,   cmaster.acquisition_date       AS ����ǯ��         -- 4
            ,   cmaster.durable_years          AS ����ǯ��         -- 5
            ,   cmaster.annual_rate            AS ǯ��Ψ           -- 6
            ,   cmaster.end_date               AS ����ǯ��         -- 7
        FROM
            assembly_machine_group_capital_asset AS groupc
        LEFT OUTER JOIN
            capital_asset_master AS cmaster
        ON (groupc.asset_no = cmaster.asset_no)
        WHERE
            cmaster.acquisition_date <= $wage_ym
            AND cmaster.end_date = 0
            OR cmaster.end_date IS NULL
            OR cmaster.end_date > $wage_ym
        ORDER BY
            group_no ASC, cmaster.asset_no ASC
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $result->add_array2('res_c', '');
        $result->add_array2('field_c', '');
        $result->add('num_c', '');
        $result->add('rows_c', '');
    } else {
        $num = count($field);
        $result->add_array2('res_c', $res);
        $result->add_array2('field_c', $field);
        $result->add('num_c', $num);
        $result->add('rows_c', $rows);
    }
    $res_g = $result->get_array2('res_g');
    for ($r=0; $r<$rows; $r++) {    // ���롼���ֹ�ȥ��롼��̾���֤�����(����񻺡�
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res[$r][0] == $res_g[$i][0]) {
                $group_name[$r] = $res_g[$i][1];
            }
        }
    }
    $result->add_array2('group_name_c', $group_name);
}

////////////// ɽ����(����ɽ)�Υ꡼���񻺥ǡ�����SQL�Ǽ���
function get_leased_master ($result, $request)
{
    $wage_ym = $result->get('wage_ym');
    $query = "
        SELECT  groupl.group_no                AS ���롼���ֹ�     -- 0
            ,   groupl.asset_no                AS �꡼����No     -- 1
            ,   lmaster.asset_name             AS �꡼��̾��       -- 2
            ,   lmaster.acquisition_money      AS �������         -- 3
            ,   lmaster.acquisition_date       AS ����ǯ��         -- 4
            ,   lmaster.annual_lease_money     AS ǯ�֥꡼����     -- 5
            ,   lmaster.end_date               AS ��λǯ��         -- 6
        FROM
            assembly_machine_group_leased_asset AS groupl
        LEFT OUTER JOIN
            leased_asset_master AS lmaster
        ON (groupl.asset_no = lmaster.asset_no)
        WHERE
            lmaster.acquisition_date <= $wage_ym
            AND lmaster.end_date = 0
            OR lmaster.end_date IS NULL
            OR lmaster.end_date > $wage_ym
        ORDER BY
            group_no
    ";
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
    } else {
        $num = count($field);
        $result->add_array2('res_l', $res);
        $result->add_array2('field_l', $field);
        $result->add('num_l', $num);
        $result->add('rows_l', $rows);
    }
    $res_g = $result->get_array2('res_g');
    for ($r=0; $r<$rows; $r++) {    // ���롼���ֹ�ȥ��롼��̾���֤�����(����񻺡�
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res[$r][0] == $res_g[$i][0]) {
                $group_name[$r] = $res_g[$i][1];
            }
        }
    }
    $result->add_array2('group_name_l', $group_name);
}

////////////// ���ѳ۷׻��ᥤ��
function depreciationCal_main ($result)
{
    repaymentAmount_cal ($result);        // ���ѳۡ�����Ģ���۷׻�
    //repaymentAmount_cal_min ($result);    // ���ѳۡ�����Ģ���۷׻�����ˡ�����ǡ�
    repaymentAmount_group ($result);      // �׻���̤򥰥롼���̤˿�ʬ
    repaymentAmount_entry ($result);      // �������������Ͽ
}

////////////// ���ѳۡ�����Ģ���۷׻�
function repaymentAmount_cal ($result)
{
    for ($r=0; $r<$result->get('rows_c'); $r++) {                    // �Ŀ�ʬ�����֤�
        $year5_y = 0;                                                // ���������ѤΥ������
        $res = $result->get_array2('res_c');
        $book_value[$r] = $res[$r][3];                               // ����Ģ����ʤν��������
        if ($res[$r][4] > $result->get('wage_ym')) {
            $acquisition_month[$r] = 0;
        } else {
            $acquisition_year[$r]  = substr($res[$r][4], 0, 4);      // ����ǯ�������ǯ��ȴ��
            $acquisition_month[$r] = substr($res[$r][4], 4, 2);      // ����ǯ����������ȴ��
            if ($result->get('wage_y') == $acquisition_year[$r]) {
                if ($acquisition_month[$r] < 4 && $result->get('wage_m') > 3) {           // Ʊ������ǯ�٤Ǥ����ޤ��������
                    $y = 2;
                } else {
                    $y = 1;
                }
            } else if ($result->get('wage_m') < 4) { 
                // $y = $result->get('wage_y') - $acquisition_year[$r];     //���ѳ۷׻���ǯ��
                // �夬���ץ���ࡢ�����ѹ�
                if ($acquisition_month[$r] < 4) { 
                    $y = $result->get('wage_y') - $acquisition_year[$r] + 1;     //���ѳ۷׻���ǯ��
                } else {
                    $y = $result->get('wage_y') - $acquisition_year[$r];     //���ѳ۷׻���ǯ��
                }
            } else {
                // $y = $result->get('wage_y') - $acquisition_year[$r] + 1; // ���ѳ۷׻���ǯ��
                // �夬���ץ���ࡢ�����ѹ�
                if ($acquisition_month[$r] < 4) { 
                    $y = $result->get('wage_y') - $acquisition_year[$r] + 2; // ���ѳ۷׻���ǯ��
                } else {
                    $y = $result->get('wage_y') - $acquisition_year[$r] + 1; // ���ѳ۷׻���ǯ��
                }
            }
            $min_money[$r] = ceil($res[$r][3] * 0.05);              // Ģ����ʺ���۷׻�
            if ($acquisition_month[$r] > 3) {                        // ����ǯ�٤λ�ʧ����׻�
                $repayment_month[$r] = 16 - $acquisition_month[$r];
            } else {
                $repayment_month[$r] = 4 - $acquisition_month[$r];
            }
            // ������ʬ���Ǹ������Ͽ�������ѹ�����������������ʤ���Τ˴ؤ���
            // ���������������Ѥ�Ԥ����å����ɲä��Ƥ��롣
            $this_asset_no = $res[$r][1];
            get_book_value ($result, $this_asset_no);
            $before_book_value = $result->get('before_book_value');
            $before_book_ym    = $result->get('before_book_ym');
            if ($before_book_value > 0) {
                $book_value[$r] = $before_book_value;                    // ����Ģ����ʤν���������������
                // ���������ص�����������(yyyy/03)�μ��δ���4�������
                $before_book_year  = substr($before_book_ym, 0, 4);      // ��������겾�μ���ǯ��ȴ��
                $before_book_month = '04';                               // ���μ������4��Ǹ���
                if ($result->get('wage_m') < 4) { 
                    $y = $result->get('wage_y') - $before_book_year;     //���ѳ۷׻���ǯ��
                } else {
                    $y = $result->get('wage_y') - $before_book_year + 1; // ���ѳ۷׻���ǯ��
                }
                for ($i=0; $y>$i; $i++) {
                    if ($min_money[$r] >= $book_value[$r]) {             // �������������ۤ�꾮�����������Ϻ����ͤ�
                        if ($year5_y==0) {                                                      // ��������ܤΤߤν���
                            $standard_repayment     = floor(($book_value[$r] - 1) / 5);         // �׻��δ��Ȥʤ�5ʬ��1�ˤ�������������
                            $temp_book              = $book_value[$r] - $standard_repayment;    // ��ǯ�٤δ������
                            $repayment_amount[$r]   = $standard_repayment;                      // �������ѳ�
                            $book_value[$r]         = $temp_book;                               // �����������
                        }
                        if ($year5_y > 0 && $year5_y < 5) {                                     // 2ǯ�ܰʹߤν���
                            $repayment_amount[$r]   = $standard_repayment;                      // �������ѳ�
                            $book_value[$r]         = $book_value[$r] - $standard_repayment;    // �����������
                        }
                        if ($year5_y > 4) {                                                     // 6ǯ�ܰʹߤν���
                            if ($book_value[$r] > 1) {
                                $repayment_amount[$r] = $book_value[$r] - 1;
                                $book_value[$r]       = $book_value[$r] - $repayment_amount[$r];
                            } elseif($book_value[$r] == 1) {
                                $repayment_amount[$r] = 0;
                                $book_value[$r] = $book_value[$r];
                            }
                        }
                        $year5_y = $year5_y + 1;
                    } else {
                        $repayment_amount[$r] = floor($book_value[$r] * $res[$r][6]); // ���ѳ۷׻�    
                        $book_value[$r] = $book_value[$r] - $repayment_amount[$r];    // ����Ģ���۷׻�
                        if ($min_money[$r] > $book_value[$r]) {
                            $book_value[$r] = $min_money[$r];
                            if ($y - 1 > $i) {
                                $repayment_amount[$r] = 0;
                            }
                        }
                    }
                }
            } else {
                for ($i=0; $y>$i; $i++) {
                    if ($acquisition_month[$r] > 3) {
                        $now_y = $acquisition_year[$r] + $i;
                    } else {
                        $now_y = $acquisition_year[$r] + $i;
                    }
                    if ($min_money[$r] >= $book_value[$r]) {             // �������������ۤ�꾮�����������Ϻ����ͤ�
                        if ($now_y > 2006) {
                            if ($year5_y==0) {                                                      // ��������ܤΤߤν���
                                $standard_repayment     = floor(($book_value[$r] - 1) / 5);         // �׻��δ��Ȥʤ�5ʬ��1�ˤ�������������
                                $temp_book              = $book_value[$r] - $standard_repayment;    // ��ǯ�٤δ������
                                $repayment_amount[$r]   = $standard_repayment;                      // �������ѳ�
                                $book_value[$r]         = $temp_book;                               // �����������
                            }
                            if ($year5_y > 0 && $year5_y < 5) {                                     // 2ǯ�ܰʹߤν���
                                $repayment_amount[$r]   = $standard_repayment;                      // �������ѳ�
                                $book_value[$r]         = $book_value[$r] - $standard_repayment;    // �����������
                            }
                            if ($year5_y > 4) {                                                     // 6ǯ�ܰʹߤν���
                                if ($book_value[$r] > 1) {
                                    $repayment_amount[$r] = $book_value[$r] - 1;
                                    $book_value[$r]       = $book_value[$r] - $repayment_amount[$r];
                                } elseif($book_value[$r] == 1) {
                                    $repayment_amount[$r] = 0;
                                    $book_value[$r] = $book_value[$r];
                                }
                            }
                            $year5_y = $year5_y + 1;
                        } else {
                            $book_value[$r] = $min_money[$r];
                        }
                    } else {
                        switch ($i) {
                            case 0:                                                           // �оݷ��Ʊ��ǯ�˻���Ͽ���줿��Τξ��
                                $repayment_amount[$r] = floor($res[$r][3] * $res[$r][6] * $repayment_month[$r] / 12); // ���ѳ۷׻�
                                $book_value[$r] = $res[$r][3] - $repayment_amount[$r];        // ����Ģ���۷׻�
                                if ($min_money[$r] > $book_value[$r]) {
                                    $book_value[$r] = $min_money[$r];
                                    if ($y - 1 > $i) {
                                        $repayment_amount[$r] = 0;
                                    }
                                }
                            break;
                            default:
                                $repayment_amount[$r] = floor($book_value[$r] * $res[$r][6]); // ���ѳ۷׻�    
                                $book_value[$r] = $book_value[$r] - $repayment_amount[$r];    // ����Ģ���۷׻�
                                if ($min_money[$r] > $book_value[$r]) {
                                    $book_value[$r] = $min_money[$r];
                                    if ($y - 1 > $i) {
                                        $repayment_amount[$r] = 0;
                                    }
                                }
                            break;
                        }
                    }
                }
            }
        }
        //$repayment_amount[$r] = $now_y;
        //$book_value[$r] = $year5_y;
    }
    $result->add_array2('repayment_amount', $repayment_amount);
    $result->add_array2('book_value', $book_value);
}

////////////// �ʹ�200704��ˡ��������б� ������Ͽ����Ƥ���񻺤�200704���������5%��ã���Ƥ�����
////////////// �ʹ�5ǯ�֤�5ʬ��1���Ľ��Ѥ��Ǹ����������ˤʤ�褦�˷׻����롣
////////////// ü�����Ф���ϰʲ��ν�����6ǯ�ܤ˽��Ѥ��롣
////////////// ü���ڼΤ�
//////////////�ʣ��������������ݣ��ˡࣵ�ᣱ����������   �������������ߣ��᣹����������
////////////// �ʤΤǡ���ǯ�ܤˣ��ߤ���Ѥ��ƣ��ߤ�Ĥ�
 
function repaymentAmount_cal_min ($result)
{
    if ($result->get('wage_ym') >= 200704) {
        for ($r=0; $r<$result->get('rows_c'); $r++) {                            // �Ŀ�ʬ�����֤�
            $res = $result->get_array2('res_c');
            $repayment_amount = $result->get_array2('repayment_amount');
            $book_value = $result->get_array2('book_value');
            $min_money[$r] = ceil($res[$r][3] * 0.05);                          // Ģ����ʺ���۷׻�
            if ($min_money[$r] >= $book_value[$r]) {
                if ($book_value[$r] != 1) {
                    $y = $result->get('wage_y') - 2006;                              // ���ѳ۷׻���ǯ��
                    for ($i=0; $y>$i; $i++) { 
                        if ($i==0) {                                                            // ��������ܤΤߤν���
                            $standard_repayment     = floor(($book_value[$r] - 1) / 5);         // �׻��δ��Ȥʤ�5ʬ��1�ˤ�������������
                            $temp_book              = $book_value[$r] - $standard_repayment;    // ��ǯ�٤δ������
                            $repayment_amount_m[$r] = $standard_repayment;                      // �������ѳ�
                            $book_value_m[$r]       = $temp_book;                               // �����������
                        }
                        if ($i > 0 && $i < 5) {                                                 // 2ǯ�ܰʹߤν���
                            $repayment_amount_m[$r] = $standard_repayment;                      // �������ѳ�
                            $book_value_m[$r]       = $book_value_m[$r] - $standard_repayment;  // �����������
                        }
                        if ($i > 4) {                                                           // 6ǯ�ܰʹߤν���
                            if ($book_value_m[$r] > 1) {
                                $repayment_amount_m[$r] = $book_value_m[$r] - 1;
                                $book_value_m[$r]       = $book_value_m[$r] - $repayment_amount_m[$r];
                            } elseif($book_value_m[$r] == 1) {
                                $repayment_amount_m[$r] = 0;
                                $book_value_m[$r] = $book_value[$r];
                            }
                        }
                        //switch ($i) {
                        //    case 0:                                                  // ��������ܤΤߤν���
                        //        $standard_repayment = ceil($book_value[$r] / 5);     // �׻��δ��Ȥʤ�5ʬ��1�ˤ�������������
                        //        $temp_repayment = $standard_repayment;               // ���θ��������������
                        //        $temp_book      = $book_value[$r] - $temp_repayment; // �������������������
                        //        if ($temp_book <= 0) { 
                        //            $temp_repayment = $book_value[$r] - 1;
                        //            $temp_book = 1;
                        //            $repayment_amount_m[$r] = $temp_repayment;
                        //            $book_value_m[$r] = $temp_book;
                        //        } else {
                        //            $repayment_amount_m[$r] = $temp_repayment;
                        //            $book_value_m[$r] = $temp_book;
                        //        }
                        //    break;
                        //    default:
                        //        $temp_repayment = $standard_repayment;               // ���θ��������������
                        //        $temp_book      = $book_value[$r] - $temp_repayment; // �������������������
                        //        if ($temp_book <= 0) { 
                        //            $temp_repayment = $book_value[$r] - 1;
                        //            $temp_book = 1;
                        //            $repayment_amount_m[$r] = $temp_repayment;
                        //            $book_value_m[$r] = $temp_book;
                        //        } else {
                        //            $repayment_amount_m[$r] = $temp_repayment;
                        //            $book_value_m[$r] = $temp_book;
                        //        }
                        //    break;
                        //}
                    }
                } else {
                    $repayment_amount_m[$r] = 0;
                    $book_value_m[$r] = $book_value[$r];
                }
            } else {
                $repayment_amount_m[$r] = $repayment_amount[$r];
                $book_value_m[$r] = $book_value[$r];
            }
        }
        $result->add_array2('repayment_amount', $repayment_amount_m);
        $result->add_array2('book_value', $book_value_m);
    }
}

////////////// �׻���̤򥰥롼���̤˿���
function repaymentAmount_group ($result)
{
    $res_g = $result->get_array2('res_g');
    $res_l = $result->get_array2('res_l');
    $res = $result->get_array2('res_c');
    ////////// �ǡ����ν����
    $group_money = array();
    for ($i=0; $i<$result->get('rows_c'); $i++) {
        $group_money[$i] = 0;
    }
    $group_capital = array();
    for ($i=0; $i<$result->get('rows_c'); $i++) {
        $group_capital[$i] = 0;
    }
    $group_lease = array();
    for ($i=0; $i<$result->get('rows_c'); $i++) {
        $group_lease[$i] = 0;
    }
    $repayment_amount = $result->get_array2('repayment_amount');
    for ($r=0; $r<$result->get('rows_c'); $r++) {    // ���ѳۤ򥰥롼���̤˿�ʬ
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res[$r][0] == $res_g[$i][0]) {
                $group_money[$i] += $repayment_amount[$r];
            }
        }
    }

    for ($r=0; $r<$result->get('rows_c'); $r++) {    // ���롼����������Ϥ��ǡ����η׻���
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res[$r][0] == $res_g[$i][0]) {
                $group_capital[$i] = $group_capital[$i] + $repayment_amount[$r];
            }
        }
    }

    for ($r=0; $r<$result->get('rows_l'); $r++) {    // ǯ�֥꡼�����򥰥롼���̤˿�ʬ
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res_l[$r][0] == $res_g[$i][0]) {
                $group_money[$i] = $group_money[$i] + $res_l[$r][5];
            }
        }
    }

    for ($r=0; $r<$result->get('rows_l'); $r++) {    // ���롼����������Ϥ��ǡ����η׻���
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res_l[$r][0] == $res_g[$i][0]) {
                $group_lease[$i] = $group_lease[$i] + $res_l[$r][5];
            }
        }
    }

    for ($r=0; $r<$result->get('rows_g'); $r++) {    // ���롼���̸���������׻�
        $group_money[$r] = $group_money[$r] * $result->get('provisional_month') / 12;
        $group_capital[$r] = $group_capital[$r] * $result->get('provisional_month') / 12;
        $group_lease[$r] = $group_lease[$r] * $result->get('provisional_month') / 12;
    }
    $result->add_array2('group_money', $group_money);
    $result->add_array2('group_capital', $group_capital);
    $result->add_array2('group_lease', $group_lease);
}

////////////// ���롼���̤θ����������DB��Ͽ
function repaymentAmount_entry ($result)
{
    $res_g = $result->get_array2('res_g');
    $group_capital = $result->get_array2('group_capital');
    $group_lease = $result->get_array2('group_lease');
    $query = sprintf("SELECT group_machine_rate FROM assembly_machine_group_rate WHERE total_date=%d", $result->get('wage_ym'));
    $res_check = array();
    $rows_check = getResult($query,$res_check);
    if ($rows_check <= 0) {      // ��Ψ����Ͽ�Ѥߤ������å���Ψ����Ͽ�Ѥߤξ��ϸ������������Ͽ���ʤ�
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                $res_g[$i][0], $result->get('wage_ym'));
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE����
                $query = sprintf("UPDATE assembly_machine_group_rate SET group_capital=%d, group_lease=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $group_capital[$i], $group_lease[$i], $_SESSION['User_ID'], $res_g[$i][0], $result->get('wage_ym'));
                if (query_affected($query) <= 0) {
                }
            } else {                                    //////// ��Ͽ�ʤ� INSERT ����   
                $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_capital, group_lease, last_date, last_user)
                                  VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                    $res_g[$i][0], $result->get('wage_ym'), $group_capital[$i], $group_lease[$i], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                }
            }
        }
    } else {
        if ($res_check[0]['group_machine_rate'] == '') {      // ��Ψ����Ͽ�Ѥߤ������å���Ψ����Ͽ�Ѥߤξ��ϸ������������Ͽ���ʤ�
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                    $res_g[$i][0], $result->get('wage_ym'));
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE����
                    $query = sprintf("UPDATE assembly_machine_group_rate SET group_capital=%d, group_lease=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $group_capital[$i], $group_lease[$i], $_SESSION['User_ID'], $res_g[$i][0], $result->get('wage_ym'));
                    if (query_affected($query) <= 0) {
                    }
                } else {                                    //////// ��Ͽ�ʤ� INSERT ����   
                    $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_capital, group_lease, last_date, last_user)
                                      VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                        $res_g[$i][0], $result->get('wage_ym'), $group_capital[$i], $group_lease[$i], $_SESSION['User_ID']);
                    if (query_affected($query) <= 0) {
                    }
                }
            }
        }
    }
    
    $group_capital = $result->get_array2('group_capital');
    $group_lease = $result->get_array2('group_lease');
    $res_g = $result->get_array2('res_g');
    $query = sprintf("SELECT group_machine_rate FROM assembly_machine_group_rate WHERE total_date=%d", $result->get('wage_ym'));
    $res_check = array();
    $rows_check = getResult($query,$res_check);
    if ($rows_check <= 0) {      // ��Ψ����Ͽ�Ѥߤ������å���Ψ����Ͽ�Ѥߤξ��ϸ������������Ͽ���ʤ�
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                $res_g[$i][0], $result->get('wage_ym'));
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE����
                $query = sprintf("UPDATE assembly_machine_group_rate SET group_capital=%d, group_lease=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $group_capital[$i], $group_lease[$i], $_SESSION['User_ID'], $res_g[$i][0], $result->get('wage_ym'));
                if (query_affected($query) <= 0) {
                }
            } else {                                    //////// ��Ͽ�ʤ� INSERT ����   
                $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_capital, group_lease, last_date, last_user)
                                  VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                    $res_g[$i][0], $result->get('wage_ym'), $group_capital[$i], $group_lease[$i], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                }
            }
        }
    } else {
        if ($res_check[0]['group_machine_rate'] == '') {    // ��Ψ����Ͽ�Ѥߤ������å���Ψ����Ͽ�Ѥߤξ��ϸ������������Ͽ���ʤ�
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                    $res_g[$i][0], $result->get('wage_ym'));
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE����
                    $query = sprintf("UPDATE assembly_machine_group_rate SET group_capital=%d, group_lease=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $group_capital[$i], $group_lease[$i], $_SESSION['User_ID'], $res_g[$i][0], $result->get('wage_ym'));
                    if (query_affected($query) <= 0) {
                    }
                } else {                                    //////// ��Ͽ�ʤ� INSERT ����   
                    $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_capital, group_lease, last_date, last_user)
                                      VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                        $res_g[$i][0], $result->get('wage_ym'), $group_capital[$i], $group_lease[$i], $_SESSION['User_ID']);
                    if (query_affected($query) <= 0) {
                    }
                }
            }
        }
    }
}

////////////// ����������Ȳ���̤�HTML�κ���
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
    $listTable .= "    <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->\n";
    $listTable .= "    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "    <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='700' bgcolor='#ffffc6' align='center' colspan='7'>\n";
    $listTable .= "                ". format_date6_kan($result->get('wage_ym')) ."\n";
    $listTable .= "                    �꡼����\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    $field_l = $result->get_array2('field_l');
    for ($i=0; $i<$result->get('num_l')-1; $i++) {             // �ե�����ɿ�ʬ���֤�
        $listTable .= "                <th class='winbox' nowrap>". $field_l[$i] ."</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' �������� -->\n";
    $listTable .= "            <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res_l = $result->get_array2('res_l');
    $group_name_l = $result->get_array2('group_name_l');
    for ($r=0; $r<$result->get('rows_l'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'><div class='pt10b'>". ($r + 1) ."</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
        for ($i=0; $i<$result->get('num_l'); $i++) {         // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:     // ���롼��
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $group_name_l[$r] ."</div></td>\n";
                    break;
                case 1:     // ��No.
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_l[$r][$i] ."</div></td>\n";
                    break;
                case 2:     // ̾��
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_l[$r][$i] ."</div></td>\n";
                    break;
                case 3:     // �������
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". number_format($res_l[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:     // ����ǯ��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date6($res_l[$r][$i]) ."</div></td>\n";
                    break;
                case 5:     // ǯ�֥꡼����
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". number_format($res_l[$r][$i], 0) ."</div></td>\n";
                    break;
                default:
                    break;
            }
        }
        $listTable .= "        </tr>\n";
    }
    $listTable .= "        </TBODY>\n";
    $listTable .= "    </table>\n";
    $listTable .= "        </td></tr>\n";
    $listTable .= "    </table> <!----------------- ���ߡ�End ------------------>\n";
    $listTable .= "    <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->\n";
    $listTable .= "    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "    <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='900' bgcolor='#ffffc6' align='center' colspan='10'>\n";
    $listTable .= "             ". format_date6_kan($result->get('wage_ym')) ."\n";
    $listTable .= "                �����\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    $field = $result->get_array2('field_c');
    for ($i=0; $i<$result->get('num_c')-1; $i++) {             // �ե�����ɿ�ʬ���֤�
        $listTable .= "            <th class='winbox' nowrap>". $field[$i] ."</th>\n";
    }
    $listTable .= "                <th class='winbox' nowrap>���ѳ�</th>\n";
    $listTable .= "                <th class='winbox' nowrap>����������</th>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' �������� -->\n";
    $listTable .= "            <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = $result->get_array2('res_c');
    $group_name = $result->get_array2('group_name_c');
    $repayment_amount = $result->get_array2('repayment_amount');
    $book_value = $result->get_array2('book_value');
    $group_money = $result->get_array2('group_money');
    for ($r=0; $r<$result->get('rows_c'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'><div class='pt10b'>". ($r + 1) ."</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
        for ($i=0; $i<$result->get('num_c'); $i++) {         // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:     // ���롼��
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
                    break;
                case 1:     // ��No.
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 2:     // ̾��
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 3:     // �������
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:     // ����ǯ��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date6($res[$r][$i]) ."</div></td>\n";
                    break;
                case 5:     // ����ǯ��
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 6:    // ǯ��Ψ
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 7:    //����ǯ��
                    break;
                case 8:     // ���ѳ�
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". number_format($repayment_amount[$r], 0) ."</div></td>\n";
                    break;
                case 9:    // ����Ģ�����
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($book_value[$r], 0) ."</div></td>\n";
                    break;
                default:
                    break;
            }
        }
        $listTable .= "            <td class='winbox' nowrap align='center'><div class='pt9'>". number_format($repayment_amount[$r], 0) ."</div></td>\n"; //���ѳۤ�ɽ��
        $listTable .= "            <td class='winbox' nowrap align='center'><div class='pt9'>". number_format($book_value[$r], 0) ."</div></td>\n"; //���������ۤ�ɽ��
        $listTable .= "        </tr>\n";
    }
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox_field' colspan='4' rowspan='10' align='right' border='1' cellspacing='0' cellpadding='3'>�ƥ��롼�׸�����������</th>\n";
    $res_g = $result->get_array2('res_g');
    for ($i=0; $i<$result->get('rows_g'); $i++) {             // ���롼�׿�ʬ���֤�
        $listTable .= "            <tr>\n";
        $listTable .= "<th class='winbox_field' colspan='2' align='right' border='1' cellspacing='0' cellpadding='3'>". $res_g[$i][1] ."</th>\n";
        $listTable .= "<th class='winbox_field' colspan='2' align='right' border='1' cellspacing='0' cellpadding='3'>". number_format($group_money[$i], 0) ."</th>\n";
        $listTable .= "                <th class='winbox_field' colspan='2' rowspan='1' align='right' border='1' cellspacing='0' cellpadding='3'></th>\n";
        $listTable .= "            </tr>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </TBODY>\n";
    $listTable .= "    </table>\n";
    $listTable .= "        </td></tr>\n";
    $listTable .= "    </table> <!----------------- ���ߡ�End ------------------>\n";
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

////////////// ����������Ȳ���̤�HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '�Ȳ�');
    ////////// HTML�ե��������
    $file_name = "list/assemblyRate_depreciationCal_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}
