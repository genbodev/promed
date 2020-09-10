/**
 * swRecalcKSGWindow -
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author
 * @version			28.02.2018
 */
/*NO PARSE JSON*/

sw.Promed.swInfoKVSFromERSBWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swInfoKVSFromERSBWindow',
	title: "Информация о КВС из ЭРСБ",
	layout: 'form',
	resizable: true,
	autoHeight: true,
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	resizable: false,
	plain: true,
	initComponent: function()
	{
		var _this = this;

		this.onePanel = new Ext.form.FormPanel({
			layout: 'form',
			region: 'center',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'left',
			labelWidth: 170,
			border: false,
			frame: true,
			renderTo: Ext.getBody(),
			items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						labelWidth: 200,
						layout: 'form',
						items: [{
							fieldLabel: langs('Идентификатор'),
							text: '',
							name: 'Hosp_id',
							hiddenName: 'Hosp_id',
							width: 300,
							xtype: 'textfield',
							readOnly: true
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							border: false,
							labelWidth: 200,
							layout: 'form',
							items: [{
								fieldLabel: langs('Дата передачи в ЭРСБ'),
								text: '',
								name: 'Hosp_date',
								hiddenName: 'Hosp_date',
								width: 300,
								xtype: 'textfield',
								readOnly: true
							}]
						}]
					}]
				}
			]
		});

		this.formPanel = new Ext.Panel({
			region: 'center',
			labelAlign: 'right',
			labelWidth: 50,
			frame: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			autoHeight: true,
			items: [
				this.onePanel
			]
		});

		Ext.apply(this, {
			xtype: 'panel',
			items: [
				_this.formPanel
			],
			buttons: [{
				text: '-'
			},
				{
					iconCls: 'close16',
					tabIndex: TABINDEX_RRLW + 14,
					handler: function() {
						_this.hide();
					},
					text: BTN_FRMCLOSE
				}]
		});

		sw.Promed.swInfoKVSFromERSBWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swInfoKVSFromERSBWindow.superclass.show.apply(this, arguments);
		var base_form = this.onePanel.getForm();
		Ext.Ajax.request({
			url: '/?c=EvnPS&m=getInfoKVSfromERSB',
			params: { EvnPS_id: arguments[0].EvnID },
			callback: function (options, success, response) {
				if (success) {
					if (response.responseText) {
						var resp = Ext.util.JSON.decode(response.responseText);
						base_form.findField('Hosp_id').setValue(resp.data.Hosp_id);
						base_form.findField('Hosp_date').setValue(resp.data.Hosp_date);
					}
				}
			}
		});
	}
});
