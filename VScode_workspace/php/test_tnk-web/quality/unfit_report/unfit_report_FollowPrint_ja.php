<?php
/////////////////////////////////////////////////////////////////////////////////
// 全社共有 不適合報告書の作成 フォローアップＰＤＦ出力(印刷) FPDF/FPDF-JA使用 //
// Copyright (C) 2008-2015 Norihisa.Ohya usoumu@nitto-kohki.co.jp              //
// Changed history                                                             //
// 2008/05/30 Created   unfit_report_FollowPrint_ja.php                        //
// 2008/08/29 masterstで本稼動開始                                             //
// 2015/01/26 管理部長→技術部長へ変更。改定日を15-01-26へ変更                 //
/////////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');                // ajaxで使用する場合
// ini_set('error_reporting', E_STRICT);                    // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);                          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');                          // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');             // zend 1.X コンパチ php4の互換モード
ob_start('ob_gzhandler');                                   // 出力バッファをgzip圧縮
session_start();                                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');                           // access_log()等で使用
access_log();                                               // Script Name は自動取得

$current_script  = $_SERVER['PHP_SELF'];                    // 現在実行中のスクリプト名を保存
$serial_temp  = array();
$serial_temp  = explode( '&', $_SERVER["QUERY_STRING"]);
$serial_temp2 = explode( 'serial_no=', $serial_temp[0]);
$serial_no    = $serial_temp2[1];

#日本語表示の場合必須。すなわち、必ずインクルードすること。
require('/home/www/html/fpdf152/japanese.php');
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');     // Core Font のパス

class PDF_j extends PDF_Japanese                            //日本語PDFクラスを拡張します。
{
    
    //Simple table...未使用
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
    
    //Better table...未使用
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
    
    #このメンバ関数を修正しています。
    //Colored table
    function FancyTable($data_follow)
    {   
        $this->SetY(19);
        $this->SetX(5);
        $this->SetFont('SJIS','',10);
        $this->Cell(0,9,'発行部門',0,0,'L');
        $this->SetY(28);
        $this->SetX(5);
        $this->Cell(185,9,'［ フォローアップ ］','LRT','C','L');    //フィールド名を出力
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,' ' . $data_follow[0],'LR','C','L');      //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[1],'LR','C','L');            //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[2],'LR','C','L');            //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[3],'LR','C','L');            //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[4],'LRB','C','L');           //フィールド名を出力
        $this->SetFont('SJIS','',10);
        $this->SetY(87);
        $this->SetX(5);
        $this->Cell(0,9,'品質保証課',0,0,'L');
        $this->SetY(96);
        $this->SetX(5);
        $this->Cell(185,9,'［ フォローアップ ］','LRT','C','L');    //フィールド名を出力
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,' ' . $data_follow[5],'LR','C','L');      //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[6],'LR','C','L');            //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[7],'LR','C','L');            //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[8],'LR','C','L');            //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[9],'LRB','C','L');           //フィールド名を出力
        $this->SetFont('SJIS','',10);
        $this->SetY(156);
        $this->SetX(5);
        $this->Cell(185,5,date('Y　年　m　月　d　日'),'',0,'R');
        $this->SetY(161);
        $this->SetX(5);
        $header = array('工　場　長', '副 工 場 長', '品質管理責任者', '技 術 部 長', '品質保証課長', '部　長', '課　長');
        $w=array(27, 26, 27, 26, 27, 26, 26);                       //各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            $this->Cell($w[$i],6,$header[$i],1,'C','C');            //フィールド名を出力
        }
        $this->SetY(167);
        $this->SetX(5);
        $header = array('', '', '', '', '', '', '');
        $w=array(27, 26, 27, 26, 27, 26, 26);                       //各セルの横幅を指定しています。
        for($i=0;$i<count($header);$i++) {
            $this->Cell($w[$i],20,$header[$i],'LRB','C','C');       //フィールド名を出力
        }
        
        $this->SetY(192);
        $this->SetX(5);
        $this->SetFont('SJIS','',10);
        $this->Cell(0,8,'［ 意見欄 ］',0,0,'L');
        $this->SetY(200);
        $this->SetX(5);
        $this->SetFont('SJIS','B',10);
        $this->Cell(185,9,' ' . $data_follow[10],'TLR','C','L');    //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[11],'LR','C','L');           //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[12],'LR','C','L');           //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[13],'LR','C','L');           //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[14],'LR','C','L');           //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[15],'LR','C','L');           //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,9,$data_follow[16],'LR','C','L');           //フィールド名を出力
        $this->SetFont('SJIS','',10);
        // 破線を引くロジック$lstartから$endまで破線を引いていく
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
        $this->Cell(185,8,$data_follow[17],'LRB','C','L');          //フィールド名を出力
        $this->SetFont('SJIS','',10);
    }
    
    //頭につけたい内容はここに。コンストラクタみたいに自動実行されます。
    function Header()
    {
        
    }
    
    //ケツにつけたい内容はここに。コンストラクタみたいに自動実行されます。
    function Footer()
    {
        //Go to 1.5 cm from bottom
        //Select Arial italic 8
        $this->SetFont('SJIS','',10);
        //Print centered page number
        $this->SetY(-15);                                           // 下から15mmに印刷
        $this->SetX(5);
        //$this->Cell(0,10,'('.$this->PageNo().')',0,0,'C');
        $this->Cell(185,10,'２／２',0,0,'C');
        $this->SetY(-25);                                           // 下から20mmに印刷
        $this->SetX(5);
        //$this->Cell(0,10,'('.$this->PageNo().')',0,0,'C');
        //報告書の改定を行った際はこの部分を直す
        $this->Cell(185,10,'制定０３－１１－１０　改定１５－０１－２６',0,0,'R');    //改定年月日
    }

}

Header('Pragma: public');                                           // httpsを利用する際のおまじないです。

#FPDF
$pdf = new PDF_j();                                                 // 上で用意した拡張クラスを生成

///// PDF文書のプロパティ設定
$pdf->SetAuthor('Tochigi Nitto Kohki Co.,Ltd.');
$pdf->SetCreator("$current_script");
$pdf->SetTitle('Employee name list');
$pdf->SetSubject('Section distinction');
$pdf->SetDisplayMode('fullwidth', 'default');                       // ページのレイアウト=第２引数を設定しない場合はcontinuous=連続して配置
$pdf->SetCompression(true);                                         // 圧縮を有効にする(default=on)

///// PDF文書の使用フォントの設定
$pdf->AddSJISFont();                                                // 日本語が必要な場合のおまじない
$pdf->Open();                                                       // PDFを開始
$pdf->SetFont('SJIS','',10);                                        //デフォルトフォントをSJIS12ポイントにしておく。

///// 不適合報告書全社共有フォローアップテーブル の取得
$sql = "    SELECT follow_section               AS follow_section
                ,follow_quality                 AS follow_quality
                ,follow_opinion                 AS follow_opinion
            FROM
                unfit_report_follow
            WHERE
                serial_no = {$serial_no}
        ";
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                                      // 持続的接続
$res = pg_query($con, $sql);
$row = pg_fetch_object($res);

// 発行部門・品質保証課フォローアップと意見欄の改行
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

$data_follow = array();            //スカラー変数ではなくて、配列だということを明示
$data_follow = array($follow_section[0], $follow_section[1], $follow_section[2], $follow_section[3], $follow_section[4]
                   , $follow_quality[0], $follow_quality[1], $follow_quality[2], $follow_quality[3], $follow_quality[4]
                   , $follow_opinion[0], $follow_opinion[1], $follow_opinion[2], $follow_opinion[3], $follow_opinion[4]
                   , $follow_opinion[5], $follow_opinion[6], $follow_opinion[7]);

// 出力
$pdf->AddPage();                   // ページを生成。最低1回はコールする必要がありそうです
//$pdf->SetFont('SJIS', '', 12);
$pdf->FancyTable($data_follow);    //上でカスタムしたメンバ関数を呼び出します。
//$pdf->Image('/var/www/html/img/logo_pro-works.png',170,5,30,0,'','');
// 複数頁がある時はHeader()に記述する。イメージを配置します。場所を指定します。→リファレンス参照
$pdf->Output();                    // 最後に、上記データを出力します。
exit;                              //なるべくコールするべきです。また、最後のPHPカッコに改行などが含まれるとダメです。
?>