#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// �������μ�ư��Ͽ(ɸ����)Ⱦǯ����κǿ���ư��Ͽ�ʤ�����å������ԡ����� //
// Copyright (C) 2005-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/05/31 Created  material_auto_registry_cli.php                       //
// 2005/06/01 last_user �˥��ԡ����ηײ��ֹ����Ͽ���롣��ư��Ͽ�ʤ�00:00:00//
// 2005/06/02 ̤��Ͽ�Υ����å���header��history���ѹ�(���٤�����Ͽ���б�)   //
//            ��Ͽ���� ����������������ԡ�������Ͽ�� ���ѹ�(ʬ����䤹��)  //
// 2005/06/08 ���ԡ��������վ�����Ͽ���������׾������ѹ�                //
// 2005/06/09 ����®�٤�®��뤿��׾�������Ⱦǯ�������դ����������˹Ԥ�  //
// 2005/12/20 �롼����Ǥ���Ͽ�Ѥߤ�ng->ok���ѹ����᡼��Υ���$fpc��$fpa��//
// 2006/09/13 ���Ū��Ⱦǯ������򣴥��������ѹ� '6 month' �� '4 month'     //
// 2006/09/21 Ⱦǯ��������إ����������ִ����¹�                            //
// 2006/11/30 ���������򣳥��������ѹ� '4 month' �� '3 month' ��å������ѹ�//
// 2007/08/07 ���������򣱥��������ѹ� '3 month' �� '1 month' ��å������ѹ�//
// 2009/07/10 �᡼���������norihisa_ooya@nitto-kohki.co.jp���ɲ�      ��ë //
// 2009/12/18 ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤ��ɲ�           ��ë //
// 2015/02/16 ���������򣲥��������ѹ� '1 month' �� '2 month' ��å������ѹ�//
// 2019/04/24 ���������򣳥��������ѹ� '2 month' �� '3 month' ��å������ѹ�//
// 2020/01/06 ���������򣲥��������ѹ� '3 month' �� '2 month' ��å������ѹ�//
// 2020/03/03 �������ryota_waki@nitto-kohki.co.jp���ɲ�               ��ë //
// 2020/03/17 ���������򣱥��������ѹ� '2 month' �� '1 month' ��å������ѹ�//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
    // ���ߤ�CLI�Ǥ�default='1', SAPI�Ǥ�default='0'�ˤʤäƤ��롣CLI�ǤΤߥ�����ץȤ����ѹ�����롣
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ(CLI�ǰʳ�)
require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���

$log_date = date('Y-m-d H:i:s');            // �����ѥ�������
// $regdate  = date('Y-m-d') . ' 00:00:00';    // ��ư��Ͽ��
$log_name_a = '/tmp/material_auto_registry.log';
$log_name_b = '/tmp/material_auto_unregist.log';
$log_name_c = '/tmp/material_auto_registok.log';
$fpa = fopen($log_name_a, 'a+');    // ���ƤΥ�
$fpb = fopen($log_name_b, 'a+');    // ��Ͽ����ʤ��ä���
$fpc = fopen($log_name_c, 'a+');    // ��Ͽ���褿��
$fpd = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// ����ǡ����Ƽ����ѥ��ե�����ؤν���ߤǥ����ץ�
fwrite($fpd, "�������μ�ư��Ͽ\n");
fwrite($fpd, "/home/www/html/tnk-web/industry/material/material_auto_registry_cli.php\n");

/////////// �ǡ����١����ȥ��ͥ�������Ω
if ( !($con = funcConnect()) ) {
    fwrite($fpa, "$log_date funcConnect() error \n");
    fwrite($fpd, "$log_date funcConnect() error \n");
    fclose($fpa);      ////// �����ѥ�����߽�λ
    fwrite($fpd, "------------------------------------------------------------------------\n");
    fclose($fpd);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
    exit;
}

///// ������̤��Ͽ�Υꥹ�Ȥ�DB��������������˳�Ǽ����
///// ����Υե�����ɤ� assy_no, plan_no, �����
$str_date = date_offset(1);
$end_date = date_offset(1);
// $str_date = '20001001';  // �����Υǡ���
// $end_date = '20050602';
$query = "
    SELECT  uri.assyno                      as �����ֹ�         -- 0
        ,   trim(substr(item.midsc, 1, 16)) as ����̾           -- 1
        ,   uri.�ײ��ֹ�                    as �ײ��ֹ�         -- 2
        ,   uri.�׾���                      as �����           -- 3
        ,   uri.����                        as ����           -- 4
    FROM
        hiuuri as uri
    LEFT OUTER JOIN
        miitem as item
    ON (uri.assyno = item.mipn)
    LEFT OUTER JOIN
        material_cost_history as mate    -- Ⱦ���ǹʤ���ޤʤ�(�ײ��ֹ椬���Фξ��) header��history��
    ON (uri.�ײ��ֹ� = mate.plan_no)
    LEFT OUTER JOIN
          assembly_schedule as sch
    ON (uri.�ײ��ֹ�=sch.plan_no)
    WHERE
        uri.�׾���>={$str_date}
        and uri.�׾���<={$end_date}
        and uri.datatype='1'
        and mate.plan_no IS NULL
        and sch.note15 not like 'SC%'   -- C�����̵�����
    ORDER BY uri.assyno ASC
    -- OFFSET 0 LIMIT 2000
";
fwrite($fpa, "$log_date ����� $str_date �� $end_date \n");
fwrite($fpb, "$log_date ����� $str_date �� $end_date \n");
fwrite($fpc, "$log_date ����� $str_date �� $end_date \n");
$res = array();
if ( ($rows=getResult($query, $res)) <= 0) {
    $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
    fwrite($fpa, "$log_date ������� ̤��Ͽ�����(����)�ǡ���������ޤ���\n");
    fwrite($fpb, "$log_date ������� ̤��Ͽ�����(����)�ǡ���������ޤ���\n");
    fwrite($fpc, "$log_date ������� ̤��Ͽ�����(����)�ǡ���������ޤ���\n");
    fclose($fpa);      ////// �����ѥ�����߽�λ
    fclose($fpb);      ////// �����ѥ�����߽�λ
    fclose($fpc);      ////// �����ѥ�����߽�λ
    exit;
}

///// ����ǡ������� INSERT INTO table SELECT ��� ��¹�
$rec_ok = 0;
$rec_ng = 0;
$dupli  = 0;
for ($i=0; $i<$rows; $i++) {
    /////////// begin �ȥ�󥶥�����󳫻�
    query_affected_trans($con, 'begin');
    /////////// ̤��Ͽ�ʤη׾������飱�����������դ����(����®�٤�®��뤿������˹Ԥ�)
    //$query = "SELECT to_char(date '{$res[$i]['�����']}' - interval '3 month', 'YYYYMMDD')";
    //$query = "SELECT to_char(date '{$res[$i]['�����']}' - interval '2 month', 'YYYYMMDD')";
    $query = "SELECT to_char(date '{$res[$i]['�����']}' - interval '1 month', 'YYYYMMDD')";
    if (getUniResTrs($con, $query, $pre_date) <= 0) {    // �ȥ�󥶥��������Ǥ� Unique�Ȳ����ѥ����꡼
        $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
        fwrite($fpa, "{$log_date} {$res[$i]['�ײ��ֹ�']} {$res[$i]['�����ֹ�']} {$res[$i]['�����']} �������������դμ����˼��� {$res[$i]['����̾']}\n");
        query_affected_trans($con, 'rollback');         // �ȥ�󥶥������Υ���Хå�
        exit;
    }
    /////////// ���������κǿ�����μ��� (2005/06/08 regdate�Ǥʤ���������ѹ�)
    $query = "SELECT plan_no, to_char(regdate, 'YYYYMMDD')
                FROM
                    material_cost_header
                LEFT OUTER JOIN
                    hiuuri
                ON (�ײ��ֹ� = plan_no)
                WHERE
                    assy_no='{$res[$i]['�����ֹ�']}'
                and
                    �׾��� >= {$pre_date}           -- ������������
                and
                    �׾��� <= {$res[$i]['�����']}  -- �����(������)�ޤ�
                and
                    CAST(regdate AS time(0)) != (time '00:00:00')   -- ��ư��Ͽ�����
                ORDER BY assy_no DESC, �׾��� DESC LIMIT 1
    ";
    $pre = array();
    if (getResultTrs($con, $query, $pre) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
        fwrite($fpa, "{$log_date} {$res[$i]['�ײ��ֹ�']} {$res[$i]['�����ֹ�']} ���������˷���̵���Τ���Ͽ����ޤ��� {$res[$i]['����̾']}\n");
        fwrite($fpb, "{$log_date} {$res[$i]['�ײ��ֹ�']} {$res[$i]['�����ֹ�']} ���������˷���̵���Τ���Ͽ����ޤ��� {$res[$i]['����̾']}\n");
        $rec_ng++;
    } else {
        ////////// ��Ͽ�ѤߤΥ����å�(�롼����Ǵ�����Ͽ�������)
        $query = "SELECT plan_no FROM material_cost_header WHERE plan_no='{$res[$i]['�ײ��ֹ�']}'";
        if (getUniResTrs($con, $query, $tmp_plan_no) >= 1) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
            fwrite($fpa, "{$log_date} {$res[$i]['�ײ��ֹ�']} {$res[$i]['�����ֹ�']} {$res[$i]['����̾']} ������Ͽ�Ѥ�\n");
            /////////// �ȥ�󥶥������Υ���Хå�
            query_affected_trans($con, 'rollback');
            $rec_ok++;
            $dupli++;
            continue;
        }
        $pre_plan_no = $pre[0][0];                      // ���ԡ����ηײ��ֹ�
        /////////// ��ư��Ͽ��������
        $regdate     = "{$pre[0][1]} 00:00:00";         // ���ԡ�������Ͽ����ư��Ͽ���ˤ���
        // $regdate  = "{$res[$i]['�����']} 00:00:00";    // ��ư��Ͽ��
        /////////// ���٥ơ��֥뤫�鹹��
        $query = "INSERT INTO material_cost_history (
                plan_no, assy_no, parts_no, pro_no, pro_mark,
                par_parts, pro_price, pro_num, intext,
                regdate, last_date, last_user
            )
            SELECT
                  '{$res[$i]['�ײ��ֹ�']}', '{$res[$i]['�����ֹ�']}', parts_no, pro_no, pro_mark,
                  par_parts, pro_price, pro_num, intext, '{$regdate}', CURRENT_TIMESTAMP, '{$pre_plan_no}'
            FROM material_cost_history
            WHERE
                plan_no='{$pre_plan_no}'
            and
                assy_no='{$res[$i]['�����ֹ�']}'
            ORDER BY regdate ASC
        ";
        if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
            $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
            fwrite($fpa, "{$log_date} ���٤� INSERT INTO table SELECT �˼��ԡ�\n{$query}\n");
            fwrite($fpb, "{$log_date} {$res[$i]['�ײ��ֹ�']} {$res[$i]['�����ֹ�']} ���٤� INSERT INTO table SELECT �˼��ԡ� {$res[$i]['����̾']}\n");
        } else {
            $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
            fwrite($fpa, "{$log_date} {$res[$i]['�ײ��ֹ�']}��{$pre_plan_no} {$res[$i]['�����ֹ�']} ����data��Ͽ��λ {$res[$i]['����̾']}\n");
            /////////// ���٤������Ǥ�����إå����ơ��֥�ι���
            $query = "INSERT INTO material_cost_header (
                    plan_no, assy_no, sum_price, ext_price, int_price,
                    m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate,
                    regdate, last_date, last_user
                )
                SELECT
                      '{$res[$i]['�ײ��ֹ�']}', '{$res[$i]['�����ֹ�']}', sum_price, ext_price, int_price,
                      m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate,
                      '{$regdate}', CURRENT_TIMESTAMP, '{$pre_plan_no}'
                FROM material_cost_header
                WHERE
                    plan_no='{$pre_plan_no}'
                and
                    assy_no='{$res[$i]['�����ֹ�']}' -- �����ɬ�פʤ������
                ORDER BY plan_no ASC
            ";
            if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
                fwrite($fpa, "{$log_date} �إå����� INSERT INTO table SELECT �˼��ԡ�\n{$query}\n");
                fwrite($fpb, "{$log_date} {$res[$i]['�ײ��ֹ�']} {$res[$i]['�����ֹ�']} �إå����� INSERT INTO table SELECT �˼��ԡ� {$res[$i]['����̾']}\n");
                /////////// �ȥ�󥶥������Υ���Хå�
                query_affected_trans($con, 'rollback');
                $rec_ng++;
                continue;
            } else {
                $log_date = date('Y-m-d H:i:s');    // �����ѥ�������
                fwrite($fpa, "{$log_date} {$res[$i]['�ײ��ֹ�']}��{$pre_plan_no} {$res[$i]['�����ֹ�']} �إå�����Ͽ��λ {$res[$i]['����̾']}\n");
                fwrite($fpc, "{$log_date} {$res[$i]['�ײ��ֹ�']}��{$pre_plan_no} {$res[$i]['�����ֹ�']} ���١��إå�����Ͽ��λ {$res[$i]['����̾']}\n");
            }
        }
        $rec_ok++;
    }
    /////////// commit �ȥ�󥶥������Υ��ߥå�
    query_affected_trans($con, 'commit');
}

$log_date = date('Y-m-d H:i:s');    // �����ѥ�������
fwrite($fpa, "{$log_date} {$rec_ok}/{$rows} ��ư��Ͽ���ޤ����� {$rec_ng}/{$rows} ��Ͽ����ޤ���Ǥ�����(��ʣ�ײ衧{$dupli}��)\n");
fwrite($fpb, "{$log_date} {$rec_ok}/{$rows} ��ư��Ͽ���ޤ����� {$rec_ng}/{$rows} ��Ͽ����ޤ���Ǥ�����(��ʣ�ײ衧{$dupli}��)\n");
fwrite($fpc, "{$log_date} {$rec_ok}/{$rows} ��ư��Ͽ���ޤ����� {$rec_ng}/{$rows} ��Ͽ����ޤ���Ǥ�����(��ʣ�ײ衧{$dupli}��)\n");

if (rewind($fpa)) {
    $to = 'tnksys@nitto-kohki.co.jp, usoumu@nitto-kohki.co.jp, norihisa_ooya@nitto-kohki.co.jp, ryota_waki@nitto-kohki.co.jp';
    $subject = "�������μ�ư��Ͽ��� {$log_date}";
    $msg = fread($fpa, filesize($log_name_a));
    $header = "From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp\r\n";
    mb_send_mail($to, $subject, $msg, $header);
}

fclose($fpa);      ////// �����ѥ�����߽�λ
fclose($fpb);      ////// �����ѥ�����߽�λ
fclose($fpc);      ////// �����ѥ�����߽�λ
fwrite($fpd, "------------------------------------------------------------------------\n");
fclose($fpd);      ////// ����ǡ����Ƽ����ѥ�����߽�λ
exit();
?>
