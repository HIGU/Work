<?php
//////////////////////////////////////////////////////////////////////////////
// 社員メニュー 有給5日取得対応 有給管理台帳 PDF出力(印刷) FPDF/MBFPDF使用  //
// Copyright (C) 2019-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2019/06/13 Created  print_yukyu_five_list.php  ゴシック体                //
// 2019/07/25 期間と必要日数の表示を追加                                    //
// 2019/09/13 部門別出力を追加                                              //
// 2021/03/30 基準日の最終日を計算していたが取得できるので変更              //
//////////////////////////////////////////////////////////////////////////////
ini_set('memory_limit', '100M');             // PDFの大量出力のため 52MでOKだが 64Mへ
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('/home/www/html/tnk-web/function.php');   // access_log()を使うためdefine→functionへ切替
// require_once ('/home/www/html/tnk-web/define.php');
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
if (isset($_POST['yukyulist'])) {
    $list_year = $_POST['yukyulist'];
    $_SESSION['yukyulist'] = $_POST['yukyulist'];
} elseif (isset($_GET['yukyulist'])) {
    $list_year = $_GET['yukyulist'];
    $_SESSION['yukyulist'] = $_GET['yukyulist'];
    // $_SESSION['s_sysmsg'] = $_GET['emp_name'];   // Debug用
} else {
    $list_year = '';
    $_SESSION['yukyulist'] = $list_year;
}

if (preg_match("/^[0-9]+$/",$list_year)) {
    if (mb_strlen($list_year) != 4) {
        $_SESSION['s_sysmsg'] = '年度は西暦４桁を入力して下さい！';
        header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
        // header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = '年度は半角数字で入力してください！';
    header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
    // header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}
////////// 対象部門の取得
if (isset($_POST['fivesection'])) {
    $fivesection = $_POST['fivesection'];
    $_SESSION['fivesection'] = $_POST['fivesection'];
} elseif (isset($_GET['fivesection'])) {
    $fivesection = $_GET['fivesection'];
    $_SESSION['fivesection'] = $_GET['fivesection'];
    // $_SESSION['s_sysmsg'] = $_GET['emp_name'];   // Debug用
} else {
    $fivesection = '-1';
    $_SESSION['fivesection'] = $fivesection;
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
        //$this->Cell($this->w_usr[0], 6, '', 'LTB', 0, 'L', 1);
        //$this->Cell($this->w_usr[1], 6, '', 'TB', 0, 'L', 1);
        //$this->SetTextColor(50, 0, 255);    // キャプションだけ色を変える(青)
        //$this->Cell($this->w_usr[2], 6, $caption, 'RTB', 0, 'L', 1);
        $this->SetTextColor(0, 0, 0);
        //$this->Ln();    // 改行
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
        $this->Image('/home/www/html/tnk-web/img/t_nitto_logo2.png', 155, 5, 50, 0, '', '');  //イメージを配置します。場所を指定します。→リファレンス参照
        $this->SetX(60);
        // Select Arial bold 15
        $this->SetFont(GOTHIC, 'B', 16);
        // Move to the right
        /// $this->Cell(80);
        // Framed title
        $list_title = $this->list_year . '年度有給管理台帳';
        $this->Cell(80, 10, $list_title, 'TB', 0, 'C');
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
        // Line break
        $this->Ln(3);
        $this->SetFont(GOTHIC, '', 10);
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        // $this->SetX(15);
        
        $this->Cell(20, 7, '社員番号', 'LRTB', 0, 'C', 1);    // 以下、各フィールドごとに出力
        $this->Cell(15, 7, $this->data_usr[0], 'LRTB', 0, 'C', 1);
        $this->Cell(15, 7, '部　署', 'LRTB', 0, 'C', 1);
        $this->Cell(45, 7, $this->data_usr[1], 'LRTB', 0, 'C', 1);
        $this->Cell(20, 7, '氏　名', 'LRTB', 0, 'C', 1);
        $this->Cell(35, 7, $this->data_usr[3], 'LRTB', 0, 'C', 1);
        $this->Ln();
        $this->Cell(20, 7, '基準日', 'LRTB', 0, 'C', 1);
        $this->Cell(60, 7, $this->data_usr[4], 'LRTB', 0, 'C', 1);
        $this->Cell(20, 7, '取得日数', 'LRTB', 0, 'C', 1);
        $this->Cell(30, 7, $this->data_usr[5], 'LRTB', 0, 'C', 1);
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
$pdf->list_year = $list_year;
$pdf->fivesection = $fivesection;

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

/////////// PostgreSQLと接続
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // 持続的接続

/////////// 社員番号等の取得SQL
// 社長・顧問・その他・日東工器を除外
if ($fivesection == '-1') {
$sql = "SELECT f.uid AS uid
            , trim(section_name) AS section
            , trim(position_name) AS position
            ,trim(name) AS name
            ,f.reference_ym AS reference_ym
            ,f.end_ref_ym AS end_ref_ym
            ,f.need_day AS need_day
        FROM five_yukyu_master AS f LEFT OUTER JOIN user_detailes USING(uid) LEFT OUTER JOIN
            section_master
        USING(sid)
        LEFT OUTER JOIN
            position_master
        USING(pid)
        WHERE business_year={$list_year}
        ORDER BY sid DESC, pid DESC, uid ASC";
} else {
$sql = "SELECT f.uid AS uid
            , trim(section_name) AS section
            , trim(position_name) AS position
            ,trim(name) AS name
            ,f.reference_ym AS reference_ym
            ,f.end_ref_ym AS end_ref_ym
            ,f.need_day AS need_day
        FROM five_yukyu_master AS f LEFT OUTER JOIN user_detailes USING(uid) LEFT OUTER JOIN
            section_master
        USING(sid)
        LEFT OUTER JOIN
            position_master
        USING(pid)
        WHERE business_year={$list_year} and sid={$fivesection}
        ORDER BY sid DESC, pid DESC, uid ASC";
}
if ( !($res_usr = pg_query($con, $sql)) ) {
    $_SESSION['s_sysmsg'] = '社員番号が取得できません：' . pg_last_error($con);
    header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
    exit();
}

            // mysql_fetch_objectはいらないが pg_fetch_objectは行番号がいる
            // はずだったが マニュアルを良く見たら4.1.0以降はオプションとなった。
            // 内部的にレコードカウンターを１増加させている。
$data_f = array();  // スカラー変数ではなくて、配列だということを明示
while ($row = pg_fetch_object($res_usr)) {
    $now_uid       = $row->uid;                                     // 社員番号
    $now_section   = mb_substr($row->section, -10);                  // 部署(短縮)
    $now_position  = mb_substr($row->position, 0, 5);               // 職位(短縮)
    $now_name      = $row->name;                                    // 氏名
    $r_yy          = substr($row->reference_ym, 0,4);               // 基準日：年
    $r_mm          = substr($row->reference_ym, 4,2);               // 基準日：月
    $r_dd          = substr($row->reference_ym, 6,2);               // 基準日：日
    $now_reference = $r_yy . "年" . $r_mm . "月" . $r_dd . "日";    // 基準開始日
    $e_yy          = substr($row->end_ref_ym, 0,4);                 // 基準日：年
    $e_mm          = substr($row->end_ref_ym, 4,2);                 // 基準日：月
    $e_dd          = substr($row->end_ref_ym, 6,2);                 // 基準日：日
    $now_end_ref   = $e_yy . "年" . $e_mm . "月" . $e_dd . "日";    // 基準終了日
    $now_need      = $row->need_day . "日";                         // 必要日数
    $end_rmd = $row->reference_ym + 10000;
    //$end_rmd = 20200331;
    
    // Column titles
    $pdf->wh_usr = array('取得年月日', '取得内容', '取得日数');
    $pdf->w_usr  = array(50, 50, 50);   //各セルの横幅を指定しています。
    /* 受講履歴を取得 SQL */
    $res_w = array();
    $total_num   = 0;       // 取得日数合計
    // データ無し用
    $res_w[0]['s_date'] = '---';   // 取得日
    $res_w[0]['s_name'] = '---';   // 取得内容
    $res_w[0]['s_num'] = '---';   // 取得日数
    $total_num   = '---';       // 取得日数合計
    /*
    $query = "SELECT   uid    AS uid --00 
                ,working_date AS working_date   --01
                ,working_day  AS working_day     --02
                ,absence      AS absence --03
                ,str_mc       AS str_mc --04
                ,end_mc       AS end_mc --05
                FROM working_hours_report_data_new 
                WHERE uid='$now_uid' and working_date >= $row->reference_ym and
                working_date < $end_rmd and ( absence = '11' or str_mc = '41' or end_mc = '42' )";
    */
    $query = "SELECT   uid    AS uid --00 
                ,working_date AS working_date   --01
                ,working_day  AS working_day     --02
                ,absence      AS absence --03
                ,str_mc       AS str_mc --04
                ,end_mc       AS end_mc --05
                FROM working_hours_report_data_new 
                WHERE uid='$now_uid' and working_date >= $row->reference_ym and 
                working_date < $row->end_ref_ym and ( absence = '11' or str_mc = '41' or end_mc = '42' )
                ORDER BY working_date
                ";
    if ( !($res_a = pg_query($con, $query)) ) {
        $res_w[0]['s_date'] = '---';   // 取得日
        $res_w[0]['s_name'] = '---';   // 取得内容
        $res_w[0]['s_num'] = '---';   // 取得日数
        $total_num   = 0;       // 取得日数合計
        $c_num = count($res_w);
    } else {
        $cnt = 0;
        while ($rows_a = pg_fetch_object($res_a)) {
            if ($rows_a->absence == 11) {
                $w_yy          = substr($rows_a->working_date, 0,4);            // 取得日：年
                $w_mm          = substr($rows_a->working_date, 4,2);            // 取得日：月
                $w_dd          = substr($rows_a->working_date, 6,2);            // 取得日：日
                $w_view_date   = $w_yy . "年" . $w_mm . "月" . $w_dd . "日";    // 取得日：年月日
                $res_w[$cnt]['s_date'] = $w_view_date; // 取得日
                $res_w[$cnt]['s_name'] = '有給';      // 取得内容
                $res_w[$cnt]['s_num']  = 1;           // 取得日数
                $total_num += 1;
            } elseif ($rows_a->str_mc == 41) {
                $w_yy          = substr($rows_a->working_date, 0,4);            // 取得日：年
                $w_mm          = substr($rows_a->working_date, 4,2);            // 取得日：月
                $w_dd          = substr($rows_a->working_date, 6,2);            // 取得日：日
                $w_view_date   = $w_yy . "年" . $w_mm . "月" . $w_dd . "日";    // 取得日：年月日
                $res_w[$cnt]['s_date'] = $w_view_date; // 取得日
                $res_w[$cnt]['s_name'] = '半AM';      // 取得内容
                $res_w[$cnt]['s_num']  = 0.5;           // 取得日数
                $total_num += 0.5;
            } elseif ($rows_a->end_mc == 42) {
                $w_yy          = substr($rows_a->working_date, 0,4);            // 取得日：年
                $w_mm          = substr($rows_a->working_date, 4,2);            // 取得日：月
                $w_dd          = substr($rows_a->working_date, 6,2);            // 取得日：日
                $w_view_date   = $w_yy . "年" . $w_mm . "月" . $w_dd . "日";    // 取得日：年月日
                $res_w[$cnt]['s_date'] = $w_view_date; // 取得日
                $res_w[$cnt]['s_name'] = '半PM';      // 取得内容
                $res_w[$cnt]['s_num']  = 0.5;           // 取得日数
                $total_num += 0.5;
            } else {
                $res_w[$cnt]['s_date'] = '---'; // 取得日
                $res_w[$cnt]['s_name'] = '---'; // 取得内容
                $res_w[$cnt]['s_num']  = '---'; // 取得日数
                $total_num = 0;
            }
            /*
            if ($res[$t][3] == 11) {
                $res_w[$t]['s_date'] = '---';   // 取得日
                $res_w[$t]['s_name'] = '---';   // 取得内容
                $res_w[$t]['s_num'] = '---';   // 取得日数
                $total_num += 1;
            } elseif ($res[$t][4] == 41) {
                $res_w[$t]['s_date'] = '---';   // 取得日
                $res_w[$t]['s_name'] = '---';   // 取得内容
                $res_w[$t]['s_num'] = '---';   // 取得日数
                $total_num += 0.5;
            } elseif ($res[$t][5] == 42) {
                $res_w[$t]['s_date'] = '---';   // 取得日
                $res_w[$t]['s_name'] = '---';   // 取得内容
                $res_w[$t]['s_num'] = '---';   // 取得日数
                $total_num += 0.5;
            } else {
                $res_w[$t]['s_date'] = '---';   // 取得日
                $res_w[$t]['s_name'] = '---';   // 取得内容
                $res_w[$t]['s_num'] = '---';   // 取得日数
            }
            */
            $cnt ++;
        }
        $c_num = count($res_w);
    }
    
    ///// 出力 社員番号・部署・役職・氏名 の本文中の見出し
    // 取得日／必要日数の形式に変換
    $total_num = $total_num . "日";
    $total_num = $total_num . "／" . $now_need;
    // 基準開始日〜基準終了日の形式に変換
    $now_reference = $now_reference . "〜" . $now_end_ref;
    $pdf->data_usr = array($now_uid, $now_section, $now_position, $now_name, $now_reference, $total_num);
    
    $cnt = 0;   // データの行カウンタ
    $data_f = array();
    for ($r=0; $r<$c_num; $r++) {
        $s_date = $res_w[$r]['s_date'];
        $s_name = $res_w[$r]['s_name'];
        $s_num  = $res_w[$r]['s_num'];
        $data_f[$cnt] = array($s_date, $s_name, $s_num);
        $cnt++;
    }
    $pdf->AddPage();    // ページを生成。最低1回はコールする必要がある(逆に$pdf->Open()は省略可能)
    // $pdf->SetFont(GOTHIC, '', 12);
    ///// 出力 本文
    $pdf->FancyTable($data_f, '教　育');  // 上でカスタムしたメンバ関数を呼び出す
    
}

$pdf->Output();     // 最後に、上記データを出力します。
exit;               // なるべくコールする。また、最後のPHPカッコに改行などが含まれるとダメ
?> 