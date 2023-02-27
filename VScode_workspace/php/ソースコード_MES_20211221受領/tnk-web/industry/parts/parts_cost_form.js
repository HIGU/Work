//////////////////////////////////////////////////////////////////////////////
// 単価経歴の照会 条件選択フォーム JavaScriptによる入力チェック             //
// Copyright(C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2004/05/17 新規作成 parts_cost_form.js                                   //
// 2005/01/11 ディレクトリを industry/ → industry/parts/ へ変更            //
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
function chk_parts_cost_form(obj) {
    /* 部品番号の入力チェック & 大文字変換 */
    if (!obj.parts_no.value.length) {
        alert('部品番号が入力されていません！');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    if (obj.parts_no.value.length != 0) {
        if (obj.parts_no.value.length != 9) {
            alert("部品番号の桁数は９桁です。");
            obj.parts_no.focus();
            obj.parts_no.select();
            return false;
        }
    }
    
    /* １頁の表示行数 入力チェック */
    if ( !isDigit(obj.cost_page.value) ) {
        alert('ページ数に数字以外の文字があります！');
        obj.cost_page.focus();
        obj.cost_page.select();
        return false;
    } else if (obj.cost_page.value < 1) {
        alert('ページ数は１以上です！');
        obj.cost_page.focus();
        obj.cost_page.select();
        return false;
    } else if (obj.cost_page.value > 999) {
        alert('ページ数は９９９までです！');
        obj.cost_page.focus();
        obj.cost_page.select();
        return false;
    }
    return true;
}
