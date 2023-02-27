<?php
//////////////////////////////////////////////////////////////////////////////
// 資材管理の部品出庫 着手・完了時間 集計用  MVC View 部                    //
//                                          出庫 着手・完了 時間の修正画面  //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/10 Created   parts_pickup_linear_TimeEdit.php                    //
// 2006/04/07 </label> が抜けていた４箇所を修正                             //
// 2006/06/06 parts_pickup_time → parts_pickup_linear へ変更しリニア版作成 //
//            ASP(JSP)タグを廃止して phpの推奨タグへ変更                    //
//////////////////////////////////////////////////////////////////////////////
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
<link rel='stylesheet' href='parts_pickup_linear.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_pickup_linear.js?<?php echo $uniq ?>'></script>
</head>
<body>
<center>
<?php echo $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='6'>
        <tr>
        <form name='ControlForm' action='<?php echo $menu->out_self(), "?id={$uniq}"?>' method='post'>
            <td nowrap <?php if($current_menu=='apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["apend"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='apend' id='apend'
                <?php if($current_menu=='apend') echo 'checked' ?>>
                <label for='apend'>出庫着手入力</label>
            </td>
            <td nowrap <?php if($current_menu=='list') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["list"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='list' id='list'
                <?php if($current_menu=='list') echo 'checked' ?>>
                <label for='list'>出庫着手一覧</label>
            </td>
            <td nowrap <?php if($current_menu=='EndList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["EndList"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='EndList' id='EndList'
                <?php if($current_menu=='EndList') echo 'checked' ?>>
                <label for='EndList'>出庫完了一覧</label>
            </td>
            <td nowrap class='winbox'>
                &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
            </td>
            <td nowrap <?php if($current_menu=='user') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupLinear.ControlFormSubmit(document.ControlForm.elements["user"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='user' id='user'
                <?php if($current_menu=='user') echo 'checked' ?>>
                <label for='user'>作業者登録</label>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>出庫 着手・完了時間の修正</caption>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='8'>
        <form name='timeEdit_form' action='<?php echo $menu->out_self(), "?current_menu=EndList&{$pageParm}"?>' method='post'>
            <input type='hidden' name='serial_no' value='<?php echo $serial_no?>'>
            <tr>
                <!-- No. -->
                <td class='winbox pt14b' align='center' nowrap>1</td>
                <th class='winbox' width='100'>製品名</th>
                <!-- 製品名 -->
                <td class='winbox pt14b' align='center' nowrap><?php echo mb_convert_kana($assy_name, 'k')?></td>
                <input type='hidden' name='assy_name' value='<?php echo $assy_name?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>2</td>
                <th class='winbox' nowrap>製品番号</th>
                <!-- 製品番号 -->
                <td class='winbox pt14b' align='center' nowrap><?php echo $assy_no?></td>
                <input type='hidden' name='assy_no' value='<?php echo $assy_no?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' width='30'>3</td>
                <th class='winbox' nowrap>計画番号</th>
                <!-- 計画番号 -->
                <td class='winbox pt14b' align='center' nowrap><?php echo $plan_no?></td>
                <input type='hidden' name='plan_no' value='<?php echo $plan_no?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>4</td>
                <th class='winbox' nowrap>計画数</th>
                <!-- 計画数 -->
                <td class='winbox pt14b' align='center' nowrap><?php echo $plan_pcs?></td>
                <input type='hidden' name='plan_pcs' value='<?php echo $plan_pcs?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>5</td>
                <th class='winbox' nowrap>社員番号</th>
                <!-- 社員番号 -->
                <td class='winbox pt14b' align='center' nowrap><?php echo $user_id?></td>
                <input type='hidden' name='user_id' value='<?php echo $user_id?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>5</td>
                <th class='winbox' nowrap>作業者</th>
                <!-- 作業者 -->
                <td class='winbox pt14b' align='center' nowrap><?php echo $user_name?></td>
                <input type='hidden' name='user_name' value='<?php echo $user_name?>'>
            </tr>
            <tr class='TimeEdit'>
                <td class='winbox pt14b' align='center' nowrap>6</td>
                <th class='winbox' nowrap>出庫着手</th>
                <!-- 出庫着手日時 -->
                <td class='winbox pt14b' align='center' nowrap>
                    <select name='str_year' size='1'>
                        <?php for ($i=($str_year-1); $i<=($str_year+3); $i++) { ?>
                        <?php $data = sprintf('%04d', $i); ?>
                        <option value='<?php echo $data?>'<?php if($str_year==$data)echo' selected' ?>><?php echo $data?></option>
                        <?php } ?>
                    </select>
                    年
                    <select name='str_month' size='1'>
                        <?php for ($i=1; $i<=12; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?php echo $data?>'<?php if($str_month==$data)echo' selected' ?>><?php echo $data?></option>
                        <?php } ?>
                    </select>
                    月
                    <select name='str_day' size='1'>
                        <?php for ($i=1; $i<=31; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?php echo $data?>'<?php if($str_day==$data)echo' selected' ?>><?php echo $data?></option>
                        <?php } ?>
                    </select>
                    日
                    <select name='str_hour' size='1'>
                        <?php for ($i=0; $i<=23; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?php echo $data?>'<?php if($str_hour==$data)echo' selected' ?>><?php echo $data?></option>
                        <?php } ?>
                    </select>
                    時
                    <select name='str_minute' size='1'>
                        <?php for ($i=0; $i<=59; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?php echo $data?>'<?php if($str_minute==$data)echo' selected' ?>><?php echo $data?></option>
                        <?php } ?>
                    </select>
                    分
                    <!-- <?php echo $str_time?> -->
                </td>
            </tr>
            <tr class='TimeEdit'>
                <td class='winbox pt14b' align='center' nowrap>7</td>
                <th class='winbox' nowrap>出庫完了</th>
                <!-- 出庫完了日時 -->
                <td class='winbox pt14b' align='center' nowrap>
                    <select name='end_year' size='1'>
                        <?php for ($i=($end_year-1); $i<=($end_year+3); $i++) { ?>
                        <?php $data = sprintf('%04d', $i); ?>
                        <option value='<?php echo $data?>'<?php if($end_year==$data)echo' selected' ?>><?php echo $data?></option>
                        <?php } ?>
                    </select>
                    年
                    <select name='end_month' size='1'>
                        <?php for ($i=1; $i<=12; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?php echo $data?>'<?php if($end_month==$data)echo' selected' ?>><?php echo $data?></option>
                        <?php } ?>
                    </select>
                    月
                    <select name='end_day' size='1'>
                        <?php for ($i=1; $i<=31; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?php echo $data?>'<?php if($end_day==$data)echo' selected' ?>><?php echo $data?></option>
                        <?php } ?>
                    </select>
                    日
                    <select name='end_hour' size='1'>
                        <?php for ($i=0; $i<=23; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?php echo $data?>'<?php if($end_hour==$data)echo' selected' ?>><?php echo $data?></option>
                        <?php } ?>
                    </select>
                    時
                    <select name='end_minute' size='1'>
                        <?php for ($i=0; $i<=59; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?php echo $data?>'<?php if($end_minute==$data)echo' selected' ?>><?php echo $data?></option>
                        <?php } ?>
                    </select>
                    分
                    <!-- <?php echo $end_time?> -->
                </td>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>8</td>
                <th class='winbox' nowrap>工数(分)</th>
                <!-- 出庫工数(分) -->
                <td class='winbox pt14b' align='center' nowrap><?php echo $pick_time?> 分</td>
                <input type='hidden' name='pick_time' value='<?php echo $pick_time?>'>
            </tr>
            <tr>
                <td class='winbox pg12b' align='center' colspan='3'>
                    <input type='submit' name='timeEdit' value='変更' class='pt12b' style='color:red;'>
                    &nbsp; &nbsp;
                    <input type='button' name='Cancel' value='取消' class='pt12b' onClick='location.replace("<?php echo $menu->out_self(), "?current_menu=EndList&{$pageParm}"?>")'>
                </td>
            </tr>
        </form>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    <?php } ?>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
