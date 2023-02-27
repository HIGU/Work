//////////////////////////////////////////////////////////////////////////////
// 開発メニュー(プログラム開発依頼書)用入力フォーム等のチェック用 JavaScript//
// 2002/02/12 Copyright(C) 2002-2004 Kobayashi tnksys@nitto-kohki.co.jp     //
// 変更経歴                                                                 //
// 2003/12/15 新規作成 dev_req.js                                           //
// 2004/01/28 [社員No]を６桁未満なら自動０詰するように変更。                //
// 2004/02/23 確認用に社員番号のみ入力した時でも即社員名が出るように変更    //
//////////////////////////////////////////////////////////////////////////////
/* 入力文字が数字かどうかチェック */
function isDigit(str){
    var len=str.length;
    var c;
    for(i=0;i<len;i++){
        c=str.charAt(i);
        if("0">c||c>"9")
            return false;
        }
    return true;
}

/* 開発依頼書の検索条件入力内容をチェック */
function chk_dev_req_input(obj) {
    if(obj.dev_req_client.value.length) {
        if(obj.dev_req_client.value.length != 6) {
            switch (obj.dev_req_client.value.length) {
            case 1:
                obj.dev_req_client.value = ('00000' + obj.dev_req_client.value);
                break;
            case 2:
                obj.dev_req_client.value = ('0000' + obj.dev_req_client.value);
                break;
            case 3:
                obj.dev_req_client.value = ('000' + obj.dev_req_client.value);
                break;
            case 4:
                obj.dev_req_client.value = ('00' + obj.dev_req_client.value);
                break;
            case 5:
                obj.dev_req_client.value = ('0' + obj.dev_req_client.value);
                break;
            }
            // alert("依頼者の社員№の桁数は６桁です。");
            // obj.dev_req_client.focus();
            // obj.dev_req_client.select();
            // return false;
        }
    }
    if(obj.dev_req_sdate.value.length){
        if(obj.dev_req_sdate.value.length!=8){
            alert("開始日付の桁数は８桁です。");
            obj.dev_req_sdate.focus();
            obj.dev_req_sdate.select();
            return false;
        }
        if(!isDigit(obj.dev_req_sdate.value)){
            alert("開始日付に数字以外のデータがあります。");
            obj.dev_req_sdate.focus();
            obj.dev_req_sdate.select();
            return false;
        }
/*      if(!obj.dev_req_edate.value.length){
            alert("開始日付を入力した時は終了日付も入力して下さい。");
            obj.dev_req_edate.focus();
            obj.dev_req_edate.select();
            return false;
        }
*/  }
    if(obj.dev_req_edate.value.length){
        if(obj.dev_req_edate.value.length!=8){
            alert("終了日付の桁数は８桁です。");
            obj.dev_req_edate.focus();
            obj.dev_req_edate.select();
            return false;
        }
        if(!isDigit(obj.dev_req_edate.value)){
            alert("終了日付に数字以外のデータがあります。");
            obj.dev_req_edate.focus();
            obj.dev_req_edate.select();
            return false;
        }
/*      if(!obj.dev_req_sdate.value.length){
            alert("終了日付を入力した時は開始日付も入力して下さい。");
            obj.dev_req_sdate.focus();
            obj.dev_req_sdate.select();
            return false;
        }
*/  }
    if(obj.dev_req_No.value.length){
        if(!isDigit(obj.dev_req_No.value)){
            alert("依頼№に数字以外のデータがあります。");
            obj.dev_req_No.focus();
            obj.dev_req_No.select();
            return false;
        }
    }
    return true;
}

/* 開発依頼書 作成 送信 の入力内容チェック */
function chk_dev_req_submit(obj){
    if(!obj.iraisya.value.length){
        alert("依頼者の社員№が未入力です。");
        obj.iraisya.focus();
        obj.iraisya.select();
        return false;
    }
    if(obj.iraisya.value.length) {
        if(obj.iraisya.value.length != 6){
            switch (obj.iraisya.value.length) {
            case 1:
                obj.iraisya.value = ('00000' + obj.iraisya.value);
                break;
            case 2:
                obj.iraisya.value = ('0000' + obj.iraisya.value);
                break;
            case 3:
                obj.iraisya.value = ('000' + obj.iraisya.value);
                break;
            case 4:
                obj.iraisya.value = ('00' + obj.iraisya.value);
                break;
            case 5:
                obj.iraisya.value = ('0' + obj.iraisya.value);
                break;
            }
            // alert("依頼者の社員№の桁数は６桁です。");
            // obj.iraisya.focus();
            // obj.iraisya.select();
            // return false;
        }
    }
    /*  サーバー再度でチェックするように変更 社員名の確認を即出来る様にするため
    if(!obj.mokuteki.value.length){
        alert("目的又はタイトルが未入力です。");
        obj.mokuteki.focus();
        obj.mokuteki.select();
        return false;
    }
    if(!obj.naiyou.value.length){
        alert("依頼内容がが未入力です。");
        obj.naiyou.focus();
        obj.naiyou.select();
        return false;
    }
    */
    if(obj.yosoukouka.value.length > 0) {
        if(!isDigit(obj.yosoukouka.value)) {
            alert("予想効果工数(分／年)に数字以外のデータがあります。");
            obj.yosoukouka.focus();
            obj.yosoukouka.select();
            return false;
        }
    }
}

// 開発依頼書のメンテナンスAdministrator権限での操作
function chk_dev_req_edit(obj){
    if(!obj.yuusendo.value.length){
        alert("優先度が未入力です。");
        obj.yuusendo.focus();
        obj.yuusendo.select();
        return false;
    }
    if(!obj.sagyouku.value.length){
        alert("作業区が未入力です。");
        obj.sagyouku.focus();
        obj.sagyouku.select();
        return false;
    }
    if(!obj.sintyoku.value.length){
        alert("進捗状況が未入力です。");
        obj.sintyoku.focus();
        obj.sintyoku.select();
        return false;
    }
/*  if(!obj.kousuu.value.length){
        alert("開発工数が未入力です。");
        obj.kousuu.focus();
        obj.kousuu.select();
        return false;
    }
    if(!obj.kanryou.value.length){
        alert("完了日が未入力です。");
        obj.kanryou.focus();
        obj.kanryou.select();
        return false;
    }
*/
}
