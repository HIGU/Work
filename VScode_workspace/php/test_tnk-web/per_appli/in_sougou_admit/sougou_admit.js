////////////////////////////////////////////////////////////////////////////////
// 総合届（承認）                                                             //
//                                            MVC View 部 (JavaScriptクラス)  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit.js                                         //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

//  1000：ラジオボタン      承認
//  5000：ラジオボタン      否認
// 10000：テキストボックス  否認理由入力欄
// 15000：隠しパラメータ    総合届の判定用 承認 or 否認
// 20000：隠しパラメータ    日付
// 55000：文字色制御用      ※否認理由の文字色
// 70000：チェックボックス  否認メール
// 90000：隠しパラメータ    編集する総合届何番目か判別用

// ページ読み込み時に毎回呼び出す初期処理
function Init(editNo)
{
    if( isNaN(editNo) ) {
        editNo = 0;
        return;
    }
//    document.getElementsByName(5000+editNo)[0].focus(); // 修正後にフォーカスをセットするところ。
    var obj = document.getElementsByName(5000+editNo);
    if( obj[0] ) obj[0].focus(); // 修正後にフォーカスをセットするところ。
}

// 
function SetValue(obj)
{
    if( obj.checked ) {
        obj.value = 'on';
    } else {
        obj.value = '';
    }
}

function OutaiEnter(no)
{
    if( event.keyCode == 13 ) { // Enter
        event.keyCode = 9;      // Tab
        if( !document.getElementsByName('outai' + no)[0].value.match(/\S/g) ) {
            alert("応対者が入力されていません。");
            event.keyCode = 35; // End (他に影響なさそうなコードをセット)
        }
    }
}

function ReasonEnter(no)
{
    if( event.keyCode == 13 ) { // Enter
        event.keyCode = 35;     // End (他に影響なさそうなコードをセット)
        if( !document.getElementsByName(10000 + no)[0].value.match(/\S/g) ) {
            alert("否認理由が入力されていません。");
        }
    }
}

function onAdmit(cnt)
{
    for( var i=0; i<cnt; i++ ) {
        if( document.getElementsByName(90000 + i)[0].value != '') {
            return true;
        }
    }

    var admit_ok = 0, admit_ng = 0, ng_mail = true;
    for( var i=0; i<cnt; i++ ) {
        if( document.getElementById(1000+i).checked ) {
            admit_ok++;
        }
        if( document.getElementById(5000+i).checked ) {
            admit_ng++;
            if( !document.getElementById(10000+i).value.match(/\S/g) ) {
                alert("否認理由が入力されていない総合届があります。");
                return false;
            }
/**/
            if( ng_mail ) {
                if( document.getElementsByName(70000 + i + '_sinsei')[0].value == '' ) {
                    if( document.getElementsByName(70000 + i + '_kakari')[0].value == '' ) {
                        if( document.getElementsByName(70000 + i + '_katyo')[0].value == '' ) {
                            if( document.getElementsByName(70000 + i + '_butyo')[0].value == '' ) {
                                if( document.getElementsByName(70000 + i + '_soumu')[0].value == '' ) {
                                    if( document.getElementsByName(70000 + i + '_kanri')[0].value == '' ) {
                                        if( document.getElementsByName(70000 + i + '_kojyo')[0].value == '' ) {
                                            ng_mail = false;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
/**/
        }
    }

    if( admit_ok == 0 && admit_ng == 0 ) {
//        alert("承認 又は、否認 を選択後押して下さい。");
        alert("承認 又は、否認 の選択や受電者登録を確認して下さい。");
        return false;
    }
/**/
    if(  admit_ng > 0 && ! ng_mail ) {
        var admit = confirm("否認メール送信先の選択されていない総合届がありますがよろしいですか？\n\n※メール送信しない場合は、必ず否認することを申請者本人へ伝えて下さい。");
        if( !admit ) return false;
    }
/**/
    var admit = confirm("選択した総合届(承認 " + admit_ok + " 件 / 否認 " + admit_ng + " 件)を確定してもよろしいですか？");

    if( !admit ) return false;

    var hiduke = new Date(); 
    var year = hiduke.getFullYear();
    var month = hiduke.getMonth()+1;
    var day = hiduke.getDate();

    var hour = hiduke.getHours();
    var minute = hiduke.getMinutes();
    var second = hiduke.getSeconds();

    for( var i=0; i<cnt; i++ ) {
        if( document.getElementsByName(i)[0].checked ) {
            document.getElementsByName(15000+i)[0].value = '承認';
            document.getElementsByName(20000+i)[0].value = year + '-' + month + '-' + day + ' ' + hour + ':' + minute;
        } else if( document.getElementsByName(i)[1].checked ) {
            document.getElementsByName(15000+i)[0].value = '否認';
            document.getElementsByName(20000+i)[0].value = year + '-' + month + '-' + day + ' ' + hour + ':' + minute;
        }
    }

    return admit;
}

// 承認のチェックを一括で制御
function BulkSelection(obj, cnt)
{
    var flag = true;
    if( obj.value == "承認一括選択" ) {
        obj.value = "承認一括解除";
    } else {
        flag = false;
        obj.value = "承認一括選択";
    }
    for( var i=0; i<cnt; i++ ) {
        if( document.getElementById(1000 + i).disabled ) {
            if( flag ) {
                document.getElementById(5000 + i).checked = false;
            }
        } else {
            document.getElementById(1000 + i).checked = flag;
            if( flag ) {
                document.getElementById(1000 + i).value = "";
                document.getElementById(5000 + i).value = "否認";
            } else {
                document.getElementById(1000 + i).value = "承認";
            }
        }
        DenyReason(i);
    }
}

// 否認を選択時、理由の部分を入力できるよう制御
function DenyReason(no)
{
    if( document.getElementById(5000 + no).checked ) {
        document.getElementById(10000 + no).disabled = false;
        setDisableStyle(55000+no, false);
    } else {
        document.getElementById(10000 + no).disabled = true;
        document.getElementById(10000 + no).value = "";
        setDisableStyle(55000+no, true);
    }
}

// 承認ラジオボタンのチェックを外すため
function AdmitSelect(obj, no)
{
    if( obj.value ) {
        obj.value = "";
        obj.checked = true;
        document.getElementById(5000 + no).value = "否認";
    } else {
        obj.value = "承認";
        obj.checked = false;
    }
    DenyReason(no);
}

// 否認ラジオボタンのチェックを外すため
function DenySelect(obj, no)
{
    if( obj.value ) {
        obj.value = "";
        obj.checked = true;
        document.getElementById(1000 + no).value = "承認";
    } else {
        obj.value = "否認";
        obj.checked = false;
    }
    DenyReason(no);
}

// 修正の確認
function EditRun(no)
{
    document.getElementsByName('edit_no')[0].value = no;

    if( ! confirm("内容の修正を行いますか？") ) return false;

    document.getElementsByName('EditFlag')[0].value = 'on';
    document.getElementsByName(90000 + no)[0].value = 'Edit';

    return true;
}

// 指定IDの文字色を グレー or 黒 に設定
function setDisableStyle(id, flag)
{
    obj = document.getElementById(id);
    if( flag ) {
        obj.style.color = 'DarkGray';   //文字色をグレーにする
    } else {
        obj.style.color = 'black';  //文字色を黒にする
    }
}

// 否認メール送信先をセット
function setNgMail(no, idx)
{
    var flag = '';

    if( document.getElementsByName(70000 + no)[idx].checked ) {
        flag = true;
    }

    switch (idx) {
        case 0:
            document.getElementsByName(70000 + no + '_sinsei')[0].value = flag;
            break;
        case 1:
            document.getElementsByName(70000 + no + '_kakari')[0].value = flag;
            break;
        case 2:
            document.getElementsByName(70000 + no + '_katyo')[0].value = flag;
            break;
        case 3:
            document.getElementsByName(70000 + no + '_butyo')[0].value = flag;
            break;
        case 4:
            document.getElementsByName(70000 + no + '_soumu')[0].value = flag;
            break;
        case 5:
            document.getElementsByName(70000 + no + '_kanri')[0].value = flag;
            break;
        case 6:
            document.getElementsByName(70000 + no + '_kojyo')[0].value = flag;
            break;
    }
}

// 応対日時コピー
function JyuDateCopy(no)
{
    document.getElementsByName("jyu_date" + no)[0].value = document.getElementsByName("ddlist_ye" + no)[0].value + '-' + document.getElementsByName("ddlist_mo" + no)[0].value + '-' + document.getElementsByName("ddlist_da" + no)[0].value + ' ' + document.getElementsByName("ddlist_ho" + no)[0].value + ':' + document.getElementsByName("ddlist_mi" + no)[0].value;

    if( !isDate(document.getElementsByName("ddlist_ye" + no)[0].value + document.getElementsByName("ddlist_mo" + no)[0].value + document.getElementsByName("ddlist_da" + no)[0].value) ) {
        var dt = new Date(document.getElementsByName("ddlist_ye" + no)[0].value, document.getElementsByName("ddlist_mo" + no)[0].value, 0);
        document.getElementsByName("ddlist_da" + no)[0].value = dt.getDate();
        document.getElementsByName("jyu_date" + no)[0].value = document.getElementsByName("ddlist_ye" + no)[0].value + '-' + document.getElementsByName("ddlist_mo" + no)[0].value + '-' + document.getElementsByName("ddlist_da" + no)[0].value + ' ' + document.getElementsByName("ddlist_ho" + no)[0].value + ':' + document.getElementsByName("ddlist_mi" + no)[0].value;
    }
}

function isDate (str) {
  var arr = (str.substr(0, 4) + '/' + str.substr(4, 2) + '/' + str.substr(6, 2)).split('/');

  if (arr.length !== 3) return false;
  var date = new Date(arr[0], arr[1] - 1, arr[2]);
  if (arr[0] !== String(date.getFullYear()) || arr[1] !== ('0' + (date.getMonth() + 1)).slice(-2) || arr[2] !== ('0' + date.getDate()).slice(-2)) {
    return false;
  } else {
    return true;
  }
}

// 受電者欄の[登録]ボタンクリック時
function ReceivedPhoneRegister(no)
{
    if( !document.getElementsByName('outai' + no)[0].value.match(/\S/g) ) {
        alert("応対者が入力されていません。");
        document.getElementsByName('outai' + no)[0].focus();
        return false;
    }

    JyuDateCopy(no);

    document.getElementsByName('jyu_register' + no)[0].value = "ok";

//alert("*** 受電者登録作成中です。*** ReceivedPhoneRegister(" + no + ":" + document.getElementsByName('jyu_register' + no)[0].value + ")");
    document.getElementsByName('edit_no')[0].value = no;

    return true;
}

function AgentCheck(obj)
{
    if( obj.checked ) {
        var list = document.getElementById('ddlist');
        obj.value = list.options[list.selectedIndex].value;
        document.getElementById('agent_select').disabled = false;
        setDisableStyle('agent_select', false);
    } else {
        obj.value = '';
        document.getElementById('agent_select').disabled = true;
        setDisableStyle('agent_select', true);
    }
}

// タイトルに、（事後報告）追加
function Zigo(no)
{
    document.getElementById('id_title' + no).value += '（事後報告）';
}

// タイトルに、受電者未登録 追加
function ZigoOutai(no)
{
    document.getElementById('id_title' + no).value += '（事後報告）受電者未登録';
}

// 応対者入力のコメント表示位置調整しようとしたがうまくいかない。
function GetOutaiTop(no)
{
    var top = document.getElementsByName('outai' + no)[0].style.top;
//    alert("top : " + top);
    return top;
}

function GetOutaiLeft(no)
{
    var left = document.getElementsByName('outai' + no)[0].style.left;
//    alert("left : " + left);
    return left;
}

function SetOutaiTop(no)
{
    document.getElementById('Coment' + no).style.top = 630;
}
function SetOutaiLeft(no)
{
    document.getElementById('Coment' + no).style.left = 680;
}

// 送信者、相手のUIDをセット
function SetSendInfo(no)
{
    document.getElementById('id_send_uid').value = document.getElementById('id_w_uid'+no).value;
    
    document.getElementsByName("form_send")[0].submit();
}
