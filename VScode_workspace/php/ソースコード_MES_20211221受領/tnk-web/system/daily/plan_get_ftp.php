#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ����ǡ��� �����ײ�ե�����download ��ưFTP Download  cron �ǽ�����      //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright(C) 2003-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed histoy                                                           //
// 2003/05/30 Created   plan_get_ftp.php                                    //
// 2003/05/30 ��Ω�����ײ�ɽ�� DownLoad ���������(���ȥե��������)      //
//            ������ ���ͤ�桼����̾�� [']�����Ȥ��Ƥ����礬���� ���  //
// 2003/05/31 �嵭���������� addslashes() �ǲ�� ���ͣ�����ȼ�����̾       //
// 2003/06/06 AS/400��TIPPLNP���Υȥ�󥶥������ե�����ϥ���̵����ʪ��   //
//             �ե������Ƭ������֤��ɹ�������ʤ��Ȥ����ʤ���ʣ�쥳���ɤ� //
//             ���뤿��ǿ����ݤƤʤ���                                     //
// 2003/11/17 cgi �� cli�Ǥ��ѹ������褦�� requier_once �����л����      //
// 2004/04/19 php-4.3.4-cgi --> php-4.3.6-cgi ���ѹ�                        //
// 2004/06/07 php-4.3.6-cgi -q �� php-4.3.7-cgi -q  �С�����󥢥åפ�ȼ��  //
// 2004/11/18 php-5.0.2-cli�إС�����󥢥å� *�����륹����ץȤ��б����ѹ� //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli �� php (������5.0.3RC2)���ѹ�  //
// 2006/01/31 $plan_no �ν�����ɲ�                                         //
// 2007/01/29 ������̾��"'"������ addslashes() �� pg_escape_string()���б�  //
//            postgresql 8.2.X �ؤ� VerUP �ؤ��б����ͤƤ���              //
// 2007/02/05 echoʸ�򥳥��ȥ�����                                        //
// 2007/07/30 AS/400�Ȥ�FTP error �б��Τ��� ftpCheckAndExecute()�ؿ����ɲ� //
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
// 2009/12/28 �����Ȥ�����                                           ��ë //
// 2010/01/15 �᡼��˥�å�������̵���ä��١�echo���ɲ�               ��ë //
// 2010/01/19 �᡼���ʬ����䤹������١����ա�����������ɲ�       ��ë //
// 2010/01/20 $log_date�������'�Ǥ�̵��"�ʤΤǽ���                    ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI�Ǥ�ɬ�פʤ�
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');    ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "�����ײ�ǡ����ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/plan_get_ftp.php\n");
echo "/home/www/html/tnk-web/system/daily/plan_get_ftp.php\n";

// �������åȥե�����
define('MIPPLP', 'UKWLIB/W#MIPPLP');    // �����ײ�ե�����
// ��¸��Υǥ��쥯�ȥ�ȥե�����̾
define('W_MIPPLP', '/home/www/html/tnk-web/system/backup/W#MIPPLP.TXT');  // �����ײ�

// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftpCheckAndExecute($ftp_stream, W_MIPPLP, MIPPLP, FTP_ASCII)) {
            echo "$log_date �����ײ� ftp_get download OK ", MIPPLP, "��", W_MIPPLP, "\n";
            fwrite($fpa,"$log_date �����ײ� ftp_get download OK " . MIPPLP . '��' . W_MIPPLP . "\n");
            fwrite($fpb,"$log_date �����ײ� ftp_get download OK " . MIPPLP . '��' . W_MIPPLP . "\n");
        } else {
            echo "$log_date �����ײ� ftp_get() error", MIPPLP, "\n";
            fwrite($fpa,"$log_date �����ײ� ftp_get() error " . MIPPLP . "\n");
            fwrite($fpb,"$log_date �����ײ� ftp_get() error " . MIPPLP . "\n");
        }
    } else {
        echo "$log_date �����ײ� ftp_login() error \n";
        fwrite($fpa,"$log_date �����ײ� ftp_login() error \n");
        fwrite($fpb,"$log_date �����ײ� ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> �����ײ�\n";
    fwrite($fpa,"$log_date ftp_connect() error --> �����ײ�\n");
    fwrite($fpb,"$log_date ftp_connect() error --> �����ײ�\n");
}

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date �����ײ� db_connect() error \n";
    fwrite($fpa,"$log_date �����ײ� db_connect() error \n");
    fwrite($fpb,"$log_date �����ײ� db_connect() error \n");
    exit();
}
// ��Ω�����ײ� ������� �������
$file_orign  = W_MIPPLP;
// $file_backup = "W#MIPPLP-BAK.TXT";
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign,"r");
    // $fpw = fopen($file_test,"w");        // TEST �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $plan_no = '�ǡ����ʤ�';    // ����� 2006/01/31 ADD
    while (1) {
        $data = fgets($fp,150);       // �¥쥳���ɤ�140�Х��ȤʤΤǤ���ä�;͵��
        $data = mb_convert_encoding($data, "EUC-JP", "auto");       // auto��EUC-JP���Ѵ�
        // $data_KV = mb_convert_kana($data);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        // fwrite($fpw,$data_KV);
        if (feof($fp)) {
            break;
        }
        $plan_no   = substr($data,0,8);         // �ײ��ֹ�
        $parts_no  = substr($data,8,9);         // �����ֹ�
        $syuka     = substr($data,17,8);        // ������  
        $chaku     = substr($data,25,8);        // �����  
        $kanryou   = substr($data,33,8);        // ��λ��  
        $plan      = substr($data,41,8);        // �ײ��  
        $cut_plan  = substr($data,49,8);        // ���ڿ�  
        $kansei    = substr($data,57,8);        // ������  
        $nyuuko    = substr($data,65,2);        // ���˾��
        $sei_kubun = substr($data,67,1);        // ��¤��ʬ
        $line_no   = substr($data,68,4);        // �饤���
        //$note15    = addslashes(substr($data,72,15));     // ����15��
        $note15    = pg_escape_string(substr($data,72,15)); // ����15��
        $order_no  = substr($data,87,6);        // �����  
        // $user_name = addslashes(substr($data,93,15));    // ������̾
        $user_name = pg_escape_string(substr($data,93,15)); // ������̾
        $p_kubun   = substr($data,108,1);       // �ײ��ʬ
        $assy_site = substr($data,109,5);       // ��Ω���
        $dept      = substr($data,114,1);       // ������  
        $orign_kan = substr($data,115,8);       // ����λ��
        $priority  = substr($data,123,1);       // ͥ����
        $rep_date  = substr($data,124,8);       // ������  
        $crt_date  = substr($data,132,8);       // ������  
        
        $rec++;
        
        $query_chk = sprintf("select plan_no from assembly_schedule where plan_no='%s'", $plan_no);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = sprintf("insert into assembly_schedule values(
                '%s','%s',%d,%d,%d,%d,%d,%d,'%s','%s','%s','%s',%d,'%s','%s','%s','%s',%d,'%s',%d,%d)", 
                $plan_no,  
                $parts_no, 
                $syuka,    
                $chaku,    
                $kanryou,  
                $plan,     
                $cut_plan, 
                $kansei,   
                $nyuuko,   
                $sei_kubun,
                $line_no,  
                $note15,   
                $order_no, 
                $user_name,
                $p_kubun,  
                $assy_site,
                $dept,     
                $orign_kan,
                $priority, 
                $rep_date, 
                $crt_date);
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa,"$log_date �����ײ�:$plan_no : ".($rec).":�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb,"$log_date �����ײ�:$plan_no : ".($rec).":�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // echo ($rec) . ":�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n";
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        } else {
            ///// ��Ͽ���� update ����
            $query = "update assembly_schedule set parts_no='$parts_no', syuka=$syuka, chaku=$chaku,
                kanryou=$kanryou, plan=$plan, cut_plan=$cut_plan, kansei=$kansei, nyuuko='$nyuuko',
                sei_kubun='$sei_kubun', line_no='$line_no', note15='$note15', order_no=$order_no,
                user_name='$user_name', p_kubun='$p_kubun', assy_site='$assy_site', dept='$dept',
                orign_kan=$orign_kan, priority='$priority', rep_date=$rep_date, crt_date=$crt_date 
                where plan_no='$plan_no'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa,"$log_date �����ײ�:$plan_no : ".($rec).":�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb,"$log_date �����ײ�:$plan_no : ".($rec).":�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // echo ($rec) . ":�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        }
    }
    fclose($fp);
    // fclose($fpw);
    fwrite($fpa,"$log_date �����ײ�:$plan_no : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb,"$log_date �����ײ�:$plan_no : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    echo "$log_date �����ײ� : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    /*****
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        unlink($file_backup);       // Backup �ե�����κ��
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa,"$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            // echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
        }
    }
    *****/
} else {
    fwrite($fpa,"$log_date �ե�����$file_orign ������ޤ���!\n");
    fwrite($fpb,"$log_date �ե�����$file_orign ������ޤ���!\n");
    echo "$log_date �����ײ�ե�����$file_orign ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ

exit();

function ftpCheckAndExecute($stream, $local_file, $as400_file, $ftp)
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
?>
