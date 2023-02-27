<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ・リニアの売上推移グラフ(棒グラフ)                                 //
// Copyright (C)2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2001/10/01 Created   view_cl_graph.php                                   //
// 2002/07/02 グラフに合わせて表をカプラはオレンジ・リニアはブルーに変更    //
// 2002/07/19 jpgraph 1.5→1.7へVersionUPに伴いクラスの仕様が変更になった   //
// 2002/08/08 セッション管理に切替えたため戻るボタンを追加                  //
// 2002/09/20 サイトメニュー方式対応         ↑register global off 対応     //
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
//            mark->SetWidth()                                              //
// 2003/09/05 グラフファイルの更新日のチェックを追加 高速化を図ったが？     //
//            error_reporting = E_ALL 対応のため 配列変数の初期化追加       //
// 2003/11/04 $graph ->yaxis->scale->SetGrace(15)追加 グラフの年月範囲を小  //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/12/29 スタート年月を 200204 → 200304 へ変更 (ページ制御を追加予定) //
//            MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/02/23 $menu->set_action()をコメントアウト 該当個所ののコメント参照  //
// 2007/09/25 ページ制御を追加(当月と過去を比較できる)。グラフデザインを変更//
//            エラーチェックをE_STRICTへ SQL文のキーワードを大文字へ        //
// 2007/10/01 if ($str_ym < 200010) → if ($str_ym <= 200010) へ訂正        //
// 2007/10/31 phpコードとSQL文にUround()関数を追加                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);       // E_ALL='2047' debug 用
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
$menu->set_site( 1,  6);                    // site_index=1(売上メニュー) site_id=6(CLグラフ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('カプラとリニアの売上合計');
//////////// 表題の設定
$menu->set_caption('売上比率(%)');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('全体カプラリニアグラフ',   SALES . 'view_all_hiritu.php');
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
// $str_ym = '200504';
//////////// グラフファイルの存在チェック
$graph_name = 'graph/view_cl_graph.png';
if (file_exists($graph_name)) {
    //////////// 現在の年月日とグラフファイルの更新日データを取得
    $current_date = date("Ymd");
    $file_date    = date("Ymd", filemtime($graph_name) );
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

/////////////// グラフ生成
        // 当月より以前の各月の金額はワークファイルを参照する
$query_wrk = "SELECT 年月, カプラ, リニア FROM wrk_uriage WHERE 年月>={$str_ym} AND 年月<={$end_ym} ORDER BY 年月 ASC";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk,$res_wrk)) {
    $tuki_kin_c = array();
    $tuki_kin_l = array();
    $datax    = array();
    for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt は配列用のカウンター下でも使う
        if (substr(date_offset(1), 0, 6) == $res_wrk[$cnt][0]) {  // 月初にワークファイル更新時の対策
            break;
        }
        $datax[$cnt]    = $res_wrk[$cnt][0];
        $tuki_kin_c[$cnt] = $res_wrk[$cnt][1];
        $tuki_kin_l[$cnt] = $res_wrk[$cnt][2];
    }
    $rui_kin_c = array();
    $rui_kin_l = array();
    for ($i=0; $i<$cnt; $i++) {             // ワークファイルの分の累積を求める。カプラとリニア
        if ($i==0) {
            $rui_kin_c[$i] = $tuki_kin_c[$i];
            $rui_kin_l[$i] = $tuki_kin_l[$i];
        } else {
            $rui_kin_c[$i] = $tuki_kin_c[$i] + $rui_kin_c[$i-1];
            $rui_kin_l[$i] = $tuki_kin_l[$i] + $rui_kin_l[$i-1];
        }
    }
    for ($i=0; $i<$cnt; $i++) {
        $tuki_kin_c[$i] = Uround($tuki_kin_c[$i] / 1000000, 1);   // 単位を百万円にする
        $rui_kin_c[$i]  = Uround($rui_kin_c[$i] / 1000000, 1);
        $tuki_kin_l[$i] = Uround($tuki_kin_l[$i] / 1000000, 1);   // 単位を百万円にする
        $rui_kin_l[$i]  = Uround($rui_kin_l[$i] / 1000000, 1);
    }
}

$temp_date = date_offset(1);                    // 当月分のカプラを売上明細から拾う。
$temp_date = substr($temp_date, 0, 6);
$s_date = $temp_date . '01';
$e_date = $temp_date . '31';
$query = "SELECT 計上日, Uround(数量*単価, 0) AS 金額 FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date} AND 事業部='C' ORDER BY 計上日 ASC";
$res = array();
if ($rows = getResult($query,$res)) {
    $cnt_c = $cnt;                  // カプラの配列値をcnt値にする
    $datax[$cnt] = $temp_date;          // X軸の項目を代入
    $tuki_kin_c[$cnt] = 0;              // 初期化 当月分を初期化
    for ($r=0; $r<$rows; $r++) {                // 当月の合計金額を算出
        $tuki_kin_c[$cnt] += $res[$r][1];       // カプラ
    }
    $rui_kin_c[$cnt] = $tuki_kin_c[$cnt] + $rui_kin_c[$cnt-1];      // 当月分の累積を配列に追加する。
    
    $tuki_kin_c[$cnt] = Uround($tuki_kin_c[$cnt] / 1000000, 1);       // 単位を百万円にする
    $rui_kin_c[$cnt]  = Uround($rui_kin_c[$cnt] / 1000000, 1);
} else {
    $cnt_c = $cnt - 1;              // 当月のデータが無ければカプラの配列値を-1する
}
$query = "SELECT 計上日, Uround(数量*単価, 0) AS 金額 FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date} AND 事業部='L' ORDER BY 計上日 ASC";
$res = array();
if ($rows = getResult($query,$res)) {
    $cnt_l = $cnt;                  // リニアの配列値をcnt値にする
    $datax[$cnt] = $temp_date;          // X軸の項目を代入
    $tuki_kin_l[$cnt] = 0;              // 初期化 当月分
    for ($r=0; $r<$rows; $r++) {                  // 当月の合計金額を算出
        $tuki_kin_l[$cnt] += $res[$r][1];   // リニア
    }
    $rui_kin_l[$cnt] = $tuki_kin_l[$cnt] + $rui_kin_l[$cnt-1];      // 当月分の累積を配列に追加する。
    
    $tuki_kin_l[$cnt] = Uround($tuki_kin_l[$cnt] / 1000000, 1);       // 単位を百万円にする
    $rui_kin_l[$cnt]  = Uround($rui_kin_l[$cnt] / 1000000, 1);
} else {
    $cnt_l = $cnt - 1;              // 当月のデータが無ければリニアの配列値を-1する
}

////////// グラフ生成チェック
if ($create_flg) {
    //$datax =array("2001/04","2001/05","2001/06","2001/07","2001/08","2001/09","2001/10","2001/11","2001/12","2002/01","2002/02","2002/03");
    //$tuki_kin_c=array(   342.3 ,   347.1 ,   347.6 ,   338.7 ,   319.5 ,   378.5 ,   336.7 ,   321.8 ,   267.2 ,   241.3 ,       0 ,       0 ); // カプラ
    //$tuki_kin_l=array(   125.9 ,   129.2 ,   151.6 ,   126.0 ,   141.5 ,   113.0 ,    96.2 ,    86.1 ,   107.1 ,    91.4 ,       0 ,       0 ); // リニア
    
    // Create the graph. These two calls are always required 
    $graph = new Graph(820, 360, 'auto');       // グラフの大きさ X/Y
    $graph->SetScale('textlin'); 
    $graph->img->SetMargin(40, 120, 30, 70);    // グラフ位置のマージン 左右上下
    $graph->SetShadow(); 
    $graph->title->SetFont(FF_GOTHIC,FS_NORMAL, 14);    // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
    $graph->title->Set(mb_convert_encoding('カプラ・リニアの売上比率   単位：百万円', 'UTF-8')); 
    $graph->legend->Pos(0.015, 0.5, 'right', 'center'); // 凡例の位置指定
    $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL, 14);   // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上
    
    // Setup X-scale 
    $graph->xaxis->SetTickLabels($datax); // 項目設定
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // フォントはボールドも指定できる。
    $graph->xaxis->SetLabelAngle(65); 
    
    
    // Create the bar plots 
    $b1plot = new BarPlot($tuki_kin_c); 
    $b1plot->SetFillColor('orange'); 
    $b1plot->SetFillGradient('darkorange3', 'darkgoldenrod1', GRAD_WIDE_MIDVER);
    $targ = array();
    $alts = array();
    for ($i=0; $i<=$cnt_c; $i++) {
        $targ[$i] = 'view_all_hiritu.php';
        $alts[$i] = 'カプラ=%3.1f';
    }
    //$targ=array("view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php",
    //            "view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php"); 
    //$alts=array("val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f",
    //            "val=%3.1f","val=%3.1f","val=%3.1f"); 
    $b1plot->SetCSIMTargets($targ, $alts); 
    // $b1plot->SetLegend('CUPLA');    // 凡例の名称設定
    $b1plot->SetLegend(mb_convert_encoding('カプラ', 'UTF-8'));    // 凡例の名称設定
    
    $b2plot = new BarPlot($tuki_kin_l); 
    $b2plot->SetFillColor('blue'); 
    $b2plot->SetFillGradient('navy', 'lightsteelblue', GRAD_WIDE_MIDVER);
    $targ = array();
    $alts = array();
    for ($i=0; $i<=$cnt_l; $i++) {
        $targ[$i] = 'view_all_hiritu.php';
        $alts[$i] = 'リニア=%3.1f';
    }
    //$targ=array("view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php",
    //            "view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php"); 
    //$alts=array("val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f",
    //            "val=%3.1f","val=%3.1f","val=%3.1f"); 
    $b2plot->SetCSIMTargets($targ, $alts); 
    // $b2plot->SetLegend("LINEAR");    // 凡例の名称設定
    $b2plot->SetLegend(mb_convert_encoding('リニア', 'UTF-8'));    // 凡例の名称設定
    
    // Create the grouped bar plot 
    $abplot = new AccBarPlot(array($b1plot, $b2plot)); 
    
    // $abplot->SetShadow();    // 2007/09/25 コメントアウト
    // 2002/07/19 jpgraph 1.5->1.7 ShowValue()→value->Show()へ
    $abplot->value->Show(); 
    $abplot->value->SetFormat('%3.1f'); // 整数部３桁、小数部１桁を指定
    // 2002/07/19 end
    $abplot->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/09/25 追加
    
    // ...and add it to the graPH 
    $graph->Add($abplot);
    $graph ->yaxis->scale->SetGrace(15);        // 2003/11/04 追加 グラフの年月範囲を小さくした
    
    // Create and add a new text 2007/09/25 ADD
    $txt= new Text(mb_convert_encoding('←当月です', 'UTF-8'));
    $txt->SetPos(730, 300, 'center');
    $txt->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $txt->SetBox('darkseagreen1','navy','gray');
    $txt->SetColor('red');
    $graph->AddText($txt);
    
    // Display the graph 
    $graph->Stroke($graph_name); 
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<style type="text/css">
<!--
select      {background-color:teal; color:white;}
textarea        {background-color:black; color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
.pt10b      {font-size:0.80em; font-weight:bold;}
.pt11           {font-size:11pt;}
.pt12b      {font:bold 12pt;}
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
<body style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        
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
        <table width='100%'>
            <tr><td align='center'>
                <?php
                echo $graph->GetHTMLImageMap("myimagemap"); 
                echo "\n<img src='" . $graph_name . "?" . uniqid(rand(),1) . "' ISMAP USEMAP='#myimagemap' border=0>"; 
                ?>
            </td></tr>
            <tr><td align='center'>カーソールを金額の知りたいグラフの位置に合わせれば表示されます。</td></tr>
            <tr><td align='center'>カプラ・リニアの各グラフをクリックすれば製品・部品の比率グラフを表示します。</td></tr>
        </table>
        <table width='400' align='center' border='1' bordercolor='teal' cellspacing='0' cellpadding='3'>
            <th >------</th>
            <th bgcolor='orange'>カプラ</th>
            <th class='fc_white'>リニア</th>
            <th>全体</th>
            <tr>
                <td align='center'>当月合計</td>
                <td align='right' bgcolor='orange'><?php echo number_format($tuki_kin_c[$cnt_c], 1) ?></td>
                <td align='right' class='fc_white'><?php echo number_format($tuki_kin_l[$cnt_l], 1) ?></td>
                <td align='right'><?php echo number_format($tuki_kin_c[$cnt_c]+$tuki_kin_l[$cnt_l], 1) ?></td>
            </tr>
            <tr>
                <td align='center'><?php echo $menu->out_caption()?></td>
                <td align='right' bgcolor='orange'><?php echo number_format(($tuki_kin_c[$cnt_c] / ($tuki_kin_c[$cnt_c] + $tuki_kin_l[$cnt_l])) * 100, 1) ?></td>
                <td align='right' class='fc_white'><?php echo number_format(100 - (($tuki_kin_c[$cnt_c] / ($tuki_kin_c[$cnt_c] + $tuki_kin_l[$cnt_l])) * 100), 1) ?></td>
                <td align='right'>100.0</td>
            </tr>
            <tr>
                <td align='center'>累計</td>
                <td align='right' bgcolor='orange'><?php echo number_format($rui_kin_c[$cnt_c]+$rui_kin_c[$cnt_c-1], 1) ?></td>
                <td align='right' class='fc_white'><?php echo number_format($rui_kin_l[$cnt_l]+$rui_kin_l[$cnt_l-1], 1) ?></td>
                <td align='right'><?php echo number_format($rui_kin_c[$cnt_c]+$rui_kin_l[$cnt_l]+$rui_kin_c[$cnt_c-1]+$rui_kin_l[$cnt_l-1], 1) ?></td>
            </tr>
            <tr>
                <td align='center'><?php echo $menu->out_caption()?></td>
                <td align='right' bgcolor='orange'><?php echo number_format(( ($rui_kin_c[$cnt_c]+$rui_kin_c[$cnt_c-1]) / ($rui_kin_c[$cnt_c]+$rui_kin_c[$cnt_c-1] + $rui_kin_l[$cnt_l]+$rui_kin_l[$cnt_l-1])) * 100, 1) ?></td>
                <td align='right' class='fc_white'><?php echo number_format(100 - (( ($rui_kin_c[$cnt_c]+$rui_kin_c[$cnt_c-1]) / ($rui_kin_c[$cnt_c]+$rui_kin_c[$cnt_c-1] + $rui_kin_l[$cnt_l]+$rui_kin_l[$cnt_l-1])) * 100), 1) ?></td>
                <td align='right'>100.0</td>
            </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
