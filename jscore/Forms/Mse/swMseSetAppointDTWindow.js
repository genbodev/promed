/**
 * swMseSetAppointDTWindow - окно "Ввод даты и времени проведения экспертизы"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Dmitrii Vlasenko
 * @version			18.08.2017
 */

/*NO PARSE JSON*/

sw.Promed.swMseSetAppointDTWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swMseSetAppointDTWindow',
	layout: 'form',
	autoHeight: true,
	width: 500,
	action: 'view',
	title: 'Ввод даты и времени проведения экспертизы',
	doSave: function()
	{
		if ( this.formStatus == 'save' ) {
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
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			this.formStatus = 'edit';
			return false;
		}

		var params = {};

		win.getLoadMask(LOAD_WAIT_SAVE).show();
		base_form.submit({
			params: params,
			failure: function() {
				win.getLoadMask().hide();
				this.formStatus = 'edit';
			}.createDelegate(this),
			success: function(form, action) {
				win.getLoadMask().hide();
				this.formStatus = 'edit';
				if (action.result.success) {
					this.callback();
					this.hide();
				}
			}.createDelegate(this)
		});

		return true;
	},
	show: function(){
		sw.Promed.swMseSetAppointDTWindow.superclass.show.apply(this, arguments);

		this.formStatus = 'edit';
		this.enableEdit(true);

		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].EvnPrescrMse_id) {
			base_form.findField('EvnPrescrMse_id').setValue(arguments[0].EvnPrescrMse_id);
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}
	},

	initComponent: function() {
		var win = this;
		this.FormPanel = new sw.Promed.FormPanel({
			border: true,
			bodyStyle:'width:100%;background:#DFE8F6;padding:5px;',
			autoHeight: true,
			labelWidth: 160,
			url: '/?c=Mse&m=setMseAppointDT',
			timeout: 6000,
			items: [{
				name: 'EvnPrescrMse_id',
				xtype: 'hidden'
			}, {
				fieldLabel: 'Дата',
				allowBlank: false,
				name: 'EvnPrescrMse_appointDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				fieldLabel: 'Время',
				allowBlank: false,
				name: 'EvnPrescrMse_appointTime',
				plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
				tabIndex: TABINDEX_EHPEF + 4,
				validateOnBlur: false,
				width: 60,
				xtype: 'swtimefield'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//
				}
			},
			[
				{ name: 'EvnPrescrMse_appointDate' },
				{ name: 'EvnPrescrMse_appointTime' }
			])
		});

		Ext.apply(this, {
			items: [this.FormPanel],
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}]
		});

		sw.Promed.swMseSetAppointDTWindow.superclass.initComponent.apply(this, arguments);
	}
});
