<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理 運転日報の材料マスター保守 照会画面     Client interface 部 //
//           MaterialEntry.phpから呼出  登録内容照会    MVC View の List 部 //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   MaterialView.php                                    //
// 2006/06/09 access_log() 対応  ob_start()とsession_start()は呼出元既使用  //
//            style='width:200px; height:25px;' を追加                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント

require_once ('../../../function.php');     // access_log()等で使用
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
// access_log();                               // Script Name は自動取得

// コードから名称へ変換
if ($Materials['Type'] == 'B')  $TYPE = 'バー材';
else                            $TYPE = '切断材';

SetHttpHeader();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<LINK rel="stylesheet" href="../com/css.css" type="text/css">
<Script Language='JavaScript'>
function init() {
}
function doSubmit() {
    document.MainForm.submit();
}
<?php if ($_REQUEST['RetUrl'] != '') { ?>
function doBack() {
    location.href = '<?=$_REQUEST['RetUrl']?>';
}
<?php } ?>
</Script>
</head>
<body>
    <center>
        <table border="1">
            <!-- 材料コード -->
            <tr>
                <td class="HED" style="width:100px;">
                    材料コード
                </td>
                <td style='width:200px; height:25px;'>
                    <?=outHtml($Materials['Code'])?>
                </td>
            </tr>
            <!-- 材料名称 -->
            <tr>
                <td class="HED" style="width:100px;">
                    材料名称
                </td>
                <td style='width:200px; height:25px;'>
                    <?=outHtml($Materials['Name'])?>
                </td>
            </tr>
            <!-- バー材 or 切断材 -->
            <tr>
                <td class="HED" style="width:100px;">
                    タイプ
                </td>
                <td style='width:200px; height:25px;'>
                    <?=outHtml($TYPE)?>
                </td>
            </tr>
            <!-- 材質 -->
            <tr>
                <td class="HED" style="width:100px;">
                    部品材質
                </td>
                <td style='width:200px; height:25px;'>
                    <?=outHtml($Materials['Style'])?>
                </td>
            </tr>
            <!-- 対重量 -->
            <tr>
                <td class="HED" style="width:100px;">
                    単位重量
                </td>
                <td align='right' style='width:200px; height:25px;'>
                    <?=outHtml(sprintf ('%.04f',$Materials['Weight']))?> Kg/m
                </td>
            </tr>
            <!-- 標準長さ -->
            <tr>
                <td class="HED" style="width:100px;">
                    標準長さ
                </td>
                <td align='right' style='width:200px; height:25px;'>
                    <?=outHtml(sprintf ('%.04f',$Materials['Length']))?> ｍ
                </td>
            </tr>
        </table>
        <br>
        <?php if ($_REQUEST['RetUrl'] != '') { ?>
        <input type="button" value="戻　る" style="width:80;" onClick="doBack()">
        <?php } ?>
        <?php if ($Message != '') { ?>
            <br><br><br><font color='#ff0000'><b><?=$Message?></b></font><br>
        <?php } ?>
        
    </center>
</form>
</body>
</html>
