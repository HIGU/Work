<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 月計 グラフの全体・カプラ・リニア (折れ線グラフ)                    //
// Copyright (C) 2001-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/10/01 Created  uriage_graph_all_tuki.php                            //
// 2002/08/08 セッション管理に切替えたため戻るボタンを追加                  //
// 2002/12/20 グラフを日本語対応に変更(図形描画)                            //
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
//            凡例の位置指定(左右マージン,上下マージン,"right","center")    //
//            $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14);              //
//            FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上                     //
// 2003/05/01 jpGraph 1.12.1 UP による微調整 SetMargin() legend->Pos()      //
//            mark->SetWidth()                                              //
// 2003/09/05 グラフファイルの更新日のチェックを追加 処理の高速化を図った。 //
//            error_reporting = E_ALL 対応のため 配列変数の初期化追加。     //
// 2003/12/10 タイトルの月次グラフを月計グラフへ変更                        //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/04/27 3個の戻るボタンの内1個が SALES_MENU の変更漏れがあったのを追加//
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2006/09/01 開始年月を $str_ym で統一                                     //
// 2007/10/02 ページ制御を追加(１年毎のグラフ表示)  E_ALL | E_STRICTへ      //
// 2009/04/14 期が変わった時(4月)にデータが無くエラーが出てしまう為         //
//            4月に照会すると前期分を表示するように変更                大谷 //
// 2010/05/06 データがない時のエラー対応（途中でデータが取込まれたので      //
//            未確認）                                                 大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('memory_limit', '64M');
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // 内部でdefine.php pgsql.php を require()している。
require_once ('../tnk_func.php');
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
require_once ('../../jpgraph.php');
require_once ('../../jpgraph_line.php');
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1,  4);                    // site_index=1(売上メニュー) site_id=4(月計グラフ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SALES_MENU);              // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売上月計グラフ');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('日計グラフ',   SALES . 'uriage_graph_all_niti.php');

///// ページリクエストの処理
if (isset($_REQUEST['pageNo'])) {
    $pageNo = $_REQUEST['pageNo'];
} else {
    $pageNo = 1;
}
if ($pageNo < 1) $pageNo = 1;
///// スタート年月の指定
if (date('m') < 4) {
    $str_ym = date('Y') - $pageNo . '04';
} else {
    $str_ym = date('Y') - $pageNo + 1 . '04';
}
if (date('m') == 4) {
    $str_ym = date('Y') - $pageNo . '04';
} else {
    $str_ym = date('Y') - $pageNo + 1 . '04';
}
///// エンド年月の指定
$end_ym = substr($str_ym, 0, 4) + 1 . '03';
if ($end_ym > date('Ym')) {
    $end_ym = date('Ym');
}
///// リミッター設定
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
///// スタート年月の指定
// $str_ym = '200704';
//////////// グラフファイルの存在チェック
$graph_name1 = "graph/uriage_graph_all_tuki.png";       // 全体の月次売上 折線グラフ
$graph_name2 = "graph/uriage_graph_all_tuki_c.png";     // カプラの月次売上 折線グラフ
$graph_name3 = "graph/uriage_graph_all_tuki_l.png";     // リニアの月次売上 折線グラフ
if (file_exists($graph_name1)) {
    //////////// 現在の年月日とグラフファイルの更新日データを取得
    $current_date = date("Ymd");
    $file_date    = date("Ymd", filemtime($graph_name1) );
    //////////// グラフファイルの更新日チェック
    if ($current_date == $file_date) {
        $create_flg1 = false;           // グラフ作成不要
    } else {
        $create_flg1 = true;            // グラフ作成
    }
} else {
    $create_flg1 = true;                // グラフ作成
}
if (file_exists($graph_name2)) {
    //////////// 現在の年月日とグラフファイルの更新日データを取得
    $current_date = date("Ymd");
    $file_date    = date("Ymd", filemtime($graph_name2) );
    //////////// グラフファイルの更新日チェック
    if ($current_date == $file_date) {
        $create_flg2 = false;           // グラフ作成不要
    } else {
        $create_flg2 = true;            // グラフ作成
    }
} else {
    $create_flg2 = true;                // グラフ作成
}
if (file_exists($graph_name3)) {
    //////////// 現在の年月日とグラフファイルの更新日データを取得
    $current_date = date("Ymd");
    $file_date    = date("Ymd", filemtime($graph_name3) );
    //////////// グラフファイルの更新日チェック
    if ($current_date == $file_date) {
        $create_flg3 = false;           // グラフ作成不要
    } else {
        $create_flg3 = true;            // グラフ作成
    }
} else {
    $create_flg3 = true;                // グラフ作成
}
///// テスト用
$create_flg1 = true;
$create_flg2 = true;
$create_flg3 = true;


if ($create_flg1) {
    $query_wrk = "SELECT 年月,全体 FROM wrk_uriage WHERE 年月>={$str_ym} AND 年月<={$end_ym} ORDER BY 年月 ASC";
    $query = "SELECT sum(Uround(数量*単価, 0)) AS 金額 FROM hiuuri WHERE 計上日>=%s AND 計上日<=%s";
    graphDataCreate($query_wrk, $query, '全体', $graph_name1);
}
if ($create_flg2) {
    $query_wrk = "SELECT 年月,カプラ FROM wrk_uriage WHERE 年月>={$str_ym} AND 年月<={$end_ym} ORDER BY 年月 ASC";
    $query = "SELECT sum(Uround(数量*単価, 0)) AS 金額 FROM hiuuri WHERE 計上日>=%s AND 計上日<=%s AND 事業部='C'";
    graphDataCreate($query_wrk, $query, 'カプラ', $graph_name2);
}
if ($create_flg3) {
    $query_wrk = "SELECT 年月,リニア FROM wrk_uriage WHERE 年月>={$str_ym} AND 年月<={$end_ym} ORDER BY 年月 ASC";
    $query = "SELECT sum(Uround(数量*単価, 0)) AS 金額 FROM hiuuri WHERE 計上日>=%s AND 計上日<=%s AND 事業部='L'";
    graphDataCreate($query_wrk, $query, 'リニア', $graph_name3);
}

function graphDataCreate($query_wrk, $query, $title, $graph_name)
{
    global $pageNo;
    ////////////////////////// 月次売上 全体
                            // 当月より以前の各月の金額はワークファイルを参照する
    $cnt = 0;
    $res_wrk = array();
    if ($rows_wrk = getResult($query_wrk, $res_wrk)) {
        $tuki_kin = array();
        $datax    = array();
        for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt は配列用のカウンター下でも使う
            if (substr(date_offset(1), 0, 6) == $res_wrk[$cnt][0]) {  // 月初にワークファイル更新時の対策
                break;
            }
            $datax[$cnt]    = $res_wrk[$cnt][0];
            $tuki_kin[$cnt] = $res_wrk[$cnt][1];
        }
    }
    if ($pageNo == 1) {     // ページ番号が１の時だけ当月のデータを読込む
        $temp_date = date_offset(1);
        $temp_date = substr($temp_date, 0, 6);
        $s_date = $temp_date . '01';
        $e_date = $temp_date . '31';
        $query = sprintf($query, $s_date, $e_date);
        getUniResult($query, $tuki_kin[$cnt]);
        $datax[$cnt] = $temp_date;              // X軸の項目を代入
        $cnt++;
    }
    $rui_kin = array();
    for ($i=0; $i<$cnt; $i++) {
        if ($i == 0) {
            $rui_kin[$i] = $tuki_kin[$i];
        } else {
            $rui_kin[$i] = $tuki_kin[$i] + $rui_kin[$i-1];
        }
    }
    for ($i=0; $i<$cnt; $i++) {
        $tuki_kin[$i] = Uround($tuki_kin[$i] / 1000000,1);   // 単位を百万円にする
        $rui_kin[$i]  = Uround($rui_kin[$i]  / 1000000,1);
    }
    ///// グラフ作成
    graphCreate($datax, $rui_kin, $tuki_kin, $title, $graph_name);
    return;
}


function userFormat($aLabel)
{
    return number_format($aLabel, 1);
}

function graphCreate($datax, $rui_kin, $tuki_kin, $title, $graph_name)
{
    // Some data 
    //$datax = array("2001-04","2001-05","2001-06","2001-07","2001-08","2001-09"); 
    //$datay  = array(5,9,15,21,25,32); 
    //$data2y = array(5,4, 6,6,4,7); 
    
    // A nice graph with anti-aliasing 
    $graph = new Graph(740, 350, 'auto');       // グラフの大きさ X/Y
    $graph->img->SetMargin(40, 130, 30, 60);    // グラフ位置のマージン 左右上下
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
    $graph->title->Set(mb_convert_encoding("売上実績 月計 {$title} グラフ   単位：百万円", 'UTF-8')); 
    // $graph->title->SetFont(FF_FONT1,FS_BOLD); 
    
    // Setup X-scale
    $graph->xaxis->SetTickLabels($datax);
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // フォントはボールドも指定できる。
    $graph->xaxis->SetLabelAngle(45);
    
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
    $p1->value->Show();
    $graph->Add($p1); 
    
    // ... and the second 
    $graph->SetY2Scale('lin');      // Y2スケールを追加
    $graph->y2axis->SetColor('red');// Y2スケールの色
    $graph->y2axis->SetWeight(2);   // Y2スケールの太さ(２ドット)
    $graph->y2scale->SetGrace(10);  // Set 10% grace. 余裕スケール
    $p2 = new LinePlot($tuki_kin);  // 二つ目のラインプロットクラスの宣言
    $p2->mark->SetType(MARK_STAR);  // プロットマークの形
    $p2->mark->SetFillColor("red"); // プロットマークの色
    $p2->mark->SetWidth(4);         // プロットマークの大きさ
    $p2->SetColor('red');           // プロット線の色
    $p2->SetCenter();               // プロットを中央へ
    $p2->SetWeight(1);              // プロット線の太さ(１ドット)
    $p2->SetLegend(mb_convert_encoding('月計', 'UTF-8')); 
    // $p2->value->SetFormat('%01.1f'); // 整数部が無い時は0、小数部１桁を指定
    $p2->value->SetFormatCallback('userFormat');    // 上記では３桁のカンマに対応できないため
    $p2->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $p2->value->SetColor('red');                    // 値のの色
    $p2->value->Show();
    //  $graph->Add($p2);           // 普通のグラフからY2スケールへ変更のためコメント
    $graph->AddY2($p2);             // Y2スケール用のプロット２を追加
    
    // Output line 
    $graph->Stroke($graph_name); 
    // echo $graph->GetHTMLImageMap("myimagemap"); 
    // echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 
    return;
}

///////////// HTML Header を出力してブラウザーのキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
    font-size:      1.00em;
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
<table width='100%' border='0'>
    <tr>
        <td align='center'>
           <img src='<?php echo  $graph_name1 . "?" . uniqid(rand(),1) ?>' alt='売上実績 月次 全体 グラフ' border='0'>
        </td>
    </tr>
</table>


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
                    <!--
                    <table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
                        <form method='post' action='<?php echo $menu->out_RetUrl() ?>'>
                            <td align='center'><input class='pt12b' type='submit' name='return' value='戻る'></td>
                        </form>
                    </table>
                    -->
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
<table width='100%' border='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name2 . "?" . uniqid(rand(),1) ?>' alt='売上実績 月次 カプラ グラフ' border='0'>
        </td>
    </tr>
</table>


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
                    <!--
                    <table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
                        <form method='post' action='<?php echo $menu->out_RetUrl() ?>'>
                            <td align='center'><input class='pt12b' type='submit' name='return' value='戻る'></td>
                        </form>
                    </table>
                    -->
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
<table width='100%' border='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name3 . "?" . uniqid(rand(),1) ?>' alt='売上実績 月次 リニア グラフ' border='0'>
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
