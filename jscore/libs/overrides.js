/**
*  Переопределения и добавления новых функций в базовые классы.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      09.07.2009
*/

// количество одинаковых test_id
testIdCounts = {};


Ext.override(Ext.form.CheckboxGroup, {
	 afterRender : function(){
        Ext.form.CheckboxGroup.superclass.afterRender.call(this);
		if(this.singleValue){
			this.items.each(function(item){
				item.on('check', this.fireSingleChecked, this);
			}, this);
		}
		else{
			this.items.each(function(item){
				item.on('check', this.fireChecked, this);
			}, this);
		}
    },
	fireSingleChecked: function (checkedCmp) {
		this.items.each(function (item) {
          if (item.checked && item.id != checkedCmp.id) {
			item.checked = false;
            item.el.dom.checked = false;
            item.el.dom.defaultChecked = false;
            item.wrap['removeClass'](item.checkedCls);
			item.fireEvent('check', item, false);
          }
        });
        checkedCmp.el.dom.checked = checkedCmp.checked;
        checkedCmp.el.dom.defaultChecked = checkedCmp.checked;
        checkedCmp.wrap[checkedCmp.checked ? 'addClass' : 'removeClass'](checkedCmp.checkedCls);
       
        this.fireEvent('change', this, [checkedCmp]);
		this.validate();
    }
});

/**
 * оверрайд для даты
 */
Ext.apply(Date.prototype, {
	getFirstDateOfMonth : function() {
		var day = this.getDate();
		var date = new Date(this.getFullYear(), this.getMonth(), 1);
		if (date.getDate() != day) {
			// если улетели в другой день тогда пусть лучше будет час ночи текущего дня.
			date = new Date(this.getFullYear(), this.getMonth(), 1, 1);
		}
		return date;
	},
	clearTime : function(clone) {
		if (clone) {
			return this.clone().clearTime();
		}

		// get current date before clearing time
		var y = this.getFullYear();
		var m = this.getMonth();
		var d = this.getDate();

		// clear time
		this.setHours(0);
		this.setMinutes(0);
		this.setSeconds(0);
		this.setMilliseconds(0);

		if (this.getDate() != d) { // account for DST (i.e. day of month changed when setting hour = 0)
			// note: DST adjustments are assumed to occur in multiples of 1 hour (this is almost always the case)
			// refer to http://www.timeanddate.com/time/aboutdst.html for the (rare) exceptions to this rule

			// increment hour until cloned date == current date
			for (var hr = 1, c = this.add(Date.HOUR, hr); c.getDate() != d; hr++, c = this.add(Date.HOUR, hr));

			this.setDate(d);
			this.setMonth(m);
			this.setFullYear(y);
			this.setHours(c.getHours());
		}

		return this;
	}
});
Ext.apply(Date, {
	xf: function(format) {
		var args = Array.prototype.slice.call(arguments, 1);
		return format.replace(/\{(\d+)\}/g, function(m, i) {
			return args[i];
		});
	},
	createParser : function() {
		var code = [
			"var dt, y, m, d, h, i, s, ms, o, z, zz, u, v,",
			"def = Date.defaults,",
			"results = String(input).match(Date.parseRegexes[{0}]);", // either null, or an array of matched strings

			"if(results){",
			"{1}",

			"if(u != null){", // i.e. unix time is defined
			"v = new Date(u * 1000);", // give top priority to UNIX time
			"}else{",
			// create Date object representing midnight of the current day;
			// this will provide us with our date defaults
			// (note: clearTime() handles Daylight Saving Time automatically)
			"dt = (new Date()).clearTime();",

			// date calculations (note: these calculations create a dependency on Ext.num())
			"y = y >= 0? y : Ext.num(def.y, dt.getFullYear());",
			"m = m >= 0? m : Ext.num(def.m - 1, dt.getMonth());",
			"d = d >= 0? d : Ext.num(def.d, dt.getDate());",

			// time calculations (note: these calculations create a dependency on Ext.num())
			"h  = h || Ext.num(def.h, dt.getHours());",
			"i  = i || Ext.num(def.i, dt.getMinutes());",
			"s  = s || Ext.num(def.s, dt.getSeconds());",
			"ms = ms || Ext.num(def.ms, dt.getMilliseconds());",

			"mycodehere",

			"if(z >= 0 && y >= 0){",
			// both the year and zero-based day of year are defined and >= 0.
			// these 2 values alone provide sufficient info to create a full date object
			// create Date object representing January 1st for the given year
			"v = new Date(y, 0, 1, h, i, s, ms);",

			// then add day of year, checking for Date "rollover" if necessary
			"v = !strict? v : (strict === true && (z <= 364 || (v.isLeapYear() && z <= 365))? v.add(Date.DAY, z) : null);",
			"}else if(strict === true && !Date.isValid(y, m + 1, d, h, i, s, ms)){", // check for Date "rollover"
			"v = null;", // invalid date, so return null
			"}else{",
			// plain old Date object
			"v = new Date(y, m, d, h, i, s, ms);",
			"}",
			"}",
			"}",

			"if(v){",
			// favour UTC offset over GMT offset
			"if(zz != null){",
			// reset to UTC, then add offset
			"v = v.add(Date.SECOND, -v.getTimezoneOffset() * 60 - zz);",
			"}else if(o){",
			// reset to GMT, then add offset
			"v = v.add(Date.MINUTE, -v.getTimezoneOffset() + (sn == '+'? -1 : 1) * (hr * 60 + mn));",
			"}",
			"}",

			"return v;"
		].join('\n');

		return function(format) {
			var regexNum = Date.parseRegexes.length,
				currentGroup = 1,
				calc = [],
				regex = [],
				special = false,
				ch = "";

			for (var i = 0; i < format.length; ++i) {
				ch = format.charAt(i);
				if (!special && ch == "\\") {
					special = true;
				} else if (special) {
					special = false;
					regex.push(String.escape(ch));
				} else {
					var obj = Date.formatCodeToRegex(ch, currentGroup);
					currentGroup += obj.g;
					regex.push(obj.s);
					if (obj.g && obj.c) {
						calc.push(obj.c);
					}
				}
			}

			var newcode = code;
			if (format.indexOf('h') == -1) { // если в формате не учитываются часы и дата в 00:00 часов - другой день, то добавляем 1 час.
				newcode = code.replace(/mycodehere/g,'if(h==0 && new Date(y,m,d).getDate() != d) {h = 1;}');
			} else {
				newcode = code.replace(/mycodehere/g,'');
			}

			Date.parseRegexes[regexNum] = new RegExp("^" + regex.join('') + "$", "i");
			Date.parseFunctions[format] = new Function("input", "strict", Date.xf(newcode, regexNum, calc.join('')));
		}
	}()
});

// add method setAllowedDates
Ext.override(Ext.form.DateField, {
	allowedDatesRE: null,
	/**
	 * @method onTriggerClick
	 * @hide
	 */
	// private
	// Implements the default empty TriggerField.onTriggerClick function to display the DatePicker
	onTriggerClick : function(){
		if(this.disabled){
			return;
		}
		if(this.menu == null){
			this.menu = new Ext.menu.DateMenu();
		}
		Ext.apply(this.menu.picker,  {
			minDate : this.minValue,
			maxDate : this.maxValue,
			allowedDatesRE : this.allowedDatesRE,
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
		this.menu.show(this.el, "tl-bl?");
		// Перерисуем
		this.menu.picker.update(this.menu.picker.activeDate, true);
	},
    // private
    validateValue : function(value){
        value = this.formatDate(value);
        if(!Ext.form.DateField.superclass.validateValue.call(this, value)){
            return false;
        }
        if(value.length < 1){ // if it's blank and textfield didn't flag it then it's valid
             return true;
        }
        var svalue = value;
        value = this.parseDate(value);
        if(!value){
            this.markInvalid(String.format(this.invalidText, svalue, this.format));
            return false;
        }
        var time = value.getTime();
        if(this.minValue && time < this.minValue.getTime()){
            this.markInvalid(String.format(this.minText, this.formatDate(this.minValue)));
            return false;
        }
        if(this.maxValue && time > this.maxValue.getTime()){
            this.markInvalid(String.format(this.maxText, this.formatDate(this.maxValue)));
            return false;
        }
        if(this.disabledDays){
            var day = value.getDay();
            for(var i = 0; i < this.disabledDays.length; i++) {
            	if(day === this.disabledDays[i]){
            	    this.markInvalid(this.disabledDaysText);
                    return false;
            	}
            }
        }
        var fvalue = this.formatDate(value);
        if(this.disabledDatesRE && this.disabledDatesRE.test(fvalue)){
            this.markInvalid(String.format(this.disabledDatesText, fvalue));
            return false;
        }
		if(this.allowedDatesRE && !this.allowedDatesRE.test(fvalue)){
			this.markInvalid(String.format(this.disabledDatesText, fvalue));
			return false;
		}
        return true;
    },
	setAllowedDates: function (value){
		this.allowedDatesRE = null;
		this.allowedDates = value;
		if (value){
			var re = "(?:";
			for(var i = 0; i < value.length; i++){
				re += value[i];
				if(i != value.length-1) re += "|";
			}
			this.allowedDatesRE = new RegExp(re + ")");
		}
		if (this.menu){
			this.menu.picker.setAllowedDatesRE(this.allowedDatesRE);
		}
		this.validate();
	}
});
// add method setAllowedDatesRE
Ext.override(Ext.DatePicker, {
	allowedDatesRE: null,
	setAllowedDatesRE: function (value){
		this.allowedDatesRE = value;
		this.disabledDatesRE = null;
		this.update(this.value, true);
	}
});
Ext.override(Ext.DatePicker, {
	update : function(date, forceRefresh){
		var vd = this.activeDate, vis = this.isVisible();
		this.activeDate = date;
		if(!forceRefresh && vd && this.el){
			var t = date.getTime();
			if(vd.getMonth() == date.getMonth() && vd.getFullYear() == date.getFullYear()){
				this.cells.removeClass("x-date-selected");
				this.cells.each(function(c){
					if(c.dom.firstChild.dateValue == t){
						c.addClass("x-date-selected");
						if(vis){
							setTimeout(function(){
								try{c.dom.firstChild.focus();}catch(e){}
							}, 50);
						}
						return false;
					}
				});
				return;
			}
		}
		var days = date.getDaysInMonth();
		var firstOfMonth = date.getFirstDateOfMonth();
		var startingPos = firstOfMonth.getDay()-this.startDay;

		if(startingPos < 0){
			startingPos += 7;
		}

		var pm = date.add("mo", -1);
		var prevStart = pm.getDaysInMonth()-startingPos;

		var cells = this.cells.elements;
		var textEls = this.textNodes;
		days += startingPos;

		// convert everything to numbers so it's fast
		var day = 86400000;
		var d = (new Date(pm.getFullYear(), pm.getMonth(), prevStart)).clearTime();
		var today = new Date().clearTime().getTime();
		var sel = date.clearTime().getTime();
		var min = this.minDate ? this.minDate.clearTime() : Number.NEGATIVE_INFINITY;
		var max = this.maxDate ? this.maxDate.clearTime() : Number.POSITIVE_INFINITY;
		var ddMatch = this.disabledDatesRE;
		var adMatch = this.allowedDatesRE;
		var ddText = this.disabledDatesText;
		var ddays = this.disabledDays ? this.disabledDays.join("") : false;
		var ddaysText = this.disabledDaysText;
		var format = this.format;

		if(this.showToday){
			var td = new Date().clearTime();
			var disable = (td < min || td > max ||
			(ddMatch && format && ddMatch.test(td.dateFormat(format))) ||
			(adMatch && format && !adMatch.test(td.dateFormat(format))) || 
			(ddays && ddays.indexOf(td.getDay()) != -1));

			if(!this.disabled){
				this.todayBtn.setDisabled(disable);
				this.todayKeyListener[disable ? 'disable' : 'enable']();
			}
		}

		var setCellClass = function(cal, cell){
			cell.title = "";
			var t = d.getTime();
			cell.firstChild.dateValue = t;
			if(t == today){
				cell.className += " x-date-today";
				cell.title = cal.todayText;
			}
			if(t == sel){
				cell.className += " x-date-selected";
				if(vis){
					setTimeout(function(){
						try{cell.firstChild.focus();}catch(e){}
					}, 50);
				}
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
			if(adMatch && format){
				var fvalue = d.dateFormat(format);
				if(!adMatch.test(fvalue)){
					cell.title = ddText.replace("%0", fvalue);
					cell.className = " x-date-disabled";
				}
			}
		};

		var i = 0;
		for(; i < startingPos; i++) {
			textEls[i].innerHTML = (++prevStart);
			var now = d.getDate();
			d.setDate(d.getDate()+1);
			if (now == d.getDate()) {
				d.setDate(d.getDate()+1);
				d.clearTime();
			}
			cells[i].className = "x-date-prevday";
			setCellClass(this, cells[i]);
		}
		for(; i < days; i++){
			var intDay = i - startingPos + 1;
			textEls[i].innerHTML = (intDay);
			var now = d.getDate();
			d.setDate(d.getDate()+1);
			if (now == d.getDate()) {
				d.setDate(d.getDate()+1);
				d.clearTime();
			}
			cells[i].className = "x-date-active";
			setCellClass(this, cells[i]);
		}
		var extraDays = 0;
		for(; i < 42; i++) {
			textEls[i].innerHTML = (++extraDays);
			var now = d.getDate();
			d.setDate(d.getDate()+1);
			if (now == d.getDate()) {
				d.setDate(d.getDate()+1);
				d.clearTime();
			}
			cells[i].className = "x-date-nextday";
			setCellClass(this, cells[i]);
		}

		this.mbtn.setText(this.monthNames[date.getMonth()] + " " + date.getFullYear());

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
				main.rows[0].cells[1].style.width = (w - (main.rows[0].cells[0].offsetWidth+main.rows[0].cells[2].offsetWidth)) + "px";
				this.secondPass = true;
				this.update.defer(10, this, [date]);
			}
		}
	}
});

Ext.override(Ext.Editor, {
		startEdit : function(el, value){
			if(this.editing){
				return false; // чтобы Editor не появлялся пока есть предыдущий
			}
			this.boundEl = Ext.get(el);
			var v = value !== undefined ? value : this.boundEl.dom.innerHTML;
			if(!this.rendered){
				this.render(this.parentEl || document.body);
			}
			if(this.fireEvent("beforestartedit", this, this.boundEl, v) === false){
				return;
			}
			this.startValue = v;
			this.field.setValue(v);
			this.doAutoSize();
			this.el.alignTo(this.boundEl, this.alignment);
			this.editing = true;
			this.show();
		}
});

/**
 * Overrides the Ext.TabPanel to add .setTabTitle() function
 */
Ext.override(Ext.TabPanel, {
	/**
	* Set the title of a specific tab
	*/
	setTabTitle: function( tabNo, newTitle ) {
	// make sure we have a number and tab exists
		if( tabNo>=0 && !Ext.isEmpty( this.getTabEl(tabNo))) {
			var tabEl = this.getTabEl(tabNo); // walk down dom, update title span
			Ext.fly(tabEl).child('span.x-tab-strip-text', true).innerHTML = newTitle;
		}
	},
	setTabStripItemTitle: function (item, title) {
		item = this.getComponent(item);
		item.setTitle(title);
	},
	initTab : function(item, index){
		var before = this.strip.dom.childNodes[index];
		var cls = item.closable ? 'x-tab-strip-closable' : '';
		if(item.disabled){
			cls += ' x-item-disabled';
		}
		if(item.iconCls){
			cls += ' x-tab-with-icon';
		}
		if(item.tabCls){
			cls += ' ' + item.tabCls;
		}

		var p = {
			id: this.id + this.idDelimiter + item.getItemId(),
			text: item.title,
			cls: cls,
			iconCls: item.iconCls || ''
		};
		var el = before ?
			this.itemTpl.insertBefore(before, p) :
			this.itemTpl.append(this.strip, p);

		Ext.fly(el).addClassOnOver('x-tab-strip-over');

		if(item.tabTip){
			Ext.fly(el).child('span.x-tab-strip-text', true).qtip = item.tabTip;
		}
		item.tabEl = el;

		item.on('disable', this.onItemDisabled, this);
		item.on('enable', this.onItemEnabled, this);
		item.on('titlechange', this.onItemTitleChanged, this);
		item.on('iconchange', this.onItemIconChanged, this);
		item.on('beforeshow', this.onBeforeShowItem, this);

		if (TEST_ID_ENABLED) {
			var parentTestId = '';
			if (el && Ext.get(el)) {
				var elem = Ext.get(el);
				if (elem && elem.up) {
					var parentTest = elem.up('[test_id]');
					if (parentTest) {
						parentTestId = parentTest.getAttribute('test_id') + '_';
					}
				}
			}

			var xtype = 'ti';
			var name = '';
			if (item.name) {
				name = item.name;
			} else if (item.hiddenName) {
				name = item.hiddenName;
			} else if (item.id) {
				name = item.id;
			}

			var test_id = parentTestId + xtype;
			if (name.indexOf('ext-') < 0) {
				test_id = test_id + '_' + name;
			}
			test_id = test_id.replace(/<\/?[^>]+>/gi, '');
			test_id = test_id.replace(/\./gi, '');
			el.setAttribute('test_id', test_id);
		}

		if(this.qtip) {
			var qt = this.qtip;
			Ext.QuickTips.register({
				target:  this,
				title: '',
				text: qt,
				enabled: true,
				showDelay: 20
			});
		}
	}
});
 
// Революция в борьбе с недобросовестными программистами, вместо console.log надо использовать log.
if (typeof console!=="object" ) {
	console = new Object({
		log: function() {},
		group: function() {},
		groupEnd: function() {},
		warn: function() {},
		debug: function() {},
		info: function() {},
		error: function() {},
		timeStamp: function() {}
	});
}

// Возможность сортировки Store по нескольким полям
Ext.override(Ext.data.Store, {
    sortData : function(f, direction){
        direction = direction || 'ASC';
        if (!f) {
            this.sortInfo = {field: f, direction: direction};
            this.sortToggle[f] = direction;
            return;
        }
        var st = this.fields.get(f).sortType;
     	var multipleSortInfo = this.fields.get(f).multipleSortInfo;
     	var caseInsensitively = this.fields.get(f).caseInsensitively;
		if (typeof direction == 'object') {
			multipleSortInfo = direction;
			direction = 'ASC';
		}
        var fn = function(r1, r2){
            var v1 = st(r1.data[f]), v2 = st(r2.data[f]);
			if (caseInsensitively !== undefined && v1.toLowerCase) {
				v1 = v1.toLowerCase();
				v2 = v2.toLowerCase();
			}
            var ret = v1 > v2 ? 1 : (v1 < v2 ? -1 : 0);
			if (multipleSortInfo !== undefined) {
				ret = 0;
			}
			for (i = 0 ; (multipleSortInfo !== undefined && ret == 0 && i < multipleSortInfo.length); i++) {
				var x1 = r1.data[multipleSortInfo[i].field], x2 = r2.data[multipleSortInfo[i].field];
				var dir = (direction != multipleSortInfo[i].direction) ? direction.toggle("ASC", "DESC") : direction;
				   ret = (x1 > x2) ? 1 : ((x1 < x2) ? -1 : 0);
				   if (dir == 'DESC') ret = -ret;
			};
            return ret;
        };
        this.data.sort(direction, fn);
        if(this.snapshot && this.snapshot != this.data){
            this.snapshot.sort(direction, fn);
        }
    }   
});

// Альтернативное представление отключенных полей
Ext.override(Ext.Component, {
	isComponent: true,
	disabledClass: 'field-disabled',
	onDisable : function(){
        this.getActionEl().addClass(this.disabledClass);
		/* Сие неправильно работает в случае контекстного меню
		if (this.getActionEl().findParent('div', 0, true) != undefined)
			this.getActionEl().findParent('div', 0, true).addClass(this.disabledClass);
		*/
        this.el.dom.disabled = true;
    },
	disable : function(){
        if(this.rendered){
            this.onDisable();
        }
		if(!this.disabled){ // иначе можем уйти в бесконечный цикл с this.fireEvent("disable", this)
			this.disabled = true;
			this.fireEvent("disable", this);
		}
		// запускаем валидэйт чтобы снять зелёную подсветку, если поле было отмечено как обязательное
		if (this.rendered && typeof this.validate == 'function') {
			this.validate();
		}
        return this;
    },
	enable : function(){
        if(this.rendered){
            this.onEnable();
        }
        this.disabled = false;
        this.fireEvent("enable", this);
		// запускаем валидэйт чтобы снять зелёную подсветку, если поле было отмечено как обязательное
		if (this.rendered && typeof this.validate == 'function') {
			this.validate();
		}
        return this;
    },
	afterRender: function() {
		if(this.testId) {
			this.addClass(this.testId);
		}
	}
});

/*
 * Fix для IE
 */
Ext.override(Ext.TabPanel, {
	autoSizeTabs : function(){
		// для автоматической ширины колонок дальнейший код ломается, а в IE взрывается, поэтому return'имся. возможно что то другое надо делать.
		if (this.tabWidth == 'auto') {
			return false;
		}
		
        var count = this.items.length;
        var ce = this.tabPosition != 'bottom' ? 'header' : 'footer';
        var ow = this[ce].dom.offsetWidth;
        var aw = this[ce].dom.clientWidth;

        if(!this.resizeTabs || count < 1 || !aw){ // !aw for display:none
            return;
        }

        var each = Math.max(Math.min(Math.floor((aw-4) / count) - this.tabMargin, this.tabWidth), this.minTabWidth); // -4 for float errors in IE
        this.lastTabWidth = each;
        var lis = this.stripWrap.dom.getElementsByTagName('li');
        for(var i = 0, len = lis.length-1; i < len; i++) { // -1 for the "edge" li
            var li = lis[i];
            var inner = li.childNodes[1].firstChild.firstChild;
            var tw = li.offsetWidth;
            var iw = inner.offsetWidth;
            inner.style.width = (each - (tw-iw)) + 'px';
        }
    }
});

/*
 * Fix прокрутки табов
 */
Ext.override(Ext.TabPanel, {
    // private
    getScrollIncrement : function(){
        var inc = this.scrollIncrement || (this.resizeTabs ? this.lastTabWidth+2 : 100);
		if (isNaN(inc)) {
			inc = 100;
		}
		return inc;
    }
});

/*
 * Расширение numberfield для возможности ввода чисел начинающихся с нуля 
 * при allowLeadingZeroes = true
 */
Ext.override(Ext.form.NumberField, {
	allowLeadingZeroes : false,
    setValue : function(v){
    	v = typeof v == 'number' ? v : this.parseFloatZeroes(String(v).replace(this.decimalSeparator, "."));
        v = isNaN(v) ? '' : String(v).replace(".", this.decimalSeparator);
        Ext.form.NumberField.superclass.setValue.call(this, v);
    },
    // private
    parseValue : function(value){
        value = this.parseFloatZeroes(String(value).replace(this.decimalSeparator, "."));      
        return isNaN(value) ? '' : value;
    },
    // private
    parseFloatZeroes : function(value){
        if(this.allowLeadingZeroes){
            var zeroes = "";
            while(value.substr(0, 1) == "0"){
                zeroes += "0";
                value = value.substring(1, value.length);
            }
            return zeroes + parseFloat(value);
        } else {
            return parseFloat(value);
        }        
    },
    // private
    parseFloatZeroesToFix : function(value){
        if(this.allowLeadingZeroes){
            var zeroes = "";
            while(value.substr(0, 1) == "0"){
                zeroes += "0";
                value = value.substring(1, value.length);
            }
            return zeroes + parseFloat(value).toFixed(this.decimalPrecision);
        } else {
			// toFixed работает коряво
			// https://redmine.swan.perm.ru/issues/31052
			var digit = Math.round(parseFloat(value) * Math.pow(10, this.decimalPrecision)) / Math.pow(10, this.decimalPrecision);
            return digit.toFixed(this.decimalPrecision);
        }        
    },
    // private
    fixPrecision : function(value){
        var nan = isNaN(value);
        if(!this.allowDecimals || this.decimalPrecision == -1 || nan || !value){
           return nan ? '' : value;
        }
        return this.parseFloatZeroes(this.parseFloatZeroesToFix(value));
    }
});

// метод для установки ограничения на обязательность поля
Ext.override(Ext.form.Field, {
	setAllowBlank: function(allowBlank) {
		this.allowBlank = allowBlank;
		if (this.rendered && typeof this.validate == 'function') {
			this.validate();
		}
	},
	/** Функция ищет (и если находит, то возвращает) родителя с типом form
	 * функция може принимать в качестве аргумента объект, от которого искать
	 */
	findForm: function(component) {
		component = component || this;
		var yep = false;
		while (!yep && component) {
			component = (typeof component==="object" && component['ownerCt'])?component['ownerCt']:null;
			if (component) {
				if (component.form) {
					yep = true;
				}
			} else {
				component = null;
			}
		}
		return component;
	},
	onRender : function(ct, position) {
		Ext.form.Field.superclass.onRender.call(this, ct, position);
		if(!this.el){
			var cfg = this.getAutoCreate();
			if(!cfg.name){
				cfg.name = this.name || this.id;
			}
			if(this.inputType){
				cfg.type = this.inputType;
			}
			this.el = ct.createChild(cfg, position);
		}
		var type = this.el.dom.type;
		if(type){
			if(type == 'password'){
				type = 'text';
			}
			this.el.addClass('x-form-'+type);
		}
		if(this.readOnly){
			this.el.dom.readOnly = true;
		}
		if(this.tabIndex !== undefined){
			this.el.dom.setAttribute('tabIndex', this.tabIndex);
		}

		this.el.addClass([this.fieldClass, this.cls]);

		if (TEST_ID_ENABLED) {
			var parentTestId = '';
			if (this.el.up) {
				var parentTest = this.el.up('[test_id]');
				if (parentTest) {
					parentTestId = parentTest.getAttribute('test_id') + '_';
				}
			}
			var xtype = this.xtype;
			var name = '';
			if (this.name) {
				name = this.name;
			} else if (this.hiddenName) {
				name = this.hiddenName;
			} else if (this.id) {
				name = this.id;
			}

			var test_id = parentTestId + xtype;
			if (name.indexOf('ext-') < 0) {
				test_id = test_id + '_' + name;
			}
			test_id = test_id.replace(/<\/?[^>]+>/gi, '');
			test_id = test_id.replace(/[\.\/]/gi, '');
			this.el.setAttribute('test_id', test_id);
		}

		if(this.qtip) {
			var qt = this.qtip;
			Ext.QuickTips.register({
				target:  this,
				title: '',
				text: qt,
				enabled: true,
				showDelay: 20
			});
		}
	},
    /**
     * Show the container including the label
     */
    showContainer: function() {
        // this.enable();
        this.show();

		if ( !Ext.isEmpty(this.getEl()) && !Ext.isEmpty(this.getEl().up('.x-form-item')) ) {
            this.getEl().up('.x-form-item').setDisplayed(true); // show entire container and children (including label if applicable)
        }


    },

    /**
     * Hide the container including the label
     */
    hideContainer: function() {
        // this.disable(); // for validation
        this.hide();

        if ( !Ext.isEmpty(this.getEl()) && !Ext.isEmpty(this.getEl().up('.x-form-item')) ) {
            this.getEl().up('.x-form-item').setDisplayed(false); // hide container and children (including label if applicable)
        }
    },

    /**
     * Hide / Show the container including the label
     * @param visible
     */
    setContainerVisible: function(visible) {
        if (this.rendered) {
            if (visible) {
                this.showContainer();
            } else {
                this.hideContainer();
            }
        }

        return this;
    }

});

Ext.override(Ext.form.TextField, {
    filterKeys : function(e){
        // special keys don't generate charCodes, so leave them alone
        if(e.ctrlKey || e.isSpecialKey() || (e.button !=45&&e.getCharCode() == e.DELETE)){
			return;
		}
        if(!this.maskRe.test(String.fromCharCode(e.getCharCode()))){
            e.stopEvent();
        }
    }
});



Ext.override(Ext.Panel, {
	/**
	*  Добавление кнопки эмулирующей пустое место, кнопка с названием '-'
	*/
	onRender : function(ct, position){
		Ext.Panel.superclass.onRender.call(this, ct, position);

		this.createClasses();

		if(this.el){ // existing markup
			this.el.addClass(this.baseCls);
			this.header = this.el.down('.'+this.headerCls);
			this.bwrap = this.el.down('.'+this.bwrapCls);
			var cp = this.bwrap ? this.bwrap : this.el;
			this.tbar = cp.down('.'+this.tbarCls);
			this.body = cp.down('.'+this.bodyCls);
			this.bbar = cp.down('.'+this.bbarCls);
			this.footer = cp.down('.'+this.footerCls);
			this.fromMarkup = true;
		}else{
			this.el = ct.createChild({
				id: this.id,
				cls: this.baseCls
			}, position);
		}
		var el = this.el, d = el.dom;

		if(this.cls){
			this.el.addClass(this.cls);
		}

		if(this.buttons){
			this.elements += ',footer';
		}

		// This block allows for maximum flexibility and performance when using existing markup

		// framing requires special markup
		if(this.frame){
			el.insertHtml('afterBegin', String.format(Ext.Element.boxMarkup, this.baseCls));

			this.createElement('header', d.firstChild.firstChild.firstChild);
			this.createElement('bwrap', d);

			// append the mid and bottom frame to the bwrap
			var bw = this.bwrap.dom;
			var ml = d.childNodes[1], bl = d.childNodes[2];
			bw.appendChild(ml);
			bw.appendChild(bl);

			var mc = bw.firstChild.firstChild.firstChild;
			this.createElement('tbar', mc);
			this.createElement('body', mc);
			this.createElement('bbar', mc);
			this.createElement('footer', bw.lastChild.firstChild.firstChild);

			if(!this.footer){
				this.bwrap.dom.lastChild.className += ' x-panel-nofooter';
			}
		}else{
			this.createElement('header', d);
			this.createElement('bwrap', d);

			// append the mid and bottom frame to the bwrap
			var bw = this.bwrap.dom;
			this.createElement('tbar', bw);
			this.createElement('body', bw);
			this.createElement('bbar', bw);
			this.createElement('footer', bw);

			if(!this.header){
				if(this.body){
					this.body.addClass(this.bodyCls + '-noheader');
				}
				if(this.tbar){
					this.tbar.addClass(this.tbarCls + '-noheader');
				}
			}
		}

		if(this.border === false){
			this.el.addClass(this.baseCls + '-noborder');
			this.body.addClass(this.bodyCls + '-noborder');
			if(this.header){
				this.header.addClass(this.headerCls + '-noborder');
			}
			if(this.footer){
				this.footer.addClass(this.footerCls + '-noborder');
			}
			if(this.tbar){
				this.tbar.addClass(this.tbarCls + '-noborder');
			}
			if(this.bbar){
				this.bbar.addClass(this.bbarCls + '-noborder');
			}
		}

		if(this.bodyBorder === false){
			this.body.addClass(this.bodyCls + '-noborder');
		}

		this.bwrap.enableDisplayMode('block');

		if(this.header){
			this.header.unselectable();

			// for tools, we need to wrap any existing header markup
			if(this.headerAsText){
				this.header.dom.innerHTML =
					'<span class="' + this.headerTextCls + '">'+this.header.dom.innerHTML+'</span>';

				if(this.iconCls){
					this.setIconClass(this.iconCls);
				}
			}
		}

		if(this.floating){
			this.makeFloating(this.floating);
		}

		if(this.collapsible){
			this.tools = this.tools ? this.tools.slice(0) : [];
			if(!this.hideCollapseTool){
				this.tools[this.collapseFirst?'unshift':'push']({
					id: 'toggle',
					handler : this.toggleCollapse,
					scope: this
				});
			}
			if(this.titleCollapse && this.header){
				this.header.on('click', this.toggleCollapse, this);
				this.header.setStyle('cursor', 'pointer');
			}
		}
		if(this.tools){
			var ts = this.tools;
			this.tools = {};
			this.addTool.apply(this, ts);
		}else{
			this.tools = {};
		}

		if(this.buttons && this.buttons.length > 0){
			// tables are required to maintain order and for correct IE layout
			var tb = this.footer.createChild({cls:'x-panel-btns-ct', cn: {
				cls:"x-panel-btns x-panel-btns-"+this.buttonAlign,
				html:'<table cellspacing="0"><tbody><tr></tr></tbody></table><div class="x-clear"></div>'
			}}, null, true);
			var tr = tb.getElementsByTagName('tr')[0];
			for(var i = 0, len = this.buttons.length; i < len; i++) {
				var b = this.buttons[i];
				var td = document.createElement('td');
				if (b.text!='-') {
					td.className = 'x-panel-btn-td';
					b.render(tr.appendChild(td));
				}
				else {
					td.style.width = "100%";
					tr.appendChild(td);
				}
			}
		}

		if(this.tbar && this.topToolbar){
			if(Ext.isArray(this.topToolbar)){
				this.topToolbar = new Ext.Toolbar(this.topToolbar);
			}
			this.topToolbar.render(this.tbar);
			this.topToolbar.ownerCt = this;
		}
		if(this.bbar && this.bottomToolbar){
			if(Ext.isArray(this.bottomToolbar)){
				this.bottomToolbar = new Ext.Toolbar(this.bottomToolbar);
			}
			this.bottomToolbar.render(this.bbar);
			this.bottomToolbar.ownerCt = this;
		}

		if (TEST_ID_ENABLED) {
			var parentTestId = '';
			if (this.el.up) {
				var parentTest = this.el.up('[test_id]');
				if (parentTest) {
					parentTestId = parentTest.getAttribute('test_id') + '_';
				}
			}
			var xtype = 'pnl';
			var name = '';
			if (this.title && typeof this.title == 'string' && this.title.length > 0) {
				name = swTranslite(this.title);

				var test_id = parentTestId + xtype;
				if (name.indexOf('ext-') < 0) {
					test_id = test_id + '_' + name;
				}
				test_id = test_id.replace(/<\/?[^>]+>/gi, '');
				test_id = test_id.replace(/[\.\/]/gi, '');
				this.el.setAttribute('test_id', test_id);
			}
		}
	}
});

Ext.override(Ext.form.FormPanel, {
	ownerWindow: null,
/**
 * Disable для всех полей формы
 */
	disableAllFields: function() {
		this.items.each(function(f) {
			if ( f.disable ) {
				f.disable();
			}
		});
	},

/**
*  Получение первого расово неверного элемента формы
*/
	getFirstInvalidEl: function() {
		var result = new Array();

		function elCheck(el, arr)
		{
			if ( el.items && typeof el.items.each != 'function' ) {
				log('azaza', el);
			}
			if ( el.items && typeof el.items.each == 'function' )
			{
				el.items.each(function(ff) {
					elCheck(ff, arr);
				});
			}
			else if (el.isValid && !el.isValid())
			{
				arr.push(el);
			}

			return arr;
		}

		this.items.each(function(f) {
			result = elCheck(f, result);
		});

		if ( result.length == 0 )
			result[0] = null;

		return result[0];
	},

	/**
	 * Получение ошибочно заполненных полей
	 */
	getInvalidFields: function(items, recnum) {
		var that = this;
		var fields = [];

		if (!recnum) {
			recnum = 1;
		} else {
			recnum++;
		}

		if (recnum > 8) { // защита от рекурсий
			return [];
		}

		items.each(function(el) {
			if ( el.items )
			{
				fields = fields.concat(that.getInvalidFields(el.items, recnum));
			}
			else if (el.isValid && !el.isValid())
			{
				fields.push(el);
			}
		});

		return fields;
	},

	/**
	 * Получение сообщения об ошибочно заполненных полях
	 */
	getInvalidFieldsMessage: function() {
		var fields = this.getInvalidFields(this.items);

		var fieldsString = "";
		fields.forEach(function (field) {
			if (field.fieldLabel) {
				if (fieldsString.length > 0) {
					fieldsString += ', ';
				}
				fieldsString += field.fieldLabel;
			} else if (field.boxLabel) {
				if (fieldsString.length > 0) {
					fieldsString += ', ';
				}
				fieldsString += field.boxLabel;
			}
		});

		if (fieldsString.length > 0) {
			return 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля: ' + fieldsString + '.'
		} else {
			return ERR_INVFIELDS_MSG;
		}
	},
	
	/**
	 * Возвращает все элементы формы не прошедшие валидацию
	 * Пример использования:
	 * ```js
	 * this.FormPanel.getInvalid()[0].focus();
	 * ```
	 */
	getInvalid: function(){
		var result = [];
		
		function appendInvalidRecursively( items ){
			if ( items instanceof Ext.util.MixedCollection ) {
				if ( items.items ) {
					appendInvalidRecursively( items.items );
				}
				return;
			}
						
			var cnt = items.length,
				i,
				item;
		
			for(i=0; i<cnt; i++){
				item=items[i];
				if ( item.items ) {
					if ( item.el && item.el.hasClass( item.invalidClass ) ) {
						result.push(item);
					} else {
						appendInvalidRecursively( items[i].items );
					}
				} else {
					if ( !(item).disabled && item.el.hasClass( item.invalidClass ) ) {
						result.push(item);
					}
				}
			}
		};
		
		appendInvalidRecursively( this.items.items );
		return result;
    },
	
	/**
	 * Возвращает первый видимый элемент формы
	 * Пример использования:
	 * ```js
	 * var active = this.FormPanel.getFirstActiveField();
	 * if (active) {
	 *		active.ensureVisible().focus();
	 * }
	 * ```
	 */
	getFirstActiveField: function(){
		function getFirstActiveFieldRecursively(items){
			if (items instanceof Ext.util.MixedCollection) {
				if (items.items) {
					return getFirstActiveFieldRecursively(items.items);
				}
			}

			var cnt = items.length,
					item;

			for (var i = 0; i < cnt; i++) {
				item = items[i];
				if (item.items) {
					var child = getFirstActiveFieldRecursively(item.items);
					if (child) {
						return child;
					}
				} else if (item.xtype !== 'hidden' && item.ensureVisible()) {
					return item;
				}
			}

			return false;
		}

		return getFirstActiveFieldRecursively(this.items);
	},
	
/**
*  Проверка заполненности полей формы
*  Необходимо для поисковых форм
*/
	isEmpty: function(){
		var flag = true;
		var vals = this.getForm().getValues();
		var base_form = this.getForm();
		var value;

		for ( value in vals )
		{
			if (
				!base_form.findField(value)['hidden']
				&& base_form.findField(value)['xtype'] != 'hidden'
				&& base_form.findField(value)['xtype'] != 'button'
				&& vals[value].toString() != ','
				&& vals[value].toString() != '__.__.____'			
				&& !base_form.findField(value)['ignoreIsEmpty']
				&& vals[value] != null
				&& vals[value].toString().replace(/[%_]/g, '').length > 0
			)
			{
				flag = false;
			}
		}
		return flag;
	},

	getOwnerWindow: function() {
		if (!this.ownerWindow) {
			var fn = function(c){return c instanceof Ext.Window};
			this.ownerWindow = this.findParentBy(fn);
		}
		return this.ownerWindow;
	},

	getAttributesFrame: function(object) { 
		if (Ext.isEmpty(this.attrObject) && Ext.isEmpty(object)) {
			return false;
		}
		var attrObject = object;
		var frame = null;
		if (this.items) {
			var fn = function(item) {
				if (item instanceof sw.Promed.AttributesFrame && item.object == attrObject) {
					frame = item;
					return false;
				}
			}.createDelegate(this);

			if (Ext.isArray(this.items)) {
				this.items.forEach(fn);
			} else {
				this.items.each(fn);
			}
		}
		return frame;
	},

	addAttributesFrame: function(attributesFrame, toBegin) {
		attributesFrame.formPanel = this;
		if (!Ext.isArray(this.attributesFrameList)) {
			this.attributesFrameList = [];
			this.form.attributesFrameList = [];
		}
		for (var i=0;i < this.attributesFrameList.length; i++) {
			if (this.attributesFrameList[i].object == attributesFrame.object) {
				return false;
			}
		}
		this.attributesFrameList.push(attributesFrame);
		this.form.attributesFrameList.push(attributesFrame);

		if (!this.items) {
			this.items = [attributesFrame];
		} else {
			if (!this.getAttributesFrame(attributesFrame.object)) {
				if (Ext.isArray(this.items)) {
					if (toBegin) {
						this.items.unshift(attributesFrame);
					} else {
						this.items.push(attributesFrame);
					}
				} else {
					if (toBegin) {
						this.insert(0, attributesFrame);
					} else {
						this.add(attributesFrame);
					}
				}
			}
		}
		return attributesFrame;
	},

	initAttributes: function(){
		if (this.attrObject && !this.getAttributesFrame(this.attrObject)) {
			var frame = new sw.Promed.AttributesFrame({
				object: this.attrObject,
				identField: this.identField ? this.identField : this.attrObject+'_id'
			});
			this.addAttributesFrame(frame);
		}
		if (this.items) {
			var items = Ext.isArray(this.items) ? this.items : this.items.items;
			for (var i=0;i < items.length; i++) {
				if (items[i] instanceof sw.Promed.AttributesFrame) {
					this.addAttributesFrame(items[i]);
				}
			}
		}
		this.on('actioncomplete', function(base_form, action) {
			if (this.attributesFrameList && this.attributesFrameList.length > 0) {
				if (action.type == 'load') {
					this.attributesFrameList.forEach(function(frame){ frame.afterLoad(action); });
				} else if (action.type == 'submit' && action.result.attributes) {
					for (var Attribute_SysNick in action.result.attributes) {
						var attrResponse = action.result.attributes;
						if (attrResponse.AttributeValue_id) {
							base_form.findField(Attribute_SysNick).attribute.AttributeValue_id = attrResponse.AttributeValue_id;
						}
					}
					this.attributesFrameList.forEach(function(frame){ frame.afterSave(action); });
				}
			}
		}.createDelegate(this));
	},

	setAttributes: function(data) {
		if (!data.attributes || data.attributes.length == 0) {
			return false;
		}
		var attrObjects = {};
		data.attributes.forEach(function(attribute){
			var key = attribute.AttributeVision_TableName;
			if (this && !this.getAttributesFrame(key)) {
				var frame = new sw.Promed.AttributesFrame({object: key, identField: attribute.Attribute_IdentField});
				this.addAttributesFrame(frame);
			}
			if (!attrObjects[key]) {
				attrObjects[key] = [];
			}
			attrObjects[key].push(attribute);
		}.createDelegate(this));

		if (this.attributesFrameList) {
			this.attributesFrameList.forEach(function(frame){
				if (attrObjects[frame.object]) {
					frame.setAttributes(attrObjects[frame.object]);

					if (data.tableDirectData) {
						frame.appendTableDirectData(data.tableDirectData);
					}
				}
			});
			this.doLayout();
		}
		return true;
	},

	initComponent: function(){
		this.form = this.createForm();
		this.form.formPanel = this;

		this.bodyCfg = {
			tag: 'form',
			cls: this.baseCls + '-body',
			method : this.method || 'POST',
			id : this.formId || Ext.id()
		};
		if(this.fileUpload) {
			this.bodyCfg.enctype = 'multipart/form-data';
		}

		Ext.FormPanel.superclass.initComponent.call(this);

		this.initAttributes();

		this.initItems();

		this.addEvents(
			/**
			 * @event clientvalidation
			 * If the monitorValid config option is true, this event fires repetitively to notify of valid state
			 * @param {Ext.form.FormPanel} this
			 * @param {Boolean} valid true if the form has passed client-side validation
			 */
			'clientvalidation'
		);

		this.relayEvents(this.form, ['beforeaction', 'actionfailed', 'actioncomplete']);
	}
});

/**
 * Переопределение Ext.form.BasicForm
 */
Ext.override(Ext.form.BasicForm, {
	findField : function(id){
		var field = this.items.get(id);
		if(!(field && typeof field == 'object')){
			this.items.each(function(f){
				if(f.isFormField && (f.dataIndex == id || f.id == id || f.getName() == id)){
					field = f;
					return false;
				}
			});
		}
		if (!field) {
			log('findField: поле не найдено', id);
		}
		return field;
	},
	formPanel: null,
	timeout: 120,
	getAllValues: function(asString) { // получает все данные с формы, даже задисабленные.
		var form = this.el.dom;

		if(typeof form == 'string') {
			form = (document.getElementById(form) || document.forms[form]);
		}

		var el, name, val, disabled, data = '', hasSubmit = false;
		for (var i = 0; i < form.elements.length; i++) {
			el = form.elements[i];
			disabled = form.elements[i].disabled;
			name = form.elements[i].name;
			val = form.elements[i].value;

			if (el.type && el.type == "hidden" && val == "undefined") { // костылёк, почему то некоторые скрытые поля для кобмиков заполнены строкой undefined
				val = "";
			}

			if (name) {
				switch (el.type)
				{
					case 'select-one':
					case 'select-multiple':
						for (var j = 0; j < el.options.length; j++) {
							if (el.options[j].selected) {
								var opt = el.options[j],
									sel = (opt.hasAttribute ? opt.hasAttribute('value') : opt.getAttributeNode('value').specified) ? opt.value : opt.text;
								data += encodeURIComponent(name) + '=' + encodeURIComponent(sel) + '&';
							}
						}
						break;
					case 'radio':
					case 'checkbox':
						if (el.checked) {
							data += encodeURIComponent(name) + '=' + encodeURIComponent(val) + '&';
						}
						break;
					case 'file':

					case undefined:

					case 'reset':

					case 'button':

						break;
					case 'submit':
						if(hasSubmit == false) {
							data += encodeURIComponent(name) + '=' + encodeURIComponent(val) + '&';
							hasSubmit = true;
						}
						break;
					default:
						data += encodeURIComponent(name) + '=' + encodeURIComponent(val) + '&';
						break;
				}
			}
		}
		data = data.substr(0, data.length - 1);

		if(asString === true){
			return data;
		}

		return Ext.urlDecode(data);
	},
	reset : function(){ // при ресете невалидные поля помечаем 
		this.items.each(function(f){
			f.reset();
			if (!f.isValid())
				f.markInvalid();
		});
		return this;
	},
	load : function(options){
		var win = this.formPanel.getOwnerWindow();
		var winId = win ? win.id : null;
		var alreadyHasAttrFrame = false;
		var attrObjects = [];
		if (this.attributesFrameList && this.attributesFrameList.length > 0) {
			this.attributesFrameList.forEach(function(item){
				//todo: убирать те атрибуты, у которых не пришли данные ПОСЛЕ ЗАГРУЗКИ, остальные атрибуты не трогать(можно обновить некоторые свойтва атрибутов)
				item.clearAttributes();
				attrObjects.push({object: item.object, identField: item.identField ? item.identField : item.object+'_id'});
				if (item.object == winId) {
					alreadyHasAttrFrame = true;
				}
			});
		}
		if (winId && !alreadyHasAttrFrame && !win.withoutAttr) {
			var match = /^(?:sw){0,1}(\w+)Edit(?:Form|Window)$/.exec(winId);
			var identField = match ? match[1]+'_id' : '';
			attrObjects.push({object: winId, identField: identField});
		}
		if (attrObjects.length > 0) {
			options = options || {};
			options.params = options.params || {};
			Ext.apply(options.params, {attrObjects: Ext.util.JSON.encode(attrObjects)});
		}
		this.doAction('load', options);
		return this;
	},
	submit : function(options){
		options = Ext.applyIf(options || {}, {
			params: {}
		});
		if(this.standardSubmit){
			var v = this.isValid();
			if(v){
				this.el.dom.submit();
			}
			return v;
		}
		if (this.attributesFrameList && this.attributesFrameList.length > 0) {
			var attributes = [];
			var attributesBySign = [];
			this.attributesFrameList.forEach(function(item){
				if (item.denyAutoSubmit) {
					return;
				}
				switch(true) {
					case item instanceof sw.Promed.AttributesBySignFrame:
						attributesBySign = attributesBySign.concat(item.getSaveParams());
						break;
					case item instanceof sw.Promed.AttributesFrame:
						attributes = attributes.concat(item.getSaveParams());
						break;
				}
			});
			if (attributes.length > 0) {
				Ext.apply(options.params, {attributes: Ext.util.JSON.encode(attributes)});
			}
			if (attributesBySign.length > 0) {
				Ext.apply(options.params, {attributesBySign: Ext.util.JSON.encode(attributesBySign)});
			}
		}
		this.doAction('submit', options);
		return this;
	}
});

Ext.override(Ext.form.Action.Load, {
	handleResponse : function(response){
		var jsonData = Ext.decode(response.responseText);
		if (jsonData.attributes) {
			this.form.formPanel.setAttributes(jsonData);
		}
		if(this.form.reader){
			var rs = this.form.reader.read(response);
			var data = rs.records && rs.records[0] ? rs.records[0].data : null;
			return {
				success : rs.success,
				data : data
			};
		}
		return Ext.decode(response.responseText);
	}
});

/**
*  Переопределен метод onRender кнопки, в котором добавляется листенер для выполнения тех или иных действий при нажатии на TAB или Shift+Tab
*  При нажатии на Tab или Shift + Tab вызывается определенный при инициализации
*  метод onTabAction() или onShiftTabAction() соответственно, в качестве параметра в эти методы передается ссылка на кнопку.
*  Так же можно использовать свойства onTabElement и onShiftTabElement, в которых задаются идентификатор элемента или ссылка на элемент, на котором
*  должен оказаться фокус при нажатии Tab или ShiftTab.
*/

Ext.override(Ext.Element, {
	saveTabbableState: function() {

	},
	restoreTabbableState: function() {

	},
    setAttribute : function(att, value) { 
        if (this.dom.setAttributeNS) { 
            this.dom.setAttributeNS(undefined, att, value); 
        } else if (this.dom.setAttribute) { 
            this.dom.setAttribute(att, value); 
        } 
    },
	getAttribute: function(name){
		return this.getAttributeNS(undefined, name);
	},
	addKeyListenerWithStop : function(key, fn, scope){
		var config;
		if(typeof key != "object" || Ext.isArray(key)){
			config = {
				key: key,
				fn: fn,
				scope: scope,
				stopEvent: true
			};
		}else{
			config = {
				key : key.key,
				shift : key.shift,
				ctrl : key.ctrl,
				alt : key.alt,
				fn: fn,
				scope: scope,
				stopEvent: true
			};
		}
		return new Ext.KeyMap(this, config);
	}
});

Ext.override(Ext.menu.Item, {
	onRender : function(container, position){
		var el = document.createElement("a");
		el.hideFocus = true;
		el.unselectable = "on";
		el.href = this.href || "#";
		if(this.hrefTarget){
			el.target = this.hrefTarget;
		}
		el.className = this.itemCls + (this.menu ?  " x-menu-item-arrow" : "") + (this.cls ?  " " + this.cls : "");
		el.innerHTML = String.format(
			'<img src="{0}" class="x-menu-item-icon {2}" />{1}',
			this.icon || Ext.BLANK_IMAGE_URL, this.itemText||this.text, this.iconCls || '');
		this.el = el;
		Ext.menu.Item.superclass.onRender.call(this, container, position);

		if (TEST_ID_ENABLED) {
			var parentTestId = '';
			if (this.el.up) {
				var parentTest = this.el.up('[test_id]');
				if (parentTest) {
					parentTestId = parentTest.getAttribute('test_id') + '_';
				}
			}
			var xtype = 'mi';
			var name = '';
			if (this.text && typeof this.text == 'string') {
				name = swTranslite(this.text);
			} else if (this.tooltip && typeof this.tooltip == 'string') {
				name = swTranslite(this.tooltip);
			} else if (this.id) {
				name = this.id;
			}

			var test_id = parentTestId + xtype;
			if (name.indexOf('ext-') < 0) {
				test_id = test_id + '_' + name;
			}
			test_id = test_id.replace(/<\/?[^>]+>/gi, '');
			test_id = test_id.replace(/\./gi, '');
			this.el.setAttribute('test_id', test_id);
		}
	}
});

Ext.override(Ext.Toolbar, {
	onRender : function(ct, position){
		this.el = ct.createChild(Ext.apply({ id: this.id },this.autoCreate), position);
		this.tr = this.el.child("tr", true);

		if (TEST_ID_ENABLED) {
			var parentTestId = '';
			if (this.el.up) {
				var parentTest = this.el.up('[test_id]');
				if (parentTest) {
					parentTestId = parentTest.getAttribute('test_id') + '_';
				}
			}
			var xtype = 'tbr';
			var name = '';
			if (this.text && typeof this.text == 'string') {
				name = swTranslite(this.text);
			} else if (this.tooltip && typeof this.tooltip == 'string') {
				name = swTranslite(this.tooltip);
			} else if (this.id) {
				name = this.id;
			}

			var test_id = parentTestId + xtype;
			if (name.indexOf('ext-') < 0) {
				test_id = test_id + '_' + name;
			}
			test_id = test_id.replace(/<\/?[^>]+>/gi, '');
			test_id = test_id.replace(/\./gi, '');
			this.el.setAttribute('test_id', test_id);
		}
	},

	add: function() {
		var a = arguments, l = a.length;
		for(var i = 0; i < l; i++){
			var el = a[i];
			if(el.isFormField){ // some kind of form field
				this.addField(el);
			}else if(el.render){ // some kind of Toolbar.Item
				this.addItem(el);
			}else if(typeof el == "string"){ // string
				if(el == "separator" || el == "-"){
					this.addSeparator();
				}else if(el == " "){
					this.addSpacer();
				}else if(el == "->"){
					this.addFill();
				}else{
					this.addText(el);
				}
			}else if(el.tagName){ // element
				this.addElement(el);
			}else if(Ext.isArray(el)) {
				for(var j = 0; j < el.length; j++) {
					this.add.call(this, el[j]);
				}
			}else if(typeof el == "object"){ // must be button config?
				if(el.tbarItems) {
					this.add.call(this, el.tbarItems);
				}else if(el.xtype){
					this.addField(Ext.ComponentMgr.create(el, 'button'));
				}else{
					this.addButton(el);
				}
			}
		}
	}
});

Ext.override(Ext.Button, {
	onRender: function (ct, position) {
		Ext.Button.superclass.onRender.call(this);
		if (!this.template) {
			if (!Ext.Button.buttonTemplate) {
				// hideous table template
				Ext.Button.buttonTemplate = new Ext.Template(
					'<table border="0" cellpadding="0" cellspacing="0" class="x-btn-wrap"><tbody><tr>',
					'<td class="x-btn-left"><i>&#160;</i></td><td class="x-btn-center"><em unselectable="on"><button class="x-btn-text" type="{1}">{0}</button></em></td><td class="x-btn-right"><i>&#160;</i></td>',
					"</tr></tbody></table>");
			}
			this.template = Ext.Button.buttonTemplate;
		}
		var btn, targs = [this.text || '&#160;', this.type];

		if (position) {
			btn = this.template.insertBefore(position, targs, true);
		} else {
			btn = this.template.append(ct, targs, true);
		}
		var btnEl = this.btnEl = btn.child(this.buttonSelector);
		btnEl.on('focus', this.onFocus, this);
		btnEl.on('blur', this.onBlur, this);

		this.initButtonEl(btn, btnEl);

		if (this.menu) {
			this.el.child(this.menuClassTarget).addClass("x-btn-with-menu");
		}
		if (this.iconCls) {
			this.el.child(this.menuClassTarget).addClass(this.text ? 'x-btn-text-icon' : 'x-btn-icon');
		}

		Ext.ButtonToggleMgr.register(this);

		if (TEST_ID_ENABLED) {
			var parentTestId = '';
			if (this.el.up) {
				var parentTest = this.el.up('[test_id]');
				if (parentTest) {
					parentTestId = parentTest.getAttribute('test_id') + '_';
				}
			}
			var xtype = 'btn';
			var name = '';
			if (this.text && typeof this.text == 'string') {
				name = swTranslite(this.text);
			} else if (this.tooltip && typeof this.tooltip == 'string') {
				name = swTranslite(this.tooltip);
			} else if (this.id) {
				name = this.id;
			}

			var test_id = parentTestId + xtype;
			if (name.indexOf('ext-') < 0) {
				test_id = test_id + '_' + name;
			}
			test_id = test_id.replace(/<\/?[^>]+>/gi, '');
			test_id = test_id.replace(/\./gi, '');
			this.el.setAttribute('test_id', test_id);
		}

		// действия при нажатии на TAB или Shift+TAB
		if (this.onTabElement || this.onTabAction || this.onShiftTabElement || this.onShiftTabAction) {
			this.getClickEl().addKeyListenerWithStop(
				{key: [Ext.EventObject.TAB]},
				// e.shiftKey == true
				function (code, e) {
					if (e.shiftKey == true) {
						if (this.onShiftTabElement != undefined) {
							if (typeof(this.onShiftTabElement) == 'string') {
								var el = Ext.getCmp(this.onShiftTabElement);
								if (el) {
									el.focus(true, 100);
								}
							}
							else {
								if (typeof(this.onShiftTabElement.focus) == 'function') {
									this.onShiftTabElement.focus(true, 100);
								}
							}
						}

						if (typeof(this.onShiftTabAction) == 'function') {
							this.onShiftTabAction(this);
						}
					}
					else {
						if (this.onTabElement != undefined) {
							if (typeof(this.onTabElement) == 'string') {
								var el = Ext.getCmp(this.onTabElement);
								if (el) {
									el.focus(true, 100);
								}
							}
							else {
								if (typeof(this.onTabElement.focus) == 'function') {
									this.onTabElement.focus(true, 100);
								}
							}
						}

						if (typeof(this.onTabAction) == 'function') {
							this.onTabAction(this);
						}
					}

					e.stopEvent();
				}.createDelegate(this),
				this
			);
		}
	}
});

Ext.override(Ext.form.ComboBox, {
	clearValue : function(){
        if(this.hiddenField){
            this.hiddenField.value = '';
        }
        this.setRawValue('');
        this.lastSelectionText = '';
        this.applyEmptyText();
        this.value = '';
		// запускаем валидэйт чтобы поставить зелёную подсветку, если поле было отмечено как обязательное
		if (this.rendered && typeof this.validate == 'function') {
			this.validate();
		}
    },
	insertAdditionalRecord: function(value, text, code) {
		var data = {};					
		
		if ( this.codeField && code != undefined )
			data[this.codeField] = code;
		data[this.valueField] = value;
		data[this.displayField] = text;			
		
		var record = new Ext.data.Record(data, value);
		this.store.insert(0,[record]);
	},
	
	/**
	 * Добавление пустой записи в комбобокс
	 */
	insertEmptyRecord: function () {
		var data = {};
		
		if ( this.codeField )
			data[this.codeField] = "";
		data[this.valueField] = "";
		data[this.displayField] = "";
		
		var record = new Ext.data.Record(data);

		if (this.store && this.store.getById(record.id)) {
			record.id = this.store.data.last().id + 1000;
		}

		this.store.insert(0,[record]);
	},

	// private
	onViewClick : function(doFocus){
		var index = this.view.getSelectedIndexes()[0];
		var r = this.store.getAt(index);
		if ( doFocus === false )
		{
			if ( r && this.selectRawValueOnly != undefined && this.selectRawValueOnly === true )
			{
				if ( this.getRawValue() != r.data[this.displayField] )
				{
					var oldValue = this.getRawValue();
					this.clearValue();
					this.setRawValue(oldValue);
					this.collapse();
					return true;
				}
			}
		}
		if(r){
			// если текущий элемент пустой, то выбираем следующий, если он есть
//            	if ( doFocus === false && r.data[this.displayField]=='' )
//				{
//					index = index + 1
//			        if ( this.store.getAt(index) )
//					{
//						r = this.store.getAt(index);
//					}
//				}
			this.onSelect(r, index);
		}
		if(doFocus !== false){
			this.el.focus();
		}
	},
	/**
	 * Вызывается при загрузке значений в список, для добавления пустой строки
	 */
	onLoad : function(){
		/*if ( this.noExpandOnLoad === true )
		{
			this.noExpandOnLoad = false;
			return;
		}*/

		if(!this.hasFocus){
			return;
		}
		
		if ( this.store.getCount() > 0 && this.store.getAt(0).data[this.valueField] != "" && this.additionalRecord )
		{
			if ( !this.store.getById(this.additionalRecord.value) ) {
				this.insertAdditionalRecord(this.additionalRecord.value, this.additionalRecord.text, (this.additionalRecord.code != undefined ? this.additionalRecord.code : null ));
			}
		}
		
		if ( this.store.getCount() > 0 && this.store.getAt(0).data[this.valueField] != "" && this.allowBlank == true && this.hideEmptyRow != true )
		{
			this.insertEmptyRecord();
		}
				
		if(this.store.getCount() > 0 ){
			this.expand();
			this.restrictHeight();
			if(this.lastQuery == this.allQuery){
				if(this.editable){
					this.el.dom.select();
				}
				if(!this.selectByValue(this.value, true)){
					this.select(0, true);
				}
			}else{
				this.selectNext();
				// если текущий выбраный пустой, то переходим на следующий
				if ( this.getStore().getAt(this.view.getSelectedIndexes()[0]) )
				{
					var row = this.getStore().getAt(this.view.getSelectedIndexes()[0]);
					if ( row.data[this.displayField] == '' )
					{
						this.selectNext();
					}
				}
				if(this.typeAhead && this.lastKey != Ext.EventObject.BACKSPACE && this.lastKey != Ext.EventObject.DELETE){
					this.taTask.delay(this.typeAheadDelay);
				}
			}
		}else{
			this.onEmptyResults();
		}
	},
	// private
	initList : function(){
		if(!this.list){
			var cls = 'x-combo-list';

			var zseed = getActiveZIndex();
			this.list = new Ext.Layer({
				shadow: this.shadow, cls: [cls, this.listClass].join(' '), constrain:false, zindex: zseed + 15000
			});

			var lw = this.listWidth || Math.max(this.wrap.getWidth(), this.minListWidth);
			this.list.setWidth(lw);
			this.list.swallowEvent('mousewheel');
			this.assetHeight = 0;

			if(this.title){
				this.header = this.list.createChild({cls:cls+'-hd', html: this.title});
				this.assetHeight += this.header.getHeight();
			}

			this.innerList = this.list.createChild({cls:cls+'-inner'});
			this.innerList.on('mouseover', this.onViewOver, this);
			this.innerList.on('mousemove', this.onViewMove, this);
			this.innerList.setWidth(lw - this.list.getFrameWidth('lr'));

			if(this.pageSize){
				this.footer = this.list.createChild({cls:cls+'-ft'});
				this.pageTb = new Ext.PagingToolbar({
					store:this.store,
					pageSize: this.pageSize,
					renderTo:this.footer
				});
				this.assetHeight += this.footer.getHeight();
			}

			if(!this.tpl){
				/**
				* @cfg {String/Ext.XTemplate} tpl The template string, or {@link Ext.XTemplate}
				* instance to use to display each item in the dropdown list. Use
				* this to create custom UI layouts for items in the list.
				* <p>
				* If you wish to preserve the default visual look of list items, add the CSS
				* class name <pre>x-combo-list-item</pre> to the template's container element.
				* <p>
				* <b>The template must contain one or more substitution parameters using field
				* names from the Combo's</b> {@link #store Store}. An example of a custom template
				* would be adding an <pre>ext:qtip</pre> attribute which might display other fields
				* from the Store.
				* <p>
				* The dropdown list is displayed in a DataView. See {@link Ext.DataView} for details.
				*/
				//this.tpl = '<tpl for="."><div class="'+cls+'-item">{[(values.'+this.displayField+')?"":"values.'+this.displayField+'"]}'+'</div></tpl>';
				this.tpl = '<tpl for="."><div class="'+cls+'-item">{[ (String(values.'+this.displayField+') == "undefined") ? "&nbsp;" : values.'+this.displayField+' ]}'+'</div></tpl>';
				
				/**
				 * @cfg {String} itemSelector
				 * <b>This setting is required if a custom XTemplate has been specified in {@link #tpl}
				 * which assigns a class other than <pre>'x-combo-list-item'</pre> to dropdown list items</b>.
				 * A simple CSS selector (e.g. div.some-class or span:first-child) that will be
				 * used to determine what nodes the DataView which handles the dropdown display will
				 * be working with.
				 */
			}

			/**
			* The {@link Ext.DataView DataView} used to display the ComboBox's options.
			* @type Ext.DataView
			*/
			this.view = new Ext.DataView({
				applyTo: this.innerList,
				tpl: this.tpl,
				singleSelect: true,
				selectedClass: this.selectedClass,
				itemSelector: this.itemSelector || '.' + cls + '-item'
			});

			this.view.on('click', this.onViewClick, this);

			this.bindStore(this.store, true);

			if(this.resizable){
				this.resizer = new Ext.Resizable(this.list,  {
				   pinned:true, handles:'se'
				});
				this.resizer.on('resize', function(r, w, h){
					this.maxHeight = h-this.handleHeight-this.list.getFrameWidth('tb')-this.assetHeight;
					this.listWidth = w;
					this.innerList.setWidth(w - this.list.getFrameWidth('lr'));
					this.restrictHeight();
				}, this);
				this[this.pageSize?'footer':'innerList'].setStyle('margin-bottom', this.handleHeight+'px');
			}
		}
	}
});

Ext.override(Ext.form.Checkbox, {

	initComponent : function(){
		Ext.form.Checkbox.superclass.initComponent.call(this);
		this.addEvents(
			'check', 'change'
		);
	},

	getResizeEl : function(){
		if(!this.resizeEl){
			//this.resizeEl = Ext.isWebKit ? this.wrap : (this.wrap.up('.x-form-element', 5) || this.wrap);
			this.resizeEl = this.wrap;
		}
		return this.resizeEl;
	},

	onRender : function(ct, position){
        Ext.form.Checkbox.superclass.onRender.call(this, ct, position);
        if(this.inputValue !== undefined){
            this.el.dom.value = this.inputValue;
        }
        this.el.addClass('x-hidden');

        this.innerWrap = this.el.wrap({
            tabIndex: this.tabIndex,
            cls: this.baseCls+'-wrap-inner'
        });
        this.wrap = this.innerWrap.wrap({cls: this.baseCls+'-wrap'});

        if(this.boxLabel){
            this.labelEl = this.innerWrap.createChild({
                tag: 'label',
                htmlFor: this.el.id,
                cls: 'x-form-cb-label',
                html: this.boxLabel
            });
        }

        this.imageEl = this.innerWrap.createChild({
            tag: 'img',
            src: Ext.BLANK_IMAGE_URL,
            cls: this.baseCls
        }, this.el);

        if(this.checked && this.checked!='false' && this.checked !=''){
            this.setValue(true);
        }else{
            this.checked = this.el.dom.checked;
        }
        this.originalValue = this.checked;
    },

	initCheckEvents : function(){
		// Исправление бага неправильной отметки при нажатии на лейбл
		this.innerWrap.removeAllListeners();
		this.innerWrap.addClassOnOver(this.overCls);
		this.innerWrap.addClassOnClick(this.mouseDownCls);
		//this.innerWrap.on('click', this.onClick, this);
		this.el.on('click', this.onClick, this);
		this.imageEl.on('click', this.onClick, this);
		this.innerWrap.on('keyup', this.onKeyUp, this);
	},

	onClick : function(e){
		if (!this.disabled && !this.readOnly) {
			this.toggleValue();
		}
		//событие на пользовательский ввод
		this.fireEvent("change", this, this.checked);
		//e.stopEvent();
	},

	onEnable: function(){
		Ext.form.Checkbox.superclass.onEnable.call(this);
	}
});
	
/**
* Изменение поведения комбобокса, чтобы можно было оставлять пустые значения
*/
Ext.override(Ext.form.ComboBox, {
	// private
	initEvents : function(){
		Ext.form.ComboBox.superclass.initEvents.call(this);

		this.keyNav = new Ext.KeyNav(this.el, {
			"up" : function(e){
				this.inKeyMode = true;
				this.selectPrev();
			},

			"down" : function(e){
				if(!this.isExpanded()){
					this.onTriggerClick();
				}else{
					this.inKeyMode = true;
					this.selectNext();
				}
			},

			"enter" : function(e){
				if (this.getRawValue() != '' || this.isExpanded()) {
					this.onViewClick();
				}
				else {
					this.collapse();
				}
				this.delayedCheck = true;
				this.unsetDelayCheck.defer(10, this);
			},

			"esc" : function(e){
				this.collapse();
			},

			"tab" : function(e){
//				if (this.getRawValue() != '') {
					this.onViewClick(false);
//				}
//				else {
//					this.collapse();
//				}
				return true;
			},

			"home" : function(e){
				this.inKeyMode = true;
				this.select(0);
			},

			"end" : function(e){
				this.inKeyMode = true;
				this.select(this.store.getCount() - 1);
			},

			"pageUp" : function(e){
				this.inKeyMode = true;
				var ct = this.store.getCount();
				if(ct > 0){
					if(this.selectedIndex == -1){
						this.select(0);
					}else if(this.selectedIndex != 0){
						if (this.selectedIndex-10>=0)
								this.select(this.selectedIndex-10);
							else
								this.select(0);
					}
				}
			},

			"pageDown" : function(e){
				if(!this.isExpanded()){
					this.onTriggerClick();
				}else{
					this.inKeyMode = true;
					var ct = this.store.getCount();
					if(ct > 0){
						if(this.selectedIndex == -1){
							this.select(0);
						}else if(this.selectedIndex != ct-1){
							if (this.selectedIndex+10<ct-1)
								this.select(this.selectedIndex+10);
							else
								this.select(ct-1);
						}
					}
				}
			},

			scope : this,

			doRelay : function(foo, bar, hname){
				if(hname == 'down' || this.scope.isExpanded()){
					return Ext.KeyNav.prototype.doRelay.apply(this, arguments);
				}
				return true;
			},

			forceKeyDown : true
		});
		this.queryDelay = Math.max(this.queryDelay || 10,
				this.mode == 'local' ? 10 : 250);
		this.dqTask = new Ext.util.DelayedTask(this.initQuery, this);
		if(this.typeAhead){
			this.taTask = new Ext.util.DelayedTask(this.onTypeAhead, this);
		}
		if(this.editable !== false){
			this.el.on("keyup", this.onKeyUp, this);
		}
	},

	onKeyUp : function(e){
		if(this.editable !== false && !e.isSpecialKey()){
			if (this.getRawValue() == '' && (e.keyCode == e.DELETE || e.keyCode == e.BACKSPACE)) {
				this.setValue('');
				if (this.onClearValue)
					this.onClearValue();
				this.collapse();
			}
			else {
				this.lastKey = e.getKey();
				if (this.dqTask != undefined)
					this.dqTask.delay(this.queryDelay);
			}
		}
	},
	
	onTypeAhead : function(){
        if(this.store.getCount() > 0){
            var r = this.store.getAt(0);
			// типа пустая строка и все такое
			if ( r.data[this.displayField] == '' && this.store.getCount() > 0 )
				var r = this.store.getAt(1);
            var newValue = r.data[this.displayField];
            var len = newValue.length;
            var selStart = this.getRawValue().length;
            if(selStart != len){
                this.setRawValue(newValue);
                this.selectText(selStart, newValue.length);
            }
        }
    }
});

// плагин CheckColumn
Ext.grid.CheckColumn = function(config){
	Ext.apply(this, config);
	if(!this.id){
		this.id = Ext.id();
	}
	this.renderer = this.renderer.createDelegate(this);
};

Ext.override(Ext.grid.CellSelectionModel, {
	getSelected: function() {
		var selected_cells = this.getSelectedCell();
		if ( selected_cells )
			return this.grid.getStore().getAt(selected_cells[0]);
		else
			return selected_cells;
	},
	selectRow: function(index) {
		// если была выбрана конкретная ячейка, то устанавливаем в этой колонке
		var selected_cells = this.getSelectedCell();
		if ( selected_cells )
		{
			this.select(index, selected_cells[1]);
			return true;
		}
		for ( var key in this.grid.getColumnModel().lookup )
			if ( this.grid.getColumnModel().lookup[key]['hidden'] === false )
			{
				this.select(index, key);
				return true;
			}
	},
	selectFirstRow: function() {
		var index = 0;
		// если была выбрана конкретная ячейка, то устанавливаем в этой колонке
		var selected_cells = this.getSelectedCell();
		if ( selected_cells )
		{
			this.select(index, selected_cells[1]);
			return true;
		}
		for ( var key in this.grid.getColumnModel().lookup )
			if ( this.grid.getColumnModel().lookup[key]['hidden'] === false )
			{
				this.select(index, key);
				return true;
			}
	},
	selectLastRow: function() {
		if ( this.grid.getStore().getCount() == 0 )
			return;
		var index = this.grid.getStore().getCount() - 1;
		// если была выбрана конкретная ячейка, то устанавливаем в этой колонке
		var selected_cells = this.getSelectedCell();
		if ( selected_cells )
		{
			this.select(index, selected_cells[1]);
			return true;
		}
		for ( var key in this.grid.getColumnModel().lookup )
			if ( this.grid.getColumnModel().lookup[key]['hidden'] === false )
			{
				this.select(index, key);
				return true;
			}
	}
});

Ext.grid.CheckColumn.prototype ={
	init : function(grid){
		this.grid = grid;
		this.grid.on('render', function(){
			var view = this.grid.getView();
			view.mainBody.on('mousedown', this.onMouseDown, this);
		}, this);
	},

	onMouseDown : function(e, t){
		if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
			e.stopEvent();
			var index = this.grid.getView().findRowIndex(t);
			var record = this.grid.store.getAt(index);
			record.set(this.dataIndex, !record.data[this.dataIndex]);
		}
	},

	renderer : function(v, p, record){
		var qtip = '';
		if (typeof this.qtip == 'function') {
			qtip = this.qtip(v, p, record);
			qtip = (qtip?'ext:qtip="'+qtip+'"':'');
		}
		if(!v){
			return "";
		}
		if ( !p )
		{
			if ( v == 'true' )
				return langs('Да')
			else
				return langs('Нет')
		}
		p.css += ' x-grid3-check-col-td';
		if ( v == 'gray' )
			var style = 'x-grid3-check-col-on-non-border-gray';
		else
			if ( v == 'red' )
				var style = 'x-grid3-check-col-on-non-border-red';
			else if ( v == 'yellow' )
				var style = 'x-grid3-check-col-on-non-border-yellow';
			else if ( v == 'orange' )
				var style = 'x-grid3-check-col-on-non-border-orange';
			else if ( v == 'blue' )
				var style = 'x-grid3-check-col-on-non-border-blue';
			else
				var style = 'x-grid3-check-col-non-border'+((String(v)=='true')?'-on':'');
		return '<div class="'+style+' x-grid3-cc-'+this.id+'" '+qtip+'>&#160;</div>';
	}
};

// 
Ext.override(Ext.ToolTip, {
    onTargetOver : function(e){
        if(this.disabled || e.within(this.target.dom, true)){
            return;
        }
        var t = e.getTarget(this.delegate);
        if (t) {
            this.triggerElement = t;
            this.clearTimer('hide');
            this.targetXY = e.getXY();
            this.delayShow();
        }
    },
    onMouseMove : function(e){
        var t = e.getTarget(this.delegate);
        if (t) {
            this.targetXY = e.getXY();
            if (t === this.triggerElement) {
                if(!this.hidden && this.trackMouse){
                    this.setPagePosition(this.getTargetXY());
                }
            } else {
                this.hide();
                this.lastActive = new Date(0);
                this.onTargetOver(e);
            }
        } else if (!this.closable && this.isVisible()) {
            this.hide();
        }
    },
    hide: function(){
        this.clearTimer('dismiss');
        this.lastActive = new Date();
		if (typeof(this.triggerElement) != "undefined") {
			delete this.triggerElement;
		}
        Ext.ToolTip.superclass.hide.call(this);
    }
});
Ext.override(Ext.Panel, {
	doLayout: function() {
		var a = arguments;
		// If collapsed, then defer layout operation until the next time this Panel is expended.
		if (this.collapsed) {
			this.on('expand', function() {
				this.doLayout.apply(this, a);
			}, this, {single: true});
			return;
		}
		Ext.Panel.superclass.doLayout.apply(this, a);
	}
});

// Night: В принципе эти расширения можно вынести в SwBaseLocalCombo (или любой другой базовый элемент) (и скорее всего даже нужно!) 
Ext.override(Ext.form.ComboBox, {
	/**
	 * Добавление метода комбобокса, позволяющего проставить значение fieldValue по полю fieldName
	 * Пример вызова функции: base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
	 */
	setFieldValue: function (fieldName, fieldValue) {
		var table = '';
		if (this.tableName) {
			table = this.tableName;
		}
		else {
			table = fieldName.substr(0, fieldName.indexOf('_'));
		}
		if (table.length > 0) {
			var idx = this.getStore().findBy(function (rec) {
				if (rec.get(fieldName) == fieldValue) {
					return true;
				}
				else {
					return false;
				}
			});
			var record = this.getStore().getAt(idx);
			if (record) {
				this.setValue(record.get(table + '_id'));
			}
			else {
				this.clearValue();
			}
			this.fireEvent('change', this, this.getValue());
		}
		else {
			if (IS_DEBUG) {
				console.warn('Наименование объекта (%o) не определено!', this);
				console.warn('Поле: %s', fieldName);
				console.warn('Значение: %s', fieldValue);
			}
		}
	},
	/**
	 * Добавление метода комбобокса, позволяющего получить значение поля fieldName из Store комбобокса
	 * Пример вызова функции: base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
	 */
	getFieldValue: function (fieldName) {
		var table = '';
		if (!Ext.isEmpty(this.getValue()) && (this.getStore().getCount() > 0)) {
			var idx = this.getStore().findBy(function (rec) {
				var key = null;
				if (this.valueField) { // в первую очередь берём по valueField
					key = this.valueField;
				} else if (this.getStore().key) {
					key = this.getStore().key;
				} else if (this.getStore().reader) {
					key = this.getStore().reader.meta.id;
				}
				if (rec.get(key) == this.getValue()) {
					return true;
				}
				else {
					return false;
				}
			}.createDelegate(this));
			var record = this.getStore().getAt(idx);
			if (record) {
				return record.get(fieldName);
			}
			else {
				if (IS_DEBUG) {
					console.warn('Невозможно выбрать запись из Store комбобокса (%o) или поле %s отсутствует в Store!', this, fieldName);
					console.warn(record);
				}
			}
		}
		else {
			return null;
		}
	},
	/**
	 * Установка фильтра комбобокса.
	 * @filter (object)      Массив значений, которые должны остаться после установки фильтра
	 * @fieldName (string)   Наименовение поля из Store для фильтрации.
	 * @fieldValue (string)  Значение поля
	 * Примеры вызова функции:
	 *   base_form.findField('PayType_id').setFilter([1,2,3]);
	 *   base_form.findField('PayType_id').setFilter(['oms'], 'PayType_SysNick', 1);
	 */
	setFilter: function (filter, fieldName, fieldValue) {

		var combo = this;
		// Если фильтр установлен и есть данные для фильтрации, тогда работаем дальше
		if ((filter && typeof filter === "object") && (combo.getStore().getCount() > 0)) {
			/*
			 // Определяем локальные данные использует комбо или нет
			 if (combo.getStore().dbFile)
			 {

			 }
			 */
			if (!fieldName) {
				// Если наименование поля, по которому нужно выполнить фильтрацию не передано, то выбираем поле-ключ
				if (combo.getStore().key)
					fieldName = combo.getStore().key;
				else if (combo.getStore().reader)
					fieldName = combo.getStore().reader.meta.id;
			}
			// Если поле установлено в какое то значение, то при фильтрации мы должны это учесть
			if (!fieldValue) {
				fieldValue = combo.getValue();
			}
			// фильтруем

			//  предварительно очищаем фильтр
			combo.getStore().clearFilter();
			combo.lastQuery = '';
			combo.getStore().filterBy(function (record) {
				// Если установлено значение то мы его выбираем 
				if (fieldValue == record.get(fieldName)) {
					combo.fireEvent('select', combo, record, 0);
					combo.fireEvent('change', combo, fieldValue, '');
				}
				return (record.get(fieldName).inlist(filter));
			});
			if (fieldValue == null) {
				combo.fireEvent('change', combo, null, null);
			}
			return true;
		}
		else {
			if (IS_DEBUG) {
				console.warn('Установить фильтр (%f) в поле (%o) невозможно!', filter, combo);
				console.warn('Или фильтр пустой, или данные в Store отсутствуют.');
			}
			return false;
		}
	},
	/**
	 * Сброс фильтра комбобокса.
	 * Примеры вызова функции:
	 *   base_form.findField('PayType_id').clearFilter();
	 */
	clearFilter: function () {
		this.getStore().clearFilter();
		this.lastQuery = '';
		return true;
	}
});

// добавил передачу признака архивной записи, если грид находится на форме с признаком архивной записи
Ext.override( Ext.grid.GridPanel, {
	ownerWindow: null,
	applyState : function(state){
		var cm = this.colModel;
		var cs = state.columns;
		if (cs) {
			for (var i = 0, len = cs.length; i < len; i++) {
				var s = cs[i];

				if (s.dataIndex && s.dataIndex.length > 0) {
					// если есть dataIndex, ищем по нему (обычно dataIndex есть, т.к. колонка обычно выводит некие данные).
					var c = cm.getColumnsBy(function(c) { return (c && c.dataIndex && c.dataIndex == s.dataIndex); });
					if (c && c[0]) {
						c = c[0];
						c.hidden = s.hidden;
						c.width = s.width;
						var oldIndex = cm.getIndexById(s.id);
						if (cm.config[oldIndex] && oldIndex != i) {
							cm.moveColumn(oldIndex, i);
						}
					}
				} else {
					// иначе по id
					var c = cm.getColumnById(s.id);
					if (c && !(c.dataIndex && c.dataIndex.length > 0)) { // применяем только, если у нашедшейся колонки тоже нет dataIndex
						c.hidden = s.hidden;
						c.width = s.width;
						var oldIndex = cm.getIndexById(s.id);
						if (cm.config[oldIndex] && oldIndex != i) {
							cm.moveColumn(oldIndex, i);
						}
					}
				}
			}
		}
		if(state.sort){
			this.store[this.store.remoteSort ? 'setDefaultSort' : 'sort'](state.sort.field, state.sort.direction);
		}
		delete state.columns;
		delete state.sort;
		Ext.grid.GridPanel.superclass.applyState.call(this, state);
	},
	getState : function(){
		var o = {columns: []};
		for(var i = 0, c; c = this.colModel.config[i]; i++){
			o.columns[i] = {
				id: c.id,
				width: c.width,
				dataIndex: c.dataIndex
			};
			if(c.hidden){
				o.columns[i].hidden = true;
			}
		}
		var ss = this.store.getSortState();
		if(ss){
			o.sort = ss;
		}
		return o;
	},
	onRender : function(ct, position){
		Ext.grid.GridPanel.superclass.onRender.apply(this, arguments);

		var c = this.body;

		this.el.addClass('x-grid-panel');

		var view = this.getView();
		view.init(this);

		c.on("mousedown", this.onMouseDown, this);
		c.on("click", this.onClick, this);
		c.on("dblclick", this.onDblClick, this);
		c.on("contextmenu", this.onContextMenu, this);
		c.on("keydown", this.onKeyDown, this);

		this.relayEvents(c, ["mousedown","mouseup","mouseover","mouseout","keypress"]);

		this.getSelectionModel().init(this);
		this.view.render();

		var grid = this;
		// ищем базовую форму по ownerCt и проверяем права на редактирование
		var ownerCur = grid.ownerCt;
		if( ownerCur != undefined ){
			while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
				ownerCur = ownerCur.ownerCt;
			}
			if (typeof ownerCur.checkRole == 'function') {
				grid.ownerWindow = ownerCur;
			}
		}

		this.getStore().addListener('beforeload', function (store, options) {
			if (grid.ownerWindow && grid.ownerWindow.archiveRecord) {
				options.params.archiveRecord = grid.ownerWindow.archiveRecord;
			}
		});

		if (TEST_ID_ENABLED) {
			var parentTestId = '';
			if (this.el.up) {
				var parentTest = this.el.up('[test_id]');
				if (parentTest) {
					parentTestId = parentTest.getAttribute('test_id') + '_';
				}
			}

			log(this.getStore());

			var xtype = 'grd';
			var name = '';
			if (this.getStore() && this.getStore().reader && this.getStore().reader.meta && this.getStore().reader.meta.id) {
				name = this.getStore().reader.meta.id.replace(/_id$/g, "");
			} else if (this.id) {
				name = this.id;
			}

			var test_id = parentTestId + xtype;
			if (name.indexOf('ext-') < 0) {
				test_id = test_id + '_' + name;
			}
			test_id = test_id.replace(/<\/?[^>]+>/gi, '');
			test_id = test_id.replace(/\./gi, '');

			if (testIdCounts[test_id]) {
				testIdCounts[test_id]++;
				test_id = test_id + '_' + testIdCounts[test_id];
			} else {
				testIdCounts[test_id] = 1;
			}

			this.el.setAttribute('test_id', test_id);
		}
	}
});

// добавлена проверка, проверать ли пустое значение
Ext.override( Ext.grid.EditorGridPanel, {
    onEditComplete : function(ed, value, startValue){
        this.editing = false;
        this.activeEditor = null;
        ed.un("specialkey", this.selModel.onEditorKey, this.selModel);
		var r = ed.record;
        var field = this.colModel.getDataIndex(ed.col);
        value = this.postEditValue(value, startValue, r, field);
		
		var rawvalue = value;
		if (typeof ed.field.getFieldValue == 'function' && !Ext.isEmpty(ed.field.displayField)) {
			rawvalue = ed.field.getFieldValue(ed.field.displayField);
		} else if (typeof ed.field.getRawValue == 'function') {
			rawvalue = ed.field.getRawValue();
		}
		if (ed.field.useRawValueForGrid && typeof ed.field.getRawValue == 'function') {
			rawvalue = ed.field.getRawValue();
			value = rawvalue;
		}
        if(String(value) !== String(startValue) || (ed.field.fireAfterEditOnEmpty === true && String(value) == '' && String(startValue) == '') ){
            var e = {
                grid: this,
                record: r,
                field: field,
                originalValue: startValue,
                value: value,
				rawvalue: rawvalue,
                row: ed.row,
                column: ed.col,
                cancel:false
            };
            if(this.fireEvent("validateedit", e) !== false && !e.cancel){
                r.set(field, e.value);
                delete e.cancel;
                this.fireEvent("afteredit", e);
            }
        }
        this.view.focusCell(ed.row, ed.col);
    }
});


// Для того чтобы окна не убегали за клиентскую область
Ext.override(Ext.Window, {
	// start: 6-ой экст не хочет дружить с окнами второго экста, пытаемся подружить
	activeCounter: 0,
	setActiveCounter: function(activeCounter) {
		this.activeCounter = activeCounter;

		// Rebase the local zIndices
		var me = this;
		var zIndexManager = me.zIndexManager;
		if (zIndexManager) {
			zIndexManager.onComponentUpdate(me);
		}
	},
	getActiveCounter: function() {
		return this.activeCounter;
	},
	isFloating: function() {
		return true;
	},
	toFrontOnShow: true,
	isFocusable: function() {
		return true;
	},
	focusOnToFront: true,
	onZIndexChange: function(isTopMost) {
		// для совместимости с ExtJS 6.5
		var me = this;
		if (isTopMost) {
			me.fireEvent('activate', me);
		} else {
			me.fireEvent('deactivate', me);
		}
	},
	onFocusTopmost: function() {
		// для совместимости с ExtJS 6.5
	},
	toFront: function(preventFocus) {
		var me = this;
		// ZIndexManager#onCollectionSort will call setActive if this component ends up on the top.
		// That will focus it if we have been requested to do so.
		if (me.zIndexManager) {
			me.zIndexManager.bringToFront(me, preventFocus || !me.focusOnToFront);
		}
		return me;
	},
	// end: 6-ой экст не хочет дружить с окнами второго экста, пытаемся подружить
	show : function(animateTarget, cb, scope){
		if(!this.rendered){
			this.render(Ext.getBody());
		}
		if(this.hidden === false){
			this.toFront();
			return;
		}
		if(this.fireEvent("beforeshow", this) === false){
			return;
		}
		if(cb){
			this.on('show', cb, scope, {single:true});
		}
		this.hidden = false;
		if(animateTarget !== undefined){
			this.setAnimateTarget(animateTarget);
		}
		this.beforeShow();
		if(this.animateTarget){
			this.animShow();
		}else{
			this.afterShow();
		}

		// 6-ой экст не хочет дружить с окнами второго экста, пытаемся подружить
		var me = this;
		var zIndexManager = me.zIndexManager;
		if (zIndexManager) {
			zIndexManager.onComponentShowHide(me);
		}
	},
	hide : function(animateTarget, cb, scope){
		if(this.activeGhost){ // drag active?
			this.hide.defer(100, this, [animateTarget, cb, scope]);
			return;
		}
		if(this.hidden || this.fireEvent("beforehide", this) === false){
			return;
		}
		if(cb){
			this.on('hide', cb, scope, {single:true});
		}
		this.hidden = true;
		if(animateTarget !== undefined){
			this.setAnimateTarget(animateTarget);
		}
		if(this.animateTarget){
			this.animHide();
		}else{
			this.el.hide();
			this.afterHide();
		}

		// 6-ой экст не хочет дружить с окнами второго экста, пытаемся подружить
		var me = this;
		var zIndexManager = me.zIndexManager;
		if (zIndexManager) {
			zIndexManager.onComponentShowHide(me);
		}
	},
	onRender : function(ct, position){
		Ext.Window.superclass.onRender.call(this, ct, position);

		if(this.plain){
			this.el.addClass('x-window-plain');
		}

		// this element allows the Window to be focused for keyboard events
		this.focusEl = this.el.createChild({
			tag: "a", href:"#", cls:"x-dlg-focus",
			tabIndex:"-1", html: "&#160;"});
		this.focusEl.swallowEvent('click', true);

		this.proxy = this.el.createProxy("x-window-proxy");
		this.proxy.enableDisplayMode('block');

		if(this.modal){
			this.mask = this.container.createChild({cls:"ext-el-mask"}, this.el.dom);
			this.mask.enableDisplayMode("block");
			this.mask.hide();
			this.mask.on('click', this.focus, this);
		}
		this.initTools();

		if (TEST_ID_ENABLED) {
			var xtype = 'win';
			var name = '';
			if (this.name) {
				name = this.name;
			} else if (this.objectClass) {
				name = this.objectClass;
			} else if (this.id) {
				name = this.id;
			}

			var test_id = xtype;
			if (name.indexOf('ext-') < 0) {
				test_id = test_id + '_' + name;
			}
			test_id = test_id.replace(/<\/?[^>]+>/gi, '');
			test_id = test_id.replace(/\./gi, '');
			this.el.setAttribute('test_id', test_id);

			this.el.query('[test_id]').forEach(function(el) {
				var child_test_id = el.getAttribute('test_id');
				child_test_id = test_id + '_' + child_test_id;
				el.setAttribute('test_id', child_test_id);
			});
		}
	},
    constrain: true,
	// для менджера окон 4 экста надо чтобы возвращался index.
	setZIndex : function(index) {
		if(this.modal){
			this.mask.setStyle("z-index", index);
		}
		this.el.setZIndex(++index);
		index += 5;

		if(this.resizer){
			this.resizer.proxy.setStyle("z-index", ++index);
		}

		this.lastZIndex = index;

		return index;
	},
	// sw.WindowMgr вместо Ext.WindowMgr
	initEvents : function(){
		Ext.Window.superclass.initEvents.call(this);
		if(this.animateTarget){
			this.setAnimateTarget(this.animateTarget);
		}

		if(this.resizable){
			this.resizer = new Ext.Resizable(this.el, {
				minWidth: this.minWidth,
				minHeight:this.minHeight,
				handles: this.resizeHandles || "all",
				pinned: true,
				resizeElement : this.resizerAction
			});
			this.resizer.window = this;
			this.resizer.on("beforeresize", this.beforeResize, this);
		}

		if(this.draggable){
			this.header.addClass("x-window-draggable");
		}
		this.el.on("mousedown", this.toFront, this);
		this.manager = this.manager || sw.WindowMgr;
		this.manager.register(this);
		this.hidden = true;
		if(this.maximized){
			this.maximized = false;
			this.maximize();
		}
		if(this.closable){
			var km = this.getKeyMap();
			km.on(27, this.onEsc, this);
			km.disable();
		}
	}
});

//Смена тайтла компонента на лету 
Ext.override(Ext.form.Field, {
  setFieldLabel : function(text) {
    if (this.rendered) {
      this.el.up('.x-form-item', 10, true).child('.x-form-item-label').update(text);
    }
    this.fieldLabel = text;
  }
});

//Учёт параметра, регулирующего автоматического скроллирования грида в начало при перезагрузке данных
Ext.override(Ext.grid.GridView, {
	// private
	onLoad : function(){
		if (this.isScrollToTopOnLoad) {
			this.scrollToTop();
		}
	},
	// private
	getColumnStyle : function(col, isHeader){
        var style = !isHeader ? (this.cm.config[col].css || '') : '';
        style += 'width:'+this.getColumnWidth(col)+';';
        if(this.cm.isHidden(col)){
            style += 'display:none;';
        }
        var align = this.cm.config[col].align;
		if (isHeader && this.cm.config[col].headerAlign) {
			align = this.cm.config[col].headerAlign;
		}
        if(align){
            style += 'text-align:'+align+';';
        }
        return style;
    }
});

// В ридере getId определяется немного не так как нужно, ведь можно определять id по key
Ext.override(Ext.data.JsonReader, {
	// private
	readRecords : function(o){
        
        this.jsonData = o;
        if(o.metaData){
            delete this.ef;
            this.meta = o.metaData;
            this.recordType = Ext.data.Record.create(o.metaData.fields);
            this.onMetaChange(this.meta, this.recordType, o);
        }
        var s = this.meta, Record = this.recordType,
            f = Record.prototype.fields, fi = f.items, fl = f.length;
//      Generate extraction functions for the totalProperty, the root, the id, and for each field
        if (!this.ef) {
            if(s.totalProperty) {
	            this.getTotal = this.getJsonAccessor(s.totalProperty);
	        }
	        this.getOverLimit = this.getJsonAccessor('overLimit');
	        if(s.successProperty) {
	            this.getSuccess = this.getJsonAccessor(s.successProperty);
	        }
	        this.getRoot = s.root ? this.getJsonAccessor(s.root) : function(p){return p;};
	        if (s.id) {
	        	var g = this.getJsonAccessor(s.id);
	        	this.getId = function(rec) {
	        		var r = g(rec);
		        	return (r === undefined || r === "") ? null : r;
	        	};
	        } else {
	        	this.getId = function(rec) {
					if (s.key && rec && rec[s.key]) {
						var r = rec[s.key];
					}
					return (r === undefined || r === "") ? null : r;
				};
	        }
            this.ef = [];
            for(var i = 0; i < fl; i++){
                f = fi[i];
                var map = (f.mapping !== undefined && f.mapping !== null) ? f.mapping : f.name;
                this.ef[i] = this.getJsonAccessor(map);
            }
        }

    	var root = this.getRoot(o);
		if (root) {
			var	c = root.length, totalRecords = c, success = true, overLimit = false;
		} else {
			var	c = 0, totalRecords = c, success = true, overLimit = false;
		}
    	if(s.totalProperty){
            var v = parseInt(this.getTotal(o), 10);
            if(!isNaN(v)){
                totalRecords = v;
            }
        }
        overLimit = this.getOverLimit(o)?true:false;
        if(s.successProperty){
            var v = this.getSuccess(o);
            if(v === false || v === 'false'){
                success = false;
            }
        }
		
		var records = [];
	    for(var i = 0; i < c; i++){
		    var n = root[i];
	        var values = {};
	        var id = this.getId(n);
	        for(var j = 0; j < fl; j++){
	            f = fi[j];
                var v = this.ef[j](n);
                values[f.name] = f.convert((v !== undefined) ? v : f.defaultValue, n);
	        }
	        var record = new Record(values, id);
	        record.json = n;
	        records[i] = record;
	    }
	    return {
	        success : success,
	        records : records,
	        totalRecords : totalRecords,
			overLimit : overLimit
	    };
    }
});

// https://redmine.swan.perm.ru/issues/49095
// Спасибо Хабру http://habrahabr.ru/post/241359/
Ext.override(Ext.form.TimeField, {
	initDate: '2/1/2008'
});

Ext.override(Ext.layout.ColumnLayout, {
	onLayout : function(ct, target){
		var cs = ct.items.items, len = cs.length, c, i;
		if(!this.innerCt){
			target.addClass('x-column-layout-ct');
			this.innerCt = target.createChild({cls:'x-column-inner'});
			this.innerCt.createChild({cls:'x-clear'});
		}
		this.renderAll(ct, this.innerCt);
		var size = Ext.isIE && target.dom != Ext.getBody().dom ? target.getStyleSize() : target.getViewSize();
		if(size.width < 1 || size.height < 1){ return; } //Изменил условие, т.к. getViewSize может вернуть width=1 и height=0
		var w = size.width - target.getPadding('lr') - this.scrollOffset,
			h = size.height - target.getPadding('tb'),
			pw = w;
		this.innerCt.setWidth(w);
		for(i = 0; i < len; i++){
			c = cs[i];
			if(!c.columnWidth && typeof c.getSize == 'function'){
				pw -= (c.getSize().width + c.getEl().getMargins('lr'));
			}
		}
		pw = pw < 0 ? 0 : pw;
		for(i = 0; i < len; i++){
			c = cs[i];
			if(c.columnWidth){
				c.setSize(Math.floor(c.columnWidth*pw) - c.getEl().getMargins('lr'));
			}
		}
	}
});

/**
 * Чтобы менюшки влазили в экран и прочая магия с менюшками перед их показом
 */
Ext.override(Ext.menu.Menu, {
	createEl : function(){
		var zseed = getActiveZIndex();
		return new Ext.Layer({
			cls: "x-menu",
			shadow:this.shadow,
			constrain: false,
			parentEl: this.parentEl || document.body,
			zindex: zseed + 15000
		});
	},
	show : function(el, pos, parentMenu){
		this.parentMenu = parentMenu;
		if(!this.el){
			this.render();
		}
		this.fireEvent("beforeshow", this);
		this.showAt(this.el.getAlignToXY(el, pos || this.defaultAlign), parentMenu, false, pos, el);
	},
	showAt : function(xy, parentMenu, _e, pos, el){
		this.parentMenu = parentMenu;
		if(!this.el){
			this.render();
		}
		if(_e !== false){
			this.fireEvent("beforeshow", this);
			xy = this.el.adjustForConstraints(xy);
		}
		this.el.setXY(xy);

		// изменяем высоту меню если меню больше чем рабочая область

		// определяем максимальный Top менюшки
		var maxTop = 2;
        var maxHeight = 100;
		// если меню вылезает вниз относительно заданного объекта (кнопки/ссылки), то пусть не перекрывает данный объект, поэтому максимальный Top ограничиваем
		if (el && (!pos || pos == 'tl-bl' || pos == 'tl-bl?')) {
			var maxTop = xy[1];
            maxHeight = Ext.getBody().getHeight() - 2 - maxTop;
		}
		else { //А иначе - пусть себе поднимается вверх, но с определенным ограничением на топ парента
			if(el && el.dom.parentElement){
				var children = el.dom.parentElement.childNodes; //Получаем все дочерние элементы у родитея
				var children_lenght = el.dom.parentElement.childNodes.length; //Количество дочерних элементов
				
				var el_id = el.dom.id; //идентификатор нашего элемента
				var i = 0;
				var el_index = 0;
				var el_top = el.getTop(); //положение по вертикали нашего элемента
				var height = el.getHeight(); //высота нашего элемента
				for (i = 0; i < children_lenght; i++) //пробегаемся по всем дочерним элементам родиеля и ищем наш по совпадению идентификаторов, чтоб узнать, на каком он месте в списке
				{
					if(children[i].id == el_id)
						el_index = i;
				}
				//определяем top для меню, учитывая, что он не должен быть выше top-а первого дочернего элемента в меню
				var FirstChildTop = el_top - el_index*height;
				maxTop = FirstChildTop;

				/*var menu_height = el.dom.parentElement.style.height;
				menu_height = menu_height.substring(0, menu_height.length-2);
				if(!Ext.isEmpty(menu_height))
				{
					maxHeight = + menu_height + 22;
				}
				else
				{
					maxHeight = Ext.getBody().getHeight() - 2 - maxTop;
				}*/
                maxHeight = Ext.getBody().getHeight() - 2 - maxTop - 36;
                //maxHeight = Ext.getBody().getHeight() - 2 - maxTop - 35;
			}
			else
				maxHeight = Ext.getBody().getHeight() - 2 - maxTop - 36;
		}
		//var maxHeight = Ext.getBody().getHeight() - 2 - maxTop;

		this.el.setHeight('auto');
		this.el.applyStyles('overflow:visible;');
		if (this.el.getHeight() > maxHeight) {
			this.el.setHeight(maxHeight);
			this.el.applyStyles('overflow:auto;');
		}

		var zseed = getActiveZIndex();
		this.el.setZIndex(zseed + 15000);

		// и чтобы менюшки двигались по высоте
		if (this.el.getHeight() + xy[1] - maxTop > maxHeight) {
			xy[1] = maxHeight - this.el.getHeight() + maxTop;
			this.el.setXY(xy);
		}

		this.el.show();
		this.hidden = false;
		this.focus();
		this.fireEvent("show", this);
	}
});

// Ensures that a Component is visible by walking up its ownerCt chain and activating any parent Container
// It also scrolls if needed
Ext.override(Ext.Component,{
	ensureVisible: function(stopAt){
		var p;
		this.ownerCt.bubble(function(c){
			if (p = c.ownerCt) {
				if (p instanceof Ext.TabPanel) {
					p.setActiveTab(c);
				} else if (p.layout.setActiveItem) {
					p.layout.setActiveItem(c);
				}
			}
			return (c !== stopAt);
		});
		return this;
	}
});

Ext.override(Ext.tree.TreeEventModel, {
	beforeEvent : function(e){
		if(this.disabled || !this.getNode(e)){
			e.stopEvent();
			return false;
		}
		return true;
	}
});

Ext.override(Ext.tree.TreeNode, {
	preventScroll: false
});

Ext.override(Ext.tree.TreeNodeUI, {
	focus: function() {
		if (!this.node.preventScroll) {
			if(!this.node.preventHScroll){
				try{this.anchor.focus();
				}catch(e){}
			}else{
				try{
					var noscroll = this.node.getOwnerTree().getTreeEl().dom;
					var l = noscroll.scrollLeft;
					this.anchor.focus();
					noscroll.scrollLeft = l;
				}catch(e){}
			}
		}
	}
});

Ext.override(Ext.data.Connection, {
	localCmpMethods: [ // методы, которые необходимо дублировать в локальную БД
		'setStatusCmpCallCard',
		'saveCmpCallCard'
	],
	checkUrlToLocalCmp: function(url) {
		var me = this;

		if (new RegExp(me.localCmpMethods.join("|")).test(url)) {
			return true;
		}

		return false;
	},
	setProgressHidden: function(hidden) {
		var _menu_progress = Ext.getCmp('_menu_progress');
		var _menu_easy_progress = Ext.getCmp('_menu_easy_progress');
		if (_menu_progress) {
			if (hidden) {
				var menu = _menu_progress;
				menu.setVisible(false);
				if (_menu_easy_progress) {
					_menu_easy_progress.setVisible(false);
				}
			} else {
				var menu = _menu_progress;
				menu.setVisible(true);
				if (_menu_easy_progress) {
					_menu_easy_progress.setVisible(true);
				}
			}
		}
	},
	handleResponse: function(response) {
		var me = this;

		this.setProgressHidden(true);
		this.transId = false;
		var options = response.argument.options;

		if (!options.toLocalCMPRedirect) {
			// сохранилось. надо сохранить и в локальный промед #90822
			if (options.toLocalCMP) {
				// если уже сохраняем в локальный, то калбэк не нужен
				options.callback = Ext.emptyFn;
				options.failure = Ext.emptyFn;
				options.success = Ext.emptyFn;
			} else if (!options.toLocalCMP && sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedService_IsLocalCMP && parseInt(sw.Promed.MedStaffFactByUser.last.MedService_IsLocalCMP) == 2) {
				if (me.checkUrlToLocalCmp(options.url)) { // нас интересуют только определённые методы сохранения в локальную БД.
					log('toLocalCMP: ', options.url, options);
					var local_url = sw.Promed.MedStaffFactByUser.last.MedService_LocalCMPPath; // урл локального веба
					options.toLocalCMP = true; // признак, что отправляем на локальный веб
					options.url = local_url + options.url; // меняем на локальный УРЛ.

					if (response.responseText && options.params) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result && result.CmpCallCard_id) {
							if (typeof options.params == 'object') {
								options.params.CmpCallCard_insID = result.CmpCallCard_id;
							} else {
								options.params = options.params + '&CmpCallCard_insID=' + result.CmpCallCard_id;
							}
						}
					}

					me.request(options); // запускаем запрос повторно
				}
			}
		}

		response.argument = options ? options.argument : null;
		this.fireEvent("requestcomplete", this, response, options);
		// Единая обработка ошибок.
		// Если с сервера приходит неверно сформированный объект JSON
		// то выдаем ошибку и прекращаем обработку
		// Если с сервера приходит массив,
		// в котором есть параметр success = false
		// то выдается окно с описанием ошибки, берущееся из
		// параметра Error_Msg
		var err = 0;
		var result = undefined;
		if (response.responseText.length > 0 && response.responseText.indexOf('/*NO PARSE JSON*/') == -1 && response.responseText.indexOf('sw.Promed.') == -1) {
			try {
				result = Ext.util.JSON.decode(response.responseText);
			} catch (e) {
				var link = langs('ответ сервера');
				if (true || isAdmin) { // открыл для всех, всё равно можно вытащить из консоли ответ, а для понимания ошибок наличие ответа всегда важно.
					sw.responseServer = '<pre>' + response.responseText + '</pre>';
					link = '<a href="_blank" onclick="openNewWindow(sw.responseServer);return false;">ответ сервера</a>';
				}
				
				if (getRegionNick() == 'vologda') {
					if (this.usedUrl != '/?c=Common&m=saveSystemError') {
						// собираем инфу и сохраняем в БД.
						var techInfo = getPromedTechInfo();

						Ext.Ajax.request({
							url: '/?c=Common&m=saveSystemError',
							params: {
								techInfo: Ext.util.JSONalt.encode(techInfo, 0, 6),
								error: response.responseText,
								window: techInfo.currentWindow,
								url: this.usedUrl,
								params: Ext.util.JSON.encode(this.usedParams)
							},
							callback: function(opt, success, response) {
								var text = '';
								if (success && response.responseText != '') {
									var result = Ext.util.JSON.decode(response.responseText);
									if (IS_DEBUG && result.num) {
										text = langs('Номер ошибки: ') + result.num + '<br/>';
									}
								}
								if(!options.withoutErrorMsgBox)
									sw.swMsg.alert(langs('Ошибка'), langs('Неверно сформированный ') + link + '!<br />' + text + langs('Обратитесь к разработчикам программы.'));
							}
						});
					}
				} else {
					if(!options.withoutErrorMsgBox)
						sw.swMsg.alert(langs('Ошибка'), langs('Неверно сформированный ') + link + '!<br />' + langs('Обратитесь к разработчикам программы.'));
				}
				err = 1;
			}
			// Выход теперь возможен и без уведомления
			if (result != undefined && result.Action != undefined) {
				if (result.Action == 'logout') {
					window.onbeforeunload = null;
					window.location = C_LOGOUT_ERROR;
				}
			}
			// Ванина проверка
			if (result != undefined && result.success != undefined && result.Error_Msg != undefined && result.Cancel_Error_Handle == undefined) {
				if (!result.success && result.cancelErrorHandle == undefined && !options.withoutErrorMsgBox) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							if (result.Action != undefined) {
								if (result.Action == 'logout') {
									window.onbeforeunload = null;
									window.location = C_LOGOUT;
								}
							}
						},
						icon: Ext.Msg.WARNING,
						title: langs('Ошибка'),
						msg: result.Error_Msg
					});
				}
			}
			if (result != undefined && result.success != undefined /*&& result.Alert_Msg == undefined*/) {
				if (!Ext.isEmpty(result.Warning_Msg)) {
					showPopupWarningMsg(result.Warning_Msg);
				}
				if (!Ext.isEmpty(result.Info_Msg)) {
					showPopupInfoMsg(result.Info_Msg);
				}
			}
			// проверка IVP
			if (result != undefined && result.Response_Data != undefined) {
				// проверяем, если пришло Cancel_Error_Handle и оно равно true, то все передаем в callback вызывающей функции
				// если false, то сами обрабатываем ошибку и выдаем сообщение
				if (result.Cancel_Error_Handle != undefined && !result.Cancel_Error_Handle) {
					if (result.success != undefined && !result.success) {
						sw.swMsg.alert(langs('Ошибка'), result.Error_Msg,
							function() {
								if (result.Action != undefined) {
									if (result.Action == 'logout') {
										window.onbeforeunload = null;
										window.location = C_LOGOUT;
									}
								}
							}
						);
					}
					response.responseText = Ext.util.JSON.encode(result.Response_Data, true);
				}
			}
		}
		// Если ошибок нет, то продолжаем обработку успешного выполнения
		if (err == 0) {
			Ext.callback(options.success, options.scope, [response, options]);
			Ext.callback(options.callback, options.scope, [options, true, response]);
		}
		else { //иначе вызываем обработку ошибки
			Ext.callback(options.failure, options.scope, [response, options]);
			Ext.callback(options.callback, options.scope, [options, false, response]);
		}
	},
	handleResponseWithoutErrors: function(response) {
		this.setProgressHidden(true);
		this.transId = false;
		var options = response.argument.options;
		response.argument = options ? options.argument : null;
		this.fireEvent("requestcomplete", this, response, options);
		Ext.callback(options.success, options.scope, [response, options]);
		Ext.callback(options.callback, options.scope, [options, true, response]);
	},
	handleFailure: function(response, e) {
		this.setProgressHidden(true);
		this.transId = false;
//		sw.swMsg.alert('Ошибка', 'В результате выполнения запроса к серверу произошла ошибка: "' + response.statusText + '".');
		var options = response.argument.options;
		response.argument = options ? options.argument : null;
		this.fireEvent("requestexception", this, response, options, e);
		Ext.callback(options.failure, options.scope, [response, options]);
		Ext.callback(options.callback, options.scope, [options, false, response]);
	},
	request: function(o) {
		var me = this;

		this.setProgressHidden(false);
		// Не показывать messageBox с ошибкой автоматически
		o.withoutErrorMsgBox = o.withoutErrorMsgBox || false;
		// если не пинг запрос
		if (!o.ignoreCheckConnection && !o.isPingRequest && !o.toLocalCMP) {
			if (sw.lostConnection) {
				// если соединение с основным сервером потеряно, то все запросы переадресуются на локальный веб
				if (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedService_IsLocalCMP && parseInt(sw.Promed.MedStaffFactByUser.last.MedService_IsLocalCMP) == 2) {
					var local_url = sw.Promed.MedStaffFactByUser.last.MedService_LocalCMPPath; // урл локального веба
					if (!o.url) {
						o.url = this.url;
					}
					o.url = local_url + o.url; // меняем на локальный УРЛ.
					o.toLocalCMPRedirect = true;
				}
			}

			o.ignoreCheckConnection = true;
			return me.request(o);
		} else {
			return me.requestAdditional(o);
		}
	},
	requestAdditional: function(o) {
		if (this.fireEvent("beforerequest", this, o) !== false) {
			var p = o.params;

			if (typeof p == "function") {
				p = p.call(o.scope || window, o);
			}
			if (typeof p == "object") {
				p = Ext.urlEncode(p);
			}
			if (this.extraParams) {
				var extras = Ext.urlEncode(this.extraParams);
				p = p ? p + "&" + extras : extras;
			}

			var showErrors = true;
			if (typeof o.showErrors == "boolean") {
				showErrors = o.showErrors;
			}

			var url = o.url || this.url;
			if (typeof url == "function") {
				url = url.call(o.scope || window, o);
			}
			this.usedUrl = url;

			if (o.form) {
				var form = Ext.getDom(o.form);
				url = url || form.action;
				var enctype = form.getAttribute("enctype");
				if (o.isUpload ||
					(enctype && enctype.toLowerCase() == "multipart/form-data")) {
					return this.doFormUpload(o, p, url);
				}
				var f = Ext.lib.Ajax.serializeForm(form);
				p = p ? p + "&" + f : f;
			}
			var hs = o.headers;
			if (this.defaultHeaders) {
				hs = Ext.apply(hs || {}, this.defaultHeaders);
				if (!o.headers) {
					o.headers = hs;
				}
			}
			var cb = {
				success: this.handleResponse,
				failure: this.handleFailure,
				scope: this,
				argument: {options: o},
				timeout: o.timeout || this.timeout
			};

			if (!showErrors) {
				cb.success = this.handleResponseWithoutErrors;
			}

			var method = o.method ||
				this.method ||
				((p || o.xmlData || o.jsonData) ? "POST" : "GET");

			if (method == "GET" &&
				(this.disableCaching && o.disableCaching !== false) ||
				o.disableCaching === true) {
				var dcp = o.disableCachingParam || this.disableCachingParam;
				url += (url.indexOf("?") != -1 ? "&" : "?") + dcp + "=" + (new Date).getTime();
			}
			if (typeof o.autoAbort == "boolean") {
				if (o.autoAbort) {
					this.abort();
				}
			} else if (this.autoAbort !== false) {
				this.abort();
			}
			if ((method == "GET" || o.xmlData || o.jsonData) && p) {
				url += (url.indexOf("?") != -1 ? "&" : "?") + p;
				p = "";
			}
			this.usedParams = p;

			this.transId = Ext.lib.Ajax.request(method, url, cb, p, o);
			return this.transId;
		} else {
			Ext.callback(o.callback, o.scope, [o, null, null]);
			return null;
		}
	}
});