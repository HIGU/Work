<?php
//////////////////////////////////////////////////////////////////////////////
// 部品売上げの材料費(購入費)の照会 材料費の結果 表示(Ajax) MVC View部      //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/19 Created   parts_material_show_ViewListTable.php               //
//////////////////////////////////////////////////////////////////////////////
echo "<br>\n";
echo $this->model->getViewListTable($this->request);
?>
