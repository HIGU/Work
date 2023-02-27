//////////////////////////////////////////////////////////////////////////////
// ���Ϲ�������ĥꥹ�ȤξȲ� �������ե����� JavaScript�ˤ�����ϥ����å� //
// Copyright (C) 2015-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/04/26 Created  vendor_order_list_form.js                            //
// 2005/04/30 ����������ľ���������������٤뵡ǽ�ɲ� *�֥�󥯽������ݥ����//
// 2005/05/06 window top = offset�� 60��ޥ��ʥ�WinXP�б� 2005/05/06 ADD    //
// 2015/10/19 ���ʥ��롼�פ�T=�ġ�����ɲ�                             ��ë //
//////////////////////////////////////////////////////////////////////////////

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.vendor_form.vendor.focus();
    document.vendor_form.vendor.select();
}

/* ����ʸ�����������ɤ��������å� */
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

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if ("0">c || c>"9") {
                return false;
            }
        }
    }
    return true;
}

/***** �ե���������ϥ����å� function *****/
function chk_vendor_order_list_form(obj) {
    /* ���ʥ��롼�פ���ʸ���Ѵ� */
    obj.div.value = obj.div.value.toUpperCase();
    /* ȯ���ʬ����ʸ���Ѵ� */
    obj.plan_cond.value = obj.plan_cond.value.toUpperCase();
    
    /* ȯ���襳�������ϥ����å� */
//    if (!obj.vendor.value.length) {
//        vendor_copy();
//    }
    if (!obj.vendor.value.length) {
        alert('ȯ���襳���ɤ����Ϥ���Ƥ��ޤ���');
        obj.vendor.focus();
        obj.vendor.select();
        return false;
    }
    if (obj.vendor.value.length != 5) {
        alert("ȯ���襳���ɤη���ϣ���Ǥ���");
        obj.vendor.focus();
        obj.vendor.select();
        return false;
    }
    if ( !isDigit(obj.vendor.value) ) {
        alert('ȯ���襳���ɤ˿����ʳ���ʸ��������ޤ���');
        obj.vendor.focus();
        obj.vendor.select();
        return false;
    }
    
    /* ���ʥ��롼�פ����ϥ����å� */
    switch (obj.div.value) {
    case 'C':
    case 'L':
    case 'T':
    case 'SC':
    case 'CS':
        break;
    case ' ' :
    case '' :
        /* ���ʥ��롼��2(����ե�����)�Υ֥�󥯻���������� */
        obj.div2.value = '';
        break;
    default:
        alert('���ʥ��롼�פϥ֥��=����, C=���ץ�, L=��˥�, T=�ġ���, SC=C����, CS=Cɸ�� �Τɤ줫�Ǥ���');
        obj.div.focus();
        obj.div.select();
        return false;
    }
    
    /* ȯ��ײ��ʬ�����ϥ����å� */
    switch (obj.plan_cond.value) {
    case 'O':
    case 'R':
    case 'P':
        break;
    case ' ' :
    case '' :
        /* ȯ��ײ��ʬ2(����ե�����)�Υ֥�󥯻���������� */
        obj.plan_cond2.value = '';
        break;
    default:
        alert('ȯ��ײ��ʬ�ϥ֥��=����, O=��ʸ��ȯ�Ժ�, R=�⼨��, P=ͽ�� �Τɤ줫�Ǥ���');
        obj.plan_cond.focus();
        obj.plan_cond.select();      // <input type='text'>����<select>���ѹ��Τ��ᥳ����
        return false;
    }
    return true;
}

/****** Window ���� function *********/
function win_open(url, win_name) {
    if (win_name == 'undefind') return false;
    url += ('?vendor='      + document.vendor_form.vendor.value);
    url += ('&div='         + document.vendor_form.div.value);
    url += ('&plan_cond='   + document.vendor_form.plan_cond.value);
    var w = 830;    // (820=���ꥸ�ʥ�)
    var h = 620;
    var left = ((screen.availWidth  - w) / 2);
    var top  = ((screen.availHeight - h - 60) / 2); // offset�� 60��ޥ��ʥ�WinXP�б� 2005/05/06 ADD
    window.open(url, win_name, 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=yes,top='+top+',left='+left);
}

/****** Window ����2 function *********/
function win_open2(url, win_name) {
    if (win_name == 'undefind') return false;
    if (!chk_vendor_order_list_form(document.vendor_form)) return false;
    url += ('?vendor='      + document.vendor_form.vendor.value);
    url += ('&div='         + document.vendor_form.div.value);
    url += ('&plan_cond='   + document.vendor_form.plan_cond.value);
    var w = 830;    // (820=���ꥸ�ʥ�)
    var h = 620;
    var left = ((screen.availWidth  - w) / 2);
    var top  = ((screen.availHeight - h - 60) / 2); // offset�� 60��ޥ��ʥ�WinXP�б� 2005/05/06 ADD
    window.open(url, win_name, 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=yes,top='+top+',left='+left);
}

/****** Window ����2 function *********/
function csv_output2(url, win_name) {
    if (win_name == 'undefind') return false;
    if (!chk_vendor_order_list_form(document.vendor_form)) return false;
    url += ('?vendor='      + document.vendor_form.vendor.value);
    url += ('&div='         + document.vendor_form.div.value);
    url += ('&plan_cond='   + document.vendor_form.plan_cond.value);
    location.href = url;
    /*var w = 830;    // (820=���ꥸ�ʥ�)
    var h = 620;
    var left = ((screen.availWidth  - w) / 2);
    var top  = ((screen.availHeight - h - 60) / 2); // offset�� 60��ޥ��ʥ�WinXP�б� 2005/05/06 ADD
    window.open(url, win_name, 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=yes,top='+top+',left='+left); */
}

/****** ȯ���襳���ɤΥ��ԡ� function *********/
function vendor_copy() {
    document.vendor_form.vendor.value = document.vendor_form.vendor2.value;
    return true;
}
function vendor_copy2() {
    document.vendor_form.vendor2.value = document.vendor_form.vendor.value;
    return true;
}

/****** ���ʥ��롼�פΥ��ԡ� function *********/
function div_copy() {
    document.vendor_form.div.value = document.vendor_form.div2.value;
    return true;
}
/****** ���ʥ��롼�פεե��ԡ� function *********/
function div_copy2() {
    document.vendor_form.div.value = document.vendor_form.div.value.toUpperCase();
    document.vendor_form.div2.value = document.vendor_form.div.value;
    /* �֥�󥯻���������� */
    switch (document.vendor_form.div.value) {
    case ' ' :
    case '' :
        document.vendor_form.div2.value = '';
        break;
    }
    return true;
}

/****** ȯ��ײ��ʬ�Υ��ԡ� function *********/
function plan_cond_copy() {
    document.vendor_form.plan_cond.value = document.vendor_form.plan_cond2.value;
    return true;
}

/****** ȯ��ײ��ʬ�εե��ԡ� function *********/
function plan_cond_copy2() {
    document.vendor_form.plan_cond.value = document.vendor_form.plan_cond.value.toUpperCase();
    document.vendor_form.plan_cond2.value = document.vendor_form.plan_cond.value;
    /* �֥�󥯻���������� */
    switch (document.vendor_form.plan_cond.value) {
    case ' ' :
    case '' :
        document.vendor_form.plan_cond2.value = '';
        break;
    }
    return true;
}

