/* 
 * АРМ заведующего отделением БСМЭ "Судебно-гистологическое отделение"
 */


Ext.define('common.BSME.ForenHist.DprtHeadWP.swDprtHeadWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultDprtHeadWP.swDefaultDprtHeadWorkPlace',
	refId: 'ForenHistDprtHeadWorkPlace',
	id: 'ForenHistDprtHeadWorkPlace',
	
	createRequestButtonHandler: function(){
		Ext.create('common.BSME.ForenHist.DprtHeadWP.tools.swCreateRequestWindow').show();
	},
	
	JournalTreeStoreChildren: [
		{ Journal_Text: 'Все заявки',Journal_Type: '', expanded: true, children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicHist'},
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
		