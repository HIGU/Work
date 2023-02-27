#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �������������Ǥ򥿥��५���ɤ��ǹ���֤����椹��(����˺��λ��ߤ�) CLI�� //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/31 Created  inspection_force_hold_cli.php                        //
// 2007/09/04 $_ENV['HOSTNAME']/$_SERVER['HOSTNAME']�ϻ��ѤǤ��ʤ���SYSTEM��//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)

function main()
{
    $currentFullPathName = realpath(dirname(__FILE__));
    require_once ("{$currentFullPathName}/../../../function.php");
    // require_once ('/home/www/html/tnk-web/function.php');
    
    $fpa = fopen('/tmp/timepro.log', 'a');  // �����ѥ��ե�����ؤν���ߤǥ����ץ�
    $log_date = date('Y-m-d H:i:s');        // �����ѥ�������
    
    /////////// begin �ȥ�󥶥�����󳫻�
    if ($con = db_connect()) {
        query_affected_trans($con, 'BEGIN');
    } else {
        fwrite($fpa, "$log_date db_connect() error \n");
        fclose($fpa);
        return;
    }
    /********** ���ǳ��Ϥν��� **********/
    setForceHoldStart($fpa, $con);
    
    /********** ���ǽ�λ�ν��� **********/
    setForceHoldEnd($fpa, $con);
    
    /////////// commit �ȥ�󥶥������λ
    query_affected_trans($con, 'COMMIT');
    fclose($fpa);      ////// ȯ��ײ�κ��ۥǡ����ѥ�����߽�λ
    return;
}
main();
exit();


/********** ���ǳ��Ϥν��� **********/
function setForceHoldStart($fpa, $con)
{
    /********** ���ߤθ�����ǡ�������� **********/
    $query = "
        SELECT ken.uid FROM acceptance_kensa AS ken
        WHERE end_timestamp IS NULL AND str_timestamp IS NOT NULL AND
            (SELECT str_timestamp FROM inspection_holding WHERE order_seq = ken.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL LIMIT 1)
            IS NULL
        GROUP BY ken.uid
    ";
    if ( ($rows=getResultTrs($con, $query, $res)) <= 0) {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa, "$log_date ���ߤθ�����ǡ���������ޤ���\n");
        return;
    }
    for ($i=0; $i<$rows; $i++) {
        /********** �嵭�μҰ��ֹ����Ф����������å� **********/
        $uid = $res[$i][0];
        $date = date('Ymd');
        $query = "
            SELECT end_time FROM timepro_get_time(TEXT '{$uid}', TEXT '{$date}')
        ";
        getUniResTrs($con, $query, $end_time);
        if ($end_time == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date $uid �������Ф��Ƥޤ���\n");
        } else {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date $uid �����{$end_time}ʬ����Ф����ΤǶ������Ǥ��ޤ�\n");
            /********** �嵭����л��֤�����Ұ��θ�����ǡ�������� (ȯ��Ϣ�֤�����) **********/
            $query = "
                SELECT ken.order_seq, ken.uid, ken.str_timestamp FROM acceptance_kensa AS ken
                WHERE end_timestamp IS NULL AND str_timestamp IS NOT NULL AND ken.uid = '{$uid}' AND
                    (SELECT str_timestamp FROM inspection_holding WHERE order_seq = ken.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL LIMIT 1)
                    IS NULL
            ";
            if ( ($rows2=getResultTrs($con, $query, $res2)) <= 0) {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date $uid ����θ�����ǡ������ʤ��ʤ�ޤ����Τ����Ǥ���ߤ��ޤ�\n");
            } else {
                /********** ȯ��Ϣ�֤ȼҰ��ֹ�� ���� �������� ���� **********/
                for ($j=0; $j<$rows2; $j++) {
                    $sql = "
                        INSERT INTO inspection_holding (order_seq, str_timestamp, client, uid)
                        VALUES ({$res2[$j][0]}, '{$date} {$end_time}00', 'SYSTEM', '{$uid}')
                        ;
                        INSERT INTO inspection_force_hold (order_seq, str_timestamp, uid)
                        VALUES ({$res2[$j][0]}, '{$date} {$end_time}00', '{$uid}')
                    ";
                    if (query_affected_trans($con, $sql) <= 0) {
                        $log_date = date('Y-m-d H:i:s');
                        fwrite($fpa, "$log_date $uid ����θ�����ǡ����ζ������Ǥ˼��Ԥ��ޤ���\n");
                    } else {
                        $log_date = date('Y-m-d H:i:s');
                        fwrite($fpa, "$log_date $uid ����θ�����ǡ����������Ǥ��ޤ���\n");
                    }
                }
            }
        }
    }
}

/********** ���ǽ�λ�ν��� **********/
function setForceHoldEnd($fpa, $con)
{
    /********** �������Ǥ�����Τ�Ұ��ֹ����� **********/
    $date = date('Ymd');
    $query = "
        SELECT uid, order_seq, str_timestamp FROM inspection_force_hold
        WHERE end_timestamp IS NULL AND CAST(str_timestamp AS DATE) != DATE '{$date}'
    ";
    if ( ($rows=getResultTrs($con, $query, $res)) <= 0) {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa, "$log_date �������붯�����ǥǡ���������ޤ���\n");
        return;
    } else {
        for ($i=0; $i<$rows; $i++) {
            /********** �嵭�μҰ����жФ����������å� **********/
            $uid = $res[$i][0];
            $order_seq = $res[$i][1];
            $query = "
                SELECT start_time FROM timepro_get_time(TEXT '{$uid}', TEXT '{$date}')
            ";
            getUniResTrs($con, $query, $start_time);
            if ($start_time == '') {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date $uid ����ϽжФ��Ƥ��ޤ���\n");
            } else {
                /********** �жФ��Ƥ���Τ����ǽ�λ�Τ��ṹ�� **********/
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date $uid �����{$start_time}ʬ�˽жФ����ΤǶ������Ǥ�λ���ޤ�\n");
                $sql = "
                    UPDATE inspection_holding SET end_timestamp = '{$date} {$start_time}00' WHERE order_seq = {$order_seq} AND str_timestamp = '{$res[$i][2]}'
                    ;
                    UPDATE inspection_force_hold SET end_timestamp = '{$date} {$start_time}00' WHERE order_seq = {$order_seq} AND str_timestamp = '{$res[$i][2]}'
                ";
                if (query_affected_trans($con, $sql) <= 0) {
                    $log_date = date('Y-m-d H:i:s');
                    fwrite($fpa, "$log_date $uid ����ζ������Ǥν�λ�˼��Ԥ��ޤ���\n");
                } else {
                    $log_date = date('Y-m-d H:i:s');
                    fwrite($fpa, "$log_date $uid ����ζ������Ǥ�λ���ޤ���\n");
                }
            }
        }
    }
}


?>
