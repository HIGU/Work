<?php
//////////////////////////////////////////////////////////////////////////////
// �����������Υ����󥿡� �ޥ����� �Ȳ�����ƥʥ�                       //
//              MVC View ��  ��ǧ����(�ɲá��ѹ������ ��ͭ)                //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/16 Created   equip_counterMaster_ViewConfirm.php                 //
// 2005/08/19 �ڡ�������ǡ����� action=''��$model->get_htmlGETparm()���ղ� //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><%= $menu->out_title() %></title>
<%= $menu->out_site_java() %>
<%= $menu->out_css() %>

<style type='text/css'>
<!--
.center {
    text-align:         center;
}
.right {
    text-align:         right;
}
.left {
    text-align:         left;
}
.fc_yellow {
    color:              yellow;
    background-color:   blue;
}
.fc_red {
    color:              red;
    background-color:   blue;
}
.s_radio {
    color:              white;
    background-color:   blue;
    font-size:          11pt;
    font-weight:        bold;
}
.n_radio {
    font-size:          11pt;
}
-->
</style>
<script language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    document.confirm_form.mac_no.focus();
    // document.confirm_form.mac_no.select();
}
// -->
</script>
<script language='JavaScript' src='counterMaster.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='set_focus()'>
    <center>
<%= $menu->out_title_border() %>
    
    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
    <tr><td>
        <form action='<%=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"%>' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' nowrap>
                        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                            <tr align='center'>
                                <td class='winbox' nowrap>
                                    <span <?php if($current_menu=='apend') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                                        <input type='radio' name='current_menu' value='apend' id='apend' onClick='submit()'
                                        <?php if($current_menu=='apend') echo 'checked' ?>>
                                        <label for='apend'>�ޥ������ɲ�
                                    </span>
                                </td>
                                <td class='winbox' nowrap>
                                    <span <?php if($current_menu=='list') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                                        <input type='radio' name='current_menu' value='list' id='work' onClick='submit()'
                                        <?php if($current_menu=='list') echo 'checked' ?>>
                                        <label for='work'>�ޥ���������
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        <style type='text/css'>
        <!--
        th {
            font-size:          11pt;
            font-weight:        bold;
            color:              white;
            background-color:   teal;
        }
        td {
            font-size:          11pt;
            font-weight:        normal;
        }
        caption {
            font-size:          11pt;
            font-weight:        bold;
        }
        input {
            font-size:          11pt;
            font-weight:        bold;
        }
        select {
            background-color:   lightblue;
            color:              black;
            font-size:          11pt;
            font-weight:        bold;
        }
        a {
            color: blue;
        }
        a:hover {
            background-color: blue;
            color: white;
        }
        -->
        </style>
        <form name='confirm_form' action='<%=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"%>' method='post' onSubmit='return chk_counterMaster(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>
                <caption>��������󥿡� �ޥ����� ��ǧ</caption>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' width='40'>1</th>
                    <td class='winbox' align='left' nowrap>
                        �����ֹ�
                    </td>
                    <td class='winbox' align='left' nowrap>
                        <input type='text' name='mac_no' size='5' value='<%=$mac_no%>' maxlength='4' readonly style='background-color:#d6d3ce;'>
                        <input type='hidden' name='preMac_no' size='5' value='<%=$preMac_no%>'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='3' align='right' nowrap style='color:yellow; font-weight:bold;'>
                        <%=$mac_name%>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>2</th>
                    <td class='winbox' align='left' nowrap>
                        ����(����)�ֹ�
                    </td>
                    <td class='winbox' align='left' nowrap>
                        <input type='text' name='parts_no' size='11' value='<%=$parts_no%>' maxlength='9' readonly style='background-color:#d6d3ce;'>
                        <input type='hidden' name='preParts_no' size='11' value='<%=$preParts_no%>'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='3' align='right' nowrap style='color:yellow; font-weight:bold;'>
                        <%=$parts_name%>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>3</th>
                    <td class='winbox' align='left' nowrap>
                        ������ȡ���������󥿡�
                    </td>
                    <td class='winbox' align='left' nowrap>
                        <input type='text' name='count' size='4' value='<%=$count%>' maxlength='3' style='text-align:right;' readonly style='background-color:#d6d3ce;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='3' align='center' nowrap>
                        <?php if ($current_menu == 'confirm_apend') { ?>
                        <input type='submit' name='apend' value='��Ͽ�¹�' style='color:blue;'>
                        &nbsp;&nbsp;
                        <input type='submit' name='cancel_apend' value='���'>
                        <?php } elseif ($current_menu == 'confirm_edit') { ?>
                        <input type='submit' name='edit' value='�ѹ��¹�' style='color:blue;'>
                        &nbsp;&nbsp;
                        <input type='submit' name='cancel_edit' value='���'>
                        <?php } elseif ($current_menu == 'confirm_delete') { ?>
                        <input type='submit' name='delete' value='����¹�' style='color:red;' onClick='return confirm("��������ǡ����ϸ����᤻�ޤ���\n\n�������Ǥ�����")'>
                        &nbsp;&nbsp;
                        <input type='submit' name='cancel_del' value='���'>
                        <?php } ?>
                    </td>
                </tr>
            </table>
                </td></tr> <!----------- ���ߡ�(�ǥ�������) ------------>
            </table>
        </form>
        <form name='cancel_form' action='<%=$menu->out_self(), '?', $model->get_htmlGETparm(), "&current_menu=list&id={$uniq}"%>' method='post'>
        </form>
    </td></tr>
    </table>
    </center>
</body>
<%=$menu->out_alert_java()%>
</html>
