#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �������κ�����μ�ư��Ͽ���� (ñ����Ͽ�ֹ桦����������񡦹��ñ��)  //
// Copyright (C) 2006-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/27 Created  parts_material_auto_registry_span_cli.php            //
//            ���ֻ������Ͽ����(��Ͽ�Ǥ��ʤ��ä���Τ��б�)                //
// 2006/03/02 2/28��CQ36860-0������ñ����Ͽ�������׾�ؤ��б�(̤����ʲ���) //
//      as_regdate<{$res[$i]['�׾���']} �� as_regdate<={$res[$i]['�׾���']} //
// 2007/08/08 ���׾������������Ͽ����Ѣ��������ѹ�������Ǥ�ʤ�����  //
//            �����Ͽ����Ѥ�����ѹ�������Ǥ�ʤ�����̵���Υ�å�����  //
// 2009/07/10 �᡼���������norihisa_ooya@nitto-kohki.co.jp�ɲ�        ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
    // ���ߤ�CLI�Ǥ�default='1', SAPI�Ǥ�default='0'�ˤʤäƤ��롣CLI�ǤΤߥ�����ץȤ����ѹ�����롣
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ(CLI�ǰʳ�)
require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
$log_name_a = '/tmp/parts_material_auto_registry.log';
$fpa = fopen($log_name_a, 'a+');    // ���ƤΥ� w=���Υ���ä� a=�ɲ�

/////////// �ǡ����١����ȥ��ͥ�������Ω
if ( !($con = funcConnect()) ) {
    fwrite($fpa, "$log_date funcConnect() error \n");
    fclose($fpa);      ////// �����ѥ�����߽�λ
    exit;
}

///// ����������ʬ(���ϻ����ϰ�)�Υꥹ�Ȥ�DB��������������˳�Ǽ����
///// ����Υե�����ɤ� assy_no, �����
$str_date = '20070501';
$end_date = '20070531';
// $end_date = date_offset(1);

$query = "
    SELECT  uri.assyno                      as �����ֹ�         -- 0
        ,   trim(substr(item.midsc, 1, 16)) as ����̾           -- 1
        ,   uri.�׾���                      as �׾���           -- 3
    FROM
        hiuuri AS uri
    LEFT OUTER JOIN
        miitem AS item
    ON (uri.assyno = item.mipn)
    WHERE
        uri.�׾���>={$str_date}
        and uri.�׾���<={$end_date}
        and uri.datatype >= '5'
        and trim(uri.assyno) != ''
        and uri.assyno IS NOT NULL
    ORDER BY uri.�׾��� ASC, uri.assyno ASC
";
fwrite($fpa, "$log_date ����� $str_date �� $end_date \n");
$res = array();
if ( ($rows=getResult($query, $res)) <= 0) {
    $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
    fwrite($fpa, "$log_date �������κ����� ̤��Ͽ�����ǡ���������ޤ���\n");
    fclose($fpa);      ////// �����ѥ�����߽�λ
    exit;
}

///// ����ǡ������� INSERT INTO table SELECT ��� ��¹�
$rec_ok = 0;
$rec_ng = 0;
$dupli  = 0;
for ($i=0; $i<$rows; $i++) {
    /////////// begin �ȥ�󥶥�����󳫻�
    query_affected_trans($con, 'begin');
    ////////// ��Ͽ�ѤߤΥ����å�(�롼����Ǵ�����Ͽ�������)
    $query = "
        SELECT cost_reg FROM sales_parts_material_history
        WHERE parts_no='{$res[$i]['�����ֹ�']}' AND sales_date={$res[$i]['�׾���']}
    ";
    if (getUniResTrs($con, $query, $tmp_plan_no) >= 1) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        // $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
        // fwrite($fpa, "{$log_date} {$res[$i]['�����ֹ�']} �׾��� {$res[$i]['�׾���']} �Ǵ�����Ͽ�Ѥ� {$res[$i]['����̾']}\n");
        /////////// �ȥ�󥶥������Υ���Хå�
        query_affected_trans($con, 'ROLLBACK');
        $rec_ok++;
        $dupli++;
        continue;
    }
    /////////// ����ñ�����򤫤�ñ����Ͽ�ֹ桦����������񡦹��ñ�������
    $query = "
        SELECT
            (   SELECT reg_no FROM parts_cost_history
                WHERE parts_no='{$res[$i]['�����ֹ�']}' AND as_regdate <= {$res[$i]['�׾���']}
                AND lot_no=1 AND vendor!='88888'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC LIMIT 1
            ) AS cost_reg   -- 0
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['�����ֹ�']}' AND as_regdate <= {$res[$i]['�׾���']}
                AND lot_no=1 AND vendor!='88888' AND vendor!='01111' AND vendor!='00222'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC LIMIT 1
            ) AS ext_cost   -- 1
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['�����ֹ�']}' AND as_regdate <= {$res[$i]['�׾���']}
                AND lot_no=1 AND vendor!='88888' AND (vendor='01111' OR vendor='00222')
                GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC LIMIT 1
            ) AS int_cost   -- 2
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['�����ֹ�']}' AND as_regdate <= {$res[$i]['�׾���']}
                AND lot_no=1 AND vendor!='88888'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC LIMIT 1
            ) AS unit_cost  -- 3
            ,   ----------------- �ʲ������׾��������Ǹ��Ĥ���ʤ����˻��� -----------------
            (   SELECT reg_no FROM parts_cost_history
                WHERE parts_no='{$res[$i]['�����ֹ�']}' AND as_regdate > {$res[$i]['�׾���']}
                AND lot_no=1 AND vendor!='88888'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate ASC, reg_no DESC LIMIT 1
            ) AS cost_reg2  -- 4
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['�����ֹ�']}' AND as_regdate > {$res[$i]['�׾���']}
                AND lot_no=1 AND vendor!='88888' AND vendor!='01111' AND vendor!='00222'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate ASC, reg_no ASC LIMIT 1
            ) AS ext_cost2  -- 5
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['�����ֹ�']}' AND as_regdate > {$res[$i]['�׾���']}
                AND lot_no=1 AND vendor!='88888' AND (vendor='01111' OR vendor='00222')
                GROUP BY as_regdate, reg_no ORDER BY as_regdate ASC, reg_no ASC LIMIT 1
            ) AS int_cost2  -- 6
            ,
            (   SELECT sum(lot_cost) FROM parts_cost_history
                WHERE parts_no='{$res[$i]['�����ֹ�']}' AND as_regdate > {$res[$i]['�׾���']}
                AND lot_no=1 AND vendor!='88888'
                GROUP BY as_regdate, reg_no ORDER BY as_regdate ASC, reg_no ASC LIMIT 1
            ) AS unit_cost2 -- 7
    ";
    if (getResultTrs($con, $query, $resCost) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
        fwrite($fpa, "{$log_date} {$res[$i]['�����ֹ�']} �Ϸ׾��� {$res[$i]['�׾���']} ����ñ����Ͽ��̵�� {$res[$i]['����̾']}\n");
        /////////// �ȥ�󥶥������Υ���Хå�
        query_affected_trans($con, 'ROLLBACK');
        $rec_ng++;
        continue;
    }
    /////////// �ǡ�����ͭ��̵�������å�
    if ( (!$resCost[0][0]) && (!$resCost[0][4]) ) {  // ��Ͽ�ֹ椬���뤫��
        $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
        fwrite($fpa, "{$log_date} {$res[$i]['�����ֹ�']} �Ϸ׾��� {$res[$i]['�׾���']} ����ñ����Ͽ��̵�� {$res[$i]['����̾']}\n");
        /////////// �ȥ�󥶥������Υ���Хå�
        query_affected_trans($con, 'ROLLBACK');
        $rec_ng++;
        continue;
    }
    if (!$resCost[0][1]) $resCost[0][1] = '0';
    if (!$resCost[0][2]) $resCost[0][2] = '0';
    if (!$resCost[0][3]) $resCost[0][3] = '0';
    if (!$resCost[0][5]) $resCost[0][5] = '0';
    if (!$resCost[0][6]) $resCost[0][6] = '0';
    if (!$resCost[0][7]) $resCost[0][7] = '0';
    /////////// sales_parts_material_history ����Ͽ�¹�
    if ($resCost[0][0]) {   // ��������Ͽ�ֹ椬���뤫��
        $query = "
            INSERT INTO sales_parts_material_history (parts_no, sales_date, cost_reg, ext_cost, int_cost, unit_cost)
            VALUES ('{$res[$i]['�����ֹ�']}', {$res[$i]['�׾���']}, {$resCost[0][0]}, {$resCost[0][1]}, {$resCost[0][2]}, {$resCost[0][3]})
        ";
    } else {                // �����Ͽ�ֹ�Τ�Τ���Ѥ���
        $query = "
            INSERT INTO sales_parts_material_history (parts_no, sales_date, cost_reg, ext_cost, int_cost, unit_cost)
            VALUES ('{$res[$i]['�����ֹ�']}', {$res[$i]['�׾���']}, {$resCost[0][4]}, {$resCost[0][5]}, {$resCost[0][6]}, {$resCost[0][7]})
        ";
    }
    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
        $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
        fwrite($fpa, "{$log_date} {$res[$i]['�����ֹ�']} �׾��� {$res[$i]['�׾���']} ��Ͽ�˼��ԡ� {$res[$i]['����̾']}\n");
        query_affected_trans($con, 'ROLLBACK');
        $rec_ng++;
        continue;
    }
    $rec_ok++;
    /////////// COMMIT �ȥ�󥶥������Υ��ߥå�
    query_affected_trans($con, 'COMMIT');
}

$log_date = date('Y-m-d H:i:s');    // �����ѥ�������
fwrite($fpa, "{$log_date} {$rec_ok}/{$rows} ��ư��Ͽ���ޤ����� {$rec_ng}/{$rows} ��Ͽ����ޤ���Ǥ�����(��ʣ�����{$dupli}��)\n");

if (rewind($fpa)) {
    $to = 'tnksys@nitto-kohki.co.jp, usoumu@nitto-kohki.co.jp, norihisa_ooya@nitto-kohki.co.jp';
    $subject = "�������κ�����μ�ư��Ͽ��� {$log_date}";
    $msg = fread($fpa, filesize($log_name_a));
    $header = "From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp\r\n";
    mb_send_mail($to, $subject, $msg, $header);
}

fclose($fpa);      ////// �����ѥ�����߽�λ
exit();
?>
