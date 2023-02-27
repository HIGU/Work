<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械のカウンター マスター 照会＆メンテナンス                       //
//              MVC View 部     リスト表示                                  //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/16 Created   equip_counterMaster_ViewList.php                    //
//                              <a href='' にキーフィールドがある事に注意   //
// 2005/08/19 ページ制御データを<a href=''に$model->get_htmlGETparm()で付加 //
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
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
<script language='JavaScript' src='counterMaster.js?=<%= $uniq %>'></script>
</head>
<body onLoad='set_focus()'>
    <center>
<%= $menu->out_title_border() %>
    
    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
    <tr><td>
        <form action='<%=$menu->out_self(), "?id={$uniq}"%>' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' nowrap>
                        <span <?php if($current_menu=='apend') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                            <input type='radio' name='current_menu' value='apend' id='apend' onClick='submit()'
                            <?php if($current_menu=='apend') echo 'checked' ?>>
                            <label for='apend'>マスター追加
                        </span>
                    </td>
                    <td class='winbox' nowrap>
                        <span <?php if($current_menu=='list') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                            <input type='radio' name='current_menu' value='list' id='work' onClick='submit()'
                            <?php if($current_menu=='list') echo 'checked' ?>>
                            <label for='work'>マスター一覧
                        </span>
                    </td>
                    <td class='winbox' nowrap>
                        <%=$pageControll%>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
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
            <caption>ワークカウンター マスター 一覧</caption>
                <tr><td> <!-- ダミー -->
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <th class='winbox'>&nbsp;</th>
                <th class='winbox'>&nbsp;</th>
                <th class='winbox' nowrap>機械番号</th>
                <th class='winbox' nowrap>機械名称</th>
                <th class='winbox' nowrap>部品(製品)番号</th>
                <th class='winbox' nowrap>部品(製品)名</th>
                <th class='winbox' nowrap>カウンター</th>
                <th class='winbox' nowrap>登録日</th>
                <th class='winbox' nowrap>変更日</th>
            <?php for ($r=0; $r<$rows; $r++) { ?>
                <tr>
                <td class='winbox' align='right' nowrap><%=$r + 1 + $model->get_offset()%></td>
                <td class='winbox' align='center' nowrap>
                    <a href='<%=$menu->out_self(), "?mac_no={$res[$r][0]}&parts_no=", urlencode($res[$r][2]), "&current_menu=edit&", $model->get_htmlGETparm(), "&id={$uniq}"%>'
                        style='text-decoration:none;'>編集
                    </a>
                </td>
                <!-- 機械番号 -->
                <td class='winbox' align='center' nowrap><%=$res[$r][0]%></td>
                <!-- 機械名称 -->
                <td class='winbox' align='left' nowrap><%=$res[$r][1]%></td>
                <!-- 部品(製品)番号 -->
                <td class='winbox' align='center' nowrap><%=$res[$r][2]%></td>
                <!-- 部品(製品)名 -->
                <td class='winbox' align='center' nowrap><%=$res[$r][3]%></td>
                <!-- カウンター -->
                <td class='winbox' align='right' nowrap><%=$res[$r][4]%></td>
                <!-- 登録日 -->
                <td class='winbox' align='center' nowrap><%=$res[$r][5]%></td>
                <!-- 変更日 -->
                <td class='winbox' align='center' nowrap><%=$res[$r][6]%></td>
                </tr>
            <?php } ?>
            </table>
                </td></tr> <!-- ダミー -->
            </table>
        <?php } ?>
    </td></tr>
    </table>
    </center>
</body>
<%=$menu->out_alert_java()%>
</html>
