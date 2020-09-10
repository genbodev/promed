/**
 * swRegistryErrorTFOMSEditWindow - окно загрузки реестра-ответа в формате XML.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      06.02.2015
 *
 *
 * @input data: Registry_id - ID реестра
 */

sw.Promed.swRegistryErrorTFOMSEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'RegistryErrorTFOMSEditWindow',
	width: 500,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	onHide: Ext.emptyFn,
	title: lang['oshibka_mek_dobavlenie'],
	initComponent: function()
	{
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			url: '/?c=Registry&m=saveRegistryErrorTFOMS',
			labelWidth: 150,
			labelAlign: 'right',

			items: [{
				name: 'RegistryErrorTFOMS_id',
				xtype: 'hidden'
			}, {
				name: 'Registry_id',
				xtype: 'hidden'
			}, {
				name: 'Evn_id',
				xtype: 'hidden'
			}, {
				name: 'RegistryErrorType_Code',
				xtype: 'hidden'
			}, {
				name: 'RegistryErrorTFOMSLevel_id',
				allowBlank: false,
				value: 1,
				fieldLabel: lang['uroven_oshibki'],
				comboSubject: 'RegistryErrorTFOMSLevel',
				xtype: 'swcommonsprcombo',
				anchor: '100%'
			}, {
				hiddenName: 'RegistryErrorType_id',
				allowBlank: false,
				fieldLabel: lang['oshibka'],
				comboSubject: 'RegistryErrorType',
				editable: true,
				forceSelection: true,
				xtype: 'swcommonsprcombo',
				anchor: '100%'
			}, {
				name: 'RegistryErrorTFOMS_FieldName',
				fieldLabel: lang['imya_polya'],
				xtype: 'textfield',
				anchor: '100%'
			}, {
				name: 'RegistryErrorTFOMS_BaseElement',
				fieldLabel: lang['bazovyiy_element'],
				xtype: 'textfield',
				anchor: '100%'
			}, {
				name: 'RegistryErrorTFOMS_Comment',
				fieldLabel: lang['kommentariy'],
				xtype: 'textfield',
				anchor: '100%'
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'RegistryErrorTFOMS_id'},
				{name: 'Registry_id'},
				{name: 'Evn_id'},
				{name: 'RegistryErrorType_id'},
				{name: 'RegistryErrorType_Code'},
				{name: 'RegistryErrorTFOMS_FieldName'},
				{name: 'RegistryErrorTFOMS_BaseElement'},
				{name: 'RegistryErrorTFOMS_Comment'},
				{name: 'RegistryErrorTFOMSLevel_id'}
			])
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swRegistryErrorTFOMSEditWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners:
	{
		'hide': function()
		{
			this.onHide();
		}
	},
	doSave: function() {
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';

		var win = this;
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					log(this);
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		win.getLoadMask(LOAD_WAIT_SAVE).show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				win.getLoadMask().hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				win.getLoadMask().hide();
				this.formStatus = 'edit';
				this.hide();
			}.createDelegate(this)
		});
	},
	show: function()
	{
		sw.Promed.swRegistryErrorTFOMSEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		win.Registry_id = null;
		win.Evn_id = null;
		win.onHide = Ext.emptyFn;
		win.action = 'view';
		win.formStatus = 'edit';

		var base_form = win.FormPanel.getForm();
		base_form.reset();

		if (!arguments[0] || !arguments[0].Registry_id || !arguments[0].Evn_id)
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi'] + win.id + lang['ne_ukazanyi_neobhodimyie_vhodyaschie_parametryi'],
				title: lang['oshibka']
			});
			this.hide();
		}
		
		if (arguments[0].action)
		{
			win.action = arguments[0].action;
		}
		if (arguments[0].Registry_id)
		{
			win.Registry_id = arguments[0].Registry_id;
		}
		if (arguments[0].Evn_id)
		{
			win.Evn_id = arguments[0].Evn_id;
		}
		if (arguments[0].onHide)
		{
			win.onHide = arguments[0].onHide;
		}

		base_form.findField('Registry_id').setValue(win.Registry_id);
		base_form.findField('Evn_id').setValue(win.Evn_id);
	}
});