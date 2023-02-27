<?php
//////////////////////////////////////////////////////////////////////////////
// 間接費配賦率 照会 View部 assemblyRate_actAllocate_View.php               //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/06 Created  assemblyRate_actAllocate_View.php                    //
// 2007/12/29 初期値のフォーカスを決算処理の終了年月に変更                  //
// 2011/06/22 format_date系をtnk_funcに移動のためこちらを削除               //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<script type='text/javascript' src='assemblyRate_actAllocate.js'></script>
<link rel='stylesheet' href='assemblyRate_actAllocate.css' type='text/css' media='screen'>
</head>
<body onLoad='document.kessan_form.end_ym.focus()' scroll=no>
    <center>
    <?php echo $menu->out_title_border()?>    <!-- 初期画面表示 -->
        <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1'>
            <form name='ini_form' action='<?php echo $menu->out_self() ?>' method='post' onSubmit='return ym_chk_tangetu(this)'>
                <tr>    
                    <td colspan='2' align='right' valign='middle' class='pt11' nowrap>
                        対象年月（範囲）を指定して下さい。例：200604 (2006年04月)
                        <input type='text' name='tan_str_ym' size='7' value='<?php echo $request->get('tan_str_ym') ?>' maxlength='6'>
                        ～
                        <input type='text' name='tan_end_ym' size='7' value='<?php echo $request->get('tan_end_ym') ?>' maxlength='6'>
                    </td>
                    <td align='center'>
                        <input class='pt11b' type='submit' name='tangetu' value='自由計算'>
                    </td>
                    <td align='center'>
                        <input type="button" name="print" value="印刷" onclick="framePrint()">
                    </td>
                </tr>
            </form>
            <form name='kessan_form' action='<?php echo $menu->out_self() ?>' method='post' onSubmit='return ym_chk_kessan(this)'>
                <tr>
                    <td align='left' class='pt11' nowrap>
                        対象年月を指定して下さい。
                        <input type='text' name='str_ym' size='7' value='<?php echo $request->get('str_ym') ?>' readonly class='readonly'>
                        ～
                        <input type='text' name='end_ym' id='end_ym' size='7' value='<?php echo $request->get('end_ym') ?>' maxlength='6' onkeyup='start_ym()'>
                    </td>
                    <td align='center'>
                        <input class='pt11b' type='submit' name='kessan' value='決算処理'>
                    </td>
                    <?php
                    if ($request->get('tangetu') != '') {    // 単月の場合は常に照会
                    ?>    
                        <td align='center' class='pt11bb' nowrap>
                        照会
                        </td>
                    <?php
                    } else if ($request->get('kessan') != '') {    // 決算で賃率が登録されている場合は照会
                        if($request->get('input') != '') {
                            $rate_register = "照会";
                            $request->add('rate_register', $rate_register);
                        }
                        if ($request->get('rate_register') == "照会") {
                        ?>    
                                <td align='center' class='pt11bb' nowrap>
                                照会
                                </td>
                                <td align='center' class='pt11bb' nowrap>
                                    <input class='pt11b' type='submit' name='delete' value='確定解除'>
                                </td>
                            <?php
                        } else if ($request->get('rate_register') == "登録") {    // 決算で賃率が登録されていない場合は登録確認画面
                        ?>
                                <td align='center' class='pt11br' nowrap>
                                確認
                                </td>
                                <td align='center' class='pt11bb' nowrap>
                                    <input class='pt11b' type='submit' name='input' value='登録'>
                                    <input type='hidden' name='kessan' value='kessan'>
                                    <input type='hidden' name='c_indirect_cost' value='<?php echo $result->get('c_indirect_cost') ?>'>
                                    <input type='hidden' name='c_suppli_section_cost' value='<?php echo $result->get('c_suppli_section_cost') ?>'>
                                    <input type='hidden' name='l_indirect_cost' value='<?php echo $result->get('l_indirect_cost') ?>'>
                                    <input type='hidden' name='l_suppli_section_cost' value='<?php echo $result->get('l_suppli_section_cost') ?>'>
                                </td>
                        <?php
                        }
                    }
                    ?>
                </tr>
            </form>
        </table>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '照会') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assemblyRate_actAllocate_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='80%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>

