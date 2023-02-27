<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の作業管理 着手・実績データ 照会         MVC Model 部                //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/19 Created   assembly_process_show_Model.php                     //
// 2006/01/20 _constructに$request->get('showGroup') != '0' && の場合を追加 //
// 2006/04/12 １ページの表示行数の初期化ロジックを__construct()に追加       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../ComTableMntClass.php');    // TNK 全共通 テーブルメンテ&ページ制御Class


/*****************************************************************************************
*         組立 手作業 着手・実績の照会用 MVCのModel部の base class 基底クラスの定義      *
*****************************************************************************************/
class AssemblyProcessShow_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        switch ($request->get('showMenu')) {
        case 'EndList':
            $where  = "WHERE end_time <= '" . date('Ymd 235959') . "' ";
            $where .= "AND end_time >= (timestamp '" . date('Ymd 000000') . "' - interval '62 day') ";
            if ($request->get('showGroup') != '0' && $request->get('showGroup') != '') {    // 0 は全てを意味する
                $where .= "AND group_no = " . $request->get('showGroup');
            }
            $sql_sum = "
                SELECT count(*) FROM assembly_process_time $where
            ";
            break;
        case 'StartList':
        default:
            if ($request->get('showGroup') != '0' && $request->get('showGroup') != '') {    // 0 は全てを意味する
                $where = "WHERE end_time='19700101 000000' AND " . "group_no = " . $request->get('showGroup');
            } else {
                $where = "WHERE end_time='19700101 000000'";
            }
            $sql_sum = "
                SELECT count(*) FROM assembly_process_time $where
            ";
        }
        ///// SQL文のWHERE区をPropertiesに登録
        $this->where  = $where;
        ///// log file の指定
        $log_file = 'assembly_process_show.log';
        ///// １ページの行数 初期値 指定
        $query = "
            SELECT count(*) FROM assembly_process_group WHERE active
        ";
        $count = 0;
        $this->getUniResult($query, $count);
        $rows = ($count / 6);
        switch (TRUE) {
        case ($rows <= 1):
            $page = 20;
            break;
        case ($rows <= 2):
            $page = 18;
            break;
        case ($rows <= 3):
            $page = 16;
            break;
        default:
            $page = 14;
        }
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, $log_file, $page);
    }
    
    ////////// Operatorの権限チェック & 組立作業者の名前を検索し返す
    public function getAuthorityUserName($user_id='')
    {
        if ($this->assemblyAuthUser()) {
            if ($user_id != '') {
                ///// user_idの適正チェック
                return $this->checkUserID($user_id);
            } else {
                return true;    // User_idが指定されてなければ Authority Check のみでリターン
            }
        } else {
            $_SESSION['s_sysmsg'] = '組立 作業 実績 修正 メニューの編集権限がありません！';
        }
        return false;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    組立着手 一覧表
    public function getViewStartList(&$result)
    {
        ///// 以下で AS assyuser は AS user でエラーになる(予約語)ため冗長化した
        $query = "SELECT plan_no        AS 計画番号         -- 00
                        ,parts_no       AS 製品番号         -- 01
                        ,substr(midsc, 1, 20)
                                        AS 製品名           -- 02
                        ,plan_pcs       AS 計画残数         -- 03
                        ,user_id        AS 社員番号         -- 04
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '応援者' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS 作業者           -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS 開始日時         -- 06
                        ,serial_no      AS 連番             -- 07
                        ,plan           AS 計画数           -- 08
                        -----------------------------リストは上記まで
                        ,serial_no      AS 連番             -- 09
                        ,to_char(str_time, 'YY/MM/DD HH24:MI:SS')
                                        AS 開始詳細         -- 10
                        ,to_char(end_time, 'YY/MM/DD HH24:MI:SS')
                                        AS 完了詳細         -- 11
                        ,CASE
                            WHEN plan_pcs > 0
                            THEN Uround(assy_time / plan_pcs, 3)
                            ELSE assy_time
                         END            AS 工数             -- 12
                        ,plan - cut_plan AS 計画数          -- 13
                    FROM
                        assembly_process_time
                    LEFT OUTER JOIN
                        assembly_schedule USING(plan_no)
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    LEFT OUTER JOIN
                        user_detailes   AS assyuser ON (user_id=uid)
                    {$this->where}
                    ORDER BY
                        str_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List部    組立完了 一覧表
    public function getViewEndList(&$result)
    {
        $query = "SELECT plan_no        AS 計画番号     -- 00
                        ,parts_no       AS 製品番号     -- 01
                        ,substr(midsc, 1, 20)
                                        AS 製品名       -- 02
                        ,plan_pcs       AS 計画残数     -- 03
                        ,user_id        AS 社員番号     -- 04
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '応援者' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS 作業者       -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS 開始日時     -- 06
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS 完了日時     -- 07
                        ,assy_time      AS 合計工数     -- 08
                        -----------------------------リストは上記まで
                        ,serial_no      AS 連番         -- 09
                        ,to_char(str_time, 'YY/MM/DD HH24:MI:SS')
                                        AS 開始詳細     -- 10
                        ,to_char(end_time, 'YY/MM/DD HH24:MI:SS')
                                        AS 完了詳細     -- 11
                        ,CASE
                            WHEN plan_pcs > 0
                            THEN Uround(assy_time / plan_pcs, 3)
                            ELSE assy_time
                         END            AS 工数         -- 12
                        ,plan - cut_plan AS 計画数      -- 13
                        ,CASE
                            WHEN CURRENT_DATE = CAST(end_time AS date)
                            THEN '取消有効'
                            ELSE '取消無効'
                        END             AS 取消         -- 14
                    FROM
                        assembly_process_time
                    LEFT OUTER JOIN
                        assembly_schedule USING(plan_no)
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    LEFT OUTER JOIN
                        user_detailes   AS assyuser ON (user_id=uid)
                    {$this->where}
                    ORDER BY
                        end_time DESC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List部    組立グループ(作業区) 登録内容 一覧表 (ページコントロールなし)
    public function getViewGroupList(&$result)
    {
        $query = "SELECT group_no           AS グループ番号         -- 00
                        ,group_name         AS グループ名           -- 01
                        ------------------------ リストは上記まで
                        ,CASE
                            WHEN div = 'C' THEN 'カプラ'
                            WHEN div = 'L' THEN 'リニア'
                            ELSE '未登録'
                         END                AS 事業部               -- 02
                        ,CASE
                            WHEN product = 'C' THEN 'カプラ標準'
                            WHEN product = 'S' THEN 'カプラ特注'
                            WHEN product = 'L' THEN 'リニア製品'
                            WHEN product = 'B' THEN 'バイモル'
                            ELSE '未登録'
                         END                AS 製品グループ         -- 03
                        ,to_char(last_date, 'YY/MM/DD HH24:MI')
                                            AS 変更日時             -- 04
                        ,CASE
                            WHEN active THEN '有効'
                            ELSE '無効'
                         END                AS 有効無効             -- 05
                        ,div                                        -- 06
                        ,product                                    -- 07
                    FROM
                        assembly_process_group
                    WHERE
                        active
                    ORDER BY
                        group_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 組立グループ(作業区) 名称を返す
    public function getGroupName($group_no)
    {
        $query = "
            SELECT
                group_name     AS グループ名
            FROM
                assembly_process_group
            WHERE
                group_no = {$group_no}
        ";
        $res = '未登録';
        $this->getUniResult($query, $res);
        return $res;
    }
    
    ///// Edit Confirm_delete 1レコード分
    public function getViewDataEdit($serial_no, $request)
    {
        $query = "SELECT plan_no        AS 計画番号             -- 00
                        ,parts_no       AS 製品番号             -- 01
                        ,substr(midsc, 1, 20)
                                        AS 製品名               -- 02
                        ,plan - cut_plan
                                        AS 計画数               -- 03
                        ,user_id        AS 社員番号             -- 04
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '応援者' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS 作業者               -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS 開始日時             -- 06
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS 完了日時             -- 07
                        ,serial_no      AS 連番                 -- 08
                        ,assy_time      AS 合計工数             -- 09
                        --------------- これ以下はリストデータではない
                        ,to_char(str_time, 'YYYY') AS str_year   -- 10
                        ,to_char(str_time, 'MM')   AS str_month  -- 11
                        ,to_char(str_time, 'DD')   AS str_day    -- 12
                        ,to_char(str_time, 'HH24') AS str_hour   -- 13
                        ,to_char(str_time, 'MI')   AS str_minute -- 14
                        ,to_char(end_time, 'YYYY') AS end_year   -- 15
                        ,to_char(end_time, 'MM')   AS end_month  -- 16
                        ,to_char(end_time, 'DD')   AS end_day    -- 17
                        ,to_char(end_time, 'HH24') AS end_hour   -- 18
                        ,to_char(end_time, 'MI')   AS end_minute -- 19
                    FROM
                        assembly_process_time
                    LEFT OUTER JOIN
                        assembly_schedule USING(plan_no)
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    LEFT OUTER JOIN
                        user_detailes   AS assyuser ON (user_id=uid)
                    WHERE
                        serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $request->add('plan_no',    $res[0][0]);
            $request->add('assy_no',    $res[0][1]);
            $request->add('assy_name',  $res[0][2]);
            $request->add('plan',       $res[0][3]);
            $request->add('user_id',    $res[0][4]);
            $request->add('user_name',  $res[0][5]);
            $request->add('str_time',   $res[0][6]);
            $request->add('end_time',   $res[0][7]);
            $request->add('serial_no',  $res[0][8]);
            $request->add('assy_time',  $res[0][9]);
            // これより以下は修正用データ
            $request->add('str_year',   $res[0][10]);
            $request->add('str_month',  $res[0][11]);
            $request->add('str_day',    $res[0][12]);
            $request->add('str_hour',   $res[0][13]);
            $request->add('str_minute', $res[0][14]);
            $request->add('end_year',   $res[0][15]);
            $request->add('end_month',  $res[0][16]);
            $request->add('end_day',    $res[0][17]);
            $request->add('end_hour',   $res[0][18]);
            $request->add('end_minute', $res[0][19]);
        }
        return $rows;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// 組立指示メニューの編集権限チェックメソッド(共用メソッド)
    protected function assemblyAuthUser()
    {
        $LoginUser = $_SESSION['User_ID'];
        $query = "select act_id from cd_table where uid='$LoginUser'";
        if (getUniResult($query, $sid) > 0) {
            switch ($sid) {             // 社員の所属する部門コードでチェック
            case 500:                   // 生産部 (2005/12/15追加)
            case 176:
            case 522:
            case 523:
            case 525:
                return true;            // カプラ組立(資材を除く)
            case 551:
            case 175:
            case 560:
            case 537:
            case 534:
                return true;            // リニア組立(資材・検査を除く)
            default:
                if ($_SESSION['Auth'] >= 3) { // テスト用
                    return true;
                }
                return false;
            }
        } else {
            return false;
        }
    }
    ////////// 組立指示メニューの時間(休み時間を除く)の合計(分)を返す
    protected function getSumTime($str_time, $end_time)
    {
        // 合計時間(分)を取得(休み時間を除く前)
        // 合計工数は小数点以下3位まで計算する。(組立工数のため)
        $query = "
            SELECT
            Uround(CAST(extract(epoch from timestamp '{$end_time}' - timestamp '{$str_time}') / 60 AS NUMERIC), 3)
        ";
        $res = 0;
        $this->getUniResult($query, $res);
        $str_date = substr($str_time, 0, 10);
        $end_date = substr($end_time, 0, 10);
        // 朝礼の５分
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 08:30:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 08:35:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 5;
        // 10:30の５分
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 10:30:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 10:35:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 5;
        // 昼休みの４５分
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 12:00:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 12:45:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 45;
        // 15:00の１０分
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 15:00:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 15:10:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 10;
        // 17:15の１５分
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 17:15:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 17:30:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 15;
        
        // エラーチェック
        if ($res < 0) $res = 0;
        return number_format($res, 3);      // 途中の休み時間で引算した場合に少数桁が0の場合なくなるため追加
    }
    ////////// 同時作業 計画番号の計画数の合計を再計算し更新する
    protected function plan_pcsUpdate($request)
    {
        // 同時 計画番号の合計計画数(plan_pcs)を取得 同一作業者の場合を同時組立計画分と見なす
        $query = "
            SELECT sum(plan_pcs) FROM assembly_process_time
            WHERE
                (str_time='{$request->get('str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$request->get('end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
        ";
        $plan_all_pcs = 0;     // 初期化
        $this->getUniResult($query, $plan_all_pcs);
        // ここで同時組立着手分の他の計画があればplan_all_pcsをUPDATEする
        $query = "
            SELECT serial_no FROM assembly_process_time
            WHERE
                (str_time='{$request->get('str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$request->get('end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
        ";
        if ($this->getUniResult($query, $tmp) > 0) {    // 1件でもあれば UPDATE する
            $update_sql = "
            UPDATE assembly_process_time SET plan_all_pcs={$plan_all_pcs}
            WHERE
                (str_time='{$request->get('str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$request->get('end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            ";
            if (!$this->execute_Update($update_sql)) {
                $_SESSION['s_sysmsg'] = '同時組立計画分の合計計画数の変更が出来ませんでした！ 管理担当者へ連絡して下さい。';
            }
        }
        return $plan_all_pcs;
    }
    
    ////////// 同時作業計画のassy_timeの更新 (1作業者の同時作業 計画)
    protected function assyTimeUpdate($request)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // 休み時間を除いた合計工数(分)を取得
        $sum_time = $this->getSumTime($request->get('str_time'), $request->get('end_time'));
        $query = "
            SELECT serial_no, plan_pcs, plan_all_pcs FROM assembly_process_time
            WHERE
                (str_time='{$request->get('str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$request->get('end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        for ($i=0; $i<$rows; $i++) {
            $serial_no    = $res[$i][0];
            $plan_pcs     = $res[$i][1];
            $plan_all_pcs = $res[$i][2];
            $assy_time = round(($plan_pcs / $plan_all_pcs) * $sum_time, 3);    // １計画分の工数(分)を算出
            $update_sql = "
                UPDATE assembly_process_time SET
                assy_time={$assy_time}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            if (!$this->execute_Update($update_sql)) {
                $_SESSION['s_sysmsg'] = '同時組立計画分の工数の変更が出来ませんでした！ 管理担当者へ連絡して下さい。';
            }
        }
        return true;
    }
    
    ////////// 組立 作業者のuser_idの適正をチェックしメッセージ＋結果(氏名=OK,false=NG)を返す
    protected function checkUserID($user_id)
    {
        ///// user_idの適正チェック
        $chk = "SELECT trim(name) FROM user_detailes WHERE uid='{$user_id}'";
        if ($this->getUniResult($chk, $user_name) <= 0) {   // 社員登録されているか
            if ($user_id < 777001 || $user_id > 777999) {   // 臨時(応援)でなければ
                $_SESSION['s_sysmsg'] = "社員番号：{$user_id} は登録されていません！";
            } else {
                return ('応援者' . substr($user_id, 3, 3) );
            }
        } else {
            return $user_name;
        }
        return false;
    }
    ////////// 着手時間又は完了時間が既存のデータと重複するかチェック(但し同時作業分を除く)
    protected function DuplicateCheck($request, $str_time, $end_time)
    {
        // 着手時間が重複するもの
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (str_time>'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (str_time<'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(    -- 除く同時作業分
                (str_time='{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (plan_no != '{$request->get('plan_no')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} さんは 計画番号：{$duplicate} の着手と重複しています！";
            return false;
        }
        // 完了時間が重複するもの
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (end_time>'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (end_time<'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(
                (str_time='{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (plan_no != '{$request->get('plan_no')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} さんは 計画番号：{$duplicate} の完了と重複しています！";
            return false;
        }
        // 着手も完了時間も重複するもの
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (str_time<'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (end_time>'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(
                (str_time='{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (plan_no != '{$request->get('plan_no')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} さんは 計画番号：{$duplicate} と重複しています！";
            return false;
        }
        return true;
    }
    
    ////////// 着手時間又は完了時間が既存のデータと重複するかチェック(但し同時作業分と自分自身も除く)
    protected function DuplicateCheckEdit($request, $str_time, $end_time, $pre_str_time, $pre_end_time)
    {
        // 着手時間が重複するもの
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (str_time>'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (str_time<'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(    -- 除く同時作業分
                (str_time='{$pre_str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$pre_end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} さんは 計画番号：{$duplicate} の着手と重複しています！";
            return false;
        }
        // 完了時間が重複するもの
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (end_time>'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (end_time<'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(
                (str_time='{$pre_str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$pre_end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} さんは 計画番号：{$duplicate} の完了と重複しています！";
            return false;
        }
        // 着手も完了時間も重複するもの
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (str_time<'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (end_time>'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(
                (str_time='{$pre_str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$pre_end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} さんは 計画番号：{$duplicate} と重複しています！";
            return false;
        }
        return true;
    }
    
    ////////// 同時作業の計画と計画番号が重複するかチェック(自分自身も除く)
    protected function DuplicatePlanNoCheck($request, $session)
    {
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (str_time>'{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
            AND
            (end_time<'{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
            AND
            serial_no != {$request->get('serial_no')}
            AND
            plan_no = '{$request->get('plan_no')}'
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "同時作業計画と計画番号：{$request->get('plan_no')} が重複しています。";
            return false;
        }
        return true;
    }
    
    ////////// 修正後に変更前の同時作業 計画数の合計を再計算し更新する
    protected function pre_plan_pcsUpdate($request, $session)
    {
        // 同時 計画番号の合計計画数(plan_pcs)を取得 同一作業者の場合を同時組立計画分と見なす
        $query = "
            SELECT sum(plan_pcs) FROM assembly_process_time
            WHERE
                (str_time='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
        ";
        $plan_all_pcs = 0;     // 初期化
        $this->getUniResult($query, $plan_all_pcs);
        // ここで同時組立着手分の他の計画があればplan_all_pcsをUPDATEする
        $query = "
            SELECT serial_no FROM assembly_process_time
            WHERE
                (str_time='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
        ";
        if ($this->getUniResult($query, $tmp) > 0) {    // 1件でもあれば UPDATE する
            $update_sql = "
            UPDATE assembly_process_time SET plan_all_pcs={$plan_all_pcs}
            WHERE
                (str_time='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
            ";
            if (!$this->execute_Update($update_sql)) {
                $_SESSION['s_sysmsg'] = '同時組立計画分の合計計画数の変更が出来ませんでした！ 管理担当者へ連絡して下さい。';
            }
        }
        return $plan_all_pcs;
    }
    
    ////////// 同時作業計画のassy_timeの更新 (1作業者の同時作業 計画)
    protected function pre_assyTimeUpdate($request, $session)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // 休み時間を除いた合計工数(分)を取得
        $sum_time = $this->getSumTime($session->get_local('pre_str_time'), $session->get_local('pre_end_time'));
        $query = "
            SELECT serial_no, plan_pcs, plan_all_pcs FROM assembly_process_time
            WHERE
                (str_time='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        for ($i=0; $i<$rows; $i++) {
            $serial_no    = $res[$i][0];
            $plan_pcs     = $res[$i][1];
            $plan_all_pcs = $res[$i][2];
            $assy_time = round(($plan_pcs / $plan_all_pcs) * $sum_time, 3);    // １計画分の工数(分)を算出
            $update_sql = "
                UPDATE assembly_process_time SET
                assy_time={$assy_time}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            if (!$this->execute_Update($update_sql)) {
                $_SESSION['s_sysmsg'] = '同時組立計画分の工数の変更が出来ませんでした！ 管理担当者へ連絡して下さい。';
            }
        }
        return true;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 組立実績の追加実行
    private function ApendExecute($request)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $insert_sql = "
            INSERT INTO assembly_process_time
            (group_no, plan_no, user_id, str_time, end_time, plan_all_pcs, plan_pcs, assy_time, last_date, last_host)
            values
            ({$request->get('showGroup')}, '{$request->get('plan_no')}', '{$request->get('user_id')}', '{$request->get('str_time')}', '{$request->get('end_time')}'
            , {$request->get('plan')}, {$request->get('plan')}, {$request->get('assy_time')}, '{$last_date}', '{$last_host}')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// 組立実績の削除実行
    private function DeleteExecute($request)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // 同時作業計画分のために必要なデータを先に残す
        $query = "
            SELECT str_time, end_time, user_id FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $request->add('str_time', $res[0][0]);
            $request->add('end_time', $res[0][1]);
            $request->add('user_id',  $res[0][2]);
        } else {
            return false;
        }
        $save_sql = "
            SELECT * FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        $delete_sql = "
            DELETE FROM assembly_process_time
            WHERE serial_no={$request->get('serial_no')}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// 組立実績の修正実行
    private function EditExecute($request, $session)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $save_sql = "
            SELECT * FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        // 最初に単独で変更を実行
        $update_sql = "
            UPDATE assembly_process_time SET
                plan_no='{$request->get('plan_no')}', user_id='{$request->get('user_id')}',
                str_time='{$request->get('str_time')}', end_time='{$request->get('end_time')}',
                plan_all_pcs={$request->get('plan')}, plan_pcs={$request->get('plan')},
                assy_time={$request->get('assy_time')}, last_date='{$last_date}', last_host='{$last_host}'
            WHERE
                serial_no={$request->get('serial_no')}
        ";
        if (!$this->execute_Update($update_sql, $save_sql)) {
            return false;
        }
        // 同時作業計画が存在するかチェック
        $query = "
            SELECT serial_no FROM assembly_process_time
            WHERE
                (str_time<='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time>='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
        ";
        $rows = $this->getResult2($query, $res);
        // 社員番号をチェックし同時作業計画の修正を分岐させる
        if ($session->get_local('pre_user_id') == $request->get('user_id') && $rows > 0) {
            // 社員番号が同じなので同時作業計画のstr_timeとend_timeを変更
            $update_sql = "
                UPDATE assembly_process_time SET
                    str_time='{$request->get('str_time')}', end_time='{$request->get('end_time')}',
                    last_date='{$last_date}', last_host='{$last_host}'
                WHERE
                (str_time<='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time>='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
            ";
            return $this->execute_Update($update_sql, $save_sql);
        } else {
            // 社員番号が変わったため単独と見なして同時作業計画の日時変更はしない
            // 又は同時作業計画が存在しない(最初はトランザクションで行っていたが同時作業計画が存在しない場合に単独の更新が出来なくなるため個別にした)
        }
        return true;
    }
    
} // Class AssemblyProcessShow_Model End

?>
