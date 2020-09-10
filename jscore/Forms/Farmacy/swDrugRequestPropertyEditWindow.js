/**
* swDrugRequestPropertyEditWindow - окно редактирования переченя списков медикаментов для заявки 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      17.09.2012
* @comment      
*/
sw.Promed.swDrugRequestPropertyEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spisok_medikamentov_dlya_zayavki_redaktirovanie'],
	layout: 'border',
	id: 'DrugRequestPropertyEditWindow',
	modal: true,
	shim: false,
	width: 605,
	height: 288,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	editName: false, //флаг true если имя перечня уже редактировалось пользователем. Значени true может принимать только при добавлении нового списка.
	setDefaultName: function() {
		var wnd = this;
		if (!wnd.editName) {
			var person_register = wnd.getSelectedValueName('PersonRegisterType');
			var period = wnd.getSelectedValueName('DrugRequestPeriod');
			var finance = wnd.getSelectedValueName('DrugFinance');
			var drug_group = wnd.getSelectedValueName('DrugGroup');

			var str = lang['spisok_medikamentov'];

			if (!Ext.isEmpty(person_register)) {
				str += ' ' + lang['po'] + ' ' + person_register;
			}
			if (!Ext.isEmpty(period)) {
				str += ' ' + lang['na'] + ' ' + period;
			}
			if (!Ext.isEmpty(finance)) {
				str += ', ' + finance;
			}
			if (!Ext.isEmpty(drug_group)) {
				str += ', ' + drug_group;
			}

			wnd.form.findField('DrugRequestProperty_Name').setValue(str);
		}
	},
	setDisabled: function(disable) {
		var wnd = this;
		var form = wnd.form;		
		
		if (disable) {
			form.findField('DrugRequestPeriod_id').disable();
			form.findField('PersonRegisterType_id').disable();
			form.findField('DrugFinance_id').disable();
			form.findField('DrugGroup_id').disable();
			form.findField('Org_id').disable();
			form.findField('DrugRequestProperty_Name').disable();
			wnd.buttons[0].disable();
		} else {
			form.findField('DrugRequestPeriod_id').enable();
			form.findField('PersonRegisterType_id').enable();
			form.findField('DrugFinance_id').enable();
			form.findField('DrugGroup_id').enable();
			form.findField('Org_id').enable();
			form.findField('DrugRequestProperty_Name').enable();
			wnd.buttons[0].enable();
		}
	},
	doSave:  function() {
		var wnd = this;
		if (!this.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('slewDrugRequestPropertyEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        wnd.submit();
		return true;		
	},
	submit: function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = wnd.action;
		params.DrugRequestProperty_id = wnd.DrugRequestProperty_id;
		params.OriginalDrugRequestProperty_id = wnd.OriginalDrugRequestProperty_id;
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				var id = 0;
				if (action.result && action.result.DrugRequestProperty_id > 0) {
					id = action.result.DrugRequestProperty_id;
				}
				loadMask.hide();
				wnd.callback(wnd.owner, id);
				wnd.hide();
			}
		});
	},
	getSelectedValueName: function(object_name) { //возвращает имя выбранного значения в комбобоксе
		var value_name = null;
		var combo = this.form.findField(object_name+'_id');

		if (combo && !Ext.isEmpty(combo.getValue())) {
			var record = combo.getStore().getById(combo.getValue());
			if (record && !Ext.isEmpty(record.get(object_name+'_Name'))) {
				value_name = record.get(object_name+'_Name');
			}
		}
		return value_name;
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRequestPropertyEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugRequestProperty_id = null;
		this.OriginalDrugRequestProperty_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		this.action = (arguments[0].action) ? arguments[0].action : 'add';
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
			if (this.action == 'edit') {
				var record = this.owner.getGrid().getSelectionModel().getSelected();
				if (record) {
					if (record.get('Mnn_Count') > 0)
						this.action = 'view';
				}
			}
		}
		if ( arguments[0].DrugRequestProperty_id ) {
			this.DrugRequestProperty_id = arguments[0].DrugRequestProperty_id;
		}
		if ( arguments[0].OriginalDrugRequestProperty_id ) {
			this.OriginalDrugRequestProperty_id = arguments[0].OriginalDrugRequestProperty_id;
		}
		
		this.form.reset();
		this.form.findField('DrugFinance_id').unsetFilter();
		this.editName = (this.action != 'add');		
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		wnd.setTitle(lang['spisok_medikamentov_dlya_zayavki']);
		switch (this.action) {
			case 'add':
				wnd.setDisabled(false);
				wnd.setTitle(wnd.title+lang['_dobavlenie']+(this.OriginalDrugRequestProperty_id > 0 ? lang['kopii'] : ''));
				loadMask.hide();
			break;
			case 'edit':				
			case 'view':
				wnd.setTitle(wnd.title+(wnd.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						DrugRequestProperty_id: wnd.DrugRequestProperty_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
							wnd.form.setValues(result[0]);

							var period_idx = wnd.form.findField('DrugRequestPeriod_id').getStore().findBy(function(rec) { return rec.get('DrugRequestPeriod_id') == result[0].DrugRequestPeriod_id; });
							if (period_idx > -1) {
								var period_rec = wnd.form.findField('DrugRequestPeriod_id').getStore().getAt(period_idx);
								wnd.form.findField('DrugFinance_id').setDateFilter({
									begDate: period_rec.get('DrugRequestPeriod_begDate'),
									endDate: period_rec.get('DrugRequestPeriod_endDate')
								});
							}

							if (result[0].DrugRequestProperty_BegDT && result[0].DrugRequestProperty_EndDT) {
								wnd.dateMenu.setValue(result[0].DrugRequestProperty_BegDT + ' - ' + result[0].DrugRequestProperty_EndDT);
							}

							if (result[0].Org_id && result[0].Org_id > 0) {
								wnd.org_combo.setValueById(result[0].Org_id);
							}
						}
						wnd.setDisabled(wnd.action == 'view');
						loadMask.hide();
					},
					url:'/?c=DrugRequestProperty&m=load'
				});		
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;

		this.org_combo = new sw.Promed.SwBaseRemoteCombo ({
			fieldLabel: lang['organizatsiya'],
			hiddenName: 'Org_id',
			displayField: 'Org_Name',
			valueField: 'Org_id',
			allowBlank: true,
			editable: true,
			anchor: '100%',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'{Org_Name}&nbsp;',
				'</div></tpl>'
			),
			store: new Ext.data.SimpleStore({
				autoLoad: false,
				fields: [
					{ name: 'Org_id', mapping: 'Org_id' },
					{ name: 'Org_Name', mapping: 'Org_Name' }
				],
				key: 'Org_id',
				sortInfo: { field: 'Org_Name' },
				url:'/?c=DrugRequestProperty&m=loadOrgCombo'
			}),
			onTrigger2Click: function() {
				var combo = this;

				if (combo.disabled) {
					return false;
				}

				combo.clearValue();
				combo.lastQuery = '';
				combo.getStore().removeAll();
				combo.getStore().baseParams.query = '';
				combo.fireEvent('change', combo, null);
			},
			setValueById: function(id) {
				var combo = this;
				combo.store.baseParams.Org_id = id;
				combo.store.load({
					callback: function(){
						combo.setValue(id);
						combo.store.baseParams.Org_id = null;
					}
				});
			}
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'slewDrugRequestPropertyEditForm',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: false,
				labelWidth: 170,
				collapsible: true,
				url:'/?c=DrugRequestProperty&m=save',
				items: [{
					name: 'DrugRequestProperty_id',
					xtype: 'hidden'
				}, {
					fieldLabel: lang['rabochiy_period'],
					hiddenName: 'DrugRequestPeriod_id',
					xtype: 'swdynamicdrugrequestperiodcombo',
					anchor: '100%',
					allowBlank: true,
					listeners: {
						'select': function(obj, rec, idx) {
							wnd.setDefaultName();
							wnd.form.findField('DrugFinance_id').setDateFilter({
								begDate: rec.get('DrugRequestPeriod_begDate'),
								endDate: rec.get('DrugRequestPeriod_endDate')
							});
						}
					}
				}, {
					fieldLabel: lang['tip_spiska'],
					comboSubject: 'PersonRegisterType',
					id: 'drpePersonRegisterType_id',
					name: 'PersonRegisterType_id',
					xtype: 'swcustomobjectcombo',
					anchor: '100%',
					allowBlank: true,
					listeners: {
						'select': function() {
							wnd.setDefaultName();
						}
					}
				}, {
					fieldLabel: lang['istochnik_finansirovaniya'],
					name: 'DrugFinance_id',
					xtype: 'swdrugfinancecombo',
					anchor: '100%',
					allowBlank: true,
					listeners: {
						'select': function() {
							wnd.setDefaultName();
						}
					}
				}, {
					fieldLabel: lang['gruppa_medikamentov'],
					comboSubject: 'DrugGroup',
					name: 'DrugGroup_id',
					xtype: 'swcustomobjectcombo',
					anchor: '100%',
					allowBlank: true,
					listeners: {
						'select': function() {
							wnd.setDefaultName();
						}
					}
				},
				this.org_combo,
				{
					fieldLabel: lang['naimenovanie'],
					name: 'DrugRequestProperty_Name',
					xtype: 'textarea',
					anchor: '100%',
					allowBlank:false,
					enableKeyEvents: true,
					listeners: {
						'keypress': function() {
							wnd.editName = true;
						}
					}
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			bodyStyle: 'padding: 7px;',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swDrugRequestPropertyEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('slewDrugRequestPropertyEditForm').getForm();
	}	
});