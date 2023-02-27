<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理 部品番号マスター メンテナンス View部                            //
// Copyright (C) 2007 Norihisa.Ohya                                         //
// Changed history                                                          //
// 2007/07/30 punchMark_partsMasterMnt_View.php                             //
// 2007/10/02 部品番号の入力にonKeyUp='baseJS.keyInUpper(this);'を追加し    //
//            大文字になるよう変更                                          //
// 2007/10/18 <iframe>にhspace='0' vspace='0'を追加                    小林 //
// 2007/10/20 リストのヘッダーを<iframe>で追加                         小林 //
// 2007/11/09 変更時は刻印コードも入力禁止にする                       小林 //
// 2007/11/10 Markをセットして指定行へジャンプ                         小林 //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<script type='text/javascript' src='punchMark_partsMasterMnt.js'></script>
<link rel='stylesheet' href='punchMark_MasterMnt.css' type='text/css' media='screen'>
<script type='text/javascript'>
function set_focus_code() {
    document.entry_form.parts_no.focus();
    document.entry_form.parts_no.select();
}

function set_focus_name() {
    document.entry_form.note.focus();
    // document.entry_form.note.select();
}
</script>
</head>
<body style='overflow-y:hidden;'
<?php if ($request->get('copy_flg') == 1) { ?>
    onLoad='set_focus_name();'
>
<?php } else { ?>
    onLoad='set_focus_code();'
>
<?php } ?>
<?php echo $menu->out_title_border() ?>
    <form name='entry_form' action='punchMark_partsMasterMnt_Main.php' method='post' target='_self'>
        <table class='outside_field' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='inside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <th class='winbox' nowrap>部品番号</th>
                <th class='winbox' nowrap>刻印コード</th>
                <th class='winbox' nowrap>備　　考</th>
                <th class='winbox' colspan='4' nowrap>機能ボタン</th>
            </tr>
            <tr>
                <?php if ($request->get('copy_flg') == 1) { ?>
                    <td class='winbox' align='center'><input type='text' name='parts_no' value='<?php echo $request->get('parts_no') ?>' size='10' maxlength='9' readonly class='readonly'></td>
                    <td class='winbox' align='center'><input type='text' name='punchMark_code' value='<?php echo $request->get('punchMark_code') ?>' size='6' maxlength='6' readonly class='readonly'></td>
                <?php } else { ?>
                    <td class='winbox' align='center'><input type='text' name='parts_no' value='<?php echo $request->get('parts_no') ?>' size='10' maxlength='9' onKeyUp='baseJS.keyInUpper(this);'></td>
                    <td class='winbox' align='center'><input type='text' name='punchMark_code' value='<?php echo $request->get('punchMark_code') ?>' size='6' maxlength='6'></td>
                <?php } ?>
                <td class='winbox' align='center'><input type='text' name='note' value='<?php echo $request->get('note') ?>' size='50' maxlength='50'></td>
                <td class='winbox' align='center'>
                    <input type='submit' class='pt11b' name='search' value='検索'>
                </td>
                <td class='winbox' align='center'>
                <?php if ($request->get('copy_flg') == 1) { ?>
                    <input type='button' class='pt11b' name='changeButton' value='変更' onClick='checkChange(document.entry_form);'>
                <?php } else { ?>
                    <input type='button' class='pt11b' name='entryButton' value='登録' onClick='checkEdit(document.entry_form);'>
                <?php } ?>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pt11b' name='delButton' value='削除' onClick='checkDelete(document.entry_form);'>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pt11b' name='cancelButton' value='取消' onClick='clearKeyValue(document.entry_form); document.entry_form.submit();'>
                </td>
                <input type='hidden' name='entry' value=''>
                <input type='hidden' name='change' value=''>
                <input type='hidden' name='del' value=''>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    <br>
    <?php
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='punchMark_partsMasterMnt_ViewHeader.html?{$uniq}' name='header' align='center' width='100%' height='32' title='項目'>\n";
    echo "    項目を表示しています。\n";
    echo "</iframe>\n";
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/punchMark_partsMasterMnt_List-{$_SESSION['User_ID']}.html?{$uniq}#Mark' name='list' align='center' width='100%' height='71%' title='リスト'>\n";
    echo "    一覧を表示しています。\n";
    echo "</iframe>\n";
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
