/* 
 * Классы текстовых полей
 */

// Поле ввода СНИЛС
// @todo Для двойки этот компонент использует hidden поле, уточнить необходимо ли повторить это здесь.
Ext.define('sw.SnilsField',{
	extend: 'Ext.form.TextField',
	alias: 'widget.swSnilsField',
	fieldLabel: 'СНИЛС',
	name: 'Person_Snils',
	requires: ['Ux.InputTextMask'],
	plugins: [new Ux.InputTextMask('999-999-999-99')],	
});
