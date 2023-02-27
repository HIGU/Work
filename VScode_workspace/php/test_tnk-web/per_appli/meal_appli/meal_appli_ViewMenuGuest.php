<?php
////////////////////////////////////////////////////////////////////////////////
// 食堂メニュー予約（メニュー選択：来客用）                                   //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2022-2022 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2022/04/29 Created meal_appli_ViewMenuGuest.php                            //
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

<body onLoad='InitSelect()'>

<center>
    <?php include('meal_appli_ViewCommon.php'); ?>
    
<form name='form_guest' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return true;'>
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
                    <th id='<?php echo $id; ?>' colspan='2'>
                        <script>SetDateInfo(<?php echo $arg; ?>);</script>
                    </th>
                <?php } ?>
            </tr>
            <!-- メニュー -->
            <?php $max = count($menu_name[0]); ?>
            <?php for($r=0; $r<$max+1; $r++ ) { ?>
                <tr>
                    <?php for($f=1; $f<6; $f++) { ?>
<?php $idx_date = $model->getIndexDate($f); $rowspan = 1; ?>
<?php if( $r==0 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 2; // イベント ?>
<?php } else if( $r==1 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 0; // 何もしない ?>
<?php } ?>
                        <?php if( !empty($res[$f][8]) ) $comment[$f] = $res[$f][8]; ?>
                        <?php if($r==$max) { ?>
                            <?php $name_comment = "comment_0_{$f}" ; $id_comment = "id_{$name_comment}"; ?>
                            <td name='<?php echo $name_comment; ?>' id='<?php echo $id_comment; ?>' colspan='2'>
                                理由：<?php if( !empty($comment[$f])) echo "$comment[$f]"; ?>
                            </td>
                        <?php } else { ?>
<?php if( $rowspan > 0 ) { ?>
                            <?php $menu_no = $menu_name[0][$r]; $name = "{$menu_no}_0_{$f}"; $id = "id_{$name}"; $id_c = "{$id}_c"; $cnt=0; ?>
                            <?php if( !empty($res[$f][$r+3]) ) $cnt = $res[$f][$r+3]; else $cnt = 0; ?>
                            <input type='hidden' name='<?php echo $name; ?>' value='<?php echo $cnt; ?>'>
                            <?php if( $cnt == 0 ) $style = ""; else $style = "background-color:skyblue;"; ?>
                            <td rowspan='<?php echo $rowspan; ?>' id='<?php echo $id; ?>' style='<?php echo $style; ?>'>
    <?php if( $rowspan > 1 ) { ?>
                                イベント
    <?php } else { ?>
                                <?php echo $menu_name[1][$r]; ?>
    <?php } ?>
                            </td>
                            <td rowspan='<?php echo $rowspan; ?>' align='right' id='<?php echo $id_c; ?>' style='<?php echo $style; ?>'>
                                <?php echo $cnt; ?>
                            </td>
<?php } ?>
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
                <td><button id='id_save' onClick='if(IsComment()) OperationClick("save"); else return false;' <?php echo $btn_save_disabl;?>>保　存</button></td>
                <!-- --
                <td><button id='id_save' onClick='OperationClick("save");' <?php echo $btn_save_disabl;?>>保　存</button></td>
                <!-- -->
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
        <?php for($w=1; $w<3; $w++) { ?>
            <?php if( $w == 0 || $order_stop ) $btn_pointer = ""; else $btn_pointer = "onMouseover='CursorPointer(this)'"; ?>
            <?php if( $w == 0 ) $btn_disable = " disabled"; else $btn_disable = ""; ?>
            <?php $btn_click = ""; ?>
            
            <!-- 食事メニュー選択テーブル -->
            <table class='pt10' border="1" cellspacing="0">
            <tr><td> <!----------- ダミー(デザイン用) ------------>
                <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                    <!-- キャプション -->
                    <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                        <?php for($f=1; $f<6; $f++) { ?>
                            <?php $id = "id_cap_{$w}_{$f}"; $arg = "'$id',$f+$w*7" ?>
                            <th id='<?php echo $id; ?>' colspan='4'>
                                <script>SetDateInfo(<?php echo $arg; ?>);</script>
                            </th>
                        <?php } ?>
                    </tr>
                    <!-- メニュー -->
                    <?php $comment = array(); ?>
                    <?php $max = count($menu_name[0]); ?>
                    <?php for($r=0; $r<$max+1; $r++ ) { ?>
                        <tr>
                            <?php for($f=1; $f<6; $f++) { ?>
<?php $idx_date = $model->getIndexDate($f+$w*7); $rowspan = 1; ?>
<?php if( $r==0 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 2; // イベント ?>
<?php } else if( $r==1 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 0; // 何もしない ?>
<?php } ?>
                                <?php if( !empty($res[$f+$w*7][8]) ) $comment[$f] = $res[$f+$w*7][8]; ?>
                                <?php if($r==$max) { ?>
                                    <?php $name_comment = "comment_{$w}_{$f}" ; $id_comment = "id_{$name_comment}"; ?>
                                    <td colspan='4'>
                                        理由：<input type='text' size='14' id='<?php echo $id_comment; ?>' name='<?php echo $name_comment; ?>' <?php if( !empty($comment[$f])) echo "value='$comment[$f]'"; ?> <?php if($order_stop) echo "style='background-color:#D8D8D8' readonly"; ?>>
                                    </td>
                                <?php } else { ?>
<?php if( $rowspan > 0 ) { ?>
                                    <?php $menu_no = $menu_name[0][$r]; $name = "{$menu_no}_{$w}_{$f}"; $id = "id_{$name}"; $id_c = "{$id}_c"; $cnt=0; ?>
                                    <?php if( !empty($res[$f+$w*7][$r+3]) ) $cnt = $res[$f+$w*7][$r+3]; else $cnt = 0; ?>
                                    <input type='hidden' name='<?php echo $name; ?>' value='<?php echo $cnt; ?>'>
                                    <?php if( $cnt == 0 ) $style = ""; else $style = "background-color:skyblue;"; ?>
                                    <td rowspan='<?php echo $rowspan; ?>' id='<?php echo $id; ?>' style='<?php echo $style; ?>'>
    <?php if( $rowspan > 1 ) { ?>
                                        イベント
    <?php } else { ?>
                                        <?php echo $menu_name[1][$r]; ?>
    <?php } ?>
                                    </td>
                                    <td rowspan='<?php echo $rowspan; ?>' align='right' id='<?php echo $id_c; ?>' style='<?php echo $style; ?>'>
                                        <?php echo $cnt; ?>
                                    </td>
                                    <?php $idx = ($f-date('w'))+$w*7; $date = date('Ymd', strtotime("{$idx} day")); ?>
                                    <?php if( $model->IsHoliday($date) ) $btn_disable = " disabled"; else $btn_disable = ""; ?>
                                    <?php if( $btn_pointer ) $btn_click ="return CountChange('$name','up')"; ?>
                                    <td rowspan='<?php echo $rowspan; ?>'>
                                        <button <?php echo $btn_pointer . $btn_disable . $order_stop; ?> onClick="<?php echo $btn_click; ?>">＋</button>
                                    </td>
                                    <?php if( $btn_pointer ) $btn_click ="return CountChange('$name','down')"; ?>
                                    <td rowspan='<?php echo $rowspan; ?>'>
                                        <button <?php echo $btn_pointer . $btn_disable . $order_stop; ?> onClick="<?php echo $btn_click; ?>">－</button>
                                    </td>
<?php } ?>
                                <?php } ?>
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
