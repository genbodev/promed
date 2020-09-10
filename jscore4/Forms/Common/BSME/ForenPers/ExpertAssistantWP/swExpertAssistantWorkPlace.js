/* 
 * Форма АРМ лаборанта (аналог АРМ эксперта) службы "Cудебно-медицинской экспертизы потерпевших, обвиняемых и других лиц"
 */

Ext.define('common.BSME.ForenPers.ExpertAssistantWP.swExpertAssistantWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultExpertWP.swDefaultExpertWorkPlace',
	refId: 'forenmedexppersdprtbsmeexpertassistant',
	id: 'ForenPersExpertAssistantWorkPlace',
	updateSearchFormMedPersonalEid: Ext.emptyFn,
	additionalRequestListDataviewStoreFields: [
		{name: 'XmlType_id', type: 'int'},
		{name: 'EvnXml_id', type: 'int'}
	],
	XmlTypeFilterValues: [
		13, //Заключение эксперта по уголовным делам
		14, //Заключение эксперта по административным делам
		15, //Акт медицинского исследования
		16  //Акт медицинского освидетельствования
	],
	//Получаение шаблона документа документа по умолчанию
	_getDefaultXmlTemplateId: function(params,callback) {
		if (!Ext.isObject(params) || !Ext.isFunction(callback)) {
			return false;
		}
		Ext.Ajax.request({
			url: '?c=XmlTemplateDefault&m=getXmlTemplateId',
			params: {
				XmlType_id : params.XmlType_id || null,
				EvnClass_id : params.EvnClass_id || null,
				MedStaffFact_id : params.MedStaffFact_id || null,
				MedService_id : params.MedService_id || null,
				MedPersonal_id : params.MedPersonal_id || null
			},
			callback: function(params,success,result) {
				if (result.status !== 200) {
					Ext.Msg.alert('Ошибка', 'При запросе возникла ошибка');
					return false;
				} 

				var resp = Ext.JSON.decode(result.responseText, true);
				if (resp === null || !resp[0] || !resp[0]['XmlTemplate_id']) {
					return false;
				}

				callback(resp[0]['XmlTemplate_id']);

			}
		});
	},
	//Обработчик клика по элементу списка заявок
	getRequestListDataviewItemClick: function(self, rec, html_element, node_index, event){
		var me = this;
		//Создадим документ по дефолтному шаблону, если документ ещё не создан
		if (!rec.get('EvnXml_id')) {
			if (!rec.get('XmlType_id') || !rec.get('EvnForensic_id')) {
				return false;
			}

			var EvnClass_id = 120,
				MedStaffFact_id = getGlobalOptions().CurMedStaffFact_id,
				XmlType_id = rec.get('XmlType_id');

			this._getDefaultXmlTemplateId({
				XmlType_id : XmlType_id,
				EvnClass_id : EvnClass_id,
				MedStaffFact_id : MedStaffFact_id,
				MedService_id : (getGlobalOptions().CurMedService_id) || null,
				MedPersonal_id : (getGlobalOptions().CurMedPersonal_id) || null
			}, function(XmlTemplate_id) {
				me.createEmptyDocument({
					params : {
						EvnForensic_id:rec.get('EvnForensic_id'),
						EvnXml_id: null,
						XmlTemplate_id: XmlTemplate_id,
						XmlType_id: XmlType_id,
						EvnClass_id: EvnClass_id, //120
						MedStaffFact_id: MedStaffFact_id
					},
					callback: function(data){
						me.loadRequestViewStore()
						me.loadExpertisePanel(data['EvnXml_id']||null);
					}
				});
			})
		}
	},
	
	//Элементы дерева журналов
	JournalTreeStoreChildren: [
		{ Journal_Text: 'Журнал экспертиз',Journal_Type: '', expanded: true, type: 'current', loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '1', ARMType: 'expert'},
			}, children: [
			{Journal_Text: 'Заключение по уголовным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '1', XmlType_id: '13',ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Заключение по административным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '1', XmlType_id: '14',ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Акт медицинского исследования',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '1', XmlType_id: '15',ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Акт медицинского освидетельствования',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '1', XmlType_id: '16',ARMType: 'expert'},
				aftercallback: function(){}
			}},
		]},
		{ Journal_Text: 'Журнал мед. освидетельствований',Journal_Type: '', type: 'current', loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '3', ARMType: 'expert'},
				aftercallback: function(){}
			}, children: [
			{Journal_Text: 'Акт медицинского освидетельствования',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '3', XmlType_id: '16',ARMType: 'expert'},
				aftercallback: function(){}
			}},
		]},
		{ Journal_Text: 'Журнал дополнительных экспертиз',Journal_Type: '', type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '4', ARMType: 'expert'},
				aftercallback: function(){}
			}, children: [
			{Journal_Text: 'Заключение по уголовным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '4', XmlType_id: '13',ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Заключение по административным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '4', XmlType_id: '14',ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Акт медицинского исследования',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '4', XmlType_id: '15',ARMType: 'expert'},
				aftercallback: function(){}
			}},
		]},
		{ Journal_Text: 'Журнал экспертиз по материалам дела',Journal_Type: '', type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '2', ARMType: 'expert'},
				aftercallback: function(){}
			}, children: [
			{Journal_Text: 'Заключение по уголовным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '2', XmlType_id: '13',ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Заключение по административным делам',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '2', XmlType_id: '14',ARMType: 'expert'},
				aftercallback: function(){}
			}},
			{Journal_Text: 'Акт медицинского исследования',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '2', XmlType_id: '15',ARMType: 'expert'},
				aftercallback: function(){}
			}},
		]},
		{Journal_Text: 'Все заявки',Journal_Type: '',leaf: true, type: 'current',loadStoreParams:{
			params: {JournalType: 'EvnForensicSub', ForensicSubType_id: 0, XmlType_id: 0, ARMType: 'expert'},
			aftercallback: function(){}
		}},
	],
	
	getEvnForensicRequestUrl:'/?c=BSME&m=getForenPersRequest',
	RequestTemplate: Ext.create('common.BSME.ForenPers.ux.RequestViewXTemplate'),
	
	// Вынесено для переиспользования в функционале заведующего отделением
	changeDirectionMenuOnItemClick: function(rec){
		var directionMenu = this.RequestViewPanel.down('[refId=ExpertDirectionMenu]');
		directionMenu.setDisabled(rec.get('EvnStatus_SysNick') == 'Check');
	},

	requestListViewItemClick: function(rec){
		this.changeDirectionMenuOnItemClick( rec );
		this.callParent(arguments);
	},
	
	// Вынесено для переиспользования в функционале заведующего отделением
	getExpertDirectionMenu: function(){
		var me = this;
		return {
			id: me.id+'_DirectionMenu',
			refId: 'ExpertDirectionMenu',
			xtype: 'splitbutton',
			iconCls: 'add16',
			text: 'Создать',
			disabled: true,
			menu: {
				xtype: 'menu',
				items: [{
	//					xtype: 'menuitem',
	//					text: 'Запрос дополнительных материалов',
	//					handler: function () {
	//						var EvnForensic_id = me.RequestViewPanel._Evn_id;
	//						if ( !EvnForensic_id ) {
	//							Ext.Msg.alert('Ошибка', 'Не выбрана заявка.');
	//							return false;
	//						}
	//
	//						Ext.create('common.BSME.ForenPers.ExpertWP.tools.swCreateDopMatQueryWindow').show({
	//							action: 'add',
	//							formParams: {EvnForensicSub_id: EvnForensic_id},
	//							callback: function(){
	//								me.updateCurrentRequest()
	//							}
	//						});
	//						return true;
	//					}
	//				},{
					xtype: 'menuitem',
					text: 'Запрос дополнительных документов',
					handler: function () {
						var EvnForensic_id = me.RequestViewPanel._Evn_id;
						if ( !EvnForensic_id ) {
							Ext.Msg.alert('Ошибка', 'Не выбрана заявка.');
							return false;
						}

						Ext.create('common.BSME.ForenPers.ExpertWP.tools.swCreateDopDocQueryWindow').show({
							action: 'add',
							formParams: {EvnForensicSub_id: EvnForensic_id},
							callback: function(){
								me.updateCurrentRequest()
							}
						});
						return true;
					}
				},{
					xtype: 'menuitem',
					text: 'Запрос на участие',
					handler: function () {
						var EvnForensic_id = me.RequestViewPanel._Evn_id;
						if ( !EvnForensic_id ) {
							Ext.Msg.alert('Ошибка', 'Не выбрана заявка.');
							return false;
						}

						Ext.create('common.BSME.ForenPers.ExpertWP.tools.swCreateDopPersQueryWindow').show({
							action: 'add',
							formParams: {EvnForensicSub_id: EvnForensic_id},
							callback: function(){
								me.updateCurrentRequest()
							}
						});
						return true;
					}
				},{
					xtype: 'menuitem',
					text: 'Сопроводительное письмо',
					handler: function () {
						var EvnForensic_id = me.RequestViewPanel._Evn_id;
						if ( !EvnForensic_id ) {
							Ext.Msg.alert('Ошибка', 'Не выбрана заявка.');
							return false;
						}

						Ext.create('common.BSME.ForenPers.ExpertWP.tools.swCreateCoverLetterWindow').show({
							action: 'add',
							formParams: {EvnForensicSub_id: EvnForensic_id},
							callback: function(){
								me.updateCurrentRequest()
							}
						});
						return true;
					}
				}]
			},
			listeners: {
				click: function(){
					this.showMenu();
				}
			}
		};
	},
	
	// Вынес для наследования в АРМ лаборанта
	JournalTreePanelOnBeforeselect: function( tree, record, index, eOpts ){
		if ( !record.data ) {
			return;
		}

		if ( record.data.loadStoreParams.params ) {
			this.updateRequestCountInTabsParams = record.data.loadStoreParams.params;
			this.XmlType_id = record.data.loadStoreParams.params.XmlType_id;
		}

		var ForensicSubType_id = (!record.data
				|| !record.data.loadStoreParams
				|| !record.data.loadStoreParams.params
				|| !record.data.loadStoreParams.params.ForensicSubType_id
				|| !record.data.loadStoreParams.params.XmlType_id
			)?0:record.data.loadStoreParams.params.ForensicSubType_id;
		this.createRequestButton.setDisabled( !ForensicSubType_id );
	},
	
	getJournalTreePanelSelectionModelParams: function(){
		var selection = this.JournalTreePanel.getSelectionModel().getSelection()[0];
		if (!selection || !selection.data || !selection.data.loadStoreParams || !selection.data.loadStoreParams.params){
			return false;
		} else {
			return selection.data.loadStoreParams.params;
		}
	},

	createRequestButtonHandler: function(){
		var me = this,
			loadMask = new Ext.LoadMask(this, {msg: "Пожалуйста, подождите, идёт открытие формы..."});

		loadMask.show();

		var JTSMParams = this.getJournalTreePanelSelectionModelParams();
		if ( JTSMParams === false ) {
			loadMask.hide();
			return false;
		}

		Ext.create('common.BSME.ForenPers.SecretaryWP.tools.swCreateRequestWindow', {
			XmlType_id: JTSMParams.XmlType_id,
			ForensicSubType_id: JTSMParams.ForensicSubType_id,
			MedPersonal_eid: null,
			callback: function(){
				me.loadRequestViewStore();
				loadMask.hide();
			}
		});
	},
	
	// Вынесено для переиспользования заведующим отделения
	initPrintRequestButton: function(){
		var me = this;
		this.printRequestButton = Ext.create('Ext.Button',{
			text: 'Печать заключения',
			cls: 'createButton', 
			disabled: true,
			handler: function(){
				if (!me.RequestViewPanel._Evn_id) {
					return false;
				}
				
				// Проверка заполненности отчета перед отправкой
				if ( me.ReportPanel ) {
					var rp_form = me.ReportPanel.getForm(),
						rp_params = rp_form.getValues();
					if ( !rp_params.ForensicSubReportWorking_id ) {
						Ext.Msg.alert('Ошибка','Перед закрытием заявки необходимо заполнить отчет «Деятельности бюро».');
						return false;
					}
				}
				
				Ext.Ajax.request({
					url: '/?c=BSME&m=printEvnXml',
					params: {
						EvnXml_id: me.ExpertisePanel._EvnXml_id,
						EvnForensic_id: me.RequestViewPanel._Evn_id
					},
					callback: function(opt, success, response){
						me.loadRequestViewStore();
						if ( !success ) {
							Ext.Msg.alert('Ошибка','Во время загрузки печатной формы произошла ошибка.');
							return;
						}
						var win = window.open();
						win.document.write(response.responseText);
						
						me.clearRequestView();
					}
				});
			}
		});
	},
		
	initComponent: function() {
		var me = this;
		
		this.editExpertiseProtocolHandler = function(params) {
			Ext.create('common.BSME.ForenPers.ExpertWP.tools.swEditExpertiseProtocolWindow').show(params);
		};

		//me.additionalRequestViewPanelButtons.push( this.getExpertDirectionMenu() );		
		me.additionalRequestViewPanelButtons = [this.getExpertDirectionMenu()];
		
		
		var EvnForensicSubGrid = Ext.create('common.BSME.ForenPers.ux.ArchiveEvnSubGrid',{
			extraParams: {
				JournalType:'EvnForensicSub'
			}
		});
		
		//EvnForensicSubGrid.MedPersonalSearchCombo.setValue(parseInt(getGlobalOptions().CurMedPersonal_id)).setDisabled(true);
		
		me.archivePanelItems = [
			EvnForensicSubGrid,
		];
		me.archiveGrids = {
			'EvnForensicSub':EvnForensicSubGrid
		};
		
		this.createRequestButton = Ext.create('Ext.Button',{
			text: 'Создать заявку',
			cls: 'createButton', 
			handler: this.createRequestButtonHandler,
			scope: this
		});
		
		this.initPrintRequestButton();
		
		me.callParent(arguments);
		
		this.JournalTreePanel.on('beforeselect', this.JournalTreePanelOnBeforeselect, this);
		this.RequestListDataview.on('itemclick', this.getRequestListDataviewItemClick, this);
	}
})

/* 
 * Форма АРМ лаборанта (аналог АРМ эксперта) службы "Cудебно-медицинской экспертизы потерпевших, обвиняемых и других лиц"
 */
/*
Ext.define('common.BSME.ForenPers.ExpertAssistantWP.swExpertAssistantWorkPlace', {
	extend: 'common.BSME.ForenPers.ExpertWP.swExpertWorkPlace',
	refId: 'ForenPersExpertAssistantWorkPlace',
	id: 'ForenPersExpertAssistantWorkPlace',
	
	// Лаборант не может быть выбран в качестве эксперта,
	// поэтому не вызываем установку значения в списке формы поиска
	updateSearchFormMedPersonalEid: Ext.emptyFn,
	
	createRequestButtonHandler: function(){
		var me = this,
			loadMask = new Ext.LoadMask(this, {msg: "Пожалуйста, подождите, идёт открытие формы..."});

		loadMask.show();

		var JTSMParams = this.getJournalTreePanelSelectionModelParams();
		if ( JTSMParams === false ) {
			loadMask.hide();
			return false;
		}

		Ext.create('common.BSME.ForenPers.SecretaryWP.tools.swCreateRequestWindow', {
			XmlType_id: JTSMParams.XmlType_id,
			ForensicSubType_id: JTSMParams.ForensicSubType_id,			
			// Передаем пустое значение, т.к. в списке экспертов данного сотрудника быть не должно
			MedPersonal_eid: null,			
			callback: function(){
				me.loadRequestViewStore();
				loadMask.hide();
			}
		});
	},
	
});

*/