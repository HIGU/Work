<?php
//////////////////////////////////////////////////////////////////////////////
// 生産システムの部品・製品関係のアイテムマスターの照会・メンテナンス       //
//      MVC View 部     追加フォーム    インクリメントサーチ対応            //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/14 Created   parts_item_ViewApend.php                            //
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
<link rel='stylesheet' href='parts_item.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_item.js?<?= $uniq ?>'></script>
</head>
<body onLoad='PartsItem.setFocus(document.apend_form.parts_no, "select")'>
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
                                部品番号
                            </span>
                            <input class='pt12b' type='text' name='partsKey' value='<?=$partsKey?>' maxlength='9' size='12' readonly style='text-align:left; background-color:#d6d3ce;'>
                        </td>
                    </form>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    <form name='apend_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post' onSubmit='return PartsItem.CheckItemMaster(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>
            <caption>製品・部品のアイテム マスター 追加</caption>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <th class='winbox' width='40'>1</th>
                <td class='winbox' align='left' nowrap>
                    部品・製品 番号
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='parts_no' size='12' value='<?=$parts_no?>' maxlength='9' style='text-align:left;'>
                    <input type='hidden' name='partsKey' value='<?=$partsKey?>'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>2</th>
                <td class='winbox' align='left' nowrap>
                    部品・製品 名称
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='parts_name' size='58' value='<?=$parts_name?>' maxlength='38'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>3</th>
                <td class='winbox' align='left' nowrap>
                    材　質
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='partsMate' size='24' value='<?=$partsMate?>' maxlength='14'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>4</th>
                <td class='winbox' align='left' nowrap>
                    親　機　種
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='partsParent' size='26' value='<?=$partsParent?>' maxlength='20'>
                </td>
            </tr>
            <tr>
                <th class='winbox'>5</th>
                <td class='winbox' align='left' nowrap>
                    AS登録日(手入力時はなし)
                </td>
                <td class='winbox' align='left' nowrap>
                    <input type='text' name='partsASReg' size='14' value='<?=$partsASReg?>' maxlength='10'>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='3' align='center' nowrap>
                    <input type='submit' name='confirm_apend' value='登録' style='color:blue;'>
                    &nbsp;&nbsp;
                    <input type='button' name='cancel' value='取消' onClick='document.cancel_form.submit()'>
                </td>
            </tr>
        </table>
            </td></tr> <!----------- ダミー(デザイン用) ------------>
        </table>
    </form>
    <form name='cancel_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&partsKey={$partsKey}", "&current_menu=list&id={$uniq}"?>' method='post'>
    </form>
</center>
</body>
<?=$menu->out_alert_java()?>
<script type='text/javascript'>
var G_incrementalSearch = false;
var G_UpperSwitch = "apend";
</script>
</html>
