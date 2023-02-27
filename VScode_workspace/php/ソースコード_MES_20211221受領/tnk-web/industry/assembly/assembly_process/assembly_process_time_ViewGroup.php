<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�ؼ���˥塼�� ��ꡦ��λ���� ������  MVC View ��                    //
//                                 ��Ω���롼��(��ȶ�) ��Ͽ���Խ�������ɽ  //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/17 Created   assembly_process_time_ViewGroup.php                 //
// 2005/10/24 style='ime-mode:active;' ��group_name�ǻ��Ѥ������Ȥ��ˤ����� //
// 2005/11/23 ControlFormSubmit()�᥽�å� ���Submit�к����ɲ�              //
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
<body onLoad='AssemblyProcessTime.set_focus(document.group_form.<%=$focus%>), "noSelect"'>
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
    
    <div></div>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <caption>��Ω���롼��(��ȶ�) �� ��Ͽ</caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='group_form' action='<%=$menu->out_self(), "?id={$uniq}"%>' method='post' onSubmit='return AssemblyProcessTime.group_formCheck(this)'>
        <input type='hidden' name='showMenu' value='group'>
        <th class='winbox pt12b' nowrap>
            ���롼���ֹ�
        </th>
        <th class='winbox pt12b' nowrap>
            ���롼��(��ȶ�)̾��
        </th>
        <th class='winbox pt12b' nowrap>
            ������
        </th>
        <th class='winbox pt12b' nowrap>
            ���ʥ��롼��
        </th>
        <th class='winbox pt12b' nowrap>
            &nbsp;
        </th>
        <tr>
            <td class='winbox' nowrap align='center'>
                <input type='text' name='Ggroup_no' value='<%=$Ggroup_no%>' size='3' maxlength='3'
                    style='ime-mode:disabled;' class='pt12b' onChange='this.value=this.value.toUpperCase()'
                    <%=$readonly%>
                >
            </td>
            <td class='winbox' nowrap align='center'>
                <input type='text' name='group_name' value='<%=$group_name%>' size='18' maxlength='10'
                    class='pt12b'
                >
            </td>
            <td class='winbox' nowrap align='center'>
                <select name='div' size='1'>
                    <option value='C'<?php if ($div=='C') echo ' selected'?>>���ץ�</option>
                    <option value='L'<?php if ($div=='L') echo ' selected'?>>��˥�</option>
                </select>
            </td>
            <td class='winbox' nowrap align='center'>
                <select name='product' size='1'>
                    <option value='C'<?php if ($product=='C') echo ' selected'?>>���ץ�ɸ��</option>
                    <option value='S'<?php if ($product=='S') echo ' selected'?>>���ץ�����</option>
                    <option value='L'<?php if ($product=='L') echo ' selected'?>>��˥�����</option>
                    <option value='B'<?php if ($product=='B') echo ' selected'?>>�Х����</option>
                </select>
                <input type='hidden' name='active' value='t'>
            </td>
            <td class='winbox' nowrap align='center'>
                <input type='submit' name='groupEdit' value='��Ͽ' class='pt12b'>
            </td>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>��Ω���롼��(��ȶ�) ��Ͽ ����</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>���롼���ֹ�</th>
            <th class='winbox' nowrap>���롼��(��ȶ�)̾��</th>
            <th class='winbox' nowrap>������</th>
            <th class='winbox' nowrap>���ʶ�ʬ</th>
                <!-- <th class='winbox' nowrap>��Ͽ����</th> -->
            <th class='winbox' nowrap>ͭ����̵��</th>
            <th class='winbox' nowrap>ͭ����̵��������</th>
            <th class='winbox' nowrap>���ߤΥ��롼��</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr<?php if ($res[$r][5] == '̵��') echo " style='color:gray;'"?>>
            <!-- No. -->
            <td class='winbox' align='right' nowrap><%=$r + 1 + $model->get_offset()%></td>
            <!-- ��� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<%=$menu->out_self(), "?Ggroup_no={$res[$r][0]}&showMenu=group&groupOmit=go&group_name=", urlencode($res[$r][1]), '&', $model->get_htmlGETparm(), "&id={$uniq}"%>'
                style='text-decoration:none;'
                onMouseover="status='��Ω(���롼��)��ȶ�κ����Ԥ��ޤ������Ӥ�����������Ф˺������̵���ˤ��Ʋ�������';return true;"
                onMouseout="status=''"
                title='��Ω(���롼��)��ȶ�κ����Ԥ��ޤ������Ӥ�����������Ф˺������̵���ˤ��Ʋ�������'
                onClick='return confirm("���ӥǡ��������٤�ʤ���к�����Ƥ����ꤢ��ޤ���\n\n������Ϻ������̵���ˤ��Ʋ�������\n\n������ޤ��������Ǥ�����")'
                >
                    ���
                </a>
            </td>
            <!-- �ѹ� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<%=$menu->out_self(), "?Ggroup_no={$res[$r][0]}&showMenu=group&groupCopy=go&group_name=", urlencode($res[$r][1]), "&div={$res[$r][6]}&product={$res[$r][7]}&", $model->get_htmlGETparm(), "&id={$uniq}"%>'
                style='text-decoration:none;'
                onMouseover="status='��Ͽ���Ƥ��Խ���Ԥ��ޤ����ѹ���̵����м¹Ԥ��Ƥ���Ͽ����ޤ���';return true;"
                onMouseout="status=''"
                title='��Ͽ���Ƥ��Խ���Ԥ��ޤ����ѹ���̵����м¹Ԥ��Ƥ���Ͽ����ޤ���'
                >
                    �ѹ�
                </a>
            </td>
            <!-- ���롼���ֹ� -->
            <td class='winbox pt12b' align='right' nowrap><%=$res[$r][0]%>&nbsp;&nbsp;</td>
            <!-- ���롼��(��ȶ�)̾�� -->
            <td class='winbox pt12b' align='left' nowrap><%=$res[$r][1]%></td>
            <!-- ������ -->
            <td class='winbox pt12b' align='left' nowrap><%=$res[$r][2]%></td>
            <!-- ���ʶ�ʬ -->
            <td class='winbox pt12b' align='left' nowrap><%=$res[$r][3]%></td>
            <!-- ��Ͽ���� -->
                <!-- <td class='winbox pt12b' align='center' nowrap><%=$res[$r][4]%></td> -->
            <!-- ͭ����̵�� -->
            <td class='winbox pt12b' align='center' nowrap><%=$res[$r][5]%></td>
            <!-- ͭ����̵�������� -->
            <td class='winbox pt12b' align='center' nowrap>
                <?php if ($res[$r][0] == $group_no) { ?>
                �Ǥ��ޤ���
                <?php } else { ?>
                <a
                href='<%=$menu->out_self(), "?Ggroup_no={$res[$r][0]}&showMenu=group&groupActive=go&group_name=", urlencode($res[$r][1]), '&', $model->get_htmlGETparm(), "&id={$uniq}"%>'
                style='text-decoration:none;'
                onMouseover="status='��Ω(���롼��)��ȶ��ͭ����̵�������ؤ��ޤ���';return true;"
                onMouseout="status=''"
                title='��Ω(���롼��)��ȶ��ͭ����̵�������ؤ��ޤ���'
                >
                    <?php if ($res[$r][5] == 'ͭ��') { ?>
                    ̵���ˤ���
                    <?php } else { ?>
                    ͭ���ˤ���
                    <?php } ?>
                </a>
                <?php } ?>
            </td>
            <!-- ���ߤΥ��롼�� -->
            <td class='winbox pt11' align='center' nowrap>
                <?php if ($res[$r][5] == '̵��') { ?>
                �Ǥ��ޤ���
                <?php } else { ?>
                <a
                href='javascript:AssemblyProcessTime.groupChange("<?=$res[$r][0]?>", "<%=$menu->out_self(), "?showMenu=group&", $model->get_htmlGETparm(), "&id={$uniq}"%>")'
                style='text-decoration:none;'
                onMouseover="status='���Υѥ��������Ω(���롼��)��ȶ������򤳤Υ��롼�פ����ؤ��ޤ���';return true;"
                onMouseout="status=''"
                title='���Υѥ��������Ω(���롼��)��ȶ������򤳤Υ��롼�פ����ؤ��ޤ���'
                >
                    <?php if ($res[$r][0] == $group_no) { ?>
                    <span class='pt12b' style='color:red;'>��</span>
                    <?php } else { ?>
                    ���Υ��롼�פˤ���
                    <?php } ?>
                </a>
                <?php } ?>
            </td>
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
