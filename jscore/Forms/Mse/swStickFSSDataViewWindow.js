/**
 * swStickFSSDataViewWindow - окно просмотра запросов в ФСС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Mse
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Dmitrii Vlasenko
 * @version			17.08.2017
 */

/*NO PARSE JSON*/

sw.Promed.swStickFSSDataViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swStickFSSDataViewWindow',
	width: 640,
	height: 800,
	maximized: true,
	maximizable: false,
	layout: 'border',
	title: 'Запросы в ФСС',

	searchStickFSSData: function(reset, callback) {
		var base_form = this.StickFSSDataFilter.getForm();
		if (reset) {
			base_form.reset();
			var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			base_form.findField('StickFSSData_DateRange').setValue(Ext.util.Format.date(date.add('d',-30), 'd.m.Y')+' - '+Ext.util.Format.date(date));
		}
		var params = {globalFilters: base_form.getValues()};
		params.callback = (typeof callback == 'function') ? callback : Ext.emptyFn;
		this.StickFSSDataGrid.loadData(params);
	},

	searchStickFSSDataGet: function(StickFSSData_id, reset) {
		if (!StickFSSData_id) {
			return false;
		}

		var params = {};
		params.StickFSSData_id = StickFSSData_id;

		var grid = null;
		switch(this.DetailsTabPanel.getActiveTab().id) {
			case 'StickFSSDataGet':
				grid = this.StickFSSDataGetGrid;
				break;
			case 'StickFSSError':
				grid = this.StickFSSErrorGrid;
				params.StickFSSErrorStageType_Code = 1;
				break;
		}

		if (grid) {
			grid.loadData({globalFilters: params});
		}
	},

	onStickFSSDataSelect: function(record) {

		if (record && (Ext.isEmpty(record.get('StickFSSDataStatus_Code')) || !record.get('StickFSSDataStatus_Code').inlist([1, 5, 7, 11]))) {
			this.StickFSSDataGrid.getAction('action_edit').enable();
			//this.StickFSSDataGrid.getAction('action_delete').enable();
		} else {
			this.StickFSSDataGrid.getAction('action_edit').disable();
			this.StickFSSDataGrid.getAction('action_delete').disable();
		}

		if ( record && record.get('StickFSSDataStatus_id') && record.get('StickFSSDataStatus_id').inlist([1,3]) ){
			this.StickFSSDataGrid.getAction('action_delete').enable();
		} else {
			this.StickFSSDataGrid.getAction('action_delete').disable();
		}

		if (Ext.getCmp('SFDVW_StickFSSDataRequestToFSS')) {
			Ext.getCmp('SFDVW_StickFSSDataRequestToFSS').disable();
			// доступен для реестров с типом: «Электронные ЛН» или «ЛН на удаление»
			if (
				record 	&& (
					(!Ext.isEmpty(record.get('StickFSSDataStatus_id')) && record.get('StickFSSDataStatus_id').inlist([1,3]))
					|| (record.get('StickFSSData_IsNeedMSE') == 2 && record.get('StickFSSType_Code') == '040')
				)
			) {
				Ext.getCmp('SFDVW_StickFSSDataRequestToFSS').enable();
			}
		}

		if (record && record.get('StickFSSData_id')) {
			switch (this.DetailsTabPanel.getActiveTab().id) {
				case 'StickFSSDataParams':
					this.overwriteStickFSSDataTpl(record);
					break;

				case 'StickFSSDataGet':
					this.searchStickFSSDataGet(record.get('StickFSSData_id'));
					break;

				case 'StickFSSError':
					this.searchStickFSSDataGet(record.get('StickFSSData_id'));
					break;
			}
		} else {
			this.overwriteStickFSSDataTpl();
		}
	},

	getStickFSSDataId: function() {
		var record = this.StickFSSDataGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('StickFSSData_id')) {
			return undefined;
		} else {
			return record.get('StickFSSData_id');
		}
	},

	overwriteStickFSSDataTpl: function(record){
		var sparams = {
			StickFSSData_Num: '',
			StickFSSData_insDT: '',
			Lpu_Nick: '',
			Lpu_FSSRegNum: '',
			Lpu_INN: '',
			Lpu_OGRN: '',
			pmUser_Name: '',
			Person_Fio: '',
			Person_Snils: ''
		};

		if (record) {
			sparams = Ext.apply(sparams, record.data);
			sparams.StickFSSData_insDT = Ext.util.Format.date(sparams.StickFSSData_insDT, 'd.m.Y H:i:s');
		}
		this.StickFSSDataTpl.overwrite(this.StickFSSDataPanel.body, sparams);
	},
	hex2bin: function (hex) {
		var bytes = [], str;
		for (var i = 0; i < hex.length - 1; i += 2)
			bytes.push(parseInt(hex.substr(i, 2), 16));
		return String.fromCharCode.apply(String, bytes);
	},
	listeners: {
		'resize': function (win, nW, nH, oW, oH) {
			win.DetailsTabPanel.setHeight(Math.round(nH/3));
		}
	},
	doQuery: function() {
		var win = this;

		var grid = this.StickFSSDataGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || !record.get('StickFSSData_id')) {
			return false;
		}

		var params = {
			StickFSSData_id: record.get('StickFSSData_id')
		};

		var doc_signtype = getOthersOptions().doc_signtype;

		// выбираем сертификат
		getWnd('swCertSelectWindow').show({
			signType: doc_signtype,
			callback: function (cert) {
				params.certhash = cert.Cert_Thumbprint;
				params.certbase64 = cert.Cert_Base64;

				if (doc_signtype && doc_signtype.inlist(['authapplet', 'authapi', 'authapitomee'])) {
					params.needHash = 1;
				}

				win.getLoadMask('Создание запроса').show();
				Ext.Ajax.request({
					params: params,
					callback: function (options, success, response) {
						win.getLoadMask().hide();
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.xml) {
								if (doc_signtype && doc_signtype == 'authapplet') {
									// хотим подписать с помощью AuthApplet
									sw.Applets.AuthApplet.signText({
										text: response_obj.Base64ToSign,
										Cert_Thumbprint: cert.Cert_Thumbprint,
										callback: function (sSignedData) {
											params.signType = 'authapplet';
											params.xml = response_obj.xml;
											params.Hash = response_obj.Hash;
											params.SignedData = sSignedData;

											win.getLoadMask('Выполняется обмен с ФСС, пожалуйста подождите...').show();
											Ext.Ajax.request({
												params: params,
												url: '/?c=StickFSSData&m=queryStickFSSData',
												callback: function(options, success, response) {
													win.getLoadMask().hide();
													if (success) {
														win.searchStickFSSData();
													}
												}
											});
										}
									});
								} else if (doc_signtype && doc_signtype.inlist(['authapi', 'authapitomee'])) {
									// хотим подписать с помощью AuthApi
									sw.Applets.AuthApi.signText({
										win: win,
										text: response_obj.Base64ToSign,
										Cert_Thumbprint: cert.Cert_Thumbprint,
										callback: function (sSignedData) {
											params.signType = 'authapplet';
											params.xml = response_obj.xml;
											params.Hash = response_obj.Hash;
											params.SignedData = sSignedData;

											win.getLoadMask('Выполняется обмен с ФСС, пожалуйста подождите...').show();
											Ext.Ajax.request({
												params: params,
												url: '/?c=StickFSSData&m=queryStickFSSData',
												callback: function(options, success, response) {
													win.getLoadMask().hide();
													if (success) {
														win.searchStickFSSData();
													}
												}
											});
										}
									});
								} else {
									// подписываем файл экспорта с помощью КриптоПро
									sw.Applets.CryptoPro.signXML({
										xml: response_obj.xml,
										Cert_Thumbprint: cert.Cert_Thumbprint,
										callback: function(sSignedData) {
											params.signType = 'cryptopro';
											params.xml = sSignedData;
											// 3. пробуем отправить в ФСС
											win.getLoadMask('Выполняется обмен с ФСС, пожалуйста подождите...').show();
											Ext.Ajax.request({
												params: params,
												url: '/?c=StickFSSData&m=queryStickFSSData',
												callback: function(options, success, response) {
													win.getLoadMask().hide();
													if (success) {
														win.searchStickFSSData();
													}
												}
											});
										}
									});
								}
							}
						}
					},
					url: '/?c=StickFSSData&m=exportStickFSSDataToXml'
				});
			}
		});
	},
	show: function(){
		sw.Promed.swStickFSSDataViewWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = this.StickFSSDataFilter.getForm();

		if( IS_DEBUG ) {
			this.StickFSSDataGrid.addActions({
				name: 'action_uploadXml',
				text: 'Загрузить тестовый файл',
				handler: function() {
					var selModel = win.StickFSSDataGrid.getGrid().getSelectionModel();
					var params = {
						saveUrl: '/?c=StickFSSData&m=UploadTestXml',
						saveParams:{
							StickFSSData_id: selModel.getSelected().get('StickFSSData_id')
						},
						callback: function() {
							win.StickFSSDataGrid.getAction('action_refresh').execute();
						},
						ignoreCheckData: true
					}

					getWnd('swFileUploadWindow').show(params);
				}
			});
		}

		this.StickFSSDataGrid.addActions({
			name:'action_new',
			text:lang['deystviya'],
			iconCls: 'actions16',
			menu: new Ext.menu.Menu({
				id:'SFDVW_StickFSSDataMenu',
				items: [{
					text: 'Подписать и отправить в ФСС',
					id: 'SFDVW_StickFSSDataRequestToFSS',
					handler: function() {
						win.doQuery();
					}.createDelegate(this)
				}]
			})
		});

		this.searchStickFSSData(true);
	},

	openStickFSSDataEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var grid = this.StickFSSDataGrid.getGrid();
		var params = {action: action};

		params.ignoreCheckExist = true;

		if (action != 'add') {
			params.StickFSSData_id = grid.getSelectionModel().getSelected().get('StickFSSData_id');
		}

		params.callback = function() {
			this.StickFSSDataGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swStickFSSDataEditWindow').show(params);
	},

	initComponent: function() {
		var win = this;

		this.StickFSSDataFilter = new sw.Promed.FormPanel({
			keys:
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e)
				{
					win.searchStickFSSData();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'daterangefield',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						name: 'StickFSSData_DateRange',
						fieldLabel: 'Период',
						width: 180
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 20px',
					items: [{
						xtype: 'button',
						handler: function () {
							this.searchStickFSSData();
						}.createDelegate(this),
						iconCls: 'search16',
						id: 'SFDVW_StickFSSDataSearchButton',
						text: lang['nayti'],
						minWidth: 80
					}]
				}, {
					layout: 'form',
					style: 'margin-left: 10px',
					items: [{
						xtype: 'button',
						handler: function () {
							this.searchStickFSSData(true);
						}.createDelegate(this),
						iconCls: 'resetsearch16',
						id: 'SFDVW_StickFSSDataResetButton',
						text: lang['sbrosit']
					}]
				}]
			}]
		});

		this.StickFSSDataGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=StickFSSData&m=loadStickFSSDataGrid',
			id: 'SFDVW_StickFSSDataGrid',
			border: true,
			autoLoadData: false,
			object: 'StickFSSData',
			region: 'center',
			grouping: true,
			groupField: 'StickFSSData_IsNeedMSE_StatusName',
			showGroup: true,
			headergrouping: true,
			groupingView: {showGroupName: false, showGroupsText: false},
			groupSortInfo: {
				field: 'StickFSSData_IsNeedMSE_StatusName',
				direction: 'asc'
			},
			stringfields: [
				{name: 'StickFSSData_id', type: 'int', header: 'StickFSSData_id', key: true, hidden:!isSuperAdmin()},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'Lpu_Nick', type: 'string', hidden: true},
				{name: 'Lpu_FSSRegNum', type: 'string', hidden: true},
				{name: 'Lpu_INN', type: 'string', hidden: true},
				{name: 'Lpu_OGRN', type: 'string', hidden: true},
				{name: 'Person_Snils', type: 'string', hidden: true},
				{name: 'StickFSSData_IsNeedMSE', type: 'int', hidden: true},
				{name: 'StickFSSType_Code', type: 'string', hidden: true},
				{name: 'StickFSSData_IsNeedMSE_StatusName', type: 'string', group: true},
				{name: 'StickFSSDataStatus_id', type: 'int', hidden: true},
				{name: 'StickFSSData_Num', type: 'int', header: 'Номер запроса', width: 120},
				{name: 'StickFSSData_insDT', type: 'datetime', header: 'Дата и время создания запроса', width: 120},
				{name: 'pmUser_Name', type: 'string', header: 'Пользователь', width: 120},
				{name: 'EvnStick_ParentTypeName', type: 'string', header: 'ТАП/КВС', width: 120},
				{name: 'EvnStick_ParentNum', type: 'string', header: 'Номер ТАП/КВС', width: 120},
				{name: 'Person_Fio', type: 'string', header: 'Пациент', width: 120},
				{name: 'StickFSSData_StickNum', type: 'string', header: 'Запрос по ЭЛН №', width: 120},
				{name: 'StickFSSData_xmlExpDT', type: 'datetime', header: 'Дата и время отправки', width: 120},
				{name: 'StickFSSDataStatus_Name', type: 'string', header: 'Статус', width: 120},
				{name: 'StickFSSType_Name', type: 'string', header: 'Состояние ЛВН в ФСС', width: 120},
				{name: 'StickFSSData_hasErrors', type: 'string', header: 'Расхождения', width: 120},
				{name: 'StickFSSData_showLogs', renderer: function(v, p, r) {
					if (r.get('StickFSSData_id') && r.get('StickFSSDataStatus_Name') != 'Ожидает отправки') {
						return "<a href='/?c=StickFSSData&m=showFiles&StickFSSData_id=" + r.get('StickFSSData_id') + "' target='_blank'>Просмотреть</a>";
					} else {
						return "";
					}
				}, header: 'Выгрузка запроса/ответа', width: 120}
			],
			actions: [
				{name:'action_add', hidden: false, disabled: false, handler: function() {
					this.openStickFSSDataEditWindow('add');
				}.createDelegate(this)},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', handler: function(){this.openStickFSSDataEditWindow('view')}.createDelegate(this)},
				{name:'action_delete', hidden: (getRegionNick()=='kz')?true:false, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,index,record){
				this.onStickFSSDataSelect(record);
			}.createDelegate(this)
		});

		this.StickFSSDataTpl = new Ext.XTemplate([
			'<div style="padding:2px;font-size: 12px;"><b>Номер запроса: {StickFSSData_Num}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Дата и время создания запроса: {StickFSSData_insDT}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>МО: {Lpu_Nick}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Рег. номер МО в ФСС: {Lpu_FSSRegNum}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>ИНН МО: {Lpu_INN}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>ОГРН МО: {Lpu_OGRN}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Пользователь: {pmUser_Name}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>Пациент: {Person_Fio}</b></div>',
			'<div style="padding:2px;font-size: 12px;"><b>СНИЛС пациента: {Person_Snils}</b></div>'
		]);
		this.StickFSSDataPanel = new Ext.Panel({
			id: 'SFDVW_StickFSSDataPanel',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			border: false,
			layout: 'fit',
			height: 28,
			maxSize: 28,
			html: ''
		});

		this.StickFSSDataGetGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=StickFSSData&m=loadStickFSSDataGetGrid',
			id: 'SFDVW_StickFSSDataGetGrid',
			border: false,
			autoLoadData: false,
			region: 'center',
			toolbar: true,
			stringfields: [
				{name: 'StickFSSDataGetGrid_id', type: 'int', header: 'ID', key: true},
				{name: 'StickFSSDataGet_Field', type: 'string', header: 'Категория', width: 300},
				{name: 'StickFSSDataGet_Value', type: 'string', header: 'Значение', width: 120, id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			]
		});

		this.StickFSSErrorGrid = new sw.Promed.ViewFrame({
			dataUrl: '/?c=StickFSSData&m=loadStickFSSErrorGrid',
			id: 'SFDVW_StickFSSErrorGrid',
			border: false,
			autoLoadData: false,
			region: 'center',
			toolbar: false,
			stringfields: [
				{name: 'StickFSSError_id', type: 'int', header: 'ID', key: true},
				{name: 'StickFSSErrorType_Code', type: 'string', header: 'Код ошибки', width: 100},
				{name: 'StickFSSErrorType_Name', type: 'string', header: 'Описание ошибки', id: 'autoexpand'},
				{name: 'StickFSSDataGet_StickNum', type: 'int', header: 'Номер ЭЛН', width: 120}
			],
			actions: [
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			]
		});

		this.DetailsTabPanel = new Ext.TabPanel({
			border: false,
			autoHeight: true,
			activeTab: 0,
			split: true,
			id: 'SFDVW_DetailsTabPanel',
			layoutOnTabChange: true,
			region: 'south',
			items: [{
				border: false,
				frame: false,
				id: 'StickFSSDataParams',
				title: 'Параметры запроса',
				items: []
			}, {
				border: false,
				frame: false,
				id: 'StickFSSDataGet',
				title: 'Данные ЭЛН',
				items: []
			}, {
				border: false,
				frame: false,
				id: 'StickFSSError',
				title: 'Расхождение данных',
				items: []
			}],
			listeners:
			{
				tabchange: function(panel, tab) {
					log(win.DetailsCardPanel.getLayout());
					switch(tab.id) {
						case 'StickFSSDataParams':
							win.DetailsCardPanel.getLayout().setActiveItem(0);
							break;
						case 'StickFSSDataGet':
							win.DetailsCardPanel.getLayout().setActiveItem(1);
							break;
						case 'StickFSSError':
							win.DetailsCardPanel.getLayout().setActiveItem(2);
							break;
					}
					win.DetailsDataPanel.doLayout();

					var record = this.StickFSSDataGrid.getGrid().getSelectionModel().getSelected();
					this.onStickFSSDataSelect(record);
				}.createDelegate(this)
			}
		});

		win.DetailsCardPanel = new sw.Promed.Panel({
			title: '',
			layout: 'card',
			region: 'center',
			height: 200,
			activeItem: 0,
			border: false,
			items: [
				win.StickFSSDataPanel,
				win.StickFSSDataGetGrid,
				win.StickFSSErrorGrid
			]
		});

		win.DetailsDataPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [
				this.DetailsCardPanel
			]
		});

		Ext.apply(this, {
			items: [{
				border: false,
				layout: 'border',
				region: 'center',
				items: [
					{
						autoHeight: true,
						frame: true,
						region: 'north',
						items: [this.StickFSSDataFilter]
					},
					this.StickFSSDataGrid
				]
			}, {
				border: false,
				layout: 'border',
				region: 'south',
				height: 380,
				items: [
					{
						autoHeight: true,
						frame: true,
						region: 'north',
						items: [
							this.DetailsTabPanel
						]
					},
					win.DetailsDataPanel
				]
			}],
			buttons: [
			{
				text: '-'
			},
			HelpButton(this, 1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'SFDVW_CancelButton',
				text: lang['zakryit']
			}]
		});

		sw.Promed.swStickFSSDataViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
