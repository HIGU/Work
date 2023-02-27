<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の照会  ASSY(製品)番号の入力form・一覧表照会                     //
// Copyright (C) 2004-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/04/07 Created   metarialCost_view_assy.php                          //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/06/01 GET用パラメーターに#がありえるため urlencode() を付加した。   //
// 2005/02/08 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/06/08 PostgreSQL8.0で where assy_no like '%{$assy}%'→'{$assy}%'    //
//            へ変更すれば Index Scan になるため変更した。                  //
// 2005/09/07 MenuON/Offで$_SESSION['material_max']等がNotiseになるのを@へ  //
// 2006/10/06 order by 計画日 DESC → ORDER BY assy_no ASC, 計画日 DESC に  //
//            変更。及び一覧に自動・手動登録の識別項目追加                  //
// 2006/12/04 基本ﾃｰﾌﾞﾙをmaterial_cost_header→assembly_completion_historyへ//
// 2007/03/07 総材料費等のリンクをクリックして戻った時に行マーカー追加 recNo//
//            phpのショートカットを中止                                     //
// 2007/08/31 ダイレクト呼出し対応のため$_POST/$_GET → $_REQUEST へ変更    //
//            $_SESSION['mate_offset']が他と競合するため$session->add_local //
// 2007/09/05 計画番号が指定されている場合に行マーカー表示追加              //
//            進捗状況報告のため一時的に MenuHeader(-1) へ変更              //
// 2007/09/14 最新総材料費の登録リンクを追加                                //
// 2007/09/28 Uround(assy_time * assy_rate, 2) →    自動機賃率を計算に追加 //
//    Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) //
// 2016/08/08 mouseOverを追加                                          大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);  // E_ALL='2047' debug 用
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
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
$menu = new MenuHeader(-1);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 23);                    // site_index=30(生産メニュー) site_id=20(総材料費の照会 計画番号)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 の 照 会 (ASSY番号指定)');
//////////// 表題の設定
$menu->set_caption('製品番号の入力');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費明細',   INDUST . 'material/materialCost_view.php');
$menu->set_action('総材料費assy登録',   INDUST . 'material/materialCost_entry_assy.php');

if (isset($_REQUEST['material'])) {     // 総材料費の未登録からの呼出対応
    $menu->set_retGET('page_keep', $_REQUEST['material']);
    $material = '?material=1';
} else {
    // $material = ''; 総材料費専用にするため常にページキープでmaterial=1 2007/08/31
    $menu->set_retGET('page_keep', 'on');
    $material = '?material=1';
}
//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

///// 他のアプリから計画番号指定で照会された場合に行マーカー表示
if (isset($_REQUEST['plan_no'])) {
    $plan_no = $_REQUEST['plan_no'];
} else {
    $plan_no = '';
}
//////////// 初回時のセッションデータ保存   次頁・前頁を軽くするため
if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    if (isset($_REQUEST['assy'])) {
        $assy = $_REQUEST['assy'];
        $query = "select count(*)
                from
                    -- material_cost_header
                    assembly_completion_history
                where assy_no like '{$assy}%'";
        if (getUniResult($query, $maxrows) <= 0) {
            $_SESSION['s_sysmsg'] = '合計レコード数の取得に失敗';
        } else {
            $_SESSION['material_max'] = $maxrows;
        }
        $_SESSION['mate_assy'] = $_REQUEST['assy'];
    }
} else {        // 次頁・前頁・頁保存 の時は
    $maxrows = @$_SESSION['material_max'];       // 合計レコード数を復元
    $_REQUEST['assy'] = @$_SESSION['mate_assy'];    // ポストデータをエミュレート
}

//////////// 一頁の行数
if (isset($_SESSION['material_page'])) {
    define('PAGE', $_SESSION['material_page']);
} else {
    define('PAGE', 23);
}

//////////// ページオフセット設定
$offset = $session->get_local('offset');
if ($offset == '') $offset = 0;         // 初期化
if ( isset($_REQUEST['forward']) ) {                       // 次頁が押された
    $offset += PAGE;
    if ($offset >= $maxrows) {
        $offset -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // 次頁が押された
    $offset -= PAGE;
    if ($offset < 0) {
        $offset = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {               // 現在のページを維持する
    $offset = $offset;
} elseif ( isset($_REQUEST['page_keep']) ) {                // 現在のページを維持する
    $offset = $offset;
} else {
    $offset = 0;                            // 初回の場合は０で初期化
}
$session->add_local('offset', $offset);

////////////// 自分のポストデータをチェック
if (isset($_REQUEST['assy'])) {
    $assy = $_REQUEST['assy'];
    $query = "SELECT hist.assy_no                                               AS 製品番号     -- 0
                    , hist.plan_no                                              AS 計画番号     -- 1
                    , trim(substr(item.midsc, 1, 21))                           AS 製品名       -- 2
                    , asse.kanryou                                              AS 計画日       -- 3
                    , asse.kansei                                               AS 完成累       -- 4
                    , mate.ext_price                                            AS 外作費       -- 5
                    , mate.int_price                                            AS 内作費       -- 6
                    , Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                                                AS 組立費       -- 7
                    , Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) + sum_price
                                                                                AS 総材料費     -- 8
                    , CASE
                        WHEN to_char(mate.regdate, 'HH24:MI:SS') = '00:00:00' THEN '自動'
                        WHEN mate.plan_no IS NULL THEN '未登録'
                        ELSE '手動'
                      END                                                       AS 登録         -- 9
                    , to_char(hist.comp_date, 'FM9999/99/99')                   AS 完成日       -- 10
                    , to_char(hist.comp_pcs, 'FM99,999')                        AS 完成数       -- 11
                FROM
                    assembly_completion_history AS hist
                LEFT OUTER JOIN
                    material_cost_header AS mate USING(plan_no)
                LEFT OUTER JOIN
                    assembly_schedule AS asse USING(plan_no)
                LEFT OUTER JOIN
                    miitem AS item ON (hist.assy_no=item.mipn)
                WHERE hist.assy_no LIKE '{$assy}%' -- '%{$assy}%'
                ORDER BY hist.assy_no DESC, hist.comp_date DESC --計画日 DESC
                OFFSET {$offset} LIMIT " . PAGE;
    $res = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "{$assy} ：では登録されていません！";
        unset($_REQUEST['assy']);      // 照会の実行をリセット
    } else {
        $num = count($field);       // フィールド数取得
        for ($r=0; $r<$rows; $r++) {
            $res[$r][2] = mb_convert_kana($res[$r][2], 'ka', 'UTF-8');   // 全角カナを半角カナへ
            $res[$r][2] = mb_substr($res[$r][2], 0, 21);    // マルチバイト対応で半角カナベースで21文字にする
        }
    }
} else {
    $assy = '';
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
-->

<script type='text/javascript' language='JavaScript'>
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

function chk_assy_entry(obj) {
    obj.assy.value = obj.assy.value.toUpperCase();
    return true;
    /************************************
    if (obj.assy.value.length != 0) {
        if (obj.assy.value.length != 9) {
            alert("製品番号の桁数は９桁です。");
            obj.assy.focus();
            obj.assy.select();
            return false;
        } else {
            return true;
        }
    }
    alert('製品番号が入力されていません！');
    obj.assy.focus();
    obj.assy.select();
    return false;
    ***********************************/
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.entry_form.assy.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.entry_form.assy.select();
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
.pt9y {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
    color:          teal;
}
.pt9r {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
    color:          red;
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
.assy_font {
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
            <form name='entry_form' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return chk_assy_entry(this)'>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption() ?></div>
                    </td>
                    <td class='winbox' nowrap align='center'>
                        <input class='assy_font' type='text' name='assy' value='<?php echo $assy ?>' size='9' maxlength='9'>
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
        
        <?php if (isset($_REQUEST['assy'])) { ?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self(), $material ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <a href='<?php echo $menu->out_action('総材料費assy登録'), '?assy=', urlencode($assy);?>'>最新総材料費の登録</a>
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
                $res[-1][0] = ''; $res[-1][1] = ''; ///// ダミー
                for ($r=0; $r<$rows; $r++) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo || $plan_no == $res[$r][1]) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", ($r + $offset + 1), "</div></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                        case 0:     // 製品番号
                            if ($res[$r-1][$i] == $res[$r][$i]) {
                                echo "<td class='winbox pt9' nowrap align='center'>〃</td>\n";
                            } else {
                                echo "<td class='winbox pt9' nowrap align='center'>{$res[$r][$i]}</td>\n";
                            }
                            break;
                        case 1:     // 計画番号
                            if ($res[$r-1][$i] == $res[$r][$i]) {
                                echo "<td class='winbox pt9' nowrap align='center'>〃</td>\n";
                            } else {
                                echo "<td class='winbox pt9' nowrap align='center'>{$res[$r][$i]}</td>\n";
                            }
                            break;
                        case 2:     // 製品名
                            if ($res[$r-1][1] == $res[$r][1]) {
                                echo "<td class='winbox pt10' nowrap width='150' align='center'>〃</td>\n";
                            } else {
                                echo "<td class='winbox pt10' nowrap width='150' align='left'>{$res[$r][$i]}</td>\n";
                            }
                            break;
                        case 3:     // 計画日
                            if ($res[$r-1][1] == $res[$r][1]) {
                                echo "<td class='winbox pt9' nowrap align='center'>〃</td>\n";
                            } else {
                                echo "<td class='winbox pt9' nowrap align='center'>", format_date($res[$r][$i]), "</td>\n";
                            }
                            break;
                        case 4:     // 完成累
                            if ($res[$r-1][1] == $res[$r][1]) {
                                echo "<td class='winbox pt9' nowrap width='45' align='center'>〃</td>\n";
                            } else {
                                echo "<td class='winbox pt9' nowrap width='45' align='right'>", number_format($res[$r][$i], 0), "</td>\n";
                            }
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
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'><a href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費明細'),
                                        "?plan_no=", urlencode("{$res[$r][1]}"), "&assy_no=", urlencode("{$res[$r][0]}"),
                                        "\")' target='application' style='text-decoration:none;'>", number_format($res[$r][$i], 2), "</a></div></td>\n";
                            }
                            break;
                        case 9:     // 登録
                            if ($res[$r][$i] == '手動') {
                                echo "<td class='winbox pt9y' nowrap align='center'>{$res[$r][$i]}</td>\n";
                            } elseif($res[$r][$i] == '未登録') {
                                echo "<td class='winbox pt9r' nowrap align='center'>{$res[$r][$i]}</td>\n";
                            } else {
                                echo "<td class='winbox pt9' nowrap align='center'>{$res[$r][$i]}</td>\n";
                            }
                            break;
                        case 10:    // 完成日
                            echo "<td class='winbox pt9' nowrap align='right'>{$res[$r][$i]}</td>\n";
                            break;
                        case 11:    // 完成数
                            echo "<td class='winbox pt9' nowrap align='right'>{$res[$r][$i]}</td>\n";
                            break;
                        default:    // その他
                            echo "<td class='winbox pt9' nowrap align='center'>{$res[$r][$i]}</td>\n";
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
