<?php
//////////////////////////////////////////////////////////////////////////////
// 損益予測の集計・分析 結果 照会(都度照会)  条件選択 Form      MVC View 部 //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/13 Created   profit_loss_estimate_ViewCondForm.php               //
// 2011/07/19 都度照会版としてコメント追加                                  //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<!-- <meta http-equiv='Refresh' content='15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}"?>'> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<script type='text/javascript' src='profit_loss_estimate.js?<?php echo $uniq ?>'></script>
<link rel='stylesheet' href='profit_loss_estimate.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        ProfitLossEstimate.set_focus(document.ConditionForm.targetDateStr, "noSelect");
        // ProfitLossEstimate.intervalID = setInterval("ProfitLossEstimate.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('AutoStart') != '') echo 'ProfitLossEstimate.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return ProfitLossEstimate.checkANDexecute(this, 1)'
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
                    <select name='targetDateYM' class='pt14b' onChange='ProfitLossEstimate.dateCreate(document.ConditionForm)'>
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
                    <input type='button' name='exec1' value='実行' onClick='ProfitLossEstimate.checkANDexecute(document.ConditionForm, 1);' title='クリックすれば、この下に表示します。'>
                    &nbsp;
                    <input type='button' name='clear' value='クリア' onClick='ProfitLossEstimate.viewClear();'>
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
