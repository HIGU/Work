<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の作業管理実績データ 編集             MVC View 部                    //
//                                      組立実績一覧表 修正・追加のリンク付 //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/13 Created   assembly_time_edit_ViewList.php                     //
// 2006/11/28 group_nameボタンに&{$pageParameter}パラメーターを追加         //
// 2007/09/13 組立実績 一覧のcaption定義を削除 phpのショートを標準タグへ    //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='assembly_time_edit.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_time_edit.js?<?php echo $uniq ?>'></script>
</head>
<body>
<center>
<?php echo $menu->out_title_border() ?>
    
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
                <input type='button' name='group_name' value='<?php echo $resGroup[$i][1]?>' class='pt12b bg'
                    onClick='location.replace("<?php echo $menu->out_self(), "?showGroup={$resGroup[$i][0]}&showMenu=List&{$pageParameter}&id={$uniq}"?>")'
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
        <form name='ControlForm' action='<?php echo $menu->out_self(), "?showMenu=List&id={$uniq}"?>' method='post'>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center' nowrap width='20%'>
                    <input type='button' name='apendForm' value='追加' class='pt12b bg' style='color:blue;'
                    onClick='location.replace("<?php echo $menu->out_self(), "?showMenu=Apend&{$pageParameter}"?>")'
                </td>
                <td align='center' nowrap width='40%'>
                    <span class='caption_font'>組立実績 一覧</span>
                </td>
                <td align='center' nowrap width='40%'>
                    <?php echo $pageControl?>
                </td>
            </tr>
        </table>
        </form>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>計画番号</th>
            <th class='winbox' nowrap>製品番号</th>
            <th class='winbox' nowrap>製　品　名</th>
            <th class='winbox' nowrap>計画残</th>
            <!-- <th class='winbox' nowrap>社員番号</th> -->
            <th class='winbox' nowrap>作業者</th>
            <th class='winbox' nowrap>組立着手</th>
            <th class='winbox' nowrap>完了(中断)</th>
            <th class='winbox' nowrap>工数計(分)</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $r + 1 + $model->get_offset()?></td>
            <!-- 組立実績の修正 -->
            <td class='winbox pt12b' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$res[$r][9]}&showMenu=Edit&user_id={$res[$r][4]}&", $pageParameter?>'
                style='text-decoration:none;'
                onMouseover="status='実績データの修正を行います。';return true;"
                onMouseout="status=''"
                title='実績データの修正を行います。'
                >
                    修正
                </a>
                <!-- onClick='return confirm("実績データの編集を行います。\n\n削除する事も可能です。\n\n宜しいですか？")' -->
            </td>
            <!-- 組立実績の削除 -->
            <td class='winbox pt10' align='center' nowrap>
                <a
                href='<?php echo $menu->out_self(), "?serial_no={$res[$r][9]}&showMenu=ConfirmDelete&ConfirmDelete=go&user_id={$res[$r][4]}&", $pageParameter?>'
                style='text-decoration:none;'
                onMouseover="status='実績データの削除を行います。';return true;"
                onMouseout="status=''"
                title='実績データの削除を行います。'
                >
                    削除
                </a>
            </td>
            <!-- 計画番号 -->
            <td class='winbox pt12b' align='right' nowrap>
                <a
                href='<?php echo $menu->out_action('引当構成表'), '?plan_no=', urlencode($res[$r][0]), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='この計画番号の引当部品構成表にジャンプします。';return true;"
                onMouseout="status=''"
                title='この計画番号の引当部品構成表にジャンプします。'
                >
                    <?php echo $res[$r][0]?>
                </a>
            </td>
            <!-- 製品番号 -->
            <td class='winbox' align='left' nowrap><?php echo $res[$r][1]?></td>
            <!-- 製品名 -->
            <td class='winbox' align='left' nowrap><?php echo mb_convert_kana($res[$r][2], 'k')?></td>
            <!-- 計画残数 -->
            <td class='winbox' align='right' nowrap onDblClick='alert("計画残／計画数は\n\n<?php echo $res[$r][3]?>／<?php echo $res[$r][13]?>\n\nです。")'>
                <?php echo $res[$r][3]?>
            </td>
                <!-- 社員番号 -->
                <!-- <td class='winbox' align='center' nowrap><?php echo $res[$r][4]?></td> -->
            <!-- 作業者 -->
            <td class='winbox' align='left' nowrap onDblClick='alert("社員番号\n\n <?php echo $res[$r][4]?>")'>
                <?php echo $res[$r][5]?>
            </td>
            <!-- 組立着手日時 -->
            <td class='winbox' align='center' nowrap onDblClick='alert("開始時間の詳細\n\n<?php echo $res[$r][10]?>")'>
                <?php echo $res[$r][6]?>
            </td>
            <!-- 組立完了日時 -->
            <td class='winbox' align='center' nowrap onDblClick='alert("完了(中断)時間の詳細\n\n<?php echo $res[$r][11]?>")'>
                <?php echo $res[$r][7]?>
            </td>
            <!-- 組立工数(分) -->
            <td class='winbox' align='right' nowrap onDblClick='alert("１個あたりの工数\n\n<?php echo $res[$r][12]?> 分/個")'>
                <?php echo $res[$r][8]?>
            </td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    <?php } ?>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '登録がありません！') { ?>
<?php echo $menu->out_alert_java()?>
<?php } ?>
</html>
