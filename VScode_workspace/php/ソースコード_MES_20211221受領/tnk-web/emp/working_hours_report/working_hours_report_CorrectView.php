<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ƚ���ν��� �������Ƥ�����                                   View ��  //
// Copyright (C) 2008 - 2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2008/11/21 Created   working_hours_report_CorrectView.php                //
// 2017/06/02 ����Ĺ���� �ܳʲ�ư                                           //
// 2017/06/29 ���顼�ս���������                                            //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ���Υ����å��ؿ� */
function chk_entry(obj) {
    if (obj.uid.value.length != 6) {
        alert('�Ұ��ֹ�ϣ���ο��������Ϥ��Ʋ�������');
        obj.uid.focus();
        obj.uid.select();
        return false;
    } else if ( !(isDigit(obj.uid.value)) ) {
        alert('�Ұ��ֹ�Ͽ����ʳ����Ͻ���ޤ���');
        obj.uid.focus();
        obj.uid.select();
        return false;
    }
    if (obj.working_date.value.length != 8) {
        alert('����ǯ�����ϣ���ο��������Ϥ��Ʋ�������(�㡧2008ǯ4��1����20080401)');
        obj.working_date.focus();
        obj.working_date.select();
        return false;
    } else if ( !(isDigit(obj.working_date.value)) ) {
        alert('����ǯ�����Ͽ����ʳ����Ͻ���ޤ���');
        obj.working_date.focus();
        obj.working_date.select();
        return false;
    }
    if (obj.correct_contents.value.length == 0) {
        alert('�������Ƥ����Ϥ���Ƥ��ޤ���');
        obj.correct_contents.focus();
        obj.correct_contents.select();
        return false;
    }
    return true;
}
// -->
</script>

<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='working_hours_report.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
</head>
<body scroll=no>
    <center>
       <form name='entry_form' action='working_hours_report_CorrectMain.php' method='post' onSubmit='return chk_entry(this)'>
            <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' colspan='5' nowrap align='left'><font size='3'>���������������μҰ��ֹ����������������ǯ�������������Ƥ����Ϥ��Ƥ���������</font></th>
                </tr>
                <tr>
                    <th class='winbox' colspan='5' nowrap align='left'><font size='3'>����˼Ұ��ֹ桧300144(��ë)������ǯ����:20081201���������ơ��ĶȻ��֤�2���֤���1���֤ˤ��Ʋ�������</font></th>
                </tr>
                <tr>
                    <th class='winbox' colspan='5' nowrap align='left'><font size='3'>�����Ʊ���Ұ��ֹ桦����ǯ��������Ͽ�Ͼ�񤭤���ޤ��Τ���դ��Ƥ���������</font></th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>�Ұ��ֹ�</th>
                    <th class='winbox' nowrap>����ǯ����</th>
                    <th class='winbox' nowrap>��������</th>
                    <th class='winbox' colspan='2' nowrap>�ɲá��ѹ� / ���</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='uid' value='<?php echo $field_g = $request->get('uid') ?>' size='8' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='working_date' value='<?php echo $field_g = $request->get('working_date') ?>' size='10' maxlength='8'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='correct_contents' value='<?php echo $field_g = $request->get('correct_contents') ?>' size='80'></td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='�ɲá��ѹ�' onClick='return confirm("�����������Ƥ��ѹ����ɲä��Ƥ�����Ǥ�����");'>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='del' value='���' onClick='return confirm("�����������Ƥ������Ƥ�����Ǥ�����\n�������ȸ��ˤ��᤻�ޤ��󤬡�������Ǥ�����");'>
                    </td>
                </tr>
            </TBODY>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '�Ȳ�') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_Correct_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
    <div align='center'><input type='button' name='closeButton' id='closeID' value='&nbsp;&nbsp;�Ĥ���&nbsp;&nbsp;' onClick='window.close();'></div>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
