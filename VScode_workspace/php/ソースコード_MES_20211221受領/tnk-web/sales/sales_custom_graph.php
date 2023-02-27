<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 明細 特注カプラ専用 照会                                            //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/01/21 Created   sales_custom_graph.php                              //
// 2005/01/27 売上高だけでなく総材料費のグラフも追加                        //
// 2005/06/05 最終日を31のリテラルから last_day()で取得に変更               //
// 2005/08/21 jpGraph2.0betaへUPによるvalue表示位置SetValuePos('center')追加//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1, 13);                    // site_index=01(売上メニュー) site_id=11(特注カプラ売上)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SALES_MENU);              // 通常は指定する必要はない(売上メニュー)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売上グラフ 特注カプラ専用 照会');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('売上明細', SALES . 'sales_custom_view.php');
$menu->set_retGET('sum_exec', '合計表照会');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// １カ月分のデータ抜出しfunction
function getCustomSales($strYm)
{
    if ( ($strYm < 200310) || ($strYm > date('Ym')) ) {
        return FALSE;
    }
    ///// SQL文を組立
    $last_day = last_day(substr($strYm, 0, 4), substr($strYm, 4, 2));
    $d_start = ($strYm . '01');
    $d_end   = ($strYm . $last_day);
    $where= "where
                    計上日>={$d_start} and 計上日<={$d_end} and 事業部='C' and note15 like 'SC%' {$_SESSION['custom_where_assy_no']}
    ";
    //////// 条件１
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['custom_condition1']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= 'データベースからの抽出でエラーが発生しました。管理担当者へ連絡して下さい！';
        return FALSE;
    }
    $data[0] = $res_sum[0];
    //////// 条件２
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['custom_condition2']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= 'データベースからの抽出でエラーが発生しました。管理担当者へ連絡して下さい！';
        return FALSE;
    }
    $data[1] = $res_sum[0];
    //////// 条件３
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['custom_condition3']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= 'データベースからの抽出でエラーが発生しました。管理担当者へ連絡して下さい！';
        return FALSE;
    }
    $data[2] = $res_sum[0];
    //////// 条件４
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['custom_condition4']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= 'データベースからの抽出でエラーが発生しました。管理担当者へ連絡して下さい！';
        return FALSE;
    }
    $data[3] = $res_sum[0];
    return $data;
    // 連想配列を返す
    /*****************************
    $data[0]['売上金額']    +
    $data[0]['総材料費']    |   条件１
    $data[0]['件数']        |
    $data[0]['数量']        +
    $data[1]                    条件２
    *****************************/
}

//////////// 初回のセッション登録とセッション復元
if (isset($_REQUEST['graph_exec'])) {
    $_SESSION['custom_graph_exec'] = $_REQUEST['graph_exec'];
    $graph_exec = $_SESSION['custom_graph_exec'];
} else {
    $graph_exec = $_SESSION['custom_graph_exec'];
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['custom_div'];
$d_start    = $_SESSION['custom_d_start'];
$d_end      = $_SESSION['custom_d_end'];
$kubun      = $_SESSION['custom_kubun'];
$uri_ritu   = 52;   // リテラルに変更
$assy_no    = $_SESSION['custom_assy_no'];

////////////// パスワードチェック
if ($uri_passwd != date('Ymd')) {
    $_SESSION['s_sysmsg'] = "<font color='yellow'>パスワードが違います！</font>";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

//////////// 表題の設定
// $menu->set_caption('条件１=販売価格の　５２％が仕切単価<br>条件２=総材料費の１２７％が仕切単価<br>条件３=総材料費の１００％が仕切単価<br>条件４=その他（上記以外）＿＿＿＿＿');
$menu->set_caption('条件４=その他（下記以外）＿＿＿＿＿<br>条件３=総材料費の１００％が仕切単価<br>条件２=総材料費の１２７％が仕切単価<br>条件１=販売価格の　５２％が仕切単価');

//////////// １グラフの表示月数
switch ($graph_exec) {
case 1:
    define('PAGE', 3);
    $graph_title  = '特注カプラ ３ヶ月 条件別 売上グラフ';
    $graph2_title = '特注カプラ ３ヶ月 条件別 総材料費グラフ';
    break;
case 2:
    define('PAGE', 6);
    $graph_title  = '特注カプラ ６ヶ月 条件別 売上グラフ';
    $graph2_title = '特注カプラ ６ヶ月 条件別 総材料費グラフ';
    break;
case 3:
    define('PAGE', 12);
    $graph_title  = '特注カプラ １２ヶ月 条件別 売上グラフ';
    $graph2_title = '特注カプラ １２ヶ月 条件別 総材料費グラフ';
    break;
default:
    define('PAGE', 12);
    $graph_title  = '特注カプラ １２ヶ月 条件別 売上グラフ';
    $graph2_title = '特注カプラ １２ヶ月 条件別 総材料費グラフ';
}

//////////// 合計 有効月数 取得
$ym = date('Ym');   // 現在の年月 基準点
$i = 0;
while ($ym > 200310) {
    $i++;
    $year  = substr($ym, 0, 4);
    $month = (substr($ym, 4, 2) - 1);
    if ($month < 1) {
        $year -= 1;
        $month = 12;
    }
    $ym = sprintf("%d%02d", $year, $month);
}
$maxrows = $i;

//////////// ページオフセット設定
if ( isset($_POST['forward']) ) {                       // 次頁が押された
    $_SESSION['custom_graph_offset'] += PAGE;
    if ($_SESSION['custom_graph_offset'] >= $maxrows) {
        $_SESSION['custom_graph_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // 次頁が押された
    $_SESSION['custom_graph_offset'] -= PAGE;
    if ($_SESSION['custom_graph_offset'] < 0) {
        $_SESSION['custom_graph_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {                // 現在のページを維持する GETに注意
    $offset = $_SESSION['custom_graph_offset'];
} elseif ( isset($_POST['page_keep']) ) {               // 現在のページを維持する
    $offset = $_SESSION['custom_graph_offset'];
} else {
    $_SESSION['custom_graph_offset'] = 0;               // 初回の場合は０で初期化
}
$offset = $_SESSION['custom_graph_offset'];

/////////// グラフデータの取得
$graph_flg = TRUE;
$ym = date('Ym');                   // 現在の年月 基準点
/////////// オフセット処理
for ($i=0; $i<$offset; $i++) {
    $year  = substr($ym, 0, 4);
    $month = (substr($ym, 4, 2) - 1);
    if ($month < 1) {
        $year -= 1;
        $month = 12;
    }
    $ym = sprintf("%d%02d", $year, $month);
}
for ($i=0; $i<PAGE; $i++) {
    $year  = substr($ym, 0, 4);
    $month = (substr($ym, 4, 2) - 1);
    if ($month < 1) {
        $year -= 1;
        $month = 12;
    }
    $datax[$i] = sprintf("%2\$02d\n%1\$d", $year, $month);     // X軸の項目設定 1\$ 2\$ 等で引数の交換
    $ym        = sprintf("%d%02d", $year, $month);      // 関数へ渡すデータの生成 グラフで引数としても使う
    $ym_p[$i]  = $ym;                                   // グラフイメージマップの引数として使う
    if ( ($data = getCustomSales($ym)) == FALSE) {
        $datay['売上金額'][0][$i] = 0;
        $datay['総材料費'][0][$i] = 0;
        $datay['件数'    ][0][$i] = 0;
        $datay['数量'    ][0][$i] = 0;
        $datay['売上金額'][1][$i] = 0;
        $datay['総材料費'][1][$i] = 0;
        $datay['件数'    ][1][$i] = 0;
        $datay['数量'    ][1][$i] = 0;
        $datay['売上金額'][2][$i] = 0;
        $datay['総材料費'][2][$i] = 0;
        $datay['件数'    ][2][$i] = 0;
        $datay['数量'    ][2][$i] = 0;
        $datay['売上金額'][3][$i] = 0;
        $datay['総材料費'][3][$i] = 0;
        $datay['件数'    ][3][$i] = 0;
        $datay['数量'    ][3][$i] = 0;
        if ($i == 0) $graph_flg = FALSE;            // １件目でデータが無ければグラフは作らない
    } else {
        $datay['売上金額'][0][$i] = Uround($data[0]['売上金額'] / 1000000, 1);
        $datay['総材料費'][0][$i] = Uround($data[0]['総材料費'] / 1000000, 1);
        $datay['件数'    ][0][$i] = $data[0]['件数'    ];
        $datay['数量'    ][0][$i] = $data[0]['数量'    ];
        $datay['売上金額'][1][$i] = Uround($data[1]['売上金額'] / 1000000, 1);
        $datay['総材料費'][1][$i] = Uround($data[1]['総材料費'] / 1000000, 1);
        $datay['件数'    ][1][$i] = $data[1]['件数'    ];
        $datay['数量'    ][1][$i] = $data[1]['数量'    ];
        $datay['売上金額'][2][$i] = Uround($data[2]['売上金額'] / 1000000, 1);
        $datay['総材料費'][2][$i] = Uround($data[2]['総材料費'] / 1000000, 1);
        $datay['件数'    ][2][$i] = $data[2]['件数'    ];
        $datay['数量'    ][2][$i] = $data[2]['数量'    ];
        $datay['売上金額'][3][$i] = Uround($data[3]['売上金額'] / 1000000, 1);
        $datay['総材料費'][3][$i] = Uround($data[3]['総材料費'] / 1000000, 1);
        $datay['件数'    ][3][$i] = $data[3]['件数'    ];
        $datay['数量'    ][3][$i] = $data[3]['数量'    ];
    }
}
/////////// グラフ生成
if ($graph_flg) {
    require_once ('../../jpgraph.php');
    require_once ('../../jpgraph_bar.php');
    
    /* ################################## 売上高のグラフ作成 ############################### */
    $graph = new Graph(820, 360);               // グラフの大きさ X/Y
    $graph->SetScale('textlin'); 
    $graph->img->SetMargin(50, 160, 40, 50);    // グラフ位置のマージン 左右上下
    $graph->SetShadow(); 
    // $graph->SetMarginColor('#d6d3ce');          // 表の標準色に合わせる
    
    // Setup title
    $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
    $graph->title->Set(mb_convert_encoding($graph_title, 'UTF-8')); 
    // $graph->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    // $graph->subtitle->Set(mb_convert_encoding('単位:百万円', 'UTF-8'));
    // $graph->subtitle->SetAlign('right');
    $graph->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $graph->yaxis->title->Set(mb_convert_encoding('売上高　　単位:百万円', 'UTF-8'));
    $graph->yaxis->title->SetMargin(10, 0, 0, 0);       // 売上とY軸の数値を離す 10
    
    // Setup X-scale 
    $graph->xaxis->SetTickLabels($datax);               // 項目設定
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 10);   // フォントはボールドも指定できる。
    $graph->xaxis->SetLabelAngle(0);                    // 斜めは60度
    
    // Setup format for legend
    $graph->legend->SetFillColor('antiquewhite');
    $graph->legend->SetShadow(true);
    $graph->legend->Pos(0.015, 0.5, 'right', 'center'); // 凡例の位置指定
    $graph->legend->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上
    
    /****************** 条件１ *********************/
    // Create the bar plots 
    $bplot0 = new BarPlot($datay['売上金額'][0]);
    $bplot0->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplot0->SetFillGradient('navy', 'lightsteelblue', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplot0->SetColor('navy');
    $bplot0->value->SetFormat('%01.1f');    // 少数１位フォーマット
    $bplot0->SetValuePos('center');         // 位置設定
    $bplot0->value->Show();                 // 数値表示
    if (PAGE > 6) {
        $bplot0->value->SetColor('maroon', 'navy'); // 表示色設定
    } else {
        $bplot0->value->SetColor('white', 'navy');  // 表示色設定
    }
    $bplot0->SetLegend(mb_convert_encoding('　５２％(条件１)', 'UTF-8'));   // 凡例の名称設定 \nも使用可
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('売上明細') . "?ym_p={$ym_p[$j]}&custom_view1=on')";
        $alts[$j] = "{$ym_p[$j]}の売上高 条件１ 金額＝%0.1f";
    }
    $bplot0->SetCSIMTargets($targ, $alts); 
    
    /****************** 条件２ *********************/
    // Create the bar plots 
    $bplot1 = new BarPlot($datay['売上金額'][1]);
    $bplot1->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplot1->SetFillGradient('darkgreen', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplot1->SetColor('navy');
    $bplot1->value->SetFormat('%01.1f');    // 少数１位フォーマット
    $bplot1->SetValuePos('center');         // 位置設定
    $bplot1->value->Show();                 // 数値表示
    $bplot1->SetLegend(mb_convert_encoding('１２７％(条件２)', 'UTF-8'));   // 凡例の名称設定 \nも使用可
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('売上明細') . "?ym_p={$ym_p[$j]}&custom_view2=on')";
        $alts[$j] = "{$ym_p[$j]}の売上高 条件２ 金額＝%0.1f";
    }
    $bplot1->SetCSIMTargets($targ, $alts); 
    
    /****************** 条件３ *********************/
    // Create the bar plots 
    $bplot2 = new BarPlot($datay['売上金額'][2]);
    $bplot2->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplot2->SetFillGradient('maroon', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplot2->SetColor('navy');
    $bplot2->value->SetFormat('%01.1f');    // 少数１位フォーマット
    $bplot2->SetValuePos('center');         // 位置設定
    $bplot2->value->Show();                 // 数値表示
    $bplot2->SetLegend(mb_convert_encoding('１００％(条件３)', 'UTF-8'));   // 凡例の名称設定 \nも使用可
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('売上明細') . "?ym_p={$ym_p[$j]}&custom_view3=on')";
        $alts[$j] = "{$ym_p[$j]}の売上高 条件３ 金額＝%0.1f";
    }
    $bplot2->SetCSIMTargets($targ, $alts); 
    
    /****************** 条件４ *********************/
    // Create the bar plots 
    $bplot3 = new BarPlot($datay['売上金額'][3]);
    $bplot3->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplot3->SetFillGradient('gray4', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplot3->SetColor('navy');
    $bplot3->value->SetFormat('%01.1f');    // 少数１位フォーマット
    $bplot3->SetValuePos('center');         // 位置設定
    $bplot3->value->Show();                 // 数値表示
    $bplot3->SetLegend(mb_convert_encoding('　その他(条件４)', 'UTF-8'));   // 凡例の名称設定 \nも使用可
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('売上明細') . "?ym_p={$ym_p[$j]}&custom_view4=on')";
        $alts[$j] = "{$ym_p[$j]}の売上高 条件４ 金額＝%0.1f";
    }
    $bplot3->SetCSIMTargets($targ, $alts); 
    
    // Create the grouped bar plot
    $gbplot = new AccBarPlot(array($bplot0, $bplot1, $bplot2, $bplot3));
    $gbplot->value->SetFormat('%01.1f');    // 整数フォーマット
    $gbplot->value->Show();                 // 数値表示
    
    // Create the graph
    $graph->Add($gbplot);
    // $graph_name = ('graph/sales_custom' . session_id() . '.png');
    $graph_name = 'graph/sales_custom_graph.png';
    $graph->Stroke($graph_name);
    
    
    /* ################################## 総材料費のグラフ作成 ############################### */
    $graph2 = new Graph(820, 360);               // グラフの大きさ X/Y
    $graph2->SetScale('textlin'); 
    $graph2->img->SetMargin(50, 160, 40, 50);    // グラフ位置のマージン 左右上下
    $graph2->SetShadow(); 
    // $graph2->SetMarginColor('#d6d3ce');          // 表の標準色に合わせる
    
    // Setup title
    $graph2->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
    $graph2->title->Set(mb_convert_encoding($graph2_title, 'UTF-8')); 
    // $graph2->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    // $graph2->subtitle->Set(mb_convert_encoding('単位:百万円', 'UTF-8'));
    // $graph2->subtitle->SetAlign('right');
    $graph2->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $graph2->yaxis->title->Set(mb_convert_encoding('総材料費　　単位:百万円', 'UTF-8'));
    $graph2->yaxis->title->SetMargin(10, 0, 0, 0);       // 売上とY軸の数値を離す 10
    
    // Setup X-scale 
    $graph2->xaxis->SetTickLabels($datax);               // 項目設定
    $graph2->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 10);   // フォントはボールドも指定できる。
    $graph2->xaxis->SetLabelAngle(0);                    // 斜めは60度
    
    // Setup format for legend
    $graph2->legend->SetFillColor('antiquewhite');
    $graph2->legend->SetShadow(true);
    $graph2->legend->Pos(0.015, 0.5, 'right', 'center'); // 凡例の位置指定
    $graph2->legend->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上
    
    /****************** 条件１ *********************/
    // Create the bar plots 
    $bplotM0 = new BarPlot($datay['総材料費'][0]);
    $bplotM0->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplotM0->SetFillGradient('navy', 'lightsteelblue', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplotM0->SetColor('navy');
    $bplotM0->value->SetFormat('%01.1f');    // 少数１位フォーマット
    $bplotM0->SetValuePos('center');         // 位置設定
    $bplotM0->value->Show();                 // 数値表示
    if (PAGE > 6) {
        $bplotM0->value->SetColor('maroon', 'navy');    // 表示色設定
    } else {
        $bplotM0->value->SetColor('white', 'navy');     // 表示色設定
    }
    $bplotM0->SetLegend(mb_convert_encoding('　５２％(条件１)', 'UTF-8'));   // 凡例の名称設定 \nも使用可
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('売上明細') . "?ym_p={$ym_p[$j]}&custom_view1=on')";
        $alts[$j] = "{$ym_p[$j]}の総材料費 条件１ 金額＝%0.1f";
    }
    $bplotM0->SetCSIMTargets($targ, $alts); 
    
    /****************** 条件２ *********************/
    // Create the bar plots 
    $bplotM1 = new BarPlot($datay['総材料費'][1]);
    $bplotM1->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplotM1->SetFillGradient('darkgreen', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplotM1->SetColor('navy');
    $bplotM1->value->SetFormat('%01.1f');    // 少数１位フォーマット
    $bplotM1->SetValuePos('center');         // 位置設定
    $bplotM1->value->Show();                 // 数値表示
    $bplotM1->SetLegend(mb_convert_encoding('１２７％(条件２)', 'UTF-8'));   // 凡例の名称設定 \nも使用可
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('売上明細') . "?ym_p={$ym_p[$j]}&custom_view2=on')";
        $alts[$j] = "{$ym_p[$j]}の総材料費 条件２ 金額＝%0.1f";
    }
    $bplotM1->SetCSIMTargets($targ, $alts); 
    
    /****************** 条件３ *********************/
    // Create the bar plots 
    $bplotM2 = new BarPlot($datay['総材料費'][2]);
    $bplotM2->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplotM2->SetFillGradient('maroon', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplotM2->SetColor('navy');
    $bplotM2->value->SetFormat('%01.1f');    // 少数１位フォーマット
    $bplotM2->SetValuePos('center');         // 位置設定
    $bplotM2->value->Show();                 // 数値表示
    $bplotM2->SetLegend(mb_convert_encoding('１００％(条件３)', 'UTF-8'));   // 凡例の名称設定 \nも使用可
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('売上明細') . "?ym_p={$ym_p[$j]}&custom_view3=on')";
        $alts[$j] = "{$ym_p[$j]}の総材料費 条件３ 金額＝%0.1f";
    }
    $bplotM2->SetCSIMTargets($targ, $alts); 
    
    /****************** 条件４ *********************/
    // Create the bar plots 
    $bplotM3 = new BarPlot($datay['総材料費'][3]);
    $bplotM3->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplotM3->SetFillGradient('gray4', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplotM3->SetColor('navy');
    $bplotM3->value->SetFormat('%01.1f');    // 少数１位フォーマット
    $bplotM3->SetValuePos('center');         // 位置設定
    $bplotM3->value->Show();                 // 数値表示
    $bplotM3->SetLegend(mb_convert_encoding('　その他(条件４)', 'UTF-8'));   // 凡例の名称設定 \nも使用可
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('売上明細') . "?ym_p={$ym_p[$j]}&custom_view4=on')";
        $alts[$j] = "{$ym_p[$j]}の総材料費 条件４ 金額＝%0.1f";
    }
    $bplotM3->SetCSIMTargets($targ, $alts); 
    
    // Create the grouped bar plot
    $gbplotM = new AccBarPlot(array($bplotM0, $bplotM1, $bplotM2, $bplotM3));
    $gbplotM->value->SetFormat('%01.1f');    // 整数フォーマット
    $gbplotM->value->Show();                 // 数値表示
    
    // Create the graph
    $graph2->Add($gbplotM);
    // $graph2_name = ('graph/sales_custom' . session_id() . '.png');
    $graph_name2 = 'graph/sales_custom_graph_material.png';
    $graph2->Stroke($graph_name2);
} else {
    if (isset($_REQUEST['forward'])) $_SESSION['s_sysmsg'] = '次頁はありません！';
    elseif (isset($_REQUEST['backward'])) $_SESSION['s_sysmsg'] = '前頁はありません！';
    else $_SESSION['s_sysmsg'] = 'グラフデータがありません！';
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>
<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.body.focus();                          // F2/F12キーで戻るための対応
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt10 {
    font-size:l     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow:hidden-x;'>
    <center>
<?=$menu->out_title_border()?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?= $menu->out_caption(), "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ここからグラフを表示する -------------------->
        <table width='100%' border='0'>
            <tr>
                <td align='center'>
                <?php
                if ($graph_flg) {
                    echo $graph->GetHTMLImageMap('custom_graph_map');
                    echo "<img src='", $graph_name, "?id=", $uniq, "' ismap usemap='#custom_graph_map' alt='カプラ特注 条件別 売上グラフ' border='0'>\n";
                }
                ?>
                </td>
            </tr>
            <tr>
                <td align='center'>
                <?php
                if ($graph_flg) {
                    echo $graph2->GetHTMLImageMap('custom_graph2_map');
                    echo "<img src='", $graph_name2, "?id=", $uniq, "' ismap usemap='#custom_graph2_map' alt='カプラ特注 条件別 総材料費グラフ' border='0'>\n";
                }
                ?>
                </td>
            </tr>
        </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
