<?php
///////////////////////////////////////////////////////////////////////////////
// ���� ���ʥ��롼�ץ������Խ� View��                                        //
// ���ʥ��롼�סʾܺ١ˤθ����ѥ��롼������                                  //
// Copyright (C) 2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp                 //
// Changed history                                                           //
// 2011/05/31 Created  product_groupMaster_View2.php                         //
// 2011/11/10 ����ե��������������ѥ��롼�פˤʤ�褦���ɲ�                 //
//            �ޤ������롼�ץ����ɡ����롼��̾�˥ե����������ܤ�ʤ��褦�ɲ� //
///////////////////////////////////////////////////////////////////////////////
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
.rightb{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#e6e6e6';
}
.10pt{
    font:bold 10pt;
    font-family: monospace;
    background-color: '#e6e6e6';
}
-->
</style>
</head>
<body scroll=no>
    <center>
    <?php echo $menu->out_title_border() ?>
    <form name='entry_form' method='post' action='product_groupMaster_Main2.php' onSubmit='return chk_entry(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td bgcolor='#ffffc6' align='center' colspan='20'>
                        ���ʥ��롼�ץ����� �Ȳ��ѥ��롼���Խ�
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>���롼�ץ�����</th>
                    <th class='winbox' nowrap>���롼��̾</th>
                    <th class='winbox' nowrap>�����ѥ��롼��</th>
                    <th class='winbox' colspan='10' nowrap>�ѹ�</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='10pt' name='mhgcd' value='<?php echo $request->get('mhgcd') ?>' size='9' maxlength='8' readonly tabindex='-1'></td>
                    <td class='winbox' align='center'><input type='text' class='10pt' name='mhgnm' value='<?php echo $request->get('mhgnm') ?>' size='30' readonly tabindex='-1'></td>
                    <td class='winbox' align='center'>
                        <span class='caption_font'>
                            <select name='mhggp' size='1'>
                            <?php
                            $res_g = $result->get_array2('res_g');
                            for ($i=0; $i<$result->get('rows_g'); $i++) {
                                if ( $res_g[$i][0] == $request->get('mhggp')) {
                                    printf("<option value='%s' selected>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                } else {
                                    printf("<option value='%s'>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                }
                            }
                            ?>
                            <option value=' '>������</option>
                            </select>
                        </span>
                    </td>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='�ѹ�'>
                    </td>
                </tr>
             </table>
                </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </form>
    �����ѥ��롼��̤��Ͽ
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/product_groupMaster_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='�ꥹ��'>\n";
        echo "    ������ɽ�����Ƥ��ޤ���\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
