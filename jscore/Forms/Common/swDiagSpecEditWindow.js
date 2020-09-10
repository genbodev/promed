/**
* swDiagSpecEditWindow - Добавление уточненного диагноза
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Swan
* @version      01.06.2014
*/

sw.Promed.swDiagSpecEditWindow = Ext.extend(sw.Promed.BaseForm, {
	
	modal: true,
	id:'DiagSpecEditWindow',
	width: 900,
	autoHeight: true,
	onCancel: Ext.emptyFn,
	action:'edit',
	callback: Ext.emptyFn,

	show: function() {
		sw.Promed.swDiagSpecEditWindow.superclass.show.apply(this, arguments);
		var base_form = this.FormPanel.getForm();
		this.formStatus = 'edit';
		var win = this;
		var isPerm = (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm');
		base_form.reset();
		this.hideMedStaff();
		if ( arguments[0] )
		{
			if(arguments[0].action && arguments[0].action!=null){
				this.action = arguments[0].action;
			}
			if(arguments[0].callback && arguments[0].callback!=null){
				this.callback = arguments[0].callback;
			}
			if(arguments[0].action && arguments[0].action!=null){
				this.EvnDiagSpec_id = arguments[0].EvnDiagSpec_id
			}
			base_form.setValues(arguments[0]);
		}else{
			return false;
		}
		switch(this.action){
			case 'add': this.setTitle(lang['spisok_utochnennyih_diagnozov_dobavlenie']); break;
			case 'edit': this.setTitle(lang['spisok_utochnennyih_diagnozov_redaktirovanie']); break;
			case 'view': this.setTitle(lang['spisok_utochnennyih_diagnozov_prosmotr']); break;
			default: this.setTitle(lang['spisok_utochnennyih_diagnozov']); break;
			
		}
		if(this.action == 'add'){
			
			//if(isPerm){
				
				
			//}
			base_form.findField('Org_id').getStore().load({params:{Org_id:getGlobalOptions().org_id,OrgType:'lpu'},
				callback:function(){
					base_form.findField('Org_id').setValue(getGlobalOptions().org_id);
					base_form.findField('Org_id').fireEvent('change', base_form.findField('Org_id'), base_form.findField('Org_id').getValue(), 0);
				}
			})
			
		}
		if ( this.action != 'add' ) {
			var params = {};
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			base_form.reset();
			
			params.EvnDiagSpec_id = this.EvnDiagSpec_id;
			base_form.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera']);
				}.createDelegate(this),
				url:'/?c=EvnDiag&m=getDiagSpecEditWindow',
				params:params,
				success: function(fm,rec,d) {
						var response_obj = Ext.util.JSON.decode(rec.response.responseText);
						
						var diag_id = base_form.findField('Diag_id').getValue();
						var org_id = base_form.findField('Org_id').getValue();

						base_form.findField('Diag_id').getStore().load({
							callback: function() {
								base_form.findField('Diag_id').getStore().each(function(record) {
									if ( record.get('Diag_id') == diag_id ) {
										base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
										base_form.findField('Diag_id').fireEvent('change', base_form.findField('Diag_id'), diag_id);
									}
								});
							},
							params: {where: "where Diag_id = " + diag_id}
						});
						base_form.findField('Org_id').getStore().load({params:{Org_id:org_id,OrgType:'lpu'},
							callback:function(){
								var index = base_form.findField('Org_id').getStore().findBy(function(rec) {
									return (rec.get('Org_id') == org_id);
								});
								if ( index >= 0 ) {
									base_form.findField('Org_id').setValue(org_id);
									base_form.findField('Org_id').fireEvent('change', base_form.findField('Org_id'), org_id, 0);
								}
								
							}
						})
						//base_form.findField('Org_id').fireEvent('change', base_form.findField('Org_id'), Org_id);
				}
			});
			
			win.getLoadMask().hide();

		}
		this.setFieldDisable();
		base_form.findField('Org_id').setDisabled(isPerm);
		base_form.findField('EvnDiagSpec_setDate').focus(true, 500);
	},
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';
		var base_form = this.FormPanel;
		if (!base_form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//this.formStatus = 'edit';
					base_form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
            this.formStatus = 'edit';
			return false;
		}

		this.submit();
	},
	setFieldDisable:function(){
		var form = this.FormPanel;
		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.items.each(function(item,s,f){
			item.setDisabled(win.action=='view');
		});
		base_form.findField('EvnDiagSpec_setDate').setAllowBlank(win.action=='view');
		base_form.findField('Diag_id').setAllowBlank(win.action=='view');
		if(win.action=='view'){
			this.buttons[0].disable();
		}else{
			this.buttons[0].enable();
		}
	
	},
	submit: function(mode,onlySave) {
		var form = this.FormPanel;
		var base_form = this.FormPanel.getForm();
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit({
			params: {
				EvnDiagSpec_Lpu:base_form.findField('Org_id').getRawValue(),
				EvnDiagSpec_LpuSectionProfile:base_form.findField('EvnDiagSpec_LpuSectionProfile').getValue()
			},
			url:'/?c=EvnDiag&m=saveDiagSpecEditWindow',
			failure: function(result_form, action) {
				loadMask.hide();
				win.formStatus = 'edit';
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				var data={};
				data.Person_id = base_form.findField('Person_id').getValue();
				win.callback(data);
				form.getForm().reset();
				win.hide();
				
			}
		});
	},
	showMedStaff: function() {
		var base_form = this.FormPanel.getForm();

		base_form.findField('MedStaffFact_id').setValue('');
		base_form.findField('MedStaffFact_id').hideContainer();
		base_form.findField('EvnDiagSpec_MedWorker').showContainer();
		base_form.findField('EvnDiagSpec_LpuSectionProfile').setDisabled(false);

		if (Ext.isEmpty(base_form.findField('EvnDiagSpec_MedWorker').getValue())) {
			base_form.findField('EvnDiagSpec_LpuSectionProfile').setValue('');
		}
	},
	hideMedStaff: function() {
		var base_form = this.FormPanel.getForm();

		base_form.findField('MedStaffFact_id').showContainer();
		base_form.findField('EvnDiagSpec_MedWorker').setValue('');
		base_form.findField('EvnDiagSpec_MedWorker').hideContainer();
		base_form.findField('EvnDiagSpec_LpuSectionProfile').setDisabled(true);
		if (Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue())) {
			base_form.findField('EvnDiagSpec_LpuSectionProfile').setValue('');
		}
	},
	initComponent: function() {
    	
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			height:200,
			bodyStyle: 'padding: 5px',
			buttonAlign: 'left',
			frame: true,
			id: 'PersEvalEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
			{
				name: 'Person_id'
			},

			{
				name: 'PersonEvn_id'
			},

			{
				name: 'EvnDiagSpec_id'
			},

			{
				name: 'EvnDiagSpec_setDate'
			},
			{
				name: 'EvnDiagSpec_setDT'
			},
			{
				name: 'EvnDiagSpec_Post'
			},
			{
				name: 'Diag_id'
			},
			{
				name:'Server_id'
			},
			{
				name:'Lpu_id'
			},
			{
				name:'Org_id'
			},
			{
				name: 'MedStaffFact_id'
			},
			{
				name: 'EvnDiagSpec_MedWorker'
			},
			{
				name: 'EvnDiagSpec_LpuSectionProfile'
			}
			]),
			items: [
			{
				name: 'Person_id',
				xtype: 'hidden'
			},

			{
				name: 'PersonEvn_id',
				xtype: 'hidden'
			},
			{
				name: 'EvnDiagSpec_id',
				xtype: 'hidden'
			},
			{
				name: 'Server_id',
				xtype: 'hidden'
			},

			{
				layout: 'form',
				items: [
					{
						allowBlank: false,
						fieldLabel: lang['data_vvoda'],
						format: 'd.m.Y',
						maxValue: getGlobalOptions().date,
						minValue: getMinBirthDate(),
						setDate:true,
						value:getGlobalOptions().date,
						hiddenName: 'EvnDiagSpec_setDate',
						name: 'EvnDiagSpec_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_DSEW + 5,
						width: 95,
						xtype: 'swdatefield'
						
					},{
						allowBlank: true,
						fieldLabel: lang['data_ustanovki'],
						format: 'd.m.Y',
						maxValue: getGlobalOptions().date,
						minValue: getMinBirthDate(),
						hiddenName: 'EvnDiagSpec_setDT',
						name: 'EvnDiagSpec_setDT',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						width: 95,
						tabIndex: TABINDEX_DSEW + 10,
						xtype: 'swdatefield'
					},/*{
						allowBlank: false,
						autoLoad: false,
						comboSubject: 'DiagSetClass',
						fieldLabel: lang['vid_diagnoza'],
						hiddenName: 'DiagSetClass_id',
						tabIndex: this.tabIndex + 3,
						typeCode: 'int',
						width: 480,
						xtype: 'swcommonsprcombo'
					},*/{
						allowBlank: false,
						hiddenName: 'Diag_id',
						listWidth: 580,
						tabIndex: TABINDEX_DSEW + 15,
						width: 650,
						xtype: 'swdiagcombo'
					},/*{
						lastQuery: '',
						width: 300,
						fieldLabel: lang['mo'],
						hiddenName: 'Lpu_id',
						tabIndex: TABINDEX_DSEW + 20,
						xtype: 'swlpucombo',
						listeners: {
							'select': function(combo){
								this.fireEvent('change', combo, combo.getValue(), 0);
							},
							'change': function(combo, newValue, oldValue) {
								var base_form = this.FormPanel.getForm(),
									mestafffact_id = base_form.findField('MedStaffFact_id').getValue();
								if (!newValue.inlist(getGlobalOptions().lpu) && newValue != 0) {
									win.showMedStaff();
								} else {
									win.hideMedStaff();
									base_form.findField('MedStaffFact_id').clearValue();
									if (oldValue != newValue)  {
										base_form.findField('MedStaffFact_id').getStore().load({
										callback: function() {
												base_form.findField('MedStaffFact_id').getStore().each(function(record) {
													var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
														return rec.get('MedStaffFact_id') == mestafffact_id;
													});
													if ( index >= 0 ) {
														base_form.findField('MedStaffFact_id').setValue(mestafffact_id);
													}
													else {
														base_form.findField('MedStaffFact_id').clearValue();
													}
												});
											},
											params: {Lpu_id: combo.getValue()}
										});
									}
								}
							}.createDelegate(this)
						}
					},*/{
						displayField: 'Org_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: lang['organizatsiya'],
						hiddenName: 'Org_id',
						listeners: {
							'change': function(combo,s,o) {
								var newValue = combo.getValue();
								var base_form = this.FormPanel.getForm(),
									mestafffact_id = base_form.findField('MedStaffFact_id').getValue();
								if (newValue!=getGlobalOptions().org_id && newValue != 0) {
									win.showMedStaff();
								} else {
									win.hideMedStaff();
									base_form.findField('MedStaffFact_id').clearValue();
									if (newValue)  {
										base_form.findField('MedStaffFact_id').getStore().load({
										callback: function() {
												base_form.findField('MedStaffFact_id').getStore().each(function(record) {
													
													var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
														
														return rec.get('MedStaffFact_id') == mestafffact_id;
													});
													if ( index >= 0 ) {
														base_form.findField('MedStaffFact_id').setValue(mestafffact_id);
													}
													else {
														base_form.findField('MedStaffFact_id').clearValue();
													}
												});
											},
											params: {Org_id: combo.getValue()}
										});
									}
								}
							}.createDelegate(this),
							'keydown': function( inp, e ) {
								if ( inp.disabled )
									return;

								if ( e.F4 == e.getKey() ) {
									if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
									else
										e.browserEvent.cancelBubble = true;

									if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
									else
										e.browserEvent.returnValue = false;

									e.returnValue = false;

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									inp.onTrigger1Click();
									return false;
								}
							},
							'keyup': function(inp, e) {
								if ( e.F4 == e.getKey() ) {
									if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
									else
										e.browserEvent.cancelBubble = true;

									if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
									else
										e.browserEvent.returnValue = false;

									e.returnValue = false;

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									return false;
								}
							}
						},
						mode: 'local',
						onTrigger1Click: function() {
							var base_form = this.FormPanel.getForm();
							var combo = base_form.findField('Org_id');

							if ( combo.disabled ) {
								return false;
							}

							
							var org_type = 'lpu';


							getWnd('swOrgSearchWindow').show({
								object: org_type,
								onClose: function() {
									combo.focus(true, 200)
								},
								onSelect: function(org_data) {
									if ( org_data.Org_id > 0 ) {
										combo.getStore().loadData([{
											Org_id: org_data.Org_id,
											Org_Name: org_data.Org_Name
										}]);
										combo.setValue(org_data.Org_id);
										combo.fireEvent('change', combo, combo.getValue(), 0);
										
										getWnd('swOrgSearchWindow').hide();
										combo.collapse();
									}
								}
							});
						}.createDelegate(this),
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'Org_id', type: 'int' },
								{ name: 'Org_Name', type: 'string' }
							],
							key: 'Org_id',
							sortInfo: {
								field: 'Org_Name'
							},
							url: C_ORG_LIST
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Org_Name}',
							'</div></tpl>'
						),
						trigger1Class: 'x-form-search-trigger',
						triggerAction: 'none',
						valueField: 'Org_id',
						width: 650,
						xtype: 'swbaseremotecombo'
					},
					{
						width: 650,
						anchor: false,
						editable: true,
						listeners: {
							select: function(combo){
								this.fireEvent('change', combo, combo.getValue(), 0);
							},
							change: function(combo, newValue, oldValue) {
								var PostField = win.FormPanel.getForm().findField('EvnDiagSpec_LpuSectionProfile');
								PostField.setValue(combo.getFieldValue('LpuSectionProfile_Name'));
								PostField.setDisabled(true);
							}
						},
						lastQuery: '',
						fieldLabel: lang['vrach'],
						hiddenName: 'MedStaffFact_id',
						tabIndex: TABINDEX_DSEW + 25,
						xtype: 'swmedstafffactglobalcombo'
					},{
						xtype:'textfield',
						fieldLabel: lang['vrach'],
						name:'EvnDiagSpec_MedWorker',
						tabIndex: TABINDEX_DSEW + 30,
						width: 650
					},{
						xtype:'textfield',
						fieldLabel: lang['profil'],
						name:'EvnDiagSpec_LpuSectionProfile',
						tabIndex: TABINDEX_DSEW + 35,
						width: 650
					}
				]
			}],
			enableKeyEvents: true,
			keys: [{
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('PersonEvalEditWindow').hide();
				},
				key: [ Ext.EventObject.J ],
				stopEvent: true
			}, {
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('PersonEvalEditWindow').doSave();
				},
				key: [ Ext.EventObject.C ],
				stopEvent: true
			}]
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'ok16',
				tabIndex: TABINDEX_DSEW + 40,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this,TABINDEX_DSEW + 45),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.FormPanel.getForm().findField('EvnDiagSpec_setDate').focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_DSEW + 50,
				text: BTN_FRMCANCEL
			}],
			items: [
			this.FormPanel
			]
		});
		
		sw.Promed.swDiagSpecEditWindow.superclass.initComponent.apply(this, arguments);
	}
});