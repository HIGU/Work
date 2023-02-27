#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// ͭ��5�������(daily)����                                                 //
// Copyright(C) 2019-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2019/05/273 �������� daily_yukyu_cli.php���¤                           //
//                      daily_yukyu_reference_cli.php                       //
// 2019/07/25 ��ཪλ����ɬ���������ɲ�                                    //
// 2019/10/02 20�����������ҼԤϲ�̣���ʤ��褦���ѹ�                        //
// 2020/07/03 ���ӼҰ���������׻��Ǥ��ʤ������ʤ��ΤǼ�ư����              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "ͭ��5��������ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_yukyu_reference_cli.php\n");

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date ͭ��ι��� db_connect() error \n");
    fwrite($fpb, "$log_date ͭ��ι��� db_connect() error \n");
    echo "$log_date ͭ��ι��� db_connect() error \n\n";
    exit();
}

$ym    =  date("Ym");
$tmp   = $ym - 200003;
$tmp   = $tmp / 100;
$ki    = ceil($tmp);
$nk_ki = $ki + 44;
$yyyy = substr($ym, 0,4);
$mm   = substr($ym, 4,2);

// ǯ�ٷ׻�
if ($mm < 4) {              // 1��3��ξ��
    $business_year = $yyyy - 1;
} else {
    $business_year = $yyyy;
}
$ki_first_ym     = $business_year . '04';    // ��������ǯ��
$ki_first_ymd    = $business_year . '0401';  // ��������ǯ����
$b_ki_first_ymd  = $ki_first_ymd - 10000;    // ������ǯ����
$bb_ki_first_ymd = $b_ki_first_ymd - 10000;  // ��������ǯ����
$ki_end_yy       = $business_year + 1;
$ki_end_ym       = $ki_end_yy . '03';    // ��������ǯ��
$ki_end_ymd      = $ki_end_yy . '0331';  // ��������ǯ����
// �ѡ���ͭ��ȯ��������� �������Ҥ�10��ʹߤǤ����������ͭ���ȯ�����ʤ���
if ($mm < 4) {              // 1��3��ξ��
    $s_yy = $yyyy - 1;
} else {
    $s_yy = $yyyy;
}
$six_ym  = $s_yy . '10';    // �ѡ���ͭ��ȯ������
$six_ymd = $s_yy . '1001';  // �ѡ���ͭ��ȯ�������

$query_u = "SELECT p.uid, d.name, p.ki, p.current_day, d.sid, to_char(d.enterdate, 'YYYYMMDD'), d.retire_date, p.work_time_p, p.work_time_s
          FROM paid_holiday_master as p left outer join user_detailes as d on p.uid=d.uid 
          WHERE ki={$nk_ki};
        ";
$res = array();
$rows = getResult2($query_u, $res);
// �嵭�ξ��ǡ������ޤǤ��࿦�Ԥ����칩��μҰ��Ͻ��������
//$fp = fopen($file_orign, 'r');
//$fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
$rec = 0;       // �쥳���ɭ�
$rec_ok = 0;    // ����������쥳���ɿ�
$rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
$ins_ok = 0;    // INSERT�ѥ����󥿡�
for ($r=0; $r<$rows; $r++) {
    $query_chk = sprintf("SELECT uid FROM five_yukyu_master WHERE uid='%s' and business_year=%d", $res[$r][0], $business_year);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼ 
    // ��Ͽ�ʤ��ξ��ϰʲ��Υ����å�����Ͽ��Ԥ���
       if ($res[$r][4] <> '80') {              // ��������
            if ($res[$r][3] == 0) {             // ��������ͭ�뤬0�ξ��
                if ($res[$r][6] == '') {        // �࿦����¸�ߤ��ʤ� 
                    // ����(6�������)�⤷�����������ҤΥѡ���
                    if ($res[$r][5] >= $ki_first_ymd) { // ��������
                        if ($res[$r][5] <= $six_ymd) {  // ��������10������(10��ʹߤǤ����������ͭ���ȯ�����ʤ�)
                            $r_yy = substr($res[$r][5], 0,4);
                            $r_mm = substr($res[$r][5], 4,2);
                            $r_dd = substr($res[$r][5], 6,2);
                            // ��̩��ͭ�����������󥷥��ƥ����˳�ǧ �� AS��ư��Ϳ�ǤϤʤ�
                            // �������Ҥ�10�������ξ�硢ͽ��������׻����Ƥ���
                            if ($r_mm < 7) {
                                $reference_ym = $res[$r][5] + 600;
                            } else {
                                $reference_ym = $res[$r][5] - 600 + 10000;
                            }
                            $c_yy = substr($reference_ym, 0,4);
                            $c_mm = substr($reference_ym, 4,2);
                            $c_dd = substr($reference_ym, 6,2);
                            if(checkdate($c_mm, $c_dd, $c_yy)) {
                                // ¸������OK ���⤷�ʤ�
                            } else {
                                // ���դ�¸�ߤ��ʤ��Τ��̤롣���������б���
                                // 1/31 �� 2/31 �� 2/30 �� 2/29 �� 2/28 -3�ޤǤ�OK
                                $t_ymd = $reference_ym - 1;     // 2/30
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // ¸������OK ������ִ��� 31��30���ξ��Ϥ�����OK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // ���դ�¸�ߤ��ʤ��Τ��̤�
                                    $t_ymd = $reference_ym - 1;     // 2/29
                                    $t_yy = substr($t_ymd, 0,4);
                                    $t_mm = substr($t_ymd, 4,2);
                                    $t_dd = substr($t_ymd, 6,2);
                                    if(checkdate($t_mm, $t_dd, $t_yy)) {
                                        // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                        $reference_ym = $t_ymd;
                                    } else {
                                        // ���դ�¸�ߤ��ʤ��Τ��̤�
                                        $t_ymd = $reference_ym - 1;     // 2/28
                                        $t_yy = substr($t_ymd, 0,4);
                                        $t_mm = substr($t_ymd, 4,2);
                                        $t_dd = substr($t_ymd, 6,2);
                                        if(checkdate($t_mm, $t_dd, $t_yy)) {
                                            // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                            $reference_ym = $t_ymd;
                                        } else {
                                            // ���ä��ˤ��뤳�Ȥ�̵����ǰ�Τ��������ִ���
                                            $reference_ym = $t_ymd;
                                        }
                                    }
                                }
                            }
                            $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                                VALUES(
                                '{$res[$r][0]}',
                                {$business_year} ,
                                {$reference_ym})";
                            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                                fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                                fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                                // query_affected_trans($con, "rollback");     // transaction rollback
                                $rec_ng++;
                                ////////////////////////////////////////// Debug start
                                for ($f=0; $f<$rows; $f++) {
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
                        }
                        // ����10��ʹ����Ҥ�������ͭ���ȯ�����ʤ��Τ���Ͽ̵��
                    } elseif ($res[$r][5] >= $b_ki_first_ymd) { // ��������
                        // �������ҤʤΤǡ��������դ��麣ǯ�٤δ�����򤳤��Ƿ׻�
                        // �������ҤǤ�10��ʹߤξ��ϡ�����ͭ�뤬ȯ������
                        // 9������ξ��ϡ�������������Ȥʤ뤬��λ���������ȹ绻�ˤʤ�١�����������롣
                        // â������������2018/09�����ξ���ǯ����Ϳ��2019/03�ޤǤ�
                        // �ʤ�١�ˡŪ�˺����ȹ绻����ɬ�פϤʤ���(4/1ȯ���Τ�Τ����Ǥ���)
                        $r_yy = substr($res[$r][5], 0,4);
                        $r_mm = substr($res[$r][5], 4,2);
                        $r_dd = substr($res[$r][5], 6,2);
                        $r_ym = substr($res[$r][5], 0,6);
                        $r_md = substr($res[$r][5], 4,4);
                        if ($r_mm > 6) {
                            $reference_ym = $res[$r][5] - 600 + 10000;
                        } else {
                            $reference_ym = $res[$r][5] + 600;
                        }
                        $c_yy = substr($reference_ym, 0,4);
                        $c_mm = substr($reference_ym, 4,2);
                        $c_dd = substr($reference_ym, 6,2);
                        if(checkdate($c_mm, $c_dd, $c_yy)) {
                            // ¸������OK ���⤷�ʤ�
                        } else {
                            // ���դ�¸�ߤ��ʤ��Τ��̤롣���������б���
                            // 1/31 �� 2/31 �� 2/30 �� 2/29 �� 2/28 -3�ޤǤ�OK
                            $t_ymd = $reference_ym - 1;     // 2/30
                            $t_yy = substr($t_ymd, 0,4);
                            $t_mm = substr($t_ymd, 4,2);
                            $t_dd = substr($t_ymd, 6,2);
                            if(checkdate($t_mm, $t_dd, $t_yy)) {
                                // ¸������OK ������ִ��� 31��30���ξ��Ϥ�����OK
                                $reference_ym = $t_ymd;
                            } else {
                                // ���դ�¸�ߤ��ʤ��Τ��̤�
                                $t_ymd = $reference_ym - 1;     // 2/29
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // ���դ�¸�ߤ��ʤ��Τ��̤�
                                    $t_ymd = $reference_ym - 1;     // 2/28
                                    $t_yy = substr($t_ymd, 0,4);
                                    $t_mm = substr($t_ymd, 4,2);
                                    $t_dd = substr($t_ymd, 6,2);
                                    if(checkdate($t_mm, $t_dd, $t_yy)) {
                                        // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                        $reference_ym = $t_ymd;
                                    } else {
                                        // ���ä��ˤ��뤳�Ȥ�̵����ǰ�Τ��������ִ���
                                        $reference_ym = $t_ymd;
                                    }
                                }
                            }
                        }
                        $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                            VALUES(
                              '{$res[$r][0]}',
                               {$business_year} ,
                               {$reference_ym})";
                        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                            fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                            fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                            // query_affected_trans($con, "rollback");     // transaction rollback
                            $rec_ng++;
                            ////////////////////////////////////////// Debug start
                            for ($f=0; $f<$rows; $f++) {
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
                    } else {                        // ����������
                        // ����������������10/02�ʹߤ��оݤȤʤ�
                        // 10/02�ʹ����04/02��04/01�ޤǤȤʤ��
                        $r_yy = substr($res[$r][5], 0,4);
                        $r_mm = substr($res[$r][5], 4,2);
                        $r_dd = substr($res[$r][5], 6,2);
                        $r_ym = substr($res[$r][5], 0,6);
                        $r_md = substr($res[$r][5], 4,4);
                        if ($r_md >= 1002) {
                            $reference_ym = $res[$r][5] - 600 + 10000;
                        }
                        $c_yy = substr($reference_ym, 0,4);
                        $c_mm = substr($reference_ym, 4,2);
                        $c_dd = substr($reference_ym, 6,2);
                        if(checkdate($c_mm, $c_dd, $c_yy)) {
                            // ¸������OK ���⤷�ʤ�
                        } else {
                            // ���դ�¸�ߤ��ʤ��Τ��̤롣���������б���
                            // 1/31 �� 2/31 �� 2/30 �� 2/29 �� 2/28 -3�ޤǤ�OK
                            $t_ymd = $reference_ym - 1;     // 2/30
                            $t_yy = substr($t_ymd, 0,4);
                            $t_mm = substr($t_ymd, 4,2);
                            $t_dd = substr($t_ymd, 6,2);
                            if(checkdate($t_mm, $t_dd, $t_yy)) {
                                // ¸������OK ������ִ��� 31��30���ξ��Ϥ�����OK
                                $reference_ym = $t_ymd;
                            } else {
                                // ���դ�¸�ߤ��ʤ��Τ��̤�
                                $t_ymd = $reference_ym - 1;     // 2/29
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // ���դ�¸�ߤ��ʤ��Τ��̤�
                                    $t_ymd = $reference_ym - 1;     // 2/28
                                    $t_yy = substr($t_ymd, 0,4);
                                    $t_mm = substr($t_ymd, 4,2);
                                    $t_dd = substr($t_ymd, 6,2);
                                    if(checkdate($t_mm, $t_dd, $t_yy)) {
                                        // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                        $reference_ym = $t_ymd;
                                    } else {
                                        // ���ä��ˤ��뤳�Ȥ�̵����ǰ�Τ��������ִ���
                                        $reference_ym = $t_ymd;
                                    }
                                }
                            }
                        }
                        $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                            VALUES(
                              '{$res[$r][0]}',
                               {$business_year} ,
                               {$reference_ym})";
                        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                            fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                            fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                            // query_affected_trans($con, "rollback");     // transaction rollback
                            $rec_ng++;
                            ////////////////////////////////////////// Debug start
                            for ($f=0; $f<$rows; $f++) {
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
                    }
                }
                //ͭ��ȯ�������࿦�ʤΤ���Ͽ����
            } elseif ($res[$r][3] >= 10) {      // ��������ͭ�뤬10���ʾ�(���ӼҰ������)
                // ͭ�뤬���Ǥ���Ϳ����Ƥ���Τǳμ¤�����6����ʾ��������ҤȤʤ�
                // ����(6�������)�⤷�����������ҤΥѡ���
                if ($res[$r][5] >= $ki_first_ymd) { // ��������
                if ($res[$r][7] != 0 && $res[$r][8] == 0) {
                    if ($res[$r][5] <= $six_ymd) {    // ��������10������(10��ʹߤǤ����������ͭ���ȯ�����ʤ�)
                        $r_yy = substr($res[$r][5], 0,4);
                        $r_mm = substr($res[$r][5], 4,2);
                        $r_dd = substr($res[$r][5], 6,2);
                        // ��̩��ͭ�����������󥷥��ƥ����˳�ǧ �� AS��ư��Ϳ�ǤϤʤ�
                        // �������Ҥ�10�������ξ�硢ͽ��������׻����Ƥ���
                        if ($r_mm < 7) {
                            $reference_ym = $res[$r][5] + 600;
                        } else {
                            $reference_ym = $res[$r][5] - 600 + 10000;
                        }
                        $c_yy = substr($reference_ym, 0,4);
                        $c_mm = substr($reference_ym, 4,2);
                        $c_dd = substr($reference_ym, 6,2);
                        if(checkdate($c_mm, $c_dd, $c_yy)) {
                            // ¸������OK ���⤷�ʤ�
                        } else {
                            // ���դ�¸�ߤ��ʤ��Τ��̤롣���������б���
                            // 1/31 �� 2/31 �� 2/30 �� 2/29 �� 2/28 -3�ޤǤ�OK
                            $t_ymd = $reference_ym - 1;     // 2/30
                            $t_yy = substr($t_ymd, 0,4);
                            $t_mm = substr($t_ymd, 4,2);
                            $t_dd = substr($t_ymd, 6,2);
                            if(checkdate($t_mm, $t_dd, $t_yy)) {
                                // ¸������OK ������ִ��� 31��30���ξ��Ϥ�����OK
                                $reference_ym = $t_ymd;
                            } else {
                                // ���դ�¸�ߤ��ʤ��Τ��̤�
                                $t_ymd = $reference_ym - 1;     // 2/29
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // ���դ�¸�ߤ��ʤ��Τ��̤�
                                    $t_ymd = $reference_ym - 1;     // 2/28
                                    $t_yy = substr($t_ymd, 0,4);
                                    $t_mm = substr($t_ymd, 4,2);
                                    $t_dd = substr($t_ymd, 6,2);
                                    if(checkdate($t_mm, $t_dd, $t_yy)) {
                                        // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                        $reference_ym = $t_ymd;
                                    } else {
                                        // ���ä��ˤ��뤳�Ȥ�̵����ǰ�Τ��������ִ���
                                        $reference_ym = $t_ymd;
                                    }
                                }
                            }
                        }
                        $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                            VALUES(
                            '{$res[$r][0]}',
                            {$business_year} ,
                            {$reference_ym})";
                        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                            fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                            fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                            // query_affected_trans($con, "rollback");     // transaction rollback
                            $rec_ng++;
                            ////////////////////////////////////////// Debug start
                            for ($f=0; $f<$rows; $f++) {
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
                    }
                    // ����10��ʹ����Ҥ�������ͭ���ȯ�����ʤ��Τ���Ͽ̵��
                } else {
                    $reference_ym = $res[$r][5];
                    $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                          VALUES(
                          '{$res[$r][0]}',
                           {$business_year} ,
                           {$reference_ym})";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                        fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                        // query_affected_trans($con, "rollback");     // transaction rollback
                        $rec_ng++;
                        ////////////////////////////////////////// Debug start
                        for ($f=0; $f<$rows; $f++) {
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
                }
                } elseif ($res[$r][5] >= $b_ki_first_ymd && $res[$r][5] >= 20180999) { // ��������
                    // �������ҤʤΤǡ��������դ��麣ǯ�٤δ�����򤳤��Ƿ׻�
                    // �������ҤǤ�10��ʹߤξ��ϡ�����ͭ�뤬ȯ������
                    // 9������ξ��ϡ�������������Ȥʤ뤬��λ���������ȹ绻�ˤʤ�١�����������롣
                    // â������������2018/09�����ξ���ǯ����Ϳ��2019/03�ޤǤ�
                    // �ʤ�١�ˡŪ�˺����ȹ绻����ɬ�פϤʤ���(4/1ȯ���Τ�Τ����Ǥ���)
                if ($res[$r][7] != 0 && $res[$r][8] == 0) {
                    $r_yy = substr($res[$r][5], 0,4);
                    $r_mm = substr($res[$r][5], 4,2);
                    $r_dd = substr($res[$r][5], 6,2);
                    $r_ym = substr($res[$r][5], 0,6);
                    $r_md = substr($res[$r][5], 4,4);
                    if ($r_mm > 6) {
                        $reference_ym = $res[$r][5] - 600 + 10000;
                    } else {
                        $reference_ym = $res[$r][5] + 600;
                    }
                    $c_yy = substr($reference_ym, 0,4);
                    $c_mm = substr($reference_ym, 4,2);
                    $c_dd = substr($reference_ym, 6,2);
                    if(checkdate($c_mm, $c_dd, $c_yy)) {
                        // ¸������OK ���⤷�ʤ�
                    } else {
                        // ���դ�¸�ߤ��ʤ��Τ��̤롣���������б���
                        // 1/31 �� 2/31 �� 2/30 �� 2/29 �� 2/28 -3�ޤǤ�OK
                        $t_ymd = $reference_ym - 1;     // 2/30
                        $t_yy = substr($t_ymd, 0,4);
                        $t_mm = substr($t_ymd, 4,2);
                        $t_dd = substr($t_ymd, 6,2);
                        if(checkdate($t_mm, $t_dd, $t_yy)) {
                            // ¸������OK ������ִ��� 31��30���ξ��Ϥ�����OK
                            $reference_ym = $t_ymd;
                        } else {
                            // ���դ�¸�ߤ��ʤ��Τ��̤�
                            $t_ymd = $reference_ym - 1;     // 2/29
                            $t_yy = substr($t_ymd, 0,4);
                            $t_mm = substr($t_ymd, 4,2);
                            $t_dd = substr($t_ymd, 6,2);
                            if(checkdate($t_mm, $t_dd, $t_yy)) {
                                // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                $reference_ym = $t_ymd;
                            } else {
                                // ���դ�¸�ߤ��ʤ��Τ��̤�
                                $t_ymd = $reference_ym - 1;     // 2/28
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // ���ä��ˤ��뤳�Ȥ�̵����ǰ�Τ��������ִ���
                                    $reference_ym = $t_ymd;
                                }
                            }
                        }
                    }
                    $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                        VALUES(
                          '{$res[$r][0]}',
                           {$business_year} ,
                           {$reference_ym})";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                        fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                        // query_affected_trans($con, "rollback");     // transaction rollback
                        $rec_ng++;
                        ////////////////////////////////////////// Debug start
                        for ($f=0; $f<$rows; $f++) {
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
                } else {        // �Ұ�
                    $reference_ym = $ki_first_ymd;
                    $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                          VALUES(
                          '{$res[$r][0]}',
                           {$business_year} ,
                           {$reference_ym})";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                        fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                        // query_affected_trans($con, "rollback");     // transaction rollback
                        $rec_ng++;
                        ////////////////////////////////////////// Debug start
                        for ($f=0; $f<$rows; $f++) {
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
                }
                } elseif ($res[$r][5] >= $bb_ki_first_ymd) {    // ����������
                    // ����������������10/02�ʹߤ��оݤȤʤ�
                    // 10/02�ʹ����04/02��04/01�ޤǤȤʤ��
                    // ����ʳ��Ϻ���4/1ͭ����Ϳ�Ȥʤ롣
                    $r_yy  = substr($res[$r][5], 0,4);
                    $r_mm  = substr($res[$r][5], 4,2);
                    $r_dd  = substr($res[$r][5], 6,2);
                    $r_ym  = substr($res[$r][5], 0,6);
                    $r_md  = substr($res[$r][5], 4,4);
                    if ($r_md >= 1002) {
                        $reference_ym = $res[$r][5] - 600 + 10000;
                    } else {    // ��������10/1��������
                        $reference_ym = $ki_first_ymd;
                    }
                    $c_yy = substr($reference_ym, 0,4);
                    $c_mm = substr($reference_ym, 4,2);
                    $c_dd = substr($reference_ym, 6,2);
                    if(checkdate($c_mm, $c_dd, $c_yy)) {
                        // ¸������OK ���⤷�ʤ�
                    } else {
                        // ���դ�¸�ߤ��ʤ��Τ��̤롣���������б���
                        // 1/31 �� 2/31 �� 2/30 �� 2/29 �� 2/28 -3�ޤǤ�OK
                        $t_ymd = $reference_ym - 1;     // 2/30
                        $t_yy = substr($t_ymd, 0,4);
                        $t_mm = substr($t_ymd, 4,2);
                        $t_dd = substr($t_ymd, 6,2);
                        if(checkdate($t_mm, $t_dd, $t_yy)) {
                            // ¸������OK ������ִ��� 31��30���ξ��Ϥ�����OK
                            $reference_ym = $t_ymd;
                        } else {
                            // ���դ�¸�ߤ��ʤ��Τ��̤�
                            $t_ymd = $reference_ym - 1;     // 2/29
                            $t_yy = substr($t_ymd, 0,4);
                            $t_mm = substr($t_ymd, 4,2);
                            $t_dd = substr($t_ymd, 6,2);
                            if(checkdate($t_mm, $t_dd, $t_yy)) {
                                // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                $reference_ym = $t_ymd;
                            } else {
                                // ���դ�¸�ߤ��ʤ��Τ��̤�
                                $t_ymd = $reference_ym - 1;     // 2/28
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // ¸������OK ������ִ��� ���뤦ǯ�Ϥ�����OK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // ���ä��ˤ��뤳�Ȥ�̵����ǰ�Τ��������ִ���
                                    $reference_ym = $t_ymd;
                                }
                            }
                        }
                    }
                    $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                        VALUES(
                          '{$res[$r][0]}',
                           {$business_year} ,
                           {$reference_ym})";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                        fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                        // query_affected_trans($con, "rollback");     // transaction rollback
                        $rec_ng++;
                        ////////////////////////////////////////// Debug start
                        for ($f=0; $f<$rows; $f++) {
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
                } else {    // ��������10/01�������� ������4/1�������
                    $reference_ym = $ki_first_ymd;
                    $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                          VALUES(
                          '{$res[$r][0]}',
                           {$business_year} ,
                           {$reference_ym})";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        fwrite($fpa, "$log_date �Ұ��ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                        fwrite($fpb, "$log_date �����ֹ�:{$data[0]} : ��:{$data[1]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                        // query_affected_trans($con, "rollback");     // transaction rollback
                        $rec_ng++;
                        ////////////////////////////////////////// Debug start
                        for ($f=0; $f<$rows; $f++) {
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
                }
            }
        }
    }
}

//fclose($fp);
//fclose($fpw);       // debug
fwrite($fpa, "$log_date ͭ��ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
fwrite($fpa, "$log_date ͭ��ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
fwrite($fpb, "$log_date ͭ��ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n");
fwrite($fpb, "$log_date ͭ��ι��� : {$ins_ok}/{$rec} �� �ɲ� \n");
echo "$log_date ͭ��ι��� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
echo "$log_date ͭ��ι��� : {$ins_ok}/{$rec} �� �ɲ� \n";

// ��λ����� ɬ�������׻�
$query_u = "SELECT uid, reference_ym
          FROM five_yukyu_master
          WHERE business_year={$business_year};
        ";
$res = array();
$rows = getResult2($query_u, $res);
// �嵭�ξ��ǡ������ޤǤ��࿦�Ԥ����칩��μҰ��Ͻ��������
for ($r=0; $r<$rows; $r++) {
    if ($res[$r][1] == $ki_first_ymd) { // ��������������դξ�� ��λ��������������ɬ��������5��
        $end_ref_ym = $ki_end_ymd;
        if ($ki_first_ymd >= 20210401) {
            $need_day   = 6;
        } else {
            $need_day   = 5;
        }
    } elseif ($res[$r][1] > $ki_first_ymd) {
    // ������������ξ�� ����ȯ���ʤΤǽ�λ����+1ǯ��¸�����ե����å��ˡ�ɬ�������ϤȤꤢ����5��
    // ����ˤʤ�����դ������Ϲ�������롣
        $need_day    = 5;
        $cal_end_ymd = $res[$r][1] + 10000;                                     // ������Σ�ǯ��
        $e_yy = substr($cal_end_ymd, 0,4);                                      // �׻���ǯ
        $e_mm = substr($cal_end_ymd, 4,2);                                      // �׻��ѷ�
        $e_dd = substr($cal_end_ymd, 6,2);                                      // �׻�����
        $end_ref_ym = date('Ymd', mktime(0, 0, 0, $e_mm, $e_dd - 1, $e_yy));    // �������׻�
    } else {    // ������������ξ���ɬ�������η׻���Ԥ�����λ��������������
        $end_ref_ym = $ki_end_ymd;
        $cal_mm      = 0;                               // �׻��Ѥη����ꥻ�å�
        $b_ki_end_ymd = $ki_end_ymd - 10000;            // ������ǯ����
        $str_ym       = substr($res[$r][1], 0,6);       // ���ǯ��
        $b_end_ym     = substr($b_ki_end_ymd, 0,6);     // ������ǯ��
        $str_mm       = substr($res[$r][1], 4,2);       // ����
        if ($str_mm < 3) {  // 1��3��
            $cal_mm = $b_end_ym - $str_ym + 1 + 12;     // �׻��ѷ��
            if ($ki_first_ymd >= 20210401) {
                $need_day = round($cal_mm / 12 * 6, 1);     // ����ࣱ���ߣ��������׻�
            } else {
                $need_day = round($cal_mm / 12 * 5, 1);     // ����ࣱ���ߣ��������׻�
            }
            $need_day = ceil($need_day * 2) / 2;        // 0.5ñ�̤��ڤ�夲
        } else {
            $cal_mm = $b_end_ym - $str_ym - 87 + 12;    // �׻��ѷ��
            if ($ki_first_ymd >= 20210401) {
                $need_day = round($cal_mm /12 * 6, 1);      // ����ࣱ���ߣ��������׻�
            } else {
                $need_day = round($cal_mm /12 * 5, 1);      // ����ࣱ���ߣ��������׻�
            }
            $need_day = ceil($need_day * 2) / 2;        // 0.5ñ�̤��ڤ�夲
        }
    }
    $query = "UPDATE five_yukyu_master SET
                            need_day      = {$need_day},
                            end_ref_ym    = {$end_ref_ym}
                      WHERE uid ='{$res[$r][0]}'  and business_year={$business_year}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                break;                                  // debug
            } else {
            }
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
