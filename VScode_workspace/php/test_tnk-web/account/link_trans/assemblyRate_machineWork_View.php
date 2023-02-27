<?php
//////////////////////////////////////////////////////////////////////////////
// 機械ワークデータ追加・編集 View部                                        //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/13 Created  assemblyRate_machineWork_View.php                    //
//            旧ファイルより$result,$request等の変更                        //
// 2011/06/22 format_date系をtnk_funcに移動のためこちらを削除               //
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

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* 入力データのチェック */
function chk_entry(obj) {
    if(!obj.mac_no.value.length){
        alert("機械番号が入力されていません。");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    } else if(!(isDigit(obj.mac_no.value))){
        alert("機械番号には数値以外の文字は入力出来ません｡");
        obj.mac_no.focus();
        obj.mac_no.select();
        return false;
    }
    
    if(!obj.setup_time.value.length){
        alert("段取時間が入力されていません。");
        obj.setup_time.focus();
        obj.setup_time.select();
        return false;
    } else if(!(isDigit(obj.mac_no.value))){
        alert("段取時間には数値以外の文字は入力出来ません｡");
        obj.setup_time.focus();
        obj.setup_time.select();
        return false;
    }
    
    if(!obj.operation_time.value.length){
        alert("本稼働時間が入力されていません。");
        obj.operation_time.focus();
        obj.operation_time.select();
        return false;
    } else if(!(isDigit(obj.mac_no.value))){
        alert("本稼働時間には数値以外の文字は入力出来ません｡");
        obj.operation_time.focus();
        obj.operation_time.select();
        return false;
    }
    
    if(!obj.repairing_expenses.value.length){
        alert("修繕費が入力されていません。");
        obj.repairing_expenses.focus();
        obj.repairing_expenses.select();
        return false;
    } else if(!(isDigit(obj.repairing_expenses.value))){
        alert("修繕費には数値以外の文字は入力出来ません｡");
        obj.repairing_expenses.focus();
        obj.repairing_expenses.select();
        return false;
    }
    
    return true;
}
// -->
</script>
<style type="text/css">
<!--
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body scroll=no>
    <center>
    <?php echo $menu->out_title_border() ?>
    <form name='entry_form' method='post' action='assemblyRate_machineWork_Main.php' onSubmit='return chk_entry(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td bgcolor='#ffffc6' align='center' colspan='20'>
                        <?php
                        echo format_date6_kan($request->get('wage_ym'));
                        ?>
                        機械ワークデータ
                        <font size=2>
                        (単位:分・円)
                        </font>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>グループ名</th>
                    <th class='winbox' nowrap>機械番号</th>
                    <th class='winbox' nowrap>段取時間</th>
                    <th class='winbox' nowrap>本稼働時間</th>
                    <th class='winbox' nowrap>修繕費</th>
                    <th class='winbox' colspan='20' nowrap>追加・変更</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <span class='caption_font'>
                            <select name='group_no' size='1'>
                            <?php
                            $res_g = $result->get_array2('res_g');
                            for ($i=0; $i<$result->get('rows_g'); $i++) {
                                if ( $res_g[$i][0] == $request->get('group_no')) {
                                    printf("<option value='%s' selected>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                } else {
                                    printf("<option value='%s'>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                }
                            }
                            ?>
                            </select>
                        </span>
                    </td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='mac_no' value='<?php echo $request->get('mac_no') ?>' size='5' maxlength='4'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='setup_time' value='<?php echo $request->get('setup_time') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='operation_time' value='<?php echo $request->get('operation_time') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='repairing_expenses' value='<?php echo $request->get('repairing_expenses') ?>' size='10'></td>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='追加・変更'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='del' value='削除'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                </tr>
             </table>
                </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '照会') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assemblyRate_machineWork_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='80%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
