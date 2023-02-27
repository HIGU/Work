<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ޥ����� �� �Ȳ� �� ���ƥʥ�                               //
//              MVC View ��     �ꥹ��ɽ��                                  //
// Copyright (C) 2002-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/03/13 Created   equip_macMasterMnt_ViewList.php                     //
// 2002/08/08 register_globals = Off �б�                                   //
// 2003/06/17 servey(�ƻ�ե饰) Y/N ���ѹ��Ǥ��ʤ��Զ����� �ڤ�        //
//              �����ϥե������ץ�����󼰤��ѹ�                          //
// 2003/06/19 $uniq = uniqid('script')���ɲä��� JavaScript File��ɬ���ɤ�  //
// 2004/03/04 ���ǥơ��֥� equip_machine_master2 �ؤ��б�                   //
// 2004/07/12 Netmoni & FWS ���������� �����å����� ���Τ��� Net&FWS�����ɲ�//
//            CSV ������������ �ƻ������� ����̾�ѹ�                        //
// 2005/02/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/06/24 �ǥ��쥯�ȥ��ѹ� equip/ �� equip/master/                      //
// 2005/06/28 MVC��View�����ѹ�  Listɽ�� equip_macMasterMnt_ListView.php   //
// 2005/07/15 ../equipment.jp �� machineMaster.js ���ѹ�                    //
// 2005/08/19 �ڡ�������ǡ�����<a href=''��$model->get_htmlGETparm()���ղ� //
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
// 2018/12/25 �������﫤�SUS��ʬΥ���塹�ΰ١�                      ��ë //
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
}
// -->
</script>
<script language='JavaScript' src='machineMaster.js?<?php echo $uniq ?>'></script>
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
            <caption>�ޥ����� ����</caption>
                <tr><td> <!-- ���ߡ� -->
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <th class='winbox'>&nbsp;</th>
                <th class='winbox'>&nbsp;</th>
                <th class='winbox' nowrap>�����ֹ�</th>
                <th class='winbox' width='80'>����̾��</th>
                <th class='winbox' nowrap>�᡼��������</th>
                <th class='winbox' nowrap>�᡼����̾</th>
                <th class='winbox' nowrap>�����ʬ</th>
                <th class='winbox' nowrap>ͭ��</th>
                <th class='winbox' nowrap>Interface</th>
                <th class='winbox' nowrap>��ȶ�</th>
                <th class='winbox' nowrap>��������</th>
                <th class='winbox' nowrap>���Ϸ���</th>
            <?php for ($r=0; $r<$rows; $r++) { ?>
                <tr>
                <td class='winbox' align='right' nowrap><%=$r + 1 + $model->get_offset()%></td>
                <td class='winbox' align='center' nowrap><a href='<%=$menu->out_self(), "?mac_no={$res[$r][0]}&current_menu=edit&", $model->get_htmlGETparm(), "&id={$uniq}"%>'
                    style='text-decoration:none;'>�Խ�</a></td>
                <td class='winbox' align='center' nowrap><%=$res[$r][0]%></td>
                <td class='winbox' align='left' nowrap><%=$res[$r][1]%></td>
                <td class='winbox' align='left' nowrap><%=$res[$r][2]%></td>
                <td class='winbox' align='left' nowrap><%=$res[$r][3]%></td>
                <?php if ($res[$r][4] == 1) {?>
                <td class='winbox' align='center' nowrap>������</td>
                <?php } elseif ($res[$r][4] == 2) {?>
                <td class='winbox' align='center' nowrap>������</td>
                <?php } elseif ($res[$r][4] == 4) {?>
                <td class='winbox' align='center' nowrap>������</td>
                <?php } elseif ($res[$r][4] == 5) {?>
                <td class='winbox' align='center' nowrap>������</td>
                <?php } elseif ($res[$r][4] == 6) {?>
                <td class='winbox' align='center' nowrap>������</td>
                <?php } elseif ($res[$r][4] == 7) {?>
                <td class='winbox' align='center' nowrap>������(���)</td>
                <?php } elseif ($res[$r][4] == 8) {?>
                <td class='winbox' align='center' nowrap>������(SUS)</td>
                <?php } ?>
                <!-- ͭ����̵�� -->
                <td class='winbox' align='center' nowrap><%=$res[$r][5]%></td>
                <!-- ���󥿡��ե����� -->
                <td class='winbox' align='center' nowrap><%=$res[$r][6]%></td>
                <!-- ��ȶ� -->
                <td class='winbox' align='center' nowrap><%=$res[$r][7]%></td>
                <td class='winbox' align='right' nowrap><%=$res[$r][8]%></td>
                <td class='winbox' align='right' nowrap><%=$res[$r][9]%></td>
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
