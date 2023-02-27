<?php
//////////////////////////////////////////////////////////////////////////////
// プログラムマスターの照会・メンテナンス                                   //
//      MVC View 部  確認(共用)フォーム    インクリメントサーチ対応         //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_input_ViewConfirm.php                    //
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
<link rel='stylesheet' href='progMaster_input.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='progMaster_input.js?<?= $uniq ?>'></script>
</head>
<body onLoad='ProgMaster.setFocus(document.confirm_form.pid)'>
<center>
<?= $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <td class='winbox' align='center' nowrap>
                <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                    <tr align='center'>
                    <form name='ControlForm' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post'>
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
                        <td class='winbox' nowrap>
                            <span <?php if($current_menu=='list') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                                プログラム名
                            </span>
                            <input class='pt12b' type='text' name='pidKey' value='<?=$pidKey?>' maxlength='18' size='20' readonly style='text-align:left; background-color:#d6d3ce; ime-mode:disabled;'>
                        </td>
                    </form>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    <form name='confirm_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post' onSubmit='return ProgMaster.CheckItemMaster(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>
            <caption>プログラム マスター 確認b</caption>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <th class='winbox' width='40'>1</th>
                <td class='winbox' align='left' nowrap>
                    プログラムＩＤ
                </td>
                <td colspan='2' class='winbox' align='left' nowrap>
                    <input type='text' name='pid' size='52' value='123456' maxlength='50' readonly style='text-align:left; background-color:#d6d3ce; ime-mode:disabled;'>
                    <input type='hidden' name='prePid' value='<?=$prePid?>'>
                    <input type='hidden' name='pidKey' value='<?=$pidKey?>'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>2</th>
                <td class='winbox' align='left' nowrap>
                    プログラム名
                </td>
                <td colspan='2' class='winbox' align='left' nowrap>
                    <input type='text' name='pname' size='58' value='test' maxlength='56' readonly style='background-color:#d6d3ce;'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>3</th>
                <td class='winbox' align='left' nowrap>
                    ディレクトリ
                </td>
                <td colspan='2' class='winbox' align='left' nowrap>
                /home/www/html/tnk-web
                    <input type='text' name='pdir' size='52' value='C:test\\' maxlength='50' readonly style='background-color:#d6d3ce;'>
                    <input type='hidden' name='preDir' value='<?=$preDir?>'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>4</th>
                <td class='winbox' align='left' nowrap>
                    説明
                </td>
                <td colspan='2' class='winbox' align='left' nowrap>
                    <input type='text' name='pcomment' size='78' value='dummy' maxlength='76' readonly style='background-color:#d6d3ce;'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>5</th>
                <td class='winbox' align='left' nowrap>
                    使用ＤＢ
                </td>
                <td class='winbox' align='left' nowrap>
                1.
                    <input type='text' name='db1' size='40' value='test db' maxlength='38' readonly style='background-color:#d6d3ce;'>
                </td>
                <td class='winbox' align='left' nowrap>
                2.
                    <input type='text' name='db2' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>　</th>
                <td class='winbox' align='left' nowrap>　</td>
                <td class='winbox' align='left' nowrap>
                3.
                    <input type='text' name='db3' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
                <td class='winbox' align='left' nowrap>
                4.
                    <input type='text' name='db4' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>　</th>
                <td class='winbox' align='left' nowrap>　</td>
                <td class='winbox' align='left' nowrap>
                5.
                    <input type='text' name='db5' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
                <td class='winbox' align='left' nowrap>
                6.
                    <input type='text' name='db6' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>　</th>
                <td class='winbox' align='left' nowrap>　</td>
                <td class='winbox' align='left' nowrap>
                7.
                    <input type='text' name='db7' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
                <td class='winbox' align='left' nowrap>
                8.
                    <input type='text' name='db8' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>　</th>
                <td class='winbox' align='left' nowrap>　</td>
                <td class='winbox' align='left' nowrap>
                9.
                    <input type='text' name='db9' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
                <td class='winbox' align='left' nowrap>
                10.
                    <input type='text' name='db10' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>　</th>
                <td class='winbox' align='left' nowrap>　</td>
                <td class='winbox' align='left' nowrap>
                11.
                    <input type='text' name='db11' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
                <td class='winbox' align='left' nowrap>
                12.
                    <input type='text' name='db12' size='40' value='test db' maxlength='38' readonly style='text-align:left; background-color:#d6d3ce;'>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='4' align='center' nowrap>
                    <?php
                    //$current_menu = 'confirm_delete';
                    if ($current_menu == 'confirm_apend') { ?>
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
    <form name='cancel_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&pidKey={$pidKey}", "&current_menu=list&id={$uniq}"?>' method='post'>
    </form>
</center>
</body>
<?=$menu->out_alert_java()?>
<script type='text/javascript'>
var G_incrementalSearch = false;
</script>
</html>
