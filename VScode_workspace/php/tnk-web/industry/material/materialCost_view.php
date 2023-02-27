<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の照会 (工程明細)                                                //
// Copyright (C) 2003-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/19 Created   metarialCost_view.php                               //
// 2003/12/20 リンクで呼ばれた場合の頁キープ機能を追加 ?page_keep=1         //
// 2003/12/22 工程番号が２以上の場合は部品番号・部品名を空白にする          //
// 2004/01/06 リターンアドレスより先に認証チェックをしてNGならTOP Indexへ   //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/05/25 部品表の表示をレベル表示に対応 Level5まで page=300へ変更      //
// 2004/06/03 上記に伴いORDER BY par_parts ASC, parts_no ASC, pro_no ASC へ //
//         親番号を備考へ変更し無償・有償・客先支給・親番号・なしの順に表示 //
// 2004/10/07 $search = sprintf("where plan_no='%s' and par_parts=''", $plan_no);
//            $search = sprintf("where plan_no='%s'", $plan_no); へ変更     //
// 2005/02/07 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
//            $search = sprintf("where plan_no='%s'", $plan_no) →          //
//            "where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no)へ  //
//            colspan='$num+1' → colspan='$num+2' へ変更                   //
//            ＊重要 コメント内でもphpタグは使用不可                        //
// 2005/05/11 総材料費の変更日・登録日を表示追加(last_date, regdate)        //
// 2005/06/13 自動登録品の表示追加 $regdate を書式フォーマット              //
// 2005/06/17 部品番号クリックで買掛実績の照会へジャンプする機能追加        //
// 2006/03/15 レベルで && ($res[$r2-1][4] == 'NK' || $res[$r2-1][4] == 'MT')//
//            2レベル以下が抜けているのを修正                               //
// 2006/03/16 更にレベル表示をロジック方式からDBのストアードプロシージャーへ//
// 2006/05/17 material_cost_level_as()を追加してAS/400のリストと合わせた    //
// 2006/10/06 買掛実績を呼出していたのを在庫経歴を呼出すように変更          //
//            その時 $_SESSION['material_plan_no'] をセットするのを忘れずに //
// 2007/02/20 parts/からparts/parts_stock_history/parts_stock_view.phpへ変更//
// 2007/03/24 上記のparts_stock_view.php → parts_stock_history_Main.php へ //
//            <tr>タグにアンカーを立ててもNN7.1では無効なため<td>タグへ変更 //
//            更にNN7.1では set_focus()でアンカーへのジャンプが無効になる   //
// 2007/09/18 E_ALL | E_STRICT へ変更  ZZで25.60追加                        //
// 2007/09/28 組立機の賃率計算を参考だけでなく契約賃率へ反映(端末に合わせる)//
// 2007/09/29 25.60 → 57.00 でシミュレーションして元に戻した(コメントアウト//
// 2007/10/01 総材料費のフッター部分の明細にrowspan='9'を追加               //
// 2008/11/12 賃率変更による仕切価格変更の為賃率を                          //
//            カプラ=57.00 リニア=44.00 に変更                         大谷 //
// 2015/05/28 機工製品の総材料費登録に対応                             大谷 //
// 2016/08/08 mouseOverを追加                                          大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);     // E_ALL='2047' debug 用
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
$menu->set_site(30, 20);                    // site_index=30(生産メニュー) site_id=20(総材料費の照会 計画番号)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 の 照 会 (工程明細)');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('買掛実績照会',   INDUST . 'payable/act_payable_view.php');
$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
//////////// リターンアドレスへのGETデーターセット
$menu->set_retGET('page_keep', 'on');

if (isset($_REQUEST['material'])) {     // 再帰呼出のチェック
    $menu->set_retGET('page_keep', $_REQUEST['material']);
    $parts_no = @$_SESSION['stock_parts'];
    if (isset($_REQUEST['row'])) {
        $row_no   = $_REQUEST['row'];   // 前回呼出した行番号
    } else {
        $row_no = -1;       // 在庫経歴からの再帰呼出でない場合
    }
} else {
    $parts_no = '';
    $row_no   = '-1';       // 単体で照会された時
}

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// パラメーターの取得
if ( !(isset($_POST['forward']) || isset($_POST['backward']) || isset($_GET['page_keep'])) ) {
    if (isset($_GET['plan_no'])) {
        $_SESSION['plan_no'] = $_GET['plan_no'];    // 下で使うため保存しておく
        $_SESSION['assy_no'] = $_GET['assy_no'];    // 下で使うため保存しておく
    }
}

//////////// 一頁の行数
define('PAGE', '300');

//////////// 計画番号・製品番号をセッションから取得
if (isset($_SESSION['plan_no'])) {
    $plan_no = $_SESSION['plan_no'];
    $_SESSION['material_plan_no'] = $plan_no;   // 総材料費用セッションに保存(マーキングとアンカーセットのため)
    if (substr($plan_no, 0, 1) == 'C') {
        /******** 特注・標準の項目追加 *********/
        $sql2 = "
            SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $sc = '';
        getUniResult($sql2, $sc);
        if ($sc == 'SC') {
            define('RATE', 25.60);  // カプラ特注
        } else {
            $sql2 = "
                SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
            ";
            $kan = '';
            getUniResult($sql2, $kan);
            if ($kan < 20071001) {
                define('RATE', 25.60);  // カプラ標準 2007/10/01価格改定以前
            } elseif ($kan < 20110401) {
                define('RATE', 57.00);  // カプラ標準 2007/10/01価格改定以降
            } else {
                define('RATE', 45.00);  // カプラ標準 2011/04/01価格改定以降
            }
        }
    } elseif (substr($plan_no, 0, 2) == 'ZZ') {
        if (substr($assy_no, 0, 1) == 'C') {
            if ($kan < 20110401) {
                define('RATE', 57.00);  // カプラ標準 2007/10/01価格改定以降
            } else {
                define('RATE', 45.00);  // カプラ標準 2011/04/01価格改定以降
            }
        } elseif (substr($assy_no, 0, 1) == 'L') {
            if ($kan < 20110401) {
                define('RATE', 44.00);  // リニア 2007/10/01価格改定以降
            } else {
                define('RATE', 53.00);  // リニア 2011/04/01価格改定以降
            }
        } else {
            define('RATE', 50.00);  // ツール
        }
    } elseif (substr($plan_no, 0, 1) == 'L') {
        $sql2 = "
            SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $kan = '';
        getUniResult($sql2, $kan);
        if ($kan < 20081001) {
            define('RATE', 37.00);  // リニア 2008/10/01価格改定以前
        } elseif ($kan < 20110401) {
            define('RATE', 44.00);  // リニア 2008/10/01価格改定以降
        } else {
            define('RATE', 53.00);  // リニア 2011/04/01価格改定以降
        }
    } else {
        $sql2 = "
            SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $kan = '';
        getUniResult($sql2, $kan);
        define('RATE', 50.00);  // ツール
    }
} else {
    $_SESSION['s_sysmsg'] .= '計画番号が指定されてない！';      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());      // 直前の呼出元へ戻る
    exit();
}
if (isset($_SESSION['assy_no'])) {
    $assy_no = $_SESSION['assy_no'];
} else {
    $_SESSION['s_sysmsg'] .= '製品番号が指定されてない！';      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());      // 直前の呼出元へ戻る
    exit();
}

//////////// 製品名の取得
$query = "select midsc from miitem where mipn='{$assy_no}'";
if ( getUniResult($query, $assy_name) <= 0) {           // 製品名の取得
    $_SESSION['s_sysmsg'] .= "製品名の取得に失敗";      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());      // 直前の呼出元へ戻る
    exit();
}

//////////// 表題の設定
$menu->set_caption("計画番号：{$plan_no}  製品番号：{$assy_no}  製品名：{$assy_name}");

//////////// SQL 文の where 句を 共用する
$search = sprintf("where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);  // 2004/10/07  and par_parts=''を削除
// $search = '';

//////////// 合計レコード数・総材料費の取得     (対象データの最大数をページ制御に使用)
$query = sprintf("select count(*), sum(Uround(pro_price * pro_num, 2)) from material_cost_history %s", $search);
$res_sum = array();
if ( getResult2($query, $res_sum) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
}
$maxrows = $res_sum[0][0];
$sum_kin = $res_sum[0][1];

$query = sprintf("select sum(Uround(pro_num * pro_price, 2)) from material_cost_history
                    %s and intext='0'", $search);
if ( getUniResult($query, $ext_kin) <= 0) {  // 内作の総材料費
    $_SESSION['s_sysmsg'] .= "外作総材料費の取得に失敗";      // .= メッセージを追加する
}
$query = sprintf("select sum(Uround(pro_num * pro_price, 2)) from material_cost_history
                    %s and intext='1'", $search);
if ( getUniResult($query, $int_kin) <= 0) {  // 外作の総材料費
    $_SESSION['s_sysmsg'] .= "内作総材料費の取得に失敗";      // .= メッセージを追加する
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


//////////// SQL 文の where 句を 共用する
$search = sprintf("where plan_no='%s' and par_parts=''", $plan_no);  // 2004/10/07 $searchを共有出来ないので追加
//////////// 計画番号単位の工程明細の作表
$query = "
    SELECT  
        mate.last_user
                    AS  \"Level\"               -- 0
        ,parts_no   as 部品番号                 -- 1
        ,trim(substr(midsc,1,25))
                    as 部品名                   -- 2
        ,pro_num    as 使用数                   -- 3
        ,pro_no     as 工程                     -- 4
        ,pro_mark   as 工程名                   -- 5
        ,pro_price  as 工程単価                 -- 6
        ,Uround(pro_num * pro_price, 2)
                    as 工程金額                 -- 7
        ,CASE
            WHEN intext = '0' THEN '外作'
            WHEN intext = '1' THEN '内作'
            ELSE intext
        END         as 内外作                   -- 8
        ,CASE
            WHEN pro_mark = 'NK'
                AND pro_price = 0 THEN '無償'
            WHEN pro_mark = 'NK'
                AND pro_price > 0 THEN '有償'
            WHEN par_parts = ''
                AND pro_price = 0 THEN '客先支給'
            WHEN par_parts != ''  THEN par_parts
            ELSE par_parts
        END         as 備　考                   -- 9 親番号→備考へ変更
    FROM
        material_cost_level_as('{$plan_no}') AS mate
    LEFT OUTER JOIN
         miitem ON parts_no=mipn 
    OFFSET {$offset} LIMIT 300
";
$res_view   = array();
$field_view = array();
if (($rows_view = getResultWithField2($query, $field_view, $res_view)) <= 0) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'><br>現在未登録です！</font>";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());      // 直前の呼出元へ戻る
    exit();
} else {
    ////// フィールド数の設定
    $num_view = count($field_view);       // フィールド数取得
}

/////////// 組立費 & 合計金額 & 変更日・登録日の取得
                                // 'YYYY-MM-DD HH24:MI:SS'
$query = "SELECT m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate
                , to_char(last_date AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI')   AS 変更日
                , to_char(regdate AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS')     AS 登録日
                , m_time + g_time AS ma_time -- 手作業工数合計 2007/09/28 ADD
            FROM material_cost_header WHERE plan_no='{$plan_no}'";
$res_time = array();
if ( getResult2($query, $res_time) > 0 ) {
    $m_time = $res_time[0][0];
    $m_rate = $res_time[0][1];
    $a_time = $res_time[0][2];
    $a_rate = $res_time[0][3];
    $g_time = $res_time[0][4];
    $g_rate = $res_time[0][5];
    ///// 合計 組立費(社内用)
    $m_price = Uround($m_time * $m_rate, 2);
    $a_price = Uround($a_time * $a_rate, 2);
    $g_price = Uround($g_time * $g_rate, 2);
    $assy_int_price = ( $m_price + 
                        $a_price + 
                        $g_price );
    ///// 対日東工器 契約賃率の組立費
    $assy_time  = $res_time[0][10];     // 2007/09/28 手作業と外注の合計に変更(m_time + a_time)
    $assy_rate  = $res_time[0][7];
    // $assy_rate  = 57.00;                // 2007/09/29 25.60→57.00でシミュレーション
    $assy_price = Uround($assy_time * $assy_rate, 2);
    $auto_price = Uround($a_time * $a_rate, 2);     // 2007/09/28 自動組立機の組立費を追加
    ///// 変更日・登録日
    $last_date = $res_time[0][8];
    $regdate   = $res_time[0][9];
    if (substr($regdate, 11, 8) == '00:00:00') {
        $regdate = "<span style='color:red;'>自動登録</span>(" . substr($regdate, 0, 10) . ')';
    }
} else {
    $m_time = 0;
    $m_rate = 0;
    $a_time = 0;
    $a_rate = 0;
    $g_time = 0;
    $g_rate = 0;
    $assy_int_price = 0;
    $assy_time  = 0;
    $assy_rate  = RATE;
    $assy_price = 0;
    $auto_price = 0;    // 2007/09/28 ADD
}

/////////// HTML Header を出力してキャッシュを制御
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

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
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

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.mhForm.backwardStack.focus();  // IE/NN 両対応
    // document.entry_form.parts_no.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.entry_form.parts_no.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font:normal     9pt;
    font-family:    monospace;
}
.pt10 {
    font:normal     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.caption_font {
    font-size:      11pt;
    color:          blue;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-wieght:        bold;
    font-family:        monospace;
}
.parts_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     left;
}
.pro_num_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     center;
}
.price_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     right;
}
.entry_font {
    font-size:      11pt;
    font-weight:    normal;
    color:          red;
}
a:hover {
    background-color: gold;
}
a {
    font-size:   10pt;
    font-weight: bold;
    color:       blue;
}
.winbox {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style:           solid;
    border-width:           1px;
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
<?php echo $menu->out_title_border()?>
        
        <div>
        <span class='entry_font'>総材料費：<?php echo number_format($sum_kin + $assy_price + $auto_price, 2) ."\n" ?></span>
        <span class='pt10' style='color:gray;'>(現在の契約賃率：<?php echo number_format(RATE, 2) ?>)</span>
        <span class='entry_font'>　社内用 総材料費：<?php echo number_format($sum_kin + $assy_int_price, 2) ."\n" ?></span>
        <span class='pt10' style='color:gray;'>　変更日：<?php echo $last_date ?></span>
        <span class='pt10' style='color:gray;'>　登録日：<?php echo $regdate ?></span>
        </div>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption() . "\n" ?>
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
        <table width='98%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- テーブル ヘッダーの表示 -->
            <tr>
                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->
            <?php
            for ($i=0; $i<$num_view; $i++) {             // フィールド数分繰返し
            ?>
                <th class='winbox' nowrap><?php echo $field_view[$i] ?></th>
            <?php
            }
            ?>
            </tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 -->
                    <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
            <?php
            for ($r=0; $r<$rows_view; $r++) {
                if ($row_no == $r) {
                    echo "<tr style='background-color:#ffffc6;'>\n";
                    echo "<td class='winbox' nowrap align='right'>\n";
                    echo "    <a name='mark'><div class='pt10b'>", ($r + $offset + 1), "</div></a>\n";
                    echo "</td>    <!-- 行ナンバーの表示 -->\n";
                } else {
                    echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    echo "<td class='winbox' nowrap align='right'>\n";
                    echo "    <div class='pt10b'>", ($r + $offset + 1), "</div>\n";
                    echo "</td>    <!-- 行ナンバーの表示 -->\n";
                }
                for ($i=0; $i<$num_view; $i++) {         // レコード数分繰返し
                    if ($res_view[$r][9] == '') {
                        switch ($i) {   // 親部品なら
                        case 0:    // レベル
                            echo "<td class='winbox' nowrap align='left'><div class='pt10b'>" . $res_view[$r][$i] . "</div></td>\n";
                            break;
                        case 1:     // 部品番号
                            if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'><a href='", $menu->out_action('在庫経歴'), "?parts_no=", urlencode($res_view[$r][$i]), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='application' style='text-decoration:none;'>{$res_view[$r][$i]}</a></div></td>\n";
                            }
                            break;
                        case 2:     // 部品名
                            if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                echo "<td class='winbox' nowrap width='300 align='left'><div class='pt9'>&nbsp;</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='300 align='left'><div class='pt9'>{$res_view[$r][$i]}</div></td>\n";
                            }
                            break;
                        case  3:    // 使用数
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>" . number_format($res_view[$r][$i], 4) . "</div></td>\n";
                            break;
                        case  6:    // 工程単価
                        case  7:    // 工程金額
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>" . number_format($res_view[$r][$i], 2) . "</div></td>\n";
                            break;
                        case  9:    // 備考
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                            break;
                        default:    // 工程・工程名・内外作
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res_view[$r][$i]}</div></td>\n";
                        }
                    } else {            // 親部品が指定されている子部品なら
                        switch ($i) {
                        case 0:    // レベル
                            echo "<td class='winbox' nowrap align='left'><div class='pt10b'>" . $res_view[$r][$i] . "</div></td>\n";
                            break;
                        case 1:     // 部品番号
                            if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                            } else {
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center'><div class='pt9'>
                                        <a href='", $menu->out_action('在庫経歴'), "?parts_no=", urlencode($res_view[$r][$i]), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='application' style='text-decoration:none;'>
                                            {$res_view[$r][$i]}
                                        </a></div></td>\n";
                            }
                            break;
                        case 2:     // 部品名
                            if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap width='300 align='left'><div class='pt9'>&nbsp;</div></td>\n";
                            } else {
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap width='300 align='left'><div class='pt9'>{$res_view[$r][$i]}</div></td>\n";
                            }
                            break;
                        case  3:    // 使用数
                            echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='right'><div class='pt9'>" . number_format($res_view[$r][$i], 4) . "</div></td>\n";
                            break;
                        case  6:    // 工程単価
                        case  7:    // 工程金額
                            echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='right'><div class='pt9'>" . number_format($res_view[$r][$i], 2) . "</div></td>\n";
                            break;
                        default:    // 工程・工程名・内外作
                            echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center'><div class='pt9'>{$res_view[$r][$i]}</div></td>\n";
                        }
                    }
                }
                echo "</tr>\n";
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <table width='98%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='0'>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td rowspan='9' class='winbox pt10' width='45%'>
                    &nbsp;
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    内作材料費：<?php echo number_format($int_kin, 2) ."\n" ?>  
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    外作材料費：<?php echo number_format($ext_kin, 2) ."\n" ?>  
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    合計材料費：<?php echo number_format($sum_kin, 2) ."\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    合計手作業工数：<?php echo number_format($assy_time, 3) ."\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    　 契約賃率：<?php echo number_format($assy_rate, 2) ."\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    　組立費：<?php echo number_format($assy_price, 2) ."\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    自動機工数：<?php echo number_format($a_time, 3) ."\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    自動機賃率：<?php echo number_format($a_rate, 2) ."\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    　組立費：<?php echo number_format($auto_price, 2) ."\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td colspan='3' class='winbox pt10' nowrap align='right' style='color:red;'>
                    総材料費：<?php echo number_format($sum_kin + $assy_price + $auto_price, 2) ."\n" ?>
                </td>
            </tr>
            <tr>
                <td colspan='3' class='winbox pt10' nowrap align='right'>
                    &nbsp;
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    (参考：社内賃率)　
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    組立費：<?php echo number_format($assy_int_price, 2) ."\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    　総材料費：<?php echo number_format($sum_kin + $assy_int_price, 2) ."\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    手作業工数：<?php echo number_format($m_time, 3), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    手作業賃率：<?php echo number_format($m_rate, 2), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    手作業金額：<?php echo number_format($m_price, 2), "\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    自動機工数：<?php echo number_format($a_time, 3), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    自動機賃率：<?php echo number_format($a_rate, 2), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    自動機金額：<?php echo number_format($a_price, 2), "\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    外注工数：<?php echo number_format($g_time, 3), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    外注賃率：<?php echo number_format($g_rate, 2), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    外注金額：<?php echo number_format($g_price, 2), "\n" ?>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
