<?php
////////////////////////////////////////////////////////////////////////////////
// 食堂メニュー予約（メニュー選択）                                           //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2022-2022 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2022/04/29 Created meal_appli_ViewMenuSelect.php                           //
// 2022/05/07 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>

<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='meal_appli.js'></script>

</head>

<body onLoad='InitSelect(); setInterval("blink_disp(\"blink_item\")", 1000);'>

<center>

    <?php include('meal_appli_ViewCommon.php'); ?>

<form name='form_select' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return true;'>
    <input type='hidden' name='showMenu' id='id_showMenu' value='<?php echo $showMenu ?>'>

    <div>※今週の予約情報は、発注済みのため変更できません。</div>
    <div>予約のキャンセルは、<font style='color:red'>前日の午前中に</font>総務課の担当へ連絡下さい。</div>
    <!-- 食事メニュー選択情報テーブル -->
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- キャプション -->
            <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                <?php for($f=1; $f<6; $f++) { ?>
                    <?php $id = "id_cap_0_{$f}"; $arg = "'$id',$f" ?>
                    <th id='<?php echo $id; ?>'>
                        <script>SetDateInfo(<?php echo $arg; ?>);</script>
                    </th>
                <?php } ?>
            </tr>
            <!-- メニュー -->
            <?php $max = count($menu_name[0]); ?>
            <?php for($r=0; $r<$max; $r++ ) { ?>
                <tr>
                    <?php for($f=1; $f<6; $f++) { ?>
<?php $idx_date = $model->getIndexDate($f); $rowspan = 1; ?>
<?php if( $r==0 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 2; // イベント ?>
<?php } else if( $r==1 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 0; // 何もしない ?>
<?php } ?>
                        <?php $menu_no = $menu_name[0][$r]; $name = "{$menu_no}_0_{$f}"; $id = "id_{$name}"; $id_c = "{$id}_c"; ?>
                        <?php if( !empty($res[$f][$r+3]) ) $cnt = $res[$f][$r+3]; else $cnt = 0; ?>
                        <?php if( $cnt == 0 ) $style = ""; else $style = "background-color:skyblue;"; ?>
<?php if( $rowspan > 0 ) { ?>
                        <td rowspan='<?php echo $rowspan; ?>' style='<?php echo $style; ?>' id='<?php echo $id; ?>'>
    <?php if( $rowspan > 1 ) { ?>
                            イベント
    <?php } else { ?>
                            <?php echo $menu_name[1][$r]; ?>
    <?php } ?>
                            <input type='hidden' id='<?php echo $id_c; ?>' name='<?php echo $name; ?>' value='<?php echo $cnt; ?>'>
                        </td>
<?php } ?>
                    <?php } ?>
                </tr>
            <?php } ?>
        </table>
    </td></tr> <!----------- ダミー(デザイン用) ------------>
    </table>
    <BR>

    <div style='color:red'>【注意】注文したメニューの料金は、食べられなくてもお支払い頂きます!!</div>
    <div>翌週分の予約を週末<font style='color:red'> 13:00 までに</font>行ってください。</div>

    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- キャプション -->
            <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                <th>社員情報</th>
                <th colspan='3'>操作</th>
            </tr>
            <tr>
                <?php if( $input_uid ) { ?>
                <td><?php echo $model->getName($input_uid); ?></td>
                <?php } else { ?>
                <td><input type='text' id='id_input_uid' size='12' maxlength='6' onKeypress='EnterRead();'></td>
                <?php } ?>
                <td><button id='id_read' onClick='OperationClick("read");' <?php echo $btn_read_disabl;?>>読込み</button></td>
                <td><button id='id_cancel' onClick='OperationClick("cancel");' <?php echo $btn_cancel_disabl;?>>キャンセル</button></td>
                <td><button id='id_save' onClick='OperationClick("save");' <?php echo $btn_save_disabl;?>>保　存</button></td>
            </tr>
            <input type='hidden' name='input_uid' value='<?php echo $input_uid; ?>'>
            <input type='hidden' name='read'>
            <input type='hidden' name='cancel'>
            <input type='hidden' name='save'>
        </table>
    </td></tr> <!----------- ダミー(デザイン用) ------------>
    </table>
    <BR>
<?php
// TEST エリア
?>
    <?php if( $input_uid ) { ?>
<?php if($input_uid=="000000"){ ?>
        <?php if( $res_sougou ){ ?>
            <!-- 総合届情報テーブル -->
            <table class='pt10' border="1" cellspacing="0">
            <tr><td> <!----------- ダミー(デザイン用) ------------>
                <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                    <tr class='winbox' style='background-color:DarkCyan; color:White;' align='center'>
                        <th>【総合届情報】</th>
                        <th>日付</th>
                        <th>時間</th>
                        <th>内容</th>
                    </tr>
                    <?php $max = count($res_sougou); ?>
                    <?php for($r=0; $r<$max; $r++ ) { ?>
                        <?php $date_range = $res_sougou[$r][0]; ?>
                        <?php if( $res_sougou[$r][0] != $res_sougou[$r][1] ) $date_range .= " ～ {$res_sougou[$r][1]}"; ?>
                        <?php $time_range = "{$res_sougou[$r][2]} ～ {$res_sougou[$r][3]}"; ?>
                        <?php if($res_sougou[$r][5] == "END" ) { ?>
                            <?php $status = "完了"; ?>
                            <?php $style = "background-color:SkyBlue; color:White;"; ?>
                        <?php } else { ?>
                            <?php $status = "途中"; ?>
                            <?php $style = "background-color:Yellow; color:Blue;"; ?>
                        <?php } ?>
                        <tr>
                            <td align='center' style='<?php echo $style ?>'><?php echo $status; ?></td>
                            <td><?php echo $date_range; ?></td>
                            <td><?php echo $time_range; ?></td>
                            <td><?php echo $res_sougou[$r][4]; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </td></tr> <!----------- ダミー(デザイン用) ------------>
            </table>
            <BR>
        <?php } ?>
<?php } ?>

        <?php for($w=1; $w<3; $w++) { ?>
            <?php if( $w == 0 ) $tr_pointer = ""; else $tr_pointer = "CursorPointer(this)"; ?>
            <!-- 食事メニュー選択テーブル -->
            <table class='pt10' border="1" cellspacing="0">
            <tr><td> <!----------- ダミー(デザイン用) ------------>
                <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                    <!-- キャプション -->
                    <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                        <?php for($f=1; $f<6; $f++) { ?>
                            <?php $id = "id_cap_{$w}_{$f}"; $arg = "'$id',$f+$w*7" ?>
                            <th id='<?php echo $id; ?>'>
                                <script>SetDateInfo(<?php echo $arg; ?>);</script>
                            </th>
                        <?php } ?>
                    </tr>
                    <!-- メニュー -->
                    <?php $max = count($menu_name[0]); ?>
                    <?php for($r=0; $r<$max; $r++ ) { ?>
                        <tr onMouseover='<?php echo $tr_pointer; ?>'>
                            <?php for($f=1; $f<6; $f++) { ?>
<?php $idx_date = $model->getIndexDate($f+$w*7); $rowspan = 1; ?>
<?php if( $r==0 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 2; // イベント ?>
<?php } else if( $r==1 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 0; // 何もしない ?>
<?php } ?>
                                <?php $menu_no = $menu_name[0][$r]; $name = "{$menu_no}_{$w}_{$f}"; $id = "id_{$name}"; $id_c = "{$id}_c"; ?>
                                <?php if( $w == 0 || $order_stop ) $td_click =""; else $td_click ="ChangeBkColoer('$id')"; ?>
                                <?php if( !empty($res[$f+$w*7][$r+3]) ) $cnt = $res[$f+$w*7][$r+3]; else $cnt = 0; ?>
                                <?php if( $cnt == 0 ) $style = ""; else $style = "background-color:skyblue;"; ?>
<?php if( $rowspan > 0 ) { ?>
                                <td rowspan='<?php echo $rowspan; ?>' style='<?php echo $style; ?>' id='<?php echo $id; ?>' onClick="<?php echo $td_click; ?>">
    <?php if( $rowspan > 1 ) { ?>
                                    イベント
    <?php } else { ?>
                                    <?php echo $menu_name[1][$r]; ?>
    <?php } ?>
                                    <input type='hidden' id='<?php echo $id_c; ?>' name='<?php echo $name; ?>' value='<?php echo $cnt; ?>'>
                                </td>
<?php } ?>
                                <?php $cap_id = "id_cap_{$w}_{$f}"; $arg = "'$cap_id','$id'"; ?>
                                <script>CheckHoliday(<?php echo $arg; ?>);</script>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </table>
            </td></tr> <!----------- ダミー(デザイン用) ------------>
            </table>
            <BR>
        <?php } ?>
    <?php } ?>
</form>
    
</center>

</body>
<BR><BR><?php echo $menu->out_alert_java(); ?>
</html>
