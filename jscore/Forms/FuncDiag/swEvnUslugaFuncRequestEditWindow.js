/**
* swEvnUslugaFuncRequestEditWindow - окно редактирования выполнения услуги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      апрель.2012
* @comment      Префикс для id компонентов EUFREF (EvnUslugaFuncRequestEditForm)
*
*/
/*NO PARSE JSON*/

sw.Promed.swEvnUslugaFuncRequestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnUslugaFuncRequestEditWindow',
	objectSrc: '/jscore/Forms/FuncDiag/swEvnUslugaFuncRequestEditWindow.js',
	action: 'edit',
	autoScroll: true,
	autoHeight: false,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	id: 'EvnUslugaFuncRequestEditWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaFuncRequestEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: true,
	height: 550,
	width: 700,
	minHeight: 550,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function(options) {
		// options @Object
		// options.copyMode @String Режим создания копии выполняемой параклинической услуги

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		
		var silent = (options && options.silent) ? true : null;

		this.formStatus = 'save';
		var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm(),
			templ_panel = Ext.getCmp('EUFREF_TemplPanel'),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});

			if ( !base_form.isValid() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						//log(base_form);
						//log(this.findById('EvnUslugaFuncRequestEditForm'));
						//log(this.findById('EvnUslugaFuncRequestEditForm').getFirstInvalidEl());
						this.findById('EvnUslugaFuncRequestEditForm').getFirstInvalidEl().focus(true);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			var evn_usluga_set_time = base_form.findField('EvnUslugaPar_setTime').getValue();

			base_form.findField('MedPersonal_uid').setValue(null);
			var record = base_form.findField('MedStaffFact_id').getStore().getById(base_form.findField('MedStaffFact_id').getValue());
			if ( record ) {
				base_form.findField('MedPersonal_uid').setValue(record.get('MedPersonal_id'));
			}

			var med_personal_uid = base_form.findField('MedPersonal_uid').getValue();
			var med_personal_sid = base_form.findField('MedPersonal_sid').getValue();
			var med_staff_fact_uid = base_form.findField('MedStaffFact_id').getValue();
			//var med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();
			
			var params = new Object();
			if ( base_form.findField('Org_uid').disabled ) {
				params.Org_uid = base_form.findField('Org_uid').getValue();
			}

			//params.MedStaffFact_id = base_form.findField('MedPersonal_uid').getFieldValue('MedStaffFact_id');
			params.MedStaffFact_id = med_staff_fact_uid;
			params.MedPersonal_uid = med_personal_uid;
			params.MedPersonal_sid = med_personal_sid;
			params.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
			params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			//params.AnamnezData = Ext.util.JSON.encode(templ_panel.getSavingData());
			//params.XmlTemplate_id = templ_panel.getXmlTemplate_id();
			
			loadMask.show();

			base_form.submit({
				failure: function(result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();

					if (action.result) {
						if (action.result.Error_Msg && action.result.Error_Code == 301) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function() {
									this.formStatus = 'edit';
									this.EvnDirectionPanel.expand();
									base_form.findField('EvnUslugaPar_UslugaNum').focus(true);
								}.createDelegate(this),
								icon: Ext.Msg.WARNING,
								msg: action.result.Error_Msg,
								title: ERR_INVFIELDS_TIT
							});
						} else if (action.result.Error_Msg) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				}.createDelegate(this),
				params: params,
				success: function(result_form, action) {
					loadMask.hide();
					if ( action.result && action.result.EvnUslugaPar_id > 0 ) {
						this.FileUploadPanel.listParams = {Evn_id: action.result.EvnUslugaPar_id};
						this.FileUploadPanel.saveChanges();
						params.EvnUslugaPar_id = action.result.EvnUslugaPar_id;
						this.EvnXmlPanel.onEvnSave();
						this.formStatus = 'edit';
						if (!silent) {
							this.hide();
							this.callback(params);
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
					}
				}.createDelegate(this)
			});
	},

	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.findById('EvnUslugaFuncRequestEditForm').getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var Region_Nick = getRegionNick();

		base_form.items.each(function(field){
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;

			var UslugaComplex_AttributeList_str = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_AttributeList');
			var UslugaComplex_AttributeList = [];
			if (!Ext.isEmpty(UslugaComplex_AttributeList_str)) {
				UslugaComplex_AttributeList = UslugaComplex_AttributeList_str.split(',');
			}

			switch(field.getName()) {
				case 'EvnUslugaPar_UslugaNum':
					visible = false;
					if (Region_Nick == 'penza') {
						var hasKtOrMrt = UslugaComplex_AttributeList.some(function(attr) {
							return attr.inlist(['kt','mrt','NumberPortal']);
						});
						visible = hasKtOrMrt;
						allowBlank = !hasKtOrMrt;
					}
					if (visible === false && win.formLoaded) {
						value = null;
					}
					if (value != field.getValue()) {
						field.setValue(value);
						field.fireEvent('change', field, value);
					}
					if (allowBlank !== null) {
						field.setAllowBlank(allowBlank);
					}
					if (visible !== null) {
						field.setContainerVisible(visible);
					}
					if (enable !== null) {
						field.setDisabled(!enable || action == 'view');
					}
					if (typeof filter == 'function' && field.store) {
						field.lastQuery = '';
						if (typeof field.setBaseFilter == 'function') {
							field.setBaseFilter(filter);
						} else {
							field.store.filterBy(filter);
						}
					}
					break;
				case 'EUP_MedicalCareFormType_id':
					var hasKtOrMrt = false;
					if(Region_Nick == 'penza') {
						hasKtOrMrt = UslugaComplex_AttributeList.some(function(attr) {
							return attr.inlist(['kt', 'mrt']);
						});
					}
					field.setContainerVisible(hasKtOrMrt);
					if (!base_form.findField('EvnUslugaPar_UslugaNum').getValue() && hasKtOrMrt) {
						field.setValue(3);
						field.setRawValue('Плановая');
					}
					break;
			}
		});
	},

	initComponent: function() {
		var cur_wnd = this;
		
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			win: this,
			width: 1000,
			buttonAlign: 'left',
			buttonLeftMargin: 100,
			labelWidth: 150,
			folder: 'evnmedia/',
			style: 'background: transparent',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});
		
		this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
            autoHeight: true,
            border: true,
            collapsible: true,
			loadMask: {},
            id: 'EUFREF_TemplPanel',
            layout: 'form',
            title: lang['protokol_funktsionalnoy_diagnostiki'],
            ownerWin: this,
            options: {
                XmlType_id: sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID, // только протоколы услуг
                EvnClass_id: 47 // документы и шаблоны только категории параклинические услуги
            },
			signEnabled: true,
            onAfterLoadData: function(panel){
                var bf = this.findById('EvnUslugaFuncRequestEditForm').getForm();
                bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
                panel.expand();
                this.syncSize();
                this.doLayout();
            }.createDelegate(this),
            onAfterClearViewForm: function(panel){
                var bf = this.findById('EvnUslugaFuncRequestEditForm').getForm();
                bf.findField('XmlTemplate_id').setValue(null);
            }.createDelegate(this),
            // определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
            onBeforeCreate: function (panel, method, params) {
                if (!panel || !method || typeof panel[method] != 'function') {
                    return false;
                }
                var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();
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
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: 2538,
				text: BTN_FRMSAVE
			}, /*{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'signature16',
				tabIndex: 17,
				text: BTN_FRMSIGN
			},*/ {
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
						this.findById('EvnUslugaFuncRequestEditForm').getForm().findField('EvnUslugaPar_setDate').focus(true);
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				tabIndex: 2539,
				text: BTN_FRMCANCEL
			}],
			layout: 'form',
			items: [
				new sw.Promed.PersonInformationPanelShortWithDirection({
					id: 'EUFREF_PersonInformationFrame',
					showHistoryLink: false
				}),
				new Ext.form.FormPanel({
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnUslugaFuncRequestEditForm',
					labelAlign: 'right',
					labelWidth: 130,
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: Ext.emptyFn
					}, [
						{name: 'accessType'},
						{name: 'EvnDirection_id'},
						{name: 'EvnRequest_id'},
						{name: 'EvnDirection_Num'},
						{name: 'EvnDirection_setDate'},
						{name: 'UslugaComplex_id'}, // комплексная услуга
						{name: 'EvnUslugaPar_NumUsluga'},
						{name: 'EvnUslugaPar_UslugaNum'},
						{name: 'EUP_MedicalCareFormType_id'},
						{name: 'StudyResult_id'},
						{name: 'XmlTemplate_id'},
						{name: 'EvnUslugaPar_id'},
						{name: 'TimetablePar_id'},
						{name: 'MedProductCard_id'},
						{name: 'EvnUslugaPar_setDate'},
						{name: 'EvnUslugaPar_setTime'},
						{name: 'LpuSection_did'},
						{name: 'LpuSection_uid'},												
						{name: 'MedPersonal_uid'},
						{name: 'MedStaffFact_id'},
						{name: 'MedPersonal_sid'},
						{name: 'EvnUslugaPar_Comment'},
						{name: 'EvnUslugaPar_IsPaid'},
						{name: 'EvnUslugaPar_IndexRep'},
						{name: 'EvnUslugaPar_IndexRepInReg'},
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
						name: 'EvnRequest_id',
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
					}, {
						name: 'EvnUslugaPar_Regime',
						value: 2,
						xtype: 'hidden'
					}, {
						name:'EvnUslugaPar_IsPaid',
						xtype:'hidden'
					}, {
						name:'EvnUslugaPar_IndexRep',
						xtype:'hidden'
					}, {
						name:'EvnUslugaPar_IndexRepInReg',
						xtype:'hidden'
					},
					new sw.Promed.Panel({
						autoHeight: true,
						bodyStyle: 'padding-top: 0.5em;',
						border: true,
						collapsible: true,
						id: 'EUFREF_EvnDirectionPanel',
						layout: 'form',
						style: 'margin-bottom: 0.5em;',
						items: [{
							allowBlank: true,
							value: null,
							fieldLabel: lang['kompleksnaya_usluga'],
							name: 'UslugaComplex_id',
							to: 'EvnUslugaPar',
							listWidth: 600,
							tabIndex: 2531,
							width: 500,
							listeners: {
								'change': function(combo, newValue, oldValue) {
									_this.refreshFieldsVisibility(['EvnUslugaPar_UslugaNum']);
									_this.refreshFieldsVisibility(['EUP_MedicalCareFormType_id']);
								}.createDelegate(this)
							},
							xtype: 'swuslugacomplexnewcombo'
						}, {
							allowBlank: true,
							editable: true,
							codeField: 'AccountingData_InventNumber',
							displayField: 'MedProductClass_Name',
							fieldLabel: lang['meditsinskoe_izdelie'],
							hiddenName: 'MedProductCard_id',
							store: new Ext.data.Store({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'MedProductCard_id'
								}, [
									{ name: 'MedProductCard_id', mapping: 'MedProductCard_id', type: 'int' },
									{ name: 'LpuSection_id', mapping: 'LpuSection_id', type: 'int' },
									{ name: 'Resource_id', mapping: 'Resource_id', type: 'int' },
									{ name: 'AccountingData_InventNumber', mapping: 'AccountingData_InventNumber', type: 'string' },
									{ name: 'MedProductClass_Name', mapping: 'MedProductClass_Name', type: 'string' },
									{ name: 'MedProductClass_Model', mapping: 'MedProductClass_Model', type: 'string' }
								]),
								url: '/?c=LpuPassport&m=loadMedProductCard'
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<table style="border: 0;"><td style="width: 70px"><font color="red">{AccountingData_InventNumber}</font></td><td><h3>{MedProductClass_Name}</h3>{MedProductClass_Model}</td></tr></table>',
								'</div></tpl>'
							),
							triggerAction: 'all',
							valueField: 'MedProductCard_id',
							lastQuery: '',
							width: 500,
							listWidth: 600,
							xtype: 'swbaselocalcombo'
						}, {
							fieldLabel: 'Повторная подача',
							listeners: {
								'check': function(checkbox, value) {
									if ( getRegionNick() != 'perm' ) {
										return false;
									}

									var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();

									var
										EvnUslugaPar_IndexRep = parseInt(base_form.findField('EvnUslugaPar_IndexRep').getValue()),
										EvnUslugaPar_IndexRepInReg = parseInt(base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()),
										EvnUslugaPar_IsPaid = parseInt(base_form.findField('EvnUslugaPar_IsPaid').getValue());

									var diff = EvnUslugaPar_IndexRepInReg - EvnUslugaPar_IndexRep;

									if ( EvnUslugaPar_IsPaid != 2 || EvnUslugaPar_IndexRepInReg == 0 ) {
										return false;
									}

									if ( value == true ) {
										if ( diff == 1 || diff == 2 ) {
											EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep + 2;
										}
										else if ( diff == 3 ) {
											EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep + 4;
										}
									}
									else if ( value == false ) {
										if ( diff <= 0 ) {
											EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep - 2;
										}
									}

									base_form.findField('EvnUslugaPar_IndexRep').setValue(EvnUslugaPar_IndexRep);

								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EUPAREF + 57,
							name: 'EvnUslugaPar_RepFlag',
							xtype: 'checkbox'
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
									tabIndex: 2532,
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
										var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();
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
									tabIndex: 2533,
									validateOnBlur: false,
									width: 60,
									xtype: 'swtimefield'
								}]
							}]
						}, {
							allowDicimals: false,
							allowNegative: false,
							xtype: 'numberfield',
							name: 'EvnUslugaPar_UslugaNum',
							fieldLabel: '№ услуги из журнала выполненных услуг',
							width: 100
						}, {
							allowBlank: true,
							editable: true,
							hiddenName: 'EUP_MedicalCareFormType_id',
							width: 100,
							listWidth: 100,
							xtype: 'swmedicalcareformtypecombo'
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
								var base_form = this.findById('EvnUslugaFuncRequestEditForm').getForm();
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
													params:{Lpu_id: org_data.Lpu_id, mode: 'combo'}
											});

											var med_stafffact_combo = base_form.findField('MedStaffFact_id');
											med_stafffact_combo.clearValue();
											med_stafffact_combo.getStore().load({
													params:{Lpu_id: org_data.Lpu_id, isDoctor: 1, mode: 'combo'}
											});

											var med_personal_combo2 = base_form.findField('MedPersonal_sid');
											med_personal_combo2.clearValue();
											med_personal_combo2.getStore().load({
													params:{Lpu_id: org_data.Lpu_id, isMidMedPersonal: 1, mode: 'combo'}
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
							tabIndex: 2534,
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
							id: 'EUFREF_LpuSectionCombo',
							lastQuery: '',
							tabIndex: 2535,
							width: 500,
							xtype: 'swlpusectionglobalcombo', 
							linkedElements: [
								//'EUFREF_MedStaffFactCombo',
								'EUFREF_MedPersonalCombo',					
								'EUFREF_MedStaffFactCombo2'								
							]
						}, 
						{
							//allowBlank: false,
							hiddenName: 'MedStaffFact_id',
							id: 'EUFREF_MedPersonalCombo',
							lastQuery: '',
							listWidth: 750,
							parentElementId: 'EUFREF_LpuSectionCombo',
							tabIndex: 2536,
							width: 500,
							xtype: 'swmedstafffactglobalcombo',
							listeners: {
								'change': function(field, nV, oV) {

									var base_form = cur_wnd.findById('EvnUslugaFuncRequestEditForm').getForm();
									var n = field.getStore().findBy(function(rec) { return rec.get('MedStaffFact_id') == field.getValue(); });
									var MedPersonal_uid = base_form.findField('MedPersonal_uid');
									MedPersonal_uid.setValue('');
									var rec = field.getStore().getAt(n);
									if (!rec) {
										return false;
									}
									var MedPersonal_id = field.getStore().getAt(n).get('MedPersonal_id');
									if (MedPersonal_uid) {
										MedPersonal_uid.setValue(MedPersonal_id);
									}
								}

							}
						},
						{
							name: 'MedPersonal_uid',
							value: 0,
							xtype: 'hidden'
						},
//						{
//							allowBlank: false,
//							fieldLabel: 'Врач',
//							hiddenName: 'MedPersonal_uid',
//							id: 'EUFREF_MedStaffFactCombo',
//							lastQuery: '',
//							listWidth: 750,
//							parentElementId: 'EUFREF_LpuSectionCombo',
//							tabIndex: 11,
//							width: 500,
//							valueField: 'MedPersonal_id',
//							xtype: 'swmedstafffactglobalcombo'
//						}, 
						{
							fieldLabel: lang['sredniy_med_personal'],
							hiddenName: 'MedPersonal_sid',
							id: 'EUFREF_MedStaffFactCombo2',
							lastQuery: '',
							listWidth: 750,
							parentElementId: 'EUFREF_LpuSectionCombo',
							tabIndex: 2537,
							width: 500,
							valueField: 'MedPersonal_id',
							xtype: 'swmedstafffactglobalcombo'
						}, {
							fieldLabel: 'Количество оказанных услуг',
							xtype: 'numberfield',
							//allowBlank: false,
							allowDecimals: false,
							allowNegative: false,
							autoCreate: {tag: "input", size: 10, maxLength: 2, autocomplete: "off"},
							name: 'EvnUslugaPar_NumUsluga',
							minValue: 1,
							tabIndex: 2537
						}, {
							fieldLabel: 'Результат',
							xtype: 'swcommonsprcombo',
							//allowBlank: false,
							hiddenName: 'StudyResult_id',
							comboSubject: 'StudyResult',
							tabIndex: 2537,
							width: 500
						}, {
							fieldLabel: lang['kommentariy'],
							xtype: 'textarea',
							name: 'EvnUslugaPar_Comment',
							width: 500
						}]
					})]
				}),
				this.EvnXmlPanel
				,{
					title: lang['faylyi'],
					id: 'EUFREF_FileTab',
					border: false,
					collapsible: true,
					autoHeight: true,
					items: [this.FileUploadPanel]
				}
			]
		});
		sw.Promed.swEvnUslugaFuncRequestEditWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function() {
		sw.Promed.swEvnUslugaFuncRequestEditWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();
		
		if ( !arguments[0] )
		{
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		this.formStatus = 'edit'; // или 'save'
		this.show_complete = false;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onSaveUsluga = arguments[0].onSaveUsluga || Ext.emptyFn;
		this.onSaveProtocol = arguments[0].onSaveProtocol || Ext.emptyFn;
		this.addProtocolAfterSaveUsluga = arguments[0].addProtocolAfterSaveUsluga || false;
		this.editProtocolAfterSaveUsluga = arguments[0].editProtocolAfterSaveUsluga || false;
		// определяем параметры, влияющие на внешний вид.
		this.ARMType = arguments[0].ARMType || '';
		this.face = ( arguments[0].face ) ? arguments[0].face : '';
		this.is_UslugaComplex = false; // обычная или комплексная услуга. Можно определить только после загрузки формы
		this.is_doctorpar = false; // Врач параклиники или др. пользователь
		this.is_operator = false; // Оператор или работающий врач (есть список мест работы)
		// параметры, влияющие на свободный выбор врача и отделения, а также внешний вид
		this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id || null;
		this.UserLpuSection_id = arguments[0].UserLpuSection_id || null;
		this.LpuSection_did = arguments[0].LpuSection_did || null;

		this.Lpu_id = arguments[0].Lpu_id || getGlobalOptions().lpu_id;
		this.LpuSection_id = arguments[0].LpuSection_id || null;
		this.MedPersonal_id = arguments[0].MedPersonal_id || null;
		this.Resource_id = arguments[0].Resource_id || null;
		if (sw.Promed.MedStaffFactByUser.last) {
			this.Lpu_id = sw.Promed.MedStaffFactByUser.last.Lpu_id || this.Lpu_id;
			this.LpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id || this.LpuSection_id;
			this.MedPersonal_id = sw.Promed.MedStaffFactByUser.last.MedPersonal_id || this.MedPersonal_id;
		}
				
		this.UserMedStaffFacts = null;
		this.UserLpuSections = null;
		
		if ( !arguments[0].EvnUslugaPar_id && this.action != 'add')
		{
			sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuet_identifikator_paraklinicheskoy_uslugi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		/*if ( this.ARMType == 'par' && !(this.UserMedStaffFact_id > 0 && this.UserLpuSection_id > 0) )
		{
			sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuyut_parametryi_polzovatelya_arma_parakliniki'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}*/
		if ( this.ARMType == 'par' && (this.UserMedStaffFact_id > 0 && this.UserLpuSection_id > 0) )
		{
			this.is_doctorpar = true;
		}
		
		if ( this.is_doctorpar && this.action == 'add')
		{
			// добавление обычной паракл.услуги врачом из АРМа парки
			this.is_UslugaComplex = false;
		}

		// если в настройках есть medstafffact, то имеем список мест работы
		if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 )
		{
			this.UserMedStaffFacts = Ext.globalOptions.globals['medstafffact'];
		}
		// если в настройках есть lpusection, то имеем список мест работы
		if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 )
		{
			this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
		}

		this.is_operator = (!this.UserMedStaffFacts || !this.UserLpuSections);
		
		var form_panel = this.findById('EvnUslugaFuncRequestEditForm');
		var base_form = form_panel.getForm();
		
		var usluga_complex_combo = base_form.findField('UslugaComplex_id');	
		var usluga_setdate = base_form.findField('EvnUslugaPar_setDate');
		var lpu_section_combo = base_form.findField('LpuSection_uid');
		var med_personal_combo = base_form.findField('MedStaffFact_id');
		var med_stafffact_combo = base_form.findField('MedStaffFact_id');
		var sid_med_personal_combo = base_form.findField('MedPersonal_sid');
		var org_combo = base_form.findField('Org_uid');
		//var templ_panel = this.findById('EUFREF_TemplPanel');

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		base_form.reset();
        this.EvnXmlPanel.doReset();
        this.EvnXmlPanel.collapse();
        this.EvnXmlPanel.LpuSectionField = lpu_section_combo;
        this.EvnXmlPanel.MedStaffFactField = med_personal_combo;
		base_form.setValues(arguments[0]);

		this.refreshFieldsVisibility();

		base_form.clearInvalid();
		this.findById('EUFREF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				clearDateAfterPersonDeath('personpanelid', 'EUFREF_PersonInformationFrame', usluga_setdate);
			}
		});

		base_form.findField('EvnUslugaPar_RepFlag').hideContainer();

		var evn_usluga_par_id = arguments[0].EvnUslugaPar_id;
		
		//загружаем файлы
		this.FileUploadPanel.reset();
		this.FileUploadPanel.listParams = {
			Evn_id: evn_usluga_par_id
		};	
		this.FileUploadPanel.loadData({
			Evn_id: evn_usluga_par_id
		});
		/*
		templ_panel.getToolbarItem('btnTemplatePrint').setVisible(true);
		templ_panel.Evn_id = evn_usluga_par_id;
		templ_panel.loadTemplate({
			Evn_id: evn_usluga_par_id,
			onNotFound: function(){
			}
		});
        */
		var parentObj = this;
		base_form.load({
			failure: function() {
				loadMask.hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
			}.createDelegate(this),
			params: {
				'EvnUslugaPar_id': evn_usluga_par_id
			},
			success: function() {
				var modeAdd = false;
				if (Ext.isEmpty(base_form.findField('EvnUslugaPar_setDate').getValue())) {
					modeAdd = true;
				}
				base_form.findField('MedProductCard_id').getStore().load({
					params: {Lpu_id: parentObj.Lpu_id, MedService_id: parentObj.MedService_id},
					callback: function(records, options, success) {
						if (!Ext.isEmpty(base_form.findField('MedProductCard_id').getValue())) {
							base_form.findField('MedProductCard_id').setValue(base_form.findField('MedProductCard_id').getValue());
						} else if (modeAdd && !Ext.isEmpty(parentObj.Resource_id)) {
							// проставить по умолчанию изделие с ресурса
							var index = base_form.findField('MedProductCard_id').getStore().findBy( function(rec) {
								if ( rec.get('Resource_id') == parentObj.Resource_id ) {
									return true;
								}
							});
							if (index > -1) {
								mpc_id = base_form.findField('MedProductCard_id').getStore().getAt(index).get('MedProductCard_id');
								base_form.findField('MedProductCard_id').setValue(mpc_id);
							}
						}
					}
				});
				
				var lpu_section_uid = lpu_section_combo.getValue();
				var med_personal_uid = med_personal_combo.getValue();
				var med_personal_sid = sid_med_personal_combo.getValue();
				var med_stafffact_id = med_stafffact_combo.getValue();
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

				var MedPersonal_id = usluga_setdate.getValue()=='' ? this.MedPersonal_id : null; // Если услуга уже выполнена - врача не подставляем

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
						this.fireEvent('change', this, this.getValue());
					}.createDelegate(usluga_complex_combo)
				});
				usluga_complex_combo.disable();
				
				org_combo.getStore().load({
					callback: function(records, options, success) {
						if ( success ) {
							if (org_combo.getStore().getCount()>0) {
								org_combo.setValue(org_combo.getStore().getAt(0).get('Org_id')); 
								lpu_uid = org_combo.getStore().getAt(0).get('Lpu_id');
							}
							// Врачи
//							med_personal_combo.getStore().load({
//								params: {Lpu_id: lpu_uid, isDoctor: 1},
//								callback: function(records, options, success) {
//									// ср.медперсонал
//                                    sid_med_personal_combo.getStore().load({
//										params: {Lpu_id: lpu_uid, isMidMedPersonal: 1},
//										callback: function(records, options, success) {
//											// отделения
//											lpu_section_combo.getStore().load({
//												params:{Lpu_id: lpu_uid},
//												callback: function(records, options, success) {
//													// и теперь расставляем значения
//													lpu_section_combo.setValue(lpu_section_uid);
//													lpu_section_combo.fireEvent('change', lpu_section_combo, lpu_section_uid);
//													if (!med_personal_uid || (med_personal_uid && med_personal_uid == '')) {
//														var ix = med_personal_combo.getStore().findBy(function(r) {
//															if(r.get('MedPersonal_id') == MedPersonal_id)
//															{	
//																med_personal_combo.setValue(r.get('MedPersonal_id'));
//															}
//														}.createDelegate(this));
//													} else {
//														med_personal_combo.setValue(med_personal_uid);
//													}
//        											base_form.findField('MedPersonal_sid').setValue(med_personal_sid);
//												}
//											});	
//										}
//									});
//								}
//							});
							med_stafffact_combo.getStore().load({
								params: {Lpu_id: lpu_uid, isDoctor: 1, mode: 'combo'},
								callback: function(records, options, success) {
									// ср.медперсонал
                                    sid_med_personal_combo.getStore().load({
										params: {Lpu_id: lpu_uid, isMidMedPersonal: 1, mode: 'combo'},
										callback: function(records, options, success) {
											// отделения
											lpu_section_combo.getStore().load({
												params:{Lpu_id: lpu_uid, mode: 'combo'},
												callback: function(records, options, success) {
													// и теперь расставляем значения
													lpu_section_combo.setValue(lpu_section_uid);
													lpu_section_combo.fireEvent('change', lpu_section_combo, lpu_section_uid);
													if (!med_personal_uid || (med_personal_uid && med_personal_uid == '')) {
														var ix = med_stafffact_combo.getStore().findBy(function(r) {
															if(r.get('MedPersonal_id') == MedPersonal_id)
															{	
																med_personal_combo.setValue(r.get('MedPersonal_id'));
																med_stafffact_combo.setValue(r.get('MedStaffFact_id'));
															}
														}.createDelegate(this));
														if (!ix) {
															sid_med_personal_combo.getStore().findBy(function(r) {
																if(r.get('MedPersonal_id') == MedPersonal_id) {	
																	sid_med_personal_combo.setValue(r.get('MedPersonal_id'));
																}
															}.createDelegate(this));														
														}
													} else {
														med_personal_combo.setValue(med_personal_uid);
														med_stafffact_combo.setValue(med_stafffact_id);
													}
        											base_form.findField('MedPersonal_sid').setValue(med_personal_sid);
													med_stafffact_combo.fireEvent('change',med_stafffact_combo,med_stafffact_combo.getValue(),0);
//													parentObj.findById('EUFREF_DicomObj').expand();
												}
											});	
										}
									});
								}
							})
						}
					},
					params: params
				});

				if ( getRegionNick() == 'perm' && base_form.findField('EvnUslugaPar_IsPaid').getValue() == 2) {
					base_form.findField('EvnUslugaPar_RepFlag').showContainer();

					var indexRep = Ext.isEmpty(base_form.findField('EvnUslugaPar_IndexRep').getValue()) ?
						0 : base_form.findField('EvnUslugaPar_IndexRep').getValue(),
						indexRepInReg = Ext.isEmpty(base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()) ?
							0 : base_form.findField('EvnUslugaPar_IndexRepInReg').getValue();

					if (indexRep >= indexRepInReg) {
						base_form.findField('EvnUslugaPar_RepFlag').setValue(true);
					}
					else {
						base_form.findField('EvnUslugaPar_RepFlag').setValue(false);
					}
				}

				base_form.findField('EUP_MedicalCareFormType_id').getStore().load();

				loadMask.hide();
				this.refreshFieldsVisibility();
                this.EvnXmlPanel.setReadOnly('view' == base_form.findField('accessType').getValue());
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
	},
	title: lang['rezultat_vyipolneniya_uslugi']
});
