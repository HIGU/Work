<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ϲ�������ĥꥹ�� CSV����                                             //
// Copyright (C) 2011-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/05/20 Created   vendor_order_csv.php                                //
// 2103/10/12 ���Ϲ���̾�����κݤ�;�פʥ��ڡ�������                      //
// 2015/10/19 ���ʥ��롼�פ�T=�ġ�����ɲá�����No.��ʸ���ܤ�T��            //
//            ���ɾ�������ˤ�ꡢL�����T��������ʤ�(T���ʤ�L������)      //
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

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// �꥿���󥢥ɥ쥹����
$menu->set_RetUrl('/industry/vendor/vendor_order_list_form.php');             // �̾�ϻ��ꤹ��ɬ�פϤʤ�

// �ե�����̾��SQL�Υ���������������
//$outputFile = $_GET['csvname'];
//$csv_search = $_GET['csvsearch'];
// SQL�Υ��������ǰ���ѹ�������ʬ�򸵤��᤹
//$search     = str_replace('keidate','�׾���',$csv_search);
//$search     = str_replace('jigyou','������',$search);
//$search     = str_replace('denban','��ɼ�ֹ�',$search);
//$search     = str_replace('/','\'',$search);
// ����������ʸ�������ɤ�EUC���ѹ������ǰ�Τ����
//$search     = mb_convert_encoding($search, 'EUC-JP', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

// �ե�����̾�ǰ���ѹ�������ʬ�򸵤��᤹
//$outputFile     = str_replace('ALL','����',$outputFile);
//$outputFile     = str_replace('C-all','���ץ�����',$outputFile);
//$outputFile     = str_replace('C-hyou','���ץ�ɸ��',$outputFile);
//$outputFile     = str_replace('L-all','��˥�����',$outputFile);
//$outputFile     = str_replace('L-hyou','��˥��Τ�',$outputFile);
//$outputFile     = str_replace('L-bimor','�Х����',$outputFile);
//$outputFile     = str_replace('C-shuri','���ץ�',$outputFile);
//$outputFile     = str_replace('L-shuri','��˥��',$outputFile);
//$outputFile     = str_replace('NKB','���ʴ���',$outputFile);
//$outputFile     = str_replace('TOOL','�ġ���',$outputFile);
//$outputFile     = str_replace('NONE','�ʤ�',$outputFile);
//$outputFile     = str_replace('SHISAKU','���',$outputFile);
//$outputFile     = str_replace('NONE','�ʤ�',$outputFile);


if (isset($_REQUEST['vendor'])) {
    $vendor = $_REQUEST['vendor'];
} else {
    $vendor = '00485';                           // Default(����)���ꤨ�ʤ���
    // $view = 'NG';
}
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];
} else {
    $div = 'C';                              // Default(����)
}
if (isset($_REQUEST['plan_cond'])) {
    $plan_cond = $_REQUEST['plan_cond'];
} else {
    $plan_cond = '';                        // Default(����)
}
//////// ���Ϲ���̾�μ���
$query = "select trim(name) from vendor_master where vendor='{$vendor}'";
if (getUniResult($query, $vendor_name) < 1) {
    $_SESSION['s_sysmsg'] = "ȯ���襳���ɤ�̵���Ǥ���";
    $vendor_name = '̤��Ͽ';
    $view = 'NG';
}

//////// ɽ�������
//if ($div == '') $div_name = '����'; else $div_name = $div;
//if ($plan_cond == '') $cond_name = '����'; else $cond_name = $plan_cond;
//$menu->set_caption("�����ɡ�{$vendor}���٥����̾��{$vendor_name}�����ʥ��롼�ס�{$div_name}��ȯ���ʬ��{$cond_name}");

////////// ���դǶ��̤� where�������
// ����200��������153(������)��184��(������)��ޤǢ�200�����ѹ�
//$where_date = 'proc.delivery <= ' . date('Ymd', mktime() + (86400*200)) . ' and proc.delivery >= ' . date('Ymd', mktime() - (86400*200));
$where_date = 'proc.delivery <= ' . date('Ymd', time() + (86400*200)) . ' and proc.delivery >= ' . date('Ymd', time() - (86400*200));

//////// ���������鶦�̤� where�������
switch ($div) {
case 'C':       // C����
    $div_name  = "���ץ�";
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and proc.locate != '52   '";
    break;
case 'SC':      // C����
    $div_name  = "C����";
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and plan.kouji_no like '%SC%' and proc.locate != '52   '";
    break;
case 'CS':      // Cɸ��
    $div_name  = "Cɸ��";
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and plan.kouji_no not like '%SC%' and proc.locate != '52   '";
    break;
case 'L':       // L����
    $div_name  = "��˥�";
    $where_div = "proc.vendor='{$vendor}' and plan.div='L' and proc.locate != '52   '";
    break;
case 'T':       // T����
    $div_name  = "T����";
    $where_div = "proc.vendor='{$vendor}' and proc.parts_no like 'T%' and proc.locate != '52   '";
    break;
case 'F':       // F����
    $div_name  = "F����";
    $where_div = "proc.vendor='{$vendor}' and plan.div='F' and proc.locate != '52   '";
    break;
case 'A':       // TNK����
    $div_name  = "TNK����";
    $where_div = "(proc.vendor='{$vendor}' and plan.div='C' or plan.div='L' or plan.div='T' or plan.div='F') and proc.locate != '52   '";
    break;
case 'N':       // NK���ץ�
    $div_name  = "NK���ץ�";
    $where_div = "(proc.vendor='{$vendor}' and plan.div='C' or plan.div='L' or plan.div='T' or plan.div='F') and proc.locate = '52   '";
    break;
default:        // �����ʥ��롼�� '' ' ' �ΰ㤤�����ä����� default ���ѹ�
    $div_name  = "����";
    $where_div = "proc.vendor='{$vendor}' and proc.locate != '52   '";
    break;
}
//////// ȯ��ײ��ʬ���鶦�̤� where�������
switch ($plan_cond) {
case 'P':       // ͽ��
case 'R':       // �⼨��(��꡼��)
case 'O':       // ��ʸ��ȯ�ԺѤ�
    $where_cond = "proc.plan_cond='{$plan_cond}'";
    break;
default:
    $where_cond = "proc.plan_cond != '{$plan_cond}'";
    break;
}

switch ($plan_cond) {
case 'P':       // ͽ��
    $cond_name  = "ͽ��";
    break;
case 'R':       // �⼨��(��꡼��)
    $cond_name  = "�⼨��";
    break;
case 'O':       // ��ʸ��ȯ�ԺѤ�
    $cond_name  = "��ʸ��ȯ�Ժ�";
    break;
default:
    $cond_name  = "����";
    break;
}
// �ե�����̾��SQL�Υ���������������
$vendor_name = trim($vendor_name);
$vendor_name = rtrim($vendor_name, "��");
$outputFile = "��ĥꥹ��-" . $vendor_name . "-" . $div_name . "-" . $cond_name . ".csv";

// �¹ԼԤΥѥ������CSV����¸����١��ե�����̾��ʸ�������ɤ�SJIS���Ѵ�
$outputFile = mb_convert_encoding($outputFile, 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�

////////// ����SQLʸ������
$query_csv = "select    
                    substr(to_char(proc.delivery, 'FM9999/99/99'), 6, 5)
                                                            AS Ǽ��
                  , to_char(proc.sei_no,'FM0000000')        AS ��¤�ֹ�
                  , proc.parts_no                           AS �����ֹ�
                  , trim(item.midsc)                         AS ����̾
                  , CASE
                          WHEN trim(item.mzist) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                          ELSE item.mzist
                    END                                     AS ���
                  , CASE
                          WHEN trim(item.mepnt) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                          ELSE item.mepnt
                    END                                     AS �Ƶ���
                  , CASE
                        WHEN proc.order_q = 0 THEN trim(to_char((plan.order_q - plan.utikiri - plan.nyuko), '9,999,999'))
                        ELSE trim(to_char((proc.order_q - proc.siharai - proc.cut_siharai), '9,999,999'))
                    END                                     AS ��Ŀ�
                  , (select CASE
                                WHEN (sum(uke_q)-sum(siharai)) IS NULL THEN '0'
                                ELSE trim(to_char(sum(uke_q)-sum(siharai), '9,999,999'))    --����
                            END
                        from
                            order_data
                        where sei_no=proc.sei_no and order_no=proc.order_no and vendor=proc.vendor and ken_date<=0
                    )                                       AS ������
                  , proc.pro_mark                           AS ����
                  , CASE
                        WHEN proc.plan_cond = 'P' THEN 'ͽ����'
                        WHEN proc.plan_cond = 'O' THEN '��ʸ��'
                        WHEN proc.plan_cond = 'R' THEN '�⼨��'
                        ELSE proc.plan_cond
                    END                                     AS ȯ��ײ��ʬ
                  , CASE
                        WHEN proc.next_pro != 'END..' THEN
                            (select name from vendor_master where vendor=proc.next_pro limit 1)
                        ELSE proc.next_pro
                    END                                     AS ������̾
            from
                order_process   AS proc
            left outer join
                order_plan      AS plan
                                        using(sei_no)
            left outer join
                vendor_master   AS mast
                                        on(proc.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(proc.parts_no = item.mipn)
            where
                {$where_date}
                and
                {$where_div}
                and
                (plan.order_q - plan.utikiri - plan.nyuko) > 0
                    -- �إå�������Ĥ�����ʪ��
                and
                ( (proc.order_q = 0) OR ((proc.order_q - proc.siharai - proc.cut_siharai > 0)) )
                    -- ���������� ���ϼ�ʬ�ι�������Ĥ�����ʪ
                and
                {$where_cond}
            order by ȯ��ײ��ʬ ASC, proc.delivery ASC, proc.parts_no ASC
            offset 0
            limit 1000
";

$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    $_SESSION['s_sysmsg'] .= '��ĥǡ���������ޤ���';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // ľ���θƽи������
    exit();
} else {
    $num_csv = count($field_csv);       // �ե�����ɿ�����
    for ($r=0; $r<$rows_csv; $r++) {
        //$res_csv[$r][4] = mb_convert_kana($res_csv[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        $res_csv[$r][3]  = str_replace(',',' ',$res_csv[$r][3]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][4]  = str_replace(',',' ',$res_csv[$r][4]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][5]  = str_replace(',',' ',$res_csv[$r][5]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][8]  = str_replace(',',' ',$res_csv[$r][8]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][9]  = str_replace(',',' ',$res_csv[$r][9]);                   // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        $res_csv[$r][10] = str_replace(',',' ',$res_csv[$r][10]);                  // ����̾��,�����äƤ����CSV�Ƿ夬�����Τ�Ⱦ�ѥ��ڡ�����
        //$res_csv[$r][4] = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'EUC');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�(EUC���ꤸ��ʤ���ľǼ��Ĵ����������)
        $res_csv[$r][3]  = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        $res_csv[$r][4]  = mb_convert_encoding($res_csv[$r][4], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        $res_csv[$r][5]  = mb_convert_encoding($res_csv[$r][5], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        $res_csv[$r][8]  = mb_convert_encoding($res_csv[$r][8], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        $res_csv[$r][9]  = mb_convert_encoding($res_csv[$r][9], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
        $res_csv[$r][10] = mb_convert_encoding($res_csv[$r][10], 'SJIS', 'auto');  // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
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