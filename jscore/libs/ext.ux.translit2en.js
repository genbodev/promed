/**
* ext.ux.translit2en - плагин для автоматического транслита в полях ввода. реализован по примеру ext.ux.translit 
* (@author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru))
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
 * @param {Boolean} toEn Перевести в английскую раскладку
 * @param {Boolean} toUpperCase Привести строку к верхнему регистру
 * @param {Object} replaceSymbols Возжможные заменяемые параметры букв вставка в tArr
 */
Ext.ux.translit2en = function(toEn, toUpperCase, replaceSymbols) {
	this.toEn = toEn;
	if (toUpperCase!=undefined)
		this.toUpperCase = toUpperCase;
	if(replaceSymbols != undefined) {
		this.replaceSymbols = replaceSymbols;
	}
};

Ext.ux.translit2en.prototype = {
	init : function(field) {

		this.field = field;

		this.tArr = [];
		
		this.tArr['Ф'] = 'a';
		this.tArr['Ф'] = 'A';
		this.tArr['и'] = 'b';
		this.tArr['И'] = 'B';
		this.tArr['с'] = 'c';
		this.tArr['С'] = 'C';
		this.tArr['в'] = 'd';
		this.tArr['В'] = 'D';
		

		this.tArr['у'] = 'e';
		this.tArr['У'] = 'E';
		this.tArr['а'] = 'f';
		this.tArr['А'] = 'F';
		this.tArr['п'] = 'g';
		this.tArr['П'] = 'G';
		this.tArr['р'] = 'h';
		this.tArr['Р'] = 'H';
		this.tArr['ш'] = 'i';
		this.tArr['Ш'] = 'I';
		this.tArr['о'] = 'j';
		this.tArr['О'] = 'J';
		this.tArr['л'] = 'k';
		this.tArr['Л'] = 'K';
		this.tArr['д'] = 'l';
		this.tArr['Д'] = 'L';
		this.tArr['ь'] = 'm';
		this.tArr['Ь'] = 'M';
		this.tArr['т'] = 'n';
		this.tArr['Т'] = 'N';
		this.tArr['щ'] = 'o';
		this.tArr['Щ'] = 'O';
		this.tArr['з'] = 'p';
		this.tArr['З'] = 'P';
		this.tArr['й'] = 'q';
		this.tArr['Й'] = 'Q';
		this.tArr['к'] = 'r';
		this.tArr['К'] = 'R';
		this.tArr['ы'] = 's';
		this.tArr['Ы'] = 'S';
		this.tArr['е'] = 't';
		this.tArr['Е'] = 'T';
		this.tArr['г'] = 'u';
		this.tArr['Г'] = 'U';
		this.tArr['м'] = 'v';
		this.tArr['М'] = 'V';
		this.tArr['ц'] = 'w';
		this.tArr['Ц'] = 'W';
		this.tArr['ч'] = 'x';
		this.tArr['Ч'] = 'X';
		this.tArr['н'] = 'y';
		this.tArr['Н'] = 'Y';
		this.tArr['я'] = 'z';
		this.tArr['Я'] = 'Z';
		

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