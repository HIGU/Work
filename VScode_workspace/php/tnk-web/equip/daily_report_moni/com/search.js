// CONTEXT_PATH 設定確認用
if (CONTEXT_PATH == "") {
    alert("CONTEXT_PATH が設定されていません");
}
// NN 対応用
var NnRetValue = null;
var NnTarget   = null;
function NnReturn() {
    if (NnTarget == null || typeof(NnTarget) == "undefined") return;
    for (var i=0;i<NnTarget.length;i++) {
        var target = NnTarget[i];
        if (target != null && typeof(target) != "undefined") {
            NnTarget[i].value = NnRetValue[i];
        }
    }
}

// 材料マスタ検索ポップアップ
function SearchMaterials(target1,target2) {
    
    // 検索プログラム
    var url = new Array(CONTEXT_PATH + "search/Materials.php");
    
    if (navigator.userAgent.indexOf('MSIE') > -1) {
        // 検索ＰＯＰＵＰ起動
        var retValue = window.showModalDialog(CONTEXT_PATH + "/com/popup.php",url,"status:no; dialogWidth:640px; dialogHeight:480px; center:yes" );
        if(( retValue == null ) || ( typeof(retValue) == "undefined" )) return;
    
        // コード取得
        if (target1 != null && typeof(target1) != "undefined") {
            target1.value = retValue[0];
        }
        // 名称取得
        if (target2 != null && typeof(target2) != "undefined") {
            target2.value = retValue[1];
        }
    }
    else {
        var retValue = window.open(url,"popup","width=640,height=480,scrollbars=yes");
        NnTarget = new Array(target1,target2);
    }
}
// 部品（アイテム）検索ポップアップ
function SearchItem(target1,target2,target3) {

    // 検索プログラム
    var url = new Array(CONTEXT_PATH + "search/Item.php");
    
    if (navigator.userAgent.indexOf('MSIE') > -1) {
        // 検索ＰＯＰＵＰ起動
        var retValue = window.showModalDialog(CONTEXT_PATH + "/com/popup.php",url,"status:no; dialogWidth:640px; dialogHeight:480px; center:yes" );
        
        if(( retValue == null ) || ( typeof(retValue) == "undefined" )) return;
        
        // コード取得
        if (target1 != null && typeof(target1) != "undefined") {
            target1.value = retValue[0];
        }
        // 名称取得
        if (target2 != null && typeof(target2) != "undefined") {
            target2.value = retValue[1];
        }
        // 材質取得
        if (target3 != null && typeof(target3) != "undefined") {
            target3.value = retValue[2];
        }
    }
    else {
        var retValue = window.open(url,"popup","width=640,height=480" );
        NnTarget = new Array(target1,target2,target3);
    }
}
// 部品（関連づけ）検索ポップアップ
function SearchParts(target1,target2) {
    // 検索プログラム
    var url = new Array(CONTEXT_PATH + "search/Parts.php");
    
    if (navigator.userAgent.indexOf('MSIE') > -1) {
        // 検索ＰＯＰＵＰ起動
        var retValue = window.showModalDialog(CONTEXT_PATH + "/com/popup.php",url,"status:no; dialogWidth:640px; dialogHeight:480px; center:yes" );

        if(( retValue == null ) || ( typeof(retValue) == "undefined" )) return;
    
        // コード取得
        if (target1 != null && typeof(target1) != "undefined") {
            target1.value = retValue[0];
        }
        // 名称取得
        if (target2 != null && typeof(target2) != "undefined") {
            target2.value = retValue[1];
        }
    }
    else {
        var retValue = window.open(url,"popup","width=640,height=480" );
        NnTarget = new Array(target1,target2);
    }
}
