<?php
//////////////////////////////////////////////////////////////////////////////
// �������ñ�� ���ڳ�Ψ����Ͽ View��                                       //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/05/13 Created  materialPartsCredit_View.php                         //
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
    if (obj.parts_no.value.length == 0) {
        alert('���ʣΣ�.�����Ϥ���Ƥ��ޤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (obj.credit_per.value.length == 0) {
        alert('��Ψ�����Ϥ���Ƥ��ޤ���');
        obj.credit_per.focus();
        obj.credit_per.select();
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
<body scroll=no onload='document.entry_form.parts_no.focus();'>
    <center>
    <?php echo $menu->out_title_border() ?>
        <form name='entry_form' action='materialPartsCredit_Main.php' method='post' onSubmit='return chk_entry(this)'>
            <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>
                    ��Ψ�ޥ�����
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
                    <td class='winbox' align='center'><input type='text' class='price_font' name='parts_no' value='<?php echo $field_g = $request->get('parts_no') ?>' size='11' maxlength='9'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='credit_per' value='<?php echo $field_g = $request->get('credit_per') ?>' size='8' maxlength='6'></td>
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/materialPartsCredit_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
