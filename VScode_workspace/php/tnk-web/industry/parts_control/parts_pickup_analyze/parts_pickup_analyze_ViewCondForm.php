<?php
//////////////////////////////////////////////////////////////////////////////
// 資材出庫時間の集計･分析 結果 照会        条件選択 Form       MVC View 部 //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/30 Created   parts_pickup_analyze_ViewCondForm.php               //
// 2006/06/21 colspan='7'が無意味に入っていたのを削除  AutoStartに変更      //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<!-- <meta http-equiv='Refresh' content='15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>'> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='parts_pickup_analyze.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_pickup_analyze.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PartsPickupAnalyze.set_focus(document.ConditionForm.targetDateStr, "noSelect");
        // PartsPickupAnalyze.intervalID = setInterval("PartsPickupAnalyze.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('AutoStart') != '') echo 'PartsPickupAnalyze.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PartsPickupAnalyze.checkANDexecute(this, 1)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td align='center' class='winbox caption_color'>
                    <span id='blink_item'>対象年月</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDateYM' class='pt14b' onChange='PartsPickupAnalyze.dateCreate(document.ConditionForm)'>
                        <!-- <option value='200605' selected>2005年05月</option> -->
                        <option value='<?php echo date('Ym') ?>' selected>日付選択</option>
                        <?php echo $model->getTargetDateYMvalues($request) ?>
                    </select>
                    又は
                    <input type='text' name='targetDateStr' size='8' class='pt12b' value='<?php echo $request->get('targetDateStr'); ?>' maxlength='8'>
                    ～
                    <input type='text' name='targetDateEnd' size='8' class='pt12b' value='<?php echo $request->get('targetDateEnd'); ?>' maxlength='8'>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' name='exec1' value='実行' onClick='PartsPickupAnalyze.checkANDexecute(document.ConditionForm, 1);' title='クリックすれば、この下に表示します。'>
                    &nbsp;
                    <input type='button' name='exec2' value='開く' onClick='PartsPickupAnalyze.checkANDexecute(document.ConditionForm, 2);' title='クリックすれば、別ウィンドウで表示します。'>
                    &nbsp;
                    <input type='button' name='clear' value='クリア' onClick='PartsPickupAnalyze.viewClear();'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    <br>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
