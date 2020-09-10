/**
* swEvnUslugaProcRequestEditWindow - окно редактирования выполнения услуги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      апрель.2012 / copypasted 15.01.2013
* @comment      Префикс для id компонентов EUFREF (EvnUslugaProcRequestEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnUslugaProcRequestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnUslugaProcRequestEditWindow',
	objectSrc: '/jscore/Forms/FuncDiag/swEvnUslugaProcRequestEditWindow.js',
	action: 'edit',
	autoScroll: true,
	autoHeight: false,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	id: 'EvnUslugaProcRequestEditWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaProcRequestEditWindow');

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

		var base_form = this.findById('EvnUslugaProcRequestEditForm').getForm(),
		loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});

			if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					//log(base_form);
					//log(this.findById('EvnUslugaProcRequestEditForm'));
					//log(this.findById('EvnUslugaProcRequestEditForm').getFirstInvalidEl());
					this.findById('EvnUslugaProcRequestEditForm').getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var
			evn_usluga_set_time = base_form.findField('EvnUslugaPar_setTime').getValue(),
			index,
			med_staff_fact_uid = base_form.findField('MedStaffFact_uid').getValue(),
			med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();

		index = base_form.findField('MedStaffFact_uid').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == med_staff_fact_uid);
		});
		if ( index >= 0 ) {
			base_form.findField('MedPersonal_uid').setValue(base_form.findField('MedStaffFact_uid').getStore().getAt(index).get('MedPersonal_id'));
		}
		
		index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
			return (rec.get('MedStaffFact_id') == med_staff_fact_sid);
		});
		if ( index >= 0 ) {
			base_form.findField('MedPersonal_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedPersonal_id'));
		}
		
		var params = new Object();

		params.AnamnezData = null;
		params.XmlTemplate_id = null;
		params.EvnUslugaPar_Regime = 2;

		if ( base_form.findField('Org_uid').disabled ) {
			params.Org_uid = base_form.findField('Org_uid').getValue();
		}

		if ( base_form.findField('LpuSection_uid').disabled ) {
			params.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
		}

		if ( base_form.findField('UslugaComplex_id').disabled ) {
			params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		}

		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
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
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: 17,
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
						this.findById('EvnUslugaProcRequestEditForm').getForm().findField('PrehospDirect_id').focus(true);
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				tabIndex: 18,
				text: BTN_FRMCANCEL
			}],
			layout: 'form',
			items: [
				new sw.Promed.PersonInformationPanelShort({
					id: 'EUFREF_PersonInformationFrame'
				}),
				new Ext.form.FormPanel({
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnUslugaProcRequestEditForm',
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
						name: 'MedPersonal_uid',
						value: 0,
						xtype: 'hidden'
					}, {
						name: 'MedPersonal_sid',
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
							listWidth: 600,
							tabIndex: 12,
							width: 500,
							xtype: 'swuslugacomplexpidcombo'
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
										var base_form = this.findById('EvnUslugaProcRequestEditForm').getForm();
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
								var base_form = this.findById('EvnUslugaProcRequestEditForm').getForm();
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
											var med_personal_combo = base_form.findField('MedStaffFact_uid');
											med_personal_combo.clearValue();
											med_personal_combo.getStore().load({
													params:{Lpu_id: org_data.Lpu_id}
											});								
											var med_personal_combo2 = base_form.findField('MedStaffFact_sid');
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
							id: 'EUFREF_LpuSectionCombo',
							lastQuery: '',
							tabIndex: 10,
							width: 500,
							xtype: 'swlpusectionglobalcombo', 
							linkedElements: [
								'EUFREF_MedStaffFactCombo'
							]
						}, {
							allowBlank: false,
							fieldLabel: lang['vrach'],
							hiddenName: 'MedStaffFact_uid',
							id: 'EUFREF_MedStaffFactCombo',
							lastQuery: '',
							listWidth: 750,
							parentElementId: 'EUFREF_LpuSectionCombo',
							tabIndex: 11,
							width: 500,
							valueField: 'MedStaffFact_id',
							xtype: 'swmedstafffactglobalcombo'
						}, {
							fieldLabel: lang['sredniy_med_personal'],
							hiddenName: 'MedStaffFact_sid',
							id: 'EUFREF_MedStaffFactCombo2',
							lastQuery: '',
							listWidth: 750,
							tabIndex: 12,
							width: 500,
							valueField: 'MedStaffFact_id',
							xtype: 'swmedstafffactglobalcombo'
						}, {
							fieldLabel: lang['kommentariy'],
							xtype: 'textarea',
							name: 'EvnLabRequest_Comment',
							width: 500
						}]
					})]
				}), {
					title: lang['faylyi'],
					id: 'EUFREF_FileTab',
					border: false,
					collapsible: true,
					autoHeight: true,
					items: [this.FileUploadPanel]
				}
			]
		});
		sw.Promed.swEvnUslugaProcRequestEditWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function() {
		sw.Promed.swEvnUslugaProcRequestEditWindow.superclass.show.apply(this, arguments);

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

		this.Lpu_id = arguments[0].Lpu_id || null;
		this.LpuSection_id = arguments[0].LpuSection_id || null;
		this.MedPersonal_id = arguments[0].MedPersonal_id || null;
		
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
		
		var form_panel = this.findById('EvnUslugaProcRequestEditForm');
		var base_form = form_panel.getForm();
		
		var usluga_complex_combo = base_form.findField('UslugaComplex_id');	
		var usluga_setdate = base_form.findField('EvnUslugaPar_setDate');
		var lpu_section_combo = base_form.findField('LpuSection_uid');
		var med_personal_combo = base_form.findField('MedStaffFact_uid');
		var mid_med_personal_combo = base_form.findField('MedStaffFact_sid');
		var org_combo = base_form.findField('Org_uid');

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		base_form.reset();
		base_form.setValues(arguments[0]);
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

		var evn_usluga_par_id = arguments[0].EvnUslugaPar_id;
		
		//загружаем файлы
		this.FileUploadPanel.reset();
		this.FileUploadPanel.listParams = {
			Evn_id: evn_usluga_par_id
		};	
		this.FileUploadPanel.loadData({
			Evn_id: evn_usluga_par_id
		});
		base_form.load({
			failure: function() {
				loadMask.hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {this.hide();}.createDelegate(this) );
			}.createDelegate(this),
			params: {
				'EvnUslugaPar_id': evn_usluga_par_id
			},
			success: function() {
				var
					index,
					lpu_section_uid = lpu_section_combo.getValue(),
					lpu_uid,
					med_personal_sid = base_form.findField('MedPersonal_sid').getValue(),
					med_personal_uid = base_form.findField('MedPersonal_uid').getValue(),
					org_uid = org_combo.getValue(),
					usluga_complex_id = usluga_complex_combo.getValue();
				
				if ( Ext.isEmpty(lpu_section_uid) ) {
					lpu_section_uid = this.LpuSection_id;
				}
				
				var params = {
					OrgType: 'lpu'
				};
				
				if ( Ext.isEmpty(org_uid) ) {
					params.Lpu_oid = this.Lpu_id;
				}
				else {
					params.Org_id = org_uid;
				}

				if ( Ext.isEmpty(usluga_setdate.getValue()) ) {
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
					callback: function() {
						this.setValue(this.getValue());
					}.createDelegate(usluga_complex_combo),
					params: {
						UslugaComplex_id: usluga_complex_id
					}
				});
				usluga_complex_combo.disable();
				
				var MedPersonal_id  = this.MedPersonal_id;
				
				org_combo.getStore().load({
					callback: function(records, options, success) {
						if ( !success ) {
							loadMask.hide();
							return false;
						}

						if (org_combo.getStore().getCount()>0) {
							org_combo.setValue(org_combo.getStore().getAt(0).get('Org_id')); 
							lpu_uid = org_combo.getStore().getAt(0).get('Lpu_id');
						}

						// Врачи
						med_personal_combo.getStore().load({
							params: {
								Lpu_id: lpu_uid
							},
							callback: function(records, options, success) {
								// ср.медперсонал
								mid_med_personal_combo.getStore().load({
									params: {
										Lpu_id: lpu_uid
									},
									callback: function(records, options, success) {
										// отделения
										lpu_section_combo.getStore().load({
											params: {
												Lpu_id: lpu_uid
											},
											callback: function(records, options, success) {
												loadMask.hide();

												index = lpu_section_combo.getStore().findBy(function(r) {
													return (r.get('LpuSection_id') == lpu_section_uid);
												});

												if ( index >= 0 ) {
													lpu_section_combo.setValue(lpu_section_uid);
												}
												else {
													lpu_section_combo.clearValue();
												}
												
												lpu_section_combo.fireEvent('change', lpu_section_combo, lpu_section_combo.getValue());

												if ( Ext.isEmpty(med_personal_uid) ) {
													med_personal_uid = MedPersonal_id;
												}

												index = med_personal_combo.getStore().findBy(function(r) {
													return (r.get('MedPersonal_id') == med_personal_uid && r.get('LpuSection_id') == lpu_section_uid);
												});

												if ( index >= 0 ) {
													med_personal_combo.setValue(med_personal_combo.getStore().getAt(index).get('MedStaffFact_id'));
												}
												else {
													med_personal_combo.clearValue();
												}

												index = mid_med_personal_combo.getStore().findBy(function(r) {
													return (r.get('MedPersonal_id') == med_personal_sid);
												});

												if ( index >= 0 ) {
													mid_med_personal_combo.setValue(mid_med_personal_combo.getStore().getAt(index).get('MedStaffFact_id'));
												}
												else {
													mid_med_personal_combo.setValue(getGlobalOptions().medstafffact[0]);
												}
											}
										});	
									}
								});
							}
						});
					},
					params: params
				});
				if(this.FileUploadPanel.lastItemsIndex<0){
					this.FileUploadPanel.buttons[0].disable();
				}
				this.syncSize();
				this.doLayout();
			}.createDelegate(this),
			url: '/?c=EvnFuncRequest&m=loadEvnUslugaEditForm'
		});
			
		
	},
	title: lang['rezultat_vyipolneniya_uslugi']
});
