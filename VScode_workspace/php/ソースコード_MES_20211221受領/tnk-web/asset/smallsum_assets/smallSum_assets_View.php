<?php
//////////////////////////////////////////////////////////////////////////////
// ���ۻ񻺴�����Ģ�ɲá��Խ� View�� smallSum_assets_View.php               //
// Copyright (C) 2010 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/10/05 Created  smallSum_assets_View.php                             //
// 2010/10/15 ���̤��Ϥ߽Ф�Τ��礭����Ĵ��                                //
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
        alert('��������̾�����򤷤Ƥ���������');
        obj.act_name.focus();
        obj.act_name.select();
        return false;
    }
    if (obj.set_place.value.length == 0) {
        alert('���־������򤷤Ƥ���������');
        obj.set_place.focus();
        obj.set_place.select();
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
    
    if(!obj.buy_ym.value.length){
        alert("����ǯ���������Ϥ���Ƥ��ޤ���");
        obj.buy_ym.focus();
        obj.buy_ym.select();
        return false;
    } else if(!(isDigit(obj.buy_ym.value))){
        alert("����ǯ�����ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.buy_ym.focus();
        obj.buy_ym.select();
        return false;
    }
    
    if ( !(isDigitDot(obj.buy_price.value)) ) {
        alert('������ۤϿ����ʳ����Ͻ���ޤ���');
        obj.buy_price.focus();
        obj.buy_price.select();
        return false;
    } else {
        if (obj.buy_price.value < 0) {
            alert('������ۤϣ�����礭�����������Ϥ��Ʋ�������');
            obj.buy_price.focus();
            obj.buy_price.select();
            return false;
        }
    }
    
    if (obj.buy_ym.value.length == 8) {
    } else {
        alert('����ǯ������YYYYMMDD��8������Ϥ��Ƥ���������');
        obj.buy_ym.focus();
        obj.buy_ym.select();
        return false;
    }
    
    if(obj.delete_ym.value.length == 0){
        return true;
    } else {
        if(!(isDigit(obj.delete_ym.value))){
            alert("����ǯ��ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
            obj.delete_ym.focus();
            obj.delete_ym.select();
            return false;
        }
        if (obj.delete_ym.value.length == 8) {
        } else {
            alert('����ǯ���YYYYMMDD�Σ�������Ϥ��Ƥ���������');
            obj.delete_ym.focus();
            obj.delete_ym.select();
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
    <form name='entry_form' method='post' action='smallSum_assets_Main.php' onSubmit='return chk_entry(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>��������̾</th>
                    <th class='winbox' nowrap>���־��</th>
                    <th class='winbox' colspan='3' nowrap>����̾</th>
                    <th class='winbox' nowrap><font size =2>�᡼����������</font></th>
                    <th class='winbox' nowrap>����ǯ����</th>
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
                    <td class='winbox' colspan='3' align='center'><input type='text' class='price_font' name='assets_name' value='<?php echo $request->get('assets_name') ?>' size='50'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='assets_model' value='<?php echo $request->get('assets_model') ?>' size='30'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='buy_ym' value='<?php echo $request->get('buy_ym') ?>' size='9' maxlength='8'></td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>��������</th>
                    <th class='winbox' nowrap><font size =2>����ǯ����</font></th>
                    <th class='winbox' colspan='3' nowrap>����</th>
                    <th class='winbox' colspan='2' nowrap>�ɲ�/�ѹ�/���</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='buy_price' value='<?php echo $request->get('buy_price') ?>' size='9' maxlength='8'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='delete_ym' value='<?php echo $request->get('delete_ym') ?>' size='9' maxlength='8'></td>
                    <td class='winbox' colspan='3' align='center'><input type='text' class='price_font' name='note' value='<?php echo $request->get('note') ?>' size='50'></td>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='�ɲ�'>
                    &nbsp;&nbsp;
                        <input type='submit' class='entry_font' name='change' value='�ѹ�'>
                    &nbsp;&nbsp;
                        <input type='submit' class='entry_font' name='del' value='���'>
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/smallSum_assets_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='60%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
