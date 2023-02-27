<?php
//////////////////////////////////////////////////////////////////////////////
// 資材在庫部品 全品目の月平均出庫数・保有月数等照会 表示(Ajax) MVC View 部 //
// Copyright (C) 2007-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/06/08 Created   inventory_average_ViewList.php                      //
// 2007/06/09 ControlForm → ControlForm2 へ変更(ViewCondFormと重複するとNG)//
// 2007/06/11 ジャンプ用に#Mark を追加                                      //
// 2016/06/24 CSV出力を追加                                            大谷 //
//////////////////////////////////////////////////////////////////////////////
// echo "<br>\n";
echo "<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
echo "   <tr>\n";
echo "   <td width='40%' align='center'>&nbsp;</td>\n";
// CSV出力の追加
$csv_where = str_replace('\'','/',$csv_where);
echo "   <td width='20%' align='right'><a href='inventory_average_csv.php?csvsearch={$csv_where}'>CSV出力</a></td>\n";
//
echo "   <form name='ControlForm2' action='{$menu->out_self()}?showMenu=Both' method='post'>\n";
echo "   <td width='40%' align='center'>{$pageControl}</td>\n";
echo "   </form>\n";
echo "   </tr>\n";
echo "</table>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/inventory_average_ViewListHeader-{$_SESSION['User_ID']}.html?&{$uniq}' name='header' align='center' width='100%' height='35' title='項目'>\n";
echo "    表の項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/inventory_average_ViewListBody-{$_SESSION['User_ID']}.html?item={$request->get('targetSortItem')}&{$uniq}#Mark' name='list' align='center' width='100%' height='70%' title='資材在庫部品 保有月等の一覧'>\n";
echo "    資材在庫部品 保有月等の一覧を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/inventory_average_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='100%' height='35' title='フッター'>\n";
echo "    フッターを表示しています。\n";
echo "</iframe>\n";
?>
