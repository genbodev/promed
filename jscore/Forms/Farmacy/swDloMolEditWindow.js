/**
* swDloMolEditWindow - окно просмотра и редактирования МОЛ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright © 2014 Swan Ltd.
* @author       Salakhov R.
* @version      09.2014
* @comment      Префикс для id компонентов dme (swDloMolEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swDloMolEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['materialno-otvetstvennyie_litsa_sklada'],
	layout: 'border',
	id: 'DloMolEditWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 250,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	generateCode: function() {
		var wnd = this;
		wnd.getLoadMask().show();

		Ext.Ajax.request({
			//params: params,
			callback: function(opt, success, resp) {
				wnd.getLoadMask().hide();
				var response_obj = Ext.util.JSON.decode(resp.responseText);
				if (response_obj && response_obj[0].Mol_Code != '') {
					var new_code = response_obj[0].Mol_Code;
					if (wnd.formParams.Mol_MaxCode && wnd.formParams.Mol_Code != wnd.formParams.Mol_MaxCode && new_code < wnd.formParams.Mol_MaxCode+1) {
						new_code = wnd.formParams.Mol_MaxCode+1;
					}

					wnd.form.findField('Mol_Code').setValue(new_code);
				}
			},
			url: '/?c=Farmacy&m=generateMolCode'
		});
	},
	setNameFields: function() {
		var wnd = this;
		var org_name = 'test';
		var params = new Object();

		wnd.form.findField('Storage_Name').setValue(wnd.formParams.Storage_Name);
		if (wnd.formParams.Lpu_id && wnd.formParams.Lpu_id > 0) {
			params.OrgType = 'lpu';
			params.Lpu_oid = wnd.formParams.Lpu_id;
		} else if (wnd.formParams.Org_id && wnd.formParams.Org_id > 0) {
			params.Org_id = wnd.formParams.Org_id;
		}

		Ext.Ajax.request({
			url: '/?c=Org&m=getOrgList',
			params: params,
			callback: function(options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj[0] && response_obj[0].Org_Nick) {
					wnd.form.findField('Org_Nick').setValue(response_obj[0].Org_Nick);
				}
			}
		});
	},
	setDisabled: function(disable) {
		var wnd = this;

		var field_arr = [
			'Mol_Code',
			'Mol_begDT',
			'Mol_endDT',
			'Person_id',
			'MedStaffFact_id'
		];

		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			var field = wnd.form.findField(field_arr[i]);
			if (disable) {
				field.disable();
			} else {
				field.enable();
			}
		}

		if (disable) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}
	},
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('DloMolEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = wnd.form.getValues(),
			person_field_name = (wnd.mode === 'lpu' ) ? 'MedStaffFact_id' : 'Person_id';

		if(person_field_name == 'Person_id') {
			params.Person_FIO = wnd.form.findField( person_field_name ).getRawValue();
		} else {
			var msf = wnd.form.findField( person_field_name ).getStore().getById(wnd.form.findField( person_field_name ).getValue());
			params.Person_FIO = msf.get('MedPersonal_Fio');
			params.MedPersonal_id = msf.get('MedPersonal_id');
		}	
		
		this.callback(params);
		this.hide();
		return true;
	},
	show: function() {
		var wnd = this;
		sw.Promed.swDloMolEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.Mol_id = null;
		this.formParams = new Object();
		this.struct_level_grid = null;
		this.struct = null;
		this.mode = getGlobalOptions().orgtype;

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].Mol_id ) {
			this.Mol_id = arguments[0].Mol_id;
		}
		if ( arguments[0].formParams ) {
			this.formParams = arguments[0].formParams;
		}
		if ( arguments[0].struct_level_grid ) {
			this.struct_level_grid = arguments[0].struct_level_grid;
		}
		if ( arguments[0].struct ) {
			this.struct = arguments[0].struct;
		}
		if ( arguments[0].mode ) {
			this.mode = arguments[0].mode;
		}

		var lpu_type = this.mode,
			lpu_id = this.formParams.Lpu_id || getGlobalOptions().lpu_id;

		this.form.findField('MedStaffFact_id').hideContainer();
		this.form.findField('MedStaffFact_id').setAllowBlank(true);
		this.form.findField('Person_id').hideContainer();
		this.form.findField('Person_id').setAllowBlank(true);

		if (this.mode == 'lpu') {
			this.personField = this.form.findField('MedStaffFact_id');
		} else {
			this.personField = this.form.findField('Person_id');
		}
		this.personField.showContainer();
		this.personField.setAllowBlank(false);

		this.form.reset();
		this.setTitle(lang['materialno-otvetstvennyie_litsa_sklada']);
		this.setNameFields();

		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();

		if (lpu_type === 'lpu') {
			var medStaffFactFilter = {
				Lpu_id: lpu_id,
				withoutLpuSection: true
			};
			if (!Ext.isEmpty(this.formParams.Storage_begDate)) {
				medStaffFactFilter.dateFrom = this.formParams.Storage_begDate;
			}
			if (!Ext.isEmpty(this.formParams.Storage_endDate)) {
				medStaffFactFilter.dateTo = this.formParams.Storage_endDate;
			}
			// фильтр мест работы по структурному уровню склада
			if (this.struct_level_grid) {
				var lvl = this.struct_level_grid.getStore().findBy(function(rec){
					return (rec.get('RecordStatus_Code') != 3);
				});
				if (lvl != -1) {
					var lvlrec = this.struct_level_grid.getStore().getAt(lvl);
					if (!Ext.isEmpty(lvlrec.get('LpuSection_id'))) {
						medStaffFactFilter.LpuSection_id = lvlrec.get('LpuSection_id');
					} else if (!Ext.isEmpty(lvlrec.get('LpuUnit_id'))) {
						medStaffFactFilter.LpuUnit_id = lvlrec.get('LpuUnit_id');
					} else if (!Ext.isEmpty(lvlrec.get('LpuBuilding_id'))) {
						medStaffFactFilter.LpuBuilding_id = lvlrec.get('LpuBuilding_id');
					}
				}
			}
			// переопределение фильтра мест работы по структурному уровню службы
			if (this.struct) {
				if (Ext.isEmpty(medStaffFactFilter.LpuSection_id) && !Ext.isEmpty(this.struct.LpuSection_id)) {
					medStaffFactFilter.LpuSection_id = this.struct.LpuSection_id;
					medStaffFactFilter.LpuUnit_id = null;
					medStaffFactFilter.LpuBuilding_id = null;
				} else if (Ext.isEmpty(medStaffFactFilter.LpuUnit_id) && !Ext.isEmpty(this.struct.LpuUnit_id)) {
					medStaffFactFilter.LpuUnit_id = this.struct.LpuUnit_id;
					medStaffFactFilter.LpuBuilding_id = null;
				} else if (Ext.isEmpty(medStaffFactFilter.LpuBuilding_id) && !Ext.isEmpty(this.struct.LpuBuilding_id)) {
					medStaffFactFilter.LpuBuilding_id = this.struct.LpuBuilding_id;
				}
			}
			log(['medStaffFactFilter', medStaffFactFilter]);
			setMedStaffFactGlobalStoreFilter(medStaffFactFilter);
			this.personField.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		}

		switch (this.action) {
			case 'add':
				this.setTitle(this.title + lang['_dobavlenie']);
				this.setDisabled(false);
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
				this.setDisabled(this.action == 'view');
				this.form.setValues(this.formParams);
				
				var person_field_name = (this.mode === 'lpu' ) ? 'MedStaffFact_id' : 'Person_id';
				
				this.form.findField( person_field_name ).setRawValue(this.formParams.Person_FIO);
				loadMask.hide();
				break;
		}
	},
	initComponent: function() {
		var wnd = this;
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'DloMolEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 150,
				collapsible: true,
				url:'/?c=Mol&m=save',
				items: [{
					xtype: 'hidden',
					name: 'Mol_id'
				}, {
					xtype: 'textfield',
					fieldLabel: lang['organizatsiya'],
					name: 'Org_Nick',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel: lang['sklad'],
					name: 'Storage_Name',
					anchor: '100%',
					disabled: true
				}, {
					xtype: 'hidden',
					name: 'Storage_id',
					anchor: '100%'
				},/* {
					xtype: 'swstoragecombo',
					fieldLabel: lang['sklad'],
					name: 'Storage_id',
					anchor: '100%'
				}, */{
					xtype: 'trigger',
					fieldLabel : lang['kod'],
					name: 'Mol_Code',
					allowBlank: false,
					enableKeyEvents: true,
					width: 150,
					onTriggerClick: function() {
						if (!this.disabled) {
							wnd.generateCode();
						}
					},
					triggerClass: 'x-form-plus-trigger',
					validateOnBlur: false
				}, {
					xtype: 'swdatefield',
					name: 'Mol_begDT',
					fieldLabel : lang['data_nachala'],
					allowBlank: (getRegionNick() != 'ekb'),
					width: 150,
					format: 'd.m.Y',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					xtype: 'swdatefield',
					name: 'Mol_endDT',
					fieldLabel : lang['data_okonchaniya'],
					allowBlank: true,
					width: 150,
					format: 'd.m.Y',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				},

				new sw.Promed.SwMedStaffFactGlobalCombo({
					name: 'MedStaffFact_id',
					emptyText: null,
					editable: false,
					anchor: '100%',
					autoLoad: true
				}),
				new sw.Promed.SwPersonComboEx({
					name: 'Person_id',
					emptyText: null,
					editable: false,
					anchor: '100%'
				})
				
				]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
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
		sw.Promed.swDloMolEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('DloMolEditForm').getForm();
	}
});