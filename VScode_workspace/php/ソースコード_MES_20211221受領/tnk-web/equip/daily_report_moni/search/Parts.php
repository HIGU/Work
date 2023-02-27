<?php 
ini_set('error_reporting', E_ALL);

require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/PageControl.php');

// コネクションの取得
$con = getConnection();

$sql = 'select ' 
     . '    equip_parts.item_code   as item_code, '
     . '    miitem.midsc            as item_name, '
     . '    miitem.mzist            as zai, '
     . '    equip_parts.size        as size, '
     . '    equip_parts.use_item    as use_item, '
     . '    equip_parts.abandonment as abandonment '
     . 'from '
     . '    equip_parts '
     . 'left outer join miitem on equip_parts.item_code = miitem.mipn '
     . ' order by equip_parts.item_code';
$rs = pg_query ($con , $sql);

/** ページコントロール */
$PageCtl['ViewPage']    = @$_REQUEST['ViewPage'];
$PageCtl['ListNum']     = 10;
$PageCtl['RowsNum']     = pg_num_rows ($rs);
$PageCtl['StartRecNum'] = getStartRecNum($PageCtl['ViewPage'],$PageCtl['ListNum']);
$PageCtl['EndRecNum']   = getEndRecNum($PageCtl['ViewPage'],$PageCtl['ListNum'],$PageCtl['RowsNum']);

ob_start('ob_gzhandler');
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<SCRIPT language='JavaScript' SRC='../com/popup.js'></SCRIPT>
<script language='JavaScript'>
function doSelect(code,name){
    if (navigator.userAgent.indexOf('MSIE') > -1) {
        parent.returnValue = new Array(code,name);
    } else {
        window.opener.NnRetValue = new Array(code,name);
        window.opener.NnReturn();
    }
    window.close();
}
function MovePage(page) {
    var NextPage = page;
    if (page == 0) {
        var idx = document.MainForm.SelectPage.selectedIndex;
        NextPage = document.MainForm.SelectPage.options[idx].text;
    }
    
    document.MovePageForm.ViewPage.value = NextPage;
    document.MovePageForm.submit();
}
</script>
</head>
<body>
<form name='MovePageForm' action='Parts.php' method='post'>
<input type='hidden' name='ProcCode' value='VIEW'>
<input type='hidden' name='ViewPage' value=''>
</form>
<form name='MainForm'>
    <center>
<?php if (pg_num_rows ($rs) == 0){ ?>
<table border='0' class='LAYOUT' width='100%' height='100%'>
    <tr class='LAYOUT'>
        <td class='LAYOUT' align='center'>
            該当データが存在しません
        </td>
    </tr>
</table>
<?php } else { ?>
        <table border='1'>
            <tr>
                <td class='HED'>
                </td>
                <td class='HED'>
                    部品番号
                </td>
                <td class='HED'>
                    部品名称
                </td>
                <td class='HED'>
                    部品材質
                </td>
                <td class='HED'>
                    寸法
                </td>
                <td class='HED'>
                    使用材料
                </td>
                <td class='HED'>
                    破材サイズ
                </td>
            </tr>
<?php
        for ($i=@$PageCtl['StartRecNum'];$i<=@$PageCtl['EndRecNum'];$i++) {
            $row = pg_fetch_array ($rs,$i); 
?>
            <tr>
                <td>
                    <input type='button' value='選択' onClick='doSelect("<?=outHtml($row['item_code'])?>","<?=outHtml($row['item_name'])?>")'>
                </td>
                <td nowrap>
                    <?=outHtml($row['item_code'])?>
                </td>
                <td nowrap>
                    <?=outHtml($row['item_name'])?>
                </td>
                <td nowrap>
                    <?=outHtml($row['zai'])?>
                </td>
                <td align='right' nowrap>
                    <?=outHtml($row['size'])?>
                </td>
                <td align='center' nowrap>
                    <?=outHtml($row['use_item'])?>
                </td>
                <td align='right' nowrap>
                    <?=outHtml($row['abandonment'])?>
                </td>
            </tr>
  <?php } ?>
        </table>
<?= getPageControlHtml($PageCtl['ViewPage'],$PageCtl['RowsNum'],$PageCtl['ListNum']) ?>
    </center>
</form>
<?php } ?>
</body>
</html>
