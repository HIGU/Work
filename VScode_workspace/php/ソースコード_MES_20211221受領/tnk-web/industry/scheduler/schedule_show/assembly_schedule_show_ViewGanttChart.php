<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����ײ�ɽ(AS/400��)�������塼�� �Ȳ�  ����ȥ��㡼��   MVC View ��  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/26 Created   assembly_schedule_show_ViewGanttChart.php           //
// 2006/02/14 ��ư������ON��OFF��ǽ���ɲá���������˥��åץإ�פ��ɲ�     //
// 2006/02/15 ��ư�������֤򣱣��� �� �����ä��ѹ�                          //
// 2006/02/16 JavaScript��AssemblyScheduleShow.switchAutoReLoad()�᥽�åɤ� //
//            ��ư�������˲��̤ΰ��֤��ݻ������뤿��<img width='990'���ɲ�  //
// 2006/03/03 AssemblyScheduleShow.switchComplete()�ɲ�(����ʬ������ɽ��ɽ��//
// 2006/04/11 �ȥ��륹���å�ɽ����̤�����ȴ����� �� ͽ���ʤȴ�λ�� ���ѹ�   //
//       ���߻��Ѥ��Ƥ��ʤ���ViewGanttChartAjax�ȥ�������Ʊ���Τ߹ԤäƤ��� //
// 2006/06/16 ����ȥ��㡼�ȤΤߤ��̥�����ɥ��ǳ�����ǽ���ɲ� zoomGantt()  //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
        <!-- <caption>��Ω�����ײ�Υ饤���ֹ������</caption> -->
        <tr>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='group_name' value='����' class='pt12b bg'
                    onClick='location.replace("<?=$this->menu->out_self(), "?showLine=0&showMenu={$this->request->get('showMenu')}&id={$uniq}"?>")'
                    <?php if ($this->request->get('showLine') == '') echo 'style=color:red;';?>
                >
            </td>
        <td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
<?php if ($rowsLine <= 0) { ?>
        <tr>
            <td class='winbox pt12b' align='center' nowrap>������������ײ�ˤ���Ω�饤�����Ͽ������ޤ���</td>
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
        </td> <!-- ���ߡ� -->
            <td class='winbox' align='right' nowrap>
                ���ʶ�ʬ
                <select name='targetSeiKubun' onChange='location.replace("<?=$this->menu->out_self(), "?showLine=0&showMenu={$this->request->get('showMenu')}&id={$uniq}"?>"+"&targetSeiKubun="+this.value)' style='text-align:right;'>
                    <option value='0'<?php if ($this->request->get('targetSeiKubun') == '0') echo ' selected'?>>����</option>
                    <option value='1'<?php if ($this->request->get('targetSeiKubun') == '1') echo ' selected'?>>ɸ��</option>
                    <option value='3'<?php if ($this->request->get('targetSeiKubun') == '3') echo ' selected'?>>����</option>
                </select>
                <br>
                ������
                <select name='targetDept' onChange='location.replace("<?=$this->menu->out_self(), "?showLine=0&showMenu={$this->request->get('showMenu')}&id={$uniq}"?>"+"&targetDept="+this.value)' style='text-align:right;'>
                    <option value='0'<?php if ($this->request->get('targetDept') == '0') echo ' selected'?>>������</option>
                    <option value='C'<?php if ($this->request->get('targetDept') == 'C') echo ' selected'?>>���ץ�</option>
                    <option value='L'<?php if ($this->request->get('targetDept') == 'L') echo ' selected'?>>��˥�</option>
                    <option value='T'<?php if ($this->request->get('targetDept') == 'T') echo ' selected'?>>�ġ���</option>
                </select>
            </td>
        </tr> <!-- ���ߡ� -->
    </table>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
        <form name='ControlForm' action='<?=$this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}&id={$uniq}"?>' method='post'>
            <table border='0' width='100%'>
                <tr>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='List' value='�����ײ����' class='pt12b bg'
                        onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=PlanList&{$pageParameter}"?>")'
                    </td>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='Gantt' value='����ȥ��㡼��' class='pt12b bg' style='color:red;'
                        onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=GanttChart&{$pageParameter}"?>")'
                    </td>
                    <td align='center' nowrap width='40%'>
                        <!-- <span class='caption_font'>�����ײ� ����</span> -->
                        <?= $this->model->getDateSpanHTML($this->request->get('targetDate')) ?>
                        <select name='targetDateSpan' onChange='submit()' class='pt12b' style='text-align:right;'>
                            <option value='0'<?php if ($this->request->get('targetDateSpan') == '0') echo ' selected'?>>�Τ�</option>
                            <option value='1'<?php if ($this->request->get('targetDateSpan') == '1') echo ' selected'?>>�ޤ�</option>
                        </select>
                    </td>
                    <td align='center' nowrap width='40%'>
                        <?=$pageControl?>
                    </td>
                </tr>
            </table>
        </form>
        </caption>
        <tr><td> <!-- ���ߡ� #e6e6e6 -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox pt9' width='20'
                onClick='AssemblyScheduleShow.switchAutoReLoad("AssemblyScheduleShow.AjaxLoadTable(\"GanttTable\")", 30000);'
                id='toggleSwitch' onMouseover="this.style.backgroundColor='red'" onMouseout ="this.style.backgroundColor=''"
                title='���̹����� ��ư����ư �����ؤ��ޤ�������å��������MAN(��ư)��AUT(��ư)���ȥ��뼰�����ؤ��ޤ���'
            >
                <label for='toggleSwitch'><span id='toggleView'>AUT</span></label>
            </th>
            <th class='winbox pt12b' width='80' nowrap>&nbsp;</th>
            <th class='winbox pt12b' width='80' nowrap>&nbsp;</th>
            <th class='winbox pt12b' width='180' nowrap
                onClick='AssemblyScheduleShow.zoomGantt("<?php echo $this->menu->out_self()?>")' style='background-Color:teal;'
                id='zoomButton' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='����ȥ��㡼�ȤΤ��̥�����ɥ���ɽ�����ޤ����ޤ����������θ��Ф�����ꤷ�ƥ�������Ǥ��ޤ���'
            >
                <label for='zoomButton'>������ǳ���</label>
            </th>
            <th class='winbox pt12b' width='80' onClick='AssemblyScheduleShow.switchComplete("Gantt")' style='background-Color:darkred;'
                id='CompleteName' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor='darkred'"
                title='ͽ���ʤȴ�λ�ʤ����ؤ�������ɽ��ɽ�����ޤ���'
            >
                <label for='CompleteName' id='CompleteFlag'><?php if ($this->request->get('targetCompleteFlag') == 'no') echo 'ͽ����'; else echo '��λ��'; ?></label>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=GanttChart&targetDateItem=syuka"?>")'
                id='syuka' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='�������������ǡ�������Ф��ƽ���������¤��ؤ��ޤ���'
            >
                <?php if ($this->request->get('targetDateItem') == 'syuka') { ?>
                <label for='syuka' style='background-color:red;'><span id='blink_item'>��������</span></label>
                <?php } else { ?>
                <label for='syuka'>��������</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=GanttChart&targetDateItem=chaku"?>")'
                id='chaku' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='������������ǡ�������Ф������������¤��ؤ��ޤ���'
            >
                <?php if ($this->request->get('targetDateItem') == 'chaku') { ?>
                <label for='chaku' style='background-color:red;'><span id='blink_item'>�������</span></label>
                <?php } else { ?>
                <label for='chaku'>�������</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=GanttChart&targetDateItem=kanryou"?>")'
                id='kanryou' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='��λ���������ǡ�������Ф��ƴ�λ������¤��ؤ��ޤ���'
            >
                <?php if ($this->request->get('targetDateItem') == 'kanryou') { ?>
                <label for='kanryou' style='background-color:red;'><span id='blink_item'>��λ����</span></label>
                <?php } else { ?>
                <label for='kanryou'>��λ����</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='110' nowrap>&nbsp;</th>
        </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
    <span id='showAjax'>
    <?php if ($rows > 0) { ?>
    <table border='0'>
        <tr><td align='center'>
            <?= $this->model->graph->GetHTMLImageMap('myimagemap')?> 
            <?= "<img width='990' src='", $this->model->getGraphName(), "?id={$uniq}' ISMAP USEMAP='#myimagemap' alt='�������塼���ɽ��' border='0'>\n"; ?>
        </td></tr>
    </table>
    <?php } ?>
    </span>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '��Ͽ������ޤ���') { ?>
<?=$this->menu->out_alert_java()?>
<?php } ?>
</html>
