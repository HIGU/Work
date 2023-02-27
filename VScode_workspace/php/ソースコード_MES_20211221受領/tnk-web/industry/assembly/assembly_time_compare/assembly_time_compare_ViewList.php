<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の完成一覧より実績工数と登録工数の比較    表示(Ajax)     MVC View部  //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created   assembly_time_Compare_ViewList.php                  //
// 2006/04/13 height='38→'35'(NN7.1対策), frameborder='0' を追加           //
// 2006/08/31 item={$this->request->get('targetSortItem')} を追加           //
// 2007/06/12 ジャンプ用に#Mark を追加                                      //
//////////////////////////////////////////////////////////////////////////////
// echo "<br>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='assembly_time_compare_ViewHeader.html?item={$this->request->get('targetSortItem')}&{$uniq}' name='header' align='center' width='100%' height='35' title='項目'>\n";
echo "    表の項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assembly_time_compare_ViewList-{$_SESSION['User_ID']}.html?item={$this->request->get('targetSortItem')}&{$uniq}#Mark' name='list' align='center' width='100%' height='75%' title='組立完成一覧'>\n";
echo "    組立完成一覧を表示しています。\n";
echo "</iframe>\n";
?>
