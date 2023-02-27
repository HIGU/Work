<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ����ޥ������ξȲ񡦥��ƥʥ�                                   //
//      MVC View ��     ����ɽ���ڤ��Խ����ʤ����� ���󥯥���ȥ������б� //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_input_ViewList.php                       //
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
<link rel='stylesheet' href='progMaster_input.css?id=<%= $uniq %>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='progMaster_input.js?<%= $uniq %>'></script>
</head>
<body onLoad='ProgMaster.setFocus(document.ControlForm.pidKey)'>
<center>
<%= $menu->out_title_border() %>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<%=$menu->out_self(), "?id={$uniq}"%>' method='post'>
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
                <span <?php if($current_menu=='list') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                    �ץ����̾
                </span>
                <input class='pt12b' type='text' name='pidKey' value='<%=$pidKey%>' maxlength='18' size='20' style='ime-mode:disabled;'>
            </td>
            <td class='winbox' nowrap>
                <%=$pageControll%>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
    <span id='showAjax'>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>�ץ���� �ޥ����� ����</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!--
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>�ץ����ID</th>
            <th class='winbox' nowrap>�ץ����̾</th>
            <th class='winbox' nowrap>�ǥ��쥯�ȥ�</th>
            <th class='winbox' nowrap>������</th>
            <th class='winbox' nowrap>DB����</th>
            -->
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <% if ($res[$r][2] != '̵��') { %>
            <tr>
            <% } else {%>
            <tr style='color:gray;'>
            <% } %>
            <td class='winbox' align='right' rowspan='5' nowrap><%=$r + 1 + $model->get_offset()%></td>
            <td class='winbox' align='center' rowspan='5' nowrap>
            <?php $res[$r][0] = str_replace('#', '���㡼��', $res[$r][0]); ?>
                <a href='<%=$menu->out_self(), "?pid={$res[$r][0]}&pdir={$res[$r][2]}&current_menu=edit&", $model->get_htmlGETparm(), "&pidKey={$pidKey}", "&id={$uniq}"%>'
                 style='text-decoration:none;'>
                    �Խ�
                </a>
            </td>
            <th class='winbox' nowrap>�ץ����ID</th>
            <th class='winbox' nowrap>�ץ����̾</th>
            <th class='winbox' nowrap>�ǥ��쥯�ȥ�</th>
            </tr>
            <% if ($res[$r][2] != '̵��') { %>
            <tr>
            <% } else {%>
            <tr style='color:gray;'>
            <% } %>
            <?php $res[$r][0] = str_replace('���㡼��', '#', $res[$r][0]); ?>
            <!-- �ץ����ID -->
            <td class='winbox' align='left' nowrap><B><%=$res[$r][0]%></B></td>
            <!-- �ץ����̾ -->
            <td class='winbox' align='left' nowrap><%=$res[$r][1]%></td>
            <!-- �ǥ��쥯�ȥ� -->
            <td class='winbox' align='left' nowrap><%=$res[$r][2]%></td>
            </tr>
            <% if ($res[$r][2] != '̵��') { %>
            <tr>
            <% } else {%>
            <tr style='color:gray;'>
            <% } %>
            <th class='winbox' colspan='2' nowrap>������</th>
            <th class='winbox' nowrap>DB����</th>
            </tr>
            <% if ($res[$r][2] != '̵��') { %>
            <tr>
            <% } else {%>
            <tr style='color:gray;'>
            <% } %>
            <!-- ������ -->
            <td class='winbox' align='left' colspan='2' nowrap><%=$res[$r][3]%></td>
            <?php
            $db_use = 0;
            for ($i=4; $i<16; $i++) {
                if ($res[$r][$i] != '') {
                    $db_use = 1;
                }
            }
            if ($db_use == 1) {
            $db_url = 'progMaster_input_db_detail.php?db1='. $res[$r][4] .'&db2='. $res[$r][5] .'&db3='. $res[$r][6] .'&db4='. $res[$r][7] .'&db5='. $res[$r][8] .'&db6='. $res[$r][9] .'&db7='. $res[$r][10] .'&db8='. $res[$r][11] .'&db9='. $res[$r][12] .'&db10='. $res[$r][13] .'&db11='. $res[$r][14] .'&db12='. $res[$r][15];
            ?>
            <!-- DB���� -->
            <td class='winbox' align='center' nowrap><a href='<?php echo $db_url ?>' onclick="ProgMaster.win_open('<?php echo $db_url ?>', 1000, 440); return false;" title='����å��ǻ��ѣģ¤ξܺ٤�ɽ�����ޤ���'>��</a></td>
            </tr>
            <?php
            } else {
            ?>
            <!-- DB̤���� -->
            <td class='winbox' align='center' nowrap>��</td>
            </tr>
            <?php
            }
            ?>
            <tr>
            <th class='winboxb' nowrap>��Ͽ����</th>
            <?php if ($res[$r][16] == ' ') { ?>
            <td class='winboxb'  colspan='2' align='left' nowrap>��</td>
            <% } else {%>
            <td class='winboxb'  colspan='2' align='left' nowrap>��<%=$res[$r][16]%>��</td>
            <% } %>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ���ߡ� -->
        </table>
    <?php } elseif ($pidKey != '') { ?>
        <p>
        <div class='caption_font'>�嵭�Υץ����̾�˹��פ���ǡ����Ϥ���ޤ���</div>
        </p>
    <?php } else { ?>
        <p>
        <div class='caption_font'>�ץ����̾��ˣ�ʸ�����Ϥ�����˸�����̤�ɽ�����ޤ���(���󥯥��󥿥륵����)</div>
        </p>
    <?php } ?>
    </span>
</center>
</body>
<%=$menu->out_alert_java()%>
<script type='text/javascript'>
ProgMaster.GpidKey = document.ControlForm.pidKey.value;
var G_incrementalSearch = true;
var G_UpperSwitch = "list";
</script>
</html>
