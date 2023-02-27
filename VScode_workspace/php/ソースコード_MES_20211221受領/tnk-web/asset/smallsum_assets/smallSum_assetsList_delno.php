<?php
//////////////////////////////////////////////////////////////////////////////
// ���ۻ���Ģ�� �Уģƽ���(����) FPDF/MBFPDF ����                         //
// Copyright (C) 2010 - 2012 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2010/10/19 Created  smallSum_assetsList_delno.php  �����å���            //
// 2012/02/14 ʸ�����Ϥ߽Ф��Ƥ��ޤ���SQL��ʸ�������¤��ɲ�                 //
// 2012/03/02 ��ǧ�β�������ѹ�                                            //
// 2012/10/03 ǰ�Τ���᡼���������������ͤ�ʸ�������¤��ɲ�                //
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
//if ($_SESSION['Auth'] <= 1) {        // ���¥�٥뤬���ʲ��ϵ���(���桼�����Τ�)
// if (account_group_check() == FALSE) {        // ����Υ��롼�װʳ��ϵ���
//    if ($_SESSION['User_ID'] != '970268') {
//        $_SESSION['s_sysmsg'] = '�Ұ�̾���������븢�¤�����ޤ���';
//        header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
//        // header("Location: $url_referer");                   // ľ���θƽи������
//        exit();
//    }
//}
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
    
    /// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
    function PDF_j()
    {
        // $this->FPDF();  // ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ���
        parent::FPDF_Protection();
        $this->wh_usr = array();
        $this->w_usr  = array();
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
        //$this->Cell($this->w_usr[1], 6, '', 'TB', 0, 'L', 1);
        //$this->SetTextColor(50, 0, 255);    // ����ץ������������Ѥ���(��)
        //$this->Cell($this->w_usr[2], 6, $caption, 'RTB', 0, 'L', 1);
        $this->SetTextColor(0, 0, 0);
        //$this->Ln();    // ����
        $this->SetFillColor(235);   // ���졼��������⡼��
        $this->SetFont(GOTHIC, '', 12);
        $fill = 0;
        foreach ($data as $row) {
            $this->SetX(5);
            $this->Cell($this->w_usr[0], 7, $row[0], 'LRTB', 0, 'L', $fill);    // �ʲ����ƥե�����ɤ��Ȥ˽���
            $this->Cell($this->w_usr[1], 7, $row[1], 'LRTB', 0, 'L', $fill);
            $this->Cell($this->w_usr[2], 7, $row[2], 'LRTB', 0, 'L', $fill);
            $this->Cell($this->w_usr[3], 7, $row[3], 'LRTB', 0, 'C', $fill);
            $this->Cell($this->w_usr[4], 7, $row[4], 'LRTB', 0, 'R', $fill);
            $this->Cell($this->w_usr[5], 7, $row[5], 'LRTB', 0, 'L', $fill);
            $this->Cell($this->w_usr[6], 7, "��", 'LRTB', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->SetX(5);
        $this->Cell(array_sum($this->w_usr), 0, '', 'T');
        $this->Ln();    // ����
    }

    function Header()   //Ƭ�ˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    {
        $this->Image('/home/www/html/tnk-web/img/t_nitto_logo2.png', 235, 14, 50, 0, '', 'R');  //���᡼�������֤��ޤ���������ꤷ�ޤ�������ե���󥹻���
        $this->SetX(60);
        // Select Arial bold 15
        $this->SetFont(GOTHIC, 'B', 16);
        // Move to the right
        /// $this->Cell(80);
        // Framed title
        $this->SetY(16);
        $this->SetX(100);
        $this->Cell(100, 10, '�� �� �� �� �� �� �� Ģ', 'TB', 0, 'C');
        $this->Ln(15);
        $this->SetFont(GOTHIC, '', 8);
        $this->SetY(26);
        $this->Cell(268, 0, date('Yǯm��d�� H��iʬs��'), 0, 0, 'R');
        $this->SetY(29);
        $this->Cell(268, 0, '��̳��', 0, 0, 'R');
        // $this->SetY(22);
        // $this->Cell(0, 0, '��329-1311 ���ڸ�������Ի��3473-2', 0, 0, 'R');
        // $this->SetY(25);
        // $this->Cell(0, 0, 'Tel:028-682-8851/Fax:028-681-7038', 0, 0, 'R');
        $this->SetFont(GOTHIC, '', 9);
        $this->SetXY(205, 32);
        $this->Cell(20,  5, '����Ĺ', 'LRTB', 0, 'C');
        $this->Cell(20,  5, '�ݡ�Ĺ', 'LRTB', 0, 'C');
        $this->Cell(20,  5, '����Ĺ', 'LRTB', 0, 'C');
        $this->Cell(20,  5, 'ô����', 'LRTB', 0, 'C');
        $this->SetXY(205, 37);
        $this->Cell(20, 15, '', 'LRTB', 0, 'C');
        $this->Cell(20, 15, '', 'LRTB', 0, 'C');
        $this->Cell(20, 15, '', 'LRTB', 0, 'C');
        $this->Cell(20, 15, '', 'LRTB', 0, 'C');
        $this->SetY(42);
        // Line break
        $this->Ln(3);
        $this->SetFont(GOTHIC, '', 12);
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        // $this->SetX(15);
        $this->Cell(20, 7, '��������', 'LRTB', 0, 'C', 1);
        $this->Cell(50, 7, $this->data_usr[1], 'LRTB', 0, 'C', 1);
        $this->Ln(20);
        
        // Colors, line width and bold font
        $this->SetFillColor(128, 128, 128);
        $this->SetTextColor(255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        
        $this->SetFont(GOTHIC, 'B', 12);
        $this->SetX(5);
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
    function get_setname($assetcode)   // ���־��̾�μ���
    {
        $conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
        $con = pg_pConnect($conn_str);                  // ��³Ū��³
        $sql = "SELECT name_place AS nplace
        FROM
            smallsum_assets_placename_master 
        WHERE code_place='$assetcode'";
        if ( !($res_s = pg_query($con, $sql)) ) {
            $set_name = '-----';
        } else {
            $rowp = pg_fetch_object($res_s);
            $set_name = $rowp->nplace;
        }
        return $set_name;
    }
    function format_date8($date8)
    {
        if (0 == $date8) {
            $date8 = '--------';    
        }
        if (8 == strlen($date8)) {
            $nen   = substr($date8,0,4);
            $tsuki = substr($date8,4,2);
            $hi    = substr($date8,6,2);
            return $nen . "/" . $tsuki . "/" . $hi;
        } else {
            return $date8;
        }
    }
}

Header('Pragma: public');   // https�����Ѥ���ݤΤ��ޤ��ʤ��Ǥ���

///////// FPDF
$pdf = new PDF_j();     // ����Ѱդ�����ĥ���饹������

///// PDFʸ��Υץ�ѥƥ�����
$pdf->SetAuthor('�������칩��������');    // Tochigi Nitto Kohki Co.,Ltd. k.kobayashi
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
// Column titles
$pdf->wh_usr = array('���־��', '�ʡ���̾', '�᡼����������', '����ǯ����', '�������', '����', '��ǧ');
$pdf->w_usr  = array(36, 75, 60, 25, 20, 55, 15);   //�ƥ���β�������ꤷ�Ƥ��ޤ���

/////////// PostgreSQL����³
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // ��³Ū��³

/////////// ��������μ���SQL
$sql = "SELECT code_act AS acode
            , name_act AS aname
        FROM
            smallsum_assets_actname_master
        ORDER BY code_act ASC";
if ( !($res_usr = pg_query($con, $sql)) ) {
    $_SESSION['s_sysmsg'] = '�������礬�����Ǥ��ޤ���' . pg_last_error($con);
    header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
    exit();
}

            // mysql_fetch_object�Ϥ���ʤ��� pg_fetch_object�Ϲ��ֹ椬����
            // �Ϥ����ä��� �ޥ˥奢����ɤ�������4.1.0�ʹߤϥ��ץ����Ȥʤä���
            // ����Ū�˥쥳���ɥ����󥿡������ä����Ƥ��롣
$data_f = array();  // �����顼�ѿ��ǤϤʤ��ơ�������Ȥ������Ȥ�����
while ($row = pg_fetch_object($res_usr)) {
    $now_act      = $row->acode;                        // �������祳����
    $now_aname    = $row->aname;                        // ��������̾
    ///// ���� �������祳���ɡ���������̾ ����ʸ��θ��Ф�
    $pdf->data_usr = array($now_act, $now_aname);
    
    /* ������������ SQL */
    $query = "SELECT set_place              AS placecode
                , substr(assets_name,0,26)  AS assetname
                , substr(assets_model,0,14) AS amodel
                , buy_ym                    AS abuyym
                , buy_price                 AS abuyprice
                , substr(note,0,20)         AS anote
              FROM smallsum_assets_master
              WHERE act_name='$now_act' AND delete_ym = 0 ORDER BY set_place ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '���ۻ���Ģ�������Ǥ��ޤ���' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
        exit();
    }
    $cnt = 0;   // �ǡ����ιԥ�����
    $data_f = array();
    while ($rows = pg_fetch_object($res)) {
        $placecode = $rows->placecode;
        $aplace    = $pdf->get_setname($placecode);
        $assetname = $rows->assetname;
        $amodel    = $rows->amodel;
        $abuyym    = $pdf->format_date8($rows->abuyym);
        $abuyprice = number_format($rows->abuyprice);
        $anote     = $rows->anote;
        $data_f[$cnt] = array($aplace, $assetname, $amodel, $abuyym, $abuyprice, $anote);
        $cnt++;
    }
    $pdf->AddPage('L','A4');    // �ڡ���������������1��ϥ����뤹��ɬ�פ�����(�դ�$pdf->Open()�Ͼ�ά��ǽ)
    //$pdf->AddPage();    // �ڡ���������������1��ϥ����뤹��ɬ�פ�����(�դ�$pdf->Open()�Ͼ�ά��ǽ)
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// ���� ��ʸ
    $pdf->FancyTable($data_f, '̤����');  // ��ǥ������ष�����дؿ���ƤӽФ�
}

$pdf->Output();     // �Ǹ�ˡ��嵭�ǡ�������Ϥ��ޤ���
exit;               // �ʤ�٤������뤹�롣�ޤ����Ǹ��PHP���å��˲��Ԥʤɤ��ޤޤ��ȥ���
?> 