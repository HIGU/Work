//////////////////////////////////////////////////////////////////////////////
// �ԣΣ˼Ұ���˥塼�� ���ϥե��������Υ����å��� JavaScript               //
// Copyright (C) 2001-2019      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/07/07 Created   confirm.php                                         //
// 2002/08/07 obj.img_file.value=obj.photo.value; ����ա������PHP�ǤǤ��� //
// 2007/10/15 �ե�����Υ����å��ѥ�����ץȤ��� win_open function ���ɲ�   //
// 2014/07/29 �Ժ߼ԾȲ�Ǥ����ϥ����å��򤷤ʤ��褦�ѹ�               ��ë //
// 2015/07/30 ǯ���Ǥ����ϥ����å��򤷤ʤ����ѹ�                     ��ë //
// 2019/09/17 ͭ�������Ģ��ǯ�٥����å����ɲ�                         ��ë //
//////////////////////////////////////////////////////////////////////////////
/* ����ʸ�����������ɤ��������å� */
function isDigit(str){
    var len = str.length;
    var c;
    for (i=0; i<len; i++){
        c=str.charAt(i);
        if(("0" > c) || (c > "9")) {
            return false;
        }
    }
    return true;
}
/* ���Ͼ���Υ����å� */
function inpConf(obj) {
    if (!obj.userid.value.length) {
        alert("[�Ұ�No]�������󤬶���Ǥ���������Ǥ��ޤ���");
        obj.userid.focus();
        return false;
    }
    if (obj.userid.value.length!=6) {
        alert("[�Ұ�No]�������ͤ������Ǥ���������Ǥ��ޤ���");
        obj.userid.focus();
        return false;
    }
    if (!obj.passwd.value.length) {
        alert("[�ѥ����]�������󤬶���Ǥ���������Ǥ��ޤ���");
        obj.passwd.focus();
        return false;
    }
    return true;
}
/* �����ֹ������å� */
function isTelnum(str) {
    var count = 0;
    var msg = "";
    for (i=0; i<str.length; i++) {
        var c = str.charAt(i);
        if ( ('0' <= c) && (c <= '9') ) {
            count++;
            continue;
        } else {
            if(c == '-') {
                continue;
            }
            msg="�����ֹ�˻��ѤǤ���ʸ���ϡ�\n"+
                "Ⱦ�ѿ����Ȥȥϥ��ե�(-)�����Ǥ���";
            alert(msg);
            return false;
        }
    }
    if (count < 10) {
        msg="�����ֹ�η����10��̤���Ǥ���\n"+
            "�Գ����֤������Ϥ��Ƥ���������";
        alert(msg);
        return false;
    }
    return true;
}
/* ��������˻���ʸ����¸�ߤ��뤫������å� */
function isInchar(str,substr) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == substr)
            return i+1;
    }
    return 0;
}
/* �������ܤΥ����å� */
function chkLookupTermsY(obj) {
    if (obj.lookupkeykind.selectedIndex != 0) {
        if (obj.lookupkeykind.selectedIndex != 5 && obj.lookupkeykind.selectedIndex != 6) {
            if (!obj.lookupkey.value.length) {
                alert("���ܾ���θ��������������󤬶���Ǥ��������ϹԤ��ޤ���");
                obj.lookupkey.focus();
                return false;
            }
            if (obj.lookupkeykind.selectedIndex == 2) {
                /* �ե�͡���ˤ�븡�����ϥ��ڡ�����ɬ�� */
                if ( !(isInchar(obj.lookupkey.value," ") || isInchar(obj.lookupkey.value,"��")) ){
                    alert("�ե�͡���ˤ�븡����������̾�����ڡ����Ƕ��ڤ��Ƥ��ʤ���Фʤ�ޤ���");
                    obj.lookupkey.focus();
                    obj.lookupkey.select();
                    return false;   
                }
                var str    = obj.lookupkey.value;
                var len    = str.length;
                var substr = "";
                var c;
                for(i=0; i<len; i++) {
                    c = str.charAt(i);
                    if(c == "��") {
                        c = " ";
                    }
                    substr += c;
                }
                obj.lookupkey.value = substr;
            } else {
                /* ���ڡ���������� */
                var str    = obj.lookupkey.value;
                var len    = str.length;
                var substr = "";
                var c;
                for (i=0; i<len; i++) {
                    c = str.charAt(i);
                    if((c == " ") || (c == "��")) {
                        obj.lookupkey.value = substr;
                        break;
                    }
                    substr += c;
                }
            }
        }
    }
    if (obj.lookupyukyukind.selectedIndex != 0) {
        if (!obj.lookupyukyu.value.length) {
            alert("ͭ�����θ��������������󤬶���Ǥ������������Ϥ��Ƥ���������");
            obj.lookupyukyu.focus();
            return false;
        }
        if (!isDigit(obj.lookupyukyu.value)) {
            alert("���������ˤ�Ⱦ�ѿ�������ꤷ�Ƥ���������");
            obj.lookupyukyu.focus();
            obj.lookupyukyu.select();
            return false;
        }
    }
    if (obj.lookupyukyufive.selectedIndex != 0) {
        if (!obj.lookupyukyuf.value.length) {
            alert("ͭ��5������θ��������������󤬶���Ǥ������������Ϥ��Ƥ���������");
            obj.lookupyukyuf.focus();
            return false;
        }
        if (!isDigit(obj.lookupyukyuf.value)) {
            alert("���������ˤ�Ⱦ�ѿ�������ꤷ�Ƥ���������");
            obj.lookupyukyuf.focus();
            obj.lookupyukyuf.select();
            return false;
        }
    }
    return true;
}

/* �������ܤΥ����å� */
function chkLookupTerms(obj) {
    if (obj.lookupkeykind.selectedIndex != 0) {
        if (obj.lookupkeykind.selectedIndex != 5 && obj.lookupkeykind.selectedIndex != 6) {
            if (!obj.lookupkey.value.length) {
                alert("�����оݤ������󤬶���Ǥ��������ϹԤ��ޤ���");
                obj.lookupkey.focus();
                return false;
            }
            if (obj.lookupkeykind.selectedIndex == 2) {
                /* �ե�͡���ˤ�븡�����ϥ��ڡ�����ɬ�� */
                if ( !(isInchar(obj.lookupkey.value," ") || isInchar(obj.lookupkey.value,"��")) ){
                    alert("�ե�͡���ˤ�븡����������̾�����ڡ����Ƕ��ڤ��Ƥ��ʤ���Фʤ�ޤ���");
                    obj.lookupkey.focus();
                    obj.lookupkey.select();
                    return false;   
                }
                var str    = obj.lookupkey.value;
                var len    = str.length;
                var substr = "";
                var c;
                for(i=0; i<len; i++) {
                    c = str.charAt(i);
                    if(c == "��") {
                        c = " ";
                    }
                    substr += c;
                }
                obj.lookupkey.value = substr;
            } else {
                /* ���ڡ���������� */
                var str    = obj.lookupkey.value;
                var len    = str.length;
                var substr = "";
                var c;
                for (i=0; i<len; i++) {
                    c = str.charAt(i);
                    if((c == " ") || (c == "��")) {
                        obj.lookupkey.value = substr;
                        break;
                    }
                    substr += c;
                }
            }
        }
    }
    return true;
}

/* ���Ϥ��줿�ѥ���ɤΥ����å� */
function chkPasswd(obj) {
    if (!obj.passwd.value.length) {
        alert("[�������ѥ����]�������󤬶���Ǥ���");
        obj.passwd.focus();
        return false;
    }
    if (!obj.repasswd.value.length) {
        alert("[�ѥ���ɳ�ǧ]�������󤬶���Ǥ���");
        obj.repasswd.focus();
        return false;
    }
    if (obj.passwd.value!=obj.repasswd.value) {
        alert("�ѥ���ɤ����פ��ޤ���");
        obj.repasswd.focus();
        obj.repasswd.select();
        return false;
    }
    return true;
}
/* DB����������Ƥ�����å� */
function chkUserQuery(obj) {
    if (!obj.userquery.value.length) {
        alert("�桼���������꡼�����Ϥ���Ƥ��ޤ���");
        obj.userquery.focus();
        return false;
    }
    return true;
}
/* �����桼�������������Ƥ�����å� */
function chkUserInfo(obj) {
    if (!obj.userid.value.length) {
        alert("[�Ұ�No]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.userid.focus();
        return false;
    }
    if (obj.userid.value.length!=6) {
        alert("[�Ұ�No]�������ͤ������Ǥ������������Ϥ�ԤäƤ���������");
        obj.userid.focus();
        obj.userid.select();
        return false;
    }
    if (!obj.name_1.value.length) {
        alert("[��̾]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.name_1.focus();
        return false;
    }
    if (!obj.name_2.value.length) {
        alert("[��̾]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.name_2.focus();
        return false;
    }
    if (!obj.kana_1.value.length) {
        alert("[�եꥬ��]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.kana_1.focus();
        return false;
    }
    if (!obj.kana_2.value.length) {
        alert("[�եꥬ��]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.kana_2.focus();
        return false;
    }
    if (!obj.spell_2.value.length) {
        alert("[���ڥ�]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.spell_2.focus();
        return false;
    }
    if (obj.spell_2.value.match(/[^a-z]/g)) {
        alert("[���ڥ�]�������ͤ������Ǥ���Ⱦ�Ѿ�ʸ�������Ϥ�ԤäƤ���������");
        obj.spell_2.focus();
        return false;
    }
    if (!obj.spell_1.value.length) {
        alert("[���ڥ�]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.spell_1.focus();
        return false;
    }
    if (obj.spell_1.value.match(/[^a-z]/g)) {
        alert("[���ڥ�]�������ͤ������Ǥ���Ⱦ�Ѿ�ʸ�������Ϥ�ԤäƤ���������");
        obj.spell_1.focus();
        return false;
    }
    if ( (!obj.zipcode_1.value.length) || (obj.zipcode_1.value.length != 3) ) {
        alert("[͹���ֹ�]�������󤬶�������ʸ�����������Ǥ������������Ϥ�ԤäƤ���������");
        obj.zipcode_1.focus();
        obj.zipcode_1.select();
        return false;
    }
    if ( (!obj.zipcode_2.value.length) || (obj.zipcode_2.value.length != 4) ) {
        alert("[͹���ֹ�]�������󤬶�������ʸ�����������Ǥ������������Ϥ�ԤäƤ���������");
        obj.zipcode_2.focus();
        obj.zipcode_2.select();
        return false;
    }
    if (!isDigit(obj.zipcode_1.value)) {
        alert("[͹���ֹ�]�ˤ������ͤ���ꤷ�Ƥ���������");
        obj.zipcode_1.focus();
        obj.zipcode_1.select();
        return false;
    }
    if (!isDigit(obj.zipcode_2.value)) {
        alert("[͹���ֹ�]�ˤ������ͤ���ꤷ�Ƥ���������");
        obj.zipcode_2.focus();
        obj.zipcode_2.select();
        return false;
    }
    /* �ѹ��Ľ� 2001/11/29 �������� */
    if (!obj.address.value.length) {
        alert("[����]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.address.focus();
        return false;
    }
    if (obj.address.value.length > 64) {
        alert("[����]�������ʸ�����������Ǥ������������Ϥ�ԤäƤ���������");
        obj.address.focus();
        obj.address.select();
        return false;
    }
    /* �����ޤ� */
    if (!obj.tel.value.length) {
        alert("[�����ֹ�]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.tel.focus();
        return false;
    } 
    if (!isTelnum(obj.tel.value)) {
        obj.tel.focus();
        obj.tel.select();
        return false;   
    }
    if (!isInchar(obj.tel.value,"-")) {
        alert("�����ֹ�϶��֤δ֤�\"-\"������Ƥ���������");
        obj.tel.focus();
        obj.tel.select();
        return false;   
    }
    if (!obj.birthday_1.value.length) {
        alert("[��ǯ����]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.birthday_1.focus();
        obj.birthday_1.select();
        return false;
    }
    if (!obj.birthday_2.value.length) {
        alert("[��ǯ����]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.birthday_2.focus();
        obj.birthday_2.select();
        return false;
    }
    if (!obj.birthday_3.value.length) {
        alert("[��ǯ����]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.birthday_3.focus();
        obj.birthday_3.select();
        return false;
    }
    if (!isDigit(obj.birthday_1.value)) {
        alert("[��ǯ����]�ˤ������ͤ���ꤷ�Ƥ���������");
        obj.birthday_1.focus();
        obj.birthday_1.select();
        return false;
    }
    if (!isDigit(obj.birthday_2.value)) {
        alert("[��ǯ����]�ˤ������ͤ���ꤷ�Ƥ���������");
        obj.birthday_2.focus();
        obj.birthday_2.select();
        return false;
    }
    if (!isDigit(obj.birthday_3.value)) {
        alert("[��ǯ����]�ˤ������ͤ���ꤷ�Ƥ���������");
        obj.birthday_3.focus();
        obj.birthday_3.select();
        return false;
    }
    if (obj.birthday_1.value < 1900) {
        alert("[��ǯ����]�������ͤ������Ǥ���");
        obj.birthday_1.focus();
        obj.birthday_1.select();
        return false;
    }
    if ( (obj.birthday_2.value == 0) || (obj.birthday_2.value > 12) ){
        alert("[��ǯ����]�������ͤ������Ǥ���");
        obj.birthday_2.focus();
        obj.birthday_2.select();
        return false;
    }
    if( (obj.birthday_3.value == 0) || (obj.birthday_3.value > 31) ){
        alert("[��ǯ����]�������ͤ������Ǥ���");
        obj.birthday_3.focus();
        obj.birthday_3.select();
        return false;
    }
    if (!obj.entrydate_1.value.length) {
        alert("[����ǯ����]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.entrydate_1.focus();
        return false;
    }
    if (!obj.entrydate_2.value.length) {
        alert("[����ǯ����]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.entrydate_2.focus();
        return false;
    }
    if (!obj.entrydate_3.value.length) {
        alert("[����ǯ����]�������󤬶���Ǥ������������Ϥ�ԤäƤ���������");
        obj.entrydate_3.focus();
        return false;
    }
    if (!isDigit(obj.entrydate_1.value)) {
        alert("[����ǯ����]�ˤ������ͤ���ꤷ�Ƥ���������");
        obj.entrydate_1.focus();
        obj.entrydate_1.select();
        return false;
    }
    if (!isDigit(obj.entrydate_2.value)) {
        alert("[����ǯ����]�ˤ������ͤ���ꤷ�Ƥ���������");
        obj.entrydate_2.focus();
        obj.entrydate_2.select();
        return false;
    }
    if (!isDigit(obj.entrydate_3.value)) {
        alert("[����ǯ����]�ˤ������ͤ���ꤷ�Ƥ���������");
        obj.entrydate_3.focus();
        obj.entrydate_3.select();
        return false;
    }
    if (obj.entrydate_1.value < 1900) {
        alert("[����ǯ����]�������ͤ������Ǥ���");
        obj.entrydate_1.focus();
        obj.entrydate_1.select();
        return false;
    }
    if ( (obj.entrydate_2.value == 0) || (obj.entrydate_2.value > 12) ){
        alert("[����ǯ����]�������ͤ������Ǥ���");
        obj.entrydate_2.focus();
        obj.entrydate_2.select();
        return false;
    }
    if ( (obj.entrydate_3.value == 0) || (obj.entrydate_3.value > 31) ){
        alert("[����ǯ����]�������ͤ������Ǥ���");
        obj.entrydate_3.focus();
        obj.entrydate_3.select();
        return false;
    }
    obj.img_file.value = obj.photo.value;
    return true;
}
/* ���Ͼ���ʻ����Ͽ��������Ͽ�ˤΥ����å� */
function chkData(obj) {
    if (!isDigit(obj.begin_date_1.value)) {
        alert("ǯ�����ˤ������ͤ���ꤷ�Ƥ���������");
        obj.begin_date_1.focus();
        obj.begin_date_1.select();
        return false;
    }
    if (!isDigit(obj.begin_date_2.value)) {
        alert("ǯ�����ˤ������ͤ���ꤷ�Ƥ���������");
        obj.begin_date_2.focus();
        obj.begin_date_2.select();
        return false;
    }
    if (!isDigit(obj.begin_date_3.value)) {
        alert("ǯ�����ˤ������ͤ���ꤷ�Ƥ���������");
        obj.begin_date_3.focus();
        obj.begin_date_3.select();
        return false;
    }
    if (!isDigit(obj.end_date_1.value)) {
        alert("ǯ�����ˤ������ͤ���ꤷ�Ƥ���������");
        obj.begin_date_1.focus();
        obj.begin_date_1.select();
        return false;
    }
    if (!isDigit(obj.end_date_2.value)) {
        alert("ǯ�����ˤ������ͤ���ꤷ�Ƥ���������");
        obj.end_date_2.focus();
        obj.end_date_2.select();
        return false;
    }
    if (!isDigit(obj.end_date_3.value)) {
        alert("ǯ�����ˤ������ͤ���ꤷ�Ƥ���������");
        obj.end_date_3.focus();
        obj.end_date_3.select();
        return false;
    }
    if (!isDigit(obj.entry_num.value)) {
        alert("��Ͽ�Ϳ��ˤ������ͤ���ꤷ�Ƥ���������");
        obj.entry_num.focus();
        obj.entry_num.select();
        return false;
    }
    return true;
}

/* �������ܤΥ����å� */
function chkLookupFive(obj) {
    if (!obj.yukyulist.value.length) {
        alert("ͭ�������Ģ��ǯ���󤬶���Ǥ��������ϹԤ��ޤ���");
        obj.yukyulist.focus();
        return false;
    }
    if (!isDigit(obj.yukyulist.value)) {
        alert("ǯ�٤ˤ�Ⱦ�ѿ�������ꤷ�Ƥ���������");
        obj.yukyulist.focus();
        obj.yukyulist.select();
        return false;
    }
    if (obj.yukyulist.value.length!=4) {
        alert("ǯ�٤�4��ǻ��ꤷ�Ƥ���������");
        obj.yukyulist.focus();
        obj.yukyulist.select();
        return false;
    }
    return true;
}

/***** ������ɥ��Υ����ץ�ؿ�  *****/
function win_open(url, w, h) {
    if (!w) w = 800;     // �����
    if (!h) h = 600;     // �����
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    w -= 10; h -= 30;   // ��Ĵ����ɬ��
    window.open(url, '', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,resizeble=yes,top='+top+',left='+left);
}
