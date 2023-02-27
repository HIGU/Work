#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����ײ�ǡ��������������ʡ��� CHECK UPDATE ������             CLI�� //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/22 Created  assembly_schedule_checkUpdate.php                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 300);     // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
} else {
    fwrite($fpa, "$log_date ��Ω�����ײ�� db_connect() error \n");
    exit();
}
fwrite($fpa, "$log_date ��Ω�����ײ����������ʬ�ǡ��������å����� \n");

/////////// �������λ���
$year = date('Y'); $month = date('m');
if ($month == 1) {
    $month = 12;
    $year -= 1;
} else {
    $month -= 1;    // 1������
    $month = sprintf('%02d', $month);
}
$startDate = ($year . $month . '01');
/////////// ��λ���λ���
$year = date('Y'); $month = date('m');
$month += 3;        // 3������
if ($month > 12) {
    $month -= 12;
    $year  += 1;
}
$month = sprintf('%02d', $month);
$endDate = ($year . $month . '01');

/////////// �оݥǡ���ȴ�Ф�
$query = "
    SELECT plan_no
        , parts_no AS �����ֹ�
        , substr(midsc, 1, 20) AS ����̾
        , plan-cut_plan AS �ײ��
        , kansei
        , line_no
    FROM assembly_schedule
    LEFT OUTER JOIN miitem ON(parts_no=mipn)
    WHERE kanryou>={$startDate} AND kanryou<{$endDate} AND plan_no LIKE '@%' AND assy_site='01111'
    AND (plan-cut_plan-kansei) > 0
";
$res = array();
if ( ($rows=getResultTrs($con, $query, $res)) < 1) {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date ��Ω�����ײ� �оȥǡ���������ޤ���$startDate �� $endDate \n");
    query_affected_trans($con, 'ROLLBACK');
    fclose($fpa);
    exit();
}

/////////// ���������å�(����Ū�˰�����̵����Τ����оݤˤ���)
$delCount = 0;
for ($i=0; $i<$rows; $i++) {
    $plan_no = $res[$i][0];
    $query = "
        SELECT parts_no FROM allocated_parts WHERE plan_no='{$plan_no}' LIMIT 1
    ";
    if (getUniResTrs($con, $query, $parts_no) < 1) {
        ///// ����̵���Τ�����
        $del_sql = "
            DELETE FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        if (query_affected_trans($con, $del_sql) < 1) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date ����˼��Ԥ��ޤ������ײ��ֹ桧$plan_no ����̾��{$res[$i][2]} \n");
        } else {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date ������ޤ������ײ��ֹ桧$plan_no ����̾��{$res[$i][2]} \n");
        }
        $delCount++;
    }
}
$log_date = date('Y-m-d H:i:s');
if ($delCount <= 0) {
    fwrite($fpa, "$log_date ����оݤ�����ޤ���Ǥ����� \n");
}
fwrite($fpa, "$log_date ��Ω�����ײ����������ʬ�ǡ��������å���λ \n");

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'COMMIT');
fclose($fpa);      ////// �����ѥ�����߽�λ
?>
