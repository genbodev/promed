/**
* Форма "Протокол МСЭ"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      10.10.2011
*/

sw.Promed.swProtocolMseEditForm = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	action: null,
	onHide: Ext.emptyFn,
	EvnPrescrMse_id: null,
	EvnVK_id: null,
	layout: 'border',
	buttonAlign: "right",
	objectName: 'swProtocolMseEditForm',
	closeAction: 'hide',
	id: 'swProtocolMseEditForm',
	objectSrc: '/jscore/Forms/Mse/swProtocolMseEditForm.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.doSave();
			},
			iconCls: 'save16',
			text: langs('Сохранить')
		}, {
			handler: function()
			{
				var win = this.ownerCt,
					field = win.CommonForm.getForm().findField('EvnMse_id');
				
				if( field.getValue() == null || field.getValue() == '' ) {
					win.doSave(function() {
						win.buttons[0].setVisible(false);
						win.printEvnMse();
					}.createDelegate(win));
				} else {
					win.printEvnMse();
				}
			},
			iconCls: 'print16',
			text: BTN_FRMPRINT
		},
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : langs('Отмена'),
			tabIndex  : -1,
			tooltip   : langs('Отмена'),
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		hide: function(w){
			if (w.isAutosaved) {w.undoSave();}
			w.buttons[1].enable();
			w.CommonForm.getForm().reset();
			w.action = null;
			w.EvnPrescrMse_id = null;
			w.EvnVK_id = null;
			w.disableFormFields(false);
		}
	},

	undoSave: function() {
		var win = this;
		var b_f = win.CommonForm.getForm();
		win.getLoadMask("Отмена изменений...").show();
		Ext.Ajax.request({
			params: {EvnMse_id: b_f.findField('EvnMse_id').getValue()},
			url: '/?c=Mse&m=deleteEvnMse',
			callback: function(opt, success, resp)  {
				win.getLoadMask().hide();
			}
		});
	},
	
	show: function()
	{
		sw.Promed.swProtocolMseEditForm.superclass.show.apply(this, arguments);
		
		/** Параметры с которыми вызывается форма:
		*	
		*	action - необязательный (может определяться автоматом)
		*	Person_id - обязательный
		*	Server_id - обязательный
		*	EvnPrescrMse_id - необязательный
		*	EvnMse_id - необязательный (передавать только тогда когда нет направления на МСЭ EvnPrescrMse_id )
		*	MedService_id - необязательный
		*/
		
		if(!arguments[0]){
			sw.swMsg.alert(langs('Ошибка'), langs('Неверные параметры'));
			this.hide();
			return false;
		}
		
		if(arguments[0].action) {
			this.action = arguments[0].action;
		}

		this.onHide = Ext.emptyFn;
		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}

		var win = this;
		var b_f = win.CommonForm.getForm();
		
		this.isAutosaved = false;

		this.EvnPrescrMse_id = null;
		if (arguments[0].EvnPrescrMse_id) {
			this.EvnPrescrMse_id = arguments[0].EvnPrescrMse_id;
			b_f.findField('EvnPrescrMse_id').setValue(arguments[0].EvnPrescrMse_id);
		}

		this.EvnVK_id = null;
		if (arguments[0].EvnVK_id) {
			this.EvnVK_id = arguments[0].EvnVK_id;
			b_f.findField('EvnVK_id').setValue(arguments[0].EvnVK_id);
		}
				
		this.PersonFrame.personId = arguments[0].Person_id;
		this.PersonFrame.serverId = arguments[0].Server_id;
		
		win.getLoadMask(langs('Загрузка данных..')).show();

		this.EvnMse_id = null;
		if(arguments[0].EvnMse_id) {
			b_f.findField('EvnMse_id').setValue(arguments[0].EvnMse_id);
		}

		this.MedService_id = null;
		if (arguments[0].MedService_id) {
			this.MedService_id = arguments[0].MedService_id;
		}
		
		this.PersonIdFrame.setTitle('<div class="x-panel-collapsed-title">ИД пациента: ' + this.PersonFrame.personId+'</div>');
		this.PersonFrame.setTitle('...');
		this.PersonFrame.load({
			callback: function() {
				this.PersonFrame.setPersonTitle();
			}.createDelegate(this),
			Person_id: this.PersonFrame.personId,
			Server_id: this.PersonFrame.serverId
		});
		
		if (isMseDepers()) {
			this.PersonIdFrame.show();
			this.PersonFrame.hide();
		} else {
			this.PersonIdFrame.hide();
			this.PersonFrame.show();
		}
								
		win.SopDiagListPanel.reset();
		win.OslDiagListPanel.reset();
		win.EvnMseCategoryLifeTypePanel.removeAll({clearAll: true});
		
		var cur_date = new Date();
		
		this.defineFormParams(function(){
			b_f.load({
				url: '/?c=Mse&m=getEvnMse',
				params: {
					EvnMse_id: b_f.findField('EvnMse_id').getValue(),
					EvnPrescrMse_id: win.EvnPrescrMse_id,
					EvnVK_id: win.EvnVK_id
				},
				success: function(f, r){
					win.getLoadMask().hide();
					var obj = Ext.util.JSON.decode(r.response.responseText)[0];
					// Проставляем диагнозы

					if (obj.EPMDiag_id) {
						b_f.findField('Diag_id').setValue(obj.EPMDiag_id);
					}
					
					if (obj.EPMDiag_sid) {
						b_f.findField('Diag_sid').setValue(obj.EPMDiag_sid);
					}

					if (obj.EPMDiag_aid) {
						b_f.findField('Diag_aid').setValue(obj.EPMDiag_aid);
					}
									
					win.SopDiagListPanel.setValues(obj.SopDiagList);
					win.OslDiagListPanel.setValues(obj.OslDiagList);
					
					if (obj.Diag_bid) {
						b_f.findField('Diag_bid').setValue(obj.Diag_bid);
					}

					win.diagsSetValues();
					
					if (obj.EvnMse_DiagDetail) {
							b_f.findField('EvnMse_DiagDetail').setValue(obj.EvnMse_DiagDetail);
					}
					if (obj.EvnMse_DiagSDetail) {
							b_f.findField('EvnMse_DiagSDetail').setValue(obj.EvnMse_DiagSDetail);
					}
					if (obj.EvnMse_DiagADetail) {
							b_f.findField('EvnMse_DiagADetail').setValue(obj.EvnMse_DiagADetail);
					}
					if (obj.Diag_bid) {
							b_f.findField('Diag_bid').setValue(obj.Diag_bid);
					}
					if (obj.EvnMse_DiagBDetail) {
							b_f.findField('EvnMse_DiagBDetail').setValue(obj.EvnMse_DiagBDetail);
					}
					if (obj.EvnMse_SendStickDetail) {
							b_f.findField('EvnMse_SendStickDetail').setValue(obj.EvnMse_SendStickDetail);
					}		
					
					Ext.QuickTips.register({
						target: b_f.findField('EvnMse_DiagDetail').getEl(),
						text: obj.EvnMse_DiagDetail,
						enabled: true,
						showDelay: 5,
						trackMouse: true,
						autoShow: true
					});	
					
					// <!-- Вид нарушения
					var healthabnorm_combo = b_f.findField('HealthAbnorm_id');
					healthabnorm_combo.getStore().each(function(rec){
						if(rec.get('HealthAbnorm_id') == healthabnorm_combo.getValue()){
							var idx = healthabnorm_combo.getStore().indexOf(rec)+1;
							healthabnorm_combo.fireEvent('select', healthabnorm_combo, rec, idx);
						}
					});
					// -->
					
					// <!-- Категория жизнедеятельности
					if(!Ext.isEmpty(b_f.findField('EvnMse_id').getValue())) {
						win.EvnMseCategoryLifeTypePanel.loadData({
							params: {EvnMse_id: b_f.findField('EvnMse_id').getValue()},
							globalFilters: {EvnMse_id: b_f.findField('EvnMse_id').getValue()},
						});
					}
					// -->
					
					// <!-- Установлена инвалидность
					var invalidgrouptype_combo = b_f.findField('InvalidGroupType_id');
					invalidgrouptype_combo.getStore().each(function(rec){
						if(rec.get('InvalidGroupType_id') == invalidgrouptype_combo.getValue()){
							var idx = invalidgrouptype_combo.getStore().indexOf(rec);
							invalidgrouptype_combo.fireEvent('select', invalidgrouptype_combo, rec, idx);
						}
					});
					// -->
					
					switch(win.action){
						case 'add':
							win.setTitle(langs('Обратный талон: добавление'));
							if (win.MedService_id != null) {
								b_f.findField('MedService_id').setValue(win.MedService_id);
							}
							
							b_f.findField('EvnMse_SendStickDate').setValue(cur_date);
							b_f.findField('EvnMse_ProfDisabilityStartDate').setValue(cur_date);
							//win.buttons[1].disable();
						break;
						case 'edit':
							win.setTitle(langs('Обратный талон: редактирование'));
						break;
						case 'view':
							win.setTitle(langs('Обратный талон: просмотр'));
							b_f.findField('EvnMse_setDT').focus(true, 100);
							win.disableFormFields(true);
						break;
					}
					b_f.findField('EvnMse_setDT').focus(true, 100);
				},
				failure: function(){
					win.getLoadMask().hide();
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось загрузить данные для формы!'));
					win.hide();
					return false;
				}
			});
		});
	},
	
	doSave: function(cb)
	{
		var win = this;
		var frm = this.CommonForm.getForm();
		if(!frm.isValid() && !cb){
			sw.swMsg.alert(langs('Ошибка'), langs('Заполнены не все обязательные поля!<br />Обязательные к заполнению поля выделены особо.'));
			return false;
		}
		var params = {};
		params.EvnMse_NumAct = frm.findField('EvnMse_NumAct').getValue();
		params.PersonEvn_id = this.PersonFrame.getFieldValue('PersonEvn_id');
		params.Server_id = this.PersonFrame.getFieldValue('Server_id');
		params.SopDiagList = Ext.util.JSON.encode(this.SopDiagListPanel.getValues());
		params.OslDiagList = Ext.util.JSON.encode(this.OslDiagListPanel.getValues());

		if (frm.findField('Diag_id').disabled) {
			params.Diag_id = frm.findField('Diag_id').getValue();
		}

		if (frm.findField('Diag_sid').disabled) {
			params.Diag_sid = frm.findField('Diag_sid').getValue();
		}

		if (frm.findField('Diag_aid').disabled) {
			params.Diag_aid = frm.findField('Diag_aid').getValue();
		}
		
		win.getLoadMask(langs('Сохранение данных...')).show();
		frm.submit({
			clientValidation: false,
			params: params,
			success: function( form, action ) {
				win.getLoadMask().hide();
				if( cb ) {
					frm.findField('EvnMse_id').setValue(action.result.EvnMse_id);
					win.isAutosaved = true;
					cb();
				} else {
					win.isAutosaved = false;
					win.hide();
					win.onHide();
				}
			},
			failure: function() {
				win.getLoadMask().hide();
				//sw.swMsg.alert(langs('Ошибка'), langs('Не удалось сохранить данные!'));
			}
		});
	},
	
	defineFormParams: function(callback)
	{
		var win = this,
			b_f = win.CommonForm.getForm(),
			cur_date = Ext.util.Format.date(Date(), 'd.m.Y');
			
		Ext.Ajax.request({
			url: '/?c=Mse&m=defineEvnMseFormParams',
			params: {
				EvnMse_id: b_f.findField('EvnMse_id').getValue(),
				EvnPrescrMse_id: win.EvnPrescrMse_id,
				EvnVK_id: win.EvnVK_id
			},
			callback: function(o, s, r){
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText)[0];
					if(obj.EvnMse_ImportedCouponGUID != null) {
						win.action = 'view';
					} else if(win.action == null){
						if(obj.EvnMse_id != null) {
							win.action = 'edit';
						} else {
							win.action = 'add';
						}
					}
					b_f.setValues(obj);
					callback();
				} else {
					win.getLoadMask().hide();
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось определить параметры формы!'));
					win.hide();
					return false;
				}
			}
		});
	},
	
	disableFormFields: function(isView)
	{
		this.findBy(function(field){
			if(field.xtype && !field.xtype.inlist(['panel', 'fieldset'])){
				if(isView)
					field.disable();
				else
					field.enable();
			}
		});
		
		this.EvnMseCategoryLifeTypePanel.setReadOnly(isView);
		
		if(isView){
			this.SopDiagListPanel.disable();
			this.OslDiagListPanel.disable();
			this.buttons[0].setVisible(false);
		} else {
			this.SopDiagListPanel.enable();
			this.OslDiagListPanel.enable();
			this.buttons[0].setVisible(true);
		}
	},
	
	diagsSetValues: function()
	{
		var diagFset = this.PatientForm.find('xtype', 'fieldset')[0];
		diagFset.findBy(function(field){
			if(field.xtype == 'swdiagcombo' && field.getValue() != '' && field.getValue() != null){
				field.getStore().load({
					params: { where: "where Diag_id = " + field.getValue() },
					callback: function(){
						field.getStore().each(function(rec){
							if(rec.get('Diag_id') == field.getValue())
								field.fireEvent('select', field, rec, 0);
						});
					}
				});
			}
		});
	},
	
	printEvnMse: function()
	{
		var field = this.CommonForm.getForm().findField('EvnMse_id');
		if(field.getValue()==null) return false;
		if ( getRegionNick() == 'kz' ) {
			printBirt({
				'Report_FileName': 'NotificationConclusionMSE.rptdesign',
				'Report_Params': '&paramEvnMse_id=' + field.getValue(),
				'Report_Format': 'pdf'
			});
		}
		else {
			var lm = this.getLoadMask(langs('Выполняется печать талона...'));
			lm.show();
			Ext.Ajax.request({
				url: '/?c=Mse&m=printEvnMse',
				params: {
					EvnMse_id: field.getValue(),
					isMseDepers: isMseDepers() ? 1 : 0,
				},
				callback: function(o, s, r){
					lm.hide();
					if(s){
						openNewWindow(r.responseText);
					}
				}.createDelegate(this)
			});
		}
	},

	initComponent: function()
	{
		var cur_win = this;

		this.PersonIdFrame = new sw.Promed.Panel({
			floatable: false,
			collapsed: true,
			region: 'north',
			title: '...',
			collapsible: false
		});		

		this.PersonFrame = new sw.Promed.PersonInfoPanel({
			floatable: false,
			collapsed: true,
			region: 'north',
			title: langs('Загрузка...'),
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			titleCollapse: true,
			collapsible: true
		});		

		this.SopDiagListPanel = new sw.Promed.DiagListPanelWithDescr({
			win: this,
			width: 1200,
			buttonAlign: 'left',
			labelAlign: 'top',
			buttonLeftMargin: 0,
			labelWidth: 140,
			fieldWidth: 270,
			showOsl: getRegionNick() != 'kz',
			showDescr: getRegionNick() != 'kz',
			style: 'background: transparent; margin: 0; padding: 0;',
			fieldLabel: 'Сопутствующие заболевания по МКБ',
			fieldDescLabel: getRegionNick() == 'ufa' ? 'Уточнение для сопутствующего заболевания':'Сопутствующие заболевания',
			onChange: function() {
				
			}
		});
		
		this.OslDiagListPanel = new sw.Promed.DiagListPanelWithDescr({
			win: this,
			width: 1200,
			buttonAlign: 'left',
			labelAlign: 'top',
			buttonLeftMargin: 0,
			labelWidth: 140,
			fieldWidth: 270,
			showDescr: true,
			style: 'background: transparent; margin: 0; padding: 0;',
			fieldLabel: 'Осложнения основного заболевания по МКБ',
			fieldDescLabel: getRegionNick() == 'ufa' ? 'Уточнение для осложнения основного заболевания по МКБ':'Осложнения основного заболевания',
			onChange: function() {
				
			}
		});
		
		this.PatientForm = new sw.Promed.Panel({
			title: langs('Пациент'),
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			items: [
				{
					layout: 'column',
					border: false,
					defaults: {
						border: false
					},
					labelAlign: 'right',
					items: [
						{
							layout: 'form',
							labelWidth: 200,
							items: [
								{
									xtype: 'hidden',
									name: 'EvnMse_id'
								}, {
									xtype: 'hidden',
									name: 'EvnPrescrMse_id'
								}, {
									xtype: 'hidden',
									name: 'EvnVK_id'
								}, {
									xtype: 'hidden',
									name: 'MedService_id'
								}, {
									xtype: 'swdatefield',
									allowBlank: false,
									name: 'EvnMse_setDT',
									fieldLabel: langs('Дата освидетельствования')
								}
							]
						},
						{
							layout: 'form',
							labelWidth: 300,
							width: 380,
							items: [
								{
									xtype: 'textfield',
									anchor: '100%',
									readOnly: true,
									disabled: true,
									name: 'EvnMse_NumAct',
									fieldLabel: langs('Номер акта медико-социальной экспертизы')
								}
							]
						}
					]
				},
				{
					xtype: 'fieldset',
					layout: 'form',
					autoHeight: true,
					labelWidth: 260,
					labelAlign: 'right',
					width: 1200,
					title: langs('Диагноз федерального государственного учреждения медико-социальной экспертизы'),
					collapsible: true,
					items: [
						{
							xtype: 'swdiagcombo',
							anchor: '100%',
							allowBlank: false,
							hiddenName: 'Diag_id',
							fieldLabel: langs('Код основного заболевания по МКБ')
						},
 						{
							xtype: 'textfield',
							anchor: '100%',
							allowBlank: true,
							name: 'EvnMse_DiagDetail',
							maxLength: 255,
							fieldLabel: getRegionNick() == 'ufa' ? langs('Уточнение основного заболевания по МКБ') : langs('Основное заболевание'),
							listeners: {
								change: function(c,v) {
								  Ext.QuickTips.register({
									target: c.getEl(),
									text: v,
									enabled: true,
									showDelay: 5,
									trackMouse: true,
									autoShow: true
								  });
								}
							}
						},
						this.OslDiagListPanel,
						this.SopDiagListPanel,
						{
							border: false,
							hidden: true,
							layout: 'form',
							xtype: 'panel',
							items: [{
								xtype: 'swdiagcombo',
								anchor: '100%',
								hiddenName: 'Diag_sid',
								hideLabel: true,
								fieldLabel: langs('Сопутствующее заболевание по МКБ')
							}]
						},
 						{
							xtype: 'textfield',
							anchor: '100%',
							allowBlank: true,
							name: 'EvnMse_DiagSDetail',
							hidden: true,
							hideLabel: true,
							maxLength: 500,
							fieldLabel: langs('Уточнение для сопутствующего заболевания по МКБ')
						},						
						{
							border: false,
							hidden: getRegionNick() != 'ufa',
							layout: 'form',
							xtype: 'panel',
							items: [{
								xtype: 'swdiagcombo',
								anchor: '100%',
								hiddenName: 'Diag_aid',
								hidden: getRegionNick() != 'ufa',
								hideLabel: getRegionNick() != 'ufa',
								fieldLabel: langs('Осложнение основного заболевания')
							}]
						},
						{
							border: false,
							hidden: getRegionNick() != 'ufa',
							layout: 'form',
							xtype: 'panel',
							items: [{
								xtype: 'textfield',
								anchor: '100%',
								hidden: true,
								hideLabel: true,
								allowBlank: true,
								name: 'EvnMse_DiagADetail',
								maxLength: 500,
								fieldLabel: langs('Уточнение для осложнения основного заболевания по МКБ')
							},	
							{
								xtype: 'swdiagcombo',
								anchor: '100%',
								hidden: true,
								hideLabel: true,
								hiddenName: 'Diag_bid',
								fieldLabel: langs('Осложнение сопутствующего заболевания по МКБ')
							},
							{
								xtype: 'textfield',
								anchor: '100%',
								allowBlank: true,
								name: 'EvnMse_DiagBDetail',
								maxLength: 500,
								fieldLabel: langs('Уточнение для осложнения сопутствующего заболевания')
							}]
						},
					]
				},
				{
					xtype: 'fieldset',
					layout: 'column',
					autoHeight: true,
					labelAlign: 'right',
					width: 800,
					defaults: {
						border: false
					},
					title: langs('Виды нарушений функций организма и степень их выраженности'),
					collapsible: true,
					items: [
						{
							layout: 'form',
							width: 370,
							labelWidth: 100,
							items: [
								{
									xtype: 'swhealthabnormcombo',
									anchor: '100%',
									listWidth: 400,
									hiddenName: 'HealthAbnorm_id',
									listeners: {
										render: function(c)
										{
											c.getStore().filterBy(function(rec){
												var filter_flag = true;
												if(!rec.get('HealthAbnorm_id').inlist([2,8,22,58,225,243,282]))
													filter_flag = false;
												return filter_flag;
											});
											c.getStore().loadData(getStoreRecords(c.getStore()));
										},
										select: function(combo, r, idx)
										{
											var controlField = this.CommonForm.getForm().findField('HealthAbnormDegree_id');
											if(combo.getValue() != '' && combo.getValue() != null) {
												controlField.enable();
												controlField.allowBlank = false;
											} else {
												controlField.clearValue();
												controlField.allowBlank = true;
												controlField.disable();
											}
											controlField.validate();
										}.createDelegate(this)
									},
									fieldLabel: langs('Вид нарушения')
								}
							]
						},
						{
							layout: 'form',
							labelWidth: 150,
							width: 405,
							items: [
								{
									xtype: 'swbaselocalcombo',
									anchor: '100%',
									mode: 'local',
									disabled: true,
									editable: false,
									listWidth: 300,
									triggerAction: 'all',
									store: new Ext.db.AdapterStore({
										autoLoad: true,
										dbFile: 'Promed.db',
										fields: [
											{name: 'HealthAbnormDegree_id', mapping: 'HealthAbnormDegree_id'},
											{name: 'HealthAbnormDegree_Name', mapping: 'HealthAbnormDegree_Name'},
											{name: 'HealthAbnormDegree_Code', mapping: 'HealthAbnormDegree_Code'}
										],
										key: 'HealthAbnormDegree_id',
										sortInfo: {field: 'HealthAbnormDegree_Code'},
										tableName: 'HealthAbnormDegree'
									}),
									hiddenName: 'HealthAbnormDegree_id',
									valueField: 'HealthAbnormDegree_id',
									displayField: 'HealthAbnormDegree_Name',
									fieldLabel: langs('Степень выраженности')
								}
							]
						}
					]
				}
			]
		});
		
		this.EvnMseCategoryLifeTypePanel = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			height: 150,
			region: 'center',
			border: false,
			obj_isEvn: false,
			object: 'EvnMseCategoryLifeTypeLink',
			editformclassname: 'swEvnMseCategoryLifeTypeEditWindow',
			actions: [
				{ name: 'action_add', handler: function () {
					var b_f = cur_win.CommonForm.getForm(),
						action = function() {
							getWnd('swEvnMseCategoryLifeTypeEditWindow').show({
								EvnMse_id: b_f.findField('EvnMse_id').getValue(),
								action: 'add',
								onHide: function() {		
									cur_win.EvnMseCategoryLifeTypePanel.loadData({
										params: {EvnMse_id: b_f.findField('EvnMse_id').getValue()},
										globalFilters: {EvnMse_id: b_f.findField('EvnMse_id').getValue()}
									});
								}
							});
						};
					if (Ext.isEmpty(b_f.findField('EvnMse_id').getValue())) {
						cur_win.doSave(function() {
							action();
						}.createDelegate(cur_win));
					} else {
						action();
					}
				}},
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'EvnMseCategoryLifeTypeLink_id', type: 'int', hidden: true, key: true },
				{ name: 'EvnMse_id', type: 'int', hidden: true, isparams: true },
				{ name: 'CategoryLifeType_id', type: 'int', hidden: true, isparams: true },
				{ name: 'CategoryLifeTypeLink_id', type: 'int', hidden: true, isparams: true },
				{ name: 'CategoryLifeType_Name', type: 'string', header: 'Категория жизнедеятельности', width: 200 },
				{ name: 'CategoryLifeDegreeType_Name', type: 'string', header: 'Степень выраженности', width: 150 },
				{ name: 'CategoryLifeTypeLink_Name', type: 'string', header: 'Описание степени выраженности', width: 200, id: 'autoexpand' }
			],
			paging: false,
			dataUrl: '/?c=Mse&m=loadEvnMseCategoryLifeTypeLink',
			totalProperty: 'totalCount'
		});
				
		this.DecisionFSIForm = new sw.Promed.Panel({
			title: langs('Решение Федерального государственного учреждения медико-социальной экспертизы'),
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			items: [
				{
					layout: 'column',
					border: false,
					defaults: {
						border: false
					},
					labelAlign: 'right',
					items: [
						{
							layout: 'form',
							labelWidth: 180,
							width: 400,
							items: [
								{
									xtype: 'swinvalidgrouptypecombo',
									editable: false,
									allowBlank: false,
									mode: 'local',
									triggerAction: 'all',
									hiddenName: 'InvalidGroupType_id',
									fieldLabel: langs('Установлена инвалидность'),
									listeners: {
										select: function(c, r, idx)
										{
											var controlField1 = this.CommonForm.getForm().findField('InvalidCouseType_id');
											var controlField2 = this.CommonForm.getForm().findField('InvalidRefuseType_id');
											switch(idx)
											{
												case 0:
													controlField1.reset();
													controlField1.allowBlank = true;
													controlField1.disable();
													controlField2.enable();
													controlField2.allowBlank = false;
												break;
												default:
													controlField1.enable();
													controlField1.allowBlank = false;
													controlField2.allowBlank = true;
													controlField2.disable();
													controlField2.reset();
												break;
											}
											controlField1.validate();
											controlField2.validate();

											this.CommonForm.getForm().findField('ProfDisabilityPeriod_id').setAllowBlank(getRegionNick() == 'kz' || idx == 0);
										}.createDelegate(this)
									}
								}
							]
						},
						{
							layout: 'form',
							labelWidth: 180,
							width: 400,
							items: [
								{
									comboSubject: 'InvalidCouseType',
									xtype: 'swcommonsprcombo',
									anchor: '100%',
									listWidth: 500,
									disabled: true,
									hiddenName: 'InvalidCouseType_id',
									fieldLabel: langs('Причина инвалидности')
								}
							]
						}
					]
				},
				{
					layout: 'form',
					labelWidth: 500,
					width: 650,
					border: false,
					labelAlign: 'right',
					items: [
						{
							xtype: 'numberfield',
							anchor: '100%',
							minValue: 0,
							maxValue: 100,
							name: 'EvnMse_InvalidPercent',
							fieldLabel: langs('Степень утраты профессиональной трудоспособности в процентах')
						}, 
						{
							comboSubject: 'ProfDisabilityPeriod',
							xtype: 'swcommonsprcombo',
							anchor: '100%',
							hidden: getRegionNick() == 'kz',
							hideLabel: getRegionNick() == 'kz',
							hiddenName: 'ProfDisabilityPeriod_id',
							fieldLabel: langs('Срок, на который установлена степень утраты профессиональной трудоспособности'),
							listeners: {
								select: function(combo, r, idx) {
									var dateField = this.CommonForm.getForm().findField('EvnMse_ProfDisabilityEndDate');
									var val = combo.getValue();
									var dt = new Date();
									if (val) {
										switch(val) {
											case 1: // 6  мес
												dt.setMonth(dt.getMonth() + 6);
												break;
											case 2: // 1 год
												dt.setFullYear(dt.getFullYear() + 1);
												break;
											case 3: // 2 года
												dt.setFullYear(dt.getFullYear() + 2);
												break;
											default:
												dt = null;
												break;
										}
										dateField.setValue(dt);
									}
								}.createDelegate(this)
							}
						}, 
						{
							xtype: 'swdatefield',
							name: 'EvnMse_ProfDisabilityStartDate',
							hidden: getRegionNick() == 'kz',
							hideLabel: getRegionNick() == 'kz',
							fieldLabel: langs('Дата, с которой установлена степень утраты профессиональной трудоспособности')
						}, {
							xtype: 'swdatefield',			
							name: 'EvnMse_ProfDisabilityEndDate',
							hidden: getRegionNick() == 'kz',
							hideLabel: getRegionNick() == 'kz',
							fieldLabel: langs('Дата, до которой установлена степень утраты профессиональной трудоспособности')
						}
					]
				},
				{
					layout: 'column',
					border: false,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							labelWidth: 200,
							width: 320,
							labelAlign: 'right',
							items: [
								{
									xtype: 'swdatefield',
									name: 'EvnMse_ReExamDate',
									fieldLabel: langs('Дата переосвидетельствования')
								}
							]
						},
						{
							layout: 'form',
							labelWidth: 280,
							width: 480,
							labelAlign: 'right',
							items: [
								{
									comboSubject: 'InvalidRefuseType',
									xtype: 'swcommonsprcombo',
									anchor: '100%',
									listWidth: 500,
									disabled: true,
									name: 'InvalidRefuseType_id',
									fieldLabel: langs('Причины отказа в установлении инвалидности')
								}
							]
						}
					]
				},
 				{
					layout: 'column',
 					border: false,
					defaults: {
						border: false
					},
 					items: [
 						{
							layout: 'form',
							labelWidth: 200,
							width: 320,
							border: false,
							labelAlign: 'right',
							items: [
								{
									xtype: 'swdatefield',
								allowBlank: false,
									name: 'EvnMse_SendStickDate',
									fieldLabel: langs('Дата отправки обратного талона')
								}															
							]
						},
						{
							layout: 'form',
							labelWidth: 280,
							width: 480,
							labelAlign: 'right',
							items: [
								{
									xtype: 'textfield',
									anchor: '100%',
									allowBlank: true,
									name: 'EvnMse_SendStickDetail',
									hidden: getRegionNick() != 'ufa',
									hideLabel: getRegionNick() != 'ufa',
									maxLength: 500,
									fieldLabel: langs('Уточнение причин отказа в установлении инвалидности')
								}
							]
						}						
 					]
 				},
				{
					layout: 'form',
					labelWidth: 550,
					width: 800,
					border: false,
					labelAlign: 'right',
					items: [
						{
							xtype: 'textfield',
							anchor: '100%',
							fieldLabel: 'Руководитель бюро/экспертного состава, в котором проводилась медико-социальная экспертиза',
							name: 'EvnMse_HeadStaffMse'
						}, 
						{
							xtype: 'hidden',
							name: 'MedServiceMedPersonal_id'
						}
					]
				}
			]
		});
		
		this.RecommendationsForm = new sw.Promed.Panel({
			title: langs('Рекомендации'),
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			items: [
				{
					layout: 'form',
					labelAlign: 'right',
					width: 800,
					border: false,
					labelWidth: 300,
					items: [
						{
							xtype: 'textarea',
							anchor: '100%',
							maxLength: 256,
							name: 'EvnMse_MedRecomm',
							fieldLabel: langs('Рекомендации по медицинской реабилитации')
						},
						{
							xtype: 'textarea',
							anchor: '100%',
							maxLength: 256,
							name: 'EvnMse_ProfRecomm',
							fieldLabel: langs('Рекомендации по профессиональной, социальной, психолого-педагогической реабилитации')
						}
					]
				}
			]
		});
		
		
		this.CommonForm = new Ext.form.FormPanel({
			region: 'center',
			autoScroll: true,
			url: '/?c=Mse&m=saveEvnMse',
			layout: 'form',
			items: [
				this.PersonIdFrame,
				this.PatientForm,
				new sw.Promed.Panel({
					title: langs('Ограничения основных категорий жизнедеятельности и степень их выраженности'),
					collapsible: true,
					items: [
						this.EvnMseCategoryLifeTypePanel
					]
				}),
				this.DecisionFSIForm,
				this.RecommendationsForm
			],
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[
				{ name: 'EvnMse_id' },
				{ name: 'EvnPrescrMse_id' },
				{ name: 'EvnVK_id' },
				{ name: 'MedService_id' },
				{ name: 'EvnMse_setDT' },
				{ name: 'EvnMse_NumAct' },
				{ name: 'Diag_id' },
				{ name: 'Diag_sid' },
				{ name: 'Diag_aid' },
				{ name: 'HealthAbnorm_id' },
				{ name: 'HealthAbnormDegree_id' },
				{ name: 'CategoryLifeType_id' },
				{ name: 'CategoryLifeDegreeType_id' },
				{ name: 'InvalidGroupType_id' },
				{ name: 'InvalidCouseType_id' },
				{ name: 'EvnMse_InvalidPercent' },
				{ name: 'ProfDisabilityPeriod_id' },
				{ name: 'EvnMse_ProfDisabilityStartDate' },
				{ name: 'EvnMse_ProfDisabilityEndDate' },
				{ name: 'EvnMse_ReExamDate' },
				{ name: 'InvalidRefuseType_id' },
				{ name: 'EvnMse_SendStickDate' },
				{ name: 'EvnMse_HeadStaffMse' },
				{ name: 'MedServiceMedPersonal_id' },
				{ name: 'EvnMse_MedRecomm' },
				{ name: 'EvnMse_ProfRecomm' }
			])
		});
	
		Ext.apply(this,
		{
			items: [
				this.PersonFrame,
				this.CommonForm
			]
		});
		sw.Promed.swProtocolMseEditForm.superclass.initComponent.apply(this, arguments);
	}
});