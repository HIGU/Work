<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 システム管理 メニュー                                       //
// Copyright(C) 2001-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created   system_menu.php                                     //
// 2002/08/08 セッション管理を追加                                          //
// 2002/08/27 フレーム対応                                                  //
// 2002/12/03 サイトメニュー対応ため site_id = 99 へ                        //
// 2002/12/27 function menu_bar() によるメニュー画像自動生成                //
// 2003/02/14 売上関係ニュー のフォントを style で指定に変更                //
//                              ブラウザーによる変更が出来ない様にした      //
// 2003/06/30 開発用テンプレートファイルの表示をメニューに追加              //
// 2003/07/15 class patTemplate()の導入 メニューに追加                      //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/02/13 index1.php→index.phpへ変更(index1はauthenticateに変更のため) //
// 2004/03/31 header("Location: http:" . WEB_HOST) --->                     //
//                            header("Location: " . H_WEB_HOST . TOP_MENU)  //
// 2004/04/23 /test/patTemplate を削除したため system/以下にpatexample1追加 //
// 2004/06/10 メニューヘッダーの追加とview_user($_SESSION['User_ID'])の追加 //
// 2004/07/07 DATA-SUMの日報処理実行時にJavaScriptで確認のメッセージを追加  //
// 2004/07/20 MenuHeader class を使用して共通メニュー・認証方式へ変更       //
//            $uniq=uniqid('menu')を追加して乱数生成のオーバーヘッドを減少  //
// 2004/10/12 php4 → php5 へフッターのロゴ変更                             //
// 2004/12/25 style='overflow:hidden;' (-xy両方)を追加                      //
// 2005/01/14 F2/F12キーを有効化する対応のため document.body.focus()を追加  //
// 2005/07/26 <div style='position:absolute; left:15%; bottom:0%;'><img追加 //
// 2006/03/07 組立の登録工数日報処理 (AS/400→DBサーバー) を追加            //
// 2006/03/08 NK仕切単価の日報処理 (AS/400→DBサーバー) を追加              //
// 2006/03/08 組立完成経歴の日報処理 (AS/400→DBサーバー) を追加            //
// 2006/09/04 共通権限マスタ編集メニューを追加                              //
// 2007/03/31 機械運転日報のディレクトリを変更 equip_report/ へ             //
// 2007/04/04 機械運転日報のチップヘルプメッセージをリリース版へ及び配置変更//
// 2007/04/07 data_sum のディレクトリを data_sum/ へ変更                    //
// 2007/04/21 phpのショートカットタグを標準タグへ変更(推奨値へ)             //
// 2007/05/01 $menu->out_alert_java() → out_alert_java(false) へ変更       //
// 2007/05/07 機械運転日報の自動化に伴いメッセージを変更                    //
// 2007/05/15 完成経歴日報のディレクトリ変更 daily/ → assembly_completion/ //
// 2007/05/16 組立工数日報処理のディレクトリ変更 daily/ → assembly_time/   //
// 2007/05/17 前日分の資材在庫サマリー(保有月等を含む)の日報処理を追加      //
// 2007/06/15 設備の自動ログ管理メニューを追加                              //
// 2007/09/11 $menu =& new MenuHeader → $menu = new MenuHeader へ変更      //
// 2007/09/22 メニューを３列へ変更し印刷プログラムのテストメニューを追加    //
// 2007/12/06 印刷フォームコンバートメニューを追加                          //
// 2007/12/10 特注カプラの要領書・ユーザーファイルの更新メニューを追加      //
// 2008/09/29 タイムプロデータ更新メニューを追加                       大谷 //
// 2009/08/03 売上データ再取得を管理メニューに追加                          //
//            AS生産メニュー 19→60でデータを作成してから実行すること  大谷 //
// 2009/12/18 AS400停止・復旧後の前日データ再取得処理                       //
//            as400get_ftp_re.phpを追加                                     //
// 2009/12/25 手動製品マスター更新を追加                               大谷 //
// 2010/01/14 製品マスターの更新を自動に組み込んだので、その場所に          //
//            生産日報データの再取得を組込み。ASでの生産日報処理が          //
//            8:45頃までに出来なかった場合実行                         大谷 //
// 2010/03/05 総平均単価取得を追加（ASからデータ取得後）                    //
// 2015/03/12 生産日報データ・日報データの再取得のコメントを変更            //
//            日報データの再取得の中に生産日報データの再取得を入れ込んで    //
//            いたが、別々に行うよう変更した為。                            //
//            流れは AS復旧→日報データ再取得                               //
//                         →09の栃木生産日報処理が待機中になるまで待つ     //
//                         →生産日報データ再取得を実施                     //
//               ※daily_cli.phpが流れる前に、待機中になっていれば不要 大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮

require_once ('../function.php');           // TNK 全共通 function
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
require_once ('../tnk_func.php');           // menu_bar() で使用
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(3, TOP_MENU);        // 認証チェック3=admin以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(99, 999);           // site_index=99(システム管理メニュー) site_id=999(子メニューあり)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('Administrator System Menu');
//////////// 表題の設定
$menu->set_caption('System Menu');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('日報処理',       SYS . 'system_daily.php');
$menu->set_action('color',          SYS . 'color_check_input.php');
$menu->set_action('月次処理',       SYS . 'system_getuji_select.php');
$menu->set_action('systemDB',       SYS . 'database/system_db.php');
$menu->set_action('as400file',      SYS . 'system_as400_file.php');
$menu->set_action('free_chk',       SYS . 'top-free/free_chk.php');
$menu->set_action('top_chk',        SYS . 'top-free/top_chk.php');
$menu->set_action('template',       SYS . 'templateSample/template.php');
$menu->set_action('phpinfo',        SYS . 'phpinfo/phpinfoMain.php');
$menu->set_action('tnktemplate',    SYS . 'tnkTemplate.php');
// $menu->set_action('patexample',     SYS . 'patexample1.php');
$menu->set_action('data_sum',       SYS . 'data_sum/data_sum--as400-upload.php');
$menu->set_action('log_view',       SYS . 'log_view/php_log_view_clear.php');
$menu->set_action('運転日報',       SYS . 'equip_report/equip_report--as400-upload.php');
$menu->set_action('組立工数日報',   SYS . 'assembly_time/assembly_timeAllUpdate.php');
$menu->set_action('仕切単価日報',   SYS . 'daily/sales_price_update.php');
$menu->set_action('完成経歴日報',   SYS . 'assembly_completion/assembly_completion_history.php');
$menu->set_action('カレンダー',     SYS . 'calendar/companyCalendar_Main.php');
$menu->set_action('共通権限',       SYS . 'common_authority/common_authority_Main.php');
$menu->set_action('在庫サマリー',   SYS . 'inventory_average/inventory_average_summary.php');
$menu->set_action('設備自動ログ',   SYS . 'equip_auto_log_ctl/equip_auto_log_ctl.php');
$menu->set_action('印刷テスト',     TEST . 'print/svgSimplatePXDocTest.php');
$menu->set_action('印刷雛形送信',   SYS . 'printFormUpload/printFormUpload.php');
$menu->set_action('要領書更新',     INDUST . 'inspectionPrint/inspectionPrintUpdate.php');
$menu->set_action('タイムプロ更新', EMP . 'timepro/timePro_update_cli_manu.php');
$menu->set_action('売上データ再取得', SYS . 'daily/sales_get_ftp.php');
$menu->set_action('日報データ再取得', SYS . 'daily/as400get_ftp_re.php');
$menu->set_action('製品マスター更新', SYS . 'daily/product_master_get_ftp.php');
$menu->set_action('生産日報データ再取得', SYS . 'daily/daily_cli.php');
$menu->set_action('総平均単価取得', SYS . 'daily/periodic_average_cost_get_ftp.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('sysMenu');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type='text/css'>
<!--
.top-font {
    font-size: 12pt;
    font-weight: bold;
    font-family: serif;
}
-->
</style>
<script type='text/javascript'>
<!--
function upload_click(msg) {
    return confirm(msg + "\n\n宜しいですか？");
}
function set_focus()
{
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
</head>
<body bgcolor='#ffffff' text='#000000' style='overflow:hidden' onLoad='set_focus()'>
<center>
<?php echo $menu->out_title_border() ?>
    
    <table width='80%' border='0' cellspacing='0' cellpadding='0'> <!-- widthで間隔を調整 heightでbottomの位置調整 -->
    <tr>
    <td valign='top'>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td><p><img src='<?php echo IMG ?>t_nitto_logo3.gif' width='348' height='83'></p></td>
            </tr>
            <tr>
                <td align='center' class='top-font'>
                    <?php echo $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr><td align='center'>
            <img src='<?php echo IMG ?>tnk-turbine.gif' width='68' height='72'>
            </td></tr>
        </table>
        
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table width='100%' border='0' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('日報処理') ?>'><?php echo "\n"; // 旧ファイルmenu_item_edp_nippou.gif ?>
                            <input type='image' alt='日報処理' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_edp_nippou.png','  日  報  処  理'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('月次処理') ?>'><?php echo "\n"; // 旧ファイルmenu_item_edp_getuji.gif ?>
                            <input type='image' alt='月次処理' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_edp_getuji.png', '  月  次  処  理'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('as400file') ?>'><?php echo "\n"; // 旧ファイルmenu_item.gif ?>
                            <input type='image' alt='AS/400 ファイル照会' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_as400_query.png', '  AS/400 ファイル'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('data_sum') ?>'>
                            <input type='image' alt='データサム日報処理(１日に一回処理)後、端末で CALL GBY049C を実行する' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_data_sum_nippo.png', 'データサム日報処理'), '?',$uniq ?>'
                            onClick="return upload_click('既にDATA-SUMの日報処理は自動化されています。\n\nそれでも実行しまか？')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('カレンダー') ?>'>
                            <input type='image' alt='会社の休日などのカレンダーメンテナンス' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_calendar.png', 'カレンダーのメンテ', 14, 0), '?',$uniq ?>'
                            onClick="//return upload_click('会社の休日などのカレンダーメンテナンスを行います。')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('組立工数日報') ?>'>
                            <input type='image' alt='組立の登録工数をAS/400→DBサーバーへ更新します。' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_assemblyTimeAllUPDATE.png', '組立 登録工数 日報'), '?',$uniq ?>'
                            onClick="return upload_click('組立の登録工数及び工程マスターをAS/400→DBサーバーへ更新します。')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('仕切単価日報') ?>'>
                            <input type='image' alt='日東工器用の仕切単価をAS/400→DBサーバーへ更新します。' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_salesPriceUPDATE.png', 'NK仕切単価 日報処理'), '?',$uniq ?>'
                            onClick="return upload_click('日東工器用の仕切単価をAS/400→DBサーバーへ更新します。')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('完成経歴日報') ?>'>
                            <input type='image' alt='組立完成経歴をAS/400→DBサーバーへ更新します。' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_assyComplete.png', ' 組立完成 日報処理'), '?',$uniq ?>'
                            onClick="return upload_click('既に組立完成経歴の日報処理は自動化されています。\n\nそれでも実行しまか？\n\n組立完成経歴をAS/400→DBサーバーへ更新します。')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('共通権限') ?>'>
                            <input type='image' alt='共通権限マスターの編集を行います。' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_common_authority.png', ' 共通権限マスタ編集', 14, 0), '?',$uniq ?>'
                            onClick="//return upload_click('共通権限マスターのメンテナンスを行います。')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('在庫サマリー') ?>'>
                            <input type='image' alt='前日分の資材在庫サマリーをAS/400→DBサーバーへ更新します。' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_inventoryAverageSummary.png', '在庫サマリー 日報処理'), '?',$uniq ?>'
                            onClick="return upload_click('前日分の資材在庫サマリーをAS/400→DBサーバーへ更新します。')">
                        </form>
                    </td>
                </tr>
                
                <!--
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_self() ?>'>
                            <input type='image' border='0'
                                alt='空のアイテム'
                                src='<?php echo IMG ?>menu_item.gif'
                            >
                        </form>
                    </td>
                </tr>
                -->
                
            </table>
        </td>
            <!-- /////////////// center view ////////////// -->
        <td align='center' valign='top'>
            <table width='100%' border='0' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('color') ?>'><?php echo "\n"; // 旧ファイルmenu_item_system_color.gif ?>
                            <input type='image' alt='カラーチェック' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_system_color.png', '  カラーチェック'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('systemDB') ?>'><?php echo "\n"; // 旧ファイルmenu_item_system_db.gif ?>
                            <input type='image' alt='ＤＢ処理' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_system_db.png', '  データベース処理'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('top_chk') ?>'><?php echo "\n"; ?>
                            <input type='image' alt='Free Memory' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_top.png', ' System status view'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('free_chk') ?>'><?php echo "\n"; // 旧ファイルmenu_item_free.gif ?>
                            <input type='image' alt='Free Memory' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_free.png', '    Free Memory'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('phpinfo') ?>'><?php echo "\n"; // 旧ファイルmenu_item_phpinfo.gif ?>
                            <input type='image' alt='PHP Information' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_phpinfo.png', '   PHP Information'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('log_view') ?>'>
                            <input type='image' alt='phpとapacheのerror及びログチェック' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_log_view.png', 'php apache log check'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('template') ?>'>
                            <input type='image' alt='開発用テンプレートの表示' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_template.png', ' 開発用テンプレート'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('tnktemplate') ?>'>
                            <input type='image' alt='開発用テンプレート クラスの表示' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_patTemplate.png', 'テンプレート クラス'), '?',$uniq ?>'
                            onClick="return upload_click('開発用テンプレートクラスを表示します。')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('運転日報') ?>'>
                            <input type='image' alt='機械運転日報データの日報処理(１日に何回でも処理可能)後、端末で CALL GOKK201C を実行する' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_report.png', '機械運転日報処理'), '?',$uniq ?>'
                            onClick="return upload_click('既に機械運転日報の日報処理は自動化されています。\n\nそれでも実行しまか？')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('設備自動ログ') ?>'>
                            <input type='image' alt='設備稼働管理システムの自動ログ収集を管理します。' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_equipAutoLogCtl.png', '　設備自動ログ管理', 14, 0), '?',$uniq ?>'
                            onClick="//return upload_click('設備稼働管理システムの自動ログ収集を管理します。')">
                        </form>
                    </td>
                </tr>
                
                <!--
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_self() ?>'>
                            <input type='image' border='0'
                                alt='空のアイテム'
                                src='<?php echo IMG ?>menu_item.gif'
                            >
                        </form>
                    </td>
                </tr>
                -->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table width='100%' border='0' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('印刷テスト') ?>'>
                            <input type='image' border='0'
                                alt='simplate(テンプレートエンジン) と PXDoc(印刷用Scalable Vector Graphicsエンジン) のテストプログラム'
                                src='<?php echo menu_bar('menu_tmp/menu_item_test_simplate_pxdoc.png', '印刷プログラムテスト', 14, 0), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('印刷雛形送信') ?>'>
                            <input type='image' border='0'
                                alt='simplate(テンプレートエンジン) と PXDoc(印刷用Scalable Vector Graphicsエンジン) のテンプレート用のＳＶＧをアップロードして作成します。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_printFormUpload.png', '印刷フォームコンバート', 13, 0), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('要領書更新') ?>'>
                            <input type='image' border='0'
                                alt='特注カプラの開発ファイル(要領書・承認図・ユーザー番号等)及び客先コードの手動更新を行います。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_inspectionPrintUpload.png', '特注カプラ要領書更新', 13, 0), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('タイムプロ更新') ?>'>
                            <input type='image' alt='タイムプロデータ更新。実行前にタイムプロのデータ(DAIRY_MANU.txt)を端末で作成しフォルダに入れておく' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_timepro.png', 'タイムプロデータ更新'), '?',$uniq ?>'
                            onClick="return upload_click('タイムプロのデータを更新します。\n\nデータはすでに作成しましたか？')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('売上データ再取得') ?>'>
                            <input type='image' alt='売上データ再取得。実行前にAS400で売上データをワークファイルに落としておく' border='0'
                            src='<?php echo menu_bar('menu_tmp/salse_get.png', '売上データ再取得'), '?',$uniq ?>'
                            onClick="return upload_click('売上データを再取得します。\n\nデータはすでに作成しましたか？')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('総平均単価取得') ?>'>
                            <input type='image' alt='総平均単価取得。実行前に月次フォルダ内の総平均単価取得を実行。3月・9月は決算、それ以外は仮。' border='0'
                            src='<?php echo menu_bar('menu_tmp/average_cost_get.png', '総平均単価取得'), '?',$uniq ?>'
                            onClick="return upload_click('総平均単価を取得します。\n\nデータはすでに作成しましたか？')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_self() ?>'>
                            <input type='image' border='0'
                                alt='空のアイテム'
                                src='<?php echo menu_bar('menu_tmp/menu_item_empty.png', ''), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_self() ?>'>
                            <input type='image' border='0'
                                alt='空のアイテム'
                                src='<?php echo menu_bar('menu_tmp/menu_item_empty.png', ''), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('生産日報データ再取得') ?>'>
                            <input type='image' alt='ASで生産日報の処理が遅れた場合（8:45以降～）に行う(ASが夜間バッチなどで停止していた場合は、日報データ再取得の方を実行）。AS400で栃木生産日報処理を実行し、待機中になったら行う。' border='0'
                            src='<?php echo menu_bar('menu_tmp/daily_cli.png', '生産日報データ再取得'), '?',$uniq ?>'
                            onClick="return upload_click('生産日報データを再取得します。\n\n栃木生産日報処理は待機中になっていますか？\n\n先に日報データ再取得を行いましたか？')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('日報データ再取得') ?>'>
                            <input type='image' alt='AS400が夜間バッチで停止した際、復旧後に行う。通常7:00頃に流れるプログラムな為、AS400復旧後すぐに流していい。栃木生産日報処理の待機中を待つ必要はない。' border='0'
                            src='<?php echo menu_bar('menu_tmp/as400get_ftp_re.png', '日報データ再取得'), '?',$uniq ?>'
                            onClick="return upload_click('日報データを再取得します。\n\nAS400は復旧していますか？')">
                        </form>
                    </td>
                </tr>
                
                <!--
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_self() ?>'>
                            <input type='image' border='0'
                                alt='空のアイテム'
                                src='<?php echo menu_bar('menu_tmp/menu_item_empty.png', ''), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                -->
                
            </table>
        </td>
        </tr>
        </table>
    </td>
    </tr>
    </table>
    <div style='position:absolute; left:15%; bottom:0%;'>
        <!-- <img src='<?php echo IMG ?>php4.gif'   width='64'  height='32'> -->
        <img src='<?php echo IMG ?>php5_logo.gif'>
        <img src='<?php echo IMG ?>linux.gif'  width='74'  height='32'>
        <img src='<?php echo IMG ?>redhat.gif' width='96'  height='32'>
        <img src='<?php echo IMG ?>apache.gif' width='259' height='32'>
        <img src='<?php echo IMG ?>pgsql.gif'  width='160' height='32'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
