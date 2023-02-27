//  ========================================================
//  jkl-calendar.js ---- �|�b�v�A�b�v�J�����_�[�\���N���X
//  Copyright 2005-2006 Kawasaki Yusuke <u-suke [at] kawa.net>
//  Thanks to 2tak <info [at] code-hour.com>
//  http://www.kawa.net/works/js/jkl/calender.html
//  2005/04/06 - �ŏ��̃o�[�W����
//  2005/04/10 - �O���X�^�C���V�[�g���g�p���Ȃ��AJKL.Opacity �̓I�v�V����
//  2006/10/22 - typo�C���Aspliter/min_date/max_date�v���p�e�B�A�~�{�^���ǉ�
//  2006/10/23 - prototype.js���p���́AEvent.observe()�ŃC�x���g�o�^
//  2006/10/24 - max_date �͈̓o�O�C��
//  2006/10/25 - �t�H�[���ɏ����l������΁A�J�����_�[�̏����l�ɍ̗p����
//  2006/11/15 - MOM Update �T�̏��߂̗j����ύX�ł���悤�ɏC��
//  2006/11/23 - MOM Update �������t�̕����F���w��ł���悤�ɏC���A���Ƙg�����`�悵�Ă݂�
//               �ז���<select>�ւ̉��}���u�������Ă݂�
//  2006/11/27 - MOM Update �ז���<select>�ւ̉��}���u���C���A�`��̈�̍������擾����
//  2006/11/30 - MOM Update �I���\�ȗj�����v���p�e�B�ɒǉ��A�������t�ƑI��s�\�ȓ��t�̔w�i�F���X�^�C���ɒǉ�
//               �J�����_�[��z-index���v���p�e�B�ɒǉ�
//  2006/12/04 - ksuzu Update �I���\�����Ȃ����ɂ͈ړ��ł��Ȃ��悤�ɕύX
//               �J�����_�[�̕\�������N���b�N����ƌ��݂̌��Ɉړ��ł���悤�ύX
//               ����{�^���ɂăJ�����_�[������Ƃ��A�J�����_�[�̏����\����߂��悤�ύX
//  2006/12/30 - MOM IFRAME��SRC������dummy.html��}��
//  2007/02/04 - MOM setDateYMD�̃o�O���C��
//               TD�^�O�̃X�^�C���ɔw�i�F���w�肷��悤�C��
//  2007/03/12 - ���B�G�s �j����������ǉ�
//                   �Љ�u���O�Fhttp://www.adachi-shihosyoshi.com/archives/50727775.html
//               �E�p�c �j�� ����j������p�\�[�X���g�p
//                   �z�[���y�[�W�Fhttp://www.h3.dion.ne.jp/~sakatsu/index.htm
//                   �_�E�����[�h�Fhttp://www.h3.dion.ne.jp/~sakatsu/HolidayChk.js               
//               �E�j���Z���̕����F��ύX
//               �E�}�E�X�𓖂Ă�Əj������\��
//  2007/03/25 - ���B�G�s �Œ�\���A�I���\���w��ݒ�@�\��ǉ�
//               �Ejoao ����̃\�[�X�𗘗p�����Ă��������܂���m(__)m
//                   �z�[���y�[�W�Fhttp://www.goigoipro.com/
//  2007/04/03 - ���B�G�s ���ʂȋx���ݒ�@�\������
//  ========================================================

/***********************************************************
//  �i�T���v���j�|�b�v�A�b�v����J�����_�[

  <html>
    <head>
      <script type="text/javascript" src="jkl-calendar_sp1.0.js" charset="Shift_JIS"></script>
      // 2007.03.12 ���B�G�s �p�c����̏j������p�X�N���v�g��ǉ�
      <script type="text/javascript" src="HolidayChk.js" charset="Shift_JIS"></script>
      <script>
        var cal1 = new JKL.Calendar("calid","formid","colname");
      </script>
    </head>
    <body>
      <form id="formid" action="">
        <input type="text" name="colname" onClick="cal1.write();" onChange="cal1.getFormValue(); cal1.hide();"><br>
        <div id="calid"></div>
      </form>
    </body>
  </html>

//  �i�T���v���j�Œ�\��
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p

    <head>
      <script type="text/javascript" src="jkl-calendar_sp1.1.js" charset="Shift_JIS"></script>
      <script type="text/javascript" src="HolidayChk.js" charset="Shift_JIS"></script>
      <script>
        var cal1 = new JKL.Calendar("calid","formid","colname");
      </script>
    </head>
    //  �Œ�\���F<body onload="cal1.write(1);">;
    <body onload="cal1.write(1);">
      <form id="formid" action="">
        <input type="text" name="colname"><br>
        <div id="calid"></div>
      </form>
    </body>
  </html>

 **********************************************************/

// �e�N���X

if ( typeof(JKL) == 'undefined' ) JKL = function() {};

// JKL.Calendar �R���X�g���N�^�̒�`

JKL.Calendar = function ( eid, fid, valname ) {
    this.eid = eid;
    this.formid = fid;
    this.valname = valname;
    this.__dispelem = null;  // �J�����_�[�\�����G�������g
    this.__textelem = null;  // �e�L�X�g���͗��G�������g
    this.__opaciobj = null;  // JKL.Opacity �I�u�W�F�N�g
    this.style = new JKL.Calendar.Style();
    return this;
};

// �o�[�W�����ԍ�

JKL.Calendar.VERSION = "0.13";

// �f�t�H���g�̃v���p�e�B

JKL.Calendar.prototype.spliter = "/";
JKL.Calendar.prototype.date = null;
JKL.Calendar.prototype.min_date = null;
JKL.Calendar.prototype.max_date = null;
JKL.Calendar.prototype.show_cd  = null;

// 2006.11.15 MOM �\���J�n�j�����v���p�e�B�ɒǉ�(�f�t�H���g�͓��j��=0)
JKL.Calendar.prototype.start_day = 0;

// 2006.11.23 MOM �J�����_�[���̓��t��g���ŋ�؂邩�ǂ����̃v���p�e�B(�f�t�H���g��true)
JKL.Calendar.prototype.draw_border = true;

// 2007.04.03 ���B�G�s ���ʂȋx���i�y�E���E�j���������j�ݒ�@�\�������i'����'�A'�N����'�̂����ꂩ�Őݒ�ł��܂����A'�N����'�̏ꍇ�͂��̔N�Ɍ��肳��܂��B�j
//�i�L����j'1/2','�N�n�x��',
//          '1/3','�N�n�x��',
//          '2007/8/15','�n��10���N�L�O��',
//          '12/29','�N���x��',
//          '12/30','�N���x��',
//          '12/31','�N���x��'
//�i�L����̒��Ӂj��1 �x����ݒ肷��ꍇ�͓��t�Ƌx�������K�{
//                ��2 ���t�Ƌx�����͔��p '' �ň͂݁A��؂�ɂ͔��p , ���L��
//                ��3 �Ō�̋x�����̋�؂� , �͕s�v
//                ��4 �L����ɂ��鍶�[�� // �͕s�v
//                ��5 �ݒ肵�Ȃ��ꍇ�͋󔒂̂܂�
//                ��6 �x���̕����̐F�̓f�t�H���g�ł͓��j�E�j���Ɠ��F�@�ύX�̓X�^�C���ց���
JKL.Calendar.prototype.kyuzitsu_days = new Array(
//_/��������_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
   //'1/2','�N�n�x�Ɠ�',
   //'1/3','�N�n�x�Ɠ�',
   //'12/29','�N���x�Ɠ�',
   //'12/30','�N���x�Ɠ�',
   //'12/31','�N���x�Ɠ�'
   // ��17��
    '2016/8/15','�Ċ��x��',
    '2016/8/16','�Ċ��x��',
    '2016/8/17','�Ċ��x��',
    '2016/8/18','�Ċ��x��',
    '2016/8/19','�Ċ��x��',
    '2016/12/29','�N���x��',
    '2016/12/30','�N���x��',
    '2017/1/2','�N�n�x��',
    '2017/1/3','�N�n�x��',
    '2017/1/4','�N�n�x��',
   // ��18��
    '2017/8/14','�Ċ��x��',
    '2017/8/15','�Ċ��x��',
    '2017/8/16','�Ċ��x��',
    '2017/8/17','�Ċ��x��',
    '2017/8/18','�Ċ��x��',
    '2017/12/29','�N���x��',
    '2018/1/2','�N�n�x��',
    '2018/1/3','�N�n�x��',
    '2018/1/4','�N�n�x��'
//_/�����܂łɋL��_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
);

// 2006.11.30 MOM �e�j���̑I���ۂ��v���p�e�B�ɒǉ�(�f�t�H���g�͑S��true)
// �z��̓Y�����ŗj�����w��(0�`6 = ���j�`�y�j)�A�I���ۂ�boolean�l�ő������A�Ƃ����g����
JKL.Calendar.prototype.selectable_days = new Array(true,true,true,true,true,true,true);

// 2007.03.25 ���B�G�s �j���E�x���̑I���ۂ��v���p�e�B�ɒǉ��i�I���\��:true �A�I��s�\��:false�A�f�t�H���g�͑S��true�j
// �j��
JKL.Calendar.prototype.selectable_holiday = true;
// �x��
JKL.Calendar.prototype.selectable_kyuzitsu = true;

// 2006.11.30 MOM �J�����_�[��z-index���v���p�e�B�ɒǉ�
JKL.Calendar.prototype.zindex = 10;

// JKL.Calendar.Style

JKL.Calendar.Style = function() {
    return this;
};

// �f�t�H���g�̃X�^�C��

JKL.Calendar.Style.prototype.frame_width        = "150px";      // �t���[������
JKL.Calendar.Style.prototype.frame_color        = "#006000";    // �t���[���g�̐F
JKL.Calendar.Style.prototype.font_size          = "12px";       // �����T�C�Y
JKL.Calendar.Style.prototype.day_bgcolor        = "#F0F0F0";    // �J�����_�[�̔w�i�F
JKL.Calendar.Style.prototype.month_color        = "#FFFFFF";    // ���N���������̔w�i�F
JKL.Calendar.Style.prototype.month_hover_color  = "#009900";    // �}�E�X�I�[�o�[���́�╶���F
JKL.Calendar.Style.prototype.month_hover_bgcolor= "#FFFFCC";    // �}�E�X�I�[�o�[���́��w�i�F
JKL.Calendar.Style.prototype.weekday_color      = "#404040";    // ���j�`���j���Z���̕����F
JKL.Calendar.Style.prototype.saturday_color     = "#0040D0";    // �y�j���Z���̕����F
JKL.Calendar.Style.prototype.sunday_color       = "#D30000";    // ���j���E�j���Z���̕����F
// 2007.03.25 ���B�G�s �x���Z���̕����F��ǉ�
JKL.Calendar.Style.prototype.kyuzitsu_color     = "#D30000";    // �x���Z���̕����F
JKL.Calendar.Style.prototype.others_color       = "#999999";    // ���̌��̓��Z���̕����F
JKL.Calendar.Style.prototype.day_hover_bgcolor  = "#FFCC33";    // �}�E�X�I�[�o�[���̓��Z���̔w�i
JKL.Calendar.Style.prototype.cursor             = "pointer";    // �}�E�X�I�[�o�[���̃J�[�\���`��

// 2006.11.23 MOM �������t�̕����F���v���p�e�B�ɒǉ�
JKL.Calendar.Style.prototype.today_color        = "#008000";    // �������t�Z���̕����F
// 2006.11.23 MOM �g�������Ă݂�
JKL.Calendar.Style.prototype.today_border_color = "#00A000";    // �������t�Z���̘g���̐F
JKL.Calendar.Style.prototype.others_border_color= "#E0E0E0";    // ���̓��Z���̘g���̐F

// 2006.11.30 MOM �������t�̔w�i�F��Y��Ă��̂Œǉ����Ă݂�
JKL.Calendar.Style.prototype.today_bgcolor      = "#D0FFD0";    // �������t�Z���̔w�i�F
// 2006.11.30 MOM �I��s�\�ȓ��t�̔w�i�F��ǉ�
JKL.Calendar.Style.prototype.unselectable_day_bgcolor = "#CECEDD";    // �I��s�\�ȓ��t�̔w�i�F
// 2007.03.25 ���B�G�s �I��s�\�ȍ������t�Z���̔w�i�F��ǉ�
JKL.Calendar.Style.prototype.unselectable_today_bgcolor      = "#E0E0E0";    // �I��s�\�ȍ������t�Z���̔w�i�F
// 2007.03.25 ���B�G�s �I��s�\���t�Z���̘g���̐F��ǉ�
JKL.Calendar.Style.prototype.unselectable_day_border_color = "#0000CC";// �I��s�\���t�Z���̘g���̐F

//  ���\�b�h

JKL.Calendar.Style.prototype.set = function(key,val) { this[key] = val; }
JKL.Calendar.Style.prototype.get = function(key) { return this[key]; }
JKL.Calendar.prototype.setStyle = function(key,val) { this.style.set(key,val); };
JKL.Calendar.prototype.getStyle = function(key) { return this.style.get(key); };

// ���t������������

JKL.Calendar.prototype.initDate = function ( dd ) {
    if ( ! dd ) dd = new Date();
    var year = dd.getFullYear();
    var mon  = dd.getMonth();
    var date = dd.getDate();
    this.date = new Date( year, mon, date );
    this.getFormValue();
    return this.date;
}

// �����x�ݒ�̃I�u�W�F�N�g��Ԃ�

JKL.Calendar.prototype.getOpacityObject = function () {
    if ( this.__opaciobj ) return this.__opaciobj;
    var cal = this.getCalendarElement();
    if ( ! JKL.Opacity ) return;
    this.__opaciobj = new JKL.Opacity( cal );
    return this.__opaciobj;
};

// �J�����_�[�\�����̃G�������g��Ԃ�

JKL.Calendar.prototype.getCalendarElement = function () {
    if ( this.__dispelem ) return this.__dispelem;
    this.__dispelem = document.getElementById( this.eid )
    return this.__dispelem;
};

// �e�L�X�g���͗��̃G�������g��Ԃ�

JKL.Calendar.prototype.getFormElement = function () {
    if ( this.__textelem ) return this.__textelem;
    var frmelms = document.getElementById( this.formid );
    if ( ! frmelms ) return;
    for( var i=0; i < frmelms.elements.length; i++ ) {
        if ( frmelms.elements[i].name == this.valname ) {
            this.__textelem = frmelms.elements[i];
        }
    }
    return this.__textelem;
};

// �I�u�W�F�N�g�ɓ��t���L������iYYYY/MM/DD�`���Ŏw�肷��j

JKL.Calendar.prototype.setDateYMD = function (ymd) {
    var form_yy  = "" + ymd.substr(0,4);
    var form_mm  = "" + ymd.substr(4,2);
    var form_dd  = "" + ymd.substr(6,2);
    var form_ymd = form_yy + this.spliter + form_mm + this.spliter + form_dd; // ��ʕ\�����A���ɓ��t���Z�b�g����Ă���ꍇ �����\���������ɂȂ��Ă��܂��o�O���C��(YYYY/MM/DD��YYYYMMDD�ɕύX�����ה���)
    var splt    = form_ymd.split( this.spliter );
    //var splt = ymd.split( this.spliter );
    if ( splt[0]-0 > 0 &&
         splt[1]-0 >= 1 && splt[1]-0 <= 12 &&       // bug fix 2006/03/03 thanks to ucb
         splt[2]-0 >= 1 && splt[2]-0 <= 31 ) {
        if ( ! this.date ) this.initDate();
/* 2007.02.04 MOM ��ʕ\�����A���ɓ��t���Z�b�g����Ă���ꍇ�ɔ�������o�O���C��
            this.date.setFullYear( splt[0] );
            this.date.setMonth( splt[1]-1 );
            this.date.setDate( splt[2] );
*/
            this.date.setDate( splt[2] );
            this.date.setMonth( splt[1]-1 );
            this.date.setFullYear( splt[0] );
    } else {
        ymd = "";
    }
    return ymd;
};

// �I�u�W�F�N�g������t�����o���iYYYY/MM/DD�`���ŕԂ�j
// ������ Date �I�u�W�F�N�g�̎w�肪����΁A
// �I�u�W�F�N�g�͖������āA�����̓��t���g�p����i�P�Ȃ�fprint�@�\�j

JKL.Calendar.prototype.getDateYMD = function ( dd ) {
    if ( ! dd ) {
        if ( ! this.date ) this.initDate();
        dd = this.date;
    }
    var mm = "" + (dd.getMonth()+1);
    var aa = "" + dd.getDate();
    if ( mm.length == 1 ) mm = "" + "0" + mm;
    if ( aa.length == 1 ) aa = "" + "0" + aa;
    return dd.getFullYear() + this.spliter + mm + this.spliter + aa;
};

// 2007.04.03 ���B�G�s �x������֐���V���ɐ݂���i�v���p�e�B�Őݒ肵���x������Ԃ��j

JKL.Calendar.prototype.getKyuzitsu = function ( prmDate ){
    MyDate = new Date(prmDate);
    MyYear = MyDate.getFullYear();
    MyMonth = MyDate.getMonth() + 1;    // MyMonth:1�`12
    MyDay = MyDate.getDate();
    MyYMD = MyYear + '/' + MyMonth + '/' + MyDay;
    Result = "";
    var kyuzitsuLastCnt = this.kyuzitsu_days.length / 2;

    for(var i=0; i<kyuzitsuLastCnt; i++){
        if (this.kyuzitsu_days[i*2].length>5){
            var test = this.kyuzitsu_days[i*2];
        }else {
            test = MyYear + '/' + this.kyuzitsu_days[i*2];
        }
        if (MyYMD == test){Result = this.kyuzitsu_days[i*2+1]}
    }
    return Result;
};

// �e�L�X�g���͗��̒l��Ԃ��i���łɃI�u�W�F�N�g���X�V����j

JKL.Calendar.prototype.getFormValue = function () {
    var form1 = this.getFormElement();
    if ( ! form1 ) return "";
    var date1 = this.setDateYMD( form1.value );
    return date1;
};

// �t�H�[�����͗��Ɏw�肵���l����������

JKL.Calendar.prototype.setFormValue = function (ymd) {
    if ( ! ymd ) ymd = this.getDateYMD();   // ���w�莞�̓I�u�W�F�N�g����H
    var form1 = this.getFormElement();
    var ret_yy  = "" + ymd.substr(0,4);
    var ret_mm  = "" + ymd.substr(5,2);
    var ret_dd  = "" + ymd.substr(8,2);
    var ret_ymd = ret_yy + ret_mm + ret_dd;
    if ( form1 ) form1.value = ret_ymd;
    //if ( form1 ) form1.value = ymd;
};

// �J�����_�[�\������\������

JKL.Calendar.prototype.show = function () {
    this.getCalendarElement().style.display = "";
    this.show_cd = "1";
};

// �J�����_�[�\�����𑦍��ɉB��
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
JKL.Calendar.prototype.hide = function () {
    this.getCalendarElement().style.display = "none";
};

// �J�����_�[�ȊO�̃N���b�N���̓���
JKL.Calendar.prototype.hide_nocd = function () {
    var clickElement = this.eid;
        if (clickElement != null ) {
            alert(clickElement);
            //cal1.hide();
            //cal2.hide();
        }
};

// �J�����_�[�\�������t�F�[�h�A�E�g����
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
JKL.Calendar.prototype.fadeOut = function ( s,fix ) {
    if( fix ){return}

    if ( JKL.Opacity ) {
        this.getOpacityObject().fadeOut(s);
    } else {
        this.hide();
    }
};

// ���P�ʂňړ�����
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
JKL.Calendar.prototype.moveMonth = function ( mon,fix ) {
    // �O�ֈړ�
    if ( ! this.date ) this.initDate();
    for( ; mon<0; mon++ ) {
        this.date.setDate(1);   // ����1����1���O�͕K���O�̌�
        this.date.setTime( this.date.getTime() - (24*3600*1000) );
    }
    // ��ֈړ�
    for( ; mon>0; mon-- ) {
        this.date.setDate(1);   // ����1����32����͕K�����̌�
        this.date.setTime( this.date.getTime() + (24*3600*1000)*32 );
    }
    this.date.setDate(1);       // ������1���ɖ߂�
    this.write( fix );    // �`�悷��
};

// �C�x���g��o�^����

JKL.Calendar.prototype.addEvent = function ( elem, ev, func ) {
//  prototype.js ������Η��p����(IE���������[�N���)
    if ( window.Event && Event.observe ) {
        Event.observe( elem, ev, func, false );
    } else {
        elem["on"+ev] = func;
    }
}

// �J�����_�[��`�悷��

// 2006.03.25 ���B�G�s joao����̌Œ�\���𗘗p
JKL.Calendar.prototype.write = function ( fix ) {
    var date = new Date();
    if ( ! this.date ) this.initDate();
    date.setTime( this.date.getTime() );

    var year = date.getFullYear();          // �w��N
    var mon  = date.getMonth();             // �w�茎
    var today = date.getDate();             // �w���
    var form1 = this.getFormElement();
    //var form1 = this.getFormElement();
    //var f_ymd = this.getFormElement();
    //var f_yy  = '2017';
    //var f_mm  = '06';
    //var f_dd  = '14';
    //var form1 = f_yy + this.spliter + f_mm + this.spliter + f_dd; 

    // �I���\�ȓ��t�͈�
    var min;
    if ( this.min_date ) {
        var tmp = new Date( this.min_date.getFullYear(), 
            this.min_date.getMonth(), this.min_date.getDate() );
        min = tmp.getTime();
    }
    var max;
    if ( this.max_date ) {
        var tmp = new Date( this.max_date.getFullYear(), 
            this.max_date.getMonth(), this.max_date.getDate() );
        max = tmp.getTime();
    }

    // ���O�̌��j���܂Ŗ߂�
    date.setDate(1);                        // 1���ɖ߂�
    var wday = date.getDay();               // �j�� ���j(0)�`�y�j(6)

// 2006.11.15 MOM �\���J�n�j�����ςɂ����̂ŁA���W�b�N������Ƃ�����܂���
    if ( wday != this.start_day ) {
        date.setTime( date.getTime() - (24*3600*1000)*((wday-this.start_day+7)%7) );
    }
/*
    if ( wday != 1 ) {
        if ( wday == 0 ) wday = 7;
        date.setTime( date.getTime() - (24*3600*1000)*(wday-1) );
    }
*/

    // �ő��7���~6�T�ԁ�42�����̃��[�v
    var list = new Array();
    for( var i=0; i<42; i++ ) {
        var tmp = new Date();
        tmp.setTime( date.getTime() + (24*3600*1000)*i );
        if ( i && i%7==0 && tmp.getMonth() != mon ) break;
        list[list.length] = tmp;
    }

    // �X�^�C���V�[�g�𐶐�����
    var month_table_style = 'width: 100%; ';
    month_table_style += 'background: '+this.style.frame_color+'; ';
    month_table_style += 'border: 1px solid '+this.style.frame_color+';';

    var week_table_style = 'width: 100%; ';
    week_table_style += 'background: '+this.style.day_bgcolor+'; ';
    week_table_style += 'border-left: 1px solid '+this.style.frame_color+'; ';
    week_table_style += 'border-right: 1px solid '+this.style.frame_color+'; ';

    var days_table_style = 'width: 100%; ';
    days_table_style += 'background: '+this.style.day_bgcolor+'; ';
    days_table_style += 'border: 1px solid '+this.style.frame_color+'; ';

    var month_td_style = "";
// 2007.02.04 MOM TD�^�O���w�i�F�̃X�^�C���𖾎��I�Ɏw�肷��
    month_td_style += 'background: '+this.style.frame_color+'; ';
    month_td_style += 'font-size: '+this.style.font_size+'; ';
    month_td_style += 'color: '+this.style.month_color+'; ';
    month_td_style += 'padding: 4px 0px 2px 0px; ';
    month_td_style += 'text-align: center; ';
    month_td_style += 'font-weight: bold;';

    var week_td_style = "";
// 2007.02.04 MOM TD�^�O���w�i�F�̃X�^�C���𖾎��I�Ɏw�肷��
    week_td_style += 'background: '+this.style.day_bgcolor+'; ';
    week_td_style += 'font-size: '+this.style.font_size+'; ';
    week_td_style += 'padding: 2px 0px 2px 0px; ';
    week_td_style += 'font-weight: bold;';
    week_td_style += 'text-align: center;';

    var days_td_style = "";
// 2007.02.04 MOM TD�^�O���w�i�F�̃X�^�C���𖾎��I�Ɏw�肷��
    days_td_style += 'background: '+this.style.day_bgcolor+'; ';
    days_td_style += 'font-size: '+this.style.font_size+'; ';
    days_td_style += 'padding: 1px; ';
    days_td_style += 'text-align: center; ';
    days_td_style += 'font-weight: bold;';

    var days_unselectable = "font-weight: normal;";

    // HTML�\�[�X�𐶐�����
    var src1 = "";

// 2006.11.23 MOM �ז���<select>�ւ̉��}���u���̂P
// �e�[�u����div�ň͂�ŏ�ʃ��C���ɐݒ�(z-index�̒l��傫�����Ă���)
// 2006.11.27 MOM �`��t�B�[���h�̍������擾���邽�߁Aid���Z�b�g���Ă���
    src1 += '<BR><BR>';
    src1 += '<div id="'+this.eid+'_screen" style="position:relative;z-index:'+(this.zindex+1)+';">\n';

    src1 += '<table border="0" cellpadding="0" cellspacing="0" style="'+month_table_style+'"><tr>';
    src1 += '<td id="__'+this.eid+'_btn_prev" title="�O�̌���" style="'+month_td_style+'">��</td>';
    src1 += '<td style="'+month_td_style+'">&nbsp;</td>';
// 2006.12.04 ksuzu �\�������N���b�N����ƌ��݂̌��Ɉړ�
    src1 += '<td id="__'+this.eid+'_btn_today" style="'+month_td_style+'">'+(year)+'�N '+(mon+1)+'��</td>';
//    src1 += '<td style="'+month_td_style+'">'+(year)+'�N '+(mon+1)+'��</td>';
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
    src1 += '<td id="__'+this.eid+'_btn_close" title="����" style="'+month_td_style+'">';
	if( ! fix ){src1+='�~'}
	src1 += '</td>';
    src1 += '<td id="__'+this.eid+'_btn_next" title="���̌���" style="'+month_td_style+'">��</td>';
    src1 += "</tr></table>\n";
    src1 += '<table border="0" cellpadding="0" cellspacing="0" style="'+week_table_style+'"><tr>';

// 2006.11.15 MOM �\���J�n�j��start_day���珇�Ɉ�T�ԕ��\������
    for(var i = this.start_day; i < this.start_day + 7; i++){
        var _wday = i%7;
        if(_wday == 0){
             src1 += '<td style="color: '+this.style.sunday_color+'; '+week_td_style+'">��</td>';
        }else if(_wday == 6){
             src1 += '<td style="color: '+this.style.saturday_color+'; '+week_td_style+'">�y</td>';
        }else{
             src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">';
            if(_wday == 1)        src1 += '��</td>';
            else if(_wday == 2)    src1 += '��</td>';
            else if(_wday == 3)    src1 += '��</td>';
            else if(_wday == 4)    src1 += '��</td>';
            else if(_wday == 5)    src1 += '��</td>';
        }
    }
/*
    src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">��</td>';
    src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">��</td>';
    src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">��</td>';
    src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">��</td>';
    src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">��</td>';
    src1 += '<td style="color: '+this.style.saturday_color+'; '+week_td_style+'">�y</td>';
    src1 += '<td style="color: '+this.style.sunday_color+'; '+week_td_style+'">��</td>';
*/

    src1 += "</tr></table>\n";
    src1 += '<table border="0" cellpadding="0" cellspacing="0" style="'+days_table_style+'">';

    var curutc;
    if ( form1 && form1.value ) {
        var form_yy  = "" + form1.value.substr(0,4);
        var form_mm  = "" + form1.value.substr(4,2);
        var form_dd  = "" + form1.value.substr(6,2);
        var form_ymd = form_yy + this.spliter + form_mm + this.spliter + form_dd;
        var splt    = form_ymd.split(this.spliter);
        //var splt    = form1.value.split(this.spliter);
        if ( splt[0] > 0 && splt[2] > 0 ) {
            var curdd = new Date( splt[0]-0, splt[1]-1, splt[2]-0 );
            curutc = curdd.getTime();                           // �t�H�[����̓���
        }
    }

// 2006.11.23 MOM �������t���擾���A�����b��؂�̂Ă�
    var realdd = new Date();
    var realutc = (new Date(realdd.getFullYear(),realdd.getMonth(),realdd.getDate())).getTime();

    for ( var i=0; i<list.length; i++ ) {
        var dd = list[i];
        var ww = dd.getDay();
        var mm = dd.getMonth();

        if ( ww == this.start_day ) {
            src1 += "<tr>";                                     // �\���J�n�j���̑O�ɍs��
        }
/*
        if ( ww == 1 ) {
            src1 += "<tr>";                                     // ���j���̑O�ɍs��
        }
*/

        var cc = days_td_style;
        var utc = dd.getTime();

// 2007.03.12 ���B�G�s �j������ǉ�
// 2007.03.25 ���B�G�s 1����12���ł̓����O�̏j������o�O���C��
// 2007.04.03 ���B�G�s �x������֐����ɔ����C��
        var ss = this.getDateYMD(dd);
        var getholiday = ktHolidayName(ss);
        var kyuzitsu = this.getKyuzitsu(ss);

        if ( mon == mm ) {

// 2006.11.23 MOM �ŏ��ɍ������t���ǂ������`�F�b�N����
// �������łȂ��ꍇ�ɂ��F�ς���ƑI���ł������Ɍ����ĕ���킵���̂ŁA�������������t�̏ꍇ�̂ݐF��ς���
        if ( utc == realutc ){
                cc += "color: "+this.style.today_color+";";     // �������t
            }
// 2007.03.12 ���B�G�s �j���̐F��ύX
// 2007.03.25 ���B�G�s �x���̐F��ǉ�
	if ( ww == 0 || getholiday != "" ){
                cc += "color: "+this.style.sunday_color+";";    // �����̓��j���E�j��
            } else if ( kyuzitsu != "" ) {
                cc += "color: "+this.style.kyuzitsu_color+";";  // �����̋x��
            } else if ( ww == 6 ) {
                cc += "color: "+this.style.saturday_color+";";  // �����̓y�j��
            } else {
                cc += "color: "+this.style.weekday_color+";";   // �����̕���
            }
        } else {
            cc += "color: "+this.style.others_color+";";        // �O�����Ɨ������̓��t
        }

// 2006.11.23 MOM utc�̕ϐ��錾�����Ɉړ�
//      var utc = dd.getTime();

// 2006.11.30 MOM �I���\�ȗj���w��̏����ǉ�
// 2007.04.03 ���B�G�s �����ɍ�����ǉ�
        if ( mon == mm && utc == curutc ) {                                  // �t�H�[����̓���
            cc += "background: "+this.style.day_hover_bgcolor+";";
        }

// 2006.11.30 MOM �������t�̔w�i�F
// 2007.03.25 ���B�G�s �I��s�\���������t�̔w�i�F��ǉ�
        else if ( mon == mm && utc == realutc ) {
            if(!this.selectable_days[dd.getDay()] || (!this.selectable_holiday && getholiday != "") || (!this.selectable_kyuzitsu && kyuzitsu != "")){
                cc += "background: "+this.style.unselectable_today_bgcolor+";";
            } else {
                cc += "background: "+this.style.today_bgcolor+";";
            }
        }
// 2006.11.30 MOM �I��s�\�ȓ��t�̔w�i�F
// 2007.03.25 ���B�G�s joao����̑I���\���w��𗘗p
        else if (( min && min > utc ) || ( max && max < utc ) || (mon == mm && !this.selectable_days[dd.getDay()]) || (mon == mm && !this.selectable_holiday && getholiday != "") || (mon == mm && !this.selectable_kyuzitsu && kyuzitsu != "")){
            cc += 'background: '+this.style.unselectable_day_bgcolor+';'
        }

// 2006.11.23 MOM �g���`���ǉ�
// 2007.03.25 ���B�G�s �I��s�\���t�Z���̘g���̐F��ǉ�
        if ( this.draw_border ){
   // �������������t
            if ( mon == mm && utc == realutc ){
                if(!this.selectable_days[dd.getDay()] || (!this.selectable_holiday && getholiday != "" ) || (!this.selectable_kyuzitsu && kyuzitsu != "")){
                    cc += "border:solid 1px "+this.style.unselectable_day_border_color+";";// �I��s�\���t
                } else {
                    cc += "border:solid 1px "+this.style.today_border_color+";";  // ���̑�
                } 
   // ���̑�                   
            } else {
                cc += "border:solid 1px "+this.style.others_border_color+";"; 
            }
        }

        var ss = this.getDateYMD(dd);
        var tt = dd.getDate();

// 2007.03.12 ���B�G�s �j�������^�C�g���ɒǉ�
// 2007.03.25 ���B�G�s �x�������^�C�g���ɒǉ�
        if (getholiday != ""){
            var Whatday = "�u"+getholiday+"�v";
        }else if (kyuzitsu != ""){
            Whatday = "�u"+kyuzitsu+"�v";
        }else {
            Whatday = "";
        }

        src1 += '<td style="'+cc+'" title="'+ss+''+Whatday+'" id="__'+this.eid+'_td_'+ss+'">'+tt+'</td>';

        if ( ww == (this.start_day+6)%7 ) {
            src1 += "</tr>\n";                                  // �\���J�n�j���̂P��O�ōs��
        }
/*
        if ( ww == 7 ) {
            src1 += "</tr>\n";                                  // �y�j���̌�ɍs��
        }
*/
    }
    src1 += "</table>\n";

    src1 += '</div>\n';

    // �J�����_�[������������
    var cal1 = this.getCalendarElement();
    if ( ! cal1 ) return;
    cal1.style.width = this.style.frame_width;
    cal1.style.position = "absolute";

// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
    if( fix ){cal1.style.position = ""}
    else{cal1.style.position = "absolute"}

    cal1.innerHTML = src1;


// 2006.11.23 MOM �ז���<select>�ւ̉��}���u���̂Q
// �J�����_�[�ƑS�������T�C�Y��IFRAME�𐶐����A���W����v�����ĉ��ʃ��C���ɕ`�悷��

// IFRAME�Ή����\�ȃo�[�W�����̂ݏ��u���{��
    var ua = navigator.userAgent;
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
    if( ! fix && (ua.indexOf("MSIE 5.5") >= 0 || ua.indexOf("MSIE 6") >= 0 )){

// 2006.11.27 MOM ���innerHTML�ɃJ�����_�[�̎��̂�n���Ă����āA�`��t�B�[���h�̍������擾����
// ��hide()���Ă΂ꂽ���ゾ�ƁAoffsetHeight��0�ɂȂ��Ă��܂��̂ŁA�ꎞ�I��show���Ă�
        this.show();
        var screenHeight = cal1.document.getElementById(this.eid+"_screen").offsetHeight;
        this.hide();

        src1 += '<div style="position:absolute;z-index:'+this.zindex+';top:0px;left:0px;">';
        src1 += '<iframe src="dummy.html" frameborder=0 scrolling=no width='+this.style.frame_width+' height='+screenHeight+'></iframe>';
        src1 += '</div>\n';


//���߂�innerHTML�ɃZ�b�g
        cal1.innerHTML = src1;
    }


    // �C�x���g��o�^����
    var __this = this;
    var get_src = function (ev) {
        ev  = ev || window.event;
        var src = ev.target || ev.srcElement;
        return src;
    };
    var month_onmouseover = function (ev) {
        var src = get_src(ev);
        src.style.color = __this.style.month_hover_color;
        src.style.background = __this.style.month_hover_bgcolor;
    };
    var month_onmouseout = function (ev) {
        var src = get_src(ev);
        src.style.color = __this.style.month_color;
        src.style.background = __this.style.frame_color;
    };
    var day_onmouseover = function (ev) {
        var src = get_src(ev);
        src.style.background = __this.style.day_hover_bgcolor;
    };
    var day_onmouseout = function (ev) {
        var src = get_src(ev);
// 2006.11.30 MOM �������������t�ł���΁A�������t�p�̔w�i�F��K�p
        var today = new Date();
        if( today.getMonth() == __this.date.getMonth() && src.id == '__'+__this.eid+'_td_'+__this.getDateYMD(today) ){
            src.style.background = __this.style.today_bgcolor;
        }else{
            src.style.background = __this.style.day_bgcolor;
        }
    };
    var day_onclick = function (ev) {
        var src = get_src(ev);
        var srcday = src.id.substr(src.id.length-10);
        __this.setFormValue( srcday );
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
        __this.fadeOut( 1.0,fix );
    };

//
// 2006.12.04 ksuzu �I���ł��Ȃ����ւ̃����N�͍쐬���Ȃ�
//
    // �O�̌��փ{�^��
    var tdprev = document.getElementById( "__"+this.eid+"_btn_prev" );
    //�O�̌��̍ŏI��
    var tmpDate = new Date(year,mon,1);
    tmpDate.setTime( tmpDate.getTime() - (24*3600*1000) );
    //�I���\�ȓ�������H
    if ( !min || this.min_date <= tmpDate ){
        tdprev.style.cursor = this.style.cursor;
        this.addEvent( tdprev, "mouseover", month_onmouseover );
        this.addEvent( tdprev, "mouseout", month_onmouseout );
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
        this.addEvent( tdprev, "click", function(){ __this.moveMonth( -1,fix ); });
    }
    //�I��s�\
    else{
        tdprev.title = "�O�̌��͑I���ł��܂���";
    }
/*
    tdprev.style.cursor = this.style.cursor;
    this.addEvent( tdprev, "mouseover", month_onmouseover );
    this.addEvent( tdprev, "mouseout", month_onmouseout );
//  2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
    this.addEvent( tdprev, "click", function(){ __this.moveMonth( -1,fix ); });
2006.12.04 ksuzu */

//
// 2006.12.04 ksuzu �\�������N���b�N����ƌ��݂̌��Ɉړ�
//
    var nMov = (realdd.getFullYear() - year) * 12 + (realdd.getMonth() - mon);
    if ( nMov != 0 ){
        // ���݂̌��փ{�^��
        var tdtoday = document.getElementById( "__"+this.eid+"_btn_today" );
        tdtoday.style.cursor = this.style.cursor;
        tdtoday.title = "���݂̌���";
        this.addEvent( tdtoday, "mouseover", month_onmouseover );
        this.addEvent( tdtoday, "mouseout", month_onmouseout )
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
        this.addEvent( tdtoday, "click", function(){ __this.moveMonth( nMov,fix ); });
    }

    // ����{�^��
    var tdclose = document.getElementById( "__"+this.eid+"_btn_close" );
    tdclose.style.cursor = this.style.cursor;
    this.addEvent( tdclose, "mouseover", month_onmouseover );
    this.addEvent( tdclose, "mouseout", month_onmouseout );

//
// 2006.12.04 ksuzu �J�����_�[�̏����\����߂�
//
    this.addEvent( tdclose, "click", function(){ __this.getFormValue(); __this.hide(); });
//    this.addEvent( tdclose, "click", function(){ __this.hide(); });

//
// 2006.12.04 ksuzu �I���ł��Ȃ����ւ̃����N�͍쐬���Ȃ�
//
    // ���̌��փ{�^��
    var tdnext = document.getElementById( "__"+this.eid+"_btn_next" );
    //���̌��̏���
    var tmpDate = new Date(year,mon,1);
    tmpDate.setTime( tmpDate.getTime() + (24*3600*1000)*32 );
    tmpDate.setDate(1);
    //�I���\�ȓ�������H
    if ( !max || this.max_date >= tmpDate ){
        tdnext.style.cursor = this.style.cursor;
        this.addEvent( tdnext, "mouseover", month_onmouseover );
        this.addEvent( tdnext, "mouseout", month_onmouseout );
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
        this.addEvent( tdnext, "click", function(){ __this.moveMonth( +1,fix ); });
    }
    //�I��s�\
    else{
        tdnext.title = "���̌��͑I���ł��܂���";
    }
/*
    tdnext.style.cursor = this.style.cursor;
    this.addEvent( tdnext, "mouseover", month_onmouseover );
    this.addEvent( tdnext, "mouseout", month_onmouseout );
// 2007.03.25 ���B�G�s joao����̌Œ�\���𗘗p
    this.addEvent( tdnext, "click", function(){ __this.moveMonth( +1,fix ); });
2006.12.04 ksuzu */

    // �Z�����Ƃ̃C�x���g��o�^����
    for ( var i=0; i<list.length; i++ ) {
        var dd = list[i];
        if ( mon != dd.getMonth() ) continue;       // �����̃Z���ɂ̂ݐݒ肷��

        var utc = dd.getTime();
// 2007.03.25 ���B�G�s joao����̑I���\���w��𗘗p
	var tt = dd.getDate();

        if ( min && min > utc ) continue;           // �̉߂���
        if ( max && max < utc ) continue;           // �����߂���
// 2007.04.03 ���B�G�s �Œ�\���ɑΉ�
        if ( ! fix ) {if ( utc == curutc ) continue;}   // �t�H�[����̓���
// 2006.11.30 MOM �I���\�ȗj���w��Ή�
// 2007.03.25 ���B�G�s joao����̑I���\���w��𗘗p
// 2007.04.03 ���B�G�s �x������֐����ɔ����ꕔ�C��
        var ss = this.getDateYMD(dd);
        if (!this.selectable_days[dd.getDay()] || (!this.selectable_holiday && ktHolidayName(ss) != "") || (!this.selectable_kyuzitsu && this.getKyuzitsu(ss) != "")){continue}

        var cc = document.getElementById( "__"+this.eid+"_td_"+ss );
        if ( ! cc ) continue;

        cc.style.cursor = this.style.cursor;
        this.addEvent( cc, "mouseover", day_onmouseover );
        this.addEvent( cc, "mouseout", day_onmouseout );
        this.addEvent( cc, "click", day_onclick );
    }

    // �\������
    this.show();
};

// ���o�[�W�����݊��itypo�j
JKL.Calendar.prototype.getCalenderElement = JKL.Calendar.prototype.getCalendarElement;
JKL.Calender = JKL.Calendar;
