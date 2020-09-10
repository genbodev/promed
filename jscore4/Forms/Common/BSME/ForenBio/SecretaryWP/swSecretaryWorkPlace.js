/* 
 * Форма АРМ Секретаря службы "Судебно-биологическое отделение с молекулярно-генетической лабораторией"
 */


Ext.define('common.BSME.ForenBio.SecretaryWP.swSecretaryWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultSecretaryWP.swDefaultSecretaryWorkPlace',
	refId: 'ForenBioSecretaryWorkPlace',
	id: 'ForenBioSecretaryWorkPlace',
	
	//Кнопки тулбара для панели просмотре заявок
	requestViewPanelButtons: [
		{
			refId: 'print_Button',
			text: 'Печать',
			xtype: 'button',
			iconCls: 'print16',
			disabled: true,
			handler: function () {
			}
		}
	],
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
				params: {JournalType: 'EvnForensicGeneticCadBlood'}
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
	requestListViewItemClick: function(rec) {
		var me = this;
		var returnResultButton = me.RequestViewPanel.down('[refId=requestIssue_Button]');
		var printButton = me.RequestViewPanel.down('[refId=print_Button]');
		var editRequestButton = me.RequestViewPanel.down('[refId=edit_request_button]');
		
		returnResultButton.setDisabled((rec.get('EvnStatus_SysNick') != 'Approved'));
		printButton.setDisabled(!rec.get('EvnForensic_id'));
		editRequestButton.setDisabled( !((!rec.get('EvnStatus_SysNick')) || (rec.get('EvnStatus_SysNick') == 'New'))  );
		me.RequestViewPanel._Evn_id = rec.get('EvnForensic_id');
	},
    initComponent: function() {
		var me = this;
		
		//Обработчик кнопки "Создать заявку"
		//Вынес в initComponent, для создания необходимой области видимости для callback'a
		this.createRequestButtonHandler = function() {
			var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт открытие формы..."}); 
			loadMask.show();
			Ext.create('common.BSME.ForenBio.SecretaryWP.tools.swEditRequestWindow',{
				callback: function() {
					loadMask.hide();
				}
			});
		};
		
		
		this.requestViewPanelButtons.push({
			refId: 'requestIssue_Button',
			text: 'Выдать на руки',
			xtype: 'button',
			iconCls: 'edit16',
			disabled: true,
			handler: function () {
				var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт открытие формы..."}); 
				loadMask.show();
				setTimeout(function() {
					Ext.create('common.BSME.tools.swRequestIssueWindow',{
					EvnForensic_id:me.RequestViewPanel._Evn_id, 
						callback: function() {
							me.loadRequestViewStore();
							loadMask.hide();
						}
					});
				},1)
			}
		},{
			text: 'Редактировать',
			refId: 'edit_request_button',
			xtype: 'button',
			iconCls: 'edit16',
			disabled: true,
			handler: function () {
				var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт открытие формы..."}); 
				loadMask.show();
				
				setTimeout(function() {
					Ext.create('common.BSME.ForenBio.SecretaryWP.tools.swCreateRequestWindow',{
						EvnForensicSub_id:me.RequestViewPanel._Evn_id,
						callback: function() {
							me.loadRequestViewStore();
							loadMask.hide();
						}
					});
				},1)
			}
		})
		
		me.callParent(arguments);
	
	}
})
		