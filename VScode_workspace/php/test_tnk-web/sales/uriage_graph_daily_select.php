<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 日次 グラフ (年月選択)                                              //
// Copyright (C) 2002-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/04/08 Created   uriage_graph_daily_select.php                       //
// 2002/08/08 セッション管理に切替え                                        //
// 2002/08/27 フレーム対応                                                  //
// 2002/10/05 processing_msg.php を追加(計算中)                             //
// 2003/09/06 error_reporting = E_ALL 対応のため 配列変数の初期化追加       //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/02/09 selectした時に即実行するようにJavaScriptでロジック追加        //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/10/03 グラフ表示側でlocalセッションへ移行による  E_ALL | E_STRICTへ //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1,  3);                    // site_index=1(売上メニュー) site_id=3(日計グラフ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SALES_MENU);              // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売上日計グラフ(年月指定)');
//////////// 表題の設定
$menu->set_caption('日計グラフ(年月指定)');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('日計グラフ',   SALES . 'uriage_graph_all_niti.php');

$yyyymm = date('Ym');
if ( isset($_REQUEST['yyyymm']) ) {
    $s_yyyymm = $_REQUEST['yyyymm'];
} else {
    $s_yyyymm = '';
}

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

<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
td {
    font-size:          0.85em;
    font-weight:        normal;
    font-family:        monospace;
}
-->
</style>
<script language="JavaScript">
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    document.ym_form.yyyymm.focus();
}
// -->
</script>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <tr><td valign="top">
                <table align='center'>
                    <tr><td><p>
                        <!-- <img src='<?php echo IMG ?>t_nitto_logo3.gif' width=348 height=83> -->
                        <img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83>
                    </p></td></tr>
                </table>
                <table width='100%'>
                    <tr>
                        <td align='center'>
                            <span class='caption_font'><?php echo $menu->out_caption()?></span>
                        </td>
                    </tr>
                    <tr>
                        <td align='center'>
                            <br>
                            <img src='<?php echo IMG ?>tnk-turbine_small.gif'>
                        </td>
                    </tr>
                </table>
                <table width='100%' cellspacing='0' cellpadding='3'>
                    <form name='ym_form' action='<?php echo $menu->out_action('日計グラフ')?>' method='get'>
                        <tr>
                            <td align='center'>
                                表示する年月を指定して下さい。
                                <select name='yyyymm' onChange='document.ym_form.submit()'>
                                    <?php
                                    if ($s_yyyymm == $yyyymm) {
                                        echo "<option value='$yyyymm' selected>$yyyymm</option>\n";
                                    } else {
                                        echo "<option value='$yyyymm'>$yyyymm</option>\n";
                                    }
                                        // 当月より以前の各 年月はワークファイルを参照する
                                    $query_wrk = "select 年月 from wrk_uriage where 年月>=200010 order by 年月 desc";
                                    $res_wrk = array();
                                    if ($rows_wrk = getResult($query_wrk,$res_wrk)) {
                                        for ($cnt=0; $cnt<$rows_wrk; $cnt++) {
                                            if ($s_yyyymm == $res_wrk[$cnt][0]) {
                                                echo "<option value=" . $res_wrk[$cnt][0] . " selected>" . $res_wrk[$cnt][0] . "</option>\n";
                                            } else {
                                                echo "<option value=" . $res_wrk[$cnt][0] . ">" . $res_wrk[$cnt][0] . "</option>\n";
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                                <br>例：200202 （2002年02月）
                            </td>
                        </tr>
                        <tr>
                            <td align='center'>
                                <input type='submit' name='exec_graph' value='実行' >
                            </td>
                        </tr>
                    </form>
                </table>
            </td></tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
