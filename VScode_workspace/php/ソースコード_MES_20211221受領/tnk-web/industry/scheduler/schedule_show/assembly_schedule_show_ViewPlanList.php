<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����ײ�ɽ(AS/400��)�������塼�� �Ȳ�  �����ײ����     MVC View ��  //
// Copyright (C) 2006-2014 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/24 Created   assembly_schedule_show_ViewPlanList.php             //
// 2006/01/26 �ƽл���material=1�Υѥ�᡼����������page_keep�ǹԥޡ����� //
// 2006/02/07 �ײ�ĤΥ��֥륯��å����ξȲ�˽и�Ψ���ɲ�                  //
// 2006/02/14 ��ư������ON��OFF��ǽ���ɲá���������˥��åץإ�פ��ɲ�     //
// 2006/02/15 ��ư�������֤򣱣��� �� �����ä��ѹ�                          //
// 2006/02/16 JavaScript��AssemblyScheduleShow.switchAutoReLoad()�᥽�åɤ� //
// 2006/03/03 AssemblyScheduleShow.switchComplete()�ɲ�(����ʬ������ɽ��ɽ��//
// 2006/07/08 out_alert_java()��out_alert_java(false) ���ѹ� addslashes��� //
//            ���ƣ��ʰʾ�Υ�å������б� �饤��ܥ����pageParameter�ɲ�  //
// 2006/10/16 �饤������������ɲ� �ץ�ѥƥ�lineMethod, setLineMethod()�ɲ�//
// 2006/10/19 View���å�����������model->showLineNameButton()�᥽�å�Add//
// 2013/05/20 �����ײ����ɽ�����˥ǡ������������or�����ѹ����줿��Τ�    //
//            ��λ�����֤�����٤δؿ����ɲ� plan_add_check()          ��ë //
// 2014/05/23 plan_add_check()��ʬ�� �ɲä�plan_add_check()����ɽ��         //
//            �ѹ���plan_chage_check()����ɽ�����ѹ�(���ץ���Ω����)   ��ë //
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
        AssemblyScheduleShow.set_focus(document.ControlForm.List, "");
        setInterval("AssemblyScheduleShow.blink_disp(\"blink_item\")", 500);
        AssemblyScheduleShow.CompleteStatus = "<?php echo $this->request->get('targetCompleteFlag') ?>";
        AssemblyScheduleShow.switchAutoReLoad("AssemblyScheduleShow.AjaxLoadTable(\"ListTable\")", 30000);
        <?php if ($this->request->get('targetLineMethod') == '1') echo "AssemblyScheduleShow.lineMethod = \"1\";"; else echo "AssemblyScheduleShow.lineMethod = \"2\";";?>
    '
>
<center>
<?= $this->menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <!-- <caption>��Ω�����ײ�Υ饤���ֹ������</caption> -->
        <tr>
            <td class='winbox' align='center' nowrap>
                <!--
                �饤�����
                <br>
                <select name='targetLineMethod' onChange='AssemblyScheduleShow.setLineMethod(this.value);'>
                    <option value='1'<?php if ($this->request->get('targetLineMethod') == '1') echo ' selected'?>>��������</option>
                    <option value='2'<?php if ($this->request->get('targetLineMethod') == '2') echo ' selected'?>>ʣ������</option>
                </select>
                -->
                <input type='button' name='targetLineMethod' id='lineMethod1' value='��������' onClick='AssemblyScheduleShow.setLineMethod("1");'<?php if ($this->request->get('targetLineMethod') == '1') echo " class='pt12b bg method1'"; else echo " class='pt12b bg method'";?>>
                <br>
                <input type='button' name='targetLineMethod' id='lineMethod2' value='ʣ������' onClick='AssemblyScheduleShow.setLineMethod("2");'<?php if ($this->request->get('targetLineMethod') == '2') echo " class='pt12b bg method2'"; else echo " class='pt12b bg method'";?>>
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
        <?php $this->model->showLineNameButton($this->request, $this->menu, $rowsLine, $resLine, $pageParameter, $uniq); ?>
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
                        <input type='button' name='List' value='�����ײ����' class='pt12b bg' style='color:red;'
                        onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=PlanList&{$pageParameter}"?>")'
                    </td>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='Gantt' value='����ȥ��㡼��' class='pt12b bg'
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
    <span id='showAjax'>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox pt9' width='20'
                onClick='AssemblyScheduleShow.switchAutoReLoad("AssemblyScheduleShow.AjaxLoadTable(\"ListTable\")", 30000);'
                id='toggleSwitch' onMouseover="this.style.backgroundColor='red'" onMouseout ="this.style.backgroundColor=''"
                title='���̹����� ��ư����ư �����ؤ��ޤ�������å��������MAN(��ư)��AUT(��ư)���ȥ��뼰�����ؤ��ޤ���'
            >
                <label for='toggleSwitch'><span id='toggleView'>AUT</span></label>
            </th>
            <th class='winbox pt12b' width='80' nowrap>�ײ��ֹ�</th>
            <th class='winbox pt12b' width='80' nowrap>�����ֹ�</th>
            <th class='winbox pt12b' width='180' nowrap>�����ʡ�̾</th>
            <th class='winbox pt12b' width='80' onClick='AssemblyScheduleShow.switchComplete("List")' style='background-Color:darkred;'
                id='CompleteName' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor='darkred'"
                title='̤����ʬ�ȴ�����ʬ�����ؤ�������ɽ��ɽ�����ޤ���'
            >
                <label for='CompleteName' id='CompleteFlag'><?php if ($this->request->get('targetCompleteFlag') == 'no') echo '�ײ�Ŀ�'; else echo '������'; ?></label>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=PlanList&targetDateItem=syuka"?>")'
                id='syuka' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='�������������ǡ�������Ф��ƽ���������¤��ؤ��ޤ���'
            >
                <?php if ($this->request->get('targetDateItem') == 'syuka') { ?>
                <label for='syuka' style='background-color:red;'><span id='blink_item'>��������</span></label>
                <?php } else { ?>
                <label for='syuka'>��������</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=PlanList&targetDateItem=chaku"?>")'
                id='chaku' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='������������ǡ�������Ф������������¤��ؤ��ޤ���'
            >
                <?php if ($this->request->get('targetDateItem') == 'chaku') { ?>
                <label for='chaku' style='background-color:red;'><span id='blink_item'>�������</span></label>
                <?php } else { ?>
                <label for='chaku'>�������</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=PlanList&targetDateItem=kanryou"?>")'
                id='kanryou' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='��λ���������ǡ�������Ф��ƴ�λ������¤��ؤ��ޤ���'
            >
                <?php if ($this->request->get('targetDateItem') == 'kanryou') { ?>
                <label for='kanryou' style='background-color:red;'><span id='blink_item'>��λ����</span></label>
                <?php } else { ?>
                <label for='kanryou'>��λ����</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='110' nowrap>����</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <?php if ($this->request->get('material_plan_no') == $res[$r][0]) { ?>
            <tr style='background-color:#ffffc6;'>
            <?php } else { ?>
            <tr>
            <?php } ?>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?=$r + 1 + $this->model->get_offset()?></td>
            <!-- �ײ��ֹ� -->
            <td class='winbox pt12b' align='right' nowrap>
                <a
                href='<?=$this->menu->out_action('��������ɽ'), '?plan_no=', urlencode($res[$r][0]), "&material=1&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='���ηײ��ֹ�ΰ������ʹ���ɽ�˥����פ��ޤ���';return true;"
                onMouseout="status=''"
                title='���ηײ��ֹ�ΰ������ʹ���ɽ�˥����פ��ޤ���'
                >
                    <?=$res[$r][0]?>
                </a>
            </td>
            <!-- �����ֹ� -->
            <td class='winbox pt12b' align='left' nowrap><?=$res[$r][1]?></td>
            <!-- ����̾ -->
            <td class='winbox pt12b' align='left' nowrap><?=mb_convert_kana($res[$r][2], 'k')?></td>
            <!-- �ײ�Ŀ� OR ������-->
            <td class='winbox pt12b' align='right' nowrap onDblClick='alert("�ײ����<?=$res[$r][8]?>\n\n���ڿ���<?=$res[$r][9]?>\n\n��������<?=$res[$r][10]?>\n\n�и�Ψ��<?=$res[$r][11]?>%\n\n�Ǥ���")'>
                <?php if ($this->request->get('targetCompleteFlag') == 'no') echo $res[$r][3]; else echo $res[$r][10]; ?>
            </td>
            <!-- ������ -->
            <td class='winbox pt12b' align='center' nowrap><?=$res[$r][4]?></td>
            <!-- ����� -->
            <td class='winbox pt12b' align='center' nowrap><?=$res[$r][5]?></td>
            <!-- ��λ�� -->
            <?php $cstr_date = date('Ym') . '01' ?>
            <?php if ($this->model->plan_add_check($res[$r][0])) { ?>
                <td class='winbox pt12br' align='center' nowrap><font color='red'><?=$res[$r][6]?></font></td>
            <?php } elseif ($this->model->plan_change_check($res[$r][0])) { ?>
                <td class='winbox pt12br' align='center' nowrap><font color='blue'><?=$res[$r][6]?></font></td>
            <?php } else { ?>
                <td class='winbox pt12b' align='center' nowrap><?=$res[$r][6]?></td>
            <?php } ?>
            <!-- ���� -->
            <td class='winbox pt12b' align='left' nowrap><?=$res[$r][7]?></td>
            </tr>
        <?php } ?>
        </table>
    </span>
        </td></tr> <!-- ���ߡ� -->
    </table>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '��Ͽ������ޤ���') { ?>
<?=$this->menu->out_alert_java(false)?>
<?php } ?>
</html>
