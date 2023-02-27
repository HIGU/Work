<?php
//////////////////////////////////////////////////////////////////////////////
// 部門別 製造経費及び販管費の照会          表示(Ajax)          MVC View部  //
// Copyright (C) 2007-8    Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/13 Created   act_summary_ViewListAjax.php                        //
// 2007/11/09 部門コードの表示を追加                                        //
// 2008/05/21 半期累計追加の為width='80%'→width='95%'へ変更           大谷 //
// 2008/09/12 全社合計の製造経費の照会を追加                           大谷 //
//////////////////////////////////////////////////////////////////////////////
if ($session->get_local('targetAct_id') == '000') {
    echo "<div class='pt12b'>全社合計の製造経費</div>\n";
} else {
    echo "<div class='pt12b'>部門コード：{$session->get_local('targetAct_id')}</div>\n";
}
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='act_summary_ViewListHeader.html?{$uniq}' name='header' align='center' width='100%' height='35' title='ヘッダー'>\n";
echo "    項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/act_summary_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='100%' height='75%' title='ボディー'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/act_summary_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='80%' height='35' title='フッター'>\n";
// echo "    フッターを表示しています。\n";
// echo "</iframe>\n";
?>
