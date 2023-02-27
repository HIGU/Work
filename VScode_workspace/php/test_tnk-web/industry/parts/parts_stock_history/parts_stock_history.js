//////////////////////////////////////////////////////////////////////////////
// 部品 在庫 経歴 照会 (ＭＶＣ版)             MVC View部 (JavaScriptクラス) //
// Copyright (C) 2004-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/20 Created    parts_stock_history.js                             //
// 2007/03/16 オリジナルはparts_stock_view.php で                           //
//                                完全なＭＶＣモデルでコーディングした。    //
//            変更経歴は backup/parts_stock_view.php を参照すること。       //
// 2007/03/22 queryString パラメーター追加 Ajaxロード時に使用               //
// 2007/06/22 checkConditionForm()メソッドにnoMenuのチェックとparameter追加 //
// 2009/04/02 部品番号の最後が#の時に番号間違いになるのを訂正          大谷 //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*         parts_stock_history class base_class の拡張クラスの定義             *
/****************************************************************************
class parts_stock_history extends base_class
*/
///// スーパークラスの継承
parts_stock_history.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function parts_stock_history()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "部品番号";                       //     〃      , checkANDexecute(), viewClear()
    this.intervalID;                                    // 点滅用のintervalID
    this.blink_id_name;                                 // 点滅対象の ID名 ID='???'
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    parts_stock_history.prototype.set_focus = function (obj, status)
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
    parts_stock_history.prototype.blink_disp = function (id_name)
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
    parts_stock_history.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    parts_stock_history.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    parts_stock_history.prototype.win_open = function (url, w, h)
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
    parts_stock_history.prototype.winActiveChk = function ()
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
    parts_stock_history.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm の入力チェックメソッド *****/
    parts_stock_history.prototype.checkConditionForm = function (obj, queryString)
    {
        obj.targetPartsNo.value = obj.targetPartsNo.value.toUpperCase();
        if (!obj.targetPartsNo.value) {
            alert("部品番号が入力されていません！");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        if (obj.targetPartsNo.value.length != 9) {
            alert("部品番号の桁数は９桁です。");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        // 部品番号を正規表現でマッチングチェック
        if (!obj.targetPartsNo.value.match(/^[A-Z]{2}[A-Z0-9]{5}[-#]{1}[A-Z0-9#]{1}$/)) {
            alert("部品番号が間違っています！");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        this.parameter  = "&targetPartsNo=" + obj.targetPartsNo.value;
        if (obj.noMenu.value != "") {
            this.parameter += "&noMenu=" + obj.noMenu.value;
        }
        this.parameter += "&" + queryString;
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    parts_stock_history.prototype.checkANDexecute = function (obj, flg, queryString)
    {
        if (this.checkConditionForm(obj, queryString)) {
            if (flg == 1) {
                this.AjaxLoadTable("List", "showAjax");
            } else {
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("ListWin", "showAjax");
            }
            // 点滅のメッセージを変更する
            // this.blink_msg = "部品番号";
            // this.stop_blink();
        }
        return false;   // 実際にsubmitはさせない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    parts_stock_history.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("parts_stock_history_Main.php"+parm, 950, 700);
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
            xmlhttp.open("GET", "parts_stock_history_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    parts_stock_history.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        // this.blink_msg = "部品番号";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    parts_stock_history.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class parts_stock_history END  */


///// インスタンスの生成
var PartsStockHistory = new parts_stock_history();

