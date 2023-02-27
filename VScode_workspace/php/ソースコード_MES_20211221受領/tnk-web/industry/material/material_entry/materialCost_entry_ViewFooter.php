<?php
//////////////////////////////////////////////////////////////////////////////
// ����������Ͽ materialCost_entry_ViewFooter.php                         //
// Copyright (C) 2008-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2007/05/23 Created   materialCost_entry_ViewFooter.php                   //
// 2007/06/19 �ե��������Υ����ߥ󥰤��ٱ䤵���Ʒ��ʤ����PC���б�          //
// 2007/06/21 JavaScript��backgroundColor������ѹ�,���硼�ȥ�����ɸ�ॿ����//
//            onLoad=set_focus() �� onLoad='set_focus();' HTML������� ���� //
//            $menu->out_retF2Script() �ɲ� ����                            //
// 2007/06/22 $uniq�Σ������������php����̵����{$uniq} ���� ����       //
// 2007/07/04 ������Ψ����Ͽ����ʤ��Զ�� ������Ψs_rate��name���� ��ë  //
// 2007/09/18 E_ALL | E_STRICT ���ѹ� ����                                  //
// 2007/09/19 elseif (substr($plan_no, 0, 2) == 'ZZ') 25.60 ���ɲ� ����     //
// 2008/02/14 if (substr($assy_no, 0, 1) == 'C') 25.6 else 37.0 ���ɲ�      //
// 2008/11/11 ��Ψ�ѹ��ˤ����ڲ����ѹ��ΰ���Ψ��                          //
//            ���ץ�=57.00 ��˥�=44.00 ���ѹ�                              //
// 2008/11/14 ��Ψ�ѹ���������Ͽ���褦�Ȥ�����������ڤˤʤ�褦������      //
// 2011/03/04 11/04/01�ʹߤϡ����ץ�57��45����˥�44��53���ѹ�              //
// 2015/05/21 �������ʤ����������Ͽ���б�                                  //
// 2020/02/21 ��ư��Ͽ���ˡ�������Ψ������ʤ��٤��б� ����                 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 21);                    // site_index=30(������˥塼) site_id=21(����������Ͽ)

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� Ͽ (��������)');
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��
//////////// ��ʬ��ե졼��������Ѥ���
$menu->set_self(INDUST . 'material/material_entry/materialCost_entry_main.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�����������Ͽ',   INDUST . 'material/materialCost_entry_old.php');
//////////// ����ؤ�GET�ǡ�������
$menu->set_retGET('page_keep', 'On');

$request = new Request;
$session = new Session;
//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// ���ǤιԿ�
define('PAGE', '300');

//////////// ��å��������ϥե饰
$msg_flg = 'site';

//////////// ���顼���ν�����
$error_log_name = '/tmp/materialCost_entry_error.log';

//////////// �ײ��ֹ桦�����ֹ�����
$plan_no = $session->get('plan_no');
$assy_no = $session->get('assy_no');

//////////// �������κǿ���Ͽ������������ֹ����
if (substr($plan_no, 0, 2) == 'ZZ') $menu->set_retGET('assy', $assy_no);

/******** ����ɸ��ι����ɲ� *********/
$sql2 = "
    SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$plan_no}'
";
$sc = '';
getUniResult($sql2, $sc);
if ($sc == 'SC') {
    $plan = '����';
} else {
    $plan = 'ɸ��';
}
//////////// �졼�Ȥ�ײ��ֹ椫�����(�����ޥ����������ѹ�ͽ��)
if (substr($plan_no, 0, 1) == 'C') {
    /******** ����ɸ��ι����ɲ� *********/
    $sql2 = "
        SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $sc = '';
    getUniResult($sql2, $sc);
    if ($sc == 'SC') {
        define('RATE', 25.60);  // ���ץ�����
    } else {
        $sql2 = "
            SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $kan = '';
        getUniResult($sql2, $kan);
        if ($kan < 20071001) {
            define('RATE', 25.60);  // ���ץ�ɸ�� 2007/10/01���ʲ������
        } elseif ($kan < 20110401) {
            define('RATE', 57.00);  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
        } else {
            define('RATE', 45.00);  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
        }
    }
} elseif (substr($plan_no, 0, 2) == 'ZZ') {
    if (substr($assy_no, 0, 1) == 'C') {
        if ($kan < 20110401) {
            define('RATE', 57.00);  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
        } else {
            define('RATE', 45.00);  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
        }
    } elseif (substr($assy_no, 0, 1) == 'L') {
        if ($kan < 20110401) {
            define('RATE', 44.00);  // ��˥� 2007/10/01���ʲ���ʹ�
        } else {
            define('RATE', 53.00);  // ��˥� 2011/04/01���ʲ���ʹ�
        }
    } else {
        define('RATE', 50.00);  // �ġ���
    }
} elseif (substr($plan_no, 0, 1) == 'L') {
    $sql2 = "
        SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $kan = '';
    getUniResult($sql2, $kan);
    if ($kan < 20081001) {
        define('RATE', 37.00);  // ��˥� 2008/10/01���ʲ������
    } elseif ($kan < 20110401) {
        define('RATE', 44.00);  // ��˥� 2008/10/01���ʲ���ʹ�
    } else {
        define('RATE', 53.00);  // ��˥� 2011/04/01���ʲ���ʹ�
    }
} else {
    $sql2 = "
        SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $kan = '';
    getUniResult($sql2, $kan);
    define('RATE', 50.00);  // �ġ���
}

//////////// SQL ʸ�� WHERE ��� ���Ѥ���
$search = sprintf("WHERE plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// ��ץ쥳���ɿ����������μ���     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("SELECT count(*), sum(Uround(pro_price * pro_num, 2)) FROM material_cost_history %s", $search);
$res_sum = array();
if ( getResult2($query, $res_sum) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
}
$maxrows = $res_sum[0][0];
$sum_kin = $res_sum[0][1];

$query = sprintf("SELECT sum(Uround(pro_num * pro_price, 2)) FROM material_cost_history
                    %s and intext='0'", $search);
if ( getUniResult($query, $ext_kin) <= 0) {  // �����������
    $_SESSION['s_sysmsg'] .= "�����������μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
}
$query = sprintf("SELECT sum(Uround(pro_num * pro_price, 2)) FROM material_cost_history
                    %s and intext='1'", $search);
if ( getUniResult($query, $int_kin) <= 0) {  // ������������
    $_SESSION['s_sysmsg'] .= "����������μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
}

//////////// �ײ��ֹ�ñ�̤ι������٤κ�ɽ
$query = sprintf("
        SELECT
            mate.last_user  AS \"Level\",                   -- 0
            parts_no        as �����ֹ�,                    -- 1
            midsc           as ����̾,                      -- 2
            pro_num         as ���ѿ�,                      -- 3
            pro_no          as ����,                        -- 4
            pro_mark        as ����̾,                      -- 5
            pro_price       as ����ñ��,                    -- 6
            Uround(pro_num * pro_price, 2)
                            as �������,                    -- 7
            CASE
                WHEN intext = '0' THEN '����'
                WHEN intext = '1' THEN '���'
                ELSE intext
            END             as �⳰��,                      -- 8
            par_parts       as ���ֹ�                       -- 9
        FROM
            -- material_cost_history
            material_cost_level_as('{$plan_no}') AS mate
        LEFT OUTER JOIN
             miitem ON parts_no=mipn
        -- %s 
        -- ORDER BY par_parts ASC, parts_no ASC, pro_no ASC
        
    ", $search);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'><br>����̤��Ͽ�Ǥ���</font>";
    // header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    // exit();
    $num = count($field);       // �ե�����ɿ�����
    $final_flg = 0;             // ��λ�ե饰 0=NG
} else {
    $num = count($field);       // �ե�����ɿ�����
    $final_flg = 1;             // ��λ�ե饰 1=OK
    $query = "SELECT parts_no FROM material_cost_level_as('{$plan_no}')";
    $chk_rows = getResult2($query, $res_chk);
    if ($chk_rows != $maxrows) {
        $_SESSION['s_sysmsg'] .= "��٥�ɽ����{$chk_rows} �ȼ¥ǡ�����{$maxrows} �Υ쥳���ɿ������פ��Ƥ��ޤ��󡪡�ľ�����ϥ�˥塼����Ѥ��Ʋ�������";    // .= �����
        $msg_flg = 'alert';
        $old_menu = 'on';
        $_GET['page_keep'] = '1';   // ���顼�ξ��ϥڡ�����ݻ����뤿�� page_keep�����
    }
}

////////////// ���ԡ��Υ�󥯤������줿��  &&���ɲ� Undefined index�б�
if ($request->get('number') != '' && $res[$request->get('number')][0] != '') {
    $c_number = $request->get('number');
    $parts_no  = $res[$c_number][1];
    $pro_num   = $res[$c_number][3];
    $pro_no    = $res[$c_number][4];
    $pro_mark  = $res[$c_number][5];
    $pro_price = $res[$c_number][6];
    $par_parts = $res[$c_number][9];
    if ($res[$c_number][8] == '����') $intext = '0'; else $intext = '1';
} else {
    $c_number  = '';
    $parts_no  = '';
    $pro_num   = '';
    $pro_no    = '';
    $pro_mark  = '';
    $pro_price = '';
    $par_parts = '';
    $intext    = '0';
}

/////////// ��Ω��μ���
$query = "SELECT m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate
            FROM material_cost_header WHERE plan_no='{$plan_no}'";
$res_time = array();
if ( getResult2($query, $res_time) > 0 ) {
    $m_time = $res_time[0][0];
    $m_rate = $res_time[0][1];
    $a_time = $res_time[0][2];
    $a_rate = $res_time[0][3];
    $g_time = $res_time[0][4];    
    $g_rate = $res_time[0][5];
    ///// ��� ��Ω��(������)
    $assy_int_price = ( (Uround($m_time * $m_rate, 2)) + 
                        (Uround($a_time * $a_rate, 2)) + 
                        (Uround($g_time * $g_rate, 2)) );
    ///// �����칩�� ������Ψ����Ω��
    $assy_time  = $res_time[0][6];
//    $assy_rate  = $res_time[0][7];
    /* ��ư��Ͽ���ˡ�������Ψ������ʤ��٤��б� -----------------> */
    // ��������$assy_rate���ͤ����ξ�硢RATE���ͤ��ѹ�����
    if( $res_time[0][7] == 0 ) {
        $assy_rate  = RATE;
        $query = sprintf("UPDATE material_cost_header SET
                        plan_no='{$plan_no}', assy_rate=%01.2f
                        WHERE plan_no='{$plan_no}'", $assy_rate );
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "�ײ��ֹ桧{$plan_no} �η�����Ψ�ѹ��˼��ԡ�";   // .= �����
            $msg_flg = 'alert';
        }
    } else {
        $assy_rate  = $res_time[0][7];
    }
    /* <---------------------------------------------------------- */
    $assy_price = Uround($assy_time * $assy_rate, 2);
} else {
    $m_time = 0;
    $m_rate = 0;
    $a_time = 0;
    $a_rate = 0;
    $g_time = 0;
    $g_rate = 0;
    $assy_int_price = 0;
    $assy_time  = 0;
    $assy_rate  = RATE;
    $assy_price = 0;
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

function chk_cost_entry(obj) {
    obj.parts_no.style.backgroundColor  = '';
    obj.pro_num.style.backgroundColor   = '';
    obj.pro_no.style.backgroundColor    = '';
    obj.pro_mark.style.backgroundColor  = '';
    obj.pro_price.style.backgroundColor = '';
    obj.intext.style.backgroundColor    = '';
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    obj.par_parts.value = obj.par_parts.value.toUpperCase();
    if (obj.parts_no.value.length != 0) {
        if (obj.parts_no.value.length != 9) {
            alert('�����ֹ�η���ϣ���Ǥ���');
            obj.parts_no.focus();
            // obj.parts_no.select();
            obj.parts_no.style.backgroundColor='#ff99cc';
            return false;
        }
    } else {
        alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        obj.parts_no.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.pro_num.value)) ) {
        alert('���ѿ��Ͽ����ʳ����Ͻ���ޤ���');
        obj.pro_num.focus();
        obj.pro_num.select();
        obj.pro_num.style.backgroundColor='#ff99cc';
        return false;
    } else {
        if (obj.pro_num.value <= 0) {
            alert('���ѿ��ϣ�����礭�����������Ϥ��Ʋ�������');
            obj.pro_num.focus();
            obj.pro_num.select();
            obj.pro_num.style.backgroundColor='#ff99cc';
            return false;
        }
        if (obj.pro_num.value > 999.9999) {
            alert('���ѿ��� 0.0001��999.9999 �ޤǤ����Ϥ��Ʋ�������');
            obj.pro_num.focus();
            obj.pro_num.select();
            obj.pro_num.style.backgroundColor='#ff99cc';
            return false;
        }
    }
    if ( !(isDigit(obj.pro_no.value)) ) {
        alert('�����ֹ�Ͽ����ʳ����Ͻ���ޤ���');
        obj.pro_no.focus();
        obj.pro_no.select();
        obj.pro_no.style.backgroundColor='#ff99cc';
        return false;
    } else {
        if (obj.pro_no.value <= 0) {
            alert('�����ֹ�ϣ�����Ϥޤ�ޤ���');
            obj.pro_no.focus();
            obj.pro_no.select();
            obj.pro_no.style.backgroundColor='#ff99cc';
            return false;
        }
    }
    obj.pro_mark.value = obj.pro_mark.value.toUpperCase();
    if (obj.pro_mark.value.length != 0) {
        /*****      ///// ��������˿��������뤿�ᥳ����
        if ( !(isABC(obj.pro_mark.value)) ) {
            alert('��������ϥ���ե��٥åȤǤ���');
            obj.pro_mark.focus();
            obj.pro_mark.select();
            obj.pro_mark.style.backgroundColor='#ff99cc';
            return false;
        }
        *****/
    } else {
        alert('�������椬���Ϥ���Ƥ��ޤ���');
        obj.pro_mark.focus();
        obj.pro_mark.select();
        obj.pro_mark.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.pro_price.value)) ) {
        alert('����ñ���Ͽ����ʳ����Ͻ���ޤ���');
        obj.pro_price.focus();
        obj.pro_price.select();
        obj.pro_price.style.backgroundColor='#ff99cc';
        return false;
    } else if (obj.pro_price.value > 9999999.99 || obj.pro_price.value < 0) {
        alert('����ñ���� 0��9999999.99 �ޤǤ����Ϥ��Ʋ�������');
        obj.pro_price.focus();
        obj.pro_price.select();
        obj.pro_price.style.backgroundColor='#ff99cc';
        return false;
    }
    if (!( (obj.intext.value == '0') || (obj.intext.value == '1') )) {
        alert('����=0 ���=1 �Τɤ��餫�����Ϥ��Ʋ�������');
        obj.intext.focus();
        obj.intext.select();
        obj.intext.style.backgroundColor='#ff99cc';
        return false;
    }
    return true;
}

function chk_assy_entry(obj) {
    obj.m_time.style.backgroundColor = '';
    obj.m_rate.style.backgroundColor = '';
    obj.a_time.style.backgroundColor = '';
    obj.a_rate.style.backgroundColor = '';
    obj.g_time.style.backgroundColor = '';
    obj.g_rate.style.backgroundColor = '';
    /* ���Υ����å��Υե饰 */
    var flg = false;
        /* ���Ƥι��ܤο������ϥ����å� */
    if ( !(isDigitDot(obj.m_time.value)) ) {
        alert('���� �����Ͽ����ʳ����Ͻ���ޤ���');
        obj.m_time.focus();
        obj.m_time.select();
        obj.m_time.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.m_rate.value)) ) {
        alert('���� ��Ψ�Ͽ����ʳ����Ͻ���ޤ���');
        obj.m_rate.focus();
        obj.m_rate.select();
        obj.m_rate.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.a_time.value)) ) {
        alert('��ư�� �����Ͽ����ʳ����Ͻ���ޤ���');
        obj.a_time.focus();
        obj.a_time.select();
        obj.a_time.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.a_rate.value)) ) {
        alert('��ư�� ��Ψ�Ͽ����ʳ����Ͻ���ޤ���');
        obj.a_rate.focus();
        obj.a_rate.select();
        obj.a_rate.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.g_time.value)) ) {
        alert('���� �����Ͽ����ʳ����Ͻ���ޤ���');
        obj.g_time.focus();
        obj.g_time.select();
        obj.g_time.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.g_rate.value)) ) {
        alert('���� ��Ψ�Ͽ����ʳ����Ͻ���ޤ���');
        obj.g_rate.focus();
        obj.g_rate.select();
        obj.g_rate.style.backgroundColor='#ff99cc';
        return false;
    }
        /* ���ȤΥڥ������ϥ����å� */
    if (obj.m_time.value > 0) {
        if (obj.m_rate.value > 0) {
            if (obj.m_time.value > 9999.999) {  // 2006/11/24 999.999��9999.999���ѹ�
                alert('���� ������ 0.001��9999.999 �ޤǤ����Ϥ��Ʋ�������');
                obj.m_time.focus();
                obj.m_time.select();
                obj.m_time.style.backgroundColor='#ff99cc';
                return false;
            }
            if (obj.m_rate.value > 999.99) {
                alert('���� ��Ψ�� 0.01��999.99 �ޤǤ����Ϥ��Ʋ�������');
                obj.m_rate.focus();
                obj.m_rate.select();
                obj.m_rate.style.backgroundColor='#ff99cc';
                return false;
            }
            flg = true;
        } else {
            alert("���� ���������Ϥ���Ƥ���Τ�\n���� ��Ψ�����Ϥ���Ƥ��ޤ���");
            obj.m_rate.focus();
            obj.m_rate.select();
            obj.m_rate.style.backgroundColor='#ff99cc';
            return false;
        }
    } else {
        if (obj.m_rate.value > 0) {
            alert("���� ��Ψ�����Ϥ���Ƥ���Τ�\n���� ���������Ϥ���Ƥ��ޤ���");
            obj.m_time.focus();
            obj.m_time.select();
            obj.m_time.style.backgroundColor='#ff99cc';
            return false;
        }
    }
        /* ��ư���Υڥ������ϥ����å� */
    if (obj.a_time.value > 0) {
        if (obj.a_rate.value > 0) {
            if (obj.a_time.value > 999.999) {
                alert('��ư�� ������ 0.001��999.999 �ޤǤ����Ϥ��Ʋ�������');
                obj.a_time.focus();
                obj.a_time.select();
                obj.a_time.style.backgroundColor='#ff99cc';
                return false;
            }
            if (obj.a_rate.value > 999.99) {
                alert('��ư�� ��Ψ�� 0.01��999.99 �ޤǤ����Ϥ��Ʋ�������');
                obj.a_rate.focus();
                obj.a_rate.select();
                obj.a_rate.style.backgroundColor='#ff99cc';
                return false;
            }
            flg = true;
        } else {
            alert("��ư�� ���������Ϥ���Ƥ���Τ�\n���� ��Ψ�����Ϥ���Ƥ��ޤ���");
            obj.a_rate.focus();
            obj.a_rate.select();
            obj.a_rate.style.backgroundColor='#ff99cc';
            return false;
        }
    } else {
        if (obj.a_rate.value > 0) {
            alert("��ư�� ��Ψ�����Ϥ���Ƥ���Τ�\n���� ���������Ϥ���Ƥ��ޤ���");
            obj.a_time.focus();
            obj.a_time.select();
            obj.a_time.style.backgroundColor='#ff99cc';
            return false;
        }
    }
        /* ����Υڥ������ϥ����å� */
    if (obj.g_time.value > 0) {
        if (obj.g_rate.value > 0) {
            if (obj.g_time.value > 999.999) {
                alert('���� ������ 0.001��999.999 �ޤǤ����Ϥ��Ʋ�������');
                obj.g_time.focus();
                obj.g_time.select();
                obj.g_time.style.backgroundColor='#ff99cc';
                return false;
            }
            if (obj.g_rate.value > 999.99) {
                alert('���� ��Ψ�� 0.01��999.99 �ޤǤ����Ϥ��Ʋ�������');
                obj.g_rate.focus();
                obj.g_rate.select();
                obj.g_rate.style.backgroundColor='#ff99cc';
                return false;
            }
            flg = true;
        } else {
            alert("���� ���������Ϥ���Ƥ���Τ�\n���� ��Ψ�����Ϥ���Ƥ��ޤ���");
            obj.g_rate.focus();
            obj.g_rate.select();
            obj.g_rate.style.backgroundColor='#ff99cc';
            return false;
        }
    } else {
        if (obj.g_rate.value > 0) {
            alert("���� ��Ψ�����Ϥ���Ƥ���Τ�\n���� ���������Ϥ���Ƥ��ޤ���");
            obj.g_time.focus();
            obj.g_time.select();
            obj.g_time.style.backgroundColor='#ff99cc';
            return false;
        }
    }
        /* ���ΤΥե饰�����ϥ����å� */
    if (!flg) {
        alert('���ȡ���ư��������Τɤ줫�����åȰʾ塢���Ϥ��Ʋ�������');
        obj.m_time.focus();
        obj.m_time.select();
        obj.m_time.style.backgroundColor='#ff99cc';
        return false;
    } else {
        return true;
    }
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    window.setTimeout("document.entry_form.parts_no.focus()", 300);    // ������ϥե����ब������ϥ����Ȥ򳰤�
    window.setTimeout("document.entry_form.parts_no.select()", 300);
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
body {
    background-image:   none;
    overflow-x:         hidden;
    overflow-y:         hidden;
}
.pt9 {
    font:normal     9pt;
    font-family:    monospace;
}
.pt10 {
    font:normal     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.caption_font {
    font-size:      11pt;
    color:          blue;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-wieght:        bold;
    font-family:        monospace;
}
.parts_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     left;
}
.pro_num_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     center;
}
.price_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     right;
}
.entry_font {
    font-size:      11pt;
    font-weight:    normal;
    color:          red;
}
a:hover {
    background-color: gold;
}
a:active {
    background-color: yellow;
}
a {
    font-size:   10pt;
    font-weight: bold;
    color:       blue;
}
-->
</style>
</head>
<body onLoad='set_focus();'>
    <center>
       <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                    <!--  bgcolor='#ffffc6' �������� -->
                    <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
            <tr>
                <td class='winbox' nowrap colspan='<?php echo $num+1 ?>' align='right'>
                    <div class='pt10'>
                    ��������<?php echo number_format($int_kin, 2) ."\n" ?>
                    ���������<?php echo number_format($ext_kin, 2) ."\n" ?>  
                    ��׺�����<?php echo number_format($sum_kin, 2) ."\n" ?>
                    <br>
                    ��׹�����<?php echo number_format($assy_time, 3) ."\n" ?>
                    ������Ψ��<?php echo number_format($assy_rate, 2) ."\n" ?>
                    ������Ω��<?php echo number_format($assy_price, 2) ."\n" ?>
                    ���������<?php echo number_format($sum_kin + $assy_price, 2) ."\n" ?>
                    <br>
                    (���͡�����μº���Ψ)
                    ��Ω��<?php echo number_format($assy_int_price, 2) ."\n" ?>
                    ���������<?php echo number_format($sum_kin + $assy_int_price, 2) ."\n" ?>
                    </div>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <form name='entry_form' method='post' action='materialCost_entry_main.php' target='application' onSubmit='return chk_cost_entry(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>�����ֹ�</th>
                    <th class='winbox' nowrap>���ѿ�</th>
                    <th class='winbox' nowrap>�����ֹ�</th>
                    <th class='winbox' nowrap>����̾</th>
                    <th class='winbox' nowrap>����ñ��</th>
                    <th class='winbox' nowrap>0����/1���</th>
                    <th class='winbox' nowrap>�������ֹ�</th>
                </tr>
                <tr>
                    <a name='entry_point'>
                        <td class='winbox' align='center'>
                            <input type='text' tabindex='1' class='parts_font' name='parts_no' value='<?php echo $parts_no ?>' size='9' maxlength='9' style='ime-mode: disabled;' onKeyUp='baseJS.keyInUpper(this);'>
                        </td>
                    </a>
                    <td class='winbox' align='center'><input type='text' tabindex='2' class='price_font' name='pro_num' value='<?php echo $pro_num ?>' size='7' maxlength='8' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='3' class='pro_num_font' name='pro_no' value='<?php echo $pro_no ?>' size='1' maxlength='1' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='4' class='pro_num_font' name='pro_mark' value='<?php echo $pro_mark ?>' size='2' maxlength='2' style='ime-mode: disabled;' onKeyUp='baseJS.keyInUpper(this);'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='5' class='price_font' name='pro_price' value='<?php echo $pro_price ?>' size='10' maxlength='10' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='6' class='pro_num_font' name='intext' value='<?php echo $intext ?>' size='1' maxlength='1' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='7' class='parts_font' name='par_parts' value='<?php echo $par_parts ?>' size='9' maxlength='9' style='ime-mode: disabled;' onKeyUp='baseJS.keyInUpper(this);'></td>
                </tr>
                <tr>
                    <td class='winbox' colspan='7' align='center'>
                        <input type='submit' tabindex='8' class='entry_font' name='entry' value='�ɲ��ѹ�'>
                        <input type='submit' tabindex='9' class='entry_font' name='del' value='���'>
                        <input type='hidden' name='c_number' value='<?php echo $c_number ?>'>
                        <?php 
                        if ($rows == 0) {
                            echo "<a href='". H_WEB_HOST . $menu->out_self() ."?pre_copy=1' target='application' style='text-decoration:none;' tabindex='10'>
                                    ����Υǡ����򥳥ԡ�
                                  </a>";
                        }
                        if (isset($old_menu)) {
                            echo "<a href='". $menu->out_action('�����������Ͽ') ."' target='_parent' style='text-decoration:none;'>
                                    ľ�����ϥ�˥塼��
                                  </a>";
                        }
                        ?>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        
        <form name='assy_form' method='post' action='materialCost_entry_main.php' target='_parent' onSubmit='return chk_assy_entry(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>���ȹ���</th>
                    <th class='winbox' nowrap>������Ψ</th>
                    <th class='winbox' nowrap>��ư������</th>
                    <th class='winbox' nowrap>��ư����Ψ</th>
                    <th class='winbox' nowrap>������</th>
                    <th class='winbox' nowrap>������Ψ</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'><input type='text' tabindex='10' class='price_font' name='m_time' value='<?php echo $m_time ?>' size='7' maxlength='8' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='11' class='price_font' name='m_rate' value='<?php echo $m_rate ?>' size='5' maxlength='6' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='12' class='price_font' name='a_time' value='<?php echo $a_time ?>' size='6' maxlength='7' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='13' class='price_font' name='a_rate' value='<?php echo $a_rate ?>' size='5' maxlength='6' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='14' class='price_font' name='g_time' value='<?php echo $g_time ?>' size='6' maxlength='7' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='15' class='price_font' name='g_rate' value='<?php echo $g_rate ?>' size='5' maxlength='6' style='ime-mode: disabled;'></td>
                </tr>
                <tr>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit'  tabindex='16'class='entry_font' name='assy_reg' value='�ɲ��ѹ�'>
                        <input type='hidden' name='s_rate' value='<?php echo RATE ?>'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        
        <form name='final_form' method='post' action='materialCost_entry_main.php?id=<?php echo $uniq ?>' target='_parent'>
            <?php if ($final_flg == 1) { ?>
            <input type='submit' tabindex='17' class='entry_font' name='final' value='��λ'>
            <input type='submit' tabindex='18' class='entry_font' name='all_del' value='�����'
                onClick="return confirm('�������¹Ԥ��ޤ���\n\n���ν����ϸ��ˤ��᤻�ޤ���\n\n�¹Ԥ��Ƥ⵹�����Ǥ��礦����')"
            >
            <?php } ?>
        </form>
        <?php echo $menu->out_retF2Script() ?>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
