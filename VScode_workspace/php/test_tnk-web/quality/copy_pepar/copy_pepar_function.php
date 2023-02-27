<?php
//////////////////////////////////////////////////////////////////////////////
// 緊急 部品 検査 依頼 照会 共通 function                                   //
// Copyright (C) 2004-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2021/07/07 Created  order_function.php -> copy_pepar_function.php        //
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

// 期を取得
function getKi()
{
    $timeDate = date('Ym');
    $tmp = $timeDate - 195603;     // 期計算係数195603
    $tmp = $tmp / 100;             // 年の部分を取り出す
    $ki  = ceil($tmp);             // roundup と同じ

    return $ki;
}

// TNKの期を取得
function getTnkKi()
{
    return getKi() - 44;
}

// 期データを取得
function getTableKi(&$ki)
{
    $query = "
                SELECT      DISTINCT ki
                FROM        copy_paper_usage
                ORDER BY    ki DESC
             ";
    $ki   = array();

    if (($rows = getResult($query, $ki)) <= 0) {
//        $_SESSION['s_sysmsg'] .= "データが登録されていません";
    }
    return $rows;
}

// 新規で行を作成
function insertRecord($ki, $no)
{
    if( $no == 0 ) {
        $insert_qry = "
            INSERT INTO copy_paper_usage
            (ki, no, deploy, total, april, may, june, july, august, september, october, november, december, january, february, march)
            VALUES
            ('$ki', '$no', '合　計', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
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
        $_SESSION['s_sysmsg'] = "登録に失敗しました。";
        return false;
    }
}

// 行を追加
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
// データ更新
function updateKiInfo($request, $ki)
{
    $max = $request->get('tbl_rows'); // 表示されている期の行数
    $no  = 1; // 行数初期値
    $res = array();

    for( $f=0; $f<16; $f++ ) {
        $res[0][$f] = 0;
    }

    for( $r=1; $r<$max; $r++ ) {
        // 更新データをセット
        for( $f=0; $f<16; $f++ ) {
            $name = $r . "-" . $f;
            $res[$r][$f] = $request->get($name);
//            $_SESSION['s_sysmsg'] .= "TEST:({$name})" . $request->get($name);
        }

        // 更新処理
        if( trim($res[$r][2]) != "" ) { // 部署名ありなら更新
//                $query = sprintf("UPDATE copy_paper_usage SET ki=%d,no=%d,deploy='%s',total=%d,april=%d,may=%d,june=%d,july=%d,august=%d,september=%d,october=%d,november=%d,december=%d,january=%d,february=%d,march=%d WHERE ki=%d AND no=%d", $ki, $no, $res[$r][2], $res[$r][3], $res[$r][4], $res[$r][5], $res[$r][6], $res[$r][7], $res[$r][8], $res[$r][9], $res[$r][10], $res[$r][11], $res[$r][12], $res[$r][13], $res[$r][14], $res[$r][15], $ki, $no);
//                $query = sprintf("UPDATE copy_paper_usage SET ki=%d,no=%d,deploy='%s',total=april+may+june+july+august+september+october+november+december+january+february+march, april=%d,may=%d,june=%d,july=%d,august=%d,september=%d,october=%d,november=%d,december=%d,january=%d,february=%d,march=%d WHERE ki=%d AND no=%d", $ki, $no, $res[$r][2], $res[$r][4], $res[$r][5], $res[$r][6], $res[$r][7], $res[$r][8], $res[$r][9], $res[$r][10], $res[$r][11], $res[$r][12], $res[$r][13], $res[$r][14], $res[$r][15], $ki, $no);
                $query = sprintf("UPDATE copy_paper_usage SET no=%d,deploy='%s',april=%d,may=%d,june=%d,july=%d,august=%d,september=%d,october=%d,november=%d,december=%d,january=%d,february=%d,march=%d WHERE ki=%d AND no=%d", $no, $res[$r][2], $res[$r][4], $res[$r][5], $res[$r][6], $res[$r][7], $res[$r][8], $res[$r][9], $res[$r][10], $res[$r][11], $res[$r][12], $res[$r][13], $res[$r][14], $res[$r][15], $ki, $r);
//                $_SESSION['s_sysmsg'] .= "TEST:" . $query;
                if( query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "{$res[$r][2]} の更新に失敗しました。";
                } else {
                    $query = sprintf("UPDATE copy_paper_usage SET total=april+may+june+july+august+september+october+november+december+january+february+march WHERE ki=%d AND no=%d", $ki, $no);
                    query_affected($query);
                    $no++;
                }
                for( $m=4; $m<16; $m++ ) {
                    if( empty($res[$r][$m]) ) continue;
                    $res[0][$m] += $res[$r][$m];
                }
        } else { // 部署名空欄なら削除
                $query = sprintf("DELETE FROM copy_paper_usage WHERE ki=%d AND no=%d", $ki, $r );
//                $_SESSION['s_sysmsg'] .= "TEST:" . $query;
//                $res   = array();
//                if( getResult($query, $res) <= 0) {
                if( query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "空白 行の削除に失敗しました。";
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
        $_SESSION['s_sysmsg'] .= sprintf("%s 期の各月の合計情報取得に失敗!!", $ki);
    } else {
        $query = sprintf("UPDATE copy_paper_usage SET total=%d,april=%d,may=%d,june=%d,july=%d,august=%d,september=%d,october=%d,november=%d,december=%d,january=%d,february=%d,march=%d WHERE ki=%d AND no=0", $total[0][0], $total[0][1], $total[0][2], $total[0][3], $total[0][4], $total[0][5], $total[0][6], $total[0][7], $total[0][8], $total[0][9], $total[0][10], $total[0][11], $total[0][12], $ki, $no);
        if( query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$res[0][2]} の更新に失敗しました。";
        }
    }

    return true;
}

// 指定された期のデータはありますか？
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

// 指定された期の行数
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

// 指定された期の部署を取得
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
        $_SESSION['s_sysmsg'] .= sprintf("%s 期の部署情報取得に失敗!!", $ki);
    }

    return $rows;
}

// 指定された前の期の部署をセット
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
//            $_SESSION['s_sysmsg'] = "登録に失敗しました。";
//            return false;
        }
/**/
    }

    return $rows;
}

// カラム取得
function getColumn(&$column)
{
    $query = "
                SELECT  column_name
                FROM    INFORMATION_SCHEMA.COLUMNS
                WHERE   TABLE_NAME = 'copy_paper_usage'
             ";
    $res   = array();

    if( ($rows = getResult($query, $res)) <= 0 ) {
        $_SESSION['s_sysmsg'] .= "カラム情報取得失敗!!";
        return $rows;
    }

    $r = 0;
    for( $c=0; $c<$rows; $c++ ) {
        if( $res[$c][0] == 'deploy' ) {
            $column[$r][0] = "部　署";
        } else if( $res[$c][0] == 'april' ) {
            $column[$r][0] = "４月";
        } else if( $res[$c][0] == 'may' ) {
            $column[$r][0] = "５月";
        } else if( $res[$c][0] == 'june' ) {
            $column[$r][0] = "６月";
        } else if( $res[$c][0] == 'july' ) {
            $column[$r][0] = "７月";
        } else if( $res[$c][0] == 'august' ) {
            $column[$r][0] = "８月";
        } else if( $res[$c][0] == 'september' ) {
            $column[$r][0] = "９月";
        } else if( $res[$c][0] == 'october' ) {
            $column[$r][0] = "10月";
        } else if( $res[$c][0] == 'november' ) {
            $column[$r][0] = "11月";
        } else if( $res[$c][0] == 'december' ) {
            $column[$r][0] = "12月";
        } else if( $res[$c][0] == 'january' ) {
            $column[$r][0] = "１月";
        } else if( $res[$c][0] == 'february' ) {
            $column[$r][0] = "２月";
        } else if( $res[$c][0] == 'march' ) {
            $column[$r][0] = "３月";
        } else {
            continue;
        }
        $r++;
    }

    return $r;
}

// データ取得
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
//        $_SESSION['s_sysmsg'] .= sprintf("%s 期のデータがありません。", $ki);
    }
    return $rows;
}
?>
