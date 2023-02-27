#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// ��������(�ܺ�)����(daily)����   AS/400 UKWLIB/W#MIADIMDE                 //
//   AS/400 ----> Web Server (PHP) PCIX��FTPž���Ѥ�ʪ�򹹿�����            //
// Copyright(C) 2016-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// \FTPTNK USER(AS400) ASFILE(W#MIADIMDE) LIB(UKWLIB)                       //
//         PCFILE(W#MIADIMDE.TXT) MODE(TXT)                                 //
// Changed history                                                          //
// 2016/03/18 �������� daily_aden_details_cli.php aden_daily_cli.php���¤  //
// 2017/09/21 A����������NK����̳���������ä���Τ�TNK��NK���������ѹ�      //
//            ���ǡ����˴ؤ��Ƥ�NK����̳���������֤�������                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "��������(�ܺ�)�ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_aden_details_cli.php\n");

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date �����ι���(�ܺ�) db_connect() error \n");
    fwrite($fpb, "$log_date �����ι���(�ܺ�) db_connect() error \n");
    echo "$log_date �����ι���(�ܺ�) db_connect() error \n\n";
    exit();
}
///////// ��������ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#MIADIMDE.TXT';
$file_backup = '/home/guest/daily/backup/W#MIADIMDE-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-MIADIMDE.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 24) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 24 record=$rec \n");
            fwrite($fpb, "$log_date field not 24 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ� auto��NG(��ư�Ǥϥ��󥳡��ǥ��󥰤�ǧ���Ǥ��ʤ�)
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            /////// EUC-JP �إ��󥳡��ǥ��󥰤����Ⱦ�ѥ��ʤ� ���饤����Ȥ�Windows��ʤ�����ʤ��Ȥ���
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            // }
            // TNK����������
            // 20=spare2 �� 11=answer_day ����� 11������0�ξ��� 20���ǧ
            // 20������Ǥʤ����11���ִ���
            if ($data[11] == 0) {
                if ($data[20] == 0) {
                    // ξ���ǡ������ʤ���Ф��Τޤ�
                } else {
                    if ($data[20] < 20170922 ) {    // NK��������20170921�����ξ����ִ�����NK������ؤ��б���
                        $data[11] = $data[20];      // ��������NK���ɲ��������ִ����ʶ�̳�ؤβ����� ���ǡ����б���
                    }
                }
            }
        }
        
        $query_chk = sprintf("SELECT aden_no FROM aden_details_master WHERE aden_no='%s' and eda_no=%d", $data[0], $data[18]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO aden_details_master (aden_no, publish_day, receive_day, parts_no, sale_name, order_q,
                      espoir_deli, delivery, order_price, kouji_no, plan_no, answer_day, user_code, user_name, estimate_no,
                      ropes_no, div, divide_deli, eda_no, spare1, spare2, spare3, spare4, spare5, deli_com)
                      VALUES(
                      '{$data[0]}',
                       {$data[1]} ,
                       {$data[2]} ,
                      '{$data[3]}',
                      '{$data[4]}',
                      '{$data[5]}',
                       {$data[6]} ,
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                      '{$data[12]}',
                      '{$data[13]}',
                      '{$data[14]}',
                      '{$data[15]}',
                      '{$data[16]}',
                      '{$data[17]}',
                       {$data[18]} ,
                      '{$data[19]}',
                      '{$data[20]}',
                      '{$data[21]}',
                      '{$data[22]}',
                      '{$data[23]}',
                       0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�(�ܺ�):{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����ֹ�(�ܺ�):{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
        } else {
            ///// ��Ͽ���� update ����
            $query = "UPDATE aden_details_master SET
                            aden_no     ='{$data[0]}',
                            publish_day = {$data[1]} ,
                            receive_day = {$data[2]} ,
                            parts_no    ='{$data[3]}',
                            sale_name   ='{$data[4]}',
                            order_q     ='{$data[5]}',
                            espoir_deli = {$data[6]} ,
                            delivery    = {$data[7]} ,
                            order_price ='{$data[8]}',
                            kouji_no    ='{$data[9]}',
                            plan_no     ='{$data[10]}',
                            answer_day  = {$data[11]} ,
                            user_code   ='{$data[12]}',
                            user_name   ='{$data[13]}',
                            estimate_no ='{$data[14]}',
                            ropes_no    ='{$data[15]}',
                            div         ='{$data[16]}',
                            divide_deli ='{$data[17]}',
                            eda_no      = {$data[18]} ,
                            spare1      ='{$data[19]}',
                            spare2      ='{$data[20]}',
                            spare3      ='{$data[21]}',
                            spare4      ='{$data[22]}',
                            spare5      ='{$data[23]}'
                      where aden_no='{$data[0]}' and eda_no={$data[18]}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����ֹ�(�ܺ�):{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����ֹ�(�ܺ�):{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $upd_ok++;
            }
        }
    }
    // ��˾L/T�η׻� ���������Τ�
    $query_chk = sprintf("SELECT receive_day, espoir_deli, aden_no, eda_no FROM aden_details_master WHERE to_char(last_date,'yyyy-mm-dd')=current_date and espoir_deli<>0");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// ��˾Ǽ����̵���Τǽ������ʤ� 
    } else {
        ///// ��Ͽ���� update ����
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r][0] <= $res[$r][1]) {   // �ޥ��ʥ��׻��б�
                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][0], $res[$r][1]);
            } else {
                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][1], $res[$r][0]);
            }
            if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // �ǡ����ʤ��� �������ȴ�˾Ǽ����Ʊ�� �� 0
                $espoir_lt = 0;
            } else {
                $espoir_lt = $res_e[0][0];
            }
            $query = "UPDATE aden_details_master SET
                        espoir_lt = {$espoir_lt}
                        where aden_no='{$res[$r][2]}' and eda_no={$res[$r][3]}";
            query_affected_trans($con, $query);
        }
    }
    // ����L/T�η׻� ���������Τ�
    $query_chk = sprintf("SELECT receive_day, delivery, aden_no, eda_no FROM aden_details_master WHERE to_char(last_date,'yyyy-mm-dd')=current_date and delivery<>0");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// ����Ǽ����̵���Τǽ������ʤ� 
    } else {
        ///// ��Ͽ���� update ����
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r][0] <= $res[$r][1]) {   // �ޥ��ʥ��׻��б�
                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][0], $res[$r][1]);
            } else {
                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][1], $res[$r][0]);
            }
            if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // �ǡ����ʤ��� �������Ȳ���Ǽ����Ʊ�� �� 0
                $ans_lt = 0;
            } else {
                $ans_lt = $res_e[0][0];
            }
            $query = "UPDATE aden_details_master SET
                        ans_lt = {$ans_lt}
                        where aden_no='{$res[$r][2]}' and eda_no={$res[$r][3]}";
            query_affected_trans($con, $query);
        }
    }
    // L/T���η׻� ���������Τ�
    $query_chk = sprintf("SELECT espoir_lt, ans_lt, aden_no, eda_no FROM aden_details_master WHERE to_char(last_date,'yyyy-mm-dd')=current_date and espoir_lt<>0 and ans_lt<>0");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// ��˾������L/T��̵���Τǽ������ʤ� 
    } else {
        ///// ��Ͽ���� update ����
        for ($r=0; $r<$rows; $r++) {
            $lt_diff = $res[$r][1] - $res[$r][0];
            $query = "UPDATE aden_details_master SET
                        lt_diff = {$lt_diff}
                        where aden_no='{$res[$r][2]}' and eda_no={$res[$r][3]}";
            query_affected_trans($con, $query);
        }
    }
    // �´����� �����٤�ι��� ���������Τ�
    $query_chk = sprintf("SELECT a.aden_no, a.eda_no, a.delivery, a.plan_no, s.plan, s.cut_plan, s.kansei, a.espoir_deli FROM aden_details_master AS a left outer join assembly_schedule as s on ( a.plan_no=s.plan_no) WHERE to_char(a.last_date,'yyyy-mm-dd')=current_date and (s.cut_plan<>0 OR s.kansei<>0) and a.plan_no<>''");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// ����Ǽ����̵���Τǽ������ʤ� 
    } else {
        ///// ��Ͽ���� update ����
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r][4] == $res[$r][5]) {     // ���٤����ڤ�
                $query_e = "UPDATE aden_details_master SET
                            spare1 = 'U'
                            where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                query_affected_trans($con, $query_e);
            } elseif (($res[$r][4] - $res[$r][5] - $res[$r][6]) > 0) {   // Ǽ��ͭ��̤��λ
                if ($res[$r][6] > 0) {  // ������0�ʾ�Τ�Ρʰ����Ǥ��ڤä������Τ�Τ������
                    // �´������μ���
                    $query_e    = sprintf("SELECT comp_date FROM assembly_completion_history WHERE plan_no='%s' ORDER BY comp_date DESC limit 1", $res[$r][3]);
                    $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                    $finish_day = $res_e[0][0];
                    if ( $finish_day <> '') {
                        // �����٤�η׻�
                        if ($res[$r][2] == 0) {   // ����Ǽ�������Ϥ�̵���ä���硢��˾Ǽ������Ӥ���
                            if ($res[$r][7] <= $finish_day) {   // �ޥ��ʥ��׻��б�
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][7], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][7]);
                            }
                        } else {
                            if ($res[$r][2] <= $finish_day) {   // �ޥ��ʥ��׻��б�
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][2], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][2]);
                            }
                        }
                        if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // �ǡ����ʤ��� �´������Ȳ���Ǽ����Ʊ�� �� 0
                            $finish_del = 0;
                        } else {
                            $finish_del = $res_e[0][0];
                        }
                        $query_e = "UPDATE aden_details_master SET
                                    spare1 = 'B', finish_day = {$finish_day}, finish_del = {$finish_del}
                                    where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                        query_affected_trans($con, $query_e);
                    }
                }
            } elseif ($res[$r][4] == $res[$r][6]) {   // ʬǼ-��Ǽ�ȣ�ȯ��Ǽ��ȴ���Ф�
                $query_e    = sprintf("SELECT count(plan_no) FROM assembly_completion_history WHERE plan_no='%s'", $res[$r][3]);
                $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                if ($res_e[0][0] < 2 ) { // ��������1��ʲ��Τ��ᣱȯ��Ǽ
                    // �´������μ���
                    $query_e    = sprintf("SELECT comp_date FROM assembly_completion_history WHERE plan_no='%s' ORDER BY comp_date DESC limit 1", $res[$r][3]);
                    $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                    if ($rows_e == 0) {
                        $finish_day = 0;
                    } else {
                        $finish_day = $res_e[0][0];
                    }
                    if ( $finish_day <> '') {
                        // �����٤�η׻�
                        if ($res[$r][2] == 0) {   // ����Ǽ�������Ϥ�̵���ä���硢��˾Ǽ������Ӥ���
                            if ($res[$r][7] <= $finish_day) {   // �ޥ��ʥ��׻��б�
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][7], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][7]);
                            }
                        } else {
                            if ($res[$r][2] <= $finish_day) {   // �ޥ��ʥ��׻��б�
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][2], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][2]);
                            }
                        }
                        if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // �ǡ����ʤ��� �´������Ȳ���Ǽ����Ʊ�� �� 0
                            $finish_del = 0;
                        } else {
                            $finish_del = $res_e[0][0];
                        }
                        $query_e = "UPDATE aden_details_master SET
                                    spare1 = 'K', finish_day = {$finish_day}, finish_del = {$finish_del}
                                    where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                        query_affected_trans($con, $query_e);
                    }
                } else {    // ��������2��ʾ�Τ���ʬǼ-��Ǽ
                    // �´������μ���
                    $query_e    = sprintf("SELECT comp_date FROM assembly_completion_history WHERE plan_no='%s' ORDER BY comp_date DESC limit 1", $res[$r][3]);
                    $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                    $finish_day = $res_e[0][0];
                    if ( $finish_day <> '') {
                        // �����٤�η׻�
                        if ($res[$r][2] == 0) {   // ����Ǽ�������Ϥ�̵���ä���硢��˾Ǽ������Ӥ���
                            if ($res[$r][7] <= $finish_day) {   // �ޥ��ʥ��׻��б�
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][7], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][7]);
                            }
                        } else {
                            if ($res[$r][2] <= $finish_day) {   // �ޥ��ʥ��׻��б�
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][2], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][2]);
                            }
                        }
                        if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // �ǡ����ʤ��� �´������Ȳ���Ǽ����Ʊ�� �� 0
                            $finish_del = 0;
                        } else {
                            $finish_del = $res_e[0][0];
                        }
                        $query_e = "UPDATE aden_details_master SET
                                    spare1 = 'BK', finish_day = {$finish_day}, finish_del = {$finish_del}
                                    where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                        query_affected_trans($con, $query_e);
                    }
                }
            } elseif (($res[$r][4] - $res[$r][5] - $res[$r][6]) == 0) {   // ����-��Ǽ��ʬǼ-����-��Ǽ��ȴ���Ф�
                $query_e    = sprintf("SELECT count(plan_no) FROM assembly_completion_history WHERE plan_no='%s'", $res[$r][3]);
                $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                if ($res_e[0][0] < 2 ) { // ��������1��ʲ��Τ�������-��Ǽ
                    // �´������μ���
                    $query_e    = sprintf("SELECT comp_date FROM assembly_completion_history WHERE plan_no='%s' ORDER BY comp_date DESC limit 1", $res[$r][3]);
                    $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                    $finish_day = $res_e[0][0];
                    if ( $finish_day <> '') {
                        // �����٤�η׻�
                        if ($res[$r][2] == 0) {   // ����Ǽ�������Ϥ�̵���ä���硢��˾Ǽ������Ӥ���
                            if ($res[$r][7] <= $finish_day) {   // �ޥ��ʥ��׻��б�
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][7], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][7]);
                            }
                        } else {
                            if ($res[$r][2] <= $finish_day) {   // �ޥ��ʥ��׻��б�
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][2], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][2]);
                            }
                        }
                        if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // �ǡ����ʤ��� �´������Ȳ���Ǽ����Ʊ�� �� 0
                            $finish_del = 0;
                        } else {
                            $finish_del = $res_e[0][0];
                        }
                        $query_e = "UPDATE aden_details_master SET
                                    spare1 = 'UK', finish_day = {$finish_day}, finish_del = {$finish_del}
                                    where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                        query_affected_trans($con, $query_e);
                    }
                } else {    // ��������2��ʾ�Τ���ʬǼ-����-��Ǽ
                    // �´������μ���
                    $query_e    = sprintf("SELECT comp_date FROM assembly_completion_history WHERE plan_no='%s' ORDER BY comp_date DESC limit 1", $res[$r][3]);
                    $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                    $finish_day = $res_e[0][0];
                    if ( $finish_day <> '') {
                        // �����٤�η׻�
                        if ($res[$r][2] == 0) {   // ����Ǽ�������Ϥ�̵���ä���硢��˾Ǽ������Ӥ���
                            if ($res[$r][7] <= $finish_day) {   // �ޥ��ʥ��׻��б�
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][7], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][7]);
                            }
                        } else {
                            if ($res[$r][2] <= $finish_day) {   // �ޥ��ʥ��׻��б�
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][2], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][2]);
                            }
                        }
                        if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // �ǡ����ʤ��� �´������Ȳ���Ǽ����Ʊ�� �� 0
                            $finish_del = 0;
                        } else {
                            $finish_del = $res_e[0][0];
                        }
                        $query_e = "UPDATE aden_details_master SET
                                    spare1 = 'BUK', finish_day = {$finish_day}, finish_del = {$finish_del}
                                    where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                        query_affected_trans($con, $query_e);
                    }
                }
            }
        }
    }
    // A������L/T�η׻� ���������Τ�
    $query_chk = sprintf("SELECT receive_day, answer_day, aden_no, eda_no FROM aden_details_master WHERE to_char(last_date,'yyyy-mm-dd')=current_date and answer_day<>0");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// A����������̵���Τǽ������ʤ� 
    } else {
        ///// ��Ͽ���� update ����
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r][0] <= $res[$r][1]) {   // �ޥ��ʥ��׻��б�
                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][0], $res[$r][1]);
            } else {
                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][1], $res[$r][0]);
            }
            if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // �ǡ����ʤ��� �������ȴ�˾Ǽ����Ʊ�� �� 0
                $ans_day_lt = 0;
            } else {
                $ans_day_lt = $res_e[0][0];
            }
            $query = "UPDATE aden_details_master SET
                        ans_day_lt = {$ans_day_lt}
                        where aden_no='{$res[$r][2]}' and eda_no={$res[$r][3]}";
            query_affected_trans($con, $query);
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date �����ι���(�ܺ�) : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �����ι���(�ܺ�) : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �����ι���(�ܺ�) : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �����ι���(�ܺ�) : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �����ι���(�ܺ�) : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �����ι���(�ܺ�) : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �����ι���(�ܺ�) : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �����ι���(�ܺ�) : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �����ι���(�ܺ�) : {$upd_ok}/{$rec} �� �ѹ� \n";
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : ��������(�ܺ�)�ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : ��������(�ܺ�)�ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : ��������(�ܺ�)�ι����ե����� {$file_orign} ������ޤ���\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
