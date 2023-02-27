<?php
//////////////////////////////////////////////////////////////////////////////
// 部課長用会議スケジュール照会 ガントズーム親     MVC View 部              //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/03/11 Created   meeting_schedule_manager_ViewZoomGantt.php          //
//////////////////////////////////////////////////////////////////////////////
$header_height = 87 * $request->get('targetScale');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv='Refresh' content='30;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}&{$pageParameter}"?>'> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='meeting_schedule_manager.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='meeting_schedule_manager.js?<?php echo $uniq ?>'></script>
</head>
<body>
<center>
    <?php
    if ($range > 0) {
        for ($r = 1; $r <= $range; $r++) { 
            $gf_name = $g_name . "-{$r}.png";
    ?>
            <table border='0'>
                <tr><td align='center'>
                    <?= "<img width='990' src='", $gf_name, "?id={$uniq}' ISMAP USEMAP='#myimagemap' alt='スケジュールの表示' border='0'>\n"; ?>
                </td></tr>
            </table>
        <?php } ?>
    <?php } else { ?>
        <table border='0'>
            <tr><td align='center'>
                <?= "<img width='990' src='", $model->getGraphName(), "?id={$uniq}' ISMAP USEMAP='#myimagemap' alt='スケジュールの表示' border='0'>\n"; ?>
            </td></tr>
        </table>
    <?php } ?>
    <div align='center' class='pt12b'>
        <!--
        表示倍率
        <select name='targetScale' onchange='location.replace("<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}&{$pageParameter}"?>" + "&targetScale=" + this.value);'>
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
        -->
        &nbsp;&nbsp;
        <input type='button' name='closeButton' id='closeID' value='閉じる' onClick='window.close();'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
