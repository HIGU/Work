<?php
//////////////////////////////////////////////////////////////////////////////
// �����������Υ��롼��(����)��ʬ �ޥ����� �Ȳ�����ƥʥ�               //
//              MVC View ��  �ѹ�(�Խ�)����                                 //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/04 Created   equip_groupMaster_ViewEdit.php                      //
// 2005/08/19 �ڡ�������ǡ����� action=''��$model->get_htmlGETparm()���ղ� //
// 2005/09/18 �����ե�����ɤ��ѹ��ԲĤ� Controller �ȹ�碌���ѹ�          //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
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
<script type='text/javascript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    document.edit_form.group_name.focus();
    document.edit_form.group_name.select();
}
// -->
</script>
<script type='text/javascript' src='groupMaster.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='set_focus()'>
<center>
<%= $menu->out_title_border() %>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <td class='winbox' align='center' nowrap>
                <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                    <tr align='center'>
                    <form action='<%=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"%>' method='post'>
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
                    </form>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
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
    <form name='edit_form' action='<%=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"%>' method='post' onSubmit='return chk_groupMaster(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>
            <caption>�����ʬ(���롼��) �ޥ����� �Խ�</caption>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <th class='winbox' width='40'>1</th>
                <td class='winbox' align='left' nowrap>
                    ��ʬ(���롼�ץ�����)
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='group_no' size='5' value='<%=$group_no%>' maxlength='4' style='text-align:right; background-color:#d6d3ce;' readonly>
                    <input type='hidden' name='preGroup_no' size='5' value='<%=$preGroup_no%>'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>2</th>
                <td class='winbox' align='left' nowrap>
                    ����(���롼��)̾
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='group_name' size='24' value='<%=$group_name%>' maxlength='20'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>3</th>
                <td class='winbox' align='left' nowrap>
                    ͭ����̵��
                </td>
                <td class='winbox' align='center' nowrap>
                    <select name='active'>
                        <option value='t'<% if ($active == 't') echo 'selected'%>>ͭ��</option>
                        <option value='f'<% if ($active == 'f') echo 'selected'%>>̵��</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='3' align='center' nowrap>
                    <input type='submit' name='confirm_edit' value='�ѹ�' style='color:blue;'>
                    &nbsp;&nbsp;
                    <input type='button' name='cancel' value='���' onClick='document.cancel_form.submit()'>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='3' align='center' nowrap>
                    <input type='submit' name='confirm_delete' value='���' style='color:red;'>
                </td>
            </tr>
        </table>
            </td></tr> <!----------- ���ߡ�(�ǥ�������) ------------>
        </table>
    </form>
    <form name='cancel_form' action='<%=$menu->out_self(), '?', $model->get_htmlGETparm(), "&current_menu=list&id={$uniq}"%>' method='post'>
    </form>
</center>
</body>
<%=$menu->out_alert_java()%>
</html>
