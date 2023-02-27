<?php
//////////////////////////////////////////////////////////////////////////////
// 就業週報の集計 確認済一覧      表示(Ajax)                    MVC View部  //
// Copyright (C) 2008 - 2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2008/09/22 Created   working_hours_report_ViewConfirmListAjax.php        //
// 2017/06/02 部課長説明 本格稼動                                           //
// 2017/06/29 エラー箇所等を訂正                                            //
//////////////////////////////////////////////////////////////////////////////
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='98%' height='60' title='項目'>\n";
// echo "    項目を表示しています。\n";
// echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_ViewConfirmList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='99%' height='78%' title='一覧'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='98%' height='35' title='フッター'>\n";
// echo "    フッターを表示しています。\n";
// echo "</iframe>\n";
?>
