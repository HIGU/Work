<?php
//////////////////////////////////////////////////////////////////////////////
// ����������ʬ���ѥ���պ�����˥塼  ����դ�ɽ��  View��                 //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/10/06 Created   graphCreate_Main.php                                //
// 2007/10/07 ����դ���ɽ������ɽ���ɲá�Y������(����)������(�̡�)���ɲ�   //
// 2007/10/10 if ($session->get_local('g1plot1') != '̤����')   ��          //
//            if ($result->get('g1plot1_rows') > 0) �ؾ��ʬ���ѹ�          //
// 2007/10/13 X����ǯ���prot1��prot2�̡�������Ǥ��륪�ץ������ɲ�       //
// 2007/11/06 »�ץ���պ�����˥塼�������������պ�����˥塼�ز�¤      //
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
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>

        <!----------------- ������ ǯ��λ���ե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td width='10%' nowrap style='text-align:left;' class='pt10b'>
                    <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='backward' value='����'<?php echo $result->get('backward')?>>
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
                    <span style='color:blue;'>�ץ�å�1ǯ��</span><?php echo ymFormCreate($session->get_local('dataxFlg'), $session->get_local('yyyymm1'), 'yyyymm1', "onChange='{$linkAction}document.ym_form.submit()'") ?>
                    <?php if ($session->get_local('dataxFlg') == 'on') echo "<input type='hidden' name='yyyymm2' value='{$session->get_local('yyyymm2')}'>\n";?>
                    <span style='color:red;' >�ץ�å�2ǯ��</span><?php echo ymFormCreate($session->get_local('dataxFlg'), $session->get_local('yyyymm2'), 'yyyymm2', "onChange='document.ym_form.submit()'") ?>
                    </form>
                </td>
                <td width='10%' nowrap style='text-align:right;' class='pt10b'>
                    <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='forward' value='����'<?php echo $result->get('forward') ?>>
                                <input type='hidden' name='yyyymm1' value='<?php echo $result->get('next_yyyymm1') ?>'>
                                <input type='hidden' name='yyyymm2' value='<?php echo $result->get('next_yyyymm2') ?>'>
                            </td>
                        </table>
                    </form>
                </td>
            </tr>
        </table>
        
        <?php if ($result->get('g1plot1_rows') > 0) { ?>
        <!--------------- �������饰��գ� ��ɽ������ -------------------->
        <table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center'>
                    <img src='<?php echo $result->get('graph_name1') . "?" . $uniq ?>' alt='�бĻ��� ����գ�' border='0'>
                </td>
            </tr>
        </table>
        
        <br>
        
        <!--
        <table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
            <form method='post' action='<?php echo $menu->out_RetUrl()?>'>
                <td align='center'><input class='pt12b' type='submit' name='return' value='���'></td>
            </form>
        </table>
        -->
        <?php } ?>
        <?php if ($result->get('g2plot1_rows') > 0) { ?>
        <!--------------- �������饰��գ� ��ɽ������ -------------------->
        <table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center'>
                    <img src='<?php echo $result->get('graph_name2') . "?" . $uniq ?>' alt='�бĻ��� ����գ�' border='0'>
                </td>
            </tr>
        </table>
        
        <br>
        
        <!--
        <table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
            <form method='post' action='<?php echo $menu->out_RetUrl()?>'>
                <td align='center'><input class='pt12b' type='submit' name='return' value='���'></td>
            </form>
        </table>
        -->
        <?php } ?>
        <?php if ($result->get('g3plot1_rows') > 0) { ?>
        <!--------------- �������饰��գ� ��ɽ������ -------------------->
        <table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center'>
                    <img src='<?php echo $result->get('graph_name3') . "?" . $uniq ?>' alt='�бĻ��� ����գ�' border='0'>
                </td>
            </tr>
        </table>
        <?php } ?>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
