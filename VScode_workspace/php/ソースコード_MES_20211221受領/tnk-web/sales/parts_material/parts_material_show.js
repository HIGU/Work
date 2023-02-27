//////////////////////////////////////////////////////////////////////////////
// 部品売上げの材料費(購入費)の 照会  (ベース) MVC View部(JavaScriptクラス) //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/15 Created    parts_material_show.js                             //
//            メソッドをfunction()無名関数(擬似関数)の記述スタイルへ変更    //
// 2006/02/20 結果表示領域のクリアーメソッドを追加                          //
// 2006/02/21 WaitMessageをメソッドによる実装 → 更にAjaxLoadTable()に追加  //
// 2006/03/02 checkANDexecute()メソッドの return false の位置を変更         //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     parts_material_show class base_class の拡張クラスの定義              *
/****************************************************************************
class parts_material_show extends base_class
*/
///// スーパークラスの継承
parts_material_show.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function parts_material_show()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "";                               // 〃
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    parts_material_show.prototype.set_focus = function (obj, status)
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
    parts_material_show.prototype.blink_disp = function (id_name)
    {
        if (this.blink_flag == 1) {
            this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    parts_material_show.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    parts_material_show.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    parts_material_show.prototype.winActiveChk = function ()
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
    parts_material_show.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm の入力チェックメソッド(開始日・終了日・部品番号) *****/
    parts_material_show.prototype.checkConditionForm = function (obj)
    {
        obj.targetItemNo.value = obj.targetItemNo.value.toUpperCase();
        if (!obj.targetDateStr.value.length) {
            alert("開始日が入力されていません。");
            obj.targetDateStr.focus();
            return false;
        }
        if (!this.isDigit(obj.targetDateStr.value)) {
            alert("開始日に数字以外が入力されています。");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (obj.targetDateStr.value.length != 8) {
            alert("開始日が８桁でありません。");
            obj.targetDateStr.focus();
            return false;
        }
        if (!obj.targetDateEnd.value.length) {
            alert("終了日が入力されていません。");
            obj.targetDateEnd.focus();
            return false;
        }
        if (!this.isDigit(obj.targetDateEnd.value)) {
            alert("終了日に数字以外が入力されています。");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        if (obj.targetDateEnd.value.length != 8) {
            alert("終了日が８桁でありません。");
            obj.targetDateEnd.focus();
            return false;
        }
        if (obj.targetItemNo.value.length != 0) {
            if (obj.targetItemNo.value.length != 9) {
                alert("製品番号の桁数は９桁です。");
                obj.targetItemNo.focus();
                obj.targetItemNo.select();
                return false;
            }
        }
        this.parameter  = "&showDiv=" + obj.showDiv.value;
        this.parameter += "&targetDateStr=" + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd=" + obj.targetDateEnd.value;
        this.parameter += "&targetSalesSegment=" + obj.targetSalesSegment.value;
        if (obj.targetItemNo) this.parameter += "&targetItemNo=" + obj.targetItemNo.value;
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    parts_material_show.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            // AjaxLoadTableにWaitMessageを追加したため以下をコメントにした。
            // this.WaitMessage();
            // this.AjaxLoadTable("WaitMsg");
            this.AjaxLoadTable("ListTable");
        }
        return false;   // 実際にはSUBMITさせない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    parts_material_show.prototype.AjaxLoadTable = function (showMenu)
    {
        var parm = "?";
        parm += "&showMenu=" + showMenu // tableのみ抽出
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
                document.getElementById("showAjax").innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
                document.getElementById("showAjax").innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "parts_material_show_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    parts_material_show.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    parts_material_show.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class parts_material_show END  */


///// インスタンスの生成
var PartsMaterialShow = new parts_material_show();

