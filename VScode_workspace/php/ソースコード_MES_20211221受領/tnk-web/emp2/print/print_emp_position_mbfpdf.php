<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� �Ұ�̾�� �����̰���ɽ �Уģƽ���(����) FPDF/MBFPDF����    //
// Copyright (C) 2004-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/02/13 Created  print_emp_position_mbfpdf.php  �����å���            //
// 2004/02/16 japanese.php �� mbfpdf.php ���ѹ����ƥ��å����б�           //
// 2004/03/01 fpdf_protection.php ��Ȥ� �����ΤߤΥץ�ƥ��Ȥ򤫤�����     //
// 2004/09/28 php5�б��Τ���i18n_ja_jp_hantozen()��mb_convert_kana()���ѹ�  //
// 2005/10/13 ������ë�����Į��������Ԥ��ѹ� fullpage��fullwidth ���ѹ� //
// 2010/06/16 ����Ū����޼�����970268�ˤ������Ǥ���褦���ѹ�         ��ë //
// 2017/09/13 ��Ĺ�����䡦����¾�����칩���°�����                   ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI CGI��
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');   // access_log()��Ȥ�����define��function������
// require_once ('/home/www/html/tnk-web/define.php');
access_log();                           // Script Name �ϼ�ư����

//////////////// ǧ�ڥ����å�
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
if ($_SESSION['Auth'] <= 1) {                // ���¥�٥뤬���ʲ��ϵ���(���桼�����Τ�)
// if (account_group_check() == FALSE) {        // ����Υ��롼�װʳ��ϵ���
    if ($_SESSION['User_ID'] != '970268') {
        $_SESSION['s_sysmsg'] = '�Ұ�̾���������븢�¤�����ޤ���';
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ����ƽи������
        // header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
}
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸

#���ܸ�ɽ���ξ��ɬ�ܡ����ʤ����ɬ�����󥯥롼�ɤ��뤳�ȡ�
require_once ('/home/www/html/mbfpdf/mbfpdf.php');          // �ޥ���Х���FPDF
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');     // Core Font �Υѥ�

class PDF_j extends MBFPDF  // ���ܸ�PDF���饹���ĥ���ޤ���
{
    
    //Simple table...̤����
    function BasicTable($header,$data)
    {
        //Header
        foreach($header as $col)
            $this->Cell(30,7,$col,1);
        $this->Ln();
        //Data
        foreach($data as $row)
        {
            foreach($row as $col)
                $this->Cell(30,7,$col,1);
            $this->Ln();
        }
    }
    
    //Better table...̤����
    function ImprovedTable($header,$data)
    {
        //Column widths
        $w=array(40,35,40,50,50);
        //Header
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C');
        $this->Ln();
        //Data
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row[0],'LR');
            $this->Cell($w[1],6,$row[1],'LR');
            $this->Cell($w[2],6,$row[2],'LR');
            $this->Cell($w[3],6,$row[3],'LR');
            $this->Cell($w[4],6,$row[4],'LR');
            $this->Ln();
        }
        //Closure line
        $this->Cell(array_sum($w),0,'','T');
    }
    
    #���Υ��дؿ��������Ƥ��ޤ���
    //Colored table
    function FancyTable($data)
    {
        //Color and font restoration
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        //Header
        $w=array(25, 15, 24, 105, 30);   //�ƥ���β�������ꤷ�Ƥ��ޤ���
        //Data
        $fill=0;
        foreach ($data as $row)
        {
            $this->SetFont(GOTHIC, '', 9);
            $this->Cell($w[0], 7, $row[0], 'LRTB', 0, 'L', $fill);  // �ʲ����ƥե�����ɤ��Ȥ˽���
            $this->Cell($w[1], 7, $row[1], 'LRTB', 0, 'L', $fill);
            $this->SetFont(GOTHIC, '', 9);
            $this->Cell($w[2], 7, $row[2], 'LRTB', 0, 'L', $fill);
            $this->SetFont(GOTHIC, '', 11);
            $row[3] = mb_convert_kana($row[3], 'khnra');            // ���ѷϤ�Ⱦ�Ѥˤʤ����ޤ������ڡ����δط���
            $this->Cell($w[3], 7, $row[3], 'LRTB', 0, 'L', $fill);
            $this->SetFont(GOTHIC, '', 10);
            $row[4] = mb_convert_kana($row[4], 'khnra');
            $this->Cell($w[4], 7, $row[4], 'LRTB', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }

    function Header()   //Ƭ�ˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    {
        $this->Image('/home/www/html/tnk-web/img/t_nitto_logo2.png', 150, 5, 50, 0, '', '');  //���᡼�������֤��ޤ���������ꤷ�ޤ�������ե���󥹻���
        $this->SetX(70);
        //Select Arial bold 15
        $this->SetFont(GOTHIC,'B',16);
        //Move to the right
        # $this->Cell(80);
        //Framed title
        $this->Cell(70,10,'�Ұ�̾�������̰���ɽ','TB',0,'C');
        $this->Ln(15);
        $this->SetFont(GOTHIC,'',8);
        $this->SetY(20);
        $this->Cell(0,8,date('Yǯm��d�� H��iʬs��'),0,0,'R');
        $this->SetY(25);
        $this->Cell(0,8,'�������칩��������',0,0,'R');
        $this->SetY(30);
        $this->Cell(0,8,'��329-1311 ���ڸ�������Ի��3473-2',0,0,'R');
        $this->SetY(35);
        $this->Cell(0,8,'Tel:028-682-8851/Fax:028-681-7038',0,0,'R');
        //Line break
        $this->Ln(10);
        
        //Column titles
        $header = array('��°����', '��', ' ��̾', ' ����', ' �����ֹ�');
        //Column data array
        
        //Colors, line width and bold font
        $this->SetFillColor(128,128,128);
        $this->SetTextColor(255);
        $this->SetDrawColor(128,128,128);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        //Header
        $w=array(25, 15, 24, 105, 30);   //�ƥ���β�������ꤷ�Ƥ��ޤ���
        
        $this->SetFont(GOTHIC,'B',12);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',1);//�ե������̾�����
        $this->Ln();//���򤢤��ޤ�������ե���󥹻��ȤΤ���
    }
    function Footer()//���ĤˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    {
        //Go to 1.5 cm from bottom
        //Select Arial italic 8
        $this->SetFont('Times','I',8);
        //Print centered page number
        $this->SetY(-10);   // ������10mm�˰���
        $this->Cell(0,10,'('.$this->PageNo().')',0,0,'C');
        $this->Cell(0,10,'Copyright TOCHIG NITTO KOHKI Co.,Ltd. All rights reserved',0,0,'R');
    }

}

Header('Pragma: public');   // https�����Ѥ���ݤΤ��ޤ��ʤ��Ǥ���

#FPDF
$pdf = new PDF_j();     // ����Ѱդ�����ĥ���饹������
// $pdf->AddSJISFont();    // ���ܸ줬ɬ�פʾ��Τ��ޤ��ʤ�

///// PDFʸ��Υץ�ѥƥ�����
$pdf->SetAuthor('�������칩�������� ���Ӱ칰');    // Tochigi Nitto Kohki Co.,Ltd. k.kobayashi
$pdf->SetCreator("$current_script");
$pdf->SetTitle('Employee name list');
$pdf->SetSubject('Position distinction');
$pdf->SetDisplayMode('fullwidth', 'default');       // �ڡ����Υ쥤������=�裲���������ꤷ�ʤ�����continuous=Ϣ³��������
$pdf->SetCompression(true);         // ���̤�ͭ���ˤ���(default=on)
$pdf->SetProtection(array('print'), '', 'tnkowner');    // �����Τߵ��ĤΥץ�ƥ��� fpdf_protection.php��ɬ��('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32)

///// PDFʸ��λ��ѥե���Ȥ�����
$pdf->AddMBFont(GOTHIC ,'SJIS');
// $pdf->AddMBFont(PGOTHIC,'SJIS');
// $pdf->AddMBFont(MINCHO ,'SJIS');
// $pdf->AddMBFont(PMINCHO,'SJIS');
// $pdf->AddMBFont(KOZMIN ,'SJIS');
$pdf->Open();           // PDF�򳫻�
// $pdf->SetFont('SJIS','',10);//�ǥե���ȥե���Ȥ�SJIS12�ݥ���Ȥˤ��Ƥ�����
$pdf->SetFont(GOTHIC,'',10);//�ǥե���ȥե���Ȥ�SJIS12�ݥ���Ȥˤ��Ƥ�����

/////////// SQL�����Ѥ�����ʤɻ��ͤ�
$sql = "select trim(section_name) as section
            , trim(position_name) as position
            , trim(name) as name
            , trim(address) as address
            , trim(tel) as tel
        from
            user_detailes left
        outer join
            section_master
        using(sid)
        left outer join
            position_master
        using(pid)
        where sflg=1 and retire_date is null and uid!='000000' and pid != 120 and sid != 80 and sid != 90 and sid != 95
        order by pid DESC, sid DESC, uid ASC";
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // ��³Ū��³
$res = pg_query($con, $sql);

$cnt = 0;   // �ǡ����ιԥ�����
            // mysql_fetch_object�Ϥ���ʤ��� pg_fetch_object�Ϲ��ֹ椬����
            // �Ϥ����ä��� �ޥ˥奢����ɤ�������4.1.0�ʹߤϥ��ץ����Ȥʤä���
            // ����Ū�˥쥳���ɥ����󥿡������ä����Ƥ��롣
$data_f = array();//�����顼�ѿ��ǤϤʤ��ơ�������Ȥ������Ȥ�����
while ($row = pg_fetch_object($res)) {

    $now_section  = mb_substr($row->section, -6);
    $now_position = mb_substr($row->position, 0, 3);
    $now_name     = $row->name;
    $now_address  = mb_substr($row->address, 0, 39);
    $now_tel      = $row->tel;

    $data_f[$cnt] = array($now_section, $now_position, $now_name, $now_address, $now_tel);
    $cnt++;
}

// ����
$pdf->AddPage();    // �ڡ���������������1��ϥ����뤹��ɬ�פ����ꤽ���Ǥ�
$pdf->SetFont(GOTHIC, '', 12);
$pdf->FancyTable($data_f); //��ǥ������ष�����дؿ���ƤӽФ��ޤ���
    // $pdf->Image('/home/www/html/tnk-web/img/logo_pro-works.png',170,5,30,0,'','');
    // ʣ���Ǥ��������Header()�˵��Ҥ��롣���᡼�������֤��ޤ���������ꤷ�ޤ�������ե���󥹻���
$pdf->Output();     // �Ǹ�ˡ��嵭�ǡ�������Ϥ��ޤ���
exit;   //  �ʤ�٤������뤹��٤��Ǥ����ޤ����Ǹ��PHP���å��˲��Ԥʤɤ��ޤޤ��ȥ���Ǥ���
?> 