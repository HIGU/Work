<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理ステム 貸出台帳 更新 履歴メニュー         表示(Ajax) MVC View部  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/04 Created   punchMark_lendEditHistory_ViewListAjax.php          //
//////////////////////////////////////////////////////////////////////////////
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='punchMark_lendEditHistory_ViewListHeader.html?{$uniq}' name='header' align='center' width='100%' height='35' title='ヘッダー'>\n";
echo "    項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/punchMark_lendEditHistory_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='77%' title='ボディー'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/punchMark_lendEditHistory_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='80%' height='35' title='フッター'>\n";
// echo "    フッターを表示しています。\n";
// echo "</iframe>\n";
?>
