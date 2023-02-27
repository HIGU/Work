<?php
//////////////////////////////////////////////////////////////////////////////
// 少額資産台帳の ＰＤＦ出力(印刷) FPDF/MBFPDF 使用                         //
// Copyright (C) 2010 - 2012 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2010/10/19 Created  smallSum_assetsList_delno.php  ゴシック体            //
// 2012/02/14 文字がはみ出してしまう為SQLに文字数制限を追加                 //
// 2012/03/02 承認の押印欄を変更                                            //
// 2012/10/03 念のためメーカー・型式、備考に文字数制限を追加                //
//////////////////////////////////////////////////////////////////////////////
ini_set('memory_limit', '100M');             // PDFの大量出力のため 52MでOKだが 64Mへ
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('/var/www/html/function.php');   // access_log()を使うためdefine→functionへ切替
// require_once ('/var/www/html/define.php');
access_log();                           // Script Name は自動取得

//////////////// 認証チェック
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
//if ($_SESSION['Auth'] <= 1) {        // 権限レベルが１以下は拒否(上級ユーザーのみ)
// if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
//    if ($_SESSION['User_ID'] != '970268') {
//        $_SESSION['s_sysmsg'] = '社員名簿を印刷する権限がありません！';
//        header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
//        // header("Location: $url_referer");                   // 直前の呼出元へ戻る
//        exit();
//    }
//}
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存

///// MBFPDF/FPDF で使用する組込フォント
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');     // Core Font のパス
///// 日本語表示の場合必須。すなわち、必ずインクルードする
require_once ('/home/www/html/mbfpdf/mbfpdf.php');          // マルチバイトFPDF

class PDF_j extends MBFPDF  // 日本語PDFクラスを拡張します。
{
    // Private properties
    var $wh_usr;     // Header Column Text
    var $w_usr;      // Header Column Width
    var $data_usr;   // Header 用 ユーザーデータ
    
    /// Constructer を定義すると 基底クラスの Constructerが実行されない
    function PDF_j()
    {
        // $this->FPDF();  // 基底ClassのConstructerはプログラマーの責任で呼出す。
        parent::FPDF_Protection();
        $this->wh_usr = array();
        $this->w_usr  = array();
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
        //$this->Cell($this->w_usr[1], 6, '', 'TB', 0, 'L', 1);
        //$this->SetTextColor(50, 0, 255);    // キャプションだけ色を変える(青)
        //$this->Cell($this->w_usr[2], 6, $caption, 'RTB', 0, 'L', 1);
        $this->SetTextColor(0, 0, 0);
        //$this->Ln();    // 改行
        $this->SetFillColor(235);   // グレースケールモード
        $this->SetFont(GOTHIC, '', 12);
        $fill = 0;
        foreach ($data as $row) {
            $this->SetX(5);
            $this->Cell($this->w_usr[0], 7, $row[0], 'LRTB', 0, 'L', $fill);    // 以下、各フィールドごとに出力
            $this->Cell($this->w_usr[1], 7, $row[1], 'LRTB', 0, 'L', $fill);
            $this->Cell($this->w_usr[2], 7, $row[2], 'LRTB', 0, 'L', $fill);
            $this->Cell($this->w_usr[3], 7, $row[3], 'LRTB', 0, 'C', $fill);
            $this->Cell($this->w_usr[4], 7, $row[4], 'LRTB', 0, 'R', $fill);
            $this->Cell($this->w_usr[5], 7, $row[5], 'LRTB', 0, 'L', $fill);
            $this->Cell($this->w_usr[6], 7, "　", 'LRTB', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->SetX(5);
        $this->Cell(array_sum($this->w_usr), 0, '', 'T');
        $this->Ln();    // 改行
    }

    function Header()   //頭につけたい内容はここに。コンストラクタみたいに自動実行されます。
    {
        $this->Image('/var/www/html/img/t_nitto_logo2.png', 235, 14, 50, 0, '', 'R');  //イメージを配置します。場所を指定します。→リファレンス参照
        $this->SetX(60);
        // Select Arial bold 15
        $this->SetFont(GOTHIC, 'B', 16);
        // Move to the right
        /// $this->Cell(80);
        // Framed title
        $this->SetY(16);
        $this->SetX(100);
        $this->Cell(100, 10, '少 額 資 産 管 理 台 帳', 'TB', 0, 'C');
        $this->Ln(15);
        $this->SetFont(GOTHIC, '', 8);
        $this->SetY(26);
        $this->Cell(268, 0, date('Y年m月d日 H時i分s秒'), 0, 0, 'R');
        $this->SetY(29);
        $this->Cell(268, 0, '総務課', 0, 0, 'R');
        // $this->SetY(22);
        // $this->Cell(0, 0, '〒329-1311 栃木県さくら市氏家3473-2', 0, 0, 'R');
        // $this->SetY(25);
        // $this->Cell(0, 0, 'Tel:028-682-8851/Fax:028-681-7038', 0, 0, 'R');
        $this->SetFont(GOTHIC, '', 9);
        $this->SetXY(205, 32);
        $this->Cell(20,  5, '部　長', 'LRTB', 0, 'C');
        $this->Cell(20,  5, '課　長', 'LRTB', 0, 'C');
        $this->Cell(20,  5, '係　長', 'LRTB', 0, 'C');
        $this->Cell(20,  5, '担　当', 'LRTB', 0, 'C');
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
        $this->Cell(20, 7, '管理部門', 'LRTB', 0, 'C', 1);
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
            $this->Cell($this->w_usr[$i], 7, $this->wh_usr[$i], 1, 0, 'C', 1);  // フィールド名を出力
        }
        $this->Ln();    // 幅をあけます。→リファレンス参照のこと
    }
    function Footer()   // ケツにつけたい内容はここに。コンストラクタみたいに自動実行されます。
    {
        // Go to 1.5 cm from bottom
        // Select Arial italic 8
        $this->SetFont('Times', 'I', 8);
        // Print centered page number
        $this->SetY(-10);    // 下から10mmにセット(5mmだとプリンターによっては印刷されない)
        $this->Cell(0, 10, '('.$this->PageNo().')', 0, 0, 'C');
        $this->Cell(0, 10, 'Copyright TOCHIG NITTO KOHKI Co.,Ltd. All rights reserved', 0, 0, 'R');
    }
    function get_setname($assetcode)   // 設置場所名の取得
    {
        $conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
        $con = pg_pConnect($conn_str);                  // 持続的接続
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

Header('Pragma: public');   // httpsを利用する際のおまじないです。

///////// FPDF
$pdf = new PDF_j();     // 上で用意した拡張クラスを生成

///// PDF文書のプロパティ設定
$pdf->SetAuthor('栃木日東工器株式会社');    // Tochigi Nitto Kohki Co.,Ltd. k.kobayashi
$pdf->SetCreator("$current_script");
$pdf->SetTitle('Employee name list');
$pdf->SetSubject('Teaching exercise record');
$pdf->SetDisplayMode('fullwidth', 'default');       // ページのレイアウト=第２引数を設定しない場合はcontinuous=連続して配置
$pdf->SetCompression(true);         // 圧縮を有効にする(default=on)
$pdf->SetProtection(array('print'), '', 'tnkowner');    // 印刷のみ許可のプロテクト fpdf_protection.phpが必要('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32)

///// PDF文書の使用フォントの設定
$pdf->AddMBFont(GOTHIC ,'SJIS');
// $pdf->AddMBFont(PGOTHIC,'SJIS');
// $pdf->AddMBFont(MINCHO ,'SJIS');
// $pdf->AddMBFont(PMINCHO,'SJIS');
// $pdf->AddMBFont(KOZMIN ,'SJIS');
$pdf->Open();                   // PDFを開始(省略可能・AddPage()でOK)
$pdf->SetLeftMargin(15.0);      // 左のマージンを１５.０ミリに変更
$pdf->SetRightMargin(5.0);      // 右のマージンを５.０ミリに変更
$pdf->SetFont(GOTHIC,'',10);    // デフォルトフォントをMSゴシック 10ポイントにしておく。
// Header
// Column titles
$pdf->wh_usr = array('設置場所', '品　　名', 'メーカー・型式', '購入年月日', '購入金額', '備考', '確認');
$pdf->w_usr  = array(36, 75, 60, 25, 20, 55, 15);   //各セルの横幅を指定しています。

/////////// PostgreSQLと接続
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // 持続的接続

/////////// 管理部門の取得SQL
$sql = "SELECT code_act AS acode
            , name_act AS aname
        FROM
            smallsum_assets_actname_master
        ORDER BY code_act ASC";
if ( !($res_usr = pg_query($con, $sql)) ) {
    $_SESSION['s_sysmsg'] = '管理部門が取得できません：' . pg_last_error($con);
    header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
    exit();
}

            // mysql_fetch_objectはいらないが pg_fetch_objectは行番号がいる
            // はずだったが マニュアルを良く見たら4.1.0以降はオプションとなった。
            // 内部的にレコードカウンターを１増加させている。
$data_f = array();  // スカラー変数ではなくて、配列だということを明示
while ($row = pg_fetch_object($res_usr)) {
    $now_act      = $row->acode;                        // 管理部門コード
    $now_aname    = $row->aname;                        // 管理部門名
    ///// 出力 管理部門コード・管理部門名 の本文中の見出し
    $pdf->data_usr = array($now_act, $now_aname);
    
    /* 受講履歴を取得 SQL */
    $query = "SELECT set_place              AS placecode
                , substr(assets_name,0,26)  AS assetname
                , substr(assets_model,0,14) AS amodel
                , buy_ym                    AS abuyym
                , buy_price                 AS abuyprice
                , substr(note,0,20)         AS anote
              FROM smallsum_assets_master
              WHERE act_name='$now_act' AND delete_ym = 0 ORDER BY set_place ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '少額資産台帳が取得できません：' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
        exit();
    }
    $cnt = 0;   // データの行カウンタ
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
    $pdf->AddPage('L','A4');    // ページを生成。最低1回はコールする必要がある(逆に$pdf->Open()は省略可能)
    //$pdf->AddPage();    // ページを生成。最低1回はコールする必要がある(逆に$pdf->Open()は省略可能)
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// 出力 本文
    $pdf->FancyTable($data_f, '未除却');  // 上でカスタムしたメンバ関数を呼び出す
}

$pdf->Output();     // 最後に、上記データを出力します。
exit;               // なるべくコールする。また、最後のPHPカッコに改行などが含まれるとダメ
?> 