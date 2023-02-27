<?php
//////////////////////////////////////////////////////////////////////////////
// ��������ǡ����ɲá��Խ� View��                                        //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/13 Created  assemblyRate_machineWork_View.php                    //
//            ��ե�������$result,$request�����ѹ�                        //
// 2011/06/22 format_date�Ϥ�tnk_func�˰�ư�Τ��ᤳ�������               //
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

/* ���ϥǡ����Υ����å� */
function chk_entry(obj) {
    if(!obj.mac_no.value.length){
        alert("�����ֹ椬���Ϥ���Ƥ��ޤ���");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    } else if(!(isDigit(obj.mac_no.value))){
        alert("�����ֹ�ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    }
    
    if(!obj.setup_time.value.length){
        alert("�ʼ���֤����Ϥ���Ƥ��ޤ���");
        obj.setup_time.focus();
        obj.setup_time.select();
        return false;
    } else if(!(isDigit(obj.mac_no.value))){
        alert("�ʼ���֤ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.setup_time.focus();
        obj.setup_time.select();
        return false;
    }
    
    if(!obj.operation_time.value.length){
        alert("�ܲ�Ư���֤����Ϥ���Ƥ��ޤ���");
        obj.operation_time.focus();
        obj.operation_time.select();
        return false;
    } else if(!(isDigit(obj.mac_no.value))){
        alert("�ܲ�Ư���֤ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.operation_time.focus();
        obj.operation_time.select();
        return false;
    }
    
    if(!obj.repairing_expenses.value.length){
        alert("���������Ϥ���Ƥ��ޤ���");
        obj.repairing_expenses.focus();
        obj.repairing_expenses.select();
        return false;
    } else if(!(isDigit(obj.repairing_expenses.value))){
        alert("������ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.repairing_expenses.focus();
        obj.repairing_expenses.select();
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
    <form name='entry_form' method='post' action='assemblyRate_machineWork_Main.php' onSubmit='return chk_entry(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td bgcolor='#ffffc6' align='center' colspan='20'>
                        <?php
                        echo format_date6_kan($request->get('wage_ym'));
                        ?>
                        ��������ǡ���
                        <font size=2>
                        (ñ��:ʬ����)
                        </font>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>���롼��̾</th>
                    <th class='winbox' nowrap>�����ֹ�</th>
                    <th class='winbox' nowrap>�ʼ����</th>
                    <th class='winbox' nowrap>�ܲ�Ư����</th>
                    <th class='winbox' nowrap>������</th>
                    <th class='winbox' colspan='20' nowrap>�ɲá��ѹ�</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <span class='caption_font'>
                            <select name='group_no' size='1'>
                            <?php
                            $res_g = $result->get_array2('res_g');
                            for ($i=0; $i<$result->get('rows_g'); $i++) {
                                if ( $res_g[$i][0] == $request->get('group_no')) {
                                    printf("<option value='%s' selected>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                } else {
                                    printf("<option value='%s'>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                }
                            }
                            ?>
                            </select>
                        </span>
                    </td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='mac_no' value='<?php echo $request->get('mac_no') ?>' size='5' maxlength='4'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='setup_time' value='<?php echo $request->get('setup_time') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='operation_time' value='<?php echo $request->get('operation_time') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='repairing_expenses' value='<?php echo $request->get('repairing_expenses') ?>' size='10'></td>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='�ɲá��ѹ�'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='del' value='���'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                </tr>
             </table>
                </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </form>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '�Ȳ�') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assemblyRate_machineWork_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='80%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
