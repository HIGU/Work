<?php
//////////////////////////////////////////////////////////////////////////////
// �Ȳ��ѥ��롼�פ���Ͽ View��                                              //
// Copyright (C) 2009-2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/11/24 Created  product_serchMaster_View.php                         //
// 2009/11/26 �����Ȥ�����                                                //
// 2010/12/11 ��ʬ�॰�롼�פ��ɲ�                                          //
// 2011/11/10 ����ե������������롼���ֹ�ˤʤ�褦���ɲ�                  //
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
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.entry_form.group_no.focus();
    document.entry_form.group_no.select();
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
        <form name='entry_form' action='product_serchMaster_Main.php' method='post' onSubmit='return chk_entry(this)'>
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
                    $field = $result->get_array2('field');
                    for ($i=0; $i<$result->get('num'); $i++) {             // �ե�����ɿ�ʬ���֤�
                    ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
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
                    <td class='winbox' align='center'><input type='text' class='price_font' name='group_no' value='<?php echo $field = $request->get('group_no') ?>' size='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='group_name' value='<?php echo $field = $request->get('group_name') ?>' size='50'></td>
                    <td class='winbox' align='center'>
                        <span class='caption_font'>
                            <select name='top_code' size='1'>
                            <option value=' '>��ʬ��ʤ�</option>
                            <?php
                            $res_g = $result->get_array2('res_g');
                            for ($i=0; $i<$result->get('rows_g'); $i++) {
                                if ( $res_g[$i][0] == $request->get('top_code')) {
                                    printf("<option value='%s' selected>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                } else {
                                    printf("<option value='%s'>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                }
                            }
                            ?>
                            </select>
                        </span>
                    </td>
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
        ��������ʬ�॰�롼��̤��Ͽ
        <B><font color='red'>
        <?php
        echo $result->get('unreg_num');
        ?>
        </B></font>
        ��
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '�Ȳ�') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/product_serchMaster_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
