/**
 * Форма АРМ Секретаря службы "Отдел комиссионных и комплексных экспертиз"
 */


Ext.define('common.BSME.ForenComplex.SecretaryWP.swSecretaryWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultSecretaryWP.swDefaultSecretaryWorkPlace',
	refId: 'ForenComplexSecretaryWorkPlace',
	id: 'ForenComplexSecretaryWorkPlace',

	//Кнопки тулбара для панели просмотре заявок
	requestViewPanelButtons: [
//		{
//		text: 'Редактировать',
//			refId: 'edit_Button',
//			iconCls: 'edit16',
//			xtype: 'button',
//			handler: function() {
//			}
//
//		},
		{
			refId: 'print_Button',
			text: 'Печать',
			xtype: 'button',
			iconCls: 'print16',
			disabled: true,
			handler: function () {
			}
		},
		{
			refId: 'returnResult_Button',
			text: 'Выдать на руки',
			xtype: 'button',
			iconCls: 'edit16',
			disabled: true,
			handler: function () {
			}
		}

	],
	getEvnForensicRequestUrl:'/?c=BSME&m=getForenComplexRequest',
	RequestTemplate: Ext.create('common.BSME.ForenComplex.ux.RequestViewXTemplate'),
	//Элементы дерева журналов
	JournalTreeStoreChildren:[
		{ Journal_Text: 'Все заявки',Journal_Type: '', expanded: true, children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicComplex'},
				aftercallback: function(){}
			}}
			//,{Journal_Text: 'Архив',Journal_Type: '',leaf: true, type: 'archive'}
		]},
		{ Journal_Text: 'Судебно-медицинских иссл-й и медицинских судебных экспертиз',Journal_Type: '', children: [
			{Journal_Text: 'В работе',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicComplexResearch'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Архив',Journal_Type: 'EvnForensicComplexResearch',leaf: true, type: 'archive'}
		]}
	],
	requestListViewItemClick: function(rec) {
		var me = this;
		var returnResultButton = me.RequestViewPanel.down('[refId=returnResult_Button]');
		var printButton = me.RequestViewPanel.down('[refId=print_Button]');
		returnResultButton.setDisabled((rec.get('EvnStatus_SysNick') != 'Approved'));
		printButton.setDisabled(!rec.get('EvnForensic_id'));
		me.RequestViewPanel._Evn_id = rec.get('EvnForensic_id');
	},
	initComponent: function() {
		var me = this;

		//Обработчик кнопки "Создать заявку"
		//Вынес в initComponent, для создания необходимой области видимости для callback'a
		this.createRequestButtonHandler = function() {
			var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт открытие формы..."});
			loadMask.show();
			Ext.create('common.BSME.ForenComplex.SecretaryWP.tools.swCreateRequestWindow').show({
				action: 'add',
				callback: function() {
					loadMask.hide();
				}
			});
		};


		var EvnForensicComplexResearchGrid = Ext.create('common.BSME.ForenComplex.ux.ArchiveEvnComplexView',{
			extraParams: {
				JournalType:'EvnForensicComplexResearch'
			}
		});

		me.archivePanelItems = [
			EvnForensicComplexResearchGrid
		];
		me.archiveGrids = {
			'EvnForensicComplexResearch':EvnForensicComplexResearchGrid
		};

		me.callParent(arguments);

	}
})
		