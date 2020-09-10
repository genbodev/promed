/**
 * swTableDirectInfoEditWindow - окно редактирования информации о базовых справочниках
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.07.2014
 */

/*NO PARSE JSON*/

sw.Promed.swTableDirectInfoEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swTableDirectInfoEditWindow',
	width: 500,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function() {
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();
				if (typeof this.callback == 'function') {
					this.callback();
				}
				this.formStatus = 'edit';
				this.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swTableDirectInfoEditWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = form.FormPanel.getForm();

		base_form.reset();

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(form.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch (this.action) {
			case 'add':
				form.enableEdit(true);
				form.setTitle(lang['bazovyiy_spravochnik_dobavlenie']);
				loadMask.hide();

				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					form.enableEdit(true);
					form.setTitle(lang['bazovyiy_spravochnik_redaktirovanie']);
				} else {
					form.enableEdit(false);
					form.setTitle(lang['bazovyiy_spravochnik_prosmotr']);
				}

				base_form.load({
					failure:function () {
						//sw.swMsg.alert('Ошибка', 'Не удалось получить данные');
						loadMask.hide();
						form.hide();
					},
					url: '/?c=TableDirect&m=loadTableDirectInfoForm',
					params: {TableDirectInfo_id: base_form.findField('TableDirectInfo_id').getValue()},
					success: function() {
						loadMask.hide();

					}.createDelegate(this)
				});

				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'TDIEW_TableDirectInfoEditForm',
			url: '/?c=TableDirect&m=saveTableDirectInfo',
			labelWidth: 160,
			labelAlign: 'right',

			items: [{
				name: 'TableDirectInfo_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				name: 'TableDirectInfo_Code',
				fieldLabel: lang['kod'],
				xtype: 'numberfield',
				width: 100
			}, {
				allowBlank: false,
				name: 'TableDirectInfo_Name',
				fieldLabel: lang['naimenovanie'],
				xtype: 'textfield',
				width: 180
			}, {
				allowBlank: false,
				name: 'TableDirectInfo_SysNick',
				fieldLabel: lang['sistemnoe_naimenovanie'],
				xtype: 'textfield',
				width: 180
			}, {
				hiddenName: 'TableDirectInfo_Descr',
				fieldLabel: lang['opisanie'],
				xtype: 'textfield',
				width: 180
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'TableDirectInfo_id'},
				{name: 'TableDirectInfo_Code'},
				{name: 'TableDirectInfo_Name'},
				{name: 'TableDirectInfo_SysNick'},
				{name: 'TableDirectInfo_Descr'}
			]),
			keys: [{
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					id: 'TDIEW_ButtonSave',
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
					id: 'TDIEW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swTableDirectInfoEditWindow.superclass.initComponent.apply(this, arguments);
	}
});