//////////////////////////////////////////////////////////////////////////////
// ＴＮＫ社員メニュー用 入力フォーム等のチェック用 JavaScript               //
// 2001/07/07 Copyright(C) 2001-2004 K.Kobayashi tnksys@nitto-kohki.co.jp   //
// 変更経歴                                                                 //
// 2003/12/15 confirm.js から分離して login.js を新規に作成                 //
// 2004/01/28 [社員No]を６桁未満なら自動０詰するように変更。                //
//////////////////////////////////////////////////////////////////////////////
/* 入力文字が数字かどうかチェック */
/* false=数字でない  ture=数字である */
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
/* 入力情報のチェック & 補正 */
function inpConf(obj) {
    if (!obj.userid.value.length) {
        alert("[社員No]の入力欄が空白です。ログインできません。");
        obj.userid.focus();
        return false;
    }
    if (obj.userid.value.length != 6) {
        switch (obj.userid.value.length) {
        case 1:
            obj.userid.value = ('00000' + obj.userid.value);
            break;
        case 2:
            obj.userid.value = ('0000' + obj.userid.value);
            break;
        case 3:
            obj.userid.value = ('000' + obj.userid.value);
            break;
        case 4:
            obj.userid.value = ('00' + obj.userid.value);
            break;
        case 5:
            obj.userid.value = ('0' + obj.userid.value);
            break;
        }
        // alert("[社員No]の桁数は６桁です。");
        // obj.userid.focus();
        // return false;
    }
    if (!obj.passwd.value.length) {
        alert("[パスワード]の入力欄が空白です。ログインできません。");
        obj.passwd.focus();
        return false;
    }
    return true;
}
