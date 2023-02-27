<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの現在運転状況一覧マップ表示(レイアウト)Listフレーム //
// ６工場組立設備用                                                         //
// Copyright (C) 2021-2021 Norihisa.Ohya norhisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2021/06/22 Created  equip_work_monimapList.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // 設備メニュー 共通 function (function.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 12);                    // site_index=40(設備メニュー) site_id=12(マップ一覧)

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('稼動状況 レイアウト 表示');
//////////// 表題の設定
// $menu->set_caption('工場選択');

////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須
//////////// 自分をフレーム定義に変える
$menu->set_self(EQUIP2 . 'work/equip_work_monimap.php');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('運転グラフ', EQUIP2 . 'work/equip_work_monigraph.php');
$menu->set_action('現在稼動表', EQUIP2 . 'work/equip_work_monichart.php');
$menu->set_action('スケジュール', EQUIP2 . 'plan/equip_plan_monigraph.php');
// $menu->set_frame('運転グラフ', EQUIP2 . 'work/equip_work_graph.php');
// $menu->set_frame('現在稼動表', EQUIP2 . 'work/equip_work_chart.php');

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    ///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
    $factory = @$_SESSION['factory'];
}

$reload_java = "onLoad=\"setInterval('document.reload_form.submit()', 10000)\"";

//////////// 機械マスターから設備番号・設備名のリストを取得(監視設定されている物)
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                order by mac_no ASC
    ";
} else {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
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
                        , substr(midsc, 1, 12)      AS parts_name
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
            $res[$r]['plan_cnt']        = $hed[0]['plan_cnt'];
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

///// 機械毎の状態出力関数
function mac_state_view($mac_no)
{
    global $res, $menu;
    foreach ($res as $rec) {
        if ($rec['mac_no'] == $mac_no) {
            if (is_numeric($rec['mac_state'])) {
                $mac_state_txt = equip_machine_state($rec['mac_no'], $rec['mac_state'], $bg_color, $txt_color);
            } else {
                $mac_state_txt = '未指示'; $bg_color = 'white'; $txt_color = 'black';
            }
            echo "<table style='margin:0%;' border='1'>\n";
            echo "    <tr>\n";
            if ($mac_state_txt == '未指示') {
                echo "        <td style='background-color:{$bg_color}; color:{$txt_color}; font-size:9.4pt; font-weight:normal;' align='center' width='55'>{$mac_state_txt}</td>\n";
            } else {
                echo "        <td style='background-color:{$bg_color}; color:{$txt_color}; font-size:9.4pt; font-weight:normal;' align='center' width='55'><a href='" . $menu->out_action('現在稼動表') . "?mac_no={$rec['mac_no']}' target='_parent' style='text-decoration:none; color:{$txt_color}; background-color:{$bg_color};'>{$mac_state_txt}</a></td>\n";
            }
            echo "    </tr>\n";
            echo "    <tr>\n";
            if ($mac_state_txt == '未指示') {
                echo "        <td style='font-size:9.4pt; font-weight:normal;' align='center' width='55'>{$rec['mac_no']}</td>\n";
            } else {
                echo "        <td style='font-size:9.4pt; font-weight:normal;' align='center' width='55'><a href='" . $menu->out_action('運転グラフ') . "?mac_no={$rec['mac_no']}' target='_parent' style='text-decoration:none;'>{$rec['mac_no']}</a></td>\n";
            }
            echo "    </tr>\n";
            echo "    <tr>\n";
            if ($mac_state_txt == '未指示') {
                echo "        <td style='font-size:9.4pt; font-weight:normal;' align='center' width='55'>{$rec['mac_name']}</td>\n";
            } else {
                //echo "        <td style='font-size:9.4pt; font-weight:normal;' align='center' width='55'><a href='" . $menu->out_action('スケジュール') . "?mac_no={$rec['mac_no']}' target='_parent' style='text-decoration:none;'>{$rec['mac_name']}</a></td>\n";
                echo "        <td style='font-size:9.4pt; font-weight:normal;' align='center' width='55'>{$rec['mac_name']}</td>\n";
            }
            echo "    </tr>\n";
            echo "</table>\n";
        }
    }
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
table {
    font-size:      12pt;
    font-weight:    bold;
    /* font-family:    monospace; */
}
.item {
    position: absolute;
    /* top: 100px; */
    left:  5px;
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
a {
    color: red;
}
a:hover {
    background-color: blue;
    color: white;
}
-->
</style>
<script language='JavaScript'>
<!--
var FLAG = 1;
var ID;
function reload_switch() {
    if (FLAG == 1) {
        FLAG = 0;
        clearInterval(ID);
        alert('自動更新を停止しました。');
    } else {
        FLAG = 1;
        ID = setInterval('document.reload_form.submit()', 10000);
        alert('自動更新を開始しました。');
    }
}
// window.document.onclick = reload_switch;
function init() {
    ID = setInterval('document.reload_form.submit()', 10000);
}
function win_open(img_src, img_alt) {
    var w = 640;
    var h = 480;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open('photo_view.php?img_src='+img_src+'&img_alt='+img_alt, 'view_win', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
</head>
<body onLoad="init()">
    <center>
        <a href='JavaScript:reload_switch();' style='font-size:12pt; font-weight:bold; text-decoration:none;'>表示更新切替</a>
<?php
switch ($factory) {
case 1:
    require_once ('equip_work_map1List.php');
    break;
/*
case 4:
    require_once ('equip_work_map4List.php');
    break;
*/
case 5:
    require_once ('equip_work_map5List.php');
    break;
case 7:
    require_once ('equip_work_map7cList.php');
    break;
case 8:
    require_once ('equip_work_map7susList.php');
    break;
default:
    echo "        <table border='0' class='msg'>\n";
    echo "            <tr>\n";
    echo "                <td>\n";
    echo "                    <b style='color: blue;'>申し訳ありません。現在作成中です。</b>\n";
    echo "                </td>\n";
    echo "            </tr>\n";
    echo "        </table>\n";
    break;
}
?>
    </center>
</body>
<script language='JavaScript'>
<!--
// setTimeout('location.reload(true)', 10000);     // リロード用１０秒
// -->
</script>
<form name='reload_form' action='equip_work_mapList.php' method='get' target='_self'>
    <input type='hidden' name='factory' value='<?=$factory?>'>
</form>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
