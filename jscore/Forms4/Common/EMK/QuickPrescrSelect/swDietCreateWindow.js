/**
 * swDietCreateWindow - Окно быстрого добавления диеты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		Common.EMK
 * @author		GTP_fox
 * @access		public
 * @copyright	Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.QuickPrescrSelect.swDietCreateWindow', {
	/* свойства */
	alias: 'widget.swDietCreateWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new new-packet-create-window',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	layout: 'form',
	refId: 'swDietCreateWindow',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Назначение диеты',
	width: 700,
	autoHeight: true,
	parentPanel: {},
	requires: [
		'common.EMK.SpecificationDetail.EvnPrescrDietPanel'
	],
	show: function (data) {
		if (!data) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}

		var win = this;
		var params = data;
		win.callback = (typeof data.callback == 'function' ? data.callback : Ext6.emptyFn);
		params.callback = function(){
			win.callback();
			win.hide();
		};

		//win.EvnPrescrDietPanel.setData(arguments[0]);
		win.EvnPrescrDietPanel.show(params);
		this.callParent(arguments);
	},
	/* конструктор */
	initComponent: function() {
		var win = this;
		win.EvnPrescrDietPanel = Ext6.create('common.EMK.SpecificationDetail.EvnPrescrDietPanel', {
			parentPanel: win.parentPanel,
			inModalWindow: false
		});
		Ext6.apply(win, {
			layout: 'fit',
			bodyPadding: 0,
			margin: 0,
			border: false,
			items: [
				win.EvnPrescrDietPanel
			],
			buttons: ['->', {
				handler: function () {
					win.hide();
				},
				cls: 'buttonCancel',
				text: 'Отмена'
			}, {
				handler: function () {
					win.EvnPrescrDietPanel.doSave();
				},
				cls: 'buttonAccept',
				text: 'Сохранить',
				margin: '0 20 0 0'
			}]
		});

		this.callParent(arguments);
	}
});