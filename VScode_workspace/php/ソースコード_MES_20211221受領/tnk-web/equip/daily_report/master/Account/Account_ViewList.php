<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理システムの権限マスター保守           表示(Ajax)  MVC View部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/14 Created   Account_ViewList.php                                //
//            NN7.1 ではheight が 30 以上でないとScroll Barが出ない。       //
//////////////////////////////////////////////////////////////////////////////
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='Account_ViewHeader.php' name='header' align='center' width='60%' height='30' title='項目'>\n";
echo "    表の項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='Account_ViewFrameList.php' name='list' align='center' width='60%' height='70%' title='権限マスター一覧'>\n";
echo "    権限マスターを表示しています。\n";
echo "</iframe>\n";
?>
