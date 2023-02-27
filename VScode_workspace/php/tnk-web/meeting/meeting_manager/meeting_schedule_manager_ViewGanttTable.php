<?php
//////////////////////////////////////////////////////////////////////////////
// 部課長用会議スケジュール照会 ガントチャート    MVC View 部               //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
//                                      テーブルデータのみ表示  Ajax対応版  //
// Changed history                                                          //
// 2010/03/11 Created   meeting_schedule_manager_ViewGanttTable.php         //
//////////////////////////////////////////////////////////////////////////////
$_SESSION['s_sysmsg'] = '';     // 見つからなかった時のエラーメッセージを抑止
    if ($rows > 0) {
        if ($range > 0) {
            for ($r = 1; $r <= $range; $r++) { 
                $gf_name = $g_name . "-{$r}.png";
                $map_name = "#myimagemap" . $r;
        ?>
                <table border='0'>
                    <tr><td align='center'>
                        <?= $model->graph->GetHTMLImageMap($map_name)?> 
                        <!--
                        <?= "<img width='990' src='", $gf_name, "?id={$uniq}' ISMAP USEMAP='#myimagemap", $r, "' alt='スケジュールの表示' border='0'>\n"; ?>
                        -->
                    </td></tr>
                </table>
            <?php } ?>
        <?php } else { ?>
            <table border='0'>
                <tr><td align='center'>
                    <?= $model->graph->GetHTMLImageMap('myimagemap')?> 
                    <!--
                    <?= "<img width='990' src='", $model->getGraphName(), "?id={$uniq}' ISMAP USEMAP='#myimagemap' alt='スケジュールの表示' border='0'>\n"; ?>
                    -->
                </td></tr>
            </table>
        <?php } ?>
    <?php } ?>
