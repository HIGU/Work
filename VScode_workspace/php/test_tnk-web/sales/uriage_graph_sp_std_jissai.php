<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ標準品・特注品別 売上と実際原価比較グラフ                          //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/12/01 Created   uriage_graph_sp_std_jissai.php                      //
// 2002/05/01 グラフ下の年月表示を漢字からプログラムロジックに変更          //
// 2002/07/19 jpgraph 1.5→1.7へVersionUPに伴いクラスの仕様が変更           //
//            最初の線をプロットしてしまう仕様の為対策できず                //
// 2002/08/06 上記の対策とデータ数が増えたためstartを2001/04 → 2001/10     //
// 2002/08/08 セッション管理に切替えたため戻るボタンを追加                  //
// 2002/09/20 サイトメニュー方式対応       ↑register global off 対応       //
// 2002/12/20 グラフの日本語対応版 jpGraph.php 1.9.1 マルチバイト対応へ改造 //
//            日本語版 凡例を追加                                           //
//            $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14);               //
//            FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する              //
//            $graph->title->Set(mb_convert_encoding("全体 製品・部品の売上 //
//                               単位：百万円","UTF-8"));                   //
//            $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14);              //
//            FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上                     //
//            $p1->SetLegend(mb_convert_encoding("特注品 売上金額","UTF-8"))//
//            凡例の名称設定                                                //
// 2003/05/01 jpGraph 1.12.1 UP による微調整 SetMargin() legend->Pos()      //
//            mark->SetWidth()                                              //
// 2003/09/05 グラフファイルの更新日のチェックを追加 高速化を図ったが？     //
//            error_reporting = E_ALL 対応のため 配列変数の初期化追加       //
// 2003/10/03 月次データが出来るまでの実際金額０対応                        //
// 2003/10/31 カプラ特注の当月分の売上取得 SQL文を(assembly_schedule)に変更 //
//            明細表のフォーマットを横伸びから縦伸びへ変更（デザイン含む）  //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/11/05 Start年月を変更出来るように$str_ymに集約 今回は200104->200304 //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/02/13 php-5.2.1でMemory limit is now enabled by default.になったので//
//            memory_limit = '64M' をini_set()に追加                        //
// 2007/10/01 ページ制御を追加(１年毎のグラフ表示)  E_ALL | E_STRICTへ      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('memory_limit', '64M');             // ガントチャート用に使用メモリーを増やす
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
$menu->set_site( 1,  7);                    // site_index=1(売上メニュー) site_id=7(カラプ特注標準の売上と原価)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SALES_MENU);              // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('カプラ 特注品・標準品 売上と実際原価 グラフ');
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
$graph_name = "graph/uriage_graph_sp_std_jissai.png";
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
$create_flg = true; // Debug用
/////////// スタート年月の初期化
// $str_ym = 200304;

//////////// 特注・標準 売上推移 グラフ生成
        // 当月より以前の各月の金額はワークファイルを参照する
$query_wrk = "SELECT 年月,c特注,c標準 FROM wrk_uriage WHERE 年月>={$str_ym} AND 年月<={$end_ym} ORDER BY 年月 asc";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk,$res_wrk)) {
    $sp_kin    = array();
    $std_kin   = array();
    $f_sp_kin  = array();
    $f_std_kin = array();
    $datax     = array();
    for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt は配列用のカウンター下でも使う
        if (substr(date_offset(1),0,6) == $res_wrk[$cnt][0]) {  // 月初にワークファイル更新時の対策
            break;
        }
        $datax[$cnt]   = $res_wrk[$cnt][0];
        $sp_kin[$cnt]  = $res_wrk[$cnt][1];
        $std_kin[$cnt] = $res_wrk[$cnt][2];
    }
    for ($i=0; $i<$cnt; $i++) {
        $f_sp_kin[$i]  = Uround($sp_kin[$i] / 1000, 0);     // 単位を千円にする表用
        $f_std_kin[$i] = Uround($std_kin[$i] / 1000, 0);
        $sp_kin[$i]    = Uround($sp_kin[$i] / 1000000, 1);  // 単位を百万円にする
        $std_kin[$i]   = Uround($std_kin[$i] / 1000000, 1);
    }
}

// 特注品 実際原価（投入実際）$nengetsu ～のデータのみ
$nengetsu = $str_ym;
$sp_jis_kin   = array(0);       // 初期化
$f_sp_jis_kin = array();
//for($i=0;$i<=5;$i++)
//  $sp_jis_kin[$i] = "-";      // データなしセット 2002/08/06コメントへ
$i = 0;
$query_sgk = "SELECT 完了年月,投入実際 FROM sgksikp s, mipmst m WHERE 完了年月>={$str_ym} AND 完了年月<={$end_ym} AND s.製品番号=m.seihin AND s.事業部='C' AND m.kubun='3' ORDER BY s.完了年月 asc";
$res_sgk = array();
if ($rows_sgk = getResult($query_sgk,$res_sgk)) {
    for ($r=0; $r<$rows_sgk; $r++){                // 各月の合計金額を算出
        if ($res_sgk[$r][0] == $nengetsu) {
            $sp_jis_kin[$i] += $res_sgk[$r][1];
        } else {                           // 月が変わる。
            $nengetsu = $res_sgk[$r][0];
            $i++;
            $sp_jis_kin[$i] = $res_sgk[$r][1]; 
        }
    }
}

// 標準品 実際原価（投入実際）$nengetsu ～のデータのみ
$nengetsu = $str_ym;
$std_jis_kin = array(0);            // 初期化
$f_std_jis_kin = array();
//for($i=0;$i<=5;$i++)
//  $std_jis_kin[$i] = "-";         // データなしセット 2002/08/06 コメントへ
$i = 0;
$query_sgk = "SELECT 完了年月,投入実際 FROM sgksikp s, mipmst m WHERE 完了年月>={$str_ym} AND 完了年月<={$end_ym} AND s.製品番号=m.seihin AND s.事業部='C' AND m.kubun='1' ORDER BY s.完了年月 asc";
$res_sgk = array();
if ($rows_sgk = getResult($query_sgk,$res_sgk)) {
    for ($r=0; $r<$rows_sgk; $r++) {                // 各月の合計金額を算出
        if ($res_sgk[$r][0] == $nengetsu) {
            $std_jis_kin[$i] += $res_sgk[$r][1];
        } else {                           // 月が変わる。
            $nengetsu = $res_sgk[$r][0];
            $i++;
            $std_jis_kin[$i] = $res_sgk[$r][1]; 
        }
    }
}
//  i=6 を i=0 へ変更 2002/08/06
for ($i=0; $i<$cnt; $i++) {
    if (isset($sp_jis_kin[$i])) {   // 2003/10/03 月次データが出来るまでの実際金額０対応
        $f_sp_jis_kin[$i]  = Uround($sp_jis_kin[$i]/1000,0);     // 単位を千円にする表用
    } else {
        $f_sp_jis_kin[$i]  = 0;
    }
    if (isset($std_jis_kin[$i])) {
        $f_std_jis_kin[$i] = Uround($std_jis_kin[$i]/1000,0);
    } else {
        $f_std_jis_kin[$i] = 0;
    }
    if (isset($sp_jis_kin[$i])) {
        $sp_jis_kin[$i]    = Uround($sp_jis_kin[$i] / 1000000,1);  // 単位を百万円にする
    } else {
        $sp_jis_kin[$i] = 0;
    }
    if (isset($std_jis_kin[$i])) {
        $std_jis_kin[$i]   = Uround($std_jis_kin[$i] / 1000000,1);
    } else {
        $std_jis_kin[$i] = 0;
    }
}
$sp_jis_kin[$cnt]  = "-";
$std_jis_kin[$cnt] = "-";
$f_sp_jis_kin[$i]  = 0;     // 初期化
$f_std_jis_kin[$i] = 0;     // 初期化


if ($pageNo == 1) {
    $temp_date = date_offset(1);
    $temp_date = substr($temp_date,0,6);
    $s_date = $temp_date . "01";
    $e_date = $temp_date . "31";
    
    // 特注品 当月分計算
    $query = "SELECT sum(Uround(数量*単価,0)) as 合計金額 FROM hiuuri left outer join assembly_schedule on 計画番号=plan_no WHERE 計上日>=$s_date AND 計上日<=$e_date AND 事業部='C' AND note15 like 'SC%'";
    // $query = "SELECT 計上日,数量*単価 as 金額 FROM hiuuri h, mipmst m WHERE h.assyno=m.seihin AND h.計上日>=$s_date AND h.計上日<=$e_date AND h.事業部='C' AND m.kubun='3' ORDER BY h.計上日 asc";
    if(getUniResult($query,$res) > 0){
        $datax[$cnt] = substr($s_date, 0, 6);  // X軸の項目を代入
        $sp_kin[$cnt] = $res;                   // 当月の合計金額を算出
        $f_sp_kin[$cnt] = Uround($sp_kin[$cnt] / 1000,0);    // 単位を千万円にする
        $sp_kin[$cnt] = Uround($sp_kin[$cnt] / 1000000,1);   // 単位を百万円にする
    }
        
    // 標準品 当月分計算
    $query = "SELECT sum(Uround(数量*単価,0)) as 合計金額 FROM hiuuri left outer join assembly_schedule on 計画番号=plan_no WHERE 計上日>=$s_date AND 計上日<=$e_date AND 事業部='C' AND sei_kubun='1'";
    // $query = "SELECT 計上日,数量*単価 as 金額 FROM hiuuri h, mipmst m WHERE h.assyno=m.seihin AND h.計上日>=$s_date AND h.計上日<=$e_date AND h.事業部='C' AND m.kubun='1' ORDER BY h.計上日 asc";
    if(getUniResult($query,$res) > 0){
        $datax[$cnt] = substr($s_date, 0, 6);  // X軸の項目を代入
        $std_kin[$cnt] = $res;                  // 当月の合計金額を算出
        $f_std_kin[$cnt] = Uround($std_kin[$cnt] / 1000,0);  // 単位を千万円にする
        $std_kin[$cnt] = Uround($std_kin[$cnt] / 1000000,1); // 単位を百万円にする
    }
} else {
    $cnt--;
}

//////////// グラフの作成チェック
if ($create_flg) {
    // A nice graph with anti-aliasing 
    $graph = new Graph(840,380,"auto");         // グラフの大きさ X/Y
    $graph->img->SetMargin(40,210,30,60);       // グラフ位置のマージン 左右上下
    $graph->SetScale("textlin"); 
    $graph->SetShadow(); 
    $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
    $graph->title->Set(mb_convert_encoding("カプラ 特注品・標準品 売上と実際原価 推移   単位：百万円","UTF-8")); 
    $graph->legend->Pos(0.015,0.5,"right","center"); // 凡例の位置指定 X Y(0.5は縦位置中央)
    $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC は 14 以上 FF_MINCHO は 17 以上
    
    $graph->yscale->SetGrace(10);               // Set 10% grace. 余裕スケール
    $graph->yaxis->SetColor("black");
    $graph->yaxis->SetWeight(2);
    
    // Setup X-scale 
    $graph->xaxis->SetTickLabels($datax); 
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // フォントはボールドも指定できる。
    $graph->xaxis->SetLabelAngle(45); 
    
    // Create the first line 
    $p1 = new LinePlot($sp_kin); 
    $p1->mark->SetType(MARK_FILLEDCIRCLE); 
    $p1->mark->SetFillColor("blue"); 
    $p1->mark->SetWidth(2); 
    $p1->SetColor("blue"); 
    $p1->SetCenter(); 
    //  $p1->SetLegend("Custom Order"); 
    $p1->SetLegend(mb_convert_encoding("特注品 売上金額","UTF-8"));    // 凡例の名称設定
    $graph->Add($p1); 
    
    // ... and the second 
    //  $graph->SetY2Scale("lin");      // Y2スケールを追加
    //  $graph->y2axis->SetColor("red");    // Y2スケールの色
    //  $graph->y2axis->SetWeight(2);   // Y2スケールの太さ(２ドット)
    //  $graph->y2scale->SetGrace(10);  // Set 10% grace. 余裕スケール
    $p2 = new LinePlot($std_kin);   // 二つ目のラインプロットクラスの宣言
    //  $p2->mark->SetType(MARK_STAR);  // プロットマークの形
    $p2->mark->SetType(MARK_DIAMOND);   // プロットマークの形
    $p2->mark->SetFillColor("red");     // プロットマークの色
    $p2->mark->SetWidth(6);         // プロットマークの大きさ
    $p2->SetColor("red");           // プロット線の色
    $p2->SetCenter();           // プロットを中央へ
    $p2->SetWeight(1);          // プロット線の太さ(１ドット)
    //  $p2->SetLegend("Standard");     // 凡例は削除
    $p2->SetLegend(mb_convert_encoding("標準品 売上金額","UTF-8"));    // 凡例の名称設定
    $graph->Add($p2);       // 普通のグラフからY2スケールへ変更のためコメント
    //  $graph->AddY2($p2);             // Y2スケール用のプロット２を追加
    
    
    // Create the first line 
    $p3 = new LinePlot($sp_jis_kin); 
    $p3->mark->SetType(MARK_UTRIANGLE); 
    $p3->mark->SetFillColor("gray"); 
    $p3->mark->SetWidth(3); 
    $p3->SetColor("blue"); 
    $p3->SetCenter(); 
    //  $p3->SetLegend("Cost of sales"); 
    $p3->SetLegend(mb_convert_encoding("特注品 実際原価","UTF-8"));    // 凡例の名称設定
    $graph->Add($p3); 
    
    
    // ... and the Four
    $p4 = new LinePlot($std_jis_kin);   // ４つ目のラインプロットクラスの宣言
    //  $p4->mark->SetType(MARK_STAR);  // プロットマークの形
    $p4->mark->SetType(MARK_DTRIANGLE);     // プロットマークの形
    $p4->mark->SetFillColor("gold");    // プロットマークの色
    $p4->mark->SetWidth(6);         // プロットマークの大きさ
    $p4->SetColor("red");           // プロット線の色
    $p4->SetCenter();           // プロットを中央へ
    $p4->SetWeight(1);          // プロット線の太さ(１ドット)
    //  $p4->SetLegend("Cost of sales");        // 凡例は削除
    $p4->SetLegend(mb_convert_encoding("標準品 実際原価","UTF-8"));    // 凡例の名称設定
    $graph->Add($p4);           // 普通のグラフからY2スケールへ変更のためコメント
    
    
    // Output line 
    $graph->Stroke($graph_name); 
    // echo $graph->GetHTMLImageMap("myimagemap"); 
    // echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 
}
///////// グラフファイルが既に本日更新済みならば直接以下を実行

for($i=0;$i<=$cnt;$i++){
    $ff_std_kin[$i] = number_format($f_std_kin[$i]);            // ３桁ごとのカンマを付加
    $ff_std_jis_kin[$i] = number_format($f_std_jis_kin[$i]);    // ３桁ごとのカンマを付加
    $ff_std_sa_kin[$i] = number_format($f_std_kin[$i]-$f_std_jis_kin[$i]);     // ３桁ごとのカンマを付加
    $ff_sp_kin[$i] = number_format($f_sp_kin[$i]);              // ３桁ごとのカンマを付加
    $ff_sp_jis_kin[$i] = number_format($f_sp_jis_kin[$i]);      // ３桁ごとのカンマを付加
    $ff_sp_sa_kin[$i] = number_format($f_sp_kin[$i]-$f_sp_jis_kin[$i]);        // ３桁ごとのカンマを付加
}

///////////// HTML Header を出力してブラウザーのキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
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
<table align='center' width='100%' border='0'>
    <tr>
        <td align='center' nowrap>
            <img src='<?php echo $graph_name . "?" . uniqid(rand(),1) ?>' alt='カプラ特注・標準 売上原価推移 グラフ' border='0'>
        </td>
    </tr>
</table>

<table align='center' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
    <caption style='text-align:right;'><font size='2'>単位：千円</font></caption>
    <tr><td> <!-- ダミー(デザイン用) -->
  <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1' bordercolordark='white' bordercolorlight='#bdaa90'>
    <th nowrap width='100'>年月</th>
    <th nowrap width='80'><font color='red'>標準品</font></th>
    <th nowrap width='80'><font color='red'>実際原価</font></th>
    <th nowrap width='80'><font color='red'>差 額</font></th>
    <th nowrap width='80'><font color='blue'>特注品</font></th>
    <th nowrap width='80'><font color='blue'>実際原価</font></th>
    <th nowrap width='80'><font color='blue'>差 額</font></th>
    <?php
    for ($i=$cnt; $i>=0; $i--) {
        echo "<tr>\n";
        echo "    <td nowrap width='100' align='center'>" . substr($datax[$i],0,4) . "/" . substr($datax[$i],4,2) . "</td>\n";
        echo "<td align='right'><font color='red'>$ff_std_kin[$i]</font></td>\n";
        echo "<td align='right'><font color='red'>$ff_std_jis_kin[$i]</font></td>\n";
        echo "<td align='right'><font color='red'>$ff_std_sa_kin[$i]</font></td>\n";
        echo "<td align='right'><font color='blue'>$ff_sp_kin[$i]</font></td>\n";
        echo "<td align='right'><font color='blue'>$ff_sp_jis_kin[$i]</font></td>\n";
        echo "<td align='right'><font color='blue'>$ff_sp_sa_kin[$i]</font></td>\n";
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
