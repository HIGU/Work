#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����ɸ������Ψ�׻�����Ͽ cron.d tnk_daily �����Ǽ¹�                     //
// �׻��롼�� ����Ⱦ������ݤ�ɸ�������ֹ��ȴ���Ф�                        //
//            ����Ⱦ��������ɸ��и��̤�ȴ�Ф�������νи�Ψ��׻�        //
//             �ʣ��˻Ϥ���оݷ�(��Ⱦ���ǽ���)������Ψ��̵����Τ�ȴ��     //
//                  ��A�ˤ���������ֹ����Ͽ�ѤߤʤΤ����                 //
//                  ��B�ˤʤ���������ֹ�ȴ�Ф���                           //
//                       (a) �����ֹ��ȴ�Ф�������ƥ����å�����Ͽ         //
//             �ʣ�����Ͽ����Ƥ��������ֹ������Ψ��̵����Τ�ȴ�Ф�       //
//                   ���λ�2000��ޤǡʽ������Ť��Τǣ������餤�ݤ��Ƥ��� //
//             �ʣ����о����ʤǽи�Ψ�׻�������νиˤ��ʤ�����ֹ���     //
// Copyright (C) 2017-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/11/13 Created   ctoku_allocation_cal.php                            //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
//ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "����ɸ������Ψ�׻�������\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/ctoku_allocation_cal.php\n");
echo "/home/www/html/tnk-web/system/daily/ctoku_allocation_cal.php\n";

/////////// ���եǡ����μ���
$target_ym   = date('Ym');          //201710
$b_target_ym = $target_ym - 100;    //201610
$today       = date('Ymd');         //20171012
$b_today     = $today - 10000;      //20161012

// ��Ⱦ���Υǡ��������
$end_mm  = substr($target_ym, -2, 2);
$end_yy  = substr($target_ym,  0, 4);
$end_mm  = $end_mm * 1;
if ($end_mm > 9) {          // ����(10��12��)�ξ��
    $str_ym  = $end_yy . '04';
    $str_ymd = $str_ym . '01';
    $str_ym  = $str_ym * 1;
    $str_ymd = $str_ymd * 1;
    $end_ym  = $end_yy . '09';
    $end_ymd = $end_ym . '31';
    $end_ym  = $end_ym * 1;
    $end_ymd = $end_ymd * 1;
} elseif ($end_mm < 4)  {   // ����(1��3��)�ξ��
    $end_yy  = $end_yy * 1;
    $str_ym  = $end_yy - 1 . '04';
    $str_ymd = $str_ym . '01';
    $str_ym  = $str_ym * 1;
    $str_ymd = $str_ymd * 1;
    $end_ym  = $end_yy - 1 . '09';
    $end_ymd = $end_ym . '31';
    $end_ym  = $end_ym * 1;
    $end_ymd = $end_ymd * 1;
} else {                    // ����ξ��
    $end_ym  = $end_yy . '03';
    $end_ymd = $end_ym . '31';
    $end_ym  = $end_ym * 1;
    $end_ymd = $end_ymd * 1;
    $end_yy  = $end_yy * 1;
    $str_ym  = $end_yy - 1 . '10';
    $str_ymd = $str_ym . '01';
    $str_ym  = $str_ym * 1;
    $str_ymd = $str_ymd * 1;
    
}
/* �ƥ�����
$str_ym  = 201704;
$str_ymd = 20170401;
$end_ym  = 201709;
$end_ymd = 20170931;
*/
/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date ����ɸ������Ψ db_connect() error \n";
    fwrite($fpa,"$log_date ����ɸ������Ψ db_connect() error \n");
    fwrite($fpb,"$log_date ����ɸ������Ψ db_connect() error \n");
    exit();
}
$query_g = sprintf("SELECT parts_no FROM inventory_ctoku_par WHERE ctoku_ym=%d and ctoku_allo is NULL", $end_ym);
$res_g   = array();
$field_g = array();
if (($rows_g = getResultWithField3($query_g, $field_g, $res_g)) > 0) {
} else {
    $query_g2 = sprintf("SELECT parts_no FROM inventory_ctoku_par WHERE ctoku_ym=%d", $end_ym);
    $res_g2   = array();
    $field_g2 = array();
    if (($rows_g2 = getResultWithField3($query_g2, $field_g2, $res_g2)) > 0) {
        exit();
    } else {
        // �о����ʤμ���
        $query_t = getQueryStatement1($end_ym, $str_ym, $end_ymd, $str_ymd);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = getResultWithField3($query_t, $field_t, $res_t)) > 0) {
            for ($r=0; $r<$rows_t; $r++) {
                $query_u = getQueryStatement4($res_t[$r][0], $end_ym, $str_ym);
                $res_u   = array();
                $field_u = array();
                if (($rows_u = getResultWithField3($query_u, $field_u, $res_u)) > 0) {
                    // �����å��ǥǡ��������������ΰ١��ʤˤ⤷�ʤ�
                } else {
                    // �����å��ǥǡ�����̵���Τ�����DB��Ͽ
                    $query = sprintf("INSERT INTO inventory_ctoku_par (parts_no, ctoku_ym) values('%s', %d)", $res_t[$r][0], $end_ym);
                    query_affected_trans($con, $query);
                }
            }
        }
    }
}

$query_p = sprintf("SELECT parts_no FROM inventory_ctoku_par WHERE ctoku_ym=%d and ctoku_allo is NULL limit 2000", $end_ym);
$res_p   = array();
$field_p = array();
if (($rows_p = getResultWithField3($query_p, $field_p, $res_p)) > 0) {
    for ($r=0; $r<$rows_p; $r++) {
        // ����ƥ����å�
        $query_s = getQueryStatement2($res_p[$r][0], $end_ymd, $str_ymd);
        $res_s   = array();
        $field_s = array();
        if (($rows_s = getResultWithField3($query_s, $field_s, $res_s)) > 0) {
            $query_h = getQueryStatement3($res_p[$r][0], $end_ymd, $str_ymd);
            $res_h   = array();
            $field_h = array();
            if (($rows_h = getResultWithField3($query_h, $field_h, $res_h)) > 0) {
                $toku_num   = 0;
                $hyo_num    = 0;
                $total_num  = 0;
                $toku_ritsu = 0;
                
                $toku_num  = $res_s[0][0];
                $hyo_num   = $res_h[0][0];
                $total_num = $toku_num + $hyo_num;
                if ($toku_num > 0) {
                    $toku_ritsu = round(($toku_num / $total_num),5);
                    ////////// Insert Start
                    $query = sprintf("UPDATE inventory_ctoku_par SET total_num=%d, ctoku_num=%d, hyo_num=%d, ctoku_allo=%1.4f WHERE parts_no='%s' and ctoku_ym=%d", $total_num, $toku_num, $hyo_num, $toku_ritsu, $res_p[$r][0], $end_ym);
                    //$query = sprintf("INSERT INTO inventory_ctoku_par (parts_no, ctoku_num) VALUES ('%s', %d)", $res_t[$r][0], $toku_num);
                    query_affected_trans($con, $query);
                } else {
                    $query = sprintf("DELETE FROM inventory_ctoku_par WHERE parts_no='%s' and ctoku_ym=%d", $res_p[$r][0], $end_ym);
                    query_affected_trans($con, $query);
                }
            }
        }
    }
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ

exit();

    ///// List��   ����ɽ��SQL���ơ��ȥ��ȼ���
    // �о������ֹ�ΰ��������
    function getQueryStatement1($target_ym, $b_target_ym, $today, $b_today)
    {
        $query = "SELECT
                            DISTINCT a.parts_no    as �����ֹ�        -- 04
                        FROM
                            act_payable AS a
                        LEFT OUTER JOIN 
                            order_plan AS p
                                using(sei_no)
                        LEFT OUTER JOIN
                            miitem ON (a.parts_no = mipn)
                        LEFT OUTER JOIN
                            parts_stock_master AS m ON (m.parts_no=a.parts_no)
                        WHERE act_date>={$b_today} and act_date<={$today} and a.div='C' and a.parts_no<>'' and
                                a.parts_no not like '##%' and LENGTH(a.parts_no)=9 and (a.parts_no like 'C%' or a.parts_no like 'S%') and p.kouji_no NOT like 'SC%%'
                  UNION
                  SELECT
                            DISTINCT inv.parts_no                as �����ֹ�        -- 0
                        FROM
                            inventory_monthly as inv
                        LEFT OUTER JOIN
                            inventory_monthly_ctoku as o
                                on inv.parts_no = o.parts_no
                        WHERE inv.invent_ym>={$b_target_ym} and inv.invent_ym<={$target_ym} and inv.num_div='5' and o.parts_no is null
        ";
        return $query;
    }
    // �����������и˿���׻�
    function getQueryStatement2($parts_no, $today, $b_today)
    {
        $query = "SELECT 
                        SUM(p.stock_mv) 
                    FROM parts_stock_history as p
                         left outer join assembly_schedule as s
                            on(p.plan_no=s.plan_no) 
                    WHERE p.parts_no='{$parts_no}' and p.ent_date>={$b_today} and p.ent_date <={$today}
                          and p.out_id <>'' and p.plan_no like 'C%' and s.note15 like 'SC%'
        ";
        return $query;
    }
    // �������ɸ��и˿���׻�
    function getQueryStatement3($parts_no, $today, $b_today)
    {
        $query = "SELECT 
                        SUM(p.stock_mv) 
                    FROM parts_stock_history as p
                         left outer join assembly_schedule as s
                            on(p.plan_no=s.plan_no) 
                    WHERE p.parts_no='{$parts_no}' and p.ent_date>={$b_today} and p.ent_date <={$today}
                          and p.out_id <>'' and p.plan_no like 'C%' and s.note15 not like 'SC%'
        ";
        return $query;
    }
    // ����߸˺ƥ����å�
    function getQueryStatement4($parts_no, $today, $b_today)
    {
        $query = "SELECT 
                        *
                    FROM inventory_ctoku_par as inv
                         LEFT OUTER JOIN inventory_monthly_ctoku as o
                            on inv.parts_no = o.parts_no
                    WHERE inv.parts_no='{$parts_no}' and inv.ctoku_ym={$today} and o.invent_ym>={$b_today} and o.invent_ym <={$today}
                          and o.parts_no is not NULL
        ";
        return $query;
    }
?>
