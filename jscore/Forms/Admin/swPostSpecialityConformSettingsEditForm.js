/**
 * swPostSpecialityConformSettingsEditForm - Форма просмотра/добавления/редактирования соответствия должности и специальности
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package            Admin
 * @access            public
 * @copyright        Copyright (c) 2018 Swan Ltd
 */


sw.Promed.swPostSpecialityConformSettingsEditForm = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPostSpecialityConformSettingsEditForm',
	objectName: 'swPostSpecialityConformSettingsEditForm',
	objectSrc: '/jscore/Forms/Admin/swPostSpecialityConformSettingsEditForm.js',
	width: 450,
	height: 250,
	maximazible: true,
	minimazible: true,
	layout: 'border',
	title: langs('Соответствие должности и специальности'),
	initComponent: function () {
		var win = this;

		win.PostCombo = new Ext.form.ComboBox({
			name: 'PostCombo',
			allowBlank: false,
			anchor: '95%',
			fieldLabel: 'Должность',
			displayField: 'PostMed_Name',
			valueField: 'PostMed_id',
			width: 300,
			resizable: true,
			typeAhead: true,
			lazyRender: true,
			mode: 'local',
			listWidth: 600,
			queryMode: 'local',
			store: new Ext.db.AdapterStore({
				autoload: false,
				dbFile: 'Promed.db',
				fields: [
					{name: 'PostMed_id', mapping: 'PostMed_id'},
					{name: 'PostMed_Name', mapping: 'PostMed_Name'}
				],
				key: 'PostMed_id',
				sortInfo: {
					field: 'PostMed_Name'
				},
				tableName: 'PostMed'
			}),
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'{PostMed_Name}',
				'</div></tpl>'),
			listeners: {
				buffer: 50,
				change: function () {
					var store = this.store;
					store.clearFilter();
					store.filter({
						propery: 'PostMed_Name',
						anyMatch: true,
						value: this.getValue()
					});
				},
				expand: function(combo){
					combo.innerList.setWidth(600);
					combo.list.setWidth(600);
				}.createDelegate(this)
			}
		});

		win.SpecialityCombo = new Ext.form.ComboBox({
			name: 'SpecialityCombo',
			anchor: '95%',
			allowBlank: false,
			fieldLabel: 'Специальность',
			width: 300,
			resizable: true,
			typeAhead: true,
			lazyRender: true,
			listWidth: 600,
			mode: 'local',
			queryMode: 'local',
			store: new Ext.data.JsonStore({
				autoLoad: true,
				fields: [
					{name: 'id', type: 'int'},
					{name: 'name', type: 'string'},
					{name: 'code', type: 'string'},
					{name: 'fullname', type: 'string'}
				],
				key: 'id',
				sortInfo: {
					field: 'name'
				},
				url: '/?c=PostSpeciality&m=loadSpecialityList'
			}),
			displayField: 'name',
			valueField: 'id',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<b>{name}</b> <span style="font-size:0.8em">(<span style="color:red">{code}</span> {fullname})<span>',
				'</div></tpl>'),
			listeners: {
				buffer: 50,
				change: function () {
					var store = this.store;
					store.clearFilter();
					store.filter({
						propery: 'name',
						anyMatch: true,
						value: this.getValue()
					});
				},
				expand: function(combo){
					combo.innerList.setWidth(600);
					combo.list.setWidth(600);
				}.createDelegate(this)
			}
		});

		win.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'PostSpecConformEditForm',
			border: false,
			region: 'center',
			items: [
				{
					layout: 'form',
					autoHeight: true,
					labelAlign: 'right',
					labelWidth: 100,
					items: [
						win.PostCombo,
						win.SpecialityCombo
					]
				}
			],

		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'PSCSEF_SaveButton',
					text: BTN_FRMSAVE
				}, {
					text: '-'
				},
				HelpButton(this),
				{
					handler: function () {
						win.hide();
					},
					iconCls: 'cancel16',
					text: langs('Отмена')
				}],
			items: [
				this.FormPanel
			]
		});
		win.PostCombo.getStore().reload();
		sw.Promed.swPostSpecialityConformSettingsEditForm.superclass.initComponent.apply(this, arguments);
	},
	show: function () {
		var win = this;
		sw.Promed.swPostSpecialityConformSettingsEditForm.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.grid = null;
		this.url = '';
		this.PostSpeciality_id = '';

		if (!arguments[0]) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function () {
				win.hide()
			});
			return false;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		} else {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указан обязательный параметр - action'), function () {
				win.hide();
			});
		}
		if (arguments[0].grid) {
			this.grid = arguments[0].grid;
		} else {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указан обязательный параметр - grid'), function () {
				win.hide();
			});
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].caller) {
			this.callingWnd = arguments[0].caller;
		}

		this.PostCombo.enable();
		this.SpecialityCombo.enable();
		this.buttons[0].show();
		this.PostCombo.reset();
		this.SpecialityCombo.reset();
		this.url = '/?c=PostSpeciality&m=savePostSpecialityPair';

		if (this.action != 'add') {
			var rec = this.grid.getSelectionModel().getSelected().data;
			this.url = '/?c=PostSpeciality&m=editPostSpecialityPair';
			if (!Ext.isEmpty(rec)) {
				this.PostCombo.setValue(rec.Post_Name);
				this.SpecialityCombo.setValue(rec.Speciality_Name);
				this.PostSpeciality_id = rec.PostSpeciality_id;
			}
			if (this.action == 'view') {
				this.PostCombo.disable();
				this.SpecialityCombo.disable();
				this.buttons[0].hide();
			}
		}
	},
	doSave: function () {
		var win = this,
			loadMask = new Ext.LoadMask(this.getEl(), {msg: 'Сохранение...'});

		var v = this.PostCombo.getValue();
		var r = this.PostCombo.findRecord(this.PostCombo.valueField || this.PostCombo.displayField, v);
		var indexP = this.PostCombo.store.indexOf(r);
		if (indexP < 0) {
			sw.swMsg.alert(langs('Ошибка'), langs('Выбрана несуществующая должность'), function () {
				win.PostCombo.reset();
			});
			return false;
		}
		v = this.SpecialityCombo.getValue();
		r = this.SpecialityCombo.findRecord(this.SpecialityCombo.valueField || this.SpecialityCombo.displayField, v);
		var indexS = this.SpecialityCombo.store.indexOf(r);
		if (indexS < 0) {
			sw.swMsg.alert(langs('Ошибка'), langs('Выбрана несуществующая специальность'), function () {
				win.SpecialityCombo.reset();
			});
			return false;
		}

		var params = {
			Post_id: this.PostCombo.getStore().data.items[indexP].data.PostMed_id,
			Speciality_id: this.SpecialityCombo.getStore().data.items[indexS].data.id
		};
		if (this.PostSpeciality_id)
			params.PostSpeciality_id = this.PostSpeciality_id;

		loadMask.show();
		Ext.Ajax.request({
			url: win.url,
			params: params,
			success: function (response) {
				var resp = Ext.util.JSON.decode(response.responseText);
				if (resp.Error_Msg) {
					sw.swMsg.alert(resp.Error_Msg ? resp.Error_Msg : 'Ошибка');
				}
				loadMask.hide();
				win.hide();
				var data = {
					start: win.grid.getStore().lastOptions.params.start
				};
				win.callingWnd.doSearch(data);
			},
			failure: function (response) {
				sw.swMsg.alert(langs('Ошибка'), response.responseText);
				loadMask.hide();
			}
		});
	}
});