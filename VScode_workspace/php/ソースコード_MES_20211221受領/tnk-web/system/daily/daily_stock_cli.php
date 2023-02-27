#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// ���ʺ߸˶�� ����(daily)����                                             //
// Copyright(C) 2016-2016 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2016/09/16 �������� daily_stock_cli.php                                  //
// 2016/09/20 ���ץ�����ɸ����ɲ�                                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��cli��)
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// �����ѥ�������
$fpa = fopen('/tmp/nippo.log', 'a');        ///// �����ѥ��ե�����ؤν���ߤǥ����ץ�
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpb, "�������ʺ߸˶�ۤι���\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_stock_cli.php\n");

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date �������ʺ߸˶�ۤι��� db_connect() error \n");
    fwrite($fpb, "$log_date �������ʺ߸˶�ۤι��� db_connect() error \n");
    echo "$log_date �������ʺ߸˶�ۤι��� db_connect() error \n\n";
    exit();
}

//////////// �����μ���
$today = date('Ymd');

///// �о�����
$yyyymm = date('Ym');
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// �о�������
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}

// ��ʿ��ñ���оݷ��ǧ ����ʤ�����������
$query_chk = sprintf("SELECT average_cost FROM periodic_average_cost_history2 WHERE period_ym=%d limit 1", $p1_ym);
if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
    // ����ǡ���̵�����оݷ��������
    $sou_ym = $p2_ym;
} else {
    // ����ǡ������ꡢ�оݷ������
    $sou_ym = $p1_ym;
}

//////////// ���ץ��������ʺ߸˶�ۤι���
$query_csv = sprintf("SELECT
                        SUM(CASE
                                WHEN (SELECT max(period_ym) FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no) = %d
                                THEN ROUND(m.tnk_stock*(SELECT average_cost FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no and period_ym = %d))
                            ELSE ROUND(m.tnk_stock*(SELECT SUM(lot_cost) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and reg_no = (SELECT max(reg_no) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and as_regdate=(SELECT max(as_regdate) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888'))))
                            END)  as �߸˶��
                        FROM parts_stock_master AS m 
                        WHERE m.tnk_stock <> 0
                        and m.stock_id <> 'C'
                        and substr(m.parts_no, 1, 1)='C'
                    ", $sou_ym, $sou_ym);
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    echo "$log_date ���ץ��������ʺ߸˶�۹������� \n";
    fwrite($fpa,"$log_date ���ץ��������ʺ߸˶�۹������� \n");
    fwrite($fpb,"$log_date ���ץ��������ʺ߸˶�۹������� \n");
    echo "$log_date ���ץ��������ʺ߸˶�۹������� \n";
} else {
    $query_chk = sprintf("SELECT kin FROM daily_stock_money_history WHERE stock_ymd=%d and note='���ץ�'", $today);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        ///// ��Ͽ�ʤ� insert ����
        $query = sprintf("insert into daily_stock_money_history (stock_ymd, kin, note, last_date) values (%d, %d, '���ץ�', CURRENT_TIMESTAMP)", $today, $res_csv[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date ���ץ��������ʺ߸˶���ɲü��� \n");
            fwrite($fpb, "$log_date ���ץ��������ʺ߸˶���ɲü��� \n");
            echo "$log_date ���ץ��������ʺ߸˶���ɲü��� \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date ���ץ��������ʺ߸˶���ɲ� : {$kin} \n");
            fwrite($fpb, "$log_date ���ץ��������ʺ߸˶���ɲ� : {$kin} \n");
            echo "$log_date ���ץ��������ʺ߸˶���ɲ� : ", $kin, "\n";
        }
    } else {
        ///// ��Ͽ���� update ����
        $query = sprintf("update daily_stock_money_history set kin=%d, last_date=CURRENT_TIMESTAMP where stock_ymd=%d and note='���ץ�'", $res_csv[0][0], $today);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date ���ץ��������ʺ߸˶�۹������� \n");
            fwrite($fpb, "$log_date ���ץ��������ʺ߸˶�۹������� \n");
            echo "$log_date ���ץ��������ʺ߸˶�۹������� \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date ���ץ��������ʺ߸˶�۹��� : {$kin} \n");
            fwrite($fpb, "$log_date ���ץ��������ʺ߸˶�۹��� : {$kin} \n");
            echo "$log_date ���ץ��������ʺ߸˶�۹��� : ", $kin, "\n";
        }
    }
}

//////////// ��˥��������ʺ߸˶�ۤι���
$query_csv = sprintf("SELECT
                        SUM(CASE
                                WHEN (SELECT max(period_ym) FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no) = %d
                                THEN ROUND(m.tnk_stock*(SELECT average_cost FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no and period_ym = %d))
                            ELSE ROUND(m.tnk_stock*(SELECT SUM(lot_cost) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and reg_no = (SELECT max(reg_no) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and as_regdate=(SELECT max(as_regdate) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888'))))
                            END)  as �߸˶��
                        FROM parts_stock_master AS m 
                        WHERE m.tnk_stock <> 0
                        and m.stock_id <> 'C'
                        and substr(m.parts_no, 1, 1)='L'
                    ", $sou_ym, $sou_ym);
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    echo "$log_date ��˥��������ʺ߸˶�۹������� \n";
    fwrite($fpa,"$log_date ��˥��������ʺ߸˶�۹������� \n");
    fwrite($fpb,"$log_date ��˥��������ʺ߸˶�۹������� \n");
} else {
    $query_chk = sprintf("SELECT kin FROM daily_stock_money_history WHERE stock_ymd=%d and note='��˥�'", $today);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        ///// ��Ͽ�ʤ� insert ����
        $query = sprintf("insert into daily_stock_money_history (stock_ymd, kin, note, last_date) values (%d, %d, '��˥�', CURRENT_TIMESTAMP)", $today, $res_csv[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date ��˥��������ʺ߸˶���ɲü��� \n");
            fwrite($fpb, "$log_date ��˥��������ʺ߸˶���ɲü��� \n");
            echo "$log_date ��˥��������ʺ߸˶���ɲü��� \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date ��˥��������ʺ߸˶���ɲ� : {$kin} \n");
            fwrite($fpb, "$log_date ��˥��������ʺ߸˶���ɲ� : {$kin} \n");
            echo "$log_date ��˥��������ʺ߸˶���ɲ� : ", $kin, "\n";
        }
    } else {
        ///// ��Ͽ���� update ����
        $query = sprintf("update daily_stock_money_history set kin=%d, last_date=CURRENT_TIMESTAMP where stock_ymd=%d and note='��˥�'", $res_csv[0][0], $today);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date ��˥��������ʺ߸˶�۹������� \n");
            fwrite($fpb, "$log_date ��˥��������ʺ߸˶�۹������� \n");
            echo "$log_date ��˥��������ʺ߸˶�۹������� \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date ��˥��������ʺ߸˶�۹��� : {$kin} \n");
            fwrite($fpb, "$log_date ��˥��������ʺ߸˶�۹��� : {$kin} \n");
            echo "$log_date ��˥��������ʺ߸˶�۹��� : ", $kin, "\n";
        }
    }
}

//////////// �ġ����������ʺ߸˶�ۤι���
$query_csv = sprintf("SELECT
                        SUM(CASE
                                WHEN (SELECT max(period_ym) FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no) = %d
                                THEN ROUND(m.tnk_stock*(SELECT average_cost FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no and period_ym = %d))
                            ELSE ROUND(m.tnk_stock*(SELECT SUM(lot_cost) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and reg_no = (SELECT max(reg_no) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and as_regdate=(SELECT max(as_regdate) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888'))))
                            END)  as �߸˶��
                        FROM parts_stock_master AS m 
                        WHERE m.tnk_stock <> 0
                        and m.stock_id <> 'C'
                        and substr(m.parts_no, 1, 1)<>'C' and substr(m.parts_no, 1, 1)<>'L'
                    ", $sou_ym, $sou_ym);
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    echo "$log_date �ġ����������ʺ߸˶�۹������� \n";
    fwrite($fpa,"$log_date �ġ����������ʺ߸˶�۹������� \n");
    fwrite($fpb,"$log_date �ġ����������ʺ߸˶�۹������� \n");
} else {
    $query_chk = sprintf("SELECT kin FROM daily_stock_money_history WHERE stock_ymd=%d and note='�ġ���'", $today);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        ///// ��Ͽ�ʤ� insert ����
        $query = sprintf("insert into daily_stock_money_history (stock_ymd, kin, note, last_date) values (%d, %d, '�ġ���', CURRENT_TIMESTAMP)", $today, $res_csv[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date �ġ����������ʺ߸˶���ɲü��� \n");
            fwrite($fpb, "$log_date �ġ����������ʺ߸˶���ɲü��� \n");
            echo "$log_date �ġ����������ʺ߸˶���ɲü��� \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date �ġ����������ʺ߸˶���ɲ� : {$kin} \n");
            fwrite($fpb, "$log_date �ġ����������ʺ߸˶���ɲ� : {$kin} \n");
            echo "$log_date �ġ����������ʺ߸˶���ɲ� : ", $kin, "\n";
        }
    } else {
        ///// ��Ͽ���� update ����
        $query = sprintf("update daily_stock_money_history set kin=%d, last_date=CURRENT_TIMESTAMP where stock_ymd=%d and note='�ġ���'", $res_csv[0][0], $today);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date �ġ����������ʺ߸˶�۹������� \n");
            fwrite($fpb, "$log_date �ġ����������ʺ߸˶�۹������� \n");
            echo "$log_date �ġ����������ʺ߸˶�۹������� \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date �ġ����������ʺ߸˶�۹��� : {$kin} \n");
            fwrite($fpb, "$log_date �ġ����������ʺ߸˶�۹��� : {$kin} \n");
            echo "$log_date �ġ����������ʺ߸˶�۹��� : ", $kin, "\n";
        }
    }
}

$act_date = $yyyymm . '99';

//////////// ���ץ�ɸ���������ʺ߸˶�ۤι���
$query_csv = sprintf("SELECT
                        SUM(CASE
                                WHEN (SELECT max(period_ym) FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no) = %d
                                THEN ROUND(m.tnk_stock*(SELECT average_cost FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no and period_ym = %d))
                            ELSE ROUND(m.tnk_stock*(SELECT SUM(lot_cost) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and reg_no = (SELECT max(reg_no) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and as_regdate=(SELECT max(as_regdate) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888'))))
                            END)  as �߸˶��
                        FROM parts_stock_master AS m 
                        WHERE m.tnk_stock <> 0
                            and m.stock_id <> 'C'
                            and substr(m.parts_no, 1, 1)='C'
                            and (case when   
                                    (select kouji_no
                                    from
                                        act_payable as act
                                    left outer join
                                        order_plan
                                        using(sei_no)
                                    where
                                        act_date<=%d and act.parts_no=m.parts_no
                                        order by act_date DESC limit 1) is null THEN ''
                                ELSE
                                    (select kouji_no
                                    from
                                        act_payable as act
                                    left outer join
                                        order_plan
                                        using(sei_no)
                                    where
                                        act_date<=%d and act.parts_no=m.parts_no
                                    order by act_date DESC limit 1)
                                END
                                ) not like 'SC%%'
                        ", $sou_ym, $sou_ym, $act_date, $act_date);
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    echo "$log_date ���ץ�ɸ���������ʺ߸˶�۹������� \n";
    fwrite($fpa,"$log_date ���ץ�ɸ���������ʺ߸˶�۹������� \n");
    fwrite($fpb,"$log_date ���ץ�ɸ���������ʺ߸˶�۹������� \n");
    echo "$log_date ���ץ�ɸ���������ʺ߸˶�۹������� \n";
} else {
    $query_chk = sprintf("SELECT kin FROM daily_stock_money_history WHERE stock_ymd=%d and note='���ץ�ɸ��'", $today);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        ///// ��Ͽ�ʤ� insert ����
        $query = sprintf("insert into daily_stock_money_history (stock_ymd, kin, note, last_date) values (%d, %d, '���ץ�ɸ��', CURRENT_TIMESTAMP)", $today, $res_csv[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date ���ץ�ɸ���������ʺ߸˶���ɲü��� \n");
            fwrite($fpb, "$log_date ���ץ�ɸ���������ʺ߸˶���ɲü��� \n");
            echo "$log_date ���ץ�ɸ���������ʺ߸˶���ɲü��� \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date ���ץ�ɸ���������ʺ߸˶���ɲ� : {$kin} \n");
            fwrite($fpb, "$log_date ���ץ�ɸ���������ʺ߸˶���ɲ� : {$kin} \n");
            echo "$log_date ���ץ�ɸ���������ʺ߸˶���ɲ� : ", $kin, "\n";
        }
    } else {
        ///// ��Ͽ���� update ����
        $query = sprintf("update daily_stock_money_history set kin=%d, last_date=CURRENT_TIMESTAMP where stock_ymd=%d and note='���ץ�ɸ��'", $res_csv[0][0], $today);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date ���ץ�ɸ���������ʺ߸˶�۹������� \n");
            fwrite($fpb, "$log_date ���ץ�ɸ���������ʺ߸˶�۹������� \n");
            echo "$log_date ���ץ�ɸ���������ʺ߸˶�۹������� \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date ���ץ�ɸ���������ʺ߸˶�۹��� : {$kin} \n");
            fwrite($fpb, "$log_date ���ץ�ɸ���������ʺ߸˶�۹��� : {$kin} \n");
            echo "$log_date ���ץ�ɸ���������ʺ߸˶�۹��� : ", $kin, "\n";
        }
    }
}

//////////// ���ץ������������ʺ߸˶�ۤι���
$query_csv = sprintf("SELECT
                        SUM(CASE
                                WHEN (SELECT max(period_ym) FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no) = %d
                                THEN ROUND(m.tnk_stock*(SELECT average_cost FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no and period_ym = %d))
                            ELSE ROUND(m.tnk_stock*(SELECT SUM(lot_cost) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and reg_no = (SELECT max(reg_no) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and as_regdate=(SELECT max(as_regdate) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888'))))
                            END)  as �߸˶��
                        FROM parts_stock_master AS m 
                        WHERE m.tnk_stock <> 0
                            and m.stock_id <> 'C'
                            and substr(m.parts_no, 1, 1)='C'
                            and (select kouji_no
                                from
                                    act_payable as act
                                left outer join
                                    order_plan
                                    using(sei_no)
                                where
                                    act_date<=%d and act.parts_no=m.parts_no
                                order by act_date DESC limit 1) like 'SC%%'
                        ", $sou_ym, $sou_ym, $act_date);
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    echo "$log_date ���ץ������������ʺ߸˶�۹������� \n";
    fwrite($fpa,"$log_date ���ץ������������ʺ߸˶�۹������� \n");
    fwrite($fpb,"$log_date ���ץ������������ʺ߸˶�۹������� \n");
    echo "$log_date ���ץ������������ʺ߸˶�۹������� \n";
} else {
    $query_chk = sprintf("SELECT kin FROM daily_stock_money_history WHERE stock_ymd=%d and note='���ץ�����'", $today);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        ///// ��Ͽ�ʤ� insert ����
        $query = sprintf("insert into daily_stock_money_history (stock_ymd, kin, note, last_date) values (%d, %d, '���ץ�����', CURRENT_TIMESTAMP)", $today, $res_csv[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date ���ץ������������ʺ߸˶���ɲü��� \n");
            fwrite($fpb, "$log_date ���ץ������������ʺ߸˶���ɲü��� \n");
            echo "$log_date ���ץ������������ʺ߸˶���ɲü��� \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date ���ץ������������ʺ߸˶���ɲ� : {$kin} \n");
            fwrite($fpb, "$log_date ���ץ������������ʺ߸˶���ɲ� : {$kin} \n");
            echo "$log_date ���ץ������������ʺ߸˶���ɲ� : ", $kin, "\n";
        }
    } else {
        ///// ��Ͽ���� update ����
        $query = sprintf("update daily_stock_money_history set kin=%d, last_date=CURRENT_TIMESTAMP where stock_ymd=%d and note='���ץ�����'", $res_csv[0][0], $today);
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            fwrite($fpa, "$log_date ���ץ������������ʺ߸˶�۹������� \n");
            fwrite($fpb, "$log_date ���ץ������������ʺ߸˶�۹������� \n");
            echo "$log_date ���ץ������������ʺ߸˶�۹������� \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date ���ץ������������ʺ߸˶�۹��� : {$kin} \n");
            fwrite($fpb, "$log_date ���ץ������������ʺ߸˶�۹��� : {$kin} \n");
            echo "$log_date ���ץ������������ʺ߸˶�۹��� : ", $kin, "\n";
        }
    }
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
fclose($fpa);      ////// �����ѥ�����߽�λ
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
?>
