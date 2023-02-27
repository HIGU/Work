<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械のインターフェースマスター 照会＆メンテナンス                  //
//              MVC View 部  変更(編集)画面                                 //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/14 Created   equip_interfaceMaster_ViewEdit.php                  //
// 2005/07/15 ../equipment.js → interfaceMaster.jp へ変更                  //
// 2005/08/03 interface は JavaScript の予約語(NN7.1)なので inter へ変更    //
// 2005/08/19 ページ制御データを action=''に$model->get_htmlGETparm()で付加 //
// 2005/09/18 キーフィールドを変更不可へ Controller と合わせて変更          //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

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
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    document.edit_form.host.focus();
    document.edit_form.host.select();
}
// -->
</script>
<script type='text/javascript' src='interfaceMaster.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='set_focus()'>
<center>
<?= $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <td class='winbox' align='center' nowrap>
                <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                    <tr align='center'>
                    <form action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post'>
                        <td class='winbox' nowrap>
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
                    </form>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
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
    <form name='edit_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post' onSubmit='return chk_interfaceMaster(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>
            <caption>インターフェース マスター 編集</caption>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <th class='winbox' width='40'>1</th>
                <td class='winbox' align='left' nowrap>
                    インターフェース
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='inter' size='5' value='<?=$interface?>' maxlength='4' style='text-align:right; background-color:#d6d3ce;' readonly>
                    <input type='hidden' name='preInterface' value='<?=$preInterface?>'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>2</th>
                <td class='winbox' align='left' nowrap>
                    HOST名
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='host' size='24' value='<?=$host?>' maxlength='20'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>3</th>
                <td class='winbox' align='left' nowrap>
                    IP Address
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='ip_address' size='20' value='<?=$ip_address?>' maxlength='15'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>4</th>
                <td class='winbox' align='left' nowrap>
                    FTPユーザー
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='ftp_user' size='24' value='<?=$ftp_user?>' maxlength='20'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>5</th>
                <td class='winbox' align='left' nowrap>
                    パスワード
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='ftp_pass' size='24' value='<?=$ftp_pass?>' maxlength='20'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>6</th>
                <td class='winbox' align='left' nowrap>
                    有効・無効
                </td>
                <td class='winbox' align='center' nowrap>
                    <select name='ftp_active'>
                        <option value='t'<% if ($ftp_active == 't') echo 'selected'%>>有効</option>
                        <option value='f'<% if ($ftp_active == 'f') echo 'selected'%>>無効</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='3' align='center' nowrap>
                    <input type='submit' name='confirm_edit' value='変更' style='color:blue;'>
                    &nbsp;&nbsp;
                    <input type='button' name='cancel' value='取消' onClick='document.cancel_form.submit()'>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='3' align='center' nowrap>
                    <input type='submit' name='confirm_delete' value='削除' style='color:red;'>
                </td>
            </tr>
        </table>
            </td></tr> <!----------- ダミー(デザイン用) ------------>
        </table>
    </form>
    <form name='cancel_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&current_menu=list&id={$uniq}"?>' method='post'>
    </form>
</center>
</body>
<?=$menu->out_alert_java()?>
</html>
