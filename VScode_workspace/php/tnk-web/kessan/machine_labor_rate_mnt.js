//////////////////////////////////////////////////////////////////////////
// 機械賃率計算表 手作業(刻印)・機械運転時間を入力し賃率を自動算出      //
// Java Script                                                          //
// 2002/09/23 Copyright(C) 2002 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// 変更経歴                                                             //
// 2002/09/23 新規作成                                                  //
//////////////////////////////////////////////////////////////////////////
/* 入力文字が数字かどうかチェック */
function isDigit(str){
    var len=str.length;
    var c;
    for(i=0;i<len;i++){
        c=str.charAt(i);
        if("0">c||c>"9")
            return true;
        }
    return false;
}
function ym_chk(obj){
    if(!obj.rate_ym.value.length){
        alert("[対象年月]の入力欄が空白です。");
        obj.rate_ym.focus();
        obj.rate_ym.select();
        return false;
    }
    if(isDigit(obj.rate_ym.value)){
        alert("数値以外の文字は入力出来ません｡");
        obj.rate_ym.focus();
        obj.rate_ym.select();
        return false;
    }
    return true;
}
function kessan_chk(obj){
    if(!obj.str_ym.value.length){
        alert("[対象年月]の入力欄が空白です。");
        obj.str_ym.focus();
        obj.str_ym.select();
        return false;
    }
    if(isDigit(obj.str_ym.value)){
        alert("数値以外の文字は入力出来ません｡");
        obj.str_ym.focus();
        obj.str_ym.select();
        return false;
    }
    if(!obj.end_ym.value.length){
        alert("[対象年月]の入力欄が空白です。");
        obj.end_ym.focus();
        obj.end_ym.select();
        return false;
    }
    if(isDigit(obj.end_ym.value)){
        alert("数値以外の文字は入力出来ません｡");
        obj.end_ym.focus();
        obj.end_ym.select();
        return false;
    }
    if(obj.span[0].checked == false){
        if(obj.span[1].checked == false)
            if(obj.span[2].checked == false){
                alert("[中間・期末・ランダム]を選んで下さい｡");
                return false;
            }
    }
    return true;
}
