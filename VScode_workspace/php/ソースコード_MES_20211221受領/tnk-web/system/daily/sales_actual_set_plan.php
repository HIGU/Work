#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ͽ�� ���ǡ���DB����¸ new version  sales_actual_set_plan.php      //
// Copyright (C) 2020-2020 Waki.Ryota tnksys@nitto-kohki.co.jp              //
// Changed history                                                          //
// 2020/12/17 Created   sales_actual_set_plan.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('error_reporting', E_ALL);               // E_ALL='2047' debug ��
// ini_set('display_errors', '1');                  // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');     // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');                // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);             // ����¹Ի���=20ʬ CLI CGI��
// ob_start('ob_gzhandler');                           // ���ϥХåե���gzip����
// session_start();                                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('/home/www/html/tnk-web/function.php');                // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('/home/www/html/tnk-web/tnk_func.php');                // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('/home/www/html/tnk-web/MenuHeader.php');              // TNK ������ menu class
require_once ('/home/www/html/tnk-web/ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class

/*
//////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();

if( isset($_REQUEST['start_date']) ) {
    $d_start = $_REQUEST['start_date'];
} else {
    $d_start = 20201201;    // �ƥ��ȸ���
}

if( isset($_REQUEST['end_date']) ) {
    $d_end = $_REQUEST['end_date'];
} else {
    $d_end = 20201231;      // �ƥ��ȸ���
}

access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

$err_flg = false;

///// day �Υ����å�
if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
///// �ǽ���������å����ƥ��åȤ���
if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
    $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
    if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
        $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ���';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
}
///// day �Υ����å�
if (substr($d_end, 6, 2) < 1) $d_end = substr($d_end, 0, 6) . '01';
///// �ǽ���������å����ƥ��åȤ���
if (!checkdate(substr($d_end, 4, 2), substr($d_end, 6, 2), substr($d_end, 0, 4))) {
    $d_end = ( substr($d_end, 0, 6) . last_day(substr($d_end, 0, 4), substr($d_end, 4, 2)) );
    if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
        $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ���';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
}

$_SESSION['s_d_start'] = $d_start;
$_SESSION['s_d_end']   = $d_end  ;
*/

//$d_start = 20201201;    // �ƥ��ȸ���
//$d_end   = 20201231;    // �ƥ��ȸ���

$today_ym = date('Ymd');

//if (substr($today_ym, 6, 2) == 1) {
    
    $d_start = $today_ym;
    $d_end   = substr($today_ym, 0, 6) . '99';
    
    // ���ˡ����ͽ�꤬��Ͽ����Ƥ��ʤ��������å�
    $target_ym = substr($d_start,0,6);
    $query = "SELECT kanryou FROM month_first_sales_plan WHERE kanryou LIKE '{$target_ym}%' LIMIT 1";
    if( getResult2($query, $res_chk) > 0 ) {
        //$_SESSION['s_sysmsg'] .= "���ͽ��ϴ�����Ͽ����Ƥ��ޤ���{$d_start} �� {$d_end}";
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        //echo "���ͽ��ϴ�����Ͽ����Ƥ��ޤ���$d_start �� $d_end \n";
        exit();
    }
    
    //////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
    $query = sprintf("select
                            a.kanryou                     AS ��λͽ����,  -- 0
                            CASE
                                WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE a.plan_no
                            END                           AS �ײ��ֹ�,    -- 1
                            CASE
                                WHEN trim(a.parts_no) = '' THEN '---'
                                ELSE a.parts_no
                            END                           AS �����ֹ�,    -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END                           AS ����̾,      -- 3
                            a.plan -a.cut_plan - a.kansei AS ����,        -- 4
                            (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                          AS ����ñ��,    -- 5
                            Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                          AS ���,        -- 6
                            a.line_no                     AS �饤��No     -- 7
                      FROM
                            assembly_schedule as a
                      left outer join
                            miitem as m
                      on a.parts_no=m.mipn
                      left outer join
                            material_cost_header as mate
                      on a.plan_no=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                      left outer join
                            product_support_master AS groupm
                      on a.parts_no=groupm.assy_no
                      WHERE a.kanryou>=%d AND a.kanryou<=%d AND (a.plan -a.cut_plan) > 0 AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F' AND (a.plan -a.cut_plan - kansei) > 0
                      order by a.kanryou
                      ", $d_start, $d_end);
    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>���ͽ��Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    } else {
        $num = count($field);       // �ե�����ɿ�����
        for ($r=0; $r<$rows; $r++) {
            $res[$r][3] = mb_convert_kana($res[$r][3], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        }
    }
    
    for ($r=0; $r<$rows; $r++) {
        $set_arr = "";  // ��Ͽ���������
        for ($i=0; $i<$num; $i++) {    // �쥳���ɿ�ʬ���֤�
            if ($i >= 8) break;
            $set_arr[$i] = $res[$r][$i];
        }
    
        if( $set_arr[5] == 0 ) {
            $insert_qry = "INSERT INTO month_first_sales_plan (kanryou, plan_no, parts_no, midsc, plan, line_no ) VALUES ('{$set_arr[0]}', '{$set_arr[1]}', '{$set_arr[2]}', '{$set_arr[3]}', '{$set_arr[4]}', '{$set_arr[7]}');";
        } else {
            $insert_qry = "INSERT INTO month_first_sales_plan (kanryou, plan_no, parts_no, midsc, plan, partition_price, price, line_no) VALUES ('{$set_arr[0]}', '{$set_arr[1]}', '{$set_arr[2]}', '{$set_arr[3]}', '{$set_arr[4]}', '{$set_arr[5]}', '{$set_arr[6]}', '{$set_arr[7]}');";
        }
        if( query_affected($insert_qry) <= 0 ) {
            $err_flg = true;
    //        $_SESSION['s_sysmsg'] .= "���ͽ����Ͽ���ԡ�({$r}){$set_arr[5]}";
    //        $_SESSION['s_sysmsg'] .= $insert_qry;
        }
    
    }
//}

/*
if( $err_flg ) {
    $_SESSION['s_sysmsg'] .= "���ͽ�����Ͽ�˼��Ԥ��Ƥ���쥳���ɤ�����ޤ���";
} else {
    $_SESSION['s_sysmsg'] .= "���ͽ�����Ͽ���������ޤ�����";
}

header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
exit();
*/

?>
