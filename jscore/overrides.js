/**
*  Переопределения и добавления новых функций в общие базовые классы (не зависящие от ExtJS).
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

Date.prototype.lastday = function() {
	var d = new Date(this.getFullYear(), this.getMonth() + 1, 0);
	return d.getDate();
};

Date.prototype.getMonthsBetween = function(d) {
	var sDate, eDate;
	var d1 = this.getFullYear() * 12 + this.getMonth();
	var d2 = d.getFullYear() * 12 + d.getMonth();
	var sign;
	var months = 0;

	if (this == d) {
		months = 0;
	} else if (d1 == d2) { //тот же год и месяц
		months = (d.getDate() - this.getDate()) / this.lastday();
	} else {
		if (d1 <  d2) {
			sDate = this;
			eDate = d;
			sign = 1;
		} else {
			sDate = d;
			eDate = this;
			sign = -1;
		}

		var sAdj = sDate.lastday() - sDate.getDate();
		var eAdj = eDate.getDate();
		var adj = (sAdj + eAdj) / sDate.lastday() - 1;
		months = Math.abs(d2 - d1) + adj;
		months = (months * sign)
	}
	return months;
};

Array.prototype.in_array = function(val) {
	for(var i = 0, l = this.length; i < l; i++) {
		if(this[i] == val) {
			return true;
		}
	}
	return false;
};

if (!Array.prototype.find) {
	Array.prototype.find = function(predicate) {
		if (this == null) {
			throw new TypeError('Array.prototype.find called on null or undefined');
		}
		if (typeof predicate !== 'function') {
			throw new TypeError('predicate must be a function');
		}
		var list = Object(this);
		var length = list.length >>> 0;
		var thisArg = arguments[1];
		var value;

		for (var i = 0; i < length; i++) {
			value = list[i];
			if (predicate.call(thisArg, value, i, list)) {
				return value;
			}
		}
		return undefined;
	};
}