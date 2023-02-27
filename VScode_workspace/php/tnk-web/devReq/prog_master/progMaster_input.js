//////////////////////////////////////////////////////////////////////////////
// プログラム管理 プログラムの登録 MVC View 部 (JavaScriptクラス)           //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created    progMaster_input.js                                //
// 2010/03/05 ディレクトリの入力チェックを追加                              //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*              progMaster_input class テンプレートの拡張クラスの定義       *
/****************************************************************************
class progMaster_input extends base_class
{   */
    ///// スーパークラスの継承
    progMaster_input.prototype = new base_class();   // base_class の継承
    ///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
    var _GDEBUG = false;
    
    ///// Constructer の定義
    function progMaster_input()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // プロパティーの初期化
        this.Gid = false;                               // setTimeout()の戻り値 clearTimeout()で使用する
        this.GpidKey;                                 // HTML内でdocument.ControlForm.pidKey.valueで初期化
        // this.incrementalSearch = false;     // インクリメンタルサーチの実行フラグ    イベントから呼出される関数内では使用できないため
        //this.UpperSwitch;                   // 自動大文字変換する対象をスイッチ切替  グローバル変数へ変更
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        progMaster_input.prototype.blink_disp     = blink_disp;       // 点滅表示メソッド
        progMaster_input.prototype.setFocus       = setFocus;         // 初期フォーカス位置
        progMaster_input.prototype.win_open       = win_open;         // サブウィンドウを中央に表示
        progMaster_input.prototype.winActiveChk   = winActiveChk;     // サブウィンドウのActiveチェック
        progMaster_input.prototype.win_show       = win_show;         // モーダルダイアログを表示(IE専用)
        progMaster_input.prototype.evt_key_chk    = evt_key_chk;      // イベントキー(オーバーライド)自動大文字変換機能と擬似インクリメントサーチ機能を追加
        progMaster_input.prototype.incExecChk     = incExecChk;       // インクリメントサーチ実行部
        progMaster_input.prototype.CheckItemMaster= CheckItemMaster;  // 編集フォームの入力チェック用
        
        return this;    // Object Return
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
    
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    function setFocus(obj)
    {
        if (obj) {
            obj.focus();
        }
        // document.body.focus();   // F2/F12キーを有効化する対応
        // document.mhForm.backwardStack.focus();  // 上記はIEのみのため、こちらに変更しNN対応
        // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
        // document.form_name.element_name.select();
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
    
    /***** 共通キー割当て base_class のメソッドをオーバーライド *****/
    /***** 1.戻るボタン用 F12=123, F2=113 どちらでも使えるように  *****/
    /***** 部品番号の自動大文字変換機能と擬似インクリメントサーチ機能を追加 *****/
    function evt_key_chk(evt)
    {
        // グローバル変数の backward_obj が戻り先のコントロールオブジェクト
        var browser = navigator.appName;
        if (browser.charAt(0) == 'M') {         // IEの場合
            var chk_key = event.keyCode;        // IEではキーコードを調べるには event.keyCode を使う。
        } else {                                // NNの場合を想定
            var chk_key = evt.which;            // NNでは evt.which を使う。(evtはイベントによって呼び出される関数のカッコ内に入れるオブジェクト変数名)
            if (chk_key == 13) {                    // NNで<input>が複数ある場合に改行でsubmitしないための対応
                var work = evt.target + "";             // 文字列変換
                /***** debug 
                alert(work);
                *****/
                if (work.match("Input") == "Input") {   // targetがInputエレメントの時だけsubmitする
                    window.document.ControlForm.submit();
                }
            }
        }
        switch (chk_key) {
        case 113:   // F2
        case 123:   // F12
            backward_obj.submit();
            return true;
        case 112:   // F1   ← これを無効にするには(onHelp='return false')IEのみ
        case 114:   // F3   検索
        case 116:   // F5   更新ボタン
        case 117:   // F6   google
            if (browser.charAt(0) == 'M') {         // IEの場合
                event.keyCode = null;
            } else {                                // NNの場合を想定
                evt.which = null;
            }
            return false;
        default:
            ///// 以下のG_UpperSwitchとG_incrementalSearchはプロパティでは動作しないためグローバル変数へ変更
            if (!G_incrementalSearch) return;    // インクリメンタルサーチの実行判断
            if (this.Gid) {
                clearTimeout(this.Gid);
                this.Gid = false;
            }
            this.Gid = setTimeout("this.incExecChk()", 200);     // 入力が遅い人は500ぐらい?
        }
    }
    
    /***** 擬似インクリメントサーチのための実行メソッドを追加 *****/
    /***** GpidKey はHTML内でdocument.ControlForm.pidKey.valueで初期化 *****/
    function incExecChk()
    {
        if (document.ControlForm.pidKey.value != this.GpidKey) {
            /*****
            document.ControlForm.submit();  // SUBMIT 版
            *****/
            this.GpidKey = document.ControlForm.pidKey.value;
            var parm = "?";
            parm += "pidKey="             + document.ControlForm.pidKey.value;
            parm += "&current_menu=table"   // tableのみ抽出
            parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
            parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
            parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
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
                    document.getElementById("showAjax").innerHTML = xmlhttp.responseText;
                }
            }
            try {
                xmlhttp.open("GET", "progMaster_input_Main.php"+parm);
                xmlhttp.send(null);
            } catch (e) {
                alert(url + "\n\nをオープン出来ません！\n\n" + e);
            }
        }
    }
    
    function CheckItemMaster(obj) {
        if (obj.pid.value.length == 0) {
            alert("プログラムIDがブランクです。");
            obj.pid.focus();
            obj.pid.select();
            return false;
        }
        if (obj.pdir.value.length == 0) {
            alert("ディレクトリがブランクです。");
            obj.pdir.focus();
            obj.pdir.select();
            return false;
        }
        return true;
    }
/*
}   // class progMaster_input END  */


///// インスタンスの生成
var ProgMaster = new progMaster_input();
// blink_disp()メソッド内で使用するグローバル変数のセット
var blink_flag = 1;


