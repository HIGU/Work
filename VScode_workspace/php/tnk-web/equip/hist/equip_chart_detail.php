<?php
//////////////////////////////////////////////////////////////////////////////
// 機械運転(製造用) 加工実績より明細表 表示                                 //
// Copyright(C) 2003-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2003/06/27 equip_chart_detail.php 新規作成                               //
// 2003/07/10 次頁・前頁のロジック変更しメッセージを出力する                //
// 2004/06/21 新版テーブルへ全面改訂                                        //
// 2004/06/25 最大レコード数[count(*)]を使わずに頁制御するように変更        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');               //Warning: Cannot add header の対策のため追加。
require_once ("equip_function.php");
// $sysmsg = $_SESSION["s_sysmsg"];
// $_SESSION["s_sysmsg"] = NULL;
access_log();                           // Script Name は自動取得
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
$url_referer     = $_SESSION['equip_referer'];     // 分岐処理前に保存されている呼出元をセットする
// $url_referer     = $_SERVER["HTTP_REFERER"];    // 呼出もとのURLを保存 前のスクリプトで分岐処理をしている場合は使用しない

//////////// 認証チェック
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
    $_SESSION["s_sysmsg"] = "認証されていないか認証期限が切れました。Loginしなおして下さい。";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

//////////// セッション情報に指定された条件が保存されているかチェック
if ( !isset($_SESSION['mac_no']) || !isset($_SESSION['siji_no']) || !isset($_SESSION['koutei']) ) {
    $_SESSION['s_sysmsg'] = "機械No/指示No/工程が指定されていません!";
    header("Location: $url_referer");
    exit();
} else {
    $mac_no   = $_SESSION['mac_no'];
    $siji_no  = $_SESSION['siji_no'];
    $parts_no = $_SESSION['parts_no'];
    $koutei   = $_SESSION['koutei'];
    /********* // 次頁・前頁で使用するためコメント
    unset($_SESSION['mac_no']);
    unset($_SESSION['siji_no']);
    unset($_SESSION['parts_no']);
    unset($_SESSION['koutei']);
    *********/
}

/********** Logic Start **********/
//////////// タイトルの日付・時間設定
$today = date('Y/m/d H:i:s');

//////////////// 機械マスターから機械名を取得
$query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
if (getUniResult($query, $mac_name) <= 0) {
    $mac_name = '　';   // error時は機械名をブランク
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu_title = $mac_no . ' ' . $mac_name . " 加工 実績 明細 表示２\n";
//////////// 表題の設定
$caption    = '';

////////////// １ページの表示行数
$disp_rows = 24;

//////////// 一頁の行数
// define('PAGE', 24);

//////////// 最大レコード数取得
/********************************
$query  = "select count(*) from equip_work_log2 ";
$query .= "where mac_no=$mac_no and siji_no=$siji_no and koutei=$koutei";
if ( getUniResult($query, $maxrows) <= 0) {
    $_SESSION['s_sysmsg'] = "最大レコード数の取得に失敗";
    header("Location: $url_referer");
    exit();
}
********************************/

//////////// ヘッダーファイルから計画数取得 & 開始日時の取得
$query = "select plan_cnt
                , jisseki
                , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
                , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
            from
                equip_work_log2_header
            where
                mac_no=$mac_no and siji_no=$siji_no and koutei=$koutei
        ";
if ( ($rows=getResult2($query, $res_head)) <= 0) {
    $_SESSION['s_sysmsg'] = '開始日時の取得に失敗';
    $_SESSION['s_sysmsg'] .= "<br>{$query}";    // debug用
    header("Location: $url_referer");
    exit();
} else {
    $plan_cnt = $res_head[0][0];
    $jisseki  = $res_head[0][1];
    $str_timestamp = $res_head[0][2];
    $end_timestamp = $res_head[0][3];
}

$page_up_flg = true;    // 次頁ボタン制御用

if (isset($_POST['page_up'])) {
    if (isset($_SESSION['equip_maxrows'])) {
        $maxrows = $_SESSION['equip_maxrows'];
    } else {
        $maxrows = $disp_rows ;    // 次頁のために
    }
    $_SESSION["s_offset"] += $disp_rows;
    if ($_SESSION['s_offset'] > $maxrows) {
        $_SESSION['s_offset'] -= $disp_rows;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません!</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません!</font>";
        }
    }
} elseif (isset($_POST['page_down'])) {
    $_SESSION["s_offset"] -= $disp_rows;
    if ($_SESSION["s_offset"] < 0) {
        $_SESSION["s_offset"] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません!</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません!</font>";
        }
    }
} else {
    $_SESSION['s_offset'] = 0;
    $_SESSION['equip_maxrows'] = $disp_rows;  // 初回のみ初期化
}
$offset = $_SESSION['s_offset'];

//////////// アイテムマスターから部品名取得
$query = "select midsc,mzist from miitem where mipn='$parts_no'";
$res = array();
if ( ($rows=getResult2($query,$res)) >= 1) {        // 部品名取得
    $buhin_name    = mb_substr($res[0][0],0,10);
    $buhin_zaisitu = mb_substr($res[0][1],0,7);
} else {
    $buhin_name    = '';
    $buhin_zaisitu = '';
}

////////////// 明細データの取得
//                -- date_time >= (CURRENT_TIMESTAMP - interval '168 hours')      -- テスト用に残す(168=7日に型変換される)
//                -- and date_time <= (CURRENT_TIMESTAMP - interval '0 hours')
// TIMESTAMP型の場合は CAST しないと Seq Scan となるので注意  indexを使う場合は明示的に型変換が必要
$query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD')
                ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS')
                ,mac_state
                ,work_cnt
            from
                equip_work_log2
            where
                date_time >= CAST('$str_timestamp' as TIMESTAMP)
                and date_time <= CAST('$end_timestamp' as TIMESTAMP)
                and mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
            order by
                date_time ASC
            limit
                $disp_rows
            offset
                $offset
        ";
$res = array();
if ( ($rows=getResult2($query,$res)) <= 0) {
    if (isset($_POST['page_up'])) {
        $_SESSION['equip_maxrows'] -= $disp_rows;  // 次頁が無いため 1頁分マイナス
        $page_up_flg = false;
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません!</font>";
    } else {
        $_SESSION['s_sysmsg'] = "equip_work_log2 の明細 取得に失敗";
        header("Location: $url_referer");
        exit();
    }
} else {
    $num = count($res[0]);
    if ($rows == $disp_rows) {
        $_SESSION['equip_maxrows'] += $rows;  // 次の頁のために+
    } else {
        $_SESSION['equip_maxrows'] = ($rows + $offset); // 実際のレコード数を入れる
    }
}


/********** Logic End   **********/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>機械運転状況表示(製造用)</TITLE>
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
    font:bold 11.5pt;
    font-family: monospace;
}
th {
    font:bold 11.5pt;
    font-family: monospace;
}
.table_font {
    font: 11.9pt;
    font-family: monospace;
}
.pick_font {
    font:bold 8.5pt;
    font-family: monospace;
}
th {
    font:bold 12.0pt;
    font-family: monospace;
}
.ext_font {
    background-color:blue;
    color:yellow;
    font:bold 12.0pt;
    font-family: monospace;
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
select      {background-color:teal; color:white;}
textarea        {background-color:black; color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
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
                        <input type='hidden' name='mac_no' value='<?php echo $mac_no ?>'>
                        <input type='hidden' name='page_keep' value='ページ維持'>
                    </td>
                </form>
                <td colspan='1' bgcolor='#d6d3ce' align='center' class='title_font'>
                    <?= $menu_title ?>
                </td>
                <td colspan='1' bgcolor='#d6d3ce' align='center' width='140' class='today_font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        <hr color='797979'>

        <!----------------- 見出しを表示 ------------------------>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <form method='post' action='<?php echo $current_script ?>'>
                    <td width='52' bgcolor='green'align='center' valign='center'>
                        <input class='pt11b' type='submit' name='page_down' value='前頁'>
                    </td>
                </form>
                <?php if ($page_up_flg) { ?>
                <form method='post' action='<?php echo $current_script ?>'>
                <?php } ?>
                    <td width='52' bgcolor='green'align='center' valign='center'>
                        <input class='pt11b' type='submit' name='page_up' value='次頁'>
                    </td>
                <?php if ($page_up_flg) { ?>
                </form>
                <?php } ?>
                <td align='center' nowrap>部品No</td>
                <td align='center' nowrap><?php echo $parts_no ?></td>
                <td align='center' nowrap>部品名</td>
                <td class='pick_font' align='center' nowrap><?php echo $buhin_name ?></td>
                <td align='center' nowrap>材質</td>
                <td class='pick_font' align='center' nowrap><?php echo $buhin_zaisitu ?></td>
                <td align='center' nowrap>指示No</td>
                <td align='center' nowrap><?php echo $siji_no ?></td>
                <td align='center' nowrap>工程</td>
                <td align='center' nowrap><?php echo $koutei ?></td>
                <td align='center' nowrap>計画数</td>
                <td align='center' nowrap><?php echo number_format($plan_cnt) ?></td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        <hr color='797979'>

        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% align='center' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='1'>
            <th nowrap>No</th>
                <!-- <th nowrap width='80'>機械No</th> -->
            <th nowrap width='100'>年月日</th>
            <th nowrap width='100'>時分秒</th>
                <!-- <th nowrap width='80'>型 式</th> -->
            <th nowrap width='100'>状態</th>
            <th nowrap width='80'>加工数</th>
                <!--
                <th nowrap>変数1</th> <th nowrap>変数2</th>
                <th nowrap>変数3</th> <th nowrap>変数4</th> <th nowrap>変数5</th>
                -->
<?php
    for ($i=0; $i<$rows; $i++) {
        print("<tr class='table_font'>\n");
        print("<td align='center' nowrap bgcolor='#d6d3ce'>" . ($i+1+$_SESSION["s_offset"]) . "</td>\n");
        for ($j=0; $j<$num; $j++) {
            switch ($j) {
            case 0:
                echo " <td align='center' nowrap bgcolor='#d6d3ce'>", $res[$i][$j], "</td>\n";
                break;
            case 1:
                echo " <td align='center' nowrap bgcolor='#d6d3ce'>", $res[$i][$j], "</td>\n";
                // echo " <td align='center' nowrap bgcolor='#d6d3ce'>-</td>\n";
                break;
            case 2:
                $mac_state_txt = equip_machine_state($mac_no, $res[$i][$j], $bg_color, $txt_color);
                echo " <td align='center' nowrap bgcolor='$bg_color'><font color='$txt_color'>$mac_state_txt</font></td>\n";
                break;
            case 3:
                echo " <td align='right' nowrap bgcolor='#d6d3ce'>", number_format($res[$i][$j]), "</td>\n";
                break;
            default:
                if($res[$i][$j]=='')
                    echo " <td align='center' nowrap bgcolor='#d6d3ce'>-</td>\n";
                else
                    echo " <td align='center' nowrap bgcolor='#d6d3ce'>{$res[$i][$j]}</td>\n";
            }
        }
        echo "</tr>\n";
    }
    echo "</table>\n";
    echo "    </td></tr>\n";
    echo "</table>\n";
?>
        <!--
        <table align='center' with=100% border='2' cellspacing='0' cellpadding='0'>
            <form method='post' action='equipment_working_disp.php'>
                <td>
                    <input type='submit' name='return' value='戻る'>
                    <input type='hidden' name='mac_no' value='<?php echo $mac_no ?>'>
                    <input type='hidden' name='page_keep' value='ページ維持'>
                </td>
            </form>
        </table>
        -->
    </center>
</BODY>
</HTML>
<?php
ob_end_flush();  //Warning: Cannot add header の対策のため追加。
?>
