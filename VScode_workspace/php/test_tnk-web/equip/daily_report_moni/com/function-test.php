<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理システムの機械運転日報 共通関数定義                          //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2004/07/15 Created   ReportList.php                                      //
// 2005/05/20 getConnection()のpg_pConnect()パラメータを統一 以下をコメント //
//            違うパラメーターで接続すると新規にコネクションが出来るため    //
// 2005/07/09 ページオプション出力用関数SelectPageListNumOptions()を→      //
//            SelectPageListNumOptions($default=15)へ(初期値を指定できる)   //
// 2007/04/04 outHtml()関数に半角換算で20を超えた場合に全角カナを半角へ追加 //
// 2007/04/14 ホール穴 → ボール穴 へ訂正                                   //
//////////////////////////////////////////////////////////////////////////////
// require_once ('function.php');   // ←マテハンでなぜ自分をrequireしているのか不明(単なる間違い？)
require_once ('/var/www/html/pgsql.php');

/****************************************************************************************************/
/*                                          システム制御系                                          */
/****************************************************************************************************/

// --------------------------------------------------
// DataBase のコネクションを取得
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
// 共通 Http ヘッダの出力
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
// 管理者権限を判断
// --------------------------------------------------
function AdminUser($function)
{
    @session_start();
    $LoginUser = @$_SESSION['User_ID'];
    
    if ($LoginUser == '') {
        $SYSTEM_MESSAGE = "ログイン情報が取得できませんでした\n";
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
// html表示用文字列変換
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
/*                                          ビジネスロジック系                                      */
/****************************************************************************************************/

// --------------------------------------------------
// 一覧表示用の出力行 option タグ
// --------------------------------------------------
function SelectPageListNumOptions($default=15)
{
    $Options = '';
    if ($default == 10) {
        $Options .= "<option value='10' selected>10行</option>";
    } else {
        $Options .= "<option value='10'>10行</option>";
    }
    if ($default == 15) {
        $Options .= "<option value='15' selected>15行</option>";
    } else {
        $Options .= "<option value='15'>15行</option>";
    }
    if ($default == 20) {
        $Options .= "<option value='20' selected>20行</option>";
    } else {
        $Options .= "<option value='20'>20行</option>";
    }
    if ($default == 30) {
        $Options .= "<option value='30' selected>30行</option>";
    } else {
        $Options .= "<option value='30'>30行</option>";
    }
    if ($default == 50) {
        $Options .= "<option value='50' selected>50行</option>";
    } else {
        $Options .= "<option value='50'>50行</option>";
    }
    if ($default == 100) {
        $Options .= "<option value='100' selected>100行</option>";
    } else {
        $Options .= "<option value='100'>100行</option>";
    }
    if ($default == 500) {
        $Options .= "<option value='500' selected>500行</option>";
    } else {
        $Options .= "<option value='500'>500行</option>";
    }
    if ($default == 1000) {
        $Options .= "<option value='1000' selected>1000行</option>";
    } else {
        $Options .= "<option value='1000'>1000行</option>";
    }
    
    return $Options;
}
// --------------------------------------------------
// 作業区分名称取得
// --------------------------------------------------
function getMachineStateName($CsvFlg,$State)
{
    $retVal = '';
    if ($CsvFlg == '1') {
        switch ($State) {
            case '0':
                $retVal = '電源OFF';
                break;
            case '1':
                $retVal = '自動運転';
                break;
            case '2':
                $retVal = 'アラーム';
                break;
            case '3':
                $retVal = '停止中';
                break;
            case '4':
                $retVal = 'Net起動';
                break;
            case '5':
                $retVal = 'Net終了';
                break;
            case '10':
                $retVal = '暖機中';
                break;
            case '11':
                $retVal = '段取中';
                break;
            case '12':
                $retVal = '故障修理';
                break;
            case '13':
                $retVal = '刃具交換';
                break;
            case '14':
                $retVal = '無人運転';
                break;
            case '15':
                $retVal = '中断';
                break;
            default :
                $retVal = "[未定義]";
                break;
        }
    } else {
        switch ($State) {
            case '0':
                $retVal = '電源OFF';
                break;
            case '1':
                $retVal = '自動運転';
                break;
            case '2':
                $retVal = 'アラーム';
                break;
            case '3':
                $retVal = '停止中';
                break;
            case '4':
                $retVal = '暖機中';
                break;
            case '5':
                $retVal = '段取中';
                break;
            case '6':
                $retVal = '故障修理';
                break;
            case '7':
                $retVal = '刃具交換';
                break;
            case '8':
                $retVal = '無人運転';
                break;
            case '9':
                $retVal = '中断';
                break;
            case '10':
                //$retVal = '予備１';
                $retVal = '計画停止';
                break;
            case '11':
                //$retVal = '予備２';
                $retVal = '優先停止';
                break;
            default :
                $retVal = "[未定義]";
                break;
        }
    }
    
    return $retVal;
}
// --------------------------------------------------
// 作業区分 OPTION タグ取得
// --------------------------------------------------
function MachineStateSelectOptions($CsvFlg,$State)
{
    $OPTIONS = "<option value=''></option>";
    
    if ($CsvFlg == 1) {
        // 長くなるのであえて
        if ($State ==  '0') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '0' $SELECTED>電源OFF</option>";
        if ($State ==  '1') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '1' $SELECTED>自動運転</option>";
        if ($State ==  '2') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '2' $SELECTED>アラーム</option>";
        if ($State ==  '3') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '3' $SELECTED>停止中</option>";
        if ($State ==  '4') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '4' $SELECTED>Net起動</option>";
        if ($State ==  '5') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '5' $SELECTED>Net終了</option>";
        if ($State == '10') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='10' $SELECTED>暖機中</option>";
        if ($State == '11') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='11' $SELECTED>段取中</option>";
        if ($State == '12') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='12' $SELECTED>故障修理</option>";
        if ($State == '13') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='13' $SELECTED>刃具交換</option>";
        if ($State == '14') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='14' $SELECTED>無人運転</option>";
        if ($State == '15') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='15' $SELECTED>中断</option>";
    } else {
        if ($State ==  '0') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '0' $SELECTED>電源OFF</option>";
        if ($State ==  '1') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '1' $SELECTED>自動運転</option>";
        if ($State ==  '2') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '2' $SELECTED>アラーム</option>";
        if ($State ==  '3') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '3' $SELECTED>停止中</option>";
        if ($State ==  '4') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '4' $SELECTED>暖機中</option>";
        if ($State ==  '5') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '5' $SELECTED>段取中</option>";
        if ($State ==  '6') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '6' $SELECTED>故障修理</option>";
        if ($State ==  '7') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '7' $SELECTED>刃具交換</option>";
        if ($State ==  '8') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '8' $SELECTED>無人運転</option>";
        if ($State ==  '9') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value= '9' $SELECTED>中断</option>";
        if ($State == '10') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='10' $SELECTED>予備１</option>";
        if ($State == '11') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='11' $SELECTED>予備２</option>";
    }
    
    return $OPTIONS;
}
// --------------------------------------------------
// 作業区分デザイン style  取得
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
// 不良区分 OPTION タグ取得
// --------------------------------------------------
function NgKbnSelectOptions($Kbn)
{
    $OPTIONS = "<option value=''></option>";
    
    if ($Kbn == '01') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='01' $SELECTED>外径</option>";
    if ($Kbn == '02') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='02' $SELECTED>内径</option>";
    if ($Kbn == '03') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='03' $SELECTED>ストップリング</option>";
    if ($Kbn == '04') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='04' $SELECTED>Ｏリングミゾ</option>";
    if ($Kbn == '05') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='05' $SELECTED>リティーナーミゾ</option>";
    if ($Kbn == '06') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='06' $SELECTED>シート面</option>";
    if ($Kbn == '07') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='07' $SELECTED>ネジ</option>";
    if ($Kbn == '08') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='08' $SELECTED>二面取り</option>";
    if ($Kbn == '09') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='09' $SELECTED>全長</option>";
    if ($Kbn == '10') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='10' $SELECTED>刻印</option>";
    if ($Kbn == '11') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='11' $SELECTED>ホール穴</option>";
    if ($Kbn == '12') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='12' $SELECTED>キズ．ダコン</option>";
    if ($Kbn == '13') $SELECTED = ' selected '; else $SELECTED = ''; $OPTIONS .= "<option value='13' $SELECTED>その他</option>";
    
    return $OPTIONS;

}
// --------------------------------------------------
// 終了区分名称取得
// --------------------------------------------------
function getNgKbnName($Kbn)
{
    $retVal = "";
    
    switch ($Kbn) {
        case  '01' :
            $retVal = '外径';
            break;
        case  '02' :
            $retVal = '内径';
            break;
        case  '03' :
            $retVal = 'ストップリング';
            break;
        case  '04' :
            $retVal = 'Ｏリングミゾ';
            break;
        case  '05' :
            $retVal = 'リティーナーミゾ';
            break;
        case  '06' :
            $retVal = 'シート面';
            break;
        case  '07' :
            $retVal = 'ネジ';
            break;
        case  '08' :
            $retVal = '二面取り';
            break;
        case  '09' :
            $retVal = '全長';
            break;
        case  '10' :
            $retVal = '刻印';
            break;
        case  '11' :
            $retVal = 'ボール穴';
            break;
        case  '12' :
            $retVal = 'キズ．ダコン';
            break;
        case  '13' :
            $retVal = 'その他';
            break;
        default :
            $retVal = '';
            break;
    }
    
    return $retVal;
}
// --------------------------------------------------
// 作業時間の計算
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
// 指定された小数位で切り捨て
// --------------------------------------------------
function Mfloor ($num,$point) {
    
    // 端数処理の問題で一度文字列化
    $num = sprintf("%s",$num);
    $tmp = (int)($num * pow (10,$point));
    $tmp /= pow (10,$point);
    return $tmp;
    
}

?>
