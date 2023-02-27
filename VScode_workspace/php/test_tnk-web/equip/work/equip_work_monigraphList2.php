<?php
//////////////////////////////////////////////////////////////////////////////
// 組立機械稼動管理システムの 運転 グラフ 表示  Graph本体フレーム           //
// Copyright (C) 2022-2022 ryota_waki@nitto-kohki.co.jp                     //
// Changed history                                                          //
// 2022/02/28 Created. equip_work_monigraphList.php ->                      //
//                                            equip_work_monigraphList2.php //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // 設備メニュー 共通 function (function.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../EquipGraphClassMoni.php');    // 設備稼働管理 Graph class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 11);                    // site_index=40(設備メニュー) site_id=10(状況表)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

$mac_no  = @$_SESSION['work_mac_no'];
$plan_no = @$_SESSION['work_plan_no'];
$koutei  = @$_SESSION['work_koutei'];
if (isset($_REQUEST['select'])) {
    $select = $_REQUEST['select'];
} else {
    $select = 'NG';
}
if ($mac_no == '') {
    $select = 'NG';
}
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
}

if ($select == 'GO') {
    if (isset($_SESSION['equip_xtime'])) {
        $equip_xtime = $_SESSION['equip_xtime'];
        unset($_SESSION['equip_xtime']);
    } else {
        $equip_xtime = 20;
    }

    $s_date = $_SESSION['select_date'];
    $q_select = "";
    $v_type = "";   // 表示グラフ "lot" or "day"
    $x_type = "";   // ｘ軸 "day" or "hou" or "min" or "sec"
    switch ($equip_xtime) {
        // ロット全体（X軸：日）
        case  9:// 縦棒
        case 10:// 面
        case 34:// 縦棒（積み上げ）
        case 35:// 面（積み上げ）
            $q_select = "to_char((CAST(date_time AS TIMESTAMP)), 'YYYY-MM-DD 99:99:99') AS 日時";
            $v_type = "lot";    // 表示：ロット全体
            $x_type = "day";    // ｘ軸：日
            break;
        // ロット全体（X軸：時間）
        case 14:// 縦棒
        case 15:// 面
        case 40:// 縦棒（積み上げ）
        case 45:// 面（積み上げ）
            $q_select = "to_char((CAST(date_time AS TIMESTAMP)), 'YYYY-MM-DD HH24:00:00') AS 日時";
            $v_type = "lot";    // 表示：ロット全体
            $x_type = "hou";    // ｘ軸：時間
            break;
        // 1日（X軸：時）
        case 19:// 縦棒
        case 20:// 面
        case 50:// 縦棒（積み上げ）
            $q_select = "to_char((CAST(date_time AS TIMESTAMP)), 'YYYY-MM-DD HH24:00:00') AS 日時";
            $v_type = "day";    // 表示：１日
            $x_type = "hou";    // ｘ軸：時間
            break;
        // 1日（X軸：分）
        case 24:// 縦棒
        case 25:// 面
            $q_select = "to_char((CAST(date_time AS TIMESTAMP)), 'YYYY-MM-DD HH24:MI:00') AS 日時";
            $v_type = "day";    // 表示：１日
            $x_type = "min";    // ｘ軸：分
            break;
        // 1日（X軸：秒）
        case 30:// 面
        default:// 設定なし不明
            $q_select = "to_char((CAST(date_time AS TIMESTAMP)), 'YYYY-MM-DD HH24:MI:SS') AS 日時";
            $v_type = "day";    // 表示：１日
            $x_type = "sec";    // ｘ軸：秒
            break;
    }
    $q_select .= ", max(work_cnt) AS 組立数";
    if( $x_type == "min" || $x_type == "sec" ) {
        $q_select .= ", max(mac_state) AS 機械の状態";
    }
    $q_where = "";
    if( $v_type == "lot" ) {
        $q_where = "mac_no='$mac_no' AND plan_no='$plan_no'";
    } else {
        $q_where = "mac_no='$mac_no' AND plan_no='$plan_no' AND to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDD')='$s_date'";
    }
    $query  = "select $q_select FROM equip_work_log2_moni WHERE $q_where GROUP BY 日時 ORDER BY 日時";
    $res    = array();
    $rows   = getResult2($query, $res);
    $target = json_encode($res);
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<!--
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
-->
<script src="canvasjs.min.js"></script>
<script src="equip_work_graph.js"></script>

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
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
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
</script>
<?php if ($select == 'OK') { ?>
<form name='MainForm' action='<?= $menu->out_self() ?>' method='post'>
    <input type='hidden' name='select' value='GO'>
</form>
<?php } ?>
</head>

<body onLoad='init()'>
    <center>
    <?php
    if( $select == 'NG' ) {
    ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>選択した機械は、運転開始されていません。<BR><BR>別の機械を選択して下さい！</b>
                </td>
            </tr>
        </table>
    <?php
    } else if( $select == 'OK' ) {
    ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: blue;'>処理中です。お待ち下さい。</b>
                </td>
            </tr>
        </table>
    <?php
    } else if( $rows <= 0 ) {   // グラフ表示データなし
        $work = substr($s_date,0,4) . "/" . substr($s_date,4,2) . "/" . substr($s_date,6,2);
    ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: red;'><?php echo $work; ?> の稼働ログがない為、グラフを表示できません。</b>
                </td>
            </tr>
        </table>
    <?php
    } else {    // グラフ表示
        // ヘッダーより計画数を取得
        $query = "SELECT plan_cnt FROM equip_work_log2_header_moni WHERE mac_no=$mac_no AND work_flg IS TRUE";
        $res_head = array();
        $plan_cnt = 0;
        if( getResult($query, $res_head) > 0 ) {
            $plan_cnt = $res_head[0][0];
        }
        // 組立機械稼動管理システムの個別稼動ログよりログ日時、組立数
        $q_where = "mac_no=$mac_no AND plan_no='$plan_no' AND koutei=$koutei";
        $query = "SELECT date_time, work_cnt FROM equip_work_log2_moni WHERE $q_where ORDER BY date_time DESC OFFSET 0 LIMIT 1";
        $res = array();
        $total_cnt = 0;
        if( getResult($query, $res) > 0 ) {
            $total_cnt = $res[0][1];
        }
        
        $graph_cnt = 0;
        if( $v_type == "day" ) {
            // 組立機械稼動管理システムの個別稼動ログより組立数の最大と最小を取得
            $q_where = "mac_no=$mac_no AND plan_no='$plan_no' AND koutei=$koutei AND to_char(date_time, 'YYYYMMDD')='$s_date'";
            $query = "SELECT max(work_cnt) FROM equip_work_log2_moni WHERE $q_where";
            $res = array();
            $graph_cnt = 0; // グラフ内の組立数初期化
            if( getResult($query, $res) > 0 ) $graph_cnt = $res[0][0]; // グラフ内の組立数最大をセット
            $q_where = "mac_no=$mac_no AND plan_no='$plan_no' AND koutei=$koutei AND to_char(date_time, 'YYYYMMDD')<'$s_date'";
            $query = "SELECT max(work_cnt) FROM equip_work_log2_moni WHERE $q_where";
            $res = array();
            if( getResult($query, $res) > 0 ) $graph_cnt -= $res[0][0]; // グラフ内の組立数を計算（最大から前回の最大を引く）
        }
    ?>
        <BR> <!-- グラフ領域 -->
        <div id="chartContainer" style="height: 300px; width: 100%;"></div>
        <script>GraphDisplay(<?php echo "$target, $equip_xtime, $plan_cnt";?>);</script>
        <BR> <!-- 日の組立数表示領域 -->
        <div id="wrk_cnt_table"></div>
        <script>StackedTable("wrk_cnt_table", <?php echo "$target, $plan_cnt"; ?>);</script>
        <BR> <!-- 累積時間表示領域 -->
        <div id="table"></div>
        <script>StateTimeTable("table", <?php echo "$graph_cnt, $total_cnt, $plan_cnt"; ?>);</script>
    <?php
    }
    ?>
    </center>
</body>
<?php if ($select == 'GO') { ?>
<script language='JavaScript'>
<!--
if( <?php echo $s_date; ?> == <?php echo date('Ymd'); ?> ) {
//    setTimeout('location.replace("equip_work_monigraphList2.php?select=<?=$select?>&equip_xtime=<?=$equip_xtime?>")',10000);      // リロード用１０秒
}
// -->
</script>
<?php } ?>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
