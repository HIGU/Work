//////////////////////////////////////////////////////////////////////////////
// 単価経歴より販売価格(仕切単価)設定 フォーム JavaScriptによる入力チェック //
// Copyright(C) 2004-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/11/19 Created  parts_sales_price_form.js                            //
// 2010/06/25 日付の基準日の最大値を20380331から20990331ﾆ変更          大谷 //
// 2013/01/30 プログラム変更の為日付の制限を一時解除                   大谷 //
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
function chk_parts_sales_price_form(obj) {
    /* 部品番号の大文字変換 & 入力チェック */
    obj.parts.value = obj.parts.value.toUpperCase();
    if (!obj.parts.value.length) {
        alert('部品番号が入力されていません！');
        obj.parts.focus();
        obj.parts.select();
        return false;
    }
    if (obj.parts.value.length != 0) {
        if (obj.parts.value.length != 9) {
            alert("部品番号の桁数は９桁です。");
            obj.parts.focus();
            obj.parts.select();
            return false;
        }
    }
    
    /* 基準日の 入力チェック */
    if ( !isDigit(obj.regdate.value) ) {
        alert('基準日に数字以外の文字があります！');
        obj.regdate.focus();
        obj.regdate.select();
        return false;
    }  else if (obj.regdate.value < 20001001) {
        alert('基準日は20001001以上です！');
        obj.regdate.focus();
        obj.regdate.select();
        return false;
    } else if (obj.regdate.value > 20990331) {
        alert('基準日は20990331までです！');
        obj.regdate.focus();
        obj.regdate.select();
        return false;
    }
    
    /* 販売価格(仕切単価)レートの 入力チェック */
    if ( !isDigitDot(obj.sales_rate.value) ) {
        alert('販売価格(仕切単価)レートに数字以外の文字があります！');
        obj.sales_rate.focus();
        obj.sales_rate.select();
        return false;
    } else if (obj.sales_rate.value < 1.00) {
        alert('販売価格(仕切単価)レートは1.00以上です！');
        obj.sales_rate.focus();
        obj.sales_rate.select();
        return false;
    } else if (obj.sales_rate.value > 1.99) {
        alert('販売価格(仕切単価)レートは1.99までです！');
        obj.sales_rate.focus();
        obj.sales_rate.select();
        return false;
    }
    
    return true;
}
