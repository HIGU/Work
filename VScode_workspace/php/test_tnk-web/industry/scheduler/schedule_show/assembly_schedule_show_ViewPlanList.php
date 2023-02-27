<?php
//////////////////////////////////////////////////////////////////////////////
// 組立日程計画表(AS/400版)スケジュール 照会  日程計画一覧     MVC View 部  //
// Copyright (C) 2006-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/24 Created   assembly_schedule_show_ViewPlanList.php             //
// 2006/01/26 呼出時にmaterial=1のパラメータで戻り時にpage_keepで行マーカー //
// 2006/02/07 計画残のダブルクリック時の照会に出庫率を追加                  //
// 2006/02/14 自動更新のON・OFF機能を追加。着手日等にチップヘルプを追加     //
// 2006/02/15 自動更新時間を１５秒 → ３０秒へ変更                          //
// 2006/02/16 JavaScriptのAssemblyScheduleShow.switchAutoReLoad()メソッドへ //
// 2006/03/03 AssemblyScheduleShow.switchComplete()追加(完成分の日程表を表示//
// 2006/07/08 out_alert_java()→out_alert_java(false) へ変更 addslashes解除 //
//            して２段以上のメッセージ対応 ラインボタンにpageParameter追加  //
// 2006/10/16 ラインの選択方式追加 プロパティlineMethod, setLineMethod()追加//
// 2006/10/19 Viewロジックを取除くためmodel->showLineNameButton()メソッドAdd//
// 2013/05/20 日程計画一覧表示時にデータが当月作成or当月変更されたものの    //
//            完了日を赤くする為の関数を追加 plan_add_check()          大谷 //
// 2014/05/23 plan_add_check()を分割 追加はplan_add_check()で赤表示         //
//            変更はplan_chage_check()で青表示に変更(カプラ組立依頼)   大谷 //
// 2021/12/16 入庫場所を追加                                           大谷 //
// 2021/12/20 CSV出力を追加                                            大谷 //
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
                        <input type='button' name='List' value='日程計画一覧' class='pt12b bg' style='color:red;'
                        onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=PlanList&{$pageParameter}"?>")'
                    </td>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='Gantt' value='ガントチャート' class='pt12b bg'
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
    <span id='showAjax'>
        <a href="assembly_schedule_show_ViewListCsv.php?csvsearch=<?php echo $csv_search ?>&csvorder=<?php echo $csv_order ?>">
        CSV出力
        </a>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox pt9' width='20'
                onClick='AssemblyScheduleShow.switchAutoReLoad("AssemblyScheduleShow.AjaxLoadTable(\"ListTable\")", 30000);'
                id='toggleSwitch' onMouseover="this.style.backgroundColor='red'" onMouseout ="this.style.backgroundColor=''"
                title='画面更新の 自動・手動 を切替えます。クリックする毎にMAN(手動)・AUT(自動)がトグル式に切替わります。'
            >
                <label for='toggleSwitch'><span id='toggleView'>AUT</span></label>
            </th>
            <th class='winbox pt12b' width='80' nowrap>計画番号</th>
            <th class='winbox pt12b' width='80' nowrap>製品番号</th>
            <th class='winbox pt12b' width='180' nowrap>製　品　名</th>
            <th class='winbox pt12b' width='80' onClick='AssemblyScheduleShow.switchComplete("List")' style='background-Color:darkred;'
                id='CompleteName' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor='darkred'"
                title='未完成分と完成済分を切替えて日程表を表示します。'
            >
                <label for='CompleteName' id='CompleteFlag'><?php if ($this->request->get('targetCompleteFlag') == 'no') echo '計画<BR>残数'; else echo '完成<BR>数'; ?></label>
            </th>
            <th class='winbox pt12b' width='40' nowrap>入庫<BR>場所</th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=PlanList&targetDateItem=syuka"?>")'
                id='syuka' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='集荷日で日程データを抽出して集荷日順に並び替えます。'
            >
                <?php if ($this->request->get('targetDateItem') == 'syuka') { ?>
                <label for='syuka' style='background-color:red;'><span id='blink_item'>集荷日▼</span></label>
                <?php } else { ?>
                <label for='syuka'>集荷日▼</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=PlanList&targetDateItem=chaku"?>")'
                id='chaku' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='着手日で日程データを抽出して着手日順に並び替えます。'
            >
                <?php if ($this->request->get('targetDateItem') == 'chaku') { ?>
                <label for='chaku' style='background-color:red;'><span id='blink_item'>着手日▼</span></label>
                <?php } else { ?>
                <label for='chaku'>着手日▼</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='80' onClick='location.replace("<?=$this->menu->out_self(), "?showMenu=PlanList&targetDateItem=kanryou"?>")'
                id='kanryou' onMouseover="this.style.backgroundColor='blue'" onMouseout ="this.style.backgroundColor=''"
                title='完了日で日程データを抽出して完了日順に並び替えます。'
            >
                <?php if ($this->request->get('targetDateItem') == 'kanryou') { ?>
                <label for='kanryou' style='background-color:red;'><span id='blink_item'>完了日▼</span></label>
                <?php } else { ?>
                <label for='kanryou'>完了日▼</label>
                <?php } ?>
            </th>
            <th class='winbox pt12b' width='110' nowrap>備考</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <?php if ($this->request->get('material_plan_no') == $res[$r][0]) { ?>
            <tr style='background-color:#ffffc6;'>
            <?php } else { ?>
            <tr>
            <?php } ?>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?=$r + 1 + $this->model->get_offset()?></td>
            <!-- 計画番号 -->
            <td class='winbox pt12b' align='right' nowrap>
                <a
                href='<?=$this->menu->out_action('引当構成表'), '?plan_no=', urlencode($res[$r][0]), "&material=1&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='この計画番号の引当部品構成表にジャンプします。';return true;"
                onMouseout="status=''"
                title='この計画番号の引当部品構成表にジャンプします。'
                >
                    <?=$res[$r][0]?>
                </a>
            </td>
            <!-- 製品番号 -->
            <td class='winbox pt12b' align='left' nowrap><?=$res[$r][1]?></td>
            <!-- 製品名 -->
            <td class='winbox pt12b' align='left' nowrap><?=mb_convert_kana($res[$r][2], 'k')?></td>
            <!-- 計画残数 OR 完成数-->
            <td class='winbox pt12b' align='right' nowrap onDblClick='alert("計画数：<?=$res[$r][8]?>\n\n打切数：<?=$res[$r][9]?>\n\n完成数：<?=$res[$r][10]?>\n\n出庫率：<?=$res[$r][11]?>%\n\nです。")'>
                <?php if ($this->request->get('targetCompleteFlag') == 'no') echo $res[$r][3]; else echo $res[$r][10]; ?>
            </td>
            <!-- 入庫場所 -->
            <td class='winbox pt12b' align='center' nowrap><?=$res[$r][13]?></td>
            <!-- 集荷日 -->
            <td class='winbox pt12b' align='center' nowrap><?=$res[$r][4]?></td>
            <!-- 着手日 -->
            <td class='winbox pt12b' align='center' nowrap><?=$res[$r][5]?></td>
            <!-- 完了日 -->
            <?php $cstr_date = date('Ym') . '01' ?>
            <?php if ($this->model->plan_add_check($res[$r][0])) { ?>
                <td class='winbox pt12br' align='center' nowrap><font color='red'><?=$res[$r][6]?></font></td>
            <?php } elseif ($this->model->plan_change_check($res[$r][0])) { ?>
                <td class='winbox pt12br' align='center' nowrap><font color='blue'><?=$res[$r][6]?></font></td>
            <?php } else { ?>
                <td class='winbox pt12b' align='center' nowrap><?=$res[$r][6]?></td>
            <?php } ?>
            <!-- 備考 -->
            <td class='winbox pt12b' align='left' nowrap><?=$res[$r][7]?></td>
            </tr>
        <?php } ?>
        </table>
    </span>
        </td></tr> <!-- ダミー -->
    </table>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '登録がありません！') { ?>
<?=$this->menu->out_alert_java(false)?>
<?php } ?>
</html>
