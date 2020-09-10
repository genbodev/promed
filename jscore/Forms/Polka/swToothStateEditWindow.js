/**
* swToothStateEditWindow - редактирование состояния зуба
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Stanislav Bykov aka Savage (savage@swan.perm.ru)
* @version      15.08.2013
* @comment      Префикс для id компонентов TSEW (ToothStateEditWindow)
*
*
* Использует: -
*/

sw.Promed.swToothStateEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	clearToothStateClassGrid: function() {
		this.ToothStateClassGrid.getGrid().getStore().each(function(rec) {
			rec.set('ToothStateClass_IsSet', false);
			rec.commit();
		});
	},
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {
		if ( this.action == 'view' ) {
			return false;
		}
		else if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();

		var toothStateValues = new Array();
		
		// Собираем данные из грида
		this.ToothStateClassGrid.getGrid().getStore().each(function(rec) {
			if ( rec.get('ToothStateClass_IsSet') == true ) {
				toothStateValues.push(rec.get('ToothStateClass_id'));
			}
		});

		var ToothType_Code = 0;
		var ToothType_id = base_form.findField('ToothType_id').getValue();
		var ToothType_Name = '';

		var index = base_form.findField('ToothType_id').getStore().findBy(function(rec) {
			return (rec.get('ToothType_id') == ToothType_id);
		});

		if ( index >= 0 ) {
			ToothType_Code =  base_form.findField('ToothType_id').getStore().getAt(index).get('ToothType_Code');
			ToothType_Name =  base_form.findField('ToothType_id').getStore().getAt(index).get('ToothType_Name');
		}
		
		data.toothStateData = {
			'Tooth_Num': base_form.findField('Tooth_Num').getValue(),
			'ToothCard_id': base_form.findField('ToothCard_id').getValue(),
			'ToothType_id': ToothType_id,
			'ToothType_Code': ToothType_Code,
			'ToothType_Name': ToothType_Name,
			'ToothState_Values': Ext.util.JSON.encode(toothStateValues)
		};

		this.formStatus = 'edit';
		loadMask.hide();

		this.callback(data);
		this.hide();
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();

		if ( enable ) {
			base_form.findField('ToothType_id').enable();
			this.ToothStateClassGrid.enable();
		}
		else {
			base_form.findField('ToothType_id').disable();
			this.ToothStateClassGrid.disable();
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'ToothStateEditWindow',
	initComponent: function() {
		this.ToothStateClassGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print', disabled: true, hidden: true },
				{ name: 'action_save', disabled: true, hidden: true }
			],
			autoLoadData: false,
			dataUrl: '/?c=MongoDBWork&m=getData',
			height: 300,
			id: 'TSEW_ToothStateClassGrid',
			onAfterEdit: function(o) {
				if ( o && o.field ) {
					if ( o.record.get('ToothStateClass_Code').inlist([ lang['p1'], lang['p2'], lang['p3'] ]) && o.value == true ) {
						this.getGrid().getStore().each(function(rec) {
							if ( rec.get('ToothStateClass_Code').inlist([ lang['p1'], lang['p2'], lang['p3'] ]) && rec.get('ToothStateClass_Code') != o.record.get('ToothStateClass_Code') ) {
								rec.set('ToothStateClass_IsSet', false);
								rec.commit();
							}
						});
					}
				}
			},
			onLoadData: function() {
				this.doLayout();
			},
			region: 'center',
			saveAllParams: false, 
			saveAtOnce: false, 
			stringfields: [
				{ name: 'ToothStateClass_id', type: 'int', header: 'ID', key: true },
				{ name: 'ToothStateClass_Code', type: 'string', hidden: true },
				{ name: 'ToothStateClass_Name', type: 'string', sortable: false, header: lang['parametr'], id: 'autoexpand' },
				{ name: 'ToothStateClass_IsSet', sortable: false, type: 'checkcolumnedit', isparams: true, header: lang['uslovie'], width: 100 }
			],
			toolbar: false
		});

		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'ToothStateEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Tooth_Num' },
				{ name: 'ToothCard_id' },
				{ name: 'ToothType_id' }
			]),
			url: '/?c=EvnVizit&m=saveToothState',
			items: [{
				name: 'ToothCard_id',
				value: 0,
				xtype: 'hidden'
			}, {
				disabled: true,
				fieldLabel: lang['nomer_zuba'],
				name: 'Tooth_Num',
				width: 70,
				xtype: 'numberfield'
			}, {
				comboSubject: 'ToothType',
				fieldLabel: lang['tip_zuba'],
				hiddenName: 'ToothType_id',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index));
					},
					'select': function(combo, record, index) {
						var base_form = this.FormPanel.getForm();

						this.clearToothStateClassGrid();

						// Показываем или скрываем поле "Тип зуба" и грид "Состояник" в зависимости от выбранного значения
						if ( typeof record == 'object' ) {
							switch ( record.get('ToothType_Code').toString() ) {
								case '1':
								case '2':
									if ( this.action == 'edit' ) {
										this.ToothStateClassGrid.enable();
									}

									this.setToothStateClassGrid(); // Проставляем значения из this.ToothState_Values
								break;

								default:
									this.ToothStateClassGrid.disable();
								break;
							}
						}
						else {
							this.ToothStateClassGrid.disable();
						}
					}.createDelegate(this)
				},
				width: 150,
				xtype: 'swcommonsprcombo'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				 this.FormPanel
				,this.ToothStateClassGrid
			]
		});

		sw.Promed.swToothStateEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('ToothStateEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
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
	setToothStateClassGrid: function() {
		if ( typeof this.ToothState_Values == 'object' && this.ToothState_Values.length > 0 ) {
			this.ToothStateClassGrid.getGrid().getStore().each(function(rec) {
				if ( rec.get('ToothStateClass_id').inlist(this.ToothState_Values) ) {
					rec.set('ToothStateClass_IsSet', true);
					rec.commit();
				}
			}.createDelegate(this));
		}
	},
	show: function() {
		sw.Promed.swToothStateEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = 'edit';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.ToothState_Values = new Array();

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action && arguments[0].action == 'view' ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].ToothState_Values ) {
			this.ToothState_Values = Ext.util.JSON.decode(arguments[0].ToothState_Values);
		}

		this.enableEdit(this.action == 'edit');

		base_form.findField('ToothType_id').getStore().clearFilter();

		if ( !Ext.isEmpty(base_form.findField('Tooth_Num').getValue()) && parseInt(base_form.findField('Tooth_Num').getValue().toString().substr(1, 1)) > 5 ) {
			base_form.findField('ToothType_id').getStore().filterBy(function(rec) {
				return (rec.get('ToothType_Code').toString().inlist([ '1', '3', '4']));
			});
		}

		if ( this.ToothStateClassGrid.getGrid().getStore().getCount() == 0 ) {
			this.ToothStateClassGrid.getGrid().getStore().load({
				callback: function() {
					base_form.findField('ToothType_id').fireEvent('change', base_form.findField('ToothType_id'), base_form.findField('ToothType_id').getValue());
				}.createDelegate(this),
				params: {
					object: "ToothStateClass"
				}
			});
		}
		else {
			base_form.findField('ToothType_id').fireEvent('change', base_form.findField('ToothType_id'), base_form.findField('ToothType_id').getValue());
		}

		if ( this.action == 'edit' ) {
			base_form.findField('ToothType_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	title: lang['sostoyanie_zuba'],
	width: 400
});