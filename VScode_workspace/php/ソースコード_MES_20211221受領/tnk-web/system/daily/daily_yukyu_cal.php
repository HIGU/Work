#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ͭ����������׻�����                                                     //
// Copyright(C) 2015-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Cron�ǣ���ʬ�����˼�ư��ͭ�����������׻����Ƥ������Ȥ�                 //
// �Ȳ���˽������Ť��ʤ�Τ��ɤ�(�̾�¹Ի�20ʬ���礤������)               //
// Changed history                                                          //
// 2015/01/29 �������� daily_yukyu_cal.php                                  //
// 2015/03/20 cron�Ǽ¹Ԥ�����ѹ�                                          //
// 2015/03/24 timepro�λ��ֵٷ׻���AS�˹�碌���ѹ�                         //
// 2015/03/27 �����Ѥ�ä�����ư�������Ū�˺Ƹ��ƥ���                      //
// 2015/04/16 ���ս�λǯ�������ʤ����(ͭ�빹���Τ߽�λ�ξ��)��            //
//            timepro�Υǡ�����YYYY0401�������ޤǼ������Ʒ׻������ͤ��ѹ�   //
//            �㳰Ū�˽�λǯ�������ʤ��ͤ⤤�뤬�׻��λ��֤򾯤ʤ��Ǥ���    //
//            ��0���������ޤǤ��Ͼ��ʤ��Ϥ���                             //
// 2015/04/17 16�����ѹ������ޤ��ԤäƤʤ��ä��ΤǺ��ѹ�                    //
// 2018/09/25 ü���η׻����ھ�ߤ����ʤΤǽ���                              //
//            AS��PGM�ѹ���L55�򥳥��Ȳ��                                //
// 2018/10/17 ����Ұ���ϫƯ���֤�3��ʤΤǡ�100���ɲ�                      //
//            AS��PGM�ѹ����2018/09/25�Ǹ�������PGM����                    //
// 2018/10/22 AS��PGM�ѹ���λ�ˤ�ꡢ�������PGM���ѹ�                      //
// 2018/10/25 ����Ұ��ζ�̳���֤�7����30ʬ�ʤ�ʬ����ȯ���������           //
//            AS����Ͽ��730�ʤɡ���ʬ�����Ϥ����PGM����                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

// ͭ��ķ׻�
$timeDate = date('Ym');
$today_ym = date('Ymd');
$tmp = $timeDate - 195603;     // ���׻�����195603
$tmp = $tmp / 100;             // ǯ����ʬ����Ф�
$ki  = ceil($tmp);             // roundup ��Ʊ��    
$query = "
            SELECT
                 before_day     AS ������������     --  0
                ,current_day    AS ����ȯ������     --  1
                ,day_holiday    AS ͭ���������     --  2
                ,half_holiday   AS Ⱦ��ͭ����     --  3
                ,time_holiday   AS ���ֵټ���ʬ     --  4
                ,total_holiday  AS ������׼���     --  5
                ,end_ymd        AS ���ս�λǯ����   --  6
                ,work_time_p    AS �ѡ���ϫƯ����   --  7
                ,work_time_s    AS �Ұ�ϫƯ����     --  8
                ,uid            AS �Ұ��ֹ�         --  9
                ,ki             AS ��               -- 10
                FROM paid_holiday_master
                WHERE ki={$ki};
                ";
$rows=getResult2($query, $yukyu);
for ($r=0; $r<$rows; $r++) {
    // AS400ͭ��ǡ�������
    $first_yukyu = $yukyu[$r][0] + $yukyu[$r][1];
    if ($yukyu[$r][8] == 9) {       // 9�Ϸ���Ұ��ΰ١������
            // $work_time          = 6;
            // $first_time_holiday = 30;
            // AS�Υץ���ཤ���夳�ä� 2018/09/25
            // 2018/10/25 ����Ұ��ζ�̳���֤�30ñ�����ˤʤä������б�
            // AS��PGM��3���7����30ʬ��730�����Ϥ�Ԥ�
            // ʬ��ü���Ȼ��ֵ٤θ��ٷ׻����ڤ�夲�ˤ��Ƥ��뤬���ºݤ˵������ݤˤ��ǧ
            $temp_time          = floor($yukyu[$r][7] / 100);
            $temp_min           = $yukyu[$r][7] - ($temp_time * 100);
            $temp_min_nue       = ceil(($temp_min / 60) * 1000) / 1000;
            $work_time          = $temp_time + $temp_min_nue;
            $first_time_holiday = ceil($work_time * 5);
            // $work_time          = $yukyu[$r][7] / 100 * 1;
            // $first_time_holiday = $yukyu[$r][7] / 100 * 5;
    } else {
        if ($yukyu[$r][7] != 0) {          // �ѡ��Ȥξ���ϫƯ���֡�5
            $work_time          = $yukyu[$r][7] * 1;
            $first_time_holiday = $yukyu[$r][7] * 5;
        } else {                            // �ѡ���ϫƯ���֤���Ͽ���ʤ����ϰ��̼Ұ�
            $work_time          = 8;
            $first_time_holiday = 40;
            
            // �ʲ��ϻ����ǡ��ºݤ�ϫƯ���֤����äƤ���
            /* ifʸ�ι������ѹ� �ʲ����ѹ�����ifʸ
            if ($yukyu[$r][8] == 9) {       // 9�Ϸ���Ұ��ΰ١������
                $work_time          = 6;
                $first_time_holiday = 30;
            } else {
                $work_time          = 8;
                $first_time_holiday = 40;
            }
            */
        }
    }
    $total_holiday     = $yukyu[$r][5] * 1;
    $day_holiday       = $yukyu[$r][2] - ($yukyu[$r][3] * 0.5);
    $half_holiday      = $yukyu[$r][3] * 0.5;
    $time_holiday      = $total_holiday - $day_holiday - $half_holiday;
    $yukyu_zan         = $first_yukyu - $yukyu[$r][5];
    $half_holiday_num  = $yukyu[$r][3] * 1;
    $time_holiday_hour = $yukyu[$r][4] / 60;
    // ����λ���ͭ��׻����ˤ϶��ս�λǯ������0�ˤʤ�١�����Ū�������������դ��ѹ�
    $kishu_temp        = $yukyu[$r][6] * 1;
    if ($kishu_temp == 0 ) {
        $yukyu[$r][6] = date('Y');
        $yukyu[$r][6] = $yukyu[$r][6] * 1;
        $yukyu[$r][6] = $yukyu[$r][6] * 10000 + 331;
    }
    // timeproͭ��ǡ�������
    $timepro_end_ym    = date('Ymd');
    $query = "SELECT count(*) AS ͭ��������
                     FROM timepro_daily_data
                     WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                     AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                     AND substr(timepro, 173, 2)=11;
             ";
    getResult2($query, $timepro_yukyu);
    $yukyu_zan = $yukyu_zan - $timepro_yukyu[0][0];
    $query = "SELECT count(*) AS Ⱦ�ټ������
                     FROM timepro_daily_data
                     WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                     AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                     AND (substr(timepro, 37, 2)=41 OR substr(timepro, 45, 2)=42);
             ";
    getResult2($query, $timepro_yukyu);
    $half_holiday_num = $half_holiday_num + $timepro_yukyu[0][0];
    $yukyu_zan = $yukyu_zan - $timepro_yukyu[0][0] * 0.5;
    $timepro_time = 0;
    $query = "SELECT count(*)    AS �����ֵټ������
                    ,count(*)*1  AS �����ֵټ�������
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=62 or substr(timepro, 37, 2)=62 or substr(timepro, 45, 2)=62);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time = $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS �����ֵټ������
                    ,count(*)*2  AS �����ֵټ�������
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=65 or substr(timepro, 37, 2)=65 or substr(timepro, 45, 2)=65);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS �����ֵټ������
                    ,count(*)*3  AS �����ֵټ�������
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=66 or substr(timepro, 37, 2)=66 or substr(timepro, 45, 2)=66);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS �����ֵټ������
                    ,count(*)*4  AS �����ֵټ�������
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=67 or substr(timepro, 37, 2)=67 or substr(timepro, 45, 2)=67);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS �����ֵټ������
                    ,count(*)*5  AS �����ֵټ�������
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=68 or substr(timepro, 37, 2)=68 or substr(timepro, 45, 2)=68);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS �����ֵټ������
                    ,count(*)*6  AS �����ֵټ�������
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=69 or substr(timepro, 37, 2)=69 or substr(timepro, 45, 2)=69);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS �����ֵټ������
                    ,count(*)*7  AS �����ֵټ�������
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=70 or substr(timepro, 37, 2)=70 or substr(timepro, 45, 2)=70);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $time_holiday_hour = $time_holiday_hour + $timepro_time;
    // ���ֵ٤�������֤��Ѵ� AS��Ʊ�ͤ˷׻�
    if ($timepro_time != 0) {
        $l1hkyu        = floor($timepro_time * 60);             // ���ֵټ������֤�ʬ���Ѵ�
        
        $jikan         = floor($work_time * 60);                // ��̳���֤�ʬ���Ѵ�
        
        //$wkake         = floor((60 / $jikan) * 1000) / 1000;    // �����ּ��������ݤ�������׻�
        // ü���η׻����ھ�ߤ��� 2018/09/25
        $wkake         = ceil((60 / $jikan) * 1000) / 1000;     // �����ּ��������ݤ�������׻�
        // �����������ֵ٤�����ñ�̤˷���夬��������׻�
        $wnisu         = floor($l1hkyu / $jikan);
        
        $yukyu_zan     = $yukyu_zan - $wnisu;                   // ����ñ�̤˷���夬�ä�������ͭ��Ĥ���ޥ��ʥ�
        
        $whiku         = floor($wnisu * $jikan);                // ����ñ�̤˷���夬�ä�ʬ����׻�
        $wnoko         = $l1hkyu - $whiku;                      // �����������֤��飱��ñ�̤˷���夬�ä�ʬ��ޥ��ʥ�
        $whour         = round($wnoko / 60, 3);                 // �Ĥä�ʬ������֤˴���
        $work53        = round($whour * $wkake, 3);             // �Ĥä�ʬ���������˴���
        
        $yukyu_zan     = $yukyu_zan - $work53;
    }
    //$yukyu_zan     = $yukyu_zan - $timepro_time / $work_time;
    $total_holiday   = $first_yukyu - $yukyu_zan;
    // ��Ͽ���å�
    $query = sprintf("SELECT uid FROM holiday_rest_master WHERE uid='%s' and ki=%d",
                        $yukyu[$r][9], $ki);
    $res_chk = array();
    if ( getResult2($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE �ѹ�
        $query = sprintf("UPDATE holiday_rest_master SET current_day=%f,
                            total_holiday=%f, holiday_rest=%f, half_holiday=%d, time_holiday=%d, time_limit=%d, update_ymd=%d,
                            web_ymd=%d, work_time=%f, last_date=CURRENT_TIMESTAMP",
                          $first_yukyu, $total_holiday, $yukyu_zan, $half_holiday_num, $time_holiday_hour, $first_time_holiday,
                          $yukyu[$r][6], $today_ym, $work_time);
        $query .= sprintf(" WHERE uid='%s' and ki=%d",
                        $yukyu[$r][9], $ki);
        if (query_affected($query) <= 0) {
            echo "{$yukyu[$r][9]},{$ki},{$first_yukyu},{$total_holiday},{$yukyu_zan},{$half_holiday_num}, {$time_holiday_hour}, {$first_time_holiday},{$yukyu[$r][6]}, {$today_ym}, {$work_time}<BR>\n";
        } else {
            echo "{$yukyu[$r][9]},{$ki},{$first_yukyu},{$total_holiday},{$yukyu_zan},{$half_holiday_num}, {$time_holiday_hour}, {$first_time_holiday},{$yukyu[$r][6]}, {$today_ym}, {$work_time}<BR>\n";
        }
    } else {                                    //////// ��Ͽ�ʤ� INSERT ����
        $query = sprintf("INSERT INTO holiday_rest_master (uid, ki, current_day, total_holiday, holiday_rest,
                        half_holiday, time_holiday, time_limit, update_ymd, web_ymd, work_time, last_date)
                      VALUES ('%s', %d, %f, %f, %f, %d, %d, %d, %d, %d, %f, CURRENT_TIMESTAMP)",
                        $yukyu[$r][9], $ki, $first_yukyu, $total_holiday, $yukyu_zan, $half_holiday_num, $time_holiday_hour, $first_time_holiday,
                        $yukyu[$r][6], $today_ym, $work_time);
        if (query_affected($query) <= 0) {
            
        } else {
            
        }
    }
}
?>
