/* 
 * АРМ заведующего отделением БСМЭ "Судебно-биологическое отделение с молекулярно-генетической лабораторией"
 */


Ext.define('common.BSME.ForenBio.DprtHeadWP.swDprtHeadWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultDprtHeadWP.swDefaultDprtHeadWorkPlace',
	refId: 'ForenBioDprtHeadWorkPlace',
	id: 'ForenBioDprtHeadWorkPlace',
	
	createRequestButtonHandler: function(){
		Ext.create('common.BSME.ForenBio.DprtHeadWP.tools.swCreateRequestWindow').show();
	},

	getEvnForensicRequestUrl:'/?c=BSME&m=getEvnForensicGeneticRequest',
	RequestTemplate: Ext.create('common.BSME.ForenBio.ux.RequestViewXTemplate'),
	//Элементы дерева журналов
	JournalTreeStoreChildren:[
		{ Journal_Text: 'Все заявки',Journal_Type: '', expanded: true, children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGenetic'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Вещественных доказательств и док-в к ним',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGeneticEvid'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Трупной крови в лаборатории',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGeneticCadBlood'},
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Биологических образцов живых лиц ',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGeneticSampleLive'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Биологических образцов живых лиц для мол/ген иссл',Journal_Type: '' , children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGeneticGenLive'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Исследований мазков и тампонов в лаборатории',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGeneticSmeSwab'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]}
	],
	
    initComponent: function() {
		var me = this;
		me.callParent(arguments);
	}
})
		