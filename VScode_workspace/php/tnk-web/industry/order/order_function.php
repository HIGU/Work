<?php
//////////////////////////////////////////////////////////////////////////////
// 緊急 部品 検査 依頼 照会 共通 function                                   //
// Copyright (C) 2004-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/19 Created  order_function.php                                   //
// 2004/10/29 谷口さんが抜けているのを修正                                  //
// 2005/03/10 菅谷さんを許可 及び退社された人のメンテ                       //
// 2006/04/13 人の移動に伴う権限変更(深見・菊地・吉成・添田・五十嵐)        //
// 2006/04/20 緊急依頼の権限関係の function を独立 （メンテナンス性向上）   //
// 2006/06/15 10.1.3.24～30 の７台を追加                                    //
// 2006/09/04 高橋さん(新パートさん)を追加。藤田さんが抜けていたので追加    //
// 2007/01/09 認証用関数を共通権限マスター対応へ変更。旧を_oldで保管        //
// 2007/01/22 検査のキャンセル関数 acceptanceInspectionCancel() を追加      //
// 2007/10/25 getDivWhereSQL()getSQLbody()を追加。検査済リストの最適化のため//
//            2回に分けてSQL取得                                            //
// 2007/11/20 データ無しの場合にマスターチェックgetItemMaster()を追加       //
// 2007/12/28 PostgreSQL8.3でTEXTと非TEXTとの自動キャストが無効になったため //
//            uke_no > 500000 → uke_no > '500000' へ変更。                 //
//            to_numberも試したがTEXTに''又は' 'があるとＮＧであった        //
// 2014/01/07 getSQLbody()のソートが受付日(MM/DD形式)で行われていた為       //
//            ソート用にYYYY/MM/DD(sort_date)のデータを作りソート順を       //
//            変更                                                     大谷 //
// 2018/06/11 すべて・日東工器に部品番号 'S%'を追加                    大谷 //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../function.php');        // TNK 全共通 function (define.phpを含む) getCheckAuthority()で使用

/////////// 検査側のクライアントを限定させる関数 共通権限マスター対応
function client_check()
{
    if (getCheckAuthority(15)) {
        return TRUE;
    } else {
        $_SESSION['s_sysmsg'] = 'このパソコンでは更新処理出来ません。管理担当者へ連絡して下さい。';
        return FALSE;
    }
}
/////////// 依頼ユーザーのチェック 共通権限マスター対応
function user_check($uid, $opt=0)
{
    if (getCheckAuthority(16)) {
        return TRUE;
    } else {
        $uid = $_SESSION['User_ID'];            // 依頼登録ユーザー
        $query = "SELECT trim(name) FROM user_detailes WHERE uid = '{$uid}' LIMIT 1";
        if (getUniResult($query, $name) <= 0) $name = '';
        switch ($opt) {
        case 1:
            $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは緊急部品の依頼は出来ません。管理担当者へ連絡して下さい。";
            break;
        case 2:
            $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは緊急検査依頼品の削除は出来ません。管理担当者へ連絡して下さい。";
            break;
        case 3:
            $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは緊急部品の依頼内容の変更は出来ません。管理担当者へ連絡して下さい。";
            break;
        default:
            $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは許可されていない操作です。管理担当者へ連絡して下さい。";
        }
        return FALSE;
    }
}
/////////// 検査側のクライアントを限定させる関数
function client_check_old()
{
    switch ($_SERVER['REMOTE_ADDR']) {
    case '10.1.3.24' :      // 品証課
    case '10.1.3.25' :      // 品証課
    case '10.1.3.26' :      // 品証課
    case '10.1.3.27' :      // 品証課
    case '10.1.3.28' :      // 品証課
    case '10.1.3.29' :      // 品証課
    case '10.1.3.30' :      // 品証課
    case '10.1.3.120':      // 品証課
    case '10.1.3.127':      // リニア検査
    case '10.1.3.128':      // リニア品証
    case '10.1.3.130':      // カプラ検査
    // case '10.1.3.175':      // カプラ検査テレメジャー → 五十嵐へ変更(移動で組立)
    case '10.1.3.179':      // 品証課
    case '10.1.3.191':      // 品証課
    case '10.1.3.196':      // カプラ検査(タッチパネル専用)T-ckensa
    case '10.1.3.155':      // 吉成個人のパソコン
    case '10.1.3.154':      // 添田個人のパソコン
    // case '10.1.3.136':      // kobayashi
    // case '10.1.3.164':      // ooya
        return TRUE;
        break;
    default:
        $_SESSION['s_sysmsg'] = 'このパソコンでは更新処理出来ません。管理担当者へ連絡して下さい。';
        return FALSE;
    }
}
/////////// 依頼ユーザーのチェック
function user_check_old($uid, $opt=0)
{
    $uid = $_SESSION['User_ID'];            // 依頼登録ユーザー
    switch ($uid) {
    case '007340':      // 千葉
    case '009946':      // 名畑目
    case '011061':      // 小森谷
    case '005789':      // 郡司
    case '007315':      // 安達
    case '010529':      // 飯島
    case '011819':      // 手塚
    case '013013':      // 鈴木
    case '014834':      // 石崎
    case '015580':      // 谷口
    case '009555':      // 菊地
    case '980001':      // 深見(ショートタイム時)
    case '970294':      // 深見(フルタイム))
    // case '016080':      // 吉成(移動)
    case '970212':      // 小松
    case '970220':      // 長谷川
    // case '970221':      // 片山(退社)
    // case '970226':      // 大谷恵美(退社)
    case '970255':      // 花塚
    case '970257':      // 秋元
    case '001406':      // 菅谷
    case '300161':      // 斎藤千尋
    case '980002':      // 藤田
    case '970301':      // 高橋
    // case '010561':      // 小林 テスト用
    // case '300101':      // 大谷 テスト用
        return TRUE;
        break;
    default:
        $query = "select trim(name) from user_detailes where uid = '{$uid}' limit 1";
        if (getUniResult($query, $name) <= 0) $name = '';
        switch ($opt) {
        case 1:
            $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは緊急部品の依頼は出来ません。管理担当者へ連絡して下さい。";
            break;
        case 2:
            $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは緊急検査依頼品の削除は出来ません。管理担当者へ連絡して下さい。";
            break;
        case 3:
            $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは緊急部品の依頼内容の変更は出来ません。管理担当者へ連絡して下さい。";
            break;
        default:
            $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは許可されていない操作です。管理担当者へ連絡して下さい。";
        }
        return FALSE;
    }
}

/////////// 開始・終了日時のキャンセル 関数(共用)
function acceptanceInspectionCancel($order_seq, $hostName='')
{
    if ($hostName == '') $hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $query = "
        SELECT str_timestamp FROM acceptance_kensa WHERE order_seq = {$order_seq} and end_timestamp IS NULL limit 1
    ";
    if (getUniResult($query, $check) >= 1) {
        ////////// 開始日時のキャンセル
        $update = "
            BEGIN ;
            UPDATE acceptance_kensa SET str_timestamp = NULL, client = '{$hostName}', uid = '{$_SESSION['User_ID']}' WHERE order_seq = {$order_seq} ;
            DELETE FROM inspection_holding WHERE order_seq = {$order_seq} ;
            COMMIT ;
        ";
        if (query_affected($update) < 0) {  // トランザクションへ変更したため <= → < へ変更
            $_SESSION['s_sysmsg'] = '検査開始の取消しが出来ませんでした。管理担当者へ連絡して下さい。';
        } else {
            $_SESSION['s_sysmsg'] = '検査開始を取消しました。';
        }
    } else {
        ////////// 終了日時のキャンセル
        $update = "UPDATE acceptance_kensa SET end_timestamp = NULL, client = '{$hostName}', uid = '{$_SESSION['User_ID']}' WHERE order_seq = {$order_seq}";
        if (query_affected($update) <= 0) {
            $_SESSION['s_sysmsg'] = '検査完了の取消しが出来ませんでした。管理担当者へ連絡して下さい。';
        } else {
            $_SESSION['s_sysmsg'] = '検査完了を取消しました。';
        }
    }
}
/////////// 検査開始日時の登録 関数(共有)
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
            $_SESSION['s_sysmsg'] = '開始時間を新規 登録出来ませんでした。';
        }
    } else {
        ////////// UPDATE
        $update = "
            UPDATE acceptance_kensa SET str_timestamp = CURRENT_TIMESTAMP, end_timestamp = NULL, client = '{$hostName}', uid = '{$_SESSION['User_ID']}' WHERE order_seq = {$order_seq}
        ";
        if (query_affected($update) <= 0) {
            $_SESSION['s_sysmsg'] = '開始時間を更新 登録出来ませんでした。';
        }
    }
}
/////////// 終了日時の登録 関数(共有)
function acceptanceInspectionEnd($order_seq, $hostName='')
{
    if ($hostName == '') $hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $query = "
        SELECT order_seq FROM acceptance_kensa WHERE order_seq = {$order_seq} limit 1
    ";
    if (getUniResult($query, $check) <= 0) {
        $_SESSION['s_sysmsg'] = "完了時間を登録する時に発行連番:{$order_seq} が見つかりませんでした。";
    } else {
        ////////// UPDATE
        $update = "
            UPDATE acceptance_kensa SET end_timestamp = CURRENT_TIMESTAMP, client = '{$hostName}', uid = '{$_SESSION['User_ID']}' WHERE order_seq = {$order_seq}
        ";
        if (query_affected($update) <= 0) {
            $_SESSION['s_sysmsg'] = '完了時間を更新 登録出来ませんでした。';
        }
    }
}
/////////// 中断 開始日時の登録 関数(共用)
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
            $_SESSION['s_sysmsg'] = '検査中断の登録が出来ませんでした！ 管理担当者に連絡して下さい。';
        }
    } else {
        ////////// 既に中断中
        $_SESSION['s_sysmsg'] = '既に中断中です。';
    }
}
/////////// 中断 終了日時の登録 関数(共用)
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
            $_SESSION['s_sysmsg'] = '検査中断の再開が出来ませんでした！ 管理担当者に連絡して下さい。';
        }
    } else {
        ////////// 既に再開
        $_SESSION['s_sysmsg'] = '既に再開しています。';
    }
}
/////////// 指定事業部によるSQL WHERE区を取得
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
/////////// 検査仕掛と検査済リストの基本SQL文を取得
function getSQLbody($ken_date, $timestamp, $where_div, $where_parts)
{
    $query = "
        SELECT
            substr(to_char(uke_date, 'FM9999/99/99'), 6, 5) AS uke_date
            , data.order_seq            AS order_seq
            , to_char(data.order_seq,'FM000-0000')            AS 発行連番
            , data.uke_no               AS uke_no
            , data.parts_no             AS parts_no
            , replace(midsc, ' ', '')   AS parts_name
            , CASE
                    WHEN trim(mzist) = '' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                    ELSE substr(mzist, 1, 8)
              END                       AS parts_zai
            , CASE
                    WHEN trim(mepnt) = '' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                    ELSE substr(mepnt, 1, 8)
              END                       AS parts_parent
            , uke_q                                         -- 受付数
            , pro_mark                                      -- 工程記号
            , data.vendor               AS vendor           -- 納入先番号
            , substr(mast.name, 1, 8)   AS vendor_name      -- 納入先名
            , to_char(data.sei_no,'FM0000000')  AS sei_no   -- 指定桁数での0詰めサンプル
            , CASE
                    WHEN trim(data.kouji_no) = '' THEN '---'    --NULLでなくてスペースで埋まっている場合はこれ！
                    ELSE trim(data.kouji_no)
              END                       AS kouji_no
            , CASE
                    WHEN proc.next_pro = 'END..' THEN proc.next_pro    --NULLでなくてスペースで埋まっている場合はこれ！
                    ELSE (SELECT substr(name, 1, 8) FROM vendor_master WHERE vendor=proc.next_pro)
              END                       AS 次工程
            , ken.str_timestamp         AS str_timestamp
            , ken.end_timestamp         AS end_timestamp
            , CASE
                    WHEN (SELECT order_seq FROM inspection_holding WHERE order_seq=data.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL) IS NULL
                    THEN ''
                    ELSE '中断中'
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
            data.sei_no > 0     -- 製造用であり
            AND
            (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
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
/////////// 部品番号で部品名を返す(部品番号の適正チェックと部品名取得)
function getItemMaster($parts_no)
{
    $query = "SELECT midsc FROM miitem WHERE mipn = '{$parts_no}'";
    $name  = 'アイテムマスター未登録';
    getUniResult($query, $name);
    return $name;
}
?>
