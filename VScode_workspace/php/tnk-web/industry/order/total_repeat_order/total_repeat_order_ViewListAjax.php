<?php
//////////////////////////////////////////////////////////////////////////////
// リピート部品発注の集計 照会                  表示(Ajax)      MVC View部  //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/19 Created   total_repeat_order_ViewListAjax.php                 //
//            height='38→'35'(NN7.1対策), frameborder='0' を追加           //
// 2007/12/20 width='80%' → '90%' へ変更                                   //
// 2008/07/30 width='90%' → '100%' へ変更                             大谷 //
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/total_repeat_order_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='100%' height='35' title='項目'>\n";
echo "    項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/total_repeat_order_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='70%' title='一覧'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/total_repeat_order_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='100%' height='35' title='フッター'>\n";
echo "    フッターを表示しています。\n";
echo "</iframe>\n";
?>
