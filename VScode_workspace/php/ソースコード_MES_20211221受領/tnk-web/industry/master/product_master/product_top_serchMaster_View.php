<?php
//////////////////////////////////////////////////////////////////////////////
// �Ȳ�����ʬ�॰�롼�פ���Ͽ View��                                        //
// Copyright (C) 2011-2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/12/11 Created  product_top_serchMaster_View.php                     //
// 2011/11/10 ����ե������������롼���ֹ�ʤ�褦���ɲ�                    //
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
    if (obj.top_no.value.length == 0) {
        alert('���롼�ףΣ�.�����Ϥ���Ƥ��ޤ���');
        obj.top_no.focus();
        obj.top_no.select();
        return false;
    } else if ( !(isDigit(obj.top_no.value)) ) {
        alert('���롼��No.�Ͽ����ʳ����Ͻ���ޤ���');
        obj.top_no.focus();
        obj.top_no.select();
        return false;
    }
    
    if (obj.top_name.value.length == 0) {
        alert('���롼��̾�����Ϥ���Ƥ��ޤ���');
        obj.top_name.focus();
        obj.top_name.select();
        return false;
    }
    if ( !(isDigit(obj.s_order.value)) ) {
        alert('�Ȳ��Ͽ����ʳ����Ͻ���ޤ���');
        obj.s_order.focus();
        obj.s_order.select();
        return false;
    }
    return true;
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.entry_form.top_no.focus();
    document.entry_form.top_no.select();
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
<body onLoad='set_focus()'>
<body scroll=no>
    <center>
    <?php echo $menu->out_title_border() ?>
        <form name='entry_form' action='product_top_serchMaster_Main.php' method='post' onSubmit='return chk_entry(this)'>
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
                    <td class='winbox' align='center'><input type='text' class='price_font' name='top_no' value='<?php echo $field_g = $request->get('top_no') ?>' size='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='top_name' value='<?php echo $field_g = $request->get('top_name') ?>' size='50'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='s_order' value='<?php echo $field_g = $request->get('s_order') ?>' size='6'></td>
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/product_top_serchMaster_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
