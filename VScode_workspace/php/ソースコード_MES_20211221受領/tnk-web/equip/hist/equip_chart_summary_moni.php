<?php
//////////////////////////////////////////////////////////////////////////////
// 組立設備稼働管理システムの加工実績より機械状態の集計表の作成・照会       //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_chart_summary_moni.php                         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮

require_once ('../equip_function.php');     // 設備稼動管理 専用
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
// $menu->set_site(40, 6);                     // site_index=40(設備メニュー2) site_id=999(siteを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('組立実績より集計表の照会');
//////////// 表題の設定
$menu->set_caption('計画番号単位の集計表');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid("target");

//////////// 対照データ絞込み用 検索値 取得
$mac_no   = $_SESSION['mac_no']  ;
$plan_no  = $_SESSION['plan_no'] ;
$parts_no = $_SESSION['parts_no'];
$koutei   = $_SESSION['koutei']  ;

////////////// 戻先に渡すパラメーター設定
// $menu->set_retGET('page_keep', 'on');   // name value の順で設定
// $menu->set_retGET('mac_no', $mac_no);   // name value の順で設定
$menu->set_retPOST('page_keep', 'on');   // name value の順で設定
$menu->set_retPOST('mac_no', $mac_no);   // name value の順で設定

//////////// SQL 文の where 句を 共用する
$search = "where mac_no=$mac_no and plan_no='$plan_no' and koutei=$koutei";

//////////// 一頁の行数
define('PAGE', '20');

//////////// 最大レコード数取得     (対照データの最大数をページ制御に使用)
$query = "select mac_no
            from
                equip_state_summary2_moni
            where
                mac_no=$mac_no and plan_no='$plan_no' and koutei=$koutei
        ";
$res_chk = array();
if ( ($maxrows = getResult2($query, $res_chk)) <= 0) {         // $maxrows の取得
    ////////// データ未集計のため集計開始
    $query = "select EXTRACT(EPOCH FROM date_time) as date_time
                    , mac_state
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time ASC
            ";
    /*
    $query = "select EXTRACT(EPOCH FROM date_time) as date_time
                    , mac_state
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                    and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
            ";
    */
    $rows = getResult($query, $res);
    $rui_state = array();                       // 累積用 変数 初期化
    for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
        $rui_state[$i] = 0;                     // 各要素の初期化
    }
    for ($r=1; $r<$rows; $r++) {                // 各状態毎の累積時間を算出
        for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
            if ($res[$r-1]['mac_state'] == $i) {     // 状態が変化した時のレコードの一つ前のレコードを使用
                $rui_state[$i] += ($res[$r]['date_time'] - $res[$r-1]['date_time']);
            }
        }
    }
    for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
        $state_name[$i] = equip_machine_state($mac_no, $i, $tmp_bg, $tmp_txt);   // 状態の名称の取得
        if ($rui_state[$i] <= 0) {
            continue;
        }
        $rui_state[$i] = Uround($rui_state[$i]/60,0);           // 秒を分へ変更
    }
    /////////// 機械マスターから状態テーブル方式の取得
    $query = "select csv_flg from equip_machine_master2 where mac_no=$mac_no";
    if (getUniResult($query, $state_type) <= 0) {
        $_SESSION["s_sysmsg"] .= "機械マスターから状態タイプの取得に失敗";
        header('Location: ' . H_WEB_HOST . $menu->RetUrl());                   // 直前の呼出元へ戻る
        exit();             ///// $state_type は以下で Netmoni or ロータリースイッチ方式等の切替で使用
    }
    /////////// begin トランザクション開始
    if ($con = funcConnect()) {
        query_affected_trans($con, 'begin');
        for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
            $query = sprintf("insert into equip_state_summary2_moni
                            (mac_no, plan_no, parts_no, koutei, state, total_time, state_name, state_type)
                            values(%s, '%s', '%s', %s, %d, %d, '%s', $state_type)",
                            $mac_no, $plan_no, $parts_no, $koutei, $i, $rui_state[$i], $state_name[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("集計結果の登録に失敗 機械番号：%s 計画番号：%s", $mac_no, $plan_no);
                query_affected_trans($con, 'rollback');         // transaction rollback
                header('Location: ' . H_WEB_HOST . $menu->RetUrl());               // 直前の呼出元へ戻る
                exit();
            }
        }
    } else {
        $_SESSION['s_sysmsg'] .= "データベースに接続できません";
        header('Location: ' . H_WEB_HOST . $menu->RetUrl());                   // 直前の呼出元へ戻る
        exit();
    }
    /////////// commit トランザクション終了
    query_affected_trans($con, 'commit');
}
//////////// ページオフセット設定
if ( isset($_POST['forward']) ) {                       // 次頁が押された
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません!</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません!</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // 次頁が押された
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません!</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません!</font>";
        }
    }
} elseif ( isset($_POST['page_keep']) ) {               // 現在のページを維持する
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['offset'];

//////////// ヘッダーファイルから計画数取得
$query = "select plan_cnt
                , jisseki
            from
                equip_work_log2_header_moni
            where
                mac_no=$mac_no and plan_no='$plan_no' and koutei=$koutei";
if ( ($rows=getResult2($query, $res_head)) <= 0) {
    $plan_cnt = "";                                  // 計画数取得に失敗
    $jisseki  = "";                                  // 計画数取得に失敗
} else {
    $plan_cnt = $res_head[0][0];
    $jisseki  = $res_head[0][1];
}

/////////////// 明細表(詳細表示)作成のためのデータ取得 開始時の１レコード
$query = "select mac_no
                , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                , mac_state
                , work_cnt
            from
                equip_work_log2_moni
            where
                plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
            order by
                date_time ASC
            limit 1
        ";
/*
$query = "select mac_no
                , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                , mac_state
                , work_cnt
            from
                equip_work_log2_moni
            where
                equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
            order by
                equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
            limit 1
        ";
*/
$res_str = array();
if ( ($rows=getResult($query, $res_str)) <= 0) {
    $_SESSION['s_sysmsg'] = "機械No：$mac_no 計画No：$plan_no 工程：$koutei の明細がありません。";
    header('Location: ' . H_WEB_HOST . $menu->RetUrl());           // 直前の呼出元へ戻る
    exit();
}

/////////////// 明細表(詳細表示)作成のためのデータ取得 終了時の１レコード
$query = "select mac_no
                , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                , mac_state
                , work_cnt
            from
                equip_work_log2_moni
            where
                plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
            order by
                date_time DESC
            limit 1
        ";
/*
$query = "select mac_no
                , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                , mac_state
                , work_cnt
            from
                equip_work_log2_moni
            where
                equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
            order by
                equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
            limit 1
        ";
*/
$res_end = array();
if ( ($rows=getResult($query, $res_end)) <= 0) {
    $_SESSION['s_sysmsg'] = "機械No：$mac_no 計画No：$plan_no 工程：$koutei の明細がありません。";
    header('Location: ' . H_WEB_HOST . $menu->RetUrl());           // 直前の呼出元へ戻る
    exit();
} else {
    $res_end[0]['work_cnt'] = $jisseki;          // work_cnt に実績数を強制的に入れる!
}

//////////////// 機械マスターから機械名を取得
$query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
if (getUniResult($query, $mac_name) <= 0) {
    $mac_name = '　';   // error時は機械名をブランク
}

//////////// アイテムマスターから部品名取得
$query = "select midsc,mzist from miitem where mipn='$parts_no'";
$res = array();
if ( ($rows=getResult2($query,$res)) >= 1) {        // 部品名取得
    $buhin_name    = mb_substr($res[0][0],0,10);
    $buhin_zaisitu = mb_substr($res[0][1],0,7);
} else {
    $buhin_name    = "";
    $buhin_zaisitu = "";
}

//////////// equip_state_summary テーブルから集計結果取得
$query = "select state as 状態番号
                , state_name as 運転状況
                , total_time as 時間（分）
                , state_type as 状態テーブル
            from
                equip_state_summary2_moni
            where
                mac_no=$mac_no and plan_no='$plan_no' and koutei=$koutei
            order by
                state ASC
        ";
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("集計ファイルにデータがありません。機械番号：%s 計画番号：%s 工程：%s", $mac_no, $plan_no, $koutei);
    header('Location: ' . H_WEB_HOST . $menu->RetUrl());                   // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
    /////////////// 合計時間等の計算
    $time_all_sum = 0;                  // 総合計時間
    $time_ope_sum = 0;                  // 電源OFFを除く合計時間
    $time_act_sum = 0;                  // 段取中＋自動運転＋無人運転
    $time_sto_sum = 0;                  // 電源OFF以外の停止時間
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r]['運転状況'] == "中 断") {
            continue;       ///// 中断は全てカットする
        }
        $time_all_sum += $res[$r]['時間（分）'];
        if ($res[$r]['運転状況'] != "電源OFF") {
            $time_ope_sum += $res[$r]['時間（分）'];
        }
        if ( ($res[$r]['運転状況'] == "段取中") || ($res[$r]['運転状況'] == "自動運転") || ($res[$r]['運転状況'] == "無人運転") ) {
            $time_act_sum += $res[$r]['時間（分）'];
        }
        if ( ($res[$r]['運転状況'] != "段取中") && ($res[$r]['運転状況'] != "自動運転") && ($res[$r]['運転状況'] != "無人運転") && ($res[$r]['運転状況'] != "電源OFF") ) {
            $time_sto_sum += $res[$r]['時間（分）'];
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
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>

    <!-- ファイル指定の場合 -->
<link rel='stylesheet' href='../equipment.css?<?= $uniq ?>' type='text/css' media='screen'>

<style type="text/css">     <!-- ローカル指定 -->
<!--
th {
    background-color:yellow;
    color:blue;
    font:bold 11pt;
    font-family: monospace;
}
.table_font {
    font: 11.5pt;
    font-family: monospace;
}
.ext_font {
    background-color:blue;
    color:yellow;
    font:bold 12.0pt;
    font-family: monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------- ここは 部品名・材質等の見出し ------------->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='1' border='1'>
            <tr class='sub_font'>
                <!--
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td width='52' bgcolor='green'align='center' valign='center'>
                        <input class='pt11b' type='submit' name='backward' value='前頁'>
                    </td>
                </form>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td width='52' bgcolor='green'align='center' valign='center'>
                        <input class='pt11b' type='submit' name='forward' value='次頁'>
                    </td>
                </form>
                -->
                <td align='center' nowrap>製品No</td>
                <td align='center' nowrap><?php echo $parts_no ?></td>
                <td align='center' nowrap>製品名</td>
                <td class='pick_font' align='center' nowrap><?php echo $buhin_name ?></td>
                <td align='center' nowrap>材質</td>
                <td class='pick_font' align='center' nowrap><?php echo $buhin_zaisitu ?></td>
                <td align='center' nowrap>計画No</td>
                <td align='center' nowrap><?php echo $plan_no ?></td>
                <td align='center' nowrap>工程</td>
                <td align='center' nowrap><?php echo $koutei ?></td>
                <td align='center' nowrap>計画数</td>
                <td align='right'  nowrap><?php echo number_format($plan_cnt) ?></td>
            </tr>
        </table>
        
        <!-- <hr color='797979'> -->
        
        <!--------------- ここから詳細表 見出し ２行を表示する -------------------->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='3' cellspacing='0' cellpadding='2'>
            <tr>
                <th nowrap>　</th>
                <th nowrap>機械No</th><th nowrap>年月日</th><th nowrap>時分秒</th><th nowrap>型 式</th>
                <th nowrap>状態</th><th nowrap>加工数</th><th nowrap>備考1</th><th nowrap>備考2</th>
                <th nowrap>備考3</th><th nowrap>備考4</th><th nowrap>備考5</th>
            </tr>
            <tr class='table_font'>
                <td class='ext_font' align='center' nowrap>開始</td>
                <td align='center' nowrap><?php echo $res_str[0]['mac_no'] ?></td>
                <td align='center' nowrap><?php echo $res_str[0]['date'] ?></td>
                <td align='center' nowrap><?php echo $res_str[0]['time'] ?></td>
                <td align='center' nowrap><?php echo $mac_name ?></td>
                <?php
                $mac_state_txt = equip_machine_state($mac_no, $res_str[0]['mac_state'],$bg_color,$txt_color);
                print(" <td align='center' nowrap bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
                ?>
                <td align='right' nowrap><?php echo number_format($res_str[0]['work_cnt']) ?></td>
                <?php
                for ($i=5; $i<=9; $i++) {
                    echo " <td align='center' nowrap>-</td>\n";
                }
                ?>
            </tr>
            <tr class='table_font'>
                <td class='ext_font' align='center' nowrap>終了</td>
                <td align='center' nowrap><?php echo $res_end[0]['mac_no'] ?></td>
                <td align='center' nowrap><?php echo $res_end[0]['date'] ?></td>
                <td align='center' nowrap><?php echo $res_end[0]['time'] ?></td>
                <td align='center' nowrap><?php echo $mac_name ?></td>
                <?php
                $mac_state_txt = equip_machine_state($mac_no, $res_end[0]['mac_state'],$bg_color,$txt_color);
                print(" <td align='center' nowrap bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
                ?>
                <td align='right' nowrap><?php echo number_format($res_end[0]['work_cnt']) ?></td>
                <?php
                for ($i=5; $i<=9; $i++) {
                    echo " <td align='center' nowrap>-</td>\n";
                }
                ?>
            </tr>
        </table>
        
        <hr color='797979'>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                ?>
                    <th><?php echo $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='pt11b' align='right'><?php echo ($r + $offset + 1) ?></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        if ($i == 0) {                      // 状況番号
                            echo "<td align='center' class='pt12b' bgcolor='#ffffc6'>{$res[$r][$i]}</td>\n";
                        } elseif ($i == 1) {                // 運転状況
                            $tmp = equip_machine_state($mac_no, $r, $bg_color, $txt_color);
                            echo "<td width='100' align='center' class='pt12b' bgcolor='$bg_color'><font color='$txt_color'>{$res[$r][$i]}</font></td>\n";
                        } elseif ($i == 2) {                // 運転時間
                            echo "<td width='100' align='right' class='pt12b' bgcolor='#ffffc6'>" . number_format($res[$r][$i]) . "</td>\n";
                        } elseif ($i == 3) {                // state_type
                            if ($res[$r][$i] == 1) {
                                if ( ($res[$r][0] >= 0) && ($res[$r][0] <= 5) ) {
                                    echo "<td align='center' class='pt10'>ネットモニ(中留製)</td>\n";
                                } else {
                                    echo "<td align='center' class='pt10'>カスタムマクロ#500</td>\n";
                                }
                            } elseif ($res[$r][$i] >= 101 && $i <= 200) {  // ネットモニとロータリースイッチのコンビ
                                if ( ($res[$r][0] >= 0) && ($res[$r][0] <= 3) ) {
                                    echo "<td align='center' class='pt10'>ネットモニ(中留製)</td>\n";
                                } else {
                                    echo "<td align='center' class='pt10'>ロータリースイッチ</td>\n";
                                }
                            } else {
                                if ( ($res[$r][0] == 0) || ($res[$r][0] == 1) || ($res[$r][0] == 3) ) {
                                    echo "<td align='center' class='pt10'>ＦＷＳテーブル</td>\n";
                                } else {
                                    echo "<td align='center' class='pt10'>ロータリースイッチ</td>\n";
                                }
                            }
                        } else {                            // その他(現時点では想定外)
                            echo "<td align='center' class='pt12b' bgcolor='#ffffc6'>{$res[$r][$i]}</td>\n";
                        }
                        // <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>　</td> -->
                    }
                    ?>
                    </tr>
                <?php
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <hr color='797979'>
        
        <!--------------- ここから合計表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <thead>
                <tr>
                    <th>総 合 計 時 間</th><th>電源OFFを除く合計時間</th><th>段取中＋自動運転＋無人運転</th>
                    <th>電源OFF以外の停止時間</th><th>単位</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td align='center' nowrap class='pt12b'><?php echo number_format($time_all_sum) ?></td>
                    <td align='center' nowrap class='pt12b'><?php echo number_format($time_ope_sum) ?></td>
                    <td align='center' nowrap class='pt12b'><?php echo number_format($time_act_sum) ?></td>
                    <td align='center' nowrap class='pt12b'><?php echo number_format($time_sto_sum) ?></td>
                    <td align='center' nowrap class='pt12b'>分</td>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
