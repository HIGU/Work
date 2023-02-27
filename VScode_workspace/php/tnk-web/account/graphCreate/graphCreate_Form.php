<?php
//////////////////////////////////////////////////////////////////////////////
// 経費内訳の分析用グラフ作成メニュー 条件選択フォーム                      //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/10/04 Created   graphCreate_Form.php                                //
// 2007/10/07 グラフの値表示・非表示追加。Y軸１個(共用)・２個(別々)を追加   //
// 2007/10/13 X軸の年月をprot1とprot2別々に設定できるオプションを追加       //
// 2007/11/05 Y軸の初期値2=別々へX軸の初期値on=共有へ初期値を変更           //
// 2007/11/06 損益グラフ作成メニューを経費内訳グラフ作成メニューへ改造      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('memory_limit', '64M');             // グラフ等で必要メモリーが足らない場合に指定
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
require_once ('graphCreate_Function.php');  // グラフ作成メニュー共用関数
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_ACT, 15);             // site_index=(経理メニュー) site_id=15(経費グラフ)999(未定)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SALES_MENU);              // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('経費内訳 分析用 グラフ作成メニュー(条件指定フォーム)');
//////////// 表題の設定
$menu->set_caption('作成するグラフの条件を指定して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('グラフ作成',   ACT . 'graphCreate/graphCreate_Main.php');

//////////// リクエストのインスタンスを生成
$request = new Request();
if ($request->get('yaxis') == '') $request->add('yaxis', '2');     // 初期値を2の別々へ変更 2007/11/05
if ($request->get('dataxFlg') == '') $request->add('dataxFlg', 'on');   // 初期値をonへ変更 2007/11/05

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('dailyGraph');

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
<script type='text/javascript' src='graphCreate.js?<?php echo $uniq ?>'></script>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
    font-size:          1.05em;
    font-weight:        bold;
}
td {
    font-size:          0.85em;
    font-weight:        normal;
    font-family:        monospace;
}
body {
    background-image:       url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
    overflow-y:             hidden;
}
-->
</style>
<script type='text/javascript' language='JavaScript'>
<!--
// -->
</script>
</head>
<body onLoad='GraphCreate.set_focus(document.ConditionForm.yyyymm, "noSelect")'
>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr><td align='center' valign='top'>
                <table align='center'>
                    <tr><td><p>
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
                <table border='0' cellspacing='0' cellpadding='5'>
                    <form name='ConditionForm' action='<?php echo $menu->out_action('グラフ作成')?>' method='get'
                        onSubmit='return GraphCreate.checkConditionForm(this)'
                    >
                        <tr>
                            <td align='left'>
                                １．グラフ１の指定　プロット１<?php echo graphSelectForm('g1plot1', $request->get('g1plot1')) ?>　プロット２<?php echo graphSelectForm('g1plot2', $request->get('g1plot2')) ?>
                            </td>
                        </tr>
                        <tr>
                            <td align='left'>
                                ２．グラフ２の指定　プロット１<?php echo graphSelectForm('g2plot1', $request->get('g2plot1')) ?>　プロット２<?php echo graphSelectForm('g2plot2', $request->get('g2plot2')) ?>
                            </td>
                        </tr>
                        <tr>
                            <td align='left'>
                                ３．グラフ３の指定　プロット１<?php echo graphSelectForm('g3plot1', $request->get('g3plot1')) ?>　プロット２<?php echo graphSelectForm('g3plot2', $request->get('g3plot2')) ?>
                            </td>
                        </tr>
                        <tr>
                            <td align='left'>
                                ４．グラフのY軸の指定　　　　
                                <input type='radio' name='yaxis' value='1'<?php echo getRadioChecked($request, 'yaxis', 1)?> id='g11'><label for='g11'>Y軸１個(共用)</label>
                                <input type='radio' name='yaxis' value='2'<?php echo getRadioChecked($request, 'yaxis', 2)?> id='g12'><label for='g12'>Y軸２個(別々)</label>
                            </td>
                        </tr>
                        <tr>
                            <td align='left'>
                                　　　　　　　　　　　　　　　 
                                Y軸１個は主に金額差を含めた比較をする場合に使用します。<br>
                                　　　　　　　　　　　　　　　 
                                Y軸２個は主に傾向を比較する場合に使用します。
                            </td>
                        </tr>
                        <tr>
                            <td align='left'>
                                ５．グラフの終了年月の指定 　
                                <label for='dataxCheck'>共用の場合チェック</label><input type='checkbox' name='dataxFlg' id='dataxCheck'
                                    value='<?php echo $request->get('dataxFlg')?>'<?php if ($request->get('dataxFlg') == 'on') echo ' checked';?>
                                    onClick='GraphCreate.checkboxAction(this);'
                                >
                                プロット1<?php echo ymFormCreate($request->get('dataxFlg'), $request->get('yyyymm1'), 'yyyymm1', 'onChange="GraphCreate.prot1Action()"') ?>
                                プロット2<?php echo ymFormCreate($request->get('dataxFlg'), $request->get('yyyymm2'), 'yyyymm2') ?>
                            </td>
                        </tr>
                        <tr>
                            <td align='center'>
                                <input type='submit' name='createGraph' value='実行' >
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
