/**
 * swStorageStructLevelEditWindow - окно редактирования/добавления склада.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			07.07.2014
 */

sw.Promed.swStorageStructLevelEditWindow = Ext.extend(sw.Promed.BaseForm,
	{
		action: null,
		autoHeight: true,
		buttonAlign: 'left',
		closable: true,
		closeAction: 'hide',
		draggable: true,
		split: true,
		width: 450,
		layout: 'form',
		id: 'swStorageStructLevelEditWindow',
		listeners:
		{
			hide: function()
			{
				this.onHide();
			}
		},
		modal: true,
		onHide: Ext.emptyFn,
		plain: true,
		resizable: false,
		doSave: function()
		{
			var base_form = this.FormPanel.getForm();
			if ( !base_form.isValid() )
			{
				sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function()
						{
							form.getFirstInvalidEl().focus(true);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: ERR_INVFIELDS_MSG,
						title: ERR_INVFIELDS_TIT
					});
				return false;
			}

			this.getLoadMask("Подождите, идет сохранение...").show();

			var data = new Object();

			data.StorageStructLevelData = getAllFormFieldValues(this.FormPanel);

			this.callback(data);
			this.getLoadMask().hide();

			this.hide();
		},
		show: function()
		{
			sw.Promed.swStorageStructLevelEditWindow.superclass.show.apply(this, arguments);

			var base_form = this.FormPanel.getForm();
			this.callback = Ext.emptyFn;
			this.onHide = Ext.emptyFn;
			this.mode = null;

			if (!arguments[0])
			{
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
					title: lang['oshibka'],
					fn: function() {
						this.hide();
					}
				});
			}

			this.action = arguments[0].action;

			if (arguments[0].callback) {
				this.callback = arguments[0].callback;
			}
			if (arguments[0].onHide) {
				this.onHide = arguments[0].onHide;
			}
			if (arguments[0].mode) {
				this.mode = arguments[0].mode;
			}

			base_form.reset();
			//base_form.setValues(arguments[0].formParams);
			// var org_id = base_form.findField('Org_id').getValue();
			// var lpu_id = base_form.findField('Lpu_id').getValue();

			var org_id = (arguments[0].formParams.org_id) ? arguments[0].formParams.Org_id : null;
			var lpu_id = (arguments[0].formParams.Lpu_id ) ? arguments[0].formParams.Lpu_id : null;
			var formParams = arguments[0].formParams;

			var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
			loadMask.show();

			switch (this.action)
			{
				case 'add':
					this.setTitle(lang['strukturnyiy_uroven_sklada_dobavlenie']);
					this.enableEdit(true);

					//loadMask.hide();
					break;

				case 'edit':
				case 'view':
					if (this.action == 'edit') {
						this.setTitle(lang['strukturnyiy_uroven_sklada_redaktirovanie']);
						this.enableEdit(true);
					} else {
						this.setTitle(lang['strukturnyiy_uroven_sklada_prosmotr']);
						this.enableEdit(false);
					}

					//loadMask.hide();
					break;
			}

			if (!isSuperAdmin()) {
				if(this.mode == 'org') {
					base_form.findField('Org_id').disable();

					base_form.findField('Lpu_id').disable();
					base_form.findField('LpuBuilding_id').disable();
					base_form.findField('LpuUnit_id').disable();
					base_form.findField('LpuSection_id').disable();
				} else if (this.mode == 'lpu') {
					base_form.findField('Lpu_id').disable();

					base_form.findField('Org_id').disable();
					base_form.findField('OrgStruct_id').disable();
				}
			}

			if (!Ext.isEmpty(org_id)) {
				Ext.Ajax.request({
					url: '/?c=Org&m=getOrgList',
					params: {Org_id: org_id},
					callback: function(options, success, response)
					{
						if (success)
						{
							var response_obj = Ext.util.JSON.decode(response.responseText);
							base_form.findField('Org_id').getStore().loadData(response_obj);
							base_form.findField('Org_id').setValue(org_id);

							base_form.findField('Lpu_id').disable();
							base_form.findField('LpuBuilding_id').disable();
							base_form.findField('LpuUnit_id').disable();
							base_form.findField('LpuSection_id').disable();

							var params = {Org_id: org_id};
							base_form.findField('OrgStruct_id').getStore().load({
								params: params,
								callback: function(){
									base_form.findField('OrgStruct_id').setValue(base_form.findField('OrgStruct_id').getValue());
								}
							});
							base_form.findField('MedService_id').getStore().load({
								params: params,
								callback: function(){
									base_form.findField('MedService_id').setValue(base_form.findField('MedService_id').getValue());
								}
							});
						}
					}
				});
			}
			
			if (!Ext.isEmpty(lpu_id)) {
				base_form.findField('Org_id').disable();
				base_form.findField('OrgStruct_id').disable();

				var params = {Lpu_id: lpu_id};
				base_form.findField('LpuBuilding_id').getStore().load({params: params});
				base_form.findField('LpuUnit_id').getStore().load({params: params});
				base_form.findField('LpuSection_id').getStore().load({params: params});
				base_form.findField('MedService_id').getStore().load({
					params: params,
					callback: function(){
						base_form.findField('MedService_id').setValue(base_form.findField('MedService_id').getValue())
						setValues(formParams);
					}
				});
			}else{
				loadMask.hide();
				setValues(formParams);
			}

			function setValues(params){
				if(!params) {
					loadMask.hide();
					return false;
				}
				var LpuBuilding = base_form.findField('LpuBuilding_id');
				var LpuUnit = base_form.findField('LpuUnit_id');
				var LpuSection = base_form.findField('LpuSection_id');
				var MedService = base_form.findField('MedService_id');
				
				if(params.LpuBuilding_id){
					LpuBuilding.fireEvent('change', LpuBuilding, params.LpuBuilding_id);
				}else if(params.LpuUnit_id){
					LpuUnit.fireEvent('change', LpuUnit, params.LpuUnit);
				}else if(params.LpuSection_id){
					LpuSection.fireEvent('change', LpuSection, params.LpuSection);
				}else if(params.MedService_id){
					MedService.fireEvent('change', MedService, params.MedService);
				}

				if(LpuBuilding.getStore().find('LpuBuilding_id', params.LpuBuilding_id) < 0) params.LpuBuilding_id = '';
				if(LpuUnit.getStore().find('LpuUnit_id', params.LpuUnit_id) < 0) params.LpuUnit_id = '';
				if(LpuSection.getStore().find('LpuSection_id', params.LpuSection_id) < 0) params.LpuSection_id = '';
				if(MedService.getStore().find('MedService_id', params.MedService_id) < 0) params.MedService_id = '';

				base_form.setValues(params);
				loadMask.hide();
			}
		},
		initComponent: function()
		{
			this.FormPanel = new Ext.form.FormPanel(
				{
					autoHeight: true,
					bodyStyle: 'padding: 5px',
					border: false,
					buttonAlign: 'left',
					frame: true,
					id: 'SSLEW_StorageStructLevelForm',
					labelAlign: 'right',
					labelWidth: 130,
					items:
						[{
							name: 'StorageStructLevel_id',
							xtype: 'hidden'
						},{
							name: 'Storage_id',
							xtype: 'hidden'
						},{
							name: 'RecordStatus_Code',
							value: 0,
							xtype: 'hidden'
						},{
							layout: 'form',
							id: 'SSLEW_OrgPanel',
							items: [{
								fieldLabel: lang['organizatsiya'],
								xtype: 'sworgcomboex',
								emptyText: '',
								width: 280,
								hiddenName: 'Org_id',
								listeners: {
									'select': function(combo, record, index) {
										var newValue = record.get('Org_id');

										var base_form = this.FormPanel.getForm();

										base_form.findField('Lpu_id').setValue(null);
										base_form.findField('OrgStruct_id').setValue(null);
										base_form.findField('LpuBuilding_id').setValue(null);
										base_form.findField('LpuUnit_id').setValue(null);
										base_form.findField('LpuSection_id').setValue(null);

										if (Ext.isEmpty(newValue) || newValue == 0) {
											base_form.findField('Lpu_id').enable();
											base_form.findField('LpuBuilding_id').enable();
											base_form.findField('LpuUnit_id').enable();
											base_form.findField('LpuSection_id').enable();

											base_form.findField('OrgStruct_id').getStore().removeAll();
											base_form.findField('MedService_id').getStore().removeAll();
										} else {
											base_form.findField('Lpu_id').disable();
											base_form.findField('LpuBuilding_id').disable();
											base_form.findField('LpuUnit_id').disable();
											base_form.findField('LpuSection_id').disable();

											var params = {Org_id: newValue};
											base_form.findField('OrgStruct_id').getStore().load({params: params});
											base_form.findField('MedService_id').getStore().load({params: params});
										}
									}.createDelegate(this),
									'change': function(combo, newValue, oldValue) {
										var base_form = this.FormPanel.getForm();

										base_form.findField('Lpu_id').setValue(null);
										base_form.findField('OrgStruct_id').setValue(null);
										base_form.findField('LpuBuilding_id').setValue(null);
										base_form.findField('LpuUnit_id').setValue(null);
										base_form.findField('LpuSection_id').setValue(null);

										if (Ext.isEmpty(newValue) || newValue == 0) {
											base_form.findField('Lpu_id').enable();
											base_form.findField('LpuBuilding_id').enable();
											base_form.findField('LpuUnit_id').enable();
											base_form.findField('LpuSection_id').enable();

											base_form.findField('OrgStruct_id').getStore().removeAll();
											base_form.findField('MedService_id').getStore().removeAll();
										}
									}.createDelegate(this)
								}
							},{
								fieldLabel: lang['strukturnyiy_uroven'],
								xtype: 'sworgstructcombo',
								width: 280,
								hiddenName: 'OrgStruct_id',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.FormPanel.getForm();

										base_form.findField('MedService_id').setValue(null);

										if (!Ext.isEmpty(newValue)) {
											base_form.findField('MedService_id').getStore().filterBy(function(rec){
												return (rec.get('OrgStruct_id') == newValue);
											});
										} else {
											base_form.findField('MedService_id').getStore().clearFilter();
										}
									}.createDelegate(this)
								}
							}]
						},{
							layout: 'form',
							id: 'SSLEW_LpuPanel',
							items: [{
								fieldLabel: lang['mo'],
								xtype: 'swlpucombo',
								width: 280,
								hiddenName: 'Lpu_id',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.FormPanel.getForm();

										base_form.findField('Org_id').setValue(null);
										base_form.findField('OrgStruct_id').setValue(null);
										base_form.findField('LpuBuilding_id').setValue(null);
										base_form.findField('LpuUnit_id').setValue(null);
										base_form.findField('LpuSection_id').setValue(null);
										base_form.findField('MedService_id').setValue(null);

										if (Ext.isEmpty(newValue) || newValue == 0) {
											base_form.findField('Org_id').enable();
											base_form.findField('OrgStruct_id').enable();

											base_form.findField('LpuBuilding_id').getStore().removeAll();
											base_form.findField('LpuUnit_id').getStore().removeAll();
											base_form.findField('LpuSection_id').getStore().removeAll();
											base_form.findField('MedService_id').getStore().removeAll();
										} else {
											base_form.findField('Org_id').disable();
											base_form.findField('OrgStruct_id').disable();

											var params = {Lpu_id: newValue};
											base_form.findField('LpuBuilding_id').getStore().load({params: params});
											base_form.findField('LpuUnit_id').getStore().load({params: params});
											base_form.findField('LpuSection_id').getStore().load({params: params});
											base_form.findField('MedService_id').getStore().load({params: params});
										}
									}.createDelegate(this)
								}
							},{
								fieldLabel: lang['podrazdelenie'],
								hiddenName: 'LpuBuilding_id',
								id: 'SSLEW_LpuBuildingCombo',
								linkedElements: ['SSLEW_LpuUnitCombo','SSLEW_LpuSectionCombo','SSLEW_MedServiceCombo'],
								width: 280,
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.FormPanel.getForm();

									}.createDelegate(this)
								},
								xtype: 'swlpubuildingglobalcombo'
							},{
								fieldLabel: lang['gruppa_otdeleniy'],
								id: 'SSLEW_LpuUnitCombo',
								parentElementId: 'SSLEW_LpuBuildingCombo',
								linkedElements: ['SSLEW_LpuSectionCombo','SSLEW_MedServiceCombo'],
								xtype: 'swlpuunitglobalcombo',
								width: 280,
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.FormPanel.getForm();

									}.createDelegate(this)
								},
								hiddenName: 'LpuUnit_id'
							},{
								fieldLabel: lang['otdelenie'],
								id: 'SSLEW_LpuSectionCombo',
								parentElementId: 'SSLEW_LpuUnitCombo',
								linkedElements: ['SSLEW_MedServiceCombo'],
								xtype: 'swlpusectionglobalcombo',
								width: 280,
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.FormPanel.getForm();

									}.createDelegate(this)
								},
								hiddenName: 'LpuSection_id'
							}]
						},{
							fieldLabel: lang['slujba'],
							allowBlank: true,
							id: 'SSLEW_MedServiceCombo',
							xtype: 'swmedservicecombo',
							width: 280,
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = this.FormPanel.getForm();

									var record = combo.getStore().getById(newValue);

									if (!record) {return false;}
									log(['medservice',record]);
									base_form.findField('OrgStruct_id').setValue(record.get('OrgStruct_id'));
									base_form.findField('LpuBuilding_id').setValue(record.get('LpuBuilding_id'));
									base_form.findField('LpuUnit_id').setValue(record.get('LpuUnit_id'));
									base_form.findField('LpuSection_id').setValue(record.get('LpuSection_id'));
								}.createDelegate(this)
							},
							hiddenName: 'MedService_id'
						}],
					url: '/?c=Storage&m=saveStorage'
				});
			Ext.apply(this,
				{
					buttons:
						[{
							handler: function()
							{
								this.doSave();
							}.createDelegate(this),
							iconCls: 'save16',
							text: BTN_FRMSAVE
						},
							{
								text: '-'
							},
							HelpButton(this),
							{
								handler: function()
								{
									this.hide();
								}.createDelegate(this),
								iconCls: 'cancel16',
								tabIndex: TABINDEX_LPEEW + 17,
								text: BTN_FRMCANCEL
							}],
					items: [this.FormPanel]
				});
			sw.Promed.swStorageStructLevelEditWindow.superclass.initComponent.apply(this, arguments);
		}
	});