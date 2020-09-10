/**
* swEvnUslugaFuncRequestPatientHistoryWindow - окно редактирования выполнения услуги с встроенным DICOM просмотровщиком.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      сентябрь.2013
*
*/
/*NO PARSE JSON*/

sw.Promed.swEvnUslugaFuncRequestPatientHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnUslugaFuncRequestPatientHistoryWindow',
	objectSrc: '/jscore/Forms/FuncDiag/swEvnUslugaFuncRequestPatientHistoryWindow.js',
	action: 'edit',
	autoScroll: true,
	autoHeight: false,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	id: 'EvnUslugaFuncRequestPatientHistoryWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaFuncRequestPatientHistoryWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.PatientResearchHistoryPanel.removeAll({clearAll:true, addEmptyRecord:false});
			win.AssociatedResearches.removeAll({clearAll:true, addEmptyRecord:false});
			win.onHide();
			this.ResearchRegion.collapse();
		}
	},
	maximizable: true,
//	height: 550,
	width: '100%',
//	minHeight: 550,
	minWidth: '100%',
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	masksInitiated: false,

	initMasks: function() {
		if (!this.masksInitiated) {
			maskCfg = {
				msgCls:'hiddenMessageForLoadMask'
			};
//			this.PatientResearchHistoryPanel.loadMask  = new Ext.LoadMask(Ext.get(this.PatientResearchHistoryPanel.id),maskCfg);
			this.AssociatedResearches.loadMask  = new Ext.LoadMask(Ext.get(this.AssociatedResearches.id),maskCfg);
			this.EvnXmlPanel.loadMask  = new Ext.LoadMask(Ext.get(this.EvnXmlPanel.id),maskCfg);
		}
	},
	
	
	loadResearchByUid: function(data) {
		
		if (!data['study_uid']) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuet_identifikator_issledovaniya'], Ext.emptyFn );
			return false;
		}
		if (!data['LpuEquipmentPacs_id']) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuet_identifikator_ustroystva_pacs'], Ext.emptyFn );
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=Dicom&m=getStudyView',
			params:{
				'study_uid':data['study_uid'],
				'LpuEquipmentPacs_id':data['LpuEquipmentPacs_id']
			},
			success: function(response, opts) {
				var resp = JSON.parse(response.responseText);
				this.DicomViewerPanel.getEl().update(resp.html);
				this.DicomViewerPanel.doLayout();
				loadMask.hide();
				this.ResearchRegion.expand();
			}.createDelegate(this),
			failure: function(response, opts) {
				loadMask.hide();
			}.createDelegate(this)
		});
		
		this.DicomViewerPanel.getEl().up('div').addClass('EvnUslugaparFunctRequest_position');
	},
	
	calculateAssociatedResearchesPanelHeight: function(store) {
		var rowCount = store.getCount(),
			minRowCount = 2,
			maxRowCount = 4,
			setRowCount = (rowCount<minRowCount)?minRowCount:((rowCount>maxRowCount)?maxRowCount:rowCount)
		
		this.AssociatedResearches.setHeight(50+setRowCount*21);
		this.AssociatedResearches.getGrid().setHeight(50+setRowCount*21);
	},
	
	calculatePatientResearchHistoryPanelHeight: function(store) {
		var rowCount = store.getCount(),
			minRowCount = 2,
			maxRowCount = 6,
			setRowCount = (rowCount<minRowCount)?minRowCount:((rowCount>maxRowCount)?maxRowCount:rowCount)
		
		this.PatientResearchHistoryPanel.setHeight(50+setRowCount*21);
		this.PatientResearchHistoryPanel.getGrid().setHeight(50+setRowCount*21);
	},
	
	initComponent: function() {
		var cur_wnd = this;
		
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			id: this.id+'_FileUploadPanel',
			win: this,
			buttonAlign: 'left',
			maxHeight: 150,
			buttonLeftMargin: 100,
			labelWidth: 100,
			commentTextfieldWidth: 250,
			folder: 'evnmedia/',
			style: 'background: transparent',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});
				
		// Дикомовские запросики
		
		this.PatientResearchHistoryPanel = new sw.Promed.ViewFrame({
			id: this.id + '_PatientResearchHistoryPanel',
			focusOnFirstLoad:false,
			toolbar: true,
			autoExpandColumn: 'autoexpand',
			useEmptyRecord: false,
			autoWidth: true,
			autoLoadData: false,
			paging: false,
			border: false,
			stripeRows: true,
			height: 91,
			stringfields: [
				{ name: 'EvnUslugaPar_id',  hidden: true, key: true, hideable: false },
				{ header: lang['vremya_napravleniya'], name: 'EvnDirection_setDT', width: 200, hideable: false },
				{ header: lang['naimenovanie_uslugi'], name: 'UslugaComplex_Name', hideable: false,width: 300 },
				{ name: 'EvnUslugaParAssociatedResearches_id', hidden: true, hideable: false },
			],
			dataUrl: '/?c=EvnFuncRequest&m=getEvnFuncRequestWithAssociatedResearches',
			totalProperty: 'totalCount',
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_refresh', text: lang['obnovit'], handler: this.searchUslugaPar.createDelegate(this) },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_print', hidden: true, disabled: true }
			]
		});
		
		//this.PatientResearchHistoryPanel.getGrid().getStore().on('update',this.calculatePatientResearchHistoryPanelHeight.createDelegate(this));
		//this.PatientResearchHistoryPanel.getGrid().getStore().on('add',this.calculatePatientResearchHistoryPanelHeight.createDelegate(this));
		//this.PatientResearchHistoryPanel.getGrid().getStore().on('remove',this.calculatePatientResearchHistoryPanelHeight.createDelegate(this));
		this.PatientResearchHistoryPanel.getGrid().getStore().on('load',this.calculatePatientResearchHistoryPanelHeight.createDelegate(this));
		
		this.PatientResearchHistoryPanel.getGrid().on('rowdblclick',function(grid,rowIndex,evt){
			var params = {};
			params.EvnUslugaPar_id = grid.getStore().getAt(rowIndex).get('EvnUslugaPar_id');
			
			this.AssociatedResearches.removeAll({clearAll:true, addEmptyRecord:false});
			this.AssociatedResearches.loadData({globalFilters: params,callback: function(r,opts,success){				
					if ((r.length == 1)&&(typeof r[0]['json'] != undefined)&&(typeof r[0]['json']['Error_Msg'] != undefined)&&( r[0]['json']['Error_Msg']!=null )) {
						sw.swMsg.alert(lang['oshibka'], r[0]['json']['Error_Msg']);
					}
			}.createDelegate(this)});
			
			this.FileUploadPanel.reset();
			this.FileUploadPanel.listParams = {
				Evn_id: params.EvnUslugaPar_id
			};	
			var fileUploadCallback = function() {
				this.FileUploadPanel.disable();
			}.createDelegate(this);
			
			this.FileUploadPanel.loadData({
				Evn_id: params.EvnUslugaPar_id,
				callback:fileUploadCallback
			});
			var form_panel = this.findById('EvnUslugaFuncRequestEditFormHistoryWindow');
			var base_form = form_panel.getForm();
			
			var usluga_complex_combo = base_form.findField('UslugaComplex_id');
			var usluga_setdate = base_form.findField('EvnUslugaPar_setDate');
			var lpu_section_combo = base_form.findField('LpuSection_uid');
			var med_personal_combo = base_form.findField('MedPersonal_uid');
			var sid_med_personal_combo = base_form.findField('MedPersonal_sid');
			var org_combo = base_form.findField('Org_uid');

			var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
			loadMask.show();
			base_form.reset();
			this.EvnXmlPanel.doReset();
			this.EvnXmlPanel.collapse();
			this.EvnXmlPanel.LpuSectionField = lpu_section_combo;
			this.EvnXmlPanel.MedStaffFactField = med_personal_combo;


			base_form.setValues(arguments[0]);
			base_form.clearInvalid();
			
			base_form.load({
				failure: function() {
					loadMask.hide();
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
				}.createDelegate(this),
				params: {
					'EvnUslugaPar_id': params.EvnUslugaPar_id
				},
				success: function() {

					var lpu_section_uid = lpu_section_combo.getValue();
					var med_personal_uid = med_personal_combo.getValue();
					var med_personal_sid = sid_med_personal_combo.getValue();
					var org_uid = org_combo.getValue();
					var lpu_uid = null;

					if (!lpu_section_uid || (lpu_section_uid && lpu_section_uid == '')) {
						lpu_section_uid = this.LpuSection_id;
					}

					var params = {OrgType: 'lpu'};

					if (!org_uid || (org_uid && org_uid == '')) {
						params.Lpu_oid = this.Lpu_id;
					} else {
						params.Org_id = org_uid;
					}

					if (usluga_setdate.getValue()=='') {
						setCurrentDateTime({
							callback: function() {
								usluga_setdate.fireEvent('change', usluga_setdate, usluga_setdate.getValue());
							},
							dateField: usluga_setdate,
							loadMask: false,
							setDate: true,
							setDateMaxValue: true,
							setDateMinValue: false,
							setTime: true,
							timeField: base_form.findField('EvnUslugaPar_setTime'),
							windowId: this.id
						});
					}

					usluga_complex_combo.getStore().removeAll();
					usluga_complex_combo.getStore().load({
						params: {UslugaComplex_id: usluga_complex_combo.getValue()},
						callback: function() {
							this.setValue(this.getValue());
						}.createDelegate(usluga_complex_combo)
					});
					usluga_complex_combo.disable();

					var MedPersonal_id  = this.MedPersonal_id;
					org_combo.getStore().load({
						callback: function(records, options, success) {
							if ( success ) {
								if (org_combo.getStore().getCount()>0) {
									org_combo.setValue(org_combo.getStore().getAt(0).get('Org_id')); 
									lpu_uid = org_combo.getStore().getAt(0).get('Lpu_id');
								}
								// Врачи
								med_personal_combo.getStore().load({
									params: {Lpu_id: lpu_uid},
									callback: function(records, options, success) {
										// ср.медперсонал
										sid_med_personal_combo.getStore().load({
											params: {Lpu_id: lpu_uid},
											callback: function(records, options, success) {
												// отделения
												lpu_section_combo.getStore().load({
													params:{Lpu_id: lpu_uid},
													callback: function(records, options, success) {
														// и теперь расставляем значения
														lpu_section_combo.setValue(lpu_section_uid);
														lpu_section_combo.fireEvent('change', lpu_section_combo, lpu_section_uid);
														if (!med_personal_uid || (med_personal_uid && med_personal_uid == '')) {
															var ix = med_personal_combo.getStore().findBy(function(r) {
																if(r.get('MedPersonal_id') == MedPersonal_id)
																{	
																	med_personal_combo.setValue(r.get('MedPersonal_id'));
																}
															}.createDelegate(this));
														} else {
															med_personal_combo.setValue(med_personal_uid);
														}
														base_form.findField('MedPersonal_sid').setValue(med_personal_sid);
	//													parentObj.findById('EUFRPH_DicomObj').expand();
													}
												});	
											}
										});
									}
								});
							}
						},
						params: params
					});

					loadMask.hide();
					this.EvnXmlPanel.setReadOnly(true);
					this.EvnXmlPanel.setBaseParams({
						userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
						UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue(),
						Evn_id: base_form.findField('EvnUslugaPar_id').getValue()
					});
					this.EvnXmlPanel.doLoadData();
					this.syncSize();
					this.doLayout();
				}.createDelegate(this),
				url: '/?c=EvnFuncRequest&m=loadEvnUslugaEditForm'
			});
			
		
		
		}.createDelegate(this));
		
			
		
		this.AssociatedResearches = new sw.Promed.ViewFrame({
			title: lang['prikreplennyie_izobrajeniya'],
			id: this.id + '_AssociatedResearchesGrid',
			focusOnFirstLoad:false,
			autoExpandColumn: 'autoexpand',
			useEmptyRecord: false,
			height:91,
			toolbar: false,
			autoLoadData: false,
			paging: false,
			border: false,
			stripeRows: true,
			dataUrl: '/?c=Dicom&m=getAssociatedResearches',
			cls: 'additionalGridRowHoverClass',
			stringfields: [
				{ header: 'Study UID', key: true, name: 'study_uid',  hidden: true, hideable: false },
				{ header: lang['data'], width: 100, name: 'study_date', hideable: false},
				{ header: lang['vremya'], width: 100,  name: 'study_time', hideable: false},
				{ header: lang['imya_patsienta'], name: 'patient_name', width: 300, hideable: false },
				{ header: '', name: 'link_to_oviyam', hideable: false, width:120},
				{ name: 'LpuEquipmentPacs_id', hidden: true, hideable: false }
			],
			totalProperty: 'totalCount'
		});
		
		this.AssociatedResearches.ViewGridStore.on('update',this.calculateAssociatedResearchesPanelHeight.createDelegate(this));
		this.AssociatedResearches.ViewGridStore.on('add',this.calculateAssociatedResearchesPanelHeight.createDelegate(this));
		this.AssociatedResearches.ViewGridStore.on('remove',this.calculateAssociatedResearchesPanelHeight.createDelegate(this));
		this.AssociatedResearches.ViewGridStore.on('load',this.calculateAssociatedResearchesPanelHeight.createDelegate(this));
		
		this.AssociatedResearches.getGrid().on('rowdblclick',function(grid,rowIndex,evt){
	
			this.loadResearchByUid({
				'study_uid': grid.getStore().getAt(rowIndex).get('study_uid'),
				'LpuEquipmentPacs_id': grid.getStore().getAt(rowIndex).get('LpuEquipmentPacs_id')
			});
		}.createDelegate(this));
		
		this.DicomViewerPanel = new Ext.Panel({
			id: 'EUFRPH_DicomViewerPanel'
		});
		
		
        this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
            autoHeight: true,
            border: true,
            collapsible: true,
			loadMask: {},
            id: 'EUFRPH_TemplPanel',
            layout: 'form',
            title: lang['protokol_funktsionalnoy_diagnostiki'],
            ownerWin: this,
            options: {
                XmlType_id: sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID, // только протоколы услуг
                EvnClass_id: 47 // документы и шаблоны только категории параклинические услуги
            },
            onAfterLoadData: function(panel){
                var bf = this.findById('EvnUslugaFuncRequestEditFormHistoryWindow').getForm();
                bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
                panel.expand();
                this.syncSize();
                this.doLayout();
            }.createDelegate(this),
            onAfterClearViewForm: function(panel){
                var bf = this.findById('EvnUslugaFuncRequestEditFormHistoryWindow').getForm();
                bf.findField('XmlTemplate_id').setValue(null);
            }.createDelegate(this),
            // определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
            onBeforeCreate: function (panel, method, params) {
                if (!panel || !method || typeof panel[method] != 'function') {
                    return false;
                }
                var base_form = this.findById('EvnUslugaFuncRequestEditFormHistoryWindow').getForm();
                var evn_id_field = base_form.findField('EvnUslugaPar_id');
                var evn_id = evn_id_field.getValue();
                if (evn_id && evn_id > 0) {
                    // услуга была создана ранее
                    // все базовые параметры уже должно быть установлены
                    panel[method](params);
                } else {
                    this.doSave({
                        openChildWindow: function() {
                            panel.setBaseParams({
                                userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
                                UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
                                Server_id: base_form.findField('Server_id').getValue(),
                                Evn_id: evn_id_field.getValue()
                            });
                            panel[method](params);
                        }.createDelegate(this)
                    });
                }
                return true;
            }.createDelegate(this)
        });
		
		this.EvnDirectionPanel = new sw.Promed.Panel({
			autoHeight: true,
			bodyStyle: 'padding-top: 0.5em;',
			border: true,
			title: lang['osnovnyie_dannyie'],
			collapsed: false,
			collapsible: true,
			// hidden: true,
			id: 'EUFRPH_EvnDirectionPanel',
			layout: 'form',
			items: [{
				allowBlank: true,
				value: null,
				fieldLabel: lang['kompleksnaya_usluga'],
				name: 'UslugaComplex_id',
				listWidth: 600,
				tabIndex: 12,
				width: 500,
				xtype: 'swuslugacomplexnewcombo'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: lang['data_issledovaniya'],
						format: 'd.m.Y',
						name: 'EvnUslugaPar_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: 8,
						width: 100,
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: lang['vremya'],
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnUslugaPar_setTime',
						onTriggerClick: function() {
							var base_form = this.findById('EvnUslugaFuncRequestEditFormHistoryWindow').getForm();
							var time_field = base_form.findField('EvnUslugaPar_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								dateField: base_form.findField('EvnUslugaPar_setDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: this.id
							});
						}.createDelegate(this),
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: 9,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				displayField: 'Org_Name',
				editable: false,
				enableKeyEvents: true,
				fieldLabel: lang['organizatsiya'],
				hiddenName: 'Org_uid',
				listeners: {
					'keydown': function( inp, e ) {
						if ( inp.disabled ) {
							return;
						}

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

							return true;
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

							return true;
						}
					}
				},
				mode: 'local',
				onTrigger1Click: function() {
					var base_form = this.findById('EvnUslugaFuncRequestEditFormHistoryWindow').getForm();
					var combo = base_form.findField('Org_uid');

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
									Lpu_id: org_data.Lpu_id,
									Org_Name: org_data.Org_Name
								}]);
								combo.setValue(org_data.Org_id);
								getWnd('swOrgSearchWindow').hide();
								combo.collapse();

								var lpu_section_combo = base_form.findField('LpuSection_uid');
								lpu_section_combo.clearValue();
								lpu_section_combo.getStore().load({
										params:{Lpu_id: org_data.Lpu_id}
								});
								var med_personal_combo = base_form.findField('MedPersonal_uid');
								med_personal_combo.clearValue();
								med_personal_combo.getStore().load({
										params:{Lpu_id: org_data.Lpu_id}
								});								
								var med_personal_combo2 = base_form.findField('MedPersonal_sid');
								med_personal_combo2.clearValue();
								med_personal_combo2.getStore().load({
										params:{Lpu_id: org_data.Lpu_id}
								});								
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
				tabIndex: 4,
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
				allowBlank: false,
				hiddenName: 'LpuSection_uid',
				id: 'EUFRPH_LpuSectionCombo',
				lastQuery: '',
				tabIndex: 10,
				width: 500,
				xtype: 'swlpusectionglobalcombo', 
				linkedElements: [
					'EUFRPH_MedStaffFactCombo',
					'EUFRPH_MedStaffFactCombo2'								
				]
			}, {
				allowBlank: false,
				fieldLabel: lang['vrach'],
				hiddenName: 'MedPersonal_uid',
				id: 'EUFRPH_MedStaffFactCombo',
				lastQuery: '',
				listWidth: 750,
				parentElementId: 'EUFRPH_LpuSectionCombo',
				tabIndex: 11,
				width: 500,
				valueField: 'MedPersonal_id',
				xtype: 'swmedstafffactglobalcombo'
			}, {
				fieldLabel: lang['sredniy_med_personal'],
				hiddenName: 'MedPersonal_sid',
				id: 'EUFRPH_MedStaffFactCombo2',
				lastQuery: '',
				listWidth: 750,
				parentElementId: 'EUFRPH_LpuSectionCombo',
				tabIndex: 12,
				width: 500,
				valueField: 'MedPersonal_id',
				xtype: 'swmedstafffactglobalcombo'
			}, {
				fieldLabel: lang['kommentariy'],
				xtype: 'textarea',
				name: 'EvnLabRequest_Comment',
				width: 500
			}]
		})
		
		
		this.ExtendedPersonInformationPanelShort = new sw.Promed.PersonInformationPanelShort({
			id: 'EUFRPH_PersonInformationFrame'
		});
		
		this.FilePanel = new Ext.Panel({
			title: lang['faylyi'],
			id: 'EUFRPH_FileTab',
			border: false,
			collapsible: true,
			autoHeight: true,
			items: [this.FileUploadPanel],
			listeners: {
				'expand':function(panel){
					//Приходится делать такую ерунду, чтобы cодержимое адекватно перерисовывалось
					//console.log(panel);
					//this.FileUploadPanel.setWidth(adjWidth);
					this.FileUploadPanel.doLayout();

				}.createDelegate(this)
			}
		});
		
		
		this.ResearchRegion = new Ext.form.FormPanel({
				id:'EUFRPH_ResearchRegion',
				region:'east',
				title: lang['issledovanie'],
				collapsible: true,
				collapsed: true,
				split:true,
				width: '50%',
				heigth: '100%',
				margins:'0 5 0 0',
				items:
				[
					this.DicomViewerPanel
				]
			});
		this.ResearchRegion.on('beforeexpand',function(pan,anim) {
			this.ResearchRegion.getForm().getEl().setHeight(this.getEl().getHeight());
		}.createDelegate(this));
		
		
        Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: 17,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[2].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnUslugaFuncRequestEditFormHistoryWindow').getForm().findField('PrehospDirect_id').focus(true);
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				tabIndex: 18,
				text: BTN_FRMCANCEL
			}],
			layout: 'border',
			autoScroll: true,
			items:[
			{
					listeners: {
						'resize':function(panel, adjWidth, adjHeight, rawWidth, rawHeight){
							//Приходится делать такую ерунду, чтобы cодержимое адекватно перерисовывалось
							this.PatientResearchHistoryPanel.getGrid().setWidth(adjWidth);
							this.PatientResearchHistoryPanel.getGrid().doLayout();
							this.AssociatedResearches.getGrid().setWidth(adjWidth);
							this.AssociatedResearches.getGrid().doLayout();
							this.FileUploadPanel.setWidth(adjWidth);
							this.FileUploadPanel.doLayout();
							
						}.createDelegate(this)
					},
					region: 'center',
					layout: 'form',
					border: false,
					autoScroll: true,
					items:[
						this.ExtendedPersonInformationPanelShort,
						new Ext.form.FormPanel({
							bodyBorder: false,
							border: false,
							frame: false,
							id: 'EvnUslugaFuncRequestEditFormHistoryWindow',
							labelAlign: 'right',
							labelWidth: 130,
							layout: 'form',
							reader: new Ext.data.JsonReader({
								success: Ext.emptyFn
							}, [
								{name: 'accessType'},
								{name: 'EvnDirection_id'},
								{name: 'EvnDirection_Num'},
								{name: 'EvnDirection_setDate'},
								{name: 'UslugaComplex_id'}, // комплексная услуга 
								{name: 'XmlTemplate_id'},
								{name: 'EvnUslugaPar_id'},
								{name: 'TimetablePar_id'},
								{name: 'EvnUslugaPar_setDate'},
								{name: 'EvnUslugaPar_setTime'},
								{name: 'LpuSection_did'},
								{name: 'LpuSection_uid'},
								{name: 'MedPersonal_uid'},
								{name: 'MedPersonal_sid'},
								{name: 'Lpu_id'},
								{name: 'Org_uid'},
								{name: 'PayType_id'},
								{name: 'Person_id'},
								{name: 'PersonEvn_id'},
								{name: 'PrehospDirect_id'},
								{name: 'Server_id'},
								{name: 'Usluga_id'}
							]),
							region: 'center',
							url: '/?c=EvnFuncRequest&m=saveEvnUslugaEditForm',
							items: [{
								name: 'accessType',
								value: '',
								xtype: 'hidden'
							}, {
								name: 'EvnUslugaPar_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'XmlTemplate_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'EvnUslugaPar_isCito',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'TimetablePar_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'EvnDirection_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'Lpu_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'PayType_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'Person_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'PersonEvn_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'Server_id',
								value: 0,
								xtype: 'hidden'
							},
							this.EvnDirectionPanel
							]
						}),
						{
							title:			lang['istoriya_issledovaniy'],
							id:				'EUFRPH_DicomObj',
							border:			false,
	//						collapsed :		true,
							collapsible:	false,
							autoHeight:		true,
							items: [ this.PatientResearchHistoryPanel ]
						},
						this.AssociatedResearches,
						this.EvnXmlPanel, 
						this.FilePanel
					]
				},
					this.ResearchRegion
				]
//			}]
		});
		sw.Promed.swEvnUslugaFuncRequestPatientHistoryWindow.superclass.initComponent.apply(this, arguments);
	},
	searchUslugaPar: function() {
		var params = {};
		params.Person_id = this.Person_id;
		this.PatientResearchHistoryPanel.removeAll({clearAll:true, addEmptyRecord:false});
		this.PatientResearchHistoryPanel.loadData({globalFilters: params,callback: function(r,opts,success){				
				if ((r.length == 1)&&(typeof r[0]['json'] != undefined)&&(typeof r[0]['json']['Error_Msg'] != undefined)&&( r[0]['json']['Error_Msg']!=null )) {
					sw.swMsg.alert(lang['oshibka'], r[0]['json']['Error_Msg']);
				}
		}});
	},
	show: function() {
		sw.Promed.swEvnUslugaFuncRequestPatientHistoryWindow.superclass.show.apply(this, arguments);
//base_form.findField('accessType').getValue()
		this.restore();
		this.center();
		this.maximize();
		if ( !arguments[0] )
		{
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		this.Lpu_id = arguments[0].Lpu_id || null;
		this.LpuSection_id = arguments[0].LpuSection_id || null;
		this.MedPersonal_id = arguments[0].MedPersonal_id || null;
		if (sw.Promed.MedStaffFactByUser.last) {
			this.Lpu_id = sw.Promed.MedStaffFactByUser.last.Lpu_id || this.Lpu_id;
			this.LpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id || this.LpuSection_id;
			this.MedPersonal_id = sw.Promed.MedStaffFactByUser.last.MedPersonal_id || this.MedPersonal_id;
		}
				
		this.EvnDirectionPanel.collapse();
		this.ExtendedPersonInformationPanelShort.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : '')
		});

		var evn_usluga_par_id = arguments[0].EvnUslugaPar_id;
		
		//загружаем файлы
		this.FileUploadPanel.reset();
		
		var params = {};
		params.EvnUslugaPar_id = evn_usluga_par_id;
		this.AssociatedResearches.removeAll({clearAll:true, addEmptyRecord:false});
		  
	   
		this.initMasks();

		this.Person_id = arguments[0].Person_id;
		
		this.FileUploadPanel.disable();
		this.EvnXmlPanel.setReadOnly(true);
		this.EvnXmlPanel.doReset();
		this.EvnXmlPanel.collapse();
		
		this.EvnDirectionPanel.items.each(function(el,idx,n){
			el.disable();
		});
		
		this.searchUslugaPar();
		
	},
	title: lang['istoriya_issledovaniy']
});
