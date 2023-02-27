<?php
//////////////////////////////////////////////////////////////////////////////
// プログラムマスターの照会・メンテナンス                                   //
//      MVC View 部     一覧表示及び編集部品の選択 インクリメントサーチ対応 //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_input_ViewList.php                       //
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
<link rel='stylesheet' href='progMaster_input.css' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='progMaster_input.js'></script>
</head>
<body onLoad='ProgMaster.setFocus(0)'>
<center>
<?= $menu->out_title_border() ?>
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?=$menu->out_self(), "?id={$uniq}"?>' method='post'>
            <td class='winbox' align='center' nowrap>
                <span <?php if($current_menu=='apend') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                    <input type='radio' name='current_menu' value='apend' id='apend' onClick='submit()'
                    <?php if($current_menu=='apend') echo 'checked' ?>>
                    <label for='apend'>マスター追加
                </span>
            </td>
            <td class='winbox' nowrap>
                <span <?php if($current_menu=='list') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                    <input type='radio' name='current_menu' value='list' id='work' onClick='submit()'
                    <?php if($current_menu=='list') echo 'checked' ?>>
                    <label for='work'>マスター一覧
                </span>
            </td>
            <td class='winbox' nowrap>
                <span <?php if($current_menu=='list') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                    プログラム名
                </span>
                <input class='pt12b' type='text' name='pidKey' value='<?=$pidKey?>' maxlength='18' size='20' style='ime-mode:disabled;'>
            </td>
            <td class='winbox' nowrap>
                <?=$pageControll?>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    <span id='showAjax'>
    <?php
        $res = array(
            [1, "test1", "C:test1\\", "dummy1", "DB1", "DB2", "DB3", "DB4", "DB5", "DB6", "DB7", "DB8", "DB9", "DB10", "DB11", "DB12", 202211],
            [2, "test2", "C:test2\\", "dummy2", "DB1", "DB2", "DB3", "DB4", "DB5", "DB6", "DB7", "DB8", "DB9", "DB10", "DB11", "DB12", 202211],
            [3, "test3", "C:test3\\", "dummy3", "DB1", "DB2", "DB3", "DB4", "DB5", "DB6", "DB7", "DB8", "DB9", "DB10", "DB11", "DB12", 202211],
            [4, "test4", "C:test4\\", "dummy4", "DB1", "DB2", "DB3", "DB4", "DB5", "DB6", "DB7", "DB8", "DB9", "DB10", "DB11", "DB12", 202211],
            [5, "test5", "C:test5\\", "dummy5", "DB1", "DB2", "DB3", "DB4", "DB5", "DB6", "DB7", "DB8", "DB9", "DB10", "DB11", "DB12", 202211],
            [6, "test6", "C:test6\\", "dummy6", "DB1", "DB2", "DB3", "DB4", "DB5", "DB6", "DB7", "DB8", "DB9", "DB10", "DB11", "DB12", 202211],
            [7, "test7", "C:test7\\", "dummy7", "DB1", "DB2", "DB3", "DB4", "DB5", "DB6", "DB7", "DB8", "DB9", "DB10", "DB11", "DB12", 202211],
            [8, "test8", "C:test8\\", "dummy8", "DB1", "DB2", "DB3", "DB4", "DB5", "DB6", "DB7", "DB8", "DB9", "DB10", "DB11", "DB12", 202211],
            [9, "test9", "C:test9\\", "dummy9", "DB1", "DB2", "DB3", "DB4", "DB5", "DB6", "DB7", "DB8", "DB9", "DB10", "DB11", "DB12", 202211],
            [10,"test10","C:test10\\","dummy10","DB1", "DB2", "DB3", "DB4", "DB5", "DB6", "DB7", "DB8", "DB9", "DB10", "DB11", "DB12", 202211],
        );
        $rows = count($res);
    ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <caption>プログラム マスター 一覧d</caption>
                <tr><td> <!-- ダミー -->
                <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <?php
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr></tr>
                    <td class='winbox' align='right' rowspan='5' nowrap><?=$r + 1?></td>
                    <td class='winbox' align='center' rowspan='5' nowrap>
                    <?php $res[$r][0] = str_replace('#', 'シャープ', $res[$r][0]); ?>
                        <a>
                            編集
                        </a>
                    </td>
                    <th class='winbox' nowrap>プログラムID</th>
                    <th class='winbox' nowrap>プログラム名</th>
                    <th class='winbox' nowrap>ディレクトリ</th>
                    </tr>
                    <tr>
                    <?php $res[$r][0] = str_replace('シャープ', '#', $res[$r][0]); ?>
                    <!-- プログラムID -->
                    <td class='winbox' align='left' nowrap><B><?=$res[$r][0]?></B></td>
                    <!-- プログラム名 -->
                    <td class='winbox' align='left' nowrap><?=$res[$r][1]?></td>
                    <!-- ディレクトリ -->
                    <td class='winbox' align='left' nowrap><?=$res[$r][2]?></td>
                    </tr>
                    <tr>
                    <th class='winbox' colspan='2' nowrap>コメント</th>
                    <th class='winbox' nowrap>DB使用</th>
                    </tr>
                    <tr>
                    <!-- コメント -->
                    <td class='winbox' align='left' colspan='2' nowrap><?=$res[$r][3]?></td>
                    <?php
                    $db_use = 0;
                    for ($i=4; $i<16; $i++) {
                        if ($res[$r][$i] != '') {
                            $db_use = 1;
                        }
                    }
                    if ($db_use == 1) {
                    $db_url = 'progMaster_input_db_detail.php?db1='. $res[$r][4] .'&db2='. $res[$r][5] .'&db3='. $res[$r][6] .'&db4='. $res[$r][7] .'&db5='. $res[$r][8] .'&db6='. $res[$r][9] .'&db7='. $res[$r][10] .'&db8='. $res[$r][11] .'&db9='. $res[$r][12] .'&db10='. $res[$r][13] .'&db11='. $res[$r][14] .'&db12='. $res[$r][15];
                    ?>
                    <!-- DB使用 -->
                    <td class='winbox' align='center' nowrap><a href='<?php echo $db_url ?>' onclick="ProgMaster.win_open('<?php echo $db_url ?>', 1000, 440); return false;" title='クリックで使用ＤＢの詳細を表示します。'>○</a></td>
                    </tr>
                    <tr>
                    <th class='winboxb' nowrap>登録日時</th>
                    <td class='winboxb'  colspan='2' align='left' nowrap>　<?=$res[$r][16]?>　</td>
                    </tr>
                <?php } ?>
                <?php } ?>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    </span>
</center>
</body>
<?=$menu->out_alert_java()?>
<script type='text/javascript'>
ProgMaster.GpidKey = 0;
var G_incrementalSearch = true;
var G_UpperSwitch = "list";
</script>
</html>
