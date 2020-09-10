/**
 * swPersonSearchWindow - Человек: Поиск
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.swPersonSearchWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swPersonSearchWindow',
	modal: true,
	noTaskBarButton: true,
	defaultAlign: 'tr',
	swMaximized: true,
	autoShow: false,
	maximized: false,
	width: 900,
	height: 600,
	resizable: false,
	maximizable: false,
	findWindow: false,
	closable: true,
	cls: 'arm-window-new person-search-window',
	title: 'Человек: Поиск',
	header: true,
	renderTo: main_center_panel.body.dom,
	layout: 'border',
	constrain: true,
	show: function() {
		this.callParent(arguments);
		this.personDoublesCache = sw.Promed.personDoublesCache;

		if (this.personDoublesCache.cacheEnabled) {
			this.showDoublesUnionBar();
		} else {
			this.cancelDoubles();
		}
		

		this.notHideOnSelect = false;
		if (arguments && arguments[0] && arguments[0].notHideOnSelect) {
			this.notHideOnSelect = arguments[0].notHideOnSelect;
		}

		if (arguments && arguments[0] && arguments[0].onSelect) {
			this.onSelect = arguments[0].onSelect;
			this.Grid.down('#action_ok').show();
		} else {
			this.onSelect = null;
			this.Grid.down('#action_ok').hide();
		}

		var me = this;
		var base_form = me.FilterPanel.getForm();
		me.doReset();
	},
	showDoublesUnionBar: function() {
		this.customBar.show();
		this.Grid.getSelectionModel().column.setWidth(45);
		this.Grid.getSelectionModel().column.show();
		this.Grid.queryById('action_doubles').addCls('x6-btn-pressed');
		this.Grid.queryById('action_ok').disable();
		this.Grid.queryById('action_edit').disable();
		this.Grid.setColumnHidden('IsMainRec', false);
	},
	onRecordSelect: function() {
		var me = this;
		me.Grid.down('#action_edit').disable();
		me.Grid.down('#action_revive').disable();
		me.Grid.down('#action_ok').disable();
		me.Grid.down('#action_doubles').disable();

		var cnt = this.Grid.getSelectionModel().getSelection().length;
		if (cnt > 0) {
			me.selectedLabel.setText('Выбран: ' + cnt);
		} else {
			me.selectedLabel.setText('');
		}

		var record = this.Grid.getSelectionModel().getSelectedRecord();
		if (record) {
			if (record.get('Person_id')) {
				me.Grid.down('#action_edit').enable();
				me.Grid.down('#action_ok').enable();
				me.Grid.down('#action_doubles').enable();

				if (record.get('Person_deadDT')) {
					me.Grid.down('#action_revive').enable();
				}
			}
		}
	},
	onLoadGrid: function() {
		var me = this;
		var rowIndex, i, row;
		var selectedRecords = [];
		me.onRecordSelect();

		if (me.personDoublesCache.cacheEnabled) {
			this.showDoublesUnionBar();
		} else {
			this.cancelDoubles();
		}

		var gridStore = this.Grid.getStore();
		var Person_ids = me.personDoublesCache.getPersonIds().reverse();
		for ( var index = 0; Person_ids.length > index; index++ ) {
			i = gridStore.findBy(function(rec) {
				return rec.get('Person_id') == Person_ids[index];
			});
			if (i == -1) {
				gridStore.insert(0, me.personDoublesCache.getRecord(Person_ids[index]));

				rowIndex = 0;
				selectedRecords.push(gridStore.getAt(rowIndex));
			} else {
				row = gridStore.getAt(i);
				if (me.personDoublesCache.getRecord(Person_ids[index]).get('IsMainRec')) {
					row.set('IsMainRec', true);
					row.commit();
				}
				gridStore.removeAt(i);
				gridStore.insert(0, row);
				selectedRecords.push(row);
			}
		}
		this.Grid.getSelectionModel().select(selectedRecords);
	},
	doSearch: function (mode) {
		var base_form = this.FilterPanel.getForm();
		var params = base_form.getValues();
		var is_kz = (getRegionNick() == 'kz');
		if (
			Ext6.isEmpty(params.Person_id)
			&& Ext6.isEmpty(params.PersonSurName_SurName)
			&& (Ext6.isEmpty(params.PersonFirName_FirName) || Ext6.isEmpty(params.PersonSecName_SecName) || Ext6.isEmpty(params.PersonBirthDay_BirthDay))
			&& (!is_kz || (is_kz && Ext6.isEmpty(params.Person_Inn)))
			&& (is_kz || (!is_kz && Ext6.isEmpty(params.Person_Snils)))
			&& Ext6.isEmpty(params.PersonCard_Code)
			&& Ext6.isEmpty(params.EvnPS_NumCard)
			&& Ext6.isEmpty(params.Polis_Num)
			&& Ext6.isEmpty(params.Polis_EdNum)
			&& (Ext6.isEmpty(params.EvnUdost_Ser) || Ext6.isEmpty(params.EvnUdost_Num != ''))
		) {
			Ext6.Msg.alert("Сообщение", "Не заполнены обязательные поля. Возможные варианты поиска:<br/>" +
				(isAdmin || isLpuAdmin() ? "Поиск по ИД пациента.<br/>" : "") +
				"Поиск по фамилии.<br/>" +
				"Поиск по совпадению имени, отчества и даты рождения.<br/>" +
				(is_kz ? "Поиск по точному совпадению ИИН.<br/>" : "") +
				(!is_kz ? "Поиск по точному совпадению СНИЛС.<br/>" : "") +
				"Поиск по точному совпадению номера амбулаторной карты.<br/>" +
				"Поиск по точному совпадению номера КВС.<br/>" +
				(!is_kz ? "Поиск по точному совпадению номера полиса.<br/>" : "") +
				"Поиск по точному совпадению номера единого номера полиса.<br/>" +
				"Поиск по точному совпадению серии и номера удостоверения льготника.<br/>"
				, function() {
					base_form.findField('PersonSurName_SurName').focus(true, 100);
				});

			return false;
		}

		if (params.showAll) {
			params.showAll = 2;
		} else {
			params.showAll = 1;
		}

		params.start = 0;
		params.limit = 100;
		params.Double_ids = Ext6.util.JSON.encode(this.personDoublesCache.getIdsOtherModels('search'));

		this.Grid.getStore().removeAll();
		this.countLabel.setText('');
		this.Grid.getStore().load({params: params});
	},
	doReset: function () {
		var base_form = this.FilterPanel.getForm();
		base_form.reset();
		this.Grid.getStore().removeAll();
		this.onRecordSelect();
		this.countLabel.setText('');
		base_form.findField('PersonSurName_SurName').focus(true, 100);
	},
	notHideOnSelect: false,
	onPersonSelect: function() {
		if (typeof this.onSelect == 'function') {
			var record = this.Grid.getSelectionModel().getSelectedRecord();
			if (record) {
				var data_to_return = {};
				Ext6.applyIf(data_to_return, record.data);
				Ext6.applyIf(data_to_return, {
					Person_Birthday: record.get('PersonBirthDay_BirthDay'),
					Person_Firname: record.get('PersonFirName_FirName'),
					Person_Secname: record.get('PersonSecName_SecName'),
					Person_Surname: record.get('PersonSurName_SurName')
				});

				this.onSelect(data_to_return);

				if (!this.notHideOnSelect) {
					this.hide();
				}
			}
		} else {
			this.openPersonEditWindow('edit');
		}
	},
	revivePerson: function () {
		var me = this;
		var record = me.Grid.getSelectionModel().getSelectedRecord();
		if (!record || !record.get('Person_id')) {
			return false;
		}

		me.mask('Удаление признака смерти...');
		Ext6.Ajax.request({
			url: '/?c=Person&m=revivePerson',
			params: {
				Person_id: record.get('Person_id')
			},
			callback: function (options, success, response) {
				me.unmask();
				if (success) {
					var result = Ext6.JSON.decode(response.responseText);

					if (result.success) {
						record.set('Person_deadDT', null);
						record.commit();
					}
				}
			}
		});
	},
	openPersonEditWindow: function(action) {
		var me = this;
		var base_form = this.FilterPanel.getForm();
		var grid = me.Grid;
		var params = {
			action: action
		};

		if (action == 'add') {
			params.callback = function(callback_data) {
				if (callback_data) {
					me.doReset();

					if (callback_data.PersonData.Person_FirName) {
						base_form.findField('PersonFirName_FirName').setValue(callback_data.PersonData.Person_FirName);
					}
					if (callback_data.PersonData.Person_SurName) {
						base_form.findField('PersonSurName_SurName').setValue(callback_data.PersonData.Person_SurName);
					}
					if (callback_data.PersonData.Person_SecName) {
						base_form.findField('PersonSecName_SecName').setValue(callback_data.PersonData.Person_SecName);
					}
					if (callback_data.PersonData.Person_BirthDay) {
						base_form.findField('PersonBirthDay_BirthDay').setValue(callback_data.PersonData.Person_BirthDay);
					}
					if (callback_data.PersonData.Person_Snils) {
						base_form.findField('Person_Snils').setValue(callback_data.PersonData.Person_Snils);
					}
					if (callback_data.PersonData.Person_Inn) {
						base_form.findField('Person_Inn').setValue(callback_data.PersonData.Person_Inn);
					}

					me.doSearch();
				}
			}
		} else {
			var record = grid.getSelectionModel().getSelectedRecord();
			if (!record || !record.get('Person_id')) {
				return false;
			}

			params.Person_id = record.get('Person_id');
			params.Server_id = record.get('Server_id');
			params.callback = function (callback_data) {
				if (callback_data) {
					grid.getStore().each(function (record) {
						if (record.data.Person_id == callback_data.Person_id) {
							record.set('Server_id', callback_data.Server_id);
							record.set('PersonEvn_id', callback_data.PersonEvn_id);
							record.set('PersonSurName_SurName', callback_data.PersonData.Person_SurName);
							record.set('PersonFirName_FirName', callback_data.PersonData.Person_FirName);
							record.set('PersonSecName_SecName', callback_data.PersonData.Person_SecName);
							record.set('PersonBirthDay_BirthDay', callback_data.PersonData.Person_BirthDay);
							record.commit();
						}
					});
				}
			};
		}

		getWnd('swPersonEditWindow').show(params);
	},
	setDoubles: function() {
		this.customBar.show();
		this.Grid.getSelectionModel().column.setWidth(45);
		this.Grid.getSelectionModel().column.show();
		this.Grid.queryById('action_doubles').addCls('x6-btn-pressed');
		this.Grid.queryById('action_ok').disable();
		this.Grid.queryById('action_edit').disable();
		this.Grid.setColumnHidden('IsMainRec', false);

		this.personDoublesCache.setCacheEnable();
		var record = this.Grid.getSelectionModel().getSelectedRecord();
		if (record && record.get('Person_id')) {
			this.setIsMainRec(record);
			this.personDoublesCache.addRecord(record, 'search');
		}
	},
	setIsMainRec: function(record) {
		this.Grid.getStore().each(function(rec) {
			if (rec != record && rec.get('IsMainRec') == true) {
				rec.set('IsMainRec', false);
				rec.commit();
			}
		});

		record.set('IsMainRec', true);
		record.commit();

		this.personDoublesCache.setMainRecord(record.get('Person_id'));
	},
	cancelDoubles: function() {
		this.customBar.hide();
		this.Grid.getSelectionModel().column.hide();
		this.Grid.queryById('action_doubles').removeCls('x6-btn-pressed');
		this.Grid.queryById('action_ok').enable();
		this.Grid.queryById('action_edit').enable();
		this.Grid.setColumnHidden('IsMainRec', true);
	},
	doPersonUnion: function () {
		var me = this;
		var mainGrid = this.Grid;
		var hasMainRec = false;
		var records = [];
		if (mainGrid.getSelectionModel().hasSelection()) {
			mainGrid.getSelectionModel().getSelection().forEach(function(record) {
				if (record.get('Person_id')) {
					if (record.get('IsMainRec')) {
						hasMainRec = true;
					}

					records.push({
						Person_id: record.get('Person_id'),
						IsMainRec: record.get('IsMainRec') ? 1 : 0
					});
				}
			});
		}
		if (records.length < 2) {
			Ext6.Msg.alert(langs('Внимание'),langs('Для объединения должны быть хотя бы 2 записи!'));
			return false;
		}
		if (!hasMainRec){
			Ext6.Msg.alert(langs('Внимание'),langs('Должна быть выбрана главная запись для объединения!'));
			return false;
		}

		me.mask('Пожалуйста, подождите, идет сохранение данных...');
		Ext6.Ajax.request({
			url: C_PERSON_UNION,
			success: function(result){
				me.unmask();
				if ( result.responseText.length > 0 ) {
					var resp_obj = Ext6.JSON.decode(result.responseText);
					if (resp_obj.success == true) {
						me.cancelDoubles();
						// удаляем записи из кэша
						me.personDoublesCache.resetCache();
						mainGrid.getStore().reload();
						if (resp_obj.Info_Msg) {
							sw4.showInfoMsg({
								type: 'info',
								text: langs('Выбранные записи успешно отправлены на модерацию') + '<br />' + resp_obj.Info_Msg
							});
							mainGrid.queryById('action_doubles').removeCls('x6-btn-pressed');
						} else if (resp_obj.Success_Msg) {
							sw4.showInfoMsg({
								type: 'info',
								text: langs('Выбранные записи успешно отправлены на модерацию')
							});
							mainGrid.queryById('action_doubles').removeCls('x6-btn-pressed');
						}
					}
				}
			},
			params: {
				'Records': Ext6.JSON.encode(records)
			},
			failure: function(result){
				me.unmask();
			},
			method: 'POST',
			timeout: 120000
		});
	},
	addQTip: function(val, metaData, record) {
		var PQ_tooltip = false;
		if(metaData && metaData.column && metaData.column.dataIndex && metaData.column.dataIndex == 'PersonSurName_SurName'){
			if(record.get('PersonQuarantine_IsOn')){
				if(record.get('PersonQuarantine_begDT')){
					PQ_tooltip = "<b>Карантин с " +
						Ext6.util.Format.date(record.get('PersonQuarantine_begDT'), "d.m.Y").replace(new RegExp('"', 'g'), '&quot;') +
						'</b>';
				} else {
					PQ_tooltip += '<b>Пациент на карантине</b>';
				}
				val = "<span style='float: left;' class='quarantined-patient' data-qtip='"+PQ_tooltip+"'></span>" + val;
			}
		}
		
		var tooltip = '';
		tooltip += '<b>ИД:</b> ' + record.get('Person_id');
		if(PQ_tooltip){
			tooltip += '<br>' + PQ_tooltip;
		}
		tooltip += '<br><b>№ полиса:</b> ' + record.get('Polis_Num').replace(new RegExp('"', 'g'), '&quot;');
		tooltip += '<br><b>Прикрепление:</b> ' + record.get('Lpu_Nick').replace(new RegExp('"', 'g'), '&quot;');
		tooltip += '<br><b>№ ам. карты:</b> ' + record.get('PersonCard_Code').replace(new RegExp('"', 'g'), '&quot;');

		metaData.tdAttr = 'data-qtip="' + tooltip + '"';

		if (val && typeof val == 'object') {
			// для столбцов с датой
			return val.format('d.m.Y');
		} else if (val && typeof val == 'string') {
			return val[0].toUpperCase()+val.slice(1).toLowerCase();
		} else {
			return val;
		}
	},
	initComponent: function() {
		var me = this;

		me.selectedLabel = Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			cls: 'person-double-text-select',
			text: ''
		});

		me.customBar = Ext6.create('Ext6.toolbar.Toolbar', {
			padding: '4 20 3 20',
			xtype: 'toolbar',
			dock: 'bottom',
			style: {
				'backgroundColor': '#f5f5f5'
			},
			ui: 'footer',
			items: [{
				handler: function () {
					me.doPersonUnion();
				},
				cls: 'button-primary',
				text: 'Объединить'
			}, {
				handler: function () {
					me.cancelDoubles();
					// удаляем записи из кэша
					me.personDoublesCache.resetCache();
				},
				cls: 'button-secondary',
				text: 'Отмена',
				margin: '0 0 0 3'
			}, {
				xtype: 'checkbox',
				boxLabel: 'Перенести случаи',
				fieldLabel: 'Перенести случаи',
				margin: '0 0 0 20',
				hideLabel: true,
				name: 'transferEvn'
			}, '->', me.selectedLabel ]
		});

		me.Grid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common person-search-grid',
			xtype: 'grid',
			border: false,
			region: 'center',
			selModel: {
				selType: 'checkboxmodel',
				width: 65,
				listeners: {
					select: function(model, record, index) {
						me.onRecordSelect();
						me.personDoublesCache.addRecord(record, 'search');
					},
					deselect: function(model, record, index) {
						me.onRecordSelect();
						me.personDoublesCache.removeRecord(record);
					}
				}
			},
			viewConfig: {
				getRowClass: function(record, rowIndex, rowParams, store) {
					var cls = '';
					if (record.get('Person_deadDT')) {
						cls = cls + 'x-grid-rowgray ';
					}
					if (record.get('PersonQuarantine_IsOn')) {
						cls = cls + 'x-grid-rowbackred ';
					}
					return cls;
				}
			},
			dockedItems: [{
				padding: "0 10px",
				xtype: 'toolbar',
				dock: 'top',
				height: 32,
				cls: 'grid-toolbar',
				items: [{
					height: 26,
					margin: '-3 0 0 0',
					xtype: 'button',
					text: 'Выбрать',
					itemId: 'action_ok',
					iconCls: 'action_ok',
					handler: function(){
						me.onPersonSelect();
					}
				}, {
					xtype: 'button',
					height: 26,
					margin: '-3 0 0 0',
					text: 'Редактировать',
					itemId: 'action_edit',
					iconCls: 'menu_dispedit',
					handler: function(){
						me.openPersonEditWindow('edit');
					}
				}, {
					xtype: 'button',
					height: 26,
					margin: '-3 0 0 0',
					text: 'Удалить признак смерти',
					itemId: 'action_revive',
					iconCls: 'action_revive',
					handler: function(){
						me.revivePerson();
					}
				}, {
					xtype: 'button',
					height: 26,
					margin: '-3 0 0 0',
					text: 'Это двойник',
					itemId: 'action_doubles',
					iconCls: 'action_doubles',
					handler: function(){
						me.setDoubles();
					}
				}, '->', me.countLabel = Ext6.create('Ext6.form.Label', {
					xtype: 'label',
					userCls: 'person-text-search-result',
					text: ''
				})]
			}, me.customBar],
			listeners: {
				itemdblclick: function() {
					me.onPersonSelect();
				}
			},
			store: {
				fields: [
					{ name: 'Person_id', type: 'int' },
					{ name: 'Server_id', type: 'int' },
					{ name: 'PersonEvn_id', type: 'int' },
					{ name: 'PersonSurName_SurName', type: 'string' },
					{ name: 'PersonFirName_FirName', type: 'string' },
					{ name: 'PersonSecName_SecName', type: 'string' },
					{ name: 'PersonBirthDay_BirthDay', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'Person_deadDT', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'Polis_Num', type: 'string' },
					{ name: 'Lpu_Nick', type: 'string' },
					{ name: 'PersonCard_Code', type: 'string' },
					{ name: 'Person_IsDMS'},
					{ name: 'Person_IsBDZ'},
					{ name: 'Person_IsFedLgot'},
					{ name: 'Person_IsRegLgot'},
					{ name: 'Person_IsRefuse'},
					{ name: 'Person_Is7Noz' },
					{ name: 'PersonQuarantine_IsOn', type: 'boolean'},
					{name: 'PersonQuarantine_begDT', type: 'date', dateFormat: 'd.m.Y'}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Person&m=getPersonSearchGrid',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'Person_Fio'
				],
				listeners: {
					load: function(grid, records) {
						me.onLoadGrid();

						if (records && records.length > 0) {
							me.countLabel.setText(ru_word_case('Найден', 'Найдено', 'Найдено', records.length) + ' ' + records.length + ' ' + ru_word_case('человек', 'человека', 'человек', records.length));
						} else {
							me.countLabel.setText('Не найдено ни одного человека');
						}
					}
				}
			},
			columns: [
				{text: 'Главная запись', width: 100, hidden: true, xtype: 'checkcolumn', listeners: {
					'checkchange': function (column, rowIndex, checked, rec, e, eOpts) {
						if (checked) {
							me.setIsMainRec(rec);
						} else {
							rec.commit();
						}
						// все остальные чекбосы надо снять
					}
				}, dataIndex: 'IsMainRec'},
				{text: 'Фамилия', width: 130, dataIndex: 'PersonSurName_SurName', renderer: me.addQTip},
				{text: 'Имя', width: 119, dataIndex: 'PersonFirName_FirName', renderer: me.addQTip},
				{text: 'Отчество', width: 119, dataIndex: 'PersonSecName_SecName', renderer: me.addQTip},
				{text: 'Дата рождения', width: 130, dataIndex: 'PersonBirthDay_BirthDay', renderer: me.addQTip},
				{text: 'Дата смерти', width: 119, dataIndex: 'Person_deadDT', renderer: me.addQTip},
				{text: 'МО прикрепления', width: 120, dataIndex: 'Lpu_Nick', renderer: me.addQTip},
				{text: 'РЗ', width: 49, dataIndex: 'Person_Bdz', renderer: function(val, metaData, record) {
					var s = '';
					if (record.get('Person_IsBDZ') && record.get('Person_IsBDZ') == 'true') {
						s += "<span class='lgot_rz' data-qtip='Регистр застрахованных'>РЗ</span>";
					}
					return s;
				}},
				{text: 'Льготы', flex: 1, width: 80, dataIndex: 'Person_Lgots', renderer: function(val, metaData, record) {
					var s = '';
					var addClass = "";
					var isRefuse = false;
					if (record.get('Person_IsRefuse') && record.get('Person_IsRefuse') == 'true') {
						addClass += " lgot_refuse";
						isRefuse = true;
					}
					if (record.get('Person_IsFedLgot') && record.get('Person_IsFedLgot') == 'true') {
						s += "<span class='lgot_fl" + addClass + "' data-qtip='" + (isRefuse ? "Пациент отказался от федеральной льготы" : "Федеральная льгота") + "'>ФЛ</span>";
					}
					if (record.get('Person_IsRegLgot') && record.get('Person_IsRegLgot') == 'true') {
						s += "<span class='lgot_rl" + addClass + "' data-qtip='" + (isRefuse ? "Пациент отказался от региональной льготы" : "Региональная льгота") + "'>РЛ</span>";
					}
					return s;
				}}
			]
		});

		me.FilterPanel = Ext6.create('Ext6.form.FormPanel', {
			autoScroll: true,
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 20px 30px 0px 0px;',
			cls: 'person-search-input-panel',
			region: 'north',
			items: [{
				border: false,
				layout: 'column',
				padding: '0 0 0 28',
				items: [{
					border: false,
					layout: 'anchor',
					defaults: {
						anchor: '100%',
						labelWidth: 65,
						width: 283,
						listeners: {
							specialkey: function (field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Фамилия',
						plugins: [ new Ext6.ux.Translit(true, false) ],
						name: 'PersonSurName_SurName'
					}, {
						xtype: 'textfield',
						fieldLabel: 'Имя',
						plugins: [ new Ext6.ux.Translit(true, false) ],
						name: 'PersonFirName_FirName'
					}, {
						xtype: 'textfield',
						fieldLabel: 'Отчество',
						plugins: [ new Ext6.ux.Translit(true, false) ],
						name: 'PersonSecName_SecName'
					}, {
						xtype: 'checkbox',
						labelWidth: 137,
						margin: '0 0 0 0',
						fieldLabel: 'Показывать умерших',
						name: 'showAll'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 27',
					defaults: {
						anchor: '100%',
						labelWidth: 85,
						width: 250,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'swDateField',
						fieldLabel: 'Дата рожд.',
						name: 'PersonBirthDay_BirthDay'
					}, {
						xtype: 'textfield',
						fieldLabel: 'ИД пациента',
						name: 'Person_id'
					}, {
						xtype: 'textfield',
						fieldLabel: 'СНИЛС',
						name: 'Person_Snils',
						hidden: getRegionNick() == 'kz'
					}, {
						xtype: 'textfield',
						fieldLabel: (getRegionNick() == 'kz' ? 'ИИН' : 'ИНН'),
						name: 'Person_Inn',
						hidden: getRegionNick() != 'kz'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%',
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Серия полиса',
						width: 250,
						labelWidth: 95,
						name: 'Polis_Ser',
						hidden: getRegionNick() == 'kz'
					}, {
						xtype: 'textfield',
						width: 250,
						labelWidth: 95,
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num',
						hidden: getRegionNick() == 'kz'
					}, {
						xtype: 'textfield',
						width: 250,
						labelWidth: 95,
						fieldLabel: 'Единый номер',
						name: 'Polis_EdNum',
						hidden: getRegionNick() == 'kz'
					}]
				}]
			},  {
				xtype: 'fieldset',
				collapsible: true,
				userCls: 'dop-parameters-person-search',
				collapsed: true,
				border: false,
				title: 'Дополнительные параметры',
				margin: '5 0 0 0',
				items: [{
					border: false,
					layout: 'column',
					padding: '6 0 0 28',
					items: [{
						border: false,
						width: 200,
						layout: 'anchor',
						defaults: {
							anchor: '100%',
							listeners: {
								specialkey: function(field, e, eOpts) {
									if (e.getKey() == e.ENTER) {
										me.doSearch();
									}
								}
							}
						},
						items: [{
							xtype: 'numberfield',
							minValue: 0,
							allowDecimals: false,
							fieldLabel: 'Возраст (лет) с',
							name: 'PersonAge_AgeFrom'
						}, {
							xtype: 'numberfield',
							minValue: 1800,
							allowDecimals: false,
							fieldLabel: 'Год рожд. с',
							margin: '0 0 20 0',
							name: 'PersonBirthYearFrom'
						}]
					}, {
						border: false,
						width: 120,
						layout: 'anchor',
						margin: "0 0 0 10px",
						defaults: {
							labelWidth: 20,
							anchor: '100%',
							listeners: {
								specialkey: function(field, e, eOpts) {
									if (e.getKey() == e.ENTER) {
										me.doSearch();
									}
								}
							}
						},
						items: [{
							xtype: 'numberfield',
							minValue: 0,
							allowDecimals: false,
							fieldLabel: 'по',
							name: 'PersonAge_AgeTo'
						}, {
							xtype: 'numberfield',
							minValue: 1800,
							allowDecimals: false,
							fieldLabel: 'по',
							name: 'PersonBirthYearTo'
						}]
					}, {
						border: false,
						width: 220,
						layout: 'anchor',
						margin: "0 0 0 10px",
						defaults: {
							labelWidth: 120,
							anchor: '100%',
							listeners: {
								specialkey: function(field, e, eOpts) {
									if (e.getKey() == e.ENTER) {
										me.doSearch();
									}
								}
							}
						},
						items: [{
							xtype: 'textfield',
							fieldLabel: 'Номер ам. карты',
							name: 'PersonCard_Code'
						}, {
							xtype: 'textfield',
							fieldLabel: 'Номер КВС',
							name: 'EvnPS_NumCard'
						}]
					}, {
						border: false,
						width: 250,
						layout: 'anchor',
						margin: "0 0 0 10px",
						defaults: {
							labelWidth: 150,
							anchor: '100%',
							listeners: {
								specialkey: function(field, e, eOpts) {
									if (e.getKey() == e.ENTER) {
										me.doSearch();
									}
								}
							}
						},
						items: [{
							xtype: 'textfield',
							fieldLabel: 'Серия удостоверения',
							name: 'EvnUdost_Ser'
						}, {
							xtype: 'textfield',
							fieldLabel: 'Номер удостоверения',
							name: 'EvnUdost_Num'
						}]
					}]
				}]
			}, {
				border: false,
				cls: 'panel-80',
				layout: 'column',
				margin: '0 0 25 28',
				items: [{
					cls: 'button-primary',
					iconCls: 'person-search-btn-icon',
					text: 'Найти',
					iconCls: 'action_find_white',
					xtype: 'button',
					handler: function() {
						me.doSearch();
					}
				}, {
					cls: 'button-secondary',
					text: 'Очистить',
					iconCls: 'action_clear',
					xtype: 'button',
					iconCls: 'person-clear-btn-icon',
					cls: 'button-secondary',
					style: 'margin-left: 10px;',
					handler: function() {
						me.doReset();
					}
				}, {
					text: 'Считать с карты',
					xtype: 'button',
					iconCls: 'person-read-card-btn-icon',
					cls: 'button-secondary',
					style: 'margin-left: 10px;',
					handler: function() {
						me.readFromCard();
					}
				}, {
					cls: 'button-secondary',
					text: 'Добавить нового',
					iconCls: 'menu_dispadd',
					xtype: 'button',
					style: 'margin-left: 10px;',
					handler: function() {
						me.openPersonEditWindow('add');
					}
				}]
			}]
		});

		Ext6.apply(me, {
			items: [
				me.FilterPanel,
				me.Grid
			],
			buttonAlign: 'left'
		});

		this.callParent(arguments);
	}
});