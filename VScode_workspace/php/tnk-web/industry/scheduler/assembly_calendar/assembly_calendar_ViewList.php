<?php
//////////////////////////////////////////////////////////////////////////////
// 組立ラインのカレンダー メンテナンス   表示(Ajax)             MVC View部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/11 Created   assembly_calendar_ViewList.php                      //
//            height='38→'35'(NN7.1対策), frameborder='0' を追加           //
//////////////////////////////////////////////////////////////////////////////
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assembly_calendar_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='90%' height='35' title='項目'>\n";
echo "    項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assembly_calendar_ViewListBody-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='90%' height='72%' title='一覧'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assembly_calendar_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='90%' height='35' title='フッター'>\n";
echo "    フッターを表示しています。\n";
echo "</iframe>\n";
?>
