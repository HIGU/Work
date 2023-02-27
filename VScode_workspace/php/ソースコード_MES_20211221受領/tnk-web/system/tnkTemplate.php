<?php
//////////////////////////////////////////////////////////////////////////
// ？？？？？？？？？？？？？？？？？？                                 //
// 2003/01/16 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// 変更経歴                                                             //
// 2003/01/16 新規作成  patTemplate.php                                 //
// 2003/06/30 全般的に見直し表形式に基準を置きHTMLを雛型かした。        //
//             名前をtemplate.php へ変更し system ディレクトリへ移動    //
//             ファイルサーバーの 雛型.php と連動させる事               //
// 2003/07/15 class patTemplate() を新規導入 template.phpと明確に分ける //
//            getResultWithField2() を getResultWithField3()へ変更して  //
//            数値インデックス専用の関数とした。                        //
// 2003/09/07 <!DOC... "http://www.w3.org/TR/html4/loose.dtd"> を追加   //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 CLI CGI版
ob_start("ob_gzhandler");               // 出力バッファをgzip圧縮
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');       // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');       // TNK に依存する部分の関数を require_once している
access_log();                           // Script Name は自動取得
$_SESSION['site_index'] = 99;           // 最後のメニュー    = 99   システム管理用は９９番
$_SESSION['site_id'] = 60;              // 下位メニュー無し <= 0    テンプレートファイルは６０番
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
$url_referer     = $_SERVER["HTTP_REFERER"];    // 呼出もとのURLを保存 前のスクリプトで分岐処理をしている場合は使用しない
// $url_referer     = $_SESSION['pl_referer'];     // 分岐処理前に保存されている呼出元をセットする

//////////////// 認証チェック
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // 権限レベルが２以下は拒否
// if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
    $_SESSION['s_sysmsg'] = "認証されていないか認証期限が切れました。ログインからお願いします。";
    // header("Location: http:" . WEB_HOST . "menu.php");   // 固定呼出元へ戻る
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

/********** Logic Start **********/
//////////////// サイトメニューのＵＲＬ設定 & JavaScript生成
$menu_site_url = 'http:' . WEB_HOST . 'menu_site.php';
$menu_site_script =
"<script language='JavaScript'>
<!--
    parent.menu_site.location = '$menu_site_url';
// -->
</script>";

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid("target");

//////////// システムメッセージ変数初期化
// $_SESSION['s_sysmsg'] = "";      // menu_site.php で使用するためここで初期化は不可

//////////// template 用 検索値
$reg_up_date = 20030624;
//////////// SQL 文の where 句を 共用する
$search = sprintf("where madat=%d", $reg_up_date);

//////////// 一頁の行数
define("PAGE", 10);

//////////// 最大レコード数取得     (対照データの最大数をページ制御に使用)
$query = sprintf("select count(*) from miitem %s", $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "最大レコード数の取得に失敗";      // .= メッセージを追加する
}
//////////// ページオフセット設定
if ( isset($_POST['forward']) ) {                       // 次頁が押された
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // 次頁が押された
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません</font>";
        }
    }
} elseif ( isset($_POST['page_keep']) ) {               // 現在のページを維持する
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['offset'];

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$query = sprintf("
        select
            mipn as 部品番号,
            midsc as 部品名
        from
            miitem
        %s offset %d limit %d
    ", $search, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("アイテムマスターの更新日:%s で<br>データがありません。", format_date($reg_up_date) );
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
}
///////////// 表示用行番号の生成
$dsp_num = array();
for ($i=0; $i<$rows; $i++) {
    $dsp_num[$i] = ($i + $offset + 1);
}
//////////// 表示用のフィールド配列を生成
for ($r=0; $r<$rows; $r++) {
    $field0[$r] = $res[$r][0];
    $field1[$r] = $res[$r][1];
}
/********** Logic End   **********/

//////////// 共通ヘッダーの書出し
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");               // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                     // HTTP/1.0

/********** patTemplate 書出し ************/
include_once ( "../../patTemplate/include/patTemplate.php" );
$tmpl = new patTemplate();

//  In diesem Verzeichnis liegen die Templates
$tmpl->setBasedir( "templates" );

$tmpl->readTemplatesFromFile( "tnkTemplate.tmpl.html" );

$tmpl->addVars( "page", array("PAGE_TITLE"         => "TNK 開発用テンプレート") );
$tmpl->addVars( "page", array("PAGE_MENU_SITE_URL" => $menu_site_script) );
$tmpl->addVars( "page", array("PAGE_UNIQUE"        => $uniq) );
$tmpl->addVars( "page", array("PAGE_RETURN_URL"    => $url_referer) );
$tmpl->addVars( "page", array("PAGE_CURRENT_URL"   => $current_script) );
$tmpl->addVars( "page", array("PAGE_HEADER_TITLE"  => "ＴＮＫ 開発用テンプレート") );
$tmpl->addVars( "page", array("PAGE_HEADER_TODAY"  => $today) );
$tmpl->addVars( "page", array("PAGE_BODY_TITLE"    => "テンプレートでアイテムマスター 一覧") );

$tmpl->addVars( "item", array("ITEM_FIELD" => $field) );

$tmpl->addVars( "tbody_rows", array("TBODY_DSP_NUM" => $dsp_num) );
$tmpl->addVars( "tbody_rows", array("TBODY_FIELD0"  => $field0) );
$tmpl->addVars( "tbody_rows", array("TBODY_FIELD1"  => $field1) );

//  Alle Templates ausgeben
$tmpl->displayParsedTemplate();
/************* patTemplate 終了 *****************/

/////// デバッグ用
echo    "<br><br>--------------------------------------------&lt;DUMP INFOS&gt;--------------------------------------------<br><br>";
$tmpl->dump();
?>
