<?php
//////////////////////////////////////////////////////////////////////////////
// 品名の登録 View部                                                        //
// Copyright (C) 2014-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/11/17 Created  newjis_groupMaster_View.php                          //
// 2014/12/02 微調整                                                        //
// 2014/12/08 品目→形式へ変更                                              //
// 2014/12/22 形式→型式へ変更                                              //
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

/* 入力文字のチェック関数 */
function chk_entry(obj) {
    if (obj.newjis_group_code.value.length == 0) {
        alert('品目コードが入力されていません！');
        obj.newjis_group_code.focus();
        obj.newjis_group_code.select();
        return false;
    } else if ( !(isDigit(obj.newjis_group_code.value)) ) {
        alert('品目コードは数字以外入力出来ません！');
        obj.newjis_group_code.focus();
        obj.newjis_group_code.select();
        return false;
    }
    
    if (obj.newjis_group_code.value.length == 0) {
        alert('品目名が入力されていません！');
        obj.newjis_group_code.focus();
        obj.newjis_group_code.select();
        return false;
    }
    return true;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.entry_form.newjis_group_code.focus();
    document.entry_form.newjis_group_code.select();
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
<body onLoad='set_focus()'>
<body scroll=no>
    <center>
    <?php echo $menu->out_title_border() ?>
        <form name='entry_form' action='newjis_groupMaster_Main.php' method='post' onSubmit='return chk_entry(this)'>
            <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td width='700' bgcolor='#ffffc6' align='center' colspan='8'>
                    型式マスター
                    </td>
                </tr>
                <tr>
                    <?php
                    $field_g = $result->get_array2('field_g');
                    for ($i=0; $i<$result->get('num_g'); $i++) {             // フィールド数分繰返し
                    ?>
                    <th class='winbox' nowrap><?php echo $field_g[$i] ?></th>
                    <?php
                    }
                    ?>
                    <th class='winbox' colspan='2' nowrap>追加・変更</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='newjis_group_code' value='<?php echo $field_g = $request->get('newjis_group_code') ?>' size='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='newjis_apply_code' value='<?php echo $field_g = $request->get('newjis_apply_code') ?>' size='20'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='newjis_kind_name' value='<?php echo $field_g = $request->get('newjis_kind_name') ?>' size='50'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='newjis_certification_code' value='<?php echo $field_g = $request->get('newjis_certification_code') ?>' size='10'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='newjis_period_ym' value='<?php echo $field_g = $request->get('newjis_period_ym') ?>' size='10'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='newjis_group_name' value='<?php echo $field_g = $request->get('newjis_group_name') ?>' size='30'></td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='追加'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='del' value='削除'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                </tr>
            </TBODY>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '照会') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/newjis_groupMaster_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
