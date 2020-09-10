/**
* swTreatmentEditWindow - окно добавления, редактирования обращения.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swTreatmentEditWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      4.08.2010
* @comment      Префикс для id компонентов TEW (TreatmentEditWindow). TABINDEX_TEW
*/

sw.Promed.swTreatmentEditWindow = Ext.extend(sw.Promed.BaseForm, {
	formStatus: 'edit',
	id: 'TreatmentEditWindow',
	title: lang['registratsiya_obrascheniy_dobavlenie'],
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	maximized: true,
	//minimizable: false,
	modal: false,
	plain: false,
	resizable: false,
	width : 700,
	height : 570,
	autoScroll: true,
	border : false,
	initComponent: function() {
		var current_window = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({});
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_TEW + 21,
				text: BTN_FRMSAVE,
				tooltip: lang['sohranit_vvedennyie_dannyie']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_TEW + 22,
				text: BTN_FRMCANCEL,
				tooltip: lang['zakryit_okno']
			}],
			buttonAlign : "right",
			items : [
				new Ext.form.FormPanel({
					animCollapse: false,
					autoHeight: true,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					buttonAlign: 'left',
					frame: false,
					id: 'TreatmentEditForm',
					labelAlign: 'right',
					labelWidth: 180,
					reader: new Ext.data.JsonReader({
						success: Ext.emptyFn
					}, [
						{ name: 'Treatment_id' },
						{ name: 'Treatment_Reg' },
						{ name: 'Treatment_DateReg' },
						{ name: 'TreatmentUrgency_id' },
						{ name: 'TreatmentMultiplicity_id' },
						{ name: 'TreatmentSenderType_id' },
						{ name: 'Treatment_SenderDetails' },
						{ name: 'TreatmentType_id' },
						{ name: 'TreatmentCat_id' },
						{ name: 'TreatmentRecipientType_id' },
						{ name: 'Lpu_rid' },
						{ name: 'TreatmentSubjectType_id' },
						{ name: 'Org_sid' },
						{ name: 'MedPersonal_sid' },
						{ name: 'MedPersonal_Lpu_sid' },
						{ name: 'Lpu_sid' },
						{ name: 'Treatment_Text' },
						{ name: 'Treatment_Document' },
						{ name: 'TreatmentMethodDispatch_id' },
						{ name: 'Treatment_Comment' },
						{ name: 'TreatmentReview_id' },
						{ name: 'Treatment_SenderPhone' },
						{ name: 'TreatmentMethodDispatch_fid' },
						{ name: 'TreatmentFeedback_Message' },
						{ name: 'TreatmentFeedback_Note' },
						{ name: 'TreatmentFeedback_Document' },
						{ name: 'Treatment_DateReview' }
					]),
					url: '/?c=Treatment&m=saveTreatment',
					items: [{
						autoHeight: true,
						style: 'padding: 0px;',
						title: lang['registratsiya'],
						xtype: 'fieldset',
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [
								{
									fieldLabel: lang['sozdatel'],
									disabled: true,
									id: 'TEW_pmUser_login',
									name: 'pmUser_login',
									width: 100,
									value: UserLogin,
									xtype: 'textfield'
								},
								{
									id: 'TEW_Treatment_id',
									name: 'Treatment_id',
									xtype: 'hidden'
								},
								{
									id: 'TEW_Treatment_CurDate',
									name: 'Treatment_CurDate',
									xtype: 'hidden'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [
								{ 
									fieldLabel: lang['nomer_registratsii'],
									allowBlank: false,
									disabled: false,
									tabIndex: TABINDEX_TEW + 1,
									maxLength: 50,
									id: 'TEW_Treatment_Reg',
									name: 'Treatment_Reg',
									width: 100,
									value: '',
									xtype: 'numberfield',
									allowNegative: false,
									allowDecimals: false,
									decimalPrecision: 0
								},
								{
									fieldLabel: lang['data_registratsii'],
									allowBlank: false,
									disabled: false,
									tabIndex: TABINDEX_TEW + 2,
									id: 'TEW_Treatment_DateReg',
									name: 'Treatment_DateReg',
									format: 'd.m.Y',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									width: 100,
									xtype: 'swdatefield',
									listeners: {
										'select': function(date_field, date){
											if ( date ) {
												Ext.getCmp('TEW_Treatment_DateReview').setMinValue(date);
											}
											date_field.focus(false);
										}
									}
								}]
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: lang['obraschenie'],
						xtype: 'fieldset',
						items: [
						{
							fieldLabel: lang['srochnost'],
							allowBlank: false,
							disabled: false,
							tabIndex: TABINDEX_TEW + 3,
							width: 250,
							value: 2, // По умолчанию – «Нормальная».
							comboSubject: 'TreatmentUrgency',
							id: 'TEW_TreatmentUrgency_id',
							autoLoad: false,
							xtype: 'swcommonsprcombo'
						},
						{ 
							fieldLabel: lang['kratnost_obrascheniya'],
							allowBlank: false,
							disabled: false,
							comboSubject: 'TreatmentMultiplicity',
							id: 'TEW_TreatmentMultiplicity_id',
							tabIndex: TABINDEX_TEW + 4,
							width: 250,
							value: 1,
							autoLoad: false,
							xtype: 'swcommonsprcombo'
						},
						{ 
							fieldLabel: lang['tip_obrascheniya'],
							allowBlank: false,
							disabled: false,
							comboSubject: 'TreatmentType',
							id: 'TEW_TreatmentType_id',
							tabIndex: TABINDEX_TEW + 5,
							width: 250,
							value: 4, // По умолчанию – «Жалоба».
							autoLoad: false,
							xtype: 'swcommonsprcombo'
						},
						{
							fieldLabel: lang['tip_initsiatora_obrascheniya'],
							allowBlank: false,
							disabled: false,
							comboSubject: 'TreatmentSenderType',
							id: 'TEW_TreatmentSenderType_id',
							tabIndex: TABINDEX_TEW + 6,
							width: 250,
							value: '',
							autoLoad: false,
							xtype: 'swcommonsprcombo'
						},
						{
							editable: false,
							fieldLabel: 'Инициатор обращения',
							allowBlank: true,
							disabled: false,
							tabIndex: TABINDEX_TEW + 7,
							hiddenName: 'Person_id',
							id: 'TEW_Person_id',
							width: 250,
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
											if (!!personData.Person_Phone) {
												Ext.getCmp('TEW_Treatment_SenderPhone').setValue(personData.Person_Phone);
											}
										}
										getWnd('swPersonSearchWindow').hide();
									},
									onClose: function() {combo.focus(true, 500)}
								});
							},
							onTrigger2Click: function() {
								this.clearValue();
								Ext.getCmp('TEW_Treatment_SenderDetails').setDisabled(false);
								Ext.getCmp('TEW_Treatment_SenderPhone').setValue('');
							},
							enableKeyEvents: true,
							listeners: {
								'change': function(combo) {
									Ext.getCmp('TEW_Treatment_SenderDetails').setDisabled(!!combo.getValue());
									if (!!combo.getValue()) {
										Ext.getCmp('TEW_Treatment_SenderDetails').setValue('');
									}
								}
							}
						},
						{
							fieldLabel: 'Инициатор обращения (ручной ввод)',
							allowBlank: true,
							disabled: false,
							tabIndex: TABINDEX_TEW + 7,
							maxLength: 255,
							name: 'Treatment_SenderDetails',
							id: 'TEW_Treatment_SenderDetails',
							width: 250,
							value: '',
							xtype: 'textfield'
						}, 
						{
							fieldLabel: 'Телефон инициатора'+' +7',
							name: 'Treatment_SenderPhone',
							id: 'TEW_Treatment_SenderPhone',
							tabIndex: TABINDEX_TEW + 7,
							fieldWidth: 250,
							xtype: 'swphonefield'
						},
						{
							allowBlank: false,
							fieldLabel: lang['sposob_polucheniya_obrascheniya'],
							comboSubject: 'TreatmentMethodDispatch',
							id: 'TEW_TreatmentMethodDispatch_id',
							tabIndex: TABINDEX_TEW + 8,
							width: 250,
							listWidth: 400,
							xtype: 'swcommonsprcombo'
						},
						{
							allowBlank: false,
							width: 250,
							listWidth: 400,
							fieldLabel: lang['kategoriya_obrascheniya'],
							comboSubject: 'TreatmentCat',
							id: 'TEW_TreatmentCat_id',
							tabIndex: TABINDEX_TEW + 9,
							xtype: 'swcommonsprcombo'
						},
						{
							border: false,
							layout: 'column',
							items: [
							{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: false,
									fieldLabel: lang['adresat_obrascheniya'],
									comboSubject: 'TreatmentRecipientType',
									id: 'TEW_TreatmentRecipientType_id',
									tabIndex: TABINDEX_TEW + 10,
									width: 250,
									xtype: 'swcommonsprcombo',
									listeners: {
										'beforeselect': function(combo, record){
											if (record.get(combo.valueField)) {
												var rectype = record.get(combo.valueField);
												if (rectype == 1)
												{
													current_window.enableField('TEW_Lpu_rid');
													Ext.getCmp('TEW_Lpu_rid').focus(true, 250);
												}
												else
												{
													current_window.disableField('TEW_Lpu_rid', true );
												}
											}
										}
									}
								}]
							},
							{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									disabled: false,
									tabIndex: TABINDEX_TEW + 11,
									width: 200,
									listWidth: 400,
									hideLabel : true,
									emptyText: lang['lpu_adresat_obrascheniya'],
									autoLoad: true,
									id: 'TEW_Lpu_rid',
									hiddenName: 'Lpu_rid',
									xtype: 'swlpulocalcombo'
								}]
							}]
						},
						{
							fieldLabel: lang['subyekt_obrascheniya'],
							disabled: true,
							name: 'SubjectName',
							id: 'TEW_SubjectName',
							width: 450,
							value: '',
							xtype: 'textfield'
						},
						{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [
								{
									fieldLabel: lang['tip_subyekta_obrascheniya'],
									allowBlank: false,
									disabled: false,
									comboSubject: 'TreatmentSubjectType',
									id: 'TEW_TreatmentSubjectType_id',
									tabIndex: TABINDEX_TEW + 12,
									width: 250,
									value: '',
									enableKeyEvents: true,
									autoLoad: false,
									xtype: 'swcommonsprcombo',
									listeners: {
										'beforeselect': function(combo, record){
											if (record.get(combo.valueField)) {
												Ext.getCmp('TEW_SubjectName').setValue('');
												var rectype = record.get(combo.valueField);
												var subject = null;
												var subject_field = Ext.getCmp('TEW_SubjectName');
												var org_combo = current_window.findById('TEW_Org_sid');
												var lpu_combo = current_window.findById('TEW_Lpu_sid');
												var doc_combo = current_window.findById('TEW_MedPersonal_sid');
												var doclpu_combo = current_window.findById('TEW_MedPersonal_Lpu_sid');
												switch ( rectype ) {
													case 1://org
														if ( 0 == org_combo.getStore().getCount() ) 
														{
															org_combo.getStore().load({
																callback: function() {
																	org_combo.setValue(org_combo.getValue());
																	subject = current_window.getDisplayField('TEW_Org_sid', org_combo.getValue());
																	subject_field.setValue(subject);
																}
															});
														}
														else
														{
															subject = current_window.getDisplayField('TEW_Org_sid', org_combo.getValue());
															subject_field.setValue(subject);
														}
														org_combo.setContainerVisible(true);
														if (current_window.action != 'view') {
															org_combo.enable();
														}
														lpu_combo.setContainerVisible(false);
														doc_combo.setContainerVisible(false);
														doclpu_combo.setContainerVisible(false);
														lpu_combo.clearValue();
														doc_combo.clearValue();
														doclpu_combo.clearValue();
													break;
													case 2://lpu
														if ( 0 == lpu_combo.getStore().getCount() ) 
														{
															lpu_combo.getStore().load({
																callback: function() {
																	lpu_combo.setValue(lpu_combo.getValue());
																	subject = current_window.getDisplayField('TEW_Lpu_sid', lpu_combo.getValue());
																	subject_field.setValue(subject);
																}
															});
														}
														else
														{
															subject = current_window.getDisplayField('TEW_Lpu_sid', lpu_combo.getValue());
															subject_field.setValue(subject);
														}
														org_combo.setContainerVisible(false);
														lpu_combo.setContainerVisible(true);
														if (current_window.action != 'view') {
															lpu_combo.enable();
														}
														doc_combo.setContainerVisible(false);
														doclpu_combo.setContainerVisible(false);
														org_combo.clearValue();
														doc_combo.clearValue();
														doclpu_combo.clearValue();
													break;
													case 3://doctor
														doc_combo.getStore().removeAll();
														doc_combo.getStore().load({
															params: {
																Lpu_id: doclpu_combo.getValue()
															},
															callback: function() {
																doc_combo.setValue(doc_combo.getValue());
																subject = current_window.getDisplayField('TEW_MedPersonal_sid', doc_combo.getValue()) + ' ' + current_window.getDisplayField('TEW_MedPersonal_Lpu_sid', doclpu_combo.getValue());
																subject_field.setValue(subject);
															}
														});
														org_combo.setContainerVisible(false);
														lpu_combo.setContainerVisible(false);
														doc_combo.setContainerVisible(true);
														doclpu_combo.setContainerVisible(true);
														if (current_window.action != 'view') {
															doc_combo.enable();
															doclpu_combo.enable();
														}
														org_combo.clearValue();
														lpu_combo.clearValue();
													break;
												}
											}
										}
									}
								}
								]
							}, {
								border: false,
								layout: 'form',
								items: [
								{
									// При выборе значения "Врач" справочника 'Тип субъекта обращения' появляются форма выбора ЛПУ
									allowBlank: true,
									disabled: false,
									autoLoad: true,
									id: 'TEW_MedPersonal_Lpu_sid',
									hiddenName: 'MedPersonal_Lpu_sid',
									tabIndex: TABINDEX_TEW + 13,
									width: 200,
									listWidth: 400,
									xtype: 'swlpulocalcombo',
									hideLabel : true,
									emptyText: lang['mesto_rabotyi_vracha'],
									listeners: {
										'beforeselect': function(combo, record){
											if (record.get(combo.valueField)) {
											//if (record.get(combo.displayField)) {
												//var recttitle = record.get(combo.displayField);
												//Ext.getCmp('TEW_SubjectName').setValue(recttitle);
												var med_personal_combo = Ext.getCmp('TEW_MedPersonal_sid');
												med_personal_combo.clearValue();
												med_personal_combo.getStore().removeAll();
												med_personal_combo.getStore().load({
													params: {
														Lpu_id: record.get(combo.valueField)
													}
												});
												Ext.getCmp('TEW_MedPersonal_sid').focus(true, 100);
											}
										}
									}
								},
								{ // При выборе значения "Врач" справочника 'Тип субъекта обращения' появляются соответственно форма выбора Врача из списка врачей данного ЛПУ
									//fieldLabel: 'Врач (субъект)',
									allowBlank: true,
									disabled: false,
									tabIndex: TABINDEX_TEW + 14,
									width: 200,
									listWidth: 400,
									id: 'TEW_MedPersonal_sid',
									hiddenName: 'MedPersonal_sid',
									xtype: 'swmedpersonalcombo',
									hideLabel : true,
									emptyText: lang['vrach_subyekt'],
									listeners: {
										'beforeselect': function(combo, record){
											if (record.get(combo.displayField)) {
												var doctor_name = record.get(combo.displayField);
												var doctor_work_combo = Ext.getCmp('TEW_MedPersonal_Lpu_sid');
												var doctor_work = '';
												if ( 0 < doctor_work_combo.getStore().getCount() )
													doctor_work = current_window.getDisplayField('TEW_MedPersonal_Lpu_sid', doctor_work_combo.getValue());
												Ext.getCmp('TEW_SubjectName').setValue(doctor_name + ' ' + doctor_work);
											}
										}
									}
								},
								{ 
									// комбо выбора из списка организации субъекта обращения
									allowBlank: true,
									disabled: false,
									width: 200,
									listWidth: 400,
									tabIndex: TABINDEX_TEW + 13,
									id: 'TEW_Org_sid',
									hiddenName: 'Org_sid',
									autoLoad: true,
									xtype: 'sworgcombo',
									hideLabel : true,
									emptyText: lang['organizatsiya_subyekt'],
									onTrigger1Click: function() {
										var combo = this;
										getWnd('swOrgSearchWindow').show({
											onSelect: function(orgData) {
												if ( orgData.Org_id > 0 )
												{
													combo.getStore().load({
														params: {
															Object:'Org',
															Org_id: orgData.Org_id,
															Org_Name:''
														},
														callback: function()
														{
															combo.setValue(orgData.Org_id);
															combo.focus(true, 500);
														}
													});
												}
												getWnd('swOrgSearchWindow').hide();
											},
											onClose: function() {combo.focus(true, 200)}
										});
									},
									enableKeyEvents: true,
									listeners: {
										'keydown': function( inp, e ) {
											if ( e.F4 == e.getKey() )
											{
												inp.onTrigger1Click();
											}
										},
										'beforeselect': function(combo, record){
											if (record.get(combo.displayField)) {
												var recttitle = record.get(combo.displayField);
												Ext.getCmp('TEW_SubjectName').setValue(recttitle);
											}
										}
									}
								},
								{
									// В зависимости от выбора значения справочника 'Тип субъекта обращения' появляются соответственно форма выбора из списка ЛПУ
									allowBlank: true,
									disabled: false,
									tabIndex: TABINDEX_TEW + 13,
									width: 200,
									listWidth: 400,
									autoLoad: true,
									id: 'TEW_Lpu_sid',
									hiddenName: 'Lpu_sid',
									xtype: 'swlpulocalcombo',
									hideLabel : true,
									emptyText: lang['lpu_subyekt'],
									listeners: {
										'beforeselect': function(combo, record){
											if (record.get(combo.displayField)) {
												var recttitle = record.get(combo.displayField);
												Ext.getCmp('TEW_SubjectName').setValue(recttitle);
											}
										}
									}
								}]
							}]
						},
						{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: langs('Статус обращения'),
									allowBlank: false,
									disabled: false,
									comboSubject: 'TreatmentReview',
									tabIndex: TABINDEX_TEW + 15,
									width: 170,
									value: 1,
									id: 'TEW_TreatmentReview_id',
									enableKeyEvents: true,
									autoLoad: false,
									xtype: 'swcommonsprcombo',
									listeners: {
										'beforeselect': function(combo, record){
											if (record.get(combo.valueField)) {
												var rectype = record.get(combo.valueField);
												if ( rectype  == 1) // Не рассмотрено
												{
														current_window.disableField('TEW_Treatment_DateReview', true);
														Ext.getCmp('TEW_Treatment_DateReview').setValue('');
												}
												else
												{
														current_window.enableField('TEW_Treatment_DateReview');
														Ext.getCmp('TEW_Treatment_DateReview').focus(true, 250);
												}
												Ext.getCmp('TEW_Answer').setVisible(rectype == 2);
												Ext.getCmp('TEW_TreatmentMethodDispatch_fid').setAllowBlank(rectype != 2);
												if (rectype != 2) {
													Ext.getCmp('TEW_TreatmentMethodDispatch_fid').setValue('');
												}
											}
										}
									}
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['data_rassmotreniya'],
									allowBlank: true,
									disabled: true,
									tabIndex: TABINDEX_TEW + 16,
									id: 'TEW_Treatment_DateReview',
									name: 'Treatment_DateReview',
									format: 'd.m.Y',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
									width: 100,
									xtype: 'swdatefield'
								}]
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: lang['dopolnitelno'],
						xtype: 'fieldset',
						items: [
						{
							fieldLabel: langs('Текст обращения'),
							allowBlank: true,
							disabled: false,
							tabIndex: TABINDEX_TEW + 17,
							maxLength: 8000,
							maxLengthText: lang['vyi_prevyisili_maksimalnyiy_razmer_soobscheniya_8000_simvolov'],
							id: 'TEW_Treatment_Text',
							name: 'Treatment_Text',
							value: '',
							height: 80,
							width: 450,
							xtype: 'textarea'
						},
						{
							fieldLabel: langs('Примечание к обращению'),
							allowBlank: true,
							disabled: false,
							tabIndex: TABINDEX_TEW + 18,
							maxLength: 8000,
							maxLengthText: lang['vyi_prevyisili_maksimalnyiy_razmer_8000_simvolov'],
							name: 'Treatment_Comment',
							id: 'TEW_Treatment_Comment',
							value: '',
							height: 80,
							width: 450,
							xtype: 'textarea'
						},
						{
							border: false,
							layout : 'column',
							items : [{
								border: false,
								layout : 'form',
								width : 200,
								items : [{
									id: 'TEW_Treatment_Document',
									name: 'Treatment_Document',
									xtype: 'hidden'
								},
								{
									text : lang['prikreplennyie_dokumentyi'],
									id: 'TEW_FilesLabel',
									width : 180,
									style : 'font-size: 12px; text-align: right; padding: 5px 5px 0',
									xtype: 'label'
								}]
							}, {
								border: false,
								layout : 'form',
								width : 210,
								items : [
									new Ext.Panel({
										fieldLabel: lang['prikrep_dokumentyi'],
										autoHeight: true,
										id : 'TEW_FilesPanel',
										split : true,
										hidden: false,
										html : '',
										border : false
									})]
							}, {
								border: false,
								layout : 'form',
								items : [
								{
									xtype : 'button',
									text : lang['prikrepit'],
									tabIndex : TABINDEX_TEW + 19,
									style : 'margin: 0px 2px 0px 3px;',
									iconCls : 'add16',
									id : 'TEW_FileUploadButton',
									handler : function() {
										if(current_window.action == 'view') {
											return false;
										}
										var params = new Object();
										//params.action = action;
										params.FilesData = Ext.getCmp('TEW_Treatment_Document').getValue();
										params.callback = function(data) {
											if ( !data )
											{
												return false;
											}
											Ext.getCmp('TEW_Treatment_Document').setValue(data);
											Ext.getCmp('TEW_FilesPanel').removeAll();
											var response_obj = Ext.util.JSON.decode(data);
											var id = Ext.getCmp('TEW_Treatment_id').getValue();
											if ( ! id ) id = 0;
											current_window.createFilesLinks( response_obj, id );
										};
										getWnd('swFileUploadWindow').show(params);
									}
								}]
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: 'Ответ на обращение',
						id: 'TEW_Answer',
						xtype: 'fieldset',
						items: [{
							fieldLabel: 'Ответ предоставлен',
							comboSubject: 'TreatmentMethodDispatch',
							hiddenName: 'TreatmentMethodDispatch_fid',
							id: 'TEW_TreatmentMethodDispatch_fid',
							allowBlank: true,
							tabIndex: TABINDEX_ETSF + 19,
							width: 255,
							xtype: 'swcommonsprcombo'
						}, {
							fieldLabel: 'Текст ответа',
							allowBlank: true,
							disabled: false,
							tabIndex: TABINDEX_TEW + 19,
							maxLength: 8000,
							maxLengthText: langs('Вы превысили максимальный размер сообщения 8000 символов'),
							name: 'TreatmentFeedback_Message',
							id: 'TEW_TreatmentFeedback_Message',
							value: '',
							height: 80,
							width: 450,
							xtype: 'textarea'
						}, {
							fieldLabel: 'Примечание',
							allowBlank: true,
							disabled: false,
							tabIndex: TABINDEX_TEW + 19,
							maxLength: 8000,
							maxLengthText: langs('Вы превысили максимальный размер сообщения 8000 символов'),
							name: 'TreatmentFeedback_Note',
							id: 'TEW_TreatmentFeedback_Note',
							value: '',
							height: 80,
							width: 450,
							xtype: 'textarea'
						},
						{
							border: false,
							layout : 'column',
							id: 'Request_FilesPanel_' + current_window.id,
							items : [{
								border: false,
								layout : 'form',
								width : 200,
								items : [
									{
										id: 'TEW_TreatmentFeedback_Document',
										name: 'TreatmentFeedback_Document',
										xtype: 'hidden'
									},
									{
										text : langs('Прикрепленные документы'),
										id: 'TEW_TreatmentFeedback_FilesLabel',
										width : 180,
										style : 'font-size: 12px; text-align: right; padding: 5px 5px 0',
										xtype: 'label'
									}
								]
							},
							{
								border: false,
								layout : 'form',
								width : 210,
								items : [
									new Ext.Panel({
										fieldLabel: langs('Прикреп. документы'),
										autoHeight: true,
										id : 'TEW_TreatmentFeedback_FilesPanel',
										split : true,
										hidden: false,
										html : '',
										border : false
									})
								]
							},
							{
								border: false,
								layout : 'form',
								items : [{
									xtype : 'button',
									text : langs('Прикрепить'),
									tabIndex : TABINDEX_TEW + 19,
									style : 'margin: 0px 2px 0px 3px;',
									iconCls : 'add16',
									id : 'TEW_TreatmentFeedback_FileUploadButton',
									handler : function() {
										var params = {};
										//params.action = action;
										params.FilesData = Ext.getCmp('TEW_TreatmentFeedback_Document').getValue();
										params.callback = function(data) {
											if ( !data ) return false;
											Ext.getCmp('TEW_TreatmentFeedback_Document').setValue(data);
											Ext.getCmp('TEW_TreatmentFeedback_FilesPanel').removeAll();
											var response_obj = Ext.util.JSON.decode(data);
											var form = current_window.getFormPanel()[0].getForm();
											var id = form.findField('Treatment_id').getValue();
											if ( ! id ) id = 0;
											current_window.createFilesLinksFeedback( response_obj, id );
										};
										getWnd('swFileUploadWindow').show(params);
									}
								}]
							}]
						}]
					}]
				})
			]
		});
		sw.Promed.swTreatmentEditWindow.superclass.initComponent.apply(this, arguments);
	},
	enableField: function(id) {
		var cmp = Ext.getCmp(id);
		cmp.enable();
		//cmp.setVisible(true);
		cmp.allowBlank = false;
	},
	disableField: function(id, visible) {
		var cmp = Ext.getCmp(id);
		cmp.disable();
		//cmp.setVisible(visible);
		cmp.setValue('');
		cmp.allowBlank = true;
	},
	getDisplayField: function(id_combo, id) {
		var combo = this.findById('TreatmentEditForm').findById(id_combo);
		var record = combo.getStore().getById(id);
		if ( !record )
		{
			var record = combo.getStore().getAt( (id - 1) );
			if ( !record ) return combo.lastSelectionText;
			else return record.get(combo.displayField);
		}
		else
			return record.get(combo.displayField);
	},
	setCurrentDate: function() {
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					this.Date = response_obj.begDate;
					this.findById('TreatmentEditForm').findById('TEW_Treatment_CurDate').setValue(response_obj.begDate);
					this.findById('TreatmentEditForm').findById('TEW_Treatment_DateReg').setMaxValue( response_obj.begDate )
					this.findById('TreatmentEditForm').findById('TEW_Treatment_DateReview').setMaxValue( response_obj.begDate )
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	loadCombo: function(id_combo, value) {
		var combo = this.findById('TreatmentEditForm').findById(id_combo);
		combo.getStore().baseParams.Object = combo.comboSubject;
		if(!value) {
			value = combo.getValue();
		}
		switch ( combo.comboSubject ) {
			case 'TreatmentCat':
				combo.getStore().baseParams.TreatmentCat_id = value;
				combo.getStore().baseParams.TreatmentCat_Name = '';
				break;
			case 'TreatmentMethodDispatch':
				combo.getStore().baseParams.TreatmentMethodDispatch_id = value;
				combo.getStore().baseParams.TreatmentMethodDispatch_Name = '';
				break;
			case 'TreatmentRecipientType':
				combo.getStore().baseParams.TreatmentRecipientType_id = value;
				combo.getStore().baseParams.TreatmentRecipientType_Name = '';
				break;
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
		if ( 0 == combo.getStore().getCount() ) 
		{
			combo.getStore().load({
				callback: function() {
					combo.setValue(value);
				}
			});
		}
		else
		{
			combo.setValue(value);
		}
	},
	setFieldsDisabled: function(d) {
		this.findById('TreatmentEditForm').getForm().items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset'))
			{
				f.setDisabled(d);
			}
		});
		this.findById('TEW_FileUploadButton').setDisabled(d);
		this.buttons[0].setDisabled(d);
	},
	show: function() {
		var current_window = this;
		var form = current_window.findById('TreatmentEditForm');
		sw.Promed.swTreatmentEditWindow.superclass.show.apply(this, arguments);
		this.center();
		form.getForm().reset();
		current_window.action = null;
		current_window.treatmenttype_id = null;
		var treatment_datereg = null;
		var treatment_id = null;
		current_window.callback = Ext.emptyFn;
		current_window.owner = null;
		current_window.onHide = Ext.emptyFn;
		if ( arguments[0] ) {
			if ( arguments[0].action )
				current_window.action = arguments[0].action;

			if ( arguments[0].callback )
				current_window.callback = arguments[0].callback;

			if ( arguments[0].owner ) {
				current_window.owner = arguments[0].owner;
			}

			if ( arguments[0].onHide )
				current_window.onHide = arguments[0].onHide;

			if ( arguments[0].TreatmentType_id )
				current_window.treatmenttype_id = arguments[0].TreatmentType_id;

			if ( arguments[0].Treatment_setDateReg )
				treatment_datereg = arguments[0].Treatment_setDateReg;

			if ( arguments[0].Treatment_id )
				treatment_id = arguments[0].Treatment_id;

			//log(arguments[0]);
		}
		if ( treatment_datereg )
		{
			form.getForm().setValues({
				TEW_Treatment_DateReg: treatment_datereg
			});
			form.findById('TEW_Treatment_DateReview').setMinValue( treatment_datereg );
		}
		else
		{
			form.getForm().setValues({
				TEW_Treatment_DateReg: ''
			});
		}
		if ( treatment_id )
		{
			form.getForm().setValues({
				TEW_Treatment_id: treatment_id
			});
		}
		else
		{
			form.getForm().setValues({
				TEW_Treatment_id: ''
			});
		}
		
		Ext.getCmp('TEW_TreatmentMethodDispatch_fid').enable();
		Ext.getCmp('TEW_TreatmentFeedback_Message').enable();
		Ext.getCmp('TEW_TreatmentFeedback_Note').enable();
		Ext.getCmp('TEW_TreatmentFeedback_FileUploadButton').enable();

		this.setCurrentDate();
		if ( this.action ) {
			switch ( this.action ) {
				case 'add':
					this.setTitle(lang['dobavlenie_obraschenie']);
					this.setFieldsDisabled(false);
					this.loadCombo('TEW_TreatmentMultiplicity_id', 1);
					this.loadCombo('TEW_TreatmentReview_id', 1);
					this.loadCombo('TEW_TreatmentSenderType_id', '');
					this.loadCombo('TEW_TreatmentSubjectType_id', '');
					this.loadCombo('TEW_TreatmentType_id', 4);
					this.loadCombo('TEW_TreatmentUrgency_id', 2);
					if ( this.treatmenttype_id )
					{
						form.getForm().setValues({
							TEW_TreatmentType_id: this.treatmenttype_id
						});
					}
					else
					{
						form.getForm().setValues({
							TEW_TreatmentType_id: 4
						});
					}
					this.disableField('TEW_Lpu_rid', true );
					this.disableField('TEW_Org_sid', false );
					this.disableField('TEW_Lpu_sid', false );
					this.disableField('TEW_MedPersonal_Lpu_sid', false );
					this.disableField('TEW_MedPersonal_sid', false );
					this.disableField('TEW_Treatment_DateReview', true );
					Ext.getCmp('TEW_FilesPanel').removeAll();
					Ext.getCmp('TEW_Treatment_Document').setValue('');
					form.getForm().findField('TEW_Treatment_Reg').focus(100, true);
					Ext.getCmp('TEW_Answer').hide();
					Ext.getCmp('TEW_TreatmentMethodDispatch_fid').setAllowBlank(true);
					Ext.getCmp('TEW_Treatment_SenderDetails').setDisabled(false);
					break;

				case 'view':
				case 'edit':
					this.setTitle(((this.action == 'edit')?'Редактирование':'Просмотр')+': Обращение');
					this.setFieldsDisabled(false);
					var Mask = new Ext.LoadMask(Ext.get('TreatmentEditWindow'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..."} );
					Mask.show();
					form.getForm().load({
						failure: function() {
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { this.hide(); }.createDelegate(this) );
						},
						params: {
							Treatment_id: treatment_id
						},
						success: function(f, r) {
							var obj = Ext.util.JSON.decode(r.response.responseText)[0];
							Mask.hide();
							this.loadCombo('TEW_TreatmentMultiplicity_id');
							this.loadCombo('TEW_TreatmentReview_id');
							this.loadCombo('TEW_TreatmentSenderType_id');
							this.loadCombo('TEW_TreatmentSubjectType_id');
							this.loadCombo('TEW_TreatmentType_id');
							this.loadCombo('TEW_TreatmentUrgency_id');
							this.loadCombo('TEW_TreatmentCat_id');
							this.loadCombo('TEW_TreatmentMethodDispatch_id');
							this.loadCombo('TEW_TreatmentRecipientType_id');
							if (form.findById('TEW_TreatmentRecipientType_id').getValue() == 1) // Адресат ЛПУ
							{
								this.enableField('TEW_Lpu_rid');
							}
							else
							{
								this.disableField('TEW_Lpu_rid', true );
							}
							form.findById('TEW_TreatmentSubjectType_id').fireEvent('beforeselect',form.findById('TEW_TreatmentSubjectType_id'),form.findById('TEW_TreatmentSubjectType_id').getStore().getById(form.findById('TEW_TreatmentSubjectType_id').getValue()));

							if ( form.findById('TEW_TreatmentReview_id').getValue()  == 1) // Не рассмотрено
							{
								this.disableField('TEW_Treatment_DateReview', true);
							}
							else
							{
								if ( form.findById('TEW_TreatmentReview_id').getValue() == 2){
									form.findById('TEW_TreatmentReview_id').disable();
								}else{form.findById('TEW_TreatmentReview_id').enable();}
								this.enableField('TEW_Treatment_DateReview');
							}
							Ext.getCmp('TEW_TreatmentRecipientType_id').setValue(form.findById('TEW_TreatmentRecipientType_id').getValue());
							Ext.getCmp('TEW_TreatmentCat_id').setValue(form.findById('TEW_TreatmentCat_id').getValue());
							Ext.getCmp('TEW_TreatmentMethodDispatch_id').setValue(form.findById('TEW_TreatmentMethodDispatch_id').getValue());
							Ext.getCmp('TEW_FilesPanel').removeAll();
							if (Ext.getCmp('TEW_Treatment_Document').getValue().length > 0)
							{
								var response_obj = Ext.util.JSON.decode(Ext.getCmp('TEW_Treatment_Document').getValue());
								this.createFilesLinks(response_obj, treatment_id);
							}
							Ext.getCmp('TEW_TreatmentFeedback_FilesPanel').removeAll();
							if (Ext.getCmp('TEW_TreatmentFeedback_Document').getValue().length > 0)
							{
								var response_obj = Ext.util.JSON.decode(Ext.getCmp('TEW_TreatmentFeedback_Document').getValue());
								this.createFilesLinksFeedback(response_obj, treatment_id);
							}
							if(this.action == 'edit') {
								form.getForm().findField('TEW_Treatment_Reg').focus(100, true);
							} else {
								this.setFieldsDisabled(true);
								this.buttons[2].focus(100, true);
							}
							Ext.getCmp('TEW_Answer').setVisible(form.findById('TEW_TreatmentReview_id').getValue() == 2);
							Ext.getCmp('TEW_TreatmentMethodDispatch_fid').setAllowBlank(form.findById('TEW_TreatmentReview_id').getValue() != 2);
							Ext.getCmp('TEW_Treatment_SenderDetails').setDisabled(!!form.findById('TEW_Person_id').getValue());
							if (obj.Person_Fio) {
								form.findById('TEW_Person_id').setRawValue(obj.Person_Fio);
							}
							if (form.findById('TEW_TreatmentReview_id').getValue() == 2) {
								Ext.getCmp('TEW_TreatmentMethodDispatch_fid').disable();
								Ext.getCmp('TEW_TreatmentFeedback_Message').disable();
								Ext.getCmp('TEW_TreatmentFeedback_Note').disable();
								Ext.getCmp('TEW_TreatmentFeedback_FileUploadButton').disable();
							}
						}.createDelegate(this),
						url: '/?c=Treatment&m=getTreatment'
					});
					break;
			}
		}
	},
	/**
	 * swTEWDeleteFile() Функция для отправки запроса на удаление файла и удаления ссылки файла из поля прикрепленных документов
	 * 
	 * @copyright     Copyright (c) 2009-2010 Swan Ltd.
	 * @author        Permyakov Alexander <permjakov-am@mail.ru>
	 * @param         integer $index index файла в массиве данных прикрепленных файлов 
	 * @param         integer $Treatment_id идентификатор обращения 
	 * @return        void 
	 * @version       19.07.2010
	 * @todo          Надо будет сделать обновление данных о прикрепленных файлах на сервере по Treatment_id на случай того, что пользователь не нажмет кнопку сохранить или сделать контроль: при изменении данных формы просить сохранить изменения. 
	 */
	swTEWDeleteFile: function(index, Treatment_id) {
		if(this.action == 'view') {
			return false;
		}
		var files_str = Ext.getCmp('TEW_Treatment_Document').getValue();
		var files_obj = Ext.util.JSON.decode( files_str );
		sw.swMsg.show(
		{
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_fayl'] + files_obj[index].orig_name + '?',
			title: lang['vopros'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					var Mask = new Ext.LoadMask(this.getEl(), { msg: "Пожалуйста, подождите, идет обработка запроса на удаление файла " + files_obj[index].orig_name + " ..." });
					Mask.show();
					Ext.Ajax.request({
						callback: function(opt, success, resp) {
							Mask.hide();
							var response_obj = Ext.util.JSON.decode(resp.responseText);

							if ( response_obj.Error_Msg )
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
							if (!response_obj.data) 
								return false; //To-do нужна обработка ситуации, когда файл был загружен, но документ не был сохранен.

							// Обновление данных о прикрепленных файлах
							Ext.getCmp('TEW_Treatment_Document').setValue(response_obj.data);
							// Удаление ссылки на файл
							var files_panel = Ext.getCmp('TEW_FilesPanel');
							files_panel.remove( 'TEW_' + files_obj[index].file_name );
							files_panel.doLayout();
							this.doSave({isEditFile: true});
							//sw.swMsg.alert( this.title, 'Файл ' + files_obj[index].orig_name + ' удален.');
						}.createDelegate(this),
						params: {
							file: files_obj[index].file_name,
							data: files_str
						},
						url: '/?c=Treatment&m=deleteFile'
					});
				}
				else
				{
					return false;
				}
			}.createDelegate(this)
		});
	},
	createFilesLinks: function(files_data, treatment_id) {
		var files_panel = Ext.getCmp('TEW_FilesPanel');
		for(i in files_data)
		{
			if ( ! files_data[i].file_name ) continue;
			files_panel.add(new Ext.Panel({
				id: 'TEW_' + files_data[i].file_name,
				border : false,
				html   : '<a href="/uploads/' + files_data[i].file_name + '" target="_blank">' + files_data[i].orig_name + '</a> <span onclick="Ext.getCmp(\'TreatmentEditWindow\').swTEWDeleteFile(' + i + ', ' + treatment_id + ');" style="color: red; cursor: pointer; font-weight: bold; " title="удалить"> X </span> ' // + files_data[i].file_descr
			}));
			files_panel.doLayout();
		}
	},
	doSave: function(options) {
		if ( this.formStatus == 'save' || this.action == 'view' ) return false;
		this.formStatus = 'save';
		var base_form = this.findById('TreatmentEditForm').getForm();
		var params = new Object();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('TreatmentEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		//проверки
		var errMsg = "";
		
		if (Ext.getCmp('TEW_TreatmentReview_id').disabled) {
			params.TreatmentReview_id = Ext.getCmp('TEW_TreatmentReview_id').getValue();
		}
		if (Ext.getCmp('TEW_TreatmentMethodDispatch_fid').disabled) {
			params.TreatmentMethodDispatch_fid = Ext.getCmp('TEW_TreatmentMethodDispatch_fid').getValue();
		}
		if (Ext.getCmp('TEW_TreatmentFeedback_Message').disabled) {
			params.TreatmentFeedback_Message = Ext.getCmp('TEW_TreatmentFeedback_Message').getValue();
		}
		if (Ext.getCmp('TEW_TreatmentFeedback_Note').disabled) {
			params.TreatmentFeedback_Note = Ext.getCmp('TEW_TreatmentFeedback_Note').getValue();
		}

		if (errMsg == "") {

			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение обращения..." });
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
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this),
				params: params,
				success: function(result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();
					//Ext.getCmp('ETSIF_TreatmentGrid').ViewGridStore.reload();
					var response = new Object();
					if ( !action.result.id ) response.Treatment_id = 0;
					else response.Treatment_id = action.result.id;
					response.PMUser = base_form.findField('TEW_pmUser_login').getValue();
					response.Treatment_Reg = base_form.findField('TEW_Treatment_Reg').getValue();
					response.Treatment_DateReg = base_form.findField('TEW_Treatment_DateReg').getValue();
					response.TreatmentType = this.getDisplayField( 'TEW_TreatmentType_id', base_form.findField('TEW_TreatmentType_id').getValue() );
					response.TreatmentSenderType = this.getDisplayField( 'TEW_TreatmentSenderType_id', base_form.findField('TEW_TreatmentSenderType_id').getValue() );
					response.Treatment_SenderDetails = base_form.findField('TEW_Treatment_SenderDetails').getValue();
					response.TreatmentRecipientType = this.getDisplayField( 'TEW_TreatmentRecipientType_id', base_form.findField('TEW_TreatmentRecipientType_id').getValue() );
					// for searchgrid
					response.Treatment_DateReview = base_form.findField('TEW_Treatment_DateReview').getValue();
					response.TreatmentReview = this.getDisplayField( 'TEW_TreatmentReview_id', base_form.findField('TEW_TreatmentReview_id').getValue() );
					if(!options.isEditFile) {
						this.callback({ TEW_Data: response });
						this.hide();
						base_form.reset();
					}
				}.createDelegate(this)
			});
		} else {
			sw.swMsg.alert(lang['oshibka'], errMsg);
		}
	},
	createFilesLinksFeedback: function(files_data, treatment_id) {
		var files_panel = Ext.getCmp('TEW_TreatmentFeedback_FilesPanel');
		for(var i in files_data)
		{
			if ( ! files_data[i].file_name ) continue;
			files_panel.add(new Ext.Panel({
				id: 'TEW_TreatmentFeedback_' + files_data[i].file_name,
				border : false,
				html   : '<a href="/uploads/' + files_data[i].file_name + '" target="_blank">' + files_data[i].orig_name + '</a> <span onclick="Ext.getCmp(\'swTreatmentEditWindow\').swDeleteFileFeedback(' + i + ', ' + treatment_id + ');" style="color: red; cursor: pointer; font-weight: bold; " title="удалить"> X </span> ' // + files_data[i].file_descr
			}));
			files_panel.doLayout();
		}
	},
	swDeleteFileFeedback: function(index, Treatment_id) {
		if(this.action == 'view') {
			return false;
		}
		var files_str = Ext.getCmp('TEW_TreatmentFeedback_Document').getValue();
		var files_obj = Ext.util.JSON.decode( files_str );
		sw.swMsg.show(
			{
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Вы действительно хотите удалить файл ') + files_obj[index].orig_name + '?',
				title: langs('Вопрос'),
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						var Mask = new Ext.LoadMask(this.getEl(), { msg: "Пожалуйста, подождите, идет обработка запроса на удаление файла " + files_obj[index].orig_name + " ..." });
						Mask.show();
						Ext.Ajax.request({
							callback: function(opt, success, resp) {
								Mask.hide();
								var response_obj = Ext.util.JSON.decode(resp.responseText);

								if ( response_obj.Error_Msg )
									sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
								if (!response_obj.data)
									return false; //To-do нужна обработка ситуации, когда файл был загружен, но документ не был сохранен.

								// Обновление данных о прикрепленных файлах
								Ext.getCmp('TEW_TreatmentFeedback_Document').setValue(response_obj.data);
								// Удаление ссылки на файл
								var files_panel = Ext.getCmp('TEW_TreatmentFeedback_FilesPanel');
								files_panel.remove( 'TreatmentFeedback_' + files_obj[index].file_name );
								files_panel.doLayout();
								this.doSave({isEditFile: true});
								//sw.swMsg.alert( this.title, 'Файл ' + files_obj[index].orig_name + ' удален.');
							}.createDelegate(this),
							params: {
								file: files_obj[index].file_name,
								data: files_str
							},
							url: '/?c=Treatment&m=deleteFile'
						});
					}
					else
					{
						return false;
					}
				}.createDelegate(this)
			});
	}
});