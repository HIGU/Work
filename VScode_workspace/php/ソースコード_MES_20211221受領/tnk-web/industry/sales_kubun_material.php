<?php
//////////////////////////////////////////////////////////////////////////////
// 仕切単価の登録区分別 総材料費との比較表                                  //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/29 Created  sales_kubun_material.php                             //
// 2004/11/01 全ての区分で検索出来る機能を追加                              //
// 2004/11/05 総材料費に対する仕切単価の比率表示を追加                      //
// 2006/05/10 2006/04/01〜仕切単価一部ＵＰに伴い登録区分の追加 Ｆ           //
// 2006/11/29 2006/11/01〜真鍮･ステンの材料費分を仕切単価に反映(UP)区分G    //
//            合わせて１頁行数を指定できるように変更(全リストのコピー用)    //
// 2007/10/26 E_ALL | E_STRICTへ php標準タグへ 仕切単価改訂'H'を追加20071026//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(サイトを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('区分指定の仕切単価と総材料費の比較');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

/**********************
////////////// リターンアドレス設定(新方式)
// セッション変数名はスクリプト名から拡張子部分を除いたものに'_ret'を付加する事でUNIQUE性を持たせる。
if (isset($_SESSION['template_ret'])) {
    $url_referer = $_SESSION['template_ret'];   // 呼出元で保存してあるリターンアドレスを取得
} else {
    $url_referer = $_SERVER['HTTP_REFERER'];    // error 回避のため
    $_SESSION['template_ret'] = $url_referer;
}
**********************/

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

if (isset($_REQUEST['reg_kubun'])) {
    $_SESSION['reg_kubun'] = $_REQUEST['reg_kubun'];
    $reg_kubun = $_SESSION['reg_kubun'];
} else {
    if (isset($_SESSION['reg_kubun'])) {
        $reg_kubun = $_SESSION['reg_kubun'];
    } else {
        $reg_kubun = ' ';                               // Default
    }
}
//////////// 表題の設定
$menu->set_caption("された仕切単価と総材料費の比較");
//////////// SQL 文の where 句を 共用する
$search = sprintf("WHERE reg_kubun='%s'", $reg_kubun);

//////////// 一頁の行数
if (isset($_REQUEST['pageRows']) && $_REQUEST['pageRows'] > 0 && $_REQUEST['pageRows'] <= 5000) {
    define('PAGE', $_REQUEST['pageRows']);
} else {
    define('PAGE', '25');
}
$pageRows = PAGE;

//////////// 合計レコード数取得     (対象テーブルの最大数をページ制御に使用)
$query = sprintf('SELECT count(*) FROM sales_price_nk %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= '合計レコード数の取得に失敗<br>DBの接続を確認！';  // .= メッセージを追加する
}
//////////// ページオフセット設定(offsetは使用する時に名前を変更 例：sales_offset)
if ( isset($_POST['forward']) ) {                       // 次頁が押された
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // 次頁が押された
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {                // 現在のページを維持する GETに注意
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['offset'];

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$query = sprintf("
        SELECT
            sal.parts_no            AS 製品番号,                -- 0
            substr(midsc, 1, 26)    AS 製品名,                  -- 1
            sal.regdate             AS 登録日,                  -- 2
            CASE
                WHEN trim(sal.note) = '' THEN
                    '---'
                ELSE
                    sal.note
            END                     AS \"特注=S\",              -- 3
            sal.price               AS 仕切単価,                -- 4
            (SELECT mate.sum_price + Uround(mate.assy_time * assy_rate, 2) FROM material_cost_header AS mate WHERE mate.assy_no=sal.parts_no ORDER BY mate.regdate DESC LIMIT 1)
                                    AS 総材料費                 -- 5
        FROM
            sales_price_nk          AS sal
        LEFT OUTER JOIN
            miitem                  ON (sal.parts_no=mipn)
        %s      -- ここに where句の and を挿入できる
        ORDER BY sal.parts_no ASC
        offset %d LIMIT %d
    ", $search, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'データがありません。';
    // header('Location: ' . $menu->out_retUrl());                   // 直前の呼出元へ戻る
    // exit();
}
$num = count($field);       // フィールド数取得

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
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'>
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
function set_focus() {
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
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
    background-color: yellow;
    color:            blue;
    font-size:        10pt;
    font-weight:      bold;
    font-family:      monospace;
}
<?php
/************
table {
    border-top:    1.0pt outset #bdaa90;
    border-right:  1.0pt outset white;
    border-bottom: 1.0pt outset white;
    border-left:   0.5pt outset #bdaa90;
}
td {
    border-top:    1.0pt outset #bdaa90;
    border-right:  1.0pt outset white;
    border-bottom: 1.0pt outset white;
    border-left:   0.5pt outset #bdaa90;
}
**************/
?>
.tuborgbox {
    border-style: solid;
    border-width: 1px;
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
    border-right-color: #AAAAAA;
    border-bottom-color: #AAAAAA;
}
.tuborgboxsimple {
    border-style: solid;
    border-width: 1px;
    border-color: #AAAAAA;
}
.rappsbox {
    border-style: solid;
    border-width: 1px;
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
    border-right-color: #DFDFDF;
    border-bottom-color: #DFDFDF;
}
.rappsboxsimple {
    border-style: solid;
    border-width: 1px;
    border-color: #DFDFDF;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <!--
            <div style='position: absolute; top: 80; left: 7; width: 185; height: 31'>
                絶対値で位置指定
            </div>
        -->
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td align='center' class='caption_font'>
                        <select name='reg_kubun' class='ret_font' onChange='document.page_form.submit()' style='color:white; background-color:blue;'>
                            <option value=' ' <?php if($reg_kubun == ' ') echo 'selected'; ?>>手入力(手動入力)</option>
                            <option value='1' <?php if($reg_kubun == '1') echo 'selected'; ?>>総材料費の1.21倍</option>
                            <option value='2' <?php if($reg_kubun == '2') echo 'selected'; ?>>総材料費の1.05倍</option>
                            <option value='3' <?php if($reg_kubun == '3') echo 'selected'; ?>>粗　利　配　分</option>
                            <option value='4' <?php if($reg_kubun == '4') echo 'selected'; ?>>原　価　据　置</option>
                            <option value='A' <?php if($reg_kubun == 'A') echo 'selected'; ?>>４％コストダウン</option>
                            <option value='C' <?php if($reg_kubun == 'C') echo 'selected'; ?>>３％コストダウン</option>
                            <option value='D' <?php if($reg_kubun == 'D') echo 'selected'; ?>>７％コストダウン</option>
                            <option value='E' <?php if($reg_kubun == 'E') echo 'selected'; ?>>６％コストダウン</option>
                            <option value='F' <?php if($reg_kubun == 'F') echo 'selected'; ?>>コストアップ</option>
                            <option value='G' <?php if($reg_kubun == 'G') echo 'selected'; ?>>真鍮・SUS材アップ</option>
                            <option value='H' <?php if($reg_kubun == 'H') echo 'selected'; ?>>2007/10/01仕切改訂</option>
                        </select>
                        <?php echo $menu->out_caption(), '&nbsp;&nbsp;&nbsp;合計件数＝', number_format($maxrows), "件\n" ?>
                    </td>
                    <td align='left' class='caption_font'>
                        頁行数
                        <select name='pageRows' class='ret_font' onChange='document.page_form.submit()'>
                            <option value='25'<?php if($pageRows == '25') echo ' selected'; ?>>&nbsp;&nbsp;25</option>
                            <option value='100'<?php if($pageRows == '100') echo ' selected'; ?>>&nbsp;100</option>
                            <option value='500'<?php if($pageRows == '500') echo ' selected'; ?>>&nbsp;500</option>
                            <option value='1000'<?php if($pageRows == '1000') echo ' selected'; ?>>1000</option>
                            <option value='3000'<?php if($pageRows == '3000') echo ' selected'; ?>>3000</option>
                        </select>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th nowrap width='30'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                    switch ($i) {
                    case 0: $w=60;  break;
                    case 1: $w=200; break;
                    case 2: $w=70;  break;
                    case 3: $w=50;  break;
                    case 4: $w=60;  break;
                    case 5: $w=60;  break;
                    default:$w=60;  break;
                    }
                    echo "<th nowrap width='{$w}'>", $field[$i], "</th>\n";
                }
                ?>
                    <th nowrap width='50'>率</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr>\n";
                    echo "    <td nowrap class='pt10b' align='right'>", ($r + $offset + 1), "</td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                        case 1:
                            echo "<td nowrap align='left' class='pt9'>", $res[$r][$i], "</td>\n";
                            break;
                        case 2:
                            echo "<td nowrap align='center' class='pt9'>", format_date($res[$r][$i]), "</td>\n";
                            break;
                        case 4:     // 仕切単価
                        case 5:     // 総材料費
                            if ($res[$r][$i]) {
                                echo "<td nowrap align='right' class='pt9'>", number_format($res[$r][$i], 2), "</td>\n";
                            } else {
                                echo "<td nowrap align='right' class='pt9'>---</td>\n";
                            }
                            break;
                        default:
                            echo "<td nowrap align='center' class='pt9'>", $res[$r][$i], "</td>\n";
                        }
                        // <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    if ( ($res[$r][4] != 0) && ($res[$r][5]) ) {   // 率を表示
                        echo "<td nowrap align='right' class='pt9'>", number_format(Uround($res[$r][4] / $res[$r][5], 4) * 100, 2), "</td>\n";
                    } else {
                        echo "<td nowrap align='center' class='pt9'>---</td>\n";
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
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
