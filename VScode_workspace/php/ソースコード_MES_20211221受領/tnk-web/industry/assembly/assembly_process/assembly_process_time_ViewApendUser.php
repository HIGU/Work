<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�ؼ���˥塼�� ��ꡦ��λ���� ������  MVC View ��                    //
//                                             ��Ω��� ��ȼ� �ؼ�(�ܥ���) //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/18 Created   assembly_process_time_ViewApendUserID.php           //
// 2005/10/24 style='ime-mode:disabled;' ��ä�IME������ON���б��Τ����ɲ�  //
// 2005/11/23 ControlFormSubmit()�᥽�å� ���Submit�к����ɲ�              //
// 2005/12/01 ��ȼԤ����Ϥ���Ƥ��ʤ���зײ��ֹ�ؤΥܥ����ɽ�����ʤ�    //
// 2006/04/07 </label> ��ȴ���Ƥ������ս����                             //
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
<link rel='stylesheet' href='assembly_process_time.css?id=<%= $uniq %>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_process_time.js?<%= $uniq %>'></script>
</head>
<body onLoad='AssemblyProcessTime.set_focus(document.user_form.user_id, "select")'>
<center>
<%= $menu->out_title_border() %>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<%=$menu->out_self(), "?id={$uniq}"%>' method='post'>
            <td nowrap <?php if($showMenu=='apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='apend' id='apend'
                <?php if($showMenu=='apend') echo 'checked' ?>>
                <label for='apend'>��Ω�������</label>
            </td>
            <td nowrap <?php if($showMenu=='StartList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["StartList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='StartList' id='StartList'
                <?php if($showMenu=='StartList') echo 'checked' ?>>
                <label for='StartList'>��Ω������</label>
            </td>
            <td nowrap <?php if($showMenu=='EndList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["EndList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='EndList' id='EndList'
                <?php if($showMenu=='EndList') echo 'checked' ?>>
                <label for='EndList'>��Ω��λ����</label>
            </td>
            <td nowrap class='winbox'>
                <%=$pageControl%>
            </td>
            <td nowrap <?php if($showMenu=='group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='group' id='group'
                <?php if($showMenu=='group') echo 'checked' ?>>
                <label for='group'>���롼���Խ�</label>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
    
    <div class='caption_font'></div>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <caption>��Ω��� ��ȼ� �ؼ�</caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='user_form' action='<%=$menu->out_self(), "?id={$uniq}"%>' method='post' onSubmit='return AssemblyProcessTime.user_formCheck(this)'>
            <input type='hidden' name='showMenu' value='apend'>
        <tr>
            <td class='winbox pt12b' nowrap>
                �Ұ��ֹ�
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='user_id' value='<%=$user_id%>' size='8' maxlength='6'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                >
                <input type='hidden' name='apendUser' value='dummy'>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='apendUser' value='��Ͽ' class='pt12b'>
            </td>
            <?php if ($userRows >= 1) { ?>
            <td class='winbox' nowrap>
                <input type='button' name='userEnd' value='�ײ��ֹ��' class='pt12b'
                    onClick='location.replace("<%=$menu->out_self(), "?showMenu=apend&userEnd=go&", $model->get_htmlGETparm(), "&id={$uniq}"%>")'
                >
            </td>
            <?php } ?>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
    
    <?php if ($userRows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>��Ω��� ��ȼ� �ؼ� ����</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>�Ұ��ֹ�</th>
            <th class='winbox' nowrap>��ȼ�</th>
            <th class='winbox' nowrap>��Ω���</th>
        <?php for ($r=0; $r<$userRows; $r++) { ?>
            <tr>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><%=$r + 1 + $model->get_offset()%></td>
            <!-- ��� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<%=$menu->out_self(), "?user_id={$userRes[$r][0]}&showMenu=apend&deleteUser=go&", $model->get_htmlGETparm(), "&id={$uniq}"%>'
                style='text-decoration:none;'
                onClick='return confirm("��ȼԤ����μ�ä򤷤ޤ��������Ǥ�����")'
                onMouseover="status='��Ω ��ȼԤ� ���μ�ä�Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='��Ω ��ȼԤ� ���μ�ä�Ԥ��ޤ���'
                >
                    ���
                </a>
            </td>
            <!-- �Ұ��ֹ� -->
            <td class='winbox pt12b' align='center' nowrap><%=$userRes[$r][0]%></td>
            <!-- ��ȼ� -->
            <td class='winbox pt12b' align='left' nowrap><%=$userRes[$r][1]%></td>
            <!-- ��Ω������� -->
            <td class='winbox pt12b' align='center' nowrap><%=$userRes[$r][2]%></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ���ߡ� -->
        </table>
    <?php } ?>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '��Ͽ������ޤ���') { ?>
<%=$menu->out_alert_java()%>
<?php } ?>
</html>
