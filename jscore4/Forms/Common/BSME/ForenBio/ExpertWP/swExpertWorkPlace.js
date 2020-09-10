/**
 * Форма АРМ Эксперта службы "Судебно-биологическое отделение с молекулярно-генетической лабораторией"
 */


Ext.define('common.BSME.ForenBio.ExpertWP.swExpertWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultExpertWP.swDefaultExpertWorkPlace',
	refId: 'ForenBioExpertWorkPlace',
	id: 'ForenBioExpertWorkPlace',

	RequestTemplate: Ext.create('common.BSME.ForenBio.ux.RequestViewXTemplate'),
	//Элементы дерева журналов
	JournalTreeStoreChildren:[
		{ Journal_Text: 'Все заявки',Journal_Type: '', expanded: true, children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGenetic', ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Вещественных доказательств и док-в к ним',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGeneticEvid', ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Трупной крови в лаборатории',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGeneticCadBlood', ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Биологических образцов живых лиц ',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGeneticSampleLive', ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Биологических образцов живых лиц для мол/ген иссл',Journal_Type: '' , children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGeneticGenLive', ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Исследований мазков и тампонов в лаборатории',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGeneticSmeSwab', ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]}
	],

	getEvnForensicRequestUrl: '/?c=BSME&m=getEvnForensicGeneticRequest',
	
	editExpertiseProtocolHandler: function(params) {
		var me = this;
		Ext.create('common.BSME.ForenBio.ExpertWP.tools.swEditExpertiseProtocolWindow').show(params);
	},

	initComponent: function() {
		var me = this;

		me.callParent(arguments);

	}
});