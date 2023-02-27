<?php
//////////////////////////////////////////////////////////////////////////////
// 買掛実績の照会 条件選択フォーム  更新元 UKWLIB/W#HIBCTR                  //
// Copyright (C) 2004-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/05/13 Created  act_payable_form.php                                 //
// 2004/05/17 start日付の初期化を前月の１日～に変更 買掛金の合計を表示追加  //
//            発注先(協力工場)の指定を追加                                  //
// 2004/12/07 ディレクトリを階層下の industry/payable に変更                //
// 2004/12/28 form の method を post から get へ変更 BackSpace Key対応      //
// 2004/12/29 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/03/28 協力工場毎の合計金額のサマリー照会(年月による期間指定)を追加  //
// 2005/04/25 買掛 科目を指定して検索出来る様に追加 (AND検索)               //
// 2006/01/16 買掛科目の番号入力欄に ブランク=全て を追加                   //
// 2006/01/24 SESSIONクラスを使用して開始・終了日をローカルセッションに登録 //
//            単体照会と他のメニューからのリンク照会とを明確に分けるため    //
// 2007/05/17 初回の年月日のDEFAULTを１ヶ月前からを５年前からに変更         //
// 2011/12/27 NKCT及びNKT対応の為、部門を追加                          大谷 //
// 2012/10/01 表示件数を４桁まで可能に変更                             大谷 //
// 2013/04/09 生産管理課依頼 生管購入部品のみの照会を追加              大谷 //
// 2014/04/17 生管依頼により科目２～３を１～３へ変更                   大谷 //
// 2015/05/21 機工生産に対応                                           大谷 //
// 2015/08/26 条件日付を保持するように変更                             大谷 //
// 2016/01/29 各種項目が保持されなかったため修正                       大谷 //
// 2017/07/19 86,107行目 $str_date = $_SESSION['paya_strdate']; が          //
//            エラーとなっていた為、修正                               大谷 //
// 2018/01/29 カプラ特注・標準を追加                                   大谷 //
// 2018/06/29 条件日付が保持されていないのを訂正                       大谷 //
// 2019/05/10 部門と発注先コードを部品番号指定しても無視しないよう変更      //
//            したので、文言を修正                                     大谷 //
// 2019/06/25 開始日の初期値を7年前に変更。総材料費でないことがある為  大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);     // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 10);                    // site_index=40(生産メニュー) site_id=10(買掛実績)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('買 掛 実 績 の 照 会 (条件選択)');
//////////// 表題の設定
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('買掛実績表示',     INDUST . 'payable/act_payable_view.php');
$menu->set_action('買掛金集計表',     INDUST . 'payable/payable_vendor_summary.php');
$menu->set_action('買掛金集計表２',   INDUST . 'payable/payable_vendor_summary2.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// セッションのインスタンスを生成
$session = new Session();

/////////////// 受け渡し変数の初期化
if ($session->get('paya_parts_no') != '') {
    $parts_no = $session->get('paya_parts_no');
} else {
    $parts_no = '';              // 初期化
}
if ($session->get('paya_kamoku') != '') {
    $kamoku = $session->get('paya_kamoku');
} else {
    $kamoku = '';              // 初期化
}
if ( isset($_SESSION['payable_div']) ) {
    $div = $_SESSION['payable_div'];
} else {
    $div = '';
    $_SESSION['payable_div'] = $div;
}
if ( isset($_SESSION['paya_vendor']) ) {
    $vendor = $_SESSION['paya_vendor'];
} else {
    $vendor = '';
    $_SESSION['paya_vendor'] = $vendor;
}
if(isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    /*************************************
    $year  = date('Y');
    $month = date('m') - 1; // １ヶ月前からをコメント
    if ($month == 0) {
        $month = 12;
        $year -= 1;
    } else {
        $month = sprintf('%02d', $month);
    }
    *************************************/
    //$year  = date('Y') - 5; // ５年前から
    $year  = date('Y') - 7; // ７年前から
    $month = date('m');
    $str_date = $year . $month . '01';  // 初期化 (前月の１日からに変更)
    $session->add_local('paya_strdate', $str_date);
}
if(isset($_SESSION['paya_enddate'])) {
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '99999999';     // 初期化
    $session->add_local('paya_enddate', $end_date);
}

if ( isset($_SESSION['payable_page']) ) {   // １ページ表示行数設定
    $paya_page = $_SESSION['payable_page'];
} else {
    $paya_page = 25;             // Default 25
    $_SESSION['payable_page'] = $paya_page;
}

/////////// summary_view で追加されたパラメーター
if ( isset($_SESSION['payable_s_ym']) ) {
    $s_ym = $_SESSION['payable_s_ym'];
} else {
    $s_ym = '';
}
if ( isset($_SESSION['payable_e_ym']) ) {
    $e_ym = $_SESSION['payable_e_ym'];
} else {
    $e_ym = '';
}
if ( isset($_SESSION['payable_div']) ) {
    $sum_div = $_SESSION['payable_div'];
} else {
    $sum_div = ' ';
}

/////////// summary2_view で追加されたパラメーター
if ( isset($_SESSION['payable_s2_ym']) ) {
    $s2_ym = $_SESSION['payable_s2_ym'];
} else {
    $s2_ym = '';
}
if ( isset($_SESSION['payable_e2_ym']) ) {
    $e2_ym = $_SESSION['payable_e2_ym'];
} else {
    $e2_ym = '';
}
if ( isset($_SESSION['payable2_div']) ) {
    $sum2_div = $_SESSION['payable2_div'];
} else {
    $sum2_div = ' ';
}

/////////// summary_view が押された
if (isset($_REQUEST['summary_view'])) {
    if (isset($_REQUEST['s_ym'])) {
        $_SESSION['payable_s_ym'] = $_REQUEST['s_ym'];
    }
    if (isset($_REQUEST['e_ym'])) {
        $_SESSION['payable_e_ym'] = $_REQUEST['e_ym'];
    }
    if (isset($_REQUEST['sum_div'])) {
        $_SESSION['payable_div'] = $_REQUEST['sum_div'];
    }
    header('Location: ' . H_WEB_HOST . $menu->out_action('買掛金集計表'));
}

/////////// summary2_view が押された
if (isset($_REQUEST['summary2_view'])) {
    if (isset($_REQUEST['s2_ym'])) {
        $_SESSION['payable_s2_ym'] = $_REQUEST['s2_ym'];
    }
    if (isset($_REQUEST['e2_ym'])) {
        $_SESSION['payable_e2_ym'] = $_REQUEST['e2_ym'];
    }
    if (isset($_REQUEST['sum2_div'])) {
        $_SESSION['payable2_div'] = $_REQUEST['sum2_div'];
    }
    header('Location: ' . H_WEB_HOST . $menu->out_action('買掛金集計表２'));
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

<!--    ファイル指定の場合 -->
<script language='JavaScript' src='./act_payable_form.js?<?= $uniq ?>'>
</script>

<script language="JavaScript">
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
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
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
.caption_font {
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   blue;
    color:              white;
}
-->
</style>
</head>
</style>
<body onLoad='document.payable_form.parts_no.focus(); document.payable_form.parts_no.select()'>
    <center>
<?=$menu->out_title_border()?>
        
        <form name='payable_form' action='<?=$menu->out_action('買掛実績表示')?>' method='get' onSubmit='return chk_payable_form(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' bgcolor='yellow' colspan='2' align='center'>
                        <div class='caption_font'><?=$menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        部品番号の指定(指定しない場合は下の部門を指定して下さい)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts_no' class='pt12b' size='9' value='<?= $parts_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <!--
                    <td class='winbox' align='right'>
                        部門を選択して下さい(部品を指定した場合は無視されます)
                    </td>
                    -->
                    <td class='winbox' align='right'>
                        部門を選択して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($div==' ') echo 'selected' ?>>全部門</option>
                            <option value='C'<?php if($div=='C') echo 'selected' ?>>カプラ</option>
                            <option value='D'<?php if($div=='D') echo 'selected' ?>>Ｃ標準</option>
                            <option value='S'<?php if($div=='S') echo 'selected' ?>>Ｃ特注</option>
                            <option value='L'<?php if($div=='L') echo 'selected' ?>>リニア</option>
                            <!-- <option value='B'<?php if($div=='B') echo 'selected' ?>>バイモル</option> -->
                            <option value='T'<?php if($div=='T') echo 'selected' ?>>ツール</option>
                            <option value='NKCT'<?php if($div=='NKCT') echo 'selected' ?>>ＮＫＣＴ</option>
                            <option value='NKT'<?php if($div=='NKT') echo 'selected' ?>>ＮＫＴ</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <!--
                    <td class='winbox' align='right'>
                        発注先のコードを指定(部品を指定した場合は無視されます)
                    </td>
                    -->
                    <td class='winbox' align='right'>
                        発注先のコードを指定
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='vendor' class='pt12b' size='5' value='<?= $vendor ?>' maxlength='5'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        買掛 科目 番号を指定 (AND検索になります)ブランク=全て
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='kamoku' class='pt12b' size='3' value='<?= $kamoku ?>' maxlength='2' style='text-align:center;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        日付を指定して下さい(必須)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='str_date' size='9' value='<?php echo($str_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        ～
                        <input type='text' name='end_date' size='9' value='<?php echo($end_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        １ページの表示行数を指定して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='paya_page' size='4' value='<?= $paya_page ?>' maxlength='4' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        初期値：25
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='paya_view' value='実行' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
            <div class='pt11b'>＊NKCT及びNKTは棚番の先頭が'8'の物と受付番号の先頭が'H'の物が対象</div>
        </form>
        
        <hr>
        
        <form name='summary_form' action='<?=$menu->out_self()?>' method='get'>
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' bgcolor='yellow' colspan='2' align='center'>
                        <div class='caption_font'>協力工場毎の合計買掛金額の照会</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        開始年月に指定
                    </td>
                    <td class='winbox' align='center'>
                        <select name='s_ym' class='pt11b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != '01') {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($s_ym == $ym) {
                                    printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200010)
                                    break;
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        終了年月に指定
                    </td>
                    <td class='winbox' align='center'>
                        <select name='e_ym' class='pt11b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != '01') {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($e_ym == $ym) {
                                    printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200010)
                                    break;
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        対象グループの指定
                    </td>
                    <td class='winbox' align='center'>
                        <select name='sum_div' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($sum_div==' ') echo 'selected' ?>>グループ全体</option>
                            <option value='C'<?php if($sum_div=='C') echo 'selected' ?>>カプラ全体</option>
                            <option value='D'<?php if($sum_div=='D') echo 'selected' ?>>カプラ標準</option>
                            <option value='S'<?php if($sum_div=='S') echo 'selected' ?>>カプラ特注</option>
                            <option value='L'<?php if($sum_div=='L') echo 'selected' ?>>リニア全体</option>
                            <option value='NKCT'<?php if($sum_div=='NKCT') echo 'selected' ?>>ＮＫＣＴ</option>
                            <option value='NKT'<?php if($sum_div=='NKT') echo 'selected' ?>>ＮＫＴ</option>
                            <!-- <option value='B'<?php if($sum_div=='B') echo 'selected' ?>>バイモル</option> -->
                            <option value='T'<?php if($sum_div=='T') echo 'selected' ?>>ツール</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='summary_view' value='実行' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
            <div class='pt11b'>＊買掛 科目 １～５(仕入)までが対象</div>
        </form>
        
        <hr>
        
        <form name='summary2_form' action='<?=$menu->out_self()?>' method='get'>
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' bgcolor='yellow' colspan='2' align='center'>
                        <div class='caption_font'>協力工場毎の合計買掛金額の照会</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        開始年月に指定
                    </td>
                    <td class='winbox' align='center'>
                        <select name='s2_ym' class='pt11b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != '01') {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($s2_ym == $ym) {
                                    printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200010)
                                    break;
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        終了年月に指定
                    </td>
                    <td class='winbox' align='center'>
                        <select name='e2_ym' class='pt11b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != '01') {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($e2_ym == $ym) {
                                    printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200010)
                                    break;
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        対象グループの指定
                    </td>
                    <td class='winbox' align='center'>
                        <select name='sum2_div' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($sum2_div==' ') echo 'selected' ?>>グループ全体</option>
                            <option value='C'<?php if($sum2_div=='C') echo 'selected' ?>>カプラ全体</option>
                            <option value='D'<?php if($sum2_div=='D') echo 'selected' ?>>カプラ標準</option>
                            <option value='S'<?php if($sum2_div=='S') echo 'selected' ?>>カプラ特注</option>
                            <option value='L'<?php if($sum2_div=='L') echo 'selected' ?>>リニア全体</option>
                            <option value='NKCT'<?php if($sum2_div=='NKCT') echo 'selected' ?>>ＮＫＣＴ</option>
                            <option value='NKT'<?php if($sum2_div=='NKT') echo 'selected' ?>>ＮＫＴ</option>
                            <!-- <option value='B'<?php if($sum_div=='B') echo 'selected' ?>>バイモル</option> -->
                            <option value='T'<?php if($sum2_div=='T') echo 'selected' ?>>ツール</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='summary2_view' value='実行' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
            <div class='pt11b'>＊買掛 科目 １～３(製造・注文No.有)が対象</div>
        </form>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
