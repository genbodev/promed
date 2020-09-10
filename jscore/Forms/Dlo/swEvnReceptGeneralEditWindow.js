/**
* swEvnReceptGeneralEditWindow - окно редактирования рецепта.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.002-29.03.2010
* @comment      Префикс для id компонентов ERGEF (EvnReceptGeneralEditForm)
*
*
* @input data: action - действие (add, view)
*              EvnReceptGeneral_id - ID рецепта
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*
*              Потоковый ввод:
*                  EvnReceptGeneral_setDate - дата выписки рецепта
*                  LpuSection_id - отделение
*                  MedPersonal_id - врач
*                  ReceptType_id - тип рецепта
*
*              ТАП -> Посещение:
*                  Diag_id - диагноз
*                  EvnVizitPL_id - ID посещения
*                  EvnVizitPL_setDate - дата посещения
*                  LpuSection_id - отделение (не редактируемое)
*                  MedPersonal_id - врач (не редактируемое)
*                  ??? Список доступных диагнозов
*/
/*NO PARSE JSON*/

sw.Promed.swEvnReceptGeneralEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnReceptGeneralEditWindow',
	objectSrc: '/jscore/Forms/Dlo/swEvnReceptGeneralEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doSave: function(options) {
				var win = this;
		
		if ( !options || typeof options != 'object' ) {
			return false;
		}

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.findById('EvnReceptGeneralEditForm').getForm();
		var form = this.findById('EvnReceptGeneralEditForm');
		var person_information = this.findById('ERGEF_PersonInformationFrame');
		var post = new Object();
		var record;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var person_age = swGetPersonAge(person_information.getFieldValue('Person_Birthday'), base_form.findField('EvnReceptGeneral_setDate').getValue());

		if ( person_age == -1 ) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Проверьте правильность ввода даты выписки рецепта и даты рождения пациента. Возможно, дата рождения пациента больше даты выписки рецепта.');
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Проверка возможности сохранения рецепта..." });

		//для задачи https://redmine.swan-it.ru/issues/167780, комментарий 11. Если на запрос '/?c=EvnRecept&m=getReceptGeneralAddDetails'
		//этой формы ответ сервера задерживается, поля зависящие от него, в т.ч. MedPersonal_id, остаются пустыми. При сохранении, значение NULL, переданное из MedPersonal_id
		//в "хранимку", вызывает ошибку - недопустимое значение.
		if (Ext.isEmpty(base_form.findField('MedPersonal_id').getValue())){
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Сохранение невозможно, т.к. отсутствует ответ сервера, попробуйте позже');
			return false;
		}

		post.EvnCourseTreatDrug_id = this.EvnCourseTreatDrug_id;
		post.EvnReceptGeneral_pid = base_form.findField('EvnReceptGeneral_pid').getValue();
		post.ReceptForm_id = base_form.findField('ReceptForm_id').getValue();
		post.ReceptType_id = base_form.findField('ReceptType_id').getValue();
		post.EvnReceptGeneral_Ser = base_form.findField('EvnReceptGeneral_Ser').getValue();
		post.EvnReceptGeneral_Num = base_form.findField('EvnReceptGeneral_Num').getValue();


		var EvnReceptGeneral_setDate = base_form.findField('EvnReceptGeneral_setDate').getValue();
		
		var dd = EvnReceptGeneral_setDate.getDate();
		if(dd<10)
			dd = '0'+dd;
		var mm = EvnReceptGeneral_setDate.getMonth()+1;
		if(mm<10)
			mm = '0'+mm;
		var yyyy = EvnReceptGeneral_setDate.getFullYear();
		post.EvnReceptGeneral_setDate =	dd+'.'+mm+'.'+yyyy;
		
		if(!Ext.isEmpty(base_form.findField('EvnReceptGeneral_endDate').getValue()))
		{
			var EvnReceptGeneral_endDate = base_form.findField('EvnReceptGeneral_endDate').getValue();
			dd = EvnReceptGeneral_endDate.getDate();
			if(dd<10)
				dd = '0'+dd;
			mm = EvnReceptGeneral_endDate.getMonth()+1;
			if(mm<10)
				mm = '0'+mm;
			yyyy = EvnReceptGeneral_endDate.getFullYear();

			post.EvnReceptGeneral_endDate = dd+'.'+mm+'.'+yyyy;//base_form.findField('EvnReceptGeneral_endDate').getValue();
		}
		else
			post.EvnReceptGeneral_endDate = null;
		post.EvnReceptGeneral_IsChronicDisease = (base_form.findField('EvnReceptGeneral_IsChronicDisease').getValue())?'on':'';
		post.EvnReceptGeneral_IsSpecNaz = (base_form.findField('EvnReceptGeneral_IsSpecNaz').getValue())?'on':'';
		post.EvnReceptGeneral_IsExcessDose = (base_form.findField('EvnReceptGeneral_IsExcessDose').getValue())?'on':'';
		post.PrescrSpecCause_id = base_form.findField('PrescrSpecCause_id').getValue();
		post.ReceptUrgency_id = base_form.findField('ReceptUrgency_id').getValue();
		post.ReceptValid_id = base_form.findField('ReceptValid_id').getValue();
		post.EvnReceptGeneral_Validity = base_form.findField('EvnReceptGeneral_Validity').getValue();
		post.EvnReceptGeneral_Period = base_form.findField('EvnReceptGeneral_Period').getValue();

		post.Diag_id = base_form.findField('Diag_id').getValue();
		post.Drug_Fas0 = base_form.findField('Drug_Fas0').getValue();
		post.Drug_Fas1 = base_form.findField('Drug_Fas1').getValue();
		post.Drug_Fas2 = base_form.findField('Drug_Fas2').getValue();
		post.Drug_Kolvo_Pack0 = base_form.findField('Drug_Kolvo_Pack0').getValue();
		post.Drug_Kolvo_Pack1 = base_form.findField('Drug_Kolvo_Pack1').getValue();
		post.Drug_Kolvo_Pack2 = base_form.findField('Drug_Kolvo_Pack2').getValue();
		post.Drug_Signa0 = base_form.findField('Drug_Signa0').getValue();
		post.Drug_Signa1 = base_form.findField('Drug_Signa1').getValue();
		post.Drug_Signa2 = base_form.findField('Drug_Signa2').getValue();
		
		this.doSubmit({
			copy: options.copy,
			postData: post,
			print: options.print
		});
		
	},
	doSubmit: function(options) {
		if ( !options || typeof options != 'object' ) {
			this.formStatus = 'edit';
			return false;
		}

		var base_form = this.findById('EvnReceptGeneralEditForm').getForm();
		var person_information = this.findById('ERGEF_PersonInformationFrame');
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
					}
				}
			}.createDelegate(this),
			params: options.postData,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				//loadMask.hide();

				if ( action.result ) {
					if(options.print)
					{
						wnd.action = 'view';
						base_form.findField('EvnReceptGeneral_id').setValue(action.result.EvnReceptGeneral_id);
						wnd.printRecept();
						/*if (getRegionNick() == 'kz') {
							printBirt({
								'Report_FileName': 'EvnReceptMoney_print.rptdesign',
								'Report_Params': '&paramEvnRecept=' + action.result.EvnReceptGeneral_id,
								'Report_Format': 'pdf'
							});
							printBirt({
								'Report_FileName': 'EvnReceptMoney_Oborot_print.rptdesign',
								'Report_Params': '&paramEvnRecept=' + action.result.EvnReceptGeneral_id,
								'Report_Format': 'pdf'
							});
						} else {
							if (options.postData.ReceptForm_id == 3) {
								printBirt({
									'Report_FileName': 'EvnReceptGenprint2.rptdesign',
									'Report_Params': '&paramEvnRecept=' + action.result.EvnReceptGeneral_id,
									'Report_Format': 'pdf'
								});
							} else {
								printBirt({
									'Report_FileName': 'EvnReceptGenprint.rptdesign',
									'Report_Params': '&paramEvnRecept=' + action.result.EvnReceptGeneral_id,
									'Report_Format': 'pdf'
								});
							}
						}*/
						
					}
					wnd.callback(base_form.findField('EvnReceptGeneral_pid').getValue());
					wnd.hide();
				}
				else {
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	formStatus: 'edit',
	height: 500,
	id: 'EvnReceptGeneralEditWindow',
	initComponent: function() {
		var wnd = this;
		this.formFirstShow = true;
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({
						checkDrugRequest: true,
						checkPersonAge: true,
						checkPersonSnils: true,
						copy: false,
						print: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				testId: 'ERGEF_btn_Save',
				tabIndex: TABINDEX_ERGEF + 25,
				text: BTN_FRMSAVE//,
				//tooltip: 'Сохранить введенные данные'
			},
            {
				handler: function() {
					this.printRecept();
				}.createDelegate(this),
				iconCls: 'print16',
				testId: 'ERGEF_btn_Print',
				tabIndex: TABINDEX_ERGEF + 28,
				text: '<u>П</u>ечать'//,
				//tooltip: 'Напечатать рецепт'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[2].focus();
				}.createDelegate(this),
				onTabAction: function () {
					if ( !this.findById('EvnReceptGeneralEditForm').getForm().findField('ReceptType_id').disabled ) {
						this.findById('EvnReceptGeneralEditForm').getForm().findField('ReceptType_id').focus(true);
					}
					else if ( !this.findById('EvnReceptGeneralEditForm').getForm().findField('EvnReceptGeneral_setDate').disabled ) {
						this.findById('EvnReceptGeneralEditForm').getForm().findField('EvnReceptGeneral_setDate').focus(true);
					}
					else if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_ERGEF + 29,
				text: BTN_FRMCANCEL//,
				//tooltip: 'Закрыть окно'
			}],
			items: [ new sw.Promed.PersonInformationPanel({
				/*button2Callback: function(callback_data) {
					var base_form = this.findById('EvnReceptGeneralEditForm').getForm();

					base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
					base_form.findField('Server_id').setValue(callback_data.Server_id);

					this.findById('ERGEF_PersonInformationFrame').load({
						Person_id: callback_data.Person_id,
						Server_id: callback_data.Server_id
					});
				}.createDelegate(this),
				button2OnHide: function() {
					var base_form = this.findById('EvnReceptGeneralEditForm').getForm();

					if ( !base_form.findField('ReceptType_id').disabled ) {
						base_form.findField('ReceptType_id').focus(false);
					}
					else if ( !base_form.findField('EvnReceptGeneral_setDate').disabled ) {
						base_form.findField('EvnReceptGeneral_setDate').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				button3OnHide: function() {
					var base_form = this.findById('EvnReceptGeneralEditForm').getForm();

					if ( !base_form.findField('ReceptType_id').disabled ) {
						base_form.findField('ReceptType_id').focus(false);
					}
					else if ( !base_form.findField('EvnReceptGeneral_setDate').disabled ) {
						base_form.findField('EvnReceptGeneral_setDate').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				button4OnHide: function() {
					var base_form = this.findById('EvnReceptGeneralEditForm').getForm();
					var person_id = base_form.findField('Person_id').getValue();
				}.createDelegate(this),*/
				forReceptCommonAstra: (getRegionNick()=='astra'),
				height: (getRegionNick()=='astra')?60:130,
				id: 'ERGEF_PersonInformationFrame',
				region: 'north'
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyStyle: 'padding: 0.5em;',
				border: false,
				frame: false,
				id: 'EvnReceptGeneralEditForm',
				strongDrug: false,
				items: [{
					name: 'EvnReceptGeneral_id',
					value: 0,
					xtype: 'hidden'
				}, 
				{
					name: 'EvnReceptGeneral_pid',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Lpu_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_id',
					value: 0,
					xtype: 'hidden'
				},{
					name: 'LpuSection_id',
					value: 0,
					xtype: 'hidden'
				},{
					name: 'PersonEvn_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'Drug_Fas_0',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'Drug_Fas_1',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'Drug_Fas_2',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'ReceptDelayType_Code',
					value: null,
					xtype: 'hidden'
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'ERGEF_ReceptPanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: '1. Рецепт',
					fieldWidth: 300,
					items: [
						{
							allowBlank: false,
							fieldLabel: 'Дата рецепта',
							format: 'd.m.Y',
							name: 'EvnReceptGeneral_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_ERGEF + 2,
							validateOnBlur: true,
							xtype: 'swdatefield'
						},
						{
                            allowBlank: false,
                            codeField: 'ReceptForm_Code',
                            displayField: 'ReceptForm_Name',
                            editable: false,
                            fieldLabel: 'Форма рецепта',
                            hiddenName: 'ReceptForm_id',
                            lastQuery: '',
                            listeners: {
                                'change': function(combo, newValue, oldValue) {
                                	var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
                                	var recept_type_combo = base_form.findField('ReceptType_id');
									var ReceptValid_id = base_form.findField('ReceptValid_id').getValue();
                                	base_form.findField('ReceptValid_id').clearValue();
									base_form.findField('EvnReceptGeneral_IsExcessDose').hideContainer();
									base_form.findField('PrescrSpecCause_id').getStore().filterBy(function(rec) {
										return (rec.get('PrescrSpecCause_Code').toString().inlist(['1','3']));
									});

									wnd.setReceptTypeFilter();


									if (newValue && newValue.toString().inlist(['3', '5', '8'])) {
										base_form.findField('EvnReceptGeneral_hasVK').showContainer();
										base_form.findField('EvnReceptGeneral_hasVK').setAllowBlank(false);
									} else {
										base_form.findField('EvnReceptGeneral_hasVK').hideContainer();
										base_form.findField('EvnReceptGeneral_hasVK').setAllowBlank(true);
										base_form.findField('EvnReceptGeneral_hasVK').setValue(1);
										base_form.findField('EvnReceptGeneral_hasVK').fireEvent('change', base_form.findField('EvnReceptGeneral_hasVK'), base_form.findField('EvnReceptGeneral_hasVK').getValue());
									}

									if(newValue == 5) //148-1/у-88
									{
										base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
											return (rec.get('ReceptValid_Code').toString().inlist(['11']));
										});
										base_form.findField('ReceptValid_id').setAllowBlank(false);
										base_form.findField('EvnReceptGeneral_IsChronicDisease').setValue(0);
										base_form.findField('EvnReceptGeneral_IsChronicDisease').hideContainer();
										base_form.findField('EvnReceptGeneral_IsSpecNaz').showContainer();

										if (base_form.strongDrug) {
											base_form.findField('EvnReceptGeneral_IsExcessDose').showContainer();
										}
									}
									if(newValue == 3) //107-1
									{
										base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
											return (rec.get('ReceptValid_Code').toString().inlist(['8','12']));
										});
										base_form.findField('ReceptValid_id').setAllowBlank(true);
										base_form.findField('EvnReceptGeneral_IsChronicDisease').showContainer();
										base_form.findField('EvnReceptGeneral_IsSpecNaz').showContainer();
										base_form.findField('PrescrSpecCause_id').getStore().filterBy(function(rec) {
											return (rec.get('PrescrSpecCause_Code').toString().inlist(['1','2','3']));
										});
									}
									if(newValue == 2) //1-ми
									{
										base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
											return (rec.get('ReceptValid_Code').toString().inlist(['1','2','5','12']));
										});
										base_form.findField('ReceptValid_id').setAllowBlank(false);
										base_form.findField('EvnReceptGeneral_IsChronicDisease').showContainer();
										base_form.findField('EvnReceptGeneral_IsSpecNaz').setValue(0);
										base_form.findField('EvnReceptGeneral_IsSpecNaz').hideContainer();
									}
									if(newValue == 8) //107/у-НП
									{
										recept_type_combo.setValue(1);
										base_form.findField('ReceptUrgency_id').hideContainer();
										base_form.findField('EvnReceptGeneral_IsSpecNaz').setValue(0);
										base_form.findField('EvnReceptGeneral_IsSpecNaz').hideContainer();
										base_form.findField('ReceptValid_id').setAllowBlank(false);
										base_form.findField('ReceptValid_id').getStore().filterBy(function(rec) {
											return (rec.get('ReceptValid_Code').toString().inlist(['11']));
										});
										if(getRegionNick() != 'kz'){
											recept_type_combo.disable();
										}
										recept_type_combo.fireEvent('change', recept_type_combo, 1);
									}else{
										base_form.findField('ReceptUrgency_id').showContainer();
										if (wnd.action == 'add') {
										recept_type_combo.enable();
									}
									}
									if(newValue && !combo.disabled) {
                                        wnd.setReceptNumber();
                                    }
									// при редактировании заблокировано поле Форма рецепта, т.е. меняться не должно,
									// поэтому срок рецепта не очищаем. Влияет на состояение чекбокса "По специальному назначению"
                                    if(this.action == 'edit'){
										base_form.findField('ReceptValid_id').setValue(ReceptValid_id);
									}
                                    base_form.findField('ReceptValid_id').fireEvent('change',
										base_form.findField('ReceptValid_id'),base_form.findField('ReceptValid_id').getValue());
									base_form.findField('EvnReceptGeneral_IsSpecNaz').fireEvent('check',
										base_form.findField('EvnReceptGeneral_IsSpecNaz'),base_form.findField('EvnReceptGeneral_IsSpecNaz').getValue());
                                }.createDelegate(this)
                            },
                            store: new Ext.data.Store({
                                autoLoad: true,
                                reader: new Ext.data.JsonReader({
                                    id: 'ReceptForm_id'
                                }, [
                                    { name: 'ReceptForm_id', mapping: 'ReceptForm_id', type: 'int', hidden: 'true'},
                                    { name: 'ReceptForm_Code', mapping: 'ReceptForm_Code'},
                                    { name: 'ReceptForm_Name', mapping: 'ReceptForm_Name' }
                                ]),
                                url: C_RECEPTGENFORM_GET_LIST
                            }),

                            tpl: new Ext.XTemplate(
                                '<tpl for="."><div class="x-combo-list-item">',
                                '<table style="border: 0;"><tr><td style="width: 25px;"><font color="red">{ReceptForm_Code}</font></td><td style="font-weight: normal;">{ReceptForm_Name}</td></tr></table>',
                                '</div></tpl>'
                            ),
                            validateOnBlur: true,
                            valueField: 'ReceptForm_id',
                            width: 517,
                            xtype: 'swbaselocalcombo'
                        },
                        {
						allowBlank: false,
						fieldLabel: 'Тип рецепта',
						hiddenName: 'ReceptType_id',
						value: 2,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
								var buttonPrint = wnd.buttons[1];
								if(newValue == 2) //На листе
								{
									base_form.findField('EvnReceptGeneral_Ser').disable();
									base_form.findField('EvnReceptGeneral_Num').disable();
									Ext.getCmp('swUpdateSerNum').show();
									if(buttonPrint) buttonPrint.show();
								}
								else
								{
									base_form.findField('EvnReceptGeneral_Ser').enable();
									base_form.findField('EvnReceptGeneral_Num').enable();
									Ext.getCmp('swUpdateSerNum').hide();
									if(buttonPrint) buttonPrint.hide();
								}

								if (Ext.isEmpty(oldValue) || newValue == 3) {
									wnd.setReceptNumber();
								}
							}.createDelegate(this),
							'keydown': function (inp, e) {
								if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_ERGEF + 1,
						validateOnBlur: true,
						xtype: 'swrecepttypecombo'
					},
					{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								allowDecimals: false,
								allowNegative: false,
								autoCreate: {
									tag: 'input',
									type: 'text',
									maxLength: '7'
								},
								fieldLabel: 'Серия',
								name: 'EvnReceptGeneral_Ser',
								tabIndex: TABINDEX_ERGEF + 3,
								validateOnBlur: true,
								//xtype: 'numberfield',
								xtype: 'textfield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								allowBlank: false,
								allowDecimals: false,
								allowNegative: false,
								autoCreate: {
									maxLength: 8,
									tag: 'input',
									type: 'text'
								},
								fieldLabel: 'Номер',
								maskRe: /\d/,
								name: 'EvnReceptGeneral_Num',
								tabIndex: TABINDEX_ERGEF + 4,
								validateOnBlur: true,
								xtype: 'textfield'
							}]
						},
						{
							border: false,
							layout: 'form',
							items:[{
								style: "padding-left: 20px;",
								xtype: 'button',
								id: 'swUpdateSerNum',
								text: '',
								iconCls: 'add16',
								handler: function() {
									wnd.setReceptNumber('update');
								}
							}]
						}
						]
					}, 

					{
						boxLabel: 'Пациенту с хроническими заболеваниями',
						checked: false,
                        fieldLabel: '',
                        labelSeparator: '',
						name: 'EvnReceptGeneral_IsChronicDisease',
						tabIndex: TABINDEX_ERGEF + 4,
						xtype: 'checkbox'
					},

					{
						boxLabel: 'По специальному назначению',
						checked: false,
                        fieldLabel: '',
                        labelSeparator: '',
						name: 'EvnReceptGeneral_IsSpecNaz',
						tabIndex: TABINDEX_ERGEF + 4,
						xtype: 'checkbox',
						listeners: {
							'check' :function(field,checked)
							{
								var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
								if(checked)
								{
									base_form.findField('PrescrSpecCause_id').setAllowBlank(false);
									base_form.findField('PrescrSpecCause_id').showContainer();
								}
								else
								{
									base_form.findField('PrescrSpecCause_id').setAllowBlank(true);
									base_form.findField('PrescrSpecCause_id').hideContainer();
									base_form.findField('PrescrSpecCause_id').setValue(null);
								}
								base_form.findField('ReceptValid_id').fireEvent('change',
									base_form.findField('ReceptValid_id'),base_form.findField('ReceptValid_id').getValue());
							}.createDelegate(this)
						}
					},

					{
						comboSubject: 'PrescrSpecCause',
						fieldLabel: langs('Причина специального назначения'),
						hiddenName: 'PrescrSpecCause_id',
						lastQuery: '',
						onLoadStore: Ext.emptyFn,
						width: 517,
						tabIndex: TABINDEX_ESTEF + 4,
						listeners: {
							'select': function() {
								var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
								base_form.findField('ReceptValid_id').fireEvent('change',
									base_form.findField('ReceptValid_id'),base_form.findField('ReceptValid_id').getValue());
							}
						},
						xtype: 'swcommonsprcombo'
					},

					{
                        allowBlank: true,
                        codeField: 'ReceptUrgency_Code',
                        displayField: 'ReceptUrgency_Name',
                        editable: false,
                        fieldLabel: 'Срочность',
                        hiddenName: 'ReceptUrgency_id',
                        lastQuery: '',
                        listeners: {
                            'change': function(combo, newValue, oldValue) {

                            	//alert('11');
								log(combo);
								log(newValue);
								//alert('22');

                            }.createDelegate(this)
                        },
                        store: new Ext.data.Store({
                            autoLoad: true,
                            reader: new Ext.data.JsonReader({
                                id: 'ReceptUrgency_id'
                            }, [
                                { name: 'ReceptUrgency_id', mapping: 'ReceptUrgency_id', type: 'int', hidden: 'true'},
                                { name: 'ReceptUrgency_Code', mapping: 'ReceptUrgency_Code'},
                                { name: 'ReceptUrgency_Name', mapping: 'ReceptUrgency_Name' }
                            ]),
                            url: C_RECEPTURG_GET_LIST
                        }),

                        tpl: new Ext.XTemplate(
                            '<tpl for="."><div class="x-combo-list-item">',
                            '<table style="border: 0;"><tr><td style="width: 25px;"><font color="red">{ReceptUrgency_Code}</font></td><td style="font-weight: normal;">{ReceptUrgency_Name}</td></tr></table>',
                            '</div></tpl>'
                        ),
                        validateOnBlur: true,
                        valueField: 'ReceptUrgency_id',
                        width: 164,
                        xtype: 'swbaselocalcombo'
                    },


					{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								autoLoad: false,
								comboSubject: 'ReceptValid',
								fieldLabel: 'Срок действия',
								hiddenName: 'ReceptValid_id',
								width: 164,
								lastQuery: '',
								listeners: {
									'render': function(combo) {
										combo.getStore().load();
									}.createDelegate(this),
									'change': function(combo,value)
									{
										var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
										if(Ext.isEmpty(value) && base_form.findField('PrescrSpecCause_id').getValue()=='2')
										{
											base_form.findField('EvnReceptGeneral_Validity').setAllowBlank(false);
											base_form.findField('EvnReceptGeneral_endDate').setAllowBlank(false);
											base_form.findField('EvnReceptGeneral_Period').setAllowBlank(false);
											base_form.findField('EvnReceptGeneral_Validity').showContainer();
											base_form.findField('EvnReceptGeneral_endDate').showContainer();
											base_form.findField('EvnReceptGeneral_Period').showContainer();
										}
										else
										{
											base_form.findField('EvnReceptGeneral_Validity').setAllowBlank(true);
											base_form.findField('EvnReceptGeneral_endDate').setAllowBlank(true);
											base_form.findField('EvnReceptGeneral_Period').setAllowBlank(true);
											base_form.findField('EvnReceptGeneral_Validity').hideContainer();
											base_form.findField('EvnReceptGeneral_endDate').hideContainer();
											base_form.findField('EvnReceptGeneral_Period').hideContainer();

											base_form.findField('EvnReceptGeneral_Validity').setValue('');
											base_form.findField('EvnReceptGeneral_endDate').setValue('');
											base_form.findField('EvnReceptGeneral_Period').setValue('');
										}
										if (getRegionNick() != 'kz'){
											if (!Ext.isEmpty(value)){
												var ReceptValid_Code = combo.getStore().getById(value).get('ReceptValid_Code');
											}
											var ReceptForm_id = base_form.findField('ReceptForm_id').getValue();
											if (!Ext.isEmpty(ReceptValid_Code) && ReceptValid_Code == 12 && !Ext.isEmpty(ReceptForm_id) && ReceptForm_id.inlist(['3', '5'])){
												base_form.findField('EvnReceptGeneral_IsSpecNaz').setValue(1);
												base_form.findField('EvnReceptGeneral_IsSpecNaz').disable();
												wnd.MayClearIsSpecNaz = true;
											} else if (wnd.MayClearIsSpecNaz) {
												base_form.findField('EvnReceptGeneral_IsSpecNaz').setValue(0);
												base_form.findField('EvnReceptGeneral_IsSpecNaz').enable();
												wnd.MayClearIsSpecNaz = false;		//запретить выполнение двух строчек выше, если срок рецепта был не "до 1 года"
											}
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_ERGEF + 5,
								validateOnBlur: true,
								xtype: 'swcommonsprcombo'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 220,
							items: [{
								allowBlank: true,
								fieldLabel: 'Срок действия, указанный врачом',
								name: 'EvnReceptGeneral_Validity',
								tabIndex: TABINDEX_ERGEF + 5,
								xtype: 'textfield',
								width: 128
							}]
						}]
					}, 
					{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 201,
							items: [{
								allowBlank: true,
								fieldLabel: 'Рецепт действителен до',
								format: 'd.m.Y',
								name: 'EvnReceptGeneral_endDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_ERGEF + 2,
								validateOnBlur: true,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth:162,
							items: [{
								allowBlank: true,
								fieldLabel: 'Периодичность отпуска',
								name: 'EvnReceptGeneral_Period',
								tabIndex: TABINDEX_ERGEF + 5,
								xtype: 'textfield',
								width: 185
							}]
						}]
					}, 


					{
						allowBlank: true,
						disabled: true,
						fieldLabel: 'МО',
						id: 'ERGEF_Lpu',
						name: 'Lpu_Name',
						tabIndex: TABINDEX_ERGEF + 6,
						width: 517,
						xtype: 'textfield'
					},
					{
						allowBlank: true,
						disabled: true,
						fieldLabel: 'Отделение',
						id: 'ERGEF_LpuSection',
						name: 'LpuSection_Name',
						tabIndex: TABINDEX_ERGEF + 6,
						width: 517,
						xtype: 'textfield'
					},
					{
						allowBlank: true,
						disabled: true,
						fieldLabel: 'Врач',
						id: 'ERGEF_MedPersonal',
						name: 'MedPersonal_Name',
						tabIndex: TABINDEX_ERGEF + 7,
						width: 517,
						xtype: 'textfield'
					},
					{
						checkAccessRights: true,
						allowBlank: false,
						fieldLabel: 'Диагноз',
						disabled: true,
						hiddenName: 'Diag_id',
						listWidth: 600,
						tabIndex: TABINDEX_ERGEF + 8,
						validateOnBlur: true,
						width: 517,
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: 'Выдан уполномоченному лицу',
						name: 'EvnReceptGeneral_IsDelivery',
						hiddenName: 'EvnReceptGeneral_IsDelivery',
						xtype: 'checkbox'
					}, {
						fieldLabel: 'Протокол ВК',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
								base_form.findField('EvnReceptGeneral_VKProtocolNum').setContainerVisible(newValue == 2);
								base_form.findField('EvnReceptGeneral_VKProtocolNum').setAllowBlank(newValue != 2);
								base_form.findField('EvnReceptGeneral_VKProtocolDT').setContainerVisible(newValue == 2);
								base_form.findField('EvnReceptGeneral_VKProtocolDT').setAllowBlank(newValue != 2);
								base_form.findField('CauseVK_id').setContainerVisible(newValue == 2);
								base_form.findField('CauseVK_id').setAllowBlank(newValue != 2);
							}
						},
						hiddenName: 'EvnReceptGeneral_hasVK',
						value: 1,
						width: 80,
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: 'Номер протокола ВК',
						name: 'EvnReceptGeneral_VKProtocolNum',
						width: 517,
						xtype: 'textfield'
					}, {
						fieldLabel: 'Дата протокола ВК',
						format: 'd.m.Y',
						name: 'EvnReceptGeneral_VKProtocolDT',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						xtype: 'swdatefield'
					}, {
						comboSubject: 'CauseVK',
						fieldLabel: 'Основание для проведения ВК',
						hiddenName: 'CauseVK_id',
						width: 517,
						listeners: {
							expand: function (combo) {
								combo.getStore().filterBy(function(record, id) {
									if (typeof record.get == 'function') {
										return (record.get('CauseVK_Code') != '2');
									} else if (rec.CauseVK_Code) {
										return (record.CauseVK_Code != '2');
									} else {
										return true;
									}
								});
							}
						},
						xtype: 'swcommonsprcombo'
					}]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'ERGEF_DrugPanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: 'Медикамент',
					items: [ 
						{
							name: 'EvnReceptGeneralDrugLink_id0',
							value: 0,
							xtype: 'hidden'
						},
						{
	                        name: 'Drug_Name0',
	                        id: 'ERGEF_Drug_Name0',
	                        disabled: true,
	                        fieldLabel: 'Наименование',
	                        width: 517,
	                        tabIndex: TABINDEX_EVNPRESCR + 122,
							xtype: 'textfield'
              			},
						{
							border: false,
							layout: 'column',
							items: [
							{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									allowNegative: false,
									fieldLabel: 'Кол-во (уп.)',
									minValue: 0.01,
									name: 'Drug_Kolvo_Pack0',
									tabIndex: TABINDEX_ERGEF + 23,
									validateOnBlur: true,
									listeners: {
										'change': function (cmp,value)
										{
											var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
											if(!Ext.isEmpty(value))
											{
												base_form.findField('Drug_Fas0').setValue(base_form.findField('Drug_Fas_0').getValue() * value);
											}
											else
												base_form.findField('Drug_Fas0').setValue('');
										}
									},
									xtype: 'numberfield'
								}]
							}, 
							{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									allowNegative: false,
									disabled: true,
									fieldLabel: 'Кол-во (доз.)',
									minValue: 0.01,
									name: 'Drug_Fas0',
									tabIndex: TABINDEX_ERGEF + 23,
									validateOnBlur: true,
									//value: 1,
									xtype: 'numberfield'
								}]
							}
							]
						},
						{
							allowBlank: true,
							fieldLabel: 'Signa',
							name: 'Drug_Signa0',
							tabIndex: TABINDEX_ERGEF + 24,
							validateOnBlur: true,
							width: 517,
							xtype: 'textfield',
							bodyStyle: 'margin-top: 50px;'
						},
						{
							boxLabel: 'Превышение дозировки',
							checked: false,
							fieldLabel: '',
							labelSeparator: '',
							name: 'EvnReceptGeneral_IsExcessDose',
							tabIndex: TABINDEX_ERGEF + 24,
							xtype: 'checkbox'
						},
						{
							name: 'EvnReceptGeneralDrugLink_id1',
							value: 0,
							xtype: 'hidden'
						},
						{
							border: false,
							layout: 'form',
							style: 'margin-top: 30px',
							items: [{
								name: 'Drug_Name1',
		                        id: 'ERGEF_Drug_Name1',
		                        disabled: true,
		                        fieldLabel: 'Наименование',
		                        width: 517,
		                        tabIndex: TABINDEX_EVNPRESCR + 122,
								xtype: 'textfield'
							}]
						},
						{
							border: false,
							layout: 'column',
							items: [
							{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									allowNegative: false,
									fieldLabel: 'Кол-во (уп.)',
									minValue: 0.01,
									name: 'Drug_Kolvo_Pack1',
									tabIndex: TABINDEX_ERGEF + 23,
									validateOnBlur: true,
									listeners: {
										'change': function (cmp,value)
										{
											var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
											if(!Ext.isEmpty(value))
											{
												base_form.findField('Drug_Fas1').setValue(base_form.findField('Drug_Fas_1').getValue() * value);
											}
											else
												base_form.findField('Drug_Fas1').setValue('');
										}
									},
									xtype: 'numberfield'
								}]
							}, 
							{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									allowNegative: false,
									disabled: true,
									fieldLabel: 'Кол-во (доз.)',
									minValue: 0.01,
									name: 'Drug_Fas1',
									tabIndex: TABINDEX_ERGEF + 23,
									validateOnBlur: true,
									//value: 1,
									xtype: 'numberfield'
								}]
							}
							]
						},
						{
							allowBlank: true,
							fieldLabel: 'Signa',
							name: 'Drug_Signa1',
							tabIndex: TABINDEX_ERGEF + 24,
							validateOnBlur: true,
							width: 517,
							xtype: 'textfield',
							bodyStyle: 'margin-top: 50px;'
						},
						{
							name: 'EvnReceptGeneralDrugLink_id2',
							value: 0,
							xtype: 'hidden'
						},
						{
							border: false,
							layout: 'form',
							style: 'margin-top: 30px',
							items: [{
								name: 'Drug_Name2',
		                        id: 'ERGEF_Drug_Name2',
		                        disabled: true,
		                        fieldLabel: 'Наименование',
		                       	width: 517,
		                        tabIndex: TABINDEX_EVNPRESCR + 122,
								xtype: 'textfield'
							}]
						},
						{
							border: false,
							layout: 'column',
							items: [
							{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									allowNegative: false,
									fieldLabel: 'Кол-во (уп.)',
									minValue: 0.01,
									name: 'Drug_Kolvo_Pack2',
									tabIndex: TABINDEX_ERGEF + 23,
									validateOnBlur: true,
									listeners: {
										'change': function (cmp,value)
										{
											var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
											if(!Ext.isEmpty(value))
											{
												base_form.findField('Drug_Fas2').setValue(base_form.findField('Drug_Fas_2').getValue() * value);
											}
											else
												base_form.findField('Drug_Fas2').setValue('');
										}
									},
									xtype: 'numberfield'
								}]
							}, 
							{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									allowNegative: false,
									disabled: true,
									fieldLabel: 'Кол-во (доз.)',
									name: 'Drug_Fas2',
									tabIndex: TABINDEX_ERGEF + 23,
									validateOnBlur: true,
									//value: 1,
									xtype: 'numberfield'
								}]
							}
							]
						},
						{
							allowBlank: true,
							fieldLabel: 'Signa',
							name: 'Drug_Signa2',
							tabIndex: TABINDEX_ERGEF + 24,
							validateOnBlur: true,
							width: 517,
							xtype: 'textfield',
							bodyStyle: 'margin-top: 50px;'
						}
					]
				})
				],

				labelAlign: 'right',
				labelWidth: 130,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [

					{ name: 'EvnReceptGeneral_id'},
					{ name: 'EvnReceptGeneral_pid'},
					{ name: 'ReceptForm_id'},
					{ name: 'ReceptType_id'},
					{ name: 'EvnReceptGeneral_setDate'},
					{ name: 'EvnReceptGeneral_Ser'},
					{ name: 'EvnReceptGeneral_Num'},
					{ name: 'EvnReceptGeneral_IsChronicDisease'},
					{ name: 'EvnReceptGeneral_IsSpecNaz'},
					{ name: 'EvnReceptGeneral_IsExcessDose'},
					{ name: 'PrescrSpecCause_id'},
					{ name: 'ReceptUrgency_id'},
					{ name: 'ReceptValid_id'},
					{ name: 'EvnReceptGeneral_Validity'},
					{ name: 'EvnReceptGeneral_endDate'},
					{ name: 'EvnReceptGeneral_Period'},

					{ name: 'Lpu_id'},
					{ name: 'MedPersonal_id'},
					{ name: 'LpuSection_id'},
					{ name: 'Lpu_Name'},
					{ name: 'LpuSection_Name'},
					{ name: 'MedPersonal_Name'},

					{ name: 'Diag_id'},
					{ name: 'EvnReceptGeneral_IsDelivery'},
					{ name: 'EvnReceptGeneral_hasVK'},
					{ name: 'EvnReceptGeneral_VKProtocolNum'},
					{ name: 'EvnReceptGeneral_VKProtocolDT'},
					{ name: 'CauseVK_id'},

					{ name: 'EvnCourseTreatDrug_id'},
					/*
					{ name: 'EvnReceptGeneralDrugLink_id'},
					{ name: 'Drug_Name'},
					{ name: 'Drug_Kolvo_Pack'},
					{ name: 'Drug_Fas'},
					{ name: 'Drug_Signa'}
					*/
					{ name: 'Drug_Fas_0'},
					{ name: 'Drug_Fas_1'},
					{ name: 'Drug_Fas_2'},

					{ name: 'EvnReceptGeneralDrugLink_id0'},
					{ name: 'Drug_Name0'},
					{ name: 'Drug_Kolvo_Pack0'},
					{ name: 'Drug_Fas0'},
					{ name: 'Drug_Signa0'},

					{ name: 'EvnReceptGeneralDrugLink_id1'},
					{ name: 'Drug_Name1'},
					{ name: 'Drug_Kolvo_Pack1'},
					{ name: 'Drug_Fas1'},
					{ name: 'Drug_Signa1'},

					{ name: 'EvnReceptGeneralDrugLink_id2'},
					{ name: 'Drug_Name2'},
					{ name: 'Drug_Kolvo_Pack2'},
					{ name: 'Drug_Fas2'},
					{ name: 'Drug_Signa2'},


					{ name: 'Person_id'}
				]),
				region: 'center',
				trackResetOnLoad: true,
				url: C_EVNRECGEN_SAVE
			})]
		});

		sw.Promed.swEvnReceptGeneralEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [
		{
		fn: function(inp, e) {
			e.stopEvent();

			if (e.browserEvent.stopPropagation)
				e.browserEvent.stopPropagation();
			else
				e.browserEvent.cancelBubble = true;

			if (e.browserEvent.preventDefault)
				e.browserEvent.preventDefault();
			else
				e.browserEvent.returnValue = false;

			e.returnValue = false;

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			switch ( e.getKey() ) {
				case Ext.EventObject.F6:
					Ext.getCmp('ERGEF_PersonInformationFrame').panelButtonClick(1);
				break;

				case Ext.EventObject.F10:
					Ext.getCmp('ERGEF_PersonInformationFrame').panelButtonClick(2);
				break;

				case Ext.EventObject.F11:
					Ext.getCmp('ERGEF_PersonInformationFrame').panelButtonClick(3);
				break;

				case Ext.EventObject.F12:
					if (e.ctrlKey == true) {
						Ext.getCmp('ERGEF_PersonInformationFrame').panelButtonClick(5);
					}
					else {
						Ext.getCmp('ERGEF_PersonInformationFrame').panelButtonClick(4);
					}
				break;
			}
		},
		key: [
			Ext.EventObject.F6,
			Ext.EventObject.F10,
			Ext.EventObject.F11,
			Ext.EventObject.F12
		],
		scope: this,
		stopEvent: false
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnReceptGeneralEditWindow');

			e.stopEvent();

			if (e.browserEvent.stopPropagation)
				e.browserEvent.stopPropagation();
			else
				e.browserEvent.cancelBubble = true;

			if (e.browserEvent.preventDefault)
				e.browserEvent.preventDefault();
			else
				e.browserEvent.returnValue = false;

			e.returnValue = false;

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			switch (e.getKey()) {
				case Ext.EventObject.G:
					current_window.printRecept();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.C:
					current_window.doSave({
						checkDrugRequest: true,
						checkPersonAge: true,
						checkPersonSnils: true,
						copy: false,
						print: false
					});
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					Ext.getCmp('ERGEF_ReceptPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					Ext.getCmp('ERGEF_DrugPanel').toggleCollapse();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.G,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.ONE,
			Ext.EventObject.R,
			Ext.EventObject.THREE,
			Ext.EventObject.TWO,
			Ext.EventObject.Z
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			//this.buttons[5].focus();
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 500,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	printRecept: function() {
		switch ( this.action ) {
			case 'add':
			case 'edit':
				this.doSave({
					checkDrugRequest: true,
					checkPersonAge: true,
					checkPersonSnils: true,
					copy: false,
					print: true
				});
			break;
			case 'view':
			var base_form = this.findById('EvnReceptGeneralEditForm').getForm();
			var evn_receptgeneral_date = !Ext.isEmpty(base_form.findField('EvnReceptGeneral_setDate').getValue()) ? Ext.util.Format.date(base_form.findField('EvnReceptGeneral_setDate').getValue(), 'Y-m-d') : null;
			var evn_receptgeneral_id = this.findById('EvnReceptGeneralEditForm').getForm().findField('EvnReceptGeneral_id').getValue();
			var ReceptForm_id = this.findById('EvnReceptGeneralEditForm').getForm().findField('ReceptForm_id').getValue();
			Ext.Ajax.request({
				url: '/?c=EvnRecept&m=saveEvnReceptGeneralIsPrinted',
				params:{
					EvnReceptGeneral_id: evn_receptgeneral_id
				},
				callback: function(){
					if (getRegionNick() == 'kz') 
					{
						printBirt({
							'Report_FileName': 'EvnReceptMoney_print.rptdesign',
							'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
							'Report_Format': 'pdf'
						});
						printBirt({
							'Report_FileName': 'EvnReceptMoney_Oborot_print.rptdesign',
							'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
							'Report_Format': 'pdf'
						});
					} 
					else 
					{
						if (ReceptForm_id == 3) {
							if (!Ext.isEmpty(evn_receptgeneral_date) && evn_receptgeneral_date > '2019-04-06') {
							printBirt({
									'Report_FileName': 'EvnReceptGenprint2_new.rptdesign',
									'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
									'Report_Format': 'pdf'
								});
								printBirt({
									'Report_FileName': 'EvnReceptGenPrintOb_new.rptdesign',
									'Report_Params': '',
									'Report_Format': 'pdf'
								});
							}
							else {
								printBirt({
								'Report_FileName': 'EvnReceptGenprint2.rptdesign',
								'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
								'Report_Format': 'pdf'
							});
							printBirt({
								'Report_FileName': 'EvnReceptGenPrintOb.rptdesign',
								'Report_Params': '',
								'Report_Format': 'pdf'
							});
						}
						}
						else if (ReceptForm_id == 2) {
							printBirt({
								'Report_FileName': 'EvnReceptGenprint_1MI.rptdesign',
								'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
								'Report_Format': 'pdf'
							});
						}
						else if (ReceptForm_id == 5 && !Ext.isEmpty(evn_receptgeneral_date) && evn_receptgeneral_date > '2019-04-07') { //при дате выписки рецепта позже 07.04.2019, для рецептов с формой 148-1/у-88 используются отдельные шаблоны
							printBirt({
								'Report_FileName': 'EvnReceptGenprint_2019.rptdesign',
								'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
								'Report_Format': 'pdf'
							});
							printBirt({
								'Report_FileName': 'EvnReceptGenPrintOb_2019.rptdesign',
								'Report_Params': '',
								'Report_Format': 'pdf'
							});
						}
						else if (ReceptForm_id == 5 && !Ext.isEmpty(evn_receptgeneral_date) && evn_receptgeneral_date > '2019-04-07') { //при дате выписки рецепта позже 07.04.2019, для рецептов с формой 148-1/у-88 используются отдельные шаблоны
							printBirt({
								'Report_FileName': 'EvnReceptGenprint_2019.rptdesign',
								'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
								'Report_Format': 'pdf'
							});
							printBirt({
								'Report_FileName': 'EvnReceptGenPrintOb_2019.rptdesign',
								'Report_Params': '',
								'Report_Format': 'pdf'
							});
						}
						else {
							printBirt({
								'Report_FileName': 'EvnReceptGenprint.rptdesign',
								'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
								'Report_Format': 'pdf'
							});
							printBirt({
								'Report_FileName': 'EvnReceptGenPrintOb.rptdesign',
								'Report_Params': '',
								'Report_Format': 'pdf'
							});
						}
					}
				}
			});
			/*if (getRegionNick() == 'kz') 
			{
				printBirt({
					'Report_FileName': 'EvnReceptMoney_print.rptdesign',
					'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
					'Report_Format': 'pdf'
				});
				printBirt({
					'Report_FileName': 'EvnReceptMoney_Oborot_print.rptdesign',
					'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
					'Report_Format': 'pdf'
				});
			} 
			else 
			{
				if (ReceptForm_id == 3) {
					printBirt({
						'Report_FileName': 'EvnReceptGenprint2.rptdesign',
						'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
						'Report_Format': 'pdf'
					});
				} else {
					printBirt({
						'Report_FileName': 'EvnReceptGenprint.rptdesign',
						'Report_Params': '&paramEvnRecept=' + evn_receptgeneral_id,
						'Report_Format': 'pdf'
					});
				}
			}*/
			break;
		}
	},
	resizable: true,
	setReceptNumber: function(action) {
		var wnd = this;
		var base_form = this.findById('EvnReceptGeneralEditForm').getForm();
		if(base_form.findField('ReceptType_id').getValue() == 1){
			// при выписке на бланке генерация не должна работать
			 base_form.findField('EvnReceptGeneral_Num').setValue('');
			 base_form.findField('EvnReceptGeneral_Ser').setValue('');
			 return false;
		}
        var receptform_id = base_form.findField('ReceptForm_id').getValue();
        var recepttype_id = base_form.findField('ReceptType_id').getValue();
        var recept_date = Ext.util.Format.date(base_form.findField('EvnReceptGeneral_setDate').getValue(), 'd.m.Y');
        var sernum_data_check = (wnd.SerNumData.ReceptForm_id == receptform_id && wnd.SerNumData.EvnRecept_setDate == recept_date); //флаг необходимости получения данных серии и номера из кэша

		// чтобы не отрабатывало, пока форма не прогрузится
		if (Ext.isEmpty(receptform_id) || Ext.isEmpty(recepttype_id)) {
			return false;
		}

        if (action != 'update' && sernum_data_check) { //проверяем нет ли у нас уже номера и серии для указанных параметров
            base_form.findField('EvnReceptGeneral_Num').setValue(wnd.SerNumData.EvnRecept_Num);
            base_form.findField('EvnReceptGeneral_Ser').setValue(wnd.SerNumData.EvnRecept_Ser);
        } else if (wnd.SerNumData.state != 'loading' || !sernum_data_check) {
            //чтобы избежать многократных загрузок
            wnd.SerNumData.state = 'loading';
            wnd.SerNumData.ReceptForm_id = receptform_id;
            wnd.SerNumData.EvnRecept_setDate = recept_date;

            Ext.Ajax.request({
                params: {
                    isGeneral: 1,
                    ReceptForm_id: receptform_id,
					ReceptType_id: recepttype_id,
                    EvnRecept_setDate: recept_date
                },
                callback: function(options, success, response) {
                    if ( success ) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        var recept_Num = (response_obj.EvnRecept_Num) ? response_obj.EvnRecept_Num : '';
                        var recept_Ser = (response_obj.EvnRecept_Ser) ? response_obj.EvnRecept_Ser : '';
                        var sernum_source = (response_obj.SerNum_Source) ? response_obj.SerNum_Source : '';

                        var current_receptform_id = base_form.findField('ReceptForm_id').getValue();
                        var current_recept_date = Ext.util.Format.date(base_form.findField('EvnReceptGeneral_setDate').getValue(), 'd.m.Y');

                        if (current_receptform_id == current_receptform_id && current_recept_date == current_recept_date) { //проверяем актуальность параметров
                            base_form.findField('EvnReceptGeneral_Num').setValue(recept_Num);
                            base_form.findField('EvnReceptGeneral_Ser').setValue(recept_Ser);

                            //если серия и номер пришли от нумератора - кэшируем их
                            if (sernum_source == 'Numerator' && !Ext.isEmpty(recept_Num)) {
                                wnd.SerNumData.EvnRecept_Num = recept_Num;
                                wnd.SerNumData.EvnRecept_Ser = recept_Ser;
                                wnd.SerNumData.ReceptForm_id = receptform_id;
                                wnd.SerNumData.EvnRecept_setDate = recept_date;
                            }
                        }
                    }
                    else {
                        sw.swMsg.alert('Ошибка', 'Ошибка при определении номера рецепта', function() { base_form.findField('EvnReceptGeneral_setDate').focus(true); }.createDelegate(this) );
                    }
                    wnd.SerNumData.state = null;
                }.createDelegate(this),
                url: C_RECEPT_NUM
            });
        }
	},
	getReceptElectronicAllow: function(callback) { //вычисление допустимости выбора электронного рецпта, исходя из настроек и наличия у пациента разрешения на фвписку такого рецепта
		var wnd = this;
		var base_form = this.findById('EvnReceptGeneralEditForm').getForm();
		var recept_electronic_allow = getGlobalOptions().recept_electronic_allow; //разрешение выписки рецептов в электронной форме
		var result_data = new Object();

		result_data.recept_electronic_allow = (!Ext.isEmpty(recept_electronic_allow) && wnd.recept_electronic_is_agree == 2);

		if (typeof callback != 'function') {
			callback = Ext.emptyFn;
		}

		if (wnd.recept_electronic_is_agree != null) { //если уже есть информация о согласии пациента
			callback(result_data);
		} else { //если информации о согласии пациента еще нет, то грузим её с сервера
			Ext.Ajax.request({
				url: '/?c=Person&m=isReceptElectronicStatus',
				params: {
					Person_id: base_form.findField('Person_id').getValue()
				},
				callback: function(options, success, response) {
					var error_msg = null;
					if (success) {

						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.Error_Msg) {
							error_msg = langs('Ошибка при получении сведений о согласии на рецепты в электронной форме');
						} else if (response_obj && !Ext.isEmpty(response_obj[0]['ReceptElectronic_IsAgree'])) {
							wnd.recept_electronic_is_agree = response_obj[0]['ReceptElectronic_IsAgree'];
							result_data.recept_electronic_allow = (!Ext.isEmpty(recept_electronic_allow) && wnd.recept_electronic_is_agree == 2);
						}
					} else {
						error_msg = langs('Ошибка при получении сведений о согласии на рецепты в электронной форме');
					}

					if (Ext.isEmpty(error_msg)) {
						callback(result_data);
					} else {
						sw.swMsg.alert(langs('Ошибка'), error_msg);
					}
				}
			});
		}
	},
	getLoadMask: function(txt) {
		if ( Ext.isEmpty(txt) ) {
			txt = 'Подождите...';
		}

		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: txt });
		}

		return this.loadMask;
	},
	resetForm: function(form)
	{
		form.findField('EvnReceptGeneral_id').setValue(0);
		form.findField('EvnReceptGeneral_pid').setValue(0);
		form.findField('ReceptForm_id').clearValue();
		form.findField('ReceptType_id').clearValue();
		form.findField('EvnReceptGeneral_setDate').setValue('');
		form.findField('EvnReceptGeneral_Ser').setValue('');
		form.findField('EvnReceptGeneral_Num').setValue('');
		form.findField('EvnReceptGeneral_IsChronicDisease').setValue(0);
		form.findField('EvnReceptGeneral_IsSpecNaz').setValue(0);
		form.findField('EvnReceptGeneral_IsExcessDose').setValue(0);
		form.findField('PrescrSpecCause_id').setValue(null);
		form.findField('ReceptUrgency_id').clearValue();
		form.findField('ReceptValid_id').clearValue();
		form.findField('EvnReceptGeneral_Validity').setValue('');
		form.findField('EvnReceptGeneral_endDate').setValue('');
		form.findField('EvnReceptGeneral_Period').setValue('');
		form.findField('Lpu_id').setValue(0);
		form.findField('MedPersonal_id').setValue(0);
		form.findField('LpuSection_id').setValue(0);
		form.findField('Lpu_Name').setValue('');
		form.findField('LpuSection_Name').setValue('');
		form.findField('MedPersonal_Name').setValue('');
		form.findField('Diag_id').clearValue();
		form.findField('EvnReceptGeneral_IsDelivery').setValue(false);
		form.findField('Drug_Fas_0').setValue(0);
		form.findField('Drug_Fas_1').setValue(0);
		form.findField('Drug_Fas_2').setValue(0);
		form.findField('EvnReceptGeneralDrugLink_id0').setValue(0);
		form.findField('Drug_Name0').setValue('');
		form.findField('Drug_Kolvo_Pack0').setValue('');
		form.findField('Drug_Fas0').setValue('');
		form.findField('Drug_Signa0').setValue('');
		form.findField('EvnReceptGeneralDrugLink_id1').setValue(0);
		form.findField('Drug_Name1').setValue('');
		form.findField('Drug_Kolvo_Pack1').setValue('');
		form.findField('Drug_Fas1').setValue('');
		form.findField('Drug_Signa1').setValue('');
		form.findField('EvnReceptGeneralDrugLink_id2').setValue(0);
		form.findField('Drug_Name2').setValue('');
		form.findField('Drug_Kolvo_Pack2').setValue('');
		form.findField('Drug_Fas2').setValue('');
		form.findField('Drug_Signa2').setValue('');
		form.findField('Person_id').setValue(0);
		form.strongDrug = false;

	},
	setReceptTypeFilter: function() {
		var base_form = this.findById('EvnReceptGeneralEditForm').getForm();
		var form_field = base_form.findField('ReceptForm_id');
		var type_field = base_form.findField('ReceptType_id');

		if (this.action != 'view' && getRegionNick() != 'kz') { //фильтрация не нужна в режиме просмотра, а также не применяется в регионе Казахстан
			this.getReceptElectronicAllow(function (allow_data) {
				type_field.lastQuery = '';
				type_field.getStore().clearFilter();

				var form_id = form_field.getValue();
				var type_id = type_field.getValue(); //запоминаем выбранную форму

				type_field.getStore().filterBy(function(record) {
					return (record.get('ReceptType_Code') != 3 || (allow_data.recept_electronic_allow && (form_id == 3 || form_id == 5))); //3 - Электронный документ; 3 - 107-1/у; 5 - 148-1/у-88
				});

				var record_idx = type_field.getStore().findBy(function(record) {
					return (record.get('ReceptType_id') == type_id);
				});
				if (Ext.isEmpty(type_id) && record_idx >= 0 && type_field.getStore().getCount() > 0) {
					type_id = type_field.getStore().getAt(0).get('ReceptType_id');
					type_field.setValue(type_id);
					type_field.fireEvent('change', type_field, type_id);
				}
			});
		}
	},
	setReceptType: function () {
		var base_form = this.findById('EvnReceptGeneralEditForm').getForm();

		this.getReceptElectronicAllow(function (allow_data) {
		var receptForm = base_form.findField('ReceptForm_id').getValue();
					var recept_type_combo = base_form.findField('ReceptType_id');

			//если возможно выписывать с типом "Электронный документ" и форма рецепта 148-1/у-88 или 107-1/у
			if ((receptForm == 3 || receptForm == 5) && allow_data.recept_electronic_allow) {
						var index = recept_type_combo.getStore().findBy(function (rec) {
							return (rec.get('ReceptType_Code') == 3);
						});
						if (index >= 0) {
							recept_type_combo.setValue(3);
							recept_type_combo.fireEvent('change', recept_type_combo, 3);
						}
					} else if (receptForm == 8) {
						recept_type_combo.setValue(1);
						recept_type_combo.fireEvent('change', recept_type_combo, 1);
					} else {
						recept_type_combo.setValue(2);
						recept_type_combo.fireEvent('change', recept_type_combo, 2);
					}
		});
	},
	show: function() {
		sw.Promed.swEvnReceptGeneralEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		var base_form = this.findById('EvnReceptGeneralEditForm').getForm();
		//alert('1');
		//log(base_form);
		//alert('2');
		base_form.reset();
		this.resetForm(base_form);

		if ( !arguments[0] ) {
			sw.swMsg.alert('Ошибка', 'Отсутствуют необходимые параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		log(arguments);

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.SerNumData = new Object();

		this.restore();
		this.center();
		this.maximize();

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.findById('ERGEF_DrugPanel').expand();
		this.findById('ERGEF_ReceptPanel').expand();


		var diag_combo = base_form.findField('Diag_id');
		var evn_recept_set_date_field = base_form.findField('EvnReceptGeneral_setDate');
		var recept_type_combo = base_form.findField('ReceptType_id');
		var recept_form_combo = base_form.findField('ReceptForm_id');
		var dt = new Date();
		var evn_receptgeneral_id = 0;
		var EvnCourseTreatDrug_id = 0;
		this.EvnCourseTreatDrug_id = 0;
		this.EvnReceptGeneral_pid = 0;
		var evn_recept_set_date = null;
		var person_id = 0;
		var person_evn_id = 0;
		var ReceptForm_id = 0;
		var recept_type_id = null;
		var server_id = 0;
		//alert('1');
		log(arguments[0]);
		//alert('2');
		//if ( arguments[0].Diag_id )
		//	diag_id = arguments[0].Diag_id;

		if ( arguments[0].EvnReceptGeneral_id )
			evn_receptgeneral_id = arguments[0].EvnReceptGeneral_id;
		if( arguments[0].EvnCourseTreatDrug_id)
		{
			EvnCourseTreatDrug_id = arguments[0].EvnCourseTreatDrug_id;
			this.EvnCourseTreatDrug_id = EvnCourseTreatDrug_id;
		}

		if ( arguments[0].EvnReceptGeneral_pid )
			this.EvnReceptGeneral_pid = arguments[0].EvnReceptGeneral_pid;
		base_form.findField('EvnReceptGeneral_pid').setValue(this.EvnReceptGeneral_pid);

		if ( arguments[0].Person_id )
			person_id = arguments[0].Person_id;

		if ( arguments[0].PersonEvn_id )
			person_evn_id = arguments[0].PersonEvn_id;

		if(arguments[0].ReceptForm_id)
			ReceptForm_id = arguments[0].ReceptForm_id;

		//if ( arguments[0].ReceptType_id > 0 )
		//	recept_type_id = arguments[0].ReceptType_id;

		if ( arguments[0].Server_id >= 0 )
			server_id = arguments[0].Server_id;

		// прогрузим список аптек привязанных + любимую аптеку человека.

		base_form.setValues({
			EvnReceptGeneral_id: evn_receptgeneral_id,
			Person_id: person_id,
			PersonEvn_id: person_evn_id,
			Server_id: server_id
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		//loadMask.show();
		var that = this;
		this.findById('ERGEF_PersonInformationFrame').load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				var field = base_form.findField('EvnReceptGeneral_setDate');
				clearDateAfterPersonDeath('personpanelid', 'ERGEF_PersonInformationFrame', field);
			}
		});

		this.findById('ERGEF_PersonInformationFrame').items.items[1].hide();
		var index;
		for (var i=0; i < 3; i ++)
		{
			base_form.findField('Drug_Name'+i).hideContainer();
			base_form.findField('Drug_Kolvo_Pack'+i).hideContainer();
			base_form.findField('Drug_Fas'+i).hideContainer();
			base_form.findField('Drug_Signa'+i).hideContainer();

			base_form.findField('Drug_Kolvo_Pack'+i).setAllowBlank(true);
			base_form.findField('Drug_Fas'+i).setAllowBlank(true);
			base_form.findField('Drug_Signa'+i).setAllowBlank(true);
		}
		that.Drug_Fas0 = 0;
		that.Drug_Fas1 = 0;
		that.Drug_Fas2 = 0;

		base_form.findField('EvnReceptGeneral_Validity').hideContainer();
		base_form.findField('EvnReceptGeneral_endDate').hideContainer();
		base_form.findField('EvnReceptGeneral_Period').hideContainer();

		base_form.findField('EvnReceptGeneral_Validity').setValue('');
		base_form.findField('EvnReceptGeneral_endDate').setValue('');
		base_form.findField('EvnReceptGeneral_Period').setValue('');


		base_form.findField('ReceptForm_id').disable();
		base_form.findField('ReceptType_id').disable();
		base_form.findField('EvnReceptGeneral_Ser').disable();
		base_form.findField('EvnReceptGeneral_Num').disable();
		Ext.getCmp('swUpdateSerNum').hide();

		if(getRegionNick() == 'kz') {
			base_form.findField('ReceptUrgency_id').hideContainer();
		}else{
			base_form.findField('ReceptUrgency_id').showContainer();
		}
		this.MayClearIsSpecNaz = false;		//для обеспечения работы чекбокса "По специальному назначению" в зависимости от срока рецепта
		that.buttons[0].enable();
		that.buttons[1].enable();
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_DLO_RCPTGENADD);

				base_form.findField('EvnReceptGeneral_hasVK').fireEvent('change', base_form.findField('EvnReceptGeneral_hasVK'), base_form.findField('EvnReceptGeneral_hasVK').getValue());
				base_form.findField('ReceptForm_id').enable();
				base_form.findField('ReceptType_id').enable();
				base_form.findField('EvnReceptGeneral_Ser').enable();
				base_form.findField('EvnReceptGeneral_Num').enable();
				Ext.getCmp('swUpdateSerNum').show();

				if(EvnCourseTreatDrug_id == 0)
				{
					sw.swMsg.alert('Ошибка', 'Не указана строка лекарственного лечения', function() { this.hide(); }.createDelegate(this) );
					return false;
				}
				Ext.Ajax.request({
					url: '/?c=EvnRecept&m=getReceptGeneralAddDetails',
					params: {
						EvnCourseTreatDrug_id: EvnCourseTreatDrug_id
					},
					callback: function(opt,suc, res)
					{
						if(suc)
						{

							var response_obj = Ext.util.JSON.decode(res.responseText);
							base_form.findField('EvnReceptGeneral_setDate').setValue(response_obj[0].EvnReceptGeneral_setDate);
							base_form.findField('EvnReceptGeneral_Ser').setValue(response_obj[0].EvnReceptGeneral_Ser);
							base_form.findField('EvnReceptGeneral_Num').setValue(response_obj[0].EvnReceptGeneral_Num);
							base_form.findField('Lpu_id').setValue(response_obj[0].Lpu_id);
							base_form.findField('MedPersonal_id').setValue(response_obj[0].MedPersonal_id);
							base_form.findField('LpuSection_id').setValue(response_obj[0].LpuSection_id);
							base_form.findField('Lpu_Name').setValue(response_obj[0].Lpu_Name);
							base_form.findField('LpuSection_Name').setValue(response_obj[0].LpuSection_Name);
							base_form.findField('MedPersonal_Name').setValue(response_obj[0].MedPersonal_Name);
							var Diag_id = response_obj[0].Diag_id;
							if ( Diag_id ) {
								diag_combo.getStore().load({
									callback: function() {
										diag_combo.setValue(Diag_id);
										diag_combo.fireEvent('change', diag_combo, Diag_id);
									},
									params: { where: "where Diag_id = " + Diag_id }
								});
							}


							base_form.findField('EvnReceptGeneral_setDate').enable();
							base_form.findField('EvnReceptGeneral_IsChronicDisease').enable();
							base_form.findField('EvnReceptGeneral_IsSpecNaz').enable();
							base_form.findField('ReceptUrgency_id').enable();
							base_form.findField('ReceptValid_id').enable();
							base_form.findField('EvnReceptGeneral_Validity').enable();
							base_form.findField('EvnReceptGeneral_endDate').enable();
							base_form.findField('EvnReceptGeneral_Period').enable();

							base_form.findField('Drug_Name0').showContainer();
							base_form.findField('Drug_Kolvo_Pack0').showContainer();
							base_form.findField('Drug_Kolvo_Pack0').enable();
							base_form.findField('Drug_Fas0').showContainer();
							base_form.findField('Drug_Signa0').showContainer();
							base_form.findField('Drug_Signa0').enable();

							base_form.findField('Drug_Kolvo_Pack0').setAllowBlank(false);
							base_form.findField('Drug_Fas0').setAllowBlank(false);
							base_form.findField('Drug_Signa0').setAllowBlank(false);

							base_form.findField('Drug_Name0').setValue(response_obj[1].Drug_Name);
							base_form.findField('Drug_Kolvo_Pack0').setValue(response_obj[1].Drug_Kolvo_Pack);
							//that.Drug_Fas0 = response_obj[1].Drug_Fas;
							base_form.findField('Drug_Fas_0').setValue(response_obj[1].Drug_Fas_);
							base_form.findField('Drug_Fas0').setValue(base_form.findField('Drug_Fas_0').getValue() * response_obj[1].Drug_Kolvo_Pack);
							base_form.findField('Drug_Signa0').setValue(response_obj[1].Drug_Signa);
							
							var params = {};
							if(!Ext.isEmpty(response_obj[1])){
								if(!Ext.isEmpty(response_obj[1]['narco'])) {
									params['group'] = 'narco';
								}else if(!Ext.isEmpty(response_obj[1]['stronggroup'])){
									params['group'] = 'stronggroup';
								}
								base_form.strongDrug = !Ext.isEmpty(response_obj[1]['narco'])||!Ext.isEmpty(response_obj[1]['stronggroup']);
							}
							
							recept_form_combo.getStore().load({
								params: params,
								callback: function() {
									if(recept_form_combo.findRecord('ReceptForm_id', ReceptForm_id)) {
										recept_form_combo.setValue(ReceptForm_id);
									}else{
										recept_form_combo.clearValue();
									}
									recept_form_combo.fireEvent('change', recept_form_combo, ReceptForm_id);
									that.setReceptType();
									that.setReceptNumber();
								}
							});
						}
					}.createDelegate(this)

				});

				this.setReceptTypeFilter();
			break;
			case 'view':
			case 'edit':
				this.setTitle(WND_DLO_RCPTVIEW);
				if(EvnCourseTreatDrug_id == 0)
					this.setTitle(WND_DLO_RCPTGENEDIT);
				else
					this.setTitle(WND_DLO_RCPTGENADDNEW);
				log(base_form);
				base_form.load({
					failure: function(a,b,c) {
                        var response_obj = Ext.util.JSON.decode(b.response.responseText);
                        //loadMask.hide();
                        if(!Ext.isEmpty(response_obj) && !Ext.isEmpty(response_obj.Error_Msg) && response_obj.Error_Msg != 'Вы не можете открыть рецепт, созданный в другом ЛПУ')
						    sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
                        else
                            this.hide();
					}.createDelegate(this),
					params: {
						EvnReceptGeneral_id: evn_receptgeneral_id,
						archiveRecord: win.archiveRecord
					},
					success: function() {

						var Diag_id = diag_combo.getValue();
						if ( Diag_id ) {
							diag_combo.getStore().load({
								callback: function() {
									diag_combo.setValue(Diag_id);
									diag_combo.fireEvent('change', diag_combo, Diag_id);
								},
								params: { where: "where Diag_id = " + Diag_id }
							});
						}
						var ReceptValid_id = base_form.findField('ReceptValid_id').getValue();
						var PrescrSpecCause_id = base_form.findField('PrescrSpecCause_id').getValue();
						base_form.findField('EvnReceptGeneral_IsSpecNaz').fireEvent('check',base_form.findField('EvnReceptGeneral_IsSpecNaz'),base_form.findField('EvnReceptGeneral_IsSpecNaz').getValue());
						recept_form_combo.fireEvent('change', recept_form_combo, recept_form_combo.getValue());
						base_form.findField('PrescrSpecCause_id').setValue(PrescrSpecCause_id);
						base_form.findField('EvnReceptGeneral_hasVK').fireEvent('change', base_form.findField('EvnReceptGeneral_hasVK'), base_form.findField('EvnReceptGeneral_hasVK').getValue());
						base_form.findField('ReceptValid_id').setValue(ReceptValid_id);
						var index = 0;
						for(var i=0; i < 3; i++)
						{
							if(base_form.findField('EvnReceptGeneralDrugLink_id'+i).getValue() > 0)
							{
								Ext.Ajax.request({
									url: '/?c=EvnRecept&m=checkDrugByLinkIsStrong',
									params: {
										EvnReceptGeneralDrugLink_id: base_form.findField('EvnReceptGeneralDrugLink_id'+i).getValue()
									},
									callback: function(opt,suc,res)
									{
										if(suc)
										{
											var response_obj = Ext.util.JSON.decode(res.responseText);
											base_form.strongDrug = !Ext.isEmpty(response_obj[0]['narco'])||!Ext.isEmpty(response_obj[0]['stronggroup']);
											recept_form_combo.fireEvent('change', recept_form_combo, recept_form_combo.getValue());
										}
									}.createDelegate(this)
								});
								index ++;
								base_form.findField('Drug_Name'+i).showContainer();
								base_form.findField('Drug_Kolvo_Pack'+i).showContainer();
								base_form.findField('Drug_Fas'+i).showContainer();
								base_form.findField('Drug_Signa'+i).showContainer();

								base_form.findField('Drug_Kolvo_Pack'+i).setAllowBlank(false);
								base_form.findField('Drug_Fas'+i).setAllowBlank(false);
								base_form.findField('Drug_Signa'+i).setAllowBlank(false);

								if(EvnCourseTreatDrug_id > 0) //Если добавляем новый медикамент, то остальное дизаблим
								{
									base_form.findField('Drug_Kolvo_Pack'+i).disable();
									base_form.findField('Drug_Signa'+i).disable();
								}
								else
								{
									base_form.findField('Drug_Kolvo_Pack'+i).enable();
									base_form.findField('Drug_Signa'+i).enable();
								}
							}
						}
						if(EvnCourseTreatDrug_id > 0)
						{
							base_form.findField('EvnReceptGeneral_setDate').disable();
							base_form.findField('EvnReceptGeneral_IsChronicDisease').disable();
							base_form.findField('EvnReceptGeneral_IsSpecNaz').disable();
							base_form.findField('ReceptUrgency_id').disable();
							base_form.findField('ReceptValid_id').disable();
							base_form.findField('EvnReceptGeneral_Validity').disable();
							base_form.findField('EvnReceptGeneral_endDate').disable();
							base_form.findField('EvnReceptGeneral_Period').disable();

							base_form.findField('Drug_Name'+index).showContainer();
							base_form.findField('Drug_Kolvo_Pack'+index).showContainer();
							base_form.findField('Drug_Fas'+index).showContainer();
							base_form.findField('Drug_Signa'+index).showContainer();

							base_form.findField('Drug_Kolvo_Pack'+index).setAllowBlank(false);
							base_form.findField('Drug_Fas'+index).setAllowBlank(false);
							base_form.findField('Drug_Signa'+index).setAllowBlank(false);

							Ext.Ajax.request({
								url: '/?c=EvnRecept&m=getReceptGeneralAddDetails',
								params: {
									EvnCourseTreatDrug_id: EvnCourseTreatDrug_id
								},
								callback: function(opt,suc, res)
								{
									if(suc)
									{
										var response_obj = Ext.util.JSON.decode(res.responseText);
										base_form.findField('Drug_Name'+index).setValue(response_obj[1].Drug_Name);
										base_form.findField('Drug_Kolvo_Pack'+index).setValue(response_obj[1].Drug_Kolvo_Pack);
										base_form.findField('Drug_Fas_'+index).setValue(response_obj[1].Drug_Fas_);
										base_form.findField('Drug_Fas'+index).setValue(base_form.findField('Drug_Fas_0').getValue() * response_obj[1].Drug_Kolvo_Pack);
										base_form.findField('Drug_Signa'+index).setValue(response_obj[1].Drug_Signa);

										base_form.findField('Drug_Kolvo_Pack'+index).enable();
										base_form.findField('Drug_Signa'+index).enable();

										base_form.strongDrug = !Ext.isEmpty(response_obj[1]['narco'])||!Ext.isEmpty(response_obj[1]['stronggroup']);
										recept_form_combo.fireEvent('change', recept_form_combo, recept_form_combo.getValue());
									}
								}.createDelegate(this)

							});


						}
						else
						{
							if(
								getGlobalOptions().medpersonal_id != base_form.findField('MedPersonal_id').getValue()
								||
								(base_form.findField('ReceptForm_id').getValue() == 8 && base_form.findField('ReceptDelayType_Code').getValue() === 0)
							)
							{
								that.action = 'view';
							}
							if(that.action!='view')
							{
								base_form.findField('EvnReceptGeneral_setDate').enable();
								base_form.findField('EvnReceptGeneral_IsChronicDisease').enable();
								base_form.findField('EvnReceptGeneral_IsSpecNaz').enable();
								base_form.findField('ReceptUrgency_id').enable();
								base_form.findField('ReceptValid_id').enable();
								base_form.findField('EvnReceptGeneral_Validity').enable();
								base_form.findField('EvnReceptGeneral_endDate').enable();
								base_form.findField('EvnReceptGeneral_Period').enable();
							}
							else
							{
								that.setTitle(WND_DLO_RCPTGENVIEW);
								that.buttons[0].disable();
								that.buttons[1].disable();
								base_form.findField('EvnReceptGeneral_setDate').disable();
								base_form.findField('EvnReceptGeneral_IsChronicDisease').disable();
								base_form.findField('EvnReceptGeneral_IsSpecNaz').disable();
								base_form.findField('ReceptUrgency_id').disable();
								base_form.findField('ReceptValid_id').disable();
								base_form.findField('EvnReceptGeneral_Validity').disable();
								base_form.findField('EvnReceptGeneral_endDate').disable();
								base_form.findField('EvnReceptGeneral_Period').disable();
								for(var j=0;j<3;j++)
								{
									base_form.findField('Drug_Kolvo_Pack'+j).disable();
									base_form.findField('Drug_Signa'+j).disable();
								}
							}
						}
					}.createDelegate(this),
					url: C_EVNRECGEN_LOAD
				});
			break;

			default:
				sw.swMsg.alert('Ошибка', 'Неверно указан режим открытия формы', function() { this.hide(); }.createDelegate(this) );
			break;
		}
	},
	title: WND_DLO_RECADD,
	width: 700
});
