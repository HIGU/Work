<?php
//////////////////////////////////////////////////////////////////////////////
// 資材管理の部品出庫 着手・完了時間 集計用  MVC View 部                    //
//                                           出庫担当者 登録・編集・一覧表  //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/26 Created   parts_pickup_time_ViewUser.php                      //
// 2005/09/30 set_focus()メソッドにstatus Parameter 追加                    //
// 2005/10/04 出庫作業者の登録テーブルに有効・無効を追加  伴うメソッド追加  //
// 2005/10/24 style='ime-mode:disabled;' 誤ってIMEキーのONに対応のため追加  //
// 2005/11/12 onLoad=***.set_focus( の閉じ括弧の位置が間違っているのを修正  //
// 2005/11/23 ControlFormSubmit()メソッド 二重Submit対策で追加              //
// 2006/04/07 </label> が抜けていた４箇所を修正                             //
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
<link rel='stylesheet' href='parts_pickup_time.css?id=<%= $uniq %>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_pickup_time.js?<%= $uniq %>'></script>
</head>
<body onLoad='PartsPickupTime.set_focus(document.user_form.<%=$focus%>, "noSelect")'>
<center>
<%= $menu->out_title_border() %>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<%=$menu->out_self(), "?id={$uniq}"%>' method='post'>
            <td nowrap <?php if($current_menu=='apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupTime.ControlFormSubmit(document.ControlForm.elements["apend"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='apend' id='apend'
                <?php if($current_menu=='apend') echo 'checked' ?>>
                <label for='apend'>出庫着手入力</label>
            </td>
            <td nowrap <?php if($current_menu=='list') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupTime.ControlFormSubmit(document.ControlForm.elements["list"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='list' id='list'
                <?php if($current_menu=='list') echo 'checked' ?>>
                <label for='list'>出庫着手一覧</label>
            </td>
            <td nowrap <?php if($current_menu=='EndList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupTime.ControlFormSubmit(document.ControlForm.elements["EndList"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='EndList' id='EndList'
                <?php if($current_menu=='EndList') echo 'checked' ?>>
                <label for='EndList'>出庫完了一覧</label>
            </td>
            <td nowrap class='winbox'>
                <%=$pageControl%>
            </td>
            <td nowrap <?php if($current_menu=='user') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupTime.ControlFormSubmit(document.ControlForm.elements["user"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='user' id='user'
                <?php if($current_menu=='user') echo 'checked' ?>>
                <label for='user'>作業者登録</label>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    
    <div></div>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>出庫 作業者 の 登録</caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='user_form' action='<%=$menu->out_self(), "?id={$uniq}"%>' method='post' onSubmit='return PartsPickupTime.user_formCheck(this)'>
            <input type='hidden' name='current_menu' value='user'>
        <tr>
            <td class='winbox pt12b' nowrap>
                作業者の社員番号
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='user_id' value='<%=$user_id%>' size='10' maxlength='6'
                    style='ime-mode:disabled;' class='pt12b'
                    onChange='this.value=this.value.toUpperCase()'
                <%=$readonly%>
                >
            </td>
            <td class='winbox pt12b' nowrap>
                氏名
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='user_name' value='<%=$user_name%>' size='16' maxlength='8' class='pt12b'>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='userEdit' value='登録' class='pt12b'>
            </td>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>出庫 作業者 登録 一覧</caption>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>社員番号</th>
            <th class='winbox' nowrap>氏　名</th>
            <th class='winbox' nowrap>登録日時</th>
            <th class='winbox' nowrap>有効・無効</th>
            <th class='winbox' nowrap>有効・無効の切替</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr<?php if ($res[$r][3] == '無効') echo " style='color:gray;'"?>>
            <!-- No. -->
            <td class='winbox' align='right' nowrap><%=$r + 1 + $model->get_offset()%></td>
            <!-- 削除 -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<%=$menu->out_self(), "?user_id={$res[$r][0]}&current_menu=user&userOmit=go&user_name=", urlencode($res[$r][1]), '&', $model->get_htmlGETparm(), "&id={$uniq}"%>'
                style='text-decoration:none;'
                onClick='return confirm("実績データが一度もなければ削除しても問題ありませんが\n\nある場合は削除せず無効にして下さい。\n\n削除します宜しいですか？")'
                >
                    削除
                </a>
            </td>
            <!-- 変更 -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<%=$menu->out_self(), "?user_id={$res[$r][0]}&current_menu=user&userCopy=go&user_name=", urlencode($res[$r][1]), '&', $model->get_htmlGETparm(), "&id={$uniq}"%>'
                style='text-decoration:none;'
                >
                    変更
                </a>
            </td>
            <!-- 社員番号 -->
            <td class='winbox pt12b' align='right' nowrap><%=$res[$r][0]%></td>
            <!-- 氏名 -->
            <td class='winbox pt12b' align='left' nowrap><%=$res[$r][1]%></td>
            <!-- 登録日時 -->
            <td class='winbox pt12b' align='left' nowrap><%=$res[$r][2]%></td>
            <!-- 有効・無効 -->
            <td class='winbox pt12b' align='center' nowrap><%=$res[$r][3]%></td>
            <!-- 有効・無効の切替 -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<%=$menu->out_self(), "?user_id={$res[$r][0]}&current_menu=user&userActive=go&user_name=", urlencode($res[$r][1]), '&', $model->get_htmlGETparm(), "&id={$uniq}"%>'
                style='text-decoration:none;'
                >
                    <?php if ($res[$r][3] == '有効') { ?>
                    無効にする
                    <?php } else { ?>
                    有効にする
                    <?php } ?>
                </a>
            </td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    <?php } ?>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '登録がありません！') { ?>
<%=$menu->out_alert_java()%>
<?php } ?>
</html>
