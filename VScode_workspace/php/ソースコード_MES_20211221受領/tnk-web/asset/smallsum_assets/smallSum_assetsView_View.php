<?php
//////////////////////////////////////////////////////////////////////////////
// ���ۻ񻺴�����Ģ�ɲá��Խ� View�� smallSum_assetsView_View.php           //
// Copyright (C) 2010 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/10/05 Created  smallSum_assetsView_View.php                         //
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

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* ���ϥǡ����Υ����å� */
function chk_entry(obj) {
    if (obj.act_name.value.length == 0) {
        alert('��������̾�����Ϥ���Ƥ��ޤ���');
        obj.act_name.focus();
        obj.act_name.select();
        return false;
    }
    if (obj.assets_name.value.length == 0) {
        alert('����̾�����Ϥ���Ƥ��ޤ���');
        obj.assets_name.focus();
        obj.assets_name.select();
        return false;
    }
    if (obj.assets_model.value.length == 0) {
        alert('�᡼���������������Ϥ���Ƥ��ޤ���');
        obj.assets_model.focus();
        obj.assets_model.select();
        return false;
    }
    if ( !(isDigitDot(obj.acquisition_money.value)) ) {
        alert('������ۤϿ����ʳ����Ͻ���ޤ���');
        obj.acquisition_money.focus();
        obj.acquisition_money.select();
        return false;
    } else {
        if (obj.acquisition_money.value <= 0) {
            alert('������ۤϣ�����礭�����������Ϥ��Ʋ�������');
            obj.acquisition_money.focus();
            obj.acquisition_money.select();
            return false;
        }
    }
    
    if(!obj.acquisition_date.value.length){
        alert("����ǯ����Ϥ���Ƥ��ޤ���");
        obj.acquisition_date.focus();
        obj.acquisition_date.select();
        return false;
    } else if(!(isDigit(obj.acquisition_date.value))){
        alert("����ǯ��ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.acquisition_date.focus();
        obj.acquisition_date.select();
        return false;
    }
    
    if (obj.acquisition_date.value.length == 6) {
    } else {
        alert('����ǯ���YYYYMM�Σ�������Ϥ��Ƥ���������');
        obj.acquisition_date.focus();
        obj.acquisition_date.select();
        return false;
    }
    
    if ( !(isDigit(obj.durable_years.value)) ) {
        alert('����ǯ���Ͽ����ʳ����Ͻ���ޤ���');
        obj.durable_years.focus();
        obj.durable_years.select();
        return false;
    } else {
        if (obj.durable_years.value <= 0) {
            alert('����ǯ���ϣ�����礭�����������Ϥ��Ʋ�������');
            obj.durable_years.focus();
            obj.durable_years.select();
            return false;
        }
    }
    
    if ( !(isDigitDot(obj.annual_rate.value)) ) {
        alert('ǯ��Ψ�Ͽ����ʳ����Ͻ���ޤ���');
        obj.annual_rate.focus();
        obj.annual_rate.select();
        return false;
    } else {
        if (obj.annual_rate.value <= 0) {
            alert('ǯ��Ψ�ϣ�����礭�����������Ϥ��Ʋ�������');
            obj.annual_rate.focus();
            obj.annual_rate.select();
            return false;
        }
    }
    
    if(obj.end_date.value.length == 0){
        return true;
    } else {
        if(!(isDigit(obj.end_date.value))){
            alert("����ǯ��ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
            obj.end_date.focus();
            obj.end_date.select();
            return false;
        }
        if (obj.end_date.value.length == 6) {
        } else {
            alert('����ǯ���YYYYMM�Σ�������Ϥ��Ƥ���������');
            obj.end_date.focus();
            obj.end_date.select();
            return false;
        }
        return true;
    }
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
    <form name='entry_form' method='post' action='smallSum_assetsView_Main.php'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>��������̾</th>
                    <th class='winbox' nowrap>���־��</th>
                    <th class='winbox' nowrap>����̾</th>
                    <th class='winbox' nowrap><font size =2>�᡼����������</font></th>
                    <th class='winbox' nowrap><font size =2>���Ѥ�ޤ�</font></th>
                    <td class='winbox' align='center' rowspan='2'>
                        <input type='submit' class='entry_font' name='search' value='����'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <select name='act_name' class='pt11b' size='1'>
                            <?php echo getActOptionsBody($request) ?>
                        </select>
                    </td>
                    <td class='winbox' align='center'>
                        <select name='set_place' class='pt11b' size='1'>
                            <?php echo getPlaceOptionsBody($request) ?>
                        </select>
                    </td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='assets_name' value='<?php echo $request->get('assets_name') ?>' size='30'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='assets_model' value='<?php echo $request->get('assets_model') ?>' size='30'></td>
                    <?php
                    if ($request->get('delete_in') == 'IN') {
                    ?>
                    <td class='winbox' align='center'><input type='checkbox' class='price_font' name='delete_in' value='IN' checked></td>
                    <?php } else { ?>
                    <td class='winbox' align='center'><input type='checkbox' class='price_font' name='delete_in' value='IN'></td>
                    <?php } ?>
                </tr>
             </table>
                </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </form>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '�Ȳ�') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/smallSum_assetsView_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='80%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
