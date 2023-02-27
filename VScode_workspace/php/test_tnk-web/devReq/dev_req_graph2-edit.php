<?php
//////////////////////////////////////////////////////////////////////////////
//プログラム開発 受付・完了・未完了 件数 グラフ                             //
// 2002/02/10 Copyright(C)2002-2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// 変更経歴                                                                 //
// 2002/02/12 グラフのMARKを変更                                            //
// 2002/07/04 最新の月が完了入力だけされていて受付されていない時の対策      //
// 2002/07/05 $datax をデータベースからでなく計算で求める。                 //
// 2002/08/09   register_globals = Off 対応                                 //
// 2002/12/20 グラフの日本語対応版 jpGraph.php 1.9.1 マルチバイト対応へ改造 //
//            日本語版 凡例を追加                                           //
//            $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14);               //
//            FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する              //
//            $graph->title->Set(mb_convert_encoding("???????","UTF-8"));   //
//            $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14);              //
//            FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上                     //
//            $p1->SetLegend(mb_convert_encoding(""受付件数"","UTF-8"));    //
//            凡例の名称設定                                                //
// 2003/12/12 defineされた定数でディレクトリとメニュー名を使用する          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
ini_set('max_execution_time', 10);      // 最大実行時間 10=10秒 1200=20分 CLI CGI版
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
// require("../define.php");
require_once ("../../jpgraph-4.4.1/src/jpgraph.php"); 
require_once ("../../jpgraph-4.4.1/src/jpgraph_line.php"); 
$sysmsg = $_SESSION["s_sysmsg"];
$_SESSION["s_sysmsg"] = NULL;
$_SESSION["site_index"] = 4;
$_SESSION["site_id"] = 4;
access_log();       // Script Name は自動取得
// $_SESSION["dev_req_menu"] = date("H:i");
if(!isset($_SESSION["User_ID"])||!isset($_SESSION["Password"])||!isset($_SESSION["Auth"])){
    $_SESSION["s_sysmsg"] = "認証されていないか認証期限が切れました。Login し直して下さい。";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

//////////// X軸のラベル用と年月チェック用に生成する。
$yyyy = 2001;
$mm   = 01;
$yyyymm = $yyyy . $mm;
$j = 0;
while( (int) $yyyymm <= (int) date("Ym")){
    $datax[$j] = $yyyy . '-' . sprintf('%02s',$mm);
    $j++;
    $mm++;
    if($mm > 12){
        $yyyy++;
        $mm = 01;
    }
    $yyyymm = $yyyy . sprintf('%02s',$mm);      // 20012 → 200102 へ変換する
}


/////////// グラフデータ生成
    // 昔の依頼件数（2001-01-01より前は合計件数のみ）を抽出 優先度＝Xを除く
$query = "select 依頼日,開発工数 from dev_req where 優先度<>'X' and 作業区='1' and 依頼日<'2001-01-01' order by 依頼日 asc";
$res = array();
if ($rows = getResult($query,$res)) {
    $cnt = 0;                   //配列用のカウンター
    $tuki_cnt = array();
    // $datax    = array();
    // $datax[$cnt] = "2001-01";
    for ($r=0; $r<$rows; $r++) {                // 2001-01-01より前の合計件数を算出
        $tuki_cnt[$cnt] += 1;
    }
}
                            // 各月の 依頼件数 を抽出 優先度＝Xを除く
$query = "select 依頼日,開発工数 from dev_req where 優先度<>'X' and 作業区='1' and 依頼日>='2001-01-01' order by 依頼日 asc";
$res = array();
if ($rows = getResult($query,$res)) {
    $start_flg = 0;             //スタート時のフラグ
    $cnt = 0;                   //配列用のカウンター
    // for ($r=0; $r<$rows; $r++) {                // 各月毎の合計件数を算出
    $r = 0;
    while (isset($datax[$cnt])) {
        $yyyy_mm = substr($res[$r][0],0,7);
        $r++;
        if ($start_flg==0) {
            $tuki_cnt[$cnt] += 1;
            // $datax[$cnt]    = $yyyy_mm;
            $start_flg      = 1;
        } elseif ($datax[$cnt]==$yyyy_mm) {
            $tuki_cnt[$cnt] += 1;
        } else {
            $cnt += 1;
            // $tuki_cnt[$cnt] += 1;
            // $datax[$cnt]     = $yyyy_mm;
        }
    }
    $rui_cnt = array();             // 累積を算出
    for($i=0;$i<=$cnt;$i++){
        if($i==0)
            $rui_cnt[$i] = $tuki_cnt[$i];
        else
            $rui_cnt[$i] = $tuki_cnt[$i] + $rui_cnt[$i-1];
    }
}
$tuki_uketuke = $tuki_cnt;
$rui_uketuke = $rui_cnt;


                            // 各月の完了件数を抽出 完了日は2001-01-01より発生
$query = "select 完了日,開発工数 from dev_req where (完了日<>'1970-01-01' or 完了日<>NULL) and 作業区='1' order by 完了日 asc";
$res = array();
if ($rows = getResult($query,$res)) {
    $start_flg = 0;             //スタート時のフラグ
    $cnt = 0;                   //配列用のカウンター
    $tuki_cnt = array();
    // for($r=0;$r<$rows;$r++){                // 各月毎の合計件数を算出
    $r = 0;
    while (isset($datax[$cnt])) {
        $yyyy_mm = substr($res[$r][0],0,7);
        $r++;
        if ($start_flg==0) {
            $tuki_cnt[$cnt] = 1;
            $start_flg      = 1;
        } elseif ($datax[$cnt]==$yyyy_mm) {
            $tuki_cnt[$cnt] += 1;
        } else {
            $cnt += 1;
            // $tuki_cnt[$cnt] += 1;
        }
    }
    $rui_cnt = array();             // 累積を算出
    for($i=0;$i<=$cnt;$i++){
        if($i==0)
            $rui_cnt[$i] = $tuki_cnt[$i];
        else
            $rui_cnt[$i] = $tuki_cnt[$i] + $rui_cnt[$i-1];
    }
}
$tuki_kan = $tuki_cnt;
$rui_kan = $rui_cnt;


            // 各月の 未完了 件数を計算
$tuki_mikan = array();
for($i=0;$i<=$cnt;$i++){
    if($rui_uketuke[$i] == 0)       // 最新の月が完了入力だけされていて受付されていない時の対策
        $rui_uketuke[$i] = $rui_uketuke[$i-1];
    $tuki_mikan[$i] = $rui_uketuke[$i] - $rui_kan[$i];
}


// A nice graph with anti-aliasing 
$graph = new Graph(770,350,"auto");         // グラフの大きさ X/Y
$graph->img->SetMargin(30,160,30,70);       // グラフ位置のマージン 左右上下
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
//$graph->title->Set("Line plot with null values"); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
$graph->title->Set(mb_convert_encoding("プログラム開発 受付累計・受付・完了・未完了 件数グラフ","UTF-8")); 
$graph->yscale->SetGrace(10);     // Set 10% grace. 余裕スケール
$graph->yaxis->SetColor("blue");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.02,0.5,"right","center");  // 凡例の位置指定
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD); 
$graph->xaxis->SetLabelAngle(90); 

// Create the first line 
//  MARK_SQUARE, A filled square
//  MARK_UTRIANGLE, A upward pointing triangle
//  MARK_DTRIANGLE, A downward pointing triangle
//  MARK_DIAMOND, A diamond shape
//  MARK_CIRCLE, A non-filled circle.
//  MARK_FILLEDCIRCLE, A filled circle
//  MARK_STAR
$p1 = new LinePlot($tuki_uketuke); 
$p1->mark->SetType(MARK_FILLEDCIRCLE); 
$p1->mark->SetFillColor("blue"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("blue"); 
$p1->SetCenter(); 
// $p1->SetLegend(" uketuke"); 
$p1->SetLegend(mb_convert_encoding("受付件数","UTF-8"));    // 凡例の名称設定
$graph->Add($p1); 

// ... and the second Y Scale
$graph->SetY2Scale("lin");      // Y2スケールを追加
$graph->y2axis->SetColor("red");    // Y2スケールの色
$graph->y2axis->SetWeight(2);   // Y2スケールの太さ(２ドット)
$graph->y2scale->SetGrace(10);  // Set 10% grace. 余裕スケール
$py2 = new LinePlot($rui_uketuke);  // 二つ目のラインプロットクラスの宣言
//  $py2->mark->SetType(MARK_STAR);     // プロットマークの形
$py2->mark->SetType(MARK_SQUARE);   // プロットマークの形
$py2->mark->SetFillColor("red");    // プロットマークの色
$py2->mark->SetWidth(4);        // プロットマークの大きさ
$py2->SetColor("red");          // プロット線の色
$py2->SetCenter();          // プロットを中央へ
$py2->SetWeight(1);         // プロット線の太さ(１ドット)
// $py2->SetLegend(" uketuke-rui");     // 凡例は削除
$py2->SetLegend(mb_convert_encoding("受付累計","UTF-8"));    // 凡例の名称設定
$graph->AddY2($py2);            // Y2スケール用のプロット２を追加

// ... and the second
$p2 = new LinePlot($tuki_kan); 
$p2->mark->SetType(MARK_STAR); 
$p2->mark->SetFillColor("black"); 
$p2->mark->SetWidth(4); 
$p2->SetColor("black"); 
$p2->SetCenter(); 
$p2->SetWeight(2);
// $p2->SetLegend(" kanryou"); 
$p2->SetLegend(mb_convert_encoding("完了件数","UTF-8"));    // 凡例の名称設定
$graph->Add($p2); 

// ... and the third
$p3 = new LinePlot($tuki_mikan); 
//  $p3->mark->SetType(MARK_STAR); 
$p3->mark->SetType(MARK_UTRIANGLE); 
$p3->mark->SetFillColor("orange"); 
$p3->mark->SetWidth(4); 
$p3->SetColor("orange"); 
$p3->SetCenter(); 
$p3->SetWeight(2);
// $p3->SetLegend(" mikanryo"); 
$p3->SetLegend(mb_convert_encoding("未完了","UTF-8"));    // 凡例の名称設定
$graph->Add($p3); 

// Output line 
$graph->Stroke("graph/dev_req_graph2.png"); 


//////////////// グラフデータ生成２
// A nice graph with anti-aliasing 
$graph = new Graph(770,350,"auto");         // グラフの大きさ X/Y
$graph->img->SetMargin(30,130,30,70);       // グラフ位置のマージン 左右上下
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
//$graph->title->Set("Line plot with null values"); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
$graph->title->Set(mb_convert_encoding("プログラム開発 受付・完了・未完了 件数グラフ","UTF-8")); 
$graph->yscale->SetGrace(10);     // Set 10% grace. 余裕スケール
$graph->yaxis->SetColor("blue");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.02,0.5,"right","center");  // 凡例の位置指定
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD); 
$graph->xaxis->SetLabelAngle(90); 

// Create the first line 
$p1 = new LinePlot($tuki_uketuke); 
$p1->mark->SetType(MARK_FILLEDCIRCLE); 
$p1->mark->SetFillColor("blue"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("blue"); 
$p1->SetCenter(); 
// $p1->SetLegend(" uketuke"); 
$p1->SetLegend(mb_convert_encoding("受付件数","UTF-8"));    // 凡例の名称設定
$graph->Add($p1); 

/*
// ... and the second Y Scale
$graph->SetY2Scale("lin");      // Y2スケールを追加
$graph->y2axis->SetColor("red");    // Y2スケールの色
$graph->y2axis->SetWeight(2);   // Y2スケールの太さ(２ドット)
$graph->y2scale->SetGrace(10);  // Set 10% grace. 余裕スケール
$py2 = new LinePlot($rui_uketuke);  // 二つ目のラインプロットクラスの宣言
$py2->mark->SetType(MARK_STAR);     // プロットマークの形
$py2->mark->SetFillColor("red");    // プロットマークの色
$py2->mark->SetWidth(4);        // プロットマークの大きさ
$py2->SetColor("red");          // プロット線の色
$py2->SetCenter();          // プロットを中央へ
$py2->SetWeight(1);         // プロット線の太さ(１ドット)
// $py2->SetLegend(" uketuke-rui");     // 凡例は削除
$py2->SetLegend(mb_convert_encoding("受付累計","UTF-8"));    // 凡例の名称設定
$graph->AddY2($py2);            // Y2スケール用のプロット２を追加
*/

// ... and the second
$p2 = new LinePlot($tuki_kan); 
$p2->mark->SetType(MARK_STAR); 
$p2->mark->SetFillColor("black"); 
$p2->mark->SetWidth(4); 
$p2->SetColor("black"); 
$p2->SetCenter(); 
$p2->SetWeight(2);
// $p2->SetLegend(" kanryou"); 
$p2->SetLegend(mb_convert_encoding("完了件数","UTF-8"));    // 凡例の名称設定
$graph->Add($p2); 

// ... and the third
$p3 = new LinePlot($tuki_mikan); 
$p3->mark->SetType(MARK_UTRIANGLE); 
$p3->mark->SetFillColor("orange"); 
$p3->mark->SetWidth(4); 
$p3->SetColor("orange"); 
$p3->SetCenter(); 
$p3->SetWeight(2);
// $p3->SetLegend(" mikanryo"); 
$p3->SetLegend(mb_convert_encoding("未完了","UTF-8"));    // 凡例の名称設定
$graph->Add($p3); 

// Output line 
$graph->Stroke("graph/dev_req_graph3.png"); 


////////////// グラフデータ生成３
// A nice graph with anti-aliasing 
$graph = new Graph(770,350,"auto");         // グラフの大きさ X/Y
$graph->img->SetMargin(30,130,30,70);       // グラフ位置のマージン 左右上下
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
//$graph->title->Set("Line plot with null values"); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
$graph->title->Set(mb_convert_encoding("プログラム開発 完了・未完了 件数グラフ","UTF-8")); 
$graph->yscale->SetGrace(10);     // Set 10% grace. 余裕スケール
$graph->yaxis->SetColor("blue");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.02,0.5,"right","center");  // 凡例の位置指定
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD); 
$graph->xaxis->SetLabelAngle(90); 

/*
// Create the first line 
$p1 = new LinePlot($tuki_uketuke); 
$p1->mark->SetType(MARK_FILLEDCIRCLE); 
$p1->mark->SetFillColor("blue"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("blue"); 
$p1->SetCenter(); 
// $p1->SetLegend(" uketuke"); 
$p1->SetLegend(mb_convert_encoding("受付件数","UTF-8"));    // 凡例の名称設定
$graph->Add($p1); 
*?

/*
// ... and the second Y Scale
$graph->SetY2Scale("lin");      // Y2スケールを追加
$graph->y2axis->SetColor("red");    // Y2スケールの色
$graph->y2axis->SetWeight(2);   // Y2スケールの太さ(２ドット)
$graph->y2scale->SetGrace(10);  // Set 10% grace. 余裕スケール
$py2 = new LinePlot($rui_uketuke);  // 二つ目のラインプロットクラスの宣言
$py2->mark->SetType(MARK_STAR);     // プロットマークの形
$py2->mark->SetFillColor("red");    // プロットマークの色
$py2->mark->SetWidth(4);        // プロットマークの大きさ
$py2->SetColor("red");          // プロット線の色
$py2->SetCenter();          // プロットを中央へ
$py2->SetWeight(1);         // プロット線の太さ(１ドット)
// $py2->SetLegend(" uketuke-rui");     // 凡例は削除
$py2->SetLegend(mb_convert_encoding("受付累計","UTF-8"));    // 凡例の名称設定
$graph->AddY2($py2);            // Y2スケール用のプロット２を追加
*/

// ... and the second
$p2 = new LinePlot($tuki_kan); 
$p2->mark->SetType(MARK_STAR); 
$p2->mark->SetFillColor("black"); 
$p2->mark->SetWidth(4); 
$p2->SetColor("black"); 
$p2->SetCenter(); 
$p2->SetWeight(2);
// $p2->SetLegend(" kanryou"); 
$p2->SetLegend(mb_convert_encoding("完了件数","UTF-8"));    // 凡例の名称設定
$graph->Add($p2); 

// ... and the third
$p3 = new LinePlot($tuki_mikan); 
$p3->mark->SetType(MARK_UTRIANGLE); 
$p3->mark->SetFillColor("orange"); 
$p3->mark->SetWidth(4); 
$p3->SetColor("orange"); 
$p3->SetCenter(); 
$p3->SetWeight(2);
// $p3->SetLegend(" mikanryo"); 
$p3->SetLegend(mb_convert_encoding("未完了","UTF-8"));    // 凡例の名称設定
$graph->Add($p3); 

// Output line 
$graph->Stroke("graph/dev_req_graph4.png"); 


?>
<!DOCTYPE html>
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>プログラム開発受付・完了・未完了 件数グラフ</TITLE>
<style type="text/css">
<!--
select      {background-color:teal; color:white;}
textarea        {background-color:black; color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
.pt14           {font-size:14pt;}
.pt12b      {font:bold 12pt;}
.margin1        {margin:1%;}
-->
</style>
<script language="JavaScript">
<!--
    parent.menu_site.location = '<?php echo H_WEB_HOST . SITE_MENU ?>';
// -->
</script>
</HEAD>
<BODY class='margin1'>

<table align='center' with=100% border='2' cellspacing='0' cellpadding='0'>
    <form action='<?php echo DEV_MENU ?>' method='post'>
        <td width='60' bgcolor='blue'align='center' valign='center'><input class='pt12b' type="submit" name="dev_req_graph2.php" value="戻る" ></td>
    </form>
</table>

<table align='center' with=100% border='0'>
    <tr>
        <td align='center'>
<!--        <h3>プログラム開発 完了・未完了 件数グラフ<br> -->
            <img src='graph/dev_req_graph4.png?<?php echo uniqid(rand(),1) ?>' alt='開発受付・完了・未完了 件数グラフ' border='0'>
        </td>
<!--        <td align='center' nowrap>
            <img src='graph/graph1_legend1.png?<?php echo uniqid(rand(),1) ?>' alt='凡例' border='0'>
        </td>
--> </tr>
</table>



<table align='center' with=100% border='2' cellspacing='0' cellpadding='0'>
    <form action='<?php echo DEV_MENU ?>' method='post'>
        <td width='60' bgcolor='blue'align='center' valign='center'><input class='pt12b' type="submit" name="dev_req_graph2.php" value="戻る" ></td>
    </form>
</table>

<table align='center' with=100% border='0'>
    <tr>
        <td align='center'>
<!--        <h3>プログラム開発 受付・完了・未完了 件数グラフ<br> -->
            <img src='graph/dev_req_graph3.png?<?php echo uniqid(rand(),1) ?>' alt='開発受付・完了・未完了 件数グラフ' border='0'>
        </td>
<!--        <td align='center' nowrap>
            <img src='graph/graph1_legend1.png?<?php echo uniqid(rand(),1) ?>' alt='凡例' border='0'>
        </td>
--> </tr>
</table>



<table align='center' with=100% border='2' cellspacing='0' cellpadding='0'>
    <form action='<?php echo DEV_MENU ?>' method='post'>
        <td width='60' bgcolor='blue'align='center' valign='center'><input class='pt12b' type="submit" name="dev_req_graph2.php" value="戻る" ></td>
    </form>
</table>

<table align='center' with=100% border='0'>
    <tr>
        <td align='center'>
<!--        <h3>プログラム開発 受付累計・受付・完了・未完了 件数グラフ<br> -->
            <img src='graph/dev_req_graph2.png?<?php echo uniqid(rand(),1) ?>' alt='開発受付・完了・未完了 件数グラフ' border='0'>
        </td>
<!--        <td align='center' nowrap>
            <img src='graph/graph1_legend1.png?<?php echo uniqid(rand(),1) ?>' alt='凡例' border='0'>
        </td>
--> </tr>
</table>




</BODY>
</HTML>
 