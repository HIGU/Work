<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理 刻印マスター メンテナンス View部                                //
// Copyright (C) 2007-8 Norihisa.Ohya                                       //
// Changed history                                                          //
// 2007/07/26 punchMark_MasterMnt_View.php                                  //
// 2007/10/18 <iframe>にhspace='0' vspace='0'を追加 ソート順の機能追加 小林 //
// 2007/10/19 棚番順追加 デザイン変更(ヘッダーをインラインへ)               //
//            検索機能を追加したので<select>に未選択を追加             小林 //
// 2007/10/20 <ifram>のリストを height='68%' → height='66%' へ変更    小林 //
// 2007/10/23 entry_formのcellpadding='3'→'1'と'3'→'0'へ各々変更     小林 //
//            <ifram>のリストを height='66%' → height='67%' へ変更    小林 //
// 2007/10/24 ,の後にスペースがない個所を訂正                               //
// 2007/11/09 変更時は棚番も入力禁止にする                             小林 //
// 2007/11/10 Markをセットして指定行へジャンプ                         小林 //
// 2008/04/21 購買依頼により客先入力欄を6文字に変更                         //
// 2008/05/15 購買依頼により備考欄の入力文字数を100に変更                   //
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
<script type='text/javascript' src='punchMark_MasterMnt.js'></script>
<link rel='stylesheet' href='punchMark_MasterMnt.css' type='text/css' media='screen'>
<script type='text/javascript'>
function set_focus_code() {
    document.entry_form.punchMark_code.focus();
    document.entry_form.punchMark_code.select();
}

function set_focus_name() {
    document.entry_form.mark.focus();
    // document.entry_form.mark.select();
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
<center>
<?php echo $menu->out_title_border() ?>
    <form name='entry_form' action='punchMark_MasterMnt_Main.php' method='post' target='_self'>
        <table class='outside_field' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='inside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='0'>
            <!--
            <tr>
                <td class='winbox_title' align='center' colspan='9'>
                刻印マスター
                </td>
            </tr>
            -->
            <tr>
                <th class='winbox' nowrap>刻印コード</th>
                <th class='winbox' nowrap>棚　番</th>
                <th class='winbox' nowrap>刻印内容</th>
                <th class='winbox' nowrap>形　状</th>
                <th class='winbox' nowrap>サイズ</th>
                <th class='winbox' nowrap>客先コード</th>
                <th class='winbox' nowrap>備　考</th>
                <th class='winbox' colspan='2' nowrap>機能ボタン</th>
            </tr>
            <tr>
                <?php if ($request->get('copy_flg') == 1) { ?>
                    <td class='winbox' align='center'><input type='text' name='punchMark_code' value='<?php echo $request->get('punchMark_code') ?>' size='6' maxlength='6' readonly class='readonly'></td>
                    <td class='winbox' align='center'><input type='text' name='shelf_no' value='<?php echo $request->get('shelf_no') ?>' size='6' maxlength='6' readonly class='readonly'></td>
                <?php } else { ?>
                    <td class='winbox' align='center'><input type='text' name='punchMark_code' value='<?php echo $request->get('punchMark_code') ?>' size='6' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' name='shelf_no' value='<?php echo $request->get('shelf_no') ?>' size='6' maxlength='6'></td>
                <?php } ?>
                <td class='winbox' align='center'><textarea name='mark' rows='3' cols='10'><?php echo $request->get('mark') ?></textarea></td>
                <td class='winbox' align='center'>
                    <span class='pt11b'>
                        <select name='shape_code' size='1'>
                            <option value='' style='color:red;'>未選択</option>
                        <?php
                        $res_shape = $result->get_array2('res_shape');
                        $rows_shape  = $request->get('rows_shape');
                        for ($i=0; $i<$rows_shape; $i++) {
                            if ( $res_shape[$i][0] == $request->get('shape_code')) {
                                printf("<option value='%s' selected>%s</option>\n", $res_shape[$i][0], $res_shape[$i][1]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res_shape[$i][0], $res_shape[$i][1]);
                            }
                        }
                        ?>
                        </select>
                    </span>
                </td>
                <td class='winbox' align='center'>
                    <span class='pt11b'>
                        <select name='size_code' size='1'>
                            <option value='' style='color:red;'>未選択</option>
                        <?php
                        $res_size = $result->get_array2('res_size');
                        $rows_size  = $request->get('rows_size');
                        for ($i=0; $i<$rows_size; $i++) {
                            if ( $res_size[$i][0] == $request->get('size_code')) {
                                printf("<option value='%s' selected>%s</option>\n", $res_size[$i][0], $res_size[$i][1]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res_size[$i][0], $res_size[$i][1]);
                            }
                        }
                        ?>
                        </select>
                    </span>
                </td>
                <td class='winbox' align='center'><input type='text' name='user_code' value='<?php echo $request->get('user_code') ?>' size='6' maxlength='6'></td>
                <td class='winbox' align='center'><input type='text' name='note' value='<?php echo $request->get('note') ?>' size='25' maxlength='100'></td>
                <td class='winbox' align='center'>
                <?php if ($request->get('copy_flg') == 1) { ?>
                    <input type='button' class='pt11b' name='chagenButton' value='変更' onClick='checkChange(document.entry_form);'><br>
                <?php } else { ?>
                    <input type='button' class='pt11b' name='entryButton' value='登録' onClick='checkEdit(document.entry_form);'><br>
                <?php } ?>
                    <input type='submit' class='pt11b' name='search' value='検索'>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pt11b' name='delButton' value='削除' onClick='checkDelete(document.entry_form);'><br>
                    <input type='button' class='pt11b' name='cancelButton' value='取消' onClick='clearKeyValue(document.entry_form); document.entry_form.submit();'>
                </td>
                <input type='hidden' name='entry' value=''>
                <input type='hidden' name='change' value=''>
                <input type='hidden' name='del' value=''>
                <input type='hidden' name='targetSortItem' value='<?php echo $request->get('targetSortItem')?>'>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <div style='text-align:left;'>
            　　　　　
            <input type='button' class='sortButton' name='sortCode'  value='コード順' onClick='sortItem("code")'<?php if ($request->get('targetSortItem')=='code') echo " style='color:blue;'"?>>
            　
            <input type='button' class='sortButton' name='sortShelf' value='棚番号順' onClick='sortItem("shelf")'<?php if ($request->get('targetSortItem')=='shelf') echo " style='color:blue;'"?>>
            &nbsp;<input type='button' class='sortButton' name='sortMark'  value='内容　順' onClick='sortItem("mark")'<?php if ($request->get('targetSortItem')=='mark') echo " style='color:blue;'"?>>
        </div>
    </form>
    <?php
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='punchMark_MasterMnt_ViewHeader.html?{$uniq}' name='header' align='center' width='100%' height='32' title='項目'>\n";
    echo "    項目を表示しています。\n";
    echo "</iframe>\n";
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/punchMark_MasterMnt_List-{$_SESSION['User_ID']}.html?{$uniq}#Mark' name='list' align='center' width='100%' height='67%' title='リスト'>\n";
    echo "    一覧を表示しています。\n";
    echo "</iframe>\n";
    ?>
</center>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
