<?php
//////////////////////////////////////////////////////////////////////////////
// 少額資産管理 設置場所マスター追加・編集 View部                           //
// Copyright (C) 2010 Norihisa.Ooya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2010/10/05 Created  smallSum_actMasterMainte_View.php                    //
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
    if (obj.code_place.value.length == 0) {
        alert('設置場所コードが入力されていません！');
        obj.code_place.focus();
        obj.code_place.select();
        return false;
    } else if ( !(isDigit(obj.code_place.value)) ) {
        alert('設置場所コードは数字以外入力出来ません！');
        obj.code_place.focus();
        obj.code_place.select();
        return false;
    }
    
    if (obj.name_place.value.length == 0) {
        alert('設置場所名が入力されていません！');
        obj.name_place.focus();
        obj.name_place.select();
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
        <form name='entry_form' action='smallSum_placeMasterMainte_Main.php' method='post' onSubmit='return chk_entry(this)'>
            <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>
                    設置場所コードマスター
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
                    <th class='winbox' colspan='12' nowrap>追加・変更</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='code_place' value='<?php echo $field_g = $request->get('code_place') ?>' size='8' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='name_place' value='<?php echo $field_g = $request->get('name_place') ?>' size='50'></td>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='追加'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                    <td class='winbox' colspan='6' align='center'>
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/smallSum_placeMasterMainte_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
