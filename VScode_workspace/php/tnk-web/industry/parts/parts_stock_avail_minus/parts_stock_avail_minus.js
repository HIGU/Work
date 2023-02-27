//////////////////////////////////////////////////////////////////////////////
// 部品 在庫・有効利用数マイナスリスト         MVC View 部(JavaScriptクラス)//
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/02 Created    parts_stock_avail_minus.js                         //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*       parts_stock_avail_minus class base_class の拡張クラスの定義        *
/****************************************************************************
class parts_stock_avail_minus extends base_class
*/
///// スーパークラスの継承
parts_stock_avail_minus.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function parts_stock_avail_minus()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "製品グループを選択して下さい。";
                                                        //     〃      , checkANDexecute(), viewClear()
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    parts_stock_avail_minus.prototype.set_focus = function (obj, status)
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
    parts_stock_avail_minus.prototype.blink_disp = function (id_name)
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
    parts_stock_avail_minus.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    parts_stock_avail_minus.prototype.win_open = function (url, w, h)
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
    parts_stock_avail_minus.prototype.winActiveChk = function ()
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
    parts_stock_avail_minus.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm の入力チェックメソッド(製品グループ) *****/
    parts_stock_avail_minus.prototype.checkConditionForm = function (obj)
    {
        // 部品番号を正規表現でマッチングチェック
        /***************
        if (!obj.targetPartsNo.value.match(/^[A-Z]{2}[A-Z0-9]{5}[-#]{1}[A-Z0-9]{1}$/)) {
            alert("部品番号が間違っています！");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        ***************/
        switch (obj.targetDivision.value) {
        case "AL" :
        case "CA" :
        case "CH" :
        case "CS" :
        case "LA" :
        case "LL" :
        case "LB" :
            obj.exec.focus();       // obj.targetDivision のフォーカスを外すため
            break;
        default :
            alert("製品グループを選んで下さい。");
            obj.targetDivision.focus();
            return false;
        }
        this.parameter = "&targetDivision=" + obj.targetDivision.value;
        this.parameter += "&targetMinusItem=" + obj.targetMinusItem.value;
        this.parameter += "&searchPartsNo=" + obj.searchPartsNo.value;
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    parts_stock_avail_minus.prototype.sortClear = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.parameter += "&Action=ClearSort";
            this.AjaxLoadTable("List", "showAjax");
        }
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    parts_stock_avail_minus.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("List", "showAjax");
        }
        // 点滅のメッセージを変更する
        this.blink_msg = "ソートしたい項目をクリックして下さい。";
        return false;   // 実際にsubmitはさせない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    parts_stock_avail_minus.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
        this.parameter = "";    // 初期化(重要)
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
            xmlhttp.open("GET", "parts_stock_avail_minus_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    parts_stock_avail_minus.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        this.blink_msg = "製品グループを選択して下さい。";
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    parts_stock_avail_minus.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** EditFactorForm の入力チェックメソッド(要因項目・要因説明) *****/
    parts_stock_avail_minus.prototype.checkEditFactorForm = function (obj)
    {
        obj.targetFactorName.value = obj.targetFactorName.value.substr(0, 5);
        if (obj.targetFactorName.value.replace(/[ 　]+/g, "") == "") {
            alert("要因項目が入力されていません！");
            obj.targetFactorName.focus();
            obj.targetFactorName.select();
            return false;
        } else if (obj.targetFactorExplanation.value.replace(/[ 　]+/g, "") == "") {
            alert("要因説明が入力されていません！");
            obj.targetFactorExplanation.focus();
            obj.targetFactorExplanation.select();
            return false;
        } else if (!obj.targetFactor) {
            alert("要因番号が存在しません！");
            return false;
        }
        this.parameter = "&Action=EditFactor";
        this.parameter += "&targetFactor=" + obj.targetFactor.value;
        this.parameter += "&targetFactorName=" + obj.targetFactorName.value;
        this.parameter += "&targetFactorExplanation=" + obj.targetFactorExplanation.value;
        this.AjaxLoadTable("FactorMnt", "showAjax");
        return false;
    }
    
    /***** 要因マスターの削除 確認 ＆ 実行 メソッド *****/
    parts_stock_avail_minus.prototype.deleteFactor = function (factor, factor_name)
    {
        if (confirm(factor_name + "\n\nを削除します。宜しいですか？")) {
            // Ajax用パラメーターをセット
            this.parameter = "&Action=DeleteFactor";
            this.parameter += "&targetFactor="   + factor;
            // Ajax通信
        this.AjaxLoadTable("FactorMnt", "showAjax");
        }
    }
    
    /***** 要因マスターの有効・無効の切替 メソッド *****/
    parts_stock_avail_minus.prototype.activeFactor = function (factor, factor_name, active)
    {
        if (confirm(factor_name + "\n\nを 「" + active + "」 にします。宜しいですか？")) {
            // Ajax用パラメーターをセット
            this.parameter = "&Action=ActiveFactor";
            this.parameter += "&targetFactor="   + factor;
            // Ajax通信
            this.AjaxLoadTable("FactorMnt", "showAjax");
        }
    }
    
    /***** 要因マスターの修正のため登録フォームにコピー メソッド *****/
    parts_stock_avail_minus.prototype.copyFactor = function (factor, name, explanation)
    {
        window.form.document.EditFactorForm.targetFactor.value = factor;
        window.form.document.EditFactorForm.targetFactorName.value = name;
        window.form.document.EditFactorForm.targetFactorExplanation.value = explanation;
        window.form.document.EditFactorForm.cancelButton.style.visibility = "visible";
        window.form.document.EditFactorForm.targetFactorName.focus();
        window.form.document.EditFactorForm.targetFactorName.select();
        alert("登録フォームにコピーしました。\n\n修正して登録ボタンを押して下さい。");
    }
    
    /***** 要因マスターのOPTIONSを FactorName→FactorExplanationにコピー メソッド *****/
    parts_stock_avail_minus.prototype.selectOptionsLink = function (obj, obj2)
    {
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                obj2.options[i].selected = true;
            } else {
                obj2.options[i].selected = false;
            }
        }
    }
    
    return this;    // Object Return
    
}   /* class parts_stock_avail_minus END  */


///// インスタンスの生成
var PartsStockAvailMinus = new parts_stock_avail_minus();

