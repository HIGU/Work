#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ŭ�����Ϣ��񹹿� �Хå��� (W#MICLDTN1,W#MICLDTN2) ��� ������ CLI��  //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2013-2013 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// AS UKSLIB/QCLSRC \TNKDAILYC��LOOP�����˰ʲ�����Ͽ���뤳��                //
// SNDF     RCDFMT(TITLE)                                                   //
// SNDF     RCDFMT(MICLDTN1)                                                //
// RUNQRY   QRY(UKPLIB/Q#MICLDTN1)                                          //
// \FTPTNK  USER(AS400) ASFILE(W#MICLDTN1) PCFILE(Q#MICLDTN1.TXT) MODE(TXT) //
// SNDF     RCDFMT(TITLE)                                                   //
// SNDF     RCDFMT(MICLDTN2)                                                //
// RUNQRY   QRY(UKPLIB/Q#MICLDTN2)                                          //
// \FTPTNK  USER(AS400) ASFILE(W#MICLDTN2) PCFILE(Q#MICLDTN2.TXT) MODE(TXT) //
// Changed history                                                          //
// 2013/01/10 Created  claim_disposal_details_update_cli.php                //
// 2013/01/25 update���Υǡ���ȴ������                                    //
// 2013/01/28 �ǡ������ɲ�                                                  //
// 2013/01/29 ʸ�������β��ȥǡ���������ΰ١��ե�����򣲤Ĥ�ʬ����      //
// 2013/01/31 �������Υ᡼����ɽ��������                                  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�ǤʤΤ�ɬ�פʤ�
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "��Ŭ�����Ϣ���ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/claim_disposal_details_update_cli.php \n");

$_ENV['LANG'] = 'ja_JP.eucJP';
/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
//     $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    fwrite($fpb, "$log_date db_connect() error \n");
    exit();
}
// ��Ŭ�����Ϣ��� ��Ͽ���� �������
$file_orign1  = '/home/guest/daily/W#MICLDTN1.TXT';
$file_temp1   = '/home/guest/daily/W#MICLDTN1-TEMP.TXT';

$sql = "DELETE FROM claim_disposal_details";
query_affected($sql);
echo "$log_date ��Ŭ�����Ϣ��񥵥ޥ꡼�������Ƽ¹�\n";
fwrite($fpa, "$log_date ��Ŭ�����Ϣ��񥵥ޥ꡼�������Ƽ¹�\n");
fwrite($fpb, "$log_date ��Ŭ�����Ϣ��񥵥ޥ꡼�������Ƽ¹�\n");

// ��Ŭ�����Ϣ��� ��Ͽ����
if(file_exists($file_orign1)){           // �ե������¸�ߥ����å�
    $fp = fopen($file_orign1,"r");
        ///////////// SJIS �� EUC �Ѵ����å� START (SJIS��EUC�ˤʤ�ʸ����NULL�Х��Ȥ��Ѵ������������)
    $fp_conv = fopen($file_temp1, 'w');  // EUC ���Ѵ���
    while (!(feof($fp))) {
        $data = fgets($fp,800);     // �ºݤˤ�120 ��OK����;͵����ä�
        ///////////// SJIS �� EUC �Ѵ����å� START (SJIS��EUC�ˤʤ�ʸ����NULL�Х��Ȥ��Ѵ������������)
        //$data = mb_convert_encoding($data, 'EUC-JP', 'SJIS');             // SJIS��EUC-JP���Ѵ�(auto)
        $data = mb_convert_encoding($data, 'eucJP-win', 'sjis-win');      // SJIS��EUC-JP���Ѵ�(auto)
        $data = str_replace("\0", ' ', $data);                            // NULL�Х��Ȥ�SPACE���Ѵ�
        $data = mb_ereg_replace('��', '  ', $data);                       // ���֤������Τ����ѥ��ڡ�����Ⱦ�Ѥ�
        $data = mb_ereg_replace('��', '||', $data);                       // �����¸ʸ���򵬳�ʸ���ذ���ѹ�
        $data = mb_ereg_replace('��', '@@', $data);                       // �����¸ʸ���򵬳�ʸ���ذ���ѹ�
        fwrite($fp_conv, $data);
    }
    fclose($fp);
    fclose($fp_conv);
    $fp = fopen($file_temp1, 'r');       // EUC ���Ѵ���Υե�����
    ///////////// SJIS �� EUC �Ѵ����å� END
    $rec1 = 0;       // �쥳���ɭ�
    $rec1_ok = 0;    // ����������쥳���ɿ�
    $rec1_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins1_ok = 0;    // INSERT�ѥ����󥿡�
    $upd1_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!feof($fp)) {            // �ե������EOF�����å�
        $data = fgetcsv($fp, 800, "_");     // �¥쥳���ɤ�150�Х��� �ǥ�ߥ��ϥ��֤��饢��������������ѹ�
        if (feof($fp)) {
            break;
        }
        $rec1++;
        $num  = count($data);       // �ե�����ɿ��μ���
        for ($f=0; $f<$num; $f++) {
            // $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        $data[4] = str_replace('||', '(��)', $data[4]);       // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[4] = str_replace('@@', 'No.', $data[4]);        // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[6] = str_replace('||', '(��)', $data[6]);       // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[6] = str_replace('@@', 'No.', $data[6]);        // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[7] = str_replace('||', '(��)', $data[7]);       // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[7] = str_replace('@@', 'No.', $data[7]);        // �����¸ʸ���򵬳�ʸ�����ѹ�
        
        if ($data[10] != '') {
            if ($data[10] != '00') {
                if ($data[10] != '0 ') {
                    if ($data[10] != ' 0') {
                        $data[10] = $data[10] * 1;
                        $data[10] = '0' . $data[10];
                    } else {
                        $data[10] = '00';
                    }
                } else {
                    $data[10] = '00';
                }
            }
        }
        $query_chk = sprintf("SELECT assy_no FROM claim_disposal_details WHERE assy_no='%s' AND publish_no='%s' AND parts_no='%s'", $data[0], $data[1], $data[5]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO claim_disposal_details (assy_no, publish_no, publish_date, claim_no,
                     claim_name, parts_no, claim_explain1, claim_explain2, ans_hope_date, delivery_date,
                     process_name, claim_sec, product_no, delivery_num, bad_num, bad_par, charge_no)
                      VALUES(
                      '{$data[0]}',
                      '{$data[1]}',
                      '{$data[2]}',
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                      '{$data[7]}',
                       {$data[8]} ,
                       {$data[9]} ,
                      '{$data[10]}',
                      '{$data[11]}',
                      '{$data[12]}',
                       {$data[13]} ,
                       {$data[14]} ,
                       {$data[15]} ,
                      '{$data[16]}')";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                // $_SESSION['s_sysmsg'] .= "{$rec1}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!<br>";
                fwrite($fpa, "$log_date ȯ���ֹ�:{$data[1]} : {$rec1}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ȯ���ֹ�:{$data[1]} : {$rec1}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec1_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec1_ok++;
                $ins1_ok++;
            }
        } else {
            ///// ��Ͽ���� update ����
            $query = "UPDATE claim_disposal_details SET publish_date={$data[2]}, claim_no='{$data[3]}', claim_name='{$data[4]}',
                      claim_explain1='{$data[6]}', claim_explain2='{$data[7]}', ans_hope_date={$data[8]},
                      delivery_date={$data[9]}, process_name='{$data[10]}', claim_sec='{$data[11]}', product_no='{$data[12]}',
                      delivery_num={$data[13]}, bad_num={$data[14]}, bad_par={$data[15]}, charge_no='{$data[16]}'
                      where assy_no='{$data[0]}' and publish_no='{$data[1]}' and parts_no='{$data[5]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                // $_SESSION['s_sysmsg'] .= "{$rec1}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                fwrite($fpa, "$log_date ȯ���ֹ�:{$data[1]} : {$rec1}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ȯ���ֹ�:{$data[1]} : {$rec1}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec1_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec1_ok++;
                $upd1_ok++;
            }
        }
    }
    fclose($fp);
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec1_ok}/{$rec1} ����Ͽ���ޤ�����</font><br><br>";
    // $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins1_ok}/{$rec1} �� �ɲ�<br>";
    // $_SESSION['s_sysmsg'] .= "{$upd1_ok}/{$rec1} �� �ѹ�</font>";
    echo "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$rec1_ok}/{$rec1} ����Ͽ���ޤ�����\n";
    echo "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$ins1_ok}/{$rec1} �� �ɲ� \n";
    echo "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$upd1_ok}/{$rec1} �� �ѹ� \n";
    fwrite($fpa, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : $rec1_ok/$rec1 ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$ins1_ok}/{$rec1} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$upd1_ok}/{$rec1} �� �ѹ� \n");
    fwrite($fpb, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : $rec1_ok/$rec1 ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$ins1_ok}/{$rec1} �� �ɲ� \n");
    fwrite($fpb, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$upd1_ok}/{$rec1} �� �ѹ� \n");
} else {
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>�ȥ�󥶥������ե����뤬����ޤ���</font>";
    fwrite($fpa,"$log_date ��Ŭ�����Ϣ���ι����ե����룱 :  {$file_orign1} ������ޤ���\n");
    fwrite($fpb,"$log_date ��Ŭ�����Ϣ���ι����ե����룱 :  {$file_orign1} ������ޤ���\n");
    echo '$log_date ��Ŭ�����Ϣ���ι����ե����룱 :  {$file_orign1} ������ޤ���\n';
}

// ��Ŭ�����Ϣ��� ��Ͽ���� �������
$file_orign2  = '/home/guest/daily/W#MICLDTN2.TXT';
$file_temp2   = '/home/guest/daily/W#MICLDTN2-TEMP.TXT';

// ��Ŭ�����Ϣ��� ��Ͽ����
if(file_exists($file_orign2)){           // �ե������¸�ߥ����å�
    $fp = fopen($file_orign2,"r");
        ///////////// SJIS �� EUC �Ѵ����å� START (SJIS��EUC�ˤʤ�ʸ����NULL�Х��Ȥ��Ѵ������������)
    $fp_conv = fopen($file_temp2, 'w');  // EUC ���Ѵ���
    while (!(feof($fp))) {
        $data = fgets($fp,800);     // �ºݤˤ�120 ��OK����;͵����ä�
        ///////////// SJIS �� EUC �Ѵ����å� START (SJIS��EUC�ˤʤ�ʸ����NULL�Х��Ȥ��Ѵ������������)
        //$data = mb_convert_encoding($data, 'EUC-JP', 'SJIS');             // SJIS��EUC-JP���Ѵ�(auto)
        $data = mb_convert_encoding($data, 'eucJP-win', 'sjis-win');      // SJIS��EUC-JP���Ѵ�(auto)
        $data = str_replace("\0", ' ', $data);                            // NULL�Х��Ȥ�SPACE���Ѵ�
        $data = mb_ereg_replace('��', '  ', $data);                       // ���֤������Τ����ѥ��ڡ�����Ⱦ�Ѥ�
        $data = mb_ereg_replace('��', '||', $data);                       // �����¸ʸ���򵬳�ʸ���ذ���ѹ�
        $data = mb_ereg_replace('��', '@@', $data);                       // �����¸ʸ���򵬳�ʸ���ذ���ѹ�
        fwrite($fp_conv, $data);
    }
    fclose($fp);
    fclose($fp_conv);
    $fp = fopen($file_temp2, 'r');       // EUC ���Ѵ���Υե�����
    ///////////// SJIS �� EUC �Ѵ����å� END
    $rec2 = 0;       // �쥳���ɭ�
    $rec2_ok = 0;    // ����������쥳���ɿ�
    $rec2_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins2_ok = 0;    // INSERT�ѥ����󥿡�
    $upd2_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!feof($fp)) {            // �ե������EOF�����å�
        $data = fgetcsv($fp, 800, "_");     // �¥쥳���ɤ�150�Х��� �ǥ�ߥ��ϥ��֤��饢��������������ѹ�
        if (feof($fp)) {
            break;
        }
        $rec2++;
        $num  = count($data);       // �ե�����ɿ��μ���
        for ($f=0; $f<$num; $f++) {
            // $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        $data[3]  = str_replace('||', '(��)', $data[3]);    // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[3]  = str_replace('@@', 'No.', $data[3]);     // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[4]  = str_replace('||', '(��)', $data[4]);    // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[4]  = str_replace('@@', 'No.', $data[4]);     // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[5]  = str_replace('||', '(��)', $data[5]);    // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[5]  = str_replace('@@', 'No.', $data[5]);     // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[6]  = str_replace('||', '(��)', $data[6]);    // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[6]  = str_replace('@@', 'No.', $data[6]);     // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[7]  = str_replace('||', '(��)', $data[7]);    // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[7]  = str_replace('@@', 'No.', $data[7]);     // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[8]  = str_replace('||', '(��)', $data[8]);    // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[8]  = str_replace('@@', 'No.', $data[8]);     // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[9]  = str_replace('||', '(��)', $data[9]);    // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[9]  = str_replace('@@', 'No.', $data[9]);     // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[10] = str_replace('||', '(��)', $data[10]);   // �����¸ʸ���򵬳�ʸ�����ѹ�
        $data[10] = str_replace('@@', 'No.', $data[10]);    // �����¸ʸ���򵬳�ʸ�����ѹ�
        
        $query_chk = sprintf("SELECT assy_no FROM claim_disposal_details WHERE assy_no='%s' AND publish_no='%s' AND parts_no='%s'", $data[0], $data[1], $data[2]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ��Ϥ��ꤨ�ʤ��Τǲ��⤷�ʤ�
        } else {
            ///// ��Ͽ���� update ����
            $query = "UPDATE claim_disposal_details SET occur_cause1='{$data[3]}', occur_cause2='{$data[4]}', outflow_cause1='{$data[5]}', outflow_cause2='{$data[6]}',
                      occur_measures1='{$data[7]}', occur_measures2='{$data[8]}', outflow_measures1='{$data[9]}', outflow_measures2='{$data[10]}'
                      where assy_no='{$data[0]}' and publish_no='{$data[1]}' and parts_no='{$data[2]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                // $_SESSION['s_sysmsg'] .= "{$rec2}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                fwrite($fpa, "$log_date ȯ���ֹ�:{$data[1]} : {$rec2}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date ȯ���ֹ�:{$data[1]} : {$rec2}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec2_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec2_ok++;
                $upd2_ok++;
            }
        }
    }
    fclose($fp);
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec2_ok}/{$rec2} ����Ͽ���ޤ�����</font><br><br>";
    // $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins2_ok}/{$rec2} �� �ɲ�<br>";
    // $_SESSION['s_sysmsg'] .= "{$upd2_ok}/{$rec2} �� �ѹ�</font>";
    echo "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$rec2_ok}/{$rec2} ����Ͽ���ޤ�����\n";
    echo "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$ins2_ok}/{$rec2} �� �ɲ� \n";
    echo "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$upd2_ok}/{$rec2} �� �ѹ� \n";
    fwrite($fpa, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : $rec2_ok/$rec2 ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$ins2_ok}/{$rec2} �� �ɲ� \n");
    fwrite($fpa, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$upd2_ok}/{$rec2} �� �ѹ� \n");
    fwrite($fpb, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : $rec2_ok/$rec2 ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$ins2_ok}/{$rec2} �� �ɲ� \n");
    fwrite($fpb, "$log_date ��Ŭ�����Ϣ���ե�����ι����� : {$upd2_ok}/{$rec2} �� �ѹ� \n");
} else {
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>�ȥ�󥶥������ե����뤬����ޤ���</font>";
    fwrite($fpa,"$log_date ��Ŭ�����Ϣ���ι����ե����룲 : {$file_orign2} ������ޤ���\n");
    fwrite($fpb,"$log_date ��Ŭ�����Ϣ���ι����ե����룲 : {$file_orign2} ������ޤ���\n");
    echo '$log_date ��Ŭ�����Ϣ���ι����ե����룲 : {$file_orign2} ������ޤ���\n';
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fwrite($fpa,"$log_date : LANG = {$_ENV['LANG']}\n");    // fgetcsv()�Ѥ�LANG�Ķ��ѿ��γ�ǧ
fwrite($fpb,"$log_date : LANG = {$_ENV['LANG']}\n");    // fgetcsv()�Ѥ�LANG�Ķ��ѿ��γ�ǧ
fclose($fpa);      ////// �����ѥ�����߽�λ
fclose($fpb);      ////// �����ѥ�����߽�λ

// header('Location: ' . H_WEB_HOST . ACT . 'vendor_master_view.php');   // �����å��ꥹ�Ȥ�
exit();
?>
