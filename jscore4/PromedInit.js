/**
* PromedInit - инициализация неймспейсов и прочего
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Init
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       SWAN developers
* @version      19.06.2009
*/

Ext.BLANK_IMAGE_URL = 'extjs/resources/images/default/s.gif';
Ext.ns('sw');
Ext.ns('sw.Promed');
Ext.ns('sw.Promed.Dlo');
Ext.ns('sw.Promed.Polka');
Ext.ns('sw.Promed.Admin');
sw.Promed.Glossary = {};

(function(){
    var idSeed = 0;
	Ext.apply(Ext, {		 
		/**
		 * Generates unique ids. If the element already has an id, it is unchanged
		 * @param {Mixed} el (optional) The element to generate an id for
		 * @param {String} prefix (optional) Id prefix (defaults "ext-gen")
		 * @return {String} The generated Id.
		 */

		id : function(el, prefix){
			prefix = prefix || "ext-gen";
			el = Ext.getDom(el);
			if (el && el.setAttribute) {
				el.setAttribute('test_id', this.itemText||this.text);
			}
			//log(el);
			var id = prefix + (++idSeed);
			return el ? (el.id ? el.id : (el.id = id)) : id;
		}
	});
})();