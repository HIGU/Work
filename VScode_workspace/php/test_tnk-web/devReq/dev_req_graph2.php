<?php
//////////////////////////////////////////////////////////////////////////////
//プログラム開発 受付・完了・未完了 件数 グラフ                             //
// Copyright(C) 2002-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// History                                                                  //
// 2002/02/10 Created dev_req_graph2.php                                    //
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
// 2004/07/20 MenuHeader Class を追加                                       //
// 2007/09/19 E_ALL | E_STRICT 対応へロジック変更                           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // TNK 全共通 function
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
require_once ('../../jpgraph-4.4.1/src/jpgraph.php'); 
require_once ('../../jpgraph-4.4.1/src/jpgraph_line.php'); 
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);  // 認証レベル=0, リターンアドレスなし=セッションより, タイトルの指定なし

////////////// サイト設定
$menu->set_site(4, 4);      // site_index=4(プログラム開発) site_id=4(未完了件数グラフ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(DEV_MENU);     // セッションより取得
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('プログラム開発 完了・未完了 件数グラフ');
//////////// 表題の設定
$menu->set_caption('プログラム開発実績グラフ');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('開発依頼照会', DEV . 'dev_req_select.php');   // 現在は呼出先なし

$datax = array("2001-04","2001-05","2001-06","2001-07","2001-08","2001-09"); 
$tuki_uketuke = array(5,9,15,21,25,32);
$rui_uketuke = array(5,9,15,21,25,32) ;
$tuki_kan = array(5,9,15,21,25,32);
$rui_kan = array(5,9,15,21,25,32);
$tuki_mikan = array(5,9,15,21,25,32);

// A nice graph with anti-aliasing 
$graph = new Graph(770, 350, "auto");           // グラフの大きさ X/Y
$graph->img->SetMargin(30, 160, 30, 70);        // グラフ位置のマージン 左右上下
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
$py2 = new LinePlot($tuki_uketuke);  // 二つ目のラインプロットクラスの宣言
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
$p2 = new LinePlot($tuki_uketuke); 
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
$p3 = new LinePlot($tuki_uketuke); 
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
$p2 = new LinePlot($tuki_uketuke); 
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
$p3 = new LinePlot($tuki_uketuke); 
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
$p2 = new LinePlot($tuki_uketuke); 
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
$p3 = new LinePlot($tuki_uketuke); 
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

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        
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
            <form action='<?= $menu->out_RetUrl() ?>' method='post'>
                <td width='60' bgcolor='blue'align='center' valign='center'><input class='ret_font' type='submit' name='dev_req_graph2.php' value='戻る' ></td>
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
            <form action='<?= $menu->out_RetUrl() ?>' method='post'>
                <td width='60' bgcolor='blue'align='center' valign='center'><input class='ret_font' type="submit" name="dev_req_graph2.php" value="戻る" ></td>
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
    </center>
</table>
</body>
</html>
 