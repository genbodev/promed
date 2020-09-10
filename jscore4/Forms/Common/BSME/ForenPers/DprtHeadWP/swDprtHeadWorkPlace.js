/* 
 * АРМ заведующего отделением БСМЭ "Отдел судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц"
 */


Ext.define('common.BSME.ForenPers.DprtHeadWP.swDprtHeadWorkPlace', {
	extend: 'common.BSME.DefaultWP.DefaultDprtHeadWP.swDefaultDprtHeadWorkPlace',
	refId: 'forenmedexppersdprtbsmedprthead',
	id: 'ForenPersDprtHeadWorkPlace',

	//Элементы дерева журналов
	JournalTreeStoreChildren: [
		{ Journal_Text: 'Журнал экспертиз',Journal_Type: '', expanded: true, type: 'current',loadStoreParams:{
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
				params: {JournalType: 'EvnForensicSub', ForensicSubType_id: '3' }
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
	
	additionalRequestListDataviewStoreFields: [
		{name: 'EvnClass_id', type: 'int'},
		{name: 'XmlType_id', type: 'int'},
		{name: 'EvnXml_id', type: 'int'}
  	],
	
	mixins: {
		swExpertWorkPlace: 'common.BSME.ForenPers.ExpertWP.swExpertWorkPlace'
	},
	
	_updateExpertisePanel: function(data){
		// Функционал эксперта
		if ( this.allowExpertFunctionality ) {
			this.mixins.swExpertWorkPlace._updateExpertisePanel.call(this,data);
		} else {
			this.callParent(arguments);
		}
	},
	
	allowExpertFunctionality: false,
	
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
	
	initReportPanel: function(){
		// Инициализируем отчет деятельности бюро. С той лишь разницей, что если у
		// заведующего нет АРМа эксперта, он не сможет его редактировать
		var me = this,
			editable = this.allowExpertFunctionality;
		
		this.mixins.swExpertWorkPlace.initReportPanel.call(this,editable);
		
		if ( !this.allowExpertFunctionality ) {
			setTimeout(function(){
				var form = me.ReportPanel.getForm(),
					fields = form.getFields();
				Ext.each(fields.items, function(f){
					f.setReadOnly(true);
				});
			},200);
		}
	},
	
	initExpertFunctionality: function(){
		var group_list = getGlobalOptions().groups.split('|');
		this.allowExpertFunctionality = Ext.Array.contains(group_list,'bsmeexpert');
	},

	initComponent: function() {
		var me = this;
		
		this.initExpertFunctionality();
		
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
					},
					disbleEvnForensicSub_pid: false
				});
			}
			
		};
		me.createRequestButton = Ext.create('Ext.Button',{
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
		
		// Функционал эксперта
		if ( this.allowExpertFunctionality ) {
			this.additionalRequestViewPanelButtons.push( this.getExpertSelectTemplateBtn() );
			this.additionalRequestViewPanelButtons.push( this.getExpertDirectionMenu() );
			this.mixins.swExpertWorkPlace.initPrintRequestButton.call(this);
		} else {
			this.initPrintRequestButton();
		}
		this.initReportPanel();
		
		EvnForensicSubGrid.firstToolbar.add({
			xtype: 'button',
			text: 'Отчет 3100',
			handler: function() {
				
				var dateFrom = EvnForensicSubGrid.datePickerRange.isVisible()?(Ext.Date.format(EvnForensicSubGrid.datePickerRange.dateFrom, 'd.m.Y')+' 00:00:00'):'01.01.1900 00:00:00',
					dateTo = EvnForensicSubGrid.datePickerRange.isVisible()?(Ext.Date.format(EvnForensicSubGrid.datePickerRange.dateTo, 'd.m.Y')+' 23:59:59'):(Ext.Date.format(new Date, 'd.m.Y')+' 23:59:59'),

					pattern = 'ForensicSubReportWorking.rptdesign',
					params = '&paramMedService='+(getGlobalOptions().CurMedService_id || '')+'&paramBegDate='+dateFrom+'&paramEndDate='+dateTo;

				printBirt({
					'Report_FileName': pattern,
					'Report_Params': params,
					'Report_Format': 'pdf'
				});
			}
		});
		
		me.callParent(arguments);
		
		//Передаем дополнительные параметры в метод загрузки количества заявок,
		//Проверяем доступность кнопки создания
		this.JournalTreePanel.on('beforeselect',function( tree, record, index, eOpts ){
			if ( !record.data ) {
				return;
			}

			if ( record.data.loadStoreParams.params ) {
				this.updateRequestCountInTabsParams = record.data.loadStoreParams.params;

				// Функционал эксперта
				if ( this.allowExpertFunctionality ) {
					this.XmlType_id = record.data.loadStoreParams.params.XmlType_id;
				}
			}

			var ForensicSubType_id = (!record.data
					|| !record.data.loadStoreParams
					|| !record.data.loadStoreParams.params
					|| !record.data.loadStoreParams.params.ForensicSubType_id
					|| !record.data.loadStoreParams.params.XmlType_id
				)?0:record.data.loadStoreParams.params.ForensicSubType_id;
			this.createRequestButton.setDisabled( !ForensicSubType_id );
		}, this);
		
		// Функционал эксперта
		if ( this.allowExpertFunctionality ) {
			this.RequestListDataview.on("itemclick", function(self, rec, html_element, node_index, event){
				this.mixins.swExpertWorkPlace.changeDirectionMenuOnItemClick.call(this, rec);
				this.mixins.swExpertWorkPlace.changeTemplateButtonOnItemClick.call(this, rec);
				this.mixins.swExpertWorkPlace.getRequestListDataviewItemClick.call(this, self, rec, html_element, node_index, event);
			}, this);
		}
	}
})
		
