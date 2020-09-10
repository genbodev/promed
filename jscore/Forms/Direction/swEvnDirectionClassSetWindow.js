/**
* swEvnDirectionClassSetWindow - форма выбора типа направления
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Direction
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      01.02.2011
* @comment      Префикс для id компонентов EDCSW (EvnDirectionClassSetWindow)
*
*
* Использует: swEvnDirectionHistologicEditWindow
*             swEvnDirectionMorfoHistologicEditWindow
*/

sw.Promed.swEvnDirectionClassSetWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSelect: function() {
		//
	},
	draggable: true,
	id: 'EvnDirectionClassSetWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'EvnDirectionClassSetForm',
			labelAlign: 'right',
			labelWidth: 120,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'EvnDirectionClass_id' }
			]),
			style: 'padding: 5px',

			items: [{
				allowBlank: true,
				codeField: 'EvnDirectionClass_Code',
				displayField: 'EvnDirectionClass_Name',
				editable: false,
				fieldLabel: lang['vid_napravleniya'],
				hiddenName: 'EvnDirectionClass_id',
				hideEmptyRow: true,
				listeners: {
					'blur': function(combo)  {
						if ( combo.value == '' )
							combo.setValue(1);
					}
				},
				store: new Ext.data.SimpleStore({
					autoLoad: true,
					data: [
						[ 1, 1, lang['gistologicheskoe_issledovanie'] ],
						[ 2, 2, lang['patologoanatomicheskoe_issledovanie'] ]
					],
					fields: [
						{ name: 'EvnDirectionClass_id', type: 'int' },
						{ name: 'EvnDirectionClass_Code', type: 'int' },
						{ name: 'EvnDirectionClass_Name', type: 'string' }
					],
					key: 'EvnDirectionClass_id',
					sortInfo: { field: 'EvnDirectionClass_Code' }
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{EvnDirectionClass_Code}</font>&nbsp;{EvnDirectionClass_Name}',
					'</div></tpl>'
				),
				value: 1,
				valueField: 'EvnDirectionClass_id',
				width: 300,
				xtype: 'swbaselocalcombo'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				onShiftTabAction: function () {
					//
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				// tabIndex: TABINDEX_EDHEF + 2,
				text: lang['vyibrat']
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
					this.buttons[0].focus(true);
				}.createDelegate(this),
				onTabAction: function () {
					this.FormPanel.getForm().findField('EvnDirectionClass_id').focus(true);
				}.createDelegate(this),
				// tabIndex: TABINDEX_EDHEF + 3,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnDirectionClassSetWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnDirectionClassSetWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			// 
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnDirectionClassSetWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].params ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		base_form.findField('EvnDirectionClass_id').focus(true, 250)
	},
	title: lang['vid_issledovaniya'],
	width: 500
});