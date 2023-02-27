<?php
//////////////////////////////////////////////////////////////////////////////
// ������夲�κ�����(������)�� �Ȳ�   ������� Form  (�١���)  MVC View �� //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/15 Created   parts_material_show_ViewCondForm.php                //
// 2006/02/20 ���ɽ���ΰ�Υ��ꥢ���ܥ�����ɲ�                            //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="15;URL=<?=$this->menu->out_self(), "?showMenu={$this->request->get('showMenu')}"?>"> -->
<title><?= $this->menu->out_title() ?></title>
<?= $this->menu->out_site_java() ?>
<?= $this->menu->out_css() ?>
<link rel='stylesheet' href='parts_material_show.css?id=<?= $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_material_show.js?<?= $uniq ?>'></script>
</head>
<body
    onLoad='
        PartsMaterialShow.set_focus(document.ConditionForm.showDiv, "");
        setInterval("PartsMaterialShow.blink_disp(\"blink_item\")", 500);
        //setInterval("PartsMaterialShow.AjaxLoadTable(\"ListTable\")", 15000);
    '
>
<center>
<?= $this->menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?= $this->menu->out_self() ?>' method='post'
        onSubmit='return PartsMaterialShow.checkANDexecute(this)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td colspan='2' align='center' class='caption_font'>
                    <span id='blink_item'>�Ȳ� ������ꤷ�Ʋ�������</span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right'>
                    ��������򤷤Ʋ�����
                </td>
                <td class='winbox' align='center'>
                    <select name='showDiv' class='pt12b'>
                        <option value=' '<?php if($this->request->get('showDiv')=='')  echo('selected'); ?>>�����롼��</option>
                        <option value='C'<?php if($this->request->get('showDiv')=='C') echo('selected'); ?>>���ץ�����</option>
                        <option value='L'<?php if($this->request->get('showDiv')=='L') echo('selected'); ?>>��˥�����</option>
                        <!------------------------------------
                        <option value='H'<?php if($this->request->get('showDiv')=='H') echo('selected'); ?>>���ץ�ɸ��</option>
                        <option value='S'<?php if($this->request->get('showDiv')=='S') echo('selected'); ?>>���ץ�����</option>
                        <option value='M'<?php if($this->request->get('showDiv')=='M') echo('selected'); ?>>��˥�ɸ��</option>
                        <option value='B'<?php if($this->request->get('showDiv')=='B') echo('selected'); ?>>�Х����</option>
                        <option value='T'<?php if($this->request->get('showDiv')=='T') echo('selected'); ?>>�ġ���</option>
                        ------------------------------------->
                    </select>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right'>
                    ���դ���ꤷ�Ʋ�����(ɬ��)
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetDateStr' class='pt12b' size='8' value='<?php echo $this->request->get('targetDateStr'); ?>' maxlength='8'>
                    ��
                    <input type='text' name='targetDateEnd' class='pt12b' size='8' value='<?php echo $this->request->get('targetDateEnd'); ?>' maxlength='8'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right'>
                    �����ֹ�λ���
                    (���ꤷ�ʤ����϶���)
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetItemNo' size='9' class='pt12b' value='<?php echo $this->request->get('targetItemNo'); ?>' maxlength='9'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='right' width='400'>
                    ����ʬ=
                    ��������(����) ��������(���) ��������(��ư) ��������(ľǼ) ��������(���)
                    ��������(����) ��������(����)
                </td>
                <td class='winbox' align='center'>
                    <select name='targetSalesSegment'>
                        <option value='2'<?php if($this->request->get('targetSalesSegment')=='2') echo('selected'); ?>>2����</option>
                        <option value='5'<?php if($this->request->get('targetSalesSegment')=='5') echo('selected'); ?>>5��ư</option>
                        <option value='6'<?php if($this->request->get('targetSalesSegment')=='6') echo('selected'); ?>>6ľǼ</option>
                        <option value='7'<?php if($this->request->get('targetSalesSegment')=='7') echo('selected'); ?>>7���</option>
                        <option value='8'<?php if($this->request->get('targetSalesSegment')=='8') echo('selected'); ?>>8����</option>
                        <option value='9'<?php if($this->request->get('targetSalesSegment')=='9') echo('selected'); ?>>9����</option>
                        <!-----------------------------
                        <option value='1'<?php if($this->request->get('targetSalesSegment')=='1') echo('selected'); ?>>1����</option>
                        ------------------------------->
                    <select>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='2' align='center'>
                    <input type='submit' name='exec' value='�¹�'>
                    &nbsp;&nbsp;
                    <input type='button' name='clear' value='���ꥢ' onClick='PartsMaterialShow.viewClear();'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </form>
    
    <div id='showAjax'>
    </div>
</center>
</body>
<?=$this->menu->out_alert_java()?>
</html>
