/**
* swParameterValueEditWindow - форма редактирования параметра и значений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Alexander Permyakov (alexpm)
* @version      07.2013
* @comment      
*/

/*NO PARSE JSON*/
sw.Promed.swParameterValueEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swParameterValueEditWindow',
	objectSrc: '/jscore/Forms/Common/swParameterValueEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swParameterValueEditWindow',
	width: 600,
	height: 457,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm(),
			grid = this.viewFrame.getGrid();
		form.reset();
		grid.getStore().baseParams = {};
		this.viewFrame.removeAll(true);
		this.viewFrame.ViewGridPanel.getStore().removeAll();
	},
	submit: function() {
		var win = this,
			form = this.formPanel.getForm(),
			grid = this.viewFrame.getGrid();

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
        form.findField('values_change').setValue(this.viewFrame.getChangedJsonStr());

		win.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
                form.findField('ParameterValue_id').setValue(action.result.ParameterValue_id);
                form.findField('ParameterValue_Alias').setValue(action.result.ParameterValue_Alias);
                form.findField('ParameterValue_SysNick').setValue(action.result.ParameterValue_SysNick);
				var data = form.getValues();
                data.ParameterValueListType_Name = form.findField('ParameterValueListType_id').getRawValue();
                data.ParameterValue_Marker = action.result.ParameterValue_Marker;
                data.ParameterValue_valueCnt = grid.getStore().getCount();
                data.accessType = 'edit';
				win.callback(data);
			}
		});
	},
	setFormDisable: function(config) {
		var win = this,
			form = this.formPanel.getForm(),
			save_btn = this.buttons[0],
			n;
		
		if (!config) {
			config = {allowAll: true, focusField: this.fieldNames[0]};
		}
		
		if (config.allowAll) {
			for ( var i = 0 ; i < this.fieldNames.length ; i++ ) {
				n = this.fieldNames[i];
				form.findField(n).setDisabled(false);
			}
			this.viewFrame.setReadOnly(false); 
			save_btn.show();
		}
		
		if (config.disableAll) {
			for ( var i = 0 ; i < this.fieldNames.length ; i++ ) {
				n = this.fieldNames[i];
				form.findField(n).setDisabled(true);
			}
			this.viewFrame.setReadOnly(true); 
			save_btn.hide();
		}

		if (config.allowExcept) {
			for ( var i = 0 ; i < config.allowExcept.length ; i++ ) {
				n = config.allowExcept[i];
				form.findField(n).setDisabled(true);
			}
		}
		
		if (config.focusField) {
			form.findField(config.focusField).focus(true, 250);
		} else if (config.indexFocusButton) {
			this.buttons[config.indexFocusButton].focus(true, 250);
		}
	},
	editStore: function(a) {
		var win = this,
			form = this.formPanel.getForm(),
			grid = win.viewFrame.getGrid(),
			record = grid.getSelectionModel().getSelected();
        if(this.action == 'view')
        {
            return false;
        }
        if(a != 'addValue' && !record)
        {
            sw.swMsg.alert(lang['soobschenie'], lang['vyi_nichego_ne_vyibrali']);
            return false;
        }

		switch (a) {
			case 'editValue':
                sw.swMsg.prompt(lang['redaktirovanie_znacheniya'],
                    lang['vvedite_novoe_znachenie_parametra'],
                    function(btnId, newValue){
                        if (btnId != 'ok') {
                            return false;
                        }
                        record.set('ParameterValue_Name', newValue);
                        var status = 'changed';
                        if (!record.get('ParameterValue_Name')) {
                            status = 'deleted';
                        } else if (!record.get('ParameterValue_id')) {
                            status = 'inserted';
                        }
                        record.set('ParameterValue_status', status);
                        record.commit();
                        win.viewFrame.onAfterEditStore();
                        return true;
                    },
                    this,
                    false,
                    record.get('ParameterValue_Name')
                );
                break;

			case 'deleteValue':
				record.set('ParameterValue_status', 'deleted');
				record.commit();
				win.viewFrame.onAfterEditStore();
			break;

			case 'addValue':
                sw.swMsg.prompt(lang['dobavlenie_znacheniya'],
                    lang['vvedite_novoe_znachenie_parametra'],
                    function(btnId, newValue){
                        if (btnId != 'ok' || !newValue) {
                            return false;
                        }
                        record = new Ext.data.Record({
                            ParameterValue_id: null
                            ,ParameterValue_pid: form.findField('ParameterValue_id').getValue()
                            ,ParameterValue_Name: newValue
                            ,ParameterValue_status: 'inserted'
                        });
                        grid.getStore().add([record]);
                        grid.getStore().commitChanges();
                        var i = grid.getStore().indexOf(record);
                        grid.getSelectionModel().selectRow(i);
                        grid.getView().focusRow(i);
                        win.viewFrame.setActionDisabled('action_edit',false);
                        win.viewFrame.setActionDisabled('action_delete',false);
                        return true;
                    },
                    this,
                    false,
                    ''
                );
                break;
			default:
				return false;
			break;
		}
        return true;
	},

	initComponent: function() {
		var win = this;
		this.fieldNames = ['ParameterValue_Alias','ParameterValue_Name',
            'ParameterValueListType_id','XmlTemplateScope_id','XmlTemplateScope_eid'];
        this.accessRightsPanel = new sw.Promed.XmlTemplateScopePanel({
            object: 'ParameterValue',
            tabIndexStart: TABINDEX_ETSW+25
        });
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'left',
			labelWidth: 170,
			region: 'north',
			items: [{
                anchor: '100%',
                allowBlank: false,
                fieldLabel: lang['naimenovanie_parametra'],
                name: 'ParameterValue_Alias',
                maskRe: new RegExp("^[а-яА-ЯёЁ]*$"),
                maxLength: 100,
                xtype: 'textfield'
            }, {
                anchor: '100%',
                allowBlank: false,
                fieldLabel: lang['naimenovanie_dlya_pechati'],
                name: 'ParameterValue_Name',
                maxLength: 400,
                xtype: 'textfield'
            }, {
				anchor: '100%',
				allowBlank: false,
				fieldLabel: lang['tip_spiska_znacheniy'],
				comboSubject: 'ParameterValueListType',
				allowSysNick: true,
				typeCode: 'int',
				autoLoad: false,
				enableKeyEvents: true,
				xtype: 'swcommonsprcombo'
            }, this.accessRightsPanel, {
                name: 'ParameterValue_SysNick',
                xtype: 'hidden'
            }, {
                name: 'values_change',
                xtype: 'hidden'
            }, {
				name: 'ParameterValue_id',
				xtype: 'hidden'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						/*case Ext.EventObject.ENTER:
					log(inp);
					log(e.getKey());
					log(Ext.EventObject.ENTER);
						if(win.viewFrame.getGrid().getSelectionModel().getSelected()) {
							win.editStore('editValue');
						} else {
							win.editStore('addValue');
						}
							break;*/
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.submit();
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
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
                { name: 'ParameterValue_Alias' },
                { name: 'ParameterValue_Name' },
				{ name: 'ParameterValue_SysNick' },
				{ name: 'ParameterValueListType_id' },
                { name: 'XmlTemplateScope_id' },
                { name: 'XmlTemplateScope_eid' },
				{ name: 'LpuSection_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuSection_Name' },
				{ name: 'Lpu_Name' },
				{ name: 'PMUser_Name' },
                { name: 'values_change' },
				{ name: 'ParameterValue_id' }
			]),
			timeout: 600,
			url: '/?c=ParameterValue&m=doSave'
		});

		this.viewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=ParameterValue&m=loadValueGrid',
			object: 'ParameterValue',
			actions:
			[
				{name:'action_add', handler: function(){ win.editStore('addValue'); } },
				{name:'action_edit', handler: function(){ win.editStore('editValue'); } },
				{name:'action_view', hidden: true },
				{name:'action_delete', handler: function(){ win.editStore('deleteValue'); }},
				{name: 'action_refresh', disabled: true, hidden: true},
				{name: 'action_print', disabled: true, hidden: true}
			],
			/*editing: true,
			onAfterEditSelf: function(o) {
				//o.record.commit();
				win.editStore('editValue');
			},*/
			region: 'center',
			stringfields: [
				{ header: 'ID', type: 'int', name: 'ParameterValue_id', key: true },
				/*{ header: lang['znachenie'],  type: 'string', name: 'ParameterValue_Alias', width: 510, editor: new Ext.form.TextField({
					allowBlank: false,
					width: 500
				})},*/
				{ header: lang['znachenie'],  type: 'string', name: 'ParameterValue_Name', id: 'autoexpand' },
				{ header: lang['parametr'],  type: 'int', name: 'ParameterValue_pid', hidden: true },
				{ header: lang['sostoyanie'],  type: 'string', name: 'ParameterValue_status', hidden: true } // 'saved','inserted','deleted','changed'
			],
			onAfterEditStore: function(){
				var gr = this.ViewGridPanel;
				gr.getStore().clearFilter();
				gr.getStore().filterBy(function(record){
					return (record.get('ParameterValue_status') != 'deleted');
				});
			},
			//возвращает новые и измененные значения в виде закодированной JSON строки
			getChangedJsonStr: function(){ 
				var gr = this.ViewGridPanel;
				var data = [];
				gr.getStore().clearFilter();
				gr.getStore().each(function(record) {
					//if (record.get('ParameterValue_status') != 'saved')
						data.push(record.data);
				});
				this.onAfterEditStore();
				return data.length > 0 ? Ext.util.JSON.encode(data) : '';
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				this.onEnter();
			},
			onEnter: function()
			{
				this.runAction('action_edit');
			},
			toolbar: true
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.submit();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				},
                onTabAction: function() {
                    win.formPanel.getForm().findField('ParameterValue_Alias').focus();
                },
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel,
				this.viewFrame
			]
		});
		sw.Promed.swParameterValueEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swParameterValueEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) {
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;

		this.doReset();
		this.center();

		var win = this,
			form = this.formPanel.getForm();

		form.setValues(arguments[0]);
		switch (this.action) {
			case 'view':
				this.setTitle(lang['prosmotr_parametra']);
				this.setFormDisable({disableAll: true, indexFocusButton: 3});
			break;

			case 'edit':
				this.setTitle(lang['redaktirovanie_parametra']);
				this.setFormDisable({allowAll: true, focusField: 'ParameterValue_Alias'});
			break;

			case 'add':
				this.setTitle(lang['dobavlenie_parametra']);
				this.setFormDisable({allowAll: true, focusField: 'ParameterValue_Alias'});
			break;

			default:
				log('swParameterValueEditWindow - action invalid');
				return false;
			break;
		}

        var onLoadForm = function() {
            win.accessRightsPanel.onLoadForm(form, win.action);
            win.syncSize();
            win.doLayout();
        };
		
		if (win.action == 'add') {
            onLoadForm();
            win.viewFrame.removeAll(true);
		} else {
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
            win.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
				},
				params: {
					ParameterValue_id: form.findField('ParameterValue_id').getValue()
				},
				success: function() {
					win.getLoadMask().hide();
                    onLoadForm();
                    win.viewFrame.removeAll(true);
                    //win.viewFrame.loadData({globalFilters: { ParameterValue_id: form.findField('ParameterValue_id').getValue() }});
                    var values = Ext.util.JSON.decode(form.findField('values_change').getValue());
                    win.viewFrame.getGrid().getStore().loadData(values, true);
				},
				url: '/?c=ParameterValue&m=doLoadEditForm'
			});
		}
	}
});
