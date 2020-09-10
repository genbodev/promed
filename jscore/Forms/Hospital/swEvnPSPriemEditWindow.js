/**
* swEvnPSPriemEditWindow - Поступление пациента в приемное отделение.
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
* @comment      Префикс для id компонентов EPSPEF (EvnPSPriemEditForm)
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
*             окно редактирования осмотров беспризорных (swPrehospWaifInspectionEditWindow)
*             окно редактирования общей услуги (swEvnUslugaEditWindow)
*             окно добавления комплексной услуги (swEvnUslugaComplexEditWindow)
*             окно добавления оперативной услуги (swEvnUslugaOperEditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPSPriemEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPSPriemEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnPSPriemEditWindow.js',

	title: langs('Поступление пациента в приемное отделение'),
	buttonAlign: 'left',
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	plain: true,
	draggable: true,
	height: 550,
	id: 'EvnPSPriemEditWindow',
	width: 800,
	resizable: true,
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	layout: 'border',
	tabindex: TABINDEX_EPSPEF,
	
	action: null,
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	onCancelAction: Ext.emptyFn,
	formStatus: 'edit',
	evnPSAbortStore: null,
	firstRun: true,
	isCopy: false,
	form_panels: [
		'EPSPEF_HospitalisationPanel',
		'EPSPEF_DirectDiagPanel',
		'EPSPEF_AdmitDepartPanel',
		'EPSPEF_AdmitDiagPanel',
		'EPSPEF_PriemLeavePanel',
		'EPSPEF_EvnUslugaPanel',
		'EPSPEF_EvnDrugPanel',
		'EPSPEF_PrehospWaifPanel'
	],
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			for(var i = 0; i < win.form_panels.length; i++)
			{
				if(!win.findById(win.form_panels[i]).hidden)
				{
					win.findById(win.form_panels[i]).doLayout();
				}
			}
		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPSPriemEditWindow');

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
					if ( !current_window.findById('EPSPEF_HospitalisationPanel').hidden ) {
						current_window.findById('EPSPEF_HospitalisationPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					if ( !current_window.findById('EPSPEF_DirectDiagPanel').hidden ) {
						current_window.findById('EPSPEF_DirectDiagPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					if ( !current_window.findById('EPSPEF_AdmitDepartPanel').hidden ) {
						current_window.findById('EPSPEF_AdmitDepartPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.FOUR:
				case Ext.EventObject.NUM_FOUR:
					if ( !current_window.findById('EPSPEF_AdmitDiagPanel').hidden ) {
						current_window.findById('EPSPEF_AdmitDiagPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.FIVE:
				case Ext.EventObject.NUM_FIVE:
					if ( !current_window.findById('EPSPEF_PriemLeavePanel').hidden ) {
						current_window.findById('EPSPEF_PriemLeavePanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					if ( !current_window.findById('EPSPEF_EvnUslugaPanel').hidden ) {
						current_window.findById('EPSPEF_EvnUslugaPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.NUM_SEVEN:
				case Ext.EventObject.SEVEN:
					if ( !current_window.findById('EPSPEF_EvnDrugPanel').hidden ) {
						current_window.findById('EPSPEF_EvnDrugPanel').toggleCollapse();
					}
				break;

				case Ext.EventObject.EIGHT:
				case Ext.EventObject.NUM_EIGHT:
					if ( !current_window.findById('EPSPEF_PrehospWaifPanel').hidden ) {
						current_window.findById('EPSPEF_PrehospWaifPanel').toggleCollapse();
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
		stopEvent: true
	}, {
		alt: false,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPSPriemEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.F6:
					current_window.findById('EPSPEF_PersonInformationFrame').panelButtonClick(1);
				break;

				case Ext.EventObject.F10:
					current_window.findById('EPSPEF_PersonInformationFrame').panelButtonClick(2);
				break;

				case Ext.EventObject.F11:
					current_window.findById('EPSPEF_PersonInformationFrame').panelButtonClick(3);
				break;

				case Ext.EventObject.F12:
					if ( e.ctrlKey == true ) {
						current_window.findById('EPSPEF_PersonInformationFrame').panelButtonClick(5);
					}
					else {
						current_window.findById('EPSPEF_PersonInformationFrame').panelButtonClick(4);
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
	deleteEvent: function(event) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( !event.inlist(['EvnUsluga', 'EvnDiagPSHosp', 'EvnDiagPSRecep', 'EvnDrug']) ) {
			return false;
		}

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnDrug':
				grid = this.findById('EPSPEF_EvnDrugGrid');
			break;

			case 'EvnUsluga':
				grid = this.findById('EPSPEF_EvnUslugaGrid');
			break;

			case 'EvnDiagPSHosp':
				grid = this.findById('EPSPEF_EvnDiagPSHospGrid');
			break;

			case 'EvnDiagPSRecep':
				grid = this.findById('EPSPEF_EvnDiagPSRecepGrid');
			break;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		else if ( (event == 'EvnDiagPSHosp' || event == 'EvnDiagPSRecep') && !grid.getSelectionModel().getSelected().get('EvnDiagPS_id') ) {
			return false;
		}
		else if ( event != 'EvnDiagPSHosp' && event != 'EvnDiagPSRecep' && !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		if (selected_record.get('EvnClass_SysNick') == 'EvnUslugaPar') {
			return false;
		}
			
		switch ( event ) {
			case 'EvnDrug':
				error = langs('При удалении случая использования медикаментов возникли ошибки');
				question = langs('Удалить случай использования медикаментов?');
				url = '/?c=EvnDrug&m=deleteEvnDrug';

				params['EvnDrug_id'] = selected_record.get('EvnDrug_id');
			break;

			case 'EvnUsluga':
				error = langs('При удалении услуги возникли ошибки');
				question = langs('Удалить услугу?');
				url = '/?c=EvnUsluga&m=deleteEvnUsluga';

				params['class'] = selected_record.get('EvnClass_SysNick');
				params['id'] = selected_record.get('EvnUsluga_id');
			break;

			case 'EvnDiagPSHosp':
			case 'EvnDiagPSRecep':
				error = langs('При удалении диагноза возникли ошибки');
				question = langs('Удалить диагноз?');
				url = '/?c=EvnDiag&m=deleteEvnDiag';

				params['class'] = 'EvnDiagPS';
				params['id'] = selected_record.get('EvnDiagPS_id');
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
							sw.swMsg.alert(langs('Ошибка'), error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();

							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : error);
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
			title: langs('Вопрос')
		});
	},
	checkTrauma:function(){
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var traumaField = base_form.findField('PrehospTrauma_id');
		var isAB = true;
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
		var diag_pid = base_form.findField('Diag_pid').getValue();
		if(diag_pid){
			var rec = base_form.findField('Diag_pid').getStore().getById(diag_pid)
			if(rec&&rec.get('Diag_Code')&&(rec.get('Diag_Code')[0].inlist(['T',"S"]))){
				if(rec.get('Diag_Code').substr(0,2)!="T9"||isUfa){
					isAB = false;
				}
			}
		}
		traumaField.setAllowBlank(isAB);
		return true;
	},
	setDiagSpidComboDisabled: function() {

		if (!getRegionNick().inlist(['perm', 'msk', 'ufa']) || this.action == 'view') return false;

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var diag_spid_combo = base_form.findField('Diag_spid');
		var iszno_checkbox = this.findById('EPSPEF_EvnPS_IsZNOCheckbox');

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
	checkZNO: function(options){
		if(getRegionNick()!='ekb') return;
		var win = this,
			base_form = win.findById('EvnPSPriemEditForm').getForm(),
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
						win.findById('EPSPEF_EvnPS_IsZNOCheckbox').setValue(true);
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
			base_form = win.findById('EvnPSPriemEditForm').getForm(),
			person_id = base_form.findField('Person_id'),
			Evn_id = base_form.findField('EvnPS_id');
			
		if(base_form.findField('EvnPS_IsZNORemove').getValue() == '2') {
			Ext.getCmp('EPSPEF_BiopsyDatePanel').show();
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
		} else Ext.getCmp('EPSPEF_BiopsyDatePanel').hide();
	},

	wardOnSexFilter: function () {
		var base_form = this.findById('EvnPSPriemEditForm').getForm(),
			filterdate = null;
		if (base_form.findField('EvnPS_OutcomeDate').getValue()) {
			filterdate = Ext.util.Format.date(base_form.findField('EvnPS_OutcomeDate').getValue(), 'd.m.Y');
		}
		sw.Promed.LpuSectionWard.filterWardBySex({
			date: filterdate,
			LpuSection_id: base_form.findField('LpuSection_eid').getValue(),
			LpuSectionBedProfileLink_id: base_form.findField('LpuSectionBedProfileLink_id').getValue(),
			Sex_id: this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Sex_Code'),
			lpuSectionWardCombo: base_form.findField('LpuSectionWard_id'),
			win: this
		});
	},

	bedProfileByWardFilter: function () {
		var base_form = this.findById('EvnPSPriemEditForm').getForm(),
			filterdate = null;
		if (base_form.findField('EvnPS_OutcomeDate').getValue()) {
			filterdate = Ext.util.Format.date(base_form.findField('EvnPS_OutcomeDate').getValue(), 'd.m.Y');
		}

		var Person_Birthday = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday');
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
	
	changeZNO: function(options){
		if(getRegionNick()!='ekb') return;

		var win = this,
			base_form = win.findById('EvnPSPriemEditForm').getForm(),
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

		win.setDiagSpidComboDisabled();
	},
	
	checkAndOpenRepositoryObserv: function () {
		var win = this;
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var params = {
			action: 'add',
			useCase: 'evnpspriem',
			callback: function(data) {
				if (!data) return false;
				win.RepositoryObservData = data;
			},
			MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
			Person_id: base_form.findField('Person_id').getValue()
		};
		
		Ext.Ajax.request({
			callback: function(cbOptions, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj[0] && response_obj[0].RepositoryObserv_id) {
						params.hasPrev = true;
						params.PlaceArrival_id = response_obj[0].PlaceArrival_id;
						params.KLCountry_id = response_obj[0].KLCountry_id;
						params.Region_id = response_obj[0].Region_id;
						params.RepositoryObserv_arrivalDate = response_obj[0].RepositoryObserv_arrivalDate;
						params.TransportMeans_id = response_obj[0].TransportMeans_id;
						params.RepositoryObserv_TransportDesc = response_obj[0].RepositoryObserv_TransportDesc;
						params.RepositoryObserv_TransportPlace = response_obj[0].RepositoryObserv_TransportPlace;
						params.RepositoryObserv_TransportRoute = response_obj[0].RepositoryObserv_TransportRoute;
						params.RepositoryObserv_FlightNumber = response_obj[0].RepositoryObserv_FlightNumber;
						params.RepositoryObserv_IsCVIContact = response_obj[0].RepositoryObserv_IsCVIContact;
						params.RepositoryObesrv_contactDate = response_obj[0].RepositoryObesrv_contactDate || null;
						params.RepositoryObserv_Height = response_obj[0].RepositoryObserv_Height;
						params.RepositoryObserv_Weight = response_obj[0].RepositoryObserv_Weight;
						getWnd('swRepositoryObservEditWindow').show(params);
					} else {
						getWnd('swRepositoryObservEditWindow').show(params);
					}
				}
			},
			params: {
				Person_id: base_form.findField('Person_id').getValue()
			},
			url: '/?c=RepositoryObserv&m=findByPerson'
		});
	},

	doSave: function(options) {
		// options @Object
		// options.print @Boolean Вызывать печать КВС, если true
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}
		
        if ( typeof options != 'object' ) {
            options = new Object();
        }

		this.formStatus = 'save';
		this.checkTrauma();

		var base_form = this.findById('EvnPSPriemEditForm').getForm(),
            _this = this;

		// #145312 Обязательность полей «№ направления» и «Дата направления» при типе госпитализации «1. Планово»
		// ещё раз установка перед проверкой на всякий случай
		if(getRegionNick().inlist(['ufa']) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick') == 'plan'){
			base_form.findField('EvnDirection_Num').setAllowBlank(false);
			base_form.findField('EvnDirection_setDate').setAllowBlank(false);
		}

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnPSPriemEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if(getRegionNick().inlist(['vologda','msk','ufa'])){
			var blockPediculos = this.findById('EPSPEF_blockPediculos');
			if(blockPediculos.isVisible() && base_form.findField('isPediculos').getValue()){

				if(!base_form.findField('Pediculos_isSanitation').getValue()){
					Ext.Msg.alert(langs('Ошибка'), langs('У пациента обнаружен педикулёз, необходима санитарная обработка'));
					this.formStatus = 'edit';
					return false;
				}
				if(!options.pediculosPrint && !options.ignorePediculosPrint && base_form.findField('Pediculos_isPrint').getValue() != 2){
					Ext.MessageBox.show({
						title: 'Вопрос',
						msg: 'Распечатать уведомление в СЭС?',
						icon: Ext.MessageBox.QUESTION,
						buttons: {yes: 'Печать', cancel: 'Отмена'},
						fn: function(buttonId, text, obj) {
							this.formStatus = 'edit';
							if ( 'yes' == buttonId ) {
								// var buttomPediculosPrint = this.findById('EPSPEF_PediculosPrint');
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
				msg: langs('Случаи с результатом "313 Констатация факта смерти" не подлежат оплате по ОМС'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}*/

		// https://redmine.swan.perm.ru/issues/42421
		// https://redmine.swan.perm.ru/issues/76713

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

		/*
		#PROMEDWEB-13134  Регион: Вологда
		 */
		if (getRegionNick() == 'vologda') {
			base_form.findField('FamilyContact_FIO').setValue(base_form.findField('VologdaFamilyContact_FIO').getValue());
			base_form.findField('FamilyContact_Phone').setValue(base_form.findField('VologdaFamilyContact_Phone').getValue());
		}
		// -------------------------------------------------------------------------------------------------------------
		
		// -------------------------------------------------------------------------------------------------------------
		// --------------------------   #150913  -----------------------------------------------//
		if(getRegionNick() == 'kareliya'){        	
        	if(
        		base_form.findField('EvnPS_IsCont').getValue()==1 
        		&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms' 
        		&& base_form.findField('PrehospType_id').getFieldValue('PrehospType_Code') == 1
        		&& !base_form.findField('PrehospWaifRefuseCause_id').getValue()
        		&& (!base_form.findField('PrehospDirect_id').getValue() || !base_form.findField('EvnDirection_Num').getValue() || !base_form.findField('EvnDirection_setDate').getValue())
        	){
        		this.formStatus = 'edit';
        		sw.swMsg.alert('Сообщение', 
        			'При плановой госпитализации в круглосуточный стационар с видом оплаты ОМС и без перевода, начиная с 01.04.2012 поля <Номер направления> и <Дата направления> - обязательны к заполнению,'+
        			' поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ" или "Военкомат"» '
        		);
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
					this.formStatus = 'edit';
					return false;
				}
        	}
        }
		// -------------------------- end #150913 ----------------------------------------------//

		if(
			getRegionNick().inlist(['khak', 'adygeya'])
			&& base_form.findField('EvnPS_IsCont').getValue()==1
			&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'
			&& base_form.findField('PrehospType_id').getFieldValue('PrehospType_Code') == 1
			&& !base_form.findField('PrehospWaifRefuseCause_id').getValue()
			&& (!base_form.findField('PrehospDirect_id').getValue() || !base_form.findField('EvnDirection_Num').getValue() || !base_form.findField('EvnDirection_setDate').getValue())
		){
			this.formStatus = 'edit';
			sw.swMsg.alert('Сообщение',
				'При плановой госпитализации в круглосуточный стационар или дневной стационар с видом оплаты ОМС и без перевода, ' +
				'поля <Номер направления> и <Дата направления> - обязательны к заполнению, поле <Кем направлен> может принимать значение "Другое ЛПУ" или "Отделение ЛПУ"'
			);
			return false;
		}
		if (
			!getRegionNick().inlist([ 'buryatiya', 'pskov' ])
			&& Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) && Ext.isEmpty(base_form.findField('LpuSection_eid').getValue())
			&& !Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';

					if ( this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
						this.findById('EPSPEF_PriemLeavePanel').expand();
					}

					base_form.findField('EvnPS_OutcomeDate').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('При заполненной дате исхода из приемного отделения должен быть заполнен исход пребывания в приемном отделении (отказ) или отделение, куда пациент госпитализирован'),
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

					if ( this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
						this.findById('EPSPEF_PriemLeavePanel').expand();
					}

					base_form.findField('LeaveType_prmid').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('При заполненной дате исхода из приемного отделения должен быть заполнен исход пребывания в приемном отделении'),
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

					if ( this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
						this.findById('EPSPEF_PriemLeavePanel').expand();
					}

					base_form.findField('EvnPS_OutcomeDate').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('При заполненном исходе пребывания в приемном отделении должна быть заполнена дата исхода из приемного отделения'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var tmp_bool = (
			getRegionNick() != 'kz' &&
			!options.ignoreEmptyDiagDid &&
			base_form.findField('PrehospDirect_id').getValue() == 2 &&//в поле «Тип направления» значение «Другое МО»
			base_form.findField('EvnDirection_id').getValue() > 0 &&//есть ссылка на электронное направление
			!base_form.findField('Diag_did').getValue());//поле «Диагноз напр. учр-я» не заполнено

		if (tmp_bool) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						options.ignoreEmptyDiagDid = true;
						this.doSave(options);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Не указан диагноз направившего учреждения. Продолжить?'),
				title: langs('Вопрос')
			});
			return false;
			/*sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('Diag_did').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('При выбранном направлении поле "Основной диагноз направившего учреждения" обязательно для заполнения'),
				title: ERR_INVFIELDS_TIT
			});*/
		}

		if(!base_form.findField('Diag_did').getValue() && getRegionNick() == 'msk' && !options.ignoreEmptyDiagDid) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					options.ignoreEmptyDiagDid = true;
					this.doSave(options);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('Внимание! Не заполнен диагноз направившего учреждения'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if(!base_form.findField('Diag_did').getValue() && getRegionNick() == 'msk' && !options.ignoreEmptyDiagDid) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					options.ignoreEmptyDiagDid = true;
					this.doSave(options);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('Внимание! Не заполнен диагноз направившего учреждения'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'); // https://redmine.swan.perm.ru/issues/4549
        var priemDiag = (getGlobalOptions().check_priemdiag_allow && getGlobalOptions().check_priemdiag_allow=='1')
		tmp_bool = (priemDiag
            && !base_form.findField('LpuSection_pid').getValue() > 0
            && !base_form.findField('Diag_pid').getValue()
            && isUfa == false);
        tmp_bool = (getGlobalOptions().check_priemdiag_allow
            && base_form.findField('LpuSection_pid').getValue() > 0
            && !base_form.findField('Diag_pid').getValue());
		if ( tmp_bool ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('Diag_pid').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('При выбранном приемном отделении поле "Основной диагноз приемного отделения" обязательно для заполнения'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue();
		var evnps_setdate = base_form.findField('EvnPS_setDate').getValue();
		var evnps_settime = base_form.findField('EvnPS_setTime').getValue();
		var evnps_outcomedate = base_form.findField('EvnPS_OutcomeDate').getValue();
		var evnps_outcometime = base_form.findField('EvnPS_OutcomeTime').getValue();
		var evnps_familydate = base_form.findField('FamilyContact_msgDate').getValue();
		var evnps_familytime = base_form.findField('FamilyContact_msgTime').getValue();

		//yl:180378 контроль даты сообщения родственнику
		if (evnps_familydate) {
			if(!evnps_familytime)evnps_familytime='00:00';
			var familyDT = Date.parseDate(Ext.util.Format.date(evnps_familydate, 'd.m.Y') + ' ' + evnps_familytime, 'd.m.Y H:i');//Дата сообщения родственнику

			//yl: Если Дата сообщения родственнику меньше Дата поступления
			if(evnps_setdate){
				if(!evnps_settime)evnps_settime='00:00';
				var setDT = Date.parseDate(Ext.util.Format.date(evnps_setdate, 'd.m.Y') + ' ' + evnps_settime, 'd.m.Y H:i');//Дата поступления
				if (familyDT < setDT) {
					this.formStatus = 'edit';
					Ext.Msg.alert(langs('Ошибка'), langs('Дата и время сообщения родственнику не может быть раньше даты и времени поступления'));
					return false;
				}
			}

			//yl: выбран отказ и установлено время отказа
			var rc_combo = this.findById('EPSPEF_PrehospWaifRefuseCause_id');//Отказ
			if(rc_combo.getValue() && evnps_outcomedate){
				if(!evnps_outcometime)evnps_outcometime='00:00';
				var outDT = Date.parseDate(Ext.util.Format.date(evnps_outcomedate, 'd.m.Y')+' '+evnps_outcometime,'d.m.Y H:i');
				if (familyDT > outDT) {
					this.formStatus = 'edit';
					Ext.Msg.alert(langs('Ошибка'), langs('Дата и время сообщения родственнику должны быть меньше даты и времени исхода пребывания в приемном отделении'));
					return false;
				}
			}
		}

		// Если дата направления больше даты госпитализации
		// @task https://redmine.swan.perm.ru/issues/137188
		if ( typeof evnps_setdate == 'object' && typeof EvnDirection_setDate == 'object' && EvnDirection_setDate.getTime() > evnps_setdate.getTime() ) {
			this.formStatus = 'edit';
			Ext.Msg.alert(langs('Ошибка'), langs('Дата выписки направления позже даты поступления пациента в стационар. Дата направления должна быть раньше или совпадать с датой начала лечения. Проверьте дату направления и/или дату госпитализации'));
			return false;
		}

		if ( evnps_setdate && evnps_outcomedate ) {
			if (!evnps_settime) evnps_settime = '00:00';
			if (!evnps_outcometime) evnps_outcometime = '00:00';

			var setDT = Date.parseDate(Ext.util.Format.date(evnps_setdate, 'd.m.Y') + ' ' + evnps_settime, 'd.m.Y H:i');
			var outDT = Date.parseDate(Ext.util.Format.date(evnps_outcomedate, 'd.m.Y') + ' ' + evnps_outcometime, 'd.m.Y H:i');

			if ( outDT < setDT ) {
				this.formStatus = 'edit';
				sw.swMsg.alert(
					langs('Ошибка'),
					langs('Дата и время поступления в стационар') + ' ' + setDT.format('d.m.Y H:i') + ' ' + langs('позже даты и времени исхода пребывания в приемном отделении') + ' ' + outDT.format('d.m.Y H:i'),
					function() {
						base_form.findField('EvnPS_OutcomeDate').focus(false);
					}
				);
				return false;
			}
			if ( !Ext.isEmpty(evnps_outcomedate) && (evnps_outcomedate.getTime()-evnps_setdate.getTime())>86400000*(getRegionNick() == 'penza' ? 3 : 1) ) {
				this.formStatus = 'edit';
	            sw.swMsg.show({
	                buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('EvnPS_OutcomeDate').focus(false);
	                }.createDelegate(this),
	                icon: Ext.Msg.ERROR,
	                msg: 'Дата и время поступления в стационар '+setDT.format('d.m.Y H:i')+' раньше даты исхода из приемного отделения '+outDT.format('d.m.Y H:i')+' больше чем на ' + (getRegionNick() == 'penza' ? '3 суток' : 'сутки') + '.',
	                title: ERR_INVFIELDS_TIT
	            });
	            return false;
	        }
		}
		var Person_Birthday = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		if (evnps_setdate && Person_Birthday) {
			var age = swGetPersonAge(Person_Birthday, evnps_setdate);
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
					msg: langs('Возрастная группа отделения не соответствуют возрасту пациента. Продолжить?'),
					title: langs('Вопрос')
				});
				
				return false;
			}
		}

		var params = new Object();

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

		if ( base_form.findField('PrehospArrive_id').disabled ) {
			params.PrehospArrive_id = base_form.findField('PrehospArrive_id').getValue();
		}

		if ( base_form.findField('PrehospDirect_id').disabled ) {
			params.PrehospDirect_id = base_form.findField('PrehospDirect_id').getValue();
		}

		if ( base_form.findField('Diag_did').disabled ) {
			params.Diag_did = base_form.findField('Diag_did').getValue();
		}

		if ( base_form.findField('EvnPS_IsPLAmbulance').disabled ) {
			params.EvnPS_IsPLAmbulance = base_form.findField('EvnPS_IsPLAmbulance').getValue();
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

		if ( base_form.findField('Diag_spid').disabled ) {
			params.Diag_spid = base_form.findField('Diag_spid').getValue();
		}

		params.LpuSection_pid = base_form.findField('LpuSection_pid').getValue();
		params.EvnDirection_setDate = Ext.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'd.m.Y');

		params.EvnPS_disDate = null;
		params.EvnPS_disTime = null;
		
		params.TimetableStac_id = this.TimetableStac_id;

		params.RepositoryObservData = Ext.util.JSON.encode(this.RepositoryObservData);

        //Если "Госпитализирован в" и Дата исхода не совпадает с отделением и датой поступления в первом движении выводим предупреждение
        if (!options.ignoreOutcomeAndAction
            && !Ext.isEmpty(base_form.findField('EvnPS_id').getValue())
            && !Ext.isEmpty(base_form.findField('LpuSection_eid').getValue())
            && !Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())
        ) {
            Ext.Ajax.request({
                callback: function(cbOptions, success, response) {
                    if ( success ) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        if (response_obj[0].ignoreOutcomeAndAction) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.YESNO,
                                fn: function(buttonId, text, obj) {
                                    _this.formStatus = 'edit';

                                    if ( 'yes' == buttonId ) {
                                        options.ignoreOutcomeAndAction = true;
                                        _this.doSave(options);
                                    } else {
                                        _this.buttons[0].focus();
                                    }
                                },
                                icon: Ext.MessageBox.QUESTION,
                                msg: langs('Обнаружено первое движение с отличающимися отделением и/или датой исхода. Продолжить сохранение?'),
                                title: langs('Вопрос')
                            });
                        } else {
                            _this.formStatus = 'edit';
                            options.ignoreOutcomeAndAction = true;
                            _this.doSave(options);
                        }
                    } else {
                        _this.formStatus = 'edit';
                        sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при проверке даты и отделения в движении.'));
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


		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение поступления в приемное отделение..." });
		loadMask.show();

		if ( this.findById('EPSPEF_EvnPS_IsZNOCheckbox').getValue() == true ) {
			base_form.findField('EvnPS_IsZNO').setValue(2);
		}
		else {
			base_form.findField('EvnPS_IsZNO').setValue(1);
		}

        if ( base_form.findField('LpuSection_eid').disabled ) {
            params.LpuSection_eid = base_form.findField('LpuSection_eid').getValue();
        }
        if ( options && typeof options.openChildWindow == 'function' && this.action == 'add') {
            params.isAutoCreate = 1;
        }

		params.vizit_direction_control_check = (options && !Ext.isEmpty(options.vizit_direction_control_check) && options.vizit_direction_control_check === 1) ? 1 : 0;
		params.ignoreEvnPSDoublesCheck = (options && !Ext.isEmpty(options.ignoreEvnPSDoublesCheck) && options.ignoreEvnPSDoublesCheck === 1) ? 1 : 0;
		params.ignoreEvnPSTimeDeseaseCheck = (!Ext.isEmpty(options.ignoreEvnPSTimeDeseaseCheck) && options.ignoreEvnPSTimeDeseaseCheck === 1) ? 1 : 0;
		params.ignoreEvnPSHemoDouble = (!Ext.isEmpty(options.ignoreEvnPSHemoDouble) && options.ignoreEvnPSHemoDouble === 1) ? 1 : 0;
		params.ignoreEvnPSHemoLong = (!Ext.isEmpty(options.ignoreEvnPSHemoLong) && options.ignoreEvnPSHemoLong === 1) ? 1 : 0;
		params.ignoreMorbusOnkoDrugCheck = (!Ext.isEmpty(options.ignoreMorbusOnkoDrugCheck) && options.ignoreMorbusOnkoDrugCheck === 1) ? 1 : 0;
		params.ignoreCheckMorbusOnko = (!Ext.isEmpty(options.ignoreCheckMorbusOnko) && options.ignoreCheckMorbusOnko === 1) ? 1 : 0;

		if(getRegionNick().inlist(['vologda','msk','ufa']) && Ext.getCmp('EPSPEF_blockPediculos').isVisible()){
			params.Pediculos_id =  base_form.findField('Pediculos_id').getValue();
			params.Pediculos_isPrint = (options.pediculosPrint) ? 2 : base_form.findField('Pediculos_isPrint').getValue();
		}


		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Alert_Msg ) {
						if ( 'YesNo' == action.result.Error_Msg ) {
							var msg = getMsgForCheckDoubles(action.result);

							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										if (action.result.Error_Code == 106) {
											options.ignoreMorbusOnkoDrugCheck = 1;
										}
										if (action.result.Error_Code == 112) {
											options.vizit_direction_control_check = 1;
										}
										if (action.result.Error_Code == 113) {
											options.ignoreEvnPSDoublesCheck = 1;
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
									else {
										base_form.findField('EvnSection_setDate').focus(true);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: msg,
								title: langs(' Продолжить сохранение?')
							});
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										if (action.result.Error_Code == 102) {
											//options.ignoreUslugaComplexTariffCountCheck = 1;
										}

										this.doSave(options);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: action.result.Alert_Msg,
								title: langs(' Продолжить сохранение?')
							});
						}
					} else if ( action.result.Error_Msg ) {
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg, function() {
							switch ( action.result.Error_Code ) {
								case 1: // Дублирование номера карты
									base_form.findField('EvnPS_NumCard').focus(true);
								break;
							}
						});
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnPS_id ) {
						var evn_ps_id = action.result.EvnPS_id;

						base_form.findField('EvnPS_id').setValue(evn_ps_id);

						if ( options && typeof options.openChildWindow == 'function' /* && this.action == 'add' */ ) {
							options.openChildWindow();
						}
						else {
							var date = null;
							var person_information = this.findById('EPSPEF_PersonInformationFrame');
							var response = new Object();

							response.EvnPS_id = evn_ps_id;
							/*
							response.LpuSection_Name = lpu_section_name;
							response.EvnPS_disDate = Date.parseDate(evn_ps_dis_date, 'd.m.Y');
							
							response.Diag_Name = base_form.findField('Diag_pid').getRawValue();
							response.EvnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue();
							response.EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue();
							response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
							response.Person_Firname = person_information.getFieldValue('Person_Firname');
							response.Person_id = base_form.findField('Person_id').getValue();
							response.Person_Secname = person_information.getFieldValue('Person_Secname');
							response.Person_Surname = person_information.getFieldValue('Person_Surname');
							response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
							response.Server_id = base_form.findField('Server_id').getValue();
							*/

							this.callback({ evnPSData: response });

							if ( action.result.Alert_Msg ) {
								sw.swMsg.alert(langs('Предупреждение'), action.result.Alert_Msg);
							}

							if ( options && options.print == true ) {
								var params = {};
								params.EvnPS_id = evn_ps_id;
								printEvnPS(params);

								this.action = 'edit';
								//this.setTitle(WND_HOSP_EPSEDIT);
							} else if(options.pediculosPrint){
								this.pediculosPrint(false);
							}else if (options.printRefuse) {
								//this.hide();
								printBirt({
									'Report_FileName': 'printEvnPSPrehospWaifRefuseCause.rptdesign',
									'Report_Params': '&paramEvnPsID=' + evn_ps_id,
									'Report_Format': 'pdf'
								});
								printBirt({
									'Report_FileName': 'printPatientRefuse.rptdesign',
									'Report_Params': '&paramEvnPsID=' + evn_ps_id,
									'Report_Format': 'pdf'
								});
							}

							if (
								!options 
								|| options.ignorePediculosPrint 
								|| (!options.print && !options.printRefuse && !options.pediculosPrint)
							) {
								this.hide();
							}
						}
						if( !getRegionNick().inlist([ 'kz' ]) && _this.getOKSDiag()) {
							_this.saveInBskRegistry();
						}
						if(getRegionNick() == 'ufa' && _this.getONMKDiag()) {
							_this.saveOnmkFromKvc(_this.getONMKDiag(),  '', '', '', '', '');
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}.createDelegate(this)
		});
	},
	/*enableEdit: function(enable) {
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
	
		var form_fields = new Array(
			'EvnPS_IsWithoutDirection',
			'Diag_did',
			'DiagSetPhase_did',
			'EvnPS_PhaseDescr_did',
			'EvnPS_IsPLAmbulance',
			'PrehospWaifRefuseCause_id',
			'UslugaComplex_id',
			'LpuSectionProfile_id',
			'EvnPS_IsTransfCall',
			'EvnPS_IsWaif',
			'PrehospWaifArrive_id',
			'PrehospWaifReason_id',
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
			'Okei_id',
			'LpuSection_did',
			'LpuSection_eid',
			//'LpuSection_pid',
			'MedStaffFact_pid',
			'Org_did',
			'PayType_id',
			'PrehospArrive_id',
			'PrehospDirect_id',
			'PrehospToxic_id',
			'PrehospTrauma_id',
			'PrehospType_id'
		);
		for ( var i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				this.setEnableField(form_fields[i]);
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
	loadSpecificsTree: function () {
		var tree = this.findById(this.id + '_SpecificsTree');
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
		var evn_ps_num_field = this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_NumCard');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение номера карты выбывшего из стационара..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					evn_ps_num_field.setValue(response_obj.EvnPS_NumCard);
					evn_ps_num_field.focus(true);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении номера КВС'));
				}
			},
			url: '/?c=EvnPS&m=getEvnPSNumber'
		});
	},
	IshemiaCode: ['I20','I21', 'I22', 'I23', 'I24', 'I25'],
	OksDiagCode: ['I20.0','I21.0','I21.1','I21.2','I21.3','I21.4','I21.9','I22.0','I22.1','I22.8','I22.9','I24.1'], //['I20.0','I21.0','I21.1','I21.2','I21.3','I21.4','I21.9','I22.0','I22.1','I22.8','I22.9','I24.0','I24.1','I24.8','I24.9'],
	ONMKDiagCode: ['G45','I60','I61','I62','I63','I64'],
	isValidTltUslugaDT: function() {
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
	loadUslugaGrid: function (){
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var evn_ps_id = base_form.findField('EvnPS_id').getValue();
		var usluga_panel = this.findById('EPSPEF_EvnUslugaPanel');
		var uslugaGridStore = this.findById('EPSPEF_EvnUslugaGrid').getStore();
		if(!usluga_panel.isLoaded){
			uslugaGridStore.load({ params: { pid: evn_ps_id } });
			uslugaGridStore.on('load',function(){ usluga_panel.isLoaded = true; });
		}
	},
	loadECGResult: function() {
		var uslugaGridStore = this.findById('EPSPEF_EvnUslugaGrid').getStore();
		var uslugaRowIndex = uslugaGridStore.findBy(function(rec){
			return rec.get('Usluga_Code') == 'A05.10.006';
		});
		var uslugaRow = uslugaGridStore.getAt(uslugaRowIndex);
		if(uslugaRow)
			this.findById(this.id + '_ECGResult').getStore().load({ 'params' : { 'EvnUsluga_id' : uslugaRow.get('EvnUsluga_id') } });
		else 
			return '';
	},
	getOKSDiag: function() {
		var DiagHospCombo  = this.findById('EPSPEF_DiagHospCombo');
		var DiagRecepCombo = this.findById('EPSPEF_DiagRecepCombo');
		if(DiagHospCombo.getCode().inlist(this.OksDiagCode))
			return { id: DiagHospCombo.getValue(),  name: DiagHospCombo.getRawValue() };
		else if (DiagRecepCombo.getCode().inlist(this.OksDiagCode))
			return { id: DiagRecepCombo.getValue(), name: DiagRecepCombo.getRawValue() };
		return false;
	},
	getONMKDiag: function() {
		var DiagRecepCombo = this.findById('EPSPEF_DiagRecepCombo');
		//var DiagRecepCombo = this.findById('EPSPEF_DiagRecepCombo');
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
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var person_id = base_form.findField('Person_id').getValue();
		var evnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue();
		var evnPS_id = base_form.findField('EvnPS_id').getValue();
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
		var diagDir = base_form.findField('EPSPEF_DiagHospCombo').getRawValue();
		var diagPriem = base_form.findField('EPSPEF_DiagRecepCombo').getRawValue();
		var LpuSectionHosp = base_form.findField('EPSPEF_LpuSectionCombo').getRawValue();
		var kagDT = getUslugaDT(KAGUslugaCode) ? getUslugaDT(KAGUslugaCode).format('d-m-Y H:i'): '';
		var PainDT = new Date(getPainDT()).format('d-m-Y H:i');
		var params_OKS = {
			'Registry_method'     : 'ins',
			'Person_id'           : person_id,
			'MorbusType_id'       : 19,
			'Diag_id'             : diag.id,
			'DiagOKS'             : diag.name,
			'EvnPS_NumCard'       : evnPS_NumCard,
			'EvnPS_id'            : evnPS_id,
			'PainDT'              : PainDT,
			'ECGDT'               : ecgDT,
			'ResultECG'           : EcgResult,
			'TLTDT'               : tltDT,
			'LpuDT'               : evnPsSetDT,
			'MOHospital'          : getGlobalOptions().lpu_nick,
			'ZonaCHKV'            : CHKVUslugaDT,
			'TimeFromEnterToChkv' : timeFromEnterToCHKV,
			'diagDir'             : diagDir,
			'diagPriem'           : diagPriem,
			'LpuSection'          : LpuSectionHosp,
			'KAGDT'               : kagDT
		};

		function getUslugaDT(Code) {
			var uslugaGridStore = wnd.findById('EPSPEF_EvnUslugaGrid').getStore();

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
		// Основной диагноз из КВС #161793#note-26
		function getDiagFromEvnPS(params_OKS){
			Ext.Ajax.request({
				params: params_OKS,
				url: '/?c=BSK_Register_User&m=getDiagFromEvnPS',
				success: function (response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj.length > 0){
						params_OKS.DiagOKS = response_obj[0].Diag_FullName;
					} else {
						params_OKS.DiagOKS = null;
					}
					checkLpuType(params_OKS);
					return true;
				}
			});
		};

		Ext.Ajax.request({
			params: {
				'Person_id' 	: person_id,
				'EvnPS_NumCard' : evnPS_NumCard
			},
			url: '/?c=BSK_RegisterData&m=getOksId',
			success: function (response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj.length > 0){
					if(!Ext.isEmpty(response_obj[0].Person_deadDT))
						return false;
					if(!Ext.isEmpty(response_obj[0].BSKRegistry_id))
						params_OKS.Registry_method = response_obj[0].BSKRegistry_id;
				}
				getDiagFromEvnPS(params_OKS);
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
	saveOnmkFromKvc: function(diag, rankinScale_id, rankinScale_sid, evnSection_InsultScale, leaveType_id, evn_section_id) {
	
		var wnd = this;
		
		//Услуги ТЛТ
		var TLTUslugaCode = ['A16.23.034.011','A16.23.034.012','A25.30.036.002','A25.30.036.003'];
		
		//Услуги КТ
		var KTUslugaCode = ['A06.03.058.003', 'A06.03.058.004', 'A06.03.058.005', 'A06.04.017.002', 'A06.04.017.003', 'A06.04.017.004', 'A06.04.017.005', 'A06.04.017.006', 'A06.04.017.007',
							'A06.12.050.001', 'A06.12.050.002', 'A06.23.004.006', 'A06.23.004.007'];//???уточнить все коды
		
		//Услуги МРТ
		var MRTUslugaCode = ['A05.03.002.002', 'A05.03.002.003', 'A05.03.002.004', 'A05.04.001.002', 'A05.04.001.003', 'A05.04.001.004', 'A05.04.001.005', 'A05.04.001.006', 'A05.04.001.007',
							'A05.12.004.001', 'A05.12.004.002', 'A05.23.009.017', 'A05.23.009.018', 'A05.30.002', 'A05.30.003.001', 'A17.20.001', 'A22.20.006.001'];//???уточнить все коды
		
		//var base_form = this.findById('EvnPSEditForm').getForm();
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		
		console.log('base_form');
		//console.log(base_form.findField('EvnPSEditWindow_LpuSectionRecCombo').getValue());
		console.log(base_form.findField('LpuSection_pid').getValue());
		console.log(base_form.findField('MedStaffFact_pid').getValue());
		var person_id = base_form.findField('Person_id').getValue();
		//№ медицинской карты
		var evnPS_NumCard = base_form.findField('EvnPS_NumCard').getValue();
		
		//Дата поступления
		var evnPsSetDT = base_form.findField('EvnPS_setDate').getValue().format('Y-m-d') + ' ' + base_form.findField('EvnPS_setTime').getValue();
				
		var tltDT = getUslugaDT(TLTUslugaCode) ? getUslugaDT(TLTUslugaCode).format('Y-m-d H:i:s') : '';
		var ktDT = getUslugaDT(KTUslugaCode) ? getUslugaDT(KTUslugaCode).format('Y-m-d H:i:s') : '';
		var mrtDT = getUslugaDT(MRTUslugaCode) ? getUslugaDT(MRTUslugaCode).format('Y-m-d H:i:s') : '';		

		var timeFromEnterToCHKV; 		//timeFromEnterToCHKV разница в минутах
		console.log('evnPsSetDT');
		console.log(evnPsSetDT);
		evnPsSetDT = new Date(evnPsSetDT).format('Y-m-d H:i:s');
				
		//Дата госпитализации
		var evnPSEditWindowEvnPS_setDate = base_form.findField('EvnPSPriemEditWindowEvnPS_setDate').getValue();
		var evnPSEditWindowEvnPS_setTime = base_form.findField('EvnPSPriemEditWindowEvnPS_setTime').getValue();		

		var params_ONMK = {
			'Registry_method'     : 'ins',
			'Person_id'           : person_id,
			'MorbusType_name'     : 'onmk',
			'Diag_id'             : diag.id,
			'Diag_Name'           : diag.name,
			'EvnPS_NumCard'       : evnPS_NumCard,			
			'PainDT'              : getPainDT(), //дата начала заболевания
			'MRTDT'               : mrtDT,
			'KTDT'				  : ktDT,
			'TLTDT'               : tltDT,
			'LpuDT'               : evnPsSetDT,//дата поступления
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
			                                    
			var uslugaGridStore = wnd.findById('EPSPEF_EvnUslugaGrid').getStore();

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
			
			var base_form = wnd.findById('EvnPSPriemEditForm').getForm();
			
			var EvnPsSetDate = base_form.findField('EvnPS_setDate').getValue();
			var EvnPsSetTime =  base_form.findField('EvnPS_setTime').getValue();
			if(EvnPsSetDate && EvnPsSetTime){
				var painDT = new Date(EvnPsSetDate.format('Y-m-d') + ' ' + EvnPsSetTime);
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
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function() {
					var base_form = this.findById('EvnPSPriemEditForm').getForm();

					if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
						this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
						this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
						this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
						this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
						if ( !base_form.findField('Diag_pid').disabled ) {
							base_form.findField('Diag_pid').focus(true);
						}
						else {
							base_form.findField('MedStaffFact_pid').focus(true);
						}
					}
					else if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
						this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
						this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
					}
					else if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
						base_form.findField('EvnPS_IsDiagMismatch').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
					/*
					else if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
						
					}
					*/
				}.createDelegate(this),
				tabIndex: TABINDEX_EPSPEF + 81,
				text: BTN_FRMSAVE
			}, { // Petrov
			    onShiftTabAction: function () {
			        var base_form = this.findById('EvnPSPriemEditForm').getForm();

			        if (this.action != 'view') {
			            this.buttons[0].focus();
			        }
			        else if (!this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0) {
			            this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
			            this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
			        }
			        else if (!this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0) {
			            this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
			            this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
			        }
			        else if (!this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0) {
			            this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
			            this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
			        }
			        else {
			            this.buttons[this.buttons.length - 1].focus();
			        }
			    }.createDelegate(this),
			    tabIndex: TABINDEX_EPSPEF + 82,
			    text: langs('Печать'),
			    iconCls: 'print16',
				menu: [{
					text: langs(' Форма 066/у-02'),
					handler: function () {
						this.printEvnPS();
					}.createDelegate(this)
				}]
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabAction: function() {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EPSPEF + 83,
				text: BTN_FRMCANCEL
			}],
			items: [ 
			new sw.Promed.PersonInfoPanel({
				button1OnHide: function() {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_NumCard').focus(true);
					}
				}.createDelegate(this),
				button2Callback: function(callback_data) {
					var form = this.findById('EvnPSPriemEditForm');

					form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
					form.getForm().findField('Server_id').setValue(callback_data.Server_id);

					this.findById('EPSPEF_PersonInformationFrame').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
				}.createDelegate(this),
				button2OnHide: function() {
					this.findById('EPSPEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button3OnHide: function() {
					this.findById('EPSPEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button4OnHide: function() {
					this.findById('EPSPEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button5OnHide: function() {
					this.findById('EPSPEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				collapsible: true,
				collapsed: true,
				floatable: false,
				id: 'EPSPEF_PersonInformationFrame',
				region: 'north',
				plugins: [ Ext.ux.PanelCollapsedTitle ],
				title: langs('Загрузка...'),
				listeners:{
					'render': function(panel) {
						if (panel.header)
						{
							panel.header.on('click',panel.toggleCollapse,panel,false);
						}
					}.createDelegate(this)
				},
				titleCollapse: true
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnPSPriemEditForm',
				labelAlign: 'right',
				labelWidth: 180,
				items: [{
					name: 'EvnPS_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name:'Lpu_id',
					xtype:'hidden'
				}, {
					name: 'EvnDie_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'from',
					value: 'workplacepriem',
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnQueue_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnLeave_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'LeaveType_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnOtherLpu_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnOtherSection_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnOtherSectionBedProfile_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnOtherStac_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PrehospStatus_id',
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
					name: 'EvnPS_IsZNO',
					xtype: 'hidden'
				}, {
					name: 'EvnPS_IsZNORemove',
					xtype: 'hidden'
				}, {
					name: 'EvnSection_id',
					xtype:'hidden'
				},
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPSPEF_HospitalisationPanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							// this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_IsCont').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('1. Госпитализация'),
					items: [{
						allowBlank: false,
						fieldLabel: langs('Переведен'),
						hiddenName: 'EvnPS_IsCont',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnPSPriemEditForm').getForm();
								var prehosp_direct_field = base_form.findField('PrehospDirect_id');
								//var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
								var record = combo.getStore().getById(newValue);

								var prehosp_direct_id = prehosp_direct_field.getValue();

								prehosp_direct_field.clearValue();
								prehosp_direct_field.getStore().clearFilter();

								if ( record ) {
									switch ( Number(record.get('YesNo_Code')) ) {
										case 0:
											//this.setEnableField('EvnPS_IsWithoutDirection');
										break;

										case 1:
											prehosp_direct_field.getStore().filterBy(function(rec) {
												if ( rec.get('PrehospDirect_Code').toString().inlist([ '1', '2' ])) {
													return true;
												}
												else {
													return false;
												}
											});

											//iswd_combo.setValue(1);
											//iswd_combo.disable();
										break;
									}
								}

								prehosp_direct_field.getStore().each(function(rec) {
									if ( rec.get('PrehospDirect_id') == prehosp_direct_id ) {
										prehosp_direct_field.setValue(prehosp_direct_id);
									}
								});

								//prehosp_direct_field.fireEvent('change', prehosp_direct_field, prehosp_direct_field.getValue());
								base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), base_form.findField('EvnPS_setDate').getValue());
							}.createDelegate(this),
							'keydown': function(inp, e) {
								if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EPSPEF + 1,
						value: 1,
						width: 70,
						xtype: 'swyesnocombo'
					}, {
						allowBlank: false,
						autoCreate: { tag: "input", type: "text", maxLength: "50", autocomplete: "off" },
						enableKeyEvents: true,
						fieldLabel: langs('№ медицинской карты'),
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
						tabIndex: TABINDEX_EPSPEF + 2,
						triggerClass: 'x-form-plus-trigger',
						validateOnBlur: false,
						width: 300,
						xtype: 'trigger'
					},{
						border: false,
						layout: 'form',
						width: 400,
						items: [{
							fieldLabel: 'Вид транспортировки',
							hiddenName: 'LpuSectionTransType_id',
							tabIndex: this.tabindex + 21,
							xtype: 'swlpusectiontranstypecombo'
						}]
					}, {
						allowBlank: false,
						useCommonFilter: true,
						tabIndex: TABINDEX_EPSPEF + 3,
						width: 300,
						xtype: 'swpaytypecombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if ( getRegionNick() == 'ekb' ) {
								var wnd = this;	
							
							var base_form = this.findById('EvnPSPriemEditForm').getForm();
							var uslugacomplex_combo = base_form.findField('UslugaComplex_id');
							
							if ( getRegionNick().inlist([ 'ekb' ]) ) {
								uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
								uslugacomplex_combo.getStore().removeAll();
								uslugacomplex_combo.clearValue();
								uslugacomplex_combo.getStore().baseParams.query = '';
								if (combo.getFieldValue('PayType_SysNick') == 'bud') {
									uslugacomplex_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([350]);
								} else {
									uslugacomplex_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300, 301]);
								}
							}
									this.filterLpuSectionProfile();
								}
							}.createDelegate(this)
						}
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								fieldLabel: langs('Дата поступления'),
								format: 'd.m.Y',
								listeners: {
									'change': function(field, newValue, oldValue) {
										if (blockedDateAfterPersonDeath('personpanelid', 'EPSPEF_PersonInformationFrame', field, newValue, oldValue)) return;
										var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
										var base_form = this.findById('EvnPSPriemEditForm').getForm();

										var EvnPS_OutcomeDate = base_form.findField('EvnPS_OutcomeDate').getValue();
										var lpu_section_did = base_form.findField('LpuSection_did').getValue();
										var lpu_section_pid = base_form.findField('LpuSection_pid').getValue();
										var med_staff_fact_pid = base_form.findField('MedStaffFact_pid').getValue();

										base_form.findField('LpuSection_did').clearValue();
										base_form.findField('LpuSection_pid').clearValue();

										var age;
										var WithoutChildLpuSectionAge = false;
										var Person_Birthday = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday');
												
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
										} else {
											base_form.findField('MedStaffFact_pid').clearValue();
										}
										win.setMKB();
										if (getRegionNick() == 'perm') {
											win.filterLpuSectionProfile();
										}
										this.setPrehospArriveAllowBlank();
										this.setDiagEidAllowBlank();
										this.refreshFieldsVisibility(['TumorStage_id']);
									}.createDelegate(this)
								},
								name: 'EvnPS_setDate',
								id: this.id + 'EvnPS_setDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								selectOnFocus: true,
								tabIndex: TABINDEX_EPSPEF + 4,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							labelWidth: 50,
							layout: 'form',
							items: [{
								fieldLabel: langs('Время'),
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
									var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
										windowId: 'EvnPSPriemEditWindow'
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_EPSPEF + 5,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: langs('Кем направлен'),
						width: 730,
						xtype: 'fieldset',

						items: [{
							hiddenName: 'PrehospDirect_id',
							lastQuery: '',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = this.findById('EvnPSPriemEditForm').getForm();
									var isPerm = (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm');
									var omsSprTerrCode = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');
									var priemDiag = (getGlobalOptions().check_priemdiag_allow && getGlobalOptions().check_priemdiag_allow=='1');
									var evn_direction_set_date_field = base_form.findField('EvnDirection_setDate');
									var evn_direction_num_field = base_form.findField('EvnDirection_Num');
									var lpu_section_combo = base_form.findField('LpuSection_did');
									var org_combo = base_form.findField('Org_did');
									var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');

									var lpu_section_id = lpu_section_combo.getValue();

									base_form.findField('Diag_did').setAllowBlank(true);
									// base_form.findField('LpuSection_pid').setAllowBlank(true);
									// base_form.findField('MedStaffFact_pid').setAllowBlank(true);
									base_form.findField('LpuSection_pid').setAllowBlank(false);
									base_form.findField('MedStaffFact_pid').setAllowBlank(false);
                                    base_form.findField('Diag_pid').setAllowBlank(!priemDiag);

									base_form.findField('EvnDirection_id').setValue(0);
									evn_direction_set_date_field.setValue(null);
									evn_direction_num_field.setValue(null);
									lpu_section_combo.clearValue();
									org_combo.clearValue();
									org_combo.fireEvent('change', org_combo, org_combo.getValue());

									base_form.findField('MedStaffFact_did').setContainerVisible(false);
									base_form.findField('MedStaffFact_did').disable();
									base_form.findField('MedStaffFact_TFOMSCode').setContainerVisible(false);
									base_form.findField('MedStaffFact_TFOMSCode').disable();
									
									base_form.findField('LpuSection_did').disableLinkedElements();
									base_form.findField('MedStaffFact_did').disableParentElement();

									var record = base_form.findField('EvnPS_IsCont').getStore().getById(base_form.findField('EvnPS_IsCont').getValue());
									var evn_ps_is_cont = false;

									if ( record && record.get('YesNo_Code') == 1 ) {
										evn_ps_is_cont = true;
									}

									record = combo.getStore().getById(newValue);
									this.refreshFieldsVisibility(['LpuSection_did', 'Org_did', 'PrehospDirect_id']);

									if ( record == undefined || record == null ) {
										evn_direction_set_date_field.disable();
										evn_direction_num_field.disable()
										// lpu_section_combo.disable();
										// org_combo.disable();

										return false;
									}

									if ( record.get('PrehospDirect_Code') == 1 || record.get('PrehospDirect_Code') == 2 ) {
										this.setEnableField('EvnPS_IsWithoutDirection');
										evn_direction_set_date_field.enable();
										evn_direction_num_field.enable();
										iswd_combo.enable();
									} else if(getRegionNick() == 'kareliya'){
										var flag = (record.get('PrehospDirect_Code') == 4) ? false : true;
										if(!evn_direction_set_date_field.disabled) evn_direction_set_date_field.setAllowBlank(flag);
										if(!evn_direction_num_field.disabled) evn_direction_num_field.setAllowBlank(flag);
									}else {
										iswd_combo.setValue(1);
										iswd_combo.disable();
									}
									
									// https://redmine.swan.perm.ru/issues/4549
									if ( record.get('PrehospDirect_Code') && isPerm == true && omsSprTerrCode > 100 ) {
										base_form.findField('Diag_did').setAllowBlank(false);
									}

									switch ( Number(record.get('PrehospDirect_Code')) ) {
										case 1:
											if (getRegionNick().inlist([ 'ekb', 'perm' ]) && iswd_combo.getValue() == 1) {
												base_form.findField('MedStaffFact_did').setContainerVisible(true);
												base_form.findField('MedStaffFact_did').setDisabled( this.action == 'view' );

												if ( getRegionNick() == 'ekb' ) {
													base_form.findField('MedStaffFact_TFOMSCode').setContainerVisible(true);
													base_form.findField('MedStaffFact_TFOMSCode').setDisabled( this.action == 'view' );
												}

												base_form.findField('LpuSection_did').enableLinkedElements();
												base_form.findField('MedStaffFact_did').enableParentElement();

												this.loadMedStaffFactDidCombo();
											}
											if ( lpu_section_id ) {
												lpu_section_combo.setValue(lpu_section_id);
											}

											// lpu_section_combo.enable();
											// lpu_section_combo.setAllowBlank(false);
											// org_combo.disable();
											// org_combo.setAllowBlank(true);
										break;

										case 2:
											if (getRegionNick().inlist([ 'ekb', 'perm' ]) && iswd_combo.getValue() == 1) {
												base_form.findField('MedStaffFact_did').setContainerVisible(true);
												base_form.findField('MedStaffFact_did').setDisabled( this.action == 'view' );

												if ( getRegionNick() == 'ekb' ) {
													base_form.findField('MedStaffFact_TFOMSCode').setContainerVisible(true);
													base_form.findField('MedStaffFact_TFOMSCode').setDisabled( this.action == 'view' );
												}
											}

											// lpu_section_combo.disable();
											// lpu_section_combo.setAllowBlank(true);
											// org_combo.enable();
											// org_combo.setAllowBlank(false);
										break;

										case 3:
										case 4:
										case 5:
										case 6:
											evn_direction_set_date_field.enable();
											evn_direction_num_field.enable();
											// lpu_section_combo.disable();
											// lpu_section_combo.setAllowBlank(true);
											// org_combo.enable();
											// org_combo.setAllowBlank(true);

											// http://redmine.swan.perm.ru/issues/22684
											// Перенес выше тут https://redmine.swan.perm.ru/issues/77114
											if ( this.action == 'add' && Number(record.get('PrehospDirect_Code')) == 5 ) {
												base_form.findField('PrehospArrive_id').setFieldValue('PrehospArrive_Code', 2);
												base_form.findField('PrehospArrive_id').fireEvent('change', base_form.findField('PrehospArrive_id'), base_form.findField('PrehospArrive_id').getValue());
											}
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
									if(iswd_combo.getValue() == 2) {
										evn_direction_set_date_field.disable();
										evn_direction_num_field.disable()
										//lpu_section_combo.disable();
										//lpu_section_combo.setAllowBlank(true);
										//org_combo.disable();
										//org_combo.setAllowBlank(true);
									}
								}.createDelegate(this),
								'select': function(combo, record, index) {
									combo.fireEvent('change', combo, record.get(combo.valueField));
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EPSPEF + 6,
							width: 300,
							xtype: 'swprehospdirectcombo'
						},{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								width: 500,
								items: [
								new sw.Promed.SwYesNoCombo({
									fieldLabel: langs('С электронным направлением'),
									hiddenName: 'EvnPS_IsWithoutDirection',
									value: 2,
									allowBlank: false,
									tabIndex: TABINDEX_EPSPEF + 7,
									width: 60,
									listeners: 
									{
										'change': function (iswd_combo, newValue, oldValue) 
										{
											if ( this.action == 'view' ) {
												return false;
											}
											var base_form = this.findById('EvnPSPriemEditForm').getForm();
											if ( newValue == 2 ) {
												// поля заполняются из эл.направления
												base_form.findField('PrehospDirect_id').disable();
												base_form.findField('Diag_did').disable();
												base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue());
											}
											else {
												base_form.findField('EvnDirection_Num').enable();
												base_form.findField('EvnDirection_setDate').enable();
												base_form.findField('LpuSection_did').enable();
												base_form.findField('Org_did').enable();
												base_form.findField('Diag_did').enable();
												base_form.findField('PrehospDirect_id').enable();
												base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue());
											}
										}.createDelegate(this)
									}
								})]
							}]
						}, {
							hiddenName: 'LpuSection_did',
							id: this.id + 'LpuSectionDid',
							linkedElements: [
								this.id + 'MedStaffFactDid'
							],
							tabIndex: TABINDEX_EPSPEF + 8,
							width: 500,
							xtype: 'swlpusectionglobalcombo'
						}, {
							displayField: 'Org_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: langs('Организация'),
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
								var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
												Org_Name: org_data.Org_Name,
												Org_Nick: org_data.Org_Nick,
												OrgType_SysNick: org_data.OrgType_SysNick
											}]);
											combo.setValue(org_data.Org_id);
											combo.fireEvent('change', combo, combo.getValue());
											getWnd('swOrgSearchWindow').hide();
											combo.collapse();
										}
									}
								});
							}.createDelegate(this),
							onTrigger2Click: function() {
								if ( !this.disabled ) this.clearValue();

								var combo = this;
								combo.fireEvent('change', combo, combo.getValue());
							},
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'Org_id', type: 'int' },
									{ name: 'Org_Name', type: 'string' },
									{name: 'Org_Nick', type: 'string'},
									{name: 'OrgType_SysNick', type: 'string'}
								],
								key: 'Org_id',
								sortInfo: {
									field: 'Org_Name'
								},
								url: C_ORG_LIST
							}),
							tabIndex: TABINDEX_EPSPEF + 9,
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
							tabIndex: TABINDEX_EPSPEF + 9.1,
							fieldLabel: 'Направивший врач',
							hiddenName: 'MedStaffFact_did',
							id: this.id + 'MedStaffFactDid',
							listWidth: 650,
							parentElementId: this.id + 'LpuSectionDid',
							width: 500,
							xtype: 'swmedstafffactglobalcombo'
						}, {
							tabIndex: TABINDEX_EPSPEF + 9.2,
							fieldLabel: 'Код направившего врача',
							name: 'MedStaffFact_TFOMSCode',
							allowDecimals: false,
							allowNegative: false,
							autoCreate: {tag: "input", maxLength: "14", autocomplete: "off"},
							xtype: 'numberfield'
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: langs('№ направления'),
									maskRe: /[0-9]/,
									regex: /^[0-9]*$/,
									name: 'EvnDirection_Num',
									tabIndex: TABINDEX_EPSPEF + 10,
									autoCreate: {
										tag: "input", 
										type: "text", 
										maxLength: 16,
										autocomplete: "off"
									},
									width: 150,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								labelWidth: 200,
								layout: 'form',
								items: [{
									fieldLabel: langs('Дата направления'),
									format: 'd.m.Y',
									name: 'EvnDirection_setDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									tabIndex: TABINDEX_EPSPEF + 11,
									width: 100,
									xtype: 'swdatefield'
								}]
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0px;',
						title: langs('Кем доставлен'),
						width: 730,
						xtype: 'fieldset',

						items: [{
							allowBlank: getRegionNick() != 'krym',
							fieldLabel: langs('Кем доставлен'),
							hiddenName: 'PrehospArrive_id',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									win.setMedicalCareFormType();
									
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get(combo.valueField) == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
								},
								'select': function(combo, record, index) {
									var base_form = this.findById('EvnPSPriemEditForm').getForm();
									if(typeof record == 'object' && record.get('PrehospArrive_Code') != 2){
										base_form.findField('EvnPS_CodeConv').setValue('');
										base_form.findField('EvnPS_NumConv').setValue('');
									}
									if ( this.action == 'add' )
										base_form.findField('EvnPS_IsPLAmbulance').setValue(1);

									base_form.findField('CmpCallCard_id').hideContainer();
									if ( typeof record != 'object' || Ext.isEmpty(record.get('PrehospArrive_Code')) || record.get('PrehospArrive_Code') == 1 ) {
										base_form.findField('EvnPS_CodeConv').disable();
										base_form.findField('EvnPS_NumConv').disable();
										base_form.findField('EvnPS_IsPLAmbulance').disable();
									}
									else if ( typeof record == 'object' && record.get('PrehospArrive_Code') == 2 ) {
										base_form.findField('EvnPS_CodeConv').enable();
										base_form.findField('EvnPS_NumConv').enable();
										base_form.findField('EvnPS_IsPLAmbulance').enable();
										base_form.findField('CmpCallCard_id').showContainer();
										if ( this.action == 'add' && base_form.findField('PrehospDirect_id').getValue() == 5 )
											base_form.findField('EvnPS_IsPLAmbulance').setValue(2);
									}
									else {
										base_form.findField('EvnPS_CodeConv').enable();
										base_form.findField('EvnPS_NumConv').enable();
										base_form.findField('EvnPS_IsPLAmbulance').disable();
									}
								}.createDelegate(this),
								'render': function(combo) {
									combo.getStore().load();
								}
							},
							tabIndex: TABINDEX_EPSPEF + 12,
							width: 300,
							xtype: 'swprehosparrivecombo'
						}, {
							fieldLabel: 'Номер талона вызова',
							hiddenName: 'CmpCallCard_id',
							tabIndex: TABINDEX_EPSPEF + 12.2,
							width: 300,
							listWidth: 400,
							beforeLoadStore: function(store, options) {
								var base_form = this.findById('EvnPSPriemEditForm').getForm(),
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
							fieldLabel: langs('Код'),
							maxLength: 18,
							name: 'EvnPS_CodeConv',
							tabIndex: TABINDEX_EPSPEF + 13,
							width: 150,
							xtype: 'textfield'
						}, {
							fieldLabel: langs('Номер наряда'),
							maxLength: 10,
							name: 'EvnPS_NumConv',
							tabIndex: TABINDEX_EPSPEF + 14,
							width: 150,
							xtype: 'textfield'
						},{
							id: 'EPSPEF_EvnPS_IsPLAmbulance',
							comboSubject: 'YesNo',
							disabled: true,
							fieldLabel: langs('Талон передан на ССМП'),
							hiddenName: 'EvnPS_IsPLAmbulance',
							tabIndex: TABINDEX_EPSPEF + 15,
							width: 150,
							value: 1,
							xtype: 'swcommonsprcombo'
						}]
					}, new sw.Promed.swDiagPanel({
						labelWidth: 180,
						phaseDescrName: 'EvnPS_PhaseDescr_did',
						diagSetPhaseName: 'DiagSetPhase_did',
						diagPhaseFieldLabel: langs('Состояние пациента при направлении'),
						diagField: {
							checkAccessRights: true,
							MKB:null,
							// allowBlank: false,
							fieldLabel: langs('Диагноз напр. учр-я'),
							hiddenName: 'Diag_did',
							id: 'EPSPEF_DiagHospCombo',
							onChange: function(combo, newValue) {
								var base_form = this.findById('EvnPSPriemEditForm').getForm();

								if ( !newValue ) {
									return true;
								}

								base_form.findField('LpuSection_pid').fireEvent('change', base_form.findField('LpuSection_pid'), base_form.findField('LpuSection_pid').getValue());
							}.createDelegate(this),
							getCode: function(){
								var record = this.getStore().getById(this.getValue());
								return record != null ? record.get('Diag_Code'):'';
							},
							tabIndex: TABINDEX_EPSPEF + 16,
							width: 500,
							xtype: 'swdiagcombo'
						}
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
						title: langs('Дефекты догоспитального этапа'),
						width: 730,
						xtype: 'fieldset',

						items: [{
							allowBlank: false,
							fieldLabel: langs('Несвоевременность госпитализации'),
							hiddenName: 'EvnPS_IsImperHosp',
							tabIndex: TABINDEX_EPSPEF + 17,
							value: 1,
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							allowBlank: false,
							fieldLabel: langs('Недост. объем клинико-диаг. обследования'),
							hiddenName: 'EvnPS_IsShortVolume',
							tabIndex: TABINDEX_EPSPEF + 18,
							value: 1,
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							allowBlank: false,
							fieldLabel: langs('Неправильная тактика лечения'),
							hiddenName: 'EvnPS_IsWrongCure',
							tabIndex: TABINDEX_EPSPEF + 19,
							value: 1,
							width: 100,
							xtype: 'swyesnocombo'
						}, {
							allowBlank: false,
							fieldLabel: langs('Несовпадение диагноза'),
							hiddenName: 'EvnPS_IsDiagMismatch',
							listeners: {
								'keydown': function(inp, e) {
									var base_form = this.findById('EvnPSPriemEditForm').getForm();

									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
										e.stopEvent();

										if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
											this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
											this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
											base_form.findField('PrehospToxic_id').focus(true);
										}
										else if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
											this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
											this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
										}
										else if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {

										}
										else if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
											this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
											this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
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
							tabIndex: TABINDEX_EPSPEF + 20,
							value: 1,
							width: 100,
							xtype: 'swyesnocombo'
						}]
					}]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 125,
					id: 'EPSPEF_DirectDiagPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPSPEF_EvnDiagPSHospGrid').getStore().load({
									params: {
										'class': 'EvnDiagPSHosp',
										EvnDiagPS_pid: this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('2. Сопутствующие диагнозы направившего учреждения'),
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_diag_hosp',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnDiagPS_setDate',
							header: langs('Дата'),
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'DiagSetClass_Name',
							header: langs('Вид диагноза'),
							hidden: false,
							resizable: true,
							sortable: true,
							width: 200
						}, {
							dataIndex: 'Diag_Code',
							header: langs('Код диагноза'),
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Diag_Name',
							header: langs('Диагноз'),
							hidden: false,
							id: 'autoexpand_diag_hosp',
							resizable: true,
							sortable: true
						}],
						frame: false,
						height: 200,
						id: 'EPSPEF_EvnDiagPSHospGrid',
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

								var grid = Ext.getCmp('EPSPEF_EvnDiagPSHospGrid');

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
										var base_form = this.findById('EvnPSPriemEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
												base_form.findField('PrehospToxic_id').focus(true);
											}
											else if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {

											}
											else if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												this.buttons[0].focus();
											}
											else {
												this.buttons[1].focus();
											}
										}
										else {
											if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
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
									var evn_diag_ps_id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.grid.getTopToolbar();

									if ( selected_record ) {
										evn_diag_ps_id = selected_record.get('EvnDiagPS_id');
									}

									if ( evn_diag_ps_id ) {
										toolbar.items.items[1].enable();
										toolbar.items.items[2].enable();
										toolbar.items.items[3].enable();
									}
									else {
										toolbar.items.items[1].disable();
										toolbar.items.items[2].disable();
										toolbar.items.items[3].disable();
									}
								}
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
										LoadEmptyRow(this.findById('EPSPEF_EvnDiagPSHospGrid'));
									}

									// this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
									// this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
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
								text: langs('Добавить')
							}, {
								handler: function() {
									this.openEvnDiagPSEditWindow('edit', 'hosp');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: langs('Изменить')
							}, {
								handler: function() {
									this.openEvnDiagPSEditWindow('view', 'hosp');
								}.createDelegate(this),
								iconCls: 'view16',
								text: langs('Просмотр')
							}, {
								handler: function() {
									this.deleteEvent('EvnDiagPSHosp');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: langs('Удалить')
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPSPEF_AdmitDepartPanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							this.findById('EvnPSPriemEditForm').getForm().findField('PrehospToxic_id').focus(true);
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('3. Первичный осмотр'),
                    labelWidth: 200,
                    //Width: 250,
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
						}]
					}, {
						allowBlank: false,
						fieldLabel: langs('Тип госпитализации'),
						hiddenName: 'PrehospType_id',
						tabIndex: TABINDEX_EPSPEF + 32,
						width: 300,
						xtype: 'swprehosptypecombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								win.setMedicalCareFormType();

								var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
								var base_form = this.findById('EvnPSPriemEditForm').getForm();
								var priemDiag = (getGlobalOptions().check_priemdiag_allow && getGlobalOptions().check_priemdiag_allow=='1');
                                base_form.findField('Diag_pid').setAllowBlank(!priemDiag);
								if (isUfa) {
									if (combo.getFieldValue('PrehospType_Code') == 1) {
										base_form.findField('Diag_pid').setAllowBlank(true);
										// #145312 Обязательность полей «№ направления» и «Дата направления» при типе госпитализации «1. Планово»
										base_form.findField('EvnDirection_Num').setAllowBlank(false);
										base_form.findField('EvnDirection_setDate').setAllowBlank(false);
									} else {
										base_form.findField('Diag_pid').setAllowBlank(!priemDiag);

										base_form.findField('EvnDirection_Num').setAllowBlank(true);// и лучше выдумать не мог...
										base_form.findField('EvnDirection_setDate').setAllowBlank(true);// возможно, есть ещё условия для этих полей
									}
									this.refreshFieldsVisibility(['PrehospDirect_id']);
								}
							}.createDelegate(this),
							'select': function() {
								if ( getRegionNick() == 'buryatiya' ) {
									win.setMedicalCareFormType();
								}
							}.createDelegate(this)
						}
					}, {
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: langs('Количество госпитализаций'),
						minValue: 0,
						maxValue: 99,
						name: 'EvnPS_HospCount',
						tabIndex: TABINDEX_EPSPEF + 33,
						width: 100,
						xtype: 'numberfield'
					}, {
						layout: 'column',
						border: false,
						items: [{
							layout: 'form',
							border: false,
							items: [{
								fieldLabel: langs('Время с начала заболевания'),
								hiddenName: 'Okei_id',
								displayField: 'Okei_Name',
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'{Okei_Name}',
									'</div></tpl>'
								),
								tabIndex: TABINDEX_EPSPEF + 33.2,
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
								tabIndex: TABINDEX_EPSPEF + 33.4,
								width: 100,
								xtype: 'numberfield'
							}]
						}]
					}, {
						allowBlank: true,
						fieldLabel: langs('Случай запущен'),
						hiddenName: 'EvnPS_IsNeglectedCase',
						tabIndex: TABINDEX_EPSPEF + 35,
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
									decimalPrecision: 2,
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
									decimalPrecision: 2,
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
							decimalPrecision: 2,
							fieldLabel: 'Температура тела',
							minValue: 0,
							maxValue: 50,
							name: 'RepositoryObserv_TemperatureFrom',
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
						width: 850,
						xtype: 'fieldset',

						items: [{
							border: false,
							layout: 'column',
							items:[{
								border: false,
								layout: 'form',
								items:[new sw.Promed.SwPrehospTraumaCombo({
									hiddenName: 'PrehospTrauma_id',
									fieldLabel: 'Вид травмы (внешнего воздействия)',
									lastQuery: '',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnPSPriemEditForm').getForm();

											var is_unlaw_combo = base_form.findField('EvnPS_IsUnlaw');
											var record = combo.getStore().getById(newValue);

											if ( !record ) {
												is_unlaw_combo.clearValue();
												is_unlaw_combo.disable();
												is_unlaw_combo.setAllowBlank(true);
											}
											else {
												is_unlaw_combo.setValue(1);
												is_unlaw_combo.enable();
												is_unlaw_combo.setAllowBlank(false);
											}
													
											is_unlaw_combo.fireEvent('change', is_unlaw_combo, is_unlaw_combo.getValue());
										}.createDelegate(this)
									},
									tabIndex: TABINDEX_EPSPEF + 36,
									width: 300
								})]
							}, {
                                border: false,
								hidden: (getRegionNick() == 'kz'),
                                labelWidth: 120,
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
												this.findById('TraumaCircumEvnPS_setDT').setVisible((value !== '') ? true : false);
											}
										}.createDelegate(this),
										'select': function(cmp, value){
											if(!getRegionNick().inlist(['kz'])) {
												this.findById('TraumaCircumEvnPS_Name').setVisible((value !== '') ? true : false);
												this.findById('TraumaCircumEvnPS_setDT').setVisible((value !== '') ? true : false);
											}
										}.createDelegate(this)
									},
                                    tabIndex: TABINDEX_EPSPEF + 36.2,
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
							id: 'TraumaCircumEvnPS_setDT',
							items: [{
								border: false,
								layout: 'form',
								items: [{
										fieldLabel: langs('Дата, время получения травмы'),
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
										var base_form = this.findById('EvnPSPriemEditForm').getForm(),
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
							}],
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [ new sw.Promed.SwYesNoCombo({
									fieldLabel: langs('Противоправная'),
									hiddenName: 'EvnPS_IsUnlaw',
									lastQuery: '',
									tabIndex: TABINDEX_EPSPEF + 37,
									width: 70,
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnPSPriemEditForm').getForm();

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
												notificationDateField.setValue(this.evn_ps_notification_date);
												notificationTimeField.setValue(this.evn_ps_notification_time);
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
								labelWidth: 200,
								layout: 'form',
								items: [ new sw.Promed.SwYesNoCombo({
									fieldLabel: langs('Нетранспортабельность'),
									hiddenName: 'EvnPS_IsUnport',
									lastQuery: '',
									tabIndex: TABINDEX_EPSPEF + 38,
									width: 70
								})]
							}]
						}, {
							border: false,
							hidden: (getRegionNick() != 'kz'),
							layout: 'form',
							items: [{
								comboSubject: 'EntranceModeType',
								hiddenName: 'EntranceModeType_id',
								fieldLabel: langs('Вид транспортировки'),
								tabIndex: TABINDEX_EPSPEF + 39,
								width: 300,
								xtype: 'swcommonsprcombo'
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
									tabIndex: TABINDEX_EPSPEF + 39.2,
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
										var base_form = this.findById('EvnPSPriemEditForm').getForm(), 
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
									tabIndex: TABINDEX_EPSPEF + 39.4,
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
							tabIndex: TABINDEX_EPSPEF + 39.6,
							width: 500,
							xtype:'swmedstafffactglobalcombo'
						}, {
							fieldLabel: (getRegionNick() == 'kz' ? 'Сотрудник, принявший информацию' : 'Сотрудник МВД России, принявший информацию'),
							name: 'EvnPS_Policeman',
							tabIndex: TABINDEX_EPSPEF + 39.8,
							width: 500,
							xtype: 'textfield'
						}]
					}, {
						fieldLabel: langs('Приемное отделение'),
						hiddenName: 'LpuSection_pid',
                        bodyStyle: 'padding-top: 0.5em;',
                        style: 'margin-bottom: 0.5em;',
						id: 'EPSPEF_LpuSectionRecCombo',
						disabled: true,
						listeners: {
							'change': function(field, newValue, oldValue) {
								
								var base_form = this.findById('EvnPSPriemEditForm').getForm();
								var lpu_section_id = base_form.findField('LpuSection_eid').getValue();
								base_form.findField('LpuSection_eid').clearValue();

								if ( newValue ) {
									field.getStore().each(function(record) {
										if ( record.get('LpuSection_id') == newValue ) {
											var LpuUnitType_SysNick = record.get('LpuUnitType_SysNick');
											var filterList = {
												onDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y')
											};

											if ( LpuUnitType_SysNick.toString().inlist([ 'priem' ]) ) {
												filterList.arrayLpuUnitType = [ '2', '3', '4', '5' ];
											}
											else if ( LpuUnitType_SysNick.toString().inlist([ 'stac', 'dstac' ]) ) {
												filterList.arrayLpuUnitType = [ '2', '3' ];
											}
											else if ( LpuUnitType_SysNick.toString().inlist([ 'polka', 'hstac', 'pstac' ]) ) {
												filterList.arrayLpuUnitType = [ '4', '5' ];
											}

											setLpuSectionGlobalStoreFilter(filterList);
											base_form.findField('LpuSection_eid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
											if ( base_form.findField('LpuSection_eid').getStore().getById(lpu_section_id) ) {
												base_form.findField('LpuSection_eid').setValue(lpu_section_id);
											}
										}
									});
								}
							}.createDelegate(this)
						},
/*
						linkedElements: [
							'EPSPEF_MedStaffFactRecCombo'
						],
*/
						listWidth: 650,
						tabIndex: TABINDEX_EPSPEF + 40,
						width: 500,
						xtype: 'swlpusectionglobalcombo'
					}, {
						fieldLabel: langs('Врач'),
						hiddenName: 'MedStaffFact_pid',
						id: 'EPSPEF_MedStaffFactRecCombo',
						listWidth: 650,
						// parentElementId: 'EPSPEF_LpuSectionRecCombo',
						tabIndex: TABINDEX_EPSPEF + 41,
						width: 500,
						xtype: 'swmedstafffactglobalcombo'
					}, new sw.Promed.swDiagPanel({
						labelWidth: 200,
                        bodyStyle: 'padding-top: 0.5em;',
                        style: 'margin-bottom: 0.5em;',
						phaseDescrName: 'EvnPS_PhaseDescr_pid',
						diagSetPhaseName: 'DiagSetPhase_pid',
						diagPhaseFieldLabel: langs('Состояние пациента при поступлении'),
						diagField: {
							checkAccessRights: true,
							MKB:null,
							// allowBlank: false,
							fieldLabel: langs('Диагноз прием. отд-я'),
							hiddenName: 'Diag_pid',
							id: 'EPSPEF_DiagRecepCombo',
							tabIndex: TABINDEX_EPSPEF + 42,
							width: 500,
							xtype: 'swdiagcombo',
							onChange: function() {
								win.checkTrauma();
								win.refreshFieldsVisibility([ 'DeseaseType_id' ]);
								win.setSpecificsPanelVisibility();
								win.setCovidFieldsAllowBlank();
							},
							getCode: function(){
								var record = this.getStore().getById(this.getValue());
								return record != null ? record.get('Diag_Code') : '';
							},
							getGroup: function(){
								var record = this.getStore().getById(this.getValue());
								return record != null ? record.get('Diag_Code').slice(0,-2):'';
							}
						}
					}),
					{
						autoHeight: true,
						style: 'padding: 5px 0px 0px;',
						title: '',
						width: 850,
						xtype: 'fieldset',
						name: 'blockPediculos',
						hiddenName: 'blockPediculos',
						id: 'EPSPEF_blockPediculos',
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
											id: 'EPSPEF_isPediculos',
											name: 'isPediculos',
											xtype: 'checkbox',
											width: 100,
											listeners:{
												'change': function(checkbox, value) {
													var base_form = win.findById('EvnPSPriemEditForm').getForm();
													var comboPediculosDiag = base_form.findField('PediculosDiag_id');
													if(!comboPediculosDiag) return false;
													if(value){
														comboPediculosDiag.setAllowBlank(false);
														comboPediculosDiag.setDisabled(false);
													}else{
														comboPediculosDiag.setAllowBlank(true);
														comboPediculosDiag.clearValue();
														comboPediculosDiag.setDisabled(true);
													}

													if (!(value || base_form.findField('ScabiesDiag_id').getFieldValue('Diag_Code'))) {
														win.findById('EPSPEF_PediculosPrint').disable();
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
												var base_form = win.findById('EvnPSPriemEditForm').getForm();
												if(this.getFieldValue('Diag_Code') || base_form.findField('ScabiesDiag_id').getFieldValue('Diag_Code')){
													if(base_form.findField('isPediculos').getValue()) win.findById('EPSPEF_PediculosPrint').enable();
												}else{
													win.findById('EPSPEF_PediculosPrint').disable();
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
													var base_form = win.findById('EvnPSPriemEditForm').getForm();
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
														win.findById('EPSPEF_PediculosPrint').disable();
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
												var base_form = win.findById('EvnPSPriemEditForm').getForm();
												if(this.getFieldValue('Diag_Code') || base_form.findField('PediculosDiag_id').getFieldValue('Diag_Code')){	
													if(base_form.findField('isScabies').getValue()) win.findById('EPSPEF_PediculosPrint').enable();
												}else{
													win.findById('EPSPEF_PediculosPrint').disable();
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
								id: 'EPSPEF_PediculosPrint',
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
											id: 'EPSPEF_Pediculos_isSanitation',
											name: 'Pediculos_isSanitation',
											xtype: 'checkbox',
											width: 100,
											listeners:{
												'change': function(checkbox, value) {
													var base_form = win.findById('EvnPSPriemEditForm').getForm();
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
													//...
												}.createDelegate(this)
											},
											name: 'Pediculos_Sanitation_setDate',
											id: this.id + 'Pediculos_Sanitation_setDate',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											selectOnFocus: true,
											tabIndex: TABINDEX_EPSPEF + 4,
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
											// onTriggerClick: function() {

											// }.createDelegate(this),
											plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
											tabIndex: TABINDEX_EPSPEF + 5,
											validateOnBlur: false,
											width: 60,
											xtype: 'swtimefield'
										}]
									}
								]
							}
						]
					},
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
					}, {	//#111791
						id: this.id + '_TltPanel',
						border: false,
						hidden: getRegionNick().inlist(['kz']),
						layout: 'column',
						items:[{
							width: 250,
							layout: 'form',
							border: false,
							items: new Ext.form.Checkbox({
								fieldLabel: 'ТЛТ проведена в СМП',
								id: this.id + '_isCmpTlt',
								tabIndex: TABINDEX_EPSPEF + 42.2,
								xtype: 'checkbox',
								setVisibleFormDT: function(isVisible) {
									var base_form = win.findById('EvnPSPriemEditForm').getForm();
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
						},
						{
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
									tabIndex: TABINDEX_EPSPEF + 42.4,
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
									tabIndex: TABINDEX_EPSPEF + 42.6,
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
						allowBlank: false,
						fieldLabel: langs('Дееспособен'),
						hiddenName: 'EvnPS_IsActive',
						tabIndex: TABINDEX_EPSPEF + 42.7,
						width: 100,
						xtype: 'swyesnocombo'
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'DeseaseType',
						hiddenName: 'DeseaseType_id',
						fieldLabel: 'Характер',
						moreFields: [
							{ name: 'DeseaseType_begDT', type: 'date', dateFormat: 'd.m.Y' },
							{ name: 'DeseaseType_endDT', type: 'date', dateFormat: 'd.m.Y' }
						],
						tabIndex: TABINDEX_EPSPEF + 42.8,
						allowSysNick: true,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								win.refreshFieldsVisibility(['TumorStage_id']);
							}
						},
						width: 500
					}, {
						fieldLabel: langs('Стадия выявленного ЗНО'),
						width: 500,
						hiddenName:'TumorStage_id',
						xtype:'swtumorstagenewcombo',
						loadParams: getRegionNumber().inlist([58,66,101]) ? {mode: 1} : {mode:0}, // только свой регион / + нулловый рег
						tabIndex: TABINDEX_EPSPEF + 42.9
					}, {
						bodyStyle: 'padding: 0px',
						border: false,
						id: 'EPSEF_IsZNOPanel',
						hidden: getRegionNick().inlist([ 'kz' ]),
						layout: 'form',
						xtype: 'panel',
						items: [{
							fieldLabel: langs('Подозрение на ЗНО'),
							id: 'EPSPEF_EvnPS_IsZNOCheckbox',
							tabIndex: TABINDEX_EPSPEF + 42.10,
							xtype: 'checkbox',
							listeners:{
								'change': function(checkbox, value) {
									if(getRegionNick()!='ekb' || checkbox.disabled) return;
									var base_form = win.findById('EvnPSPriemEditForm').getForm(),
										diagcode = base_form.findField('Diag_pid').getFieldValue('Diag_Code');
										DiagSpid = base_form.findField('Diag_spid');
									if(!value && win.lastzno == 2 && (Ext.isEmpty(diagcode) || diagcode.search(new RegExp("^(C|D0)", "i"))<0)) {
										var personframe = win.findById('EPSPEF_PersonInformationFrame');
										sw.swMsg.show({
											buttons: Ext.Msg.YESNO,
											fn: function (buttonId, text, obj) {
												if (buttonId == 'yes') {
													win.changeZNO({isZNO: false});
												} else {
													checkbox.setValue(true);
													if(!Ext.isEmpty(DiagSpid.lastvalue))
														DiagSpid.setValue(DiagSpid.lastvalue);
												}
											}.createDelegate(this),
											icon: Ext.MessageBox.QUESTION,
											msg: 'По пациенту '+
												personframe.getFieldValue('Person_Surname')+' '+
												personframe.getFieldValue('Person_Firname')+' '+
												personframe.getFieldValue('Person_Secname')+
												' ранее установлено подозрение на ЗНО. Снять признак подозрения?',
											title: 'Вопрос'
										});
									}
									
									if(value) {
										if(Ext.isEmpty(DiagSpid.getValue()) && !Ext.isEmpty(win.lastznodiag)) {
											DiagSpid.getStore().load({
												callback:function () {
													DiagSpid.getStore().each(function (rec) {
														if (rec.get('Diag_id') == win.lastznodiag) {
															DiagSpid.fireEvent('select', DiagSpid, rec, 0);
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
									var DiagSpid = Ext.getCmp('EPSPEF_Diag_spid');
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
							tabIndex: TABINDEX_EPSPEF + 42.10,
							hiddenName: 'Diag_spid',
							id: 'EPSPEF_Diag_spid',
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
							id: 'EPSPEF_BiopsyDatePanel',
							hidden: getRegionNick()!='ekb',
							items: [{
								fieldLabel: 'Дата взятия биопсии',
								format: 'd.m.Y',
								name: 'EvnPS_BiopsyDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								width: 100,
								xtype: 'swdatefield'
							}]
						},
						new sw.Promed.Panel({
							border: true,
							height: 100,
							id: this.id + '_SpecificsPanel',
							isLoaded: false,
							layout: 'border',
							style: 'margin-top: 1em;',
							items: [
								{
									autoScroll:true,
									border:false,
									collapsible:false,
									wantToFocus:false,
									id: this.id + '_SpecificsTree',
									listeners:{
										'bodyresize': function(tree) {
											
										}.createDelegate(this),
										'beforeload': function(node) {
											
										}.createDelegate(this),
										'click':function (node, e) {
											var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
												openChildWindow: function() {
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
						})]
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
												tabIndex: TABINDEX_EPSPEF + 42.9,
												xtype: 'swdatefield',
												listeners: {
													'change': function (combo, value) {
														var base_form = win.findById('EvnPSPriemEditForm').getForm();
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
												onTriggerClick: function () {
													var base_form = this.findById('EvnPSPriemEditForm').getForm(),
														time_field = base_form.findField('FamilyContact_msgTime'),
														date_field = base_form.findField('FamilyContact_msgDate');

													if (time_field.disabled) {
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
														windowId: 'EvnPSPriemEditWindow',
														callback: function() {
															date_field.fireEvent('change', date_field, date_field.getValue());
														}
													});
												}.createDelegate(this),
												plugins: [new Ext.ux.InputTextMask('99:99', true)],
												validateOnBlur: false,
												tabIndex: TABINDEX_EPSPEF + 42.9,
												width: 60,
												xtype: 'swtimefield'
											}]
										}]}, {
											border: false,
											layout: 'column',
											hidden: getRegionNick().inlist(['vologda']),
											items: [{
												border: false,
												layout: 'form',
												items: [{
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
												}]
											}]
										}, {
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
													var base_form = win.findById('EvnPSPriemEditForm').getForm();
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
													var base_form = win.findById('EvnPSPriemEditForm').getForm();
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
														var base_form = win.findById('EvnPSPriemEditForm').getForm();
															
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
								}]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 125,
					id: 'EPSPEF_AdmitDiagPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().load({
									params: {
										'class': 'EvnDiagPSRecep',
										EvnDiagPS_pid: this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue(),
										EvnDiagPS_rid: this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('4. Сопутствующие диагнозы приемного отделения'),
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_diag_recep',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnDiagPS_setDate',
							header: langs('Дата'),
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'DiagSetClass_Name',
							header: langs('Вид диагноза'),
							hidden: false,
							resizable: true,
							sortable: true,
							width: 200
						}, {
							dataIndex: 'Diag_Code',
							header: langs('Код диагноза'),
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Diag_Name',
							header: langs('Диагноз'),
							hidden: false,
							id: 'autoexpand_diag_recep',
							resizable: true,
							sortable: true
						}],
						frame: false,
						height: 200,
						id: 'EPSPEF_EvnDiagPSRecepGrid',
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

								var grid = Ext.getCmp('EPSPEF_EvnDiagPSRecepGrid');

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
										var base_form = this.findById('EvnPSPriemEditForm').getForm();

										grid.getSelectionModel().clearSelections();
										grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

										if ( e.shiftKey == false ) {
											if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {

											}
											else if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
											}
											else if ( this.action != 'view' ) {
												this.buttons[0].focus();
											}
											else {
												this.buttons[1].focus();
											}
										}
										else {
											if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
												if ( !base_form.findField('Diag_pid').disabled ) {
													base_form.findField('Diag_pid').focus(true);
												}
												else {
													base_form.findField('MedStaffFact_pid').focus(true);
												}
											}
											else if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
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
									var evn_diag_ps_id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.grid.getTopToolbar();

									if ( selected_record ) {
										evn_diag_ps_id = selected_record.get('EvnDiagPS_id');
									}

									if ( evn_diag_ps_id ) {
										toolbar.items.items[1].enable();
										toolbar.items.items[2].enable();
										toolbar.items.items[3].enable();
									}
									else {
										toolbar.items.items[1].disable();
										toolbar.items.items[2].disable();
										toolbar.items.items[3].disable();
									}
								}
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EPSPEF_EvnDiagPSRecepGrid'));
									}

									// this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
									// this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
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
								text: langs('Добавить')
							}, {
								handler: function() {
									this.openEvnDiagPSEditWindow('edit', 'recep');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: langs('Изменить')
							}, {
								handler: function() {
									this.openEvnDiagPSEditWindow('view', 'recep');
								}.createDelegate(this),
								iconCls: 'view16',
								text: langs('Просмотр')
							}, {
								handler: function() {
									this.deleteEvent('EvnDiagPSRecep');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: langs('Удалить')
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					autoHeight: true,
					bodyStyle: 'padding-top: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EPSPEF_PriemLeavePanel',
					layout: 'form',
					listeners: {
						'expand': function(panel) {
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('5. Исход пребывания в приемном отделении'),

					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('Дата исхода'),
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = win.findById('EvnPSPriemEditForm').getForm();

										win.setMedicalCareFormTypeAllowBlank();
										win.refreshFieldsVisibility([ 'DeseaseType_id' ]);

										if ( getRegionNick().inlist([ 'ekb', 'perm' ]) ) {
											var uslugacomplex_combo = base_form.findField('UslugaComplex_id');

											uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
											uslugacomplex_combo.getStore().removeAll();
											uslugacomplex_combo.getStore().baseParams.query = '';
											uslugacomplex_combo.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(newValue, 'd.m.Y');
										}

										if ( getRegionNick() == 'perm' ) {
											base_form.findField('EvnPS_setDate').fireEvent('change', base_form.findField('EvnPS_setDate'), base_form.findField('EvnPS_setDate').getValue());
										}

										if ( getRegionNick() == 'buryatiya' && base_form.findField('EvnPS_OutcomeDate').getValue() >= new Date(2019, 6, 1)) {
												base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
										}else{
											base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
										}
									}
								},
								name: 'EvnPS_OutcomeDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EPSPEF + 43,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							labelWidth: 50,
							layout: 'form',
							items: [{
								fieldLabel: langs('Время'),
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
									var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
								tabIndex: TABINDEX_EPSPEF + 44,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					},{
						allowSysNick: true,
						autoLoad: false,
						comboSubject: 'LeaveType',
						fieldLabel: langs('Исход пребывания'),
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
								var base_form = this.findById('EvnPSPriemEditForm').getForm();

								// 1. Чистим и скрываем все поля
								// 2. В зависимости от выбранного значения, открываем поля

								var
									LpuSection_eid = base_form.findField('LpuSection_eid').getValue(),
									PrehospWaifRefuseCause_id = base_form.findField('PrehospWaifRefuseCause_id').getValue(),
									MedicalCareFormType_id = base_form.findField('MedicalCareFormType_id').getValue(),
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
								this.findById('EPSPEF_PrehospWaifRefuseCauseButton').hide();

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
											this.findById('EPSPEF_PrehospWaifRefuseCauseButton').show();
											diag_a_phase_combo.setAllowBlank(false);

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
						tabIndex:TABINDEX_EPSPEF + 45,
						width:300,
						xtype:'swcommonsprcombo'
					}, {
						hiddenName: 'LpuSection_eid',
						fieldLabel: langs('Госпитализирован в'),
						id: 'EPSPEF_LpuSectionCombo',
						linkedElements: [
							'EPSPEF_LpuSectionWardCombo'
						],
						tabIndex: TABINDEX_EPSPEF + 46,
						width: 500,
						xtype: 'swlpusectionglobalcombo', 
						listeners: 
						{
							'select': function (combo,record,index)
							{
								var base_form = this.findById('EvnPSPriemEditForm').getForm();
								var ward_combo = base_form.findField('LpuSectionWard_id');
								var bedprofile_combo = base_form.findField('LpuSectionBedProfileLink_id');
								if ( typeof record == 'object' && !Ext.isEmpty(record.get('LpuSection_id')) )
								{
									if (!this.isProcessLoadForm && Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())) {
										base_form.findField('EvnPS_OutcomeTime').onTriggerClick();
									}
									var rc_combo = this.findById('EPSPEF_PrehospWaifRefuseCause_id');
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
						id: 'EPSPEF_PrehospWaifRefuseCause_id',
						fieldLabel: langs('Отказ'),
						tabIndex: TABINDEX_EPSPEF + 47,
						width: 500,
						comboSubject: 'PrehospWaifRefuseCause',
						autoLoad: true,
						typeCode: 'int',
						xtype: 'swcommonsprcombo', 
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
							},
							'select': function(combo, record, idx) {
								var base_form = this.findById('EvnPSPriemEditForm').getForm();
								var is_transf_call_combo = base_form.findField('EvnPS_IsTransfCall');
								if( !record || Ext.isEmpty(record.get(combo.valueField)) ) {
									is_transf_call_combo.disable();
									this.findById('EPSPEF_PrehospWaifRefuseCauseButton').disable();
									
									if (getRegionNick().inlist([ 'ekb' ])) {
										base_form.findField('UslugaComplex_id').hideContainer();
										base_form.findField('LpuSectionProfile_id').hideContainer();
										base_form.findField('UslugaComplex_id').setAllowBlank(true);
										base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
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
								else
								{
									if (!this.isProcessLoadForm && Ext.isEmpty(base_form.findField('EvnPS_OutcomeDate').getValue())) {
										base_form.findField('EvnPS_OutcomeTime').onTriggerClick();
									}
									
									is_transf_call_combo.enable();
									this.findById('EPSPEF_PrehospWaifRefuseCauseButton').enable();
									this.findById('EPSPEF_LpuSectionCombo').clearValue();
									
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
							fieldLabel: langs('Профиль'),
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
									if ( !getRegionNick().inlist([ 'perm' ]) ) {
										return false;
									}

									var base_form = this.findById('EvnPSPriemEditForm').getForm();
									var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

									if ( typeof record == 'object' && !Ext.isEmpty(record.get(combo.valueField)) ) {
										var load = (base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSectionProfile_id != record.get(combo.valueField));

										base_form.findField('UslugaComplex_id').setLpuSectionProfile_id(record.get(combo.valueField));

										if ( load == true ) {
											base_form.findField('UslugaComplex_id').clearValue();
											base_form.findField('UslugaComplex_id').getStore().load({
												callback: function() {
													if ( !Ext.isEmpty(UslugaComplex_id) ) {
														var index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
															return (rec.get('UslugaComplex_id') == UslugaComplex_id);
														});

														if ( index >= 0 ) {
															base_form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
														}

														base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
													}
												}
											});
										}
									}
									else {
										base_form.findField('UslugaComplex_id').clearValue();
										base_form.findField('UslugaComplex_id').getStore().removeAll();
										base_form.findField('UslugaComplex_id').getStore().baseParams.query = '';

										base_form.findField('UslugaComplex_id').setLpuSectionProfile_id(null);
									}
								}.createDelegate(this)
							},
							listWidth: 600,
							tabIndex: TABINDEX_EPSPEF + 48,
							width: 500,
							xtype: 'swlpusectionprofileekbremotecombo'
						}]
					},{
						border: false,
						hidden: !(getRegionNick().inlist([ 'buryatiya', 'ekb', 'perm' ])), // Открыто для Бурятии, Екатеринбурга и Перми
						layout: 'form',
						items: [{
							fieldLabel: langs('Код посещения'),
							hiddenName: 'UslugaComplex_id',
							listeners: {
								'change': function(combo, newValue) {
									if (getRegionNick() == 'perm') {
										var base_form = win.findById('EvnPSPriemEditForm').getForm();
										base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
										if (!Ext.isEmpty(newValue)) {
											// поле профиль обязательное
											base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
										}
									}
									var base_form = win.findById('EvnPSPriemEditForm').getForm();
									if (
										getRegionNick() == 'ekb' 
										&& newValue == '4568436' 
										&& !Ext.isEmpty(base_form.findField('Diag_pid').getValue())
										&& base_form.findField('Diag_pid').getStore().getById(base_form.findField('Diag_pid').getValue()).data.DiagFinance_IsOms != '1'
										&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'bud'
										) 
									{
										var textMsg = 'Услуга В01.069.998 может быть выбрана только при диагнозе, оплачиваемом по ОМС';
											sw.swMsg.alert('Ошибка', textMsg, function() {
												this.formStatus = 'edit';
												base_form.findField('UslugaComplex_id').clearValue();
												base_form.findField('UslugaComplex_id').markInvalid(textMsg);
												base_form.findField('UslugaComplex_id').focus(true);
											}.createDelegate(this));
											return false;
									}
								}
							},
							listWidth: 600,
							tabIndex: TABINDEX_EPSPEF + 49,
							width: 500,
							xtype: 'swuslugacomplexnewcombo'
						}]
					}, {
						border: false,
						hidden: !(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'krym', 'pskov', 'penza' ])),
						layout: 'form',
						items: [{
							fieldLabel: langs('Результат обращения'),
							hiddenName: 'ResultClass_id',
							listWidth: 600,
							tabIndex:TABINDEX_EPSPEF + 50,
							width: 500,
							xtype: 'swresultclasscombo'
						}, {
							comboSubject: 'ResultDeseaseType',
							fieldLabel: langs('Исход'),
							hiddenName: 'ResultDeseaseType_id',
							lastQuery: '',
							listWidth: 600,
							tabIndex:TABINDEX_EPSPEF + 51,
							width: 500,
							xtype: 'swcommonsprcombo'
						}]
					}, {
						border: false,
						hidden: !(getRegionNick().inlist([ 'perm' ])), // Открыто для Перми
						layout: 'form',
						items: [{
							disabled: true,
							fieldLabel: langs('Фед. результат'),
							hiddenName: 'LeaveType_fedid',
							listWidth: 600,
							tabIndex:TABINDEX_EPSPEF + 52,
							width: 500,
							xtype: 'swleavetypefedcombo'
						}, {
							//disabled: true,
							fieldLabel: langs('Фед. исход'),
							hiddenName: 'ResultDeseaseType_fedid',
							lastQuery: '',
							listWidth: 600,
							tabIndex:TABINDEX_EPSPEF + 53,
							width: 500,
							xtype: 'swresultdeseasetypefedcombo'
						}]
					},{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							width: 300,
							items: [{
								allowBlank: true,
								id: 'EPSPEF_EvnPS_IsTransfCall',
								tabIndex: TABINDEX_EPSPEF + 54,
								comboSubject: 'YesNo',
								fieldLabel: langs('Передан активный вызов'),
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
											'Report_Params': '&paramEvnPsID=' + this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue(),
											'Report_Format': 'pdf'
										});
										printBirt({
											'Report_FileName': 'printPatientRefuse.rptdesign',
											'Report_Params': '&paramEvnPsID=' + this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue(),
											'Report_Format': 'pdf'
										});
                                    }
								}.createDelegate(this),
								iconCls: 'print16',
								id: 'EPSPEF_PrehospWaifRefuseCauseButton',
								tabIndex: TABINDEX_EPSPEF + 55,
								text: langs('Справка об отказе в госпитализации'),
								tooltip: langs('Справка об отказе в госпитализации'),
								xtype: 'button'
							}]
						}]
					}, {
						xtype: 'swdiagsetphasecombo',
						hiddenName: 'DiagSetPhase_aid',
						fieldLabel: langs('Состояние пациента при выписке'),
						width: 300,
						tabIndex: TABINDEX_EPSPEF + 56,
						editable: false
					}]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EPSPEF_EvnUslugaPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPSPEF_EvnUslugaGrid').getStore().load({
									params: {
										pid: this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('6. Услуги'),
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_usluga',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnUsluga_setDate',
							header: langs('Дата'),
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnUsluga_setTime',
							header: langs('Время'),
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Usluga_Code',
							header: langs('Код'),
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Usluga_Name',
							header: langs('Наименование'),
							hidden: false,
							id: 'autoexpand_usluga',
							resizable: true,
							sortable: true
						}, {
							dataIndex: 'EvnUsluga_Kolvo',
							header: langs('Количество'),
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}],
						frame: false,
						id: 'EPSPEF_EvnUslugaGrid',
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

								var grid = Ext.getCmp('EPSPEF_EvnUslugaGrid');

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
										var base_form = this.findById('EvnPSPriemEditForm').getForm();

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
											if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
												
											}
											else if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
												if ( !base_form.findField('Diag_pid').disabled ) {
													base_form.findField('Diag_pid').focus(true);
												}
												else {
													base_form.findField('MedStaffFact_pid').focus(true);
												}
											}
											else if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
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
									var evn_usluga_id = null;
									var evnclass_sysnick = null;
									var selected_record = sm.getSelected();
									var toolbar = this.grid.getTopToolbar();

									if ( selected_record ) {
										evn_usluga_id = selected_record.get('EvnUsluga_id');
										evnclass_sysnick = selected_record.get('EvnClass_SysNick');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[2].disable();
									toolbar.items.items[3].disable();
										
									if ( evn_usluga_id ) {
										toolbar.items.items[1].enable();
										toolbar.items.items[2].enable();
										if (evnclass_sysnick != 'EvnUslugaPar') {
											toolbar.items.items[3].enable();
										}
									}
								}
							}
						}),
						stripeRows: true,
						store: new Ext.data.Store({
							autoLoad: false,
							baseParams: {
								'parent': 'EvnPS',
								'parentName': 'EvnPS'
							},
							listeners: {
								'load': function(store, records, index) {
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EPSPEF_EvnUslugaGrid'));
									}

									// this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
									// this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
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
								text: langs('Добавить'),
								menu: {
									xtype: 'menu',
									plain: true,
									items: [{
										handler: function() {
											this.openEvnUslugaEditWindow('addOper');
										}.createDelegate(this),
										text: langs('Добавить операцию')
									}, {
										handler: function() {
											this.openEvnUslugaEditWindow('add');
										}.createDelegate(this),
										text: langs('Добавить общую услугу')
									}]
								}
							}, {
								handler: function() {
									this.openEvnUslugaEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: langs('Изменить')
							}, {
								handler: function() {
									this.openEvnUslugaEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: langs('Просмотр')
							}, {
								handler: function() {
									this.deleteEvent('EvnUsluga');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: langs('Удалить')
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 200,
					id: 'EPSPEF_EvnDrugPanel',
					isLoaded: false,
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('EPSPEF_EvnDrugGrid').getStore().load({
									params: {
										EvnDrug_pid: this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue()
									}
								});
							}

							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('7. Использование медикаментов'),
					items: [ new Ext.grid.GridPanel({
						autoExpandColumn: 'autoexpand_drug',
						autoExpandMin: 100,
						border: false,
						columns: [{
							dataIndex: 'EvnDrug_setDate',
							header: langs('Дата'),
							hidden: false,
							renderer: Ext.util.Format.dateRenderer('d.m.Y'),
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Drug_Code',
							header: langs('Код'),
							hidden: false,
							resizable: false,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'EvnDrug_Kolvo',
							header: langs('Количество'),
							hidden: false,
							resizable: true,
							sortable: true,
							width: 100
						}, {
							dataIndex: 'Drug_Name',
							header: langs('Наименование'),
							hidden: false,
							id: 'autoexpand_drug',
							resizable: true,
							sortable: true
						}],
						frame: false,
						id: 'EPSPEF_EvnDrugGrid',
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

								var grid = this.findById('EPSPEF_EvnDrugGrid');

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
										var base_form = this.findById('EvnPSPriemEditForm').getForm();

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
											if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
												
											}
											else if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
												if ( !base_form.findField('Diag_pid').disabled ) {
													base_form.findField('Diag_pid').focus(true);
												}
												else {
													base_form.findField('MedStaffFact_pid').focus(true);
												}
											}
											else if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
												this.findById('EPSPEF_EvnDiagPSHospGrid').getView().focusRow(0);
												this.findById('EPSPEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
											}
											else if ( !this.findById('EPSPEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
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
									var evn_drug_id = null;
									var selected_record = sm.getSelected();
									var toolbar = this.findById('EPSPEF_EvnDrugGrid').getTopToolbar();

									if ( selected_record ) {
										evn_drug_id = selected_record.get('EvnDrug_id');
									}

									toolbar.items.items[1].disable();
									toolbar.items.items[2].disable();
									toolbar.items.items[3].disable();

									if ( evn_drug_id ) {
										toolbar.items.items[2].enable();

										if ( this.action != 'view' ) {
											toolbar.items.items[1].enable();
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
									if ( store.getCount() == 0 ) {
										LoadEmptyRow(this.findById('EPSPEF_EvnDrugGrid'));
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
								text: langs('Добавить')
							}, {
								handler: function() {
									this.openEvnDrugEditWindow('edit');
								}.createDelegate(this),
								iconCls: 'edit16',
								text: langs('Изменить')
							}, {
								handler: function() {
									this.openEvnDrugEditWindow('view');
								}.createDelegate(this),
								iconCls: 'view16',
								text: langs('Просмотр')
							}, {
								handler: function() {
									this.deleteEvent('EvnDrug');
								}.createDelegate(this),
								iconCls: 'delete16',
								text: langs('Удалить')
							}]
						})
					})]
				}),
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 290,
					id: 'EPSPEF_PrehospWaifPanel',
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							//to-do не загружать грид, если он загружен
							//log(this.findById('EPSPEF_PrehospWaifInspection'));
							this.PrehospWaifInspectionRefreshGrid();
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: langs('8. Беспризорный'),
					items: [{
						bodyStyle: 'padding-top: 0.5em;',
						border: false,
						height: 90,
						layout: 'form',
						region: 'north',
						items: [{
							id: 'EPSPEF_EvnPS_IsWaif',
							comboSubject: 'YesNo',
							fieldLabel: langs('Беспризорный'),
							hiddenName: 'EvnPS_IsWaif',
							tabIndex: TABINDEX_EPSPEF + 56,
							width: 100,
							value: 1,
							xtype: 'swcommonsprcombo', 
							listeners: 
							{
								'change': function (combo,newValue,oldValue) 
								{
									var base_form = this.findById('EvnPSPriemEditForm').getForm();
									var pw_arrive_combo = base_form.findField('PrehospWaifArrive_id');
									var pw_reason_combo = base_form.findField('PrehospWaifReason_id');
									var view_frame = this.findById('EPSPEF_PrehospWaifInspection');
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
										pw_arrive_combo.enable();
										pw_arrive_combo.setAllowBlank(false);
										// Обратился самостоятельно ставить автоматически и поле не доступно, если Беспризорный = Да и в разделе КВС Госпитализация поле Кем доставлен = Самостоятельно
										/*if (base_form.findField('PrehospArrive_id').getValue() == 1)
										{
											pw_arrive_combo.setValue(3);
											pw_arrive_combo.disable();
										}*/
										// Причина помещения в ЛПУ: доступно и обязательное если Беспризорный = Да.
										pw_reason_combo.enable();
										pw_reason_combo.setAllowBlank(false);
										view_frame.setReadOnly(false);
									}
								}.createDelegate(this)
							}
						},{
							fieldLabel: langs('Кем доставлен'),
							tabIndex: TABINDEX_EPSPEF + 57,
							width: 500,
							comboSubject: 'PrehospWaifArrive',
							hiddenName: 'PrehospWaifArrive_id',
							autoLoad: true,
							xtype: 'swcommonsprcombo'
						},{
							id: 'EPSPEF_PrehospWaifReason_id',
							fieldLabel: langs('Причина помещения в ЛПУ'),
							tabIndex: TABINDEX_EPSPEF + 58,
							width: 500,
							comboSubject: 'PrehospWaifReason',
							autoLoad: true,
							xtype: 'swcommonsprcombo'
						}]
					},
					new sw.Promed.ViewFrame({
						id: 'EPSPEF_PrehospWaifInspection',
						title:langs('Осмотры'),
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
							{name: 'PrehospWaifInspection_SetDT',  type: 'string', header: langs('Дата/время'), width: 100},
							{name: 'LpuSection_Name',  type: 'string', header: langs('Отделение'), width: 250},
							{name: 'MedPersonal_Fio',  type: 'string', header: langs('Врач'), width: 200},
							{id: 'autoexpand', name: 'Diag_Name',  type: 'string', header: langs('Диагноз')}
						],
						actions:
						[
							{name:'action_add', handler: function() { this.openPrehospWaifInspectionEditWindow('add'); }.createDelegate(this)},
							{name:'action_edit', handler: function() { this.openPrehospWaifInspectionEditWindow('edit'); }.createDelegate(this)},
							{name:'action_view', handler: function() { this.openPrehospWaifInspectionEditWindow('view'); }.createDelegate(this)},
							{name:'action_delete'},
							{name:'action_refresh', handler: function() { this.PrehospWaifInspectionRefreshGrid(); }.createDelegate(this)},
							{name:'action_print'}
						],
						paging: false,
						root: 'data',
						totalProperty: 'totalCount',
						focusOn: {name:'EPSPEF_PrintBtn',type:'button'},
						focusPrev: {name:'EPSPEF_PrehospWaifReason_id',type:'field'},
						focusOnFirstLoad: false
					})
					]
				})],
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'EvnPS_IsActive' },
					{ name: 'EvnPS_OutcomeDate' },
					{ name: 'EvnPS_OutcomeTime' },
					{ name: 'EvnPS_IsPLAmbulance' },
					{ name: 'EvnPS_IsTransfCall' },
					{ name: 'EvnPS_IsWaif' },
					{ name: 'LpuSection_eid' },
					{ name: 'LpuSectionWard_id' },
					{ name: 'LpuSectionBedProfileLink_id' },
					{ name: 'PrehospWaifArrive_id' },
					{ name: 'PrehospWaifReason_id' },
					{ name: 'PrehospWaifRefuseCause_id' },
					{ name: 'MedicalCareFormType_id' },
					{ name: 'LeaveType_prmid' },
					{ name: 'LeaveType_fedid' },
					{ name: 'ResultDeseaseType_fedid' },
					{ name: 'ResultClass_id' },
					{ name: 'ResultDeseaseType_id' },
					{ name: 'UslugaComplex_id' },
					{ name: 'LpuSectionProfile_id' },
					{ name: 'Diag_did' },
					{ name: 'DiagSetPhase_did' },
					{ name: 'EvnPS_PhaseDescr_did' },
					{ name: 'Diag_pid' },
					{ name: 'Diag_eid' },
					{ name: 'TraumaCircumEvnPS_Name' },
					{ name: 'TraumaCircumEvnPS_setDT' },
					{ name: 'DeseaseType_id' },
					{ name: 'DiagSetPhase_pid' },
					{ name: 'DiagSetPhase_aid' },
					{ name: 'EvnPS_PhaseDescr_pid' },
					{ name: 'EvnDie_id' },
					{ name: 'EvnQueue_id' },
					{ name: 'EvnDirection_id' },
					{ name: 'EvnDirection_Num' },
					{ name: 'EvnDirection_setDate' },
					{ name: 'EvnLeave_id' },
					{ name: 'EvnOtherLpu_id' },
					{ name: 'EvnOtherSection_id' },
					{ name: 'EvnOtherSectionBedProfile_id' },
					{ name: 'EvnOtherStac_id' },
					{ name: 'EvnPS_CodeConv' },
					{ name: 'EvnPS_HospCount' },
					{ name: 'EvnPS_id' },
					{ name: 'EvnPS_IsCont' },
					{ name: 'EvnPS_IsDiagMismatch' },
					{ name: 'EvnPS_IsImperHosp' },
					{ name: 'EvnPS_IsNeglectedCase' },
					{ name: 'RepositoryObserv_BreathRate' },
					{ name: 'RepositoryObserv_Systolic' },
					{ name: 'RepositoryObserv_Diastolic' },
					{ name: 'RepositoryObserv_Height' },
					{ name: 'RepositoryObserv_Weight' },
					{ name: 'RepositoryObserv_TemperatureFrom' },
					{ name: 'RepositoryObserv_SpO2' },
					{ name: 'CovidType_id' },
					{ name: 'DiagConfirmType_id' },
					{ name: 'EvnPS_IsWrongCure' },
					{ name: 'EvnPS_IsUnlaw' },
					{ name: 'EvnPS_IsUnport' },
					{ name: 'EvnPS_NotificationDate' },
					{ name: 'EvnPS_NotificationTime' },
					{ name: 'MedStaffFact_id' },
					{ name: 'EvnPS_Policeman' },
					{ name: 'EvnPS_IsShortVolume' },
					{ name: 'EvnPS_IsWithoutDirection' },
					{ name: 'EvnPS_NumCard' },
					{ name: 'EvnPS_NumConv' },
					{ name: 'CmpCallCard_id'},
					{ name: 'EvnPS_setDate' },
					{ name: 'EvnPS_setTime' },
					{ name: 'EvnPS_TimeDesease' },
					{ name: 'Okei_id' },
					{ name: 'LeaveType_id' },
					{ name: 'LpuSection_did' },
					{ name: 'LpuSection_pid' },
					{ name: 'MedStaffFact_pid' },
					{ name: 'Lpu_id' },
					{ name: 'Org_did' },
					{ name: 'MedStaffFact_did' },
					{ name: 'MedStaffFact_TFOMSCode' },
					{ name: 'PayType_id' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'PrehospArrive_id' },
					{ name: 'PrehospDirect_id' },
					{ name: 'PrehospStatus_id' },
					{ name: 'PrehospToxic_id' },
					{ name: 'LpuSectionTransType_id'},
					{ name: 'PrehospTrauma_id' },
					{ name: 'PrehospType_id' },
					{ name: 'EntranceModeType_id' },
					{ name: 'Server_id' },
					{ name: 'TumorStage_id' },
					{ name: 'EvnPS_CmpTltDate' }, //#111791
					{ name: 'EvnPS_CmpTltTime' },
					{ name: 'EvnPS_IsZNO' },
					{ name: 'EvnPS_IsZNORemove' },
					{ name: 'EvnPS_BiopsyDate'},
					{ name: 'Diag_spid' },
					{ name: 'FamilyContact_msgDate'}, //#180378
					{ name: 'FamilyContact_msgTime'},
					{ name: 'FamilyContact_FIO'},
					{ name: 'FamilyContact_Phone'},
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
					{ name: 'buttonPrint058'},
					{ name: 'EvnSection_id' }
				]),
				region: 'center',
				url: '/?c=EvnPS&m=saveEvnPS'
			})]
		});
		sw.Promed.swEvnPSPriemEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EPSPEF_LpuSectionRecCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnPSPriemEditForm').getForm();
			var pay_type = combo.getStore().getById(newValue);
			var pay_type_nick = (pay_type && pay_type.get('PayType_SysNick')) || '';
							
			if ( getRegionNick().inlist([ 'ekb', 'perm' ]) ) {
				var uslugacomplex_combo = base_form.findField('UslugaComplex_id');
				if(!pay_type_nick)
					pay_type_nick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');

				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';

				uslugacomplex_combo.getStore().baseParams.LpuSection_id = newValue;
				uslugacomplex_combo.getStore().baseParams.UslugaComplexPartition_CodeList = ('bud' == pay_type_nick) ? Ext.util.JSON.encode([350]) :Ext.util.JSON.encode([300, 301]);
			}

			this.filterLpuSectionProfile();
			
			var diag_d_combo = base_form.findField('Diag_did');
			var diag_p_combo = base_form.findField('Diag_pid');
			var isUfa = (getRegionNick() == 'ufa');
			var priemDiag = (getGlobalOptions().check_priemdiag_allow && getGlobalOptions().check_priemdiag_allow=='1');
			if ( Ext.isEmpty(newValue) ) {
				diag_p_combo.clearValue();
				diag_p_combo.disable();
				diag_p_combo.setAllowBlank(true);
				return false;
			}

			diag_p_combo.enable();
			diag_p_combo.setAllowBlank(!priemDiag);

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
					where: "where Diag_id = " + diag_did
				}
			});
		}.createDelegate(this));

		this.findById(this.id + 'MedStaffFactDid').addListener('change', function(combo, newValue, oldValue) {
			if ( getRegionNick().inlist([ 'ekb' ]) ) {
				var
					base_form = this.findById('EvnPSPriemEditForm').getForm(),
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

		this.findById('EPSPEF_MedStaffFactRecCombo').addListener('change', function(combo, newValue, oldValue) {
			if ( getRegionNick().inlist([ 'ekb', 'perm' ]) ) {
				var base_form = this.findById('EvnPSPriemEditForm').getForm();
				var pay_type = combo.getStore().getById(newValue);
				var pay_type_nick = (pay_type && pay_type.get('PayType_SysNick')) || '';
				if(!pay_type_nick)
					pay_type_nick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
				var uslugacomplex_combo = base_form.findField('UslugaComplex_id');

				uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
				uslugacomplex_combo.getStore().removeAll();
				uslugacomplex_combo.getStore().baseParams.query = '';
				uslugacomplex_combo.getStore().baseParams.UslugaComplexPartition_CodeList = ('bud' == pay_type_nick) ? Ext.util.JSON.encode([350]) : Ext.util.JSON.encode([300, 301]);
				uslugacomplex_combo.getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_pid').getValue();
				uslugacomplex_combo.getStore().baseParams.MedPersonal_id = combo.getFieldValue('MedPersonal_id');
				uslugacomplex_combo.getStore().baseParams.FedMedSpec_id = combo.getFieldValue('FedMedSpec_id');

				if ( getRegionNick() == 'ekb' ) {
					this.filterLpuSectionProfile();
				}
			}
			else if ( getRegionNick().inlist([ 'krym', 'buryatiya' ])) {
				this.filterLpuSectionProfile();
			}
		}.createDelegate(this));
		
		this.findById('EPSPEF_MedStaffFactRecCombo').addListener('keydown', function(inp, e) {
			var base_form = this.findById('EvnPSPriemEditForm').getForm();

			if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false && base_form.findField('Diag_pid').disabled ) {
				e.stopEvent();

				if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
					this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
					this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
				}
				else if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {
					
				}
				else if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
					this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
					this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
				}
				else if ( this.action != 'view' ) {
					this.buttons[0].focus();
				}
				else {
					this.buttons[1].focus();
				}
			}
		}.createDelegate(this));

		this.findById('EPSPEF_DiagRecepCombo').addListener('keydown', function(inp, e) {
			this.refreshFieldsVisibility(['DeseaseType_id','TumorStage_id']);

			var base_form = this.findById('EvnPSPriemEditForm').getForm();

			if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
				e.stopEvent();

				if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed && this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
					this.findById('EPSPEF_EvnDiagPSRecepGrid').getView().focusRow(0);
					this.findById('EPSPEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
				}
				else if ( !this.findById('EPSPEF_PriemLeavePanel').collapsed ) {

				}
				else if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed && this.findById('EPSPEF_EvnUslugaGrid').getStore().getCount() > 0 ) {
					this.findById('EPSPEF_EvnUslugaGrid').getView().focusRow(0);
					this.findById('EPSPEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
				}
				else if ( this.action != 'view' ) {
					this.buttons[0].focus();
				}
				else {
					this.buttons[1].focus();
				}
			}
		}.createDelegate(this));

		this.findById('EPSPEF_DiagRecepCombo').addListener('change', function(combo, newValue) {
			if ( getRegionNick() == 'ekb' ) {
				var base_form = this.findById('EvnPSPriemEditForm').getForm();

				if ( typeof combo.getStore().getById(newValue) == 'object' ) {
					if (
						base_form.findField('UslugaComplex_id').getValue() == '4568436' 
						&& combo.getStore().getById(newValue).get('DiagFinance_IsOms') != '1'
						&& base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'bud'
					) {
						var textMsg = 'Услуга В01.069.998 может быть выбрана только при диагнозе, оплачиваемом по ОМС';
						sw.swMsg.alert('Ошибка', textMsg, function() {
							this.formStatus = 'edit';
							base_form.findField('Diag_pid').clearValue();
							base_form.findField('Diag_pid').markInvalid(textMsg);
							base_form.findField('Diag_pid').focus(true);
						}.createDelegate(this));
						return false;
					}
				}
			}
			if ( !getRegionNick().inlist([ 'kz' ]) ) {
				var tltPanel = this.findById(this.id+'_TltPanel');
				var tltCheckbox = this.findById(this.id + '_isCmpTlt');
				var timeDeseaseField = this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_TimeDesease');

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
			this.setDiagEidAllowBlank();
			win.refreshFieldsVisibility(['DeseaseType_id','TumorStage_id']);
		}.createDelegate(this));

		this.findById('EPSPEF_EvnUslugaGrid').getStore().addListener('load', function(store,records,ooptions) {
			if (getRegionNick() == 'ufa') {
				this.loadECGResult();
			}
		}.createDelegate(this));

		if(getRegionNick() == 'ufa') { 
			this.findById(this.id + 'EvnPS_setDate').addListener('change', function(datefield,newValue,oldValue){
				win.findById(win.id + '_CmpTltTime').validate();
			});
			this.findById(this.id + 'EvnPS_setTime').addListener('change', function(datefield,newValue,oldValue){
				win.findById(win.id + '_CmpTltTime').validate();
			});
		}
	},
	openEvnDiagPSEditWindow: function(action, type) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования диагноза уже открыто'));
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnDiagPSEditWindow(action, type);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		switch ( type ) {
			case 'hosp':
				if ( this.findById('EPSPEF_HospitalisationPanel').hidden ) {
					return false;
				}

				if ( !base_form.findField('Diag_did').getValue() ) {
					sw.swMsg.alert(langs('Ошибка'), langs('Не заполнен основной диагноз направившего учреждения'), function() { base_form.findField('Diag_did').focus(true); });
					return false;
				}

				grid = this.findById('EPSPEF_EvnDiagPSHospGrid');
			break;

			case 'recep':
				if ( this.findById('EPSPEF_AdmitDepartPanel').hidden ) {
					return false;
				}

				if ( !base_form.findField('Diag_pid').getValue() ) {
					sw.swMsg.alert(langs('Ошибка'), langs('Не заполнен основной диагноз в приемном отделении'), function() { base_form.findField('Diag_pid').focus(true); });
					return false;
				}

				grid = this.findById('EPSPEF_EvnDiagPSRecepGrid');
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

			//для Перми передаем id движения (#187520)
			if(getRegionNick() == 'perm' && type=='recep'){
				params.EvnDiagPS_pid = base_form.findField('EvnSection_id').getValue();
			}
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnDiagPS_id') ) {
				return false;
			}

			params = selected_record.data;
		}

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
						if ( !this.findById('EPSPEF_AdmitDepartPanel').hidden ) {
							this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().load({
								params: {
									'class': 'EvnDiagPSRecep',
									'EvnDiagPS_pid': base_form.findField('EvnPS_id').getValue()
								}
							});
						}
					break;
				}
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this),
			Person_Birthday: this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
			Person_Firname: this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Firname'),
			Person_id: base_form.findField('Person_id').getValue(),
			Person_Secname: this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Secname'),
			Person_Surname: this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Surname'),
			type: type
		});
	},
	openEvnDrugEditWindow: function(action) {
		if ( this.findById('EPSPEF_EvnDrugPanel').hidden || this.findById('EPSPEF_EvnDrugPanel').collapsed ) {
			return false;
		}

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var grid = this.findById('EPSPEF_EvnDrugGrid');

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd(getEvnDrugEditWindowName()).isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно добавления случая использования медикаментов уже открыто'));
			return false;
		}

		if ( action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnDrugEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}

		// данные для ParentEvnCombo (КВС как движение в приемном)
		var parent_evn_combo_data = new Array({
			Evn_id: base_form.findField('EvnPS_id').getValue(),
			Evn_Name: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y') + ' / ' + this.userMedStaffFact.LpuSection_Name + ' / ' + this.userMedStaffFact.MedPersonal_FIO,
			Evn_setDate: base_form.findField('EvnPS_setDate').getValue(),
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			Lpu_id: this.userMedStaffFact.Lpu_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			MedPersonal_id: this.userMedStaffFact.MedPersonal_id
		});

		var formParams = new Object();
		var params = new Object();
		var person_id = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_id');
		var person_birthday = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		var person_firname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Firname');
		var person_secname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Secname');
		var person_surname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Surname');

		params.action = action;
		params.parentEvnComboData = parent_evn_combo_data;
		params.callback = function(data) {
			if ( !data || !data.evnDrugData ) {
				return false;
			}
			var grid = this.findById('EPSPEF_EvnDrugGrid');
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
		}

		params.formParams = formParams;

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
							params.parentEvnComboData = new Array({
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

						getWnd(getEvnDrugEditWindowName()).show(params);
					}
				  
				}.createDelegate(this),
				url: '/?c=EvnSection&m=getSectionPriemData'
			});
		} else {
			params.parentEvnComboData = new Array({
				Evn_id: base_form.findField('EvnPS_id').getValue(),
				Evn_Name: 'Приемное отделение'
			});
			getWnd(getEvnDrugEditWindowName()).show(params);
		}
	},
	/*
	params: {
		EvnLeave_setDate: null,
		EvnLeave_UKL: null,
		LeaveCause_id: null,
		LpuSection_id: null,
		MedPersonal_id: null,
		ResultDesease_id: null,
		TariffClass_id: null
	},
	selectEvnDirection: function(ed_record) {
		var bf = this.findById('EvnPSPriemEditForm').getForm();
		bf.findField('PrehospDirect_id').setValue(2);
		bf.findField('EvnDirection_id').setRawValue(ed_record.get('EvnDirection_id'));
		bf.findField('Org_did').getStore().loadData([{
			Org_id: ed_record.get('Lpu_id'),
			Org_Code: null,
			Org_Nick: ed_record.get('Org_Nick'),
			Org_Name: ed_record.get('Org_Name')
		}], false);
		bf.findField('Org_did').setValue(ed_record.get('Lpu_id'));
		bf.findField('EvnDirection_Num').setRawValue(ed_record.get('EvnDirection_Num'));
		bf.findField('EvnDirection_setDate').setValue(ed_record.get('EvnDirection_setDateTime'));
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
				params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
			});
		}
	},
	*/
	openEvnUslugaEditWindow: function(action) {
		if ( this.findById('EPSPEF_EvnUslugaPanel').hidden ) {
			return false;
		}

		if ( Ext.isEmpty(action) || !action.inlist([ 'add', 'addOper', 'edit', 'view']) ) {
			return false;
		}

		var wnd = this;
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var grid = this.findById('EPSPEF_EvnUslugaGrid');

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
			if( !getRegionNick().inlist([ 'kz' ]) && wnd.getOKSDiag()) {
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
		params.Person_id = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Surname');

		var parent_evn_combo_data = [];

		if ( !getRegionNick().inlist([ 'kareliya', 'perm', 'ekb', 'krym' ]) ) {
			parent_evn_combo_data.push({
				Evn_id: base_form.findField('EvnPS_id').getValue(),
				Evn_Name: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y') + ' / ' + this.userMedStaffFact.LpuSection_Name + ' / ' + this.userMedStaffFact.MedPersonal_FIO,
				Evn_setDate: base_form.findField('EvnPS_setDate').getValue(),
				Evn_setTime: base_form.findField('EvnPS_setTime').getValue(),
				MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
				LpuSection_id: this.userMedStaffFact.LpuSection_id,
				MedPersonal_id: this.userMedStaffFact.MedPersonal_id,
				Diag_id: base_form.findField('Diag_pid').getValue(),
				UslugaComplex_Code: base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code')
			});
		}

		switch ( action ) {
			case 'addOper':
			case 'add':
				params.action = 'add';

				if ( base_form.findField('EvnPS_id').getValue() == 0 ) {
					this.doSave({
						openChildWindow: function() {
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

				if ( getRegionNick().inlist([ 'kareliya', 'perm', 'ekb', 'krym' ]) ) {
					params.parentClass = 'EvnSection';

					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { this.hide(); }.createDelegate(this) );
						},
						params: {
							EvnPS_id: base_form.findField('EvnPS_id').getValue()
						},
						success: function(response, options) {
							if (!Ext.isEmpty(response.responseText)) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.length <= 0 && params.parentEvnComboData.length <= 0 ) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function() {
											wnd.priem = true;
											base_form.findField('LpuSection_pid').focus(false);
										},
										icon: Ext.Msg.WARNING,
										msg: langs('Не введено ни одного движения. Поля "Приемное отделение", "Врач приемного отделения" и "Диагноз приемного отделения" должны быть заполнены.'),
										title: ERR_INVFIELDS_TIT
									});
									return false;
								}

								if ( response_obj.length > 0 ) {
									params.parentEvnComboData = [{
										Evn_id: response_obj[0].EvnSection_id,
										Evn_Name: response_obj[0].EvnSection_setDate + ' / ' + response_obj[0].LpuSection_Name + ' / ' + response_obj[0].MedPersonal_Fio,
										Evn_setDate: response_obj[0].EvnSection_setDate,
										Evn_setTime: response_obj[0].EvnSection_setTime,
										MedStaffFact_id: response_obj[0].MedStaffFact_id,
										LpuSection_id: response_obj[0].LpuSection_id,
										MedPersonal_id: response_obj[0].MedPersonal_id,
										Diag_id: response_obj[0].Diag_id,
										UslugaComplex_Code: response_obj[0].UslugaComplex_Code
									}];
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

					// данные для ParentEvnCombo (КВС как движение в приемном)
					params.parentEvnComboData = new Array({
						Evn_id: base_form.findField('EvnPS_id').getValue(),
						Evn_Name: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y') + ' / ' + this.userMedStaffFact.LpuSection_Name + ' / ' + this.userMedStaffFact.MedPersonal_FIO,
						Evn_setDate: base_form.findField('EvnPS_setDate').getValue(),
						MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
						LpuSection_id: this.userMedStaffFact.LpuSection_id,
						MedPersonal_id: this.userMedStaffFact.MedPersonal_id,
						Diag_id: base_form.findField('Diag_pid').getValue()
					});

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
						if ( getRegionNick().inlist([ 'kareliya', 'perm', 'ekb', 'krym' ]) ) {
							params.parentClass = 'EvnSection';

							Ext.Ajax.request({
								failure: function(response, options) {
									sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { this.hide(); }.createDelegate(this) );
								},
								params: {
									EvnPS_id: base_form.findField('EvnPS_id').getValue()
								},
								success: function(response, options) {
									if ( !Ext.isEmpty(response.responseText) ) {
										var response_obj = Ext.util.JSON.decode(response.responseText);

										if ( response_obj.length <= 0 && params.parentEvnComboData.length <= 0 ) {
											sw.swMsg.show({
												buttons: Ext.Msg.OK,
												fn: function() {
													wnd.formStatus = 'edit';
													base_form.findField('LpuSection_pid').focus(false);
												},
												icon: Ext.Msg.WARNING,
												msg: langs('Не введено ни одного движения. Поля "Приемное отделение", "Врач приемного отделения" и "Диагноз приемного отделения" должны быть заполнены.'),
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

									if ( selected_record.get('EvnClass_SysNick') == 'EvnUslugaCommon' ){
										params.formParams = {
											EvnUslugaCommon_id: evn_usluga_id
										}
										getWnd('swEvnUslugaEditWindow').show(params);
									}
									else {
										params.formParams = {
											EvnUslugaOper_id: evn_usluga_id
										}
										getWnd('swEvnUslugaOperEditWindow').show(params);
									}
								}.createDelegate(this),
								url: '/?c=EvnSection&m=getSectionPriemData'
							});
						}
						else {
							params.parentEvnComboData = parent_evn_combo_data;

							if ( selected_record.get('EvnClass_SysNick') == 'EvnUslugaCommon' ){
								params.formParams = {
									EvnUslugaCommon_id: evn_usluga_id
								}
								getWnd('swEvnUslugaEditWindow').show(params);
							}
							else {
								params.formParams = {
									EvnUslugaOper_id: evn_usluga_id
								}
								getWnd('swEvnUslugaOperEditWindow').show(params);
							}
						}
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
			break;
		}
	},
	PrehospWaifInspectionRefreshGrid: function() 
	{
		if ( Ext.getCmp('EPSPEF_PrehospWaifPanel').hidden ) {
			return false;
		}

		var base_form = Ext.getCmp('EvnPSPriemEditForm').getForm();
		if ( this.action == 'add' && base_form.findField('EvnPS_id').getValue() == 0 ) {
			this.doSave({
				openChildWindow: function() {
					this.PrehospWaifInspectionRefreshGrid();
				}.createDelegate(this),
				print: false
			});
			return false;
		}
		var view_frame = Ext.getCmp('EPSPEF_PrehospWaifInspection');
		view_frame.removeAll(true);
		var params = { EvnPS_id: base_form.findField('EvnPS_id').getValue() };
		params.start = 0; 
		params.limit = 100;
		view_frame.loadData({globalFilters:params});
	},
	openPrehospWaifInspectionEditWindow: function(action) {
		if ( this.findById('EPSPEF_PrehospWaifPanel').hidden ) {
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
		
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var view_frame = this.findById('EPSPEF_PrehospWaifInspection');
		var grid = view_frame.getGrid();
		
		if ( getWnd('swPrehospWaifInspectionEditWindow').isVisible() )
		{
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования осмотра уже открыто'), function() {
				grid.getSelectionModel().selectFirstRow();
				grid.getView().focusRow(0);
			});
			return false;
		}

		var params = new Object();

		params.action = action;
		params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
		params.LpuSection_id  = this.userMedStaffFact.LpuSection_id;
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
						openChildWindow: function() {
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
					params.PrehospWaifInspection_id = record.get('PrehospWaifInspection_id');
					getWnd('swPrehospWaifInspectionEditWindow').show(params);
				}
				else
				{
					sw.swMsg.alert(langs('Сообщение'), langs('Вы не выбрали осмотр!'), function() {
						grid.focus();
					});
				}
			break;
		}
	},
	_onSelectEvnDirection: function(data) {
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var prehosp_arrive_combo = base_form.findField('PrehospArrive_id');
		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var lpu_section_dir_combo = base_form.findField('LpuSection_did');
		
		prehosp_direct_combo.setValue(data.PrehospDirect_id || ((data.Org_did != getGlobalOptions().org_id)?2:1));
		prehosp_arrive_combo.setValue((data.PrehospArrive_id || 1));
		prehosp_arrive_combo.fireEvent('change', prehosp_arrive_combo, prehosp_arrive_combo.getValue());
		
		var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
		iswd_combo.setValue(2);
		iswd_combo.disable();
		this.disableFields.push('EvnPS_IsWithoutDirection');
		iswd_combo.fireEvent('change', iswd_combo, 2);

		base_form.findField('EvnDirection_id').setValue(data.EvnDirection_id);

		var index = prehosp_direct_combo.getStore().findBy(function(rec) {
			return (rec.get(prehosp_direct_combo.valueField) == prehosp_direct_combo.getValue());
		});

		if ( index >= 0 ) {
			if ( prehosp_direct_combo.getStore().getAt(index).get('PrehospDirect_Code') == 1 ) {
				lpu_section_dir_combo.setValue(data.LpuSection_did || 0);
			}
			else {
				var org_type = '';

				switch ( prehosp_direct_combo.getStore().getAt(index).get('PrehospDirect_Code') ) {
					case 2:
						org_type = 'lpu';
					break;
					case 3:
					case 4:
					case 5:
					case 6:
						org_type = 'org';
					break;
				}

				if ( org_type.length > 0 && !Ext.isEmpty(data.Org_did) ) {
					base_form.findField('Org_did').getStore().load({
						callback: function(records, options, success) {
							if ( success ) {
								base_form.findField('Org_did').setValue(data.Org_did);
								base_form.findField('Org_did').fireEvent('change', base_form.findField('Org_did'), base_form.findField('Org_did').getValue());
							}
						},
						params: {
							Org_id: data.Org_did,
							OrgType: org_type
						}
					});
				}
			}
		}
		
		base_form.findField('EvnDirection_Num').setValue(data.EvnDirection_Num);
		base_form.findField('EvnDirection_setDate').setValue(data.EvnDirection_setDate);

		if ( data.Diag_did ) {
			base_form.findField('Diag_did').getStore().load({
				callback: function() {
					base_form.findField('Diag_did').getStore().each(function(record) {
						if ( record.get('Diag_id') == data.Diag_did ) {
							base_form.findField('Diag_did').setValue(data.Diag_did);
							base_form.findField('Diag_did').disable();
							base_form.findField('Diag_did').fireEvent('select', base_form.findField('Diag_did'), record, 0);
						}
					});
				},
				params: { where: "where Diag_id = " + data.Diag_did }
			});
		}
	},
	printEvnPS: function() {
		if ( 'add' == this.action || 'edit' == this.action ) {
			this.doSave({
				print: true
			});
		}
		else if ( 'view' == this.action ) {
			var evn_ps_id = this.findById('EvnPSPriemEditForm').getForm().findField('EvnPS_id').getValue(),
				params = {};

			params.EvnPS_id = evn_ps_id;
			printEvnPS(params);
		}
	},
	setDisableFields: function() {
		var bf = this.findById('EvnPSPriemEditForm').getForm();
		for ( var i = 0; i < this.disableFields.length; i++ ) {
			bf.findField(this.disableFields[i]).disable();
		}
	},
	setEnableField: function(f) {
		var bf = this.findById('EvnPSPriemEditForm').getForm();
		if(this.disableFields.indexOf(f) < 0)
			bf.findField(f).enable();
	},
	setMKB: function(){
		var parentWin =this
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var sex = parentWin.findById('EPSPEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('EPSPEF_PersonInformationFrame').getFieldValue('Person_Birthday'),base_form.findField('EvnPS_setDate').getValue());
		base_form.findField('Diag_did').setMKBFilter(age,sex,true);
		base_form.findField('Diag_pid').setMKBFilter(age,sex,true);
	},
	filterLpuSectionProfile: function() {
		var win = this;
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		if ( getRegionNick() == 'ekb' ) {
			var params = {
				LpuSection_id: base_form.findField('LpuSection_pid').getValue(),
				MedPersonal_id: base_form.findField('MedStaffFact_pid').getFieldValue('MedPersonal_id'),
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
						index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
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

		if ( getRegionNick().inlist([ 'krym', 'buryatiya' ])) {
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

			if ( base_form.findField('LpuSectionProfile_id').getStore().getCount() == 0 ) {
				base_form.findField('LpuSectionProfile_id').getStore().load({
					callback: filterLSP
				});
			}
			else {
				filterLSP();
			}
		}

		if ( getRegionNick() == 'perm' ) {
			var params  = {
				LpuSection_id: base_form.findField('LpuSection_pid').getValue(),
				onDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y')
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
						index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
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
			var params  = {
				onDate: Ext.util.Format.date(base_form.findField('EvnPS_setDate').getValue(), 'd.m.Y')
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
						index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
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
	setSpecificsPanelVisibility: function() {
		var win = this;
		var base_form = win.findById('EvnPSPriemEditForm').getForm();
		var PrehospWaifRefuseCause_Code = base_form.findField('PrehospWaifRefuseCause_id').getFieldValue('PrehospWaifRefuseCause_Code');
		var Diag_pCode = base_form.findField('Diag_pid').getFieldValue('Diag_Code');
		
		if (getRegionNick() == 'perm' && !Ext.isEmpty(PrehospWaifRefuseCause_Code) && Diag_pCode && Diag_pCode.search(new RegExp("^(C|D0)", "i")) >= 0) {
			this.findById(this.id + '_SpecificsPanel').show();
			this.loadSpecificsTree();
		} else {
			this.findById(this.id + '_SpecificsPanel').hide();
		}
	},
	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.findById('EvnPSPriemEditForm').getForm();
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
					EvnPS_OutcomeDate = Ext.isEmpty(EvnPS_OutcomeDate) ? new Date():EvnPS_OutcomeDate;

					base_form.findField('DeseaseType_id').getStore().clearFilter();
					base_form.findField('DeseaseType_id').lastQuery = '';
					base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {
						return (
							(!rec.get('DeseaseType_begDT') || rec.get('DeseaseType_begDT') <= EvnPS_OutcomeDate)
							&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= EvnPS_OutcomeDate)
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
						enable = Region_Nick.inlist(['ekb']);
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
					if(getRegionNick() == 'ufa'){
						var prehospType_SysNick = base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick');
						allowBlank = (prehospType_SysNick && prehospType_SysNick == 'plan') ? false : true;
					}
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
	setMedicalCareFormTypeAllowBlank: function() {
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
			var base_form = this.findById('EvnPSPriemEditForm').getForm();
			var date = base_form.findField('EvnPS_setDate').getValue();
			var field = base_form.findField('PrehospArrive_id');
			var xdate = new Date(2016,9,1);
			if(Ext.isEmpty(date) || date>xdate)	{
				field.setAllowBlank(false);
			} else {
				field.setAllowBlank(true);
			}
		}
	},
	setDiagEidAllowBlank: function(clear) {
		if(getRegionNick() != 'kz'){
			var win = this;
			var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
		}
	},
	disableSetMedicalCareFormType: false,
	filterMedicalCareFormType: function() {
		var base_form = this.findById('EvnPSPriemEditForm').getForm();

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
				var LpuUnitType_SysNick = base_form.findField('LpuSection_eid').getFieldValue('LpuUnitType_SysNick');

				base_form.findField('MedicalCareFormType_id').getStore().clearFilter();
				base_form.findField('MedicalCareFormType_id').lastQuery = '';
				base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
					if (rec.get('MedicalCareFormType_id') == 1) {
						// Экстренная
						if (LpuUnitType_SysNick && LpuUnitType_SysNick.inlist(['dstac','hstac','pstac'])) {
							return false;
						} else {
							return true;
						}
					} else {
						return true;
					}
				});

				// если значения больше нет в сторе очищаем поле
				var MedicalCareFormType_id = base_form.findField('MedicalCareFormType_id').getValue();
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
					|| !base_form.findField('LpuSection_eid').getFieldValue('LpuUnitType_SysNick').inlist([ 'dstac', 'hstac', 'pstac' ])
				) {
					base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
						return (rec.get('MedicalCareFormType_Code') != 1);
					});
				}
			break;
		}
	},
	setMedicalCareFormType: function() {
		if (this.disableSetMedicalCareFormType || this.isProcessLoadForm) {
			return;
		}

		var base_form = this.findById('EvnPSPriemEditForm').getForm();

		switch(getRegionNick()) {
			case 'perm':
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
				// Если отказ в приемном отделении, то 2 «Неотложная»;
				if (!Ext.isEmpty(base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick')) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick').inlist(['plan'])) {
					// Если Тип госпитализации = 1. Планово, то 3 «Плановая»;
					base_form.findField('MedicalCareFormType_id').setValue(3);
				} else if (!Ext.isEmpty(base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick')) && base_form.findField('PrehospType_id').getFieldValue('PrehospType_SysNick').inlist(['oper', 'extreme'])) {
					// Иначе если Тип госпитализации = 2. Экстренно или 3. Экстренно по хирургическим показаниям
					var LpuUnitType_SysNick = base_form.findField('LpuSection_eid').getFieldValue('LpuUnitType_SysNick');
					if (LpuUnitType_SysNick && LpuUnitType_SysNick.inlist(['dstac','hstac','pstac'])) {
						// Если отделение из группы отделений типа «3. Дневной стационар при стационаре» или «4. Стационар на дому» или «5. Дневной стационар при поликлинике», то значение «экстренная» не доступна для выбора. то 2 «Плановая».
						base_form.findField('MedicalCareFormType_id').setValue(2);
					} else {
						// иначе 1 «Экстренная».
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
					base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
						return (rec.get('MedicalCareFormType_Code') != 3);
					});
					base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 1);
				}
				break;
		}
	},
	loadMedStaffFactDidCombo: function() {
    	if (getRegionNick().inlist([ 'ekb', 'perm' ])) {
			var
				base_form = this.findById('EvnPSPriemEditForm').getForm(),
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
	setCovidFieldsAllowBlank: function() {
		if (getRegionNick() != 'msk') {
			return;
		}
		
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
	show: function() {
		sw.Promed.swEvnPSPriemEditWindow.superclass.show.apply(this, arguments);

		var thisWin = this;

		this.RepositoryObservData = {};
		this.lastLpuSectionProfileParams = null;
		this.disableSetMedicalCareFormType = false;

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		base_form.reset();
		base_form.findField('EvnPS_OutcomeDate').fireEvent('change', base_form.findField('EvnPS_OutcomeDate'), base_form.findField('EvnPS_OutcomeDate').getValue());
		base_form.findField('LpuSection_eid').fireEvent('change', base_form.findField('LpuSection_eid'), base_form.findField('LpuSection_eid').getValue());
		
		if ( getRegionNick().inlist([ 'ekb', 'perm' ]) ) {
			base_form.findField('UslugaComplex_id').clearBaseParams();
			base_form.findField('UslugaComplex_id').getStore().baseParams.filterByLpuSection = 1;

			if ( getRegionNick().inlist([ 'ekb' ]) ) {
				base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300,301]);
			}

			if ( getRegionNick().inlist([ 'perm' ]) ) {
				base_form.findField('UslugaComplex_id').setVizitCodeFilters({
					isStac: true
				});
				base_form.findField('UslugaComplex_id').getStore().baseParams.isEvnPS = 1;

				base_form.findField('ResultDeseaseType_fedid').getStore().clearFilter();
				base_form.findField('ResultDeseaseType_fedid').lastQuery = '';
				base_form.findField('ResultDeseaseType_fedid').getStore().filterBy(function(rec) {
					return (rec.get('ResultDeseaseType_Code').toString().substr(0, 1) == '3');
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

			if ( getRegionNick().inlist([ 'buryatiya' ]) ) {
				if ( base_form.findField('UslugaComplex_id').getStore().getCount() == 0 ) {
					base_form.findField('UslugaComplex_id').setUslugaComplexCodeList([ '021613', '061129', '161129' ]);
				}
			}
		}
		else {
			base_form.findField('LeaveType_prmid').setContainerVisible(false);
		}

		base_form.findField('LpuSectionBedProfileLink_id').setContainerVisible(false);

		if (!getRegionNick().inlist([ 'kz' ])) {
			thisWin.findById(thisWin.id + '_TltPanel').setVisible(false);
		}

		if ( getRegionNick().inlist([ 'krym', 'penza' ]) ) {
			this.filterMedicalCareFormType();
		}		
		
		setMedStaffFactGlobalStoreFilter({
			EvnClass_SysNick: 'EvnPS',
			isStac:true
		});
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
/*
		setLpuSectionGlobalStoreFilter({
			isStac: true
			//,onDate: Ext.util.Format.date(newValue, 'd.m.Y')
		});
		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
*/
		if ( this.firstRun == true ) {
			this.findById('EPSPEF_HospitalisationPanel').collapse();
			this.findById('EPSPEF_DirectDiagPanel').collapse();
			this.findById('EPSPEF_AdmitDepartPanel').collapse();
			this.findById('EPSPEF_AdmitDiagPanel').collapse();
			this.findById('EPSPEF_PriemLeavePanel').collapse();
			this.findById('EPSPEF_EvnUslugaPanel').collapse();
			this.findById('EPSPEF_EvnDrugPanel').collapse();
			this.findById('EPSPEF_PrehospWaifPanel').collapse();
		}
		
		this.formStatus = 'edit';
		this.isCopy = false;
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'));
			return false;
		}
		
		base_form.setValues(arguments[0]);
		this.action = arguments[0].action || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.disableFields = arguments[0].disableFields || [];
		this.TimetableStac_id = arguments[0].TimetableStac_id || null;
		this.userMedStaffFact = arguments[0].userMedStaffFact || {};
		this.activatePanel = arguments[0].activatePanel || null;

		this.findById('EPSPEF_HospitalisationPanel').hide();
		this.findById('EPSPEF_DirectDiagPanel').hide();
		this.findById('EPSPEF_AdmitDepartPanel').hide();
		this.findById('EPSPEF_AdmitDiagPanel').hide();
		this.findById('EPSPEF_PriemLeavePanel').hide();
		this.findById('EPSPEF_EvnUslugaPanel').hide();
		this.findById('EPSPEF_EvnDrugPanel').hide();
		this.findById('EPSPEF_PrehospWaifPanel').hide();

		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_setDate').disable();
		base_form.findField('LpuSection_did').disable();
		base_form.findField('Org_did').disable();
		base_form.findField('PrehospTrauma_id').setAllowBlank(true);

		if ( this.action == 'add' ) {
			this.findById('EPSPEF_DirectDiagPanel').isLoaded = true;
			this.findById('EPSPEF_AdmitDiagPanel').isLoaded = true;
			this.findById('EPSPEF_EvnUslugaPanel').isLoaded = true;
			this.findById('EPSPEF_EvnDrugPanel').isLoaded = true;
		}
		else {
			this.findById('EPSPEF_DirectDiagPanel').isLoaded = false;
			this.findById('EPSPEF_AdmitDiagPanel').isLoaded = false;
			this.findById('EPSPEF_EvnUslugaPanel').isLoaded = false;
			this.findById('EPSPEF_EvnDrugPanel').isLoaded = false;
		}

		this.findById('EPSPEF_EvnDiagPSHospGrid').getStore().removeAll();
		this.findById('EPSPEF_EvnDiagPSHospGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPSPEF_EvnDiagPSHospGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSPEF_EvnDiagPSHospGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSPEF_EvnDiagPSHospGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSPEF_EvnDiagPSRecepGrid').getStore().removeAll();
		this.findById('EPSPEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPSPEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSPEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSPEF_EvnDiagPSRecepGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSPEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('EPSPEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPSPEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSPEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSPEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPSPEF_EvnDrugGrid').getStore().removeAll();
		this.findById('EPSPEF_EvnDrugGrid').getTopToolbar().items.items[0].enable();
		this.findById('EPSPEF_EvnDrugGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPSPEF_EvnDrugGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPSPEF_EvnDrugGrid').getTopToolbar().items.items[3].disable();
		base_form.findField('LpuSectionWard_id').setContainerVisible(
			!Ext.isEmpty(base_form.findField('LpuSection_eid').getValue())
		);

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
					from: 'workplacepriem',
					MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && typeof sw.Promed.MedStaffFactByUser.current == 'object' && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null),
					ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && typeof sw.Promed.MedStaffFactByUser.current == 'object' && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType) ? sw.Promed.MedStaffFactByUser.current.ARMType : null)
				},
				success: function(response, options) {
					if (!Ext.isEmpty(response.responseText)) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success == false ) {
							sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при загрузке данных формы');
							thisWin.action = 'view';
						}
					}

					thisWin.onShow(arguments);
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		}
		else {
			thisWin.onShow(arguments);
		}
	},
	visibleBlockblockPediculos: function(){
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
		var blockPediculos = Ext.getCmp('EPSPEF_blockPediculos');
		var comboPediculosDiag = base_form.findField('PediculosDiag_id');
		var pediculos_Sanitation_setDate = base_form.findField('Pediculos_Sanitation_setDate');
		var pediculos_Sanitation_setTime = base_form.findField('Pediculos_Sanitation_setTime');
		var buttonsPediculosPrint = Ext.getCmp('EPSPEF_PediculosPrint');
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
	onShow: function(arguments) {
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		var thisWin = this;

		var base_form = this.findById('EvnPSPriemEditForm').getForm();
/*
		setLpuSectionGlobalStoreFilter();

		prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, null);
*/
		var evn_ps_id = base_form.findField('EvnPS_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();

		var set_date_field = base_form.findField('EvnPS_setDate');
		var iswd_combo = base_form.findField('EvnPS_IsWithoutDirection');
		var diag_d_combo = base_form.findField('Diag_did');
		var diag_p_combo = base_form.findField('Diag_pid');
		var diag_e_combo = base_form.findField('Diag_eid');
		var EvnPS_CodeConv = base_form.findField('EvnPS_CodeConv');
		var EvnPS_NumConv = base_form.findField('EvnPS_NumConv');
		var EvnPS_CodeConv_val = EvnPS_CodeConv.getValue();
		var EvnPS_NumConv_val = EvnPS_NumConv.getValue();
		var diag_e_combo = base_form.findField('Diag_eid');
		var diag_p_phase_combo = base_form.findField('DiagSetPhase_pid');
		var lpu_section_dir_combo = base_form.findField('LpuSection_did');
		var lpu_section_rec_combo = base_form.findField('LpuSection_pid');
		var med_staff_fact_rec_combo = base_form.findField('MedStaffFact_pid');
		var org_combo = base_form.findField('Org_did');
		var prehosp_arrive_combo = base_form.findField('PrehospArrive_id');
		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var prehosp_trauma_combo = base_form.findField('PrehospTrauma_id');
		var prehosp_type_combo = base_form.findField('PrehospType_id');
		var lpu_section_hosp_combo = base_form.findField('LpuSection_eid');
		var refuse_cause_combo = base_form.findField('PrehospWaifRefuseCause_id');
		var okei_combo = base_form.findField('Okei_id');
		this.findById('EPSPEF_PrehospWaifRefuseCauseButton').disable();
		var is_waif_combo = base_form.findField('EvnPS_IsWaif');
		var priemDiag = (getGlobalOptions().check_priemdiag_allow && getGlobalOptions().check_priemdiag_allow=='1');
        is_waif_combo.setAllowBlank(true);
		var person_info = this.findById('EPSPEF_PersonInformationFrame');
		var cmp_call_card_combo = base_form.findField('CmpCallCard_id');
		//var EvnPS_OutcomeDate = base_form.findField('EvnPS_OutcomeDate').getValue();
		//var EvnPS_OutcomeTime = base_form.findField('EvnPS_OutcomeTime').getValue();

		okei_combo.setValue(100); // По умолчанию: час

		diag_p_combo.setAllowBlank(!priemDiag);
		diag_p_phase_combo.setAllowBlank(false);
		
		prehosp_direct_combo.getStore().clearFilter();

		lpu_section_dir_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		set_date_field.setMinValue(undefined);
		this.setSpecificsPanelVisibility();

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);

				if (false && set_date_field.getValue()) {
					set_date_field.fireEvent('change', set_date_field, set_date_field.getValue());
				} else {
					setCurrentDateTime({
						callback: function() {
							set_date_field.fireEvent('change', set_date_field, set_date_field.getValue());
						},
						dateField: set_date_field,
						loadMask: false,
						setDate: true,
						setDateMaxValue: true,
						setTime: true,
						timeField: base_form.findField('EvnPS_setTime'),
						windowId: this.id
					});
				}
				
				base_form.findField('UslugaComplex_id').setPersonId(base_form.findField('Person_id').getValue());
				base_form.findField('PrehospWaifRefuseCause_id').fireEvent('change', base_form.findField('PrehospWaifRefuseCause_id'), base_form.findField('PrehospWaifRefuseCause_id').getValue());
				base_form.findField('MedStaffFact_did').fireEvent('change', base_form.findField('MedStaffFact_did'), base_form.findField('MedStaffFact_did').getValue());

				person_info.load({
					callback: function() {
						var
							// Возраст пациента:
							age = person_info.getFieldValue('Person_Age'),
							// Поле "Дееспособен":
							isActive_combo = base_form.findField('EvnPS_IsActive');

						loadMask.hide();
						person_info.setPersonTitle();
						base_form.findField('EvnPS_setDate').setMinValue(person_info.getFieldValue('Person_Birthday'));
						if (age < 18)
						{
							this.findById('EPSPEF_PrehospWaifPanel').show();
							is_waif_combo.setAllowBlank(false);
							is_waif_combo.fireEvent('change', is_waif_combo,1, null);
						}

						// Заполняем поле "Дееспособен" значением "Нет", если пациенту меньше 18 лет, и
						// значением "Да" в противном случае:
						if (isActive_combo)
							isActive_combo.setValue(age < 18 ? 1 : 2);

						this.setDisableFields();
						this.setMKB();
					}.createDelegate(this),
					Person_id: person_id,
					Server_id: server_id
				});

				if ( this.firstRun == true ) {
					this.findById('EPSPEF_HospitalisationPanel').expand();
					this.findById('EPSPEF_AdmitDepartPanel').expand();

					this.firstRun = false;
				}

				// base_form.clearInvalid();

				lpu_section_rec_combo.fireEvent('change', lpu_section_rec_combo, lpu_section_rec_combo.getValue());
				prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_combo.getValue());
				base_form.findField('EvnPS_IsUnlaw').fireEvent('change', base_form.findField('EvnPS_IsUnlaw'), base_form.findField('EvnPS_IsUnlaw').getValue());

				this.getEvnPSNumber();

				this.findById('EPSPEF_HospitalisationPanel').show();
				this.findById('EPSPEF_DirectDiagPanel').show();
				this.findById('EPSPEF_AdmitDepartPanel').show();
				this.findById('EPSPEF_AdmitDiagPanel').show();
				this.findById('EPSPEF_PriemLeavePanel').show();
				this.findById('EPSPEF_EvnUslugaPanel').show();
				this.findById('EPSPEF_EvnDrugPanel').show();

				LoadEmptyRow(this.findById('EPSPEF_EvnDiagPSHospGrid'));
				LoadEmptyRow(this.findById('EPSPEF_EvnDiagPSRecepGrid'));
				LoadEmptyRow(this.findById('EPSPEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('EPSPEF_EvnDrugGrid'));

				if ( !prehosp_type_combo.getValue() ) {
					prehosp_type_combo.setValue(2);
				}

				prehosp_type_combo.getStore().on('load', function(store, records, index) {
					prehosp_type_combo.setValue(prehosp_type_combo.getValue());
				});

				// #145312 Обязательность полей «№ направления» и «Дата направления» при типе госпитализации «1. Планово»
				if(getRegionNick().inlist(['ufa']) && prehosp_type_combo.getValue() == 2){
					base_form.findField('EvnDirection_Num').setAllowBlank(false);
					base_form.findField('EvnDirection_setDate').setAllowBlank(false);
				}

				prehosp_arrive_combo.getStore().on('load', function(store, records, index) {
					prehosp_arrive_combo.setValue(prehosp_arrive_combo.getValue());
				});

				if ( arguments[0].EvnDirection_id ) {
					this._onSelectEvnDirection(arguments[0]);
				} else {
					var org_did = org_combo.getValue();
					iswd_combo.setValue(1);
					prehosp_arrive_combo.fireEvent('change', prehosp_arrive_combo, prehosp_arrive_combo.getValue());
					EvnPS_CodeConv.setValue(EvnPS_CodeConv_val);
					EvnPS_NumConv.setValue(EvnPS_NumConv_val);
					iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
					prehosp_direct_combo.getStore().on('load', function(store, records, index) {
						prehosp_direct_combo.setValue(prehosp_direct_combo.getValue());
						var record = prehosp_direct_combo.getStore().getById(prehosp_direct_combo.getValue());
						if ( !record ) {
							return;
						}
						var org_type = '';
						switch ( record.get('PrehospDirect_Code') ) {
							case 14:
							case 2:
								org_type = 'lpu';
							break;
							case 3:
							case 4:
							case 5:
							case 6:
								org_type = 'org';
							break;
						}

						if ( org_type.length > 0 && org_did ) {
							org_combo.getStore().load({
								callback: function(records, options, success) {
									org_combo.clearValue();

									if ( success ) {
										org_combo.setValue(org_did);
									}

									org_combo.fireEvent('change', org_combo, org_combo.getValue());
								},
								params: {
									Org_id: org_did,
									OrgType: org_type
								}
							});
						}
						else {
							org_combo.clearValue();
							org_combo.fireEvent('change', org_combo, org_combo.getValue());
						}
					});
				}

				if ( diag_d_combo.getValue() )
				{
					var diag_did = diag_d_combo.getValue()
					diag_d_combo.getStore().load({
						callback: function() {
							diag_d_combo.setValue(diag_did);
							diag_d_combo.fireEvent('select', diag_d_combo, diag_d_combo.getStore().getAt(0), 0);
							diag_d_combo.disable();
							if(getRegionNick() == 'ufa' && diag_d_combo.getCode().inlist(thisWin.OksDiagCode))
								thisWin.loadUslugaGrid();
						},
						params: {
							where: "where Diag_id = " + diag_did
						}
					});
				}
				if(getRegionNick()=='ekb') this.checkZNO({action: this.action });
				this.setMedicalCareFormType();
				this.setPrehospArriveAllowBlank();
				this.setDiagEidAllowBlank();
				this.refreshFieldsVisibility();
				this.visibleBlockblockPediculos();
				if (getRegionNick() != 'msk') this.checkAndOpenRepositoryObserv();
			break;

			case 'edit':
			case 'view':
				if ( 'edit' == this.action ) {
					this.enableEdit(true);

				}
				else {
					this.enableEdit(false);
					this.buttons[this.buttons.length - 1].focus();
				}

				this.findById('EPSPEF_HospitalisationPanel').show();
				this.findById('EPSPEF_DirectDiagPanel').show();
				this.findById('EPSPEF_AdmitDepartPanel').show();
				this.findById('EPSPEF_AdmitDiagPanel').show();
				this.findById('EPSPEF_PriemLeavePanel').show();
				if(this.activatePanel && this.findById(this.activatePanel)){
					this.findById(this.activatePanel).expand();
				}
				this.findById('EPSPEF_PriemLeavePanel').expand();
				this.findById('EPSPEF_EvnUslugaPanel').show();
				this.findById('EPSPEF_EvnDrugPanel').show();

				this.isProcessLoadForm = true;
				this.disableSetMedicalCareFormType = true;
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnPS_id: evn_ps_id
					},
					success: function(a,v,b) {
						loadMask.hide();

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

						this.findById('EPSPEF_EvnPS_IsZNOCheckbox').setValue(base_form.findField('EvnPS_IsZNO').getValue() == 2);
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
								params:{where:"where Diag_id = " + diag_spid}
							});
						}
						if(getRegionNick()=='ekb') {
							this.checkZNO({action: this.action });
							this.checkBiopsyDate();
						}

						var evn_direction_id = base_form.findField('EvnDirection_id').getValue();
						var evn_direction_num = base_form.findField('EvnDirection_Num').getValue();
						var evn_direction_set_date = base_form.findField('EvnDirection_setDate').getValue();
						var evn_ps_code_conv = base_form.findField('EvnPS_CodeConv').getValue();
						var evn_ps_is_cont = base_form.findField('EvnPS_IsCont').getValue();
						var evn_ps_is_unlaw = base_form.findField('EvnPS_IsUnlaw').getValue();
						this.evn_ps_notification_date = base_form.findField('EvnPS_NotificationDate').getValue();
						this.evn_ps_notification_time = base_form.findField('EvnPS_NotificationTime').getValue();
						var evn_ps_num_conv = base_form.findField('EvnPS_NumConv').getValue();
						var LeaveType_prmid = base_form.findField('LeaveType_prmid').getValue();
						var lpu_section_did = lpu_section_dir_combo.getValue();
						var med_staff_fact_pid = med_staff_fact_rec_combo.getValue();
						var org_did = org_combo.getValue();
						var prehosp_arrive_id = prehosp_arrive_combo.getValue();
						var prehosp_direct_id = prehosp_direct_combo.getValue();
						var prehosp_trauma_id = prehosp_trauma_combo.getValue();
						var diag_did = diag_d_combo.getValue();
						var lpu_section_pid = lpu_section_rec_combo.getValue();
						var diag_pid = diag_p_combo.getValue();
						var diag_eid = diag_e_combo.getValue();
						var
							LeaveType_fedid = base_form.findField('LeaveType_fedid').getValue(),
							LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue(),
							MedicalCareFormType_id = base_form.findField('MedicalCareFormType_id').getValue(),
							ResultDeseaseType_fedid = base_form.findField('ResultDeseaseType_fedid').getValue();

						base_form.findField('EvnPS_OutcomeDate').fireEvent('change', base_form.findField('EvnPS_OutcomeDate'), base_form.findField('EvnPS_OutcomeDate').getValue());
						
						var lpu_section_id = lpu_section_hosp_combo.getValue();
						var prehospwaif_refuse_cause_id = refuse_cause_combo.getValue();
						var is_waif_yesno = is_waif_combo.getValue();
						var cmp_call_card_id = cmp_call_card_combo.getValue();

						// base_form.findField('EvnPS_OutcomeDate').setValue(EvnPS_OutcomeDate);
						// base_form.findField('EvnPS_OutcomeTime').setValue(EvnPS_OutcomeTime);

						if ( !lpu_section_pid && 'edit' == this.action ) {
							lpu_section_pid = this.userMedStaffFact.LpuSection_id;
							lpu_section_rec_combo.setValue(lpu_section_pid);
						}
						
						if ( !diag_pid && 'edit' == this.action ) {
							diag_p_combo.enable();
						}
						
						base_form.findField('UslugaComplex_id').setPersonId(base_form.findField('Person_id').getValue());
						base_form.findField('PrehospWaifRefuseCause_id').fireEvent('change', base_form.findField('PrehospWaifRefuseCause_id'), base_form.findField('PrehospWaifRefuseCause_id').getValue());
						base_form.findField('MedStaffFact_did').fireEvent('change', base_form.findField('MedStaffFact_did'), base_form.findField('MedStaffFact_did').getValue());
						
						if ( !Ext.isEmpty(base_form.findField('UslugaComplex_id').getValue()) ) {
							base_form.findField('UslugaComplex_id').getStore().load({
								callback: function() {
									index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
										return (rec.get('UslugaComplex_id') == base_form.findField('UslugaComplex_id').getValue());
									});

									if ( index >= 0 ) {
										base_form.findField('UslugaComplex_id').setValue(base_form.findField('UslugaComplex_id').getValue());
									}
									else {
										base_form.findField('UslugaComplex_id').clearValue();
									}

									base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
								}.createDelegate(this),
								params: {
									UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue()
								}
							});
						}
						
						person_info.load({
							callback: function() {
								person_info.setPersonTitle();
								base_form.findField('EvnPS_setDate').setMinValue(person_info.getFieldValue('Person_Birthday'));
								if(person_info.getFieldValue('Person_Age') < 18)
								{
									this.findById('EPSPEF_PrehospWaifPanel').show();
									is_waif_combo.setAllowBlank(false);
									is_waif_combo.fireEvent('change', is_waif_combo, is_waif_yesno, null);
								}
								lpu_section_rec_combo.fireEvent('change', lpu_section_rec_combo, lpu_section_pid);
								if('edit' == this.action)
								{
									var omsSprTerrCode = person_info.getFieldValue('OmsSprTerr_Code');

									var isPerm = (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm');

									var record;

									base_form.findField('EvnPS_IsCont').fireEvent('change', base_form.findField('EvnPS_IsCont'), evn_ps_is_cont);

									if ( lpu_section_pid ) {
										diag_p_combo.enable();
									}

									if ( evn_direction_id ) {
										iswd_combo.setValue(2);
										iswd_combo.disable();
										this.disableFields.push('EvnPS_IsWithoutDirection');
									} else {
										iswd_combo.setValue(1);
									}
									iswd_combo.fireEvent('change', iswd_combo, iswd_combo.getValue());
									
									if ( prehosp_direct_id != null && prehosp_direct_id.toString().length > 0 ) {
										// prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_id, -1);
										prehosp_direct_combo.disable();


										record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

										if ( !record ) {
											loadMask.hide();
											return false;
										}

										var prehosp_direct_code = record.get('PrehospDirect_Code');
										var org_type = '';

										// https://redmine.swan.perm.ru/issues/4549
										if ( prehosp_direct_code && isPerm == true && Number(omsSprTerrCode) > 100 ) {
											lpu_section_rec_combo.setAllowBlank(true);
											med_staff_fact_rec_combo.setAllowBlank(true);
											diag_p_combo.setAllowBlank(true);
										}

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
											case 14:
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

												// https://redmine.swan.perm.ru/issues/4549
												if ( isPerm == true ) {
													lpu_section_rec_combo.setAllowBlank(false);
													med_staff_fact_rec_combo.setAllowBlank(false);
													diag_p_combo.setAllowBlank(false);
												}
											break;
										}

										if ( org_type.length > 0 && org_did ) {
											org_combo.getStore().load({
												callback: function(records, options, success) {
													org_combo.clearValue();

													if ( success ) {
														org_combo.setValue(org_did);
													}

													base_form.findField('MedStaffFact_did').setValue(MedStaffFact_did);
													org_combo.fireEvent('change', org_combo, org_combo.getValue());
												},
												params: {
													Org_id: org_did,
													OrgType: org_type
												}
											});
										}
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
									base_form.findField('EvnDirection_id').setValue(evn_direction_id);
									base_form.findField('EvnDirection_Num').setValue(evn_direction_num);
									base_form.findField('EvnDirection_setDate').setValue(evn_direction_set_date);

									prehosp_arrive_combo.fireEvent('change', prehosp_arrive_combo, prehosp_arrive_id, -1);
									base_form.findField('EvnPS_CodeConv').setValue(evn_ps_code_conv);
									base_form.findField('EvnPS_NumConv').setValue(evn_ps_num_conv);

									base_form.findField('EvnPS_IsUnlaw').setValue(evn_ps_is_unlaw);
									prehosp_trauma_combo.fireEvent('change', prehosp_trauma_combo, prehosp_trauma_id, -1);
									base_form.findField('EvnPS_IsUnlaw').setValue(evn_ps_is_unlaw);
									base_form.findField('EvnPS_IsUnlaw').fireEvent('change', base_form.findField('EvnPS_IsUnlaw'), base_form.findField('EvnPS_IsUnlaw').getValue());

									// base_form.clearInvalid();

									this.setDisableFields();
									
									base_form.findField('EvnPS_IsCont').focus(true, 250);

									this.disableSetMedicalCareFormType = false;

									this.filterMedicalCareFormType();

									if ( !Ext.isEmpty(LpuSectionProfile_id) ) {
										base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
									}

									if ( !Ext.isEmpty(MedicalCareFormType_id) ) {
										base_form.findField('MedicalCareFormType_id').setValue(MedicalCareFormType_id);
									}
								}
							}.createDelegate(this),
							Person_id: base_form.findField('Person_id').getValue(),
							PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue()
						});
						var TraumaCircumEvnPS_setDT = v.result.data.TraumaCircumEvnPS_setDT;
						if ( !getRegionNick().inlist['kz'] && !Ext.isEmpty(TraumaCircumEvnPS_setDT)) {
							base_form.findField('TraumaCircumEvnPS_setDTDate').setValue(Ext.util.Format.date(TraumaCircumEvnPS_setDT, 'd.m.Y'));
							base_form.findField('TraumaCircumEvnPS_setDTTime').setValue(Ext.util.Format.date(TraumaCircumEvnPS_setDT, 'H:i'));
						}
						
						// Остальные гриды - только если развернуты панельки
						if ( !this.findById('EPSPEF_DirectDiagPanel').collapsed ) {
							this.findById('EPSPEF_DirectDiagPanel').fireEvent('expand', this.findById('EPSPEF_DirectDiagPanel'));
						}

						if ( !this.findById('EPSPEF_AdmitDiagPanel').collapsed ) {
							this.findById('EPSPEF_AdmitDiagPanel').fireEvent('expand', this.findById('EPSPEF_AdmitDiagPanel'));
						}


						if ( !this.findById('EPSPEF_EvnUslugaPanel').collapsed ) {
							this.findById('EPSPEF_EvnUslugaPanel').fireEvent('expand', this.findById('EPSPEF_EvnUslugaPanel'));
						}

						if ( !this.findById('EPSPEF_EvnDrugPanel').collapsed ) {
							this.findById('EPSPEF_EvnDrugPanel').fireEvent('expand', this.findById('EPSPEF_EvnDrugPanel'));
						}
						
						set_date_field.fireEvent('change', set_date_field, set_date_field.getValue());

						var index = med_staff_fact_rec_combo.getStore().findBy(function(record, id) {
							if ( record.get('MedStaffFact_id') == med_staff_fact_pid )
								return true;
							else
								return false;
						})

						if ( index >= 0 ) {
							med_staff_fact_rec_combo.setValue(med_staff_fact_rec_combo.getStore().getAt(index).get('MedStaffFact_id'));
							med_staff_fact_rec_combo.fireEvent('change', med_staff_fact_rec_combo, med_staff_fact_rec_combo.getValue());
						}

						if (!getRegionNick().inlist([ 'kz' ])) {
							var isCmpTlt = !Ext.isEmpty(base_form.findField('EvnPS_CmpTltDate').getValue());
							thisWin.findById(thisWin.id + '_isCmpTlt').setValue(isCmpTlt);
							thisWin.findById(thisWin.id + '_isCmpTlt').setVisibleFormDT(isCmpTlt);
							if(!diag_pid){
								thisWin.findById(thisWin.id + '_TltPanel').setVisible(false);
								base_form.findField('EvnPS_TimeDesease').setAllowBlank(true);
							}
						}

						// #145312 Обязательность полей «№ направления» и «Дата направления» при типе госпитализации «1. Планово»
						if(getRegionNick().inlist(['ufa']) && prehosp_type_combo.getFieldValue('PrehospType_SysNick') == 'plan'){
							base_form.findField('EvnDirection_Num').setAllowBlank(false);
							base_form.findField('EvnDirection_setDate').setAllowBlank(false);
						}

						if ( diag_did )
						{
							diag_d_combo.getStore().load({
								callback: function() {
									diag_d_combo.setValue(diag_did);
									diag_d_combo.fireEvent('select', diag_d_combo, diag_d_combo.getStore().getAt(0), 0);
									diag_d_combo.disable();
									if(getRegionNick() == 'ufa' && diag_d_combo.getCode().inlist(thisWin.OksDiagCode))
										thisWin.loadUslugaGrid();
								},
								params: {
									where: "where Diag_id = " + diag_did
								}
							});
						}

						if ( diag_pid )
						{
							diag_p_combo.getStore().load({
								callback: function() {
									diag_p_combo.fireEvent('select', diag_p_combo, diag_p_combo.getStore().getAt(0), 0);
									thisWin.setDiagEidAllowBlank();
									thisWin.refreshFieldsVisibility();
									var diag_d_count = diag_d_combo.getStore().getCount();
									if (!getRegionNick().inlist([ 'kz' ])) {
										if (thisWin.getOKSDiag())
											thisWin.loadUslugaGrid();
										var isIshemic = diag_p_combo.getGroup().inlist(thisWin.IshemiaCode);
										thisWin.findById(thisWin.id + '_TltPanel').setVisible(isIshemic);
										base_form.findField('EvnPS_TimeDesease').setAllowBlank(!isIshemic);
									}
								},
								params: {
									where: "where Diag_id = " + diag_pid
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
									where: "where Diag_id = " + diag_eid
								}
							});
						}

						if ( lpu_section_id )
						{
							index = lpu_section_hosp_combo.getStore().findBy(function(rec, id) {
								return (rec.get('LpuSection_id') == lpu_section_id);
							});

							lpu_section_hosp_combo.fireEvent('select', lpu_section_hosp_combo, lpu_section_hosp_combo.getStore().getAt(index), index);
						}

						refuse_cause_combo.fireEvent('change', refuse_cause_combo, prehospwaif_refuse_cause_id, null);

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
						if (getRegionNick().inlist['kareliya', 'krym', 'penza']) {
							this.setMedicalCareFormTypeAllowBlank();
						}
						
						this.setPrehospArriveAllowBlank();
						this.setDiagEidAllowBlank();
						this.isProcessLoadForm = false;
						this.setSpecificsPanelVisibility();
						this.visibleBlockblockPediculos();
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
		var diag_eid = diag_e_combo.getValue();
		if(getRegionNick().inlist(['vologda', 'ufa'])) {
			this.findById('TraumaCircumEvnPS_Name').setVisible((diag_eid == "") ? false : true);
			this.findById('TraumaCircumEvnPS_setDT').setVisible((diag_eid == "") ? false : true);
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
	pediculosPrint: function(save){
		var save = save || false;
		var base_form = this.findById('EvnPSPriemEditForm').getForm();
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
	}
});
