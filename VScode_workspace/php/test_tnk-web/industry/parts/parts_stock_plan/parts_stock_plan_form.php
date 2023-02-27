<?php
//////////////////////////////////////////////////////////////////////////////
// 生産用 部品在庫予定 照会 部品指定フォーム                                //
// Copyright(C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2007/02/20 Created  parts_stock_plan_form.php                            //
// 2007/05/22 最低必要日の照会を追加 requireDateのリクエストダイレクト処理  //
// 2007/06/22 noMenuをAjaxへ渡すためhidden属性でフォーム部品追加            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);           // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug 用
ini_set('display_errors', '1');                 // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');               // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);            // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                       // 出力バッファをgzip圧縮
session_start();                                // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');                 // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');                 // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');               // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
access_log();                                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_INDUST, 16);          // site_index=INDEX_INDUST(生産メニュー) site_id=16(在庫予定)999(サイトを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('部品在庫予定照会(引当・発注状況)');
//////////// 表題の設定
$menu->set_caption('部品番号');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('在庫予定照会',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
//////////// 在庫経歴照会から呼出されていなければアクションをセット
if (preg_match('/parts_stock_view.php/', $menu->out_RetUrl())) {
    $menu->set_retGet('material', '1');
    $stockViewFlg = false;
} else {
    $menu->set_action('在庫経歴照会',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');
    $stockViewFlg = true;
}

//////////// リクエストのインスタンスを登録
$request = new Request();

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='parts_stock_plan.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_stock_plan.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PartsStockPlan.set_focus(document.ConditionForm.targetPartsNo, "select");
        // PartsStockPlan.intervalID = setInterval("PartsStockPlan.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('targetPartsNo') != '') echo 'PartsStockPlan.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_action('在庫予定照会') ?>' method='post'
        onSubmit='return PartsStockPlan.checkConditionForm(this)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td colspan='7' align='center' class='winbox caption_color'>
                    <span id='blink_item'>部品番号</span>
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetPartsNo' size='9' class='pt12b' value='<?php echo $request->get('targetPartsNo'); ?>' maxlength='9'
                        onKeyUp='PartsStockPlan.keyInUpper(this);'
                    >
                </td>
                <td class='winbox' align='center'>
                    <input type='submit' name='exec1' value='実行' title='Enter キーを押すか、ボタンをクリックすれば、実行します。'>
                    &nbsp;
                    <input type='button' name='exec2' value='開く' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 2);' title='別ウィンドウで表示します。'>
                    &nbsp;
                    <input type='button' name='clear' value='クリア' style='width:54px;' disabled>
                    &nbsp;
                    <!-- <input type='button' name='exec3' value='必要日' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 3);' title='クリックすれば、この下に発注を除いた引当のみにし必要日を表示します。'> -->
                    <input type='button' name='exec3' value='必要日' style='width:54px;' disabled>
                    &nbsp;
                    <input type='button' name='exec3' value='必開く' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 4);' title='クリックすれば、別ウィンドウに発注を除いた引当のみにし必要日を表示します。'>
                    <input type='hidden' name='material' value='1'>
                </td>
                <td class='winbox' align='center'>
                    &nbsp&nbsp<a href='javascript:void(0);' style='color:gray; text-decoration:none;'>在庫経歴照会</a>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <input type='hidden' name='noMenu' value='<?php echo $request->get('noMenu')?>'>
    </form>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
