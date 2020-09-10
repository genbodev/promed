/* 
 * Форма АРМ Секретаря службы "Судебно-биологическое отделение с молекулярно-генетической лабораторией"
 */


Ext.define('common.BSME.ForenPers.SecretaryWP.swSecretaryWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultSecretaryWP.swDefaultSecretaryWorkPlace',
	refId: 'forenmedexppersdprtbsmesecretary',
	id: 'ForenPersSecretaryWorkPlace',

	//Кнопки тулбара для панели просмотре заявок
	requestViewPanelButtons: [
//		{
//			xtype: 'splitbutton',
//			refId: 'split_print_button',
//			iconCls: 'print16',
//			disabled: true,
//			text: 'Печать',
//			menu: {
//				xtype: 'menu',
//				items: [
//					{
//						xtype: 'menuitem',
//						itemId: 'print_request',
//						text: 'Печать заявления',
//						handler: function () {
//							var pattern = 'CME_Statement.rptdesign';
//							printBirt({
//								'Report_FileName': pattern,
//								'Report_Params': '&paramEvnForensicSub='+this.ownerCt.up('panel')._Evn_id,
//								'Report_Format': 'pdf'
//							});
//						}
//					},
//					{
//						xtype: 'menuitem',
//						itemId: 'print_dog',
//						text: 'Печать договора',
//						handler: function () {
//							var pattern = 'CME_Contract.rptdesign';
//							printBirt({
//								'Report_FileName': pattern,
//								'Report_Params': '&paramEvnForensicSubOwn='+this.ownerCt.up('panel')._Evn_id,
//								'Report_Format': 'pdf'
//							});
//						}
//					},
//					{
//						xtype: 'menuitem',
//						itemId: 'print_act',
//						text: 'Печать заключения',
//						handler: function () {
//							var pattern = 'CME_EvnForensicSub_List.rptdesign';
//							//console.log(this.ownerCt.up('panel'));
//							printBirt({
//								'Report_FileName': pattern,
//								'Report_Params': '&paramEvnForensicSub='+this.ownerCt.up('panel')._Evn_id,
//								'Report_Format': 'pdf'
//							});
//						}
//					},
//				]
//			},
//			listeners: {
//				click: function(){
//					this.showMenu();
//				}
//			}
//			
//		}
	],
	additionalRequestListDataviewStoreFields: [
		{name: 'EvnClass_id', type: 'int'}
	],
	requestListViewItemClick: function(rec) {
		var me = this;
//		var returnResultButton = me.RequestViewPanel.down('[refId=requestIssue_Button]');
//		var editRequestButton = me.RequestViewPanel.down('[refId=edit_request_button]');
//		var splitPrintButton = me.RequestViewPanel.down('[refId=split_print_button]');
//		var printActButtonMenuItem = splitPrintButton.menu.queryById('print_act');
//		var printDogButtonMenuItem = splitPrintButton.menu.queryById('print_dog');
//		var printRequestButtonMenuItem = splitPrintButton.menu.queryById('print_request');
		
//		returnResultButton.setDisabled((rec.get('EvnStatus_SysNick') != 'Approved'));
//
//		printActButtonMenuItem.setDisabled((rec.get('EvnStatus_SysNick') != 'Approved'));
//		printDogButtonMenuItem.setDisabled(!( ((rec.get('EvnStatus_SysNick') == 'New') || (!rec.get('EvnStatus_SysNick'))) && (rec.get('EvnClass_id') == 125) ));
//		printRequestButtonMenuItem.setDisabled(!( ((rec.get('EvnStatus_SysNick') == 'New') || (!rec.get('EvnStatus_SysNick'))) && (rec.get('EvnClass_id') == 125) ));
//		editRequestButton.setDisabled( !((!rec.get('EvnStatus_SysNick')) || (rec.get('EvnStatus_SysNick') == 'New'))  );
		
//		splitPrintButton.setDisabled( printActButtonMenuItem.disabled && printDogButtonMenuItem.disabled && printRequestButtonMenuItem.disabled  );
		me.RequestViewPanel.down('[itemId=edit_request_button]').setDisabled(false);
		me.RequestViewPanel.down('[itemId=xml_versions_button]').setDisabled(false);
		me.RequestViewPanel._Evn_id = rec.get('EvnForensic_id');
	},
	
	//Элементы дерева журналов
	JournalTreeStoreChildren: [
		{ Journal_Text: 'Журнал экспертиз',Journal_Type: '',  expanded: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '1'}
			}, children: [
			{Journal_Text: 'Заключение по уголовным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '1', XmlType_id: '13'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Заключение по административным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '1', XmlType_id: '14'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Акт медицинского исследования',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '1', XmlType_id: '15'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Акт медицинского освидетельствования',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '1', XmlType_id: '16'},
				aftercallback: function(){}
			}},
		]},
		{ Journal_Text: 'Журнал мед. освидетельствований',Journal_Type: '', type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '3'}
			}, children: [
			{Journal_Text: 'Акт медицинского освидетельствования',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '3', XmlType_id: '16'},
				aftercallback: function(){}
			}},
		]},
		{ Journal_Text: 'Журнал дополнительных экспертиз',Journal_Type: '', type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '4'}
			}, children: [
			{Journal_Text: 'Заключение по уголовным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '4', XmlType_id: '13'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Заключение по административным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '4', XmlType_id: '14'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Акт медицинского исследования',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '4', XmlType_id: '15'},
				aftercallback: function(){}
			}},
		]},
		{ Journal_Text: 'Журнал экспертиз по материалам дела',Journal_Type: '', type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '2'}
			}, children: [
			{Journal_Text: 'Заключение по уголовным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '2', XmlType_id: '13'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Заключение по административным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '2', XmlType_id: '14'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Акт медицинского исследования',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '2', XmlType_id: '15'},
				aftercallback: function(){}
			}},
		]},
		{Journal_Text: 'Все заявки',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
			params: {JournalType: 'EvnForensicSub', ForensicSubType_id: 0, XmlType_id: 0},
			aftercallback: function(){}
		}},
	],
	getEvnForensicRequestUrl:'/?c=BSME&m=getForenPersRequest',
	RequestTemplate: Ext.create('common.BSME.ForenPers.ux.RequestViewXTemplate'),
    initComponent: function() {
		var me = this;
		
		this.requestViewPanelButtons.push(
//		{
//			refId: 'requestIssue_Button',
//			text: 'Выдать на руки',
//			xtype: 'button',
//			iconCls: 'edit16',
//			disabled: true,
//			handler: function () {
//				var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт открытие формы..."}); 
//				loadMask.show();
//				setTimeout(function() {
//					Ext.create('common.BSME.tools.swRequestIssueWindow',{
//					EvnForensic_id:me.RequestViewPanel._Evn_id, 
//						callback: function() {
//							me.loadRequestViewStore();
//							loadMask.hide();
//						}
//					});
//				},1)
//			}
//		},
		{
			text: 'Редактировать',
			itemId: 'edit_request_button',
			xtype: 'button',
			iconCls: 'edit16',
			disabled: true,
			handler: function () {
				var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт открытие формы..."}); 
				loadMask.show();
				
				setTimeout(function() {
					Ext.create('common.BSME.ForenPers.SecretaryWP.tools.swCreateRequestWindow',{
						EvnForensicSub_id:me.RequestViewPanel._Evn_id,
						callback: function() {
							me.loadRequestViewStore();
							loadMask.hide();
						}
					});
				},1)
			}
		},
		{
			text: 'Версии документа',
			itemId: 'xml_versions_button',
			xtype: 'button',
			disabled: true,
			handler: function() {
				Ext.create('common.BSME.tools.swBSMEXmlVersionListWindow',{
					EvnForensic_id: me.RequestViewPanel._Evn_id
				});
			}
		}
		)
		
		//Вынес в initComponent, для создания необходимой области видимости для callback'a
		this.createRequestButtonHandler = function() {
			var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт открытие формы..."}); 
			loadMask.show();
			var selection = me.JournalTreePanel.getSelectionModel().getSelection()[0];
			if (selection && selection.data.loadStoreParams.params) {
				var wnd = Ext.create('common.BSME.ForenPers.SecretaryWP.tools.swCreateRequestWindow',{
					XmlType_id: selection.data.loadStoreParams.params.XmlType_id,
					ForensicSubType_id: selection.data.loadStoreParams.params.ForensicSubType_id,
					callback: function() {
						me.loadRequestViewStore();
						loadMask.hide();
					}
				});
			}
			
		};
		
				
		this.createRequestButton = Ext.create('Ext.Button',{
			text: 'Создать заявку',
			cls: 'createButton', 
			handler: this.createRequestButtonHandler
		});
		
		var EvnForensicSubGrid = Ext.create('common.BSME.ForenPers.ux.ArchiveEvnSubGrid',{
			extraParams: {
				JournalType:'EvnForensicSub'
			}
		});
		
		me.archivePanelItems = [
			EvnForensicSubGrid,
		];
		me.archiveGrids = {
			'EvnForensicSub':EvnForensicSubGrid
		};
		
		EvnForensicSubGrid.returnToWorkButton.hide();
			
		me.callParent(arguments);
		
		//Передаем дополнительные параметры в метод загрузки количества заявок,
		//Проверяем доступность кнопки создания
		this.JournalTreePanel.on('beforeselect',function( tree, record, index, eOpts ){
			if ( !record.data ) {
				return;
			}

			if ( record.data.loadStoreParams.params ) {
				this.updateRequestCountInTabsParams = record.data.loadStoreParams.params;
			}

			var ForensicSubType_id = (!record.data
					|| !record.data.loadStoreParams
					|| !record.data.loadStoreParams.params
					|| !record.data.loadStoreParams.params.ForensicSubType_id
					|| !record.data.loadStoreParams.params.XmlType_id
				)?0:record.data.loadStoreParams.params.ForensicSubType_id;
			this.createRequestButton.setDisabled( !ForensicSubType_id );
		}, this);
	}
})
		