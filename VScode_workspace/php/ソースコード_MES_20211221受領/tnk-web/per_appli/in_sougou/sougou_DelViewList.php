<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʿ�����                                                             //
//                                                    MVC View �� �ꥹ��ɽ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_ViewList.php                                     //
//            ��ǧ���Խ����̡�sougou_admit_EditView.php�ˤ�ɬ�פ˱���Ʊ������ //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
$menu->out_html_header();

$menu->set_caption('�������μ����ͳ�����Ϥ��Ʋ�������');

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
<script type='text/javascript' language='JavaScript' src='sougou.js'></script>

</head>

<body onLoad='document.del.del_reason.focus()'>

<center>
    <?php
    if( $request->get('del_reason_mail') == 'on' ) {
        $model->DelReasonMail($request);
        ?>
        <script>window.open("about:blank","_self").close()</script>
        <?php         
    }
    if( !$model->GetReViewData($request) ) {        // �������������
        ?>
        <script>alert("��ǧ���줿��������μ����˼��Ԥ��ޤ�����"); window.open("about:blank","_self").close();</script>
        <?php
    }
    if( !$model->IsReApplPossible($request) ) {
        ?>
        <script>alert("���ˡ��ƿ����ѤߤǤ���"); window.open("about:blank","_self").close();</script>
        <?php
    }
    if( ! $model->IsDelPossible($request) ) {
        ?>
        <script>alert("���ˡ���úѤߤǤ���");window.open("about:blank","_self").close()</script>
        <?php         
    }
    ?>
    <BR>
    <form name="del" method="post" action='<?php echo $menu->out_self(), "?del_reason_mail=on&showMenu=Del" ?>'>
        <input type='hidden' name='date' value='<?php echo $request->get('date'); ?>'>
        <input type='hidden' name='syainbangou' value='<?php echo $request->get('syainbangou'); ?>'>
        <input type='hidden' name='deny_uid' value='<?php echo $request->get('deny_uid'); ?>'>

        <table class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>

            <tr>
                <td nowrap align='center'>�� ǧ ��</td>
                <td>
                    <?php echo $model->getSyainName($request->get('deny_uid')); ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='center'>��ǧ��ͳ</td>
                <td>
                    <?php echo $model->GetDelViewData($request); ?>
                </td>
            </tr>

            <tr>
                <td nowrap align='center'>�����ͳ</td>
                <td align='center'>
                    <textarea cols="52" rows="6" name="del_reason"></textarea>
                </td>
            </tr>
        </table>
        </td></tr> <!----------- ���ߡ�(�ǥ�������) ------------>
        </table>

        <p align='center'>
            <input type="submit" value=" < ���� > " name="submit" onClick='return MailSend()'>��
            <input type="button" value="[��]�Ĥ���" name="close" onClick='window.open("about:blank","_self").close()'>
            <BR>
            <BR>
            ��[<����>]�ܥ���򥯥�å�����ȡ������ͳ��<BR>���ܤ��줿�᡼�����ǧ�Ԥ�����ޤ���
        </p>
    </form>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
