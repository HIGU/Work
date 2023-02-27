<?php
//////////////////////////////////////////////////////////////////////////////
// 組立のライン別工数 各種グラフ   条件選択 Form                MVC View 部 //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/12 Created   assembly_time_graph_ViewCondForm.php                //
// 2006/05/24 点滅間隔を0.5→1.3秒へ変更(別件で見づらいと連絡があった)      //
// 2006/09/27 グラフタイプ(工数計算方法)のオプション(工数日割り計算)追加    //
// 2006/11/02 グラフ画像の倍率指定の追加 targetScale                        //
// 2007/01/16 過去工数のグラフ表示ON/OFF追加 targetPastData checkbox 追加   //
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
<link rel='stylesheet' href='assembly_time_graph.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_graph.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        AssemblyTimeGraph.lineViewCopy(document.ConditionForm.elements["targetLine[]"]);
        AssemblyTimeGraph.set_focus(document.ConditionForm.targetDateYM, "noSelect");
        AssemblyTimeGraph.intervalID = setInterval("AssemblyTimeGraph.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('targetPlanNo') != '') echo 'AssemblyTimeGraph.checkANDexecute(document.ConditionForm)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_self() ?>' method='post'
        onSubmit='return AssemblyTimeGraph.checkANDexecute(this)'
    >
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td colspan='9' width='880' align='center' class='winbox caption_color'>
                    <span id='blink_item'>対象年月と組立ラインを選んで下さい。</span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' onChange='//AssemblyTimeGraph.checkANDexecute(ConditionForm)'>
                    対象年月
                    <!-- <input type='text' name='targetDateYM' size='7' class='pt14b' value='<?php echo $request->get('targetDateYM'); ?>' maxlength='6'> -->
                    <select name='targetDateYM' class='pt14b'>
                        <!-- <option value='200605' selected>2005年05月</option> -->
                        <?php echo $model->getTargetDateYMvalues($request) ?>
                    </select>
                </td>
                <td class='winbox' align='center' rowspan='2'>
                    <span style='position:relative; top:-15px;'>組立ライン</span>
                    <select name='targetLine[]' size='3' multiple
                         onChange='AssemblyTimeGraph.lineViewCopy(this);' onClick='AssemblyTimeGraph.lineViewCopy(this);'
                    >
                        <!-- <option value='2OC1' selected>2OC1</option> -->
                        <?php echo $model->getTargetLineValues($request) ?>
                    </select>
                </td>
                <td class='winbox' align='center'>
                    １人の持工数
                    <select name='targetSupportTime' onChange='//AssemblyTimeGraph.checkANDexecute(ConditionForm)'>
                        <!-- <option value='440'<?php if($request->get('targetSupportTime')==440) echo ' selected';?>>４４０</option> -->
                        <?php echo $model->getTargetSupportTimeValues($request) ?>
                    </select>
                    分
                </td>
                <td class='winbox' align='center'>
                    工数計算
                    <select name='targetGraphType' onChange='//AssemblyTimeGraph.checkANDexecute(ConditionForm)'>
                        <option value='avr'<?php if($request->get('targetGraphType')=='avr') echo ' selected';?>>日割り</option>
                        <option value='end'<?php if($request->get('targetGraphType')=='end') echo ' selected';?>>完了日</option>
                    </select>
                </td>
            <!--
                <td class='winbox' align='center'>
                    工程
                    <select name='targetProcess' onChange='//AssemblyTimeGraph.checkANDexecute(ConditionForm)'>
                        <option value='H'<?php if($request->get('targetProcess')=='H')echo ' selected'?>>手作業工程</option>
                        <option value='M'<?php if($request->get('targetProcess')=='M')echo ' selected'?>>自動機工程</option>
                        <option value='G'<?php if($request->get('targetProcess')=='G')echo ' selected'?>>&nbsp;&nbsp;外注工程</option>
                        <option value='A'<?php if($request->get('targetProcess')=='A')echo ' selected'?>>&nbsp;&nbsp;全体工程</option>
                    </select>
                </td>
            -->
                <td class='winbox' align='center'>
                    <input type='submit' name='exec' value='実行'>
                    &nbsp;
                    <input type='button' name='clear' value='クリア' onClick='AssemblyTimeGraph.viewClear();'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>
                    表示倍率
                    <select name='targetScale'>
                        <option value='0.3'<?php if($request->get('targetScale')==0.3) echo ' selected'?>>&nbsp;30%</option>
                        <option value='0.4'<?php if($request->get('targetScale')==0.4) echo ' selected'?>>&nbsp;40%</option>
                        <option value='0.5'<?php if($request->get('targetScale')==0.5) echo ' selected'?>>&nbsp;50%</option>
                        <option value='0.6'<?php if($request->get('targetScale')==0.6) echo ' selected'?>>&nbsp;60%</option>
                        <option value='0.7'<?php if($request->get('targetScale')==0.7) echo ' selected'?>>&nbsp;70%</option>
                        <option value='0.8'<?php if($request->get('targetScale')==0.8) echo ' selected'?>>&nbsp;80%</option>
                        <option value='0.9'<?php if($request->get('targetScale')==0.9) echo ' selected'?>>&nbsp;90%</option>
                        <option value='1.0'<?php if($request->get('targetScale')==1.0) echo ' selected'?>>100%</option>
                        <option value='1.1'<?php if($request->get('targetScale')==1.1) echo ' selected'?>>110%</option>
                        <option value='1.2'<?php if($request->get('targetScale')==1.2) echo ' selected'?>>120%</option>
                        <option value='1.3'<?php if($request->get('targetScale')==1.3) echo ' selected'?>>130%</option>
                        <option value='1.4'<?php if($request->get('targetScale')==1.4) echo ' selected'?>>140%</option>
                        <option value='1.5'<?php if($request->get('targetScale')==1.5) echo ' selected'?>>150%</option>
                        <option value='1.6'<?php if($request->get('targetScale')==1.6) echo ' selected'?>>160%</option>
                        <option value='1.7'<?php if($request->get('targetScale')==1.7) echo ' selected'?>>170%</option>
                    </select>
                </td>
                <td class='winbox' colspan='2'>
                    <textarea name='lineView' cols='40' rows=1 wrap='virtual' style='background-color:#d6d3ce; font-weight:bold;' readonly></textarea>
                </td>
                <td class='winbox'>
                    <input type='checkbox' name='targetPastData' value='1'>過去工数表示
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
