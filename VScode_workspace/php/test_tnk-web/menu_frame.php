<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 トップフレーム設定                                          //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// History                                                                  //
// 2002/08/26 Created   menu_frame.php                                      //
// 2002/08/26 セッション管理を追加 & register_globals = Off 対応            //
// 2002/09/21 cols="18%,*" → 16% へ 変更 800 X 600 対応                    //
// 2003/11/11 cols="16%,*" → 14% へ 変更 損益表等を広い画面で見るため      //
// 2003/11/15 cols="14%,*" → 12% へ 変更 menu_site.phpのfontを11pt→9pt    //
// 2003/12/15 cols="12%,*" → 10% へ 変更 site_view の On/Off 設定追加      //
// 2004/07/21 <title>→全角から半角のTNK Web Systemへ変更 タグを小文字へ変更//
// 2005/08/31 base_class を使用しクライアントのウィンドウ位置を保存する     //
// 2005/09/05 setWinOpen()メソッドを追加 menuOn/OffスイッチによるUnload対応 //
//            onLoad='menu.setWinOpen()'で対応                              //
// 2005/09/07 site_menu On/Offのための noSwitchフラグ追加(base_class.jsから)//
// 2005/09/13 site表示の初期値を$_SESSION['site_view'] = 'on' → 'off'へ変更//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('function.php');          // TNK Web 全共通function
require_once ('MenuHeader.php');        // TNK 全共通 menu class
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);              // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
if (!isset($_REQUEST['name'])) {        // site_menu On/Off でなければ
    $menu->set_site(0, 0);              // site_index=0(未設定) site_id=0(未設定)
}
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('TNK Web System');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

////////////// 実行スクリプト名の取得
if ( isset($_REQUEST['name']) ) {
    $exec_name = $_REQUEST['name'];
} else {
    $exec_name = TOP_MENU;  // 初期値
}

////////////// 表示の On Off 設定・取得
if (!isset($_SESSION['site_view'])) $_SESSION['site_view'] = 'off';  // 初期値
///// name と noSwitch をフラグに使っていることに注意
if ( (isset($_REQUEST['name'])) && (!isset($_REQUEST['noSwitch'])) ) {
    if ($_SESSION['site_view'] == 'on') {
        $_SESSION['site_view'] = 'off';
        $frame_cols = '0%,*';
    } else {
        $_SESSION['site_view'] = 'on';
        $frame_cols = '10%,*';
    }
} else {
    if ($_SESSION['site_view'] == 'on') {
        $frame_cols = '10%,*';
    } else {
        $frame_cols = '0%,*';
    }
}

////////////// リターンアドレス用のインデックスの初期化
$_SESSION['ScriptStack'] = 0;         // Startは0から menu.phpが Stack=1となる

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="Content-Style-Type" content="text/css">
        <meta http-equiv="Content-Script-Type" content="text/javascript">
        <title><?=$menu->out_title()?></title>
        <?=$menu->out_jsBaseClass()?>
        <script type='text/javascript' src='base_class.js'></script>
        <script type='text/javascript' src='menu_frame.js?id=<?=$uniq?>'></script>
    </head>
    <frameset name='topFrame' cols='<?=$frame_cols?>' border='0' onUnload='menu.win_close()' onLoad='menu.setWinOpen(); menu.siteMenuView();' onHelp='return false'>
        <frame src='menu_site.php' name='menu_site' scrolling='no'>
        <frame src='<?=$exec_name?>' name='application'>
    </frameset>
    <noframes>
        <p>栃木日東工器(株)のWebサイトではフレームを使う前提になっています。</p>
        <p>フレームを使用しない設定にしている場合は変更して下さい。</p>
        <p>未対応のブラウザーの場合は対応ブラウザーに変更して下さい。<p>
    </noframes>
</html>
