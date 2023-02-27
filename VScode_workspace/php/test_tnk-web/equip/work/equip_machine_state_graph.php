<?php
//////////////////////////////////////////////////////////////////////////////
// 機械運転(製造用) 時系列による加工数・状態グラフ                          //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2002/03/04 Created   equip_machine_state_graph.php                       //
// 2004/08/07 設備２へ移行   jpGraph-1.9.1→1.16へUP                        //
// 2004/08/09 $graph->xaxis->SetTextLabelInterval(2);   function SetTextLabelInterval($aStep)
//            $graph->SetTextTickInterval(1,2);         function SetTextTickInterval($aStep,$aStart)
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
require_once ('equip_function.php');
require_once ('../tnk_func.php');
access_log();                               // Script Name は自動取得
// require_once ('../../jpGraph-1.9.1-bak/src/jpgraph.php'); 
// require_once ('../../jpGraph-1.9.1-bak/src/jpgraph_line.php'); 
require_once ('../../jpgraph-4.4.1/src/jpgraph.php'); 
require_once ('../../jpgraph-4.4.1/src/jpgraph_line.php'); 

$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
// $url_referer     = $_SERVER['HTTP_REFERER'];    // 呼出もとのURLを保存 前のスクリプトで分岐処理をしている場合は使用しない
$url_referer     = $_SESSION['equip_referer'];     // 分岐処理前に保存されている呼出元をセットする
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
    $_SESSION['s_sysmsg'] = '認証されていないか認証期限が切れました。Loginしなおして下さい。';
    header('Location: http:' . WEB_HOST . 'index1.php');
    exit();
}
$disp_rows = 2;         // １指示Noあたりの表示行数
if (!isset($_GET['mac_no'])) {
    if (isset($_SESSION['mac_no'])) {
        $mac_no = $_SESSION['mac_no'];
    } else {
        $mac_no = '';
        $_SESSION['s_sysmsg'] = '機械Noが指定されていません!';
        header('Location: http:' . WEB_HOST . $url_referer);
        exit();
    }
} else {
    $_SESSION['mac_no'] = $_GET['mac_no'];
    $mac_no = $_GET['mac_no'];
}

if ($mac_no == '') {
    $_SESSION['s_sysmsg'] = '機械Noがマスター未登録です!';
    header('Location: http:' . WEB_HOST . $url_referer);
    exit();
}

if (equip_header_field($mac_no, 1) == NULL) {
    $_SESSION['s_sysmsg'] = '機械No：$mac_no が運転登録されていません!';
    header('Location: http:' . WEB_HOST . $url_referer);
    exit();
}

/********** Logic Start **********/
//////////// タイトルの日付・時間設定
$today = date('Y/m/d H:i:s');

//////////// CSV File から 各フィールドを取得
$siji_no  = equip_header_field($mac_no,1);
$parts_no = equip_header_field($mac_no,2);
$koutei   = equip_header_field($mac_no,3);
$keikaku  = equip_header_field($mac_no,4);

//////////// アイテムマスターから部品名取得
$query = "select midsc,mzist from miitem where mipn='$parts_no' limit 1";
$res=array();
if ( ($rows=getResult($query,$res)) >= 1) {      // 部品名取得
    $parts_name = mb_substr($res[0][0],0,10);
    $parts_zai  = mb_substr($res[0][1],0,7);
} else {
    $parts_name = '';
    $parts_zai  = '';
}

/*************  equip_machine_state()を変更のため以下は不要
/////////// 機械マスターから状態テーブル方式の取得
$query = "select csv_flg from equip_machine_master where mac_no='$mac_no'";
if (getUniResult($query, $state_type) <= 0) {
    $_SESSION['s_sysmsg'] .= '機械マスターから状態タイプの取得に失敗';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();             ///// $state_type は以下で Netmoni or ロータリースイッチ方式等の切替で使用
}
*************/

//////////// 現在加工中のグラフ作成
$query = 'select date_time,work_cnt,mac_state from equip_work_log ';
$query .= "where mac_no='$mac_no' and siji_no='$siji_no' and koutei='$koutei' order by date_time ASC ";
$res = array();
if ($rows = getResult($query,$res) ) {
    $log_cnt   = $rows;                 // ログ件数を保存 $rows は下で多用するため
    $samp_data = sampling($rows);       // グラフの時系列用サンプリングタイム設定
    $cnt = 0;                           // 配列用のカウンター
    $t_cnt = 0;                         // 1分毎の累積時間作成の配列用のカウンター
    $rui_time = array();                // 累積 加工時間
    $worked_time = array();             // １個あたりの加工時間
    $work_cnt    = array();             // 加工数 各状態ごとの $work_cnt[状態][$t_cnt]
    $rui_state   = array();             // 各状態ごとの累積時間
    $max_qry = "select max(work_cnt) from equip_work_log where mac_no='$mac_no' and siji_no='$siji_no' and koutei='$koutei'";
    $max_res = array();
    if ( ($max_rows = getResult($max_qry,$max_res)) >= 1) {
        $max_data = $max_res[0][0];
    }
    $yaxis_min_data = yaxis_min($max_data);     // work_counter の最大加工数からグラフの最小値を算出
    for ($r=0; $r<$rows; $r++) {                // 各日毎の合計金額を算出
        if ($r == 0) {
            $str_date_time = $res[$r][0];       // 初回のtimestamp
            $worked_time[$cnt] = 0;             // 初回の加工時間
            $rui_time[$t_cnt] = 0;              // 初回の加工時間(累積用)
            for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
                $work_cnt[$i][$r]    = $yaxis_min_data; //$res[$r][1];  // 初回の加工数(普通なら０個のはず)
            }
            $start_flag = 1;
            $cnt++;
            $t_cnt++;
            $next_time = ($str_date_time + $samp_data);     // 次のデータはサンプリングタイム秒後
        } else {
            if ($res[$r][0] < $next_time) {                 // サンプリング秒後に来てなければ飛ばす
                continue;
            } else {
                $next_time = ($res[$r][0] + $samp_data);    // 次のサンプリング秒後にセット
            }
            for ($j=$res[$r-1][0]; $j<$res[$r][0]; $j += $samp_data) {  // 10分毎だったのをlogの件数によって可変にして累積時間作成
                for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
                    if ($res[$r-1][2] == $i) {              // 状態が同じもの
                        if ($res[$r-1][2] == 0) {               // 電源OFFなら2個前を確認
                            if ($r >= 2) {
                                $work_cnt[$i][$t_cnt] = $res[$r-2][1];      // 加工数を入れる
                            } else {
                                if ($r <= 1){                   // 初回が電源Offだったら
                                    $work_cnt[$i][$t_cnt] = 0;  // 加工数 0 を入れる
                                } else {
                                    $work_cnt[$i][$t_cnt] = $res[$r-1][1];      // 加工数を入れる
                                }
                            }
                        } else {
                            $work_cnt[$i][$t_cnt] = $res[$r-1][1];      // 加工数を入れる
                        }
                    } else {
                        $work_cnt[$i][$t_cnt] = $yaxis_min_data;        // 状態が違うものは加工数をクリア
                    }
                }
                $rui_time[$t_cnt] = Uround((($j - $str_date_time)/60),0); // 累積 加工時間計算(分)
                $t_cnt++;
            }
        }
    }
    ///// 現在の最新情報を手動でのせる
    $saisin = mktime();
    for ($j=$res[$r-1][0]; $j<$saisin; $j+=$samp_data){     // 10分毎に累積時間作成 サンプリングに変更
        for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
            if ($res[$r-1][2] == $i) {              // 状態が同じもの
                if ($res[$r-1][2] == 0) {               // 電源OFF
                    if ($r >= 2) {
                        $work_cnt[$i][$t_cnt] = $res[$r-2][1];      // 加工数を入れる
                    } else {
                        $work_cnt[$i][$t_cnt] = $res[$r-1][1];      // 加工数を入れる
                    }
                } else {
                    $work_cnt[$i][$t_cnt] = $res[$r-1][1];      // 加工数を入れる
                }
            } else {
                $work_cnt[$i][$t_cnt] = $yaxis_min_data;        // 状態が違うものは加工数をクリア
            }
        }
        $rui_time[$t_cnt] = Uround((($j - $str_date_time)/60),0); // 累積 加工時間計算(分)
        $t_cnt++;
    }
    ///// 最新情報 END
} else {
    $_SESSION['s_sysmsg'] = "機械No：$mac_no 指示No：$siji_no 工程：$koutei のデータがありません。";
    header('Location: http:' . WEB_HOST . '/equipment/equipment_working_graph_select.php');
    exit();
}
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
    $rui_state[$i] = 0;                 // 配列の初期化
}
for ($r=1; $r<$rows; $r++) {                // 各状態毎の累積時間を算出
    for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
        if ($res[$r-1][2] == $i) {     // 状態が変化した時のレコードの一つ前のレコードを使用
            $rui_state[$i] += ($res[$r][0]-$res[$r-1][0]);
        }
    }
}
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
    if ($res[$r-1][2] == $i) {    // 最新の時間と最後のレコードの差で最新データを追加
        $rui_state[$i] += (mktime()-$res[$r-1][0]);
    }
}
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
    if ($rui_state[$i] <= 0) {
        continue;
    }
    $rui_state[$i] = Uround($rui_state[$i]/60,0);
}

$graph = new Graph(670,350,'auto');           // グラフの大きさ X/Y
$graph->img->SetMargin(40,40,20,70);  // グラフ位置のマージン 左右上下
$graph->SetScale('linlin');         // X / Y LinearX LinearY (通常はtextlin TextX LinearY)
$graph->SetShadow(); 
$graph->yscale->SetGrace(10);     // Set 10% grace. 余裕スケール
$graph->yaxis->SetColor('blue');
$graph->yaxis->SetWeight(2);
$graph->yaxis->scale->ticks->SupressFirst();        // Y軸の最初のメモリラベルを表示しない
$graph->yscale->SetAutoMin($yaxis_min_data);            // Y軸のスタートを変更
$graph->xaxis->SetPos('min');               // X軸のプロットエリアを一番下へ

// Setup X-scale 
$graph->xaxis->SetTickLabels($rui_time); 
$graph->xaxis->SetFont(FF_FONT1); 
$graph->xaxis->SetLabelAngle(90); 

// Create the first line 
$p1 = array();
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {           // 0～3 までを生成 0～15に変更
    if ($rui_state[$i] <= 0) {
        continue;
    }
    equip_machine_state($mac_no, $i, $bg_color, $txt_color);
    $p1[$i] = new LinePlot($work_cnt[$i]); 
    $p1[$i]->SetFillColor($bg_color); 
    $p1[$i]->SetFillFromYMin($yaxis_min_data);  // 2004/08/06 ADD 1.10以上の変更点を追加
    $p1[$i]->SetColor($bg_color); 
    $p1[$i]->SetCenter(); 
    $p1[$i]->SetStepStyle();
    $graph->Add($p1[$i]); 
}

// Output line 
$graph_name = 'graph/equip_machine_state_graph.png';
$graph->Stroke($graph_name); 

/////////////// 明細表(詳細表示)作成のためのデータ取得
$query = 'select mac_no,date_time,mac_name,mac_state,work_cnt,
        macro1,macro2,macro3,macro4,macro5 from equip_work_log ';
$query .= "where mac_no='$mac_no' and siji_no='$siji_no' and koutei='$koutei' order by date_time DESC limit $disp_rows";
$res = array();
if ( ($rows=getResult2($query,$res)) <= 0) {
    $_SESSION['s_sysmsg'] = "機械No：$mac_no 指示No：$siji_no 工程：$koutei の明細がありません。";
    header('Location: http:' . WEB_HOST . $url_referer);
    exit();
} else {
    $num = count($res[0]);          // フィールド数なぜか28になる getResult2()ならOK
    // $num = 14;
}

/********** Logic End   **********/
?>
<!DOCTYPE html>
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>機械運転 加工数・状態グラフ(製造用)</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>
<style type="text/css">
<!--
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.sub_font {
    font: 8.5pt;
    font-family: monospace;
}
.table_font {
    font: 11.5pt;
    font-family: monospace;
}
.pick_font {
    font: 12.0pt;
    font-family: monospace;
}
th {
    font:bold 12.0pt;
    font-family: monospace;
}
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt12b {
    font:bold 12pt;
}
.pt11b {
    font:bold 11pt;
}
.margin0 {
    margin:0%;
}
-->
</style>
</HEAD>
<BODY class='margin0'>
    <center>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <form method='post' action='<?php echo $url_referer ?>'>
                    <td width='60' bgcolor='blue' align='center' valign='center'>
                        <input class='pt12b' type='submit' name='return' value='戻る'>
                    </td>
                </form>
                <td colspan='1' bgcolor='#d6d3ce' align='center' class='title_font'>
                    <?php
                        print("機械 運転 加工数・稼働状況集計グラフ\n");
                    ?>
                </td>
                <td colspan='1' bgcolor='#d6d3ce' align='center' width='140' class='today_font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        <table width=100%>
            <hr color='797979'>
        </table>

        <!-- //////////// 見出しを表示 -->
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr class='pt11b'>
                <td align='center' nowrap>部品No</td>
                <td align='center' nowrap><?php echo $parts_no ?></td>
                <td align='center' nowrap>部品名</td>
                <td align='center' nowrap><?php echo $parts_name ?></td>
                <td align='center' nowrap>材質</td>
                <td align='center' nowrap><?php echo $parts_zai ?></td>
                <td align='center' nowrap>指示No</td>
                <td align='center' nowrap><?php echo $siji_no ?></td>
                <td align='center' nowrap>工程</td>
                <td align='center' nowrap><?php echo $koutei ?></td>
                <td align='center' nowrap>計画数</td>
                <td align='center' nowrap><?php echo $keikaku ?></td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        <hr color='797979'>

        <!-- // 詳細データ表示のための表を作成 -->
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='1' cellpadding='2'>
            <tr>
                <th nowrap>No</th>
                <th nowrap>機械No</th><th nowrap>年月日</th><th nowrap>時分秒</th><th nowrap>型 式</th>
                <th nowrap>状態</th><th nowrap>加工数</th><th nowrap>変数1</th><th nowrap>変数2</th>
                <th nowrap>変数3</th><th nowrap>変数4</th><th nowrap>変数5</th>
            </tr>
<?php
/////// 現在時刻の最新情報を手動でのせる
print("<tr class='pick_font'>\n");
print(" <td align='center' nowrap bgcolor='blue'><font color='yellow'><b>最新</b></font></td>\n");
print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . $res[0][0] . "</td>\n");
print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . date("Y/m/d",mktime()) . "</td>\n");
print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . date("H:i:s",mktime()) . "</td>\n");
print(" <td align='left' nowrap bgcolor='#d6d3ce'>" . $res[0][2] . "</td>\n");
    $mac_state_txt = equip_machine_state($mac_no, $res[0][3], $bg_color, $txt_color);
print(" <td align='center' nowrap bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
print(" <td align='right' nowrap bgcolor='#d6d3ce'>" . $res[0][4] . "</td>\n");
for ($a=5; $a<=9; $a++) {
    if ($res[0][$a] == "") {
        print(" <td align='center' nowrap bgcolor='#d6d3ce'>-</td>\n");
    } else {
        print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . $res[0][$a] . "</td>\n");   //マクロ変数
    }
}
print("</tr>\n");
/////// 最新情報 END
for ($i=0; $i<$rows; $i++) {
    print("<tr class='table_font'>\n");
    print("<td align='center' nowrap bgcolor='#d6d3ce'>" . ($i+1) . "</td>\n");
    for ($j=0; $j<$num; $j++) {
        switch ($j) {
        case 1:
            print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . date("Y/m/d",$res[$i][$j]) . "</td>\n");
            print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . date("H:i:s",$res[$i][$j]) . "</td>\n");
            break;
        case 2:
            print(" <td align='left' nowrap bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
            break;
        case 3:
            $mac_state_txt = equip_machine_state($mac_no, $res[$i][$j], $bg_color, $txt_color);
            print(" <td align='center' nowrap bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
            break;
        case 4:
            print(" <td align='right' nowrap bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
            break;
        default:
            if ($res[$i][$j] == "") {
                print(" <td align='center' nowrap bgcolor='#d6d3ce'>-</td>\n");
            } else {
                print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
            }
        }
    }
    print("</tr>\n");
}
print("</table>\n");
echo "    </td></tr>\n";
echo "</table> <!-- ダミーEnd -->\n";


echo "<table align='center' width='100%' border='0'>\n";
echo "  <tr>\n";
echo "      <td align='center'>\n";
echo "          <font class='title_font'>機械運転 加工数 状態 グラフ　</font><font class='sub_font'>縦軸:加工数/横軸:時間(H)</font><br>\n";
echo "          <img src='" . $graph_name . "?" . uniqid(rand(),1) . "' alt='機械運転 加工数 グラフ' border='0'>\n";
echo "      </td>\n";
echo "  </tr>\n";
echo "</table>\n";

echo "<table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>\n";
echo "    <tr><td> <!-- ダミー(デザイン用) -->\n";
echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
    if ($i == 0) {
        echo "  <th>-----</th>\n";
    }
    if ($rui_state[$i] <= 0) {
        continue;
    }
    $name = equip_machine_state($mac_no, $i,$bg_color,$txt_color);
    echo "  <th bgcolor='$bg_color'><font color='$txt_color'>" . $name . "</font></th>\n";
}
echo "<th>log件数</th>\n";                      // debug 用
echo "<tr>\n";
echo "  <td align='center'>累積時間(分)</td>\n";
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
    if ($rui_state[$i] <= 0) {
        continue;
    }
    $name = equip_machine_state($mac_no, $i, $bg_color, $txt_color);
    echo "      <td align='center'>" . number_format($rui_state[$i]) . "</td>\n";
}
echo "<td align='center'>$log_cnt</td> \n";      // debug 用
?>
        </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        <table align='center' border='2' cellspacing='0' cellpadding='0'>
            <form method='post' action='<?php echo $url_referer ?>'>
                <td><input type='submit' name='return' value='戻る'></td>
            </form>
        </table>
    </center>
</BODY>
</HTML>
<?php
ob_end_flush();  //Warning: Cannot add header の対策のため追加。
?>
