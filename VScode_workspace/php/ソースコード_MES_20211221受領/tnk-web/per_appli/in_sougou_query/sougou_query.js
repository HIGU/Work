////////////////////////////////////////////////////////////////////////////////
// ����ϡʾȲ��                                                             //
//                                            MVC View �� (JavaScript���饹)  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_query.js                                         //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

// �Ұ��ֹ����ϥ����å�
function InputCheck(obj){
    var str1=obj.value;

    if(isDigit(str1)) {
//        alert("����");
        return str1;
    }else{
//        alert("ʸ��"+str);
        return getDigit(str1);
    }
}

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

function getDigit(str) {
    var len = str.length;
    var c, str1="";
    for (i=0; i<len; i++){
        c = str.charAt(i);
        if(("0" > c) || ("9" < c)) {
            continue;
        }
        str1 += c;
    }
    return str1;
}

// ���������å����ν���
function OneDay(obj)
{
    document.getElementById(obj.id + '-2').disabled = obj.checked;

    if( obj.checked ) {
        document.getElementById(obj.id + '-2').value = '';
        setDisableStyle(obj.id + '-0', true);
    } else {
        document.getElementById(obj.id + '-2').value = document.getElementById(obj.id + '-1').value;
        setDisableStyle(obj.id + '-0', false);
    }
}

// ���ν���
function Init()
{
    document.getElementsByName("si_s_date")[0].focus();

    document.getElementsByName("c0")[0].checked = true;
    setDisableStyle('001-0', true);
    document.getElementsByName("c1")[0].checked = true;
    setDisableStyle('101-0', true);

    document.getElementsByName("si_e_date")[0].disabled = true;
    document.getElementsByName("end_date")[0].disabled = true;

    if( document.getElementsByName("r4")[0] )
        document.getElementsByName("r4")[0].checked = true;

    if( document.getElementsByName("r5")[0] )
        document.getElementsByName("r5")[0].checked = true;

    document.getElementsByName("r6")[0].checked = true;
    document.getElementsByName("r7")[0].checked = true;
    document.getElementsByName("r8")[0].checked = true;
    document.getElementsByName("r9")[0].checked = true;
}

function huzaisya(ischecked)
{
    setNameDisable("ddlist", ischecked);
    setNameDisable("r6", ischecked);
    setNameDisable("r7", ischecked);
    setNameDisable("r8", ischecked);
    setNameDisable("r9", ischecked);

    setDisableStyle("6000", ischecked);
    setDisableStyle("7000", ischecked);
    setDisableStyle("8000", ischecked);
    setDisableStyle("9000", ischecked);
}

function setDisableStyle(id, flag)
{
    obj = document.getElementById(id);
    if( flag ) {
        obj.style.color = 'DarkGray';   //ʸ�����򥰥졼�ˤ���
    } else {
        obj.style.color = 'black';  //ʸ��������ˤ���
    }
}

function setNameDisable(name, flag)
{
    var obj = document.getElementsByName(name);
    for( var i=0; i<obj.length; i++) {
        obj[i].disabled = flag;
    }
}

// ��ɽ�����ν���
function Rep()
{
    document.getElementsByName("si_e_date")[0].disabled = document.getElementsByName("c0")[0].checked;
    setDisableStyle('001-0', document.getElementsByName("c0")[0].checked);

    document.getElementsByName("end_date")[0].disabled = document.getElementsByName("c1")[0].checked;
    setDisableStyle('101-0', document.getElementsByName("c1")[0].checked);

    if( document.getElementsByName("c2")[0] ) {
        huzaisya(document.getElementsByName("c2")[0].checked);
    }
}

// ���Ϥ��줿���դ�����å�
function checkDate(obj)
{
        if( obj.value == '' ) return;
        var str = obj.value;
        if( str.length != 8 ) {
            alert('�����­��ޤ���');
            obj.focus();
            obj.select();
            return;
        }

        var yyymmdd = obj.value;
        if(yyymmdd.substr(6, 2) < 1) yyymmdd = yyymmdd.substr(0, 6) + '01';
        ///// �ǽ���������å����ƥ��åȤ���
        if (!isDate(yyymmdd)) {
            var dt = new Date(yyymmdd.substr(0, 4),  yyymmdd.substr(4, 2), 0);
            yyymmdd = ( yyymmdd.substr(0, 6) + dt.getDate() );
            if (!isDate(yyymmdd)) {
                alert('���դλ��꤬�����Ǥ���');
                obj.select();
                obj.focus();
                return;
            }

        }
        obj.value = yyymmdd;
}

// ¸�ߤ������դ������å�
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

// ����������λ���δط���������å�
function CheckDateRange(sdate, edate)
{
    var sd_val = document.getElementsByName(sdate)[0].value;
    var ed_val = document.getElementsByName(edate)[0].value;
    if( sd_val.length == 0 && sd_val.length == 0 ) return true;

    if( sd_val.length == 0 ) {
        document.getElementsByName(sdate)[0].focus();
        document.getElementsByName(sdate)[0].select();
        alert("����ǯ���������Ϥ���Ƥ��ޤ���\n\nǯ���������Ϥ��Ƥ���¹Ԥ��Ʋ�������");
        return false;
    }
    var sd = new Date(sd_val.substr(0, 4), sd_val.substr(4, 2)-1, sd_val.substr(6, 2));
    if( ed_val.length == 0 ) {
        alert("��λǯ���������Ϥ���Ƥ��ޤ���\n\nǯ���������Ϥ��롣\n\n���ϡ������˥����å���������ټ¹Ԥ��Ʋ�������");
        return false;
    }
    var ed = new Date(ed_val.substr(0, 4), ed_val.substr(4, 2)-1, ed_val.substr(6, 2));
    if( sd > ed ) {
        document.getElementsByName(edate)[0].focus();
        document.getElementsByName(edate)[0].select();
        alert(sd.toLocaleDateString() + '��' + ed.toLocaleDateString() + "\n\n���ꤵ�줿ǯ�����˸�꤬����ޤ���\n\n���Τ���Τ������١��¹Ԥ��Ʋ�������");
        return false;
    }

    return true;
}

function SetVal(obj)
{
    if( obj.checked ) {
        obj.value = 'on';
    } else {
        obj.value = '';
    }
}

// ���Ͼ��󹹿��ܥ��󥯥�å��������򤵤�Ƥ��뤫���¹Ԥ��Ƥ�������
function AmanoRun(rows)
{
    var cnt = 0, obj;
    for( var r=0; r < rows; r++ ) {
        obj = document.getElementsByName("amano" + r);
        if( obj[0] && obj[0].checked ) {
            cnt++;
        }
    }

    if( cnt == 0 ) {
        alert('���� �� �ˤ���������Ϥ����򤵤�Ƥ��ޤ���');
        return false;
    }

    var flag = confirm("���򤷤� " + cnt + " �������Ϥ����� �� ���ѹ����ޤ���\n\n�����᤹���ȤϤǤ��ޤ��󤬡�������Ǥ�����")

    document.getElementsByName('amano_run')[0].value = flag;

    return flag;
}

// ��ü¹ԥܥ��󥯥�å�����������򤵤�Ƥ��뤫���¹Ԥ��Ƥ�������
function CancelRun(rows)
{
    var cnt = 0;
    for( var r=0; r < rows; r++ ) {
        if(  document.getElementsByName(r)[0].checked ) {
            cnt++;
        }
    }

    if( cnt == 0 ) {
        alert('���ä���������Ϥ����򤵤�Ƥ��ޤ���');
        return false;
    }

    var flag = confirm("���򤷤� " + cnt + " �������� ��������ä��ޤ���\n\n�����᤹���ȤϤǤ��ޤ��󤬡�������Ǥ�����")

    document.getElementsByName('cancel_run')[0].value = flag;

    return flag;
}

function InputAllCheck()
{
    if( !document.getElementsByName("c0")[0].checked ) {
        if( !CheckDateRange("si_s_date", "si_e_date") ) return false;
    }

    if( !document.getElementsByName("c1")[0].checked ) {
        if( !CheckDateRange("str_date", "end_date") ) return false;
    }

    huzaisya(false); // �����Բľ��֤Τޤ��ڤ��ؤ���������֤��ꥻ�åȤ����١����ٻ��Ѳ�ǽ���֤ˤ��롣

    return true;
}
