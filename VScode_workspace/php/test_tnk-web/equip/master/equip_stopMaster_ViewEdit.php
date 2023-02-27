<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械の停止の定義(ストップ) マスター 照会＆メンテナンス             //
//              MVC View 部  変更(編集)画面                                 //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/16 Created   equip_stopMaster_ViewEdit.php                       //
// 2005/08/19 ページ制御データを action=''に$model->get_htmlGETparm()で付加 //
// 2005/09/18 キーフィールドを変更不可へ Controller と合わせて変更          //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
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
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    document.edit_form.stop.focus();
    document.edit_form.stop.select();
}
// -->
</script>
<script language='JavaScript' src='stopMaster.js?<?php echo $uniq ?>'></script>
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
        font-size:          12pt;
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
    <form name='edit_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post' onSubmit='return chk_stopMaster(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>
            <caption>停止の定義 マスター 編集</caption>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <th class='winbox' width='40'>1</th>
                <td class='winbox' align='center' nowrap>
                    <span style='font-size:12pt; font-weight:bold;'>機械番号</span>
                </td>
                <td class='winbox' align='left' nowrap>
                    <!-- <input type='text' name='mac_no' size='5' value='<?=$mac_no?>' maxlength='4'> -->
                    <select name='mac_no' size='1'>
                    <?php
                        echo "\n";  // ソースを見やすくするため
                        for ($i=0; $i<$mac_cnt; $i++) {
                            if ($mac_no == $mac_no_name[$i][0]) {
                                echo "<option value='{$mac_no_name[$i][0]}' selected>\n";
                            } else {
                                // echo "<option value='{$mac_no_name[$i][0]}'>\n";
                            }
                            echo "    {$mac_no_name[$i][1]}\n";
                            echo "</option>\n";
                        }
                    ?>
                    </select>
                    <input type='hidden' name='preMac_no' size='5' value='<?=$preMac_no?>'>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='3' align='right' nowrap style='color:yellow; font-weight:bold;'>
                    <?=$mac_name?>
                </td>
            </tr>
            <tr>
                <th class='winbox'>2</th>
                <td class='winbox' align='left' nowrap>
                    部品(製品)番号
                    &nbsp;
                    <% if ($parts_no == '000000000') { %>
                    <input type='checkbox' name='def_box' onClick='edit_checkbox(this.checked)' checked disabled>
                    <% } else { %>
                    <input type='checkbox' name='def_box' onClick='edit_checkbox(this.checked)' disabled>
                    <% } %>
                    規定値
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='parts_no' size='11' value='<?=$parts_no?>' maxlength='9' readonly style='background-color:#d6d3ce;'>
                    <input type='hidden' name='preParts_no' size='11' value='<?=$preParts_no?>'>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='3' align='right' nowrap style='color:yellow; font-weight:bold;'>
                    <?=$parts_name?>
                </td>
            </tr>
            <tr>
                <th class='winbox'>3</th>
                <td class='winbox' align='left' nowrap>
                    停止と判断する時間(秒)
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='stop' size='5' value='<?=$stop?>' maxlength='4' style='text-align:right;'>
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
