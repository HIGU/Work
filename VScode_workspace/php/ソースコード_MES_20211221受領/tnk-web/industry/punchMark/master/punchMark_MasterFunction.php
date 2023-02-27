<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理システム 全マスター共通 Function                                 //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/11/08 Created   punchMark_MasterFunction.php                        //
// 2007/11/10 putErrorLogWrite()を使いしてSQLエラーのdebugを行う            //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../function.php');     // define.php と pgsql.php を require_once している

define('ERROR_LOG_FILE', 'error_log.txt');

///// テーブル変更・削除 前のデータをログに出力
/////   使用方法 if ( ($old_data=getPreDataRows($query)) === false ) エラー処理
function getPreDataRows($save_sql='')
{
    if (!preg_match('/\bSELECT\b/i', $save_sql)) return false;
    $res = array();
    if ( ($rows = getResult2($save_sql, $res)) > 0) {
        for ($r=0; $r<$rows; $r++) {
            if ($r == 0) {
                $save_data = implode(', ', $res[$r]);   // 手動からimplode()版へ変更
            } else {
                $save_data .= "\n" . implode(', ', $res[$r]);
            }
        }
        return $save_data;
    } else {
        return false;
    }
}

////////// 編集履歴を保存する この関数の実行タイミングは編集データが正常に登録された場合に直後に実行する
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
        $_SESSION['s_sysmsg'] = '編集履歴の保存に失敗しました！ 管理担当者へ連絡して下さい。';
        return false;
    } else {
        return true;
    }
}

////////// SQLエラーをログに記録する debug用
function putErrorLogWrite($query)
{
    $fp_error = fopen(ERROR_LOG_FILE, 'a');   // エラーログへの書込みでオープン
    $log_msg  = date('Y-m-d H:i:s');
    $log_msg .= " エラーの時の SQL 文は以下 \n{$query}\n";
    fwrite($fp_error, $log_msg);
    fclose($fp_error);
}

?>
