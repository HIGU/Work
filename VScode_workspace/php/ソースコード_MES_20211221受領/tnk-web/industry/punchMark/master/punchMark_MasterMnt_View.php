<?php
//////////////////////////////////////////////////////////////////////////////
// ������� ����ޥ����� ���ƥʥ� View��                                //
// Copyright (C) 2007-8 Norihisa.Ohya                                       //
// Changed history                                                          //
// 2007/07/26 punchMark_MasterMnt_View.php                                  //
// 2007/10/18 <iframe>��hspace='0' vspace='0'���ɲ� �����Ƚ�ε�ǽ�ɲ� ���� //
// 2007/10/19 ê�ֽ��ɲ� �ǥ������ѹ�(�إå����򥤥�饤���)               //
//            ������ǽ���ɲä����Τ�<select>��̤������ɲ�             ���� //
// 2007/10/20 <ifram>�Υꥹ�Ȥ� height='68%' �� height='66%' ���ѹ�    ���� //
// 2007/10/23 entry_form��cellpadding='3'��'1'��'3'��'0'�سơ��ѹ�     ���� //
//            <ifram>�Υꥹ�Ȥ� height='66%' �� height='67%' ���ѹ�    ���� //
// 2007/10/24 ,�θ�˥��ڡ������ʤ��Ľ������                               //
// 2007/11/09 �ѹ�����ê�֤����϶ػߤˤ���                             ���� //
// 2007/11/10 Mark�򥻥åȤ��ƻ���Ԥإ�����                         ���� //
// 2008/04/21 �������ˤ������������6ʸ�����ѹ�                         //
// 2008/05/15 �������ˤ�������������ʸ������100���ѹ�                   //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='inside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='0'>
            <!--
            <tr>
                <td class='winbox_title' align='center' colspan='9'>
                ����ޥ�����
                </td>
            </tr>
            -->
            <tr>
                <th class='winbox' nowrap>���������</th>
                <th class='winbox' nowrap>ê����</th>
                <th class='winbox' nowrap>�������</th>
                <th class='winbox' nowrap>������</th>
                <th class='winbox' nowrap>������</th>
                <th class='winbox' nowrap>���襳����</th>
                <th class='winbox' nowrap>������</th>
                <th class='winbox' colspan='2' nowrap>��ǽ�ܥ���</th>
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
                            <option value='' style='color:red;'>̤����</option>
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
                            <option value='' style='color:red;'>̤����</option>
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
                    <input type='button' class='pt11b' name='chagenButton' value='�ѹ�' onClick='checkChange(document.entry_form);'><br>
                <?php } else { ?>
                    <input type='button' class='pt11b' name='entryButton' value='��Ͽ' onClick='checkEdit(document.entry_form);'><br>
                <?php } ?>
                    <input type='submit' class='pt11b' name='search' value='����'>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' class='pt11b' name='delButton' value='���' onClick='checkDelete(document.entry_form);'><br>
                    <input type='button' class='pt11b' name='cancelButton' value='���' onClick='clearKeyValue(document.entry_form); document.entry_form.submit();'>
                </td>
                <input type='hidden' name='entry' value=''>
                <input type='hidden' name='change' value=''>
                <input type='hidden' name='del' value=''>
                <input type='hidden' name='targetSortItem' value='<?php echo $request->get('targetSortItem')?>'>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <div style='text-align:left;'>
            ����������
            <input type='button' class='sortButton' name='sortCode'  value='�����ɽ�' onClick='sortItem("code")'<?php if ($request->get('targetSortItem')=='code') echo " style='color:blue;'"?>>
            ��
            <input type='button' class='sortButton' name='sortShelf' value='ê�ֹ��' onClick='sortItem("shelf")'<?php if ($request->get('targetSortItem')=='shelf') echo " style='color:blue;'"?>>
            &nbsp;<input type='button' class='sortButton' name='sortMark'  value='���ơ���' onClick='sortItem("mark")'<?php if ($request->get('targetSortItem')=='mark') echo " style='color:blue;'"?>>
        </div>
    </form>
    <?php
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='punchMark_MasterMnt_ViewHeader.html?{$uniq}' name='header' align='center' width='100%' height='32' title='����'>\n";
    echo "    ���ܤ�ɽ�����Ƥ��ޤ���\n";
    echo "</iframe>\n";
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/punchMark_MasterMnt_List-{$_SESSION['User_ID']}.html?{$uniq}#Mark' name='list' align='center' width='100%' height='67%' title='�ꥹ��'>\n";
    echo "    ������ɽ�����Ƥ��ޤ���\n";
    echo "</iframe>\n";
    ?>
</center>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
