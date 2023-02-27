<?php
//////////////////////////////////////////////////////////////////////////////
// 組立日程計画表(AS/400版)スケジュール 照会 ガントチャート    MVC View 部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
//                                      テーブルデータのみ表示  Ajax対応版  //
// Changed history                                                          //
// 2006/02/08 Created   assembly_schedule_show_ViewGanttTable.php           //
// 2006/02/16 自動更新時に画面の位置を保持させるため<img width='990'を追加  //
//////////////////////////////////////////////////////////////////////////////
$_SESSION['s_sysmsg'] = '';     // 見つからなかった時のエラーメッセージを抑止
?>
    <?php if ($rows > 0) { ?>
    <table border='0'>
        <tr><td align='center'>
            <?= $this->model->graph->GetHTMLImageMap('myimagemap')?> 
            <?= "<img width='990' src='", $this->model->getGraphName(), "?id={$uniq}' ISMAP USEMAP='#myimagemap' alt='スケジュールの表示' border='0'>\n"; ?>
        </td></tr>
    </table>
    <?php } ?>
