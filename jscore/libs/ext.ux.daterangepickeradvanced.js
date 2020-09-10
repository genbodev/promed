/*
  * Ext.ux.DateRangePickerAdvanced  Addon
  * Ext.ux.form.DateRangeFieldAdvanced Addon
  *
  * @author    Ivan Petukhov aka Lich (megatherion@list.ru)
  * @copyright (c) 2008, Ivan Petukhov (megatherion@list.ru)
  *
  * @class Ext.ux.DateRangePickerAdvanced
  * @extends Ext.DatePicker
  *
  * @class Ext.ux.form.DateRangeFieldAdvanced
  * @extends Ext.form.DateField
  *
  * @version 1.01
  * @date 24.02.2009

  You need at least ExtJS 2.0.2 or higher
*/

Ext.DateRangePickerAdvanced = Ext.extend(Ext.Component, {

    todayText : langs('Сегодня'),

    okText : "&#160;OK&#160;", // &#160; to give the user extra clicking room

    cancelText : langs('Отмена'),

    todayTip : "{0}",

    minText : "This date is before the minimum date",

    maxText : "This date is after the maximum date",

    format : "d.m.y",

    disabledDaysText : "Disabled",

    disabledDatesText : "Disabled",

    constrainToViewport : true,

    monthNames : Date.monthNames,

    dayNames : Date.dayNames,

    nextText: 'Next Month (Control+Right)',

    prevText: 'Previous Month (Control+Left)',

    monthYearText: 'Choose a month (Control+Up/Down to move years)',

    startDay : 1,

    showToday : true,

    value1 : '',

    value2 : '',


    // private
    initComponent : function(){
        Ext.DateRangePickerAdvanced.superclass.initComponent.call(this);

        this.value = this.value ?
                 this.value.clearTime() : new Date().clearTime();

        this.addEvents(

            'select'
        );

        if(this.handler){
            this.on("select", this.handler,  this.scope || this);
        }

        this.initDisabledDays();
    },

    // private
    initDisabledDays : function(){
        if(!this.disabledDatesRE && this.disabledDates){
            var dd = this.disabledDates;
            var re = "(?:";
            for(var i = 0; i < dd.length; i++){
                re += dd[i];
                if(i != dd.length-1) re += "|";
            }
            this.disabledDatesRE = new RegExp(re + ")");
        }
    },


    setDisabledDates : function(dd){
        if(Ext.isArray(dd)){
            this.disabledDates = dd;
            this.disabledDatesRE = null;
        }else{
            this.disabledDatesRE = dd;
        }
        this.initDisabledDays();
        this.update(this.value, true);
    },


    setDisabledDays : function(dd){
        this.disabledDays = dd;
        this.update(this.value, true);
    },


    setMinDate : function(dt){
        this.minDate = dt;
        this.update(this.value, true);
    },


    setMaxDate : function(dt){
        this.maxDate = dt;
        this.update(this.value, true);
    },

    // private
    parseDate : function(value){
        if(!value || Ext.isDate(value)){
            return value;
        }
        var v = Date.parseDate(value, this.format);
        if(!v && this.altFormats){
            if(!this.altFormatsArray){
                this.altFormatsArray = this.altFormats.split("|");
            }
            for(var i = 0, len = this.altFormatsArray.length; i < len && !v; i++){
                v = Date.parseDate(value, this.altFormatsArray[i]);
            }
        }
        return v;
    },

    // private
    formatDate : function(date){
        return Ext.isDate(date) ? date.dateFormat(this.format) : date;
    },

    setValue : function(value){
        var old = this.value;
        this.value = value.clearTime(true);
        if(this.el){
            this.update(this.value);
        }
    },

    setValue1 : function(value){
        var old = this.value;
        this.value1 = value.clearTime(true);
        if(this.el){
            this.update(this.value1);
        }
    },

    setValue2 : function(value){
        var old = this.value;
        this.value2 = value.clearTime(true);
        if(this.el){
            this.update(this.value2);
        }
    },

    getValue : function(){
        return this.value;
    },

    getValue1 : function(){
        return this.value1;
    },

    getValue2 : function(){
        return this.value2;
    },
    // private
    focus : function(){
        if(this.el){
            this.update(this.activeDate1);
        }
    },

    // private
    onRender : function(container, position){
        var m = [
             '<table cellspacing="0"><tr><td class="x-datepicker-1"><table cellspacing="0">',
                '<tr><td class="x-date-left1"><a href="#" title="', this.prevText ,'">&#160;</a></td><td class="x-date-middle1" align="center"></td><td class="x-date-right1"><a href="#" title="', this.nextText ,'">&#160;</a></td></tr>',
                '<tr><td colspan="3">'];
        m.push('<table class="x-date-inner1" cellspacing="0"><thead><tr>');
        var dn = this.dayNames;
        for(var i = 0; i < 7; i++){
            var d = this.startDay+i;
            if(d > 6){
                d = d-7;
            }
            m.push("<th><span>", dn[d].substr(0,1), "</span></th>");
        }
        m[m.length] = "</tr></thead><tbody><tr>";
        for(var i = 0; i < 42; i++) {
            if(i % 7 == 0 && i != 0){
                m[m.length] = "</tr><tr>";
            }
            m[m.length] = '<td><a href="#" hidefocus="on" class="x-date-date1" tabIndex="1"><em><span></span></em></a></td>';
        }
        m.push('</tr></tbody></table></td></tr>',
                '<tr><td colspan="3" class="x-date-bottom" align="center"><div class="x-date-bottom1" style="float:center;"></div></td></tr>',
                '</table></td>');
		
		m.push('<td class="x-datepicker-2"><table cellspacing="0"><tr><td class="x-date-left2"><a href="#" title="', this.prevText ,'">&#160;</a></td><td class="x-date-middle2" align="center"></td><td class="x-date-right2"><a href="#" title="', this.nextText ,'">&#160;</a></td>');
		m.push('<tr><td colspan="3"><table class="x-date-inner2" style="border-left:1px solid #1b376c;" cellspacing="0"><thead><tr>');
        var dn = this.dayNames;
        for(var i = 0; i < 7; i++){
            var d = this.startDay+i;
            if(d > 6){
                d = d-7;
            }
            m.push("<th><span>", dn[d].substr(0,1), "</span></th>");
        }
        m[m.length] = "</tr></thead><tbody><tr>";
        for(var i = 0; i < 42; i++) {
            if(i % 7 == 0 && i != 0){
                m[m.length] = "</tr><tr>";
            }
            m[m.length] = '<td><a href="#" hidefocus="on" class="x-date-date2" tabIndex="1"><em><span></span></em></a></td>';
        }
        m.push('</tr></tbody></table></td></tr>',
				'<tr><td colspan="3" class="x-date-bottom" align="center"><div class="x-date-bottom2" style="float:center;"></div></td></tr>',
				'</table></td></tr></table>');
				
		m.push('<div class="x-date-mp1"></div><div class="x-date-mp2"></div>');

        var el = document.createElement("div");
        el.className = "x-date-picker";
        el.innerHTML = m.join("");

        container.dom.insertBefore(el, position);

        this.el = Ext.get(el);
        this.eventEl = Ext.get(el.firstChild);

        this.leftClickRpt1 = new Ext.util.ClickRepeater(this.el.child("td.x-date-left1 a"), {
            handler: this.showPrevMonth1,
            scope: this,
            preventDefault:true,
            stopDefault:true
        });

        this.rightClickRpt1 = new Ext.util.ClickRepeater(this.el.child("td.x-date-right1 a"), {
            handler: this.showNextMonth1,
            scope: this,
            preventDefault:true,
            stopDefault:true
        });

        this.leftClickRpt2 = new Ext.util.ClickRepeater(this.el.child("td.x-date-left2 a"), {
            handler: this.showPrevMonth2,
            scope: this,
            preventDefault:true,
            stopDefault:true
        });

        this.rightClickRpt2 = new Ext.util.ClickRepeater(this.el.child("td.x-date-right2 a"), {
            handler: this.showNextMonth2,
            scope: this,
            preventDefault:true,
            stopDefault:true
        });

        this.eventEl.on("mousewheel", this.handleMouseWheel,  this);

		this.datePicker1 = this.el.child('td.x-datepicker-1');
		this.datePicker2 = this.el.child('td.x-datepicker-2');
		
        this.monthPicker1 = this.el.down('div.x-date-mp1');
        this.monthPicker1.enableDisplayMode('block');
        this.monthPicker2 = this.el.down('div.x-date-mp2');
        this.monthPicker2.enableDisplayMode('block');

        var kn = new Ext.KeyNav(this.eventEl, {
            "left" : function(e){
                e.ctrlKey ?
                    this.showPrevMonth1() :
                    //this.update(this.activeDate1.add("d", -1));
                    this.setValue1(this.activeDate1.add("d", -1));
            },

            "right" : function(e){
                e.ctrlKey ?
                    this.showNextMonth1() :
                    this.setValue1(this.activeDate1.add("d", 1));
            },

            "up" : function(e){
                e.ctrlKey ?
                    this.showNextYear1() :
                    this.setValue1(this.activeDate1.add("d", -7));
            },

            "down" : function(e){
                e.ctrlKey ?
                    this.showPrevYear1() :
                    this.setValue1(this.activeDate1.add("d", 7));
            },

            "pageUp" : function(e){
                this.showNextMonth1();
            },

            "pageDown" : function(e){
                this.showPrevMonth1();
            },

            "enter" : function(e){
                e.stopPropagation();
                return true;
            },

            scope : this
        });

        this.eventEl.on("click", this.handleDateClick1,  this, {delegate: "a.x-date-date1"});
        this.eventEl.on("click", this.handleDateClick2,  this, {delegate: "a.x-date-date2"});

        this.el.unselectable();

        this.cells1 = this.el.select("table.x-date-inner1 tbody td");
        this.textNodes1 = this.el.query("table.x-date-inner1 tbody span");
        this.cells2 = this.el.select("table.x-date-inner2 tbody td");
        this.textNodes2 = this.el.query("table.x-date-inner2 tbody span");

        this.mbtn1 = new Ext.Button({
            text: "&#160;",
            tooltip: this.monthYearText,
            renderTo: this.el.child("td.x-date-middle1", true)
        });
        this.mbtn2 = new Ext.Button({
            text: "&#160;",
            tooltip: this.monthYearText,
            renderTo: this.el.child("td.x-date-middle2", true)
        });

        this.mbtn1.on('click', this.showMonthPicker1, this);
        this.mbtn1.el.child(this.mbtn1.menuClassTarget).addClass("x-btn-with-menu1");

        this.mbtn2.on('click', this.showMonthPicker2, this);
        this.mbtn2.el.child(this.mbtn2.menuClassTarget).addClass("x-btn-with-menu2");

        if(this.showToday){
            this.todayKeyListener = this.eventEl.addKeyListener(Ext.EventObject.SPACE, this.selectToday,  this);
            var today = (new Date()).dateFormat(this.format);
            this.todayBtn1 = new Ext.Button({
                renderTo: this.el.child("div.x-date-bottom1", true),
                text: String.format(this.todayText, today),
                tooltip: String.format(this.todayTip, today),
                handler: this.selectToday1,
                scope: this
            });
            this.todayBtn2 = new Ext.Button({
                renderTo: this.el.child("div.x-date-bottom2", true),
                text: String.format(this.todayText, today),
                tooltip: String.format(this.todayTip, today),
                handler: this.selectToday2,
                scope: this
            });
        }

        if(Ext.isIE){
            this.el.repaint();
        }
        this.update(this.value);
    },

    // private
    createMonthPicker1 : function(){
        if(!this.monthPicker1.dom.firstChild){
            var buf = ['<table border="0" cellspacing="0">'];
            for(var i = 0; i < 6; i++){
                buf.push(
                    '<tr><td class="x-date-mp-month"><a href="#">', this.monthNames[i].substr(0, 3), '</a></td>',
                    '<td class="x-date-mp-month x-date-mp-sep"><a href="#">', this.monthNames[i+6].substr(0, 3), '</a></td>',
                    i == 0 ?
                    '<td class="x-date-mp-ybtn" align="center"><a class="x-date-mp-prev"></a></td><td class="x-date-mp-ybtn" align="center"><a class="x-date-mp-next"></a></td></tr>' :
                    '<td class="x-date-mp-year"><a href="#"></a></td><td class="x-date-mp-year"><a href="#"></a></td></tr>'
                );
            }
            buf.push(
                '<tr class="x-date-mp-btns"><td colspan="4"><button type="button" class="x-date-mp-ok">',
                    this.okText,
                    '</button><button type="button" class="x-date-mp-cancel">',
                    this.cancelText,
                    '</button></td></tr>',
                '</table>'
            );
            this.monthPicker1.update(buf.join(''));
            this.monthPicker1.on('click', this.onMonthClick1, this);
            this.monthPicker1.on('dblclick', this.onMonthDblClick1, this);

            this.mpMonths = this.monthPicker1.select('td.x-date-mp-month');
            this.mpYears = this.monthPicker1.select('td.x-date-mp-year');

            this.mpMonths.each(function(m, a, i){
                i += 1;
                if((i%2) == 0){
                    m.dom.xmonth = 5 + Math.round(i * .5);
                }else{
                    m.dom.xmonth = Math.round((i-1) * .5);
                }
            });
        }
    },

    // private
    createMonthPicker2 : function(){
        if(!this.monthPicker2.dom.firstChild){
            var buf = ['<table border="0" cellspacing="0">'];
            for(var i = 0; i < 6; i++){
                buf.push(
                    '<tr><td class="x-date-mp-month"><a href="#">', this.monthNames[i].substr(0, 3), '</a></td>',
                    '<td class="x-date-mp-month x-date-mp-sep"><a href="#">', this.monthNames[i+6].substr(0, 3), '</a></td>',
                    i == 0 ?
                    '<td class="x-date-mp-ybtn" align="center"><a class="x-date-mp-prev"></a></td><td class="x-date-mp-ybtn" align="center"><a class="x-date-mp-next"></a></td></tr>' :
                    '<td class="x-date-mp-year"><a href="#"></a></td><td class="x-date-mp-year"><a href="#"></a></td></tr>'
                );
            }
            buf.push(
                '<tr class="x-date-mp-btns"><td colspan="4"><button type="button" class="x-date-mp-ok">',
                    this.okText,
                    '</button><button type="button" class="x-date-mp-cancel">',
                    this.cancelText,
                    '</button></td></tr>',
                '</table>'
            );
            this.monthPicker2.update(buf.join(''));
            this.monthPicker2.on('click', this.onMonthClick2, this);
            this.monthPicker2.on('dblclick', this.onMonthDblClick2, this);

            this.mpMonths = this.monthPicker2.select('td.x-date-mp-month');
            this.mpYears = this.monthPicker2.select('td.x-date-mp-year');

            this.mpMonths.each(function(m, a, i){
                i += 1;
                if((i%2) == 0){
                    m.dom.xmonth = 5 + Math.round(i * .5);
                }else{
                    m.dom.xmonth = Math.round((i-1) * .5);
                }
            });
        }
    },

    // private
    showMonthPicker1 : function(){
        this.createMonthPicker1();
        var size = this.el.getSize();
        size.width=size.width/2;
        this.monthPicker1.setSize(size);
        this.monthPicker1.child('table').setSize(size);
        this.monthPicker1.setLeft(0);

        this.mpSelMonth = (this.activeDate1 || this.value1).getMonth();
        this.updateMPMonth(this.mpSelMonth);
        this.mpSelYear = (this.activeDate1 || this.value1).getFullYear();
        this.updateMPYear(this.mpSelYear);

        this.monthPicker1.slideIn('t', {duration:.2});
    },

    // private
    showMonthPicker2 : function(){
        this.createMonthPicker2();
        var size = this.el.getSize();
        size.width=size.width/2;
        this.monthPicker2.setSize(size);
        this.monthPicker2.child('table').setSize(size);
        this.monthPicker2.setLeft(size.width);

        this.mpSelMonth = (this.activeDate2 || this.value2).getMonth();
        this.updateMPMonth(this.mpSelMonth);
        this.mpSelYear = (this.activeDate2 || this.value2).getFullYear();
        this.updateMPYear(this.mpSelYear);

        this.monthPicker2.slideIn('t', {duration:.2});
    },

    // private
    updateMPYear : function(y){
        this.mpyear = y;
        var ys = this.mpYears.elements;
        for(var i = 1; i <= 10; i++){
            var td = ys[i-1], y2;
            if((i%2) == 0){
                y2 = y + Math.round(i * .5);
                td.firstChild.innerHTML = y2;
                td.xyear = y2;
            }else{
                y2 = y - (5-Math.round(i * .5));
                td.firstChild.innerHTML = y2;
                td.xyear = y2;
            }
            this.mpYears.item(i-1)[y2 == this.mpSelYear ? 'addClass' : 'removeClass']('x-date-mp-sel');
        }
    },

    // private
    updateMPMonth : function(sm){
        this.mpMonths.each(function(m, a, i){
            m[m.dom.xmonth == sm ? 'addClass' : 'removeClass']('x-date-mp-sel');
        });
    },

    // private
    selectMPMonth: function(m){

    },

    // private
    onMonthClick1 : function(e, t){
        e.stopEvent();
        var el = new Ext.Element(t), pn;
        if(el.is('button.x-date-mp-cancel')){
            this.hideMonthPicker1();
        }
        else if(el.is('button.x-date-mp-ok')){
            var d = new Date(this.mpSelYear, this.mpSelMonth, (this.activeDate1 || this.value1).getDate());
            if(d.getMonth() != this.mpSelMonth){
                // "fix" the JS rolling date conversion if needed
                d = new Date(this.mpSelYear, this.mpSelMonth, 1).getLastDateOfMonth();
            }
            this.setValue1(d);
            this.hideMonthPicker1();
        }
        else if(pn = el.up('td.x-date-mp-month', 2)){
            this.mpMonths.removeClass('x-date-mp-sel');
            pn.addClass('x-date-mp-sel');
            this.mpSelMonth = pn.dom.xmonth;
        }
        else if(pn = el.up('td.x-date-mp-year', 2)){
            this.mpYears.removeClass('x-date-mp-sel');
            pn.addClass('x-date-mp-sel');
            this.mpSelYear = pn.dom.xyear;
        }
        else if(el.is('a.x-date-mp-prev')){
            this.updateMPYear(this.mpyear-10);
        }
        else if(el.is('a.x-date-mp-next')){
            this.updateMPYear(this.mpyear+10);
        }
    },

    // private
    onMonthClick2 : function(e, t){
        e.stopEvent();
        var el = new Ext.Element(t), pn;
        if(el.is('button.x-date-mp-cancel')){
            this.hideMonthPicker2();
        }
        else if(el.is('button.x-date-mp-ok')){
            var d = new Date(this.mpSelYear, this.mpSelMonth, (this.activeDate2 || this.value2).getDate());
            if(d.getMonth() != this.mpSelMonth){
                // "fix" the JS rolling date conversion if needed
                d = new Date(this.mpSelYear, this.mpSelMonth, 1).getLastDateOfMonth();
            }
            this.setValue2(d);
            this.hideMonthPicker2();
        }
        else if(pn = el.up('td.x-date-mp-month', 2)){
            this.mpMonths.removeClass('x-date-mp-sel');
            pn.addClass('x-date-mp-sel');
            this.mpSelMonth = pn.dom.xmonth;
        }
        else if(pn = el.up('td.x-date-mp-year', 2)){
            this.mpYears.removeClass('x-date-mp-sel');
            pn.addClass('x-date-mp-sel');
            this.mpSelYear = pn.dom.xyear;
        }
        else if(el.is('a.x-date-mp-prev')){
            this.updateMPYear(this.mpyear-10);
        }
        else if(el.is('a.x-date-mp-next')){
            this.updateMPYear(this.mpyear+10);
        }
    },

    // private
    onMonthDblClick1 : function(e, t){
        e.stopEvent();
        var el = new Ext.Element(t), pn;
        if(pn = el.up('td.x-date-mp-month', 2)){
            this.setValue1(new Date(this.mpSelYear, pn.dom.xmonth, (this.activeDate1 || this.value1).getDate()));
            this.hideMonthPicker1();
        }
        else if(pn = el.up('td.x-date-mp-year', 2)){
            this.setValue1(new Date(pn.dom.xyear, this.mpSelMonth, (this.activeDate1 || this.value1).getDate()));
            this.hideMonthPicker1();
        }
    },

    // private
    onMonthDblClick2 : function(e, t){
        e.stopEvent();
        var el = new Ext.Element(t), pn;
        if(pn = el.up('td.x-date-mp-month', 2)){
            this.setValue2(new Date(this.mpSelYear, pn.dom.xmonth, (this.activeDate2 || this.value2).getDate()));
            this.hideMonthPicker2();
        }
        else if(pn = el.up('td.x-date-mp-year', 2)){
            this.setValue2(new Date(pn.dom.xyear, this.mpSelMonth, (this.activeDate2 || this.value2).getDate()));
            this.hideMonthPicker2();
        }
    },

    // private
    hideMonthPicker1 : function(disableAnim){
        if(this.monthPicker1){
            if(disableAnim === true){
                this.monthPicker1.hide();
            }else{
                this.monthPicker1.slideOut('t', {duration:.2});
            }
        }
    },

    // private
    hideMonthPicker2 : function(disableAnim){
        if(this.monthPicker2){
            if(disableAnim === true){
                this.monthPicker2.hide();
            }else{
                this.monthPicker2.slideOut('t', {duration:.2});
            }
        }
    },

    // private
    showPrevMonth1 : function(e){
        this.setValue1(this.activeDate1.add("mo", -1));
        this.update(this.value1);
    },

    // private
    showNextMonth1 : function(e){
        this.setValue1(this.activeDate1.add("mo", 1));
        this.update(this.value1);
    },

    // private
    showPrevMonth2 : function(e){
        this.setValue2(this.activeDate2.add("mo", -1));
        this.update(this.value2);
    },

    // private
    showNextMonth2 : function(e){
        this.setValue2(this.activeDate2.add("mo", 1));
        this.update(this.value2);
    },

    // private
    showPrevYear : function(){
        this.setValue2(this.activeDate2.add("y", -1));
        this.update(this.value2);
    },

    // private
    showNextYear : function(){
        this.setValue2(this.activeDate2.add("y", 1));
        this.update(this.value2);
    },

    // private
    handleMouseWheel : function(e){
        var delta = e.getWheelDelta();
        if(delta > 0){
            this.showPrevMonth1();
            e.stopEvent();
        } else if(delta < 0){
            this.showNextMonth1();
            e.stopEvent();
        }
    },

    // private
    handleDateClick1 : function(e, t){
        e.stopEvent();
        if(t.dateValue && !Ext.fly(t.parentNode).hasClass("x-date-disabled")){
            this.setValue1(new Date(t.dateValue));
			if (this.mode == 'oneday') {
				this.applyClicked();
			}
            //this.fireEvent("select", this, this.value);
        }
    },

    // private
    handleDateClick2 : function(e, t){
        e.stopEvent();
        if(t.dateValue && !Ext.fly(t.parentNode).hasClass("x-date-disabled")){
            this.setValue2(new Date(t.dateValue));
			this.applyClicked();
            //this.fireEvent("select", this, this.value);
        }
    },

    // private
    selectToday : function(){
        if(this.todayBtn && !this.todayBtn.disabled){
	        this.setValue(new Date().clearTime());
	        this.fireEvent("select", this, this.value);
        }
    },
    // private
    selectToday1 : function(){
        if(this.todayBtn1 && !this.todayBtn1.disabled){
	        this.setValue1(new Date().clearTime());
            this.update(this.value1);
			
			if (this.mode == 'oneday') {
				this.applyClicked();
			}
	        //this.fireEvent("select", this, this.value);
        }
    },
    // private
    selectToday2 : function(){
        if(this.todayBtn2 && !this.todayBtn2.disabled){
	        this.setValue2(new Date().clearTime());
            this.update(this.value2);
			
			this.applyClicked();
	        //this.fireEvent("select", this, this.value);
        }
    },

    // private
	applyClicked : function() {
		if (this.mode == 'oneday') {
			this.fireEvent("select", this, this.formatDate(this.value1)+' - '+this.formatDate(this.value1));
		} else {
			this.fireEvent("select", this, this.formatDate(this.value1)+' - '+this.formatDate(this.value2));
		}
	},

    // private
    update : function(date, forceRefresh){
        date=this.value1;
        var vd = this.activeDate1;
        this.activeDate1 = date;
        if(!forceRefresh && vd && this.el){
            var t = date.getTime();
            if(vd.getMonth() == date.getMonth() && vd.getFullYear() == date.getFullYear()){
                this.cells1.removeClass("x-date-selected");
                this.cells1.each(function(c){
                   if(c.dom.firstChild.dateValue == t){
                       c.addClass("x-date-selected");
                       setTimeout(function(){
                            try{c.dom.firstChild.focus();}catch(e){}
                       }, 50);
                       return false;
                   }
                });
                //break;
            }
        }
        var days = date.getDaysInMonth();
        var firstOfMonth = date.getFirstDateOfMonth();
        var startingPos = firstOfMonth.getDay()-this.startDay;

        if(startingPos <= this.startDay){
            startingPos += 7;
        }

        var pm = date.add("mo", -1);
        var prevStart = pm.getDaysInMonth()-startingPos;

        var cells1 = this.cells1.elements;
        var textEls1 = this.textNodes1;
        days += startingPos;

        // convert everything to numbers so it's fast
        var day = 86400000;
        var d = (new Date(pm.getFullYear(), pm.getMonth(), prevStart)).clearTime();
        var today = new Date().clearTime().getTime();
        var sel = date.clearTime().getTime();
        var min = this.minDate ? this.minDate.clearTime() : Number.NEGATIVE_INFINITY;
        var max = this.maxDate ? this.maxDate.clearTime() : Number.POSITIVE_INFINITY;
        var ddMatch = this.disabledDatesRE;
        var ddText = this.disabledDatesText;
        var ddays = this.disabledDays ? this.disabledDays.join("") : false;
        var ddaysText = this.disabledDaysText;
        var format = this.format;

        if(this.showToday){
            var td = new Date().clearTime();
            var disable = (td < min || td > max ||
                (ddMatch && format && ddMatch.test(td.dateFormat(format))) ||
                (ddays && ddays.indexOf(td.getDay()) != -1));

            this.todayBtn1.setDisabled(disable);
            this.todayKeyListener[disable ? 'disable' : 'enable']();
        }

        var setCellClass1 = function(cal, cell){
            cell.title = "";
            var t = d.getTime();
            cell.firstChild.dateValue = t;
            if(t == today){
                cell.className += " x-date-today";
                cell.title = cal.todayText;
            }
            if(t == sel){
                cell.className += " x-date-selected";
                setTimeout(function(){
                    try{cell.firstChild.focus();}catch(e){}
                }, 50);
            }
            // disabling
            if(t < min) {
                cell.className = " x-date-disabled";
                cell.title = cal.minText;
                return;
            }
            if(t > max) {
                cell.className = " x-date-disabled";
                cell.title = cal.maxText;
                return;
            }
            if(ddays){
                if(ddays.indexOf(d.getDay()) != -1){
                    cell.title = ddaysText;
                    cell.className = " x-date-disabled";
                }
            }
            if(ddMatch && format){
                var fvalue = d.dateFormat(format);
                if(ddMatch.test(fvalue)){
                    cell.title = ddText.replace("%0", fvalue);
                    cell.className = " x-date-disabled";
                }
            }
        };

        var i = 0;
        for(; i < startingPos; i++) {
            textEls1[i].innerHTML = (++prevStart);
			var now = d.getDate();
			d.setDate(d.getDate()+1);
			if (now == d.getDate()) {
				d.setDate(d.getDate()+1);
				d.clearTime();
			}
            cells1[i].className = "x-date-prevday";
            setCellClass1(this, cells1[i]);
        }
        for(; i < days; i++){
            var intDay = i - startingPos + 1;
            textEls1[i].innerHTML = (intDay);
			var now = d.getDate();
			d.setDate(d.getDate()+1);
			if (now == d.getDate()) {
				d.setDate(d.getDate()+1);
				d.clearTime();
			}
            cells1[i].className = "x-date-active";
            setCellClass1(this, cells1[i]);
        }
        var extraDays = 0;
        for(; i < 42; i++) {
             textEls1[i].innerHTML = (++extraDays);
			var now = d.getDate();
			d.setDate(d.getDate()+1);
			if (now == d.getDate()) {
				d.setDate(d.getDate()+1);
				d.clearTime();
			}
             cells1[i].className = "x-date-nextday";
             setCellClass1(this, cells1[i]);
        }

        this.mbtn1.setText(this.monthNames[date.getMonth()] + " " + date.getFullYear());

        date=this.value2;
        var vd2 = this.activeDate2;
        this.activeDate2 = date;
        if(!forceRefresh && vd2 && this.el){
            var t = date.getTime();

            if(vd2.getMonth() == date.getMonth() && vd2.getFullYear() == date.getFullYear()){
                this.cells2.removeClass("x-date-selected");
                this.cells2.each(function(c){
                   if(c.dom.firstChild.dateValue == t){
                       c.addClass("x-date-selected");
                       setTimeout(function(){
                            try{c.dom.firstChild.focus();}catch(e){}
                       }, 50);
                       return false;
                   }
                });
                return;
            }
        }
        var days = date.getDaysInMonth();
        var firstOfMonth = date.getFirstDateOfMonth();
        var startingPos = firstOfMonth.getDay()-this.startDay;

        if(startingPos <= this.startDay){
            startingPos += 7;
        }

        var pm = date.add("mo", -1);
        var prevStart = pm.getDaysInMonth()-startingPos;

        var cells2 = this.cells2.elements;
        var textEls2 = this.textNodes2;
        days += startingPos;

        // convert everything to numbers so it's fast
        var day2 = 86400000;
        var d2 = (new Date(pm.getFullYear(), pm.getMonth(), prevStart)).clearTime();
        var today2 = new Date().clearTime().getTime();
        var sel2 = date.clearTime().getTime();
        var min2 = this.minDate ? this.minDate.clearTime() : Number.NEGATIVE_INFINITY;
        var max2 = this.maxDate ? this.maxDate.clearTime() : Number.POSITIVE_INFINITY;
        var ddMatch2 = this.disabledDatesRE;
        var ddText2 = this.disabledDatesText;
        var ddays2 = this.disabledDays ? this.disabledDays.join("") : false;
        var ddaysText2 = this.disabledDaysText;
        var format2 = this.format;

        if(this.showToday){
            var td = new Date().clearTime();
            var disable = (td < min2 || td > max2 ||
                (ddMatch2 && format2 && ddMatch.test(td.dateFormat(format2))) ||
                (ddays2 && ddays2.indexOf(td.getDay()) != -1));

            this.todayBtn2.setDisabled(disable);
            this.todayKeyListener[disable ? 'disable' : 'enable']();
        }

        var setCellClass2 = function(cal, cell){
            cell.title = "";
            var t = d2.getTime();
            cell.firstChild.dateValue = t;
            if(t == today){
                cell.className += " x-date-today";
                cell.title = cal.todayText;
            }
            if(t == sel){
                cell.className += " x-date-selected";
                setTimeout(function(){
                    try{cell.firstChild.focus();}catch(e){}
                }, 50);
            }
            // disabling
            if(t < min2) {
                cell.className = " x-date-disabled";
                cell.title = cal.minText;
                return;
            }
            if(t > max2) {
                cell.className = " x-date-disabled";
                cell.title = cal.maxText;
                return;
            }
            if(ddays2){
                if(ddays2.indexOf(d.getDay()) != -1){
                    cell.title = ddaysText;
                    cell.className = " x-date-disabled";
                }
            }
            if(ddMatch2 && format2){
                var fvalue = d2.dateFormat(format2);
                if(ddMatch2.test(fvalue)){
                    cell.title = ddText.replace("%0", fvalue);
                    cell.className = " x-date-disabled";
                }
            }
        };

        var i = 0;
        for(; i < startingPos; i++) {
            textEls2[i].innerHTML = (++prevStart);
			var now = d2.getDate();
			d2.setDate(d2.getDate()+1);
			if (now == d2.getDate()) {
				d2.setDate(d2.getDate()+1);
				d2.clearTime();
			}
            cells2[i].className = "x-date-prevday";
            setCellClass2(this, cells2[i]);
        }
        for(; i < days; i++){
            var intDay = i - startingPos + 1;
            textEls2[i].innerHTML = (intDay);
			var now = d2.getDate();
			d2.setDate(d2.getDate()+1);
			if (now == d2.getDate()) {
				d2.setDate(d2.getDate()+1);
				d2.clearTime();
			}
            cells2[i].className = "x-date-active";
            setCellClass2(this, cells2[i]);
        }
        var extraDays = 0;
        for(; i < 42; i++) {
             textEls2[i].innerHTML = (++extraDays);
			var now = d2.getDate();
			d2.setDate(d2.getDate()+1);
			if (now == d2.getDate()) {
				d2.setDate(d2.getDate()+1);
				d2.clearTime();
			}
             cells2[i].className = "x-date-nextday";
             setCellClass2(this, cells2[i]);
        }

        this.mbtn2.setText(this.monthNames[date.getMonth()] + " " + date.getFullYear());

        if(!this.internalRender){
            var main = this.el.dom.firstChild;
            var w = main.offsetWidth;
            this.el.setWidth(w + this.el.getBorderWidth("lr"));
            Ext.fly(main).setWidth(w);
            this.internalRender = true;
            // opera does not respect the auto grow header center column
            // then, after it gets a width opera refuses to recalculate
            // without a second pass
            if(Ext.isOpera && !this.secondPass){
                main.rows[0].cells1[1].style.width = (w - (main.rows[0].cells1[0].offsetWidth+main.rows[0].cells1[2].offsetWidth)) + "px";
                this.secondPass = true;
                this.update.defer(10, this, [date]);
            }
        }
    },

    // private
    beforeDestroy : function() {
        if(this.rendered){
            Ext.destroy(
                this.leftClickRpt1,
                this.leftClickRpt2,
                this.rightClickRpt1,
                this.rightClickRpt2,
                this.monthPicker1,
                this.monthPicker2,
                this.eventEl,
                this.mbtn1,
                this.mbtn2,
                this.todayBtn1,
                this.todayBtn2,
                this.applyBtn
            );
        }
    }


});
Ext.reg('daterangepickeradvanced', Ext.DateRangePickerAdvanced);

Ext.form.DateRangeFieldAdvanced = Ext.extend(Ext.form.TriggerField,  {
	mode: 'twodays',

    altFormats : "dmy|dm|d|d/m/Y|j/n/Y|j/n/y|j/m/y|d/n/y|j/m/Y|d/n/Y|d-m-y|d-m-Y|d/m|d-m|dmY|Y-m-d",

    disabledDaysText : "Disabled",

    disabledDatesText : "Disabled",

    minText : "The date in this field must be equal to or after {0}",

    maxText : "The date in this field must be equal to or before {0}",

    invalidText : "{0} is not a valid date - it must be in the format {1}",

    triggerClass : 'x-form-date-trigger',

    showToday : true,

    format : "d.m.Y",

    enableKeyEvents: true,

    validationEvent: 'blur',

    // private
    defaultAutoCreate : {tag: "input", type: "text", size: "10", autocomplete: "off"},

    initComponent : function(){
        Ext.form.DateRangeFieldAdvanced.superclass.initComponent.call(this);

        this.addEvents(

            'select'
        );

        if(typeof this.minValue == "string"){
            this.minValue = this.parseDate(this.minValue);
        }
        if(typeof this.maxValue == "string"){
            this.maxValue = this.parseDate(this.maxValue);
        }
        this.disabledDatesRE = null;
        this.initDisabledDays();
    },

    // private
    initDisabledDays : function(){
        if(this.disabledDates){
            var dd = this.disabledDates;
            var re = "(?:";
            for(var i = 0; i < dd.length; i++){
                re += dd[i];
                if(i != dd.length-1) re += "|";
            }
            this.disabledDatesRE = new RegExp(re + ")");
        }
    },

    setDisabledDates : function(dd){
        this.disabledDates = dd;
        this.initDisabledDays();
        if(this.menu){
            this.menu.picker.setDisabledDates(this.disabledDatesRE);
        }
    },


    setDisabledDays : function(dd){
        this.disabledDays = dd;
        if(this.menu){
            this.menu.picker.setDisabledDays(dd);
        }
    },


    setMinValue : function(dt){
        this.minValue = (typeof dt == "string" ? this.parseDate(dt) : dt);
        if(this.menu){
            this.menu.picker.setMinDate(this.minValue);
        }
    },


    setMaxValue : function(dt){
        this.maxValue = (typeof dt == "string" ? this.parseDate(dt) : dt);
        if(this.menu){
            this.menu.picker.setMaxDate(this.maxValue);
        }
    },

    // private
    validateValue : function(value){
        var ar = value.split(' - ');
        if (ar[0]=='__.__.____' && ar[1]=='__.__.____') {
            return true;
        }

        if (0 == value.length)
        {
            if (this.allowBlank) {
                return true;
            }
            else {
                this.markInvalid("Поле не может быть пустым");
                return false;
           }
        }

        ar[0] = this.formatDate(ar[0]);
        d1=this.parseDate(ar[0]);
        if(!d1 && ar[0]!='__.__.____'){
            this.markInvalid(String.format("Первая дата введена неправильно", ar[0], this.format));
            return false;
        }

        ar[1] = this.formatDate(ar[1]);
        d2=this.parseDate(ar[1]);
        if(!d2 && ar[1]!='__.__.____'){
            this.markInvalid(String.format("Вторая дата введена неправильно", ar[1], this.format));
            return false;
        }
        if (d1>d2) {
            this.markInvalid(langs('Дата начала должна быть меньше даты конца'));
            return false;
        }
        return true;
    },

    // private
    // Provides logic to override the default TriggerField.validateBlur which just returns true
    validateBlur : function(){
        return !this.menu || !this.menu.isVisible();
    },


    getValue : function(){
        return this.parseDate(Ext.form.DateField.superclass.getValue.call(this)) || "";
    },

    getValue1 : function(){
        if (this.value) {
            var ar = this.value.split(' - ');
            return this.parseDate(ar[0]) || "";
        }
        else
            return "";
    },

    getValue2 : function(){
        if (this.value) {
            var ar = this.value.split(' - ');
            return this.parseDate(ar[1]) || "";
        }
        else
            return "";
    },

    setValue : function(date){
		if (!date) {
            Ext.form.DateField.superclass.setValue.call(this, null);
            return;
        }
    		
    	var ar = date.split(' - ');
        if (ar[0]!=undefined && ar[0]!='' ) {
            ar[0] = ar[0].replace(/\./g,'');
			ar[0] = ar[0].replace(/\_/g,'');
            d1=this.parseDate(ar[0]);
            if(d1){
                ar[0] = this.formatDate(d1);
            }
            else {
                ar[0] = '__.__.____';
            }
        }
        else {
            ar[0] = '__.__.____';
        }

        if (ar[1]!=undefined && ar[1]!='' ) {
            ar[1] = ar[1].replace(/\./g,'');
			ar[1] = ar[1].replace(/\_/g,'');
            d2=this.parseDate(ar[1]);
            if(d2) {
                ar[1] = this.formatDate(d2);
            }
            else {
                ar[1] = '__.__.____';
            }
        }
        else {
            ar[1] = '__.__.____';
        }
        if ( ar[0]!='__.__.____' || ar[1]!='__.__.____' ) {
            date = ar[0]+' - '+ar[1];
        }
        else {
            date = '';
        }
        Ext.form.DateField.superclass.setValue.call(this, date);
    },

    // private
    parseDate : function(value){
        if(!value || Ext.isDate(value)){
            return value;
        }
        var v = Date.parseDate(value, this.format);
        if(!v && this.altFormats){
            if(!this.altFormatsArray){
                this.altFormatsArray = this.altFormats.split("|");
            }
            for(var i = 0, len = this.altFormatsArray.length; i < len && !v; i++){
                v = Date.parseDate(value, this.altFormatsArray[i]);
            }
        }
        return v;
    },

    // private
    onDestroy : function(){
        if(this.menu) {
            this.menu.destroy();
        }
        if(this.wrap){
            this.wrap.remove();
        }
        Ext.form.DateRangeFieldAdvanced.superclass.onDestroy.call(this);
    },

    // private
    formatDate : function(date){
        return Ext.isDate(date) ? date.dateFormat(this.format) : date;
    },

    // private
    menuListeners : {
        select: function(m, d){
            this.setValue(d);
            this.fireEvent('select', this, d);
        },
        show : function(){ // retain focus styling
            this.onFocus();
        },
        hide : function(){
            this.focus.defer(10, this);
            var ml = this.menuListeners;
            this.menu.un("select", ml.select,  this);
            this.menu.un("show", ml.show,  this);
            this.menu.un("hide", ml.hide,  this);
        }
    },


    // private
    // Implements the default empty TriggerField.onTriggerClick function to display the DatePicker
    onTriggerClick : function(){
		var cmp = this;
		
        var v = this.getRawValue();
        if(v){
            this.setValue(v);
        }

        if(this.disabled){
            return;
        }
        if(this.menu == null){
            this.menu = new Ext.menu.DateRangeMenuAdvanced();
        }
		
        Ext.apply(this.menu.picker,  {
			mode: this.mode,
            minDate : this.minValue,
            maxDate : this.maxValue,
            disabledDatesRE : this.disabledDatesRE,
            disabledDatesText : this.disabledDatesText,
            disabledDays : this.disabledDays,
            disabledDaysText : this.disabledDaysText,
            format : this.format,
            showToday : this.showToday,
            minText : String.format(this.minText, this.formatDate(this.minValue)),
            maxText : String.format(this.maxText, this.formatDate(this.maxValue))
        });
        this.menu.on(Ext.apply({}, this.menuListeners, {
            scope:this
        }));
        this.menu.picker.setValue(this.getValue() || new Date());
        this.menu.picker.setValue1(this.getValue1() || new Date());
        this.menu.picker.setValue2(this.getValue2() || new Date());
        this.menu.show(this.el, "tl-bl?");
    },

    // private
    beforeBlur : function(){
        var v = this.getRawValue();
        if(v){
            if (v=='__.__.____ - __.__.____') {
                this.setValue('');
            }
            else
                this.setValue(v);
        }
    },
    listeners: {
        'keydown': function( inp, e ) {
            if ( e.F4 == e.getKey() )
            {
                if ( e.browserEvent.stopPropagation )
                    e.browserEvent.stopPropagation();
                else
                    e.browserEvent.cancelBubble = true;
                if ( e.browserEvent.preventDefault )
                    e.browserEvent.preventDefault();
                else
                    e.browserEvent.returnValue = false;
                e.browserEvent.returnValue = false;
                e.returnValue = false;
                if ( Ext.isIE )
                {
                    e.browserEvent.keyCode = 0;
                    e.browserEvent.which = 0;
                }
                inp.onTriggerClick();
                inp.menu.picker.focus();
                return false;
            }
            if ( e.ENTER == e.getKey() )
            {
                var v = this.getRawValue();
                this.setValue(v);
            }
        },
        'keyup': function(inp, e) {
            if ( e.F4 == e.getKey() )
            {
                if ( e.browserEvent.stopPropagation )
                    e.browserEvent.stopPropagation();
                else
                    e.browserEvent.cancelBubble = true;
                if ( e.browserEvent.preventDefault )
                    e.browserEvent.preventDefault();
                else
                    e.browserEvent.returnValue = false;
                e.browserEvent.returnValue = false;
                e.returnValue = false;
                if ( Ext.isIE )
                {
                    e.browserEvent.keyCode = 0;
                    e.browserEvent.which = 0;
                }
                return false;
            }
        },
		'render': function( inp ) {
			inp.getEl().on('click', function() {
				inp.onTriggerClick();
				inp.menu.picker.focus();
			});
		},
        'select': function ( inp, date )
        {
            inp.focus(false, 500);
        }
    }
});
Ext.reg('daterangefieldadvanced', Ext.form.DateRangeFieldAdvanced);

Ext.menu.DateRangeItemAdvanced = function(config){
    Ext.menu.DateItem.superclass.constructor.call(this, new Ext.DateRangePickerAdvanced(config), config);

    this.picker = this.component;
    this.addEvents('select');

    this.picker.on("render", function(picker){
        picker.getEl().swallowEvent("click");
        picker.container.addClass("x-menu-date-item");
    });

    this.picker.on("select", this.onSelect, this);
};

Ext.extend(Ext.menu.DateRangeItemAdvanced, Ext.menu.Adapter, {
    // private
    onSelect : function(picker, date){
        this.fireEvent("select", this, date, picker);
        Ext.menu.DateRangeItemAdvanced.superclass.handleClick.call(this);
    }
});

Ext.menu.DateRangeMenuAdvanced = function(config){
    Ext.menu.DateRangeMenuAdvanced.superclass.constructor.call(this, config);
    this.plain = true;
    var di = new Ext.menu.DateRangeItemAdvanced(config);
    this.add(di);

    this.picker = di.picker;

    this.relayEvents(di, ["select"]);

    this.on('beforeshow', function(){
        if(this.picker){
			// прячем второй календарь, если режим выбора одного дня
			if (this.picker.mode && this.picker.mode == 'oneday') {
				this.picker.datePicker2.hide();
				this.picker.el.setWidth(220);
			} else {
				this.picker.datePicker2.show();
				this.picker.el.setWidth(440);
			}
			
            this.picker.hideMonthPicker1(true);
            this.picker.hideMonthPicker2(true);
        }
    }, this);
	
	this.on('beforehide', function(){
        if(this.picker){
            this.picker.applyClicked();
        }
    }, this);
};
Ext.extend(Ext.menu.DateRangeMenuAdvanced, Ext.menu.Menu, {
    cls:'x-date-menu',

    // private
    beforeDestroy : function() {
        this.picker.destroy();
    }
});
