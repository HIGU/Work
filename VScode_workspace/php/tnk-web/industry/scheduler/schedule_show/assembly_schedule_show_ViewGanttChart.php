<?php
//////////////////////////////////////////////////////////////////////////////
// 組立日程計画表(AS/400版)スケジュール 照会  ガントチャート   MVC View 部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/26 Created   assembly_schedule_show_ViewGanttChart.php           //
// 2006/02/14 自動更新のON・OFF機能を追加。着手日等にチップヘルプを追加     //
// 2006/02/15 自動更新時間を１５秒 → ３０秒へ変更                          //
// 2006/02/16 JavaScriptのAssemblyScheduleShow.switchAutoReLoad()メソッドへ //
//            自動更新時に画面の位置を保持させるため<img width='990'を追加  //
// 2006/03/03 AssemblyScheduleShow.switchComplete()追加(完成分の日程表を表示//
// 2006/04/11 トグルスイッチ表示の未完成と完成済 → 予定品と完了品 へ変更   //
//       現在使用していないがViewGanttChartAjaxとソースの同期のみ行っている //
// 2006/06/16 ガントチャートのみを別ウィンドウで開く機能を追加 zoomGantt()  //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?=$this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}&{$pageParameter}"?>"> -->
<title><?= $this->menu->out_title() ?></title>
<?= $this->menu->out_site_java() ?>
<?= $this->menu->out_css() ?>
<link rel='stylesheet' href='assembly_schedule_show.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_schedule_show.js?<?= $uniq ?>'></script>
</head>
<body
    onLoad='
        AssemblyScheduleShow.set_focus(document.ControlForm.Gantt, "");
        setInterval("AssemblyScheduleShow.blink_disp(\"blink_item\")", 500);
        AssemblyScheduleShow.CompleteStatus = "<?php echo $this->request->get('targetCompleteFlag') ?>";
        AssemblyScheduleShow.switchAutoReLoad("AssemblyScheduleShow.AjaxLoadTable(\"GanttTable\")", 30000);
    '
>
<center>
<?= $this->menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <!-- <caption>組立日程計画のライン番号の選択</caption> -->
        <tr>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='group_name' value='全て' class='pt12b bg'
                    onClick='location.replace("<?=$this->menu->out_self(), "?showLine=0&showMenu={$this->request->get('showMenu')}&id={$uniq}"?>")'
                    <?php if ($this->request->get('showLine') == '') echo 'style=color:red;';?>
                >
            </td>
        <td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
<?php if ($rowsLine <= 0) { ?>
        <tr>
            <td class='winbox pt12b' align='center' nowrap>指定条件の日程計画には組立ラインの登録がありません。</td>
        </tr>
        <tr>
            <td class='winbox' width='620'>&nbsp</td>
        </tr>
<?php } else { ?>
    
    <?php $tr = 0; $column = 10; ?>
    <?php for ($i=0; $i<$rowsLine; $i++) { ?>
        <?php if ($tr == 0) {?>
        <tr>
        <?php } ?>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='group_name' value='<?=$resLine[$i][0]?>' class='pt12b bg'
                    onClick='location.replace("<?=$this->menu->out_self(), "?showLine={$resLine[$i][0]}&showMenu={$this->request->get('showMenu')}&id={$uniq}"?>")'
                    <?php if ($resLine[$i][0] == $this->request->get('showLine')) echo 'style=color:red;';?>
                >
            </td>
            <?php $tr++ ?>
        <?php if ($tr >= $column) {?>
        </tr>
        <?php } ?>
        <?php if ($tr >= $column) $tr = 0;?>
    <?php } ?>
    <?php
    if ($tr != 0) {
        while ($tr < $column) {
            echo "            <td class='winbox' width='55'>&nbsp;</td>\n";
            $tr++;
        }
        echo "        </tr>\n";
    }
    ?>
<?php } ?>
    </table>
        </td> <!-- ダミー -->
            <td class='winbox' align='right' nowrap>
                製品区分
                <select name='targetSeiKubun' onChange='location.replace("<?=$this->menu->out_self(), "?showLine=0&showMenu={$this->request->get('showMenu')}&id={$uniq}"?>"+"&targetSeiKubun="+this.value)' style='text-align:right;'>
                    <option value='0'<?php if ($this->request->get('targetSeiKubun') == '0') echo ' selected'?>>全て</option>
                    <option value='1'<?php if ($this->request->get('targetSeiKubun') == '1') echo ' selected'?>>標準</option>
                    <option value='3'<?php if ($this->request->get('targetSeiKubun') == '3') echo ' selected'?>>特注</option>
                </select>
                <br>
                事業部
                <select name='targetDept' onChange='location.replace("<?=$this->menu->out_self(), "?showLine=0&showMenu={$this->request->get('showMenu')}&id={$uniq}"?>"+"&targetDept="+this.value)' style='text-align:right;'>
                    <option value='0'<?php if ($this->request->get('targetDept') == '0') echo ' selected'?>>　全て</option>
                    <option value='C'<?php if ($this->request->get('targetDept') == 'C') echo ' selected'?>>カプラ</option>
                    <option value='L'<?php if ($this->request->get('targetDept') == 'L') echo ' selected'?>>リニア</option>
                    <option value='T'<?php if ($this->request->get('targetDept') == 'T') echo ' selected'?>>ツール</option>
                </select>
            </td>
        </tr> <!-- ダミー -->
    </table>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
        <form name='ControlForm' action='<?=$this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}&id={$uniq}"?>' method='post'>
            <table border='0' width='100%'>
                <tr>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='List' value='日程計画一覧' class='pt12b bg'
                        onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=PlanList&{$pageParameter}"?>")'
                    </td>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='Gantt' value='ガントチャート' class='pt12b bg' style='color:red;'
                        onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=GanttChart&{$pageParameter}"?>")'
                    </td>
                    <td align='center' nowrap width='40%'>
                        <!-- <span class='caption_font'>日程計画 一覧</span> -->
                        <?= $this->model->getDateSpanHTML($this->request->get('targetDate')) ?>
                        <select name='targetDateSpan' onChange='submit()' class='pt12b' style='text-align:right;'>
                            <option value='0'<?php if ($this->request->get('targetDateSpan') == '0') echo ' selected'?>>のみ</option>
                            <option value='1'<?php if ($this->request->get('targetDateSpan') == '1') echo ' selected'?>>まで</option>
                        </select>
                    </td>
                    <td align='center' nowrap width='40%'>
                        <?=$pageControl?>
                    </td>
                </tr>
            </table>
        </form>
        </caption>
        <tr><td> <!-- ダミー #e6e6e6 -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox pt9' width='20'
                onClick='AssemblyScheduleShow.switchAutoReLoad("AssemblyScheduleShow.AjaxLoadTable(\"GanttTable\")", 30000);'
                id='toggleSwitch' onMouseover="this.style.backgroundColor='red'" onMouseout ="this.style.backgroundColor=''"
                title='画面更新の 自動・手動 を切替えます。クリックする毎にMAN(手動)・AUT(自動)がトグル式に切替わります。'
            >
                <label for='toggleSwitch'><span id='toggleView'>AUT</span></label>
            </th>
            <th class='winbox pt12b' width='80' nowrap>&nbsp;</th>
            <th class='winbox pt12b' width='80' nowrap>&nbsp;</th>
            <th class='winbox pt12b' width='180' nowrap
                onClick='AssemblyScheduleShow.zoomGantt("<?php echo $this->menu->out_self()?>")' style='background-Color:teal;'
                id='zoomButton' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='ガントチャートのみ別ウィンドウで表示します。また、日付等の見出しを固定してスクロールできます。'
            >
                <label for='zoomButton'>ズームで開く</label>
            </th>
            <th class='winbox pt12b' width='80' onClick='AssemblyScheduleShow.switchComplete("Gantt")' style='background-Color:darkred;'
                id='CompleteName' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor='darkred'"
                title='予定品と完了品を切替えて日程表を表示します。'
            >
                <label for='CompleteName' id='CompleteFlag'><?php if ($this->request->get('targetCompleteFlag') == 'no') echo '予定品'; else echo '完了品'; ?></label>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=GanttChart&targetDateItem=syuka"?>")'
                id='syuka' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='集荷日で日程データを抽出して集荷日順に並び替えます。'
            >
                <?php if ($this->request->get('targetDateItem') == 'syuka') { ?>
                <label for='syuka' style='background-color:red;'><span id='blink_item'>集荷日▼</span></label>
                <?php } else { ?>
                <label for='syuka'>集荷日▼</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=GanttChart&targetDateItem=chaku"?>")'
                id='chaku' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='着手日で日程データを抽出して着手日順に並び替えます。'
            >
                <?php if ($this->request->get('targetDateItem') == 'chaku') { ?>
                <label for='chaku' style='background-color:red;'><span id='blink_item'>着手日▼</span></label>
                <?php } else { ?>
                <label for='chaku'>着手日▼</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=GanttChart&targetDateItem=kanryou"?>")'
                id='kanryou' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='完了日で日程データを抽出して完了日順に並び替えます。'
            >
                <?php if ($this->request->get('targetDateItem') == 'kanryou') { ?>
                <label for='kanryou' style='background-color:red;'><span id='blink_item'>完了日▼</span></label>
                <?php } else { ?>
                <label for='kanryou'>完了日▼</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='110' nowrap>&nbsp;</th>
        </table>
        </td></tr> <!-- ダミー -->
    </table>
    <span id='showAjax'>
    <?php if ($rows > 0) { ?>
    <table border='0'>
        <tr><td align='center'>
            <?= $this->model->graph->GetHTMLImageMap('myimagemap')?> 
            <?= "<img width='990' src='", $this->model->getGraphName(), "?id={$uniq}' ISMAP USEMAP='#myimagemap' alt='スケジュールの表示' border='0'>\n"; ?>
        </td></tr>
    </table>
    <?php } ?>
    </span>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '登録がありません！') { ?>
<?=$this->menu->out_alert_java()?>
<?php } ?>
</html>
