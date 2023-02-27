////////////////////////////////////////////////////////////////////////////////
// 食堂メニュー予約                                                           //
//                                            MVC View 部 (JavaScriptクラス)  //
// Copyright (C) 2022-2022 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2022/04/29 Created meal_appli.js                                           //
// 2022/05/07 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
//alert("TEST : ");

// 会社カレンダーの休日情報をセットしておく。
var holiday = "";
function SetHoliday(day)
{
    holiday = day;
//holiday = '["2022-05-25"],["2022-05-31"]'; // TEST
}

// 休日ですか？
function IsHoliday(day)
{
    if( holiday.search((day.substr(0,4)+'-'+day.substr(4,2)+'-'+day.substr(6,2))) != -1 ) {
        return true;
    } else {
        return false;
    }
}

// イベントメニュー日をセット
var event_date = "";
function SetEventDate()
{
    var now = new Date();
    var date = new Date(now.getFullYear(), (now.getMonth()+1), 0);  // 月末
    
    for(var n=0; n<15; n++) {
        event_date = date.getFullYear()+('0'+(date.getMonth()+1)).slice(-2)+('0'+date.getDate()).slice(-2);
        if( date.getDay() == 3 ) {   // 3:水曜日
            if( ! IsHoliday(event_date) ) break;
        }
        date.setDate( date.getDate()-1 );
    }
    event_date = date.getFullYear()+"-"+('0'+(date.getMonth()+1)).slice(-2)+"-"+('0'+date.getDate()).slice(-2);
}

// キャプションは休日ですか？
function IsCapcionHoliday(id)
{
    if( document.getElementById(id).style.color == "red" ) return true;
    
    return false;
}

// 休日ならクリック無効
function CheckHoliday(cap_id, id)
{
    if( IsCapcionHoliday(cap_id) ) {
        var obj = document.getElementById(id).onclick = "";
        obj.onclick = "";
    }
}

var winObj = "";  // win_open()メソッド内で使用する
/***** 指定の大きさのサブウィンドウを中央に表示する *****/
/***** Windows XP SP2 ではセキュリティの警告が出る  *****/
function win_open(url, winName, w, h)
{
    if (!w) w = 964;     // 初期値
    if (!h) h = 708;     // 初期値
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    w -= 10; h -= 30;   // 微調整が必要
    
    if( (winObj) && (!winObj.closed) ){ // メニュー表ウインドウが開かれているか？
        winObj.close();                 // メニュー表ウインドウを閉じる
    }
    
    winObj  = window.open(url, winName, 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}

// メニュー選択初期処理
function InitSelect()
{
    var obj = document.getElementById("id_input_uid");
    if( obj ) { // 
        obj.focus();
        obj.select();
    }
}

// 選択したメニュー画面へ切り替える
function ItemSelect(show_menu)
{
    document.getElementById("id_showMenu").value = show_menu;
    document.getElementsByName("form_main")[0].submit();
}

// 社員番号入力領域で、Enterなら[読込み]を実行
function EnterRead()
{
    if( event.keyCode == 13 || event.key == "Enter" ) {
        OperationClick("read");
    }
}

// 予約ありに、コメント入力されていますか？
function IsComment()
{
/**/
    for(var y=1; y<3; y++){
        for(var x=1; x<6; x++){
            for(var z=1, cnt=0; z<4; z++){
                var obj = document.getElementsByName("menu"+z+"_"+y+"_"+x);
                if(obj[0]) cnt += obj[0].value;
            }
            if( cnt > 0 ) {
                if( document.getElementById("id_comment_"+y+"_"+x).value == "" ) {
                    alert("予約する場合は必ず、理由を入力して下さい。");
                    return false;
                }
            }
        }
    }
/**/
//    alert("予約する場合は必ず、理由を入力して下さい。");
    return true;
}

// 操作ボタンクリック
function OperationClick(operation)
{
    var obj = "";
    switch (operation) {
        case 'read':
            obj = document.getElementById("id_input_uid");
            document.getElementsByName("input_uid")[0].value = ('0'+obj.value).slice(-6); // 入力社員番号をセット
            break;
        case 'cancel':
            document.getElementsByName("input_uid")[0].value = "";
            break;
        case 'save':
            alert("内容を保存します。");
            break;
        default:
            break;
    }
    document.getElementsByName(operation)[0].value = "on";
    
    obj = document.getElementById("id_showMenu");
    if( obj.value == "MenuSelect" ) {
        document.getElementsByName("form_select")[0].submit();
    } else if ( obj.value == "MenuGuest" ) {
        document.getElementsByName("form_guest")[0].submit();
    } else if ( obj.value == "OrderDetail" ) {
        document.getElementsByName("form_detail")[0].submit();
    }
}

// [氏名<=>コード]ボタンクリック
function DetailClick()
{
    var obj = document.getElementsByName("detail");
    if( obj[0].value == "on" ) obj[0].value = ""; else obj[0].value = "on";
    
    var obj = document.getElementsByName("form_detail");
    if(obj[0]) obj[0].submit();
}

//
function GetWeekInfo()
{
    var now = new Date();
    return now.getDay();
}

// 
function GetDate(idx)
{
    var num = idx - GetWeekInfo();
    var now = new Date();
    var date = new Date(now.getFullYear(),('0'+now.getMonth()).slice(-2),('0'+(now.getDate()+num)).slice(-2));
    
    return date.getFullYear()+"-"+('0'+(date.getMonth()+1)).slice(-2)+"-"+('0'+date.getDate()).slice(-2);
}

// 
function GetDateInfo(idx)
{
    var num = idx - GetWeekInfo();
    var now = new Date();
    var date_info = new Date(now.getFullYear(),('0'+now.getMonth()).slice(-2),('0'+(now.getDate()+num)).slice(-2));
    var week = date_info.getDay();
    var yobi = new Array(" (日)"," (月)"," (火)"," (水)"," (木)"," (金)"," (土)");
    
    return ('　'+(date_info.getMonth()+1)).slice(-2)+ "月"+(' '+date_info.getDate()).slice(-2)+"日"+yobi[week];
}

// 
function SetDateInfo(id, idx)
{
    var num = idx - GetWeekInfo();
    var now = new Date();
    var date_info = new Date(now.getFullYear(),('0'+now.getMonth()).slice(-2),('0'+(now.getDate()+num)).slice(-2));
    var week = date_info.getDay();
    var yobi = new Array(" (日)"," (月)"," (火)"," (水)"," (木)"," (金)"," (土)");
    
    var obj = document.getElementById(id);
    obj.innerHTML = ('　'+(date_info.getMonth()+1)).slice(-2)+ "月"+(' '+date_info.getDate()).slice(-2)+"日"+yobi[week];
    
    var date = date_info.getFullYear()+"-"+('0'+(date_info.getMonth()+1)).slice(-2)+"-"+('0'+date_info.getDate()).slice(-2);
    if( holiday.search(date) != -1 ) {
        obj.style.color = "red";
    }
}

// マウスカーソルをポインタ
function CursorPointer(obj)// this.cursor = 'pointer'
{
    obj.style.cursor = 'pointer';
}

// チェックボックスのチェックと背景色を変更する
function ChangeBkColoer(id)
{
    var obj = document.getElementById(id+'_c');
    var obj2 = document.getElementById(id); // メニュー名
    if( obj.value == 0 ) {
        obj2.style.background = "";         // ダミー
        obj2.style.background = "skyblue";
        obj.value = 1;  // 数量を post する隠し要素へセット
    } else {
        obj2.style.background = "skyblue";  // ダミー
        obj2.style.background = "";
        obj.value = 0;  // 数量を post する隠し要素へセット
    }
}

//
function CountChange(name, flg)
{
    var obj = document.getElementById('id_'+name+'_c'); // 表示数量
    var cnt = obj.innerHTML;
    if( flg == "up" ) {
        if( cnt < 98 ) cnt++;
    } else {
        if( cnt > 0 ) cnt--;
    }
    document.getElementsByName(name)[0].value = cnt;    // 数量を post する隠し要素へセット
    
    var obj2 = document.getElementById('id_'+name);     // メニュー名
    if( cnt > 0 ) {
        obj.style.background = "";          // ダミー
        obj2.style.background = "";         // ダミー
        obj.style.background = "skyblue";
        obj2.style.background = "skyblue";
    } else {
        obj.style.background = "skyblue";   // ダミー
        obj2.style.background = "skyblue";  // ダミー
        obj.style.background = "";
        obj2.style.background = "";
    }
    obj.innerHTML = cnt;
    return false;
}

// キャンセル
function OrderDelete(idx, name, menu)
{
    var date = GetDateInfo(idx);
    
    var msg = "";
    msg += "【重要】発注済みの場合、ナスココ株式会社へ\n";
    msg += "　　　キャンセルすることをメールして下さい。\n\n";
    msg += "※※※ TEST中 ※※※\n\n";
    msg += "日　付："+date+"\n\n";
    msg += "氏　名：　"+name+"\n\n";
    msg += "品　名：　"+menu+"\n\n";
    msg += "上記内容を取り消してもよろしいですか？";
    if( ! confirm(msg) ) return ;
    
    document.getElementById('id_delete_date').value = GetDate(idx);
    document.getElementById('id_delete_uid').value = "";
    document.getElementById('id_delete_who').value = "";
    document.getElementById('id_delete_menu').value = "";
    
    document.getElementsByName("form_detail")[0].submit();
}


//alert("TEST : ");
//alert("TEST : ");
//alert("TEST : ");

function CangeUID(str, name)   // ユーザー切替（テスト用）
{
    document.getElementsByName("login_uid")[0].value = str;
    document.getElementsByName(name)[0].submit();
}

function MsgView(str)   // メッセージ表示
{
    if( str ) alert(str);
}

// ============================================================================
//alert("TEST : ");
// ============================================================================
// 社員番号入力チェック
function InputCheck(obj){
    var str1=obj.value;

    if(isDigit(str1)) {
//        alert("数値");
        return str1;
    }else{
//        alert("文字"+str);
        return getDigit(str1);
    }
}

function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++){
        c = str.charAt(i);
        if(("0" > c) || ("9" < c)) {
            return false;
        }
    }
    return true;
}

function getDigit(str) {
    var len = str.length;
    var c, str1="";
    for (i=0; i<len; i++){
        c = str.charAt(i);
        if(("0" > c) || ("9" < c)) {
            continue;
        }
        str1 += c;
    }
    return str1;
}

/***** 点滅表示メソッド *****/
/***** blink_flg Private property 下の例は0.5秒毎に点滅 *****/
/***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
var blink_flag = 1;
var blink_msg  = "";
function blink_disp(id_name)
{
    if( blink_flag == 1 ) {
        // 初期値をプロパティで指定したため以下をコメント
        // this.blink_msg = document.getElementById(id_name).innerHTML;
        blink_msg = document.getElementById(id_name).innerHTML;
        document.getElementById(id_name).innerHTML = "★★★★★★★★★";
        blink_flag = 2;
    } else {
        document.getElementById(id_name).innerHTML = blink_msg;
        blink_flag = 1;
    }
}
