/**
 * swLpuFilialEditWindow - окно формы филиала
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Stepan Petrov
 * @version			22.12.2017
 */

sw.Promed.swLpuFilialEditWindow = Ext.extend(sw.Promed.BaseForm, {

	id: 'swLpuFilialEditWindow',
	action: null,
	//callback: ,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	autoHeight: true,
	width: 670,
	layout: 'form',
	modal: true,
	plain: true,
	resizable: false,

	entity: 'LpuFilial',
	formName: 'LpuFilialEditForm',
	//formPrefix: 'LFEW_',
	mainIdField: 'LpuFilial_id',
	isLoadData: false,

	getMainForm: function() {
		return this[this.formName].getForm();
	},

	doSave: function () {
		var wnd = this,
		form = wnd.getMainForm(),
		loadMask = new Ext.LoadMask(wnd.getEl(), {

				msg: langs('Подождите, идет сохранение')
			});

		var begDate = form.findField('LpuFilial_begDate').getValue(),
			endDate = form.findField('LpuFilial_endDate').getValue(),
			dateIsValid = endDate instanceof Date ? begDate < endDate : true;


		if ( ! form.isValid() || ! dateIsValid )
		{
			sw.swMsg.show({
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT,
				icon: Ext.Msg.WARNING,
				buttons: Ext.Msg.OK,

				fn: function () {

					form.getFirstInvalidEl() ? form.getFirstInvalidEl().focus(true) : null;
				}
			});

			return false;
		}

		loadMask.show();

		var required_params = {
			action: null,
			Lpu_id: null
		},
			input_params = {};

		for (var key in required_params)
		{
			input_params[key] = wnd[key];
		}


		form.submit({
			params: input_params,

			failure: function (result_form, action) {

				loadMask.hide();

				if (action.result)
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert( langs('Ошибка #') + action.result.Error_Code,  action.result.Error_Message);
					}
				}

			},

			success: function (result_form, action) {

				loadMask.hide();

				if (action.result)
				{
					Ext.getCmp('LpuPassportEditWindow').findById('LPEW_FilialGrid').loadData();

					wnd.hide();
				} else
				{
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
				}

			}

		});


	},

	loadForm: function (loadMask) {

		var wnd = this,
			form = wnd.getMainForm(),
			main_id_field = wnd.mainIdField,
			input_params = {};


		input_params[main_id_field] = wnd[main_id_field];


		form.load({
			params: input_params,

			failure: function() {

				loadMask.hide();

				sw.swMsg.show({
					title: langs('Ошибка'),
					msg: langs('Ошибка запроса к серверу, попробуйте повторить операцию'),
					icon: Ext.Msg.ERROR,
					buttons: Ext.Msg.OK,

					fn: function () {
						wnd.hide();
					}
				});

			},

			success: function() {

				wnd.isLoadData = true;

				loadMask.hide();
			},

			url: '/?c=LpuPassport&m=getLpuFilialRecord'
		});

	},

	show: function (params) {

		sw.Promed.swLpuFilialEditWindow.superclass.show.apply(this, arguments);

		var wnd = this,
			form = wnd.getMainForm(),
			main_id_field = wnd.mainIdField;

		loadMask = new Ext.LoadMask(wnd.getEl(), {

			msg: LOAD_WAIT
			});

		wnd.isLoadData = false;

		if ( ! arguments[0])
		{
			sw.swMsg.show({
				title: langs('Ошибка'),
				msg: langs('Ошибка открытия формы, не указаны нужные входные параметры'),
				icon: Ext.Msg.ERROR,
				buttons: Ext.Msg.OK,

				fn: function () {
					wnd.hide();
				}
			})
		}

		var args = arguments[0];
		wnd.action = args.action ? args.action : null;
		wnd.PassportToken_tid = args.PassportToken_tid ? args.PassportToken_tid : null;

		loadMask.show();
		wnd.focus();

		form.reset();
		form.setValues(args);


		for (var key in args)
		{
			wnd[key] = args[key];
		}


		if ( ! wnd.action)
		{
			if (Number.isInteger(wnd[main_id_field]) && wnd[main_id_field] > 0)
			{
				wnd.action = 'edit';
			} else
			{
				wnd.action = 'add';
			}
		}

		var base_form = this.LpuFilialEditForm.getForm();
		base_form.findField('RegisterMO_id').getStore().clearFilter();
		base_form.findField('RegisterMO_id').getStore().filterBy(function(rec) {
			return rec.get('RegisterMO_ParentOID') == wnd.PassportToken_tid
		});
		base_form.findField('RegisterMO_id').lastQuery = "";


		loadMask.show();

		switch (wnd.action)
		{
			case 'add':

				wnd.setTitle(langs('Филиал: добавление'));
				wnd.enableEdit(true);

				form.clearInvalid();
				loadMask.hide();

				break;

			case 'edit':

				wnd.setTitle(langs('Филиал: редактирование'));

				wnd.enableEdit(true);
				wnd.loadForm(loadMask);

				break;

			case 'view':

				wnd.setTitle(langs('Филиал: просмотр'));

				wnd.enableEdit(false);
				wnd.loadForm(loadMask);

				break;

			default:

				wnd.hide();

				break;
		}


	},

	initComponent: function() {

		var wnd = this,
			formName = this.formName;
			//formPrefix = this.formPrefix;

		wnd[formName] = new Ext.form.FormPanel({

			bodyStyle: '{pading-top: 0.5em}',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 200,
			layout: 'form',
			id: formName,
			autoLoad: false,
			autoHeight: true,
			url: '/?c=LpuPassport&m=saveLpuFilialRecord',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
		}, [
			{ name: 'LpuFilial_id'},
			{ name: 'LpuFilial_begDate'},
			{ name: 'LpuFilial_endDate'},
			{ name: 'LpuFilial_Name' },
			{ name: 'LpuFilial_Nick' },
			{ name: 'LpuFilial_Code' },
			{ name: 'Oktmo_id' },
			{ name: 'Oktmo_Name' },
			{ name: 'RegisterMO_id' }
		]),

			items: [
				{
					name: 'LpuFilial_id',
					xtype: 'hidden'
				},
				{
					allowBlank: false,
					xtype: 'swdatefield',
					fieldLabel: langs('Дата начала'),
					format: 'd.m.Y',
					name: 'LpuFilial_begDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
				},
				{
					allowBlank: true,
					xtype: 'swdatefield',
					fieldLabel: langs('Дата окончания'),
					format: 'd.m.Y',
					name: 'LpuFilial_endDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
				},
				{
					fieldLabel: langs('Код'),
					allowBlank: false,
					name: 'LpuFilial_Code',
					xtype: "textfield",
					autoCreate: {tag: 'input', maxLength: '30', autocomplete: 'off'},
					width: 400,
					maxLength: 30
				},
				{
					fieldLabel: langs('Наименование'),
					allowBlank: false,
					name: 'LpuFilial_Name',
					xtype: 'textfield',
					autoCreate: {tag: 'input', maxLength: '300', autocomplete: 'off'},
					width: 400,
					maxLength: 300
				},
				{
					fieldLabel: langs('Краткое наименование'),
					allowBlank: false,
					name: 'LpuFilial_Nick',
					xtype: 'textfield',
					autoCreate: {tag: 'input', maxLength: 300, autocomplete: 'off'},
					maxLength: 300
				},
				{
					id: 'Oktmo_id',
					name: 'Oktmo_id',
					xtype: 'hidden'
				},
				{
					allowBlank: false,
					allowLowLevelRecordsOnly: false,
					anchor: '100%',
					fieldLabel: langs('Код ОКТМО'),
					id: 'Oktmo_Name',
					name: 'Oktmo_Name',
					object: 'Oktmo',
					selectionWindowParams: {
						height: 500,
						title: langs('Код ОКТМО'),
						width: 600
					},
					showCodeMode: 2,
					useCodeOnly: true,
					useNameWithPath: false,
					valueFieldId: 'Oktmo_id',
					xtype: 'swtreeselectionfield'
				},
				{
					anchor: '100%',
					fieldLabel: langs('ОИД филиала'),
					comboSubject: 'RegisterMO',
					orderBy: 'ShortName',
					displayField: 'RegisterMO_Display',
					hiddenName: 'RegisterMO_id',
					moreFields: [
						{name: 'RegisterMO_ShortName', type: 'string'},
						{name: 'RegisterMO_OID', type: 'string'},
						{name: 'RegisterMO_ParentOID', type: 'string'},
						{name: 'RegisterMO_Display',
							convert: function(val, row) {
								return row.RegisterMO_ShortName + ' (' + row.RegisterMO_OID + ')';
							}
						}
					],
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{RegisterMO_Display}',
						'</div></tpl>'
					),
					prefix: 'nsi_',
					xtype: 'swcommonsprcombo'
				}
			]
		});

		Ext.apply(this, {
			items: [
				this[this.formName]
			],

			buttons: [
				{
					id: 'LFEW_SaveButton',
					iconCls: 'save16',
					text: BTN_FRMSAVE,
					handler: function () {
						this.doSave();
					}.createDelegate(this)
				},

				'-', HelpButton(this, -1),
				{
					id: 'LFEW_CancelButton',
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL,
					handler: function () {
						console.log('cancel');
						this.hide();
					}.createDelegate(this)
				}
			]
		});

		sw.Promed.swLpuFilialEditWindow.superclass.initComponent.apply(this, arguments);
	}

});