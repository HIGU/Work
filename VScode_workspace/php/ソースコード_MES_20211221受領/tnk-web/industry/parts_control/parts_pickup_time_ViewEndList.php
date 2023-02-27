<?php
//////////////////////////////////////////////////////////////////////////////
// 資材管理の部品出庫 着手・完了時間 集計用  MVC View 部                    //
//                                              出庫完了一覧表              //
// Copyright (C) 2005-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/25 Created   parts_pickup_time_ViewEndList.php                   //
// 2005/09/27 製品名で全角カナの時 不揃いがあるためmb_convert_kanaで桁合わせ//
//            一覧表内の計画番号以降をpt12b化すると標準サイズから はみ出る  //
// 2005/11/23 ControlFormSubmit()メソッド 二重Submit対策で追加              //
// 2005/12/08 完了の取消を当日までに限定。php6用にASP/JSPタグをphp専用に変更//
// 2005/12/10 着手・完了時間の修正用ロジックを追加 と引き当て構成表のリンク //
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
<link rel='stylesheet' href='parts_pickup_time.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_pickup_time.js?<?= $uniq ?>'></script>
</head>
<body>
<center>
<?= $menu->out_title_border() ?>
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
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
                <?=$pageControl?>
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
        <caption>出庫完了 一覧 &nbsp; &nbsp; &nbsp; (時間の欄をダブルクリックすれば日時を修正できます。)</caption>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>計画番号</th>
            <th class='winbox' nowrap>製品番号</th>
            <th class='winbox' nowrap>製　品　名</th>
            <th class='winbox' nowrap>計画数</th>
            <!-- <th class='winbox' nowrap>社員番号</th> -->
            <th class='winbox' nowrap>作業者</th>
            <th class='winbox' nowrap>出庫着手</th>
            <th class='winbox' nowrap>出庫完了</th>
            <th class='winbox' nowrap>工数(分)</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?=$r + 1 + $model->get_offset()?></td>
            <!-- 出庫完了の取消 -->
            <td class='winbox pt12b' align='center' nowrap>
                <?php if ($res[$r][10] == '取消有効') { ?>
                <a
                href='<?=$menu->out_self(), "?serial_no={$res[$r][8]}&current_menu=EndList&editCancel=go&plan_no={$res[$r][0]}&user_id={$res[$r][4]}&", $model->get_htmlGETparm(), "&id={$uniq}"?>'
                style='text-decoration:none;'
                onClick='return confirm("完了の取消をします宜しいですか？")'
                onMouseover="status='部品出庫 完了の取消を行います。';return true;"
                onMouseout="status=''"
                title='部品出庫 完了の取消を行います。'
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
            <!-- 製品番号 -->
            <td class='winbox' align='left' nowrap><?=$res[$r][1]?></td>
            <!-- 製品名 -->
            <td class='winbox' align='left' nowrap><?=mb_convert_kana($res[$r][2], 'k')?></td>
            <!-- 計画数 -->
            <td class='winbox' align='right' nowrap><?=$res[$r][3]?></td>
            <!-- 社員番号 -->
            <!-- <td class='winbox' align='center' nowrap><?=$res[$r][4]?></td> -->
            <!-- 作業者 -->
            <td class='winbox' align='left' nowrap><?=$res[$r][5]?></td>
            <!-- 出庫着手日時 -->
            <td class='winbox' align='center' nowrap onDblClick='location.replace("<?=$menu->out_self(), "?current_menu=TimeEdit&serial_no={$res[$r][8]}&{$pageParm}"?>");'>
                <?=$res[$r][6]?>
            </td>
            <!-- 出庫完了日時 -->
            <td class='winbox' align='center' nowrap onDblClick='location.replace("<?=$menu->out_self(), "?current_menu=TimeEdit&serial_no={$res[$r][8]}&{$pageParm}"?>");'>
                <?=$res[$r][7]?>
            </td>
            <!-- 出庫工数(分) -->
            <td class='winbox' align='right' nowrap><?=$res[$r][9]?></td>
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
