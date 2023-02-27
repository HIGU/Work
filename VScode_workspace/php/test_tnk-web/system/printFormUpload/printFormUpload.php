<?php
//////////////////////////////////////////////////////////////////////////////
// OpenOffice Draw で出力したSVGファイルをUPLOADしテンプレートを作成        //
// テンプレートエンジンはsimplate, クライアント印刷はPXDoc を使用           //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/06 Created  printFormUpload.php                                  //
//////////////////////////////////////////////////////////////////////////////
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_cache_limiter('public');            // PXDocを使用する場合のおまじない(出力ファイルをキャッシュさせる)
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
// access_log();                               // Script Name は自動取得
define('START_TIME', microtime(true));

//////////// リクエストのインスタンスを作成
$request = new Request();
///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('印刷フォーム(SVG)をアップロードしてテンプレートにコンバート処理');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('svgUpload', '/pxd/svgUpload.php');
$menu->set_action('verUP',     '/pxd/downloadPXDoc.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

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
<?php echo $menu->out_jsBaseClass() ?>

<!-- JavaScriptのファイル指定をbodyの最後にする。 HTMLタグのコメントは入れ子に出来ない事に注意  -->
<script type='text/javascript' src='/pxd/checkPXD.js?<?php echo $uniq ?>'></script>

<!-- スタイルシートのファイル指定をコメント HTMLタグのコメントは入れ子に出来ない事に注意  -->
<!-- <link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'> -->

<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>

<style type='text/css'><!-- --></style>
</head>

<script type='text/javascript'>
function checkTemplateFile(obj)
{
    if (obj.svgFile.value) {
        return true;
    } else {
        alert('SVG(スケーラーブル・ベクター・グラフィックス)ファイルが指定されていません！');
        return false;
    }
}
</script>
<body style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        <br><br>
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form enctype='multipart/form-data' method='post' name='svgUploadForm' action='<?php echo $menu->out_action('svgUpload')?>' onSubmit='return checkTemplateFile(this)'>
            <tr>
                <td class='winbox' nowrap colspan='1' align='left'>
                    <input type='hidden' name='MAX_FILE_SIZE' value='1000000' />
                    Scalable Vector Graphics (SVG) → テンプレートファイル<br>
                    <input type='file' name='svgFile' size='60' maxlength='256' />
                    <input type='submit' name='svgUPload' style='width:110px; font-size:0.9em; font-weight:bold;' value='コンバート実行' />
                </td>
            </tr>
            </form>
            <tr>
                <td class='winbox' nowrap colspan='1' align='center'>
                    <input type='button' name='verUP' style='width:210px;' value='印刷プログラムのバージョンアップ' onClick='window.open("<?php echo $menu->out_action('verUP')?>", "down_win", "width=1,height=1");'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
