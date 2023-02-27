<?php
//////////////////////////////////////////////////////////////////////////////
// プログラム開発依頼 工数グラフ                                            //
// Copyright(C) 2002-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// History                                                                  //
// 2002/02/01 Created dev_req_graph_jisseki.php                             //
// 2002/02/26 件数のみを工数も追加                                          //
// 2002/08/09 register_globals = Off 対応                                   //
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
$menu->set_site(4, 3);      // site_index=4(プログラム開発) site_id=3(実績グラフ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(DEV_MENU);     // セッションより取得
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('プログラム開発実績 件数・工数グラフ');
//////////// 表題の設定
$menu->set_caption('プログラム開発実績グラフ');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('開発依頼照会', DEV . 'dev_req_select.php');   // 現在は呼出先なし

///////////// グラフ生成１
$query = "select 完了日,開発工数 from dev_req where (完了日<>'1970-01-01' or 完了日<>NULL) and 作業区='1' order by 完了日 asc";
$res = array();
if ($rows = getResult($query,$res)) {
    $start_flg = 0;             //スタート時のフラグ
    $cnt = 0;                   //配列用のカウンター
    $tuki_cnt = array();
    $tuki_kousuu = array();
    $datax    = array();
    for ($r=0; $r<$rows; $r++) {                // 各月毎の合計件数を算出
        $yyyymm = substr($res[$r][0],0,7);
        if ($start_flg == 0) {
            $tuki_cnt[$cnt] = 1;
            $tuki_kousuu[$cnt] = $res[$r][1];
            $datax[$cnt]    = $yyyymm;
            $start_flg      = 1;
        } elseif ($datax[$cnt]==$yyyymm) {
            $tuki_cnt[$cnt] += 1;
            $tuki_kousuu[$cnt] += $res[$r][1];
        } else {
            $cnt += 1;
            $datax[$cnt]     = $yyyymm;
            if (isset($tuki_cnt[$cnt])) {
                $tuki_cnt[$cnt] += 1;
                $tuki_kousuu[$cnt] += $res[$r][1];
            } else {
                $tuki_cnt[$cnt] = 1;
                $tuki_kousuu[$cnt] = $res[$r][1];
            }
        }
    }
    $rui_cnt = array();
    $rui_kousuu = array();
    for ($i=0; $i<=$cnt; $i++) {
        if ($i == 0) {
            $rui_cnt[$i] = $tuki_cnt[$i];
            $rui_kousuu[$i] = $tuki_kousuu[$i];
        } else {
            $rui_cnt[$i] = $tuki_cnt[$i] + $rui_cnt[$i-1];
            $rui_kousuu[$i] = $tuki_kousuu[$i] + $rui_kousuu[$i-1];
        }
    }
}
// Some data 
//$datax = array("2001-04","2001-05","2001-06","2001-07","2001-08","2001-09"); 
//$datay  = array(5,9,15,21,25,32); 
//$data2y = array(5,4, 6,6,4,7); 


// A nice graph with anti-aliasing 
$graph = new Graph(770,350,"auto");           // グラフの大きさ X/Y
$graph->img->SetMargin(30,110,30,60);  // グラフ位置のマージン 左右上下
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
//$graph->title->Set("Line plot with null values"); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
$graph->title->Set(mb_convert_encoding("開発実績件数グラフ","UTF-8")); 
$graph->yscale->SetGrace(10);     // Set 10% grace. 余裕スケール
$graph->yaxis->SetColor("blue");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.015,0.5,"right","center");  // 凡例の位置指定
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_FONT1); 
$graph->xaxis->SetLabelAngle(90); 

// Create the first line 
$p1 = new LinePlot($rui_cnt); 
$p1->mark->SetType(MARK_FILLEDCIRCLE); 
$p1->mark->SetFillColor("blue"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("blue"); 
$p1->SetCenter(); 
//$p1->SetLegend(" ruiseki"); 
$p1->SetLegend(mb_convert_encoding("累計","UTF-8"));    // 凡例の名称設定
$graph->Add($p1); 

// ... and the second 
$graph->SetY2Scale("lin");      // Y2スケール追加
$graph->y2axis->SetWeight(2);       // Y2スケールの太さドット
$graph->y2axis->SetColor("black");  // Y2スケールの色
$graph->y2scale->SetGrace(10);  // Set 10% grace. 余裕スケール
$p2 = new LinePlot($tuki_cnt); 
$p2->mark->SetType(MARK_STAR); 
$p2->mark->SetFillColor("red"); 
$p2->mark->SetWidth(4); 
$p2->SetColor("black");
$p2->SetCenter(); 
//$p2->SetLegend(" month"); 
$p2->SetLegend(mb_convert_encoding("月次","UTF-8"));    // 凡例の名称設定
//  $graph->Add($p2); 
$graph->AddY2($p2);
//  $graph->SetColor("red");

// Output line 
$graph->Stroke("graph/dev_req_graph1.png"); 
// echo $graph->GetHTMLImageMap("myimagemap"); 
// echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 


//////////// グラフ生成２
// A nice graph with anti-aliasing 
$graph = new Graph(770,350,"auto");           // グラフの大きさ X/Y
$graph->img->SetMargin(50,130,30,60);  // グラフ位置のマージン 左右上下
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
//$graph->title->Set("Line plot with null values"); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
$graph->title->Set(mb_convert_encoding("開発実績工数グラフ    単位:分","UTF-8")); 
$graph->yscale->SetGrace(10);     // Set 10% grace. 余裕スケール
$graph->yaxis->SetColor("blue");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.015,0.5,"right","center");  // 凡例の位置指定
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_FONT1); 
$graph->xaxis->SetLabelAngle(90); 

// Create the first line 
$p1 = new LinePlot($rui_kousuu); 
$p1->mark->SetType(MARK_FILLEDCIRCLE); 
$p1->mark->SetFillColor("blue"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("blue"); 
$p1->SetCenter(); 
//$p1->SetLegend(" ruiseki"); 
$p1->SetLegend(mb_convert_encoding("累計","UTF-8"));    // 凡例の名称設定
$graph->Add($p1); 

// ... and the second 
$graph->SetY2Scale("lin");      // Y2スケール追加
$graph->y2axis->SetWeight(2);       // Y2スケールの太さドット
$graph->y2axis->SetColor("purple");     // Y2スケールの色
$graph->y2scale->SetGrace(10);  // Set 10% grace. 余裕スケール
$p2 = new LinePlot($tuki_kousuu); 
$p2->mark->SetType(MARK_STAR); 
$p2->mark->SetFillColor("red"); 
$p2->mark->SetWidth(4); 
$p2->SetColor("purple");
$p2->SetCenter(); 
//$p2->SetLegend(" month"); 
$p2->SetLegend(mb_convert_encoding("月次","UTF-8"));    // 凡例の名称設定
//  $graph->Add($p2); 
$graph->AddY2($p2);
//  $graph->SetColor("red");

// Output line 
$graph->Stroke("graph/dev_req_graph_kousuu.png"); 
// echo $graph->GetHTMLImageMap("myimagemap"); 
// echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
        <!--        <font size='5'><b>開発実績件数グラフ<b></font><br> -->
                    <img src='graph/dev_req_graph1.png?<?php echo uniqid(rand(),1) ?>' alt='開発実績件数グラフ' border='0'>
                </td>
        <!--    <td align='center' nowrap>
                    <img src='graph/graph1_legend1.png?<?php echo uniqid(rand(),1) ?>' alt='凡例' border='0'>
                </td>
        --> </tr>
        </table>
        
        <table align='center' with=100% border='1' bordercolor='navy'>
            <th>-</th>
            <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<th>$datax[$i]</th>\n");
            ?>
            <tr>
                <td align='center' nowrap><font color='black'>件数</font></td>
                <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<td align='right'><font color='black'><b>$tuki_cnt[$i]</b></font></td>");
                ?>
            </tr>
            <tr>
                <td align='center' nowrap><font color='blue'>累積</font></td>
                <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<td align='right'><font color='blue'>$rui_cnt[$i]</font></td>");
                ?>
            </tr>
        </table>
        
        <table align='center' with=100% border='2' cellspacing='0' cellpadding='0'>
            <form action='<?= $menu->out_RetUrl() ?>' method='post'>
                <td width='60' bgcolor='blue'align='center' valign='center'><input class='ret_font' type="submit" name="dev_req_graph1" value="戻る" ></td>
            </form>
        </table>
        
        <table align='center' with=100% border='0'>
            <tr>
                <td align='center'>
        <!--        <font size='5'><b>開発 実績 工数 グラフ </b></font><font size='2'> 単位：分</font><br> -->
                    <img src='graph/dev_req_graph_kousuu.png?<?php echo uniqid(rand(),1) ?>' alt='開発実績工数グラフ' border='0'>
        <!--    </td>
                <td align='center' nowrap>
                    <img src='graph/graph1_legend1.png?<?php echo uniqid(rand(),1) ?>' alt='凡例' border='0'>
                </td>
        --> </tr>
        </table>
        
        <table align='center' with=100% border='1' bordercolor='navy'>
            <th>-</th>
            <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<th>$datax[$i]</th>\n");
            ?>
            <tr>
                <td align='center' nowrap><font color='purple'>工数(分)</font></td>
                <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<td align='right'><font color='purple'><b>" . number_format($tuki_kousuu[$i]) . "</b></font></td>");
                ?>
            </tr>
            <tr>
                <td align='center' nowrap><font color='blue'>累積(分)</font></td>
                <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<td align='right'><font color='blue'>" . number_format($rui_kousuu[$i]) . "</font></td>");
                ?>
            </tr>
        </table>
    </center>
</body>
</html>
 