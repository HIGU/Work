<?php 
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理システムの機械運転日報 日報照会フォーム                      //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2004/07/15 Created  ReportView.php                                       //
// 2004/08/26 寸法チェック欄を追加                                          //
// 2006/04/17 寸法チェック欄を nowrap height:40; 追加                       //
// 2006/04/20 バー材の場合の投入重量を小数点２桁対応及び端材の算出変更      //
// 2006/04/21 累計投入数・累計投入重量を追加                                //
// 2006/04/26 access_log()の追加と備考の<preをコメント化(枠内で改行させる為)//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);

require_once ('../../../function.php');     // TNK 全共通 function
require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');
access_log();                               // Script Name は自動取得

?>
<!DOCTYPE HTML>
<html>
<head>
<title>機械運転日報</title>
<?php require_once ('../com/PageHeader.php'); ?>
<LINK rel='stylesheet' href='../com/css.css' type='text/css'>
<SCRIPT language='JavaScript' SRC='../com/popup.js'></SCRIPT>
<Script Language='JavaScript'>
function doDecision() {
    if (confirm('確定を行うとこの機械運転日報は修正できなくなります。\n確定しますか？')) {
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
    <!-- <Div class='TITLE'>機械運転日報</Div> -->
    <center>
        <!-- レイアウトテーブル -->
        <table class='LAYOUT'>
            <tr class='LAYOUT'>
                <td class='LAYOUT'>
                    
                    <!-- ヘッダ情報 -->
                    <table border='1' style='width:830;'>
                        <tr>
                            <td CLASS='HED' style='width:90;'>
                                運転日
                            </td>
                            <td align='center' style='width:90;'>
                                <?=outHtml(mu_Date::toString($Report['WorkDate'],'Y/m/d'))?>
                            </td>
                            <td class='HED' style='width:90;'>
                                機械No.
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml($Report['MacNo'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                機械名
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml($Report['MacName'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                指示No.
                            </td>
                            <td align='center' style='width:80;'>
                                <?=outHtml($Report['SijiNo'])?>
                            </td>
                        </tr>
                        <tr>
                            <td class='HED' style='width:90;'>
                                部品No.
                            </td>
                            <td align='center' style='width:90;'>
                                <?=outHtml($Report['ItemCode'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                部品名
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml($Report['ItemName'],16)?>
                            </td>
                            <td class='HED' style='width:80;'>
                                部品材質
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
                                行程No.
                            </td>
                            <td align='center' style='width:90;'>
                                <?=outHtml($Report['KouteiNo'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                行程名
                            </td>
                            <td align='center' style='width:130;'>
                                <?=outHtml($Report['KouteiName'])?>
                            </td>
                            <td class='HED' style='width:90;'>
                                納期
                            </td>
                            <td align='center' style='width:130;'>
                                <?=$Report['DeliveryYYYY']?>/<?=$Report['DeliveryMM']?>/<?=$Report['DeliveryDD']?>
                            </td>
                            <td class='HED' style='width:90;'>
                                指示数量
                            </td>
                            <td class='NUM' style='width:80;'>
                                <?=outHtml($Report['SijiNum'])?>
                            </td>
                            
                        </tr>
                    </table>
                    
                    <!-- 明細部 レイアウトテーブル -->
                    <table class='LAYOUT'>
                        <tr class='LAYOUT'>
                            <td class='LAYOUT'>
                                <table border='1'>
                                    <tr>
                                        <td class='HED' style='width:100;'>
                                            前日良品累計数
                                        </td>
                                        <td class='HED' style='width:100;'>
                                            当日良品数
                                        </td>
                                        <td class='HED' style='width:100;'>
                                            当日良品累計数
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
                                            不良数
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml($Report['Ng'])?>
                                        </td>
                                        <td class='HED' style='width:100'>
                                            段取数
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml($Report['Plan'])?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            終了区分
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml($Report['EndFlg'])?>
                                        </td>
                                        <td class='HED' style='width:100'>
                                            不良区分
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=getNgKbnName($Report['NgKbn'])?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            チョコ停回数
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml($Report['Stop'])?>
                                        </td>
                                        <td class='HED' style='width:100'>
                                            故障回数
                                        </td>
                                        <td style='width:100' align='center'>
                                            <?=outHtml($Report['Failure'])?>
                                        </td>
                                    </tr>
                                </table>
                                <br>
                                    <?php if (@$_REQUEST['SummaryType'] == 2) { ?><font color='#ff0000'><b>＊ 集計モード ＊</b></font><?php } ?>
                                <table border='1'>
                                    <tr>
                                        <td class='HED' style='width:100'>
                                            作業区分
                                        </td>
                                        <td class='HED' style='width:150'>
                                            作業時間
                                        </td>
                                        <td class='HED' style='width:100'>
                                            カット時間
                                        </td>
                                        <td class='HED' style='width:100'>
                                            作業時間(分)
                                        </td>
                                    </tr>
                                <?php for($i=0;$i<$LogNum;$i++) { ?>
                                    <tr>
                                        <td align='center' style='width:100;<?=MachineStateStyle($CsvFlg,$Report['MacState'][$i] )?>'>
                                            <?=outHtml($Report['MacStateName'][$i])?>
                                        </td>
                                        <td align='center' style='width:150'>
                                            <?=outHtml($Report['FromHH'][$i])?>:<?=outHtml($Report['FromMM'][$i])?>～<?=outHtml($Report['ToHH'][$i])?>:<?=outHtml($Report['ToMM'][$i])?>
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
                                            備考
                                        </td>
                                    </tr>
                                </table>
                                <table border='1'>
                                    <tr>
                                        <td style='width:400;height:100' valign='top'>
                                            <!-- オリジナルは<pre></pre> -->
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
                                                    投入材料
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    材料コード
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?=outHtml($Report['InjectionItem'])?>
                                                </td>
                                            </tr>
                                            <?php if ($Report['Type'] == 'B') { ?>
                                            <tr>
                                                <td class='HED' nowrap>
                                                    １本あたりの長さ
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?=outHtml($Report['Length'])?> m&nbsp;
                                                </td>
                                            </tr>
                                            <?php } ?>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    投入数
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?=outHtml($Report['Injection'])?>
                                                    <?php if ($Report['Type'] == 'B') { ?>
                                                    本&nbsp;&nbsp;
                                                    <?php } else { ?>
                                                    個&nbsp;&nbsp;
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    投入重量
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?php if ($Report['Type'] == 'B') { ?>
                                                        <?=outHtml($Report['inWeight'])?>
                                                        Kg&nbsp;&nbsp;
                                                    <?php } else { ?>
                                                        切断材
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    累計投入数
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?=outHtml($Report['SUMinjection'])?>
                                                    <?php if ($Report['Type'] == 'B') { ?>
                                                    本&nbsp;&nbsp;
                                                    <?php } else { ?>
                                                    個&nbsp;&nbsp;
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    累計投入重量
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?php if ($Report['Type'] == 'B') { ?>
                                                        <?=outHtml($Report['SUMinWeight'])?>
                                                        Kg&nbsp;&nbsp;
                                                    <?php } else { ?>
                                                        切断材
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan='2' nowrap align='center'>
                                                    以下はバー材の場合の参考
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    投入端材長さ
                                                </td>
                                                <td style='width:100' align='center'>
                                                    <?=outHtml(sprintf ('%.04f',$Report['Abandonment']))?> m&nbsp;
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class='HED' style='width:100'>
                                                    投入端材重量
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
                                                    寸法チェック
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
                                            <input type='button' value='確定' style='width:80px' onClick='doDecision()'>
                                            <?php } ?>
                                            <?php if (@$_REQUEST['RetUrl'] != '') { ?>
                                            <input type='button' value='戻　る' style='width:80;' onClick='doBack()'>
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
                        
                    <!-- ↓ LAYOUT TABLE ↓ -->
                </td>
            </tr>
        </table>
    </center>
</form>
</body>
</html>
