/* 
 * АРМ заведующего отделением БСМЭ "Медико-криминалистическое отделение"
 */


Ext.define('common.BSME.ForenCrim.DprtHeadWP.swDprtHeadWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultDprtHeadWP.swDefaultDprtHeadWorkPlace',
	refId: 'ForenCrimDprtHeadWorkPlace',
	id: 'ForenCrimDprtHeadWorkPlace',

	createRequestButtonHandler: function(){
		Ext.create('common.BSME.ForenCrim.DprtHeadWP.tools.swCreateRequestWindow').show();
	},
	getEvnForensicRequestUrl:'/?c=BSME&m=getEvnForenCrimeRequest',
	RequestTemplate: Ext.create('common.BSME.ForenBio.ux.RequestViewXTemplate'),
	//Элементы дерева журналов
	JournalTreeStoreChildren:[
		{ Journal_Text: 'Все заявки',Journal_Type: '', expanded: true, children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicCrime'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Вещественных доказательств и док-в к ним',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicCrimeEvid'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Регистрации фоторабот',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicCrimePhot'},
			}},
			{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'},
		]},
		{ Journal_Text: 'Разрушения почки на планктон',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicCrimeDesPlan'},
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
		