<?php
////////////////////////////////////////////////////////////////////////////////
// 食堂メニュー予約（注文情報）                                               //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2022-2022 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2022/04/29 Created meal_appli_ViewOrderInfo.php                            //
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

<body onLoad=''>

<center>

    <?php include('meal_appli_ViewCommon.php'); ?>
    
<form name='form_order' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return true;'>
    <input type='hidden' name='showMenu' id='id_showMenu' value='<?php echo $showMenu ?>'>

    <div>※今週の予約情報は、発注済み。</div>
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
            <?php for($r=0; $r<$max; $r++ ) { ?>
                <tr>
                    <?php for($f=1; $f<6; $f++) { ?>
<?php $idx_date = $model->getIndexDate($f); $rowspan = 1; ?>
<?php if( $r==0 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 2; // イベント ?>
<?php } else if( $r==1 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 0; // 何もしない ?>
<?php } ?>
<?php if( $rowspan > 0 ) { ?>
                        <td rowspan='<?php echo $rowspan; ?>'>
    <?php if( $rowspan > 1 ) { ?>
                            イベント
    <?php } else { ?>
                            <?php echo $menu_name[1][$r]; ?>
    <?php } ?>
                        </td>
                        <td rowspan='<?php echo $rowspan; ?>' align='right' id='id_cnt'>
                            <?php if( !empty($res[$f][$r+1]) ) $cnt = $res[$f][$r+1]; else $cnt = 0; ?>
                            <b>
                            <?php echo $cnt; ?>
                            </b>
                        </td>
<?php } ?>
                    <?php } ?>
                </tr>
            <?php } ?>
        </table>
    </td></tr> <!----------- ダミー(デザイン用) ------------>
    </table>
    <BR>

    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- キャプション -->
            <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                <th>情報</th>
                <!-- -->
                <th colspan='3'>操作</th>
                <!-- -->
            </tr>
            <tr>
                <td>注文：翌週分を<font style='color:red'>週末 13:00～15:00 までに</font>ナスココ（株）へ連絡</td>
                <?php if( $order_stop ) { ?>
                <td align='center'><a href='JavaScript:void(0)' onClick='win_open("files/NextOrder.msg", "NextOrder")' title='注文メールを作成します。' >【注文メール作成】</a></td>
                <?php } else { ?>
                <td align='center'><a href='#' title='予約可能な為、注文できません。' disabled>【注文メール作成】</a></td>
                <?php } ?>
<?php if($login_uid=="300667") { ?>
<?php } ?>
                <!-- --
                <td><button id='id_order' onClick='alert("look");'>メール送信画面</button></td>
                <!-- --
                <td><button id='id_order' onClick='alert("look");'>予約締切</button></td>
                <!-- --
                <td><input type="button" value="Faxできるよう印刷" onclick="window.print();"></td>
                <!-- -->
            </tr>
            <tr>
                <td>変更・キャンセル：<font style='color:red'>前日の 15:00 までに</font>ナスココ（株）へ連絡</td>
                <td align='center'><a href='JavaScript:void(0)' onClick='win_open("files/ChangeOrder.msg", "ChangeOrder")' title='メールを作成します。' >【メール作成】</a></td>
            </tr>
            <input type='hidden' name='order'>
        </table>
    </td></tr> <!----------- ダミー(デザイン用) ------------>
    </table>
    <BR>
<?php
// TEST エリア
//echo $model->getEventDate();  // イベント日を取得
//echo $model->getIndexDate(1); // 指定indx日を取得
?>
    <?php $print_week = 2; ?>
    <?php for($w=1; $w<3; $w++) { ?>
        <?php for($f=1; $f<6; $f++) { ?>
            <?php $idx = ($f-date('w'))+$w*7; $date = date('Ymd', strtotime("{$idx} day")); ?>
            <?php if( !$model->IsHoliday($date) ) $print_week = 1; ?>
        <?php } ?>
    <?php } ?>

    <?php for($w=1; $w<3; $w++) { ?>
        <?php if( $w == $print_week ) { ?>
        <article>
        <section class="print_pages">
        <div align='center'>
        <?php } else { ?>
        <div class="no-print" align='center'>
        <?php } ?>
        <!-- 食事メニュー選択テーブル -->
        <table class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                <!-- キャプション -->
                <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                    <?php for($f=1; $f<6; $f++) { ?>
                        <?php $id = "id_cap_{$w}_{$f}"; $arg = "'$id',$f+$w*7" ?>
                        <th id='<?php echo $id; ?>' colspan='2'>
                            <script>SetDateInfo(<?php echo $arg; ?>);</script>
                        </th>
                    <?php } ?>
                </tr>
                <!-- メニュー -->
                <?php $max = count($menu_name[0]); ?>
                <?php for($r=0; $r<$max; $r++ ) { ?>
                    <tr>
                        <?php for($f=1; $f<6; $f++) { ?>

<?php $idx_date = $model->getIndexDate($f+$w*7); $rowspan = 1; ?>
<?php if( $r==0 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 2; // イベント ?>
<?php } else if( $r==1 && $event_date == $idx_date ) { ?>
    <?php $rowspan = 0; // 何もしない ?>
<?php } ?>
<?php if( $rowspan > 0 ) { ?>
                            <td rowspan='<?php echo $rowspan; ?>'>
    <?php if( $rowspan > 1 ) { ?>
                                イベント
    <?php } else { ?>
                                <?php echo $menu_name[1][$r]; ?>
    <?php } ?>
                            </td>
                            <td rowspan='<?php echo $rowspan; ?>' align='right' id='id_cnt'>
                                <?php if( !empty($res[$f+$w*7][$r+1]) ) $cnt = $res[$f+$w*7][$r+1]; else $cnt = 0; ?>
                                <b>
                                <?php echo $cnt; ?>
                                </b>
                            </td>
<?php } ?>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </table>
        </td></tr> <!----------- ダミー(デザイン用) ------------>
        </table>
        <BR>
        <?php if( $w == 1 ) { ?>
        </div>
        </section>
        </article>
        <?php } else { ?>
        </div>
        <?php } ?>
    <?php } ?>

</form>

</center>
</body>
<BR><BR><?php echo $menu->out_alert_java(); ?>
</html>
