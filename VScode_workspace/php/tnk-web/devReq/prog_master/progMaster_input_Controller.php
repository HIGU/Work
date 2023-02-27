<?php
//////////////////////////////////////////////////////////////////////////////
// プログラムマスターのメンテナンス MVC Controller部                        //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_input_Controller.php                     //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class ProgMaster_Controller
{
    ///// Private properties
    private $current_menu;                  // メニュー切替
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($menu, $request, $result, $model)
    {
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用データ取得
        $current_menu = $request->get('current_menu');
        if ($current_menu == '') $current_menu = 'list'; // 指定がない場合は一覧表を表示(特に初回)
        
        ///// 表示用フィールド データ取得
        $pid   = $request->get('pid');        // mipn (部品番号)
        $pname = $request->get('pname');      // midsc(名称)
        $pdir  = $request->get('pdir');       // mzist(材質)
        $pcomment= $request->get('pcomment');     // mepnt(親機種)
        $db1 = $request->get('db1');      // madat(AS登録日)
        $db2 = $request->get('db2');      // madat(AS登録日)
        $db3 = $request->get('db3');      // madat(AS登録日)
        $db4 = $request->get('db4');      // madat(AS登録日)
        $db5 = $request->get('db5');      // madat(AS登録日)
        $db6 = $request->get('db6');      // madat(AS登録日)
        $db7 = $request->get('db7');      // madat(AS登録日)
        $db8 = $request->get('db8');      // madat(AS登録日)
        $db9 = $request->get('db9');      // madat(AS登録日)
        $db10 = $request->get('db10');      // madat(AS登録日)
        $db11 = $request->get('db11');      // madat(AS登録日)
        $db12 = $request->get('db12');      // madat(AS登録日)
        
        // 入力された使用DBを前に詰める処理
        if ($db1 == '') {
            if ($db2 != '') {
                $db1 = $db2;
                $db2 = '';
            } elseif ($db3 != '') {
                $db1 = $db3;
                $db3 = '';
            } elseif ($db4 != '') {
                $db1 = $db4;
                $db4 = '';
            } elseif ($db5 != '') {
                $db1 = $db5;
                $db5 = '';
            } elseif ($db6 != '') {
                $db1 = $db6;
                $db6 = '';
            } elseif ($db7 != '') {
                $db1 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db1 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db1 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db1 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db1 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db1 = $db12;
                $db12 = '';
            }
        }
        if ($db2 == '') {
            if ($db3 != '') {
                $db2 = $db3;
                $db3 = '';
            } elseif ($db4 != '') {
                $db2 = $db4;
                $db4 = '';
            } elseif ($db5 != '') {
                $db2 = $db5;
                $db5 = '';
            } elseif ($db6 != '') {
                $db2 = $db6;
                $db6 = '';
            } elseif ($db7 != '') {
                $db2 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db2 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db2 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db2 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db2 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db2 = $db12;
                $db12 = '';
            }
        }
        if ($db3 == '') {
            if ($db4 != '') {
                $db3 = $db4;
                $db4 = '';
            } elseif ($db5 != '') {
                $db3 = $db5;
                $db5 = '';
            } elseif ($db6 != '') {
                $db3 = $db6;
                $db6 = '';
            } elseif ($db7 != '') {
                $db3 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db3 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db3 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db3 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db3 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db3 = $db12;
                $db12 = '';
            }
        }
        if ($db4 == '') {
            if ($db5 != '') {
                $db4 = $db5;
                $db5 = '';
            } elseif ($db6 != '') {
                $db4 = $db6;
                $db6 = '';
            } elseif ($db7 != '') {
                $db4 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db4 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db4 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db4 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db4 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db4 = $db12;
                $db12 = '';
            }
        }
        if ($db5 == '') {
            if ($db6 != '') {
                $db5 = $db6;
                $db6 = '';
            } elseif ($db7 != '') {
                $db5 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db5 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db5 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db5 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db5 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db5 = $db12;
                $db12 = '';
            }
        }
        if ($db6 == '') {
            if ($db7 != '') {
                $db6 = $db7;
                $db7 = '';
            } elseif ($db8 != '') {
                $db6 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db6 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db6 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db6 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db6 = $db12;
                $db12 = '';
            }
        }
        if ($db7 == '') {
            if ($db8 != '') {
                $db7 = $db8;
                $db8 = '';
            } elseif ($db9 != '') {
                $db7 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db7 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db7 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db7 = $db12;
                $db12 = '';
            }
        }
        if ($db8 == '') {
            if ($db9 != '') {
                $db8 = $db9;
                $db9 = '';
            } elseif ($db10 != '') {
                $db8 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db8 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db8 = $db12;
                $db12 = '';
            }
        }
        if ($db9 == '') {
            if ($db10 != '') {
                $db9 = $db10;
                $db10 = '';
            } elseif ($db11 != '') {
                $db9 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db9 = $db12;
                $db12 = '';
            }
        }
        if ($db10 == '') {
            if ($db11 != '') {
                $db10 = $db11;
                $db11 = '';
            } elseif ($db12 != '') {
                $db10 = $db12;
                $db12 = '';
            }
        }
        if ($db11 == '') {
            if ($db12 != '') {
                $db11 = $db12;
                $db12 = '';
            }
        }
        ////////// 確認フォームで取消が押された時のリクエスト取得
        $cancel_apend  = $request->get('cancel_apend');
        $cancel_del    = $request->get('cancel_del');
        $cancel_edit   = $request->get('cancel_edit');
        
        /********* 修正用 *********/
        $prePid = $request->get('prePid');
        $preDir = $request->get('preDir');
        
        ////////// 確認フォームへ渡すデータ取得
        $confirm_apend  = $request->get('confirm_apend');
        $confirm_edit   = $request->get('confirm_edit');
        $confirm_delete = $request->get('confirm_delete');
        if ($confirm_apend != '') {
            $current_menu = 'confirm_apend';
        } elseif ($confirm_edit != '') {
            $current_menu = 'confirm_edit';
        } elseif ($confirm_delete != '') {
            $current_menu = 'confirm_delete';
        }
        
        ////////// 確認フォームで取消が押された時のステータスを取得しメニュー切替
        if ($cancel_apend != '') {
            $current_menu = 'apend';
        } elseif ($cancel_edit != '') {
            $current_menu = 'edit';
        } elseif ($cancel_del != '') {
            $current_menu = 'edit';
        }
        
        //////////////// 登録・修正・削除の POST 変数を ローカル変数に登録
        $apend  = $request->get('apend');
        $edit   = $request->get('edit');
        $delete = $request->get('delete');
        
        ////////// MVC の Model 部の 実行部ロジック切替
        if ($apend != '') {         ////////// マスター追加
            $response = $model->table_add($pid, $pname, $pdir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12);
            if (!$response) $current_menu = 'apend';    // 登録出来なかったので追加画面にする
        } elseif ($edit != '') {    ////////// マスター 変更
            $response = $model->table_change($prePid, $pid, $pname, $pdir, $preDir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12);
            if (!$response) {
                $current_menu = 'edit';                 // 変更出来なかったので編集画面にする
                $cancel_edit  = '取消';                 // 変更時のデータで表示
            }
        } elseif ($delete != '') {  ////////// マスター完全削除
            $response = $model->table_delete($pid, $pdir);
            if (!$response) $current_menu = 'edit';     // 削除出来なかったので編集画面にする
        }
        
        $this->current_menu = $current_menu;
        
        ////////// リクエストデータの一部を変更したので再登録
        $request->add('cancel_apend', $cancel_apend);
        $request->add('cancel_del',   $cancel_del);
        $request->add('cancel_edit',  $cancel_edit);
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('item');
        
        ///// ローカルへオブジェクトコピー(HTML埋め込み変数用)
        $current_menu = $this->current_menu;
        
        ///// 表示用フィールド データ取得
        $pid   = $request->get('pid');      // mipn (部品番号)
        $pid   = str_replace('シャープ', '#', $pid);
        $pname = $request->get('pname');    // midsc(名称)
        $pdir  = $request->get('pdir');     // mzist(材質)
        $pcomment= $request->get('pcomment');   // mepnt(親機種)
        $db1 = $request->get('db1');    // madat(AS登録日)
        $db2 = $request->get('db2');    // madat(AS登録日)
        $db3 = $request->get('db3');    // madat(AS登録日)
        $db4 = $request->get('db4');    // madat(AS登録日)
        $db5 = $request->get('db5');    // madat(AS登録日)
        $db6 = $request->get('db6');    // madat(AS登録日)
        $db7 = $request->get('db7');    // madat(AS登録日)
        $db8 = $request->get('db8');    // madat(AS登録日)
        $db9 = $request->get('db9');    // madat(AS登録日)
        $db10 = $request->get('db10');    // madat(AS登録日)
        $db11 = $request->get('db11');    // madat(AS登録日)
        $db12 = $request->get('db12');    // madat(AS登録日)
        /********* 修正用 *********/
        $prePid = $request->get('prePid');  // 変更前の部品番号
        $preDir = $request->get('preDir');  // 変更前の部品番号
        
        ///// キーフィールドのリクエスト取得
        $pidKey   = $request->get('pidKey');      // mipn(部品番号)のキーフィールド
        
        ////////// MVC の Model部の View部に渡すデータ取得
        switch ($current_menu) {
        case 'list':            // アイテム 一覧表 表示
        case 'table':           // アイテム 一覧表 のテーブル部のみ表示(Ajax用)
            if ($pidKey == '') {
                // キーフィールドが指定されていない(初回)ので入力フォームのみ
                $rows = 0; $res = array();
            } else {
                $rows = $model->getViewDataList($result);
                $res  = $result->get_array();
            }
            break;
        case 'edit':            // マスター修正
        case 'confirm_delete':  // 削除の確認
            if ($prePid == '') $prePid = $pid;   // 前の番号が設定されていない場合は初回と判定してpidを代入する
            if ($preDir == '') $preDir = $pdir;  // 前の番号が設定されていない場合は初回と判定してpidを代入する
            if ($request->get('cancel_edit') == '') {     // 確認フォームの取消の時は前のデータをそのまま使う
                $rows = $model->getViewDataEdit($pid, $pdir, $result);
                $pname = $result->get_once('pname');
                $pcomment= $result->get_once('pcomment');
                $db1 = $result->get_once('db1');
                $db2 = $result->get_once('db2');
                $db3 = $result->get_once('db3');
                $db4 = $result->get_once('db4');
                $db5 = $result->get_once('db5');
                $db6 = $result->get_once('db6');
                $db7 = $result->get_once('db7');
                $db8 = $result->get_once('db8');
                $db9 = $result->get_once('db9');
                $db10 = $result->get_once('db10');
                $db11 = $result->get_once('db11');
                $db12 = $result->get_once('db12');
            }
            break;
        }
        
        ////////// HTML Header を出力してキャッシュ等を制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($current_menu) {
        case 'list':            // 一覧表 表示
            // $pageControll = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            // $model->set_page_rec(20);     // 1頁のレコード数
            $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            require_once ('progMaster_input_ViewList.php');
            break;
        case 'table':           // 出庫 一覧表 のテーブル部のみ表示(Ajax用)
            require_once ('progMaster_input_ViewTable.php');
            break;
        case 'apend':           // マスター追加
            require_once ('progMaster_input_ViewApend.php');
            break;
        case 'edit':            // マスター修正
            require_once ('progMaster_input_ViewEdit.php');
            break;
        case 'confirm_apend':   // 登録の確認
        case 'confirm_edit':    // 変更の確認
        case 'confirm_delete':  // 削除の確認
            require_once ('progMaster_input_ViewConfirm.php');
            break;
        default:                // リクエストデータにエラー
            require_once ('progMaster_input_ViewList.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
