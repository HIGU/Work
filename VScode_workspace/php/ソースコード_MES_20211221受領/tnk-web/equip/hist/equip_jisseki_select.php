<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理システムの実績照会 機械番号 選択フォーム                     //
// Copyright(C) 2003-2021 Kazuhiro.Kobayashi tnksys@nito-kohki.co.jp        //
// Changed history                                                          //
// 2003/05/13 Created   equip_jisseki_select.php                            //
// 2003/05/27 このスクリプト上で実績一覧も表示するように追加                //
// 2003/07/08 SQLで where work_flg='f' を work_flg is FALSE に変更          //
// 2004/02/07 selectした時に即実行するようにJavaScriptでロジック追加        //
// 2004/06/21 新版テーブル 全面改訂                                         //
// 2004/11/19 工場名をタイトルに付加 (2004/09/15に工場別対応済み)           //
// 2005/06/24 F2/F12キーで戻るための対応で JavaScriptの set_focus()を追加   //
// 2006/03/27 加工完了したものを取消すボタン(ロジック)を追加                //
// 2007/02/02 PostgreSQL8.2.X により SQL文の '\'YY → 'YY エスケープ中止    //
// 2007/09/26 E_ALL → E_ALL | E_STRICT  へ変更                             //
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
// 2021/06/22 ７工場を真鍮とSUSに分離                                  大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共有 function
require_once ('../../tnk_func.php');        // TNK 依存 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 6);                     // site_index=40(設備メニュー) site_id=10(実績ロット選択)

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    $factory = '';
}
///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
if ($factory == '') {
    $factory = @$_SESSION['factory'];
}
switch ($factory) {
case 1:
    $fact_name = '１工場';
    break;
case 2:
    $fact_name = '２工場';
    break;
case 4:
    $fact_name = '４工場';
    break;
case 5:
    $fact_name = '５工場';
    break;
case 6:
    $fact_name = '６工場';
    break;
case 7:
    $fact_name = '７工場(真鍮)';
    break;
case 8:
    $fact_name = '７工場(SUS)';
    break;
default:
    $fact_name = '全工場';
    break;
}

////////////// リターンアドレス設定
// $menu->set_RetUrl(EQUIP2_MENU);
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("加 工 実 績 照 会&nbsp;&nbsp;{$fact_name}");
//////////// 表題の設定
$menu->set_caption('指示番号別 加工実績一覧');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('旧分岐処理',   EQUIP2 . 'hist/equip_branch_msg.php');     // 旧タイプを残す
$menu->set_action('稼動実績表旧', EQUIP2 . 'hist/equip_chart_detail.php');   // 旧タイプを残す
$menu->set_action('実績集計表旧', EQUIP2 . 'hist/equip_chart_summary.php');  // 旧タイプを残す
// $menu->set_action('実績グラフ旧', EQUIP2 . 'equip_graph_shiji_no.php'); // 旧タイプを残す
$menu->set_action('グラフlot',     EQUIP2 . 'hist/equip_hist_graph.php');
// $menu->set_action('グラフ24旧',   EQUIP2 . 'equip_graph_shiji_no.php'); // 旧タイプを残す
$menu->set_action('グラフ24',     EQUIP2 . 'hist/equip_hist_graph.php');
$menu->set_action('稼動実績表',   EQUIP2 . 'hist/equip_chart.php');


//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 機械マスターから設備番号・設備名のリストを取得
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    , mac_name              AS mac_name
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
                    , mac_name              AS mac_name
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
} else {
    $mac_no_name = array();
    for ($i=0; $i<$rows; $i++) {
        $mac_no_name[$i] = $res[$i]['mac_no'] . " " . trim($res[$i]['mac_name']);   // 機械番号と名称の間にスペース追加
    }
}

//////////// 一頁の行数
define('PAGE', '20');

if (isset($_REQUEST['page_keep'])) {
    $_REQUEST['mac_no'] = $_SESSION['mac_no'];
    $page_keep = $_REQUEST['page_keep'];
}

if (isset($_REQUEST['kancancel'])) {
    $update_sql = "UPDATE equip_work_log2_header SET end_timestamp=NULL, work_flg=true WHERE mac_no={$_REQUEST['mac_no']} AND siji_no={$_REQUEST['siji_no']} AND koutei={$_REQUEST['koutei']}";
    if (query_affected($update_sql) >= 1) {
        $_SESSION['s_sysmsg'] = '完了を取消しました。';
    } else {
        $_SESSION['s_sysmsg'] = '完了の取消が出来ませんでした！ 管理担当者へ連絡して下さい。';
    }
}

//////////// POST 時のmac_no を保存
if (isset($_REQUEST['mac_no'])) {
    $mac_no = $_REQUEST['mac_no'];  // 下で if 文で使用するため保存 以下の文脈でunsetの可能性があるため
    
    //////////// 最大レコード数取得
    $query = "select count(*) from equip_work_log2_header where mac_no=$mac_no and work_flg IS FALSE and end_timestamp is not null";
    if ( getUniResult($query, $maxrows) <= 0) {
        $_SESSION['s_sysmsg'] .= '最大レコード数の取得に失敗';
    }
    //////////// ページオフセット設定
    if ( isset($_POST['forward']) ) {
        $_SESSION['ej_offset'] += PAGE;
        if ($_SESSION['ej_offset'] >= $maxrows) {
            $_SESSION['ej_offset'] -= PAGE;
            if ($_SESSION['s_sysmsg'] == "") {
                $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません!</font>";
            } else {
                $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません!</font>";
            }
        }
    } elseif ( isset($_POST['backward']) ) {
        $_SESSION['ej_offset'] -= PAGE;
        if ($_SESSION['ej_offset'] < 0) {
            $_SESSION['ej_offset'] = 0;
            if ($_SESSION['s_sysmsg'] == "") {
                $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません!</font>";
            } else {
                $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません!</font>";
            }
        }
    } elseif ( isset($page_keep) ) {       // 現在のページを維持する
        $offset = $_SESSION['ej_offset'];
    } else {
        $_SESSION['ej_offset'] = 0;
    }
    $offset = $_SESSION['ej_offset'];
} else {
    $mac_no = "";
}
//////////// 機械番号から実績取得
if (isset($_REQUEST['mac_no'])) {
    $query = sprintf("select 
                            siji_no as \"指示No\",
                            parts_no as 部品番号,
                            midsc as 部品名,
                            koutei as 工程,
                            plan_cnt as 計画数,
                            jisseki as 実績数,
                            to_char(str_timestamp, 'YY/MM/DD HH24:MI') as 開始日,
                            to_char(end_timestamp, 'YY/MM/DD HH24:MI') as 終了日
                            -- to_char(str_timestamp, '\'YY/MM/DD HH24:MI:SS') as 開始日,
                            -- to_char(end_timestamp, '\'YY/MM/DD HH24:MI:SS') as 終了日
                        from
                            equip_work_log2_header
                            left outer join
                            miitem
                            on parts_no=mipn
                        where
                            mac_no=%s and
                            work_flg is FALSE and
                            end_timestamp is not null
                        order by end_timestamp DESC
                        limit %d
                        offset %d", $mac_no, PAGE, $offset);
    $res_j = array();
    $field = array();
    if (($rows_j = getResultWithField2($query, $field, $res_j)) <= 0) {
        $_SESSION['s_sysmsg'] = sprintf("<font color='yellow'>機械番号:%s の<br>実績データがありません。</font>", $mac_no);
        unset($_REQUEST['mac_no']);
    } else {
        $num = count($field);       // フィールド数取得
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
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.mac_form.mac_no.focus();
//    document.mac_form.mac_no.select();
}

/* selectを変更したときに即実行 */
function select_send(obj)
{
    // location.href = obj.options[obj.selectedIndex].value;
    // document.mac_form.action = '<?=$menu->out_self()?>';
    document.mac_form.submit();
}
// -->
</script>

    <!-- ファイル指定の場合 -->
<link rel='stylesheet' href='../equipment.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<style type="text/css">
<!--
th {
    background-color:yellow;
    color:          blue;
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
td.gb {
    background-color:#d6d3ce;
    color:black;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table border='0' cellspacing='5' cellpadding='0'>
            <form name='mac_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td class='pt11b'>機械番号を選択して下さい</td>
                    <td align='center'>
                        <select name='mac_no' class='pt12b' onChange='document.mac_form.submit()'>
                        <?php
                            for ($j=0; $j<$rows; $j++) {
                                if ($mac_no == $res[$j]['mac_no']) {
                                    printf("<option value='%s' selected>%s</option>\n", $res[$j]['mac_no'], $mac_no_name[$j]);
                                } else {
                                    printf("<option value='%s'>%s</option>\n", $res[$j]['mac_no'], $mac_no_name[$j]);
                                }
                            }
                            if ($rows == 0) {
                                echo "<option value='00000'>登録なし</option>\n";
                            }
                        ?>
                        </select>
                        <input type='submit' name='select' value='実行'>
                    </td>
                </tr>
            </form>
        </table>
    <?php if (isset($_REQUEST['mac_no'])) { ?>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <form name='mac_page_form' method='post' action='<?= $menu->out_self() ?>'>
                <td align='left'>
                    <table align='left' border='3' cellspacing='0' cellpadding='0'>
                        <td align='left'>
                            <input class='pt10b' type='submit' name='backward' value='前頁'>
                            <input type='hidden' name='mac_no' value='<?= $mac_no ?>'>
                        </td>
                    </table>
                </td>
                <td align='center' class='pt12b'>
                    <?= $menu->out_caption() ?>
                </td>
                <td align='right'>
                    <table align='right' border='3' cellspacing='0' cellpadding='0'>
                        <td align='right'>
                            <input class='pt10b' type='submit' name='forward' value='次頁'>
                            <input type='hidden' name='mac_no' value='<?= $mac_no ?>'>
                        </td>
                    </table>
                </td>
                </form>
            </tr>
        </table>
    <?php } ?>
        <?php
            if (isset($_REQUEST['mac_no'])) {
                echo "<table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>\n";
                echo "    <tr><td> <!-- ダミー(デザイン用) -->\n";
                echo "<table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>\n";
                for ($n=0; $n<$num; $n++){
                    if ($n == 0) {
                        echo "<th nowrap>No</th>\n";
                        echo "<th nowrap>Graph</th>\n";
                        echo "<th nowrap>表形式</th>\n";
                    }
                    echo "<th nowrap>" . $field[$n] . "</th>\n";
                }
                echo "<th nowrap>完了</th>\n";
                for ($r=0; $r<$rows_j; $r++){
                    echo "<tr class='pt11'>\n";
                    echo "<td class='gb' nowrap align='center'>" . ($r+1+$offset) . "</td>\n";
                    echo "<form method='post' action='", $menu->out_action('旧分岐処理'), "'>\n";
                    echo "<td class='gb' nowrap align='center'>\n";
                        echo "<input type='submit' name='graph_24' value='24Hr'>\n";
                        echo "<input type='submit' name='graph_lot' value='全体'>\n";
                        echo "<input type='hidden' name='script_graph_24' value='", $menu->out_action('グラフ24'), "?hist=24'>\n";
                        echo "<input type='hidden' name='script_graph_lot' value='", $menu->out_action('グラフlot'), "?hist=max'>\n";
                        echo "<input type='hidden' name='mac_no' value='{$mac_no}'>\n";
                        echo "<input type='hidden' name='siji_no' value='{$res_j[$r][0]}'>\n";
                        echo "<input type='hidden' name='parts_no' value='{$res_j[$r][1]}'>\n";
                        echo "<input type='hidden' name='koutei' value='{$res_j[$r][3]}'>\n";
                    echo "</td>\n";
                    echo "<td class='gb' nowrap align='center'>\n";
                        echo "<input type='submit' name='detail' value='明細'>\n";
                        echo "<input type='submit' name='summary' value='集計'>\n";
                        echo "<input type='hidden' name='script_detail' value='", $menu->out_action('稼動実績表'), "'>\n";
                        echo "<input type='hidden' name='script_summary' value='", $menu->out_action('実績集計表旧'), "'>\n";
                    echo "</td>\n";
                    echo "</form>\n";
                    for ($n=0; $n<$num; $n++){
                        if ($res_j[$r][$n] == "")
                            echo "<td class='gb' nowrap align='center'>---</td>\n";
                        elseif ($n == 2)
                            echo "<td class='gb' nowrap>", mb_substr($res_j[$r][$n], 0, 14), "</td>\n";
                        elseif ($n == 4 || ($n == 5))
                            echo "<td class='gb' align='right' nowrap>", number_format($res_j[$r][$n]), "</td>\n";
                        elseif (($n == 6) || ($n == 7))
                            echo "<td class='gb' nowrap align='center'>", $res_j[$r][$n], "</td>\n";
                        else
                            echo "<td class='gb' align='right' nowrap>", $res_j[$r][$n], "</td>\n";
                    }
                    if ($r == 0 && $offset == 0) {
                        $query = "SELECT siji_no FROM equip_work_log2_header WHERE mac_no={$mac_no} AND work_flg IS TRUE";
                        if (getResult2($query, $temp) <= 0) {
                            ///// end_timestamp IS NOT NULL は中断計画の対応のため
                            $query = "SELECT siji_no, koutei FROM equip_work_log2_header WHERE mac_no={$mac_no} AND end_timestamp IS NOT NULL ORDER BY mac_no DESC, end_timestamp DESC LIMIT 1";
                            if (getResult2($query, $temp) > 0) {
                                if ($temp[0][0] == $res_j[$r][0] && $temp[0][1] == $res_j[$r][3]) {
                                    ///// 完了の取消を実行する
                                    echo "<td align='center'><input type='button' name='cancel' value='取消' onClick='location.replace(\"{$menu->out_self()}?mac_no={$mac_no}&siji_no={$res_j[$r][0]}&koutei={$res_j[$r][3]}&kancancel=go\")'></td>\n";
                                } else {
                                    echo "<td align='center'><input type='button' name='cancel' value='取消' disabled></td>\n";
                                }
                            } else {
                                echo "<td align='center'><input type='button' name='cancel' value='取消' disabled></td>\n";
                            }
                        } else {
                            echo "<td align='center'><input type='button' name='cancel' value='取消' disabled></td>\n";
                        }
                    } else {
                        echo "<td align='center'><input type='button' name='cancel' value='取消' disabled></td>\n";
                    }
                    echo("</tr>\n");
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!-- ダミーEnd -->\n";
            }
        ?>
    </center>
</body>
</html>
<?php
echo $menu->out_alert_java();
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
