<?php
//////////////////////////////////////////////////////////////////////////////
// 指定検収日で指定保管場所の一覧(NKB入庫品)照会  条件選択 Form MVC View 部 //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/21 Created   parts_storage_space_ViewCondForm.php                //
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
<link rel='stylesheet' href='parts_storage_space.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_storage_space.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PartsStorageSpace.set_focus(document.ConditionForm.targetDateStr, "noSelect");
        <?php if ($request->get('AutoStart') != '') echo 'PartsStorageSpace.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return PartsStorageSpace.checkANDexecute(this, 1)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色  #ceffce エメラルドグリーン --> 
                <td align='center' class='winbox caption_color'>
                    <span id='blink_item'>対象年月日</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDate' class='pt14b' onChange='PartsStorageSpace.dateCreate(document.ConditionForm)'>
                        <!-- <option value='20060621' selected>2006年06月21日</option> -->
                        <option value='<?php echo workingDayOffset('-0') ?>' selected>日付選択</option>
                        <?php echo $model->getTargetDateValues($request) ?>
                    </select>
                    又は
                    <input type='text' name='targetDateStr' size='8' class='pt12b' value='<?php echo $request->get('targetDateStr'); ?>' maxlength='8'>
                    ～
                    <input type='text' name='targetDateEnd' size='8' class='pt12b' value='<?php echo $request->get('targetDateEnd'); ?>' maxlength='8'>
                </td>
                <td align='center' class='winbox caption_color'>
                    入庫場所
                </td>
                <td class='winbox' align='center'>
                    <select name='targetLocate' class='pt14b'>
                        <option value='14'<?php if($request->get('targetLocate') == 14) echo ' selected'?>>ＮＫＢ</option>
                        <option value='30'<?php if($request->get('targetLocate') == 30) echo ' selected'?>>資材</option>
                        <option value='33'<?php if($request->get('targetLocate') == 33) echo ' selected'?>>組立</option>
                        <option value='52'<?php if($request->get('targetLocate') == 52) echo ' selected'?>>東京</option>
                        <option value='40'<?php if($request->get('targetLocate') == 40) echo ' selected'?>>山形</option>
                        <option value='91'<?php if($request->get('targetLocate') == 91) echo ' selected'?>>海外</option>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' name='exec1' value='実行' onClick='PartsStorageSpace.checkANDexecute(document.ConditionForm, 1);' title='クリックすれば、この下に表示します。'>
                    &nbsp;
                    <input type='button' name='exec2' value='開く' onClick='PartsStorageSpace.checkANDexecute(document.ConditionForm, 2);' title='クリックすれば、別ウィンドウで表示します。'>
                    &nbsp;
                    <input type='button' name='clear' value='クリア' onClick='PartsStorageSpace.viewClear();'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    <!-- <br> -->
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
