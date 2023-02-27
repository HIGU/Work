<?php 
//////////////////////////////////////////////////////////////////////////////
// ��Ω������Ư���������ƥ�ε�����ž���� �������ե�����                  //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created  ReportEdit.php                                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);

require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<title>������ž����</title>
<?php require_once ('../com/PageHeader.php'); ?>
<LINK rel='stylesheet' href='../com/css.css' type='text/css'>
<SCRIPT language='JavaScript' SRC='../com/popup.js'></SCRIPT>
<SCRIPT language='JavaScript' SRC='<?=SEARCH_JS?>'></SCRIPT>
<Script Language='JavaScript'>
function init() {
<?php if ($Message != '') { ?>
    alert('<?=$Message?>');
<?php } ?>
}
function inMaterialChange() {
    if (isNaN(document.MainForm.Ng.value)) {
        alert("���ɿ��Ͽ��������Ϥ��Ʋ�������");
        document.MainForm.Ng.focus();
        document.MainForm.Ng.select();
        return false;
    }
    if (isNaN(document.MainForm.Plan.value)) {
        alert("�ʼ���Ͽ��������Ϥ��Ʋ�������");
        document.MainForm.Plan.focus();
        document.MainForm.Plan.select();
        return false;
    }
    <?php if ($Report['Type'] != 'C') echo "return true;\n"; ?>
    document.MainForm.Injection.value = Number(document.MainForm.Today.value) + Number(document.MainForm.Ng.value) + Number(document.MainForm.Plan.value);
    return true;
}
function doEntry() {
        document.MainForm.ProcCode.value = 'EDIT';
        document.MainForm.ErrorCheckLevel.value = '1';
        document.MainForm.LogNum.value = '0';
        document.MainForm.submit();
}
function doRetry() {
        document.MainForm.ProcCode.value = 'EDIT';
        document.MainForm.ErrorCheckLevel.value = '0';
        document.MainForm.LogNum.value = '0';
        document.MainForm.submit();
}
function doSave() {
    if (!CalOkNum() || !inMaterialChange()) {
        return false;
    }
    if (confirm('��Ͽ���ޤ���������Ǥ�����')) {
        document.MainForm.ErrorCheckLevel.value = '2';
        document.MainForm.ProcCode.value = 'WRITE';
        document.MainForm.submit();
    }
}
function doDelete() {
    if (confirm('������ޤ���������Ǥ�����')) {
        document.MainForm.ProcCode.value = 'DELETE';
        document.MainForm.submit();
    }
}
<?php if (@$_REQUEST['RetUrl'] != '') { ?>
function doBack() {
    location.href = '<?=@$_REQUEST['RetUrl']?>';
}
<?php } ?>
function CalOkNum() {
    var Yesterday = document.MainForm.Yesterday.value;
    if (isNaN(Yesterday)) {
        alert("���������߷׿��Ͽ��������Ϥ��Ʋ�������");
        document.MainForm.Yesterday.focus();
        document.MainForm.Yesterday.select();
        return false;
    }
    var Today     = document.MainForm.Today.value;
    if (isNaN(Today)) {
        alert("�������ʿ��Ͽ��������Ϥ��Ʋ�������");
        document.MainForm.Today.focus();
        document.MainForm.Today.select();
        return false;
    }
    var sum       = 0;
    
    if (!isNaN(Yesterday) && !isNaN(Today)) {
        sum = Number(Yesterday) + Number(Today);
    }
    
    document.MainForm.GOKEI.value = sum;
    var PlanNum     = document.MainForm.PlanNum.value;
    if (sum > PlanNum) {
        alert("���������߷׿����ؼ�����Ķ���Ƥ��ޤ�");
        document.MainForm.Today.focus();
        document.MainForm.Today.select();
        return false;
    }
    
    return true;
}
function DelLog(line) {
    
    document.MainForm.elements["MacState[]"][line].selectedIndex = 0;
    document.MainForm.elements["FromDate[]"][line].selectedIndex = 0;
    document.MainForm.elements["FromHH[]"][line].value = "";
    document.MainForm.elements["FromMM[]"][line].value = "";
    document.MainForm.elements["ToDate[]"][line].selectedIndex = 0;
    document.MainForm.elements["ToHH[]"][line].value = "";
    document.MainForm.elements["ToMM[]"][line].value = "";
    document.MainForm.elements["CutTime[]"][line].value = "";
}
</Script>
</head>
<body onLoad='init()'>
<form name='MainForm' action='ReportEntry.php' method='post'>
<input type='hidden' name='RetUrl' value='<?=@$_REQUEST['RetUrl']?>'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='EDIT_MODE' value='<?=@$_REQUEST['EDIT_MODE']?>'>
<input type='hidden' name='SummaryType' value='1'>
<input type='hidden' name='LogNum' value='<?=$LogNum?>'>
<input type='hidden' name='ErrorCheckLevel' value=''>
<input type='hidden' name='Type' value='<?php echo $Report['Type'] ?>'>
    <!-- <Div class='TITLE'>������ž����</Div> -->
    <center>
        <!-- �쥤�����ȥơ��֥� -->
        <table class='LAYOUT'>
            <tr class='LAYOUT'>
                <td class='LAYOUT'>
<?php if (@$Report['ENTRY_LEVEL'] == 1) { ?>
                    <!-- �إå����� �������ϥ⡼��-->
                    <table border='1'>
                        <tr>
                            <td CLASS='HED' style='width:80;'>
                                ��ž��
                            </td>
                            <td align='center' style='width:150;'>
                                <input type='text' name='WorkYear'  size='4' maxlength='4' value='<?=outHtml(@$Report['WorkYear'])?>' class='NUM'>/<input type='text' name='WorkMonth' size='2' maxlength='2' value='<?=outHtml(@$Report['WorkMonth'])?>'class='NUM'>/<input type='text' name='WorkDay'   size='2' maxlength='2' value='<?=outHtml(@$Report['WorkDay'])?>'  class='NUM'>
                            </td>
                            <td class='HED' style='width:80;'>
                                ����No.
                            </td>
                            <td align='center' style='width:100;'>
                                <input type='text' size='5' maxlength='5' name='MacNo' value='<?=outHtml(@$Report['MacNo'])?>' class='NUM'>
                            </td>
                            <td class='HED' style='width:80;'>
                                ����̾
                            </td>
                            <td align='center' style='width:150;'>
                                <?=outHtml(@$Report['MacName'])?>
                            </td>
                            <td class='HED' style='width:80;'>
                                �ײ�No.
                            </td>
                            <td align='center' style='width:100;'>
                                <input type='text' name='PlanNo' size='6' maxlength='6' value='<?=outHtml(@$Report['PlanNo'])?>' class='NUM'>
                            </td>
                        </tr>
                        <tr>
                            <td class='HED' style='width:80;'>
                                ����No.
                            </td>
                            <td align='center'>
                                <?=outHtml(@$Report['ItemCode'])?>
                            </td>
                            <td class='HED' style='width:80;'>
                                ����̾
                            </td>
                            <td align='center' style='width:100;'>
                                <?=outHtml(@$Report['ItemName'],12)?>
                            </td>
                            <td class='HED' style='width:80;'>
                                ���ʺ��
                            </td>
                            <td align='center' style='width:150;'>
                                <?=outHtml(@$Report['Mzist'],20)?>
                            </td>
                            <td class='HED' colspan='2' style='width:180;'>
                                <!-- LAYOUT AREA -->
                            </td>
                        </tr>
                        <tr>
                            <td class='HED' style='width:80;'>
                                ����No.
                            </td>
                            <td align='center' style='width:150;'>
                                <input type='text' name='KouteiNo' size='2' maxlength='2' value='<?=outHtml(@$Report['KouteiNo'])?>' class='NUM'>
                            </td>
                            <td class='HED' style='width:80;'>
                                ����̾
                            </td>
                            <td align='center' style='width:100;'>
                                <?=outHtml($Report['KouteiName'])?>
                            </td>
                            <td class='HED' style='width:80;'>
                                Ǽ��
                            </td>
                            <td align='center' style='width:150;'>
                                <?=@$Report['DeliveryYYYY']?>/<?=@$Report['DeliveryMM']?>/<?=@$Report['DeliveryDD']?>
                            </td>
                            <td class='HED' style='width:80;'>
                                �ؼ�����
                            </td>
                            <td align='center' style='width:80;'>
                                <input type='text' name='PlanNum' size='6' maxlength='6' value='<?=outHtml(@$Report['PlanNum'])?>' class='READONLY' onChange='CalOkNum()' style='text-align: right;' readonly>
                            </td>
                            
                        </tr>
                    </table>
                    <br>
                    <center><input type='button' value='��' style='width:80px;' onClick='doEntry()'></center>
<?php } else { ?>                    
                    <!-- �إå����� �������ϥ⡼��-->
                    <table border='1' style='width:830;'>
                        <tr>
                            <td CLASS='HED' style='width:90;'>
                                ��ž��
                            </td>
                            <td align='center' style='width:90;'>
                                <?=outHtml(@$Report['WorkYear'])?>/<?=outHtml(@$Report['WorkMonth'])?>/<?=outHtml(@$Report['WorkDay'])?>
                                <input type='hidden' name='WorkYear'  value='<?=outHtml(@$Report['WorkYear'])?>'>
                                <input type='hidden' name='WorkMonth' value='<?=outHtml(@$Report['WorkMonth'])?>'>
                                <input type='hidden' name='WorkDay'   value='<?=outHtml(@$Report['WorkDay'])?>'>
                            </td>
                            <td class='HED' style='width:90;'>
                                ����No.
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml(@$Report['MacNo'])?>
                                <input type='hidden' name='MacNo' value='<?=outHtml(@$Report['MacNo'])?>'>
                            </td>
                            <td class='HED' style='width:90;'>
                                ����̾
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml(@$Report['MacName'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                �ؼ�No.
                            </td>
                            <td align='center' style='width:80;'>
                                <?=outHtml(@$Report['PlanNo'])?>
                                <input type='hidden' name='PlanNo' value='<?=outHtml(@$Report['PlanNo'])?>'>
                            </td>
                        </tr>
                        <tr>
                            <td class='HED' style='width:90;'>
                                ����No.
                            </td>
                            <td align='center' style='width:90;'>
                                <?=outHtml(@$Report['ItemCode'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                ����̾
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml(@$Report['ItemName'],10)?>
                            </td>
                            <td class='HED' style='width:90;'>
                                ���ʺ��
                            </td>
                            <td align='center' style='width:150;'>
                                <?=outHtml(@$Report['Mzist'],20)?>
                            </td>
                            <td class='HED' colspan='2' style='width:170;'>
                                <!-- LAYOUT AREA -->
                            </td>
                        </tr>
                        <tr>
                            <td class='HED' style='width:90;'>
                                ����No.
                            </td>
                            <td align='center' style='width:90;'>
                                <?=outHtml(@$Report['KouteiNo'])?>
                                <input type='hidden' name='KouteiNo' value='<?=outHtml(@$Report['KouteiNo'])?>'>
                            </td>
                            <td class='HED' style='width:90;'>
                                ����̾
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml(@$Report['KouteiName'])?>
                            </td>
                            <td class='HED'style='width:90;'>
                                Ǽ��
                            </td>
                            <td align='center'  style='width:130;'>
                                <?=@$Report['DeliveryYYYY']?>/<?=@$Report['DeliveryMM']?>/<?=@$Report['DeliveryDD']?>
                            </td>
                            <td class='HED' style='width:90;'>
                                �ؼ�����
                            </td>
                            <td align='center' style='width:80;'>
                                <input type='text' name='PlanNum' size='6' maxlength='6' value='<?=outHtml(@$Report['PlanNum'])?>' class='READONLY' onChange='CalOkNum()' style='text-align: right;' readonly>
                            </td>
                            
                        </tr>
                    </table>
                    <!-- ������ �쥤�����ȥơ��֥� -->
                    <table class='LAYOUT'>
                        <tr class='LAYOUT'>
                            <td class='LAYOUT' valign='top'>
                                <table border='1' style='width:390;'>
                                    <tr>
                                        <td class='HED' style='width:130;'>
                                            ���������߷׿�
                                        </td>
                                        <td class='HED' style='width:130;'>
                                            �������ʿ�
                                        </td>
                                        <td class='HED' style='width:130;'>
                                            ���������߷׿�
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align='center'>
                                            <input type='text' name='Yesterday' size='6' maxlength='6' value='<?=outHtml(@$Report['Yesterday'])?>' class='READONLY' onChange='CalOkNum()' style='text-align: right;' readonly>
                                        </td>
                                        <td align='center'>
                                            <input type='text' name='Today' size='6' maxlength='6' value='<?=outHtml(@$Report['Today'])?>' class='NUM' onChange='CalOkNum(); inMaterialChange();'>
                                        </td>
                                        <td align='right'>
                                            <input type='text' name='GOKEI' size='6' maxlength='6' value='<?=outHtml(@$Report['Yesterday']+@$Report['Today'])?>' class='READONLY' style='text-align: right;' readonly>
                                        </td>
                                    </tr>
                                </table>
                                <br>
                                <table border='1'>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ���ɿ�
                                        </td>
                                        <td class='NUM' style='width:100'>
                                            <input type='text' name='Ng' size='6' maxlength='6' value='<?=outHtml(@$Report['Ng'])?>' class='NUM' onChange='inMaterialChange()'>
                                        </td>
                                        <td class='HED' style='width:100'>
                                            �ʼ��
                                        </td>
                                        <td class='NUM' style='width:150'>
                                            <input type='text' name='Plan' size='6' maxlength='6' value='<?=outHtml(@$Report['Plan'])?>' class='NUM' onChange='inMaterialChange()'>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ��λ��ʬ
                                        </td>
                                        <td class='NUM' style='width:100'>
                                            <select name='EndFlg'>
                                                <?php if (@$Report['EndFlg'] != 'E') { ?><option value='' selected></option><option value='E'>E (��λ)</option>
                                                <?php } else  { ?><option value=''></option><option value='E' selected>E (��λ)</option> <?php } ?>
                                            </select>
                                        </td>
                                        <td class='HED' style='width:100'>
                                            ���ɶ�ʬ
                                        </td>
                                        <td class='NUM' style='width:150'>
                                            <select name='NgKbn'>
                                            <?=NgKbnSelectOptions(@$Report['NgKbn'])?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ���祳����
                                        </td>
                                        <td class='NUM' style='width:100'>
                                            <?=outHtml(@$Report['Stop'])?>
                                        </td>
                                        <td class='HED' style='width:100'>
                                            �ξ���
                                        </td>
                                        <td class='NUM' style='width:150'>
                                            <?=outHtml(@$Report['Failure'])?>
                                        </td>
                                    </tr>
                                </table>
                                <br>
                                <?php if (@$_REQUEST['SummaryType'] == 2) { ?><font color='#ff0000'><b>�� ���ץ⡼�� ��</b></font><?php } ?>
                                <table border='1'>
                                    <tr>
                                        <td class='HED' nowrap>
                                        </td>
                                        <td class='HED' nowrap>
                                            ��ȶ�ʬ
                                        </td>
                                        <td class='HED' nowrap>
                                            ��Ȼ���
                                        </td>
                                        <td class='HED' nowrap>
                                            ���åȻ���
                                        </td>
                                        <td class='HED' nowrap>
                                            ��Ȼ���(ʬ)
                                        </td>
                                    </tr>
                                <?php for($i=0;$i<$LogNum;$i++) { ?>
                                    <tr>
                                        <td align='center' nowrap>
                                            <input type='button' value='��' onClick='DelLog(<?=$i?>)'>
                                        </td>
                                        <td align='center' nowrap>
                                            <select name='MacState[]'>
                                            <?=MachineStateSelectOptions($CsvFlg,@$Report['MacState'][$i])?>
                                            </select>
                                        </td>
                                        <td align='center' nowrap>
                                            <?=LogSelectDate(@$Report['WorkDate'],'FromDate',@$Report['FromDate'][$i])?>
                                            <input type='text' name='FromHH[]' size='2' maxlength='2' value='<?=outHtml(@$Report['FromHH'][$i])?>' class='NUM'>:<input type='text' name='FromMM[]' size='2' maxlength='2' value='<?=outHtml(@$Report['FromMM'][$i])?>' class='NUM'>
                                            ��
                                            <?=LogSelectDate(@$Report['WorkDate'],'ToDate',@$Report['ToDate'][$i])?>
                                            <input type='text' name='ToHH[]' size='2' maxlength='2' value='<?=outHtml(@$Report['ToHH'][$i])?>' class='NUM'>:<input type='text' name='ToMM[]' size='2' maxlength='2' value='<?=outHtml(@$Report['ToMM'][$i])?>' class='NUM'>
                                        </td>
                                        <td  align='center' nowrap>
                                            <input type='text' name='CutTime[]' size='6' maxlength='6' value='<?=outHtml(@$Report['CutTime'][$i])?>' class='NUM'>
                                        </td>
                                        <td align='center'  nowrap>
                                            <?= outHtml(CalWorkTime(@$Report['FromDate'][$i],@$Report['FromTime'][$i],@$Report['ToDate'][$i],@$Report['ToTime'][$i]) - @$Report['CutTime'][$i])?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </table>
                            </td>
                            <td class='LAYOUT' valign='top'>
                                <br>
                                <table border='1'>
                                    <tr>
                                        <td class='HED' style='width:70'>
                                            ����
                                        </td>
                                    </tr>
                                </table>
                                <table border='0' class='LAYOUT'>
                                    <tr class='LAYOUT'>
                                        <td class='LAYOUT'>
                                        <textarea name='Memo' cols='40' rows='7'><?=outHtml(@$Report['Memo'])?></textarea>
                                        </td>
                                    </tr>
                                </table>
                                <br>
                                <!--
                                <table border='1'>
                                    <tr>
                                        <td class='HED' colspan='2' style='width:200'>
                                            ��������
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ����������
                                        </td>
                                        <td style='width:150' align='center'>
                                            <input type='button' value='����' onClick='SearchMaterials(InjectionItem)'>
                                            <input type='text' name='InjectionItem' size='9' maxlength='9' value='<?=outHtml(@$Report['InjectionItem'])?>' class='CODE'>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ������
                                        </td>
                                        <td style='width:150' align='center'>
                                            <input type='text' name='Injection' size='8' maxlength='6' value='<?=outHtml(@$Report['Injection'])?>' class='NUM'>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ����ü��Ĺ��
                                        </td>
                                        <td style='width:150' align='center'>
                                            <input type='text' name='Abandonment' size='8' maxlength='6' value='<?=outHtml(sprintf ('%.04f',@$Report['Abandonment']))?>' class='NUM'>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ����ü�����
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml(sprintf ('%.04f',$Report['AbandonmentWeight']))?> kg
                                        </td>
                                    </tr>
                                </table>
                                -->
                                <br>
                                <br>
                                <br>
                                <br>
                                <table class='LAYOUT'>
                                    <tr class='LAYOUT'>
                                        <td class='LAYOUT' align='center' style='width:400;'>
                                        <?php if ($AdminUser) { ?>
                                            <input type='button' value='�С�Ͽ' style='width:80px' onClick='doSave()'>
                                            <?php if (@$_REQUEST['EDIT_MODE'] == 'INSERT') { ?>
                                            <input type='button' value='�� ���' style='width:80px;' onClick='doRetry()'>
                                            <?php } ?>
                                            <?php if (@$_REQUEST['EDIT_MODE'] == 'UPDATE') { ?>
                                            <input type='button' value='���' style='width:80px' onClick='doDelete()'>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if (@$_REQUEST['RetUrl'] != '') { ?>
                                            <input type='button' value='�ᡡ��' style='width:80;' onClick='doBack()'>
                                        <?php } ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
<?php } ?>
                        
                    <!-- �� LAYOUT TABLE �� -->
                </td>
            </tr>
        </table>
    </center>
</form>
</body>
</html>
