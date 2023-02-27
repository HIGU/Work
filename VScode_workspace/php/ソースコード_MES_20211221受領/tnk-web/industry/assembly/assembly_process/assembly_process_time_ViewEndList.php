<?php
//////////////////////////////////////////////////////////////////////////////
// 組立指示メニューの 着手・完了時間 集計用  MVC View 部                    //
//                                              組立完了一覧表              //
// Copyright (C) 2005-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/18 Created   assembly_process_time_ViewEndList.php               //
// 2005/10/18 製品名で全角カナの時 不揃いがあるためmb_convert_kanaで桁合わせ//
//            一覧表内の計画番号以降をpt12b化すると標準サイズから はみ出る  //
// 2005/11/23 ControlFormSubmit()メソッド 二重Submit対策で追加              //
// 2005/11/30 計画数を計画残へ変更。それに伴いダブルクリックで明細照会追加  //
// 2005/12/07 完了の取消を当日までに限定。php6用にASP/JSPタグをphp専用に変更//
// 2006/04/07 </label> が抜けていた４箇所を修正                             //
// 2016/08/08 mouseOverを追加                                          大谷 //
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
<link rel='stylesheet' href='assembly_process_time.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_process_time.js?<?= $uniq ?>'></script>
</head>
<body>
<center>
<?= $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
        <form name='ControlForm' action='<?=$menu->out_self(), "?id={$uniq}"?>' method='post'>
            <td nowrap <?php if($showMenu=='apend') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["apend"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='apend' id='apend'
                <?php if($showMenu=='apend') echo 'checked' ?>>
                <label for='apend'>組立着手入力</label>
            </td>
            <td nowrap <?php if($showMenu=='StartList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["StartList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='StartList' id='StartList'
                <?php if($showMenu=='StartList') echo 'checked' ?>>
                <label for='StartList'>組立着手一覧</label>
            </td>
            <td nowrap <?php if($showMenu=='EndList') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["EndList"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='EndList' id='EndList'
                <?php if($showMenu=='EndList') echo 'checked' ?>>
                <label for='EndList'>組立完了一覧</label>
            </td>
            <td nowrap class='winbox'>
                <?=$pageControl?>
            </td>
            <td nowrap <?php if($showMenu=='group') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='return AssemblyProcessTime.ControlFormSubmit(document.ControlForm.elements["group"], document.ControlForm);'
            >
                <input type='radio' name='showMenu' value='group' id='group'
                <?php if($showMenu=='group') echo 'checked' ?>>
                <label for='group'>グループ編集</label>
            </td>
        </form>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>組立完了 一覧</caption>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
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
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?=$r + 1 + $model->get_offset()?></td>
            <!-- 組立完了の取消 -->
            <td class='winbox pt12b' align='center' nowrap>
                <?php if ($res[$r][14] == '取消有効') { ?>
                <a
                href='<?=$menu->out_self(), "?serial_no={$res[$r][9]}&showMenu=EndList&endCancel=go&plan_no={$res[$r][0]}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onClick='return confirm("完了(中断)の取消をします宜しいですか？")'
                onMouseover="status='組立 完了(中断)の取消を行います。';return true;"
                onMouseout="status=''"
                title='組立 完了(中断)の取消を行います。'
                >
                    取消
                </a>
                <?php } else { ?>
                    確定
                <?php } ?>
            </td>
            <!-- 計画番号 -->
            <td class='winbox pt12b' align='right' nowrap>
                <a
                href='<?=$menu->out_action('引当構成表'), '?plan_no=', urlencode($res[$r][0]), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onMouseover="status='この計画番号の引当部品構成表にジャンプします。';return true;"
                onMouseout="status=''"
                title='この計画番号の引当部品構成表にジャンプします。'
                >
                    <?=$res[$r][0]?>
                </a>
            </td>
                <!-- <td class='winbox' align='right' nowrap><?=$res[$r][0]?></td> -->
            <!-- 製品番号 -->
            <td class='winbox' align='left' nowrap><?=$res[$r][1]?></td>
            <!-- 製品名 -->
            <td class='winbox' align='left' nowrap><?=mb_convert_kana($res[$r][2], 'k')?></td>
            <!-- 計画残数 -->
            <td class='winbox' align='right' nowrap onDblClick='alert("計画残／計画数は\n\n<?=$res[$r][3]?>／<?=$res[$r][13]?>\n\nです。")'>
                <?=$res[$r][3]?>
            </td>
            <!-- 社員番号 -->
            <!-- <td class='winbox' align='center' nowrap><?=$res[$r][4]?></td> -->
            <!-- 作業者 -->
            <td class='winbox' align='left' nowrap><?=$res[$r][5]?></td>
            <!-- 組立着手日時 -->
            <td class='winbox' align='center' nowrap onDblClick='alert("開始時間の詳細\n\n<?=$res[$r][10]?>")'>
                <?=$res[$r][6]?>
            </td>
            <!-- 組立完了日時 -->
            <td class='winbox' align='center' nowrap onDblClick='alert("完了(中断)時間の詳細\n\n<?=$res[$r][11]?>")'>
                <?=$res[$r][7]?>
            </td>
            <!-- 組立工数(分) -->
            <td class='winbox' align='right' nowrap onDblClick='alert("１個あたりの工数\n\n<?=$res[$r][12]?> 分/個")'>
                <?=$res[$r][8]?>
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
<?=$menu->out_alert_java()?>
<?php } ?>
</html>
