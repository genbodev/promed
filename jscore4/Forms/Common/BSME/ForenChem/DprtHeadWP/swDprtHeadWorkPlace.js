/* 
 * АРМ заведующего отделением БСМЭ "Судебно-химическое отделение"
 */


Ext.define('common.BSME.ForenChem.DprtHeadWP.swDprtHeadWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultDprtHeadWP.swDefaultDprtHeadWorkPlace',
	refId: 'ForenChemDprtHeadWorkPlace',
	id: 'ForenChemDprtHeadWorkPlace',
	
	createRequestButtonHandler: function(){
		Ext.create('common.BSME.ForenChem.DprtHeadWP.tools.swCreateRequestWindow').show();
	},
	
	JournalTreeStoreChildren: [
		{ Journal_Text: 'Все заявки',Journal_Type: '', expanded: true, children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicGenetic'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]}
//		{ Journal_Text: 'Вещественных доказательств и док-в к ним',Journal_Type: '', children: [
//			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
//				params: {JournalType: 'EvnForensicGeneticEvid'},
//				aftercallback: function(){}
//			}},
//			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
//		]},
//		{ Journal_Text: 'Трупной крови в лаборатории',Journal_Type: '', children: [
//			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
//				JournalType: ''
//			}},
//			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
//		]},
//		{ Journal_Text: 'Биологических образцов живых лиц ',Journal_Type: '', children: [
//			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
//				params: {JournalType: 'EvnForensicGeneticSampleLive'},
//				aftercallback: function(){}
//			}},
//			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
//		]},
//		{ Journal_Text: 'Биологических образцов живых лиц для мол/ген иссл',Journal_Type: '' , children: [
//			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
//				params: {JournalType: 'EvnForensicGeneticGenLive'},
//				aftercallback: function(){}
//			}},
//			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
//		]},
//		{ Journal_Text: 'Исследований мазков и тампонов в лаборатории',Journal_Type: '', children: [
//			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
//				params: {JournalType: 'EvnForensicGeneticSmeSwab'},
//				aftercallback: function(){}
//			}},
//			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
//		]}
	],
	
    initComponent: function() {
		var me = this;
		me.callParent(arguments);
	}
})
		