<?php
//////////////////////////////////////////////////////////////////////////////
// �۵� ���� ���� ���� �Ȳ� ���� function                                   //
// Copyright (C) 2004-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2021/07/07 Created  order_function.php -> copy_pepar_function.php        //
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

// �������
function getKi()
{
    $timeDate = date('Ym');
    $tmp = $timeDate - 195603;     // ���׻�����195603
    $tmp = $tmp / 100;             // ǯ����ʬ����Ф�
    $ki  = ceil($tmp);             // roundup ��Ʊ��

    return $ki;
}

// TNK�δ������
function getTnkKi()
{
    return getKi() - 44;
}

// ���ǡ��������
function getTableKi(&$ki)
{
    $query = "
                SELECT      DISTINCT ki
                FROM        copy_paper_usage
                ORDER BY    ki DESC
             ";
    $ki   = array();

    if (($rows = getResult($query, $ki)) <= 0) {
//        $_SESSION['s_sysmsg'] .= "�ǡ�������Ͽ����Ƥ��ޤ���";
    }
    return $rows;
}

// �����ǹԤ����
function insertRecord($ki, $no)
{
    if( $no == 0 ) {
        $insert_qry = "
            INSERT INTO copy_paper_usage
            (ki, no, deploy, total, april, may, june, july, august, september, october, november, december, january, february, march)
            VALUES
            ('$ki', '$no', '�硡��', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
        ";
    } else {
        $insert_qry = "
            INSERT INTO copy_paper_usage
            (ki, no )
            VALUES
            ('$ki', '$no');
        ";
    }

    if( query_affected($insert_qry) <= 0 ) {
        $_SESSION['s_sysmsg'] = "��Ͽ�˼��Ԥ��ޤ�����";
        return false;
    }
}

// �Ԥ��ɲ�
function addRecord($ki)
{
    if( isTnkKi($ki) ) {
        if( ($rows = getKiRec($ki)) > 0 ) {
            insertRecord($ki, $rows);
        }
    } else {
        insertRecord($ki, 1);
    }
}

//ki,no,deploy,total,april,may,june,july,august,september,october,november,december,january,february,march
// �ǡ�������
function updateKiInfo($request, $ki)
{
    $max = $request->get('tbl_rows'); // ɽ������Ƥ�����ιԿ�
    $no  = 1; // �Կ������
    $res = array();

    for( $f=0; $f<16; $f++ ) {
        $res[0][$f] = 0;
    }

    for( $r=1; $r<$max; $r++ ) {
        // �����ǡ����򥻥å�
        for( $f=0; $f<16; $f++ ) {
            $name = $r . "-" . $f;
            $res[$r][$f] = $request->get($name);
//            $_SESSION['s_sysmsg'] .= "TEST:({$name})" . $request->get($name);
        }

        // ��������
        if( trim($res[$r][2]) != "" ) { // ����̾����ʤ鹹��
//                $query = sprintf("UPDATE copy_paper_usage SET ki=%d,no=%d,deploy='%s',total=%d,april=%d,may=%d,june=%d,july=%d,august=%d,september=%d,october=%d,november=%d,december=%d,january=%d,february=%d,march=%d WHERE ki=%d AND no=%d", $ki, $no, $res[$r][2], $res[$r][3], $res[$r][4], $res[$r][5], $res[$r][6], $res[$r][7], $res[$r][8], $res[$r][9], $res[$r][10], $res[$r][11], $res[$r][12], $res[$r][13], $res[$r][14], $res[$r][15], $ki, $no);
//                $query = sprintf("UPDATE copy_paper_usage SET ki=%d,no=%d,deploy='%s',total=april+may+june+july+august+september+october+november+december+january+february+march, april=%d,may=%d,june=%d,july=%d,august=%d,september=%d,october=%d,november=%d,december=%d,january=%d,february=%d,march=%d WHERE ki=%d AND no=%d", $ki, $no, $res[$r][2], $res[$r][4], $res[$r][5], $res[$r][6], $res[$r][7], $res[$r][8], $res[$r][9], $res[$r][10], $res[$r][11], $res[$r][12], $res[$r][13], $res[$r][14], $res[$r][15], $ki, $no);
                $query = sprintf("UPDATE copy_paper_usage SET no=%d,deploy='%s',april=%d,may=%d,june=%d,july=%d,august=%d,september=%d,october=%d,november=%d,december=%d,january=%d,february=%d,march=%d WHERE ki=%d AND no=%d", $no, $res[$r][2], $res[$r][4], $res[$r][5], $res[$r][6], $res[$r][7], $res[$r][8], $res[$r][9], $res[$r][10], $res[$r][11], $res[$r][12], $res[$r][13], $res[$r][14], $res[$r][15], $ki, $r);
//                $_SESSION['s_sysmsg'] .= "TEST:" . $query;
                if( query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "{$res[$r][2]} �ι����˼��Ԥ��ޤ�����";
                } else {
                    $query = sprintf("UPDATE copy_paper_usage SET total=april+may+june+july+august+september+october+november+december+january+february+march WHERE ki=%d AND no=%d", $ki, $no);
                    query_affected($query);
                    $no++;
                }
                for( $m=4; $m<16; $m++ ) {
                    if( empty($res[$r][$m]) ) continue;
                    $res[0][$m] += $res[$r][$m];
                }
        } else { // ����̾����ʤ���
                $query = sprintf("DELETE FROM copy_paper_usage WHERE ki=%d AND no=%d", $ki, $r );
//                $_SESSION['s_sysmsg'] .= "TEST:" . $query;
//                $res   = array();
//                if( getResult($query, $res) <= 0) {
                if( query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "���� �Ԥκ���˼��Ԥ��ޤ�����";
                }
        }
    }

    for( $f=0; $f<3; $f++ ) {
        $name = "0-" . $f;
        $res[0][$f] = $request->get($name);
    }
    $query = sprintf("SELECT sum(total),sum(april),sum(may),sum(june),sum(july),sum(august),sum(september),sum(october),sum(november),sum(december),sum(january),sum(february),sum(march) FROM copy_paper_usage WHERE ki=%d AND no>0", $ki);
    $total = array();
    if( ($rows = getResult2($query, $total)) <= 0 ) {
        $_SESSION['s_sysmsg'] .= sprintf("%s ���γƷ�ι�׾�������˼���!!", $ki);
    } else {
        $query = sprintf("UPDATE copy_paper_usage SET total=%d,april=%d,may=%d,june=%d,july=%d,august=%d,september=%d,october=%d,november=%d,december=%d,january=%d,february=%d,march=%d WHERE ki=%d AND no=0", $total[0][0], $total[0][1], $total[0][2], $total[0][3], $total[0][4], $total[0][5], $total[0][6], $total[0][7], $total[0][8], $total[0][9], $total[0][10], $total[0][11], $total[0][12], $ki, $no);
        if( query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$res[0][2]} �ι����˼��Ԥ��ޤ�����";
        }
    }

    return true;
}

// ���ꤵ�줿���Υǡ����Ϥ���ޤ�����
function isTnkKi($ki)
{
    $query = "
                SELECT      ki
                FROM        copy_paper_usage
                WHERE       ki=$ki
                LIMIT 1
             ";
    $res   = array();

    if( getResult($query, $res) <= 0) {
        return false;
    }
    return true;
}

// ���ꤵ�줿���ιԿ�
function getKiRec($ki)
{
    $query = "
                SELECT      no
                FROM        copy_paper_usage
                WHERE       ki=$ki
             ";
    $res   = array();

    return getResult($query, $res);
}

// ���ꤵ�줿������������
function getBusyoRec($ki, &$res)
{
    $query = "
                SELECT      deploy
                FROM        copy_paper_usage
                WHERE       ki=$ki AND no>0
                ORDER BY    no ASC
             ";
    $res   = array();

    if( ($rows = getResult2($query, $res)) <= 0 ) {
        $_SESSION['s_sysmsg'] .= sprintf("%s ���������������˼���!!", $ki);
    }

    return $rows;
}

// ���ꤵ�줿���δ�������򥻥å�
function setBusyoRec($ki)
{
    $res  = array();
    $rows = getBusyoRec($ki-1, $res);

    $query = sprintf("DELETE FROM copy_paper_usage WHERE ki=%d AND no>0", $ki );
    query_affected($query);

    for( $n=0, $no=1; $n<$rows; $n++, $no++ ) {
        $insert_qry = "
            INSERT INTO copy_paper_usage
            (ki, no, deploy )
            VALUES
            ('$ki', '$no', '{$res[$n][0]}');
        ";
/**/
        if( query_affected($insert_qry) <= 0 ) {
//            $_SESSION['s_sysmsg'] = "��Ͽ�˼��Ԥ��ޤ�����";
//            return false;
        }
/**/
    }

    return $rows;
}

// ��������
function getColumn(&$column)
{
    $query = "
                SELECT  column_name
                FROM    INFORMATION_SCHEMA.COLUMNS
                WHERE   TABLE_NAME = 'copy_paper_usage'
             ";
    $res   = array();

    if( ($rows = getResult($query, $res)) <= 0 ) {
        $_SESSION['s_sysmsg'] .= "���������������!!";
        return $rows;
    }

    $r = 0;
    for( $c=0; $c<$rows; $c++ ) {
        if( $res[$c][0] == 'deploy' ) {
            $column[$r][0] = "������";
        } else if( $res[$c][0] == 'april' ) {
            $column[$r][0] = "����";
        } else if( $res[$c][0] == 'may' ) {
            $column[$r][0] = "����";
        } else if( $res[$c][0] == 'june' ) {
            $column[$r][0] = "����";
        } else if( $res[$c][0] == 'july' ) {
            $column[$r][0] = "����";
        } else if( $res[$c][0] == 'august' ) {
            $column[$r][0] = "����";
        } else if( $res[$c][0] == 'september' ) {
            $column[$r][0] = "����";
        } else if( $res[$c][0] == 'october' ) {
            $column[$r][0] = "10��";
        } else if( $res[$c][0] == 'november' ) {
            $column[$r][0] = "11��";
        } else if( $res[$c][0] == 'december' ) {
            $column[$r][0] = "12��";
        } else if( $res[$c][0] == 'january' ) {
            $column[$r][0] = "����";
        } else if( $res[$c][0] == 'february' ) {
            $column[$r][0] = "����";
        } else if( $res[$c][0] == 'march' ) {
            $column[$r][0] = "����";
        } else {
            continue;
        }
        $r++;
    }

    return $r;
}

// �ǡ�������
function getKiInfo($ki, &$res)
{
    $query = sprintf("
                SELECT      *
                FROM        copy_paper_usage
                WHERE       ki=%d
                ORDER BY    ki DESC, no ASC
             ", $ki);
    $res   = array();

    if (($rows = getResult($query, $res)) <= 0) {
//        $_SESSION['s_sysmsg'] .= sprintf("%s ���Υǡ���������ޤ���", $ki);
    }
    return $rows;
}
?>
