/**
* swMarkerEditWindow - окно просмотра, добавления и редактирования маркера
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Salakhov R.
* @version      27.01.2012
*/

/*NO PARSE JSON*/
sw.Promed.swMarkerEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMarkerEditWindow',
	objectSrc: '/jscore/Forms/Common/swMarkerEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	title: '',
	draggable: true,
	id: 'swMarkerEditWindow',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	listeners: {
		'hide': function(win) {
			//
		},
		'beforeShow': function(win) {
			if ( !isSuperAdmin() )
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Cуперадминистратор»');
				return false;
			}
		}
	},
	doReset: function() {
		var form = this.formPanel.getForm();		
		form.reset();
        var evnclass_combo = form.findField('EvnClass_id');
        evnclass_combo.getStore().baseParams = {
            withBase: 1
        };
        if (evnclass_combo.getStore().getCount()==0) {
            evnclass_combo.getStore().load({});
        }
		Ext.getCmp('MEW_MarkerDebugResultPanel').body.dom.innerHTML = '';
	},
	doSave: function(callback) {
		var win = this,
			form = this.formPanel.getForm(),
			params = {};

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		win.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();				
				win.action = 'edit';
				var data = {};
				var id = action.result.FreeDocMarker_id;
				form.findField('FreeDocMarker_id').setValue(id);
				data.FreeDocMarker_id = id;
				if(callback) {
					callback(data);
				}
				if(win.owner && win.owner.id == 'MarkerViewFrame') {
					win.owner.refreshRecords(win.owner, id);
				}
			}
		});
	},
	allowEdit: function(is_allow) {
		var win = this,
			form = this.formPanel.getForm(),
			save_btn = this.buttons[0];
			
		form.findField('EvnClass_id').setDisabled(!is_allow);
		form.findField('FreeDocMarker_Name').setDisabled(!is_allow);
		form.findField('FreeDocMarker_TableAlias').setDisabled(!is_allow);
		form.findField('FreeDocMarker_Field').setDisabled(!is_allow);
		form.findField('FreeDocMarker_Query').setDisabled(!is_allow);
		form.findField('FreeDocMarker_Description').setDisabled(!is_allow);    
		form.findField('FreeDocMarker_IsTableValue').setDisabled(!is_allow);
		form.findField('FreeDocMarker_Options').setDisabled(!is_allow);
		
		if (is_allow) {
			save_btn.show();
		} else {
			save_btn.hide();
		}
		this.debugButtonPanel.findById('MEW_ShowTableHeaderButton').setDisabled(!form.findField('FreeDocMarker_IsTableValue').getValue());
	},
	showDebug: function(type) {
		if (this.action == 'view') {
			this.getDebug(this.formPanel.getForm().findField('FreeDocMarker_id').getValue(), type);
		} else {
			this.doSave(function(data){
				this.getDebug(data.FreeDocMarker_id, type);
			}.createDelegate(this));
		}
	},
	getDebug: function(id, type) {
		Ext.getCmp('MEW_MarkerDebugResultPanel').body.dom.innerHTML = '';
		Ext.Ajax.request({
			url: '/?c=FreeDocument&m=getDebugInformation',
			callback: function(opt, success, response) {
				
				if (success && response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success && response_obj.success === true) {
						if (response_obj.data) {
							Ext.getCmp('MEW_MarkerDebugResultPanel').body.dom.innerHTML = response_obj.data;
						}
					}
				}
			},
			params: {
				'type': type,
				'FreeDocMarker_id': id
			}
		});
	},
	initComponent: function() {
		var win = this;
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'MEW_MarkerRecordEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			region: 'north',
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						fieldLabel: lang['klass_sobyitiya'],
						name: 'EvnClass_id',
						id: 'MEW_EvnClass_id',
						width: 150,
						listWidth: 400,
						xtype: 'swevnclasscombo'
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel: lang['nazvanie_markera'],
						name: 'FreeDocMarker_Name',
						id: 'MEW_FreeDocMarker_Name',
						maskRe: new RegExp("^[а-яА-ЯёЁ0-9]*$"),
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel: lang['svyazannyiy_alias'],
						name: 'FreeDocMarker_TableAlias',
						id: 'MEW_FreeDocMarker_TableAlias',
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel: lang['pole'],
						name: 'FreeDocMarker_Field',
						id: 'MEW_FreeDocMarker_Field',
						xtype: 'textfield'
					}]
				}]
			}, {
				anchor: '100%',
				fieldLabel: lang['zapros'],
				name: 'FreeDocMarker_Query',
				id: 'MEW_FreeDocMarker_Query',
				xtype: 'textarea'
			}, {
				anchor: '100%',
				fieldLabel: lang['opisanie_markera'],
				name: 'FreeDocMarker_Description',
				id: 'MEW_FreeDocMarker_Description',
				xtype: 'textarea'
			}, {
				fieldLabel: lang['tablichnyiy_marker'],
				name: 'FreeDocMarker_IsTableValue',
				id: 'MEW_FreeDocMarker_IsTableValue',
				xtype: 'checkbox',
				listeners: {
					check: function(field) {
						this.debugButtonPanel.findById('MEW_ShowTableHeaderButton').setDisabled(!field.checked);
					}.createDelegate(this)
				}
			}, {
				anchor: '100%',
				fieldLabel: lang['dop_nastroyki'],
				name: 'FreeDocMarker_Options',
				id: 'MEW_FreeDocMarker_Options',
				xtype: 'textarea'
			}, {
				name: 'FreeDocMarker_id',
				xtype: 'hidden'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey())  {
						case Ext.EventObject.C:
							if (this.action != 'view') {
								this.doSave();
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: function() { 
					//
				}
			}, 
			[
				{ name: 'FreeDocMarker_id' },
				{ name: 'EvnClass_id' },
				{ name: 'FreeDocMarker_Name' },
				{ name: 'FreeDocMarker_TableAlias' },
				{ name: 'FreeDocMarker_Field' },
				{ name: 'FreeDocMarker_Query' },
				{ name: 'FreeDocMarker_Description' },
				{ name: 'FreeDocMarker_IsTableValue' },
				{ name: 'FreeDocMarker_Options' }
			]),
			timeout: 600,
			url: '/?c=FreeDocument&m=saveFreeDocMarker'
		});		

		this.debugResultPanel = new Ext.Panel({
			width: '100%',
			height: 200,
			autoScroll: true,
			id: 'MEW_MarkerDebugResultPanel',
			bodyStyle: 'margin-top: 5px; padding: 10px; border: 1px #b5bace solid; background-color: #ffffff;',
			border: true,
			html: '111'
		});
		
		this.debugButtonPanel = new Ext.form.FormPanel({
			//autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'MEW_MarkerDebugButtonPanel',
			labelAlign: 'left',
			labelWidth: 120,
			region: 'center',
			layout: 'column',
			items: [{
				layout: 'form',
				bodyStyle:'margin-left:1px;',
				items: [{	
					xtype: 'button',
					id: 'MEW_ShowQueryButton',
					handler: function() {
						win.showDebug('query');
					},
					iconCls: 'save16',
					tabIndex: 20,
					text: lang['pokazat_gotovyiy_zapros']
				}]
			}, {
				layout: 'form',
				bodyStyle:'margin-left:6px;',
				items: [{
					xtype: 'button',
					id: 'MEW_ShowRelationshipsButton',
					bodyStyle:'margin-left:5px;',
					handler: function() {
						win.showDebug('relationships');
					},
					iconCls: 'save16',
					tabIndex: 21,
					text: lang['pokazat_svyazi']
				}]
			}, {
				layout: 'form',
				bodyStyle:'margin-left:6px;',
				items: [{
					xtype: 'button',
					id: 'MEW_ShowTableHeaderButton',
					handler: function() {
						win.showDebug('table_header');
					},
					iconCls: 'save16',
					tabIndex: 22,
					text: lang['pokazat_shapku_tablitsyi']
				}]			
			}, 
			this.debugResultPanel
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_GL + 29,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				onTabElement: 'GREW_Marker_Word',
				tabIndex: TABINDEX_GL + 31,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel,
				this.debugButtonPanel
			]
		});
		sw.Promed.swMarkerEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swMarkerEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) {
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.owner = arguments[0].owner || null;		

		this.doReset();
		this.center();

		var win = this,
			form = this.formPanel.getForm(); 

		form.setValues(arguments[0]);
		switch (this.action) {
			case 'view':
				this.setTitle(lang['prosmotr_marker']);
				break;
			case 'edit':
				this.setTitle(lang['redaktirovanie_marker']);
				break;
			case 'add':
				this.setTitle(lang['dobavlenie_marker']);
				break;
			break;
		}

		if (this.action == 'add') {
			win.allowEdit(true);
			this.syncSize();
			this.doLayout();
		} else {
			win.allowEdit(false);
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
				},
				params: {
					FreeDocMarker_id: form.findField('FreeDocMarker_id').getValue()
				},
				success: function(form, action) {					
					win.getLoadMask().hide();
					form.findField('FreeDocMarker_IsTableValue').setValue(action.result.data.FreeDocMarker_IsTableValue ? true : false)
					if(win.action == 'edit') {
						win.allowEdit(true);
					}
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=FreeDocument&m=getFreeDocMarkerData'
			});
		}
	}
});
