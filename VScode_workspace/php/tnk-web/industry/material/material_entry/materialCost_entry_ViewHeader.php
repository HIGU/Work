<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の登録 materialCost_entry_ViewHeader.php                         //
// Copyright (C) 2008 - 2015 Norihisa.Ohya                                  //
// Changed history                                                          //
// 2007/05/23 Created   metarialCost_entry_main.php                         //
// 2007/06/21 phpショートタグ→標準タグへ。 HTMLの余分なソースを削除 小林   //
//            $menu->out_retF2Script() 追加 小林                            //
// 2007/06/22 $uniqが使用されていないのでコメントアウト。小林               //
// 2007/09/14 総材料費の最新登録の戻り先製品番号指定 小林                   //
// 2007/09/18 E_ALL | E_STRICT へ変更 小林                                  //
// 2007/09/19 elseif (substr($plan_no, 0, 2) == 'ZZ') 25.60 を追加 小林     //
// 2008/02/14 if (substr($assy_no, 0, 1) == 'C') 25.6 else 37.0 を追加      //
// 2015/05/21 機工製品の総材料費登録に対応                                  //
// 2020/06/11 総材料費自動登録時、戻った時の表示を維持する為 追加      和氣 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');     // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');     // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 21);                    // site_index=30(生産メニュー) site_id=21(総材料費の登録)
/////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 の 登 録 (工程明細)');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('旧総材料費登録',   INDUST . 'material/materialCost_entry_old.php');
//////////// 戻先へのGETデータ設定
$menu->set_retGET('page_keep', 'On');
$menu->set_retGET('material', '1');   // 総材料費自動登録時、戻った時の表示を維持する為

$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

$request = new Request;
$session = new Session;
//////////// ブラウザーのキャッシュ対策用
// $uniq = $menu->set_useNotCache('target');

if (isset($_REQUEST['msg_flg'])) {
    $msg_flg = 'alert';
} else {
    $msg_flg = 'site';
}
//////////// 計画番号・製品番号を取得
$plan_no = $session->get('plan_no');
$assy_no = $session->get('assy_no');

//////////// 総材料費の最新登録の戻り先製品番号指定
if (substr($plan_no, 0, 2) == 'ZZ') $menu->set_retGET('assy', $assy_no);

//////////// レートを計画番号から取得(後日マスター式に変更予定)
if (substr($plan_no, 0, 1) == 'C') {
    $sql2 = "
        SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $sc = '';
    getUniResult($sql2, $sc);
    if ($sc == 'SC') {
        define('RATE', 25.60);  // カプラ特注
    } else {
        $sql2 = "
            SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $kan = '';
        getUniResult($sql2, $kan);
        if ($kan < 20071001) {
            define('RATE', 25.60);  // カプラ標準 2007/10/01価格改定以前
        } elseif ($kan < 20110401) {
            define('RATE', 57.00);  // カプラ標準 2007/10/01価格改定以降
        } else {
            define('RATE', 45.00);  // カプラ標準 2011/04/01価格改定以降
        }
    }
} elseif (substr($plan_no, 0, 2) == 'ZZ') {
    if (substr($assy_no, 0, 1) == 'C') {
        if ($kan < 20110401) {
            define('RATE', 57.00);  // カプラ標準 2007/10/01価格改定以降
        } else {
            define('RATE', 45.00);  // カプラ標準 2011/04/01価格改定以降
        }
    } elseif (substr($assy_no, 0, 1) == 'L') {
        if ($kan < 20110401) {
            define('RATE', 44.00);  // リニア 2007/10/01価格改定以降
        } else {
            define('RATE', 53.00);  // リニア 2011/04/01価格改定以降
        }
    } else {
        define('RATE', 50.00);  // ツール
    }
} elseif (substr($plan_no, 0, 1) == 'L') {
    $sql2 = "
        SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $kan = '';
    getUniResult($sql2, $kan);
    if ($kan < 20081001) {
        define('RATE', 37.00);  // リニア 2008/10/01価格改定以前
    } elseif ($kan < 20110401) {
        define('RATE', 44.00);  // リニア 2008/10/01価格改定以降
    } else {
        define('RATE', 53.00);  // リニア 2011/04/01価格改定以降
    }
} else {
    $sql2 = "
        SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $kan = '';
    getUniResult($sql2, $kan);
    define('RATE', 50.00);  // ツール
}

//////////// 製品名の取得
$query = "select midsc from miitem where mipn='{$assy_no}'";
if ( getUniResult($query, $assy_name) <= 0) {           // 製品名の取得
    $_SESSION['s_sysmsg'] .= "製品名の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

//////////// 表題の設定
// $menu->set_caption('現在の契約賃率：' . number_format(RATE, 2));
$menu->set_caption("計画番号：{$plan_no}&nbsp;&nbsp;製品番号：{$assy_no}&nbsp;&nbsp;製品名：{$assy_name}");

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<script language="JavaScript">
</script>

<style type="text/css">
<!--
.pt10 {
    font:normal     10pt;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          9pt;
    font-family:        monospace;
}
-->
</style>
</head>
<body>
<center>
<?php echo $menu->out_title_border() ?>
        <div class='pt10' style='color:gray;'>現在の契約賃率：<?php echo number_format(RATE, 2) ?></div>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <tr>
                <td nowrap align='center' class='caption_font'>
                    <?php echo $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        <table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <th class='winbox' width=' 4%' nowrap>No</th>
                <th class='winbox' width=' 5%' nowrap>Level</th>
                <th class='winbox' width=' 8%' nowrap>部品番号</th>
                <th class='winbox' width='39%' nowrap>部品名</th>
                <th class='winbox' width=' 6%' nowrap>使用数</th>
                <th class='winbox' width=' 4%' nowrap>工程</th>
                <th class='winbox' width=' 6%' nowrap>工程名</th>
                <th class='winbox' width=' 7%' nowrap>工程単価</th>
                <th class='winbox' width=' 7%' nowrap>工程金額</th>
                <th class='winbox' width=' 6%' nowrap>内外作</th>
                <th class='winbox' width=' 8%' nowrap>親番号</th>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <?php echo $menu->out_retF2Script() ?>
</center>
</body>
<?php if ($msg_flg == 'alert') echo $menu->out_alert_java(); ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END    
?>