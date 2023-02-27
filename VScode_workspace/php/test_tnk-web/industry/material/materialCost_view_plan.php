<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の照会  計画番号の入力・確認 form                                //
// Copyright (C) 2003-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/19 Created   metarialCost_view_plan.php                          //
// 2004/04/07 タイトル名に計画番号指定によるを追記                          //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2005/02/07 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/06/08 materialCost_view_assy.php のソースをカスタマイズ             //
// 2005/06/08 PostgreSQL8.0で where plan_no like '%{$plan}%'→'{$plan}%'    //
//            へ変更すれば Index Scan になるため変更した。                  //
// 2005/09/07 MenuON/Offで$_SESSION['material_max']等がNotiseになるのを@へ  //
// 2007/03/07 総材料費等のリンクをクリックして戻った時に行マーカー追加 recNo//
// 2007/03/24 phpのショートカットを中止 Ajaxにも$uniqを追加 NN用だが効果なし//
// 2019/05/31 組立費の計算が、すべての工数を足して手作業賃率を掛けるように  //
//            なったいたので訂正(Assyの入力の方と同じ)                 大谷 //
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
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 20);                    // site_index=30(生産メニュー) site_id=20(総材料費の照会 計画番号)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 の 照 会 (計画番号指定)');
//////////// 表題の設定
$menu->set_caption('計画番号の入力');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費明細',   INDUST . 'material/materialCost_view.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = 'ID=' . uniqid('target');

//////////// 初回時のセッションデータ保存   次頁・前頁を軽くするため
if (! (isset($_POST['forward']) || isset($_POST['backward']) || isset($_GET['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    if (isset($_POST['plan'])) {
        $plan = $_POST['plan'];
        $query = "select count(*)
                from
                    material_cost_header
                where plan_no like '{$plan}%'";
        if (getUniResult($query, $maxrows) <= 0) {
            $_SESSION['s_sysmsg'] = '合計レコード数の取得に失敗';
        } else {
            $_SESSION['material_max'] = $maxrows;
        }
        $_SESSION['mate_plan'] = $_POST['plan'];
    }
} else {        // 次頁・前頁・頁保存 の時は
    $maxrows = @$_SESSION['material_max'];       // 合計レコード数を復元
    $_POST['plan'] = @$_SESSION['mate_plan'];    // ポストデータをエミュレート
}

//////////// 一頁の行数
if (isset($_SESSION['material_page'])) {
    define('PAGE', $_SESSION['material_page']);
} else {
    define('PAGE', 23);
}

//////////// ページオフセット設定
if ( isset($_POST['forward']) ) {                       // 次頁が押された
    $_SESSION['mate_offset'] += PAGE;
    if ($_SESSION['mate_offset'] >= $maxrows) {
        $_SESSION['mate_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // 次頁が押された
    $_SESSION['mate_offset'] -= PAGE;
    if ($_SESSION['mate_offset'] < 0) {
        $_SESSION['mate_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_POST['page_keep']) ) {               // 現在のページを維持する
    $offset = $_SESSION['mate_offset'];
} elseif ( isset($_GET['page_keep']) ) {                // 現在のページを維持する
    $offset = $_SESSION['mate_offset'];
} else {
    $_SESSION['mate_offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['mate_offset'];

////////////// 自分のポストデータをチェック
if (isset($_POST['plan'])) {
    $plan = $_POST['plan'];
    $query = "select mate.assy_no                                               as 製品番号     -- 0
                    , mate.plan_no                                              as 計画番号     -- 1
                    , trim(substr(item.midsc, 1, 32))                           as 製品名       -- 2
                    , asse.kanryou                                              as 計画日       -- 3
                    , asse.kansei                                               as 完成数       -- 4
                    , mate.ext_price                                            as 外作費       -- 5
                    , mate.int_price                                            as 内作費       -- 6
                    , Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                                                AS 組立費       -- 7
                    , Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) + sum_price
                                                                                AS 総材料費     -- 8
                from
                    material_cost_header as mate
                left outer join
                    assembly_schedule as asse
                using (plan_no)
                left outer join
                    miitem as item
                on (mate.assy_no=item.mipn)
                where plan_no like '{$plan}%' -- '%{$plan}%'
                order by 計画日 DESC
                offset $offset limit " . PAGE;
    $res = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "{$plan} ：では登録されていません！";
        unset($_POST['plan']);      // 照会の実行をリセット
    } else {
        $num = count($field);       // フィールド数取得
        for ($r=0; $r<$rows; $r++) {
            $res[$r][2] = mb_convert_kana($res[$r][2], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
        }
    }
} else {
    $plan = '';
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
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

function chk_plan_entry(obj) {
    obj.plan.value = obj.plan.value.toUpperCase();
    return true;
    /************************************
    if (obj.plan.value.length != 0) {
        if (obj.plan.value.length != 9) {
            alert("製品番号の桁数は９桁です。");
            obj.plan.focus();
            obj.plan.select();
            return false;
        } else {
            return true;
        }
    }
    alert('製品番号が入力されていません！');
    obj.plan.focus();
    obj.plan.select();
    return false;
    ***********************************/
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.entry_form.plan.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.entry_form.plan.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
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
    font-family:    monospace;
}
.plan_font {
    font-size:      16pt;
    font-weight:    bold;
    text-align:     left;
    font-family:    monospace;
}
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
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
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='entry_form' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return chk_plan_entry(this)'>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption() ?></div>
                    </td>
                    <td class='winbox' nowrap align='center'>
                        <input class='plan_font' type='text' name='plan' value='<?php echo $plan ?>' size='8' maxlength='8'>
                    </td>
                    <td class='winbox' nowrap align='center'>
                        <div class='pt10'>
                            <!-- <input class='pt11b' type='submit' name='conf' value='実行'> -->
                            番号は分かる範囲で入力後Enterを押すとインクリメンタルサーチします。
                        </div>
                    </td>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <?php if (isset($_POST['plan'])) { ?>
        
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
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                    echo "<th class='winbox' nowrap>{$field[$i]}</th>\n";
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", ($r + $offset + 1), "</div></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                        case 0:     // 製品番号
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 1:     // 計画番号
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 2:     // 製品名
                            echo "<td class='winbox' nowrap width='270' align='left'><div class='pt10'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 3:     // 計画日
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>", format_date($res[$r][$i]), "</div></td>\n";
                            break;
                        case 4:     // 完成数
                            echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 5:     // 外作費
                            echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        case 6:     // 内作費
                            echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        case 7:     // 組立費
                            echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        case 8:     // 総材料費
                            if ($res[$r][$i] == 0) {
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'><a href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}&{$uniq}\");location.replace(\"", $menu->out_action('総材料費明細'),
                                        "?plan_no=", urlencode("{$res[$r][1]}"), "&assy_no=", urlencode("{$res[$r][0]}"), "&{$uniq}",
                                        "\")' target='application' style='text-decoration:none;'>", number_format($res[$r][$i], 2), "</a></div></td>\n";
                            }
                            break;
                        default:    // その他
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        }
                        // <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    echo "</tr>\n";
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <?php } ?>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
