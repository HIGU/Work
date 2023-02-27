<?php
//////////////////////////////////////////////////////////////////////////////
// »�׷׻��� ���������̤Υǡ�������� ���å��� include(require) file   //
// � ���ê��ɽ�Υǡ�������� ���å��� include file                    //
// Copyright(C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp            //
// Changed history                                                          //
// 2007/10/10 Created   pl_segment_get.php                                  //
//////////////////////////////////////////////////////////////////////////////
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

//////////////// ǧ�ڥ����å�
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION["Auth"] <= 2) {
    $_SESSION['s_sysmsg'] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ����������󤫤餪�ꤤ���ޤ���";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

/////// pl_segmentYYYYMM.csv �ե����뤫�饻��������»�׷׻���ǡ����μ����
// $file_name = "/home/www/html/monthly/pl_segment" . $yyyymm . ".csv";    // �ɹ��ե�����SJIS
$file_name = "/home/guest/monthly/pl_segment" . $yyyymm . ".csv";    // �ɹ��ե�����SJIS
if (file_exists($file_name)) {            // �ե������¸�ߥ����å�
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number �����������˥�����ȥ��å�
    $row_up = 0;        // update record number   ��
    if ( ($fp = fopen($file_name, 'r')) ) {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ( !($con = db_connect()) ) {
            $_SESSION['s_sysmsg'] = "�ǡ����١�������³�Ǥ��ޤ���";
            return FALSE;
        } else {
            query_affected_trans($con, 'BEGIN');
            while ($data = fgetcsv($fp, 100, "\t")) {
                if ( ($num=count($data)) != 2) {        // CSV File �� field �������å�
                    $_SESSION['s_sysmsg'] = "CSV�ե������field����2�ĤǤʤ���:$num";
                    return FALSE;
                }
                $data[0] = mb_convert_encoding($data[0], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
                $data[1] = mb_convert_encoding($data[1], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
                // �������
                $data[0] = str_replace('J', '��',   $data[0]);
                $data[0] = str_replace('j', '���', $data[0]);
                $rowcsv++;
                ///////// ��Ͽ�ѤߤΥ����å�
                $query_chk = "SELECT note FROM act_pl_history WHERE pl_bs_ym={$yyyymm} AND note='{$data[0]}'";
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                    ///// ��Ͽ�ʤ� insert ����
                    $query = "INSERT INTO act_pl_history (pl_bs_ym, note, kin)
                            VALUES({$yyyymm}, '{$data[0]}', {$data[1]})
                    ";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        query_affected_trans($con, 'ROLLBACK');         // transaction rollback
                        $_SESSION['s_sysmsg'] = "���������� »�׷׻��� INSERT ERROR rec No.={$rowcsv}";
                        return FALSE;
                    } else {
                        $row_in++;      // insert ����
                    }
                } else {
                    ///// ��Ͽ���� update ����
                    $query = "
                        UPDATE act_pl_history SET pl_bs_ym={$yyyymm}, note='{$data[0]}', kin={$data[1]}
                            WHERE pl_bs_ym={$yyyymm} and note='{$data[0]}'
                    ";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        query_affected_trans($con, 'ROLLBACK');         // transaction rollback
                        $_SESSION['s_sysmsg'] = "���������� »�׷׻��� UPDATE ERROR rec No.={$rowcsv}";
                        return FALSE;
                    } else {
                        $row_up++;      // update ����
                    }
                }
            }
        }
        query_affected_trans($con, 'COMMIT');       // ����ߴ�λ
    } else {
        $_SESSION['s_sysmsg'] = "����ǯ��:{$yyyymm} �Υե����뤬�����ץ����ޤ���";
        return FALSE;
    }
    fclose($fp);
    unlink($file_name);     // ����ե�������� txt
} else {
    $_SESSION['s_sysmsg'] = "����ǯ��:{$yyyymm} �Υե����뤬����ޤ���";
    return FALSE;
}
$_SESSION['s_sysmsg'] = "<font color='yellow'>CSV file = $rowcsv ��<br>\\n\\nInsert file = $row_in ��<br>\\n\\nUpdate file = $row_up ��<br></font>\\n\\n";
return TRUE;    // ��ʸ����ʸ���˰�¸���ʤ��� JavaScript�˹�碌�롣

?>

