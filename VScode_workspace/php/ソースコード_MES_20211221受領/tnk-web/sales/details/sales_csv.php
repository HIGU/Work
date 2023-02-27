<?php
//////////////////////////////////////////////////////////////////////////////
// ������٤ξȲ� CSV����                                                   //
// Copyright (C) 2010-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/05/21 Created   materialNewSales_csv.php                            //
// 2010/12/20 ��������б�       ľǼ��Ĵ����������                       //
// 2010/12/24 ľǼ��Ĵ����ʸ���������б�                                    //
// 2011/11/10 �ƥ��Ȥ�NKCT��NKT���ɲ� �� �����ɲ� ���θ���                  //
// 2011/11/21 �ե�����̾�Ѵ��ǥ��ץ�����ȴ���Ƥ����Τ���                //
//            �ե�����̾����������.csv���ѹ�                              //
// 2013/01/29 �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ�     //
// 2013/05/28 ������λ�����ɲ�                                            //
// 2014/11/19 ����ξ��Ϲ����ֹ����Ϥ���褦���ѹ�                      //
// 2015/11/27 ����(���ܡ�5�ʹ�)���ϻ���������������������Ǥ��Ƥ��ʤ�     //
//            �Զ�����                                                  //
// 2018/06/22 �����������ʤ�ȴ���Ф��Ƥ��ʤ��ä��Τ�����                  //
// 2019/10/09 ��ɥƥå��ȥ�ɡ����ȤΥե�����̾�Ѵ�����äƤ����Τǽ�����ë//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class

// �ե�����̾��SQL�Υ���������������
$outputFile = $_GET['csvname'] . '-' . '�������.csv';
$csv_search = $_GET['csvsearch'];
$act_name   = $_GET['actname'];
// SQL�Υ��������ǰ���ѹ�������ʬ�򸵤��᤹
$search     = str_replace('keidate','�׾���',$csv_search);
$search     = str_replace('jigyou','������',$search);
$search     = str_replace('denban','��ɼ�ֹ�',$search);
$search     = str_replace('tokui','������',$search);
$search     = str_replace('/','\'',$search);
// ����������ʸ�������ɤ�EUC���ѹ������ǰ�Τ����
$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

// �ե�����̾�ǰ���ѹ�������ʬ�򸵤��᤹
$outputFile     = str_replace('ALL','�����롼��',$outputFile);
$outputFile     = str_replace('C-all','���ץ�����',$outputFile);
$outputFile     = str_replace('C-hyou','���ץ�ɸ��',$outputFile);
$outputFile     = str_replace('C-toku','���ץ�����',$outputFile);
$outputFile     = str_replace('L-all','��˥�����',$outputFile);
$outputFile     = str_replace('L-hyou','��˥��Τ�',$outputFile);
$outputFile     = str_replace('L-bimor','���Υݥ��',$outputFile);
$outputFile     = str_replace('C-shuri','���ץ�',$outputFile);
$outputFile     = str_replace('L-shuri','��˥��',$outputFile);
$outputFile     = str_replace('NKB','���ʴ���',$outputFile);
$outputFile     = str_replace('TOOL','�ġ���',$outputFile);
$outputFile     = str_replace('NONE','�ʤ�',$outputFile);
$outputFile     = str_replace('SHISAKU','���',$outputFile);
$outputFile     = str_replace('NKCT','NKCT',$outputFile);
$outputFile     = str_replace('NKT','NKT',$outputFile);
$outputFile     = str_replace('NONE','�ʤ�',$outputFile);

// �ե�����̾�ǰ���ѹ�������ʬ�򸵤��᤹��������̾��
$outputFile     = str_replace('T-�����롼��','����',$outputFile);
$outputFile     = str_replace('T-NK','���칩��',$outputFile);
$outputFile     = str_replace('T-MEDOS','��ɡ�����',$outputFile);
$outputFile     = str_replace('T-NKT','NKT',$outputFile);
$outputFile     = str_replace('T-MEDOTEC','��ɥƥå�',$outputFile);
$outputFile     = str_replace('T-SNK','������칩��',$outputFile);
$outputFile     = str_replace('T-NKCT','NKCT',$outputFile);
$outputFile     = str_replace('T-BRECO','BRECO',$outputFile);
$outputFile     = str_replace('T-SHO','����',$outputFile);

// �¹ԼԤΥѥ������CSV����¸����١��ե�����̾��ʸ�������ɤ�SJIS���Ѵ�
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

//////////// CSV�����ѤΥǡ�������
if ($act_name == 'C-toku') {        // ���ץ�����ξ��
    $query_csv = sprintf("select
                            u.�׾���        as �׾���,                  -- 0
                            CASE
                                    WHEN u.datatype=1 THEN '����'
                                    WHEN u.datatype=2 THEN '����'
                                    WHEN u.datatype=3 THEN '����'
                                    WHEN u.datatype=4 THEN 'Ĵ��'
                                    WHEN u.datatype=5 THEN '��ư'
                                    WHEN u.datatype=6 THEN 'ľǼ'
                                    WHEN u.datatype=7 THEN '���'
                                    WHEN u.datatype=8 THEN '����'
                                    WHEN u.datatype=9 THEN '����'
                                    ELSE u.datatype
                                END             as ��ʬ,                -- 1
                            CASE
                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.�ײ��ֹ�
                            END                     as �ײ��ֹ�,        -- 2
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as �����ֹ�,        -- 3
                            CASE
                                WHEN trim(substr(m.midsc,1,25)) = '' THEN '-----'
                                ELSE substr(m.midsc,1,38)
                            END             as ����̾,                  -- 4
                            CASE
                                WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.���˾��
                            END                     as ����,            -- 5
                            u.����          as ����,                    -- 6
                            u.ñ��          as ����ñ��,                -- 7
                            Uround(u.���� * u.ñ��, 0) as ����,       -- 8
                            trim(a.note15)  as �����ֹ�,                -- 9
                            aden.order_price  as ����ñ��,              --10
                            CASE
                                WHEN aden.order_price <= 0 THEN '0'
                                ELSE Uround(u.ñ�� / aden.order_price, 3) * 100
                            END                     as Ψ��,            --11
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) = 0 THEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as �������,        -- 12
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) = 0 THEN Uround(u.ñ�� / ((select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)), 3) * 100
                                ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                     as Ψ��             -- 13
                      from
                            hiuuri as u
                      left outer join
                            assembly_schedule as a
                      on u.�ײ��ֹ�=a.plan_no
                      left outer join
                            aden_master as aden
                      -- on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)
                      on (a.plan_no=aden.plan_no)
                      left outer join
                            miitem as m
                      on u.assyno=m.mipn
                      left outer join
                            material_cost_header as mate
                      on u.�ײ��ֹ�=mate.plan_no
                      left outer join
                            product_support_master AS groupm
                      on u.assyno=groupm.assy_no
                      %s
                      order by �׾���, assyno
                      ", $search);   // ���� $search �Ǹ���

} else {        // ����ʳ�
    $query_csv = sprintf("select
                            u.�׾���        as �׾���,                  -- 0
                            CASE
                                    WHEN u.datatype=1 THEN '����'
                                    WHEN u.datatype=2 THEN '����'
                                    WHEN u.datatype=3 THEN '����'
                                    WHEN u.datatype=4 THEN 'Ĵ��'
                                    WHEN u.datatype=5 THEN '��ư'
                                    WHEN u.datatype=6 THEN 'ľǼ'
                                    WHEN u.datatype=7 THEN '���'
                                    WHEN u.datatype=8 THEN '����'
                                    WHEN u.datatype=9 THEN '����'
                                    ELSE u.datatype
                                END             as ��ʬ,                -- 1
                            CASE
                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.�ײ��ֹ�
                            END                     as �ײ��ֹ�,        -- 2
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as �����ֹ�,        -- 3
                            CASE
                                WHEN trim(substr(m.midsc,1,25)) = '' THEN '-----'
                                ELSE substr(m.midsc,1,38)
                            END             as ����̾,                  -- 4
                            CASE
                                WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.���˾��
                            END                     as ����,            -- 5
                            u.����          as ����,                    -- 6
                            u.ñ��          as ����ñ��,                -- 7
                            Uround(u.���� * u.ñ��, 0) as ����,       -- 8
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL 
                                    THEN CASE
                                            WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1) IS NULL
                                                THEN (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                            ELSE (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                         END
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as �������,        -- 9
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL
                                    THEN CASE
                                            WHEN Uround(u.ñ�� / ((select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)), 3) * 100 IS NULL
                                                THEN Uround(u.ñ�� / (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1), 3) * 100
                                            ELSE Uround(u.ñ�� / ((select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)), 3) * 100
                                         END
                                ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                     as Ψ��             -- 10
                      from
                            hiuuri as u
                      left outer join
                            assembly_schedule as a
                      on u.�ײ��ֹ�=a.plan_no
                      left outer join
                            miitem as m
                      on u.assyno=m.mipn
                      left outer join
                            material_cost_header as mate
                      on u.�ײ��ֹ�=mate.plan_no
                      LEFT OUTER JOIN
                        sales_parts_material_history AS pmate
                      ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
                      left outer join
                            product_support_master AS groupm
                      on u.assyno=groupm.assy_no
                      %s
                      order by �׾���, assyno
                      ", $search);   // ���� $search �Ǹ���
}
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // ľ���θƽи������
    exit();
} else {
    $num_csv = count($field_csv);       // �ե�����ɿ�����
    for ($r=0; $r<$rows_csv; $r++) {
        //$res_csv[$r][4] = mb_convert_kana($res_csv[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        $res_csv[$r][4] = str_replace(',',' ',$res_csv[$r][4]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][1] = mb_convert_encoding($res_csv[$r][1], 'SJIS', 'EUC');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
        $res_csv[$r][4] = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
    }
    //$_SESSION['SALES_TEST'] = sprintf("order by �׾��� offset %d limit %d", $offset, PAGE);
    $i = 1;                             // CSV�񤭽Ф��ѥ�����ȡʥե������̾��0������Τǣ������
    $csv_data = array();                // CSV�񤭽Ф�������
    for ($s=0; $s<$num_csv; $s++) {     // �ե������̾��CSV�񤭽Ф�������˽���
        $field_csv[$s]   = mb_convert_encoding($field_csv[$s], 'SJIS', 'auto');
        $csv_data[0][$s] = $field_csv[$s];
    }
    for ($r=0; $r<$rows_csv; $r++) {    // �ǡ�����CSV�񤭽Ф�������˽���
        for ($s=0; $s<$num_csv; $s++) {
            $csv_data[$i][$s]  = $res_csv[$r][$s];
        }
        $i++;
    }
}

// �������餬CSV�ե�����κ����ʰ���ե�����򥵡��С��˺�����
//$outputFile = 'csv/' . $d_start . '-' . $d_end . '.csv';
//$outputFile = 'csv/' . $d_start . '-' . $d_end . '-' . $act_name . '.csv';
//$outputFile = "test.csv";
touch($outputFile);
$fp = fopen($outputFile, "w");

foreach($csv_data as $line){
    fputcsv($fp,$line);         // ������CSV�ե�����˽񤭽Ф�
}
fclose($fp);
//$outputFile = $d_start . '-' . $d_end . '.csv';
//$outputFile = $d_start . '-' . $d_end . '-' . $act_name . '.csv';

// �������餬CSV�ե�����Υ�������ɡʥ����С������饤����ȡ�
touch($outputFile);
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=".$outputFile);
header("Content-Length:".filesize($outputFile));
readfile($outputFile);
unlink("{$outputFile}");         // ��������ɸ�ե��������
?>