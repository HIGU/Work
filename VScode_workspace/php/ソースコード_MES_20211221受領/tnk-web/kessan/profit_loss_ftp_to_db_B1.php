<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� ��ưFTP Download  �����̡�������� *** ���� ***             //
// AS/400 ----> Web Server (PHP) TNKACT �� 77 �� 77 �� 31 �� 4              //
// 2003/01/31 Copyright(C) 2003-2004 K.Kobayashi tnksys@nitto-kohki.co.jp   //
// �ѹ�����                                                                 //
// 2003/01/31 ��������  profit_loss_ftp_to_db_B1.php                        //
//              �ǡ����� B ɽ��Ʊ�����˺��������                           //
//              select sum(kin) from bm_km_summary where actcod=8103        //
//              and act_id<>900 and k_kubun='1' and                         //
//              (act_id=173 or act_id=174 or act_id=500)                    //
// 2003/02/28 �ǡ����١����ؤ���Ͽ��ȥ�󥶥��������ѹ�                  //
// 2003/06/06 ����ޥ����������ѹ����줿���˴�����Ͽ����Ƥ��뤫          //
//            ��Ͽ����Ƥ��ʤ������쥳������˥����å�����褦���ѹ�        //
// 2004/02/05 �������Υ�å�����ʸ����������ѹ�  �����¸ʸ���έ� �� No �� //
//            ���ߤ� AS/400 �� WCBMKMP ���о�ǯ�������å�������ܤϤʤ�   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name �ϼ�ư����
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
    header("Location: http:" . WEB_HOST . "kessan/kessan_menu.php");
    exit();
}

    ///// �о�ǯ��μ���
$yyyymm = $_SESSION['pl_ym'];
    ///// ���μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);

    ///// AS/400 �� �饤�֥��ȥե�����̾����
$as_lib_file = "UKFLIB/WCBMKMP";
    ///// Dounload File Name ����
$file_orign = "WCBMKMP.TXT";
    ///// Dounload file ��������
$file_note  = "�������������B1";

    ///// �ե������¸�ߥ����å�
if (file_exists($file_orign)) {
    unlink($file_orign);    // ������ϵ�ե�����Τ����� FTP error ���˵�ե�����ǹ������ʤ�����
}
// ���ͥ���������(FTP��³�Υ����ץ�)
if ($ftp_stream = ftp_connect("10.1.1.252")) {
    if (ftp_login($ftp_stream,"FTPUSR","AS400FTP")) {
        if (ftp_get($ftp_stream, $file_orign, $as_lib_file, FTP_ASCII)) {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='white'>%d %s�� DOWNLOAD ����</font>", $yyyymm, $file_note);
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("%d %s�� DOWNLOAD ����<br>ftp_get_error", $yyyymm, $file_note);
        }
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("%d %s�� DOWNLOAD ����<br>ftp_login_error", $yyyymm, $file_note);
    }
    ftp_close($ftp_stream);
} else {
    $_SESSION['s_sysmsg'] .= sprintf("%d %s�� DOWNLOAD ����<br>ftp_connect_error", $yyyymm, $file_note);
}

///// �»�� �����̡������� ������� FTP �ǡ��������
if (file_exists($file_orign)) {           // �ե������¸�ߥ����å�
    $fp = fopen($file_orign,"r");
    $act_id   = array();    // ���祳����  3
    $actcod   = array();    // ���ܥ�����  4
    $k_kubun  = array();    // �����ʬ    1
    $div      = array();    // ������      1
    $kin      = array();    // ���       11
    $rec      = 0;          // �쥳����No
    while (!feof($fp)) {        // �ե������EOF�����å�
        $data = fgets($fp,100);   // �ºݤˤ�21(LF�ޤ�) ��OK����;͵����ä�
        $data = mb_convert_encoding($data, "EUC-JP", "auto");       // auto��EUC-JP���Ѵ�
        $act_id[$rec]  = substr($data,0,3);         // ���祳����
        $actcod[$rec]  = substr($data,3,4);         // ���ܥ�����
        $k_kubun[$rec] = substr($data,7,1);         // �����ʬ(��¤�����δ���) '1'=��¤���� ' '=�δ���
        $div[$rec]     = substr($data,8,1);         // ������ 'C'=���ץ� 'L'=��˥� ' '=������ '9'=��¤������
        $kin[$rec]     = substr($data,9,11);        // ���
        $rec++;
    }
    fclose($fp);
    $rec--;         // �Ǹ��LFʬ�� �쥳���ɺ��

    /////////// begin �ȥ�󥶥�����󳫻�
    if ($con = db_connect()) {
        query_affected_trans($con, "begin");
    } else {
        $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///////////////// �ǡ����١����ؤμ�����
    $ok_row = 0;        ///// �����ߴ�λ�쥳���ɿ�
    for ($i=0; $i < $rec; $i++) {       // ������Ͽ
        $query_chk = sprintf("select pl_bs_ym from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod=%d", $yyyymm, $act_id[$i], $actcod[$i]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = sprintf("insert into bm_km_summary (pl_bs_ym, ki, act_id, actcod, k_kubun, div, kin) 
                values(%d, %d, %d, %d, '%s', '%s', %d)",
                $yyyymm, $ki, $act_id[$i], $actcod[$i], $k_kubun[$i], $div[$i], $kin[$i]);
            if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
                $NG_row = ($i + 1);
                $_SESSION['s_sysmsg'] .= "<br>�ǡ����١����ο�����Ͽ�˼��Ԥ��ޤ��� No$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else {
                $ok_row++;
            }
        } else {                                // UPDATE
            ///// ��Ͽ���� update ����
            $query = sprintf("update bm_km_summary set pl_bs_ym=%d, ki=%d, act_id=%d, actcod=%d, k_kubun='%s', div='%s', kin=%d 
                where pl_bs_ym=%d and act_id=%d and actcod=%d", 
                $yyyymm, $ki, $act_id[$i], $actcod[$i], $k_kubun[$i], $div[$i], $kin[$i], 
                $yyyymm, $act_id[$i], $actcod[$i]);
            if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
                $NG_row = ($i + 1);
                $_SESSION['s_sysmsg'] .= "<br>�ǡ����١�����UPDATE�˼��Ԥ��ޤ��� No$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else {
                $ok_row++;
            }
        }
    }
    $_SESSION['s_sysmsg'] .= sprintf("<br><font color='white'>%d %s %d �� �����ߴ�λ</font>", $yyyymm, $file_note, $ok_row);
    /////////// commit �ȥ�󥶥������λ
    query_affected_trans($con, "commit");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>�»�� FTP Download </TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</HEAD>
<BODY>
    <center>AS/400 �� �ǡ������ ��λ</center>

    <script language="JavaScript">
    <!--
        location = 'http:<?php echo(WEB_HOST) . "kessan/profit_loss_select.php" ?>';
    // -->
    </script>
</BODY>
</HTML>
