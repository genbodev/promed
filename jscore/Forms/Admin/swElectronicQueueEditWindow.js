/**
 * swElectronicQueueEditWindow - электронная очередь
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2015 Swan Ltd.
 */
/*NO PARSE JSON*/
sw.Promed.swElectronicQueueEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: false,
	maximized: true,
	height: 600,
	width: 900,
	id: 'swElectronicQueueEditWindow',
	title: 'Электронная очередь',
	layout: 'border',
	resizable: true,
	formAction: null,
	listeners: {
		'hide': function(wnd) {
			var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: "Подождите, идет удаление..."});
			if(wnd.action == 'add' && wnd.TempEQ && wnd.ElectronicQueueInfo_id) { // отменяем сохранение
				loadMask.show();
				Ext.Ajax.request({
					params: { ElectronicQueueInfo_id: wnd.ElectronicQueueInfo_id },
					callback: function() {
						loadMask.hide();
						wnd.hide();
					},
					url: '/?c=ElectronicQueueInfo&m=delete'
				});
			} else {
				wnd.hide();
			}		
		}
	},
	getMainForm: function()
	{
		return this.formEditPanel.getForm();
	},
	onGridRowSelect: function(grid) {

		var wnd = this;

		if (wnd.formAction && wnd.formAction != 'view') {
			grid.ViewActions.action_delete.setDisabled(false);
		}
	},
	clearGridFilter: function(grid) { //очищаем фильтры (необходимо делать всегда перед редактированием store)

		grid.getGrid().getStore().clearFilter();
	},
	setGridFilter: function(grid) { //скрывает удаленные записи
		grid.getGrid().getStore().filterBy(function(record){
			return (record.get('state') != 'delete');
		});
	},
	deleteGridRecord: function(){

		var wnd = this,
			view_frame = this.GridPanel,
			grid = view_frame.getGrid(),
			selected_record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_hotite_udalit_zapis'],
			title: lang['podtverjdenie'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {

				if ('yes' == buttonId) {
					if (selected_record.get('state') == 'add') {
						grid.getStore().remove(selected_record);
					} else {
						selected_record.set('state', 'delete');
						selected_record.commit();
						wnd.setGridFilter(view_frame);
					}
				} else {
					if (grid.getStore().getCount()>0) {
						grid.getView().focusRow(0);
					}
				}
			}
		});
	},
	//переделал на отложенное сохранение через jsonData
	openElectronicServiceEditWindow: function(action) {
		var wnd = this,
			grid = this.GridPanel.getGrid(),
			form = this.formEditPanel.getForm();

		var params = new Object();

		params.action = action;
		params.ElectronicQueueInfo_id = form.findField('ElectronicQueueInfo_id').getValue();
        params.MedServiceType_SysNick = form.findField('MedServiceType_SysNick').getValue();
        params.isProfosmotr = wnd.isProfosmotr;

		//имеет ли ЭО тип "Служба"
		params.queueIsService = form.findField('ElectronicQueueAssign').items.items[0].checked;

		var lpu_id = this.formEditPanel.getForm().findField('Lpu_id').getValue();
		if (lpu_id) params.Lpu_id = lpu_id;

		var view_frame = wnd.GridPanel,
			store = view_frame.getGrid().getStore();

		if (action == 'add') {

			params.onSave = function(data) {

				var record_count = store.getCount();
				if ( record_count == 1 && !store.getAt(0).get('ElectronicService_id') ) {
					view_frame.removeAll({addEmptyRecord: false});
				}

				var record = new Ext.data.Record.create(view_frame.jsonData['store']);
				wnd.clearGridFilter(view_frame);

				data.ElectronicService_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
				data.state = 'add';

				store.insert(record_count, new record(data));
				wnd.setGridFilter(view_frame);
			};

			getWnd('swElectronicServiceEditWindow').show(params);
		}

		if (action == 'edit' || action == 'view') {

			var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
			if (selected_record.get('ElectronicService_id') > 0) {

				var	selectedRecordFields = Object.keys(selected_record.data);

				selectedRecordFields.forEach(function(fieldName){
					params[fieldName] = selected_record.data[fieldName];
				});

				params.onSave = function(data) {

					wnd.clearGridFilter(view_frame);

					for(var key in data) {
						selected_record.set(key, data[key]);
					}

					if (selected_record.get('state') != 'add') {
						selected_record.set('state', 'edit');
					}

					selected_record.commit();
					wnd.setGridFilter(view_frame);
				};

				getWnd('swElectronicServiceEditWindow').show(params);
			}
		}
	},
	doSave: function(options) {

		if (typeof options != 'object') {
			options = new Object();
		}

		var wnd = this,
			form = this.formEditPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
			
		if (!form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.formEditPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!options.ignoreOffQuestion
			&& wnd.lastElectronicQueueInfo_IsOn != form.findField('ElectronicQueueInfo_IsOn').getValue()
		) {
			var code = form.findField('ElectronicQueueInfo_Code').getValue();
			var nick = form.findField('ElectronicQueueInfo_Nick').getValue();

			if (form.findField('ElectronicQueueInfo_IsOn').getValue()) {

				// Если у ЭО признак «Очередь выключена» изменилось на false
				sw.swMsg.alert('Внимание', 'Работа ЭО ' + code + ' ' + nick + ' возобновлена.');

			} else {

				// Если у ЭО признак «Очередь выключена» изменилось на true
				sw.swMsg.show({

					buttons: {
						yes: 'Продолжить',
						no: 'Отмена'
					},
					fn: function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							options.ignoreOffQuestion = 1;
							wnd.doSave(options);
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: 'ЭО ' + code + ' ' + nick + ' будет отключена. При отключении ЭО прием пациентов в автоматическом режиме недоступен.',
					title: 'Внимание'
				});
				return false;
			}
		}

		var params = {};

		
		params.IgnoreEQCodeDuplicate = (options.IgnoreEQCodeDuplicate == true)
		
		if (form.findField('Lpu_id').disabled) {
			params.Lpu_id = form.findField('Lpu_id').getValue();
		}

		params.servicesData = wnd.GridPanel.getJSONChangedData();
		loadMask.show();

		form.submit({
			failure: function(result_form, action) {
				var response = Ext.util.JSON.decode(action.response.responseText);

				wnd.TempEQ = false;
				loadMask.hide();

				switch ( response.Error_Code ) {
					case 101:
					case 103:
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING,
							title: ERR_WND_TIT,
							msg: response.Alert_Msg,
							fn: function( buttonId ) {
								if (buttonId == 'ok') {
									form.findField('ElectronicQueueInfo_Code').setValue('');
								}
							}
						});
					break;
					case 102:
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							title: langs('Продолжить сохранение?'),
							icon: Ext.MessageBox.QUESTION,
							msg: response.Alert_Msg,
							fn: function( buttonId ) {
								if( buttonId == 'yes' ) {
									options.IgnoreEQCodeDuplicate = true;
									wnd.doSave(options);
								}	
							}
						});
					break;
					case 104:
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							title: langs('Данные сохранились, но подключение к Node.js вернуло:'),
							icon: Ext.MessageBox.WARNING,
							msg: response.Error_Msg,
							fn: function(){
								wnd.hide();
								wnd.returnFunc();
							}
						});
						break;
				}
			},
			params: params,
			success: function(result_form, action) {
				wnd.TempEQ = false;
				loadMask.hide();
				if (action.result) {

					wnd.hide();
					wnd.returnFunc();

				} else {
					Ext.Msg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
				}

			}.createDelegate(this)
		});
	},
	toggleElectronicQueueAssign: function(selectedType) {

		var wnd = this,
			form = wnd.getMainForm(),
			medServiceCombo = form.findField('MedService_id'),
			lpuBuildingCombo = form.findField('LpuBuilding_id'),
			lpuSectionCombo = form.findField('LpuSection_id'),
			inputParams = {
				Lpu_id:  form.findField('Lpu_id').getValue()
			};

		switch (selectedType) {

			case 'medservice':

				if (wnd.action != 'view') {

					form.clearInvalid();

					medServiceCombo.setDisabled(false);
					lpuBuildingCombo.setDisabled(true);
					lpuSectionCombo.setDisabled(true);
				}

				medServiceCombo.showContainer();
				lpuBuildingCombo.hideContainer();
				lpuSectionCombo.hideContainer();

				lpuBuildingCombo.clearValue();
				lpuSectionCombo.clearValue();
				lpuSectionCombo.getStore().removeAll();

				//wnd.loadComboData(medServiceCombo, inputParams, (medServiceCombo.getValue() ? medServiceCombo.getValue() : null));

				break;

			case 'lpubuilding':

				if (wnd.action != 'view') {

					form.clearInvalid();

					lpuBuildingCombo.setDisabled(false);
					lpuSectionCombo.setDisabled(false);
					medServiceCombo.setDisabled(true);
				}


				lpuBuildingCombo.showContainer();
				lpuSectionCombo.showContainer();
				medServiceCombo.hideContainer();

				medServiceCombo.clearValue();

				//wnd.loadComboData(lpuBuildingCombo, inputParams, (lpuBuildingCombo.getValue() ? medServiceCombo.getValue() : null));

				break;
		}
	},
	initComponent: function()
	{
		var wnd = this;

		this.formEditPanel = new Ext.FormPanel({
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 100,
			frame: true,
			border: false,
			items: [{
				border: false,
				layout: 'column',
				labelWidth: 140,
				anchor: '10',
				items: [{
					layout: 'form',
					columnWidth: .50,
					border: false,
					items: [
						{
							name: 'ElectronicQueueInfo_id',
							xtype: 'hidden'
						},
						{
							allowBlank: true,
							name: 'MedServiceType_SysNick',
							xtype: 'hidden'
						},
						{
							xtype: 'fieldset',
							autoHeight: true,
							collapsible: false,
							title: 'Основные настройки',
							style: 'margin-top: 5px; margin-right: 10px; height: 340px;',
							labelWidth: 230,
							items: [
								{
									name: 'ElectronicQueueInfo_Code',
									fieldLabel: 'Код ЭО',
									xtype: 'textfield',
									allowBlank: false,
									maskRe: /[0-9]/,
									autoCreate: {tag: "input", type: "text", size: "2", maxLength: "2", autocomplete: "off"},
									width: 150
								}, {
									name: 'ElectronicQueueInfo_Name',
									fieldLabel: 'Наименование',
									xtype: 'textfield',
									allowBlank: false,
									width: 350
								}, {
									name: 'ElectronicQueueInfo_Nick',
									fieldLabel: 'Краткое наименование',
									xtype: 'textfield',
									allowBlank: true,
									width: 350
								}, {
									name: 'ElectronicQueueInfo_begDate',
									fieldLabel: 'Дата начала',
									xtype: 'swdatefield',
									allowBlank: false,
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
									width: 100
								}, {
									name: 'ElectronicQueueInfo_endDate',
									fieldLabel: 'Дата окончания',
									xtype: 'swdatefield',
									allowBlank: true,
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
									width: 100
								},
								new sw.Promed.SwLpuSearchCombo({
									fieldLabel: 'МО',
									hiddenName: 'Lpu_id',
									allowBlank: false,
									listWidth: 400,
									width: 350,
									listeners: {
										'change': function (combo, newValue, oldValue) {

											var radioGroup = wnd.formEditPanel.getForm().findField('ElectronicQueueAssign');

											if (newValue && radioGroup.disabled) {
												radioGroup.setDisabled(false);
											}

											var
												mscombo = wnd.formEditPanel.getForm().findField('MedService_id'),
												lbcombo = wnd.formEditPanel.getForm().findField('LpuBuilding_id'),
												lscombo = wnd.formEditPanel.getForm().findField('LpuSection_id');

											if ( Ext.isEmpty(newValue) ) {
												mscombo.clearValue();
												lbcombo.clearValue();
												lscombo.clearValue();

												mscombo.getStore().removeAll();
												lbcombo.getStore().removeAll();
												lscombo.getStore().removeAll();

												return false;
											}
											else if ( newValue == oldValue ) {
												return false;
											}

											var
												index,
												MedService_id = mscombo.getValue(),
												LpuBuilding_id = lbcombo.getValue(),
												LpuSection_id = lscombo.getValue();

											mscombo.clearValue();
											lbcombo.clearValue();
											lscombo.clearValue();

											lscombo.getStore().removeAll();

											mscombo.getStore().baseParams.Lpu_id = newValue;
											mscombo.getStore().load();

											lbcombo.getStore().baseParams.Lpu_id = newValue;
											lbcombo.getStore().load({
												callback: function() {
													if ( Ext.isEmpty(LpuBuilding_id) ) {
														return false;
													}

													index = lbcombo.getStore().findBy(function(rec) {
														return (rec.get('LpuBuilding_id') == LpuBuilding_id);
													});

													if ( index == -1 ) {
														return false;
													}

													lbcombo.setValue(LpuBuilding_id);

													lscombo.getStore().baseParams.LpuBuilding_id = LpuBuilding_id;
													lscombo.getStore().load({
														callback: function() {
															if ( Ext.isEmpty(LpuSection_id) ) {
																return false;
															}

															index = lscombo.getStore().findBy(function(rec) {
																return (rec.get('LpuSection_id') == LpuSection_id);
															});

															if ( index == -1 ) {
																return false;
															}

															lscombo.setValue(LpuSection_id);
														}
													});
												}
											});
										}
									}
								}),{
									fieldLabel: 'Назначение',
									xtype: 'radiogroup',
									width: 270,
									columns: 1,
									name: 'ElectronicQueueAssign',
									id: 'ElectronicQueueAssign',
									items: [
										{
											name: 'ElectronicQueueAssign',
											id: 'ElectronicQueueAssignToMedservice',
											boxLabel: 'Служба МО',
											inputValue: 'medservice'
										},
										{
											name: 'ElectronicQueueAssign',
											id: 'ElectronicQueueAssignToLpuBuilding',
											boxLabel: 'Подразделение МО',
											inputValue: 'lpubuilding'
										}
									],
									listeners: {
										'change': function (radioGroup, radioBtn) {

											if (radioBtn) {
												wnd.toggleElectronicQueueAssign(radioBtn.inputValue);
											}
										}
									}
								},
								{
									xtype: 'swmedserviceglobalcombo',
									hiddenName: 'MedService_id',
									//allowBlank: false,
									width: 250,
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var base_form = form = wnd.formEditPanel.getForm();
											if(combo.getValue()) {
												
												var MedServiceType_SysNick = combo.getFieldValue('MedServiceType_SysNick');
												
												wnd.isProfosmotr = ( MedServiceType_SysNick.inlist(['profosmotrvz', 'profosmotr']) );
												wnd.GridPanel.getAction('action_order_service_points').setDisabled(!wnd.isProfosmotr);

											}	
										}
									}
								}, {
									xtype: 'swlpubuildingcombo',
									hiddenName: 'LpuBuilding_id',
									//allowBlank: false,
									width: 250,
									listeners:{
										'change':function (combo, newValue, oldValue) {
											var lscombo = wnd.formEditPanel.getForm().findField('LpuSection_id');

											if ( Ext.isEmpty(newValue) ) {
												lscombo.clearValue();
												lscombo.getStore().removeAll();
												return false;
											}
											else if ( newValue == oldValue ) {
												return false;
											}

											var
												index,
												LpuSection_id = lscombo.getValue();

											lscombo.clearValue();
											lscombo.getStore().removeAll();

											lscombo.getStore().baseParams.LpuBuilding_id = newValue;
											lscombo.getStore().load({
												callback: function() {
													if ( Ext.isEmpty(LpuSection_id) ) {
														return false;
													}

													index = lscombo.getStore().findBy(function(rec) {
														return (rec.get('LpuSection_id') == LpuSection_id);
													});

													if ( index == -1 ) {
														return false;
													}

													lscombo.setValue(LpuSection_id);
												}
											});
										}.createDelegate(this)
									},
								}, {
									xtype: 'swlpusectioncombo',
									hiddenName: 'LpuSection_id',
									//allowBlank: false,
									width: 250
								},
								{
									name: 'ElectronicQueueInfo_IsOn',
									fieldLabel: 'ЭО включена',
									xtype: 'checkbox',
									allowBlank: false,
									width: 100
								}
							]
						}

					]
				},
					{
						layout: 'form',
						columnWidth: .50,
						border: false,
						items: [
							{
								xtype: 'fieldset',
								autoHeight: true,
								collapsible: false,
								title: 'Опции',
								style: 'margin-top: 5px; margin-right: 10px; height: 340px;',
								labelWidth: 230,
								items: [{
									fieldLabel: 'Продолжительность вызова (сек.)',
									name: 'ElectronicQueueInfo_CallTimeSec',
									xtype: 'numberfield',
									minValue: 30,
									maxValue: 300,
									width: 100,
									value: 30,
									allowBlank: false
								}, {
									fieldLabel: 'Время, за которое возможна регистрация в очереди (мин.)',
									name: 'ElectronicQueueInfo_QueueTimeMin',
									xtype: 'numberfield',
									minValue: 30,
									maxValue: 100,
									width: 100,
									value: 100,
									allowBlank: false
								}, {
									fieldLabel: 'Время опоздания при регистрации в очереди (мин.)',
									name: 'ElectronicQueueInfo_LateTimeMin',
									xtype: 'numberfield',
									minValue: 0,
									maxValue: 30,
									width: 100,
									value: 10,
									allowBlank: false
								}, {
									fieldLabel: 'Количество вызовов (до отмены пациента)',
									name: 'ElectronicQueueInfo_CallCount',
									xtype: 'numberfield',
									minValue: 1,
									maxValue: 20,
									width: 100,
									value: 2,
									allowBlank: false
								}, {
									fieldLabel: 'Время отсрочки вызова пациента после регистрации (мин)',
									name: 'ElectronicQueueInfo_PersCallDelTimeMin',
									xtype: 'numberfield',
									minValue: 0,
									maxValue: 10,
									width: 100,
									value: 0,
									allowBlank: false
								}, {

									xtype: 'checkbox',
									width: 120,
									labelSeparator: '',
									id: 'ElectronicQueueInfo_IsIdent',
									fieldLabel: 'Идентифицикация пациента',
									checked: true,
								},
								{

									xtype: 'checkbox',
									width: 120,
									labelSeparator: '',
									id: 'ElectronicQueueInfo_IsNoTTGInfo',
									fieldLabel: 'Скрывать дату и время бирки при печати талона',
									checked: true,
								},
									{
										xtype: 'fieldset',
										autoHeight: true,
										collapsible: false,
										title: 'Настройки предварительной записи',
										style: 'margin-top: 15px; margin-right: 10px; height: 60px;',
										labelWidth: 220,
										items: [
											{
												xtype: 'checkbox',
												width: 120,
												labelSeparator: '',
												id: 'ElectronicQueueInfo_IsCurDay',
												fieldLabel: 'Запись на текущий день',
												checked: false,
											},
											{
												xtype: 'checkbox',
												width: 120,
												labelSeparator: '',
												id: 'ElectronicQueueInfo_IsAutoReg',
												fieldLabel: 'Автоматическая регистрация в ЭО',
												checked: false,
											}
										]
									}
								]
							}]
					}
				]
				}],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'ElectronicQueueInfo_id' },
				{ name: 'Lpu_id' },
				{ name: 'ElectronicQueueInfo_Code' },
				{ name: 'ElectronicQueueInfo_Name' },
				{ name: 'ElectronicQueueInfo_Nick' },
				{ name: 'ElectronicQueueInfo_IsOn' },
				{ name: 'ElectronicQueueInfo_begDate' },
				{ name: 'ElectronicQueueInfo_endDate' },
				{ name: 'ElectronicQueueInfo_CallTimeSec' },
				{ name: 'ElectronicQueueInfo_QueueTimeMin' },
				{ name: 'ElectronicQueueInfo_LateTimeMin' },
				{ name: 'ElectronicQueueInfo_CallCount' },
				{ name: 'ElectronicQueueInfo_IsIdent' },
				{ name: 'ElectronicQueueInfo_IsCurDay' },
				{ name: 'ElectronicQueueInfo_IsAutoReg' },
				{ name: 'ElectronicQueueInfo_PersCallDelTimeMin'},
                { name: 'ElectronicQueueInfo_IsNoTTGInfo'},
				{ name: 'MedService_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedServiceType_SysNick'}
			]),
			url: '/?c=ElectronicQueueInfo&m=save'
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			id: wnd.id+'GridPanel',
			title: 'Пункты обслуживания',
			object: 'ElectronicService',
			dataUrl: '/?c=ElectronicService&m=loadList',
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			toolbar: true,
			useEmptyRecord: false,
			stringfields: [
				{name: 'ElectronicService_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'ElectronicService_Code', header: 'Код', width: 100},
				{name: 'ElectronicService_Name', header: 'Наименование', width: 150, id: 'autoexpand'},
				{name: 'ElectronicService_Nick', header: 'Краткое наименование', width: 150},
				{name: 'ElectronicService_Num', header: 'Порядковый номер', width: 100},
				{name: 'ElectronicService_isShownET', hidden: true},
				{name: 'ElectronicService_tid', hidden: true},
                {name: 'SurveyType_id', hidden: true}

			],
			actions: [
				{name:'action_add', handler: function() { wnd.openElectronicServiceEditWindow('add'); }},
				{name:'action_edit', handler: function() { wnd.openElectronicServiceEditWindow('edit'); }},
				{name:'action_view', handler: function() { wnd.openElectronicServiceEditWindow('view'); }},
				{name:'action_delete', handler: function() { wnd.deleteGridRecord() }},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_refresh', hidden: true}
			],
			onRowSelect: function(sm, rowIdx, record) {
				wnd.onGridRowSelect(this);
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				wnd.clearGridFilter(this);
				this.getGrid().getStore().each(function(record) {
					if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete') {
						data.push(record.data);
					}
				});
				wnd.setGridFilter(this);
				return data;
			},
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
		});

		this.formPanel = new Ext.Panel({
			region: 'center',
			labelAlign: 'right',
			layout: 'border',
			labelWidth: 50,
			border: false,
			items: [
				this.formEditPanel,
				this.GridPanel
			]
		});

		Ext.apply(this, {
			items: [
				wnd.formPanel
			],
			buttons: [{
				text: BTN_FRMSAVE,
				iconCls: 'save16',
				handler: function() {
					wnd.doSave();
				}
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_RRLW + 13),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RRLW + 14,
				handler: function() {
					wnd.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swElectronicQueueEditWindow.superclass.initComponent.apply(this, arguments);
	},
	setFieldsDisabled: function(d) {
		var form = this.formEditPanel.getForm();
		form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		this.buttons[0].show();
		this.buttons[0].setDisabled(d);
		this.GridPanel.setReadOnly(d);
	},
	loadComboData: function(combo, inputParams, setValue) {

		var form = this.formEditPanel.getForm();

		if (inputParams.Lpu_id)
			combo.getStore().baseParams.Lpu_id = inputParams.Lpu_id;

		combo.getStore().load({
			params: inputParams,
			callback: function(){

				if (setValue)
					combo.setValue(setValue);
			}
		});
	},
	show: function() {
		sw.Promed.swElectronicQueueEditWindow.superclass.show.apply(this, arguments);

		var wnd = this,
			form = this.formEditPanel.getForm(),
			grid = this.GridPanel;

		wnd.formAction = null;
		wnd.saved = false;
		wnd.TemEQ = false;

		form.reset();
		grid.getGrid().getStore().baseParams = {};
		grid.getGrid().getStore().removeAll();
		this.isAutosaved = false;
		
		if (arguments[0]['action']) {
			this.action = arguments[0]['action'];
			wnd.formAction = this.action;
		}

		if (arguments[0]['callback']) {
			this.returnFunc = arguments[0]['callback'];
		}
		
		if (arguments[0]['ElectronicQueueInfo_id']) {
			this.ElectronicQueueInfo_id = arguments[0]['ElectronicQueueInfo_id'];
		} else {
			this.ElectronicQueueInfo_id = null;
		}

		this.lastElectronicQueueInfo_IsOn = true;
		this.setFieldsDisabled(this.action == 'view');

		if (isLpuAdmin() && !isSuperAdmin()) {
			form.findField('Lpu_id').disable();
		}
		
		switch (this.action){
			case 'add':

				this.setTitle('Электронная очередь: Добавление');

				var radioGroup = form.findField('ElectronicQueueAssign');

				form.findField('ElectronicQueueInfo_IsOn').setValue(true);
				wnd.lastElectronicQueueInfo_IsOn = form.findField('ElectronicQueueInfo_IsOn').getValue();

				if (isLpuAdmin() && !isSuperAdmin()) {
					form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
					form.findField('Lpu_id').fireEvent('change', form.findField('Lpu_id'), form.findField('Lpu_id').getValue());
					form.findField('ElectronicQueueInfo_Code').focus(true, 100);
				} else {
					form.findField('Lpu_id').focus(true, 100);
				}

				// настройки для блока "Привязка" в режиме добавления
				form.findField('MedService_id').showContainer();
				form.findField('LpuBuilding_id').hideContainer();
				form.findField('LpuSection_id').hideContainer();

				// дизаблим пока не выбрано МО
				if (!form.findField('Lpu_id').getValue()) {
					radioGroup.setDisabled(true);
					form.findField('MedService_id').setDisabled(true);
				}

				break;

			case 'edit':

				this.setTitle('Электронная очередь: Редактирование');
				break;

			case 'view':

				this.setTitle('Электронная очередь: Просмотр');
				break;
		}
		wnd.GridPanel.addActions({
			name: 'action_order_service_points',
			text: 'Порядок пунктов обслуживания',
			handler: function() {
				if (wnd.action == 'add' && !wnd.saved) { // сохраняем очередь для добавления порядка 
					if (!form.isValid()) {
						sw.swMsg.show( {
							buttons: Ext.Msg.OK,
							fn: function() {
								wnd.formEditPanel.getFirstInvalidEl().focus(false);
							},
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}
					var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: "Подождите, идет сохранение..."});
					var form_params = form.getValues();
					form_params.ElectronicQueueInfo_IsOn = '';
					form_params.hiddenSave = 1;
					
					loadMask.show();
					Ext.Ajax.request({
						params: form_params,
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							wnd.saved = true;
							wnd.TempEQ = true;

							loadMask.hide();

							if(response_obj.success == true) {
								wnd.ElectronicQueueInfo_id = response_obj.ElectronicQueueInfo_id;
								form.findField('ElectronicQueueInfo_id').setValue(wnd.ElectronicQueueInfo_id);

								getWnd('swOrderServicePointsListWindow').show({
									ElectronicQueueInfo_id: wnd.ElectronicQueueInfo_id
								});
							}
						},
						failure: function() {
							loadMask.hide();
						},
						url: '/?c=ElectronicQueueInfo&m=save'
					});
				} else {
					getWnd('swOrderServicePointsListWindow').show({
						ElectronicQueueInfo_id: wnd.ElectronicQueueInfo_id
					});
				}
			}
		});
		var orderAction = wnd.GridPanel.getAction('action_order_service_points');
		orderAction.setDisabled(true);
		
		if (this.action != 'add') {

			var loadMask = new Ext.LoadMask(this.getEl(),{
				msg: "Подождите, идет загрузка..."
			});

			loadMask.show();

			form.load({

				url: '/?c=ElectronicQueueInfo&m=load',
				params: {
					ElectronicQueueInfo_id: wnd.ElectronicQueueInfo_id
				},
				success: function (elem, resp) {
					form.findField('Lpu_id').fireEvent('change', form.findField('Lpu_id'), form.findField('Lpu_id').getValue());

					var radioGroup = form.findField('ElectronicQueueAssign').items;
					
					
					if ( form.findField('MedServiceType_SysNick').getValue().inlist(['profosmotrvz', 'profosmotr']) ) {
						orderAction.setDisabled(false);
						wnd.isProfosmotr = true;
					} else {
						orderAction.setDisabled(true);
						wnd.isProfosmotr = false;
					}

					if (resp.result.data) {

						if (resp.result.data.MedService_id) {

							log('MedService_id',resp.result.data.MedService_id);

							// включаем радиокнопку "службы"
							radioGroup.each(function(radioBtn){

								if (radioBtn.inputValue == 'medservice') {
									radioBtn.setValue(true);
									form.findField('MedService_id').getStore().load({
										callback: function(){
											form.findField('MedService_id').setValue(resp.result.data.MedService_id);
										}
							});
								}
							});

						} else {

							if (resp.result.data.LpuBuilding_id) {

								// включаем радиокнопку "подразделение"
								radioGroup.each(function (radioBtn) {

									if (radioBtn.inputValue == 'lpubuilding') {
										radioBtn.setValue(true);
										form.findField('LpuBuilding_id').getStore().load({
											callback: function(){
												form.findField('LpuBuilding_id').setValue(resp.result.data.LpuBuilding_id);
											}
										});
										form.findField('LpuSection_id').getStore().load({
											callback: function(){
												form.findField('LpuSection_id').setValue(resp.result.data.LpuSection_id);
											}
										});
									}
								});
							}
						}

					} else {

						log('no resp.result.data');

						// включаем радиокнопку "службы"
						radioGroup.each(function(radioBtn){

							if (radioBtn.inputValue == 'medservice')
								radioBtn.setValue(true);
						});
					}

					wnd.lastElectronicQueueInfo_IsOn = form.findField('ElectronicQueueInfo_IsOn').getValue();

					grid.getGrid().getStore().baseParams = {
						ElectronicQueueInfo_id: wnd.ElectronicQueueInfo_id,
						start: 0,
						limit: 100
					};

					grid.loadData();
					loadMask.hide();
				},
				failure: function (elem, resp) {

					loadMask.hide();
					if (!resp.result.success) {
						Ext.Msg.alert(
							lang['oshibka'],
							lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']
						);
						this.hide();
					}
				},
				scope: this
			});
		}
	}
});