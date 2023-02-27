<?php
//////////////////////////////////////////////////////////////////////////////////
// 月次損益関係 消費税申告書 確定申告書付表1-(1) PDF出力(印刷) FPDF/MBFPDF使用  //
// Copyright (C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp        //
// Changed history                                                              //
// 2019/06/13 Created  sales_tax_kakutei_fuhyo1-1_pdf  ゴシック体               //
//////////////////////////////////////////////////////////////////////////////////
ini_set('memory_limit', '100M');             // PDFの大量出力のため 52MでOKだが 64Mへ
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');   // access_log()を使うためdefine→functionへ切替
require_once ('../../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');         // TNK 全共通 menu class
// require_once ('/var/www/html/define.php');
access_log();                           // Script Name は自動取得

//////////////// 認証チェック
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
/*
if (!getCheckAuthority(58)) {        // 権限レベルが１以下は拒否(上級ユーザーのみ)
// if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
    $_SESSION['s_sysmsg'] = '有給管理台帳を印刷する権限がありません！';
    header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
    // header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}
*/
////////// 台帳対象年度の取得
///// 対象当月
$ki2_ym   = 202211;
$yyyymm   = 202211;
$ki = 22;
$b_yyyymm = $yyyymm - 100;
$p1_ki   = 21;

///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 前期末年月

///// 期・半期の取得
$tuki_chk   =12;
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $hanki = '４';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $hanki = '１';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $hanki = '２';
} elseif ($tuki_chk >= 10) {    //第３四半期
    $hanki = '３';
}

///// 年月範囲の取得
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 10) {    //第３四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
}
///// TNK期 → NK期へ変換
$nk_ki   = $ki + 44;
$nk_p1ki = $p1_ki + 44;

$cost_ym = array();
$tuki_chk   =12;
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $hanki = '４';
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
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $hanki = '１';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cnum        = 3;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $hanki = '２';
    $cost_ym[0] = $yyyy . '04';
    $cost_ym[1] = $yyyy . '05';
    $cost_ym[2] = $yyyy . '06';
    $cost_ym[3] = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cnum        = 6;
} elseif ($tuki_chk >= 10) {    //第３四半期
    $hanki = '３';
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

// 翌期4月分
$cost_ym_next = $yyyy + 1 . '04';

///// MBFPDF/FPDF で使用する組込フォント
define('FPDF_FONTPATH', '../../mbfpdf/font/');     // Core Font のパス
///// 日本語表示の場合必須。すなわち、必ずインクルードする
require_once ('../../mbfpdf/mbfpdf.php');          // マルチバイトFPDF

class PDF_j extends MBFPDF  // 日本語PDFクラスを拡張します。
{
    // Private properties
    var $wh_usr;     // Header Column Text
    var $w_usr;      // Header Column Width
    var $data_usr;   // Header 用 ユーザーデータ
    var $usr_cnt;    // Header 用 ユーザー切替
    
    /// Constructer を定義すると 基底クラスの Constructerが実行されない
    function PDF_j()
    {
        // $this->FPDF();  // 基底ClassのConstructerはプログラマーの責任で呼出す。
        parent::FPDF_Protection();
        $this->wh_usr   = array();
        $this->w_usr    = array();
        $this->usr_cnt  = 1;    // 押印欄表示用
        $this->data_usr = array('', '', '', '');    // テスト用のユーザーを照会するとワーニングになるため追加
    }
    
    // Simple table...未使用
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
    
    // Better table...未使用
    function ImprovedTable($header, $data)
    {
        // Column widths プロパティへ変更
        // $w = array(25, 15, 24, 105, 30);   //各セルの横幅を指定しています。
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
    
    //このメンバ関数を修正しています。
    // Colored table
    function FancyTable($data, $caption)
    {
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('');
        // Header Column プロパティへ変更
        // $w = array(25, 15, 24, 105, 30);   // 各セルの横幅を指定しています。
        // Data
        $this->SetFont(GOTHIC, 'B', 12);
        //$this->Cell($this->w_usr[0], 6, '', 'LTB', 0, 'L', 1);
        //$this->Cell(120, 6, $caption, 'RTB', 0, 'L', 1);
        $this->SetTextColor(50, 0, 255);    // キャプションだけ色を変える(青)
        //$this->Cell($this->w_usr[2], 5, '', 'TB', 0, 'L', 1);
        //$this->Cell($this->w_usr[3], 5, '', 'RTB', 0, 'L', 1);
        $this->SetTextColor(0, 0, 0);
        //$this->Ln();    // 改行
        $this->SetFillColor(235);   // グレースケールモード
        $this->SetFont(GOTHIC, '', 12);
        $fill = 0;
        foreach ($data as $row) {
                $this->SetFont(GOTHIC, '', 8);
                $this->Cell($this->w_usr[0], 6, $row[0], 'LRTB', 0, 'C', $fill);    // 以下、各フィールドごとに出力
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
        $this->Ln();    // 改行
    }

    function Header()   //頭につけたい内容はここに。コンストラクタみたいに自動実行されます。
    {
        /*
        $this->Image('/var/www/html/img/t_nitto_logo2.png', 155, 5, 50, 0, '', '');  //イメージを配置します。場所を指定します。→リファレンス参照
        */
        $this->SetX(60);
        // Select Arial bold 15
        $this->SetFont(GOTHIC, 'B', 14);
        // Move to the right
        /// $this->Cell(80);
        // Framed title
        $list_title = '付表１-１ 税率別消費税額計算表 兼地方消費税の課税標準となる消費税額計算表';
        $this->Cell(80, 10, $list_title, 'TB', 0, 'C');
        /*
        $this->Ln(80);
        $this->SetFont(GOTHIC, '', 8);
        $this->SetY(16);
        $this->Cell(0, 0, date('Y年m月d日 H時i分s秒'), 0, 0, 'R');
        $this->SetY(19);
        $this->Cell(0, 0, '栃木日東工器株式会社', 0, 0, 'R');
        */
        // $this->SetY(22);
        // $this->Cell(0, 0, '〒329-1311 栃木県さくら市氏家3473-2', 0, 0, 'R');
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
        $this->Cell(0, 0, '（単位：円）', 0, 0, 'R');
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
            $this->Cell($this->w_usr[$i], 7, $this->wh_usr[$i], 1, 0, 'C', 1);  // フィールド名を出力
        }
        $this->Ln();    // 幅をあけます。→リファレンス参照のこと
    }
    function Footer()   // ケツにつけたい内容はここに。コンストラクタみたいに自動実行されます。
    {
        // Go to 1.5 cm from bottom
        // Select Arial italic 8
        /*
        $this->SetFont('Times', 'I', 8);
        // Print centered page number
        $this->SetY(-10);    // 下から10mmにセット(5mmだとプリンターによっては印刷されない)
        $this->Cell(0, 10, '('.$this->PageNo().')', 0, 0, 'C');
        $this->Cell(0, 10, 'Copyright TOCHIG NITTO KOHKI Co.,Ltd. All rights reserved', 0, 0, 'R');
        */
    }

}

Header('Pragma: public');   // httpsを利用する際のおまじないです。

///////// FPDF
$pdf = new PDF_j();     // 上で用意した拡張クラスを生成

///// PDF文書のプロパティ設定
$pdf->SetAuthor('栃木日東工器株式会社');    // Tochigi Nitto Kohki Co.,Ltd.
$pdf->SetTitle('Employee name list');
$pdf->SetSubject('Teaching exercise record');
$pdf->SetDisplayMode('fullwidth', 'default');       // ページのレイアウト=第２引数を設定しない場合はcontinuous=連続して配置
$pdf->SetCompression(true);         // 圧縮を有効にする(default=on)
$pdf->SetProtection(array('print'), '', 'tnkowner');    // 印刷のみ許可のプロテクト fpdf_protection.phpが必要('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32)

///// PDF文書の使用フォントの設定
$pdf->AddMBFont(GOTHIC ,'EUC');
// $pdf->AddMBFont(PGOTHIC,'SJIS');
// $pdf->AddMBFont(MINCHO ,'SJIS');
// $pdf->AddMBFont(PMINCHO,'SJIS');
// $pdf->AddMBFont(KOZMIN ,'SJIS');
$pdf->Open();                   // PDFを開始(省略可能・AddPage()でOK)
$pdf->SetLeftMargin(15.0);      // 左のマージンを１５.０ミリに変更
$pdf->SetRightMargin(5.0);      // 右のマージンを５.０ミリに変更
$pdf->SetFont(GOTHIC,'',10);    // デフォルトフォントをMSゴシック 10ポイントにしておく。
// Header

/////////// PostgreSQLと接続
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;

            // mysql_fetch_objectはいらないが pg_fetch_objectは行番号がいる
            // はずだったが マニュアルを良く見たら4.1.0以降はオプションとなった。
            // 内部的にレコードカウンターを１増加させている。
$data_f = array();  // スカラー変数ではなくて、配列だということを明示
    // Column titles
    $pdf->wh_usr = array('区', '', '分', '旧税率小計Ｘ', '税率6.24％適用分Ｄ', '税率7.8％適用分Ｅ', '合計 Ｆ（Ｘ+Ｄ+Ｅ）');
    $pdf->w_usr  = array(15, 65, 7, 20, 30, 26, 28);   //各セルの横幅を指定しています。
    /* 受講履歴を取得 SQL */
    $res_w = array();
    ///// 出力 社員番号・部署・役職・氏名 の本文中の見出し
    // 取得日／必要日数の形式に変換
    // 基準開始日～基準終了日の形式に変換
    //$pdf->data_usr = array($now_uid, $now_section, $now_position, $now_name, $now_reference, $total_num);
    
    $s_1 = '';
    $s_2 = '課税標準額';
    $s_3 = '①';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '5,159,434,000';
    $s_7 = '5,159,434,000';
    $data_f[0] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '①の';
    $s_2 = '課税資産の譲渡等の対価の額';
    $s_3 = '①-1';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '5,159,434,097';
    $s_7 = '5,159,434,097';
    $data_f[1] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '内訳';
    $s_2 = '特定課税仕入れに係る支払対価の額';
    $s_3 = '①-2';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[2] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '消費税額';
    $s_3 = '②';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '402,435,852';
    $s_7 = '402,435,852';
    $data_f[3] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '控除過大調整税額';
    $s_3 = '③';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[4] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '控除税額';
    $s_2 = '控除対象仕入税額';
    $s_3 = '④';
    $s_4 = '1,214,517';
    $s_5 = '8,991';
    $s_6 = '356,596,992';
    $s_7 = '357,820,500';
    $data_f[5] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '控除税額';
    $s_2 = '返還等対価に係る税額';
    $s_3 = '⑤';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[6] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '控除税額';
    $s_2 = '⑤の内訳 売上げの返還等対価に係る税額';
    $s_3 = '⑤-1';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[7] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '控除税額';
    $s_2 = '⑤の内訳 特定課税仕入れの返還等対価に係る税額';
    $s_3 = '⑤-2';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[8] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '控除税額';
    $s_2 = '貸倒れに係る税額';
    $s_3 = '⑥';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '0';
    $data_f[9] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '控除税額';
    $s_2 = '控除税額小計（④+⑤+⑥）';
    $s_3 = '⑦';
    $s_4 = '1,214,517';
    $s_5 = '8,991';
    $s_6 = '356,596,992';
    $s_7 = '357,820,500';
    $data_f[10] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '控除不足還付税額（⑦-②-③）';
    $s_3 = '⑧';
    $s_4 = '1,214,517';
    $s_5 = '8,991';
    $s_6 = '';
    $s_7 = '1,223,508';
    $data_f[11] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '差引税額（②+③-⑦）';
    $s_3 = '⑨';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '45,838,860';
    $s_7 = '45,838,860';
    $data_f[12] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '合計差引税額（⑨-⑧）';
    $s_3 = '⑩';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '44,615,352';
    $data_f[13] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '地方消費税の課税';
    $s_2 = '控除不足還付税額';
    $s_3 = '⑪';
    $s_4 = '1,214,517';
    $s_5 = '';
    $s_6 = '8,991';
    $s_7 = '1,223,508';
    $data_f[14] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '標準となる消費税額';
    $s_2 = '差引税額';
    $s_3 = '⑫';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '45,838,860';
    $s_7 = '45,838,860';
    $data_f[15] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '合計差引地方消費税の課税標準となる消費税額（⑫-⑪）';
    $s_3 = '⑬';
    $s_4 = '-1,214,517';
    $s_5 = '';
    $s_6 = '45,829,869';
    $s_7 = '44,615,352';
    $data_f[16] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '譲渡';
    $s_2 = '還付額';
    $s_3 = '⑭';
    $s_4 = '327,726';
    $s_5 = '';
    $s_6 = '2,535';
    $s_7 = '330,261';
    $data_f[17] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '割額';
    $s_2 = '納税額';
    $s_3 = '⑮';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '12,928,909';
    $s_7 = '12,928,909';
    $data_f[18] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $s_1 = '';
    $s_2 = '合計差引譲渡割額（⑮-⑭）';
    $s_3 = '⑯';
    $s_4 = '';
    $s_5 = '';
    $s_6 = '';
    $s_7 = '12,598,648';
    $data_f[19] = array($s_1, $s_2, $s_3, $s_4, $s_5, $s_6, $s_7);
    
    $pdf->AddPage();    // ページを生成。最低1回はコールする必要がある(逆に$pdf->Open()は省略可能)
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// 出力 本文
    $pdf->FancyTable($data_f, 'この申告書による消費税の税額の計算');  // 上でカスタムしたメンバ関数を呼び出す
    

$pdf->Output();     // 最後に、上記データを出力します。
exit;               // なるべくコールする。また、最後のPHPカッコに改行などが含まれるとダメ
?> 