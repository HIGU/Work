#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// �Х��ޥ��ǡ��� ��ưFTP Download cron�ǽ����� cli��                       //
// AS/400 ----> Web Server (daily�ݴ�) �Х��ޥ���Web�Ǥϻ��Ѥ��ʤ�          //
// Copyright (C) 2016-2016 Norihisa.Ohya nirihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/09/15 Created  daily_backet_ftp_cli.php                             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "�Х��åȥޥ����������ʡ��и˻ؼ�����\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_backet_ftp_cli.php\n");
echo "/home/www/html/tnk-web/system/daily/sales_get_ftp.php\n";

//�и˻ؼ��ǡ���

// FTP�Υ������åȥե�����
$target_file = 'FTPLIB/THMSSJP';        // download file
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
$save_file = '/home/guest/daily/THMSSJP.TXT';     // save file

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            echo "$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_get download OK ", $target_file, "��", $save_file, "\n";
            fwrite($fpa,"$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_get download OK " . $target_file . '��' . $save_file . "\n");
            fwrite($fpb,"$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_get download OK " . $target_file . '��' . $save_file . "\n");
        } else {
            echo "$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_get() error ", $target_file, "\n";
            fwrite($fpa,"$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_get() error " . $target_file . "\n");
            fwrite($fpb,"$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_get() error " . $target_file . "\n");
        }
    } else {
        echo "$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_login() error \n";
        fwrite($fpa,"$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_login() error \n");
        fwrite($fpb,"$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_connect() error --> MICCC\n";
    fwrite($fpa,"$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_connect() error --> MICCC\n");
    fwrite($fpb,"$log_date �Х��ޥ��и˻ؼ��ǡ��� ftp_connect() error --> MICCC\n");
}

/////// ��������� �ѿ� �����
$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$msg   = '';        // ��å�����
$flag1 = '';        // �����¹ԥե饰 ���
$flag2 = '';        // �����¹ԥե饰 �����ƥ�
$flag3 = '';        // �����¹ԥե饰 ���ʻų�
$flag4 = '';        // �����¹ԥե饰 ϫ̳�񡦷���
$b     = 0;         // �ƥ����ȥե�����Υ쥳���ɿ�
$c     = 0;
$d     = 0;

$first = 0;

// �и˻ؼ��ǡ��� ������� �������
$file_name  = '/home/guest/daily/THMSSJP.TXT';
$file_temp  = '/home/guest/daily/THMSSJP.tmp';
$file_write = '/home/guest/daily/YOTEI.TXT';
///// ����Υǡ�������
if (file_exists($file_write)) {
    unlink($file_write);
}
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
        $data = mb_convert_kana($data, 'KV', 'EUC-JP'); // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ� (DB��¸�������ѤǾȲ����ɬ�פ˱�����Ⱦ���Ѵ�����)
        fwrite($fpw,$data);
        $c++;
        if (feof($fp)) {
            $c--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp, 'r');
    $fpw = fopen($file_write, 'a');
    while (FALSE !== ($data = fgetcsv($fp, 300, ',')) ) {    // CSV file �Ȥ����ɹ���
        if ($data[0] == '') continue;   // ���Ԥν���
        $first += 1;                    // ������Ƚ����
        if ($first == 1) continue;      // �����ܤ�̵��
        $data[1] = substr($data[0], 21,9);      // �߸˥�����
        $data[2] = 'O';      // ��ȶ�ʬ
        $data[3] = substr($data[0], 32,5) * 1;      // �и˿�
        $data[4] = substr($data[0], 13,8);      // ��Ω�ؼ�No
        ///// data[0]�����ֹ��data[4]��Ͽ���϶�̳�Υ롼��奨�������פ���ɬ�פ�̵��
        fwrite($fpw,"{$data[1]},{$data[2]},{$data[3]},{$data[4]}\r\n");
        ///// ʸ������(��̾��)��","�����ä����� fgetcsv()�ˤޤ����롣
        
    }
    fclose($fp);
    fclose($fpw);
    // unlink($file_name);     // ����ե�������� CSV
    // unlink($file_temp);     // ����ե�������� tmp
    if (file_exists("{$file_name}.bak")) {
        unlink("{$file_name}.bak");         // ����Υǡ�������
    }
    if (file_exists("{$file_temp}.bak")) {
        unlink("{$file_temp}.bak");         // ����Υǡ�������
    }
    if (!rename($file_name, "{$file_name}.bak")) {
        echo "$log_date DownLoad File $file_name ��Backup�Ǥ��ޤ���\n";
    }
    if (!rename($file_temp, "{$file_temp}.bak")) {
        echo "$log_date DownLoad File $file_temp ��Backup�Ǥ��ޤ���\n";
    }
}

//���ʥޥ������ǡ���

// ���ʥޥ������ǡ��� ������� �������
$file_temp  = '/home/guest/daily/INP-BK.tmp';
$file_write = '/home/guest/daily/INP.CSV';

/////// ��������� �ѿ� �����
$c     = 0;

// ����Υǡ�����Хå����å�
if (file_exists($file_write)) {
    $fp  = fopen($file_write, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,300);
        fwrite($fpw,$data);
        $c++;
        if (feof($fp)) {
            $c--;
            break;
        }
    }
}
///// ����Υǡ�������
if (file_exists($file_write)) {
    unlink($file_write);
    fclose($fp);
    fclose($fpw);
}
$fpw = fopen($file_write, 'a');
//////////// CSV�����ѤΥǡ�������
$query_csv = sprintf("select
                            mipn                          as �߸˥�����,    -- 0
                            to_char(last_date,'yyyymmdd') as ��Ͽ��,        -- 1
                            ''                            as ��Ͽ����,      -- 2
                            trim(midsc)                   as ��̾           -- 3
                        from
                            miitem
                        where
                            to_char(last_date,'yyyy-mm-dd')>current_date-3
                    ");
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // ľ���θƽи������
    exit();
} else {
    for ($r=0; $r<$rows_csv; $r++) {    // �ǡ�����CSV�˽���
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'EUC-JP');   // EUC-JP��SJIS���Ѵ�
        fwrite($fpw,"{$res_csv[$r][0]},{$res_csv[$r][1]},{$res_csv[$r][2]},{$res_csv[$r][3]}\r\n");
    }
    echo "$log_date �Х��ޥ����ʥޥ������ǡ��� ���� OK Web miitem ��", $file_write, "\n";
    fwrite($fpa,"$log_date �Х��ޥ����ʥޥ������ǡ��� ���� OK Web miitem ��" . $file_write . "\n");
    fwrite($fpb,"$log_date �Х��ޥ����ʥޥ������ǡ��� ���� Web miitem ��" . $file_write . "\n");
}
fclose($fpw);

fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
