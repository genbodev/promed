/**
 * swPacketPrescrSelectWindow - Окно пакетных назначений ExtJS 6
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @author       gtp_fox
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.PacketPrescrExt2.swPacketPrescrSelectWindowExt2', {
	/* свойства */
	alias: 'widget.swPacketPrescrSelectWindowExt2',
	cls: 'arm-window-new packetWindow',
	constrain: true,
	extend: 'base.BaseForm',
	defaultAlign: 'tr',
	findWindow: false,
	refId: 'swPacketPrescrSelectWindow',
	title: 'Пакетные назначения',
	EvnPrescrPanelCntr: {},
	callback: Ext6.emptyFn,
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	requires: [
		'common.EMK.PacketPrescrExt2.AddPrescrByCheckGridsPanelExt2',
		'common.EMK.PacketPrescrExt2.AddPrescrInPacketByCheckGridsPanelExt2',
		'common.EMK.controllers.EvnPrescribePanelCntr'
	],
	resizable: false,
	maximized: true,
	swMaximized: true,
	header: false,
	border: false,
	width: 1000,
	bodyPadding: 0,
	margin: 0,
	padding: 0,
	layout: 'border',
	mode: 'my',
	controller: 'EvnPrescribePanelCntr',
	show: function () {
		if (!arguments || !arguments[0] || !arguments[0].Evn_id) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}
		var win = this;
		win.PatientInfoPanel.clearParams();
		if (arguments[0].callback) {
			win.callback = arguments[0].callback;
		} else {
			win.callback = Ext6.emptyFn;
		}
		win.MedPersonal_id = arguments[0].MedPersonal_id;
		win.EvnPrescrPanelCntr = arguments[0].EvnPrescrPanelCntr;
		win.PersonInfoPanel = arguments[0].PersonInfoPanel;
		win.Diag_id = arguments[0].Diag_id || null;
		win.PersonEvn_id = arguments[0].PersonEvn_id;
		win.LpuSection_id = arguments[0].LpuSection_id;
		win.Server_id = arguments[0].Server_id;
		win.Person_id = arguments[0].Person_id;
		win.Evn_id = arguments[0].Evn_id;
		win.data = arguments[0].data;
		var mode = 'my';
		if(arguments[0].packet){
			win.selectPacketPrescr_id = arguments[0].packet.PacketPrescr_id;
		}

		win.swRegimeCreateWindow = Ext6.create('common.EMK.QuickPrescrSelect.swRegimeCreateWindow');
		win.swDietCreateWindow = Ext6.create('common.EMK.QuickPrescrSelect.swDietCreateWindow');
		win.DrugSelectPanel = Ext6.create('common.EMK.QuickPrescrSelect.swDrugQuickSelectWindow', {
			parentPanel: win,
		});
		win.UslugaSelectPanel = Ext6.create('common.EMK.QuickPrescrSelect.swUslugaQuickSelectWindow',{
			parentPanel: win,
			reference: 'UslugaSelectPanel',
			onSelect: function(params){
				if(params.PacketPrescr_id)
					win.getController().addPrescrToPacket(params,this);
				else
					win.getController().saveEvnPrescr(params,this);
			}
		});

		win.getController().loadData({
			userMedStaffFact: win.EvnPrescrPanelCntr.userMedStaffFact,
			Person_id: win.data.Person_id,
			Server_id: win.data.Server_id,
			PersonEvn_id: win.data.PersonEvn_id,
			Evn_id: arguments[0].Evn_id,
			Evn_setDate: arguments[0].Evn_setDate,
			LpuSection_id: win.data.LpuSection_id,
			MedPersonal_id:win.data.MedPersonal_id
		}, arguments[0].EvnParams);

		win.EvnParams = arguments[0].EvnParams;
		this.callParent(arguments);
		if(arguments[0].mode)
			mode = arguments[0].mode;
		win.setMode(mode);

	},
	setMode: function(mode) {
		var win = this,
			evnParams = win.EvnParams;
		if(win.PersonInfoPanel){
			var pers = win.PersonInfoPanel,
				sex_id = pers.getFieldValue('Sex_id'),
				age = pers.getFieldValue('Person_Age');
			if(age)
				var ageGroup = (age<18)?2:1;
			var	fio = pers.getFieldValue('Person_Surname').charAt(0).toUpperCase()
					+ pers.getFieldValue('Person_Surname').slice(1).toLowerCase()
					+' '+ pers.getFieldValue('Person_Firname').charAt(0).toUpperCase()
					+ pers.getFieldValue('Person_Firname').slice(1).toLowerCase()
					+' '+pers.getFieldValue('Person_Secname').charAt(0).toUpperCase()
					+ pers.getFieldValue('Person_Secname').slice(1).toLowerCase();

			win.PatientInfoPanel.setParams({fio: fio});
			win.applySexAndAgeGroup(sex_id,ageGroup);
		}
		if(evnParams && evnParams.Diag_Name){
			var Diag_name = '(Диагноз ' + evnParams.Diag_Code + ' ' + evnParams.Diag_Name + ')';
			win.PatientInfoPanel.setParams({diag: Diag_name})
		}
		win.mode = mode;

		switch(win.mode) {
			// Вкладки "Мои" и "Общие"
			case 'my':
			case 'shared':
				break;
			// Вкладка "Клинические рекоммендации"
			case 'standart':
				break;
		}
		// Для смены значения кнопки, без смены состояния формы добавляем forceChangeModeBtn true
		win.loadGrid();
		win.focus();
	},
	applySexAndAgeGroup: function(sex_id,ageGroup){
		this.setFiltersAuto = true;
		if(sex_id)
			this.SexCombo.setValue(sex_id);
		else
			this.SexCombo.reset();
		if(sex_id)
			this.PersonAgeCombo.setValue(ageGroup);
		else
			this.PersonAgeCombo.reset();
		delete this.setFiltersAuto;
	},
	loadGrid: function() {
		var params = new Object(),
			win = this;
		var grid = win.PacketPrescrGrid;
		if (win.mode == 'standart') {
			grid = win.CureStandartsGrid;
		}

		params = Ext6.Object.merge(params,win.getFilterParams());
		params.MedPersonal_id = win.MedPersonal_id;
		params.Evn_id = win.Evn_id;

		params.mode = win.mode;

		params.PersonEvn_id = win.PersonEvn_id;
		params.Server_id = win.Server_id;
		params.Person_id = win.Person_id;
		params.Evn_id = win.Evn_id;
		//params.Diag_id = win.Diag_id;
		//params.CureStandart_id = win.selectCureStandart_id;
		//params.PacketPrescr_id = win.selectPacketPrescr_id;
		grid.getStore().removeAll();
		grid.getStore().load({
			params: params,
			callback: function () {
				win.onLoadGrid();
			}
		});
	},
	onLoadGrid: function(){
		var win = this;
		// Одноразовая функция, если есть, выполняем (нужно при создании пустого пакета)
		if(win.cbFn && typeof win.cbFn == 'function'){
			win.cbFn();
			delete win.cbFn;
		}
	},
	getFilterParams: function(mode){
		var win = this,
			params = {};
		switch(mode){
			case 'new_packet':
				if(win.SexCombo.getValue())
					params.Sex_id = win.SexCombo.getValue();
				if(win.PersonAgeCombo.getValue())
					params.PersonAgeGroup_id = win.PersonAgeCombo.getValue();
				break;
			default:
				params.mode = win.mode;
				if(win.withDiag.getValue())
					params.Diag_id = win.Diag_id;
				if(win.onlyFavor.getValue())
					params.onlyFavor = true;
				if(win.SexCombo.getValue())
					params.Sex_Code = win.SexCombo.getValue();
				if(win.PersonAgeCombo.getValue())
					params.PersonAgeGroup_Code = win.PersonAgeCombo.getValue();
		}
		return params;
	},
	loadPacketOrStandart: function() {
		var params = new Object(),
			win = this;
		var grid = win.PacketPrescrGrid;
		if (win.mode == 'standart') {
			grid = win.CureStandartsGrid;
		}
		params.MedPersonal_id = win.MedPersonal_id;
		params.Evn_id = win.Evn_id;
		params.mode = win.mode;

		params.PersonEvn_id = win.PersonEvn_id;
		params.Server_id = win.Server_id;
		params.Person_id = win.Person_id;
		params.Evn_id = win.Evn_id;

		switch(win.mode){
			case 'standart': {
				params.CureStandart_id = win.selectCureStandart_id;
				var AddPrescrByCheckGridsCntr = win.AddPrescrByCheckGridsPanel.getController();
				AddPrescrByCheckGridsCntr.loadGrids(params);
				break;
			}
			case 'my':
			case 'shared': {
				params.PacketPrescr_id = win.selectPacketPrescr_id;
				var AddPrescrInPacketByCheckGridsCntr = win.AddPrescrInPacketByCheckGridsPanel.getController();
				AddPrescrInPacketByCheckGridsCntr.loadGrids(params);
				break;
			}
		}
	},
	onSprLoad: function(arguments) {
		// Можно потанцевать
		this.setFiltersAuto = true;
		this.SexCombo.getStore().addFilter(function(rec) {
			return (rec.get('Sex_Code') != '3');
		});
		this.SexCombo.setValue(this.SexCombo.getValue());
		this.PersonAgeCombo.setValue(this.PersonAgeCombo.getValue());
		delete this.setFiltersAuto;
	},
	loadPacketInfoForm: function(PacketPrescr_id) {
		var me = this,
			form = me.PacketInfoPanel.getForm();
		if(Ext6.isEmpty(PacketPrescr_id))
			return false;
		me.mask(LOADING_MSG);
		me.loadData = true;
		form.load({
			params: {
				PacketPrescr_id: PacketPrescr_id
			},
			success: function (form, action) {
				var diag = form.findField('Diag_id');
				var descr = form.findField('PacketPrescr_Descr');
				diag.saveValue = diag.getValue();
				descr.saveValue = descr.getValue();
				delete me.loadData;
				me.unmask();
				if (action.response && action.response.responseText) {
					var data = Ext6.JSON.decode(action.response.responseText);
					//if(data && data[0])
					//me.setTitle(data[0]['PacketPrescr_Name']);
				}
			},
			failure: function (form, action) {
				delete me.loadData;
				me.unmask();
			}
		});
	},
	setFavorite: function(rec){
		var win = this,
			params = {};
		win.PacketPrescrGrid.mask('Изменение параметра');
		params.MedPersonal_id = win.MedPersonal_id;
		params.PacketPrescr_id = rec.get('PacketPrescr_id');
		params.Packet_IsFavorite  = (rec.get('Packet_IsFavorite') == 2)?0:2;
		Ext6.Ajax.request({
			url: '/?c=PacketPrescr&m=setPacketFavorite',
			params: params,
			callback: function(options, success, response) {
				if ( success )
					rec.set('Packet_IsFavorite',(params.Packet_IsFavorite?2:0));
				win.PacketPrescrGrid.unmask();
			}.createDelegate(this)
		});
	},
	getData: function(){
		return {
			'MedPersonal_id': this.MedPersonal_id,
			'Evn_id': this.Evn_id,
			'mode': this.mode,
			'PersonEvn_id': this.PersonEvn_id,
			'Server_id': this.Server_id,
			'Person_id': this.Person_id
		};
	},
	savePacketInfo: function(){
		var me = this;
		var base_form = me.PacketInfoPanel.getForm();

		if ( !base_form.isValid() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.formPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {
			MedPersonal_id: me.MedPersonal_id,
			PacketPrescr_id: me.PacketPrescr_id
		};

		sw4.showInfoMsg({
			panel: me,
			type: 'loading',
			text: 'Сохранение...'
		});
		base_form.submit({
			url: '/?c=PacketPrescr&m=createPacketPrescr',
			params: params,
			success: function(result_form, action) {
				sw4.showInfoMsg({
					panel: me,
					type: 'success',
					text: 'Данные сохранены.'
				});
			},
			failure: function(result_form, action) {
				sw4.showInfoMsg({
					panel: me,
					type: 'error',
					text: 'Ошибка сохранения данных.'
				});
			}
		});
	},
	deletePacket: function(){
		var win = this,
			g = win.PacketPrescrGrid,
			rec = g.getSelectionModel().getSelectedRecord();
		if(rec){
			var packet_id = rec.get('PacketPrescr_id');
			Ext6.Msg.alert({
				buttons: Ext6.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						Ext6.Ajax.request({
							url: '/?c=PacketPrescr&m=deletePacket',
							params: {
								PacketPrescr_id: packet_id
							},
							callback: function(options, success, response) {
								delete win.selectPacketPrescr_id;
								win.loadGrid();
							}.createDelegate(this)
						});
					}
				}.createDelegate(this),
				icon: Ext6.MessageBox.QUESTION,
				msg: 'Вы уверены что хотите удалить пакет?'
			});

		}
		else
			Ext6.Msg.alert('Ошибка', 'Необходимо выбрать пакет');
	},
	copyPacket: function(){
		var win = this,
			g = win.PacketPrescrGrid,
			rec = g.getSelectionModel().getSelectedRecord();
		if(rec){
			var packet_id = rec.get('PacketPrescr_id');
			Ext6.Msg.prompt({
				title: "Создать копию пакета",
				msg: "Название",
				prompt: true,
				minWidth: 450,
				buttons: Ext6.Msg.OKCANCEL,
				callback: function (btn, text) {
					if (btn == "ok") {
						win.mask("Копирование пакета");
						Ext6.Ajax.request({
							url: "/?c=PacketPrescr&m=copyPacket",
							params: {
								PacketPrescr_id: packet_id,
								PacketPrescr_Name: text
							},
							callback: function (options, success, response) {
								win.unmask();
								win.loadGrid();
								var data = Ext6.JSON.decode(response.responseText);
								if (data && data[0])
									win.selectPacketPrescr_id = data[0]["PacketPrescr_id"];
							}.createDelegate(this)
						});
					}
				},
				scope: null,
				multiline: false,
				value: rec.get("PacketPrescr_Name") + " - копия"
			});
		}
		else
			Ext6.Msg.alert('Ошибка', 'Необходимо выбрать пакет');
	},
	createEmptyPacket: function(){
		var me = this;


		var params = me.getFilterParams('new_packet');
		params.PacketPrescr_Name = 'Новый пакет';
		params.PacketPrescrVision_id = 1;
		params.MedPersonal_id = me.MedPersonal_id || getGlobalOptions().CurMedPersonal_id;
		params.Diag_id = (!Ext6.isEmpty(me.Diag_id))?me.Diag_id:'';

		Ext6.Ajax.request({
			url: '/?c=PacketPrescr&m=createEmptyPacketPrescr',
			params: params,
			callback: function(options, success, response) {
				if ( success )
				{
					var response_obj = Ext6.util.JSON.decode(response.responseText);
					if(response_obj && response_obj.PacketPrescr_id){
						me.selectPacketPrescr_id = response_obj.PacketPrescr_id;
						sw4.showInfoMsg({
							panel: me,
							type: 'success',
							text: 'Шаблон (пакет назначений) создан'
						});
						me.loadGrid();
					}
				}
			}.createDelegate(this)
		});

	},
	selectPacketById: function(PacketPrescr_id){
		if(!Ext6.isEmpty(PacketPrescr_id)){
			var me = this,
				grid = me.PacketPrescrGrid,
				store = grid.getStore();
			var rec = store.findRecord('PacketPrescr_id', PacketPrescr_id);
			if(rec)
				grid.getSelectionModel().select(rec);
		}
	},
	showFAQPanel: function(count){
		this.PacketStructure.getLayout().setActiveItem(((count>0)?0:1));
		this.PacketName.setVisible((count>0));
		this.PatientApplyPanel.setVisible((count>0));
		this.PacketInfoPanel.setDisabled();
		this.CancelPanel.setVisible((count<1));
		if(count)
			this.PacketInfoPanel.body.unmask();
		else
			this.PacketInfoPanel.body.mask();
	},
	/* конструктор */
	initComponent: function() {
		var win = this;

		this.AddPrescrInPacketByCheckGridsPanel = Ext6.create('common.EMK.PacketPrescrExt2.AddPrescrInPacketByCheckGridsPanelExt2', {
			viewModel: true,
			autoHeight: true,
			buttonAlign: 'center',
			defaults: {
				border: false
			},
			frame: false,
			parentPanel: win,
			cbFn: function(){
				//me.getController().toggleView();
			},
			setCount: function(count){
				win.PatientInfoPanel.setParams({count: count})
			}
		});

		this.InDevelopPanel = Ext6.create('common.EMK.SpecificationDetail.InDevelopPanel', {
			parentPanel: win,
			mode: 'PacketWindow'
		});

		this.PacketPrescrGrid = Ext6.create('Ext6.grid.Panel', {
			scrollable: true,
			margin: '20 0 0 0',
			cls: 'cureStandartsGrid select-packet-grid',
			viewModel: true,
			buttonAlign: 'center',
			frame: false,
			region: 'center',
			hideHeaders: true,
			border: false,
			default: {
				border: false
			},
			rowLines: false,
			columns: [
				{
					dataIndex: 'Packet_IsFavorite',
					xtype: 'actioncolumn',
					width: 51,
					sortable: false,
					menuDisabled: true,
					tooltip: 'В избранное',
					items: ['@favoriteTemplates']
				}, {
					flex: 1,
					padding: '2 0 7 10',
					dataIndex: 'PacketPrescr_Name',
					text: '',
					userCls: 'cell-without-right-border',
					tdCls: 'packet-grid-name'
				}
			],
			store: {
				fields: [{
					name: 'PacketPrescr_id',
					type: 'string'
				}, {
					name: 'PacketPrescr_Name',
					type: 'string'
				}, {
					name: 'PacketPrescr_Descr',
					type: 'string'
				}, {
					name: 'Diag_Codes',
					type: 'string'
				}, {
					name: 'Packet_IsFavorite',
					type: 'int'
				}, {
					name: 'PacketPrescrVision_Name',
					type: 'string'
				}, {
					name: 'PacketPrescr_updDT',
					type: 'string'
				}, {
					name: 'active',
					type: 'bool',
					defaultValue: false
				}],
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketPrescrList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null,
				listeners: {
					load: function( store, records, successful, operation, eOpts){
						var grid = win.PacketPrescrGrid;
						grid.getView().focus();
						win.showFAQPanel(records.length);
						if(records.length){
							if(win.selectPacketPrescr_id)
								win.selectPacketById(win.selectPacketPrescr_id);
							else
								grid.getSelectionModel().select(0);
						}


					}
				}
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record) {
						record.set('active', true);
					},
					deselect: function(model, record) {
						record.set('active', false);
					}
				}
			},
			actions: {
				favoriteTemplates: {
					userCls: 'button-without-frame',
					getClass: function(value) {
						return (value == 2)
							?'icon-star-active'
							:'icon-star';
					},
					getTip: function(value) {
						return (value == 2)
							?'Убрать из избранных'
							:'Добавить в избранное';
					},
					handler: function(panel, rowIndex, colIndex, item, e, record) {
						win.setFavorite(record);
					}
				}
			},
			listeners: {
				itemdblclick: function (cmp, record) {
					win.selectPacketPrescr_id = record.get('PacketPrescr_id');
					//win.setMode('addPrescrByPacket');
				},
				itemmouseenter: function(grid, record) {
					if (grid.selection != record) {
						record.set('active', true);
					}
				},
				itemmouseleave: function(grid, record) {
					if (grid.selection != record) {
						record.set('active', false);
					}
				},
				select: function( grid, record, index, eOpts ){
					win.selectPacketPrescr_id = record.get('PacketPrescr_id');
					win.loadPacketOrStandart();
					win.loadPacketInfoForm(win.selectPacketPrescr_id);
				}
	}
		});

		win.withDiag = new Ext6.create('Ext6.form.field.Checkbox', {
			boxLabel: 'С учетом диагноза',
			margin: 0,
			checked: true
		});
		win.onlyFavor = new Ext6.create('Ext6.form.field.Checkbox', {
			boxLabel: 'Только избранные',
			margin: 0,
			itemId: 'onlyFavor'
		});
		win.SexCombo = new Ext6.create('widget.commonSprCombo',{
			labelAlign: 'top',
			fieldLabel: 'Пол',
			name: 'Sex_id',
			comboSubject: 'Sex',
			displayCode: false
		});
		win.PersonAgeCombo = new Ext6.create('widget.commonSprCombo',{
				labelAlign: 'top',
				fieldLabel: 'Возраст',
				name: 'PersonAgeGroup_id',
				comboSubject: 'PersonAgeGroup',
				displayCode: false,
				hideEmptyRow: false
			});

		win.searchField = new Ext6.create('Ext6.form.field.Text', {
			margin: '7 10 0 9',
			xtype: 'textfield',
			width: 240,
			triggers: {
				search: {
					cls: 'x6-form-search-trigger',
					handler: function () {
						// ?
					}
				}
			},
			listeners: {
				'change': function (combo, newValue, oldValue) {
					win.PacketPrescrGrid.getStore().clearFilter();
					var filters = [
						new Ext6.util.Filter({
							filterFn: function(rec) {
								var arrFilterFields = [
										'PacketPrescr_Name',
										'PacketPrescr_Descr',
										'Diag_Codes'
									],
									BreakException = {},
									filter = false;
								// Как только найдем сходство по строке - сразу прекратим поиск по записи
								try {
									arrFilterFields.forEach(function (fname) {
										var val = rec.get(fname) || '';
										if (val && ((val.indexOf(newValue) + 1)
												|| (val.toLowerCase().indexOf(newValue.toLowerCase()) + 1))) {
											filter = true;
											// Если нашли совпадение по одному полю, зачем искать по остальным
											throw BreakException;
										}
									});
								} catch (e) {
									if (e !== BreakException) throw e;
								}
								return filter;
							}
						})
					];
					win.PacketPrescrGrid.getStore().filter(filters);
				}
			}
		});
		this.filters = Ext6.create('Ext6.panel.Panel', {
			region: 'north',
			border: false,
			bodyStyle: {
				backgroundColor: '#f5f5f5;'
			},
			items: [
				win.searchField,
				{
					xtype: 'fieldset',
					margin: '0 10 0 9',
					title: 'Фильтры',
					cls: 'fieldset-default',
					style: {
						borderLeft: 'none',
						borderRight: 'none'
					},
					collapsible: true,
					defaults: {
						padding: '0 0 0 20',
						listeners: {
							change: function (c, val) {
								if(!win.setFiltersAuto){
									delete win.selectPacketPrescr_id;
									win.loadGrid();
								}
							}
						}
					},
					items: [
						win.withDiag,
						win.onlyFavor,
						win.SexCombo,
						win.PersonAgeCombo
						]
				}
			]
		});
		this.PacketInfoPanel = Ext6.create('Ext6.form.Panel', {
			width: 260,
			region: 'east',
			split: true,
			collapseMode: 'mini',
			collapsible: true,
			header: false,
			title: 'Свойства пакета',
			bodyPadding: '0 17 0 12',
			cls: 'packet-select-right-panel',
			layout: 'auto',
			height: '100%',
			align: 'stretch',
			bodyStyle: {
				backgroundColor: '#f5f5f5;'
			},
			url: '/?c=PacketPrescr&m=loadEditPacketForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'PacketPrescr_id'},
						{name: 'PacketPrescr_Name'},
						{name: 'PacketPrescr_Descr'},
						{name: 'Sex_id'},
						{name: 'PersonAgeGroup_id'},
						{name: 'PacketPrescrVision_id'},
						{
							name: 'Diag_id',
							type: 'auto',
							convert: function (value_str) {
								var res = value_str.split(",");
								console.log(res);
								return res;
							}
						}
					]
				})
			}),
			tbar: {
				height: 40,
				items: [
					'->',
					{
						xtype: 'tool',
						cls: 'add-folder',
						userCls: 'sw-tool',
						tooltip: 'Новый пакет',
						width: 16,
						margin: '0 10 0 20',
						handler: function(){
							win.createEmptyPacket();
						}
					}, {
						xtype: 'tool',
						cls: 'packet-copy',
						userCls: 'sw-tool',
						tooltip: 'Скопировать пакет',
						width: 16,
						margin: '0 10',
						handler: function(){
							win.copyPacket();
						}
					}, {
						xtype: 'tool',
						cls: 'packet-share',
						userCls: 'sw-tool',
						tooltip: 'Поделиться пакетом',
						width: 16,
						margin: '0 10',
						handler: function(){
							inDevelopmentAlert();
						}
					}, {
						xtype: 'tool',
						cls: 'packet-delete',
						userCls: 'sw-tool',
						tooltip: 'Удалить пакет',
						width: 16,
						margin: '0 20 0 10',
						handler: function(){
							win.deletePacket();
						}
					}
				]
			},
			items: [
				{
					name: 'PacketPrescr_id',
					xtype: 'textfield',
					hidden: true
				}, {
					name: 'PacketPrescr_Name',
					hidden: true,
					xtype: 'textfield',
					listeners: {
						'change': function (field, newValue, oldValue) {
							win.PacketName.setValue(newValue);
							win.PacketName.saveValue = newValue;
							if (!win.loadData)
								win.savePacketInfo();
						}
					}
				}, {
					fieldLabel: 'Диагноз',
					name: 'Diag_id',
					width: 230,
					labelAlign: 'top',
					xtype: 'swDiagTagCombo',
					cls: 'diagnoz-tag-input-field',
					listConfig: {
						cls: 'choose-bound-list-menu update-scroller'
					}
				}, {
					fieldLabel: 'Видимость',
					value: 1,
					name: 'PacketPrescrVision_id',
					labelAlign: 'top',
					width: 230,
					comboSubject: 'PacketPrescrVision',
					displayCode: false,
					allowBlank: false,
					xtype: 'commonSprCombo'
				}, {
					fieldLabel: 'Возрастная группа',
					name: 'PersonAgeGroup_id',
					comboSubject: 'PersonAgeGroup',
					labelAlign: 'top',
					displayCode: false,
					xtype: 'commonSprCombo',
					hideEmptyRow: false
				}, {
					fieldLabel: 'Пол',
					name: 'Sex_id',
					labelAlign: 'top',
					comboSubject: 'Sex',
					displayCode: false,
					xtype: 'commonSprCombo'
				}, {
					fieldLabel: 'Краткое описание',
					name: 'PacketPrescr_Descr',
					width: 230,
					labelAlign: 'top',
					xtype: 'textareafield'
				}
			],
			defaults: {
				listeners: {
					change: function(field){
						var name = field.getName();
						if (win.loadData || name.inlist(['Diag_id','PacketPrescr_Descr'])) {
							return false;
						}
						win.savePacketInfo();
					},
					blur: function(field){
						var name = field.getName();
						if (win.loadData || !name.inlist(['Diag_id','PacketPrescr_Descr'])) {
							return false;
						}
						if(
							Ext6.isEmpty(field.saveValue) // Если форма еще не сохранялась
							|| (!Ext6.isEmpty(field.saveValue) && field.getValue().toString() != field.saveValue.toString()) // или после потери фокуса значение поменялось
						)
						{
							win.savePacketInfo();
							field.saveValue = field.getValue();
						}
					}
				}
			}
		});

		this.PacketName = new Ext6.create('widget.swEditableDisplayField',{
			onBlurText: function(val){
				if(!Ext6.isEmpty(val) && val != win.PacketName.saveValue){
					win.PacketInfoPanel.getForm().setValues({PacketPrescr_Name: val});
					win.PacketName.saveValue = val;
				}
			}
		});

		this.PacketStructure = Ext6.create('Ext6.panel.Panel', {
			flex: 1,
			scrollable: 'y',
			region: 'center',
			cls: 'packet-select-right-panel',
			layout: 'card',
			height: '100%',
			align: 'stretch',
			bodyPadding: '0 20 10 20',
			tbar: {
				height: 40,
				items: [
					'->',
					win.PacketName,
					'->'
				]
			},
			activeItem: 0,
			items: [
				win.AddPrescrInPacketByCheckGridsPanel,
				win.InDevelopPanel
			]
		});

		this.PatientInfoPanel = Ext6.create('Ext6.panel.Panel', {
			border: false,
			flex: 1,
			cls: 'patient-apply-panel',
			params: {
				fio: '',
				diag: '',
				count: 0
			},
			setParams: function(params){
				var data = Ext6.Object.merge(this.params,params);
				this.applyData(data);
			},
			clearParams: function(){
				this.applyData({fio: '', diag: '', count: 0});
			},
			tpl: new Ext6.Template([
				'Для пациента <span>{fio}</span> {diag} выбрано назначений: {count}'
			])
		});

		this.PatientApplyPanel = Ext6.create('Ext6.panel.Panel', {
			region: 'south',
			style: 'background-color: #2196f3',
			cls: 'packet-select-footer',
			padding:'7 0 7 6',
			height: 60,
			margin: 0,
			layout: {
				type: 'hbox',
				pack: 'end',
				align: 'stretch'
			},
			items: [
				this.PatientInfoPanel,
				{
					xtype: 'button',
					cls: 'button-secondary-blue',
					text: 'Отмена',
					handler: function () {
						win.hide();
					},
					margin: '0 0 20 0'
				}, {
					xtype: 'button',
					text: 'Применить',
					cls: 'button-primary-white',
					margin: '0 33 20 9',
					handler: function (btn) {
						btn.disable();
						win.AddPrescrInPacketByCheckGridsPanel.doSave('apply',function(){btn.enable()});
					}
				}]
		});

		this.CancelPanel = Ext6.create('Ext6.panel.Panel', {
			region: 'south',
			style: 'background-color: #2196f3',
			cls: 'packet-select-footer',
			padding:'7 0 7 6',
			height: 60,
			margin: 0,
			layout: {
				type: 'hbox',
				pack: 'end',
				align: 'stretch'
			},
			items: [
				{
					xtype: 'button',
					cls: 'button-secondary-blue',
					text: 'Отмена',
					handler: function () {
						win.hide();
					},
					margin: '0 0 20 0'
				}]
		});

		this.SelectPacketPanel = Ext6.create('Ext6.panel.Panel', {
			width: 260,
			layout: 'border',
			region: 'west',
			cls: 'packet-select-left-panel',
			border: false,
			split: true,
			collapseMode: 'mini',
			collapsible: true,
			header: false,
			title: {
				text: 'Список пакетов',
				rotation: 2,
				textAlign: 'right'
			},
			bodyStyle: {
				backgroundColor: '#f5f5f5'
			},
			items: [
				win.filters,
				win.PacketPrescrGrid
			]
		});

		win.centerMainPanel = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			defaults: {
				border: false
			},
			items: [
				win.PacketInfoPanel,
				win.PacketStructure,
				win.PatientApplyPanel,
				win.CancelPanel
			]
		});
		win.PacketPanel = new Ext6.Panel({
			border: false,
			defaults: {
				border: false
			},
			floatable: false,
			layout: 'border',
			items: [
				win.SelectPacketPanel,
				win.centerMainPanel
			]
		});

		this.cardPanel = new Ext6.Panel({
			region: 'center',
			border: false,
			defaults: {
				border: false
			},
			layout: 'card',
			activeItem: 0,
			items: [
				win.PacketPanel
				//win.CureStandartPanel
			]
		});

		this.tabs = new Ext6.tab.Panel({
			border: false,
			region: 'north',
			tabBar: {
				border: false,
				cls: 'white-tab-bar',
				defaults: {
					cls: 'simple-tab',
					padding: '10 0',
					margin: '0 10'
				}
			},
			defaults: {
				border: false
			},
			items: [
				{
					title: 'Мои пакеты назначений',
					itemId: 'my'
				}, {
					title: 'Общие пакеты назначений',
					itemId: 'shared'
				}, {
					title: 'Стандарты лечения',
					itemId: 'standart'
				}
			],
			listeners: {
				tabchange: function () {
					var tab = this.getActiveTab();
					delete win.selectPacketPrescr_id;
					if(tab.itemId !== 'standart')
						win.setMode(tab.itemId);
					else inDevelopmentAlert();
				}
			}
		});
		Ext6.apply(win, {
			defaults:{
				border: false
			},
			items: [
				win.tabs,
				win.cardPanel
			],
			listeners: {
				hide: function ( panel, eOpts ){
					win.callback();
				}
			}
		});

		this.callParent(arguments);
	}
});