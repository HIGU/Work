<?php
////////////////////////////////////////////////////////////////////////////////
// 食堂メニュー予約（注文情報詳細）                                           //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2022-2022 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2022/04/29 Created meal_appli_ViewOrderDetail.php                          //
// 2022/05/07 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
$menu->out_html_header();
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
<?php echo $menu->out_jsBaseClass() ?>

<link rel="stylesheet" href="print.css" type="text/css" media="print">
<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='meal_appli.js'></script>

</head>

<body onLoad=''>

<center>
<div class="no-print">

    <?php include('meal_appli_ViewCommon.php'); ?>

<form name='form_detail' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return true;'>
    <input type='hidden' name='showMenu' id='id_showMenu' value='<?php echo $showMenu ?>'>
    <input type='hidden' name='delete_date' id='id_delete_date' value=''>
    <input type='hidden' name='delete_uid' id='id_delete_uid' value=''>
    <input type='hidden' name='delete_who' id='id_delete_who' value=''>
    <input type='hidden' name='delete_menu' id='id_delete_menu' value=''>

    <div class='pt10'>※「定食」「丼・定食」と「麺類」２つに分け印刷、配膳台上にあるバインダーに挟む。</div>
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- キャプション -->
            <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                <th>情報</th>
                <th colspan='3'>操作</th>
            </tr>
            <tr>
                <td>社員番号と氏名の表示切替が可能</td>
                <td align='center'><button id='id_detail' onClick='DetailClick();'>社員番号 <＝> 氏名</button></td>
<?php if($login_uid=="300667") { ?>
<?php } ?>
                <!-- --
                <td><a href="download_file.php/files/掲示用.xlsx">【掲示入力】</a></td>
                <td><input type="button" value="今週分を印刷" onclick='alert("右クリック→ [印刷プレビュー]\n\rサイズ調整後、印刷して下さい。");'></td>
                <td><input type="button" value="今週分を印刷" onclick="window.print();"></td>
                <!-- -->
            </tr>
            <!-- -->
            <tr>
                <td>※印刷方法、方法１（注文後のみ）</td>
                <td>右クリックより印刷プレビュー</td>
            </tr>
            <tr>
                <td>※印刷方法、方法２（いつでも）</td>
                <td>リンクを開き編集：<a href='JavaScript:void(0)' onClick='win_open("files/bulletin.xlsx", "bulletin")' title='掲示する内容を作成する。'>【掲示用作成】</a></td>
            </tr>
            <!-- --
            <tr>
                <td>※印刷方法、リンクを開き編集</td>
                <td align='center'><a href='JavaScript:void(0)' onClick='win_open("files/bulletin.xlsx", "bulletin")' title='掲示する内容を作成する。'>【掲示用作成】</a></td>
            </tr>
            <!-- -->
            <input type='hidden' name='detail' value='<?php echo $detail; ?>'>
        </table>
    </td></tr> <!----------- ダミー(デザイン用) ------------>
    </table>
    <BR>
</div>
<?php
// TEST エリア
?>
<?php $print_w = 1; ?>
<article>
<div class="no-print">
<b>【重要】</b>当日、自分の社員番号があることを確認、チェックを入れて下さい。<BR><BR>
</div>
    <?php for($w=0; $w<3; $w++) { ?>
        <?php $max = count($menu_name[0]); ?>
        <?php for($m=0; $m<$max; $m++ ) { ?>

        <?php if( $w == $print_w) { ?>
            <?php if( $m==0 || $m==($max-1) ) { ?>
            <section class="print_pages">
            <?php } ?>
        <?php } else { ?>
            <div class="no-print">
        <?php } ?>

        <!-- 食事メニュー選択テーブル -->
        <table class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%' class='winbox_field' bgcolor='#FFFFFF' align='center' border='1' cellspacing='0' cellpadding='3'>
                <!-- キャプション 日付 -->
                <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                    <?php for($f=1; $f<6; $f++) { ?>
                        <?php $id = "id_cap_{$w}_{$f}_{$m}"; $arg = "'$id',$f+$w*7" ?>
                        <th id='<?php echo $id; ?>' colspan='2'>
                            <script>SetDateInfo(<?php echo $arg; ?>);</script>
                        </th>
                    <?php } ?>
                </tr>
                <!-- メニュー -->
                <tr align='center'>
                    <?php for($f=1; $f<6; $f++) { ?>
                        <td nowrap colspan='2'>
                            <?php $idx_date = $model->getIndexDate($f+$w*7); ?>
                            <?php $view_menu = str_replace("・","<BR>",$menu_name[1][$m]); ?>
                            <?php if( $event_date == $idx_date ) { ?>
                                <?php if( $m == 0 ) { ?>
                                    <?php $view_menu = "イベント"; ?>
                                <?php } else if( $m == 1 ) { ?>
                                    <?php $view_menu = "　"; ?>
                                <?php } ?>
                            <?php } ?>
                            <?php echo $view_menu; ?>
                        </td>
                    <?php } ?>
                </tr>
                <!-- 注文数 -->
                <?php $max_cnt = 0; ?>
                <tr align='right'>
                    <?php for($f=1; $f<6; $f++) { ?>
                        <td nowrap id='id_cnt' colspan='2'>
                            <?php if( !empty($res[$f+$w*7][$m+1]) ) $cnt = $res[$f+$w*7][$m+1]; else $cnt = 0; ?>
                            <b>
                            <?php echo $cnt; ?> 食
                            </b>
                        </td>
                        <?php if( $cnt > $max_cnt ) $max_cnt = $cnt; ?>
                    <?php } ?>
                </tr>
                <!-- 注文者情報 -->
                <?php for($n=0; $n<$max_cnt; $n++ ) { ?>
                <tr>
                    <?php for($f=1; $f<6; $f++) { ?>
                        <td align='center'>
                            <?php if( empty($res2[$f+$w*7][$m][$n][0]) ) { ?>
                                <?php echo "　"; ?>
                            <?php } else { ?>
                                <?php echo "□"; ?>
                            <?php } ?>
                        </td>
                        <?php if( empty($res2[$f+$w*7][$m][$n][0]) ) { ?>
                        <td nowrap>
                                <?php echo "　" ?>
                        </td>
                        <?php } else { ?>
                            <?php $uid = $res2[$f+$w*7][$m][$n][0]; ?>
                            <?php $whose = $res2[$f+$w*7][$m][$n][1]; ?>
                            <?php if( $whose == "My" ) { ?>
                                <?php $view = $uid; ?>
                                <?php $name = $model->getName($uid); ?>
                                <?php $name_sub = $model->getName($uid); ?>
                                <?php $title = "{$model->getName($uid)}"; ?>
                            <?php } else { ?>
                                <?php $view = "Guest"; ?>
                                <?php $name = "来客用"; ?>
                                <?php $name_sub = $model->getName($uid) . "（来客用）"; ?>
                                <?php $title = "{$model->getName($uid)}"; ?>
                                <?php if( $res2[$f+$w*7][$m][$n][3] ) { ?>
                                <?php $view = $res2[$f+$w*7][$m][$n][3]; ?>
                                <?php $name = $res2[$f+$w*7][$m][$n][3]; ?>
                                <?php $name_sub = $model->getName($uid) . "（" . $res2[$f+$w*7][$m][$n][3] . "）"; ?>
                                <?php } ?>
                            <?php } ?>
                            <?php $arg = "$f+$w*7, '$name_sub', '{$menu_name[1][$m]}'"; ?>
                        <td nowrap title='<?php echo $title ; ?>' ondblclick="OrderDelete(<?php echo $arg; ?>)">
                            <?php if( $detail == "on" ) { ?>
                                <?php echo $name; ?>
                            <?php } else { ?>
                                <?php echo $view; ?>
                            <?php } ?>
                            <?php if( $whose == "Guest" ) { ?>
                                <?php $x = count($res2[$f+$w*7][$m])-1; ?>
                                <?php for( $r=1; $r<$res2[$f+$w*7][$m][$n][2]; $r++ ) { ?>
                                    <?php $res2[$f+$w*7][$m][$x+$r][0] = $uid; ?>
                                    <?php $res2[$f+$w*7][$m][$x+$r][1] = "Guest"; ?>
                                    <?php $res2[$f+$w*7][$m][$x+$r][2] = 0; ?>
                                    <?php $res2[$f+$w*7][$m][$x+$r][3] = $res2[$f+$w*7][$m][$n][3]; ?>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <?php } ?>
                    <?php } ?>
                </tr>
                <?php } ?>
            </table>
        </td></tr> <!----------- ダミー(デザイン用) ------------>
        </table>
        <BR>
        <?php if( $w == $print_w ) { ?>
            <?php if( $m==1 || $m==($max-1) ) { ?>
            </section>
            <?php } ?>
        <?php } else { ?>
            </div>
        <?php } ?>
        <?php } // for($m<$max) ?>
    <?php } // for($w<3) ?>
</article>

</form>

</center>
</body>
<BR><BR><?php echo $menu->out_alert_java(); ?>
</html>
