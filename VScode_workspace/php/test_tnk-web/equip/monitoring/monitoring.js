////////////////////////////////////////////////////////////////////////////////
// 機械稼働管理指示メンテナンス                                               //
//                                            MVC View 部 (JavaScriptクラス)  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring.js                                           //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*     monitoring class テンプレートの拡張クラスの定義           *
/****************************************************************************
class monitoring extends base_class
{   */
    ///// スーパークラスの継承
    monitoring.prototype = new base_class();   // base_class の継承
    ///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
    var _GDEBUG = false;
    
    ///// Constructer の定義
    function monitoring()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // プロパティーの初期化
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        monitoring.prototype.set_focus        = set_focus;        // 指定の入力エレメントにフォーカス
        monitoring.prototype.blink_disp       = blink_disp;       // 点滅表示メソッド
        monitoring.prototype.obj_upper        = obj_upper;        // オブジェの値を大文字変換
        monitoring.prototype.win_open         = win_open;         // サブウィンドウを中央に表示
        monitoring.prototype.winActiveChk     = winActiveChk;     // サブウィンドウのActiveチェック
        monitoring.prototype.win_show         = win_show;         // モーダルダイアログを表示(IE専用)
        monitoring.prototype.ControlFormSubmit= ControlFormSubmit;// ControlForm のサブミットメソッド
        monitoring.prototype.checkANDexecute  = checkANDexecute;  // 不在者のウインドウ表示
        monitoring.prototype.AjaxLoadTable    = AjaxLoadTable;    // 不在者のウインドウ表示2
        monitoring.prototype.AjaxLoadPITable  = AjaxLoadPITable;  // PIカレンダーのウインドウ表示
        
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
    
    // 自動更新（リロード）設定
    function init() 
    {
        setInterval('document.reload_form.submit()', 30000);   // 30秒
    }
    
    // 自動更新（リロード）なし
    function init2() 
    {
        var obj = document.getElementById('id_plan_no');
        if( obj ) set_focus(obj, "select");
    }
    
    // 入力した計画番号の桁数チェック
    function planNoCheck() {
        if( document.getElementById('id_plan_no').value.length < 8 ) {
            alert("計画番号は、８桁必要です。");
            return false;
        } else {
            return true;
        }
    }
    
    function setState(obj) {
        document.getElementById('id_state').value = obj.name;
//alert("setState(" + obj.name + ")");
        return true;
    }

    function setSelectMode(obj) {
        document.getElementById('id_select_mode').value = obj.value;
        document.header_form.submit();
//        obj.submit();
//alert("TEST");
        return true;
    }

    function setViewMode(obj) {
        document.getElementById('id_view_mode').value = obj.id;
        document.header_form.submit();
//        obj.submit();
//alert("TEST");
        return true;
    }
    
    //
    function setSlectInfo(rec){
        document.getElementById('id_m_no').value = document.getElementById('id_m_no'+rec).value;
        document.getElementById('id_m_name').value = document.getElementById('id_m_name'+rec).value;
        document.getElementById('id_plan_no').value = document.getElementById('id_plan_no'+rec).value;
//alert("setSlectInfo(" + rec + ")");
    }

    function chk_break_del(obj, mac_no, name, plan_no, parts_no) {
        var flag = confirm(   "機械番号：" + mac_no + "\n\n"
                        + "機 械 名：" + name + "\n\n"
                        + "計画番号：" + plan_no + "\n\n"
                        + "部品番号：" + parts_no + "\n\n"
                        + "を完全削除します。宜しいですか？");
        if( flag ) {
            obj.value = 'delete';
            return true;
        } else {
            return false;
        }
    }
    
    function chk_break_restart(obj, mac_no, name, plan_no, parts_no) {
        var flag = confirm(   "機械番号：" + mac_no + "\n\n"
                        + "機 械 名：" + name + "\n\n"
                        + "計画番号：" + plan_no + "\n\n"
                        + "部品番号：" + parts_no + "\n\n"
                        + "を再開します。宜しいですか？");
        if( flag ) {
            obj.value = 'restart';
            return true;
        } else {
            return false;
        }
    }
    
    function chk_end_inst(obj, mac_no, name, plan_no, parts_no) {
        var flag = confirm(   "機械番号：" + mac_no + "\n\n"
                        + "機 械 名：" + name + "\n\n"
                        + "計画番号：" + plan_no + "\n\n"
                        + "部品番号：" + parts_no + "\n\n"
                        + "を完了します。宜しいですか？");
        if( flag ) {
            obj.value = 'end';
            return true;
        } else {
            return false;
        }
    }
    
    function chk_cut_form(obj, mac_no, name, plan_no, parts_no) {
        var flag = confirm(   "機械番号：" + mac_no + "\n\n"
                        + "機 械 名：" + name + "\n\n"
                        + "計画番号：" + plan_no + "\n\n"
                        + "部品番号：" + parts_no + "\n\n"
                        + "を中断します。宜しいですか？");
        if( flag ) {
            obj.value = 'break';
            return true;
        } else {
            return false;
        }
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
    
    /***** ControlForm の Submit メソッド 二重送信対策 *****/
    function ControlFormSubmit(radioObj, formObj)
    {
        radioObj.checked = true;
        formObj.submit();
        return false;       // ←これが二重Submitの対策
    }
    
    /***** ControlForm の入力チェックをしてAjax実行 *****/
    function checkANDexecute(flg)
    {
        // confirm("お気に入りにアイコンを追加します。\n\n宜しいですか？");
            if (flg == 1) {
                this.AjaxLoadTable("List", "showAjax");
            } else if (flg == 2){
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("ListWin", "showAjax");
            } else if (flg == 3) {
                this.parameter += "&requireDate=yes"
                this.AjaxLoadTable("List", "showAjax");
            } else if (flg == 4){
                this.parameter += "&requireDate=yes"
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("ListWin", "showAjax");
            } else if (flg == 5){
                this.parameter += "&noMenu=yes";
                this.AjaxLoadPITable("ListWin", "showAjax");
            } else if (flg == 6){        // 通達発効状況照会用
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("NotiWin", "showAjax");
            } else if (flg == 7){        // 営繕状況照会用
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("EizWin", "showAjax");
            } else {
                this.AjaxLoadTable("List", "showAjax");
            }
            // 点滅のメッセージを変更する
            // this.blink_msg = "部品番号";
            // this.stop_blink();
        return false;   // 実際にsubmitはさせない
    }
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    function AjaxLoadTable(showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("monitoring_absence_Main.php"+parm, 500, 400);
            return;
        }
        // 通達発効状況照会用
        if (showMenu == "NotiWin") {    // 別ウィンドウで表示
            this.win_open("notification.php"+parm, 1100, 600);
            return;
        }
        // 営繕状況照会用
        if (showMenu == "EizWin") {    // 別ウィンドウで表示
            this.win_open("notification_eizen.php"+parm, 1200, 600);
            return;
        }
        /***
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        ***/
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("ご使用のブラウザーは未対応です。\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "monitoring_absence_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    function AjaxLoadPITable(showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("monitoring_pi_Main.php"+parm, 1000, 600);
            return;
        }
        /***
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        ***/
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("ご使用のブラウザーは未対応です。\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "monitoring_pi_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
/*
}   // class monitoring END  */

///// インスタンスの生成
var Monitoring = new monitoring();
// blink_disp()メソッド内で使用するグローバル変数のセット
var blink_flag = 1;

