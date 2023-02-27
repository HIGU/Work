<?php
//////////////////////////////////////////////////////////////////////////////
// 部品 在庫 経歴 照会 (ＭＶＣ版)           表示(Ajax)          MVC View部  //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/20 Created parts_stock_history_ViewListAjax.php(parts_stock_view)//
// 2007/03/16 オリジナルはparts_stock_view.php でparts_stock_plan_ViewList  //
//            Ajax.phpを雛形にして完全なＭＶＣモデルでコーディングした。    //
//            変更経歴は backup/parts_stock_view.php を参照すること。       //
//            height='38→'35'(NN7.1対策), frameborder='0' を追加           //
//////////////////////////////////////////////////////////////////////////////
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_stock_history_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='98%' height='60' title='項目'>\n";
echo "    項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_stock_history_ViewList-{$_SESSION['User_ID']}.html?{$uniq}#last' name='list' align='center' width='98%' height='73%' title='一覧'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_stock_history_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='98%' height='35' title='フッター'>\n";
echo "    フッターを表示しています。\n";
echo "</iframe>\n";
?>
