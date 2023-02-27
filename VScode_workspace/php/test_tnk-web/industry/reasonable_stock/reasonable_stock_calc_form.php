<?php
//////////////////////////////////////////////////////////////////////////////
// 適正在庫数の計算フォーム                                                 //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/06/17 Created   reasonable_stock_calc_form.php                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_INDUST, 16);               // site_index=10(損益メニュー) site_id=7(損益作成メニュー)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('適正在庫数の計算');
//////////// 表題の設定
$menu->set_caption('基準年月を指定して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('計算実行',   INDUST . 'reasonable_stock_calc.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('r_stock');

//////////// 月次年月の取得
if (isset($_POST['st_ym'])) {
    $st_ym = $_POST['st_ym'];
} else {
    $st_ym = '';
}

//////////// データ取込み開始
if (isset($_POST['calcRstock'])) {
    // ここに header('Location: http:' . WEB_HOST . $menu->out_action('取込実行'));
    // 又は require ('invent_comp_get.php');
    // を入れて画面最後に取込み完了のメッセージを出すか システムメッセージを出すかどちらかにする。
    if ( (require ('reasonable_stock_calc.php')) == TRUE) {   // 括弧の優先順位に注意
        //////////// システムメッセージ変数へ完了通知を書込む
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>計算完了しました。</font>";  // menu_site.php で使用する
        unset($_POST['calcRstock']);
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='red'>計算に失敗しました。</font>";  // menu_site.php で使用する
        unset($_POST['calcRstock']);
    }
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

<!--    ファイル指定の場合
<script type='text/javascript' language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script type='text/javascript' language='JavaScript'>
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
function rs_calc_click(obj) {
    if (confirm("適正在庫数計算を実行します。\n既にデータがある場合は上書きされます。\n元には戻せません。")){
        return confirm("本当に実行していいですね？");
    }
    return false;
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type='text/css'>
<!--
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt10 {
    font-size:   10pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
th {
    background-color:yellow;
    color:blue;
    font:bold 11pt;
    font-family: monospace;
}
body {
    overflow-y:             hidden;
    background-image:       url(/img/t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
}
-->
</style>
</head>
<body>
    <center>
<?php echo $menu->out_title_border() ?>
        <br>
        <br>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='center' class='pt11b'>
                        <?php echo "{$menu->out_caption()}\n" ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt10'>
                        <select name='st_ym'>
                        <?php
                        $ym = date("Ym");
                        while(1) {
                            if (substr($ym,4,2)>03) {
                                $ym = substr($ym, 0, 4) . "03";
                            } else {
                                $ym = substr($ym, 0, 4) - 1 . "03";
                            }
                            printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                            if ($ym <= 200803)
                                break;
                        }
                        ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt10'>
                        <br>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='calcRstock' value='計算実行' onClick='return rs_calc_click(this)'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
