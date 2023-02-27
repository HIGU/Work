<?php
//////////////////////////////////////////////////////////////////////////////
// 長期滞留部品の照会 最終入庫日指定で在庫あり   表示(Ajax)     MVC View部  //
// Copyright (C) 2006-2011 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/03 Created   long_holding_parts_ViewList.php                     //
// 2006/04/13 height='38→'35'(NN7.1対策), frameborder='0' を追加           //
// 2007/06/05 合計件数・合計金額をボディ部からフッター部へ移動              //
// 2011/07/28 親機種追加に伴い、表示幅を変更                           大谷 //
//////////////////////////////////////////////////////////////////////////////
// echo "<br>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='long_holding_parts_ViewHeader.html?item={$this->request->get('targetSortItem')}&{$uniq}' name='header' align='center' width='100%' height='35' title='項目'>\n";
echo "    表の項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/long_holding_parts_ViewList-{$_SESSION['User_ID']}.html?item={$this->request->get('targetSortItem')}&{$uniq}' name='list' align='center' width='100%' height='70%' title='長期滞留部品一覧'>\n";
echo "    長期滞留部品一覧を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/long_holding_parts_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='100%' height='35' title='フッター'>\n";
echo "    フッターを表示しています。\n";
echo "</iframe>\n";
?>
