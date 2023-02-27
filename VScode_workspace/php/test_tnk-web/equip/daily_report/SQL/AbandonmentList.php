<?php 
require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/PageControl.php');

// 管理者モード
$AdminUser = AdminUser( FNC_REPORT );

// コネクションの取得
$con = getConnection();
// sqlの生成
$sql = MakeSql();
// 一覧取得
$rs = pg_query ($con , $sql);


/** ページコントロール */
$PageCtl['ViewPage']    = @$_REQUEST['ViewPage'];
$PageCtl['ListNum']     = @$_REQUEST['ListNum'];
$PageCtl['RowsNum']     = pg_num_rows ($rs);
$PageCtl['StartRecNum'] = getStartRecNum($PageCtl['ViewPage'],$PageCtl['ListNum']);
$PageCtl['EndRecNum']   = getEndRecNum($PageCtl['ViewPage'],$PageCtl['ListNum'],$PageCtl['RowsNum']);

// --------------------------------------------------
// 一覧取得用のＳＱＬ生成
// --------------------------------------------------
function MakeSql()
{
    $FirstParam = true;
    
    $sql = 'select mtcode,mtname,type,style,weight,length from equip_materials where 0=0 ';
    $sql = 'select '
         . '    a.mtcode as mtcode, '
         . '    a.mtname as mtname, '
         . '    a.type   as type,   '
         . '    b.length as length, '
         . '    b.weight as weight '
         . '    from '
         . '        equip_materials a '
         . '    join equip_abandonment_item b on a.mtcode=b.item_code '
         . '    where b.length <> 0  ';
    // where句生成
    if (@$_REQUEST['FromCode'] != '') {
        $sql .= " and a.mtcode >='".pg_escape_string(@$_REQUEST['FromCode'])."'";
    }
    if (@$_REQUEST['ToCode'] != '') {
        $sql .= " and a.mtcode <='".pg_escape_string(@$_REQUEST['ToCode'])."'";
    }
    if (@$_REQUEST['Type'] != 'A') {
        $sql .= " and a.type ='".pg_escape_string(@$_REQUEST['Type'])."'";
    }
    
    $sql .= ' order by mtcode';
    
    return $sql;
}
ob_start('ob_gzhandler');

// 共通ヘッダの出力
SetHttpHeader();
?>
<!DOCTYPE HTML>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<script language='JavaScript'>
function doView(code) {
    MainForm.ProcCode.value = 'VIEW';
    MainForm.EDIT_MODE.value = '';
    MainForm.Code.value = code;
    MainForm.action = 'MaterialsEntry.php';
    MainForm.submit();
}
function doEdit(code) {
    MainForm.ProcCode.value = 'EDIT';
    MainForm.EDIT_MODE.value = 'UPDATE';
    MainForm.Code.value = code;
    MainForm.action = 'MaterialsEntry.php';
    MainForm.submit();
}
function MovePage(page) {
    var NextPage = page;
    if (page == 0) {
        var idx = MainForm.SelectPage.selectedIndex;
        NextPage = MainForm.SelectPage.options[idx].text;
    }
    
    MovePageForm.ViewPage.value = NextPage;
    MovePageForm.submit();
}
</script>
</head>
<body>
<form name='MovePageForm' action='AbandonmentList.php' method='post'>
<input type='hidden' name='ProcCode' value='VIEW'>
<input type='hidden' name='FromCode' value='<?=$_REQUEST['FromCode']?>'>
<input type='hidden' name='ToCode' value='<?=$_REQUEST['ToCode']?>'>
<input type='hidden' name='Type' value='<?=$_REQUEST['Type']?>'>
<input type='hidden' name='ListNum' value='<?=$_REQUEST['ListNum']?>'>
<input type='hidden' name='ViewPage' value=''>
</form>

<form name='MainForm' method='post'>
<input type='hidden' name='RetUrl' value='Abandonment.php?ProcCode=VIEW&FromCode=<?=$_REQUEST['FromCode']?>&ToCode=<?=$_REQUEST['ToCode']?>&Type=<?=$_REQUEST['Type']?>&ViewPage=<?=$PageCtl['ViewPage']?>&ListNum=<?=$PageCtl['ListNum']?>'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='Code' value=''>
<?php if (@$_REQUEST['ProcCode'] == '') { ?>
<table border='0' class='LAYOUT' width='100%' height='100%'>
    <tr class='LAYOUT'>
        <td class='LAYOUT' align='center'>
            抽出条件を入力して下さい
        </td>
    </tr>
</table>
<?php } else if ($PageCtl['RowsNum'] == 0) { ?>
<table border='0' class='LAYOUT' width='100%' height='100%'>
    <tr class='LAYOUT'>
        <td class='LAYOUT' align='center'>
            該当データが存在しません
        </td>
    </tr>
</table>
<?php } else { ?>
    <center>
        <table border='1'>
            <tr>
                <td class='HED'>
                    材料コード
                </td>
                <td class='HED'>
                    材料名称
                </td>
                <td class='HED'>
                    タイプ
                </td>
                <td class='HED'>
                    端材サイズ
                </td>
                <td class='HED'>
                    端材重量
                </td>
            </tr>
<?php
        for ($i=$PageCtl['StartRecNum'];$i<=$PageCtl['EndRecNum'];$i++) {
            $row = pg_fetch_array ($rs,$i); 
?>
            <tr>
                <td nowrap>
                    <?=outHtml($row['mtcode'])?>
                </td>
                <td nowrap>
                    <?=outHtml($row['mtname'])?>
                </td>
                <td nowrap>
                    <?php if ($row['type'] == 'B') echo('バー材'); ?>
                    <?php if ($row['type'] == 'C') echo('切断材'); ?>
                </td>
                <td align='right' nowrap>
                    <?=outHtml($row['length'])?> m
                </td>
                <td align='right' nowrap>
                    <?=outHtml($row['weight'])?> kg
                </td>
            </tr>
    <?php } ?>
        </table>
        <br>
<?= getPageControlHtml($PageCtl['ViewPage'],$PageCtl['RowsNum'],$PageCtl['ListNum']) ?>
    </center>
</form>
<?php } ?>
</body>
</html>
<?php ob_end_flush(); ?>
