<?php 
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ�ε�����ž���� ����Ȳ�ե�����                      //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2004/07/15 Created  ReportView.php                                       //
// 2004/08/26 ��ˡ�����å�����ɲ�                                          //
// 2006/04/17 ��ˡ�����å���� nowrap height:40; �ɲ�                       //
// 2006/04/20 �С���ξ����������̤򾮿��������б��ڤ�ü��λ����ѹ�      //
// 2006/04/21 �߷����������߷��������̤��ɲ�                                //
// 2006/04/26 access_log()���ɲä����ͤ�<pre�򥳥��Ȳ�(����ǲ��Ԥ������)//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);

require_once ('../../../function.php');     // TNK ������ function
require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');
access_log();                               // Script Name �ϼ�ư����

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<title>������ž����</title>
<?php require_once ('../com/PageHeader.php'); ?>
<LINK rel='stylesheet' href='../com/css.css' type='text/css'>
<SCRIPT language='JavaScript' SRC='../com/popup.js'></SCRIPT>
<Script Language='JavaScript'>
function doDecision() {
    if (confirm('�����Ԥ��Ȥ��ε�����ž����Ͻ����Ǥ��ʤ��ʤ�ޤ���\n���ꤷ�ޤ�����')) {
        document.MainForm.ProcCode.value = 'DECISION';
        document.MainForm.submit();
    }
}
<?php if (@$_REQUEST['RetUrl'] != '') { ?>
function doBack() {
    location.href = '<?=@$_REQUEST['RetUrl']?>';
}
<?php } ?>
</Script>
</head>
<body>
<form name='MainForm' method='post'>
<input type='hidden' name='RetUrl' value='<?=@$_REQUEST['RetUrl']?>'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='WorkDate' value='<?=outHtml(mu_Date::toString($Report['WorkDate'],'Ymd'))?>'>
<input type='hidden' name='MacNo' value='<?=outHtml($Report['MacNo'])?>'>
<input type='hidden' name='SijiNo' value='<?=outHtml($Report['SijiNo'])?>'>
<input type='hidden' name='KouteiNo' value='<?=outHtml($Report['KouteiNo'])?>'>
<input type='hidden' name='SummaryType' value='1'>
<input type='hidden' name='LogNum' value='<?=$LogNum?>'>
    <!-- <Div class='TITLE'>������ž����</Div> -->
    <center>
        <!-- �쥤�����ȥơ��֥� -->
        <table class='LAYOUT'>
            <tr class='LAYOUT'>
                <td class='LAYOUT'>
                    
                    <!-- �إå����� -->
                    <table border='1' style='width:830;'>
                        <tr>
                            <td CLASS='HED' style='width:90;'>
                                ��ž��
                            </td>
                            <td align='center' style='width:90;'>
                                <?=outHtml(mu_Date::toString($Report['WorkDate'],'Y/m/d'))?>
                            </td>
                            <td class='HED' style='width:90;'>
                                ����No.
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml($Report['MacNo'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                ����̾
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml($Report['MacName'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                �ؼ�No.
                            </td>
                            <td align='center' style='width:80;'>
                                <?=outHtml($Report['SijiNo'])?>
                            </td>
                        </tr>
                        <tr>
                            <td class='HED' style='width:90;'>
                                ����No.
                            </td>
                            <td align='center' style='width:90;'>
                                <?=outHtml($Report['ItemCode'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                ����̾
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml($Report['ItemName'],16)?>
                            </td>
                            <td class='HED' style='width:80;'>
                                ���ʺ��
                            </td>
                            <td align='center'>
                                <?=outHtml($Report['Mzist'],20)?>
                            </td>
                            <td class='HED' colspan='2' style='width:170;'>
                                <!-- LAYOUT AREA -->
                            </td>
                        </tr>
                        <tr>
                            <td class='HED' style='width:90'>
                                ����No.
                            </td>
                            <td align='center' style='width:90;'>
                                <?=outHtml($Report['KouteiNo'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                ����̾
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml($Report['KouteiName'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                Ǽ��
                            </td>
                            <td align='center' style='width:130;'>
                                <?=$Report['DeliveryYYYY']?>/<?=$Report['DeliveryMM']?>/<?=$Report['DeliveryDD']?>
                            </td>
                            <td class='HED' style='width:90;'>
                                �ؼ�����
                            </td>
                            <td class='NUM' style='width:80;'>
                                <?=outHtml($Report['SijiNum'])?>
                            </td>
                            
                        </tr>
                    </table>
                    
                    <!-- ������ �쥤�����ȥơ��֥� -->
                    <table class='LAYOUT'>
                        <tr class='LAYOUT'>
                            <td class='LAYOUT'>
                                <table border='1'>
                                    <tr>
                                        <td class='HED' style='width:100;'>
                                            ���������߷׿�
                                        </td>
                                        <td class='HED' style='width:100;'>
                                            �������ʿ�
                                        </td>
                                        <td class='HED' style='width:100;'>
                                            ���������߷׿�
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align='center'>
                                            <?=outHtml($Report['Yesterday'])?>
                                        </td>
                                        <td align='center'>
                                            <?=outHtml($Report['Today'])?>
                                        </td>
                                        <td align='center'>
                                            <?=outHtml($Report['Yesterday']+$Report['Today'])?>
                                        </td>
                                    </tr>
                                </table>
                                <br>
                                <table border='1'>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ���ɿ�
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml($Report['Ng'])?>
                                        </td>
                                        <td class='HED' style='width:100'>
                                            �ʼ��
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml($Report['Plan'])?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ��λ��ʬ
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml($Report['EndFlg'])?>
                                        </td>
                                        <td class='HED' style='width:100'>
                                            ���ɶ�ʬ
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=getNgKbnName($Report['NgKbn'])?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ���祳����
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml($Report['Stop'])?>
                                        </td>
                                        <td class='HED' style='width:100'>
                                            �ξ���
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml($Report['Failure'])?>
                                        </td>
                                    </tr>
                                </table>
                                <br>
                                    <?php if (@$_REQUEST['SummaryType'] == 2) { ?><font color='#ff0000'><b>�� ���ץ⡼�� ��</b></font><?php } ?>
                                <table border='1'>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            ��ȶ�ʬ
                                        </td>
                                        <td class='HED' style='width:150'>
                                            ��Ȼ���
                                        </td>
                                        <td class='HED' style='width:100'>
                                            ���åȻ���
                                        </td>
                                        <td class='HED' style='width:100'>
                                            ��Ȼ���(ʬ)
                                        </td>
                                    </tr>
                                <?php for($i=0;$i<$LogNum;$i++) { ?>
                                    <tr>
                                        <td align='center' style='width:100;<?=MachineStateStyle($CsvFlg,$Report['MacState'][$i] )?>'>
                                            <?=outHtml($Report['MacStateName'][$i])?>
                                        </td>
                                        <td align='center' style='width:150'>
                                            <?=outHtml($Report['FromHH'][$i])?>:<?=outHtml($Report['FromMM'][$i])?>��<?=outHtml($Report['ToHH'][$i])?>:<?=outHtml($Report['ToMM'][$i])?>
                                        </td>
                                        <td align='center' style='width:100'>
                                            <?=outHtml($Report['CutTime'][$i])?>
                                        </td>
                                        <td align='center' style='width:100'>
                                            <?=CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i])-$Report['CutTime'][$i]?>
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
                                <table border='1'>
                                    <tr>
                                        <td style='width:400;height:100' valign='top'>
                                            <!-- ���ꥸ�ʥ��<pre></pre> -->
                                            <?=outHtml($Report['Memo'])?>
                                        </td>
                                    </tr>
                                </table>
                                <br>
                                <table class='LAYOUT'>
                                    <tr>
                                    <td class='LAYOUT'>
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
                                                <td style='width:100' align='center'>
                                                    <?=outHtml($Report['InjectionItem'])?>
                                                </td>
                                            </tr>
                                            <?php if ($Report['Type'] == 'B') { ?>
                                            <tr>
                                                <td class='HED' nowrap>
                                                    ���ܤ������Ĺ��
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?=outHtml($Report['Length'])?> m&nbsp;
                                                </td>
                                            </tr>
                                            <?php } ?>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    ������
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?=outHtml($Report['Injection'])?>
                                                    <?php if ($Report['Type'] == 'B') { ?>
                                                    ��&nbsp;&nbsp;
                                                    <?php } else { ?>
                                                    ��&nbsp;&nbsp;
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    ��������
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?php if ($Report['Type'] == 'B') { ?>
                                                        <?=outHtml($Report['inWeight'])?>
                                                        Kg&nbsp;&nbsp;
                                                    <?php } else { ?>
                                                        ���Ǻ�
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    �߷�������
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?=outHtml($Report['SUMinjection'])?>
                                                    <?php if ($Report['Type'] == 'B') { ?>
                                                    ��&nbsp;&nbsp;
                                                    <?php } else { ?>
                                                    ��&nbsp;&nbsp;
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    �߷���������
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?php if ($Report['Type'] == 'B') { ?>
                                                        <?=outHtml($Report['SUMinWeight'])?>
                                                        Kg&nbsp;&nbsp;
                                                    <?php } else { ?>
                                                        ���Ǻ�
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan='2' nowrap align='center'>
                                                    �ʲ��ϥС���ξ��λ���
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    ����ü��Ĺ��
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?=outHtml(sprintf ('%.04f',$Report['Abandonment']))?> m&nbsp;
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
                                    </td>
                                    <td class='LAYOUT' valign='top'>
                                        <table border='1'>
                                            <tr>
                                                <td class='HED' nowrap style='font-size:8pt;'>
                                                    ��ˡ�����å�
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style='width:70; height:40;' align='center'>
                                                    <input type='checkbox' name='dimension_check' checked>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    </tr>
                                </table>
                                <br>
                                <table class='LAYOUT'>
                                    <tr class='LAYOUT'>
                                        <td class='LAYOUT' align='center' style='width:400;'>
                                            <?php if ($AcceptUser && $Report['DecisionFlg'] == 0) { ?>
                                            <input type='button' value='����' style='width:80px' onClick='doDecision()'>
                                            <?php } ?>
                                            <?php if (@$_REQUEST['RetUrl'] != '') { ?>
                                            <input type='button' value='�ᡡ��' style='width:80;' onClick='doBack()'>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr class='LAYOUT'>
                                        <td class='LAYOUT' align='center'>
                                            <br>
                                            <font color='#ff0000'><b><?=$Message?></b></font>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                        
                    <!-- �� LAYOUT TABLE �� -->
                </td>
            </tr>
        </table>
    </center>
</form>
</body>
</html>
