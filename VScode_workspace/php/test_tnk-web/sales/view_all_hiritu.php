<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ・リニアの製品・部品 売上推移グラフ(棒グラフ)                      //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/10/01 Created   view_all_hiritu.php                                 //
// 2002/07/04 valを全体・製品などの言葉に変更                               //
// 2002/07/19 jpgraph 1.5→1.7へVersionUPに伴いクラスの仕様が変更になった   //
//            jpgraph 1.5->1.7 ShowValue()→value->Show()へ                 //
//                             SetValueFormat→value->SetFormat()へ         //
// 2002/08/08 セッション管理に切替え & register global off 対応             //
// 2002/09/20 サイトメニュー方式対応                                        //
// 2002/12/20 グラフの日本語対応版 jpGraph.php 1.9.1 マルチバイト対応へ改造 //
//            日本語版 凡例を追加                                           //
//            $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14);               //
//            FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する              //
//            $graph->title->Set(mb_convert_encoding("全体 製品・部品の売上 //
//            単位：百万円","UTF-8"));                                      //
//            $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14);              //
//            FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上                     //
//            $b1plot->SetLegend(mb_convert_encoding("製 品 ","UTF-8"));    //
//            凡例の名称設定                                                //
// 2003/05/01 jpGraph 1.12.1 UP による微調整 SetMargin() legend->Pos()      //
//       mark->SetWidth() SetLegend("製品 ")→("製品")は余分なスペーを削除  //
// 2003/09/05 グラフファイルの更新日のチェックを追加 高速化を図ったが？     //
//            error_reporting = E_ALL 対応のため 配列変数の初期化追加       //
// 2003/11/04 $graph ->yaxis->scale->SetGrace(15)追加 グラフの年月範囲を小  //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/12/29 スタート年月を 200204 → 200304 へ変更 (ページ制御を追加予定) //
//            MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/02/23 $menu->set_action()をコメントアウト 該当個所ののコメント参照  //
// 2007/05/31 ページ制御を追加(当月と過去を比較できる)。グラフデザインを変更//
// 2007/09/25 エラーチェックをE_STRICTへ SQL文のキーワードを大文字へ        //
// 2007/10/01 if ($str_ym < 200010) → if ($str_ym <= 200010) へ訂正        //
// 2007/10/31 製品と部品とを各々計算しているため四捨五入対策用ロジック追加  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);  // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../jpgraph-4.4.1/src/jpgraph.php'); 
require_once ('../../jpgraph-4.4.1/src/jpgraph_bar.php'); 
require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1,  5);                    // site_index=1(売上メニュー) site_id=5(製品・部品グラフ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('全体・カプラ・リニア 製品・部品 売上グラフ');
//////////// 表題の設定
$menu->set_caption('カーソールを金額の知りたいグラフの位置に合わせれば表示されます。');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('全体カプラリニアグラフ',   SALES . 'view_cl_graph.php');
// 上記を設定すると戻先が無限ループになる可能性があるため(互いに戻先となる時)下でリテラルで使用

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
// $str_ym = '200604';
// $end_ym = '200704';
//////////// グラフファイルの存在チェック
$graph_name1 = "graph/view_all_hiritu.png";     // 全体の製品・部品の比率
$graph_name2 = "graph/view_c_hiritu.png";       // カプラの製品・部品の比率
$graph_name3 = "graph/view_l_hiritu.png";       // リニアの製品・部品の比率 グラフ ファイル名
if (file_exists($graph_name1)) {
    //////////// 現在の年月日とグラフファイルの更新日データを取得
    $current_date = date("Ymd");
    $file_date    = date("Ymd", filemtime($graph_name1) );
    //////////// グラフファイルの更新日チェック
    if ($current_date == $file_date) {
        $create_flg = false;            // グラフ作成不要
    } else {
        $create_flg = true;             // グラフ作成
    }
} else {
    $create_flg = true;                 // グラフ作成
}
$create_flg = true;     // ImageMapがあるため都度、生成する必要がある。

///////////////// 全ての製品・部品の月次比率グラフ
        // 当月より以前の各月の金額はワークファイルを参照する
$query_wrk = "SELECT 年月, c製品+l製品 AS 製品全体, 全体-(c製品+l製品) AS 部品全体 FROM wrk_uriage WHERE 年月>={$str_ym} AND 年月<={$end_ym} ORDER BY 年月 ASC";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk, $res_wrk)) {
    $seihin_kin = array();
    $buhin_kin = array();
    $datax    = array();
    for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt は配列用のカウンター下でも使う
        if (substr(date_offset(1), 0, 6) == $res_wrk[$cnt][0]) {  // 月初にワークファイル更新時の対策
            break;
        }
        $datax[$cnt]    = $res_wrk[$cnt][0];
        $seihin_kin[$cnt] = $res_wrk[$cnt][1];
        $buhin_kin[$cnt] = $res_wrk[$cnt][2];
    }
    for ($i=0; $i<$cnt; $i++) {
        $seihin_kin[$i] = Uround($seihin_kin[$i] / 1000000, 1);   // 単位を百万円にする
        $buhin_kin[$i]  = Uround($buhin_kin[$i] / 1000000, 1);
    }
}

        // 当月は売上明細を参照する
$temp_date = date_offset(1);
$temp_date = substr($temp_date, 0, 6);
$s_date = $temp_date . '01';
$e_date = $temp_date . '31';            // datatype=1=製品 それ以外は部品の当月分検索
$query = "SELECT 計上日, Uround(数量*単価, 0) AS 金額, datatype FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date} ORDER BY 計上日 ASC";
$res = array();
if ($rows = getResult($query,$res)) {
    $datax[$cnt] = substr($res[0][0],0,6);  // X軸の項目を代入
    $seihin_kin[$cnt] = 0;                  // 初期化
    $buhin_kin[$cnt]  = 0;                  // 初期化
    for ($r=0; $r<$rows; $r++) {                // 当月の合計金額を算出
        if ($res[$r][2] == '1') {
            $seihin_kin[$cnt] += $res[$r][1];
        } else {
            $buhin_kin[$cnt] += $res[$r][1];
        }
    }
    $seihin_kin[$cnt] = Uround($seihin_kin[$cnt] / 1000000, 1);   // 単位を百万円にする
    $buhin_kin[$cnt]  = Uround($buhin_kin[$cnt] / 1000000, 1);
}
$query = "SELECT Uround(sum(Uround(数量*単価, 0))/1000000, 6) FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date}";
$tan_all = 0;
getUniResult($query, $tan_all);
$buhin_kin[$cnt] = $tan_all - $seihin_kin[$cnt];     // 四捨五入対策で追加


// Create the graph. These two calls are always required 
$graph_all = new Graph(820, 360, 'auto');       // グラフの大きさ X/Y
$graph_all->SetScale('textlin'); 
$graph_all->img->SetMargin(40, 120, 30, 70);    // グラフ位置のマージン 左右上下
$graph_all->SetShadow(); 
$graph_all->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
$graph_all->title->Set(mb_convert_encoding("全体 製品・部品の売上   単位：百万円","UTF-8")); 
$graph_all->legend->Pos(0.015, 0.5, "right", "center"); // 凡例の位置指定
$graph_all->legend->SetFont(FF_GOTHIC, FS_NORMAL, 14);  // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上

// Setup X-scale 
$graph_all->xaxis->SetTickLabels($datax); // 項目設定
$graph_all->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // 2007/05/31 変更
$graph_all->xaxis->SetLabelAngle(65);


// Create the bar plots 1
$b1plot_all = new BarPlot($seihin_kin); 
$b1plot_all->SetFillColor("orange");
$b1plot_all->SetFillGradient("darkorange3","darkgoldenrod1",GRAD_WIDE_MIDVER);
$targ_all = array();
$alts_all = array();
for ($i=0; $i<=$cnt; $i++) {
    $targ_all[$i] = 'view_cl_graph.php';
    $alts_all[$i] = "全体・製品=%3.1f";
}
$b1plot_all->SetCSIMTargets($targ_all, $alts_all);
$b1plot_all->SetLegend(mb_convert_encoding("製 品", "UTF-8"));  // 凡例の名称設定

// Create the bar plots 2
$b2plot_all = new BarPlot($buhin_kin);
$b2plot_all->SetFillColor("blue");
$b2plot_all->SetFillGradient("navy","lightsteelblue",GRAD_WIDE_MIDVER);
for ($i=0; $i<=$cnt; $i++) {
    $targ_all[$i] = 'view_cl_graph.php';
    $alts_all[$i] = "全体・部品=%3.1f";
}
$b2plot_all->SetCSIMTargets($targ_all,$alts_all); 
$b2plot_all->SetLegend(mb_convert_encoding("部 品", "UTF-8"));  // 凡例の名称設定

// Create the grouped bar plot 
$abplot_all = new AccBarPlot(array($b1plot_all, $b2plot_all)); 

// $abplot_all->SetShadow(); 
$abplot_all->value->Show(); 
$abplot_all->value->SetFormat("%3.1f"); // 整数部３桁、小数部１桁を指定
$abplot_all->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/05/31 追加

// ...and add it to the graPH 
$graph_all->Add($abplot_all); 
$graph_all->yaxis->scale->SetGrace(15);        // 2003/11/04 追加 グラフの年月範囲を小さくした

// Create and add a new text
$txt= new Text(mb_convert_encoding('←当月です', 'UTF-8'));
$txt->SetPos(730, 300, 'center');
$txt->SetFont(FF_GOTHIC, FS_NORMAL, 11);
$txt->SetBox('darkseagreen1','navy','gray');
$txt->SetColor('red');
$graph_all->AddText($txt);

/*
$graph_all->title->Set("Image map barex2"); // 各タイトル指定 省略可能
$graph_all->xaxis->title->Set("X-title"); 
$graph_all->yaxis->title->Set("Y-title"); 
*/

// $graph_all->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_all->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_all->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

// Display the graph 
$graph_all->Stroke($graph_name1); 


/////////////////////// カプラ製品・部品の月次比率グラフ
        // 当月より以前の各月の金額はワークファイルを参照する
$query_wrk = "SELECT 年月,c製品, カプラ-c製品 as 部品 FROM wrk_uriage WHERE 年月>={$str_ym} AND 年月<={$end_ym} ORDER BY 年月 ASC";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk, $res_wrk)) {
    $seihin_kin = array();
    $buhin_kin = array();
    $datax    = array();
    for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt は配列用のカウンター下でも使う
        if (substr(date_offset(1),0,6) == $res_wrk[$cnt][0]) {  // 月初にワークファイル更新時の対策
            break;
        }
        $datax[$cnt]    = $res_wrk[$cnt][0];
        $seihin_kin[$cnt] = $res_wrk[$cnt][1];
        $buhin_kin[$cnt] = $res_wrk[$cnt][2];
    }
    for ($i=0; $i<$cnt; $i++) {
        $seihin_kin[$i] = Uround($seihin_kin[$i] / 1000000,1);   // 単位を百万円にする
        $buhin_kin[$i]  = Uround($buhin_kin[$i] / 1000000,1);
    }
}

$temp_date = date_offset(1);
$temp_date = substr($temp_date, 0, 6);
$s_date = $temp_date . '01';
$e_date = $temp_date . '31';            // datatype=1=製品 それ以外は部品の当月分検索
$query = "SELECT 計上日, Uround(数量*単価, 0) as 金額, datatype FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date} AND 事業部='C' ORDER BY 計上日 ASC";
$res = array();
if ($rows = getResult($query, $res)) {
    $datax[$cnt] = substr($res[0][0], 0, 6);    // X軸の項目を代入
    $seihin_kin[$cnt] = 0;                      // 初期化
    $buhin_kin[$cnt]  = 0;                      // 初期化
    for ($r=0; $r<$rows; $r++) {                // 当月の合計金額を算出
        if ($res[$r][2] == '1') {
            $seihin_kin[$cnt] += $res[$r][1];
        } else {
            $buhin_kin[$cnt] += $res[$r][1];
        }
    }
    $seihin_kin[$cnt] = Uround($seihin_kin[$cnt] / 1000000, 1);   // 単位を百万円にする
    $buhin_kin[$cnt]  = Uround($buhin_kin[$cnt] / 1000000, 1);
}
$query = "SELECT Uround(sum(Uround(数量*単価, 0))/1000000, 6) FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date} AND 事業部='C'";
$tan_coupler_all = 0;
getUniResult($query, $tan_coupler_all);
$buhin_kin[$cnt] = $tan_coupler_all - $seihin_kin[$cnt];     // 四捨五入対策で追加


// Create the graph. These two calls are always required 
$graph_c = new Graph(820, 360, 'auto');         // グラフの大きさ X/Y
$graph_c->SetScale("textlin"); 
$graph_c->img->SetMargin(40, 120, 30, 70);      // グラフ位置のマージン 左右上下
$graph_c->SetShadow(); 
$graph_c->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
$graph_c->title->Set(mb_convert_encoding("カプラ 製品・部品の売上   単位：百万円","UTF-8")); 
$graph_c->legend->Pos(0.015,0.5,"right","center"); // 凡例の位置指定
$graph_c->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上
$graph_c->yscale->SetGrace(30);     // Set 30% grace. 余裕スケール

// Setup X-scale 
$graph_c->xaxis->SetTickLabels($datax); // 項目設定
$graph_c->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // 2007/05/31 変更
$graph_c->xaxis->SetLabelAngle(65);


// Create the bar plots 1
$b1plot_c = new BarPlot($seihin_kin);
$b1plot_c->SetFillColor("orange");
$b1plot_c->SetFillGradient("darkorange3","darkgoldenrod1",GRAD_WIDE_MIDVER);
$targ_c = array();
$alts_c = array();
for ($i=0; $i<=$cnt; $i++) {
    $targ_c[$i] = 'view_cl_graph.php';
    $alts_c[$i] = "カプラ・製品=%3.1f";
}
$b1plot_c->SetCSIMTargets($targ_c,$alts_c); 
$b1plot_c->SetLegend(mb_convert_encoding("製 品","UTF-8"));    // 凡例の名称設定

// Create the bar plots 2
$b2plot_c = new BarPlot($buhin_kin);
$b2plot_c->SetFillColor("blue");
$b2plot_c->SetFillGradient("navy","lightsteelblue",GRAD_WIDE_MIDVER);
for ($i=0; $i<=$cnt; $i++) {
    $targ_c[$i] = 'view_cl_graph.php';
    $alts_c[$i] = "カプラ・部品=%3.1f";
}
$b2plot_c->SetCSIMTargets($targ_c,$alts_c); 
$b2plot_c->SetLegend(mb_convert_encoding("部 品","UTF-8"));    // 凡例の名称設定

// Create the grouped bar plot 
$abplot_c = new AccBarPlot(array($b1plot_c,$b2plot_c)); 

// $abplot_c->SetShadow(); 
$abplot_c->value->Show(); 
$abplot_c->value->SetFormat("%3.1f"); // 整数部３桁、小数部１桁を指定
$abplot_c->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/05/31 追加

// ...and add it to the graPH 
$graph_c->Add($abplot_c); 
$graph_c->yaxis->scale->SetGrace(15);        // 2003/11/04 追加 グラフの年月範囲を小さくした

// Create and add a new text
$graph_c->AddText($txt);

/*
$graph_c->title->Set("Image map barex2"); // 各タイトル指定 省略可能
$graph_c->xaxis->title->Set("X-title"); 
$graph_c->yaxis->title->Set("Y-title"); 
*/

// $graph_c->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_c->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_c->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

// Display the graph 
$graph_c->Stroke($graph_name2); 


////////////////// リニア製品・部品の月次比率グラフ
    // 当月より以前の各月の金額はワークファイルを参照する
$query_wrk = "SELECT 年月,l製品, リニア-l製品 as 部品 FROM wrk_uriage WHERE 年月>={$str_ym} AND 年月<={$end_ym} ORDER BY 年月 ASC";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk,$res_wrk)) {
    $seihin_kin = array();
    $buhin_kin = array();
    $datax    = array();
    for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt は配列用のカウンター下でも使う
        if (substr(date_offset(1),0,6) == $res_wrk[$cnt][0]) {  // 月初にワークファイル更新時の対策
            break;
        }
        $datax[$cnt]    = $res_wrk[$cnt][0];
        $seihin_kin[$cnt] = $res_wrk[$cnt][1];
        $buhin_kin[$cnt] = $res_wrk[$cnt][2];
    }
    for ($i=0; $i<$cnt; $i++) {
        $seihin_kin[$i] = Uround($seihin_kin[$i] / 1000000, 1);   // 単位を百万円にする
        $buhin_kin[$i]  = Uround($buhin_kin[$i] / 1000000, 1);
    }
}

$temp_date = date_offset(1);
$temp_date = substr($temp_date, 0, 6);
$s_date = $temp_date . '01';
$e_date = $temp_date . '31';            // datatype=1=製品 それ以外は部品の当月分検索
$query = "SELECT 計上日, Uround(数量*単価, 0) as 金額, datatype FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date} AND 事業部='L' ORDER BY 計上日 ASC";
$res = array();
if ($rows = getResult($query, $res)) {
    $datax[$cnt] = substr($res[0][0], 0, 6);    // X軸の項目を代入
    $seihin_kin[$cnt] = 0;                      // 初期化
    $buhin_kin[$cnt]  = 0;                      // 初期化
    for ($r=0; $r<$rows; $r++) {                // 当月の合計金額を算出
        if ($res[$r][2] == '1') {
            $seihin_kin[$cnt] += $res[$r][1];
        } else {
            $buhin_kin[$cnt] += $res[$r][1];
        }
    }
    $seihin_kin[$cnt] = Uround($seihin_kin[$cnt] / 1000000, 1);   // 単位を百万円にする
    $buhin_kin[$cnt]  = Uround($buhin_kin[$cnt] / 1000000, 1);
}
$query = "SELECT Uround(sum(Uround(数量*単価, 0))/1000000, 6) FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date} AND 事業部='L'";
$tan_linear_all = 0;
getUniResult($query, $tan_linear_all);
$buhin_kin[$cnt] = $tan_linear_all - $seihin_kin[$cnt];     // 四捨五入対策で追加


// Create the graph. These two calls are always required 
$graph_l = new Graph(820, 360, 'auto');         // グラフの大きさ X/Y
$graph_l->SetScale('textlin'); 
$graph_l->img->SetMargin(40, 120, 30, 70);      // グラフ位置のマージン 左右上下
$graph_l->SetShadow(); 
$graph_l->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
$graph_l->title->Set(mb_convert_encoding("リニア 製品・部品の売上   単位：百万円","UTF-8")); 
$graph_l->legend->Pos(0.015,0.5,"right","center"); // 凡例の位置指定
$graph_l->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上
$graph_l->yscale->SetGrace(90);     // Set 90% grace. 余裕スケール

// Setup X-scale 
$graph_l->xaxis->SetTickLabels($datax); // 項目設定
$graph_l->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // 2007/05/31 変更
$graph_l->xaxis->SetLabelAngle(65);


// Create the bar plots 1
$b1plot_l = new BarPlot($seihin_kin);
$b1plot_l->SetFillColor("orange");
$b1plot_l->SetFillGradient("darkorange3","darkgoldenrod1",GRAD_WIDE_MIDVER);
$targ_l = array();
$alts_l = array();
for ($i=0; $i<=$cnt; $i++) {
    $targ_l[$i] = 'view_cl_graph.php';
    $alts_l[$i] = "リニア・製品=%3.1f";
}
$b1plot_l->SetCSIMTargets($targ_l,$alts_l); 
$b1plot_l->SetLegend(mb_convert_encoding("製 品","UTF-8"));    // 凡例の名称設定

// Create the bar plots 2
$b2plot_l = new BarPlot($buhin_kin);
$b2plot_l->SetFillColor("blue");
$b2plot_l->SetFillGradient("navy","lightsteelblue",GRAD_WIDE_MIDVER);
for ($i=0; $i<=$cnt; $i++) {
    $targ_l[$i] = 'view_cl_graph.php';
    $alts_l[$i] = "リニア・部品=%3.1f";
}
$b2plot_l->SetCSIMTargets($targ_l,$alts_l); 
$b2plot_l->SetLegend(mb_convert_encoding("部 品","UTF-8"));    // 凡例の名称設定

// Create the grouped bar plot 
$abplot_l = new AccBarPlot(array($b1plot_l,$b2plot_l)); 

// $abplot_l->SetShadow(); 
$abplot_l->value->Show(); 
$abplot_l->value->SetFormat("%3.1f"); // 整数部３桁、小数部１桁を指定
$abplot_l->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/05/31 追加

// ...and add it to the graPH 
$graph_l->Add($abplot_l); 
$graph_l->yaxis->scale->SetGrace(15);        // 2003/11/04 追加 グラフの年月範囲を小さくした

// Create and add a new text
$graph_l->AddText($txt);

/*
$graph_l->title->Set("Image map barex2"); // 各タイトル指定 省略可能
$graph_l->xaxis->title->Set("X-title"); 
$graph_l->yaxis->title->Set("Y-title"); 
*/

// $graph_l->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_l->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_l->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

// Display the graph 
$graph_l->Stroke($graph_name3); 


/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" constent="text/javascript">
<title><?php echo $menu->out_title()?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<style type='text/css'>
<!--
select      {background-color:teal; color:white;}
textarea        {background-color:black; color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
.pt10b      {font-size:10pt; font-weight:bold;}
.pt11           {font-size:11pt;}
.pt12b      {font-size:12pt; font-weight:bold;}
.right      {text-align:right;}
.center     {text-align:center;}
.left           {text-align:left;}
.margin1        {margin:1%;}
.margin0        {margin:0%;}
.fc_red     {color:red;
             background-color:blue;}
.fc_orange      {color:orange;}
.fc_yellow      {color:yellow;
             background-color:blue;}
.fc_white       {color:white;
             background-color:blue;
             font-weight:bold;}
-->
</style>
</head>
<body>
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
        <table width=100% border='0'>
            <tr><td align='center'>
                <?php
                echo $graph_all->GetHTMLImageMap("all_imagemap"); 
                echo "\n<img src='" . $graph_name1 . "?" . uniqid(rand(),1) . "' alt='全体 製品・部品の売上グラフ' ISMAP USEMAP=\"#all_imagemap\" border=0>"; 
                ?>
            </td></tr>
            <tr><td align='center'><?php echo $menu->out_caption() ?></td></tr>
        </table>

        <table align='center' width='70' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
            <form method='get' action='<?php echo $menu->out_RetUrl() ?>'>
                <td align='center'><input class='pt12b' type='submit' name='return' value='戻る'></td>
            </form>
        </table>

        <table width=100% border='0'>
            <tr><td align='center'>
                <?php
                echo $graph_c->GetHTMLImageMap("カプラ"); 
                echo "\n<img src='". $graph_name2 . "?" . uniqid(rand(),1) . "' alt='カプラ 製品・部品の売上グラフ' ISMAP USEMAP=\"#カプラ\" border=0>"; 
                ?>
            </td></tr>
            <tr><td align='center'><?php echo $menu->out_caption() ?></td></tr>
        </table>

        <table align='center' width='70' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
            <form method='get' action='<?php echo $menu->out_RetUrl() ?>'>
                <td align='center'><input class='pt12b' type='submit' name='return' value='戻る'></td>
            </form>
        </table>

        <table width=100% border='0'>
            <tr><td align='center'>
                <?php
                echo $graph_l->GetHTMLImageMap("リニア"); 
                echo "\n<img src='". $graph_name3 . "?" . uniqid(rand(),1) . "' alt='リニア 製品・部品の売上グラフ' ISMAP USEMAP=\"#リニア\" border=0>"; 
                ?>
            </td></tr>
            <tr><td align='center'><?php echo $menu->out_caption() ?></td></tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
