<?php 
ini_set('error_reporting', E_ALL);

require_once ('../com/define.php');
require_once ('../com/function.php');

// コネクションの取得
$con = getConnection();

$sql = 'select mtcode,mtname,type,style,weight,length from equip_materials order by mtcode';
$rs = pg_query ($con , $sql);

ob_start('ob_gzhandler');
?>
<!DOCTYPE HTML>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<SCRIPT language='JavaScript' SRC='../com/popup.js'></SCRIPT>
<script language="JavaScript">
function doSelect(code,name){

    if (navigator.userAgent.indexOf('MSIE') > -1) {
        parent.returnValue = new Array(code,name);
    } else {
        window.opener.NnRetValue = new Array(code,name);
        window.opener.NnReturn();
    }
    window.close();
}
</script>
</head>
<body>
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
                    材料コード
                </td>
                <td class='HED'>
                    材料名称
                </td>
                <td class='HED'>
                    タイプ
                </td>
                <td class='HED'>
                    部品材質
                </td>
                <td class='HED'>
                    単位重量
                </td>
                <td class='HED'>
                    標準長さ
                </td>
            </tr>
            <?php while ($row = pg_fetch_array ($rs)) { ?>
            <tr>
                <td>
                    <input type='button' value='選択' onClick='doSelect("<?=outHtml($row['mtcode'])?>","<?=outHtml($row['mtname'])?>")'>
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
                <td nowrap>
                    <?=outHtml($row['style'])?>
                </td>
                <td align='right' nowrap>
                    <?=outHtml(sprintf ('%.04f',$row['weight']))?>
                </td>
                <td align='right' nowrap>
                    <?=outHtml(sprintf('%.04f',$row['length']))?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </center>
</form>
<?php } ?>
</body>
</html>
