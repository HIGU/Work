<?php
//////////////////////////////////////////////////////////////////////////////
// Ŭ���߸˿��η׻� ���å���                                              //
// Copyright(C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                 //
// Changed history                                                          //
// 2008/06/17 Created   reasonable_stock_calc.php                           //
//////////////////////////////////////////////////////////////////////////////
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

//////////////// ǧ�ڥ����å�
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION["Auth"] <= 2) {
    $_SESSION['s_sysmsg'] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ����������󤫤餪�ꤤ���ޤ���";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

/////// ǯ��μ���
if ($st_ym != 0) {
    $str_date = $st_ym - 300 . "31";
    $end_date = $st_ym + 1 . "01";
} else {
    return false;
}

/////// �߸˷����������ֹ�Ƚи˿���פ����
$query = "
    SELECT   parts_no
            ,sum(stock_mv) 
            FROM parts_stock_history 
            WHERE ent_date > {$str_date} AND ent_date < {$end_date} 
            AND out_id = '2' AND den_kubun < '6' 
            GROUP BY parts_no
";
$res = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    return false;
} else {
    $num = count($field);
}

/////// �߸˷������ưɽ�Ƿײ�No.��%�ǻϤޤ�ʪ�νи˿���פ����
$query_pl = "
    SELECT   parts_no
             ,sum(stock_mv) 
             FROM parts_stock_history 
             WHERE ent_date > {$str_date} AND ent_date < {$end_date} 
             AND out_id='2' AND den_kubun = '6' AND plan_no LIKE '$%%' ESCAPE '$' 
             GROUP BY parts_no
";
$res_pl = array();
if (($rows_pl = getResultWithField2($query_pl, $field_pl, $res_pl)) <= 0) {
    return false;
} else {
    $num_pl = count($field_pl);
}

/////// �и˿���פ˷ײ�No.����ǻϤޤ�ʪ�νи˹�פ�­��
for ($r=0; $r<$rows_pl; $r++) {
    for ($i=0; $i<$rows; $i++) {
        if($res_pl[$r][0] == $res[$i][0]) {
            $res[$i][1] = $res[$i][1] + $res_pl[$r][1];
        }
    }
}

/////// �и˿���סࣳ�ߣ���Ŭ���߸ˤ�׻�
for ($r=0; $r<$rows; $r++) {
    $r_stock[$r] = round($res[$r][1] / 3 * 2, 0);
}

/////// parts_stock_history���裱�쥳���ɤ�tnk_stock�����
/////// tnk_stock�����ʾ�ξ���2000ǯ4������˺߸ˤ����ä���ΤȤʤ�Τ�
/////// ������������裱�쥳���ɤ��ͤˤ���
for ($r=0; $r<$rows; $r++) {
    $parts_no = $res[$r][0];
    $query_in = "
        SELECT   parts_no
                 ,
                 CASE
                     WHEN tnk_stock > 0 THEN ent_date
                     WHEN nk_stock > 0 THEN ent_date
                     ELSE 0
                 END
                 FROM parts_stock_history 
                 WHERE parts_no='{$parts_no}' AND den_kubun <> '7'
                 ORDER BY ent_date ASC
                 LIMIT 1
    ";
    $res_in = array();
    if (($rows_in = getResultWithField2($query_in, $field_in, $res_in)) <= 0) {
        $first_date[$r] = 0;
    } else {
        if ($res_in[0][1] == 0) {
            $first_date[$r] = 0;
        } else {
            $first_date[$r] = $res_in[0][1];
        }
    }
}

/////// �߸˷����������������������ɼ��ʬ5�ʲ���
/////// �������������Ͽ�Ѥߤξ������
for ($r=0; $r<$rows; $r++) {
    if ($first_date[$r] == 0) {
        $parts_no = $res[$r][0];
        $query_in = "
            SELECT   parts_no
                     ,ent_date
                     FROM parts_stock_history 
                     WHERE (in_id='2' OR in_id='1') AND den_kubun < '6' 
                     AND parts_no='{$parts_no}'
                     ORDER BY ent_date ASC
                     LIMIT 1
        ";
        $res_in = array();
        if (($rows_in = getResultWithField2($query_in, $field_in, $res_in)) <= 0) {
            $first_date[$r] = 0;
        } else {
            $first_date[$r] = $res_in[0][1];
        }
    }
}

/////// �߸˷�������ɼ��ʬ���ν��������������ʷײ��ֹ��Ƭ��%�Τ�ΤΤߡ�
$query_in6 = "
    SELECT   parts_no
             ,min(ent_date) 
             FROM parts_stock_history 
             WHERE (in_id='2' OR in_id='1') AND den_kubun = '6' 
             AND plan_no LIKE '$%%' ESCAPE '$' 
             GROUP BY parts_no
             ORDER BY parts_no ASC
";
$res_in6 = array();
if (($rows_in6 = getResultWithField2($query_in6, $field_in6, $res_in6)) <= 0) {
    $num_in6 = 0;
} else {
    $num_in6 = count($field_in6);
}

/////// ��ɼ��ʬ��6�Ƿײ��ֹ��Ƭ��%��ʪ������Ф���������������ִ���
if ($num_in6 != 0) {
    for ($r=0; $r<$rows_in6; $r++) {
        for ($i=0; $i<$rows; $i++) {
            if ($res_in6[$r][0] == $res[$i][0]) {
                $first_date[$i] = $res_in6[$r][1];
            }
        }
    }
}

////////////// Ŭ���߸˥ǡ�������Ͽ
for ($r=0; $r<$rows; $r++) {
    $query = sprintf("SELECT parts_no FROM reasonable_stock WHERE parts_no='%s' AND standard_ym=%d", $res[$r][0], $st_ym);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE����
        $query = sprintf("UPDATE reasonable_stock SET r_stock=%d, shipment_sum=%d, first_date=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE parts_no='%s' AND standard_ym=%d", $r_stock[$r], $res[$r][1], $first_date[$r], $_SESSION['User_ID'], $res[$r][0], $st_ym);                
        if (query_affected($query) <= 0) {
            return false;
        }
    } else {                                    // ��Ͽ�ʤ� INSERT ����   
        $query = sprintf("INSERT INTO reasonable_stock (parts_no, standard_ym, r_stock, shipment_sum, first_date, last_date, last_user)
                            VALUES ('%s', %d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                            $res[$r][0], $st_ym, $r_stock[$r], $res[$r][1], $first_date[$r], $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            return false;
        }
    }
}
return TRUE;    // ��ʸ����ʸ���˰�¸���ʤ��� JavaScript�˹�碌�롣
?>

