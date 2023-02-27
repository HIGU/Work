<?php
//////////////////////////////////////////////////////////////////////////////
// 損益予測の集計・分析 結果 照会(都度照会)    表示(Ajax)       MVC View部  //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/13 Created   profit_loss_estimate_ViewListAjax.php               //
// 2011/07/19 都度照会版としてコメント追加                                  //
// 2011/07/20 フレームの大きさを調整                                        //
//////////////////////////////////////////////////////////////////////////////
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/profit_loss_estimate_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='98%' height='60' title='項目'>\n";
// echo "    項目を表示しています。\n";
// echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/profit_loss_estimate_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='70%' title='一覧'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/profit_loss_estimate_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='98%' height='35' title='フッター'>\n";
// echo "    フッターを表示しています。\n";
// echo "</iframe>\n";
?>
