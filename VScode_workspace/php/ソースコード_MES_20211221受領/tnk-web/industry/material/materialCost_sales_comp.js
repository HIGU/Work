//////////////////////////////////////////////////////////////////////////////
// 売上メニューのチェックルーチン JavaScript uriage.js → salse_form.js     //
// 2001/07/07 Copyright(C)2001-2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// 変更経歴                                                                 //
// 2001/07/07 新規作成 uriage.js                                            //
// 2003/10/31 obj.assy_no.value.length!=9を追加 .value.toUpperCase()追加    //
// 2003/12/23 obj.sales_form.value のチェックを追加                         //
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
function chk_sales_form(obj) {
    if (!obj.d_start.value.length) {
        alert("日付の選択開始日が入力されていません。");
        obj.d_start.focus();
        return false;
    }
    if (!isDigit(obj.d_start.value)) {
        alert("開始日付に数字以外のデータがあります。");
        obj.d_start.focus();
        obj.d_start.select();
        return false;
    }
    if (obj.d_start.value.length != 8) {
        alert("日付の開始日が８桁でありません。");
        obj.d_start.focus();
        return false;
    }
    if (!obj.d_end.value.length) {
        alert("日付の選択終了日が選択されていません。");
        obj.d_end.focus();
        return false;
    }
    if (!isDigit(obj.d_end.value)) {
        alert("終了日付に数字以外のデータがあります。");
        obj.d_end.focus();
        obj.d_end.select();
        return false;
    }
    if (obj.d_end.value.length != 8) {
        alert("日付の終了日が８桁でありません。");
        obj.d_end.focus();
        return false;
    }
    obj.assy_no.value = obj.assy_no.value.toUpperCase();
    if (obj.assy_no.value.length != 0) {
        if (obj.assy_no.value.length != 9) {
            alert("製品番号の桁数は９桁です。");
            obj.assy_no.focus();
            obj.assy_no.select();
            return false;
        }
    }
    return true;
}

