<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 売上 メニュー       旧版 uriage_menu.php                    //
// Copyright(C) 2001-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created sales_menu.php                                        //
// 2002/08/07 セッション管理を追加                                          //
// 2002/08/27 フレーム対応 & フレームによるサイトメニュー                   //
// 2002/10/05 processing_msg.php を追加(計算中)                             //
// 2003/02/14 売上関係ニュー のフォントを style で指定に変更                //
//                              ブラウザーによる変更が出来ない様にした      //
// 2003/03/27 月次損益関係の照会を追加  関数 menu_bar() を使用              //
// 2003/11/28 売上明細照会(カプラ特注の単価率対応)を追加 sales_menu.php     //
// 2003/12/10 menu_bar() png 名が間違っているのを訂正  $uniqを使用する      //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/01/27 売上と総材料費の比較表をメニューに追加                        //
// 2004/02/13 index1.php→index.phpへ変更(index1はauthenticateに変更のため) //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/06/10 view_user($_SESSION['User_ID']) をメニューヘッダーの下に追加  //
// 2004/09/21 MenuHeader Class を導入                                       //
// 2004/12/25 style='overflow:hidden;' (-xy両方)を追加                      //
// 2005/01/14 F2/F12キーを有効化する対応のため document.body.focus()を追加  //
// 2005/06/02 標準品の条件別 売上照会を追加                                 //
// 2005/06/09 上記を名前変更 標準品 条件別 売上 → 原価率分析(総材・仕切)   //
// 2005/08/02 各メニュー間の<br>レイアウトを<div>&nbsp;</div>へ変更NN対応   //
// 2006/02/20 総材料費比較2 → 製品売上材料費 へ変更。部品売上材料費を追加  //
// 2006/09/21 売上明細照会S 売上シミュレーション追加                        //
// 2007/04/18 2007/04/02暫定仕切単価アップのシミュレーション追加 simulate3  //
// 2007/04/21 phpのショートカットタグを標準タグへ変更(推奨値へ)             //
// 2007/05/23 組立完成納入分の日東工器未検収照会を追加(industry/へリンク)   //
// 2007/09/20 標準品の最新総材料費の表示用に sales_form_simulate4.phpを追加 //
// 2007/10/08 グラフ作成メニューを追加。E_ALL|E_STRICTへ                    //
// 2008/05/13 手作業賃率変更用に sales_form_simulate7.phpを追加        大谷 //
// 2011/04/05 2011/04/01仕切価格改定の影響額を表示する為変更           大谷 //
// 2011/11/21 売上予定照会をメニューに追加                             大谷 //
// 2013/01/29 メニュー量が増えてきたのでサイドバーの非表示を解除       大谷 //
// 2013/05/13 仕切改定差額を基準日指定に変更の為、タイトルを変更       大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');
require_once ('../tnk_func.php');
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(1, 999);                    // site_index=40(売上メニュー) site_id=999(サイトメニューを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売 上 メニュー');
//////////// 表題の設定
$menu->set_caption('売上関係照会 メニュー');
//////////// 呼出先のaction名とアドレス設定
    /************ left view *************/
$menu->set_action('売上明細照会',       SALES . 'details/sales_form.php');
$menu->set_action('製品売上未検収',     SALES . 'sales_miken/sales_miken_Main.php');
$menu->set_action('売上照会特注カプラ', SALES . 'custom/sales_custom_form.php');
$menu->set_action('原価率分析',         SALES . 'sales_material/sales_standard_form.php');
$menu->set_action('総材料費比較',       SALES . 'materialCost_sales_comp.php');
$menu->set_action('製品売上材料費',     SALES . 'materialCost_sales_comp2.php');    // 上記を明細化したもの
$menu->set_action('部品売上材料費',     SALES . 'parts_material/parts_material_show_Main.php');
$menu->set_action('月次損益照会'      , SALES . 'profit_loss_query_menu.php');
$menu->set_action('グラフ作成メニュー', PL . 'graphCreate/graphCreate_Form.php');
    /************ right view *************/
$menu->set_action('製品部品売上グラフ', SALES . 'view_all_hiritu.php');
$menu->set_action('CL売上グラフ'      , SALES . 'view_cl_graph.php');
$menu->set_action('特注標準売上グラフ', SALES . 'uriage_graph_sp_std.php');
$menu->set_action('特注標準実際グラフ', SALES . 'uriage_graph_sp_std_jissai.php');
$menu->set_action('売上日計グラフ',     SALES . 'uriage_graph_daily_select.php');
$menu->set_action('売上月計グラフ',     SALES . 'uriage_graph_all_tuki.php');
$menu->set_action('売上明細照会S1',     SALES . 'details/sales_form_simulate1.php');    // 売上シミュレーション1追加
$menu->set_action('売上明細照会S2',     SALES . 'details/sales_form_simulate2.php');    // 売上シミュレーション2追加
$menu->set_action('売上明細照会S3',     SALES . 'details/sales_form_simulate3.php');    // 2007/04/02暫定仕切単価アップの売上シミュレーション3追加
$menu->set_action('売上明細照会S4',     SALES . 'details/sales_form_simulate4.php');    // 標準品の最新総材料費の表示用に売上シミュレーション4追加
$menu->set_action('売上明細照会S5',     SALES . 'details/sales_form_simulate8.php');    // 仕切価格改定の差額明細一覧表
$menu->set_action('売上明細照会S7',     SALES . 'details/sales_form_simulate7.php');    // 2008/05/13賃率変更可能な総材料費のシュミレーション
$menu->set_action('製品グループ別売上明細照会',     SALES . 'details/sales_form_product.php');      // 売上照会 製品グループ別（明細）
$menu->set_action('製品グループ別売上集計照会',     SALES . 'details/sales_form_product_all.php');      // 売上照会 製品グループ別（集計）
$menu->set_action('売上予定照会',       SALES . 'sales_plan/sales_plan_form.php');
$menu->set_action('売上実績照会',       SALES . 'actual/sales_actual_form.php');

//////////////// 各アンカーに変数でセットする 関数コールのオーバーヘッドを１回で済ませるため
$uniq = uniqid('menu');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<!-- 現在はコメント
<script type='text/javascript' src='../sales.js'></script>
-->
<script type='text/javascript'>
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>

<style type='text/css'>
<!--
body {
    background-image:       url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
    /* overflow-y:             hidden; */
}
-->
</style>

</head>
<body onLoad='set_focus()'>
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
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- widthで間隔を調整 -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0' cellspacing='0' cellpadding='5'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('売上明細照会') ?>'>
                        <td align='center'>
                            <input type='image' alt='売上明細照会(カプラ特注単価率対応)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form.png', '売 上 明 細 照 会', 14) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('製品売上未検収') ?>'>
                        <td align='center'>
                            <input type='image' alt='組立完成納入分の日東工器側、未検収明細の照会を行います。' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_miken.png', '売 上 未 検 収 照 会', 14) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('売上照会特注カプラ') ?>'>
                        <td align='center'>
                            <input type='image' alt='特注カプラの条件別 売上合計表 及び 明細表の照会' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_custom_form.png', '特注カプラ条件別売上', 13, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('原価率分析') ?>'>
                        <td align='center'>
                            <input type='image' alt='総材料比率・仕切単価率の分析メニュー（条件別 各比率の合計表 及び 明細表・グラフの照会）' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_standard_form.png', '原価率分析(総材・仕切)', 13, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('総材料費比較') ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='売上と総材料費の比較表' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_materialCost_sales_comp.png', '売上と総材料費 比較', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('製品売上材料費') ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='製品売上と各材料費の比較表(詳細明細あり)' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_materialCost_sales_comp2.png', '製品売上の材料費照会', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('部品売上材料費') ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='部品売上の各材料費を照会します。' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_materialCost_sales_parts.png', '部品売上の材料費照会', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('月次損益照会') ?>'>
                        <td align='center'>
                            <input type='image' alt='月次損益関係の照会' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_pl_query_menu.png', '月次損益関係の照会', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('グラフ作成メニュー')?>'>
                        <td align='center'>
                            <input type='image' alt='損益関係のグラフ作成メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_graphCreate.png', '損益グラフ作成メニュー', 13, 0) . "?id=$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('売上明細照会S5')?>'>
                        <td align='center'>
                            <input type='image' alt='仕切改定による売上差額 明細一覧' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_form_s8-4.png', '仕切改定売上差額', 13, 0) . "?id=$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('売上明細照会S7') ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='手作業賃率変更シュミレーション' border=0 src=<?php echo menu_bar('menu_tmp/menu_item_form_s7.png', '手作業賃率変更シュミレーション', 10, 0) . "?id=$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <!--
                <tr>
                    <form method='post' action='<?php echo SALES_MENU ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                        </td>
                    </form>
                </tr>
                -->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0' cellspacing='0' cellpadding='5'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('製品グループ別売上明細照会') ?>'>
                        <td align='center'>
                            <input type='image' alt='製品グループ別の売上明細を照会' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_product.png', '製品グループ別売上明細照会', 11) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('製品グループ別売上集計照会') ?>'>
                        <td align='center'>
                            <input type='image' alt='製品グループ別の売上を集計表形式で照会' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_product_all.png', '製品グループ別売上集計照会', 11) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('売上予定照会') ?>'>
                        <td align='center'>
                            <input type='image' alt='売上予定照会(組立計画のみ)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_plan_form.png', '売 上 予 定 照 会', 14) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method="post" action='/processing_msg.php?script=<?php echo $menu->out_action('製品部品売上グラフ') ?>'>
                        <td align='center'>
                            <input type='image' value='製品・部品の売上グラフ' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_hiritu_graph.png', '製品・部品の売上グラフ', 13) . "?$uniq" ?>'>
                            <!-- <input type='image' value='製品・部品の売上グラフ' border=0 src='<?php echo IMG ?>menu_item_uriage_hiritu.gif'> -->
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='/processing_msg.php?script=<?php echo $menu->out_action('CL売上グラフ') ?>'>
                        <td align='center'>
                            <input type='image' alt='カプラ・リニア売上グラフ' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_cl_graph.png', 'カプラ・リニア売上グラフ', 11, 0) . "?$uniq" ?>'>
                            <!-- <input type='image' value='カプラ・リニア売上グラフ' border=0 src='<?php echo IMG ?>menu_item_uriage_cl.gif'> -->
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='/processing_msg.php?script=<?php echo $menu->out_action('特注標準売上グラフ') ?>'>
                        <td align='center'>
                            <input type='image' alt='カプラ特注品・標準品 売上推移グラフ' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_sp_std_graph.png', 'カプラ特注品・標準品グラフ', 11) . "?$uniq" ?>'>
                            <!-- <input type='image' value='カプラ特注・標準グラフ' border=0 src='<?php echo IMG ?>menu_item_uriage_sp_std.gif'> -->
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='/processing_msg.php?script=<?php echo $menu->out_action('特注標準実際グラフ') ?>'>
                        <td align='center'>
                            <input type='image' alt='カプラ特注品・標準品 実際原価 比較グラフ' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_sp_std_jissai_graph.png', 'カプラ特注・標準 実際原価', 11) . "?$uniq" ?>'>
                            <!-- <input type='image' value='カプラ特注標準実際原価' border=0 src='<?php echo IMG ?>menu_item_uriage_sp_std_jissai.gif'> -->
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('売上日計グラフ') ?>'>
                        <td align='center' class='margin1'>
                            <input type='image' alt='全体・カプラ・リニア日計の売上グラフ' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_daily_form.png', '売 上 日 計 グラフ', 14) . "?$uniq" ?>'>
                            <!-- <input type='image' name='uriage_graph_niti' border=0 src='<?php echo IMG ?>menu_item_niti_graph.gif'> -->
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method="post" action='/processing_msg.php?script=<?php echo $menu->out_action('売上月計グラフ') ?>'>
                        <td align='center' class='margin1'>
                            <input type='image' alt='全体・カプラ・リニア月計の売上グラフ' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_uriage_monthly_graph.png', '売 上 月 計 グラフ', 14) . "?$uniq" ?>'>
                            <!-- <input type='image' name='uriage_graph_tuki' border=0 src='<?php echo IMG ?>menu_item_tuki_graph.gif'> -->
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method="post" action='/processing_msg.php?script=<?php echo $menu->out_action('売上実績照会') ?>'>
                        <td align='center' class='margin1'>
                            <input type='image' alt='売上実績照会(組立計画のみ)' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_sales_actual_form.png', '売 上 実 績 照 会', 14) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?php echo SALES_MENU ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                        </td>
                    </form>
                </tr>
                <!--
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('売上明細照会S1') ?>'>
                        <td align='center'>
                            <input type='image' alt='売上明細照会(2006年4月の仕切アップ前でシミュレーションする)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_s.png', '売１シミュレーション', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('売上明細照会S2') ?>'>
                        <td align='center'>
                            <input type='image' alt='売上明細照会(最初の仕切単価のままでシミュレーションする)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_s2.png', '売２シミュレーション', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('売上明細照会S3') ?>'>
                        <td align='center'>
                            <input type='image' alt='売上明細照会(2007年4月2日暫定仕切単価のシミュレーションをする)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_s3.png', '2007/4/2までの総材料', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('売上明細照会S4') ?>'>
                        <td align='center'>
                            <input type='image' alt='売上明細照会(2007年10月から暫定仕切単価のシミュレーションをするため最新総材料費を標準品のみ表示)' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_sales_form_s4.png', '標準品の最新総材料費', 14, 0) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                -->
                
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
