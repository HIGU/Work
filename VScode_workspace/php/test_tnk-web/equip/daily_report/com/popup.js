// CONTEXT_PATH 設定確認用
if (CONTEXT_PATH == "") {
    alert("CONTEXT_PATH が設定されていません");
}
//汎用ポップアップ表示
// url      ... URL
// arg      ... 呼び出し検索プログラムに HttpServletRequest を通じて渡したいフィールド（配列）
function ShowDialog(url,arg,width,height) {
    var args;
    if((arg != null) && (typeof(arg) != "undefined")) {
        var prmLen = 0;
        for (i=0; i<arg.length; i++) {
            if( typeof(arg[i].length) == "undefined" ) {
                prmLen++;
            } else {
                prmLen+=arg[i].length;
            }
        }
        args = new Array(prmLen+1);
        args[0] = url;
        var k = 0;
        for(i=0; i<arg.length; i++) {
            if( typeof(arg[i].length) == "undefined" ) {
                args[k+1] = arg[i];
                k++;
            } else {
                for (j=0 ; j<arg[i].length ; j++) {
                    args[k+1] = arg[i][j];
                    k++;
                }
            }

        }
    } else {
        args = new Array(1);
        args[0] = url;
    }
    return window.showModalDialog(CONTEXT_PATH + "/com/popup.php",args,"status:no; dialogWidth:" + width + "px; dialogHeight:" + height + "px; center:yes" );
}
//ポップアップ検索実行
// pgm      ... 検索プログラム名 ( URL )
// target   ... 検索結果の反映対象 Field
// btn      ... 検索終了後に click() イベントを発生させたいボタン（省略可）
// arg      ... 呼び出し検索プログラムに HttpServletRequest を通じて渡したいフィールド（配列）
function SearchClick(pgm,target,arg) {
    var width;
    var height;

    width   = "600";
    height  = "400";

    var retValue = ShowDialog(getUrl(pgm),arg,width,height);
    if(( retValue != null ) && ( typeof(retValue) != "undefined" )) {
        retValue.replace(/ /g,"").match(/(.+)：(.+)/);
        target.value = RegExp.$1;
        if (( btn != null ) && ( typeof(btn) != "undefined" )) {
            btn.click();
        }
    }
}
// カレンダーＰＯＰアップ
// pgm      ... 検索プログラム名 ( URL )
// year     ... 検索結果の反映対象 Field
// month    ... 検索結果の反映対象 Field
// day      ... 検索結果の反映対象 Field
// btn      ... 検索終了後に click() イベントを発生させたいボタン（省略可）
// arg      ... 呼び出し検索プログラムに HttpServletRequest を通じて渡したいフィールド（配列）
function CalendarPopup(pgm,year,month,day) {
    var width;
    var height;
    
    width   = "600";
    height  = "400";
    
    var QueryString = "";
    if (year.value != "" && month.value != "") {
        var QueryString = "?CalYear=" + year.value + "&CalMonth=" + month.value;
    }
    
    var retValue = ShowDialog(getUrl(pgm + QueryString),null,width,height);
    
    if(( retValue != null ) && ( typeof(retValue) != "undefined" )) {
        
        year.value  = retValue.substring(0,4);
        month.value = retValue.substring(4,6);
        day.value   = retValue.substring(6,8);
        
    }
}

//現在の環境に合わせたURLを生成
function getUrl(target) {
    var host = window.location.host;
    var path = window.location.pathname;

    //パス名から最初の階層部分を抽出
    path.match(/\/(.+)\//);

    return "http://" + host + "/" + RegExp.$1 + "/" + target;
}

