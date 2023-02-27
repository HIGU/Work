<?php
//////////////////////////////////////////////////////////////////////////////
// 受入検査の時間・件数の集計･分析 結果 照会   条件選択 Form    MVC View 部 //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/08/04 Created   acceptance_inspection_analyze_ViewCondForm.php      //
// 2006/11/30 日付選択の時<option value='echo date('Ym')' → value='' クリア//
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
<link rel='stylesheet' href='acceptance_inspection_analyze.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='acceptance_inspection_analyze.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        <?php if ($request->get('AutoStart') != '') echo 'AcceptanceInspectionAnalyze.checkANDexecute(document.ConditionForm, 1)'; ?>
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
                <td class='winbox' align='center'>
                    <input type='submit' class='regular' name='exec1' value='実行' title='クリックすれば、この下に表示します。'>
                    &nbsp;
                    <input type='button' class='regular' id='showWin' value='開く' title='クリックすれば、別ウィンドウで表示します。'>
                    &nbsp;
                    <input type='button' class='regular' id='clear' value='クリア' title='グラフやリストをクリアします。'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' colspan='3'>
                    <input type='button' class='large' id='ListLeadTime' value='検査日数集計' title='担当者毎の受付日からの実際検査日数を集計します。'>→<input type='button' class='large' id='WinListLeadTime' value='Windowで開く' title='担当者毎の受付日からの実際検査日数を別ウィンドウで表示します。'>
                    &nbsp;&nbsp;
                    <input type='button' class='large' id='ListInspectionTime' value='検査時間集計' title='担当者毎の実際検査時間を集計します。'>→<input type='button' class='large' id='WinListInspectionTime' value='Windowで開く' title='担当者毎の実際検査時間を別ウィンドウで表示します。'>
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
