<?php
//////////////////////////////////////////////////////////////////////////////
// 適正在庫数の照会 直近三年間の出荷数÷３×２   表示(Ajax)     MVC View部  //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/06/17 Created   reasonable_stock_ViewList.php                       //
//////////////////////////////////////////////////////////////////////////////
// echo "<br>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='reasonable_stock_ViewHeader.html?item={$this->request->get('targetSortItem')}&{$uniq}' name='header' align='center' width='96%' height='35' title='項目'>\n";
echo "    表の項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/reasonable_stock_ViewList-{$_SESSION['User_ID']}.html?item={$this->request->get('targetSortItem')}&{$uniq}' name='list' align='center' width='96%' height='70%' title='長期滞留部品一覧'>\n";
echo "    長期滞留部品一覧を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/reasonable_stock_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='96%' height='35' title='フッター'>\n";
echo "    フッターを表示しています。\n";
echo "</iframe>\n";
?>
