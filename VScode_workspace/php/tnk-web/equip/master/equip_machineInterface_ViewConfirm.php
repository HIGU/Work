<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理の機械とインターフェースのリレーション 照会＆メンテナンス    //
//              MVC View 部  確認画面(追加・変更・削除 共有)                //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/16 Created   equip_machineInterface_ViewConfirm.php              //
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
    document.confirm_form.mac_no.focus();
    // document.confirm_form.mac_no.select();
}
// -->
</script>
<script language='JavaScript' src='machineInterface.js?<?php echo $uniq ?>'></script>
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
        <form name='confirm_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post' onSubmit='return chk_machineInterface(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>
                <caption>機械の使用インターフェース マスター 確認</caption>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' width='40'>1</th>
                    <td class='winbox' align='left' nowrap>
                        <span style='font-size:12pt; font-weight:bold;'>機械番号</span>
                    </td>
                    <td class='winbox' align='left' nowrap>
                        <select name='mac_no' size='1'>
                        <?php
                            for ($i=0; $i<$mac_cnt; $i++) {
                                if ($mac_no == $mac_no_name[$i][0]) {
                                    echo "<option value='{$mac_no_name[$i][0]}' selected>{$mac_no_name[$i][1]}</option>\n";
                                }
                            }
                        ?>
                        </select>
                        <input type='hidden' name='preMac_no' size='5' value='<?=$preMac_no?>'>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>2</th>
                    <td class='winbox' align='left' nowrap>
                        <span style='font-size:12pt; font-weight:bold;'>インターフェース</span>
                    </td>
                    <td class='winbox' align='left' nowrap>
                        <select name='inter' size='1'>
                        <?php
                            for ($i=0; $i<$inter_cnt; $i++) {
                                if ($interface == $inter_name[$i][0]) {
                                    echo "<option value='{$inter_name[$i][0]}' selected>{$inter_name[$i][1]}</option>\n";
                                }
                            }
                        ?>
                        </select>
                        <input type='hidden' name='preInterface' size='5' value='<?=$preInterface?>'>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>3</th>
                    <td class='winbox' align='left' nowrap>
                        <span style='font-size:12pt; font-weight:bold;'>ＣＳＶファイル入出力</span>
                    </td>
                    <td class='winbox' align='left' nowrap>
                        <select name='csv' size='1'>
                            <option value='<?=$csv?>' selected><?php if ($csv == 0) echo 'なし';elseif ($csv == 1) echo '出力'; else echo '入力';?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>4</th>
                    <td class='winbox' id='file_item' align='left' nowrap<?php if ($csv == 0) echo " style='color:gray;'"?>>
                        <span style='font-size:12pt; font-weight:bold;'>入出力ファイル名</span>
                    </td>
                    <td class='winbox' align='left' nowrap>
                        <input type='text' name='file_name' size='20' value='<?=$file_name?>' maxlength='20' readonly style='background-color:#d6d3ce;'>
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
