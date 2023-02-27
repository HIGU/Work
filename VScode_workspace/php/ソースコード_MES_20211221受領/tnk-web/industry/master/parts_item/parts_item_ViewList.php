<?php
//////////////////////////////////////////////////////////////////////////////
// ���������ƥ�����ʡ����ʴط��Υ����ƥ�ޥ������ξȲ񡦥��ƥʥ�       //
//      MVC View ��     ����ɽ���ڤ��Խ����ʤ����� ���󥯥���ȥ������б� //
// Copyright (C) 2005-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/13 Created   parts_item_ViewList.php                             //
// 2005/09/14 Ajax�Τ����PartsItem.GpartsKey=.partsKey.value ��ǽ��Ԥ��ɲ�//
// 2005/09/20 NN7.1��Enter������submit�����뤿�� onChange='submit()'���ɲ�  //
// 2005/09/23 [���פ���ǡ����Ϥ���ޤ���] �Υ�å��������ɲ�               //
// 2005/09/26 �嵭��NN7.1�Ѥ� partsKey onChange= ��¾�ΰ��ƶ������뤿���� //
// 2009/07/24 �����ֹ������ˡ������ä��Ȥ��������б�                 ��ë //
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
<link rel='stylesheet' href='parts_item.css?id=<%= $uniq %>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_item.js?<%= $uniq %>'></script>
</head>
<body onLoad='PartsItem.setFocus(document.ControlForm.partsKey)'>
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
                    �����ֹ�
                </span>
                <input class='pt12b' type='text' name='partsKey' value='<%=$partsKey%>' maxlength='9' size='11'>
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
        <caption>���ʡ����� �Υ����ƥ� �ޥ����� ����</caption>
            <tr><td> <!-- ���ߡ� -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>�����ֹ�</th>
            <th class='winbox' nowrap>����̾��</th>
            <th class='winbox' nowrap>�ࡡ����</th>
            <th class='winbox' nowrap>�Ƶ���</th>
            <th class='winbox' nowrap>AS��Ͽ��</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <% if ($res[$r][2] != '̵��') { %>
            <tr>
            <% } else {%>
            <tr style='color:gray;'>
            <% } %>
            <td class='winbox' align='right' nowrap><%=$r + 1 + $model->get_offset()%></td>
            <td class='winbox' align='center' nowrap>
            <?php $res[$r][0] = str_replace('#', '���㡼��', $res[$r][0]); ?>
                <a href='<%=$menu->out_self(), "?parts_no={$res[$r][0]}&current_menu=edit&", $model->get_htmlGETparm(), "&partsKey={$partsKey}", "&id={$uniq}"%>'
                 style='text-decoration:none;'>
                    �Խ�
                </a>
            </td>
            <?php $res[$r][0] = str_replace('���㡼��', '#', $res[$r][0]); ?>
            <!-- ���ʡ����� �ֹ� -->
            <td class='winbox' align='center' nowrap><%=$res[$r][0]%></td>
            <!-- ���ʡ����� ̾�� -->
            <td class='winbox' align='left' nowrap><%=$res[$r][1]%></td>
            <!-- ��� -->
            <td class='winbox' align='left' nowrap><%=$res[$r][2]%></td>
            <!-- �Ƶ��� -->
            <td class='winbox' align='left' nowrap><%=$res[$r][3]%></td>
            <!-- AS��Ͽ�� -->
            <td class='winbox' align='center' nowrap><%=$res[$r][4]%></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ���ߡ� -->
        </table>
    <?php } elseif ($partsKey != '') { ?>
        <p>
        <div class='caption_font'>�嵭�������ֹ�˹��פ���ǡ����Ϥ���ޤ���</div>
        </p>
    <?php } else { ?>
        <p>
        <div class='caption_font'>�����ֹ���ˣ�ʸ�����Ϥ�����˸�����̤�ɽ�����ޤ���(���󥯥��󥿥륵����)</div>
        </p>
    <?php } ?>
    </span>
</center>
</body>
<%=$menu->out_alert_java()%>
<script type='text/javascript'>
PartsItem.GpartsKey = document.ControlForm.partsKey.value;
var G_incrementalSearch = true;
var G_UpperSwitch = "list";
</script>
</html>
