//////////////////////////////////////////////////////////////////////////////
// 協力工場別注残リストの照会 条件選択フォーム JavaScriptによる入力チェック //
// Copyright (C) 2015-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/04/26 Created  vendor_order_list_form.js                            //
// 2005/04/30 選択方式と直接入力方式を選べる機能追加 *ブランク処理がポイント//
// 2005/05/06 window top = offset値 60をマイナスWinXP対応 2005/05/06 ADD    //
// 2015/10/19 製品グループにT=ツールを追加                             大谷 //
//////////////////////////////////////////////////////////////////////////////

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.vendor_form.vendor.focus();
    document.vendor_form.vendor.select();
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
function chk_vendor_order_list_form(obj) {
    /* 製品グループの大文字変換 */
    obj.div.value = obj.div.value.toUpperCase();
    /* 発注区分の大文字変換 */
    obj.plan_cond.value = obj.plan_cond.value.toUpperCase();
    
    /* 発注先コード入力チェック */
//    if (!obj.vendor.value.length) {
//        vendor_copy();
//    }
    if (!obj.vendor.value.length) {
        alert('発注先コードが入力されていません！');
        obj.vendor.focus();
        obj.vendor.select();
        return false;
    }
    if (obj.vendor.value.length != 5) {
        alert("発注先コードの桁数は５桁です。");
        obj.vendor.focus();
        obj.vendor.select();
        return false;
    }
    if ( !isDigit(obj.vendor.value) ) {
        alert('発注先コードに数字以外の文字があります！');
        obj.vendor.focus();
        obj.vendor.select();
        return false;
    }
    
    /* 製品グループの入力チェック */
    switch (obj.div.value) {
    case 'C':
    case 'L':
    case 'T':
    case 'SC':
    case 'CS':
        break;
    case ' ' :
    case '' :
        /* 製品グループ2(選択フォーム)のブランク時の描画処理 */
        obj.div2.value = '';
        break;
    default:
        alert('製品グループはブランク=全て, C=カプラ, L=リニア, T=ツール, SC=C特注, CS=C標準 のどれかです！');
        obj.div.focus();
        obj.div.select();
        return false;
    }
    
    /* 発注計画区分の入力チェック */
    switch (obj.plan_cond.value) {
    case 'O':
    case 'R':
    case 'P':
        break;
    case ' ' :
    case '' :
        /* 発注計画区分2(選択フォーム)のブランク時の描画処理 */
        obj.plan_cond2.value = '';
        break;
    default:
        alert('発注計画区分はブランク=全て, O=注文書発行済, R=内示中, P=予定 のどれかです！');
        obj.plan_cond.focus();
        obj.plan_cond.select();      // <input type='text'>から<select>へ変更のためコメント
        return false;
    }
    return true;
}

/****** Window 処理 function *********/
function win_open(url, win_name) {
    if (win_name == 'undefind') return false;
    url += ('?vendor='      + document.vendor_form.vendor.value);
    url += ('&div='         + document.vendor_form.div.value);
    url += ('&plan_cond='   + document.vendor_form.plan_cond.value);
    var w = 830;    // (820=オリジナル)
    var h = 620;
    var left = ((screen.availWidth  - w) / 2);
    var top  = ((screen.availHeight - h - 60) / 2); // offset値 60をマイナスWinXP対応 2005/05/06 ADD
    window.open(url, win_name, 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=yes,top='+top+',left='+left);
}

/****** Window 処理2 function *********/
function win_open2(url, win_name) {
    if (win_name == 'undefind') return false;
    if (!chk_vendor_order_list_form(document.vendor_form)) return false;
    url += ('?vendor='      + document.vendor_form.vendor.value);
    url += ('&div='         + document.vendor_form.div.value);
    url += ('&plan_cond='   + document.vendor_form.plan_cond.value);
    var w = 830;    // (820=オリジナル)
    var h = 620;
    var left = ((screen.availWidth  - w) / 2);
    var top  = ((screen.availHeight - h - 60) / 2); // offset値 60をマイナスWinXP対応 2005/05/06 ADD
    window.open(url, win_name, 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=yes,top='+top+',left='+left);
}

/****** Window 処理2 function *********/
function csv_output2(url, win_name) {
    if (win_name == 'undefind') return false;
    if (!chk_vendor_order_list_form(document.vendor_form)) return false;
    url += ('?vendor='      + document.vendor_form.vendor.value);
    url += ('&div='         + document.vendor_form.div.value);
    url += ('&plan_cond='   + document.vendor_form.plan_cond.value);
    location.href = url;
    /*var w = 830;    // (820=オリジナル)
    var h = 620;
    var left = ((screen.availWidth  - w) / 2);
    var top  = ((screen.availHeight - h - 60) / 2); // offset値 60をマイナスWinXP対応 2005/05/06 ADD
    window.open(url, win_name, 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=yes,top='+top+',left='+left); */
}

/****** 発注先コードのコピー function *********/
function vendor_copy() {
    document.vendor_form.vendor.value = document.vendor_form.vendor2.value;
    return true;
}
function vendor_copy2() {
    document.vendor_form.vendor2.value = document.vendor_form.vendor.value;
    return true;
}

/****** 製品グループのコピー function *********/
function div_copy() {
    document.vendor_form.div.value = document.vendor_form.div2.value;
    return true;
}
/****** 製品グループの逆コピー function *********/
function div_copy2() {
    document.vendor_form.div.value = document.vendor_form.div.value.toUpperCase();
    document.vendor_form.div2.value = document.vendor_form.div.value;
    /* ブランク時の描画処理 */
    switch (document.vendor_form.div.value) {
    case ' ' :
    case '' :
        document.vendor_form.div2.value = '';
        break;
    }
    return true;
}

/****** 発注計画区分のコピー function *********/
function plan_cond_copy() {
    document.vendor_form.plan_cond.value = document.vendor_form.plan_cond2.value;
    return true;
}

/****** 発注計画区分の逆コピー function *********/
function plan_cond_copy2() {
    document.vendor_form.plan_cond.value = document.vendor_form.plan_cond.value.toUpperCase();
    document.vendor_form.plan_cond2.value = document.vendor_form.plan_cond.value;
    /* ブランク時の描画処理 */
    switch (document.vendor_form.plan_cond.value) {
    case ' ' :
    case '' :
        document.vendor_form.plan_cond2.value = '';
        break;
    }
    return true;
}

