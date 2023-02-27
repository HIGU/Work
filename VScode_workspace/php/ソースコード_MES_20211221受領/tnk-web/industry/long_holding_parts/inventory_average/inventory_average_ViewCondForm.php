<?php
//////////////////////////////////////////////////////////////////////////////
// 資材在庫部品 全品目の月平均出庫数・保有月数等照会           MVC View 部  //
// Copyright (C) 2007 - 2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2007/06/08 Created   inventory_average_ViewCondForm.php                  //
// 2007/06/10 ソート項目クリック時のパラメータ渡しのため CTM_viewPage を追加//
// 2007/06/11 要因マスター編集ボタンを追加→inventory_average.js も変更     //
// 2007/07/11 部品番号(searchPartsNo)のLIKE検索追加。チップヘルプ追加       //
// 2007/07/23 保有月の指定を追加(フィルター機能)                            //
// 2013/01/29 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>"> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='inventory_average.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='inventory_average.js?<?php echo $uniq ?>'></script>
<form name='ControlForm'>
    <input type='hidden' name='CTM_selectPage' value='<?php echo $request->get('CTM_selectPage')?>'>
    <input type='hidden' name='CTM_prePage'    value='<?php echo $request->get('CTM_prePage')?>'>
    <input type='hidden' name='CTM_pageRec'    value='<?php echo $request->get('CTM_pageRec')?>'>
    <input type='hidden' name='CTM_back'       value='<?php echo $request->get('CTM_back')?>'>
    <input type='hidden' name='CTM_next'       value='<?php echo $request->get('CTM_next')?>'>
    <input type='hidden' name='CTM_viewPage'   value='<?php echo $request->get('CTM_viewPage')?>'>
</form>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        InventoryAverage.set_focus(document.ConditionForm.searchPartsNo, "select");
        // InventoryAverage.set_focus(document.ConditionForm.targetDivision, "noSelect");
        // setInterval("InventoryAverage.blink_disp(\"blink_item\")", 500);
        <?php if ($request->get('showMenu') == 'Both') echo "InventoryAverage.checkANDexecute(document.ConditionForm);\n"; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return InventoryAverage.checkANDexecute(this)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <td width='20%' align='center' class='winbox caption_color' nowrap>
                    部品番号
                </td>
                <td width='10%' align='center' class='winbox'>
                    <input type='text' name='searchPartsNo' value='<?php echo $request->get('searchPartsNo') ?>' size='10' maxlength='9'
                        class='pt12b' onKeyUp='InventoryAverage.keyInUpper(this);'
title='
部品番号の指定方法

CP012 → 頭から CP012 に合致するもの全て

0354 → 途中の 0354 に合致するもの全て

#3   → 最後が #3   に合致するもの全て

-6   → 最後が -6   に合致するもの全て
'
                    >
                </td>
                <td width='20%' align='center' class='winbox caption_color' nowrap
title='
製品グループを選択した時点で検索を実行します。
カプラは現在のところ、全体しかありません。
'
                >
                    <span id='blink_item'>製品グループ</span>
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <select name='targetDivision' onChange='InventoryAverage.checkANDexecute(ConditionForm)'>
                        <option value='AL'<?php if($request->get('targetDivision')=='AL')echo ' selected'?>>全グループ</option>
                        <option value='CA'<?php if($request->get('targetDivision')=='CA')echo ' selected'?>>カプラ全体</option>
                   <!-- <option value='CH'<?php if($request->get('targetDivision')=='CH')echo ' selected'?>>カプラ標準</option> -->
                   <!-- <option value='CS'<?php if($request->get('targetDivision')=='CS')echo ' selected'?>>カプラ特注</option> -->
                        <option value='LA'<?php if($request->get('targetDivision')=='LA')echo ' selected'?>>リニア全体</option>
                        <option value='LH'<?php if($request->get('targetDivision')=='LH')echo ' selected'?>>リニアのみ</option>
                        <option value='LB'<?php if($request->get('targetDivision')=='LB')echo ' selected'?>>液体ポンプ</option>
                        <option value='OT'<?php if($request->get('targetDivision')=='OT')echo ' selected'?>>その他</option>
                    </select>
                </td>
                <td width='20%' align='center' class='winbox caption_color' nowrap
title='
保有月を指定した場合は、指定月以上のものを表示します。
指定しない場合は全て表示します。
'
                >
                    <span id='blink_item'>保有月</span>
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <select name='targetHoldMonth'>
                        <option value='0'<?php if($request->get('targetHoldMonth')=='0')echo ' selected'?>>未指定</option>
                        <option value='2'<?php if($request->get('targetHoldMonth')=='1')echo ' selected'?>>２ヶ月</option>
                        <option value='4'<?php if($request->get('targetHoldMonth')=='4')echo ' selected'?>>４ヶ月</option>
                        <option value='6'<?php if($request->get('targetHoldMonth')=='6')echo ' selected'?>>６ヶ月</option>
                        <option value='8'<?php if($request->get('targetHoldMonth')=='8')echo ' selected'?>>８ヶ月</option>
                        <option value='10'<?php if($request->get('targetHoldMonth')=='10')echo ' selected'?>>１０ヶ月</option>
                        <option value='12'<?php if($request->get('targetHoldMonth')=='12')echo ' selected'?>>１２ヶ月</option>
                        <option value='14'<?php if($request->get('targetHoldMonth')=='14')echo ' selected'?>>１４ヶ月</option>
                        <option value='16'<?php if($request->get('targetHoldMonth')=='16')echo ' selected'?>>１６ヶ月</option>
                        <option value='18'<?php if($request->get('targetHoldMonth')=='18')echo ' selected'?>>１８ヶ月</option>
                        <option value='20'<?php if($request->get('targetHoldMonth')=='20')echo ' selected'?>>２０ヶ月</option>
                        <option value='22'<?php if($request->get('targetHoldMonth')=='22')echo ' selected'?>>２２ヶ月</option>
                        <option value='24'<?php if($request->get('targetHoldMonth')=='24')echo ' selected'?>>２４ヶ月</option>
                        <option value='26'<?php if($request->get('targetHoldMonth')=='26')echo ' selected'?>>２６ヶ月</option>
                        <option value='28'<?php if($request->get('targetHoldMonth')=='28')echo ' selected'?>>２８ヶ月</option>
                        <option value='30'<?php if($request->get('targetHoldMonth')=='30')echo ' selected'?>>３０ヶ月</option>
                        <option value='32'<?php if($request->get('targetHoldMonth')=='32')echo ' selected'?>>３２ヶ月</option>
                        <option value='34'<?php if($request->get('targetHoldMonth')=='34')echo ' selected'?>>３４ヶ月</option>
                        <option value='36'<?php if($request->get('targetHoldMonth')=='36')echo ' selected'?>>３６ヶ月</option>
                        <option value='38'<?php if($request->get('targetHoldMonth')=='38')echo ' selected'?>>３８ヶ月</option>
                        <option value='40'<?php if($request->get('targetHoldMonth')=='40')echo ' selected'?>>４０ヶ月</option>
                        <option value='42'<?php if($request->get('targetHoldMonth')=='42')echo ' selected'?>>４２ヶ月</option>
                        <option value='44'<?php if($request->get('targetHoldMonth')=='44')echo ' selected'?>>４４ヶ月</option>
                        <option value='46'<?php if($request->get('targetHoldMonth')=='46')echo ' selected'?>>４６ヶ月</option>
                        <option value='48'<?php if($request->get('targetHoldMonth')=='48')echo ' selected'?>>４８ヶ月</option>
                        <option value='50'<?php if($request->get('targetHoldMonth')=='50')echo ' selected'?>>５０ヶ月</option>
                        <option value='52'<?php if($request->get('targetHoldMonth')=='52')echo ' selected'?>>５２ヶ月</option>
                        <option value='54'<?php if($request->get('targetHoldMonth')=='54')echo ' selected'?>>５４ヶ月</option>
                        <option value='56'<?php if($request->get('targetHoldMonth')=='56')echo ' selected'?>>５６ヶ月</option>
                        <option value='58'<?php if($request->get('targetHoldMonth')=='58')echo ' selected'?>>５８ヶ月</option>
                        <option value='60'<?php if($request->get('targetHoldMonth')=='60')echo ' selected'?>>６０ヶ月</option>
                        <option value='62'<?php if($request->get('targetHoldMonth')=='62')echo ' selected'?>>６２ヶ月</option>
                        <option value='64'<?php if($request->get('targetHoldMonth')=='64')echo ' selected'?>>６４ヶ月</option>
                        <option value='66'<?php if($request->get('targetHoldMonth')=='66')echo ' selected'?>>６６ヶ月</option>
                        <option value='68'<?php if($request->get('targetHoldMonth')=='68')echo ' selected'?>>６８ヶ月</option>
                        <option value='70'<?php if($request->get('targetHoldMonth')=='70')echo ' selected'?>>７０ヶ月</option>
                        <option value='72'<?php if($request->get('targetHoldMonth')=='72')echo ' selected'?>>７２ヶ月</option>
                        <option value='74'<?php if($request->get('targetHoldMonth')=='74')echo ' selected'?>>７４ヶ月</option>
                        <option value='76'<?php if($request->get('targetHoldMonth')=='76')echo ' selected'?>>７６ヶ月</option>
                        <option value='78'<?php if($request->get('targetHoldMonth')=='78')echo ' selected'?>>７８ヶ月</option>
                        <option value='80'<?php if($request->get('targetHoldMonth')=='80')echo ' selected'?>>８０ヶ月</option>
                        <option value='82'<?php if($request->get('targetHoldMonth')=='82')echo ' selected'?>>８２ヶ月</option>
                        <option value='84'<?php if($request->get('targetHoldMonth')=='84')echo ' selected'?>>８４ヶ月</option>
                        <option value='86'<?php if($request->get('targetHoldMonth')=='86')echo ' selected'?>>８６ヶ月</option>
                        <option value='88'<?php if($request->get('targetHoldMonth')=='88')echo ' selected'?>>８８ヶ月</option>
                        <option value='90'<?php if($request->get('targetHoldMonth')=='90')echo ' selected'?>>９０ヶ月</option>
                        <option value='92'<?php if($request->get('targetHoldMonth')=='92')echo ' selected'?>>９２ヶ月</option>
                        <option value='94'<?php if($request->get('targetHoldMonth')=='94')echo ' selected'?>>９４ヶ月</option>
                        <option value='96'<?php if($request->get('targetHoldMonth')=='96')echo ' selected'?>>９６ヶ月</option>
                        <option value='98'<?php if($request->get('targetHoldMonth')=='98')echo ' selected'?>>９８ヶ月</option>
                        <option value='100'<?php if($request->get('targetHoldMonth')=='100')echo ' selected'?>>１００ヶ月</option>
                        <option value='999'<?php if($request->get('targetHoldMonth')=='999')echo ' selected'?>>９９９ヶ月</option>
                    </select>
                    以上
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <input type='submit' name='exec' value='実行'>
                    <input type='button' name='clear' value='クリア' onClick='InventoryAverage.viewClear();'>
                </td>
            </tr>
        </table>
            </td>
            <td>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <td width='90%' class='winbox caption_color' align='center' nowrap>
                    <span id='blink_item'>要因マスター</span>
                </td>
                <td width='10%' class='winbox' align='center' nowrap>
                    <input type='button' name='factorMnt' value='編集' onclick='InventoryAverage.AjaxLoadTable("FactorMnt", "showAjax")'>
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
