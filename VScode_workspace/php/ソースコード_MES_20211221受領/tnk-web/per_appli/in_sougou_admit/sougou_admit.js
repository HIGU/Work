////////////////////////////////////////////////////////////////////////////////
// ����ϡʾ�ǧ��                                                             //
//                                            MVC View �� (JavaScript���饹)  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit.js                                         //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

//  1000���饸���ܥ���      ��ǧ
//  5000���饸���ܥ���      ��ǧ
// 10000���ƥ����ȥܥå���  ��ǧ��ͳ������
// 15000�������ѥ�᡼��    ����Ϥ�Ƚ���� ��ǧ or ��ǧ
// 20000�������ѥ�᡼��    ����
// 55000��ʸ����������      ����ǧ��ͳ��ʸ����
// 70000�������å��ܥå���  ��ǧ�᡼��
// 90000�������ѥ�᡼��    �Խ���������ϲ����ܤ�Ƚ����

// �ڡ����ɤ߹��߻������ƤӽФ��������
function Init(editNo)
{
    if( isNaN(editNo) ) {
        editNo = 0;
        return;
    }
//    document.getElementsByName(5000+editNo)[0].focus(); // ������˥ե��������򥻥åȤ���Ȥ���
    var obj = document.getElementsByName(5000+editNo);
    if( obj[0] ) obj[0].focus(); // ������˥ե��������򥻥åȤ���Ȥ���
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
            alert("���мԤ����Ϥ���Ƥ��ޤ���");
            event.keyCode = 35; // End (¾�˱ƶ��ʤ������ʥ����ɤ򥻥å�)
        }
    }
}

function ReasonEnter(no)
{
    if( event.keyCode == 13 ) { // Enter
        event.keyCode = 35;     // End (¾�˱ƶ��ʤ������ʥ����ɤ򥻥å�)
        if( !document.getElementsByName(10000 + no)[0].value.match(/\S/g) ) {
            alert("��ǧ��ͳ�����Ϥ���Ƥ��ޤ���");
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
                alert("��ǧ��ͳ�����Ϥ���Ƥ��ʤ�����Ϥ�����ޤ���");
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
//        alert("��ǧ ���ϡ���ǧ ������岡���Ʋ�������");
        alert("��ǧ ���ϡ���ǧ ���������ż���Ͽ���ǧ���Ʋ�������");
        return false;
    }
/**/
    if(  admit_ng > 0 && ! ng_mail ) {
        var admit = confirm("��ǧ�᡼������������򤵤�Ƥ��ʤ�����Ϥ�����ޤ���������Ǥ�����\n\n���᡼���������ʤ����ϡ�ɬ����ǧ���뤳�Ȥ������ܿͤ������Ʋ�������");
        if( !admit ) return false;
    }
/**/
    var admit = confirm("���򤷤������(��ǧ " + admit_ok + " �� / ��ǧ " + admit_ng + " ��)����ꤷ�Ƥ������Ǥ�����");

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
            document.getElementsByName(15000+i)[0].value = '��ǧ';
            document.getElementsByName(20000+i)[0].value = year + '-' + month + '-' + day + ' ' + hour + ':' + minute;
        } else if( document.getElementsByName(i)[1].checked ) {
            document.getElementsByName(15000+i)[0].value = '��ǧ';
            document.getElementsByName(20000+i)[0].value = year + '-' + month + '-' + day + ' ' + hour + ':' + minute;
        }
    }

    return admit;
}

// ��ǧ�Υ����å����������
function BulkSelection(obj, cnt)
{
    var flag = true;
    if( obj.value == "��ǧ�������" ) {
        obj.value = "��ǧ�����";
    } else {
        flag = false;
        obj.value = "��ǧ�������";
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
                document.getElementById(5000 + i).value = "��ǧ";
            } else {
                document.getElementById(1000 + i).value = "��ǧ";
            }
        }
        DenyReason(i);
    }
}

// ��ǧ�����������ͳ����ʬ�����ϤǤ���褦����
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

// ��ǧ�饸���ܥ���Υ����å��򳰤�����
function AdmitSelect(obj, no)
{
    if( obj.value ) {
        obj.value = "";
        obj.checked = true;
        document.getElementById(5000 + no).value = "��ǧ";
    } else {
        obj.value = "��ǧ";
        obj.checked = false;
    }
    DenyReason(no);
}

// ��ǧ�饸���ܥ���Υ����å��򳰤�����
function DenySelect(obj, no)
{
    if( obj.value ) {
        obj.value = "";
        obj.checked = true;
        document.getElementById(1000 + no).value = "��ǧ";
    } else {
        obj.value = "��ǧ";
        obj.checked = false;
    }
    DenyReason(no);
}

// �����γ�ǧ
function EditRun(no)
{
    document.getElementsByName('edit_no')[0].value = no;

    if( ! confirm("���Ƥν�����Ԥ��ޤ�����") ) return false;

    document.getElementsByName('EditFlag')[0].value = 'on';
    document.getElementsByName(90000 + no)[0].value = 'Edit';

    return true;
}

// ����ID��ʸ������ ���졼 or �� ������
function setDisableStyle(id, flag)
{
    obj = document.getElementById(id);
    if( flag ) {
        obj.style.color = 'DarkGray';   //ʸ�����򥰥졼�ˤ���
    } else {
        obj.style.color = 'black';  //ʸ��������ˤ���
    }
}

// ��ǧ�᡼��������򥻥å�
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

// �����������ԡ�
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

// ���ż����[��Ͽ]�ܥ��󥯥�å���
function ReceivedPhoneRegister(no)
{
    if( !document.getElementsByName('outai' + no)[0].value.match(/\S/g) ) {
        alert("���мԤ����Ϥ���Ƥ��ޤ���");
        document.getElementsByName('outai' + no)[0].focus();
        return false;
    }

    JyuDateCopy(no);

    document.getElementsByName('jyu_register' + no)[0].value = "ok";

//alert("*** ���ż���Ͽ������Ǥ���*** ReceivedPhoneRegister(" + no + ":" + document.getElementsByName('jyu_register' + no)[0].value + ")");
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

// �����ȥ�ˡ��ʻ��������ɲ�
function Zigo(no)
{
    document.getElementById('id_title' + no).value += '�ʻ�������';
}

// �����ȥ�ˡ����ż�̤��Ͽ �ɲ�
function ZigoOutai(no)
{
    document.getElementById('id_title' + no).value += '�ʻ������˼��ż�̤��Ͽ';
}

// ���м����ϤΥ�����ɽ������Ĵ�����褦�Ȥ��������ޤ������ʤ���
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

// �����ԡ�����UID�򥻥å�
function SetSendInfo(no)
{
    document.getElementById('id_send_uid').value = document.getElementById('id_w_uid'+no).value;
    
    document.getElementsByName("form_send")[0].submit();
}
