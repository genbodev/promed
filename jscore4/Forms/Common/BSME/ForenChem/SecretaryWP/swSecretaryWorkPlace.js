/* 
 * Форма АРМ Секретаря службы "Судебно-химическое отделение"
 */


Ext.define('common.BSME.ForenChem.SecretaryWP.swSecretaryWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultSecretaryWP.swDefaultSecretaryWorkPlace',
	refId: 'ForenChemSecretaryWorkPlace',
	id: 'ForenChemSecretaryWorkPlace',
	additionalRequestListDataviewStoreFields: [
		{name: 'EvnForensicChemBiomat_id', type: 'int'},
		{name: 'EvnForensicChemDirection_id', type: 'int'},
		{name: 'EvnForensicChemKidneyDestruct_id', type: 'int'}
	],
	//Обработчик кнопки "Создать заявку"
	createRequestButtonHandler: function() {
		var createRequestWnd = Ext.create('common.BSME.ForenChem.SecretaryWP.tools.swCreateRequestWindow').show();
	},
	//Элементы дерева журналов
	JournalTreeStoreChildren:[
		{ Journal_Text: 'Все заявки',Journal_Type: '', expanded: true, children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicChem'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Вещественных доказательств и док-в к ним',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: ''},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
	],
    initComponent: function() {
		var me = this;
		
		me.callParent(arguments);
		
		
	}
})
		