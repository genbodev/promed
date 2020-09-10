/**
* swDateSetWindow - форма выбора даты
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      25.02.2011
* @comment      Префикс для id компонентов DSF (DateSetForm)
*/

sw.Promed.swDateSetWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSelect: function() {
		var date = this.FormPanel.getForm().findField('date').getValue();
		this.callback(date);
		this.hide();
	},
	draggable: true,
	id: 'DateSetWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'DateSetForm',
			labelAlign: 'right',
			labelWidth: 100,

			items: [{
				allowBlank: false,
				fieldLabel: lang['data'],
				format: 'd.m.Y',
				name: 'date',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_DSF + 1,
				width: 100,
				xtype: 'swdatefield'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSelect();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					this.FormPanel.getForm().findField('date').focus(true);
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_DSF + 2,
				text: BTN_FRMSELECT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.FormPanel.getForm().findField('Lpu_id').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_DSF + 3,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swDateSetWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('DateSetWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.D:
					current_window.doSelect();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.D,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swDateSetWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		setCurrentDateTime({
			callback: Ext.emptyFn,
			dateField: base_form.findField('date'),
			loadMask: false,
			setDate: true,
			setDateMaxValue: false,
			windowId: this.id
		});

		base_form.clearInvalid();

		base_form.findField('date').focus(true, 250);
	},
	title: lang['vyibor_datyi'],
	width: 300
});