<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理 形状マスター メンテナンス View部                                //
// Copyright (C) 2007 Norihisa.Ohya                                         //
// Changed history                                                          //
// 2007/07/24 punchMark_shapeMasterMnt_View.php                             //
// 2007/10/20 リストのヘッダーを<iframe>で追加リストのheight='50%'を削除小林//
// 2007/10/24 ,の後にスペースがない個所を訂正                               //
//            <select>内にValueが設定されているのを削除                     //
// 2007/11/09 上記のheight='50%'を復活させ'67%'へ変更  取消ボタンを追加 小林//
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<script type='text/javascript' src='punchMark_shapeMasterMnt.js'></script>
<link rel='stylesheet' href='punchMark_MasterMnt.css' type='text/css' media='screen'>
<script type='text/javascript'>
function set_focus_code() {
    document.entry_form.shape_code.focus();
    document.entry_form.shape_code.select();
}

function set_focus_name() {
    document.entry_form.shape_name.focus();
}
</script>
</head>
<body style='overflow-y:hidden;' onLoad='
<?php if ($request->get('copy_flg') == 1) { ?>
            set_focus_name();
<?php } else { ?>
            set_focus_code();
<?php } ?>
        '
>
<?php echo $menu->out_title_border() ?>
    <form name='entry_form' action='punchMark_shapeMasterMnt_Main.php' method='post' target='_self'>
        <table class='outside_field' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='inside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox_title' align='center' colspan='5'>
                形状マスター
                </td>
            </tr>
            <tr>
                <?php
                $field = $result->get_array2('field');
                for ($i=0; $i<$request->get('num'); $i++) {             // フィールド数分繰返し
                ?>
                <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                }
                ?>
                <th class='winbox' colspan='2' nowrap>追加・変更</th>
            </tr>
            <tr>
                <?php if ($request->get('copy_flg') == 1) { ?>
                    <td class='winbox' align='center'><input type='text' name='shape_code' value='<?php echo $request->get('shape_code') ?>' size='6' maxlength='3' readonly class='readonly'></td>
                <?php } else { ?>
                    <td class='winbox' align='center'><input type='text' name='shape_code' value='<?php echo $request->get('shape_code') ?>' size='6' maxlength='3'></td>
                <?php } ?>
                <td class='winbox' align='center'>
                    <span class='pt11b'>
                        <select name='shape_name' size='1'>
                            <?php
                            $res_name = $result->get_array2('shape_name_master');
                            $rows_name  = $request->get('rows_name');
                            for ($i=0; $i<$rows_name; $i++) {
                                if ( $res_name[$i] == $request->get('shape_name')) {
                                    printf("<option value='%s' selected>%s</option>\n", $res_name[$i], $res_name[$i]);
                                } else {
                                    printf("<option value='%s'>%s</option>\n", $res_name[$i], $res_name[$i]);
                                }
                            }
                            ?>
                        </select>
                    </span>
                </td>
                <td class='winbox' align='center'><input type='text' name='note' value='<?php echo $request->get('note') ?>' size='50' maxlength='50'></td>
                <td class='winbox' align='center'>
                    <input type='submit' class='pt11b' name='cancelButton' value='取消'>
                <?php if ($request->get('copy_flg') == 1) { ?>
                    <input type='button' class='pt11b' name='entryButton' value='変更' onClick='return checkEdit(document.entry_form);'>
                <?php } else { ?>
                    <input type='button' class='pt11b' name='entryButton' value='登録' onClick='checkEdit(document.entry_form);'>
                <?php } ?>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pt11b' name='delButton' value='削除' onClick='checkDelete(document.entry_form);'>
                </td>
                <input type='hidden' name='entry' value=''>
                <input type='hidden' name='del' value=''>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    <br>
    <?php
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='punchMark_shapeMasterMnt_ViewHeader.html?{$uniq}' name='header' align='center' width='60%' height='32' title='項目'>\n";
    echo "    項目を表示しています。\n";
    echo "</iframe>\n";
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/punchMark_shapeMasterMnt_List-{$_SESSION['User_ID']}.html?{$uniq}' name='list' align='center' width='60%' height='67%' title='リスト'>\n";
    echo "    一覧を表示しています。\n";
    echo "</iframe>\n";
    //echo "<iframe frameborder='0' scrolling='no' src='list/punchMark_shapeMasterMnt_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='リスト'>\n";
    //echo "</iframe>\n";
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
