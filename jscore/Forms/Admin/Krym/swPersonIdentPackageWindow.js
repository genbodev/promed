/**
 * swPersonIdentPackageWindow - окно для пакетной идентификации людей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.02.2018
 */
/*NO PARSE JSON*/

sw.Promed.ComboMenuButton = Ext.extend(Ext.Button, {
	label: '',
	labelSeparator: ':',
	valueField: '',
	displayField: '',
	value: null,
	store: null,
	onChange: Ext.emptyFn,

	getStore: function() {
		return this.store;
	},

	setValue: function(value) {
		var me = this;

		me.value = value;

		var index = me.getStore().find(me.valueField, value);
		var record = me.getStore().getAt(index);

		var text = '';
		if (!Ext.isEmpty(me.label)) {
			text = me.label;

			if (!Ext.isEmpty(me.labelSeparator)) {
				text += me.labelSeparator+' ';
			}
			if (record && !Ext.isEmpty(record.get(me.displayField))) {
				text += '<b>'+record.get(me.displayField)+'</b>';
			}
		}

		me.setText(text);

		me.onChange(me, value);
	},

	getValue: function() {
		return this.value;
	},

	refreshList: function() {
		var me = this;

		me.menu.removeAll();

		var handler = function() {
			var item = this;
			me.setValue(item.value);
		};

		if (me.store && me.store.getCount() > 0) {
			me.store.each(function(record) {
				me.menu.add({
					value: record.get(me.valueField),
					text: record.get(me.displayField),
					handler: handler
				});
			});
		}
	},

	initComponent: function() {
		var me = this;

		me.menu = new Ext.menu.Menu;

		me.refreshList();

		me.defaultValue = me.value;
		me.setValue(me.value);

		sw.Promed.ComboMenuButton.superclass.initComponent.apply(me, arguments);
	}
});

sw.Promed.CustomDateRangeField = Ext.extend(Ext.form.DateRangeField, {
	plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
	oldValue: '',
	onChange: Ext.emptyFn,

	setRange: function(date1, date2) {
		this.setValue(date1.format('d.m.Y')+' - '+date2.format('d.m.Y'));
	},

	initComponent: function() {
		var me = this;

		sw.Promed.CustomDateRangeField.superclass.initComponent.apply(me, arguments);

		me.addListener('select', function(field, value) {
			field.onChange();
			field.oldValue = value;
		});

		me.addListener('keydown', function(field, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				field.beforeBlur();
				field.onChange();
				field.oldValue = field.getRawValue();
			}
		});

		me.addListener('blur', function(field) {
			if (field.oldValue != field.getRawValue()) {
				field.onChange();
				field.oldValue = field.getRawValue();
			}
		});
	}
});

sw.Promed.DateRangeMenuComp = function(conf) {
	var me = this;

	var _onChange = Ext.emptyFn;
	var _mode = 'range';

	if (typeof conf == 'object') {
		if (typeof conf.onChange) {
			_onChange = conf.onChange;
		}
	}

	me.setDateRange = function(date1, date2) {
		var date = null;
		var modify = 0;
		if (typeof date1 == 'string') {
			if (date1 == 'prev') modify = -1;
			if (date1 == 'next') modify = 1;
			date1 = date2 = null;
		}

		if (date1 instanceof Date) date = new Date(date1);
		if (Ext.isEmpty(date)) date = me.DateRangeField.getValue1();
		if (Ext.isEmpty(date)) date = new Date();

		switch(_mode) {
			case 'day':
				date.setDate(date.getDate() + modify);
				date1 = new Date(date);
				date2 = new Date(date);
				break;
			case 'week':
				date.setDate(date.getDate() + modify*7);
				date1 = new Date(date.getFirstDateOfWeek());
				date2 = new Date(date.setDate(date1.getDate() + 6));
				break;
			case 'month':
				date.setMonth(date.getMonth() + modify);
				date1 = new Date(date.getFirstDateOfMonth());
				date2 = new Date(date.getLastDateOfMonth());
				break;
			case 'range':
				if (Ext.isEmpty(date1)) {
					date1 = new Date(!Ext.isEmpty(me.DateRangeField.getValue1())?me.DateRangeField.getValue1():date);
				}
				if (Ext.isEmpty(date2)) {
					date2 = new Date(!Ext.isEmpty(me.DateRangeField.getValue2())?me.DateRangeField.getValue2():date);
				}
				date1.setDate(date1.getDate() + modify);
				date2.setDate(date2.getDate() + modify);
				break;
			default:
				return;
		}

		me.DateRangeField.setRange(date1, date2);
		_onChange();
	};

	me.buttonByMode = function(mode) {
		if (!mode.inlist(['day','week','month'])) return null;
		var buttonName = mode[0].toUpperCase()+mode.slice(1)+'Button';
		return me[buttonName];
	};

	me.selectMode = function(mode) {
		if (!mode.inlist(['day','week','month','range'])) {
			return;
		}
		var prevBtn = me.buttonByMode(_mode);
		var btn = me.buttonByMode(mode);

		if (prevBtn) {
			prevBtn.toggle();
		}
		if (btn && mode != 'range') {
			btn.toggle();
		}
		_mode = mode;
		me.setDateRange();
	};

	me.PrevButton = new Ext.Button({
		iconCls: 'arrow-previous16',
		handler: function(){
			me.setDateRange('prev');
		}
	});

	me.NextButton = new Ext.Button({
		iconCls: 'arrow-next16',
		handler: function(){
			me.setDateRange('next');
		}
	});

	me.DateRangeField = new sw.Promed.CustomDateRangeField({
		id: 'PIPW_Range',
		fieldLabel: langs('Период'),
		onChange: function() {
			me.selectMode('range');
		},
		width: 150
	});

	me.DayButton = new Ext.Button({
		text: 'День',
		handler: function(){me.selectMode('day')}
	});
	me.WeekButton = new Ext.Button({
		text: 'Неделя',
		handler: function(){me.selectMode('week')}
	});
	me.MonthButton = new Ext.Button({
		text: 'Месяц',
		handler: function(){me.selectMode('month')}
	});

	me.tbarItems = [
		me.PrevButton,
		me.DateRangeField,
		me.NextButton,
		' ',
		me.DayButton,
		me.WeekButton,
		me.MonthButton
	];
};

sw.Promed.swPersonIdentPackageWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonIdentPackageWindow',
	title: 'Пакетная идентификация ТФОМС',
	maximizable: true,
	maximized: true,
	layout: 'border',

	downloadRequestIdentFile: function() {
		var package_grid = this.PackageGridPanel.getGrid();
		var record = package_grid.getSelectionModel().getSelected();

		if (record && !Ext.isEmpty(record.get('PersonIdentPackage_Name'))) {
			window.open(record.get('PersonIdentPackage_Name'), '_blank');
		}
	},

	uploadResponseIdentFile: function() {
		var wnd = this;
		getWnd('swPersonIdentPackageResponseImportWindow').show({
			ARMType: wnd.ARMType,
			callback: function(){wnd.doFilterPackage()}
		});
	},

	deletePersonIdentPackage: function() {
		var package_grid = this.PackageGridPanel.getGrid();
		var record = package_grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('PersonIdentPackage_id'))) {
			return;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет удаление пакета..."});
					loadMask.show();

					Ext.Ajax.request({
						url: '/?c=PersonIdentPackage&m=deletePersonIdentPackage',
						params: {PersonIdentPackage_id: record.get('PersonIdentPackage_id')},
						success: function(response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.success) {
								this.doFilterPackage();
							}
						}.createDelegate(this),
						failure: function(response) {
							loadMask.hide();
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Вы хотите удалить запись?'),
			title: langs('Подтверждение')
		});
	},

	openableEvnClassList: ['EvnPL','EvnSection'],

	openEvnWindow: function(PersonIdentPackagePos_id) {
		var package_pos_grid = this.PackagePosGridPanel.getGrid();
		var record = package_pos_grid.getStore().getById(PersonIdentPackagePos_id);
		if (!record) return;

		var Evn_id = record.get('Evn_id');
		var Evn_pid = record.get('Evn_pid');
		var Evn_rid = record.get('Evn_rid');
		var EvnClass_SysNick = record.get('EvnClass_SysNick');
		var Person_id = record.get('Person_id');

		if (!Evn_id || !EvnClass_SysNick.inlist(this.openableEvnClassList)) {
			return;
		}

		switch(EvnClass_SysNick) {
			case 'EvnPL':
				getWnd('swEvnPLEditWindow').show({
					action: 'view',
					EvnPL_id: Evn_id
				});
				break;
			case 'EvnSection':
				getWnd('swEvnSectionEditWindow').show({
					action: 'view',
					Person_id: Person_id,
					formParams: {
						EvnSection_id: Evn_id,
						EvnSection_pid: Evn_pid,
						Person_id: Person_id
					}
				});
				break;
		}
	},

	doFilterPackage: function() {
		if (!this.PackageGridPanel) return;

		var package_grid = this.PackageGridPanel.getGrid();
		var package_pos_grid = this.PackagePosGridPanel.getGrid();

		package_pos_grid.getStore().removeAll();
		this.DownloadButton.disable();
		this.DeleteButton.disable();

		if (Ext.isEmpty(this.DateRangeMenu.DateRangeField.value)) {
			return;
		}

		var params = {
			PersonIdentPackage_DateRange: this.DateRangeMenu.DateRangeField.value,
			PersonIdentPackage_IsResponseRetrieved: null,
			start: 0,
			limit: 100
		};

		switch(this.ResponseStatusField.value) {
			case 2: params.PersonIdentPackage_IsResponseRetrieved = 2;break;
			case 3: params.PersonIdentPackage_IsResponseRetrieved = 1;break;
		}

		package_grid.getStore().load({
			params: params
		});
	},

	doFilterPackagePos: function(params) {
		var package_pos_grid = this.PackagePosGridPanel.getGrid();

		var map = {
			Person: function(data) {
				return [
					data.Person_SurName, data.Person_FirName, data.Person_SecName, data.Sex_Name.slice(0,3),
					Ext.util.Format.date(data.Person_BirthDay, 'd.m.Y')
				].join(' ');
			},
			IdentRange: function(data) {
				return [
					Ext.util.Format.date(data.PersonIdentPackagePos_identDT, 'd.m.Y'),
					Ext.util.Format.date(data.PersonIdentPackagePos_identDT2, 'd.m.Y')
				].join(' - ');
			},
			Evn: function(data) {
				return [data.EvnClass_Nick, data.Evn_id].join(' ');
			}
		};

		package_pos_grid.getStore().filterBy(function(record) {
			return Object.keys(params).every(function(key) {
				var value1 = String(params[key]).toLowerCase();
				if (Ext.isEmpty(value1)) return true;

				var value2 = String(map[key]?map[key](record.data):record.get(key)).toLowerCase();
				return value2.indexOf(value1) >= 0;
			});
		});
	},

	show: function() {
		sw.Promed.swPersonIdentPackageWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;

		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		this.DateRangeMenu.selectMode('week');

		this.doFilterPackage();

		this.FilterRow.syncFields();
	},

	initComponent: function() {
		var wnd = this;

		this.DateRangeMenu = new sw.Promed.DateRangeMenuComp({
			onChange: function() {
				this.doFilterPackage();
			}.createDelegate(this)
		});

		this.ResponseStatusField = new sw.Promed.ComboMenuButton({
			id: 'PIPW_Status',
			label: 'Показать',
			valueField: 'ResponseStatus_id',
			displayField: 'ResponseStatus_Name',
			value: 1,
			store: new Ext.data.SimpleStore({
				autoLoad: false,
				fields: [
					{name: 'ResponseStatus_id', type: 'int'},
					{name: 'ResponseStatus_sort', type: 'int'},
					{name: 'ResponseStatus_Name', type: 'string'}
				],
				key: 'ResponseStatus_id',
				sortInfo: {
					field: 'ResponseStatus_sort',
					direction: 'ASC'
				},
				data: [
					[1, 3, 'Все'],
					[2, 1, 'С ответом'],
					[3, 2, 'Без ответа'],
				]
			}),
			onChange: function(value) {
				this.doFilterPackage();
			}.createDelegate(this)
		});

		this.RefreshButton = new Ext.Button({
			text: 'Обновить',
			iconCls: 'refresh16',
			handler: function(){
				this.doFilterPackage();
			}.createDelegate(this)
		});
		this.DownloadButton = new Ext.Button({
			text: 'Скачать',
			iconCls: 'archive16',
			handler: function(){
				this.downloadRequestIdentFile();
			}.createDelegate(this)
		});
		this.UploadButton = new Ext.Button({
			text: 'Загрузить',
			iconCls: 'database-export16',
			handler: function(){
				this.uploadResponseIdentFile();
			}.createDelegate(this)
		});
		this.DeleteButton = new Ext.Button({
			text: 'Удалить',
			iconCls: 'delete16',
			handler: function(){
				this.deletePersonIdentPackage();
			}.createDelegate(this)
		});


		this.Toolbar = new sw.Promed.Toolbar({
			autoHeight: true,
			items: [
				this.DateRangeMenu,
				'-',
				this.ResponseStatusField,
				'-',
				this.RefreshButton,
				this.DownloadButton,
				this.UploadButton,
				this.DeleteButton
			]
		});

		var packageTpl1 = new Ext.Template(
			'<div style="float: left"><span>{file}</span></div>',
			'<div style="float: right; margin-right: 10px; color: grey;"><i>{date}</i></div>',
			'<div style="clear: both"></div>',
			'<div style="color: grey"><i>Ответ не загружен</i></div>'
		);
		var packageTpl2 = new Ext.Template(
			'<div style="float: left"><span>{file}</span></div>',
			'<div style="float: right; margin-right: 10px; color: grey;"><i>{date}</i></div>',
			'<div style="clear: both"></div>',
			'<div style="color: green"><i>Ответ загружен</i></div>',
			'<div><i>Ошибки: <span style="color: red">{errorCount}</i></span></div>',
			'<div><i>Идентифицированно: <span style="color: green">{actualCount}</span></i></div>'
		);

		var packageRenderer = function(value, meta, record) {
			if (!record || Ext.isEmpty(record.get('PersonIdentPackage_id'))) {
				return '';
			}

			var tpl = (record.get('PersonIdentPackage_IsResponseRetrieved') == 2)?packageTpl2:packageTpl1;
			var arr = record.get('PersonIdentPackage_Name').split('/');

			var params = {
				file: arr[arr.length-1],
				link: record.get('PersonIdentPackage_Name'),
				date: Ext.util.Format.date(record.get('PersonIdentPackage_begDate'), 'd.m.Y'),
				actualCount: record.get('PersonIdentPackage_ActualCount'),
				errorCount: record.get('PersonIdentPackage_ErrorCount')
			};

			return tpl.apply(params);
		};

		this.PackageGridPanel = new sw.Promed.ViewFrame({
			id: 'PIPW_PackageGridPanel',
			dataUrl: '/?c=PersonIdentPackage&m=loadPersonIdentPackageGrid',
			width: 260,
			region: 'west',
			bodyStyle: 'border-right: 1px solid #99bbe8;',
			border: false,
			hideHeaders: true,
			autoLoadData: false,
			contextmenu: false,
			toolbar: false,
			paging: false,
			useEmptyRecord: false,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'PersonIdentPackage_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonIdentPackage_Name', type: 'string', hidden: true},
				{name: 'PersonIdentPackage_begDate', type: 'date', hidden: true},
				{name: 'PersonIdentPackage_IsResponseRetrieved', type: 'int', hidden: true},
				{name: 'PersonIdentPackage_ActualCount', type: 'int', hidden: true},
				{name: 'PersonIdentPackage_ErrorCount', type: 'int', hidden: true},
				{name: 'PersonIdentPackage', header: 'Пакет', id: 'autoexpand', renderer: packageRenderer}
			],
			onRowSelect: function(sm, index, record) {
				var package_grid = this.PackageGridPanel.getGrid();
				var package_pos_grid = this.PackagePosGridPanel.getGrid();

				package_pos_grid.getStore().removeAll();

				this.DownloadButton.disable();
				this.DeleteButton.disable();

				if (!record || Ext.isEmpty(record.get('PersonIdentPackage_id'))) {
					return false;
				}

				if (record.get('PersonIdentPackage_IsResponseRetrieved') != 2) {
					this.DownloadButton.enable();
				}
				this.DeleteButton.enable();

				package_pos_grid.getStore().load({
					params: {PersonIdentPackage_id: record.get('PersonIdentPackage_id')},
					callback: function() {
						this.FilterRow._search();
					}.createDelegate(this)
				});
			}.createDelegate(this)
		});

		var personTpl = new Ext.Template(
			'<p>{Person_SurName} {Person_FirName} {Person_SecName}</p>',
			'<p>{Sex_Name} {Person_BirthDay}</p>'
		);
		var personRenderer = function(value, meta, record) {
			var params = Ext.apply({}, record.data);
			params.Sex_Name = String(params.Sex_Name).slice(0,3);
			params.Person_BirthDay = Ext.util.Format.date(record.get('Person_BirthDay'), 'd.m.Y');
			return personTpl.apply(params);
		};

		var identRangeRenderer = function(value, meta, record) {
			var period = [];
			if (!Ext.isEmpty(record.get('PersonIdentPackagePos_identDT')))
				period.push(Ext.util.Format.date(record.get('PersonIdentPackagePos_identDT'), 'd.m.Y'));
			if (!Ext.isEmpty(record.get('PersonIdentPackagePos_identDT2')))
				period.push(Ext.util.Format.date(record.get('PersonIdentPackagePos_identDT2'), 'd.m.Y'));
			return period.join(' - ');
		};

		var evnTpl = new Ext.Template(
			'{EvnClass}<br/><span class="{linkCls}" onClick="Ext.getCmp(\'{wndId}\').openEvnWindow({Record_id})">{Evn_id}</span>'
		);
		var evnRenderer = function(value, meta, record) {
			if (Ext.isEmpty(record.get('Evn_id'))) {
				return '';
			}
			var params = {
				wndId: wnd.getId(),
				linkCls: String(record.get('EvnClass_SysNick')).inlist(wnd.openableEvnClassList)?'fake-link':'',
				Record_id: record.get('PersonIdentPackagePos_id'),
				EvnClass: record.get('EvnClass_Nick'),
				Evn_id: record.get('Evn_id')
			};
			return evnTpl.apply(params);
		};

		this.FilterRow = new Ext.ux.grid.FilterRow({
			parId: this.id,
			hidden: true,
			clearFilterBtn: false,
			listeners: {
				'search': function(params) {
					this.doFilterPackagePos(params);
				}.createDelegate(this)
			}
		});

		var delaySearch = function(delay) {
			if (wnd.delaySearchId) {
				clearTimeout(wnd.delaySearchId);
			}
			wnd.delaySearchId = setTimeout(function() {
				wnd.FilterRow._search();
				wnd.delaySearchId = null;
			}, delay);
		};

		var createTextFilter = function(name) {
			return new Ext.form.TextField({
				enableKeyEvents: true,
				name: name,
				listeners: {
					'keypress': function(field, e) {
						delaySearch(500);
					}
				}

			});
		}.createDelegate(this);

		this.PackagePosGridPanel = new sw.Promed.ViewFrame({
			id: 'PIPW_PackagePosGridPanel',
			dataUrl: '/?c=PersonIdentPackage&m=loadPersonIdentPackagePosGrid',
			border: false,
			autoLoadData: false,
			contextmenu: false,
			toolbar: false,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			groups: false,
			paging: false,
			useEmptyRecord: false,
			gridplugins: [this.FilterRow],
			stringfields: [
				{name: 'PersonIdentPackagePos_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonIdentPackage_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Person_SurName', type: 'string', hidden: true},
				{name: 'Person_FirName', type: 'string', hidden: true},
				{name: 'Person_SecName', type: 'string', hidden: true},
				{name: 'Sex_id', type: 'string', hidden: true},
				{name: 'Sex_Code', type: 'string', hidden: true},
				{name: 'Sex_Name', type: 'string', hidden: true},
				{name: 'Person_BirthDay', type: 'date', hidden: true},
				{name: 'PersonIdentPackagePos_identDT', type: 'date', hidden: true},
				{name: 'PersonIdentPackagePos_identDT2', type: 'date', hidden: true},
				{name: 'Evn_id', type: 'int', hidden: true},
				{name: 'Evn_pid', type: 'int', hidden: true},
				{name: 'Evn_rid', type: 'int', hidden: true},
				{name: 'Evn_NumCard', type: 'string', hidden: true},
				{name: 'EvnClass_Name', type: 'string', hidden: true},
				{name: 'EvnClass_Nick', type: 'string', hidden: true},
				{name: 'EvnClass_SysNick', type: 'string', hidden: true},
				{name: 'PersonIdentState_id', type: 'int', hidden: true},
				{name: 'PersonIdentState_Code', type: 'int', hidden: true},
				{name: 'Person', header: 'Пациент', width: 240, renderer: personRenderer, filter: createTextFilter('Person')},
				{name: 'IdentRange', header: 'Период', width: 140, renderer: identRangeRenderer, filter: createTextFilter('IdentRange')},
				{name: 'Evn', header: 'Случай лечения', width: 180, renderer: evnRenderer, filter: createTextFilter('Evn')},
				{name: 'PersonIdentState_Name', type: 'string', header: 'Статус', width: 200, filter: createTextFilter('PersonIdentState_Name')},
				{name: 'Errors', type: 'string', header: 'Ошибки', width: 180, filter: createTextFilter('Errors')},
				{name: 'Results', type: 'string', header: 'Результат запроса', id: 'autoexpand', filter: createTextFilter('Results')},
				this.FilterRow
			]
		});

		this.MainPanel = new Ext.Panel({
			region: 'center',
			layout: 'border',
			tbar: this.Toolbar,
			items: [
				this.PackageGridPanel,
				this.PackagePosGridPanel
			]
		});

		Ext.apply(this,{
			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.MainPanel]
		});

		sw.Promed.swPersonIdentPackageWindow.superclass.initComponent.apply(this, arguments);
	}
});