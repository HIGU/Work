////////////////////////////////////////////////////////////////////////////////
// ����ϡʿ�����                                                             //
//                                            MVC View �� (JavaScript���饹)  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou.js                                               //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// alert("TEST : ");

// ����name�ƥ����ȥܥå����λ��Ѳ��� true���ͥ��ꥢ�����Բġ�false�����Ѳ�ǽ
function NameTextReset( name, flag )
{
    if( flag ) {
        document.getElementsByName(name)[0].value = "";
    }
    document.getElementsByName(name)[0].disabled = flag;
}

// ����ϤΥƥ����ȥܥå��� true = �����
function IkisakiText( flag )
{
    NameTextReset( "ikisaki", flag );
    NameTextReset( "todouhuken", flag );
    NameTextReset( "mokuteki", flag );
    NameTextReset( "setto1", flag );
    NameTextReset( "setto2", flag );
    NameTextReset( "doukou", flag );
}

// �ƥ����ȥܥå����ν����
function TextInit()
{
    IkisakiText(true);

    NameTextReset( "hurikae", true );
    NameTextReset( "tokubetu_sonota", true );
    NameTextReset( "syousai_sonota", true );
    NameTextReset( "tel_sonota", true );
    NameTextReset( "tel_no", true );
    NameTextReset( "bikoutext", true );
//    NameTextReset( "outai", true );
}

// ����name�Υ饸���ܥ���Υ����å��򳰤�
function NameRadioReset(name)
{
    var obj = document.getElementsByName(name);

    for(var i=0; i<obj.length; i++) {
        obj[i].checked = false;
    }
}

// ����name�Υ饸���ܥ���λ��Ѳ���
function NameRadioDisabl(name, flag)
{
    var obj = document.getElementsByName(name);
    for(var i=0; i<obj.length; i++) {
        obj[i].disabled = flag;
    }
}

// �ܥ���Ϥν����
function ButtonInit(button_array, syokai)
{
    for( var idx = 0; idx < button_array.length; idx++ ) {
        var button = document.getElementsByName(button_array[idx]);
        NameRadioReset(button_array[idx]);
        if( !syokai && (button_array[idx] == "r1" || button_array[idx] == "c2") ) continue;
        NameRadioDisabl(button_array[idx], true);
    }
}

// ����name�Υɥ�åץ�����ꥹ�Ȼ��Ѳ���
function NameDdlistDisabl(name, flag)
{
    var obj = document.getElementsByName(name);

    for( var idx = 0; idx < obj.length; idx++ ) {
        obj[idx].disabled = flag;
    }
}

// ����ID��ʸ������ ���졼 or �� ������
function setDisableStyle(id, flag)
{
    var obj = document.getElementById(id);

    if( flag ) {
        obj.style.color = 'DarkGray';   //ʸ�����򥰥졼�ˤ���
    } else {
        obj.style.color = 'black';  //ʸ��������ˤ���
    }
}

function setDisableStyleRed(id, flag)
{
    var obj = document.getElementById(id);

    if( obj ) {
        if( flag ) {
            obj.style.color = 'DarkGray';   //ʸ�����򥰥졼�ˤ���
        } else {
            obj.style.color = 'red';  //ʸ�������֤ˤ���
        }
    }
}

// �ڡ����ɤ߹��߻������ƤӽФ��������
function Init()
{
    TextInit();

    var flag = false;

    var obj = document.getElementsByName('syainbangou');

    if( obj[0] ) { // �Ұ��ֹ�����
        flag = true;
        obj[0].focus();
        obj[0].select();
    } else {
        SetWorkTime();
//        setDisableStyle('id_renraku', true);
/*
        var obj_kei = document.getElementById('id_keiyaku');
        if( obj_kei ) {
            alert("����Ұ��Τ��ᡢ��λ���֤μ�ư���åȤ��Ǥ��ޤ���\n\n�����κݤϡ����֤�褯����ǧ��������" );
        }
*/
    }

    var button_array = new Array("r1","r2","r3","r4","r5","r6","c2");
    ButtonInit(button_array, flag);

    setDisableStyle('1000' , true);
    setDisableStyle('2000' , true);
    setDisableStyle('2500' , true);
    setDisableStyleRed('2550' , true);
    setDisableStyle('3000' , true);

    var obj2 = document.getElementsByName('approval'); // ��ǧ�롼�ȼ���
    setDisableStyle('idc2l' , flag);
    if( !obj[0] && obj2[0].value=='' ) { // ��ǧ�롼����Ͽ����Ƥʤ��Ȥ�
        document.getElementsByName("bikoutext")[0].disabled = !flag;
        document.getElementsByName("c2")[0].disabled = !flag;
        setDisableStyle('idc2l' , !flag);
        document.getElementsByName("submit")[0].disabled = !flag;
    } else {
        document.getElementsByName("bikoutext")[0].disabled = flag;
        document.getElementsByName("c0")[0].checked = !flag;
        document.getElementsByName("submit")[0].disabled = flag;
        document.getElementsByName("cancel")[0].disabled = flag;
    }

    OneDay(!flag);
    telno();

    SinseiDate(); StartDateCopy(); EndDateCopy(); StartTimeCopy(); EndTimeCopy();
    NameRadioDisabl("r6", flag);

    if( obj[0] ) { // �Ұ��ֹ�����
        OneDay(flag);
    }
}

// ��ǧ��꽤���ΰ١�ɽ��������
function AdmitEdit()
{
    OneDay(document.getElementsByName("c0")[0].checked);
    syousai();
    telno();
    StartDateCopy(); EndDateCopy(); StartTimeCopy(); EndTimeCopy();
}

function SougouUpdate()
{
    document.getElementsByName("sougou_update")[0].value = 'on';
}

// ID�������̤�˺��ʽжС�����������ϻ��֡����� �Υ����å�
function IsWorkStartTime()
{
    var start = 11 - document.getElementById("id_s_work").value;
    if( start == 8 ) {
        start = ('0'+start).slice(-2) + ":30";
    } else {
        start = ('0'+start).slice(-2) + ":15";
    }

    var str_time = document.getElementsByName("str_time")[0].value;
    if( str_time == start ) {
        document.getElementsByName("end_time")[0].value = "";
        return true;
    }

    if( document.getElementsByName('bikoutext')[0].value.match(/\S/g) ) return true;

    alert("���ꤷ�����ϻ���(" + str_time + ")�ϡ�\n\n���ȳ��ϻ���(" + start + ")�ǤϤ���ޤ���\n\n���ͤ���ͳ�����Ϥ��Ʋ�������");

    return false;
//alert("�ʽжС�" + document.getElementsByName("str_time")[0].value + " = " + start);
}

// ID�������̤�˺�����С����������λ���֡����� �Υ����å�
function IsWorkEndTime()
{
    var end = 12 + parseInt(document.getElementById("id_e_work").value, 10);
    if( end == 16 || end == 17 ) {
        end += ":15";
    } else {
        end += ":00";
    }

    var end_time = document.getElementsByName("end_time")[0].value;
    if( end_time == end ) {
        document.getElementsByName("str_time")[0].value = "";
        return true;
    }
/*
    var obj_kei = document.getElementById('id_keiyaku');
    if( obj_kei ) {
        end = "16:15";
        if( end_time == end ) return true;
        end += " or 17:15";
    }
*/
    if( document.getElementsByName('bikoutext')[0].value.match(/\S/g) ) return true;

    alert("���ꤷ����λ����(" + end_time + ")�ϡ�\n\n���Ƚ�λ����(" + end + ")�ǤϤ���ޤ���\n\n���ͤ���ͳ�����Ϥ��Ʋ�������");

    return false;
//alert("����С�" + document.getElementsByName("end_time")[0].value + " = " + end);
}

var def_sh = 0;
var def_sm = 0;
var def_eh = 0;
var def_em = 0;
function SetDefTime()
{
    def_sh = 11 - document.getElementById("id_s_work").value;

    if( def_sh == 8 ) {
        def_sm = 30;
    } else {
        def_sm = 15;
    }

    def_eh = 12 + parseInt(document.getElementById("id_e_work").value, 10);

    if( def_eh == 16 || def_eh == 17 ) {
        def_em = 15;
    } else {
        def_em = 0;
    }
//alert("TEST : " + def_sh + ":" + def_sm + " - " + def_eh + ":" + def_em);
}

function SetWorkTime()
{
//alert(document.getElementById("id_t_work").value);
//alert(document.getElementById("id_s_work").value);
    var start = 11 - document.getElementById("id_s_work").value;

    document.getElementById("id_shh")[start].selected = true;
    if( start == 8 ) {
        document.getElementById("id_smm")[6].selected = true;
    } else {
        document.getElementById("id_smm")[3].selected = true;
    }

//alert(document.getElementById("id_e_work").value);
    var end = 12 + parseInt(document.getElementById("id_e_work").value, 10);

    document.getElementById("id_ehh")[end].selected = true;
    if( end == 16 || end == 17 ) {
        document.getElementById("id_emm")[3].selected = true;
    } else {
        document.getElementById("id_emm")[0].selected = true;
    }

    StartTimeCopy();
    EndTimeCopy();
}

/* waki 2021.06.04 --------------------------------------------------------> */
// �ĶȻ��֤Υ����å�
function IsOverTime()
{
    var year   = document.getElementById("id_syear").value;
    var month  = document.getElementById("id_smonth").value-1;
    var day    = document.getElementById("id_sday").value;
/**
    // �Ķȳ��ϲ�ǽ���� ------------------------------------------------------>
    var def_h = def_eh;
    var def_m = def_em;
    if( def_h == 17 ) {
        def_m = 30;
    }
    var d_d = new Date(year, month, day, def_h, def_m, 00);
    // <-----------------------------------------------------------------------
/**/
    // ���򤷤��Ķȳ��ϻ��� -------------------------------------------------->
    var hour   = document.getElementById("id_shh").value;
    var minute = document.getElementById("id_smm").value;

    var s_d = new Date(year, month, day, hour, minute, 00);
    // <-----------------------------------------------------------------------
//alert("TEST : ���� : " + hour + ":" + minute);

/**
    if( d_d > s_d ) {
        alert("���ϻ��֤� �Ķȳ��ϻ���(" + def_h + ":" + def_m + ")\n\n��λ���֤� �ĶȽ�λ���� �ˤ��Ʋ�������");
        document.getElementById("id_ehh")[def_eh].selected = true;
        if( def_eh == 16 ) {
            document.getElementById("id_emm")[3].selected = true;
        } else if( def_eh == 17 ) {
            document.getElementById("id_emm")[6].selected = true;
        } else {
            document.getElementById("id_emm")[0].selected = true;
        }
        EndTimeCopy();
        return false;
    }
/**
    // �ĶȽ�λ��ǽ���� ------------------------------------------------------>
    if( def_h == 16 ) {
        def_m = 44;
    } else if( def_h == 17 ) {
        def_m = 59;
    }
    d_d = new Date(year, month, day, def_h, def_m, 00);
    // <-----------------------------------------------------------------------
/**/
    // ���򤷤��ĶȽ�λ���� -------------------------------------------------->
    hour   = document.getElementById("id_ehh").value;
    minute = document.getElementById("id_emm").value;

    var e_d = new Date(year, month, day, hour, minute, 00);
    // <-----------------------------------------------------------------------
/**
    if( d_d >= e_d ) {
        alert("��λ���֤� �ĶȽ�λ���� �ˤ��Ʋ�������");
        return false;
    }
/**/
    if( s_d >= e_d ) {
        alert("���Ϥϡ��Ķȳ��ϻ���\n\n��λ�ϡ��ĶȽ�λ���� �ˤ��Ʋ�������");
        return false;
    }
//alert("TEST : ��λ : " + hour + ":" + minute);

//    setStartTimeDisable(false);
    return true;
}
/* <------------------------------------------------------------------------ */

// �ײ�ν���
function Iskeikaku()
{
//alert("TEST �� : ");
    if( !document.getElementById("209").checked ) return true;

    var first_half  = 3;   // ���( 4/1��9/30)�ײ����22�� 3��
    var second_half = 3;   // ����(10/1��3/31)�ײ����22�� 3��
    var ki_total = first_half + second_half;
/**/
    var jisseki = Number(document.getElementById("id_k_jisseki").value);
    var yotei_1 = Number(document.getElementById("id_k_yotei_1").value);
    var yotei_2 = Number(document.getElementById("id_k_yotei_2").value);
//alert("TEST �� : " + jisseki + " : " + yotei_1 + " : " + yotei_2);
/**/
    var yotei = (jisseki + yotei_1 + yotei_2);
//alert("TEST �� : " + yotei + " : " + PeriodDays);

    // ͭ�ټ������Ӥ�������ξ��̤���ʤ������ǽ��
    if( first_half > (yotei + PeriodDays) ) return true;

    // ͭ�ټ�����
    var year  = document.getElementById("id_syear").value;
    var month = document.getElementById("id_smonth").value;

    var sin_date = (document.getElementsByName("sin_date")[0].value).substr(0,4);
    var sin_year = (document.getElementsByName("sin_date")[0].value).substr(0,4);
//alert("TEST �� : " + sin_date + " : " + sin_year + " : " + year + " : " + month);
    if( year > sin_year && month > 3 ) return true; // ���ʬ�ϥ��롼���롣

    if( (yotei % 1) == 0.5 ) yotei -= 0.5;

    var msg = "���߼���ͭ�٤ϡ�" + yotei + "��ʬ�Ǥ���(��ͽ��ޤ�)\n\n���� " + PeriodDays + "��ʬ�������褦�Ȥ��Ƥ��ޤ�����\n\n";
    // ��� or ����
    if( month > 3 && month < 10) {
//alert("TEST �� : ���" + jisseki + " : " + yotei_1);
        // ����˼�������ݡ�����ײ�ͭ�٤ξ��̤���ʤ������ǽ
        if( (first_half +1) <= (jisseki + yotei_1 + PeriodDays) ) {
            alert(msg + "*** ��� *** �ײ�ͭ�٤ξ��(" + first_half + "��)��Ķ���뤿�ᡢ\n\n�ײ�ͭ�٤Ǥο����ϤǤ��ޤ���");
            document.getElementById("209").checked = false;
            return false;
        }
    } else {
//alert("TEST �� : ����" + jisseki + " : " + yotei_1 + " : " + yotei_2);
        // �����˼�������ݡ�ǯ�ַײ�ͭ�٤ξ��̤���ʤ������ǽ
        if( (ki_total +1) <= (jisseki + yotei_1 + yotei_2 + PeriodDays) ) {
            alert(msg + "*** ǯ�� *** �ײ�ͭ�٤ξ��(" + ki_total + "��)��Ķ���뤿�ᡢ\n\n�ײ�ͭ�٤Ǥο����ϤǤ��ޤ���");
            document.getElementById("209").checked = false;
            return false;
        }
    }
//alert("TEST ��" + jisseki);
    return true;
}

// �ٲ˷Ϥν���
function kyuuka( flag, no )
{
    if( no==0 || no==1 || no==2 ) {
        SetWorkTime();
    }

    if( no==1 ) { // AMȾ���ٲˤΰ١���λ���ﶯ�����å�
        document.getElementById("id_ehh")[12].selected = true;
        document.getElementById("id_emm")[0].selected = true;
        EndTimeCopy();
    }

    if( no==2 ) { // PMȾ���ٲˤΰ١����ϻ��ﶯ�����å�
        document.getElementById("id_shh")[12].selected = true;
        document.getElementById("id_smm")[9].selected = true;
        StartTimeCopy();
    }

    NameRadioDisabl("r2", flag);

    setDisableStyle('1000', flag);
    if( flag ){
        NameRadioReset("r2");
    }

    if( no==0 ) { // ͭ��ٲ˻����ײ�ͭ�٣ϣ�
        document.getElementById('209').disabled = false;
        setDisableStyle('keikaku', false);
    } else {    // ����ʳ����ײ�ͭ�٣ϣƣ�
        document.getElementById('209').checked = false;
        document.getElementById('209').disabled = true;
        setDisableStyle('keikaku', true);
    }
    if( no==0 || no==1 || no==2 || no==3  ) { // ͭ�١�Ⱦ�������ֵ٤ʤ顢���̷ײ�ϣ�
        document.getElementById('210').disabled = false;
        setDisableStyle('tokukei', false);
    } else {    // ����ʳ������̷ײ�ϣƣ�
        document.getElementById('210').checked = false;
        document.getElementById('210').disabled = true;
        setDisableStyle('tokukei', true);
    }
}

/***** ����ɽ���᥽�å� *****/
/***** blink_flg Private property �������0.5��������� *****/
/***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
var blink_flag = 1;
var blink_msg  = "";
function blink_disp(id_name)
{
    if( blink_flag == 1 ) {
        // ����ͤ�ץ�ѥƥ��ǻ��ꤷ������ʲ��򥳥���
        // this.blink_msg = document.getElementById(id_name).innerHTML;
        blink_msg = document.getElementById(id_name).innerHTML;
        document.getElementById(id_name).innerHTML = "&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;";    // [00/00] ����
        blink_flag = 2;
    } else {
        document.getElementById(id_name).innerHTML = blink_msg;
        blink_flag = 1;
    }
}

// �說�����ܼ�
function vaccine()
{
    if( document.getElementsByName("r1")[11].disabled ) return;

    document.getElementsByName("r1")[11].checked=true;
    syousai();
    if( document.getElementById("506").disabled ) return;
    
    document.getElementsByName("tokubetu_sonota")[0].value="�說�����ܼ�";
}

// ����������ԲĤ��б�
// suica �ν���
function suica()
{
    var r3 = document.getElementsByName("r3");

    if( !r3[0].checked && r3[1].checked ) {
        document.getElementsByName('setto1')[0].value=1;
    } else {
        r3[0].checked = true;
        document.getElementsByName('setto1')[0].value="";
    }
}

// ������ν���
function setto()
{
    var r3 = document.getElementsByName("r3");
    if( !r3[0].value ) return;
    var r4 = document.getElementsByName("r4");
    if( !r4[0].value ) return;

    if( r3[1].checked || r3[2].checked ) {
        NameTextReset("setto1", false);
    } else {
        r3[0].checked = true;
        NameTextReset("setto1", true);
    }

    if( r4[1].checked || r4[2].checked ) {
        NameTextReset("setto2", false);
    } else {
        r4[0].checked = true;
        NameTextReset("setto2", true);
    }
}

// ����Ϥν���
function ikisaki( flag, no )
{
    IkisakiText(flag);

    NameRadioDisabl("r3", flag);
    NameRadioDisabl("r4", flag);

    setDisableStyle('2000', flag);
    setDisableStyle('2500', flag);
    setDisableStyleRed('2550', flag);
    var obj = document.getElementById('2550');
    if( obj ) {
        if( !flag ) {
            obj.setAttribute("href", "../in_account_appli/download_file.php/������������(��).xls");   // href°�����դ���
        } else {
            obj.removeAttribute("href");    // href°����ʤ���
        }
    }

    if( no == 8 || no == 9 ) {  // ľ�� �ޤ��ϡ�ľ��
        setDisable( 'id_time_area', !flag );
        setDisable( 'id_time_sum_area', !flag );
        if( no == 8 ) {
            setEndTimeDisable(!flag);    // ľ�� ��λ���ֻ����Բ�
        } else if( no == 9 ) {
            setStartTimeDisable(!flag);  // ľ�� ���ϻ��ֻ����Բ�
        }
    }

    if( flag ){
        NameRadioReset("r3");
        NameRadioReset("r4");
    }

    if( document.getElementById('id_suica') ) {
        suica();
    } else {
        setto();
    }

    if( flag ){
        NameRadioReset("r3");
        NameRadioReset("r4");
    }
}

// ���̵ٲ���Τ���¾�ν���
function toku()
{
    var flag = true;
    var r5 = document.getElementsByName("r5");
    if( r5[(r5.length-1)].checked ){
        flag = false;
    }
    NameTextReset("tokubetu_sonota", flag);
}

// ���̵ٲˤν���
function tokubetu( flag )
{
    NameRadioDisabl("r5", flag);
    setDisableStyle('3000', flag);

    if( flag ){
        NameRadioReset("r5");
    }

    toku();
}

// ���ص����ν���
function hurikae( flag )
{
    NameTextReset("hurikae", flag);
}

// ����ID�λ��Ѳ���
function setDisable( id, flag )
{
    document.getElementById(id).disabled = flag;
    setDisableStyle(id, flag);
}

// ���֡����ϻ��֤λ��Ѳ���
function setStartTimeDisable( flag )
{
    setDisable( 'id_start_time_area', flag );
    setDisable( 'id_shh', flag );
    setDisable( 'id_smm', flag );
    setDisable( '001', flag );
    setDisable( '002', flag );
    setDisable( 'id_sum_hour', flag );
    setDisable( 'id_sum', flag );
}

// ���֡���λ���֤λ��Ѳ���
function setEndTimeDisable( flag )
{
    setDisable( 'id_end_time_area', flag );
    setDisable( 'id_ehh', flag );
    setDisable( 'id_emm', flag );
    setDisable( '001', flag );
    setDisable( '002', flag );
    setDisable( 'id_sum_hour', flag );
    setDisable( 'id_sum', flag );
}

// Ϣ����ν��� + 14.ID�ж� 15.ID��� 16.���� 18.ID��Сܻ���
function renraku( flag , no )
{
    NameRadioDisabl("r6", flag);
    setDisableStyle('id_renraku', flag);

    if( flag ){
        NameRadioReset("r6");
        if( no == 14 || no == 15 ) {    // ID˺��(�ж�) �ޤ��ϡ�ID˺��(���)
            setDisable( 'id_time_area', flag );
            setDisable( 'id_time_sum_area', flag );
            if( no == 14 ) {
                setEndTimeDisable(flag);    // ID˺��(�ж�) ��λ���ֻ����Բ�
            } else if( no == 15 ) {
                setStartTimeDisable(flag);  // ID˺��(���) ���ϻ��ֻ����Բ�
            }
        } else {    // ���¾�ǧ˺��ʻĶȿ���ϳ��˴�Ϣ�λ��ϡ��Ķȳ��ϡ���λ���֤����򤹤�
/* waki 2021.06.04 --------------------------------------------------------> */
            // �Ķȳ��ϻ��֥��å�
            document.getElementById("id_shh")[def_eh].selected = true;
            if( def_eh == 16 ) {
                document.getElementById("id_smm")[3].selected = true;
            } else if( def_eh == 17 ) {
                document.getElementById("id_smm")[6].selected = true;
            } else {
                document.getElementById("id_smm")[0].selected = true;
            }
            StartTimeCopy();
/**
            // �ĶȽ�λ���֥��å�
            document.getElementById("id_ehh")[def_eh].selected = true;
            if( def_eh == 16 ) {
                document.getElementById("id_emm")[3].selected = true;
            } else if( def_eh == 17 ) {
                document.getElementById("id_emm")[6].selected = true;
            } else {
                document.getElementById("id_emm")[0].selected = true;
            }
            EndTimeCopy();
/* <------------------------------------------------------------------------ */
        }
    }

    telno();
}

// ����¾�ν���
function sonota( flag )
{
    NameTextReset("syousai_sonota", flag);
}

// �饸���ܥ���������ν���
function syousai()
{
    var kyu = iki = tok = hur = son = true;
    var ren = false;
    var r1 = document.getElementsByName("r1");

    // ���ϡ���λ���֥��ꥢ����Ѳ�ǽ�ˤ���
    setStartTimeDisable(false);
    setEndTimeDisable(false);
    setDisable( 'id_time_area', false );
    setDisable( 'id_time_sum_area', false );

    for( var i=0; i<r1.length; i++ ) {
        if( r1[i].checked ) {
            document.getElementById('id_content_no').value = i;
            AfterReport();

            if( i>=0 && i<6 ) { // �ٲ˷�
                kyu = false;
                break;
            }
            if( i>=6 && i<11 ) { // �����
                iki = false;
                break;
            }
            if( i == 11 ) { // ���̵ٲ�
                tok = false;
                break;
            }
            if( i == 12 ) { // ���ص���
                hur = false;
                break;
            }
            if( i>=14 && i<18 ) { // ID�̤������¾�ǧ˺���
                ren = true;
                break;
            }
            if( i == 18 ) { // ����¾
                son = false;
                break;
            }
        }
    }

    kyuuka(kyu, i);
    ikisaki(iki, i);
    tokubetu(tok);
    hurikae(hur);
    renraku(ren, i);
    sonota(son);
}

// TEL�ν���
function telno()
{
    var r6 = document.getElementsByName("r6");
    if( r6[(r6.length-2)].checked || r6[(r6.length-1)].checked ){
        setDisableStyle('id_tel_no', false);
        NameTextReset("tel_no", false);
    } else {
        setDisableStyle('id_tel_no', true);
        NameTextReset("tel_no", true);
    }

    if( r6[(r6.length-1)].checked ){
        NameTextReset("tel_sonota", false);
    } else {
        NameTextReset("tel_sonota", true);
    }
}

// ���żԤν���
function jyudensya( ischeck )
{
    if( ischeck == false ) {
        document.getElementsByName("jyu_date")[0].value = "";
    } else {
        JyuDateCopy();
    }

    setDisableStyle('id_jyuden', !ischeck);
    NameTextReset("outai", !ischeck);
    NameDdlistDisabl("ddlist_jyu", !ischeck);
}


// �Ұ��ֹ����ϥ����å�
function check(){
    var str1=document.sinseisya.syainbangou.value;

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

// ���ߤ����ռ���
function sinseibi()
{
    var hiduke=new Date(); 

    var year = hiduke.getFullYear();
    var month = hiduke.getMonth()+1;
    var week = hiduke.getDay();
    var day = hiduke.getDate();

    var yobi= new Array("��","��","��","��","��","��","��");

    document.write(year+"ǯ"+month+"��"+day+"�� "+yobi[week]+"����");

    var hour = hiduke.getHours();
    var minute = hiduke.getMinutes();
    var second = hiduke.getSeconds();

/*
    document.write("    "+hour+"��"+minute+"ʬ"+second+"��");
    document.write("    "+hour+"��"+minute+"ʬ");
*/
}

// ����name�Υ饸���ܥ���˥����å������뤫
function IsRadioSelect(name)
{
    var obj = document.getElementsByName(name);

    if( !obj ) return;

    for( var i=0; i<obj.length; i++ ) {
        if( obj[i].checked ) {
            return true;
        }
    }
    return false;
}

// ���ż���λ��Ѳ�������
function AfterReport()
{
    if(  document.getElementById('id_jyear') == null ) {
        return; // ���ż�������Ǥʤ��ʤ�꥿���󤹤롣
    }

    var no = document.getElementById('id_content_no').value;
/**/
    if( no>13 && no<18 ) {  // ID�̤������¾�ǧ˺���
        jyudensya(false);   // ���ż���������
        return;
    }
/**/
    var sin = new Date(document.getElementById("sin_year").value, document.getElementById("sin_month").value-1, document.getElementById("sin_day").value, document.getElementById("sin_hour").value, document.getElementById("sin_minute").value);
    var s_d = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_shh").value, document.getElementById("id_smm").value);
    var e_d = new Date(document.getElementById("id_eyear").value, document.getElementById("id_emonth").value-1, document.getElementById("id_eday").value, document.getElementById("id_ehh").value, document.getElementById("id_emm").value);

//alert( '��������' + sin.toLocaleDateString() + ' ' + sin.toLocaleTimeString() + '\n��    �֡�' + e_d.toLocaleDateString() + ' ' + e_d.toLocaleTimeString() +  "\n\n�������" );
    if( sin >= s_d && sin >= e_d ) {    // 2021.09.27
//        alert( '��������' + sin.toLocaleDateString() + ' ' + sin.toLocaleTimeString() + '\n��    �֡�' + s_d.toLocaleDateString() + ' ' + s_d.toLocaleTimeString() +  "\n\n�������" );
        jyudensya(true);
        return;
    }

    jyudensya(false);
    return;
}

// AMȾ��ͭ��ٲ� + 12:45���λ���ñ��ͭ��ٲˤο������������å���
function IsAMandTimeVacation()
{
    var obj = document.getElementsByName('r1');

    var indx = document.getElementsByName('indx')[0].value;
    var rows = document.getElementsByName('rows')[0].value;
    var res = new Array();

    var str = document.getElementsByName("str_date")[0].value;
    var checkday = str.substr(0, 4) + '-' + str.substr(4, 2) + '-' + str.substr(6, 2)
    var am_flag = time_flag = true;

    // ������������� ����[0] ������[1] ��λ��[2]
    for( var r=0; r<rows; r++ ) {
        var posname = "res-" + r + "[]";
        var res = document.getElementsByName(posname);

        for( var i=1; i<indx; i++ ) {
            if( checkday == res[i].value) {
                if( res[0].value == 'AMȾ��ͭ��ٲ�' ) {
                    // alert(checkday + " �ϡ�����AMȾ��ͭ��ٲˤ������Ƥ��롣");
                    time_flag = false;  // 12��45�� �λ���ñ��ͭ��ٲ� �����Բ�
                } else if( res[0].value == '����ñ��ͭ��ٲ�' ) {
                    // alert(checkday + " �ϡ�����12��45�� �λ���ñ��ͭ��ٲˤ������Ƥ��롣");
                    am_flag = false;    // AMȾ��ͭ��ٲ� �����Բ�
                }
            }
            if( res[i].value == res[i+1].value) break;
        }
    }

    // ���Ƥ� AMȾ��ͭ��ٲ� or ����ñ��ͭ��ٲ� ����������˿�������Ƥʤ��������å�
    for( var i=0; i<obj.length; i++ ) {
        if( !obj[i].checked ) continue;

        if( obj[i].value == 'AMȾ��ͭ��ٲ�' && !am_flag) {
            alert("���ꤵ�줿���ϡ�����12:45�� �λ���ñ��ͭ��ٲˤ���������Ƥ��ޤ���\n\nAMȾ��ͭ��ٲ� �� 12:45�� �λ���ñ��ͭ��ٲˤϼ����Ǥ��ޤ���\n\n���ο�����Ԥ��ˤϡ�AM��ޤ����Ƥ����ñ��ͭ��ٲˤǿ�����Ԥ���\n���˿������Ƥ���AMȾ��ͭ��ٲˤμ�ä���ꤷ�Ʋ�������");
            return false;
        } else if( obj[i].value == '����ñ��ͭ��ٲ�' ) {
            if( document.getElementsByName("str_time")[0].value == '12:45' && !time_flag ) {
                alert("���ꤵ�줿���ϡ�����AMȾ��ͭ��ٲˤ���������Ƥ��ޤ���\n\nAMȾ��ͭ��ٲ� �� 12:45�� �λ���ñ��ͭ��ٲˤϼ����Ǥ��ޤ���\n\n���ο�����Ԥ��ˤϡ�AM��ޤ����Ƥ����ñ��ͭ��ٲˤǿ�����Ԥ���\n���˿������Ƥ���AMȾ��ͭ��ٲˤμ�ä���ꤷ�Ʋ�������");
                return false;
            }
        }
        break;
    }

    return true;
}

function SpecialText(obj)
{
    if( obj.value.match(/��/) ) {
        alert("�Ķ���¸ʸ�� �� �����Ѥ���Ƥ��ޤ���\n\nȾ�ѥ��å� �� �� �� Ⱦ�ѥ��å� �� (��)\n\n���ѥ��å� �� �� �� ���ѥ��å� �� �ʳ���\n\n��ľ���Ƥ���������");
        obj.focus();
        obj.select();
    }
    return obj.value;
}

// ���ֹ��ܤΥ����å�
function PeriodCheck()
{
    var sd = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value);
    var ed = new Date(document.getElementById("id_eyear").value, document.getElementById("id_emonth").value-1, document.getElementById("id_eday").value);
    if( sd > ed ) {
        alert(sd.toLocaleDateString() + '��' + ed.toLocaleDateString() + "\n\n���ꤵ�줿���֤˸�꤬����ޤ���");
        return false;
    }

    var sd = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_shh").value, document.getElementById("id_smm").value);
    var ed = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_ehh").value, document.getElementById("id_emm").value);
    if( sd > ed ) {
        alert(sd.getHours() + '��' + sd.getMinutes() + 'ʬ' + '��' + ed.getHours() + '��' + ed.getMinutes() + 'ʬ' + "\n\n���ꤵ�줿����˸�꤬����ޤ���");
        return false;
    }

    return true;
}

// ���͹��ܤΥ����å�
function BikouCheck()
{
    if( document.getElementsByName("r2")[7].checked ) {// �������
        if( !document.getElementsByName('bikoutext')[0].value.match(/\S/g) ) {
            alert("���� �����Ϥ���Ƥ��ޤ���\n\n��������򵷤Τ��ᡢͧ�����뻲��Τ��ᡢ�ʤ�");
            return false;
        }
    }

    return true;
}

// Ϣ������ܤΥ����å�
function ContactCheck()
{
    var r6 = document.getElementsByName("r6");
    for( var i=0; i<r6.length; i++ ) {
        if( r6[i].checked ) {
            switch (i) {
                case  0:
                case  1:
                    break;
                case  3:
                    if( !document.getElementsByName('tel_sonota')[0].value.match(/\S/g) ) {
                        alert("Ϣ���� ����¾ �����Ϥ���Ƥ��ޤ���");
                        return false;
                    }
                case  2:
                    if( !document.getElementsByName('tel_no')[0].value.match(/\S/g) ) {
                        alert("Ϣ���� TEL �����Ϥ���Ƥ��ޤ���");
                        return false;
                    }
                    break;
                default:
                    break;
            }
            break;
        }
    }

    if( i == r6.length ) {
        alert("Ϣ���褬���򤵤�Ƥ��ޤ���");
        return false;
    }

    return true;
}

// ���żԹ��ܤΥ����å�
function JyudensyaCheck()
{
    if(  document.getElementById('id_jyear') == null ) {
        return true; // ���ż�������Ǥʤ��ʤ�꥿���󤹤롣
    }

    if( document.getElementsByName("outai")[0].disabled ) return true;
/*
    if( document.getElementsByName("outai")[0].value == '' ) {
        alert("���мԤ����Ϥ���Ƥ��ޤ���");
        return false;
    }
*/
    return true;
}

// ���֤γ�ǧ�ʳ���or��λ���郎������or�ٷƻ��֤ˤʤäƤ��ʤ�����
function TimeCheck( t_str, t_end )
{
    var t1200 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 12, 00);
    var t1245 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 12, 45);
    var t1500 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 15, 00);
    var t1510 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 15, 10);
//alert(t_str.toLocaleTimeString() + ' ***** ' + t_end.toLocaleTimeString());

    if( t1200 <= t_str && t1245 > t_str ) {
        return '���ϻ��郎������ٷƻ���(12:00��12:45)�ˤʤäƤ��ޤ���\n\n�ѹ����Ʋ�������';
    } else if( t1200 < t_end && t1245 >= t_end ) {
        return '��λ���郎������ٷƻ���(12:00��12:45)�ˤʤäƤ��ޤ���\n\n�ѹ����Ʋ�������';
    } else if( t1500 <= t_str && t1510 > t_str ) {
        return '���ϻ��郎���ٷƻ���(15:00��15:10)�ˤʤäƤ��ޤ���\n\n�ѹ����Ʋ�������';
    } else if( t1500 < t_end && t1510 >= t_end ) {
        return '��λ���郎���ٷƻ���(15:00��15:10)�ˤʤäƤ��ޤ���\n\n�ѹ����Ʋ�������';
    }
/**
    if( (t1200 > t_str && t1200 >= t_end) ) {
        alert('������ ���ִط��ʤ�');
    }
    if( (t1245 <= t_str && t1500 >= t_end) ) {
        alert('����塢�ٷ��� ���ִط��ʤ�');
    }
    if( (t1510 <= t_str && t1510 < t_end) ) {
        alert('�ٷƸ� ���ִط��ʤ�');
    }
/**/
    var ttime = new Date();
    ttime.setHours(t_end.getHours() - t_str.getHours());
    ttime.setMinutes(t_end.getMinutes() - t_str.getMinutes());
    ttime.setSeconds(0);

    if( (t1200 > t_str && t1245 <= t_end) ) {
//        alert('���� ���� �ޤ�����Ĵ��ɬ��');
        ttime.setMinutes(ttime.getMinutes() - t1245.getMinutes());
    }
    if( (t1500 > t_str && t1510 <= t_end) ) {
//        alert('�ٷ� ���� �ޤ�����Ĵ��ɬ��');
        ttime.setMinutes(ttime.getMinutes() - t1510.getMinutes());
    }
    var msg ="";
    if( ttime.getHours() <= 0 || ttime.getMinutes() != 0 ) {
        msg = '����ñ�̤ˤʤäƤ��ޤ���( ' + ttime.getHours() + '����' + ('0'+ttime.getMinutes()).slice(-2)  + 'ʬ )\n\n';
        msg += '�ʲ��λ��֤Ͻ�������ޤ���\n 12:00��12:45 ����ٷƻ���(45ʬ)\n 15:00��15:10 �ٷƻ���(10ʬ) \n\n���١�������ǧ���Ʋ�������';
    }
//alert('���ѻ��֡�' +  ttime.toLocaleTimeString());
    return msg;
}

// ������ �����ܤκǽ������å�
function allcheck()
{
    if( !PeriodCheck() ) return false;

    var r1 = document.getElementsByName("r1");
    var flag = false;
    var msg = "";
/* �����������å� */
    for( var i=0; i<r1.length; i++ ) {
        if( r1[i].checked ) {
            switch (i) {
                case  0: // ͭ��ٲ�
/**/
//                    if( i == 0 && (document.getElementsByName("str_time")[0].value == '08:30' || document.getElementsByName("str_time")[0].value == '09:15')
//                               && (document.getElementsByName("end_time")[0].value == '16:15' || document.getElementsByName("end_time")[0].value == '17:15' || document.getElementsByName("end_time")[0].value == '18:00')) {
                    if( i == 0 &&
                         ( (document.getElementsByName("str_time")[0].value == '08:30' && document.getElementsByName("end_time")[0].value == '16:15')
                        || (document.getElementsByName("str_time")[0].value == '08:30' && document.getElementsByName("end_time")[0].value == '17:15')
                        || (document.getElementsByName("str_time")[0].value == '09:15' && document.getElementsByName("end_time")[0].value == '18:00') )
                    ) {
                       ;
                    } else {
                        msg = 'ͭ��ٲˤ����򤷤Ƥ��ޤ���\n\n���� ' + document.getElementsByName("str_time")[0].value + ' �� ' + document.getElementsByName("end_time")[0].value + ' ��\n\n����������ޤ���!!';
                        break;
                    }
/**/
                case  1: // AMȾ��ͭ��ٲ�
                    if( i == 1 && document.getElementsByName("end_time")[0].value != '12:00' ) {
                        msg = 'AMȾ��ͭ��ٲ� �����򤷤Ƥ���Τǡ�\n\n��λ����� 12��00 ���ѹ����Ʋ�������';
                        break;
                    }
                case  2: // PMȾ��ͭ��ٲ�
                    if( i == 2 && document.getElementsByName("str_time")[0].value != '12:45' ) {
                        msg = 'PMȾ��ͭ��ٲ� �����򤷤Ƥ���Τǡ�\n\n���ϻ���� 12��45 ���ѹ����Ʋ�������';
                        break;
                    }
                case  3: // ����ñ��ͭ��ٲ�
                    if( i == 3 ) {
                        var t_str = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_shh").value, document.getElementById("id_smm").value);
                        var t_end = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_ehh").value, document.getElementById("id_emm").value);
                        msg = TimeCheck( t_str, t_end );
                        if( msg ) break;
                    }
                case  4: // ���
                case  5: // �ٹ�����
//                    if( IsHoliday(document.getElementsByName("str_date")[0].value) || IsHoliday(document.getElementsByName("end_date")[0].value) ) {
//                        msg = "���ꤵ�줿���֡ʳ����� or ��λ���ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʳ����� or ��λ���� or ���� ���ѹ����Ʋ�������";
                    if( IsHoliday(document.getElementsByName("str_date")[0].value) ) {
                        msg = "���ꤵ�줿���֡ʳ������ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʳ������� or ���� ���ǧ���Ʋ�������";
                        if( document.getElementsByName("str_date")[0].value != document.getElementsByName("end_date")[0].value && IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                            msg = "���ꤵ�줿���֡ʳ����� or ��λ���ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʳ����� or ��λ���� or ���� ���ǧ���Ʋ�������";
                        }
                    } else if( IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                        msg = "���ꤵ�줿���֡ʽ�λ���ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʽ�λ���� or ���� ���ǧ���Ʋ�������";
                    } else {
                        flag = IsRadioSelect("r2");
                        msg = r1[i].value + " ����ͳ�����򤵤�Ƥ��ޤ���";
                    }
                    break;
                case  6: // ��ĥ���������
                case  7: // ��ĥ�ʽ����
                case  8: // ľ��
                case  9: // ľ��
                case 10: // ľ��/ľ��
                    if( !document.getElementsByName('ikisaki')[0].value.match(/\S/g) ) {
                        msg = "���褬���Ϥ���Ƥ��ޤ���";
                        break;
                    }
                    if( document.getElementsByName('ikisaki')[0].value.match(/��/) ) {
                        msg = "�Ķ���¸ʸ�� �� �����Ѥ���Ƥ��ޤ���\n\nȾ�ѥ��å� �� �� �� Ⱦ�ѥ��å� �� (��)\n\n���ѥ��å� �� �� �� ���ѥ��å� �� �ʳ���\n\n��ľ���Ƥ���������";
                        break;
                    }
                    if( !document.getElementsByName('todouhuken')[0].value.match(/\S/g) ) {
                        msg = "��ƻ�ܸ������Ϥ���Ƥ��ޤ���";
                        break;
                    }
                    if( !document.getElementsByName('mokuteki')[0].value.match(/\S/g) ) {
                        msg = "��Ū�����Ϥ���Ƥ��ޤ���";
                        break;
                    }
                    var r3 = document.getElementsByName("r3");
//                    if( !r3[0].checked ) {
                    if( !r3[0].checked && r3[0].value == "����" ) {
                        if( !document.getElementsByName('setto1')[0].value.match(/\S/g) ) {
                            msg = "��ַ���ɬ�ץ��åȿ������Ϥ���Ƥ��ޤ���";
                            break;
                        }
                    }
                    var r4 = document.getElementsByName("r4");
//                    if( !r4[0].checked ) {
                    if( !r4[0].checked && r4[0].value == "����" ) {
                        if( !document.getElementsByName('setto2')[0].value.match(/\S/g) ) {
                            msg = "�������õޤ�ɬ�ץ��åȿ������Ϥ���Ƥ��ޤ���";
                            break;
                        }
                    }
                    flag = true;
                    break;
                case 11: // ���̵ٲ�
                    if( IsHoliday(document.getElementsByName("str_date")[0].value) ) {
                        msg = "���ꤵ�줿���֡ʳ������ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʳ������� or ���� ���ǧ���Ʋ�������";
                        if( document.getElementsByName("str_date")[0].value != document.getElementsByName("end_date")[0].value && IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                            msg = "���ꤵ�줿���֡ʳ����� or ��λ���ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʳ����� or ��λ���� or ���� ���ǧ���Ʋ�������";
                        }
                    } else if( IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                        msg = "���ꤵ�줿���֡ʽ�λ���ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʽ�λ���� or ���� ���ǧ���Ʋ�������";
                    } else {
                        flag = IsRadioSelect("r5");
                        msg = r1[i].value + " ����ͳ�����Ϥ���Ƥ��ޤ���";
                        var r5 = document.getElementsByName("r5");
                        if( r5[r5.length-1].checked ) {
                            if( !document.getElementsByName('tokubetu_sonota')[0].value.match(/\S/g) ) {
                                flag = false;
                                msg = r1[i].value + ' ' + r5[r5.length-1].value + " ����ͳ�����Ϥ���Ƥ��ޤ���";
                            }
                        }
                    }
                    break;
                case 12: // ���ص���
                    if( IsHoliday(document.getElementsByName("str_date")[0].value) ) {
                        msg = "���ꤵ�줿���֡ʳ������ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʳ������� or ���� ���ǧ���Ʋ�������";
                        if( document.getElementsByName("str_date")[0].value != document.getElementsByName("end_date")[0].value && IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                            msg = "���ꤵ�줿���֡ʳ����� or ��λ���ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʳ����� or ��λ���� or ���� ���ǧ���Ʋ�������";
                        }
                    } else if( IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                        msg = "���ꤵ�줿���֡ʽ�λ���ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʽ�λ���� or ���� ���ǧ���Ʋ�������";
                    } else {
                        if( document.getElementsByName('hurikae')[0].value.match(/\S/g) ) {
                            flag = true;
                        } else {
                            msg = r1[i].value + " ���Ľж�ʬ�����Ϥ���Ƥ��ޤ���";
                        }
                    }
                    break;
                case 13: // �����ٲ�
                    if( IsHoliday(document.getElementsByName("str_date")[0].value) ) {
                        msg = "���ꤵ�줿���֡ʳ������ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʳ������� or ���� ���ǧ���Ʋ�������";
                        if( document.getElementsByName("str_date")[0].value != document.getElementsByName("end_date")[0].value && IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                            msg = "���ꤵ�줿���֡ʳ����� or ��λ���ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʳ����� or ��λ���� or ���� ���ǧ���Ʋ�������";
                        }
                    } else if( IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                        msg = "���ꤵ�줿���֡ʽ�λ���ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ��" +r1[i].value+"�פϿ����Ǥ��ޤ���\n\n���֡ʽ�λ���� or ���� ���ǧ���Ʋ�������";
                    } else {
                        flag = true;
                    }
                    break;
                case 18: // ����¾
                    if( document.getElementsByName('syousai_sonota')[0].value.match(/\S/g) ) {
                        flag = true;
                    } else {
                        msg = r1[i].value + " ����ͳ�����Ϥ���Ƥ��ޤ���";
                    }
                    break;
                default: // ����ʳ���ID������˺�졦���¾�ǧ˺�� etc��
                    flag = true;
                    break;
            }
            break;
        }
    }
    if( i == r1.length ) {
        msg = "���Ƥ����򤵤�Ƥ��ޤ���\n\n��������塢�������Ʋ�������";
    }
    if( !flag ) {
        alert(msg);
        return false;
    }

    if( i>=0 && i<3 ) {
        var s_t_d = ('0'+def_sh).slice(-2) + ':' + def_sm;
        var e_t_d = def_eh + ':' + ('0'+def_em).slice(-2);
        var s_t = document.getElementsByName("str_time")[0].value;
        var e_t = document.getElementsByName("end_time")[0].value;
        
        if( i == 0 ) {
            if( s_t_d != s_t || e_t_d != e_t ) {
                msg = 'ͭ��ٲˤ����򤷤Ƥ��ޤ��������֤�\n\n' + s_t_d + ' �� ' + e_t_d + '\n\n������������\n\n' + s_t + ' �� ' + e_t + '\n\n���ѹ�����Ƥ��ޤ���������Ǥ�����';
                if( ! confirm(msg) ) return false;
            }
        }
        if( i == 1 ) {
            if( s_t_d != s_t ) {
                msg = 'AMȾ��ͭ��ٲˤ����򤷤Ƥ��ޤ�����\n\n���ϻ��֤� ' + s_t_d + ' ���� ' + s_t + ' ���ѹ�����Ƥ��ޤ���\n\nAMȾ��ͭ��ٲˤ����ꤢ��ޤ��󤫡�';
                if( ! confirm(msg) ) return false;
            }
        }
        if( i == 2 ) {
            if( e_t_d != e_t ) {
                msg = 'PMȾ��ͭ��ٲˤ����򤷤Ƥ��ޤ�����\n\n��λ���֤� ' + e_t_d + ' ���� ' + e_t + ' ���ѹ�����Ƥ��ޤ���\n\nPMȾ��ͭ��ٲˤ����ꤢ��ޤ��󤫡�';
                if( ! confirm(msg) ) return false;
            }
        }
    }

    if( i>=0 && i<6 ) {  // ͭ��ٲˡ��ٹ����� �� ������� ����������ͤ�����å�
        if( !BikouCheck() ) return false;
    }

    if( i<=13 || i>17 ) {  // ID�̤������¾�ǧ˺�� �ʳ���Ϣ���������å�
        if( !ContactCheck() ) return false;
    } else {
        if( i == 14 ) {         // ID�������̤�˺��ʽжС�
            if( !IsWorkStartTime() ) return false;
        } else if( i == 15 ) {  // ID�������̤�˺�����С�
            if( !IsWorkEndTime() ) return false;
        } else {
            if( !IsOverTime() ) return false;
        }
    }

    if( !JyudensyaCheck() ) return false;

    var obj = document.getElementsByName("sougou_update");
    if( obj[0] && obj[0].value ) {
        return confirm("�������Ƥ������Ǥ�����\n�����塢�����᤹���ȤϤǤ��ޤ���");
    }

    return true;
}

// ����ñ�̤η׻�
function TimeCalculation()
{
    if( !document.getElementsByName('sum_hour')[0].value.match(/\S/g) ) {
        alert("�׻���ɬ�פ� ���� �����Ϥ���Ƥ��ޤ���");
        return;
    }

    var base_s_time = document.getElementsByName("r0")[0].checked;
    var base_e_time = document.getElementsByName("r0")[1].checked;

    // ���� or ��λ������ѹ����֤���������ѹ���λ������롣
    var change_hour = befor_hour = after_hour = 0;
    if( base_s_time ) {
        change_hour = parseInt(document.getElementsByName("sum_hour")[0].value, 10);
        befor_hour = parseInt(document.getElementById("id_shh").value, 10);
        after_hour = befor_hour + change_hour;
        var t_str = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_shh").value, document.getElementById("id_smm").value);
        var t_end = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, after_hour, document.getElementById("id_smm").value);
    } else if ( base_e_time ) {
        change_hour = parseInt(document.getElementsByName("sum_hour")[0].value, 10);
        befor_hour = parseInt(document.getElementById("id_ehh").value, 10);
        after_hour = befor_hour - change_hour;
        var t_str = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, after_hour, document.getElementById("id_emm").value);
        var t_end = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_ehh").value, document.getElementById("id_emm").value);
    } else {
        alert("���� or ��λ �ɤ�������򤵤�Ƥ��ޤ���");
        return;
    }

    var t1200 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 12, 00);
    var t1245 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 12, 45);
    var t1500 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 15, 00);
    var t1510 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 15, 10);

    var lunch_time = break_time = false;

    if( t1200 <= t_str && t1245 > t_str ) {
        lunch_time = true;  // ���ϻ��郎������ٷƻ���(12:00��12:45)
    } else if( t1200 < t_end && t1245 >= t_end ) {
        lunch_time = true;  // ��λ���郎������ٷƻ���(12:00��12:45)
    } else if( t1500 <= t_str && t1510 > t_str ) {
        break_time = true;  // ���ϻ��郎���ٷƻ���(15:00��15:10)
    } else if( t1500 < t_end && t1510 >= t_end ) {
        break_time = true; // ��λ���郎���ٷƻ���(15:00��15:10)
    }

    if( (t1200 > t_str && t1245 <= t_end) ) {
        lunch_time = true;  // ���� ���� �ޤ�����Ĵ��ɬ��
    }
    // �ѹ���λ��郎����˴ط������硢45ʬ�Ť餹��
    if( lunch_time ) {
        if( base_s_time ) {
            t_end.setMinutes(t_end.getMinutes() + t1245.getMinutes());
/**
            if( t_str.getHours() == 12 ) {
                t_end.setMinutes(t_end.getMinutes() - t1245.getMinutes());
            }
/**/
        } else {
            t_str.setMinutes(t_str.getMinutes() - t1245.getMinutes());
/**
            if( t_end.getHours() == 12 ) {
                t_str.setMinutes(t_str.getMinutes() + t1245.getMinutes());
            }
/**/
        }
    }

    if( t1200 <= t_str && t1245 > t_str ) {
        lunch_time = true;  // ���ϻ��郎������ٷƻ���(12:00��12:45)
    } else if( t1200 < t_end && t1245 >= t_end ) {
        lunch_time = true;  // ��λ���郎������ٷƻ���(12:00��12:45)
    } else if( t1500 <= t_str && t1510 > t_str ) {
        break_time = true;  // ���ϻ��郎���ٷƻ���(15:00��15:10)
    } else if( t1500 < t_end && t1510 >= t_end ) {
        break_time = true; // ��λ���郎���ٷƻ���(15:00��15:10)
    }

    if( (t1500 > t_str && t1510 <= t_end) ) {
        break_time = true;  // �ٷ� ���� �ޤ�����Ĵ��ɬ��
    }
    // �ѹ���λ��郎�ٷƻ��֤˴ط������硢10ʬ�Ť餹��
    if( break_time ) {
        if( base_s_time ) {
            t_end.setMinutes(t_end.getMinutes() + t1510.getMinutes());
/**
            if( t_str.getHours() == 15 ) {
                t_end.setMinutes(t_end.getMinutes() - t1510.getMinutes());
            }
/**/
        } else {
//alert('TEST �ѹ�����' +  t_str.getHours() + '��' +  t_str.getMinutes());
            t_str.setMinutes(t_str.getMinutes() - t1510.getMinutes());
/**
            if( t_end.getHours() == 15 ) {
                t_str.setMinutes(t_str.getMinutes() + t1510.getMinutes());
            }
/**/
//alert('TEST �ѹ����' +  t_str.getHours() + '��' +  t_str.getMinutes());
        }
    }

    // �ѹ���λ���򥻥åȤ��롣
    if( base_s_time ) {
        document.getElementById("id_ehh").value = ('0'+t_end.getHours()).slice(-2);
        document.getElementById("id_emm").value = ('0'+t_end.getMinutes()).slice(-2);
        EndTimeCopy();
//alert('���ϻ��֡�' +  t_end.getHours() + '\n\n��λ���֡�' +  t_end.getMinutes());
    } else {
        document.getElementById("id_shh").value = ('0'+t_str.getHours()).slice(-2);
        document.getElementById("id_smm").value = ('0'+t_str.getMinutes()).slice(-2);
        StartTimeCopy();
//alert('���ϻ��֡�' +  t_str.getHours() + '\n\n��λ���֡�' +  t_str.getMinutes());
    }

    // ����κǽ������å��򤹤롣
    msg = TimeCheck( t_str, t_end );
    if( msg ) alert(msg);

    return;
}

// ��ҥ��������ε�������򥻥åȤ��Ƥ�����
var holiday = "";
function SetHoliday(day)
{
    holiday = day;
}

// �����Ǥ�����
function IsHoliday(day)
{
    if( holiday.search((day.substr(0,4)+'-'+day.substr(4,2)+'-'+day.substr(6,2))) != -1 ) {
        return true;
    } else {
        return false;
    }
}

// ������ɽ��
function Youbi(w_date, id)
{
    var hiduke = new Date(w_date.substr(0,4),w_date.substr(4,2)-1,w_date.substr(6,2));
    var week = hiduke.getDay();
    var yobi = new Array(" (��)"," (��)"," (��)"," (��)"," (��)"," (��)"," (��)");
    var obj = document.getElementById(id);
//    obj.innerHTML = yobi[week];
    if( document.getElementById("0").checked && id == 'id_e_youbi') {
        obj.innerHTML = "<span style='color: DarkGray;'>" + yobi[week] + "</span>";
        return;
    }
    if( week == 0 ) {
        obj.innerHTML = "<span style='color: red;'>" + yobi[week] + "</span>";
    } else if( week == 6 ) {
        obj.innerHTML = "<span style='color: blue;'>" + yobi[week] + "</span>";
    } else if( holiday.search((w_date.substr(0,4)+'-'+w_date.substr(4,2)+'-'+w_date.substr(6,2))) != -1 ) {
        obj.innerHTML = "<span style='color: red;'>" + yobi[week] + "</span>";
    } else {
        obj.innerHTML = "<span style='color: black;'>" + yobi[week] + "</span>";
    }
}

var PeriodDays = 1;   // ���������ʽ���͡�1��
// �ٲ������򥻥å�
function SetPeriod()
{
    var sd = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value,0,0,0);
    var ed = new Date(document.getElementById("id_eyear").value, document.getElementById("id_emonth").value-1, document.getElementById("id_eday").value,0,0,0);
    if( (ed - sd ) > 0 ) {
        var difference = ed.getTime()-sd.getTime();
        PeriodDays = difference/(1000 * 3600 * 24)+1;
        for( var i=0; i<PeriodDays; i++ ) {
            if( IsHoliday(sd.getFullYear() + ('00'+(sd.getMonth()+1)).slice(-2) + ('00'+sd.getDate()).slice(-2)) ) {
                PeriodDays--;
            }
            sd.setDate(sd.getDate()+1);
        }
    } else {
        PeriodDays = 1;
    }
//alert(PeriodDays + ' ��');
}

// �������ե��ԡ�
function StartDateCopy()
{
    document.getElementsByName("str_date")[0].value = document.getElementById("id_syear").value + document.getElementById("id_smonth").value + document.getElementById("id_sday").value;

    if( !isDate(document.getElementsByName("str_date")[0].value) ) {
        var dt = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value, 0);
        document.getElementById("id_sday").value = dt.getDate();
        document.getElementsByName("str_date")[0].value = document.getElementById("id_syear").value + document.getElementById("id_smonth").value + document.getElementById("id_sday").value;
    }

    if( document.getElementById("0").checked ) {
        document.getElementById("id_eyear").value = document.getElementById("id_syear").value;
        document.getElementById("id_emonth").value = document.getElementById("id_smonth").value;
        document.getElementById("id_eday").value = document.getElementById("id_sday").value;
        EndDateCopy();
    }
    AfterReport();
    Youbi(document.getElementsByName("str_date")[0].value, 'id_s_youbi');

    SetPeriod();
    Iskeikaku();
}

// ��λ���ե��ԡ�
function EndDateCopy()
{
    document.getElementsByName("end_date")[0].value = document.getElementById("id_eyear").value + document.getElementById("id_emonth").value + document.getElementById("id_eday").value;

    if( !isDate(document.getElementsByName("end_date")[0].value) ) {
        var dt = new Date(document.getElementById("id_eyear").value, document.getElementById("id_emonth").value, 0);
        document.getElementById("id_eday").value = dt.getDate();
        document.getElementsByName("end_date")[0].value = document.getElementById("id_eyear").value + document.getElementById("id_emonth").value + document.getElementById("id_eday").value;
    }
    Youbi(document.getElementsByName("end_date")[0].value, 'id_e_youbi');

    SetPeriod();
    Iskeikaku();
}

// ���ϻ��拾�ԡ�
function StartTimeCopy()
{
    document.getElementsByName("str_time")[0].value = document.getElementById("id_shh").value + ':' + ('0'+document.getElementById("id_smm").value).slice(-2);

    AfterReport();
}

// ��λ���拾�ԡ�
function EndTimeCopy()
{
    document.getElementsByName("end_time")[0].value = document.getElementById("id_ehh").value + ':' + ('0'+document.getElementById("id_emm").value).slice(-2);

    AfterReport();
}

// �����������ԡ�
function JyuDateCopy()
{
    document.getElementsByName("jyu_date")[0].value = document.getElementById("id_jyear").value + '-' + document.getElementById("id_jmonth").value + '-' + document.getElementById("id_jday").value + ' ' + document.getElementById("id_jhh").value + ':' + document.getElementById("id_jmm").value;

    if( !isDate(document.getElementById("id_jyear").value + document.getElementById("id_jmonth").value + document.getElementById("id_jday").value) ) {
        var dt = new Date(document.getElementById("id_jyear").value, document.getElementById("id_jmonth").value, 0);
        document.getElementById("id_jday").value = dt.getDate();
        document.getElementsByName("jyu_date")[0].value = document.getElementById("id_jyear").value + '-' + document.getElementById("id_jmonth").value + '-' + document.getElementById("id_jday").value + ' ' + document.getElementById("id_jhh").value + ':' + document.getElementById("id_jmm").value;
    }
    Youbi(document.getElementById("id_jyear").value + document.getElementById("id_jmonth").value + document.getElementById("id_jday").value, 'id_j_youbi');
}

// ����������
function SinseiDate()
{
    var hiduke=new Date(); 

    var year = hiduke.getFullYear();
    var month =  ('00' + (hiduke.getMonth()+1)).slice( -2 );
    var week = hiduke.getDay();
    var day = ('00' + hiduke.getDate()).slice( -2 );

    var hour = ('00' + hiduke.getHours()).slice( -2 );
    var minute = ('00' + hiduke.getMinutes()).slice( -2 );
    var second = ('00' + hiduke.getSeconds()).slice( -2 );

    document.getElementsByName("sin_date")[0].value = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
    document.getElementsByName("sin_year")[0].value = year;
    document.getElementsByName("sin_month")[0].value = month;
    document.getElementsByName("sin_day")[0].value = day;
    document.getElementsByName("sin_hour")[0].value = hour;
    document.getElementsByName("sin_minute")[0].value = minute;
}

function OneDay( ischecked )
{
    setDisableStyle('id_1000' , ischecked);
    document.getElementById("id_eyear").disabled = ischecked;
    document.getElementById("id_emonth").disabled = ischecked;
    document.getElementById("id_eday").disabled = ischecked;
    StartDateCopy(); EndDateCopy();
}

function checkedday(obj)
{
        var yyymmdd = obj.value;
        if(yyymmdd.substr(6, 2) < 1) yyymmdd = yyymmdd.substr(0, 6) + '01';
        ///// �ǽ���������å����ƥ��åȤ���
        if (!isDate(yyymmdd)) {
            var dt = new Date(yyymmdd.substr(0, 4),  yyymmdd.substr(4, 2), 0);
            yyymmdd = ( yyymmdd.substr(0, 6) + dt.getDate() );
            if (!isDate(yyymmdd)) {
                alert(yyymmdd + '���դλ��꤬�����Ǥ���');
            }

        }
        obj.value = yyymmdd;
}

function isDate (str)
{
    var arr = (str.substr(0, 4) + '/' + str.substr(4, 2) + '/' + str.substr(6, 2)).split('/');

    if (arr.length !== 3) return false;
    var date = new Date(arr[0], arr[1] - 1, arr[2]);

    if (arr[0] !== String(date.getFullYear()) || arr[1] !== ('0' + (date.getMonth() + 1)).slice(-2) || arr[2] !== ('0' + date.getDate()).slice(-2)) {
        return false;
    } else {
        return true;
    }
}

// �ǽ���ǧ�Υե饰�򥻥å�
function SetCheckFlag(str)
{
    if( str == "����" ) {
//alert('����');
        document.getElementsByName("check_flag")[0].value = "ok";
        document.getElementsByName("syainbangou")[0].value = "";    // ������λ�ΰ١����ˤ��롣
    } else {
//alert('���');
        document.getElementsByName("check_flag")[0].value = "replay";
    }
    return true;
}

// �ƿ�����ꡢɽ��������
function ReInit()
{
    OneDay(document.getElementsByName("c0")[0].checked);
    syousai();
    telno();
    SinseiDate(); StartDateCopy(); EndDateCopy(); StartTimeCopy(); EndTimeCopy();
}


// �ǽ���ǧ��ꡢ��ɽ��������
function ReDisp()
{
    OneDay(document.getElementsByName("c0")[0].checked);
    syousai();
    telno();
    StartDateCopy(); EndDateCopy(); StartTimeCopy(); EndTimeCopy();
}

// �ǽ���ǧ����ɽ������ݤΡ���ҵ������ͭ��ٲˤʤɤ򥨥顼�ǤϤ�����
function CheckDisp(sinseiNG)
{
    if( sinseiNG ) {
        alert("���ꤵ�줿���֡ʳ����� or ��λ���ˤ� �����ʲ�ҥ��������ˤǤ���\n���Τ��ᡢ�������ƤǤϿ����Ǥ��ޤ���\n\n���֡ʳ����� or ��λ���� or ���� ���ѹ����Ʋ�������" );
    } else {
        document.getElementsByName("submit")[0].disabled = false;
    }
}

// ��å᡼�������γ�ǧ����
function MailSend()
{
    if( !document.getElementsByName('del_reason')[0].value.match(/\S/g) ) {
        alert("�����ͳ�����Ϥ���Ƥ��ޤ���\n\n���ϸ塢����[����]�ܥ���򥯥�å����Ʋ�������");
        return false;
    }

    return confirm("�����ͳ���������Ƥ������Ǥ�����\n\n�����塢��ưŪ�˥�����ɥ����Ĥ��ޤ���");
}
