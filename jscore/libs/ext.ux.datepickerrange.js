/**
 *
 * ext.ux.datepickerrange.js
 *
 * Its an extension of the datepicker but now you can set a selection mode ('day', 'week', 'month').
 * @author cocorossello
 * http://www.extjs.com/forum/showthread.php?t=22473
 *
 */
Ext.namespace('Ext.ux');

Date.prototype.getFirstDateOfWeek = function(){
    var value = this.clone();
    var dayOfWeek = this.getDay();
    dayOfWeek = (dayOfWeek + 6) % 7;
    value.setDate(value.getDate() - dayOfWeek);
    return value;
}


Ext.ux.DatePickerRange = Ext.extend(Ext.DatePicker, {
    selectionMode: 'month',
    amount: 1,
    setSelectionMode: function(mode){
        this.selectionMode = mode;
        this.setValue(this.value);
        //Update todayBtn Text on mode change
        this.todayBtn.setText(this._TodayText());
    },
    
    getSelectionMode: function(){
        return this.selectionMode();
    },
    
    handleDateClick: function(e, t){
        e.stopEvent();
        if (t.dateValue && !Ext.fly(t.parentNode).hasClass("x-date-disabled")) {
            if (Ext.EventObject.shiftKey) {
                if (t.dateValue && t.dateValue >= this.activeDate) {
                
                    var d = new Date(t.dateValue);
                    var DAY = 86400000;
                    
                    if (this.selectionMode == 'day') {
                        this.amount = ((d.getTime() - this.activeDate.getTime()) / DAY) + 1;
                        //if(this.selectionMode=='month')
                        //	this.amount = 1;
                    }
                    else 
                        if (this.selectionMode == 'week') {
                            var days = d.getFirstDateOfWeek().getTime() - this.activeDate.getFirstDateOfWeek().getTime() + DAY;
                            this.amount = Math.ceil(days / (DAY * 7));
                        }
                    
                    this.setValue(new Date(this.value));
                }
            }
            else {
                this.amount = 1;
                this.setValue(new Date(t.dateValue));
            }
            this.fireEvent("select", this, this.value, this.amount);
        }
    },
    
    //private
    update: function(date){
        var vd = this.activeDate;
        this.activeDate = date;
        if (vd && this.el) {
            var t = date.getTime();
            if (vd.getMonth() == date.getMonth() && vd.getFullYear() == date.getFullYear()) {
                this.cells.removeClass("x-date-selected");
                this.cells.each(function(c){
                    if (this.isSelected(c.dom.firstChild.dateValue)) {
                        c.addClass("x-date-selected");
                    }
                }, this);
                return;
            }
        }
        var days = date.getDaysInMonth();
        var firstOfMonth = date.getFirstDateOfMonth();
        var startingPos = firstOfMonth.getDay() - this.startDay;
        
        if (startingPos <= this.startDay) {
            startingPos += 7;
        }
        
        var pm = date.add("mo", -1);
        var prevStart = pm.getDaysInMonth() - startingPos;
        
        var cells = this.cells.elements;
        var textEls = this.textNodes;
        days += startingPos;
        
        
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
        
        var setCellClass = function(cal, cell){
            cell.title = "";
            var t = d.getTime();
            cell.firstChild.dateValue = t;
            if (t == today) {
                cell.className += " x-date-today";
                cell.title = cal.todayText;
            }
            if (cal.isSelected(cell.firstChild.dateValue)) {
                cell.className += " x-date-selected";
            }
            
            if (t < min) {
                cell.className = " x-date-disabled";
                cell.title = cal.minText;
                return;
            }
            if (t > max) {
                cell.className = " x-date-disabled";
                cell.title = cal.maxText;
                return;
            }
            if (ddays) {
                if (ddays.indexOf(d.getDay()) != -1) {
                    cell.title = ddaysText;
                    cell.className = " x-date-disabled";
                }
            }
            if (ddMatch && format) {
                var fvalue = d.dateFormat(format);
                if (ddMatch.test(fvalue)) {
                    cell.title = ddText.replace("%0", fvalue);
                    cell.className = " x-date-disabled";
                }
            }
        };
        
        var i = 0;
        for (; i < startingPos; i++) {
            textEls[i].innerHTML = (++prevStart);
            d.setDate(d.getDate() + 1);
            cells[i].className = "x-date-prevday";
            setCellClass(this, cells[i]);
        }
        
        for (; i < days; i++) {
            intDay = i - startingPos + 1;
            textEls[i].innerHTML = (intDay);
            d.setDate(d.getDate() + 1);
            cells[i].className = "x-date-active";
            setCellClass(this, cells[i]);
        }
        var extraDays = 0;
        for (; i < 42; i++) {
            textEls[i].innerHTML = (++extraDays);
            d.setDate(d.getDate() + 1);
            cells[i].className = "x-date-nextday";
            setCellClass(this, cells[i]);
        }
        
        this.mbtn.setText(this.monthNames[date.getMonth()] + " " + date.getFullYear());
        
        if (!this.internalRender) {
            var main = this.el.dom.firstChild;
            var w = main.offsetWidth;
            this.el.setWidth(w + this.el.getBorderWidth("lr"));
            Ext.fly(main).setWidth(w);
            this.internalRender = true;
            
            if (Ext.isOpera && !this.secondPass) {
                main.rows[0].cells[1].style.width = (w - (main.rows[0].cells[0].offsetWidth + main.rows[0].cells[2].offsetWidth)) + "px";
                this.secondPass = true;
                this.update.defer(10, this, [date]);
            }
        }
    },
    
    isSelected: function(date){
        // date to test
        date = new Date(date).clearTime();
        // activeDate
        ad = new Date(this.value).clearTime();
        var DAY = 86400000;
        
        if (this.selectionMode == 'day') {
            date = date.getTime()
            for (var i = 0; i < this.amount; ++i) {
                if (date == ad.add(Date.DAY, i).getTime()) 
                    return true;
            }
            return false;
        }
        if (this.selectionMode == 'month') 
            return date.getFirstDateOfMonth().getTime() == this.value.getFirstDateOfMonth().getTime();
        
        if (this.selectionMode == 'week') {
            var refTime = date.getFirstDateOfWeek().getTime();
            var ad = ad.getFirstDateOfWeek();
            
            if (refTime == ad.getTime()) 
                return true;
            
            for (var i = 1; i < this.amount; ++i) {
                ad.setDate(ad.getDate() + 7);
                if (refTime == ad.getTime()) 
                    return true;
            }
            return false;
        }
        throw 'Illegal selection mode';
        
    },
    
    getValues: function(startEnd, unix_timestamp){
        //arguments
        startEnd = (startEnd === undefined) ? false : startEnd;
        unix_timestamp = (unix_timestamp === undefined) ? false : unix_timestamp;
        //predefined data
        var currentAmount = this.amount;
        var currentValue = this.value;
        var currentDate = new Date(currentValue);
        var date = new Date(currentDate);
        //private data
        var selectedDates = new Array();
        var push = false;
        var tmp_date = null;
        
        switch (this.selectionMode) {
            case 'week':
                currentAmount *= 7;
                currentDate = date.getFirstDateOfWeek();
                break;
                
            case 'month':
                currentAmount *= date.getDaysInMonth();
                currentDate = date.getFirstDateOfMonth();
                break;
                
            default:
        }
        
        for (var i = 0; i < currentAmount; i++) {
            push = (startEnd) ? ((i === 0 || (i + 1) === currentAmount) ? true : false) : true;
            if (push) {
                tmp_date = currentDate.add(Date.DAY, i).getTime();
                tmp_date = (unix_timestamp) ? parseInt(tmp_date / 1000) : tmp_date;
                selectedDates.push(tmp_date);
                tmp_date = null;
            }
        }
        
        return selectedDates;
    },
    
    selectToday: function(){
        if (this.todayBtn && !this.todayBtn.disabled) {
            this.amount = 1;
            this.setValue(new Date().clearTime());
            this.fireEvent("select", this, this.value);
        }
    },
    
    selectPrevDay: function(){
        this.amount = 1;
        this.setValue(this.value.add(Date.DAY, -1).clearTime());
        this.fireEvent("select", this, this.value);
    },
    
    selectNextDay: function(){
        this.amount = 1;
        this.setValue(this.value.add(Date.DAY, 1).clearTime());
        this.fireEvent("select", this, this.value);
    },
    
    _TodayText: function(){
//        var todayText = 'Current ';
        switch (this.selectionMode) {
            case 'day':
                todayText = langs('Сегодня');
                break;
                
            case 'week':
                todayText = langs('Текущая неделя');
                break;
                
            case 'month':
            default:
                todayText = langs('Текущий месяц');
        }
        return todayText;
    },
    
    initComponent: function(){
        this.todayText = this._TodayText();
        Ext.ux.DatePickerRange.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('datepickerrange', Ext.ux.DatePickerRange);
