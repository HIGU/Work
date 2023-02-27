<?php
//////////////////////////////////////////////////////////////////////////////
// 組立日程計画表(AS/400版)スケジュール照会 ガントズーム親     MVC View 部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/16 Created   assembly_schedule_show_ViewZoomGantt.php            //
// 2006/06/22 ズームで開くRefreshにpageParameter追加                        //
// 2006/11/01 表示倍率の指定 targetScale を追加                             //
// 2006/11/09 metaのRefreshをやめてsetInterval()でzoomGanttReload()を呼出し //
//////////////////////////////////////////////////////////////////////////////
$header_height = 87 * $this->request->get('targetScale');
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv='Refresh' content='30;URL=<?php echo $this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}&{$pageParameter}"?>'> -->
<title><?php echo $this->menu->out_title() ?></title>
<?php echo $this->menu->out_site_java() ?>
<?php echo $this->menu->out_css() ?>
<link rel='stylesheet' href='assembly_schedule_show.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_schedule_show.js?<?php echo $uniq ?>'></script>
</head>
<body
    style='overflow:hidden;'
    onLoad='
        AssemblyScheduleShow.set_focus(document.getElementById("closeID"), "noSelect");
        setInterval("AssemblyScheduleShow.zoomGanttReload(\"<?php echo $this->menu->out_self(), "?showMenu=ZoomGanttAjax&{$pageParameter}"?>\")", 30000);
    '
>
<center>
    <iframe id='frameHeader' hspace='0' vspace='0' frameborder='0' scrolling='yes' src='assembly_schedule_show_ViewZoomGanttHeader.php?<?php echo $uniq?>' name='header' align='center' width='100%' height='<?php echo $header_height ?>' title='項目'>
        表の項目を表示しています。
    </iframe>
    <iframe id='frameBody' hspace='0' vspace='0' frameborder='0' scrolling='yes' src='assembly_schedule_show_ViewZoomGanttBody.php?<?php echo $uniq?>' name='list' align='center' width='100%' height='85%' title='ガントチャート'>
        表の本文を表示しています。
    </iframe>
    <div align='center' class='pt12b'>
        表示倍率
        <select name='targetScale' onchange='location.replace("<?php echo $this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}&{$pageParameter}"?>" + "&targetScale=" + this.value);'>
            <option value='0.3'<?php if($this->request->get('targetScale')==0.3) echo ' selected'?>>&nbsp;30%</option>
            <option value='0.4'<?php if($this->request->get('targetScale')==0.4) echo ' selected'?>>&nbsp;40%</option>
            <option value='0.5'<?php if($this->request->get('targetScale')==0.5) echo ' selected'?>>&nbsp;50%</option>
            <option value='0.6'<?php if($this->request->get('targetScale')==0.6) echo ' selected'?>>&nbsp;60%</option>
            <option value='0.7'<?php if($this->request->get('targetScale')==0.7) echo ' selected'?>>&nbsp;70%</option>
            <option value='0.8'<?php if($this->request->get('targetScale')==0.8) echo ' selected'?>>&nbsp;80%</option>
            <option value='0.9'<?php if($this->request->get('targetScale')==0.9) echo ' selected'?>>&nbsp;90%</option>
            <option value='1.0'<?php if($this->request->get('targetScale')==1.0) echo ' selected'?>>100%</option>
            <option value='1.1'<?php if($this->request->get('targetScale')==1.1) echo ' selected'?>>110%</option>
            <option value='1.2'<?php if($this->request->get('targetScale')==1.2) echo ' selected'?>>120%</option>
            <option value='1.3'<?php if($this->request->get('targetScale')==1.3) echo ' selected'?>>130%</option>
            <option value='1.4'<?php if($this->request->get('targetScale')==1.4) echo ' selected'?>>140%</option>
            <option value='1.5'<?php if($this->request->get('targetScale')==1.5) echo ' selected'?>>150%</option>
            <option value='1.6'<?php if($this->request->get('targetScale')==1.6) echo ' selected'?>>160%</option>
            <option value='1.7'<?php if($this->request->get('targetScale')==1.7) echo ' selected'?>>170%</option>
        </select>
        &nbsp;&nbsp;
        <!-- <input type='button' name='closeButton' id='closeID' value='&nbsp;&nbsp;OK&nbsp;&nbsp;' onClick='window.close();'> -->
        <input type='button' name='closeButton' id='closeID' value='閉じる' onClick='window.close();'>
    </div>
</center>
</body>
<?php echo $this->menu->out_alert_java()?>
</html>
