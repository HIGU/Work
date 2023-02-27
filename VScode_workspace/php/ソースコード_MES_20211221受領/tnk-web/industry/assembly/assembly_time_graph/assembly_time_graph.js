//////////////////////////////////////////////////////////////////////////////
// 組立のライン別工数 各種グラフ               MVC View部(JavaScriptクラス) //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/12 Created    assembly_time_graph.js                             //
// 2006/05/24 stop_blink()メソッドを追加し、グラフ表示時は点滅を止める      //
// 2006/07/08 メンバーのblink_id_name の初期値を設定 設定される前の呼出対応 //
// 2006/09/27 グラフタイプ(工数計算方法)のオプション(工数日割り計算)追加    //
// 2006/11/02 グラフ画像の倍率指定の追加 targetScale                        //
// 2007/01/16 過去工数の表示ON/OFF追加 targetPastData checkConditionForm()  //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     assembly_time_graph class base_class の拡張クラスの定義            *
/****************************************************************************
class assembly_time_graph extends base_class
*/
///// スーパークラスの継承
assembly_time_graph.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function assembly_time_graph()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "グラフを作成する条件を指定して下さい。"; //     〃      , checkANDexecute(), viewClear()
    this.intervalID;                                    // 点滅用のintervalID
    this.blink_id_name = "blink_item";                  // 点滅対象の ID名 ID='???' 初期値はDefault値
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    assembly_time_graph.prototype.set_focus = function (obj, status)
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
    assembly_time_graph.prototype.blink_disp = function (id_name)
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
    assembly_time_graph.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    assembly_time_graph.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    assembly_time_graph.prototype.win_open = function (url, w, h)
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
    assembly_time_graph.prototype.winActiveChk = function ()
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
    assembly_time_graph.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm の入力チェックメソッド(開始日・終了日・部品番号) *****/
    assembly_time_graph.prototype.checkConditionForm = function (obj)
    {
        if (!obj.targetDateYM.value) {
            alert("開始年月日(YYYYMM)が入力されていません！");
            obj.targetDateYM.focus();
            // obj.targetDateYM.select();
            return false;
        }
        if (obj.targetDateYM.value.length != 6) {
            alert("開始年月日(YYYYMM)の桁数は８桁です。");
            obj.targetDateYM.focus();
            // obj.targetDateYM.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateYM.value)) {
            alert("開始年月日(YYYYMM)は数字で入力して下さい。");
            obj.targetDateYM.focus();
            // obj.targetDateYM.select();
            return false;
        }
        // obj.targetLine.value = obj.targetLine.value.toUpperCase();
        // if (obj.targetLine.value.length != 4) {
        if (obj.lineView.value.length <= 0) {
            alert("ライン番号が指定されていません。");
            obj.elements["targetLine[]"].focus();
            // obj.targetLine.focus();
            // obj.targetLine.select();
            return false;
        }
        if (!this.isDigit(obj.targetSupportTime.value)) {
            alert("持ち工数は数字で入力して下さい。");
            obj.targetSupportTime.focus();
            // obj.targetSupportTime.select();
            return false;
        }
        /************
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
        ************/
        if ( (obj.targetScale.value >= 0.3) && (obj.targetScale.value <= 1.7) ) {
            obj.exec.focus();       // obj.targetProcess のフォーカスを外すため
        } else {
            alert("倍率指定が不正です。");
            obj.targetScale.focus();
            return false;
        }
        this.parameter  = "&targetDateYM=" + obj.targetDateYM.value;
        this.parameter += "&targetSupportTime=" + obj.targetSupportTime.value;
        this.parameter += "&targetGraphType=" + obj.targetGraphType.value;
        this.parameter += "&targetScale=" + obj.targetScale.value;
        // this.parameter += "&targetProcess=" + obj.targetProcess.value;
        // this.parameter += "&targetLine=" + obj.targetLine.value;
        this.setTargetLineArray(obj.elements["targetLine[]"]);
        if (obj.targetPastData.checked) {
            this.parameter += "&targetPastData=1";
        }
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    assembly_time_graph.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("Graph", "showAjax");
            // 点滅のメッセージを変更する
            this.blink_msg = "グラフのバーをクリックすれば明細を表示します。";
            this.stop_blink();
        }
        return false;   // 実際にsubmitはさせない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    assembly_time_graph.prototype.AjaxLoadTable = function (showMenu, location)
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
            xmlhttp.open("GET", "assembly_time_graph_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    assembly_time_graph.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        this.blink_msg = "グラフを作成する条件を指定して下さい。";
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    assembly_time_graph.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** lineView の表示用コピーメソッド *****/
    assembly_time_graph.prototype.lineViewCopy = function (obj)
    {
        document.ConditionForm.lineView.value = "";
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                if (document.ConditionForm.lineView.value == "") {
                    document.ConditionForm.lineView.value += obj.options[i].text;
                } else {
                    document.ConditionForm.lineView.value += (", " + obj.options[i].text);
                }
            }
        }
    }
    
    /***** ConditionForm.targetLine[] の配列データをGETパラメーターにセット *****/
    assembly_time_graph.prototype.setTargetLineArray = function (obj)
    {
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                                // URLエンコード処理 2006/11/06 追加            以下のvalue→textにも出来る
                this.parameter += "&targetLine" + escape("[]") + "=" + obj.options[i].value;
            }
        }
    }
    
    return this;    // Object Return
    
}   /* class assembly_time_graph END  */


///// インスタンスの生成
var AssemblyTimeGraph = new assembly_time_graph();

