<?php
// --------------------------------------------------
// �ڡ�������ȥ�����
// --------------------------------------------------


// --------------------------------------------------
// ɽ�����ϥ쥳�����ֹ�μ���
// --------------------------------------------------
function getStartRecNum($ViewPage,$PageListNum)
{
    if (!is_numeric($PageListNum))  return 0;
    if (!is_numeric($ViewPage))     $ViewPage = 1;
    
    $StartRecNum = ($ViewPage - 1) * $PageListNum;
    return $StartRecNum;
}
// --------------------------------------------------
// ɽ����λ�쥳�����ֹ�μ���
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
// �ڡ�������ȥ����Ѥ�Html����
// --------------------------------------------------
function getPageControlHtml($ViewPage,$MaxRows,$PageLine)
{
    if ($ViewPage == null) $ViewPage = 1;
    
    $PrevPage = $ViewPage - 1;
    $NextPage = $ViewPage + 1;
    
    $MaxPage  = (int)($MaxRows / $PageLine);
    if ($MaxRows % $PageLine != 0) $MaxPage++;
    
    // html����
    $Html = "<table border='0' class='LAYOUT'><tr class='LAYOUT'><td class='LAYOUT'>";
    
    if ($ViewPage > 1) $Html .= "<input type='button' value='<- ���ڡ���' onClick='MovePage($PrevPage)'>";
    else               $Html .= "<input type='button' value='<- ���ڡ���' disabled>";
    
    $Html .= "</td><td width='120' align='center' class='LAYOUT'><select name='SelectPage' onChange='MovePage(0)'>";
    
    for ($i=1;$i<=$MaxPage;$i++) {
        if ($i == $ViewPage) $Seleced = ' selected ';
        else                 $Seleced = '';
        $Html .= "<option value='$i'$Seleced>$i</option>";
    }
    
    $Html .= "</select>��$MaxPage</td><td class='LAYOUT'>";
    
    if ($ViewPage != $MaxPage) $Html .= "<input type='button' value='���ڡ��� ->' onClick='MovePage($NextPage)'>";
    else                       $Html .= "<input type='button' value='���ڡ��� ->' disabled>";
    
    $Html .= "</td></tr></table>";
    
    return $Html;
}
//
?>
