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
// 2007/03/24 ディレクトリmaterial/→parts/allocate_config/ フレーム版へ変更//
// 2020/03/10 総材料費未登録照会に戻る時のパラメータを追加             和氣 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
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
$menu->set_title('引当 部品 構成表 の 照会 *** New *** ');
//////////// 呼出先のaction名とアドレス設定
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

// 総材料費未登録照会に戻る時のパラメータを追加
// 総材料費未登録照会から呼出、登録画面へコピーをクリックし総材料費の登録(工程明細)から
// 戻ってきたとき、さらに戻るで総材料費未登録照会に戻ると初期画面に戻ってしまう時の対応
if( isset($_REQUEST['page_keep']) && $_REQUEST['page_keep'] == 'On' ) {
    $menu->set_retGET('page_keep', '2');
}

if (isset($_REQUEST['aden_flg'])) {     // A伝詳細情報の照会からの呼出対応
    $menu->set_retGET('page_keep', $_REQUEST['sc_no']);
    $menu->set_retGET('page_keep', $_REQUEST['aden_flg']);
    $sc_no = $_REQUEST['sc_no'];
    $aden_flg = $_REQUEST['aden_flg'];
    $param  = "&sc_no={$_REQUEST['sc_no']}&aden_flg=1";
} else {
    $sc_no = '';
    $aden_flg = '';
    $param  = '';
}
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
    $_SESSION['s_sysmsg'] .= "合計部品数の取得が出来ませんでした！";      // .= メッセージを追加する
} else {
    if ($maxrows <= 0) {
        $_SESSION['s_sysmsg'] .= "引当がありません！";      // .= メッセージを追加する
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
}


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

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('alloConf');

$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='allo_conf_parts.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<!-- <script type='text/javascript' src='allo_conf_parts.js?<?php echo $uniq ?>'></script> -->
<script language="JavaScript">
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // <a name='mark' でフォーカスが移るため0.1秒ずらしてフォーカスをセットする。
    // フレームを切っていないためフォーカスを変えるとmarkへいかないためコメント
    // setTimeout("document.mhForm.backwardStack.focus()", 100);  //こちらに変更しNN対応
}
// -->
</script>

<style type="text/css">
<!--
body {
    overflow-x:         hidden;
    overflow-y:         hidden;
}
-->
</style>
</head>
<body>
<center>
<?php echo $menu->out_title_border() ?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <tr>
                <td nowrap align='center' class='caption_font'>
                    <?php echo $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        
<?php
if ($aden_flg == '1') {
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='allo_conf_parts_ViewHeader_aden.html?{$uniq}' name='header' align='center' width='98%' height='42' title='項目'>\n";
    echo "    項目を表示しています。\n";
    echo "</iframe>\n";
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='allo_conf_parts_ViewBody_aden.php?", $_SERVER['QUERY_STRING'], "&{$uniq}#mark' name='list' align='center' width='98%' height='77%' title='一覧'>\n";
    echo "    一覧を表示しています。\n";
    echo "</iframe>\n";
} else {
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='allo_conf_parts_ViewHeader.html?{$uniq}' name='header' align='center' width='98%' height='42' title='項目'>\n";
    echo "    項目を表示しています。\n";
    echo "</iframe>\n";
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='allo_conf_parts_ViewBody.php?", $_SERVER['QUERY_STRING'], "&{$uniq}#mark' name='list' align='center' width='98%' height='77%' title='一覧'>\n";
    echo "    一覧を表示しています。\n";
    echo "</iframe>\n";
}
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='allo_conf_parts_ViewFooter.php?rows={$maxrows}&{$uniq}' name='footer' align='center' width='100%' height='32' title='フッター'>\n";
// echo "    フッターを表示しています。\n";
// echo "</iframe>\n";
?>
        
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
