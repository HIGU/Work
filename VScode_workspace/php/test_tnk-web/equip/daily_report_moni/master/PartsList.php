<?php 
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理システムの部品マスター保守   List        Client interface 部 //
//                                                  MVC View の List 部     //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   PartsList.php                                       //
// 2006/04/12 access_log() 対応                                             //
// 2006/06/10 equip_partsテーブルを変更し、機械番号と機械名をリストに追加   //
// 2006/06/12 一覧からの呼出ボタンdoView()doEdit()のパラメーターにMacNo追加 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');     // access_log()等で使用
require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/PageControl.php');
access_log();                               // Script Name は自動取得

// 管理者モード
$AdminUser = AdminUser( FNC_MASTER );

// パラメータの格納
setParameter();

// コネクションの取得
$con = getConnection();

if ($Parameter['ProcCode'] != '') { 
    // ＳＱＬの生成
    $sql = MakeSql();
    $rs = pg_query ($con , $sql);
    /** ページコントロール */
    $PageCtl['ViewPage']    = $Parameter['ViewPage'];
    $PageCtl['ListNum']     = $Parameter['ListNum'];
    $PageCtl['RowsNum']     = pg_num_rows ($rs);
    $PageCtl['StartRecNum'] = getStartRecNum($PageCtl['ViewPage'],$PageCtl['ListNum']);
    $PageCtl['EndRecNum']   = getEndRecNum($PageCtl['ViewPage'],$PageCtl['ListNum'],$PageCtl['RowsNum']);
}

// --------------------------------------------------
// パラメータの格納         
// --------------------------------------------------
function setParameter()
{
    global $Parameter;
    
    $Parameter['ProcCode']       = @$_REQUEST['ProcCode'];
    $Parameter['ViewPage']       = @$_REQUEST['ViewPage'];
    $Parameter['ListNum']        = @$_REQUEST['ListNum'];
    $Parameter['FromCode']       = @$_REQUEST['FromCode'];
    $Parameter['ToCode']         = @$_REQUEST['ToCode'];
    $Parameter['Type']           = @$_REQUEST['Type'];
    
}
// --------------------------------------------------
// ＳＱＬの生成     
// --------------------------------------------------
function MakeSql()
{
    global $Parameter;
    
    $sql = "
        SELECT
            to_char(equip_parts.mac_no, 'FM0000')
                                    AS mac_no       ,
            CASE
                WHEN equip_parts.mac_no = 0 THEN '共用データ(初期値)'
                ELSE mac_master.mac_name
            END                     AS mac_name     ,
            equip_parts.item_code   AS item_code    ,
            substr(miitem.midsc, 1, 20)
                                    AS item_name    ,
            miitem.mzist            AS zai          ,
            equip_parts.size        AS size         ,
            equip_parts.use_item    AS use_item     ,
            equip_parts.abandonment AS abandonment  ,
            equip_materials.type    AS type
        FROM
            equip_parts
        LEFT OUTER JOIN miitem ON (equip_parts.item_code = miitem.mipn)
        LEFT OUTER JOIN equip_materials ON (equip_parts.use_item = equip_materials.mtcode)
        LEFT OUTER JOIN equip_machine_master2 AS mac_master USING (mac_no)
        WHERE 0=0
    ";
    
    // where句生成
    if ($Parameter['FromCode'] != '') {
        $sql .= " and equip_parts.item_code >='".pg_escape_string($Parameter['FromCode'])."'";
    }
    if ($Parameter['ToCode'] != '') {
        $sql .= " and equip_parts.item_code <='".pg_escape_string($Parameter['ToCode'])."'";
    }
    
    $sql .= ' order by equip_parts.item_code';
    return $sql;
}

// 共通ヘッダの出力
SetHttpHeader();
?>
<!DOCTYPE HTML>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<script language='JavaScript'>
function doView(code, mac_no) {
    document.MainForm.ProcCode.value = 'VIEW';
    document.MainForm.EDIT_MODE.value = '';
    document.MainForm.Code.value = code;
    document.MainForm.MacNo.value = mac_no;
    document.MainForm.action = 'PartsEntry.php';
    document.MainForm.submit();
}
function doEdit(code, mac_no) {
    document.MainForm.ProcCode.value = 'EDIT';
    document.MainForm.EDIT_MODE.value = 'UPDATE';
    document.MainForm.Code.value = code;
    document.MainForm.MacNo.value = mac_no;
    document.MainForm.action = 'PartsEntry.php';
    document.MainForm.submit();
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
<?php if ($Parameter['ProcCode'] == '') { ?>
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
<form name='MovePageForm' action='PartsList.php' method='post'>
<input type='hidden' name='ProcCode' value='VIEW'>
<input type='hidden' name='FromCode' value='<?=outHtml($Parameter['FromCode'])?>'>
<input type='hidden' name='ToCode' value='<?=outHtml($Parameter['ToCode'])?>'>
<input type='hidden' name='ListNum' value='<?=outHtml($Parameter['ListNum'])?>'>
<input type='hidden' name='ViewPage' value=''>
</form>
<form name='MainForm' method='post'>
<input type='hidden' name='RetUrl' value='PartsList.php?ProcCode=VIEW&FromCode=<?=outHtml($Parameter['FromCode'])?>&ToCode=<?=outHtml($Parameter['ToCode'])?>&ViewPage=<?=$PageCtl['ViewPage']?>&ListNum=<?=$PageCtl['ListNum']?>'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='EDIT_MODE' value=''>
<input type='hidden' name='Code' value=''>
<input type='hidden' name='MacNo' value=''>
    <center>
        <table border="1">
            <tr>
                <td class="HED" nowrap>
                </td>
                <td class="HED" nowrap>
                    機械番号
                </td>
                <td class="HED" nowrap>
                    機械名
                </td>
                <td class="HED" nowrap>
                    部品番号
                </td>
                <td class="HED" nowrap>
                    部品名称
                </td>
                <td class="HED" nowrap>
                    部品材質
                </td>
                <td class="HED" nowrap>
                    寸法
                </td>
                <td class="HED" nowrap>
                    使用材料
                </td>
                <td class="HED" nowrap>
                    タイプ
                </td>
                <td class="HED" nowrap>
                    破材サイズ
                </td>
            </tr>
<?php
        for ($i=$PageCtl['StartRecNum'];$i<=$PageCtl['EndRecNum'];$i++) {
            $row = pg_fetch_array ($rs,$i); 
?>
            <tr>
                <td nowrap>
                    <input type='button' value='表示' onClick="doView('<?=outHtml($row['item_code']), "', '", outHtml($row['mac_no'])?>')"><?php if ($AdminUser) { ?><input type='button' value='修正' onClick="doEdit('<?=outHtml($row['item_code']), "', '", outHtml($row['mac_no'])?>')"><?php } ?>
                <td nowrap align='center'>
                    <?=outHtml($row['mac_no'])?>
                </td>
                <td nowrap>
                    <?=outHtml($row['mac_name'])?>
                </td>
                <td nowrap>
                    <?=outHtml($row['item_code'])?>
                </td>
                <td nowrap style='width:220px;'>
                    <?=outHtml($row['item_name'])?>
                </td>
                <td nowrap>
                    <?=outHtml($row['zai'])?>
                </td>
                <td align='right' nowrap>
                    <?=outHtml($row['size'])?> mm
                </td>
                <td align='center' nowrap>
                    <?=outHtml($row['use_item'])?>
                </td>
                <td align='center' nowrap>
                    <?php if ($row['type'] == 'B') echo ('バー材'); ?>
                    <?php if ($row['type'] == 'C') echo ('切断材'); ?>
                </td>
                <td align='right' nowrap>
                    <?=outHtml($row['abandonment'])?> mm
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
