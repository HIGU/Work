<?php 
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理 運転日報の材料マスター保守 登録画面     Client interface 部 //
//                PartsEntry.phpから呼出  編集・新規    MVC View の List 部 //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   MaterialEntryPage.php                               //
// 2006/06/09 access_log() 対応  ob_start()とsession_start()は呼出元既使用  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント

require_once ('../../../function.php');     // access_log()等で使用
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
// access_log();                               // Script Name は自動取得

// チェックの設定
if ($Materials['Type'] != 'C') {
    $BAR_CHECKED = ' checked ';
    $CUT_CHECKED = '';
} else {
    $BAR_CHECKED = '';
    $CUT_CHECKED = ' checked ';
}

// 共通ヘッダの出力
SetHttpHeader();
?>
<!DOCTYPE HTML>
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
<?php if (isset($_REQUEST['RetUrl'])) { ?>
function doBack() {
    location.href = '<?=$_REQUEST['RetUrl']?>';
}
<?php } ?>
</Script>
</head>
<body onLoad='init()'>
<form name='MainForm' action='MaterialsEntry.php' method='post'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='EDIT_MODE' value='<?=$EDIT_MODE?>'>
<input type='hidden' name='RetUrl' value='<?=outHtml(@$_REQUEST['RetUrl'])?>'>
    <center>
        <table border='1'>
            <!-- 材料コード -->
            <tr>
                <td class='HED' style='width:100px;'>
                    材料コード
                </td>
                <td>
                <?php if ($EDIT_MODE == 'INSERT') { ?>
                    <input type='text' name='Code' size='7' maxlength='7' class='CODE' value='<?=outHtml($Materials['Code'])?>'>
                <?php } else { ?>
                    <?=outHtml($Materials['Code'])?>
                    <input type='hidden' name='Code' value='<?=outHtml($Materials['Code'])?>'>
                <?php } ?>
                </td>
            </tr>
            <!-- 材料名称 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    材料名称
                </td>
                <td>
                    <input type='text' name='Name' size='30' maxlength='30' class='TEXT' value='<?=outHtml($Materials['Name'])?>'>
                </td>
            </tr>
            <!-- バー材 or 切断材 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    タイプ
                </td>
                <td>
                    <input type='radio' name='Type' value='B' ID='TypeB'<?=$BAR_CHECKED?>><Label for='TypeB'>バー材</Label>
                    <input type='radio' name='Type' value='C' ID='TypeC'<?=$CUT_CHECKED?>><Label for='TypeC'>切断材</Label>
                </td>
            </tr>
            <!-- 材質 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    部品材質
                </td>
                <td>
                    <input type='text' name='Style' size='30' maxlength='30' class='TEXT' value='<?=outHtml($Materials['Style'])?>'>
                </td>
            </tr>
            <!-- 対重量 -->
            <tr>
                <td class='HED' style='width:100px;'>
                    単位重量
                </td>
                <td>
                    <input type='text' name='Weight' size='7' maxlength='7' class='NUM' value='<?=outHtml(sprintf ('%.04f',$Materials['Weight']))?>'> Kg/m(切断材は１個当りの重さ)
                </td>
            </tr>
            <!-- 標準長さ -->
            <tr>
                <td class='HED' style='width:100px;'>
                    標準長さ
                </td>
                <td>
                    <input type='text' name='Length' size='7' maxlength='7' class='NUM' value='<?=outHtml(sprintf ('%.04f',$Materials['Length']))?>'> ｍ
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
