<?php
//////////////////////////////////////////////////////////////////////////////
// �����������Υ��롼��(����)��ʬ �ޥ����� �Ȳ�����ƥʥ�               //
//              MVC View ��                                                 //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/04 Created   equip_groupMaster_ViewList.php                      //
//                              <a href='' �˥����ե�����ɤ�����������   //
// 2005/08/19 �ڡ�������ǡ�����<a href=''��$model->get_htmlGETparm()���ղ� //
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
}
// -->
</script>
<script type='text/javascript' src='groupMaster.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='set_focus()'>
    <center>
<%= $menu->out_title_border() %>
    
    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
    <tr><td>
        <form action='<%=$menu->out_self(), "?id={$uniq}"%>' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' nowrap>
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
                    <td class='winbox' nowrap>
                        <%=$pageControll%>
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
        <?php if ($rows >= 1) { ?>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <caption>�����ʬ(���롼��) �ޥ����� ����</caption>
                <tr><td> <!-- ���ߡ� -->
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <th class='winbox'>&nbsp;</th>
                <th class='winbox'>&nbsp;</th>
                <th class='winbox' nowrap>������</th>
                <th class='winbox' nowrap>����̾(���롼��̾)</th>
                <th class='winbox' nowrap>ͭ��/̵��</th>
                <th class='winbox' nowrap>��Ͽ��</th>
                <th class='winbox' nowrap>�ѹ���</th>
            <?php for ($r=0; $r<$rows; $r++) { ?>
                <% if ($res[$r][2] != '̵��') { %>
                <tr>
                <% } else {%>
                <tr style='color:gray;'>
                <% } %>
                <td class='winbox' align='right' nowrap><%=$r + 1 + $model->get_offset()%></td>
                <td class='winbox' align='center' nowrap><a href='<%=$menu->out_self(), "?group_no={$res[$r][0]}&current_menu=edit&", $model->get_htmlGETparm(), "&id={$uniq}"%>' style='text-decoration:none;'>�Խ�</a></td>
                <!-- ���롼�ץ����� -->
                <td class='winbox' align='right' nowrap><%=$res[$r][0]%></td>
                <!-- ����̾(���롼��̾) -->
                <td class='winbox' align='left' nowrap><%=$res[$r][1]%></td>
                <!-- ͭ����̵�� -->
                <td class='winbox' align='center' nowrap><%=$res[$r][2]%></td>
                <!-- ��Ͽ�� -->
                <td class='winbox' align='center' nowrap><%=$res[$r][3]%></td>
                <!-- �ѹ��� -->
                <td class='winbox' align='center' nowrap><%=$res[$r][4]%></td>
                </tr>
            <?php } ?>
            </table>
                </td></tr> <!-- ���ߡ� -->
            </table>
        <?php } ?>
    </td></tr>
    </table>
    </center>
</body>
<%=$menu->out_alert_java()%>
</html>
