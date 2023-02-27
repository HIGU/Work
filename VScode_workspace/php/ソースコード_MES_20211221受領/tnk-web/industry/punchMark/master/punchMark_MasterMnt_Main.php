<?php
//////////////////////////////////////////////////////////////////////////////
// ������� ����ޥ����� ���ƥʥ� �ᥤ����                              //
// Copyright (C) 2007-2008 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/07/26 Created   punchMark_MasterMnt_Main.php                        //
// 2007/09/25 header('Location: $menu->RetUrl()') ��                        //
//            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl())  ���� //
// 2007/09/26 site_index �� INDEX_INDUST ���ѹ�  ����                       //
// 2007/10/02 ��������ɤν�ʣ�����å��򳰤���Ͽ�Ǥ���褦���ѹ�            //
// 2007/10/05 ��λ����ä�ܥ��󤫤��󥯤��ѹ�                            //
// 2007/10/18 getViewHTMLbody()��<body>����ȴ���ڤ�menu_form.css���ɲ�      //
//            ������Ƥ�$tmpView�����Ϥ��줿�Ȥ����ɽ�� E_ALL | E_STRICT�� //
//            ��������ɤȹ�����ƤǤΥ����ȵ�ǽ���ɲ�                 ���� //
// 2007/10/19 ê�ֽ��ɲ� �ǥ������ѹ�(�إå����򥤥�饤���)�ڤӸ����ɲ�   //
//            �����ܤ��оݤ˸�����ǽ���ɲ�                             ���� //
// 2007/10/20 ;ʬ��</tr>���� get_master()�����ʥޥ����������򥳥���OUT //
//            punchMark_del()�����ʥޥ������ȼ�ʬ�Υ����å����å��ѹ� ����//
// 2007/10/23 cellpadding='3' �� cellpadding='1' ���ѹ�(CSS���ѹ�)      ����//
// 2007/10/24 �ץ����κǸ�˲��Ԥ��ɲ�                                  //
// 2007/11/08 Ʊ����������ɤ���Ͽ���줿���ι�����Ƥ�Ʊ���������å��ؿ�  //
//            punchMarkSameCheck()���ɲá�ê�֤����ͤ��ѹ���)               //
//            �嵭�ؿ���SQLʸ�� LIMIT 1 ���ɲ�  ����                        //
// 2007/11/09 header('Location: ' . H_WEB_HOST . $menu->out_self());�������//
//            getPreDataRows()��setEditHistory()�Խ�������ɲá�        ����//
//            ���襳���ɤΥե����ޥåȤ� %d �� %s  %05d���ѻ�           ����//
//            INSERT INTOʸ�� punchMark_entryBody()������               ����//
// 2007/11/10 ;ʬ��<font color='yellow'></font>����                  ����//
//            putErrorLogWrite()��Ȥ�����SQL���顼��debug��Ԥ�        ����//
//            Mark���ɲä��ԥޡ������ȥ����פ򤹤�                    ����//
// 2007/11/16 ��������ɤ�Ʊ�����Ϲ�������ɤ�ɽ�����ʤ����ɲ�        ����//
// 2008/09/03 �ߤ��Ф���ξ����������ɽ������褦���ѹ�                  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
//ini_set('error_reporting', E_STRICT);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');     // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
require_once ('punchMark_MasterFunction.php');      // ������������ƥඦ�̥ޥ������ؿ�
access_log();                               // Script Name �ϼ�ư����

main();

function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 999);         // site_index=������˥塼 site_id=999(�����ȥ�˥塼�򳫤�)
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('������� ����ޥ����� ���ƥʥ�');
      
    $request = new Request;
    $result  = new Result;
    
    get_master($request, $result, $menu);       // �����ǧ�ѤΥޥ������ǡ�������
    
    request_check($request, $result);           // ������ʬ��
    
    get_data($request, $result);                // ����ޥ������Υǡ�������
    
    outViewListHTML($request, $menu, $result);  // View��HTML�ν���
    
    display($menu, $request, $result);          // ����ɽ��
}

// ����ɽ��
function display($menu, $request, $result)
{       
    //////////// �֥饦�����Υ���å����к���
    $uniq = 'id=' . $menu->set_useNotCache('target');

    ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
    
    /////////// HTML Header ����Ϥ��ƥ���å��������
    $menu->out_html_header();
 
    /////////// View�ν���
    require_once ('punchMark_MasterMnt_View.php');

    ob_end_flush(); 
}

function request_check($request, $result)    //������ʬ����Ԥ�
{
    $ok = true;
    if ($request->get('entry') != '')  $ok = punchMark_entry($request);
    if ($request->get('change') != '') $ok = punchMark_change($request);
    if ($request->get('del') != '')    $ok = punchMark_del($request, $result);
    if ($request->get('finish') != '') $ok = punchMark_finish($request);
    if ($request->get('cancel') != '') $ok = punchMark_cancel($request, $result);
    if ($ok) {
        // �ޡ������Ѥ˥��å�
        $result->add('punchMark_code', $request->get('punchMark_code'));
        $result->add('shelf_no', $request->get('shelf_no'));
    }
    if ($request->get('search') != '') {
        $result->add('where', getSearchCondition($request));
    } elseif ($ok) {
        $request->add('punchMark_code', '');
        $request->add('shelf_no', '');
        $request->add('mark', '');
        $request->add('shape_code', '');
        $request->add('size_code', '');
        $request->add('user_code', '');
        $request->add('note', '');
        $request->add('make_flg', '');
    }
    if ($request->get('number') != '') pre_copy($request, $result);
}

////////////// ��Ͽ���å�
function punchMark_entry($request)
{
    $punchMark_code = $request->get('punchMark_code');
    $shelf_no = $request->get('shelf_no');
    $mark = $request->get('mark');
    $shape_code = $request->get('shape_code');
    $size_code = $request->get('size_code');
    $user_code = $request->get('user_code');
    $note      = $request->get('note');
    $query = sprintf("SELECT * FROM punchMark_master WHERE shelf_no='%s'", $shelf_no);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� ���顼
        $_SESSION['s_sysmsg'] .= "ê�֡�{$shelf_no}�Ϥ��Ǥ˻��Ѥ���Ƥ��ޤ���";    // .= �����
        return false;
    }
    if (!punchMarkSameCheck($request)) {    /////// Ʊ����������ɤ���Ͽ������ι�����Ƥ�Ʊ���������å���ê�֤����Ͱʳ���Ʊ������)
        $_SESSION['s_sysmsg'] .= "��������Ƥ��ѹ�����Ƥ��ޤ���Ʊ����������ɤ���Ͽ������ϡ�ê�֤����Ͱʳ��ѹ����ʤ��ǲ�������";    // .= �����
        return false;
    }
    $query = sprintf("SELECT punchMark_code FROM punchMark_master WHERE punchMark_code='%s' AND shelf_no='%s'", $punchMark_code, $shelf_no);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� ���顼
        $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code} ê�֡�{$shelf_no}�Ϥ��Ǥ���Ͽ����Ƥ��ޤ���";    // .= �����
        return false;
    } else {                                    //////// ��Ͽ�ʤ� INSERT ����
        if (!punchMark_entryBody($request)) {
            return false;
        }
    }
    return true;
}

////////////// ��Ͽ ���� ���å�
function punchMark_entryBody($request)
{
    $punchMark_code = $request->get('punchMark_code');
    $query  = "INSERT INTO punchMark_master ";
    $query .= "(punchMark_code, shelf_no, mark, shape_code, size_code, user_code, note, make_flg, lend_flg, last_date, last_user) ";
    $query .= "VALUES ('{$punchMark_code}', '{$request->get('shelf_no')}', '{$request->get('mark')}', {$request->get('shape_code')}, ";
    $query .= "{$request->get('size_code')}, '{$request->get('user_code')}', '{$request->get('note')}', TRUE, FALSE,  CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}')";
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}���ɲä˼��ԡ�";    // .= �����
        putErrorLogWrite($query);
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}���ɲä��ޤ�����";
        // �Խ�������¸
        setEditHistory('punchMark_master', 'I', $query);
    }
    return true;
}

////////////// �ѹ����å�
function punchMark_change($request)
{
    $punchMark_code = $request->get('punchMark_code');
    $shelf_no = $request->get('shelf_no');
    $mark = $request->get('mark');
    $shape_code = $request->get('shape_code');
    $size_code = $request->get('size_code');
    $user_code = $request->get('user_code');
    $note      = $request->get('note');
    if (!punchMarkSameCheck($request, 'change')) {    // Ʊ����������ɤ���Ͽ������ι�����Ƥ�Ʊ���������å���ê�֤����Ͱʳ���Ʊ������)
        $_SESSION['s_sysmsg'] .= "��������Ƥ��ѹ�����Ƥ��ޤ���Ʊ����������ɤ���Ͽ������ϡ�ê�֤����Ͱʳ��ѹ����ʤ��ǲ�������";    // .= �����
        return false;
    }
    $query = sprintf("SELECT * FROM punchMark_master WHERE punchMark_code='%s' AND shelf_no='%s'", $punchMark_code, $shelf_no);
    $old_data = getPreDataRows($query);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE ����
        $query = sprintf("UPDATE punchMark_master SET punchMark_code='%s', shelf_no='%s', mark='%s', shape_code=%d, size_code=%d, user_code='%s', note='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE punchMark_code='%s' and shelf_no='%s'", $punchMark_code, $shelf_no, $mark, $shape_code, $size_code, $user_code, $note, $_SESSION['User_ID'], $punchMark_code, $shelf_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}���ѹ����ԡ�";    // .= �����
            putErrorLogWrite($query);
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}���ѹ����ޤ�����";
            // �Խ�������¸
            setEditHistory('punchMark_master', 'U', $query, $old_data);
            return true;
        }
    } else {                                    //////// ��Ͽ�ʤ� INSERT ����
        if (!punchMark_entryBody($request)) {
            return false;
        }
    }
    return false;
}

//////////// ����ܥ��󤬲����줿��
function punchMark_del($request, $result)
{
    $punchMark_code = $request->get('punchMark_code');
    $shelf_no       = $request->get('shelf_no');
    ///// ���ʥޥ������ȼ�ʬ(����ޥ�����)����Ͽ����������å�
    $query = "SELECT parts_no FROM punchMark_parts_master WHERE punchMark_code='{$punchMark_code}'";
    if (getResult2($query, $check) > 0) {   // ���ʥޥ���������Ͽ����Ƥ���
        $query = "SELECT punchMark_code FROM punchMark_master WHERE punchMark_code='{$punchMark_code}'";
        if (getResult2($query, $check) <= 1) {  // ����ޥ��������Ĥ꣱�Ĥˤʤä������Ǥ��ʤ���å���������
            $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}�����ʥޥ������ǻ��Ѥ���Ƥ��ޤ�����������ʥޥ������������Ʋ�������";
            return false;
        }
    }
    $query = "SELECT * FROM punchMark_master WHERE punchMark_code = '{$punchMark_code}' AND shelf_no = '{$shelf_no}'";
    if ( ($old_data=getPreDataRows($query)) === false ) {
        $_SESSION['s_sysmsg'] .= '�оݥǡ����μ����˼��ԡ� ����ô���Ԥ�Ϣ���Ʋ�������';
        return false;
    }
    $query = sprintf("DELETE FROM punchMark_master WHERE punchMark_code ='%s' AND shelf_no ='%s'", $punchMark_code, $shelf_no);
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}�κ���˼��ԡ�";    // .= �����
        putErrorLogWrite($query);
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}�������ޤ�����";
        // �Խ�������¸
        setEditHistory('punchMark_master', 'D', $query, $old_data);
    }
    return true;
}

//////////// �����ܥ��󤬲����줿��
function punchMark_finish($request)
{
    $punchMark_code = $request->get('punchMark_code');
    $shelf_no = $request->get('shelf_no');
    $query = sprintf("SELECT * FROM punchMark_master WHERE punchMark_code='%s' AND shelf_no='%s'", $punchMark_code, $shelf_no);
    if ( ($old_data=getPreDataRows($query)) === false ) {
        $_SESSION['s_sysmsg'] .= '�оݥǡ����μ����˼��ԡ� ����ô���Ԥ�Ϣ���Ʋ�������';
        return false;
    }
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE ����
        $query = sprintf("UPDATE punchMark_master SET make_flg=FALSE, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE punchMark_code='%s' AND shelf_no='%s'", $_SESSION['User_ID'], $punchMark_code, $shelf_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}��������λ����ޤ���";    // .= �����
            putErrorLogWrite($query);
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}�κ�������λ���ޤ�����";
            // �Խ�������¸
            setEditHistory('punchMark_master', 'U', $query, $old_data);
            return true;
        }
    } else {                                    //////// ��Ͽ�ʤ� ���顼
        $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}��������λ����ޤ���";    // .= �����
    }
    return false;
}

//////////// ����� ��åܥ��󤬲����줿��
function punchMark_cancel($request, $result)
{
    $punchMark_code = $request->get('punchMark_code');
    $shelf_no       = $request->get('shelf_no');
    $query = sprintf("SELECT * FROM punchMark_master WHERE punchMark_code='%s' AND shelf_no='%s'", $punchMark_code, $shelf_no);
    if ( ($old_data=getPreDataRows($query)) === false ) {
        $_SESSION['s_sysmsg'] .= '�оݥǡ����μ����˼��ԡ� ����ô���Ԥ�Ϣ���Ʋ�������';
        return false;
    }
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE ����
        $query = sprintf("UPDATE punchMark_master SET make_flg=TRUE, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE punchMark_code='%s' AND shelf_no='%s'", $_SESSION['User_ID'], $punchMark_code, $shelf_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}����λ��ä�����ޤ���";    // .= �����
            putErrorLogWrite($query);
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}�δ�λ���ä��ޤ�����";
            // �Խ�������¸
            setEditHistory('punchMark_master', 'U', $query, $old_data);
            return true;
        }
    } else {                                    //////// ��Ͽ�ʤ� ���顼
        $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}����λ��ä�����ޤ���";    // .= �����
    }
    return false;
}

////////////// ���ԡ��Υ�󥯤������줿��  &&���ɲ� Undefined index�б�
function pre_copy($request, $result)
{
    $res = array();
    $punchMark_code = $request->get('number');
    $shelf_no   = $request->get('shelf');
    $request->add('punchMark_code', $punchMark_code);
    $query = "SELECT * FROM punchMark_master WHERE punchMark_code='{$punchMark_code}' AND shelf_no='{$shelf_no}'";
    if (getResult($query, $res) <= 0) putErrorLogWrite($query);
    $mark       = $res[0]['mark'];
    $shape_code = $res[0]['shape_code'];
    $size_code  = $res[0]['size_code'];
    $user_code  = $res[0]['user_code'];
    $note       = $res[0]['note'];
    $request->add('shelf_no', $shelf_no);
    $request->add('mark', $mark);
    $request->add('shape_code', $shape_code);
    $request->add('size_code', $size_code);
    $request->add('user_code', $user_code);
    $request->add('note', $note);
}

////////////// ɽ����(����ɽ)�ι���ޥ������ǡ�����SQL�Ǽ���
function get_data($request, $result)
{
    $query = "
        SELECT  punchm.punchMark_code           AS ���������     -- 0
            ,   punchm.shelf_no                 AS ê��           -- 1
            ,   punchm.mark                     AS �������       -- 2
            ,   punchm.shape_code               AS ����������     -- 3
            ,   punchm.size_code                AS ������������   -- 4
            ,   punchm.user_code                AS ���襳����     -- 5
            ,   punchm.note                     AS ����           -- 6
            ,   punchm.make_flg                 AS ������ե饰   -- 7
            ,
            CASE
                WHEN lend_flg IS TRUE THEN '�߽���'
                ELSE ''
            END                         AS �߽о���     -- 8
        FROM
            punchMark_master AS punchm
        {$result->get('where')}
        ORDER BY
            -- mark ASC
            -- punchMark_code ASC
    ";
    if ($request->get('targetSortItem') == 'code') {
        $query .= '            punchMark_code ASC';
    } elseif ($request->get('targetSortItem') == 'shelf') {
        $query .= '            shelf_no ASC';
    } else {
        $request->add('targetSortItem', 'mark');
        $query .= '            mark ASC';
    }
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $field[0]   = "���������";
        $field[1]   = "ê��";
        $field[2]   = "�������";
        $field[3]   = "����������";
        $field[4]   = "������������";
        $field[5]   = "���襳����";
        $field[6]   = "����";
        $field[7]   = "������ե饰";
        $num = 8;
        $result->add_array($res);
        $result->add_array2('field', $field);
        $request->add('num', $num);
        $request->add('rows', $rows);
    } else {
        $num = count($field);
        $result->add_array($res);
        $result->add_array2('field', $field);
        $request->add('num', $num);
        $request->add('rows', $rows);
    }
}

////////////// �����ǧ�Ѥ˥������ޥ������ȷ����ޥ������������ֹ��̹���ޥ������Υǡ�����SQL�Ǽ���
function get_master($request, $result, $menu)
{
    $query = "
        SELECT  sizem.size_code                AS ������������     -- 0
            ,   sizem.size_name                AS ������̾         -- 1
            ,   sizem.note                     AS ����             -- 2
        FROM
            punchMark_size_master AS sizem
        ORDER BY
            size_code
    ";

    $res = array();
    $res_size_code = array();
    $res_size_name = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] .= "�������ޥ��������������Ͽ����Ƥ��ޤ��� ��Ͽ���ǧ���Ƥ���������";    // .= �����
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    } else {
        $result->add_array2('res_size', $res);
        $request->add('rows_size', $rows);
    }
    
    $query = "
        SELECT  shapem.shape_code               AS ����������     -- 0
            ,   shapem.shape_name               AS ����̾         -- 1
            ,   shapem.note                     AS ����           -- 2
        FROM
            punchMark_shape_master AS shapem
        ORDER BY
            shape_code
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] .= "�����ޥ��������������Ͽ����Ƥ��ޤ��� ��Ͽ���ǧ���Ƥ���������";    // .= �����
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    } else {
        $result->add_array2('res_shape', $res);
        $request->add('rows_shape', $rows);
    }
    /****************** �ʲ��������ɤ߹��ߤϣΣ�
    $query = "
        SELECT  partsm.parts_no               AS �����ֹ�     -- 0
            ,   partsm.punchMark_code         AS ���������   -- 1
            ,   partsm.note                   AS ����         -- 2
        FROM
            punchMark_parts_master as partsm
        ORDER BY
            parts_no
    ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) > 0) {
        $result->add_array2('res_parts', $res);
        $request->add('rows_parts', $rows);
    }
    ******************/
}

// View��HTML�κ���
function getViewHTMLbody($request, $menu, $result)
{
    $res         = $result->get_array();
    $field       = $result->get_array2('field');
    $num         = $request->get('num');
    $rows        = $request->get('rows');
    $res_shape   = $result->get_array2('res_shape');
    $rows_shape  = $request->get('rows_shape');
    $res_size    = $result->get_array2('res_size');
    $rows_size   = $request->get('rows_size');
    // �����
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>\n";
    $listTable .= "<link rel='stylesheet' href='../punchMark_MasterMnt.css' type='text/css' media='screen'>\n";
    $listTable .= "<script type='text/javascript' src='../punchMark_MasterMnt.js'></script>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body style='background-image:none;'>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <table class='outside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
    $listTable .= "        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "    <table class='inside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
    for ($r=0; $r<$rows; $r++) {
        if ($result->get('punchMark_code') == $res[$r][0] && $result->get('shelf_no') == $res[$r][1]) {
            $listTable .= "<tr style='background-color:#ffffc6;'>\n";
            $Mark = "name='Mark' ";
        } elseif ($request->get('punchMark_code') == $res[$r][0] && $request->get('shelf_no') == $res[$r][1]) {
            $listTable .= "<tr style='background-color:#ffffc6;'>\n";
            $Mark = "name='Mark' ";
        } else {
            $listTable .= "<tr>\n";
            $Mark = '';
        }
        $listTable .= "    <td class='winbox' width=' 6%' align='right'>    <!-- ����ѹ��Ѥ�������˥��ԡ�  -->\n";
        $listTable .= "        <a {$Mark}href='../punchMark_MasterMnt_Main.php?copy_flg=1&number=". urlencode($res[$r][0]) . "&shelf=". urlencode($res[$r][1]) . "&targetSortItem={$request->get('targetSortItem')}' target='_parent' style='text-decoration:none;'>\n";
        $del_no = $r + 1;
        $listTable .= "        {$del_no}\n";
        $listTable .= "        </a>\n";
        $listTable .= "    </td>\n";
        $res[-1][0] = '';   // ���ߡ������
        for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
            // <!--  bgcolor='#ffffc6' �������� --> 
            switch ($i) {
            case 0:     // ���������
                if ($res[$r-1][$i] == $res[$r][$i]) {
                    $listTable .= "<td class='winbox pt12b' width='10%' align='center'>&nbsp;</td>\n";
                } else {
                    $listTable .= "<td class='winbox pt12b' width='10%' align='center'>{$res[$r][$i]}</td>\n";
                }
                $listTable .= "<input type='hidden' name='punchMark_code' value='{$res[$r][$i]}'>\n";
                break;
            case 1:     // ê��
                $listTable .= "<td class='winbox pt12b' width='8%' align='center'>{$res[$r][$i]}</td>\n";
                break;
            case 2:     // �������
                $tmpView = str_replace("\r", '<br>', $res[$r][$i]);
                $listTable .= "<td class='winbox pt12b' width='14%' align='center'>{$tmpView}</td>\n";
                break;
            case 3:     // ����������
                for ($sh=0; $sh<$rows_shape; $sh++) {
                    if ($res_shape[$sh][0] == $res[$r][$i]) {
                        $shape_name = $res_shape[$sh][1];
                        break;
                    } else {
                        $shape_name = '̤����';
                    }
                }
                $listTable .= "<td class='winbox pt12b' width=' 7%' align='center'>{$shape_name}</td>\n";
                break;
            case 4:     // ������������
                for ($si=0; $si<$rows_size; $si++) {
                    if ($res_size[$si][0] == $res[$r][$i]) {
                        $size_name = $res_size[$si][1];
                        break;
                    } else {
                        $size_name = '̤����';
                    }
                }
                $listTable .= "<td class='winbox pt12b' width=' 7%' align='center'>{$size_name}</td>\n";
                break;
            case 5:     // ���襳����
                // $user_code = sprintf("%05d", $res[$r][$i]);
                if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                $listTable .= "<td class='winbox pt12b' width=' 8%' align='center'>{$res[$r][$i]}</td>\n";
                break;
            case 6:     // ����
                if ($res[$r][8] == '�߽���') {
                    $addMsg = "<span style='color:red;'>{$res[$r][8]}</span>";
                } else {
                    $addMsg = '';
                }
                if ($res[$r][$i] == '') {
                    $listTable .= "<td class='winbox pt12b' width='24%' align='left'>{$addMsg}&nbsp;</td>\n";
                } else {
                    $listTable .= "<td class='winbox pt12b' width='24%' align='left'>{$addMsg}{$res[$r][$i]}</td>\n";
                }
                break;
            case 7:     // ������ե饰
                if ($res[$r][$i] == 't') {
                    $listTable .= "<td class='winbox' align='center' width=' 8%'>\n";
                    $listTable .= "<span class='pt12br'>�����桡</span>";
                    $listTable .= "</td>\n";
                    $listTable .= "<td class='winbox' align='center' width=' 8%'>\n";
                    $listTable .= "<a href='../punchMark_MasterMnt_Main.php?finish=1&shelf_no={$res[$r][1]}&punchMark_code={$res[$r][0]}&targetSortItem={$request->get('targetSortItem')}' target='_parent' style='text-decoration:none;' class='button'>��λ</a>\n";
                    $listTable .= "</td>\n";
                } else {
                    $listTable .= "<td class='winbox' align='center' width=' 8%'>\n";
                    $listTable .= "<span class='pt12bb'>�����</span>";
                    $listTable .= "</td>\n";
                    $listTable .= "<td class='winbox' align='center' width=' 8%'>\n";
                    $listTable .= "<a href='../punchMark_MasterMnt_Main.php?cancel=1&shelf_no={$res[$r][1]}&punchMark_code={$res[$r][0]}&targetSortItem={$request->get('targetSortItem')}' target='_parent' style='text-decoration:none;' class='button'>���</a>\n";
                    $listTable .= "</td>\n";
                }
                break;
            default:
                break;
            }
        }
        $listTable .= "</tr>\n";
    }
    $listTable .= "</table>\n";
    $listTable .= "</td></tr>\n";
    $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

// View��HTML�ե�����ν���
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    // HTML�ե��������
    $file_name = "list/punchMark_MasterMnt_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}

////////// �������μ���
function getSearchCondition($request)
{
    if ($request->get('search') == '') return '';
    $where = '';
    if ($request->get('punchMark_code') != '') {
        $where .= "WHERE punchMark_code LIKE '%{$request->get('punchMark_code')}%'";
    }
    if ($request->get('shelf_no') != '' && $where != '') {
        $where .= " AND shelf_no LIKE '%{$request->get('shelf_no')}%'";
    } elseif ($request->get('shelf_no') != '') {
        $where .= "WHERE shelf_no LIKE '%{$request->get('shelf_no')}%'";
    }
    if ($request->get('mark') != '' && $where != '') {
        $where .= " AND mark LIKE '%{$request->get('mark')}%'";
    } elseif ($request->get('mark') != '') {
        $where .= "WHERE mark LIKE '%{$request->get('mark')}%'";
    }
    if ($request->get('shape_code') != '' && $where != '') {
        $where .= " AND shape_code LIKE '%{$request->get('shape_code')}%'";
    } elseif ($request->get('shape_code') != '') {
        $where .= "WHERE shape_code LIKE '%{$request->get('shape_code')}%'";
    }
    if ($request->get('size_code') != '' && $where != '') {
        $where .= " AND size_code LIKE '%{$request->get('size_code')}%'";
    } elseif ($request->get('size_code') != '') {
        $where .= "WHERE size_code LIKE '%{$request->get('size_code')}%'";
    }
    if ($request->get('user_code') != '' && $where != '') {
        $where .= " AND user_code LIKE '%{$request->get('user_code')}%'";
    } elseif ($request->get('user_code') != '') {
        $where .= "WHERE user_code LIKE '%{$request->get('user_code')}%'";
    }
    if ($request->get('note') != '' && $where != '') {
        $where .= " AND note LIKE '%{$request->get('note')}%'";
    } elseif ($request->get('note') != '') {
        $where .= "WHERE note LIKE '%{$request->get('note')}%'";
    }
    return $where;
}

//////////// ��������ɤ�Ʊ��ʪ����Ͽ����������Ƥ��ѹ������å�(���͡�ê�֤��ѹ���)
function punchMarkSameCheck($request, $flg='')
{
    $query = sprintf("SELECT * FROM punchMark_master WHERE punchMark_code='%s' LIMIT 2", $request->get('punchMark_code'));
    $res_chk = array();
    if ( ($rows=getResult($query, $res_chk)) > 0 ) {
        if ($flg == 'change' && $rows == 1) return true;    // ��ʬ���ȤΤߤ��ѹ��ϵ��Ĥ���
        if ( $request->get('mark') !== $res_chk[0]['mark'] ) {              //////// ��������ѹ��Υ����å�
            return false;
        }
        if ( $request->get('shape_code') !== $res_chk[0]['shape_code'] ) {  //////// �����ѹ��Υ����å�
            return false;
        }
        if ( $request->get('size_code') !== $res_chk[0]['size_code'] ) {    //////// �������ѹ��Υ����å�
            return false;
        }
        if ( $request->get('user_code') !== $res_chk[0]['user_code'] ) {    //////// �����ѹ��Υ����å�
            return false;
        }
    }
    return true;
}

?>
