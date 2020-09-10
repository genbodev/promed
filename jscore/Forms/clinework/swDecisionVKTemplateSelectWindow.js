/**
 * swDecisionVKTemplateSelectWindow - окно выбора шаблона решения ВК
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			13.11.2014
 */

sw.Promed.swDecisionVKTemplateSelectWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Выбор шаблона решения ВК',
	maximized: false,
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	onSelect: Ext.emptyFn,
	layout: 'form',
	autoScroll: true,
	autoHeight: true,
	buttonAlign: "right",
	id: 'swDecisionVKTemplateSelectWindow',

	select: function() {
		var base_form = this.FormPanel.getForm();

		if(!base_form.isValid()) {
			sw.swMsg.alert('Ошибка', 'Заполнены не все обязательные поля! Обязательные к заполнению поля выделены особо.');
			return false;
		}

		var templateField = base_form.findField('DecisionVKTemplate_id')
		var record = templateField.getStore().getById(templateField.getValue());

		this.onSelect(record.data);
		this.hide();
	},

	show: function()
	{
		sw.Promed.swDecisionVKTemplateSelectWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if( !arguments[0] || !arguments[0].onSelect ) {
			sw.swMsg.alert('Ошибка', 'Неверные параметры');
			this.hide();
			return false;
		}

		this.onSelect = arguments[0].onSelect;

		var params = {};
		if (arguments[0].ExpertiseNameType_id) {
			params.ExpertiseNameType_id = arguments[0].ExpertiseNameType_id;
		}

		base_form.findField('DecisionVKTemplate_id').getStore().load({params: params});
	},

	initComponent: function()
	{
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyStyle: 'padding: 5px 5px 0',
			frame: true,
			labelAlign: 'right',
			layout: 'form',
			labelWidth: 140,
			items: [
				{
					allowBlank: false,
					xtype: 'swdecisionvktemplatecombo',
					fieldLabel: 'Шаблон решения ВК',
					width: 520
				}
			]
		});

		Ext.apply(this,
			{
				buttons: [
					{
						iconCls: 'ok16',
						text: 'Выбрать',
						handler: function() {
							this.select();
						}.createDelegate(this)
					},
					'-',
					{
						text: BTN_FRMHELP,
						iconCls: 'help16',
						handler: function(button, event)
						{
							ShowHelp(this.title);
						}
					}, {
						text      : 'Отмена',
						tabIndex  : -1,
						tooltip   : 'Отмена',
						iconCls   : 'cancel16',
						handler   : function() {
							this.hide();
						}.createDelegate(this)
					}
				],
				items: [this.FormPanel]
			});
		sw.Promed.swDecisionVKTemplateSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});