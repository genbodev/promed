/**
 * swElectronicInfomatProfileEditWindow - основная специальность инфомата
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 */
/*NO PARSE JSON*/
sw.Promed.swElectronicInfomatProfileEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: false,
	maximized: false,
	modal: true,
	height: 152,
	width: 550,
	id: 'swElectronicInfomatProfileEditWindow',
	title: 'Пункт обслуживания',
	layout: 'border',
	resizable: false,
	formName: 'ElectronicInfomatProfileEditForm', // имя основной формы
	formPrefix: 'EIPEW_', // краткое имя формы (для айдишников)

	getMainForm: function() { return this[this.formName].getForm(); },
	showWarnMessage: function (message, fn) {

		sw.swMsg.show({

			msg: message,
			title: ERR_INVFIELDS_TIT,
			icon: Ext.Msg.WARNING,
			buttons: Ext.Msg.OK,

			fn: function() { if (fn && typeof fn == 'function') fn(); }
		});
	},
	doSave: function() {

		var wnd = this,
			form = wnd.getMainForm();

		var valid = true;

		if (!form.isValid()) {
			wnd.showWarnMessage(ERR_INVFIELDS_MSG, function(){ wnd[wnd.formName].getFirstInvalidEl().focus(true); });
			valid = false;
		}

		var values = form.getValues();

		let LpuSectionProfile_Field = wnd.getMainForm().findField("LpuSectionProfile_id");
		let MedSpecOms_Field = wnd.getMainForm().findField("MedSpecOms_id");

		if (LpuSectionProfile_Field.getValue() && MedSpecOms_Field.getValue()) {
			wnd.showWarnMessage('Выберите либо специальность либо профиль');
			valid =  false;
		}

		if (LpuSectionProfile_Field.getValue() === "" && MedSpecOms_Field.getValue() === "") {
			wnd.showWarnMessage('Заполните либо специальность либо профиль');
			valid =  false;
		}

		if (wnd.profiles) {
			Object.keys(wnd.profiles).some(function(obj){

				var key = obj;
				var profile_id = wnd.profiles[key].profile_id,
					position = wnd.profiles[key].position,
					profileName = wnd.profiles[key].profileName;


					if (profile_id == values.LpuSectionProfile_id && profile_id !== ""
						&& ((wnd.action != 'add' && key != values.ElectronicInfomatProfile_id)
						|| wnd.action == 'add')
					) {

						wnd.showWarnMessage('Cпециальность ' + profileName + ' уже добавлена в ' +
							'качестве основной для позиции отображения № ' + position
						);
						valid =  false;
					}

					if (position == values.ElectronicInfomatProfile_Position
						&& ((wnd.action != 'add' && key != values.ElectronicInfomatProfile_id)
						|| wnd.action == 'add')
					) {
						wnd.showWarnMessage('Для позиции отображения № ' + position +
							' уже добавлена специальность ' + profileName
						);
						valid = false;
					}
			});
		}

		if (!valid) return false;

		form.submit({
			success: function(form, action) {
				wnd.hide();
				if (wnd.callback && typeof wnd.callback == 'function') { wnd.callback(); }
			},
			failure: function(form, action) {
				switch (action.failureType) {
					case Ext.form.Action.SERVER_INVALID:
						Ext.Msg.alert("Ошибка", action.result.msg);
				}
			}
		});
		return true;
	},
	initComponent: function()
	{
		var wnd = this;
		var	formName = wnd.formName;

		wnd[formName] = new Ext.form.FormPanel({
			id: formName,
			region: 'center',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 150,
			frame: true,
			border: false,
			items: [
				{
					name: 'ElectronicInfomatProfile_id',
					xtype: 'hidden'
				},{
					name: 'ElectronicInfomat_id',
					xtype: 'hidden'
				},{
					xtype: 'swmedspecomscombo',
					fieldLabel: "Специальность",
					tabIndex:14,
					displayField: 'MedSpecOms_Name',
					width: 300,
					hiddenName: 'MedSpecOms_id'
				}, {
					name: 'LpuSectionProfile_id',
					allowBlank: true,
					xtype: 'swlpusectionprofilelitecombo',
					displayField: 'ProfileSpec_Name',
					width: 300,
					listeners: {
						render: function (combo) {
							if (getRegionNick() != 'ekb') {
								wnd.getMainForm().findField("MedSpecOms_id").hideContainer();
							}
						}
					},
					fieldLabel: langs(getRegionNick() != 'ekb' ? 'Специальность' : 'Профиль'),
					tpl: '<tpl for="."><div class="x-combo-list-item">'+
						'{ProfileSpec_Name}'+
						'</div></tpl>'
				},{
					name: 'ElectronicInfomatProfile_Position',
					fieldLabel: 'Позиция отображения',
					xtype: 'textfield',
					allowBlank: false,
					width: 100,
					autoCreate: {tag: "input",  maxLength: "1", autocomplete: "off"},
					maskRe: /[1-5]/,
				}
			],
			url: '/?c=ElectronicInfomat&m=saveElectronicInfomatProfile',
			reader: new Ext.data.JsonReader({
				success: function(){}
			}, [
				{name: 'ElectronicInfomatProfile_id'},
				{name: 'ElectronicInfomat_id'},
				{name: 'LpuSectionProfile_id'},
				{name: 'MedSpecOms_id'},
				{name: 'ElectronicInfomatProfile_Position'}
			])
		});

		Ext.apply(this, {
			buttons:
				[
					{
						handler: function() { this.ownerCt.doSave(); },
						iconCls: 'save16',
						text: BTN_FRMSAVE
					},{ text: '-' }, HelpButton(this, 0),
					{
						handler: function() { this.ownerCt.hide(); },
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}
				],
			items: [
				this[this.formName]
			]
		});

		sw.Promed.swElectronicInfomatProfileEditWindow.superclass.initComponent.apply(this, arguments);
	},
	setDisabled: function(disable) {

		var wnd = this,
			form = wnd.getMainForm(),
			field_arr = [];

		form.items.each(function(field){ field.setDisabled(disable); });

		if (disable) {
			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
		} else {
			wnd.buttons[0].enable();
			wnd.buttons[1].enable();
		}
	},
	loadForm: function(loadMask) {

		var wnd = this,
			form = wnd.getMainForm();

		var ElectronicInfomatProfile_id = form.findField('ElectronicInfomatProfile_id').getValue();

		if (ElectronicInfomatProfile_id) {
			form.load({
				params: { ElectronicInfomatProfile_id: ElectronicInfomatProfile_id },
				url: '/?c=ElectronicInfomat&m=loadElectronicInfomatProfileForm',
				success: function() { loadMask.hide(); }.createDelegate(this),
				failure: function() {  loadMask.hide(); }
			});
		}
	},
	show: function() {

		sw.Promed.swElectronicInfomatProfileEditWindow.superclass.show.apply(this, arguments);

		var wnd = this,
			form = wnd.getMainForm(),
			loadMask = new Ext.LoadMask(wnd.getEl(),{ msg: LOAD_WAIT });

		wnd.action = null;
		if (!arguments[0]){

			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				title: langs('Ошибка'),
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),

				fn: function() { wnd.hide(); }
			});
		}

		var args = arguments[0];
		wnd.focus();
		form.reset();

		this.setTitle("Основная специальность");

		for (var field_name in args) {
			log(field_name +':'+ args[field_name]);
			wnd[field_name] = args[field_name];
		}

		form.setValues(args);
		loadMask.show();

		var store = form.findField('LpuSectionProfile_id').getStore();

		store.baseParams = {
			LpuUnitType_id: 2,
			isProfileSpecCombo: true
		};

		store.load();

		switch (this.action){
			case 'add':

				this.setTitle(this.title + ": Добавление");
				loadMask.hide();
				wnd.setDisabled(false);
				break;

			case 'edit':
			case 'view':

				wnd.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				wnd.setDisabled(this.action == "view");
				wnd.loadForm(loadMask);
				break;
		}
	}
});