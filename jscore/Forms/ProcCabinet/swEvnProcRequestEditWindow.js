/**
* swEvnProcRequestEditWindow - редактирование заявки на исследование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      апрель.2012 / copypasted 15.01.2013
* 
*/

sw.Promed.swEvnProcRequestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	height: 550,
	id: 'swEvnProcRequestEditWindow',
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
	listeners: {
		hide: function () {
			var base_form = this.findById('EvnProcRequestEditForm').getForm(),
				enable_fields = [
					'EvnDirection_Num',
					'EvnDirection_setDT',
					'PrehospDirect_id',
					'EvnLabRequest_Ward',
					'Org_sid',
					'LpuSection_id',
					'MedPersonal_id',
					'EvnDirection_IsCito',
					'PayType_id',
					'EvnPrescrProc_didDT',
					'EvnPrescr_IsExec',
					'EvnPrescrProc_Descr'
				];

			Ext.each(enable_fields, function (item, index, allitems) {
				base_form.findField(item).enable();
			});

			this.onHide();
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
                    var field = that.EvnProcRequestEditForm.getForm().findField('EvnDirection_Num');
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
	doSave: function() 
	{
	
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		
		this.formStatus = 'save';
		
        var form = this.findById('EvnProcRequestEditForm');
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
		};

        this.submit();
		return true;
	},
	submit: function() 
	{
        var form = this.findById('EvnProcRequestEditForm');
		var base_form = form.getForm();
		var current_window = this;

		if(!base_form.isValid()){
			this.formStatus = '';
			this.action = '';
			return;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		//var params = base_form.getValues();
		var params = {},
			recurs = function(arr){
				if(arr.items){
					arr.items.each(function(c){
						if(
							c.xtype &&
							(typeof c.getValue == "function") &&
							(typeof c.getName == "function") &&
							(c.isXType('field') || c.xtype.inlist(['swlpusectionglobalcombo']))
						)
						{
							if(c.xtype == "swdatefield"){
								params[c.getName()] = c.value;
							}
							else{
								params[c.getName()] = c.getValue();
							}
						}
						if(c.items){
							recurs(c);
						}
					});
				}
			};

		recurs(form);

		params.action = current_window.action;
		params.object = 'TimetableResource';
		if (!Ext.isEmpty(base_form.findField('PrehospDirect_id').getValue()) && base_form.findField('PrehospDirect_id').getValue() <= 2 && base_form.findField('Org_sid').getStore().getCount()>0 ) {
			params.Lpu_sid = base_form.findField('Org_sid').getStore().getAt(0).get('Lpu_id');
		}
		params.EvnPrescr_IsExec = base_form.findField('EvnPrescr_IsExec').getValue() ? 2 : 1;
		params.EvnDirection_IsCito = base_form.findField('EvnDirection_IsCito').getValue() ? 2 : 1;

		Ext.Ajax.request({
			params: params,
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					loadMask.hide();
					this.formStatus = 'edit';
					if (response_obj.success) {
						if (this.swPersonSearchWindow != null) {
							this.swPersonSearchWindow.hide();
						}
						if (this.swWorkPlaceFuncDiagWindow) {
							this.swWorkPlaceFuncDiagWindow.findById('WorkPlaceGridPanel').loadData();
						}
						current_window.hide();
						current_window.callback();
					}
				}
			}.createDelegate(this),
			url: '/?c=EvnFuncRequestProc&m=saveEvnProcRequest'
		});
		/*
		var params = {};
		params.action = current_window.action;


		params.object = 'TimetableResource';
		if (!Ext.isEmpty(base_form.findField('PrehospDirect_id').getValue()) && base_form.findField('PrehospDirect_id').getValue() <= 2 && base_form.findField('Org_sid').getStore().getCount()>0 ) {
			params.Lpu_sid = base_form.findField('Org_sid').getStore().getAt(0).get('Lpu_id');
		}
		params.EvnPrescr_IsExec = base_form.findField('EvnPrescr_IsExec').getValue() ? 2 : 1;

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
                if (this.swPersonSearchWindow != null) {
                    this.swPersonSearchWindow.hide();
                }
                if (this.swWorkPlaceFuncDiagWindow) {
                    this.swWorkPlaceFuncDiagWindow.findById('WorkPlaceGridPanel').loadData();
                }
				current_window.hide();
				current_window.callback();
			}.createDelegate(this)
		});
		*/
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

		// панель с расписанием из назначения
		this.EvnPrescrProcDataPanel = new sw.Promed.Panel({
			width: 700,
			labelWidth: 150,
			layout: 'form',
			items: [{
					name: 'EvnCourseProc_id',
					xtype: 'hidden'
				}, {
					name: 'EvnPrescrProc_id',
					xtype: 'hidden'
				}, {
					fieldLabel: lang['povtorov_v_sutki'],
					style: 'text-align: right;',
					name: 'EvnPrescrProc_CountInDay',
					width: 100,
					xtype: 'numberfield'
				}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					labelWidth: 150,
					layout: 'form',
					items: [{
						fieldLabel: lang['prodoljitelnost'],
						style: 'text-align: right;', 
						name: 'EvnPrescrProc_CourseDuration',
						width: 100,
						xtype: 'numberfield'
					},{
						fieldLabel: lang['povtoryat_nepreryivno'],
						style: 'text-align: right;', 
						name: 'EvnPrescrProc_ContReception',
						width: 100,
						xtype: 'numberfield'
					},{
						fieldLabel: lang['pereryiv'],
						style: 'text-align: right;', 
						name: 'EvnPrescrProc_Interval',
						width: 100,
						xtype: 'numberfield'
					}]
				},{
					border: false,
					layout: 'form',
					style: 'margin-left: 10px; padding: 0px;',
					items: [{
						hiddenName: 'DurationType_id',
						width: 70,
						xtype: 'swdurationtypecombo'
					},{
						hiddenName: 'DurationType_nid',
						width: 70,
						xtype: 'swdurationtypecombo'
					},{
						hiddenName: 'DurationType_sid',
						width: 70,
						xtype: 'swdurationtypecombo'
					}]
				}]
			}]
		});
		
		this.UslugaComplexPanel = new sw.Promed.Panel({
			width: 750,
			labelWidth: 150,
			layout: 'form',
			items: [{
					xtype: 'swuslugacomplexpidcombo',
					width: 550,
					name: 'Search_Usluga',
					hiddenName: 'UslugaComplex_id',
					fieldLabel: lang['usluga'],
					allowBlank: false
				},
				this.EvnPrescrProcDataPanel
			]
		});
		
		this.EvnProcRequestEditForm = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			region: 'center',
			id: 'EvnProcRequestEditForm',
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
						name: 'PrescriptionStatusType_id',
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
							this.getEvnDirectionNumber();
						}.createDelegate(this),
						tabIndex: TABINDEX_EPLEF + 1,
						triggerClass: 'x-form-plus-trigger',
						validateOnBlur: false,
						width: 150,
						xtype: 'trigger',
						autoCreate: {
							tag: "input", 
							autocomplete: "off"
						}
					}, {
						fieldLabel: lang['data_napravleniya'],
						name: 'EvnDirection_setDT',
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
					}, {
						fieldLabel: lang['kem_napravlen'],
						hiddenName: 'PrehospDirect_id',
						xtype: 'swprehospdirectcombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnProcRequestEditForm').getForm();
								var record = combo.getStore().getById(newValue);
								
								var org_combo = base_form.findField('Org_sid');
								var lpu_section_combo = base_form.findField('LpuSection_id');
								var medpersonal_combo = base_form.findField('MedPersonal_id');
								var prehosp_direct_code = null;
								
								org_combo.clearValue();
								org_combo.getStore().removeAll();
								
								lpu_section_combo.clearValue();
								lpu_section_combo.getStore().removeAll();
								
								medpersonal_combo.clearValue();
								medpersonal_combo.getStore().removeAll();

								if ( record ) {
									prehosp_direct_code = record.get('PrehospDirect_Code');
								}
								
								if (getRegionNick() == 'kz') {	
									switch ( prehosp_direct_code ) {
										case 8:
											org_combo.getStore().load({
												callback: function(records, options, success) {
													if ( success ) {
														if (org_combo.getStore().getCount()>0) {
															org_combo.setValue(org_combo.getStore().getAt(0).get('Org_id'));
														}
														lpu_section_combo.getStore().load(
														{
															params: {Lpu_id: getGlobalOptions().lpu_id}
														});
													}
												},
												params: {
													OrgType: 'lpu', 
													Lpu_oid: getGlobalOptions().lpu_id
												}
											});
										case 9:
											lpu_section_combo.enable();
											org_combo.enable();
										break;
											
										default:
											lpu_section_combo.disable();
											org_combo.enable();
											break;
									}
								} else {								
									switch ( prehosp_direct_code ) {
										case 1:
											org_combo.getStore().load({
												callback: function(records, options, success) {
													if ( success ) {
														if (org_combo.getStore().getCount()>0) {
															org_combo.setValue(org_combo.getStore().getAt(0).get('Org_id'));
														}
														lpu_section_combo.getStore().load(
														{
															params: {Lpu_id: getGlobalOptions().lpu_id}
														});
													}
												},
												params: {
													OrgType: 'lpu', 
													Lpu_oid: getGlobalOptions().lpu_id
												}
											});
										case 2:
											lpu_section_combo.enable();
											org_combo.enable();
										break;

										case 3:
										case 4:
										case 5:
										case 6:
											lpu_section_combo.disable();
											org_combo.enable();
										break;

										default:
											lpu_section_combo.disable();
											org_combo.disable();
											if(prehosp_direct_code == null)
												return false;
										break;
									}
								}
							}.createDelegate(this)
						},
						width: 500
					}, {
						displayField: 'Org_Name',
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
							var base_form = this.findById('EvnProcRequestEditForm').getForm();
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

							var prehosp_direct_code = record.get('PrehospDirect_Code');
							var org_type = '';
							
							if (getRegionNick() == 'kz') {	
								switch ( prehosp_direct_code ) {
									case 9:
										org_type = 'lpu';
									break;
									
									default:
										org_type = 'org';
										break;
								}
							} else {
								switch ( prehosp_direct_code ) {
									case 2:
										org_type = 'lpu';
									break;

									case 4:
										org_type = 'military';
									break;

									case 3:
									case 5:
									case 6:
										org_type = 'org';
									break;

									default:
										return false;
									break;
								}
							}

							getWnd('swOrgSearchWindow').show({
								object: org_type,
								onClose: function() {
									combo.focus(true, 200);
								},
								onSelect: function(org_data) {
									if ( org_data.Org_id > 0 ) {
										combo.getStore().loadData([{
											Org_id: org_data.Org_id,
											Lpu_id: org_data.Lpu_id,
											Org_Name: org_data.Org_Name
										}]);
										combo.setValue(org_data.Org_id);
										getWnd('swOrgSearchWindow').hide();
										base_form.findField('MedPersonal_id').clearValue();
										base_form.findField('LpuSection_id').clearValue();
										base_form.findField('LpuSection_id').getStore().load(
										{
											params: {Lpu_id: org_data.Lpu_id}
										});
										combo.collapse();
									}
								}
							});
						}.createDelegate(this),
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'Org_id', type: 'int'},
								{name: 'Lpu_id', type: 'int'},
								{name: 'Org_Name', type: 'string'}
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
						width: 500,
						xtype: 'swbaseremotecombo'
					}, {
						hiddenName: 'LpuSection_id',
						xtype: 'swlpusectionglobalcombo',
						width: 500,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnProcRequestEditForm').getForm();
								base_form.findField('MedPersonal_id').clearValue();
								base_form.findField('MedPersonal_id').getStore().removeAll();
								if (newValue > 0) {
									base_form.findField('MedPersonal_id').getStore().load({
										params:
										{
											LpuSection_id: newValue
										},
										callback: function()
										{
											base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
										}
									});
								}
							}.createDelegate(this)
						}
					}, {
						fieldLabel: lang['palata'],
						xtype: 'textfield',
						width: 500,
                        name: 'EvnLabRequest_Ward'
					}, {
						fieldLabel: lang['vrach'],
						hiddenName: 'MedPersonal_id',
						allowBlank: true,
						xtype: 'swmedpersonalcombo',
						width: 500,
						anchor: 'auto'
					}, {
						id: 'EvnDirection_IsCito',
						fieldLabel: 'Cito!',
						xtype: 'checkbox',
						checked: false
					}, {
						fieldLabel: lang['vid_oplatyi'],
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
					title: lang['2_naznachenie'],
					items: [this.UslugaComplexPanel]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					style: 'margin-bottom: 0.5em;',
					bodyStyle:'background:#DFE8F6;',
					border: true,
					collapsible: true,
					region: 'north',
					layout: 'column',
					frame: true,
					title: langs('3. Выполнение'),
					items: [
						{
							border: false,
							labelWidth: 150,
							layout: 'form',
							items: [{
								fieldLabel: langs('Время выполнения'),
								width: 80,
								name: 'EvnPrescrProc_didDT',
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								xtype: 'swtimefield',
								format: 'H:i',
								listeners: {
									change: function(field, newValue, oldValue){
										form.EvnProcRequestEditForm.getForm().findField('EvnPrescr_IsExec').setValue(true);
									}
								}
							}]
						},
						{
							border: false,
							labelWidth: 60,
							layout: 'form',
							items: [{
								fieldLabel: langs('Статус'),
								name: 'EvnPrescr_IsExec',
								xtype: 'checkbox',
								checked: false,
								listeners: {
									check: function(field, checked){
										form.allowBlankDidDT(!checked);
									}
								}
							}]
						},
						{
							border: false,
							labelWidth: 100,
							layout: 'form',
							items: [{
								fieldLabel: langs('Примечание'),
								xtype: 'textfield',
								width: 500,
								name: 'EvnPrescrProc_Descr',
								enableKeyEvents: true,
								listeners: {
									keyup: function(field, e){
										form.allowBlankDidDT(field.getValue() === '');
									}
								}
							}]
						}
					]
				})
			]
			//url: '/?c=EvnFuncRequestProc&m=saveEvnProcRequest'
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
			items: [this.InformationPanel,this.EvnProcRequestEditForm]
		});
		
		sw.Promed.swEvnProcRequestEditWindow.superclass.initComponent.apply(this, arguments);
		
	},
	show: function() {
        
		var that = this;
		sw.Promed.swEvnProcRequestEditWindow.superclass.show.apply(this, arguments);	

		this.formStatus = 'edit';
		
		if (!arguments[0]) {
			alert(lang['ne_ukazanyi_vhodnyie_dannyie']);
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		} else {
			this.onHide = Ext.emptyFn;
		}
		
		this.callback = arguments[0].callback || Ext.emptyFn;

		if ( arguments[0].EvnFuncRequest_id ) {
			this.EvnFuncRequest_id = arguments[0].EvnFuncRequest_id;
		} else {
			this.EvnFuncRequest_id = null;
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

		this.EvnPrescrProcDataPanel.hide();
		
        var loadMask = new Ext.LoadMask(this.EvnProcRequestEditForm.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		
		var base_form = this.findById('EvnProcRequestEditForm').getForm();
		base_form.reset();
		base_form.setValues(arguments[0]);

		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var lpu_section_combo = base_form.findField('LpuSection_id');
		var org_combo = base_form.findField('Org_sid');
		base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
		base_form.findField('UslugaComplex_id').getStore().removeAll();
		base_form.findField('UslugaComplex_id').getStore().baseParams.MedService_id = this.MedService_id;
		base_form.findField('UslugaComplex_id').getStore().baseParams['allowedUslugaComplexAttributeList'] = Ext.util.JSON.encode(['manproc']);
		
		base_form.findField('UslugaComplex_id').enable();

		var action = arguments[0].action;
		
		//lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		switch (arguments[0].action) {
			
			case 'add':
				that.setTitle(lang['zayavka_na_issledovanie'] + lang['_dobavlenie']);
				
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
				var usluga_complex_id = base_form.findField('UslugaComplex_id').getValue() || false;
				if (usluga_complex_id) {
					base_form.findField('UslugaComplex_id').getStore().load({
						callback: function() {
							if ( base_form.findField('UslugaComplex_id').getStore().getCount() > 0 ) {
								base_form.findField('UslugaComplex_id').setValue(usluga_complex_id);
							}
							else {
								base_form.findField('UslugaComplex_id').clearValue();
							}
						}.createDelegate(this),
						params: {
							UslugaComplex_id: usluga_complex_id
						}
					});
				}
				prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_combo.getValue());
				loadMask.hide();
				that.getEvnDirectionNumber();
				base_form.findField('EvnDirection_setDT').setValue(new Date());
				base_form.findField('EvnDirection_Num').focus(true, 250);
				that.buttons[0].enable();
				break;

			case 'view':
			case 'edit':
				var appendTitle = lang['_redaktirovanie'];
				that.buttons[0].enable();
				if(arguments[0].action == 'view'){
					appendTitle = lang['_prosmotr'];
					that.buttons[0].disable();
				};

				that.setTitle(lang['zayavka_na_issledovanie'] + appendTitle);

				Ext.Ajax.request({
					failure:function (response, options) {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					},
					params:{
						EvnFuncRequest_id: this.EvnFuncRequest_id,
						EvnDirection_id: this.EvnDirection_id
					},
					success:function (response, options) {
						var result = Ext.util.JSON.decode(response.responseText);

						base_form.setValues(result[0]);
						
						if (result[0].EvnPrescrProc_id) {
							that.EvnPrescrProcDataPanel.show();
						}
						
						if (result[0].disabled) {
							base_form.findField('UslugaComplex_id').disable();					
						}
						
						var usluga_complex_id = result[0].UslugaComplex_id || false;
						if (usluga_complex_id) {
							base_form.findField('UslugaComplex_id').getStore().load({
								callback: function() {
									if ( base_form.findField('UslugaComplex_id').getStore().getCount() > 0 ) {
										base_form.findField('UslugaComplex_id').setValue(usluga_complex_id);
									}
									else {
										base_form.findField('UslugaComplex_id').clearValue();
									}
								}.createDelegate(this),
								params: {
									UslugaComplex_id: usluga_complex_id
								}
							});
						}
						
						var Org_sid = org_combo.getValue();
						var Lpu_sid = null;

						if (result[0].Lpu_sid != null) {
							Lpu_sid = result[0].Lpu_sid;
						}

						var prehosp_direct_id = prehosp_direct_combo.getValue();
						var lpu_section_did = lpu_section_combo.getValue();
					
						var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

						if ( record ) {
							var org_type = '';

							if (getRegionNick() == 'kz') {							
								switch ( parseInt(record.get('PrehospDirect_Code')) ) {
									case 8:
									case 9:
										org_type = 'lpu';
										break;
										
									default:
										org_type = 'org';
										break;
								}
							} else {							
								switch ( parseInt(record.get('PrehospDirect_Code')) ) {
									case 1:
									case 2:
										org_type = 'lpu';
										break;

									case 4:
										org_type = 'military';
										break;

									case 3:
									case 5:
									case 6:
										org_type = 'org';
										break;

									default:
										return false;
										break;
								}
							}
							
							var params = {OrgType: org_type};
							if ( org_type == 'lpu' && !(getRegionNick() == 'kz' && Org_sid!=null && parseInt(record.get('PrehospDirect_Code')) == 9) ) {
								params.Lpu_oid = Lpu_sid;
							} else {
								params.Org_id = Org_sid;
							}

							if ( org_type.length == 0 ) {
								lpu_section_combo.setValue(lpu_section_did);
							}
							else {
								org_combo.getStore().load({
									callback: function(records, options, success) {
										if (org_combo.getStore().getCount()>0) {
											var lpu_id = org_combo.getStore().getAt(0).get('Org_id');
											org_combo.setValue(lpu_id);

											lpu_section_combo.getStore().load({
												params:{Lpu_id:lpu_id},
												callback: function() {
													lpu_section_combo.setValue(lpu_section_did);
												}
											});
										}
									},
									params: params
								});
							}
							
							//lpu_section_combo.setValue(lpu_section_did);
														
							base_form.findField('MedPersonal_id').getStore().load(
							{
								params:
								{
									LpuSection_id: lpu_section_did,
									Lpu_id: Org_sid ||  getGlobalOptions().lpu_id
								},
								callback: function()
								{
									base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
								}
							});
							
						} else {
							if (Org_sid){
								lpu_section_combo.getStore().load({
									params:{Lpu_id:Org_sid},
									callback: function() {
										lpu_section_combo.setValue(lpu_section_combo.getValue());
									}
								});
							}
						}

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
						
						if (result[0].EvnDirection_IsAuto || (Lpu_sid !=  getGlobalOptions().lpu_id)) { // если автоматически созданное направление или направление от другого ЛПУ, делаем направление недоступным для редактирования 
							base_form.findField('EvnDirection_Num'   ).disable();
							base_form.findField('EvnDirection_setDT' ).disable();
							base_form.findField('PrehospDirect_id'   ).disable();
							base_form.findField('EvnLabRequest_Ward' ).disable();
							base_form.findField('Org_sid'            ).disable();
							base_form.findField('LpuSection_id'      ).disable();
							base_form.findField('MedPersonal_id'     ).disable();
							base_form.findField('EvnDirection_IsCito').disable();
 						}
						
						base_form.findField('EvnDirection_Num').focus(true, 250);

						var recurs = function(arr, actt){
							if(arr.items){
								arr.items.each(function(c){
									if(c.xtype && (c.isXType('field') || c.xtype.inlist(['swlpusectionglobalcombo']))){
										if(actt == 'setDisable') c.disable();
										else c.enable();
									}
									if(c.items){
										recurs(c, actt);
									}
								});
							}
						};
						
						if(action == 'view'){
							recurs(that, 'setDisable');
						} else {
							recurs(that, 'setEnable');
						}
						
						loadMask.hide();
					}.createDelegate(this),
					url:'/?c=EvnFuncRequestProc&m=getEvnProcRequest'
				});
				break;
		}
	},
	allowBlankDidDT: function (allowBlank) {
		var didDTfield = this.EvnProcRequestEditForm.getForm().findField('EvnPrescrProc_didDT');

		didDTfield.allowBlank = allowBlank;
		didDTfield.validate();
	}
});