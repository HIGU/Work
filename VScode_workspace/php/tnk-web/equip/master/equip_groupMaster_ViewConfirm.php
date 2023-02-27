<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械のグループ(工場)区分 マスター 照会＆メンテナンス               //
//              MVC View 部  確認画面(追加・変更・削除 共有)                //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/04 Created   equip_groupMaster_ViewConfirm.php                   //
// 2005/08/19 ページ制御データを action=''に$model->get_htmlGETparm()で付加 //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
    document.confirm_form.group_no.focus();
    // document.confirm_form.group_no.select();
}
// -->
</script>
<script type='text/javascript' src='groupMaster.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
    
    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
    <tr><td>
        <form action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' nowrap>
                        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                            <tr align='center'>
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
                            </tr>
                        </table>
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
        <form name='confirm_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post' onSubmit='return chk_groupMaster(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>
                <caption>工場区分(グループ) マスター 確認</caption>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' width='40'>1</th>
                    <td class='winbox' align='left' nowrap>
                        区分(グループコード)
                    </td>
                    <td class='winbox' align='left' nowrap>
                        <input type='text' name='group_no' size='5' value='<?=$group_no?>' maxlength='4' readonly style='text-align:right; background-color:#d6d3ce;'>
                        <input type='hidden' name='preGroup_no' size='5' value='<?=$preGroup_no?>'>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>2</th>
                    <td class='winbox' align='left' nowrap>
                        工場(グループ)名
                    </td>
                    <td class='winbox' align='left' nowrap>
                        <input type='text' name='group_name' size='24' value='<?=$group_name?>' maxlength='20' readonly style='background-color:#d6d3ce;'>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>6</th>
                    <td class='winbox' align='left' nowrap>
                        有効・無効
                    </td>
                    <td class='winbox' align='center' nowrap>
                        <select name='active'>
                            <% if ($active == 't') {%><option value='t'>有効</option><?php } ?>
                            <% if ($active == 'f') {%><option value='f'>無効</option><?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='3' align='center' nowrap>
                        <?php if ($current_menu == 'confirm_apend') { ?>
                        <input type='submit' name='apend' value='登録実行' style='color:blue;'>
                        &nbsp;&nbsp;
                        <input type='submit' name='cancel_apend' value='取消'>
                        <?php } elseif ($current_menu == 'confirm_edit') { ?>
                        <input type='submit' name='edit' value='変更実行' style='color:blue;'>
                        &nbsp;&nbsp;
                        <input type='submit' name='cancel_edit' value='取消'>
                        <?php } elseif ($current_menu == 'confirm_delete') { ?>
                        <input type='submit' name='delete' value='削除実行' style='color:red;' onClick='return confirm("削除したデータは元に戻せません。\n\n宜しいですか？")'>
                        &nbsp;&nbsp;
                        <input type='submit' name='cancel_del' value='取消'>
                        <?php } ?>
                    </td>
                </tr>
            </table>
                </td></tr> <!----------- ダミー(デザイン用) ------------>
            </table>
        </form>
        <form name='cancel_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&current_menu=list&id={$uniq}"?>' method='post'>
        </form>
    </td></tr>
    </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
