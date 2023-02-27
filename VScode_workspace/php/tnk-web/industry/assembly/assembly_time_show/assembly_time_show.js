//////////////////////////////////////////////////////////////////////////////
// 組立の登録工数と実績工数の比較 照会         MVC View部(JavaScriptクラス) //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created    assembly_time_show.js                              //
// 2006/05/19 登録工数のみの表示機能を追加 regOnly                          //
// 2006/05/20 winActiveChk()メソッドでNN場合は、部品のフォーカスobj.focus() //
//            だけではActiveにならないためwindow.focus()を必ず最後に入れる  //
// 2007/06/17 regOnlyの場合の usedTime, workerCountをhidden属性で追加       //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     assembly_time_show class base_class の拡張クラスの定義              *
/****************************************************************************
class assembly_time_show extends base_class
*/
///// スーパークラスの継承
assembly_time_show.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function assembly_time_show()
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
    assembly_time_show.prototype.set_focus = function (obj, status)
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
    assembly_time_show.prototype.blink_disp = function (id_name)
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
    assembly_time_show.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    assembly_time_show.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('instanceObj.winActiveChk(targetFocusObj)',50)">*****/
    assembly_time_show.prototype.winActiveChk = function (obj)
    {
        if (document.all) {     // IEなら
            if (document.hasFocus() == false) {     // IE5.5以上で使える
                if (obj) {  // 指定されているか又は、存在するか？
                    obj.focus();
                } else {
                    window.focus();
                }
                return;
            }
            return;
        } else {                // NN ならとワリキッテ
            if (obj) {
                obj.focus();
            }
            window.focus();
            return;
        }
    }
    
    /***** 指定の大きさのモーダルダイアログを表示する *****/
    /***** IE 専用なのと Windows XP SP2 ではセキュリティの警告が出る *****/
    /***** ダイアログ内でリクエストを出す場合はフレームを切って行う *****/
    assembly_time_show.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** ConditionForm の入力チェックメソッド(開始日・終了日・部品番号) *****/
    assembly_time_show.prototype.checkConditionForm = function (obj)
    {
        obj.targetPlanNo.value = obj.targetPlanNo.value.toUpperCase();
        if (!obj.targetPlanNo.value) {
            alert("計画番号が入力されていません！");
            obj.targetPlanNo.focus();
            obj.targetPlanNo.select();
            return false;
        }
        if (obj.targetPlanNo.value.length != 8) {
            alert("計画番号の桁数は８桁です。");
            obj.targetPlanNo.focus();
            obj.targetPlanNo.select();
            return false;
        }
        this.parameter  = "&targetPlanNo=" + obj.targetPlanNo.value;
        this.parameter += "&regOnly=" + obj.regOnly.value;
        this.parameter += "&usedTime=" + obj.usedTime.value;
        this.parameter += "&workerCount=" + obj.workerCount.value;
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    assembly_time_show.prototype.checkANDexecute = function (obj)
    {
        if (this.checkConditionForm(obj)) {
            this.AjaxLoadTable("ListTable", "showAjax");
        }
        return false;   // 実際にsubmitはさせない
    }
    
    /***** 登録工数の登録番号クリックによる工程明細照会 Ajax実行 *****/
    assembly_time_show.prototype.processExecute = function (assy_no, reg_no)
    {
        this.parameter  = "&targetAssyNo=" + assy_no + "&targetRegNo=" + reg_no;
        this.AjaxLoadTable("ProcessTable", "showAjax2");
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    assembly_time_show.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu // tableのみ抽出
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
            xmlhttp.open("GET", "assembly_time_show_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    assembly_time_show.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    assembly_time_show.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class assembly_time_show END  */


///// インスタンスの生成
var AssemblyTimeShow = new assembly_time_show();

