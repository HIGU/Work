<?php
//////////////////////////////////////////////////////////////////////////////
// ���ȥǡ����ɲá��Խ� View��                                            //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/14 Created  assemblyRate_manRate_View.php                        //
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

/* ���ϥǡ��������å� */
function chk_entry(obj) {
    if (obj.item.value.length == 0) {
        alert('�оݥ��롼�פ����Ϥ���Ƥ��ޤ���');
        obj.item.focus();
        obj.item.select();
        return false;
    }
    
    if(!obj.worker_time.value.length){
        alert("��Ȼ��֤����Ϥ���Ƥ��ޤ���");
        obj.worker_time.focus();
        obj.worker_time.select();
        return false;
    } else if(!(isDigit(obj.worker_time.value))){
        alert("��Ȼ��֤ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.worker_time.focus();
        obj.worker_time.select();
        return false;
    }
    
    if(!obj.assistance_time.value.length){
        alert("������֤����Ϥ���Ƥ��ޤ���");
        obj.assistance_time.focus();
        obj.assistance_time.select();
        return false;
    } else if(!(isDigit(obj.assistance_time.value))){
        alert("������֤ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.assistance_time.focus();
        obj.assistance_time.select();
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
    <form name='entry_form' method='post' action='assemblyRate_manRate_Main.php' onSubmit='return chk_entry(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td bgcolor='#ffffc6' align='center' colspan='20'>
                        <?php
                        echo format_date6_kan($request->get('wage_ym'));
                        ?>
                        ���� �ǡ���
                        <font size=2>
                        (ñ��:ʬ)
                        </font>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>�оݥ��롼��</th>
                    <th class='winbox' nowrap>��Ȼ���</th>
                    <th class='winbox' nowrap>�������</th>
                    <th class='winbox' colspan='20' nowrap>�ɲá��ѹ�</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='item' value='<?php echo $request->get('item') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='worker_time' value='<?php echo $request->get('worker_time') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='assistance_time' value='<?php echo $request->get('assistance_time') ?>' size='15'></td>
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assemblyRate_manRate_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='80%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
