/**
* swMongoCacheEditWindow - окно добавления/редактирования информации об объекте кэша
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright	Copyright (c) 2014 Swan Ltd.
* @author		Марков Андрей <markov@swan.perm.ru>
* @version		07.2014
*/

sw.Promed.swMongoCacheEditWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 700,
	modal: true,
	autoHeight: true,
	plain: true,
	resizable: false,
	title: '',
	action: null,
	callback: Ext.emptyFn,
	id: 'MongoCacheEditWindow',
	listeners: {
		hide: function() {}
	},
	show: function() {
		sw.Promed.swMongoCacheEditWindow.superclass.show.apply(this, arguments);
		var bf = this.Form.getForm();
		bf.reset();
		
		if( !arguments[0] || !arguments[0].action ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], this.hide.createDelegate(this, []));
			return;
		}
		
		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		
		this.action = arguments[0].action;
		this.buttons[0].setDisabled(this.action === 'view');
		if (this.action != 'view') {
			bf.setValues(arguments[0]);
		}
		this.setTitle(lang['mongodb_kesh'] + this._getActionName(this.action) +' '+ lang['zapisi']);
		
		// Если кэш автоматический, то редактировать SQL-запрос нельзя
		bf.findField('sysCache_auto').fireEvent('check', bf.findField('sysCache_auto'), bf.findField('sysCache_auto').getValue());
	},
	_getActionName: function(name) {
		return {
			add: lang['_dobavlenie'],
			edit: lang['_redaktirovanie'],
			view: lang['_prosmotr']
		}[name];
	},
	doSave: function() {
		var form = this.Form;
		
		if (!form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if (!form.getForm().findField('sysCache_auto').getValue() && form.getForm().findField('sysCache_sql').getValue().length==0) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getForm().findField('sysCache_sql').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['esli_kesh_ne_avtomaticheskiy_nujno_vvesti_tekst_zaprosa'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.getForm().submit({
			scope: this,
			success: function(f, a) {
				this.hide();
				this.callback(this.owner, 1);
			},
			failure: function(f, a) {}
		});
	},

	initComponent: function() {
		this.Form = new Ext.form.FormPanel({
			frame: true,
			autoScroll: true,
			url: '/?c=MongoCache&m=saveMongoCache',
			layout: 'form',
			height: 420,
			labelAlign: 'right',
			labelWidth: 140,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			items: [{
				name: 'sysCache_id',
				value: null,
				xtype: 'hidden'
			}, {
				fieldLabel: lang['naimenovanie'],
				xtype: 'textfield',
				allowBlank: false,
				autoCreate: {tag: "input", maxLength: "80", autocomplete: "off"},
				anchor: '100%',
				name: 'sysCache_name',
				tabIndex: TABINDEX_MCEW + 1
			}, {
				fieldLabel: lang['obyekt_bd'],
				xtype: 'textfield',
				allowBlank: false,
				autoCreate: {tag: "input", maxLength: "80", autocomplete: "off"},
				anchor: '100%',
				name: 'sysCache_object',
				tabIndex: TABINDEX_MCEW + 2
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					labelWidth: 140,
					items: [{
						fieldLabel: lang['aktualnost_kesha'],
						xtype: 'numberfield',
						allowBlank: false,
						minValue: 0,
						maxValue: 999,
						autoCreate: {tag: "input", size:14, autocomplete: "off"},
						width: '100',
						name: 'sysCache_ttl',
						tabIndex: TABINDEX_MCEW + 3
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 100,
					items: [{
						bodyStyle: 'padding: 4px',
						html: lang['v_sek_0_-_aktualen_vsegda']
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 80,
					items: [{
						xtype: 'checkbox',
						name: 'sysCache_auto',
						labelSeparator: '',
						boxLabel: lang['avtomaticheskiy_kesh'],
						listeners: {
							'check': function(checkbox, value) {
								var bf = this.Form.getForm();
								// Блокируем поля, если кэш автоматический
								bf.findField('sysCache_name').setDisabled(value);
								bf.findField('sysCache_object').setDisabled(value);
								bf.findField('sysCache_ttl').setDisabled(value);
								bf.findField('sysCache_sql').setDisabled(value);
								this.buttons[0].setDisabled(value);
							}.createDelegate(this)
						}

					}]
				}]
			}, {
				anchor: '100% 80%',
				hideLabel: true,
				name: 'sysCache_sql',
				defaultValue: '',
				xtype: 'textarea',
				tabIndex: TABINDEX_MCEW + 4
			}],
			reader: new Ext.data.JsonReader({
				success: function() {}
			},
			[
				{name: 'sysCache_id'},
				{name: 'sysCache_name'},
				{name: 'sysCache_object'},
				{name: 'sysCache_auto'}, 
				{name: 'sysCache_ttl'}, 
				{name: 'sysCache_sql'}
			])
		});
		
    	Ext.apply(this, {
			layout: 'fit',
			items: [this.Form],
			buttons: [{
				handler: this.doSave.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			}, 
			HelpButton(this), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: this.hide.createDelegate(this, [])
			}],
			buttonAlign: 'right'
		});
		sw.Promed.swMongoCacheEditWindow.superclass.initComponent.apply(this, arguments);
	}
});