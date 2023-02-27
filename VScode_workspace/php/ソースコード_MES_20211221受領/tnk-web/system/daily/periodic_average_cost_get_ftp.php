#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ��ʿ��ñ������ ��ưFTP Download cron �ǽ�����       ���ޥ�ɥ饤����     //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2010 - 2013 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed histoy                                                           //
// 2010/02/23 Created  periodic_average_cost_get_ftp.php                    //
// 2012/08/03 ��Ⱦ������6��12��ˤˤ��ޤ�������ʤ��ä��Τ���           //
// 2013/06/05 ���ʬ���˺��ΰ١�ǯ���ľ�ܤ��������ᤷ�Ѥ�                //
// 2013/10/21 ���ʬ���˺��ΰ١�ǯ���ľ�ܤ��������ᤷ�Ѥ�                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
    // ���ߤ�CLI�Ǥ�default='1', SAPI�Ǥ�default='0'�ˤʤäƤ��롣CLI�ǤΤߥ�����ץȤ����ѹ�����롣
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// �����ѥ�������
$temp_ym  = date('Ym');                 ///// ����ʿ����Ͽ�Ѥ�����
///// �о�ǯ��(�¹�ǯ������η�о�ǯ���
if (substr($temp_ym,4,2)!=01) {
    $yyyymm = $temp_ym - 1;
} else {
    $yyyymm = $temp_ym - 100;
    $yyyymm = $yyyymm + 11;
}
//// �оݷ�μ���
$mm = substr($yyyymm,4,2);
/////// ��������� �ѿ� �����\
$flag2 = '';        // �����¹ԥե饰 �����ƥ�
// ��ʿ��ñ�� ��Ͽ���� �������
$file_orign  = '/home/guest/monthly/W#SGAVE@L.TXT';

///// ����Υǡ�������
//if (file_exists($file_orign)) {
//    unlink($file_orign);
//}
// ��ʿ��ñ�� ��Ͽ����
if(file_exists($file_orign)){           // �ե������¸�ߥ����å�
    $row_in = 0;        // insert record number �����������˥�����ȥ��å�
    $row_up = 0;        // update record number   ��
    $rec_ok = 0;        // �������������
    $fp = fopen($file_orign,"r");
    $parts_no      = array();  // ����No.   ����ե��٥å� 9
    $period_ym     = array();  // ��ʿ��ǯ��               6
    $average_cost  = array();  // ��ʿ��ñ��               10
    $mate_cost     = array();  // ��������ʿ��             10
    $out_cost      = array();  // ������ʿ��               10
    $manu_cost     = array();  // ������ʿ��               10
    $assem_cost    = array();  // ��Ω��ʿ��               10
    $other_cost    = array();  // ����¾��ʿ��             10
    $indirect_cost = array();  // ��������ʿ��             10
    $rec    = 0;        // �쥳����No
    while (!feof($fp)) {            // �ե������EOF�����å�
        $data = fgets($fp,200);     // �ºݤˤ�120 ��OK����;͵����ä�
        $data = mb_convert_encoding($data, "EUC-JP", "auto");       // auto��EUC-JP���Ѵ�
        $parts_no[$rec]      = substr($data,0,9);          // ����No.
        $period_ym[$rec]     = substr($data,10,6);         // ��ʿ��ǯ��
        $average_cost[$rec]  = substr($data,16,11);        // ��ʿ��ñ��
        $mate_cost[$rec]     = substr($data,27,11);        // ��������ʿ��
        $out_cost[$rec]      = substr($data,38,11);        // ������ʿ��
        $manu_cost[$rec]     = substr($data,49,11);        // ������ʿ��
        $assem_cost[$rec]    = substr($data,60,11);        // ��Ω��ʿ��
        $other_cost[$rec]    = substr($data,71,11);        // ����¾��ʿ��
        $indirect_cost[$rec] = substr($data,82,11);        // ��������ʿ��
        $rec++;
    }
    $rec--;             // �쥳���ɿ���Ĵ�� �Ǹ�Υ쥳���ɤβ��Ԥǥ�����Ȥ������ä��뤿��
    fclose($fp);
    
    /////////// begin �ȥ�󥶥�����󳫻�
    if ($con = db_connect()) {
        query_affected_trans($con, "begin");
    } else {
        echo "�ǡ����١�������³�Ǥ��ޤ���";
        header("Location: http:" . WEB_HOST . "system/system_menu.php");
        exit();
    }
    ///// �ǡ����١����ؤμ�����
    //echo "$parts_no[0]/$period_ym[0]/$average_cost[0]/$mate_cost[0]/$out_cost[0]/$manu_cost[0]/$assem_cost[0]/$other_cost[0]/$indirect_cost[0]/";
    //echo "$log_date/$yyyymm/$temp_ym/";
    $ok_row  = 0;       ///// �����ߴ�λ�쥳���ɿ�
    $res_chk = array();
    for ($i=0; $i < $rec; $i++) {
        //if ($mm !=3 || $mm !=6 || $mm !=9 || $mm !=12) {
            $period_ym[$i] = $yyyymm;   // �軻���ʤ������ʿ��ǯ��϶�������
            //$period_ym[$i] = 201308;   // ľ��������
        //}
        $query_chk = sprintf("select parts_no from periodic_average_cost_history2 where parts_no='%s' and period_ym=%d", $parts_no[$i], $period_ym[$i]);
        if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
                                // ������Ͽ
            $query = sprintf("insert into periodic_average_cost_history2 (parts_no, period_ym, average_cost, mate_cost, out_cost, manu_cost, assem_cost, other_cost, indirect_cost) 
                values('%s',%d,%f,%f,%f,%f,%f,%f,%f)",
                $parts_no[$i], $period_ym[$i], $average_cost[$i], $mate_cost[$i], $out_cost[$i], 
                $manu_cost[$i], $assem_cost[$i], $other_cost[$i], $indirect_cost[$i]);
            if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
                //$NG_row = ($i + 1);
                //echo "�ǡ����١����ο�����Ͽ�˼��Ԥ��ޤ��� No$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                ////header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else {
                $row_in++;      // insert ����
                $rec_ok++;      // �������������
            }
        } else {                // UPDATE
            $query = "UPDATE periodic_average_cost_history2 SET
                            average_cost  = {$average_cost[$i]},
                            mate_cost     = {$mate_cost[$i]},
                            out_cost      = {$out_cost[$i]},
                            manu_cost     = {$manu_cost[$i]},
                            assem_cost    = {$assem_cost[$i]},
                            other_cost    = {$other_cost[$i]},
                            indirect_cost = {$indirect_cost[$i]}
                WHERE parts_no='{$parts_no[$i]}' and period_ym={$period_ym[$i]}";
            if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
                //$NG_row = ($i + 1);
                //echo "�ǡ����١�����UPDATE�˼��Ԥ��ޤ��� No$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                ////header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else {
                $row_up++;      // update ����
                $rec_ok++;      // �������������
            }
        }
    }
    $flag2 = 1;
    /////////// commit �ȥ�󥶥������λ
    query_affected_trans($con, "commit");
}
// ��å��������֤�
if ($flag2==1) {
    echo "$log_date ��ʿ�ѥǡ����ι���: $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date ��ʿ�ѥǡ����ι���: {$row_in}/{$rec} �� �ɲ� \n";
    echo "$log_date ��ʿ�ѥǡ����ι���: {$row_up}/{$rec} �� �ѹ� \n";
} else {
    echo "{$log_date} ���ʥ��롼�ץ����ɤι����ǡ���������ޤ���\n";
}
?>
