<?php
//////////////////////////////////////////////////////////////////////////////
// ������Pro�λ���(�жС����)DAYLY_MANU.TXT��ǡ����١����ؼ�ư����CLI��   //
// Copyright (C) 2008      Norihisa.Ohya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2008/09/18 Created  timePro_update_cli.php(timePro_update_cli.php)       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 60);          // ����¹Ի��� = 60�� 
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

$currentFullPathName = realpath(dirname(__FILE__));
///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(3);                  // ǧ�ڥ����å�3=administrator�ʾ� �����=TOP_MENU �����ȥ�̤����

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('������ץ�DB�����С� ������ץ�ǡ��� ���������¹�');
//////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
$menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�

$log_date = date('Y-m-d H:i:s');        // �����ѥ�������
$fpa = fopen('/tmp/timepro_manu.log', 'a');  // �����ѥ��ե�����ؤν���ߤǥ����ץ�

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    // query_affected_trans($con, 'BEGIN');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
$file_orign  = '/home/guest/timepro/DAYLY_MANU.TXT';
$file_debug  = "{$currentFullPathName}/debug/debug-DAYLY-MANU.TXT";
$file_backup  = "{$currentFullPathName}/backup/backup-DAYLY-MANU.TXT";
///// �����ե�����Υ����ॹ����פ����
$save_file_time = "{$currentFullPathName}/timestamp_manu.txt";
if (file_exists($save_file_time)) {
    $fpt  = fopen($save_file_time, 'r');
    $timestamp = fgets($fpt, 50);
    fclose($fpt);
} else {
    $timestamp = '';
}
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $now = date('Ymd His', filemtime($file_orign));
    if ($now == $timestamp) {
        $log_date = date('Y-m-d H:i:s');
        $Message = "$log_date DAYLY_MANU.TXT���ѹ�����Ƥ��ʤ������������ߤ��ޤ���\n";
        exit();
    } else {
        $fpt  = fopen($save_file_time, 'w');
        fwrite($fpt, $now);
        fclose($fpt);
    }
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug �ѥե�����Υ����ץ�
    $fpb = fopen($file_backup, 'w');     // backup �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $no_upd = 0;    // ̤�ѹ��ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgets($fp, 300);     // �¥쥳���ɤ�255�Х��ȤʤΤǤ���ä�;͵��
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $data = trim($data);       // 179��255�Υ��ڡ�������
        ///// �Хå����åפؽ����
        fwrite($fpb, "{$data}\n");
        if ($data == '') {
            $log_date = date('Y-m-d H:i:s');
            $Message = "$log_date ���ԤʤΤ����Ф��ޤ���\n";
            continue;
        }
        ////////// �ǡ�����¸�ߥ����å�
        $query = "
            SELECT * FROM timepro_daily_data WHERE timepro_index(timepro) = timepro_index('{$data}')
        ";
        if (getUniResult($query, $res_chk) > 0) {
            if ($res_chk === $data) {   // ===�����(�����碌�Ƥ���)
                ///// �ǡ������ѹ���̵�� �ʤˤ⤷�ʤ�
                $no_upd++;
            } else {
                ///// �ѹ����� update ����
                $query = "
                    UPDATE timepro_daily_data SET timepro = '{$data}' WHERE timepro_index(timepro) = timepro_index('{$data}')
                ";
                if (query_affected($query) <= 0) {      // �����ѥ����꡼�μ¹�
                    $log_date = date('Y-m-d H:i:s');
                    $Message = "$log_date {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n";
                    $rec_ng++;
                    ////////////////////////////////////////// Debug start
                    fwrite($fpw, "$query \n");              // debug
                    break;                                  // debug
                    ////////////////////////////////////////// Debug end
                } else {
                    $rec_ok++;
                    $upd_ok++;
                }
            }
        } else {    //////// ������Ͽ
            $query = "
                INSERT INTO timepro_daily_data VALUES ('{$data}')
            ";
            if (query_affected($query) <= 0) {
                $log_date = date('Y-m-d H:i:s');
                $Message = "$log_date {$rec}:�쥳�����ܤ�INSERT�˼��Ԥ��ޤ���!\n";
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fclose($fpb);       // backup
    $log_date = date('Y-m-d H:i:s');
    $Message = "$log_date TimePro�ǡ������� : {$rec_ok}/{$rec} ����Ͽ���ޤ�����\n";
    $Message .= "$log_date TimePro�ǡ������� : {$ins_ok}/{$rec} �� �ɲ� \n";
    $Message .= "$log_date TimePro�ǡ������� : {$upd_ok}/{$rec} �� �ѹ� \n";
    $Message .= "$log_date TimePro�ǡ������� : {$no_upd}/{$rec} �� ̤�ѹ� \n";
} else {
    $log_date = date('Y-m-d H:i:s');
    $Message = "$log_date �ե�����$file_orign ������ޤ���!\n";
}
/////////// commit �ȥ�󥶥������λ
// query_affected_trans($con, 'COMMIT');
// echo $query . "\n";  // debug

///// alert()�����Ѥ˥�å��������Ѵ�
$Message = str_replace("\n", '\\n', $Message);  // "\n"�����

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<script type='text/javascript'>
function resultMessage()
{
    
    location.replace("<?php echo SYS_MENU ?>");
    alert("<?php echo $Message ?>");
    
}
</script>
<body   onLoad='
            resultMessage();
        '
</body>
<html>
