//////////////////////////////////////////////////////////////////////////////
// 生産用 部品在庫経歴 照会 部品指定フォーム JavaScriptによる入力チェック   //
// Copyright(C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/12/20 Created  parts_stock_form.js                                  //
//////////////////////////////////////////////////////////////////////////////

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
}

/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ("0">c || c>"9") {
            return false;
        }
    }
    return true;
}

/* 入力文字が数字かどうかチェック 小数点対応 */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1個目かチェック
                cnt_dot++;
            } else {
                return false;       // 2個目は false
            }
        } else {
            if ("0">c || c>"9") {
                return false;
            }
        }
    }
    return true;
}

/***** フォームの入力チェック function *****/
function chk_parts_stock_form(obj) {
    /* 部品番号の大文字変換 & 入力チェック */
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    if (!obj.parts_no.value.length) {
        alert('部品番号が入力されていません！');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (obj.parts_no.value.length != 0) {
        if (obj.parts_no.value.length != 9) {
            alert("部品番号の桁数は９桁です。");
            obj.parts_no.focus();
            obj.parts_no.select();
            return false;
        }
    }
    
    /* 日付範囲指定(下限)の 入力チェック */
    if ( !isDigit(obj.date_low.value) ) {
        alert('日付範囲指定(下限)に数字以外の文字があります！');
        obj.date_low.focus();
        obj.date_low.select();
        return false;
    } else if (obj.date_low.value < 20000401) {
        alert('日付範囲指定(下限)は20000401以上です！');
        obj.date_low.focus();
        obj.date_low.select();
        return false;
    } else if (obj.date_low.value.length != 8) {
        alert('日付範囲指定(下限)の桁数は８桁です！');
        obj.date_low.focus();
        obj.date_low.select();
        return false;
    }
    
    /* 日付範囲指定(上限)の 入力チェック */
    if ( !isDigit(obj.date_upp.value) ) {
        alert('日付範囲指定(上限)に数字以外の文字があります！');
        obj.date_upp.focus();
        obj.date_upp.select();
        return false;
    } else if (obj.date_upp.value.length != 8) {
        alert('日付範囲指定(上限)の桁数は８桁です！');
        obj.date_upp.focus();
        obj.date_upp.select();
        return false;
    }
    
    return true;
}
