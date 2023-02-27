<?php
//////////////////////////////////////////////////////////////////////////////
// ���ץ�����δ����ʸ������ӽ� ����                                        //
// �ƥ�ץ졼�ȥ��󥸥��simplate, ���饤����Ȱ�����PXDoc �����           //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/06 Created  inspectionPrint.php                                  //
// 2007/12/07 ����̾�������ֹ����γ�ǧ���̤���Ϥ���褦���ɲ�              //
// 2007/12/10 �ۥ󥿥�������������Υǡ��������� getMaterial()���ɲ�      //
// 2007/12/18 template�����1���ѹ��� ����̾�ȥ桼����̾��ʸ���������䤷��  //
// 2007/12/20 template�����2���ѹ��� ���������ͼ��ֹ�����������        //
// 2007/12/25 ɸ���ʤ��б���SC���֤�SC�����äƤ��ʤ�����--------�������  //
// 2007/12/26 �嵭�Υ����å��򹹤�ctype_alnum()���ѹ� -> SC�����ʎ� ���б�      //
// 2007/12/28 ���κ���ȥ����������Ǥ���褦�˵�ǽ�ɲä������������    //
//            �Ĥ�������ΰ������˺ǽ�����Υǡ�������Ѥ��롣����������ɲ�//
// 2007/12/29 �������¸�˷ײ��ֹ���ɲ� $result->get('prePlanNo')          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_cache_limiter('public');            // PXDoc����Ѥ�����Τ��ޤ��ʤ�(���ϥե�����򥭥�å��夵����)
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
// access_log();                               // Script Name �ϼ�ư����
define('START_TIME', microtime(true));

//////////// �ꥯ�����ȤΥ��󥹥��󥹤����
$request = new Request();
//////////// �ꥶ��ȤΥ��󥹥��󥹤����
$result = new Result();
///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ץ����� �����ʸ������ӽ�ΰ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('svgUpload', TEST . '/pxd/svgUpload.php');

main($request, $menu, $result);

function main($request, $menu, $result)
{
    switch( $request->get('showMenu') ){
    case 'preView':
        if (!printPXDoc($request, 1, $result)) inputForm($menu, $request, $result);
        break;
    case 'execPrint':
        if (!printPXDoc($request, 2, $result)) inputForm($menu, $request, $result);
        break;
    case 'inputForm':
    default:
        inputForm($menu, $request, $result);
    }
}
ob_end_flush();                 // ���ϥХåե���gzip���� END


function printPXDoc($request, $flg=0, $result)
{
    if ($flg == 0) return;
    $baseName = basename($_SERVER['SCRIPT_NAME'], '.php');
    
    // if(!extension_loaded('simplate')) { dl('simplate.so'); }
    $smarty = new simplate();
    
    $header  = '<?xml version="1.0" encoding="EUC-JP"?>' . "\n";
    $header .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n";
    
    if ($flg == 2) {
        $strPXD = "<pxd name='��ư����' title='���ץ����� �����ʸ������ӽ�ΰ���' paper-type='A4' paper-name='A4-���åȻ�' orientation='portrait' delete='yes' save='no' print='yes'>\n";
    } else {
        $strPXD = "<pxd name='�ץ�ӥ塼' title='���ץ����� �����ʸ������ӽ�ΰ����ץ�ӥ塼' paper-type='A4' paper-name='A4-���åȻ�' orientation='portrait' delete='yes' save='no' print='yes' tool-fullscreen='no'>\n";
    }
    $endPXD = "</pxd>\n";
    
    if (!getPlanData($request, $result)) {
        return false;
    }
    ////////// �ꥯ�����Ȥ����κ���ȥ��������ѹ������å�������
    setMaterial($request, $result);
    
    $smarty->assign('planNo', $request->get('targetPlanNo'));
    $smarty->assign('partsNo', $result->get('assyNo'));
    $smarty->assign('partsName', $result->get('assyName'));
    $smarty->assign('plan', $result->get('plan'));
    $smarty->assign('scNo', $result->get('scNo'));
    $smarty->assign('material', $result->get('material'));
    $smarty->assign('material2', $result->get('material2'));
    $smarty->assign('userName', $result->get('userName'));
    // $end_time = sprintf("%01.05f",microtime(true)-START_TIME);
    // $smarty->assign('cdNo', "�������֡�$end_time ��");
    $smarty->assign('cdNo', $result->get('cdNo'));
    
    $output  = $header;
    $output .= $strPXD;
    $output .= "<page>\n";
    $output .= "<chapter name='���ڡ���' id='1' parent='' />\n";
    // $output .= $smarty->fetch('�����ץ鴰���ʸ������ӽ�.tpl');
    $output .= $smarty->fetch('�����ץ鴰���ʸ������ӽ�-����3.tpl');
    $output .= "</page>\n";
    $output .= $endPXD;
    /******************* �ǥХå��� **************************/
    if ($request->get('DEBUG') == 'yes') {
        $fp = fopen("{$baseName}-debug.txt", 'w');
        fwrite($fp, $output);
        fclose($fp);
        chmod("{$baseName}-debug.txt", 0666);
    }
    /*********************************************************/
    
    header('Content-type: application/pxd;');
    header("Content-Disposition:inline;filename=\"{$baseName}.pxd\"");
    echo $output;
    ///// ����������¸
    setPrintHistory($request, $result);
    return true;
}

function inputForm($menu, $request, $result)
{
    ////////// �֥饦�����Υ���å����к���
    $uniq = $menu->set_useNotCache('target');
    ////////// HTML Header ����Ϥ��ƥ���å��������
    $menu->out_html_header();
    ////////// �ꥯ�����Ȥ�����Хǡ��������
    if ($request->get('targetPlanNo') != '') getPlanData($request, $result);
    ////////// �ꥯ�����Ȥ����κ���ȥ��������ѹ������å�������
    setMaterial($request, $result);
    ////////// ���ϥե������ɽ��
    require_once ('inputForm.php');
}

function getPlanData($request, $result)
{
    $query = "
        SELECT
            parts_no                        -- 0
            ,
            plan - cut_plan                 -- 1
            ,
            substr(note15, 1, 8)            -- 2 �����ֹ�ϣ���
            ,
            sche.user_name                  -- 3 ����ϸ��߻��ѤǤ��ʤ�
            ,
            substr(midsc, 1, 38)            -- 4 (��18)
            ,
            mzist                           -- 5
            ,
            substr(devuser.user_name, 1, 26)-- 6 (��17)
            ,
            dev_no                          -- 7
            ,
            midsc                           -- 8
        FROM
            assembly_schedule AS sche
        LEFT OUTER JOIN
            miitem ON (parts_no = mipn)
        LEFT OUTER JOIN
            assy_develop_user ON (parts_no = assy_no)
        LEFT OUTER JOIN
            assy_develop_user_code AS devuser USING (user_no)
        WHERE
            plan_no = '{$request->get('targetPlanNo')}'
    ";
    $res = array();
    if (getResult2($query, $res) < 1) {
        $_SESSION['s_sysmsg'] = '�ײ��ֹ椬����������ޤ���';
        return false;
    } else {
        $result->add('assyNo', $res[0][0]);
        $result->add('plan', $res[0][1]);
        if (ctype_alnum($res[0][2])) {
            $result->add('scNo', $res[0][2]);
        } else {
            $result->add('scNo', '--------');
        }
        // $result->add('userName', $res[0][3]);    // �ʲ���Ⱦ��26ʸ���ޤǤ��ѹ�
        $result->add('assyName', mb_substr(mb_convert_kana($res[0][4], 'k'), 0, 26) );
        $result->add('material', $res[0][5]);
        $result->add('material2', '');
        $result->add('userName', $res[0][6]);
        $result->add('cdNo', $res[0][7]);
        if ($res[0][5] == '') {
            $result->add('material', getMaterial($result, $res[0][8], 1));
        }
        $result->add('material2', getMaterial($result, $res[0][8], 2));
        return true;
    }
}
///// �����ƥ�ޥ�����������̾�������κ���ȥ����������(��«����ASSY�μ��˥��ڡ��������κ�����ڡ����ǥ�����)
function getMaterial($result, $data, $flg)
{
    ///// ���򤫤����
    if ($flg == 1) {
        $query = "
            SELECT material, regdate, plan_no FROM inspection_print_history WHERE assy_no = '{$result->get('assyNo')}'
            ORDER BY assy_no DESC, regdate DESC LIMIT 1
        ";
    } else {
        $query = "
            SELECT material2, regdate, plan_no FROM inspection_print_history WHERE assy_no = '{$result->get('assyNo')}'
            ORDER BY assy_no DESC, regdate DESC LIMIT 1
        ";
    }
    $res = array();
    if (getResult2($query, $res) > 0) {
        $result->add('prePrintDate', $res[0][1]);
        $result->add('prePlanNo', $res[0][2]);
        return $res[0][0];
    } else {
        $result->add('prePrintDate', '���');
    }
    
    ///// �ޥ������������
    $arrayData = explode(' ', $data);
    $count = count($arrayData);
    for ($i=0; $i<$count; $i++) {
        if ($arrayData[$i] == 'ASSY') {
            if (isset($arrayData[$i+$flg])) return $arrayData[$i+$flg];
            break;
        } elseif (substr($arrayData[$i], -4) == 'ASSY') {
            if (isset($arrayData[$i+$flg])) return $arrayData[$i+$flg];
            break;
        }
    }
    return '';
}
///// ���κ���ȥ������ν��������å��ڤ�����
function setMaterial($request, $result)
{
    if ($request->get('targetMaterial') != '') $result->add('material', $request->get('targetMaterial'));
    if ($request->get('targetMaterial2') != '') $result->add('material2', $request->get('targetMaterial2'));
}
///// �����������¸
function setPrintHistory($request, $result)
{
    $query = "
        INSERT INTO inspection_print_history (assy_no, material, material2, plan_no)
        VALUES ('{$result->get('assyNo')}', '{$result->get('material')}', '{$result->get('material2')}', '{$request->get('targetPlanNo')}')
    ";
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] = '�������¸�˼��Ԥ��ޤ����� ô���Ԥ�Ϣ���Ʋ�������';
    }
}
?>
