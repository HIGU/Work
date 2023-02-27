<?php
//////////////////////////////////////////////////////////////////////////////
// ������ɲá��Խ� View��                                                //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/07 Created  assemblyRate_capitalAsset_View.php                   //
// 2007/12/13 �嵭�����Ȥ˳�ĥ��.php�����äƤ��ʤ��Τ��ɲ�                //
// 2008/01/09 �ɲá�����������եǡ����μ����Ϥ����ɲ�                      //
// 2008/02/05 �����No.�η����9�夫��11����ѹ�XX-XX-XXX-X               //
//            (���֤θ�����б��ΰ�)                                      //
//            �����No.��Maxlength��11���ѹ����ե�����Υ�������Ĵ��      //
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
    if (obj.asset_no.value.length == 0) {
        alert('����񻺣Σ�.�����Ϥ���Ƥ��ޤ���');
        obj.asset_no.focus();
        obj.asset_no.select();
        return false;
    }
    
    if (obj.asset_no.value.length == 9) {
    } else {
        if (obj.asset_no.value.length == 11) {
        } else {
            alert('����񻺣Σ�.��XX-XX-XXX�Σ��夫XX-XX-XXX-X�Σ���������Ϥ��Ƥ���������');
            obj.asset_no.focus();
            obj.asset_no.select();
            return false;
        }
    }
    
    if (obj.asset_name.value.length == 0) {
        alert('�����̾�Τ����Ϥ���Ƥ��ޤ���');
        obj.asset_name.focus();
        obj.asset_name.select();
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
    <form name='entry_form' method='post' action='assemblyRate_capitalAsset_Main.php' onSubmit='return chk_entry(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>���롼��̾</th>
                    <th class='winbox' nowrap><font size =2>��No.</font><BR><font size=1>(XX-XX-XXX)</font></th>
                    <th class='winbox' nowrap>��̾��</th>
                    <th class='winbox' nowrap>�������</th>
                    <th class='winbox' nowrap>����ǯ��</th>
                    <th class='winbox' nowrap>����ǯ��</th>
                    <th class='winbox' nowrap>ǯ��Ψ</th>
                    <th class='winbox' nowrap>����ǯ��</th>
                    <th class='winbox' colspan='2' nowrap>�ɲá����</th>
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
                    <td class='winbox' align='center'><input type='text' class='price_font' name='asset_no' value='<?php echo $request->get('asset_no') ?>' size='14' maxlength='11'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='asset_name' value='<?php echo $request->get('asset_name') ?>' size='35'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='acquisition_money' value='<?php echo $request->get('acquisition_money') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='acquisition_date' value='<?php echo $request->get('acquisition_date') ?>' size='7' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='durable_years' value='<?php echo $request->get('durable_years') ?>' size='3' maxlength='3'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='annual_rate' value='<?php echo $request->get('annual_rate') ?>' size='6' maxlength='5'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='end_date' value='<?php echo $request->get('end_date') ?>' size='7' maxlength='6'></td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='�ɲá��ѹ�'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                    <td class='winbox' align='center'>
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assemblyRate_capitalAsset_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='80%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
