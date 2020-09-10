/**
* swEvnPLDispSomeAdultEditWindow - окно редактирования/добавления талона Диспансеризация отдельных групп взрослого населения
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.002-16.11.2009
* @comment      Префикс для id компонентов EPLDSAEF (EvnPLDispSomeAdultEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              EvnPL_id - ID талона для редактирования или просмотра
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*
*
* Использует: окно выписки листа нетрудоспособности (swEvnStickEditWindow)
*             окно выписки справки учащегося (swEvnStickStudentEditWindow)
*             окно редактирования посещения (swEvnVizitPLDispSomeAdultEditWindow)
*             окно редактирования общей услуги (swEvnUslugaCommonEditWindow)
*             окно добавления комплексной услуги (swEvnUslugaComplexEditWindow)
*             окно поиска организации (swOrgSearchWindowWindow)
*/

sw.Promed.swEvnPLDispSomeAdultEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	filterResultClassCombo: function() {
		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
		// фильтрация комбо ResultClass в зависимости от даты последнего посещения, либо текущей даты, если нет посещений.
		var evn_vizit_pl_store = this.findById('EPLDSAEF_EvnVizitPLGrid').getStore();

		var lastEvnVizitPLDate = null;
		
		evn_vizit_pl_store.each(function(record) {
			if ( lastEvnVizitPLDate == null || record.get('EvnVizitPL_setDate') <= lastEvnVizitPLDate ) {
				lastEvnVizitPLDate = record.get('EvnVizitPL_setDate');
			}
		});
		
		if (Ext.isEmpty(lastEvnVizitPLDate)) {
			lastEvnVizitPLDate = new Date();
		}
		
		base_form.findField('ResultClass_id').getStore().filterBy(function(rec) {
			/*log(lastEvnVizitPLDate);
			log(rec.get('ResultClass_begDT'));
			log(rec.get('ResultClass_endDT'));*/			
			if ( (rec.get('ResultClass_begDT') <= lastEvnVizitPLDate || rec.get('ResultClass_begDT') == '') && (rec.get('ResultClass_endDT') >= lastEvnVizitPLDate || rec.get('ResultClass_endDT') == '') ) {
				return true;
			} else {
				return false;
			}
		});
	},
	deleteEvent: function(event) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( !event.inlist([ 'EvnStick', 'EvnUsluga', 'EvnVizitPL' ]) ) {
			return false;
		}
/*
		if ( event == 'EvnUsluga' && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			return false;
		}
*/
		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnStick':
				grid = this.findById('EPLDSAEF_EvnStickGrid');
			break;

			case 'EvnUsluga':
				grid = this.findById('EPLDSAEF_EvnUslugaGrid');
			break;

			case 'EvnVizitPL':
				grid = this.findById('EPLDSAEF_EvnVizitPLGrid');
			break;
		}
		

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		switch ( event ) {
			case 'EvnStick':
				var evn_pl_id = base_form.findField('EvnPL_id').getValue();
				var evn_stick_mid = selected_record.get('EvnStick_mid');

				if ( selected_record.get('evnStickType') == 3 ) {
					if ( evn_pl_id == evn_stick_mid ) {
						error = lang['pri_udalenii_spravki_uchaschegosya_voznikli_oshibki'];
						question = lang['udalit_spravku_uchaschegosya'];
					}
					else {
						error = lang['pri_udalenii_svyazi_spravki_uchaschegosya_s_tekuschim_dokumentom_voznikli_oshibki'];
						question = lang['udalit_svyaz_spravki_uchaschegosya_s_tekuschim_dokumentom'];
					}

					url = '/?c=Stick&m=deleteEvnStickStudent';

					params['EvnStickStudent_id'] = selected_record.get('EvnStick_id');
					params['EvnStickStudent_mid'] = evn_pl_id;
				}
				else {
					error = lang['pri_udalenii_lvn_voznikli_oshibki'];
					question = lang['udalit_lvn'];

					url = '/?c=Stick&m=deleteEvnStick';

					params['EvnStick_id'] = selected_record.get('EvnStick_id');
					params['EvnStick_mid'] = evn_pl_id;
				}
			break;

			case 'EvnUsluga':
				error = lang['pri_udalenii_uslugi_voznikli_oshibki'];
				question = lang['udalit_uslugu'];
				url = '/?c=EvnUsluga&m=deleteEvnUsluga';

				params['class'] = selected_record.get('EvnClass_SysNick');
				params['id'] = selected_record.get('EvnUsluga_id');
			break;

			case 'EvnVizitPL':
				error = lang['pri_udalenii_posescheniya_voznikli_oshibki'];
				question = lang['udalit_poseschenie'];
				url = '/?c=Evn&m=deleteEvn';

				params['Evn_id'] = selected_record.get('EvnVizitPL_id');
			break;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();

							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
									grid.getTopToolbar().items.items[1].disable();
									grid.getTopToolbar().items.items[2].disable();
									grid.getTopToolbar().items.items[3].disable();
									LoadEmptyRow(grid);
								}
							}
							
							if ( event == 'EvnVizitPL' ) {
								this.getDirectionIf();

								// Перезагрузить грид с услугами
								if ( this.findById('EPLDSAEF_EvnUslugaPanel').isLoaded === true ) {
									this.findById('EPLDSAEF_EvnUslugaGrid').getStore().load({
										params: {
											pid: this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('EvnPL_id').getValue()
										}
									});
								}
							}
							
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}.createDelegate(this),
						url: url
					});
				}
				else {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	doSave: function(options) {
		// options @Object
		// options.ignoreEvnVizitPLCountCheck @Boolean Не проверять наличие посещений, если true
		// options.ignoreDiagCountCheck @Boolean Не проверять наличие основного диагноза, если true
		// options.print @Boolean Вызывать печать рецепта, если true
		// options.openChildWindow @Function Открыть дочернее окно после сохранения

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnPLDispSomeAdultEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var evn_vizit_pl_store = this.findById('EPLDSAEF_EvnVizitPLGrid').getStore();

		if ( !options || !options.ignoreEvnVizitPLCountCheck ) {
			if ( evn_vizit_pl_store.getCount() == 0 || (evn_vizit_pl_store.getCount() == 1 && !evn_vizit_pl_store.getAt(0).get('EvnVizitPL_id')) ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						this.findById('EPLDSAEF_EvnVizitPLGrid').getView().focusRow(0);
						this.findById('EPLDSAEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['ne_vvedeno_ni_odnogo_posescheniya_sohranenie_talona_nevozmojno'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var evn_pl_ukl = base_form.findField('EvnPL_UKL').getValue();
		var is_finish = base_form.findField('EvnPL_IsFinish').getValue();
		var result_class_id = base_form.findField('ResultClass_id').getValue();
		var result_desease_type_id = base_form.findField('ResultDeseaseType_id').getValue();

		if ( !options || !options.ignoreDiagCountCheck ) {
			var diag_exists = false;

			evn_vizit_pl_store.each(function(record) {
				if ( record.get('Diag_id') > 0 ) {
					diag_exists = true;
				}
			});

			if ( !diag_exists ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						this.findById('EPLDSAEF_EvnVizitPLGrid').getView().focusRow(0);
						this.findById('EPLDSAEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['sluchay_lecheniya_doljen_imet_hotya_byi_odin_osnovnoy_diagnoz'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			// https://redmine.swan.perm.ru/issues/15258
			// Проверяем, чтобы для посещения по заболеванию случай был незакончен, а для профилактического - закончен

			// https://redmine.swan.perm.ru/issues/17388
			// Для некоторых отделений допускается сохранение законченного случая с одним посещением по заболеванию

			var isProfVizit = false;
			var isSpecialCase = false;
			var morbusVizitCnt = 0;

			this.findById('EPLDSAEF_EvnVizitPLGrid').getStore().each(function(rec) {
				if ( rec.get('LpuUnitSet_Code').toString().inlist([ '22112', '22105', '22119', '5058', '140', '114' ]) ) {
					isSpecialCase = true;
				}

				if ( rec.get('UslugaComplex_Code') && rec.get('UslugaComplex_Code').length == 6 ) {
					if ( isProphylaxisVizitOnly(rec.get('UslugaComplex_Code')) ) {
						isProfVizit = true;
					} else if ( isMorbusVizitOnly(rec.get('UslugaComplex_Code')) ) {
						morbusVizitCnt = morbusVizitCnt + 1;
					}

					// https://redmine.swan.perm.ru/issues/18168
					if ( /*!Ext.isEmpty(getGlobalOptions().lpu_id)
						&& getGlobalOptions().lpu_id.toString().inlist([ '77', '78', '79', '80', '85', '87' ])
						&&*/ rec.get('UslugaComplex_Code').substr(rec.get('UslugaComplex_Code').length - 3, 3) == '871'
					) {
						isSpecialCase = true;
					}
				}
			});

			if ( isProfVizit == true && is_finish != 2 ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(langs('Ошибка'), langs('Для профилактического/консультативного посещения должен быть указан признак окончания случая лечения и результат лечения'), function() {
					base_form.findField('EvnPL_IsFinish').focus(true);
				});
				return false;
			}
			else if ( morbusVizitCnt == 1 && is_finish == 2 && isSpecialCase == false ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], lang['sohranenie_zakryitogo_tap_po_zabolevaniyu_s_odnim_posescheniem_nevozmojno']);
				return false;
			}
		}

		if ( is_finish == 2 ) {
			if ( evn_pl_ukl == null || evn_pl_ukl.toString().length == 0 || evn_pl_ukl < 0 || evn_pl_ukl > 1 ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnPL_UKL').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['proverte_pravilnost_zapolneniya_polya_ukl_pri_zakonchennom_sluchae_ukl_doljno_byit_zapolneno'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( !options || !options.ignoreDiagCountCheck ) {
				if ( !result_class_id ) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							this.formStatus = 'edit';
							base_form.findField('ResultClass_id').markInvalid();
							base_form.findField('ResultClass_id').focus(false);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: 'При законченном случае поле "'+ ( getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' ) ? 'Результат обращения' : 'Результат лечения' + '" должно быть заполнено',
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
				
				if ( !result_desease_type_id && getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' ) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							this.formStatus = 'edit';
							base_form.findField('ResultDeseaseType_id').markInvalid();
							base_form.findField('ResultDeseaseType_id').focus(false);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: lang['pri_zakonchennom_sluchae_pole_ishod_doljno_byit_zapolneno'],
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
		}

		var params = new Object();

		if ( base_form.findField('Org_did').disabled ) {
			params.Org_did = base_form.findField('Org_did').getValue();
		}

		if ( base_form.findField('EvnDirection_Num').disabled ) {
			params.EvnDirection_Num = base_form.findField('EvnDirection_Num').getRawValue();
		}

		params.EvnDirection_setDate = Ext.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'd.m.Y');

		if ( base_form.findField('Diag_did').disabled ) {
			params.Diag_did = base_form.findField('Diag_did').getValue();
		}

		if ( !getRegionNick().inlist([ 'buryatiya' ]) && base_form.findField('PrehospDirect_id').getValue() == 2 && base_form.findField('EvnDirection_id').getValue() == 0 ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('PrehospDirect_id').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['pri_vyibrannom_znachenii_drugoe_lpu_v_pole_kem_napravlen_vyibor_elektronnogo_napravleniya_yavlyaetsya_obyazatelnyim'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}	

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение талона..." });
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

				if ( action.result ) {
					if ( action.result.EvnPL_id ) {
						var evn_pl_id = action.result.EvnPL_id;

						base_form.findField('EvnPL_id').setValue(evn_pl_id);

						if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
							options.openChildWindow();
						}
						else {
							var date = null;
							var person_information = this.findById('EPLDSAEF_PersonInformationFrame');
							var response = new Object();

							evn_vizit_pl_store.each(function(record) {
								if ( date == null || record.get('EvnVizitPL_setDate') <= date ) {
									date = record.get('EvnVizitPL_setDate');
								}
							});

							response.accessType = 'edit';
							response.EvnPL_disDate = '';
							response.EvnPL_id = evn_pl_id;
							response.EvnPL_IsFinish = base_form.findField('EvnPL_IsFinish').getStore().getById(is_finish).get('YesNo_Name');
							response.EvnPL_NumCard = base_form.findField('EvnPL_NumCard').getValue();
							response.EvnPL_setDate = date;
							response.EvnPL_VizitCount = evn_vizit_pl_store.getCount();
							response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
							response.Person_Firname = person_information.getFieldValue('Person_Firname');
							response.Person_id = base_form.findField('Person_id').getValue();
							response.Person_Secname = person_information.getFieldValue('Person_Secname');
							response.Person_Surname = person_information.getFieldValue('Person_Surname');
							response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
							response.Server_id = base_form.findField('Server_id').getValue();

							this.callback({ evnPLData: response });

							if ( options && options.print == true ) {
								if(getGlobalOptions().region.nick == 'penza'){ //https://redmine.swan.perm.ru/issues/63097
									printBirt({
										'Report_FileName': 'EvnPLPrint.rptdesign',
										'Report_Params': '&paramEvnPL=' + evn_pl_id,
										'Report_Format': 'pdf'
									});
								}
								else
									window.open('/?c=EvnPL&m=printEvnPL&EvnPL_id=' + evn_pl_id, '_blank');

								this.action = 'edit';
								this.setTitle(WND_POL_EPLDSAEDIT);
								
							}
							else {
								this.hide();
							}
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	// тест, переход на универсальную функцию из BaseForm.
	/*enableEdit: function(enable) {
		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
		var form_fields = new Array(
			'Diag_did',
			'DirectClass_id',
			'DirectType_id',
			'EvnPL_Complexity',
			'EvnPL_IsFirstTime',
			'EvnPL_NumCard',
			'EvnPL_UKL',
			'EvnPL_IsFinish',
			'EvnPL_IsUnlaw',
			'EvnPL_IsUnport',
			'PrehospDirect_id',
			'EvnPL_IsWithoutDirection',
			'PrehospTrauma_id',
			'ResultClass_id',
			'ResultDeseaseType_id'
		);
		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},*/
	firstRun: true,
	formStatus: 'edit',
	getEvnPLNumber: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var evnpl_num_field = this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('EvnPL_NumCard');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение номера талона..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					evnpl_num_field.setValue(response_obj.EvnPL_NumCard);
					evnpl_num_field.focus(true);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_talona']);
				}
			},
			url: '/?c=EvnPL&m=getEvnPLNumber'
		});
	},
	getDirectionIf: function()
	{
		var form = this;
		var bf = this.findById('EvnPLDispSomeAdultEditForm').getForm();
		
		//var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Проверка наличия входящего направления" });
		//loadMask.show();
		var EvnPL_id = bf.findField('EvnPL_id').getValue();
		
		if (EvnPL_id>0)
		{
			form.DirectionInfoData.getStore().load(
			{
				params: 
				{
					EvnPL_id: EvnPL_id
				},
				callback: function()
				{
					var form = Ext.getCmp('EvnPLDispSomeAdultEditWindow');
					if (form.DirectionInfoData.getStore().getCount()>0)
					{
						// Экспандим панель входящего направления 
						form.findById('EPLDSAEF_DirectInfoPanel').hide();
						form.DirectionInfoPanel.show();
						form.DirectionInfoPanel.expand();
					}
					else 
					{
						// Коллапсим и скрываем (?) панель направления
						form.findById('EPLDSAEF_DirectInfoPanel').show();
						form.DirectionInfoPanel.collapse();
						form.DirectionInfoPanel.hide();
					}
				}
			});
		}
	},
	height: 550,
	id: 'EvnPLDispSomeAdultEditWindow',
	initComponent: function() {
		this.DirectionInfoData = new Ext.DataView({
			border: false,
			frame: false,
			itemSelector: 'div',
			layout: 'fit',
			//region: 'center',
			getFieldValue: function(field) 
			{
				var result = '';
				if (this.getStore().getAt(0))
					result = this.getStore().getAt(0).get(field);
				return result;
			},
			store: new Ext.data.JsonStore({
				autoLoad: false,
				fields: [
					{ name: 'EvnDirection_id'}, 
					{ name: 'TimetableGraf_id' },
					{ name: 'EvnDirection_Num' }, // Номер
					{ name: 'EvnDirection_setDate', dateFormat: 'd.m.Y', type: 'date' }, // Дата 
					{ name: 'EvnDirection_getDate', dateFormat: 'd.m.Y', type: 'date' }, // Дата 
					{ name: 'DirType_id' },
					{ name: 'DirType_Name' }, // Тип направления 
					{ name: 'Lpu_did' },
					{ name: 'Lpu_Nick' }, // ЛПУ направления 
					{ name: 'LpuSectionProfile_id' },
					{ name: 'LpuSectionProfile_Code' }, // Профиль
					{ name: 'LpuSectionProfile_Name' }, // Профиль
					{ name: 'EvnDirection_setDateTime'}, // Время записи
					{ name: 'Diag_id' },
					{ name: 'Diag_Code' }, 
					{ name: 'Diag_Name' }, 
					{ name: 'EvnDirection_Descr' }, // Описание 
					{ name: 'MedStaffFact_id' }, 
					{ name: 'MedStaffFact_FIO' }, // Врач 
					{ name: 'MedStaffFact_zid' }, 
					{ name: 'MedStaffFact_ZFIO' } // Зав.отделением
				],
				url: '/?c=EvnDirection&m=getDirectionIf'// '/?c=EvnDirection&m=loadEvnDirectionEditForm'
			}),
			tpl: new Ext.XTemplate(
				'<tpl for=".">',
				'<div>Направление №<font style="color: blue; font-weight: bold;">{EvnDirection_Num}</font>, выписано: <font style="color: blue;">{[Ext.util.Format.date(values.EvnDirection_setDate, "d.m.Y")]}</font>, тип направления: <font style="color: blue;">{DirType_Name}</font> </div>',
				'<div>ЛПУ направления: <font style="color: blue;">{Lpu_Nick}</font>, по профилю: <font style="color: blue;">{LpuSectionProfile_Code}.{LpuSectionProfile_Name}</font> ',
				'<div>Диагноз: <font style="color: blue;">{Diag_Code}.{Diag_Name}</font></div>',
				'<div>Врач: <font style="color: blue;">{MedStaffFact_FIO}</font>,  Зав.отделением: <font style="color: blue;">{MedStaffFact_ZFIO}</font></div>',
				'<div>Время записи: <font style="color: blue;">{EvnDirection_setDateTime}</font>',
				'</tpl>'
			)
		});	
				
		this.DirectionInfoPanel = new sw.Promed.Panel({
			autoHeight: true,
			border: true,
			frame: true,
			hidden: true,
			collapsible: true,
			id: 'EPLDSAEF_DirectInfoPanel2',
			isLoaded: false,
			layout: 'form',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						var form = Ext.getCmp('EvnVizitPLDispSomeAdultEditWindow');
						panel.isLoaded = true;
					}
					panel.doLayout();
				}.createDelegate(this)
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['1_po_napravleniyu'],
			items: [this.DirectionInfoData]
		});
	
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({
						ignoreDiagCountCheck: false,
						ignoreEvnVizitPLCountCheck: false,
						print: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

					if ( !this.findById('EPLDSAEF_DirectPanel').collapsed && this.action != 'view' ) {
						if ( !base_form.findField('Lpu_oid').disabled ) {
							base_form.findField('Lpu_oid').focus(true);
						}
						else if ( !base_form.findField('LpuSection_oid').disabled ) {
							base_form.findField('LpuSection_oid').focus(true);
						}
						else {
							base_form.findField('DirectClass_id').focus(true);
						}
					}
					else if ( !this.findById('EPLDSAEF_ResultPanel').collapsed && this.action != 'view' ) {
						base_form.findField('EvnPL_UKL').focus(true);
					}
					else if ( !this.findById('EPLDSAEF_EvnStickPanel').collapsed ) {
						this.findById('EPLDSAEF_EvnStickGrid').getView().focusRow(0);
						this.findById('EPLDSAEF_EvnStickGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPLDSAEF_EvnUslugaPanel').collapsed ) {
						this.findById('EPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPLDSAEF_EvnVizitPLPanel').collapsed ) {
						this.findById('EPLDSAEF_EvnVizitPLGrid').getView().focusRow(0);
						this.findById('EPLDSAEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPLDSAEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
						base_form.findField('EvnPL_IsUnport').focus(true);
					}
					else if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						base_form.findField('EvnPL_Complexity').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLDSAEF + 41,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPL();
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function () {
					if ( this.action == 'view' ) {
						this.buttons[0].onShiftTabAction();
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLDSAEF + 42,
				text: BTN_FRMPRINT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('EvnPL_NumCard').focus(true)
					}
					else {
						if ( !this.findById('EPLDSAEF_EvnVizitPLPanel').collapsed ) {
							this.findById('EPLDSAEF_EvnVizitPLGrid').getView().focusRow(0);
							this.findById('EPLDSAEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EPLDSAEF_EvnUslugaPanel').collapsed ) {
							this.findById('EPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
							this.findById('EPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
						}
						else if ( !this.findById('EPLDSAEF_EvnStickPanel').collapsed ) {
							this.findById('EPLDSAEF_EvnStickGrid').getView().focusRow(0);
							this.findById('EPLDSAEF_EvnStickGrid').getSelectionModel().selectFirstRow();
						}
						else {
							this.buttons[1].focus();
						}
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLDSAEF + 43,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInfoPanel({
				button1OnHide: function() {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('EvnPL_NumCard').focus(true);
					}
				}.createDelegate(this),
				button2Callback: function(callback_data) {
					var form = this.findById('EvnPLDispSomeAdultEditForm');

					form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
					form.getForm().findField('Server_id').setValue(callback_data.Server_id);
					var p = { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id };
					if (form.PersonEvn_id)
						p.PersonEvn_id = form.PersonEvn_id;
					
					this.findById('EPLDSAEF_PersonInformationFrame').load(p); //или прямо form.PersonEvn_id
				}.createDelegate(this),
				button2OnHide: function() {
					this.findById('EPLDSAEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button3OnHide: function() {
					this.findById('EPLDSAEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button4OnHide: function() {
					this.findById('EPLDSAEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button5OnHide: function() {
					this.findById('EPLDSAEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				collapsible: true,
				collapsed: true,
				collectAdditionalParams: function(winType) {
					var params = new Object();

					switch ( winType ) {
						case 5:
							params.Diag_id = null;
							params.LpuSection_id = null;
							params.MedPersonal_id = null;

							var evn_vizit_pl_set_date = null;

							this.findById('EPLDSAEF_EvnVizitPLGrid').getStore().each(function(rec) {
								if ( evn_vizit_pl_set_date == null || evn_vizit_pl_set_date < getValidDT(Ext.util.Format.date(rec.get('EvnVizitPL_setDate'), 'd.m.Y'), rec.get('EvnVizitPL_setTime')) ) {
									evn_vizit_pl_set_date = getValidDT(Ext.util.Format.date(rec.get('EvnVizitPL_setDate'), 'd.m.Y'), rec.get('EvnVizitPL_setTime'));

									params.Diag_id = rec.get('Diag_id');
									params.LpuSection_id = rec.get('LpuSection_id');
									params.MedPersonal_id = rec.get('MedPersonal_id');
								}
							}.createDelegate(this));
						break;
					}

					return params;
				}.createDelegate(this),
				floatable: false,
				id: 'EPLDSAEF_PersonInformationFrame',
				plugins: [ Ext.ux.PanelCollapsedTitle ],
				region: 'north',
				title: '<div>Загрузка...</div>',
				titleCollapse: true
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnPLDispSomeAdultEditForm',
				labelAlign: 'right',
				labelWidth: 180,
				items: [{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				}, {
					name: 'EvnPL_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_id',
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
					value: -1,
					xtype: 'hidden'
				}, {
					name: 'CmpCallCard_id',
					value: 0,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					enableKeyEvents: true,
					fieldLabel: lang['№_talona'],
					listeners: {
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.F2:
									e.stopEvent();
									this.getEvnPLNumber();
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
					name: 'EvnPL_NumCard',
					onTriggerClick: function() {
						this.getEvnPLNumber();
					}.createDelegate(this),
					tabIndex: TABINDEX_EPLDSAEF + 1,
					triggerClass: 'x-form-plus-trigger',
					validateOnBlur: false,
					width: 150,
					xtype: 'trigger'
				}, {
					border: false,
					id: 'EPLDSAEF_KDKBFields',
					layout: 'column',

					items: [{
						border: false,
						layout: 'form',
						items: [{
							comboSubject: 'YesNo',
							fieldLabel: lang['vpervyie_v_dannoy_lpu'],
							hiddenName: 'EvnPL_IsFirstTime',
							tabIndex: TABINDEX_EPLDSAEF + 2,
							width: 150,
							xtype: 'swcommonsprcombo'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							allowDecimals: false,
							allowNegative: false,
							enableKeyEvents: true,
							fieldLabel: lang['kategoriya_slojnosti'],
							listeners: {
								'keydown': function(inp, e) {
									switch ( e.getKey() ) {
										case Ext.EventObject.TAB:
											if ( e.shiftKey == false ) {
												e.stopEvent();

												var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

												if ( !this.findById('EPLDSAEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
													base_form.findField('PrehospDirect_id').focus(true);
												}
												else if ( !this.findById('EPLDSAEF_EvnVizitPLPanel').collapsed ) {
													this.findById('EPLDSAEF_EvnVizitPLGrid').getView().focusRow(0);
													this.findById('EPLDSAEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLDSAEF_EvnUslugaPanel').collapsed ) {
													this.findById('EPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
													this.findById('EPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLDSAEF_EvnStickPanel').collapsed ) {
													this.findById('EPLDSAEF_EvnStickGrid').getView().focusRow(0);
													this.findById('EPLDSAEF_EvnStickGrid').getSelectionModel().selectFirstRow();
												}
												else if ( !this.findById('EPLDSAEF_ResultPanel').collapsed && this.action != 'view' ) {
													base_form.findField('EvnPL_IsFinish').focus(true);
												}
												else if ( !this.findById('EPLDSAEF_DirectPanel').collapsed && this.action != 'view' ) {
													base_form.findField('DirectType_id').focus(true);
												}
												
												else if ( this.action == 'view' ) {
													this.buttons[1].focus();
												}
												else {
													this.buttons[0].focus();
												}
											}
										break;
									}
								}.createDelegate(this)
							},
							maxValue: 5,
							minValue: 1,
							name: 'EvnPL_Complexity',
							tabIndex: TABINDEX_EPLDSAEF + 3,
							width: 150,
							xtype: 'numberfield'
						}]
					}]
				},
				this.DirectionInfoPanel,
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPLDSAEF_DirectInfoPanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							// this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('PrehospDirect_id').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['1_dannyie_o_napravlenii'],
					items: [{
						hiddenName: 'PrehospDirect_id',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
								var record = combo.getStore().getById(newValue);

								var diag_combo = base_form.findField('Diag_did');
								var evn_direction_set_date_field = base_form.findField('EvnDirection_setDate');
								var evn_direction_num_field = base_form.findField('EvnDirection_Num');
								var lpu_section_combo = base_form.findField('LpuSection_did');
								var org_combo = base_form.findField('Org_did');
								var iswd_combo = base_form.findField('EvnPL_IsWithoutDirection');
								var dirsel_btn = this.findById('EPLDSAEF_EvnDirectionSelectButton');
								var prehosp_direct_code = null;
								var evn_direction_id = base_form.findField('EvnDirection_id').getValue();

								dirsel_btn.disable();
								iswd_combo.disable();

								base_form.findField('EvnDirection_id').setValue(0);
								diag_combo.clearValue();
								evn_direction_set_date_field.setRawValue(null);
								evn_direction_num_field.setRawValue(null);
								lpu_section_combo.clearValue();
								org_combo.clearValue();

								if ( record ) {
									prehosp_direct_code = record.get('PrehospDirect_Code');
								}

								switch ( prehosp_direct_code ) {
									case 1:
									case 2:
										diag_combo.enable();
										evn_direction_set_date_field.enable();
										evn_direction_num_field.enable();
										lpu_section_combo.enable();
										org_combo.enable();
										if(iswd_combo.getValue() == 2)
										{
											dirsel_btn.enable();
											diag_combo.disable();
											evn_direction_set_date_field.disable();
											evn_direction_num_field.disable();
											lpu_section_combo.disable();
											org_combo.disable();
										}
										iswd_combo.enable();
										base_form.findField('EvnDirection_id').setValue(evn_direction_id);
									break;

									case 3:
									case 4:
									case 5:
									case 6:
										diag_combo.enable();
										evn_direction_set_date_field.enable();
										evn_direction_num_field.enable();
										lpu_section_combo.disable();
										org_combo.enable();
									break;

									default:
										diag_combo.disable();
										evn_direction_set_date_field.disable();
										evn_direction_num_field.disable()
										lpu_section_combo.disable();
										org_combo.disable();
										if(prehosp_direct_code == null)
											return false;
									break;
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPLDSAEF + 4,
						width: 300,
						xtype: 'swprehospdirectcombo'
					},{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							width: 250,
							items: [
							new sw.Promed.SwYesNoCombo({
								fieldLabel: lang['s_elektronnyim_napravleniem'],
								hiddenName: 'EvnPL_IsWithoutDirection',
								value: 1,
								allowBlank: false,
								tabIndex: TABINDEX_EPLDSAEF + 5,
								width: 60,
								listeners: 
								{
									'change': function (iswd_combo, newValue, oldValue) 
									{
										if ( this.action == 'view' ) {
											return false;
										}

										var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
										var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
										var diag_combo = base_form.findField('Diag_did');
										var evn_direction_set_date_field = base_form.findField('EvnDirection_setDate');
										var evn_direction_num_field = base_form.findField('EvnDirection_Num');
										var lpu_section_combo = base_form.findField('LpuSection_did');
										var org_combo = base_form.findField('Org_did');
										if ( newValue == 2 ) {
											// поля заполняются из эл.направления
											evn_direction_num_field.disable();
											evn_direction_set_date_field.disable();
											lpu_section_combo.disable();
											org_combo.disable();
											diag_combo.disable();
											//prehosp_direct_combo.disable();
										} else {
											evn_direction_num_field.enable();
											evn_direction_set_date_field.enable();
											lpu_section_combo.enable();
											org_combo.enable();
											diag_combo.enable();
											//prehosp_direct_combo.enable();
										}
										prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_combo.getValue());
									}.createDelegate(this)
								}
							})]
						}, {
							border: false,
							layout: 'form',
							width: 200,
							items: [{
								handler: function() {
									this.openEvnDirectionSelectWindow();
								}.createDelegate(this),
								icon: 'img/icons/add16.png', 
								iconCls: 'x-btn-text',
								id: 'EPLDSAEF_EvnDirectionSelectButton',
								tabIndex: TABINDEX_EPLDSAEF + 6,
								text: lang['vyibrat_napravlenie'],
								tooltip: lang['vyibor_napravleniya'],
								xtype: 'button'
							}]
						}]
					}, {
						hiddenName: 'LpuSection_did',
						tabIndex: TABINDEX_EPLDSAEF + 8,
						width: 500,
						xtype: 'swlpusectionglobalcombo'
					}, {
						displayField: 'Org_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: lang['organizatsiya'],
						hiddenName: 'Org_did',
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
							var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
							var combo = base_form.findField('Org_did');

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

							switch ( prehosp_direct_code ) {
								case 2:
								case 5:
									org_type = 'lpu';
								break;

								case 4:
									org_type = 'military';
								break;

								case 3:
								case 6:
									org_type = 'org';
								break;

								default:
									return false;
								break;
							}

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
						tabIndex: TABINDEX_EPLDSAEF + 9,
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
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['№_napravleniya'],
								name: 'EvnDirection_Num',
								tabIndex: TABINDEX_EPLDSAEF + 10,
								width: 150,
								autoCreate: {tag: "input", type: "text", maxLength: "6", autocomplete: "off"},
								xtype: 'numberfield'
							}]
						}, {
							border: false,
							labelWidth: 200,
							layout: 'form',
							items: [{
								fieldLabel: lang['data_napravleniya'],
								format: 'd.m.Y',
								name: 'EvnDirection_setDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EPLDSAEF + 11,
								width: 100,
								xtype: 'swdatefield',
								'change': function(field, newValue, oldValue) {
									blockedDateAfterPersonDeath('personpanelid', 'EPLDSAEF_PersonInformationFrame', field, newValue, oldValue);
								}
							}]
						}]
					},
					new sw.Promed.SwDiagCombo({
						fieldLabel: lang['diagnoz_napr_uchrejdeniya'],
						hiddenName: 'Diag_did',
						tabIndex: TABINDEX_EPLDSAEF + 12,
						width: 500
					}),
					new sw.Promed.SwPrehospTraumaCombo({
						hiddenName: 'PrehospTrauma_id',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

								var is_unlaw_combo = base_form.findField('EvnPL_IsUnlaw');
								var record = combo.getStore().getById(newValue);

								if ( !record ) {
									is_unlaw_combo.clearValue();
									is_unlaw_combo.disable();
								}
								else {
									is_unlaw_combo.setValue(1);
									is_unlaw_combo.enable();
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPLDSAEF + 13,
						width: 300
					}), {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [ new sw.Promed.SwYesNoCombo({
								fieldLabel: lang['protivopravnaya'],
								hiddenName: 'EvnPL_IsUnlaw',
								lastQuery: '',
								tabIndex: TABINDEX_EPLDSAEF + 15,
								width: 70
							})]
						}, {
							border: false,
							labelWidth: 200,
							layout: 'form',
							items: [ new sw.Promed.SwYesNoCombo({
								fieldLabel: lang['netransportabelnost'],
								hiddenName: 'EvnPL_IsUnport',
								lastQuery: '',
								listeners: {
									'keydown': function(inp, e) {
										switch ( e.getKey() ) {
											case Ext.EventObject.TAB:
												var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

												if ( e.shiftKey == false ) {
													e.stopEvent();

													if ( !this.findById('EPLDSAEF_EvnVizitPLPanel').collapsed ) {
														this.findById('EPLDSAEF_EvnVizitPLGrid').getView().focusRow(0);
														this.findById('EPLDSAEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPLDSAEF_EvnUslugaPanel').collapsed ) {
														this.findById('EPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
														this.findById('EPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPLDSAEF_EvnStickPanel').collapsed ) {
														this.findById('EPLDSAEF_EvnStickGrid').getView().focusRow(0);
														this.findById('EPLDSAEF_EvnStickGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPLDSAEF_ResultPanel').collapsed && this.action != 'view' ) {
														base_form.findField('EvnPL_IsFinish').focus(true);
													}
													else if ( !this.findById('EPLDSAEF_DirectPanel').collapsed && this.action != 'view' ) {
														base_form.findField('DirectType_id').focus(true);
													}
													
													else if ( this.action == 'view' ) {
														this.buttons[1].focus();
													}
													else {
														this.buttons[0].focus();
													}
												}
												else if ( this.action == 'view' ) {
													e.stopEvent();
													this.buttons[this.buttons.length - 1].focus();
												}
											break;
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_EPLDSAEF + 16,
								width: 70
							})]
						}]
					}]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 140,
					id: 'EPLDSAEF_EvnVizitPLPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPLDSAEF_EvnVizitPLGrid').getStore().load({
									params: {
										EvnPL_id: this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('EvnPL_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['2_posescheniya'],
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_vizit',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnVizitPL_setDate',
							header: lang['data_posescheniya'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'LpuSection_Name',
							header: lang['otdelenie'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'MedPersonal_Fio',
							header: lang['vrach'],
							hidden: false,
							id: 'autoexpand_vizit',
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'UslugaComplex_Name',
							header: lang['kod_posescheniya'],
							hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'ufa' ])),
							resizable: true,
							sortable: true,
							width: 300
						}, {
							dataIndex: 'Diag_Name',
							header: lang['osnovnoy_diagnoz'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'Diag_Code',
							header: lang['kod'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 80
						}, {
							dataIndex: 'ServiceType_Name',
							header: lang['mesto_obslujivaniya'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'VizitType_Name',
							header: lang['tsel_posescheniya'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}, {
							dataIndex: 'PayType_Name',
							header: lang['vid_oplatyi'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 130
						}],
						frame: false,
						id: 'EPLDSAEF_EvnVizitPLGrid',
						keys: [{
							key: [
								Ext.EventObject.DELETE,
								Ext.EventObject.END,
								Ext.EventObject.ENTER,
								Ext.EventObject.F3,
								Ext.EventObject.F4,
								Ext.EventObject.HOME,
								Ext.EventObject.INSERT,
								Ext.EventObject.PAGE_DOWN,
								Ext.EventObject.PAGE_UP,
								Ext.EventObject.TAB
							],
							fn: function(inp, e) {
								e.stopEvent();

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

								var grid = Ext.getCmp('EPLDSAEF_EvnVizitPLGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										Ext.getCmp('EvnPLDispSomeAdultEditWindow').deleteEvent('EvnVizitPL');
									break;

									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
									case Ext.EventObject.INSERT:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										var action = 'add';

										if ( e.getKey() == Ext.EventObject.F3 ) {
											action = 'view';
										}
										else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
											action = 'edit';
										}

										Ext.getCmp('EvnPLDispSomeAdultEditWindow').openEvnVizitPLDispSomeAdultEditWindow(action);
									break;

									case Ext.EventObject.HOME:
										GridHome(grid);
									break;

									case Ext.EventObject.PAGE_DOWN:
										GridPageDown(grid);
									break;

									case Ext.EventObject.PAGE_UP:
										GridPageUp(grid);
									break;

									case Ext.EventObject.TAB:
										var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPLDSAEF_EvnUslugaPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_EvnStickPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_ResultPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsFinish').focus(true);
											}
											else if ( !this.findById('EPLDSAEF_DirectPanel').collapsed && this.action != 'view' ) {
												base_form.findField('DirectType_id').focus(true);
											}

											else if ( this.action == 'view' ) {
												this.buttons[1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											if ( !this.findById('EPLDSAEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPL_Complexity').focus(true);
											}
										}
									break;
								}
							},
							scope: this,
							stopEvent: true
						}],
						layout: 'fit',
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openEvnVizitPLDispSomeAdultEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var access_type = 'view';
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EPLDSAEF_EvnVizitPLGrid').getTopToolbar();

									if ( selected_record ) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnVizitPL_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' && access_type == 'edit' ) {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
									}
									else {
										toolbar.items.items[2].disable();
									}
								}.createDelegate(this)
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EPLDSAEF_EvnVizitPLGrid'));
									}
									// узкое место, конечно получилось, по идее надо брать высоту шапки и высоту тулбара, а не 78
									if ( store.getCount() < 3 ) {
										this.findById('EPLDSAEF_EvnVizitPLPanel').setHeight(78+store.getCount()*21);
									}
									else
									{
										this.findById('EPLDSAEF_EvnVizitPLPanel').setHeight(140);
									}
									
									this.filterResultClassCombo();
									
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnVizitPL_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'EvnVizitPL_id',
								name: 'EvnVizitPL_id',
								type: 'int'
							}, {
								mapping: 'EvnPL_id',
								name: 'EvnPL_id',
								type: 'int'
							}, {
								mapping: 'Person_id',
								name: 'Person_id',
								type: 'int'
							}, {
								mapping: 'PersonEvn_id',
								name: 'PersonEvn_id',
								type: 'int'
							}, {
								mapping: 'Server_id',
								name: 'Server_id',
								type: 'int'
							}, {
								mapping: 'Diag_id',
								name: 'Diag_id',
								type: 'int'
							}, {
								mapping: 'MedStaffFact_id',
								name: 'MedStaffFact_id',
								type: 'int'
							}, {
								mapping: 'LpuSection_id',
								name: 'LpuSection_id',
								type: 'int'
							}, {
								mapping: 'MedPersonal_id',
								name: 'MedPersonal_id',
								type: 'int'
							}, {
								mapping: 'LpuUnitSet_Code',
								name: 'LpuUnitSet_Code',
								type: 'int'
							}, {
								mapping: 'UslugaComplex_Code',
								name: 'UslugaComplex_Code',
								type: 'string'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnVizitPL_setDate',
								name: 'EvnVizitPL_setDate',
								type: 'date'
							}, {
								mapping: 'EvnVizitPL_setTime',
								name: 'EvnVizitPL_setTime',
								type: 'string'
							}, {
								mapping: 'LpuSection_Name',
								name: 'LpuSection_Name',
								type: 'string'
							}, {
								mapping: 'MedPersonal_Fio',
								name: 'MedPersonal_Fio',
								type: 'string'
							}, {
								mapping: 'Diag_Code',
								name: 'Diag_Code',
								type: 'string'
							}, {
								mapping: 'Diag_Name',
								name: 'Diag_Name',
								type: 'string'
							}, {
								mapping: 'ServiceType_Name',
								name: 'ServiceType_Name',
								type: 'string'
							}, {
								mapping: 'VizitType_Name',
								name: 'VizitType_Name',
								type: 'string'
							}, {
								mapping: 'PayType_Name',
								name: 'PayType_Name',
								type: 'string'
							}, {
								mapping: 'UslugaComplex_Name',
								name: 'UslugaComplex_Name',
								type: 'string'
							}]),
							url: '/?c=EvnPL&m=loadEvnVizitPLGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnVizitPLDispSomeAdultEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: BTN_GRIDADD,
								tooltip: BTN_GRIDADD_TIP
							}, {
								handler: function() {
									this.openEvnVizitPLDispSomeAdultEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: BTN_GRIDEDIT,
								tooltip: BTN_GRIDEDIT_TIP
							}, {
								handler: function() {
									this.openEvnVizitPLDispSomeAdultEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: BTN_GRIDVIEW,
								tooltip: BTN_GRIDVIEW_TIP
							}, {
								handler: function() {
									this.deleteEvent('EvnVizitPL');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: BTN_GRIDDEL,
								tooltip: BTN_GRIDDEL_TIP
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EPLDSAEF_EvnUslugaPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPLDSAEF_EvnUslugaGrid').getStore().load({
									params: {
										'class': 'EvnUslugaCommon',
										'pid': this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('EvnPL_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['3_uslugi'],
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_usluga',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnUsluga_setDate',
							header: lang['data'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Usluga_Code',
							header: lang['kod'],
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Usluga_Name',
							header: lang['naimenovanie'],
							hidden: false,
							id: 'autoexpand_usluga',
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'EvnUsluga_Kolvo',
							header: lang['kolichestvo'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnUsluga_Price',
							header: lang['tsena_uet'],
							hidden: false,
							resizable: true,
							sortable: true,
							renderer: twoDecimalsRenderer,
							width: 100
						}, {
							dataIndex: 'EvnUsluga_Summa',
							header: lang['summa_uet'],
							hidden: false,
							renderer: twoDecimalsRenderer,
							resizable: true,
							sortable: true,
							width: 100
						}],
						frame: false,
						id: 'EPLDSAEF_EvnUslugaGrid',
						keys: [{
							key: [
								Ext.EventObject.DELETE,
								Ext.EventObject.END,
								Ext.EventObject.ENTER,
								Ext.EventObject.F3,
								Ext.EventObject.F4,
								Ext.EventObject.HOME,
								Ext.EventObject.INSERT,
								Ext.EventObject.PAGE_DOWN,
								Ext.EventObject.PAGE_UP,
								Ext.EventObject.TAB
							],
							fn: function(inp, e) {
								e.stopEvent();

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

								var grid = Ext.getCmp('EPLDSAEF_EvnUslugaGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										Ext.getCmp('EvnPLDispSomeAdultEditWindow').deleteEvent('EvnUsluga');
									break;

									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
									case Ext.EventObject.INSERT:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										var action = 'add';

										if ( e.getKey() == Ext.EventObject.F3 ) {
											action = 'view';
										}
										else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
											action = 'edit';
										}

										Ext.getCmp('EvnPLDispSomeAdultEditWindow').openEvnUslugaEditWindow(action);
									break;

									case Ext.EventObject.HOME:
										GridHome(grid);
									break;

									case Ext.EventObject.PAGE_DOWN:
										GridPageDown(grid);
									break;

									case Ext.EventObject.PAGE_UP:
										GridPageUp(grid);
									break;

									case Ext.EventObject.TAB:
										var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPLDSAEF_EvnStickPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_ResultPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsFinish').focus(true);
											}
											else if ( !this.findById('EPLDSAEF_DirectPanel').collapsed && this.action != 'view' ) {
												base_form.findField('DirectType_id').focus(true);
											}

											else if ( this.action == 'view' ) {
												this.buttons[1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											if ( !this.findById('EPLDSAEF_EvnVizitPLPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnVizitPLGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPL_Complexity').focus(true);
											}
										}
									break;
								}
							},
							scope: this,
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openEvnUslugaEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var access_type = 'view';
									var id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EPLDSAEF_EvnUslugaGrid').getTopToolbar();

									if ( selected_record ) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnUsluga_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' && access_type == 'edit' ) {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
									}
									else {
										toolbar.items.items[2].disable();
									}
								}.createDelegate(this)
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							baseParams: {
								'parent': 'EvnPL'
							},
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EPLDSAEF_EvnUslugaGrid'));
									}
									// узкое место, конечно получилось, по идее надо брать высоту шапки и высоту тулбара, а не 78
									if ( store.getCount() < 3 ) {
										this.findById('EPLDSAEF_EvnUslugaPanel').setHeight(78+store.getCount()*21);
									}
									else
									{
										this.findById('EPLDSAEF_EvnUslugaPanel').setHeight(140);
									}

									// this.findById('EPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
									// this.findById('EPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnUsluga_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'EvnUsluga_id',
								name: 'EvnUsluga_id',
								type: 'int'
							}, {
								mapping: 'EvnClass_SysNick',
								name: 'EvnClass_SysNick',
								type: 'string'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnUsluga_setDate',
								name: 'EvnUsluga_setDate',
								type: 'date'
							}, {
								mapping: 'Usluga_Code',
								name: 'Usluga_Code',
								type: 'string'
							}, {
								mapping: 'Usluga_Name',
								name: 'Usluga_Name',
								type: 'string'
							}, {
								mapping: 'EvnUsluga_Kolvo',
								name: 'EvnUsluga_Kolvo',
								type: 'float'
							}, {
								mapping: 'EvnUsluga_Price',
								name: 'EvnUsluga_Price',
								type: 'float'
							}, {
								mapping: 'EvnUsluga_Summa',
								name: 'EvnUsluga_Summa',
								type: 'float'
							}]),
							url: '/?c=EvnUsluga&m=loadEvnUslugaGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnUslugaEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: lang['dobavit']
							}, {
								handler: function() {
									this.openEvnUslugaEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: lang['izmenit']
							}, {
								handler: function() {
									this.openEvnUslugaEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: lang['prosmotr']
							}, {
								handler: function() {
									this.deleteEvent('EvnUsluga');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: lang['udalit']
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EPLDSAEF_EvnStickPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPLDSAEF_EvnStickGrid').getStore().load({
									params: {
										EvnStick_pid: this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('EvnPL_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['4_netrudosposobnost'],
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_stick',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnStick_ParentTypeName',
							header: lang['tap_kvs'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStick_ParentNum',
							header: lang['nomer_tap_kvs'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 120
						}, {
							dataIndex: 'StickType_Name',
							header: lang['vid_dokumenta'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 150
						}, {
							dataIndex: 'EvnStick_IsOriginal',
							header: lang['originalnost'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 150
						}, {
							dataIndex: 'StickWorkType_Name',
							header: lang['tip_zanyatosti'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 150
						}, {
							dataIndex: 'EvnStick_setDate',
							header: lang['data_vyidachi'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStickWorkRelease_begDate',
							header: lang['osvobojden_s'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStickWorkRelease_endDate',
							header: lang['osvobojden_po'],
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStick_disDate',
							header: lang['data_ishoda_lvn'],
    						hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStick_Ser',
							header: lang['seriya'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnStick_Num',
							header: lang['nomer'],
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'StickOrder_Name',
							header: lang['poryadok_vyipiski'],
							hidden: false,
							id: 'autoexpand_stick',
							resizable: true,
							sortable: true
						}],
						frame: false,
						id: 'EPLDSAEF_EvnStickGrid',
						keys: [{
							key: [
								Ext.EventObject.DELETE,
								Ext.EventObject.END,
								Ext.EventObject.ENTER,
								Ext.EventObject.F3,
								Ext.EventObject.F4,
								Ext.EventObject.HOME,
								Ext.EventObject.INSERT,
								Ext.EventObject.PAGE_DOWN,
								Ext.EventObject.PAGE_UP,
								Ext.EventObject.TAB
							],
							fn: function(inp, e) {
								e.stopEvent();

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

								var grid = this.findById('EPLDSAEF_EvnStickGrid');

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										this.deleteEvent('EvnStick');
									break;

									case Ext.EventObject.END:
										GridEnd(grid);
									break;

									case Ext.EventObject.ENTER:
									case Ext.EventObject.F3:
									case Ext.EventObject.F4:
									case Ext.EventObject.INSERT:
										if ( !grid.getSelectionModel().getSelected() ) {
											return false;
										}

										var action = 'add';
										var evnStickType = 0;

										if ( e.getKey() == Ext.EventObject.F3 ) {
											action = 'view';
										}
										else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
											action = 'edit';
										}

										this.openEvnStickEditWindow(action);
									break;

									case Ext.EventObject.HOME:
										GridHome(grid);
									break;

									case Ext.EventObject.PAGE_DOWN:
										GridPageDown(grid);
									break;

									case Ext.EventObject.PAGE_UP:
										GridPageUp(grid);
									break;

									case Ext.EventObject.TAB:
										var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPLDSAEF_ResultPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsFinish').focus(true);
											}
											else if ( !this.findById('EPLDSAEF_DirectPanel').collapsed && this.action != 'view' ) {
												base_form.findField('DirectType_id').focus(true);
											}

											else if ( this.action == 'view' ) {
												this.buttons[1].focus();
											}
											else {
												this.buttons[0].focus();
											}
										}
										else {
											if ( !this.findById('EPLDSAEF_EvnUslugaPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_EvnVizitPLPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnVizitPLGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPL_Complexity').focus(true);
											}
										}
									break;
								}
							}.createDelegate(this),
							scope: this,
							stopEvent: true
						}],
						listeners: {
							'rowdblclick': function(grid, number, obj) {
								this.openEvnStickEditWindow('edit');
							}.createDelegate(this)
						},
						loadMask: true,
						region: 'center',
						sm: new Ext.grid.RowSelectionModel({
							listeners: {
								'rowselect': function(sm, rowIndex, record) {
									var access_type = 'view';
									var id;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EPLDSAEF_EvnStickGrid').getTopToolbar();

									if ( selected_record ) {
										access_type = selected_record.get('accessType');
										id = selected_record.get('EvnStick_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[3].disable();

									if ( id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' && access_type == 'edit' ) {
											toolbar.items.items[1].enable();
											toolbar.items.items[3].enable();
										}
									}
									else {
										toolbar.items.items[2].disable();
									}
								}.createDelegate(this)
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EPLDSAEF_EvnStickGrid'));
									}

									// this.findById('EPLDSAEF_EvnStickGrid').getView().focusRow(0);
									// this.findById('EPLDSAEF_EvnStickGrid').getSelectionModel().selectFirstRow();
								}.createDelegate(this)
							},
							reader: new Ext.data.JsonReader({
								id: 'EvnStick_id'
							}, [{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							}, {
								mapping: 'EvnStick_id',
								name: 'EvnStick_id',
								type: 'int'
							}, {
								mapping: 'EvnStick_mid',
								name: 'EvnStick_mid',
								type: 'int'
							}, {
								mapping: 'EvnStick_pid',
								name: 'EvnStick_pid',
								type: 'int'
							}, {
								mapping: 'evnStickType',
								name: 'evnStickType',
								type: 'int'
							}, {
								mapping: 'parentClass',
								name: 'parentClass',
								type: 'string'
							}, {
								mapping: 'Person_id',
								name: 'Person_id',
								type: 'int'
							}, {
								mapping: 'PersonEvn_id',
								name: 'PersonEvn_id',
								type: 'int'
							}, {
								mapping: 'Server_id',
								name: 'Server_id',
								type: 'int'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnStick_setDate',
								name: 'EvnStick_setDate',
								type: 'date'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnStickWorkRelease_begDate',
								name: 'EvnStickWorkRelease_begDate',
								type: 'date'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnStickWorkRelease_endDate',
								name: 'EvnStickWorkRelease_endDate',
								type: 'date'
							}, {
								dateFormat: 'd.m.Y',
								mapping: 'EvnStick_disDate',
								name: 'EvnStick_disDate',
								type: 'date'
							}, {
								mapping: 'StickOrder_Name',
								name: 'StickOrder_Name',
								type: 'string'
							}, {
								mapping: 'StickType_Name',
								name: 'StickType_Name',
								type: 'string'
							}, {
								mapping: 'StickWorkType_Name',
								name: 'StickWorkType_Name',
								type: 'string'
							}, {
								mapping: 'EvnStick_Ser',
								name: 'EvnStick_Ser',
								type: 'string'
							}, {
								mapping: 'EvnStick_Num',
								name: 'EvnStick_Num',
								type: 'string'
							}, {
								mapping: 'EvnStick_ParentTypeName',
								name: 'EvnStick_ParentTypeName',
								type: 'string'
							}, {
								mapping: 'EvnStick_ParentNum',
								name: 'EvnStick_ParentNum',
								type: 'string'
							}, {
								mapping: 'EvnStick_IsOriginal',
								name: 'EvnStick_IsOriginal',
								type: 'string'							
							}]),
							url: '/?c=Stick&m=loadEvnStickGrid'
						}),
						tbar: new sw.Promed.Toolbar({
							buttons: [{
								handler: function() {
									this.openEvnStickEditWindow('add');
								}.createDelegate(this),
								iconCls: 'add16',
								text: lang['dobavit']
							}, {
								handler: function() {
									this.openEvnStickEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: lang['izmenit']
							}, {
								handler: function() {
									this.openEvnStickEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: lang['prosmotr']
							}, {
								handler: function() {
									this.deleteEvent('EvnStick');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: lang['udalit']
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPLDSAEF_ResultPanel',
					layout: 'form',
						listeners: {
						'expand': function(panel) {
							// this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('EvnPL_IsFinish').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['5_rezultat'],
					items: [{
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['sluchay_zakonchen'],
						hiddenName: 'EvnPL_IsFinish',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));

								return true;
							}.createDelegate(this),
							'select': function(combo, record, id) {
								var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

								if ( !record || record.get('YesNo_Code') == 0 ) {
									base_form.findField('ResultClass_id').clearValue();
									base_form.findField('ResultDeseaseType_id').clearValue();
									base_form.findField('ResultClass_id').setAllowBlank(true);
									base_form.findField('ResultDeseaseType_id').setAllowBlank(true);

									if ( Ext.globalOptions.polka.is_finish_result_block == '1' ) {
										base_form.findField('ResultClass_id').disable();
										base_form.findField('ResultDeseaseType_id').disable();
									}
									else {
										base_form.findField('ResultClass_id').enable();
										base_form.findField('ResultDeseaseType_id').enable();
									}
								}
								else {
									base_form.findField('ResultClass_id').enable();
									base_form.findField('ResultDeseaseType_id').enable();
									base_form.findField('ResultClass_id').setAllowBlank(false);
									base_form.findField('ResultDeseaseType_id').setAllowBlank( !(getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya') );
								}
							}.createDelegate(this),
							'keydown': function(inp, e) {
								switch ( e.getKey() ) {
									case Ext.EventObject.TAB:
										if ( e.shiftKey == true ) {
											e.stopEvent();

											if ( !this.findById('EPLDSAEF_EvnStickPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_EvnUslugaPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_EvnVizitPLPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnVizitPLGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPL_Complexity').focus(true);
											}
										}
									break;
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPLDSAEF + 20,
						width: 70,
						xtype: 'swyesnocombo'
					}, {
						hiddenName: 'ResultClass_id',
						fieldLabel: ( getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' ) ? lang['rezultat_obrascheniya'] : lang['rezultat_lecheniya'],
						lastQuery: '',
						tabIndex: TABINDEX_EPLDSAEF + 21,
						width: 300,
						xtype: 'swresultclasscombo'
					}, {
						comboSubject: 'ResultDeseaseType',
						fieldLabel: lang['ishod'],
						hiddenName: 'ResultDeseaseType_id',
						lastQuery: '',
						tabIndex: TABINDEX_EPLDSAEF + 22,
						width: 300,
						xtype: 'swcommonsprcombo'
					}, {
						allowDecimals: true,
						allowNegative: false,
						fieldLabel: lang['ukl'],
						maxValue: 1,
						name: 'EvnPL_UKL',
						tabIndex: TABINDEX_EPLDSAEF + 22,
						width: 70,
						value: 1,
						xtype: 'numberfield'
					}]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPLDSAEF_DirectPanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							// this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('DirectType_id').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: lang['6_napravlenie'],
					items: [{
						enableKeyEvents: true,
						hiddenName: 'DirectType_id',
						lastQuery: '',
						listeners: {
							'keydown': function(inp, e) {
								switch ( e.getKey() ) {
									case Ext.EventObject.TAB:
										if ( e.shiftKey == true ) {
											e.stopEvent();

											var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

											if ( !this.findById('EPLDSAEF_ResultPanel').collapsed ) {
												base_form.findField('EvnPL_UKL').focus(true);
											}
											else if ( !this.findById('EPLDSAEF_EvnStickPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnStickGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnStickGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_EvnUslugaPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_EvnVizitPLPanel').collapsed ) {
												this.findById('EPLDSAEF_EvnVizitPLGrid').getView().focusRow(0);
												this.findById('EPLDSAEF_EvnVizitPLGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPLDSAEF_DirectInfoPanel').collapsed && this.action != 'view' ) {
												base_form.findField('EvnPL_IsUnport').focus(true);
											}
											else if ( this.action == 'view' ) {
												this.buttons[this.buttons.length - 1].focus();
											}
											else {
												base_form.findField('EvnPL_Complexity').focus(true);
											}
										}
									break;
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPLDSAEF + 25,
						width: 300,
						xtype: 'swdirecttypecombo'
					}, {
						hiddenName: 'DirectClass_id',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
								var record = combo.getStore().getById(newValue);

								var lpu_combo = base_form.findField('Lpu_oid');
								var lpu_section_combo = base_form.findField('LpuSection_oid');

								lpu_combo.clearValue();
								lpu_section_combo.clearValue();

								if ( !record ) {
									lpu_section_combo.disable();
									lpu_combo.disable();
								}
								else if ( record.get('DirectClass_Code') == 1 ) {
									lpu_section_combo.enable();
									lpu_combo.disable();
								}
								else if ( record.get('DirectClass_Code') == 2 ) {
									lpu_section_combo.disable();
									lpu_combo.enable();
								}
								else {
									lpu_section_combo.disable();
									lpu_combo.disable();
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPLDSAEF + 26,
						width: 300,
						xtype: 'swdirectclasscombo'
					}, {
						hiddenName: 'LpuSection_oid',
						tabIndex: TABINDEX_EPLDSAEF + 27,
						width: 500,
						xtype: 'swlpusectionglobalcombo'
					}, {
						displayField: 'Org_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: lang['lpu'],
						hiddenName: 'Lpu_oid',
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
							var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
							var combo = base_form.findField('Lpu_oid');

							if ( combo.disabled ) {
								return false;
							}

							var current_window = this;
							var direct_class_combo = base_form.findField('DirectClass_id');
							var direct_class_id = direct_class_combo.getValue();
							var record = direct_class_combo.getStore().getById(direct_class_id);

							if ( !record ) {
								return false;
							}

							var direct_class_code = record.get('DirectClass_Code');
							var org_type = 'lpu';

							getWnd('swOrgSearchWindow').show({
								object: org_type,
								onClose: function() {
									combo.focus(true, 200)
								},
								onSelect: function(org_data) {
									if ( org_data.Lpu_id > 0 ) {
										combo.getStore().loadData([{
											Lpu_id: org_data.Lpu_id,
											Org_Name: org_data.Org_Name
										}]);
										combo.setValue(org_data.Lpu_id);
										getWnd('swOrgSearchWindow').hide();
									}
								}
							});
						}.createDelegate(this),
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'Lpu_id', type: 'int' },
								{ name: 'Org_Name', type: 'string' }
							],
							key: 'Lpu_id',
							sortInfo: {
								field: 'Org_Name'
							},
							url: C_ORG_LIST
						}),
						tabIndex: TABINDEX_EPLDSAEF + 28,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Org_Name}',
							'</div></tpl>'
						),
						trigger1Class: 'x-form-search-trigger',
						triggerAction: 'none',
						valueField: 'Lpu_id',
						width: 500,
						xtype: 'swbaseremotecombo'
					}]
				})],
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'accessType' },
					{ name: 'Diag_did' },
					{ name: 'DirectClass_id' },
					{ name: 'EvnDirection_id' },
					{ name: 'EvnDirection_Num' },
					{ name: 'EvnDirection_setDate' },
					{ name: 'DirectType_id' },
					{ name: 'EvnPL_Complexity' },
					{ name: 'EvnPL_id' },
					{ name: 'EvnPL_IsFinish' },
					{ name: 'EvnPL_IsFirstTime' },
					{ name: 'EvnPL_IsUnlaw' },
					{ name: 'EvnPL_IsUnport' },
					{ name: 'EvnPL_NumCard' },
					{ name: 'EvnPL_UKL' },
					{ name: 'Lpu_oid' },
					{ name: 'LpuSection_did' },
					{ name: 'LpuSection_oid' },
					{ name: 'Org_did' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'PrehospDirect_id' },
					{ name: 'PrehospTrauma_id' },
					{ name: 'ResultClass_id' },
					{ name: 'ResultDeseaseType_id' },
					{ name: 'Server_id' },
					{ name: 'CmpCallCard_id' }
				]),
				region: 'center',
				url: '/?c=EvnPL&m=saveEvnPL'
			})]
		});

		sw.Promed.swEvnPLDispSomeAdultEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispSomeAdultEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave({
						ignoreDiagCountCheck: false,
						ignoreEvnVizitPLCountCheck: false
					});
				break;

				case Ext.EventObject.G:
					current_window.printEvnPL();
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('EPLDSAEF_DirectInfoPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					current_window.findById('EPLDSAEF_EvnVizitPLPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					current_window.findById('EPLDSAEF_EvnUslugaPanel').toggleCollapse();
				break;

				case Ext.EventObject.FOUR:
				case Ext.EventObject.NUM_FOUR:
					current_window.findById('EPLDSAEF_EvnStickPanel').toggleCollapse();
				break;

				case Ext.EventObject.FIVE:
				case Ext.EventObject.NUM_FIVE:
					current_window.findById('EPLDSAEF_ResultPanel').toggleCollapse();
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					current_window.findById('EPLDSAEF_DirectPanel').toggleCollapse();
				break;
				
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.G,
			Ext.EventObject.FOUR,
			Ext.EventObject.FIVE,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SEVEN,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
		],
		stopEvent: true
	}, {
		alt: false,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispSomeAdultEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.F6:
					current_window.findById('EPLDSAEF_PersonInformationFrame').panelButtonClick(1);
				break;

				case Ext.EventObject.F10:
					current_window.findById('EPLDSAEF_PersonInformationFrame').panelButtonClick(2);
				break;

				case Ext.EventObject.F11:
					current_window.findById('EPLDSAEF_PersonInformationFrame').panelButtonClick(3);
				break;

				case Ext.EventObject.F12:
					if ( e.ctrlKey == true ) {
						current_window.findById('EPLDSAEF_PersonInformationFrame').panelButtonClick(5);
					}
					else {
						current_window.findById('EPLDSAEF_PersonInformationFrame').panelButtonClick(4);
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
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EPLDSAEF_DirectInfoPanel').doLayout();
			win.findById('EPLDSAEF_DirectPanel').doLayout();
			win.findById('EPLDSAEF_EvnStickPanel').doLayout();
			win.findById('EPLDSAEF_EvnUslugaPanel').doLayout();
			win.findById('EPLDSAEF_EvnVizitPLPanel').doLayout();
			win.findById('EPLDSAEF_ResultPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EPLDSAEF_DirectInfoPanel').doLayout();
			win.findById('EPLDSAEF_DirectPanel').doLayout();
			win.findById('EPLDSAEF_EvnStickPanel').doLayout();
			win.findById('EPLDSAEF_EvnUslugaPanel').doLayout();
			win.findById('EPLDSAEF_EvnVizitPLPanel').doLayout();
			win.findById('EPLDSAEF_ResultPanel').doLayout();
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	onCancelAction: function() {
		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
		var evn_pl_id = base_form.findField('EvnPL_id').getValue();

		if ( evn_pl_id > 0 && this.action == 'add') {
			// удалить талон
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление талона..." });
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

					if ( success ) {
						this.hide();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_voznikli_oshibki']);
						return false;
					}
				}.createDelegate(this),
				params: {
					Evn_id: evn_pl_id
				},
				url: '/?c=Evn&m=deleteEvn'
			});
		}
		else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnDirectionSelectWindow: function() {
		if ( this.action == 'view') {
			return false;
		}

		if ( getWnd('swEvnDirectionSelectWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_vyibora_napravleniya_uje_otkryito']);
			return false;
		}

		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

		getWnd('swEvnDirectionSelectWindow').show({
			callback: function(data) {
				base_form.findField('EvnDirection_id').setRawValue(data.EvnDirection_id);

				base_form.findField('Org_did').getStore().load({
					callback: function(records, options, success) {
						if ( success ) {
							base_form.findField('Org_did').setValue(data.Org_did);
						}
					},
					params: {
						Org_id: data.Org_did,
						OrgType: 'lpu'
					}
				});

				base_form.findField('EvnDirection_Num').setRawValue(data.EvnDirection_Num);
				base_form.findField('EvnDirection_setDate').setValue(data.EvnDirection_setDate);

				if ( data.Diag_did ) {
					base_form.findField('Diag_did').getStore().load({
						callback: function() {
							base_form.findField('Diag_did').getStore().each(function(record) {
								if ( record.get('Diag_id') == data.Diag_did ) {
									base_form.findField('Diag_did').setValue(data.Diag_did);
									base_form.findField('Diag_did').fireEvent('select', base_form.findField('Diag_did'), record, 0);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + data.Diag_did }
					});
				}
			}.createDelegate(this),
			onHide: function() {
				base_form.findField('PrehospTrauma_id').focus(true);
			}.createDelegate(this),
			Person_Birthday: this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
			Person_Firname: this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Firname'),
			Person_id: base_form.findField('Person_id').getValue(),
			Person_Secname: this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Secname'),
			Person_Surname: this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Surname')
		});
	},
	// @todo: action implementation :)
	/*
	*
	* @changes:
	* 	- Нам нет необходимости в evnStickType, так как, данный функционал мы переносим в вышележащую форму.
	* 	- Удаляем соответсвующие проверки из функции.
	*
	* */
	openEvnStickEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
		var grid = this.findById('EPLDSAEF_EvnStickGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( action == 'add' && base_form.findField('EvnPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreDiagCountCheck: true,
				ignoreEvnVizitPLCountCheck: false,
				openChildWindow: function() {
					this.openEvnStickEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var formParams = new Object();
		var joborg_id = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('JobOrg_id');
		var params = new Object();
		var person_id = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_post = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Post');
		var person_secname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Surname');

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnStickData ) {
				return false;
			}

			var record = grid.getStore().getById(data.evnStickData[0].EvnStick_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnStick_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.evnStickData, true);
			}
			else {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnStickData[0][grid_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.JobOrg_id = joborg_id;
		params.parentClass = 'EvnPL';
		params.Person_id = base_form.findField('Person_id').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Person_Birthday = person_birthday;
		params.Person_Firname = person_firname;
		params.Person_Post = person_post;
		params.Person_Secname = person_secname;
		params.Person_Surname = person_surname;
		params.Server_id = base_form.findField('Server_id').getValue();

		formParams.EvnStick_mid = base_form.findField('EvnPL_id').getValue();

		if ( action == 'add' ) {
			var evn_stick_beg_date = null;
			var evn_vizit_pl_store = this.findById('EPLDSAEF_EvnVizitPLGrid').getStore();

			evn_vizit_pl_store.each(function(record) {
				if ( evn_stick_beg_date == null || record.get('EvnVizitPL_setDate') <= evn_stick_beg_date ) {
					evn_stick_beg_date = record.get('EvnVizitPL_setDate');
				}
			});

			formParams.EvnStick_begDate = evn_stick_beg_date;
			formParams.EvnStick_pid = base_form.findField('EvnPL_id').getValue();
			formParams.EvnStick_setDate = evn_stick_beg_date;
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();

			params.formParams = formParams;
										
			getWnd('swEvnStickChangeWindow').show(params);
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnStick_id') ) {
				return false;
			}

			if ( selected_record.get('accessType') != 'edit' ) {
				params.action = 'view';
			}

			formParams.EvnStick_id = selected_record.get('EvnStick_id');
			formParams.EvnStick_pid = selected_record.get('EvnStick_pid');
			formParams.Person_id = selected_record.get('Person_id');
			formParams.Server_id = selected_record.get('Server_id');

			params.evnStickType = selected_record.get('evnStickType');
			params.formParams = formParams;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			}.createDelegate(this);
			//params.parentClass = selected_record.get('parentClass');
			params.parentNum = selected_record.get('EvnStick_ParentNum');

			switch ( selected_record.get('evnStickType') ) {
				case 1:
				case 2:
					getWnd('swEvnStickEditWindow').show(params);
				break;

				case 3:
					getWnd('swEvnStickStudentEditWindow').show(params);
				break;

				default:
					return false;
				break;
			}
		}
	},
	openEvnUslugaEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}
/*
		// Если Уфа, то добавление услуги с формы редактирования талона недоступно
		if ( action == 'add' && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			return false;
		}
*/
		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
		var grid = this.findById('EPLDSAEF_EvnUslugaGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnUslugaData ) {
				grid.getStore().load({
					params: {
						pid: base_form.findField('EvnPL_id').getValue()
					}
				});
				return false;
			}

			var record = grid.getStore().getById(data.evnUslugaData.EvnUsluga_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnUsluga_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data.evnUslugaData ], true);
			}
			else {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnUslugaData[grid_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.onHide = function() {
			if ( grid.getSelectionModel().getSelected() ) {
				grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
			}
			else {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}.createDelegate(this);
		params.parentClass = 'EvnPL';
		params.Person_id = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Surname');

		// Собрать данные для ParentEvnCombo
		var parent_evn_combo_data = new Array();

		// Формируем parent_evn_combo_data
		var evn_vizit_pl_grid = this.findById('EPLDSAEF_EvnVizitPLGrid');

		evn_vizit_pl_grid.getStore().each(function(record) {
			var temp_record = new Object();

			temp_record.Evn_id = record.get('EvnVizitPL_id');
			temp_record.Evn_Name = Ext.util.Format.date(record.get('EvnVizitPL_setDate'), 'd.m.Y') + ' / ' + record.get('LpuSection_Name') + ' / ' + record.get('MedPersonal_Fio');
			temp_record.Evn_setDate = record.get('EvnVizitPL_setDate');
			temp_record.Evn_setTime = record.get('EvnVizitPL_setTime');
			temp_record.MedStaffFact_id = record.get('MedStaffFact_id');
			temp_record.LpuSection_id = record.get('LpuSection_id');
			temp_record.MedPersonal_id = record.get('MedPersonal_id');

			parent_evn_combo_data.push(temp_record);
		});

		switch ( action ) {
			case 'add':
				if ( base_form.findField('EvnPL_id').getValue() == 0 ) {
					this.doSave({
						ignoreDiagCountCheck: true,
						ignoreEvnVizitPLCountCheck: false,
						openChildWindow: function() {
							this.openEvnUslugaEditWindow(action);
						}.createDelegate(this),
						print: false
					});
					return false;
				}
/*
				// Открываем форму выбора класса услуги
				if ( getWnd('swEvnUslugaSetWindow').isVisible() ) {
					sw.swMsg.alert(lang['soobschenie'], lang['okno_vyibora_tipa_uslugi_uje_otkryito'], function() {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					});
					return false;
				}
*/
				params.formParams = {
					Person_id: base_form.findField('Person_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue()
				}
				params.parentEvnComboData = parent_evn_combo_data;
/*
				getWnd('swEvnUslugaSetWindow').show({
					EvnUsluga_rid: base_form.findField('EvnPL_id').getValue(),
					onHide: function() {
						if ( grid.getSelectionModel().getSelected() ) {
							grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
						}
						else {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					},
					params: params,
					parentEvent: 'EvnPL'
				});
*/
				getWnd('swEvnUslugaEditWindow').show(params);
			break;

			case 'edit':
			case 'view':
				// Открываем форму редактирования услуги (в зависимости от EvnClass_SysNick)

				var selected_record = grid.getSelectionModel().getSelected();

				if ( !selected_record || !selected_record.get('EvnUsluga_id') ) {
					return false;
				}

				if ( selected_record.get('accessType') != 'edit' ) {
					params.action = 'view';
				}

				var evn_usluga_id = selected_record.get('EvnUsluga_id');
				switch ( selected_record.get('EvnClass_SysNick') ) {
					case 'EvnUslugaCommon':
						params.formParams = {
							EvnUslugaCommon_id: evn_usluga_id
						}
						params.parentEvnComboData = parent_evn_combo_data;
					break;

					default:
						return false;
					break;
				}

				if ( getWnd('swEvnUslugaEditWindow').isVisible() ) {
					sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_uslugi_uje_otkryito'], function() {
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					});
					return false;
				}

				getWnd('swEvnUslugaEditWindow').show(params);
			break;
		}
	},
	openEvnVizitPLDispSomeAdultEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
		var grid = this.findById('EPLDSAEF_EvnVizitPLGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnVizitPLDispSomeAdultEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_posescheniya_patsientom_polikliniki_uje_otkryito']);
			return false;
		}

		// https://redmine.swan.perm.ru/issues/15258
		// Для Уфы проверяем наличие посещений с кодом профилактического посещения. Если такие посещения уже есть, то больше добавлять нельзя
		// Если имеются посещения по заболеванию, то добавлять можно только посещения по заболеваниям

		// Признак возможности добавлять только посещения по заболеваниям
		var allowMorbusVizitOnly = false;
		var allowNonMorbusVizitOnly = false;

		if ( action == 'add' && getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			var allowAdd = true;
			var is871 = false;

			grid.getStore().each(function(rec) {
				if ( rec.get('UslugaComplex_Code') && rec.get('UslugaComplex_Code').length == 6 ) {
					if ( isOneVizitCode(rec.get('UslugaComplex_Code')) ) {
						allowAdd = false;
						is871 = isMorbusOneVizitCode(rec.get('UslugaComplex_Code'));
					} else if ( isMorbusMultyVizitCode(rec.get('UslugaComplex_Code')) ) {
						allowMorbusVizitOnly = true;
					} else {
						allowNonMorbusVizitOnly = true;
					}
				}
			});

			if ( allowAdd == false ) {
				sw.swMsg.alert(lang['soobschenie'], lang['dobavlenie_posescheniya_nevozmojno_t_k_v_ramkah_tekuschego_tap_uje_est_poseschenie_s_kodom'] + (is871 ? lang['odnokratnogo_posescheniya_po_zabolevaniyu'] : lang['profilakticheskogo_posescheniya']));
				return false;
			}
		}

		if ( action == 'add' && base_form.findField('EvnPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreDiagCountCheck: true,
				ignoreEvnVizitPLCountCheck: true,
				openChildWindow: function() {
					this.openEvnVizitPLDispSomeAdultEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		var formParams = new Object();
		var params = new Object();

		params.action = action;
		params.allowMorbusVizitOnly = allowMorbusVizitOnly;
		params.allowNonMorbusVizitOnly = allowNonMorbusVizitOnly;
		params.callback = function(data) {
			if ( !data || !data.evnVizitPLData ) {
				return false;
			}

			if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
				// https://redmine.swan.perm.ru/issues/15258
				// профилактических посещений автоматически устанавливаем признак законченности случая лечения "Да"
				if ( !Ext.isEmpty(data.evnVizitPLData[0].UslugaComplex_Code)
					&& isProphylaxisVizitOnly(data.evnVizitPLData[0].UslugaComplex_Code.toString())
				) {
					base_form.findField('EvnPL_IsFinish').setValue(2);
					base_form.findField('EvnPL_IsFinish').fireEvent('change', base_form.findField('EvnPL_IsFinish'), 2);
				}
			}

			// Проверяем наличие 
			this.getDirectionIf();

			var index = grid.getStore().findBy(function(rec) {
				return (rec.get('EvnVizitPL_id') == data.evnVizitPLData[0].EvnVizitPL_id);
			});
			var record = grid.getStore().getAt(index);
			
			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnVizitPL_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.evnVizitPLData, true);
			}
			else {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnVizitPLData[0][grid_fields[i]]);
				}

				record.commit();
			}
/*
			if ( action == 'add' ) {
				// Перезагружаем информационный фрейм
				this.findById('EPLDSAEF_PersonInformationFrame').load({
					callback: function() {
						this.findById('EPLDSAEF_PersonInformationFrame').setPersonTitle();
					}.createDelegate(this),
					// loadFromDB: true,
					Person_id: base_form.findField('Person_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue()
				});
			}
*/
		}.createDelegate(this);
		params.from = this.params.from;
		params.onHide = function(options) {
			if ( this.findById('EPLDSAEF_EvnUslugaPanel').isLoaded === true && options.EvnUslugaGridIsModified === true ) {
				this.findById('EPLDSAEF_EvnUslugaGrid').getStore().load({
					params: {
						pid: base_form.findField('EvnPL_id').getValue()
					}
				});
			}

			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Surname');
		params.TimetableGraf_id = this.params.TimetableGraf_id;
		params.OmsSprTerr_Code = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');
		params.Sex_Code = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Sex_Code');
		params.UserMedStaffFact_id = this.workplace_params.UserMedStaffFact_id;
		params.UserLpuSection_id = this.workplace_params.UserLpuSection_id;
		params.streamInput = this.streamInput; // Признак добавления посещения с формы поточного ввода

		if ( action == 'add' ) {
			if ( grid.getStore().getCount() == 0 || !grid.getStore().getAt(0).get('EvnVizitPL_id') ) {
				formParams = this.params;
				params.loadLastData = false;

				// Если заполнен диагноз направившего учреждения...
				if ( base_form.findField('Diag_did').getValue() ) {
					formParams.Diag_id = base_form.findField('Diag_did').getValue();
				}
			}
			else if ( this.streamInput == true ) {
				formParams = this.params;
				params.loadLastData = true;
			}
			else {
				params.loadLastData = true;
			}

			formParams.EvnPL_id = base_form.findField('EvnPL_id').getValue();
			formParams.EvnVizitPL_id = 0;
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnVizitPL_id') ) {
				return false;
			}

			if ( selected_record.get('accessType') != 'edit' ) {
				params.action = 'view';
			}

			formParams.EvnVizitPL_id = selected_record.get('EvnVizitPL_id');
			formParams.Person_id = selected_record.get('Person_id');
			formParams.Server_id = selected_record.get('Server_id');
		}

		params.formParams = formParams;
		params.ServiceType_SysNick = this.params.ServiceType_SysNick;

		getWnd('swEvnVizitPLDispSomeAdultEditWindow').show(params);
	},
	openSpecificEditWindow: function(action) {
		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( action == 'add' && base_form.findField('EvnPL_id').getValue() == 0 ) {
			this.doSave({
				ignoreDiagCountCheck: true,
				ignoreEvnVizitPLCountCheck: true,
				openChildWindow: function() {
					this.openSpecificEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		var params = new Object();

		var person_id = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Person_Surname');

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.EvnPLAbortData ) {
				return false;
			}
		}.createDelegate(this);
		params.onHide = Ext.emptyFn;
		params.Person_id = person_id;
		params.Person_Birthday = person_birthday;
		params.Person_Firname = person_firname;
		params.Person_Secname = person_secname;
		params.Person_Surname = person_surname;

		if ( action == 'add' ) {
			var sex_id = this.findById('EPLDSAEF_PersonInformationFrame').getFieldValue('Sex_id');
			var specificList = new Array();
			var is_z303_diag = false;

			params.formParams = {
				EvnPL_id: base_form.findField('EvnPL_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue()
			};

			// Ищем диагноз Z30.3 в списке посещений
			this.findById('EPLDSAEF_EvnVizitPLGrid').getStore().each(function(record) {
				if ( record.get('Diag_id') == 11034 ) {
					is_z303_diag = true;
				}
			});

			if ( sex_id == 2 && Ext.globalOptions.specifics.abort_data === true && is_z303_diag === true ) {
				specificList.push(1);
			}

			if ( specificList.length == 0 ) {
				sw.swMsg.alert(lang['soobschenie'], lang['vvod_spetsifiki_nedostupen']);
				return false;
			}

			getWnd('swSpecificSetWindow').show({
				params: params,
				specificList: specificList
			});
		}
	},
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null,
		MedPersonal_sid: null,
		PayType_id: null,
		ServiceType_id: null,
		VizitType_id: null
	},
	plain: true,
	printEvnPL: function() {
		if ( 'add' == this.action || 'edit' == this.action ) {
			this.doSave({
				ignoreDiagCountCheck: false,
				ignoreEvnVizitPLCountCheck: false,
				print: true
			});
		}
		else if ( 'view' == this.action ) {
			var evn_pl_id = this.findById('EvnPLDispSomeAdultEditForm').getForm().findField('EvnPL_id').getValue();
			if(getGlobalOptions().region.nick == 'penza'){ //https://redmine.swan.perm.ru/issues/63097
				printBirt({
					'Report_FileName': 'EvnPLPrint.rptdesign',
					'Report_Params': '&paramEvnPL=' + evn_pl_id,
					'Report_Format': 'pdf'
				});
			}
			else
				window.open('/?c=EvnPL&m=printEvnPL&EvnPL_id=' + evn_pl_id, '_blank');
		}
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLDispSomeAdultEditWindow.superclass.show.apply(this, arguments);

		if ( getGlobalOptions().lpu_id == 10011165 ) {
			this.findById('EPLDSAEF_KDKBFields').setVisible(true);
		}
		else {
			this.findById('EPLDSAEF_KDKBFields').setVisible(false);
		}

		if ( this.firstRun == true ) {
			this.findById('EPLDSAEF_EvnStickPanel').collapse();
			this.findById('EPLDSAEF_EvnUslugaPanel').collapse();

			this.firstRun = false;
		}

		this.findById('EPLDSAEF_DirectInfoPanel2').hide();
		this.findById('EPLDSAEF_DirectInfoPanel2').isLoaded = false;
		this.DirectionInfoData.getStore().removeAll();
		
		this.restore();
		this.center();
		this.maximize();

		var base_form = this.findById('EvnPLDispSomeAdultEditForm').getForm();
		base_form.reset();

		base_form.findField('ResultDeseaseType_id').setContainerVisible( getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' );
		
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.PersonEvn_id = null;
		this.streamInput = false;
		this.filterResultClassCombo();

		this.params.Diag_id = null;
		this.params.EvnVizitPL_setDate = null;
		this.params.LpuSection_id = null;
		this.params.MedPersonal_id = null;
		this.params.MedPersonal_sid = null;
		this.params.PayType_id = null;
		this.params.ServiceType_id = null;
		this.params.ServiceType_SysNick = null;
		this.params.VizitType_id = null;
		
		this.params.from = null;
		this.params.TimetableGraf_id = null;
		
		this.workplace_params = {};
		this.workplace_params.UserMedStaffFact_id = null;
		this.workplace_params.UserLpuSection_id = null;

		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_setDate').disable();
		base_form.findField('EvnPL_IsUnlaw').disable();
		base_form.findField('Lpu_oid').disable();
		base_form.findField('LpuSection_did').disable();
		base_form.findField('LpuSection_oid').disable();
		base_form.findField('Org_did').disable();

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		base_form.setValues(arguments[0]);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( !Ext.isEmpty(arguments[0].Diag_id) ) {
			this.params.Diag_id = arguments[0].Diag_id;
		}

		if ( !Ext.isEmpty(arguments[0].EvnVizitPL_setDate) ) {
			this.params.EvnVizitPL_setDate = arguments[0].EvnVizitPL_setDate;
		}

		if ( !Ext.isEmpty(arguments[0].LpuSection_id) ) {
			this.params.LpuSection_id = arguments[0].LpuSection_id;
		}

		if ( !Ext.isEmpty(arguments[0].MedPersonal_id) ) {
			this.params.MedPersonal_id = arguments[0].MedPersonal_id;
		}

		if ( !Ext.isEmpty(arguments[0].MedPersonal_sid) ) {
			this.params.MedPersonal_sid = arguments[0].MedPersonal_sid;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		if ( !Ext.isEmpty(arguments[0].PayType_id) ) {
			this.params.PayType_id = arguments[0].PayType_id;
		}

		if ( !Ext.isEmpty(arguments[0].ServiceType_id) ) {
			this.params.ServiceType_id = arguments[0].ServiceType_id;
		}

		if ( !Ext.isEmpty(arguments[0].ServiceType_SysNick) ) {
			this.params.ServiceType_SysNick = arguments[0].ServiceType_SysNick;
		}
		
		if ( !Ext.isEmpty(arguments[0].VizitType_id) ) {
			this.params.VizitType_id = arguments[0].VizitType_id;
		}
		
		if ( arguments[0].from ) {
			this.params.from = arguments[0].from;
		}

		if ( arguments[0].streamInput ) {
			this.streamInput = arguments[0].streamInput;
		}

		if ( arguments[0].PersonEvn_id && arguments[0].usePersonEvn ) {
			this.PersonEvn_id = arguments[0].PersonEvn_id;
		}

		if ( !Ext.isEmpty(arguments[0].TimetableGraf_id) ) {
			this.params.TimetableGraf_id = arguments[0].TimetableGraf_id;
		}
		
		if ( !Ext.isEmpty(arguments[0].UserMedStaffFact_id) ) {
			this.workplace_params.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}

		if ( !Ext.isEmpty(arguments[0].UserLpuSection_id) ) {
			this.workplace_params.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}

	
		if ( this.action == 'add' ) {
			this.findById('EPLDSAEF_EvnStickPanel').isLoaded = true;
			this.findById('EPLDSAEF_EvnUslugaPanel').isLoaded = true;
			this.findById('EPLDSAEF_EvnVizitPLPanel').isLoaded = true;
		}
		else {
			this.findById('EPLDSAEF_EvnStickPanel').isLoaded = false;
			this.findById('EPLDSAEF_EvnUslugaPanel').isLoaded = false;
			this.findById('EPLDSAEF_EvnVizitPLPanel').isLoaded = false;
			//this.findById('EPLDSAEF_SpecificPanel').isLoaded = false;
		}

		var diag_combo = base_form.findField('Diag_did');
		var direct_class_combo = base_form.findField('DirectClass_id');
		var is_finish_combo = base_form.findField('EvnPL_IsFinish');
		var is_unlaw_combo = base_form.findField('EvnPL_IsUnlaw');
		var is_unport_combo = base_form.findField('EvnPL_IsUnport');
		var lpu_section_combo = base_form.findField('LpuSection_did');
		var lpu_section_dir_combo = base_form.findField('LpuSection_oid');
		var org_combo = base_form.findField('Org_did');
		var org_dir_combo = base_form.findField('Lpu_oid');
		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var iswd_combo = base_form.findField('EvnPL_IsWithoutDirection');
		var prehosp_trauma_combo = base_form.findField('PrehospTrauma_id');
		var result_class_combo = base_form.findField('ResultClass_id');

		var evn_pl_id = base_form.findField('EvnPL_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		this.findById('EPLDSAEF_EvnVizitPLGrid').getStore().removeAll();
		this.findById('EPLDSAEF_EvnVizitPLGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPLDSAEF_EvnVizitPLGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLDSAEF_EvnVizitPLGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLDSAEF_EvnVizitPLGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPLDSAEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('EPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPLDSAEF_EvnStickGrid').getStore().removeAll();
		this.findById('EPLDSAEF_EvnStickGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPLDSAEF_EvnStickGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLDSAEF_EvnStickGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLDSAEF_EvnStickGrid').getTopToolbar().items.items[3].disable();
/*
		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
			this.findById('EPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[0].disable();
			this.findById('EPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable(); // и удаление 
		}
*/
		setLpuSectionGlobalStoreFilter();

		lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		lpu_section_dir_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_EPLDSAADD);
				this.enableEdit(true);

				LoadEmptyRow(this.findById('EPLDSAEF_EvnStickGrid'));
				LoadEmptyRow(this.findById('EPLDSAEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('EPLDSAEF_EvnVizitPLGrid'));

				var direct_class_id = direct_class_combo.getValue();
				var is_finish = is_finish_combo.getValue();

				if ( is_finish == null || is_finish.toString().length == 0 ) {
					is_finish = 1;
				}

				this.findById('EPLDSAEF_PersonInformationFrame').setTitle('...');
				this.findById('EPLDSAEF_PersonInformationFrame').clearPersonChangeParams();
				this.findById('EPLDSAEF_PersonInformationFrame').load({
					callback: function() {
						this.findById('EPLDSAEF_PersonInformationFrame').setPersonTitle();
					}.createDelegate(this),
					onExpand: true,
					Person_id: person_id,
					Server_id: server_id
				});

				direct_class_combo.setValue(direct_class_id);
				is_finish_combo.setValue(is_finish);
				is_unport_combo.setValue(1);

				direct_class_combo.fireEvent('change', direct_class_combo, direct_class_id, direct_class_id + 1);
				is_finish_combo.fireEvent('change', is_finish_combo, is_finish, is_finish + 1);
				iswd_combo.setValue(1);
				iswd_combo.fireEvent('change',iswd_combo, iswd_combo.getValue());
				//prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, null, 1);
				prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, null, 1);

				loadMask.hide();

				this.getEvnPLNumber();

				// base_form.findField('EvnPL_NumCard').focus(false, 250);
			break;

			case 'edit':
			case 'view':
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnPL_id: evn_pl_id
					},
					success: function() {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'view' ) {
							this.setTitle(WND_POL_EPLDSAVIEW);
							this.enableEdit(false);

							this.findById('EPLDSAEF_PersonInformationFrame').clearPersonChangeParams();
						}
						else {
							this.setTitle(WND_POL_EPLDSAEDIT);
							this.enableEdit(true);

							this.findById('EPLDSAEF_PersonInformationFrame').setPersonChangeParams({
								 callback: function(data) {
									this.hide();
								 }.createDelegate(this)
								,Evn_id: evn_pl_id
							});
						}

						this.findById('EPLDSAEF_PersonInformationFrame').setTitle('...');
						this.findById('EPLDSAEF_PersonInformationFrame').load({
							callback: function() {
								this.findById('EPLDSAEF_PersonInformationFrame').setPersonTitle();
							}.createDelegate(this),
							onExpand: true,
							Person_id: base_form.findField('Person_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue(),
							PersonEvn_id: (this.PersonEvn_id ? this.PersonEvn_id : base_form.findField('PersonEvn_id').getValue())
						});

						// Посещения прогружаем в любом случае
						this.findById('EPLDSAEF_EvnVizitPLPanel').fireEvent('expand', this.findById('EPLDSAEF_EvnVizitPLPanel'));

						// Остальные гриды - только если развернуты панельки
						if ( !this.findById('EPLDSAEF_EvnStickPanel').collapsed ) {
							this.findById('EPLDSAEF_EvnStickPanel').fireEvent('expand', this.findById('EPLDSAEF_EvnStickPanel'));
						}

						if ( !this.findById('EPLDSAEF_EvnUslugaPanel').collapsed ) {
							this.findById('EPLDSAEF_EvnUslugaPanel').fireEvent('expand', this.findById('EPLDSAEF_EvnUslugaPanel'));
						}

						/*
						if ( !this.findById('EPLDSAEF_SpecificPanel').collapsed ) {
							this.findById('EPLDSAEF_SpecificPanel').fireEvent('expand', this.findById('EPLDSAEF_SpecificPanel'));
						}*/

						var diag_did = diag_combo.getValue();
						var direct_class_id = direct_class_combo.getValue();
						var evn_direction_id = base_form.findField('EvnDirection_id').getValue();
						var evn_direction_num = base_form.findField('EvnDirection_Num').getValue();
						var evn_direction_set_date = base_form.findField('EvnDirection_setDate').getValue();
						var evnpl_isfinish = is_finish_combo.getValue();
						var evnpl_isunlaw = is_unlaw_combo.getValue();
						var lpu_section_did = lpu_section_combo.getValue();
						var lpu_section_oid = lpu_section_dir_combo.getValue();
						var org_did = org_combo.getValue();
						var lpu_oid = org_dir_combo.getValue();
						var prehosp_direct_id = prehosp_direct_combo.getValue();
						var prehosp_trauma_id = prehosp_trauma_combo.getValue();
						var record;
						var result_class_id = result_class_combo.getValue();

						if ( this.action == 'edit' ) {
							iswd_combo.setValue((evn_direction_id > 0)?2:1);
							iswd_combo.fireEvent('change',iswd_combo, iswd_combo.getValue());
							//prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_id, -1);
							prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_id, -1);
							is_unlaw_combo.setValue(evnpl_isunlaw);
							direct_class_combo.fireEvent('change', direct_class_combo, direct_class_id, direct_class_id + 1);
							is_finish_combo.fireEvent('change', is_finish_combo, evnpl_isfinish, -1);
							result_class_combo.setValue(result_class_id);
						}
						else {
							this.findById('EPLDSAEF_EvnDirectionSelectButton').disable();

							this.findById('EPLDSAEF_EvnVizitPLGrid').getTopToolbar().items.items[0].disable();
							this.findById('EPLDSAEF_EvnUslugaGrid').getTopToolbar().items.items[0].disable();
							this.findById('EPLDSAEF_EvnStickGrid').getTopToolbar().items.items[0].disable();
						}

						record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

						if ( record ) {
							var org_type = '';

							switch ( record.get('PrehospDirect_Code') ) {
								case 1:
								break;

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

							if ( org_type.length == 0 ) {
								lpu_section_combo.setValue(lpu_section_did);
							}
							else {
								org_combo.getStore().load({
									callback: function(records, options, success) {
										if ( success ) {
											org_combo.setValue(org_did);
										}
									},
									params: {
										Org_id: org_did,
										OrgType: org_type
									}
								});
							}
						}

						base_form.findField('EvnDirection_id').setValue(evn_direction_id);
						base_form.findField('EvnDirection_Num').setRawValue(evn_direction_num);
						base_form.findField('EvnDirection_setDate').setValue(evn_direction_set_date);

						if ( diag_did != null && diag_did.toString().length > 0 ) {
							diag_combo.getStore().load({
								callback: function() {
									diag_combo.getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_did ) {
											diag_combo.setValue(diag_did);
											diag_combo.fireEvent('select', diag_combo, record, 0);
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_did }
							});
						}

						record = direct_class_combo.getStore().getById(direct_class_id);

						if ( record ) {
							var direct_class_code = record.get('DirectClass_Code');

							switch ( direct_class_code ) {
								case 1:
									lpu_section_dir_combo.setValue(lpu_section_oid);
								break;

								case 2:
									org_dir_combo.getStore().load({
										callback: function(records, options, success) {
											if ( success ) {
												org_dir_combo.setValue(lpu_oid);
											}
										},
										params: {
											Lpu_oid: lpu_oid,
											OrgType: 'lpu'
										}
									});
								break;

								default:
									return false;
								break;
							}
						}

						this.getDirectionIf();

						loadMask.hide();

						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnPL_NumCard').focus(false, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnPL&m=loadEvnPLEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}

		base_form.clearInvalid();
	},
    collectGridData:function (gridName) {
        var result = '';
		if (this.findById('MHW_' + gridName)) {
			var grid = this.findById('MHW_' + gridName).getGrid();
			grid.getStore().clearFilter();
			if (grid.getStore().getCount() > 0) {
				if ((grid.getStore().getCount() == 1) && ((grid.getStore().getAt(0).data.RecordStatus_Code == undefined))) {
					return '';
				}
				var gridData = getStoreRecords(grid.getStore(), {convertDateFields:true});
				result = Ext.util.JSON.encode(gridData);
			}
			grid.getStore().filterBy(function (rec) {
				return Number(rec.get('RecordStatus_Code')) != 3;
			});
		}
        return result;
    },
	openWindow: function(gridName, action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('sw'+gridName+'Window').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_uje_otkryito']);
			return false;
		}

		var grid = this.findById('MHW_'+gridName).getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			
			if (!data || !data.BaseData) {
				return false;
			}
			
			data.BaseData.RecordStatus_Code = 0;

			// Обновить запись в grid
			var record = grid.getStore().getById(data.BaseData[gridName+'_id']);

			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.BaseData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.BaseData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get(gridName+'_id')) {
					grid.getStore().removeAll();
				}

				data.BaseData[gridName+'_id'] = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.BaseData ], true);
			}
		}
		params.formMode = 'local';
		params.formParams = new Object();

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.formParams = selected_record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('sw'+gridName+'Window').show(params);
		
	},
	deleteGridSelectedRecord: function(gridId, idField) {
		var grid = this.findById(gridId).getGrid();
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					if (!grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField)) {
						return false;
					}
					switch (Number(record.get('RecordStatus_Code'))) {
						case 0:
							grid.getStore().remove(record);
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								if (Number(rec.get('RecordStatus_Code')) == 3) {
									return false;
								}
								else {
									return true;
								}
							});
							break;
					}
				}
				if (grid.getStore().getCount() > 0) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_etu_zapis'],
			title: lang['vopros']
		});
	},
	width: 800
});
