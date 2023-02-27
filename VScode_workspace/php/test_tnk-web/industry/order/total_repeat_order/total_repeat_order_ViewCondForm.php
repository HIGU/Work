<?php
//////////////////////////////////////////////////////////////////////////////
// リピート部品発注の集計 照会                 条件選択 Form    MVC View 部 //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/19 Created   total_repeat_order_ViewCondForm.php                 //
// 2007/12/20 targetLimitの<option>数量を変更                               //
// 2008/07/30 targetLimitの<option>数量を変更                          大谷 //
//////////////////////////////////////////////////////////////////////////////
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
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
<link rel='stylesheet' href='total_repeat_order.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='total_repeat_order.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        <?php if ($request->get('AutoStart') != '') echo 'TotalRepeatOrder.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' id='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return false;'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td align='center' class='winbox caption_color pt12b'>
                    <span id='blink_item'>対象年月</span>
                </td>
                <td class='winbox' align='center'>
                    <select id='targetDateYM' class='pt14b'>
                        <!-- <option value='200608' selected>2006年08月</option> -->
                        <option value=''>日付選択</option>
                        <?php echo $model->getTargetDateYMvalues($request) ?>
                    </select>
                    又は
                    <input type='text' name='targetDateStr' size='8' class='pt12b' value='<?php echo $request->get('targetDateStr'); ?>' maxlength='8'>
                    ～
                    <input type='text' name='targetDateEnd' size='8' class='pt12b' value='<?php echo $request->get('targetDateEnd'); ?>' maxlength='8'>
                </td>
                <td align='center' class='winbox caption_color pt12b'>
                    <span id='blink_item2'>表示件数</span>
                </td>
                <td class='winbox' align='center'>
                    <select id='targetLimit' class='pt12b'>
                        <option value='50' <?php if ($request->get('targetLimit')  ==   50) echo ' selected'?>>　　５０</option>
                        <option value='200'<?php if ($request->get('targetLimit')  ==  200) echo ' selected'?>>　２００</option>
                        <option value='500'<?php if ($request->get('targetLimit')  ==  500) echo ' selected'?>>　５００</option>
                        <option value='1000'<?php if ($request->get('targetLimit') == 1000) echo ' selected'?>>１０００</option>
                        <option value='3000'<?php if ($request->get('targetLimit') == 3000) echo ' selected'?>>３０００</option>
                        <option value='20000'<?php if ($request->get('targetLimit') == 20000) echo ' selected'?>>す べ て</option>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <input type='submit' class='regular' name='exec1' value='実行' title='クリックすれば、この下に表示します。'>
                    <input type='button' class='regular' id='showWin' value='開く' title='クリックすれば、別ウィンドウで表示します。'>
                    <input type='button' class='regular' id='clear' value='クリア' title='グラフやリストをクリアします。'>
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
