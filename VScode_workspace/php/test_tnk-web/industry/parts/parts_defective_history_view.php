<?php
//////////////////////////////////////////////////////////////////////////////
// 生産用 部品在庫経歴 照会 表示画面(ＭＶＣ)                                //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/20 Created  parts_stock_view.php                                 //
// 2004/12/21 order byをserial_no DESC→upd_date DESC, serial_no DESCへ変更 //
// 2004/12/23 半角カナはmb_substrを使わない length が合わなくなる。         //
// 2004/12/25 style='overflow:hidden;' (-xy両方)を追加                      //
// 2005/01/07 $menu->set_retGET('page_keep', $_REQUEST['material']);で統一  //
// 2005/01/11 ＣＣ部品の時に入庫経歴がないため買掛のリンクを表題に設定      //
//            大谷氏の要望で条件を付けずにリンクとするように変更            //
// 2005/01/12 総材料費の未登録からの呼出時は500→200へ変更 retGETに#mark追加//
//            カプラのボール等の対策でtnk_stockが100000個を超える場合は400  //
// 2005/01/31 $menu->set_retGETanchor('mark') を追加 urlencode()に対応      //
//            '&material=' . urlencode($_SESSION['stock_parts']) を追加     //
//            初回のみ行番号をセッションに保存し、リターン時に行番号を返す  //
// 2005/03/02 allo_parts_rowとmaterial_plan_noを@で抑止(単体呼出対応)       //
// 2005/05/22 order by parts_no DESC, upd_date DESC, serial_no DESC に変更  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');// zend 1.X コンパチ php4の互換モード
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
$menu->set_site(30, 40);                    // site_index=30(生産メニュー) site_id=40(部品在庫経歴)999(サイトを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('部品在庫経歴の照会');
//////////// 呼出先のaction名とアドレス設定
$menu->set_frame('フレームで表示',   INDUST . 'parts/parts_stock_iframe.php');
$menu->set_action('買掛実績照会',     INDUST . 'payable/act_payable_view.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// GET & POST データの取得
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];
    $_SESSION['stock_parts'] = $parts_no;
} else {
    $parts_no = $_SESSION['stock_parts'];
}
if (isset($_REQUEST['row'])) {
    $_SESSION['allo_parts_row'] = $_REQUEST['row'];             // 初回のみ行番号をセッションに保存
}

    $material = '';
    $plan_no  = '　';

if (isset($_REQUEST['date_low'])) {
    $date_low = $_REQUEST['date_low'];
    $_SESSION['stock_date_lower'] = $date_low;
} else {
    $date_low = '20000401';      // 指定されていない場合
}
if (isset($_REQUEST['date_upp'])) {
    $date_upp = $_REQUEST['date_upp'];
    $_SESSION['stock_date_upper'] = $date_upp;
} else {
    $date_upp = date('Ymd');    // 指定されていない場合
}
if (isset($_REQUEST['view_rec'])) {
    $view_rec = $_REQUEST['view_rec'];
    $_SESSION['stock_view_rec'] = $view_rec;
} else {
    $sql = "select tnk_stock from parts_stock_master where parts_no='{$parts_no}'";
    getUniResult($sql, $tnk_stock);
    if ($tnk_stock >= 100000) {
        $view_rec = '400';      // 指定されていない場合(総材料費の未登録からの呼出等) 500→400
    } else {
        $view_rec = '200';      // 指定されていない場合(総材料費の未登録からの呼出等) 500→200
    }
}

//////////// 表題の設定
$query = "select substr(midsc, 1, 20)
                , substr(mzist, 1, 6)
                , substr(mepnt, 1, 6)
                , tnk_tana, nk_stock+tnk_stock AS sum_stock
            from
                miitem
                left outer join
                parts_stock_master
                on mipn=parts_no
            where mipn='{$parts_no}'";
$item  = array();
if (getResult2($query, $item) <= 0) {
    $_SESSION['s_sysmsg'] .= 'マスター未登録';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
        // (全角カナを半角へ)半角カナはmb_substrを使わないとうまくいかない。
    $name  = mb_substr(mb_convert_kana($item[0][0], 'krsna'), 0, 10);   // 部品名
    $zai   = mb_substr(mb_convert_kana($item[0][1], 'krsna'), 0,  7);   // 材質
    $kisyu = $item[0][2];   // 親機種
    $tana  = $item[0][3];   // TNK棚番
    $zaiko = number_format($item[0][4]);   // 現在在庫
}
//////////// 表題の設定
/****************************************
if (getUniResult("select miccc from miccc where mipn='{$parts_no}'", $miccc)) {
    if ($miccc == 'D') {
        $link = "<a href='" . $menu->out_action('買掛実績照会') . "?parts_no={$parts_no}&material=1' style='text-decoration:none;'>{$parts_no}</a>";
        $menu->set_caption("部品番号：{$link}　部品名：{$name}&nbsp;&nbsp;材質：{$zai}&nbsp;&nbsp;親機種：{$kisyu}　棚番：{$tana}　現在在庫：<font color='red'>{$zaiko}</font>");
    } else {
        $menu->set_caption("部品番号：{$parts_no}　部品名：{$name}&nbsp;&nbsp;材質：{$zai}&nbsp;&nbsp;親機種：{$kisyu}　棚番：{$tana}　現在在庫：<font color='red'>{$zaiko}</font>");
    }
} else {
    $menu->set_caption("部品番号：{$parts_no}　部品名：{$name}&nbsp;&nbsp;材質：{$zai}&nbsp;&nbsp;親機種：{$kisyu}　棚番：{$tana}　現在在庫：<font color='red'>{$zaiko}</font>");
}
****************************************/
$link = "<a href='" . $menu->out_action('買掛実績照会') . "?parts_no=" . urlencode($parts_no) . "&material=1' style='text-decoration:none;'>{$parts_no}</a>";
$menu->set_caption("部品番号：{$link}　部品名：{$name}&nbsp;&nbsp;材質：{$zai}&nbsp;&nbsp;親機種：{$kisyu}　棚番：{$tana}　現在在庫：<font color='red'>{$zaiko}</font>");

//////////// 表形式のデータ表示用 Query & 初期化
$query = "select substr(to_char(ent_date, 'FM9999/99/99'), 3, 8)
                                                            as 計上日       -- 0
                , CASE
                    WHEN plan_no = '' THEN '&nbsp;'
                    ELSE plan_no
                  END                                       as 摘　要       -- 1
                , CASE
                    WHEN out_id = '1' THEN CAST(stock_mv AS TEXT)
                    WHEN out_id = '2' THEN CAST(stock_mv AS TEXT)
                    ELSE '&nbsp;'
                  END                                       as 出庫数       -- 2
                , CASE
                    WHEN in_id = '1' THEN CAST(stock_mv AS TEXT)
                    WHEN in_id = '2' THEN CAST(stock_mv AS TEXT)
                    ELSE '&nbsp;'
                  END                                       as 入庫数       -- 3
                , CASE
                    WHEN out_id = '1' THEN nk_stock  - stock_mv + tnk_stock
                    WHEN out_id = '2' THEN tnk_stock - stock_mv + nk_stock
                    WHEN in_id  = '1' THEN nk_stock  + stock_mv + tnk_stock
                    WHEN in_id  = '2' THEN tnk_stock + stock_mv + nk_stock
                    ELSE nk_stock + tnk_stock
                  END                                       as 合計在庫     -- 4
                , den_kubun                                 as 区分         -- 5
                , CASE
                    WHEN den_no = '' THEN '&nbsp;'
                    ELSE den_no
                  END                                       as 伝票番号     -- 6
                , CASE
                    WHEN out_id = '2' THEN tnk_stock - stock_mv
                    WHEN in_id  = '2' THEN tnk_stock + stock_mv
                    ELSE tnk_stock
                  END                                       as 栃木在庫     -- 7
                , CASE
                    WHEN out_id = '1' THEN nk_stock  - stock_mv
                    WHEN in_id  = '1' THEN nk_stock  + stock_mv
                    ELSE nk_stock
                  END                                       as ＮＫ在庫     -- 8
                , CASE
                    WHEN note = '' THEN '&nbsp;'
                    ELSE note
                  END                                       as 備　考       -- 9
            from
                parts_stock_history
            where
                parts_no='{$parts_no}'
                and
                upd_date>={$date_low}
                and
                upd_date<={$date_upp}
            order by
                parts_no DESC, upd_date DESC, serial_no DESC
";
$_SESSION['stock_history_query'] = $query . " limit {$view_rec}";
$query = $query . ' limit 0';
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $num = count($field);       // フィールド数取得
} else {
    $num = count($field);       // フィールド数取得
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
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    ファイル指定の場合
<script type='text/javascript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script type='text/javascript'>
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
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.form_name.element_name.select();
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
th {
    background-color: blue;
    color:            yellow;
    font-size:        12pt;
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
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
-->
</style>
</head>
<body style='overflow:hidden;' onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table width='100%' align='center' border='0'>
            <tr>
                <td nowrap class='pt12b' align='center'>
                    <?= $menu->out_caption(), "\n"?>
                </td>
            </tr>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table width='880' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th width='42' class='winbox' nowrap>No</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                    switch ($i) {
                    case 0:     // 計上日
                        echo "<th width='80' class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    case 1:     // 摘要
                        echo "<th width='80' class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    case 2:     // 出庫数
                    case 3:     // 入庫数
                    case 4:     // 合計在庫
                        echo "<th width='80' class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    case 5:     // 伝票区分
                        echo "<th width='40' class='winbox' nowrap style='font-size:10pt;'>{$field[$i]}</th>\n";
                        break;
                    case 6:     // 伝票番号
                    case 7:     // 栃木在庫
                    case 8:     // ＮＫ在庫
                        echo "<th width='80' class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    case 9:     // 備考
                        echo "<th width='120' class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    default:    // その他があれば
                        echo "<th class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    }
                }
                ?>
                    <th width='5' class='winbox' nowrap>&nbsp;</th>     <!-- スクロールバーの幅を確保 -->
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <!-- iframeで表示 -->
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
                <?php echo "<iframe hspace='0' vspace='0' scrolling='yes' src='", $menu->out_frame('フレームで表示'), "?plan_no=", urlencode($plan_no), $material, "&id={$uniq}#last' name='parts_stock_iframe' align='center' width='882' height='560' title='parts_stock_history'>\n" ?>
                    部品在庫経歴の明細を表示します。
                </iframe>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
