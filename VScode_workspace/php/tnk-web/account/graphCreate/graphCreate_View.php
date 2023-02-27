<?php
//////////////////////////////////////////////////////////////////////////////
// 経費内訳の分析用グラフ作成メニュー  グラフの表示  View部                 //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/10/06 Created   graphCreate_Main.php                                //
// 2007/10/07 グラフの値表示・非表示追加。Y軸１個(共用)・２個(別々)を追加   //
// 2007/10/10 if ($session->get_local('g1plot1') != '未設定')   →          //
//            if ($result->get('g1plot1_rows') > 0) へ条件分岐変更          //
// 2007/10/13 X軸の年月をprot1とprot2別々に設定できるオプションを追加       //
// 2007/11/06 損益グラフ作成メニューを経費内訳グラフ作成メニューへ改造      //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<style type='text/css'>
<!--
.pt10b {
    font-size:      0.80em;
    font-weight:    bold;
}
.pt12b {
    font-size:      1.00em;
    font-weight:    bold;
}
select {
    background-color:   teal;
    color:              white;
    font-size:          1.00em;
    font-weight:        bold;
}
body {
    background-image:       url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
    /*overflow-y:             hidden;*/
}
-->
</style>
<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>

        <!----------------- ここは 年月の指定フォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td width='10%' nowrap style='text-align:left;' class='pt10b'>
                    <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='backward' value='前月'<?php echo $result->get('backward')?>>
                                <input type='hidden' name='yyyymm1' value='<?php echo $result->get('pre_yyyymm1') ?>'>
                                <input type='hidden' name='yyyymm2' value='<?php echo $result->get('pre_yyyymm2') ?>'>
                            </td>
                        </table>
                    </form>
                </td>
                <td width='40%' nowrap style='text-align:center;' class='pt12b'>
                    <?php echo getPlotValueOnOff($session, $menu, $uniq) ?>
                </td>
                <td width='40%' nowrap style='text-align:center;' class='pt12b'>
                    <form name='ym_form' method='post' action='<?php echo $menu->out_self() ?>'>
                    <?php if ($session->get_local('dataxFlg') == 'on') $linkAction = 'document.ym_form.yyyymm2.value = document.ym_form.yyyymm1.value; '; else $linkAction = '';?>
                    <span style='color:blue;'>プロット1年月</span><?php echo ymFormCreate($session->get_local('dataxFlg'), $session->get_local('yyyymm1'), 'yyyymm1', "onChange='{$linkAction}document.ym_form.submit()'") ?>
                    <?php if ($session->get_local('dataxFlg') == 'on') echo "<input type='hidden' name='yyyymm2' value='{$session->get_local('yyyymm2')}'>\n";?>
                    <span style='color:red;' >プロット2年月</span><?php echo ymFormCreate($session->get_local('dataxFlg'), $session->get_local('yyyymm2'), 'yyyymm2', "onChange='document.ym_form.submit()'") ?>
                    </form>
                </td>
                <td width='10%' nowrap style='text-align:right;' class='pt10b'>
                    <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='forward' value='次月'<?php echo $result->get('forward') ?>>
                                <input type='hidden' name='yyyymm1' value='<?php echo $result->get('next_yyyymm1') ?>'>
                                <input type='hidden' name='yyyymm2' value='<?php echo $result->get('next_yyyymm2') ?>'>
                            </td>
                        </table>
                    </form>
                </td>
            </tr>
        </table>
        
        <?php if ($result->get('g1plot1_rows') > 0) { ?>
        <!--------------- ここからグラフ１ を表示する -------------------->
        <table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center'>
                    <img src='<?php echo $result->get('graph_name1') . "?" . $uniq ?>' alt='経営資料 グラフ１' border='0'>
                </td>
            </tr>
        </table>
        
        <br>
        
        <!--
        <table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
            <form method='post' action='<?php echo $menu->out_RetUrl()?>'>
                <td align='center'><input class='pt12b' type='submit' name='return' value='戻る'></td>
            </form>
        </table>
        -->
        <?php } ?>
        <?php if ($result->get('g2plot1_rows') > 0) { ?>
        <!--------------- ここからグラフ２ を表示する -------------------->
        <table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center'>
                    <img src='<?php echo $result->get('graph_name2') . "?" . $uniq ?>' alt='経営資料 グラフ２' border='0'>
                </td>
            </tr>
        </table>
        
        <br>
        
        <!--
        <table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
            <form method='post' action='<?php echo $menu->out_RetUrl()?>'>
                <td align='center'><input class='pt12b' type='submit' name='return' value='戻る'></td>
            </form>
        </table>
        -->
        <?php } ?>
        <?php if ($result->get('g3plot1_rows') > 0) { ?>
        <!--------------- ここからグラフ３ を表示する -------------------->
        <table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center'>
                    <img src='<?php echo $result->get('graph_name3') . "?" . $uniq ?>' alt='経営資料 グラフ３' border='0'>
                </td>
            </tr>
        </table>
        <?php } ?>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
