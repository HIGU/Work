<?php
//////////////////////////////////////////////////////////////////////////////
// 資材在庫部品 全品目の月平均出庫数・保有月数等照会           MVC View 部  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/06/12 Created   inventory_average_ViewEditFactor.php                //
// 2007/06/13 入力フォームをインラインへ変更 EditFactorForm.php でｺﾝﾄﾛｰﾙ    //
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
?>
<br>

<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='inventory_average_EditFactorForm.php?<?php echo $uniq ?>' name='form' align='center' width='90%' height='35' title='入力'>
    入力フォームを表示しています。
</iframe>
<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='inventory_average_ViewFactorHeader.html?<?php echo $uniq ?>' name='header' align='center' width='90%' height='35' title='項目'>
    項目を表示しています。
</iframe>
<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='factor/inventory_average_ViewFactorBody-<?php echo $_SESSION['User_ID'] ?>.html?<?php echo $uniq ?>#Mark' name='list' align='center' width='90%' height='40%' title='一覧'>
    一覧を表示しています。
</iframe>

<!--
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='factor/inventory_average_ViewFactorFooter.html?<?php echo $uniq ?>' name='footer' align='center' width='90%' height='35' title='フッター'>
        フッターを表示しています。
    </iframe>
-->
