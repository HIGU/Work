<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（申請）                                                             //
//                                                    MVC View 部 リスト表示  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_ViewList.php                                     //
//            承認の編集画面（sougou_admit_EditView.php）も必要に応じ同時修正 //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
$menu->out_html_header();

$menu->set_caption('※申請の取消理由を入力して下さい。');

?>
<!DOCTYPE html>
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
    if( !$model->GetReViewData($request) ) {        // 前回申請情報取得
        ?>
        <script>alert("否認された申請情報の取得に失敗しました。"); window.open("about:blank","_self").close();</script>
        <?php
    }
    if( !$model->IsReApplPossible($request) ) {
        ?>
        <script>alert("既に、再申請済みです。"); window.open("about:blank","_self").close();</script>
        <?php
    }
    if( ! $model->IsDelPossible($request) ) {
        ?>
        <script>alert("既に、取消済みです。");window.open("about:blank","_self").close()</script>
        <?php         
    }
    ?>
    <BR>
    <form name="del" method="post" action='<?php echo $menu->out_self(), "?del_reason_mail=on&showMenu=Del" ?>'>
        <input type='hidden' name='date' value='<?php echo $request->get('date'); ?>'>
        <input type='hidden' name='syainbangou' value='<?php echo $request->get('syainbangou'); ?>'>
        <input type='hidden' name='deny_uid' value='<?php echo $request->get('deny_uid'); ?>'>

        <table class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>

            <tr>
                <td nowrap align='center'>否 認 者</td>
                <td>
                    <?php echo $model->getSyainName($request->get('deny_uid')); ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='center'>否認理由</td>
                <td>
                    <?php echo $model->GetDelViewData($request); ?>
                </td>
            </tr>

            <tr>
                <td nowrap align='center'>取消理由</td>
                <td align='center'>
                    <textarea cols="52" rows="6" name="del_reason"></textarea>
                </td>
            </tr>
        </table>
        </td></tr> <!----------- ダミー(デザイン用) ------------>
        </table>

        <p align='center'>
            <input type="submit" value=" < 送信 > " name="submit" onClick='return MailSend()'>　
            <input type="button" value="[×]閉じる" name="close" onClick='window.open("about:blank","_self").close()'>
            <BR>
            <BR>
            ※[<送信>]ボタンをクリックすると、取消理由が<BR>記載されたメールを否認者へ送ります。
        </p>
    </form>

</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
