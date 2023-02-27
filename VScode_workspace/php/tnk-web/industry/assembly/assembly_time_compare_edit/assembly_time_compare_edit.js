//////////////////////////////////////////////////////////////////////////////
// 組立の完成一覧より実績工数と登録工数の比較  MVC View部(JavaScriptクラス) //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created    assembly_time_compare.js                           //
// 2006/03/12 win_open()メソッドをresizable=yesにして名前無しに変更         //
// 2006/03/13 製品区分の選択として targetDivision を追加                    //
// 2006/05/10 手作業・自動機・外注・全体 別に照会オプションを追加           //
// 2006/08/31 項目ソート機能 追加による highlight() メソッドを実装          //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     assembly_time_compare class base_class の拡張クラスの定義            *
/****************************************************************************
class assembly_time_compare extends base_class
*/
///// スーパークラスの継承
assembly_time_compare.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function assembly_time_compare()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "完成日の範囲を指定して下さい。"; //     〃      , checkANDexecute(), viewClear()
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    assembly_time_compare.prototype.set_focus = function (obj, status)
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
    assembly_time_compare.prototype.blink_disp = function (id_name)
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
    assembly_time_compare.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    assembly_time_compare.prototype.win_open = function (url, w, h)
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
    assembly_time_compare.prototype.winActiveChk = function ()
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
    assembly_time_compare.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm の入力チェックメソッド(開始日・終了日・部品番号) *****/
    assembly_time_compare.prototype.checkConditionForm = function (obj)
    {
        // obj.targetDateStr.value = obj.targetDateStr.value.toUpperCase();
        if (!obj.targetDateStr.value) {
            alert("開始年月日(YYYYMMDD)が入力されていません！");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (obj.targetDateStr.value.length != 8) {
            alert("開始年月日(YYYYMMDD)の桁数は８桁です。");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateStr.value)) {
            alert("開始年月日(YYYYMMDD)は数字で入力して下さい。");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (!obj.targetDateEnd.value) {
            alert("開始年月日(YYYYMMDD)が入力されていません！");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        if (obj.targetDateEnd.value.length != 8) {
            alert("開始年月日(YYYYMMDD)の桁数は８桁です。");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateEnd.value)) {
            alert("開始年月日(YYYYMMDD)は数字で入力して下さい。");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        switch (obj.targetDivision.value) {
        case "AL" :
        case "CA" :
        case "CH" :
        case "CS" :
        case "LA" :
        case "LH" :
        case "LB" :
            obj.exec.focus();       // obj.targetDivision のフォーカスを外すため
            break;
        default :
            alert("製品区分が不正です。");
            obj.targetDivision.focus();
            return false;
        }
        switch (obj.targetProcess.value) {
        case "H" :
        case "M" :
        case "G" :
        case "A" :
            obj.exec.focus();       // obj.targetProcess のフォーカスを外すため
            break;
        default :
            alert("工程区分が不正です。");
            obj.targetProcess.focus();
            return false;
        }
        this.parameter  = "&targetDateStr="  + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd="  + obj.targetDateEnd.value;
        this.parameter += "&targetAssyNo="   + obj.targetAssyNo.value;
        this.parameter += "&targetDivision=" + obj.targetDivision.value;
        this.parameter += "&targetProcess="  + obj.targetProcess.value;
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    assembly_time_compare.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("List", "showAjax");
        }
        // 点滅のメッセージを変更する
        this.blink_msg = "実績工数か登録工数をクリックすれば明細を表示します。";
        return false;   // 実際にsubmitはさせない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    assembly_time_compare.prototype.AjaxLoadTable = function (showMenu, location)
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
            xmlhttp.open("GET", "assembly_time_compare_edit_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    assembly_time_compare.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        this.blink_msg = "完成日の範囲を指定して下さい。";
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    assembly_time_compare.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** long_holding_parts_ViewHeader.html用のソート項目 強調 表示 メソッド *****/
    assembly_time_compare.prototype.highlight = function ()
    {
        if (location.search.substr(1, 9) == "item=plan") {
            document.getElementById("plan").style.color = "#000000";
            document.getElementById("plan").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=assy") {
            document.getElementById("assy").style.color = "#000000";
            document.getElementById("assy").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=name") {
            document.getElementById("name").style.color = "#000000";
            document.getElementById("name").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 8) == "item=pcs") {
            document.getElementById("pcs").style.color = "#000000";
            document.getElementById("pcs").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 9) == "item=date") {
            document.getElementById("date").style.color = "#000000";
            document.getElementById("date").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 10) == "item=in_no") {
            document.getElementById("in_no").style.color = "#000000";
            document.getElementById("in_no").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 8) == "item=res") {
            document.getElementById("res").style.color = "#000000";
            document.getElementById("res").style.backgroundColor = "#ffffc6";
        } else if (location.search.substr(1, 8) == "item=reg") {
            document.getElementById("reg").style.color = "#000000";
            document.getElementById("reg").style.backgroundColor = "#ffffc6";
        } else {
            document.getElementById("line").style.color = "#000000";
            document.getElementById("line").style.backgroundColor = "#ffffc6";
        }
    }
    
    return this;    // Object Return
    
}   /* class assembly_time_compare END  */


///// インスタンスの生成
var AssemblyTimeCompare = new assembly_time_compare();

function window.onscroll()
{
    var w1 = document.getElementsByName("header");
    var w2 = document.getElementsByName("list");

    if( document.body.scrollLeft == 1) {
        var t = document.body.Element.scrollLeft;
//        alert(document.body.scrollLeft + " : " + t);
    }
}
