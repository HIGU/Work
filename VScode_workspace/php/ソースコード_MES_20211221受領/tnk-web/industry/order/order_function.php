<?php
//////////////////////////////////////////////////////////////////////////////
// �۵� ���� ���� ���� �Ȳ� ���� function                                   //
// Copyright (C) 2004-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/19 Created  order_function.php                                   //
// 2004/10/29 ë������ȴ���Ƥ���Τ���                                  //
// 2005/03/10 ��ë�������� �ڤ���Ҥ��줿�ͤΥ���                       //
// 2006/04/13 �ͤΰ�ư��ȼ�������ѹ�(���������ϡ�������ź�ġ��޽���)        //
// 2006/04/20 �۵ް���θ��´ط��� function ����Ω �ʥ��ƥʥ��������   //
// 2006/06/15 10.1.3.24��30 �Σ�����ɲ�                                    //
// 2006/09/04 �ⶶ����(���ѡ��Ȥ���)���ɲá�ƣ�Ĥ���ȴ���Ƥ����Τ��ɲ�    //
// 2007/01/09 ǧ���Ѵؿ����̸��¥ޥ������б����ѹ������_old���ݴ�        //
// 2007/01/22 �����Υ���󥻥�ؿ� acceptanceInspectionCancel() ���ɲ�      //
// 2007/10/25 getDivWhereSQL()getSQLbody()���ɲá������ѥꥹ�Ȥκ�Ŭ���Τ���//
//            2���ʬ����SQL����                                            //
// 2007/11/20 �ǡ���̵���ξ��˥ޥ����������å�getItemMaster()���ɲ�       //
// 2007/12/28 PostgreSQL8.3��TEXT����TEXT�Ȥμ�ư���㥹�Ȥ�̵���ˤʤä����� //
//            uke_no > 500000 �� uke_no > '500000' ���ѹ���                 //
//            to_number������TEXT��''����' '������ȣΣǤǤ��ä�        //
// 2014/01/07 getSQLbody()�Υ����Ȥ�������(MM/DD����)�ǹԤ��Ƥ�����       //
//            �������Ѥ�YYYY/MM/DD(sort_date)�Υǡ������꥽���Ƚ��       //
//            �ѹ�                                                     ��ë //
// 2018/06/11 ���٤ơ����칩��������ֹ� 'S%'���ɲ�                    ��ë //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../function.php');        // TNK ������ function (define.php��ޤ�) getCheckAuthority()�ǻ���

/////////// ����¦�Υ��饤����Ȥ���ꤵ����ؿ� ���̸��¥ޥ������б�
function client_check()
{
    if (getCheckAuthority(15)) {
        return TRUE;
    } else {
        $_SESSION['s_sysmsg'] = '���Υѥ�����ǤϹ�����������ޤ��󡣴���ô���Ԥ�Ϣ���Ʋ�������';
        return FALSE;
    }
}
/////////// ����桼�����Υ����å� ���̸��¥ޥ������б�
function user_check($uid, $opt=0)
{
    if (getCheckAuthority(16)) {
        return TRUE;
    } else {
        $uid = $_SESSION['User_ID'];            // ������Ͽ�桼����
        $query = "SELECT trim(name) FROM user_detailes WHERE uid = '{$uid}' LIMIT 1";
        if (getUniResult($query, $name) <= 0) $name = '';
        switch ($opt) {
        case 1:
            $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥ϶۵����ʤΰ���Ͻ���ޤ��󡣴���ô���Ԥ�Ϣ���Ʋ�������";
            break;
        case 2:
            $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥ϶۵޸��������ʤκ���Ͻ���ޤ��󡣴���ô���Ԥ�Ϣ���Ʋ�������";
            break;
        case 3:
            $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥ϶۵����ʤΰ������Ƥ��ѹ��Ͻ���ޤ��󡣴���ô���Ԥ�Ϣ���Ʋ�������";
            break;
        default:
            $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥϵ��Ĥ���Ƥ��ʤ����Ǥ�������ô���Ԥ�Ϣ���Ʋ�������";
        }
        return FALSE;
    }
}
/////////// ����¦�Υ��饤����Ȥ���ꤵ����ؿ�
function client_check_old()
{
    switch ($_SERVER['REMOTE_ADDR']) {
    case '10.1.3.24' :      // �ʾڲ�
    case '10.1.3.25' :      // �ʾڲ�
    case '10.1.3.26' :      // �ʾڲ�
    case '10.1.3.27' :      // �ʾڲ�
    case '10.1.3.28' :      // �ʾڲ�
    case '10.1.3.29' :      // �ʾڲ�
    case '10.1.3.30' :      // �ʾڲ�
    case '10.1.3.120':      // �ʾڲ�
    case '10.1.3.127':      // ��˥�����
    case '10.1.3.128':      // ��˥��ʾ�
    case '10.1.3.130':      // ���ץ鸡��
    // case '10.1.3.175':      // ���ץ鸡���ƥ�᥸�㡼 �� �޽�����ѹ�(��ư����Ω)
    case '10.1.3.179':      // �ʾڲ�
    case '10.1.3.191':      // �ʾڲ�
    case '10.1.3.196':      // ���ץ鸡��(���å��ѥͥ�����)T-ckensa
    case '10.1.3.155':      // �����ĿͤΥѥ�����
    case '10.1.3.154':      // ź�ĸĿͤΥѥ�����
    // case '10.1.3.136':      // kobayashi
    // case '10.1.3.164':      // ooya
        return TRUE;
        break;
    default:
        $_SESSION['s_sysmsg'] = '���Υѥ�����ǤϹ�����������ޤ��󡣴���ô���Ԥ�Ϣ���Ʋ�������';
        return FALSE;
    }
}
/////////// ����桼�����Υ����å�
function user_check_old($uid, $opt=0)
{
    $uid = $_SESSION['User_ID'];            // ������Ͽ�桼����
    switch ($uid) {
    case '007340':      // ����
    case '009946':      // ̾Ȫ��
    case '011061':      // ����ë
    case '005789':      // ����
    case '007315':      // ��ã
    case '010529':      // ����
    case '011819':      // ����
    case '013013':      // ����
    case '014834':      // �к�
    case '015580':      // ë��
    case '009555':      // ����
    case '980001':      // ����(���硼�ȥ������)
    case '970294':      // ����(�ե륿����))
    // case '016080':      // ����(��ư)
    case '970212':      // ����
    case '970220':      // Ĺë��
    // case '970221':      // �һ�(���)
    // case '970226':      // ��ë����(���)
    case '970255':      // ����
    case '970257':      // ����
    case '001406':      // ��ë
    case '300161':      // ��ƣ���
    case '980002':      // ƣ��
    case '970301':      // �ⶶ
    // case '010561':      // ���� �ƥ�����
    // case '300101':      // ��ë �ƥ�����
        return TRUE;
        break;
    default:
        $query = "select trim(name) from user_detailes where uid = '{$uid}' limit 1";
        if (getUniResult($query, $name) <= 0) $name = '';
        switch ($opt) {
        case 1:
            $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥ϶۵����ʤΰ���Ͻ���ޤ��󡣴���ô���Ԥ�Ϣ���Ʋ�������";
            break;
        case 2:
            $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥ϶۵޸��������ʤκ���Ͻ���ޤ��󡣴���ô���Ԥ�Ϣ���Ʋ�������";
            break;
        case 3:
            $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥ϶۵����ʤΰ������Ƥ��ѹ��Ͻ���ޤ��󡣴���ô���Ԥ�Ϣ���Ʋ�������";
            break;
        default:
            $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥϵ��Ĥ���Ƥ��ʤ����Ǥ�������ô���Ԥ�Ϣ���Ʋ�������";
        }
        return FALSE;
    }
}

/////////// ���ϡ���λ�����Υ���󥻥� �ؿ�(����)
function acceptanceInspectionCancel($order_seq, $hostName='')
{
    if ($hostName == '') $hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $query = "
        SELECT str_timestamp FROM acceptance_kensa WHERE order_seq = {$order_seq} and end_timestamp IS NULL limit 1
    ";
    if (getUniResult($query, $check) >= 1) {
        ////////// ���������Υ���󥻥�
        $update = "
            BEGIN ;
            UPDATE acceptance_kensa SET str_timestamp = NULL, client = '{$hostName}', uid = '{$_SESSION['User_ID']}' WHERE order_seq = {$order_seq} ;
            DELETE FROM inspection_holding WHERE order_seq = {$order_seq} ;
            COMMIT ;
        ";
        if (query_affected($update) < 0) {  // �ȥ�󥶥��������ѹ��������� <= �� < ���ѹ�
            $_SESSION['s_sysmsg'] = '�������Ϥμ�ä�������ޤ���Ǥ���������ô���Ԥ�Ϣ���Ʋ�������';
        } else {
            $_SESSION['s_sysmsg'] = '�������Ϥ��ä��ޤ�����';
        }
    } else {
        ////////// ��λ�����Υ���󥻥�
        $update = "UPDATE acceptance_kensa SET end_timestamp = NULL, client = '{$hostName}', uid = '{$_SESSION['User_ID']}' WHERE order_seq = {$order_seq}";
        if (query_affected($update) <= 0) {
            $_SESSION['s_sysmsg'] = '������λ�μ�ä�������ޤ���Ǥ���������ô���Ԥ�Ϣ���Ʋ�������';
        } else {
            $_SESSION['s_sysmsg'] = '������λ���ä��ޤ�����';
        }
    }
}
/////////// ����������������Ͽ �ؿ�(��ͭ)
function acceptanceInspectionStart($order_seq, $hostName='')
{
    if ($hostName == '') $hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $query = "
        SELECT order_seq FROM acceptance_kensa WHERE order_seq = {$order_seq} limit 1
    ";
    if (getUniResult($query, $check) <= 0) {
        ////////// INSERT
        $insert = "INSERT INTO acceptance_kensa (order_seq, str_timestamp, client, uid) VALUES({$order_seq}, CURRENT_TIMESTAMP, '{$hostName}', '{$_SESSION['User_ID']}')";
        if (query_affected($insert) <= 0) {
            $_SESSION['s_sysmsg'] = '���ϻ��֤򿷵� ��Ͽ����ޤ���Ǥ�����';
        }
    } else {
        ////////// UPDATE
        $update = "
            UPDATE acceptance_kensa SET str_timestamp = CURRENT_TIMESTAMP, end_timestamp = NULL, client = '{$hostName}', uid = '{$_SESSION['User_ID']}' WHERE order_seq = {$order_seq}
        ";
        if (query_affected($update) <= 0) {
            $_SESSION['s_sysmsg'] = '���ϻ��֤򹹿� ��Ͽ����ޤ���Ǥ�����';
        }
    }
}
/////////// ��λ��������Ͽ �ؿ�(��ͭ)
function acceptanceInspectionEnd($order_seq, $hostName='')
{
    if ($hostName == '') $hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $query = "
        SELECT order_seq FROM acceptance_kensa WHERE order_seq = {$order_seq} limit 1
    ";
    if (getUniResult($query, $check) <= 0) {
        $_SESSION['s_sysmsg'] = "��λ���֤���Ͽ�������ȯ��Ϣ��:{$order_seq} �����Ĥ���ޤ���Ǥ�����";
    } else {
        ////////// UPDATE
        $update = "
            UPDATE acceptance_kensa SET end_timestamp = CURRENT_TIMESTAMP, client = '{$hostName}', uid = '{$_SESSION['User_ID']}' WHERE order_seq = {$order_seq}
        ";
        if (query_affected($update) <= 0) {
            $_SESSION['s_sysmsg'] = '��λ���֤򹹿� ��Ͽ����ޤ���Ǥ�����';
        }
    }
}
/////////// ���� ������������Ͽ �ؿ�(����)
function acceptanceInspectionHold($order_seq, $hostName='')
{
    if ($hostName == '') $hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $query = "
        SELECT order_seq FROM inspection_holding WHERE order_seq = {$order_seq} AND str_timestamp IS NOT NULL AND end_timestamp IS NULL limit 1
    ";
    if (getUniResult($query, $check) <= 0) {
        ////////// INSERT
        $insert = "
            INSERT INTO inspection_holding (order_seq, str_timestamp, client, uid) VALUES({$order_seq}, CURRENT_TIMESTAMP, '{$hostName}', '{$_SESSION['User_ID']}')
        ";
        if (query_affected($insert) <= 0) {
            $_SESSION['s_sysmsg'] = '�������Ǥ���Ͽ������ޤ���Ǥ����� ����ô���Ԥ�Ϣ���Ʋ�������';
        }
    } else {
        ////////// ����������
        $_SESSION['s_sysmsg'] = '����������Ǥ���';
    }
}
/////////// ���� ��λ��������Ͽ �ؿ�(����)
function acceptanceInspectionRestart($order_seq, $hostName='')
{
    if ($hostName == '') $hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $query = "
        SELECT str_timestamp FROM inspection_holding WHERE order_seq = {$order_seq} AND str_timestamp IS NOT NULL AND end_timestamp IS NULL limit 1
    ";
    if (getUniResult($query, $str_timestamp) >= 1) {
        ////////// UPDATE
        $update = "
            UPDATE inspection_holding SET end_timestamp=CURRENT_TIMESTAMP, client='{$hostName}', uid='{$_SESSION['User_ID']}' WHERE order_seq={$order_seq} AND str_timestamp='{$str_timestamp}'
        ";
        if (query_affected($update) <= 0) {
            $_SESSION['s_sysmsg'] = '�������ǤκƳ�������ޤ���Ǥ����� ����ô���Ԥ�Ϣ���Ʋ�������';
        }
    } else {
        ////////// ���˺Ƴ�
        $_SESSION['s_sysmsg'] = '���˺Ƴ����Ƥ��ޤ���';
    }
}
/////////// ����������ˤ��SQL WHERE������
function getDivWhereSQL($div)
{
    $where_div = '';
    if ($div == 'C') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'C%'";
    if ($div == 'SC') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'C%' AND data.kouji_no LIKE '%SC%'";
    if ($div == 'CS') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'C%' AND data.kouji_no NOT LIKE '%SC%'";
    if ($div == 'L') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'L%'";
    if ($div == 'T') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'T%'";
    if ($div == 'F') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'F%'";
    if ($div == 'A') $where_div = "uke_no > '500000' AND (data.parts_no LIKE 'C%' or data.parts_no LIKE 'L%' or data.parts_no LIKE 'T%' or data.parts_no LIKE 'F%' or data.parts_no LIKE 'S%')";
    if ($div == 'N') $where_div = "uke_no <= '500000' AND uke_no >= '400000' AND (data.parts_no LIKE 'C%' or data.parts_no LIKE 'L%' or data.parts_no LIKE 'T%' or data.parts_no LIKE 'F%' or data.parts_no LIKE 'S%')";
    if ($div == 'NKB') $where_div = "uke_no > '500000' AND plan.locate = '14'";
    return $where_div;
}
/////////// �����ųݤȸ����ѥꥹ�Ȥδ���SQLʸ�����
function getSQLbody($ken_date, $timestamp, $where_div, $where_parts)
{
    $query = "
        SELECT
            substr(to_char(uke_date, 'FM9999/99/99'), 6, 5) AS uke_date
            , data.order_seq            AS order_seq
            , to_char(data.order_seq,'FM000-0000')            AS ȯ��Ϣ��
            , data.uke_no               AS uke_no
            , data.parts_no             AS parts_no
            , replace(midsc, ' ', '')   AS parts_name
            , CASE
                    WHEN trim(mzist) = '' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                    ELSE substr(mzist, 1, 8)
              END                       AS parts_zai
            , CASE
                    WHEN trim(mepnt) = '' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                    ELSE substr(mepnt, 1, 8)
              END                       AS parts_parent
            , uke_q                                         -- ���տ�
            , pro_mark                                      -- ��������
            , data.vendor               AS vendor           -- Ǽ�����ֹ�
            , substr(mast.name, 1, 8)   AS vendor_name      -- Ǽ����̾
            , to_char(data.sei_no,'FM0000000')  AS sei_no   -- �������Ǥ�0�ͤ᥵��ץ�
            , CASE
                    WHEN trim(data.kouji_no) = '' THEN '---'    --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                    ELSE trim(data.kouji_no)
              END                       AS kouji_no
            , CASE
                    WHEN proc.next_pro = 'END..' THEN proc.next_pro    --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                    ELSE (SELECT substr(name, 1, 8) FROM vendor_master WHERE vendor=proc.next_pro)
              END                       AS ������
            , ken.str_timestamp         AS str_timestamp
            , ken.end_timestamp         AS end_timestamp
            , CASE
                    WHEN (SELECT order_seq FROM inspection_holding WHERE order_seq=data.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL) IS NULL
                    THEN ''
                    ELSE '������'
              END                       AS hold_flg
            , to_char(ken_date, 'FM0000/00/00')
                                        AS ken_date
            , (SELECT str_timestamp FROM inspection_holding WHERE order_seq=data.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL LIMIT 1)
                                        AS hold_time
            , ken.uid                   AS uid
            , (SELECT trim(name) FROM user_detailes WHERE uid=ken.uid LIMIT 1)
                                        AS user_name
            , substr(to_char(proc.delivery, 'FM999999/99'), 5, 5)
                                        AS delivery
            , substr(to_char(uke_date, 'FM9999/99/99'), 0, 11)
                                        AS sort_date
        FROM
            order_data          AS data
        LEFT OUTER JOIN
            order_process       AS proc     USING (sei_no, order_no, vendor)
        LEFT OUTER JOIN
            order_plan          AS plan     USING (sei_no)
        LEFT OUTER JOIN
            vendor_master       AS mast     ON (data.vendor=mast.vendor)
        LEFT OUTER JOIN
            miitem                          ON (data.parts_no=mipn)
        LEFT OUTER JOIN
            acceptance_kensa    AS ken      USING (order_seq)
        WHERE
            {$ken_date}
            AND
            data.sei_no > 0     -- ��¤�ѤǤ���
            AND
            (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
            AND
            {$timestamp}
            AND
            {$where_div} {$where_parts}
        ORDER BY
            sort_date ASC, uke_no ASC
        OFFSET 0
        LIMIT 1000
    ";
    return $query;
}
/////////// �����ֹ������̾���֤�(�����ֹ��Ŭ�������å�������̾����)
function getItemMaster($parts_no)
{
    $query = "SELECT midsc FROM miitem WHERE mipn = '{$parts_no}'";
    $name  = '�����ƥ�ޥ�����̤��Ͽ';
    getUniResult($query, $name);
    return $name;
}
?>
