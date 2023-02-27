<?php
//////////////////////////////////////////////////////////////////////////////
// 受入検査の時間・件数の集計･分析 結果 照会    表示(Ajax)      MVC View部  //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/08/09 Created   acceptance_inspection_analyze_ViewListAjax.php      //
//            height='38→'35'(NN7.1対策), frameborder='0' を追加           //
// 2006/11/30 Header Footer 両方を使用する様に変更                          //
// 2007/09/05 if ($request->get('targetDateStr') < '20070901')条件を追加    //
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html?{$uniq}' name='header' align='center' width='80%' height='35' title='項目'>\n";
echo "    項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='80%' height='50%' title='一覧'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html?{$uniq}' name='footer' align='center' width='80%' height='35' title='フッター'>\n";
echo "    フッターを表示しています。\n";
echo "</iframe>\n";
if ($request->get('targetDateStr') < '20070901') {
    echo "<div>注意：土日は考慮されていません。</div>\n";
}
?>
