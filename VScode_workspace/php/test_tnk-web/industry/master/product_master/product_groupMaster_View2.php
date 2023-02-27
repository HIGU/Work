<?php
///////////////////////////////////////////////////////////////////////////////
// 新版 製品グループコード編集 View部                                        //
// 製品グループ（詳細）の検索用グループ設定                                  //
// Copyright (C) 2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp                 //
// Changed history                                                           //
// 2011/05/31 Created  product_groupMaster_View2.php                         //
// 2011/11/10 初期フォーカスが検索用グループになるように追加                 //
//            また、グループコード・グループ名にフォーカスが移らないよう追加 //
///////////////////////////////////////////////////////////////////////////////
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
.rightb{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#e6e6e6';
}
.10pt{
    font:bold 10pt;
    font-family: monospace;
    background-color: '#e6e6e6';
}
-->
</style>
</head>
<body scroll=no>
    <center>
    <?php echo $menu->out_title_border() ?>
    <form name='entry_form' method='post' action='product_groupMaster_Main2.php' onSubmit='return chk_entry(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td bgcolor='#ffffc6' align='center' colspan='20'>
                        製品グループコード 照会用グループ編集
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>グループコード</th>
                    <th class='winbox' nowrap>グループ名</th>
                    <th class='winbox' nowrap>検索用グループ</th>
                    <th class='winbox' colspan='10' nowrap>変更</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='10pt' name='mhgcd' value='<?php echo $request->get('mhgcd') ?>' size='9' maxlength='8' readonly tabindex='-1'></td>
                    <td class='winbox' align='center'><input type='text' class='10pt' name='mhgnm' value='<?php echo $request->get('mhgnm') ?>' size='30' readonly tabindex='-1'></td>
                    <td class='winbox' align='center'>
                        <span class='caption_font'>
                            <select name='mhggp' size='1'>
                            <?php
                            $res_g = $result->get_array2('res_g');
                            for ($i=0; $i<$result->get('rows_g'); $i++) {
                                if ( $res_g[$i][0] == $request->get('mhggp')) {
                                    printf("<option value='%s' selected>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                } else {
                                    printf("<option value='%s'>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                }
                            }
                            ?>
                            <option value=' '>設定解除</option>
                            </select>
                        </span>
                    </td>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='変更'>
                    </td>
                </tr>
             </table>
                </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    検索用グループ未登録
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/product_groupMaster_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
