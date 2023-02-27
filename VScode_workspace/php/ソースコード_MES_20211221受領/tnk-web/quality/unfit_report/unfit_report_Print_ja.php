<?php
///////////////////////////////////////////////////////////////////////////////
// 全社共有 不適合報告書の作成 不適合報告書ＰＤＦ出力(印刷) FPDF/FPDF-JA使用 //
// Copyright (C) 2008-2015 Norihisa.Ohya usoumu@nitto-kohki.co.jp            //
// Changed history                                                           //
// 2008/05/30 Created   unfit_report_Print_ja.php                            //
// 2008/08/29 masterstで本稼動開始                                           //
// 2008/11/27 実施項目システムの有無が表示されない箇所を訂正                 //
// 2015/01/26 管理部長→技術部長へ変更                                       //
///////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');               // ajaxで使用する場合
// ini_set('error_reporting', E_STRICT);                   // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);                         // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');                         // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');            // zend 1.X コンパチ php4の互換モード
ob_start('ob_gzhandler');                                  // 出力バッファをgzip圧縮
session_start();                                           // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');                          // access_log()等で使用
access_log();                                              // Script Name は自動取得

$current_script  = $_SERVER['PHP_SELF'];                   // 現在実行中のスクリプト名を保存
$serial_temp  = array();
$serial_temp  = explode( '&', $_SERVER["QUERY_STRING"]);
$serial_temp2 = explode( 'serial_no=', $serial_temp[0]);
$serial_no    = $serial_temp2[1];

#日本語表示の場合必須。すなわち、必ずインクルードすること。
require('/home/www/html/fpdf152/japanese.php');
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');    // Core Font のパス

class PDF_j extends PDF_Japanese                           // 日本語PDFクラスを拡張します。
{
    
    // Simple table...未使用
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
    
    // Better table...未使用
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
    
    #このメンバ関数を修正しています。
    //Colored table
    function FancyTable($data_header,$data_cause,$data_measure,$data_develop)
    {   
        $this->SetFont('SJIS','',10);
        $this->Cell(30,4,'受付No　　','B','C','L');
        $this->SetFont('SJIS','',12);
        $this->Cell(45,4,$data_header[7],'B','C','L');
        $this->SetY(16);
        $this->SetX(18);
        $this->SetFont('SJIS','',10);
        $this->Cell(114,4,'',0,0,'R');
        $this->Cell(16,4,'発行日：','B',0,'L');
        $this->SetFont('SJIS','',12);
        $this->Cell(59,4,date('Y　年　m　月　d　日'),'B',0,'L');
        $this->SetY(21);
        $this->SetX(18);
        // Column titles
        $header = array('', '品　証', '部　長', '課　長');
        // Column data array
        
        // Header
        $this->SetFont('SJIS','',10);
        $w=array(130, 20, 20, 20);                                               // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],5,$header[$i],0,'C','C');                     // フィールド名を出力
            } else {
                $this->Cell($w[$i],5,$header[$i],1,'C','C');                     // フィールド名を出力
            }
        }
        $this->SetY(26);
        $this->SetX(18);
        $w=array(130, 20, 20, 20);                                               // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],5,'',0,'C','C');                              // フィールド名を出力
            } else {
                $this->Cell($w[$i],5,'','LR','C','C');                           // フィールド名を出力
            }
        }

        $this->SetY(31);
        $this->SetX(18);
        $this->SetFont('SJIS','',24);
        $header = array('不　適　合　報　告　書', '', '', '');
        $w=array(130, 20, 20, 20);                                               // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],11,$header[$i],0,'C','L');                    // フィールド名を出力
            } else {
                $this->Cell($w[$i],11,$header[$i],'LRB','C','C');                // フィールド名を出力
            }
        }
        $this->SetY(45);
        $this->SetX(18);
        $this->SetFont('SJIS','',10);
        $header = array('不  適  合  内  容', '');
        $w=array(30, 160);                                                       // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],1,'C','C');                     // フィールド名を出力
            } else {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,'　　' . $data_header[0],'LRT','C','L');    // フィールド名を出力
                $this->SetFont('SJIS','',10);
            }
        }
        $this->SetY(51);
        $this->SetX(18);
        $header = array('いつ （ When ）', '発生年月日', '');
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],1,'C','C');                     // フィールド名を出力
            } else if($i == 1 ) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            } else {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,'　　' . $data_header[4] . ' 年 ' . $data_header[5] . ' 月 ' . $data_header[6] . ' 日','LRT','C','L');//フィールド名を出力
                $this->SetFont('SJIS','',10);
            }
        }
        $this->SetY(57);
        $this->SetX(18);
        $header = array('どこで（Where）', '発 生  場 所', '');
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],1,'C','C');                     // フィールド名を出力
            } else if($i == 1 ) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            } else {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,'　　' . $data_header[1],'LRT','C','L');    // フィールド名を出力
                $this->SetFont('SJIS','',10);
            }
        }
        $this->SetY(63);
        $this->SetX(18);
        $header = array('誰 が （ Who ）', '責 任  部 門', '');
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],1,'C','C');                     // フィールド名を出力
            } else if($i == 1 ) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            } else {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,'　　' . $data_header[2],'LRT','C','L');    // フィールド名を出力
                $this->SetFont('SJIS','',10);
            }
        }
        $this->SetY(69);
        $this->SetX(18);
        $header = array('何  が', '製　品　名', '', '部　品　名', '');
        $w=array(30, 25, 55, 25, 55);                                            // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            } else if($i == 2) {
                $this->SetFont('SJIS','B',9);
                $this->Cell($w[$i],6,$data_cause[10],'LRBT','C','C');            // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else if($i == 4) {
                $this->SetFont('SJIS','B',9);
                $this->Cell($w[$i],6,$data_cause[11],'LRBT','C','C');            // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            }
        }
        $this->SetY(75);
        $this->SetX(18);
        $header = array('（ what ）', '製 品  番 号', '', '部 品  番 号', '');
        $w=array(30, 25, 55, 25, 55);                                            // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LRB','C','C');                 // フィールド名を出力
            } else if($i == 2) {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,$data_cause[0],'LRBT','C','C');             // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else if($i == 4) {
                $this->SetFont('SJIS','B',13);
                $this->Cell($w[$i],6,$data_cause[1],'LRBT','C','C');             // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRTB','C','C');                // フィールド名を出力
            }
        }
        
        $this->SetY(83);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            } else if($i == 2) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,' ' . $data_cause[2],'LRT','C','L');        // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            }
        }
        
        $this->SetY(89);
        $this->SetX(18);
        $header = array('', '発 生 原 因', '');
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i < 2) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            } else {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_cause[3],'LR','C','L');               // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i < 2) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            } else {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_cause[4],'LR','C','L');               // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $header = array('どのように', '不適合数量', '');
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            } else if($i == 2) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,number_format($data_cause[5], 0) . ' 個','LRT','C','L'); // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            }
        }
        $this->SetY(107);
        $this->SetX(18);
        $header = array('（ How ）', '流 出 原 因', '');
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            } else if($i == 2) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,' ' . $data_cause[6],'LRT','C','L');        // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            }
        }
        $this->SetY(113);
        $this->SetX(18);
        $header = array('', '課外流出の', '');
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i < 2) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            } else {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_cause[7],'LR','C','L');               // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $header = array('', '有 ・ 無', '');
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i < 2) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            } else {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_cause[8],'LR','C','L');               // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $header = array('', '流 出 数 量', '');
        $w=array(30, 25, 135);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            } else if($i == 2) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,number_format($data_cause[9], 0) . ' 個','LRT','C','L'); // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            }
        }
        $this->SetY(131);
        $this->SetX(18);
        $this->Cell(190,6,'【不適合品の処置】','LRT','C','L');                   // フィールド名を出力
        $this->SetY(137);
        $this->SetX(18);
        $this->SetFont('SJIS','B',10);
        $this->Cell(190,6,' ' . $data_measure[0],'LR','C','L');                  // フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(190,6,$data_measure[1],'LR','C','L');                        // フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(190,6,$data_measure[2],'LR','C','L');                        // フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(190,6,'','LR','C','L');                                      // フィールド名を出力
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $header = array('【発生源対策】', '実施項目（品証記入欄）');
        $w=array(130, 60);                                                       // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','L');                 // フィールド名を出力
            } else {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            }
        }
        $this->SetY(167);
        $this->SetX(18);
        $header = array('', '水 平 展 開', '有 ', '□', ' ・ 無', '■');
        $w=array(130, 30, 8, 6, 10, 6);                                          // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,' ' . $data_measure[3],'LR','C','L');       // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 167, $lend, 167);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else if($i == 1) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','C');                 // フィールド名を出力
            } else if($i == 3) {
                if ($data_develop[0] == 't') {
                    $this->Cell($w[$i],6,'■','T','C','C');                      // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'□','T','C','C');                      // フィールド名を出力
                }
            } else if($i == 5) {
                if ($data_develop[0] == 't') {
                    $this->Cell($w[$i],6,'□','TR','C','C');                     // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'■','TR','C','C');                     // フィールド名を出力
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'T','C','C');                   // フィールド名を出力
            }
        }
        $this->SetY(173);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(130, 30, 30);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[4],'LR','C','L');             // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 173, $lend, 173);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            }
        }
        $this->SetY(179);
        $this->SetX(18);
        $header = array('', '課 内 展 開', '有 ', '□', ' ・ 無', '■');
        $w=array(130, 30, 8, 6, 10, 6);                                          // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[5],'LR','C','L');             // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 179, $lend, 179);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else if($i == 1) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            } else if($i == 3) {
                if ($data_develop[1] == 't') {
                    $this->Cell($w[$i],6,'■','','C','C');                       // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'□','','C','C');                       // フィールド名を出力
                }
            } else if($i == 5) {
                if ($data_develop[1] == 't') {
                    $this->Cell($w[$i],6,'□','R','C','C');                      // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'■','R','C','C');                      // フィールド名を出力
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'','C','C');                    // フィールド名を出力
            }
        }
        $this->SetY(185);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(130, 30, 30);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[6],'LR','C','L');             // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 185, $lend, 185);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            }
        }
        $this->SetY(191);
        $this->SetX(18);
        $header = array('（実施予定日：', $data_measure[7] . ' 年 ' . $data_measure[8] . ' 月 ' . $data_measure[9] . ' 日', ' ）', '課 外 展 開', '有 ', '□', ' ・ 無', '■');
        $w=array(30, 10, 90, 30, 8, 6, 10, 6);                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'L','C','L');                   // フィールド名を出力
                // 破線を引くロジック$lstartから$endまで破線を引いていく
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
                $this->Cell($w[$i],6,$header[$i],'','C','L');                    // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else if($i == 2) {
                $this->Cell($w[$i],6,$header[$i],'R','C','C');                   // フィールド名を出力
            } else if($i == 3) {
                $this->Cell($w[$i],6,$header[$i],'R','C','C');                   // フィールド名を出力
            } else if($i == 5) {
                if ($data_develop[2] == 't') {
                    $this->Cell($w[$i],6,'■','','C','C');                       // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'□','','C','C');                       // フィールド名を出力
                }
            } else if($i == 7) {
                if ($data_develop[2] == 't') {
                    $this->Cell($w[$i],6,'□','R','C','C');                      // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'■','R','C','C');                      // フィールド名を出力
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'','C','C');                    // フィールド名を出力
            }
        }
        $this->SetY(197);
        $this->SetX(18);
        $header = array('【流出対策】', '', '');
        $w=array(130, 30, 30);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LRT','C','L');                 // フィールド名を出力
            } else {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            }
        }
        $this->SetY(203);
        $this->SetX(18);
        $header = array('', '標準書展開', '有 ', '□', ' ・ 無', '■');
        $w=array(130, 30, 8, 6, 10, 6);                                          // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,' ' . $data_measure[10],'LR','C','L');      // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 203, $lend, 203);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else if($i == 1) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            } else if($i == 3) {
                if ($data_develop[3] == 't') {
                    $this->Cell($w[$i],6,'■','','C','C');                       // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'□','','C','C');                       // フィールド名を出力
                }
            } else if($i == 5) {
                if ($data_develop[3] == 't') {
                    $this->Cell($w[$i],6,'□','R','C','C');                      // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'■','R','C','C');                      // フィールド名を出力
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'','C','C');                    // フィールド名を出力
            }
        }
        $this->SetY(209);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(130, 30, 30);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[11],'LR','C','L');            // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 209, $lend, 209);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else {
                $this->Cell($w[$i],6,'','LR','C','C');                           // フィールド名を出力
            }
        }
        $this->SetY(215);
        $this->SetX(18);
        $header = array('', '教 育 実 施', '有 ', '□', ' ・ 無', '■');
        $w=array(130, 30, 8, 6, 10, 6);                                          // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[12],'LR','C','L');            // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 215, $lend, 215);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else if($i == 1) {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            } else if($i == 3) {
                if ($data_develop[4] == 't') {
                    $this->Cell($w[$i],6,'■','','C','C');                       // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'□','','C','C');                       // フィールド名を出力
                }
            } else if($i == 5) {
                if ($data_develop[4] == 't') {
                    $this->Cell($w[$i],6,'□','R','C','C');                      // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'■','R','C','C');                      // フィールド名を出力
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'','C','C');                    // フィールド名を出力
            }
        }
        $this->SetY(221);
        $this->SetX(18);
        $header = array('', '', '');
        $w=array(130, 30, 30);                                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[13],'LR','C','L');            // フィールド名を出力
                $this->SetFont('SJIS','',10);
                // 破線を引くロジック$lstartから$endまで破線を引いていく
                $lstart = 18;
                $lend   = $lstart + 1;
                $end    = 83;
                for($t=$lstart;$t<$end;$t++) {
                    $this->Line($lstart, 221, $lend, 221);
                    $lstart = $lstart + 2;
                    $lend   = $lstart + 1;
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'LR','C','C');                  // フィールド名を出力
            }
        }
        $this->SetY(227);
        $this->SetX(18);
        $header = array('（実施予定日：', $data_measure[14] . ' 年 ' . $data_measure[15] . ' 月 ' . $data_measure[16] . ' 日', ' ）', 'シ ス テ ム', '有 ', '□', ' ・ 無', '■');
        $w=array(30, 10, 90, 30, 8, 6, 10, 6);                                   // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'L','C','L');                   // フィールド名を出力
                // 破線を引くロジック$lstartから$endまで破線を引いていく
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
                $this->Cell($w[$i],6,$header[$i],'','C','L');                    // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else if($i == 2) {
                $this->Cell($w[$i],6,$header[$i],'R','C','C');                   // フィールド名を出力
            } else if($i == 3) {
                $this->Cell($w[$i],6,$header[$i],'R','C','C');                   // フィールド名を出力
            } else if($i == 5) {
                if ($data_develop[5] == 't') {
                    $this->Cell($w[$i],6,'■','','C','C');                       // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'□','','C','C');                       // フィールド名を出力
                }
            } else if($i == 7) {
                if ($data_develop[5] == 't') {
                    $this->Cell($w[$i],6,'□','R','C','C');                      // フィールド名を出力
                } else {
                    $this->Cell($w[$i],6,'■','R','C','C');                      // フィールド名を出力
                }
            } else {
                $this->Cell($w[$i],6,$header[$i],'','C','C');                    // フィールド名を出力
            }
        }
        $this->SetY(233);
        $this->SetX(18);
        $header = array('［フォローアップ］（誰', '　', '）が（いつ', $data_measure[18] . ' 年 ' . $data_measure[19] . ' 月 ' . $data_measure[20] . ' 日', '）', '');
        $w=array(40, 40, 20, 40, 10, 40);                                        // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],6,$header[$i],'LT','C','L');                  // フィールド名を出力
            } else if($i == 1){
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$data_measure[17],'T','C','C');             // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else if($i == 3){
                $this->SetFont('SJIS','B',10);
                $this->Cell($w[$i],6,$header[$i],'T','C','C');                   // フィールド名を出力
                $this->SetFont('SJIS','',10);
            } else if($i < 5){
                $this->Cell($w[$i],6,$header[$i],'T','C','C');                   // フィールド名を出力
            } else {
                $this->Cell($w[$i],6,$header[$i],'RT','C','C');                  // フィールド名を出力
            }
        }
        $this->SetY(239);
        $this->SetX(18);
        $this->SetFont('SJIS','B',10);
        $this->Cell(190,6,' ' . $data_measure[21],'LR','C','L');                 // フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(190,6,$data_measure[22],'LRB','C','L');                      // フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $header = array('', '工場長', '副工場長', '品質管理責任者', '技術部長', '品証課長');
        $w=array(65, 25, 25, 25, 25, 25);                                        // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],5,$header[$i],0,'C','C');                     // フィールド名を出力
            } else {
                $this->Cell($w[$i],5,$header[$i],1,'C','C');                     // フィールド名を出力
            }
        }
        $this->SetY(256);
        $this->SetX(18);
        $header = array('', '', '', '', '', '');
        $w=array(65, 25, 25, 25, 25, 25);                                        // 各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            if($i == 0) {
                $this->Cell($w[$i],20,$header[$i],'','C','C');                   // フィールド名を出力
            } else {
                $this->Cell($w[$i],20,$header[$i],'LRB','C','C');                // フィールド名を出力
            }
        }
    }
    
    // 頭につけたい内容はここに。コンストラクタみたいに自動実行されます。
    function Header()
    {
        // Select Arial bold 15
        $this->SetFont('SJIS','',10);
        // Move to the right
        // Framed title
        $this->SetX(18);
        $this->Cell(114,4,'様式４１４−ＣＵＢ−００９',0,0,'L');
    }
    function Footer() // ケツにつけたい内容はここに。コンストラクタみたいに自動実行されます。
    {
        $this->SetFont('SJIS','',10);
        // Print centered page number
        $this->SetY(-15);                                                        // 下から10mmに印刷
        //$this->Cell(0,10,'('.$this->PageNo().')',0,0,'C');
        $this->Cell(0,10,'１／２',0,0,'C');
    }

}

// httpsを利用する際のおまじないです。
Header('Pragma: public');

#FPDF
$pdf = new PDF_j();                                                              // 上で用意した拡張クラスを生成

///// PDF文書のプロパティ設定
$pdf->SetAuthor('Tochigi Nitto Kohki Co.,Ltd.');
$pdf->SetCreator("$current_script");
$pdf->SetTitle('Employee name list');
$pdf->SetSubject('Section distinction');
// ページのレイアウト=第２引数を設定しない場合はcontinuous=連続して配置
$pdf->SetDisplayMode('fullwidth', 'default');
$pdf->SetCompression(true);                                                      // 圧縮を有効にする(default=on)

///// PDF文書の使用フォントの設定
$pdf->AddSJISFont();                                                             // 日本語が必要な場合のおまじない
$pdf->Open();                                                                    // PDFを開始
$pdf->SetFont('SJIS','',10);                                                     // デフォルトフォントをSJIS10ポイントにしておく。

///// 不適合報告書全社共有ヘッダーテーブル の取得
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
$con = pg_pConnect($conn_str);                                                   // 持続的接続
$res = pg_query($con, $sql);
$row = pg_fetch_object($res);

// スカラー変数ではなくて、配列だということを明示
$data_header = array();
$data_header = array($row->subject, $row->place, $row->section, $row->sponsor, $row->occur_year, $row->occur_month, $row->occur_day, $row->receipt_no);

///// 不適合報告書全社共有原因テーブル の取得
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
$con = pg_pConnect($conn_str);                  // 持続的接続
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
    $con       = pg_pConnect($conn_str);                  // 持続的接続
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
$con        = pg_pConnect($conn_str);                  // 持続的接続
if ($res = getUniResult($sql, $res) <= 0) {
    $parts_name = '';
} else {
    $res_parts  = pg_query($con, $sql);
    $row_parts  = pg_fetch_object($res_parts);
    $parts_name = $row_parts->parts_name;
}

// 登録の段階で部品番号と製品番号に空白文字が入ってしまう為削除
$assy_name    = preg_replace('/\s\s+/', ' ', $assy_name);
$parts_name   = preg_replace('/\s\s+/', ' ', $parts_name);
// 製品名と部品名の文字数制限
$assy_byte    = strlen($assy_name);
if ($assy_byte > 35) {
    $assy_name    = substr($assy_name, 0, 28);
}
$parts_byte    = strlen($parts_name);
if ($parts_byte > 35) {
    $parts_name   = substr($parts_name, 0, 28);
}
// 発生原因と流出原因の改行
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

$data_cause = array();//スカラー変数ではなくて、配列だということを明示
$data_cause = array($row->assy_no, $row->parts_no, $occur_cause[0], $occur_cause[1], $occur_cause[2], $row->unfit_num, $issue_cause[0], $issue_cause[1], $issue_cause[2], $row->issue_num, $assy_name, $parts_name);

///// 不適合報告書全社共有対策テーブル の取得
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
$con = pg_pConnect($conn_str);                  // 持続的接続
$res = pg_query($con, $sql);
$row = pg_fetch_object($res);

// 不適合品の処置・発生源対策・流出対策・フォローアップの改行
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
$data_measure = array();//スカラー変数ではなくて、配列だということを明示
$data_measure = array($unfit_dispose[0], $unfit_dispose[1], $unfit_dispose[2], $occur_measure[0], $occur_measure[1]
                    , $occur_measure[2], $occur_measure[3], $row->occurmeasure_year, $row->occurmeasure_month, $row->occurmeasure_day
                    , $issue_measure[0], $issue_measure[1], $issue_measure[2], $issue_measure[3], $row->issuemeasure_year
                    , $row->issuemeasure_month, $row->issuemeasure_day, $row->follow_who, $row->follow_year, $row->follow_month
                    , $row->follow_day, $follow[0], $follow[1]);

///// 不適合報告書全社共有展開テーブル の取得
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
$con = pg_pConnect($conn_str);                  // 持続的接続
$res = pg_query($con, $sql);
$row = pg_fetch_object($res);

$data_develop = array();//スカラー変数ではなくて、配列だということを明示
$data_develop = array($row->suihei, $row->kanai, $row->kagai, $row->hyoujyun, $row->kyouiku, $row->system);

// 出力
$pdf->AddPage();    // ページを生成。最低1回はコールする必要がありそうです
//$pdf->SetFont('SJIS', '', 12);
$pdf->FancyTable($data_header,$data_cause,$data_measure,$data_develop); //上でカスタムしたメンバ関数を呼び出します。
// $pdf->Image('/home/www/html/tnk-web/img/logo_pro-works.png',170,5,30,0,'','');
// 複数頁がある時はHeader()に記述する。イメージを配置します。場所を指定します。→リファレンス参照
$pdf->Output();     // 最後に、上記データを出力します。
exit;//なるべくコールするべきです。また、最後のPHPカッコに改行などが含まれるとダメです。
?>