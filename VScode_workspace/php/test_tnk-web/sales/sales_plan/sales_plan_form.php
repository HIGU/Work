<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 売上 予定 照会 条件選択フォーム                             //
// Copyright(C) 2011-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2011/11/21 Created   sales_plan_form.php                                 //
// 2012/03/28 ＮＫＴ部品出庫分(NKTB)の照会を追加                            //
// 2013/01/29 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま     //
// 2015/05/12 機工対応の為、ツールを追加                                    //
// 2018/06/08 特注A伝販売単価に対応しようとしたが、52％だけではないので保留 //
// 2018/08/21 上のA伝販売単価を復活                                         //
// 2020/12/07 売上予定照会に達成率追加による変更                       和氣 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1, 18);                    // site_index=01(売上メニュー) site_id=18(売上予定明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売 上 予 定 条 件 設 定');
//////////// 表題の設定
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('売上予定',   SALES . 'sales_plan/sales_plan_view.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

/////////////// 受け渡し変数の初期化
if ( isset($_SESSION['s_uri_passwd']) ) {
    $uri_passwd = $_SESSION['s_uri_passwd'];
} else {
    $uri_passwd = '';
}
if ( isset($_SESSION['s_div']) ) {
    $div = $_SESSION['s_div'];
} else {
    $div = '';
}
if ( isset($_SESSION['s_d_start']) ) {
    $d_start = $_SESSION['s_d_start'];
} else {
    if ( isset($_POST['d_start']) ) {
        $d_start = $_POST['d_start'];
    } else {
        $d_start = date('Ymd');
    }
}
if ( isset($_SESSION['s_d_end']) ) {
    $d_end = $_SESSION['s_d_end'];
} else {
    if ( isset($_POST['d_end']) ) {
        $d_end = $_POST['d_end'];
    } else {
        $d_end = date('Ymd');
        $d_end = ( substr($d_end, 0, 6) . last_day(substr($d_end, 0, 4), substr($d_end, 4, 2)) );
    }
}
if ( isset($_SESSION['s_uri_ritu']) ) {
    $uri_ritu = $_SESSION['s_uri_ritu'];
    $uri_ritu = '52.0';     // 初期値
} else {
    $uri_ritu = '52.0';     // 初期値
}
if ( isset($_SESSION['s_shikiri']) ) {
    $shikiri = $_SESSION['s_shikiri'];
} else {
    $shikiri = 'S';     // 初期値
}
if ( isset($_SESSION['uri_assy_no']) ) {
    $assy_no = $_SESSION['uri_assy_no'];
} else {
    $assy_no = '';      // 初期化
}

// 2020/12/07 add. ----------------------------------------------------------->
if ( isset($_SESSION['s_tassei']) ) {
    $tassei = $_SESSION['s_tassei'];
} else {
    $tassei = 'yotei';
}
// <---------------------------------------------------------------------------

// $_SESSION['s_rec_No'] = 0;  // 表示用レコード№を0にする。

if ( isset($_SESSION['s_sales_page']) ) {   // １ページ表示行数設定
    $sales_page = $_SESSION['s_sales_page'];     // 常に Default 25 になるようにコメント解除
    // $sales_page = 25;             // Default 25
} else {
    $sales_page = 25;             // Default 25
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
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>
<script type='text/javascript' src='./sales_plan_form.js?<?php echo $uniq ?>'>
</script>

<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
td {
    font-size: 10pt;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
</style>
<body onLoad='document.uri_form.uri_passwd.focus(); document.uri_form.uri_passwd.select()' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <form name='uri_form' action='<?php echo $menu->out_action('売上予定')?>' method='post' onSubmit='return chk_sales_form(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        パスワードを入れて下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type='password' name='uri_passwd' size='12' value='<?php echo("$uri_passwd"); ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製品グループを選択して下さい<BR>(リニアのみにはリニア修理が含まれています)
                    </td>
                    <td class='winbox' align='center'>
                        <select name="div">
                            <option value=" "<?php if($div==" ") echo("selected"); ?>>全グループ</option>
                            <option value="C"<?php if($div=="C") echo("selected"); ?>>カプラ全体</option>
                            <option value="S"<?php if($div=="S") echo("selected"); ?>>カプラ特注</option>
                            <option value="D"<?php if($div=="D") echo("selected"); ?>>カプラ標準</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>リニア全体</option>
                            <option value="N"<?php if($div=="N") echo("selected"); ?>>リニアのみ</option>
                            <option value="B"<?php if($div=="B") echo("selected"); ?>>液体ポンプ</option>
                            <option value="T"<?php if($div=="T") echo("selected"); ?>>ツール</option>
                            <option value="NKCT"<?php if($div=="NKCT") echo("selected"); ?>>ＮＫＣＴ</option>
                            <option value="NKT"<?php if($div=="NKT") echo("selected"); ?>>ＮＫＴ</option>
                            <option value="NKTB"<?php if($div=="NKTB") echo("selected"); ?>>ＮＫＴ部品出庫分</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        日付を指定して下さい(必須)
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="d_start" size="9" value="<?php echo($d_start); ?>" maxlength="8">
                        ～
                        <input type="text" name="d_end" size="9" value="<?php echo($d_end); ?>" maxlength="8">
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製品番号の指定
                        (指定しない場合は空白)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='assy_no' size='11' value='<?php echo $assy_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        仕切単価の選択<font color='red'><B>※特注のみ</B></font><BR>
                        最新仕切：A伝販売単価52％：A伝52％＞最新
                    </td>
                    <td class='winbox' align='center'>
                        <select name="shikiri">
                            <option value="S"<?php if($shikiri=="S") echo("selected"); ?>>最新仕切</option>
                            <option value="A"<?php if($shikiri=="A") echo("selected"); ?>>A伝販売単価52％</option>
                            <option value="AS"<?php if($shikiri=="AS") echo("selected"); ?>>A伝52％＞最新</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        販売価格に対する仕切単価の率を指定して下さい。(例：52)<br>
                        <font color='red'>(カプラ特注の場合)</font> 指定した率 未満だと<font color='red'>赤色</font>で表示
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="uri_ritu" size="4" value="<?php echo("$uri_ritu"); ?>" maxlength="4">
                        ％ 未満
                    </td>
                </tr>
            <!-- 現在はコメント
                <input type='hidden' name='uri_ritu' value=''>
            -->
                <tr>
                    <td class='winbox' align='right'>
                        １ページの表示行数を指定して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='sales_page' size='4' value="<?php echo("$sales_page"); ?>" maxlength='4'>
                        初期値：25
                    </td>
                </tr>
                <!-- 2020/12/07 add. Start -->
                <tr>
                    <td class='winbox' align='right'>
                        照会内容を指定して下さい
                    </td>
                    <td class='winbox' align='center' colspan='1'>
                        <input type='radio' name='tassei' value='yotei' id='rateYotei'<?php if($tassei!='tassei')echo' checked' ?>><label for='rateYotei'>予定</label>
                        <input type='radio' name='tassei' value='tassei' id='rateTassei'<?php if($tassei=='tassei')echo' checked' ?>><label for='rateTassei'>達成率</label>
                    </td>
                </tr>
                <!-- 2020/12/07 add. End -->
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type="submit" name="照会" value="実行" >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
