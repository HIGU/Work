<?php
// --------------------------------------------------
// ページコントロール用
// --------------------------------------------------


// --------------------------------------------------
// 表示開始レコード番号の取得
// --------------------------------------------------
function getStartRecNum($ViewPage,$PageListNum)
{
    if (!is_numeric($PageListNum))  return 0;
    if (!is_numeric($ViewPage))     $ViewPage = 1;
    
    $StartRecNum = ($ViewPage - 1) * $PageListNum;
    return $StartRecNum;
}
// --------------------------------------------------
// 表示終了レコード番号の取得
// --------------------------------------------------
function getEndRecNum($ViewPage,$PageListNum,$Rows)
{
    if (!is_numeric($PageListNum))  return 0;
    if (!is_numeric($Rows))         return 0;
    if (!is_numeric($ViewPage))     $ViewPage = 1;

    $EndRecNum = $ViewPage * $PageListNum - 1;
    if ($EndRecNum >= $Rows) $EndRecNum = $Rows-1;
    return $EndRecNum;
}
// --------------------------------------------------
// ページコントロール用のHtml出力
// --------------------------------------------------
function getPageControlHtml($ViewPage,$MaxRows,$PageLine)
{
    if ($ViewPage == null) $ViewPage = 1;
    
    $PrevPage = $ViewPage - 1;
    $NextPage = $ViewPage + 1;
    
    $MaxPage  = (int)($MaxRows / $PageLine);
    if ($MaxRows % $PageLine != 0) $MaxPage++;
    
    // html生成
    $Html = "<table border='0' class='LAYOUT'><tr class='LAYOUT'><td class='LAYOUT'>";
    
    if ($ViewPage > 1) $Html .= "<input type='button' value='<- 前ページ' onClick='MovePage($PrevPage)'>";
    else               $Html .= "<input type='button' value='<- 前ページ' disabled>";
    
    $Html .= "</td><td width='120' align='center' class='LAYOUT'><select name='SelectPage' onChange='MovePage(0)'>";
    
    for ($i=1;$i<=$MaxPage;$i++) {
        if ($i == $ViewPage) $Seleced = ' selected ';
        else                 $Seleced = '';
        $Html .= "<option value='$i'$Seleced>$i</option>";
    }
    
    $Html .= "</select>／$MaxPage</td><td class='LAYOUT'>";
    
    if ($ViewPage != $MaxPage) $Html .= "<input type='button' value='次ページ ->' onClick='MovePage($NextPage)'>";
    else                       $Html .= "<input type='button' value='次ページ ->' disabled>";
    
    $Html .= "</td></tr></table>";
    
    return $Html;
}
//
?>
