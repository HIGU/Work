#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �����θ��Ѥ�10ʬ��˽�󤷸�����������Ͽ ��ư Update                     //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/20 Created  order_data_acceptance_update_cron.php                //
// 2005/05/26 php-5.0.2-cli �� php ���ѹ�(�ѹ������Ǥ�5.0.4)                //
//            ���ߤ��������򲼵��˵��Ҥ���                                  //
//            1.ken_date(������)�Τ���Ͽ���Ƥ���ΤǸ��ʿ������������ʤ�    //
//            2.genpin siharai ����Ͽ�����硢�������˿��̤����Ϥ��Ƥ�餦 //
//              ɬ�פ����롣�ޤ���order_process order_plan �Ȥ�Ʊ����ɬ��   //
//            3.�������ٻ��֤������ AS/400�Ȥ�Ʊ���Ǿ嵭�ϲ�ä���뤬���� //
//              ���ϲ�ä��ʤ������β����Τ��� order_data��UPDATE�����   //
//            4.acceptance_kensa �θ�����λ���֤򸫤Ƹ����ųݤ両�������   //
//              ���̤�ɽ������ɽ�������椹��褦�˲������롣                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/order_data_acceptance.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$today = date('Ymd');

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    exit;
}
////////// order_data ���оݥ쥳���ɸ���
$query = "select ord.order_seq
            from
                acceptance_kensa    AS acc
            left outer join
                order_data          AS ord      using(order_seq)
            where
                acc.end_timestamp IS NOT NULL
                and
                (CURRENT_TIMESTAMP - acc.end_timestamp) >= (interval '10 minute')
                and
                ord.ken_date = 0
";
$res = array();
if ( ($rows = getResultTrs($con, $query, $res)) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
    fwrite($fpa, "$log_date �оݥǡ����ʤ� \n");
    query_affected_trans($con, 'commit');
    exit();
} else {
    for ($i=0; $i<$rows; $i++) {
        $query = "UPDATE order_data SET ken_date={$today} where order_seq={$res[$i][0]}";
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date ����������Ͽ�˼���:ȯ��Ϣ��:{$res[$i][0]}:������:{$today}\n");
        }
    }
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fwrite($fpa, "$log_date ������λ:�оݷ��:{$rows} \n");
fclose($fpa);      ////// �����ѥ�����߽�λ
exit();
?>
