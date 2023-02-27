<?php
//////////////////////////////////////////////////////////////////////////////
// 部品売上げの材料費(購入費)の 照会                 MVC Controller 部      //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/15 Created   parts_material_show_Controller.php                  //
// 2006/02/20 InitTargetItemNo()のメソッドを指定が無い場合の処理をコメント  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class PartsMaterialShow_Controller
{
    ///// Private properties
    private $menu;                              // TNK 共用メニュークラスのインスタンス
    private $request;                           // HTTP Controller部のリクエスト インスタンス
    private $result;                            // HTTP Controller部のリザルト   インスタンス
    private $session;                           // HTTP Controller部のセッション インスタンス
    private $model;                             // ビジネスモデル部のインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($menu)
    {
        ///// MenuHeader クラスのインスタンスを properties に登録
        if (is_object($menu)) {
            $this->menu = $menu;
        } else {
            exit();
        }
        //////////// リクエストのインスタンスを登録
        $this->request = new Request();
        
        //////////// リザルトのインスタンスを登録
        $this->result = new Result();
        
        //////////// セッションのインスタンスを登録
        $this->session = new Session();
        
        //////////// リクエスト・セッション等の初期化処理
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定 (Modelで使用する)
        $this->Init();
        
        //////////// ビジネスモデル部のインスタンスを生成しプロパティへ登録
        $this->model = new PartsMaterialShow_Model($this->request);
    }
    
    ///// MVC View部の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('partsMShow');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
            require_once ('parts_material_show_ViewCondForm.php');
            break;
        case 'ListTable':                                   // Ajax用 List表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            require_once ('parts_material_show_ViewListTable.php');
            break;
        case 'WaitMsg':                                     // Ajax用 処理中です。お待ち下さい。
            require_once ('parts_material_show_ViewWaitMsg.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    protected function Init()
    {
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定
        // showMenuの処理
        $this->InitShowMenu();
        // showDivの処理
        $this->InitShowDiv();
        // targetDateStrの処理
        $this->InitTargetDateStr();
        // targetDateEndの処理
        $this->InitTargetDateEnd();
        // targetItemNoの処理
        $this->InitTargetItemNo();
        // targetSalesSegmentの処理
        $this->InitTargetSalesSegment();
        // PageKeepの処理
        $this->InitPageKeep();
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    ///// メニュー切替用 showMenu のデータチェック ＆ 設定
    // showMenuの処理
    private function InitShowMenu()
    {
        $showMenu = $this->request->get('showMenu');
        if ($showMenu == '') {
            if ($this->session->get_local('showMenu') == '') {
                $showMenu = 'CondForm';         // 指定がない場合はCondition Form (条件設定)
            } else {
                $showMenu = $this->session->get_local('showMenu');
            }
        }
        // Ajaxの場合はセッションに保存しない
        if ($showMenu != 'ListTable' || $showMenu != 'WaitMsg') {
            // 今回は照会のみの単純フォームなのでセッションは使わない
            // $this->session->add_local('showMenu', $showMenu);
        }
        $this->request->add('showMenu', $showMenu);
    }
    
    // showDivの処理
    private function InitShowDiv()
    {
        $showDiv = $this->request->get('showDiv');
        if ($showDiv == '') {
            if ($this->session->get_local('showDiv') == '') {
                $showDiv = '';                // 指定がない場合は全てを指定したものと見なす
            } else {
                $showDiv = $this->session->get_local('showDiv');
            }
        }
        if ($showDiv == '0') $showDiv = ''; // 0 は全体を意味する。
        $this->session->add_local('showDiv', $showDiv);
        $this->request->add('showDiv', $showDiv);
    }
    
    ///// 開始年月日の取得・初期化
    // targetDateStrの処理
    private function InitTargetDateStr()
    {
        $targetDateStr = $this->request->get('targetDateStr');
        if ($targetDateStr == '') {
            if ($this->session->get_local('targetDateStr') == '') {
                $targetDateStr = workingDayOffset(-1);      // 指定がない場合は前営業日
            } else {
                $targetDateStr = $this->session->get_local('targetDateStr');
            }
        }
        $this->session->add_local('targetDateStr', $targetDateStr);
        $this->request->add('targetDateStr', $targetDateStr);
    }
    
    ///// 開始年月日の取得・初期化
    // targetDateEndの処理
    private function InitTargetDateEnd()
    {
        $targetDateEnd = $this->request->get('targetDateEnd');
        if ($targetDateEnd == '') {
            if ($this->session->get_local('targetDateEnd') == '') {
                $targetDateEnd = workingDayOffset(-1);      // 指定がない場合は前営業日
            } else {
                $targetDateEnd = $this->session->get_local('targetDateEnd');
            }
        }
        $this->session->add_local('targetDateEnd', $targetDateEnd);
        $this->request->add('targetDateEnd', $targetDateEnd);
    }
    
    ///// 製品又は部品番号の取得・初期化
    // targetItemNoの処理
    private function InitTargetItemNo()
    {
        $targetItemNo = $this->request->get('targetItemNo');
        /*****
        if ($targetItemNo == '') {
            if ($this->session->get_local('targetItemNo') == '') {
                $targetItemNo = '';   // 指定がない場合は全て対象
            } else {
                $targetItemNo = $this->session->get_local('targetItemNo');
            }
        }
        *****/
        $this->session->add_local('targetItemNo', $targetItemNo);
        $this->request->add('targetItemNo', $targetItemNo);
    }
    
    ///// 売上区分 1=製品(完成), 2=部品合計(5～9), 5=部品(移動), 6=部品(直納NKT), 7=部品(売上), 8=部品(振替), 9=部品(受注)
    // targetSalesSegmentの処理 (現在は部品のみを対象とする)
    private function InitTargetSalesSegment()
    {
        $targetSalesSegment = $this->request->get('targetSalesSegment');
        if ($targetSalesSegment == '') {
            if ($this->session->get_local('targetSalesSegment') == '') {
                $targetSalesSegment = '2';   // 指定がない場合は部品合計
            } else {
                $targetSalesSegment = $this->session->get_local('targetSalesSegment');
            }
        }
        $this->session->add_local('targetSalesSegment', $targetSalesSegment);
        $this->request->add('targetSalesSegment', $targetSalesSegment);
    }
    
    ///// リンク先を照会した場合の戻り値をチェック
    // page_keepを取得してmaterial_plan_no 及びページ制御の処理
    private function InitPageKeep()
    {
        if ($this->request->get('page_keep') != '') {
            // クリックした計画番号の行にマーカー用
            if ($this->session->get('material_plan_no') != '') {
                $this->request->add('material_plan_no', $this->session->get('material_plan_no'));
            }
            // ページ制御用 (呼出した時のページに戻す)
            if ($this->session->get_local('viewPage') != '') {
                $this->request->add('CTM_viewPage', $this->session->get_local('viewPage'));
            }
            if ($this->session->get_local('pageRec') != '') {
                $this->request->add('CTM_pageRec', $this->session->get_local('pageRec'));
            }
        }
    }
    
} // class PartsMaterialShow_Controller End

?>
