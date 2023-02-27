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
// 2006/03/04 全てAjax対応へ変更 操作時は 処理中メッセージを出す            //
// 2006/04/11 トグルスイッチ表示の未完成と完成済 → 予定品と完了品 へ変更   //
// 2006/06/16 ガントチャートのみを別ウィンドウで開く機能を追加 zoomGantt()  //
// 2006/06/22 ズームで開くにpageParameter追加                               //
// 2006/07/08 out_alert_java()→out_alert_java(false) へ変更 addslashes解除 //
//            して２段以上のメッセージ対応 ラインボタンにpageParameter追加  //
// 2006/10/16 ラインの選択方式追加 プロパティlineMethod, setLineMethod()追加//
//       array_search($resLine[$i][0], $array)を使用してライン配列と比較する//
// 2006/10/19 Viewロジックを取除くためmodel->showLineNameButton()メソッドAdd//
// 2015/05/20 機工対応の為、検索にTを追加                              大谷 //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
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
        AssemblyScheduleShow.CompleteStatus = "<?php echo $this->request->get('targetCompleteFlag') ?>";
        AssemblyScheduleShow.AjaxLoadTableMsg("GanttTable", "<?php if($this->request->get('page_keep') != '')echo'page_keep';?>");
        AssemblyScheduleShow.set_focus(document.ControlForm.Gantt, "");
        setInterval("AssemblyScheduleShow.blink_disp(\"blink_item\")", 500);
        AssemblyScheduleShow.switchAutoReLoad("AssemblyScheduleShow.AjaxLoadTable(\"GanttTable\")", 30000);
        <?php if ($this->request->get('targetLineMethod') == '1') echo "AssemblyScheduleShow.lineMethod = \"1\";"; else echo "AssemblyScheduleShow.lineMethod = \"2\";";?>
    '
>
<center>
<?= $this->menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <!-- <caption>組立日程計画のライン番号の選択</caption> -->
        <tr>
            <td class='winbox' align='center' nowrap>
                <!--
                ライン指定
                <br>
                <select name='targetLineMethod' onChange='AssemblyScheduleShow.setLineMethod(this.value);'>
                    <option value='1'<?php if ($this->request->get('targetLineMethod') == '1') echo ' selected'?>>個別選択</option>
                    <option value='2'<?php if ($this->request->get('targetLineMethod') == '2') echo ' selected'?>>複数選択</option>
                </select>
                -->
                <input type='button' name='targetLineMethod' id='lineMethod1' value='個別選択' onClick='AssemblyScheduleShow.setLineMethod("1");'<?php if ($this->request->get('targetLineMethod') == '1') echo " class='pt12b bg method1'"; else echo " class='pt12b bg method'";?>>
                <br>
                <input type='button' name='targetLineMethod' id='lineMethod2' value='複数選択' onClick='AssemblyScheduleShow.setLineMethod("2");'<?php if ($this->request->get('targetLineMethod') == '2') echo " class='pt12b bg method2'"; else echo " class='pt12b bg method'";?>>
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
        <?php $this->model->showLineNameButton($this->request, $this->menu, $rowsLine, $resLine, $pageParameter, $uniq); ?>
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
                onClick='AssemblyScheduleShow.zoomGantt("<?php echo $this->menu->out_self(), "?{$pageParameter}"?>")' style='background-Color:teal;'
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
    </span>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '登録がありません！') { ?>
<?=$this->menu->out_alert_java(false)?>
<?php } ?>
</html>
