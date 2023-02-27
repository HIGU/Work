<?php
//////////////////////////////////////////////////////////////////////////////
// 共通 権限 関係テーブル メンテナンス  IDリスト表示(Ajax)      MVC View部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/25 Created   common_authority_ViewListID.php                     //
//            height='38→'35'(NN7.1対策), frameborder='0' を追加           //
// 2006/08/02 NN7.1で勝手にsubmitしてしまうため onSubmit='return false;'追加//
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
?>
<form id='addIDForm' target='<?php echo $menu->out_self()?>' method='post' onSubmit='return false;'>
<input type='hidden' id='targetDivision' value='<?php echo $request->get('targetDivision')?>'>
<table width='90%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
    <caption><?php echo $model->getViewDivisionName($request)?></caption>
    <tr><td> <!----------- ダミー(デザイン用) ------------>
<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='1'>
    <tr>
        <th class='winbox' width='10%'>メンバー</th>
        <td class='winbox center' width='26%'>
            <input type='text' id='targetID' value='' size='20' maxlength='20'>
        </td>
        <th class='winbox' width='7%'>種類</th>
        <td class='winbox' width='15%' id='showCateList'><?php echo $model->categorySelectList() ?>
        </td>
        <th class='winbox' width='7%'>内容</th>
        <td class='winbox' width='25%' id='showIDName'>&nbsp;</td>
        <td class='winbox' width='10%' align='center'><input type='button' id='addID' value='追加' class='addButton'></td>
    </tr>
</table>
    </td></tr>
</table> <!----------------- ダミーEnd ------------------>
</form>

<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_self(), "?Action=ListIDHeader&showMenu=ListIDHeader&{$uniq}"?>' name='header' align='center' width='90%' height='35' title='項目'>
    項目を表示しています。
</iframe>
<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_self(), "?Action=ListIDBody&showMenu=ListIDBody&targetDivision={$request->get('targetDivision')}&{$uniq}"?>' name='list' align='center' width='90%' height='32%' title='一覧'>
    一覧を表示しています。
</iframe>

<!--
<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_self(), "?{$uniq}"?>' name='footer' align='center' width='90%' height='35' title='フッター'>
    フッターを表示しています。
</iframe>
-->
