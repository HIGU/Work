<?php
//////////////////////////////////////////////////////////////////////////////
// プログラム管理メニュー プログラムの検索  表示(Ajax)          MVC View部  //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_search_ViewListAjax.php                  //
//////////////////////////////////////////////////////////////////////////////
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='progMaster_search_ViewListHeader.html?{$uniq}' name='header' align='center' width='100%' height='80' title='ヘッダー'>\n";
echo "    項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/progMaster_search_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='68%' title='ボディー'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/progMaster_search_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='80%' height='35' title='フッター'>\n";
// echo "    フッターを表示しています。\n";
// echo "</iframe>\n";
?>
