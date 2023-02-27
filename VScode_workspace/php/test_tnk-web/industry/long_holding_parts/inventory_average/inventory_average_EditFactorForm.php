<?php
//////////////////////////////////////////////////////////////////////////////
// 資材在庫部品 全品目の月平均出庫数・保有月数等照会           MVC View 部  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/06/13 Created   inventory_average_EditFactorForm.php                //
// 2007/06/14 取消を追加しvisibility:hidden;で初期化。チップヘルプの改行設定//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>要因マスターの編集フォーム</title>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='inventory_average.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    overflow:           hidden;
    background-image:   none;
    background-color:   #d6d3ce;
}
-->
</style>
<script type='text/javascript'>
</script>
</head>
<body
    onLoad='
        parent.InventoryAverage.set_focus(document.EditFactorForm.targetFactorName, "select");
    '
>
<center>
<form name='EditFactorForm' action='' method='post' onSubmit='return parent.InventoryAverage.checkEditFactorForm(this);'>
<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>
    <tr><td> <!----------- ダミー(デザイン用) ------------>
<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>
    <tr>
        <th class='winbox' nowrap>要因項目</th>
        <td class='winbox' align='center'>
            <input type='text' name='targetFactorName' value='' size='8' maxlength='8'
                title='
表示上の制限で登録文字数は全角文字で５文字以内に制限しています。
                '
            >
        </td>
        <th class='winbox' nowrap>要因説明</th>
        <td class='winbox' align='left'>
            <input type='text' name='targetFactorExplanation' value='' size='58' maxlength='40'
                title='
登録文字数は全角文字で４０文字以内に制限しています。
                '
            >
        </td>
        <td class='winbox' align='center'>
            <input type='submit' name='editButton' value='登録' class='editButton'>
        </td>
        <td class='winbox' align='center''>
            <input type='button' name='cancelButton' value='取消' class='cancelButton' style='visibility:hidden;'
                onclick='parent.InventoryAverage.AjaxLoadTable("FactorMnt", "showAjax");'
            >
        </td>
        <input type='hidden' name='targetFactor' value=''>
    </tr>
</table>
    </td></tr>
</table> <!----------------- ダミーEnd ------------------>
</form>
</center>
</body>
</html>
<?php echo $menu->out_alert_java(false)?>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
