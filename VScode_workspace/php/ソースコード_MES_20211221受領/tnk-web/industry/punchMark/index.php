<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理システム用 indexメニュー                                         //
// Copyright(C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2007/09/25 Created   punchMark/index.php                                 //
// 2007/09/26 site_index を INDEX_INDUST へ変更                             //
// 2007/10/20 部品番号別刻印マスター の名称を 部品マスターへ変更            //
// 2007/11/14 全マスターの検索メニューを追加                                //
// 2007/11/15 マスターの更新履歴 照会を追加                                 //
// 2007/11/19 刻印貸出台帳を追加                                            //
// 2007/12/04 刻印貸出台帳の更新履歴を追加                                  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 HTTP CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得
uriIndexCheck();                            // index.phpの指定チェック

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック -1=認証無し 0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_INDUST, 999);         // site_index=99(テストメニュー) site_id=999(サイトメニューを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない(例は生産メニュー)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('刻印管理システム トップ メニュー');
//////////// 表題の設定
$menu->set_caption('刻印管理システム メニュー');

//////////// 呼出先のaction名とアドレス設定
    /************ left view *************/
$menu->set_action('lend_list',          INDUST .  'punchMark/lend_list/punchMark_lendList_Main.php');
$menu->set_action('lend_edit_history',  INDUST .  'punchMark/lend_edit_history/punchMark_lendEditHistory_Main.php');
// $menu->set_action('size_master',        INDUST .  'punchMark/master/punchMark_sizeMasterMnt_Main.php');
// $menu->set_action('shape_master',       INDUST .  'punchMark/master/punchMark_shapeMasterMnt_Main.php');
// $menu->set_action('punchMark_master',   INDUST .  'punchMark/master/punchMark_MasterMnt_Main.php');
// $menu->set_action('parts_master',       INDUST .  'punchMark/master/punchMark_partsMasterMnt_Main.php');
    /************ right view *************/
$menu->set_action('mark_search',        INDUST .  'punchMark/search/punchMark_search_Main.php');
$menu->set_action('edit_history',       INDUST .  'punchMark/edit_history/punchMark_editHistory_Main.php');
$menu->set_action('size_master',        INDUST .  'punchMark/master/punchMark_sizeMasterMnt_Main.php');
$menu->set_action('shape_master',       INDUST .  'punchMark/master/punchMark_shapeMasterMnt_Main.php');
$menu->set_action('punchMark_master',   INDUST .  'punchMark/master/punchMark_MasterMnt_Main.php');
$menu->set_action('parts_master',       INDUST .  'punchMark/master/punchMark_partsMasterMnt_Main.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('punchMark');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 
<link rel='stylesheet' href='<?php echo 'punchMark.css?', $uniq ?>' type='text/css' media='screen'>
-->

<!-- 現在はコメント
<script type='text/javascript' src='../punchMark.js'></script>
-->
<script type='text/javascript'>
<!--
function set_focus()
{
    // 現在設定無し
}
// -->
</script>

<style type='text/css'>
<!--
-->
</style>

</head>
<body style='overflow:hidden;' onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $menu->out_caption() . "\n" ?>
                </td>
            </tr>
            <!-------------------------------------------------------------------------------------------
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $_SERVER['REQUEST_URI'], ' → ', basename($_SERVER['REQUEST_URI']), "<br>\n" ?>
                    <?php echo $_SERVER['SCRIPT_NAME'], ' → ', basename($_SERVER['SCRIPT_NAME']), "\n" ?>
                </td>
            </tr>
            -------------------------------------------------------------------------------------------->
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- widthで間隔を調整 -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('lend_list') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='刻印の貸出台帳メニューを実行します。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_lendList.png', '　貸出台帳メニュー', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('lend_edit_history') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='貸出台帳の更新履歴を照会します。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_lendEditHistory.png', '　貸出台帳更新履歴', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <!--------------------------
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' name='testEmpty' border=0
                                alt='空のアイテム'
                                src='<?php echo IMG ?>menu_item.gif'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                --------------------------->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('mark_search') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='全てのマスターの検索を行います。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_mark_search.png', '　全マスター検索', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('edit_history') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='マスターの更新履歴の照会を行います。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_mark_editHistory.png', '　マスター更新履歴', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('size_master') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='サイズマスター の編集を行います。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_size_master.png', '　サイズマスター', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('shape_master') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='形状マスター の編集を行います。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_shape_master.png', '　形状マスター', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('punchMark_master') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='刻印マスター の編集を行います。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_punchMark_master.png', '　刻印マスター', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('parts_master') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='部品マスター の編集を行います。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_parts_master.png', '　部品マスター', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <!--------------------------
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' name='testEmpty' border=0
                                alt='空のアイテム'
                                src='<?php echo IMG ?>menu_item.gif'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                --------------------------->
                
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
