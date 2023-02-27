<?php
//////////////////////////////////////////////////////////////////////////////
// 会社の基本カレンダー メンテナンス カレンダー(Ajax)用のiFrame MVC View部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/01 Created   companyCalendar_ViewCalendar.php                    //
//            height='38→'35'(NN7.1対策), frameborder='0' を追加           //
//            NN7.1対応のためインラインフレーム化する                       //
//            allowtransparency='true'を追加し読込側の<body>transparent追加 //
//////////////////////////////////////////////////////////////////////////////
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/companyCalendar_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='80%' height='35' title='項目'>\n";
// echo "    項目を表示しています。\n";
// echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/companyCalendar_ViewCalendar-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='85%' title='一覧' allowtransparency='true'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/companyCalendar_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='98%' height='35' title='フッター'>\n";
// echo "    フッターを表示しています。\n";
// echo "</iframe>\n";
?>
