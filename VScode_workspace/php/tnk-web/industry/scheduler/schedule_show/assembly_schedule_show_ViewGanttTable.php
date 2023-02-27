<?php
//////////////////////////////////////////////////////////////////////////////
// 腟・腴・・ョ・荐・・肢；(AS/400・・)・鴻・宴・吾・ャ・若・・・т・ ・・・潟・・・・・ｃ・若・・    MVC View ・・ //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
//                                      ・・・若・・・・・・・若・帥・・・粋；腓・ Ajax絲上・・・  //
// Changed history                                                          //
// 2006/02/08 Created   assembly_schedule_show_ViewGanttTable.php           //
// 2006/02/16 ・・・・・贋・井・・・・・脂・≪・・・臀・・・篆・・・・・・・・・・・・・<img width='990'・・菴遵・  //
//////////////////////////////////////////////////////////////////////////////
$_SESSION['s_sysmsg'] = '';     // 荀・・ゃ・・・・・・・・・ｃ・・・・・・・・・・・若・＜・・・祉・若・吾・・・・罩・?>
    <?php if ($rows > 0) { ?>
    <table border='0'>
        <tr><td align='center'>
            <?= $this->model->graph->GetHTMLImageMap('myimagemap')?> 
            <?= "<img width='990' src='", $this->model->getGraphName(), "?id={$uniq}' ISMAP USEMAP='#myimagemap' alt='・鴻・宴・吾・ャ・若・・・・；腓・ border='0'>\n"; ?>
        </td></tr>
    </table>
    <?php } ?>
