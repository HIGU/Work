#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����Ǽ������ AS/400<-->TNK�����С�Ʊ�� ��ưFTP Download  cron �ǽ�����   //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright(C) 2007-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed histoy                                                           //
// 2007/04/26 Created   order_delivery_answer_get_ftp.php                   //
// 2007/05/10 ����å������ѹ� �ǡ���������ǽ�λ �� �ǡ���������Τǽ�λ //
// 2007/10/25 ftpGetCheckAndExecute(),ftpPutCheckAndExecute()���ɲ�         //
//            FTPž���Υ�ȥ饤����   E_ALL �� E_ALL | E_STRICT ���ѹ�      //
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
// 2010/01/19 �᡼���ʬ����䤹������١����ա�����������ɲ�       ��ë //
// 2010/01/20 $log_date�������'�ǤϤʤ�"�ʤΤǽ���                    ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�Ǥ�ɬ�פʤ�
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// ��������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// ���ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "����Ǽ�������ǡ�����Ʊ���¹�(��¾���椢��)\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php\n");
echo "/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php\n";

// ��¾�����ѥ���ȥ���ե�����
define('CHIDLIV', 'UKWLIB/C#HIDLIV');   // ����Ǽ����������ȥ���
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('C_HIDLIV', '/home/www/html/tnk-web/system/backup/C#HIDLIV.TXT');  // ����Ǽ����������ȥ���

// �������åȥե�����
define('HIDLIV', 'UKWLIB/W#HIDLIV');    // ����Ǽ�������ե�����
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('W_HIDLIV', '/home/www/html/tnk-web/system/backup/W#HIDLIV.TXT');  // ����Ǽ������
// AS/400�Υե��������ˤ��뤿��Υ��ߡ��ե�����̾
define('LOCAL_FILE', '/home/www/html/tnk-web/system/backup/W#HIDLIV-clear.TXT');

if (file_exists(W_HIDLIV)) {         // ������������ե������¸�ߥ����å�
    unlink(W_HIDLIV);
}

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftpGetCheckAndExecute($ftp_stream, C_HIDLIV, CHIDLIV, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');        ///// ��������
            echo "$log_date ftp_get ����ȥ��� download OK ", CHIDLIV, "��", C_HIDLIV, "\n";
            fwrite($fpa,"$log_date ftp_get ����ȥ��� download OK " . CHIDLIV . '��' . C_HIDLIV . "\n");
            fwrite($fpb,"$log_date ftp_get ����ȥ��� download OK " . CHIDLIV . '��' . C_HIDLIV . "\n");
            if (checkControlFile($fpa, $fpb, C_HIDLIV)) {
                echo "$log_date ����ȥ���ե�����˥ǡ���������Τǽ�λ���ޤ���\n";
                fwrite($fpa,"$log_date ����ȥ���ե�����˥ǡ���������Τǽ�λ���ޤ���\n");
                fwrite($fpb,"$log_date ����ȥ���ե�����˥ǡ���������Τǽ�λ���ޤ���\n");
                ftp_close($ftp_stream);
                fclose($fpa);      ////// ������λ
                fwrite($fpb, "------------------------------------------------------------------------\n");
                fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߶�����λ
                exit();
            }
        } else {
            $log_date = date('Y-m-d H:i:s');        ///// ��������
            echo "$log_date ftp_get() ����ȥ��� error ", CHIDLIV, "\n";
            fwrite($fpa,"$log_date ftp_get() ����ȥ��� error " . CHIDLIV . "\n");
            fwrite($fpb,"$log_date ftp_get() ����ȥ��� error " . CHIDLIV . "\n");
            ftp_close($ftp_stream);
            fclose($fpa);      ////// ������λ
            fwrite($fpb, "------------------------------------------------------------------------\n");
            fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߶�����λ
            exit();
        }
        if (ftpGetCheckAndExecute($ftp_stream, W_HIDLIV, HIDLIV, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');        ///// ��������
            echo "$log_date ����Ǽ������ ftp_get download OK ", HIDLIV, "��", W_HIDLIV, "\n";
            fwrite($fpa,"$log_date ����Ǽ������ ftp_get download OK " . HIDLIV . '��' . W_HIDLIV . "\n");
            fwrite($fpb,"$log_date ����Ǽ������ ftp_get download OK " . HIDLIV . '��' . W_HIDLIV . "\n");
        } else {
            $log_date = date('Y-m-d H:i:s');        ///// ��������
            echo "$log_date ����Ǽ������ ftp_get() error ", HIDLIV, "\n";
            fwrite($fpa,"$log_date ����Ǽ������ ftp_get() error " . HIDLIV . "\n");
            fwrite($fpb,"$log_date ����Ǽ������ ftp_get() error " . HIDLIV . "\n");
        }
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ��������
        echo "$log_date ����Ǽ������ ftp_login() error \n";
        fwrite($fpa,"$log_date ����Ǽ������ ftp_login() error \n");
        fwrite($fpb,"$log_date ����Ǽ������ ftp_login() error \n");
    }



/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
} else {
    $log_date = date('Y-m-d H:i:s');        ///// ��������
    echo "$log_date ����Ǽ������ db_connect() error \n";
    fwrite($fpa,"$log_date ����Ǽ������ db_connect() error \n");
    fwrite($fpb,"$log_date ����Ǽ������ db_connect() error \n");
    exit();
}
// Ʊ������ �������
if (file_exists(W_HIDLIV)) {         // �ե������¸�ߥ����å�
    $fp = fopen(W_HIDLIV, 'r');
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    while (1) {
        $data = fgetcsv($fp, 50, '_');     // �¥쥳���ɤ�31�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num < 4) continue;     // �ե�����ɿ��Υ����å�
        if (!$data[0]) continue;    // �ײ襪������(��¤�ֹ�)��0�Τ�Τ�����Τǥ����å�
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
        }
        
        $query_chk = "
            SELECT * FROM order_delivery_answer
            WHERE sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "
                INSERT INTO order_delivery_answer (sei_no, order_no, vendor, delivery)
                VALUES({$data[0]}, {$data[1]}, '{$data[2]}', {$data[3]})
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');        ///// ��������
                fwrite($fpa,"$log_date ����Ǽ������ : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb,"$log_date ����Ǽ������ : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, 'ROLLBACK');     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        } else {
            ///// ��Ͽ���� update ����
            $query = "
                UPDATE order_delivery_answer SET sei_no={$data[0]}, order_no={$data[1]}, vendor='{$data[2]}',
                    delivery={$data[3]}
                WHERE sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');        ///// ��������
                fwrite($fpa,"$log_date ����Ǽ������ : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb,"$log_date ����Ǽ������ : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, 'ROLLBACK');     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        }
    }
    fclose($fp);
    $log_date = date('Y-m-d H:i:s');        ///// ��������
    echo "$log_date ����Ǽ������ : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    fwrite($fpa, "$log_date ����Ǽ������ : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date ����Ǽ������ : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    
    ////////// AS/400�Υե��������ˤ���
    if (ftpPutCheckAndExecute($ftp_stream, HIDLIV, LOCAL_FILE, FTP_ASCII)) {
        $log_date = date('Y-m-d H:i:s');        ///// ��������
        echo "$log_date ����Ǽ������ : AS/400�Υե��������ˤ��ޤ�����\n";
        fwrite($fpa, "$log_date ����Ǽ������ : AS/400�Υե��������ˤ��ޤ�����\n");
        fwrite($fpb, "$log_date ����Ǽ������ : AS/400�Υե��������ˤ��ޤ�����\n");
        /////////// commit �ȥ�󥶥������λ
        query_affected_trans($con, 'COMMIT');
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ��������
        echo "$log_date ����Ǽ������ : AS/400�Υե��������˽���ޤ���Ǥ�����\n";
        fwrite($fpa, "$log_date ����Ǽ������ : AS/400�Υե��������˽���ޤ���Ǥ�����\n");
        fwrite($fpb, "$log_date ����Ǽ������ : AS/400�Υե��������˽���ޤ���Ǥ�����\n");
        query_affected_trans($con, 'ROLLBACK');     // transaction rollback
    }
} else {
    echo '$log_date �ե����� ', W_HIDLIV, " ������ޤ���!\n";
    fwrite($fpa, "$log_date �ե����� " . W_HIDLIV . " ������ޤ���!\n");
    fwrite($fpb, "$log_date �ե����� " . W_HIDLIV . " ������ޤ���!\n");
}



    ftp_close($ftp_stream);
} else {
    $log_date = date('Y-m-d H:i:s');        ///// ��������
    echo "$log_date ftp_connect() error --> ����Ǽ������\n";
    fwrite($fpa,"$log_date ftp_connect() error --> ����Ǽ������\n");
    fwrite($fpb,"$log_date ftp_connect() error --> ����Ǽ������\n");
}

fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ



function checkControlFile($fpa, $fpb, $ctl_file)
{
    $fp = fopen($ctl_file, 'r');
    $data = fgets($fp, 20);     // �¥쥳���ɤ�11�Х��ȤʤΤǤ���ä�;͵��
    if (feof($fp)) {
        return false;
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ��������
        fwrite($fpa, "$log_date ����Ǽ������ : ����ü���� {$data}");
        fwrite($fpb, "$log_date ����Ǽ������ : ����ü���� {$data}");
        return true;
    }
}

function ftpGetCheckAndExecute($stream, $local_file, $as400_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}

function ftpPutCheckAndExecute($stream, $as400_file, $local_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}
?>
