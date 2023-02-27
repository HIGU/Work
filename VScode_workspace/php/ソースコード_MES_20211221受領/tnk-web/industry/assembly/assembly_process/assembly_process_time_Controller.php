<?php
//////////////////////////////////////////////////////////////////////////////
// 組立工程の作業工数 (着手・完了時間) 集計用  MVC Controller 部            //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/30 Created   assembly_process_time_Controller.php                //
// 2007/06/17 display()メソッドのapend部に if ($userEnd == '') →           //
//            if ($userEnd == '' || $userRows <= 0) 着手作業者のチェック    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class AssemblyProcessTime_Controller
{
    ///// Private properties
    private $showMenu;                  // メニュー切替
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($menu, $request, $result, $model)
    {
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用データ取得
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') $showMenu = 'StartList';       // 指定がない場合は一覧表を表示(特に初回)
        
        ///// キーフィールド リクエスト データ取得
        $serial_no  = $request->get('serial_no');           // table連番 キーフィールド
        $group_no   = $request->get('group_no');            // グループ番号
        $user_id    = $request->get('user_id');             // 社員番号
        $plan_no    = $request->get('plan_no');             // 計画番号
        $Ggroup_no  = $request->get('Ggroup_no');           // 登録・変更用グループ番号
        
        ///// 登録・修正・削除の 実行指示リクエストを ローカル変数に登録
        $apendUser  = $request->get('apendUser');           // 組立着手のユーザー登録
        $apendPlan  = $request->get('apendPlan');           // 組立着手の計画番号登録
        $deleteUser = $request->get('deleteUser');          // 組立着手のユーザー取消
        $deletePlan = $request->get('deletePlan');          // 組立着手の計画番号取消
        $apendEnd   = $request->get('apendEnd');            // 組立着手の入力終了
        $assyEnd    = $request->get('assyEnd');             // 組立完了の入力 個別完了(中断を含む)
        $assyEndAll = $request->get('assyEndAll');          // 組立完了の入力 一括完了(中断を含む)(2007/06/17現在は使用されていない)
        $endCancel  = $request->get('endCancel');           // 組立完了の取消
        $groupEdit  = $request->get('groupEdit');           // グループの登録・変更
        $groupOmit  = $request->get('groupOmit');           // グループの削除
        $groupActive= $request->get('groupActive');         // グループの有効・無効(トグル)
        
        ///// 登録・編集 データ収録
        $group_name = $request->get('group_name');          // グループ名
        $div        = $request->get('div');                 // 事業部
        $product    = $request->get('product');             // 製品グループ
        $active     = $request->get('active');              // グループの有効・無効(トグル)
        
        ////////// MVC の Model 部の 実行部ロジック切替
        if ($apendUser != '') {                             // 組立着手のユーザー登録 (追加)
            $response = $model->userAdd($group_no, $user_id);
            if ($response) {
                $request->add('user_id', '');               // 登録できたのでuser_idの<input>データを消す
            }
        } elseif ($deleteUser != '') {                      // 組立着手のユーザー取消 (完全削除)
            $response = $model->userDelete($group_no, $user_id);
            if (!$response) $showMenu = 'apend';            // 削除出来なかったので組立着手登録画面にする
        } elseif ($apendPlan != '') {                       // 組立着手の計画番号登録
            $response = $model->planAdd($group_no, $plan_no);
            if ($response) {
                $request->add('plan_no', '');               // 登録できたのでplan_noの<input>データを消す
            }
        } elseif ($deletePlan != '') {                      // 組立着手の計画番号(ユーザーは複数あり) 取消
            ///// 削除は個別削除でserial_noからstr_time,group_no,user_idを取得して内部データを一括更新を行う $plan_noはメッセージ用
            $response = $model->planDelete($serial_no, $plan_no);
            if (!$response) $showMenu = 'apend';            // 削除出来なかったので組立着手登録画面にする
        } elseif ($apendEnd != '') {                        // 組立着手の作業者・計画番号の入力終了処理
            ///// カレントのグループ番号の work テーブルレコードを削除する
            $response = $model->apendEnd($group_no);
            if (!$response) {
                $showMenu = 'apend';                        // 変更出来なかったので組立着手登録画面にする
            }
        } elseif ($assyEnd != '') {                         // 組立完了の入力 (変更) 個別完了
            ///// serial_noで個別(作業者・計画番号単位)で完了を行う $plan_noはメッセージ用
            $response = $model->assyEnd($serial_no, $plan_no);
            if (!$response) {
                $showMenu = 'apend';                        // 変更出来なかったので組立着手登録画面にする
            }
        } elseif ($assyEndAll != '') {                      // 組立完了の入力 (変更) 一括完了
            ///// serial_noからstr_time,group_noを取得して一括完了を行う $plan_noはメッセージ用
            $response = $model->assyEndAll($serial_no, $plan_no);
            if (!$response) {
                $showMenu = 'apend';                        // 変更出来なかったので組立着手登録画面にする
            }
        } elseif ($endCancel != '') {                       // 組立完了の取消 (変更)
            ///// serial_noからstr_time,group_no,user_idを取得して作業者毎の一括取消を行う $plan_noはメッセージ用
            $response = $model->endCancel($serial_no, $plan_no);
            if (!$response) {
                $showMenu = 'EndList';                      // 変更出来なかったので組立完了一覧画面にする
            }
        } elseif ($groupEdit != '') {                       // グループの登録・変更
            if ($model->groupEdit($Ggroup_no, $group_name, $div, $product, $active)) {
                $request->add('Ggroup_no', '');             // 登録できたのでgroup_no, group_nameの<input>データを消す
                $request->add('group_name', '');
                $request->add('div', '');
                $request->add('product', '');
                $request->add('active', '');
            }
        } elseif ($groupOmit != '') {                       // グループの削除
            $response = $model->groupOmit($Ggroup_no, $group_name);
        } elseif ($groupActive != '') {                     // グループの有効・無効(トグル)
            if ($model->groupActive($Ggroup_no, $group_name)) {
                $request->add('Ggroup_no', '');
                $request->add('group_name', '');
                $request->add('active', '');
            }
        }
        
        $this->showMenu = $showMenu;
        
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('process');
        
        ///// キーフィールド リクエスト データ取得
        $showMenu   = $this->showMenu;
        
        $serial_no  = $request->get('serial_no');           // table連番 キーフィールド
        $group_no   = $request->get('group_no');            // グループ番号
        $user_id    = $request->get('user_id');             // 社員番号
        $plan_no    = $request->get('plan_no');             // 計画番号
        $Ggroup_no  = $request->get('Ggroup_no');           // 登録・変更用グループ番号
        
        ///// 登録・修正・削除の 実行指示リクエストを ローカル変数に登録
        $groupCopy  = $request->get('groupCopy');           // グループの変更フラグ
        $userEnd    = $request->get('userEnd');             // 作業者の追加終了ボタン
        
        ///// 登録・編集 データ収録
        $group_name = $request->get('group_name');          // グループ名
        $div        = $request->get('div');                 // 事業部
        $product    = $request->get('product');             // 製品グループ
        $active     = $request->get('active');              // グループの有効・無効(トグル)
        
        ////////// MVC の Model部の View部に渡すデータ生成
        switch ($showMenu) {
        case 'StartList':                                   // 組立着手 一覧表 表示
            $rows = $model->getViewStartList($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        case 'EndList':                                     // 組立完了 一覧表 表示
            $rows = $model->getViewEndList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        case 'apend':                                       // 組立着手の入力 (追加) 複数の計画対応のため ApendListを取得する
            $userRows = $model->getViewUserListNotPage($group_no, $result);
            $userRes  = $result->get_array();
            $planRows = $model->getViewPlanListNotPage($result);
            $planRes  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        case 'group':                                       // グループ 一覧表 表示
            $rows = $model->getViewGroupList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        }
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($showMenu) {
        case 'StartList':       // 組立着手 一覧表 表示
            require_once ('assembly_process_time_ViewStartList.php');
            break;
        case 'EndList':         // 組立完了 一覧表 表示
            require_once ('assembly_process_time_ViewEndList.php');
            break;
        case 'apend':           // 組立着手の入力 (追加)
            if ($userEnd == '' || $userRows <= 0) { // 2007/06/17 着手作業者0ならばを追加(model部に個別に削除ロジック追加のため)
                require_once ('assembly_process_time_ViewApendUser.php');
            } else {            // userEndボタンが押されたら
                require_once ('assembly_process_time_ViewApendPlan.php');
            }
            break;
        case 'group':            // グループ(作業区) 一覧表 表示
            if ($groupCopy == 'go') {
                $focus    = 'group_name';
                $readonly = "readonly style='background-color:#d6d3ce;'";
            } else {
                $focus    = 'Ggroup_no';
                $readonly = '';
            }
            require_once ('assembly_process_time_ViewGroup.php');
            break;
        default:                // リクエストデータにエラーの場合は初期値の着手一覧を表示
            require_once ('assembly_process_time_ViewStartList.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
