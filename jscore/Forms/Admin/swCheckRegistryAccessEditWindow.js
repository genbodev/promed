/**
 * swCheckRegistryAccessEditWindow - окно редактирования запрета МО на добавление реестров
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			30.09.2014
 */

/*NO PARSE JSON*/

sw.Promed.swCheckRegistryAccessEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swCheckRegistryAccessEditWindow',
	autoHeight: true,
	modal: true,
	title: lang['zapret_formirovanie_reestrov'],
	width: 560,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {};

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		base_form.submit({
			url: '/?c=Options&m=saveCheckRegistryAccess',
			params: params,
			failure: function() {
				loadMask.hide();
			}.createDelegate(this),
			success: function(form, action) {
				loadMask.hide();
				if (action.result.success) {
					this.callback();
					this.hide();
				}
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swCheckRegistryAccessEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'CRAEW_FormPanel',
			bodyStyle: 'padding: 10px 0;',
			labelAlign: 'right',
			labelWidth: 120,

			items: [{
				xtype: 'hidden',
				name: 'DataStorage_id'
			}, {
				xtype: 'swlpusearchcombo',
				hiddenName: 'LimitLpu_id',
				fieldLabel: lang['mo'],
				width: 320
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					id: 'CRAEW_ButtonSave',
					text: lang['sohranit'],
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
					id: 'CRAEW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swCheckRegistryAccessEditWindow.superclass.initComponent.apply(this, arguments);
	}
});