/**
* swEvnFuncRequestEditWindow - редактирование заявки на исследование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      апрель.2012
* 
*/

sw.Promed.swEvnFuncRequestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	height: 550,
	id: 'swEvnFuncRequestEditWindow',
	layout: 'border',
	maximizable: true,
	maximized: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	plain: true,
	resizable: true,
	split: true,
	title: lang['zayavka_na_issledovanie'],
	width: 900,
	listeners: 
	{
		hide: function(data) 
		{
			var base_form = this.findById('EvnFuncRequestEditForm').getForm();
			base_form.findField('EvnDirection_Num'   ).enable();
			base_form.findField('EvnDirection_setDT' ).enable();
			base_form.findField('PrehospDirect_id'   ).enable();
			base_form.findField('EvnFuncRequest_Ward' ).enable();
			base_form.findField('Org_sid'            ).enable();
			base_form.findField('LpuSection_id'      ).enable();
			base_form.findField('MedStaffFact_id'     ).enable();
			base_form.findField('MedPersonal_Code'     ).enable();
			base_form.findField('StudyTarget_id').enable();
			base_form.findField('EvnDirection_IsCito').enable();
			base_form.findField('EvnDirection_Descr').enable();
			this.onHide(data);
		}
	},
    getEvnDirectionNumber: function() {
   		if ( this.action == 'view' ) {
   			return false;
   		}
        var that = this;
   		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение номера талона..."});
   		loadMask.show();
   		Ext.Ajax.request({
   			callback: function(options, success, response) {
   				loadMask.hide();
   				if ( success ) {
   					var response_obj = Ext.util.JSON.decode(response.responseText);
                    var field = that.EvnFuncRequestEditForm.getForm().findField('EvnDirection_Num');
                    field.setValue(response_obj.EvnPL_NumCard);
                    field.focus(true);
   				}
   				else {
   					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_napravleniya']);
   				}
   			},
   			url: '/?c=EvnLabRequest&m=getEvnDirectionNumber'
   		});
   	},
	disableDirFields: function() {
		var base_form = this.findById('EvnFuncRequestEditForm').getForm();
		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_setDT').disable();
		base_form.findField('PrehospDirect_id').disable();
		base_form.findField('EvnFuncRequest_Ward').disable();
		base_form.findField('Org_sid').disable();
		base_form.findField('LpuSection_id').disable();
		base_form.findField('MedStaffFact_id').disable();
		base_form.findField('MedPersonal_Code').disable();
		base_form.findField('StudyTarget_id').disable();
		base_form.findField('EvnDirection_IsCito').disable();
		base_form.findField('EvnDirection_Descr').disable();
		base_form.findField('PayType_id').disable();
	},
	doSave: function() 
	{
	
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		
		this.formStatus = 'save';
		
        var form = this.findById('EvnFuncRequestEditForm');
		var base_form = form.getForm();
		if ( !base_form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        this.submit();
		return true;
	},
	submit: function() 
	{
        var form = this.findById('EvnFuncRequestEditForm');
		var base_form = form.getForm();
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		var params = new Object();
		params.action = current_window.action;

		if ( !base_form.findField('EvnDirection_IsCito').disabled ) {
			params.EvnDirection_IsCito = (base_form.findField('EvnDirection_IsCito').getValue())?'on':'off';
		}

		if (!Ext.isEmpty(base_form.findField('PrehospDirect_id').getValue()) && base_form.findField('PrehospDirect_id').getValue() <= 2 && base_form.findField('Org_sid').getStore().getCount()>0 ) {
			params.Lpu_sid = base_form.findField('Org_sid').getStore().getAt(0).get('Lpu_id');
		}

		if (base_form.findField('PayType_id').disabled) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if (base_form.findField('EvnDirection_setDT').disabled) {
			params.EvnDirection_setDT = base_form.findField('EvnDirection_setDT').getValue().format('d.m.Y');
		}

		if (base_form.findField('StudyTarget_id').disabled) {
			params.StudyTarget_id = base_form.findField('StudyTarget_id').getValue();
		}

		if (base_form.findField('PrehospDirect_id').disabled) {
			params.PrehospDirect_id = base_form.findField('PrehospDirect_id').getValue();
		}
		
		// в дальнейшем всю инфу по услугам можно брать отсюда
		params.uslugaData = Ext.util.JSON.encode(this.UslugaComplexPanel.collectAllData());
		
		if (this.OuterKzDirection) {
			params.OuterKzDirection = 1;
		}

		form.getForm().submit(
		{
			params: params,
			failure: function(result_form, action) 
			{
				this.formStatus = 'edit';
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				loadMask.hide();

                if (this.swPersonSearchWindow != null) { this.swPersonSearchWindow.hide(); }

                if (this.swWorkPlaceFuncDiagWindow) {
					var gridPanel = this.swWorkPlaceFuncDiagWindow.GridPanel;
					gridPanel.loadData({
						callback: function() {
							if( action && action.result && action.result.EvnDirection_id) {
								gridPanel.getGrid().getStore().each(function (record) {
									if (record.get('EvnDirection_id') == action.result.EvnDirection_id) {
										var index = gridPanel.getGrid().getStore().indexOf(record);
										gridPanel.getGrid().getView().focusRow(index);
										gridPanel.getGrid().getSelectionModel().selectRow(index);
										return;
									}
								});
							}
						}
					});
                }

				if(action&&action.result&&action.result.EvnDirection_id){
					current_window.callback(action.result);
				}

				current_window.hide();
				
			}.createDelegate(this)
		});
	},
	selectPrehospDirect: function (combo, record, index, first_load){
		var isView = this.action == 'view';
		var base_form = this.findById('EvnFuncRequestEditForm').getForm();
		//var record = combo.getStore().getById(index);
		var org_combo = base_form.findField('Org_sid');
		var lpu_section_combo = base_form.findField('LpuSection_id');
		var medstafffact_combo = base_form.findField('MedStaffFact_id');
		var ward_field = base_form.findField('EvnFuncRequest_Ward');

		var prehosp_direct_id = null;
		var lpusection_id = lpu_section_combo.getValue();
		var org_id = org_combo.getValue();
		var org_type = '';
		if (!first_load) {
			lpu_section_combo.clearValue();
			org_combo.clearValue();
			medstafffact_combo.clearValue();
			ward_field.setValue(null);
			org_id = null;
		}

		lpu_section_combo.getStore().removeAll();
		medstafffact_combo.getStore().removeAll();
		if (record) {
			prehosp_direct_id = record.get('PrehospDirect_id');
		}
		lpu_section_combo.setAllowBlank(true);
		switch (parseInt(prehosp_direct_id)) {
			case 1: // отделение ЛПУ
			case 8:
			case 9:
			case 15:
				org_type = 'lpu';
				org_id = getGlobalOptions().org_id;
				lpu_section_combo.setAllowBlank(false);
				org_combo.disable();
				if(!isView){
					lpu_section_combo.enable();
					medstafffact_combo.enable();
					ward_field.enable();
				}
				break;
			case 2: // Другое ЛПУ
			case 10:
			case 13:
			case 16:
				org_type = 'lpu';
				if(!isView) {
					lpu_section_combo.enable();
					org_combo.enable();
					medstafffact_combo.enable();
					ward_field.enable();
				}
				break;
			case 4:
			case 12:
				org_type = 'military';
			case 3: // Другие организации
			case 5:
			case 6:
			case 11:
			case 14:
				org_type = 'org';
				lpu_section_combo.disable();
				medstafffact_combo.disable();
				ward_field.disable();
				if(!isView) {
					org_combo.enable();
				}
				break;
            default:
				lpu_section_combo.disable();
				medstafffact_combo.disable();
				org_combo.disable();
				ward_field.disable();
                // на тестовом есть такие направления, пока не понятно что делать
                // но комбики загрузить надо
				//if (prehosp_direct_code == null) return false;
				break;
		}

		if (org_id>0) {
			org_combo.getStore().load({
				callback:function (records, options, success) {
					if (success) {
						org_combo.setValue(org_id);
						org_combo.fireEvent('change', org_combo, org_id);
					}
				},
				params: {
					Org_id: org_id
					/*,OrgType: org_type*/
				}
			});
		}

	},
	filterResource: function() {
		var win = this;
		var base_form = this.findById('EvnFuncRequestEditForm').getForm();

		var values = this.UslugaComplexPanel.getValuesFuncDiag();

		base_form.findField('Resource_id').getStore().baseParams.onDate = getGlobalOptions().date;
		base_form.findField('Resource_id').getStore().baseParams.MedService_id = win.MedService_id;
		base_form.findField('Resource_id').getStore().baseParams.UslugaComplex_ids = Ext.util.JSON.encode(values);
		base_form.findField('Resource_id').lastQuery = 'This query sample that is not will never appear';
	},
	initComponent: function() {
		
		var form = this;
		
		this.InformationPanel = new sw.Promed.PersonInfoPanel({
			id: form.id + 'PersonInformationFrame',
            plugins: [Ext.ux.PanelCollapsedTitle],
            titleCollapse: true,
            floatable: false,
            collapsible: true,
            collapsed: true,
            border: true,
            region: 'north'
		});

		this.UslugaComplexPanel = new sw.Promed.UslugaComplexPanel({
			win: form,
			width: 1000,
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			labelWidth: 150,
			extendedMode: getRegionNick().inlist(['kareliya', 'ekb']),
			style: 'background: transparent',
			onChange: function() { form.filterResource(); },
			showFSIDICombo: true
		});
		
		this.EvnFuncRequestEditForm = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			region: 'center',
			id: 'EvnFuncRequestEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: [
				new sw.Promed.Panel({
					autoHeight: true,
					style: 'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					border: true,
					collapsible: true,
					region: 'north',
					layout: 'form',
					title: lang['1_napravlenie'],
					items: [{
						name: 'Person_id',
						xtype: 'hidden',
						value: 0
					}, {
						name: 'PersonEvn_id',
						xtype: 'hidden',
						value: 0
					}, {
						name: 'Server_id',
						xtype: 'hidden'
					}, {
						name: 'MedService_id',
						xtype: 'hidden'
					}, {
						name: 'EvnDirection_id',
						xtype: 'hidden'
					}, {
						name: 'TimetableResource_id',
						xtype: 'hidden'
					}, {
						name: 'EvnFuncRequest_id',
						xtype: 'hidden'
					}, {
						name: 'EvnDirection_IsReceive',
						xtype: 'hidden'
					}, {
						name:'EDPayType_id',
						xtype:'hidden'
					}, {
						name: 'parentEvnClass_SysNick',
						xtype: 'hidden'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['nomer_napravleniya'],
						listeners: {
							'keydown': function(inp, e) {
								switch ( e.getKey() ) {
									case Ext.EventObject.F2:
										e.stopEvent();
										this.getEvnDirectionNumber();
										break;

									case Ext.EventObject.TAB:
										if ( e.shiftKey == true ) {
											e.stopEvent();
											this.buttons[this.buttons.length - 1].focus();
										}
										break;
								}
							}.createDelegate(this)
						},
						name: 'EvnDirection_Num',
						onTriggerClick: function() {
							if (this.disabled) {
								return false;
							}
							this.getEvnDirectionNumber();
						}.createDelegate(this),
						triggerClass: 'x-form-plus-trigger',
						validateOnBlur: false,
						width: 150,
						xtype: 'trigger',
						autoCreate: {
							tag: "input", 
							autocomplete: "off"
						},
						allowBlank: (!getRegionNick().inlist([ 'perm', 'krym', 'ekb' ]))
					}, {
                        allowBlank: false,
						fieldLabel: lang['data_napravleniya'],
						name: 'EvnDirection_setDT',
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}, {
						fieldLabel: lang['kem_napravlen'],
						hiddenName: 'PrehospDirect_id',
						xtype: 'swprehospdirectcombo',
						listeners: {
							'select':function (combo, record, index) {
								this.selectPrehospDirect(combo, record, index);

								if (getRegionNick() == 'ekb') {
									var form_b = form.EvnFuncRequestEditForm.getForm();

									form_b.findField('MedStaffFact_id').setAllowBlank(index != 1);
									form_b.findField('MedPersonal_Code').setAllowBlank( !(index == 1 || index == 2) );
									form_b.findField('Org_sid').setAllowBlank(index != 2);

								}
							}.createDelegate(this)
						},
						width: 500
					}, {
						displayField: getRegionNick()=='ekb' ? 'Org_Nick' : 'Org_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: lang['organizatsiya'],
						hiddenName: 'Org_sid',
						listeners: {
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
							var base_form = this.findById('EvnFuncRequestEditForm').getForm();
							var combo = base_form.findField('Org_sid');
							if ( combo.disabled ) {
								return false;
							}
							var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
							var prehosp_direct_id = prehosp_direct_combo.getValue();
							var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);
							if ( !record ) {
								return false;
							}
							var prehosp_direct_id = record.get('PrehospDirect_id');
							var org_type = '' , OrgType_id = null, enableOrgType = false;
							switch ( prehosp_direct_id ) {
								case 2:
								case 5:
								case 10:
								case 11:
								case 13:
								case 16:
									org_type = 'lpu';
									OrgType_id = 11;
								break;
								case 4:
								case 12:
									org_type = 'military';
									OrgType_id = 17;
								break;
								case 3:
								case 6:
								case 14:
									org_type = 'org';
									enableOrgType = true;
								break;
								default:
									return false;
								break;
							}
							getWnd('swOrgSearchWindow').show({
								OrgType_id: OrgType_id,
								enableOrgType: enableOrgType,
								object: org_type,
								onlyFromDictionary: true,
								onClose: function() {
									combo.focus(true, 200)
								},
								onSelect: function(org_data) {
									if ( org_data.Org_id > 0 ) {
										combo.getStore().loadData([
											{
												Org_id: org_data.Org_id,
												Lpu_id: org_data.Lpu_id,
												Org_Name: org_data.Org_Name,
												Org_Nick: org_data.Org_Nick
											}
										]);
										combo.setValue(org_data.Org_id);
										getWnd('swOrgSearchWindow').hide();
										combo.fireEvent('change', combo, combo.getValue(), null);
										combo.collapse();
									}
								}
							});
						}.createDelegate(this),
						onTrigger2Click: function() {
							if ( !this.disabled ) {
								this.clearValue();
								var lpusection_combo = form.EvnFuncRequestEditForm.getForm().findField('LpuSection_id');
								var medstafffact_combo = form.EvnFuncRequestEditForm.getForm().findField('MedStaffFact_id');
								lpusection_combo.getStore().removeAll();
								lpusection_combo.clearValue();
								medstafffact_combo.getStore().removeAll();
								medstafffact_combo.clearValue();
							}
						},
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'Org_id', type: 'int'},
								{name: 'Lpu_id', type: 'int'},
								{name: 'Org_Name', type: 'string'},
								{name: 'Org_Nick', type: 'string'}
							],
							key: 'Org_id',
							sortInfo: {
								field: getRegionNick()=='ekb' ? 'Org_Nick' : 'Org_Name'
							},
							url: C_ORG_LIST
						}),
						tpl:new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', getRegionNick()=='ekb' ? '{Org_Nick}' : '{Org_Name}', '</div></tpl>'),
						trigger1Class: 'x-form-search-trigger',
						triggerAction: 'none',
						valueField: 'Org_id',
						width: 500,
						xtype: 'swbaseremotecombo',
						listeners: {
							'change':function (combo, newValue, oldValue) {
								var base_form = this.findById('EvnFuncRequestEditForm').getForm();
								var LpuSection = base_form.findField('LpuSection_id');
								var LpuSection_id = LpuSection.getValue();
								LpuSection.getStore().removeAll();
								//debugger;
								//log(['change Org_sid', newValue, LpuSection_id]);
								if (newValue>0) {
									LpuSection.getStore().load({
										params:{filterLpu_id: base_form.findField('Org_sid').getFieldValue('Lpu_id')},
										callback: function() {
											if (LpuSection_id>0 && LpuSection.getStore().getById(LpuSection_id)) {
												LpuSection.setValue(LpuSection_id);
											} else {
												LpuSection.clearValue();
												LpuSection_id = null;
											}
											LpuSection.fireEvent('change', LpuSection, LpuSection_id, null);
										}
									});
								} else {
									LpuSection.setValue(null);
								}
							}.createDelegate(this)
						}
					},
					{
						hiddenName:'LpuSection_id',
						xtype:'swlpusectioncombo',
						width:500,
						listeners:{
							'change':function (combo, newValue, oldValue) {
								var base_form = this.findById('EvnFuncRequestEditForm').getForm();
								var MedStaffFact = base_form.findField('MedStaffFact_id');
								var MedStaffFact_id = MedStaffFact.getValue();
								MedStaffFact.getStore().removeAll();
								//debugger;
								//log(['change LpuSection_id', newValue, MedStaffFact_id]);
								if (newValue>0) {
									var today = new Date();
									var dd = today.getDate();
									var mm = today.getMonth()+1; //January is 0!
									var yyyy = today.getFullYear();
									if(dd<10) {
									    dd='0'+dd
									} 
									if(mm<10) {
									    mm='0'+mm
									} 
									today = yyyy+'-'+mm+'-'+dd;
									MedStaffFact.getStore().load({
										params:{
											LpuSection_id: newValue,
											mode: 'combo',
											Lpu_id: base_form.findField('Org_sid').getFieldValue('Lpu_id') || getGlobalOptions().lpu_id,
											onDate: today
										},
										callback:function () {
											if (MedStaffFact_id>0 && MedStaffFact.getStore().getById(MedStaffFact_id)) {
												MedStaffFact.setValue(MedStaffFact_id);
											} else {
												MedStaffFact.clearValue();
											}
										}
									});
								} else {
									MedStaffFact.clearValue();
								}
							}.createDelegate(this)
						}
					},
					{
						fieldLabel: lang['palata'],
						xtype: 'textfield',
						width: 500,
                        name: 'EvnFuncRequest_Ward'
					}, {
						fieldLabel: lang['vrach'],
						hiddenName: 'MedStaffFact_id',
						allowBlank: true,
						xtype: 'swmedstafffactglobalcombo',
						width: 500,
						anchor: 'auto',
							listeners: {
								'select' : function(combo) {
									if(getRegionNick()=='ekb') {
										var base_form = form.EvnFuncRequestEditForm.getForm();
										var DloCode = base_form.findField('MedPersonal_Code');
										if(combo.getValue()) {
											DloCode.setValue(combo.getFieldValue('MedPersonal_DloCode'));
										} else DloCode.setValue('');
									}
								}
							}
					}, {
						fieldLabel:langs('Код врача'),
						tabIndex:TABINDEX_ELREW + 7.5,
						hiddenName:'MedPersonal_Code',
						id: 'MedPersonal_Code',
						allowBlank:true,
						maxLength: 14,
						regex: /^[0-9]+$/,
						xtype:'numberfield',
						width: 150
					}, {
						fieldLabel: 'Цель исследования',
						xtype: 'swcommonsprcombo',
						allowBlank: false,
						hiddenName: 'StudyTarget_id',
						value: 2,
						comboSubject: 'StudyTarget',
						width: 500
					}, {
						id: 'EvnDirection_IsCito',
						fieldLabel: 'Cito!',
						xtype: 'checkbox',
						checked: false
					}, {
						fieldLabel: lang['kommentariy'],
						height: 70,
						name: 'EvnDirection_Descr',
						width:500,
						xtype: 'textarea'
					}, {
						checkAccessRights: true,
						fieldLabel: lang['diagnoz'],
						hiddenName: 'Diag_id',
						xtype: 'swdiagcombo',
						width: 500
					}, {
						fieldLabel: getRegionNick() == 'kz' ? 'Источник финансирования' : 'Вид оплаты',
						hiddenName: 'PayType_id',
						allowBlank: false,
						xtype: 'swpaytypecombo',
						width: 500
					}]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					style: 'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;',
					border: true,
					collapsible: true,
					region: 'north',
					layout: 'form',
					frame: true,
					title: lang['2_naznachennyie_uslugi'],
					items: [{
						fieldLabel: lang['resurs'],
						hiddenName: 'Resource_id',
						onTrigger2Click: function() {
							this.clearValue();
							this.fireEvent('change', this, this.getValue());
						},
						allowBlank: false,
						listeners: {
							'change': function(combo, newValue) {
								// фильтруем услуги по ресурсу
								form.UslugaComplexPanel.baseParams.Resource_id = newValue;
								// все комбики в панельке надо очистить
								form.UslugaComplexPanel.items.each(function(item,index,length) {
									item.lastQuery = 'This query sample that is not will never appear';
								});


								if (!Ext.isEmpty(newValue) && newValue > 0) {
									// если ресурс выбран можно добавлять новые услуги
									// если форма открыта на редактирование и добавление
									// и по заявке нет выполненных услуг
									if (form.action != 'view' && (!form.isDisabled)) {
										form.UslugaComplexPanel.buttons[0].enable();
									}
								} else {
									// иначе нельзя
									form.UslugaComplexPanel.buttons[0].disable();
								}
							}
						},
						xtype: 'swresourceremotecombo'
					}, this.UslugaComplexPanel]
				})
			],
			url: '/?c=EvnFuncRequest&m=saveEvnFuncRequest'
		});
		
		Ext.apply(this, 
		{
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
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.InformationPanel,this.EvnFuncRequestEditForm]
		});
		
		sw.Promed.swEvnFuncRequestEditWindow.superclass.initComponent.apply(this, arguments);
		
	},
	onEnableEdit: function() {
		var base_form = this.findById('EvnFuncRequestEditForm').getForm();

		base_form.findField('Resource_id').disable();
		if (this.action == 'add' && Ext.isEmpty(base_form.findField('Resource_id').getValue())) {
			base_form.findField('Resource_id').enable();
		}
	},
	show: function() {
        var that = this;
		sw.Promed.swEvnFuncRequestEditWindow.superclass.show.apply(this, arguments);	
		
		this.formStatus = 'edit';
		
		if (!arguments[0]) {
			alert(lang['ne_ukazanyi_vhodnyie_dannyie']);
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		if (arguments[0].disabled) {
			this.isDisabled = arguments[0].disabled;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		} else {
			this.onHide = Ext.emptyFn;
		}
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}

		if ( arguments[0].EvnFuncRequest_id ) {
			this.EvnFuncRequest_id = arguments[0].EvnFuncRequest_id;
		} else {
			this.EvnFuncRequest_id = null;
		}
		
		if ( arguments[0].OuterKzDirection ) {
			this.OuterKzDirection = arguments[0].OuterKzDirection;
		}
		
		if ( arguments[0].MedService_id ) {
			this.MedService_id = arguments[0].MedService_id;
		} else {
			this.MedService_id = null;
		}
		// Направление может быть, а заявка еще может быть не создана 
		this.EvnDirection_id = ( arguments[0].EvnDirection_id )?arguments[0].EvnDirection_id:null;
		
		if ( arguments[0].swPersonSearchWindow ) {
			this.swPersonSearchWindow = arguments[0].swPersonSearchWindow;
		} else {
			this.swPersonSearchWindow = null;
		}

		if ( arguments[0].swWorkPlaceFuncDiagWindow ) {
			this.swWorkPlaceFuncDiagWindow = arguments[0].swWorkPlaceFuncDiagWindow;
		} else {
			this.swWorkPlaceFuncDiagWindow = null;
		}

        var loadMask = new Ext.LoadMask(this.EvnFuncRequestEditForm.getEl(), {msg:lang['zagruzka']});
        loadMask.show();

		this.UslugaComplexPanel.baseParams = {
			MedService_id: this.MedService_id,
			level: 0
		};
		
		var base_form = this.findById('EvnFuncRequestEditForm').getForm();
		base_form.reset();
		base_form.setValues(arguments[0]);
		base_form.findField('MedPersonal_Code').setContainerVisible(getRegionNick() == 'ekb');

		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var lpu_section_combo = base_form.findField('LpuSection_id');
		var org_combo = base_form.findField('Org_sid');
		
		this.UslugaComplexPanel.switchToStomUslugaComplexPanel(false);
		
		//lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		switch (arguments[0].action) {
			
			case 'add':
				this.enableEdit(true);
				var person_id = base_form.findField('Person_id').getValue();
				var server_id = base_form.findField('Server_id').getValue();

				if (person_id > 0) {
					this.InformationPanel.load({
						Person_id: person_id,
						Server_id: server_id,
						callback: function()
						{
							that.InformationPanel.setPersonTitle();
						}

					});
				}
				



				// prehosp_direct_combo.fireEvent('select', prehosp_direct_combo, prehosp_direct_combo.getValue());
				that.selectPrehospDirect(prehosp_direct_combo, prehosp_direct_combo.getStore().getById(prehosp_direct_combo.getValue()), null, true);
				
				loadMask.hide();
				that.getEvnDirectionNumber();
				base_form.findField('EvnDirection_setDT').setValue(new Date());
				this.UslugaComplexPanel.setValues([null]);
				base_form.findField('EvnDirection_Num').focus(true, 250);

				if (getRegionNick() != 'kz') {
					base_form.findField('PayType_id').setFieldValue('PayType_SysNick', getPayTypeSysNickOms()); // по умолчанию ОМС
				}

				this.filterResource();
				base_form.findField('Resource_id').fireEvent('change', base_form.findField('Resource_id'), base_form.findField('Resource_id').getValue());

				if (!Ext.isEmpty(base_form.findField('Resource_id').getValue())) {
					// прогрузим значение
					base_form.findField('Resource_id').getStore().load({
						params: {
							Resource_id: base_form.findField('Resource_id').getValue(),
							MedService_id: that.MedService_id
						},
						callback: function() {
							base_form.findField('Resource_id').setValue(base_form.findField('Resource_id').getValue());
							base_form.findField('Resource_id').fireEvent('change', base_form.findField('Resource_id'), base_form.findField('Resource_id').getValue());
						}
					});
				} else {
					// прогрузим значение комбо, если ресурс один - выберем его
					base_form.findField('Resource_id').getStore().load({
						params: {
							MedService_id: that.MedService_id
						},
						callback: function() {
							if (base_form.findField('Resource_id').getStore().getCount() == 1)  {
								var Resource_id = base_form.findField('Resource_id').getStore().getAt(0).get('Resource_id');
								base_form.findField('Resource_id').setValue(Resource_id);
								base_form.findField('Resource_id').fireEvent('change', base_form.findField('Resource_id'), Resource_id);
							}
						}
					});
				}
				break;

			case 'view':
			case 'edit':
				if (this.action == 'view') {
					this.enableEdit(false);
				} else {
					this.enableEdit(true);
				}
				Ext.Ajax.request({
					failure:function (response, options) {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					},
					params:{
						EvnFuncRequest_id: this.EvnFuncRequest_id,
						EvnDirection_id: this.EvnDirection_id
					},
					success:function (response, options) {
						var result = Ext.util.JSON.decode(response.responseText),
							isView = (this.action == 'view'),
							uslugaPanel = this.UslugaComplexPanel;

						if (!result[0]) {
							return false;
						}

						base_form.setValues(result[0]);
						base_form.findField('Resource_id').fireEvent('change', base_form.findField('Resource_id'), base_form.findField('Resource_id').getValue());

						that.selectPrehospDirect(prehosp_direct_combo, prehosp_direct_combo.getStore().getById(prehosp_direct_combo.getValue()), null, true);
						
						var Org_sid = org_combo.getValue();
						var person_id = base_form.findField('Person_id').getValue();
						var server_id = base_form.findField('Server_id').getValue();

						if (person_id > 0) {
							this.InformationPanel.load({
								Person_id: person_id,
								Server_id: server_id,
								callback: function()
								{
									that.InformationPanel.setPersonTitle();
								}

							});
						}

						if ((result[0].EvnDirection_IsAuto && result[0].EvnDirection_IsAuto!=2) || (Org_sid != getGlobalOptions().org_id) && getRegionNick() != 'kz') { // если автоматически созданное направление или направление от другого ЛПУ, делаем направление недоступным для редактирования
							this.disableDirFields();
						}

						if (result[0].EvnDirection_IsReceive != 2) {
							base_form.findField('PayType_id').disable();
						}

						if (result[0].disabled) {
							base_form.isDisabled = arguments[0].disabled;
							this.disableDirFields();
						}

						if (Ext.isEmpty(base_form.findField('PayType_id').getValue()) && getRegionNick() == 'kz') {
							base_form.findField('PayType_id').setValue(base_form.findField('EDPayType_id').getValue())
						}
						
						if (result[0].parentEvnClass_SysNick
							&& result[0].parentEvnClass_SysNick == 'EvnVizitPLStom'
						) { uslugaPanel.switchToStomUslugaComplexPanel(true); }
						
						if (result[0]['EvnFuncRequest_uslugaList']) {

							var data = result[0]['EvnFuncRequest_uslugaList'];
							if (isView) {
								for (var i = 0; i < data.length; i++) {
									data[i].disabled = true;
								}
							}
							uslugaPanel.setValuesFuncDiag(data);

						} else { uslugaPanel.setValues([null]); }

						if (!Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
							var diag_id = base_form.findField('Diag_id').getValue();
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
							});
						}
						
						base_form.findField('EvnDirection_Num').focus(true, 250);

						if (!Ext.isEmpty(base_form.findField('Resource_id').getValue())) {
							// прогрузим значение
							base_form.findField('Resource_id').getStore().load({
								params: {
									Resource_id: base_form.findField('Resource_id').getValue(),
									MedService_id: that.MedService_id
								},
								callback: function() {
									base_form.findField('Resource_id').setValue(base_form.findField('Resource_id').getValue());
									base_form.findField('Resource_id').fireEvent('change', base_form.findField('Resource_id'), base_form.findField('Resource_id').getValue());
								}
							});
						}
						
						loadMask.hide();

						if (!getRegionNick().inlist(['kareliya', 'ekb']))
						{
							uslugaPanel.buttons[0].setDisabled(isView);
						}
						
						if (this.action == 'edit') {
							if (Ext.isEmpty(result[0].TimetableResource_id)) {
								var check = false;
								for (var i = 0; i < result[0]['EvnFuncRequest_uslugaList'].length; i++) {
									check = check || result[0]['EvnFuncRequest_uslugaList'][i].disabled;
								}
								
								if (!check) {
									base_form.findField('Resource_id').enable();

									var values = uslugaPanel.getValuesFuncDiag();
									base_form.findField('Resource_id').getStore().baseParams.onDate = getGlobalOptions().date;
									base_form.findField('Resource_id').getStore().baseParams.MedService_id = that.MedService_id;
									base_form.findField('Resource_id').getStore().baseParams.UslugaComplex_ids = Ext.util.JSON.encode(values);
									base_form.findField('Resource_id').lastQuery = 'This query sample that is not will never appear';
								} else {
									this.enableEdit(false);
								}
							}
						}
					}.createDelegate(this),
					url:'/?c=EvnFuncRequest&m=getEvnFuncRequest'
				});
				break;
		}		
	}	
});