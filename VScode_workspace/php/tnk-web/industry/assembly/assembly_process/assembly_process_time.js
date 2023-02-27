//////////////////////////////////////////////////////////////////////////////
// 組立指示メニューの 着手・完了時間 集計用  MVC View 部 (JavaScriptクラス) //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/17 Created    assembly_process_time.js                           //
// 2005/11/18 クッキーのgroup_noは他のメニューで不具合が出るためDSgroup_noへ//
//            移行するロジックに変更。 同時修正はassembly_process_Main.php  //
// 2005/11/23 ControlFormSubmit()メソッド 二重Submit対策で追加              //
// 2006/05/19 メソッドを擬似function()へ変更 win_open()のwindowNameを固定   //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     assembly_process_time class base_class の拡張クラスの定義            *
/****************************************************************************
class assembly_process_time extends base_class
*/
///// スーパークラスの継承
assembly_process_time.prototype = new base_class();   // base_class の継承
///// Constructer の定義
function assembly_process_time()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "";                               //     〃      , checkANDexecute(), viewClear()
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    assembly_process_time.prototype.set_focus = function (obj, status)
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
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    assembly_process_time.prototype.blink_disp = function (id_name)
    {
        if (this.blink_flag == 1) {
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    assembly_process_time.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    assembly_process_time.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, 'regTime_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);        
        
        /*
        w -= 15; h -= 25;   // 微調整が必要
        window.open(url, 'regClame_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);        
        */
    }
    
    /***** こちらは不適合報告書用 *****/
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    assembly_process_time.prototype.win_openc = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 100; h -= 300;   // 微調整が必要
        window.open(url, 'regClame_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    assembly_process_time.prototype.winActiveChk = function ()
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
    assembly_process_time.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** user_form の入力チェックメソッド(組立着手 作業者入力) *****/
    assembly_process_time.prototype.user_formCheck = function (obj)
    {
        if (obj.user_id.value.length == 0) {
            alert("作業者の社員番号がブランクです。");
            obj.user_id.focus();
            obj.user_id.select();
            return false;
        }
        if (obj.user_id.value.length != 6) {
            alert("作業者の社員番号の桁数は６桁です。");
            obj.user_id.focus();
            obj.user_id.select();
            return false;
        }
        if (!this.isDigit(obj.user_id.value)) {
            alert("作業者の社員番号は数字で入力して下さい。");
            obj.user_id.focus();
            obj.user_id.select();
            return false;
        }
        return true;
    }
    
    /***** start_form の入力チェックメソッド(組立着手 計画番号入力) *****/
    assembly_process_time.prototype.start_formCheck = function start_formCheck(obj)
    {
        obj.plan_no.value = obj.plan_no.value.toUpperCase();
        if (obj.plan_no.value.length == 0) {
            // alert("計画番号がブランクです。");
            obj.plan_no.focus();
            obj.plan_no.select();
            return false;
        }
        if (obj.plan_no.value.length != 8) {
            alert("計画番号の桁数は８桁です。");
            obj.plan_no.focus();
            obj.plan_no.select();
            return false;
        }
        if (!this.isDigit(obj.plan_no.value.substr(2, 6))) {
            alert("計画番号の３文字目以降は数字で入力して下さい。");
            obj.plan_no.focus();
            obj.plan_no.select();
            return false;
        }
        if (obj.plan_no.value.substr(0, 1) == 'Z') {
            // バーコードのための Z を @ へ変換
            obj.plan_no.value = '@' + obj.plan_no.value.substr(1, 7);
        }
        return true;
    }
    
    /***** group_form の入力チェックメソッド(組立グループ作業区の登録・変更) *****/
    assembly_process_time.prototype.group_formCheck = function (obj)
    {
        // obj.group_no.value = obj.group_no.value.toUpperCase();    // 将来のため
        if (obj.Ggroup_no.value.length == 0) {
            alert("グループ番号がブランクです。");
            obj.Ggroup_no.focus();
            obj.Ggroup_no.select();
            return false;
        }
        if (obj.Ggroup_no.value.length > 3) {
            alert("グループ番号の桁数は３桁までです。");
            obj.Ggroup_no.focus();
            obj.Ggroup_no.select();
            return false;
        }
        if (!this.isDigit(obj.Ggroup_no.value)) {
            alert("グループ番号は数字で入力して下さい。");
            obj.Ggroup_no.focus();
            obj.Ggroup_no.select();
            return false;
        }
        if (obj.group_name.value.length == 0) {
            alert("グループ(作業区)名称がブランクです。");
            obj.group_name.focus();
            obj.group_name.select();
            return false;
        }
        if (obj.group_name.value.length > 10) {
            alert("グループ(作業区)名称は１０文字までです。");
            obj.group_name.focus();
            obj.group_name.select();
            return false;
        }
        return true;
    }
    
    /***** group_no を変更してCookieに保存後、画面更新 *****/
    assembly_process_time.prototype.groupChange = function (group_no, url)
    {
        if (this.isDigit(group_no)) {
            if (group_no.length <= 3) {
                this.setCookie('DSgroup_no', group_no);
                location.replace(url);
            }
        }
        return false;
    }
    
    /***** ControlForm の Submit メソッド 二重送信対策 *****/
    assembly_process_time.prototype.ControlFormSubmit = function (radioObj, formObj)
    {
        radioObj.checked = true;
        formObj.submit();
        return false;       // ←これが二重Submitの対策
    }
    
    return this;    // Object Return
    
}   /* class assembly_process_time END  */


///// インスタンスの生成
var AssemblyProcessTime = new assembly_process_time();

