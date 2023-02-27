//////////////////////////////////////////////////////////////////////////////
// 組立指示メニューの 実績データ編集用  MVC View 部 (JavaScriptクラス)      //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/13 Created    assembly_time_edit.js                              //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*     assembly_time_edit class テンプレートの拡張クラスの定義           *
/****************************************************************************
class assembly_time_edit extends base_class
{   */
    ///// スーパークラスの継承
    assembly_time_edit.prototype = new base_class();   // base_class の継承
    ///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
    var _GDEBUG = false;
    
    ///// Constructer の定義
    function assembly_time_edit()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // プロパティーの初期化
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        assembly_time_edit.prototype.set_focus           = set_focus;            // 指定の入力エレメントにフォーカス
        assembly_time_edit.prototype.blink_disp          = blink_disp;           // 点滅表示メソッド
        assembly_time_edit.prototype.obj_upper           = obj_upper;            // オブジェの値を大文字変換
        assembly_time_edit.prototype.win_open            = win_open;             // サブウィンドウを中央に表示
        assembly_time_edit.prototype.winActiveChk        = winActiveChk;         // サブウィンドウのActiveチェック
        assembly_time_edit.prototype.win_show            = win_show;             // モーダルダイアログを表示(IE専用)
        assembly_time_edit.prototype.checkInputForm      = checkInputForm;       // 入力フォームの入力チェックメソッド
        
        return this;    // Object Return
    }
    
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    function set_focus(obj, status)
    {
        if (obj) {
            obj.focus();
            if (status == "select") obj.select();
        }
        // document.body.focus();   // F2/F12キーを有効化する対応
        // document.mhForm.backwardStack.focus();  // 上記はIEのみのため、こちらに変更しNN対応
        // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
        // document.form_name.element_name.select();
    }
    
    /***** 点滅表示のHTMLドキュメント *****/
    /***** blink_flg はグローバル変数に注意 下の例は0.5秒毎に点滅 *****/
    /***** <body onLoad='setInterval("templ.blink_disp(\"caption\")", 500)'> *****/
    function blink_disp(id_name)
    {
        if (blink_flag == 1) {
            document.getElementById(id_name).innerHTML = "";
            blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = "サンプルでアイテムマスターを表示しています";
            blink_flag = 1;
        }
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    function obj_upper(obj) {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    function win_open(url, w, h) {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    function winActiveChk() {
        if (document.all) {     // IEなら
            if (document.hasFocus() == false) {     // IE5.5以上で使える
                window.focus();
                return;
            }
            return;
        } else {                // NN ならとワリキッテ
            window.focus();
            return;
        }
    }
    
    /***** 指定の大きさのモーダルダイアログを表示する *****/
    /***** IE 専用なのと Windows XP SP2 ではセキュリティの警告が出る *****/
    /***** ダイアログ内でリクエストを出す場合はフレームを切って行う *****/
    function win_show(url, w, h) {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** checkInputForm の入力チェックメソッド(計画番号, 社員番号) *****/
    function checkInputForm(obj) {
        obj.plan_no.value = obj.plan_no.value.toUpperCase();
        if (obj.plan_no.value.length == 0) {
            alert("計画番号がブランクです。");
            obj.plan_no.focus();
            obj.plan_no.select();
            return false;
        }
        if (obj.plan_no.value.length != 8) {
            alert("計画番号の桁数は８桁です。");
            obj.plan_no.focus();
            obj.plan_no.select();
            return false;
        }
        if (!this.isDigit(obj.plan_no.value.substr(2, 6))) {
            alert("計画番号の３文字目以降は数字で入力して下さい。");
            obj.plan_no.focus();
            obj.plan_no.select();
            return false;
        }
        if (obj.plan_no.value.substr(0, 1) == 'Z') {
            // バーコードのための Z を @ へ変換
            obj.plan_no.value = '@' + obj.plan_no.value.substr(1, 7);
        }
        if (obj.user_id.value.length == 0) {
            alert("作業者の社員番号がブランクです。");
            obj.user_id.focus();
            obj.user_id.select();
            return false;
        }
        if (obj.user_id.value.length != 6) {
            alert("作業者の社員番号の桁数は６桁です。");
            obj.user_id.focus();
            obj.user_id.select();
            return false;
        }
        if (!this.isDigit(obj.user_id.value)) {
            alert("作業者の社員番号は数字で入力して下さい。");
            obj.user_id.focus();
            obj.user_id.select();
            return false;
        }
        return true;
    }
    
/*
}   // class assembly_time_edit END  */


///// インスタンスの生成
var AssemblyTimeEdit = new assembly_time_edit();
// blink_disp()メソッド内で使用するグローバル変数のセット
var blink_flag = 1;


