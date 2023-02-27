<?php
//////////////////////////////////////////////////////////////////////////////
// 部門別 製造経費及び販管費の照会          条件選択 Form       MVC View 部 //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/13 Created   act_summary_ViewCondForm.php                        //
// 2007/11/09 部門コードの表示 追加に伴い <div id='showAjax'>の前の<br>削除 //
// 2008/06/11 部門指定を部門コードより行えるように変更                 大谷 //
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
<link rel='stylesheet' href='act_summary.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='act_summary.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        ActSummary.set_focus(document.ConditionForm.targetDateYM, "noSelect");
        // ActSummary.intervalID = setInterval("ActSummary.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('AutoStart') != '') echo 'ActSummary.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return ActSummary.checkANDexecute(this, 1)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td align='center' class='winbox caption_color' nowrap>
                    <span id='blink_item'>指定年月</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetDateYM' class='pt14b' onChange='//ActSummary.dateCreate(document.ConditionForm)'>
                        <option value='年月選択' style='color:red;' selected>年月選択</option>
                        <?php echo $model->getTargetDateYMvalues($session) ?>
                    </select>
                </td>
                <td align='center' class='winbox caption_color' nowrap>
                    <span id='act_code'>部門コード</span>
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='act_id' size='4' maxlength='3' onChange='ActSummary.selectId(act_id.value)'>
                </td>
                <td align='center' class='winbox caption_color' nowrap>
                    <span id='blink_item'>指定部門</span>
                </td>
                <td class='winbox' align='center'>
                    <select name='targetAct_id' class='pt14b' onChange='//ActSummary.dateCreate(document.ConditionForm)'>
                        <option value='部門選択' style='color:red;' selected>部門選択</option>
                        <?php echo $model->getTargetAct_idValues($session) ?>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    <input type='button' name='exec1' value='実行' onClick='ActSummary.checkANDexecute(document.ConditionForm, 1);' title='クリックすれば、この下に表示します。'>
                    &nbsp;
                    <input type='button' name='exec2' value='開く' onClick='ActSummary.checkANDexecute(document.ConditionForm, 2);' title='クリックすれば、別ウィンドウで表示します。'>
                    &nbsp;
                    <input type='button' name='clear' value='クリア' onClick='ActSummary.viewClear();'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
