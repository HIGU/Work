<?php
//////////////////////////////////////////////////////////////////////////////
// データサム バーコードカード PDF出力(印刷) FPDF/MBFPDF使用                //
// 2004/02/18 Copyright(C) 2004 K.Kobayashi tnksys@nitto-kohki.co.jp        //
// 変更経歴                                                                 //
// 2004/02/18 新規作成 print_emp_history_mbfpdf.php                         //
// 2004/02/19 check digit に対応していないバーコードが使われているため 0へ  //
// 2004/03/01 FPDF_Protection extends MBFPDFに変更し印刷のみのプロテクトへ  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 CLI CGI版
session_start();                        // ini_set()の次に指定すること Script 最上行

require_once ('/home/www/html/tnk-web/function.php');   // access_log()を使うためdefine→functionへ切替
// require_once ('/home/www/html/tnk-web/define.php');
access_log();                           // Script Name は自動取得

////////////// リターンアドレス設定
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
// if ( !(isset($_POST['check_uid']) || isset($_POST['edit'])) ) {
//    $url_referer = $_SERVER['HTTP_REFERER'];    // GETの場合は HTTP_REFERERがセットされない
//    $_SESSION['ret_addr'] = $url_referer;       // している場合は使用しない
// } else {
//    $url_referer = $_SESSION['ret_addr'];       // 確認フォームの時はセッションから読込む
// }
$url_referer = 'datasum_barcode.php';     // 固定呼出元

//////////////// 認証チェック
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 1) {        // 権限レベルが１以下は拒否(上級ユーザーのみ)
// if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
    $_SESSION['s_sysmsg'] = 'バーコードを印刷する権限がありません！';
    // header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

/////////// セッションのデータを取得
if (isset($_SESSION['dsum_uid'])) {
    $uid = $_SESSION['dsum_uid'];
} else {
    $_SESSION['s_sysmsg'] = 'セッションに社員番号がありません！';
    exit();
}
if (isset($_SESSION['dsum_name'])) {
    $name = $_SESSION['dsum_name'];
} else {
    $_SESSION['s_sysmsg'] = 'セッションに氏名がありません！';
    exit();
}

##### MBFPDF/FPDF で使用する組込フォント
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');     // Core Font のパス
##### 日本語表示の場合必須。すなわち、必ずインクルードする
require_once ('/home/www/html/mbfpdf/mbfpdf.php');          // マルチバイトFPDF


Header('Pragma: public');   // httpsを利用する際のおまじないです。

######### FPDF
// $usr_size = array(182.0, 257.0);     // B5 を指定
// $pdf = new PDF_j($usr_size);         // 上で用意した拡張クラスを生成
$usr_size = array(65.0, 94.0);          // カードサイズを指定
$pdf = new MBFPDF('P', 'mm', $usr_size);    // 横書=L 縦書(Default)=P

///// PDF文書のプロパティ設定
$pdf->SetAuthor('栃木日東工器株式会社 小林一弘');    // Tochigi Nitto Kohki Co.,Ltd. k.kobayashi
$pdf->SetCreator("$current_script");
$pdf->SetTitle('DATA SUM Barcode Card');
$pdf->SetSubject('DATA SUM Barcode Card Print');
$pdf->SetDisplayMode('fullpage', 'default');   // ページのレイアウト=第２引数を設定しない場合はcontinuous=連続して配置
$pdf->SetCompression(true);         // 圧縮を有効にする(default=on)
$pdf->SetProtection(array('print'), '', 'tnkowner');    // 印刷のみ許可のプロテクト fpdf_protection.phpが必要('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32)

///// PDF文書の使用フォントの設定
$pdf->AddMBFont(GOTHIC ,'SJIS');
// $pdf->AddMBFont(PGOTHIC,'SJIS');
// $pdf->AddMBFont(MINCHO ,'SJIS');
// $pdf->AddMBFont(PMINCHO,'SJIS');
// $pdf->AddMBFont(KOZMIN ,'SJIS');

///// PDFを開始(省略可能・AddPage()でOK)
$pdf->Open();
// $pdf->SetLeftMargin(15.0);      // 左のマージンを１５.０ミリに変更
// $pdf->SetRightMargin(5.0);      // 右のマージンを５.０ミリに変更
$pdf->SetMargins(0, 0, 0);      // マージンを全て０へ
$pdf->SetFont(GOTHIC,'',10);    // デフォルトフォントをMSゴシック 10ポイントにしておく。
$pdf->AddPage();
$pdf->SetXY(0, 0);              // X Y 座標のセット
$pdf->SetLineWidth(0.5);        // 線の太さを0.5mm
// $pdf->Rect(0.5, 0.5, 64, 93);       // 長方形を描画
// $pdf->Rect(1.5, 1.5, 62, 91);       // 長方形を描画
$pdf->SetLineWidth(0.2);        // 線の太さを0.2mm

///// １段目の応援開始を設定
$pdf->SetXY(1.5, 0);
$pdf->Cell(20, 22.75, '１応援開始', 'B', 0, 'L', 0);
$pdf->Cell(42, 22.75, '', 'B', 0, 'C', 0);
$pdf->Image('http://www.tnk.co.jp/barcode/barcode39_create_png.php?data=916&check=0&mode=white', 35, 1.5, 20, 18, 'PNG', '');  // 応援開始 *916* のバーコード表示
$pdf->SetFont(GOTHIC,'', 6);    // バーコードの数字は 6pt
$pdf->Text(35, 21.5, ' *   9   1   6   *');
$pdf->SetFont(GOTHIC,'',10);    // デフォルトに戻す

///// ２段目の社員番号を設定
$pdf->SetXY(1.5, 22.5);
$pdf->SetFont(GOTHIC,'', 7);    // 氏名は小さくする
$pdf->Cell(20, 22.75, '', 'B', 0, 'L', 0);
$pdf->Cell(42, 22.75, '', 'B', 0, 'C', 0);
$pdf->Text(3.5, 25, "２{$name}");
$pdf->Image("http://www.tnk.co.jp/barcode/barcode39_create_png.php?data={$uid}&check=0&mode=white", 20, 24.5, 42, 18, 'PNG', '');  // 社員番号 *777001* のバーコード表示
$pdf->SetFont(GOTHIC,'', 6);    // バーコードの数字は 6pt
$pdf->Text(22, 44.5, '*    '.substr($uid,0,1).'    '.substr($uid,1,1).'    '.substr($uid,2,1).'    '.substr($uid,3,1).'    '.substr($uid,4,1).'    '.substr($uid,5,1).'    *');
$pdf->SetFont(GOTHIC,'',10);    // デフォルトに戻す

///// ３段目のその他計画を設定
$pdf->SetXY(1.5, 46.5);
$pdf->SetFont(GOTHIC,'', 6);    // その他計画は小さくする
$pdf->Cell(20, 22.75, '', 'B', 0, 'L', 0);
$pdf->Cell(42, 22.75, '', 'B', 0, 'C', 0);
$pdf->Text(3.5, 48, '３その他計画');
$pdf->Image('http://www.tnk.co.jp/barcode/barcode39_create_png.php?data=C9999999&check=0&mode=white', 16, 49, 46, 18, 'PNG', '');  // その他計画 *C9999999* のバーコード表示
$pdf->SetFont(GOTHIC,'', 5);    // バーコードの数字は 5pt
$pdf->Text(18, 68.5, '*    C    9    9    9    9    9    9    9    *');
$pdf->SetFont(GOTHIC,'',10);    // デフォルトに戻す

///// ４段目の応援開始を設定
$pdf->SetXY(1.5, 73);     // なぜか73を超えるとCellとWriteでは次頁に飛んでしまう
// $pdf->Cell(20, 0, '４本作業', 'B', 0, 'L', 0);
// $pdf->Cell(42, 18.00, '', 'B', 0, 'C', 0);
// $pdf->Write(0, '４本作業');
$pdf->Text(3, 82, '４本作業');  // MBFPDF を修正した オリジナルは Text()はEUC-JP未対応
$pdf->Image('http://www.tnk.co.jp/barcode/barcode39_create_png.php?data=910&check=0&mode=white', 35, 71, 20, 18, 'PNG', '');  // 本作業 *910* のバーコード表示
$pdf->SetFont(GOTHIC,'', 6);    // バーコードの数字は 6pt
$pdf->Text(35, 91, ' *   9   1   0   *');
$pdf->SetFont(GOTHIC,'',10);    // デフォルトに戻す

$pdf->Output();     // 最後に、上記データを出力します。
exit();               // なるべくコールする。また、最後のPHPカッコに改行などが含まれるとダメ
?> 