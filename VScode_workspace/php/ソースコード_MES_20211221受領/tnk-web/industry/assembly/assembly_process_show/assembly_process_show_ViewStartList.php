<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の作業管理 着手・実績データ 照会   着手一覧画面      MVC View 部     //
//                                      組立実績一覧表 修正・追加のリンク付 //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/19 Created   assembly_process_show_ViewStartList.php             //
// 2006/01/20 <meta に Refresh 15秒を追加  Ajaxのための<span id='showAjax'> //
//            onLoad='setInterval("AssemblyProcessShow.AjaxLoadStart()"を   //
//            追加したため <meta Refreshによるリロードをコメントアウト      //
// 2006/01/24 $pageParameter の後のID=を削除 完了一覧の$pageParameterを削除 //
// 2007/03/19 文字コードの問題のためout_action('引当構成表')→'AlloConfView'//
// 2007/03/26 パラメーターにmaterial=1を追加し、戻り時にpage_keepさせる。   //
//            計画番号クリック時の行番号保存処理を追加                      //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}&{$pageParameter}"?>"> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='assembly_process_show.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='assembly_process_show.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='setInterval("AssemblyProcessShow.AjaxLoadStart()", 15000)'>
<center>
<?php echo $menu->out_title_border() ?>
    
    <?php if ($rowsGroup <= 0) { ?>
    <div>&nbsp;</div>
    <div class='pt12b'>組立グループの登録がありません。先に組立グループの登録を行って下さい。</div>
    <?php } else { ?>
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
        <!-- <caption>組立実績 作業グループの選択</caption> -->
        <tr>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='group_name' value='全て' class='pt12b bg'
                    onClick='location.replace("<?php echo $menu->out_self(), "?showGroup=0&showMenu={$request->get('showMenu')}&id={$uniq}"?>")'
                    <?php if ($request->get('showGroup') == '') echo 'style=color:red;';?>
                >
            </td>
        <td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
    <?php $tr = 0; $column = 6; ?>
    <?php for ($i=0; $i<$rowsGroup; $i++) { ?>
        <?php if ($tr == 0) {?>
        <tr>
        <?php } ?>
            <td class='winbox' align='center' nowrap>
                <input type='button' name='group_name' value='<?php echo $resGroup[$i][1]?>' class='pt12b bg'
                    onClick='location.replace("<?php echo $menu->out_self(), "?showGroup={$resGroup[$i][0]}&showMenu={$request->get('showMenu')}&id={$uniq}"?>")'
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
    
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
        <form name='ControlForm' action='<?php echo $menu->out_self(), "?showMenu={$request->get('showMenu')}&id={$uniq}"?>' method='post'>
            <table border='0' width='100%'>
                <tr>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='List' value='着手一覧' class='pt12b bg' style='color:red;'
                        onClick='location.replace("<?php echo $menu->out_self(), "?showMenu=StartList&{$pageParameter}"?>")'
                    </td>
                    <td align='center' nowrap width='10%'>
                        <input type='button' name='List' value='完了一覧' class='pt12b bg'
                        onClick='location.replace("<?php echo $menu->out_self(), "?showMenu=EndList&id={$uniq}"?>")'
                    </td>
                    <td align='center' nowrap width='40%'>
                        <span class='caption_font'>組立着手 一覧</span>
                    </td>
                    <td align='center' nowrap width='40%'>
                        <?php echo $pageControl?>
                    </td>
                </tr>
            </table>
        </form>
        </caption>
        <tr><td> <!-- ダミー #e6e6e6 -->
    <span id='showAjax'>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox pt12b' width='80' nowrap>計画番号</th>
            <th class='winbox pt12b' width='80' nowrap>製品番号</th>
            <th class='winbox pt12b' width='180' nowrap>製　品　名</th>
            <th class='winbox pt12b' width='80' nowrap>計画残</th>
            <th class='winbox pt12b' width='80' nowrap>社員番号</th>
            <th class='winbox pt12b' width='80' nowrap>作業者</th>
            <th class='winbox pt12b' width='120' nowrap>組立着手</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <?php $recNo = ($r + 1 + $this->model->get_offset() )?>
            <?php if ($session->get_local('recNo') == $recNo) { ?>
            <tr style='background-color:#ffffc6;'>
            <?php } else { ?>
            <tr>
            <?php } ?>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $recNo ?></td>
            <!-- 計画番号 -->
            <td class='winbox pt12b' align='right' nowrap>
                <a
                href='<?php echo "JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}&{$uniq}\"); location.replace(\"", $menu->out_action('AlloConfView'), '?plan_no=', urlencode($res[$r][0]), "&material=1&id={$uniq}\");"?>'
                style='text-decoration:none;'
                onMouseover="status='この計画番号の引当部品構成表にジャンプします。';return true;"
                onMouseout="status=''"
                title='この計画番号の引当部品構成表にジャンプします。'
                >
                    <?php echo $res[$r][0]?>
                </a>
            </td>
            <!-- 製品番号 -->
            <td class='winbox pt12b' align='left' nowrap><?php echo $res[$r][1]?></td>
            <!-- 製品名 -->
            <td class='winbox pt12b' align='left' nowrap><?php echo mb_convert_kana($res[$r][2], 'k')?></td>
            <!-- 計画残数 -->
            <td class='winbox pt12b' align='right' nowrap onDblClick='alert("計画残／計画数は\n\n<?php echo $res[$r][3]?>／<?php echo $res[$r][13]?>\n\nです。")'>
                <?php echo $res[$r][3]?>
            </td>
            <!-- 社員番号 -->
            <td class='winbox pt12b' align='center' nowrap><?php echo $res[$r][4]?></td>
            <!-- 作業者 -->
            <td class='winbox pt12b' align='left' nowrap onDblClick='alert("社員番号\n\n <?php echo $res[$r][4]?>")'>
                <?php echo $res[$r][5]?>
            </td>
            <!-- 組立着手日時 -->
            <td class='winbox pt12b' align='center' nowrap onDblClick='alert("開始時間の詳細\n\n<?php echo $res[$r][10]?>")'>
                <?php echo $res[$r][6]?>
            </td>
            </tr>
        <?php } ?>
        </table>
    </span>
        </td></tr> <!-- ダミー -->
    </table>
</center>
</body>
<?php if ($_SESSION['s_sysmsg'] != '登録がありません！') { ?>
<?php echo $menu->out_alert_java()?>
<?php } ?>
</html>
