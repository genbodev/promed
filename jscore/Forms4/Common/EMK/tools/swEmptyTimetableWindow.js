/**
 * swEmptyTimetableWindow - Окно "Нет расписания"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.tools.swEmptyTimetableWindow', {
	/* свойства */
	alias: 'widget.swEmptyTimetableWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Нет расписания',
	show: function (data) {
		this.callParent(arguments);

		var me = this;
		if (data && data.callback) {
			me.callback = data.callback;
		} else {
			me.callback = Ext6.emptyFn;
		}
	},
	initComponent: function() {
		var me = this;

		Ext6.apply(me, {
			border: false,
			width: 400,
			autoHeight: true,
			layout: 'anchor',
			items: [{
				text: 'Поставить в очередь',
				cls: 'select-button',
				iconCls: 'select-icon',
				textAlign: 'left',
				anchor: '100%',
				margin: "25px 25px 0 25px",
				xtype: 'button',
				handler: function() {
					me.hide();
					me.callback();
				}
			}, {
				text: 'Отмена',
				cls: 'select-button',
				iconCls: 'select-icon',
				textAlign: 'left',
				anchor: '100%',
				margin: "0 25px 25px 25px",
				xtype: 'button',
				handler: function() {
					me.hide();
				}
			}]
		});

		this.callParent(arguments);
	}
});