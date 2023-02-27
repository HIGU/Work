<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ�ε�����ž���� ���̴ؿ����                          //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2004/07/15 Created   ReportList.php                                      //
// 2005/05/20 getConnection()��pg_pConnect()�ѥ�᡼�������� �ʲ��򥳥��� //
//            �㤦�ѥ�᡼��������³����ȿ����˥��ͥ�����󤬽���뤿��    //
// 2005/07/09 �ڡ������ץ��������Ѵؿ�SelectPageListNumOptions()��      //
//            SelectPageListNumOptions($default=15)��(����ͤ����Ǥ���)   //
// 2007/04/04 outHtml()�ؿ���Ⱦ�Ѵ�����20��Ķ�����������ѥ��ʤ�Ⱦ�Ѥ��ɲ� //
// 2007/04/14 �ۡ���� �� �ܡ���� ������                                   //
//////////////////////////////////////////////////////////////////////////////
// require_once ('function.php');   // ���ޥƥϥ�Ǥʤ���ʬ��require���Ƥ���Τ�����(ñ�ʤ�ְ㤤��)
require_once ('/home/www/html/tnk-web/pgsql.php');

/****************************************************************************************************/
/*                                          �����ƥ������                                          */
/****************************************************************************************************/

// --------------------------------------------------
// DataBase �Υ��ͥ����������
// --------------------------------------------------
function getConnection()
{
    // $Connection = pg_pConnect(" dbname=".DB_NAME." user=".DB_USER." password=".DB_PASSWD);
    // $Connection = pg_pConnect('host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD);
    $Connection = connectDB(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWD);
    if (!pg_ping($Connection)) {
        require_once(ERROR_PAGE);
        exit();
    }
    return $Connection;
}
// --------------------------------------------------
// ���� Http �إå��ν���
// --------------------------------------------------
function SetHttpHeader()
{
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}
// --------------------------------------------------
// �����Ը��¤�Ƚ��
// --------------------------------------------------
function AdminUser($function)
{
    @session_start();
    $LoginUser = @$_SESSION['User_ID'];
    
    if ($LoginUser == '') {
        $SYSTEM_MESSAGE = "��������󤬼����Ǥ��ޤ���Ǥ���\n";
        require_once (DOCUMENT_ROOT . COMMON_PATH. ERROR_PAGE);
        exit();
    }
    
    $con = getConnection();
    $rs = pg_query($con,"select * from equip_account where function='$function' and staff='$LoginUser'");
    if ($row = pg_fetch_array ($rs)) {
        return true;
    } else {
        return false;
    }
}
// --------------------------------------------------
// htmlɽ����ʸ�����Ѵ�
// --------------------------------------------------
function outHtml($Str , $Len=0)
{
    $Str = str_replace ('"'    ,'&quot;' ,$Str);
    $Str = str_replace ('<'    ,'&lt;'   ,$Str);
    $Str = str_replace ('>'    ,'&gt;'   ,$Str);
    $Str = str_replace ('\''   ,'&#39;'  ,$Str);
    $Str = str_replace ('\\\\' ,'\\'     ,$Str);
    
    if ($Len > 0) $Str = mb_substr($Str,0,$Len);
    
    if (strlen($Str) > 20) $Str = mb_convert_kana($Str, 'k');
    
    return $Str;
}

function MLog($memo='') {

    $fp = fopen(DOCUMENT_ROOT . CONTEXT_PATH . 'Log.txt','a');
    $today = date('Y/m/d H:i:s');
//    $today = microtime();
    fwrite($fp,$today.' -- ' . $memo . "\n");
    fclose($fp);

}


/****************************************************************************************************/
/*                                          �ӥ��ͥ����å���                                      */
/****************************************************************************************************/

// --------------------------------------------------
// ����ɽ���Ѥν��Ϲ� option ����
// --------------------------------------------------
function SelectPageListNumOptions($default=15)
{
    $Options = '';
    if ($default == 10) {
        $Options .= "<option value='10' selected>10��</option>";
    } else {
        $Options .= "<option value='10'>10��</option>";
    }
    if ($default == 15) {
        $Options .= "<option value='15' selected>15��</option>";
    } else {
        $Options .= "<option value='15'>15��</option>";
    }
    if ($default == 20) {
        $Options .= "<option value='20' selected>20��</option>";
    } else {
        $Options .= "<option value='20'>20��</option>";
    }
    if ($default == 30) {
        $Options .= "<option value='30' selected>30��</option>";
    } else {
        $Options .= "<option value='30'>30��</option>";
    }
    if ($default == 50) {
        $Options .= "<option value='50' selected>50��</option>";
    } else {
        $Options .= "<option value='50'>50��</option>";
    }
    if ($default == 100) {
        $Options .= "<option value='100' selected>100��</option>";
    } else {
        $Options .= "<option value='100'>100��</option>";
    }
    if ($default == 500) {
        $Options .= "<option value='500' selected>500��</option>";
    } else {
        $Options .= "<option value='500'>500��</option>";
    }
    if ($default == 1000) {
        $Options .= "<option value='1000' selected>1000��</option>";
    } else {
        $Options .= "<option value='1000'>1000��</option>";
    }
    
    return $Options;
}
// --------------------------------------------------
// ��ȶ�ʬ̾�μ���
// --------------------------------------------------
function getMachineStateName($CsvFlg,$State)
{
    $retVal = '';
    if ($CsvFlg == '1') {
        switch ($State) {
            case '0':
                $retVal = '�Ÿ�OFF';
                break;
            case '1':
                $retVal = '��ư��ž';
                break;
            case '2':
                $retVal = '���顼��';
                break;
            case '3':
                $retVal = '�����';
                break;
            case '4':
                $retVal = 'Net��ư';
                break;
            case '5':
                $retVal = 'Net��λ';
                break;
            case '10':
                $retVal = '�ȵ���';
                break;
            case '11':
                $retVal = '�ʼ���';
                break;
            case '12':
                $retVal = '�ξ㽤��';
                break;
            case '13':
                $retVal = '�϶��';
                break;
            case '14':
                $retVal = '̵�ͱ�ž';
                break;
            case '15':
                $retVal = '����';
                break;
            default :
                $retVal = "[̤���]";
                break;
        }
    } else {
        switch ($State) {
            case '0':
                $retVal = '�Ÿ�OFF';
                break;
            case '1':
                $retVal = '��ư��ž';
                break;
            case '2':
                $retVal = '���顼��';
                break;
            case '3':
                $retVal = '�����';
                break;
            case '4':
                $retVal = '�ȵ���';
                break;
            case '5':
                $retVal = '�ʼ���';
                break;
            case '6':
                $retVal = '�ξ㽤��';
                break;
            case '7':
                $retVal = '�϶��';
                break;
            case '8':
                $retVal = '̵�ͱ�ž';
                break;
            case '9':
                $retVal = '����';
                break;
            case '10':
                $retVal = 'ͽ����';
                break;
            case '11':
                $retVal = 'ͽ����';
                break;
            default :
                $retVal = "[̤���]";
                break;
        }
    }
    
    return $retVal;
}
// --------------------------------------------------
// ��ȶ�ʬ OPTION ��������
// --------------------------------------------------
function MachineStateSelectOptions($CsvFlg,$State)
{
    $OPTIONS = "<option value=''></option>";
    
    if ($CsvFlg == 1) {
        // Ĺ���ʤ�ΤǤ�����
        if ($State ==  '0') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '0' $SELECTED>�Ÿ�OFF</option>";
        if ($State ==  '1') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '1' $SELECTED>��ư��ž</option>";
        if ($State ==  '2') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '2' $SELECTED>���顼��</option>";
        if ($State ==  '3') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '3' $SELECTED>�����</option>";
        if ($State ==  '4') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '4' $SELECTED>Net��ư</option>";
        if ($State ==  '5') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '5' $SELECTED>Net��λ</option>";
        if ($State == '10') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='10' $SELECTED>�ȵ���</option>";
        if ($State == '11') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='11' $SELECTED>�ʼ���</option>";
        if ($State == '12') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='12' $SELECTED>�ξ㽤��</option>";
        if ($State == '13') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='13' $SELECTED>�϶��</option>";
        if ($State == '14') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='14' $SELECTED>̵�ͱ�ž</option>";
        if ($State == '15') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='15' $SELECTED>����</option>";
    } else {
        if ($State ==  '0') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '0' $SELECTED>�Ÿ�OFF</option>";
        if ($State ==  '1') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '1' $SELECTED>��ư��ž</option>";
        if ($State ==  '2') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '2' $SELECTED>���顼��</option>";
        if ($State ==  '3') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '3' $SELECTED>�����</option>";
        if ($State ==  '4') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '4' $SELECTED>�ȵ���</option>";
        if ($State ==  '5') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '5' $SELECTED>�ʼ���</option>";
        if ($State ==  '6') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '6' $SELECTED>�ξ㽤��</option>";
        if ($State ==  '7') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '7' $SELECTED>�϶��</option>";
        if ($State ==  '8') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '8' $SELECTED>̵�ͱ�ž</option>";
        if ($State ==  '9') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '9' $SELECTED>����</option>";
        if ($State == '10') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='10' $SELECTED>ͽ����</option>";
        if ($State == '11') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='11' $SELECTED>ͽ����</option>";
    }
    
    return $OPTIONS;
}
// --------------------------------------------------
// ��ȶ�ʬ�ǥ����� style  ����
// --------------------------------------------------
function MachineStateStyle($CsvFlg,$State)
{
    $COLOR        = 'red';
    $BACKGROUND   = '';
    if ($CsvFlg == 1) {
        if ($State ==  '0') { $COLOR = 'white';   $BACKGROUND = 'black';   }
        if ($State ==  '1') { $COLOR = 'white';   $BACKGROUND = 'green';   }
        if ($State ==  '2') { $COLOR = 'white';   $BACKGROUND = 'red';     }
        if ($State ==  '3') { $COLOR = 'black';   $BACKGROUND = 'yellow';  }
        if ($State ==  '4') { $COLOR = 'black';   $BACKGROUND = 'orange';  }
        if ($State ==  '5') { $COLOR = 'white';   $BACKGROUND = 'maroon';  }
        if ($State == '10') { $COLOR = 'white';   $BACKGROUND = 'purple';  }
        if ($State == '11') { $COLOR = 'black';   $BACKGROUND = 'aqua';    }
        if ($State == '12') { $COLOR = 'black';   $BACKGROUND = 'gray';    }
        if ($State == '13') { $COLOR = 'black';   $BACKGROUND = 'silver';  }
        if ($State == '14') { $COLOR = 'white';   $BACKGROUND = 'blue';    }
        if ($State == '15') { $COLOR = 'black';   $BACKGROUND = 'magenta'; }
    } else {
        if ($State ==  '0') { $COLOR = 'white';   $BACKGROUND = 'black';   }
        if ($State ==  '1') { $COLOR = 'white';   $BACKGROUND = 'green';   }
        if ($State ==  '2') { $COLOR = 'white';   $BACKGROUND = 'red';     }
        if ($State ==  '3') { $COLOR = 'black';   $BACKGROUND = 'yellow';  }
        if ($State ==  '4') { $COLOR = 'white';   $BACKGROUND = 'purple';  }
        if ($State ==  '5') { $COLOR = 'black';   $BACKGROUND = 'aqua';    }
        if ($State ==  '6') { $COLOR = 'white';   $BACKGROUND = 'gray';    }
        if ($State ==  '7') { $COLOR = 'black';   $BACKGROUND = 'silver';  }
        if ($State ==  '8') { $COLOR = 'white';   $BACKGROUND = 'blue';    }
        if ($State ==  '9') { $COLOR = 'black';   $BACKGROUND = 'magenta'; }
        if ($State == '10') { $COLOR = 'black';   $BACKGROUND = 'orange';  }
        if ($State == '11') { $COLOR = 'white';   $BACKGROUND = 'maroon';  }
    }
    
    return "color:$COLOR;background-color:$BACKGROUND;";
}
// --------------------------------------------------
// ���ɶ�ʬ OPTION ��������
// --------------------------------------------------
function NgKbnSelectOptions($Kbn)
{
    $OPTIONS = "<option value=''></option>";
    
    if ($Kbn == '01') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='01' $SELECTED>����</option>";
    if ($Kbn == '02') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='02' $SELECTED>���</option>";
    if ($Kbn == '03') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='03' $SELECTED>���ȥåץ��</option>";
    if ($Kbn == '04') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='04' $SELECTED>�ϥ�󥰥ߥ�</option>";
    if ($Kbn == '05') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='05' $SELECTED>��ƥ����ʡ��ߥ�</option>";
    if ($Kbn == '06') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='06' $SELECTED>��������</option>";
    if ($Kbn == '07') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='07' $SELECTED>�ͥ�</option>";
    if ($Kbn == '08') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='08' $SELECTED>���̼��</option>";
    if ($Kbn == '09') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='09' $SELECTED>��Ĺ</option>";
    if ($Kbn == '10') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='10' $SELECTED>���</option>";
    if ($Kbn == '11') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='11' $SELECTED>�ۡ����</option>";
    if ($Kbn == '12') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='12' $SELECTED>������������</option>";
    if ($Kbn == '13') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='13' $SELECTED>����¾</option>";
    
    return $OPTIONS;

}
// --------------------------------------------------
// ��λ��ʬ̾�μ���
// --------------------------------------------------
function getNgKbnName($Kbn)
{
    $retVal = "";
    
    switch ($Kbn) {
        case  '01' :
            $retVal = '����';
            break;
        case  '02' :
            $retVal = '���';
            break;
        case  '03' :
            $retVal = '���ȥåץ��';
            break;
        case  '04' :
            $retVal = '�ϥ�󥰥ߥ�';
            break;
        case  '05' :
            $retVal = '��ƥ����ʡ��ߥ�';
            break;
        case  '06' :
            $retVal = '��������';
            break;
        case  '07' :
            $retVal = '�ͥ�';
            break;
        case  '08' :
            $retVal = '���̼��';
            break;
        case  '09' :
            $retVal = '��Ĺ';
            break;
        case  '10' :
            $retVal = '���';
            break;
        case  '11' :
            $retVal = '�ܡ����';
            break;
        case  '12' :
            $retVal = '������������';
            break;
        case  '13' :
            $retVal = '����¾';
            break;
        default :
            $retVal = '';
            break;
    }
    
    return $retVal;
}
// --------------------------------------------------
// ��Ȼ��֤η׻�
// --------------------------------------------------
function CalWorkTime($FromDate,$FromTime,$ToDate,$ToTime)
{
    $FromHH = (int)($FromTime / 100);
    $FromMM = (int)($FromTime - $FromHH * 100);
    $ToHH   = (int)($ToTime / 100);
    $ToMM   = (int)($ToTime - $ToHH * 100);
    
    
    $FromMinutes = $FromHH * 60 + $FromMM;
    $ToMinutes   = $ToHH   * 60 + $ToMM;
    
    $ToMinutes += (mu_date::getIntervalDay($FromDate,$ToDate )) * 24 * 60;
    //if ($FromMinutes < 510) $FromMinutes += 24 * 60;
    //if ($ToMinutes   < 510) $ToMinutes   += 24 * 60;
    
    $WorkTime = $ToMinutes-$FromMinutes;
    
    return $WorkTime;

}
// --------------------------------------------------
// ���ꤵ�줿�����̤��ڤ�Τ�
// --------------------------------------------------
function Mfloor ($num,$point) {
    
    // ü������������ǰ���ʸ����
    $num = sprintf("%s",$num);
    $tmp = (int)($num * pow (10,$point));
    $tmp /= pow (10,$point);
    return $tmp;
    
}

?>
