<?php
////////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���ʿ����˼��ä�����                                       //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_ViewCancel.php                    //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
$menu->out_html_header();
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
<?php echo $menu->out_jsBaseClass() ?>

<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='over_time_work_report.js'></script>

</head>

<body onLoad='InitCancel()'>

<center>
<?= $menu->out_title_border() ?>
    <BR>
    <!-- ��ä��ơ��֥� -->
<form name='form_cancel' method='post' action='<?php echo $menu->out_self() . "?showMenu=Appli" ?>' onSubmit='return true;'>
    <input type='hidden' name='list_view'    id='id_list_view'  value='<?php echo $list_view; ?>'>
    <input type='hidden' name='w_date'       id='id_w_date'     value='<?php echo $date; ?>'>
    <input type='hidden' name='cancel_uid'   id='id_cancel_uid' value='<?php echo $cancel_uid; ?>'>
    <input type='hidden' name='cancel_uno'   id='id_cancel_uno' value='<?php echo $cancel_uno; ?>'>
    <input type='hidden' name='type'         value='<?php echo $type; ?>'>
    <input type='hidden' name='ddlist_year'  value='<?php echo $def_y; ?>'>
    <input type='hidden' name='ddlist_month' value='<?php echo $def_m; ?>'>
    <input type='hidden' name='ddlist_day'   value='<?php echo $def_d; ?>'>
    
    <table class='pt10' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- ����ץ���� -->
            <tr>
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='4' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>

            <!-- ��ҥ��������ε��������javascript���ѿ��إ��åȤ��Ƥ�����-->
            <script> var holiday = '<?php echo $holiday; ?>';  SetHoliday(holiday);</script>

            <tr align='center'>
                <td>
                    �������<?php echo "{$def_y}ǯ {$def_m}�� {$def_d}��"; ?>
                    <font id='id_w_youbi'>
                        <script>Youbi(document.form_cancel.w_date, 'id_w_youbi');</script>
                    </font>
                </td>
                <td>
                    ����̾��<?php echo $bumon; ?>
                    <input type='hidden' name='ddlist_bumon' value='<?php echo $bumon; ?>'>
                </td>
                <td>
                    ��ȼԡ�<?php echo $cancel_name; ?>
                </td>
            </tr>

            <tr align='center' valign='middle'>
                <td colspan='2'>
                    ��ͳ��
                    <textarea name='reason' id='id_reason' rows='4' cols='30' value=''></textarea>
                </td>
                <td>
                    <input type='submit' name='execut' id='id_submit' value='��� �¹�'  onClick='return CancelExec();'><BR><BR>
                    <input type='submit' name='cancel' id='id_cancel' value='����󥻥�' onClick='return CancelCancel();'>
                </td>
            </tr>
        </table>
    </td></tr> <!----------------- ���ߡ�End --------------------->
    </table>
</form>


</center>
</body>
<?php echo $menu->out_alert_java(); ?>
</html>
