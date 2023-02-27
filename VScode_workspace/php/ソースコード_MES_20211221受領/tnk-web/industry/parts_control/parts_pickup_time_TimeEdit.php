<?php
//////////////////////////////////////////////////////////////////////////////
// 資材管理の部品出庫 着手・完了時間 集計用  MVC View 部                    //
//                                          出庫 着手・完了 時間の修正画面  //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/10 Created   parts_pickup_time_TimeEdit.php                      //
// 2006/04/07 </label> が抜けていた４箇所を修正                             //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<link rel='stylesheet' href='parts_pickup_time.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_pickup_time.js?<?= $uniq ?>'></script>
</head>
<body>
<center>
<?= $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='6'>
        <tr>
        <form name='ControlForm' action='<?=$menu->out_self(), "?id={$uniq}"?>' method='post'>
            <td nowrap <?php if($current_menu=='apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupTime.ControlFormSubmit(document.ControlForm.elements["apend"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='apend' id='apend'
                <?php if($current_menu=='apend') echo 'checked' ?>>
                <label for='apend'>出庫着手入力</label>
            </td>
            <td nowrap <?php if($current_menu=='list') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupTime.ControlFormSubmit(document.ControlForm.elements["list"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='list' id='list'
                <?php if($current_menu=='list') echo 'checked' ?>>
                <label for='list'>出庫着手一覧</label>
            </td>
            <td nowrap <?php if($current_menu=='EndList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupTime.ControlFormSubmit(document.ControlForm.elements["EndList"], document.ControlForm);'
            >
                <input type='radio' name='current_menu' value='EndList' id='EndList'
                <?php if($current_menu=='EndList') echo 'checked' ?>>
                <label for='EndList'>出庫完了一覧</label>
            </td>
            <td nowrap class='winbox'>
                &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
            </td>
            <td nowrap <?php if($current_menu=='user') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return PartsPickupTime.ControlFormSubmit(document.ControlForm.elements["user"], document.ControlForm);'
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
        <form name='timeEdit_form' action='<?=$menu->out_self(), "?current_menu=EndList&{$pageParm}"?>' method='post'>
            <input type='hidden' name='serial_no' value='<?=$serial_no?>'>
            <tr>
                <!-- No. -->
                <td class='winbox pt14b' align='center' nowrap>1</td>
                <th class='winbox' width='100'>製品名</th>
                <!-- 製品名 -->
                <td class='winbox pt14b' align='center' nowrap><?=mb_convert_kana($assy_name, 'k')?></td>
                <input type='hidden' name='assy_name' value='<?=$assy_name?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>2</td>
                <th class='winbox' nowrap>製品番号</th>
                <!-- 製品番号 -->
                <td class='winbox pt14b' align='center' nowrap><?=$assy_no?></td>
                <input type='hidden' name='assy_no' value='<?=$assy_no?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' width='30'>3</td>
                <th class='winbox' nowrap>計画番号</th>
                <!-- 計画番号 -->
                <td class='winbox pt14b' align='center' nowrap><?=$plan_no?></td>
                <input type='hidden' name='plan_no' value='<?=$plan_no?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>4</td>
                <th class='winbox' nowrap>計画数</th>
                <!-- 計画数 -->
                <td class='winbox pt14b' align='center' nowrap><?=$plan_pcs?></td>
                <input type='hidden' name='plan_pcs' value='<?=$plan_pcs?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>5</td>
                <th class='winbox' nowrap>社員番号</th>
                <!-- 社員番号 -->
                <td class='winbox pt14b' align='center' nowrap><?=$user_id?></td>
                <input type='hidden' name='user_id' value='<?=$user_id?>'>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>5</td>
                <th class='winbox' nowrap>作業者</th>
                <!-- 作業者 -->
                <td class='winbox pt14b' align='center' nowrap><?=$user_name?></td>
                <input type='hidden' name='user_name' value='<?=$user_name?>'>
            </tr>
            <tr class='TimeEdit'>
                <td class='winbox pt14b' align='center' nowrap>6</td>
                <th class='winbox' nowrap>出庫着手</th>
                <!-- 出庫着手日時 -->
                <td class='winbox pt14b' align='center' nowrap>
                    <select name='str_year' size='1'>
                        <% for ($i=($str_year-1); $i<=($str_year+3); $i++) { %>
                        <% $data = sprintf('%04d', $i); %>
                        <option value='<?=$data?>'<% if($str_year==$data)echo' selected' %>><%=$data%></option>
                        <% } %>
                    </select>
                    年
                    <select name='str_month' size='1'>
                        <% for ($i=1; $i<=12; $i++) { %>
                        <% $data = sprintf('%02d', $i); %>
                        <option value='<?=$data?>'<% if($str_month==$data)echo' selected' %>><%=$data%></option>
                        <% } %>
                    </select>
                    月
                    <select name='str_day' size='1'>
                        <% for ($i=1; $i<=31; $i++) { %>
                        <% $data = sprintf('%02d', $i); %>
                        <option value='<?=$data?>'<% if($str_day==$data)echo' selected' %>><%=$data%></option>
                        <% } %>
                    </select>
                    日
                    <select name='str_hour' size='1'>
                        <% for ($i=0; $i<=23; $i++) { %>
                        <% $data = sprintf('%02d', $i); %>
                        <option value='<?=$data?>'<% if($str_hour==$data)echo' selected' %>><%=$data%></option>
                        <% } %>
                    </select>
                    時
                    <select name='str_minute' size='1'>
                        <% for ($i=0; $i<=59; $i++) { %>
                        <% $data = sprintf('%02d', $i); %>
                        <option value='<?=$data?>'<% if($str_minute==$data)echo' selected' %>><%=$data%></option>
                        <% } %>
                    </select>
                    分
                    <!-- <?=$str_time?> -->
                </td>
            </tr>
            <tr class='TimeEdit'>
                <td class='winbox pt14b' align='center' nowrap>7</td>
                <th class='winbox' nowrap>出庫完了</th>
                <!-- 出庫完了日時 -->
                <td class='winbox pt14b' align='center' nowrap>
                    <select name='end_year' size='1'>
                        <% for ($i=($end_year-1); $i<=($end_year+3); $i++) { %>
                        <% $data = sprintf('%04d', $i); %>
                        <option value='<?=$data?>'<% if($end_year==$data)echo' selected' %>><%=$data%></option>
                        <% } %>
                    </select>
                    年
                    <select name='end_month' size='1'>
                        <% for ($i=1; $i<=12; $i++) { %>
                        <% $data = sprintf('%02d', $i); %>
                        <option value='<?=$data?>'<% if($end_month==$data)echo' selected' %>><%=$data%></option>
                        <% } %>
                    </select>
                    月
                    <select name='end_day' size='1'>
                        <% for ($i=1; $i<=31; $i++) { %>
                        <% $data = sprintf('%02d', $i); %>
                        <option value='<?=$data?>'<% if($end_day==$data)echo' selected' %>><%=$data%></option>
                        <% } %>
                    </select>
                    日
                    <select name='end_hour' size='1'>
                        <% for ($i=0; $i<=23; $i++) { %>
                        <% $data = sprintf('%02d', $i); %>
                        <option value='<?=$data?>'<% if($end_hour==$data)echo' selected' %>><%=$data%></option>
                        <% } %>
                    </select>
                    時
                    <select name='end_minute' size='1'>
                        <% for ($i=0; $i<=59; $i++) { %>
                        <% $data = sprintf('%02d', $i); %>
                        <option value='<?=$data?>'<% if($end_minute==$data)echo' selected' %>><%=$data%></option>
                        <% } %>
                    </select>
                    分
                    <!-- <?=$end_time?> -->
                </td>
            </tr>
            <tr>
                <td class='winbox pt14b' align='center' nowrap>8</td>
                <th class='winbox' nowrap>工数(分)</th>
                <!-- 出庫工数(分) -->
                <td class='winbox pt14b' align='center' nowrap><?=$pick_time?> 分</td>
                <input type='hidden' name='pick_time' value='<?=$pick_time?>'>
            </tr>
            <tr>
                <td class='winbox pg12b' align='center' colspan='3'>
                    <input type='submit' name='timeEdit' value='変更' class='pt12b' style='color:red;'>
                    &nbsp; &nbsp;
                    <input type='button' name='Cancel' value='取消' class='pt12b' onClick='location.replace("<?=$menu->out_self(), "?current_menu=EndList&{$pageParm}"?>")'>
                </td>
            </tr>
        </form>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    <?php } ?>
</center>
</body>
<?=$menu->out_alert_java()?>
</html>
