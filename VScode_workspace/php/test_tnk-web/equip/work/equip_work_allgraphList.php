<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの 現在運転中 一覧表 表示  Listフレーム              //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/09/09 Created  equip_work_allList.php                               //
// 2004/11/29 機械名のwidth=70→72へ変更(20PMｺｸｲのため)部品名12文字を11文字 //
// 2005/08/05 表にnowrap追加とallHeaderと合わせるためwidth='100%'その他追加 //
// 2007/05/24 フレーム版からインラインフレーム版へ変更。機械№→指示数へ変更//
//              その他デザイン変更 旧版は backup/ にあり                    //
// 2007/07/06 チップヘルプに機械番号・グラフ・明細・日程 表示の説明追加     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // 設備メニュー 共通 function (function.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../EquipAllGraphClass.php');    // 設備稼働管理 Graph class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 9);                     // site_index=40(設備メニュー) site_id=9(運転中一覧)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// 自分をフレーム定義に変える
$menu->set_self(EQUIP2 . 'work/equip_work_all.php');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('運転グラフ', EQUIP2 . 'work/equip_work_graph.php');
$menu->set_action('現在稼動表', EQUIP2 . 'work/equip_work_chart.php');
$menu->set_action('スケジュール', EQUIP2 . 'plan/equip_plan_graph.php');
// $menu->set_frame('運転グラフ', EQUIP2 . 'work/equip_work_graph.php');
// $menu->set_frame('現在稼動表', EQUIP2 . 'work/equip_work_chart.php');

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    ///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
    $factory = @$_SESSION['factory'];
}

//////////// 機械マスターから設備番号・設備名のリストを取得(監視設定されている物)
if ($factory == '') {
    $query = "select mac_no                     AS mac_no
                    , substr(mac_name, 1, 7)    AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                order by mac_no ASC
    ";
} else {
    $query = "select mac_no                     AS mac_no
                    , substr(mac_name, 1, 7)    AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                    and
                    factory='{$factory}'
                order by mac_no ASC
    ";
}
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>機械マスターに登録がありません！</font>";
    $view = 'NG';
} else {
    $view = 'OK';
}

if ($view == 'OK') {
    for ($r=0; $r<$rows; $r++) {
        ////////// 稼動中かヘッダーをチェック
        $query = "select  siji_no
                        , koutei
                        , parts_no
                        , substr(midsc, 1, 11)      AS parts_name
                        , plan_cnt
                        -- , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_datetime
                        , to_char(str_timestamp AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI') as str_datetime
                    from
                        equip_work_log2_header
                    left outer join
                        miitem
                    on
                        (parts_no=mipn)
                    where
                        mac_no={$res[$r]['mac_no']}
                        and
                        work_flg IS TRUE
                    offset 0 limit 1
        ";
        $hed = array();
        if (getResult($query, $hed) > 0) {
            $res[$r]['siji_no']         = $hed[0]['siji_no'];
            $res[$r]['koutei']          = $hed[0]['koutei'];
            $res[$r]['parts_no']        = $hed[0]['parts_no'];
            $res[$r]['parts_name']      = mb_convert_kana($hed[0]['parts_name'], 'k');  // 半角カナに変換
            $res[$r]['plan_cnt']        = number_format($hed[0]['plan_cnt']);
            $res[$r]['str_datetime']    = $hed[0]['str_datetime'];
            // 最新の明細データ取得
            $query = "select to_char(date_time AT TIME ZONE 'JST', 'YY/MM/DD') as date
                            ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                            ,mac_state
                            ,work_cnt
                        from
                            equip_work_log2
                        where
                            equip_index(mac_no, siji_no, koutei, date_time) <= '{$res[$r]['mac_no']}{$res[$r]['siji_no']}{$res[$r]['koutei']}99999999999999'
                            and
                            equip_index(mac_no, siji_no, koutei, date_time) >= '{$res[$r]['mac_no']}{$res[$r]['siji_no']}{$res[$r]['koutei']}00000000000000'
                        order by
                            equip_index(mac_no, siji_no, koutei, date_time) DESC
                        offset 0 limit 1
            ";
            $log = array();
            if (getResult($query, $log) > 0) {
                $res[$r]['date']        = $log[0]['date'];
                $res[$r]['time']        = $log[0]['time'];
                $res[$r]['mac_state']   = $log[0]['mac_state'];
                $res[$r]['work_cnt']    = number_format($log[0]['work_cnt']);
            } else {
                $res[$r]['date']        = '&nbsp;';
                $res[$r]['time']        = '&nbsp;';
                $res[$r]['mac_state']   = '&nbsp;';
                $res[$r]['work_cnt']    = '&nbsp;';
            }
        } else {
                $res[$r]['date']        = '未指示';
                $res[$r]['time']        = '&nbsp;';
                $res[$r]['mac_state']   = '&nbsp;';
                $res[$r]['work_cnt']    = '&nbsp;';
            $res[$r]['siji_no']         = '&nbsp;';
            $res[$r]['koutei']          = '&nbsp;';
            $res[$r]['parts_no']        = '&nbsp;';
            $res[$r]['parts_name']      = '&nbsp;';
            $res[$r]['plan_cnt']        = '&nbsp;';
            $res[$r]['str_datetime']    = '&nbsp;';
        }
    }
    $num = count($res[0]);
}

// グラフ作成
$mac_no  = $res[2]['mac_no'];
$siji_no = $res[2]['siji_no'];
$koutei  = $res[2]['koutei'];

$mac_no  = '1346';
$siji_no = '72587';
$koutei  = '1';

if (isset($_REQUEST['select'])) {
    $select = $_REQUEST['select'];
} else {
    $select = 'NG';
}
if ($mac_no == '') {
    $select = 'NG';
} else {
    $select = '';
}
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
}

if ($select == '') {
    if (isset($_SESSION['equip_xtime'])) {
        $equip_xtime = $_SESSION['equip_xtime'];
        unset($_SESSION['equip_xtime']);
    } else {
        $equip_xtime = 12;
    }
    /////////// 設備管理グラフのインスタンス作成 解像度180(高解像度は360)
    $equip_graph = new EquipGraph($mac_no, $siji_no, $koutei, 180);
    $equip_graph->set_xtime($equip_xtime);      // グラフの希望の時間軸を設定
    $equip_xtime = $equip_graph->out_xtime();   // グラフの時間軸のスケール設定値を取得
    // グラフの範囲内の書式付 DATE TIME の取得  配列(strDate, strTime, endDate, endTime)
    $graphDateTime = $equip_graph->out_graph_timestamp();
    // $graph_name = ('graph/equip' . session_id() . '.png');
    $graph_name = 'graph/equip_work_graph.png';
    $equip_graph->out_graph($graph_name);
    $graph_page = $_SESSION['equip_graph_page'];    // 表示ページ数の取得
}

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
<!--
<?php if ($select == 'OK') { ?>
<form name='MainForm' action='<?= $menu->out_self() ?>' method='post'>
    <input type='hidden' name='select' value='GO'>
</form>
<?php } ?>
-->
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
        <table width='100%' border='0'>
            <tr><td align='center'>
            <?= "<img src='", $graph_name, "?", uniqid(rand(),1), "' alt='加工数 状態 グラフ' border='0'>\n"; ?>
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
<?php if ($select == 'OK') { ?>
<script language='JavaScript'>
<!--
setTimeout('location.replace("equip_work_allgraphList.php?select=<?=$select?>&equip_xtime=<?=$equip_xtime?>")',10000);      // リロード用１０秒
// -->
</script>
<?php } ?>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
