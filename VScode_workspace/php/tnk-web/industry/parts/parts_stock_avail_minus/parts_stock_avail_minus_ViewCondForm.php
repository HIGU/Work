<?php
//////////////////////////////////////////////////////////////////////////////
// 部品 在庫・有効利用数(予定在庫数)マイナスリスト照会         MVC View 部  //
// Copyright (C) 2007 - 2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2007/08/02 Created   parts_stock_avail_minus_ViewCondForm.php            //
// 2013/01/29 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>"> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='parts_stock_avail_minus.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_stock_avail_minus.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        // PartsStockAvailMinus.set_focus(document.ConditionForm.searchPartsNo, "select");
        PartsStockAvailMinus.set_focus(document.ConditionForm.targetDivision, "noSelect");
        // setInterval("PartsStockAvailMinus.blink_disp(\"blink_item\")", 500);
        <?php if ($request->get('showMenu') == 'Both') echo "PartsStockAvailMinus.checkANDexecute(document.ConditionForm);\n"; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PartsStockAvailMinus.checkANDexecute(this)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <td width='20%' align='center' class='winbox caption_color' nowrap
title='
製品グループを選択した時点で検索を実行します。
カプラは現在のところ、全体しかありません。
'
                >
                    <span id='blink_item'>製品グループ</span>
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <select name='targetDivision' onChange='PartsStockAvailMinus.checkANDexecute(document.ConditionForm)'>
                        <option value=''  <?php if($request->get('targetDivision')==''  )echo ' selected'?>>選んで下さい</option>
                        <option value='AL'<?php if($request->get('targetDivision')=='AL')echo ' selected'?>>全グループ</option>
                        <option value='CA'<?php if($request->get('targetDivision')=='CA')echo ' selected'?>>カプラ全体</option>
                        <option value='CH'<?php if($request->get('targetDivision')=='CH')echo ' selected'?>>カプラ標準</option>
                        <option value='CS'<?php if($request->get('targetDivision')=='CS')echo ' selected'?>>カプラ特注</option>
                        <option value='LA'<?php if($request->get('targetDivision')=='LA')echo ' selected'?>>リニア全体</option>
                        <option value='LL'<?php if($request->get('targetDivision')=='LL')echo ' selected'?>>リニアのみ</option>
                        <option value='LB'<?php if($request->get('targetDivision')=='LB')echo ' selected'?>>液体ポンプ</option>
                    </select>
                </td>
                <td width='20%' align='center' class='winbox caption_color' nowrap
title='
保有月を指定した場合は、指定月以上のものを表示します。
指定しない場合は全て表示します。
'
                >
                    <span id='blink_item'>マイナス条件</span>
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <select name='targetMinusItem'>
                        <option value='1'<?php if($request->get('targetMinusItem')=='1')echo ' selected'?>>全て</option>
                        <option value='2'<?php if($request->get('targetMinusItem')=='2')echo ' selected'?>>現在在庫</option>
                        <option value='3'<?php if($request->get('targetMinusItem')=='3')echo ' selected'?>>途中在庫</option>
                        <option value='4'<?php if($request->get('targetMinusItem')=='4')echo ' selected'?>>最終在庫</option>
                    </select>
                </td>
                <td width='20%' align='center' class='winbox caption_color' nowrap>
                    部品番号
                </td>
                <td width='10%' align='center' class='winbox'>
                    <input type='text' name='searchPartsNo' value='<?php echo $request->get('searchPartsNo') ?>' size='9' maxlength='9'
                        class='pt12b' onKeyUp='PartsStockAvailMinus.keyInUpper(this);'
title='
部品番号の指定方法

CP012 → 頭から CP012 に合致するもの全て

0354 → 途中の 0354 に合致するもの全て

#3   → 最後が #3   に合致するもの全て

-6   → 最後が -6   に合致するもの全て
'
                    >
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <input type='submit' name='exec' value='実行'>
                    <input type='button' name='clear' value='クリア' onClick='PartsStockAvailMinus.viewClear();'>
                    <input type='button' name='sclear' value='ソート解除'class='cancelButton' onClick='PartsStockAvailMinus.sortClear(document.ConditionForm)'>
                </td>
            </tr>
        </table>
            </td>
            </tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
