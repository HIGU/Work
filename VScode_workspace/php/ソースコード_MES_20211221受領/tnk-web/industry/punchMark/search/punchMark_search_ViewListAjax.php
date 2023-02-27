<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理ステム 検索メニュー              表示(Ajax)          MVC View部  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/22 Created   punchMark_search_ViewListAjax.php                   //
//////////////////////////////////////////////////////////////////////////////
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='punchMark_search_ViewListHeader.html?{$uniq}' name='header' align='center' width='100%' height='62' title='ヘッダー'>\n";
echo "    項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/punchMark_search_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='60%' title='ボディー'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/punchMark_search_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='80%' height='35' title='フッター'>\n";
// echo "    フッターを表示しています。\n";
// echo "</iframe>\n";
?>
