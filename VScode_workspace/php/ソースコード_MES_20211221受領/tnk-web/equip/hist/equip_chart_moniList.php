<?php
//////////////////////////////////////////////////////////////////////////////
// 組立機械稼動管理システムの 運転 実績 状況表 照会  Listフレーム           //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_chart_moniList.php                             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // 設備メニュー 共通 function (function.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../tnk_func.php');        // TNK 全共通 tnk_func
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 6);                     // site_index=40(設備メニュー) site_id=6(実績照会)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

$offset = 0;        // テスト用
$limit  = 1000;     // テスト用

$mac_no  = @$_SESSION['mac_no'];
$plan_no = @$_SESSION['plan_no'];
$koutei  = @$_SESSION['koutei'];
if (isset($_POST['select'])) {
    $select = $_POST['select'];
} else {
    $select = 'NG';
}
if (isset($_POST['sort'])) {
    $sort = $_POST['sort'];
} else {
    $sort = 'ASC';
}

///////////// 簡易ページ制御
if ($select = 'GO') {
    if (isset($_REQUEST['forward'])) {
        $_SESSION['equip_chart_offset'] += $limit;
        $offset = $_SESSION['equip_chart_offset'];
    } elseif (isset($_REQUEST['backward'])) {
        $_SESSION['equip_chart_offset'] -= $limit;
        if ($_SESSION['equip_chart_offset'] < 0) {
            $_SESSION['equip_chart_offset'] = 0;
            $_SESSION['s_sysmsg'] = '前頁はありません！';
        }
        $offset = $_SESSION['equip_chart_offset'];
    } else {
        $offset = 0;    // 初期化
        $_SESSION['equip_chart_offset'] = $offset;
    }
} else {
    $offset = $_SESSION['equip_chart_offset'];
}

if ($select == 'GO') {
    //////////// ヘッダーより開始日時と終了日時の取得
    $query = "select to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
                    , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
                from
                    equip_work_log2_header_moni
                where
                    mac_no={$mac_no} and plan_no='{$plan_no}' and koutei={$koutei}
    ";
    $res = array();
    if ( getResult($query, $res) <= 0) {
        $_SESSION['s_sysmsg'] = "{$mac_no}は運転開始されていません！";
    } else {
        $str_timestamp = $res[0]['str_timestamp'];
        $end_timestamp = $res[0]['end_timestamp'];
    }
    
    ////////////// 明細データの取得
    //                -- date_time >= (CURRENT_TIMESTAMP - interval '168 hours')      -- テスト用に残す(168=7日に型変換される)
    //                -- and date_time <= (CURRENT_TIMESTAMP - interval '0 hours')
    // TIMESTAMP型の場合は CAST しないと Seq Scan となるので注意  indexを使う場合は明示的に型変換が必要
    $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time $sort
                limit
                    $limit
                offset
                    $offset
    ";
    /*
    $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$mac_no}{$plan_no}{$koutei}00000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}99999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) $sort
                limit
                    $limit
                offset
                    $offset
    ";
    */
    $res = array();
    if ( ($rows=getResult2($query, $res)) <= 0) {
        if ($offset > 0) {
            $_SESSION['s_sysmsg'] = '次頁はありません！';
            $offset -= $limit;
            $_SESSION['equip_chart_offset'] -= $limit;
        } else {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>機械番号：{$mac_no}の稼動データがありません！</font>";
        }
    } else {
        $num = count($res[0]);
        for ($i=0; $i<$rows; $i++) {
            $hour   = substr($res[$i][1], 0, 2);
            $minute = substr($res[$i][1], 3, 2);
            $second = substr($res[$i][1], 6, 2);
            $month  = substr($res[$i][0], 5, 2);
            $day    = substr($res[$i][0], 8, 2);
            $year   = substr($res[$i][0], 0, 4);
            $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
            if (isset($timestamp_pre)) {
                $res[$i]['cycle'] = $timestamp - $timestamp_pre;
                if ($res[$i]['cycle'] < 0) $res[$i]['cycle'] = $res[$i]['cycle'] * (-1);
                $res[$i]['cycle_m'] = number_format((int)($res[$i]['cycle'] / 60)); // 分の整数部
                $odd = sprintf('%02d', $res[$i]['cycle'] % 60);                     // 秒の余り
                $res[$i]['cycle_m'] = ($res[$i]['cycle_m'] . ':' . $odd);           // 分：秒の形式へ
                $res[$i]['cycle'] = number_format($res[$i]['cycle']);
            }
            $timestamp_pre = $timestamp;
        }
    }
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php // if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
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
<form name='MainForm' action='<?php echo $menu->out_self() ?>' method='post'>
    <input type='hidden' name='select' value='GO'>
    <input type='hidden' name='sort' value='<?php echo $sort?>'>
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
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table class='item' width='80.0%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% class='winbox_field' border='1' cellspacing='1' cellpadding='1'>
        <?php
            for ($i=0; $i<$rows; $i++) {
                print("<tr class='table_font'>\n");
                print("<td class='winbox' align='center' nowrap width='10%' bgcolor='#d6d3ce'>" . ($i+1+$offset) . "</td>\n");
                for ($j=0; $j<$num; $j++) {
                    switch ($j) {
                    case 0:     // 年月日
                        print(" <td class='winbox' align='center' nowrap width='15%' bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
                        break;
                    case 1:     // 時分秒
                        print(" <td class='winbox' align='center' nowrap width='15%' bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
                        break;
                    case 2:     // 状態
                        $mac_state_txt = equip_machine_state($mac_no, $res[$i][$j], $bg_color, $txt_color);
                        print(" <td class='winbox' align='center' nowrap width='15%' bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
                        break;
                    case 3:     // 加工数
                        print(" <td class='winbox' align='right' nowrap width='15%' bgcolor='#d6d3ce'>" . number_format($res[$i][$j]) . "</td>\n");
                        break;
                    default:
                        if($res[$i][$j]=="")
                            print(" <td class='winbox' align='center' nowrap bgcolor='#d6d3ce'>-</td>\n");
                        else
                            print(" <td class='winbox' align='center' nowrap bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
                    }
                }
                if (isset($res[$i]['cycle'])) {
                    echo " <td class='winbox' align='right' nowrap width='20%' bgcolor='#d6d3ce'>{$res[$i]['cycle']}</td>\n";
                } else {
                    echo " <td class='winbox' align='center' nowrap width='20%' bgcolor='#d6d3ce'>-</td>\n";
                }
                if (isset($res[$i]['cycle_m'])) {
                    echo " <td class='winbox' align='right' nowrap width='10%' bgcolor='#d6d3ce'>{$res[$i]['cycle_m']}</td>\n";
                } else {
                    echo " <td class='winbox' align='center' nowrap width='10%' bgcolor='#d6d3ce'>-</td>\n";
                }
                print("</tr>\n");
            }
        ?>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
