<?php
////////////////////////////////////////////////////////////////////////////////
// 食堂メニュー予約（共通ヘッダー）                                           //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2022-2022 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2022/04/29 Created meal_appli_ViewCommon.php                               //
// 2022/05/07 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
?>

<?= $menu->out_title_border() ?>
<!--
<BR><div class='pt9' align='right'>運用開始日：2022年 6月13日（月）</div>
-->
<!-- ＰＤＦファイルを開く -->
<div class='pt10' align='center'>
<!-- -->
<?php if(getCheckAuthority(69)) { // 権限No.69 総務課員の社員番号（管理部と総務課）?>
<BR>※操作方法が分からない場合、<a href="download_file.php/files/食堂メニュー予約_マニュアル（総務）.pdf">食堂メニュー予約マニュアル</a> <a href="download_file.php/files/食堂メニュー予約_マニュアル（注文）.pdf">（注文）</a>を参考にして下さい。<BR>　
<?php } else { ?>
<BR>※操作方法が分からない場合、<a href="download_file.php/files/食堂メニュー予約_マニュアル（一般）.pdf">食堂メニュー予約マニュアル</a> を参考にして下さい。<BR>　
<?php } ?>

<!-- --
<BR>※操作方法が分からない場合、<a href='JavaScript:void(0)' onClick='win_open("files/meal_manual.pdf", "manual")' title='マニュアルを表示します。'>食堂メニュー予約マニュアル</a> を参考にして下さい。<BR>　
<!-- -->
</div>

<!-- 会社カレンダーの休日情報を、javascriptの変数へセットしておく。-->
<script> var holiday = '<?php echo $holiday; ?>'; </script>
<script> SetHoliday(holiday); </script>
<script> SetEventDate(); </script>

<a href='JavaScript:void(0)' onClick='win_open("files/menu.pdf", "menu")' title='メニューを表示します。'>【メニュー表】</a>
<?php $event_file = "./files/event.pdf"; ?>
<?php $event_file_name = "file://10.1.3.248/総務課/食堂メニュー/イベント/event.pdf"; ?>
<?php if( file_exists($event_file) ) { ?>
    <?php echo "："; ?>
    <a href='JavaScript:void(0)' onClick='win_open("<?php echo $event_file ;?>", "event")' title='イベントメニューを表示します。'><font id='blink_item'>★イベントメニュー★</font></a>
<?php } ?>
<BR>　

<form name='form_main' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return true;'>
    <input type='hidden' name='showMenu' id='id_showMenu' value='<?php echo $showMenu ?>'>
    <!-- 表示項目選択テーブル -->
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- キャプション -->
            <tr>
                <th class='winbox' style='background-color:yellow; color:blue;' colspan='5' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(); ?></div>
                </th>
            </tr>
            <tr>
                <td>
                    <button style='<?php if($showMenu=="MenuSelect") echo "background-color:skyblue"; ?>' onClick='ItemSelect("MenuSelect")'>メニュー選択</button>
                </td>
                <td>
                    <button style='<?php if($showMenu=="MenuGuest") echo "background-color:skyblue"; ?>' onClick='ItemSelect("MenuGuest")'>メニュー選択（来客用）</button>
                </td>
                
                <?php if(getCheckAuthority(69)) { // 権限No.69 総務課員の社員番号（管理部と総務課）?>
                <td>
                    <button style='<?php if($showMenu=="OrderInfo") echo "background-color:skyblue"; ?>' onClick='ItemSelect("OrderInfo")'>注文情報</button>
                </td>
                <td>
                    <button style='<?php if($showMenu=="OrderDetail") echo "background-color:skyblue"; ?>' onClick='ItemSelect("OrderDetail")'>注文情報（詳細）</button>
                </td>
                <?php } ?>
                
            </tr>
        </table>
    </td></tr> <!----------- ダミー(デザイン用) ------------>
    </table>
</form>
<?php
//echo $request->get('showMenu');
if($debug) {
//    echo $request->get('showMenu');
}
?>
<BR>
