<?php
//////////////////////////////////////////////////////////////////////////////
// 部品 在庫・有効利用数(予定在庫数)マイナスリスト照会 (Ajax)   MVC View 部 //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/02 Created   parts_stock_avail_minus_ViewList.php                //
//////////////////////////////////////////////////////////////////////////////
// echo "<br>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_stock_avail_minus_ViewListHeader-{$_SESSION['User_ID']}.html?&{$uniq}' name='header' align='center' width='100%' height='35' title='項目'>\n";
echo "    表の項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_stock_avail_minus_ViewListBody-{$_SESSION['User_ID']}.html?item={$request->get('targetSortItem')}&{$uniq}#Mark' name='list' align='center' width='100%' height='70%' title='資材在庫部品 保有月等の一覧'>\n";
echo "    在庫・有効利用数(予定在庫数)マイナスリストを表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_stock_avail_minus_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='100%' height='35' title='フッター'>\n";
echo "    フッターを表示しています。\n";
echo "</iframe>\n";
?>
