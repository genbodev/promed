/* 
 * Форма АРМ Секретаря службы "Судебно-биологическое отделение с молекулярно-генетической лабораторией"
 */


Ext.define('common.BSME.ForenAreaDprt.SecretaryWP.swSecretaryWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultSecretaryWP.swDefaultSecretaryWorkPlace',
	refId: 'ForenAreaDprtSecretaryWorkPlace',
	id: 'ForenAreaDprtSecretaryWorkPlace',
	//Обработчик кнопки "Создать заявку"
	createRequestButtonHandler: function() {
		Ext.create('common.BSME.ForenAreaDprt.SecretaryWP.tools.swCreateRequestWindow').show();
	},
	//Элементы дерева журналов
	JournalTreeStoreChildren:[
		
	],
    initComponent: function() {
		var me = this;
		
		me.callParent(arguments);
		
		
		
	}
})
		