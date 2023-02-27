<?php
//////////////////////////////////////////////////////////////////////////////
// 共通 権限 関係テーブル メンテナンス Division表示(Ajax)       MVC View部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/21 Created   common_authority_ViewListDivision.php               //
//            height='38→'35'(NN7.1対策), frameborder='0' を追加           //
// 2006/08/02 NN7.1で勝手にsubmitしてしまうため onSubmit='return false;'追加//
// 2006/09/06 権限名の修正機能追加に伴い &targetEditDiv='division' を追加   //
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
?>
<form id='addDivisionForm' action='<?php echo $menu->out_self()?>' method='post' onSubmit='return false;'>
<table width='90%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
    <tr><td> <!----------- ダミー(デザイン用) ------------>
<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='1'>
    <tr>
        <th class='winbox' nowrap>権限名</th>
        <td class='winbox' align='center'><input type='text' id='targetAuthName' value='' size='80' maxlength='100'></td>
        <td class='winbox' align='center'><input type='button' id='addDivision' value='追加' class='addButton'></td>
    </tr>
</table>
    </td></tr>
</table> <!----------------- ダミーEnd ------------------>
</form>

<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_self(), "?Action=ListDivHeader&showMenu=ListDivHeader&{$uniq}"?>' name='header' align='center' width='90%' height='33' title='項目'>
    項目を表示しています。
</iframe>
<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_self(), "?Action=ListDivBody&showMenu=ListDivBody&targetEditDiv={$result->get('division')}&{$uniq}"?>' name='list' align='center' width='90%' height='32%' title='一覧'>
    一覧を表示しています。
</iframe>

<!--
<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_self()?>' name='footer' align='center' width='90%' height='35' title='フッター'>
    フッターを表示しています。
</iframe>
-->
