<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط��Υǡ��� ��ưFTP Download  �������������Υǡ���            //
// AS/400 ----> Web Server (PHP) TNKACT �� 77 �� 77 �� 31 �� 4              //
// 2003/01/17 Copyright(C) 2003-2004 K.Kobayashi tnksys@nitto-kohki.co.jp   //
// �ѹ�����                                                                 //
// 2003/01/17 ��������  profit_loss_ftp_to_db_B.php                         //
// 2003/01/24 �ǡ����١����ؤμ����ߥ��å����ɲ�                        //
// 2003/01/27 �ǡ����١����ؤμ����ߤ�ʬ�����뤿��ե�����̾�ѹ�        //
//            �ã̷������ɽ�ѤΥǡ��������� ɽID=B                       //
// 2003/01/28 �ǡ����١����Υե�������ɲ� �оݴ�(ki=3�ʤ�)                 //
// 2003/02/28 �ǡ����١����ؤ���Ͽ��ȥ�󥶥��������ѹ�                  //
// 2004/02/05 AS/400 ���о�ǯ��Υ����å���ǽ�ɲ� kin9 != $yyyymm           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name �ϼ�ư����
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���!<br>�����Ԥ�Ϣ���Ʋ�����!";
    header("Location: http:" . WEB_HOST . "kessan/kessan_menu.php");
    exit();
}

    ///// �о�ǯ��μ���
$yyyymm = $_SESSION['pl_ym'];
    ///// ���μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);

    ///// AS/400 �� �饤�֥��ȥե�����̾����
$as_lib_file = "UKFLIB/WCPLBSP";
    ///// Dounload File Name ����
$file_orign = "WCPLBSP.TXT";
    ///// Dounload file ��������
$file_note  = "������������� B";

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

///// �»�ץǡ��� ������� FTP �ǡ��������
if(file_exists($file_orign)){           // �ե������¸�ߥ����å�
    $fp = fopen($file_orign,"r");
    $t_id     = array();   // ɽID   ����ե��٥å� 1
    $t_row    = array();   // �ԭ�                  2
    $actcod = array();   // ���ܥ�����            4
    $wplkn1 = array();   // ���1                11
    $wplkn2 = array();   // ���2                11
    $wplkn3 = array();   // ���3                11
    $wplkn4 = array();   // ���4                11
    $wplkn5 = array();   // ���5                11
    $wplkn6 = array();   // ���6                11
    $wplkn7 = array();   // ���7                11
    $wplkn8 = array();   // ���8                11
    $wplkn9 = array();   // ���9                11
    $rec = 0;       // �쥳���ɭ�
    while(!feof($fp)){          // �ե������EOF�����å�
        $data=fgets($fp,200);   // �ºݤˤ�120 ��OK����;͵����ä�
        $data = mb_convert_encoding($data, "EUC-JP", "auto");       // auto��EUC-JP���Ѵ�
        $t_id[$rec]     = substr($data,0,1);        // ɽID
        if ($t_id[$rec] != 'B')     // �ã̷���ǡ����Ǥʤ���к��ɹ�
            continue;
        $t_row[$rec]  = substr($data,1,2);          // �ԭ�
        $actcod[$rec] = substr($data,3,4);          // ���ܥ�����
        $wplkn1[$rec] = substr($data,7,11)  ;       // ���1
        $wplkn2[$rec] = substr($data,18,11) ;       // ���2
        $wplkn3[$rec] = substr($data,29,11) ;       // ���3
        $wplkn4[$rec] = substr($data,40,11) ;       // ���4
        $wplkn5[$rec] = substr($data,51,11) ;       // ���5
        $wplkn6[$rec] = substr($data,62,11) ;       // ���6
        $wplkn7[$rec] = substr($data,73,11) ;       // ���7
        $wplkn8[$rec] = substr($data,84,11) ;       // ���8
        $wplkn9[$rec] = substr($data,95,11) ;       // ���9
        $rec++;
    }
    fclose($fp);
    //////////// �о�ǯ��Υ����å�
    if ($wplkn9[0] != $yyyymm) {
        $_SESSION['s_sysmsg'] .= "AS/400��ǯ��㤤�ޤ�<br>{$t_id[0]}{$t_row[0]}��{$wplkn9[0]}";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    
    /////////// begin �ȥ�󥶥�����󳫻�
    if ($con = db_connect()) {
        query_affected_trans($con, "begin");
    } else {
        $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// �ǡ����١����ؤμ�����
    $ok_row = 0;        ///// �����ߴ�λ�쥳���ɿ�
    $res_chk = array();
    $query_chk = sprintf("select pl_bs_ym from pl_bs_summary where pl_bs_ym=%d and t_id='B'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      ///// ����Ͽ�ѤߤΥ����å�
        for($i=0;$i<$rec;$i++){                     ///// ������Ͽ
            $query = sprintf("insert into pl_bs_summary (pl_bs_ym,ki,t_id,t_row,actcod,kin1,kin2,kin3,kin4,kin5,kin6,kin7,kin8,kin9) 
                values(%d,%d,'%s',%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d)",
                $yyyymm, $ki, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i]);
            if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
                $NG_row = ($i + 1);
                $_SESSION['s_sysmsg'] .= "<br>�ǡ����١����ο�����Ͽ�˼��Ԥ��ޤ��� ��$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else
                $ok_row++;
        }
        /******** debug start
        $i = 85;
            $query = sprintf("insert into pl_bs_summary (pl_bs_ym,t_id,t_row,actcod,kin1,kin2,kin3,kin4,kin5,kin6,kin7,kin8,kin9) 
                values(%d,'%s',%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d)",
                $yyyymm, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i]);
        $_SESSION['s_sysmsg'] .= $query;
        *********//// debug end
    } else {                  // UPDATE
        for($i=0;$i<$rec;$i++){
            $query = sprintf("update pl_bs_summary set pl_bs_ym=%d, ki=%d, t_id='%s', t_row=%d, actcod=%d, 
                kin1=%d, kin2=%d, kin3=%d, kin4=%d, kin5=%d, kin6=%d, kin7=%d, kin8=%d, kin9=%d 
                where pl_bs_ym=%d and t_id='%s' and t_row=%d", 
                $yyyymm, $ki, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i], 
                $yyyymm, $t_id[$i], $t_row[$i]);
            if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
                $NG_row = ($i + 1);
                $_SESSION['s_sysmsg'] .= "<br>�ǡ����١�����UPDATE�˼��Ԥ��ޤ��� ��$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else 
                $ok_row++;
        }
        /******* debug start
        $i = 1;
            $query = sprintf("update pl_bs_summary set pl_bs_ym=%d, t_id='%s', t_row=%d, actcod=%d, 
                kin1=%d, kin2=%d, kin3=%d, kin4=%d, kin5=%d, kin6=%d, kin7=%d, kin8=%d, kin9=%d 
                where pl_bs_ym=%d and t_id='%s' and t_row=%d", 
                $yyyymm, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i], 
                $yyyymm, $t_id[$i], $t_row[$i]);
        $_SESSION['s_sysmsg'] .= $query;
        ********////// debug end
    }
    $_SESSION['s_sysmsg'] .= sprintf("<br>%d %s %d �� �����ߴ�λ", $yyyymm, $file_note, $ok_row);
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
