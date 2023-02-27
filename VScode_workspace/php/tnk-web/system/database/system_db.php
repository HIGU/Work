<?php
//////////////////////////////////////////////////////////////////////////////
// システム管理用データベース処理                                           //
// Copyright (C) 2002-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/09/10 Created   system_db.php                                       //
//            セッション管理 & register_globals = Off 対応                  //
// 2002/12/03 サイトメニューに入れたため access_log と site_id=30 追加      //
// 2003/02/26 body に onLoad を追加し初期入力個所に focus() させた          //
//            以下の E_ALL を追加しデバッグレベル(警告)を最高にした         //
// 2003/05/01 エラー時のデータベースの問合せに・・・のメッセージを削除      //
//            pgsql.php の関数内で $php_errormsg の出力に変えたため         //
// 2003/05/12 SQL 文を履歴に保管し呼び出せるように変更 db_admin_history     //
// 2003/06/16 ログチェック用のSQL発行時は履歴保管せずに１発実行に変更       //
// 2003/10/29 ユーザー名等のSQL文を limit 60 → 120 へ変更                  //
// 2003/12/19 ログDB access_log → access_log2 へ変更による SQL文変更       //
// 2004/01/15 db_table_info(テーブル情報)作成による 検索ロジック追加        //
// 2004/04/15 現在の接続ユーザー(default=20分以内で可変式)を照会ボタンを追加//
// 2004/05/05 履歴0.1.2→履歴1.2.3へ変更し三項演算子でロジック(条件判断)処理//
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/07/16 今日の統計を追加して、POST→GET(<a href>)へ変更しデザイン変更 //
// 2004/08/03 stripslashes()を使用する前に履歴を保存していたのを変更予定    //
// 2004/10/18 php5.0.2のtrack_errors問題対応のため追加とテーブル詳細表示追加//
// 2004/10/22 UID別(社員番号・氏名の統計情報)を追加 テーブル情報をwindow表示//
// 2005/02/23 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/03/02 ip_addr<>10.1.3.136→ip_addr!=10.1.3.136へ変更 class使用のため//
// 2005/03/22 iframe版に変更 $userquery を $_SESSION['userquery'] に保存    //
// 2005/04/07 メニュークラスのSQLインジェクション対策ルーチンを避けるため   //
//            インスタンス生成前に$_REQUEST['userquery']をローカル変数に保存//
// 2005/09/20 NNをIEと合わせる為に textareaのCSS font-size:10pt を追加      //
// 2007/01/17 postgresql.confのstandard_conforming_strings = on にしたため  //
//            addslashes($userquery) → pg_escape_string($userquery)        //
// 2007/05/02 今日の統計の LIMIT 30 → LIMIT 70 へ変更                      //
// 2007/12/21 SQL履歴の検索メニューを追加。ショートカットを標準タグへその他 //
// 2007/12/22 履歴を削除する機能追加のためキーワード_実行日_へ変更          //
// 2010/01/19 DB検索をテーブル名でも検索できるように変更               大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('track_errors', '1');               // Store the last error/warning message in $php_errormsg (boolean)
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

//////////// メニュークラスのインスタンスを作る前にグローバル変数を取得する
if (isset($_REQUEST['userquery'])) {
    $userquery = $_REQUEST['userquery'];
}

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(3);                  // 認証チェック0=一般以上 3=admini以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(99, 30);                    // site_index=99(システムメニュー) site_id=60(データベース)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('Data Base Administration (SQL)');
//////////// 表題の設定
// $menu->set_caption('サンプルでアイテムマスターを表示しています');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// 変数の初期化
if (!isset($userquery)) {
    $userquery = "";                            // SQL 文 初期化
    $_SESSION['userquery'] = $userquery;
}

// キャッシュ対策
$uniq = uniqid('menu');

//////////// 履歴のオフセット値の初期化
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
$url_referer = $_SERVER["HTTP_REFERER"];        // 呼出もとのURLを保存 前のスクリプトで分岐処理をしている場合は使用しない
if (!preg_match($current_script, $url_referer)) {    // 自分自身で呼び出していなければ
    $_SESSION['db_admin_offset'] = 0;           // 履歴オフセット値０に初期化
}
if (!isset($_SESSION['db_admin_offset'])) {     // セッションに登録されていなければ
    $_SESSION['db_admin_offset'] = 0;           // 履歴オフセット値０に初期化
}
$offset = $_SESSION['db_admin_offset'];         // ローカル変数にオフセット値登録

if (isset($_REQUEST['db_search'])) {
    $db_search = $_REQUEST['db_search'];
} else {
    $db_search = '検索';
}
if (isset($_REQUEST['hist_search'])) {
    $hist_search = $_REQUEST['hist_search'];
} else {
    $hist_search = 'ここに検索したい内容を入れて下さい。ブランクで全てが対象です。';
}
if (isset($_REQUEST['session_time'])) {
    $session_time = $_REQUEST['session_time'];
    if ($session_time < 1 || $session_time > 59) {  // 59分より大きいとSQLエラーになる。
        $session_time = '20';   // エラーの場合はdefault値に設定
    }
} else {
    $session_time = '20';   // default値を設定(10分)
}

///////////// クエリー実行文の履歴保管
if (isset($_REQUEST['exec'])) {
    $_SESSION['db_admin_offset'] = 0;           // 履歴オフセット値０に初期化
    $offset = $_SESSION['db_admin_offset'];     // ローカル変数にオフセット値登録
    if ($userquery != '') {                     // 2005/04/07 change
        $hist_txt = stripslashes($userquery);       // 2007/12/22 MenuHeaderクラスでaddslashes()されたものの対応
        $hist_txt = pg_escape_string($hist_txt);    // 上記で処理されたものの対応と2007/01/17 change (postgresql.confのstandard_conforming_strings = on にしたため)
        $query_insert = "insert into db_admin_history (hist) values ('$hist_txt')";
        if ( ($ret = query_affected($query_insert)) > 0) {
            $_SESSION['s_sysmsg'] .= "<font color='white'>履歴保管...OK<br></font>";
        } else {
            $_SESSION['s_sysmsg'] .= "履歴保管...NG code=$ret <br>";
        }
        $userquery = stripslashes($userquery);     // ローカル変数にコピー // 2005/04/07 change
    } else {
        // SQL 文がブランクだったら 何もしない。
    }
    $_SESSION['userquery'] = $userquery;
}

///////////// クエリー実行文の履歴呼出
if (isset($_REQUEST['hist'])) {
    $query = "SELECT hist FROM db_admin_history ORDER BY regdate DESC OFFSET $offset LIMIT 1";
    if (getUniResult($query, $hist) > 0) {
        $userquery = stripslashes($hist);
        $_SESSION['userquery'] = $userquery;
    } else {
        $_SESSION['s_sysmsg'] .= "履歴呼出に失敗<br>";
    }
    $_SESSION['db_admin_offset']++;         // 履歴オフセット値インクリメント
}

///////////// クリアー時に履歴オフセットも初期化
if (isset($_REQUEST['clr'])) {
    $_SESSION['db_admin_offset'] = 0;           // 履歴オフセット値０に初期化
    $offset = $_SESSION['db_admin_offset'];     // ローカル変数にオフセット値登録
    $userquery = "";                            // SQL 文 初期化
    $_SESSION['userquery'] = $userquery;
}

//////////// ログチェックのクエリー選択及び設定
if (isset($_REQUEST['cpy'])) {
    if ($_REQUEST['cpy'] == "スクリプト別") {
        $userquery = "SELECT script,count(*) FROM access_log2 WHERE ip_addr != '10.1.3.136' GROUP BY script ORDER BY count DESC LIMIT 300";
    } elseif ($_REQUEST['cpy'] == "ホスト別") {
        $userquery = "SELECT host,count(*) FROM access_log2 WHERE ip_addr != '10.1.3.136' GROUP BY host ORDER BY count DESC LIMIT 100";
    } elseif ($_REQUEST['cpy'] == "ＵＩＤ別") {
        $userquery = "SELECT acc.uid AS 社員番号, usr.name AS 氏　名, count(*) AS アクセス数 FROM access_log2 AS acc LEFT OUTER JOIN user_detailes AS usr USING(uid) GROUP BY 社員番号, 氏　名 ORDER BY アクセス数 DESC LIMIT 100 OFFSET 0";
    } elseif ($_REQUEST['cpy'] == "ユーザー名") {
        $userquery = "SELECT a.ip_addr, a.host, a.uid, u.name, CAST(time_log AS DATE) AS 日付, CAST(time_log AS time) AS 時間, script FROM access_log2 AS a LEFT OUTER JOIN user_detailes AS u USING(uid) WHERE a.ip_addr != '10.1.3.136' ORDER BY a.time_log DESC LIMIT 500 OFFSET 0";
    } elseif ($_REQUEST['cpy'] == 'DB 検索') {
        $userquery = "SELECT db_name, table_name AS _テーブル名_, table_comment AS テーブル説明 FROM db_table_info WHERE (table_comment LIKE '%{$db_search}%') OR (table_name LIKE '%{$db_search}%')";
    } elseif ($_REQUEST['cpy'] == '履歴検索') {
        $userquery = "SELECT /* to_char(regdate, 'YY/MM/DD HH24:MI:SS') AS 実行日 */ regdate AS _実行日_, hist AS \"SQL履歴\" FROM db_admin_history WHERE hist LIKE '%{$hist_search}%' ORDER BY regdate DESC LIMIT 100";
    } elseif ($_REQUEST['cpy'] == '接続user') {
        $userquery = "SELECT a.ip_addr, a.host, a.uid, u.name FROM access_log2 AS a LEFT OUTER JOIN user_detailes AS u USING(uid) WHERE a.time_log>=(CURRENT_TIMESTAMP - time '00:{$session_time}:00') GROUP BY a.ip_addr, a.host, a.uid, u.name LIMIT 120 OFFSET 0";
    } elseif ($_REQUEST['cpy'] == '今日の統計') {
        $day = date('Ymd');
        $userquery = "SELECT a.host, a.uid, u.name, count(*) FROM access_log2 AS a LEFT OUTER JOIN user_detailes AS u USING(uid) WHERE time_log>=CAST('$day 000000' AS timestamp) AND time_log<=CAST('$day 240000' AS timestamp) AND ip_addr != '10.1.3.136' GROUP BY a.uid, u.name, a.host ORDER BY count DESC LIMIT 70";
    }
    $_SESSION['userquery'] = $userquery;
    $_REQUEST['exec'] = "実行";    // ボタン一つで実行させ 履歴保管しない
}

////////// HTML Header を出力してブラウザーのキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<script language="JavaScript">
<!--
function db_search() {
    var db_search = prompt('検索キーを入力して下さい。', '<?php echo $db_search ?>');
    if (db_search == null) {
        return;
    }
    document.ini_form.action = '<?php echo $current_script, '?cpy=', urlencode('DB 検索') ?>';
    document.ini_form.db_search.value = db_search;
    document.ini_form.submit();
}

function session_time() {
    var session_time = prompt('接続時間を1～59で入力して下さい。(単位：分)', '<?php echo $session_time ?>');
    if (session_time == null) {
        return;
    }
    document.ini_form.action = '<?php echo $current_script, '?cpy=', urlencode('接続user') ?>';
    document.ini_form.session_time.value = session_time;
    document.ini_form.submit();
}
function history_search() {
    var hist = prompt('SQLの検索キーを入力して下さい。', '<?php echo $hist_search ?>');
    if (hist == null) {
        return;
    }
    document.ini_form.action = '<?php echo $current_script, '?cpy=', urlencode('履歴検索') ?>';
    document.ini_form.hist_search.value = hist;
    document.ini_form.submit();
}
// -->
</script>
<style type="text/css">
<!--
textarea {
    background-color:black;
    color:white;
    font-size:      10pt;
}
td.gb {
    background-color:   #d6d3ce;
    color:              black;
}
.white {
    color:              white;
}
.pt6 {
    font-size:      6pt;
    font-weight:    normal;
}
.pt7 {
    font-size:      7pt;
    font-weight:    normal;
}
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.t_border {
    border-collapse: collapse;
}
.select_font {
    font-size:      10pt;
    font-weight:    bold;
    width:          100px;
}
a {
    color:              blue;
}
a:hover {
    background-color:   yellow;
}
a:active {
    background-color:   gold;
    color:              black;
}
th {
    background-color:       yellow;
    color:                  blue;
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
td {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
-->
</style>
<script language='JavaScript'>
<!--
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
</head>
<body onLoad='document.ini_form.userquery.focus()' style='overflow:hidden;'>
<center>
<?php echo $menu->out_title_border()?>
    
    <form name='ini_form' method='post' action='<?php echo $menu->out_self() ?>'>
        <table width='100%' border='0'>
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>
                <tr>
                    <td align='center' class='select_font' nowrap>
                        <a href='<?php echo $menu->out_self(), '?cpy=', urlencode('スクリプト別') ?>' style='text-decoration:none;'>スクリプト別</a>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='<?php echo $menu->out_self(), '?cpy=', urlencode('ホスト別') ?>' style='text-decoration:none;'>ホスト別</a>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='<?php echo $menu->out_self(), '?cpy=', urlencode('ＵＩＤ別') ?>' style='text-decoration:none;'>ＵＩＤ別</a>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='<?php echo $menu->out_self(), '?cpy=', urlencode('ユーザー名') ?>' style='text-decoration:none;'>ユーザー名</a>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='JavaScript:db_search()' style='text-decoration:none;'>DB 検索</a>
                        <input type='hidden' name='db_search' value='<?php echo $db_search ?>'>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='JavaScript:history_search()' style='text-decoration:none;'>履歴検索</a>
                        <input type='hidden' name='hist_search' value='<?php echo $hist_search ?>'>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='JavaScript:session_time()' style='text-decoration:none;'>接続user</a>
                        <input type='hidden' name='session_time' value='<?php echo $session_time ?>'>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='<?php echo $menu->out_self(), '?cpy=', urlencode('今日の統計') ?>' style='text-decoration:none;'>今日の統計</a>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
            <table width='100%' border='0'>
                <tr>
                    <td valign='middle' align='right' width='50' style='border-width:0px;'>
                        <input type='submit' name='hist' value='履歴<?php echo isset($_REQUEST['hist']) ? $offset+1 : '' ?>'>
                    </td>
                    <td align='center' style='border-width:0px;'>
                        <textarea name='userquery' cols='100' rows=5 wrap='virtual'><?php echo $userquery ?></textarea>
                    </td>
                    <td valign='middle' align='center' class='pt8' style='border-width:0px;'>
                        <input type='submit' name='exec' value='実 行'>
                        
                        <input type='submit' name='clr'  value='クリア'>
                        <br>
                        <a href="../../emp/help.htm" target="_blank">
                            <img border=0 src="../../img/help.gif" alt="ヘルプ" width=22 height=16>
                        </a>
                    </td>
                    <td valign='bottom' align='center' class='pt8'>
                    </td>
                </tr>
            </table>
        </table>
    </form>
<?php if ( (isset($_REQUEST['exec'])) && (!isset($_REQUEST['clr'])) && (isset($userquery)) ) { ?>
    <iframe hspace='0' vspace='0' scrolling='yes' src='system_db_iframe.php?id=<?php echo $uniq?>'
        name='system_db_view' align='center' width='100%' height='75%' title='DataBase view'>
        DataBase の Query View を表示しています。
    </iframe>
<?php } else { ?>
    <hr> <!-- ------------------------------------------- -->
<?php } ?>
</center>
</body>
<?php // = $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
