<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���˥塼 ͭ��5�������б� ͭ�������Ģ PDF����(����) FPDF/MBFPDF����  //
// Copyright (C) 2019-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2019/06/13 Created  print_yukyu_five_list.php  �����å���                //
// 2019/07/25 ���֤�ɬ��������ɽ�����ɲ�                                    //
// 2019/09/13 �����̽��Ϥ��ɲ�                                              //
// 2021/03/30 ������κǽ�����׻����Ƥ����������Ǥ���Τ��ѹ�              //
//////////////////////////////////////////////////////////////////////////////
ini_set('memory_limit', '100M');             // PDF�����̽��ϤΤ��� 52M��OK���� 64M��
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');   // access_log()��Ȥ�����define��function������
// require_once ('/home/www/html/tnk-web/define.php');
access_log();                           // Script Name �ϼ�ư����

//////////////// ǧ�ڥ����å�
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
/*
if (!getCheckAuthority(58)) {        // ���¥�٥뤬���ʲ��ϵ���(���桼�����Τ�)
// if (account_group_check() == FALSE) {        // ����Υ��롼�װʳ��ϵ���
    $_SESSION['s_sysmsg'] = 'ͭ�������Ģ��������븢�¤�����ޤ���';
    header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
    // header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}
*/
////////// ��Ģ�о�ǯ�٤μ���
if (isset($_POST['yukyulist'])) {
    $list_year = $_POST['yukyulist'];
    $_SESSION['yukyulist'] = $_POST['yukyulist'];
} elseif (isset($_GET['yukyulist'])) {
    $list_year = $_GET['yukyulist'];
    $_SESSION['yukyulist'] = $_GET['yukyulist'];
    // $_SESSION['s_sysmsg'] = $_GET['emp_name'];   // Debug��
} else {
    $list_year = '';
    $_SESSION['yukyulist'] = $list_year;
}

if (preg_match("/^[0-9]+$/",$list_year)) {
    if (mb_strlen($list_year) != 4) {
        $_SESSION['s_sysmsg'] = 'ǯ�٤����񣴷�����Ϥ��Ʋ�������';
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
        // header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = 'ǯ�٤�Ⱦ�ѿ��������Ϥ��Ƥ���������';
    header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
    // header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}
////////// �о�����μ���
if (isset($_POST['fivesection'])) {
    $fivesection = $_POST['fivesection'];
    $_SESSION['fivesection'] = $_POST['fivesection'];
} elseif (isset($_GET['fivesection'])) {
    $fivesection = $_GET['fivesection'];
    $_SESSION['fivesection'] = $_GET['fivesection'];
    // $_SESSION['s_sysmsg'] = $_GET['emp_name'];   // Debug��
} else {
    $fivesection = '-1';
    $_SESSION['fivesection'] = $fivesection;
}
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸

///// MBFPDF/FPDF �ǻ��Ѥ����ȹ��ե����
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');     // Core Font �Υѥ�
///// ���ܸ�ɽ���ξ��ɬ�ܡ����ʤ����ɬ�����󥯥롼�ɤ���
require_once ('/home/www/html/mbfpdf/mbfpdf.php');          // �ޥ���Х���FPDF

class PDF_j extends MBFPDF  // ���ܸ�PDF���饹���ĥ���ޤ���
{
    // Private properties
    var $wh_usr;     // Header Column Text
    var $w_usr;      // Header Column Width
    var $data_usr;   // Header �� �桼�����ǡ���
    var $usr_cnt;    // Header �� �桼��������
    
    /// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
    function PDF_j()
    {
        // $this->FPDF();  // ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ���
        parent::FPDF_Protection();
        $this->wh_usr   = array();
        $this->w_usr    = array();
        $this->usr_cnt  = 1;    // ������ɽ����
        $this->data_usr = array('', '', '', '');    // �ƥ����ѤΥ桼������Ȳ񤹤�ȥ�˥󥰤ˤʤ뤿���ɲ�
    }
    
    // Simple table...̤����
    function BasicTable($header, $data)
    {
        //Header
        foreach ($header as $col) {
            $this->Cell(30, 7, $col, 1);
        }
        $this->Ln();
        //Data
        foreach ($data as $row) {
            foreach ($row as $col) {
                $this->Cell(30, 7, $col, 1);
            }
            $this->Ln();
        }
    }
    
    // Better table...̤����
    function ImprovedTable($header, $data)
    {
        // Column widths �ץ�ѥƥ����ѹ�
        // $w = array(25, 15, 24, 105, 30);   //�ƥ���β�������ꤷ�Ƥ��ޤ���
        // Header
        for ($i=0; $i<count($header); $i++) {
            $this->Cell($this->w_usr[$i], 7, $header[$i], 1, 0, 'C');
        }
        $this->Ln();
        // Data
        foreach ($data as $row) {
            $this->Cell($this->w_usr[0], 6, $row[0], 'LR');
            $this->Cell($this->w_usr[1], 6, $row[1], 'LR');
            $this->Cell($this->w_usr[2], 6, $row[2], 'LR');
            $this->Cell($this->w_usr[3], 6, $row[3], 'LR');
            $this->Cell($this->w_usr[4], 6, $row[4], 'LR');
            $this->Ln();
        }
        // Closure line
        $this->Cell(array_sum($w), 0, '', 'T');
    }
    
    //���Υ��дؿ��������Ƥ��ޤ���
    // Colored table
    function FancyTable($data, $caption)
    {
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('');
        // Header Column �ץ�ѥƥ����ѹ�
        // $w = array(25, 15, 24, 105, 30);   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        // Data
        $this->SetFont(GOTHIC, 'B', 10);
        //$this->Cell($this->w_usr[0], 6, '', 'LTB', 0, 'L', 1);
        //$this->Cell($this->w_usr[1], 6, '', 'TB', 0, 'L', 1);
        //$this->SetTextColor(50, 0, 255);    // ����ץ������������Ѥ���(��)
        //$this->Cell($this->w_usr[2], 6, $caption, 'RTB', 0, 'L', 1);
        $this->SetTextColor(0, 0, 0);
        //$this->Ln();    // ����
        $this->SetFillColor(235);   // ���졼��������⡼��
        $this->SetFont(GOTHIC, '', 9);
        $fill = 0;
        foreach ($data as $row) {
            $this->Cell($this->w_usr[0], 5, $row[0], 'LRTB', 0, 'L', $fill);    // �ʲ����ƥե�����ɤ��Ȥ˽���
            $this->Cell($this->w_usr[1], 5, $row[1], 'LRTB', 0, 'L', $fill);
            $this->Cell($this->w_usr[2], 5, $row[2], 'LRTB', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($this->w_usr), 0, '', 'T');
        $this->Ln();    // ����
    }

    function Header()   //Ƭ�ˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    {
        $this->Image('/home/www/html/tnk-web/img/t_nitto_logo2.png', 155, 5, 50, 0, '', '');  //���᡼�������֤��ޤ���������ꤷ�ޤ�������ե���󥹻���
        $this->SetX(60);
        // Select Arial bold 15
        $this->SetFont(GOTHIC, 'B', 16);
        // Move to the right
        /// $this->Cell(80);
        // Framed title
        $list_title = $this->list_year . 'ǯ��ͭ�������Ģ';
        $this->Cell(80, 10, $list_title, 'TB', 0, 'C');
        $this->Ln(15);
        $this->SetFont(GOTHIC, '', 8);
        $this->SetY(16);
        $this->Cell(0, 0, date('Yǯm��d�� H��iʬs��'), 0, 0, 'R');
        $this->SetY(19);
        $this->Cell(0, 0, '�������칩��������', 0, 0, 'R');
        // $this->SetY(22);
        // $this->Cell(0, 0, '��329-1311 ���ڸ�������Ի��3473-2', 0, 0, 'R');
        // $this->SetY(25);
        // $this->Cell(0, 0, 'Tel:028-682-8851/Fax:028-681-7038', 0, 0, 'R');
        $this->SetFont(GOTHIC, '', 9);
        $this->SetXY(165, 22);
        // Line break
        $this->Ln(3);
        $this->SetFont(GOTHIC, '', 10);
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        // $this->SetX(15);
        
        $this->Cell(20, 7, '�Ұ��ֹ�', 'LRTB', 0, 'C', 1);    // �ʲ����ƥե�����ɤ��Ȥ˽���
        $this->Cell(15, 7, $this->data_usr[0], 'LRTB', 0, 'C', 1);
        $this->Cell(15, 7, '������', 'LRTB', 0, 'C', 1);
        $this->Cell(45, 7, $this->data_usr[1], 'LRTB', 0, 'C', 1);
        $this->Cell(20, 7, '�ᡡ̾', 'LRTB', 0, 'C', 1);
        $this->Cell(35, 7, $this->data_usr[3], 'LRTB', 0, 'C', 1);
        $this->Ln();
        $this->Cell(20, 7, '�����', 'LRTB', 0, 'C', 1);
        $this->Cell(60, 7, $this->data_usr[4], 'LRTB', 0, 'C', 1);
        $this->Cell(20, 7, '��������', 'LRTB', 0, 'C', 1);
        $this->Cell(30, 7, $this->data_usr[5], 'LRTB', 0, 'C', 1);
        $this->Ln(10);
        
        // Colors, line width and bold font
        $this->SetFillColor(128, 128, 128);
        $this->SetTextColor(255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        
        $this->SetFont(GOTHIC, 'B', 12);
        for ($i=0; $i<count($this->wh_usr); $i++) {
            $this->Cell($this->w_usr[$i], 7, $this->wh_usr[$i], 1, 0, 'C', 1);  // �ե������̾�����
        }
        $this->Ln();    // ���򤢤��ޤ�������ե���󥹻��ȤΤ���
    }
    function Footer()   // ���ĤˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    {
        // Go to 1.5 cm from bottom
        // Select Arial italic 8
        $this->SetFont('Times', 'I', 8);
        // Print centered page number
        $this->SetY(-10);    // ������10mm�˥��å�(5mm���ȥץ�󥿡��ˤ�äƤϰ�������ʤ�)
        $this->Cell(0, 10, '('.$this->PageNo().')', 0, 0, 'C');
        $this->Cell(0, 10, 'Copyright TOCHIG NITTO KOHKI Co.,Ltd. All rights reserved', 0, 0, 'R');
    }

}

Header('Pragma: public');   // https�����Ѥ���ݤΤ��ޤ��ʤ��Ǥ���

///////// FPDF
$pdf = new PDF_j();     // ����Ѱդ�����ĥ���饹������

///// PDFʸ��Υץ�ѥƥ�����
$pdf->SetAuthor('�������칩��������');    // Tochigi Nitto Kohki Co.,Ltd.
$pdf->SetCreator("$current_script");
$pdf->SetTitle('Employee name list');
$pdf->SetSubject('Teaching exercise record');
$pdf->SetDisplayMode('fullwidth', 'default');       // �ڡ����Υ쥤������=�裲���������ꤷ�ʤ�����continuous=Ϣ³��������
$pdf->SetCompression(true);         // ���̤�ͭ���ˤ���(default=on)
$pdf->SetProtection(array('print'), '', 'tnkowner');    // �����Τߵ��ĤΥץ�ƥ��� fpdf_protection.php��ɬ��('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32)
$pdf->list_year = $list_year;
$pdf->fivesection = $fivesection;

///// PDFʸ��λ��ѥե���Ȥ�����
$pdf->AddMBFont(GOTHIC ,'SJIS');
// $pdf->AddMBFont(PGOTHIC,'SJIS');
// $pdf->AddMBFont(MINCHO ,'SJIS');
// $pdf->AddMBFont(PMINCHO,'SJIS');
// $pdf->AddMBFont(KOZMIN ,'SJIS');
$pdf->Open();                   // PDF�򳫻�(��ά��ǽ��AddPage()��OK)
$pdf->SetLeftMargin(15.0);      // ���Υޡ�����򣱣�.���ߥ���ѹ�
$pdf->SetRightMargin(5.0);      // ���Υޡ������.���ߥ���ѹ�
$pdf->SetFont(GOTHIC,'',10);    // �ǥե���ȥե���Ȥ�MS�����å� 10�ݥ���Ȥˤ��Ƥ�����
// Header

/////////// PostgreSQL����³
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // ��³Ū��³

/////////// �Ұ��ֹ����μ���SQL
// ��Ĺ�����䡦����¾�����칩������
if ($fivesection == '-1') {
$sql = "SELECT f.uid AS uid
            , trim(section_name) AS section
            , trim(position_name) AS position
            ,trim(name) AS name
            ,f.reference_ym AS reference_ym
            ,f.end_ref_ym AS end_ref_ym
            ,f.need_day AS need_day
        FROM five_yukyu_master AS f LEFT OUTER JOIN user_detailes USING(uid) LEFT OUTER JOIN
            section_master
        USING(sid)
        LEFT OUTER JOIN
            position_master
        USING(pid)
        WHERE business_year={$list_year}
        ORDER BY sid DESC, pid DESC, uid ASC";
} else {
$sql = "SELECT f.uid AS uid
            , trim(section_name) AS section
            , trim(position_name) AS position
            ,trim(name) AS name
            ,f.reference_ym AS reference_ym
            ,f.end_ref_ym AS end_ref_ym
            ,f.need_day AS need_day
        FROM five_yukyu_master AS f LEFT OUTER JOIN user_detailes USING(uid) LEFT OUTER JOIN
            section_master
        USING(sid)
        LEFT OUTER JOIN
            position_master
        USING(pid)
        WHERE business_year={$list_year} and sid={$fivesection}
        ORDER BY sid DESC, pid DESC, uid ASC";
}
if ( !($res_usr = pg_query($con, $sql)) ) {
    $_SESSION['s_sysmsg'] = '�Ұ��ֹ椬�����Ǥ��ޤ���' . pg_last_error($con);
    header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
    exit();
}

            // mysql_fetch_object�Ϥ���ʤ��� pg_fetch_object�Ϲ��ֹ椬����
            // �Ϥ����ä��� �ޥ˥奢����ɤ�������4.1.0�ʹߤϥ��ץ����Ȥʤä���
            // ����Ū�˥쥳���ɥ����󥿡������ä����Ƥ��롣
$data_f = array();  // �����顼�ѿ��ǤϤʤ��ơ�������Ȥ������Ȥ�����
while ($row = pg_fetch_object($res_usr)) {
    $now_uid       = $row->uid;                                     // �Ұ��ֹ�
    $now_section   = mb_substr($row->section, -10);                  // ����(û��)
    $now_position  = mb_substr($row->position, 0, 5);               // ����(û��)
    $now_name      = $row->name;                                    // ��̾
    $r_yy          = substr($row->reference_ym, 0,4);               // �������ǯ
    $r_mm          = substr($row->reference_ym, 4,2);               // ���������
    $r_dd          = substr($row->reference_ym, 6,2);               // ���������
    $now_reference = $r_yy . "ǯ" . $r_mm . "��" . $r_dd . "��";    // ��೫����
    $e_yy          = substr($row->end_ref_ym, 0,4);                 // �������ǯ
    $e_mm          = substr($row->end_ref_ym, 4,2);                 // ���������
    $e_dd          = substr($row->end_ref_ym, 6,2);                 // ���������
    $now_end_ref   = $e_yy . "ǯ" . $e_mm . "��" . $e_dd . "��";    // ��ཪλ��
    $now_need      = $row->need_day . "��";                         // ɬ������
    $end_rmd = $row->reference_ym + 10000;
    //$end_rmd = 20200331;
    
    // Column titles
    $pdf->wh_usr = array('����ǯ����', '��������', '��������');
    $pdf->w_usr  = array(50, 50, 50);   //�ƥ���β�������ꤷ�Ƥ��ޤ���
    /* ������������ SQL */
    $res_w = array();
    $total_num   = 0;       // �����������
    // �ǡ���̵����
    $res_w[0]['s_date'] = '---';   // ������
    $res_w[0]['s_name'] = '---';   // ��������
    $res_w[0]['s_num'] = '---';   // ��������
    $total_num   = '---';       // �����������
    /*
    $query = "SELECT   uid    AS uid --00 
                ,working_date AS working_date   --01
                ,working_day  AS working_day     --02
                ,absence      AS absence --03
                ,str_mc       AS str_mc --04
                ,end_mc       AS end_mc --05
                FROM working_hours_report_data_new 
                WHERE uid='$now_uid' and working_date >= $row->reference_ym and
                working_date < $end_rmd and ( absence = '11' or str_mc = '41' or end_mc = '42' )";
    */
    $query = "SELECT   uid    AS uid --00 
                ,working_date AS working_date   --01
                ,working_day  AS working_day     --02
                ,absence      AS absence --03
                ,str_mc       AS str_mc --04
                ,end_mc       AS end_mc --05
                FROM working_hours_report_data_new 
                WHERE uid='$now_uid' and working_date >= $row->reference_ym and 
                working_date < $row->end_ref_ym and ( absence = '11' or str_mc = '41' or end_mc = '42' )
                ORDER BY working_date
                ";
    if ( !($res_a = pg_query($con, $query)) ) {
        $res_w[0]['s_date'] = '---';   // ������
        $res_w[0]['s_name'] = '---';   // ��������
        $res_w[0]['s_num'] = '---';   // ��������
        $total_num   = 0;       // �����������
        $c_num = count($res_w);
    } else {
        $cnt = 0;
        while ($rows_a = pg_fetch_object($res_a)) {
            if ($rows_a->absence == 11) {
                $w_yy          = substr($rows_a->working_date, 0,4);            // ��������ǯ
                $w_mm          = substr($rows_a->working_date, 4,2);            // ����������
                $w_dd          = substr($rows_a->working_date, 6,2);            // ����������
                $w_view_date   = $w_yy . "ǯ" . $w_mm . "��" . $w_dd . "��";    // ��������ǯ����
                $res_w[$cnt]['s_date'] = $w_view_date; // ������
                $res_w[$cnt]['s_name'] = 'ͭ��';      // ��������
                $res_w[$cnt]['s_num']  = 1;           // ��������
                $total_num += 1;
            } elseif ($rows_a->str_mc == 41) {
                $w_yy          = substr($rows_a->working_date, 0,4);            // ��������ǯ
                $w_mm          = substr($rows_a->working_date, 4,2);            // ����������
                $w_dd          = substr($rows_a->working_date, 6,2);            // ����������
                $w_view_date   = $w_yy . "ǯ" . $w_mm . "��" . $w_dd . "��";    // ��������ǯ����
                $res_w[$cnt]['s_date'] = $w_view_date; // ������
                $res_w[$cnt]['s_name'] = 'ȾAM';      // ��������
                $res_w[$cnt]['s_num']  = 0.5;           // ��������
                $total_num += 0.5;
            } elseif ($rows_a->end_mc == 42) {
                $w_yy          = substr($rows_a->working_date, 0,4);            // ��������ǯ
                $w_mm          = substr($rows_a->working_date, 4,2);            // ����������
                $w_dd          = substr($rows_a->working_date, 6,2);            // ����������
                $w_view_date   = $w_yy . "ǯ" . $w_mm . "��" . $w_dd . "��";    // ��������ǯ����
                $res_w[$cnt]['s_date'] = $w_view_date; // ������
                $res_w[$cnt]['s_name'] = 'ȾPM';      // ��������
                $res_w[$cnt]['s_num']  = 0.5;           // ��������
                $total_num += 0.5;
            } else {
                $res_w[$cnt]['s_date'] = '---'; // ������
                $res_w[$cnt]['s_name'] = '---'; // ��������
                $res_w[$cnt]['s_num']  = '---'; // ��������
                $total_num = 0;
            }
            /*
            if ($res[$t][3] == 11) {
                $res_w[$t]['s_date'] = '---';   // ������
                $res_w[$t]['s_name'] = '---';   // ��������
                $res_w[$t]['s_num'] = '---';   // ��������
                $total_num += 1;
            } elseif ($res[$t][4] == 41) {
                $res_w[$t]['s_date'] = '---';   // ������
                $res_w[$t]['s_name'] = '---';   // ��������
                $res_w[$t]['s_num'] = '---';   // ��������
                $total_num += 0.5;
            } elseif ($res[$t][5] == 42) {
                $res_w[$t]['s_date'] = '---';   // ������
                $res_w[$t]['s_name'] = '---';   // ��������
                $res_w[$t]['s_num'] = '---';   // ��������
                $total_num += 0.5;
            } else {
                $res_w[$t]['s_date'] = '---';   // ������
                $res_w[$t]['s_name'] = '---';   // ��������
                $res_w[$t]['s_num'] = '---';   // ��������
            }
            */
            $cnt ++;
        }
        $c_num = count($res_w);
    }
    
    ///// ���� �Ұ��ֹ桦�����򿦡���̾ ����ʸ��θ��Ф�
    // ��������ɬ�������η������Ѵ�
    $total_num = $total_num . "��";
    $total_num = $total_num . "��" . $now_need;
    // ��೫��������ཪλ���η������Ѵ�
    $now_reference = $now_reference . "��" . $now_end_ref;
    $pdf->data_usr = array($now_uid, $now_section, $now_position, $now_name, $now_reference, $total_num);
    
    $cnt = 0;   // �ǡ����ιԥ�����
    $data_f = array();
    for ($r=0; $r<$c_num; $r++) {
        $s_date = $res_w[$r]['s_date'];
        $s_name = $res_w[$r]['s_name'];
        $s_num  = $res_w[$r]['s_num'];
        $data_f[$cnt] = array($s_date, $s_name, $s_num);
        $cnt++;
    }
    $pdf->AddPage();    // �ڡ���������������1��ϥ����뤹��ɬ�פ�����(�դ�$pdf->Open()�Ͼ�ά��ǽ)
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// ���� ��ʸ
    $pdf->FancyTable($data_f, '������');  // ��ǥ������ष�����дؿ���ƤӽФ�
    
}

$pdf->Output();     // �Ǹ�ˡ��嵭�ǡ�������Ϥ��ޤ���
exit;               // �ʤ�٤������뤹�롣�ޤ����Ǹ��PHP���å��˲��Ԥʤɤ��ޤޤ��ȥ���
?> 