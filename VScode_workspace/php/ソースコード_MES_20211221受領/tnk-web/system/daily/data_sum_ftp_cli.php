#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// DATA SUM ��Ω������� ��ưFTP Download cron�ǽ����� cgi��                //
// data_sum.tnk.co.jp ----> Web Server (PHP)                                //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/06/09 Created  data_sum_ftp_cgi.php �� data_sum_ftp_cli.php         //
// 2004/06/16 sleep(3) �� sleep(6) ���ѹ� �ݡ���󥰤�2��3���ٱ䤵��뤿��  //
// 2004/06/18 data_sum_log �ơ��֥�� AS/400�Υե�����ɤ˻����ʤ���碌��  //
// 2004/12/13 #!/usr/local/bin/php-4.3.8-cgi -q�� php(������5.0.3RC2)���ѹ� //
// 2005/03/29 ����ǡ����κ����Ǿ����ǹԤäƤ���(debug)�Τ�ǲ������ѹ�   //
//            �ǡ�����ʸ����������INSERT�˼��Ԥ������˥᡼�������ɲ�    //
// 2005/04/06 mail��subject��ǡ������ࢪDATA SUM ���ѹ� ʸ�������ɤΰ㤤�� //
//            ���Ρ��ĤΥ롼��(�ե��륿��)�˥ҥåȤ��ʤ����� EUC-JP��JIS  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'on');            // echo print �� flush ������(�٤��ʤ뤬�᡼����å��Τ���)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$fpa = fopen('/tmp/data_sum.log', 'a');     // �����ѥ��ե�����ؤν���ߤǥ����ץ�

// FTP�Υ������åȥե�����
define('LOG_DATA', 'log.dat');              // �ǡ�������Υ��ե����� download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('S_FILE', '/home/www/html/tnk-web/system/backup/data_sum.log');  // save file

/*****************************
if (file_exists(S_FILE)) {
    unlink(S_FILE);                         // ���Υǡ�����������Ϻ��
}
*****************************/

// FTP�ط������
define('SUM_HOST', '10.1.3.173');           // �������åȥۥ���
define('SUM_USER', 'data_sum');             // ��³�桼����̾
define('SUM_PASS', 'data_sum');             // �ѥ����
define('SUM_STOP', 'stop.dat');             // �ǡ�������Υ���ȥ���ե�����
define('LOCAL_NAME', '/home/www/html/tnk-web/system/daily/data_sum_ctl.dat');   // ����ȥ���ե�����Υ�����̾

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(SUM_HOST)) {
    if (ftp_login($ftp_stream, SUM_USER, SUM_PASS)) {
        ///// �ǡ�������إ���ȥ���ե�����(�̿����ǻؼ�)������
        if (ftp_put($ftp_stream, SUM_STOP, LOCAL_NAME, FTP_ASCII)) {
            // echo SUM_STOP, " upload OK \n";
            // fwrite($fpa, "$log_date " . SUM_STOP . " upload OK \n");
            ///// �̿����Ǥ�ȿ�Ǥ����ޤǣ��ô��Ԥ� �ݡ���󥰤ǣ��á������ٱ䤹��Τǣ��ä��ѹ�
            sleep(6);
            ///// �ǡ�������Υ��ե�����¸�ߥ����å�
            if (ftp_size($ftp_stream, LOG_DATA) != (-1) ) { // �ե����뤬¸�ߤ��Ƥ����
                ///// �ǡ�������Υ��ե��������
                if (ftp_get($ftp_stream, S_FILE, LOG_DATA, FTP_ASCII)) {
                    // echo 'ftp_get download OK ', LOG_DATA, ' �� ', S_FILE, "\n";
                    fwrite($fpa,"$log_date ftp_get download OK " . LOG_DATA . ' �� ' . S_FILE . "\n");
                    ///// �ǡ�������Υ��ե�������
                    if (ftp_delete($ftp_stream, LOG_DATA)) {
                        // echo LOG_DATA, ":delete OK \n";
                        fwrite($fpa,"$log_date " . LOG_DATA . ":delete OK \n");
                    } else {
                        // echo LOG_DATA, ":delete Error \n";
                        fwrite($fpa,"$log_date " . LOG_DATA . ":delete Error \n");
                    }
                } else {
                    // echo 'ftp_get() Error ', LOG_DATA, "\n";
                    fwrite($fpa,"$log_date ftp_get() Error " . LOG_DATA . "\n");
                }
            }
            ///// ����ȥ���ե�������
            if (ftp_delete($ftp_stream, SUM_STOP)) {
                // echo SUM_STOP, ":delete OK \n";
                // fwrite($fpa,"$log_date " . SUM_STOP . ":delete OK \n");
            } else {
                // echo SUM_STOP, ":delete Error \n";
                fwrite($fpa,"$log_date " . SUM_STOP . ":delete Error \n");
            }
        } else {
            // echo SUM_STOP, " upload Error \n";
            fwrite($fpa, "$log_date " . SUM_STOP . " upload error \n");
        }
    } else {
        // echo "ftp_login() Error \n";
        fwrite($fpa,"$log_date ftp_login() Error \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> DATA SUM\n";
    fwrite($fpa,"$log_date ftp_connect() error --> DATA SUM\n");
}



// �ǡ�������Υ��ե����� ������� �������
$file_orign  = S_FILE;
$file_nippo  = '/home/www/html/tnk-web/system/backup/data_sum_nippo.log';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_nippo, 'a+');    // �����ѥե�����Υ����ץ�
    chmod($file_nippo, 0666);           // �ƥ����Ѥ��ɲ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    /////////// DB ���ͥ���������
    $con = db_connect();
    while (!(feof($fp))) {
        $data = fgets($fp, 300);     // �¥쥳���ɤ�240�Х��ȤʤΤǤ���ä�;͵��
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $data = substr($data, 0, 238);      // ;ʬ�ʥǡ����򥫥åȤ���
        $data = $data . "\n";               // LF ���ղä���
        if (fwrite($fpw, $data, 300)) {
            $rec_ok++;
        } else {
            $rec_ng++;
        }
        /*****************************************/
        ///// FTPž���������ե����뤫�� DB����Ͽ
        $term_no = substr($data, 0, 2);                 // �С�������ü���ֹ�
                        // �� '20040611 163000' ������     �������(�����ॹ�����)
        $dsum_stamp = ( '20' . substr($data, 2, 6) . ' ' . substr($data, 8, 4) . '00' );
        $work_dsc = substr($data, 12, 3);               // ��ȶ�(�������)
        // ���ν���ߤϥǡ��������뤿������å�̵���ǽ����
        $emp_id1  = substr($data, 15, 6);               // �Ұ��ֹ� ���� ��ȼԥ�����
        $emp_id2  = substr($data, 21, 6);
        if ($emp_id2 == 0) $emp_id2 = '';
        $emp_id3  = substr($data, 27, 6);
        if ($emp_id3 == 0) $emp_id3 = '';
        $emp_id4  = substr($data, 33, 6);
        if ($emp_id4 == 0) $emp_id4 = '';
        $emp_id5  = substr($data, 39, 6);
        if ($emp_id5 == 0) $emp_id5 = '';
        $emp_id6  = substr($data, 45, 6);
        if ($emp_id6 == 0) $emp_id6 = '';
        $emp_id7  = substr($data, 51, 6);
        if ($emp_id7 == 0) $emp_id7 = '';
        $emp_id8  = substr($data, 57, 6);
        if ($emp_id8 == 0) $emp_id8 = '';
        $emp_id9  = substr($data, 63, 6);
        if ($emp_id9 == 0) $emp_id9 = '';
        $emp_id10 = substr($data, 69, 6);
        if ($emp_id10 == 0) $emp_id10 = '';
        $emp_id11 = substr($data, 75, 6);
        if ($emp_id11 == 0) $emp_id11 = '';
        $emp_id12 = substr($data, 81, 6);
        if ($emp_id12 == 0) $emp_id12 = '';
        $emp_id13 = substr($data, 87, 6);
        if ($emp_id13 == 0) $emp_id13 = '';
        $emp_id14 = substr($data, 93, 6);
        if ($emp_id14 == 0) $emp_id14 = '';
        $emp_id15 = substr($data, 99, 6);
        if ($emp_id15 == 0) $emp_id15 = '';
        $plan_no1 = substr($data, 105, 8);              // �ײ��ֹ�
        $work_qt1 = substr($data, 113, 5);              // ��ȿ� ʬǼ���ξ��
        $plan_no2 = substr($data, 118, 8);
        $plan_no2 = trim($plan_no2);
        $work_qt2 = substr($data, 126, 5);
        $plan_no3 = substr($data, 131, 8);
        $plan_no3 = trim($plan_no3);
        $work_qt3 = substr($data, 139, 5);
        $plan_no4 = substr($data, 144, 8);
        $plan_no4 = trim($plan_no4);
        $work_qt4 = substr($data, 152, 5);
        $plan_no5 = substr($data, 157, 8);
        $plan_no5 = trim($plan_no5);
        $work_qt5 = substr($data, 165, 5);
        $plan_no6 = substr($data, 170, 8);
        $plan_no6 = trim($plan_no6);
        $work_qt6 = substr($data, 178, 5);
        $plan_no7 = substr($data, 183, 8);
        $plan_no7 = trim($plan_no7);
        $work_qt7 = substr($data, 191, 5);
        $plan_no8 = substr($data, 196, 8);
        $plan_no8 = trim($plan_no8);
        $work_qt8 = substr($data, 204, 5);
        $plan_no9 = substr($data, 209, 8);
        $plan_no9 = trim($plan_no9);
        $work_qt9 = substr($data, 217, 5);
        $plan_no10= substr($data, 222, 8);
        $plan_no10= trim($plan_no10);
        $work_qt10= substr($data, 230, 5);
        $cut_time = substr($data, 235, 3);              // ���åȻ���
        $query = "
            INSERT INTO data_sum_log
                (term_no, dsum_stamp, work_dsc, emp_id1, emp_id2, emp_id3, emp_id4, emp_id5
                , emp_id6, emp_id7, emp_id8, emp_id9, emp_id10, emp_id11, emp_id12, emp_id13
                , emp_id14, emp_id15, plan_no1, work_qt1, plan_no2, work_qt2, plan_no3, work_qt3
                , plan_no4, work_qt4, plan_no5, work_qt5, plan_no6, work_qt6, plan_no7, work_qt7
                , plan_no8, work_qt8, plan_no9, work_qt9, plan_no10, work_qt10, cut_time)
            VALUES ({$term_no}, '{$dsum_stamp}', {$work_dsc}, '{$emp_id1}', '{$emp_id2}', '{$emp_id3}'
                    , '{$emp_id4}', '{$emp_id5}', '{$emp_id6}', '{$emp_id7}', '{$emp_id8}', '{$emp_id9}'
                    , '{$emp_id10}', '{$emp_id11}', '{$emp_id12}', '{$emp_id13}', '{$emp_id14}', '{$emp_id15}'
                    , '{$plan_no1}', {$work_qt1}, '{$plan_no2}', {$work_qt2}, '{$plan_no3}', {$work_qt3}
                    , '{$plan_no4}', {$work_qt4}, '{$plan_no5}', {$work_qt5}, '{$plan_no6}', {$work_qt6}
                    , '{$plan_no7}', {$work_qt7}, '{$plan_no8}', {$work_qt8}, '{$plan_no9}', {$work_qt9}
                    , '{$plan_no10}', {$work_qt10}, {$cut_time})
        ";
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa,"$log_date DB INSERT Error \n");
            `echo "�����꡼��\n $query" > /tmp/data_sum_error.txt`;
            `/bin/cat /tmp/data_sum_error.txt | /usr/bin/nkf -Ej | /bin/mail -s 'DATA SUM �Υǡ������顼 ü��=$term_no ����=$dsum_stamp' tnksys@nitto-kohki.co.jp , usoumu@nitto-kohki.co.jp `;
            `/bin/rm -f /tmp/data_sum_error.txt`;
        }
        /*****************************************/
    }
    unlink($file_orign);    // FTPž�������ǡ������४�ꥸ�ʥ��S_FILE data_sum.log ����
    fclose($fp);
    fclose($fpw);
}

fclose($fpa);      ////// �����ѥ�����߽�λ
?>
