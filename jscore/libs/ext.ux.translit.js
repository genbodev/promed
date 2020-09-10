/**
* ext.ux.translit - плагин для автоматического транслита в полях ввода.
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
 * @param {Object} replaceSymbols Возжможные заменяемые параметры букв вставка в tArr
 */
Ext.ux.translit = function(toRus, toUpperCase, replaceSymbols) {
	this.toRus = toRus;
	if (toUpperCase!=undefined)
		this.toUpperCase = toUpperCase;
	if(replaceSymbols != undefined) {
		this.replaceSymbols = replaceSymbols;
	}
};

Ext.ux.translit.prototype = {
	init : function(field) {

		this.field = field;

		this.tArr = [];

		this.tArr['f'] = 'А';
		this.tArr['F'] = 'А';
		this.tArr[','] = 'Б';
		this.tArr['<'] = 'Б';
		this.tArr['d'] = 'В';
		this.tArr['D'] = 'В';
		this.tArr['u'] = 'Г';
		this.tArr['U'] = 'Г';
		this.tArr['l'] = 'Д';
		this.tArr['L'] = 'Д';
		this.tArr['t'] = 'Е';
		this.tArr['T'] = 'Е';
		this.tArr['`'] = 'Ё';
		this.tArr['~'] = 'Ё';
		this.tArr[';'] = 'Ж';
		this.tArr[':'] = 'Ж';
		this.tArr['p'] = 'З';
		this.tArr['P'] = 'З';
		this.tArr['b'] = 'И';
		this.tArr['B'] = 'И';
		this.tArr['q'] = 'Й';
		this.tArr['Q'] = 'Й';
		this.tArr['r'] = 'К';
		this.tArr['R'] = 'К';
		this.tArr['k'] = 'Л';
		this.tArr['K'] = 'Л';
		this.tArr['v'] = 'М';
		this.tArr['V'] = 'М';
		this.tArr['y'] = 'Н';
		this.tArr['Y'] = 'Н';
		this.tArr['j'] = 'О';
		this.tArr['J'] = 'О';
		this.tArr['g'] = 'П';
		this.tArr['G'] = 'П';
		this.tArr['h'] = 'Р';
		this.tArr['H'] = 'Р';
		this.tArr['c'] = 'С';
		this.tArr['C'] = 'С';
		this.tArr['n'] = 'Т';
		this.tArr['N'] = 'Т';
		this.tArr['e'] = 'У';
		this.tArr['E'] = 'У';
		this.tArr['a'] = 'Ф';
		this.tArr['A'] = 'Ф';
		this.tArr['['] = 'Х';
		this.tArr['{'] = 'Х';
		this.tArr['w'] = 'Ц';
		this.tArr['W'] = 'Ц';
		this.tArr['x'] = 'Ч';
		this.tArr['X'] = 'Ч';
		this.tArr['i'] = 'Ш';
		this.tArr['I'] = 'Ш';
		this.tArr['o'] = 'Щ';
		this.tArr['O'] = 'Щ';
		this.tArr['m'] = 'Ь';
		this.tArr['M'] = 'Ь';
		this.tArr['s'] = 'Ы';
		this.tArr['S'] = 'Ы';
		this.tArr[']'] = 'Ъ';
		this.tArr['}'] = 'Ъ';
		this.tArr['\''] = 'Э';
		this.tArr['\"'] = 'Э';
		this.tArr['.'] = 'Ю';
		this.tArr['>'] = 'Ю';
		this.tArr['z'] = 'Я';
		this.tArr['Z'] = 'Я';

/*        if (field.rendered){
            this.assignEl();
        } else {
            field.on('render', this.assignEl, this);
        }
*/

//        field.on('blur',this.removeValueWhenInvalid, this);
		field.enableKeyEvents = true;
		if (field.rendered){
			this.assignEl();
		} else {
			field.on('render', this.assignEl, this);
		}
	},
	assignEl: function() {
		var obj = this.field;
		var plug = this;
		this.field.getEl().on('keyup', function(e) {


			// добавление - изменение символов
			if(plug.replaceSymbols) {

				for(key in plug.replaceSymbols) {
					plug.tArr[key] = plug.replaceSymbols[key];
				}
			}

			if ( obj.disableTransPlug === true )
				return;
			if ( e.getCharCode() == 8 ||
				(e.getCharCode() > 36 && e.getCharCode() < 41) ||
				e.getCharCode() == e.DELETE ||
				e.getCharCode() == e.HOME ||
				e.getCharCode() == e.END ||
				e.getCharCode() == e.CTRL ||
				e.getCharCode() == e.ALT ||
				e.getCharCode() == e.SHIFT ||
				e.getCharCode() == e.TAB
				)
				return;
			var s = "";
			if ( obj.getRawValue )
				var inputText = obj.getRawValue();
			else
				var inputText = obj.getValue();
			for (var i = 0; i <= (inputText.length - 1); i++)
			{
				if ( typeof(plug.tArr[inputText.substr(i,1)]) != 'undefined' )
					s = s.concat(plug.tArr[inputText.substr(i,1)]);
				else
					s = s.concat(inputText.substr(i,1));
			}
			if (plug.toUpperCase) {
				if ( obj.getRawValue )
					obj.setRawValue(s.toUpperCase());
				else
					obj.setValue(s.toUpperCase());
			}
			else
			{
				if ( obj.getRawValue )
					obj.setRawValue(s);
				else
					obj.setValue(s);
			}


		});
	}
}