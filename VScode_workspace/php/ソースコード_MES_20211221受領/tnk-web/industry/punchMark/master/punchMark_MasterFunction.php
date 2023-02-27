<?php
//////////////////////////////////////////////////////////////////////////////
// ������������ƥ� ���ޥ��������� Function                                 //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/11/08 Created   punchMark_MasterFunction.php                        //
// 2007/11/10 putErrorLogWrite()��Ȥ�����SQL���顼��debug��Ԥ�            //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���

define('ERROR_LOG_FILE', 'error_log.txt');

///// �ơ��֥��ѹ������ ���Υǡ�������˽���
/////   ������ˡ if ( ($old_data=getPreDataRows($query)) === false ) ���顼����
function getPreDataRows($save_sql='')
{
    if (!preg_match('/\bSELECT\b/i', $save_sql)) return false;
    $res = array();
    if ( ($rows = getResult2($save_sql, $res)) > 0) {
        for ($r=0; $r<$rows; $r++) {
            if ($r == 0) {
                $save_data = implode(', ', $res[$r]);   // ��ư����implode()�Ǥ��ѹ�
            } else {
                $save_data .= "\n" . implode(', ', $res[$r]);
            }
        }
        return $save_data;
    } else {
        return false;
    }
}

////////// �Խ��������¸���� ���δؿ��μ¹ԥ����ߥ󥰤��Խ��ǡ������������Ͽ���줿����ľ��˼¹Ԥ���
function setEditHistory($table_name, $id, $edit_sql, $pre_data='')
{
    $id = strtoupper($id);
    $user = $_SESSION['User_ID'] . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $edit_sql = pg_escape_string($edit_sql);
    $pre_data = pg_escape_string($pre_data);
    $query = "
        INSERT INTO punchmark_edit_history (table_name, edit_code, pre_data, edit_sql, edit_user)
        VALUES ('{$table_name}', '{$id}', '{$pre_data}', '{$edit_sql}', '{$user}')
    ";
    if (query_affected($query) < 1) {
        $_SESSION['s_sysmsg'] = '�Խ��������¸�˼��Ԥ��ޤ����� ����ô���Ԥ�Ϣ���Ʋ�������';
        return false;
    } else {
        return true;
    }
}

////////// SQL���顼����˵�Ͽ���� debug��
function putErrorLogWrite($query)
{
    $fp_error = fopen(ERROR_LOG_FILE, 'a');   // ���顼���ؤν���ߤǥ����ץ�
    $log_msg  = date('Y-m-d H:i:s');
    $log_msg .= " ���顼�λ��� SQL ʸ�ϰʲ� \n{$query}\n";
    fwrite($fp_error, $log_msg);
    fclose($fp_error);
}

?>
