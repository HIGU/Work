<?php
//////////////////////////////////////////////////////////////////////////////////
// �»�״ط� �����ǿ���� ���꿽�����ɽ1-(2) PDF����(����) FPDF/MBFPDF����  //
// Copyright (C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp        //
// Changed history                                                              //
// 2019/06/13 Created  sales_tax_kakutei_fuhyo1-1_pdf  �����å���               //
//////////////////////////////////////////////////////////////////////////////////
ini_set('memory_limit', '100M');             // PDF�����̽��ϤΤ��� 52M��OK���� 64M��
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');   // access_log()��Ȥ�����define��function������
require_once ('/home/www/html/tnk-web/tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('/home/www/html/tnk-web/MenuHeader.php');         // TNK ������ menu class
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
///// �о�����
$ki2_ym   = $_SESSION['2ki_ym'];
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);

///// ������ ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // ������ǯ��

///// ����Ⱦ���μ���
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $hanki = '��';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $hanki = '��';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $hanki = '��';
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $hanki = '��';
}

///// ǯ���ϰϤμ���
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
}
///// TNK�� �� NK�����Ѵ�
$nk_ki   = $ki + 44;
$nk_p1ki = $p1_ki + 44;

$cost_ym = array();
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $hanki = '��';
    $yyyy_tou = $yyyy + 1;
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cost_ym[9]  = $yyyy_tou . '01';
    $cost_ym[10] = $yyyy_tou . '02';
    $cost_ym[11] = $yyyy_tou . '03';
    $cnum        = 12;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $hanki = '��';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cnum        = 3;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $hanki = '��';
    $cost_ym[0] = $yyyy . '04';
    $cost_ym[1] = $yyyy . '05';
    $cost_ym[2] = $yyyy . '06';
    $cost_ym[3] = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cnum        = 6;
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $hanki = '��';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cnum        = 9;
}

// ���4��ʬ
$cost_ym_next = $yyyy + 1 . '04';

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
        $this->SetFont(GOTHIC, 'B', 12);
        //$this->Cell($this->w_usr[0], 6, '', 'LTB', 0, 'L', 1);
        //$this->Cell(120, 6, $caption, 'RTB', 0, 'L', 1);
        $this->SetTextColor(50, 0, 255);    // ����ץ������������Ѥ���(��)
        //$this->Cell($this->w_usr[2], 5, '', 'TB', 0, 'L', 1);
        //$this->Cell($this->w_usr[3], 5, '', 'RTB', 0, 'L', 1);
        $this->SetTextColor(0, 0, 0);
        //$this->Ln();    // ����
        $this->SetFillColor(235);   // ���졼��������⡼��
        $this->SetFont(GOTHIC, '', 12);
        $fill = 0;
        foreach ($data as $row) {
                $this->SetFont(GOTHIC, '', 8);
                $this->Cell($this->w_usr[0], 6, $row[0], 'LRTB', 0, 'C', $fill);    // �ʲ����ƥե�����ɤ��Ȥ˽���
                $this->Cell($this->w_usr[1], 6, $row[1], 'LRTB', 0, 'L', $fill);
                $this->Cell($this->w_usr[2], 6, $row[2], 'LRTB', 0, 'C', $fill);
                $this->SetFont(GOTHIC, '', 10);
                $this->Cell($this->w_usr[3], 6, $row[3], 'LRTB', 0, 'R', $fill);
                $this->Cell($this->w_usr[4], 6, $row[4], 'LRTB', 0, 'R', $fill);
                $this->Cell($this->w_usr[5], 6, $row[5], 'LRTB', 0, 'R', $fill);
                $this->Cell($this->w_usr[6], 6, $row[6], 'LRTB', 0, 'R', $fill);
                $this->Ln();
                $fill = !$fill;
        }
        $this->Cell(array_sum($this->w_usr), 0, '', 'T');
        $this->Ln();    // ����
    }

    function Header()   //Ƭ�ˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    {
        /*
        $this->Image('/home/www/html/tnk-web/img/t_nitto_logo2.png', 155, 5, 50, 0, '', '');  //���᡼�������֤��ޤ���������ꤷ�ޤ�������ե���󥹻���
        */
        $this->SetX(60);
        // Select Arial bold 15
        $this->SetFont(GOTHIC, 'B', 12);
        // Move to the right
        /// $this->Cell(80);
        // Framed title
        $list_title = '��ɽ��-�� ��Ψ�̾����ǳ۷׻�ɽ';
        $this->Cell(80, 10, $list_title, 'TB', 0, 'C');
        $this->Ln();    // ����
        $list_title2 = ' �����������Ǥβ���ɸ��Ȥʤ�����ǳ۷׻�ɽ�̷в������оݲ��ǻ񻺤ξ��Ϥ�ޤ���Ǵ����ѡ�';
        $this->Cell(80, 10, $list_title2, 'TB', 0, 'L');
        /*
        $this->Ln(80);
        $this->SetFont(GOTHIC, '', 8);
        $this->SetY(16);
        $this->Cell(0, 0, date('Yǯm��d�� H��iʬs��'), 0, 0, 'R');
        $this->SetY(19);
        $this->Cell(0, 0, '�������칩��������', 0, 0, 'R');
        */
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
        
        $this->SetY(19);
        $this->Cell(0, 0, '��ñ�̡��ߡ�', 0, 0, 'R');
        $this->Ln();
        $this->Ln(10);
        
        // Colors, line width and bold font
        $this->SetFillColor(128, 128, 128);
        $this->SetTextColor(255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        
        $this->SetFont(GOTHIC, 'B', 8);
        for ($i=0; $i<count($this->wh_usr); $i++) {
            $this->Cell($this->w_usr[$i], 7, $this->wh_usr[$i], 1, 0, 'C', 1);  // �ե������̾�����
        }
        $this->Ln();    // ���򤢤��ޤ�������ե���󥹻��ȤΤ���
    }
    function Footer()   // ���ĤˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    {
        // Go to 1.5 cm from bottom
        // Select Arial italic 8
        /*
        $this->SetFont('Times', 'I', 8);
        // Print centered page number
        $this->SetY(-10);    // ������10mm�˥��å�(5mm���ȥץ�󥿡��ˤ�äƤϰ�������ʤ�)
        $this->Cell(0, 10, '('.$this->PageNo().')', 0, 0, 'C');
        $this->Cell(0, 10, 'Copyright TOCHIG NITTO KOHKI Co.,Ltd. All rights reserved', 0, 0, 'R');
        */
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

            // mysql_fetch_object�Ϥ���ʤ��� pg_fetch_object�Ϲ��ֹ椬����
            // �Ϥ����ä��� �ޥ˥奢����ɤ�������4.1.0�ʹߤϥ��ץ����Ȥʤä���
            // ����Ū�˥쥳���ɥ����󥿡������ä����Ƥ��롣
$data_f = array();  // �����顼�ѿ��ǤϤʤ��ơ�������Ȥ������Ȥ�����
    // Column titles
    $pdf->wh_usr = array('��', '', 'ʬ', '��Ψ3��Ŭ��ʬ��', '��Ψ4��Ŭ��ʬ��', '��Ψ6.3��Ŭ��ʬ��', '��� �ءʣ�+��+�á�');
    $pdf->w_usr  = array(15, 65, 7, 20, 30, 26, 28);   //�ƥ���β�������ꤷ�Ƥ��ޤ���
    /* ������������ SQL */
    $res_w = array();
    ///// ���� �Ұ��ֹ桦�����򿦡���̾ ����ʸ��θ��Ф�
    // ��������ɬ�������η������Ѵ�
    // ��೫��������ཪλ���η������Ѵ�
    //$pdf->data_usr = array($now_uid, $now_section, $now_position, $now_name, $now_reference, $total_num);
    
    $s_1 = '';
    $s_2 = '����ɸ���';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '';
    $data_f[0] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '����';
    $s_2 = '���ǻ񻺤ξ��������в��γ�';
    $s_3 = '��-1';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '';
    $data_f[1] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '����';
    $s_2 = '������ǻ�����˷����ʧ�в��γ�';
    $s_3 = '��-2';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '';
    $data_f[2] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '�����ǳ�';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '';
    $data_f[3] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '��������Ĵ���ǳ�';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '';
    $data_f[4] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '�����ǳ�';
    $s_2 = '�����оݻ����ǳ�';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '1,214,517';
    $s_7 = '1,214,517';
    $data_f[5] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '�����ǳ�';
    $s_2 = '�ִ����в��˷����ǳ�';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[6] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '�����ǳ�';
    $s_2 = '�������� ��夲���ִ����в��˷����ǳ�';
    $s_3 = '��-1';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[7] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '�����ǳ�';
    $s_2 = '�������� ������ǻ�������ִ����в��˷����ǳ�';
    $s_3 = '��-2';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[8] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '�����ǳ�';
    $s_2 = '���ݤ�˷����ǳ�';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[9] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '�����ǳ�';
    $s_2 = '�����ǳ۾��סʭ�+��+����';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '1,214,517';
    $s_7 = '1,214,517';
    $data_f[10] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '������­�����ǳۡʭ�-��-����';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '1,214,517';
    $s_7 = '1,214,517';
    $data_f[11] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '�����ǳۡʭ�+��-����';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[12] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '��׺����ǳۡʭ�-����';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '';
    $data_f[13] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '���������Ǥβ���';
    $s_2 = '������­�����ǳ�';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '1,214,517';
    $s_7 = '1,214,517';
    $data_f[14] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = 'ɸ��Ȥʤ�����ǳ�';
    $s_2 = '�����ǳ�';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '';
    $data_f[15] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '��׺������������Ǥβ���ɸ��Ȥʤ�����ǳۡʭ�-����';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '-1,214,517';
    $s_7 = '-1,214,517';
    $data_f[16] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '����';
    $s_2 = '���ճ�';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '327,726';
    $s_7 = '327,726';
    $data_f[17] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '���';
    $s_2 = 'Ǽ�ǳ�';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[18] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '��׺������ϳ�ۡʭ�-����';
    $s_3 = '��';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '';
    $data_f[19] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $pdf->AddPage();    // �ڡ���������������1��ϥ����뤹��ɬ�פ�����(�դ�$pdf->Open()�Ͼ�ά��ǽ)
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// ���� ��ʸ
    $pdf->FancyTable($data_f, '���ο����ˤ������Ǥ��ǳۤη׻�');  // ��ǥ������ष�����дؿ���ƤӽФ�
    

$pdf->Output();     // �Ǹ�ˡ��嵭�ǡ�������Ϥ��ޤ���
exit;               // �ʤ�٤������뤹�롣�ޤ����Ǹ��PHP���å��˲��Ԥʤɤ��ޤޤ��ȥ���
?> 