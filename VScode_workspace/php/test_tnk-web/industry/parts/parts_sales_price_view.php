<?php
//////////////////////////////////////////////////////////////////////////////
// 単価経歴より販売価格(仕切単価)設定  表示画面                             //
// Copyright (C) 2004-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/19 Created  parts_sales_price_view.php                           //
// 2004/12/02 デザイン統一  border='1' cellspacing='0' cellpadding='3'>     //
// 2004/12/20 登録がない時のフィールド数取得を $num=0→$num = count($field) //
// 2005/05/13 ロットが合算されているのを修正しlot_noで分けロット番号を追加  //
// 2009/12/07 生産管理課依頼により、表示を登録番号降順から登録日降順→      //
//            登録番号降順に変更CP00928-0で登録番号999999があったため  大谷 //
// 2013/01/30 最新の登録を色付けする際、ロットが複数の場合すべて色付けする  //
//            ように変更                                               大谷 //
// 2013/05/27 通貨単位表示を追加（円以外は赤字）                            //
// 2013/06/21 SQLのエラーを修正                                        大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
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
$menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(サイトを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('単価経歴より販売価格の照会');
//////////// 表題の設定
// $menu->set_caption('検索結果');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('view',   INDUST . 'parts/parts_sales_price_view.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// GET & POST データの取得
if (isset($_REQUEST['parts'])) {
    $parts_no = $_REQUEST['parts'];
    $_SESSION['cost_parts'] = $parts_no;
} else {
    $_SESSION['s_sysmsg'] .= '部品が指定されていません！';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}
if (isset($_REQUEST['regdate'])) {
    $regdate = $_REQUEST['regdate'];
    $_SESSION['cost_regdate'] = $regdate;
} else {
    $regdate = $_SESSION['cost_regdate'];       // 指定されていない場合はセッションから
}
if (isset($_REQUEST['sales_rate'])) {
    $sales_rate = $_REQUEST['sales_rate'];
    $_SESSION['cost_sales_rate'] = $sales_rate;
} else {
    $sales_rate = $_SESSION['cost_sales_rate']; // 指定されていない場合はセッションから
}

//////////// 表題の設定
$query = "select midsc from miitem where mipn='{$parts_no}'";
if (getUniResult($query, $name) <= 0) {
    $_SESSION['s_sysmsg'] .= 'マスター未登録';    // 後日、parts_cost_form.phpでマスターのチェックを行うように変更予定
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}
$caption = "部品番号：{$parts_no}　部品名：{$name}<br>基準日：" . format_date($regdate) . "　部品販売レート：{$sales_rate}";

//////////// 表形式のデータ表示用 Query & 初期化
$query = "select as_regdate                                 as 登録日       -- 0
                , reg_no                                    as 登録番号     -- 1
                , CASE
                    WHEN kubun = '1' THEN '継続'
                    WHEN kubun = '2' THEN '暫定'
                    WHEN kubun = '3' THEN '今回'
                  END                                       as 登録区分     -- 2
                , sum(lot_cost)                             as 単価         -- 3
                , Uround(sum(lot_cost)*{$sales_rate}, 2)    as 仕切単価     -- 4
                , lot_no                                    as ロット番号   -- 5
            from
                parts_cost_history
            where
                parts_no='{$parts_no}'
                and
                vendor!='88888'
                -- and
                -- as_regdate<={$regdate}
            group by
                reg_no, as_regdate, lot_no, kubun
            having
                (kubun='1' OR kubun='2')    -- GROUPされた物に条件を設定
            order by
                as_regdate DESC, reg_no DESC
            limit 50
";
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>部品番号:%s <br>継続・暫定では単価経歴がありません！</font>", $parts_no);
    $num = count($field);       // フィールド数取得
    // $num = 0;
} else {
    $num = count($field);       // フィールド数取得
    $set_rows   = (-1);          // 初期化(-1=セットしない設定)
    $set_first  = '';            // 初回合致したかどうかの判定
    $set_second = '';            // ２回目合致したかどうかの判定
    // ロットが複数合ったときの対応
    $set_rows1  = (-1);          // 初期化(-1=セットしない設定)
    $set_rows2  = (-1);          // 初期化(-1=セットしない設定)
    $set_rows3  = (-1);          // 初期化(-1=セットしない設定)
    $set_date   = 0;             // 合致レコードのAS登録日
    $set_reg    = 0;             // 合致レコードの登録番号
    //if ($regdate > 0) {         // 基準日がセットされていれば
    //    for ($i=0; $i<$rows; $i++) {
    //        if ($res[$i][0] <= $regdate) {  // 登録日が基準日以下になったら
    //            $set_rows = $i;     // 合致したレコードをセットする
    //            break;
    //        }
    //    }
    //}
    if ($regdate > 0) {                             // 基準日がセットされていれば
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] <= $regdate) {          // 登録日が基準日以下になったら
                if ($set_first == '1') {            // 初回合致しているか
                    if ($set_second == '1') {       // ２回目合致しているか
                        if ($set_date == $res[$i][0] && $set_reg == $res[$i][1]) {  // 初回のAS登録日と登録番号が３回目も同じか
                            $set_rows3 = $i;        // ３回目合致したレコードをセットする
                            break;                  // ロットは３までなので３回合致したら終了
                        } else {
                            break;                  // ３回目は違う単価登録なので終了
                        }
                    } else {
                        if ($set_date == $res[$i][0] && $set_reg == $res[$i][1]) {  // 初回のAS登録日と登録番号が２回目も同じか
                            $set_rows2  = $i;       // ２回目合致したレコードをセットする
                            $set_second = '1';      // ２回目合致フラグを立てる
                        } else {
                            break;                  // ２回目は違う単価登録なので終了
                        }
                    }
                } else {
                    $set_rows1 = $i;                // 初回合致したレコードをセットする
                    $set_date  = $res[$i][0];       // 初回合致時のAS登録日をセット
                    $set_reg   = $res[$i][1];       // 初回合致時の登録番号をセット
                    $set_first = '1';               // 初回合致フラグを立てる
                }
            }
        }
    }
    //if ($set_rows == (-1) ) $set_rows = 0;
    //if ($set_rows1 == (-1) ) $set_rows1 = 0;
    if ($set_rows1 == (-1)) {                   // 単価登録基準日 以前に継続・暫定の登録が無い場合
        for ($i=0; $i<$rows; $i++) {
            if ($set_first == '1') {            // レコード0をセットしたか
                if ($set_second == '1') {       // レコード1の処理を行ったか
                    if ($set_date == $res[2][0] && $set_reg == $res[2][1]) {  // レコード0のAS登録日と登録番号がレコード2も同じか
                        $set_rows3 = 2;         // 必ずレコード2
                        break;                  // ロットは３までなので終了
                    } else {
                        break;                  // レコード2は0と違う単価登録なので終了
                    }
                } else {
                    if ($set_date == $res[1][0] && $set_reg == $res[1][1]) {  // レコード0のAS登録日と登録番号がレコード1も同じか
                        $set_rows2  = 1;        // 必ずレコード1
                        $set_second = '1';      // レコード1のセットフラグを立てる
                    } else {
                        break;                  // レコード1は0と違う単価登録なので終了
                    }
                }
            } else {
                $set_rows1 = 0;                 // 必ずレコード0
                $set_date  = $res[0][0];        // レコード0のAS登録日をセット
                $set_reg   = $res[0][1];        // レコード0の登録番号をセット
                $set_first = '1';               // レコード0のセットフラグを立てる
            }
        }
    }
}
//////////// 表形式のデータ表示用 Query & 初期化 レート区分取得
for ($r=0; $r<$rows; $r++) {
    $query_r = "select        
                    h.rate_div                as レート区分   -- 0
                    , d.rate_sign               as レート記号   -- 1
                    , d.rate_name               as 名称         -- 2
                    , d.rev_par                 as 補正率       -- 3
                from
                    parts_rate_history as h
                left outer join
                    rate_div_master as d
                ON h.rate_div=d.rate_div
                where
                    h.parts_no='{$parts_no}' and h.reg_no='{$res[$r][1]}'
                limit 1
    ";
    $res_r   = array();
    $field_r = array();
    if (($rows_r = getResultWithField2($query_r, $field_r, $res_r)) <= 0) {
        $rate_div[$r]  = '\\';       // レート区分登録がなければ円
        $rate_name[$r] = '日本円';   // レート区分登録がなければ円
        $rev_par[$r]   = 1.000;      // レート区分登録がなければ円
    } else {
        $rate_div[$r]  = $res_r[0][1];       // レート記号
        $rate_name[$r] = $res_r[0][2];       // 名称
        $rev_par[$r]   = $res_r[0][3];       // 補正率
    }
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
<?php // $menu->out_site_java() ?>
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
    // var str = str.toUpperCase();    // 必要に応じて大文字に変換
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

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
 -->

<style type="text/css">
<!--
.pt8 {
    font-size:   8pt;
    font-weight: normal;
    font-family: monospace;
}
.pt9 {
    font-size:   9pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10 {
    font-size:   10pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
form {
    margin:0%;
}
th {
    background-color: blue;
    color:            yellow;
    font-size:        14pt;
    font-weight:      bold;
    font-family:      monospace;
}
td {
    font-size:   12pt;
    font-weight: bold;
    /* font-family: monospace; */
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
}
.winboxr {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    color:                  red;
    /* background-color:#d6d3ce; */
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
</head>
<body class='margin0' onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table width='100%' align='center' border='0'>
            <tr>
                <td class='pt12b' align='center'>
                    <?= $caption, "\n"?>
                </td>
            </tr>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%'align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                    <th class='winbox' nowrap>通貨単位</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    //if ($set_rows == $r) {
                    //    echo "<tr bgcolor='yellow'>\n";
                    //} else {
                    //    echo "<tr>\n";
                    //}
                    if ($set_rows1 == $r) {
                        echo "<tr bgcolor='yellow'>\n";
                    } elseif ($set_rows2 == $r) {
                        echo "<tr bgcolor='yellow'>\n";
                    } elseif ($set_rows3 == $r) {
                        echo "<tr bgcolor='yellow'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td nowrap class='winbox' align='right'>" . ($r + 1) . "</td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                        case 0:     // 登録日
                            echo "<td nowrap align='center' class='winbox'>" . format_date($res[$r][$i]) . "</td>\n";
                            break;
                        case 1:     // 登録番号
                            echo "<td nowrap align='center' class='winbox'>" . $res[$r][$i] . "</td>\n";
                            break;
                        case 2:     // 登録区分
                            echo "<td nowrap align='center' class='winbox'>" . $res[$r][$i] . "</td>\n";
                            break;
                        case 3:     // 単価
                            echo "<td width='80' nowrap align='right' class='winbox'>" . number_format($res[$r][$i], 2) . "</td>\n";
                            break;
                        case 4:    // 仕切単価
                            echo "<td width='80' nowrap align='right' class='winbox'>" . number_format($res[$r][$i], 2) . "</td>\n";
                            break;
                        case 5:    // ロット番号
                            echo "<td width='100' nowrap align='center' class='winbox'>{$res[$r][$i]}</td>\n";
                            if($rate_div[$r] == '\\') {
                                echo "<td width='100' nowrap align='center' class='winbox'>{$rate_div[$r]}</td>\n";
                            } else {
                                echo "<td width='100' nowrap align='center' class='winboxr'>{$rate_div[$r]}</td>\n";
                            }
                            break;
                        default:
                            echo "<td nowrap align='center' class='winbox'>" . $res[$r][$i] . "</td>\n";
                        }
                        // <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
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
