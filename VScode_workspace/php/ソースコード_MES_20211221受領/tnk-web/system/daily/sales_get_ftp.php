#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����ǡ��� ��ưFTP Download  cron �ǽ�����       ���ޥ�ɥ饤����        //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2002-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed histoy                                                           //
// 2002/03/11 Created  sales_get_ftp.php   (��uriage_ftp.php)               //
// 2002/11/28 �ƥ����Ǥ� debug �� �Τ��������˥�꡼��                      //
// 2003/05/30 ��Ω�����ײ�ǡ���������������ɲ�                            //
// 2003/06/06 AS/400��TIPPLNP���Υȥ�󥶥������ե�����ϥ���̵����ʪ��   //
//             �ե������Ƭ������֤��ɹ�������ʤ��Ȥ����ʤ���ʣ�쥳���ɤ� //
//             ���뤿��ǿ����ݤƤʤ���                                     //
// 2003/06/20 W#MIITEM��FTP_BINARY��Download������Ⱦ�ѥ���(EBCDIC)���Ѵ�����//
// 2003/11/14 php �� php-4.3.4-cgi ���ѹ�(���Τ�cgi��Ȥ����Ȥ�ʬ����褦��)//
// 2003/11/17 cgi �� cli�Ǥ��ѹ������褦�� requier_once �����л����      //
// 2004/04/21 FTP�Υ������åȤȥ�����ե������define()�����줷backup/ �� //
// 2004/04/30 FTP���� ���̤�����ǡ������ɲ� FTP Download �Τ�              //
// 2004/06/07 php-4.3.6-cgi -q �� php-4.3.7-cgi -q  �С�����󥢥åפ�ȼ��  //
// 2004/11/18 php-5.0.2-cli�إС�����󥢥å� *�����륹����ץȤ��б����ѹ� //
//            MIITEM���̥ץ����ǽ������Ƥ��뤿����å�����          //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli �� php (������5.0.3RC2)���ѹ�  //
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
// 2010/01/19 �᡼���ʬ����䤹������٤ˡ����ա�����������ɲ�     ��ë //
// 2010/01/20 $log_date�������'�Ǥ�̵��"�ʤΤǽ���                    ��ë //
// 2010/10/14 ���̤��ޥ��ʥ��ξ��ϡ�Web�ץ����ǤϤʤ�                 //
//            AS�θ��ǡ�����ľ�ܽ������ʤ��ȡ��ǡ��������ޤ����ʤ�   ��ë //
// 2018/09/07 20180901�ʹߡ�������L��datatype=5(��ư)�������ֹ����Ƭ��     //
//            'T'�ξ�硢������T���ѹ�                                      //
//            ������C��������뤬�Ȥꤢ�����ϻ�����L�ΰ�ư�Τ�         ��ë //
// 2019/11/28 20191128�ʹߡ�������L��datatype=7(���)�������ֹ����Ƭ��     //
//            'T'�ξ�硢������T���ѹ��ʥ٥�ȥ���б�                 ��ë //
// 2020/02/04 �٥�ȥ��б��ǻ�����C��¾�Υǡ�����̵���ä��Τ�               //
//            ���������ǧ���������ֹ�ǻ�����T���ѹ����롣                 //
//            ��碌�ơ�����������ɤ˴ؤ��Ƥ⤽�줾��λ�������          //
//            ����Ū���ѹ�                                             ��ë //
// 2021/05/31 2021/04����T���ѹ�������ʬ�򥳥��Ȳ����ޤ�������T��         //
//            ��Ƥ��ޤä���Τϻ�����L���ѹ�                          ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "���ǡ����ι���(����ʬ)�����̤�����ǡ���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/sales_get_ftp.php\n");
echo "/home/www/html/tnk-web/system/daily/sales_get_ftp.php\n";

// FTP�Υ������åȥե�����
define('HIUURA', 'UKWLIB/W#HIUURA');        // ���ե�����
///// define('MIITEM', 'UKWLIB/W#MIITEM');        // �����ƥ�ޥ�����
define('TIUKSL', 'UKWLIB/W#TIUKSL');        // ���TR�ե�����
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('W_HIUURI', '/home/www/html/tnk-web/system/backup/W#HIUURI.TXT');  // ���
///// define('W_MIITEM', 'backup/W#MIITEM.TXT');  // �����ƥ�
define('W_TIUKSL', '/home/www/html/tnk-web/system/backup/W#TIUKSL.TXT');  // ���TR��Download�ե�����
// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        /*** �������ǡ��� ***/
        if (ftp_get($ftp_stream, W_HIUURI, HIUURA, FTP_ASCII)) {
            echo "$log_date ���ǡ��� ftp_get download OK ", HIUURA, "��", W_HIUURI, "\n";
            fwrite($fpa,"$log_date ���ǡ��� ftp_get download OK " . HIUURA . '��' . W_HIUURI . "\n");
            fwrite($fpb,"$log_date ���ǡ��� ftp_get download OK " . HIUURA . '��' . W_HIUURI . "\n");
        } else {
            echo "$log_date ���ǡ��� ftp_get() error ", HIUURA, "\n";
            fwrite($fpa,"$log_date ���ǡ��� ftp_get() error " . HIUURA . "\n");
            fwrite($fpb,"$log_date ���ǡ��� ftp_get() error " . HIUURA . "\n");
        }
        /*** ���ʡ����ʥ����ƥ�ޥ����� ***/
        /*****************************************
        if (ftp_get($ftp_stream, W_MIITEM, MIITEM, FTP_ASCII)) {
            echo 'ftp_get download OK ', MIITEM, '��', W_MIITEM, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . MIITEM . "��" . W_MIITEM . "\n");
        } else {
            echo 'ftp_get() error ', MIITEM, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . MIITEM . "\n");
        }
        *****************************************/
        /*** ���̤�����ǡ��� ***/
        if (ftp_get($ftp_stream, W_TIUKSL, TIUKSL, FTP_ASCII)) {   // FTP_ASCII �� FTP_BINARY �Υƥ���
            echo "$log_date ���̤�����ǡ��� ftp_get download OK ", TIUKSL, "��", W_TIUKSL, "\n";
            fwrite($fpa,"$log_date ���̤�����ǡ��� ftp_get download OK " . TIUKSL . '��' . W_TIUKSL . "\n");
            fwrite($fpb,"$log_date ���̤�����ǡ��� ftp_get download OK " . TIUKSL . '��' . W_TIUKSL . "\n");
        } else {
            echo "$log_date ���̤�����ǡ��� ftp_get() error ", TIUKSL, "\n";
            fwrite($fpa,"$log_date ���̤�����ǡ��� ftp_get() error " . TIUKSL . "\n");
            fwrite($fpb,"$log_date ���̤�����ǡ��� ftp_get() error " . TIUKSL . "\n");
        }
    } else {
        echo "$log_date ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
        fwrite($fpb,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> ��塦�����ƥ�\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ��塦�����ƥ�\n");
    fwrite($fpb,"$log_date ftp_connect() error --> ��塦�����ƥ�\n");
}

// ��� ������� �������
$file_orign = W_HIUURI;
// $file_test  = "hiuuri.txt";
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign,"r");
    // $fpw = fopen($file_test,"w");        // TEST �ѥե�����Υ����ץ�
    $div    = array();
    $date_s = array();
    $date_k = array();
    $assyno = array();
    $sei_no = array();
    $planno = array();
    $seizou = array();
    $tyumon = array();
    $hakkou = array();
    $nyuuko = array();
    $kan_no = array();
    $den_no = array();
    $suryou = array();
    $tanka1 = array();
    $tanka2 = array();
    $tokusa = array();
    $datatp = array();
    $tokuis = array();
    $bikou  = array();
    $kubun  = array();
    $rec = 0;       // �쥳���ɭ�
    while (1) {
        $data=fgets($fp,120);
        $data = mb_convert_encoding($data, "EUC-JP", "auto");       // auto��EUC-JP���Ѵ�
        // $data_KV = mb_convert_kana($data);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        // fwrite($fpw,$data_KV);
        if (feof($fp)) {
            break;
        }
        $div[$rec]    = substr($data,0,1);      // ������
        $date_s[$rec] = substr($data,1,8);      // ������
        $date_k[$rec] = substr($data,9,8);      // �׾���
        $assyno[$rec] = substr($data,17,9);     // ���ʡ����ʭ�
        $sei_no[$rec] = substr($data,26,9);     // ���ʥ�����
        $planno[$rec] = substr($data,35,8);     // �ײ��
        $seizou[$rec] = substr($data,43,7);     // ��¤��
        $tyumon[$rec] = substr($data,50,7);     // ��ʸ��
        $hakkou[$rec] = substr($data,57,7);     // ȯ�ԭ�
        $nyuuko[$rec] = substr($data,64,2);     // ���˾��
        $kan_no[$rec] = substr($data,66,5);     // ��Ω��λ��
        $den_no[$rec] = substr($data,71,6);     // ��ɼ��
        $suryou[$rec] = substr($data,77,6);     // ����
        $tanka1[$rec]  = substr($data,83,7);    // ñ��(������)
        $tanka2[$rec]  = substr($data,90,2);    // ñ��(������)
        $tokusa[$rec] = substr($data,92,3);     // �ú�Ψ
        $datatp[$rec] = substr($data,95,1);     // datatype
        $tokuis[$rec] = substr($data,96,5);     // ������
        $bikou[$rec] = substr($data,101,15);    // ����
        $kubun[$rec] = substr($data,116,1);     // �����ʬ
        // ��������L��datatype��5�������ֹ����Ƭ��'T'�λ�����������T���ѹ�
        /*
        if ($date_k[$rec]>=20180901) {
            if ($datatp[$rec]=='5') {
                if ($div[$rec]=='L') {
                    if (substr($assyno[$rec],0,1)=='T') {
                        $div[$rec] = 'T';
                    }
                }
            }
        }
        */
        // datatype��7�������ֹ����Ƭ��'T'�λ�����������T���ѹ��ʥ٥�ȥ��
        /*
        if ($date_k[$rec]>=20191128) {
            if ($datatp[$rec]=='7') {
                //if ($div[$rec]=='L' || $div[$rec]=='C') {
                    if (substr($assyno[$rec],0,1)=='T') {
                        $div[$rec] = 'T';
                    }
                //}
            }
        }
        */
        // �����б��ʵ�������Ƥ��ޤä���ΤϤȤꤢ����L�˰ܹԡ�
        if ($date_k[$rec]>=20210401) {
            if ($div[$rec] == 'T') {
                $div[$rec] =  'L';
            }
        }
        // ������б� datatype��7�������ֹ����Ƭ��'SS'�λ�����������L���ѹ�
        if ($date_k[$rec]>=20191128) {
            if ($datatp[$rec]=='7') {
                if (substr($assyno[$rec],0,2)=='SS') {
                    $div[$rec] = 'L';
                }
            }
        }
        // �����б� datatype��7�������ֹ����Ƭ��'NKB'�λ�����������C���ѹ�
        if ($date_k[$rec]>=20191128) {
            if ($datatp[$rec]=='7') {
                if (substr($assyno[$rec],0,3)=='NKB') {
                    $div[$rec] = 'C';
                }
            }
        }
    /* �ƥ����Ѥ˥ե��������Ȥ�
        fwrite($fpw,$div[$rec]    . "\n");
        fwrite($fpw,$date_s[$rec] . "\n");
        fwrite($fpw,$date_k[$rec] . "\n");
        fwrite($fpw,$assyno[$rec] . "\n");
        fwrite($fpw,$sei_no[$rec] . "\n");
        fwrite($fpw,$planno[$rec] . "\n");
        fwrite($fpw,$seizou[$rec] . "\n");
        fwrite($fpw,$tyumon[$rec] . "\n");
        fwrite($fpw,$hakkou[$rec] . "\n");
        fwrite($fpw,$nyuuko[$rec] . "\n");
        fwrite($fpw,$kan_no[$rec] . "\n");
        fwrite($fpw,$den_no[$rec] . "\n");
        fwrite($fpw,$suryou[$rec] . "\n");
        fwrite($fpw,$tanka1[$rec]  . ".");
        fwrite($fpw,$tanka2[$rec]  . "\n");
        fwrite($fpw,$tokusa[$rec] . "\n");
        fwrite($fpw,$datatp[$rec] . "\n");
        fwrite($fpw,$tokuis[$rec] . "\n");
        fwrite($fpw,$bikou[$rec]  . "\n");
        fwrite($fpw,$kubun[$rec]  . "\n");
            �ƥ����� END */
        $rec++;
    }
    fclose($fp);
    // fclose($fpw);
}

if ($rec >= 1) { // �쥳���ɿ��Υ����å�
    $res_chk = array();
    $query_chk = "select �׾��� from hiuuri where �׾���=" . $date_k[0];
    if (getResult($query_chk,$res_chk)<=0) {
        for ($i=0; $i<$rec; $i++) {
            $query = "insert into hiuuri values('";
            $query .= $div[$i] . "',";
            $query .= $date_s[$i] . ",";
            $query .= $date_k[$i] . ",'";
            $query .= $assyno[$i] . "','";
            $query .= $sei_no[$i] . "','";
            $query .= $planno[$i] . "',";
            $query .= $seizou[$i] . ",";
            $query .= $tyumon[$i] . ",";
            $query .= $hakkou[$i] . ",'";
            $query .= $nyuuko[$i] . "',";
            $query .= $kan_no[$i] . ",'";
            $query .= $den_no[$i] . "',";
            $query .= $suryou[$i] . ",";
            $query .= $tanka1[$i] . "."; // �����������
            $query .= $tanka2[$i] . ",";
            $query .= $tokusa[$i] . ",'";
            $query .= $datatp[$i] . "','";
            $query .= $tokuis[$i] . "','";
            $query .= $bikou[$i] . "','";
            $query .= $kubun[$i] . "')";
            if (query_affected($query) <= 0) {     // �����ѥ����꡼�μ¹�
                fwrite($fpa,"$log_date ��� �׾���:".$date_k[$i].": ".($i+1).":�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb,"$log_date ��� �׾���:".$date_k[$i].": ".($i+1).":�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                echo "$log_date ��� �׾���:", ($i+1), ":�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
            }
//          $res_add = array();
//          $rows = getResult($query,$res_add);    // �쥿���פΥ����꡼function
        }
        fwrite($fpa,"$log_date ��� �׾���:" . $date_k[0] . ": " . $rec . " ����Ͽ���ޤ�����\n");
        fwrite($fpb,"$log_date ��� �׾���:" . $date_k[0] . ": " . $rec . " ����Ͽ���ޤ�����\n");
        echo "$log_date ��� �׾���:", $rec, " ����Ͽ���ޤ�����\n";
    } else {
        fwrite($fpa,"$log_date ��� �׾���:" . $date_k[0] . " ������Ͽ����Ƥ��ޤ�!\n");
        fwrite($fpb,"$log_date ��� �׾���:" . $date_k[0] . " ������Ͽ����Ƥ��ޤ�!\n");
        echo "$log_date ��� �׾���:", $date_k[0], " ������Ͽ����Ƥ��ޤ�!\n";
    }
} else {
    fwrite($fpa,"$log_date ���ǡ��� �쥳���ɤ�����ޤ���!\n");
    fwrite($fpb,"$log_date ���ǡ��� �쥳���ɤ�����ޤ���!\n");
    echo "$log_date ���ǡ��� �쥳���ɤ�����ޤ���!\n";
}
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
