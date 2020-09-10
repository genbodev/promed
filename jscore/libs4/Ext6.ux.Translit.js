/**
 * ext.Ux.Translit - плагин для автоматического транслита в полях ввода.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      libs
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
 * @version      25.05.2009
 */

/**
 * Плагин для перевода в русскую раскладку
 * @param {Boolean} toRus Перевести в рускую раскладку
 * @param {Boolean} toUpperCase Привести строку к верхнему регистру
 */

Ext6.ux.Translit = function(toRus, toUpperCase) {
	this.constructor(toRus, toUpperCase);
};

Ext6.ux.Translit.prototype = {
	pluginId: 'Translit',
	constructor: function(toRus, toUpperCase) {
		this.toRus = toRus;
		this.toUpperCase = toUpperCase;

		return this;
	},
	toRus: false,
	toUpperCase: false,
	init: function(field) {
		this.field = field;
		if (!this.toRus) {
			// с русского на латиницу
			this.tArr = [];
			this.tArr['а'] = 'f';
			this.tArr['А'] = 'F';
			this.tArr['б'] = ',';
			this.tArr['Б'] = '<';
			this.tArr['в'] = 'd';
			this.tArr['BD'] = 'В';
			this.tArr['г'] = 'u';
			this.tArr['Г'] = 'U';
			this.tArr['д'] = 'l';
			this.tArr['Д'] = 'L';
			this.tArr['е'] = 't';
			this.tArr['Е'] = 'T';
			this.tArr['ё'] = '`';
			this.tArr['Ё'] = '~';
			this.tArr['ж'] = ';';
			this.tArr['Ж'] = ':';
			this.tArr['з'] = 'p';
			this.tArr['З'] = 'P';
			this.tArr['и'] = 'b';
			this.tArr['И'] = 'B';
			this.tArr['й'] = 'q';
			this.tArr['Й'] = 'Q';
			this.tArr['к'] = 'r';
			this.tArr['К'] = 'R';
			this.tArr['л'] = 'k';
			this.tArr['Л'] = 'K';
			this.tArr['м'] = 'v';
			this.tArr['М'] = 'V';
			this.tArr['н'] = 'y';
			this.tArr['Н'] = 'Y';
			this.tArr['о'] = 'j';
			this.tArr['О'] = 'J';
			this.tArr['п'] = 'g';
			this.tArr['П'] = 'G';
			this.tArr['р'] = 'h';
			this.tArr['Р'] = 'H';
			this.tArr['с'] = 'c';
			this.tArr['С'] = 'C';
			this.tArr['т'] = 'n';
			this.tArr['Т'] = 'N';
			this.tArr['у'] = 'e';
			this.tArr['У'] = 'E';
			this.tArr['ф'] = 'a';
			this.tArr['Ф'] = 'A';
			this.tArr['х'] = '[';
			this.tArr['Х'] = '{';
			this.tArr['ц'] = 'w';
			this.tArr['Ц'] = 'W';
			this.tArr['ч'] = 'x';
			this.tArr['Ч'] = 'X';
			this.tArr['ш'] = 'i';
			this.tArr['Ш'] = 'I';
			this.tArr['щ'] = 'o';
			this.tArr['Щ'] = 'O';
			this.tArr['ь'] = 'm';
			this.tArr['Ь'] = 'M';
			this.tArr['ы'] = 's';
			this.tArr['Ы'] = 'S';
			this.tArr['ъ'] = ']';
			this.tArr['Ъ'] = '}';
			this.tArr['э'] = '\'';
			this.tArr['Э'] = '\"';
			this.tArr['ю'] = '.';
			this.tArr['Ю'] = '>';
			this.tArr['я'] = 'z';
			this.tArr['Я'] = 'Z';
		} else {
			// с латиницы на русский
			this.tArr = [];
			this.tArr['f'] = 'а';
			this.tArr['F'] = 'А';
			this.tArr[','] = 'б';
			this.tArr['<'] = 'Б';
			this.tArr['d'] = 'в';
			this.tArr['D'] = 'В';
			this.tArr['u'] = 'г';
			this.tArr['U'] = 'Г';
			this.tArr['l'] = 'д';
			this.tArr['L'] = 'Д';
			this.tArr['t'] = 'е';
			this.tArr['T'] = 'Е';
			this.tArr['`'] = 'ё';
			this.tArr['~'] = 'Ё';
			this.tArr[';'] = 'ж';
			this.tArr[':'] = 'Ж';
			this.tArr['p'] = 'з';
			this.tArr['P'] = 'З';
			this.tArr['b'] = 'и';
			this.tArr['B'] = 'И';
			this.tArr['q'] = 'й';
			this.tArr['Q'] = 'Й';
			this.tArr['r'] = 'к';
			this.tArr['R'] = 'К';
			this.tArr['k'] = 'л';
			this.tArr['K'] = 'Л';
			this.tArr['v'] = 'м';
			this.tArr['V'] = 'М';
			this.tArr['y'] = 'н';
			this.tArr['Y'] = 'Н';
			this.tArr['j'] = 'о';
			this.tArr['J'] = 'О';
			this.tArr['g'] = 'п';
			this.tArr['G'] = 'П';
			this.tArr['h'] = 'р';
			this.tArr['H'] = 'Р';
			this.tArr['c'] = 'с';
			this.tArr['C'] = 'С';
			this.tArr['n'] = 'т';
			this.tArr['N'] = 'Т';
			this.tArr['e'] = 'у';
			this.tArr['E'] = 'У';
			this.tArr['a'] = 'ф';
			this.tArr['A'] = 'Ф';
			this.tArr['['] = 'х';
			this.tArr['{'] = 'Х';
			this.tArr['w'] = 'ц';
			this.tArr['W'] = 'Ц';
			this.tArr['x'] = 'ч';
			this.tArr['X'] = 'Ч';
			this.tArr['i'] = 'ш';
			this.tArr['I'] = 'Ш';
			this.tArr['o'] = 'щ';
			this.tArr['O'] = 'Щ';
			this.tArr['m'] = 'ь';
			this.tArr['M'] = 'Ь';
			this.tArr['s'] = 'ы';
			this.tArr['S'] = 'Ы';
			this.tArr[']'] = 'ъ';
			this.tArr['}'] = 'Ъ';
			this.tArr['\''] = 'э';
			this.tArr['\"'] = 'Э';
			this.tArr['.'] = 'ю';
			this.tArr['>'] = 'Ю';
			this.tArr['z'] = 'я';
			this.tArr['Z'] = 'Я';
		}

		field.enableKeyEvents = true;
		if (field.rendered) {
			this.assignEl(field);
		} else {
			field.on('render', this.assignEl, field);
		}
	},
	assignEl: function(f) {
		var obj = f;
		var plug = f.getPlugin('Translit');

		if (!obj.getEl()) return;
		obj.getEl().on('keyup', function(e,input) {
			if (obj.disableTransPlug === true) return;

			if (e.getCharCode() == 8 ||
				(e.getCharCode() > 36 && e.getCharCode() < 41) ||
				e.getCharCode() == e.DELETE ||
				e.getCharCode() == e.HOME ||
				e.getCharCode() == e.END ||
				e.getCharCode() == e.CTRL ||
				e.getCharCode() == e.ALT ||
				e.getCharCode() == e.SHIFT ||
				e.getCharCode() == e.TAB ||
				e.getCharCode() == 13
			) {
				return;
			}

			var s = "";
			var inputText = null;
			if (typeof obj.getRawValue == 'function') {
				inputText = obj.getRawValue();
			}
			else if (typeof obj.getValue == 'function') {
				inputText = obj.getValue();
			}
			else if (obj.value) {
				inputText = obj.value;
			}
			if(obj.getXType()=='swDiagTagCombo')
				inputText = input.value;
			if (inputText) {
				var limit = -1; // ограничение транслита
				if (obj.xtype && (obj.xtype == 'swDiagCombo' || obj.for === 'diag' || obj.getXType()=='swDiagTagCombo')) {
					// если второй символ цифра, значит транслит нужен, иначе нет
					if (inputText.length < 2 || isNaN(inputText[1])) {
						return;
					}

					// транслит нужен только до пробела
					limit = inputText.indexOf(" ");
				}
				for (var i = 0; i <= (inputText.length); i++) {
					if (typeof(plug.tArr[inputText.substr(i, 1)]) != 'undefined' && (limit < 0 || i < limit))
						s = s.concat(plug.tArr[inputText.substr(i, 1)]);
					else
						s = s.concat(inputText.substr(i, 1));
				}
				if (obj.xtype && (obj.xtype == 'swDiagCombo' || obj.for === 'diag' || obj.getXType()=='swDiagTagCombo') ) {
					// если 4 символ есть и это не точка, то перед ним нужна точка
					if (s.length >= 4 && s[3] != '.') {
						s = s.substr(0, 3) + '.' + s.substr(3);
					}
				}
				if (plug.toUpperCase) {
					if(obj.getXType()=='swDiagTagCombo'){
						if (obj.getRawValue) {
							input.value = s.toUpperCase();
						}
						else {
							input.value = s.toLowerCase();
						}

					}
					else{
						if (obj.getRawValue) {
							obj.setRawValue(s.toUpperCase());
						}
						else {
							obj.setValue(s.toLowerCase());
						}
					}

					obj.fireEvent('changeTranslit', s.toUpperCase());
				}
				else {
					if(obj.getXType()=='swDiagTagCombo'){
						input.value = s;
					}
					else{
						if (obj.getRawValue) {
							obj.setRawValue(s);
						}
						else {
							obj.setValue(s);
						}
					}
					obj.fireEvent('changeTranslit', s);
				}
			}
		});
	}
};