#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// �Х��ޥ��ǡ��� ��ưFTP Download cron�ǽ����� cli��                       //
// AS/400 ----> Web Server (daily�ݴ�) �Х��ޥ���Web�Ǥϻ��Ѥ��ʤ�          //
// Copyright (C) 2016-2016 Norihisa.Ohya nirihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/09/15 Created  daily_backet_ftp_cli.php                              //
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
//���ʥޥ������ǡ���

// ���ʥޥ������ǡ��� ������� �������
$file_temp  = '/home/guest/daily/INP-C-BK.tmp';
$file_write = '/home/guest/daily/INP-C.CSV';

/////// ��������� �ѿ� �����
$c     = 0;

// ����Υǡ�����Хå����å�
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
///// ����Υǡ�������
if (file_exists($file_write)) {
    unlink($file_write);
}
fclose($fp);
fclose($fpw);
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
                            substr(mipn, 1, 1)='C' and substr(mipn, 1, 2)<>'CQ'
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
