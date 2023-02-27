<?php
//////////////////////////////////////////////////////////////////////////////
// 照会用グループの登録 View部                                              //
// Copyright (C) 2009-2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/11/24 Created  product_serchMaster_View.php                         //
// 2009/11/26 コメントの整理                                                //
// 2010/12/11 大分類グループの追加                                          //
// 2011/11/10 初期フォーカスがグループ番号になるように追加                  //
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
    if (obj.group_no.value.length == 0) {
        alert('グループＮｏ.が入力されていません！');
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    } else if ( !(isDigit(obj.group_no.value)) ) {
        alert('グループNo.は数字以外入力出来ません！');
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    }
    
    if (obj.group_name.value.length == 0) {
        alert('グループ名が入力されていません！');
        obj.group_name.focus();
        obj.group_name.select();
        return false;
    }
    return true;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.entry_form.group_no.focus();
    document.entry_form.group_no.select();
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
        <form name='entry_form' action='product_serchMaster_Main.php' method='post' onSubmit='return chk_entry(this)'>
            <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>
                    グループマスター
                    </td>
                </tr>
                <tr>
                    <?php
                    $field = $result->get_array2('field');
                    for ($i=0; $i<$result->get('num'); $i++) {             // フィールド数分繰返し
                    ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
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
                    <td class='winbox' align='center'><input type='text' class='price_font' name='group_no' value='<?php echo $field = $request->get('group_no') ?>' size='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='group_name' value='<?php echo $field = $request->get('group_name') ?>' size='50'></td>
                    <td class='winbox' align='center'>
                        <span class='caption_font'>
                            <select name='top_code' size='1'>
                            <option value=' '>大分類なし</option>
                            <?php
                            $res_g = $result->get_array2('res_g');
                            for ($i=0; $i<$result->get('rows_g'); $i++) {
                                if ( $res_g[$i][0] == $request->get('top_code')) {
                                    printf("<option value='%s' selected>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                } else {
                                    printf("<option value='%s'>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                }
                            }
                            ?>
                            </select>
                        </span>
                    </td>
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
        検索用大分類グループ未登録
        <B><font color='red'>
        <?php
        echo $result->get('unreg_num');
        ?>
        </B></font>
        件
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '照会') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/product_serchMaster_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
