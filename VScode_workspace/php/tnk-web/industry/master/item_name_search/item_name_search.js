//////////////////////////////////////////////////////////////////////////////
// アイテムマスターの品名による前方・部分検索 MVC View部(JavaScriptクラス)  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/10 Created    item_name_search.js                                //
// 2006/04/14 最初の空白削除の正規表現を /^ +/i /^　+/i へ変更              //
//            viewClear()メソッド実行時に targetItemName.value もクリア     //
// 2006/05/22 材質によるマスター検索を追加 targetItemMaterial               //
// 2006/05/23 在庫チェックオプションを追加 targetStockOption 実行時点滅をoff//
// 2006/07/05 メンバーのblink_id_name の初期値を設定 設定される前の呼出対応 //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*          item_name_search class base_class の拡張クラスの定義            *
/****************************************************************************
class item_name_search extends base_class
*/
///// スーパークラスの継承
item_name_search.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function item_name_search()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "品名または材質に検索文字を入れてEnterキーか実行ボタンを押して下さい。";
                                                        //     〃      , checkANDexecute(), viewClear()
    this.intervalID;                                    // 点滅用のintervalID
    this.blink_id_name = "blink_item";                  // 点滅対象の ID名 ID='???' 初期値はDefault値
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    item_name_search.prototype.set_focus = function (obj, status)
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
    
    /***** 点滅表示メソッド *****/
    /***** blink_flg Private property 下の例は0.5秒毎に点滅 *****/
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    item_name_search.prototype.blink_disp = function (id_name)
    {
        this.blink_id_name = id_name;
        if (this.blink_flag == 1) {
            // 初期値をプロパティで指定したため以下をコメント
            // this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** 点滅ストップメソッド *****/
    item_name_search.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    item_name_search.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    item_name_search.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, '', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    item_name_search.prototype.winActiveChk = function ()
    {
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
    item_name_search.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm の入力チェックメソッド(開始日・終了日・部品番号) *****/
    item_name_search.prototype.checkConditionForm = function (obj)
    {
        // 品名部
        obj.targetItemName.value = obj.targetItemName.value.toUpperCase();
        obj.targetItemName.value = obj.targetItemName.value.replace(/^ +/i, "");    // 最初の半角スペース
        obj.targetItemName.value = obj.targetItemName.value.replace(/^　+/i, "");   // 最初の全角スペース
        // 材質部
        obj.targetItemMaterial.value = obj.targetItemMaterial.value.toUpperCase();
        obj.targetItemMaterial.value = obj.targetItemMaterial.value.replace(/^ +/i, "");    // 最初の半角スペース
        obj.targetItemMaterial.value = obj.targetItemMaterial.value.replace(/^　+/i, "");   // 最初の全角スペース
        ///// 検索 品名と材質のチェック
        if ( (!obj.targetItemName.value) && (!obj.targetItemMaterial.value) ) {
            alert("品名か材質のどちらか一方を必ず入力して下さい！");
            obj.targetItemName.focus();
            obj.targetItemName.select();
            return false;
        }
        if ( (obj.targetItemName.value) && (obj.targetItemMaterial.value) ) {
            if (confirm("品名と材質のどちらも入力されていますので\n\n品名が優先されますが宜しいですか？")) {
                // return true;
            } else {
                obj.targetItemName.focus();
                obj.targetItemName.select();
                return false;
            }
        }
        ///// 製品グループのチェック
        switch (obj.targetDivision.value) {
        case "A" :  // すべて
        case "C" :
        case "L" :
        case "T" :
        case "O" :  // OTHER その他 完成入庫分
            obj.exec.focus();       // obj.targetDivision のフォーカスを外すため
            break;
        default :
            alert("製品区分が不正です。");
            obj.targetDivision.focus();
            return false;
        }
        ///// 在庫チェックオプションのチェック
        switch (obj.targetStockOption.value) {
        case "3" :  // 在庫がある物
        case "2" :  // 在庫経歴がある物
        case "1" :  // 在庫マスターがある物
        case "0" :  // 在庫を無視する
            obj.exec.focus();       // obj.targetDivision のフォーカスを外すため
            break;
        default :
            alert("在庫チェックオプションが不正です。");
            obj.targetStockOption.focus();
            return false;
        }
        // ２バイト文字が入力されるのでエンコードが必要なのだがescape()はAjaxでは使用しない
        this.parameter  = "&targetItemName="        + obj.targetItemName.value;
        this.parameter += "&targetItemMaterial="    + obj.targetItemMaterial.value;
        this.parameter += "&targetDivision="        + obj.targetDivision.value;
        this.parameter += "&targetStockOption="     + obj.targetStockOption.value;
        this.parameter += "&targetLimit="           + obj.targetLimit.value;
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    item_name_search.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("List", "showAjax");
            // 点滅のメッセージを変更する
            this.blink_msg = "部品番号をクリックすれば在庫経歴を表示します。項目クリックでソートします。";
            this.stop_blink();
        }
        // 品名の入力欄にフォーカス
        obj.targetItemName.focus();
        return false;   // 実際にsubmitはさせない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    item_name_search.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
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
            xmlhttp.open("GET", "item_name_search_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    item_name_search.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        document.ConditionForm.targetItemName.value = "";
        document.ConditionForm.targetItemMaterial.value = "";
        // 点滅のメッセージを初期状態に戻す
        this.blink_msg = "品名または材質に検索文字を入れてEnterキーか実行ボタンを押して下さい。";
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    item_name_search.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** item_name_search_ViewHeader.html用のソート項目 強調 表示 メソッド *****/
    item_name_search.prototype.highlight = function ()
    {
        if (location.search.substr(1, 10) == "item=parts") {
            // document.getElementById("parts").style.color = "white";
            document.getElementById("parts").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=name") {
            // document.getElementById("name").style.color = "white";
            document.getElementById("name").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 13) == "item=material") {
            // document.getElementById("date").style.color = "white";
            document.getElementById("date").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 11) == "item=parent") {
            // document.getElementById("in_pcs").style.color = "white";
            document.getElementById("in_pcs").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=date") {
            // document.getElementById("stock").style.color = "white";
            document.getElementById("stock").style.backgroundColor = "#ffffc6";
        }
    }
    
    return this;    // Object Return
    
}   /* class item_name_search END  */


///// インスタンスの生成
var ItemNameSearch = new item_name_search();

