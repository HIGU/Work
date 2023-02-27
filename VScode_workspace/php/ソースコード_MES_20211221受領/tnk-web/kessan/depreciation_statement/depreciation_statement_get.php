<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ⱦ���������������٤Υǡ�������� ���å��� include file               //
// Copyright(C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2020/01/24 Created  depreciation_statement_get.php                       //
//////////////////////////////////////////////////////////////////////////////
/***** include file �Τ���ʲ��򥳥��ȥ����� *****/
// ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI CGI��
// ob_start("ob_gzhandler");               // ���ϥХåե���gzip����
// session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
// access_log('invent_comp_get.php');      // Script Name ���ư����
// $_SESSION["site_index"] = 99;        // �Ǹ�Υ�˥塼�ˤ��뤿�� 99 �����
// $_SESSION["site_id"] = 10;           // ���̥�˥塼̵�� (0 < �Ǥ���)

//////////////// ǧ�ڥ����å�
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION["Auth"] <= 2) {
    $_SESSION['s_sysmsg'] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ����������󤫤餪�ꤤ���ޤ���";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

/////// koteiYYYYMM.csv �ե����뤫�����ê���ǡ����μ����
$file_name = "/home/guest/monthly/kotei" . $yyyymm . ".csv";    // �ɹ��ե�����SJIS
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number �����������˥�����ȥ��å�
    $row_up = 0;        // update record number   ��
    if ( ($fp = fopen($file_name, "r")) ) {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ( !($con = db_connect()) ) {
            $_SESSION['s_sysmsg'] = "�ǡ����١�������³�Ǥ��ޤ���";
            return FALSE;
        } else {
            query_affected_trans($con, "begin");
            while ($data = fgetcsv($fp, 100, "\t")) {
                if ( ($num=count($data)) != 2) {        // CSV File �� field �������å�
                    $_SESSION['s_sysmsg'] = "CSV�ե������field����2�ĤǤʤ���:$num";
                    return FALSE;
                }
                $data[0] = mb_convert_encoding($data[0], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
                $data[1] = mb_convert_encoding($data[1], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
                // �������
                $data[0] = str_replace('Y', '��',   $data[0]);
                $data[0] = str_replace('{', '��', $data[0]);
                $rowcsv++;
                ///////// ��Ͽ�ѤߤΥ����å�
                $query_chk = sprintf("select item from act_state_depreciation_history where state_ym=%d and item='%s'", $yyyymm, $data[0]);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                    ///// ��Ͽ�ʤ� insert ����
                    $query = "insert into act_state_depreciation_history (state_ym, item, kin)
                            values({$yyyymm}, '{$data[0]}', {$data[1]})
                    ";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        query_affected_trans($con, "rollback");         // transaction rollback
                        $_SESSION['s_sysmsg'] = "�������������� insert error rec No.=$rowcsv";
                        return FALSE;
                    } else {
                        $row_in++;      // insert ����
                    }
                } else {
                    ///// ��Ͽ���� update ����
                    $query = sprintf("update act_state_depreciation_history set state_ym=%d, item='%s', kin=%d
                            where state_ym=%d and item='%s'", $yyyymm, $data[0], $data[1], $yyyymm, $data[0]);
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        query_affected_trans($con, "rollback");         // transaction rollback
                        $_SESSION['s_sysmsg'] = "�������������� update error rec No.=$rowcsv";
                        return FALSE;
                    } else {
                        $row_up++;      // update ����
                    }
                }
            }
        }
        query_affected_trans($con, "commit");       // ����ߴ�λ
    } else {
        $_SESSION['s_sysmsg'] = "����ǯ��:$yyyymm �Υե����뤬�����ץ����ޤ���";
        return FALSE;
    }
    fclose($fp);
    unlink($file_name);     // ����ե�������� txt
} else {
    $_SESSION['s_sysmsg'] = "����ǯ��:$yyyymm �Υե����뤬����ޤ���";
    return FALSE;
}
$_SESSION['s_sysmsg'] = "<font color='yellow'>CSV file = $rowcsv ��<br>\\n\\nInsert file = $row_in ��<br>\\n\\nUpdate file = $row_up ��<br></font>\\n\\n";
return TRUE;    // ��ʸ����ʸ���˰�¸���ʤ��� JavaScript�˹�碌�롣

?>

