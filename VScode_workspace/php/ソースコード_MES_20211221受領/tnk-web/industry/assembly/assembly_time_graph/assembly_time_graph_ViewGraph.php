<?php
//////////////////////////////////////////////////////////////////////////////
// 組立のライン別工数 各種グラフ    表示(Ajax)                  MVC View部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/16 Created   assembly_time_graph_ViewGraph.php                   //
//////////////////////////////////////////////////////////////////////////////
// echo "<br>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='graph/assembly_time_graph_ViewGraph-{$_SESSION['User_ID']}.html?{$uniq}' name='graph' align='center' width='100%' height='75%' title='ライン別 工数 グラフ'>\n";
echo "    ライン別 工数のグラフを表示しています。\n";
echo "</iframe>\n";
?>
