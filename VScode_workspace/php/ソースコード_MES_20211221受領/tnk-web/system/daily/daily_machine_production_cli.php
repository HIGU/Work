#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// �����������ʻų�C��ɼ����(daily)����                                     //
// AS/400 UKWLIB/W#SETUBIC�������������ʻų�C��ɼ                           //
//   AS/400 ----> Web Server (PHP) PCIX��FTPž���Ѥ�ʪ�򹹿�����            //
// Copyright(C) 2018-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// �����������ʻų� C ȴ�Ф�      Q#SETUBIC   UKWLIB/W#SETUBIC              //
// �����������ʻų� C ȴ�Ф� ��� Q#SETUBICK  UKWLIB/W#SETUBICK             //
// \FTPTNK USER(AS400) ASFILE(W#SETUBICK) LIB(UKWLIB)                       //
//         PCFILE(W#SETUBICK.TXT) MODE(TXT)                                 //
// Changed history                                                          //
// 2018/08/22 �������� daily_machine_production_cli.php                     //
// 2018/10/25 ������� ������̤ʧ����ݤ��оݴ����ȵ�No.��ǯ��Ȥ˽���    //
//            machine_production_master�Ǵ�����ȵ�No.����                  //
//            machine_production_total�ǽ��׷�̤򻲾ȤǤ���                //
// 2018/10/29 �����ϴ�����Ʊ����ɼ������Τǡ����٤��٤ƺ����INSERT        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "�����������ʻų�C��ɼ�ι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_manufacture_cost_cli.php\n");

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date �����������ʻų�C��ɼ db_connect() error \n");
    fwrite($fpb, "$log_date �����������ʻų�C��ɼ db_connect() error \n");
    echo "$log_date �����������ʻų�C��ɼ db_connect() error \n\n";
    exit();
}
$data_ki = 0;
///////// �����������ʻų�C��ɼ�ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#SETUBIC.TXT';
$file_backup = '/home/guest/daily/backup/W#SETUBIC-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SETUBIC.TXT';
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    $del_fg = 0;    // ����ե饰
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 14) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ� auto��NG(��ư�Ǥϥ��󥳡��ǥ��󥰤�ǧ���Ǥ��ʤ�)
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            /////// EUC-JP �إ��󥳡��ǥ��󥰤����Ⱦ�ѥ��ʤ� ���饤����Ȥ�Windows��ʤ�����ʤ��Ȥ���
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            // }
        }
        $data_ki = $data[12];                                   // ��ۤɻ��Ѥ���ǡ����δ����ݴ�
        //$query_chk = sprintf("SELECT * FROM machine_production_denc WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_rin='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        $query_chk = sprintf("SELECT * FROM machine_production_denc WHERE den_ki=%d", $data[12]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $del_fg = 1;
            $query = "INSERT INTO machine_production_denc (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_rin, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����������ʻų�C��ɼ:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����������ʻų�C��ɼ:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
        } else {
            if ($del_fg == 0) {
                ///// ��Ͽ���� DELETE ����
                $query_del = sprintf("DELETE FROM machine_production_denc WHERE den_ki=%d", $data[12]);
                query_affected_trans($con, $query_del);
                $del_fg = 1;
            }
            ///// ��Ͽ���� DELETE�� INSERT
            $query = "INSERT INTO machine_production_denc (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_rin, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����������ʻų�C��ɼ:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����������ʻų�C��ɼ:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
            /*
            $query = "UPDATE machine_production_denc SET
                            den_ymd      = {$data[0]} ,
                            den_no       = {$data[1]} ,
                            den_eda      = {$data[2]} ,
                            den_gyo      = {$data[3]} ,
                            den_loan     ='{$data[4]}',
                            den_account  ='{$data[5]}',
                            den_break    ='{$data[6]}',
                            den_money    = {$data[7]} ,
                            den_summary1 ='{$data[8]}',
                            den_summary2 ='{$data[9]}',
                            den_id       ='{$data[10]}',
                            den_iymd     = {$data[11]} ,
                            den_ki       = {$data[12]} ,
                            den_rin    ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_rin='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����������ʻų�C��ɼ:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����������ʻų�C��ɼ:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $upd_ok++;
            }
            */
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date �����������ʻų�C��ɼ : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �����������ʻų�C��ɼ : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �����������ʻų�C��ɼ : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �����������ʻų�C��ɼ : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �����������ʻų�C��ɼ : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �����������ʻų�C��ɼ : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �����������ʻų�C��ɼ : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �����������ʻų�C��ɼ : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �����������ʻų�C��ɼ : {$upd_ok}/{$rec} �� �ѹ� \n";
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : �����������ʻų�C��ɼ�ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �����������ʻų�C��ɼ�ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �����������ʻų�C��ɼ�ι����ե����� {$file_orign} ������ޤ���\n";
    $data_ki = 0;
}
// ��۷׻�������դ�������ն�ۤ��ʤ���ΤΤ�
// UPDATE�����θ���ƻ��֤ϳݤ��뤬��$data_ki���оݴ��Υǡ������٤ƺƷ׻�
$query_chk = sprintf("SELECT * FROM machine_production_denc WHERE den_ki=%d", $data_ki);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// ����ն�ۤ��ʤ����ϲ��⤷�ʤ�
} else {
    ///// ���̵������ update ����
    for ($r=0; $r<$rows; $r++) {
        // �߼ڶ�ʬ[3]��1�λ����Τޤ� ����ʳ�����椬�դˤʤ�
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7];
        } else {
            $kin = $res[$r][7] * -1;
        }
        $query = "UPDATE machine_production_denc SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_rin='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

///////// �����������ʻų�C �����ɼ�ե�����ι��� �������
$file_orign  = '/home/guest/daily/W#SETUBICK.TXT';
$file_backup = '/home/guest/daily/backup/W#SETUBICK-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SETUBICK.TXT';
$data_ki = 0;
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug �ѥե�����Υ����ץ�
    $rec = 0;       // �쥳���ɭ�
    $rec_ok = 0;    // ����������쥳���ɿ�
    $rec_ng = 0;    // ����߼��ԥ쥳���ɿ�
    $ins_ok = 0;    // INSERT�ѥ����󥿡�
    $upd_ok = 0;    // UPDATE�ѥ����󥿡�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // �¥쥳���ɤ�183�Х��� �ǥ�ߥ��� '|' �������������'_'�ϻȤ��ʤ�
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 14) {           // �ե�����ɿ��Υ����å�
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ� auto��NG(��ư�Ǥϥ��󥳡��ǥ��󥰤�ǧ���Ǥ��ʤ�)
            $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            /////// EUC-JP �إ��󥳡��ǥ��󥰤����Ⱦ�ѥ��ʤ� ���饤����Ȥ�Windows��ʤ�����ʤ��Ȥ���
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            // }
        }
        $data_ki = $data[3];                                   // ��ۤɻ��Ѥ���ǡ����δ����ݴ�
        //$query_chk = sprintf("SELECT * FROM machine_production_kai_denc WHERE den_uke='%s'", $data[2]);
        $query_chk = sprintf("SELECT * FROM machine_production_kai_denc WHERE den_uke='%s' and den_type='%s' and den_symd=%d", $data[2], $data[1], $data[6]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            ///// ��Ͽ�ʤ� insert ����
            $query = "INSERT INTO machine_production_kai_denc (den_rin, den_type, den_uke, den_ki, den_uymd, den_kymd, den_symd, den_tori, den_tan, den_gnum, den_snum, den_toku, den_div, den_kamoku, den_kin)
                      VALUES(
                      '{$data[0]}',
                      '{$data[1]}',
                      '{$data[2]}',
                       {$data[3]} ,
                       {$data[4]} ,
                       {$data[5]} ,
                       {$data[6]} ,
                      '{$data[7]}',
                       {$data[8]} ,
                       {$data[9]} ,
                       {$data[10]} ,
                       {$data[11]} ,
                      '{$data[12]}',
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����������ʻų�C�����ɼ:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����������ʻų�C�����ɼ:{$data[0]} : {$rec}:�쥳�����ܤν���ߤ˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
        } else {
            ///// ��Ͽ���� update ����
            $query = "UPDATE machine_production_kai_denc SET
                            den_rin      ='{$data[0]}',
                            den_type     ='{$data[1]}',
                            den_uke      ='{$data[2]}',
                            den_ki       = {$data[3]} ,
                            den_uymd     = {$data[4]} ,
                            den_kymd     = {$data[5]} ,
                            den_symd     = {$data[6]} ,
                            den_tori     ='{$data[7]}',
                            den_tan      = {$data[8]} ,
                            den_gnum     = {$data[9]} ,
                            den_snum     = {$data[10]} ,
                            den_toku     = {$data[11]} ,
                            den_div      ='{$data[12]}',
                            den_kamoku   ='{$data[13]}'
                      where den_uke='{$data[2]}' and den_type='{$data[1]}' and den_symd={$data[6]}";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                fwrite($fpa, "$log_date �����������ʻų�C�����ɼ:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                fwrite($fpb, "$log_date �����������ʻų�C�����ɼ:{$data[0]} : {$rec}:�쥳�����ܤ�UPDATE�˼��Ԥ��ޤ���!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $upd_ok++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date �����������ʻų�C�����ɼ : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpa, "$log_date �����������ʻų�C�����ɼ : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpa, "$log_date �����������ʻų�C�����ɼ : {$upd_ok}/{$rec} �� �ѹ� \n");
    fwrite($fpb, "$log_date �����������ʻų�C�����ɼ : $rec_ok/$rec ����Ͽ���ޤ�����\n");
    fwrite($fpb, "$log_date �����������ʻų�C�����ɼ : {$ins_ok}/{$rec} �� �ɲ� \n");
    fwrite($fpb, "$log_date �����������ʻų�C�����ɼ : {$upd_ok}/{$rec} �� �ѹ� \n");
    echo "$log_date �����������ʻų�C�����ɼ : $rec_ok/$rec ����Ͽ���ޤ�����\n";
    echo "$log_date �����������ʻų�C�����ɼ : {$ins_ok}/{$rec} �� �ɲ� \n";
    echo "$log_date �����������ʻų�C�����ɼ : {$upd_ok}/{$rec} �� �ѹ� \n";
    if ($rec_ng == 0) {     // ����ߥ��顼���ʤ���� �ե��������
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup �ե�����κ��
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n");
            echo "$log_date DownLoad File $file_orign ��Backup�Ǥ��ޤ���\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : �����������ʻų�C�����ɼ�ι����ե����� {$file_orign} ������ޤ���\n");
    fwrite($fpb, "$log_date : �����������ʻų�C�����ɼ�ι����ե����� {$file_orign} ������ޤ���\n");
    echo "$log_date : �����������ʻų�C�����ɼ�ι����ե����� {$file_orign} ������ޤ���\n";
    $data_ki = 0;
}

if ($data_ki<>0) {
    // ��۷׻�������դ�������ն�ۤ��ʤ���ΤΤ�
    // UPDATE�����θ���ƻ��֤ϳݤ��뤬��$data_ki���оݴ��Υǡ������٤ƺƷ׻�
    $query_chk = sprintf("SELECT * FROM machine_production_kai_denc WHERE den_ki=%d", $data_ki);
    //$query_chk = sprintf("SELECT * FROM machine_production_kai_denc WHERE den_kin='0'");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// ����ն�ۤ��ʤ����ϲ��⤷�ʤ�
    } else {
        ///// ���̵������ update ����
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r][11] > 0) {
                $allo = $data[11] / 100;
                $kin  = round(($res[$r][8] * $res[$r][10] * $allo),0);
            } else {
                $kin = round(($res[$r][8] * $res[$r][10]),0);
            }
            $query = "UPDATE machine_production_kai_denc SET
                        den_kin = {$kin}
                        where den_uke='{$res[$r][2]}' and den_type='{$res[$r][1]}' and den_symd={$res[$r][6]}";
            query_affected_trans($con, $query);
        }
    }
    
    // ���׷׻�
    // ξ���Ȥ�Ʊ�����Ǥ�������ꤷ��PGM�߷�
    // �оݴ��δ���No.�����򹹿� ������̤ʧ����
    $query_chk = sprintf("SELECT DISTINCT den_rin ,den_ki FROM machine_production_denc as d WHERE d.den_ki=%d and NOT EXISTS(SELECT 1 FROM machine_production_master as m WHERE d.den_rin = m.kanri_no and m.total_ki=%d)", $data_ki, $data_ki);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// �������ɲä�ɬ�פ��ʤ��Τǲ��⤷�ʤ�
    } else {
        ///// �������ɲä�ɬ��ͭ�� insert ����
        for ($r=0; $r<$rows; $r++) {
            $query = "INSERT INTO machine_production_master (kanri_no, total_ki)
                          VALUES(
                          '{$res[$r][0]}',
                           {$data_ki})";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                break;                                  // debug
            }
        }
    }
    // Ʊ�ͤ������ɼ�ΰ����������No.�����򹹿�
    $query_chk = sprintf("SELECT DISTINCT den_rin ,den_ki FROM machine_production_kai_denc as d WHERE d.den_ki=%d and NOT EXISTS(SELECT 1 FROM machine_production_master as m WHERE d.den_rin = m.kanri_no and m.total_ki=%d)", $data_ki, $data_ki);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// �������ɲä�ɬ�פ��ʤ��Τǲ��⤷�ʤ�
    } else {
        ///// �������ɲä�ɬ��ͭ�� insert ����
        for ($r=0; $r<$rows; $r++) {
            $query = "INSERT INTO machine_production_master (kanri_no, total_ki)
                          VALUES(
                          '{$res[$r][0]}',
                           {$data_ki})";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                break;                                  // debug
            }
        }
    }
    // ������������No.������ꡢ��ݡ�������̤ʧ�Υǡ������Ʒ�ζ�ۤ򽸷�
    // ��ɼ����� ��� �� ��ݡ�������̤ʧ�ͻ��� ������ machine_production_total
    // �������
    // ���Ϸ�
    //$str_ym = ($data_ki + 1955) * 100 + 4;   // YYYY �� 100 �� YYYY00 �� ��4�� 
    $str_y   = $data_ki + 1955;
    $str_ym  = $str_y * 100 + 4;   // YYYY �� 100 �� YYYY00 �� ��4��
    $end_chk = $str_ym + 100 - 1;  // 201704 + 100 - 1 = 201803
    $end_ym  = date("Ym");
    if ($end_chk < $end_ym) {
        $end_ym = $str_ym + 100 - 1; // ����������Υǡ�����Ͽ���б��ΰ١�end����κǽ����
    }
    $end_mm  = substr($end_ym, 4,2);
    if ($end_mm < 4) {
        $num = $end_ym - $str_ym - 87;
    } else {
        $num = $end_ym - $str_ym + 1;
    }
    $total_ym = array();
    for ($r=0; $r<$num; $r++) {
        if ($r < 9) {
            $total_ym[$r] = $str_ym + $r;
        } else {
            $total_ym[$r] = $str_ym + $r + 88;
        }
    }
    $total_num = count($total_ym);
    
    // ������̤ʧ�ν���
    $den_name = '����';
    $kin = 0;
    $query_chk = sprintf("select kanri_no FROM machine_production_master WHERE total_ki=%d ORDER BY kanri_no", $data_ki);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// �������ʤ��Τǲ��⤷�ʤ�
    } else {
        ///// ���٤Ƥδ���No�Ƿ����֤�
        for ($r=0; $r<$rows; $r++) {
            ///// �Ʒ����ɼ�򽸷�
            for ($i=0; $i<$total_num; $i++) {
                $str_ymd = $total_ym[$i] * 100 + 1;
                $end_ymd = $total_ym[$i] * 100 + 99;
                $query_sum = sprintf("select SUM(den_kin) FROM machine_production_denc WHERE den_ymd>=%d and den_ymd<=%d and den_rin='%s'", $str_ymd, $end_ymd, $res[$r][0]);
                if (($rows_sum = getResultWithField3($query_sum, $field_sum, $res_sum)) <= 0) {
                    $kin = 0;
                } else {
                    if($res_sum[0][0] == "") {
                        $kin = 0;
                    } else {
                        $kin = $res_sum[0][0];
                    }
                }
                $query_chk = sprintf("select * FROM machine_production_total WHERE kanri_no='%s' and total_ki=%d and total_ym=%d and total_den='%s' ORDER BY kanri_no", $res[$r][0], $data_ki, $total_ym[$i], $den_name);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                    ///// ��Ͽ�ʤ� insert ����
                    $query = "INSERT INTO machine_production_total (kanri_no, total_ki, total_ym, total_kin, total_den)
                              VALUES(
                              '{$res[$r][0]}',
                               {$data_ki} ,
                               {$total_ym[$i]} ,
                               {$kin} ,
                              '{$den_name}')";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        break;                                  // debug
                    }
                } else {
                    ///// ��Ͽ���� update ����
                    $query = "UPDATE machine_production_total SET
                                    total_kin    = {$kin}
                              WHERE kanri_no='{$res[$r][0]}' and total_ki={$data_ki} and total_ym={$total_ym[$i]} and total_den='{$den_name}'";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        break;                                  // debug
                    }
                }
            }
        }
    }
    $den_name = '���';
    $kin = 0;
    $query_chk = sprintf("select kanri_no FROM machine_production_master WHERE total_ki=%d ORDER BY kanri_no", $data_ki);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// �������ʤ��Τǲ��⤷�ʤ�
    } else {
        ///// ���٤Ƥδ���No�Ƿ����֤�
        for ($r=0; $r<$rows; $r++) {
            ///// �Ʒ����ɼ�򽸷�
            for ($i=0; $i<$total_num; $i++) {
                $str_ymd = $total_ym[$i] * 100 + 1;
                $end_ymd = $total_ym[$i] * 100 + 99;
                $query_sum = sprintf("select SUM(den_kin) FROM machine_production_kai_denc WHERE den_kymd>=%d and den_kymd<=%d and den_rin='%s'", $str_ymd, $end_ymd, $res[$r][0]);
                if (($rows_sum = getResultWithField3($query_sum, $field_sum, $res_sum)) <= 0) {
                    $kin = 0;
                } else {
                    if($res_sum[0][0] == "") {
                        $kin = 0;
                    } else {
                        $kin = $res_sum[0][0];
                    }
                }
                $query_chk = sprintf("select * FROM machine_production_total WHERE kanri_no='%s' and total_ki=%d and total_ym=%d and total_den='%s' ORDER BY kanri_no", $res[$r][0], $data_ki, $total_ym[$i], $den_name);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                    ///// ��Ͽ�ʤ� insert ����
                    $query = "INSERT INTO machine_production_total (kanri_no, total_ki, total_ym, total_kin, total_den)
                              VALUES(
                              '{$res[$r][0]}',
                               {$data_ki} ,
                               {$total_ym[$i]} ,
                               {$kin} ,
                              '{$den_name}')";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        break;                                  // debug
                    }
                } else {
                    ///// ��Ͽ���� update ����
                    $query = "UPDATE machine_production_total SET
                                    total_kin    = {$kin}
                              WHERE kanri_no='{$res[$r][0]}' and total_ki={$data_ki} and total_ym={$total_ym[$i]} and total_den='{$den_name}'";
                    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                        break;                                  // debug
                    }
                }
            }
        }
    }
}
/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
