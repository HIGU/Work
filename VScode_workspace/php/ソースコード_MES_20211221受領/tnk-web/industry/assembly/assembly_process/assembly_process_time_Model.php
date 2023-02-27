<?php
//////////////////////////////////////////////////////////////////////////////
// 組立工程の作業工数 (着手・完了時間) 集計用       MVC Model 部            //
// Copyright (C) 2005-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/30 Created   assembly_process_time_Model.php                     //
// 2005/11/21 入力許可 部門コードにリニア検査(537)と資材(534)を追加         //
//            getViewEndList()メソッド division by zeroの対応(planが0の場合)//
// 2005/11/30 userAdd()メソッドでuserチェックをworkのみからtimeもチェック   //
//            planAdd()メソッドに@マークの計画数を修正する機能と日程計画の  //
//              計画残のチェック機能を追加                                  //
//            planAdd_execute()メソッドの計画数を計画残数へ変更             //
//            getViewStartList(),getViewStartList(),getViewPlanListNotPage()//
//              に計画残を追加し計画数を最後へ移動(ダブルクリック照会用)    //
// 2005/12/01 checkWorkUser()メソッドを追加 10分経過したら入力終了ボタンの  //
//            押し忘れと判断し自動削除する。getViewUserListNotPage()から呼出//
// 2005/12/07 getViewEndList()メソッドに取消有効・無効のSQL文を追加         //
// 2005/12/13 メッセージの訂正 出庫着手 → 組立着手                         //
// 2005/12/15 編集権限に生産部の500を追加(副部長の対応)その他の人もいるが？ //
// 2006/01/19 範囲を 60日から 62日へ変更 他プログラムと統一するため         //
// 2006/04/06 組織変更による assemblyAuthUser() メソッドのメンテナンス      //
// 2006/05/18 追加した計画番号の保存propertyを追加し、計画の登録工数の出力  //
//            メソッド outViewKousu() を追加                                //
// 2007/01/09 認証用メソッドを共通権限マスター対応へ変更。getCheckAuthority //
// 2007/06/17 &$result → $result へ(php5対応) 完了ボタンで自分のworkを削除 //
//            assyEnd()assyEndAll()メソッドにuserDelete_execute()を追加     //
//            更にcheckWorkUser()メソッドの10分→5分へ変更                  //
//            組立完了予定日時の追加のためoutViewKousu()メソッドを変更      //
// 2013/01/29 製品名の頭文字がDPEのものを液体ポンプ(バイモル)で集計するよう //
//            に変更                                                   大谷 //
//            バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
// 2013/01/31 リニアのみのDPE抜出SQLを訂正                             大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../ComTableMntClass.php');    // TNK 全共通 テーブルメンテ&ページ制御Class


/*****************************************************************************************
*  組立 手作業 工数 (着手・完了)時間 集計用 MVCのModel部の base class 基底クラスの定義   *
*****************************************************************************************/
class AssemblyProcessTime_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $addPlanNo = '';                    // 計画の追加に成功した計画番号
    private $authDiv = 17;                      // このビジネスロジックの権限区分
    
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
            if ($request->get('group_no') != '') {
                $where .= "AND group_no = " . $request->get('group_no');
            }
            $sql_sum = "
                SELECT count(*) FROM assembly_process_time $where
            ";
            break;
        case 'apend':
            ///// 計画番号の登録時のみ
            if ($request->get('group_no') != '') {
                $where = "WHERE end_time='19700101 000000' AND " . "group_no = " . $request->get('group_no');
            } else {
                $where = "WHERE end_time='19700101 000000'";
            }
            $sql_sum = "
                SELECT count(*) FROM assembly_process_time $where
            ";
            break;
        case 'group':
            $where = '';
            $sql_sum = "
                SELECT count(*) FROM assembly_process_group
            ";
            break;
        case 'StartList':
        default:
            if ($request->get('group_no') != '') {
                $where = "WHERE end_time='19700101 000000' AND " . "group_no = " . $request->get('group_no');
            } else {
                $where = "WHERE end_time='19700101 000000'";
            }
            $sql_sum = "
                SELECT count(*) FROM assembly_process_time $where
            ";
        }
        $log_file = 'assembly_process_time.log';
        $this->where = $where;
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, $log_file);
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
            $_SESSION['s_sysmsg'] = '組立指示メニューの編集権限がありません！';
        }
        return false;
    }
    
    ////////// 組立着手の作業者 登録 (work追加)
    public function userAdd($group_no, $user_id)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName($user_id)) ) {
            return false;
        }
        $chk_sql = "select plan_no from assembly_process_work where group_no={$group_no} and user_id='{$user_id}' limit 1";
        if ($this->getUniResult($chk_sql, $check) > 0) {    // group_no user_id で(重複)のチェック
            $_SESSION['s_sysmsg'] = "{$userName} さんは既に入力されています。";
        } else {
            $chk_sql = "select plan_no from assembly_process_time where end_time='1970-01-01 00:00:00' AND group_no={$group_no} AND user_id='{$user_id}' limit 1";
            if ($this->getUniResult($chk_sql, $check) > 0) {    // end_time group_no user_id で(重複)のチェック
                $_SESSION['s_sysmsg'] = "{$userName} さんは既に 計画番号 {$check} で入力されています。";
            } else {
                $response = $this->userAdd_execute($group_no, $user_id);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "{$userName} さんは登録できませんでした！";
                }
            }
        }
        return false;
    }
    
    ////////// 組立着手の作業者 登録の取消 (work削除)
    public function userDelete($group_no, $user_id)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName($user_id)) ) {
            return false;
        }
        $chk_sql = "select plan_no from assembly_process_work where group_no={$group_no} and user_id='{$user_id}'";
        if ($this->getUniResult($chk_sql, $check) < 1) {     // 完了入力前(着手指示分)が登録されているか？
            $_SESSION['s_sysmsg'] = "{$user_id} さんは他の人に変更されました！";
        } else {
            $response = $this->userDelete_execute($group_no, $user_id);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "{$userName} さんの組立着手の取消ができませんでした！";
            }
        }
        return false;
    }
    
    ////////// 組立着手の計画番号 登録 (workのuser全てを計画番号を入れてtimeに追加)
    public function planAdd($group_no, $plan_no)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        ///// 計画番号の適正チェックと計画残があるかチェック
        $chk = "select plan - cut_plan - kansei from assembly_schedule where plan_no='{$plan_no}'";
        if ($this->getUniResult($chk, $check) <= 0) {   // 計画番号が登録されているか
            $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} が見つかりません！";
            return false;
        } else {
            if (substr($plan_no, 0, 1) == '@' && $check <= 0) {
                $sei_no = substr($plan_no, 1, 7);
                $query = "SELECT order_q, utikiri, nyuko FROM order_plan WHERE sei_no={$sei_no} limit 1";
                $order = array();
                if ($this->getResult2($query, $order) > 0) {   // 製造番号で発注数をチェック
                    $order_q = $order[0][0]; $utikiri = $order[0][1]; $nyuko = $order[0][2];
                    $update_sql = "UPDATE assembly_schedule SET plan={$order_q}, cut_plan={$utikiri}, kansei={$nyuko} WHERE plan_no='{$plan_no}'";
                    $this->execute_Update($update_sql);
                    if ( ($order_q - $utikiri - $nyuko) <= 0 ) {
                        $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} は計画残がありません！ 管理担当者へ連絡して下さい。";
                        return false;
                    }
                } else {
                    $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} が見つかりません！ 管理担当者へ連絡して下さい。";
                    return false;
                }
            } elseif ($check <= 0) {
                $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} は計画残がありません！";
                return false;
            }
        }
        $chk_sql1 = "select user_id from assembly_process_work where group_no={$group_no} limit 1";
        $res = array();
        if (($rows=$this->getResult2($chk_sql1, $res)) <= 0) {    // user_id plan_noで未完了あり(重複)のチェック
            $_SESSION['s_sysmsg'] = "作業者の登録がありません！ 先に作業者の登録をして下さい。";
            return false;
        }
        for ($i=0; $i<$rows; $i++) {
            $chk_sql2 = "
                select trim(name) from assembly_process_time LEFT OUTER JOIN user_detailes ON (user_id=uid)
                where end_time='19700101 000000' and group_no={$group_no} and user_id='{$res[$i][0]}' and plan_no='{$plan_no}'
            ";
            if ($this->getUniResult($chk_sql2, $name) > 0) {    // user_id plan_noで未完了あり(重複)のチェック
                $_SESSION['s_sysmsg'] = "{$name}さんは同計画[{$plan_no}]で既に着手しています。作業者の登録を取消 又は 完了して下さい！";
                return false;
            }
        }
        $response = $this->planAdd_execute($group_no, $plan_no);
        if ($response) {
            $this->addPlanNo = $plan_no;    // 2006/05/18 追加
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '登録できませんでした。';
        }
        return false;
    }
    
    ////////// 組立着手の計画番号の取消 (plan_noをNULLへUPDATE) 組立着手の計画番号(ユーザーは複数あり) 取消
    public function planDelete($serial_no, $plan_no)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $chk_sql = "
            select group_no, user_id
            from assembly_process_time where serial_no={$serial_no} and end_time='19700101 000000'
        ";
        $res = array();
        if ($this->getResult2($chk_sql, $res) < 1) {     // 完了入力前(着手指示分)が登録されているか？
            $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} は他の人に変更されました！";
        } else {
            $group_no = $res[0][0];
            $user_id  = $res[0][1];
            $response = $this->planDelete_execute($serial_no, $group_no, $user_id);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '計画番号：{$plan_no} の取消ができませんでした。';
            }
        }
        return false;
    }
    
    ////////// 組立着手の入力終了処理 (指定のグループ番号の work テーブルレコードを削除する)
    public function apendEnd($group_no)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT user_id FROM assembly_process_work WHERE group_no={$group_no}
        ";
        if ($this->getUniResult($query, $chk) > 0) {  // workにデータがあるか？
            $save_sql   = "
                SELECT * FROM assembly_process_work WHERE group_no={$group_no}
            ";
            $delete_sql = "
                DELETE FROM assembly_process_work WHERE group_no={$group_no}
            ";
            $response = $this->execute_Delete($delete_sql, $save_sql);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '入力終了処理ができませんでした。';
            }
        }
        return false;
    }
    
    ////////// 組立完了の入力 (個別) serial_noで条件を抽出し 1作業者の全ての計画番号を完了
    public function assyEnd($serial_no, $plan_no)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT str_time, group_no, user_id
            FROM assembly_process_time WHERE serial_no={$serial_no} AND end_time='19700101 000000'
        ";
        $res = array(); // 初期化
        if ($this->getResult2($query, $res) > 0) {  // 完了入力前のserial_noが登録されているか？
            $str_time = $res[0][0];
            $group_no = $res[0][1];
            $user_id  = $res[0][2];
            $response = $this->assyEnd_execute($str_time, $group_no, $user_id);
            if ($response) {
                $this->userDelete_execute($group_no, $user_id); // 2007/06/17 着手作業者一覧に残っていれば削除
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '完了入力ができませんでした。';
            }
        } else {
            $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} は他の人に変更されました！";
        }
        return false;
    }
    
    ////////// 組立完了の入力 (一括) serial_noからstr_time,group_noとplan_noを取得して一括完了を行う
    ////////// 2007/06/17 現在は使用していない
    public function assyEndAll($serial_no, $plan_no)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT str_time, group_no, user_id
            FROM assembly_process_time WHERE serial_no={$serial_no} AND end_time='19700101 000000'
        ";
        $res = array(); // 初期化
        if ($this->getResult2($query, $res) > 0) {  // 完了入力前のserial_noが登録されているか？
            $str_time = $res[0][0];
            $group_no = $res[0][1];
            $user_id  = $res[0][2];
            $response = $this->assyEndAll_execute($str_time, $group_no);
            if ($response) {
                $this->userDelete_execute($group_no, $user_id); // 2007/06/17 着手作業者一覧に残っていれば削除
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '完了入力ができませんでした。';
            }
        } else {
            $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} は他の人に変更されました！";
        }
        return false;
    }
    
    ////////// 組立完了の取消 (作業者毎) serial_noからend_time,group_no,user_idを取得して作業者毎の一括取消を行う
    public function endCancel($serial_no, $plan_no)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            select end_time, group_no, user_id
            from assembly_process_time where serial_no={$serial_no}
        ";
        $res = array(); // 初期化
        if ($this->getResult2($query, $res) > 0) {  // 完了入力済のserial_noが登録されているか？
            $end_time = $res[0][0];
            $group_no = $res[0][1];
            $user_id  = $res[0][2];
            $response = $this->endCancel_execute($end_time, $group_no, $user_id);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '完了の取消ができませんでした。';
            }
        } else {
            $_SESSION['s_sysmsg'] = "計画番号：{$plan_no} は他の人に変更されました！";
        }
        return false;
    }
    
    ////////// 組立グループ(作業区) 登録・変更
    public function groupEdit($group_no, $group_name, $div, $product, $active)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT group_no, group_name, product, div, active FROM assembly_process_group WHERE group_no={$group_no}
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // 組立グループ(作業区) 登録
            $response = $this->groupInsert($group_no, $group_name, $div, $product, $active);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を登録しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = 'グループの登録が出来ませんでした！';
            }
        } else {
            // データが変更されているかチェック
            if ($group_no == $res[0][0] && $group_name == $res[0][1] && $product == $res[0][2] && $div == $res[0][3]) {
                return true;
            }
            // 組立グループ(作業区) 変更
            $response = $this->groupUpdate($group_no, $group_name, $div, $product);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を変更しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = 'グループの変更が出来ませんでした！';
            }
        }
        return false;
    }
    
    ////////// 組立グループ(作業区)の 削除
    public function groupOmit($group_no, $group_name)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT group_no, group_name FROM assembly_process_group WHERE group_no={$group_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} の削除対象データがありません！";
        } else {
            $response = $this->groupDelete($group_no);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を削除しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を削除出来ませんでした！";
            }
        }
        return false;
    }
    
    ////////// 組立グループ(作業区)の 有効・無効
    public function groupActive($group_no, $group_name)
    {
        ///// Operator の authentication check & user_idの適正チェック
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT active FROM assembly_process_group WHERE group_no={$group_no}
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} の対象データがありません！";
        } else {
            // ここに last_date last_host の登録処理を入れる
            // regdate=自動登録
            $last_date = date('Y-m-d H:i:s');
            $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
            if ($active == 't') {
                $active = 'FALSE';
            } else {
                $active = 'TRUE';
            }
            $update_sql = "
                UPDATE assembly_process_group SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE group_no={$group_no}
            "; 
            return $this->execute_Update($update_sql);
        }
        return false;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    組立着手 一覧表
    public function getViewStartList($result)
    {
        ///// 以下で AS assyuser は AS user でエラーになる(予約語)ため冗長化した
        $query = "SELECT plan_no        AS 計画番号
                        ,parts_no       AS 製品番号
                        ,substr(midsc, 1, 20)
                                        AS 製品名
                        ,plan_pcs       AS 計画残数
                        ,user_id        AS 社員番号
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '応援者' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS 作業者
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS 開始日時
                        ,serial_no      AS 連番
                        ,plan           AS 計画数           -- 08
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
    public function getViewEndList($result)
    {
        $query = "SELECT plan_no        AS 計画番号
                        ,parts_no       AS 製品番号
                        ,substr(midsc, 1, 20)
                                        AS 製品名
                        ,plan_pcs       AS 計画残数
                        ,user_id        AS 社員番号
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '応援者' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS 作業者
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS 開始日時
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS 完了日時
                        ,assy_time      AS 合計工数
                        -----------------------------リストは上記まで
                        ,serial_no      AS 連番         --  9
                        ,to_char(str_time, 'YY/MM/DD HH24:MI:SS')
                                        AS 開始詳細     -- 10
                        ,to_char(end_time, 'YY/MM/DD HH24:MI:SS')
                                        AS 完了詳細     -- 11
                        ,CASE
                            WHEN plan_pcs > 0
                            THEN Uround(assy_time / plan_pcs, 3)
                            ELSE assy_time
                         END            AS 工数         -- 12
                        ,plan           AS 計画数       -- 13
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
    
    ///// List部    組立着手 作業者 入力後の確認用 一覧表 (ページ制御なし)
    public function getViewUserListNotPage($group_no, $result)
    {
        // 入力終了ボタンの押し忘れチェックメソッドの呼出
        $this->checkWorkUser();
        if ($group_no != '') $where = "WHERE group_no={$group_no}"; else $where = '';
        $query = "
            SELECT user_id      AS 社員番号
                ,CASE
                    WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                    THEN '応援者' || substr(user_id, 4, 3)
                    ELSE trim(assyuser.name)
                 END            AS 作業者
                ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                AS 開始日時
                ,group_no       AS グループ番号
            FROM
                assembly_process_work
            LEFT OUTER JOIN
                user_detailes   AS assyuser ON (user_id=uid)
            {$where}
            ORDER BY
                str_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
            // $this->log_writer($query);   // debug用
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List部    組立着手 計画番号 入力後の確認用 一覧表 (ページ制御なし)
    public function getViewPlanListNotPage($result)
    {
        $query = "
            SELECT plan_no      AS 計画番号
                ,parts_no       AS 製品番号
                ,substr(midsc, 1, 20)
                                AS 製品名
                ,plan_pcs       AS 計画残数
                ,user_id        AS 社員番号
                ,CASE
                    WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                    THEN '応援者' || substr(user_id, 4, 3)
                    ELSE trim(assyuser.name)
                 END            AS 作業者
                ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                AS 開始日時
                ,serial_no      AS 連番
                ,plan           AS 計画数           -- 08
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
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
            // $this->log_writer($query);   // debug用
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List部    組立グループ(作業区) 登録内容 一覧表
    public function getViewGroupList($result)
    {
        $query = "SELECT group_no           AS グループ番号
                        ,group_name         AS グループ名
                        ,CASE
                            WHEN div = 'C' THEN 'カプラ'
                            WHEN div = 'L' THEN 'リニア'
                            ELSE '未登録'
                         END                AS 事業部
                        ,CASE
                            WHEN product = 'C' THEN 'カプラ標準'
                            WHEN product = 'S' THEN 'カプラ特注'
                            WHEN product = 'L' THEN 'リニア製品'
                            WHEN product = 'B' THEN '液体ポンプ'
                            ELSE '未登録'
                         END                AS 製品グループ
                        ,to_char(last_date, 'YY/MM/DD HH24:MI')
                                            AS 変更日時
                        ,CASE
                            WHEN active THEN '有効'
                            ELSE '無効'
                         END                AS 有効無効
                        ,div
                        ,product
                    FROM
                        assembly_process_group
                    ORDER BY
                        group_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
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
    public function getViewDataEdit($serial_no, $result)
    {
        $query = "SELECT plan_no        AS 計画番号
                        ,parts_no       AS 製品番号
                        ,substr(midsc, 1, 20)
                                        AS 製品名
                        ,plan           AS 計画数
                        ,user_id        AS 社員番号
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '応援者' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS 作業者
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS 開始日時
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS 完了日時
                        ,serial_no      AS 連番
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
            $result->add_once('plan_no',    $res[0][1]);
            $result->add_once('assy_no',    $res[0][2]);
            $result->add_once('assy_name',  $res[0][3]);
            $result->add_once('user_id',    $res[0][4]);
            $result->add_once('user_name',  $res[0][5]);
            $result->add_once('str_time',   $res[0][6]);
            $result->add_once('end_time',   $res[0][7]);
            $result->add_once('serial_no',  $res[0][7]);
        }
        return $rows;
    }
    
    ///// 着手した計画番号の工数照会
    public function outViewKousu($menu)
    {
        if ($this->addPlanNo == '') return '';
        ///// 現在までの使用工数を取得
        $used_time = Uround($this->getUsedAssyTime($this->addPlanNo), 3);
        ///// 現時点での作業者数を取得
        $worker_count = $this->getWorkerCount($this->addPlanNo);
        $script = '';
        // $script .= "<script type='text/javascript'>\n";
        $script = "AssemblyProcessTime.win_open(\"{$menu->out_action('登録工数照会')}?noMenu=yes&regOnly=yes&targetPlanNo={$this->addPlanNo}&usedTime={$used_time}&workerCount={$worker_count}\", 900, 500);\n";
        // $script .= "</script>\n";
        //$script = "AssemblyProcessTime.win_openc(\"{$menu->out_action('不適合報告書')}?noMenu=yes&regOnly=yes&targetPlanNo={$this->addPlanNo}&usedTime={$used_time}&workerCount={$worker_count}\", 900, 500);\n";
        return $script;
    }
    
    ///// 着手した計画番号の製品に対する不適合報告書照会
    public function outViewClame($menu)
    {
        if ($this->addPlanNo == '') return '';
        ///// 現在までの使用工数を取得
        $used_time = Uround($this->getUsedAssyTime($this->addPlanNo), 3);
        ///// 現時点での作業者数を取得
        $worker_count = $this->getWorkerCount($this->addPlanNo);
        $script = '';
        // $script .= "<script type='text/javascript'>\n";
        $script = "AssemblyProcessTime.win_openc(\"{$menu->out_action('不適合報告書')}?noMenu=yes&regOnly=yes&targetPlanNo={$this->addPlanNo}&usedTime={$used_time}&workerCount={$worker_count}\", 900, 500);\n";
        // $script .= "</script>\n";
        return $script;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// 組立指示メニューの編集権限チェックメソッド(共用メソッド)
    protected function assemblyAuthUser()
    {
        if ($this->getCheckAuthority($this->authDiv)) {
            return true;
        } else {
            return false;
        }
        
        ///// 以下は現在使用していない
        $LoginUser = $_SESSION['User_ID'];
        $query = "select act_id from cd_table where uid='$LoginUser'";
        if (getUniResult($query, $sid) > 0) {
            switch ($sid) {             // 社員の所属する部門コードでチェック
            case 500:                   // 生産部 (2005/12/15追加)
            case 176:
            case 522:
            case 523:
            case 525:                   // 特注
            case 514:                   // カプラ資材
                return true;            // カプラ組立(資材を除くを解除2006/04/06)
            case 551:
            case 175:
            case 560:
            case 537:                   // リニア検査
            case 534:                   // リニア資材
                return true;            // リニア組立(資材・検査を除くを解除2005/11/21)
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
        return $res;
    }
    ////////// 同時 計画番号の計画数の合計を再計算し更新する
    protected function plan_pcsUpdate($group_no, $user_id)
    {
        // 同時 計画番号の合計計画数(plan_pcs)を取得 同一作業者の場合を同時組立計画分と見なす
        $query = "
            SELECT sum(plan_pcs) FROM assembly_process_time
            WHERE end_time='19700101 000000' and group_no={$group_no} and user_id='{$user_id}'
        ";
        $plan_all_pcs = 0;     // 初期化
        $this->getUniResult($query, $plan_all_pcs);
        // ここで同時組立着手分の他の計画があればplan_all_pcsをUPDATEする
        $query = "
            SELECT serial_no FROM assembly_process_time WHERE end_time='19700101 000000' and group_no={$group_no} and user_id='{$user_id}'
        ";
        if ($this->getUniResult($query, $tmp) > 0) {    // 1件でもあれば UPDATE する
            $query = "
                UPDATE assembly_process_time SET plan_all_pcs={$plan_all_pcs}
                WHERE end_time='19700101 000000' and group_no={$group_no} and user_id='{$user_id}'
            ";
            if (!$this->execute_Update($query)) {
                $_SESSION['s_sysmsg'] = '同時組立計画分の合計計画数の変更が出来ませんでした！ 管理担当者へ連絡して下さい。';
            }
        }
        return $plan_all_pcs;
    }
    ////////// 組立 作業者のuser_idの適正をチェックしメッセージ＋結果(氏名=OK,false=NG)を返す
    protected function checkUserID($user_id)
    {
        ///// user_idの適正チェック
        $chk = "SELECT trim(name) FROM user_detailes WHERE uid='{$user_id}'";
        if ($this->getUniResult($chk, $user_name) <= 0) {   // 社員登録されているか
            if ($user_id < 777001 || $user_id > 777999) {   // 臨時(応援)でなければ
                $_SESSION['s_sysmsg'] = "社員番号：{$user_id} は不正です！";
            } else {
                return ('応援者' . substr($user_id, 3, 3) );
            }
        } else {
            return $user_name;
        }
        return false;
    }
    
    ////////// 組立開始指示の入力終了ボタンの押し忘れチェック ＆ ユーザークリア
    protected function checkWorkUser()
    {
        // 10分以上経過しているユーザーがあるか？
        $query = "
            SELECT * FROM assembly_process_work WHERE str_time <= (CURRENT_TIMESTAMP - interval '5 minute')
        ";
        if ($this->getResult2($query, $res) > 0) {
            $delete_sql = "
                DELETE FROM assembly_process_work WHERE str_time <= (CURRENT_TIMESTAMP - interval '5 minute')
            ";
            // $save_sql → $query はオプションなので指定しなくても良い
            $result_flg = $this->execute_Delete($delete_sql, $query);
        }
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 組立開始 Userの入力 (workへ追加)
    private function userAdd_execute($group_no, $user_id)
    {
        // 同時組立計画の初回計画か？
        $query = "
            SELECT str_time FROM assembly_process_work
            WHERE group_no={$group_no} and user_id='{$user_id}'
            LIMIT 1
        ";
        if ($this->getUniResult($query, $str_time) <= 0) {
            // 初回計画のため時間を設定する
            $str_time = date('Y-m-d H:i:s');
        }
        
        // 作業者の登録実行 (workへ登録)
        $insert_qry = "
            insert into assembly_process_work
            (group_no, user_id, str_time)
            values
            ($group_no, '$user_id', '$str_time')
        ";
        $result_flg = $this->execute_Insert($insert_qry);
        return $result_flg;
    }
    
    ////////// 組立着手の作業者 取消 (work完全削除)
    private function userDelete_execute($group_no, $user_id)
    {
        // 保存用のSQL文を設定
        $save_sql   = "select * from assembly_process_work where group_no={$group_no} and user_id='{$user_id}'";
        $delete_sql = "delete from assembly_process_work where group_no={$group_no} and user_id='{$user_id}'";
        // $save_sqlはオプションなので指定しなくても良い
        $result_flg = $this->execute_Delete($delete_sql, $save_sql);
        return $result_flg;
    }
    
    ////////// 組立開始 計画番号の入力 (追加)
    private function planAdd_execute($group_no, $plan_no)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // この計画(plan_no)の計画数(plan)→計画残数 を取得
        $query = "
            SELECT plan - cut_plan - kansei FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $plan = 0;     // 初期化
        $this->getUniResult($query, $plan);
        
        // 開始対象の作業者を取得
        $query = "
            SELECT user_id, str_time FROM assembly_process_work WHERE group_no={$group_no}
        ";
        $res = array(); // 初期化
        $rows = $this->getResult2($query, $res);
        
        // 登録実行 (この時点ではplan_all_pcsはparts_pcsと同じにする)
        $end_time = '19700101 000000';
        for ($i=0; $i<$rows; $i++) {
            $insert_qry = "
                insert into assembly_process_time
                (group_no, plan_no, user_id, str_time, end_time, plan_all_pcs, plan_pcs, last_date, last_host)
                values
                ($group_no, '$plan_no', '{$res[$i][0]}', '{$res[$i][1]}', '$end_time', $plan, $plan, '$last_date', '$last_host')
            ";
            $result_flg = $this->execute_Insert($insert_qry);
            
            // 同時組立分のplan_all_pcsを更新
            if ($result_flg) {
                $this->plan_pcsUpdate($group_no, $res[$i][0]);
            }
        }
        return $result_flg;
    }
    
    ////////// 組立着手の計画番号 取消 (完全削除) 個別削除
    private function planDelete_execute($serial_no, $group_no, $user_id)
    {
        // 保存用のSQL文を設定 user_idは無くても良いがdebug時に役に立つ
        $save_sql   = "select * from assembly_process_time where serial_no={$serial_no} and user_id='{$user_id}'";
        $delete_sql = "delete from assembly_process_time where serial_no={$serial_no} and user_id='{$user_id}'";
        // $save_sqlはオプションなので指定しなくても良い
        $result_flg = $this->execute_Delete($delete_sql, $save_sql);
        
        // 同時出庫分のplan_pcsを更新
        if ($result_flg) {
            $this->plan_pcsUpdate($group_no, $user_id);
        }
        return $result_flg;
    }
    
    ////////// 現時点での指定 計画番号での作業者数を取得
    private function getWorkerCount($planNo)
    {
        $query = "
            SELECT assy_time, plan_pcs, plan_all_pcs, str_time FROM assembly_process_time
            WHERE plan_no='{$planNo}' AND assy_time IS NULL AND end_time='19700101 000000'
        ";
        return $this->getResult2($query, $res);
    }
    
    ////////// 現在までの指定 計画番号での使用工数を取得 (組立完了時のロジックと同じ)
    private function getUsedAssyTime($planNo)
    {
        $end_time = date('Y-m-d H:i:s');    // 現在の日時をセット
        $query = "
            SELECT assy_time, plan_pcs, plan_all_pcs, str_time FROM assembly_process_time
            WHERE plan_no='{$planNo}'
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return 0;
        $sum_assy_time = 0;
        for ($i=0; $i<$rows; $i++) {
            $assy_time    = $res[$i][0];
            $plan_pcs     = $res[$i][1];
            $plan_all_pcs = $res[$i][2];
            $str_time     = $res[$i][3];
            if (!$assy_time) {
                $sum_time = $this->getSumTime($str_time, $end_time);    // 休み時間を除いた合計工数(分)を取得
                $assy_time = round(($plan_pcs / $plan_all_pcs) * $sum_time, 3);    // １計画分の工数(分)を算出
            }
            $sum_assy_time += $assy_time;
        }
        return $sum_assy_time;
    }
    
    ////////// 組立完了の入力 (変更) 個別(1作業者の全ての計画番号を完了)
    private function assyEnd_execute($str_time, $group_no, $user_id)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $end_time = date('Y-m-d H:i:s');
        $sum_time = $this->getSumTime($str_time, $end_time);    // 休み時間を除いた合計工数(分)を取得
        $query = "
            SELECT serial_no, plan_pcs, plan_all_pcs FROM assembly_process_time
            WHERE str_time='{$str_time}' AND group_no={$group_no} AND user_id='{$user_id}'
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        for ($i=0; $i<$rows; $i++) {
            $serial_no    = $res[$i][0];
            $plan_pcs     = $res[$i][1];
            $plan_all_pcs = $res[$i][2];
            $assy_time = round(($plan_pcs / $plan_all_pcs) * $sum_time, 3);    // １計画分の工数(分)を算出
            $update_sql = "
                UPDATE assembly_process_time SET
                end_time='{$end_time}', assy_time={$assy_time}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            $this->execute_Update($update_sql);
        }
        return true;
    }
    
    ////////// 組立完了の入力 (変更) 一括完了(全ての作業者の全ての計画番号を完了)
    private function assyEndAll_execute($str_time, $group_no)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $end_time = date('Y-m-d H:i:s');
        $sum_time = $this->getSumTime($str_time, $end_time);    // 休み時間を除いた合計工数(分)を取得
        $query = "
            SELECT serial_no, plan_pcs, plan_all_pcs FROM assembly_process_time
            WHERE str_time='{$str_time}' AND group_no={$group_no} AND end_time='19700101 000000'
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        for ($i=0; $i<$rows; $i++) {
            $serial_no    = $res[$i][0];
            $plan_pcs     = $res[$i][1];
            $plan_all_pcs = $res[$i][2];
            $assy_time = round(($plan_pcs / $plan_all_pcs) * $sum_time, 3);    // １計画分の工数(分)を算出
            $update_sql = "
                UPDATE assembly_process_time SET
                end_time='{$end_time}', assy_time={$assy_time}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            $this->execute_Update($update_sql);
        }
        return true;
    }
    
    ////////// 組立完了の取消 (変更) 作業者毎の一括取消(作業者の全ての計画番号を取消)
    private function endCancel_execute($end_time, $group_no, $user_id)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // serial_noで指定された end_time, group_no, user_id で同時完了計画分を取得する
        $query = "
            SELECT serial_no FROM assembly_process_time
            WHERE end_time='{$end_time}' AND group_no={$group_no} AND user_id='{$user_id}'
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        for ($i=0; $i<$rows; $i++) {
            $serial_no = $res[$i][0];
            // 保存用のSQL文を設定
            $save_sql = "SELECT * FROM assembly_process_time WHERE serial_no={$res[$i][0]}";
            $update_sql = "
                UPDATE assembly_process_time SET
                end_time='19700101 000000', assy_time=NULL, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            // $save_sqlはオプションなので指定しなくても良い
            $this->execute_Update($update_sql, $save_sql);
        }
        return true;
    }
    
    ////////// 組立グループ(作業区)の登録 (実行部)
    private function groupInsert($group_no, $group_name, $product, $div, $active)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $insert_sql = "
            INSERT INTO assembly_process_group
            (group_no, group_name, product, div, active, last_date, last_host)
            VALUES
            ($group_no, '$group_name', '$product', '$div', '$active', '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// 組立グループ(作業区)の変更 (実行部)
    private function groupUpdate($group_no, $group_name, $div, $product)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $update_sql = "
            UPDATE assembly_process_group SET
            group_no={$group_no}, group_name='{$group_name}', product='{$product}', div='{$div}', last_date='{$last_date}', last_host='{$last_host}'
            WHERE group_no={$group_no}
        "; 
        return $this->execute_Update($update_sql);
    }
    
    ////////// 組立グループ(作業区)の削除 (実行部)
    private function groupDelete($group_no)
    {
        // 保存用のSQL文を設定
        $save_sql   = "SELECT * FROM assembly_process_group WHERE group_no={$group_no}";
        // 削除用SQL文を設定
        $delete_sql = "DELETE FROM assembly_process_group WHERE group_no={$group_no}";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// 組立完了の入力・取消 (変更) 以下はデバッグ用に残してある
    private function chg_execute($status, $serial_no, $user_id, $str_time)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // status を見て完了入力か取消かを判断する
        if ($status == 'end') {             // 完了入力
            $end_time = date('Y-m-d H:i:s');
            $sum_time = $this->getSumTime($str_time, $end_time);    // 休み時間を除いた合計工数(分)を取得
            $query = "
                SELECT serial_no, plan_pcs, parts_pcs FROM assembly_process_time
                WHERE str_time='{$str_time}' AND user_id='{$user_id}'
            ";
            if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
            // debug $_SESSION['sum_time'] = $sum_time;
            for ($i=0; $i<$rows; $i++) {
                $serial_no = $res[$i][0];
                $plan_pcs  = $res[$i][1];
                $parts_pcs = $res[$i][2];
                // debug $_SESSION["assy_time$i"] = ($parts_pcs / $plan_pcs) * $sum_time;
                // debug $_SESSION["assy_round$i"] = round(($parts_pcs / $plan_pcs) * $sum_time, 0);
                $assy_time = round(($parts_pcs / $plan_pcs) * $sum_time, 0);    // １計画分の工数(分)を算出
                $update_sql = "
                    UPDATE assembly_process_time SET
                    end_time='{$end_time}', assy_time={$assy_time}, last_date='{$last_date}', last_host='{$last_host}'
                    WHERE serial_no={$serial_no}
                "; 
                $this->execute_Update($update_sql);
            }
        } elseif ($status == 'cancel') {     // 完了の取消
            // serial_noで指定された user_idとend_time で同時完了計画分を取得する
            $query = "
                SELECT serial_no FROM assembly_process_time
                WHERE str_time='{$str_time}' AND user_id='{$user_id}'
            ";
            if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
            for ($i=0; $i<$rows; $i++) {
                $serial_no = $res[$i][0];
                // 保存用のSQL文を設定
                $save_sql = "SELECT * FROM assembly_process_time WHERE serial_no={$res[$i][0]}";
                $update_sql = "
                    UPDATE assembly_process_time SET
                    end_time='19700101 000000', assy_time=NULL, last_date='{$last_date}', last_host='{$last_host}'
                    WHERE serial_no={$serial_no}
                "; 
                // $save_sqlはオプションなので指定しなくても良い
                $this->execute_Update($update_sql, $save_sql);
            }
        }
        return true;
    }
    
} // Class AssemblyProcessTime_Model End

?>
