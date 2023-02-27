<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の登録工数と実績工数の比較 照会  ヘッダー部   表示(Ajax) MVC View部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created   assembly_time_show_ViewListTable.php                //
//////////////////////////////////////////////////////////////////////////////
echo "<br>\n";
echo $this->model->getViewListTable($this->request);
?>
