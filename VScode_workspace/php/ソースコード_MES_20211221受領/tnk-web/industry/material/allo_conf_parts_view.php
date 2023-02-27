<?php
//////////////////////////////////////////////////////////////////////////////
// 引当部品構成表の照会  計画番号の表示 view                                //
//                              Allocated Configuration Parts 引当構成部品  //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/05/28 Created  allo_conf_parts_view.php                             //
// 2004/06/07 リターンアドレスの設定を呼出元で先セッションに保存しておく    //
// 2004/12/08 CC部品とTNKCCを表示追加                                       //
// 2004/12/28 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
//    ディレクトリをindustry→industry/materialへ変更unregistからの呼出対応 //
// 2005/01/07 $menu->set_retGET('page_keep', $_REQUEST['material']);で統一  //
// 2005/01/12 部品名をtrim(substr(midsc,1,25))→trim(substr(midsc,1,21))変更//
// 2005/01/31 部品番号から行番号へマーク変更 &row={$r} の追加で対応         //
// 2005/02/07 $search = sprintf("where plan_no='%s'", $plan_no); を↓に変更 //
//            where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);    //
// 2005/05/20 db_connect() → funcConnect() へ変更 pgsql.phpで統一のため    //
// 2006/04/13 <a name='mark'によりフォーカス移動対応で、setTimeout()を追加  //
// 2006/08/01 合計レコード数 取得時に引当が無ければ終了を追加               //
// 2006/12/01 ダブルクリックで不要な引当を削除する機能を追加delParts権限必要//
// 2006/12/18 上記の機能を使った場合もリターン情報を維持するため$param追加  //
// 2007/02/20 parts/からparts/parts_stock_history/parts_stock_view.phpへ変更//
// 2007/02/22 set_caption()に工事番号追加。部品番号10pt→11pt,支給条件→条件//
// 2007/03/22 parts_stock_view.php → parts_stock_history_Main.php へ変更   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 26);                    // site_index=30(生産メニュー) site_id=26(引当部品構成表の照会)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);          // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('引当 部品 構成表 の 照会');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('引当構成表の表示',   INDUST . 'material/allo_conf_parts_view.php');
$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
//////////// リターン時の情報復元
if (isset($_REQUEST['plan_cond'])) {    // 計画番号の入力状態をチェック(フォームからの呼出対応)
    $menu->set_retGET('plan', $_REQUEST['plan_cond']);
}
if (isset($_REQUEST['material'])) {     // 総材料費の未登録からの呼出対応
    $menu->set_retGET('page_keep', $_REQUEST['material']);
    $parts_no = @$_SESSION['stock_parts'];
    if (isset($_REQUEST['row'])) {
        $row_no = $_REQUEST['row'];   // 前回呼出した行番号
        $param  = "&material={$_REQUEST['material']}&row={$_REQUEST['row']}";
    } else {
        $row_no = -1;       // 未登録リストから呼ばれた時
        $param  = "&material={$_REQUEST['material']}";
    }
} else {
    $parts_no = '';
    $row_no   = '-1';       // 単体で照会された時
    $param    = '';
}

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 一頁の行数
define('PAGE', '300');      // 現在は300を越える引当構成はない

//////////// 計画番号・製品番号をリクエストから取得(主に総材料費の登録で使用)
if (isset($_REQUEST['plan_no'])) {
    $plan_no = $_REQUEST['plan_no'];
    $_SESSION['material_plan_no'] = $plan_no;   // セッションに保存
    $_SESSION['plan_no'] = $plan_no;            // フォーム用のデータにも保存
    //////////// 計画番号・製品番号をセッションから取得(フォームからの照会で使用)
} elseif (isset($_SESSION['plan_no'])) {
    $plan_no = $_SESSION['plan_no'];
} else {
    $_SESSION['s_sysmsg'] .= '計画番号が指定されてない！';      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}
///// 製品番号・工事番号の取得
$query = "SELECT parts_no, note15 from assembly_schedule where plan_no='{$plan_no}'";
if (getResult2($query, $assy_res) <= 0) {
    // .= メッセージを追加する
    $_SESSION['s_sysmsg'] .= "計画番号：{$plan_no} 計画データがないため Assy番号を取得出来ません！";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $assy_no = $assy_res[0][0];
    $kouji_no = $assy_res[0][1];
    if (substr($assy_no, 0, 1) == 'C') {    // assy_noの頭１桁で事業部を判定
        define('RATE', 25.60);  // カプラ
    } else {
        define('RATE', 37.00);  // リニア(それ以外は現在ない)
    }
}

//////////// 製品名の取得
$query = "select midsc from miitem where mipn='{$assy_no}'";
if ( getUniResult($query, $assy_name) <= 0) {           // 製品名の取得
    $_SESSION['s_sysmsg'] .= "製品名の取得に失敗";      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

//////////// 表題の設定
$menu->set_caption("計画番号：{$plan_no}　製品番号：{$assy_no}　製品名：{$assy_name}　<span style='color:red;'>工事：{$kouji_no}</span>");

//////////// SQL 文の where 句を 共用する
$search = sprintf("where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// 合計レコード数＝引当部品数の取得     (対象データの最大数をページ制御に使用)
$query = sprintf("select count(*) from allocated_parts %s", $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
} else {
    if ($maxrows <= 0) {
        $_SESSION['s_sysmsg'] .= "引当がありません！";      // .= メッセージを追加する
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
}


//////////// ページオフセット設定
if ( isset($_POST['forward']) ) {                       // 次頁が押された
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // 次頁が押された
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) || isset($_GET['number']) ) {   // 現在のページを維持する
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['offset'];


//////////// 不要な引当部品の削除処理 2006/12/01 ADD
if (isset($_REQUEST['delParts'])) {
    if (getCheckAuthority(23)) {
        $sql = "
            DELETE FROM allocated_parts WHERE plan_no='{$plan_no}' AND parts_no='{$_REQUEST['delParts']}'
        ";
        if (query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = "{$_REQUEST['delParts']} の削除に失敗しました！";
        } else {
            $_SESSION['s_sysmsg'] = "{$_REQUEST['delParts']} を削除しました。";
        }
    } else {
        $_SESSION['s_sysmsg'] = '削除する権限がありません！';
    }
}

//////////// 計画番号単位の工程明細の作表
$query_basic = "
        SELECT  parts_no    as 部品番号                 -- 0
                ,trim(substr(midsc,1,21))
                            as 部品名                   -- 1
                ,unit_qt    as 使用数                   -- 2
                ,allo_qt    as 引当数                   -- 3
                ,sum_qt     as 出庫累計                 -- 4
                ,allo_qt - sum_qt
                            as 出庫残                   -- 5
                ,CASE
                    WHEN cond = '2' THEN '有償'
                    WHEN cond = '3' THEN '無償'
                    ELSE cond
                END         as 条件                     -- 6 旧は支給条件
                ,price      as 有償単価                 -- 7
                ,Uround(allo_qt * price, 2)
                            as 有償金額                 -- 8
        FROM
            allocated_parts
        LEFT OUTER JOIN
             miitem ON parts_no=mipn 
        ";
$query = sprintf("{$query_basic}
        %s 
        ORDER BY parts_no ASC OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'><br>現在未登録です！</font>";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
    /////////////// 表示用の配列データを生成 view_data (子部品を親部品の最後に並び替えする)
    $res_view   = array();
    $field_view = array();
    $rows_view  = 0;
    $num_view   = 0;
    $rec        = 0;
    $col        = 0;
    $query_basic = "SELECT parts_no
                        , trim(substr(midsc,1,25))
                        , unit_qt
                        , '-'
                        , '-'
                        , '-'
                        ,CASE
                            WHEN mtl_cond = '1' THEN '自給'
                            WHEN mtl_cond = '2' THEN '有償'
                            WHEN mtl_cond = '3' THEN '無償'
                            ELSE mtl_cond
                        END
                        , '-'
                        , '-'
                    FROM
                        parts_configuration
                    LEFT OUTER JOIN
                        miitem
                    ON parts_no=mipn
                    WHERE p_parts_no='%s' AND mtl_cond!='1' ORDER BY parts_no ASC";
    //////// Level1 Start
    for ($r=0; $r<$rows; $r++) {
        for ($c=0; $c<$num; $c++) {
            if ($c == 0) {
                $res_view[$rec][$col] = '.1';   // L1=レベル１
                $col++;
                $res_view[$rec][$col] = $res[$r][$c];
                $col++;
            } else {
                $res_view[$rec][$col] = $res[$r][$c];
                $col++;
            }
        }
        $col = 0;
        $rec++;
        ////////// Level2 子部品データチェック
        $query = sprintf($query_basic, $res[$r][0]);
        $res2 = array();
        if ( ($rows2=getResult2($query, $res2)) > 0) {         // 子部品 の取得
            ////////// Level2 Start 子部品データあり
            for ($r2=0; $r2<$rows2; $r2++) {
                for ($c2=0; $c2<$num; $c2++) {
                    if ($c2 == 0) {
                        $res_view[$rec][$col] = '..2';   // L2=レベル２
                        $col++;
                        $res_view[$rec][$col] = $res2[$r2][$c2];
                        $col++;
                    } else {
                        $res_view[$rec][$col] = $res2[$r2][$c2];
                        $col++;
                    }
                }
                $col = 0;
                $rec++;
                ////////// Level3 子部品データチェック
                $query = sprintf($query_basic, $res2[$r2][0]);
                $res3 = array();
                if ( ($rows3=getResult2($query, $res3)) > 0) {         // 子部品 の取得
                    ////////// Level3 Start 子部品データあり
                    for ($r3=0; $r3<$rows3; $r3++) {
                        for ($c3=0; $c3<$num; $c3++) {
                            if ($c3 == 0) {
                                $res_view[$rec][$col] = '...3';   // L3=レベル３
                                $col++;
                                $res_view[$rec][$col] = $res3[$r3][$c3];
                                $col++;
                            } else {
                                $res_view[$rec][$col] = $res3[$r3][$c3];
                                $col++;
                            }
                        }
                        $col = 0;
                        $rec++;
                        ////////// Level4 子部品データチェック
                        $query = sprintf($query_basic, $res3[$r3][0]);
                        $res4 = array();
                        if ( ($rows4=getResult2($query, $res4)) > 0) {         // 子部品 の取得
                            ////////// Level4 Start 子部品データあり
                            for ($r4=0; $r4<$rows4; $r4++) {
                                for ($c4=0; $c4<$num; $c4++) {
                                    if ($c4 == 0) {
                                        $res_view[$rec][$col] = '....4';   // L4=レベル４
                                        $col++;
                                        $res_view[$rec][$col] = $res4[$r4][$c4];
                                        $col++;
                                    } else {
                                        $res_view[$rec][$col] = $res4[$r4][$c4];
                                        $col++;
                                    }
                                }
                                $col = 0;
                                $rec++;
                                ////////// Level5 子部品データチェック
                                $query = sprintf($query_basic, $res4[$r4][0]);
                                $res5 = array();
                                if ( ($rows5=getResult2($query, $res5)) > 0) {         // 子部品 の取得
                                    ////////// Level5 Start 子部品データあり
                                    for ($r5=0; $r5<$rows5; $r5++) {
                                        for ($c5=0; $c5<$num; $c5++) {
                                            if ($c5 == 0) {
                                                $res_view[$rec][$col] = '.....5';   // L5=レベル５
                                                $col++;
                                                $res_view[$rec][$col] = $res5[$r5][$c5];
                                                $col++;
                                            } else {
                                                $res_view[$rec][$col] = $res5[$r5][$c5];
                                                $col++;
                                            }
                                        }
                                        $col = 0;
                                        $rec++;
                                    }
                                }
                                ////////// Level5 End
                            }
                        }
                        ////////// Level4 End
                    }
                }
                ////////// Level3 End
            }
        }
        /////////// Level2 End
    }
    ///////// Level1 End
    
    ////// レコード数の設定
    $rows_view = $rec;
    ////// フィールド名の追加
    for ($i=0; $i<$num; $i++) {
        if ($i == 0) {
            $field_view[0] = 'レベル';
            $field_view[$i+1] = $field[0];
        } else {
            $field_view[$i+1] = $field[$i];
        }
    }
    ////// フィールド数の設定
    $num_view = count($field_view);       // フィールド数取得
    
    /**************** TNKCC CC部品 表示追加 *********************/
    /////////// begin トランザクション開始
    if ($con = funcConnect()) {
        // query_affected_trans($con, 'begin');
    } else {
        $_SESSION['s_sysmsg'] = 'データベースと接続できません！';
    }
    ////// TNKCC部品の取得と階層レベル(レベル２以下)のCC部品の表示
    for ($r=0; $r<$rows_view; $r++) {
        $query_tnkcc = "SELECT
                            CASE
                                WHEN miccc='E' THEN 'TNKCC'
                                WHEN miccc='D' THEN 'CC部品'
                                ELSE '&nbsp;'
                            END
                        FROM miccc WHERE mipn='{$res_view[$r][1]}'
        ";
        if (getUniResTrs($con, $query_tnkcc, $res_tnkcc) > 0) {
            // データあり
            $res_view[$r][$num_view] = $res_tnkcc;
        } else {
            // データなし
            $res_view[$r][$num_view] = '&nbsp;';
        }
    }
    $field_view[$num_view] = 'CC部品';
    ////// フィールド数の設定
    $num_view = count($field_view);       // フィールド数取得
    ////// CC部品の取得
    $query_cc = "
        SELECT  '.1'        as レベル                   -- 0
                ,parts_no   as 部品番号                 -- 1
                ,trim(substr(midsc,1,25))
                            as 部品名                   -- 2
                ,unit_qt    as 使用数                   -- 3
                ,'-'        as 引当数                   -- 4
                ,'-'        as 出庫累計                 -- 5
                ,'-'        as 出庫残                   -- 6
                ,CASE
                    WHEN mtl_cond = '1' THEN '自給'     -- ありえないが？
                    WHEN mtl_cond = '2' THEN '有償'
                    WHEN mtl_cond = '3' THEN '無償'
                    ELSE mtl_cond
                END         as 条件                     -- 7 旧は支給条件
                ,'-'        as 有償単価                 -- 8
                ,'-'        as 有償金額                 -- 9
                ,'CC部品'   as CC部品                   -- 10
        FROM
            parts_configuration
        LEFT OUTER JOIN
            miccc
        ON parts_no=miccc.mipn
        LEFT OUTER JOIN
             miitem
        ON parts_no=miitem.mipn
        WHERE
            p_parts_no='{$assy_no}'
            and
            miccc.miccc='D'
    ";
    if ( ($rows_cc=getResultTrs($con, $query_cc, $res_cc)) > 0) {
        // CC部品あり
        $num_cc = count($res_cc[0]);
        for ($r=0; $r<$rows_cc; $r++) {
            for ($i=0; $i<$num_cc; $i++) {
                $res_view[$rows_view+$r][$i] = $res_cc[$r][$i];
            }
        }
        // レコード数のセット
        $rows_view = ($rows_view + $rows_cc);
    }
    /////////// commit トランザクション終了
    // query_affected_trans($con, 'commit');
    // pg_close($con); は必要ない
}

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

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* 入力文字がアルファベットかどうかチェック isDigit()の逆 */
function isABC(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // スペースはOK
            return false;
        }
    }
    return true;
}

/* 入力文字が数字かどうかチェック 小数点対応 */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1個目かチェック
                cnt_dot++;
            } else {
                return false;       // 2個目は false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

function checkDelete(url, delParts, sumQT)
{
    if (sumQT == 0) {
        if (confirm(delParts + "の部品を削除します。\n\n宜しいですか？")) {
            location.replace(url<?php echo "+\"{$param}\""?>);
        }
    } else {
        if (confirm(delParts + "は既に出庫済みです。それでも削除しますか？\n\n削除した場合は元に戻せません！\n\n宜しいですか？")) {
            location.replace(url<?php echo "+\"{$param}\""?>);
        }
    }
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // <a name='mark' でフォーカスが移るため0.1秒ずらしてフォーカスをセットする。
    // フレームを切っていないためフォーカスを変えるとmarkへいかないためコメント
    // setTimeout("document.mhForm.backwardStack.focus()", 100);  //こちらに変更しNN対応
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
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
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family: monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-wieght:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   gold;
    color:              darkblue;
}
a {
    font-size:          11pt;
    font-weight:        bold;
    color:              blue;
    text-decoration:    none;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <!--
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                        -->
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?= $menu->out_caption() . "\n" ?>
                    </td>
                    <td align='right'>
                        <!--
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                        -->
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num_view; $i++) {             // フィールド数分繰返し
                ?>
                    <th class='winbox' nowrap><?= $field_view[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                        <!--  bgcolor='#ffffc6' 薄い黄色 -->
                        <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows_view; $r++) {
                    // if ($parts_no == $res_view[$r][1]) {
                    if ($row_no == $r) {
                        if ($res_view[$r][4] != '-') {   // 引当数のフィールドをチェックして引当部品なら
                            echo "<tr style='background-color:#ffffc6;' onDblClick='checkDelete(\"", $menu->out_self(), '?delParts=', urlencode($res_view[$r][1]), "\", \"{$res_view[$r][1]}\", \"{$res_view[$r][5]}\")'><a name='mark'></a>\n";
                        } else {
                            echo "<tr style='background-color:#ffffc6;'><a name='mark'></a>\n";
                        }
                    } else {
                        if ($res_view[$r][4] != '-') {   // 引当数のフィールドをチェックして引当部品なら
                            echo "<tr onDblClick='checkDelete(\"", $menu->out_self(), '?delParts=', urlencode($res_view[$r][1]), "\", \"{$res_view[$r][1]}\", \"{$res_view[$r][5]}\")'>\n";
                        } else {
                            echo "<tr>\n";
                        }
                    }
                    echo "    <td class='winbox' nowrap style='font-size:10pt; font-weight:bold; font-family:monospace;' align='right'>\n";
                    echo "            ", ($r + $offset + 1), "\n";
                    echo "    </td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num_view; $i++) {         // レコード数分繰返し
                        if ($res_view[$r][4] != '-') {   // 引当数のフィールドをチェックして引当部品なら
                            switch ($i) {
                            case 0:    // レベル
                                echo "<td class='winbox' nowrap align='left' style='font-size:10pt; font-weight:bold; font-family:monospace;'>" . $res_view[$r][$i] . "</td>\n";
                                break;
                            case 1:     // 部品番号
                                if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                    echo "<td class='winbox' nowrap align='center' style='font-size:9pt; font-family:monospace;'>　</td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center' style='font-size:9pt; font-family:monospace;'><a href='", $menu->out_action('在庫経歴'), "?parts_no=", urlencode($res_view[$r][$i]), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='application' style='text-decoration:none;'>{$res_view[$r][$i]}</a></td>\n";
                                }
                                break;
                            case 2:     // 部品名
                                if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                    echo "<td class='winbox' nowrap width='240' align='left' style='font-size:9pt; font-family:monospace;'>　</td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='240' align='left' style='font-size:9pt; font-family:monospace;'>{$res_view[$r][$i]}</td>\n";
                                }
                                break;
                            case  3:    // 使用数
                                echo "<td class='winbox' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 4) . "</td>\n";
                                break;
                            case  4:    // 引当数
                            case  5:    // 出庫累計
                            case  6:    // 出庫残
                                echo "<td class='winbox' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 0) . "</td>\n";
                                break;
                            case  8:    // 有償単価
                            case  9:    // 有償金額
                                echo "<td class='winbox' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 2) . "</td>\n";
                                break;
                            default:    // 7 支給条件
                                echo "<td class='winbox' nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view[$r][$i]}</td>\n";
                            }
                        } else {            // 製造用部品表から取得した子部品なら
                            switch ($i) {
                            case 0:    // レベル
                                echo "<td class='winbox' nowrap align='left' style='font-size:10pt; font-weight:bold; font-family:monospace;'>" . $res_view[$r][$i] . "</td>\n";
                                break;
                            case 1:     // 部品番号
                                if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                    echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center' style='font-size:9pt; font-family:monospace;'>　</td>\n";
                                } else {
                                    echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center' style='font-size:9pt; font-family:monospace;'><a href='", $menu->out_action('在庫経歴'), "?parts_no=", urlencode($res_view[$r][$i]), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='application' style='text-decoration:none;'>{$res_view[$r][$i]}</a></td>\n";
                                }
                                break;
                            case 2:     // 部品名
                                if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                    echo "<td class='winbox' bgcolor='#e6e6e6' nowrap width='240' align='left' style='font-size:9pt; font-family:monospace;'>　</td>\n";
                                } else {
                                    echo "<td class='winbox' bgcolor='#e6e6e6' nowrap width='240' align='left' style='font-size:9pt; font-family:monospace;'>{$res_view[$r][$i]}</td>\n";
                                }
                                break;
                            case  3:    // 使用数
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 4) . "</td>\n";
                                break;
                            case  4:    // 引当数
                            case  5:    // 出庫累計
                            case  6:    // 出庫残
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 0) . "</td>\n";
                                break;
                            case  8:    // 有償単価
                            case  9:    // 有償金額
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 2) . "</td>\n";
                                break;
                            default:    // 7 支給条件
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view[$r][$i]}</td>\n";
                            }
                        }
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
