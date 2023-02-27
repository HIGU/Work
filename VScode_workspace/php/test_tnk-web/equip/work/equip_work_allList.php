<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの 現在運転中 一覧表 表示  Listフレーム              //
// Copyright (C) 2004-2021      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/09/09 Created  equip_work_allList.php                               //
// 2004/11/29 機械名のwidth=70→72へ変更(20PMｺｸｲのため)部品名12文字を11文字 //
// 2005/08/05 表にnowrap追加とallHeaderと合わせるためwidth='100%'その他追加 //
// 2007/05/24 フレーム版からインラインフレーム版へ変更。機械№→指示数へ変更//
//              その他デザイン変更 旧版は backup/ にあり                    //
// 2007/07/06 チップヘルプに機械番号・グラフ・明細・日程 表示の説明追加     //
// 2021/12/14 リロード時間を暫定的に30秒へ変更                         大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // 設備メニュー 共通 function (function.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
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
<?php if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      0.95em;
    font-weight:    bold;
    font-family:    monospace;
}
table {
    font-size:      1.0em;
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
a {
    color: blue;
    text-decoration:none;
}
a:hover {
    background-color: blue;
    color: white;
}
.list tr.mouseOver
{
    background-color:   #ceffce;
}
-->
</style>
<script language='JavaScript'>
function init() {
    setInterval('document.reload_form.submit()', 30000);   // 120秒 → 30秒
}
</script>
</head>
<body onLoad='init()'>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>稼動管理対象の機械がありません！</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field list table_font' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>
            <!--
            -->
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                echo "<td class='winbox' nowrap align='right' width='3%'>{$i}</td>\n";
                if ($rec['date'] != '未指示') {
                    echo "<td class='winbox' nowrap align='left'   width='9%' title='\n{$rec['mac_no']}\n\nグラフを表示します。\n'><a href='" . $menu->out_action('運転グラフ') . "?mac_no={$rec['mac_no']}' target='_parent' style='text-decoration:none;'>{$rec['mac_name']}</a></td>\n";
                    echo "<td class='winbox' nowrap align='center' width='9%'>{$rec['date']}</td>\n";
                } else {
                    echo "<td class='winbox' nowrap align='left'   width='9%'><font color='gray'>{$rec['mac_name']}</font></td>\n";
                    echo "<td class='winbox' nowrap align='center' width='9%'><font color='gray'>{$rec['date']}</font></td>\n";
                }
                echo "<td class='winbox' nowrap align='center' width='8%'>{$rec['time']}</td>\n";
                if (is_numeric($rec['mac_state'])) {
                    $mac_state_txt = equip_machine_state($rec['mac_no'], $rec['mac_state'], $bg_color, $txt_color);
                    echo "<td class='winbox' nowrap align='center' width='9%' bgcolor='$bg_color' title='\n{$rec['mac_no']}\n\n時間の明細を表示します。\n'><a href='" . $menu->out_action('現在稼動表') . "?mac_no={$rec['mac_no']}' target='_parent' style='color:$txt_color;'>{$mac_state_txt}</a></font></td>\n";
                } else {
                    echo "<td class='winbox' nowrap align='center' width='9%'>{$rec['mac_state']}</td>\n";
                }
                echo "<td class='winbox' nowrap align='right' width='8%'>{$rec['work_cnt']}</td>\n";
                echo "<td class='winbox' nowrap align='right' width='8%'>{$rec['plan_cnt']}</td>\n";
                if ($rec['date'] != '未指示') {
                    //echo "<td class='winbox' nowrap align='center' width='7%' title='\n{$rec['mac_no']}\n\n日程を表示します。\n'><a href='" . $menu->out_action('スケジュール') . "?mac_no={$rec['mac_no']}' target='_parent' style='text-decoration:none;'>{$rec['siji_no']}</a></td>\n";
                    echo "<td class='winbox' nowrap align='center' width='7%'>{$rec['siji_no']}</td>\n";
                } else {
                    echo "<td class='winbox' nowrap align='center'    width='7%'>{$rec['siji_no']}</td>\n";
                }
                echo "<td class='winbox' nowrap align='center' width='11%'>{$rec['parts_no']}</td>\n";
                echo "<td class='winbox' nowrap align='left'   width='13%'>{$rec['parts_name']}</td>\n";
                echo "<td class='winbox' nowrap align='center' width='15%'>{$rec['str_datetime']}</td>\n";
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
<script language='JavaScript'>
<!--
// setTimeout('location.reload(true)', 10000);      // リロード用１０秒
// -->
</script>
<form name='reload_form' action='equip_work_allList.php' method='get' target='_self'>
    <input type='hidden' name='factory' value='<?php echo $factory?>'>
</form>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
