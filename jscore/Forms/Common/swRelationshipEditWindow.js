/**
* swRelationshipEditWindow - окно просмотра, добавления и редактирования связи
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Salakhov R.
* @version      03.02.2012
*/

/*NO PARSE JSON*/
sw.Promed.swRelationshipEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swRelationshipEditWindow',
	objectSrc: '/jscore/Forms/Common/swRelationshipEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	title: '',
	draggable: true,
	id: 'swRelationshipEditWindow',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
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
		Ext.getCmp('REW_RelationshipDebugResultPanel').body.dom.innerHTML = '';
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
				var id = action.result.FreeDocRelationship_id;
				form.findField('FreeDocRelationship_id').setValue(id);
				data.FreeDocRelationship_id = id;
				if(callback) {
					callback(data);
				}
				if(win.owner && win.owner.id == 'RelationshipViewFrame') {
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
		form.findField('FreeDocRelationship_AliasName').setDisabled(!is_allow);
		form.findField('FreeDocRelationship_AliasTable').setDisabled(!is_allow);
		form.findField('FreeDocRelationship_AliasQuery').setDisabled(!is_allow);
		form.findField('FreeDocRelationship_LinkedAlias').setDisabled(!is_allow);
		form.findField('FreeDocRelationship_LinkDescription').setDisabled(!is_allow);

		if (is_allow) {
			save_btn.show();
		} else {
			save_btn.hide();
		}		
	},
	showDebug: function(type) {
		if (this.action == 'view') {
			this.getDebug(this.formPanel.getForm().findField('FreeDocRelationship_id').getValue(), type);
		} else {
			this.doSave(function(data){
				this.getDebug(data.FreeDocRelationship_id, type);
			}.createDelegate(this));
		}
	},
	getDebug: function(id, type) {
		Ext.getCmp('REW_RelationshipDebugResultPanel').body.dom.innerHTML = '';
		Ext.Ajax.request({
			url: '/?c=FreeDocument&m=getDebugInformation',
			callback: function(opt, success, response) {
				
				if (success && response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success && response_obj.success === true) {
						if (response_obj.data) {
							Ext.getCmp('REW_RelationshipDebugResultPanel').body.dom.innerHTML = response_obj.data;
						}
					}
				}
			},
			params: {
				'type': type,
				'FreeDocRelationship_id': id
			}
		});
	},
	initComponent: function() {
		var win = this;
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'REW_RelationshipRecordEditForm',
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
						id: 'REW_EvnClass_id',
						width: 150,
						listWidth: 400,
						xtype: 'swevnclasscombo'
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel: lang['alias_svyazi'],
						name: 'FreeDocRelationship_AliasName',
						id: 'REW_FreeDocRelationship_AliasName',
						maskRe: new RegExp("^[a-zA-Z_0-9]*$"),
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel: lang['tablitsa'],
						name: 'FreeDocRelationship_AliasTable',
						id: 'REW_FreeDocRelationship_AliasTable',
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel: lang['svyazannyiy_alias'],
						name: 'FreeDocRelationship_LinkedAlias',
						id: 'REW_FreeDocRelationship_LinkedAlias',
						xtype: 'textfield'
					}]
				}]
			}, {
				anchor: '100%',
				fieldLabel: lang['zapros'],
				name: 'FreeDocRelationship_AliasQuery',
				id: 'REW_FreeDocRelationship_AliasQuery',
				xtype: 'textarea'
			}, {
				anchor: '100%',
				fieldLabel: lang['opisanie_svyazi'],
				name: 'FreeDocRelationship_LinkDescription',
				id: 'REW_FreeDocRelationship_LinkDescription',
				xtype: 'textarea'
			}, {
				name: 'FreeDocRelationship_id',
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
				{ name: 'FreeDocRelationship_id' },
				{ name: 'EvnClass_id' },
				{ name: 'FreeDocRelationship_AliasName' },
				{ name: 'FreeDocRelationship_AliasTable' },
				{ name: 'FreeDocRelationship_AliasQuery' },
				{ name: 'FreeDocRelationship_LinkedAlias' },
				{ name: 'FreeDocRelationship_LinkDescription' }
			]),
			timeout: 600,
			url: '/?c=FreeDocument&m=saveFreeDocRelationship'
		});		

		this.debugResultPanel = new Ext.Panel({
			width: '100%',
			height: 200,
			autoScroll: true,
			id: 'REW_RelationshipDebugResultPanel',
			bodyStyle: 'margin-top: 5px; padding: 10px; border: 1px #b5bace solid; background-color: #ffffff;',
			border: true,
			html: '111'
		});
		
		this.debugButtonPanel = new Ext.form.FormPanel({
			//autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'REW_RelationshipDebugButtonPanel',
			labelAlign: 'left',
			labelWidth: 120,
			region: 'center',
			layout: 'column',
			items: [{
				layout: 'form',
				bodyStyle:'margin-left:1px;',
				items: [{	
					xtype: 'button',
					id: 'REW_ShowQueryButton',
					handler: function() {
						win.showDebug('query_section_from');
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
					id: 'REW_ShowRelationshipsButton',
					bodyStyle:'margin-left:5px;',
					handler: function() {
						win.showDebug('relationships');
					},
					iconCls: 'save16',
					tabIndex: 21,
					text: lang['pokazat_svyazi']
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
				onTabElement: 'GREW_Relationship_Word',
				tabIndex: TABINDEX_GL + 31,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel,
				this.debugButtonPanel
			]
		});
		sw.Promed.swRelationshipEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swRelationshipEditWindow.superclass.show.apply(this, arguments);
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
				this.setTitle(lang['prosmotr_svyaz']);
				break;
			case 'edit':
				this.setTitle(lang['redaktirovanie_svyaz']);
				break;
			case 'add':
				this.setTitle(lang['dobavlenie_svyaz']);
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
					FreeDocRelationship_id: form.findField('FreeDocRelationship_id').getValue()
				},
				success: function(form, action) {					
					win.getLoadMask().hide();					
					if(win.action == 'edit') {
						win.allowEdit(true);
					}
					win.syncSize();
					win.doLayout();
				},
				url: '/?c=FreeDocument&m=getFreeDocRelationshipData'
			});
		}
	}
});
