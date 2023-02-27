<?php
//////////////////////////////////////////////////////////////////////////////
// 共通 権限 関係テーブル メンテナンス                       MVC Model 部   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/26 Created   common_authority_Model.php                          //
// 2006/09/06 権限名の修正機能追加に伴い Edit/UpdateDivision  関係を追加    //
// 2006/10/03 categorySelectList()メソッドに ORDER BY category ASC を追加   //
//            getIDName(), getViewListID()メソッドに部門コード[3]を追加     //
// 2006/10/04 メンバー(ID)のタイトルに 権限No.?? を表示追加                 //
// 2007/01/16 category=4(権限レベル)の追加 getViewListID(), getIDName() 変更//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

// require_once ('../../daoInterfaceClass.php');   // TNK 全共通 DAOインターフェースクラス
require_once ('../../ComTableMntClass.php');    // TNK 全共通 テーブルメンテ&ページ制御Class


/*****************************************************************************************
*                   MVCのModel部 クラス定義 ComTableMnt クラスを拡張                     *
*****************************************************************************************/
class CommonAuthority_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    
    private $authDiv = 1;                       // このビジネスロジックの権限区分
    
    ///// Public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        switch ($request->get('Action')) {
        case 'ListDivBody':     // 権限マスター
            $where  = '';
            $sql_sum = "
                SELECT count(*) FROM common_auth_master $where
            ";
            break;
        case 'ListIDBody':      // 権限メンバー
            $where  = "WHERE division={$request->get('targetDivision')}";
            $sql_sum = "
                SELECT count(*) FROM common_authority $where
            ";
            break;
        case 'AddDivision':     // 権限マスターの追加
            $query = "SELECT max(division)+1 FROM common_auth_master";
            $this->getUniResult($query, $div);
            if ($div == '') $div = 1;   // 初回の場合
            $request->add('targetDivision', $div);
        case 'DeleteDivision':  // 権限マスターの削除
        case 'EditDivision':    // 権限名の修正
        case 'UpdateDivision':  // 権限名の修正登録
            $where  = "WHERE division={$request->get('targetDivision')}";
            $sql_sum = "
                SELECT count(*) FROM common_auth_master $where
            ";
            break;
        case 'AddID':           // 権限メンバーの追加
        case 'DeleteID':        // 権限メンバーの削除
            $where  = "WHERE id='{$request->get('targetID')}' AND division={$request->get('targetDivision')}";
            $sql_sum = "
                SELECT count(*) FROM common_authority $where
            ";
            break;
        default:
            return;
        }
        ///// SQL文のWHERE区をPropertiesに登録
        $this->where  = $where;
        ///// log file の指定
        $log_file = 'common_authority.log';
        ///// １ページの行数 初期値 指定
        $page = 200;    // 今回はページ制御はしないため多めに設定
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, $log_file, $page);
    }
    
    ///// 権限マスターのリストを取得
    public function getViewListDivision($request, &$res)
    {
        // 初期化
        $res = array();
        $query = "
            SELECT division, auth_name FROM common_auth_master {$this->where} ORDER BY division ASC
        ";
        return $this->execute_ListNotPageControl($query, $res);
    }
    
    ///// 権限マスターの指定 権限名を取得
    public function getViewDivisionName($request)
    {
        // 初期化
        $authName = '';
        $query = "
            SELECT auth_name FROM common_auth_master WHERE division={$request->get('targetDivision')}
        ";
        $this->getUniResult($query, $authName);
        $authName = '権限No.' . $request->get('targetDivision') . ' &nbsp; ' . $authName;
        return mb_convert_encoding($authName, 'UTF-8', 'UTF-8');
    }
    
    ///// 権限メンバーのリストを取得
    public function getViewListID($request, &$res)
    {
        // 初期化
        $res = array();
        $query = "
            SELECT
                    auth.id ,   -- 00
                CASE
                    WHEN category = 1 THEN (SELECT trim(name) FROM user_detailes WHERE uid=auth.id)
                    WHEN category = 3 THEN (SELECT trim(act_name) FROM act_table WHERE act_id=to_number(auth.id, '999'))
                    WHEN category = 4 THEN (SELECT trim(authority_name) FROM authority_master WHERE aid=to_number(auth.id, '999'))
                    ELSE auth.id
                END         ,   -- 01
                master.cate_name
                            ,   -- 02
                -----------------------------以下はリスト外--------------------
                cate.category,  -- 03
                auth.division   -- 04
            FROM
                common_authority AS auth
            LEFT OUTER JOIN
                common_auth_category AS cate USING(id)
            LEFT OUTER JOIN
                common_auth_category_master AS master USING(category)
            {$this->where}
            ORDER BY auth.id ASC
        ";
        $rows = $this->execute_ListNotPageControl($query, $res);
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][3] == 2) {
                $res[$i][1] = gethostbyaddr($res[$i][0]);
            }
            if ($res[$i][1] == '') $res[$i][1] = $res[$i][0];
        }
        return $rows;
    }
    
    ///// 権限マスターに追加
    public function addDivision($request)
    {
        // 登録済みのチェック
        $query = "
            SELECT division FROM common_auth_master WHERE auth_name='{$request->get('targetAuthName')}'
        ";
        if ($this->getUniResult($query, $check) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('targetAuthName')}\\n\\nは既に登録されています。";
            return false;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $sql = "
            INSERT INTO common_auth_master (division, auth_name, last_date, last_user)
            VALUES ('{$request->get('targetDivision')}', '{$request->get('targetAuthName')}', '{$last_date}', '{$last_user}')
        ";
        if ($this->execute_Insert($sql)) {
            $_SESSION['s_sysmsg'] = '権限マスターに追加しました。';
        } else {
            $_SESSION['s_sysmsg'] = '権限マスターの追加に失敗しました。';
        }
    }
    
    ///// 権限マスターの削除
    public function deleteDivision($request)
    {
        // 登録済みのチェック
        $query = "
            SELECT id FROM common_authority WHERE division={$request->get('targetDivision')} LIMIT 1
        ";
        if ($this->getUniResult($query, $check) > 0) {
            $_SESSION['s_sysmsg'] = "権限No. {$request->get('targetDivision')} はメンバーが登録されています。\\n\\n先にメンバーを削除して下さい。";
            return false;
        }
        $sql = "
            DELETE FROM common_auth_master {$this->where}
        ";
        $save_sql = "
            SELECT * FROM common_auth_master {$this->where}
        ";
        if ($this->execute_Delete($sql, $save_sql)) {
            $_SESSION['s_sysmsg'] = '権限マスターを削除しました。';
        } else {
            $_SESSION['s_sysmsg'] = '権限マスターの削除に失敗しました。';
        }
    }
    
    ///// 権限名の修正
    public function editDivision($request, $result)
    {
        // 登録済みのチェック
        $query = "
            SELECT auth_name FROM common_auth_master {$this->where}
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            $_SESSION['s_sysmsg'] = "権限No. {$request->get('targetDivision')} は登録されていません。\\n\\nほかのユーザーが先に削除したと思われます。";
            return false;
        } else {
            $result->add('division', $request->get('targetDivision'));
            $result->add('auth_name', $check);
        }
        return true;
    }
    
    ///// 権限名の修正登録
    public function updateDivision($request, $result)
    {
        // 登録済みのチェック
        $query = "
            SELECT auth_name FROM common_auth_master {$this->where}
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            $_SESSION['s_sysmsg'] = "権限No. {$request->get('targetDivision')} は登録されていません。\\n\\nほかのユーザーが先に削除したと思われます。";
            return false;
        } else {
            $last_date = date('Y-m-d H:i:s');
            $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
            $sql = "
                UPDATE common_auth_master SET auth_name='{$request->get('targetAuthName')}',
                    last_date='{$last_date}', last_user='{$last_user}'
                {$this->where}
            ";
            $save_sql = "
                SELECT * FROM common_auth_master {$this->where}
            ";
            if ($this->execute_Update($sql, $save_sql)) {
                $_SESSION['s_sysmsg'] = '権限名を変更しました。';
            } else {
                $_SESSION['s_sysmsg'] = '権限名の変更に失敗しました。';
            }
        }
        return true;
    }
    
    ///// 権限メンバーに追加
    public function addID($request)
    {
        // 登録済みのチェック
        $query = "
            SELECT id FROM common_authority {$this->where}
        ";
        if ($this->getUniResult($query, $check) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('targetID')} は既に登録されています。";
            return false;
        }
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "
            SELECT category FROM common_auth_category WHERE id='{$request->get('targetID')}'
        ";
        if ($this->getUniResult($query, $check) <= 0) {
            $sql = "
            INSERT INTO common_authority (id, division, last_date, last_user)
            VALUES ('{$request->get('targetID')}', '{$request->get('targetDivision')}', '{$last_date}', '{$last_user}')
            ;
            INSERT INTO common_auth_category (id, category, last_date, last_user)
            VALUES ('{$request->get('targetID')}', {$request->get('targetCategory')}, '{$last_date}', '{$last_user}')
            ";
        } else {
            $sql = "
            INSERT INTO common_authority (id, division, last_date, last_user)
            VALUES ('{$request->get('targetID')}', '{$request->get('targetDivision')}', '{$last_date}', '{$last_user}')
            ;
            UPDATE common_auth_category SET category={$request->get('targetCategory')}
            WHERE id='{$request->get('targetID')}'
            ";
        }
        if ($this->execute_Insert($sql)) {
            $_SESSION['s_sysmsg'] = '権限メンバーに追加しました。';
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '権限メンバーの追加に失敗しました。';
            return false;
        }
    }
    
    ///// 権限メンバーの削除
    public function deleteID($request)
    {
        $sql = "
            DELETE FROM common_authority {$this->where}
        ";
        $save_sql = "
            SELECT * FROM common_authority {$this->where}
        ";
        if ($this->execute_Delete($sql, $save_sql)) {
            $_SESSION['s_sysmsg'] = '権限メンバーを削除しました。';
        } else {
            $_SESSION['s_sysmsg'] = '権限メンバーの削除に失敗しました。';
        }
    }
    
    ///// 権限メンバーのIDから category 取得
    public function getCategory($request)
    {
        $query = "
            SELECT category FROM common_auth_category WHERE id='{$request->get('targetID')}'
        ";
        $category = '';
        $this->getUniResult($query, $category);
        return $category;
    }
    
    ///// 権限メンバーの<select>リスト出力
    public function categorySelectList($targetCategory='')
    {
        $query = "
            SELECT category, cate_name FROM common_auth_category_master ORDER BY category ASC
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        $option = "\n";
        $option .= "<select id='targetCategory'>\n";
        if ($targetCategory == '') {
            $option .= "<option value='' selected>選択して下さい</option>\n";
        }
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] == $targetCategory) {
                $option .= "<option value='{$res[$i][0]}' selected>{$res[$i][0]}.{$res[$i][1]}</option>\n";
            } else {
                $option .= "<option value='{$res[$i][0]}'>{$res[$i][0]}.{$res[$i][1]}</option>\n";
            }
        }
        $option .= "</select>\n";
        return mb_convert_encoding($option, 'UTF-8', 'UTF-8');
    }
    
    ///// 権限メンバーの 内容 取得
    public function getIDName($request)
    {
        $idName = '';   // 初期化
        switch ($request->get('targetCategory')) {
        case 1:     // 社員番号 → 社員名 をセット
            $query = "
                SELECT trim(name) FROM user_detailes WHERE uid='{$request->get('targetID')}'
            ";
            $this->getUniResult($query, $idName);
            if ($idName == '') $idName = '未登録';
            break;
        case 2:     // IPアドレス → host名 をセット
            $idName = @gethostbyaddr($request->get('targetID'));
            if ($idName == '') $idName = 'IPアドレスではない';
            break;
        case 3:     // 部門コード → 部門名 をセット
            $query = "
                SELECT trim(act_name) FROM act_table WHERE act_id='{$request->get('targetID')}'
            ";
            $this->getUniResult($query, $idName);
            if ($idName == '') $idName = '未登録';
            break;
        case 4:     // 権限レベル → (0=一般, 1=中級, 2=上級, 3=アドミニ)
            $query = "
                SELECT trim(authority_name) FROM authority_master WHERE aid={$request->get('targetID')}
            ";
            $this->getUniResult($query, $idName);
            if ($idName == '') $idName = '未登録';
            break;
        default:
            $idName = $request->get('targetID');
        }
        return mb_convert_encoding($idName, 'UTF-8', 'UTF-8');
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    
    
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    
    
    
} // Class CommonAuthority_Model End

?>
