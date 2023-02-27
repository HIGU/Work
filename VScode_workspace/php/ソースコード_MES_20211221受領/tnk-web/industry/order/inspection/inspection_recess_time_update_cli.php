#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ���������θ����椬�٤߻��֤ޤ�������硢����Ū�����Ǥˤ������     CLI�� //
// �٤߻��֤ν�λ���֥��㥹�Ȥ˵�ư������ȼ�ư�¹Ԥ���                     //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/09/04 Created  inspection_recess_time_update_cli.php                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)

function main()
{
    $currentFullPathName = realpath(dirname(__FILE__));
    require_once ("{$currentFullPathName}/../../../function.php");
    // require_once ('/home/www/html/tnk-web/function.php');
    
    /////////// begin �ȥ�󥶥�����󳫻�
    if ($con = db_connect()) {
        query_affected_trans($con, 'BEGIN');
    } else {
        logWriter('db_connect() error');
        fclose($fpa);
        return;
    }
    /********** �оݥǡ�����Ф����� ���� **********/
    if ( ($rows=getOverlappsInspection($con, $res)) <= 0) {
        logWriter('�٤߻��֤˥����С���åפ��Ƥ��븡����Ϥ���ޤ���');
        return;
    }
    
    /////////// commit �ȥ�󥶥������λ
    query_affected_trans($con, 'COMMIT');
    return;
}
main();
exit();


/********** ���ǳ��Ϥν��� **********/
function logWriter($message)
{
    $fpa = fopen('/tmp/timepro.log', 'a');  // �����ѥ��ե�����ؤν���ߤǥ����ץ�
    $log_date = date('Y-m-d H:i:s');        // �����ѥ�������
    fwrite($fpa, "{$log_date} {$message}\n");
    fclose($fpa);
    return;
}

/********** �оݥǡ������ ���� **********/
function getOverlappsInspection($con, &$res)
{
    ///// ���ߤλ�����٤߻��֤�����
    $endTime = date('H:i:00');
    switch ($endTime) {
    case '12:45:00':
        $strTime = '12:00:00';
        break;
    case '15:10:00':
        $strTime = '15:00:00';
        break;
    case '17:30:00':
        $strTime = '17:15:00';
        break;
    default:
        logWriter('�٤߻��֤ǤϤ���ޤ���');
        return 0;
        ///// �ʲ��ϥƥ����� �嵭���Ԥ򥳥��Ȥˤ��ưʲ��λ��֤����ꤹ�롣
        // $strTime = '15:00:00';
        // $endTime = '15:10:00';
    }
    /********** ���ߤθ�����ǡ�������� **********/
    $query = "
        SELECT ken.order_seq, ken.uid FROM acceptance_kensa AS ken
        WHERE end_timestamp IS NULL
        AND str_timestamp IS NOT NULL
        AND (SELECT str_timestamp FROM inspection_holding WHERE order_seq = ken.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL LIMIT 1)
            IS NULL
        AND CAST(str_timestamp AS TIME) <= TIME '{$strTime}'
    ";
    if ( ($rows=getResultTrs($con, $query, $res)) <= 0) {
        return $rows;
    }
    /********** �٤߻��֤����ǻ��֤Ȥ��ƹ��� **********/
    for ($i=0; $i<$rows; $i++) {
        $date = date('Ymd');
        $sql = "
            INSERT INTO inspection_holding (order_seq, str_timestamp, end_timestamp, client, uid)
            VALUES ({$res[$i][0]}, '{$date} {$strTime}', '{$date} {$endTime}', 'SYSTEM', '{$res[$i][1]}')
        ";
        if (query_affected_trans($con, $sql) <= 0) {
            logWriter("�٤߻��֤����ǻ��ֹ����˼��Ԥ��ޤ�����ȯ��Ϣ��={$res[$i][0]} �桼����={$res[$i][1]}");
        } else {
            logWriter("�٤߻��֤����ǻ��֤򹹿����ޤ�����ȯ��Ϣ��={$res[$i][0]} �桼����={$res[$i][1]}");
        }
    }
    return $rows;
}


?>
