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

Ext.define('Ux.Translit', {
	pluginId: 'translit',
	constructor: function(toRus, toUpperCase) {
		this.toRus = toRus;
		if (toUpperCase==true)
			this.toUpperCase = toUpperCase;
		return this;
   },

   init: function(field) {
		
		this.field = field;

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
		


		field.enableKeyEvents = true;
		if (field.rendered){
			this.assignEl(field);
		} else {
			field.on('render', this.assignEl, field);
		}
	},
	assignEl: function(f) {
		var obj = f;
		var plug = f.getPlugin('translit')
		/*var charCode = 0;
			
		оставлю до лучших времен
		obj.getEl().on('keypress', function(e) {
			var text = e.type +
			' keyCode=' + e.keyCode +
			' which=' + e.which +
			' charCode=' + e.charCode +
			' char=' + String.fromCharCode(e.keyCode || e.charCode) +
			(e.shiftKey ? ' +shift' : '') +
			(e.ctrlKey ? ' +ctrl' : '') +
			(e.altKey ? ' +alt' : '') +
			(e.metaKey ? ' +meta' : '') + "\n";	
			charCode = e.charCode;
		})
		*/
		if(!obj.getEl())return;
		obj.getEl().on('keyup', function(e) {
			if ( obj.disableTransPlug === true ) return;
			
			//console.log('charCode', charCode)
			
			if ( e.getCharCode() == 8 ||
				(e.getCharCode() > 36 && e.getCharCode() < 41) ||
				e.getCharCode() == e.DELETE ||
				e.getCharCode() == e.HOME ||
				e.getCharCode() == e.END ||
				e.getCharCode() == e.CTRL ||
				e.getCharCode() == e.ALT ||
				e.getCharCode() == e.SHIFT ||
				e.getCharCode() == e.TAB ||
				e.getCharCode() == 13
				)
				return;
			if (!obj){
				return
			}
			
			var s = "";
			var inputText = null;
			if (typeof obj.getRawValue == 'function') {inputText = obj.getRawValue();}
			else if (typeof obj.getValue == 'function') {inputText = obj.getValue();}
			else if (obj.value) {inputText = obj.value; }
			if(inputText)
			{
				for (var i = 0; i <= (inputText.length); i++)
				{
					if ( typeof(plug.tArr[inputText.substr(i,1)]) != 'undefined' )
						s = s.concat(plug.tArr[inputText.substr(i,1)]);
					else
						s = s.concat(inputText.substr(i,1));
				}
				if (plug.toUpperCase) {
					if ( obj.getRawValue )
					{obj.setRawValue(s.toUpperCase());}
					else
					{obj.setValue(s.toLowerCase());}
					obj.fireEvent('changeTranslit', s.toUpperCase());
				}
				else
				{
					if ( obj.getRawValue )
						{obj.setRawValue(s);}
					else
						{obj.setValue(s);}
					obj.fireEvent('changeTranslit', s);
				}				
			}
		});
	}
});

//Ext.applyIf(RegExp, {
//   escape: function(str) {
//      return new String(str).replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
//   }
//});