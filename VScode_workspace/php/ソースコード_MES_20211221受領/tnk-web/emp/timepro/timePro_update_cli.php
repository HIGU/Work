#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �����५���ɤ��ǹ����(�жС����)DAYLY.TXT��ǡ����١����ع���    CLI�� //
// Copyright (C) 2007-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/28 Created  timePro_update_cli.php                               //
// 2007/08/29 UPDATE�� timepro = {$data} �� timepro = '{$data}'�ؽ���       //
// 2007/08/31 Time Pro XG �ȤΥ����ߥ󥰹�碌���å����ɲ�                //
//            ����Хǡ�����ȤäƸ�����ǡ����Υ����å��ȶ������������ɲ�  //
// 2008/10/09 ����Webɽ���ΰ٥�����ץ�Υǡ�����ư����������               //
//           ���������飲����)����(�������飴�����ʺ����)���ѹ�       ��ë //
// 2017/05/16 timepro�ǡ��������ѥǡ������Ѵ���Ͽ���ɲ�              ��ë //
// 2017/08/02 ����MC���ɲ� ��ȴ���б�                                  ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");
// require_once ('/home/www/html/tnk-web/function.php');

$fpa = fopen('/tmp/timepro.log', 'a');  // �����ѥ��ե�����ؤν���ߤǥ����ץ�

///// Time Pro XG �ȤΥ����ߥ󥰤��碌�뤿�� Wait
while (date('s') < 40) {    // Time Pro ����ʬ10�ä���29�äޤǤ˽�������λ���Ƥ��뤿��
    sleep(2);
}

$log_date = date('Y-m-d H:i:s');        // �����ѥ�������
/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    // query_affected_trans($con, 'BEGIN');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}

$file_orign  = '/home/guest/timepro/DAYLY.TXT';
$file_debug  = "{$currentFullPathName}/debug/debug-DAYLY.TXT";
$file_backup  = "{$currentFullPathName}/backup/backup-DAYLY.TXT";
///// �����ե�����Υ����ॹ����פ����
$save_file_time = "{$currentFullPathName}/timestamp.txt";
if (file_exists($save_file_time)) {
    $fpt  = fopen($save_file_time, 'r');
    $timestamp = fgets($fpt, 50);
    fclose($fpt);
} else {
    $timestamp = '';
}
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $now = date('Ymd His', filemtime($file_orign));
    if ($now == $timestamp) {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa, "$log_date DAYLY.TXT���ѹ�����Ƥ��ʤ������������ߤ��ޤ���\n");
        fclose($fpa);
        exit();
    } else {
        $fpt  = fopen($save_file_time, 'w');
        fwrite($fpt, $now);
        fclose($fpt);
    }
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $fpb = fopen($file_backup, 'w');     // backup �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $no_upd = 0;    // ̤�ѹ��ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgets($fp, 300);     // �¥쥳���ɤ�255�Х��ȤʤΤǤ���ä�;͵��
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $data = trim($data);       // 179��255�Υ��ڡ�������
        ///// �Хå����åפؽ����
        fwrite($fpb, "{$data}\n");
        if ($data == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date ���ԤʤΤ����Ф��ޤ���\n");
            continue;
        }
        ////////// �ǡ�����¸�ߥ����å�
        $query = "
            SELECT * FROM timepro_daily_data WHERE timepro_index(timepro) = timepro_index('{$data}')
        ";
        if (getUniResult($query, $res_chk) > 0) {
            if ($res_chk === $data) {   // ===�����(�����碌�Ƥ���)
                ///// �ǡ������ѹ���̵�� �ʤˤ⤷�ʤ�
                $no_upd++;
            } else {
                ///// �ѹ����� update ����
                $query = "
                    UPDATE timepro_daily_data SET timepro = '{$data}' WHERE timepro_index(timepro) = timepro_index('{$data}')
                ";
                if (query_affected($query) <= 0) {      // �����ѥ����꡼�μ¹�
                    $log_date = date('Y-m-d H:i:s');
                    fwrite($fpa, "$log_date {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                    $rec_ng++;
                    ////////////////////////////////////////// Debug start
                    fwrite($fpw, "$query \n");              // debug
                    break;                                  // debug
                    ////////////////////////////////////////// Debug end
                } else {
                    $rec_ok++;
                    $upd_ok++;
                }
            }
        } else {    //////// ������Ͽ
            $query = "
                INSERT INTO timepro_daily_data VALUES ('{$data}')
            ";
            if (query_affected($query) <= 0) {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date {$rec}:�쥳�����ܤ�INSERT�˼��Ԥ��ޤ���!\n");
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fclose($fpb);       // backup
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date TimePro�ǡ������� : {$rec_ok}/{$rec} ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date TimePro�ǡ������� : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date TimePro�ǡ������� : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpa, "$log_date TimePro�ǡ������� : {$no_upd}/{$rec} �� ̤�ѹ� \n");
    // ������ץ�ǡ����򽢶Ƚ�����ܥǡ�������Ͽ    
    // ��Ͽ�о����դμ�����41�����ޤǡ�
    $date  = getdate();
    $stamp = mktime(
                  $date["hours"]
                , $date["minutes"]
                , $date["seconds"]
                , $date["mon"]
                , $date["mday"] - 41    // 41����
                , $date["year"]
             );
    $date = getdate($stamp);
    $year = $date["year"];
    $month = $date["mon"];
    $day = $date["mday"];
    if ($month<10) {
        $month = '0' . $month;
    }
    if ($day<10) {
        $day = '0' . $day;
    }
    $str_date = $year . $month . $day;      // ��Ͽ�о�����
    
    $working_data = array();
    
    $query = "SELECT substr(timepro, 3, 6) AS �Ұ��ֹ�    -- 00
                    ,substr(timepro, 17, 8) AS ǯ��         -- 01
                    ,substr(timepro, 25, 2) AS ����         -- 02
                    ,substr(timepro, 27, 2) AS ������     -- 03
                    ,substr(timepro, 173, 2) AS �Ժ���ͳ    -- 04
                    ,substr(timepro, 33, 4) AS �жл���     -- 05
                    ,substr(timepro, 41, 4) AS ��л���     -- 06
                    ,substr(timepro, 79, 6) AS �������     -- 07
                    ,substr(timepro, 97, 6) AS ��Ĺ����     -- 08
                    ,substr(timepro, 85, 6) AS ��л���     -- 09
                    ,substr(timepro, 91, 6) AS �ĶȻ���     -- 10
                    ,substr(timepro, 109, 6) AS ����Ķ�    -- 11
                    ,substr(timepro, 115, 6) AS �ٽл���    -- 12
                    ,substr(timepro, 121, 6) AS �ٽлĶ�    -- 13
                    ,substr(timepro, 127, 6) AS �ٽп���    -- 14
                    ,substr(timepro, 155, 6) AS ˡ�����    -- 15
                    ,substr(timepro, 161, 6) AS ˡ��Ķ�    -- 16
                    ,substr(timepro, 133, 6) AS �������    -- 17
                    ,substr(timepro, 37, 2) AS �жУͣ�     -- 18
                    ,substr(timepro, 103, 6) AS �������    -- 19
                    ,substr(timepro, 167, 6) AS ˡ�꿼��    -- 20
                    ,substr(timepro, 139, 6) AS ���ѳ���    -- 21
                    ,substr(timepro, 175, 1) AS ���׶�ʬ    -- 22
                    ,substr(timepro, 45, 2)  AS ��Уͣ�    -- 23
                    ,substr(timepro, 53, 2)  AS ���Уͣ�    -- 24
              FROM timepro_daily_data 
              WHERE substr(timepro, 17, 8) >= {$str_date} 
              ORDER BY �Ұ��ֹ� , ǯ��;
            ";
    if (($rows = getResultWithField2($query, $field, $working_data)) <= 0) {
        $num = 0;
    } else {
        $num = count($field) + 1;
        $last_date = date('Y-m-d H:i:s');
        for ($r=0; $r<$rows; $r++) {
            $query_chk = sprintf("SELECT * FROM working_hours_report_data_new WHERE uid='%s' AND working_date=%d", $working_data[$r][0], $working_data[$r][1]);
            $res_chk = array();
            if ( getResult($query_chk, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
                $sql = "
                        UPDATE working_hours_report_data_new SET
                          calendar='{$working_data[$r][3]}', absence='{$working_data[$r][4]}', str_time='{$working_data[$r][5]}'
                        , end_time='{$working_data[$r][6]}', fixed_time='{$working_data[$r][7]}', extend_time='{$working_data[$r][8]}'
                        , earlytime='{$working_data[$r][9]}', overtime='{$working_data[$r][10]}', midnight_over='{$working_data[$r][11]}'
                        , holiday_time='{$working_data[$r][12]}', holiday_over='{$working_data[$r][13]}', holiday_mid='{$working_data[$r][14]}'
                        , legal_time='{$working_data[$r][15]}', legal_over='{$working_data[$r][16]}', late_time='{$working_data[$r][17]}'
                        , str_mc='{$working_data[$r][18]}', early_mid='{$working_data[$r][19]}', legal_mid='{$working_data[$r][20]}'
                        , private_out='{$working_data[$r][21]}', total_div='{$working_data[$r][22]}', end_mc='{$working_data[$r][23]}'
                        , out_mc='{$working_data[$r][24]}', last_date='$last_date'
                        WHERE uid='{$working_data[$r][0]}' AND working_date='{$working_data[$r][1]}'
                        ;
                    ";
            } else {                                        // ��Ͽ�ʤ� INSERT �ɲ�
                $sql = "
                        INSERT INTO working_hours_report_data_new
                        (uid, working_date, working_day, calendar, absence, str_time, end_time, fixed_time, extend_time, earlytime, overtime, midnight_over, holiday_time, holiday_over, holiday_mid, legal_time, legal_over, late_time, str_mc, early_mid, legal_mid, private_out, total_div, end_mc, out_mc, regdate, last_date)
                        VALUES
                        ('{$working_data[$r][0]}', '{$working_data[$r][1]}', '{$working_data[$r][2]}', '{$working_data[$r][3]}', '{$working_data[$r][4]}', '{$working_data[$r][5]}', '{$working_data[$r][6]}', '{$working_data[$r][7]}', '{$working_data[$r][8]}', '{$working_data[$r][9]}', '{$working_data[$r][10]}', '{$working_data[$r][11]}', '{$working_data[$r][12]}', '{$working_data[$r][13]}', '{$working_data[$r][14]}'
                        , '{$working_data[$r][15]}', '{$working_data[$r][16]}', '{$working_data[$r][17]}', '{$working_data[$r][18]}', '{$working_data[$r][19]}', '{$working_data[$r][20]}', '{$working_data[$r][21]}', '{$working_data[$r][22]}', '{$working_data[$r][23]}', '{$working_data[$r][24]}', '$last_date', '$last_date')
                        ;
                    ";
            }
            query_affected($sql);
        }
    }
    
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
}
/////////// commit �ȥ�󥶥������λ
// query_affected_trans($con, 'COMMIT');
// echo $query . "\n";  // debug
fclose($fpa);      ////// ȯ��ײ�κ��ۥǡ����ѥ�����߽�λ


/************** ������쥳�������Υǡ�����ȤäƸ�����ǡ����Υ����å��ȶ����������� **************/
///// 2007/08/31 ADD
`{$currentFullPathName}/../../industry/order/inspection/inspection_force_hold_cli.php`;

?>
