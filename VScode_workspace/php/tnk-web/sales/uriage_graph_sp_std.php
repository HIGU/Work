<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ標準品・特注品別 売上 比較グラフ                                   //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/11/01 Created   uriage_graph_sp_std.php                             //
// 2002/05/01 グラフ下の年月表示をリテラルからプログラムロジックに変更      //
// 2002/08/08 セッション管理に切替えたため戻るボタンを追加                  //
// 2003/02/13 グラフの日本語対応版 jpGraph.php 1.9.1 マルチバイト対応へ     //
//              改造 日本語版 凡例を追加 その他もろもろ                     //
// 2003/05/01 jpGraph 1.12.1 UP による微調整 legend->Pos mark->SetWidth     //
//            忘れていたのを修正 Graph(780→840  SetMargin(40,120→140 へ   //
// 2003/09/05 グラフファイルの更新日のチェックを追加 高速化を図ったが？     //
//            error_reporting = E_ALL 対応のため 配列変数の初期化追加       //
// 2003/10/31 カプラ特注の当月分の売上取得 SQL文を(assembly_schedule)に変更 //
//            明細表のフォーマットを横伸びから縦伸びへ変更（デザイン含む）  //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/06/07 当月分の標準品の算出方法変更 (完成全体－完成特注)＝完成標準   //
//            表の内容変更 標準・特注・製品(標準+特注)・部品・合計を表示する//
// 2004/11/05 Start年月を変更出来るように$str_ymに集約 今回は200104->200304 //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/10/01 ページ制御を追加(１年毎のグラフ表示)  E_ALL | E_STRICTへ      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // 内部でdefine.php pgsql.php を require()している。
require_once ('../tnk_func.php');
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
require_once ('../../jpgraph-4.4.1/src/jpgraph.php'); 
require_once ('../../jpgraph-4.4.1/src/jpgraph_line.php'); 
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1,  9);                    // site_index=1(売上メニュー) site_id=9(カラプ特注標準グラフ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SALES_MENU);              // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('カプラ 特注品・標準品 売上推移 グラフ');

///// ページリクエストの処理
if (isset($_REQUEST['pageNo'])) {
    $pageNo = $_REQUEST['pageNo'];
} else {
    $pageNo = 1;
}
if ($pageNo < 1) $pageNo = 1;
///// スタート年月の指定
$today = date('Ymd');
$query = "
    SELECT to_char(date '{$today}' - interval '{$pageNo} year', 'FMYYYYMM')
";
getUniResult($query, $str_ym);
///// エンド年月の指定
if ($pageNo == 1) {
    $query = "
        SELECT to_char(date '{$today}' - interval '{$pageNo} month', 'FMYYYYMM')
    ";
} else {
    $endNo = ($pageNo - 1);
    $query = "
        SELECT to_char(date '{$today}' - interval '{$endNo} year', 'FMYYYYMM')
    ";
}
getUniResult($query, $end_ym);
if ($str_ym <= 200010) {
    // $pageNo -= 1;
    $backward = ' disabled';
} else {
    $backward = '';
}
if ($end_ym < 200010) {
    $end_ym = 200010;
}
if ($pageNo == 1) {
    $forward = ' disabled';
} else {
    $forward = '';
}
//////////// グラフの日付範囲をキャプションで登録
$menu->set_caption("{$str_ym}から{$end_ym}までのグラフを表示");

//////////// グラフファイルの存在チェック
$graph_name = 'graph/uriage_graph_sp_std.png';
if (file_exists($graph_name)) {
    //////////// 現在の年月日とグラフファイルの更新日データを取得
    $current_date = date('Ymd');
    $file_date    = date('Ymd', filemtime($graph_name) );
    //////////// グラフファイルの更新日チェック
    if ($current_date == $file_date) {
        $create_flg = false;            // グラフ作成不要
    } else {
        $create_flg = true;             // グラフ作成
    }
} else {
    $create_flg = true;                 // グラフ作成
}
$create_flg = true;     // 各月の金額表示のため都度、生成する。後で月毎のデータをテーブルに保存する設計に変える
/////////// スタート年月の初期化
// $str_ym = 200304;

///////////////////// 特注・標準 売上推移
                   // 当月より以前の各月の金額はワークファイルを参照する
$query_wrk = "SELECT 年月,c特注,c標準,c製品,カプラ FROM wrk_uriage WHERE 年月>={$str_ym} AND 年月<={$end_ym} ORDER BY 年月 ASC";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk,$res_wrk)) {
    $sp_kin  = array();
    $std_kin = array();
    $sei_kin = array();     // 製品(完成)全体 2004/06/07 add
    $f_sp_kin  = array();
    $f_std_kin = array();
    $f_sei_kin = array();   // 製品(完成)全体 2004/06/07 add
    $f_par_kin = array();   // カプラ部品 2004/06/07 add
    $f_all_kin = array();   // カプラ全体 2004/06/07 add
    $datax   = array();
    for($cnt=0;$cnt<$rows_wrk;$cnt++){      // cnt は配列用のカウンター下でも使う
        if(substr(date_offset(1),0,6)==$res_wrk[$cnt][0])   // 月初にワークファイル更新時の対策
            break;
        $datax[$cnt]   = $res_wrk[$cnt][0];
        $sp_kin[$cnt]  = $res_wrk[$cnt][1];
        $std_kin[$cnt] = $res_wrk[$cnt][2];
    }
    for($i=0;$i<$cnt;$i++){
        $f_sp_kin[$i]  = Uround($sp_kin[$i] / 1000,0);      // 単位を千円にする表用
        $f_std_kin[$i] = Uround($std_kin[$i] / 1000,0);
        ///// 表形式のフォーマットで生成 2004/06/07 追加分
        $f_sei_kin[$i] = Uround($res_wrk[$i][3] / 1000,0);      // 製品(完成)全体 2004/06/07 add
        $f_all_kin[$i] = Uround($res_wrk[$i][4] / 1000,0);      // カラプ全体 2004/06/07 add
        $f_par_kin[$i] = Uround(($res_wrk[$i][4]-$res_wrk[$i][3]) / 1000,0);// 部品・手打 2004/06/07 add
        ///// グラフ用データ生成
        $sp_kin[$i]  = Uround($sp_kin[$i] / 1000000,1);     // 単位を百万円にする
        $std_kin[$i] = Uround($std_kin[$i] / 1000000,1);
    }
}

if ($pageNo == 1) {
    $temp_date = date_offset(1);
    $temp_date = substr($temp_date, 0, 6);
    $s_date = $temp_date . '01';
    $e_date = $temp_date . '31';
    
    // 特注品 当月分計算
    $query = "SELECT sum(Uround(数量*単価,0)) as 合計金額 FROM hiuuri left outer join assembly_schedule on 計画番号=plan_no WHERE 計上日>=$s_date AND 計上日<=$e_date AND 事業部='C' AND note15 like 'SC%'";
    // $query = "SELECT 計上日,数量*単価 as 金額 FROM hiuuri h, mipmst m WHERE h.assyno=m.seihin AND h.計上日>=$s_date AND h.計上日<=$e_date AND h.事業部='C' AND m.kubun='3' ORDER BY h.計上日 asc";
    if (getUniResult($query,$res_toku) > 0) {
        $datax[$cnt] = substr($s_date, 0, 6);  // X軸の項目を代入
        $sp_kin[$cnt] = $res_toku;             // 当月の合計金額を算出
        $f_sp_kin[$cnt] = Uround($sp_kin[$cnt] / 1000,0);    // 単位を千万円にする
        $sp_kin[$cnt] = Uround($sp_kin[$cnt] / 1000000,1);   // 単位を百万円にする
    }
        
    // 標準品 当月分計算
    $query = "SELECT sum(Uround(数量*単価,0)) as 合計金額 FROM hiuuri WHERE 計上日>=$s_date AND 計上日<=$e_date AND 事業部='C' AND datatype='1'";
    // $query = "SELECT sum(Uround(数量*単価,0)) as 合計金額 FROM hiuuri left outer join assembly_schedule on 計画番号=plan_no WHERE 計上日>=$s_date AND 計上日<=$e_date AND 事業部='C' AND sei_kubun='1'";
    // $query = "SELECT 計上日,数量*単価 as 金額 FROM hiuuri h, mipmst m WHERE h.assyno=m.seihin AND h.計上日>=$s_date AND h.計上日<=$e_date AND h.事業部='C' AND m.kubun='1' ORDER BY h.計上日 asc";
    if (getUniResult($query,$res_sei) > 0) {
        $datax[$cnt] = substr($s_date, 0, 6);       // X軸の項目を代入
        $std_kin[$cnt] = ($res_sei - $res_toku);    // 当月の合計金額を算出 (完成全体 － 完成特注)
        $f_std_kin[$cnt] = Uround($std_kin[$cnt] / 1000,0);  // 単位を千万円にする
        $std_kin[$cnt] = Uround($std_kin[$cnt] / 1000000,1); // 単位を百万円にする
    }
    
    // 部品・手打及び 製品(完成)全体の当月分計算
    $f_sei_kin[$cnt] = Uround($res_sei / 1000, 0);
    
    // カラプ全体 当月分計算
    $query = "SELECT sum(Uround(数量*単価,0)) as 合計金額 FROM hiuuri WHERE 計上日>=$s_date AND 計上日<=$e_date AND 事業部='C'";
    if (getUniResult($query,$res_all) > 0) {
        $f_par_kin[$cnt] = Uround(($res_all - $res_sei) / 1000, 0);    // 当月の部品・手打の金額を算出 (カプラ全体 － 製品全体)
        $f_all_kin[$cnt] = Uround($res_all / 1000, 0);  // カプラ全体の金額セット 単位を千万円にする
    }
} else {
    $cnt--;
}


// A nice graph with anti-aliasing 
$graph = new Graph(840, 380, 'auto');       // グラフの大きさ X/Y
$graph->img->SetMargin(40,140,30,60);       // グラフ位置のマージン 左右上下
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
$graph->title->Set(mb_convert_encoding("カプラ 特注品・標準品 売上推移   単位：百万円","UTF-8")); 
$graph->legend->Pos(0.015,0.5,"right","center"); // 凡例の位置指定 X Y(0.5は縦位置中央)
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上
//$graph->title->Set("Line plot with null values"); 
$graph->yscale->SetGrace(10);               // Set 10% grace. 余裕スケール
$graph->yaxis->SetColor("black");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.03,0.5,"right","center");  // 凡例の位置指定

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // フォントはボールドも指定できる。
$graph->xaxis->SetLabelAngle(45); 

// Create the first line 
$p1 = new LinePlot($std_kin); 
// $p1->mark->SetType(MARK_FILLEDCIRCLE);   // プロットマークの形
$p1->mark->SetType(MARK_DIAMOND);   // プロットマークの形
$p1->mark->SetFillColor("red"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("red"); 
$p1->SetCenter(); 
$p1->SetLegend(mb_convert_encoding("標準品","UTF-8"));    // 凡例の名称設定
// $p1->SetLegend("Custom Order"); 
$graph->Add($p1); 

// ... and the second 
//  $graph->SetY2Scale("lin");      // Y2スケールを追加
//  $graph->y2axis->SetColor("red");    // Y2スケールの色
//  $graph->y2axis->SetWeight(2);   // Y2スケールの太さ(２ドット)
//  $graph->y2scale->SetGrace(10);  // Set 10% grace. 余裕スケール
$p2 = new LinePlot($sp_kin);   // 二つ目のラインプロットクラスの宣言
// $p2->mark->SetType(MARK_STAR);      // プロットマークの形
// $p2->mark->SetType(MARK_DIAMOND);   // プロットマークの形
$p2->mark->SetType(MARK_FILLEDCIRCLE);    // プロットマークの形
$p2->mark->SetFillColor("blue");     // プロットマークの色
$p2->mark->SetWidth(2);         // プロットマークの大きさ
$p2->SetColor("blue");           // プロット線の色
$p2->SetCenter();           // プロットを中央へ
$p2->SetWeight(1);          // プロット線の太さ(１ドット)
$p2->SetLegend(mb_convert_encoding("特注品","UTF-8"));    // 凡例の名称設定
// $p2->SetLegend("Standard");  // 凡例は削除
$graph->Add($p2);       // 普通のグラフからY2スケールへ変更のためコメント
//  $graph->AddY2($p2);             // Y2スケール用のプロット２を追加

// Output line 
$graph->Stroke($graph_name); 
// echo $graph->GetHTMLImageMap("myimagemap"); 
// echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 


for($i=0;$i<=$cnt;$i++){
    $f_std_kin[$i] = number_format($f_std_kin[$i]);     // ３桁ごとのカンマを付加
    $f_sp_kin[$i]  = number_format($f_sp_kin[$i]);      // ３桁ごとのカンマを付加
    $f_sei_kin[$i] = number_format($f_sei_kin[$i]);     // ３桁ごとのカンマを付加 2004/06/07 add
    $f_par_kin[$i] = number_format($f_par_kin[$i]);     // ３桁ごとのカンマを付加 2004/06/07 add
    $f_all_kin[$i] = number_format($f_all_kin[$i]);     // ３桁ごとのカンマを付加 2004/06/07 add
}

///////////// HTML Header を出力してブラウザーのキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<style type='text/css'>
<!--
.pt10b {
    font-size:      0.80em;
    font-weight:    bold;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
}
-->
</style>
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'<?php echo $backward?>>
                                <input type='hidden' name='pageNo' value='<?php echo ($pageNo + 1) ?>'>
                            </td>
                        </table>
                    </td>
                </form>
                    <td nowrap align='center' width='80%' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'<?php echo $forward?>>
                                <input type='hidden' name='pageNo' value='<?php echo ($pageNo - 1) ?>'>
                            </td>
                        </table>
                    </td>
                </form>
            </tr>
        </table>
        
        <!--------------- ここからグラフを表示する -------------------->
<table align='center' with='100%' border='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name . "?" . uniqid(rand(),1) ?>' alt='カプラ特注・標準 売上推移 グラフ' border='0'>
        </td>
    </tr>
</table>

<table align='center' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
    <caption style='text-align:right;'><font size='2'>単位：千円</font></caption>
    <tr><td> <!-- ダミー(デザイン用) -->
  <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1' bordercolordark='white' bordercolorlight='#bdaa90'>
    <th nowrap>年月</th> <th nowrap>標準品(A)</th> <th nowrap>特注品(B)</th><th nowrap>製品全体(A+B)</th><th nowrap>部品・手打(D)</th><th nowrap>Ｃ合計(A+B+D)</th>
    <?php
    for ($i=$cnt; $i>=0; $i--) {
        echo "<tr>\n";
        echo "    <td nowrap width='100' align='center'>" . substr($datax[$i],0,4) . "/" . substr($datax[$i],4,2) . "</td>\n";
        echo "    <td nowrap width='100' align='right' style='color:red;'>{$f_std_kin[$i]}</td>\n";
        echo "    <td nowrap width='100' align='right' style='color:blue;'>{$f_sp_kin[$i]}</td>\n";
        echo "    <td nowrap width='100' align='right'>{$f_sei_kin[$i]}</td>\n";
        echo "    <td nowrap width='100' align='right'>{$f_par_kin[$i]}</td>\n";
        echo "    <td nowrap align='right'>{$f_all_kin[$i]}</td>\n";
        echo "</tr>\n";
    }
    ?>
  </table>
    </td></tr>
</table> <!-- ダミーEnd -->


    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
