<?php
//////////////////////////////////////////////////////////////////////////////
// アイテムマスターの品名による前方検索・部分検索   表示(Ajax)  MVC View部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/10 Created   item_name_search_ViewList.php                       //
// 2006/04/13 height='38→'35'(NN7.1対策), frameborder='0' を追加           //
//////////////////////////////////////////////////////////////////////////////
// echo "<br>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='item_name_search_ViewHeader.html?item={$this->request->get('targetSortItem')}&{$uniq}' name='header' align='center' width='96%' height='35' title='項目'>\n";
echo "    表の項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/item_name_search_ViewList-{$_SESSION['User_ID']}.html?item={$this->request->get('targetSortItem')}&{$uniq}' name='list' align='center' width='96%' height='75%' title='アイテムマスター一覧'>\n";
echo "    アイテムマスターの検索結果を表示しています。\n";
echo "</iframe>\n";
?>
