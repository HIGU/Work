<?php
//////////////////////////////////////////////////////////////////////////////
// 組立指示メニューの 着手・完了時間 集計用  MVC View 部                    //
//                                                 組立実績の編集 確認画面  //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/14 Created   assembly_time_edit_ViewConfirm.php                  //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<link rel='stylesheet' href='assembly_time_edit.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_edit.js?<?= $uniq ?>'></script>
</head>
<body>
<center>
<?= $menu->out_title_border() ?>
    
    <?php if ($rowsGroup <= 0) { ?>
    <div>&nbsp;</div>
    <div class='pt12b'>組立グループの登録がありません。先に組立グループの登録を行って下さい。</div>
    <?php } else { ?>
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <!-- <caption>組立実績 作業グループの選択</caption> -->
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
    <?php $tr = 0; $column = 6; ?>
    <?php for ($i=0; $i<$rowsGroup; $i++) { ?>
        <?php if ($tr == 0) {?>
        <tr>
        <?php } ?>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='group_name' value='<?=$resGroup[$i][1]?>' class='pt12b bg'
                    onClick='location.replace("<?=$menu->out_self(), "?showGroup={$resGroup[$i][0]}&showMenu=List&id={$uniq}"?>")'
                    <?php if ($resGroup[$i][0] == $request->get('showGroup')) echo 'style=color:red;';?>
                >
            </td>
            <?php $tr++ ?>
        <?php if ($tr >= $column) {?>
        </tr>
        <?php } ?>
        <?php if ($tr >= $column) $tr = 0;?>
    <?php } ?>
    <?php
    if ($tr != 0) {
        while ($tr < $column) {
            echo "            <td class='winbox'>&nbsp;</td>\n";
            $tr++;
        }
        echo "        </tr>\n";
    }
    ?>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
    <?php } ?>
    
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <?php if ($request->get('showMenu') == 'ConfirmApend') { ?>
        <caption>組立実績の追加 データ確認</caption>
        <?php } elseif ($request->get('showMenu') == 'ConfirmEdit') { ?>
        <caption>組立実績の修正 データ確認</caption>
        <?php } elseif ($request->get('showMenu') == 'ConfirmDelete') { ?>
        <caption>組立実績の削除 データ確認</caption>
        <?php } ?>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='8'>
        <form name='Confirm_form' action='<?=$menu->out_self(), "?showMenu=ConfirmApend&{$pageParameter}"?>' method='post'>
            <!-- <input type='hidden' name='serial_no' value='<?=$request->get('serial_no')?>'> -->
            <tr>
                <!-- No. -->
                <td class='winbox pt14b' align='center' nowrap>1</td>
                <th class='winbox' width='100'>製品名</th>
                <!-- 製品名 -->
                <td class='winbox pt14b' align='center' nowrap><?=mb_convert_kana($request->get('assy_name'), 'k')?></td>
                <input type='hidden' name='assy_name' value='<?=$request->get('assy_name')?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>2</td>
                <th class='winbox' nowrap>製品番号</th>
                <!-- 製品番号 -->
                <td class='winbox pt14b' align='center' nowrap><?=$request->get('assy_no')?></td>
                <input type='hidden' name='assy_no' value='<?=$request->get('assy_no')?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' width='30'>3</td>
                <th class='winbox' nowrap>計画番号</th>
                <!-- 計画番号 -->
                <td class='winbox pt14b' align='center' nowrap>
                    <input type='text' name='plan_no' value='<?=$request->get('plan_no')?>' size='10' maxlength='8'
                        style='ime-mode:disabled; background-color:#e6e6e6;' class='pt14b' onChange='this.value=this.value.toUpperCase()'
                        readonly
                    >
                </td>
                <!-- <input type='hidden' name='plan_no' value='<?=$request->get('plan_no')?>'> -->
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>4</td>
                <th class='winbox' nowrap>計画数</th>
                <!-- 計画数 -->
                <td class='winbox pt14b' align='center' nowrap><?=$request->get('plan')?></td>
                <input type='hidden' name='plan' value='<?=$request->get('plan')?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>5</td>
                <th class='winbox' nowrap>社員番号</th>
                <!-- 社員番号 -->
                <td class='winbox pt14b' align='center' nowrap>
                    <input type='text' name='user_id' value='<?=$request->get('user_id')?>' size='8' maxlength='6'
                        style='ime-mode:disabled; background-color:#e6e6e6;' class='pt14b' onChange='this.value=this.value.toUpperCase()'
                        readonly
                    >
                </td>
                <!-- <input type='hidden' name='user_id' value='<?=$request->get('user_id')?>'> -->
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>5</td>
                <th class='winbox' nowrap>作業者</th>
                <!-- 作業者 -->
                <td class='winbox pt14b' align='center' nowrap><?=$request->get('user_name')?></td>
                <input type='hidden' name='user_name' value='<?=$request->get('user_name')?>'>
            </tr>
            <tr class='TimeEdit'>
                <td class='winbox pt14b' align='center' nowrap>6</td>
                <th class='winbox' nowrap>組立着手</th>
                <!-- 組立着手日時 -->
                <td class='winbox pt14b' align='center' nowrap>
                    <select name='str_year' size='1' disabled>
                        <?php for ($i=($request->get('str_year')-1); $i<=($request->get('str_year')+3); $i++) { ?>
                        <?php $data = sprintf('%04d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('str_year')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    年
                    <select name='str_month' size='1' disabled>
                        <?php for ($i=1; $i<=12; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('str_month')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    月
                    <select name='str_day' size='1' disabled>
                        <?php for ($i=1; $i<=31; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('str_day')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    日
                    <select name='str_hour' size='1' disabled>
                        <?php for ($i=0; $i<=23; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('str_hour')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    時
                    <select name='str_minute' size='1' disabled>
                        <?php for ($i=0; $i<=59; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('str_minute')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    分
                </td>
            </tr>
            <tr class='TimeEdit'>
                <td class='winbox pt14b' align='center' nowrap>7</td>
                <th class='winbox' nowrap>組立完了</th>
                <!-- 組立完了日時 -->
                <td class='winbox pt14b' align='center' nowrap>
                    <select name='end_year' size='1' disabled>
                        <?php for ($i=($request->get('end_year')-1); $i<=($request->get('end_year')+3); $i++) { ?>
                        <?php $data = sprintf('%04d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('end_year')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    年
                    <select name='end_month' size='1' disabled>
                        <?php for ($i=1; $i<=12; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('end_month')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    月
                    <select name='end_day' size='1' disabled>
                        <?php for ($i=1; $i<=31; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('end_day')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    日
                    <select name='end_hour' size='1' disabled>
                        <?php for ($i=0; $i<=23; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('end_hour')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    時
                    <select name='end_minute' size='1' disabled>
                        <?php for ($i=0; $i<=59; $i++) { ?>
                        <?php $data = sprintf('%02d', $i); ?>
                        <option value='<?=$data?>'<?php if($request->get('end_minute')==$data)echo' selected' ?>><?=$data?></option>
                        <?php } ?>
                    </select>
                    分
                </td>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>8</td>
                <th class='winbox' nowrap>工数(分)</th>
                <!-- 合計工数(分) -->
                <td class='winbox pt14b' align='center' nowrap><?=$request->get('assy_time')?> 分</td>
                <input type='hidden' name='assy_time' value='<?=$request->get('assy_time')?>'>
            </tr>
            <tr>
                <td class='winbox pg12b' align='center' colspan='3'>
                    <?php if ($request->get('showMenu') == 'ConfirmApend') { ?>
                    <input type='hidden' name='Apend' value='Dummy'>
                    <input type='submit' name='Apend' value='追加' class='pt12b' style='color:red;'>
                    &nbsp; &nbsp;
                    <input type='button' name='Cancel' value='取消' class='pt12b' onClick='location.replace("<?=$menu->out_self(), "?showMenu=Apend&{$pageParameter}"?>")'>
                    <?php } elseif ($request->get('showMenu') == 'ConfirmEdit') { ?>
                    <input type='hidden' name='Edit' value='Dummy'>
                    <input type='submit' name='Edit' value='変更' class='pt12b' style='color:red;'>
                    &nbsp; &nbsp;
                    <input type='button' name='Cancel' value='取消' class='pt12b' onClick='location.replace("<?=$menu->out_self(), "?showMenu=Edit&serial_no=", $request->get('serial_no'), "&{$pageParameter}"?>")'>
                    <?php } elseif ($request->get('showMenu') == 'ConfirmDelete') { ?>
                    <input type='hidden' name='Delete' value='Dummy'>
                    <input type='submit' name='Delete' value='削除' class='pt12b' style='color:red;'>
                    &nbsp; &nbsp;
                    <input type='button' name='Cancel' value='取消' class='pt12b' onClick='location.replace("<?=$menu->out_self(), "?showMenu=Edit&serial_no=", $request->get('serial_no'), "&{$pageParameter}"?>")'>
                    <?php } ?>
                </td>
            </tr>
        </form>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    <?php } ?>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '登録がありません！') { ?>
<?=$menu->out_alert_java()?>
<?php } ?>
</html>
