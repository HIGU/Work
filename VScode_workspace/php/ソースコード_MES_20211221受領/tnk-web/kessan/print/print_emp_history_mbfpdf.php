<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���˥塼 ���顦��ʡ���ư ����ΰ���ɽ PDF����(����) FPDF/MBFPDF���� //
// Copyright (C) 2004-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/02/16 Created  print_emp_history_mbfpdf.php  �����å���             //
// 2004/03/01 fpdf_protection.php ��Ȥ� �����ΤߤΥץ�ƥ��Ȥ򤫤�����     //
// 2005/10/13 ������ë�����Į��������Ԥ��ѹ� fullpage��fullwidth ���ѹ� //
// 2007/03/07 ��ư���ư������                                              //
// 2007/10/15 ���꡼��ߥåȤ��ɲá� E_ALL �� E_ALL | E_STRICT��          //
// 2007/10/16 ���顦��ʡ���ư(����ץ����)���Ŀ���  data_usr��������ѹ�  //
// 2008/04/24 ����̾���򿦤�û��ʸ�������ѹ�                           ��ë //
// 2010/06/16 ����Ū����޼�����970268�ˤ������Ǥ���褦���ѹ�         ��ë //
// 2010/06/17 �Ұ������äˤ����꡼��ߥåȤ�64M����100M���ѹ�      ��ë //
// 2017/09/13 ��Ĺ�����䡦����¾�����칩���°�����                   ��ë //
// 2018/04/20 �������Ұ������Ƭ�ڡ����Τ߰�������褦�ѹ�           ��ë //
// 2019/07/22 �������Ĥ�getCheckAuthority(59)�ˡ�������ư���ѹ�      ��ë //
//            �Ұ��ֹ�.jpg�ʤΤ��ѹ��ξ��ϲ�������(Excel)��PGM�ѹ��� ��ë //
// 2020/05/18 ���������Ĺ�������ѹ�                                 ��ë //
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
    //if ($_SESSION['User_ID'] != '970268') {
    if (!getCheckAuthority(59)) {
        $_SESSION['s_sysmsg'] = '�Ұ�̾���������븢�¤�����ޤ���';
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
        // header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    //}
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
        $this->Cell($this->w_usr[0], 6, '', 'LTB', 0, 'L', 1);
        $this->Cell($this->w_usr[1], 6, '', 'TB', 0, 'L', 1);
        $this->SetTextColor(50, 0, 255);    // ����ץ������������Ѥ���(��)
        $this->Cell($this->w_usr[2], 6, $caption, 'RTB', 0, 'L', 1);
        $this->SetTextColor(0, 0, 0);
        $this->Ln();    // ����
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
        $this->Cell(80, 10, '���顦��ʡ���ư���� ����ɽ', 'TB', 0, 'C');
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
        if ($this->usr_cnt == 1) {  // ������ɽ���� ��Ƭ�Τ߲���ɽ��
            $this->Cell(20,  5, '����ǧ', 'LRTB', 0, 'C');
            $this->Cell(20,  5, '���', 'LRTB', 0, 'C');
            $this->SetXY(165, 27);
            $this->Cell(20, 15, '', 'LRTB', 0, 'C');
            $this->Image('/home/www/html/tnk-web/emp/print/300055.jpg', 170, 28, 12, 12, '', '');
            $this->Cell(20, 15, '', 'LRTB', 0, 'C');
            $this->Image('/home/www/html/tnk-web/emp/print/300551.jpg', 190, 28, 12, 12, '', '');
            $this->SetY(42);
            $this->usr_cnt = 0;
        } else {
            $this->Cell(20,  5, '', '', 0, 'C');
            $this->Cell(20,  5, '', '', 0, 'C');
            $this->SetXY(165, 27);
            $this->Cell(20, 15, '', '', 0, 'C');
            $this->Cell(20, 15, '', '', 0, 'C');
            $this->SetY(42);
        }
        // Line break
        $this->Ln(3);
        $this->SetFont(GOTHIC, '', 10);
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        // $this->SetX(15);
        $this->Cell(20, 7, '�Ұ��ֹ�', 'LRTB', 0, 'C', 1);    // �ʲ����ƥե�����ɤ��Ȥ˽���
        $this->Cell(15, 7, $this->data_usr[0], 'LRTB', 0, 'C', 1);
        $this->Cell(15, 7, '������', 'LRTB', 0, 'C', 1);
        $this->Cell(35, 7, $this->data_usr[1], 'LRTB', 0, 'C', 1);
        $this->Cell(20, 7, '������', 'LRTB', 0, 'C', 1);
        $this->Cell(30, 7, $this->data_usr[2], 'LRTB', 0, 'C', 1);
        $this->Cell(20, 7, '�ᡡ̾', 'LRTB', 0, 'C', 1);
        $this->Cell(35, 7, $this->data_usr[3], 'LRTB', 0, 'C', 1);
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
$pdf->wh_usr = array('��������', '��λ����', '�⡡����');
$pdf->w_usr  = array(20, 20, 150);   //�ƥ���β�������ꤷ�Ƥ��ޤ���

/////////// PostgreSQL����³
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // ��³Ū��³

/////////// �Ұ��ֹ����μ���SQL
// ��Ĺ�����䡦����¾�����칩������
$sql = "SELECT trim(uid) AS uid
            , trim(section_name) AS section
            , trim(position_name) AS position
            , trim(name) AS name
        FROM
            user_detailes
        LEFT OUTER JOIN
            section_master
        USING(sid)
        LEFT OUTER JOIN
            position_master
        USING(pid)
        WHERE sflg = 1 AND retire_date IS null AND uid != '000000' and pid != 120 and sid != 80 and sid != 90 and sid != 95
        ORDER BY sid DESC, pid DESC, uid ASC";
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
    $now_uid      = $row->uid;                          // �Ұ��ֹ�
    $now_section  = mb_substr($row->section, -9);       // ����(û��)
    $now_position = mb_substr($row->position, 0, 5);    // ����(û��)
    $now_name     = $row->name;                         // ��̾
    ///// ���� �Ұ��ֹ桦�����򿦡���̾ ����ʸ��θ��Ф�
    $pdf->data_usr = array($now_uid, $now_section, $now_position, $now_name);
    
    /* ������������ SQL */
    $query = "SELECT ur.begin_date      AS s_date
                , ur.end_date           AS e_date
                , trim(rm.receive_name) AS r_name
              FROM user_receive ur, receive_master rm
              WHERE ur.uid='$now_uid' AND ur.rid=rm.rid ORDER BY ur.begin_date ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '������򤬼����Ǥ��ޤ���' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
        exit();
    }
    $cnt = 0;   // �ǡ����ιԥ�����
    $data_f = array();
    while ($rows = pg_fetch_object($res)) {
        $s_date = $rows->s_date;
        $e_date = $rows->e_date;
        $r_name = $rows->r_name;
        $data_f[$cnt] = array($s_date, $e_date, $r_name);
        $cnt++;
    }
    $pdf->AddPage();    // �ڡ���������������1��ϥ����뤹��ɬ�פ�����(�դ�$pdf->Open()�Ͼ�ά��ǽ)
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// ���� ��ʸ
    $pdf->FancyTable($data_f, '������');  // ��ǥ������ष�����дؿ���ƤӽФ�
    
    /* ��ʰ�������� */
    $query = "SELECT uc.acq_date            AS s_date
                , trim(cm.capacity_name)    AS r_name
              FROM user_capacity uc,capacity_master cm
              WHERE uc.uid='$now_uid' AND uc.cid=cm.cid ORDER BY uc.acq_date ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '��ʷ��򤬼����Ǥ��ޤ���' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
        exit();
    }
    $cnt = 0;   // �ǡ����ιԥ�����
    $data_f = array();
    while ($rows = pg_fetch_object($res)) {
        $s_date = $rows->s_date;
        $e_date = '';
        $r_name = $rows->r_name;
        $data_f[$cnt] = array($s_date, $e_date, $r_name);
        $cnt++;
    }
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// ���� ��ʸ
    $pdf->FancyTable($data_f, '�񡡳�');  // ��ǥ������ष�����дؿ���ƤӽФ�
    
    /* ��ư�������� */
    $query = "SELECT trans_date         AS s_date
                , trim(section_name)    AS r_name
              FROM user_transfer
              WHERE uid='$now_uid' ORDER BY trans_date ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '��ư���򤬼����Ǥ��ޤ���' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
        exit();
    }
    $cnt = 0;   // �ǡ����ιԥ�����
    $data_f = array();
    while ($rows = pg_fetch_object($res)) {
        $s_date = $rows->s_date;
        $e_date = '';
        $r_name = $rows->r_name;
        $data_f[$cnt] = array($s_date, $e_date, $r_name);
        $cnt++;
    }
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// ���� ��ʸ
    $pdf->FancyTable($data_f, '�ۡ�ư');  // ��ǥ������ष�����дؿ���ƤӽФ�
    $pdf->usr_cnt = 1;                    // ������ɽ����
}

$pdf->Output();     // �Ǹ�ˡ��嵭�ǡ�������Ϥ��ޤ���
exit;               // �ʤ�٤������뤹�롣�ޤ����Ǹ��PHP���å��˲��Ԥʤɤ��ޤޤ��ȥ���
?> 