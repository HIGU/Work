<?php
/////////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ ��Ŭ������κ��� �ե������åףУģƽ���(����) FPDF/FPDF-JA���� //
// Copyright (C) 2008-2015 Norihisa.Ohya usoumu@nitto-kohki.co.jp              //
// Changed history                                                             //
// 2008/05/30 Created   unfit_report_FollowPrint_ja.php                        //
// 2008/08/29 masterst���ܲ�ư����                                             //
// 2015/01/26 ������Ĺ��������Ĺ���ѹ�����������15-01-26���ѹ�                 //
/////////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');                // ajax�ǻ��Ѥ�����
// ini_set('error_reporting', E_STRICT);                    // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);                          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');                          // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');             // zend 1.X ����ѥ� php4�θߴ��⡼��
ob_start('ob_gzhandler');                                   // ���ϥХåե���gzip����
session_start();                                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');                           // access_log()���ǻ���
access_log();                                               // Script Name �ϼ�ư����

$current_script  = $_SERVER['PHP_SELF'];                    // ���߼¹���Υ�����ץ�̾����¸
$serial_temp  = array();
$serial_temp  = explode( '&', $_SERVER["QUERY_STRING"]);
$serial_temp2 = explode( 'serial_no=', $serial_temp[0]);
$serial_no    = $serial_temp2[1];

#���ܸ�ɽ���ξ��ɬ�ܡ����ʤ����ɬ�����󥯥롼�ɤ��뤳�ȡ�
require('/home/www/html/fpdf152/japanese.php');
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');     // Core Font �Υѥ�

class PDF_j extends PDF_Japanese                            //���ܸ�PDF���饹���ĥ���ޤ���
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
    function FancyTable($data_follow)
    {   
        $this->SetY(19);
        $this->SetX(5);
        $this->SetFont('SJIS','',10);
        $this->Cell(0,9,'ȯ������',0,0,'L');
        $this->SetY(28);
        $this->SetX(5);
        $this->Cell(185,9,'�� �ե������å� ��','LRT','C','L');    //�ե������̾�����
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 37, $lend, 37);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(37);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,' ' . $data_follow[0],'LR','C','L');      //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 46, $lend, 46);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(46);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[1],'LR','C','L');            //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 55, $lend, 55);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(55);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[2],'LR','C','L');            //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 64, $lend, 64);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(64);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[3],'LR','C','L');            //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 73, $lend, 73);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(73);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[4],'LRB','C','L');           //�ե������̾�����
        $this->SetFont('SJIS','',10);
        $this->SetY(87);
        $this->SetX(5);
        $this->Cell(0,9,'�ʼ��ݾڲ�',0,0,'L');
        $this->SetY(96);
        $this->SetX(5);
        $this->Cell(185,9,'�� �ե������å� ��','LRT','C','L');    //�ե������̾�����
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 105, $lend, 105);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(105);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,' ' . $data_follow[5],'LR','C','L');      //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 114, $lend, 114);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(114);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[6],'LR','C','L');            //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 123, $lend, 123);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(123);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[7],'LR','C','L');            //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 132, $lend, 132);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(132);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[8],'LR','C','L');            //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 141, $lend, 141);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(141);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[9],'LRB','C','L');           //�ե������̾�����
        $this->SetFont('SJIS','',10);
        $this->SetY(156);
        $this->SetX(5);
        $this->Cell(185,5,date('Y��ǯ��m���d����'),'',0,'R');
        $this->SetY(161);
        $this->SetX(5);
        $header = array('�����졡Ĺ', '�� �� �� Ĺ', '�ʼ�������Ǥ��', '�� �� �� Ĺ', '�ʼ��ݾڲ�Ĺ', '����Ĺ', '�ݡ�Ĺ');
        $w=array(27, 26, 27, 26, 27, 26, 26);                       //�ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            $this->Cell($w[$i],6,$header[$i],1,'C','C');            //�ե������̾�����
        }
        $this->SetY(167);
        $this->SetX(5);
        $header = array('', '', '', '', '', '', '');
        $w=array(27, 26, 27, 26, 27, 26, 26);                       //�ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            $this->Cell($w[$i],20,$header[$i],'LRB','C','C');       //�ե������̾�����
        }
        
        $this->SetY(192);
        $this->SetX(5);
        $this->SetFont('SJIS','',10);
        $this->Cell(0,8,'�� �ո��� ��',0,0,'L');
        $this->SetY(200);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,' ' . $data_follow[10],'TLR','C','L');    //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 209, $lend, 209);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(209);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[11],'LR','C','L');           //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 218, $lend, 218);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(218);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[12],'LR','C','L');           //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 227, $lend, 227);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(227);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[13],'LR','C','L');           //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 236, $lend, 236);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(236);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[14],'LR','C','L');           //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 245, $lend, 245);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(245);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[15],'LR','C','L');           //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 254, $lend, 254);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(254);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,$data_follow[16],'LR','C','L');           //�ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 5;
        $lend   = $lstart + 1;
        $end    = 98;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 263, $lend, 263);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(263);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,8,$data_follow[17],'LRB','C','L');          //�ե������̾�����
        $this->SetFont('SJIS','',10);
    }
    
    //Ƭ�ˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    function Header()
    {
        
    }
    
    //���ĤˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    function Footer()
    {
        //Go to 1.5 cm from bottom
        //Select Arial italic 8
        $this->SetFont('SJIS','',10);
        //Print centered page number
        $this->SetY(-15);                                           // ������15mm�˰���
        $this->SetX(5);
        //$this->Cell(0,10,'('.$this->PageNo().')',0,0,'C');
        $this->Cell(185,10,'������',0,0,'C');
        $this->SetY(-25);                                           // ������20mm�˰���
        $this->SetX(5);
        //$this->Cell(0,10,'('.$this->PageNo().')',0,0,'C');
        //����β����Ԥä��ݤϤ�����ʬ��ľ��
        $this->Cell(185,10,'���꣰���ݣ����ݣ��������꣱���ݣ����ݣ���',0,0,'R');    //����ǯ����
    }

}

Header('Pragma: public');                                           // https�����Ѥ���ݤΤ��ޤ��ʤ��Ǥ���

#FPDF
$pdf = new PDF_j();                                                 // ����Ѱդ�����ĥ���饹������

///// PDFʸ��Υץ�ѥƥ�����
$pdf->SetAuthor('Tochigi Nitto Kohki Co.,Ltd.');
$pdf->SetCreator("$current_script");
$pdf->SetTitle('Employee name list');
$pdf->SetSubject('Section distinction');
$pdf->SetDisplayMode('fullwidth', 'default');                       // �ڡ����Υ쥤������=�裲���������ꤷ�ʤ�����continuous=Ϣ³��������
$pdf->SetCompression(true);                                         // ���̤�ͭ���ˤ���(default=on)

///// PDFʸ��λ��ѥե���Ȥ�����
$pdf->AddSJISFont();                                                // ���ܸ줬ɬ�פʾ��Τ��ޤ��ʤ�
$pdf->Open();                                                       // PDF�򳫻�
$pdf->SetFont('SJIS','',10);                                        //�ǥե���ȥե���Ȥ�SJIS12�ݥ���Ȥˤ��Ƥ�����

///// ��Ŭ���������Ҷ�ͭ�ե������åץơ��֥� �μ���
$sql = "    SELECT follow_section               AS follow_section
                ,follow_quality                 AS follow_quality
                ,follow_opinion                 AS follow_opinion
            FROM
                unfit_report_follow
            WHERE
                serial_no = {$serial_no}
        ";
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                                      // ��³Ū��³
$res = pg_query($con, $sql);
$row = pg_fetch_object($res);

// ȯ�����硦�ʼ��ݾڲݥե������åפȰո���β���
$follow_section_temp   = nl2br($row->follow_section);
$follow_section        = array();
$follow_section        = explode( '<br />', $follow_section_temp);
$count_follow_section  = count($follow_section);
switch ($count_follow_section) {
    case 0 :
        $follow_section[0] = '';
        $follow_section[1] = '';
        $follow_section[2] = '';
        $follow_section[3] = '';
        $follow_section[4] = '';
        break;
    case 1 :
        $follow_section[1] = '';
        $follow_section[2] = '';
        $follow_section[3] = '';
        $follow_section[4] = '';
        break;
    case 2 :
        $follow_section[2] = '';
        $follow_section[3] = '';
        $follow_section[4] = '';
        break;
    case 3 :
        $follow_section[3] = '';
        $follow_section[4] = '';
        break;
    case 4 :
        $follow_section[4] = '';
        break;
    default      :
        break;
}

$follow_quality_temp   = nl2br($row->follow_quality);
$follow_quality        = array();
$follow_quality        = explode( '<br />', $follow_quality_temp);
$count_follow_quality  = count($follow_quality);
switch ($count_follow_quality) {
    case 0 :
        $follow_quality[0] = '';
        $follow_quality[1] = '';
        $follow_quality[2] = '';
        $follow_quality[3] = '';
        $follow_quality[4] = '';
        break;
    case 1 :
        $follow_quality[1] = '';
        $follow_quality[2] = '';
        $follow_quality[3] = '';
        $follow_quality[4] = '';
        break;
    case 2 :
        $follow_quality[2] = '';
        $follow_quality[3] = '';
        $follow_quality[4] = '';
        break;
    case 3 :
        $follow_quality[3] = '';
        $follow_quality[4] = '';
        break;
    case 4 :
        $follow_quality[4] = '';
        break;
    default      :
        break;
}

$follow_opinion_temp   = nl2br($row->follow_opinion);
$follow_opinion        = array();
$follow_opinion        = explode( '<br />', $follow_opinion_temp);
$count_follow_opinion  = count($follow_opinion);
switch ($count_follow_opinion) {
    case 0 :
        $follow_opinion[0] = '';
        $follow_opinion[1] = '';
        $follow_opinion[2] = '';
        $follow_opinion[3] = '';
        $follow_opinion[4] = '';
        $follow_opinion[5] = '';
        $follow_opinion[6] = '';
        $follow_opinion[7] = '';
        break;
    case 1 :
        $follow_opinion[1] = '';
        $follow_opinion[2] = '';
        $follow_opinion[3] = '';
        $follow_opinion[4] = '';
        $follow_opinion[5] = '';
        $follow_opinion[6] = '';
        $follow_opinion[7] = '';
        break;
    case 2 :
        $follow_opinion[2] = '';
        $follow_opinion[3] = '';
        $follow_opinion[4] = '';
        $follow_opinion[5] = '';
        $follow_opinion[6] = '';
        $follow_opinion[7] = '';
        break;
    case 3 :
        $follow_opinion[3] = '';
        $follow_opinion[4] = '';
        $follow_opinion[5] = '';
        $follow_opinion[6] = '';
        $follow_opinion[7] = '';
        break;
    case 4 :
        $follow_opinion[4] = '';
        $follow_opinion[5] = '';
        $follow_opinion[6] = '';
        $follow_opinion[7] = '';
        break;
    case 5 :
        $follow_opinion[5] = '';
        $follow_opinion[6] = '';
        $follow_opinion[7] = '';
        break;
    case 6 :
        $follow_opinion[6] = '';
        $follow_opinion[7] = '';
        break;
    case 7 :
        $follow_opinion[7] = '';
        break;
    default      :
        break;
}

$data_follow = array();            //�����顼�ѿ��ǤϤʤ��ơ�������Ȥ������Ȥ�����
$data_follow = array($follow_section[0], $follow_section[1], $follow_section[2], $follow_section[3], $follow_section[4]
                   , $follow_quality[0], $follow_quality[1], $follow_quality[2], $follow_quality[3], $follow_quality[4]
                   , $follow_opinion[0], $follow_opinion[1], $follow_opinion[2], $follow_opinion[3], $follow_opinion[4]
                   , $follow_opinion[5], $follow_opinion[6], $follow_opinion[7]);

// ����
$pdf->AddPage();                   // �ڡ���������������1��ϥ����뤹��ɬ�פ����ꤽ���Ǥ�
//$pdf->SetFont('SJIS', '', 12);
$pdf->FancyTable($data_follow);    //��ǥ������ष�����дؿ���ƤӽФ��ޤ���
//$pdf->Image('/home/www/html/tnk-web/img/logo_pro-works.png',170,5,30,0,'','');
// ʣ���Ǥ��������Header()�˵��Ҥ��롣���᡼�������֤��ޤ���������ꤷ�ޤ�������ե���󥹻���
$pdf->Output();                    // �Ǹ�ˡ��嵭�ǡ�������Ϥ��ޤ���
exit;                              //�ʤ�٤������뤹��٤��Ǥ����ޤ����Ǹ��PHP���å��˲��Ԥʤɤ��ޤޤ��ȥ���Ǥ���
?>