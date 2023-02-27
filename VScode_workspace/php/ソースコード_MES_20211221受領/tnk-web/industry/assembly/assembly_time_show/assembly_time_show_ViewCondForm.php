<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の登録工数と実績工数の比較 照会         条件選択 Form    MVC View 部 //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created   assembly_time_show_ViewCondForm.php                 //
// 2006/03/03 targetPlanNoがリクエストされてきた場合は自動で実行する        //
// 2006/03/12 noMenuのリクエストがあった場合はタイトルボーダーを表示しない  //
// 2006/05/19 regOnlyのリクエストがあった場合は登録工数のみ表示する         //
// 2006/05/28 閉じるボタンがregOnlyの時だったのを noMenu 時に変更           //
// 2007/06/17 regOnlyの場合の usedTime, workerCountをhidden属性で追加       //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?=$this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}"?>"> -->
<title><?= $this->menu->out_title() ?></title>
<?= $this->menu->out_site_java() ?>
<?= $this->menu->out_css() ?>
<link rel='stylesheet' href='assembly_time_show.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_show.js?<?= $uniq ?>'></script>
</head>
<body
    onLoad='
        <?php if ($this->request->get('regOnly')) { ?>
        AssemblyTimeShow.set_focus(document.getElementById("closeButton"), "noSelect");
        setInterval("AssemblyTimeShow.winActiveChk(document.getElementById(\"closeButton\"))",50);
        <?php } else { ?>
        AssemblyTimeShow.set_focus(document.ConditionForm.targetPlanNo, "select");
        setInterval("AssemblyTimeShow.blink_disp(\"blink_item\")", 500);
        <?php } ?>
        <?php if ($this->request->get('targetPlanNo') != '') echo 'AssemblyTimeShow.checkANDexecute(document.ConditionForm)'; ?>
    '
>
<center>
<?php if (!$this->request->get('noMenu')) {?>
<?php echo $this->menu->out_title_border() ?>
<?php }?>
    
    <form name='ConditionForm' action='<?= $this->menu->out_self() ?>' method='post'
        onSubmit='return AssemblyTimeShow.checkANDexecute(this)'
    >
        <input type='hidden' name='usedTime' value='<?php echo $this->request->get('usedTime'); ?>'>
        <input type='hidden' name='workerCount' value='<?php echo $this->request->get('workerCount'); ?>'>
    <?php if ($this->request->get('regOnly')) {?>
        <input type='hidden' name='targetPlanNo' value='<?php echo $this->request->get('targetPlanNo'); ?>'>
        <input type='hidden' name='regOnly' value='yes'>
    <?php } else { ?>
        <input type='hidden' name='regOnly' value='no'>
        <!----------------- ここは 本文を表示する ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td colspan='2' width='350' align='center' class='winbox caption_color'>
                    <span id='blink_item'>計画番号を指定して下さい。</span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right'>
                    計画番号の指定
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetPlanNo' size='10' class='pt14b' value='<?php echo $this->request->get('targetPlanNo'); ?>' maxlength='8'>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='2' align='center'>
                    <input type='submit' name='exec' value='実行'>
                    &nbsp;&nbsp;
                    <input type='button' name='clear' value='クリア' onClick='AssemblyTimeShow.viewClear();'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    <?php } ?>
    </form>
    <div id='showAjax'>
    </div>
    <?php if ($this->request->get('noMenu')) { ?>
        <div align='center'>
            <input type='button' id='closeButton' value='&nbsp;閉じる&nbsp;' onClick='window.close()'>
        </div>
    <?php } ?>
</center>
</body>
<?=$this->menu->out_alert_java()?>
</html>
