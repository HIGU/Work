<?php
//////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの スケジュール ガントグラフ メンテ  本体フレーム    //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/02 Created  equip_plan_graphList.php                             //
// 2005/08/20 php5 へ移行 =& new → = new へ new by reference is deprecated //
// 2006/01/26 納期を20060101からに変更 ラインマージンを1.5→2.2へ変更2行表示//
//            数量が抜けているのを追加 head.inst_qt → $plan_pcs            //
// 2007/02/05 リテラルでdelivery >= 20060101 → ロジックへ変更40日前～100日 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // 設備メニュー 共通 function (function.phpを含む)
require_once ('../../tnk_func.php');        // TNK 専用 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../../jpgraph-4.4.1/src/jpgraph.php');      // Graph class
require_once ('../../../jpgraph-4.4.1/src/jpgraph_gantt.php');// Gantt Graph class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 8);                     // site_index=40(設備メニュー) site_id=8(スケジューラー)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

$mac_no  = @$_SESSION['mac_no'];
if (isset($_REQUEST['select'])) {
    $select = $_REQUEST['select'];
} else {
    $select = 'NG';
}
if ($mac_no == '') {
    $select = 'NG';
}

while ($select == 'GO') {
    //////////////// スケジュール照会 開始・終了日付 生成 2007/02/05 ADD
    $preDate = 40;                  //  稼働日で40日前から (土日対応へ)
    $timestamp = time();            //  E_STRICTでmktime()→time()の標準に従うようにメッセージが出たため
    for ($i=0; $i<$preDate; $i++) {
        $timestamp -= 86400;
        while (day_off($timestamp)) {
            $timestamp -= 86400;
        }
    }
    $strDate = date('Ymd', $timestamp);
    $maxDate = 100;                 // 稼働日で100日間(土日対応)
    $timestamp = time();            //  E_STRICTでmktime()→time()の標準に従うようにメッセージが出たため
    for ($i=0; $i<$maxDate; $i++) {
        while (day_off($timestamp)) {
            $timestamp += 86400;
        }
        $timestamp += 86400;
    }
    $endDate = date('Ymd', $timestamp);
    //////////////// 機械マスターから機械名を取得
    $query = "SELECT mac_name
                FROM
                    equip_machine_master2
                WHERE
                    mac_no={$mac_no}
                LIMIT 1
    ";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '　';
    }
    /////////// 加工指示テーブルよりスケジュールを取得
    $query = "SELECT inst_no
                    ,i.koutei
                    ,i.parts_no
                    ,substr(midsc, 1, 12) AS parts_name
                    ,to_char(delivery, '9999-99-99') AS delivery
                    ,to_char(str_date, 'YYYY-MM-DD') AS str_date
                    ,to_char(end_date, 'YYYY-MM-DD') AS end_date
                    ,head.inst_qt       AS pcs
                FROM
                    equip_work_instruction AS i
                LEFT outer join
                    miitem
                ON
                    (parts_no=mipn)
                LEFT outer join
                    equip_work_inst_header AS head
                USING
                    (inst_no)
                LEFT outer join
                    equip_work_log2_header AS log
                ON
                    (i.inst_no=log.siji_no)
                WHERE
                    i.mac_no = {$mac_no}
                    and
                    delivery >= {$strDate}
                    and
                    delivery <= {$endDate}
                    and
                    log.end_timestamp IS NULL
                ORDER BY
                    delivery ASC
                LIMIT 15
    ";
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>{$mac_no}：{$mac_name} はスケジュールデータがありません！</font>";
        $select = 'NG';
        break;
    }
    /////////// スケジューラー ガントグラフのインスタンス作成
    $graph = new GanttGraph(0, 0, 'auto');
    $graph->SetShadow();
    
    // Add title and subtitle
    $graph->title->Set(mb_convert_encoding('スケジューラーの照会及びメンテナンス', 'UTF-8'));
    $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 12);
    $graph->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 10);
    $graph->subtitle->Set(mb_convert_encoding("機械番号：{$mac_no} 機械名：{$mac_name}", 'UTF-8'));
    
    // Show day, week and month scale
    $graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
    
    // 1.5 line spacing to make more room
    $graph->SetVMarginFactor(2.2);
    // Setup some nonstandard colors
    $graph->SetMarginColor('lightgreen@0.8');
    $graph->SetBox(true, 'yellow:0.6', 2);
    $graph->SetFrame(true, 'darkgreen', 4);
    $graph->scale->divider->SetColor('yellow:0.6');
    $graph->scale->dividerh->SetColor('yellow:0.6');
    
    // Instead of week number show the date for the first day in the week
    // on the week scale
    $graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY2);
    
    // Make the week scale font smaller than the default
    $graph->scale->week->SetFont(FF_FONT0);
    
    // Use the short name of the month together with a 2 digit year
    // on the month scale
    $graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
    $graph->scale->month->SetFontColor('white');
    $graph->scale->month->SetBackgroundColor('blue');
    
    $targ = array();
    $alts = array();
    for ($r=0; $r<$rows; $r++) {
        $inst_no  = $res[$r]['inst_no'];
        $koutei   = $res[$r]['koutei'];
        $parts_no = $res[$r]['parts_no'];
        $parts_name = $res[$r]['parts_name'];
        $delivery = $res[$r]['delivery'];
        $str_date = $res[$r]['str_date'];
        $end_date = $res[$r]['end_date'];
        $plan_pcs = $res[$r]['pcs'];
        $item = mb_convert_encoding("{$parts_no} {$plan_pcs}\n{$parts_name}", 'UTF-8');
                                // ($row, $title, $startdate, $enddate)
        $activity[$r] = new GanttBar($r, $item, "{$delivery}","{$delivery}");
        $activity[$r]->title->SetFont(FF_GOTHIC, FS_NORMAL, 10);
        // Yellow diagonal line pattern on a red background
        $activity[$r]->SetPattern(BAND_RDIAG, 'yellow');
        $activity[$r]->SetFillColor('blue');
            // CSIM
        $targ[$r] = "JavaScript:win_open('equip_plan_edit.php?inst_no={$inst_no}&koutei={$koutei}&select=OK')";
        $alts[$r] = "指示番号：{$inst_no}";
        $activity[$r]->SetCSIMTarget($targ[$r], $alts[$r]);
        
        $graph->Add($activity[$r]);
    }
    // $graph_name = ('graph/equip' . session_id() . '.png');
    $graph_name = 'graph/equip_plan_graph.png';
    $graph->Stroke($graph_name);
    break;
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('スケジューラーの照会及びメンテナンス');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?php if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    /* top: 100px; */
    left: 90px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
    border-right-color: #AAAAAA;
    border-bottom-color: #AAAAAA;
    background-color:#d6d3ce;
}
-->
</style>
<script language='JavaScript'>
function init() {
<?php if ($select == 'OK') { ?>
    document.MainForm.submit();
<?php } ?>
}
function win_open(url) {
    var w = 640;
    var h = 480;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'edit_win', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
</script>
<?php if ($select == 'OK') { ?>
<form name='MainForm' action='<?= $menu->out_self() ?>' method='post'>
    <input type='hidden' name='select' value='GO'>
</form>
<?php } ?>
</head>
<body onLoad='init()'>
    <center>
        <?php if ($select == 'NG') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>機械を選択して下さい！</b>
                </td>
            </tr>
        </table>
        <?php } elseif ($select == 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: blue;'>処理中です。お待ち下さい。</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- グラフ表示のページコントロール作成 -------------->
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='0'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox' width='100%' border='0' cellspacing='0' cellpadding='1'>
            <tr>
            <td>
                <form name='reload_form' method='post' action='<?=$menu->out_self()?>' target='_self'>
                    <input style='font-size:9pt; color:blue;' type='submit' name='reload' value='再計算'>
                    <input type='hidden' name='select' value='OK' >
                </form>
            </td>
            </tr>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <table width='100%' border='0'>
            <tr><td align='center'>
                <?= $graph->GetHTMLImageMap('myimagemap')?> 
                <?= "<img src='", $graph_name, "?", uniqid(rand(),1), "' ISMAP USEMAP='#myimagemap' alt='スケジューラーの表示' border='0'>\n"; ?>
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
