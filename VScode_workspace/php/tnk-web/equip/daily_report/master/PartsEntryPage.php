<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理 運転日報の部品マスター保守 登録画面     Client interface 部 //
//                PartsEntry.phpから呼出  編集・新規    MVC View の List 部 //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   PartsEntryPage.php                                  //
// 2006/06/09 access_log() 対応  ob_start()とsession_start()は呼出元既使用  //
// 2006/06/12 機械番号・機械名を追加 doMacMasterCheck()を追加               //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント

require_once ('../../../function.php');     // access_log()等で使用
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
// access_log();                               // Script Name は自動取得

// 共通ヘッダの出力
SetHttpHeader();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<SCRIPT language='JavaScript' SRC='<?=SEARCH_JS?>'></SCRIPT>
<Script Language='JavaScript'>
function init() {
<?php if ($Message != '') { ?>
    alert('<?=$Message?>');
<?php } ?>
}
function doSubmit() {
    if (confirm('登録します。よろしいですか？')) {
        document.MainForm.ProcCode.value = 'WRITE';
        document.MainForm.submit();
    }
}
function doDelete() {
    if (confirm('削除します。よろしいですか？')) {
        document.MainForm.ProcCode.value = 'DELETE';
        document.MainForm.submit();
    }
}
function doMasterCheck() {
    document.MainForm.ProcCode.value = 'CHECK_MASTER';
    document.MainForm.submit();
}
function doMacMasterCheck() {
    document.MainForm.ProcCode.value = 'CHECK_MAC_MASTER';
    document.MainForm.submit();
}
<?php if (isset($_REQUEST['RetUrl'])) { ?>
function doBack() {
    location.href = '<?=$_REQUEST['RetUrl']?>';
}
<?php } ?>
</Script>
</head>
<body onLoad='init()'>
<form name='MainForm' action='PartsEntry.php' method='post'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='EDIT_MODE' value='<?=$EDIT_MODE?>'>
<input type='hidden' name='CheckMaster' value='<?=$CheckMaster?>'>
<input type='hidden' name='RetUrl' value='<?=outHtml(@$_REQUEST['RetUrl'])?>'>
    <center>
        <table border='1'>
            <!-- 機械番号 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    機械番号
                </td>
                <td align='center'>
                    <?php if ($EDIT_MODE == 'INSERT') { ?>
                    <select name='MacNo' class='pt14b' onChange='doMacMasterCheck()'>
                        <?php echo getMacNoSelectData($Parts['MacNo']) ?>
                    </select>
                    <?php } else { ?>
                        <?=outHtml($Parts['MacNo'])?>
                        <input type='hidden' name='MacNo' value='<?=outHtml($Parts['MacNo'])?>'>
                    <?php } ?>
                </td>
            </tr>
            <!-- 機械名 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    機械名
                </td>
                <td>
                    <?=outHtml($Parts['MacName'])?>
                </td>
            </tr>
            <!-- 部品番号 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    部品番号
                </td>
                <td align='center'>
                    <?php if ($EDIT_MODE == 'INSERT') { ?>
                    <input type='button' value='選択' onClick='SearchItem(Code,Name,Zai)'>
                    <input type='text' name='Code' size='9' class='CODE' value='<?=outHtml($Parts['Code'])?>'>
                    <input type='button' value='▼' onClick='doMasterCheck()'>
                    <?php } else { ?>
                        <?=outHtml($Parts['Code'])?>
                        <input type='hidden' name='Code' value='<?=outHtml($Parts['Code'])?>'>
                    <?php } ?>
                </td>
            </tr>
            <!-- 部品名称 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    部品名称
                </td>
                <td>
                    <input type='text' name='Name' size='30' class='READONLY' value='<?=outHtml($Parts['Name'])?>' readonly>
                </td>
            </tr>
            <!-- 材質 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    部品材質
                </td>
                <td align='center'>
                    <input type='text' name='Zai' size='30' class='READONLY' value='<?=outHtml($Parts['Zai'])?>' readonly>
                </td>
            </tr>
            <!-- 寸法 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    寸法
                </td>
                <td>
                    <input type='text' name='Size' size='8' class='NUM' value='<?=outHtml($Parts['Size'])?>'> mm
                </td>
            </tr>
            <!-- 使用する材用 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    使用材料
                </td>
                <td>
                    <?php if ($CheckMaster == false) { ?>
                        <?php // 旧タイプはfalseの場合のみ選択ボタンを表示させていた ?>
                    <?php } ?>
                    <input type='button' value='選択' onClick='SearchMaterials(UseItem)'>
                    <input type='text' name='UseItem' size='7' CLASS='READONLY' value='<?=outHtml($Parts['UseItem'])?>' readonly>
                </td>
            </tr>
            <!-- 部品名称 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    破材サイズ
                </td>
                <td>
                    <input type='text' name='Abandonment' size='9' class='NUM'value='<?=outHtml($Parts['Abandonment'])?>'> mm
                </td>
            </tr>
        </table>
        <br>
        <input type='button' value='登　録' style='width:80;' onClick='doSubmit()'>
        <?php if ($EDIT_MODE == 'UPDATE') { ?>
        <input type='button' value='削　除' style='width:80;' onClick='doDelete()'>
        <?php } ?>
        <?php if (@$_REQUEST['RetUrl'] != '') { ?>
        <input type="button" value="戻　る" style="width:80;" onClick="doBack()">
        <?php } ?>
    </center>
</form>
</body>
</html>
