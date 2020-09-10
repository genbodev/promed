/**
* swTreatmentSearchWindow - окно поиска обращений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      4.08.2010
* @comment      Префикс для id компонентов ETSF (EvnTreatmentSearchForm). TABINDEX_ETSF
*/

sw.Promed.swTreatmentSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	maximized: true,
	modal: false,
	plain: true,
	pmUser_Name: null,
	resizable: false,
	width : 800,
	height : 550,
	autoHeight: false,
	border : false,
	layout: 'border',
	id: 'EvnTreatmentSearchWindow',
	title: lang['registratsiya_obrascheniy_poisk'],
        NotificationFor: [1],
        TreatmentList: [],
        playNotification: function()
        {
            Ext.get('swTreatmentSearchWindowNotification').dom.play();
        },
	initComponent: function() {
		var current_window = this;

		current_window.curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType;

		this.dateRegRange = new Ext.form.DateRangeField(
		{
			width: 160,
			testId: 'ETSF_Treatment_DateReg',
			tabIndex: TABINDEX_ETSF + 1,
			fieldLabel: lang['diapazon_dat_registratsii'],
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		this.dateReviewRange = new Ext.form.DateRangeField(
		{
			width: 160,
			testId: 'ETSF_Treatment_DateReview',
			tabIndex: TABINDEX_ETSF + 15,
			fieldLabel: lang['diapazon_dat_rassmotreniya'],
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			],
			listeners: {
				'select': function(date_field, date){
					if ( date_field.getValue1() ) {
						Ext.getCmp('ETSF_TreatmentReview_id').setValue(2);
					}
				}
			}
		});

		Ext.apply(this, {
			buttonAlign : "right",
			buttons : [{
				handler: function() {
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				tabIndex: TABINDEX_ETSF + 17,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.ownerCt.doReset(true);
				},
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ETSF + 18,
				text: BTN_FRMRESET
			},
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_PRIVSF + 80),
			{
				text : lang['zakryit'],
				iconCls: 'close16',
				tabIndex: TABINDEX_ETSF + 19,
				handler : function(button, event) {
					button.ownerCt.hide();
				}
			}],
			items : [
				new Ext.form.FormPanel({
					animCollapse: false,
					autoHeight: true,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					region: 'north',
					buttonAlign: 'left',
					frame: false,
					id: 'EvnTreatmentSearchForm',
					labelAlign: 'right',
					labelWidth: 180,
					title: lang['parametryi_poiska'],
					items: [{
						autoHeight: true,
						style: 'padding: 0px;',
						title: lang['registratsiya'],
						width: 820,
						xtype: 'fieldset',
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									id: 'ETSF_Treatment_setDateReg',
									name: 'Treatment_setDateReg',
									xtype: 'hidden'
								},
								this.dateRegRange]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 140,
								items: [
									new sw.Promed.SwProMedUserCombo({
										fieldLabel: lang['sozdatel'],
										allowBlank: true,
										disabled: false,
										id: 'ETSF_pmUser_id',
										hiddenName: 'pmUser_id',
										name : "pmUser_id",
										width : 180,
										tabIndex: TABINDEX_ETSF + 3
								}),
								{
									fieldLabel: lang['nomer_registratsii'],
									allowBlank: true,
									disabled: false,
									tabIndex: TABINDEX_ETSF + 4,
									maxLength: 50,
									id: 'ETSF_Treatment_Reg',
									name: 'Treatment_Reg',
									width: 180,
									value: '', //default value
									xtype: 'numberfield',
									allowNegative: false,
									allowDecimals: false,
									decimalPrecision: 0
								}]
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: lang['obraschenie'],
						width: 820,
						xtype: 'fieldset',
						items: [
						{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [
								{
									fieldLabel: lang['srochnost'],
									allowBlank: true,
									disabled: false,
									tabIndex: TABINDEX_ETSF + 5,
									width: 120,
									value: '',
									comboSubject: 'TreatmentUrgency',
									idPrefix: 'ETSF_',
									xtype: 'swtreatmentcombo'
								},
								{
									fieldLabel: lang['kratnost_obrascheniya'],
									allowBlank: true,
									disabled: false,
									comboSubject: 'TreatmentMultiplicity',
									idPrefix: 'ETSF_',
									tabIndex: TABINDEX_ETSF + 6,
									width: 120,
									value: '',
									xtype: 'swtreatmentcombo'
								},
								{
									fieldLabel: lang['tip_obrascheniya'],
									allowBlank: true,
									disabled: false,
									comboSubject: 'TreatmentType',
									idPrefix: 'ETSF_',
									tabIndex: TABINDEX_ETSF + 7,
									width: 120,
									value: '',
									xtype: 'swtreatmentcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 240,
								items: [
								{
									fieldLabel: lang['tip_initsiatora_obrascheniya'],
									allowBlank: true,
									disabled: false,
									comboSubject: 'TreatmentSenderType',
									idPrefix: 'ETSF_',
									tabIndex: TABINDEX_ETSF + 8,
									width: 255,
									value: '',
									xtype: 'swtreatmentcombo'
								},
								{
									fieldLabel: 'Инициатор обращения',
									allowBlank: true,
									disabled: false,
									tabIndex: TABINDEX_ETSF + 9,
									maxLength: 255,
									name: 'Person_id',
									id: 'ETSF_Person_id',
									width: 255,
									xtype: 'swpersoncombo',
									onTrigger1Click: function() {
										var combo = this;
										getWnd('swPersonSearchWindow').show({
											autoSearch: false,
											onSelect: function(personData) {
												if ( personData.Person_id > 0 ) {
													var PersonSurName_SurName = !personData.PersonSurName_SurName?'':personData.PersonSurName_SurName;
													var PersonFirName_FirName = !personData.PersonFirName_FirName?'':personData.PersonFirName_FirName;
													var PersonSecName_SecName = !personData.PersonSecName_SecName?'':personData.PersonSecName_SecName;

													combo.getStore().loadData([{
														Person_id: personData.Person_id,
														Person_Fio: PersonSurName_SurName + ' ' + PersonFirName_FirName + ' ' + PersonSecName_SecName
													}]);
													combo.setValue(personData.Person_id);
													combo.collapse();
													combo.focus(true, 500);
													combo.fireEvent('change', combo);
												}
												getWnd('swPersonSearchWindow').hide();
											},
											onClose: function() {combo.focus(true, 500)}
										});
									},
									onTrigger2Click: function() {
										this.clearValue();
										Ext.getCmp('ETSF_Treatment_SenderDetails').setDisabled(false);
									},
									enableKeyEvents: true,
									listeners: {
										'change': function(combo) {
											Ext.getCmp('ETSF_Treatment_SenderDetails').setDisabled(!!combo.getValue());
											if (!!combo.getValue()) {
												Ext.getCmp('ETSF_Treatment_SenderDetails').setValue('');
											}
										}
									}
								},
								{
									fieldLabel: 'Инициатор обращения (ручной ввод)',
									allowBlank: true,
									disabled: false,
									tabIndex: TABINDEX_ETSF + 9,
									maxLength: 255,
									name: 'Treatment_SenderDetails',
									id: 'ETSF_Treatment_SenderDetails',
									width: 255,
									value: '',
									xtype: 'textfield'
								},
								{
									fieldLabel: lang['sposob_polucheniya_obrascheniya'],
									comboSubject: 'TreatmentMethodDispatch',
									id: 'ETSF_TreatmentMethodDispatch_id',
									allowBlank: true,
									tabIndex: TABINDEX_ETSF + 10,
									width: 255,
									xtype: 'swcommonsprcombo'
								}]
							}]
						},
						{
							width: 620,
							fieldLabel: lang['kategoriya_obrascheniya'],
							comboSubject: 'TreatmentCat',
							allowBlank: true,
							id: 'ETSF_TreatmentCat_id',
							tabIndex: TABINDEX_ETSF + 11,
							xtype: 'swcommonsprcombo'
						},
						{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['adresat_obrascheniya'],
									comboSubject: 'TreatmentRecipientType',
									id: 'ETSF_TreatmentRecipientType_id',
									allowBlank: true,
									tabIndex: TABINDEX_ETSF + 12,
									//emptyText: langs('ЛПУ'), // myerror
									width: 200,
									validateOnBlur: true,
									xtype: 'swcommonsprcombo',
									listeners: {
										'beforeselect': function(combo, record){
											if(current_window.curArm.inlist(['lpuadmin', 'mstat', 'regpol'])) return;
											var rectype = record.get(combo.valueField) || null;
											if (rectype == 1)
											{
												current_window.enableField('ETSF_Lpu_rid');
												Ext.getCmp('ETSF_Lpu_rid').focus(true, 250);
											}
											else
											{
												current_window.disableField('ETSF_Lpu_rid', true );
											}
											//combo.setRawValue(record.get(combo.codeField) + ". " + record.get(combo.display));
										}
									}
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{ // Обязательное, если «Адресат обращения» = ЛПУ. В противном случае пустое и недоступное.
									allowBlank: true,
									disabled: true,
									tabIndex: TABINDEX_ETSF + 13,
									width: 420,
									hidden : false,
									hideLabel : true,
									emptyText: lang['lpu_adresat_obrascheniya'], // myerror
									autoLoad: true,
									id: 'ETSF_Lpu_rid',
									hiddenName: 'Lpu_rid',
									xtype: 'swlpulocalcombo'
								}]
							}]
						},
						{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [this.dateReviewRange]
							}, {
								border: false,
								layout: 'form',
								labelWidth: 200,
								items: [{
									fieldLabel: langs('Статус обращения'),
									allowBlank: true,
									disabled: false,
									comboSubject: 'TreatmentReview',
									tabIndex: TABINDEX_ETSF + 14,
									width: 180,
									value: '', //default value
									idPrefix: 'ETSF_',
									id: 'ETSF_TreatmentReview_id',
									xtype: 'swtreatmentcombo',
									listeners: {
										'beforeselect': function(combo, record){
											if (record.get(combo.valueField)) {
												var rectype = record.get(combo.valueField);
												if ( rectype == 1) // Новое
												{
													current_window.dateReviewRange.setValue(null);
												}
												else
												{
													current_window.dateReviewRange.focus(true, 250);
												}
											}
										}
									}
								}]
							}]
						}]
					}]
				}),
				new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', handler: function() { this.openTreatmentEditWindow('add'); }.createDelegate(this), disabled: current_window.curArm.inlist(['spec_mz']) && !isUserGroup('TreatmentSpecialist') },
						{ name: 'action_edit', handler: function() { this.openTreatmentEditWindow('edit'); }.createDelegate(this), disabled: current_window.curArm.inlist(['spec_mz']) && !isUserGroup('TreatmentSpecialist') },
						{ name: 'action_view', handler: function() { this.openTreatmentEditWindow('view'); }.createDelegate(this) },
						{ name: 'action_delete', disabled: current_window.curArm.inlist(['spec_mz'])},
						{ name: 'action_refresh'},
                        { name: 'action_print',
                            menuConfig: {
                                printObjectReg: { text: lang['pechat_obrascheniya'], handler: function() { this.print(); }.createDelegate(this) }
                            }
                        }
					],
					autoExpandColumn: 'autoexpand_dir',
					autoExpandMin: 200,
					autoLoadData: false,
					//clearSelectionsOnTab: true,
					id: 'ETSF_TreatmentGrid',
					object: 'Treatment',
					editformclassname: 'swTreatmentEditWindow',
					name: 'TreatmentGrid',
					dataUrl: '/?c=Treatment&m=getTreatmentSearchList',
					focusOn: {
						name: 'TreatmentGrid',
						type: 'grid'
					},
					focusPrev: {
						name: 'TreatmentType',
						type: 'field'
					},
					pageSize: 100,
					paging: true,
					region: 'center',
					root: 'data',
					stringfields: [
						//{ name: 'Server_id', type: 'int', hidden: true },
						{ name: 'Treatment_id', type: 'int', hidden: true, key: true },
						{ name: 'PMUser', type: 'string', header: langs('Создатель'), width: 150 },
						{ name: 'Treatment_Reg', type: 'string', header: langs('Номер регистрации'), width: 120 },
						{ name: 'Treatment_DateReg', type: 'date', format: 'd.m.Y', header: langs('Дата регистрации'), width: 100 },
						{ name: 'TreatmentType', type: 'string', header: langs('Тип обращения'), width: 150 },
						{ name: 'TreatmentSenderType', type: 'string', header: langs('Тип инициатора обращения'), width: 150 },
						{ name: 'Treatment_SenderDetails', type: 'string', header: langs('Инициатор'), id: 'autoexpand_dir' },
						{ name: 'TreatmentRecipientType', type: 'string', header: langs('Адресат обращения'), width: 150 },
						{ name: 'Treatment_DateReview', type: 'date', format: 'd.m.Y', header: langs('Дата рассмотрения'), width: 100 },
						{ name: 'TreatmentReview', type: 'string', header: langs('Статус обращения'), width: 150 },/**/
						{ name: 'TreatmentReview_id', type: 'int', hidden: 'true'}
					],
					toolbar: true ,
					processLoad: false,
					lastLoadGridDate: null,
                                        auto_refresh: null,
                                        html: '<audio id="swTreatmentSearchWindowNotification"><source src="/audio/web/WavLibraryNet_Sound5825.mp3" type="audio/mpeg"></audio>',
                                        onLoadData: function() {
						Ext.getCmp('ETSF_TreatmentGrid').processLoad = false;
                                                
                                                // #157110 Звуковое оповещение пользователя о событии в системе
                                                if (getRegionNick() == 'ufa')
                                                {
                                                    var form = current_window;
                                                    var grid = this.getGrid();
                                                    grid.getStore().each(function(rec)
                                                    {
                                                        if (form.NotificationFor.includes(rec.get('TreatmentReview_id')))
                                                        {
                                                            var Treatment_id = rec.get('Treatment_id');
                                                            if (Treatment_id && !form.TreatmentList.includes(Treatment_id))
                                                            {
                                                                form.playNotification();
                                                                form.TreatmentList.push(Treatment_id);
                                                            }
                                                        }
                                                    });
                                                    grid.lastLoadGridDate = new Date();
                                                    if(grid.auto_refresh)
                                                    {
                                                        clearInterval(grid.auto_refresh);
                                                    }
                                                    grid.auto_refresh = setInterval(
                                                        function()
                                                        {
                                                            var cur_date = new Date();
                                                            // если прошло более 2 минут с момента последнего обновления
                                                            if(grid.lastLoadGridDate.getTime() < (cur_date.getTime()-120))
                                                            {
                                                                form.doSearch();
                                                            }
                                                        }.createDelegate(grid),
                                                        120000
                                                    );
                                                }
					},
					onRowSelect: function(sm,index,record)
					{
						if(record.get('TreatmentReview_id')==2){
							this.getAction('action_changeStatus').disable();
						}else {
							this.getAction('action_changeStatus').enable();
						}
					},
					totalProperty: 'totalCount' // //
				})
			]
		});
		sw.Promed.swTreatmentSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [
		{
			fn: function(inp, e) {
				Ext.getCmp('EvnTreatmentSearchWindow').openTreatmentEditWindow('add');
			},
			key: [
				Ext.EventObject.INSERT
			],
			stopEvent: true
		}, {
			alt: true,
			fn: function(inp, e) {
				Ext.getCmp('EvnTreatmentSearchWindow').doSearch();
			},
			key: [
				Ext.EventObject.ENTER,
				Ext.EventObject.G
			],
			stopEvent: true
		}, {
			fn: function(inp, e) {
				Ext.getCmp('EvnTreatmentSearchWindow').doSearch();
			},
			key: [
				Ext.EventObject.ENTER
			],
			stopEvent: true
		}
	],
	show: function() {
		var current_window = this,
			form = current_window.findById('EvnTreatmentSearchForm'),
			TreatmentRecipientType = current_window.findById('ETSF_TreatmentRecipientType_id'),
			TreatmentRecipientTypeValue = '',
			LpuRid = TreatmentRecipientType = current_window.findById('ETSF_Lpu_rid'),
			gridPanel = this.findById('ETSF_TreatmentGrid');

		sw.Promed.swTreatmentSearchWindow.superclass.show.apply(this, arguments);
		this.action = 'edit';
		if(arguments[0] && arguments[0].action)
			this.action = arguments[0].action;

		
		if(this.action == 'view')
		{
			Ext.getCmp('ETSF_TreatmentGrid').setActionDisabled('action_add', true);
			Ext.getCmp('ETSF_TreatmentGrid').setActionDisabled('action_edit', true);
			Ext.getCmp('ETSF_TreatmentGrid').setActionDisabled('action_delete', true);
		}
		else
		{
			Ext.getCmp('ETSF_TreatmentGrid').setActionDisabled('action_add', false);
			Ext.getCmp('ETSF_TreatmentGrid').setActionDisabled('action_edit', false);
			Ext.getCmp('ETSF_TreatmentGrid').setActionDisabled('action_delete', false);
		}

		//активность полей

		//если форма вызвана из АРМа оператора call-центра, из АРМа специалиста минздрава
		//или пользователь принадлежит группе «Администратор ЦОД
		// то поле доступно для редактирования
		if( current_window.curArm.inlist(['callcenter', 'spec_mz']) || isUserGroup(['superadmin']))
		{
			TreatmentRecipientType.enable();
		}
		else{
			TreatmentRecipientType.disable();
			TreatmentRecipientTypeValue = 1;
		}

		this.center();
		this.loadCombo('ETSF_TreatmentType_id', '');
		this.loadCombo('ETSF_TreatmentMultiplicity_id', '');
		this.loadCombo('ETSF_TreatmentReview_id', '');
		this.loadCombo('ETSF_TreatmentSenderType_id', '');
		this.loadCombo('ETSF_TreatmentUrgency_id', '');
		this.setCurrentDate();
		/*Ext.getCmp('ETSF_pmUser_id').getStore().load({
			callback: function() {
				Ext.getCmp('ETSF_pmUser_id').focus(true, 250);
			},
			params: {
				Lpu_id: UserLpu
			}
		});*/
		form.getForm().reset();

		//Если форма вызвана из АРМа Администратора МО, из АРМа статистика, из АРМа регистратора поликлиники,
		// то поле недоступно для редактирования
		if( current_window.curArm.inlist(['lpuadmin', 'mstat', 'regpol'])){
			LpuRid.setValue(getGlobalOptions().lpu[0]);
			LpuRid.disable();
		}else{
			LpuRid.enable();
		}

		gridPanel.getGrid().getStore().removeAll();

		gridPanel.addActions({
			name: 'action_changeStatus',
			text: 'Изменить статус ',
			iconCls: 'edit16',
			disabled: true,
			handler: function () {
				current_window.changeTreatmentStatus();
			}
		}, 3);

		this.dateRegRange.focus(true, 250);
	},
	enableField: function(id) {
		Ext.getCmp(id).enable();
		Ext.getCmp(id).setVisible(true);
		Ext.getCmp(id).allowBlank = false;
	},
	disableField: function(id, visible) {
		Ext.getCmp(id).disable();
		Ext.getCmp(id).setVisible(visible);
		Ext.getCmp(id).setValue('');
		Ext.getCmp(id).allowBlank = true;
	},
	setCurrentDate: function() {
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					this.Date = response_obj.begDate;
					this.findById('EvnTreatmentSearchForm').findById('ETSF_Treatment_setDateReg').setValue(response_obj.begDate);
					this.dateRegRange.setMaxValue( response_obj.begDate );
					this.dateReviewRange.setMaxValue( response_obj.begDate );
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	loadCombo: function(id_combo, value) {
		var combo = this.findById('EvnTreatmentSearchForm').findById(id_combo);
		combo.getStore().baseParams.Object = combo.comboSubject;
		switch ( combo.comboSubject ) {
			case 'TreatmentType':
				combo.getStore().baseParams.TreatmentType_id = value;
				combo.getStore().baseParams.TreatmentType_Name = '';
				break;
			case 'TreatmentMultiplicity':
				combo.getStore().baseParams.TreatmentMultiplicity_id = value;
				combo.getStore().baseParams.TreatmentMultiplicity_Name = '';
				break;
			case 'TreatmentReview':
				combo.getStore().baseParams.TreatmentReview_id = value;
				combo.getStore().baseParams.TreatmentReview_Name = '';
				break;
			case 'TreatmentSenderType':
				combo.getStore().baseParams.TreatmentSenderType_id = value;
				combo.getStore().baseParams.TreatmentSenderType_Name = '';
				break;
			case 'TreatmentSubjectType':
				combo.getStore().baseParams.TreatmentSubjectType_id = value;
				combo.getStore().baseParams.TreatmentSubjectType_Name = '';
				break;
			case 'TreatmentUrgency':
				combo.getStore().baseParams.TreatmentUrgency_id = value;
				combo.getStore().baseParams.TreatmentUrgency_Name = '';
				break;
		}
		combo.getStore().load({
			callback: function() {
				combo.setValue(value);
			}
		});
	},
	changeTreatmentStatus: function(){
		var win = this,
			grid = this.findById('ETSF_TreatmentGrid').getGrid(),
			rec = grid.getSelectionModel().getSelected();

		if ( getWnd('swTreatmentFeedbackWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно ответа на обращение уже открыто'));
			return;
		}

		if(rec){
			getWnd('swTreatmentFeedbackWindow').show({
				Treatment_id: rec.get('Treatment_id'),
				TreatmentReview_id: rec.get('TreatmentReview_id'),
				onsave: function(action){
					if(action == 'feedback') sw.swMsg.alert(langs('Сообщение'), langs('Ответ на обращение сохранен'));
					else sw.swMsg.alert(langs('Сообщение'), langs('Статус обращения сохранен'));
					getWnd('swTreatmentFeedbackWindow').hide();
					win.loadGridWithFilter(false);
				}
			});
		}
	},
	doReset: function(reset_form_flag) {
		var form = this.findById('EvnTreatmentSearchForm').getForm();
		if ( reset_form_flag == true ) {
			form.reset();
		}
		form.findField('Treatment_SenderDetails').enable();
		if (this.curArm.inlist(['lpuadmin', 'mstat', 'regpol'])) {
			this.findById('ETSF_Lpu_rid').setValue(getGlobalOptions().lpu[0]);
		}
		var grid = this.findById('ETSF_TreatmentGrid').getGrid();
		grid.getStore().removeAll();
	},
	doSearch: function() {
		var current_window = this;
		var form = current_window.findById('EvnTreatmentSearchForm').getForm();
		if ( !form.isValid() )
		{
			sw.swMsg.alert(lang['poisk_obrascheniy'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}
		var grid = current_window.findById('ETSF_TreatmentGrid').getGrid();
		grid.getStore().removeAll();
		current_window.loadGridWithFilter(false);
	},
	openTreatmentEditWindow: function(action) {
		if ( !action || !action.inlist(['add','edit','view']) ) {
			return false;
		}

		if ( getWnd('swTreatmentEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_obrascheniya_uje_otkryito']);
			return false;
		}

		var base_form = this.findById('EvnTreatmentSearchForm').getForm();
		var grid = this.findById('ETSF_TreatmentGrid').getGrid();
		var params = new Object();
		params.action = action;
		//params.callback = Ext.emptyFn;
		params.callback = function(data) {
			if ( !data || !data.TEW_Data )
			{
				return false;
			}
			var record = grid.getStore().getById(data.TEW_Data.Treatment_id);
			if ( !record )
			{
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Treatment_id') ) {
					grid.getStore().removeAll();
				}/* */
				grid.getStore().loadData({ 'data': [ data.TEW_Data ]}, true);
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}
			else
			{
				var treatment_fields = new Array();
				grid.getStore().fields.eachKey(function(key, item) {
					treatment_fields.push(key);
				});
				for ( i = 0; i < treatment_fields.length; i++ ) {
					record.set(treatment_fields[i], data.TEW_Data[treatment_fields[i]]);
				}
				record.commit(); 
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(grid.getStore().indexOf(record));
					grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
				}
			}
		}.createDelegate(this);
		if ( action == 'add' ) {
			params.Treatment_setDateReg = base_form.findField('ETSF_Treatment_setDateReg').getValue();
			params.TreatmentType_id = 0;
			getWnd('swTreatmentEditWindow').show(params);
		}
		else {
			if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Treatment_id') ) {
				return false;
			}
			var record = grid.getSelectionModel().getSelected();
			//params.TreatmentForm = record.data;
			params.Treatment_setDateReg = record.get('Treatment_DateReg');
			params.Treatment_id = record.get('Treatment_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
			};
			getWnd('swTreatmentEditWindow').show(params);
		}
	},
	loadGridWithFilter: function(clear) {
		var grid = Ext.getCmp('ETSF_TreatmentGrid');
		if(grid.processLoad)
			return false;
		grid.processLoad = true;
		if (clear) {
			grid.removeAll();
			grid.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					pmUser: 0,
					Treatment_Reg: '',
					Treatment_DateReg_Start: '',
					Treatment_DateReg_End: '',
					TreatmentUrgency_id: 0,
					TreatmentMultiplicity_id: 0,
					TreatmentSenderType_id: 0,
					Treatment_SenderDetails: '',
					TreatmentMethodDispatch_id: 0,
					TreatmentType_id: 0,
					TreatmentCat_id: 0,
					TreatmentRecipientType_id: 0,
					Lpu_rid: 0,
					TreatmentReview_id: 0,
					Treatment_DateReview_Start: '',
					Treatment_DateReview_End: ''
				}
			});
		} else {
			var pmUser = this.findById('ETSF_pmUser_id').getValue() || 0;
			var Treatment_Reg = this.findById('ETSF_Treatment_Reg').getValue() || '';
			var Treatment_DateReg_Start = Ext.util.Format.date(this.dateRegRange.getValue1(), 'd.m.Y') || '';
			var Treatment_DateReg_End = Ext.util.Format.date(this.dateRegRange.getValue2(), 'd.m.Y') || '';
			var Treatment_DateReview_Start = Ext.util.Format.date(this.dateReviewRange.getValue1(), 'd.m.Y') || '';
			var Treatment_DateReview_End = Ext.util.Format.date(this.dateReviewRange.getValue2(), 'd.m.Y') || '';
			var TreatmentUrgency_id = this.findById('ETSF_TreatmentUrgency_id').getValue() || 0;
			var TreatmentMultiplicity_id = this.findById('ETSF_TreatmentMultiplicity_id').getValue() || 0;
			var TreatmentSenderType_id = this.findById('ETSF_TreatmentSenderType_id').getValue() || 0;
			var Person_id = this.findById('ETSF_Person_id').getValue() || '';
			var Treatment_SenderDetails = this.findById('ETSF_Treatment_SenderDetails').getValue() || '';
			var TreatmentMethodDispatch_id = this.findById('ETSF_TreatmentMethodDispatch_id').getValue() || 0;
			var TreatmentType_id = this.findById('ETSF_TreatmentType_id').getValue() || 0;
			var TreatmentCat_id = this.findById('ETSF_TreatmentCat_id').getValue() || 0;
			var TreatmentRecipientType_id = this.findById('ETSF_TreatmentRecipientType_id').getValue() || 0;
			var Lpu_rid = this.findById('ETSF_Lpu_rid').getValue() || 0;
			var TreatmentReview_id = this.findById('ETSF_TreatmentReview_id').getValue() || 0;
			grid.removeAll();
			grid.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					pmUser: pmUser,
					Treatment_Reg: Treatment_Reg,
					Treatment_DateReg_Start: Treatment_DateReg_Start,
					Treatment_DateReg_End: Treatment_DateReg_End,
					TreatmentUrgency_id: TreatmentUrgency_id,
					TreatmentMultiplicity_id: TreatmentMultiplicity_id,
					TreatmentSenderType_id: TreatmentSenderType_id,
					Person_id: Person_id,
					Treatment_SenderDetails: Treatment_SenderDetails,
					TreatmentMethodDispatch_id: TreatmentMethodDispatch_id,
					TreatmentType_id: TreatmentType_id,
					TreatmentCat_id: TreatmentCat_id,
					TreatmentRecipientType_id: TreatmentRecipientType_id,
					Lpu_rid: Lpu_rid,
					TreatmentReview_id: TreatmentReview_id,
					Treatment_DateReview_Start: Treatment_DateReview_Start,
					Treatment_DateReview_End: Treatment_DateReview_End
				}
			});
		}
	},
	print: function() {
		if ( !this.findById('ETSF_TreatmentGrid').getGrid().getSelectionModel().getSelected().get('Treatment_id') )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('ETSF_TreatmentGrid').getGrid().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['ne_vyibrano_obraschenie'],
				title: lang['registratsiya_obrascheniy_pechat']
			});
			return false;
		}
		var Treatment_id = this.findById('ETSF_TreatmentGrid').getGrid().getSelectionModel().getSelected().get('Treatment_id');
		var query_string = '/?c=Treatment&m=printTreatment&Treatment_id=' + Treatment_id;
		window.open(query_string, '_blank');
	}
});