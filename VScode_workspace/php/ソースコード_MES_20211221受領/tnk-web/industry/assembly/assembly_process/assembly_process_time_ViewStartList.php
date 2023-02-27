<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�ؼ���˥塼�� ��ꡦ��λ���� ������  MVC View ��                    //
//                                              ��Ω������ɽ              //
// Copyright (C) 2005-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/18 Created   assembly_process_time_ViewStartList.php             //
// 2005/11/23 ControlFormSubmit()�᥽�å� ���Submit�к����ɲ�              //
// 2005/11/30 �ײ����ײ�Ĥ��ѹ��������ȼ�����֥륯��å������پȲ��ɲ�  //
// 2006/04/07 </label> ��ȴ���Ƥ������ս����                             //
// 2016/08/08 ��Ω���ųݰ�����mouseover���ɲ�                        ��ë //
// 2016/12/09 �����ֹ����Ŭ������ؤΥ�󥯤��ɲ�                   ��ë //
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
<body>
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
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>��Ω��� �ų� ����</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>�ײ��ֹ�</th>
            <th class='winbox' nowrap>�����ֹ�</th>
            <th class='winbox' nowrap>�����ʡ�̾</th>
            <th class='winbox' nowrap>�ײ��</th>
            <th class='winbox' nowrap>�Ұ��ֹ�</th>
            <th class='winbox' nowrap>��ȼ�</th>
            <th class='winbox' nowrap>��Ω���</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><%=$r + 1 + $model->get_offset()%></td>
            <!-- ��� -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<%=$menu->out_self(), "?serial_no={$res[$r][7]}&showMenu=StartList&deletePlan=go&plan_no={$res[$r][0]}&", $model->get_htmlGETparm(), "&id={$uniq}"%>'
                style='text-decoration:none;'
                onClick='return confirm("���μ�ä򤷤ޤ��������Ǥ�����")'
                onMouseover="status='��Ω ���μ�ä�Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='��Ω ���μ�ä�Ԥ��ޤ���'
                >
                    ���
                </a>
            </td>
            <!-- ��Ω��λ -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<%=$menu->out_self(), "?serial_no={$res[$r][7]}&showMenu=StartList&assyEnd=go&plan_no={$res[$r][0]}&", $model->get_htmlGETparm(), "&id={$uniq}"%>'
                style='text-decoration:none;'
                onMouseover="status='��Ω�δ�λ���Ϥ�Ԥ��ޤ���';return true;"
                onMouseout="status=''"
                title='��Ω�δ�λ���Ϥ�Ԥ��ޤ���'
                >
                    ��λ
                </a>
            </td>
            <!-- �ײ��ֹ� -->
            <td class='winbox pt12b' align='right' nowrap>
                <a
                href='<%=$menu->out_action('��������ɽ'), '?plan_no=', urlencode($res[$r][0]), "&id={$uniq}"%>'
                style='text-decoration:none;'
                onMouseover="status='���ηײ��ֹ�ΰ������ʹ���ɽ�˥����פ��ޤ���';return true;"
                onMouseout="status=''"
                title='���ηײ��ֹ�ΰ������ʹ���ɽ�˥����פ��ޤ���'
                >
                    <%=$res[$r][0]%>
                </a>
            </td>
            <!-- �����ֹ� -->
            <!-- ��Ŭ������¸�ߤ������󥯤��ɲ� -->
            <?php 
                $clame_flg = '';
                $assy_no = $res[$r][1];
                $query_g = "
                            SELECT  assy_no                 AS �����ֹ�     -- 0
                            ,   midsc                   AS ����̾       -- 1
                            ,   publish_date            AS ȯ����       -- 2
                            ,   publish_no              AS ȯ���ֹ�     -- 3
                            ,   claim_name              AS ��̾         -- 4
                            FROM
                                claim_disposal_details
                            LEFT OUTER JOIN
                                miitem
                            ON assy_no = mipn
                            WHERE assy_no LIKE '{$assy_no}%'
                            ORDER BY
                                mipn,publish_date
                ";
                $res_g = array();
                if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
                    $clame_flg = '';
                } else {
                    $clame_flg = '1';
                }
            ?>
            <?php if ($clame_flg == '1') { ?>
            <td class='winbox pt12b' align='left' nowrap>
                <a
                href='<%=$menu->out_action('��Ŭ������'), '?assy_no=', urlencode($res[$r][1]), "&various_referer=off&id={$uniq}"%>'
                style='text-decoration:none;'
                onMouseover="status='���������ֹ����Ŭ����������˥����פ��ޤ���';return true;"
                onMouseout="status=''"
                title='���������ֹ����Ŭ����������˥����פ��ޤ���'
                >
                <%=$res[$r][1]%>
                </a>
            </td>
            <?php } else { ?>
            <td class='winbox pt12b' align='left' nowrap><%=$res[$r][1]%></td>
            <?php } ?>
            <!-- ����̾ -->
            <td class='winbox pt12b' align='left' nowrap><%=$res[$r][2]%></td>
            <!-- �ײ�� -->
            <td class='winbox pt12b' align='right' nowrap onDblClick='alert("�ײ�ġ��ײ����\n\n<%=$res[$r][3]%>��<%=$res[$r][8]%>\n\n�Ǥ���")'>
                <%=$res[$r][3]%>
            </td>
            <!-- �Ұ��ֹ� -->
            <td class='winbox pt12b' align='center' nowrap><%=$res[$r][4]%></td>
            <!-- ��ȼ� -->
            <td class='winbox pt12b' align='left' nowrap><%=$res[$r][5]%></td>
            <!-- ��Ω������� -->
            <td class='winbox pt12b' align='center' nowrap><%=$res[$r][6]%></td>
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
