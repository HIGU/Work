<?php
//////////////////////////////////////////////////////////////////////////////
// 資材出庫時間の集計･分析 結果 照会        表示(Ajax)          MVC View部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/30 Created   parts_pickup_analyze_ViewListAjax.php               //
//            height='38→'35'(NN7.1対策), frameborder='0' を追加           //
//////////////////////////////////////////////////////////////////////////////
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_pickup_analyze_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='98%' height='60' title='項目'>\n";
// echo "    項目を表示しています。\n";
// echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_pickup_analyze_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='80%' height='50%' title='一覧'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/parts_pickup_analyze_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='98%' height='35' title='フッター'>\n";
// echo "    フッターを表示しています。\n";
// echo "</iframe>\n";
?>
