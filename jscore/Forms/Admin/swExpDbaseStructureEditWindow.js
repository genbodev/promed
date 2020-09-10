/**
 * swExpDbaseStructureEditWindow - окно редактирования полей запроса информационного обмена с АО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			07.11.2013
 */

sw.Promed.swExpDbaseStructureEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	id: 'swExpDbaseStructureEditWindow',
	width: 450,
	callback: Ext.emptyFn,
	draggable: true,
	maximizable: false,
	modal: true,
	objectSrc: '/jscore/Forms/Admin/swExpDbaseStructureEditWindow.js',
	title: langs('ЛЛО. Информационный обмен с АО. Настройка полей: Редактирование'),

	doSave: function()
	{
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		wnd.getLoadMask("Подождите, идет сохранение...").show();

		var data = new Object();

		data.DbaseStructureData = base_form.getValues();

		wnd.formStatus = 'edit';
		wnd.getLoadMask().hide();

		wnd.callback(data);
		wnd.hide();
	},

	show: function()
	{
		sw.Promed.swExpDbaseStructureEditWindow.superclass.show.apply(this, arguments);

		if (!isSuperAdmin() || getGlobalOptions().region.nick=='kz') {
			sw.swMsg.alert(lang['soobschenie'], lang['net_dostupa_k_forme'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.restore();
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if (arguments && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments && arguments[0].onHide) {
			this.action = arguments[0].action;
		}

		if (arguments && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(langs('ЛЛО. Информационный обмен с АО. Настройка полей: Добавление'));
				this.enableEdit(true);
				base_form.clearInvalid();
				loadMask.hide();
				base_form.findField('Ord').focus(true, 250);
				break;

			case 'edit':
				this.setTitle(langs('ЛЛО. Информационный обмен с АО. Настройка полей: Редактирование'));
				this.enableEdit(true);
				loadMask.hide();
				base_form.findField('Ord').focus(true, 250);
				break;

			case 'view':
				this.setTitle(langs('ЛЛО. Информационный обмен с АО. Настройка полей: Просмотр'));
				this.enableEdit(false);
				loadMask.hide();
				break;

			default:
				this.hide();
				loadMask.hide();
				break;
		}
	},

	initComponent: function()
	{
		var wnd = this;

		this.FormPanel = new Ext.form.FormPanel({
			border: false,
			frame: true,
			id: 'EDSE_ExpDbaseStructureEditForm',
			labelAlign: 'right',
			labelWidth: 140,
			region: 'center',
			url: '/?c=exp_Query&m=saveDbaseStructure',

			items: [{
				name: 'DbaseStructure_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Query_id',
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				allowNegative: false,
				allowDecimals: false,
				fieldLabel: lang['nomer'],
				name: 'Ord',
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['naimenovanie_zapros'],
				name: 'Query_ColumnName',
				xtype: 'textfield',
				anchor: '99%'
			}, {
				allowBlank: false,
				fieldLabel: lang['naimenovanie_dbf'],
				name: 'Dbase_ColumnName',
				xtype: 'textfield',
				anchor: '99%'
			}, {
				allowBlank: false,
				fieldLabel: lang['tip'],
				name: 'Dbase_ColumnType',
				xtype: 'textfield',
				autoCreate: {tag: 'input', type: 'text', maxLength: 1}
			}, {
				allowNegative: false,
				allowDecimals: false,
				allowBlank: false,
				fieldLabel: lang['dlina'],
				name: 'Dbase_ColumnLength',
				xtype: 'numberfield'
			}, {
				allowNegative: false,
				allowDecimals: false,
				fieldLabel: lang['tochnost'],
				name: 'Dbase_ColumnPrecision',
				xtype: 'numberfield'
			}, {
				fieldLabel: lang['opisanie'],
				name: 'Description',
				xtype: 'textarea',
				anchor: '99%'
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'FSEW_SaveButton',
				text: BTN_FRMSAVE
			},
				'-',
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'FSEW_CancelButton',
					tabIndex: 2409,
					text: BTN_FRMCANCEL
				}]
		});

		sw.Promed.swExpDbaseStructureEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
