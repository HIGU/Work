<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 日計 グラフ (全体・カプラ・リニア)                                  //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/11/01 Created   uriage_graph_all_niti.php                           //
// 2002/04/08 年月指定に対応                                                //
// 2002/08/08 戻るボタンのtableでwith=100% のスペルミスを削除               //
// 2002/08/08 セッション管理に切替え                                        //
// 2002/09/21 グラフを常に最新で表示させるためにuniqid(rand(),1)            //
// 2002/10/05 processing_msg.php を追加(計算中)                             //
// 2002/12/20 グラフの日本語対応版 jpGraph.php 1.9.1 マルチバイト対応へ改造 //
//              $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14);             //
//               FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する           //
//            title は ＯＫだが 凡例は うまくいかない jpgraph_bar.php はOK  //
//            $graph->title->SetFont(FF_GOTHIC); // FF_GOTHIC のみ          //
//                     ↓                                                   //
//            $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14);               //
//            FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する              //
//            Legend も うまくいったため変更                                //
//            $graph->legend->Pos(0.02,0.5,"right","center");               //
//            凡例の位置指定(左右マージン,上下マージン)                     //
//            $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14);              //
//            FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上                     //
// 2003/05/01 jpGraph 1.12.1 UP による微調整 SetMargin() legend->Pos()      //
//            mark->SetWidth()                                              //
// 2003/09/05 グラフファイルの更新日のチェックを追加 高速化を図ろうとしたが //
//            error_reporting = E_ALL 対応のため 配列変数の初期化追加。     //
// 2003/12/10 凡例が月次になっているのを日計へ訂正 タイトルの日次を日計へ   //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/10/03 ページ制御を追加(１ヶ月毎のページ表示)  E_ALL | E_STRICTへ    //
//            直接年月指定を追加。日計及び累計の個別に金額表示・非表示追加  //
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
require_once ('../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
require_once ('../../jpgraph-4.4.1/src/jpgraph.php'); 
require_once ('../../jpgraph-4.4.1/src/jpgraph_line.php'); 
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1,  3);                    // site_index=1(売上メニュー) site_id=3(日計グラフ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SALES_MENU);              // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売上日計グラフ');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('日計グラフ',   SALES . 'uriage_graph_all_niti.php');

//////////// セッションのインスタンスを生成
$session = new Session();

//////////// グローバル変数 年月の設定と処理中のメッセージ出力
if ( isset($_REQUEST['yyyymm']) ) {
    $session->add_local('yyyymm', $_REQUEST['yyyymm']);
    if ( isset($_REQUEST['dailyValue']) ) $session->add_local('dailyValue', $_REQUEST['dailyValue']);
    if ( isset($_REQUEST['totalValue']) ) $session->add_local('totalValue', $_REQUEST['totalValue']);
    // $yyyymm = $_REQUEST['yyyymm'];
    // header("Location: http:" . WEB_HOST . "processing_msg.php?script=". SALES ."uriage_graph_all_niti.php");
    header('Location: ' . H_WEB_HOST . '/processing_msg.php?script=' . $menu->out_self());
    exit(); ////////// これがないとスクリプトを最後までチェックするので時間がかかる。
} elseif ($session->get_local('yyyymm') != '') {
    $yyyymm = $session->get_local('yyyymm');
} else {
    $yyyymm = date('Ym');
}
//////////// 呼出し元へ年月データ戻し
$menu->set_retGET('yyyymm', $yyyymm);

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('dailyGraph');

//////////// グラフの値表示ON/OFFアンカーソース生成
if ($session->get_local('dailyValue') == 'on') {
    $dailyValue = "<a href='{$menu->out_self()}?yyyymm={$yyyymm}&dailyValue=off&{$uniq}'>日計金額非表示</a>　　\n";
} else {
    $dailyValue = "<a href='{$menu->out_self()}?yyyymm={$yyyymm}&dailyValue=on&{$uniq}'>日計金額表示</a>　　\n";
}
if ($session->get_local('totalValue') == 'on') {
    $totalValue = "<a href='{$menu->out_self()}?yyyymm={$yyyymm}&totalValue=off&{$uniq}'>累計金額非表示</a>　　\n";
} else {
    $totalValue = "<a href='{$menu->out_self()}?yyyymm={$yyyymm}&totalValue=on&{$uniq}'>累計金額表示</a>　　\n";
}

//////////// 表示する年月の選択フォーム取得
$ym_form = ymFormCreate($menu, $yyyymm);

//////////// 表題の設定
$yyyy = substr($yyyymm, 0, 4);
$mm   = substr($yyyymm, 4, 2);
$menu->set_caption("日計グラフ({$yyyy}年{$mm}月)");

//////////// 前月・次月のデータ生成
if ($mm == 1) {
    $next_yyyymm = $yyyy . '02';
    $yyyy--;
    $pre_yyyymm = $yyyy . '12';
} elseif ($mm == 12) {
    $pre_yyyymm = $yyyy . '11';
    $yyyy++;
    $next_yyyymm = $yyyy . '01';
} else {
    $pre_yyyymm = $yyyymm - 1;
    $next_yyyymm = $yyyymm + 1;
}
//////////// 前月・次月の有効･無効の設定
if ($yyyymm >= date('Ym')) {
    $forward = ' disabled';
} else {
    $forward = '';
}
if ($pre_yyyymm < 200010) {
    $backward = ' disabled';
} else {
    $backward = '';
}

//////////// グラフファイルの存在チェック
$graph_name1 = "graph/uriage_graph_all_niti.png";       // 全体の日次売上 折線グラフ
$graph_name2 = "graph/uriage_graph_all_niti_c.png";     // カプラの日次売上 折線グラフ
$graph_name3 = "graph/uriage_graph_all_niti_l.png";     // リニアの日次売上 折線グラフ

/////////////// グラフ範囲の年月日設定
$s_date = $yyyymm . '01';
$e_date = $yyyymm . '31';

/////////////////// 全体の日次グラフ
$query = "SELECT 計上日, sum(Uround(数量*単価, 0)) AS 金額 FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date} GROUP BY 計上日 ORDER BY 計上日 ASC";
graphDataCreate($query, '全体', $graph_name1);

/////////////////// カプラの日次グラフ
$query = "SELECT 計上日, sum(Uround(数量*単価, 0)) AS 金額 FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date} AND 事業部='C' GROUP BY 計上日 ORDER BY 計上日 ASC";
graphDataCreate($query, 'カプラ', $graph_name2);

////////////////////// リニアの日次グラフ
$query = "SELECT 計上日, sum(Uround(数量*単価, 0)) AS 金額 FROM hiuuri WHERE 計上日>={$s_date} AND 計上日<={$e_date} AND 事業部='L' GROUP BY 計上日 ORDER BY 計上日 ASC";
graphDataCreate($query, 'リニア', $graph_name3);

function graphDataCreate($query, $title, $graph_name)
{
    global $yyyymm;
    $res = array();
    if ($rows = getResult($query, $res)) {
        $start_flg = 0;             //スタート時のフラグ
        $niti_kin = array();
        $datax    = array();
        for ($r=0; $r<$rows; $r++) {                // 各日毎の合計金額を算出
            $datax[$r] = $res[$r][0];
            $niti_kin[$r] = $res[$r][1];
        }
        $rui_kin = array();
        for ($i=0; $i<$rows; $i++) {
            if ($i == 0) {
                $rui_kin[$i] = $niti_kin[$i];
            } else {
                $rui_kin[$i] = $niti_kin[$i] + $rui_kin[$i-1];
            }
        }
        for ($i=0; $i<$rows; $i++) {
            $niti_kin[$i] = Uround($niti_kin[$i] / 1000000, 1);
            $rui_kin[$i]  = Uround($rui_kin[$i]  / 1000000, 1);
        }
    } else {
        $datax = array("$yyyymm");
        $niti_kin = array(0.0);
        $rui_kin  = array(0.0);
    }
    ///// グラフ作成
    graphCreate($datax, $rui_kin, $niti_kin, $title, $graph_name);
    return;
}

function userFormat($aLabel)
{
    return number_format($aLabel, 1);
}

function graphCreate($datax, $rui_kin, $niti_kin, $title, $graph_name)
{
    global $yyyymm, $session;
    $ym_format = substr($yyyymm, 0, 4) . '年' . substr($yyyymm, 4, 2) . '月';
    // Some data 
    //$datax = array("2001-04","2001-05","2001-06","2001-07","2001-08","2001-09"); 
    //$datay  = array(5,9,15,21,25,32); 
    //$data2y = array(5,4, 6,6,4,7); 
    
    // A nice graph with anti-aliasing 
    $graph = new Graph(740, 350, 'auto');       // グラフの大きさ X/Y
    $graph->img->SetMargin(40, 110, 30, 60);    // グラフ位置のマージン 左右上下
    $graph->SetScale('textlin'); 
    $graph->SetShadow(); 
    // Slightly adjust the legend from it's default position in the 
    // top right corner. 
    $graph->legend->Pos(0.015, 0.5, 'right', 'center'); // 凡例の位置指定(左右マージン,上下マージン,"right","center")
    $graph->legend->SetFont(FF_GOTHIC, FS_NORMAL, 14);  // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上
    
    // $graph->title->Set("Line plot with null values"); 
    $graph->yscale->SetGrace(10);     // Set 10% grace. 余裕スケール
    $graph->yaxis->SetColor('blue');
    $graph->yaxis->SetWeight(2);
    
    // Use built in font 
    $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
    $graph->title->Set(mb_convert_encoding("{$ym_format} 売上実績 日計 {$title} グラフ   単位：百万円", 'UTF-8')); 
    // $graph->title->SetFont(FF_FONT1,FS_BOLD); 
    
    // Setup X-scale
    $graph->xaxis->SetTickLabels($datax);
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // フォントはボールドも指定できる。
    $graph->xaxis->SetLabelAngle(35);
    
    // Create the first line
    $p1 = new LinePlot($rui_kin);
    $p1->mark->SetType(MARK_FILLEDCIRCLE);
    $p1->mark->SetFillColor('blue');
    $p1->mark->SetWidth(2);
    $p1->SetColor('blue');
    $p1->SetCenter(); 
    $p1->SetLegend(mb_convert_encoding('累計', 'UTF-8'));
    // $p1->value->SetFormat('%01.1f'); // 整数部が無い時は0、小数部１桁を指定
    $p1->value->SetFormatCallback('userFormat');    // 上記では３桁のカンマに対応できないため
    $p1->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    if ($session->get_local('totalValue') == 'on') {
        $p1->value->Show();
    }
    $graph->Add($p1); 
    
    // ... and the second 
    $graph->SetY2Scale('lin');      // Y2スケールを追加
    $graph->y2axis->SetColor('red');// Y2スケールの色
    $graph->y2axis->SetWeight(2);   // Y2スケールの太さ(２ドット)
    $graph->y2scale->SetGrace(10);  // Set 10% grace. 余裕スケール
    $p2 = new LinePlot($niti_kin);  // 二つ目のラインプロットクラスの宣言
    $p2->mark->SetType(MARK_STAR);  // プロットマークの形
    $p2->mark->SetFillColor("red"); // プロットマークの色
    $p2->mark->SetWidth(4);         // プロットマークの大きさ
    $p2->SetColor('red');           // プロット線の色
    $p2->SetCenter();               // プロットを中央へ
    $p2->SetWeight(1);              // プロット線の太さ(１ドット)
    $p2->SetLegend(mb_convert_encoding('日計', 'UTF-8')); 
    // $p2->value->SetFormat('%01.1f'); // 整数部が無い時は0、小数部１桁を指定
    $p2->value->SetFormatCallback('userFormat');    // 上記では３桁のカンマに対応できないため
    $p2->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $p2->value->SetColor('red');                    // 値のの色
    if ($session->get_local('dailyValue') == 'on') {
        $p2->value->Show();
    }
    //  $graph->Add($p2);           // 普通のグラフからY2スケールへ変更のためコメント
    $graph->AddY2($p2);             // Y2スケール用のプロット２を追加
    
    // Output line 
    $graph->Stroke($graph_name); 
    // echo $graph->GetHTMLImageMap("myimagemap"); 
    // echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 
    return;
}

//////////// 表示する年月の選択フォーム生成
function ymFormCreate($menu, $yyyymm)
{
    $ym_form = "
        <form name='ym_form' action='{$menu->out_self()}' method='get'>
        <select name='yyyymm' onChange='document.ym_form.submit()'>
    ";
    $current_ym = date('Ym');
    if ($yyyymm == $current_ym) {
        $ym_form .= "    <option value='{$current_ym}' selected>{$current_ym}</option>\n";
    } else {
        $ym_form .= "    <option value='{$current_ym}'>{$current_ym}</option>\n";
    }
                    // 当月より以前の各 年月はワークファイルを参照する
    $query_wrk = "SELECT 年月 FROM wrk_uriage WHERE 年月>=200010 ORDER BY 年月 DESC";
    $res_wrk = array();
    if ( ($rows_wrk=getResult2($query_wrk, $res_wrk)) > 0 ) {
        for ($i=0; $i<$rows_wrk; $i++) {
            if ($yyyymm == $res_wrk[$i][0]) {
                $ym_form .= "    <option value='{$res_wrk[$i][0]}' selected>{$res_wrk[$i][0]}</option>\n";
            } else {
                $ym_form .= "    <option value='{$res_wrk[$i][0]}'>{$res_wrk[$i][0]}</option>\n";
            }
        }
    }
    $ym_form .= "    </select>\n";
    $ym_form .= "    </form>\n";
    return $ym_form;
}


///////////// HTML Header を出力してブラウザーのキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
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
    font-size:      1.00em;
    font-weight:    bold;
}
select {
    background-color:   teal;
    color:              white;
    font-size:          1.00em;
    font-weight:        bold;
}
-->
</style>
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>

        <!----------------- ここは 年月の指定フォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td width='25%' nowrap style='text-align:left;' class='pt10b'>
                    <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='backward' value='前月'<?php echo $backward?>>
                                <input type='hidden' name='yyyymm' value='<?php echo $pre_yyyymm ?>'>
                            </td>
                        </table>
                    </form>
                </td>
                <td width='25%' nowrap style='text-align:right;' class='pt12b'>
                    <?php echo $dailyValue ?>
                    <?php echo $totalValue ?>
                    表示する年月
                </td>
                <td width='25%' nowrap style='text-align:left;' ><?php echo $ym_form ?></td>
                <td width='25%' nowrap style='text-align:right;' class='pt10b'>
                    <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='forward' value='次月'<?php echo $forward?>>
                                <input type='hidden' name='yyyymm' value='<?php echo $next_yyyymm ?>'>
                            </td>
                        </table>
                    </form>
                </td>
            </tr>
        </table>
        
        <!--------------- ここからグラフ 全体 を表示する -------------------->
<table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name1 . "?" . $uniq ?>' alt='売上実績 日次 全体 グラフ' border='0'>
        </td>
    </tr>
</table>

<br>

<!--
<table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
    <form method='post' action='<?php echo $menu->out_RetUrl()?>'>
        <td align='center'><input class='pt12b' type='submit' name='return' value='戻る'></td>
    </form>
</table>
-->
        <!--------------- ここからグラフ カラプ を表示する -------------------->
<table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name2 . "?" . $uniq ?>' alt='売上実績 日次 カプラ グラフ' border='0'>
        </td>
    </tr>
</table>

<br>

<!--
<table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
    <form method='post' action='<?php echo $menu->out_RetUrl()?>'>
        <td align='center'><input class='pt12b' type='submit' name='return' value='戻る'></td>
    </form>
</table>
-->
        <!--------------- ここからグラフ リニア を表示する -------------------->
<table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name3 . "?" . $uniq ?>' alt='売上実績 日次 リニア グラフ' border='0'>
        </td>
    </tr>
</table>

    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
