<?php 
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理システムの材料マスター保守               Client interface 部 //
//                                                  MVC View の Header 部   //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   MaterialsSearch.php                                 //
// 2006/04/12 MenuHeader クラス対応                                         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../../../function.php');     // access_log()等で使用
require_once ('../com/define.php');
require_once ('../com/function.php');
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('部品マスターの保守');
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性に_parentが必須

// 管理者モード
$AdminUser = AdminUser( FNC_MASTER );

// 共通ヘッダの出力
SetHttpHeader();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeaderOnly.php'); ?>
<SCRIPT language='JavaScript' SRC='<?=SEARCH_JS?>'></SCRIPT>
<Script Language='JavaScript'>
<?php if ($AdminUser) { ?>
function NewEdit() {
    document.MainForm.ProcCode.value = 'EDIT';
    document.MainForm.action = 'PartsEntry.php';
    document.MainForm.submit();
}
<?php } ?>
<?php if (@$_REQUEST['RetUrl'] != '') { ?>
function doBack() {
    document.MainForm.action = '<?=@$_REQUEST['RetUrl']?>';
    document.MainForm.target = '_parent';
    document.MainForm.submit();
}
<?php } ?>
function ViewList() {
    document.MainForm.FromCode.value = document.MainForm.FromCode.value.toUpperCase();
    document.MainForm.ToCode.value = document.MainForm.ToCode.value.toUpperCase();
    document.MainForm.ProcCode.value = 'VIEW';
    document.MainForm.action = 'PartsList.php';
    document.MainForm.submit();
}
</Script>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<LINK rel='stylesheet' href='<?=CONTEXT_PATH?>com/cssConversion.css' type='text/css'>
</head>
<body style='overflow-y:hidden;'>
<center>
<?php echo $menu->out_title_border() ?>

<form name='MainForm' method='post' target='ListFream'>
    <input type='hidden' name='ProcCode' value=''>
    <table class='Conversion' border='1'>
        <tr class='Conversion'>
            <td style='width:100;' class='HED Conversion'>部品番号</td>
            <td style='width:400;' class='Conversion'>
                <input type='button' value='選択' onClick='SearchParts(FromCode,FromName)'>
                <input type='text' name='FromCode' value='' size='9'class='CODE'>
                <input type='text' name='FromName' value='' size='30'class='READONLY' readonly> 〜<br>
                <input type='button' value='選択' onClick='SearchParts(ToCode,ToName)'>
                <input type='text' name='ToCode' value='' size='9'class='CODE'>
                <input type='text' name='ToName' value='' size='30'class='READONLY' readonly> 
            </td>
            <td style='width:100;' class='HED Conversion'>表示行</td>
            <td style='width:100;' align='center' class='Conversion'>
                <select name='ListNum'><?=SelectPageListNumOptions()?></select>
            </td>
        </tr>
    </table>
    <br>
    <input type='button' value='一覧表示' style='width:80;' onClick='ViewList()'>
    <?php if ($AdminUser) { ?>
    <input type='button' value='新規登録' style='width:80;' onClick='NewEdit()'>
    <?php } ?>
    <?php if (@$_REQUEST['RetUrl'] != '') { ?>
    <input type='button' value='戻　る' style='width:80;' onClick='doBack()'>
    <?php } ?>
</form>
</center>
</body>
</html>
