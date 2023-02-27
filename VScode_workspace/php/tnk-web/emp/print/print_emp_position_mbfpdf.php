<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の 社員名簿 職位別一覧表 ＰＤＦ出力(印刷) FPDF/MBFPDF使用    //
// Copyright (C) 2004-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/02/13 Created  print_emp_position_mbfpdf.php  ゴシック体            //
// 2004/02/16 japanese.php → mbfpdf.php へ変更してロジックを対応           //
// 2004/03/01 fpdf_protection.php を使い 印刷のみのプロテクトをかけた。     //
// 2004/09/28 php5対応のためi18n_ja_jp_hantozen()→mb_convert_kana()へ変更  //
// 2005/10/13 住所を塩谷郡氏家町→さくら市へ変更 fullpage→fullwidth へ変更 //
// 2010/06/16 暫定的に大渕さん（970268）が印刷できるように変更         大谷 //
// 2017/09/13 社長・顧問・その他・日東工器所属を除外                   大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 CLI CGI版
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('/var/www/html/function.php');   // access_log()を使うためdefine→functionへ切替
// require_once ('/var/www/html/define.php');
access_log();                           // Script Name は自動取得

//////////////// 認証チェック
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
if ($_SESSION['Auth'] <= 1) {                // 権限レベルが１以下は拒否(上級ユーザーのみ)
// if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
    if ($_SESSION['User_ID'] != '970268') {
        $_SESSION['s_sysmsg'] = '社員名簿を印刷する権限がありません！';
        header('Location: http:' . WEB_HOST . EMP_MENU);   // 固定呼出元へ戻る
        // header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
}
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存

#日本語表示の場合必須。すなわち、必ずインクルードすること。
require_once ('/home/www/html/mbfpdf/mbfpdf.php');          // マルチバイトFPDF
define('FPDF_FONTPATH', '/home/www/html/mbfpdf/font/');     // Core Font のパス

class PDF_j extends MBFPDF  // 日本語PDFクラスを拡張します。
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
    function FancyTable($data)
    {
        //Color and font restoration
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        //Header
        $w=array(25, 15, 24, 105, 30);   //各セルの横幅を指定しています。
        //Data
        $fill=0;
        foreach ($data as $row)
        {
            $this->SetFont(GOTHIC, '', 9);
            $this->Cell($w[0], 7, $row[0], 'LRTB', 0, 'L', $fill);  // 以下、各フィールドごとに出力
            $this->Cell($w[1], 7, $row[1], 'LRTB', 0, 'L', $fill);
            $this->SetFont(GOTHIC, '', 9);
            $this->Cell($w[2], 7, $row[2], 'LRTB', 0, 'L', $fill);
            $this->SetFont(GOTHIC, '', 11);
            $row[3] = mb_convert_kana($row[3], 'khnra');            // 全角系を半角になおします。スペースの関係上
            $this->Cell($w[3], 7, $row[3], 'LRTB', 0, 'L', $fill);
            $this->SetFont(GOTHIC, '', 10);
            $row[4] = mb_convert_kana($row[4], 'khnra');
            $this->Cell($w[4], 7, $row[4], 'LRTB', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }

    function Header()   //頭につけたい内容はここに。コンストラクタみたいに自動実行されます。
    {
        $this->Image('/var/www/html/img/t_nitto_logo2.png', 150, 5, 50, 0, '', '');  //イメージを配置します。場所を指定します。→リファレンス参照
        $this->SetX(70);
        //Select Arial bold 15
        $this->SetFont(GOTHIC,'B',16);
        //Move to the right
        # $this->Cell(80);
        //Framed title
        $this->Cell(70,10,'社員名簿　職位別一覧表','TB',0,'C');
        $this->Ln(15);
        $this->SetFont(GOTHIC,'',8);
        $this->SetY(20);
        $this->Cell(0,8,date('Y年m月d日 H時i分s秒'),0,0,'R');
        $this->SetY(25);
        $this->Cell(0,8,'栃木日東工器株式会社',0,0,'R');
        $this->SetY(30);
        $this->Cell(0,8,'〒329-1311 栃木県さくら市氏家3473-2',0,0,'R');
        $this->SetY(35);
        $this->Cell(0,8,'Tel:028-682-8851/Fax:028-681-7038',0,0,'R');
        //Line break
        $this->Ln(10);
        
        //Column titles
        $header = array('所属部署', '役職', ' 氏名', ' 住所', ' 電話番号');
        //Column data array
        
        //Colors, line width and bold font
        $this->SetFillColor(128,128,128);
        $this->SetTextColor(255);
        $this->SetDrawColor(128,128,128);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        //Header
        $w=array(25, 15, 24, 105, 30);   //各セルの横幅を指定しています。
        
        $this->SetFont(GOTHIC,'B',12);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',1);//フィールド名を出力
        $this->Ln();//幅をあけます。→リファレンス参照のこと
    }
    function Footer()//ケツにつけたい内容はここに。コンストラクタみたいに自動実行されます。
    {
        //Go to 1.5 cm from bottom
        //Select Arial italic 8
        $this->SetFont('Times','I',8);
        //Print centered page number
        $this->SetY(-10);   // 下から10mmに印刷
        $this->Cell(0,10,'('.$this->PageNo().')',0,0,'C');
        $this->Cell(0,10,'Copyright TOCHIG NITTO KOHKI Co.,Ltd. All rights reserved',0,0,'R');
    }

}

Header('Pragma: public');   // httpsを利用する際のおまじないです。

#FPDF
$pdf = new PDF_j();     // 上で用意した拡張クラスを生成
// $pdf->AddSJISFont();    // 日本語が必要な場合のおまじない

///// PDF文書のプロパティ設定
$pdf->SetAuthor('栃木日東工器株式会社 小林一弘');    // Tochigi Nitto Kohki Co.,Ltd. k.kobayashi
$pdf->SetCreator("$current_script");
$pdf->SetTitle('Employee name list');
$pdf->SetSubject('Position distinction');
$pdf->SetDisplayMode('fullwidth', 'default');       // ページのレイアウト=第２引数を設定しない場合はcontinuous=連続して配置
$pdf->SetCompression(true);         // 圧縮を有効にする(default=on)
$pdf->SetProtection(array('print'), '', 'tnkowner');    // 印刷のみ許可のプロテクト fpdf_protection.phpが必要('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32)

///// PDF文書の使用フォントの設定
$pdf->AddMBFont(GOTHIC ,'SJIS');
// $pdf->AddMBFont(PGOTHIC,'SJIS');
// $pdf->AddMBFont(MINCHO ,'SJIS');
// $pdf->AddMBFont(PMINCHO,'SJIS');
// $pdf->AddMBFont(KOZMIN ,'SJIS');
$pdf->Open();           // PDFを開始
// $pdf->SetFont('SJIS','',10);//デフォルトフォントをSJIS12ポイントにしておく。
$pdf->SetFont(GOTHIC,'',10);//デフォルトフォントをSJIS12ポイントにしておく。

/////////// SQLを利用する場合など参考に
$sql = "select trim(section_name) as section
            , trim(position_name) as position
            , trim(name) as name
            , trim(address) as address
            , trim(tel) as tel
        from
            user_detailes left
        outer join
            section_master
        using(sid)
        left outer join
            position_master
        using(pid)
        where sflg=1 and retire_date is null and uid!='000000' and pid != 120 and sid != 80 and sid != 90 and sid != 95
        order by pid DESC, sid DESC, uid ASC";
$conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
$con = pg_pConnect($conn_str);                  // 持続的接続
$res = pg_query($con, $sql);

$cnt = 0;   // データの行カウンタ
            // mysql_fetch_objectはいらないが pg_fetch_objectは行番号がいる
            // はずだったが マニュアルを良く見たら4.1.0以降はオプションとなった。
            // 内部的にレコードカウンターを１増加させている。
$data_f = array();//スカラー変数ではなくて、配列だということを明示
while ($row = pg_fetch_object($res)) {

    $now_section  = mb_substr($row->section, -6);
    $now_position = mb_substr($row->position, 0, 3);
    $now_name     = $row->name;
    $now_address  = mb_substr($row->address, 0, 39);
    $now_tel      = $row->tel;

    $data_f[$cnt] = array($now_section, $now_position, $now_name, $now_address, $now_tel);
    $cnt++;
}

// 出力
$pdf->AddPage();    // ページを生成。最低1回はコールする必要がありそうです
$pdf->SetFont(GOTHIC, '', 12);
$pdf->FancyTable($data_f); //上でカスタムしたメンバ関数を呼び出します。
    // $pdf->Image('/var/www/html/img/logo_pro-works.png',170,5,30,0,'','');
    // 複数頁がある時はHeader()に記述する。イメージを配置します。場所を指定します。→リファレンス参照
$pdf->Output();     // 最後に、上記データを出力します。
exit;   //  なるべくコールするべきです。また、最後のPHPカッコに改行などが含まれるとダメです。
?> 