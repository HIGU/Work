<?php
//////////////////////////////////////////////////////////////////////////////
// 配賦率計算データ追加・編集 View部                                        //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/14 Created  assemblyRate_costAllocation_View.php                 //
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

/* 入力データチェック */
function chk_entry(obj) {
    if (obj.item.value.length == 0) {
        alert('対象グループが入力されていません！');
        obj.item.focus();
        obj.item.select();
        return false;
    }
    
    if(!obj.external_price.value.length){
        alert("外注費が入力されていません。");
        obj.external_price.focus();
        obj.external_price.select();
        return false;
    } else if(!(isDigit(obj.external_price.value))){
        alert("外注費には数値以外の文字は入力出来ません｡");
        obj.external_price.focus();
        obj.external_price.select();
        return false;
    }
    
    if(!obj.external_assy_price.value.length){
        alert("外注ASSY費が入力されていません。");
        obj.external_assy_price.focus();
        obj.external_assy_price.select();
        return false;
    } else if(!(isDigit(obj.external_assy_price.value))){
        alert("外注ASSY費には数値以外の文字は入力出来ません｡");
        obj.external_assy_price.focus();
        obj.external_assy_price.select();
        return false;
    }
    
    if(!obj.direct_expense.value.length){
        alert("直接費が入力されていません。");
        obj.direct_expense.focus();
        obj.direct_expense.select();
        return false;
    } else if(!(isDigit(obj.direct_expense.value))){
        alert("直接費には数値以外の文字は入力出来ません｡");
        obj.direct_expense.focus();
        obj.direct_expense.select();
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
    <form name='entry_form' method='post' action='assemblyRate_costAllocation_Main.php' onSubmit='return chk_entry(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td bgcolor='#ffffc6' align='center' colspan='20'>
                        <?php
                        echo format_date6_kan($request->get('wage_ym'));
                        ?>
                        配賦率計算データ
                        <font size=2>
                        (単位:円)
                        </font>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>対象グループ</th>
                    <th class='winbox' nowrap>外注費</th>
                    <th class='winbox' nowrap>外注ASSY費</th>
                    <th class='winbox' nowrap>直接費</th>
                    <th class='winbox' colspan='20' nowrap>追加・変更</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='item' value='<?php echo $request->get('item') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='external_price' value='<?php echo $request->get('external_price') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='external_assy_price' value='<?php echo $request->get('external_assy_price') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='direct_expense' value='<?php echo $request->get('direct_expense') ?>' size='15'></td>
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assemblyRate_costAllocation_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='80%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
