<?php
///////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ ��Ŭ������κ��� ��Ŭ������Уģƽ���(����) FPDF/FPDF-JA���� //
// Copyright (C) 2008-2015 Norihisa.Ohya usoumu@nitto-kohki.co.jp            //
// Changed history                                                           //
// 2008/05/30 Created   unfit_report_Print_ja.php                            //
// 2008/08/29 masterst���ܲ�ư����                                           //
// 2008/11/27 �»ܹ��ܥ����ƥ��̵ͭ��ɽ������ʤ��ս������                 //
// 2015/01/26 ������Ĺ��������Ĺ���ѹ�                                       //
///////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');               // ajax�ǻ��Ѥ�����
// ini_set('error_reporting', E_STRICT);                   // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);                         // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');                         // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');            // zend 1.X ����ѥ� php4�θߴ��⡼��
ob_start('ob_gzhandler');                                  // ���ϥХåե���gzip����
session_start();                                           // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');                          // access_log()���ǻ���
access_log();                                              // Script Name �ϼ�ư����

$current_script  = $_SERVER['PHP_SELF'];                   // ���߼¹���Υ�����ץ�̾����¸
$serial_temp  = array();
$serial_temp  = explode( '&', $_SERVER["QUERY_STRING"]);
$serial_temp2 = explode( 'serial_no=', $serial_temp[0]);
$serial_no    = $serial_temp2[1];

#���ܸ�ɽ���ξ��ɬ�ܡ����ʤ����ɬ�����󥯥롼�ɤ��뤳�ȡ�
require('/home/www/html/fpdf152/japanese.php');
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');    // Core Font �Υѥ�

class PDF_j extends PDF_Japanese                           // ���ܸ�PDF���饹���ĥ���ޤ���
{
    
    // Simple table...̤����
    function BasicTable($header,$data)
    {
        // Header
        foreach($header as $col)
            $this->Cell(30,7,$col,1);
        $this->Ln();
        // Data
        foreach($data as $row)
        {
            foreach($row as $col)
                $this->Cell(30,7,$col,1);
            $this->Ln();
        }
    }
    
    // Better table...̤����
    function ImprovedTable($header,$data)
    {
        // Column widths
        $w=array(40,35,40,50,50);
        // Header
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C');
        $this->Ln();
        // Data
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row[0],'LR');
            $this->Cell($w[1],6,$row[1],'LR');
            $this->Cell($w[2],6,$row[2],'LR');
            $this->Cell($w[3],6,$row[3],'LR');
            $this->Cell($w[4],6,$row[4],'LR');
            $this->Ln();
        }
        // Closure line
        $this->Cell(array_sum($w),0,'','T');
    }
    
    #���Υ��дؿ��������Ƥ��ޤ���
    //Colored table
    function FancyTable($data_header,$data_cause,$data_measure,$data_develop)
    {   
        $this->SetFont('SJIS','',10);
        $this->Cell(30,4,'����No����','B','C','L');
        $this->SetFont('SJIS','',12);
        $this->Cell(45,4,$data_header[7],'B','C','L');
        $this->SetY(16);
        $this->SetX(18);
        $this->SetFont('SJIS','',10);
        $this->Cell(114,4,'',0,0,'R');
        $this->Cell(16,4,'ȯ������','B',0,'L');
        $this->SetFont('SJIS','',12);
        $this->Cell(59,4,date('Y��ǯ��m���d����'),'B',0,'L');
        $this->SetY(21);
        $this->SetX(18);
        // Column titles
        $header = array('', '�ʡ���', '����Ĺ', '�ݡ�Ĺ');
        // Column data array
        
        // Header
        $this->SetFont('SJIS','',10);
        $w=array(130, 20, 20, 20);                                               // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],5,$header[$i],0,'C','C');                     // �ե������̾�����
            } else {
                $this->Cell($w[$i],5,$header[$i],1,'C','C');                     // �ե������̾�����
            }
        }
        $this->SetY(26);
        $this->SetX(18);
        $w=array(130, 20, 20, 20);                                               // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],5,'',0,'C','C');                              // �ե������̾�����
            } else {
                $this->Cell($w[$i],5,'','LR','C','C');                           // �ե������̾�����
            }
        }

        $this->SetY(31);
        $this->SetX(18);
        $this->SetFont('SJIS','',24);
        $header = array('�ԡ�Ŭ���硡�󡡹𡡽�', '', '', '');
        $w=array(130, 20, 20, 20);                                               // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],11,$header[$i],0,'C','L');                    // �ե������̾�����
            } else {
                $this->Cell($w[$i],11,$header[$i],'LRB','C','C');                // �ե������̾�����
            }
        }
        $this->SetY(45);
        $this->SetX(18);
        $this->SetFont('SJIS','',10);
        $header = array('��  Ŭ  ��  ��  ��', '');
        $w=array(30, 160);                                                       // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],1,'C','C');                     // �ե������̾�����
            } else {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,'����' . $data_header[0],'LRT','C','L');    // �ե������̾�����
                $this->SetFont('SJIS','',10);
            }
        }
        $this->SetY(51);
        $this->SetX(18);
        $header = array('���� �� When ��', 'ȯ��ǯ����', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],1,'C','C');                     // �ե������̾�����
            } else if($i == 1 ) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            } else {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,'����' . $data_header[4] . ' ǯ ' . $data_header[5] . ' �� ' . $data_header[6] . ' ��','LRT','C','L');//�ե������̾�����
                $this->SetFont('SJIS','',10);
            }
        }
        $this->SetY(57);
        $this->SetX(18);
        $header = array('�ɤ��ǡ�Where��', 'ȯ ��  �� ��', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],1,'C','C');                     // �ե������̾�����
            } else if($i == 1 ) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            } else {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,'����' . $data_header[1],'LRT','C','L');    // �ե������̾�����
                $this->SetFont('SJIS','',10);
            }
        }
        $this->SetY(63);
        $this->SetX(18);
        $header = array('ï �� �� Who ��', '�� Ǥ  �� ��', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],1,'C','C');                     // �ե������̾�����
            } else if($i == 1 ) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            } else {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,'����' . $data_header[2],'LRT','C','L');    // �ե������̾�����
                $this->SetFont('SJIS','',10);
            }
        }
        $this->SetY(69);
        $this->SetX(18);
        $header = array('��  ��', '�����ʡ�̾', '', '�����ʡ�̾', '');
        $w=array(30, 25, 55, 25, 55);                                            // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            } else if($i == 2) {
                $this->SetFont('SJIS','B',9);
                $this->Cell($w[$i],6,$data_cause[10],'LRBT','C','C');            // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else if($i == 4) {
                $this->SetFont('SJIS','B',9);
                $this->Cell($w[$i],6,$data_cause[11],'LRBT','C','C');            // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            }
        }
        $this->SetY(75);
        $this->SetX(18);
        $header = array('�� what ��', '�� ��  �� ��', '', '�� ��  �� ��', '');
        $w=array(30, 25, 55, 25, 55);                                            // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LRB','C','C');                 // �ե������̾�����
            } else if($i == 2) {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,$data_cause[0],'LRBT','C','C');             // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else if($i == 4) {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,$data_cause[1],'LRBT','C','C');             // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRTB','C','C');                // �ե������̾�����
            }
        }
        
        $this->SetY(83);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            } else if($i == 2) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,' ' . $data_cause[2],'LRT','C','L');        // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            }
        }
        
        $this->SetY(89);
        $this->SetX(18);
        $header = array('', 'ȯ �� �� ��', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i < 2) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            } else {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_cause[3],'LR','C','L');               // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 73;
                $lend   = $lstart + 1;
                $end    = 141;
                for($i=$lstart;$i<$end;$i++) {
                    $this->Line($lstart, 89, $lend, 89);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            }
        }
        $this->SetY(95);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i < 2) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            } else {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_cause[4],'LR','C','L');               // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 73;
                $lend   = $lstart + 1;
                $end    = 141;
                for($i=$lstart;$i<$end;$i++) {
                    $this->Line($lstart, 95, $lend, 95);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            }
        }
        $this->SetY(101);
        $this->SetX(18);
        $header = array('�ɤΤ褦��', '��Ŭ�����', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            } else if($i == 2) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,number_format($data_cause[5], 0) . ' ��','LRT','C','L'); // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            }
        }
        $this->SetY(107);
        $this->SetX(18);
        $header = array('�� How ��', 'ή �� �� ��', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            } else if($i == 2) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,' ' . $data_cause[6],'LRT','C','L');        // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            }
        }
        $this->SetY(113);
        $this->SetX(18);
        $header = array('', '�ݳ�ή�Ф�', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i < 2) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            } else {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_cause[7],'LR','C','L');               // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 73;
                $lend   = $lstart + 1;
                $end    = 141;
                for($i=$lstart;$i<$end;$i++) {
                    $this->Line($lstart, 113, $lend, 113);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            }
        }
        $this->SetY(119);
        $this->SetX(18);
        $header = array('', 'ͭ �� ̵', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i < 2) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            } else {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_cause[8],'LR','C','L');               // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 73;
                $lend   = $lstart + 1;
                $end    = 141;
                for($i=$lstart;$i<$end;$i++) {
                    $this->Line($lstart, 119, $lend, 119);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            }
        }
        $this->SetY(125);
        $this->SetX(18);
        $header = array('', 'ή �� �� ��', '');
        $w=array(30, 25, 135);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            } else if($i == 2) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,number_format($data_cause[9], 0) . ' ��','LRT','C','L'); // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            }
        }
        $this->SetY(131);
        $this->SetX(18);
        $this->Cell(190,6,'����Ŭ���ʤν��֡�','LRT','C','L');                   // �ե������̾�����
        $this->SetY(137);
        $this->SetX(18);
        $this->SetFont('SJIS','B',10);
        $this->Cell(190,6,' ' . $data_measure[0],'LR','C','L');                  // �ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 18;
        $lend   = $lstart + 1;
        $end    = 113;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 137, $lend, 137);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(143);
        $this->SetX(18);
        $this->SetFont('SJIS','B',10);
        $this->Cell(190,6,$data_measure[1],'LR','C','L');                        // �ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 18;
        $lend   = $lstart + 1;
        $end    = 113;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 143, $lend, 143);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(149);
        $this->SetX(18);
        $this->SetFont('SJIS','B',10);
        $this->Cell(190,6,$data_measure[2],'LR','C','L');                        // �ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 18;
        $lend   = $lstart + 1;
        $end    = 113;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 149, $lend, 149);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(155);
        $this->SetX(18);
        $this->Cell(190,6,'','LR','C','L');                                      // �ե������̾�����
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 18;
        $lend   = $lstart + 1;
        $end    = 113;
        for($i=$lstart;$i<$end;$i++) {
            $this->Line($lstart, 155, $lend, 155);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(161);
        $this->SetX(18);
        $header = array('��ȯ�����к���', '�»ܹ��ܡ��ʾڵ������');
        $w=array(130, 60);                                                       // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','L');                 // �ե������̾�����
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            }
        }
        $this->SetY(167);
        $this->SetX(18);
        $header = array('', '�� ʿ Ÿ ��', 'ͭ ', '��', ' �� ̵', '��');
        $w=array(130, 30, 8, 6, 10, 6);                                          // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,' ' . $data_measure[3],'LR','C','L');       // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 167, $lend, 167);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else if($i == 1) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // �ե������̾�����
            } else if($i == 3) {
                if ($data_develop[0] == 't') {
                    $this->Cell($w[$i],6,'��','T','C','C');                      // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','T','C','C');                      // �ե������̾�����
                }
            } else if($i == 5) {
                if ($data_develop[0] == 't') {
                    $this->Cell($w[$i],6,'��','TR','C','C');                     // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','TR','C','C');                     // �ե������̾�����
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'T','C','C');                   // �ե������̾�����
            }
        }
        $this->SetY(173);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(130, 30, 30);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[4],'LR','C','L');             // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 173, $lend, 173);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            }
        }
        $this->SetY(179);
        $this->SetX(18);
        $header = array('', '�� �� Ÿ ��', 'ͭ ', '��', ' �� ̵', '��');
        $w=array(130, 30, 8, 6, 10, 6);                                          // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[5],'LR','C','L');             // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 179, $lend, 179);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else if($i == 1) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            } else if($i == 3) {
                if ($data_develop[1] == 't') {
                    $this->Cell($w[$i],6,'��','','C','C');                       // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','','C','C');                       // �ե������̾�����
                }
            } else if($i == 5) {
                if ($data_develop[1] == 't') {
                    $this->Cell($w[$i],6,'��','R','C','C');                      // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','R','C','C');                      // �ե������̾�����
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'','C','C');                    // �ե������̾�����
            }
        }
        $this->SetY(185);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(130, 30, 30);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[6],'LR','C','L');             // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 185, $lend, 185);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            }
        }
        $this->SetY(191);
        $this->SetX(18);
        $header = array('�ʼ»�ͽ������', $data_measure[7] . ' ǯ ' . $data_measure[8] . ' �� ' . $data_measure[9] . ' ��', ' ��', '�� �� Ÿ ��', 'ͭ ', '��', ' �� ̵', '��');
        $w=array(30, 10, 90, 30, 8, 6, 10, 6);                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'L','C','L');                   // �ե������̾�����
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 191, $lend, 191);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else if($i == 1) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$header[$i],'','C','L');                    // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else if($i == 2) {
                $this->Cell($w[$i],6,$header[$i],'R','C','C');                   // �ե������̾�����
            } else if($i == 3) {
                $this->Cell($w[$i],6,$header[$i],'R','C','C');                   // �ե������̾�����
            } else if($i == 5) {
                if ($data_develop[2] == 't') {
                    $this->Cell($w[$i],6,'��','','C','C');                       // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','','C','C');                       // �ե������̾�����
                }
            } else if($i == 7) {
                if ($data_develop[2] == 't') {
                    $this->Cell($w[$i],6,'��','R','C','C');                      // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','R','C','C');                      // �ե������̾�����
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'','C','C');                    // �ե������̾�����
            }
        }
        $this->SetY(197);
        $this->SetX(18);
        $header = array('��ή���к���', '', '');
        $w=array(130, 30, 30);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','L');                 // �ե������̾�����
            } else {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            }
        }
        $this->SetY(203);
        $this->SetX(18);
        $header = array('', 'ɸ���Ÿ��', 'ͭ ', '��', ' �� ̵', '��');
        $w=array(130, 30, 8, 6, 10, 6);                                          // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,' ' . $data_measure[10],'LR','C','L');      // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 203, $lend, 203);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else if($i == 1) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            } else if($i == 3) {
                if ($data_develop[3] == 't') {
                    $this->Cell($w[$i],6,'��','','C','C');                       // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','','C','C');                       // �ե������̾�����
                }
            } else if($i == 5) {
                if ($data_develop[3] == 't') {
                    $this->Cell($w[$i],6,'��','R','C','C');                      // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','R','C','C');                      // �ե������̾�����
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'','C','C');                    // �ե������̾�����
            }
        }
        $this->SetY(209);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(130, 30, 30);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[11],'LR','C','L');            // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 209, $lend, 209);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else {
                $this->Cell($w[$i],6,'','LR','C','C');                           // �ե������̾�����
            }
        }
        $this->SetY(215);
        $this->SetX(18);
        $header = array('', '�� �� �� ��', 'ͭ ', '��', ' �� ̵', '��');
        $w=array(130, 30, 8, 6, 10, 6);                                          // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[12],'LR','C','L');            // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 215, $lend, 215);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else if($i == 1) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            } else if($i == 3) {
                if ($data_develop[4] == 't') {
                    $this->Cell($w[$i],6,'��','','C','C');                       // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','','C','C');                       // �ե������̾�����
                }
            } else if($i == 5) {
                if ($data_develop[4] == 't') {
                    $this->Cell($w[$i],6,'��','R','C','C');                      // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','R','C','C');                      // �ե������̾�����
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'','C','C');                    // �ե������̾�����
            }
        }
        $this->SetY(221);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(130, 30, 30);                                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[13],'LR','C','L');            // �ե������̾�����
                $this->SetFont('SJIS','',10);
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 221, $lend, 221);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // �ե������̾�����
            }
        }
        $this->SetY(227);
        $this->SetX(18);
        $header = array('�ʼ»�ͽ������', $data_measure[14] . ' ǯ ' . $data_measure[15] . ' �� ' . $data_measure[16] . ' ��', ' ��', '�� �� �� ��', 'ͭ ', '��', ' �� ̵', '��');
        $w=array(30, 10, 90, 30, 8, 6, 10, 6);                                   // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'L','C','L');                   // �ե������̾�����
                // ������������å�$lstart����$end�ޤ�����������Ƥ���
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 227, $lend, 227);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else if($i == 1) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$header[$i],'','C','L');                    // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else if($i == 2) {
                $this->Cell($w[$i],6,$header[$i],'R','C','C');                   // �ե������̾�����
            } else if($i == 3) {
                $this->Cell($w[$i],6,$header[$i],'R','C','C');                   // �ե������̾�����
            } else if($i == 5) {
                if ($data_develop[5] == 't') {
                    $this->Cell($w[$i],6,'��','','C','C');                       // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','','C','C');                       // �ե������̾�����
                }
            } else if($i == 7) {
                if ($data_develop[5] == 't') {
                    $this->Cell($w[$i],6,'��','R','C','C');                      // �ե������̾�����
                } else {
                    $this->Cell($w[$i],6,'��','R','C','C');                      // �ե������̾�����
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'','C','C');                    // �ե������̾�����
            }
        }
        $this->SetY(233);
        $this->SetX(18);
        $header = array('�Υե������åסϡ�ï', '��', '�ˤ��ʤ���', $data_measure[18] . ' ǯ ' . $data_measure[19] . ' �� ' . $data_measure[20] . ' ��', '��', '');
        $w=array(40, 40, 20, 40, 10, 40);                                        // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LT','C','L');                  // �ե������̾�����
            } else if($i == 1){
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[17],'T','C','C');             // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else if($i == 3){
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$header[$i],'T','C','C');                   // �ե������̾�����
                $this->SetFont('SJIS','',10);
            } else if($i < 5){
                $this->Cell($w[$i],6,$header[$i],'T','C','C');                   // �ե������̾�����
            } else {
                $this->Cell($w[$i],6,$header[$i],'RT','C','C');                  // �ե������̾�����
            }
        }
        $this->SetY(239);
        $this->SetX(18);
        $this->SetFont('SJIS','B',10);
        $this->Cell(190,6,' ' . $data_measure[21],'LR','C','L');                 // �ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 18;
        $lend   = $lstart + 1;
        $end    = 113;
        for($t=$lstart;$t<$end;$t++) {
            $this->Line($lstart, 239, $lend, 239);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(245);
        $this->SetX(18);
        $this->SetFont('SJIS','B',10);
        $this->Cell(190,6,$data_measure[22],'LRB','C','L');                      // �ե������̾�����
        $this->SetFont('SJIS','',10);
        // ������������å�$lstart����$end�ޤ�����������Ƥ���
        $lstart = 18;
        $lend   = $lstart + 1;
        $end    = 113;
        for($t=$lstart;$t<$end;$t++) {
            $this->Line($lstart, 245, $lend, 245);
            $lstart = $lstart + 2;
            $lend   = $lstart + 1;
        }
        $this->SetY(251);
        $this->SetX(18);
        $header = array('', '����Ĺ', '������Ĺ', '�ʼ�������Ǥ��', '������Ĺ', '�ʾڲ�Ĺ');
        $w=array(65, 25, 25, 25, 25, 25);                                        // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],5,$header[$i],0,'C','C');                     // �ե������̾�����
            } else {
                $this->Cell($w[$i],5,$header[$i],1,'C','C');                     // �ե������̾�����
            }
        }
        $this->SetY(256);
        $this->SetX(18);
        $header = array('', '', '', '', '', '');
        $w=array(65, 25, 25, 25, 25, 25);                                        // �ƥ���β�������ꤷ�Ƥ��ޤ���
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],20,$header[$i],'','C','C');                   // �ե������̾�����
            } else {
                $this->Cell($w[$i],20,$header[$i],'LRB','C','C');                // �ե������̾�����
            }
        }
    }
    
    // Ƭ�ˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    function Header()
    {
        // Select Arial bold 15
        $this->SetFont('SJIS','',10);
        // Move to the right
        // Framed title
        $this->SetX(18);
        $this->Cell(114,4,'�ͼ��������ݣãգ¡ݣ�����',0,0,'L');
    }
    function Footer() // ���ĤˤĤ��������ƤϤ����ˡ����󥹥ȥ饯���ߤ����˼�ư�¹Ԥ���ޤ���
    {
        $this->SetFont('SJIS','',10);
        // Print centered page number
        $this->SetY(-15);                                                        // ������10mm�˰���
        //$this->Cell(0,10,'('.$this->PageNo().')',0,0,'C');
        $this->Cell(0,10,'������',0,0,'C');
    }

}

// https�����Ѥ���ݤΤ��ޤ��ʤ��Ǥ���
Header('Pragma: public');

#FPDF
$pdf = new PDF_j();                                                              // ����Ѱդ�����ĥ���饹������

///// PDFʸ��Υץ�ѥƥ�����
$pdf->SetAuthor('Tochigi Nitto Kohki Co.,Ltd.');
$pdf->SetCreator("$current_script");
$pdf->SetTitle('Employee name list');
$pdf->SetSubject('Section distinction');
// �ڡ����Υ쥤������=�裲���������ꤷ�ʤ�����continuous=Ϣ³��������
$pdf->SetDisplayMode('fullwidth', 'default');
$pdf->SetCompression(true);                                                      // ���̤�ͭ���ˤ���(default=on)

///// PDFʸ��λ��ѥե���Ȥ�����
$pdf->AddSJISFont();                                                             // ���ܸ줬ɬ�פʾ��Τ��ޤ��ʤ�
$pdf->Open();                                                                    // PDF�򳫻�
$pdf->SetFont('SJIS','',10);                                                     // �ǥե���ȥե���Ȥ�SJIS10�ݥ���Ȥˤ��Ƥ�����

///// ��Ŭ���������Ҷ�ͭ�إå����ơ��֥� �μ���
$sql = "    SELECT subject                      AS subject
                ,place                          AS place
                ,section                        AS section
                ,sponsor                        AS sponsor
                ,to_char(occur_time, 'YYYY')    AS occur_year
                ,to_char(occur_time, 'MM')      AS occur_month
                ,to_char(occur_time, 'DD')      AS occur_day
                ,receipt_no                     AS receipt_no
            FROM
                unfit_report_header
            WHERE
                serial_no = {$serial_no}
        ";
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                                                   // ��³Ū��³
$res = pg_query($con, $sql);
$row = pg_fetch_object($res);

// �����顼�ѿ��ǤϤʤ��ơ�������Ȥ������Ȥ�����
$data_header = array();
$data_header = array($row->subject, $row->place, $row->section, $row->sponsor, $row->occur_year, $row->occur_month, $row->occur_day, $row->receipt_no);

///// ��Ŭ���������Ҷ�ͭ�����ơ��֥� �μ���
$sql = "    SELECT assy_no      AS assy_no
                ,parts_no       AS parts_no
                ,occur_cause    AS occur_cause
                ,unfit_num      AS unfit_num
                ,issue_cause    AS issue_cause
                ,issue_num      AS issue_num
            FROM
                unfit_report_cause AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // ��³Ū��³
$res = pg_query($con, $sql);
$row = pg_fetch_object($res);

$sql = "    SELECT
                midsc      AS assy_name
            FROM miitem
            WHERE mipn='{$row->assy_no}'
        ";
if ($res = getUniResult($sql, $res) <= 0) {
    $assy_name = '';
} else {
    $conn_str  = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
    $con       = pg_pConnect($conn_str);                  // ��³Ū��³
    $res_assy  = pg_query($con, $sql);
    $row_assy  = pg_fetch_object($res_assy);
    $assy_name = $row_assy->assy_name;
}

$sql = "    SELECT
                midsc      AS parts_name
            FROM miitem
            WHERE mipn='{$row->parts_no}'
        ";
$conn_str   = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con        = pg_pConnect($conn_str);                  // ��³Ū��³
if ($res = getUniResult($sql, $res) <= 0) {
    $parts_name = '';
} else {
    $res_parts  = pg_query($con, $sql);
    $row_parts  = pg_fetch_object($res_parts);
    $parts_name = $row_parts->parts_name;
}

// ��Ͽ���ʳ��������ֹ�������ֹ�˶���ʸ�������äƤ��ޤ��ٺ��
$assy_name    = preg_replace('/\s\s+/', ' ', $assy_name);
$parts_name   = preg_replace('/\s\s+/', ' ', $parts_name);
// ����̾������̾��ʸ��������
$assy_byte    = strlen($assy_name);
if ($assy_byte > 35) {
    $assy_name    = substr($assy_name, 0, 28);
}
$parts_byte    = strlen($parts_name);
if ($parts_byte > 35) {
    $parts_name   = substr($parts_name, 0, 28);
}
// ȯ��������ή�и����β���
$occur_temp   = nl2br($row->occur_cause);
$occur_cause  = array();
$occur_cause  = explode( '<br />', $occur_temp);
$count_occur  = count($occur_cause);
if ($count_occur == 0) {
    $occur_cause[0] = '';
    $occur_cause[1] = '';
    $occur_cause[2] = '';
} else if ($count_occur == 1) {
    $occur_cause[1] = '';
    $occur_cause[2] = '';
} else if ($count_occur == 2) {
    $occur_cause[2] = '';
}

$issue_temp   = nl2br($row->issue_cause);
$issue_cause  = array();
$issue_cause  = explode( '<br />', $issue_temp);
$count_issue  = count($issue_cause);
if ($count_issue == 0) {
    $issue_cause[0] = '';
    $issue_cause[1] = '';
    $issue_cause[2] = '';
} else if ($count_issue == 1) {
    $issue_cause[1] = '';
    $issue_cause[2] = '';
} else if ($count_issue == 2) {
    $issue_cause[2] = '';
}

$data_cause = array();//�����顼�ѿ��ǤϤʤ��ơ�������Ȥ������Ȥ�����
$data_cause = array($row->assy_no, $row->parts_no, $occur_cause[0], $occur_cause[1], $occur_cause[2], $row->unfit_num, $issue_cause[0], $issue_cause[1], $issue_cause[2], $row->issue_num, $assy_name, $parts_name);

///// ��Ŭ���������Ҷ�ͭ�к��ơ��֥� �μ���
$sql = "    SELECT unfit_dispose                     AS unfit_dispose
                ,occur_measure                       AS occur_measure
                ,to_char(occurMeasure_date, 'YYYY')  AS occurmeasure_year
                ,to_char(occurMeasure_date, 'MM')    AS occurmeasure_month
                ,to_char(occurMeasure_date, 'DD')    AS occurmeasure_day
                ,issue_measure                       AS issue_measure
                ,to_char(issueMeasure_date, 'YYYY')  AS issuemeasure_year
                ,to_char(issueMeasure_date, 'MM')    AS issuemeasure_month
                ,to_char(issueMeasure_date, 'DD')    AS issuemeasure_day
                ,follow_who                          AS follow_who
                ,to_char(follow_when, 'YYYY')        AS follow_year
                ,to_char(follow_when, 'MM')          AS follow_month
                ,to_char(follow_when, 'DD')          AS follow_day
                ,follow_how                          AS follow_how
            FROM
                unfit_report_measure AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // ��³Ū��³
$res = pg_query($con, $sql);
$row = pg_fetch_object($res);

// ��Ŭ���ʤν��֡�ȯ�����к���ή���к����ե������åפβ���
$dispose_temp   = nl2br($row->unfit_dispose);
$unfit_dispose  = array();
$unfit_dispose  = explode( '<br />', $dispose_temp);
$count_dispose  = count($unfit_dispose);
if ($count_dispose == 0) {
    $unfit_dispose[0] = '';
    $unfit_dispose[1] = '';
    $unfit_dispose[2] = '';
} else if ($count_dispose == 1) {
    $unfit_dispose[1] = '';
    $unfit_dispose[2] = '';
} else if ($count_dispose == 2) {
    $unfit_dispose[2] = '';
}
$occur_measure_temp   = nl2br($row->occur_measure);
$occur_measure        = array();
$occur_measure        = explode( '<br />', $occur_measure_temp);
$count_occur_measure  = count($occur_measure);
if ($count_occur_measure == 0) {
    $occur_measure[0] = '';
    $occur_measure[1] = '';
    $occur_measure[2] = '';
    $occur_measure[3] = '';
} else if ($count_occur_measure == 1) {
    $occur_measure[1] = '';
    $occur_measure[2] = '';
    $occur_measure[3] = '';
} else if ($count_occur_measure == 2) {
    $occur_measure[2] = '';
    $occur_measure[3] = '';
} else if ($count_occur_measure == 3) {
    $occur_measure[3] = '';
}
$issue_measure_temp   = nl2br($row->issue_measure);
$issue_measure        = array();
$issue_measure        = explode( '<br />', $issue_measure_temp);
$count_issue_measure  = count($issue_measure);
if ($count_issue_measure == 0) {
    $issue_measure[0] = '';
    $issue_measure[1] = '';
    $issue_measure[2] = '';
    $issue_measure[3] = '';
} else if ($count_issue_measure == 1) {
    $issue_measure[1] = '';
    $issue_measure[2] = '';
    $issue_measure[3] = '';
} else if ($count_issue_measure == 2) {
    $issue_measure[2] = '';
    $issue_measure[3] = '';
} else if ($count_issue_measure == 3) {
    $issue_measure[3] = '';
}
$follow_temp   = nl2br($row->follow_how);
$follow        = array();
$follow        = explode( '<br />', $follow_temp);
$count_follow  = count($follow);
if ($count_follow == 0) {
    $follow[0] = '';
    $follow[1] = '';
} else if ($count_follow == 1) {
    $follow[1] = '';
}
$data_measure = array();//�����顼�ѿ��ǤϤʤ��ơ�������Ȥ������Ȥ�����
$data_measure = array($unfit_dispose[0], $unfit_dispose[1], $unfit_dispose[2], $occur_measure[0], $occur_measure[1]
                    , $occur_measure[2], $occur_measure[3], $row->occurmeasure_year, $row->occurmeasure_month, $row->occurmeasure_day
                    , $issue_measure[0], $issue_measure[1], $issue_measure[2], $issue_measure[3], $row->issuemeasure_year
                    , $row->issuemeasure_month, $row->issuemeasure_day, $row->follow_who, $row->follow_year, $row->follow_month
                    , $row->follow_day, $follow[0], $follow[1]);

///// ��Ŭ���������Ҷ�ͭŸ���ơ��֥� �μ���
$sql = "    SELECT suihei                AS suihei
                ,kanai                   AS kanai
                ,kagai                   AS kagai
                ,hyoujyun                AS hyoujyun
                ,kyouiku                 AS kyouiku
                ,system                  AS system
            FROM
                unfit_report_develop AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // ��³Ū��³
$res = pg_query($con, $sql);
$row = pg_fetch_object($res);

$data_develop = array();//�����顼�ѿ��ǤϤʤ��ơ�������Ȥ������Ȥ�����
$data_develop = array($row->suihei, $row->kanai, $row->kagai, $row->hyoujyun, $row->kyouiku, $row->system);

// ����
$pdf->AddPage();    // �ڡ���������������1��ϥ����뤹��ɬ�פ����ꤽ���Ǥ�
//$pdf->SetFont('SJIS', '', 12);
$pdf->FancyTable($data_header,$data_cause,$data_measure,$data_develop); //��ǥ������ष�����дؿ���ƤӽФ��ޤ���
// $pdf->Image('/home/www/html/tnk-web/img/logo_pro-works.png',170,5,30,0,'','');
// ʣ���Ǥ��������Header()�˵��Ҥ��롣���᡼�������֤��ޤ���������ꤷ�ޤ�������ե���󥹻���
$pdf->Output();     // �Ǹ�ˡ��嵭�ǡ�������Ϥ��ޤ���
exit;//�ʤ�٤������뤹��٤��Ǥ����ޤ����Ǹ��PHP���å��˲��Ԥʤɤ��ޤޤ��ȥ���Ǥ���
?>