<?php
//////////////////////////////////////////////////////////////////////////////
// 社員メニュー 教育・資格・異動 経歴の一覧表 PDF出力(印刷) FPDF/MBFPDF使用 //
// 前期１年分のみ出力                                                       //
// Copyright (C) 2018-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2018/04/20 Created  print_emp_history_mbfpdf.php  より変更               //
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
if ($_SESSION['Auth'] <= 1) {        // 権限レベルが１以下は拒否(上級ユーザーのみ)
// if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
    if ($_SESSION['User_ID'] != '970268') {
        $_SESSION['s_sysmsg'] = '社員名簿を印刷する権限がありません！';
        header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
        // header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
}
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
        $this->SetFont(GOTHIC, 'B', 10);
        $this->Cell($this->w_usr[0], 6, '', 'LTB', 0, 'L', 1);
        $this->Cell($this->w_usr[1], 6, '', 'TB', 0, 'L', 1);
        $this->SetTextColor(50, 0, 255);    // キャプションだけ色を変える(青)
        $this->Cell($this->w_usr[2], 6, $caption, 'RTB', 0, 'L', 1);
        $this->SetTextColor(0, 0, 0);
        $this->Ln();    // 改行
        $this->SetFillColor(235);   // グレースケールモード
        $this->SetFont(GOTHIC, '', 9);
        $fill = 0;
        foreach ($data as $row) {
            $this->Cell($this->w_usr[0], 5, $row[0], 'LRTB', 0, 'L', $fill);    // 以下、各フィールドごとに出力
            $this->Cell($this->w_usr[1], 5, $row[1], 'LRTB', 0, 'L', $fill);
            $this->Cell($this->w_usr[2], 5, $row[2], 'LRTB', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($this->w_usr), 0, '', 'T');
        $this->Ln();    // 改行
    }

    function Header()   //頭につけたい内容はここに。コンストラクタみたいに自動実行されます。
    {
        $this->Image('/var/www/html/img/t_nitto_logo2.png', 155, 5, 50, 0, '', '');  //イメージを配置します。場所を指定します。→リファレンス参照
        $this->SetX(60);
        // Select Arial bold 15
        $this->SetFont(GOTHIC, 'B', 16);
        // Move to the right
        /// $this->Cell(80);
        // Framed title
        $this->Cell(80, 10, '教育・資格・異動経歴 一覧表', 'TB', 0, 'C');
        $this->Ln(15);
        $this->SetFont(GOTHIC, '', 8);
        $this->SetY(16);
        $this->Cell(0, 0, date('Y年m月d日 H時i分s秒'), 0, 0, 'R');
        $this->SetY(19);
        $this->Cell(0, 0, '栃木日東工器株式会社', 0, 0, 'R');
        // $this->SetY(22);
        // $this->Cell(0, 0, '〒329-1311 栃木県さくら市氏家3473-2', 0, 0, 'R');
        // $this->SetY(25);
        // $this->Cell(0, 0, 'Tel:028-682-8851/Fax:028-681-7038', 0, 0, 'R');
        $this->SetFont(GOTHIC, '', 9);
        $this->SetXY(165, 22);
        if ($this->usr_cnt == 1) {  // 押印欄表示用 先頭のみ押印表示
            $this->Cell(20,  5, '承　認', 'LRTB', 0, 'C');
            $this->Cell(20,  5, '作　成', 'LRTB', 0, 'C');
            $this->SetXY(165, 27);
            $this->Cell(20, 15, '', 'LRTB', 0, 'C');
            $this->Cell(20, 15, '', 'LRTB', 0, 'C');
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
        $this->Cell(20, 7, '社員番号', 'LRTB', 0, 'C', 1);    // 以下、各フィールドごとに出力
        $this->Cell(15, 7, $this->data_usr[0], 'LRTB', 0, 'C', 1);
        $this->Cell(15, 7, '部　署', 'LRTB', 0, 'C', 1);
        $this->Cell(35, 7, $this->data_usr[1], 'LRTB', 0, 'C', 1);
        $this->Cell(20, 7, '職　位', 'LRTB', 0, 'C', 1);
        $this->Cell(30, 7, $this->data_usr[2], 'LRTB', 0, 'C', 1);
        $this->Cell(20, 7, '氏　名', 'LRTB', 0, 'C', 1);
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

}

Header('Pragma: public');   // httpsを利用する際のおまじないです。

///////// FPDF
$pdf = new PDF_j();     // 上で用意した拡張クラスを生成

///// PDF文書のプロパティ設定
$pdf->SetAuthor('栃木日東工器株式会社');    // Tochigi Nitto Kohki Co.,Ltd.
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
$pdf->wh_usr = array('開始日付', '終了日付', '内　　容');
$pdf->w_usr  = array(20, 20, 150);   //各セルの横幅を指定しています。

/////////// PostgreSQLと接続
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // 持続的接続

/////////// 社員番号等の取得SQL
// 社長・顧問・その他・日東工器を除外
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
    $_SESSION['s_sysmsg'] = '社員番号が取得できません：' . pg_last_error($con);
    header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
    exit();
}

// 前期分の年月の計算
$today_ym = date('Ym');
$today_yy = substr($today_ym, 0, 4);
$today_mm = substr($today_ym, -2, 2);

if ($today_mm > 3) {
    $end_ymd = $today_yy . '0331';
    $str_yy  = $today_yy - 1;
    $str_ymd = $str_yy . '0401';
} else {
    $end_yy  = $today_yy - 1;
    $end_ymd = $end_yy . '0331';
    $str_yy  = $today_yy - 2;
    $str_ymd = $str_yy . '0401';
}

            // mysql_fetch_objectはいらないが pg_fetch_objectは行番号がいる
            // はずだったが マニュアルを良く見たら4.1.0以降はオプションとなった。
            // 内部的にレコードカウンターを１増加させている。
$data_f = array();  // スカラー変数ではなくて、配列だということを明示
while ($row = pg_fetch_object($res_usr)) {
    $now_uid      = $row->uid;                          // 社員番号
    $now_section  = mb_substr($row->section, -9);       // 部署(短縮)
    $now_position = mb_substr($row->position, 0, 5);    // 職位(短縮)
    $now_name     = $row->name;                         // 氏名
    ///// 出力 社員番号・部署・役職・氏名 の本文中の見出し
    $pdf->data_usr = array($now_uid, $now_section, $now_position, $now_name);
    //$pdf->data_usr = array($now_uid, $now_section, $str_ymd, $end_ymd);
    /* 受講履歴を取得 SQL */
    /*
    $query = "SELECT ur.begin_date      AS s_date
                , ur.end_date           AS e_date
                , trim(rm.receive_name) AS r_name
              FROM user_receive ur, receive_master rm
              WHERE ur.uid='$now_uid' AND ur.rid=rm.rid ORDER BY ur.begin_date ASC";
    */
    $query = "SELECT ur.begin_date      AS s_date
                , ur.end_date           AS e_date
                , trim(rm.receive_name) AS r_name
              FROM user_receive ur, receive_master rm
              WHERE ur.uid='$now_uid' AND ur.rid=rm.rid AND ur.begin_date>='$str_ymd' AND ur.begin_date<='$end_ymd'
              ORDER BY ur.begin_date ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '教育経歴が取得できません：' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
        exit();
    }
    $cnt = 0;   // データの行カウンタ
    $data_f = array();
    while ($rows = pg_fetch_object($res)) {
        $s_date = $rows->s_date;
        $e_date = $rows->e_date;
        $r_name = $rows->r_name;
        $data_f[$cnt] = array($s_date, $e_date, $r_name);
        $cnt++;
    }
    $pdf->AddPage();    // ページを生成。最低1回はコールする必要がある(逆に$pdf->Open()は省略可能)
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// 出力 本文
    $pdf->FancyTable($data_f, '教　育');  // 上でカスタムしたメンバ関数を呼び出す
    
    /* 資格一覧を取得 */
    $query = "SELECT uc.acq_date            AS s_date
                , trim(cm.capacity_name)    AS r_name
              FROM user_capacity uc,capacity_master cm
              WHERE uc.uid='$now_uid' AND uc.cid=cm.cid AND uc.acq_date>='$str_ymd' AND uc.acq_date<='$end_ymd'
              ORDER BY uc.acq_date ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '資格経歴が取得できません：' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
        exit();
    }
    $cnt = 0;   // データの行カウンタ
    $data_f = array();
    while ($rows = pg_fetch_object($res)) {
        $s_date = $rows->s_date;
        $e_date = '';
        $r_name = $rows->r_name;
        $data_f[$cnt] = array($s_date, $e_date, $r_name);
        $cnt++;
    }
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// 出力 本文
    $pdf->FancyTable($data_f, '資　格');  // 上でカスタムしたメンバ関数を呼び出す
    
    /* 異動履歴を取得 */
    $query = "SELECT trans_date         AS s_date
                , trim(section_name)    AS r_name
              FROM user_transfer
              WHERE uid='$now_uid' AND trans_date>='$str_ymd' AND trans_date<='$end_ymd'
              ORDER BY trans_date ASC";
    if ( !($res = pg_query($con, $query)) ) {
        $_SESSION['s_sysmsg'] = '異動経歴が取得できません：' . pg_last_error($con);
        header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
        exit();
    }
    $cnt = 0;   // データの行カウンタ
    $data_f = array();
    while ($rows = pg_fetch_object($res)) {
        $s_date = $rows->s_date;
        $e_date = '';
        $r_name = $rows->r_name;
        $data_f[$cnt] = array($s_date, $e_date, $r_name);
        $cnt++;
    }
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// 出力 本文
    $pdf->FancyTable($data_f, '異　動');  // 上でカスタムしたメンバ関数を呼び出す
    $pdf->usr_cnt = 1;                    // 押印欄表示用
}

$pdf->Output();     // 最後に、上記データを出力します。
exit;               // なるべくコールする。また、最後のPHPカッコに改行などが含まれるとダメ
?> 