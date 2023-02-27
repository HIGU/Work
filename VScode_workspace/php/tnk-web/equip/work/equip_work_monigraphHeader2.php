<?php
//////////////////////////////////////////////////////////////////////////////
// 組立機械稼動管理システムの 運転状況 グラフ 表示  Headerフレーム          //
// Copyright (C) 2022-2022 ryota_waki@nitto-kohki.co.jp                     //
// Changed history                                                          //
// 2022/02/28 Created. equip_work_monigraphHeader.php ->                    //
//                                          equip_work_monigraphHeader2.php //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../EquipGraphClassMoni.php');    // 設備稼働管理 Graph class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 11);                    // site_index=40(設備メニュー) site_id=11(運転グラフ)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

///// GET/POSTのチェック&設定
$mac_no = @$_REQUEST['mac_no'];
if ($mac_no == '') {
    $reload = 'disabled';
} else {
    $reload = '';
    $_SESSION['mac_no'] = $mac_no;
}

/////////// グラフのX軸の時間範囲を取得
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
} else {
    $_SESSION['equip_xtime'] = 19;  // 初期化（1日（時）のグラフ）
}
$equip_xtime = $_SESSION['equip_xtime'];    // グラフの種類

if (isset($_REQUEST['reset_page'])) {
    @$_SESSION['equip_graph_page'] = 1;     // 初期化
}

/////////// 表示するグラフの日付を取得
if( isset($_REQUEST['select_date']) ) {
    $_SESSION['select_date'] = $_REQUEST['select_date'];
} else {
    $_SESSION['select_date'] = date('Ymd');  // 初期値（今日）
}
$select_date = $_SESSION['select_date'];

///// ローカル変数の初期化
$mac_name   = '';
$plan_no    = '　';
$koutei     = '　';
$parts_no   = '　';
$parts_name = '　';
$parts_mate = '　';
$plan_cnt   = '　';
$view       = 'NG';

$str_date   = "----/--/--";
$str_time   = "--:--:--";
$end_date   = "----/--/--";
$end_time   = "--:--:--";
$g_str_date = "----/--/--";
$g_str_time = "--:--:--";
$g_end_date = "----/--/--";
$g_end_time = "--:--:--";

$page            = " - / -";
$page_left_date  = "";
$page_right_date = "";

$str_mac_state ='';
$str_work_cnt  ='';
$end_mac_state ='';
$end_work_cnt  ='';
$graph_str_mac_state = '';
$graph_str_work_cnt  = '';
$graph_end_mac_state = '';
$graph_end_work_cnt  = '';
$lotDateTime = array('strDate' => '　', 'strTime' => '　', 'endDate' => '　', 'endTime' => '　');
$graphDateTime = array('strDate' => '　', 'strTime' => '　', 'endDate' => '　', 'endTime' => '　');
$page_ctl_left  = 'disabled';
$page_ctl_right = 'disabled';

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    $factory = '';
}
///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
if ($factory == '') {
    $factory = @$_SESSION['factory'];
}

//////////// 機械マスターから設備番号・設備名のリストを取得
$add_where = "AND factory='{$factory}'";
if ($factory == '') $add_where = "";
$query = "
            SELECT      mac_no, substr(mac_name,1,7) AS mac_name
            FROM        equip_machine_master2
            WHERE       survey='Y' AND mac_no!=9999 $add_where
            ORDER BY    mac_no ASC
";
$res_sel = array();
if (($rows_sel = getResult($query, $res_sel)) < 1) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>機械マスターに登録がありません！</font>";
} else {
    $mac_no_name = array();
    for ($i=0; $i<$rows_sel; $i++) {
        $mac_no_name[$i] = $res_sel[$i]['mac_no'] . " " . trim($res_sel[$i]['mac_name']);   // 機械番号と名称の間にスペース追加
    }
}

if ($mac_no != '') {
    //////////////// 機械マスターから機械名を取得
    $query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = "<div color='red'>不明</div>";   // error時は機械名をブランク
    }
    //////////// ヘッダーより見出し用の部品番号・計画数を取得
    $query = "
                SELECT  to_char(str_timestamp, 'YYYY/MM/DD') as str_date
                      , to_char(str_timestamp, 'HH24:MI:SS') as str_time
                      , to_char(end_timestamp, 'YYYY/MM/DD') as end_date
                      , to_char(end_timestamp, 'HH24:MI:SS') as end_time
                      , to_char(CURRENT_TIMESTAMP, 'YYYY/MM/DD') as now_date
                      , to_char(CURRENT_TIMESTAMP, 'HH24:MI:SS') as now_time
                      , plan_no, koutei, parts_no, plan_cnt
                FROM    equip_work_log2_header_moni
                WHERE   mac_no={$mac_no} and work_flg IS TRUE
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
//        $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$mac_no}：{$mac_name} は運転開始されていません！</font>";
        // header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
        // exit;
    } else {
        $str_date = $res_head[0]['str_date'];
        $str_time = $res_head[0]['str_time'];
        $end_date = $res_head[0]['end_date'];
        if(!$end_date) $end_date = $res_head[0]['now_date']; // 終了がない時、現在の日付
        $end_time = $res_head[0]['end_time'];
        if(!$end_time) $end_time = $res_head[0]['now_time']; // 終了がない時、現在の時刻
        $plan_no  = $res_head[0]['plan_no'];
        $koutei   = $res_head[0]['koutei'];
        $parts_no = $res_head[0]['parts_no'];
        $plan_cnt = $res_head[0]['plan_cnt'];
        $query = "select substr(midsc, 1, 12) as midsc, mzist from miitem where mipn='{$parts_no}'";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}で部品マスターの取得に失敗！";
        } else {
            $parts_name = $res_mi[0]['midsc'];
            $parts_mate = $res_mi[0]['mzist'];
            if( ! $parts_mate) $parts_mate ='-------';
            $_SESSION['work_mac_no']  = $mac_no;
            $_SESSION['work_plan_no'] = $plan_no;
            $_SESSION['work_koutei']  = $koutei;
            $view = 'OK';
        }
    }
}

if ($view == 'OK') {
    /////////////// ロット全体のデータ取得
    $q_where = "mac_no={$mac_no} AND plan_no='{$plan_no}' AND koutei={$koutei}";
    /////////////// 開始時の１レコード
    $query = "SELECT mac_state, work_cnt FROM equip_work_log2_moni WHERE $q_where ORDER BY date_time ASC OFFSET 0 LIMIT 1";
    $res = array();
    if( getResult($query, $res) <= 0 ) {
//        $_SESSION['s_sysmsg'] = "機械No：$mac_no 計画No：$plan_no 工程：$koutei の明細がありません。";
    } else {
        $str_mac_state = $res[0]['mac_state'];
        $str_work_cnt  = $res[0]['work_cnt'];
    }
    /////////////// 終了時の１レコード
    $query = "SELECT mac_state, work_cnt FROM equip_work_log2_moni WHERE $q_where ORDER BY date_time DESC OFFSET 0 LIMIT 1";
    $res = array();
    if( getResult($query, $res) <= 0 ) {
//        $_SESSION['s_sysmsg'] = "機械No：$mac_no 計画No：$plan_no 工程：$koutei の明細がありません。";
    } else {
        $end_mac_state = $res[0]['mac_state'];
        $end_work_cnt  = $res[0]['work_cnt'];
    }
    
    /////////////// グラフ範囲のデータ取得
    switch ($equip_xtime) {
        case  9:// ロット全体（X軸：日）縦棒
        case 10:// ロット全体（X軸：日）面
        case 14:// ロット全体（X軸：時間）縦棒
        case 15:// ロット全体（X軸：時間）面
        case 34:// ロット全体積み上げ（X軸：日）縦棒
        case 35:// ロット全体積み上げ（X軸：日）面
        case 40:// ロット全体積み上げ（X軸：時間）縦棒
        case 45:// ロット全体積み上げ（X軸：時間）面
            $graph_strTime = "{$str_date}　$str_time";
            $graph_endTime = "{$end_date}　$end_time";
            $q_s_where  = "mac_no=$mac_no AND plan_no='$plan_no' AND koutei=$koutei AND to_char(date_time, 'YYYY/MM/DD')='$str_date'";
            $q_e_where  = "mac_no=$mac_no AND plan_no='$plan_no' AND koutei=$koutei AND to_char(date_time, 'YYYY/MM/DD')<='$end_date'";
            $page_enabled = false;
            break;
        case 19:// 1日（X軸：時）縦棒
        case 20:// 1日（X軸：時）面
        case 24:// 1日（X軸：分）縦棒
        case 25:// 1日（X軸：分）面
        case 30:// 1日（X軸：秒）面
        case 50:// 1日積み上げ（X軸：時間）縦棒
        default:// 設定なし不明
            $graph_strTime = substr($select_date, 0, 4) ."/". substr($select_date, 4, 2) ."/". substr($select_date, 6, 2);
            $graph_endTime = $graph_strTime;
            $q_s_where  = "mac_no=$mac_no AND plan_no='$plan_no' AND koutei=$koutei AND to_char(date_time, 'YYYYMMDD')=$select_date";
            $q_e_where  = "mac_no=$mac_no AND plan_no='$plan_no' AND koutei=$koutei AND to_char(date_time, 'YYYYMMDD')=$select_date";
            $page_enabled = true;
            break;
    }
    /////////////// 開始時の１レコード
    $query = "
                SELECT  to_char(date_time, 'YYYY/MM/DD') AS g_str_date
                      , to_char(date_time, 'HH24:MI:SS') AS g_str_time
                      , mac_state
                      , work_cnt
                FROM    equip_work_log2_moni
                WHERE   $q_s_where
                ORDER BY date_time ASC
                OFFSET 0 LIMIT 1
    ";
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
//        $_SESSION['s_sysmsg'] = "機械No：$mac_no 計画No：$plan_no 工程：$koutei 開始：{$graph_strTime} のグラフデータがありません。";
    } else {
        $g_str_date = $res[0]['g_str_date'];
        $g_str_time = $res[0]['g_str_time'];
        $graph_str_mac_state = $res[0]['mac_state'];
        $graph_str_work_cnt  = $res[0]['work_cnt'];
    }
    /////////////// 終了時の１レコード
    $query = "
                SELECT  to_char(date_time, 'YYYY/MM/DD') AS g_end_date
                      , to_char(date_time, 'HH24:MI:SS') AS g_end_time
                      , mac_state
                      , work_cnt
                FROM    equip_work_log2_moni
                WHERE   $q_e_where
                ORDER BY date_time DESC
                OFFSET 0 LIMIT 1
            ";
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
//        $_SESSION['s_sysmsg'] = "機械No：$mac_no 指示No：$plan_no 工程：$koutei 終了：{$graph_endTime} のグラフデータがありません。";
    } else {
        $g_end_date = $res[0]['g_end_date'];
        $g_end_time = $res[0]['g_end_time'];
        $graph_end_mac_state = $res[0]['mac_state'];
        $graph_end_work_cnt  = $res[0]['work_cnt'];
    }
/**    
    if ($graph_endTime > date('YmdHis')) {  // 未来だったら
        $graph_end_mac_state = '';
        $graph_end_work_cnt  = '';
    }
/**/    
    $page_now = $page_total = 1;
    // [前ページ] 有効にできるかチェック
    $query = "
                SELECT  to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDD') AS 日付
                FROM    equip_work_log2_moni
                WHERE   mac_no='$mac_no' AND plan_no='$plan_no' AND to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDD')<'$select_date'
                GROUP BY 日付 ORDER BY 日付 DESC
    ";
    $res_left_page = array();
    $rows_page_left = getResult($query, $res_page_left);
    if( $page_enabled && $rows_page_left > 0 ) {
        $page_ctl_left = '';// 有効
        $page_now   += $rows_page_left;
        $page_total += $rows_page_left;
        $page_left_date = $res_page_left[0][0];
    }
    
    // [次ページ] 有効にできるかチェック
    $query = "
                SELECT  to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDD') AS 日付
                FROM    equip_work_log2_moni
                WHERE   mac_no='$mac_no' AND plan_no='$plan_no' AND to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDD')>'$select_date'
                GROUP BY 日付 ORDER BY 日付
    ";
    $res_right_page = array();
    $rows_page_right = getResult($query, $res_page_right);
    if( $page_enabled && $rows_page_right > 0 ) {
        $page_ctl_right = '';// 有効
        $page_total += $rows_page_right;
        $page_right_date = $res_page_right[0][0];
    }
    $page = " $page_now / $page_total";// 表示するページをデータ
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("{$mac_no}　{$mac_name}　運転 グラフ 表示");

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();

// グラフ種類のリスト内容
function ddlist_L( $xtime )
{
    $list = array("ロット全体", "積み上げ");
    $no   = array(15, 35);
    $max = count($list);
    for( $i=0; $i<$max; $i++ ) {
        $selected = "";
        if($no[$i] == $xtime) $selected = " selected";
        echo "<option value='{$no[$i]}' {$selected}>{$list[$i]}</option>";
    }
}
// グラフ種類のリスト内容
function ddlist_R( $xtime )
{
    $list = array("ロット(日)棒", "ロット(日)面", "ロット(時)棒", "ロット(時)面", " １日 (時)棒", "１日 (時)面", "１日 (分)棒", "１日 (分)面", "１日 (秒)面", "ロッ積(日)棒", "ロッ積(日)面", "ロッ積(時)棒", "ロッ積(時)面", "１日積(時)棒");
    $no   = array(9, 10, 14, 15, 19, 20, 24, 25, 30, 34, 35, 40, 45, 50);
    $max = count($list);
    for( $i=0; $i<$max; $i++ ) {
        $selected = "";
        if($no[$i] == $xtime) $selected = " selected";
        echo "<option value='{$no[$i]}' {$selected}>{$list[$i]}</option>";
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<?= $menu->out_site_java() ?>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    font-size:          10.5pt;
    font-weight:        bold;
    font-family:        monospace;
    color:              blue;
    /* background-color:   yellow; */
}
.item {
    position:       absolute;
    top:            90px;
    left:           90px;
}
.table_font {
    font-size:      11.5pt;
    font-family:    monospace;
}
.ext_font {
    /* background-color:   yellow; */
    color:              blue;
    font-size:          10.5pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
    border-right-color: #DFDFDF;
    border-bottom-color: #DFDFDF;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    // document.mac_form.mac_no.focus();  // カーソルキーで機械を変更出来るようにする
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- 見出しを表示 ------------------------>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <td align='center' width='100'>
                    <form name='mac_form' method='post' action='<?= $menu->out_self() ?>'>
                        <select name='mac_no' class='ret_font' onChange='document.mac_form.submit()'>
                        <?php if ($mac_no == '') echo "<option value=''>機械選択</option>\n" ?>
                        <?php
                        for ($j=0; $j<$rows_sel; $j++) {
                            if ($mac_no == $res_sel[$j]['mac_no']) {
                                printf("<option value='%s' selected>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            }
                        }
                        ?>
                        </select>
                        <input type='hidden' name='reset_page' value=''>
                    </form>
                </td>
                <td align='center' nowrap width='65'>製品No</td>
                <td align='center' nowrap width='85'><?= $parts_no ?></td>
                <td align='center' nowrap width='65'>製品名</td>
                <td class='pick_font' align='center' nowrap width='130'><?= $parts_name ?></td>
<!-- 組立には必要ない --
                <td align='center' nowrap width='50'>材質</td>
                <td class='pick_font' align='center' nowrap width='70'><?= $parts_mate ?></td>
<!-- -->
                <td align='center' nowrap width='65'>計画No</td>
                <td align='center' nowrap width='50'><?= $plan_no ?></td>
                <td align='center' nowrap width='40'>工程</td>
                <td align='center' nowrap width='20'><?= $koutei ?></td>
                <td align='center' nowrap width='60'>計画数</td>
                <td align='right'  nowrap width='60'><?= number_format($plan_cnt) ?></td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        
        <!-- <hr color='797979'> -->
        
        <table width='100%' border='0'>
        <tr>
        <td>
            <!-------------- グラフ表示のページコントロール作成 左 -------------->
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
               <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox' width=100% bgcolor='#d6d3ce'>
                <form name='page_ctl_left' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td nowrap>
                        <input class='pt11b' type='submit' name='backward' value='前ページ' <?=$page_ctl_left?>><?php echo $page; ?>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                        <input type='hidden' name='select_date' value='<?=$page_left_date?>' >
                    </td>
                </tr>
                </form>
                <form name='xtime_ctl_left' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <select name='equip_xtime' class='ret_font' onChange='document.xtime_ctl_left.submit()'>
                            <?php ddlist_R($equip_xtime); ?>
                        </select>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='reset_page' value=''>
                        <input type='hidden' name='select_date' value='<?=$select_date?>' >
                    </td>
                </tr>
                </form>
            </table> <!----- ダミー End ----->
                </td></tr>
            </table>
        </td>
        <td>
            <!--------------- ここから見出し表(開始と現在)２行を表示する -------------------->
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
                <tr><td> <!-- ダミー(デザイン用) -->
            <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='2'>
                <tr>
                    <th nowrap>
                        <form name='reload_form' method='post' action='<?=$menu->out_self()?>' target='_self'>
                        <input style='font-size:10pt; color:blue;' type='submit' name='reload' value='再表示' <?=$reload?>>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                        <input type='hidden' name='select_date' value='<?=$select_date?>' >
                        </form>
                    </th>
                    <th nowrap>　</th>
                    <th nowrap>年月日</th><th nowrap>時分秒</th><th nowrap>状態</th><th nowrap>加工数</th>
                    <th nowrap>　</th>
                    <th nowrap>年月日</th><th nowrap>時分秒</th><th nowrap>状態</th><th nowrap>加工数</th>
                </tr>
                <tr class='table_font'>
                    <td align='center' nowrap>ロット全体</td>
                    <td class='ext_font' align='center' nowrap>開始</td>
                    <td align='center' nowrap><?php echo $str_date ?></td>
                    <td align='center' nowrap><?php echo $str_time ?></td>
                    <?php if ($str_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $str_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>--------</td>
                    <?php } ?>
                    <td align='right' nowrap><?php if($str_work_cnt != '') echo number_format($str_work_cnt); else echo '-,---'; ?></td>
                    
                    <td class='ext_font' align='center' nowrap>現在</td>
                    <td align='center' nowrap><?php echo $end_date ?></td>
                    <td align='center' nowrap><?php echo $end_time ?></td>
                    <?php if ($end_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $end_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>--------</td>
                    <?php } ?>
                    <td align='right' nowrap><?php if($end_work_cnt != '') echo number_format($end_work_cnt); else echo '-,---'; ?></td>
                </tr>
                <tr class='table_font'>
                    <td align='center' nowrap>グラフ範囲</td>
                    <td class='ext_font' align='center' nowrap>開始</td>
                    <td align='center' nowrap><?php echo $g_str_date ?></td>
                    <td align='center' nowrap><?php echo $g_str_time ?></td>
                    <?php if ($graph_str_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $graph_str_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>--------</td>
                    <?php } ?>
                    <td align='right' nowrap><?php if($graph_str_work_cnt != '') echo number_format($graph_str_work_cnt); else echo '-,---'; ?></td>
                    
                    <td class='ext_font' align='center' nowrap>終了</td>
                    <td align='center' nowrap><?php echo $g_end_date ?></td>
                    <td align='center' nowrap><?php echo $g_end_time ?></td>
                    <?php if ($graph_end_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $graph_end_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>--------</td>
                    <?php } ?>
                    <td align='right' nowrap><?php if($graph_end_work_cnt != '') echo number_format($graph_end_work_cnt); else echo '-,---'; ?></td>
                </tr>
            </table>
                </td></tr>
            </table> <!-- ダミーEnd -->
            
        </td>
        <td>
            <!-------------- グラフ表示のページコントロール作成 右 -------------->
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
               <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox' width=100% bgcolor='#d6d3ce'>
                <form name='page_ctl_right' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td nowrap>
                        <input class='pt11b' type='submit' name='forward' value='次ページ' <?=$page_ctl_right?>><?php echo $page; ?>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                        <input type='hidden' name='select_date' value='<?=$page_right_date?>' >
                    </td>
                </tr>
                </form>
                <form name='xtime_ctl_right' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <select name='equip_xtime' class='ret_font' onChange='document.xtime_ctl_right.submit()'>
                            <?php ddlist_R($equip_xtime); ?>
                        </select>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='reset_page' value=''>
                    </td>
                </tr>
                </form>
            </table> <!----- ダミー End ----->
                </td></tr>
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
<script language='JavaScript'>
<!--
// サーバー側とデータを通信するための機能を持つAPIを取得します。
function createXmlHttpRequest()
{
    var xmlhttp=null;
    if(window.ActiveXObject) {
        try {
            xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch(e) {
            try {
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch (e2) {
                ;
            }
        }
    } else if(window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
    }
    return xmlhttp;
}

// 最終稼働ログの時間を取得
var last_log_time = "--:--:--"; // 最終稼働ログの時間（初期値）
function getLastLogTime(moni)
{
    var xmlhttp=createXmlHttpRequest();
    if(xmlhttp!=null) {
        xmlhttp.open("POST", "./equip_work_monilogtime_output.php?moni="+moni, false);
        xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xmlhttp.send();
        return xmlhttp.responseText;
    } else {
        ;
    }
}

// 表示更新
function viewUp()
{
    if( last_log_time == getLastLogTime("next") ) return; // 最終稼働ログに変更がなければ再表示しない。
    
    location.replace("equip_work_monigraphHeader2.php?equip_xtime=<?=$equip_xtime?>&mac_no=<?=$mac_no?>")
}

last_log_time = getLastLogTime("first");
// 稼働状況の監視が必要（稼働ログなし または、当日）であれば監視を開始
if( <?php echo json_encode($g_end_date); ?> == "----/--/--" || <?php echo "'".$g_end_date."'"; ?> == <?php echo "'".date('Y/m/d')."'"; ?> ) {
    // 10秒間隔で監視を行い、最終稼働ログに変更があれば更新する
    setInterval('viewUp()',10000);
}
// -->
</script>

</html>
<Script Language='JavaScript'>
    document.MainForm.select.value = '<?=$view?>';
    document.MainForm.target = 'List';
    document.MainForm.action = 'equip_work_monigraphList2.php';
    document.MainForm.submit();
</Script>
<?=$menu->out_alert_java()?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
