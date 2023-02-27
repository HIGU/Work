//////////////////////////////////////////////////////////////////////////////
// 適正在庫数の照会 直近三年間の出荷数÷３×２ MVC View部(JavaScriptクラス) //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/06/17 Created    reasonable_stock.js                                //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     reasonable_stock class base_class の拡張クラスの定義            *
/****************************************************************************
class reasonable_stock extends base_class
*/
///// スーパークラスの継承
reasonable_stock.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function reasonable_stock()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "製品グループと在庫基準年月を指定して下さい。";
                                                        //     〃      , checkANDexecute(), viewClear()
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    reasonable_stock.prototype.set_focus = function (obj, status)
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
    reasonable_stock.prototype.blink_disp = function (id_name)
    {
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
    
    /***** オブジェクトの値を大文字変換する *****/
    reasonable_stock.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    reasonable_stock.prototype.win_open = function (url, w, h)
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
    reasonable_stock.prototype.winActiveChk = function ()
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
    reasonable_stock.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm の入力チェックメソッド(開始日・終了日・部品番号) *****/
    reasonable_stock.prototype.checkConditionForm = function (obj)
    {
        switch (obj.targetDivision.value) {
        case "AL" :
        case "CA" :
        case "CH" :
        case "CS" :
        case "LA" :
        case "LH" :
        case "LB" :
        case "OT" : // OTHER その他 完成入庫分
            obj.exec.focus();       // obj.targetDivision のフォーカスを外すため
            break;
        default :
            alert("製品区分が不正です。");
            obj.targetDivision.focus();
            return false;
        }
        this.parameter += "&targetDivision=" + obj.targetDivision.value;
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    reasonable_stock.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("List", "showAjax");
        }
        // 点滅のメッセージを変更する
        this.blink_msg = "部品番号をクリックすれば在庫経歴を表示します。項目クリックでソートします。";
        return false;   // 実際にsubmitはさせない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    reasonable_stock.prototype.AjaxLoadTable = function (showMenu, location)
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
            xmlhttp.open("GET", "reasonable_stock_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    reasonable_stock.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        this.blink_msg = "製品グループと在庫基準年月を指定して下さい。";
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    reasonable_stock.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** reasonable_stock_ViewHeader.html用のソート項目 強調 表示 メソッド *****/
    reasonable_stock.prototype.highlight = function ()
    {
        if (location.search.substr(1, 9) == "item=tana") {
            // document.getElementById("tana").style.color = "white";
            document.getElementById("tana").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=parts") {
            // document.getElementById("parts").style.color = "white";
            document.getElementById("parts").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=name") {
            // document.getElementById("name").style.color = "white";
            document.getElementById("name").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=date") {
            // document.getElementById("date").style.color = "white";
            document.getElementById("date").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 11) == "item=in_pcs") {
            // document.getElementById("in_pcs").style.color = "white";
            document.getElementById("in_pcs").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=stock") {
            // document.getElementById("stock").style.color = "white";
            document.getElementById("stock").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=tanka") {
            // document.getElementById("tanka").style.color = "white";
            document.getElementById("tanka").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=price") {
            // document.getElementById("price").style.color = "white";
            document.getElementById("price").style.backgroundColor = "#ffffc6";
        }
    }
    
    return this;    // Object Return
    
}   /* class reasonable_stock END  */


///// インスタンスの生成
var ReasonableStock = new reasonable_stock();

