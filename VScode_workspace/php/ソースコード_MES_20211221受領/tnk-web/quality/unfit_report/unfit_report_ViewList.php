<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ ��Ŭ������ξȲ񡦥��ƥʥ�                                //
//                                          MVC View ��     �ꥹ��ɽ��      //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created   unfit_report_ViewList.php                           //
// 2008/08/29 masterst���ܲ�ư����                                          //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<link rel='stylesheet' href='calendar.css?<?php echo $uniq ?>' type='text/css' media='screen'>
<link rel='stylesheet' href='unfit_report.css?<?php echo $uniq ?>' type='text/css' media='screen'>
<script type='text/javascript' src='unfit_report.js?=<?php echo $uniq ?>'></script>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>
</head>
<body onLoad='set_focus()'>
<center>
<?php echo $menu->out_title_border() ?>
    
    <table border='0' align='center'>
        <tr>
        <td valign='top'>
            <?php echo $calendar_pre->show_calendar($day_pre['year'], $day_pre['mon']);?>
        </td>
        <td valign='top'>
            <?php echo $calendar_now->show_calendar($day_now['year'], $day_now['mon'], $day_now['mday']);?>
        </td>
        <td valign='top'>
            <?php echo $calendar_nex1->show_calendar($day_nex1['year'], $day_nex1['mon']);?>
        </td>
        <td valign='top'>
            <?php echo $calendar_nex2->show_calendar($day_nex2['year'], $day_nex2['mon']);?>
        </td>
        </tr>
    </table>
    
    <form name='ControlForm' action='<?php echo $menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='get'>
    <table bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
    <table class='winbox_field' bgcolor='e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <td nowrap <?php if($showMenu=='Apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["Apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Apend' id='Apend'
                <?php if($showMenu=='Apend') echo 'checked' ?>>
                <label for='Apend'>��������</label>
            </td>
            <td nowrap <?php if($showMenu=='IncompleteList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["IncompleteList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='IncompleteList' id='IncompleteList'
                <?php if($showMenu=='IncompleteList') echo 'checked' ?>>
                <label for='IncompleteList'>�к�̤��λ����</label>
            </td>
            <td nowrap <?php if($showMenu=='CompleteList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["CompleteList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='CompleteList' id='CompleteList'
                <?php if($showMenu=='CompleteList') echo 'checked' ?>>
                <label for='CompleteList'>�к���λ����</label>
            </td>
            <td nowrap <?php if($showMenu=='FollowList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["FollowList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='FollowList' id='FollowList'
                <?php if($showMenu=='FollowList') echo 'checked' ?>>
                <label for='FollowList'>�ե������å״�λ����</label>
            </td>
            <td nowrap <?php if($showMenu=='Group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return UnfitReport.ControlFormSubmit(document.ControlForm.elements["Group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='Group' id='Group'
                <?php if($showMenu=='Group') echo 'checked' ?>>
                <label for='Group'>���롼�פ��Խ�</label>
            </td>
            <input type='hidden' name='year'  value='<?php echo $year?>'>
            <input type='hidden' name='month' value='<?php echo $month?>'>
            <input type='hidden' name='day'   value='<?php echo $day?>'>
            <!----------------- 
            <td nowrap class='winbox' onClick='return UnfitReport.addFavoriteIcon("http://<?php echo $_SERVER['SERVER_ADDR'],$menu->out_self()?>", "<?php echo $_SESSION['User_ID']?>");' id='favi'>
                <label for='favi'>���������ɲ�</label>
            </td>
            ------------------>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ���ߡ�End ------------------>
    
    <div class='caption_font'></div>
    
    <table class='list' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <table border='0' width='100%'>
                <tr>
                    <td align='right' nowrap width='60%'>
                        <?php echo $menu->out_caption()?>
                        &nbsp;&nbsp;
                    </td>
                    <td align='center' nowrap width='40%'>
                        <?php echo $pageControl?>
                    </td>
                </tr>
            </table>
        </caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <?php if ($showMenu=='IncompleteList') { ?>
            <th class='winbox' width='20'>&nbsp;</th>
        <?php } else if (getCheckAuthority(25)) { ?>
            <th class='winbox' width='20'>&nbsp;</th>
        <?php } ?>
        <th class='winbox' width='30'>&nbsp;</th>
        <?php if ($showMenu=='IncompleteList') { ?>
            <th class='winbox' width='320'>��Ŭ������</th>
        <?php } else { ?>
            <th class='winbox' width='250'>��Ŭ������</th>
        <?php } ?>
        <th class='winbox' nowrap>ȯ��ǯ����</th>
        <th class='winbox' nowrap>ȯ�����</th>
        <?php if ($showMenu=='IncompleteList') { ?>
            <th class='winbox' width='70'>������</th>
        <?php } else  { ?>
            <th class='winbox' width='70'>���������</th>
            <th class='winbox' width='70'>�ե������å׺�����</th>
        <?php } ?>
        <th class='winbox' width='70'>�����</th>
        <?php
            if($showMenu=='CompleteList') {
                echo "<th class='winbox' nowrap>�ե������å�<BR>�����դ�ͽ����</th>";
            } else if($showMenu=='FollowList'){
                echo "<th class='winbox' nowrap>�ե������å�<BR>��λǯ����</th>";
            } else {
                echo "<th class='winbox' nowrap>�к��»�</th>";
            }
            if($showMenu!='FollowList') {
                echo "<th class='winbox' width='70'>������</th>";
            }
        if ($rows >= 1) {
            for ($r=0; $r<$rows; $r++) {
                  if ($res[$r][15] == 'f') { ?>
            <tr>
            <?php } else { ?>
            <tr>
            <?php } ?>
            <td class='winbox' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <?php if ($showMenu=='IncompleteList') { ?>
            <td class='winbox' align='center' nowrap>
                <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&year={$year}&month={$month}&day={$day}&showMenu=Edit&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>�Խ�
                </a>
            </td>
            <?php } else if (getCheckAuthority(25)) { 
                       if ($showMenu=='FollowList') {   ?>
            <td class='winbox' align='center' nowrap>
                <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&year={$year}&month={$month}&day={$day}&showMenu=Follow&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>�Խ�
                </a>
            </td>
            <?php      } else { ?>
            <td class='winbox' align='center' nowrap>
                <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&year={$year}&month={$month}&day={$day}&showMenu=Edit&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>�Խ�
                </a>
            </td>
            
            <?php      } ?>
            <?php } ?>
            <!-- ��Ŭ������ -->
            <td class='winbox' align='left'>
                <a href='<?php echo "unfit_report_Print_ja.php?serial_no={$res[$r][0]}&id={$uniq}"?>'
                    style='text-decoration:none;' target='_blank'><B><?php echo $res[$r][1]?><B>
                </a>
            </td>
            <!-- ȯ��ǯ���� -->
            <td class='winbox' align='center'><?php echo $res[$r][2]?></td>
            <!-- ȯ����� -->
            <td class='winbox' align='center'><?php echo $res[$r][4]?></td>
            <!-- ������ -->
            <td class='winbox' align='center' nowrap>
            <?php
                if ($res[$r][5] == $_SESSION['User_ID']) {
                    echo "<span style='color:red;'>{$res[$r][6]}</span>\n";
                } else {
                    echo "{$res[$r][6]}\n";
                }
            ?>
            </td>
            <?php 
            if ($showMenu=='CompleteList') { 
                if ($res[$r][17] == $_SESSION['User_ID']) {
                    if ($res[$r][17] == '' ) {
                        echo "<td class='winbox' align='center' nowrap>---</td>\n";
                    } else {
                        echo "<td class='winbox' align='center' nowrap><span style='color:red;'>{$res[$r][21]}</span></td>\n";
                    }
                } else {
                    if ($res[$r][17] == '' ) {
                        echo "<td class='winbox' align='center' nowrap>---</td>\n";
                    } else {
                        echo "<td class='winbox' align='center' nowrap>{$res[$r][21]}</td>\n";
                    }
                }
            } 
            ?>
            <?php 
            if ($showMenu=='FollowList') { 
                if ($res[$r][17] == $_SESSION['User_ID']) {
                    echo "<td class='winbox' align='center' nowrap><span style='color:red;'>{$res[$r][20]}</span></td>\n";
                } else {
                    echo "<td class='winbox' align='center' nowrap>{$res[$r][20]}</td>\n";
                }
            } 
            ?>
            <!-- ����� -->
            <td class='winbox' align='left' nowrap onDblclick='alert("�᡼������\n\n[ <?php echo $res[$r][14]?> ]\n\n�����ꤵ��Ƥ��ޤ���");'>
                <?php 
                // ������ɽ����������
                //for ($i=0; $i<$rowsAtten[$r]; $i++) {
                //    if ($resAtten[$r][$i][1] == $_SESSION['User_ID']) {
                //        echo "<span style='color:red;'>{$resAtten[$r][$i][2]}</span><br>";
                //    } else {
                //        echo "{$resAtten[$r][$i][2]}<br>";
                //    }
                //}
                //echo "\n";
                ?>
                <?php
                //$num = 0;    // �������Ѥβ�����
                // �������ޤꤿ�����ɽ��������å�
                if ($atten_flg == 1) {
                    if ($res[$r][0] == $serial_no) { 
                        for ($i=0; $i<$rowsAtten[$r]; $i++) {
                            if ($resAtten[$r][$i][1] == $_SESSION['User_ID']) {
                                echo "<span style='color:red;'>{$resAtten[$r][$i][2]}</span><br>";
                            } else {
                                echo "{$resAtten[$r][$i][2]}<br>";
                            }
                        }
                    } else {
                        $myatten     = 0;
                        $myatten_flg = 0;
                        for ($i=0; $i<$rowsAtten[$r]; $i++) {
                            if ($resAtten[$r][$i][1] == $_SESSION['User_ID']) {
                                $myatten     = $i;
                                $myatten_flg = 1;
                            }
                        }
                        if ($myatten_flg == 1) {
                            echo "<span style='color:red;'>{$resAtten[$r][$myatten][2]}</span><br>";
                            if ($rowsAtten[$r] > 1) {
                                $num = $rowsAtten[$r] - 1;
                        ?>
                            <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&atten_flg=1&year={$year}&month={$month}&day={$day}&showMenu={$showMenu}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                            style='text-decoration:none;'>¾<?php echo $num ?>̾
                            </a><br>
                        <?php
                            }
                        } else {
                            echo "{$resAtten[$r][0][2]}<br>";
                            if ($rowsAtten[$r] > 1) {
                                $num = $rowsAtten[$r] - 1;
                            ?>
                                <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&atten_flg=1&year={$year}&month={$month}&day={$day}&showMenu={$showMenu}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                                style='text-decoration:none;'>¾<?php echo $num ?>̾
                                </a><br>
                            <?php
                            }
                        }
                    }
                } else {
                    $myatten     = 0;
                    $myatten_flg = 0;
                    for ($i=0; $i<$rowsAtten[$r]; $i++) {
                        if ($resAtten[$r][$i][1] == $_SESSION['User_ID']) {
                            $myatten     = $i;
                            $myatten_flg = 1;
                        }
                    }
                    if ($myatten_flg == 1) {
                        echo "<span style='color:red;'>{$resAtten[$r][$myatten][2]}</span><br>";
                        if ($rowsAtten[$r] > 1) {
                            $num = $rowsAtten[$r] - 1;
                    ?>
                        <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&atten_flg=1&year={$year}&month={$month}&day={$day}&showMenu={$showMenu}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                        style='text-decoration:none;'>¾<?php echo $num ?>̾
                        </a><br>
                    <?php
                        }
                    } else {
                        echo "{$resAtten[$r][0][2]}<br>";
                        if ($rowsAtten[$r] > 1) {
                            $num = $rowsAtten[$r] - 1;
                            ?>
                                <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&atten_flg=1&year={$year}&month={$month}&day={$day}&showMenu={$showMenu}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                                style='text-decoration:none;'>¾<?php echo $num ?>̾
                                </a><br>
                            <?php
                        }
                    }
                }
                ?>
            </td>
            <!-- �к��»� -->
            <td class='winbox' align='center' nowrap>
            <?php
                if ($res[$r][15] == 't') {
                    if ($showMenu=='FollowList') {
            ?>
                <a href='<?php echo "unfit_report_FollowPrint_ja.php?serial_no={$res[$r][0]}&id={$uniq}"?>'
                    style='text-decoration:none;' target='_blank'><B><?php echo $res[$r][19]?><B>
                </a>
            <?php
                    } else {
            ?>
                <B><?php echo $res[$r][16]?>
                </BR>
                <span style='color:blue;'>
                <a href='<?php echo $menu->out_self(), "?serial_no={$res[$r][0]}&year={$year}&month={$month}&day={$day}&showMenu=Follow&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>����
                </a></span></B>
            <?php
                    }
                } else {
                    echo "<B><span style='color:red;'>�к�̤��λ</span></B>";
                }
            ?>
            </td>
            <!-- ������ (ɽ���Ϲ�����������Ȥ��ѹ���) -->
            <?php
            if($showMenu!='FollowList') {
            ?>
                <?php
                if($showMenu=='CompleteList') {
                ?>
            <td class='winbox' align='center' onDblclick='alert("��� ��Ͽ����\n\n[ <?php echo $res[$r][19]?> ]\n\n�Ǥ���");'>
                <?php echo $res[$r][20]?>
            </td>
                <?php } else { ?>
            <td class='winbox' align='center' onDblclick='alert("��� ��Ͽ����\n\n[ <?php echo $res[$r][8]?> ]\n\n�Ǥ���");'>
                <?php echo $res[$r][9]?>
            </td>
                <?php } ?>
            </tr>
            <?php } ?>
        <?php } ?>
    <?php 
    } else { 
        if ($showMenu=='CompleteList') { 
    ?>
        <tr><td class='winbox' align='center' colspan='10'>
            <?php echo $noDataMessage, "\n"?>
        </td></tr>
        <?php 
        } else if ($showMenu=='FollowList') { 
        ?>
        <tr><td class='winbox' align='center' colspan='10'>
            <?php echo $noDataMessage, "\n"?>
        </td></tr>
        <?php 
        } else { 
        ?>
        <tr><td class='winbox' align='center' colspan='9'>
            <?php echo $noDataMessage, "\n"?>
        </td></tr>
        <?php } ?>
    <?php } ?>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
    </form>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
