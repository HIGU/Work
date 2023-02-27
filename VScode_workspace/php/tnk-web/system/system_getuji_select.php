<?php
//////////////////////////////////////////////////////////////////////////////
// 月　次　処　理  (年月選択フォーム)                                       //
// Copyright(C) 2002-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// History                                                                  //
// 2002/02/07 Created system_getuji_select.php                              //
// 2002/08/08  セッション管理に変更                                         //
// 2002/08/27 フレーム対応                                                  //
// 2002/12/03 サイトメニューに追加のため site_id=11 を追加                  //
// 2003/02/26 body に onLoad を追加し初期入力個所に focus() させた          //
// 2004/07/20 Class MenuHeader を追加                                       //
// 2004/10/12 php4 → php5 へフッターのロゴ変更                             //
// 2006/04/19 style='overflow-y:hidden;' 追加                               //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');       // TNK 全共通 function
require_once ('../MenuHeader.php');     // TNK 全共通 menu class
require_once ('../tnk_func.php');       // menu_bar() で使用
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(3);    // 認証チェック3=admin以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(99, 11);                // site_index=99(システム管理メニュー) site_id=11(月次処理)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('管理者用 月次処理');
//////////// 表題の設定
$menu->set_caption('月次処理(年月指定)');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('月次実行', SYS . 'system_getuji.php');

if (isset($_SESSION['yyyymm'])) {
    $yyyymm = $_SESSION['yyyymm'];      // セッション変数で初期化
} else {
    $yyyymm = "";                       // 初期化(SESSION変数には登録していないがとりあえず入れておく)
}
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
</head>
<body bgcolor='#ffffff' text='#000000' onLoad='document.ini_form.yyyymm.focus()' style='overflow-y:hidden;'>
    <center>
<?= $menu->out_title_border() ?>
        <table height='92.7%' width='80%' border='0'> <!-- widthで間隔を調整 heightでbottomの位置調整 -->
        <tr><td valign='top'>
            <table>
                <tr><td><p><img src='../img/t_nitto_logo3.gif' width='348' height='83'></p></td></tr>
            </table>
            <table width='100%'>
                <tr><td align='center'><b><?= $menu->out_caption() ?></b></td></tr>
                <tr><td align='center'>
                <br>
                <img src='../img/tnk-turbine.gif' width='68' height='72'>
                </td></tr>
            </table>
            <table width='100%' cellspacing='0' cellpadding='3'>
                <form name='ini_form' action='<?= $menu->out_action('月次実行') ?>' method='post'>
                    <tr>
                        <td></td>
                        <td align='center'>
                            月次処理の年月を指定して下さい。
                            <input type='text' name='yyyymm' size='8' value='<?php echo($yyyymm); ?>' maxlength='6'>
                            <br>例：200202 （2002年02月）
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td align='center'>
                            <input type='submit' name='system_getuji_select' value='実行' >
                        </td>
                    </tr>
                </form>
            </table>
        </td></tr>
        <tr><td valign='bottom'>
            <!-- <img src='../img/php4.gif' width='64' height='32'> -->
            <img src='../img/php5_logo.gif'>
            <img src='../img/linux.gif' width='74' height='32'>  
            <img src='../img/redhat.gif' width='96' height='32'>   
            <img src='../img/apache.gif' width='259' height='32'> 
            <img src='../img/pgsql.gif' width='160' height='32'>
        </td></tr>
        </table>
    </table>
    </center>
</body>
</html>
