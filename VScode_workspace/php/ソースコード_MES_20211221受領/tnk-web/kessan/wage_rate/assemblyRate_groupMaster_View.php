<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω��Ψ ���롼�ץޥ������ɲá��Խ� View��                               //
// Copyright (C) 2007-2008 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/07 Created  assemblyRate_groupMaster_View.php                    //
// 2007/12/13 �嵭�����Ȥ˳�ĥ��.php�����äƤ��ʤ��Τ��ɲ�                //
// 2008/01/09 �ɲá�����������եǡ����μ����Ϥ����ɲ�                      //
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
    if (obj.group_no.value.length == 0) {
        alert('���롼�ףΣ�.�����Ϥ���Ƥ��ޤ���');
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    } else if ( !(isDigit(obj.group_no.value)) ) {
        alert('���롼��No.�Ͽ����ʳ����Ͻ���ޤ���');
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    }
    
    if (obj.group_name.value.length == 0) {
        alert('���롼��̾�����Ϥ���Ƥ��ޤ���');
        obj.group_name.focus();
        obj.group_name.select();
        return false;
    }
    return true;
}
// -->
</script>

<style type="text/css">
<!--
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body scroll=no>
    <center>
    <?php echo $menu->out_title_border() ?>
        <form name='entry_form' action='assemblyRate_groupMaster_Main.php' method='post' onSubmit='return chk_entry(this)'>
            <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>
                    ���롼�ץޥ�����
                    </td>
                </tr>
                <tr>
                    <?php
                    $field_g = $result->get_array2('field_g');
                    for ($i=0; $i<$result->get('num_g'); $i++) {             // �ե�����ɿ�ʬ���֤�
                    ?>
                    <th class='winbox' nowrap><?php echo $field_g[$i] ?></th>
                    <?php
                    }
                    ?>
                    <th class='winbox' colspan='12' nowrap>�ɲá��ѹ�</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='group_no' value='<?php echo $field_g = $request->get('group_no') ?>' size='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='group_name' value='<?php echo $field_g = $request->get('group_name') ?>' size='50'></td>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='�ɲ�'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='del' value='���'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assemblyRate_groupMaster_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
