<?php
//////////////////////////////////////////////////////////////////////////////
// 生産システムの部品・製品関係のアイテムマスターの照会・メンテナンス       //
//      MVC View 部     一覧表示及び編集部品の選択 インクリメントサーチ対応 //
// Copyright (C) 2005-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/13 Created   parts_item_ViewList.php                             //
// 2005/09/14 AjaxのためにPartsItem.GpartsKey=.partsKey.value を最終行に追加//
// 2005/09/20 NN7.1をEnterキーでsubmitさせるため onChange='submit()'を追加  //
// 2005/09/23 [合致するデータはありません] のメッセージを追加               //
// 2005/09/26 上記のNN7.1用の partsKey onChange= は他の悪影響があるため削除 //
// 2009/07/24 部品番号の途中に＃が入ったときの問題対応                 大谷 //
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
<body onLoad='PartsItem.setFocus(document.ControlForm.partsKey)'>
<center>
<?= $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?=$menu->out_self(), "?id={$uniq}"?>' method='post'>
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
                <span <?php if($current_menu=='list') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                    部品番号
                </span>
                <input class='pt12b' type='text' name='partsKey' value='<?=$partsKey?>' maxlength='9' size='11'>
            </td>
            <td class='winbox' nowrap>
                <?=$pageControll?>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    <span id='showAjax'>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>部品・製品 のアイテム マスター 一覧</caption>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>部品番号</th>
            <th class='winbox' nowrap>部品名称</th>
            <th class='winbox' nowrap>材　　質</th>
            <th class='winbox' nowrap>親機種</th>
            <th class='winbox' nowrap>AS登録日</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <% if ($res[$r][2] != '無効') { %>
            <tr>
            <% } else {%>
            <tr style='color:gray;'>
            <% } %>
            <td class='winbox' align='right' nowrap><?=$r + 1 + $model->get_offset()?></td>
            <td class='winbox' align='center' nowrap>
            <?php $res[$r][0] = str_replace('#', 'シャープ', $res[$r][0]); ?>
                <a href='<?=$menu->out_self(), "?parts_no={$res[$r][0]}&current_menu=edit&", $model->get_htmlGETparm(), "&partsKey={$partsKey}", "&id={$uniq}"?>'
                 style='text-decoration:none;'>
                    編集
                </a>
            </td>
            <?php $res[$r][0] = str_replace('シャープ', '#', $res[$r][0]); ?>
            <!-- 部品・製品 番号 -->
            <td class='winbox' align='center' nowrap><?=$res[$r][0]?></td>
            <!-- 部品・製品 名称 -->
            <td class='winbox' align='left' nowrap><?=$res[$r][1]?></td>
            <!-- 材質 -->
            <td class='winbox' align='left' nowrap><?=$res[$r][2]?></td>
            <!-- 親機種 -->
            <td class='winbox' align='left' nowrap><?=$res[$r][3]?></td>
            <!-- AS登録日 -->
            <td class='winbox' align='center' nowrap><?=$res[$r][4]?></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    <?php } elseif ($partsKey != '') { ?>
        <p>
        <div class='caption_font'>上記の部品番号に合致するデータはありません！</div>
        </p>
    <?php } else { ?>
        <p>
        <div class='caption_font'>部品番号欄に１文字入力する毎に検索結果を表示します。(インクリメンタルサーチ)</div>
        </p>
    <?php } ?>
    </span>
</center>
</body>
<?=$menu->out_alert_java()?>
<script type='text/javascript'>
PartsItem.GpartsKey = document.ControlForm.partsKey.value;
var G_incrementalSearch = true;
var G_UpperSwitch = "list";
</script>
</html>
