<?php
//////////////////////////////////////////////////////////////////////////////
// DATA SUM 用 バーコードカード 作成フォーム                                //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2003/02/18 Created   datasum_barcode.php                                 //
// 2004/02/19 check digit に対応していないバーコードが使われているため 0へ  //
// 2004/06/15 応援者 777001～777099 → 777999 へ変更 AS/400は77XXXXが応援者 //
// 2005/02/10 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/10/19 ショートカットを標準タグへ変更 E_ALL → E_ALL | E_STRICT へ   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047'→現在はコードが違う debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');       // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');       // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');     // TNK 全共通 menu class
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30,  5);                    // site_index=30(生産メニュー) site_id=5(データサムバーコード)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('データサム バーコードカード作成');
//////////// 表題の設定
$menu->set_caption('バーコード カード イメージ フォーム');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('barCode');

//////////// バーコードの作成フラグ
$exec_flg = false;

//////////// template 用 検索値
if (isset($_POST['check_uid'])) {
    $uid = $_POST['check_uid'];
    if ($uid == '') {
        $_SESSION['s_sysmsg'] = '社員番号が未入力！';
        $uid  = '';
        $name = '未入力';
    } elseif (!is_numeric($uid)) {    // 数値かどうかのチェック
        $_SESSION['s_sysmsg'] = "社員番号が数字ではありません！：{$uid}";
        $uid  = '';
        $name = '未入力';
    } else {
        if ( ($uid < 777001) || ($uid > 777999) ) {
            //////////// SQL 文の実行
            $search = sprintf("where uid='%06d'", $uid);
            $query  = sprintf('SELECT trim(name) as name  FROM user_detailes %s', $search);
            if ( getUniResult($query, $name) <= 0) {         // 社員の名前の取得
                $_SESSION['s_sysmsg'] .= "社員番号が未登録！：{$uid}";  // .= メッセージを追加する
                $uid  = '';
                $name = '未入力';
            } else {
                $uid = sprintf('%06d', $uid);
                $_SESSION['dsum_uid']  = $uid;  // セッションに保管
                $_SESSION['dsum_name'] = $name;
                $exec_flg = true;       // 印刷許可
            }
        } else {
            $name = '応援者';
            $uid  = sprintf('%06d', $uid);
            $_SESSION['dsum_uid'] = $uid;       // セッションに保管
            $_SESSION['dsum_name'] = $name;
            $exec_flg = true;       // 印刷許可
        }
    }
} else {
    $uid  = '';
    $name = '未入力';
}

////////////// HTML Header を出力してブラウザーのキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* 入力文字がアルファベットかどうかチェック isDigit()の逆 */
function isABC(str) {
    // var str = str.toUpperCase();    // 必要に応じて大文字に変換
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // スペースはOK
            return false;
        }
    }
    return true;
}

/* 入力文字が数字かどうかチェック 小数点対応 */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1個目かチェック
                cnt_dot++;
            } else {
                return false;       // 2個目は false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.uid_form.check_uid.focus();      // 初期入力フォームがある場合はコメントを外す
    document.uid_form.check_uid.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
 -->

<style type="text/css">
<!--
.pt10 {
    font-size:   10pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
    font-family: monospace;
}
body {
    background-color:   #ffffc6;
    overflow-y:         hidden;
}
th {
    background-color: yellow;
    color:            blue;
    font-size:        10pt;
    font-weight:      bold;
    font-family:      monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <br>
        
        <!----------------- ここは caption のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center' class='caption_font' style='color:blue;'>
                    <?php echo $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' nowrap align='left' bgcolor='white' width='98' height='90'> <!-- width=98 and height='90'は入力時とのバランス取る(実際は75) -->
                    <div class='pt10b'>１応援開始</div>
                </td>
                <td class='winbox' nowrap align='center' bgcolor='white'>
                    <div class='pt10'>
                    <img src='/barcode/barcode39_create_png.php?data=916&check=0&mode=white'
                        alt='応援開始のバーコード 916'> <!-- width='110' height='50' -->
                    <br>*　9 　1 　6　*
                    </div>
                </td>
            </tr>
            <tr>
                <? if ($exec_flg) { ?>
                    <td class='winbox' nowrap align='left' bgcolor='white' height='90'>
                        <div class='pt10b'>２<?php echo $name ?></div>
                    </td>
                    <td class='winbox' nowrap align='center' bgcolor='white'>
                        <div class='pt10'>
                        <img src='/barcode/barcode39_create_png.php?data=<?php echo $uid ?>&check=0&mode=white'
                            alt='<?php echo $name ?>のバーコード <?php echo $uid ?>' width='220' height='50'>
                        <br>* 　<?php echo substr($uid,0,1),' 　',substr($uid,1,1),' 　',substr($uid,2,1),' 　',substr($uid,3,1),' 　',substr($uid,4,1),' 　',substr($uid,5,1) ?> 　*
                        </div>
                    </td>
                <? } else { ?>
                    <td class='winbox' nowrap align='left' bgcolor='white' height='90'>
                        <div class='pt10'>２<font color='gray'><?php echo $name ?></font></div>
                    </td>
                    <td class='winbox' nowrap align='center' bgcolor='white'>
                        <div class='pt10'><font color='gray'>未入力</font></div>
                    </td>
                <? } ?>
            </tr>
            <tr>
                <td class='winbox' nowrap align='left' bgcolor='white' height='90'>
                    <div class='pt10b'>３その他計画</div>
                </td>
                <td class='winbox' nowrap align='center' bgcolor='white'>
                    <div class='pt10'>
                    <img src='/barcode/barcode39_create_png.php?data=C9999999&check=0&mode=white'
                        alt='その他計画 C9999999' width='250' height='50'>
                    <br>*　C 　9 　9 　9 　9 　9 　9 　9　*
                    </div>
                </td>
            </tr>
            <tr>
                <td class='winbox' nowrap align='left' bgcolor='white' height='90'>
                    <div class='pt10b'>４本作業<div>
                </td>
                <td class='winbox' nowrap align='center' bgcolor='white'>
                    <div class='pt10'>
                    <img src='/barcode/barcode39_create_png.php?data=910&check=0&mode=white'
                        alt='本作業のバーコード 910' width='110' height='50'>
                    <br>*　9 　1 　0　*
                    </div>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <br>
        
        <table border='0'>
            <tr>
                <td nowrap align='center' class='pt10b'>
                    <form name='uid_form' method='post' action='<?php echo $menu->out_self() ?>'>
                        社員番号を入力
                        <input class='pt11b' type='text' name='check_uid' size='7' maxlength='6' value='<? $uid ?>'>
                        <input class='pt10b' type='submit' name='exec_chk' value='確認'>
                    </form>
                </td>
                <? if ($exec_flg) { ?>
                <td align='center'>
                    <form name='print_form' method='get' action='datasum_barcode_mbfpdf.php'>
                        <input class='pt10b' type='submit' name='exec_print' value='PDF印刷'>
                    </form>
                </td>
                <? } else { ?>
                <td align='center'>
                    　
                </td>
                <? } ?>
            </tr>
            <tr>
                <td colspan='2' align='center' class='pt10'>
                    応援者は777001～777999まで
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
