<?php
//////////////////////////////////////////////////////////////////////////////
// 引当部品構成表の照会  計画番号の入力・確認 form                          //
//                              Allocated Configuration Parts 引当構成部品  //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/05/28 Created   allo_conf_parts_form.php                            //
// 2004/06/07 インクリメントサーチを可能にし選択一覧の表機能を追加          //
// 2004/06/15 選択一覧の抽出条件に (plan-cut_plan)>0 計画数があるものを追加 //
// 2004/12/28 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
//                          ディレクトリをindustry→industry/materialへ変更 //
// 2005/01/12 総材料費の未登録と同様の処理をさせるためmaterial_plan_noを追加//
// 2005/06/17 JavaScriptのselect()をコメント解除(カーソル表示のため)        //
// 2007/03/24 allo_conf_parts_view.php → allo_conf_parts_Main.php へ変更   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');     // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');     // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_INDUST, 26);          // site_index=30(生産メニュー) site_id=26(引当部品構成表の照会)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);          // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('引当 部品 構成表 の 照会 計画番号選択');
//////////// 表題の設定
$menu->set_caption('計画番号');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('引当構成表の表示',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_action('総材料費用引当構成表の表示',   INDUST . 'parts/allocate_config_entry/allo_conf_parts_Main.php');
$menu->set_action('総材料費用引当構成表のTEST',   INDUST . 'parts/allocate_config_test/allo_conf_parts_Main.php');
// $menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_view.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

////////////// 自分のポストデータをチェック $_REQUESTを使わない理由は$_COOKIEと$_FILESを除外するため
if (isset($_POST['plan'])) {
    $plan = $_POST['plan'];
} elseif (isset($_GET['plan'])) {
    $plan = $_GET['plan'];
}
if (isset($plan)) {
    if (strlen($plan) < 8) {
        ///// 選択リストを表示させる
        $query = "select plan_no    as 計画番号     -- 0
                        , parts_no  as 製品番号     -- 1
                        , trim(substr(midsc, 1, 25))
                                    as 製品名       -- 2
                        , chaku     as 着手日       -- 3
                        , kanryou   as 完了日       -- 4
                        , plan - cut_plan
                                    as 計画数       -- 5
                        , kansei    as 完成数       -- 6
                        , trim(note15)
                                    as 備　考       -- 7
                    from
                        assembly_schedule
                    left outer join
                        miitem
                    on (parts_no=mipn)
                    where plan_no like '{$plan}%' and (plan-cut_plan)>0 and parts_no!='999999999' and note15 not like '%NKCT%'
                    ORDER BY plan_no DESC, kanryou DESC limit 50";
        $res   = array();
        $field = array();
        if ( ($rows = getResultWithField2($query, $field, $res)) > 0) {
            $num = count($field);       // フィールド数取得
            $parts_no = '';
        } else {
            $num = 0;
            $parts_no = '';
        }
        $set_view = $plan;   // 選択リストの表示
        $_SESSION['plan_cond'] = $set_view;
    } else {
        $query = "select parts_no, midsc, kansei, note15
                    from
                        assembly_schedule
                    left outer join
                        miitem
                    on (parts_no=mipn)
                    where plan_no='{$plan}'";
        $res = array();
        if (getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "{$plan}：では登録されていません！";
            $parts_no  = '';
            $assy_name = "<font color='red'>未 登 録</font>";
            $kansei    = '';
            $kouji_no  = '';
        } else {
            $parts_no  = $res[0][0];
            $assy_name = $res[0][1];
            $kansei    = $res[0][2];
            $kouji_no  = $res[0][3];
            $_SESSION['plan_no']  = $plan;       // 計画番号の確定(entryが押されたらこれで処理)
            $_SESSION['assy_no']  = $parts_no;
            $_SESSION['material_plan_no']  = $plan; // 総材料費の未登録と同様の処理をさせるため
        }
    }
} else {
    $plan = '';
}

///// スクロールバーの表示・非表示
if (isset($set_view)) {
    $scrollbar = "style='overflow:auto;'";
} else {
    $scrollbar = "style='overflow:hidden;'";
}

////////////// 照会ボタンが押された(entryボタン)
if (isset($_POST['entry'])) {       // リターンアドレス設定
    ///// 引当部品構成表のviewへ
/**/
    if( $_SESSION['User_ID'] == '300667' ) {
//        header('Location: ' . H_WEB_HOST . $menu->out_action('総材料費用引当構成表の表示'));
        header('Location: ' . H_WEB_HOST . $menu->out_action('総材料費用引当構成表のTEST'));
//        header('Location: ' . H_WEB_HOST . $menu->out_action('引当構成表の表示'));
/**
    } else if( $_SESSION['User_ID'] == '970352' || $_SESSION['User_ID'] == '300144') {
        header('Location: ' . H_WEB_HOST . $menu->out_action('総材料費用引当構成表の表示'));
/**/
    }else{
        header('Location: ' . H_WEB_HOST . $menu->out_action('総材料費用引当構成表の表示'));
//        header('Location: ' . H_WEB_HOST . $menu->out_action('引当構成表の表示'));
    }
    exit();
} elseif (isset($_GET['entry'])) {  // リターンアドレス設定
    ///// 引当部品構成表のviewへ (一覧より選択のため環境を相手に伝える)
    if( $_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '970352' || $_SESSION['User_ID'] == '300144' ) {
        header('Location: ' . H_WEB_HOST . $menu->out_action('総材料費用引当構成表の表示'));
    }else{
        header('Location: ' . H_WEB_HOST . $menu->out_action('総材料費用引当構成表の表示'));
//        header('Location: ' . H_WEB_HOST . $menu->out_action('引当構成表の表示') . "?plan_cond={$_SESSION['plan_cond']}");
    }
    exit();
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

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
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
    if (obj.plan.value.length != 0) {
        // if (obj.plan.value.length != 8) {
        if (obj.plan.value.length < 1) {
            // alert("計画番号の桁数は８桁です。");
            alert("計画番号は少なくとも１桁以上入力して下さい。");
            obj.plan.focus();
            obj.plan.select();
            return false;
        } else {
            window.location = '<?= H_WEB_HOST . $menu->out_self() ?>?plan=' + obj.plan.value;
            return true;
        }
    }
    alert('計画番号が入力されていません！');
    obj.plan.focus();
    obj.plan.select();
    return false;
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.entry_form.plan.focus();      // 初期入力フォームがある場合はコメントを外す
    document.entry_form.plan.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font-size:      11pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
.plan_font {
    font-size:      13pt;
    font-weight:    bold;
    text-align:     left;
    font-family:    monospace;
}
.entry_font {
    font-size:      11pt;
    font-weight:    bold;
    color:          red;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          11pt;
    font-wieght:        bold;
    font-family:        monospace;
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
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
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
<body <?=$scrollbar?> onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <div style='font-size:11pt'>計画番号は1桁からインクリメントサーチします。(1桁目は事業部 2桁目は月 1.2.3...A=10.B=11.C=12 3桁目以降は連番</div>
        
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='entry_form' method='post' action='<?= $menu->out_self() ?>' onSubmit='return chk_plan_entry(this)'>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'><?= $menu->out_caption() . "\n" ?></div>
                    </td>
                    <td class='winbox' width='300' nowrap align='center'>
                        <input class='plan_font' type='text' name='plan' value='<?= $plan ?>' size='8' maxlength='8'>
                        <input class='pt11b' type='submit' name='conf' value='確認'>
                    </td>
                </tr>
                <?php if ($plan != '' && !isset($set_view)) { ?>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>製品番号</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?= $parts_no ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>製 品 名</div>
                    </td>
                    <td class='winbox' width='300' nowrap align='left'>
                        <div class='pt12b'><?= $assy_name ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>完 成 数</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?= $kansei ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>工事番号</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?= $kouji_no ?></div>
                    </td>
                </tr>
                    <?php if ($parts_no != '') { // 未登録の場合は表示しない ?>
                    <tr>
                        <td class='winbox' colspan='2' nowrap align='center'>
                            <input class='entry_font' type='submit' name='entry' value='照会'>
                        </td>
                    </tr>
                    <?php } ?>
                <?php } ?>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <?php if (isset($set_view)) { ?>
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                ?>
                    <th nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr>\n";
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", ($r + 1), "</div></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                        case 0: // 計画番号
                            echo "<td class='winbox' nowrap align='center'><a class='pt9' href='{$menu->out_self()}?plan={$res[$r][$i]}&entry'>", $res[$r][$i], "</a></td>\n";
                            break;
                        case 2:
                        case 7:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                            break;
                        case 3:
                        case 4:
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>", format_date($res[$r][$i]), "</div></td>\n";
                            break;
                        case 5:
                        case 6:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
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
        <?php } ?>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
