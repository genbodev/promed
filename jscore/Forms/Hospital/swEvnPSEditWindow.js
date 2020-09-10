/**
 * swEvnPSEditWindow - окно редактирования/добавления карты выбывшего из стационара.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Stas Bykov aka Savage (savage@swan.perm.ru)
 * @version      0.001-09.03.2010
 * @comment      Префикс для id компонентов EPSEF (EvnPSEditForm)
 *
 *
 * @input data: action - действие (add, edit, view)
 *              EvnPS_id - ID КВС для редактирования или просмотра
 *              Person_id - ID человека
 *              PersonEvn_id - ID состояния человека
 *              Server_id - ID сервера
 *
 *
 * Использует: окно редактирования диагноза в стационаре (swEvnDiagPSEditWindow)
 *             окно редактирования движения пациента в стационаре (swEvnSectionEditWindow)
 *             окно выписки листа нетрудоспособности (swEvnStickEditWindow)
 *             окно редактирования общей услуги (swEvnUslugaCommonEditWindow)
 *             окно добавления комплексной услуги (swEvnUslugaComplexEditWindow)
 *             окно добавления оперативной услуги (swEvnUslugaOperEditWindow)
 */
/*NO PARSE JSON*/
sw.Promed.swEvnPSEditWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh: true,
    objectName: 'swEvnPSEditWindow',
    objectSrc: '/jscore/Forms/Hospital/swEvnPSEditWindow.js',

    action: null,
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: false,
    closeAction: 'hide',
    collapsible: true,
	dataDirection: null,

	undoDeleteEvnStick: function() {
		var win = this;
		var grid = this.findById('EPSEF_EvnStickGrid');

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnStick_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		win.getLoadMask('Отмена удаления ЛВН').show();
		Ext.Ajax.request({
			params: {
				EvnStick_id: selected_record.get('EvnStick_id')
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();

				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.success) {
						sw.swMsg.alert('Внимание', 'ЛВН успешно восстановлен');
						
						grid.getStore().load({
							params: {
								EvnStick_pid: selected_record.get('EvnStick_pid')
							}
						});
						
					}
				}
			}.createDelegate(this),
			url: '/?c=Stick&m=undoDeleteEvnStick'
		});
	},

	deleteEvent: function(event, options) {
		options = options || {};
        // @options.ignoreEvnStickIsClosed int
        /*if ( this.action == 'view') {
            return false;
        }*/

        if ( !event.inlist(['EvnStick', 'EvnUsluga', 'EvnDiagPSHosp', 'EvnDiagPSRecep', 'EvnSection', 'EvnDrug','EvnReanimatPeriod']) ) {
            return false;
        }

        var win = this;
        var base_form = this.findById('EvnPSEditForm').getForm();
        var error = '';
        var grid = null;
        var question = '';
        var params = new Object();
		if (options.params) {
			params = options.params;
		}
        var url = '';

        switch ( event ) {
            case 'EvnDrug':
                grid = this.findById('EPSEF_EvnDrugGrid');
                break;

            case 'EvnSection':
                grid = this.findById('EPSEF_EvnSectionGrid');
                break;

            case 'EvnStick':
                grid = this.findById('EPSEF_EvnStickGrid');
                break;

            case 'EvnUsluga':
                grid = this.findById('EPSEF_EvnUslugaGrid');
                break;

            case 'EvnDiagPSHosp':
                grid = this.findById('EPSEF_EvnDiagPSHospGrid');
                break;

            case 'EvnDiagPSRecep':
                grid = this.findById('EPSEF_EvnDiagPSRecepGrid');
                break;
				
			//BOB - 04.09.2018
            case 'EvnReanimatPeriod':
                grid = this.findById('EPSEF_EvnReanimatPeriodGrid');
                break;
				
        }

        if ( !grid || !grid.getSelectionModel().getSelected() ) {
            return false;
        }
        else if ( (event == 'EvnDiagPSHosp' || event == 'EvnDiagPSRecep') && ! grid.getSelectionModel().getSelected().get('EvnDiagPS_id') ) {
            return false;
        }
        else if ( event != 'EvnDiagPSHosp' && event != 'EvnDiagPSRecep' && ! grid.getSelectionModel().getSelected().get(event + '_id') ) {
            return false;
        }

        var selected_record = grid.getSelectionModel().getSelected();

		if (selected_record.get('EvnClass_SysNick') == 'EvnUslugaPar') {
			return false;
		}
		
        switch ( event ) {
            case 'EvnDrug':
                error = 'При удалении случая использования медикаментов возникли ошибки';
                question = 'Удалить случай использования медикаментов?';
                url = '/?c=EvnDrug&m=deleteEvnDrug';

                params['EvnDrug_id'] = selected_record.get('EvnDrug_id');
                break;

            case 'EvnSection':				
                error = 'При удалении случая движения пациента в стационаре возникли ошибки';
                question = 'Удалить случай движения пациента в стационаре?';
                url = '/?c=Evn&m=deleteEvn';

				if ( getRegionNick() == 'ufa' ) {
					if ( selected_record.get('EvnSection_IsPaid') == 2 ) {
						question = 'Данный случай оплачен, Вы действительно хотите удалить данный случай движения пациента в стационаре?';
					}
				}
				
                params['Evn_id'] = selected_record.get('EvnSection_id');
                break;

            case 'EvnStick':
                var evn_ps_id = base_form.findField('EvnPS_id').getValue();
                var evn_stick_mid = selected_record.get('EvnStick_mid');


                /*
                evnStickType это EvnClass_SysNick


                SQL -----
					case
						when EC.EvnClass_SysNick = 'EvnStick' then 1
						when EC.EvnClass_SysNick = 'EvnStickDop' then 2
						when EC.EvnClass_SysNick = 'EvnStickStudent' then 3
					else 0
						end as evnStickType, -- Вид док-та (код)
				-----

                1 - Выдача больничного листа (EvnStick)
				2 - Дополнительный больничный лист (EvnStickDop)
				3 - Выписка справки учащегося (EvnStickStudent)
				*/


                if ( selected_record.get('evnStickType') == 3 ) {
                    if ( evn_ps_id == evn_stick_mid ) {
                        error = 'При удалении справки учащегося возникли ошибки';
                        question = 'Удалить справку учащегося?';
                    }
                    else {
                        error = 'При удалении связи справки учащегося с текущим документом возникли ошибки';
                        question = 'Удалить связь справки учащегося с текущим документом?';
                    }

                    url = '/?c=Stick&m=deleteEvnStickStudent';

                    params['EvnStickStudent_id'] = selected_record.get('EvnStick_id');
                    params['EvnStickStudent_mid'] = evn_ps_id;
                }
                else {
					error = 'При удалении ЛВН возникли ошибки';
					question = 'Удалить ЛВН?';

                    url = '/?c=Stick&m=deleteEvnStick';

                    params['EvnStick_id'] = selected_record.get('EvnStick_id');
                    params['EvnStick_mid'] = evn_ps_id;
                }
                break;

            case 'EvnUsluga':
                error = 'При удалении услуги возникли ошибки';
                question = 'Удалить услугу?';
                url = '/?c=EvnUsluga&m=deleteEvnUsluga';

                params['class'] = selected_record.get('EvnClass_SysNick');
                params['id'] = selected_record.get('EvnUsluga_id');
                break;

            case 'EvnDiagPSHosp':
            case 'EvnDiagPSRecep':
                error = 'При удалении диагноза возникли ошибки';
                question = 'Удалить диагноз?';
                url = '/?c=EvnDiag&m=deleteEvnDiag';

                params['class'] = 'EvnDiagPS';
                params['id'] = selected_record.get('EvnDiagPS_id');
                break;
			//BOB - 04.09.2018
            case 'EvnReanimatPeriod':
				error = langs('При удалении Реанимационного Периода возникли ошибки!');
				question = langs('Вы действительно хотите удалить реанимационный период?');
				url = '/?c=EvnReanimatPeriod&m=deleteEvnReanimatPeriod';
				params['EvnReanimatPeriod_id'] = selected_record.get('EvnReanimatPeriod_id');
                break;
        }

		var alert = {
			EvnSection: {
				'701': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope) {
						if (buttonId == 'yes') {
							options.ignoreDoc = true;
							scope.deleteEvent(event, options);
						}
					}
				},
				'702': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope) {
						if (buttonId == 'yes') {
							options.ignoreEvnDrug = true;
							scope.deleteEvent(event, options);
						}
					}
				},
				'703': {
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, scope) {
						if (buttonId == 'yes') {
							options.ignoreCheckEvnUslugaChange = true;
							scope.deleteEvent(event, options);
						}
					}
				}
			}
		};

		alert['EvnStick'] = sw.Promed.EvnStick.getDeleteAlertCodes({
			callback: function(options) {
				win.deleteEvent(event, options);
			},
			options: options
		});
		
		//BOB - 21.01.2019  контроль наличия РП
		if(event == 'EvnSection'){	
			if (!options.ignoreReanimatPeriodClose) {
				var that = this;
				Ext.Ajax.request({
					callback: function (opt, success, response) {
						if (success && response.responseText != 'false') {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							//console.log('BOB_response_obj=', response_obj);
							if (response_obj.success == true) {
								options.ignoreReanimatPeriodClose = true;
								that.deleteEvent(event, options);
							} else {
								sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
								return false;
							} 
						}
						else {
							that.formStatus = 'edit';
							sw.swMsg.alert('Ошибка', 'Ошибка при проверке закрытия Реанимационного периода.');
						}
					},
					params: {
						Object_id: selected_record.get('EvnSection_id'),
						Object: 'EvnSection'
					},
					url: '/?c=EvnReanimatPeriod&m=checkBeforeDelEvn'
				});
				return false;
			}
		}		
		//BOB - 21.01.2019
		
		if (options.ignoreDoc) {
			params.ignoreDoc = options.ignoreDoc;
		}

		if (options.ignoreEvnDrug) {
			params.ignoreEvnDrug = options.ignoreEvnDrug;
		}

		if (options.ignoreCheckEvnUslugaChange) {
			params.ignoreCheckEvnUslugaChange = options.ignoreCheckEvnUslugaChange;
		}

		if (options.StickCauseDel_id) {
			params.StickCauseDel_id = options.StickCauseDel_id;
		}
		
		var doDelete = function() {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success == false ) {
							if (response_obj.Alert_Msg) {
								if (response_obj.Alert_Code == 705) {
									getWnd('swStickCauseDelSelectWindow').show({
										countNotPaid: response_obj.countNotPaid,
										existDuplicate: response_obj.existDuplicate,
										callback: function(StickCauseDel_id) {
											if (StickCauseDel_id) {
												options.ignoreQuestion = true;
												options.StickCauseDel_id = StickCauseDel_id;
												this.deleteEvent(event, options);
											}
										}.createDelegate(this)
									});
								} else {
									var a_params = alert[event][response_obj.Alert_Code];
									sw.swMsg.show({
										buttons: a_params.buttons,
										fn: function(buttonId) {
											a_params.fn(buttonId, this);
										}.createDelegate(this),
										msg: response_obj.Alert_Msg,
										icon: Ext.MessageBox.QUESTION,
										title: 'Вопрос'
									});
								}
							} else {
								sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
						} else {
							if (response_obj.IsDelQueue) {
								sw.swMsg.alert('Внимание', 'ЛВН добавлен в очередь на удаление');
								selected_record.set('EvnStick_IsDelQueue', 2);
								selected_record.set('accessType', 'view');
								selected_record.commit();
							} else {
								grid.getStore().remove(selected_record);
							}

							if ( grid.getStore().getCount() == 0 ) {
								grid.getTopToolbar().items.items[1].disable();
								grid.getTopToolbar().items.items[2].disable();
								grid.getTopToolbar().items.items[3].disable();
								LoadEmptyRow(grid);
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
							
							// При удалении движения нужно обновить грид медикаментов
							if (event == 'EvnSection') {
								win.findById('EPSEF_EvnDrugGrid').getStore().load({
									params: { EvnDrug_pid: base_form.findField('EvnPS_id').getValue() }
								});
								win.setPrehospArriveAllowBlank();
								win.getEvnSectionIndexNums();
							}
						}
						
					}
					else {
						sw.swMsg.alert('Ошибка', error);
					}
				}.createDelegate(this),
				params: params,
				url: url
			});
		}.createDelegate(this);


		if (options.ignoreQuestion) {
			doDelete();
		} else {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						options.ignoreQuestion = true;
						doDelete();
					} else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: question,
				title: 'Вопрос'
			});
		}
    },
        
	checkZNO: function(options){
		if(getRegionNick()!='ekb') return;
		var win = this,
			base_form = win.findById('EvnPSEditForm').getForm(),
			person_id = base_form.findField('Person_id'),
			Evn_id = base_form.findField('EvnPS_id');
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Проверка признака на подозрение ЗНО..."});
        loadMask.show();

		var params = new Object();
		params.object = 'EvnPS';

		if ( !Ext.isEmpty(person_id.getValue()) ) {
			params.Person_id = person_id.getValue();
		}
		
		if ( !Ext.isEmpty(Evn_id.getValue()) && Evn_id.getValue()!=0 ) {
			params.Evn_id = Evn_id.getValue();
		}

        Ext.Ajax.request({
            callback: function(opts, success, response) {
                loadMask.hide();

                if ( success ) {
                    var data = Ext.util.JSON.decode(response.responseText);
                    win.lastzno = data.iszno;
                    win.lastznodiag = data.Diag_spid;
                    if(win.lastzno==2 && Ext.isEmpty(base_form.findField('EvnPS_IsZNO').getValue())) {
						win.findById('EPSEF_EvnPS_IsZNOCheckbox').setValue(true);
						if(!Ext.isEmpty(data.Diag_spid)) {
							base_form.findField('Diag_spid').getStore().load({
								callback:function () {
									base_form.findField('Diag_spid').getStore().each(function (rec) {
										if (rec.get('Diag_id') == data.Diag_spid) {
											base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
										}
									});
								},
								params:{where:"where DiagLevel_id = 4 and Diag_id = " + data.Diag_spid}
							});
						}
					}
                }
                else {
                    sw.swMsg.alert('Ошибка', 'Ошибка при определении признака на подозрение ЗНО');
                }
            },
			params: params,
            url: '/?c=Person&m=checkEvnZNO_last'
        });
        win.checkBiopsyDate(options.action);
	},
	
	checkBiopsyDate: function(formAction) {
		if(getRegionNick()!='ekb') return;

		var win = this,
			base_form = win.findById('EvnPSEditForm').getForm(),
			person_id = base_form.findField('Person_id'),
			Evn_id = base_form.findField('EvnPS_id');
			
		if(base_form.findField('EvnPS_IsZNORemove').getValue() == '2') {
			Ext.getCmp('EPSEF_BiopsyDatePanel').show();
			if(formAction=='add' && Ext.isEmpty(base_form.findField('EvnPS_BiopsyDate').getValue()) ) {
				var params = new Object();
				params.object = 'EvnPS';
				params.Person_id = person_id.getValue();
				Ext.Ajax.request({
					url: '/?c=Person&m=getEvnBiopsyDate',
					params: params,
					callback:function (options, success, response) {
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success && response_obj.data) {
								base_form.findField('EvnPS_BiopsyDate').setValue(response_obj.data);
							}
						}
					}
				});
			}
		} else Ext.getCmp('EPSEF_BiopsyDatePanel').hide();
	},

	wardOnSexFilter: function () {
		var base_form = this.findById('EvnPSEditForm').getForm(),
			filterdate = null;
		if (base_form.findField('EvnPS_OutcomeDate').getValue()) {
			filterdate = Ext.util.Format.date(base_form.findField('EvnPS_OutcomeDate').getValue(), 'd.m.Y');
		}
		sw.Promed.LpuSectionWard.filterWardBySex({
			date: filterdate,
			LpuSection_id: base_form.findField('LpuSection_eid').getValue(),
			LpuSectionBedProfileLink_id: base_form.findField('LpuSectionBedProfileLink_id').getValue(),
			Sex_id: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Sex_Code'),
			lpuSectionWardCombo: base_form.findField('LpuSectionWard_id'),
			win: this
		});
	},

	bedProfileByWardFilter: function () {
		var base_form = this.findById('EvnPSEditForm').getForm(),
			filterdate = null;
		if (base_form.findField('EvnPS_OutcomeDate').getValue()) {
			filterdate = Ext.util.Format.date(base_form.findField('EvnPS_OutcomeDate').getValue(), 'd.m.Y');
		}

		var Person_Birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var age;

		if (Person_Birthday) {
			age = swGetPersonAge(Person_Birthday, new Date());
		}

		sw.Promed.LpuSectionBedProfileFilters.filterBedProfileByWard({
			date: filterdate,
			LpuSection_id: base_form.findField('LpuSection_eid').getValue(),
			LpuSectionWard_id: base_form.findField('LpuSectionWard_id').getValue(),
			lpuSectionBedProfileCombo: base_form.findField('LpuSectionBedProfileLink_id'),
			Is_Child: age < 18 ? '2' : '1',
			win: this
		});
	},
	
	setDiagSpidComboDisabled: function() {

		if (!getRegionNick().inlist(['perm', 'msk', 'ufa']) || this.action == 'view') return false;

		var base_form = this.findById('EvnPSEditForm').getForm();
		var diag_spid_combo = base_form.findField('Diag_spid');
		var iszno_checkbox = this.findById('EPSEF_EvnPS_IsZNOCheckbox');

		if (!diag_spid_combo.getValue()) return false;

		Ext.Ajax.request({
			params: {
				Person_id: base_form.findField('Person_id').getValue(),
				Diag_id: diag_spid_combo.getValue()
			},
			success: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				diag_spid_combo.setDisabled(response_obj.isExists == 2);
				iszno_checkbox.setDisabled(response_obj.isExists == 2);
			},
			url: '/?c=MorbusOnkoSpecifics&m=checkMorbusExists'
		});
	},

	changeZNO: function(options){
		if(getRegionNick()!='ekb') return;

		var win = this,
			base_form = win.findById('EvnPSEditForm').getForm(),
			person_id = base_form.findField('Person_id'),
			Evn_id = base_form.findField('EvnPS_id'),
			params = new Object();
		
		params.object = 'EvnPS';
		params.Evn_id = Evn_id.getValue();
		if(Ext.isEmpty(options.isZNO)) return; else params.isZNO = options.isZNO ? 2 : 1;
		
		
		base_form.findField('EvnPS_IsZNORemove').setValue(options.isZNO ? 1 : 2);
		
		win.checkBiopsyDate( !options.isZNO ? 'add' : '' );
		
		if(!Ext.isEmpty(params.Evn_id) && params.Evn_id>0) {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Запись признака ЗНО..."});
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=Person&m=changeEvnZNO',
				params: params,
				callback:function (options, success, response) {
					loadMask.hide();

					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (!response_obj.success) {
							sw.swMsg.alert('Ошибка', 'Ошибка при сохранении признака на подозрение ЗНО');
						}
					}
				}
			});
		}
	},

	checkTrauma:function(){
		var base_form = this.findById('EvnPSEditForm').getForm();
		var traumaField = base_form.findField('PrehospTrauma_id');
		var isAB = true;
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
        var grid = this.findById('EPSEF_EvnSectionGrid');
		var diag_pid = base_form.findField('Diag_pid').getValue();
		if(diag_pid){
			var rec = base_form.findField('Diag_pid').getStore().getById(diag_pid)
			if(rec&&rec.get('Diag_Code')&&(rec.get('Diag_Code')[0].inlist(['T',"S"]))){
				if(rec.get('Diag_Code').substr(0,2)!="T9"||isUfa){
					isAB = false;
				}
			}
		}
		grid.getStore().each(function(rec) {
			if(rec&&rec.get('Diag_Name')){
				var diagGroup = rec.get('Diag_Name')[0];
				if(diagGroup=="S"||diagGroup=="T"){
					if(rec.get('Diag_Name').substr(0,2)!="T9"||isUfa){
						isAB=false;
					}
				}
			}
			
		});
		
		
		traumaField.setAllowBlank(isAB);
		return true;
	},
    checkEvnDirectionAllowBlank: function() {
        var base_form = this.findById('EvnPSEditForm').getForm();
        var evn_ps_dis_dt;
        var last_evn_section_info = this.getEvnSectionInfo('last');
        var lpu_unit_type_id;
        if ( last_evn_section_info.EvnSection_id > 0 ) {
            evn_ps_dis_dt = last_evn_section_info.EvnSection_disDT;
            lpu_unit_type_id = last_evn_section_info.LpuUnitType_id;
        }

		var directionNumAllowBlank = true;
		var directionSetDateAllowBlank = true;

        // проверки по контролю направлений согласно #8881
		
		//данного пункта нет в тз, задача 8881 была реализована овер дохера лет назад, несогласные с этим контролем регионы просто убираем
        if (!getRegionNick().inlist(['ekb', 'perm']) &&
        	base_form.findField('PrehospType_id').getFieldValue('PrehospType_Code') == 1 &&
            base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' &&
            evn_ps_dis_dt > getValidDT('31.03.2012', '') &&
            lpu_unit_type_id == 1 &&
            base_form.findField('EvnPS_IsCont').getFieldValue('YesNo_Code') == 0
		) {
            // Поле <Кем направлен> могут принимать значения только "Отделение ЛПУ" или "Другое ЛПУ"
			var fieldPrehospDirect = base_form.findField('PrehospDirect_id');
            fieldPrehospDirect.getStore().clearFilter();
        	fieldPrehospDirect.getStore().filterBy(function(rec) {
                return rec.get('PrehospDirect_Code').toString().inlist([ '1', '2' ]);
            });
			if(!fieldPrehospDirect.findRecord('PrehospDirect_Code', fieldPrehospDirect.getValue())){
				fieldPrehospDirect.clearValue();
			}
        } else {
            if (base_form.findField('EvnPS_IsCont').getFieldValue('YesNo_Code') == 0) {
                base_form.findField('PrehospDirect_id').getStore().clearFilter();
            }
        }

		if (getRegionNick().inlist(['penza','perm']) && (
			!Ext.isEmpty(base_form.findField('EvnPS_HTMTicketNum').getValue())
			|| !Ext.isEmpty(base_form.findField('EvnPS_HTMBegDate').getValue())
		)) {
			directionNumAllowBlank = false;
		}
		if (getRegionNick().inlist(['perm']) && (
			!Ext.isEmpty(base_form.findField('EvnPS_HTMTicketNum').getValue())
			|| !Ext.isEmpty(base_form.findField('EvnPS_HTMBegDate').getValue())
		)) {
			directionSetDateAllowBlank = false;
		}

		// #145312 Обязательность полей «№ направления» и «Дата направления» при типе госпитализации «1. Планово»
		if (getRegionNick().inlist(['buryatiya', 'ekb', 'ufa', 'pskov']) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick') == 'plan') {
			base_form.findField('EvnDirection_Num').setAllowBlank(false);
			base_form.findField('EvnDirection_setDate').setAllowBlank(false);
		}
		else if(getRegionNick().inlist(['kareliya']) && base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code') == 4){
			base_form.findField('EvnDirection_Num').setAllowBlank(false);
			base_form.findField('EvnDirection_setDate').setAllowBlank(false);
		}else{
			base_form.findField('EvnDirection_Num').setAllowBlank(directionNumAllowBlank);
			base_form.findField('EvnDirection_setDate').setAllowBlank(directionSetDateAllowBlank);
		}

		if (base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code') == 2) {
			this.checkOtherLpuDirection();
		}

		if(getRegionNick() == 'ekb'){
			base_form.findField('MedStaffFact_did').setAllowBlank( !(base_form.findField('PrehospDirect_id').getValue() == 1) );
		}

		this.refreshFieldsVisibility(['PrehospDirect_id']);
    },
	setMKB: function(){
		var parentWin =this
		var base_form = this.findById('EvnPSEditForm').getForm();
		var sex = parentWin.findById('EPSEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'),base_form.findField('EvnPS_setDate').getValue());
		base_form.findField('Diag_pid').setMKBFilter(age,sex,true);
		base_form.findField('Diag_did').setMKBFilter(age,sex,true);
	},
	DiagSSZStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'DiagSSZ_id', type: 'int' },
			{ name: 'Diag_id', type: 'int' }
		],
		key: 'DiagSSZ_id',
		tableName: 'DiagSSZ'
	}),
	isSSZ: function(diag_id) {
		var me = this;
        if (getRegionNick() != 'perm') { return false; }
		var sszfl=false;
		if ( this.DiagSSZStore.getCount() == 0 ) {
			this.DiagSSZStore.load({
				callback: function() {
					if (me.DiagSSZStore.getCount()>0) {
                        me.isSSZ(diag_id);
					}
				}
			});
			return sszfl;
		}
		var Diag_id = diag_id;
		if ( !Ext.isEmpty(Diag_id) ) {
			 this.DiagSSZStore.each(function(rec) {
				 if ( rec.get('Diag_id') == Diag_id ) {
					 sszfl = true;
                     return false;
				 }
                 return true;
			});
		}
		return sszfl;
	},
    /**
     * Контроль ввода диагноза в приемном отделении по ССЗ #35215
     * В форме редактирования поступления в приемное эта проверка не нужна,
     * т.к. при госпитализации из приемного создается движение без диагноза
     * @return {Boolean}
     */
	checkSSZ:function(){
		var win = this;
		var base_form = this.findById('EvnPSEditForm').getForm();
        var grid = this.findById('EPSEF_EvnSectionGrid');
		var priemSSZ = win.isSSZ(base_form.findField('Diag_pid').getValue());
		var ssz = false;
		var priemallow = (getGlobalOptions().check_priemdiag_allow&&getGlobalOptions().check_priemdiag_allow=='1');
		 if ( grid.getStore().getCount() > 0 && grid.getStore().getAt(0).get('EvnSection_id') 
			 && priemallow && priemSSZ ) {
           grid.getStore().each(function(rec) { 
			   if(win.isSSZ(rec.get('Diag_id'))&&rec.get('PayType_id')==1){
				   ssz = true;
                   return false;
               }
               return true;
			});
		 }
		return ssz;
	},
	checkLpuPeriodOMS: function(org_id, date, callback) {
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Проверка периода ОМС..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=LpuPassport&m=hasLpuPeriodOMS',
			params: {Org_oid: org_id, Date: date},
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj && response_obj.success) {
					callback(response_obj.hasLpuPeriodOMS);
				}
			},
			failure: function() {
				loadMask.hide();
			}
		});
	},
	checkOtherLpuDirection: function() {
		var base_form = this.findById('EvnPSEditForm').getForm();

		if (getRegionNick() == 'perm') {
			var org_id = base_form.findField('Org_did').getValue();
			var date = Ext.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'd.m.Y');
			var PrehospType_SysNick = base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick');

			if (Ext.isEmpty(org_id) || PrehospType_SysNick != 'plan') {
				base_form.findField('EvnDirection_Num').setAllowBlank(true);
				base_form.findField('EvnDirection_setDate').setAllowBlank(true);
			} else {
				this.checkLpuPeriodOMS(org_id, date, function(hasLpuPeriodOMS) {
					base_form.findField('EvnDirection_Num').setAllowBlank(!hasLpuPeriodOMS);
					base_form.findField('EvnDirection_setDate').setAllowBlank(!hasLpuPeriodOMS);
				});
			}
		}
	},
	updateLookChange: function() {
		var base_form = this.findById('EvnPSEditForm').getForm();

		for (fieldName in this.lookChange) {
			if (base_form.findField(fieldName)) {
				this.lookChange[fieldName] = base_form.findField(fieldName).getValue();
			}
		}
	},
	isChange: function(fieldName) {
		var base_form = this.findById('EvnPSEditForm').getForm();
		var compare1 = this.lookChange[fieldName];
		var compare2 = base_form.findField(fieldName).getValue();

		if (Ext.isDate(compare1)) {
			compare1 = Ext.util.Format.date(compare1, 'd.m.Y');
		}
		if (Ext.isDate(compare2)) {
			compare2 = Ext.util.Format.date(compare2, 'd.m.Y');
		}

		return (compare1 != compare2);
	},
    doSave: function(options) {
        // options @Object
        // options.print @Boolean Вызывать печать КВС, если true
        // options.callback @Function Функция, выполняемая после сохранения
        // options.ignoreSetDateDieError @Boolean Игнорировать проверку (даты ЛВН = даты КВС и исхода, если исход = умер)
		var wnd = this;
		var me = this;

        if ( this.formStatus == 'save' || this.action == 'view' ) {
            return false;
        }
		
        if ( typeof options != 'object' ) {
            options = new Object();
        }

        this.formStatus = 'save';
		wnd.checkTrauma();
        var base_form = this.findById('EvnPSEditForm').getForm();

		if ( blockedDateAfterPersonDeath('personpanelid', 'EPSEF_PersonInformationFrame', base_form.findField('EvnPS_setDate'), base_form.findField('EvnPS_setDate').getValue(), base_form.findField('EvnPS_setDate').getValue()) ) {
			wnd.formStatus = 'edit';
			base_form.findField('EvnPS_setDate').focus(250, true);
			return false;
		}

		if(getRegionNick().inlist(['vologda','msk','ufa'])){
			var blockPediculos = this.findById('EPSEF_blockPediculos');
			if(
				blockPediculos.isVisible() 
				&& (base_form.findField('isPediculos').getValue() || base_form.findField('isScabies').getValue())
			){

				if(!base_form.findField('Pediculos_isSanitation').getValue()){
					Ext.Msg.alert(langs('Ошибка'), langs('У пациента обнаружено паразитарное заболевание, необходима санитарная обработка'));
					this.formStatus = 'edit';
					return false;
				}
				if(!options.ignorePediculosPrint && base_form.findField('Pediculos_isPrint').getValue() != 2){
					Ext.MessageBox.show({
						title: 'Вопрос',
						msg: 'Распечатать уведомление в СЭС?',
						icon: Ext.MessageBox.QUESTION,
						buttons: {yes: 'Печать', cancel: 'Отмена'},
						fn: function(buttonId, text, obj) {
							this.formStatus = 'edit';
							if ( 'yes' == buttonId ) {
								// var buttomPediculosPrint = this.findById('EPSEF_PediculosPrint');
								// if(buttomPediculosPrint) buttomPediculosPrint.handler();
								options.pediculosPrint = true;
							}
							options.ignorePediculosPrint = true;
							this.doSave(options);
						}.createDelegate(this),
					});
					return false;
				}

			}
		}

		// если сохранение не с целью открытия дочернего окна и это карелия и нет ни одного движения то поля приемное отделение, врач приемного отделения и диагноз приемного отделения должны быть заполнены. (refs #18341)
		var evnSectionGridStore = this.findById('EPSEF_EvnSectionGrid').getStore();
		if (!options.callback && getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' && ( evnSectionGridStore.getCount() == 0 || (evnSectionGridStore.getCount() == 1 && !evnSectionGridStore.getAt(0).get('EvnSection_id')) ) ) {
			if (
				Ext.isEmpty(base_form.findField('LpuSection_pid').getValue()) ||
				(Ext.isEmpty(base_form.findField('Diag_pid').getValue()) && getGlobalOptions().check_priemdiag_allow) ||
				Ext.isEmpty(base_form.findField('MedStaffFact_pid').getValue())
			) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						wnd.formStatus = 'edit';
						base_form.findField('LpuSection_pid').focus(false);
					},
					icon: Ext.Msg.WARNING,
					msg: 'Не введено ни одного движения. Поля "Приемное отделение", "Врач приемного отделения" и "Диагноз приемного отделения" должны быть заполнены.',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		if (this.checkSSZ()){
			sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg:"Диагноз приемного отделения не может быть из списка ССЗ для оплаты по ОМС",
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}

		// #145312 Обязательность полей «№ направления» и «Дата направления» при типе госпитализации «1. Планово»
		// ещё раз установка перед проверкой на всякий случай
		if(getRegionNick().inlist(['ufa','pskov']) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick') == 'plan'){
			base_form.findField('EvnDirection_Num').setAllowBlank(false);
			base_form.findField('EvnDirection_setDate').setAllowBlank(false);
		}

        if ( !base_form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                    this.findById('EvnPSEditForm').getFirstInvalidEl().focus(false);
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}

		// https://redmine.swan.perm.ru/issues/76559 - тут сделано
		// https://redmine.swan.perm.ru/issues/78033 - тут закомментировано
		/*if ( getRegionNick() == 'perm' && base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& base_form.findField('LeaveType_fedid').getFieldValue('LeaveType_Code') == '313'
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('PrehospWaifRefuseCause_id').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Случаи с результатом "313 Констатация факта смерти" не подлежат оплате по ОМС',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}*/

		// https://redmine.swan.perm.ru/issues/42421
		if ( this.findById('EPSEF_PriemLeavePanel').collapsed ) {
			this.findById('EPSEF_PriemLeavePanel').expand();
		}

		//контроль по 182339
		if (
			getRegionNick().inlist(['khak', 'adygeya'])
			&& base_form.findField('EvnPS_IsCont').getValue() == 1 // в поле «Переведён» указано значение «Нет»
			&& base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick') == 'plan' // планово
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' // тип оплаты ОМС
			&& Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue())// не заполнен отказ
			&& (
				Ext.isEmpty(base_form.findField('PrehospDirect_id').getValue())
				|| Ext.isEmpty(base_form.findField('EvnDirection_Num').getValue())
				|| Ext.isEmpty(base_form.findField('EvnDirection_setDate').getValue())
			)
		) {
			var index = this.findById('EPSEF_EvnSectionGrid').getStore().findBy(function(rec) {
				return rec.get('LpuUnitType_SysNick') && rec.get('LpuUnitType_SysNick').inlist(['stac', 'dstac', 'pstac']);
			});
			if (index != -1) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'При плановой госпитализации в круглосуточный стационар или дневной стационар с видом оплаты ОМС и без перевода, ' +
						'поля <Номер направления> и <Дата направления> - обязательны к заполнению, поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ"',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}



		// -------------------------------------------------------------------------------------------------------------
		/* #142106 */
		if(
			getRegionNick().inlist(['penza'])
			&& base_form.findField('MedicalCareFormType_id').getValue() == 2
			&& base_form.findField('LpuSection_eid').getValue()
		){
			var directionNumDate = (!base_form.findField('EvnDirection_Num').getValue() && !base_form.findField('EvnDirection_setDate').getValue()) ? true : false;
			var extraEvnPS = (base_form.findField('EvnPS_IsWithoutDirection').getValue() != 2) ? true : false;
			if(directionNumDate && extraEvnPS){
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Не указаны сведения о направлении. При оказании неотложной помощи обязательно должны быть заполнены поля «№ направления» и «Дата направления», или выбрано электронное направление. Заполните раздел «Кем направлен».',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		// Проверка отсутствия движений при отказе в госпитализации #152365
		if (
			base_form.findField('PrehospWaifRefuseCause_id').getValue()
			&& this.findById('EPSEF_EvnSectionGrid').getStore().getAt(0)
			&& this.findById('EPSEF_EvnSectionGrid').getStore().getAt(0).get('EvnSection_id')
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Невозможно сохранить КВС с отказом от госпитализации при наличии движения. Удалите движения или очистите поле «Отказ» в разделе  «Исход пребывания в приемном отделении».',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		/*
		#119999  Регион: Пенза
		При сохранении КВС со следующими свойствами: «Наличие отказа в приемном отделении» - проверяется значение в
		поле «Форма помощи». Если указана форма помощи «Экстренная», то сообщение об ошибке: «При отказе от госпитализации
		форма помощи не может быть «Экстренной». Необходимо изменить значение в разделе «Исход пребывания в приемном отделении»».
		Изменения не сохраняются, форма редактирования КВС остается открытой.
		 */
		if (
			getRegionNick().inlist(['penza'])
			&& !Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue())
			&& base_form.findField('MedicalCareFormType_id').getValue() == 1
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('MedicalCareFormType_id').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'При отказе от госпитализации форма помощи не может быть «Экстренной». Необходимо изменить значение в разделе «Исход пребывания в приемном отделении».',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		// -------------------------------------------------------------------------------------------------------------


		
		/*
		#PROMEDWEB-13134  Регион: Вологда
		 */
		if (getRegionNick() == 'vologda') {
			base_form.findField('FamilyContact_FIO').setValue(base_form.findField('VologdaFamilyContact_FIO').getValue());
			base_form.findField('FamilyContact_Phone').setValue(base_form.findField('VologdaFamilyContact_Phone').getValue());
		}
		// -------------------------------------------------------------------------------------------------------------



		// -------------------------------------------------------------------------------------------------------------
		/*
		#133580 Если в КВС есть данные об электронном направлении, при сохранении осуществляется проверка: если значение
		в поле Форма помощи КВС НЕ соответствуют значению поля  «Форма помощи» в направлении, то открывается предупреждение
		«Форма помощи в КВС и направлении не соответствует. Продолжить? Да/Нет». При нажатии «Да» КВС сохраняется,
		при нажатии  «Нет» не сохраняется.
		 */
		if(getRegionNick().inlist(['penza']) && ! options.ignoreCheckMedicalCareFormType){
			var EvnDirection_id = base_form.findField('EvnDirection_id').getValue();
			if( ! Ext.isEmpty(EvnDirection_id)){

				var dataEvnDirection = me._getDataEvnDirection(EvnDirection_id);
				var MedicalCareFormType_id = base_form.findField('MedicalCareFormType_id').getValue();

				if( ! Ext.isEmpty(MedicalCareFormType_id) && dataEvnDirection && ! Ext.isEmpty(dataEvnDirection)){
					if(dataEvnDirection.MedicalCareFormType_id && ! Ext.isEmpty(dataEvnDirection.MedicalCareFormType_id)){
						if(MedicalCareFormType_id != dataEvnDirection.MedicalCareFormType_id){
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									this.formStatus = 'edit';
									if ( 'yes' == buttonId ) {
										options.ignoreCheckMedicalCareFormType = 1;
										this.doSave(options);
									} else {
										this.buttons[0].focus();
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: 'Форма помощи в КВС и направлении не соответствует. Продолжить?',
								title: 'Вопрос'
							});
							return false;
						}
					}
				}
			}
		}
		// -------------------------------------------------------------------------------------------------------------


		if (
			!getRegionNick().inlist([ 'buryatiya', 'pskov' ])
			&& Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) && Ext.isEmpty(base_form.findField('LpuSection_eid').getValue())
			&& !Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';

					if ( this.findById('EPSEF_PriemLeavePanel').collapsed ) {
						this.findById('EPSEF_PriemLeavePanel').expand();
					}

					base_form.findField('EvnPS_OutcomeDate').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'При заполненной дате исхода из приемного отделения должен быть заполнен исход пребывания в приемном отделении (отказ) или отделение, куда пациент госпитализирован',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		else if (
			getRegionNick().inlist([ 'buryatiya', 'pskov' ])
			&& Ext.isEmpty(base_form.findField('LeaveType_prmid').getValue())
			&& !Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';

					if ( this.findById('EPSEF_PriemLeavePanel').collapsed ) {
						this.findById('EPSEF_PriemLeavePanel').expand();
					}

					base_form.findField('LeaveType_prmid').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'При заполненной дате исхода из приемного отделения должен быть заполнен исход пребывания в приемном отделении',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		else if (
			getRegionNick().inlist([ 'buryatiya', 'pskov' ])
			&& !Ext.isEmpty(base_form.findField('LeaveType_prmid').getValue())
			&& Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';

					if ( this.findById('EPSEF_PriemLeavePanel').collapsed ) {
						this.findById('EPSEF_PriemLeavePanel').expand();
					}

					base_form.findField('EvnPS_OutcomeDate').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'При заполненном исходе пребывания в приемном отделении должна быть заполнена дата исхода из приемного отделения',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if( !getRegionNick().inlist([ 'kz' ]) && !this.checkEvnSectionDiag()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';

					if ( this.findById('EPSEF_EvnSectionPanel').collapsed ) {
						this.findById('EPSEF_EvnSectionPanel').expand();
					}

				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Сохранение КВС недоступно, так как диагноз не подтвержден. Проверьте указанный диагноз и результаты ЭКГ',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var first_evn_section_info = this.getEvnSectionInfo('first');
        if ( !options.ignoreControlEvnSectionDates && first_evn_section_info.LpuUnitType_SysNick == 'stac' ) {
            // контроль на совпадение дат лечения в стационаре с датами движений (refs #7872)

            var stacBegDate = null;
            var stacEndDate = null;

            var flagControlEvnSectionDates = false;
            var controlEvnStickNumber = "";
			var controlEvnStickType = null;

            var emptyEndDate = false;

            this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec) {
                if (stacBegDate > rec.get('EvnSection_setDate') || stacBegDate == null) {
                    stacBegDate = rec.get('EvnSection_setDate');
                }

                if (stacEndDate < rec.get('EvnSection_disDate') || stacEndDate == null) {
                    if (Ext.isEmpty(rec.get('EvnSection_disDate'))) {emptyEndDate = true;}
                    stacEndDate = rec.get('EvnSection_disDate');
                }
            });

            if (emptyEndDate) {
                stacEndDate = null;
            }

            var checkStacBegDate = '';
            var checkStacEndDate = '';
			var checkBegDate = '';
			var checkEndDate = '';

            this.findById('EPSEF_EvnStickGrid').getStore().each(function(rec) {

                if (rec.get('EvnStick_id')) {

                    if (Ext.isEmpty(rec.get('EvnSection_setDate')) || (rec.get('EvnSection_setDate') > stacBegDate)) {
                        checkStacBegDate = stacBegDate;
                    } else {
                        checkStacBegDate = rec.get('EvnSection_setDate');
                    }

                    if (Ext.isEmpty(rec.get('EvnSection_setDate'))) {
                        checkStacEndDate = stacEndDate;
                    } else if (Ext.isEmpty(rec.get('EvnSection_disDate')) || stacEndDate == null) {
                        checkStacEndDate = null;
                    } else if (rec.get('EvnSection_disDate') < stacEndDate) {
                        checkStacEndDate = stacEndDate;
                    } else {
                        checkStacEndDate = rec.get('EvnSection_disDate');
                    }

					if (rec.get('evnStickType') == 3) {
						checkBegDate = rec.get('EvnStickWorkRelease_begDate');
						checkBegDate = (checkBegDate instanceof Date) ? checkBegDate : Date.parseDate(checkBegDate, 'd.m.Y');

						checkEndDate = rec.get('EvnStickWorkRelease_endDate');
						checkEndDate = (checkEndDate instanceof Date) ? checkEndDate : Date.parseDate(checkEndDate, 'd.m.Y');

						if ( !(checkStacBegDate >= checkBegDate && checkStacBegDate <= checkEndDate && (Ext.isEmpty(checkStacEndDate) || (checkStacEndDate >= checkBegDate && checkStacEndDate <= checkEndDate))) ) {
							flagControlEvnSectionDates = true;
							controlEvnStickNumber = rec.get('EvnStick_Num');
							controlEvnStickType = rec.get('evnStickType');
						}

					} else {
						checkBegDate = rec.get('EvnStick_stacBegDate');
						checkBegDate = (checkBegDate instanceof Date) ? checkBegDate : Date.parseDate(checkBegDate, 'd.m.Y');

						checkEndDate = rec.get('EvnStick_stacEndDate');
						checkEndDate = (checkEndDate instanceof Date) ? checkEndDate : Date.parseDate(checkEndDate, 'd.m.Y');

						if (
							(!Ext.isEmpty(checkBegDate) && !Ext.isEmpty(checkStacBegDate) && checkBegDate.toString() != checkStacBegDate.toString())
							|| (!Ext.isEmpty(checkBegDate) && Ext.isEmpty(checkStacBegDate))
							|| (Ext.isEmpty(checkBegDate) && !Ext.isEmpty(checkStacBegDate))
							|| (!Ext.isEmpty(checkEndDate) && !Ext.isEmpty(checkStacEndDate) && checkEndDate.toString() != checkStacEndDate.toString())
							|| (!Ext.isEmpty(checkEndDate) && Ext.isEmpty(checkStacEndDate))
							|| (Ext.isEmpty(checkEndDate) && !Ext.isEmpty(checkStacEndDate))
							|| (Ext.isEmpty(checkStacEndDate) && Ext.isEmpty(checkEndDate))
						) {
							flagControlEvnSectionDates = true;
							controlEvnStickNumber = rec.get('EvnStick_Num');
							controlEvnStickType = rec.get('evnStickType');
						}
					}
                }
            });

            if (flagControlEvnSectionDates) {
				var msg = '';
				if (controlEvnStickType == 3) {
					msg = 'Период лечения в движениях связных КВС не находится в рамках дат освобождения от занятий ('+controlEvnStickNumber+'), Продолжить?';
				} else {
					msg = 'Период лечения в стационаре в ЛВН ('+controlEvnStickNumber+') не совпадает с данными движений связанных КВС, Продолжить?';
				}

                sw.swMsg.show({
                    buttons: Ext.Msg.YESNO,
                    fn: function(buttonId, text, obj) {
                        this.formStatus = 'edit';

                        if ( 'yes' == buttonId ) {
                            options.ignoreControlEvnSectionDates = true;
                            this.doSave(options);
                        }
                        else {
                            this.buttons[0].focus();
                        }
                    }.createDelegate(this),
                    icon: Ext.MessageBox.QUESTION,
                    msg: msg,
                    title: 'Вопрос'
                });
                return false;
            }
        }

		var evnps_setdate = base_form.findField('EvnPS_setDate').getValue();
		var Person_Birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		if (evnps_setdate && Person_Birthday) {
			var age = swGetPersonAge(Person_Birthday, evnps_setdate);
			this.childPS = (this.childPS||age===0)?true:false;
			if (!options.ignoreLpuSectionAgeCheck && ((base_form.findField('LpuSection_pid').getFieldValue('LpuSectionAge_id') == 1 && age <= 17) || (base_form.findField('LpuSection_pid').getFieldValue('LpuSectionAge_id') == 2 && age >= 18))) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreLpuSectionAgeCheck = true;
							this.doSave(options);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'Возрастная группа отделения не соответствуют возрасту пациента. Продолжить?',
					title: 'Вопрос'
				});
				
				return false;
			}
		}

        var index, params = new Object();

		params.MedPersonal_did = base_form.findField('MedStaffFact_did').getFieldValue('MedPersonal_id');
		params.MedPersonal_pid = base_form.findField('MedStaffFact_pid').getFieldValue('MedPersonal_id');

        if ( base_form.findField('EvnDirection_Num').disabled ) {
            params.EvnDirection_Num = base_form.findField('EvnDirection_Num').getRawValue();
        }

        if ( base_form.findField('Org_did').disabled ) {
            params.Org_did = base_form.findField('Org_did').getValue();
        }

        if ( base_form.findField('LpuSection_did').disabled ) {
            params.LpuSection_did = base_form.findField('LpuSection_did').getValue();
        }

        if ( base_form.findField('MedStaffFact_TFOMSCode').disabled ) {
            params.MedStaffFact_TFOMSCode = base_form.findField('MedStaffFact_TFOMSCode').getValue();
        }

        if ( base_form.findField('PrehospDirect_id').disabled ) {
            params.PrehospDirect_id = base_form.findField('PrehospDirect_id').getValue();
        }

		if ( base_form.findField('EvnPS_IsWithoutDirection').disabled ) {
			params.EvnPS_IsWithoutDirection = base_form.findField('EvnPS_IsWithoutDirection').getValue();
		}

 		if ( base_form.findField('LeaveType_fedid').disabled ) {
			params.LeaveType_fedid = base_form.findField('LeaveType_fedid').getValue();
		}

 		if ( base_form.findField('ResultDeseaseType_fedid').disabled ) {
			params.ResultDeseaseType_fedid = base_form.findField('ResultDeseaseType_fedid').getValue();
		}

 		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		if ( base_form.findField('Diag_spid').disabled ) {
			params.Diag_spid = base_form.findField('Diag_spid').getValue();
		}

        params.EvnDirection_setDate = Ext.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'd.m.Y');
		var prehosp_direct_code = Number(base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code'));
		var tmp_bool = (prehosp_direct_code.inlist([1,2]) && base_form.findField('EvnDirection_id').getValue() > 0 && !base_form.findField('Diag_did').getValue());

        if (tmp_bool) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                    base_form.findField('Diag_did').focus(false);
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: 'При выбранном направлении поле "Основной диагноз направившего учреждения" обязательно для заполнения',
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        tmp_bool = (getGlobalOptions().check_priemdiag_allow
            && !Ext.isEmpty(base_form.findField('LpuSection_pid').getValue())
            && Ext.isEmpty(base_form.findField('Diag_pid').getValue())
        );
        if ( tmp_bool ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                    base_form.findField('Diag_pid').focus(false);
					base_form.findField('Diag_pid').setAllowBlank(false);
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: 'При выбранном приемном отделении поле "Основной диагноз приемного отделения" обязательно для заполнения',
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        var diag_name;
        var evn_ps_dis_dt;

        var last_evn_section_info = this.getEvnSectionInfo('last');
        var leave_type_id;
        var leave_type_code;
        var leave_type_name;
        var lpu_section_name, med_personal_fio;
		var pay_type_name;
        var evn_ps_outcome_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnPS_OutcomeDate').getValue(), 'd.m.Y'), base_form.findField('EvnPS_OutcomeTime').getValue() ? base_form.findField('EvnPS_OutcomeTime').getValue() : '');
        var evn_ps_set_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnPS_setTime').getValue() ? base_form.findField('EvnPS_setTime').getValue() : '');
        var lpu_unit_type_id;

		if ( !Ext.isEmpty(evn_ps_outcome_dt) && evn_ps_outcome_dt < evn_ps_set_dt ) {
			var LpuSectionPriem_Name = base_form.findField('LpuSection_pid').getFieldValue('LpuSection_Name');
			this.formStatus = 'edit';
			sw.swMsg.alert(
				langs('Ошибка'),
				langs('Дата и время поступления в стационар') + ' ' + evn_ps_set_dt.format('d.m.Y H:i') + ' ' + langs('позже даты и времени исхода пребывания в приемном отделении') + ' ' + LpuSectionPriem_Name + ' ' + evn_ps_outcome_dt.format('d.m.Y H:i'),
				function() {
					base_form.findField('EvnPS_OutcomeDate').focus(false);
				}
			);
			return false;
		}

        if ( !options.ignoreSetOutDT&&!Ext.isEmpty(evn_ps_outcome_dt) && (evn_ps_outcome_dt.getTime()-evn_ps_set_dt.getTime())>86400000*(getRegionNick() == 'penza' ? 3 : 1) ) {
			this.formStatus = 'edit';
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					//options.ignoreSetOutDT = true;
					//this.doSave(options);
					base_form.findField('EvnPS_OutcomeDate').focus(false);
                }.createDelegate(this),
                icon: Ext.Msg.ERROR,
                msg: 'Дата и время поступления в стационар '+evn_ps_set_dt.format('d.m.Y H:i')+' раньше даты исхода из приемного отделения '+evn_ps_outcome_dt.format('d.m.Y H:i')+' больше чем на ' + (getRegionNick() == 'penza' ? '3 суток' : 'сутки') + '.',
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }
        if ( evnSectionGridStore.getCount() > 0 && !(evnSectionGridStore.getCount() == 1 && !evnSectionGridStore.getAt(0).get('EvnSection_id')) && !Ext.isEmpty(base_form.findField('LpuSection_pid').getValue()) && Ext.isEmpty(evn_ps_outcome_dt) && getRegionNick() == 'kareliya' ) {
        	this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('EvnPS_OutcomeDate').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: 'При заполненном приемном отделении и существующем движении поле Дата исхода обязательно для заполнения!',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        if ( last_evn_section_info.EvnSection_id > 0 ) {
            evn_ps_dis_dt = last_evn_section_info.EvnSection_disDT;
            leave_type_id = last_evn_section_info.LeaveType_id;
            leave_type_code = last_evn_section_info.LeaveType_Code;
            leave_type_name = last_evn_section_info.LeaveType_Name;
            lpu_unit_type_id = last_evn_section_info.LpuUnitType_id;
            pay_type_name = last_evn_section_info.PayType_Name;

            index = this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec) {
                if ( rec.get('EvnSection_id') == last_evn_section_info.EvnSection_id ) {
                    diag_name = rec.get('Diag_Name');
                    lpu_section_name = rec.get('LpuSection_Name');
                    med_personal_fio = rec.get('MedPersonal_Fio');
                }
            });
        }

		tmp_bool = (first_evn_section_info.EvnSection_setDT && typeof first_evn_section_info.EvnSection_setDT == 'object');

		if ( tmp_bool ) {
			if ( first_evn_section_info.EvnSection_setDT.getTime() < evn_ps_set_dt.getTime() ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(
					langs('Ошибка'),
					langs('Дата и время поступления в стационар') + ' ' + evn_ps_set_dt.format('d.m.Y H:i') + ' ' + langs('позже даты и времени поступления в отделение') + ' ' + first_evn_section_info.LpuSection_Name + ' ' + first_evn_section_info.EvnSection_setDT.format('d.m.Y H:i'),
					function() {
						base_form.findField('EvnPS_setDate').focus(false);
					}
				);
				return false;
			}
			else if (!options.ignoreOutProfilDT && (first_evn_section_info.EvnSection_setDT.getTime() - evn_ps_set_dt.getTime()) > 86400000 ) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreOutProfilDT = true;
							this.doSave(options);
						}else{
							base_form.findField('EvnPS_setDate').focus(false);
						}
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Дата поступления в стационар '+evn_ps_set_dt.format('d.m.Y')+' раньше даты поступления в отделение '+first_evn_section_info.LpuSection_Name+' '+first_evn_section_info.EvnSection_setDT.format('d.m.Y')+' больше чем на сутки. Продолжить?',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

        var isUfa = (getRegionNick() == 'ufa');
        var isPerm = (getRegionNick() == 'perm');
        var payTypeArray = ['oms'];
        // По задаче #5536 + #6270
        // Если заполнен "Исход госпитализации" и "Тип госпитализации" = Плановая
        // TODO: Второе правильное условие: base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick') == 'plan'
        // Поскольку требуется проверка на тип стационара, которая подразумевает обращение к базе - убрал эту проверку на сервер полностью
		tmp_bool = (leave_type_id && //в последнем движении есть исход госпитализации
			(base_form.findField('EvnPS_IsCont').getValue()==1) && //Переведен - нет
			base_form.findField('PrehospType_id').getFieldValue('PrehospType_Code') == 1 && //Тип госпитализации - планово
			base_form.findField('PrehospDirect_id').getValue() != 1 && //Кем направле - НЕ отделение МО
			base_form.findField('PayType_id').getFieldValue('PayType_SysNick').inlist(payTypeArray) && //Вид оплаты - определяется массивом
			base_form.findField('EvnDirection_Num').getRawValue().length==0 //не заполнено поле направление
		);
        //  #18677 Кроме Уфы
        if ( tmp_bool && !isUfa /*&& (!isPerm || prehosp_direct_code != 2)*/ ) {
            // Если направлен другим ЛПУ, то направление д.б. электронным (- данное условие не меняется), а в остальных случаях "ручной ввод".
            // if ( base_form.findField('PrehospDirect_id').getFieldValue('') == 'lpu' ) {}
            // TODO: Данное условие в рамках задачи для нас непринципиально, поскольку проверяем мы на наличие любого направления
			params.checkEvnPSPersonNewbornBirthSpecStacConnect = 1;
            // Контроль на наличие направления
            /*if (base_form.findField('EvnDirection_Num').getRawValue().length==0 &&!this.childPS ) {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    fn: function() {
                        this.formStatus = 'edit';
                        base_form.findField('PrehospDirect_id').focus(false);
                    }.createDelegate(this),
                    icon: Ext.Msg.WARNING,
                    msg: 'В случае, если госпитализация плановая, должны быть заполнены данные о направлении.',
                    title: ERR_INVFIELDS_TIT
                });
                return false;
            }*/
        }


        // проверки по контролю направлений согласно #8881
        if (base_form.findField('PrehospType_id').getFieldValue('PrehospType_Code') == 1 &&
            base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' &&
            evn_ps_dis_dt > getValidDT('31.03.2012', '') &&
            lpu_unit_type_id == 1 &&
            base_form.findField('EvnPS_IsCont').getValue() == 1
            ) {
            if ( !options.ignoreControlEvnDate21Day ) {
				var EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue();
                // Если минимальная Дата поступления из движений КВС минус дата направления > 21 дня то "Случай, в котором дата направления ранее даты начала лечения более чем на 21 день, может быть не оплачен по ОМС. Продолжить сохранение?"
                tmp_bool = (first_evn_section_info.EvnSection_setDT && EvnDirection_setDate && typeof EvnDirection_setDate == 'object' && typeof first_evn_section_info.EvnSection_setDT == 'object' && first_evn_section_info.EvnSection_setDT.getTime() > EvnDirection_setDate.add(Date.DAY, 21).getTime() );
                if (tmp_bool) {
                    sw.swMsg.show({
                        buttons: Ext.Msg.YESNO,
                        fn: function(buttonId, text, obj) {
                            this.formStatus = 'edit';

                            if ( 'yes' == buttonId ) {
                                options.ignoreControlEvnDate21Day = true;
                                this.doSave(options);
                            }
                            else {
                                this.buttons[0].focus();
                            }
                        }.createDelegate(this),
                        icon: Ext.MessageBox.QUESTION,
                        msg: 'Случай, в котором дата направления ранее даты начала лечения более чем на 21 день, отмечается для проведения экспертизы СМО. Продолжить сохранение?',
                        title: 'Вопрос'
                    });
                    return false;
                }
            }
        }

		// проверки по контролю дат госпитализации и направления согласно #110233
		// проверка нужна для всех, #137188
		var
			EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue(),
			HospDate = base_form.findField('EvnPS_setDate').getValue();

		// Если дата направления больше даты госпитализации
		if ( (getRegionNick() === 'ekb' ? getOthersOptions().checkEvnDirectionDate : true) && typeof HospDate == 'object' && typeof EvnDirection_setDate == 'object' && EvnDirection_setDate.getTime() > HospDate.getTime() ) {
			this.formStatus = 'edit';
			Ext.Msg.alert(langs('Ошибка'), langs('Дата выписки направления позже даты поступления пациента в стационар. Дата направления должна быть раньше или совпадать с датой начала лечения. Проверьте дату направления и/или дату госпитализации'));
			return false;
		}
		
        if(getRegionNick() == 'kareliya'){
        	var dateOutcomeDate = base_form.findField('EvnPS_OutcomeDate').getValue();
        	var medicalCareFormType = base_form.findField('MedicalCareFormType_id').getValue();
        	if(!Ext.isEmpty(dateOutcomeDate) && Ext.isEmpty(medicalCareFormType)) {
        		this.formStatus = 'edit';
        		sw.swMsg.alert('Сообщение', 
        			'При заполненной дате исхода из приемного отделения должны быть заполнены: '+
        			'1. исход пребывания в приемном отделении (отказ) или отделение, куда пациент госпитализирован; '+
        			'2.	форма помощи'
        		);
            	return false;
        	}
        	
        	if(
        		base_form.findField('EvnPS_IsCont').getValue()==1 
        		&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' 
        		&& base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick') == 'plan'
        		&& !base_form.findField('PrehospWaifRefuseCause_id').getValue()
        		&& (!base_form.findField('PrehospDirect_id').getValue() || !base_form.findField('EvnDirection_Num').getValue() || !base_form.findField('EvnDirection_setDate').getValue())
        	){
        		this.formStatus = 'edit';
        		sw.swMsg.alert('Сообщение', 
        			'При плановой госпитализации в круглосуточный стационар с видом оплаты ОМС и без перевода, начиная с 01.04.2012 поля <Номер направления> и <Дата направления> - обязательны к заполнению,'+
        			' поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ" или "Военкомат"» '
        		);
        		wnd.formStatus = 'edit';
            	return false;
        	}

        	var comboOrgDid = base_form.findField('Org_did');
        	var comboOrgDidNickValue = comboOrgDid.getFieldValue('OrgType_SysNick');
        	if((comboOrgDidNickValue && comboOrgDidNickValue == 'lpu') || base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code') == 4){
        		if(!base_form.findField('EvnDirection_Num').getValue() || !base_form.findField('EvnDirection_setDate').getValue()){
	        		sw.swMsg.alert('Сообщение', 
						'Поля «№ направления» и «Дата направления» обязательны для заполнения'
					);
					base_form.findField('EvnDirection_Num').setAllowBlank(false);
					base_form.findField('EvnDirection_setDate').setAllowBlank(false);
					wnd.formStatus = 'edit';
					return false;
				}
        	}
        }

        params.Diag_did = base_form.findField('Diag_did').getValue();
		params.childPS = this.childPS;
        if ( evn_ps_dis_dt ) {
            params.EvnPS_disDate = Ext.util.Format.date(evn_ps_dis_dt, 'd.m.Y');
            params.EvnPS_disTime = Ext.util.Format.date(evn_ps_dis_dt, 'H:i');
        }

        if ( base_form.findField('EvnPS_IsPLAmbulance').disabled ) {
            params.EvnPS_IsPLAmbulance = base_form.findField('EvnPS_IsPLAmbulance').getValue();
        }

        //Если "Госпитализирован в" и Дата исхода не совпадает с отделением и датой поступления в первом движении выводим предупреждение
        if (!options.ignoreOutcomeAndAction && !Ext.isEmpty(base_form.findField('LpuSection_eid').getValue()) && !Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())) {
            Ext.Ajax.request({
                callback: function(opt, success, response) {
                    if ( success ) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        if (response_obj[0] && response_obj[0].ignoreOutcomeAndAction) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.YESNO,
                                fn: function(buttonId, text, obj) {
                                    wnd.formStatus = 'edit';

                                    if ( 'yes' == buttonId ) {
                                        options.ignoreOutcomeAndAction = true;
                                        wnd.doSave(options);
                                    } else {
                                        wnd.buttons[0].focus();
                                    }
                                },
                                icon: Ext.MessageBox.QUESTION,
                                msg: 'Обнаружено первое движение с отличающимися отделением и/или датой исхода. Продолжить сохранение?',
                                title: 'Вопрос'
                            });
                        } else {
                            wnd.formStatus = 'edit';
                            options.ignoreOutcomeAndAction = true;
                            wnd.doSave(options);
                        }
                    }
                    else {
                        wnd.formStatus = 'edit';
                        sw.swMsg.alert('Ошибка', 'Ошибка при проверке даты и отделения в движении.');
                    }
                },
                params: {
                    EvnPS_id: base_form.findField('EvnPS_id').getValue(),
                    LpuSection_eid: base_form.findField('LpuSection_eid').getValue(),
                    EvnPS_OutcomeDate: Ext.util.Format.date(base_form.findField('EvnPS_OutcomeDate').getValue(), 'd.m.Y'),
                    EvnPS_OutcomeTime: base_form.findField('EvnPS_OutcomeTime').getValue()
                },
                url: '/?c=EvnPS&m=checkEvnPSSectionAndDateEqual'
            });
            return false;
        }

		//yl:180378 контроль даты сообщения родственнику
		var evnps_setdate = base_form.findField("EvnPS_setDate").getValue();//поступление
		var evnps_settime = base_form.findField("EvnPS_setTime").getValue();
		var evnps_outcomedate = base_form.findField("EvnPS_OutcomeDate").getValue();//отказ
		var evnps_outcometime = base_form.findField("EvnPS_OutcomeTime").getValue();
		var evnps_familydate = base_form.findField("FamilyContact_msgDate").getValue();//родственники
		var evnps_familytime = base_form.findField("FamilyContact_msgTime").getValue();

		if (evnps_familydate) {
			if(!evnps_familytime)evnps_familytime="00:00";
			var familyDT = Date.parseDate(Ext.util.Format.date(evnps_familydate, "d.m.Y") + " " + evnps_familytime, "d.m.Y H:i");//Дата сообщения родственнику

			//yl: Если Дата сообщения родственнику меньше Дата поступления
			if(evnps_setdate){
				if(!evnps_settime)evnps_settime="00:00";
				var setDT = Date.parseDate(Ext.util.Format.date(evnps_setdate, "d.m.Y") + " " + evnps_settime, "d.m.Y H:i");//Дата поступления
				if (familyDT < setDT) {
					this.formStatus = "edit";
					Ext.Msg.alert(langs("Ошибка"), langs("Дата и время сообщения родственнику не может быть раньше даты и времени поступления"));
					return false;
				}
			}

			//yl: выбран отказ и установлено время отказа
			if(base_form.findField("PrehospWaifRefuseCause_id").getValue() && evnps_outcomedate){
				if(!evnps_outcometime)evnps_outcometime="00:00";
				var outDT = Date.parseDate(Ext.util.Format.date(evnps_outcomedate, "d.m.Y")+" "+evnps_outcometime,"d.m.Y H:i");//Дата отказа
				if (familyDT > outDT) {
					this.formStatus = "edit";
					Ext.Msg.alert(langs("Ошибка"), langs("Дата и время сообщения родственнику должны быть меньше даты и времени исхода пребывания в приемном отделении"));
					return false;
				}
			}

			//yl: Если дата и время сообщения родственнику больше даты и времени выписки в последнем движении в рамках КВС, то выводится сообщение об ошибке:
			if ((grid = this.findById("EPSEF_EvnSectionGrid")) && (store = grid.getStore()) && store.getCount() > 0) {
				var disDT_last;//дата последней выписки
				store.each(function (record) {
					if (evnsection_disdate = record.get("EvnSection_disDate")) {
						if (!(evnsection_distime = record.get("EvnSection_disTime"))) {
							evnsection_distime = "00:00";
						}
						var disDT = Date.parseDate(Ext.util.Format.date(evnsection_disdate, "d.m.Y") + " " + evnsection_distime, "d.m.Y H:i");//Дата выписки
						if (!disDT_last || disDT_last < disDT) {
							disDT_last = disDT;
						}
					}
				});
				if (disDT_last && familyDT > disDT_last) {
					this.formStatus = "edit";
					Ext.Msg.alert(langs("Ошибка"), langs("Дата и время сообщения родственнику должны быть меньше даты и времени выписки в последнем движении"));
					return false;
				}
			}
		}

		//yl:180378 контроль даты сообщения родственнику
		var evnps_setdate = base_form.findField("EvnPS_setDate").getValue();//поступление
		var evnps_settime = base_form.findField("EvnPS_setTime").getValue();
		var evnps_outcomedate = base_form.findField("EvnPS_OutcomeDate").getValue();//отказ
		var evnps_outcometime = base_form.findField("EvnPS_OutcomeTime").getValue();
		var evnps_familydate = base_form.findField("FamilyContact_msgDate").getValue();//родственники
		var evnps_familytime = base_form.findField("FamilyContact_msgTime").getValue();

		if (evnps_familydate) {
			if(!evnps_familytime)evnps_familytime="00:00";
			var familyDT = Date.parseDate(Ext.util.Format.date(evnps_familydate, "d.m.Y") + " " + evnps_familytime, "d.m.Y H:i");//Дата сообщения родственнику

			//yl: Если Дата сообщения родственнику меньше Дата поступления
			if(evnps_setdate){
				if(!evnps_settime)evnps_settime="00:00";
				var setDT = Date.parseDate(Ext.util.Format.date(evnps_setdate, "d.m.Y") + " " + evnps_settime, "d.m.Y H:i");//Дата поступления
				if (familyDT < setDT) {
					this.formStatus = "edit";
					Ext.Msg.alert(langs("Ошибка"), langs("Дата и время сообщения родственнику не может быть раньше даты и времени поступления"));
					return false;
				}
			}

			//yl: выбран отказ и установлено время отказа
			if(base_form.findField("PrehospWaifRefuseCause_id").getValue() && evnps_outcomedate){
				if(!evnps_outcometime)evnps_outcometime="00:00";
				var outDT = Date.parseDate(Ext.util.Format.date(evnps_outcomedate, "d.m.Y")+" "+evnps_outcometime,"d.m.Y H:i");//Дата отказа
				if (familyDT > outDT) {
					this.formStatus = "edit";
					Ext.Msg.alert(langs("Ошибка"), langs("Дата и время сообщения родственнику должны быть меньше даты и времени исхода пребывания в приемном отделении"));
					return false;
				}
			}

			//yl: Если дата и время сообщения родственнику больше даты и времени выписки в последнем движении в рамках КВС, то выводится сообщение об ошибке:
			if ((grid = this.findById("EPSEF_EvnSectionGrid")) && (store = grid.getStore()) && store.getCount() > 0) {
				var disDT_last;//дата последней выписки
				store.each(function (record) {
					if (evnsection_disdate = record.get("EvnSection_disDate")) {
						if (!(evnsection_distime = record.get("EvnSection_disTime"))) {
							evnsection_distime = "00:00";
						}
						var disDT = Date.parseDate(Ext.util.Format.date(evnsection_disdate, "d.m.Y") + " " + evnsection_distime, "d.m.Y H:i");//Дата выписки
						if (!disDT_last || disDT_last < disDT) {
							disDT_last = disDT;
						}
					}
				});
				if (disDT_last && familyDT > disDT_last) {
					this.formStatus = "edit";
					Ext.Msg.alert(langs("Ошибка"), langs("Дата и время сообщения родственнику должны быть меньше даты и времени выписки в последнем движении"));
					return false;
				}
			}
		}

		if (getRegionNick() == 'msk' && !options.pneumoAlerted) {
			if (wnd.checkPneumoDiag()) {
				pneumoAlert(function() {
					options.pneumoAlerted = true;
					wnd.formStatus = 'edit';
					wnd.doSave(options);
				});
				return false;
			}
		}
		if (getRegionNick() !== 'kz') {
			params.Diag_eid = base_form.findField('Diag_eid').getValue();
		}

        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение карты выбывшего из стационара..."});
        loadMask.show();

        // Необходимо, что бы ЛВН закрывался датой смерти пациента.
        // ajax запрос на проверку + калбэк.
        var checkparams = params;
        checkparams.EvnPS_id = base_form.findField('EvnPS_id').getValue();
        checkparams.LeaveType_id = leave_type_id;

		/*
		* this.findById('EPSEF_EvnSectionGrid').getStore().getCount()  && !grid.getStore().getAt(0).get('EvnSection_id')
		*/
		/*
		* Отправляем флаг "addEvnSection" для создания пустого движения в том случае,
		* Когда КВС добавляется из АРМа стационара по нажатию кнопки "добавить пациента" (this.form_mode == 'arm_stac_add_patient')
		* Для Уфы когда КВС добавляется из Журнала госпитализаций (this.form_mode == 'dj_hosp')
		*
		* Флаг не должен отправляться,
		* Когда КВС сохраняется перед открытием дочернего окна
         * Когда в КВС было добавлено движение
         * Когда в КВС было указан отказ в госпитализации
		*/
		var evnsection_grid = this.findById('EPSEF_EvnSectionGrid');
		if ( 
			this.params.addEvnSection
			&& !options.callback
			&& ( 0 == evnsection_grid.getStore().getCount() || !evnsection_grid.getStore().getAt(0).get('EvnSection_id') )
            && !base_form.findField('PrehospWaifRefuseCause_id').getValue()
		) {
			params.addEvnSection = 1;
			params.LpuSection_id = this.params.LpuSection_id || null;
			params.MedPersonal_id = this.params.MedPersonal_id || null;
			params.MedStaffFact_id = this.params.MedStaffFact_id || null;
        } else {
			params.addEvnSection = 0;
		}

		if ( this.findById('EPSEF_EvnPS_IsZNOCheckbox').getValue() == true ) {
			base_form.findField('EvnPS_IsZNO').setValue(2);
		}
		else {
			base_form.findField('EvnPS_IsZNO').setValue(1);
		}


		Ext.Ajax.request(
            {
                url: '/?c=EvnPS&m=CheckEvnPSDie',
                params: checkparams,
                callback: function(opt, scs, response)
                {
                    if ( !options.ignoreSetDateDieError ) {

                        if (scs)
                        {
                            if ( response.responseText.length > 0 )
                            {
                                var result = Ext.util.JSON.decode(response.responseText);
                                if (!result.success)
                                {
                                    sw.swMsg.show({
                                        buttons: Ext.Msg.YESNO,
                                        fn: function(buttonId, text, obj) {
                                            this.formStatus = 'edit';

                                            if ( 'yes' == buttonId ) {
                                                options.ignoreSetDateDieError = true;
                                                this.doSave(options);
                                            }
                                            else {
                                                loadMask.hide();
                                                this.buttons[0].focus();
                                            }
                                        }.createDelegate(this),
                                        icon: Ext.MessageBox.QUESTION,
                                        msg: 'Исход госпитализации и исход ЛВН не совпадают, либо отличаются даты смерти в ЛВН и КВС, Продолжить?',
                                        title: 'Вопрос'
                                    });
                                    return false;
                                }
                            }
                        }
                    }

                    if ( base_form.findField('LpuSection_eid').disabled ) {
                        params.LpuSection_eid = base_form.findField('LpuSection_eid').getValue();
                    }
                    if ( options && typeof options.callback == 'function') {
                        params.isAutoCreate = 1;
                    }
					if ( options && !Ext.isEmpty(options.ignoreUslugaComplexTariffCountCheck)) {
						params.ignoreUslugaComplexTariffCountCheck = options.ignoreUslugaComplexTariffCountCheck === 1 ? 1 : 0;
					}

        			params.vizit_direction_control_check = (options && !Ext.isEmpty(options.vizit_direction_control_check) && options.vizit_direction_control_check === 1) ? 1 : 0;
					params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
					params.ignoreEvnPSDoublesCheck = (!Ext.isEmpty(options.ignoreEvnPSDoublesCheck) && options.ignoreEvnPSDoublesCheck === 1) ? 1 : 0;
					params.ignoreEvnPSTimeDeseaseCheck = (!Ext.isEmpty(options.ignoreEvnPSTimeDeseaseCheck) && options.ignoreEvnPSTimeDeseaseCheck === 1) ? 1 : 0;
					params.ignoreEvnPSHemoDouble = (!Ext.isEmpty(options.ignoreEvnPSHemoDouble) && options.ignoreEvnPSHemoDouble === 1) ? 1 : 0;
					params.ignoreEvnPSHemoLong = (!Ext.isEmpty(options.ignoreEvnPSHemoLong) && options.ignoreEvnPSHemoLong === 1) ? 1 : 0;
					params.ignoreMorbusOnkoDrugCheck = (!Ext.isEmpty(options.ignoreMorbusOnkoDrugCheck) && options.ignoreMorbusOnkoDrugCheck === 1) ? 1 : 0;
					params.ignoreCheckMorbusOnko = (!Ext.isEmpty(options.ignoreCheckMorbusOnko) && options.ignoreCheckMorbusOnko === 1) ? 1 : 0;
					if(getRegionNick().inlist(['vologda','msk','ufa']) && Ext.getCmp('EPSEF_blockPediculos').isVisible()){
						params.Pediculos_id =  base_form.findField('Pediculos_id').getValue();
						params.Pediculos_isPrint = (options.pediculosPrint) ? 2 : base_form.findField('Pediculos_isPrint').getValue();
					}
					params.checkMoreThanOneEvnPSToEvnDirection = 1; //инициализация проверки по задаче 183005
                    base_form.submit({
                        failure: function(result_form, action) {
                            this.formStatus = 'edit';
                            loadMask.hide();

                            if ( action.result ) {
								if ( action.result.Alert_Msg ) {
									var msg = getMsgForCheckDoubles(action.result);

									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function(buttonId, text, obj) {
											if ( buttonId == 'yes' ) {
												if (action.result.Error_Code == 102) {
													options.ignoreUslugaComplexTariffCountCheck = 1;
												}
												if (action.result.Error_Code == 106) {
													options.ignoreMorbusOnkoDrugCheck = 1;
												}
												if (action.result.Error_Code == 112) {
													options.vizit_direction_control_check = 1;
												}
												if (action.result.Error_Code == 113) {
													options.ignoreEvnPSDoublesCheck = 1;
												}
												if (action.result.Error_Code == 109) {
													options.ignoreParentEvnDateCheck = 1;
												}
												if (action.result.Error_Code == 114) {
													options.ignoreEvnPSTimeDeseaseCheck = 1;
												}
												if (action.result.Error_Code == 115) {
													options.ignoreEvnPSHemoDouble = 1;
												}
												if (action.result.Error_Code == 116) {
													options.ignoreEvnPSHemoLong = 1;
												}
												if (action.result.Error_Code == 289) {
													options.ignoreCheckMorbusOnko = 1;
												}

												this.doSave(options);
											}
										}.createDelegate(this),
										icon: Ext.MessageBox.QUESTION,
										msg: msg,
										title: 'Продолжить сохранение?'
									});
								} else if ( action.result.Error_Msg ) {
                                    sw.swMsg.alert('Ошибка', action.result.Error_Msg, function() {
                                        switch ( action.result.Error_Code ) {
                                            case 1: // Дублирование номера карты
                                                base_form.findField('EvnPS_NumCard').focus(true);
                                                break;
                                        }
                                    });
                                }
                                else {
                                    sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
                                }
                            }
                        }.createDelegate(this),
                        params: params,
                        success: function(result_form, action) {
                            this.formStatus = 'edit';
                            loadMask.hide();

                            if ( action.result ) {
                                if ( action.result.EvnPS_id ) {
                                    var evn_ps_id = action.result.EvnPS_id;
                                    var koiko_dni = 0;

                                    var grid = this.findById('EPSEF_EvnSectionGrid');

									this.updateLookChange();

                                    if ( grid.getStore().getCount() > 0 && grid.getStore().getAt(0).get('EvnSection_id') ) {
                                        grid.getStore().each(function(rec) {
                                            if ( rec.get('EvnSection_KoikoDni') ) {
                                                koiko_dni = koiko_dni + parseInt(rec.get('EvnSection_KoikoDni'));
                                            }
                                        });
                                    }
									// https://redmine.swan.perm.ru/issues/37241
									else if ( !Ext.isEmpty(evn_ps_outcome_dt) ) {
										// Дата исхода из приемного
										evn_ps_dis_dt = evn_ps_outcome_dt; 
										// Койко-дни
										koiko_dni = daysBetween(evn_ps_set_dt, evn_ps_dis_dt);

										if ( koiko_dni > 0 ) {
											koiko_dni = koiko_dni + 1;
										}

										// Диагноз приемного
										if ( !Ext.isEmpty(base_form.findField('Diag_pid').getValue()) ) {
											diag_name = base_form.findField('Diag_pid').getFieldValue('Diag_Code') + '. ' + base_form.findField('Diag_pid').getFieldValue('Diag_Name');
										}

										// Исход из приемного
										if ( !Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) ) {
											leave_type_name = base_form.findField('PrehospWaifRefuseCause_id').getFieldValue('PrehospWaifRefuseCause_Name');
										}

										// Вид оплаты
										if ( !Ext.isEmpty(base_form.findField('PayType_id').getValue()) ) {
											pay_type_name = base_form.findField('PayType_id').getFieldValue('PayType_Name');
										}
									}

                                    base_form.findField('EvnPS_id').setValue(evn_ps_id);

                                    if ( action.result.PersonChild_id && base_form.findField('PersonChild_id') ) {
                                        base_form.findField('PersonChild_id').setValue(action.result.PersonChild_id);
                                    }
							
									checkSuicideRegistry({
										'Evn_id': evn_ps_id,
										'EvnClass_SysNick': 'EvnPS'
									});

									if (!getRegionNick().inlist([ 'kz' ]) && wnd.getOKSDiag()) {
										wnd.saveInBskRegistry();
									}
									if(getRegionNick() == 'ufa' && wnd.getONMKDiag()) {
										wnd.saveOnmkFromKvc(wnd.getONMKDiag(),  '', '', '', '', '');
									}
                                    if ( options && (typeof options.callback == 'function') /*&& (this.action == 'add')*/ ) {
                                        options.callback();
                                    }
                                    else {
                                        var date = null;
                                        var person_information = this.findById('EPSEF_PersonInformationFrame');
                                        var response = new Object();

                                        response.Diag_Name = diag_name;
                                        response.EvnPS_id = evn_ps_id;
                                        response.EvnPS_disDate = evn_ps_dis_dt;
                                        response.EvnPS_KoikoDni = koiko_dni;
                                        response.EvnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue();
                                        response.EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();
                                        response.LpuSection_Name = lpu_section_name;
                                        response.MedPersonal_Fio = med_personal_fio;
                                        response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
										response.Person_deadDT = person_information.getFieldValue('Person_deadDT');
                                        response.Person_Firname = person_information.getFieldValue('Person_Firname');
                                        response.Person_id = base_form.findField('Person_id').getValue();
                                        response.Person_Secname = person_information.getFieldValue('Person_Secname');
                                        response.Person_Surname = person_information.getFieldValue('Person_Surname');
                                        response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
                                        response.Server_id = base_form.findField('Server_id').getValue();
                                        response.Sex_Name = person_information.getFieldValue('Sex_Name');
                                        response.BirthWeight = this.BirthWeight;
                                        response.PersonWeight_text = this.PersonWeight_text;
                                        response.Okei_id = this.Okei_id;
                                        response.BirthHeight = this.BirthHeight;
                                        response.countChild = this.countChild;
                                        response.EvnSection_KSGKPG = this.EvnSection_KSGKPG;
                                        response.LeaveType_Code = leave_type_code;
                                        response.LeaveType_Name = leave_type_name;
                                        response.EvnSection_KSG = last_evn_section_info.EvnSection_KSG;
                                        response.EvnSection_KPG = last_evn_section_info.EvnSection_KPG;
                                        response.PayType_Name = pay_type_name;
										if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 2) {
											response.EvnCostPrint_IsNoPrintText = 'Отказ от справки';
										} else if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 1) {
											response.EvnCostPrint_IsNoPrintText = 'Справка выдана';
										} else {
											response.EvnCostPrint_IsNoPrintText = '';
										}
                                        response.EvnCostPrint_setDT = base_form.findField('EvnCostPrint_setDT').getValue();

										if (
											Ext.isEmpty(response.Person_deadDT) && !Ext.isEmpty(last_evn_section_info.LeaveType_SysNick)
											&& last_evn_section_info.LeaveType_SysNick.inlist([ 'die', 'ksdie', 'ksdiepp', 'diepp', 'dsdie', 'dsdiepp', 'kslet', 'ksletitar' ])
										) {
											response.Person_deadDT = last_evn_section_info.EvnSection_disDT;
										}

                                        if (this.childPS) {
                                            //наличие этой переменной как бы намекает, что окно КВС было вызвано из поиска человека
                                            //передаю ее дальше по каллбэкам
                                            this.callback({evnPSData: response}, {opener: this.opener});
                                        } else {
                                            this.callback({evnPSData: response});
                                        }

                                        if ( action.result.Alert_Msg ) {
                                            sw.swMsg.alert('Предупреждение', action.result.Alert_Msg);
                                        }

                                        if ( options && options.print == true ) {

											var KVS_Type = '';
											var EvnSection_id = 0;
											if(options.Parent_Code == '5')
											{
												KVS_Type = 'VG';
												EvnSection_id = grid.getSelectionModel().getSelected().get('EvnSection_id');
											}

											if (options.printType && options.printType == 'KSG') {
												printBirt({
													'Report_FileName': 'Raschet_KSG.rptdesign',
													'Report_Params': '&paramEvnPS=' + evn_ps_id,
													'Report_Format': 'html'
												});
											} else {
												var params = {};
												params.EvnPS_id = evn_ps_id;
												params.Parent_Code = options.Parent_Code;
												params.KVS_Type = KVS_Type;
												params.EvnSection_id = EvnSection_id;
												printEvnPS(params);
											}

                                            this.action = 'edit';
                                            this.setTitle(WND_HOSP_EPSEDIT);
                                        }else if (options.printRefuse) {
											printBirt({
												'Report_FileName': 'printEvnPSPrehospWaifRefuseCause.rptdesign',
												'Report_Params': '&paramEvnPsID=' + evn_ps_id,
												'Report_Format': 'pdf'
											});
                                        }

										if(options.pediculosPrint){
											this.pediculosPrint(false);
										}


										if (
											!options 
											|| options.ignorePediculosPrint 
											|| (!options.print && !options.printRefuse && !options.pediculosPrint)
										) {
											this.hide();
										}
                                    }
                                }
                                else {
                                    if ( action.result.Error_Msg ) {
                                        sw.swMsg.alert('Ошибка', action.result.Error_Msg);
                                    }
                                    else {
                                        sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
                                    }
                                }
                            }
                            else {
                                sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
                            }
                        }.createDelegate(this)
                    });

                }.createDelegate(this)
            });
    },
    draggable: true,
	// тест, переход на универсальную функцию из BaseForm.
	/*enableEdit: function(enable) {
        var base_form = this.findById('EvnPSEditForm').getForm();
        var form_fields = new Array(
            'Diag_did',
            'DiagSetPhase_did',
            'EvnPS_PhaseDescr_did',
            'EvnPS_IsPLAmbulance',
            'Diag_pid',
            'DiagSetPhase_pid',
            'EvnPS_PhaseDescr_pid',
            'EvnDirection_Num',
            'EvnDirection_setDate',
            'EvnPS_CodeConv',
            'EvnPS_HospCount',
            'EvnPS_IsCont',
            'EvnPS_IsDiagMismatch',
            'EvnPS_IsImperHosp',
            'EvnPS_IsNeglectedCase',
            'EvnPS_IsWrongCure',
            'EvnPS_IsUnlaw',
            'EvnPS_IsUnport',
            'EvnPS_IsShortVolume',
            'EvnPS_NumCard',
            'EvnPS_NumConv',
            'EvnPS_setDate',
            'EvnPS_setTime',
            'EvnPS_TimeDesease',
            'LpuSection_did',
            'LpuSection_pid',
            'MedStaffFact_pid',
            'Org_did',
            'PayType_id',
            'PrehospArrive_id',
            'PrehospDirect_id',
            'PrehospToxic_id',
            'PrehospTrauma_id',
            'PrehospType_id',
			'EvnPS_IsWithoutDirection',
			'EvnPS_OutcomeDate',
			'EvnPS_OutcomeTime',
			'LpuSection_eid',
			'PrehospWaifRefuseCause_id',
			'EvnPS_IsTransfCall'
        );

        for ( var i = 0; i < form_fields.length; i++ ) {
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
    evnPSAbortStore: null,
    firstRun: true,
    formStatus: 'edit',
	loadSpecificsTree: function () {
		var tree = this.findById('EDPLSEF_SpecificsTree');
		var base_form = this.findById('EvnPSEditForm').getForm();
		var root = tree.getRootNode();
		var win = this;

		if (win.specLoading) {
			clearTimeout(win.specLoading);
		};

		win.specLoading = setTimeout(function () {
			var Diag_ids = [];
			if (base_form.findField('Diag_pid').getValue() && base_form.findField('Diag_pid').getFieldValue('Diag_Code')) {
				Diag_ids.push([base_form.findField('Diag_pid').getValue(), 1, base_form.findField('Diag_pid').getFieldValue('Diag_Code'), '']);
			}
			
			tree.getLoader().baseParams.Diag_ids = Ext.util.JSON.encode(Diag_ids);
			tree.getLoader().baseParams.Person_id = base_form.findField('Person_id').getValue();
			tree.getLoader().baseParams.EvnPS_id = base_form.findField('EvnPS_id').getValue();
													
			if (!root.expanded) {
				root.expand();
			} else {
				var spLoadMask = new Ext.LoadMask(this.getEl(), {msg: "Загрузка специфик..."});
				spLoadMask.show();
				tree.getLoader().load(root, function () {
					spLoadMask.hide();
				});
			}
		}.createDelegate(this), 100);
	},
    getEvnPSNumber: function() {
        var evn_ps_num_field = this.findById('EvnPSEditForm').getForm().findField('EvnPS_NumCard');

        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение номера карты выбывшего из стационара..."});
        loadMask.show();

		var params = new Object();

		if ( !Ext.isEmpty(this.findById('EvnPSEditForm').getForm().findField('EvnPS_setDate').getValue()) ) {
			params.year = this.findById('EvnPSEditForm').getForm().findField('EvnPS_setDate').getValue().format('Y');
		}

        Ext.Ajax.request({
            callback: function(options, success, response) {
                loadMask.hide();

                if ( success ) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);

                    evn_ps_num_field.setValue(response_obj.EvnPS_NumCard);
                    evn_ps_num_field.focus(true);
                }
                else {
                    sw.swMsg.alert('Ошибка', 'Ошибка при определении номера КВС');
                }
            },
			params: params,
            url: '/?c=EvnPS&m=getEvnPSNumber'
        });
    },
    getEvnSectionInfo: function(type, data) {
        if ( !type || typeof type != 'string' || !type.inlist([ 'first', 'last', 'next', 'prev' ]) ) {
            return false;
        }

        if ( typeof data != 'object' ) {
            data = new Object();
        }

        var base_form = this.findById('EvnPSEditForm').getForm();
        var grid = this.findById('EPSEF_EvnSectionGrid');

        var chooseEvnSection;
        var diag_id;
		var diag_name;
        var dis_dt;
        var evn_section_dis_dt;
        var evn_section_id;
        var evn_section_set_dt;
        var leave_type_code, leave_type_sys_nick;
        var CureResult_Code;
        var leave_type_id;
        var leave_type_name;
        var pay_type_name;
        var set_dt;
        var lpu_unit_type_id;
		var lpu_section_name;
        var lpu_unit_type_sys_nick;
        var EvnSection_KSG;
        var EvnSection_KPG;

        // Получаем id, дату поступления и дату выписки искомого отделения
        if ( grid.getStore().getCount() > 0 && grid.getStore().getAt(0).get('EvnSection_id') ) {
            grid.getStore().each(function(rec) {
                chooseEvnSection = false;
                dis_dt = getValidDT(Ext.util.Format.date(rec.get('EvnSection_disDate'), 'd.m.Y'), rec.get('EvnSection_disTime'));
                set_dt = getValidDT(Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y'), rec.get('EvnSection_setTime'));

                switch ( type ) {
                    case 'first':
                        if ( !evn_section_set_dt || evn_section_set_dt > set_dt || (evn_section_set_dt == set_dt && (!evn_section_dis_dt || evn_section_dis_dt > dis_dt)) ) {
                            chooseEvnSection = true;
                        }
                        break;

                    case 'last':
                        if ( !evn_section_set_dt || evn_section_set_dt < set_dt || (evn_section_set_dt == set_dt && (!dis_dt || (evn_section_dis_dt && evn_section_dis_dt < dis_dt))) ) {
                            chooseEvnSection = true;
                        }
                        break;

                    case 'next':
                        if ( typeof data.EvnSection_setDT == 'object' && rec.get('EvnSection_id') != data.EvnSection_id && data.EvnSection_setDT < set_dt && (!evn_section_set_dt || evn_section_set_dt > set_dt) ) {
                            chooseEvnSection = true;
                        }
                        break;

                    case 'prev':
                        if ( typeof data.EvnSection_setDT == 'object' && rec.get('EvnSection_id') != data.EvnSection_id && data.EvnSection_setDT > set_dt && (!evn_section_set_dt || evn_section_set_dt < set_dt) ) {
                            chooseEvnSection = true;
                        }
                        break;
                }

                if ( chooseEvnSection == true ) {
                    evn_section_dis_dt = dis_dt;
                    evn_section_id = rec.get('EvnSection_id');
                    evn_section_set_dt = set_dt;
                    diag_id = rec.get('Diag_id');
					diag_name = rec.get('Diag_Name')
                    leave_type_code = rec.get('LeaveType_Code');
					leave_type_sys_nick = rec.get('LeaveType_SysNick');
					CureResult_Code = rec.get('CureResult_Code');
                    leave_type_id = rec.get('LeaveType_id');
                    leave_type_name = rec.get('LeaveType_Name');
                    pay_type_name = rec.get('PayType_Name');
                    lpu_unit_type_id = rec.get('LpuUnitType_id');
					lpu_section_name = rec.get('LpuSection_Name');
					lpu_unit_type_sys_nick = rec.get('LpuUnitType_SysNick');
					EvnSection_KSG = rec.get('EvnSection_KSG');
					EvnSection_KPG = rec.get('EvnSection_KPG');
                }
            });
        }

        return {
            EvnSection_disDT: evn_section_dis_dt
            ,Diag_id: diag_id
			,Diag_name: diag_name
            ,EvnSection_id: evn_section_id
            ,EvnSection_setDT: evn_section_set_dt
            ,LeaveType_Code: leave_type_code
            ,LeaveType_SysNick: leave_type_sys_nick
			,CureResult_Code: CureResult_Code
            ,LeaveType_id: leave_type_id
			,LpuSection_Name:lpu_section_name
            ,LeaveType_Name: leave_type_name
            ,LpuUnitType_id: lpu_unit_type_id
            ,LpuUnitType_SysNick: lpu_unit_type_sys_nick
            ,PayType_Name: pay_type_name
			,EvnSection_KSG: EvnSection_KSG
			,EvnSection_KPG: EvnSection_KPG
		}
    },
    height: 550,
    id: 'EvnPSEditWindow',
	isValidTltUslugaDT: function() {
		var base_form = this.findById('EvnPSEditForm').getForm();
		var EvnPsSetDate = base_form.findField('EvnPS_setDate').getValue();
		var EvnPsSetTime =  base_form.findField('EvnPS_setTime').getValue();
		var TltUslugaDate = base_form.findField('EvnPS_CmpTltDate').getValue();
		var TltUslugaTime = base_form.findField('EvnPS_CmpTltTime').getValue();

		if(EvnPsSetDate && EvnPsSetTime && TltUslugaDate && TltUslugaTime){

			var evnPsSetDT = EvnPsSetDate.format('Y-m-d') + ' ' + EvnPsSetTime;
			var tltUslugaDT = TltUslugaDate.format('Y-m-d') + ' ' + TltUslugaTime;
			return new Date(evnPsSetDT) > new Date(tltUslugaDT);
		} 
		return true;
	},
	checkPneumoDiag: function() {
		var win = this;		
		var base_form = win.findById('EvnPSEditForm').getForm();
		var EvnSectionStore = this.findById('EPSEF_EvnSectionGrid').getStore();
		var lpusectionprofile_id;
		
		var index1 = this.findById('EPSEF_EvnDiagPSHospGrid').getStore().findBy(function(rec) {
			return !!rec && isPneumoDiag(rec.get('Diag_Code'));
		});
		
		var index2 = this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().findBy(function(rec) {
			return !!rec && isPneumoDiag(rec.get('Diag_Code'));
		});
		
		if (
			!isPneumoDiag(base_form.findField('Diag_did').getFieldValue('Diag_Code')) &&  // напр
			!isPneumoDiag(base_form.findField('Diag_pid').getFieldValue('Diag_Code')) && // приём
			index1 == -1 &&
			index2 == -1
		) {
			return false;
		}
		
		if (EvnSectionStore.getCount() == 0 || !EvnSectionStore.getAt(0).get('EvnSection_id')) {
			lpusectionprofile_id = base_form.findField('LpuSection_eid').getFieldValue('LpuSectionProfile_id');
		} else {
			EvnSectionStore.sort('EvnSection_setDate','desc');
			lpusectionprofile_id = EvnSectionStore.getAt(0) ? EvnSectionStore.getAt(0).get('LpuSectionProfile_id') : null;
			EvnSectionStore.sort('EvnSection_setDate','asc');
		}
		
		if (!!lpusectionprofile_id && lpusectionprofile_id != 20000316) {
			return true;
		}
		
		return false;
	},
	checkEvnSectionDiag: function() {
		var DiagST_UP = ['I21.0','I21.1','I21.2','I21.3','I21.9','I22.0','I22.1','I22.8','I22.9'];
		var DiagST = ['I20.0','I21.4'];
		var ECG_ST_UP = ['1','2','3','4','5'];
		var ECG_ST = ['6','7','8','9','10'];
		var EvnSectionStore = this.findById('EPSEF_EvnSectionGrid').getStore();
		var lastDiagIndex = EvnSectionStore.getCount() - 1;
		var lastDiagCode = (EvnSectionStore.getAt(lastDiagIndex)) ? EvnSectionStore.getAt(lastDiagIndex).get('Diag_Code') : '';
		var EcgResultCode = this.findById(this.id + '_ECGResult').getValue();
		if(!lastDiagCode.inlist(this.OksDiagCode) || lastDiagCode == '' || EcgResultCode == '') {
			return true;
		}
		if(lastDiagCode.inlist(DiagST_UP)) {
			return EcgResultCode.inlist(ECG_ST_UP);
		}
		else if (lastDiagCode.inlist(DiagST)) {
			return EcgResultCode.inlist(ECG_ST);
		}
		return true;
	},
	IshemiaCode: ['I20','I21', 'I22', 'I23', 'I24', 'I25'],
	OksDiagCode: ['I20.0','I21.0','I21.1','I21.2','I21.3','I21.4','I21.9','I22.0','I22.1','I22.8','I22.9'], //['I20.0','I21.0','I21.1','I21.2','I21.3','I21.4','I21.9','I22.0','I22.1','I22.8','I22.9','I24.0','I24.1','I24.8','I24.9'],
	ONMKDiagCode: ['G45','I60','I61','I62','I63','I64'],
	loadUslugaGrid: function(){
		var base_form = this.findById('EvnPSEditForm').getForm();
		var evn_ps_id = base_form.findField('EvnPS_id').getValue();
		var usluga_panel = this.findById('EPSEF_EvnUslugaPanel');
		var uslugaGridStore = this.findById('EPSEF_EvnUslugaGrid').getStore();
		if(!usluga_panel.isLoaded){
			uslugaGridStore.load({ params: { pid: evn_ps_id } });
			uslugaGridStore.on('load',function(){ usluga_panel.isLoaded = true; });
		}
	},
	loadECGResult: function() {
		var ecgRow, ecg_id = 0, ecgCode = 'A05.10.006';
		var uslugaGridStore = this.findById('EPSEF_EvnUslugaGrid').getStore();
		uslugaGridStore.each(function(rec) {
			usluga_id = rec.get('EvnUsluga_id');
			usluga_code = rec.get('Usluga_Code');
			if(usluga_code == ecgCode && usluga_id > ecg_id)
				ecg_id = usluga_id;
		});
		if(ecg_id)
			this.findById(this.id + '_ECGResult').getStore().load({ 'params' : { 'EvnUsluga_id' : ecg_id } });
	},
	getOKSDiag: function() {
		var oksDiagCode = this.OksDiagCode
		var EvnSectionStore = this.findById('EPSEF_EvnSectionGrid').getStore();

		if(EvnSectionStore.getCount()>0){
			EvnSectionStore.sort('EvnSection_setDate','desc');
			var diagCode = EvnSectionStore.getAt(0).get('Diag_Code');

			if(diagCode != "" && !diagCode.inlist(this.OksDiagCode))
				return false;

			if(diagCode.inlist(this.OksDiagCode)) {
				return {
					id: EvnSectionStore.getAt(0).get('Diag_id'),
					name: EvnSectionStore.getAt(0).get('Diag_Name'),
					EvnSection_id: EvnSectionStore.getAt(0).get('EvnSection_id')
				}
			}
			EvnSectionStore.sort('EvnSection_setDate','asc');
		}

		var DiagHospCombo  = this.findById('EPSEF_DiagHospCombo');
		if(DiagHospCombo.getCode().inlist(oksDiagCode))
			return { id: DiagHospCombo.getValue(),  name: DiagHospCombo.getRawValue() };

		var DiagRecepCombo = this.findById(this.id + '_DiagRecepCombo');
		if (DiagRecepCombo.getCode().inlist(oksDiagCode))
			return { id: DiagRecepCombo.getValue(), name: DiagRecepCombo.getRawValue() };
		return false;
	},
	getONMKDiag: function() {
		var DiagRecepCombo = this.findById(this.id + '_DiagRecepCombo');
		var arr_diag = DiagRecepCombo.getCode().split('.');
		
		if (arr_diag[0].inlist(this.ONMKDiagCode) && DiagRecepCombo.getCode() != "G45.3")			
			return { id: DiagRecepCombo.getValue(), name: DiagRecepCombo.getRawValue() };
		return false;
	},
	saveInBskRegistry: function() {
		var wnd = this;
		var CHKVUslugaCode = ['A16.12.004.009','A16.12.004.010','A16.12.004.012','A16.12.004.013','A16.12.026','A16.12.026.011','A16.12.026.012','A16.12.028.003','A16.12.028.017']; //['A06.10.006','A16.12.004.009','A16.12.026.003','A16.12.026.004','A16.12.026.005','A16.12.026.006','A16.12.026.007','A16.12.028'];
		var TLTUslugaCode = ['A11.12.003.002','A16.23.034.014','A16.23.034.015','A16.23.034.016','A16.23.034.017','A25.30.036.001','A11.12.003.005','A11.12.003.006','A11.12.003.007','A11.12.003.008']; //['A11.12.003.002', 'A11.12.008']; //['A16.23.034.011','A16.23.034.012','A25.30.036.002','A25.30.036.003'];
		var ECGUslugaCode = ['A05.10.006'];
		var KAGUslugaCode = ['A06.10.006','A06.10.006.002'];
		var base_form = this.findById('EvnPSEditForm').getForm();
		var person_id = base_form.findField('Person_id').getValue();
		var evnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue()
		var diag = this.getOKSDiag();
		var evnPsSetDT = base_form.findField('EvnPS_setDate').getValue().format('Y-m-d') + ' ' + base_form.findField('EvnPS_setTime').getValue();
		var CHKVUslugaDT = getUslugaDT(CHKVUslugaCode);
		var EcgResult = this.findById(this.id + '_ECGResult').getRawValue();
		var ecgDT = getUslugaDT(ECGUslugaCode) ? getUslugaDT(ECGUslugaCode).format('d-m-Y H:i') : '';
		var tltDT = getUslugaDT(TLTUslugaCode) ? getUslugaDT(TLTUslugaCode).format('d-m-Y H:i'): '';
		var timeFromEnterToCHKV; 		//timeFromEnterToCHKV разница в минутах
		if(CHKVUslugaDT) {
			timeFromEnterToCHKV = new Date(new Date(CHKVUslugaDT) - new Date(/*evnPsSetDT*/getPainDT()))/1000/60;
			if(timeFromEnterToCHKV < 0)
				timeFromEnterToCHKV = '';
			CHKVUslugaDT = new Date(CHKVUslugaDT).format('d-m-Y H:i');
		}
		evnPsSetDT = new Date(evnPsSetDT).format('d-m-Y H:i');
		var diagDir = base_form.findField('EPSEF_DiagHospCombo').getRawValue();
		var diagPriem = base_form.findField('EvnPSEditWindow_DiagRecepCombo').getRawValue();
		var LpuSectionHosp;
		var EvnSectionStoreHosp = this.findById('EPSEF_EvnSectionGrid').getStore();
		if(EvnSectionStoreHosp.getCount()>0){
			LpuSectionHosp = EvnSectionStoreHosp.getAt(0).get('LpuSection_Name');
		}
		var kagDT = getUslugaDT(KAGUslugaCode) ? getUslugaDT(KAGUslugaCode).format('d-m-Y H:i'): '';
		var PainDT = new Date(getPainDT()).format('d-m-Y H:i');

		var params_OKS = {
			'Registry_method'     : 'ins',
			'Person_id'           : person_id,
			'MorbusType_id'       : 19,
			'Diag_id'             : diag.id,
			'DiagOKS'             : (diag.EvnSection_id) ? diag.name: null, //должен сохраняться основной диагноз из движения #161793#note-26
			'EvnPS_NumCard'       : evnPS_NumCard,
			'PainDT'              : PainDT,
			'ECGDT'               : ecgDT,
			'ResultECG'           : EcgResult,
			'TLTDT'               : tltDT,
			'LpuDT'               : evnPsSetDT,
			'MOHospital'          : getGlobalOptions().lpu_nick,
			'ZonaCHKV'            : CHKVUslugaDT,
			'TimeFromEnterToChkv' : timeFromEnterToCHKV,
			'LeaveType_Name'      : getLeaveType(),
			'diagDir'             : diagDir,
			'diagPriem'           : diagPriem,
			'LpuSection'          : LpuSectionHosp,
			'KAGDT'               : kagDT
		};

		function getLeaveType() {
			var EvnSectionStore = wnd.findById('EPSEF_EvnSectionGrid').getStore();
			var OksRowIndex = EvnSectionStore.findBy(function(rec){
				return rec.get('Diag_Code').inlist(wnd.OksDiagCode);
			});
			if(OksRowIndex > -1) {
				var leaveTypeCode = EvnSectionStore.getAt(OksRowIndex).get('LeaveType_Code');
				if(leaveTypeCode && leaveTypeCode.inlist([1,2,3,4]))
					return EvnSectionStore.getAt(OksRowIndex).get('LeaveType_Name')
			}
			return null;
		}

		function getUslugaDT(Code) {
			var uslugaGridStore = wnd.findById('EPSEF_EvnUslugaGrid').getStore();

			uslugaGridStore.sort('EvnUsluga_id','DESC');
			var uslugaRowIndex = uslugaGridStore.findBy(function(rec){
				return rec.get('Usluga_Code').inlist(Code);
			});

			var uslugaRow = uslugaGridStore.getAt(uslugaRowIndex);
			if(uslugaRow) 
				return new Date(uslugaRow.get('EvnUsluga_setDate').format('Y-m-d') + ' ' + uslugaRow.get('EvnUsluga_setTime'))
			else
				return null;
		}

		function getPainDT() {
			var base_form = wnd.findById('EvnPSEditForm').getForm();
			var EvnPsSetDate = base_form.findField('EvnPS_setDate').getValue();
			var EvnPsSetTime =  base_form.findField('EvnPS_setTime').getValue();
			if(EvnPsSetDate && EvnPsSetTime){
				var painDT = new Date(EvnPsSetDate.format('Y-m-d') + ' ' + EvnPsSetTime);
				//var painDT = new Date();
				var Okei_type = base_form.findField('Okei_id').getValue();
				var time = base_form.findField('EvnPS_TimeDesease').getValue();
				if(Okei_type == '100') { 		 // час
					painDT.setHours(painDT.getHours() - time);
				} else if (Okei_type == '101') { // сутки
					painDT.setDate(painDT.getDate() - time);
				} else if (Okei_type == '102') { // неделя
					painDT.setDate(painDT.getDate() - time * 7);
				} else if (Okei_type == '104') { // месяц					
					painDT.setMonth(painDT.getMonth() - time);
				} else if (Okei_type == '107') { // год
					painDT.setYear(painDT.getFullYear() - time);
				}

				return painDT.format('Y-m-d H:i');
			} else null;
		};

		function saveOKSAjax(params){ 
			Ext.Ajax.request({
				params: params,
				url: '/?c=BSK_RegisterData&m=saveKvsInOKS',
				  callback: function(options, success, response) {
					if(success) {
						return true;
					}
					else false;
				}
			});
		};

		// Исключаем заненсение в регистр БСК в предмете наблюдения "ОКС" (MorbusType_id === 19) пациентов МО типа
		// "1. Лечебно профилактические учреждения / 1.7. Санаторно-курортные учреждения / и подпункты..." (LpuType_Code IN (11, 89, 90, 91, 92, 93, 94))      
		function checkLpuType(params){ 
			var lpuTypeCode = getGlobalOptions().lpu_type_code;

			var LpuSanatType = new Array(11, 89, 90, 91, 92, 93, 94); // Коды типов МО, являющихся санаториями

			if (!(LpuSanatType.indexOf(lpuTypeCode) in LpuSanatType && params.MorbusType_id === 19)){ // Сохраняем данные в регистр, если ОКС и тип МО "Санаторно-курортный"
				saveOKSAjax(params_OKS);
			} else {
				Ext.Msg.alert('Внимание!', 'Данные не будут сохранены в регистр БСК');
			}
		};

		Ext.Ajax.request({
			params: {
				'Person_id' 	: person_id,
				'EvnPS_NumCard' : evnPS_NumCard
			},
			url: '/?c=BSK_RegisterData&m=getOksId',
			success: function (response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj.length > 0) {
					if(!Ext.isEmpty(response_obj[0].Person_deadDT))
						return false;
					if(!Ext.isEmpty(response_obj[0].BSKRegistry_id))
						params_OKS.Registry_method = response_obj[0].BSKRegistry_id;
				}
				checkLpuType(params_OKS);
				return true;
			}
		});
	},
    getEvnSectionInfoForONMK: function() {
        data = new Object();
		
        var base_form = this.findById('EvnPSEditForm').getForm();
        var grid = this.findById('EPSEF_EvnSectionGrid');
		
        var chooseEvnSection;
        var diag_id;
		var diag_name;
        var dis_dt;
        var evn_section_dis_dt;
        var evn_section_id;
        var evn_section_set_dt;
        var leave_type_code, leave_type_sys_nick;
        var CureResult_Code;
        var leave_type_id;
        var leave_type_name;
        var pay_type_name;
        var set_dt;
        var lpu_unit_type_id;
		var lpu_section_name;
        var lpu_unit_type_sys_nick;
        var EvnSection_KSG;
        var EvnSection_KPG;

        // Получаем id, дату поступления и дату выписки искомого отделения
        if ( grid.getStore().getCount() > 0 && grid.getStore().getAt(0).get('EvnSection_id') ) {
            grid.getStore().each(function(rec) {
                chooseEvnSection = false;
                dis_dt = getValidDT(Ext.util.Format.date(rec.get('EvnSection_disDate'), 'd.m.Y'), rec.get('EvnSection_disTime'));
                set_dt = getValidDT(Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y'), rec.get('EvnSection_setTime'));

                switch ( type ) {
                    case 'first':
                        if ( !evn_section_set_dt || evn_section_set_dt > set_dt || (evn_section_set_dt == set_dt && (!evn_section_dis_dt || evn_section_dis_dt > dis_dt)) ) {
                            chooseEvnSection = true;
                        }
                        break;

                    case 'last':
                        if ( !evn_section_set_dt || evn_section_set_dt < set_dt || (evn_section_set_dt == set_dt && (!dis_dt || (evn_section_dis_dt && evn_section_dis_dt < dis_dt))) ) {
                            chooseEvnSection = true;
                        }
                        break;

                    case 'next':
                        if ( typeof data.EvnSection_setDT == 'object' && rec.get('EvnSection_id') != data.EvnSection_id && data.EvnSection_setDT < set_dt && (!evn_section_set_dt || evn_section_set_dt > set_dt) ) {
                            chooseEvnSection = true;
                        }
                        break;

                    case 'prev':
                        if ( typeof data.EvnSection_setDT == 'object' && rec.get('EvnSection_id') != data.EvnSection_id && data.EvnSection_setDT > set_dt && (!evn_section_set_dt || evn_section_set_dt < set_dt) ) {
                            chooseEvnSection = true;
                        }
                        break;
                }

                if ( chooseEvnSection == true ) {
                    evn_section_dis_dt = dis_dt;
                    evn_section_id = rec.get('EvnSection_id');
                    evn_section_set_dt = set_dt;
                    diag_id = rec.get('Diag_id');
					diag_name = rec.get('Diag_Name')
                    leave_type_code = rec.get('LeaveType_Code');
					leave_type_sys_nick = rec.get('LeaveType_SysNick');
					CureResult_Code = rec.get('CureResult_Code');
                    leave_type_id = rec.get('LeaveType_id');
                    leave_type_name = rec.get('LeaveType_Name');
                    pay_type_name = rec.get('PayType_Name');
                    lpu_unit_type_id = rec.get('LpuUnitType_id');
					lpu_section_name = rec.get('LpuSection_Name');
					lpu_unit_type_sys_nick = rec.get('LpuUnitType_SysNick');
					EvnSection_KSG = rec.get('EvnSection_KSG');
					EvnSection_KPG = rec.get('EvnSection_KPG');
                }
            });
        }

        return {
            EvnSection_disDT: evn_section_dis_dt
            ,Diag_id: diag_id
			,Diag_name: diag_name
            ,EvnSection_id: evn_section_id
            ,EvnSection_setDT: evn_section_set_dt
            ,LeaveType_Code: leave_type_code
            ,LeaveType_SysNick: leave_type_sys_nick
			,CureResult_Code: CureResult_Code
            ,LeaveType_id: leave_type_id
			,LpuSection_Name:lpu_section_name
            ,LeaveType_Name: leave_type_name
            ,LpuUnitType_id: lpu_unit_type_id
            ,LpuUnitType_SysNick: lpu_unit_type_sys_nick
            ,PayType_Name: pay_type_name
			,EvnSection_KSG: EvnSection_KSG
			,EvnSection_KPG: EvnSection_KPG
		}
    },	
	openStickFSSDataEditWindow: function () {
		var win = this;
		var base_form = this.findById('EvnPSEditForm').getForm();
		var rec = this.findById('EPSEF_EvnStickGrid').getSelectionModel().getSelected();

		var options = {
			action: 'add',
			ignoreCheckExist: true
		}
		var rec = win.findById('EPSEF_EvnStickGrid').getSelectionModel().getSelected();
		if (rec) {
			options.Person_id = rec.get('Person_id');
			options.StickFSSData_StickNum = rec.get('EvnStick_Num');
		}
		options.callback = function () {
			win.findById('EPSEF_EvnStickGrid').getStore().load({
				params: {
					EvnStick_pid: base_form.findField('EvnPS_id').getValue()
				}
			});
		}
		getWnd('swStickFSSDataEditWindow').show(options);
	},
	saveOnmkFromKvc: function(diag, rankinScale_id, rankinScale_sid, evnSection_InsultScale, leaveType_id, evn_section_id, evnSectionPsSetDT) {
	
		console.log('in method saveOnmkFromKvc');
		var wnd = this;
		
		//Услуги ТЛТ
		var TLTUslugaCode = ['A16.23.034.011','A16.23.034.012'];
		
		//Услуги КТ
		var KTUslugaCode = ['A06.23.004','A06.23.004.001','A06.23.004.002','A06.23.004.006','A06.23.004.007','A06.23.004.008'];
		
		//Услуги МРТ		
		var MRTUslugaCode = ['A05.23.005','A05.23.005.001','A05.23.009','A05.23.009.001','A05.23.009.002','A05.23.009.003','A05.23.009.004','A05.23.009.005','A05.23.009.006','A05.23.009.007','A05.23.009.008','A05.23.009.009','A05.23.009.010','A05.23.009.011','A05.23.009.012','A05.23.009.013','A05.23.009.014','A05.23.009.015','A05.23.009.016','A05.23.009.017'];		
		
		var base_form = this.findById('EvnPSEditForm').getForm();
		
		var person_id = base_form.findField('Person_id').getValue();
		//№ медицинской карты
		var evnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue();
		
		//var diag = diag;//this.getONMKDiag();
		//Дата поступления
		var evnPsSetDT = base_form.findField('EvnPS_setDate').getValue().format('Y-m-d') + ' ' + base_form.findField('EvnPS_setTime').getValue();
				
		//var CHKVUslugaDT = getUslugaDT(CHKVUslugaCode);				гггг-мм-дд чч:ми:сс
		var tltDT = getUslugaDT(TLTUslugaCode) ? getUslugaDT(TLTUslugaCode).format('Y-m-d H:i:s') : '';
		var ktDT = getUslugaDT(KTUslugaCode) ? getUslugaDT(KTUslugaCode).format('Y-m-d H:i:s') : '';
		var mrtDT = getUslugaDT(MRTUslugaCode) ? getUslugaDT(MRTUslugaCode).format('Y-m-d H:i:s') : '';
		
		//var EcgResult = this.findById(this.id + '_ECGResult').getRawValue();
		//var evnPS_TimeDeseaseType = base_form.findField('Okei_id').getValue();
		//var evnPS_TimeDesease = base_form.findField('EvnPS_TimeDesease').getValue();


		evnPsSetDT = new Date(evnPsSetDT).format('Y-m-d H:i:s');
				
		//Дата госпитализации
		var evnPSEditWindowEvnPS_setDate = base_form.findField('EvnPSEditWindowEvnPS_setDate').getValue();
		var evnPSEditWindowEvnPS_setTime = base_form.findField('EvnPSEditWindowEvnPS_setTime').getValue();		

		var params_ONMK = {
			'Registry_method'     : 'ins',
			'Person_id'           : person_id,
			'MorbusType_name'     : 'onmk',
			'Diag_id'             : diag.id,
			'Diag_Name'           : diag.name,
			'EvnPS_NumCard'       : evnPS_NumCard,			
			'PainDT'              : evn_section_id == '' ? getPainDT() : evnSectionPsSetDT, //дата начала заболевания
			'MRTDT'               : mrtDT,
			'KTDT'				  : ktDT,
			'TLTDT'               : tltDT,
			'LpuDT'               : evn_section_id == '' ? evnPsSetDT : evnSectionPsSetDT,//дата поступления
			'MOHospital'          : getGlobalOptions().lpu_nick,
			'LpuSection_pid'	  : base_form.findField('LpuSection_pid').getValue(),
			'MedStaffFact_pid'    : base_form.findField('MedStaffFact_pid').getValue(),
			'RankinScale_id'        : rankinScale_id, 
			'RankinScale_sid'        : rankinScale_sid, 
			'EvnSection_InsultScale': evnSection_InsultScale,
			'LeaveType_id' : leaveType_id,
			'evn_section_id' : evn_section_id,
			'EvnPS_id'       : base_form.findField('EvnPS_id').getValue()
		};

		function getLeaveType() {
			var EvnSectionStore = wnd.findById('EPSEF_EvnSectionGrid').getStore();
			var OksRowIndex = EvnSectionStore.findBy(function(rec){
				return rec.get('Diag_Code').inlist(wnd.OksDiagCode);
			});
			if(OksRowIndex > -1) {
				var leaveTypeCode = EvnSectionStore.getAt(OksRowIndex).get('LeaveType_Code');
				if(leaveTypeCode && leaveTypeCode.inlist([1,2,3,4]))
					return EvnSectionStore.getAt(OksRowIndex).get('LeaveType_Name')
			}
			return null;
		}

		function getUslugaDT(Code) {
			var uslugaGridStore = wnd.findById('EPSEF_EvnUslugaGrid').getStore();

			uslugaGridStore.sort('EvnUsluga_id','DESC');
			var uslugaRowIndex = uslugaGridStore.findBy(function(rec){
				return rec.get('Usluga_Code').inlist(Code);
			});

			var uslugaRow = uslugaGridStore.getAt(uslugaRowIndex);
			if(uslugaRow) 
				return new Date(uslugaRow.get('EvnUsluga_setDate').format('Y-m-d') + ' ' + uslugaRow.get('EvnUsluga_setTime'))
			else
				return null;
		}

		function getPainDT() {
			var base_form = wnd.findById('EvnPSEditForm').getForm();
			var EvnPsSetDate = base_form.findField('EvnPS_setDate').getValue();
			var EvnPsSetTime =  base_form.findField('EvnPS_setTime').getValue();
			if(EvnPsSetDate && EvnPsSetTime){
				var painDT = new Date(EvnPsSetDate.format('Y-m-d') + ' ' + EvnPsSetTime);
				//var painDT = new Date();
				var Okei_type = base_form.findField('Okei_id').getValue();
				var time = base_form.findField('EvnPS_TimeDesease').getValue();
				if(Okei_type == '100') { 		 // час
					painDT.setHours(painDT.getHours() - time);
				} else if (Okei_type == '101') { // сутки
					painDT.setDate(painDT.getDate() - time);
				} else if (Okei_type == '102') { // неделя
					painDT.setDate(painDT.getDate() - time * 7);
				} else if (Okei_type == '104') { // месяц
					painDT.setMounth(painDT.getMounth() - time);
				} else if (Okei_type == '107') { // год
					painDT.setYear(painDT.getYear() - time);
				}

				return painDT.format('Y-m-d H:i:s');
			} else '';
		};

		function saveONMKajax(params){ 
			Ext.Ajax.request({
				params: params,
				url: '/?c=ONMKRegister&m=saveOnmkFromKvc',
				  callback: function(options, success, response) {
					if(success) {
						return true;
					}
					else false;
				}
			});
		};
		
		saveONMKajax(params_ONMK);
	},
	initComponent: function() {
		var win = this;
		var me = this;
        if (this.id == 'EvnPSEditWindow'){
            this.tabindex = TABINDEX_EPSEF;
        } else {
            this.tabindex = TABINDEX_EPSEF2;
        }
        this.keyHandlerAlt = {
            alt: true,
            fn: function(inp, e) {
                var current_window = this;
                switch ( e.getKey() ) {
                    case Ext.EventObject.C:
                        current_window.doSave();
                        break;

                    case Ext.EventObject.G:
                        current_window.printEvnPS();
                        break;

                    case Ext.EventObject.J:
                        current_window.onCancelAction();
                        break;

                    case Ext.EventObject.NUM_ONE:
                    case Ext.EventObject.ONE:
                        if ( !current_window.findById('EPSEF_HospitalisationPanel').hidden ) {
                            current_window.findById('EPSEF_HospitalisationPanel').toggleCollapse();
                        }
                        break;

                    case Ext.EventObject.NUM_TWO:
                    case Ext.EventObject.TWO:
                        if ( !current_window.findById('EPSEF_DirectDiagPanel').hidden ) {
                            current_window.findById('EPSEF_DirectDiagPanel').toggleCollapse();
                        }
                        break;

                    case Ext.EventObject.NUM_THREE:
                    case Ext.EventObject.THREE:
                        if ( !current_window.findById('EPSEF_AdmitDepartPanel').hidden ) {
                            current_window.findById('EPSEF_AdmitDepartPanel').toggleCollapse();
                        }
                        break;

                    case Ext.EventObject.FOUR:
                    case Ext.EventObject.NUM_FOUR:
                        if ( !current_window.findById('EPSEF_AdmitDiagPanel').hidden ) {
                            current_window.findById('EPSEF_AdmitDiagPanel').toggleCollapse();
                        }
                        break;

                    case Ext.EventObject.FIVE:
                    case Ext.EventObject.NUM_FIVE:
                        if ( !current_window.findById('EPSEF_EvnSectionPanel').hidden ) {
                            current_window.findById('EPSEF_EvnSectionPanel').toggleCollapse();
                        }
                        break;

                    case Ext.EventObject.NUM_SIX:
                    case Ext.EventObject.SIX:
                        if ( !current_window.findById('EPSEF_EvnStickPanel').hidden ) {
                            current_window.findById('EPSEF_EvnStickPanel').toggleCollapse();
                        }
                        break;

                    case Ext.EventObject.NUM_SEVEN:
                    case Ext.EventObject.SEVEN:
                        if ( !current_window.findById('EPSEF_EvnUslugaPanel').hidden ) {
                            current_window.findById('EPSEF_EvnUslugaPanel').toggleCollapse();
                        }
                        break;

                    case Ext.EventObject.EIGHT:
                    case Ext.EventObject.NUM_EIGHT:
                        if ( !current_window.findById('EPSEF_EvnDrugPanel').hidden ) {
                            current_window.findById('EPSEF_EvnDrugPanel').toggleCollapse();
                        }
                        break;
                }
            },
            key: [
                Ext.EventObject.C,
                Ext.EventObject.EIGHT,
                Ext.EventObject.G,
                Ext.EventObject.FOUR,
                Ext.EventObject.FIVE,
                Ext.EventObject.J,
                Ext.EventObject.NUM_EIGHT,
                Ext.EventObject.NUM_FOUR,
                Ext.EventObject.NUM_FIVE,
                Ext.EventObject.NUM_ONE,
                Ext.EventObject.NUM_SEVEN,
                Ext.EventObject.NUM_SIX,
                Ext.EventObject.NUM_TWO,
                Ext.EventObject.NUM_THREE,
                Ext.EventObject.ONE,
                Ext.EventObject.SEVEN,
                Ext.EventObject.SIX,
                Ext.EventObject.TWO,
                Ext.EventObject.THREE
            ],
            stopEvent: true,
            scope: this
        };
        this.keyHandler = {
            alt: false,
            fn: function(inp, e) {
                var current_window = this;

                switch ( e.getKey() ) {
                    case Ext.EventObject.F6:
                        current_window.findById('EPSEF_PersonInformationFrame').panelButtonClick(1);
                        break;

                    case Ext.EventObject.F10:
                        current_window.findById('EPSEF_PersonInformationFrame').panelButtonClick(2);
                        break;

                    case Ext.EventObject.F11:
                        current_window.findById('EPSEF_PersonInformationFrame').panelButtonClick(3);
                        break;

                    case Ext.EventObject.F12:
                        if ( e.ctrlKey == true ) {
                            current_window.findById('EPSEF_PersonInformationFrame').panelButtonClick(5);
                        }
                        else {
                            current_window.findById('EPSEF_PersonInformationFrame').panelButtonClick(4);
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
            stopEvent: true,
            scope: this
        };
        Ext.apply(this, {
            keys: [this.keyHandlerAlt, this.keyHandler],
            buttons: [{
                handler: function() {
                    this.doSave();
                }.createDelegate(this),
                iconCls: 'save16',
                onShiftTabAction: function() {
                    var base_form = this.findById('EvnPSEditForm').getForm();

                    if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
                        this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                        this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                    }
                    else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                        this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                        this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                    }
                    else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                        this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                        this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                    }
                    else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                        this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                        this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                    }
                    else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
                        if ( !base_form.findField('Diag_pid').disabled ) {
                            base_form.findField('Diag_pid').focus(true);
                        }
                        else {
                            base_form.findField('MedStaffFact_pid').focus(true);
                        }
                    }
                    else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
                        this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                        this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                    }
                    else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
                        base_form.findField('EvnPS_IsDiagMismatch').focus(true);
                    }
                    else {
                        this.buttons[this.buttons.length - 1].focus();
                    }
                }.createDelegate(this),
                onTabAction: function() {
                    this.buttons[1].focus();
                }.createDelegate(this),
                tabIndex: this.tabindex + 61,
                text: BTN_FRMSAVE
            }, /*{ //Скрываем, ненужную на данный момент, кнопку http://redmine.swan.perm.ru/issues/23282
                handler: function() {
                    // Надо передать с формы: EvnPS_id, scope, button, callback
                    var base_form = this.findById('EvnPSEditForm').getForm();
                    var config = {};
                    config.Evn_id = base_form.findField('EvnPS_id').getValue();
                    config.Evn_IsSigned = base_form.findField('EvnPS_IsSigned').getValue();
                    config.scope = this;
                    config.callback = function(success) {
                        if (success) {
                            /*
                             this.setTitle(WND_HOSP_EPSVIEW);
                             this.enableEdit(false);
                             */
                            /*var btn = Ext.getCmp(this.id+'_BtnSign');
                            if (isSuperAdmin()) {
                                btn.setText('Отменить подпись');
                            } else {
                                btn.disable();
                                btn.setText('Документ подписан');
                            }*/
                            /*if (this.childPS) {
                             this.callback({evnPSData: response}, {opener: this.opener});
                             } else {
                             this.callback({ evnPSData: response });
                             }
                            this.hide();
                        }
                    }.createDelegate(this);

                     config.button = ;

                    //log(config);
                    signedDocument(config);
                }.createDelegate(this),
                id: this.id+'_BtnSign',
                iconCls: 'digital-sign16',
                tabIndex: this.tabindex + 37,
                text: BTN_FRMSIGN
            },*/ {
                handler: function() {
                    this.printEvnPS('1');
                }.createDelegate(this),
                iconCls: 'print16',
                onShiftTabAction: function() {
                    var base_form = this.findById('EvnPSEditForm').getForm();

                    if ( this.action != 'view' ) {
                        this.buttons[0].focus();
                    }
                    else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
                        this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                        this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                    }
                    else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                        this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                        this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                    }
                    else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                        this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                        this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                    }
                    else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                        this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                        this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                    }
                    else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
                        this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                        this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                    }
                    else {
                        this.buttons[this.buttons.length - 1].focus();
                    }
                }.createDelegate(this),
                onTabAction: function() {
                    this.buttons[this.buttons.length - 1].focus();
                }.createDelegate(this),
                tabIndex: this.tabindex + 62,
                text: BTN_FRMPRINT
            },{
                    handler: function() {
                        var evn_ps_id = base_form.findField('EvnPS_id').getValue();
						printBirt({
							'Report_FileName': 'FormaKBK_EvnPS.rptdesign',
							'Report_Params': '&paramEvnPS=' + evn_ps_id,
							'Report_Format': 'doc'
						});
                    }.createDelegate(this),
                    iconCls: 'print16',
                    hidden: !(getGlobalOptions().region.nick == 'kareliya'),
                    text: 'Печать КВК'
            },
            {
                text: '-'
            },
                HelpButton(this, -1),
                {
                    handler: function() {
                        this.onCancelAction();
                    }/*.createDelegate(this)*/,
                    scope: this,
                    iconCls: 'cancel16',
                    onShiftTabAction: function () {
                        this.buttons[1].focus();
                    }.createDelegate(this),
                    onTabAction: function() {
                        var base_form = this.findById('EvnPSEditForm').getForm();
                        if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
                            base_form.findField('EvnPS_IsCont').focus(true);
                        }
                        else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
                            this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                            this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                        }
                        else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
                            base_form.findField('PrehospToxic_id').focus(true);
                        }
                        else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                            this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                            this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                        }
                        else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                            this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                            this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                        }
                        else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                            this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                            this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                        }
                        else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
                            this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                            this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                        }
                        else if ( this.action != 'view' ) {
                            this.buttons[0].focus();
                        }
                        else {
                            this.buttons[1].focus();
                        }
                    }.createDelegate(this),
                    tabIndex: this.tabindex + 63,
                    text: BTN_FRMCANCEL
                }],
            items: [ new sw.Promed.PersonInfoPanel({
                button1OnHide: function() {
                    if ( this.action == 'view' ) {
                        this.buttons[this.buttons.length - 1].focus();
                    }
                    else {
                        this.findById('EvnPSEditForm').getForm().findField('EvnPS_NumCard').focus(true);
                    }
                }.createDelegate(this),
                listeners: {
                	'expand': function(p) {
						p.load({
							onExpand: true,
							PersonEvn_id: p.personEvnId,
							Person_id: p.personId,
							Server_id: p.serverId,
							Evn_setDT:p.Evn_setDT,
							callback: function(){
								this.addTextPersonInfoPanel();
							}.bind(this)
						});
					}.createDelegate(this)
                },
                button2Callback: function(callback_data) {
                    var form = this.findById('EvnPSEditForm');

                    var evn_ps_id = form.getForm().findField('EvnPS_id').getValue();
					var p = {};
					if(evn_ps_id>0 && form.getForm().findField('Person_id').getValue()==callback_data.Person_id){
					Ext.Ajax.request({
						 failure: function(response, options) {
							sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
						},
						success: function(response, options) {
							if (!Ext.isEmpty(response.responseText)) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								log(["SSDRWE",response_obj])
								if ( response_obj.success == false ) {
									form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
									form.getForm().findField('Server_id').setValue(callback_data.Server_id);
									p = { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id };
									if (callback_data.PersonEvn_id>0)
										p.PersonEvn_id = callback_data.PersonEvn_id;
									if (callback_data.Server_id>=0)
										p.Server_id =callback_data.Server_id;
								}else{
									form.getForm().findField('PersonEvn_id').setValue(response_obj[0].PersonEvn_id);
									form.getForm().findField('Server_id').setValue(response_obj[0].Server_id);
									p = { 
										Person_id: callback_data.Person_id,
										Server_id: response_obj[0].Server_id,
										PersonEvn_id:response_obj[0].PersonEvn_id,
										Evn_setDT:Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(),'d.m.Y')
									};
								}
								this.findById('EPSEF_PersonInformationFrame').load(p);
							}
						}.createDelegate(this),
						params: {
							Evn_id: evn_ps_id
						},
						url: '/?c=Person&m=getPersonEvnIdByEvnId'
					});}
				
                }.createDelegate(this),
                button2OnHide: function() {
                    this.findById('EPSEF_PersonInformationFrame').button1OnHide();
                }.createDelegate(this),
                button3OnHide: function() {
                    this.findById('EPSEF_PersonInformationFrame').button1OnHide();
                }.createDelegate(this),
                button4OnHide: function() {
                    this.findById('EPSEF_PersonInformationFrame').button1OnHide();
                }.createDelegate(this),
                button5OnHide: function() {
                    this.findById('EPSEF_PersonInformationFrame').button1OnHide();
                }.createDelegate(this),
				collapsible: true,
				collapsed: true,
				floatable: false,
                id: 'EPSEF_PersonInformationFrame',
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
                    id: 'EvnPSEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items: [
	                    {
                        name: 'accessType',
                        value: '',
                        xtype: 'hidden'
                    }, {
						name:'EvnSection_IsPaid',
						xtype:'hidden'
					}, {
						name:'EvnPS_IndexRep',
						xtype:'hidden'
					}, {
						name:'EvnPS_IndexRepInReg',
						xtype:'hidden'
					}, {
						name:'Lpu_id',
						xtype:'hidden'
					}, {
                        name: 'EvnPS_id',
                        value: 0,
                        xtype: 'hidden'
					}, {
                        name: 'EvnPS_IsTransit',
                        value: 0,
                        xtype: 'hidden'
                    },{
                        name: 'ChildLpuSection_id',
                        value: 0,
                        xtype: 'hidden'
                    },
	                    /*{
                        name: 'EvnPS_IsSigned',
                        value: null,
                        xtype: 'hidden'
                    },*/
	                    {
                        name: 'EvnPS_IsPrehospAcceptRefuse',
                        value: null,
                        xtype: 'hidden'
                    },
	                    {
                        name: 'EvnPS_PrehospAcceptRefuseDT',
                        value: null,
                        xtype: 'hidden'
                    },
	                    {
                        name: 'EvnPS_PrehospWaifRefuseDT',
                        value: null,
                        xtype: 'hidden'
                    },
	                    {
                        name: 'EvnDirection_id',
                        value: 0,
                        xtype: 'hidden'
                    },
	                    {
                        name: 'EvnDirectionHTM_id',
                        xtype: 'hidden'
                    },
	                    {
                        name: 'DirType_id',
                        xtype: 'hidden'
                    },
	                    {
                        name: 'EvnDirectionExt_id',
                        value: 0,
                        xtype: 'hidden'
                    },
	                    {
                        name: 'EvnQueue_id',
                        value: 0,
                        xtype: 'hidden'
                    },
	                    {
                        name: 'PrehospStatus_id',
                        value: 0,
                        xtype: 'hidden'
                    },
	                    {
                        name: 'Person_id',
                        value: 0,
                        xtype: 'hidden'
                    },
	                    {
                        name: 'PersonEvn_id',
                        value: 0,
                        xtype: 'hidden'
                    },
	                    {
                        name: 'Server_id',
                        value: -1,
                        xtype: 'hidden'
					}, {
						name: 'EvnPS_IsZNO',
						xtype: 'hidden'
					}, {
						name: 'EvnPS_IsZNORemove',
						xtype: 'hidden'
					},/*{
                     name: 'LpuSection_id',
                     value: 0,
                     xtype: 'hidden'
                     }, {
                     name: 'PrehospWaifRefuseCause_id',
                     value: 0,
                     xtype: 'hidden'
                     }, {
                     name: 'EvnPS_IsTransfCall',
                     value: 0,
                     xtype: 'hidden'
                     }, {
                     name: 'EvnPS_IsWaif',
                     value: 0,
                     xtype: 'hidden'
                     }, {
                     name: 'PrehospWaifArrive_id',
                     value: 0,
                     xtype: 'hidden'
                     }, {
                     name: 'PrehospWaifReason_id',
                     value: 0,
                     xtype: 'hidden'
                     },*/
                        new sw.Promed.Panel({
                            autoHeight: true,
                            bodyStyle: 'padding-top: 0.5em;',
                            border: true,
                            collapsible: true,
                            id: 'EPSEF_HospitalisationPanel',
                            layout: 'form',
                            listeners: {
                                'expand': function(panel) {
                                    // this.findById('EvnPSEditForm').getForm().findField('EvnPS_IsCont').focus(true);
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '1. Госпитализация',
                            items: [
	                            {
                                allowBlank: false,
                                fieldLabel: 'Переведен',
                                hiddenName: 'EvnPS_IsCont',
                                listeners: {
                                    'change': function(combo, newValue, oldValue) {
                                        var base_form = this.findById('EvnPSEditForm').getForm();
                                        var prehosp_direct_field = base_form.findField('PrehospDirect_id');
                                        var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
                                        var diag_did_field = base_form.findField('Diag_did');
                                        var record = combo.getStore().getById(newValue);

                                        var prehosp_direct_id = prehosp_direct_field.getValue();

                                        prehosp_direct_field.clearValue();
                                        prehosp_direct_field.getStore().clearFilter();
                                        diag_did_field.clearValue();

                                        if ( record ) {
                                            switch ( Number(record.get('YesNo_Code')) ) {
                                                case 0:
                                                    iswd_combo.setDisabled( this.action == 'view' );

                                                    base_form.findField('EvnPS_IsImperHosp').setAllowBlank(false);
                                                    base_form.findField('EvnPS_IsShortVolume').setAllowBlank(false);
                                                    base_form.findField('EvnPS_IsWrongCure').setAllowBlank(false);
                                                    base_form.findField('EvnPS_IsDiagMismatch').setAllowBlank(false);

                                                    break;

                                                case 1: //yes
                                                    prehosp_direct_field.getStore().filterBy(function(rec) {
                                                        if ( rec.get('PrehospDirect_Code').toString().inlist([ '1', '2' ])) {
                                                            return true;
                                                        }
                                                        else {
                                                            return false;
                                                        }
                                                    });

                                                    base_form.findField('EvnPS_IsImperHosp').setAllowBlank(true);
                                                    base_form.findField('EvnPS_IsShortVolume').setAllowBlank(true);
                                                    base_form.findField('EvnPS_IsWrongCure').setAllowBlank(true);
                                                    base_form.findField('EvnPS_IsDiagMismatch').setAllowBlank(true);

                                                    iswd_combo.setValue(1);
                                                    iswd_combo.disable();
                                                    break;
                                            }
                                        }

                                        /* убрал, т.к. нужно чтобы очищалось. (refs #7666)
                                         prehosp_direct_field.getStore().each(function(rec) {
                                         if ( rec.get('PrehospDirect_id') == prehosp_direct_id ) {
                                         prehosp_direct_field.setValue(prehosp_direct_id);
                                         }
                                         });
                                         */
                                        this.checkEvnDirectionAllowBlank();
                                        base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), base_form.findField('EvnPS_setDate').getValue());
                                        iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
                                    }.createDelegate(this),
                                    'keydown': function(inp, e) {
                                        if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
                                            e.stopEvent();
                                            this.buttons[this.buttons.length - 1].focus();
                                        }
                                    }.createDelegate(this)
                                },
                                tabIndex: this.tabindex + 1,
                                value: 1,
                                width: 70,
                                xtype: 'swyesnocombo'
                            },
	                            {
                                allowBlank: false,
								autoCreate: { tag: "input", type: "text", maxLength: "50", autocomplete: "off" },
                                enableKeyEvents: true,
                                fieldLabel: '№ медицинской карты',
                                listeners: {
                                    'keydown': function(inp, e) {
                                        switch ( e.getKey() ) {
                                            case Ext.EventObject.F4:
                                                e.stopEvent();
                                                this.getEvnPSNumber();
                                                break;
                                        }
                                    }.createDelegate(this)
                                },
								maxLength: 50,
                                name: 'EvnPS_NumCard',
                                onTriggerClick: function() {
                                    this.getEvnPSNumber();
                                }.createDelegate(this),
                                tabIndex: this.tabindex + 2,
                                triggerClass: 'x-form-plus-trigger',
                                validateOnBlur: false,
                                width: 300,
                                xtype: 'trigger'
                            },
	                            {
                                allowBlank: false,
								useCommonFilter: true,
                                tabIndex: this.tabindex + 3,
                                width: 300,
                                xtype: 'swpaytypecombo',
                                listeners: {
                                    'change': function(combo) {
                                        this.checkEvnDirectionAllowBlank();
										if ( getRegionNick() == 'ekb' ) {
											this.reloadUslugaComplexField();
											this.filterLpuSectionProfile();
										}
                                    }.createDelegate(this)
                                }
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
                                        fieldLabel: 'Дата поступления',
                                        format: 'd.m.Y',
										id: this.id + 'EvnPS_setDate',
                                        listeners: {
                                            'change': function(field, newValue, oldValue) {

												/*if (!Ext.isEmpty(newValue)) {
													this.filterDiagByDate();
												}*/
                                                if (blockedDateAfterPersonDeath('personpanelid', 'EPSEF_PersonInformationFrame', field, newValue, oldValue)) return;
												var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
                                                var base_form = this.findById('EvnPSEditForm').getForm();

												var EvnPS_OutcomeDate = base_form.findField('EvnPS_OutcomeDate').getValue();
                                                var lpu_section_did = base_form.findField('LpuSection_did').getValue();
                                                var lpu_section_pid = base_form.findField('LpuSection_pid').getValue();
                                                var med_staff_fact_pid = base_form.findField('MedStaffFact_pid').getValue();

                                                base_form.findField('LpuSection_did').clearValue();
                                                base_form.findField('LpuSection_pid').clearValue();
                                                base_form.findField('MedStaffFact_pid').clearValue();

												var age;
												var WithoutChildLpuSectionAge = false;
												var Person_Birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');

												var LpuSectionFilters = {
													isStac: (base_form.findField('EvnPS_IsCont').getValue() == 2)
												}

                                                if ( !newValue ) {
													age = swGetPersonAge(Person_Birthday, new Date());
												}
												else {
													age = swGetPersonAge(Person_Birthday, newValue);
													LpuSectionFilters.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
													this.setMKB();
                                                }
                                                
                                                //PROMEDWEB-9115
                                                //Грузим подотделения в т.ч.
                                                LpuSectionFilters.allowLowLevel = 'all';
												setLpuSectionGlobalStoreFilter(LpuSectionFilters);
												base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

												if ( age >= 18 && !isUfa ) {
													WithoutChildLpuSectionAge = true;
												}

												LpuSectionFilters = {
													isStacReception: true,
													WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
												}

												var MedStaffFactFilters = {
													EvnClass_SysNick: 'EvnSection',
													isStac: (getRegionNick() == 'krym') ? false : true,
													isPriemMedPers: (getRegionNick() == 'kareliya') // Только Карелия https://redmine.swan.perm.ru/issues/40561
												}

												if ( newValue ) {
													LpuSectionFilters.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
													MedStaffFactFilters.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
												}

												if ( getRegionNick() == 'perm' && EvnPS_OutcomeDate ) {
													LpuSectionFilters.onDate = Ext.util.Format.date(EvnPS_OutcomeDate, 'd.m.Y');
													MedStaffFactFilters.onDate = Ext.util.Format.date(EvnPS_OutcomeDate, 'd.m.Y');
												}

												//console.log('Фильтруем на дату: ' + MedStaffFactFilters.onDate);
                                                
												setLpuSectionGlobalStoreFilter(LpuSectionFilters);
												base_form.findField('LpuSection_pid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

												setMedStaffFactGlobalStoreFilter(MedStaffFactFilters);
												base_form.findField('MedStaffFact_pid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

                                                if ( base_form.findField('LpuSection_did').getStore().getById(lpu_section_did) ) {
                                                    base_form.findField('LpuSection_did').setValue(lpu_section_did);
                                                }

                                                if ( base_form.findField('LpuSection_pid').getStore().getById(lpu_section_pid) ) {
                                                    base_form.findField('LpuSection_pid').setValue(lpu_section_pid);
													base_form.findField('LpuSection_pid').fireEvent('change', base_form.findField('LpuSection_pid'), lpu_section_pid);
                                                } else {
													base_form.findField('LpuSection_pid').fireEvent('change', base_form.findField('LpuSection_pid'), null);
												}

                                                if ( base_form.findField('MedStaffFact_pid').getStore().getById(med_staff_fact_pid) ) {
                                                    base_form.findField('MedStaffFact_pid').setValue(med_staff_fact_pid);
													base_form.findField('MedStaffFact_pid').fireEvent('change', base_form.findField('MedStaffFact_pid'), base_form.findField('MedStaffFact_pid').getValue());
                                                }

                                                // Если дата госпитализации пустая или дата направления больше даты госпитализации,
                                                // то очищаем данные по направлению
                                                if ( !newValue || base_form.findField('EvnDirection_setDate').getValue() > newValue ) {
                                                    base_form.findField('EvnDirection_id').setValue(0);
                                                    base_form.findField('EvnDirectionHTM_id').setValue(null);
                                                    base_form.findField('EvnDirection_Num').setValue('');
                                                    base_form.findField('EvnDirection_setDate').setRawValue('');
                                                    base_form.findField('LpuSection_did').clearValue();
                                                    base_form.findField('Org_did').clearValue();
													base_form.findField('Org_did').fireEvent('change', base_form.findField('Org_did'), base_form.findField('Org_did').getValue());
                                                    base_form.findField('Diag_did').clearValue();
                                                }

												if (getRegionNick() == 'perm') {
													win.filterLpuSectionProfile();
												}

												base_form.findField('Diag_pid').setFilterByDate(newValue);
												this.setPrehospArriveAllowBlank();
												this.setDiagEidAllowBlank();
												this.refreshFieldsVisibility(['TumorStage_id', 'EvnPS_HTMTicketNum', 'DiagSetPhase_did', 'DiagSetPhase_pid']);
                                            }.createDelegate(this)
                                        },
                                        name: 'EvnPS_setDate',
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                        selectOnFocus: true,
                                        tabIndex: this.tabindex + 4,
                                        width: 100,
                                        xtype: 'swdatefield'
                                    }]
                                }, {
                                    border: false,
                                    labelWidth: 50,
                                    layout: 'form',
                                    items: [{
										allowBlank: false,
                                        fieldLabel: 'Время',
                                        listeners: {
                                            'keydown': function (inp, e) {
                                                if ( e.getKey() == Ext.EventObject.F4 ) {
                                                    e.stopEvent();
                                                    inp.onTriggerClick();
                                                }
                                            }
                                        },
                                        name: 'EvnPS_setTime',
										id: this.id + 'EvnPS_setTime',
                                        onTriggerClick: function() {
                                            var base_form = this.findById('EvnPSEditForm').getForm();
                                            var time_field = base_form.findField('EvnPS_setTime');

                                            if ( time_field.disabled ) {
                                                return false;
                                            }

                                            setCurrentDateTime({
                                                dateField: base_form.findField('EvnPS_setDate'),
                                                loadMask: true,
                                                setDate: true,
                                                setDateMaxValue: true,
                                                setDateMinValue: false,
                                                setTime: true,
                                                timeField: time_field,
                                                windowId: this.id,
												callback: function() {
													base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), base_form.findField('EvnPS_setDate').getValue());
												}
                                            });
                                        }.createDelegate(this),
                                        plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
                                        tabIndex: this.tabindex + 5,
                                        validateOnBlur: false,
                                        width: 60,
                                        xtype: 'swtimefield'
                                    }]
                                }]
                            },
	                            {
                                autoHeight: true,
                                style: 'padding: 0px;',
                                title: 'Кем направлен',
                                width: 730,
                                xtype: 'fieldset',

                                items: [{
                                    border: false,
                                    layout: 'column',
                                    items: [{
                                        border: false,
                                        layout: 'form',
                                        width: 300,
                                        items: [ new sw.Promed.SwYesNoCombo({
                                            fieldLabel: 'С электронным направлением',
                                            hiddenName: 'EvnPS_IsWithoutDirection',
                                            value: 2,
                                            allowBlank: false,
                                            tabIndex: this.tabindex + 5,
                                            width: 60,
                                            listeners:
                                            {
                                                'change': function (iswd_combo, newValue, oldValue)
                                                {
                                                    var base_form = this.findById('EvnPSEditForm').getForm();
                                                    if ( newValue == 2 ) {  // да
                                                        // поля заполняются из эл.направления
                                                        base_form.findField('EvnDirection_Num').disable();
                                                        //gloEvnDirection_Num = base_form.findField('EvnDirection_Num');
                                                        base_form.findField('EvnDirection_setDate').disable();
                                                        base_form.findField('LpuSection_did').disable();
                                                        base_form.findField('Org_did').disable();
                                                        base_form.findField('Diag_did').disable();
                                                        base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue());
                                                    }
                                                    else {
                                                        base_form.findField('EvnDirection_Num').setDisabled( this.action == 'view' );
                                                        base_form.findField('EvnDirection_setDate').setDisabled( this.action == 'view' );
                                                        base_form.findField('LpuSection_did').setDisabled( this.action == 'view' );
                                                        base_form.findField('Org_did').setDisabled( this.action == 'view' );
                                                        base_form.findField('Diag_did').setDisabled( this.action == 'view' );
                                                        base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue());
                                                        this._fillHtmData();
                                                        base_form.findField('DirType_id').setValue(null);
                                                        this.checkVMPFieldEnabled();
                                                    }
                                                }.createDelegate(this),
                                                'select': function(combo, record, index) {
                                                    combo.fireEvent('change', combo, record.get(combo.valueField));
                                                }.createDelegate(this)
                                            }
                                        })]
                                    }, {
										border: false,
										layout: 'column',
										items: [{
											border: false,
											layout: 'form',
											width: 200,
											items: [{
												handler: function () {
													this.openEvnDirectionSelectWindow();
												}.createDelegate(this),
												iconCls: 'add16',
												id: 'EPSEF_EvnDirectionSelectButton',
												tabIndex: this.tabindex + 6,
												text: 'Выбрать направление',
												tooltip: 'Выбор направления',
												xtype: 'button'
											}]
										}, {
											border: false,
											layout: 'form',
											width: 200,
											items: [ win.ExtDirButton = new Ext.Button({
												iconCls: 'add16',
												id: 'EPSEF_ExtDirectionSelectButton',
												tabIndex: this.tabindex + 7,
												text: 'Внешнее направление',
												tooltip: 'Внешнее направление',
												hidden: true,
												handler: function() {
													var win = this;
													var base_form = this.findById('EvnPSEditForm').getForm();
													Ext.Ajax.request({
														callback: function(options, success, response) {
															if ( success ) {
																var response_obj = Ext.util.JSON.decode(response.responseText);
																if(response_obj && response_obj.length>0) {
																	win.openEvnDirectionSelectWindow();
																} else {//открывается мастер направлений
																	var personData = new Object();
																	person_information = win.findById('EPSEF_PersonInformationFrame');
																	personData.Person_IsDead = person_information.getFieldValue('Person_deadDT') != null;
																	personData.Person_Firname = person_information.getFieldValue('Person_Firname');
																	personData.Person_id = base_form.findField('Person_id').getValue();
																	personData.Person_Surname = person_information.getFieldValue('Person_Surname');
																	personData.Person_Secname = person_information.getFieldValue('Person_Secname');
																	personData.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
																	personData.Server_id = base_form.findField('Server_id').getValue();
																	personData.Person_Birthday = person_information.getFieldValue('Person_Birthday');															

																	var directionData = {
																		LpuUnitType_SysNick: null
																		, EvnQueue_id: null
																		, QueueFailCause_id: null
																		, Lpu_did: null // ЛПУ куда направляем
																		, LpuUnit_did: null
																		, LpuSection_did: null
																		, EvnUsluga_id: null
																		, LpuSection_id: null
																		, EvnDirection_pid: null
																		, EvnPrescr_id: null
																		, PrescriptionType_Code: null
																		, DirType_id: null
																		, LpuSectionProfile_id: null
																		, LpuUnitType_id: null
																		, Diag_id: null
																		, MedStaffFact_id: null
																		, MedPersonal_id: null
																		, MedPersonal_did: null
																		, withDirection: 3
																		, EvnDirection_IsReceive: 2
																		, fromBj: false
																	};
																	if (personData != null) {
																		var isDead = (this.personData && this.personData.Person_IsDead && this.persData.Person_IsDead == 'true');

																		if (isDead) {
																			sw.swMsg.alert(langs('Ошибка'), langs('Запись невозможна, т.к. у пациента стоит дата смерти.'));
																			return false;
																		}

																		var params = {
																			userMedStaffFact: null,
																			isDead: false,
																			type: 'ExtDirKVS',
																			personData: personData,
																			directionData: directionData,
																			callback: function() { this.hide(); },
																			onDirection: function (dataEvnDirection_id) {
																				var EvnDirId = false;
																				if(dataEvnDirection_id.EvnDirection_id) {
																					EvnDirId = dataEvnDirection_id.EvnDirection_id;
																				} else {
																					if(dataEvnDirection_id.evnDirectionData && dataEvnDirection_id.evnDirectionData.EvnDirection_id){
																						EvnDirId = dataEvnDirection_id.evnDirectionData.EvnDirection_id;
																					}
																				}
																				if(!EvnDirId) {
																					sw.swMsg.alert(langs('Сообщение'), langs('Мастер выписки направлений не вернул идентификатор направления.'));
																					return false;
																				}
																				Ext.Ajax.request({
																					params: {EvnDirection_id: EvnDirId },
																					url: '/?c=EvnDirection&m=getDataEvnDirection',
																					callback: function(options, success, response) {
																						if ( success ) {
																							var response_obj = Ext.util.JSON.decode(response.responseText);
																							if(response_obj[0]) {
																								var data = response_obj[0];
																								var dataSet = {
																									EvnDirection_id: null, //data.EvnDirection_id,
																									EvnDirectionExt_id: data.EvnDirection_id,
																									//EvnDirectionHTM_id: data.EvnDirectionHTM_id
																									LpuSection_id: data.LpuSection_id,
																									Org_did: data.Org_sid,
																									EvnDirection_Num: data.EvnDirection_Num,
																									EvnDirection_setDate: data.EvnDirection_setDate,
																									Diag_did: data.Diag_id
																								};
																								win.setDirection(dataSet);
																							}
																						}
																					}
																				});
																			}
																		};

																		getWnd('swDirectionMasterWindow').show(params);
																	}
																}
															}
															else {
																sw.swMsg.alert('Ошибка', 'Ошибка при получении направлений КВС');
															}
														},
														params: {Person_id: base_form.findField('Person_id').getValue(), useCase: 'choose_for_evnps_stream_input'},
														url: '/?c=EvnDirection&m=loadEvnDirectionList'
													});

												}.createDelegate(this)												
											})]
										}]
                                    }]
                                }, {
                                    hiddenName: 'PrehospDirect_id',
                                    lastQuery: '',
                                    listeners: {
                                        'change': function(combo, newValue, oldValue) {
                                            var base_form = this.findById('EvnPSEditForm').getForm();
                                            var isPerm = (getRegionNick() == 'perm');
                                            var isUfa = (getRegionNick() == 'ufa');
                                            var isKareliya = (getRegionNick() == 'kareliya');
                                            var omsSprTerrCode = this.findById('EPSEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');

                                            var evn_direction_set_date_field = base_form.findField('EvnDirection_setDate');
                                            var evn_direction_num_field = base_form.findField('EvnDirection_Num');
                                            var lpu_section_combo = base_form.findField('LpuSection_did');
                                            var org_combo = base_form.findField('Org_did');
                                            var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');

											var lpu_section_id = lpu_section_combo.getValue();

                                            // this.findById('EPSEF_EvnDirectionSelectButton').disable();

                                            base_form.findField('EvnDirection_id').setValue(0);
                                            base_form.findField('EvnDirectionHTM_id').setValue(null);
                                            evn_direction_set_date_field.setValue(null);
                                            evn_direction_num_field.setValue(null);
											lpu_section_combo.clearValue();
                                            org_combo.clearValue();
											org_combo.fireEvent('change', org_combo, org_combo.getValue());

                                            base_form.findField('MedStaffFact_did').setContainerVisible(false);
                                            base_form.findField('MedStaffFact_did').disable();
                                            base_form.findField('MedStaffFact_TFOMSCode').setContainerVisible(false);
											base_form.findField('MedStaffFact_TFOMSCode').setAllowBlank(true);
                                            base_form.findField('MedStaffFact_TFOMSCode').disable();

											base_form.findField('LpuSection_did').disableLinkedElements();
											base_form.findField('MedStaffFact_did').disableParentElement();

                                            base_form.findField('Diag_did').setAllowBlank(true);
                                            base_form.findField('Diag_pid').setAllowBlank(true);

                                            var record = base_form.findField('EvnPS_IsCont').getStore().getById(base_form.findField('EvnPS_IsCont').getValue());
                                            var evn_ps_is_cont = false;

                                            if ( record && record.get('YesNo_Code') == 1 ) {
                                                evn_ps_is_cont = true;
                                            }

                                            record = combo.getStore().getById(newValue);

                                            /* Кем направлен
											PrehospDirect_Code	PrehospDirect_Name
											1	Отделение МО
											2	Другое МО
											3	Другая организация
											4	Военкомат
											5	Скорая помощь
											6	Администрация
											7	Пункт помощи на дому
											*/
                                            var prehosp_direct_code = (record && record.get('PrehospDirect_Code')) || null;

                                            this.refreshFieldsVisibility(['LpuSection_did', 'Org_did', 'PrehospDirect_id']);

                                            if ( Ext.isEmpty(prehosp_direct_code) ) {

                                            	// ---------------------------------------------------------------------
                                            	// refs #127318 - поля «№ направления», «Дата направления» должны быть
												// доступны для редактирования, если в поле «С электронным направлением» выбрано «Нет».
                                            	// evn_direction_set_date_field.disable();
												// evn_direction_num_field.disable();
												// ---------------------------------------------------------------------

												// lpu_section_combo.disable();
												//org_combo.disable();
                                                //iswd_combo.setValue(1);
                                                //iswd_combo.disable();

                                                // https://redmine.swan.perm.ru/issues/4614
                                                if (isUfa == true)
                                                {
                                                    base_form.findField('Diag_pid').setAllowBlank(true);
                                                }

                                                return false;
                                            }

											if (Number(prehosp_direct_code).inlist([1,2])) {
												evn_direction_set_date_field.setAllowBlank(false);
												evn_direction_num_field.setAllowBlank(false);
											} else {
												evn_direction_set_date_field.setAllowBlank(true);
												evn_direction_num_field.setAllowBlank(true);
											}

                                            // https://redmine.swan.perm.ru/issues/4549
                                            /*if ( prehosp_direct_code && isPerm == true && omsSprTerrCode > 100 ) {
                                                base_form.findField('Diag_did').setAllowBlank(false);
                                            }*/

                                            /*prehosp_direct_code
                                             1	Отделение ЛПУ
                                             2	Другое ЛПУ
                                             3	Другая организация
                                             4	Военкомат
                                             5	Скорая помощь
                                             6	Администрация
                                             7	Пункт помощи на дому
                                             */
                                            if ( prehosp_direct_code == 1 || prehosp_direct_code == 2 ) {
                                                iswd_combo.setDisabled( this.action == 'view' );
                                                /*
                                                 if(iswd_combo.getValue() == 2)
                                                 this.findById('EPSEF_EvnDirectionSelectButton').enable();
                                                 */
                                                evn_direction_set_date_field.disable();
                                                evn_direction_num_field.disable();
                                                // lpu_section_combo.disable();
                                                // org_combo.disable();
                                            } else {
                                                iswd_combo.setValue(1);
                                                iswd_combo.disable();
                                                evn_direction_set_date_field.setDisabled( this.action == 'view' );
                                                evn_direction_num_field.setDisabled( this.action == 'view' );
                                                // lpu_section_combo.setDisabled( this.action == 'view' );
                                                // org_combo.setDisabled( this.action == 'view' );
                                            }
                                            switch ( Number(prehosp_direct_code) ) {
                                                case 1:

													if (getRegionNick().inlist([ 'ekb', 'perm' ]) && iswd_combo.getValue() == 1) {
														base_form.findField('MedStaffFact_did').setContainerVisible(true);
														base_form.findField('MedStaffFact_did').setDisabled( this.action == 'view' );

														if ( getRegionNick().inlist(['ekb']) ) {
															base_form.findField('MedStaffFact_TFOMSCode').setContainerVisible(true);
															base_form.findField('MedStaffFact_TFOMSCode').setAllowBlank(getRegionNick() != 'ufa');
															base_form.findField('MedStaffFact_TFOMSCode').setDisabled( this.action == 'view' );
														}

														base_form.findField('LpuSection_did').enableLinkedElements();
														base_form.findField('MedStaffFact_did').enableParentElement();

														this.loadMedStaffFactDidCombo();
														
													}else if(getRegionNick().inlist([ 'buryatiya' ]) ){
														
														if(iswd_combo.getValue() == 1){
															/* С электронным направлением == нет */
															base_form.findField('MedStaffFact_did').setContainerVisible(true);
															base_form.findField('MedStaffFact_did').setDisabled( this.action == 'view' );
															base_form.findField('MedStaffFact_TFOMSCode').setContainerVisible(true);
															base_form.findField('MedStaffFact_TFOMSCode').setDisabled( this.action == 'view' );

															if ( getRegionNick().inlist(['ekb']) ) {
																base_form.findField('MedStaffFact_TFOMSCode').setAllowBlank(true);
															}
															
														}else if(iswd_combo.getValue() == 2){
															/* С электронным направлением == да */
															base_form.findField('MedStaffFact_did').setContainerVisible(true);
															base_form.findField('MedStaffFact_did').setDisabled( true );
															base_form.findField('MedStaffFact_TFOMSCode').setContainerVisible(true);
															base_form.findField('MedStaffFact_TFOMSCode').setDisabled( true );
														}
														
														base_form.findField('LpuSection_did').enableLinkedElements();
														base_form.findField('MedStaffFact_did').enableParentElement();

														this.loadMedStaffFactDidCombo();
													}
													
                                                    if ( lpu_section_id ) {
                                                        lpu_section_combo.setValue(lpu_section_id);
                                                    }
                                                    evn_direction_set_date_field.setDisabled( this.action == 'view' );
                                                    evn_direction_num_field.setDisabled( this.action == 'view' );
                                                    // lpu_section_combo.setDisabled( this.action == 'view' );
                                                    // lpu_section_combo.setAllowBlank(false);
                                                    // org_combo.disable();
                                                    // org_combo.setAllowBlank(true);

                                                    break;

                                                case 2:

													if (getRegionNick().inlist([ 'ekb', 'perm' ]) && iswd_combo.getValue() == 1) {
														base_form.findField('MedStaffFact_did').setContainerVisible(true);
														base_form.findField('MedStaffFact_did').setDisabled( this.action == 'view' );

														if ( getRegionNick().inlist([ 'ekb' ])){
															base_form.findField('MedStaffFact_TFOMSCode').setContainerVisible(true);
															base_form.findField('MedStaffFact_TFOMSCode').setAllowBlank(getRegionNick() != 'ufa');
															base_form.findField('MedStaffFact_TFOMSCode').setDisabled( this.action == 'view' );
														}
													}else if(getRegionNick().inlist([ 'buryatiya' ])){
														
														if(iswd_combo.getValue() == 1){
															/* С электронным направлением == нет */
															base_form.findField('MedStaffFact_did').setContainerVisible(true);
															base_form.findField('MedStaffFact_did').setDisabled( this.action == 'view' );
															base_form.findField('MedStaffFact_TFOMSCode').setContainerVisible(true);
															base_form.findField('MedStaffFact_TFOMSCode').setDisabled( this.action == 'view' );

														}else if(iswd_combo.getValue() == 2){
															/* С электронным направлением == да */
															base_form.findField('MedStaffFact_did').setContainerVisible( true );
															base_form.findField('MedStaffFact_did').setDisabled( true );
															base_form.findField('MedStaffFact_TFOMSCode').setContainerVisible( true );
															base_form.findField('MedStaffFact_TFOMSCode').setDisabled( true );
														}
													}


                                                    evn_direction_set_date_field.setDisabled( this.action == 'view' );
                                                    evn_direction_num_field.setDisabled( this.action == 'view' );
                                                    // lpu_section_combo.disable();
                                                    // lpu_section_combo.setAllowBlank(true);
                                                    // org_combo.setDisabled( this.action == 'view' );
                                                    // org_combo.setAllowBlank(false);
                                                    break;

                                                case 3:
                                                case 4:
                                                case 5:
                                                case 6:
                                                    evn_direction_set_date_field.setDisabled( this.action == 'view' );
                                                    evn_direction_num_field.setDisabled( this.action == 'view' );
                                                    // lpu_section_combo.disable();
                                                    // lpu_section_combo.setAllowBlank(true);
                                                    // org_combo.setDisabled( this.action == 'view' );
                                                    // org_combo.setAllowBlank(true);
                                                    break;

                                                default:
                                                    evn_direction_set_date_field.disable();
                                                    evn_direction_num_field.disable()
                                                    // lpu_section_combo.disable();
                                                    // lpu_section_combo.setAllowBlank(true);
                                                    // org_combo.disable();
                                                    // org_combo.setAllowBlank(true);
                                                    break;
                                            }
                                            if (2 == Number(iswd_combo.getValue())) {
                                                evn_direction_set_date_field.disable();
                                                evn_direction_num_field.disable()
                                                // lpu_section_combo.disable();
                                                // lpu_section_combo.setAllowBlank(true);
                                                // org_combo.disable();
                                                // org_combo.setAllowBlank(true);
                                            }

											this.checkEvnDirectionAllowBlank();
                                        }.createDelegate(this),
                                        'select': function(combo, record, index) {
                                            combo.fireEvent('change', combo, record.get(combo.valueField));
                                        }.createDelegate(this)
                                    },
                                    tabIndex: this.tabindex + 7,
                                    width: 300,
                                    xtype: 'swprehospdirectcombo'
                                }, {
                                    hiddenName: 'LpuSection_did',
									id: this.id + 'LpuSectionDid',
									linkedElements: [
										this.id + 'MedStaffFactDid'
									],
                                    tabIndex: this.tabindex + 8,
                                    width: 500,
                                    xtype: 'swlpusectionglobalcombo'
                                }, {
                                    displayField: getRegionNick()=='ekb' ? 'Org_Nick' : 'Org_Name',
                                    editable: false,
                                    enableKeyEvents: true,
                                    fieldLabel: 'Организация',
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
                                        },
										'change': function() {
                                        	win.loadMedStaffFactDidCombo();
										}
                                    },
                                    mode: 'local',
                                    onTrigger1Click: function() {
                                        var base_form = this.findById('EvnPSEditForm').getForm();
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
                                                
											case 8:
												org_type = 'court';
												break;
												
											case 9:
												org_type = 'patronage';
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
											onDate: base_form.findField('EvnDirection_setDate').getValue(),
                                            onSelect: function(org_data) {
                                                if ( org_data.Org_id > 0 ) {
                                                    combo.getStore().loadData([{
                                                        Org_id: org_data.Org_id,
                                                        Org_Name: org_data.Org_Name,
                                                        Org_Nick: org_data.Org_Nick,
                                                        OrgType_SysNick: org_data.OrgType_SysNick
                                                    }]);
                                                    combo.setValue(org_data.Org_id);
													combo.fireEvent('change', combo, combo.getValue());
                                                    getWnd('swOrgSearchWindow').hide();
                                                    combo.collapse();
                                                }
												if (prehosp_direct_code == 2) {
													this.checkOtherLpuDirection();
												}
											}.createDelegate(this)
                                        });
                                    }.createDelegate(this),
									onTrigger2Click: function() {
										if ( !this.disabled ) this.clearValue();

										var combo = this;
										combo.fireEvent('change', combo, combo.getValue());

										var prehosp_direct_code = base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code');
										if (prehosp_direct_code == 2) {
											win.checkOtherLpuDirection();
										}
									},
                                    store: new Ext.data.JsonStore({
                                        autoLoad: false,
                                        fields: [
                                            {name: 'Org_id', type: 'int'},
                                            {name: 'Org_Name', type: 'string'},
                                            {name: 'Org_Nick', type: 'string'},
                                            {name: 'OrgType_SysNick', type: 'string'}
                                        ],
                                        key: 'Org_id',
                                        sortInfo: {
                                            field: getRegionNick()=='ekb' ? 'Org_Nick' : 'Org_Name'
                                        },
                                        url: C_ORG_LIST
                                    }),
                                    tabIndex: this.tabindex + 9,
                                    tpl: new Ext.XTemplate(
                                        '<tpl for="."><div class="x-combo-list-item">',
                                        getRegionNick()=='ekb'? '{Org_Nick}' : '{Org_Name}',
                                        '</div></tpl>'
                                    ),
                                    trigger1Class: 'x-form-search-trigger',
                                    triggerAction: 'none',
                                    valueField: 'Org_id',
                                    width: 500,
                                    xtype: 'swbaseremotecombo'
                                }, {
									tabIndex: this.tabindex + 9,
                                	fieldLabel: 'Направивший врач',
									hiddenName: 'MedStaffFact_did',
									id: this.id + 'MedStaffFactDid',
									listWidth: 650,
									parentElementId: this.id + 'LpuSectionDid',
									width: 500,
									xtype: 'swmedstafffactglobalcombo'
								}, {
									tabIndex: this.tabindex + 9,
									fieldLabel: 'Код направившего врача',
									name: 'MedStaffFact_TFOMSCode',
									allowDecimals: false,
									allowNegative: false,
									allowBlank: (getRegionNick() != 'ekb'),
									autoCreate: {tag: "input", maxLength: "14", autocomplete: "off"},
									xtype: 'numberfield'
								}, {
                                    border: false,
                                    layout: 'column',
                                    items: [{
                                        border: false,
                                        layout: 'form',
                                        items: [{
                                            fieldLabel: '№ направления',
											maskRe: /[0-9]/,
											regex: /^[0-9]*$/,
                                            name: 'EvnDirection_Num',
                                            tabIndex: this.tabindex + 10,
                                            width: 150,
                                            autoCreate: {tag: "input", type: "text", maxLength: "16", autocomplete: "off"},
											xtype: 'textfield',
											listeners: {
                                            	change: function (combo, newValue, oldValue) {
                                            		this.setMedicalCareFormType();
												}.createDelegate(this)
											}
                                        }]
                                    }, {
                                        border: false,
                                        labelWidth: 200,
                                        layout: 'form',
                                        items: [{
                                            fieldLabel: 'Дата направления',
                                            format: 'd.m.Y',
                                            name: 'EvnDirection_setDate',
                                            plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                            tabIndex: this.tabindex + 11,
											listeners: {
												'change': function (combo, newValue, oldValue) {
													var base_form = win.findById('EvnPSEditForm').getForm();
													base_form.findField('Diag_did').setFilterByDate(newValue);

													var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
													var prehosp_direct_id = prehosp_direct_combo.getValue();
													var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);
													var prehosp_direct_code = record?record.get('PrehospDirect_Code'):null;

													if (prehosp_direct_code == 2) {
														win.checkOtherLpuDirection();
													}
												}
											},
                                            width: 100,
                                            xtype: 'swdatefield'
                                        }]
                                    }]
                                }]
                            },
	                            {
                                autoHeight: true,
                                style: 'padding: 0px;',
                                title: 'Кем доставлен',
                                width: 730,
                                xtype: 'fieldset',

                                items: [{
                                	allowBlank: getRegionNick() != 'krym',
                                    fieldLabel: 'Кем доставлен',
                                    value: 1,
                                    hiddenName: 'PrehospArrive_id',
                                    listeners: {
                                        'change': function(combo, newValue, oldValue) {
											win.setMedicalCareFormType();

                                            var base_form = this.findById('EvnPSEditForm').getForm();

                                            base_form.findField('EvnPS_CodeConv').setValue('');
                                            base_form.findField('EvnPS_NumConv').setValue('');
                                            if ( this.action == 'add' ) {
												base_form.findField('EvnPS_IsPLAmbulance').setValue(1);
											}
											base_form.findField('CmpCallCard_id').hideContainer();

                                            var record = combo.getStore().getById(newValue);

                                            if ( !record || record.get('PrehospArrive_Code') == 1 ) {
                                                base_form.findField('EvnPS_CodeConv').disable();
                                                base_form.findField('EvnPS_NumConv').disable();
                                                base_form.findField('EvnPS_IsPLAmbulance').disable();
                                            }
                                            else if ( record.get('PrehospArrive_Code') == 2 ) {
                                                base_form.findField('EvnPS_CodeConv').setDisabled( this.action == 'view' );
                                                base_form.findField('EvnPS_NumConv').setDisabled( this.action == 'view' );
                                                base_form.findField('EvnPS_IsPLAmbulance').setDisabled( this.action == 'view' );
                                                base_form.findField('CmpCallCard_id').showContainer();
                                                if ( this.action == 'add' && base_form.findField('PrehospDirect_id').getValue() == 5 ) {
													base_form.findField('EvnPS_IsPLAmbulance').setValue(2);
												}
                                            }
                                            else {
                                                base_form.findField('EvnPS_CodeConv').setDisabled( this.action == 'view' );
                                                base_form.findField('EvnPS_NumConv').setDisabled( this.action == 'view' );
                                                base_form.findField('EvnPS_IsPLAmbulance').disable();
                                            }
                                        }.createDelegate(this)
                                    },
                                    tabIndex: this.tabindex + 12,
                                    width: 300,
                                    xtype: 'swprehosparrivecombo'
                                }, {
                                	fieldLabel: 'Номер талона вызова',
									hiddenName: 'CmpCallCard_id',
									tabIndex: this.tabindex + 12,
									width: 300,
									listWidth: 400,
									beforeLoadStore: function(store, options) {
										var base_form = this.findById('EvnPSEditForm').getForm(),
											EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();
										options.params.date = Ext.util.Format.date(EvnPS_setDate, 'd.m.Y');
									}.createDelegate(this),
									beforeBlur: function() {
										return true;
									},
									listeners: {
										'select': function(combo, record, index) {
											if (record && record.get('CmpCallCard_id') > 0) {
												combo.setRawValue(combo.fieldTpl.apply(record.data));
											}
										}.createDelegate(this),
										'change': function() {
											if(getRegionNick()=='ufa') {
												win.loadScaleFieldset();
											}
										}
									},
									xtype: 'swcmpcallcardautocompletecombo'
								}, {
                                    fieldLabel: 'Код',
                                    maxLength: 10,
                                    name: 'EvnPS_CodeConv',
                                    tabIndex: this.tabindex + 13,
                                    width: 150,
                                    xtype: 'textfield'
                                }, {
                                    fieldLabel: 'Номер наряда',
                                    maxLength: 10,
                                    name: 'EvnPS_NumConv',
                                    tabIndex: this.tabindex + 14,
                                    width: 150,
                                    xtype: 'textfield'
                                },{
                                    comboSubject: 'YesNo',
                                    disabled: true,
                                    fieldLabel: 'Талон передан на ССМП',
                                    hiddenName: 'EvnPS_IsPLAmbulance',
                                    tabIndex: this.tabindex + 15,
                                    width: 150,
                                    value: 1,
                                    xtype: 'swcommonsprcombo'
                                }]
                            },
	                            new sw.Promed.swDiagPanel({
                                labelWidth: 180,
                                phaseDescrName: 'EvnPS_PhaseDescr_did',
                                diagSetPhaseName: 'DiagSetPhase_did',
								diagPhaseFieldLabel: langs('Состояние пациента при направлении'),
                                showHSN: false,
                                diagField: {
									checkAccessRights: true,
                                    // allowBlank: false,
									MKB:null,
                                    fieldLabel: 'Диагноз напр. учр-я',
                                    hiddenName: 'Diag_did',
                                    id: 'EPSEF_DiagHospCombo',
                                    onChange: function(combo, newValue) {
                                        var base_form = this.findById('EvnPSEditForm').getForm();
                                        if ( !newValue ) {
                                            return true;
                                        }
                                        base_form.findField('LpuSection_pid').fireEvent('change', base_form.findField('LpuSection_pid'), base_form.findField('LpuSection_pid').getValue());
                                    }.createDelegate(this),
                                    getCode: function(){
                                        var record = this.getStore().getById(this.getValue());
                                        return record != null ? record.get('Diag_Code'):'';
                                    },
                                    tabIndex: this.tabindex + 16,
                                    width: 500,
                                    xtype: 'swdiagcombo'
                                },
                                HSNPrefix: 'DirectedLpu'
                            }),
                            {
                                id: this.id + '_ScaleFieldset',
                                hidden: getRegionNick() != 'ufa',
                                autoHeight: true,
                                border: false,
                                labelWidth: 300,
                                style: 'padding: 0px;',
                                xtype: 'fieldset',
                                width: 730,
                                items: [
                                    {
                                        xtype: 'hidden',
                                        name: 'PainResponse_Name',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'ExternalRespirationType_Name',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'SystolicBloodPressure_Name',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'InternalBleedingSigns_Name',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'LimbsSeparation_Name',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'textfield',
                                        labelStyle: 'color: blue; text-decoration: underline;',
                                        readOnly: true,
                                        fieldLabel: 'Шкала оценки степени тяжести',
                                        name: 'PrehospTraumaScale_Value',
                                        listeners: {
                                            focus: function(me) {
                                                if(me.getValue())
                                                    win.showPopup('trauma');
                                            }
                                        }
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'PainDT',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'ECGDT',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'TLTDT',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'FailTLT',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'textfield',
                                        labelStyle: 'color: blue; text-decoration: underline;',
                                        readOnly: true,
                                        name: 'ResultECG',
                                        fieldLabel: 'ОКС',
                                        width: 300,
                                        listeners: {
                                            focus: function(me) {
                                                if(me.getValue())
                                                    win.showPopup('oks');
                                            }
                                        }
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'FaceAsymetry_Name',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'HandHold_Name',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'SqueezingBrush_Name',
                                        hidden: true,
                                        disabled: true
                                    },
                                    {
                                        xtype: 'hidden',
                                        name: 'ScaleLams_id',
                                        hidden: true
                                    },
                                    {
                                        xtype: 'textfield',
                                        labelStyle: 'color: blue; text-decoration: underline;',
                                        readOnly: true,
                                        fieldLabel: 'Шкала LAMS',
                                        name: 'ScaleLams_Value',
                                        listeners: {
                                            focus: function(me) {
                                                if(me.getValue())
                                                    win.showPopup('lams');
                                            }
                                        }
                                    }
                                ]
                            },
                                {
                                    autoHeight: true,
                                    labelWidth: 300,
                                    style: 'padding: 0px;',
                                    title: 'Дефекты догоспитального этапа',
                                    width: 730,
                                    xtype: 'fieldset',

                                    items: [{
                                        allowBlank: false,
                                        fieldLabel: 'Несвоевременность госпитализации',
                                        hiddenName: 'EvnPS_IsImperHosp',
                                        tabIndex: this.tabindex + 17,
                                        value: 1,
                                        width: 100,
                                        xtype: 'swyesnocombo'
                                    }, {
                                        allowBlank: false,
                                        fieldLabel: 'Недост. объем клинико-диаг. обследования',
                                        hiddenName: 'EvnPS_IsShortVolume',
                                        tabIndex: this.tabindex + 18,
                                        value: 1,
                                        width: 100,
                                        xtype: 'swyesnocombo'
                                    }, {
                                        allowBlank: false,
                                        fieldLabel: 'Неправильная тактика лечения',
                                        hiddenName: 'EvnPS_IsWrongCure',
                                        tabIndex: this.tabindex + 19,
                                        value: 1,
                                        width: 100,
                                        xtype: 'swyesnocombo'
                                    }, {
                                        allowBlank: false,
                                        fieldLabel: 'Несовпадение диагноза',
                                        hiddenName: 'EvnPS_IsDiagMismatch',
                                        listeners: {
                                            'keydown': function(inp, e) {
                                                var base_form = this.findById('EvnPSEditForm').getForm();

                                                if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
                                                    e.stopEvent();

                                                    if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
                                                        base_form.findField('PrehospToxic_id').focus(true);
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( this.action != 'view' ) {
                                                        this.buttons[0].focus();
                                                    }
                                                    else {
                                                        this.buttons[1].focus();
                                                    }
                                                }
                                            }.createDelegate(this)
                                        },
                                        tabIndex: this.tabindex + 20,
                                        value: 1,
                                        width: 100,
                                        xtype: 'swyesnocombo'
                                    }]
                                }, {
                                	id: 'EPSEF_EvnSectionHtm',
                                	layout: 'form',
									labelWidth: 301,
									border: false,
									items: [{
										xtype: 'swdatefield',
										name: 'EvnPS_HTMBegDate',
										listeners: {
											'change': function(field, newValue, oldValue) {
												var base_form = win.findById('EvnPSEditForm').getForm();
												win.checkEvnDirectionAllowBlank();
												if (getRegionNick().inlist(['penza','perm'])) {
													if (Ext.isEmpty(newValue)) {
														base_form.findField('EvnPS_HTMTicketNum').setAllowBlank(true);
													} else {
														base_form.findField('EvnPS_HTMTicketNum').setAllowBlank(false);
													}
												}
											}
										},
										fieldLabel: 'Дата выдачи талона на ВМП',
										width: 100
									}, {
										xtype: 'textfield',
										name: 'EvnPS_HTMTicketNum',
										listeners: {
											'change': function(field, newValue, oldValue) {
												var base_form = win.findById('EvnPSEditForm').getForm();
												win.checkEvnDirectionAllowBlank();
												if (getRegionNick().inlist(['penza','perm'])) {
													if (Ext.isEmpty(newValue)) {
														base_form.findField('EvnPS_HTMBegDate').setAllowBlank(true);
													} else {
														base_form.findField('EvnPS_HTMBegDate').setAllowBlank(false);
													}
													win.setMedicalCareFormType();
												}
											}
										},
										maxLength: getRegionNick() == 'adygeya' ? 17 : 999, 
										fieldLabel: 'Номер талона на ВМП',
										width: 200
									}, {
										xtype: 'swdatefield',
										name: 'EvnPS_HTMHospDate',
										fieldLabel: 'Дата планируемой госпитализации (ВМП)',
										width: 100
									}, {
                                		xtype: 'swcheckbox',
										name: 'EvnPS_isMseDirected',
										fieldLabel: 'Пациент направлен на МСЭ',
										hideLabel: ! getRegionNick().inlist(['astra']),
										hidden: ! getRegionNick().inlist(['astra'])
									}]
								}]
                        }),
                        new sw.Promed.Panel({
                            border: true,
                            collapsible: true,
                            height: 125,
                            id: 'EPSEF_DirectDiagPanel',
                            isLoaded: false,
                            layout: 'border',
                            listeners: {
                                'expand': function(panel) {
                                    if ( panel.isLoaded === false ) {
                                        panel.isLoaded = true;
                                        panel.findById('EPSEF_EvnDiagPSHospGrid').getStore().load({
                                            params: {
                                                'class': 'EvnDiagPSHosp',
                                                EvnDiagPS_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
                                            }
                                        });
                                    }

                                    panel.doLayout();
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '2. Сопутствующие диагнозы направившего учреждения',
                            items: [ new Ext.grid.GridPanel({
                                autoExpandColumn: 'autoexpand_diag_hosp',
                                autoExpandMin: 100,
                                border: false,
                                columns: [{
                                    dataIndex: 'EvnDiagPS_setDate',
                                    header: 'Дата',
                                    hidden: false,
                                    renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'DiagSetClass_Name',
                                    header: 'Вид диагноза',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 200
                                }, {
                                    dataIndex: 'Diag_Code',
                                    header: 'Код диагноза',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'Diag_Name',
                                    header: 'Диагноз',
                                    hidden: false,
                                    id: 'autoexpand_diag_hosp',
                                    resizable: true,
                                    sortable: true
                                }],
                                frame: false,
                                height: 200,
                                id: 'EPSEF_EvnDiagPSHospGrid',
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

                                        var grid = this.findById('EPSEF_EvnDiagPSHospGrid');



                                        switch ( e.getKey() ) {
                                            case Ext.EventObject.DELETE:
                                                this.deleteEvent('EvnDiagPSHosp');
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

                                                this.openEvnDiagPSEditWindow(action, 'hosp');
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
                                                var base_form = this.findById('EvnPSEditForm').getForm();

                                                grid.getSelectionModel().clearSelections();
                                                grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

                                                if ( e.shiftKey == false ) {
                                                    if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
                                                        base_form.findField('PrehospToxic_id').focus(true);
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( this.action != 'view' ) {
                                                        this.buttons[0].focus();
                                                    }
                                                    else {
                                                        this.buttons[1].focus();
                                                    }
                                                }
                                                else {
                                                    if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
                                                        base_form.findField('EvnPS_IsDiagMismatch').focus(true);
                                                    }
                                                    else {
                                                        this.buttons[this.buttons.length - 1].focus();
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
                                        this.openEvnDiagPSEditWindow('edit', 'hosp');
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
                                            var toolbar = this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar();

                                            if ( selected_record ) {
                                                access_type = selected_record.get('accessType');
                                                id = selected_record.get('EvnDiagPS_id');
                                            }

                                            toolbar.items.items[1].disable();
                                            toolbar.items.items[3].disable();

                                            if ( id ) {
                                                toolbar.items.items[2].enable();

                                                if ( this.action != 'view' /*&& access_type == 'edit'*/ ) {
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
                                        'class': 'EvnDiagPSHosp'
                                    },
                                    listeners: {
                                        'load': function(store, records, index) {
                                            if ( store.getCount() == 0 ) {
                                                LoadEmptyRow(this.findById('EPSEF_EvnDiagPSHospGrid'));
                                            }

                                            // this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                                            // this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                                        }.createDelegate(this)
                                    },
                                    reader: new Ext.data.JsonReader({
                                        id: 'EvnDiagPS_id'
                                    }, [{
                                        mapping: 'EvnDiagPS_id',
                                        name: 'EvnDiagPS_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnDiagPS_pid',
                                        name: 'EvnDiagPS_pid',
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
                                        mapping: 'DiagSetPhase_id',
                                        name: 'DiagSetPhase_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnDiagPS_PhaseDescr',
                                        name: 'EvnDiagPS_PhaseDescr',
                                        type: 'string'
                                    }, {
                                        mapping: 'DiagSetClass_id',
                                        name: 'DiagSetClass_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'DiagSetType_id',
                                        name: 'DiagSetType_id',
                                        type: 'int'
                                    }, {
                                        dateFormat: 'd.m.Y',
                                        mapping: 'EvnDiagPS_setDate',
                                        name: 'EvnDiagPS_setDate',
                                        type: 'date'
                                    }, {
                                        mapping: 'DiagSetClass_Name',
                                        name: 'DiagSetClass_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'Diag_Code',
                                        name: 'Diag_Code',
                                        type: 'string'
                                    }, {
                                        mapping: 'Diag_Name',
                                        name: 'Diag_Name',
                                        type: 'string'
                                    }]),
                                    url: '/?c=EvnDiag&m=loadEvnDiagPSGrid'
                                }),
                                tbar: new sw.Promed.Toolbar({
                                    buttons: [{
                                        handler: function() {
                                            this.openEvnDiagPSEditWindow('add', 'hosp');
                                        }.createDelegate(this),
                                        iconCls: 'add16',
                                        text: 'Добавить'
                                    }, {
                                        handler: function() {
                                            this.openEvnDiagPSEditWindow('edit', 'hosp');
                                        }.createDelegate(this),
                                        iconCls: 'edit16',
                                        text: 'Изменить'
                                    }, {
                                        handler: function() {
                                            this.openEvnDiagPSEditWindow('view', 'hosp');
                                        }.createDelegate(this),
                                        iconCls: 'view16',
                                        text: 'Просмотр'
                                    }, {
                                        handler: function() {
                                            this.deleteEvent('EvnDiagPSHosp');
                                        }.createDelegate(this),
                                        iconCls: 'delete16',
                                        text: 'Удалить'
                                    }]
                                })
                            })]
                        }),
                        new sw.Promed.Panel({
                            autoHeight: true,
                            bodyStyle: 'padding-top: 0.5em;',
                            border: true,
                            collapsible: true,
                            id: 'EPSEF_AdmitDepartPanel',
                            layout: 'form',
                            listeners: {
                                'expand': function(panel) {
                                    // this.findById('EvnPSEditForm').getForm().findField('EvnPS_IsCont').focus(true);
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '3. Приемное',
                            items: [{
								border: false,
								layout: 'column',
								width: 800,
								items: [{
									border: false,
									layout: 'form',
									width: 400,
									items: [{
										fieldLabel: 'Состояние опьянения',
										hiddenName: 'PrehospToxic_id',
										listeners: {
											'keydown': function(inp, e) {
												if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
													e.stopEvent();
													var base_form = this.findById('EvnPSEditForm').getForm();

													if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
														this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
														this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
													}
													else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
														base_form.findField('EvnPS_IsDiagMismatch').focus(true);
													}
													else {
														this.buttons[this.buttons.length - 1].focus();
													}
												}
											}.createDelegate(this)
										},
										tabIndex: this.tabindex + 21,
										xtype: 'swprehosptoxiccombo'}]
								}, {
									border: false,
									layout: 'form',
									width: 400,
									items: [{
										fieldLabel: 'Вид транспортировки',
										hiddenName: 'LpuSectionTransType_id',
										tabIndex: this.tabindex + 21,
										xtype: 'swlpusectiontranstypecombo'
									}]
								}]
                            }, {
                                allowBlank: false,
                                fieldLabel: 'Тип госпитализации',
                                hiddenName: 'PrehospType_id',
	                            id: this.id  + 'PrehospType_id',
                                tabIndex: this.tabindex + 22,
                                width: 300,
                                xtype: 'swprehosptypecombo',
                                listeners: {
                                    'change': function() {
										win.setMedicalCareFormType();
                                        this.checkEvnDirectionAllowBlank();
                                    }.createDelegate(this),
                                    'select': function( combo ) {
										if ( getRegionNick() == 'buryatiya' ) {
											win.setMedicalCareFormType();
										}
                                    }.createDelegate(this)
                                }
                            }, {
                                allowDecimals: false,
                                allowNegative: false,
                                fieldLabel: 'Количество госпитализаций',
                                minValue: 0,
                                maxValue: 99,
                                name: 'EvnPS_HospCount',
                                tabIndex: this.tabindex + 23,
                                width: 100,
                                xtype: 'numberfield'
                            }, {
                                layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										fieldLabel: 'Время с начала заболевания',
										hiddenName: 'Okei_id',
										displayField: 'Okei_Name',
										tpl: new Ext.XTemplate(
											'<tpl for="."><div class="x-combo-list-item">',
											'{Okei_Name}',
											'</div></tpl>'
										),
										tabIndex: this.tabindex + 24,
										width: 80,
										xtype: 'swokeicombo',
										loadParams: {params: {where: ' where Okei_id in (100,101,102,104,107)'}}
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										hideLabel: true,
										allowNegative: false,
										maxValue: 999,
										name: 'EvnPS_TimeDesease',
										tabIndex: this.tabindex + 24,
										width: 100,
										xtype: 'numberfield'
									}]
								}]
                            }, {
                                allowBlank: true,
                                fieldLabel: 'Случай запущен',
                                hiddenName: 'EvnPS_IsNeglectedCase',
                                tabIndex: this.tabindex + 25,
                                width: 100,
                                xtype: 'swyesnocombo'
                            }, {
								border: false,
								layout: 'form',
								hidden: getRegionNick() != 'msk',
								items: [{
									allowDecimals: false,
									allowNegative: false,
									minValue: 0,
									maxValue: 999,
									fieldLabel: 'Частота дыхания',
									name: 'RepositoryObserv_BreathRate',
									xtype: 'numberfield'
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											allowDecimals: false,
											allowNegative: false,
											minValue: 0,
											maxValue: 999,
											fieldLabel: 'Систолическое АД',
											name: 'RepositoryObserv_Systolic',
											xtype: 'numberfield'
										}]
									}, {
										border: false,
										layout: 'form',
										items: [{
											allowDecimals: false,
											allowNegative: false,
											minValue: 0,
											maxValue: 999,
											fieldLabel: 'Диастолическое АД',
											name: 'RepositoryObserv_Diastolic',
											xtype: 'numberfield'
										}]
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											allowDecimals: true,
											allowNegative: false,
											decimalPrecision: 1,
											fieldLabel: 'Рост, см',
											minValue: 0,
											maxValue: 500,
											name: 'RepositoryObserv_Height',
											xtype: 'numberfield'
										}]
									}, {
										border: false,
										layout: 'form',
										items: [{
											allowDecimals: true,
											allowNegative: false,
											decimalPrecision: 1,
											fieldLabel: 'Вес, кг',
											minValue: 0,
											maxValue: 500,
											name: 'RepositoryObserv_Weight',
											xtype: 'numberfield'
										}]
									}]
								}, {
									allowDecimals: true,
									allowNegative: false,
									decimalPrecision: 1,
									fieldLabel: 'Температура тела',
									name: 'RepositoryObserv_TemperatureFrom',
									minValue: 0,
									maxValue: 50,
									xtype: 'numberfield'
								}, {
									allowDecimals: false,
									allowNegative: false,
									minValue: 0.01,
									maxValue: 100,
									fieldLabel: 'Сатурация кислорода (%)',
									name: 'RepositoryObserv_SpO2',
									xtype: 'numberfield'
								}, {
									comboSubject: 'CovidType',
									fieldLabel: 'Коронавирус',
									hiddenName: 'CovidType_id',
									xtype: 'swcommonsprcombo'
								}, {
									fieldLabel: 'Флюорография',
									name: 'RepositoryObserv_FluorographyDate',
									hidden: getRegionNick() != 'msk',
									xtype: 'swdatefield'
								}, {
									comboSubject: 'DiagConfirmType',
									hiddenName: 'DiagConfirmType_id',
									fieldLabel: langs('Диагноз подтвержден рентгенологически'),
									xtype: 'swcommonsprcombo'
								}]
							}, {
                                autoHeight: true,
                                style: 'padding: 5px 0px 0px;',
                                title: '',
                                width: 830,
                                xtype: 'fieldset',

                                items: [{
                                    border: false,
                                    layout: 'column',
                                    items: [{
                                        border: false,
                                        layout: 'form',
                                        items: [ new sw.Promed.SwPrehospTraumaCombo({
		                                    hiddenName: 'PrehospTrauma_id',
											fieldLabel: 'Вид травмы (внешнего воздействия)',
		                                    lastQuery: '',
		                                    listeners: {
		                                        'change': function(combo, newValue, oldValue) {
		                                            var base_form = this.findById('EvnPSEditForm').getForm();

		                                            var is_unlaw_combo = base_form.findField('EvnPS_IsUnlaw');
		                                            var record = combo.getStore().getById(newValue);

		                                            if ( !record ) {
		                                                is_unlaw_combo.clearValue();
		                                                is_unlaw_combo.disable();
		                                                is_unlaw_combo.setAllowBlank(true);
		                                            }
		                                            else {
		                                                is_unlaw_combo.setValue(1);
		                                                is_unlaw_combo.setDisabled( this.action == 'view' );
		                                                is_unlaw_combo.setAllowBlank(false);
		                                            }

													is_unlaw_combo.fireEvent('change', is_unlaw_combo, is_unlaw_combo.getValue());
		                                        }.createDelegate(this)
		                                    },
		                                    tabIndex: this.tabindex + 26,
		                                    width: 300
		                                })]
                                    }, {
                                        border: false,
										hidden: (getRegionNick() == 'kz'),
                                        labelWidth: 120,
										id:  'EvnPSDiag_eid',
                                        layout: 'form',
                                        items: [{
                                            checkAccessRights: true,
											MKB: null,
	                                        fieldLabel: 'Внешняя причина',
	                                        hiddenName: 'Diag_eid',
	                                        registryType: 'ExternalCause',
	                                        baseFilterFn: function(rec){
	                                        	if(typeof rec.get == 'function'){
	                                        		return (rec.get('Diag_Code').search(new RegExp("^[VWXY]", "i")) >= 0);
	                                        	} else {
	                                        		return true;
	                                        	}
	                                    	},
											listeners: {
                                            	'change': function(cmp, value){
													if(!getRegionNick().inlist(['kz'])) {
														this.findById('TraumaCircumEvnPS_Name').setVisible((value !== '') ? true : false);
													}
												}.createDelegate(this),
												'select': function(cmp, value){
													if(!getRegionNick().inlist(['kz'])) {
														this.findById('TraumaCircumEvnPS_Name').setVisible((value !== '') ? true : false);
													}
												}.createDelegate(this)
											},
	                                        tabIndex: this.tabindex + 26,
	                                        width: 200,
	                                        xtype: 'swdiagcombo'
                                        }]
                                    }]
                                }, {
									border: false,
									layout: 'column',
									hidden: getRegionNick().inlist(['kz']),
									id: 'TraumaCircumEvnPS_Name',
									items: [{
										border: false,
										layout: 'form',
										items: [
											{
												fieldLabel: langs('Обстоятельства получения травмы'),
												name: 'TraumaCircumEvnPS_Name',
												style: 'margin:5px 0px 10px',
												tabIndex: this.tabindex + 27,
												width: 500,
												maxLength: 400,
												xtype: 'textarea',
												height: 50,
											}
										]
									}]
								}, {
									border: false,
									bodyStyle: 'padding-top: 0.5em;',
									layout: 'column',
									hidden: getRegionNick().inlist(['kz']),
									name: 'TraumaCircumEvnPS_setDT',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											fieldLabel: 'Дата, время получения травмы',
											name: 'TraumaCircumEvnPS_setDTDate',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											tabIndex: this.tabindex + 28,
											width: 100,
											xtype: 'swdatefield'
										}]
									}, {
										border: false,
										labelWidth: 200,
										layout: 'form',
										items: [{
											hideLabel: true,
											name: 'TraumaCircumEvnPS_setDTTime',
											onTriggerClick: function () {
												var base_form = this.findById('EvnPSEditForm').getForm(),
													time_field = base_form.findField('TraumaCircumEvnPS_setDTTime'),
													date_field = base_form.findField('TraumaCircumEvnPS_setDTDate');
												setCurrentDateTime({
													dateField: date_field,
													loadMask: true,
													setDate: true,
													setDateMaxValue: true,
													setDateMinValue: false,
													setTime: true,
													timeField: time_field,
													windowId: this.id,
													callback: function () {
														date_field.fireEvent('change', date_field, date_field.getValue());
													}
												});
											}.createDelegate(this),
											tabIndex: this.tabindex + 28,
											plugins: [new Ext.ux.InputTextMask('99:99', true)],
											validateOnBlur: false,
											width: 60,
											xtype: 'swtimefield'
										}]
									}]
								}, {
                                    border: false,
                                    layout: 'column',
                                    items: [{
                                        border: false,
                                        layout: 'form',
                                        items: [ new sw.Promed.SwYesNoCombo({
                                            fieldLabel: 'Противоправная',
                                            hiddenName: 'EvnPS_IsUnlaw',
                                            lastQuery: '',
                                            tabIndex: this.tabindex + 27,
                                            width: 70,
		                                    listeners: {
		                                        'change': function(combo, newValue, oldValue) {
													
													if(this.isProcessInformationFrameLoad) return false;
													
		                                            var base_form = this.findById('EvnPSEditForm').getForm();

		                                            var notificationDateField = base_form.findField('EvnPS_NotificationDate'),
														notificationTimeField = base_form.findField('EvnPS_NotificationTime'),
														msfField = base_form.findField('MedStaffFact_id'),
														policeField = base_form.findField('EvnPS_Policeman'),
														msfpidField = base_form.findField('MedStaffFact_pid');

		                                            if ( newValue != 2 ) {
		                                                notificationDateField.setValue('');
		                                                notificationDateField.disable();
		                                                notificationTimeField.setValue('');
		                                                notificationTimeField.disable();
		                                                msfField.setValue('');
		                                                msfField.disable();
		                                                policeField.setValue('');
		                                                policeField.disable();
		                                                notificationDateField.setAllowBlank(true);
		                                                notificationTimeField.setAllowBlank(true);
		                                            }
		                                            else {
		                                                notificationDateField.setDisabled(this.action == 'view');
		                                                notificationTimeField.setDisabled(this.action == 'view');
		                                                msfField.setDisabled(this.action == 'view');
		                                                policeField.setDisabled(this.action == 'view');
		                                                notificationDateField.setAllowBlank(false);
		                                                notificationTimeField.setAllowBlank(false);
														if (Ext.isEmpty(msfField.getValue()) && !Ext.isEmpty(msfpidField.getValue())) {
															msfField.setValue(msfpidField.getValue());
														}
		                                            }
		                                        }.createDelegate(this)
		                                    }
                                        })]
                                    }, {
                                        border: false,
										hidden: (getRegionNick() == 'kz'),
                                        labelWidth: 200,
                                        layout: 'form',
                                        items: [{
                                            fieldLabel: 'Нетранспортабельность',
                                            hiddenName: 'EvnPS_IsUnport',
                                            lastQuery: '',
                                            tabIndex: this.tabindex + 28,
                                            width: 70,
											xtype: 'swyesnocombo'
                                        }]
                                    }]
       						 	}, {
                                    border: false,
									bodyStyle: 'padding-top: 0.5em;',
                                    layout: 'column',
                                    items: [{
                                        border: false,
                                        layout: 'form',
                                        items: [{
											fieldLabel: 'Дата, время направления Извещения',
                                            name: 'EvnPS_NotificationDate',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											tabIndex: this.tabindex + 28,
											width: 100,
											xtype: 'swdatefield'
                                        }]
                                    }, {
                                        border: false,
                                        labelWidth: 200,
                                        layout: 'form',
                                        items: [{
                                            hideLabel: true,
                                            name: 'EvnPS_NotificationTime',
											onTriggerClick: function() {
												var base_form = this.findById('EvnPSEditForm').getForm(), 
												time_field = base_form.findField('EvnPS_NotificationTime'), 
												date_field = base_form.findField('EvnPS_NotificationDate');

												if ( time_field.disabled ) {
													return false;
												}

												setCurrentDateTime({
													dateField: date_field,
													loadMask: true,
													setDate: true,
													setDateMaxValue: true,
													setDateMinValue: false,
													setTime: true,
													timeField: time_field,
													windowId: this.id,
													callback: function() {
														date_field.fireEvent('change', date_field, date_field.getValue());
													}
												});
											}.createDelegate(this),
                                            tabIndex: this.tabindex + 28,
											plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
											validateOnBlur: false,
											width: 60,
											xtype: 'swtimefield'
                                        }]
                                    }]
       						 	}, {
									fieldLabel: 'Сотрудник МО, передавший телефонограмму',
									hiddenName: 'MedStaffFact_id',
									ignoreDisableInDoc: true,
									lastQuery: '',
									tabIndex: this.tabindex + 28,
									width: 500,
									xtype:'swmedstafffactglobalcombo'
								}, {
									fieldLabel: (getRegionNick() == 'kz' ? 'Сотрудник, принявший информацию' : 'Сотрудник МВД России, принявший информацию'),
									name: 'EvnPS_Policeman',
									tabIndex: this.tabindex + 28,
									width: 500,
									xtype: 'textfield'
								}]
                            }, {
								border: false,
								hidden: (getRegionNick() != 'kz'),
								layout: 'form',
								items: [{
									comboSubject: 'EntranceModeType',
									hiddenName: 'EntranceModeType_id',
									fieldLabel: 'Вид транспортировки',
									tabIndex: this.tabindex + 29,
									width: 300,
									xtype: 'swcommonsprcombo'
								}]
							}, {
                                fieldLabel: 'Приемное отделение',
                                hiddenName: 'LpuSection_pid',
                                id: this.id + '_LpuSectionRecCombo',
								listeners: {
									'change': function(field, newValue, oldValue) {

										var base_form = this.findById('EvnPSEditForm').getForm();
										var lpu_section_eid = base_form.findField('LpuSection_eid').getValue();
                                        var grid = this.findById('EPSEF_EvnSectionGrid');
                                        // при изменении приемного отделения очищается исход из приемного и удаляется движение
                                        // пока нет постановки по этой проблеме, делаю так
                                        /*if (oldValue && grid.getStore().getCount() > 0 && grid.getStore().getAt(0).get('EvnSection_id')) {
                                            sw.swMsg.show({
                                                buttons: Ext.Msg.OK,
                                                fn: function() {
                                                    field.setValue(oldValue);
                                                    field.focus(false);
                                                },
                                                icon: Ext.Msg.WARNING,
                                                msg: 'Изменение приемного отделения невозможно, поскольку в рамках данного случая уже имеется движение в профильном отделении.',
                                                title: ERR_INVFIELDS_TIT
                                            });
                                            return false;
                                        }*/
										//base_form.findField('LpuSection_eid').clearValue();
										if ( newValue ) {
											base_form.findField('Diag_pid').setAllowBlank(false == getGlobalOptions().check_priemdiag_allow);
											field.getStore().each(function(record) {
												if ( record.get('LpuSection_id') == newValue ) {
													var LpuUnitType_SysNick = record.get('LpuUnitType_SysNick');
													var filterList = {
														onDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y')
													};

													if (!Ext.isEmpty(win.ChildLpuSection_disDate)) {
														filterList.onDate = Ext.util.Format.date(win.ChildLpuSection_disDate, 'd.m.Y')
													}
													if ( LpuUnitType_SysNick.toString().inlist([ 'priem' ]) ) {
														filterList.arrayLpuUnitType = [ '2', '3', '4', '5' ];
													}
													else if ( LpuUnitType_SysNick.toString().inlist([ 'stac', 'dstac' ]) ) {
														filterList.arrayLpuUnitType = [ '2', '3' ];
													}
													else if ( LpuUnitType_SysNick.toString().inlist([ 'polka', 'hstac', 'pstac' ]) ) {
														filterList.arrayLpuUnitType = [ '4', '5' ];
													}

													if ( !Ext.isEmpty(win.ChildLpuSection_id) ) {
														filterList.exactIdList = [ lpu_section_eid, win.ChildLpuSection_id ];
													}

													setLpuSectionGlobalStoreFilter(filterList);

													base_form.findField('LpuSection_eid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

													if ( base_form.findField('LpuSection_eid').getStore().getById(lpu_section_eid) ) {
														base_form.findField('LpuSection_eid').setValue(lpu_section_eid);
													}
													if (!base_form.findField('LpuSection_eid').getValue()
														&& !Ext.isEmpty(base_form.findField('LpuSection_eid').getStore())) {
														var id = base_form.findField('LpuSection_eid').getStore().data.keys[0];
														for (var x in base_form.findField('LpuSection_eid').getStore().data.keys) {
															base_form.findField('LpuSection_eid').setValue(x);
															if (base_form.findField('LpuSection_eid').getValue())
																break;
														}
													}
												}
											});
										} else {
											if (Ext.isEmpty(win.ChildLpuSection_id)) {
												base_form.findField('LpuSection_eid').clearValue();
												base_form.findField('LpuSection_eid').getStore().removeAll();
												base_form.findField('Diag_pid').setAllowBlank(true);
											}
										}
										base_form.findField('Diag_pid').validate();
									}.createDelegate(this)
								},
                                listWidth: 650,
                                tabIndex: this.tabindex + 30,
                                width: 500,
                                xtype: 'swlpusectionglobalcombo'
                            }, {
                                fieldLabel: 'Врач',
								dateFieldId: this.id + 'EvnPS_setDate',
								enableOutOfDateValidation: true,
                                hiddenName: 'MedStaffFact_pid',
                                id: this.id + '_MedStaffFactRecCombo',
                                listWidth: 650,
                                tabIndex: this.tabindex + 31,
                                width: 500,
                                xtype: 'swmedstafffactglobalcombo'
                            },
                                new sw.Promed.swDiagPanel({
                                    labelWidth: 180,
                                    phaseDescrName: 'EvnPS_PhaseDescr_pid',
                                    diagSetPhaseName: 'DiagSetPhase_pid',
									diagPhaseFieldLabel: langs('Состояние пациента при поступлении'),
                                    diagField: {
										checkAccessRights: true,
                                        // allowBlank: false,
										MKB:null,
                                        fieldLabel: 'Диагноз прием. отд-я',
                                        hiddenName: 'Diag_pid',
                                        id: this.id + '_DiagRecepCombo',
                                        tabIndex: this.tabindex + 32,
                                        width: 500,
                                        xtype: 'swdiagcombo',
										onChange: function() {
											win.refreshFieldsVisibility([ 'DeseaseType_id' ]);
											win.setCovidFieldsAllowBlank();
										},
                                        getCode: function(){
                                            var record = this.getStore().getById(this.getValue());
                                            return record != null ? record.get('Diag_Code'):'';
                                        },
                                        getGroup: function(){
                                            var record = this.getStore().getById(this.getValue());
                                            return record != null ? record.get('Diag_Code').slice(0,-2):'';
                                        }
										/*listeners: {
											'change': function(combo, newValue, oldValue) {
												// Не использовать listener 'change' таким образом для swdiagcombo
												// иначе затрется родительский и комбик на getValue будет возврачать не id, а name
												// использовать через addListener
											}.createDelegate(this)
										}*/
                                    },
                                }),
								{//#111791
										hidden: true,
										items: new Ext.form.ComboBox({
											id: this.id + '_ECGResult',
											displayField: 'ECGResult_Name',
											valueField: 'ECGResult_Code',
											visible: true,
											disabled: true,
											store: new Ext.data.JsonStore({
												autoLoad: false,
												fields: [
													{ name: 'ECGResult_Code', type: 'int' },
													{ name: 'ECGResult_Name', type: 'string' }
												],
												key: 'ECGResult_Code',
												url: '/?c=EvnPS&m=getEcgResult'
											}),
											listeners: {
												'render': function() {
													this.getStore().on('load', function(store,records,ooptions) {
														win.getLoadMask('Загрузка результата экг').hide();
														this.setValue(records[0].get('ECGResult_Code'));
													}.createDelegate(this));
													this.getStore().on('beforeload', function(store,records,ooptions) {
														win.getLoadMask('Загрузка результата экг').show();
													}.createDelegate(this));
												}
											}
										})
								},{//#111791
									id: this.id + '_TltPanel',
									border: false,
									hidden: (getRegionNick().inlist(['kz'])),
									layout: 'column',
									items: [{
										width: 200,
										layout: 'form',
										border: false,
										items: new Ext.form.Checkbox({
											fieldLabel: 'ТЛТ проведена в СМП',
											id: this.id + '_isCmpTlt',
											tabIndex: this.tabindex + 32,
											xtype: 'checkbox',
											setVisibleFormDT: function(isVisible) {
												var base_form = win.findById('EvnPSEditForm').getForm();
												if(!isVisible){
													base_form.findField('EvnPS_CmpTltDate').setValue(null);
													base_form.findField('EvnPS_CmpTltTime').setValue(null);
												}
												base_form.findField('EvnPS_CmpTltDate').setAllowBlank(!isVisible);
												base_form.findField('EvnPS_CmpTltTime').setAllowBlank(!isVisible);
												win.findById(win.id + '_CmpTltDTForm').setVisible(isVisible);
											},
											listeners: {
												'check': function(checkbox,checked){
													this.setVisibleFormDT(checked);
												}
											}
										})
									}, {
										id: this.id + '_CmpTltDTForm',
										border: false,
										layout: 'column',
										items: [{
											border: false,
											layout: 'form',
											items: [{
												fieldLabel: 'Дата и время проведения',
												id: win.id + '_CmpTltDate',
												name: 'EvnPS_CmpTltDate',
												plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
												tabIndex: this.tabindex + 28,
												width: 100,
												xtype: 'swdatefield',
												invalidText: 'Время проведения ТЛТ в СМП должно быть раньше времени поступления',
												validator: function() {
													return win.isValidTltUslugaDT();
												},
												listeners: {
													'change': function(_this, newValue, oldValue){
														win.findById(win.id + '_CmpTltTime').validate();
													}
												}
											}]
										}, {
											border: false,
											labelWidth: 200,
											layout: 'form',
											items: [{
												hideLabel: true,
												id: win.id + '_CmpTltTime',
												name: 'EvnPS_CmpTltTime',
												tabIndex: this.tabindex + 28,
												plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
												validateOnBlur: false,
												width: 60,
												xtype: 'swtimefield',
												invalidText: 'Время проведения ТЛТ в СМП должно быть раньше времени поступления',
												validator: function() {
													win.findById(win.id + '_CmpTltDate').validate();
													return win.isValidTltUslugaDT();
												}
											}]
										}]
									}]
								}, {
									xtype: 'swyesnocombo',
									hiddenName: 'EvnPS_IsActive',
									allowBlank: false,
									fieldLabel: langs('Дееспособен'),
									tabIndex: this.tabindex + 32.1,
									width: 100
								}, {
									xtype: 'swdeseasetypecombo',
									comboSubject: 'DeseaseType',
									hiddenName: 'DeseaseType_id',
									fieldLabel: 'Характер',
									tabIndex: this.tabindex + 33,
									allowSysNick: true,
									listeners: {
										'change': function(combo, newValue, oldValue) {
											this.refreshFieldsVisibility(['TumorStage_id']);
										}.createDelegate(this)
									},
									width: 500
								}, {
									fieldLabel: langs('Стадия выявленного ЗНО'),
									width: 500,
									hiddenName:'TumorStage_id',
									xtype:'swtumorstagenewcombo',
									loadParams: getRegionNumber().inlist([58,66,101]) ? {mode: 1} : {mode:0}, // только свой регион / + нулловый рег
									tabIndex: this.tabindex + 34.2
								}, {
									bodyStyle: 'padding: 0px',
									border: false,
									id: 'EPSEF_IsZNOPanel',
									hidden: getRegionNick().inlist([ 'kz' ]),
									layout: 'form',
									xtype: 'panel',
									items: [{
										fieldLabel: langs('Подозрение на ЗНО'),
										id: 'EPSEF_EvnPS_IsZNOCheckbox',
										tabIndex: this.tabindex + 34.4,
										xtype: 'checkbox',
										listeners:{
											'change': function(checkbox, value) {
												if(getRegionNick()!='ekb' || checkbox.disabled) return;
												
												var diagcode = win.findById('EvnPSEditForm').getForm().findField('Diag_pid').getFieldValue('Diag_Code');
												if(!value && win.lastzno == 2 && (Ext.isEmpty(diagcode) || diagcode.search(new RegExp("^(C|D0)", "i"))<0)) {
													var pframe = win.findById('EPSEF_PersonInformationFrame');
													sw.swMsg.show({
														buttons: Ext.Msg.YESNO,
														fn: function (buttonId, text, obj) {
															if (buttonId == 'yes') {
																win.changeZNO({isZNO: false});
															} else {
																checkbox.setValue(true);
																if(!Ext.isEmpty(Ext.getCmp('EPSEF_Diag_spid').lastvalue))
																	Ext.getCmp('EPSEF_Diag_spid').setValue(Ext.getCmp('EPSEF_Diag_spid').lastvalue);
															}
														}.createDelegate(this),
														icon: Ext.MessageBox.QUESTION,
														msg: 'По пациенту '+
															pframe.getFieldValue('Person_Surname')+' '+
															pframe.getFieldValue('Person_Firname')+' '+
															pframe.getFieldValue('Person_Secname')+
															' ранее установлено подозрение на ЗНО. Снять признак подозрения?',
														title: 'Вопрос'
													});
												}
												
												if(value) {
													if(Ext.isEmpty(Ext.getCmp('EPSEF_Diag_spid').getValue()) && !Ext.isEmpty(win.lastznodiag)) {
														Ext.getCmp('EPSEF_Diag_spid').getStore().load({
															callback:function () {
																Ext.getCmp('EPSEF_Diag_spid').getStore().each(function (rec) {
																	if (rec.get('Diag_id') == win.lastznodiag) {
																		Ext.getCmp('EPSEF_Diag_spid').fireEvent('select', Ext.getCmp('EPSEF_Diag_spid'), rec, 0);
																	}
																});
															},
															params:{where:"where DiagLevel_id = 4 and Diag_id = " + win.lastznodiag}
														});
													}
													win.changeZNO({isZNO: true});
												}
											},
											'check': function(checkbox, value) {
												var DiagSpid = Ext.getCmp('EPSEF_Diag_spid');
												if (value == true) {
													DiagSpid.showContainer();
													DiagSpid.setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm', 'msk' ]));
												} else {
													DiagSpid.lastvalue = DiagSpid.getValue();
													DiagSpid.setValue('');
													DiagSpid.hideContainer();
													DiagSpid.setAllowBlank(true);
												}
											}
										}
									}, {
										fieldLabel: 'Подозрение на диагноз',
										tabIndex: this.tabindex + 34.4,
										hiddenName: 'Diag_spid',
										id: 'EPSEF_Diag_spid',
										additQueryFilter: "(Diag_Code like 'C%' or Diag_Code like 'D0%')",
										baseFilterFn: function(rec){
											if(typeof rec.get == 'function') {
												return (rec.get('Diag_Code').substr(0,1) == 'C' || rec.get('Diag_Code').substr(0,2) == 'D0');
											} else if (rec.attributes && rec.attributes.Diag_Code) {
												return (rec.attributes.Diag_Code.substr(0,1) == 'C' || rec.attributes.Diag_Code.substr(0,2) == 'D0');
											} else {
												return true;
											}
										},
										onChange: function() {
											win.setDiagSpidComboDisabled();
										},
										width: 500,
										xtype: 'swdiagcombo'
									}, {
										layout: 'form',
										border: false,
										id: 'EPSEF_BiopsyDatePanel',
										hidden: getRegionNick()!='ekb',
										items: [{
											fieldLabel: 'Дата взятия биопсии',
											format: 'd.m.Y',
											name: 'EvnPS_BiopsyDate',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											width: 100,
											xtype: 'swdatefield'
										}]
									}]
								},{
									autoHeight: true,
									style: 'padding: 0px;',
									title: langs('Сообщение родственнику'),
									width: 700,
									xtype: 'fieldset',
									items: [
										{
											border: false,
											layout: 'column',
											items: [
												{
													border: false,
													layout: 'form',
													items: [{
														fieldLabel: langs('Дата сообщения'),
														name: 'FamilyContact_msgDate',
														plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
														width: 100,
														tabIndex: this.tabindex + 42.9,
														xtype: 'swdatefield',
														listeners: {
															'change': function (combo, value) {
																var base_form = win.findById('EvnPSEditForm').getForm();
																var Person_id = base_form.findField('Person_id').getValue(),
																	FIO = base_form.findField('VologdaFamilyContact_FIO').getValue(),
																	Phone = base_form.findField('VologdaFamilyContact_Phone').getValue();
																
																if (getRegionNick() != 'vologda' || ( FIO != '' || Phone != '')) return false;
																
																var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Загрузка..."});
																loadMask.show();
																Ext.Ajax.request({
																	url: '/?c=Person&m=getPersonDeputy',
																	params: {
																		Person_id: Person_id
																	},
																	success: function(response, options) {
																		loadMask.hide();
																		if (!Ext.isEmpty(response.responseText)) {
																			var response_obj = Ext.util.JSON.decode(response.responseText);
																			if (response_obj.length > 0) {
																				base_form.findField('VologdaFamilyContact_FIO').setValue(response_obj[0].Deputy_Fio);
																				base_form.findField('VologdaFamilyContact_Phone').setValue(response_obj[0].Deputy_Phone);
																				base_form.findField('FamilyContactPerson_id').setValue(response_obj[0].Deputy_id);
																			}
																		}

																	}.createDelegate(this),
																	failure: function(response, options) {
																		loadMask.hide();
																		sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных о родственнике');
																	}
																});
															}
														}
													}]
												},
												{
													border: false,
													layout: 'form',
													labelWidth: 50,
													items: [{
														fieldLabel: langs('Время'),
														listeners: {
															'keydown': function (inp, e) {
																if (e.getKey() == Ext.EventObject.F4) {
																	e.stopEvent();
																	inp.onTriggerClick();
																}
															}
														},
														name: 'FamilyContact_msgTime',
														id: this.id + 'FamilyContact_msgTime',
														onTriggerClick: function() {
															var base_form = this.findById('EvnPSEditForm').getForm(),
																time_field = base_form.findField('FamilyContact_msgTime'),
																date_field = base_form.findField('FamilyContact_msgDate');

															if ( time_field.disabled ) {
																return false;
															}

															setCurrentDateTime({
																dateField: date_field,
																loadMask: true,
																setDate: true,
																setDateMaxValue: true,
																setDateMinValue: false,
																setTime: true,
																timeField: time_field,
																windowId: this.id,
																callback: function() {
																	date_field.fireEvent('change', date_field, date_field.getValue());
																}
															});
														}.createDelegate(this),
														tabIndex: this.tabindex + 42.9,
														plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
														validateOnBlur: false,
														width: 60,
														xtype: 'swtimefield'
													}]
												}]},
										{
											border: false,
											layout: 'column',
											hidden: getRegionNick().inlist(['vologda']),
											items: [{
												border: false,
												layout: 'form',
												items: [
													{
														fieldLabel: langs('ФИО родственника'),
														name: 'FamilyContact_FIO',
														tabIndex: this.tabindex + 42.9,
														width: 300,
														xtype: 'textfield'
													}, {
														layout: 'form',
														border: false,
														fieldLabel: langs('Телефон')+'  +7',
														name: 'FamilyContact_Phone',
														tabIndex: this.tabindex + 42.9,
														fieldWidth: 120,
														xtype: 'swphonefield'
													}
												]
											}]
										},{
											border: false,
											layout: 'column',
											hidden: !getRegionNick().inlist(['vologda']),
											items: [{
												border: false,
												layout: 'form',
												items: [
													{
														xtype: 'hidden',
														name: "FamilyContactPerson_id"
													},{
														editable: true,
														forceSelection: false,
														xtype: 'swpersoncombo',
														name: 'VologdaFamilyContact_FIO',
														tabIndex: this.tabindex + 42.9,
														fieldLabel: langs('ФИО родственника'),
														onTrigger1Click: function () {
															var base_form = win.findById('EvnPSEditForm').getForm();
															var combo = base_form.findField('VologdaFamilyContact_FIO');
															var comboPerson_id = base_form.findField('FamilyContactPerson_id');

															if (combo.disabled) return false;

															getWnd('swPersonSearchWindow').show({
																onSelect: function (personData) {
																	if (personData.Person_id > 0) {
																		combo.getStore().loadData([{
																			Person_id: personData.Person_id,
																			Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
																		}]);
																		combo.setValue(personData.Person_id);
																		comboPerson_id.setValue(personData.Person_id);
																		combo.collapse();
																		combo.focus(true, 500);
																		combo.fireEvent('change', combo);

																		base_form.findField('VologdaFamilyContact_Phone').setValue(formatPhone(personData.Person_Phone, '($1)-$2-$3-$4'));
																	}
																	getWnd('swPersonSearchWindow').hide();
																},
																onClose: function () {
																	combo.focus(true, 500)
																}
															});
														},
														onTrigger2Click: function () {
															var base_form = win.findById('EvnPSEditForm').getForm();
															var combo = base_form.findField('VologdaFamilyContact_FIO');

															if (combo.disabled) return false;
															
															combo.clearValue();
															base_form.findField('VologdaFamilyContact_Phone').setValue(null);
															base_form.findField('FamilyContactPerson_id').setValue(null);
															combo.getStore().removeAll();
														},
														width: 300,
														listeners: {
															'change' : function(combo, value) {
																var base_form = win.findById('EvnPSEditForm').getForm();

																base_form.findField('FamilyContactPerson_id').setValue(null);
															}
														}
													}, {
														layout: 'form',
														border: false,
														fieldLabel: langs('Телефон')+'  +7',
														name: 'VologdaFamilyContact_Phone',
														tabIndex: this.tabindex + 42.9,
														fieldWidth: 120,
														plugins: [new Ext.ux.InputTextMask('999-999-99-99', true)],
														xtype: 'textfield'
													}
												]
											}]
										}
									]
								},
								new sw.Promed.Panel({
									border: true,
									height: 100,
									id: 'EPSEF_SpecificsPanel',
									isLoaded: false,
									layout: 'border',
									style: 'margin-top: 1em;',
									items: [
										{
											autoScroll:true,
											border:false,
											collapsible:false,
											wantToFocus:false,
											id: 'EDPLSEF_SpecificsTree',
											listeners:{
												'bodyresize': function(tree) {
													
												}.createDelegate(this),
												'beforeload': function(node) {
													
												}.createDelegate(this),
												'click':function (node, e) {
													var base_form = this.findById('EvnPSEditForm').getForm();
													var win = this;
													
													var params = {};
													params.onHide = function(isChange) {
														win.loadSpecificsTree();
													};
													params.EvnSection_id = node.attributes.value;
													params.Morbus_id = node.attributes.Morbus_id;
													params.MorbusOnko_pid = node.attributes.value;
													params.Person_id = base_form.findField('Person_id').getValue();
													params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
													params.Server_id = base_form.findField('Server_id').getValue();
													params.allowSpecificEdit = true;
													params.action = (this.action != 'view') ? 'edit' : 'view';
													// всегда пересохраняем, чтобы в специфику ушли актуальные данные
													this.doSave({
														callback: function() {
															var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Загрузка..."});
															loadMask.show();
															Ext.Ajax.request({
																failure: function(response, options) {
																	loadMask.hide();
																	sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
																},
																params: {
																	EvnPS_id: base_form.findField('EvnPS_id').getValue()
																},
																success: function(response, options) {
																	loadMask.hide();
																	if (!Ext.isEmpty(response.responseText)) {
																		var response_obj = Ext.util.JSON.decode(response.responseText);
																		if (response_obj.length <= 0) {
																			sw.swMsg.show({
																				buttons: Ext.Msg.OK,
																				fn: function() {
																					wnd.formStatus = 'edit';
																					base_form.findField('LpuSection_pid').focus(false);
																				},
																				icon: Ext.Msg.WARNING,
																				msg: 'Не введено ни одного движения. Поля "Приемное отделение", "Врач приемного отделения" и "Диагноз приемного отделения" должны быть заполнены.',
																				title: ERR_INVFIELDS_TIT
																			});
																			return false;
																		}
																		
																		params.EvnSection_id = response_obj[0].EvnSection_id;
																		params.MorbusOnko_pid = response_obj[0].EvnSection_id;
																		getWnd('swMorbusOnkoWindow').show(params);
																	}
																  
																}.createDelegate(this),
																url: '/?c=EvnSection&m=getSectionPriemData'
															});
														}.createDelegate(this),
														print: false
													});
												}.createDelegate(this),
												contextmenu: function(node, e) {
													if (!!node.leaf) {
														var c = new Ext.menu.Menu({
														items: [{
															id: 'print',
															text: langs('Печать КЛУ при ЗНО'),
															disabled: !node.attributes.Morbus_id,
															icon: 'img/icons/print16.png',
															iconCls : 'x-btn-text'
														},{
															id: 'printOnko',
															text: langs('Печать выписки по онкологии'),
															disabled: !(node.attributes.Morbus_id && getRegionNick() == 'ekb'),
															hidden: getRegionNick() != 'ekb',
															icon: 'img/icons/print16.png',
															iconCls : 'x-btn-text'
														}],
														listeners: {
															itemclick: function(item) {
																switch (item.id) {
																	case 'print': 
																		var n = item.parentMenu.contextNode;
																		printBirt({
																			'Report_FileName': 'CheckList_MedCareOnkoPatients.rptdesign',
																			'Report_Params': '&Evn_id=' + (n.attributes.EvnSection_id ? n.attributes.EvnSection_id : n.attributes.EvnSection_id),
																			'Report_Format': 'pdf'
																		});
																		break;
																	case 'printOnko':
																		var n = item.parentMenu.contextNode;
																		printBirt({
																			'Report_FileName': 'WritingOut_MedCareOnkoPatients.rptdesign',
																			'Report_Params': '&Evn_id=' + (n.attributes.EvnSection_id ? n.attributes.EvnSection_id : n.attributes.EvnSection_id),
																			'Report_Format': 'pdf'
																		});
																		break;
																}
															}
														}
														});
														c.contextNode = node;
														c.showAt(e.getXY());
													}
												}
											},
											loader:new Ext.tree.TreeLoader({
												dataUrl:'/?c=Specifics&m=getPriemSpecificsTree'
											}),
											region:'west',
											root:{
												draggable:false,
												id:'specifics_tree_root',
												nodeType:'async',
												text:'Специфика',
												value:'root'
											},
											rootVisible:false,
											split:true,
											useArrows:true,
											width:250,
											xtype:'treepanel'
										},
										{
											border:false,
											layout:'border',
											region:'center',
											xtype:'panel',
											items:[
												{
													autoHeight:true,
													border:false,
													labelWidth:150,
													split:true,
													items:[
													
													],
													layout:'form',
													region:'north',
													xtype:'panel'
												},
												{
													autoHeight:true,
													border:false,
													id:this.id + '_SpecificFormsPanel',
													items:[

													],
													layout:'fit',
													region:'center',
													xtype:'panel'
												}
											]
										}
									]
								}),
								{
									autoHeight: true,
									style: 'padding: 5px 0px 0px;',
									title: '',
									width: 850,
									xtype: 'fieldset',
									name: 'blockPediculos',
									hiddenName: 'blockPediculos',
									id: 'EPSEF_blockPediculos',
									items: [
										{
											border: false,
											layout: 'column',
											items:[
												{
													xtype: 'hidden',
													name: 'Pediculos_id',
													hidden: true,
													disabled: true
												},
												{
													border: false,
													labelWidth: 150,
													layout: 'form',
													items: [{
														fieldLabel: langs('Педикулёз'),
														id: 'EDPLSEF_isPediculos',
														name: 'isPediculos',
														xtype: 'checkbox',
														width: 100,
														listeners:{
															'change': function(checkbox, value) {
																var base_form = win.findById('EvnPSEditForm').getForm();
																var comboPediculosDiag = base_form.findField('PediculosDiag_id');
																if(!comboPediculosDiag) return false;
																if (win.action != 'view') {
																	if(value){
																		comboPediculosDiag.setAllowBlank(false);
																		comboPediculosDiag.setDisabled(false);
																	}else{
																		comboPediculosDiag.setAllowBlank(true);
																		comboPediculosDiag.clearValue();
																		comboPediculosDiag.setDisabled(true);
																	}
																}

																if (!(value || base_form.findField('ScabiesDiag_id').getFieldValue('Diag_Code'))) {
																	win.findById('EPSEF_PediculosPrint').disable();
																}
															}
														}
													}]
												},
												{
													border: false,
													labelWidth: 80,
													layout: 'form',
													allowBlank: true,
													items: [{
														allowBlank: true,
														xtype: 'swdiagcombo',
														hiddenName: 'PediculosDiag_id',
														width: 450,
														fieldLabel: langs('Диагноз'),
														additQueryFilter: "(Diag_Code like 'B%')",
														allQueryFilter: false,
														baseFilterFn: function(rec){
															var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
															return (Diag_Code.substr(0,3) == 'B85');
														},
														onChange: function() {
															var base_form = win.findById('EvnPSEditForm').getForm();
															if(this.getFieldValue('Diag_Code') || base_form.findField('ScabiesDiag_id').getFieldValue('Diag_Code')){	
																if(base_form.findField('isPediculos').getValue()) win.findById('EPSEF_PediculosPrint').enable();
															}else{
																win.findById('EPSEF_PediculosPrint').disable();
															}
														}
													}]
												},
												{
													border: false,
													labelWidth: 150,
													layout: 'form',
													items: [{
														fieldLabel: langs('Чесотка'),
														id: 'EDPLSEF_isScabies',
														name: 'isScabies',
														xtype: 'checkbox',
														width: 100,
														listeners:{
															'change': function(checkbox, value) {
																var base_form = win.findById('EvnPSEditForm').getForm();
																var comboPediculosDiag = base_form.findField('ScabiesDiag_id');
																if(!comboPediculosDiag) return false;
																if (win.action != 'view') {
																	if(value){
																		comboPediculosDiag.setAllowBlank(false);
																		comboPediculosDiag.setDisabled(false);
																	}else{
																		comboPediculosDiag.setAllowBlank(true);
																		comboPediculosDiag.clearValue();
																		comboPediculosDiag.setDisabled(true);
																	}
																}
																if (!(value || base_form.findField('PediculosDiag_id').getFieldValue('Diag_Code'))) {
																	win.findById('EPSEF_PediculosPrint').disable();
																}
															}
														}
													}]
												},
												{
													border: false,
													labelWidth: 80,
													layout: 'form',
													allowBlank: true,
													items: [{
														allowBlank: true,
														xtype: 'swdiagcombo',
														hiddenName: 'ScabiesDiag_id',
														width: 450,
														fieldLabel: langs('Диагноз'),
														additQueryFilter: "(Diag_Code like 'B%')",
														allQueryFilter: false,
														baseFilterFn: function(rec){
															var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
															return (Diag_Code.substr(0,3) == 'B86');
														},
														onChange: function() {
															var base_form = win.findById('EvnPSEditForm').getForm();
															if(this.getFieldValue('Diag_Code') || base_form.findField('PediculosDiag_id').getFieldValue('Diag_Code')){	
																if(base_form.findField('isScabies').getValue()) win.findById('EPSEF_PediculosPrint').enable();
															}else{
																win.findById('EPSEF_PediculosPrint').disable();
															}
														}
													}]
												}	
											]
										},
										{
											xtype: 'hidden',
											name: 'Pediculos_isPrint',
											hidden: true,
											disabled: true
										},
										{
											xtype: 'hidden',
											name: 'buttonPrint058',
											hidden: true,
											disabled: true
										},
										{
											handler: function() {
												this.pediculosPrint(true);
											}.createDelegate(this),
											iconCls: 'print16',
											id: 'EPSEF_PediculosPrint',
											text: langs(' Печать извещения 058у '),
											tooltip: langs('Печать извещения 058у'),
											style: 'margin: 5px 15px 10px 15px',
											xtype: 'button'
										},
										{
											border: false,
											layout: 'column',
											items:[
												{
													border: false,
													labelWidth: 150,
													layout: 'form',
													items: [{
														fieldLabel: langs('Санитарная обработка'),
														id: 'EPSEF_Pediculos_isSanitation',
														name: 'Pediculos_isSanitation',
														xtype: 'checkbox',
														width: 100,
														listeners:{
															'change': function(checkbox, value) {
																var base_form = win.findById('EvnPSEditForm').getForm();
																var pediculos_Sanitation_setDate = base_form.findField('Pediculos_Sanitation_setDate');
																var pediculos_Sanitation_setTime = base_form.findField('Pediculos_Sanitation_setTime');
																if(!pediculos_Sanitation_setDate) return false;
																if (win.action != 'view') {
																	if(value){
																		pediculos_Sanitation_setDate.setAllowBlank(false);
																		pediculos_Sanitation_setTime.setAllowBlank(false);
																		pediculos_Sanitation_setDate.setDisabled(false);
																		pediculos_Sanitation_setTime.setDisabled(false);
																	}else{
																		pediculos_Sanitation_setDate.setAllowBlank(true);
																		pediculos_Sanitation_setTime.setAllowBlank(true);
																		pediculos_Sanitation_setDate.setValue();
																		pediculos_Sanitation_setTime.setValue();
																		pediculos_Sanitation_setDate.setDisabled(true);
																		pediculos_Sanitation_setTime.setDisabled(true);
																	}
																}
															}
														}
													}]
												},
												{
													border: false,
													labelWidth: 170,
													layout: 'form',
													items: [{
														allowBlank: true,
														fieldLabel: langs('Дата санитарной обработки'),
														format: 'd.m.Y',
														listeners: {
															'change': function(field, newValue, oldValue) {

															}.createDelegate(this)
														},
														name: 'Pediculos_Sanitation_setDate',
														id: this.id + 'Pediculos_Sanitation_setDate',
														plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
														selectOnFocus: true,
														width: 100,
														xtype: 'swdatefield'
													}]
												},
												{
													border: false,
													labelWidth: 50,
													layout: 'form',
													items: [{
														fieldLabel: langs('Время'),
														name: 'Pediculos_Sanitation_setTime',
														id: this.id + 'Pediculos_Sanitation_setTime',
														plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
														validateOnBlur: false,
														width: 60,
														xtype: 'swtimefield'
													}]
												}
											]
										}
									]
								}
                            ]
                        }),
                        new sw.Promed.Panel({
                            border: true,
                            collapsible: true,
                            height: 125,
                            id: 'EPSEF_AdmitDiagPanel',
                            isLoaded: false,
                            layout: 'border',
                            listeners: {
                                'expand': function(panel) {
                                    if ( panel.isLoaded === false ) {
                                        panel.isLoaded = true;
                                        panel.findById('EPSEF_EvnDiagPSRecepGrid').getStore().load({
                                            params: {
                                                'class': 'EvnDiagPSRecep',
                                                EvnDiagPS_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
                                            }
                                        });
                                    }

                                    panel.doLayout();
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '4. Сопутствующие диагнозы приемного отделения',
                            items: [ new Ext.grid.GridPanel({
                                autoExpandColumn: 'autoexpand_diag_recep',
                                autoExpandMin: 100,
                                border: false,
                                columns: [{
                                    dataIndex: 'EvnDiagPS_setDate',
                                    header: 'Дата',
                                    hidden: false,
                                    renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'DiagSetClass_Name',
                                    header: 'Вид диагноза',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 200
                                }, {
                                    dataIndex: 'Diag_Code',
                                    header: 'Код диагноза',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'Diag_Name',
                                    header: 'Диагноз',
                                    hidden: false,
                                    id: 'autoexpand_diag_recep',
                                    resizable: true,
                                    sortable: true
                                }],
                                frame: false,
                                height: 200,
                                id: 'EPSEF_EvnDiagPSRecepGrid',
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

                                        var grid = Ext.getCmp('EPSEF_EvnDiagPSRecepGrid');

                                        switch ( e.getKey() ) {
                                            case Ext.EventObject.DELETE:
                                                this.deleteEvent('EvnDiagPSRecep');
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

                                                this.openEvnDiagPSEditWindow(action, 'recep');
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
                                                var base_form = this.findById('EvnPSEditForm').getForm();

                                                grid.getSelectionModel().clearSelections();
                                                grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

                                                if ( e.shiftKey == false ) {
                                                    if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( this.action != 'view' ) {
                                                        this.buttons[0].focus();
                                                    }
                                                    else {
                                                        this.buttons[1].focus();
                                                    }
                                                }
                                                else {
                                                    if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
                                                        if ( !base_form.findField('Diag_pid').disabled ) {
                                                            base_form.findField('Diag_pid').focus(true);
															
                                                        }
                                                        else {
                                                            base_form.findField('MedStaffFact_pid').focus(true);
                                                        }
                                                    }
                                                    else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
                                                        base_form.findField('EvnPS_IsDiagMismatch').focus(true);
                                                    }
                                                    else {
                                                        this.buttons[this.buttons.length - 1].focus();
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
                                        this.openEvnDiagPSEditWindow('edit', 'recep');
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
                                            var toolbar = this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar();

                                            if ( selected_record ) {
                                                access_type = selected_record.get('accessType');
                                                id = selected_record.get('EvnDiagPS_id');
                                            }

                                            toolbar.items.items[1].disable();
                                            toolbar.items.items[3].disable();

                                            if ( id ) {
                                                toolbar.items.items[2].enable();

                                                if ( this.action != 'view' /*&& access_type == 'edit'*/ ) {
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
                                                LoadEmptyRow(this.findById('EPSEF_EvnDiagPSRecepGrid'));
                                            }

                                            // this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                                            // this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                                        }.createDelegate(this)
                                    },
                                    reader: new Ext.data.JsonReader({
                                        id: 'EvnDiagPS_id'
                                    }, [{
                                        mapping: 'EvnDiagPS_id',
                                        name: 'EvnDiagPS_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnDiagPS_pid',
                                        name: 'EvnDiagPS_pid',
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
                                        mapping: 'DiagSetPhase_id',
                                        name: 'DiagSetPhase_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnDiagPS_PhaseDescr',
                                        name: 'EvnDiagPS_PhaseDescr',
                                        type: 'string'
                                    }, {
                                        mapping: 'DiagSetClass_id',
                                        name: 'DiagSetClass_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'DiagSetType_id',
                                        name: 'DiagSetType_id',
                                        type: 'int'
                                    }, {
                                        dateFormat: 'd.m.Y',
                                        mapping: 'EvnDiagPS_setDate',
                                        name: 'EvnDiagPS_setDate',
                                        type: 'date'
                                    }, {
                                        mapping: 'DiagSetClass_Name',
                                        name: 'DiagSetClass_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'Diag_Code',
                                        name: 'Diag_Code',
                                        type: 'string'
                                    }, {
                                        mapping: 'Diag_Name',
                                        name: 'Diag_Name',
                                        type: 'string'
                                    }]),
                                    url: '/?c=EvnDiag&m=loadEvnDiagPSGrid'
                                }),
                                tbar: new sw.Promed.Toolbar({
                                    buttons: [{
                                        handler: function() {
                                            this.openEvnDiagPSEditWindow('add', 'recep');
                                        }.createDelegate(this),
                                        iconCls: 'add16',
                                        text: 'Добавить'
                                    }, {
                                        handler: function() {
                                            this.openEvnDiagPSEditWindow('edit', 'recep');
                                        }.createDelegate(this),
                                        iconCls: 'edit16',
                                        text: 'Изменить'
                                    }, {
                                        handler: function() {
                                            this.openEvnDiagPSEditWindow('view', 'recep');
                                        }.createDelegate(this),
                                        iconCls: 'view16',
                                        text: 'Просмотр'
                                    }, {
                                        handler: function() {
                                            this.deleteEvent('EvnDiagPSRecep');
                                        }.createDelegate(this),
                                        iconCls: 'delete16',
                                        text: 'Удалить'
                                    }]
                                })
                            })]
                        }),
                        new sw.Promed.Panel({
                            autoHeight: true,
                            bodyStyle: 'padding-top: 0.5em;',
                            border: true,
                            collapsible: true,
                            id: 'EPSEF_PriemLeavePanel',
                            layout: 'form',
                            listeners: {
                                'expand': function(panel) {
									this.isProcessLoadForm = true;
                                    panel.findById('EPSEF_LpuSectionCombo').getStore().each(function(record) {
                                        if (record.get('LpuSection_id') == panel.findById('EPSEF_LpuSectionCombo').getValue())
                                        {
                                            panel.findById('EPSEF_LpuSectionCombo').fireEvent('select', panel.findById('EPSEF_LpuSectionCombo'), record, 0);
                                        }
                                    });
                                    panel.findById('EPSEF_PrehospWaifRefuseCause_id').fireEvent('change', panel.findById('EPSEF_PrehospWaifRefuseCause_id'), panel.findById('EPSEF_PrehospWaifRefuseCause_id').getValue());
									this.isProcessLoadForm = false;
                                    panel.doLayout();
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '5. Исход пребывания в приемном отделении',
                            items: [{
								fieldLabel: 'Повторная подача',
								listeners: {
									'check': function(checkbox, value) {
										if ( getRegionNick() != 'perm' ) {
											return false;
										}

										var base_form = this.findById('EvnPSEditForm').getForm();

										var
											EvnPS_IndexRep = parseInt(base_form.findField('EvnPS_IndexRep').getValue()),
											EvnPS_IndexRepInReg = parseInt(base_form.findField('EvnPS_IndexRepInReg').getValue()),
											EvnSection_IsPaid = parseInt(base_form.findField('EvnSection_IsPaid').getValue());

										var diff = EvnPS_IndexRepInReg - EvnPS_IndexRep;

										if ( EvnSection_IsPaid != 2 || EvnPS_IndexRepInReg == 0 ) {
											return false;
										}

										if ( value == true ) {
											if ( diff == 1 || diff == 2 ) {
												EvnPS_IndexRep = EvnPS_IndexRep + 2;
											}
											else if ( diff == 3 ) {
												EvnPS_IndexRep = EvnPS_IndexRep + 4;
											}
										}
										else if ( value == false ) {
											if ( diff <= 0 ) {
												EvnPS_IndexRep = EvnPS_IndexRep - 2;
											}
										}

										base_form.findField('EvnPS_IndexRep').setValue(EvnPS_IndexRep);

									}.createDelegate(this)
								},
								name: 'EvnPS_RepFlag',
								xtype: 'checkbox'
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: 'Дата исхода',
										name: 'EvnPS_OutcomeDate',
										listeners: {
											'change': function(filed, newValue) {
												win.setMedicalCareFormTypeAllowBlank();

												var base_form = win.findById('EvnPSEditForm').getForm();

												// если дата исхода заполнена то приемное и врач приемного обязателен, иначе нет.
												if (!Ext.isEmpty(newValue)) {
													base_form.findField('LpuSection_pid').setAllowBlank(false);
													base_form.findField('MedStaffFact_pid').setAllowBlank(false);
												} else {
													base_form.findField('LpuSection_pid').setAllowBlank(true);
													base_form.findField('MedStaffFact_pid').setAllowBlank(true);
													// все поля очистить
													base_form.findField('LpuSection_eid').clearValue();
													base_form.findField('PrehospWaifRefuseCause_id').clearValue();
													base_form.findField('PrehospWaifRefuseCause_id').fireEvent('change', base_form.findField('PrehospWaifRefuseCause_id'), base_form.findField('PrehospWaifRefuseCause_id').getValue());
												}

												win.reloadUslugaComplexField();
												win.refreshFieldsVisibility([ 'DeseaseType_id', 'DiagSetPhase_did', 'DiagSetPhase_pid' ]);

												if ( getRegionNick() == 'perm' ) {
													base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), base_form.findField('EvnPS_setDate').getValue());
												}

												if ( 
													getRegionNick() == 'buryatiya' 
													&& base_form.findField('EvnPS_OutcomeDate').getValue() >= new Date(2019, 7, 1)
													&& !base_form.findField('LeaveType_prmid').getFieldValue('LeaveType_SysNick').inlist(['gosp','otk'])
												) {
													base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
													base_form.findField('LpuSectionProfile_id').showContainer();
												}else{
													base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
												}
											}
										},
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: this.tabindex + 43,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									labelWidth: 50,
									layout: 'form',
									items: [{
										fieldLabel: 'Время',
										listeners: {
											'keydown': function (inp, e) {
												if ( e.getKey() == Ext.EventObject.F4 ) {
													e.stopEvent();
													inp.onTriggerClick();
												}
											}
										},
										name: 'EvnPS_OutcomeTime',
										onTriggerClick: function() {
											var base_form = this.findById('EvnPSEditForm').getForm();
											var time_field = base_form.findField('EvnPS_OutcomeTime');

											if ( time_field.disabled ) {
												return false;
											}

											setCurrentDateTime({
												dateField: base_form.findField('EvnPS_OutcomeDate'),
												loadMask: true,
												setDate: true,
												setDateMaxValue: true,
												setDateMinValue: false,
												setTime: true,
												timeField: time_field,
												windowId: this.id,
												callback: function() {
													base_form.findField('EvnPS_OutcomeDate').fireEvent('change', base_form.findField('EvnPS_OutcomeDate'), base_form.findField('EvnPS_OutcomeDate').getValue());
												}
											});
										}.createDelegate(this),
										plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
										tabIndex: this.tabindex + 44,
										validateOnBlur: false,
										width: 60,
										xtype: 'swtimefield'
									}]
								}]
							}, {
								allowSysNick: true,
								autoLoad: false,
								comboSubject: 'LeaveType',
								fieldLabel: 'Исход пребывания',
								hiddenName: 'LeaveType_prmid',
								lastQuery: '',
								listeners: {
									'change':function (combo, newValue, oldValue) {
										var index = combo.getStore().findBy(function (rec) {
											return (rec.get(combo.valueField) == newValue);
										});

										combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
									}.createDelegate(this),
									'select':function (combo, record, idx) {
										var base_form = this.findById('EvnPSEditForm').getForm();

										// 1. Чистим и скрываем все поля
										// 2. В зависимости от выбранного значения, открываем поля

										var
											LpuSection_eid = base_form.findField('LpuSection_eid').getValue(),
											PrehospWaifRefuseCause_id = base_form.findField('PrehospWaifRefuseCause_id').getValue(),
											UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue(),
											ResultClass_id = base_form.findField('ResultClass_id').getValue(),
											ResultDeseaseType_id = base_form.findField('ResultDeseaseType_id').getValue(),
											EvnPS_IsTransfCall = base_form.findField('EvnPS_IsTransfCall').getValue(),
											diag_a_phase_combo = base_form.findField('DiagSetPhase_aid');

										diag_a_phase_combo.setAllowBlank(true);
										base_form.findField('LpuSection_eid').clearValue();
										base_form.findField('LpuSection_eid').setAllowBlank(true);
										base_form.findField('LpuSection_eid').setContainerVisible(false);
										base_form.findField('PrehospWaifRefuseCause_id').clearValue();
										base_form.findField('PrehospWaifRefuseCause_id').setAllowBlank(true);
										base_form.findField('PrehospWaifRefuseCause_id').setContainerVisible(false);
										base_form.findField('UslugaComplex_id').clearValue();
										base_form.findField('UslugaComplex_id').setAllowBlank(true);
										base_form.findField('UslugaComplex_id').setContainerVisible(false);
										base_form.findField('ResultClass_id').clearValue();
										base_form.findField('ResultClass_id').setAllowBlank(true);
										base_form.findField('ResultClass_id').setContainerVisible(false);
										base_form.findField('ResultDeseaseType_id').clearValue();
										base_form.findField('ResultDeseaseType_id').setAllowBlank(true);
										base_form.findField('ResultDeseaseType_id').setContainerVisible(false);
										base_form.findField('EvnPS_IsTransfCall').clearValue();
										base_form.findField('EvnPS_IsTransfCall').setAllowBlank(true);
										base_form.findField('EvnPS_IsTransfCall').setContainerVisible(false);
										if (getRegionNick() != 'penza') {
											base_form.findField('MedicalCareFormType_id').setAllowBlank(true);
										} else {
											base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
										}
										base_form.findField('MedicalCareFormType_id').setContainerVisible(false);
										this.findById('EPSEF_PrehospWaifRefuseCauseButton').hide();

										if ( typeof record == 'object' && !Ext.isEmpty(record.get('LeaveType_id')) ) {
											this.setMedicalCareFormType();

											switch ( record.get('LeaveType_SysNick') ) {
												case 'gosp': // Госпитализация
													base_form.findField('LpuSection_eid').setAllowBlank(false);
													base_form.findField('LpuSection_eid').setContainerVisible(true);
													diag_a_phase_combo.setAllowBlank(false);

													if ( !Ext.isEmpty(LpuSection_eid) ) {
														base_form.findField('LpuSection_eid').setValue(LpuSection_eid);
													}

													if ( getRegionNick() == 'buryatiya' ) {
														base_form.findField('LpuSectionProfile_id').hideContainer();
														base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
													}
												break;

												case 'otk': // Отказ
													base_form.findField('PrehospWaifRefuseCause_id').setAllowBlank(false);
													base_form.findField('PrehospWaifRefuseCause_id').setContainerVisible(true);
													base_form.findField('EvnPS_IsTransfCall').setContainerVisible(true);
													diag_a_phase_combo.setAllowBlank(false);
													this.findById('EPSEF_PrehospWaifRefuseCauseButton').show();

													if ( !Ext.isEmpty(PrehospWaifRefuseCause_id) ) {
														base_form.findField('PrehospWaifRefuseCause_id').setValue(PrehospWaifRefuseCause_id);
													}

													if ( !Ext.isEmpty(EvnPS_IsTransfCall) ) {
														base_form.findField('EvnPS_IsTransfCall').setValue(EvnPS_IsTransfCall);
													}

													if ( getRegionNick() == 'buryatiya' ) {
														base_form.findField('MedicalCareFormType_id').setContainerVisible(true);
														base_form.findField('LpuSectionProfile_id').showContainer();
														if(base_form.findField('EvnPS_OutcomeDate').getValue() >= new Date(2019, 6, 1)){
															base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
														}
													}
												break;

												case 'osmpp': // Осмотрен в приемном отделении
													base_form.findField('PrehospWaifRefuseCause_id').setContainerVisible(true);
													base_form.findField('ResultClass_id').setAllowBlank(false);
													base_form.findField('ResultClass_id').setContainerVisible(true);
													base_form.findField('ResultDeseaseType_id').setAllowBlank(false);
													base_form.findField('ResultDeseaseType_id').setContainerVisible(true);
													diag_a_phase_combo.setAllowBlank(false);

													if ( getRegionNick().inlist([ 'buryatiya' ]) ) {
														base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
														base_form.findField('MedicalCareFormType_id').setContainerVisible(true);
														base_form.findField('UslugaComplex_id').setAllowBlank(false);
														base_form.findField('UslugaComplex_id').setContainerVisible(true);
														base_form.findField('LpuSectionProfile_id').showContainer();
														if(base_form.findField('EvnPS_OutcomeDate').getValue() >= new Date(2019, 6, 1)){
															base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
														}
													}

													if ( !Ext.isEmpty(PrehospWaifRefuseCause_id) ) {
														base_form.findField('PrehospWaifRefuseCause_id').setValue(PrehospWaifRefuseCause_id);
													}

													if ( !Ext.isEmpty(UslugaComplex_id) ) {
														base_form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
													}

													if ( !Ext.isEmpty(ResultClass_id) ) {
														base_form.findField('ResultClass_id').setValue(ResultClass_id);
													}

													if ( !Ext.isEmpty(ResultDeseaseType_id) ) {
														base_form.findField('ResultDeseaseType_id').setValue(ResultDeseaseType_id);
													}
												break;
											}

											this.refreshFieldsVisibility('DeseaseType_id','TumorStage_id');
										}
										else{
											if ( getRegionNick() == 'buryatiya' ) {
												base_form.findField('LpuSectionProfile_id').hideContainer();
												base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
											}
										}

									}.createDelegate(this)
								},
								moreFields: [
									{ name: 'LeaveType_fedid', mapping: 'LeaveType_fedid' }
								],
								tabIndex:this.tabIndex + 45,
								width:300,
								xtype:'swcommonsprcombo'
							}, {
                                hiddenName: 'LpuSection_eid',
                                fieldLabel: 'Госпитализирован в',
                                id: 'EPSEF_LpuSectionCombo',
                                tabIndex: this.tabindex + 46,
                                width: 500,
                                lastQuery: '',
                                xtype: 'swlpusectionglobalcombo',
                                listeners:
                                {
                                    'select': function (combo,record,index)
                                    {
										var base_form = this.findById('EvnPSEditForm').getForm();
                                    	var ward_combo = base_form.findField('LpuSectionWard_id');
										var bedprofile_combo = base_form.findField('LpuSectionBedProfileLink_id');
                                    	if ( !Ext.isEmpty(record.get('LpuSection_id')) )
                                        {
											if (!this.isProcessLoadForm && Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())) {
												base_form.findField('EvnPS_OutcomeTime').onTriggerClick();
											}
                                            var rc_combo = this.findById('EPSEF_PrehospWaifRefuseCause_id');
                                            var oldValue = rc_combo.getValue();
                                            rc_combo.clearValue();
                                            rc_combo.fireEvent('change',rc_combo,'',oldValue);
											if (getRegionNick().inlist(['msk','vologda','ufa'])) {
												ward_combo.showContainer();
												win.wardOnSexFilter();
											}
											if (getRegionNick().inlist(['msk'])) {
												bedprofile_combo.showContainer();
												bedprofile_combo.setAllowBlank(false);
												win.bedProfileByWardFilter();
											}
                                        } else {
											ward_combo.setValue(null);
											ward_combo.hideContainer();
											bedprofile_combo.setValue(null);
											bedprofile_combo.hideContainer();
											bedprofile_combo.setAllowBlank(true);
										}
										if ( getRegionNick() == 'krym' ) {
											win.setMedicalCareFormType();
											win.setMedicalCareFormTypeAllowBlank();
										}

										combo.fireEvent('change', combo, (typeof record == 'object' ? record.get('LpuSection_id') : null));
                                    }.createDelegate(this),
									'change': function(combo, newValue) {
										this.setCovidFieldsAllowBlank();
									}.createDelegate(this)
                                }
                            }, {
								hiddenName: 'LpuSectionWard_id',
								fieldLabel: langs('Палата'),
								allowBlank: true,
								width: 500,
								parentElementId: 'EPSPEF_LpuSectionCombo',
								id: 'EPSPEF_LpuSectionWardCombo',
								tabIndex: TABINDEX_EPSPEF + 47,
								xtype: 'swlpusectionwardglobalcombo',
								listeners: {
									'select': function(){
										win.bedProfileByWardFilter();
									}
								}
							}, {
								hiddenName: 'LpuSectionBedProfileLink_id',
								fieldLabel: langs('Профиль коек'),
								allowBlank: true,
								width: 500,
								parentElementId: 'EPSPEF_LpuSectionCombo',
								id: 'EPSPEF_LpuSectionBedProfileCombo',
								tabIndex: TABINDEX_EPSPEF + 47,
								xtype: 'swlpusectionbedprofileglobalcombo',
								listeners: {
									'select': function(){
										win.wardOnSexFilter();
									}
								}
							}, {
                                hiddenName: 'PrehospWaifRefuseCause_id',
                                id: 'EPSEF_PrehospWaifRefuseCause_id',
                                fieldLabel: 'Отказ',
                                tabIndex: this.tabindex + 47,
                                width: 500,
                                comboSubject: 'PrehospWaifRefuseCause',
                                autoLoad: false,
								typeCode: 'int',
                                xtype: 'swcommonsprcombo',
								onLoadStore: function (store) {

                                	if (getRegionNick() !== 'kareliya')
									{
										return true;
									}

                                	store.each(function(rec) {
                                		if ( ! rec.get('PrehospWaifRefuseCause_id').inlist([2,3,5,9,10,11]) ) // ограничить для карелии список причин отказа (#129610)
										{
											store.remove(rec); // убираем, потому что filterBy не помогает
										}
										return true;
									});

                                	return true;
								},
                                listeners:
                                {
                                    'change': function (combo,newValue,oldValue) {
										win.setMedicalCareFormType();
										win.setMedicalCareFormTypeAllowBlank();
										win.setSpecificsPanelVisibility();

										var index = combo.getStore().findBy(function(rec) {
											return (rec.get(combo.valueField) == newValue);
										});
										combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
										
										win.refreshFieldsVisibility([ 'DeseaseType_id' ]);
									},
									'select': function(combo, record, idx) {
                                        var base_form = this.findById('EvnPSEditForm').getForm();
                                        var is_transf_call_combo = base_form.findField('EvnPS_IsTransfCall');
										var toolbar = this.findById('EPSEF_EvnSectionGrid').getTopToolbar();

										base_form.findField('EvnPS_RepFlag').hideContainer();
										if ( getRegionNick() == 'perm' && base_form.findField('EvnSection_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnPS_IndexRepInReg').getValue()) > 0 && (!record || !record.get('PrehospWaifRefuseCause_Code').inlist([1,9])) ) {
											base_form.findField('EvnPS_RepFlag').showContainer();

											if ( parseInt(base_form.findField('EvnPS_IndexRep').getValue()) >= parseInt(base_form.findField('EvnPS_IndexRepInReg').getValue()) ) {
												base_form.findField('EvnPS_RepFlag').setValue(true);
											}
											else {
												base_form.findField('EvnPS_RepFlag').setValue(false);
											}
										}

										if( !record || Ext.isEmpty(record.get(combo.valueField)) ) {
                                            is_transf_call_combo.disable();
                                            this.findById('EPSEF_PrehospWaifRefuseCauseButton').disable();
                                            Ext.getCmp('EPSEF_EvnSection_add').setDisabled( this.action == 'view' );
											toolbar.items.items[0].setDisabled( this.action == 'view' );
											
											if (getRegionNick().inlist([ 'ekb' ])) {
												base_form.findField('LpuSectionProfile_id').hideContainer();
												base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
												base_form.findField('UslugaComplex_id').hideContainer();
												base_form.findField('UslugaComplex_id').setAllowBlank(true);
											}
											if (getRegionNick().inlist([ 'krym' ])) {
												base_form.findField('LpuSectionProfile_id').clearValue();
												base_form.findField('LpuSectionProfile_id').hideContainer();
												base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
											}
											if (getRegionNick().inlist([ 'penza' ])) {
												base_form.findField('LpuSectionProfile_id').hideContainer();
												base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
											}
											if (getRegionNick().inlist([ 'perm' ])) {
												base_form.findField('LpuSectionProfile_id').clearValue();
												base_form.findField('LpuSectionProfile_id').hideContainer();
												base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
												base_form.findField('UslugaComplex_id').clearValue();
												base_form.findField('UslugaComplex_id').hideContainer();
												base_form.findField('UslugaComplex_id').setAllowBlank(true);
												base_form.findField('LeaveType_fedid').clearValue();
												base_form.findField('LeaveType_fedid').hideContainer();
												base_form.findField('LeaveType_fedid').setAllowBlank(true);
												base_form.findField('ResultDeseaseType_fedid').clearValue();
												base_form.findField('ResultDeseaseType_fedid').hideContainer();
												base_form.findField('ResultDeseaseType_fedid').setAllowBlank(true);
											}
											if (getRegionNick().inlist([ 'kareliya', 'krym', 'penza' ])) {
												base_form.findField('ResultClass_id').clearValue();
												base_form.findField('ResultClass_id').hideContainer();
												base_form.findField('ResultClass_id').setAllowBlank(true);
												base_form.findField('ResultDeseaseType_id').clearValue();
												base_form.findField('ResultDeseaseType_id').hideContainer();
												base_form.findField('ResultDeseaseType_id').setAllowBlank(true);
											}
										}
										else {
											if (!this.isProcessLoadForm && Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())) {
												base_form.findField('EvnPS_OutcomeTime').onTriggerClick();
											}
											
											is_transf_call_combo.setDisabled(this.action == 'view');
											this.findById('EPSEF_PrehospWaifRefuseCauseButton').enable();
											this.findById('EPSEF_LpuSectionCombo').clearValue();
											Ext.getCmp('EPSEF_EvnSection_add').setDisabled( true );
											toolbar.items.items[0].disable();
											
											if (getRegionNick().inlist([ 'ekb' ])) {
												base_form.findField('UslugaComplex_id').showContainer();
												base_form.findField('LpuSectionProfile_id').showContainer();
												base_form.findField('UslugaComplex_id').setAllowBlank(false);
												base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
											}
											if (getRegionNick().inlist([ 'krym' ])) {
												base_form.findField('LpuSectionProfile_id').showContainer();
												base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
											}
											if (getRegionNick().inlist([ 'penza' ])) {
												base_form.findField('LpuSectionProfile_id').showContainer();
												base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
											}
											if (getRegionNick().inlist([ 'perm' ])) {
												base_form.findField('LeaveType_fedid').showContainer();
												base_form.findField('LeaveType_fedid').setAllowBlank(false);
												base_form.findField('ResultDeseaseType_fedid').showContainer();
												base_form.findField('ResultDeseaseType_fedid').setAllowBlank(false);

												// Поля "Профиль" и "Код посещения"
												switch ( record.get('PrehospWaifRefuseCause_Code') ) {
													case 1:
													case 9:
														base_form.findField('LpuSectionProfile_id').clearValue();
														base_form.findField('LpuSectionProfile_id').hideContainer();
														base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
														base_form.findField('UslugaComplex_id').clearValue();
														base_form.findField('UslugaComplex_id').hideContainer();
														base_form.findField('UslugaComplex_id').setAllowBlank(true);
													break;

													default:
														base_form.findField('LpuSectionProfile_id').showContainer();
														base_form.findField('UslugaComplex_id').showContainer();

														if (base_form.findField('LpuSectionProfile_id').getStore().getCount() == 1) {
															base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id'));
														}
													break;
												}

												if ( !this.isProcessLoadForm ) {
													// Устанавливаем фед. результат
													switch ( record.get('PrehospWaifRefuseCause_Code') ) {
														case 1:
														case 3:
														case 4:
														case 5:
															base_form.findField('LeaveType_fedid').setFieldValue('LeaveType_Code', '303');
														break;

														case 2:
														case 8:
															base_form.findField('LeaveType_fedid').setFieldValue('LeaveType_Code', '302');
														break;

														case 10:
															base_form.findField('LeaveType_fedid').setFieldValue('LeaveType_Code', '313');
														break;

														default:
															base_form.findField('LeaveType_fedid').setFieldValue('LeaveType_Code', '301');
														break;
													}

													// Устанавливаем фед. исход
													switch ( record.get('PrehospWaifRefuseCause_Code') ) {
														case 10:
															base_form.findField('ResultDeseaseType_fedid').setFieldValue('ResultDeseaseType_Code', '305');
														break;

														default:
															base_form.findField('ResultDeseaseType_fedid').setFieldValue('ResultDeseaseType_Code', '304');
														break;
													}
												}
											}
											if (getRegionNick().inlist([ 'kareliya', 'krym', 'penza' ])) {
												base_form.findField('ResultClass_id').showContainer();
												base_form.findField('ResultClass_id').setAllowBlank(false);
												base_form.findField('ResultDeseaseType_id').showContainer();
												base_form.findField('ResultDeseaseType_id').setAllowBlank(false);

												if ( record.get('PrehospWaifRefuseCause_Code') == 10 ) {
													base_form.findField('ResultClass_id').setFieldValue('ResultClass_Code', '313');
													base_form.findField('ResultDeseaseType_id').setFieldValue('ResultDeseaseType_Code', '305');
												}
											}
                                        }

										this.refreshFieldsVisibility(['DeseaseType_id','TumorStage_id']);
                                    }.createDelegate(this)
                                }
                            }, {
								border: false,
								hidden: !(getRegionNick().inlist([ 'buryatiya', 'ekb', 'perm', 'kareliya', 'krym', 'ufa', 'penza' ])),
								layout: 'form',
								items: [{
									comboSubject: 'MedicalCareFormType',
									hiddenName: 'MedicalCareFormType_id',
									fieldLabel: 'Форма помощи',
									lastQuery: '',
									prefix: 'nsi_',
									xtype: 'swcommonsprcombo'
								}]
							}, {
								border: false,
								hidden: !(getRegionNick().inlist([ 'ekb', 'krym', 'perm', 'penza','buryatiya' ])),
								layout: 'form',
								items: [{
									fieldLabel: 'Профиль',
									hiddenName: 'LpuSectionProfile_id',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											if ( !getRegionNick().inlist([ 'perm' ]) ) {
												return false;
											}

											var index = combo.getStore().findBy(function(rec) {
												return (rec.get(combo.valueField) == newValue);
											});
											combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
										},
										'select': function(combo, record, idx) {
											this.reloadUslugaComplexField();
										}.createDelegate(this)
									},
									listWidth: 600,
									tabIndex:this.tabIndex + 48,
									width: 500,
									xtype: 'swlpusectionprofileekbremotecombo'
								}]
                            }, {
								border: false,
								hidden: !(getRegionNick().inlist([ 'buryatiya', 'ekb', 'perm' ])), // Открыто для Бурятии, Екатеринбурга и Перми
								layout: 'form',
								items: [{
									fieldLabel: 'Код посещения',
									hiddenName: 'UslugaComplex_id',
									listeners: {
										'change': function(combo, newValue) {
											if (getRegionNick() == 'perm') {
												var base_form = win.findById('EvnPSEditForm').getForm();
												base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
												if (!Ext.isEmpty(newValue)) {
													// поле профиль обязательное
													base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
												}
											}
										}
									},
									listWidth: 600,
									tabIndex:this.tabIndex + 49,
									width: 500,
									xtype: 'swuslugacomplexnewcombo'
								}]
							}, {
								border: false,
								hidden: !(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'krym', 'pskov', 'penza' ])),
								layout: 'form',
								items: [{
									fieldLabel: 'Результат обращения',
									hiddenName: 'ResultClass_id',
									listWidth: 600,
									tabIndex:this.tabIndex + 50,
									width: 500,
									xtype: 'swresultclasscombo'
								}, {
									comboSubject: 'ResultDeseaseType',
									fieldLabel: 'Исход',
									hiddenName: 'ResultDeseaseType_id',
									lastQuery: '',
									listWidth: 600,
									tabIndex:this.tabIndex + 51,
									width: 500,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								border: false,
								hidden: !(getRegionNick().inlist([ 'perm' ])), // Открыто для Перми
								layout: 'form',
								items: [{
									fieldLabel: 'Фед. результат',
									hiddenName: 'LeaveType_fedid',
									lastQuery: '',
									listWidth: 600,
									tabIndex:this.tabIndex + 52,
									width: 500,
									xtype: 'swleavetypefedcombo'
								}, {
									//disabled: true,
									fieldLabel: 'Фед. исход',
									hiddenName: 'ResultDeseaseType_fedid',
									lastQuery: '',
									listWidth: 600,
									tabIndex:this.tabIndex + 53,
									width: 500,
									xtype: 'swresultdeseasetypefedcombo'
								}]
							}, {
                                border: false,
                                layout: 'column',
                                items: [{
                                    border: false,
                                    layout: 'form',
                                    width: 300,
                                    items: [{
                                        allowBlank: true,
                                        id: 'EPSEF_EvnPS_IsTransfCall',
                                        tabIndex: this.tabindex + 54,
                                        comboSubject: 'YesNo',
                                        fieldLabel: 'Передан активный вызов',
                                        hiddenName: 'EvnPS_IsTransfCall',
                                        width: 100,
                                        value: 1,
                                        xtype: 'swcommonsprcombo'
                                    }]
                                }, {
                                    border: false,
                                    layout: 'form',
                                    width: 300,
                                    items: [{
                                        handler: function() {
                                            if ( this.action == 'add' /*&& Number(this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()) == 0 */) {
                                                this.doSave({
                                                    printRefuse: true
                                                });
                                            }
                                            else{
												printBirt({
													'Report_FileName': 'printEvnPSPrehospWaifRefuseCause.rptdesign',
													'Report_Params': '&paramEvnPsID=' + this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue(),
													'Report_Format': 'pdf'
												});
                                            }
                                        }.createDelegate(this),
                                        iconCls: 'print16',
                                        id: 'EPSEF_PrehospWaifRefuseCauseButton',
                                        tabIndex: this.tabindex + 55,
                                        text: 'Справка об отказе в госпитализации',
                                        tooltip: 'Справка об отказе в госпитализации',
                                        xtype: 'button'
                                    }]
                                }]
                            }, {
								xtype: 'swdiagsetphasecombo',
								hiddenName: 'DiagSetPhase_aid',
								fieldLabel: langs('Состояние пациента при выписке'),
								width: 300,
								tabIndex: this.tabindex + 56,
								editable: false
							}]
                        }),
                        new sw.Promed.Panel({
                            border: true,
                            collapsible: true,
                            height: 150,
                            id: 'EPSEF_EvnSectionPanel',
                            isLoaded: false,
                            layout: 'border',
                            listeners: {
                                'expand': function(panel) {
                                    if ( panel.isLoaded === false ) {
                                        panel.isLoaded = true;
                                        panel.findById('EPSEF_EvnSectionGrid').getStore().load({
                                            params: {
                                                EvnSection_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
                                            }
                                        });
                                    }

                                    panel.doLayout();
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '6. Движение',
                            items: [ new Ext.grid.GridPanel({
                                autoExpandColumn: 'autoexpand_section',
                                autoExpandMin: 100,
                                border: false,
                                columns: [{
                                    dataIndex: 'EvnSection_setDate',
                                    header: 'Поступление',
                                    hidden: false,
                                    renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'EvnSection_disDate',
                                    header: 'Выписка',
                                    hidden: false,
                                    renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'LpuSection_Name',
                                    header: 'Отделение ЛПУ',
                                    hidden: false,
                                    id: 'autoexpand_section',
                                    resizable: true,
                                    sortable: true
                                }, {
                                    dataIndex: 'MedPersonal_Fio',
                                    header: 'ФИО врача',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 200
                                }, {
                                    dataIndex: 'LpuSectionWard_Name',
                                    header: 'Палата',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 200
                                }, {
                                    dataIndex: 'LpuSectionProfile_Name',
                                    header: 'Профиль',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 150
                                }, {
                                	dataIndex: 'LpuSectionBedProfile_Name',
                                	header: 'Профиль койки',
                                	hidden: false,
                                	resizable: true,
                                	sortable: true,
                                	width: 150
                                }, {
                                    dataIndex: 'PayType_Name',
                                    header: 'Вид оплаты',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'Diag_Name',
                                    header: 'Основной диагноз',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 200
                                }, {
                                    dataIndex: 'EvnSection_KoikoDni',
                                    header: 'К/дни',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 50
                                }, {
                                    dataIndex: 'EvnSection_KoikoDniNorm',
                                    header: 'Норматив',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 80
                                }, {
                                    dataIndex: 'LeaveType_Name',
                                    header: (getRegionNick().inlist([ 'kareliya' ])?'Результат госпитализации':'Исход госпитализации'),
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 120
                                }, {
									dataIndex: 'EvnSection_IndexNum',
									header: 'Группировка',
									hidden: !getRegionNick().inlist(['astra', 'perm', 'penza', 'vologda', 'buryatiya', 'krym']),
									resizable: true,
									sortable: true,
									width: 80
								}, {
									dataIndex: 'EvnSection_KSG',
									header: 'КСГ',
									hidden: true,
									hideable: true,
									resizable: true,
									sortable: true,
									width: 80
								}, {
									dataIndex: 'EvnSection_KPG',
									header: 'КПГ',
									hidden: true,
									hideable: getRegionNick() == 'krym',
									resizable: true,
									sortable: true,
									width: 80
								}, {
                                    dataIndex: 'LpuUnitType_id',
                                    header: 'Тип LpuUnit',
                                    hidden: true
                                }, {
                                    dataIndex: 'isLast',
                                    header: 'Последнее движение',
                                    hidden: true
                                }],
                                frame: false,
                                height: 200,
                                id: 'EPSEF_EvnSectionGrid',
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

                                        var grid = this.findById('EPSEF_EvnSectionGrid');

                                        switch ( e.getKey() ) {
                                            case Ext.EventObject.DELETE:
                                                this.deleteEvent('EvnSection');
                                                break;

                                            case Ext.EventObject.END:
                                                GridEnd(grid);
                                                break;

                                            case Ext.EventObject.F3:
												if ( !e.altKey ) {
													if ( !grid.getSelectionModel().getSelected() ) {
														return false;
													}

													var action = 'view';

													this.openEvnSectionEditWindow(action);
												} else {
													var params = new Object();
													params['key_id'] = grid.getSelectionModel().getSelected().data.EvnSection_id;
													params['key_field'] = 'EvnSection_id';
													getWnd('swAuditWindow').show(params);
												}
											break;

                                            case Ext.EventObject.ENTER:
                                            case Ext.EventObject.F4:
                                            case Ext.EventObject.INSERT:
                                                if ( !grid.getSelectionModel().getSelected() ) {
                                                    return false;
                                                }

                                                var action = 'add';

                                                if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
                                                    action = 'edit';
                                                }

                                                this.openEvnSectionEditWindow(action);
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
                                                var base_form = this.findById('EvnPSEditForm').getForm();

                                                grid.getSelectionModel().clearSelections();
                                                grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

                                                if ( e.shiftKey == false ) {
                                                    if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( this.action != 'view' ) {
                                                        this.buttons[0].focus();
                                                    }
                                                    else {
                                                        this.buttons[1].focus();
                                                    }
                                                }
                                                else {
                                                    if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
                                                        if ( !base_form.findField('Diag_pid').disabled ) {
                                                            base_form.findField('Diag_pid').focus(true);
                                                        }
                                                        else {
                                                            base_form.findField('MedStaffFact_pid').focus(true);
                                                        }
                                                    }
                                                    else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
                                                        base_form.findField('EvnPS_IsDiagMismatch').focus(true);
                                                    }
                                                    else {
                                                        this.buttons[this.buttons.length - 1].focus();
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
                                        var access_type = 'view',
                                            action = 'view',
                                            id = null,
                                            selected_record = grid.getSelectionModel().getSelected();

                                        if ( selected_record ) {
                                            access_type = selected_record.get('accessType');
                                            id = selected_record.get('EvnSection_id');
                                        }

										if (
											!Ext.isEmpty(id)
											&& this.action != 'view'
											&& access_type == 'edit'
											&& (
												Ext.isEmpty(getGlobalOptions().medpersonal_id)
												|| Ext.isEmpty(selected_record.get('MedPersonal_id'))
												|| userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == true
												|| getGlobalOptions().isMedStatUser == true
												|| isSuperAdmin() == true
											)
											//&& selected_record.get('isLast') == 1
											&& selected_record.get('EvnSection_IsSigned') == 1
										) {
											action = 'edit';
										}

                                        this.openEvnSectionEditWindow(action);
                                    }.createDelegate(this)
                                },
                                loadMask: true,
                                region: 'center',
                                sm: new Ext.grid.RowSelectionModel({
                                    listeners: {
                                        'rowselect': function(sm, rowIndex, record) {
                                            var access_type = 'view',
                                                id = null,
                                                selected_record = sm.getSelected(),
												grid = this.findById('EPSEF_EvnSectionGrid'),
                                                toolbar = this.findById('EPSEF_EvnSectionGrid').getTopToolbar();

                                            if ( selected_record ) {
                                                access_type = selected_record.get('accessType');
                                                id = selected_record.get('EvnSection_id');
                                            }

                                            toolbar.items.items[1].disable();
                                            toolbar.items.items[2].disable();
                                            toolbar.items.items[3].disable();
                                            toolbar.items.items[5].disable();
                                            toolbar.items.items[6].disable();

                                            // наполняем меню для группировки
											toolbar.items.items[6].menu.removeAll();
											var counter = 0;
											var hasIndexNum = false;
											grid.getStore().each(function(rec) {
												if (!Ext.isEmpty(rec.get('EvnSection_id'))) {
													counter++;
													var localCounter = counter;
													toolbar.items.items[6].menu.add({
														text: localCounter,
														handler: function () {
															win.setEvnSectionIndexNum(localCounter);
														}
													});
												}

												if (!Ext.isEmpty(rec.get('EvnSection_IndexNum'))) {
													hasIndexNum = true;
												}
											});

											if (
												this.action != 'view'
												&& counter > 0
												&& hasIndexNum
												&& (
													getRegionNick() != 'penza'
													|| !Ext.isEmpty(selected_record.get('EvnSection_IndexNum'))
												)
												&& selected_record.get('EvnSection_IsMultiKSG') != 2
											) {
												toolbar.items.items[6].enable();
											}

											var last_evn_section_info =  win.getEvnSectionInfo('last');
											if (last_evn_section_info && last_evn_section_info.LeaveType_Code && last_evn_section_info.LeaveType_Code.toString().inlist([ '1', '2', '3', '4' ])) {
												toolbar.items.items[5].enable();
											}

                                            if ( id ) {
                                                toolbar.items.items[2].enable();

												// Кнопка "Изменить"
												if (
													getGlobalOptions().registry_disable_edit_inreg != 2
													&& (this.action != 'view' || (this.gridAccess != 'view' && selected_record.get('EvnSection_IsPaid') != 2))
													&& access_type == 'edit'
													&& (
														Ext.isEmpty(getGlobalOptions().medpersonal_id)
														|| Ext.isEmpty(selected_record.get('MedPersonal_id'))
														|| userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == true
														|| getGlobalOptions().isMedStatUser == true
														|| isSuperAdmin() == true
													)
													//&& selected_record.get('isLast') == 1
													&& selected_record.get('EvnSection_IsSigned') == 1
												) {
													toolbar.items.items[1].enable();
												}
												if(/*selected_record.get('HasWorkGraph') == 1 && */selected_record.get('accessType') && this.action != 'view')
												{
													toolbar.items.items[1].enable();
												}
												// Кнопка "Удалить"
												if (
													this.action != 'view'
													&& access_type == 'edit'
													&& (
														Ext.isEmpty(getGlobalOptions().medpersonal_id)
														|| Ext.isEmpty(selected_record.get('MedPersonal_id'))
														|| (userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == true && selected_record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)
														|| getGlobalOptions().isMedStatUser == true
														|| isSuperAdmin() == true
													)
													&& (
														selected_record.get('isLast') == 1
														|| getGlobalOptions().isMedStatUser == true
														|| isSuperAdmin() == true
													)
													&& selected_record.get('EvnSection_IsSigned') == 1
												) {
													toolbar.items.items[3].enable();
												}
                                            }
                                        }.createDelegate(this)
                                    }
                                }),
                                stripeRows: true,
                                store: new Ext.data.Store({
                                    autoLoad: false,
                                    listeners: {
                                        'load': function(store, records, index) {
											this.checkForCostPrintPanel();

                                            if ( store.getCount() == 0 ) {
                                                LoadEmptyRow(this.findById('EPSEF_EvnSectionGrid'));
                                            }

                                            if (!Ext.isEmpty(records) && records.length > 0) {
                                            	if (Ext.isEmpty(win.ChildLpuSection_id))
                                            		win.ChildLpuSection_id = records[0].data.LpuSection_id;
												win.ChildLpuSection_disDate = records[0].data.EvnSection_disDate;
											}
                                            // this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                                            // this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                                        }.createDelegate(this)
                                    },
                                    reader: new Ext.data.JsonReader({
                                        id: 'EvnSection_id'
                                    }, [{
                                        mapping: 'accessType',
                                        name: 'accessType',
                                        type: 'string'
                                    },
									{
                                        mapping: 'EvnSection_id',
                                        name: 'EvnSection_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnSection_IsSigned',
                                        name: 'EvnSection_IsSigned',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnSection_pid',
                                        name: 'EvnSection_pid',
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
                                        mapping: 'DiagSetPhase_id',
                                        name: 'DiagSetPhase_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnSection_PhaseDescr',
                                        name: 'EvnSection_PhaseDescr',
                                        type: 'string'
                                    }, {
                                        mapping: 'LeaveType_Code',
                                        name: 'LeaveType_Code',
                                        type: 'int'
                                    }, {
                                        mapping: 'LeaveType_SysNick',
                                        name: 'LeaveType_SysNick',
                                        type: 'string'
                                    }, {
                                        mapping: 'CureResult_Code',
                                        name: 'CureResult_Code',
                                        type: 'int'
                                    }, {
                                        mapping: 'LeaveType_id',
                                        name: 'LeaveType_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'LpuSection_id',
                                        name: 'LpuSection_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'LpuSectionProfile_id',
                                        name: 'LpuSectionProfile_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'LpuSectionWard_id',
                                        name: 'LpuSectionWard_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'MedPersonal_id',
                                        name: 'MedPersonal_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'MedStaffFact_id',
                                        name: 'MedStaffFact_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'PayType_id',
                                        name: 'PayType_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'Mes_id',
                                        name: 'Mes_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'TariffClass_id',
                                        name: 'TariffClass_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnSection_setTime',
                                        name: 'EvnSection_setTime',
                                        type: 'string'
                                    }, {
                                        mapping: 'EvnSection_disTime',
                                        name: 'EvnSection_disTime',
                                        type: 'string'
                                    }, {
                                        dateFormat: 'd.m.Y',
                                        mapping: 'EvnSection_setDate',
                                        name: 'EvnSection_setDate',
                                        type: 'date'
                                    }, {
                                        dateFormat: 'd.m.Y',
                                        mapping: 'EvnSection_disDate',
                                        name: 'EvnSection_disDate',
                                        type: 'date'
                                    }, {
                                        mapping: 'LeaveType_Name',
                                        name: 'LeaveType_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'LpuSection_Name',
                                        name: 'LpuSection_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'LpuSectionProfile_Name',
                                        name: 'LpuSectionProfile_Name',
                                        type: 'string'
                                    }, {
                                    	mapping: 'LpuSectionBedProfile_Name',
                                    	name: 'LpuSectionBedProfile_Name',
                                    	type: 'string'
                                    }, {
                                        mapping: 'LpuSectionWard_Name',
                                        name: 'LpuSectionWard_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'LpuUnitType_id',
                                        name: 'LpuUnitType_id',
                                        type: 'string'
                                    }, {
                                        mapping: 'LpuUnitType_SysNick',
                                        name: 'LpuUnitType_SysNick',
                                        type: 'string'
                                    }, {
                                        mapping: 'MedPersonal_Fio',
                                        name: 'MedPersonal_Fio',
                                        type: 'string'
                                    }, {
                                        mapping: 'PayType_Name',
                                        name: 'PayType_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'Diag_Code',
                                        name: 'Diag_Code',
                                        type: 'string'
                                    },  {
                                        mapping: 'Diag_Name',
                                        name: 'Diag_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'EvnSection_KoikoDni',
                                        name: 'EvnSection_KoikoDni',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnSection_KoikoDniNorm',
                                        name: 'EvnSection_KoikoDniNorm',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnSection_KSG',
                                        name: 'EvnSection_KSG',
                                        type: 'string'
                                    }, {
                                        mapping: 'EvnSection_KPG',
                                        name: 'EvnSection_KPG',
                                        type: 'string'
                                    }, {
                                        mapping: 'EvnSection_KOEF',
                                        name: 'EvnSection_KOEF',
                                        type: 'string'
                                    }, {
                                        mapping: 'Mes_rid',
                                        name: 'Mes_rid',
                                        type: 'string'
                                    }, {
                                        mapping: 'Mes_Code',
                                        name: 'Mes_Code',
                                        type: 'string'
                                    }, {
                                        mapping: 'MesType_id',
                                        name: 'MesType_id',
                                        type: 'string'
                                    }, {
                                        mapping: 'EvnSection_IndexNum',
                                        name: 'EvnSection_IndexNum',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnSection_IsMultiKSG',
                                        name: 'EvnSection_IsMultiKSG',
                                        type: 'int'
                                    }, {
                                        mapping: 'isLast',
                                        name: 'isLast',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnSection_IsPaid',
                                        name: 'EvnSection_IsPaid',
                                        type: 'int'
                                    }, {
										mapping: 'DeseaseBegTimeType_id',
										name: 'DeseaseBegTimeType_id',
										type: 'int'
									}]),
                                    url: '/?c=EvnSection&m=loadEvnSectionGrid'
                                }),
                                tbar: new sw.Promed.Toolbar({
									buttons: [{
										id: 'EPSEF_EvnSection_add',
										handler: function() {
											this.openEvnSectionEditWindow('add');
										}.createDelegate(this),
										iconCls: 'add16',
										text: 'Добавить'
									}, {
										handler: function() {
											this.openEvnSectionEditWindow('edit');
										}.createDelegate(this),
										iconCls: 'edit16',
										text: 'Изменить'
									}, {
										handler: function() {
											this.openEvnSectionEditWindow('view');
										}.createDelegate(this),
										iconCls: 'view16',
										text: 'Просмотр'
									}, {
										handler: function() {
											this.deleteEvent('EvnSection');
										}.createDelegate(this),
										iconCls: 'delete16',
										text: 'Удалить'
									}, {
										iconCls: 'print16',
										text: 'Печать',
										menu: [
											{
												iconCls: 'print16',
												text: 'Печать',
												handler: function() {
													this.printEvnPS('5', 'EvnPS');
												}.createDelegate(this),
											},
											{
												iconCls: 'print16',
												text: 'Печать КЛУ при ЗНО',
												handler: function ()
												{
													this.printControlCardZno()
												}.createDelegate(this)
											},
											{
												iconCls: 'print16',
												text: 'Печать выписки при онкологии',
												hidden: getRegionNick() != 'ekb',
												handler: function ()
												{
													this.printControlCardOnko()
												}.createDelegate(this)
											}
										],
									}, {
										handler: function() {
											this.printEvnPSKSG();
										}.createDelegate(this),
										hidden: getRegionNick() != 'ufa',
										iconCls: 'print16',
										text: 'Расчет КСГ'
									}, {
										menu: [],
										hidden: !getRegionNick().inlist(['perm', 'penza']),
										text: 'Группировка'
									}]
                                })
                            })]
                        }),
                        new sw.Promed.Panel({
                            border: true,
                            collapsible: true,
                            height: 200,
                            id: 'EPSEF_EvnStickPanel',
                            isLoaded: false,
                            layout: 'border',
                            listeners: {
                                'expand': function(panel) {
                                    if ( panel.isLoaded === false ) {
                                        panel.isLoaded = true;
                                    }
                                    panel.doLayout();
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '7. Нетрудоспособность',
                            items: [ new Ext.grid.GridPanel({
                                autoExpandColumn: 'autoexpand_stick',
                                autoExpandMin: 100,
                                border: false,
                                columns: [{
                                    dataIndex: 'EvnStick_ParentTypeName',
                                    header: 'ТАП/КВС',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'EvnStick_ParentNum',
                                    header: 'Номер ТАП/КВС',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 120
                                }, {
                                    dataIndex: 'StickType_Name',
                                    header: 'Вид документа',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 150
                                }, {
                                    dataIndex: 'EvnStick_IsOriginal',
                                    header: 'Оригинальность',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 150
                                }, {
                                    dataIndex: 'StickWorkType_Name',
                                    header: 'Тип занятости',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 150
                                }, {
                                    dataIndex: 'EvnStick_setDate',
                                    header: 'Дата выдачи',
                                    hidden: false,
                                    renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'EvnStickWorkRelease_begDate',
                                    header: 'Освобожден с',
                                    hidden: false,
                                    renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'EvnStickWorkRelease_endDate',
                                    header: 'Освобожден по',
                                    hidden: false,
                                    renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'EvnStick_disDate',
                                    header: 'Дата исхода ЛВН',
                                    hidden: false,
                                    renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'EvnStick_Ser',
                                    header: 'Серия',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'EvnStick_Num',
                                    header: 'Номер',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'StickOrder_Name',
                                    header: 'Порядок выписки',
                                    hidden: false,
                                    id: 'autoexpand_stick',
                                    resizable: true,
                                    sortable: true
                                }, {
									dataIndex: 'Lpu_Nick',
									header: 'МО выдавшая',
									hidden: false,
									resizable: true,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'EvnStatus_Name',
									header: 'Тип ЛВН',
									hidden: false,
									resizable: true,
									sortable: true,
									width: 100
								}, {
									dataIndex: 'StickFSSType_Name',
									header: 'Состояние ЛВН в ФСС',
									hidden: getRegionNick() == 'kz',
									resizable: true,
									sortable: true,
									width: 100
								}, {
                                    dataIndex: 'EvnStick_stacBegDate',
                                    hidden: true
                                }, {
                                    dataIndex: 'EvnStick_stacEndDate',
                                    hidden: true
                                }, {
                                    dataIndex: 'EvnSection_setDate',
                                    hidden: true
                                }, {
                                    dataIndex: 'EvnSection_disDate',
                                    hidden: true
                                }],
                                frame: false,
                                id: 'EPSEF_EvnStickGrid',
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

                                        var grid = this.findById('EPSEF_EvnStickGrid');

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
                                                var base_form = this.findById('EvnPSEditForm').getForm();

                                                grid.getSelectionModel().clearSelections();
                                                grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

                                                if ( e.shiftKey == false ) {
                                                    if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( this.action != 'view' ) {
                                                        this.buttons[0].focus();
                                                    }
                                                    else {
                                                        this.buttons[1].focus();
                                                    }
                                                }
                                                else {
                                                    if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
                                                        if ( !base_form.findField('Diag_pid').disabled ) {
                                                            base_form.findField('Diag_pid').focus(true);
                                                        }
                                                        else {
                                                            base_form.findField('MedStaffFact_pid').focus(true);
                                                        }
                                                    }
                                                    else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
                                                        base_form.findField('EvnPS_IsDiagMismatch').focus(true);
                                                    }
                                                    else {
                                                        this.buttons[this.buttons.length - 1].focus();
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
                                            var del_access_type = 'view';
                                            var cancel_access_type = 'view';
                                            var id = null;
                                            var selected_record = sm.getSelected();
                                            var toolbar = this.findById('EPSEF_EvnStickGrid').getTopToolbar();

                                            if ( selected_record ) {
                                                access_type = selected_record.get('accessType');
                                                del_access_type = selected_record.get('delAccessType');
                                                cancel_access_type = selected_record.get('cancelAccessType');
                                                id = selected_record.get('EvnStick_id');
                                            }

                                            toolbar.items.items[1].disable();
                                            toolbar.items.items[3].disable();
                                            toolbar.items.items[4].disable();
											toolbar.items.items[5].disable();
											toolbar.items.items[6].disable();
                                            
                                            if ( id ) {
                                                toolbar.items.items[2].enable();

												if ((this.action != 'view' || isRegLvn() || this.gridAccess != 'view') && access_type == 'edit') {
													toolbar.items.items[1].enable();	

                                                } else if (this.evnStickAction == 'view'){
                                            		toolbar.items.items[0].disable();
												}

												if ( (this.action != 'view' || isRegLvn() || this.gridAccess != 'view') && cancel_access_type == 'edit' ) {
													if (selected_record.get('EvnStick_IsDelQueue') == 2) {
														toolbar.items.items[5].enable();
													} else {
														toolbar.items.items[4].enable();
													}
												}

												if ( selected_record.get('EvnStick_isELN') && !selected_record.get('requestExist') ) {
													toolbar.items.items[6].enable();
												}

												if (this.action != 'view' && del_access_type != 'view') {
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
									url: '/?c=Stick&m=loadEvnStickGrid',
                                    listeners: {
                                        'load': function(store, records, index) {
                                            if ( store.getCount() == 0 ) {
                                                LoadEmptyRow(this.findById('EPSEF_EvnStickGrid'));
                                            }

                                            // this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                                            // this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                                        }.createDelegate(this)
                                    },
                                    reader: new Ext.data.JsonReader(
                                    	{id: 'EvnStick_id'},
										[
											{
												mapping: 'accessType',
												name: 'accessType',
												type: 'string'
											},
											{
												mapping: 'delAccessType',
												name: 'delAccessType',
												type: 'string'
											},
											{
												mapping: 'cancelAccessType',
												name: 'cancelAccessType',
												type: 'string'
											},
											{
												mapping: 'EvnStick_id',
												name: 'EvnStick_id',
												type: 'int'
											},
											{
												mapping: 'EvnStick_mid',
												name: 'EvnStick_mid',
												type: 'int'
											},

											{
												mapping: 'EvnStick_pid',
												name: 'EvnStick_pid',
												type: 'int'
											},

											{
												mapping: 'evnStickType',
												name: 'evnStickType',
												type: 'int'
											},
											{
												mapping: 'parentClass',
												name: 'parentClass',
												type: 'string'
											},
											{
												mapping: 'Person_id',
												name: 'Person_id',
												type: 'int'
											},
											{
												mapping: 'PersonEvn_id',
												name: 'PersonEvn_id',
												type: 'int'
											},
											{
												mapping: 'Server_id',
												name: 'Server_id',
												type: 'int'
											},
											{
												dateFormat: 'd.m.Y',
												mapping: 'EvnStick_setDate',
												name: 'EvnStick_setDate',
												type: 'date'
											},
											{
												dateFormat: 'd.m.Y',
												mapping: 'EvnStickWorkRelease_begDate',
												name: 'EvnStickWorkRelease_begDate',
												type: 'date'
											},
											{
												dateFormat: 'd.m.Y',
												mapping: 'EvnStickWorkRelease_endDate',
												name: 'EvnStickWorkRelease_endDate',
												type: 'date'
											},
											{
												dateFormat: 'd.m.Y',
												mapping: 'EvnStick_disDate',
												name: 'EvnStick_disDate',
												type: 'date'
											},
											{
												mapping: 'StickOrder_Name',
												name: 'StickOrder_Name',
												type: 'string'
											},
											{
												mapping: 'Lpu_Nick',
												name: 'Lpu_Nick',
												type: 'string'
											},
											{
												mapping: 'EvnStatus_Name',
												name: 'EvnStatus_Name',
												type: 'string'
											},
											{
												mapping: 'StickFSSType_Name',
												name: 'StickFSSType_Name',
												type: 'string'
											},
											{
												mapping: 'StickType_Name',
												name: 'StickType_Name',
												type: 'string'
											},
											{
												mapping: 'StickWorkType_Name',
												name: 'StickWorkType_Name',
												type: 'string'
											},
											{
												mapping: 'EvnStick_Ser',
												name: 'EvnStick_Ser',
												type: 'string'
											},
											{
												mapping: 'EvnStick_Num',
												name: 'EvnStick_Num',
												type: 'string'
											},
											{
												mapping: 'EvnStick_ParentTypeName',
												name: 'EvnStick_ParentTypeName',
												type: 'string'
											},
											{
												mapping: 'EvnStick_ParentNum',
												name: 'EvnStick_ParentNum',
												type: 'string'
											},
											{
												mapping: 'EvnStick_IsOriginal',
												name: 'EvnStick_IsOriginal',
												type: 'string'
											},
											{
												mapping: 'EvnStick_stacBegDate',
												name: 'EvnStick_stacBegDate',
												type: 'string'
											},
											{
												mapping: 'EvnStick_stacEndDate',
												name: 'EvnStick_stacEndDate',
												type: 'string'
											},
											{
												mapping: 'EvnSection_setDate',
												name: 'EvnSection_setDate',
												type: 'string'
											},
											{
												mapping: 'EvnSection_disDate',
												name: 'EvnSection_disDate',
												type: 'string'
											},
											{
												mapping: 'EvnStick_IsDelQueue',
												name: 'EvnStick_IsDelQueue',
												type: 'int'
											},
											{
												mapping: 'EvnStick_isELN',
												name: 'EvnStick_isELN',
												type: 'int'
											},
											{
												mapping: 'requestExist',
												name: 'requestExist',
												type: 'int'
											}
										]
									)
                                }),
								view: new Ext.grid.GridView({
									getRowClass: function (row, index) {
										var cls = '';
										if (row.get('EvnStick_IsDelQueue') == 2) {
											cls = cls + 'x-grid-rowbackgray ';
										}
										if (cls.length == 0)
											cls = 'x-grid-panel';
										return cls;
									}
								}),
                                tbar: new sw.Promed.Toolbar({
                                    buttons: [{
                                        handler: function() {
                                            this.openEvnStickEditWindow('add');
                                        }.createDelegate(this),
                                        iconCls: 'add16',
                                        text: 'Добавить'
                                    },{
                                        handler: function() {
                                            this.openEvnStickEditWindow('edit');
                                        }.createDelegate(this),
                                        iconCls: 'edit16',
                                        text: 'Изменить'
                                    }, {
                                        handler: function() {
                                            this.openEvnStickEditWindow('view');
                                        }.createDelegate(this),
                                        iconCls: 'view16',
                                        text: 'Просмотр'
                                    }, {
                                        handler: function() {
                                        	
                                            this.deleteEvent('EvnStick');
                                        }.createDelegate(this),
                                        iconCls: 'delete16',
                                        text: 'Удалить'
                                    }, {
                                    	handler: function() {

                                    		this.deleteEvent('EvnStick', { ignoreQuestion: true });//--
                                    	}.createDelegate(this),
                                    	hidden: getRegionNick() == 'kz',
                                    	text: 'Аннулировать'
                                    }, {
                                        handler: function() {
                                            this.undoDeleteEvnStick();
                                        }.createDelegate(this),
										hidden: getRegionNick() == 'kz',
                                        text: 'Восстановить'
                                    }, {
										handler: function() {
											this.openStickFSSDataEditWindow();
										}.createDelegate(this),
										hidden: getRegionNick() == 'kz',
										disabled: true,
										text: 'Создать запрос в ФСС'
									}]
                                })
                            })]
                        }),
                        new sw.Promed.Panel({
                            border: true,
                            collapsible: true,
                            height: 200,
                            id: 'EPSEF_EvnUslugaPanel',
                            isLoaded: false,
                            layout: 'border',
                            listeners: {
                                'expand': function(panel) {
                                    if ( panel.isLoaded === false ) {
                                        panel.isLoaded = true;
                                        panel.findById('EPSEF_EvnUslugaGrid').getStore().load({
                                            params: {
                                                pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
                                            }
                                        });
                                    }

                                    panel.doLayout();
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '8. Услуги',
                            items: [ new Ext.grid.GridPanel({
                                autoExpandColumn: 'autoexpand_usluga',
                                autoExpandMin: 100,
                                border: false,
                                columns: [{
                                    dataIndex: 'EvnUsluga_setDate',
                                    header: 'Дата',
                                    hidden: false,
                                    renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'EvnUsluga_setTime',
                                    header: 'Время',
                                    hidden: false,
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'Usluga_Code',
                                    header: 'Код',
                                    hidden: false,
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'Usluga_Name',
                                    header: 'Наименование',
                                    hidden: false,
                                    id: 'autoexpand_usluga',
                                    resizable: true,
                                    sortable: true
                                }, {
                                    dataIndex: 'EvnUsluga_Kolvo',
                                    header: 'Количество',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 100
                                }],
                                frame: false,
                                id: 'EPSEF_EvnUslugaGrid',
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

                                        var grid = this.findById('EPSEF_EvnUslugaGrid');

                                        switch ( e.getKey() ) {
                                            case Ext.EventObject.DELETE:
                                                this.deleteEvent('EvnUsluga');
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

                                                this.openEvnUslugaEditWindow(action);
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
                                                var base_form = this.findById('EvnPSEditForm').getForm();

                                                grid.getSelectionModel().clearSelections();
                                                grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

                                                if ( e.shiftKey == false ) {
                                                    if ( this.action != 'view' ) {
                                                        this.buttons[0].focus();
                                                    }
                                                    else {
                                                        this.buttons[1].focus();
                                                    }
                                                }
                                                else {
                                                    if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
                                                        if ( !base_form.findField('Diag_pid').disabled ) {
                                                            base_form.findField('Diag_pid').focus(true);
                                                        }
                                                        else {
                                                            base_form.findField('MedStaffFact_pid').focus(true);
                                                        }
                                                    }
                                                    else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
                                                        base_form.findField('EvnPS_IsDiagMismatch').focus(true);
                                                    }
                                                    else {
                                                        this.buttons[this.buttons.length - 1].focus();
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
											var evnclass_sysnick = null;
                                            var selected_record = sm.getSelected();
                                            var toolbar = this.findById('EPSEF_EvnUslugaGrid').getTopToolbar();

                                            if ( selected_record ) {
                                                access_type = selected_record.get('accessType');
                                                id = selected_record.get('EvnUsluga_id');
												evnclass_sysnick = selected_record.get('EvnClass_SysNick');
                                            }

                                            toolbar.items.items[1].disable();
                                            toolbar.items.items[3].disable();

                                            if ( id ) {
                                                toolbar.items.items[2].enable();

                                                if ( this.action != 'view' /*&& access_type == 'edit'*/ ) {
                                                    toolbar.items.items[1].enable();
													if (evnclass_sysnick != 'EvnUslugaPar') {
														toolbar.items.items[3].enable();
													}
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
                                        'parent': 'EvnPS'
                                    },
                                    listeners: {
                                        'load': function(store, records, index) {
                                            if ( store.getCount() == 0 ) {
                                                LoadEmptyRow(this.findById('EPSEF_EvnUslugaGrid'));
                                            }

                                            // this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                                            // this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                                        }.createDelegate(this)
                                    },
                                    reader: new Ext.data.JsonReader({
                                        id: 'EvnUsluga_id'
                                    }, [{
                                        mapping: 'EvnUsluga_id',
                                        name: 'EvnUsluga_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnClass_SysNick',
                                        name: 'EvnClass_SysNick',
                                        type: 'string'
                                    }, {
                                        mapping: 'EvnUsluga_setTime',
                                        name: 'EvnUsluga_setTime',
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
									}]),
                                    url: '/?c=EvnUsluga&m=loadEvnUslugaGrid'
                                }),
                                tbar: new sw.Promed.Toolbar({
                                    buttons: [{
                                        iconCls: 'add16',
                                        text: 'Добавить',
                                        menu: {
                                            xtype: 'menu',
                                            plain: true,
                                            items: [{
                                                handler: function() {
                                                    this.openEvnUslugaEditWindow('addOper');
                                                }.createDelegate(this),
                                                    text: 'Добавить операцию'
                                            }, {
                                                handler: function() {
                                                    this.openEvnUslugaEditWindow('add');
                                                }.createDelegate(this),
                                                text: 'Добавить общую услугу'
                                            }]
                                        }
                                    }, {
                                        handler: function() {
                                            this.openEvnUslugaEditWindow('edit');
                                        }.createDelegate(this),
                                        iconCls: 'edit16',
                                        text: 'Изменить'
                                    }, {
                                        handler: function() {
                                            this.openEvnUslugaEditWindow('view');
                                        }.createDelegate(this),
                                        iconCls: 'view16',
                                        text: 'Просмотр'
                                    }, {
                                        handler: function() {
                                            this.deleteEvent('EvnUsluga');
                                        }.createDelegate(this),
                                        iconCls: 'delete16',
                                        text: 'Удалить'
                                    }]
                                })
                            })]
                        }),
                        new sw.Promed.Panel({
                            border: true,
                            collapsible: true,
                            height: 200,
                            id: 'EPSEF_EvnDrugPanel',
                            isLoaded: false,
                            layout: 'border',
                            listeners: {
                                'expand': function(panel) {
                                    if ( panel.isLoaded === false ) {
                                        panel.isLoaded = true;
                                        panel.findById('EPSEF_EvnDrugGrid').getStore().load({
                                            params: {
                                                EvnDrug_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
                                            }
                                        });
                                    }

                                    panel.doLayout();
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '9. Использование медикаментов',
                            items: [ new Ext.grid.GridPanel({
                                autoExpandColumn: 'autoexpand_drug',
                                autoExpandMin: 100,
                                border: false,
                                columns: [{
                                    dataIndex: 'EvnDrug_setDate',
                                    header: 'Дата',
                                    hidden: false,
                                    renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'Drug_Code',
                                    header: 'Код',
                                    hidden: false,
                                    resizable: false,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'EvnDrug_Kolvo',
                                    header: 'Количество',
                                    hidden: false,
                                    resizable: true,
                                    sortable: true,
                                    width: 100
                                }, {
                                    dataIndex: 'Drug_Name',
                                    header: 'Наименование',
                                    hidden: false,
                                    id: 'autoexpand_drug',
                                    resizable: true,
                                    sortable: true
                                }],
                                frame: false,
                                id: 'EPSEF_EvnDrugGrid',
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

                                        var grid = this.findById('EPSEF_EvnDrugGrid');

                                        switch ( e.getKey() ) {
                                            case Ext.EventObject.DELETE:
                                                this.deleteEvent('EvnDrug');
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

                                                this.openEvnDrugEditWindow(action);
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
                                                var base_form = this.findById('EvnPSEditForm').getForm();

                                                grid.getSelectionModel().clearSelections();
                                                grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

                                                if ( e.shiftKey == false ) {
                                                    if ( this.action != 'view' ) {
                                                        this.buttons[0].focus();
                                                    }
                                                    else {
                                                        this.buttons[1].focus();
                                                    }
                                                }
                                                else {
                                                    if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
                                                        if ( !base_form.findField('Diag_pid').disabled ) {
                                                            base_form.findField('Diag_pid').focus(true);
                                                        }
                                                        else {
                                                            base_form.findField('MedStaffFact_pid').focus(true);
                                                        }
                                                    }
                                                    else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
                                                        base_form.findField('EvnPS_IsDiagMismatch').focus(true);
                                                    }
                                                    else {
                                                        this.buttons[this.buttons.length - 1].focus();
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
                                        this.openEvnDrugEditWindow('edit');
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
                                            var toolbar = this.findById('EPSEF_EvnDrugGrid').getTopToolbar();

                                            if ( selected_record ) {
                                                access_type = selected_record.get('accessType');
                                                id = selected_record.get('EvnDrug_id');
                                            }

                                            toolbar.items.items[1].disable();
                                            toolbar.items.items[3].disable();

                                            if ( id ) {
                                                toolbar.items.items[2].enable();

                                                if ( this.action != 'view' /*&& access_type == 'edit'*/ ) {
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
                                                LoadEmptyRow(this.findById('EPSEF_EvnDrugGrid'));
                                            }
                                        }.createDelegate(this)
                                    },
                                    reader: new Ext.data.JsonReader({
                                        id: 'EvnDrug_id'
                                    }, [{
                                        mapping: 'EvnDrug_id',
                                        name: 'EvnDrug_id',
                                        type: 'int'
                                    }, {
                                        dateFormat: 'd.m.Y',
                                        mapping: 'EvnDrug_setDate',
                                        name: 'EvnDrug_setDate',
                                        type: 'date'
                                    }, {
                                        mapping: 'Drug_Code',
                                        name: 'Drug_Code',
                                        type: 'string'
                                    }, {
                                        mapping: 'Drug_Name',
                                        name: 'Drug_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'EvnDrug_Kolvo',
                                        name: 'EvnDrug_Kolvo',
                                        type: 'float'
                                    }]),
                                    url: '/?c=EvnDrug&m=loadEvnDrugGrid'
                                }),
                                tbar: new sw.Promed.Toolbar({
                                    buttons: [{
                                        handler: function() {
                                            this.openEvnDrugEditWindow('add');
                                        }.createDelegate(this),
                                        iconCls: 'add16',
                                        text: 'Добавить'
                                    }, {
                                        handler: function() {
                                            this.openEvnDrugEditWindow('edit');
                                        }.createDelegate(this),
                                        iconCls: 'edit16',
                                        text: 'Изменить'
                                    }, {
                                        handler: function() {
                                            this.openEvnDrugEditWindow('view');
                                        }.createDelegate(this),
                                        iconCls: 'view16',
                                        text: 'Просмотр'
                                    }, {
                                        handler: function() {
                                            this.deleteEvent('EvnDrug');
                                        }.createDelegate(this),
                                        iconCls: 'delete16',
                                        text: 'Удалить'
                                    }, {
                                        iconCls: 'print16',
                                        text: 'Печать',
                                        handler: function() {
                                            var grid = this.findById('EPSEF_EvnDrugGrid');
                                            Ext.ux.GridPrinter.print(grid);
                                        }.createDelegate(this)
                                    }]
                                })
                            })]
                        }),
                        new sw.Promed.Panel({
                            border: true,
                            collapsible: true,
                            height: 290,
                            id: 'EPSEF_PrehospWaifPanel',
                            layout: 'border',
                            listeners: {
                                'expand': function(panel) {
                                    //to-do не загружать грид, если он загружен
                                    this.PrehospWaifInspectionRefreshGrid();
                                    panel.doLayout();
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '10. Беспризорный',
                            items: [{
                                bodyStyle: 'padding-top: 0.5em;',
                                border: false,
                                height: 90,
                                layout: 'form',
                                region: 'north',
                                items: [{
                                    id: 'EPSEF_EvnPS_IsWaif',
                                    fieldLabel: 'Беспризорный',
                                    hiddenName: 'EvnPS_IsWaif',
                                    tabIndex: this.tabindex + 56,
                                    width: 100,
                                    xtype: 'swyesnocombo',
                                    listeners:
                                    {
                                        'change': function (combo,newValue,oldValue)
                                        {
                                            var base_form = this.findById('EvnPSEditForm').getForm();
                                            var pw_arrive_combo = base_form.findField('PrehospWaifArrive_id');
                                            var pw_reason_combo = base_form.findField('PrehospWaifReason_id');
                                            var view_frame = this.findById('EPSEF_PrehospWaifInspection');
                                            if(Ext.isEmpty(newValue) || newValue == 1)
                                            {
                                                pw_arrive_combo.disable();
                                                pw_reason_combo.disable();
                                                pw_arrive_combo.setAllowBlank(true);
                                                pw_reason_combo.setAllowBlank(true);
                                                pw_arrive_combo.clearValue();
                                                pw_reason_combo.clearValue();
                                                view_frame.setReadOnly(true);
                                            }
                                            else
                                            {
                                                //Кем доставлен; доступно и обязательное если Беспризорный = Да.
                                                pw_arrive_combo.setDisabled( this.action == 'view' );
                                                pw_arrive_combo.setAllowBlank(false);
                                                // Обратился самостоятельно ставить автоматически и поле не доступно, если Беспризорный = Да и в разделе КВС Госпитализация поле Кем доставлен = Самостоятельно
                                                /*if (base_form.findField('PrehospArrive_id').getValue() == 1)
                                                 {
                                                 pw_arrive_combo.setValue(3);
                                                 pw_arrive_combo.disable();
                                                 }*/
                                                // Причина помещения в ЛПУ: доступно и обязательное если Беспризорный = Да.
                                                pw_reason_combo.setDisabled( this.action == 'view' );
                                                pw_reason_combo.setAllowBlank(false);
                                                view_frame.setReadOnly(false);
                                            }
                                        }.createDelegate(this)
                                    }
                                },{
                                    fieldLabel: 'Кем доставлен',
                                    tabIndex: this.tabindex + 57,
                                    width: 500,
                                    comboSubject: 'PrehospWaifArrive',
                                    hiddenName: 'PrehospWaifArrive_id',
                                    autoLoad: true,
                                    xtype: 'swcommonsprcombo'
                                },{
                                    id: 'EPSEF_PrehospWaifReason_id',
                                    fieldLabel: 'Причина помещения в ЛПУ',
                                    tabIndex: this.tabindex + 58,
                                    hiddenName: 'PrehospWaifReason_id',
                                    width: 500,
                                    comboSubject: 'PrehospWaifReason',
                                    autoLoad: true,
                                    xtype: 'swcommonsprcombo'
                                }]
                            },
                                new sw.Promed.ViewFrame({
                                    id: 'EPSEF_PrehospWaifInspection',
                                    title:'Осмотры',
                                    object: 'PrehospWaifInspection',
                                    editformclassname: 'swPrehospWaifInspectionEditWindow',
                                    dataUrl: '/?c=PrehospWaifInspection&m=loadRecordGrid',
                                    height:200,
                                    autoLoadData: false,
                                    stringfields:
                                        [
                                            {name: 'PrehospWaifInspection_id', type: 'int', hidden: true, key: true},
                                            {name: 'EvnPS_id', type: 'int', hidden: true, isparams: true},
                                            {name: 'LpuSection_id', type: 'int', hidden: true},
                                            {name: 'MedStaffFact_id', type: 'int', hidden: true},
                                            {name: 'Diag_id', type: 'int', hidden: true},
                                            {name: 'PrehospWaifInspection_SetDT',  type: 'string', header: 'Дата/время', width: 100},
                                            {name: 'LpuSection_Name',  type: 'string', header: 'Отделение', width: 250},
                                            {name: 'MedPersonal_Fio',  type: 'string', header: 'Врач', width: 200},
                                            {id: 'autoexpand', name: 'Diag_Name',  type: 'string', header: 'Диагноз'}
                                        ],
                                    actions:
                                        [
                                            {name:'action_add', handler: function() {this.openPrehospWaifInspectionEditWindow('add');}.createDelegate(this)},
                                            {name:'action_edit', handler: function() {this.openPrehospWaifInspectionEditWindow('edit');}.createDelegate(this)},
                                            {name:'action_view', handler: function() {this.openPrehospWaifInspectionEditWindow('view');}.createDelegate(this)},
                                            {name:'action_delete'},
                                            {name:'action_refresh', handler: function() {this.PrehospWaifInspectionRefreshGrid();}.createDelegate(this)},
                                            {name:'action_print'}
                                        ],
                                    paging: false,
                                    root: 'data',
                                    totalProperty: 'totalCount',
                                    focusOn: {name:'EPSEF_PrintBtn',type:'button'},
                                    focusPrev: {name:'EPSEF_PrehospWaifReason_id',type:'field'},
                                    focusOnFirstLoad: false
                                })
                            ]
                        }),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 100,
							id: 'EPSEF_CostPrintPanel',
							layout: 'border',
							listeners: {
								'expand': function(panel) {
									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: '11. Справка о стоимости лечения',
							hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
							items: [{
								bodyStyle: 'padding-top: 0.5em;',
								border: false,
								height: 90,
								layout: 'form',
								region: 'center',
								items: [{
									fieldLabel: 'Дата выдачи справки/отказа',
									tabIndex: this.tabindex + 59,
									width: 100,
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									name: 'EvnCostPrint_setDT',
									xtype: 'swdatefield'
								},{
									fieldLabel: 'Номер справки/отказа',
									name:'EvnCostPrint_Number',
									readOnly: true,
									xtype: 'textfield'
								},{
									fieldLabel: 'Отказ',
									tabIndex: this.tabindex + 60,
									hiddenName: 'EvnCostPrint_IsNoPrint',
									width: 60,
									xtype: 'swyesnocombo'
								}]
							}]
						}),
						//BOB - 04.09.2018
                        new sw.Promed.Panel({
                            border: true,
                            collapsible: true,
                            height: 200,
                            id: 'EPSEF_EvnReanimatPeriodPanel',
                            isLoaded: false,
                            layout: 'border',
                            listeners: {
                                'expand': function(panel) {
                                    if ( panel.isLoaded === false ) {
                                        panel.isLoaded = true;
                                        panel.findById('EPSEF_EvnReanimatPeriodGrid').getStore().load({
                                            params: {
                                                EvnPS_id: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
                                            }
                                        });
                                    }

                                    panel.doLayout();
                                }.createDelegate(this)
                            },
                            style: 'margin-bottom: 0.5em;',
                            title: '12. Реанимационные периоды',
                            items: [ new Ext.grid.GridPanel({
                                autoExpandColumn: 'autoexpand_ReanimatPeriod',
                                autoExpandMin: 100,
                                border: false,
                                columns: [{
                                    dataIndex: 'EvnReanimatPeriod_setDT',
                                    header: 'Начало периода',
                                    hidden: false,
                                    //renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: true,
                                    //sortable: true,
                                    width: 120
                                },{
                                    dataIndex: 'EvnReanimatPeriod_disDT',
                                    header: 'Конец периода',
                                    hidden: false,
                                    //renderer: Ext.util.Format.dateRenderer('d.m.Y'),
                                    resizable: true,
                                    //sortable: true,
                                    width: 120
                                }, {
                                    dataIndex: 'ReanimResultType_Name',
                                    header: 'Исход периода',
                                    hidden: false,
                                    resizable: true,
                                    //sortable: true,
                                    width: 150
                                }, {
                                    dataIndex: 'LpuSection_Name',
                                    header: 'Отделение ЛПУ',
                                    hidden: false,
                                    resizable: true,
                                    //sortable: true,
                                    width: 300
                                }, {
                                    dataIndex: 'MedService_Name',
                                    header: 'Служба реанимации',
                                    hidden: false,
                                    id: 'autoexpand_ReanimatPeriod',
                                    resizable: true
                                    //sortable: true
                                }],
                                frame: false,
                                id: 'EPSEF_EvnReanimatPeriodGrid',
                                keys: [{
                                    key: [
                                        Ext.EventObject.DELETE,
                                        Ext.EventObject.END,
                                        Ext.EventObject.ENTER,
                                        Ext.EventObject.F3,
                                        Ext.EventObject.F4,
                                        Ext.EventObject.HOME,
                                        //Ext.EventObject.INSERT,
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

                                        var grid = this.findById('EPSEF_EvnReanimatPeriodGrid');

                                        switch ( e.getKey() ) {
                                            case Ext.EventObject.DELETE:
                                                this.deleteEvent('EvnReanimatPeriod');
                                                break;

                                            case Ext.EventObject.END:
                                                GridEnd(grid);
                                                break;

                                            case Ext.EventObject.F3:
												if ( !e.altKey ) {
													if ( !grid.getSelectionModel().getSelected() ) {
														return false;
													}
													var action = 'view';
													this.openEvnReanimatPeriodEditWindow(action);
												} else {
													var params = new Object();
													params['key_id'] = grid.getSelectionModel().getSelected().data.EvnReanimatPeriod_id;
													params['key_field'] = 'EvnReanimatPeriod_id';
													getWnd('swAuditWindow').show(params);
												}
                                                break;

                                            case Ext.EventObject.ENTER:
                                            case Ext.EventObject.F4:
                                            case Ext.EventObject.INSERT:
                                                if ( !grid.getSelectionModel().getSelected() ) {
                                                    return false;
                                                }

                                                var action = 'edit';
                                                this.openEvnReanimatPeriodEditWindow(action);
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
                                                var base_form = this.findById('EvnPSEditForm').getForm();

                                                grid.getSelectionModel().clearSelections();
                                                grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

                                                if ( e.shiftKey == false ) {
                                                    if ( this.action != 'view' ) {
                                                        this.buttons[0].focus();
                                                    }
                                                    else {
                                                        this.buttons[1].focus();
                                                    }
                                                }
                                                else {
                                                    if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
                                                        if ( !base_form.findField('Diag_pid').disabled ) {
                                                            base_form.findField('Diag_pid').focus(true);
                                                        }
                                                        else {
                                                            base_form.findField('MedStaffFact_pid').focus(true);
                                                        }
                                                    }
                                                    else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
                                                        this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
                                                    }
                                                    else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
                                                        base_form.findField('EvnPS_IsDiagMismatch').focus(true);
                                                    }
                                                    else {
                                                        this.buttons[this.buttons.length - 1].focus();
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
                                        this.openEvnReanimatPeriodEditWindow('edit');
                                    }.createDelegate(this)
                                },
                                loadMask: true,
                                region: 'center',
                                sm: new Ext.grid.RowSelectionModel({
                                    listeners: {
                                        'rowselect': function(sm, rowIndex, record) {
                                            var id = null;
                                            var selected_record = sm.getSelected();
                                            var toolbar = this.findById('EPSEF_EvnReanimatPeriodGrid').getTopToolbar();

                                            if ( selected_record ) {
                                                id = selected_record.get('EvnReanimatPeriod_id');
                                            }

                                            toolbar.items.items[0].disable();
                                            toolbar.items.items[2].disable();

                                            if ( id ) {
                                                toolbar.items.items[1].enable();

                                                if ( this.action != 'view' ) {
                                                    toolbar.items.items[0].enable();
													toolbar.items.items[2].enable();
                                                }
                                            }
                                            else {
                                                toolbar.items.items[1].disable();
                                            }
                                        }.createDelegate(this)
                                    }
                                }),
                                stripeRows: true,
                                store: new Ext.data.Store({
                                    autoLoad: false,
//                                    baseParams: {
//                                        'parent': 'EvnPS'
//                                    },
                                    listeners: {
                                        'load': function(store, records, index) {
                                            if ( store.getCount() == 0 ) {
                                                LoadEmptyRow(this.findById('EPSEF_EvnReanimatPeriodGrid'));
                                            }

                                        }.createDelegate(this)
                                    },
                                    reader: new Ext.data.JsonReader({
                                        id: 'EvnReanimatPeriod_id'
                                    }, [{
                                        mapping: 'EvnReanimatPeriod_id',
                                        name: 'EvnReanimatPeriod_id',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnReanimatPeriod_pid',
                                        name: 'EvnReanimatPeriod_pid',
                                        type: 'int'
                                    }, {
                                        mapping: 'EvnReanimatPeriod_setDT',
                                        name: 'EvnReanimatPeriod_setDT',
                                        type: 'string'
                                    }, {
                                        mapping: 'EvnReanimatPeriod_disDT',
                                        name: 'EvnReanimatPeriod_disDT',
                                        type: 'string'
                                    }, {
                                        mapping: 'ReanimReasonType_Name',
                                        name: 'ReanimReasonType_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'ReanimResultType_Name',
                                        name: 'ReanimResultType_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'LpuSection_id',
                                        name: 'LpuSection_id',
                                        type: 'int'
									}, {
                                        mapping: 'LpuSection_Name',
                                        name: 'LpuSection_Name',
                                        type: 'string'
                                    }, {
                                        mapping: 'MedService_id',
                                        name: 'MedService_id',
                                        type: 'int'
									}, {
                                        mapping: 'MedService_Name',
                                        name: 'MedService_Name',
                                        type: 'string'
                                    }]),
                                    url: '/?c=EvnReanimatPeriod&m=loudEvnReanimatPeriodGrid_PS'
                                }),
                                tbar: new sw.Promed.Toolbar({
                                    buttons: [{
                                        handler: function() {
                                            this.openEvnReanimatPeriodEditWindow('edit');
                                        }.createDelegate(this),
                                        iconCls: 'edit16',
                                        text: 'Изменить'
                                    }, {
                                        handler: function() {
                                            this.openEvnReanimatPeriodEditWindow('view');
                                        }.createDelegate(this),
                                        iconCls: 'view16',
                                        text: 'Просмотр'
                                    }, {
                                        handler: function() {
                                            this.deleteEvent('EvnReanimatPeriod');
                                        }.createDelegate(this),
                                        iconCls: 'delete16',
                                        text: 'Удалить'
                                    },
									{  //BOB - 12.07.2019
										handler:function () {
											this.findById('EPSEF_EvnReanimatPeriodGrid').getStore().load({
												params: {
													EvnPS_id: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
												}
											});
										}.createDelegate(this),
										iconCls:'refresh16',
										text:'Обновить'
									}]
                                })
                            })]
                        })
						
						
						//BOB - 04.09.2018
						
					],
                    reader: new Ext.data.JsonReader({
                        success: Ext.emptyFn
                    }, [
                        {name: 'accessType'},
						{name: 'childPS'},
						{name: 'EvnSection_IsPaid'},
						{name: 'EvnPS_IndexRep'},
						{name: 'EvnPS_IndexRepInReg'},
						{name: 'EvnPS_OutcomeDate'},
						{name: 'EvnPS_OutcomeTime'},
                        {name: 'EvnPS_IsPLAmbulance'},
                        {name: 'LpuSection_eid'},
                        {name: 'PrehospWaifRefuseCause_id'},
                        {name: 'MedicalCareFormType_id'},
						{name: 'LeaveType_prmid'},
						{name: 'LeaveType_prmSysNick'},
                        {name: 'LeaveType_fedid'},
                        {name: 'ResultDeseaseType_fedid'},
                        {name: 'ResultClass_id'},
                        {name: 'ResultDeseaseType_id'},
                        {name: 'UslugaComplex_id'},
                        {name: 'LpuSectionProfile_id'},
                        {name: 'EvnPS_IsTransfCall'},
                        {name: 'EvnPS_IsWaif'},
						{name: 'LpuSectionWard_id'},
						{name: 'LpuSectionBedProfileLink_id'},
						{name: 'PrehospWaifArrive_id'},
                        {name: 'PrehospWaifReason_id'},
                        {name: 'Diag_did'},
                        {name: 'DiagSetPhase_did'},
                        {name: 'ChildLpuSection_id'},
                        {name: 'EvnPS_PhaseDescr_did'},
                        {name: 'Diag_pid'},
                        {name: 'Diag_eid'},
						{name: 'TraumaCircumEvnPS_Name'},
						{name: 'TraumaCircumEvnPS_setDT'},
						{name: 'TraumaCircumEvnPS_setDTDate'},
						{name: 'TraumaCircumEvnPS_setDTTime'},
                        {name: 'DiagSetPhase_pid'},
						{name: 'DiagSetPhase_aid'},
                        {name: 'EvnPS_PhaseDescr_pid'},
                        {name: 'EvnQueue_id'},
                        {name: 'EvnDirection_id'},
                        {name: 'EvnDirectionHTM_id'},
                        {name: 'DirType_id'},
                        {name: 'EvnDirection_Num'},
                        {name: 'EvnDirection_setDate'},
                        {name: 'EvnPS_CodeConv'},
                        {name: 'EvnPS_HospCount'},
                        {name: 'EvnPS_id'},
                        {name: 'EvnPS_IsPrehospAcceptRefuse'},
                        //{ name: 'EvnPS_IsSigned' },
                        { name: 'EvnPS_IsTransit' },
                        {name: 'EvnPS_IsCont'},
                        {name: 'EvnPS_IsDiagMismatch'},
                        {name: 'EvnPS_IsImperHosp'},
                        {name: 'EvnPS_IsNeglectedCase'},
						{name: 'RepositoryObserv_BreathRate'},
						{name: 'RepositoryObserv_Systolic'},
						{name: 'RepositoryObserv_Diastolic'},
						{name: 'RepositoryObserv_Height'},
						{name: 'RepositoryObserv_Weight'},
						{name: 'RepositoryObserv_TemperatureFrom'},
						{name: 'RepositoryObserv_SpO2'},
						{name: 'CovidType_id'},
						{name: 'DiagConfirmType_id'},
                        {name: 'EvnPS_IsWrongCure'},
                        {name: 'EvnPS_IsUnlaw'},
                        {name: 'EvnPS_IsUnport'},
                        {name: 'EvnPS_NotificationDate'},
                        {name: 'EvnPS_NotificationTime'},
                        {name: 'MedStaffFact_id'},
                        {name: 'EvnPS_Policeman'},
                        {name: 'EvnPS_IsShortVolume'},
                        {name: 'EvnPS_IsWithoutDirection'},
                        {name: 'EvnPS_NumCard'},
                        {name: 'EvnPS_NumConv'},
                        {name: 'EvnPS_PrehospAcceptRefuseDT'},
						{name: 'EvnPS_PrehospWaifRefuseDT'},
                        {name: 'EvnPS_setDate'},
                        {name: 'EvnPS_setTime'},
                        {name: 'EvnPS_TimeDesease'},
                        {name: 'Okei_id'},
                        {name: 'LpuSection_did'},
                        {name: 'LpuSection_pid'},
                        {name: 'MedStaffFact_pid'},
						{name: 'Lpu_id'},
                        {name: 'Org_did'},
                        {name: 'MedStaffFact_did'},
                        {name: 'MedStaffFact_TFOMSCode'},
						{name: 'Lpu_did'},
                        {name: 'PayType_id'},
                        {name: 'Person_id'},
                        {name: 'PersonEvn_id'},
                        {name: 'PrehospArrive_id'},
                        {name: 'PrehospDirect_id'},
                        {name: 'PrehospStatus_id'},
                        {name: 'PrehospToxic_id'},
						{name: 'LpuSectionTransType_id'},
                        {name: 'PrehospTrauma_id'},
                        {name: 'PrehospType_id'},
                        {name: 'EntranceModeType_id'},
                        {name: 'Server_id'},
                        {name: 'EvnCostPrint_setDT'},
                        {name: 'EvnCostPrint_Number'},
                        {name: 'EvnCostPrint_IsNoPrint'},
                        {name: 'CmpCallCard_id'},
						{name: 'childPS'},
						{name: 'EvnPS_HTMBegDate'},
						{name: 'EvnPS_HTMHospDate'},
						{name: 'EvnPS_isMseDirected'},
						{name: 'EvnPS_HTMTicketNum'},
						{name: 'EvnPS_IsActive'},
						{name: 'DeseaseType_id'},
						{name: 'TumorStage_id'},
						{name: 'EvnPS_CmpTltDate'}, //#111791
						{name: 'EvnPS_CmpTltTime'},
						{name: 'EvnPS_IsZNO'},
						{name: 'EvnPS_IsZNORemove'},
						{name: 'Diag_spid'},
						{name: 'EvnPS_BiopsyDate'},
						{ name: 'FamilyContact_msgDate'}, //#180378
						{ name: 'FamilyContact_msgTime'},
						{ name: 'FamilyContact_FIO'},
						{ name: 'FamilyContact_Phone'},
						{ name: 'FaceAsymetry_Name' },
						{ name: 'FaceAsymetry_Name' },
						{ name: 'HandHold_Name' },
						{ name: 'SqueezingBrush_Name' },
						{ name: 'ScaleLams_id' },
						{ name: 'ScaleLams_Value' },
						{ name: 'PainResponse_Name' },
						{ name: 'ExternalRespirationType_Name' },
						{ name: 'SystolicBloodPressure_Name' },
						{ name: 'InternalBleedingSigns_Name' },
						{ name: 'LimbsSeparation_Name' },
						{ name: 'PrehospTraumaScale_Value' },
						{ name: 'CmpCallCard_id' },
						{ name: 'PainDT', type: 'date', dateFormat: 'Y-m-d H:i', convert: Ext.util.Format.dateRenderer('d-m-Y H:i')},
						{ name: 'ECGDT', type: 'date', dateFormat: 'Y-m-d H:i', convert: Ext.util.Format.dateRenderer('d-m-Y H:i')},
						{ name: 'TLTDT', type: 'date', dateFormat: 'Y-m-d H:i', convert: Ext.util.Format.dateRenderer('d-m-Y H:i')},
						{ name: 'ResultECG' },
						{ name: 'FailTLT' },
						{ name: 'Pediculos_id' },
						{ name: 'isPediculos' },
						{ name: 'isScabies' },
						{ name: 'RepositoryObserv_FluorographyDate' },
						{ name: 'PediculosDiag_id' },
						{ name: 'ScabiesDiag_id' },
						{ name: 'Pediculos_isSanitation' },
						{ name: 'Pediculos_Sanitation_setDate' },
						{ name: 'Pediculos_Sanitation_setTime' },
						{ name: 'Pediculos_isPrint'},
						{ name: 'buttonPrint058'}
                    ]),
                    region: 'center',
                    url: '/?c=EvnPS&m=saveEvnPS'
                })]
        });

        sw.Promed.swEvnPSEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById(this.id + '_DiagRecepCombo').addListener('change', function(combo, newValue, oldValue) {
			this.refreshFieldsVisibility(['DeseaseType_id','TumorStage_id']);
			this.setMedicalCareFormType();
			this.setSpecificsPanelVisibility();
			//листенер для диагноза приемного отделения
			if (!getRegionNick().inlist([ 'kz' ])) {
				var tltPanel = this.findById(this.id+'_TltPanel');
				var tltCheckbox = this.findById(this.id + '_isCmpTlt');
				var timeDeseaseField = this.findById('EvnPSEditForm').getForm().findField('EvnPS_TimeDesease');
				if(combo.getGroup().inlist(this.IshemiaCode)){
					tltPanel.setVisible(true);
					timeDeseaseField.setAllowBlank(false);
					if(this.getOKSDiag())
						this.loadUslugaGrid();
				} else {
					tltPanel.setVisible(false);
					timeDeseaseField.setAllowBlank(true);
				}
				tltCheckbox.setValue(false);
				tltCheckbox.setVisibleFormDT(false);
			}

			var diag_code = combo.getFieldValue('Diag_Code');
			if (getRegionNick() != 'krym' && diag_code && diag_code.search(new RegExp("^(C|D0)", "i")) >= 0) {
				this.findById('EPSEF_EvnPS_IsZNOCheckbox').setValue(false);
				this.findById('EPSEF_EvnPS_IsZNOCheckbox').disable();
			} else {
				this.findById('EPSEF_EvnPS_IsZNOCheckbox').enable();

				if (getRegionNick() == 'buryatiya') {
					this.findById('EPSEF_EvnPS_IsZNOCheckbox').setValue(diag_code == 'Z03.1');
				}
			}
		}.createDelegate(this));

		this.findById('EPSEF_EvnSectionGrid').getStore().on('load',function() {
			if (!getRegionNick().inlist([ 'kz' ]) && this.getOKSDiag()) {
				this.loadUslugaGrid();
			}
		}.createDelegate(this));

        this.findById(this.id + '_LpuSectionRecCombo').addListener('change', function(combo, newValue, oldValue) {
            var base_form = this.findById('EvnPSEditForm').getForm();
			
			if ( getRegionNick().inlist([ 'ekb', 'perm' ]) ) {
				this.reloadUslugaComplexField();
			}

			this.filterLpuSectionProfile();
			
            var diag_d_combo = base_form.findField('Diag_did');
            var diag_p_combo = base_form.findField('Diag_pid');

            if ( !newValue ) {
                diag_p_combo.clearValue();
                diag_p_combo.disable();
                return false;
            }
            diag_p_combo.setDisabled( this.action == 'view' );
			diag_p_combo.validate();
            var diag_did = diag_d_combo.getValue();
            var diag_pid = diag_p_combo.getValue();

            if ( !diag_did || diag_pid ) {
                return false;
            }

            diag_p_combo.getStore().load({
                callback: function() {
					diag_p_combo.setValue(diag_did);
                    diag_p_combo.fireEvent('select', diag_p_combo, diag_p_combo.getStore().getAt(0), 0);
					win.refreshFieldsVisibility(['DeseaseType_id','TumorStage_id']);
                },
                params: {
                    where: "where DiagLevel_id = 4 and Diag_id = " + diag_did
                }
            });
        }.createDelegate(this));
		
		this.findById(this.id + 'MedStaffFactDid').addListener('change', function(combo, newValue, oldValue) {
			if ( getRegionNick().inlist([ 'ekb', 'buryatiya' ]) ) {
				var
					base_form = this.findById('EvnPSEditForm').getForm(),
					index = combo.getStore().findBy(function(rec) {
						return (rec.get(combo.valueField) == newValue);
					});

				if ( index >= 0 && !Ext.isEmpty(combo.getStore().getAt(index).get('MedPersonal_DloCode')) ) {
					base_form.findField('MedStaffFact_TFOMSCode').setValue(combo.getStore().getAt(index).get('MedPersonal_DloCode'));
					base_form.findField('MedStaffFact_TFOMSCode').disable();
				}
				else {
					base_form.findField('MedStaffFact_TFOMSCode').setDisabled(this.action == 'view');
				}
			}
		}.createDelegate(this));

		this.findById(this.id + '_MedStaffFactRecCombo').addListener('change', function(combo, newValue, oldValue) {
			if ( getRegionNick().inlist([ 'ekb', 'perm' ]) ) {
				var base_form = this.findById('EvnPSEditForm').getForm();

				this.reloadUslugaComplexField();

				if ( getRegionNick() == 'ekb' ) {
					this.filterLpuSectionProfile();
				}
			}
			else if ( getRegionNick().inlist([ 'krym', 'buryatiya' ])) {
				this.filterLpuSectionProfile();
			}
		}.createDelegate(this));

        this.findById(this.id + '_MedStaffFactRecCombo').addListener('keydown', function(inp, e) {
            var base_form = this.findById('EvnPSEditForm').getForm();

            if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false && base_form.findField('Diag_pid').disabled ) {
                e.stopEvent();

                if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                    this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                    this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                }
                else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                    this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                    this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                }
                else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                    this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                    this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                }
                else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
                    this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                    this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                }
                else if ( this.action != 'view' ) {
                    this.buttons[0].focus();
                }
                else {
                    this.buttons[1].focus();
                }
            }
        }.createDelegate(this));

        this.findById(this.id + '_DiagRecepCombo').addListener('keydown', function(inp, e) {
            var base_form = this.findById('EvnPSEditForm').getForm();

            if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
                e.stopEvent();

                if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
                    this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
                    this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
                }
                else if ( !this.findById('EPSEF_EvnSectionPanel').collapsed && this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
                    this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
                    this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
                }
                else if ( !this.findById('EPSEF_EvnStickPanel').collapsed && this.findById('EPSEF_EvnStickGrid').getStore().getCount() > 0 ) {
                    this.findById('EPSEF_EvnStickGrid').getView().focusRow(0);
                    this.findById('EPSEF_EvnStickGrid').getSelectionModel().selectFirstRow();
                }
                else if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed && this.findById('EPSEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
                    this.findById('EPSEF_EvnUslugaGrid').getView().focusRow(0);
                    this.findById('EPSEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
                }
                else if ( this.action != 'view' ) {
                    this.buttons[0].focus();
                }
                else {
                    this.buttons[1].focus();
                }
            }
        }.createDelegate(this));

		this.findById(this.id + '_DiagRecepCombo').addListener('change', function(inp, e) {
			this.setMedicalCareFormType();
            this.setDiagEidAllowBlank(true);
        }.createDelegate(this));

        var base_form = this.findById('EvnPSEditForm').getForm();

		this.setDirection = function(data) {

			me.dataDirection = data;

			var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
			var PrehospDirect_id = (data.PrehospDirect_id || (data.Lpu_id != getGlobalOptions().lpu_id || data.DirType_id == 27 || data.DirType_id == 28 ? 2 : 1));

			base_form.findField('EvnDirection_Num').setValue('');
			base_form.findField('EvnDirection_setDate').setValue('');
			base_form.findField('LpuSection_did').clearValue();
			base_form.findField('Org_did').clearValue();

			if(getRegionNick()=='ekb' && !data.Org_did && data.Lpu_sid) {
				Ext.Ajax.request({
					params: { Lpu_sid: data.Lpu_sid },
					url: '/?c=Org&m=getOrgList',
					callback: function(options, success, response) {
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if(response_obj[0]) {
								base_form.findField('Org_did').getStore().load({
									callback: function(records, options, success) {
										if ( success ) {
											base_form.findField('Org_did').setValue(response_obj[0].Org_id);
											base_form.findField('Org_did').fireEvent('change', base_form.findField('Org_did'), base_form.findField('Org_did').getValue());
										}
									}.createDelegate(this),
									params: {
										Org_id: response_obj[0].Org_id,
										OrgType: 'lpu'
									}
								});
							}
						}
					}
				});
			} else base_form.findField('Org_did').fireEvent('change', base_form.findField('Org_did'), base_form.findField('Org_did').getValue());

			base_form.findField('PrehospDirect_id').setValue(PrehospDirect_id);
			iswd_combo.setValue(2);
			iswd_combo.fireEvent('change', iswd_combo, 2);

			if (!Ext.isEmpty(data.EvnDirection_id)) {
				base_form.findField('EvnDirection_id').setValue(data.EvnDirection_id);
				base_form.findField('EvnDirectionExt_id').setValue(null);
			} else {
				base_form.findField('EvnDirection_id').setValue(0);
				base_form.findField('EvnDirectionExt_id').setValue(data.EvnDirectionExt_id);
			}

			if (!Ext.isEmpty(data.EvnDirectionHTM_id)) {
				base_form.findField('EvnDirectionHTM_id').setValue(data.EvnDirectionHTM_id);

				if (data.DirType_id == 19 &&  // ВМП
					!getRegionNick().inlist(['ufa','kz'])) 
						me._loadHtmData(data.EvnDirectionHTM_id);
			} else {
				base_form.findField('EvnDirectionHTM_id').setValue(null);
				me._fillHtmData();
			}

			if (!Ext.isEmpty(data.DirType_id)) {
				base_form.findField('DirType_id').setValue(data.DirType_id);
			} else {
				base_form.findField('DirType_id').setValue(null);
			}

			this.checkVMPFieldEnabled();
			if (getRegionNick().inlist(['penza','perm'])) {
				// для пензы зависит от DirType_id
				this.filterMedicalCareFormType();
			}

			if ( !Ext.isEmpty(data.EvnDirection_id) || !Ext.isEmpty(data.EvnDirectionExt_id) ) {
				base_form.findField('EvnDirection_Num').setDisabled(true);
				base_form.findField('EvnDirection_setDate').setDisabled(true);
				base_form.findField('LpuSection_did').setDisabled(true);
				base_form.findField('Org_did').setDisabled(true);
			}
			else {
				base_form.findField('EvnDirection_Num').setDisabled(this.action == 'view');
				base_form.findField('EvnDirection_setDate').setDisabled(this.action == 'view');
				base_form.findField('LpuSection_did').setDisabled(this.action == 'view');
				base_form.findField('Org_did').setDisabled(this.action == 'view');
			}

			switch ( parseInt(PrehospDirect_id) ) {
				case 1:
					if ( !Ext.isEmpty(data.LpuSection_id) ) {
						//устанавливаем отделение и дизаблим поле
						base_form.findField('LpuSection_did').setValue(data.LpuSection_id);
					}
				break;

				case 2:
					base_form.findField('Org_did').getStore().load({
						callback: function(records, options, success) {
							if ( success ) {
								base_form.findField('Org_did').setValue(data.Org_did);
								base_form.findField('Org_did').fireEvent('change', base_form.findField('Org_did'), base_form.findField('Org_did').getValue());
							}
						}.createDelegate(this),
						params: {
							Org_id: data.Org_did,
							OrgType: 'lpu'
						}
					});
				break;
			}

			if ( !Ext.isEmpty(data.EvnDirection_Num) ) {
				base_form.findField('EvnDirection_Num').setValue(data.EvnDirection_Num);
			}

			if ( !Ext.isEmpty(data.EvnDirection_setDate) ) {
				base_form.findField('EvnDirection_setDate').setValue(data.EvnDirection_setDate);
			}

			if ( !Ext.isEmpty(data.Diag_did) ) {
				base_form.findField('Diag_did').getStore().load({
					callback: function() {
						base_form.findField('Diag_did').getStore().each(function(record) {
							if ( record.get('Diag_id') == data.Diag_did ) {
								base_form.findField('Diag_did').setValue(data.Diag_did);
								base_form.findField('Diag_did').fireEvent('select', base_form.findField('Diag_did'), record, 0);
							}
						});
					},
					params: {where: "where DiagLevel_id = 4 and Diag_id = " + data.Diag_did}
				});
			}

			var PrehospType_SysNick = null;
			switch(Number(data.DirType_id)) {
				case 1: PrehospType_SysNick = 'plan';break;
				case 5: PrehospType_SysNick = 'extreme';break;
			}
			if (PrehospType_SysNick) {
				base_form.findField('PrehospType_id').setFieldValue('PrehospType_SysNick', PrehospType_SysNick);
				base_form.findField('PrehospType_id', base_form.findField('PrehospType_id'), base_form.findField('PrehospType_id').getValue());
			}
		}.createDelegate(this);
		
		this.findById('EPSEF_EvnUslugaGrid').getStore().addListener('load', function(store,records,ooptions) {
			if (!getRegionNick().inlist([ 'kz' ])) {
				this.loadECGResult();
			}
		}.createDelegate(this));

		if(!getRegionNick().inlist([ 'kz' ])) {
			this.findById(this.id + 'EvnPS_setDate').addListener('change', function(datefield,newValue,oldValue){
				win.findById(win.id + '_CmpTltTime').validate();
			});
			this.findById(this.id + 'EvnPS_setTime').addListener('change', function(datefield,newValue,oldValue){
				win.findById(win.id + '_CmpTltTime').validate();
			});
		}
		if (getRegionNick() == 'penza') {
			this.findById('EPSEF_EvnUslugaPanel').setHeight(605);
			this.findById('EPSEF_EvnSectionPanel').setHeight(350);
		}
    },
    isCopy: false,
    layout: 'border',
    listeners: {
        'hide': function(win) {
			var base_form = this.findById('EvnPSEditForm').getForm(); //BOB - 29.05.2018
			var evn_ps_id = base_form.findField('EvnPS_id').getValue(); //BOB - 29.05.2018
            win.onHide(evn_ps_id);
        },
        'maximize': function(win) {
            win.findById('EPSEF_HospitalisationPanel').doLayout();
            win.findById('EPSEF_DirectDiagPanel').doLayout();
            win.findById('EPSEF_AdmitDepartPanel').doLayout();
            win.findById('EPSEF_AdmitDiagPanel').doLayout();

            if ( !win.findById('EPSEF_EvnSectionPanel').hidden ) {
                win.findById('EPSEF_EvnSectionPanel').doLayout();
            }

            if ( !win.findById('EPSEF_EvnStickPanel').hidden ) {
                win.findById('EPSEF_EvnStickPanel').doLayout();
            }

            if ( !win.findById('EPSEF_EvnUslugaPanel').hidden ) {
                win.findById('EPSEF_EvnUslugaPanel').doLayout();
            }

			//BOB - 04.09.2018
            if ( !win.findById('EPSEF_EvnReanimatPeriodPanel').hidden ) {
                win.findById('EPSEF_EvnReanimatPeriodPanel').doLayout();
            }

        },
        'restore': function(win) {
            win.fireEvent('maximize', win);
        }
    },
    maximizable: true,
    minHeight: 550,
    minWidth: 800,
    modal: true,
    onCancelAction: function() {
        var base_form = this.findById('EvnPSEditForm').getForm();
        var evn_ps_id = base_form.findField('EvnPS_id').getValue();
        if ( evn_ps_id > 0 && (this.action == 'add' || this.isCopy || this.deleteOnCancel) ) {
            // удалить КВС
            // закрыть окно после успешного удаления
            var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление КВС..."});
            loadMask.show();

            Ext.Ajax.request({
                callback: function(options, success, response) {
                    loadMask.hide();

                    if ( success ) {
                        this.hide();
                    }
                    else {
                        sw.swMsg.alert('Ошибка', 'При удалении КВС возникли ошибки');
                        return false;
                    }
                }.createDelegate(this),
				params: {
					Evn_id: evn_ps_id
				},
				url: '/?c=Evn&m=deleteEvn'
            });
        }
        else if ( this.action == 'edit' ) {
            this.hide();
        }
        else {
            this.hide();
        }
    },
    onHide: Ext.emptyFn,
    openEvnDiagPSEditWindow: function(action, type) {
        if ( action != 'add' && action != 'edit' && action != 'view' ) {
            return false;
        }

        var base_form = this.findById('EvnPSEditForm').getForm();
        var grid = null;

        if ( this.action == 'view' ) {
            if ( action == 'add' ) {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }

        if ( getWnd('swEvnDiagPSEditWindow').isVisible() ) {
            sw.swMsg.alert('Сообщение', 'Окно редактирования диагноза уже открыто');
            return false;
        }

        if ( action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
            this.doSave({
                callback: function() {
                    this.openEvnDiagPSEditWindow(action, type);
                }.createDelegate(this),
                print: false
            });
            return false;
        }

        switch ( type ) {
            case 'hosp':
                if ( this.findById('EPSEF_HospitalisationPanel').hidden ) {
                    return false;
                }

                if ( !base_form.findField('Diag_did').getValue() ) {
                    sw.swMsg.alert('Ошибка', 'Не заполнен основной диагноз направившего учреждения', function() {base_form.findField('Diag_did').focus(true);});
                    return false;
                }

                grid = this.findById('EPSEF_EvnDiagPSHospGrid');
                break;

            case 'recep':
                if ( this.findById('EPSEF_AdmitDepartPanel').hidden ) {
                    return false;
                }

                if ( !base_form.findField('Diag_pid').getValue() ) {
                    sw.swMsg.alert('Ошибка', 'Не заполнен основной диагноз в приемном отделении', function() {base_form.findField('Diag_pid').focus(true);});
                    return false;
                }

                grid = this.findById('EPSEF_EvnDiagPSRecepGrid');
                break;

            default:
                return false;
                break;
        }

        var params = new Object();

        if ( action == 'add' ) {
            params.DiagSetClass_id = 3;
            params.EvnDiagPS_id = 0;
            params.EvnDiagPS_setDate = base_form.findField('EvnPS_setDate').getValue();
            params.EvnDiagPS_setTime = base_form.findField('EvnPS_setTime').getValue();
            params.Person_id = base_form.findField('Person_id').getValue();
            params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
            params.Server_id = base_form.findField('Server_id').getValue();

            switch ( type ) {
                case 'hosp':
                case 'recep':
                    params.EvnDiagPS_pid = base_form.findField('EvnPS_id').getValue();
                    break;
            }
        }
        else {
            var selected_record = grid.getSelectionModel().getSelected();

            if ( !selected_record || !selected_record.get('EvnDiagPS_id') ) {
                return false;
            }

            params = selected_record.data;
        }

		params.archiveRecord = this.archiveRecord;

        getWnd('swEvnDiagPSEditWindow').show({
            action: action,
            callback: function(data) {
                if ( !data || !data.evnDiagPSData ) {
                    return false;
                }

                var record = grid.getStore().getById(data.evnDiagPSData[0].EvnDiagPS_id);

                if ( !record ) {
                    if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDiagPS_id') ) {
                        grid.getStore().removeAll();
                    }

                    grid.getStore().loadData(data.evnDiagPSData, true);
                }
                else {
                    var evn_diag_ps_fields = new Array();
                    var i = 0;

                    grid.getStore().fields.eachKey(function(key, item) {
                        evn_diag_ps_fields.push(key);
                    });

                    for ( i = 0; i < evn_diag_ps_fields.length; i++ ) {
                        record.set(evn_diag_ps_fields[i], data.evnDiagPSData[0][evn_diag_ps_fields[i]]);
                    }

                    record.commit();
                }

                switch ( type ) {
                    case 'hosp':
                        if ( !this.findById('EPSEF_AdmitDepartPanel').hidden ) {
                            this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().load({
                                params: {
                                    'class': 'EvnDiagPSRecep',
                                    'EvnDiagPS_pid': base_form.findField('EvnPS_id').getValue()
                                }
                            });
                        }
                        break;
                }
				
				if (getRegionNick() == 'msk') {
					if (this.checkPneumoDiag()) {
						pneumoAlert();
					}
				}
            }.createDelegate(this),
            formParams: params,
            onHide: function() {
                grid.getView().focusRow(0);
                grid.getSelectionModel().selectFirstRow();
            }.createDelegate(this),
            Person_Birthday: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
            Person_Firname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname'),
            Person_id: base_form.findField('Person_id').getValue(),
            Person_Secname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname'),
            Person_Surname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname'),
            type: type
        });
    },
    openEvnDirectionSelectWindow: function() {
        if ( this.action == 'view') {
            return false;
        }

        if ( getWnd('swEvnDirectionSelectWindow').isVisible() ) {
            sw.swMsg.alert('Сообщение', 'Окно выбора направления уже открыто');
            return false;
        }

        var base_form = this.findById('EvnPSEditForm').getForm();

        if ( !base_form.findField('EvnPS_setDate').getValue() ) {
            sw.swMsg.alert('Ошибка', 'Не указана дата госпитализации', function() {base_form.findField('EvnPS_setDate').focus();});
            return false;
        }

        getWnd('swEvnDirectionSelectWindow').show({
			useCase: (getGlobalOptions().CurLpuSection_id && this.ARMType) ? 'choose_for_evnps' : 'choose_for_evnps_stream_input',
			LpuSection_id: getGlobalOptions().CurLpuSection_id,
            callback: this.setDirection,
            onDate: base_form.findField('EvnPS_setDate').getValue(),
            onHide: function() {
                base_form.findField('PrehospArrive_id').focus(true);
            }.createDelegate(this),
            parentClass: 'EvnPS',
            Person_Birthday: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
            Person_Firname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname'),
            Person_id: base_form.findField('Person_id').getValue(),
            Person_Secname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname'),
            Person_Surname: this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname')
        });
    },
    openEvnDrugEditWindow: function(action) {
        if ( this.findById('EPSEF_EvnDrugPanel').hidden || this.findById('EPSEF_EvnDrugPanel').collapsed ) {
            return false;
        }

        if ( action != 'add' && action != 'edit' && action != 'view' ) {
            return false;
        }

        var wnd = this;
        var base_form = this.findById('EvnPSEditForm').getForm();
        var grid = this.findById('EPSEF_EvnDrugGrid');

        if ( this.action == 'view') {
            if ( action == 'add') {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }

        if ( getWnd(getEvnDrugEditWindowName()).isVisible() ) {
            sw.swMsg.alert('Сообщение', 'Окно добавления случая использования медикаментов уже открыто');
            return false;
        }

        if ( action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
            this.doSave({
                callback: function() {
                    this.openEvnDrugEditWindow(action);
                }.createDelegate(this),
                print: false
            });
            return false;
        }

        var parent_evn_combo_data = new Array();

        this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec) {
			if (rec.get('EvnSection_id') > 0) {
				parent_evn_combo_data.push({
					Evn_id: rec.get('EvnSection_id'),
					Evn_Name: Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y') + ' / ' + rec.get('LpuSection_Name') + ' / ' + rec.get('MedPersonal_Fio'),
					Evn_setDate: rec.get('EvnSection_setDate'),
					Evn_disDate: rec.get('EvnSection_disDate'),// TODO: Дата выписки пациентов, отправляем в swEvnDrugEditWindow.js
					MedStaffFact_id: rec.get('MedStaffFact_id'),
					Lpu_id: rec.get('Lpu_id'),
					LpuSection_id: rec.get('LpuSection_id'),
					MedPersonal_id: rec.get('MedPersonal_id')
				})
			}
        });

        var formParams = new Object();
        var params = new Object();
        var person_id = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_id');
        var person_birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
        var person_firname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname');
        var person_secname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname');
        var person_surname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname');

        params.action = action;
        params.parentEvnComboData = parent_evn_combo_data;
        params.callback = function(data) {
            if ( !data || !data.evnDrugData ) {
                return false;
            }
            var grid = this.findById('EPSEF_EvnDrugGrid');
            var record = grid.getStore().getById(data.evnDrugData.EvnDrug_id);

            if ( !record ) {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDrug_id') ) {
                    grid.getStore().removeAll();
                }
                grid.getStore().loadData([data.evnDrugData], true);
            }
            else {
                //
                var grid_fields = new Array();
                var i = 0;

                grid.getStore().fields.eachKey(function(key, item) {
                    grid_fields.push(key);
                });

                for ( i = 0; i < grid_fields.length; i++ ) {
                    record.set(grid_fields[i], data.evnDrugData[grid_fields[i]]);
                }

                record.commit();
            }
        }.createDelegate(this);
        params.onHide = function() {
            grid.getView().focusRow(0);
            grid.getSelectionModel().selectFirstRow();
        }.createDelegate(this);
        params.Person_id = person_id;
        params.Person_Birthday = person_birthday;
        params.Person_Firname = person_firname;
        params.Person_Secname = person_secname;
        params.Person_Surname = person_surname;

        formParams.Person_id = base_form.findField('Person_id').getValue();
        formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
        formParams.Server_id = base_form.findField('Server_id').getValue();

        if ( action == 'add' ) {
            formParams.EvnDrug_id = 0;
            //formParams.EvnDrug_pid = base_form.findField('EvnPS_id').getValue();
        }
        else {
            var selected_record = grid.getSelectionModel().getSelected();

            if ( !selected_record || !selected_record.get('EvnDrug_id') ) {
                return false;
            }

            formParams.EvnDrug_id = selected_record.get('EvnDrug_id');
			formParams.EvnDrug_rid = base_form.findField('EvnPS_id').getValue();
        }

        params.formParams = formParams;
		params.archiveRecord = this.archiveRecord;
		
		if(getRegionNick().inlist([ 'kareliya', 'perm','ekb' ])){
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
				},
				params: {
					EvnPS_id: base_form.findField('EvnPS_id').getValue()
				},
				success: function(response, options) {
					if (!Ext.isEmpty(response.responseText)) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if(response_obj.length<=0&&params.parentEvnComboData.length<=0){
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function() {
									wnd.formStatus = 'edit';
									base_form.findField('LpuSection_pid').focus(false);
								},
								icon: Ext.Msg.WARNING,
								msg: 'Не введено ни одного движения. Поля "Приемное отделение", "Врач приемного отделения" и "Диагноз приемного отделения" должны быть заполнены.',
								title: ERR_INVFIELDS_TIT
							});
							return false;
						}
						
						params.parentEvnComboData = parent_evn_combo_data;

						if ( response_obj.length > 0 ) {
							params.parentEvnComboData.unshift({
								Evn_id: response_obj[0].EvnSection_id,
								Evn_Name: response_obj[0].EvnSection_setDate + ' / ' + response_obj[0].LpuSection_Name + ' / ' + response_obj[0].MedPersonal_Fio,
								Evn_setDate: Date.parseDate(response_obj[0].EvnSection_setDate, 'd.m.Y'),
								Evn_setTime: response_obj[0].EvnSection_setTime,
								Evn_disDate: Date.parseDate(response_obj[0].EvnSection_disDate, 'd.m.Y'),
								Evn_disTime: response_obj[0].EvnSection_disTime,
								MedStaffFact_id: response_obj[0].MedStaffFact_id,
								LpuSection_id: response_obj[0].LpuSection_id,
								MedPersonal_id: response_obj[0].MedPersonal_id,
								Diag_id: response_obj[0].Diag_id
							});
						}

						getWnd(getEvnDrugEditWindowName()).show(params);
					}
				  
				}.createDelegate(this),
				url: '/?c=EvnSection&m=getSectionPriemData'
			});
		} else {
			params.parentEvnComboData.unshift({
				Evn_id: base_form.findField('EvnPS_id').getValue(),
				Evn_Name: 'Приемное отделение'
			});
			getWnd(getEvnDrugEditWindowName()).show(params);
		}
    },
    openEvnSectionEditWindow: function(action) {
        if ( this.findById('EPSEF_EvnSectionPanel').hidden ) {
            return false;
        }

        if ( action != 'add' && action != 'edit' && action != 'view' ) {
            return false;
        }

        var _this = this;
        var base_form = this.findById('EvnPSEditForm').getForm();
        var grid = this.findById('EPSEF_EvnSectionGrid');
        var last_evn_section_info = this.getEvnSectionInfo('last');
        var record;

        // Проверяем возможность добавлять новое движение, если в списке уже есть движения по отделениям
        if ( action == 'add' && last_evn_section_info.EvnSection_id ) {
            if ( !last_evn_section_info.LeaveType_Code ) {
                sw.swMsg.alert('Ошибка', 'Добавление движения невозможно, т.к. пациент не выписан из предыдущего отделения');
                return false;
            }
            else if (getRegionNick().inlist([ 'krasnoyarsk', 'krym', 'perm', 'kareliya' ]) && last_evn_section_info.CureResult_Code && last_evn_section_info.CureResult_Code == 1 ) {
				sw.swMsg.alert('Ошибка', 'Добавление движения невозможно, т.к. лечение завершено.');
				return false;
			}
			else if (!getRegionNick().inlist(['krasnoyarsk', 'krym', 'perm', 'pskov', 'kareliya']) && !last_evn_section_info.LeaveType_Code.toString().inlist([ '5', '104', '204' ]) ) {
                sw.swMsg.alert('Ошибка', 'Добавление движения невозможно, т.к. исход госпитализации в предыдущем отделении означает завершение случая лечения');
                return false;
            }
        }

		var evn_ps_set_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnPS_setTime').getValue());
		var evn_ps_outcome_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnPS_OutcomeDate').getValue(), 'd.m.Y'), base_form.findField('EvnPS_OutcomeTime').getValue() ? base_form.findField('EvnPS_OutcomeTime').getValue() : '');

        if ( evn_ps_set_dt == null ) {
            sw.swMsg.alert('Ошибка', 'Неверное значение даты/времени госпитализации');
            return false;
        }

        if ( this.action == 'view') {
            if ( action == 'add' && this.gridAccess == 'view' ) {
                return false;
            }
            else if ( action == 'edit' && this.gridAccess == 'view' ) {
                action = 'view';
            }
        }
        /*
         if ( getWnd('swEvnSectionEditWindow').isVisible() ) {
         sw.swMsg.alert('Сообщение', 'Окно редактирования случая движения пациента уже открыто');
         return false;
         }
         */
		var isChange = (
			this.isChange('PrehospType_id') || this.isChange('PayType_id')
			|| this.isChange('EvnDirection_Num') || this.isChange('EvnDirection_setDate')
			|| this.isChange('PrehospDirect_id')
		);

        if ( (action == 'add' && Number(base_form.findField('EvnPS_id').getValue()) == 0) || isChange ) {
            this.doSave({
                callback: function() {
					this.findById('EPSEF_EvnSectionPanel').isLoaded = true;

					if ( Number(base_form.findField('EvnPS_id').getValue()) > 0 ) {
						grid.getStore().load({
							callback: function() {
								if ( action == 'add' && grid.getStore().getCount() == 1 && !Ext.isEmpty(grid.getStore().getAt(0).get('EvnSection_id')) ) {
									action = 'edit';
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}

								this.openEvnSectionEditWindow(action);
							}.createDelegate(this),
							params: {
								EvnSection_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
							}
						});
					}
					else {
						this.openEvnSectionEditWindow(action);
					}
                }.createDelegate(this),
                print: false
            });
            return false;
        }

        var formParams = new Object();
        var params = new Object();

        var person_id = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_id');
        var person_birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
        var person_firname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname');
        var person_secname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname');
        var person_surname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname');

        var lpu_section_eid = base_form.findField('LpuSection_eid').getValue();
        var lpu_section_pid = base_form.findField('LpuSection_pid').getValue();

        params.action = action;
        params.callback = function(data) {
            if ( !data || !data.evnSectionData ) {
                return false;
            }

            record = grid.getStore().getById(data.evnSectionData[0].EvnSection_id);
			if (data.evnSectionData[0].deleted) {
				if (record) {
					grid.getStore().reload();
				}
				return;
			}
            var next_evn_section_info = this.getEvnSectionInfo('next', {
                EvnSection_id: data.evnSectionData[0].EvnSection_id,
                EvnSection_setDT: getValidDT(Ext.util.Format.date(data.evnSectionData[0].EvnSection_setDate, 'd.m.Y'), data.evnSectionData[0].EvnSection_setTime ? data.evnSectionData[0].EvnSection_setTime : '')
            });
            if ( !record ) {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnSection_id') ) {
                    grid.getStore().removeAll();
                }

				data.evnSectionData[0].EvnSection_IsSigned = 1;
				data.evnSectionData[0].EvnSection_IsPaid = 1;

                grid.getStore().loadData(data.evnSectionData, true);
            }
            else {
                var evn_section_fields = new Array();
                var i = 0;

				data.evnSectionData[0].EvnSection_IsSigned = record.get('EvnSection_IsSigned');
				data.evnSectionData[0].EvnSection_IsPaid = record.get('EvnSection_IsPaid');

                grid.getStore().fields.eachKey(function(key, item) {
                    evn_section_fields.push(key);
                });

                for ( i = 0; i < evn_section_fields.length; i++ ) {
                    record.set(evn_section_fields[i], data.evnSectionData[0][evn_section_fields[i]]);
                }

                record.commit();
            }

            var LastEvnSection =  _this.getEvnSectionInfo('last');
            grid.getStore().each(function(rec) {
                if ( rec.get('EvnSection_id') == LastEvnSection['EvnSection_id'] ) {
                    rec.set('isLast', 1);
                } else {
                    rec.set('isLast', 0);
                }
                rec.commit();
            });

            if ( next_evn_section_info.EvnSection_id > 0 ) {
                grid.getStore().each(function(rec) {
                    if ( rec.get('EvnSection_id') == next_evn_section_info.EvnSection_id ) {
                        rec.set('EvnSection_setDate', data.evnSectionData[0].EvnSection_disDate);
                        rec.set('EvnSection_setTime', data.evnSectionData[0].EvnSection_disTime);
                        rec.commit();
                    }
                });
            }

            if (data.evnSectionData[0].EvnSection_Index <= 1 && !Ext.isEmpty(data.evnSectionData[0].LpuSection_id)) {
                var LpuSection = _this.findById('EvnPSEditForm').getForm().findField('LpuSection_eid').getValue(),
					LpuSectionEidFieldStore = _this.findById('EvnPSEditForm').getForm().findField('LpuSection_eid').getStore();

				LpuSectionEidFieldStore.loadData(getStoreRecords(swLpuSectionGlobalStore));
				LpuSectionEidFieldStore.filterBy(function(rec){
					return rec.get('LpuSection_id').inlist([LpuSection, data.evnSectionData[0].LpuSection_id]);
				});
            }
            var LpuSection_pid = base_form.findField('LpuSection_pid').getValue();
            if(!Ext.isEmpty(LpuSection_pid) && !Ext.isEmpty(data.evnSectionData[0].LpuSection_eid)){
            	base_form.findField('LpuSection_eid').setValue(data.evnSectionData[0].LpuSection_eid);
            }
            if(!Ext.isEmpty(LpuSection_pid) && !Ext.isEmpty(data.evnSectionData[0].EvnPS_OutcomeDate)){
            	base_form.findField('EvnPS_OutcomeDate').setValue(data.evnSectionData[0].EvnPS_OutcomeDate);
            	base_form.findField('EvnPS_OutcomeDate').fireEvent('change', base_form.findField('EvnPS_OutcomeDate'), base_form.findField('EvnPS_OutcomeDate'), 0);
            	_this.findById('EPSEF_AdmitDepartPanel').expand();
            	_this.findById('EPSEF_PriemLeavePanel').expand();
            }
            if(!Ext.isEmpty(LpuSection_pid) && !Ext.isEmpty(data.evnSectionData[0].EvnPS_OutcomeTime)){
            	base_form.findField('EvnPS_OutcomeTime').setValue(data.evnSectionData[0].EvnPS_OutcomeTime);
            }

            this.BirthWeight = data.evnSectionData[0].birthWeight;
            this.PersonWeight_text = data.evnSectionData[0].PersonWeight_text;
            this.Okei_id = data.evnSectionData[0].Okei_id;
            this.BirthHeight = data.evnSectionData[0].birthHeight;
            this.countChild = data.evnSectionData[0].countChild;
            this.EvnSection_KSGKPG = data.evnSectionData[0].EvnSection_KSGKPG;
            this.checkEvnDirectionAllowBlank();
			this.setPrehospArriveAllowBlank();
			this.setDiagEidAllowBlank();
			this.filterMedicalCareFormType();
			this.getEvnSectionIndexNums();
			//this.setEvnPSOutcomeDT();

			if (!getRegionNick().inlist([ 'kz' ]) && _this.getOKSDiag()) {
				_this.saveInBskRegistry();
			}
        }.createDelegate(this);
        // params.EvnLeave_setDT = evn_leave_set_dt;
        params.EvnPS_setDT = evn_ps_set_dt;
        params.onHide = function(options) {
			if ( this.findById('EPSEF_EvnUslugaPanel').isLoaded === true && options.EvnUslugaGridIsModified === true ) {
				this.findById('EPSEF_EvnUslugaGrid').getStore().load({
					params: {
						pid: base_form.findField('EvnPS_id').getValue()
					}
				});
			}

            grid.getView().focusRow(0);
            grid.getSelectionModel().selectFirstRow();
        }.createDelegate(this);
        params.onChangeLpuSectionWard = this.onChangeLpuSectionWard;
        params.Person_id = person_id;
        params.Person_Birthday = person_birthday;
        params.Person_Firname = person_firname;
        params.Person_Secname = person_secname;
        params.Person_Surname = person_surname;
        params.DiagPred_id = null;
		params.CovidType_id = this.getCovidTypeId();
        params.RepositoryObserv_Height = base_form.findField('RepositoryObserv_Height').getValue();
        params.RepositoryObserv_Weight = base_form.findField('RepositoryObserv_Weight').getValue();
        params.EvnUsluga_rid = base_form.findField('EvnPS_id').getValue();

        if ( action == 'add' ) {
            params.evnSectionIsFirst = false;
            params.evnSectionIsLast = true;

            if ( base_form.findField('Diag_pid').getValue() ) {
                formParams.Diag_id = base_form.findField('Diag_pid').getValue();
            }

            if ( base_form.findField('DiagSetPhase_pid').getValue() ) {
                formParams.DiagSetPhase_id = base_form.findField('DiagSetPhase_pid').getValue();
            }

            if ( grid.getStore().getCount() == 0 || (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnSection_id')) ) {
                // formParams.EvnSection_disDate = this.params.EvnLeave_setDate;
                params.evnSectionIsFirst = true;
            }

            formParams.EvnSection_id = 0;
            formParams.EvnSection_pid = base_form.findField('EvnPS_id').getValue();
            formParams.PayType_id = base_form.findField('PayType_id').getValue();
            formParams.Person_id = base_form.findField('Person_id').getValue();
            formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
            formParams.Server_id = base_form.findField('Server_id').getValue();
            formParams.LpuSection_eid = lpu_section_eid;
            formParams.LpuSection_pid = lpu_section_pid;

            if ( params.evnSectionIsFirst == false ) {
                formParams.EvnSection_setDate = (typeof last_evn_section_info.EvnSection_disDT == 'object' ? Ext.util.Format.date(last_evn_section_info.EvnSection_disDT, 'd.m.Y') : Ext.util.Format.date(last_evn_section_info.EvnSection_setDT, 'd.m.Y'));
                formParams.EvnSection_setTime = (typeof last_evn_section_info.EvnSection_disDT == 'object' ? Ext.util.Format.date(last_evn_section_info.EvnSection_disDT, 'H:i') : Ext.util.Format.date(last_evn_section_info.EvnSection_setDT, 'H:i'));
                params.DiagPred_id = last_evn_section_info.Diag_id;
            }
            else {
                formParams.EvnSection_setDate = Ext.util.Format.date((!Ext.isEmpty(evn_ps_outcome_dt) ? evn_ps_outcome_dt : evn_ps_set_dt), 'd.m.Y');
                formParams.EvnSection_setTime = Ext.util.Format.date((!Ext.isEmpty(evn_ps_outcome_dt) ? evn_ps_outcome_dt : evn_ps_set_dt), 'H:i');
                params.DiagPred_id = base_form.findField('Diag_pid').getValue();
            }

            if ( this.params.EvnLeave_UKL ) {
                formParams.EvnLeave_UKL = this.params.EvnLeave_UKL;
            }

            if ( this.params.EvnLeave_setDate ) {
                formParams.EvnSection_disDate = this.params.EvnLeave_setDate;
            }

            if ( this.params.LeaveCause_id ) {
                formParams.LeaveCause_id = this.params.LeaveCause_id;
            }

            if ( this.params.LeaveType_id ) {
                formParams.LeaveType_id = this.params.LeaveType_id;
            }
			
            if ( this.params.LeaveTypeFed_id ) {
                formParams.LeaveTypeFed_id = this.params.LeaveTypeFed_id;
            }

            if ( this.params.LpuSection_id ) {
                formParams.LpuSection_id = this.params.LpuSection_id;
            }

            if ( this.params.MedPersonal_id ) {
                formParams.MedPersonal_id = this.params.MedPersonal_id;
            }

            if ( this.params.ResultDesease_id ) {
                formParams.ResultDesease_id = this.params.ResultDesease_id;
            }

            if ( this.params.TariffClass_id ) {
                formParams.TariffClass_id = this.params.TariffClass_id;
            }
        }
        else {
            var selected_record = grid.getSelectionModel().getSelected();

            if ( !selected_record || !selected_record.get('EvnSection_id') ) {
                return false;
            }

			if ( selected_record.get('accessType') != 'edit'
				|| (
					!Ext.isEmpty(getGlobalOptions().medpersonal_id)
					&& !Ext.isEmpty(selected_record.get('MedPersonal_id'))
					&& userHasWorkPlaceAtLpuSection(selected_record.get('LpuSection_id')) == false
					&& getGlobalOptions().isMedStatUser != true
					&& isSuperAdmin() != true
				)
				//|| selected_record.get('isLast') != 1
				|| selected_record.get('EvnSection_IsSigned') != 1
			) {
				params.action = 'view';
			}
			if(/*selected_record.get('HasWorkGraph') == 1 &&*/selected_record.get('accessType') == 'edit' && action == 'edit'){
				params.action = 'edit';
			}
            var evn_section_set_dt = getValidDT(Ext.util.Format.date(selected_record.get('EvnSection_setDate'), 'd.m.Y'), selected_record.get('EvnSection_setTime'));

            params.evnSectionIsFirst = true;
            params.evnSectionIsLast = true;

            grid.getStore().each(function(rec) {
                if ( rec.get('EvnSection_id') != selected_record.get('EvnSection_id') ) {
                    var set_dt = getValidDT(Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y'), rec.get('EvnSection_setTime'));

                    if ( set_dt < evn_section_set_dt ) {
                        params.evnSectionIsFirst = false;
                    }
                    else if ( set_dt > evn_section_set_dt ) {
                        params.evnSectionIsLast = false;
                    }
                }
            });
            formParams = selected_record.data;
            params.DiagPred_id = base_form.findField('Diag_pid').getValue();

            var first_evn_section_info = this.getEvnSectionInfo('first');
            params.evnSectionIsFirst = (first_evn_section_info.EvnSection_id == selected_record.get('EvnSection_id'));

            if( !params.evnSectionIsFirst ) {
                var prev_evn_section_info = this.getEvnSectionInfo('prev', {
                    EvnSection_id: selected_record.get('EvnSection_id'),
                    EvnSection_setDT: evn_section_set_dt
                });

                if( prev_evn_section_info.Diag_id )
                    params.DiagPred_id = prev_evn_section_info.Diag_id;
            }
        }

		var createDT = function(date, time) {
			var dt = (date instanceof Date)?date:new Date();
			var t = (!Ext.isEmpty(time)?time:'00:00').split(':');
			dt.setHours(t[0], t[1], 0, 0);
			return dt;
		};

        params.OtherEvnSectionList = [];
		grid.getStore().each(function(record) {
			if (record.get('EvnSection_id') != formParams.EvnSection_id) {
				params.OtherEvnSectionList.push(Ext.apply({
					EvnSection_setDT: createDT(record.data.EvnSection_setDate, record.data.EvnSection_setTime)
				}, record.data));
			}
		});
		params.OtherEvnSectionList.sort(function(a, b) {
			if (a.EvnSection_setDT < b.EvnSection_setDT) {
				return -1;
			}
			if (a.EvnSection_setDT > b.EvnSection_setDT) {
				return 1;
			}
			return 0;
		});

		var first_evn_section_info = this.getEvnSectionInfo('first');

        params.formParams = formParams;
		params.DiagPriem_id = base_form.findField('Diag_pid').getValue();
		var lprec = base_form.findField('LpuSection_pid').getStore().getById(base_form.findField('LpuSection_pid').getValue());
		if (lprec) {
			params.PLpuSection_Name = lprec.get('LpuSection_Name');
		}
        params.LpuSection_eid = base_form.findField('LpuSection_eid').getValue();
        params.LpuSection_pid = base_form.findField('LpuSection_pid').getValue();
        params.EvnPS_OutcomeDate = base_form.findField('EvnPS_OutcomeDate').getValue();
        params.EvnPS_OutcomeTime = base_form.findField('EvnPS_OutcomeTime').getValue();
		params.archiveRecord = this.archiveRecord;
		params.ARMType_id = this.ARMType_id || null;
		params.EvnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue();
		params.EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();
		params.EvnPS_id = base_form.findField('EvnPS_id').getValue();
		params.PrehospType_id = base_form.findField('PrehospType_id').getValue();
		params.PrehospType_SysNick = base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick');
		if (false && getRegionNick() == 'penza' && (
			!Ext.isEmpty(base_form.findField('EvnDirectionHTM_id').getValue())
			|| !Ext.isEmpty(base_form.findField('EvnPS_HTMTicketNum').getValue())
			|| !Ext.isEmpty(base_form.findField('EvnPS_HTMBegDate').getValue())
		)) {
			params.HTMedicalCareClassDisallowBlank = 1;
		}

		if (!this.findById('EPSEF_PrehospWaifPanel').hidden)
			params.EvnPS_IsWaif = base_form.findField('EvnPS_IsWaif').getValue();

        if ( this.childPS ) {
            //наличие этой переменной как бы намекает нам, что окно КВС было вызвано из поиска человека,
            // который был вызван из поиска КВС,
            //  который был вызван из Движения,
            //   которое было вызвано из редактирования КВС матери. Так-то!
            // Поэтому открываемое окно движения будет вторым по счету, и открывать его надо с другим идентификатором.
            params.childPS = true;
            if (this.ChildTermType_id){
                params.ChildTermType_id = this.ChildTermType_id;
            }
            if (this.BirthSpecStac_CountChild){
                params.BirthSpecStac_CountChild = this.BirthSpecStac_CountChild;
            }
            if (this.PersonChild_IsAidsMother){
                params.PersonChild_IsAidsMother = this.PersonChild_IsAidsMother;
            }
            getWnd({objectName:'swEvnSectionEditWindow2', objectClass:'swEvnSectionEditWindow'},{params:{id:'EvnSectionEditWindow2'}}).show(params);
        } else {
            getWnd('swEvnSectionEditWindow').show(params);
        };
    },
    openEvnStickEditWindow: function(action) {
        if ( this.findById('EPSEF_EvnStickPanel').hidden ) {
            return false;
        }

        if ( action != 'add' && action != 'edit' && action != 'view' ) {
            return false;
        }

        var base_form = this.findById('EvnPSEditForm').getForm();
        var grid = this.findById('EPSEF_EvnStickGrid');


        if ( action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
            this.doSave({
                callback: function() {
                    this.openEvnStickEditWindow(action);
                }.createDelegate(this),
                print: false
            });
            return false;
        }

        var formParams = new Object();
        var joborg_id = this.findById('EPSEF_PersonInformationFrame').getFieldValue('JobOrg_id');
        var params = new Object();
        var person_id = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_id');
        var person_birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
        var person_firname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname');
        var person_post = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Post');
        var person_secname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname');
        var person_surname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname');

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
                var evn_stick_fields = new Array();
                var i = 0;

                grid.getStore().fields.eachKey(function(key, item) {
                    evn_stick_fields.push(key);
                });

                for ( i = 0; i < evn_stick_fields.length; i++ ) {
                    record.set(evn_stick_fields[i], data.evnStickData[0][evn_stick_fields[i]]);
                }

                record.commit();
            }
        }.createDelegate(this);

        params.JobOrg_id = joborg_id;
		params.parentClass = 'EvnPS';
        params.Person_id = base_form.findField('Person_id').getValue();
        params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
        params.Person_Birthday = person_birthday;
        params.Person_Firname = person_firname;
        params.Person_Secname = person_secname;
        params.Person_Surname = person_surname;
        params.Person_Post = person_post;
        params.Server_id = base_form.findField('Server_id').getValue();

        formParams.EvnStick_mid = base_form.findField('EvnPS_id').getValue();

        params.stacBegDate = null;
        params.stacEndDate = null;
		params.LpuUnitType_SysNick = '';

        var emptyEndDate = false;

        this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec) {
            if (params.stacBegDate > rec.get('EvnSection_setDate') || params.stacBegDate == null) {
                params.stacBegDate = rec.get('EvnSection_setDate');
            }

            if (params.stacEndDate < rec.get('EvnSection_disDate') || params.stacEndDate == null) {
                if (rec.get('EvnSection_disDate').length == 0) {emptyEndDate = true;}
                params.stacEndDate = rec.get('EvnSection_disDate');
            } else {
				params.stacEndDate = null;
			}

			// если хотя бы одно движение в круглосутке, то передаём круглосутку
			if (params.LpuUnitType_SysNick != 'stac' && rec.get('LpuUnitType_SysNick')) {
				params.LpuUnitType_SysNick = rec.get('LpuUnitType_SysNick');
			}
        });

        if (emptyEndDate) {
            params.stacEndDate = null;
        }

        if ( action == 'add' ) {
            var evn_stick_beg_date = base_form.findField('EvnPS_setDate').getValue();
            var evn_section_store = this.findById('EPSEF_EvnSectionGrid').getStore();

            evn_section_store.each(function(record) {
                if ( evn_stick_beg_date == null || record.get('EvnSection_setDate') <= evn_stick_beg_date ) {
                    evn_stick_beg_date = record.get('EvnSection_setDate');
                }
            });

            formParams.EvnStick_pid = base_form.findField('EvnPS_id').getValue();
            //formParams.EvnStick_setDate = evn_stick_beg_date;
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
				grid.getStore().load({
					params: {
						EvnStick_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
					}
				});;
                grid.getView().focusRow(grid.getStore().indexOf(selected_record));
            }.createDelegate(this);
            //params.parentClass = selected_record.get('parentClass');
            params.parentNum = selected_record.get('EvnStick_ParentNum');
			params.archiveRecord = this.archiveRecord;

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
        if ( this.findById('EPSEF_EvnUslugaPanel').hidden ) {
            return false;
        }

        if ( action != 'add' && action != 'addOper' && action != 'edit' && action != 'view' ) {
            return false;
        }
		var wnd = this;
        var base_form = this.findById('EvnPSEditForm').getForm();
        var grid = this.findById('EPSEF_EvnUslugaGrid');

        if ( this.action == 'view' ) {
            if ( action == 'add' || action == 'addOper' ) {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }

        var params = new Object();

		// Если в КВС в поле “Вид оплаты” выбрано “Местный бюджет”, то при отказе в приемном отделении при добавлении услуг должны быть доступны только услуги связанные с группой 351
		if (getRegionNick() == 'ekb' && base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'bud' && !Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue())) {
			params.only351Group = true;
		}

        params.action = action;
        params.callback = function(data) {
            if ( !data || !data.evnUslugaData ) {
                grid.getStore().load({
                    params: {
                        pid: base_form.findField('EvnPS_id').getValue()
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
                var evn_usluga_fields = new Array();
                var i = 0;

                grid.getStore().fields.eachKey(function(key, item) {
                    evn_usluga_fields.push(key);
                });

                for ( i = 0; i < evn_usluga_fields.length; i++ ) {
                    record.set(evn_usluga_fields[i], data.evnUslugaData[evn_usluga_fields[i]]);
                }

                record.commit();
            }

			if (!getRegionNick().inlist([ 'kz' ]) && wnd.getOKSDiag()) {
				wnd.loadECGResult();
				wnd.saveInBskRegistry();
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
        params.parentClass = 'EvnPS';
        params.Person_id = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_id');
        params.Person_Birthday = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday');
        params.Person_Firname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Firname');
        params.Person_Secname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Secname');
        params.Person_Surname = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Surname');

        // Собрать данные для ParentEvnCombo
        var parent_evn_combo_data = new Array();
       this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec) {
	   if(rec.get('EvnSection_id')>0)
            parent_evn_combo_data.push({
                Evn_id: rec.get('EvnSection_id'),
                Evn_Name: Ext.util.Format.date(rec.get('EvnSection_setDate'), 'd.m.Y') + ' / ' + rec.get('LpuSection_Name') + ' / ' + rec.get('MedPersonal_Fio'),
                Evn_setDate: rec.get('EvnSection_setDate'),
                Evn_disDate: rec.get('EvnSection_disDate'),
                Evn_setTime: rec.get('EvnSection_setTime'),
                MedStaffFact_id: rec.get('MedStaffFact_id'),
                LpuSection_id: rec.get('LpuSection_id'),
                LpuSectionProfile_id: rec.get('LpuSectionProfile_id'),
                MedPersonal_id: rec.get('MedPersonal_id'),
				Diag_id: rec.get('Diag_id')
            })
        });

        switch ( action ) {
            case 'addOper':
            case 'add':
                params.action = 'add';
                if ( base_form.findField('EvnPS_id').getValue() == 0||wnd.priem==true) {
					wnd.priem=false;
                    this.doSave({
                        callback: function() {
                            this.openEvnUslugaEditWindow(action);
                        }.createDelegate(this),
                        print: false
                    });
                    return false;
                }

                params.formParams = {
                    PayType_id: base_form.findField('PayType_id').getValue(),
                    Person_id: base_form.findField('Person_id').getValue(),
                    PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
                    Server_id: base_form.findField('Server_id').getValue(),
					Evn_id: base_form.findField('EvnPS_id').getValue()
                };
                params.parentEvnComboData = parent_evn_combo_data;

				if(getRegionNick().inlist([ 'kareliya', 'perm', 'ekb', 'krym' ])){
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
						},
						params: {
							EvnPS_id: base_form.findField('EvnPS_id').getValue()
						},
						success: function(response, options) {
							if (!Ext.isEmpty(response.responseText)) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if(response_obj.length<=0&&params.parentEvnComboData.length<=0){
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function() {
											wnd.priem = true;
											base_form.findField('LpuSection_pid').focus(false);
										},
										icon: Ext.Msg.WARNING,
										msg: 'Не введено ни одного движения. Поля "Приемное отделение", "Врач приемного отделения" и "Диагноз приемного отделения" должны быть заполнены.',
										title: ERR_INVFIELDS_TIT
									});
									return false;
								}

								if ( response_obj.length > 0 ) {
									params.parentEvnComboData.push({
										Evn_id: response_obj[0].EvnSection_id,
										Evn_Name: response_obj[0].EvnSection_setDate + ' / ' + response_obj[0].LpuSection_Name + ' / ' + response_obj[0].MedPersonal_Fio,
										Evn_setDate: response_obj[0].EvnSection_setDate,
										Evn_setTime: response_obj[0].EvnSection_setTime,
										MedStaffFact_id: response_obj[0].MedStaffFact_id,
										LpuSection_id: response_obj[0].LpuSection_id,
										LpuSectionProfile_id: response_obj[0].LpuSectionProfile_id,
										MedPersonal_id: response_obj[0].MedPersonal_id,
										Diag_id: response_obj[0].Diag_id
									});
								}
							}

							if ( action == 'addOper' ) {
								getWnd('swEvnUslugaOperEditWindow').show(params);
							}
							else {
								getWnd('swEvnUslugaEditWindow').show(params);
							}
						}.createDelegate(this),
						url: '/?c=EvnSection&m=getSectionPriemData'
					});
				}
				else {
					if ( action == 'addOper' ) {
						getWnd('swEvnUslugaOperEditWindow').show(params);
					}
					else {
						getWnd('swEvnUslugaEditWindow').show(params);
					}
				}
				break;

            case 'edit':
            case 'view':
                // Открываем форму редактирования услуги (в зависимости от EvnClass_SysNick)

                var selected_record = grid.getSelectionModel().getSelected();

                if ( !selected_record || !selected_record.get('EvnUsluga_id') ) {
                    return false;
                }

				params.archiveRecord = this.archiveRecord;
				params.parentEvnComboData = [];

                var evn_usluga_id = selected_record.get('EvnUsluga_id');

                switch ( selected_record.get('EvnClass_SysNick') ) {
                    case 'EvnUslugaOnkoBeam':
                    case 'EvnUslugaOnkoChem':
                    case 'EvnUslugaOnkoGormun':
						params.formParams = {
                            EvnUslugaCommon_id: evn_usluga_id
                        }
                        params.parentEvnComboData = parent_evn_combo_data;

						getWnd('swEvnUslugaEditWindow').show(params);
						break;
                    case 'EvnUslugaCommon':
					case 'EvnUslugaOper':
						if(getRegionNick().inlist([ 'kareliya', 'perm', 'ekb', 'krym' ])){
							Ext.Ajax.request({
								failure: function(response, options) {
								sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
								},
								params: {
								EvnPS_id: base_form.findField('EvnPS_id').getValue()
								},
								success: function(response, options) {
								if (!Ext.isEmpty(response.responseText)) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if(response_obj.length<=0&&params.parentEvnComboData.length<=0){
										sw.swMsg.show({
											buttons: Ext.Msg.OK,
											fn: function() {
												wnd.formStatus = 'edit';
												base_form.findField('LpuSection_pid').focus(false);
											},
											icon: Ext.Msg.WARNING,
											msg: 'Не введено ни одного движения. Поля "Приемное отделение", "Врач приемного отделения" и "Диагноз приемного отделения" должны быть заполнены.',
											title: ERR_INVFIELDS_TIT
										});
										return false;
									}
									
									params.parentEvnComboData = parent_evn_combo_data;

									if ( response_obj.length > 0 ) {
										params.parentEvnComboData.push({
											Evn_id: response_obj[0].EvnSection_id,
											Evn_Name: response_obj[0].EvnSection_setDate + ' / ' + response_obj[0].LpuSection_Name + ' / ' + response_obj[0].MedPersonal_Fio,
											Evn_setDate: response_obj[0].EvnSection_setDate,
											Evn_setTime: response_obj[0].EvnSection_setTime,
											MedStaffFact_id: response_obj[0].MedStaffFact_id,
											LpuSection_id: response_obj[0].LpuSection_id,
											MedPersonal_id: response_obj[0].MedPersonal_id,
											Diag_id: response_obj[0].Diag_id
										});
									}
								}
								if(selected_record.get('EvnClass_SysNick')=='EvnUslugaCommon'){
									params.formParams = {
										EvnUslugaCommon_id: evn_usluga_id
									}
									getWnd('swEvnUslugaEditWindow').show(params);
								}else{
									params.formParams = {
										EvnUslugaOper_id: evn_usluga_id
									}
									getWnd('swEvnUslugaOperEditWindow').show(params);
								}
								  
								}.createDelegate(this),
								url: '/?c=EvnSection&m=getSectionPriemData'
							});
							}else{
								params.parentEvnComboData = parent_evn_combo_data;
								if(selected_record.get('EvnClass_SysNick')=='EvnUslugaCommon'){
								params.formParams = {
									EvnUslugaCommon_id: evn_usluga_id
								}
									getWnd('swEvnUslugaEditWindow').show(params);
								}else{
									params.formParams = {
										EvnUslugaOper_id: evn_usluga_id
									}
									getWnd('swEvnUslugaOperEditWindow').show(params);
								}
							}
                       
					break;
                    case 'EvnUslugaOnkoSurg':
                        params.EvnUslugaOnkoSurg_id = evn_usluga_id;
                        params.formParams = {
                            EvnUslugaOnkoSurg_id: evn_usluga_id
                        }
                        getWnd('swEvnUslugaOnkoSurgEditWindow').show(params);
                        break;

					case 'EvnUslugaPar':
						params.formParams = {
							EvnUslugaPar_id: evn_usluga_id
						}
						params.parentEvnComboData = parent_evn_combo_data;
						getWnd('swEvnUslugaParSimpleEditWindow').show(params);
						break;
						
                    default:
                        return false;
					break;
                }
                /*
                 if ( evn_usluga_edit_window.isVisible() ) {
                 sw.swMsg.alert('Сообщение', 'Окно редактирования услуги уже открыто', function() {
                 grid.getSelectionModel().selectFirstRow();
                 grid.getView().focusRow(0);
                 });
                 return false;
                 }
                 */

                break;
        }
    },
    openPrehospWaifInspectionEditWindow: function(action) {
        if ( this.findById('EPSEF_PrehospWaifPanel').hidden ) {
            return false;
        }

        if ( action != 'add' && action != 'edit' && action != 'view' ) {
            return false;
        }

        if ( this.action == 'view' ) {
            if ( action == 'add' ) {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }

        var base_form = this.findById('EvnPSEditForm').getForm();
        var view_frame = this.findById('EPSEF_PrehospWaifInspection');
        var grid = view_frame.getGrid();

        if ( getWnd('swPrehospWaifInspectionEditWindow').isVisible() )
        {
            sw.swMsg.alert('Сообщение', 'Окно редактирования осмотра уже открыто', function() {
                grid.getSelectionModel().selectFirstRow();
                grid.getView().focusRow(0);
            });
            return false;
        }

        var params = new Object();

        params.action = action;
        params.MedStaffFact_id = getGlobalOptions().CurMedStaffFact_id;
        params.LpuSection_id  = getGlobalOptions().CurLpuSection_id;
        params.PrehospWaifInspection_SetDT = base_form.findField('EvnPS_setDate').getValue();
        params.EvnPS_id = base_form.findField('EvnPS_id').getValue();
        params.Diag_id = base_form.findField('Diag_pid').getValue();
        params.callback = this.PrehospWaifInspectionRefreshGrid;
        params.onHide = function() {
            if ( grid.getSelectionModel().getSelected() ) {
                grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
            }
            else {
                grid.getView().focusRow(0);
                grid.getSelectionModel().selectFirstRow();
            }
        };

        switch ( action ) {
            case 'add':
                if ( base_form.findField('EvnPS_id').getValue() == 0 ) {
                    this.doSave({
                        callback: function() {
                            this.openPrehospWaifInspectionEditWindow(action);
                        }.createDelegate(this),
                        print: false
                    });
                    return false;
                }

                getWnd('swPrehospWaifInspectionEditWindow').show(params);
                break;

            case 'edit':
            case 'view':
                var record = grid.getSelectionModel().getSelected();
                if ( record )
                {
					params.archiveRecord = this.archiveRecord;
                    params.PrehospWaifInspection_id = record.get('PrehospWaifInspection_id');
                    getWnd('swPrehospWaifInspectionEditWindow').show(params);
                }
                else
                {
                    sw.swMsg.alert('Сообщение', 'Вы не выбрали осмотр!', function() {
                        grid.focus();
                    });
                }
                break;
        }
    },
	//BOB - 04.09.2018	
	openEvnReanimatPeriodEditWindow: function(action) {
		
        if ( this.findById('EPSEF_EvnReanimatPeriodPanel').hidden ) {
            return false;
        }

        if ( action != 'add' && action != 'edit' && action != 'view' ) {
            return false;
        }

        if ( this.action == 'view' ) {
            if ( action == 'add' ) {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }
        if ( getWnd('swEvnReanimatPeriodEditWindow').isVisible() )
        {
            sw.swMsg.alert('Сообщение', 'Окно редактирования Реанимационного периода уже открыто');
            return false;
        }
		
		var SelRaw = this.findById('EPSEF_EvnReanimatPeriodGrid').getSelectionModel().getSelected(); // .get('EvnReanimatPeriod_id');
		if (SelRaw == null){
            sw.swMsg.alert('Сообщение', 'Не выбрана строка в таблице');
            return false;
		}

		var params = {
			EvnReanimatPeriod_id: SelRaw.get('EvnReanimatPeriod_id'),
			ERPEW_title: ( action == 'edit' ) ? langs('Редактирование реанимационного периода') : langs('Просмотр реанимационного периода'),  			
			action: action,
			UserMedStaffFact_id: (this.userMedStaffFact && this.userMedStaffFact.MedStaffFact_id ? this.userMedStaffFact.MedStaffFact_id : null),
			userMedStaffFact: (this.userMedStaffFact ? this.userMedStaffFact : null),
			from: 'EvnPS', 
			ARMType: this.ARMType 											
		};
		var win = this;
		var RP_saved = false;
		params.Callback = function(pdata) {
			getWnd('swEvnReanimatPeriodEditWindow').hide();                            
			RP_saved = pdata; 
			win.findById('EPSEF_EvnReanimatPeriodGrid').getStore().load({
				params: {
					EvnPS_id: win.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
				}
			});

		};  
		
		console.log('BOB_params3=',params); 
		getWnd('swEvnReanimatPeriodEditWindow').show(params);
	},
	params: {
        EvnLeave_setDate: null,
        EvnLeave_UKL: null,
        LeaveCause_id: null,
        LeaveType_id: null,
		LeaveTypeFed_id: null,
        LpuSection_id: null,
        MedPersonal_id: null,
        ResultDesease_id: null,
        TariffClass_id: null
    },
    plain: true,
    PrehospWaifInspectionRefreshGrid: function()
    {
        if ( Ext.getCmp('EPSEF_PrehospWaifPanel').hidden ) {
            return false;
        }

        var base_form = Ext.getCmp('EvnPSEditForm').getForm();
        if ( this.action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
            this.doSave({
                callback: function() {
                    this.PrehospWaifInspectionRefreshGrid();
                }.createDelegate(this),
                print: false
            });
            return false;
        }
        var view_frame = Ext.getCmp('EPSEF_PrehospWaifInspection');
        view_frame.removeAll(true);
        var params = {EvnPS_id: base_form.findField('EvnPS_id').getValue()};
        params.start = 0;
        params.limit = 100;
        view_frame.loadData({globalFilters:params});
    },
	setSpecificsPanelVisibility: function() {
		var win = this;		var base_form = win.findById('EvnPSEditForm').getForm();
		var PrehospWaifRefuseCause_Code = base_form.findField('PrehospWaifRefuseCause_id').getFieldValue('PrehospWaifRefuseCause_Code');
		var Diag_pCode = base_form.findField('Diag_pid').getFieldValue('Diag_Code');
		
		if (getRegionNick() == 'perm' && !Ext.isEmpty(PrehospWaifRefuseCause_Code) && Diag_pCode && Diag_pCode.search(new RegExp("^(C|D0)", "i")) >= 0) {
			this.findById('EPSEF_SpecificsPanel').show();
			this.loadSpecificsTree();
		} else {
			this.findById('EPSEF_SpecificsPanel').hide();
		}
	},
	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.findById('EvnPSEditForm').getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var Region_Nick = getRegionNick();

		var record = base_form.findField('EvnPS_IsCont').getStore().getById(base_form.findField('EvnPS_IsCont').getValue());
		var prehospDirect_combo = base_form.findField('PrehospDirect_id');
		var prehospDirect_code = prehospDirect_combo.getFieldValue('PrehospDirect_Code');

		base_form.items.each(function(field){
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;

			var Diag_pCode = base_form.findField('Diag_pid').getFieldValue('Diag_Code');
			var PrehospWaifRefuseCause_Code = base_form.findField('PrehospWaifRefuseCause_id').getFieldValue('PrehospWaifRefuseCause_Code');
			var LeaveType_SysNick = base_form.findField('LeaveType_prmid').getFieldValue('LeaveType_SysNick');
			var DeseaseType_SysNick = base_form.findField('DeseaseType_id').getFieldValue('DeseaseType_SysNick');
			var EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();
			var EvnPS_OutcomeDate = base_form.findField('EvnPS_OutcomeDate').getValue();

			var diag_p_code_full = !Ext.isEmpty(Diag_pCode)?String(Diag_pCode).slice(0, 3):'';

			switch(field.getName()) {
				case 'DeseaseType_id':
					var dateX20181101 = new Date(2018, 10, 1); // 01.11.2018

					/* не понятно, что за условия по регионам, в ТЗ такого нет
					visible = (
						Region_Nick != 'kz'
						&& !Ext.isEmpty(PrehospWaifRefuseCause_Code)
						&& !Ext.isEmpty(diag_p_code_full)
						&& diag_p_code_full.substr(0, 1) != 'Z'
					);
					allowBlank = true;

					if (
						Region_Nick == 'kareliya'
						&& visible == true
						&& (
							(typeof EvnPS_OutcomeDate == 'object' && EvnPS_OutcomeDate >= dateX20181101)
							|| (diag_p_code_full >= 'C00' && diag_p_code_full <= 'C97')
							|| (diag_p_code_full >= 'D00' && diag_p_code_full <= 'D09')
						)
					) {
						allowBlank = false;
					}

					if (
						Region_Nick == 'ufa'
						&& visible == true
					) {
						allowBlank = false;
					}

					if(visible == true && typeof EvnPS_OutcomeDate == 'object' && EvnPS_OutcomeDate >= dateX20181101) {
						allowBlank = false;
					}
					*/
					visible = (
						Region_Nick != 'kz'
						&& !Ext.isEmpty(diag_p_code_full)
					);
					allowBlank = !(
						visible
						&& diag_p_code_full.substr(0, 1) != 'Z'
						&& (!Ext.isEmpty(PrehospWaifRefuseCause_Code) || (getRegionNick() == 'buryatiya' && LeaveType_SysNick == 'osmpp'))
						&& (typeof EvnPS_OutcomeDate == 'object' && EvnPS_OutcomeDate >= dateX20181101)
					);

					//если дата выписки не указана, все равно нужно загрузить значения в DeseaseType_id
					var releaseDate = Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue()) ? new Date():base_form.findField('EvnPS_OutcomeDate').getValue();
					base_form.findField('DeseaseType_id').getStore().clearFilter();
					base_form.findField('DeseaseType_id').lastQuery = '';
					base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {
						return (
							(!rec.get('DeseaseType_begDT') || rec.get('DeseaseType_begDT') <= releaseDate)
							&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= releaseDate)
						)
					});
					break;
				case 'TumorStage_id':
					var dateX20170901 = new Date(2017, 8, 1); // 01.09.2017
					var dateX20180601 = new Date(2018, 5, 1); // 01.06.2018
					visible = (
						Region_Nick.inlist(['kareliya','ekb']) &&
						!Ext.isEmpty(PrehospWaifRefuseCause_Code) && (
							(diag_p_code_full >= 'C00' && diag_p_code_full <= 'C97') ||
							(diag_p_code_full >= 'D00' && diag_p_code_full <= 'D09')
						) &&
						(!Region_Nick.inlist(['kareliya']) || (!Ext.isEmpty(EvnPS_setDate) && EvnPS_setDate >= dateX20170901)) &&
						(!Region_Nick.inlist(['ekb']) || (!Ext.isEmpty(EvnPS_setDate) && EvnPS_setDate < dateX20180601))
					);
					if (visible) {
						enable = Region_Nick.inlist(['ekb']) || DeseaseType_SysNick == 'new';
						if (getRegionNick() != 'ekb') {
							filter = function (record) {
								return true;
								//return record.get('TumorStage_Code').inlist([0, 1, 2, 3, 4])
							};
						}
						if (!enable) value = null;
					}
					allowBlank = !enable;
					break;
				case 'EvnPS_HTMTicketNum':
					visible = (
						Region_Nick.inlist(['adygeya', 'astra', 'krasnoyarsk', 'krym', 'penza', 'perm', 'ekb', 'ufa', 'pskov', 'buryatiya', 'khak', 'kareliya', 'vologda', 'msk' ])
					);
					break;
				case 'Org_did':
					//--организация
                    if(prehospDirect_code && prehospDirect_code == 2){
                    	allowBlank = false;
                    	enable = true;
                    }else {
                    	allowBlank = true;
                    	enable = (prehospDirect_code != 1);
                    }
					break;
				case 'LpuSection_did':
					//--отделение
					if(prehospDirect_code && prehospDirect_code == 1){
                    	allowBlank = false;
                    	enable = true;
                    }else{
                    	allowBlank = true;
                    	enable = false;
                    }
					break;
				case 'PrehospDirect_id':
					//--кем направлен
					if(getRegionNick().inlist(['ufa','pskov']) ){
						var prehospType_SysNick = base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick');
						allowBlank = (prehospType_SysNick && prehospType_SysNick == 'plan') ? false : true;
					}
					break;
				case 'DiagSetPhase_did':
				case 'DiagSetPhase_pid':
					field.getStore().clearFilter();
					field.lastQuery = '';
					var cmpdate = new Date();
					if(!Ext.isEmpty(EvnPS_OutcomeDate)) cmpdate = EvnPS_OutcomeDate;
					else if(!Ext.isEmpty(EvnPS_setDate)) cmpdate = EvnPS_setDate;
					
					field.getStore().filterBy(function(rec) {
						return (!rec.get('DiagSetPhase_begDT') || rec.get('DiagSetPhase_begDT') <= cmpdate)
								&& (!rec.get('DiagSetPhase_endDT') || rec.get('DiagSetPhase_endDT') >= cmpdate);
					});
					var DSPid = field.getStore().findBy(function(rec){
							return rec.get('DiagSetPhase_id')==field.getValue();
						});
					if(DSPid<0) field.clearValue(); else field.setValue(field.getValue());
					break;
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
		});
	},
	reloadUslugaComplexField: function(needUslugaComplex_id, wantUslugaComplex_id) {
		if (!getRegionNick().inlist(['ekb','perm'])) {
			return false;
		}

		var win = this;
		if (win.blockUslugaComplexReload) {
			return false;
		}

		var base_form = this.findById('EvnPSEditForm').getForm();
		var field = base_form.findField('UslugaComplex_id');

		if (getRegionNick() == 'perm') {
			field.getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
			field.getStore().baseParams.FedMedSpec_id = base_form.findField('MedStaffFact_pid').getFieldValue('FedMedSpec_id');
		}

		if (getRegionNick() == 'ekb') {
			if (base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'bud') {
				field.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([350]);
			} else {
				field.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300, 301]);
			}
		}

		field.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_pid').getValue();
		field.getStore().baseParams.MedPersonal_id = base_form.findField('MedStaffFact_pid').getFieldValue('MedPersonal_id');

		field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(base_form.findField('EvnPS_OutcomeDate').getValue(), 'd.m.Y');
		field.getStore().baseParams.query = "";

		// повторно грузить одно и то же не нужно
		var newUslugaComplexParams = Ext.util.JSON.encode(field.getStore().baseParams);
		if (needUslugaComplex_id || newUslugaComplexParams != win.lastUslugaComplexParams) {
			win.lastUslugaComplexParams = newUslugaComplexParams;
			var currentUslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			field.lastQuery = 'This query sample that is not will never appear';
			field.getStore().removeAll();

			var params = {};
			if (needUslugaComplex_id) {
				params.UslugaComplex_id = needUslugaComplex_id;
				currentUslugaComplex_id = needUslugaComplex_id;
			}

			field.getStore().load({
				callback: function (rec) {
					var index = -1;
					if (wantUslugaComplex_id) {
						index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
							return (rec.get('UslugaComplex_id') == wantUslugaComplex_id);
						});
					}
					if (index < 0) {
						index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
							return (rec.get('UslugaComplex_id') == currentUslugaComplex_id);
						});
					}

					if (index >= 0) {
						var record = base_form.findField('UslugaComplex_id').getStore().getAt(index);
						field.setValue(record.get('UslugaComplex_id'));
						field.setRawValue(record.get('UslugaComplex_Code') + '. ' + record.get('UslugaComplex_Name'));
					} else {
						field.clearValue();
					}

					field.fireEvent('change', field, field.getValue());
				},
				params: params
			});
		} else if (wantUslugaComplex_id) {
			index = base_form.findField('UslugaComplex_uid').getStore().findBy(function (rec) {
				return (rec.get('UslugaComplex_id') == wantUslugaComplex_id);
			});
			if (index >= 0) {
				var record = base_form.findField('UslugaComplex_uid').getStore().getAt(index);
				field.setValue(record.get('UslugaComplex_id'));
				field.setRawValue(record.get('UslugaComplex_Code') + '. ' + record.get('UslugaComplex_Name'));
			} else {
				field.clearValue();
			}
		}
	},
    selectEvnDirection: function(ed_record) {
		var bf = this.findById('EvnPSEditForm').getForm();
		var PrehospDirect_id = (ed_record.get('Lpu_id') != getGlobalOptions().lpu_id ? 2 : 1 );
        bf.findField('PrehospDirect_id').setValue(PrehospDirect_id);
        var iswd_combo = bf.findField('EvnPS_IsWithoutDirection');
        iswd_combo.setValue(2);
        iswd_combo.fireEvent('change', iswd_combo, 2);

        bf.findField('EvnDirection_id').setValue(ed_record.get('EvnDirection_id'));
        if (ed_record.get('EvnDirectionHTM_id')) {
			bf.findField('EvnDirectionHTM_id').setValue(ed_record.get('EvnDirectionHTM_id'));
		} else {
			bf.findField('EvnDirectionHTM_id').setValue(null);
		}

		switch ( PrehospDirect_id ) {
			case 1:
				bf.findField('LpuSection_did').setValue(ed_record.get('LpuSection_id'));
			break;

			case 2:
				bf.findField('Org_did').getStore().loadData([{
					Org_id: ed_record.get('Org_id'),
					Org_Code: null,
					Org_Nick: ed_record.get('Org_Nick'),
					Org_Name: ed_record.get('Org_Name')
				}], false);
				bf.findField('Org_did').setValue(ed_record.get('Org_id'));
			break;
		}

        /*bf.findField('Org_did').getStore().load({
         callback: function(records, options, success) {
         if ( success ) {
         bf.findField('Org_did').setValue(ed_record.get('Org_did'));
         }
         },
         params: {
         Org_id: ed_record.get('Org_did'),
         OrgType: 'lpu'
         }
         });*/

        bf.findField('EvnDirection_Num').setValue(ed_record.get('EvnDirection_Num'));
        bf.findField('EvnDirection_setDate').setValue(!Ext.isEmpty(ed_record.get('EvnDirection_setDateTime')) ? ed_record.get('EvnDirection_setDateTime') : ed_record.get('EvnDirection_setDate'));

        var diag_id = ed_record.get('Diag_id');
        if ( diag_id ) {
            bf.findField('Diag_did').getStore().load({
                callback: function() {
                    bf.findField('Diag_did').getStore().each(function(record) {
                        if ( record.get('Diag_id') == diag_id ) {
                            bf.findField('Diag_did').setValue(diag_id);
                            bf.findField('Diag_did').fireEvent('select', bf.findField('Diag_did'), record, 0);
                        }
                    });
                },
                params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
            });
        }

		var DirType_id = ed_record.get('DirType_id');
		var PrehospType_SysNick = null;
		switch(Number(DirType_id)) {
			case 1: PrehospType_SysNick = 'plan';break;
			case 5: PrehospType_SysNick = 'extreme';break;
		}
		if (PrehospType_SysNick) {
			bf.findField('PrehospType_id').setFieldValue('PrehospType_SysNick', PrehospType_SysNick);
			bf.findField('PrehospType_id', bf.findField('PrehospType_id'), bf.findField('PrehospType_id').getValue());
		}
    },
	checkVMPFieldEnabled: function() {
    	if (!getRegionNick().inlist(['perm','penza'])) {
    		return;
		}

		var base_form = this.findById('EvnPSEditForm').getForm();
		if (base_form.findField('DirType_id').getValue() == 19 || this.action == 'view') {
			base_form.findField('EvnPS_HTMBegDate').disable();
			base_form.findField('EvnPS_HTMTicketNum').disable();
			base_form.findField('EvnPS_HTMHospDate').disable();
		} else {
			base_form.findField('EvnPS_HTMBegDate').enable();
			base_form.findField('EvnPS_HTMTicketNum').enable();
			base_form.findField('EvnPS_HTMHospDate').enable();
		}
	},
	printEvnPSKSG: function() {
		var evn_ps_id = this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue();
		printBirt({
			'Report_FileName': 'Raschet_KSG.rptdesign',
			'Report_Params': '&paramEvnPS=' + evn_ps_id,
			'Report_Format': 'html'
		});
	},
    printEvnPS: function(Parent_Code, printType) {
        if ( 'add' == this.action || 'edit' == this.action ) {
            this.doSave({
                print: true,
				printType: printType,
				Parent_Code: Parent_Code
            });
        }
        else if ( 'view' == this.action ) {
            var evn_ps_id = this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue();
			var grid = this.findById('EPSEF_EvnSectionGrid');
			var KVS_Type = '';
			var EvnSection_id = 0;
			if(Parent_Code == '5')
			{
				KVS_Type = 'VG'
				EvnSection_id = grid.getSelectionModel().getSelected().get('EvnSection_id');
			}

			if (printType == 'KSG') {
				printBirt({
					'Report_FileName': 'Raschet_KSG.rptdesign',
					'Report_Params': '&paramEvnPS=' + evn_ps_id,
					'Report_Format': 'html'
				});
			} else {
				var options = {};
				options.EvnPS_id = evn_ps_id;
				options.Parent_Code = Parent_Code;
				options.KVS_Type = KVS_Type;
				options.EvnSection_id = EvnSection_id;
				printEvnPS(options);
			}
        }
    },
    resizable: true,
	setEvnPSOutcomeDT: function() {
		var base_form = this.findById('EvnPSEditForm').getForm();

		if ( Ext.isEmpty(base_form.findField('LpuSection_pid').getValue()) ) {
			return false;
		}

		var first_evn_section_info = this.getEvnSectionInfo('first');

		if ( !Ext.isEmpty(first_evn_section_info.EvnSection_setDT) ) {
			base_form.findField('EvnPS_OutcomeDate').setValue(first_evn_section_info.EvnSection_setDT);
			base_form.findField('EvnPS_OutcomeDate').fireEvent('change', base_form.findField('EvnPS_OutcomeDate'), base_form.findField('EvnPS_OutcomeDate').getValue());
			base_form.findField('EvnPS_OutcomeTime').setValue(Ext.util.Format.date(first_evn_section_info.EvnSection_setDT, 'H:i'));
		}
	},
	checkForCostPrintPanel: function() {
		var base_form = this.findById('EvnPSEditForm').getForm();

		this.findById('EPSEF_CostPrintPanel').hide();
		base_form.findField('EvnCostPrint_Number').setContainerVisible(getRegionNick() == 'khak');
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		var last_evn_section_info = this.getEvnSectionInfo('last');
		var disabledCodes = ['5', '104', '204'];
		if (getRegionNick() == 'perm') {
			disabledCodes = [];
		}
		var allowShowCostPrintPanel = (
			!Ext.isEmpty(base_form.findField('EvnCostPrint_setDT').getValue())
			&& (
				(/*getRegionNick().inlist([ 'kareliya' ]) &&*/ !getRegionNick().inlist([ 'buryatiya', 'ufa', 'penza', 'pskov' ]) && !Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()))
				|| (getRegionNick().inlist([ 'buryatiya', 'pskov' ]) && base_form.findField('LeaveType_prmid').getFieldValue('LeaveType_Code') == 603)
				|| (last_evn_section_info && !Ext.isEmpty(last_evn_section_info.LeaveType_Code) && !last_evn_section_info.LeaveType_Code.toString().inlist(disabledCodes))
			)
		);
		if (allowShowCostPrintPanel && getRegionNick().inlist(['perm', 'kz', 'ufa'])) {
			this.findById('EPSEF_CostPrintPanel').show();
			// поля обязтаельные
			base_form.findField('EvnCostPrint_setDT').setAllowBlank(false);
			base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	filterLpuSectionProfile: function() {
		var win = this;
		var base_form = this.findById('EvnPSEditForm').getForm();
		if ( getRegionNick() == 'ekb' ) {
			var params = {
				LpuSection_id: base_form.findField('LpuSection_pid').getValue(),
				MedPersonal_id: base_form.findField('MedStaffFact_pid').getFieldValue('MedPersonal_id'),
				onDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y'),
				LpuSectionProfileGRAPP_CodeIsNotNull: (base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' ? 1 : null)
			};

			// повторно грузить одно и то же не нужно
			var newLpuSectionProfileParams = Ext.util.JSON.encode(params);
			if (newLpuSectionProfileParams != win.lastLpuSectionProfileParams) {
				base_form.findField('LpuSectionProfile_id').lastQuery = '';
				base_form.findField('LpuSectionProfile_id').getStore().removeAll();
				win.lastLpuSectionProfileParams = newLpuSectionProfileParams;
				base_form.findField('LpuSectionProfile_id').getStore().load({
					params: params,
					callback: function () {
						// если старого значения нет, то очищаем
						var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
							return (rec.get('LpuSectionProfile_id') == base_form.findField('LpuSectionProfile_id').getValue());
						});

						if (index >= 0) {
							base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getValue());
						}
						else {
							base_form.findField('LpuSectionProfile_id').clearValue();
						}

						base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
					}
				});
			}
		}

		if ( getRegionNick().inlist([ 'krym', 'buryatiya' ] )) {
			var
				LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue(),
				MedStaffFact_pid = base_form.findField('MedStaffFact_pid').getValue();

			var filterLSP = function() {
				base_form.findField('LpuSectionProfile_id').lastQuery = '';
				base_form.findField('LpuSectionProfile_id').getStore().clearFilter();

				// Список профилей тянем с отделения
				var
					LpuSection_id = base_form.findField('MedStaffFact_pid').getFieldValue('LpuSection_id');
					lpuSectionProfileList = new Array();

				if ( !Ext.isEmpty(LpuSection_id) ) {
					setLpuSectionGlobalStoreFilter({
						id: LpuSection_id
					});

					if ( swLpuSectionGlobalStore.getCount() > 0 ) {
						var lpuSectionData = swLpuSectionGlobalStore.getAt(0);

						lpuSectionProfileList.push(lpuSectionData.get('LpuSectionProfile_id'));

						if ( !Ext.isEmpty(lpuSectionData.get('LpuSectionLpuSectionProfileList')) ) {
							lpuSectionProfileList = lpuSectionProfileList.concat(lpuSectionData.get('LpuSectionLpuSectionProfileList').split(','));
						}
					}
				}

				base_form.findField('LpuSectionProfile_id').getStore().filterBy(function(rec) {
					return rec.get('LpuSectionProfile_id').inlist(lpuSectionProfileList);
				});

				// если старого значения нет, то очищаем
				var index = -1;

				if ( !Ext.isEmpty(LpuSectionProfile_id) ) {
					index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
						return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
					});
				}

				if ( index >= 0 ) {
					base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getValue());
				}
				else {
					base_form.findField('LpuSectionProfile_id').clearValue();
				}
			}

			// из-за того, что при открытии формы вызывается из нескольких мест, происходило множественное выполнение
			if (win.LSPLoading) {
				clearTimeout(win.LSPLoading);
			};
			win.LSPLoading = setTimeout(function () {
				if ( base_form.findField('LpuSectionProfile_id').getStore().getCount() == 0 ) {
					base_form.findField('LpuSectionProfile_id').getStore().load({
						callback: filterLSP
					});
				}
				else {
					filterLSP();
				}
			}, 100);
		}

		if ( getRegionNick() == 'perm' ) {
			var onDate = this.getEvnSectionInfo('last').EvnSection_setDT;
			if (!onDate) {
				onDate = base_form.findField('EvnPS_setDate').getValue();
			}
			var params = {
				LpuSection_id: base_form.findField('LpuSection_pid').getValue(),
				onDate: Ext.util.Format.date(onDate, 'd.m.Y')
			};

			// повторно грузить одно и то же не нужно
			var newLpuSectionProfileParams = Ext.util.JSON.encode(params);
			if (newLpuSectionProfileParams != win.lastLpuSectionProfileParams) {
				base_form.findField('LpuSectionProfile_id').lastQuery = '';
				base_form.findField('LpuSectionProfile_id').getStore().removeAll();
				win.lastLpuSectionProfileParams = newLpuSectionProfileParams;
				base_form.findField('LpuSectionProfile_id').getStore().load({
					params: params,
					callback: function () {
						// если старого значения нет, то очищаем
						var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
							return (rec.get('LpuSectionProfile_id') == base_form.findField('LpuSectionProfile_id').getValue());
						});

						if (index >= 0) {
							base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getValue());
						}
						else {
							base_form.findField('LpuSectionProfile_id').clearValue();
						}

						if (base_form.findField('LpuSectionProfile_id').getStore().getCount() == 1) {
							base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id'));
						}

						base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
					}
				});
			}
		}

		if ( getRegionNick() == 'penza' ) {
			var onDate = this.getEvnSectionInfo('last').EvnSection_setDT;
			if (!onDate) {
				onDate = base_form.findField('EvnPS_setDate').getValue();
			}
			var params = {
				onDate: Ext.util.Format.date(onDate, 'd.m.Y')
			};

			// повторно грузить одно и то же не нужно
			var newLpuSectionProfileParams = Ext.util.JSON.encode(params);
			if (newLpuSectionProfileParams != win.lastLpuSectionProfileParams) {
				base_form.findField('LpuSectionProfile_id').lastQuery = '';
				base_form.findField('LpuSectionProfile_id').getStore().removeAll();
				win.lastLpuSectionProfileParams = newLpuSectionProfileParams;
				base_form.findField('LpuSectionProfile_id').getStore().load({
					params: params,
					callback: function () {
						// если старого значения нет, то очищаем
						var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
							return (rec.get('LpuSectionProfile_id') == base_form.findField('LpuSectionProfile_id').getValue());
						});

						if (index >= 0) {
							base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getValue());
						}
						else {
							base_form.findField('LpuSectionProfile_id').clearValue();
						}

						if (base_form.findField('LpuSectionProfile_id').getStore().getCount() == 1) {
							base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id'));
						}

						base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
					}
				});
			}
		}
	},
	filterDiagByDate: function() { // проверяет что дата начала диагноза не больше даты приёма/выписки
		var base_form = this.findById('EvnPSEditForm').getForm(),
			diagField = base_form.findField('Diag_did'),
			diagBegDate,
			filterDate = base_form.findField('EvnPS_setDate').getValue();

		diagField.lastQuery = '';
		filterDate = Ext.util.Format.date(filterDate, 'd.m.Y');
		diagField.filterDate = filterDate;
		filterDate = filterDate.split('.').reverse().join('.');
		if (!Ext.isEmpty(diagField.getValue()) && !Ext.isEmpty(diagField.getFieldValue('Diag_begDate'))){
			if (!Ext.isEmpty(diagField.getFieldValue('Diag_begDate').date)) {
				diagBegDate = diagField.getFieldValue('Diag_begDate').date.substring(0,10).split('-').join('.');
			} else {
				diagBegDate = diagField.getFieldValue('Diag_begDate').split('.').reverse().join('.');
			}

			if (diagBegDate > filterDate){
				diagField.clearValue();
			}
		}
	},
	setMedicalCareFormTypeAllowBlank: function() {
		var base_form = this.findById('EvnPSEditForm').getForm();
		var date = base_form.findField('EvnPS_OutcomeDate').getValue();
		switch(getRegionNick()){
			case 'perm':
				// Поле обязательно, при отказах с приемном отделении с 01-05-2016, в остальных случаях поле видимо, доступно, но необязательно.
				var xdate = new Date(2016,4,1);

				base_form.findField('MedicalCareFormType_id').setAllowBlank(true);
				if (!Ext.isEmpty(date) && date >= xdate && !Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue())) {
					base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
				}
			break;
			case 'krym':
				// Поле обязательно при отказах с приемном отделении с 01-05-2017, в остальных случаях поле видимо, доступно, но необязательно.
				var xdate = new Date(2017,4,1);
				base_form.findField('MedicalCareFormType_id').disable();

				base_form.findField('MedicalCareFormType_id').setAllowBlank(true);

				var LpuUnitType_SysNick = base_form.findField('LpuSection_eid').getFieldValue('LpuUnitType_SysNick') || '';

				if (
					!Ext.isEmpty(date) && date >= xdate
					&& (
						!Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue())
						|| LpuUnitType_SysNick.inlist([ 'dstac', 'hstac', 'pstac' ])
					)
				) {
					base_form.findField('MedicalCareFormType_id').setAllowBlank(LpuUnitType_SysNick.inlist([ 'dstac', 'hstac', 'pstac' ]));

					if ( this.action != 'view' ) {
						base_form.findField('MedicalCareFormType_id').enable();
					}
				}
				else if ( Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) ) {
					base_form.findField('MedicalCareFormType_id').clearValue();
				}
			break;
			case 'buryatiya':
				base_form.findField('MedicalCareFormType_id').setAllowBlank(true);
				if (base_form.findField('LeaveType_prmid').getFieldValue('LeaveType_SysNick') == 'osmpp') {
					base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
				}
			break;
			case 'ekb':
			case 'kareliya':
				base_form.findField('MedicalCareFormType_id').setAllowBlank(true);
				if (!Ext.isEmpty(date)) {
					base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
				}
			break;
			case 'penza':
				base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
			break;
		}
	},
	setPrehospArriveAllowBlank: function() {
		if(getRegionNick().inlist(['perm','ufa'])){
			var base_form = this.findById('EvnPSEditForm').getForm();
			var date = base_form.findField('EvnPS_setDate').getValue();
			var field = base_form.findField('PrehospArrive_id');
			var xdate = new Date(2016,9,1);
			var lastEvnSection = this.getEvnSectionInfo('last');
			if (!Ext.isEmpty(lastEvnSection.EvnSection_disDT)) {
				date = lastEvnSection.EvnSection_disDT;
			}
			if(Ext.isEmpty(date) || date>xdate)	{
				field.setAllowBlank(false);
			} else {
				field.setAllowBlank(true);
			}
		}
		else if (getRegionNick().inlist(['buryatiya','pskov'])) {
			var base_form = this.findById('EvnPSEditForm').getForm();
			var field = base_form.findField('PrehospArrive_id');
			field.setAllowBlank(false);
		}
	},
	setDiagEidAllowBlank: function(clear) {
		if(getRegionNick() != 'kz'){
			var win = this;
			var base_form = this.findById('EvnPSEditForm').getForm();
			var date = base_form.findField('EvnPS_setDate').getValue();
			var field = base_form.findField('Diag_eid');
			var xdate = new Date(2016,0,1);
			var diag_combo = base_form.findField('Diag_pid');
			var diag_pid = diag_combo.getValue();
			if(!Ext.isEmpty(diag_pid) 
				&& diag_combo.getStore().getById(diag_pid) 
				&& diag_combo.getStore().getById(diag_pid).get('Diag_Code').search(new RegExp("^[ST]", "i")) >= 0
				&& (Ext.isEmpty(date) || date>=xdate)
				&& this.action != 'view'
			) {
				field.setAllowBlank(false);
				field.enable();
			} else {
				field.setAllowBlank(true);
				field.disable();
				if(clear){
					field.setValue('');
				}
			}
			if(getRegionNick() == 'ufa' && this.findById('EPSEF_EvnSectionGrid').getStore().data.length > 0){
				this.findById('EPSEF_EvnSectionGrid').getStore().each(function(rec){
					if(rec.get('EvnSection_id') > 0){
						Ext.Ajax.request({
							url: '/?c=EvnSection&m=getEvnSectionDiag',
							params: {
								EvnSection_id: rec.get('EvnSection_id')
							},
							callback:function (options, success, response) {
								if (success) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if (!Ext.isEmpty(response_obj.Diag_Code)) {
										if(	response_obj.Diag_Code.search(new RegExp("^[ST]", "i")) >= 0
											&& (Ext.isEmpty(date) || date>=xdate)
											&& win.action != 'view'
										) {
											field.setAllowBlank(false);
											field.enable();
										} else {
											field.setAllowBlank(true);
											field.disable();
											if(clear){
												field.setValue('');
											}
										}
									}
								}
							}
						});
					}
				});
			}
		}
	},
	disableSetMedicalCareFormType: false,
	filterMedicalCareFormType: function() {
		var base_form = this.findById('EvnPSEditForm').getForm(),
			EvnDirectionHTM_id = base_form.findField('EvnDirectionHTM_id').getValue(),
			EvnDirection_Num = base_form.findField('EvnDirection_Num').getValue(),
			EvnPS_HTMTicketNum = base_form.findField('EvnPS_HTMTicketNum').getValue();

			base_form.findField('MedicalCareFormType_id').getStore().clearFilter();

		switch ( getRegionNick() ) {
			case 'ekb':
				if ( Ext.isEmpty(base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick')) || !base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick').inlist([ 'plan' ]) ) {
					base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
						return (rec.get('MedicalCareFormType_Code') != 3);
					});
				}
			break;

			case 'penza':
			case 'perm':
				var first_evn_section_info = this.getEvnSectionInfo('first');

				base_form.findField('MedicalCareFormType_id').getStore().clearFilter();
				base_form.findField('MedicalCareFormType_id').lastQuery = '';
				base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
					if (rec.get('MedicalCareFormType_id') == 2) {
						// Неотложная
						if (base_form.findField('DirType_id').getValue() == 19) {
							return false;
						} else {
							return true;
						}
					} else if (rec.get('MedicalCareFormType_id') == 1) {
						// Экстренная
						if (first_evn_section_info && first_evn_section_info.LpuUnitType_SysNick && first_evn_section_info.LpuUnitType_SysNick.inlist(['dstac','hstac'])) {
							return false;
						} else if ( ! Ext.isEmpty(EvnDirection_Num) &&  Ext.isEmpty(EvnDirectionHTM_id) && Ext.isEmpty(EvnPS_HTMTicketNum)) // Если есть направление, но не на ВМП
						{
							return false;
						} else {
							return true;
						}
					} else {
						return true;
					}
				});

				// если значения больше нет в сторе очищаем поле
				var MedicalCareFormType_id = base_form.findField('MedicalCareFormType_id').getValue(),
					index = base_form.findField('MedicalCareFormType_id').getStore().findBy(function(rec) {
						if ( rec.get('MedicalCareFormType_id') == MedicalCareFormType_id ) {
							return true;
						}
						else {
							return false;
						}
					});
				if (index < 0) {
					base_form.findField('MedicalCareFormType_id').clearValue();
					base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());
				}
			break;

			case 'krym':
				if (
					Ext.isEmpty(base_form.findField('LpuSection_eid').getFieldValue('LpuUnitType_SysNick'))
					|| !base_form.findField('LpuSection_eid').getFieldValue('LpuUnitType_SysNick').inlist([ 'dstac', 'hstac' ])
				) {
					base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
						return (rec.get('MedicalCareFormType_Code') != 1);
					});
				}
			break;
		}
	},
	setMedicalCareFormType: function() {
		var base_form = this.findById('EvnPSEditForm').getForm(),
			EvnDirectionHTM_id = base_form.findField('EvnDirectionHTM_id').getValue(),
			EvnPS_HTMTicketNum = base_form.findField('EvnPS_HTMTicketNum').getValue(),
			EvnDirection_Num = base_form.findField('EvnDirection_Num').getValue(),
			MedicalCareFormType = base_form.findField('MedicalCareFormType_id'),
			PrehospType_SysNick = base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick');

		if (this.disableSetMedicalCareFormType || this.isProcessLoadForm) {
			return;
		}

		switch(getRegionNick()) {
			case 'perm':
				base_form.findField('MedicalCareFormType_id').getStore().clearFilter();
				var diag_pid = base_form.findField('Diag_pid').getValue();
				var is_Z_W57 = false;
				if(diag_pid){
					var rec = base_form.findField('Diag_pid').getStore().getById(diag_pid);
					if(rec && rec.get('Diag_Code'))
					{
						var diag_code = rec.get('Diag_Code');
						if(diag_code.substr(0,1) == 'Z' || diag_code.substr(0,3) == 'W57')
							is_Z_W57 = true;
					}
				}
				if (!Ext.isEmpty(base_form.findField('PrehospArrive_id').getFieldValue('PrehospArrive_SysNick')) && base_form.findField('PrehospArrive_id').getFieldValue('PrehospArrive_SysNick').inlist(['quick', 'evak', 'avia', 'nmedp'])) {
					// Если поле "Кем доставлен" = 2. Скорая помощь или 3. Эвакопункт или 4. Санавиация или Неотложная медицинская помощь
					if (Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue())) {
						// и нет отказа от госпитализации в приемном, то экстренная
						base_form.findField('MedicalCareFormType_id').setValue(1);
					} else {
						// Иначе если есть отказ от госпитализации в приемном, то неотложная
						base_form.findField('MedicalCareFormType_id').setValue(2);
					}
				} else if (!Ext.isEmpty(base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick')) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick').inlist(['oper', 'extreme'])) {
					// Иначе если поле "Тип госпитализации" = 2. Экстренно или 3. Экстренно по хирургическим показаниям, то экстренная
					// Иначе если есть входящее направление и поле "Тип направления" = На госпитализацию экстренную, то экстренная (поле Тип госпитализации зависит от этого)
					base_form.findField('MedicalCareFormType_id').setValue(1);
				} else if(!Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) && is_Z_W57) {
					base_form.findField('MedicalCareFormType_id').setValue(3);
					base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
						return rec.get('MedicalCareFormType_id') == 3;
					});
				} else {
					// Иначе плановая
					base_form.findField('MedicalCareFormType_id').setValue(3);
				}
				break;
			case 'krym':
				// @task https://redmine.swan.perm.ru/issues/109975
				if (
					!Ext.isEmpty(base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick'))
					&& !base_form.findField('MedicalCareFormType_id').disabled
				) {
					this.filterMedicalCareFormType();

					if (
						Ext.isEmpty(base_form.findField('LpuSection_eid').getFieldValue('LpuUnitType_SysNick'))
						|| !base_form.findField('LpuSection_eid').getFieldValue('LpuUnitType_SysNick').inlist([ 'dstac', 'hstac', 'pstac' ])
					) {
						switch ( base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick') ) {
							// Если поле "Тип госпитализации" = «1. Планово», то «Плановая».
							case 'plan':
								base_form.findField('MedicalCareFormType_id').setValue(3);
							break;

							// Если поле "Тип госпитализации" = «2. Экстренно», то «Неотложная»
							case 'extreme':
							// Если поле "Тип госпитализации" = «3. Экстренно по хирургическим показания», то «Неотложная»
							case 'oper':
								base_form.findField('MedicalCareFormType_id').setValue(2);
							break;
						}
					}
				}
				break;
			case 'kareliya':
				// Если отказ в приемном отделении, то 2 «Неотложная»;
				if (!Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue())) {
					base_form.findField('MedicalCareFormType_id').setValue(2);
				} else if (!Ext.isEmpty(base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick')) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick').inlist(['plan'])) {
					// Иначе если Тип госпитализации = 1. Планово, то 3 «Плановая»;
					base_form.findField('MedicalCareFormType_id').setValue(3);
				} else if (!Ext.isEmpty(base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick')) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick').inlist(['oper', 'extreme'])) {
					// Иначе если Тип госпитализации = 2. Экстренно или 3. Экстренно по хирургическим показаниям, то 1 «Экстренная».
					base_form.findField('MedicalCareFormType_id').setValue(1);
				}
				break;
			case 'penza':
				this.filterMedicalCareFormType();
				if (Ext.isEmpty(EvnDirection_Num)) // Если нет направления
				{
					MedicalCareFormType.setValue(1); // Экстренная
				} else if (!Ext.isEmpty(PrehospType_SysNick) && PrehospType_SysNick.inlist(['plan'])) {
					// Если Тип госпитализации = 1. Планово, то 3 «Плановая»;
					MedicalCareFormType.setValue(3);
				} else if (!Ext.isEmpty(PrehospType_SysNick) && PrehospType_SysNick.inlist(['oper', 'extreme']) ) {
					// Иначе если Тип госпитализации = 2. Экстренно или 3. Экстренно по хирургическим показаниям
					if ( ! Ext.isEmpty(EvnDirection_Num) && (Ext.isEmpty(EvnDirectionHTM_id) && Ext.isEmpty(EvnPS_HTMTicketNum)) ) // Если есть направление, но не на ВМП
					{
						MedicalCareFormType.setValue(2); // Неотложная
						break;
					}

					var first_evn_section_info = this.getEvnSectionInfo('first');
					if (first_evn_section_info && first_evn_section_info.LpuUnitType_SysNick && first_evn_section_info.LpuUnitType_SysNick.inlist(['stac', 'priem']) && (! Ext.isEmpty(EvnDirectionHTM_id) || ! Ext.isEmpty(EvnPS_HTMTicketNum)) ) {
						// •	Иначе, если Тип госпитализации = 2. Экстренно или 3. Экстренно по хирургическим показаниям
						// И отделение из группы отделений с типом «2. Круглосуточный стационар» или «Приемные» и #128738 есть направление на ВМП, то «Экстренная».
						base_form.findField('MedicalCareFormType_id').setValue(1);
					}
				}
				break;
			case 'ufa':
				if (!Ext.isEmpty(base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick')) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick').inlist(['oper', 'extreme'])) {
					// Если поле "Тип госпитализации" = 2. Экстренно или 3. Экстренно по хирургическим показаниям
					if (Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue())) {
						// и нет отказа от госпитализации в приемном, то экстренная
						base_form.findField('MedicalCareFormType_id').setValue(1);
					} else {
						// Иначе если есть отказ от госпитализации в приемном, то Неотложная;
						base_form.findField('MedicalCareFormType_id').setValue(2);
					}
				} else {
					// Иначе Плановая.
					base_form.findField('MedicalCareFormType_id').setValue(3);
				}
				break;
			case 'buryatiya':
				if ( base_form.findField('LeaveType_prmid').getFieldValue('LeaveType_SysNick') == 'otk' ) {
					base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 2);
				}
				else if ( base_form.findField('LeaveType_prmid').getFieldValue('LeaveType_SysNick') == 'osmpp' && !Ext.isEmpty(base_form.findField('PrehospType_id').getValue()) ) {
					if ( base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick') == 'plan' ) {
						base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 3);
					}
					else {
						base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 1);
					}
				}
				break;
			case 'ekb':
				// @task https://redmine.swan.perm.ru/issues/103200
				this.filterMedicalCareFormType();
				if (!Ext.isEmpty(base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick')) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick').inlist(['plan'])) {
					base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 3);
				} else {
					// Если поле "Тип госпитализации" = 2. Экстренно или 3. Экстренно по хирургическим показаниям или поле не заполнено
					base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 1);
				}
				break;
		}
	},
	loadMedStaffFactDidCombo: function() {
    	if (getRegionNick().inlist([ 'ekb', 'perm', 'buryatiya' ])) {
			var
				base_form = this.findById('EvnPSEditForm').getForm(),
				MedStaffFact_did = base_form.findField('MedStaffFact_did').getValue(),
				Org_did = base_form.findField('Org_did').getValue(),
				PrehospDirect_Code = base_form.findField('PrehospDirect_id').getFieldValue('PrehospDirect_Code');

			base_form.findField('MedStaffFact_did').getStore().removeAll();
			base_form.findField('MedStaffFact_did').clearValue();
			base_form.findField('MedStaffFact_did').fireEvent('change', base_form.findField('MedStaffFact_did'), base_form.findField('MedStaffFact_did').getValue());

			var callback = function() {
				if ( !Ext.isEmpty(MedStaffFact_did) ) {
					var index = base_form.findField('MedStaffFact_did').getStore().findBy(function (rec) {
						return (rec.get('MedStaffFact_id') == MedStaffFact_did);
					});

					if ( index >= 0 ) {
						base_form.findField('MedStaffFact_did').setValue(MedStaffFact_did);
					}
					else {
						base_form.findField('MedStaffFact_did').clearValue();
					}

					base_form.findField('MedStaffFact_did').fireEvent('change', base_form.findField('MedStaffFact_did'), base_form.findField('MedStaffFact_did').getValue());
				}
			}

			if ( PrehospDirect_Code == 1 ) {
				setMedStaffFactGlobalStoreFilter({
					Lpu_id: getGlobalOptions().lpu_id
				});
				base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
				callback();

				if ( !Ext.isEmpty(MedStaffFact_did) && Ext.isEmpty(base_form.findField('MedStaffFact_did').getValue()) ) {
					base_form.findField('MedStaffFact_did').getStore().load({
						params: {
							MedStaffFact_id: MedStaffFact_did,
							mode: 'combo'
						},
						callback: callback
					});
				}
			}
			else if ( PrehospDirect_Code == 2 && !Ext.isEmpty(Org_did) ) {
				base_form.findField('MedStaffFact_did').getStore().load({
					params: {
						Org_id: Org_did,
						withDloCodeOnly: (getRegionNick() == 'ekb' ? 1 : 0),
						mode: 'combo'
					},
					callback: callback
				});
			}
		}
	},
	getEvnSectionIndexNums: function() {
    	if (!getRegionNick().inlist(['astra', 'perm', 'penza'])) {
    		return;
		}

		var win = this;
		var grid = this.findById('EPSEF_EvnSectionGrid');
		var base_form = this.findById('EvnPSEditForm').getForm();
		var EvnPS_id = base_form.findField('EvnPS_id').getValue();

		if (!Ext.isEmpty(EvnPS_id)) {
			win.getLoadMask('Получение группировки движений').show();
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=getEvnSectionIndexNum',
				params: {
					EvnSection_pid: EvnPS_id
				},
				callback:function (options, success, response) {
					win.getLoadMask().hide();

					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj) {
							for (var i in response_obj) {
								if (response_obj[i].EvnSection_id) {
									grid.getStore().each(function(rec) {
										if (rec.get('EvnSection_id') == response_obj[i].EvnSection_id) {
											rec.set('EvnSection_IndexNum', response_obj[i].EvnSection_IndexNum);
											rec.set('EvnSection_IsMultiKSG', response_obj[i].EvnSection_IsMultiKSG);
											rec.set('EvnSection_KSG', response_obj[i].EvnSection_KSG);
											rec.commit();
										}
									});
								}
							}
						}
					}
				}
			});
		}
	},
	setEvnSectionIndexNum: function(indexNum) {
    	var win = this;
		var grid = this.findById('EPSEF_EvnSectionGrid');
		var selected_record = grid.getSelectionModel().getSelected();

		if (selected_record && selected_record.get('EvnSection_id')) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						win.getLoadMask('Установка группы').show();
						Ext.Ajax.request({
							url: '/?c=EvnSection&m=setEvnSectionIndexNum',
							params: {
								EvnSection_id: selected_record.get('EvnSection_id'),
								EvnSection_IndexNum: indexNum
							},
							callback:function (options, success, response) {
								win.getLoadMask().hide();

								if (success) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if (response_obj.success) {
										selected_record.set('EvnSection_IndexNum', indexNum);
										selected_record.commit();

										if (getRegionNick() == 'penza') {
											// могли измениться КСГ, получаем их вместе номерами групп
											win.getEvnSectionIndexNums();
										}
									}
								}
							}
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Сменить группу для выбранного движения?',
				title: 'Вопрос'
			});
		}
	},
	setCovidFieldsAllowBlank: function() {
		if (getRegionNick() != 'msk') {
			return;
		}
		
		var base_form = this.findById('EvnPSEditForm').getForm();
		var LpuSection_eid = base_form.findField('LpuSection_eid').getValue();
		var Diag_Code = base_form.findField('Diag_pid').getFieldValue('Diag_Code');
		if (!Ext.isEmpty(LpuSection_eid) && LpuSection_eid > 0) {
			base_form.findField('RepositoryObserv_BreathRate').setAllowBlank(false);
			base_form.findField('RepositoryObserv_Systolic').setAllowBlank(false);
			base_form.findField('RepositoryObserv_Diastolic').setAllowBlank(false);
			base_form.findField('RepositoryObserv_Height').setAllowBlank(false);
			base_form.findField('RepositoryObserv_Weight').setAllowBlank(false);
			base_form.findField('RepositoryObserv_TemperatureFrom').setAllowBlank(false);
			if (
				!Ext.isEmpty(Diag_Code)
				&& (
					Diag_Code.inlist(['B34.2', 'B33.8', 'Z03.8', 'Z22.8', 'Z20.8', 'U07.1', 'U07.2'])
					|| (Diag_Code.substr(0, 3) >= 'J00' && Diag_Code.substr(0, 3) <= 'J99')
				)
			) {
				base_form.findField('RepositoryObserv_SpO2').setAllowBlank(false);
			} else {
				base_form.findField('RepositoryObserv_SpO2').setAllowBlank(true);
			}
			base_form.findField('CovidType_id').setAllowBlank(false);
			base_form.findField('DiagConfirmType_id').setAllowBlank(false);
		} else {
			base_form.findField('RepositoryObserv_BreathRate').setAllowBlank(true);
			base_form.findField('RepositoryObserv_Systolic').setAllowBlank(true);
			base_form.findField('RepositoryObserv_Diastolic').setAllowBlank(true);
			base_form.findField('RepositoryObserv_Height').setAllowBlank(true);
			base_form.findField('RepositoryObserv_Weight').setAllowBlank(true);
			base_form.findField('RepositoryObserv_TemperatureFrom').setAllowBlank(true);
			base_form.findField('RepositoryObserv_SpO2').setAllowBlank(true);
			base_form.findField('CovidType_id').setAllowBlank(true);
			base_form.findField('DiagConfirmType_id').setAllowBlank(true);
		}
	},
	getCovidTypeId: function() {
		var base_form = this.findById('EvnPSEditForm').getForm();
    	var CovidType_id = base_form.findField('CovidType_id').getValue();
    	if (CovidType_id != 3) {
			// Если в одном из полей: «Диагноз напр. учр-я», «Диагноз прием. отд-я» или разделов «Сопутствующие диагнозы направившего учреждения», «Сопутствующие диагнозы приемного отделения» установлен диагноз: «U07.1 COVID-19, вирус идентифицирован»
			// То, по умолчанию заполнено «3. Положительный результат»
			// Иначе, по умолчанию не заполнено
			var Diag_pCode = base_form.findField('Diag_pid').getFieldValue('Diag_Code');
			var Diag_dCode = base_form.findField('Diag_did').getFieldValue('Diag_Code');
			if (
				(!Ext.isEmpty(Diag_pCode) && Diag_pCode.inlist(['U07.1', 'U07.2']))
				|| (!Ext.isEmpty(Diag_dCode) && Diag_dCode.inlist(['U07.1', 'U07.2']))
			) {
				CovidType_id = 3;
			} else {
				this.findById('EPSEF_EvnDiagPSHospGrid').getStore().each(function(rec) {
					if (!Ext.isEmpty(rec.get('Diag_Code')) && rec.get('Diag_Code').inlist(['U07.1', 'U07.2'])) {
						CovidType_id = 3;
					}
				});
				this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().each(function(rec) {
					if (!Ext.isEmpty(rec.get('Diag_Code')) && rec.get('Diag_Code').inlist(['U07.1', 'U07.2'])) {
						CovidType_id = 3;
					}
				});
			}
		}
    	return CovidType_id;
	},
	delDocsView: false,
	show: function() {
		sw.Promed.swEvnPSEditWindow.superclass.show.apply(this, arguments);

		var thisWin = this;

		this.onPersonChange = Ext.emptyFn;
		this.priem = false;
		this.evnStickAction = null;
		this.lastLpuSectionProfileParams = null;
		this.disableSetMedicalCareFormType = false;

		if ( this.firstRun == true ) {
			this.findById('EPSEF_HospitalisationPanel').collapse();
			this.findById('EPSEF_DirectDiagPanel').collapse();
			this.findById('EPSEF_AdmitDepartPanel').collapse();
			this.findById('EPSEF_AdmitDiagPanel').collapse();
			this.findById('EPSEF_PriemLeavePanel').collapse();
			this.findById('EPSEF_PrehospWaifPanel').collapse();
			this.findById('EPSEF_CostPrintPanel').collapse();
			this.findById('EPSEF_EvnSectionPanel').collapse();
			this.findById('EPSEF_EvnStickPanel').collapse();
			this.findById('EPSEF_EvnUslugaPanel').collapse();
			this.findById('EPSEF_EvnDrugPanel').collapse();
			this.findById('EPSEF_EvnReanimatPeriodPanel').collapse();//BOB - 04.08.2018    
		}

		this.findById('EPSEF_HospitalisationPanel').hide();
		this.findById('EPSEF_DirectDiagPanel').hide();
		this.findById('EPSEF_AdmitDepartPanel').hide();
		this.findById('EPSEF_AdmitDiagPanel').hide();
		this.findById('EPSEF_PriemLeavePanel').hide();
		this.findById('EPSEF_PrehospWaifPanel').hide();
		this.findById('EPSEF_EvnSectionPanel').hide();
		this.findById('EPSEF_EvnStickPanel').hide();
		this.findById('EPSEF_EvnUslugaPanel').hide();
		this.findById('EPSEF_EvnDrugPanel').hide();
		this.findById('EPSEF_EvnReanimatPeriodPanel').hide(); //BOB - 04.08.2018

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.findById('EvnPSEditForm').getForm(),
				_this = this;
				base_form.findField('LeaveType_fedid').on('change', function (combo, newValue) {
				sw.Promed.EvnPL.filterFedResultDeseaseType({
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				})
			});
			base_form.findField('ResultDeseaseType_fedid').on('change', function (combo, newValue) {
				sw.Promed.EvnPL.filterFedLeaveType({
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
			});
		base_form.reset();
		base_form.findField('EvnPS_RepFlag').hideContainer();
		base_form.findField('EvnPS_OutcomeDate').fireEvent('change', base_form.findField('EvnPS_OutcomeDate'), base_form.findField('EvnPS_OutcomeDate').getValue());
		base_form.findField('LpuSection_eid').fireEvent('change', base_form.findField('LpuSection_eid'), base_form.findField('LpuSection_eid').getValue());

		base_form.findField('EvnPS_HTMBegDate').fireEvent('change', base_form.findField('EvnPS_HTMBegDate'), base_form.findField('EvnPS_HTMBegDate').getValue());
		base_form.findField('EvnPS_HTMTicketNum').fireEvent('change', base_form.findField('EvnPS_HTMTicketNum'), base_form.findField('EvnPS_HTMTicketNum').getValue());

		if ( getRegionNick().inlist([ 'ekb', 'perm' ]) ) {
			base_form.findField('UslugaComplex_id').clearBaseParams();
			base_form.findField('UslugaComplex_id').getStore().baseParams.filterByLpuSection = 1;
			this.lastUslugaComplexParams = null;
			this.blockUslugaComplexReload = false;

			if ( getRegionNick().inlist([ 'ekb' ]) ) {
				base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300,301]);
			}

			if ( getRegionNick().inlist([ 'perm' ]) ) {
				base_form.findField('UslugaComplex_id').setVizitCodeFilters({
					isStac: true
				});
				base_form.findField('UslugaComplex_id').getStore().baseParams.isEvnPS = 1;

				base_form.findField('ResultDeseaseType_fedid').getStore().filterBy(function(rec) {
					return (rec.get('ResultDeseaseType_Code').toString().substr(0, 1) == '3');
				});
				base_form.findField('LeaveType_fedid').getStore().filterBy(function(rec) {
						return (rec.get('LeaveType_USLOV') == '3');
				});
			}
		}
		
		if ( getRegionNick().inlist([ 'buryatiya', 'pskov' ]) ) {
			base_form.findField('LeaveType_prmid').getStore().clearFilter();
			base_form.findField('LeaveType_prmid').getStore().lastQuery = '';
			base_form.findField('LeaveType_prmid').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('LeaveType_Code')) && rec.get('LeaveType_Code').toString().substr(0, 1) == '6');
			});
			base_form.findField('LeaveType_prmid').fireEvent('change', base_form.findField('LeaveType_prmid'), null);

			if ( getRegionNick().inlist([ 'buryatiya' ]) && base_form.findField('UslugaComplex_id').getStore().getCount() == 0 ) {
				base_form.findField('UslugaComplex_id').setUslugaComplexCodeList([ '021613', '061129', '161129' ]);
			}
		}
		else {
			base_form.findField('LeaveType_prmid').setContainerVisible(false);
		}

		base_form.findField('LpuSectionWard_id').setContainerVisible(false);
		base_form.findField('LpuSectionBedProfileLink_id').setContainerVisible(false);

		if ( getRegionNick().inlist([ 'krym', 'penza' ]) ) {
			this.filterMedicalCareFormType();
		}

		if(!getRegionNick().inlist([ 'kz' ])) {
			thisWin.findById(thisWin.id + '_TltPanel').setVisible(false);
		}

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.gridAccess = 'view';
		this.isCopy = false;
		this.childPS = false;
		this.ChildLpuSection_id = null;
		this.onHide = Ext.emptyFn;
		this.params = new Object();
		this.lookChange = new Object();
		this.formLoaded = false;

		base_form.findField('Diag_pid').filterDate = null;
		base_form.findField('Diag_did').filterDate = null;

		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_setDate').disable();
		base_form.findField('LpuSection_did').disable();
		base_form.findField('Org_did').disable();
		base_form.findField('PrehospTrauma_id').setAllowBlank(true);
		base_form.findField('Org_did').fireEvent('change', base_form.findField('Org_did'), base_form.findField('Org_did').getValue());

		if ( !arguments[0] ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры');
			return false;
		}

		if (arguments[0].onPersonChange) {
			this.onPersonChange = arguments[0].onPersonChange;
		}

		if (arguments[0].delDocsView) {
			this.delDocsView = arguments[0].delDocsView;
		}

		this.form_mode = arguments[0].form_mode || null;
		this.onChangeLpuSectionWard = arguments[0].onChangeLpuSectionWard || null;

		if (arguments[0].childPS) {
			//редактируется КВС ребенка
			this.BirthWeight = null;
			this.PersonWeight_text = null;
			this.Okei_id = null;
			this.BirthHeight = null;
			this.childPS = true;
			this.findById(this.id  + 'PrehospType_id').getStore().load();//todo разобраться, почему getDataAll не вызывается для второго экземпляра формы и убрать явную загрузку
			if (arguments[0].opener){//передано кто открыл
				this.opener = arguments[0].opener;
			}
			if (arguments[0].ChildTermType_id) {
				this.ChildTermType_id = arguments[0].ChildTermType_id;
			} else {
				this.ChildTermType_id = null;
			}
			if (arguments[0].BirthSpecStac_CountChild) {
				this.BirthSpecStac_CountChild = arguments[0].BirthSpecStac_CountChild;
			} else {
				this.BirthSpecStac_CountChild = null;
			}
			if (arguments[0].PersonChild_IsAidsMother) {
				this.PersonChild_IsAidsMother = arguments[0].PersonChild_IsAidsMother;
			} else {
				this.PersonChild_IsAidsMother = null;
			}
		}

		base_form.setValues(arguments[0]);
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		this.deleteOnCancel = false;
		this.userMedStaffFact = null;

		if ( arguments[0].deleteOnCancel ) {
			this.deleteOnCancel = arguments[0].deleteOnCancel;
		}

		if ( arguments[0].EvnLeave_setDate ) {
			this.params.EvnLeave_setDate = arguments[0].EvnLeave_setDate;
		}

		if ( arguments[0].EvnLeave_UKL ) {
			this.params.EvnLeave_UKL = arguments[0].EvnLeave_UKL;
		}

		if ( arguments[0].isCopy ) {
			this.isCopy = arguments[0].isCopy;
		}

		if ( arguments[0].LeaveCause_id ) {
			this.params.LeaveCause_id = arguments[0].LeaveCause_id;
		}

		if ( arguments[0].LeaveType_id ) {
			this.params.LeaveType_id = arguments[0].LeaveType_id;
		}

		if ( arguments[0].LeaveTypeFed_id ) {
			this.params.LeaveTypeFed_id = arguments[0].LeaveTypeFed_id;
		}

		if ( arguments[0].LpuSection_id ) {
			this.params.LpuSection_id = arguments[0].LpuSection_id;
		}

		if ( arguments[0].MedPersonal_id ) {
			this.params.MedPersonal_id = arguments[0].MedPersonal_id;
		}

		if ( arguments[0].MedStaffFact_id ) {
			this.params.MedStaffFact_id = arguments[0].MedStaffFact_id;
		}
		else {
			this.params.MedStaffFact_id = null;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].ResultDesease_id ) {
			this.params.ResultDesease_id = arguments[0].ResultDesease_id;
		}

		if ( arguments[0].TariffClass_id ) {
			this.params.TariffClass_id = arguments[0].TariffClass_id;
		}

		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		} else {
			this.ARMType = null;
		}
		//BOB - 04.09.2018
		if ( arguments[0].userMedStaffFact) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		if ( arguments[0].userMedStaffFact &&  arguments[0].userMedStaffFact.ARMType_id) {
			this.ARMType_id = arguments[0].userMedStaffFact.ARMType_id;
		}

		if ( arguments[0].from ) {
			this.from = arguments[0].from;
		} else {
			this.from = null;
		}

		this.lookChange.PrehospType_id = null;
		this.lookChange.EvnDirection_Num = null;
		this.lookChange.EvnDirection_setDate = null;
		this.lookChange.PrehospDirect_id = null;
		this.lookChange.PayType_id = null;
		this.updateLookChange();

		this.ed_record = null;
		if ( typeof arguments[0].EvnDirection == 'object' )
		{
			this.ed_record = arguments[0].EvnDirection;
		}
		this.EvnDirectionData = arguments[0].EvnDirectionData || null;
		base_form.findField('Diag_pid').setAllowBlank(true);
		if ( this.action == 'add' ) {
			this.findById('EPSEF_DirectDiagPanel').isLoaded = true;
			this.findById('EPSEF_AdmitDiagPanel').isLoaded = true;
			this.findById('EPSEF_PriemLeavePanel').isLoaded = true;
			this.findById('EPSEF_PrehospWaifPanel').isLoaded = true;
			this.findById('EPSEF_EvnSectionPanel').isLoaded = true;
			this.findById('EPSEF_EvnStickPanel').isLoaded = true;
			this.findById('EPSEF_EvnUslugaPanel').isLoaded = true;
			this.findById('EPSEF_EvnDrugPanel').isLoaded = true;
			this.findById('EPSEF_EvnReanimatPeriodPanel').isLoaded = true; // BOB - 04.09.2018
		}
		else {
			this.findById('EPSEF_DirectDiagPanel').isLoaded = false;
			this.findById('EPSEF_AdmitDiagPanel').isLoaded = false;
			this.findById('EPSEF_PriemLeavePanel').isLoaded = false;
			this.findById('EPSEF_PrehospWaifPanel').isLoaded = false;
			this.findById('EPSEF_EvnSectionPanel').isLoaded = false;
			this.findById('EPSEF_EvnStickPanel').isLoaded = false;
			this.findById('EPSEF_EvnUslugaPanel').isLoaded = false;
			this.findById('EPSEF_EvnDrugPanel').isLoaded = false;
			this.findById('EPSEF_EvnReanimatPeriodPanel').isLoaded = false; // BOB - 04.09.2018
		}

		this.findById('EPSEF_EvnDiagPSHospGrid').getStore().removeAll();
		this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSEF_EvnDiagPSRecepGrid').getStore().removeAll();
		this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSEF_EvnSectionGrid').getStore().removeAll();
		this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[3].disable();
		this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[6].disable();

		this.findById('EPSEF_EvnStickGrid').getStore().removeAll();
		this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[3].disable();
		this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[4].disable();
		this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[5].disable();

		this.findById('EPSEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSEF_EvnDrugGrid').getStore().removeAll();
		this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[3].disable();

		//BOB - 04.09.2018
		this.findById('EPSEF_EvnReanimatPeriodGrid').getStore().removeAll();
		this.findById('EPSEF_EvnReanimatPeriodGrid').getTopToolbar().items.items[0].disable();
		this.findById('EPSEF_EvnReanimatPeriodGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSEF_EvnReanimatPeriodGrid').getTopToolbar().items.items[2].disable();

		if (getRegionNick() === 'ekb' && this.action.inlist(['add', 'edit']))
		{
			this.ExtDirButton.show();
		}

		// получение даты последней флюорографии
		if (getRegionNick() == 'msk' && this.action == 'add') {
			Ext.Ajax.request({
				url: '/?c=EvnPS&m=getLastFluorographyDate',
				params: {
					Person_id: base_form.findField('Person_id').getValue()
				},
				success: function(response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj[0].FluorographyDate) {
						base_form.findField('RepositoryObserv_FluorographyDate').setRawValue(response_obj[0].FluorographyDate);
					}
					
				}
			});
		}

		// Проверяем возможность редактирования документа
		if ( this.action == 'edit' ) {

			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
				},
				params: {
					Evn_id: base_form.findField('EvnPS_id').getValue(),
					from: _this.from,
					MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && typeof sw.Promed.MedStaffFactByUser.current == 'object' && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null),
					ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && typeof sw.Promed.MedStaffFactByUser.current == 'object' && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType) ? sw.Promed.MedStaffFactByUser.current.ARMType : null)
				},
				success: function(response, options) {
					if (!Ext.isEmpty(response.responseText)) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success == false ) {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при загрузке данных формы');
							_this.action = 'view';
							if(getRegionNick() == 'astra') {
								_this.gridAccess = 'full';
							}
						}

						if (response_obj.Alert_Msg) {
							sw.swMsg.alert(langs('Внимание'), response_obj.Alert_Msg);
						}
					}
					else {
						_this.gridAccess = 'full';
					}

					thisWin.onShow();
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		}
		else {
			if ( this.action == 'add' ) {
				this.gridAccess = 'full';
			}

			thisWin.onShow();
		}
	},
	onShow: function() {
		var thisWin = this;

		this.checkForCostPrintPanel();
		this.DiagSSZStore.load();

		var
			base_form = this.findById('EvnPSEditForm').getForm(),
			_this = this;

		var isUfa = (getRegionNick() == 'ufa');
		var diag_d_combo = base_form.findField('Diag_did');
		var diag_p_combo = base_form.findField('Diag_pid');
		var diag_e_combo = base_form.findField('Diag_eid');
		var lpu_section_dir_combo = base_form.findField('LpuSection_did');
		var lpu_section_rec_combo = base_form.findField('LpuSection_pid');
		var lpu_section_hosp_combo = base_form.findField('LpuSection_eid');
		var med_staff_fact_rec_combo = base_form.findField('MedStaffFact_pid');
		var org_combo = base_form.findField('Org_did');
		var prehosp_arrive_combo = base_form.findField('PrehospArrive_id');
		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var prehosp_trauma_combo = base_form.findField('PrehospTrauma_id');
		var prehosp_type_combo = base_form.findField('PrehospType_id');
		var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
		var okei_combo = base_form.findField('Okei_id');
		var cmp_call_card_combo = base_form.findField('CmpCallCard_id');

		var evn_ps_id = base_form.findField('EvnPS_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();

		okei_combo.setValue(100); // По умолчанию: час

		setCurrentDateTime({
			callback: Ext.emptyFn,
			dateField: base_form.findField('EvnPS_setDate'),
			loadMask: false,
			setDate: false,
			setDateMaxValue: true,
			windowId: this.id
		});

		var diag_eid = diag_e_combo.getValue();
		if(getRegionNick().inlist(['vologda', 'ufa'])) {
			this.findById('TraumaCircumEvnPS_Name').setVisible((diag_eid == "") ? false : true);
		}
		base_form.findField('EvnPS_setDate').setMinValue(undefined);

		setLpuSectionGlobalStoreFilter();

		lpu_section_dir_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		setMedStaffFactGlobalStoreFilter({
			EvnClass_SysNick: 'EvnPS',
			isStac:true
		});
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		var is_waif_combo = base_form.findField('EvnPS_IsWaif');
		is_waif_combo.setAllowBlank(true);

		this.refreshFieldsVisibility(['EvnPS_HTMTicketNum']);
		this.setSpecificsPanelVisibility();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_HOSP_EPSADD);
				this.enableEdit(true);

				lpu_section_hosp_combo.getStore().removeAll();

				base_form.findField('UslugaComplex_id').setPersonId(base_form.findField('Person_id').getValue());
				base_form.findField('PrehospWaifRefuseCause_id').fireEvent('change', base_form.findField('PrehospWaifRefuseCause_id'), base_form.findField('PrehospWaifRefuseCause_id').getValue());
				base_form.findField('MedStaffFact_did').fireEvent('change', base_form.findField('MedStaffFact_did'), base_form.findField('MedStaffFact_did').getValue());

				this.findById('EPSEF_PersonInformationFrame').setTitle('...');
				this.findById('EPSEF_PersonInformationFrame').clearPersonChangeParams();
				this.findById('EPSEF_PersonInformationFrame').load({
					callback: function() {
						var
							// Возраст пациента:
							age = this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Age'),
							// Поле "Дееспособен":
							isActive_combo = base_form.findField('EvnPS_IsActive');

						this.findById('EPSEF_PersonInformationFrame').setPersonTitle();

						base_form.findField('EvnPS_setDate').setMinValue(this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'));

						if (age < 18)
						{
							this.findById('EPSEF_PrehospWaifPanel').show();
							is_waif_combo.setAllowBlank(false);
							is_waif_combo.setValue(1);
							is_waif_combo.fireEvent('change', is_waif_combo,1, null);
						}

						// Заполняем поле "Дееспособен" значением "Нет", если пациенту меньше 18 лет, и
						// значением "Да" в противном случае:
						if (isActive_combo)
							isActive_combo.setValue(age < 18 ? 1 : 2);

						this.setMKB();

						if (getRegionNick() == 'ekb' && this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2 ) {
							base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'bud');
							base_form.findField('PayType_id').disable();
						}
					}.createDelegate(this),
					onExpand: true,
					Person_id: person_id,
					Server_id: server_id,
					Evn_setDT:Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(),'d.m.Y')
					
				});
				this.findById('EPSEF_HospitalisationPanel').show();
				this.findById('EPSEF_DirectDiagPanel').show();
				this.findById('EPSEF_AdmitDepartPanel').show();
				this.findById('EPSEF_AdmitDiagPanel').show();
				this.findById('EPSEF_PriemLeavePanel').show();
				//this.findById('EPSEF_PrehospWaifPanel').show();
				this.findById('EPSEF_EvnSectionPanel').show();
				this.findById('EPSEF_EvnStickPanel').show();
				this.findById('EPSEF_EvnUslugaPanel').show();
				this.findById('EPSEF_EvnDrugPanel').show();
				this.findById('EPSEF_EvnReanimatPeriodPanel').show(); //BOB - 04.09.2018
				if ( this.firstRun == true ) {
					this.findById('EPSEF_HospitalisationPanel').expand();
					this.findById('EPSEF_AdmitDepartPanel').expand();
					this.findById('EPSEF_EvnSectionPanel').expand();
					this.firstRun = false;
				}
				this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[0].enable();
				this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[0].enable();
				this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[0].enable();
				this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[0].enable();
				this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
				this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[0].enable();

				LoadEmptyRow(this.findById('EPSEF_EvnDiagPSHospGrid'));
				LoadEmptyRow(this.findById('EPSEF_EvnDiagPSRecepGrid'));
				LoadEmptyRow(this.findById('EPSEF_EvnSectionGrid'));
				LoadEmptyRow(this.findById('EPSEF_EvnStickGrid'));
				LoadEmptyRow(this.findById('EPSEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('EPSEF_EvnDrugGrid'));

				//Проверяем возможность пользователя редактировать ЛВН
				checkEvnStickEditable('EPSEF_EvnStickGrid', thisWin);

				var pt_id = (this.childPS)?1:2;
				if ( !prehosp_type_combo.getValue() ) {
					prehosp_type_combo.setValue(pt_id);
				}
				prehosp_type_combo.getStore().on('load', function(store, records, index){
					prehosp_type_combo.setValue(pt_id);
				});

				// #145312 Обязательность полей «№ направления» и «Дата направления» при типе госпитализации «1. Планово»
				if(getRegionNick().inlist(['ufa','pskov']) && prehosp_type_combo.getFieldValue('PrehospType_SysNick') == 'plan'){
					base_form.findField('EvnDirection_Num').setAllowBlank(false);
					base_form.findField('EvnDirection_setDate').setAllowBlank(false);
				}

				lpu_section_rec_combo.fireEvent('change', lpu_section_rec_combo, null);
				prehosp_arrive_combo.fireEvent('change', prehosp_arrive_combo, prehosp_arrive_combo.getValue());
				prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_combo.getValue());
				base_form.findField('EvnPS_IsUnlaw').fireEvent('change', base_form.findField('EvnPS_IsUnlaw'), base_form.findField('EvnPS_IsUnlaw').getValue());
				base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), base_form.findField('EvnPS_setDate').getValue());

				if ( this.ed_record ) {
					// добавляем по направлению из Журнал направлений на госпитализацию
					this.selectEvnDirection(this.ed_record);
				} else if ( this.EvnDirectionData ) {
					// добавляем по направлению из окна выбора направлений
					this.setDirection({
						PrehospDirect_id: sw.Promed.EvnDirectionAllPanel.calcPrehospDirectId(this.EvnDirectionData.Lpu_sid, this.EvnDirectionData.Org_did, this.EvnDirectionData.LpuSection_id, this.EvnDirectionData.EvnDirection_IsAuto),
						EvnDirection_id: this.EvnDirectionData.EvnDirection_id,
						EvnDirectionHTM_id: this.EvnDirectionData.EvnDirectionHTM_id,
						Diag_did: this.EvnDirectionData.Diag_did,
						EvnDirection_Num: this.EvnDirectionData.EvnDirection_Num,
						EvnDirection_setDate: this.EvnDirectionData.EvnDirection_setDate,
						LpuSection_id: this.EvnDirectionData.LpuSection_id,
						Org_did: this.EvnDirectionData.Org_did,
						Lpu_id: this.EvnDirectionData.Lpu_id,
						DirType_id: this.EvnDirectionData.DirType_id
					});
				} else {
					if (prehosp_direct_combo.getValue() == 1 || prehosp_direct_combo.getValue() == 2) {
						iswd_combo.setValue(2);
					} else {
						iswd_combo.setValue(1);
					}
					iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
				}
				loadMask.hide();

				//base_form.clearInvalid();

				//если уже выбрано приемное отделение то позволяем выбирать диагноз (refs #6987)
				var lpu_section_pid = lpu_section_rec_combo.getValue();
				if ( lpu_section_pid ) {
					diag_p_combo.enable();
				}
				
				if((isUfa && this.form_mode == 'dj_hosp') || this.form_mode == 'arm_stac_add_patient') {
					this.params.addEvnSection = true;
					this.params.MedStaffFact_id = (Ext.isEmpty(this.params.MedStaffFact_id) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser) && typeof sw.Promed.MedStaffFactByUser.current == 'object' && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null);
					setLpuSectionGlobalStoreFilter({
						onDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y')
					});
					lpu_section_hosp_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
					lpu_section_hosp_combo.setValue(this.params.LpuSection_id);
				}
				if(getRegionNick()=='ekb') this.checkZNO({action: this.action });

				this.getEvnPSNumber();
				if (getRegionNick().inlist['kareliya', 'penza']) {
					this.setMedicalCareFormTypeAllowBlank();
				}
				this.setMedicalCareFormType();
				this.setPrehospArriveAllowBlank();
				this.setDiagEidAllowBlank();
				this.refreshFieldsVisibility();
				this.checkVMPFieldEnabled();
				this.setSpecificsPanelVisibility();

				this.formLoaded = true;

				base_form.items.each(function(f){
					f.validate();
				});
				
				base_form.findField('Diag_spid').setContainerVisible(false);
				this.visibleBlockblockPediculos();
			break;

			case 'edit':
			case 'view':
				this.findById('EPSEF_HospitalisationPanel').show();
				this.findById('EPSEF_DirectDiagPanel').show();
				this.findById('EPSEF_AdmitDepartPanel').show();
				this.findById('EPSEF_AdmitDiagPanel').show();
				this.findById('EPSEF_PriemLeavePanel').show();
				//this.findById('EPSEF_PrehospWaifPanel').show();
				this.findById('EPSEF_EvnSectionPanel').show();
				this.findById('EPSEF_EvnStickPanel').show();
				this.findById('EPSEF_EvnUslugaPanel').show();
				this.findById('EPSEF_EvnDrugPanel').show();
				this.findById('EPSEF_EvnReanimatPeriodPanel').show(); //BOB - 04.09.2018
				lpu_section_hosp_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
				this.isProcessLoadForm = true;
				this.disableSetMedicalCareFormType = true;
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {this.hide();}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnPS_id: evn_ps_id,
						archiveRecord: _this.archiveRecord,
						delDocsView: _this.delDocsView ? 1 : 0
					},
					success: function(a,v,b) {
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if (v.result.data.childPS) {
							this.childPS = true;
						}

						var MedStaffFact_did = base_form.findField('MedStaffFact_did').getValue();

						if (getRegionNick() == 'ekb') {
							if (base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'bud') {
								base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([350]);
							} else {
								base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300, 301]);
							}
						}
						
						if (getRegionNick() == 'vologda') {
							if (!Ext.isEmpty(v.response) && !Ext.isEmpty(v.response.responseText)) {
								var response_obj = Ext.util.JSON.decode(v.response.responseText);
								if (response_obj.length > 0) {
									base_form.findField('VologdaFamilyContact_FIO').setValue(response_obj[0].VologdaFamilyContact_FIO);
									base_form.findField('VologdaFamilyContact_Phone').setValue(response_obj[0].VologdaFamilyContact_Phone);
									base_form.findField('FamilyContactPerson_id').setValue(response_obj[0].FamilyContactPerson_id);
								}
							}
						}

						var
							diag_pid = base_form.findField('Diag_pid').getValue(),
							LeaveType_fedid = base_form.findField('LeaveType_fedid').getValue(),
							LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue(),
							MedicalCareFormType_id = base_form.findField('MedicalCareFormType_id').getValue(),
							ResultDeseaseType_fedid = base_form.findField('ResultDeseaseType_fedid').getValue();

						base_form.findField('EvnPS_OutcomeDate').fireEvent('change', base_form.findField('EvnPS_OutcomeDate'), base_form.findField('EvnPS_OutcomeDate').getValue());

						base_form.findField('EvnPS_HTMBegDate').fireEvent('change', base_form.findField('EvnPS_HTMBegDate'), base_form.findField('EvnPS_HTMBegDate').getValue());
						base_form.findField('EvnPS_HTMTicketNum').fireEvent('change', base_form.findField('EvnPS_HTMTicketNum'), base_form.findField('EvnPS_HTMTicketNum').getValue());

						this.findById('EPSEF_EvnPS_IsZNOCheckbox').setValue(base_form.findField('EvnPS_IsZNO').getValue() == 2);
						base_form.findField('Diag_spid').setContainerVisible(base_form.findField('EvnPS_IsZNO').getValue() == 2);
						base_form.findField('Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]) || base_form.findField('EvnPS_IsZNO').getValue() != 2);
						var diag_spid = base_form.findField('Diag_spid').getValue();
						if (diag_spid) {
							base_form.findField('Diag_spid').getStore().load({
								callback:function () {
									base_form.findField('Diag_spid').getStore().each(function (rec) {
										if (rec.get('Diag_id') == diag_spid) {
											base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
											thisWin.setDiagSpidComboDisabled();
										}
									});
								},
								params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_spid}
							});
						}
						if(getRegionNick()=='ekb') {
							this.checkZNO({action: this.action });
							this.checkBiopsyDate();
						}

						this.checkForCostPrintPanel();

						if ( !Ext.isEmpty(arguments[1].result.data.ChildLpuSection_id) ) {
							thisWin.ChildLpuSection_id = arguments[1].result.data.ChildLpuSection_id;
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_HOSP_EPSEDIT);
							this.enableEdit(true);

							this.findById('EPSEF_EvnDiagPSHospGrid').getTopToolbar().items.items[0].enable();
							this.findById('EPSEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[0].enable();
							this.findById('EPSEF_EvnSectionGrid').getTopToolbar().items.items[0].enable();
							this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[0].enable();
							this.findById('EPSEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
							this.findById('EPSEF_EvnDrugGrid').getTopToolbar().items.items[0].enable();

							this.findById('EPSEF_PersonInformationFrame').setPersonChangeParams({
								 callback: function(data) {
									 // если открыли из ЭМК, то надо ЭМК перекотрыть, делаем это в onPersonChange
									this.onPersonChange(data);
									this.hide();
								 }.createDelegate(this)
								,Evn_id: evn_ps_id
								,isEvnPS: true
							});
						}
						else {
							if(this.gridAccess != 'view'){
								this.findById('EPSEF_EvnStickGrid').getTopToolbar().items.items[0].enable();
							}
							this.setTitle(WND_HOSP_EPSVIEW);
							this.enableEdit(false);

							this.findById('EPSEF_PersonInformationFrame').clearPersonChangeParams();
						}
						
						if (getRegionNick() == 'astra') {
							if (this.action == 'view' && this.gridAccess == 'full') {
								setTimeout(function(){
									Ext.getCmp('EPSEF_EvnSection_add').enable();
								}, 50);
							}
						}

						if ( getRegionNick() == 'buryatiya' && v.result.data.LeaveType_prmSysNick && v.result.data.LeaveType_prmSysNick.inlist(['gosp','otk'])) {
							base_form.findField('LpuSectionProfile_id').hideContainer();
							base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
						}

						this.updateLookChange();

						this.findById('EPSEF_EvnStickGrid').getStore().load({
							params: {
								EvnStick_pid: this.findById('EvnPSEditForm').getForm().findField('EvnPS_id').getValue()
							}
						});

						//Проверяем возможность пользователя редактировать ЛВН
						checkEvnStickEditable('EPSEF_EvnStickGrid', thisWin);
						var evnDirectionData = new Object();

						evnDirectionData.Diag_did = arguments[1].result.data.Diag_did;
						evnDirectionData.EvnDirection_id = arguments[1].result.data.EvnDirection_id;
						evnDirectionData.EvnDirectionHTM_id = arguments[1].result.data.EvnDirectionHTM_id;
						evnDirectionData.EvnDirection_Num = arguments[1].result.data.EvnDirection_Num;
						evnDirectionData.EvnDirection_setDate = arguments[1].result.data.EvnDirection_setDate;
						evnDirectionData.LpuSection_id = arguments[1].result.data.LpuSection_did;
						evnDirectionData.Org_did = arguments[1].result.data.Org_did;
						evnDirectionData.Lpu_id = arguments[1].result.data.Lpu_did;
						evnDirectionData.PrehospDirect_id = arguments[1].result.data.PrehospDirect_id;

						if (evnDirectionData.EvnDirectionHTM_id)
							evnDirectionData.DirType_id = 19;  // ВМП

						this.findById('EPSEF_PriemLeavePanel').expand();
						this.findById('EPSEF_PriemLeavePanel').collapse();

						if ( this.form_mode == 'edit_priem' ) {
							// приемное
							this.findById('EPSEF_HospitalisationPanel').collapse();
							this.findById('EPSEF_DirectDiagPanel').collapse();
							this.findById('EPSEF_AdmitDepartPanel').expand();
							this.findById('EPSEF_AdmitDiagPanel').expand();
							this.findById('EPSEF_PriemLeavePanel').collapse();
							this.findById('EPSEF_PrehospWaifPanel').collapse();
							this.findById('EPSEF_CostPrintPanel').collapse();
							this.findById('EPSEF_EvnSectionPanel').collapse();
							this.findById('EPSEF_EvnStickPanel').collapse();
							this.findById('EPSEF_EvnUslugaPanel').collapse();
							this.findById('EPSEF_EvnDrugPanel').collapse();
							this.findById('EPSEF_EvnReanimatPeriodPanel').collapse();//BOB - 04.09.2018
							this.firstRun = true;
						}
						else {
							if ( this.firstRun == true ) {
								this.findById('EPSEF_EvnSectionPanel').expand();
								this.firstRun = false;
							}
							else {
								this.findById('EPSEF_EvnSectionPanel').fireEvent('expand', this.findById('EPSEF_EvnSectionPanel'));
							}
						}

						// Остальные гриды - только если развернуты панельки
						if ( !this.findById('EPSEF_DirectDiagPanel').collapsed ) {
							this.findById('EPSEF_DirectDiagPanel').fireEvent('expand', this.findById('EPSEF_DirectDiagPanel'));
						}

						if ( !this.findById('EPSEF_AdmitDiagPanel').collapsed ) {
							this.findById('EPSEF_AdmitDiagPanel').fireEvent('expand', this.findById('EPSEF_AdmitDiagPanel'));
						}

						if ( !this.findById('EPSEF_PrehospWaifPanel').collapsed ) {
							this.findById('EPSEF_PrehospWaifPanel').fireEvent('expand', this.findById('EPSEF_PrehospWaifPanel'));
						}

						if ( !this.findById('EPSEF_CostPrintPanel').collapsed ) {
							this.findById('EPSEF_CostPrintPanel').fireEvent('expand', this.findById('EPSEF_CostPrintPanel'));
						}

						if ( !this.findById('EPSEF_EvnStickPanel').collapsed ) {
							this.findById('EPSEF_EvnStickPanel').fireEvent('expand', this.findById('EPSEF_EvnStickPanel'));
						}

						if ( !this.findById('EPSEF_EvnUslugaPanel').collapsed ) {
							this.findById('EPSEF_EvnUslugaPanel').fireEvent('expand', this.findById('EPSEF_EvnUslugaPanel'));
						}

						if ( !this.findById('EPSEF_EvnDrugPanel').collapsed ) {
							this.findById('EPSEF_EvnDrugPanel').fireEvent('expand', this.findById('EPSEF_EvnDrugPanel'));
						}
						//BOB - 04.09.2018
						if ( !this.findById('EPSEF_EvnReanimatPeriodPanel').collapsed ) {
							this.findById('EPSEF_EvnReanimatPeriodPanel').fireEvent('expand', this.findById('EPSEF_EvnReanimatPeriodPanel'));
						}
						
						base_form.findField('UslugaComplex_id').setPersonId(base_form.findField('Person_id').getValue());
						base_form.findField('PrehospWaifRefuseCause_id').fireEvent('change', base_form.findField('PrehospWaifRefuseCause_id'), base_form.findField('PrehospWaifRefuseCause_id').getValue());
						base_form.findField('MedStaffFact_did').fireEvent('change', base_form.findField('MedStaffFact_did'), base_form.findField('MedStaffFact_did').getValue());
						
						this.reloadUslugaComplexField(null, base_form.findField('UslugaComplex_id').getValue());

						this.findById('EPSEF_PersonInformationFrame').setTitle('...');
						this.isProcessInformationFrameLoad = true;
						this.findById('EPSEF_PersonInformationFrame').load({
							callback: function() {
								this.findById('EPSEF_PersonInformationFrame').setPersonTitle();

								base_form.findField('EvnPS_setDate').setMinValue(this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Birthday'));

								var omsSprTerrCode = this.findById('EPSEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');

								var isPerm = (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm');

								var DeseaseType_id = base_form.findField('DeseaseType_id').getValue();
								var diag_did = diag_d_combo.getValue();
								var diag_eid = diag_e_combo.getValue();
								var evn_direction_id = base_form.findField('EvnDirection_id').getValue();
								var evn_direction_num = base_form.findField('EvnDirection_Num').getValue();
								var evn_direction_set_date = base_form.findField('EvnDirection_setDate').getValue();
								var evn_ps_code_conv = base_form.findField('EvnPS_CodeConv').getValue();
								var evn_ps_is_cont = base_form.findField('EvnPS_IsCont').getValue();
								var evn_ps_is_unlaw = base_form.findField('EvnPS_IsUnlaw').getValue();
								var evn_ps_num_conv = base_form.findField('EvnPS_NumConv').getValue();
								var evn_ps_set_date = base_form.findField('EvnPS_setDate').getValue();
								var LeaveType_prmid = base_form.findField('LeaveType_prmid').getValue();
								var lpu_section_did = lpu_section_dir_combo.getValue();
								var lpu_section_pid = lpu_section_rec_combo.getValue();
								var med_staff_fact_pid = med_staff_fact_rec_combo.getValue();
								var org_did = org_combo.getValue();
								var prehosp_arrive_id = prehosp_arrive_combo.getValue();
								var prehosp_direct_id = prehosp_direct_combo.getValue();
								var prehosp_trauma_id = prehosp_trauma_combo.getValue();
								var cmp_call_card_id = cmp_call_card_combo.getValue();

								var index;
								var record;

								base_form.findField('EvnPS_IsCont').fireEvent('change', base_form.findField('EvnPS_IsCont'), evn_ps_is_cont);
								prehosp_direct_combo.setValue(prehosp_direct_id);
								base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), evn_ps_set_date);

								if ( lpu_section_pid ) {
									diag_p_combo.setDisabled( this.action == 'view' );
								}

								if ( this.action == 'view' ) {
									lpu_section_rec_combo.clearValue();
									lpu_section_rec_combo.getStore().load({
										callback: function() {
											index = lpu_section_rec_combo.getStore().findBy(function(record, id) {
												if ( record.get('LpuSection_id') == lpu_section_pid )
													return true;
												else
													return false;
											})

											if ( index >= 0 ) {
												lpu_section_rec_combo.setValue(lpu_section_pid);
											}
										},
										params: {
											Lpu_id: base_form.findField('Lpu_id').getValue() || getGlobalOptions().lpu_id,
											LpuSection_id: lpu_section_pid
										}
									});

									med_staff_fact_rec_combo.clearValue();
									med_staff_fact_rec_combo.getStore().load({
										callback: function() {
											index = med_staff_fact_rec_combo.getStore().findBy(function(record, id) {
												if ( record.get('MedStaffFact_id') == med_staff_fact_pid )
													return true;
												else
													return false;
											})

											if ( index >= 0 ) {
												med_staff_fact_rec_combo.setValue(med_staff_fact_rec_combo.getStore().getAt(index).get('MedStaffFact_id'));
												med_staff_fact_rec_combo.fireEvent('change', med_staff_fact_rec_combo, med_staff_fact_rec_combo.getValue());
											}
										},
										params: {
											Lpu_id: base_form.findField('Lpu_id').getValue() || getGlobalOptions().lpu_id,
											MedStaffFact_id: med_staff_fact_pid
										}
									});
								}
								else {
									index = med_staff_fact_rec_combo.getStore().findBy(function(record, id) {
										if ( record.get('MedStaffFact_id') == med_staff_fact_pid )
											return true;
										else
											return false;
									})

									if ( index >= 0 ) {
										med_staff_fact_rec_combo.setValue(med_staff_fact_rec_combo.getStore().getAt(index).get('MedStaffFact_id'));
										med_staff_fact_rec_combo.fireEvent('change', med_staff_fact_rec_combo, med_staff_fact_rec_combo.getValue());
									}
									else {
										Ext.Ajax.request({
											failure: function(response, options) {
												loadMask.hide();
											},
											params: {
												Lpu_id: base_form.findField('Lpu_id').getValue() || getGlobalOptions().lpu_id,
												MedStaffFact_id: med_staff_fact_pid
											},
											success: function(response, options) {
												loadMask.hide();
												
												med_staff_fact_rec_combo.getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

												index = med_staff_fact_rec_combo.getStore().findBy(function(rec) {
													if ( rec.get('MedStaffFact_id') == med_staff_fact_pid ) {
														return true;
													}
													else {
														return false;
													}
												});

												if ( index >= 0 ) {
													med_staff_fact_rec_combo.setValue(med_staff_fact_rec_combo.getStore().getAt(index).get('MedStaffFact_id'));
													med_staff_fact_rec_combo.validate();
													med_staff_fact_rec_combo.fireEvent('change', med_staff_fact_rec_combo, med_staff_fact_rec_combo.getValue());
												}
											}.createDelegate(this),
											url: C_MEDPERSONAL_LIST
										});
									}

									if ( getRegionNick() == 'ekb' && this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_IsAnonym') == 2 ) {
										base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'bud');
										base_form.findField('PayType_id').disable();
									}
								}

								if ( !Ext.isEmpty(prehosp_direct_id) ) {
									record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

									if ( !record ) {
										loadMask.hide();
										return false;
									}

									var prehosp_direct_code = record.get('PrehospDirect_Code');
									var org_type = '';

									if ( !Ext.isEmpty(evn_direction_id) ) {
										iswd_combo.setValue(2);
									} else {
										iswd_combo.setValue(1);
									}
									iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
									// prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_id, -1);

									base_form.findField('EvnDirection_id').setValue(evn_direction_id);

									switch ( prehosp_direct_code ) {
										case 1:
											lpu_section_dir_combo.setAllowBlank(false);
											lpu_section_dir_combo.setValue(lpu_section_did);
											org_combo.setAllowBlank(true);

											if ( !Ext.isEmpty(MedStaffFact_did) ) {
												base_form.findField('MedStaffFact_did').setValue(MedStaffFact_did);
												this.loadMedStaffFactDidCombo();
											}
											break;

										case 2:
											org_type = 'lpu';

											lpu_section_dir_combo.setAllowBlank(true);
											org_combo.setAllowBlank(false);
											break;

										case 3:
										case 4:
										case 5:
										case 6:
											org_type = 'org';

											lpu_section_dir_combo.setAllowBlank(true);
											org_combo.setAllowBlank(true);
											break;

										default:
											loadMask.hide();
											lpu_section_dir_combo.setAllowBlank(true);
											org_combo.setAllowBlank(true);

											break;
									}

									if ( org_type.length > 0 && org_did ) {
										org_combo.getStore().load({
											callback: function(records, options, success) {
												org_combo.clearValue();

												if ( success ) {
													org_combo.setValue(org_did);
													if ( prehosp_direct_code == 2 ) {
														this.checkOtherLpuDirection();
													}
												}

												base_form.findField('MedStaffFact_did').setValue(MedStaffFact_did);
												org_combo.fireEvent('change', org_combo, org_combo.getValue());
											}.createDelegate(this),
											params: {
												Org_id: org_did,
												OrgType: org_type
											}
										});
									}
								}

								if(!getRegionNick().inlist([ 'kz' ])) {
									var isCmpTlt = !Ext.isEmpty(base_form.findField('EvnPS_CmpTltDate').getValue());
									thisWin.findById(thisWin.id + '_isCmpTlt').setValue(isCmpTlt);
									thisWin.findById(thisWin.id + '_isCmpTlt').setVisibleFormDT(isCmpTlt);
									if(!diag_pid) {
										thisWin.findById(thisWin.id + '_TltPanel').setVisible(false);
										base_form.findField('EvnPS_TimeDesease').setAllowBlank(true);
									}
								}

								if ( diag_did ) {
									diag_d_combo.getStore().load({
										callback: function() {
											diag_d_combo.setValue(diag_did);
											diag_d_combo.fireEvent('select', diag_d_combo, diag_d_combo.getStore().getAt(0), 0);
											diag_d_combo.setFilterByDate(base_form.findField('EvnDirection_setDate').getValue());

											if (!getRegionNick().inlist([ 'kz' ]) && thisWin.getOKSDiag()) {
												thisWin.loadUslugaGrid();
											}
										},
										params: {
											where: "where DiagLevel_id = 4 and Diag_id = " + diag_did
										}
									});
								}

								if ( diag_pid ) {
									diag_p_combo.getStore().load({
										callback: function() {
											diag_p_combo.setValue(diag_pid);
											diag_p_combo.fireEvent('select', diag_p_combo, diag_p_combo.getStore().getAt(0), 0);
											thisWin.setDiagEidAllowBlank();

											thisWin.refreshFieldsVisibility();
											thisWin.setSpecificsPanelVisibility();

											if ( !Ext.isEmpty(DeseaseType_id) ) {
												base_form.findField('DeseaseType_id').setValue(DeseaseType_id);
											}

											thisWin.formLoaded = true;

											if (!getRegionNick().inlist([ 'kz' ])) {
												if (thisWin.getOKSDiag())
													thisWin.loadUslugaGrid();
												var isIshemia = diag_p_combo.getGroup().inlist(thisWin.IshemiaCode);
												thisWin.findById(thisWin.id + '_TltPanel').setVisible(isIshemia);
												base_form.findField('EvnPS_TimeDesease').setAllowBlank(!isIshemia);
											}
										},
										params: {
											where: "where DiagLevel_id = 4 and Diag_id = " + diag_pid
										}
									});
								}

								if ( diag_eid ) {
									diag_e_combo.getStore().load({
										callback: function() {
											diag_e_combo.setValue(diag_eid);
											diag_e_combo.fireEvent('select', diag_e_combo, diag_e_combo.getStore().getAt(0), 0);
											thisWin.setDiagEidAllowBlank();
										},
										params: {
											where: "where DiagLevel_id = 4 and Diag_id = " + diag_eid
										}
									});
								}

								if ( cmp_call_card_id ) {
									cmp_call_card_combo.getStore().load({
										callback: function() {
											cmp_call_card_combo.setValue(cmp_call_card_id);

											var index = cmp_call_card_combo.getStore().findBy(function(rec) { return rec.get('CmpCallCard_id') == cmp_call_card_id; });
											var record = cmp_call_card_combo.getStore().getAt(index);
											cmp_call_card_combo.fireEvent('select', cmp_call_card_combo, record, index);
										},
										params: {
											CmpCallCard_id: cmp_call_card_id
										}
									});
								}

								base_form.findField('EvnDirection_Num').setValue(evn_direction_num);
								base_form.findField('EvnDirection_setDate').setValue(evn_direction_set_date);

								prehosp_arrive_combo.fireEvent('change', prehosp_arrive_combo, prehosp_arrive_id, -1);
								base_form.findField('EvnPS_CodeConv').setValue(evn_ps_code_conv);
								base_form.findField('EvnPS_NumConv').setValue(evn_ps_num_conv);

								prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_id, -1);
								base_form.findField('EvnPS_IsUnlaw').setValue(evn_ps_is_unlaw);
								base_form.findField('EvnPS_IsUnlaw').fireEvent('change', base_form.findField('EvnPS_IsUnlaw'), base_form.findField('EvnPS_IsUnlaw').getValue());

								if ( getRegionNick().inlist([ 'buryatiya', 'pskov' ]) ) {
									base_form.findField('LeaveType_prmid').fireEvent('change', base_form.findField('LeaveType_prmid'), LeaveType_prmid, -1);
								}
								else if ( getRegionNick().inlist([ 'perm' ]) ) {
									if ( !Ext.isEmpty(LeaveType_fedid) ) {
										base_form.findField('LeaveType_fedid').setValue(LeaveType_fedid);
									}

									if ( !Ext.isEmpty(ResultDeseaseType_fedid) ) {
										base_form.findField('ResultDeseaseType_fedid').setValue(ResultDeseaseType_fedid);
									}
								}

								loadMask.hide();

								//base_form.clearInvalid();

								if ( this.action == 'edit' ) {
									if ( this.findById('EPSEF_EvnSectionGrid').getStore().getCount() > 0 ) {
										this.findById('EPSEF_EvnSectionGrid').getView().focusRow(0);
										this.findById('EPSEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
									}
									else {
										base_form.findField('EvnPS_IsCont').focus(true, 250);
									}
								}
								else {
									this.buttons[this.buttons.length - 1].focus();
								}

								this.checkEvnDirectionAllowBlank();

								if (evnDirectionData) {
									if (evnDirectionData.EvnDirection_id) {
										this.setDirection(evnDirectionData);
										//не дизаблить ЭН (refs #7817)
										//thisWin.findById('EPSEF_EvnDirectionSelectButton').disable();
										//base_form.findField('PrehospDirect_id').disable();
										//base_form.findField('EvnPS_IsWithoutDirection').disable();
									} else {
										// thisWin.findById('EPSEF_EvnDirectionSelectButton').enable();
										base_form.findField('PrehospDirect_id').setDisabled( this.action == 'view' );
										base_form.findField('EvnPS_IsWithoutDirection').setDisabled( this.action == 'view' );
									}
								} else {
									// thisWin.findById('EPSEF_EvnDirectionSelectButton').enable();
									base_form.findField('PrehospDirect_id').setDisabled( this.action == 'view' );
									base_form.findField('EvnPS_IsWithoutDirection').setDisabled( this.action == 'view' );
								}

								if(this.findById('EPSEF_PersonInformationFrame').getFieldValue('Person_Age') < 18)
								{
									this.findById('EPSEF_PrehospWaifPanel').show();
									is_waif_combo.setAllowBlank(false);
									is_waif_combo.fireEvent('change', is_waif_combo, is_waif_combo.getValue(), null);
								}

								base_form.items.each(function(f){
									f.validate();
								});
								this.setMKB();

								this.disableSetMedicalCareFormType = false;

								this.filterMedicalCareFormType();

								if ( !Ext.isEmpty(LpuSectionProfile_id) ) {
									base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
								}

								if ( !Ext.isEmpty(MedicalCareFormType_id) ) {
									base_form.findField('MedicalCareFormType_id').setValue(MedicalCareFormType_id);
								}
								this.isProcessInformationFrameLoad = false;
								this.addTextPersonInfoPanel();
							}.createDelegate(this),
							onExpand: true,
							Person_id: base_form.findField('Person_id').getValue(),
							PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue(),
							Evn_setDT:Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(),'d.m.Y')
						});
						var TraumaCircumEvnPS_setDT = v.result.data.TraumaCircumEvnPS_setDT;
						if ( !getRegionNick().inlist['kz'] && !Ext.isEmpty(TraumaCircumEvnPS_setDT)) {
							base_form.findField('TraumaCircumEvnPS_setDTDate').setValue(Ext.util.Format.date(TraumaCircumEvnPS_setDT, 'd.m.Y'));
							base_form.findField('TraumaCircumEvnPS_setDTTime').setValue(Ext.util.Format.date(TraumaCircumEvnPS_setDT, 'H:i'));
						}
						//Если есть дочернее событие, позволяем выбирать в "Госпитализирован в" только отдление первого движения и то, что было до этого
						if (!Ext.isEmpty(thisWin.ChildLpuSection_id) && thisWin.ChildLpuSection_id){
							var LpuSection = base_form.findField('LpuSection_eid').getValue();

							setLpuSectionGlobalStoreFilter({
								ids: [ LpuSection, thisWin.ChildLpuSection_id ]
							});

							base_form.findField('LpuSection_eid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
							if (!base_form.findField('LpuSection_eid').getValue()
								&& !Ext.isEmpty(base_form.findField('LpuSection_eid').getStore())) {
								var id = base_form.findField('LpuSection_eid').getStore().data.keys[0];
								for (var x in base_form.findField('LpuSection_eid').getStore().data.keys) {
									base_form.findField('LpuSection_eid').setValue(x);
									if (base_form.findField('LpuSection_eid').getValue())
										break;
								}
								base_form.findField('LpuSection_eid').setValue(id);
							}
						}

						if (getRegionNick().inlist['kareliya', 'krym', 'penza']) {
							this.setMedicalCareFormTypeAllowBlank();
						}
						this.setPrehospArriveAllowBlank();
						this.setDiagEidAllowBlank();
						this.checkVMPFieldEnabled();
						this.visibleBlockblockPediculos();

						this.isProcessLoadForm = false;
					}.createDelegate(this),
					url: '/?c=EvnPS&m=loadEvnPSEditForm'
				});
			break;

			default:
				loadMask.hide();
			break;
		}
		if(getRegionNick()=='ekb') {
			Ext.QuickTips.register({
				target: base_form.findField('EvnPS_BiopsyDate').getEl(),
				text: 'Дата взятия биопсии, по результатам которой снимается подозрение на ЗНО',
				enabled: true,
				showDelay: 5,
				trackMouse: true,
				autoShow: true
			});
		}
	},
	width: 800,

	_getDataEvnDirection: function(EvnDirection_id){
    	var data = null;

		$.ajax({
			method: "POST",
			url: '/?c=EvnDirection&m=getDataEvnDirection',
			data: {
				EvnDirection_id: EvnDirection_id
			},
			async: false,
			success: function(response){
				var result = Ext.util.JSON.decode(response);
				if(result[0]) {
					data = result[0];
				}
			}
		});

		return data;
	},
	printControlCardZno: function()
	{
		var grid = Ext.getCmp('EPSEF_EvnSectionGrid'),
			rec = grid.getSelectionModel().getSelected();

		if (rec.get('EvnSection_id'))
		{
			printControlCardZno(rec.get('EvnSection_id'));
		}
	},
	printControlCardOnko: function()
	{
		var grid = Ext.getCmp('EPSEF_EvnSectionGrid'),
			rec = grid.getSelectionModel().getSelected();

		if (rec.get('EvnSection_id'))
		{
			printControlCardOnko(rec.get('EvnSection_id'));
		}
	},

	showPopup: function(window) {
		var win = this,
			baseForm = win.getFormPanel()[0].getForm(),
			params = {};

		switch(window) {
			case 'trauma':
				params.title = 'Шкала оценки тяжести (Травма)';
				params.fields = [{
					fieldLabel: 'Реация на боль',
					value: baseForm.findField('PainResponse_Name').getValue()
				}, {
					fieldLabel: 'Характер внешнего дыхания',
					value: baseForm.findField('ExternalRespirationType_Name').getValue()
				}, {
					fieldLabel: 'Систолическое АД, мм рт. ст.',
					value: baseForm.findField('SystolicBloodPressure_Name').getValue()
				}, {
					fieldLabel: 'Признаки внутреннего кровотечения',
					value: baseForm.findField('InternalBleedingSigns_Name').getValue()
				}, {
					fieldLabel: 'Отрыв конечности',
					value: baseForm.findField('LimbsSeparation_Name').getValue()
				}, {
					fieldLabel: 'Итого баллов',
					value: baseForm.findField('PrehospTraumaScale_Value').getValue()
				}];
				break;

			case 'lams':
				params.title = 'Шкала LAMS (ОНМК)'
				params.fields = [{
					fieldLabel: 'Асимметрия лица',
					value: baseForm.findField('FaceAsymetry_Name').getValue()
				}, {
					fieldLabel: 'Удержание рук',
					value: baseForm.findField('HandHold_Name').getValue()
				}, {
					fieldLabel: 'Сжимание в кисти',
					value: baseForm.findField('SqueezingBrush_Name').getValue()
				}, {
					fieldLabel: 'Итого баллов',
					value: baseForm.findField('ScaleLams_Value').getValue()
				}];
				break;

			case 'oks':
				params.title = 'ОКС';
				params.fields = [{
					fieldLabel: 'Время начала болевых симптомов',
					value: baseForm.findField('PainDT').getValue()
				}, {
					fieldLabel: 'Результат ЭКГ',
					value: baseForm.findField('ResultECG').getValue()
				}, {
					fieldLabel: 'Время проведения ЭКГ',
					value: baseForm.findField('ECGDT').getValue()
				}, {
					fieldLabel: 'Время проведения ТЛТ',
					value: baseForm.findField('TLTDT').getValue()
				}, {
					fieldLabel: 'Причина отказа от ТЛТ',
					value:  baseForm.findField('FailTLT').getValue()
				}];
				break;
		}

		showPopupWindow(params);
	},
	loadScaleFieldset: function() {
		var win = this,
			baseForm = this.getFormPanel()[0].getForm(),
			CmpCallCard_id = baseForm.findField('CmpCallCard_id').getValue();

		if(!CmpCallCard_id) return;

		Ext.Ajax.request({
			url: "?c=EvnPS&m=loadScalesByCmpCallCardId",
			params: { CmpCallCard_id: CmpCallCard_id },
			callback: function(options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(success && Array.isArray(response_obj)) {
					baseForm.setValues(response_obj[0]);
				}
			}
		});
	},
	visibleBlockblockPediculos: function(){
		var base_form = this.findById('EvnPSEditForm').getForm();
		var blockPediculos = Ext.getCmp('EPSEF_blockPediculos');
		var comboPediculosDiag = base_form.findField('PediculosDiag_id');
		var pediculos_Sanitation_setDate = base_form.findField('Pediculos_Sanitation_setDate');
		var pediculos_Sanitation_setTime = base_form.findField('Pediculos_Sanitation_setTime');
		var buttonsPediculosPrint = Ext.getCmp('EPSEF_PediculosPrint');
		var flagShow = (getRegionNick().inlist(['vologda','msk','ufa'])) ? true : false;

		if(flagShow){
			blockPediculos.show();
		}else{
			blockPediculos.hide();
			comboPediculosDiag.setAllowBlank(true);
			pediculos_Sanitation_setDate.setAllowBlank(true);
			pediculos_Sanitation_setTime.setAllowBlank(true);
			return false;
		}

		var chek_isPediculos = base_form.findField('isPediculos');
		var chek_isScabies = base_form.findField('isScabies');
		var chek_Pediculos_isSanitation = base_form.findField('Pediculos_isSanitation');
		if(this.action == 'add'){
			comboPediculosDiag.setAllowBlank(true);
			chek_isPediculos.fireEvent('change', base_form.findField('isPediculos'), false);
			chek_isScabies.fireEvent('change', base_form.findField('isScabies'), false);
			pediculos_Sanitation_setDate.setAllowBlank(true);
			pediculos_Sanitation_setTime.setAllowBlank(true);
			chek_Pediculos_isSanitation.fireEvent('change', base_form.findField('Pediculos_isSanitation'), false);
			buttonsPediculosPrint.disable();
		}else{
			var flag_isPediculos = chek_isPediculos.getValue();
			var flag_isScabies = chek_isScabies.getValue();
			var flag_Pediculos_isSanitation = chek_Pediculos_isSanitation.getValue();
			comboPediculosDiag.setAllowBlank(!flag_isPediculos);
			chek_isPediculos.fireEvent('change', base_form.findField('isPediculos'), flag_isPediculos);
			chek_isScabies.fireEvent('change', chek_isScabies, flag_isScabies);
			pediculos_Sanitation_setDate.setAllowBlank(!flag_Pediculos_isSanitation);
			pediculos_Sanitation_setTime.setAllowBlank(!flag_Pediculos_isSanitation);
			chek_Pediculos_isSanitation.fireEvent('change', base_form.findField('Pediculos_isSanitation'), flag_Pediculos_isSanitation);

			var comboPediculosDiag = base_form.findField('PediculosDiag_id');
			var comboScabiesDiag = base_form.findField('ScabiesDiag_id');
			var pediculosDiagID = comboPediculosDiag.getValue();
			var scabiesDiagID = comboScabiesDiag.getValue();
			if (pediculosDiagID) {
				comboPediculosDiag.getStore().load({
					callback:function () {
						comboPediculosDiag.getStore().each(function (rec) {
							if (rec.get('Diag_id') == pediculosDiagID) {
								comboPediculosDiag.fireEvent('select', comboPediculosDiag, rec, 0);
							}
						});
					},
					params:{where:"where Diag_id = " + pediculosDiagID}
				});
			}
			if (scabiesDiagID) {
				comboScabiesDiag.getStore().load({
					callback: function() {
						comboScabiesDiag.getStore().each(function(rec) {
							if (rec.get('Diag_id') == scabiesDiagID) {
								comboScabiesDiag.fireEvent('select', comboScabiesDiag, rec, 0);	
							}
						});
					},
					params: {where: "where Diag_id = " + scabiesDiagID}
				});
			}
			if(parseInt(base_form.findField('buttonPrint058').getValue()) == 1){
				buttonsPediculosPrint.enable();
			}else{
				buttonsPediculosPrint.disable();
			}
		}
	},
	addTextPersonInfoPanel: function(){
		//получение дополнительной информации в блок информации о пациенте
		if(!getRegionNick().inlist(['vologda','msk','ufa'])) return false;
		var win = this;
		var base_form = this.findById('EvnPSEditForm').getForm();
		var person_id = base_form.findField('Person_id').getValue();
		var evnps_id = base_form.findField('EvnPS_id').getValue();
		var personInformationFrame = win.findById('EPSEF_PersonInformationFrame');
		if(!personInformationFrame || !evnps_id) return false;
		var divInfoPanel = personInformationFrame.body.dom.querySelector('div > .x-border-panel');
		if(!divInfoPanel) return false;
		var fields = ['accompanied_by_an_adult', 'pediculosis_check_and_sanitation', 'things_and_valuables_in_storage'];
		var fieldsObj = {
			accompanied_by_an_adult: 'Сопровождается взрослым',
			pediculosis_check_and_sanitation: 'Проведена санитарная обработка',
			things_and_valuables_in_storage: 'Вещи и ценности на хранении'
		}
		var params = {
			EvnPS_id: evnps_id,
			Person_id: person_id,
			fields: JSON.stringify(Object.keys(fieldsObj))
		}
		Ext.Ajax.request({
			url: "?c=EvnPS&m=getInfoPanelAdditionalInformation",
			params: params,
			callback: function(options, success, response) {
				var win = this.win;
				var fieldsObj = this.fieldsObj;
				var divInfoPanel = this.divInfoPanel;
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj && response_obj[0] && response_obj[0].EvnPS_id){
					var res = response_obj[0];
					for(var key in fieldsObj){
						if(res[key]){
							var div = document.createElement('div');
							div.id = res.EvnPS_id + '_' + key;
							div.style = 'padding-left: 25px;';
							var font = document.createElement('font');
							font.style = 'color: blue';
							font.textContent = res[key];
							div.textContent = fieldsObj[key]+': ';
							div.appendChild(font);
							divInfoPanel.appendChild(div);
						}
					}
					// win.findById('EPSEF_PersonInformationFrame').items.items[0].setHeight(190)
					win.findById('EPSEF_PersonInformationFrame').doLayout();
				}
			}.createDelegate({win:this, fieldsObj: fieldsObj, divInfoPanel: divInfoPanel}),
			failure: function(){
				console.log('Error: getInfoPanelAdditionalInformation')
			}
		});
	},
	pediculosPrint: function(save){
		var save = save || false;
		var base_form = this.findById('EvnPSEditForm').getForm();
		if(save && this.action != 'view'){
			base_form.findField('Pediculos_isPrint').setValue(2);
			this.doSave({pediculosPrint: true});
		}else{
			var lpu_id = getGlobalOptions().lpu_id;
			var evnps_id = base_form.findField('EvnPS_id').getValue();
			var PediculosDiag_id = base_form.findField('PediculosDiag_id').getValue();
			var ScabiesDiag_id = base_form.findField('ScabiesDiag_id').getValue();
			if(evnps_id && lpu_id) {
				if ( PediculosDiag_id ) {
					printBirt({
						'Report_FileName': 'f058u.rptdesign',
						'Report_Params': '&paramLpu=' + lpu_id + '&paramEvnPS=' + evnps_id,
						'Report_Format': 'pdf'
					});
				}

				if ( ScabiesDiag_id ) {
					printBirt({
						'Report_FileName': 'f058u.rptdesign',
						'Report_Params': '&paramLpu=' + lpu_id + '&paramEvnPS=' + evnps_id + '&paramDiag=' + ScabiesDiag_id,
						'Report_Format': 'pdf'
					});
				}
				
				base_form.findField('Pediculos_isPrint').setValue(2);
			}
		}
	},

/******* _loadHtmData *********************************************************
	*
	******************************************************************************/
	_loadHtmData: function(EvnDirectionHTM_id)
	{
		var me = this,
			lMask = null,
			v;

		if (v = this.findById('EPSEF_EvnSectionHtm').getEl())
		{
			lMask = new Ext.LoadMask(v);
			lMask.show();
		}

		Ext.Ajax.request({
			url: "?c=EvnDirectionHTM&m=loadEvnDirectionHTMForm",
			params: { EvnDirectionHTM_id: EvnDirectionHTM_id },
			callback: _onLoadHtmData,
			scope: this
		});

/******* _onLoadHtmData *******************************************************
	*
	*/
		function _onLoadHtmData(options, success, response)
		{
			me._fillHtmData(success && Ext.util.JSON.decode(response.responseText)[0]);

			if (lMask)
			{
				lMask.hide();
				lMask.destroy();
			}
		}
	},

/******* _fillHtmData *********************************************************
	*
	******************************************************************************/
	 _fillHtmData: function(data)
		{
			var baseForm = this.findById('EvnPSEditForm').getForm();

			baseForm.findField('EvnPS_HTMBegDate').setValue(data && data.EvnDirectionHTM_setDate || null);
			baseForm.findField('EvnPS_HTMTicketNum').setValue(data && data.EvnDirectionHTM_TalonNum || null);
			baseForm.findField('EvnPS_HTMHospDate').setValue(data && data.EvnDirectionHTM_planDate || null);
		}
});
