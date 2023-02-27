//////////////////////////////////////////////////////////////////////////////
// 特注カプラ冶工具・注意点のチェックルーチン JavaScript custom_form.js     //
//  Copyright(C)2013-2013 N.Ohya norihisa_ooya@nitto-kohki.co.jp            //
// 変更経歴                                                                 //
// 2013/01/24 新規作成 custom_form.js                                       //
//////////////////////////////////////////////////////////////////////////////

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

/* 日付の入力内容をチェック */
function chk_custom_form(obj) {
    obj.assy_no.value = obj.assy_no.value.toUpperCase();
    if (obj.assy_no.value.length != 0) {
        if (obj.assy_no.value.length != 9) {
            alert("製品番号の桁数は９桁です。");
            obj.assy_no.focus();
            obj.assy_no.select();
            return false;
        }
    }
    if (obj.assy_no.value.length == 0) {
        alert("製品番号を入力してください。");
        obj.assy_no.focus();
        obj.assy_no.select();
        return false;
    }
    return true;
}

