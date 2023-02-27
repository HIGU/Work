<?php
//////////////////////////////////////////////////////////////////////////////
// ？？？？？？？？？？？？？？？？？？                                     //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/07/15 Created  template.php                                         //
// 2003/06/30 全般的に見直し表形式に基準を置きHTMLを雛型化した。            //
//             名前をtemplate.php へ変更し system ディレクトリへ移動        //
//             ファイルサーバーの 雛型.php と連動させる事                   //
// 2003/09/07 <!DOC... "http://www.w3.org/TR/html4/loose.dtd"> を追加       //
// 2003/10/01 上記を取消(表示が崩れる)                                      //
// 2003/10/20 <title></title>とフォームのタイトルを $menu_title で統一      //
// 2003/11/18 <th> pt11 → pt10   <td> pt10b → pt9 へ変更                  //
// 2003/11/26 作表時に switch case 文で center left 等の切替えを追加        //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2003/12/20 $_SESSION['offset']は、使用する時にsales_offsetの様に変更する //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
//                              GETによるpage_keepに注意                    //
// 2004/05/24 style sheet の/* */はExcelでエラーになるため phpタグを使用    //
// 2004/06/07 リターンアドレス設定を新方式へ変更(呼出元で設定しておく)      //
// 2004/06/10 view_user($_SESSION['User_ID']) をメニューヘッダーの下に追加  //
// 2004/07/26 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2004/12/25 style='overflow:hidden;' (-xy両方)を追加                      //
// 2005/01/14 F2/F12キーで戻るための対応で document.body.focus()IEのみを追加//
// 2005/04/26 <div></div>→<span></span>ブロック要素からインライン要素へ変更//
// 2005/08/01 <script language= はHTML4.01で採用されていない<script type=へ //
// 2005/08/30 template.js(extends base_class.js),template.css分離して標準化 //
// 2005/11/07 サンプルのSQL文の変更とE_STRICTがエラーを出力しないためE_ALLへ//
// 2005/11/24 <link rel='shortcut icon' href='/favicon.ico'>追加            //
// 2007/01/23 phpのショートタグを廃止(＜？＝ → ＜？php echo)推奨設定へ     //
// 2007/04/21 ディレクトリをtemplateSamplへ移動。SQL文をSQL98互換に書換え   //
// 2007/09/07 $_POST/$_GET→$_REQUEST へ変更 $session->add_localでページ制御//
//            呼出し元のページ維持(無条件)を追加                            //
// 2007/09/11 error_reporting を master設定へ変更                           //
// 2007/09/18 E_ALL | E_STRICT へ変更                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=6143 debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('max_execution_time', 120);         // 最大実行時間=120秒 SAPI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを生成
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(99, 60);                    // site_index=99(システムメニュー) site_id=60(テンプレート)
////////////// リターンアドレス設定(絶対指定する場合)
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('テンプレート タイトル');
//////////// 表題の設定
$menu->set_caption('サンプルでアイテムマスターを表示しています');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('アイテムマスター編集',   INDUST . 'master/parts_item/parts_item_Main.php');
//////////// 呼出し元のページを維持
$menu->set_retGET('page_keep', 'on');

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

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// template 用 検索値
$reg_up_date = date_offset(1);
$reg_up_date2 = date_offset(3);
//////////// SQL 文の where 句を 共用する
$search = sprintf('WHERE madat>=%d AND madat<=%d', $reg_up_date2, $reg_up_date);

//////////// 一頁の行数
define('PAGE', '20');   // IEは25でOKだがNNは23のため 20に設定

//////////// 合計レコード数取得     (対象テーブルの最大数をページ制御に使用)
$query = sprintf('SELECT count(*) FROM miitem %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= '合計レコード数の取得に失敗<br>DBの接続を確認！';  // .= メッセージを追加する
}
//////////// ページオフセット設定(offsetは使用する時に名前を変更 例：sales_offset)
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
} elseif ( isset($_REQUEST['page_keep']) ) {                // 現在のページを維持する GETに注意
    $offset = $offset;
} else {
    $offset = 0;                            // 初回の場合は０で初期化
    $session->add_local('recNo', '-1');     // 0レコードでマーカー表示してしまうための対応
}
$session->add_local('offset', $offset);

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$query = sprintf("
        SELECT
            mipn                        AS 部品番号,                -- 0
            substr(midsc, 1, 26)        AS 部品名,                  -- 1
            -- mzist                    AS 材質,
            -- COALESCE(mzist, '---')   AS 材質,    --mzistがNULLなら'---'
            CASE
                WHEN trim(mzist) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                ELSE mzist
            END                         AS 材質,                    -- 2
            CASE
                WHEN trim(mepnt) = '' THEN '---'
                ELSE trim(mepnt)
            END                         AS 親機種,                  -- 3
            madat                       AS ＡＳ登録日,              -- 4
            CAST(last_date AS date)     AS \"Web登録日\",           -- 5
            CAST(last_date AS time(2))  AS \"Web登録時間\"          -- 6
        FROM
            miitem
        %s      -- ここに where句の and を挿入できる
        ORDER BY madat DESC
        OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("サンプルデータの更新日:%s で<br>データがありません。", format_date($reg_up_date) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>

<!-- JavaScriptのファイル指定をbodyの最後にする。 HTMLタグのコメントは入れ子に出来ない事に注意  -->
<script type='text/javascript' src='template.js?<?php echo $uniq ?>'></script>

<!-- スタイルシートのファイル指定をコメント HTMLタグのコメントは入れ子に出来ない事に注意  -->
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>

<style type='text/css'><!-- --></style>
</head>

<body onLoad='setInterval("templ.blink_disp(\"caption\")", 500); templ.set_focus()' style='overflow-y:hidden;'>
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
                    <td align='center' class='caption_font' id='caption'>
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
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
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
                    echo "    <td class='winbox' nowrap align='right'><span class='pt10b'>", ($r + $offset + 1), "</span></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                        case 0:
                            echo "<td class='winbox pt9' nowrap align='center'>\n";
                            echo "    <a href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"{$menu->out_action('アイテムマスター編集')}?partsKey=", urlencode($res[$r][$i]), "\")' target='_self' style='text-decoration:none;'>{$res[$r][$i]}</a>\n";
                            echo "</td>\n";
                            break;
                        case 1:
                        case 2:
                        case 3:
                            echo "<td class='winbox pt9' nowrap align='left'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case 4:
                            echo "<td class='winbox pt9' nowrap align='center'>", format_date($res[$r][$i]), "</td>\n";
                            break;
                        default:
                            echo "<td class='winbox pt9' nowrap align='center'>{$res[$r][$i]}</td>\n";
                        }
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <div>
            <input type='button' name='test_opne' value='Window表示' onClick='templ.win_open("template.php", 1024, 768)'>
            <a href='template.php' target='subwin'>Window表示</a>
            <input type='button' name='test_show' value='Window表示' onClick='templ.win_show("template.php", 1024, 768)'>
        </div>
        <div style='text-align: left;'>
            <?php echo 'E_STRICT ', E_STRICT ?>
            <br>
            <?php echo 'E_ALL ', E_ALL ?>
            <br>
            <?php echo 'E_ALL | E_STRICT ', E_ALL | E_STRICT ?>
        </div>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
