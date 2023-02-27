<?php
//////////////////////////////////////////////////////////////////////////////
// ¼Ò°÷¥á¥Ë¥å¡¼ ¶µ°é¡¦»ñ³Ê¡¦°ÛÆ° ·ÐÎò¤Î°ìÍ÷É½ PDF½ÐÎÏ(°õºþ) FPDF/MBFPDF»ÈÍÑ //
// Copyright (C) 2004-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/02/16 Created  print_emp_history_mbfpdf.php  ¥´¥·¥Ã¥¯ÂÎ             //
// 2004/03/01 fpdf_protection.php ¤ò»È¤¤ °õºþ¤Î¤ß¤Î¥×¥í¥Æ¥¯¥È¤ò¤«¤±¤¿¡£     //
// 2005/10/13 ½»½ê¤ò±öÃ«·´»á²ÈÄ®¢ª¤µ¤¯¤é»Ô¤ØÊÑ¹¹ fullpage¢ªfullwidth ¤ØÊÑ¹¹ //
// 2007/03/07 °ÜÆ°¤ò°ÛÆ°¤ØÄûÀµ                                              //
// 2007/10/15 ¥á¥â¥ê¡¼¥ê¥ß¥Ã¥È¤òÄÉ²Ã¡£ E_ALL ¢ª E_ALL | E_STRICT¤Ø          //
// 2007/10/16 ¶µ°é¡¦»ñ³Ê¡¦°ÜÆ°(¥­¥ã¥×¥·¥ç¥ó)¤òÀÄ¿§¤Ø  data_usr½é´ü²½¤òÊÑ¹¹  //
// 2008/04/24 Éô½ðÌ¾¤ÈÌò¿¦¤ÎÃ»½ÌÊ¸»ú¿ô¤òÊÑ¹¹                           ÂçÃ« //
// 2010/06/16 »ÃÄêÅª¤ËÂçÞ¼¤µ¤ó¡Ê970268¡Ë¤¬°õºþ¤Ç¤­¤ë¤è¤¦¤ËÊÑ¹¹         ÂçÃ« //
// 2010/06/17 ¼Ò°÷¿ôÁý²Ã¤Ë¤è¤ê¥á¥â¥ê¡¼¥ê¥ß¥Ã¥È¤ò64M¤«¤é100M¤ËÊÑ¹¹      ÂçÃ« //
// 2017/09/13 ¼ÒÄ¹¡¦¸ÜÌä¡¦¤½¤ÎÂ¾¡¦ÆüÅì¹©´ï½êÂ°¤ò½ü³°                   ÂçÃ« //
// 2018/04/20 ²¡°õÍó¤ò¼Ò°÷Ëè¤ÎÀèÆ¬¥Ú¡¼¥¸¤Î¤ß°õºþ¤¹¤ë¤è¤¦ÊÑ¹¹           ÂçÃ« //
// 2019/07/22 °õºþµö²Ä¤ògetCheckAuthority(59)¤Ë¡¢²¡°õ¤ò¼«Æ°¤ËÊÑ¹¹      ÂçÃ« //
//            ¼Ò°÷ÈÖ¹æ.jpg¤Ê¤Î¤ÇÊÑ¹¹¤Î¾ì¹ç¤Ï²èÁüºîÀ®(Excel)¤ÈPGMÊÑ¹¹¤ò ÂçÃ« //
// 2020/05/18 ²¡°õ¤òÀî”³²ÝÄ¹ÂåÍý¤ËÊÑ¹¹                                 ÂçÃ« //
//////////////////////////////////////////////////////////////////////////////
ini_set('memory_limit', '100M');             // PDF¤ÎÂçÎÌ½ÐÎÏ¤Î¤¿¤á 52M¤ÇOK¤À¤¬ 64M¤Ø
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors','1');              // Error É½¼¨ ON debug ÍÑ ¥ê¥ê¡¼¥¹¸å¥³¥á¥ó¥È
// ini_set('implicit_flush', 'off');           // echo print ¤Ç flush ¤µ¤»¤Ê¤¤(ÃÙ¤¯¤Ê¤ë¤¿¤á) CLI CGIÈÇ
// ini_set('max_execution_time', 1200);        // ºÇÂç¼Â¹Ô»þ´Ö=20Ê¬ CLI CGIÈÇ
session_start();                        // ini_set()¤Î¼¡¤Ë»ØÄê¤¹¤ë¤³¤È Script ºÇ¾å¹Ô
require_once ('/home/www/html/tnk-web/function.php');   // access_log()¤ò»È¤¦¤¿¤ádefine¢ªfunction¤ØÀÚÂØ
// require_once ('/home/www/html/tnk-web/define.php');
access_log();                           // Script Name ¤Ï¼«Æ°¼èÆÀ

//////////////// Ç§¾Ú¥Á¥§¥Ã¥¯
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {

//if ($_SESSION['Auth'] <= 1) {        // ¸¢¸Â¥ì¥Ù¥ë¤¬£±°Ê²¼¤ÏµñÈÝ(¾åµé¥æ¡¼¥¶¡¼¤Î¤ß)
// if (account_group_check() == FALSE) {        // ÆÃÄê¤Î¥°¥ë¡¼¥×°Ê³°¤ÏµñÈÝ
    //if ($_SESSION['User_ID'] != '970268') {
    if (!getCheckAuthority(59)) {
        $_SESSION['s_sysmsg'] = '¼Ò°÷Ì¾Êí¤ò°õºþ¤¹¤ë¸¢¸Â¤¬¤¢¤ê¤Þ¤»¤ó¡ª';
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ¸ÇÄê¸Æ½Ð¸µ¤ØÌá¤ë
        // header("Location: $url_referer");                   // Ä¾Á°¤Î¸Æ½Ð¸µ¤ØÌá¤ë
        exit();
    }
    //}
//}
$current_script  = $_SERVER['PHP_SELF'];        // ¸½ºß¼Â¹ÔÃæ¤Î¥¹¥¯¥ê¥×¥ÈÌ¾¤òÊÝÂ¸

///// MBFPDF/FPDF ¤Ç»ÈÍÑ¤¹¤ëÁÈ¹þ¥Õ¥©¥ó¥È
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');     // Core Font ¤Î¥Ñ¥¹
///// ÆüËÜ¸ìÉ½¼¨¤Î¾ì¹çÉ¬¿Ü¡£¤¹¤Ê¤ï¤Á¡¢É¬¤º¥¤¥ó¥¯¥ë¡¼¥É¤¹¤ë
require_once ('/home/www/html/mbfpdf/mbfpdf.php');          // ¥Þ¥ë¥Á¥Ð¥¤¥ÈFPDF

class PDF_j extends MBFPDF  // ÆüËÜ¸ìPDF¥¯¥é¥¹¤ò³ÈÄ¥¤·¤Þ¤¹¡£
{
    // Private properties
    var $wh_usr;     // Header Column Text
    var $w_usr;      // Header Column Width
    var $data_usr;   // Header ÍÑ ¥æ¡¼¥¶¡¼¥Ç¡¼¥¿
    var $usr_cnt;    // Header ÍÑ ¥æ¡¼¥¶¡¼ÀÚÂØ
    
    /// Constructer ¤òÄêµÁ¤¹¤ë¤È ´ðÄì¥¯¥é¥¹¤Î Constructer¤¬¼Â¹Ô¤µ¤ì¤Ê¤¤
    function PDF_j()
    {
        // $this->FPDF();  // ´ðÄìClass¤ÎConstructer¤Ï¥×¥í¥°¥é¥Þ¡¼¤ÎÀÕÇ¤¤Ç¸Æ½Ð¤¹¡£
        parent::FPDF_Protection();
        $this->wh_usr   = array();
        $this->w_usr    = array();
        $this->usr_cnt  = 1;    // ²¡°õÍóÉ½¼¨ÍÑ
        $this->data_usr = array('', '', '', '');    // ¥Æ¥¹¥ÈÍÑ¤Î¥æ¡¼¥¶¡¼¤ò¾È²ñ¤¹¤ë¤È¥ï¡¼¥Ë¥ó¥°¤Ë¤Ê¤ë¤¿¤áÄÉ²Ã
    }
    
    // Simple table...Ì¤»ÈÍÑ
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
    
    // Better table...Ì¤»ÈÍÑ
    function ImprovedTable($header, $data)
    {
        // Column widths ¥×¥í¥Ñ¥Æ¥£¤ØÊÑ¹¹
        // $w = array(25, 15, 24, 105, 30);   //³Æ¥»¥ë¤Î²£Éý¤ò»ØÄê¤·¤Æ¤¤¤Þ¤¹¡£
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
    
    //¤³¤Î¥á¥ó¥Ð´Ø¿ô¤ò½¤Àµ¤·¤Æ¤¤¤Þ¤¹¡£
    // Colored table
    function FancyTable($data, $caption)
    {
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('');
        // Header Column ¥×¥í¥Ñ¥Æ¥£¤ØÊÑ¹¹
        // $w = array(25, 15, 24, 105, 30);   // ³Æ¥»¥ë¤Î²£Éý¤ò»ØÄê¤·¤Æ¤¤¤Þ¤¹¡£
        // Data
        $this->SetFont(GOTHIC, 'B', 10);
        $this->Cell($this->w_usr[0], 6, '', 'LTB', 0, 'L', 1);
        $this->Cell($this->w_usr[1], 6, '', 'TB', 0, 'L', 1);
        $this->SetTextColor(50, 0, 255);    // ¥­¥ã¥×¥·¥ç¥ó¤À¤±¿§¤òÊÑ¤¨¤ë(ÀÄ)
        $this->Cell($this->w_usr[2], 6, $caption, 'RTB', 0, 'L', 1);
        $this->SetTextColor(0, 0, 0);
        $this->Ln();    // ²þ¹Ô
        $this->SetFillColor(235);   // ¥°¥ì¡¼¥¹¥±¡¼¥ë¥â¡¼¥É
        $this->SetFont(GOTHIC, '', 9);
        $fill = 0;
        foreach ($data as $row) {
            $this->Cell($this->w_usr[0], 5, $row[0], 'LRTB', 0, 'L', $fill);    // °Ê²¼¡¢³Æ¥Õ¥£¡¼¥ë¥É¤´¤È¤Ë½ÐÎÏ
            $this->Cell($this->w_usr[1], 5, $row[1], 'LRTB', 0, 'L', $fill);
            $this->Cell($this->w_usr[2], 5, $row[2], 'LRTB', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($this->w_usr), 0, '', 'T');
        $this->Ln();    // ²þ¹Ô
    }

    function Header()   //Æ¬¤Ë¤Ä¤±¤¿¤¤ÆâÍÆ¤Ï¤³¤³¤Ë¡£¥³¥ó¥¹¥È¥é¥¯¥¿¤ß¤¿¤¤¤Ë¼«Æ°¼Â¹Ô¤µ¤ì¤Þ¤¹¡£
    {
        $this->Image('/home/www/html/tnk-web/img/t_nitto_logo2.png', 155, 5, 50, 0, '', '');  //¥¤¥á¡¼¥¸¤òÇÛÃÖ¤·¤Þ¤¹¡£¾ì½ê¤ò»ØÄê¤·¤Þ¤¹¡£¢ª¥ê¥Õ¥¡¥ì¥ó¥¹»²¾È
        $this->SetX(60);
        // Select Arial bold 15
        $this->SetFont(GOTHIC, 'B', 16);
        // Move to the right
        /// $this->Cell(80);
        // Framed title
        $this->Cell(80, 10, '¶µ°é¡¦»ñ³Ê¡¦°ÛÆ°·ÐÎò °ìÍ÷É½', 'TB', 0, 'C');
        $this->Ln(15);
        $this->SetFont(GOTHIC, '', 8);
        $this->SetY(16);
        $this->Cell(0, 0, date('YÇ¯m·îdÆü H»þiÊ¬sÉÃ'), 0, 0, 'R');
        $this->SetY(19);
        $this->Cell(0, 0, 'ÆÊÌÚÆüÅì¹©´ï³ô¼°²ñ¼Ò', 0, 0, 'R');
        // $this->SetY(22);
        // $this->Cell(0, 0, '¢©329-1311 ÆÊÌÚ¸©¤µ¤¯¤é»Ô»á²È3473-2', 0, 0, 'R');
        // $this->SetY(25);
        // $this->Cell(0, 0, 'Tel:028-682-8851/Fax:028-681-7038', 0, 0, 'R');
        $this->SetFont(GOTHIC, '', 9);
        $this->SetXY(165, 22);
        if ($this->usr_cnt == 1) {  // ²¡°õÍóÉ½¼¨ÍÑ ÀèÆ¬¤Î¤ß²¡°õÉ½¼¨
            $this->Cell(20,  5, '¾µ¡¡Ç§', 'LRTB', 0, 'C');
            $this->Cell(20,  5, 'ºî¡¡À®', 'LRTB', 0, 'C');
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
        $this->Cell(20, 7, '¼Ò°÷ÈÖ¹æ', 'LRTB', 0, 'C', 1);    // °Ê²¼¡¢³Æ¥Õ¥£¡¼¥ë¥É¤´¤È¤Ë½ÐÎÏ
        $this->Cell(15, 7, $this->data_usr[0], 'LRTB', 0, 'C', 1);
        $this->Cell(15, 7, 'Éô¡¡½ð', 'LRTB', 0, 'C', 1);
        $this->Cell(35, 7, $this->data_usr[1], 'LRTB', 0, 'C', 1);
        $this->Cell(20, 7, '¿¦¡¡°Ì', 'LRTB', 0, 'C', 1);
        $this->Cell(30, 7, $this->data_usr[2], 'LRTB', 0, 'C', 1);
        $this->Cell(20, 7, '»á¡¡Ì¾', 'LRTB', 0, 'C', 1);
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
            $this->Cell($this->w_usr[$i], 7, $this->wh_usr[$i], 1, 0, 'C', 1);  // ¥Õ¥£¡¼¥ë¥ÉÌ¾¤ò½ÐÎÏ
        }
        $this->Ln();    // Éý¤ò¤¢¤±¤Þ¤¹¡£¢ª¥ê¥Õ¥¡¥ì¥ó¥¹»²¾È¤Î¤³¤È
    }
    function Footer()   // ¥±¥Ä¤Ë¤Ä¤±¤¿¤¤ÆâÍÆ¤Ï¤³¤³¤Ë¡£¥³¥ó¥¹¥È¥é¥¯¥¿¤ß¤¿¤¤¤Ë¼«Æ°¼Â¹Ô¤µ¤ì¤Þ¤¹¡£
    {
        // Go to 1.5 cm from bottom
        // Select Arial italic 8
        $this->SetFont('Times', 'I', 8);
        // Print centered page number
        $this->SetY(-10);    // ²¼¤«¤é10mm¤Ë¥»¥Ã¥È(5mm¤À¤È¥×¥ê¥ó¥¿¡¼¤Ë¤è¤Ã¤Æ¤Ï°õºþ¤µ¤ì¤Ê¤¤)
        $this->Cell(0, 10, '('.$this->PageNo().')', 0, 0, 'C');
        $this->Cell(0, 10, 'Copyright TOCHIG NITTO KOHKI Co.,Ltd. All rights reserved', 0, 0, 'R');
    }

}

Header('Pragma: public');   // https¤òÍøÍÑ¤¹¤ëºÝ¤Î¤ª¤Þ¤¸¤Ê¤¤¤Ç¤¹¡£

///////// FPDF
$pdf = new PDF_j();     // ¾å¤ÇÍÑ°Õ¤·¤¿³ÈÄ¥¥¯¥é¥¹¤òÀ¸À®

///// PDFÊ¸½ñ¤Î¥×¥í¥Ñ¥Æ¥£ÀßÄê
$pdf->SetAuthor('ÆÊÌÚÆüÅì¹©´ï³ô¼°²ñ¼Ò');    // Tochigi Nitto Kohki Co.,Ltd.
$pdf->SetCreator("$current_script");
$pdf->SetTitle('Employee name list');
$pdf->SetSubject('Teaching exercise record');
$pdf->SetDisplayMode('fullwidth', 'default');       // ¥Ú¡¼¥¸¤Î¥ì¥¤¥¢¥¦¥È=Âè£²°ú¿ô¤òÀßÄê¤·¤Ê¤¤¾ì¹ç¤Ïcontinuous=Ï¢Â³¤·¤ÆÇÛÃÖ
$pdf->SetCompression(true);         // °µ½Ì¤òÍ­¸ú¤Ë¤¹¤ë(default=on)
$pdf->SetProtection(array('print'), '', 'tnkowner');    // °õºþ¤Î¤ßµö²Ä¤Î¥×¥í¥Æ¥¯¥È fpdf_protection.php¤¬É¬Í×('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32)

///// PDFÊ¸½ñ¤Î»ÈÍÑ¥Õ¥©¥ó¥È¤ÎÀßÄê
$pdf->AddMBFont(GOTHIC ,'SJIS');
// $pdf->AddMBFont(PGOTHIC,'SJIS');
// $pdf->AddMBFont(MINCHO ,'SJIS');
// $pdf->AddMBFont(PMINCHO,'SJIS');
// $pdf->AddMBFont(KOZMIN ,'SJIS');
$pdf->Open();                   // PDF¤ò³«»Ï(¾ÊÎ¬²ÄÇ½¡¦AddPage()¤ÇOK)
$pdf->SetLeftMargin(15.0);      // º¸¤Î¥Þ¡¼¥¸¥ó¤ò£±£µ.£°¥ß¥ê¤ËÊÑ¹¹
$pdf->SetRightMargin(5.0);      // ±¦¤Î¥Þ¡¼¥¸¥ó¤ò£µ.£°¥ß¥ê¤ËÊÑ¹¹
$pdf->SetFont(GOTHIC,'',10);    // ¥Ç¥Õ¥©¥ë¥È¥Õ¥©¥ó¥È¤òMS¥´¥·¥Ã¥¯ 10¥Ý¥¤¥ó¥È¤Ë¤·¤Æ¤ª¤¯¡£
// Header
// Column titles
$pdf->wh_usr = array('³«»ÏÆüÉÕ', '½ªÎ»ÆüÉÕ', 'Æâ¡¡¡¡ÍÆ');
$pdf->w_usr  = array(20, 20, 150);   //³Æ¥»¥ë¤Î²£Éý¤ò»ØÄê¤·¤Æ¤¤¤Þ¤¹¡£

/////////// PostgreSQL¤ÈÀÜÂ³
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // »ýÂ³ÅªÀÜÂ³

/////////// ¼Ò°÷ÈÖ¹æÅù¤Î¼èÆÀSQL
// ¼ÒÄ¹¡¦¸ÜÌä¡¦¤½¤ÎÂ¾¡¦ÆüÅì¹©´ï¤ò½ü³°
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
    $_SESSION['s_sysmsg'] = '¼Ò°÷ÈÖ¹æ¤¬¼èÆÀ¤Ç¤­¤Þ¤»¤ó¡§' . pg_last_error($con);
    header('Location: http:' . WEB_HOST . EMP_MENU);   // ¸ÇÄê¸Æ½Ð¸µ¤ØÌá¤ë
    exit();
}

            // mysql_fetch_object¤Ï¤¤¤é¤Ê¤¤¤¬ pg_fetch_object¤Ï¹ÔÈÖ¹æ¤¬¤¤¤ë
            // ¤Ï¤º¤À¤Ã¤¿¤¬ ¥Þ¥Ë¥å¥¢¥ë¤òÎÉ¤¯¸«¤¿¤é4.1.0°Ê¹ß¤Ï¥ª¥×¥·¥ç¥ó¤È¤Ê¤Ã¤¿¡£
            // ÆâÉôÅª¤Ë¥ì¥³¡¼¥É¥«¥¦¥ó¥¿¡¼¤ò£±Áý²Ã¤µ¤»¤Æ¤¤¤ë¡£
$data_f = array();  // ¥¹¥«¥é¡¼ÊÑ¿ô¤Ç¤Ï¤Ê¤¯¤Æ¡¢ÇÛÎó¤À¤È¤¤¤¦¤³¤È¤òÌÀ¼¨
while ($row = pg_fetch_object($res_usr)) {
    $now_uid      = $row->uid;                          // ¼Ò°÷ÈÖ¹æ
    $now_section  = mb_substr($row->section, -9);       // Éô½ð(Ã»½Ì)
    $now_position = mb_substr($row->position, 0, 5);    // ¿¦°Ì(Ã»½Ì)
    $now_name     = $row->name;                         // »áÌ¾
    ///// ½ÐÎÏ ¼Ò°÷ÈÖ¹æ¡¦Éô½ð¡¦Ìò¿¦¡¦»áÌ¾ ¤ÎËÜÊ¸Ãæ¤Î¸«½Ð¤·
    $pdf->data_usr = array($now_uid, $now_section, $now_position, $now_name);
    
    /* ¼õ¹ÖÍúÎò¤ò¼èÆÀ SQL */
    $query = "SELECT ur.begin_date      AS s_date
                , ur.end_date           AS e_date
                , trim(rm.receive_name) AS r_name
              FROM user_receive ur, receive_master rm
              WHERE ur.uid='$now_uid' AND ur.rid=rm.rid ORDER BY ur.begin_date ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '¶µ°é·ÐÎò¤¬¼èÆÀ¤Ç¤­¤Þ¤»¤ó¡§' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ¸ÇÄê¸Æ½Ð¸µ¤ØÌá¤ë
        exit();
    }
    $cnt = 0;   // ¥Ç¡¼¥¿¤Î¹Ô¥«¥¦¥ó¥¿
    $data_f = array();
    while ($rows = pg_fetch_object($res)) {
        $s_date = $rows->s_date;
        $e_date = $rows->e_date;
        $r_name = $rows->r_name;
        $data_f[$cnt] = array($s_date, $e_date, $r_name);
        $cnt++;
    }
    $pdf->AddPage();    // ¥Ú¡¼¥¸¤òÀ¸À®¡£ºÇÄã1²ó¤Ï¥³¡¼¥ë¤¹¤ëÉ¬Í×¤¬¤¢¤ë(µÕ¤Ë$pdf->Open()¤Ï¾ÊÎ¬²ÄÇ½)
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// ½ÐÎÏ ËÜÊ¸
    $pdf->FancyTable($data_f, '¶µ¡¡°é');  // ¾å¤Ç¥«¥¹¥¿¥à¤·¤¿¥á¥ó¥Ð´Ø¿ô¤ò¸Æ¤Ó½Ð¤¹
    
    /* »ñ³Ê°ìÍ÷¤ò¼èÆÀ */
    $query = "SELECT uc.acq_date            AS s_date
                , trim(cm.capacity_name)    AS r_name
              FROM user_capacity uc,capacity_master cm
              WHERE uc.uid='$now_uid' AND uc.cid=cm.cid ORDER BY uc.acq_date ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '»ñ³Ê·ÐÎò¤¬¼èÆÀ¤Ç¤­¤Þ¤»¤ó¡§' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ¸ÇÄê¸Æ½Ð¸µ¤ØÌá¤ë
        exit();
    }
    $cnt = 0;   // ¥Ç¡¼¥¿¤Î¹Ô¥«¥¦¥ó¥¿
    $data_f = array();
    while ($rows = pg_fetch_object($res)) {
        $s_date = $rows->s_date;
        $e_date = '';
        $r_name = $rows->r_name;
        $data_f[$cnt] = array($s_date, $e_date, $r_name);
        $cnt++;
    }
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// ½ÐÎÏ ËÜÊ¸
    $pdf->FancyTable($data_f, '»ñ¡¡³Ê');  // ¾å¤Ç¥«¥¹¥¿¥à¤·¤¿¥á¥ó¥Ð´Ø¿ô¤ò¸Æ¤Ó½Ð¤¹
    
    /* °ÛÆ°ÍúÎò¤ò¼èÆÀ */
    $query = "SELECT trans_date         AS s_date
                , trim(section_name)    AS r_name
              FROM user_transfer
              WHERE uid='$now_uid' ORDER BY trans_date ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '°ÛÆ°·ÐÎò¤¬¼èÆÀ¤Ç¤­¤Þ¤»¤ó¡§' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // ¸ÇÄê¸Æ½Ð¸µ¤ØÌá¤ë
        exit();
    }
    $cnt = 0;   // ¥Ç¡¼¥¿¤Î¹Ô¥«¥¦¥ó¥¿
    $data_f = array();
    while ($rows = pg_fetch_object($res)) {
        $s_date = $rows->s_date;
        $e_date = '';
        $r_name = $rows->r_name;
        $data_f[$cnt] = array($s_date, $e_date, $r_name);
        $cnt++;
    }
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// ½ÐÎÏ ËÜÊ¸
    $pdf->FancyTable($data_f, '°Û¡¡Æ°');  // ¾å¤Ç¥«¥¹¥¿¥à¤·¤¿¥á¥ó¥Ð´Ø¿ô¤ò¸Æ¤Ó½Ð¤¹
    $pdf->usr_cnt = 1;                    // ²¡°õÍóÉ½¼¨ÍÑ
}

$pdf->Output();     // ºÇ¸å¤Ë¡¢¾åµ­¥Ç¡¼¥¿¤ò½ÐÎÏ¤·¤Þ¤¹¡£
exit;               // ¤Ê¤ë¤Ù¤¯¥³¡¼¥ë¤¹¤ë¡£¤Þ¤¿¡¢ºÇ¸å¤ÎPHP¥«¥Ã¥³¤Ë²þ¹Ô¤Ê¤É¤¬´Þ¤Þ¤ì¤ë¤È¥À¥á
?> 