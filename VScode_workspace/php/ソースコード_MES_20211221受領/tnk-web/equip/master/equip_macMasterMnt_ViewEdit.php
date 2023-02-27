<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ޥ����� �� �Ȳ� �� ���ƥʥ�                               //
// Copyright (C) 2002-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
//              MVC View ��  �ѹ�(�Խ�)����                                 //
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
// 2005/06/28 MVC��View�����ѹ�  �Խ��ե�����                               //
// 2005/07/15 ../equipment.jp �� machineMaster.js ���ѹ�                    //
// 2005/08/19 �ڡ�������ǡ����� action=''��$model->get_htmlGETparm()���ղ� //
// 2005/09/18 �����ե�����ɤ��ѹ��ԲĤ� Controller �ȹ�碌���ѹ�          //
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
    document.edit_form.mac_name.focus();
    document.edit_form.mac_name.select();
}
// -->
</script>
<script language='JavaScript' src='machineMaster.js?<?php echo $uniq ?>'></script>
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
    <form name='edit_form' action='<%=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"%>' method='post' onSubmit='return chk_equip_mac_mst_mnt(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>
            <caption>�ޥ����� �Խ�</caption>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <th class='winbox' width='40'>1</th>
                <td class='winbox' align='left' nowrap>
                    �����ֹ�
                    <input type='text' name='mac_no' size='5' value='<%=$mac_no%>' maxlength='4' readonly style='background-color:#d6d3ce;'>
                    <input type='hidden' name='pmac_no' value='<%=$pmac_no%>'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>2</th>
                <td class='winbox' align='left' nowrap>
                    ����̾��
                    <input type='text' name='mac_name' size='24' value='<%=$mac_name%>' maxlength='20'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>3</th>
                <td class='winbox' align='left' nowrap>
                    �᡼��������
                    <input type='text' name='maker_name' size='24' value='<%=$maker_name%>' maxlength='20'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>4</th>
                <td class='winbox' align='left' nowrap>
                    �᡼����
                    <input type='text' name='maker' size='24' value='<%=$maker%>' maxlength='20'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>5</th>
                <td class='winbox' align='left' nowrap>
                    �����ʬ
                    <select name='factory'>
                        <?php if ($factoryList == 1) {?>
                        <option value='1'>������</option>
                        <?php } elseif ($factoryList == 2) {?>
                        <option value='2'>������</option>
                        <?php } elseif ($factoryList == 4) {?>
                        <option value='4'>������</option>
                        <?php } elseif ($factoryList == 5) {?>
                        <option value='5'>������</option>
                        <?php } elseif ($factoryList == 6) {?>
                        <option value='6'>������</option>
                        <?php } elseif ($factoryList == 7) {?>
                        <option value='7'>������(���)</option>
                        <?php } elseif ($factoryList == 8) {?>
                        <option value='8'>������(SUS)</option>
                        <?php } else {?>
                        <option value='1'<% if ($factory == 1) echo 'selected'%>>������</option>
                        <option value='2'<% if ($factory == 2) echo 'selected'%>>������</option>
                        <option value='4'<% if ($factory == 4) echo 'selected'%>>������</option>
                        <option value='5'<% if ($factory == 5) echo 'selected'%>>������</option>
                        <option value='6'<% if ($factory == 6) echo 'selected'%>>������</option>
                        <option value='7'<% if ($factory == 7) echo 'selected'%>>������(���)</option>
                        <option value='8'<% if ($factory == 8) echo 'selected'%>>������(SUS)</option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th class='winbox'>6</th>
                <td class='winbox' align='left' nowrap>
                    ͭ����̵��
                    <!-- <input type='text' name='survey' size='1' value='<%=$survey%>' maxlength='1' class='center'> -->
                    <select name='survey'>
                        <option value='Y'<% if ($survey == 'Y') echo 'selected'%>>ͭ��</option>
                        <option value='N'<% if ($survey == 'N') echo 'selected'%>>̵��</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th class='winbox'>7</th>
                <td class='winbox' align='left' nowrap>
                    ���󥿡��ե����� ����
                    <select name='csv_flg'>
                        <option value='0'<% if ($csv_flg == 0) echo 'selected'%>>�ʤ�</option>
                        <option value='1'<% if ($csv_flg == 1) echo 'selected'%>>Netmoni</option>
                        <option value='2'<% if ($csv_flg == 2) echo 'selected'%>>FWS1</option>
                        <option value='3'<% if ($csv_flg == 3) echo 'selected'%>>FWS2</option>
                        <option value='4'<% if ($csv_flg == 4) echo 'selected'%>>FWS3</option>
                        <option value='5'<% if ($csv_flg == 5) echo 'selected'%>>FWS4</option>
                        <option value='6'<% if ($csv_flg == 6) echo 'selected'%>>FWS5</option>
                        <option value='7'<% if ($csv_flg == 7) echo 'selected'%>>FWS6</option>
                        <option value='8'<% if ($csv_flg == 8) echo 'selected'%>>FWS7</option>
                        <option value='9'<% if ($csv_flg == 9) echo 'selected'%>>FWS8</option>
                        <option value='10'<% if ($csv_flg == 10) echo 'selected'%>>FWS9</option>
                        <option value='11'<% if ($csv_flg == 11) echo 'selected'%>>FWS10</option>
                        <option value='12'<% if ($csv_flg == 12) echo 'selected'%>>FWS11</option>
                        <option value='101'<% if ($csv_flg == 101) echo 'selected'%>>Net&FWS</option>
                        <option value='201'<% if ($csv_flg == 201) echo 'selected'%>>����¾</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th class='winbox'>8</th>
                <td class='winbox' align='left' nowrap>
                    ��ȶ� 101 401 501��
                    <input type='text' name='sagyouku' size='3' value='<%=$sagyouku%>' maxlength='3'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>9</th>
                <td class='winbox' align='left' nowrap>
                    ��������(KW)
                    <input type='text' name='denryoku' size='8' value='<%=$denryoku%>' maxlength='7' class='right'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>10</th>
                <td class='winbox' align='left' nowrap>
                    ���Ϸ���
                    <input type='text' name='keisuu' size='4' value='<%=$keisuu%>' maxlength='4' class='right'>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='2' align='center' nowrap>
                    <input type='submit' name='confirm_edit' value='�ѹ�' style='color:blue;'>
                    &nbsp;&nbsp;
                    <input type='button' name='cancel' value='���' onClick='document.cancel_form.submit()'>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='2' align='center' nowrap>
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
