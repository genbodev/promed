/**
* swTariffDispEditWindow - окно редактирования/добавления тарифов СМП.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author		Dyomin Dmitry
* @version      06.12.2012
*/

sw.Promed.swTariffDispEditWindow = Ext.extend(sw.Promed.BaseForm,{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	id: 'TariffDispEditWindow',
	listeners: { hide: function(){ this.onHide(); } },
	Lpu_id: null,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function(){
		
		var base_form = this.FormPanel.getForm();
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (
			!Ext.isEmpty(base_form.findField('TariffDisp_begDT').getValue())
			&& !Ext.isEmpty(base_form.findField('TariffDisp_endDT').getValue())
			&& base_form.findField('TariffDisp_begDT').getValue() > base_form.findField('TariffDisp_endDT').getValue()
		) {
			sw.swMsg.alert(lang['oshibka'], lang['data_nachala_deystviya_tarifa_ne_mojet_byit_bolshe_datyi_okonchaniya'], function() {
				base_form.findField('TariffDisp_begDT').focus(true, 250);
			});
			return false;
		}

		this.submit();
		return true;
	},
	submit: function(){
		var form = this.FormPanel;
		var current_window = this;
		var loadMask = new Ext.LoadMask( this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		form.getForm().submit({
			params: {},
			failure: function( result_form, action ){
				loadMask.hide();
				if ( action.result ){
					if ( action.result.Error_Code ){
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action){
				loadMask.hide();
				if ( action.result ){
					if ( action.result.TariffDisp_id ){
						getWnd('swLpuStructureViewForm').findById('TariffDispGrid').loadData();
						current_window.hide();
					}else{
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function(){ form.hide(); },
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_v_sluchae_povtoreniya_oshibki_obratites_k_razrabotchikam'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	filterTariffClass: function() {
		// фильтрация типа тарифа в зависимости от профиля
		var base_form = this.findById('TariffDispEditForm').getForm();
		var TariffClass = base_form.findField('TariffClass_id');
		var ftc_id = TariffClass.getValue();
		var tclist = new Array();
		var tcfield = 'TariffClass_Code';

		var
			begDate = base_form.findField('TariffDisp_begDT').getValue(),
			endDate = base_form.findField('TariffDisp_endDT').getValue();

		switch ( getRegionNick() ) {
			case 'astra':
				tcfield = 'TariffClass_SysNick';
				//tclist = [ 26, 27, 28, 29, 30, 31, 32 ];
				tclist = [ 'disponebase', 'dispchildonebase', 'prof', 'profchildone', 'Periodchild', 'PredDetDosh', 'PredDetObch', 'PredDetObr' ];
			break;
			case 'pskov':
				tcfield = 'TariffClass_SysNick';
				tclist = [ 'disponebase', 'disptwobase', 'dispchildonebase', 'prof', 'profchildone', 'Periodchild', 'PredDetDosh', 'PredDetObch', 'PredDetObr' ];
				break;

			case 'kareliya':
				tclist = [ 15, 16, 17, 18, 19, 20, 21, 22, 25, 26, 27, 28, 77, 96, 99, 101, 102, 103, 104 ];
			break;

			default:
				var LpuSectionProfile_Code = base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code');

				switch ( Number(LpuSectionProfile_Code) ) {
					case 917:
						tclist = [24,25];
					break;
					case 918:
						tclist = [28];
					break;
					case 925:
						tclist = [37,38];
					break;
					case 1017:
						tclist = [22];
					break;
					case 1018:
						tclist = [26];
					break;
					/*case 1019:
						tclist = [23];
					break;*/
				}
			break;
		}

		TariffClass.getStore().clearFilter();
		TariffClass.lastQuery = '';
		TariffClass.getStore().filterBy(function(rec) {
			if ( rec.get(tcfield).inlist(tclist) ) {
				return true;
				/*// Фильтруем по датам действия
				if ( !Ext.isEmpty(begDate) || !Ext.isEmpty(endDate) ) {
					if ( Ext.isEmpty(rec.get('TariffClass_begDT')) && Ext.isEmpty(rec.get('TariffClass_endDT')) ) {
						return true;
					}

					if ( !Ext.isEmpty(begDate) && Ext.isEmpty(endDate) ) {
						return (
							(Ext.isEmpty(rec.get('TariffClass_begDT')) || typeof rec.get('TariffClass_begDT') != 'object' || rec.get('TariffClass_begDT') <= begDate)
							&& (Ext.isEmpty(rec.get('TariffClass_endDT')) || typeof rec.get('TariffClass_endDT') != 'object' || rec.get('TariffClass_endDT') >= begDate)
						);
					}
					else if ( Ext.isEmpty(begDate) && !Ext.isEmpty(endDate) ) {
						return (
							(Ext.isEmpty(rec.get('TariffClass_begDT')) || typeof rec.get('TariffClass_begDT') != 'object' || rec.get('TariffClass_begDT') <= endDate)
							&& (Ext.isEmpty(rec.get('TariffClass_endDT')) || typeof rec.get('TariffClass_endDT') != 'object' || rec.get('TariffClass_endDT') >= endDate)
						);
					}
					else {
						return (
							(Ext.isEmpty(rec.get('TariffClass_begDT')) || typeof rec.get('TariffClass_begDT') != 'object' || (rec.get('TariffClass_begDT') <= endDate && rec.get('TariffClass_begDT') <= begDate))
							&& (Ext.isEmpty(rec.get('TariffClass_endDT')) || typeof rec.get('TariffClass_endDT') != 'object' || (rec.get('TariffClass_endDT') >= endDate && rec.get('TariffClass_endDT') >= begDate))
						);
					}
				}
				else {
					return true;
				}*/
			}
			else {
				return false;
			}
		});

		if ( !Ext.isEmpty(ftc_id) ) {
			var index = TariffClass.getStore().findBy(function(rec) {
				return (rec.get('TariffClass_id') == ftc_id);
			});
			
			if ( index >= 0 ) {
				TariffClass.setValue(ftc_id);
			}
			else {
				TariffClass.clearValue();
			}

			this.filterAgeGroupDisp();
		}
	},
	filterAgeGroupDisp: function() {
		// фильтрация возрастной группы в зависимости от пола и вида тарифа
		var base_form = this.findById('TariffDispEditForm').getForm();
		var AgeGroupDisp = base_form.findField('AgeGroupDisp_id');
		var agd_id = AgeGroupDisp.getValue();
		AgeGroupDisp.getStore().clearFilter();
		AgeGroupDisp.lastQuery = '';
		AgeGroupDisp.getStore().filterBy(function(r){
			return false;
		});
		AgeGroupDisp.setAllowBlank(false);
		
		var sex_id = base_form.findField('Sex_id').getValue();
		var tariffclass_code = base_form.findField('TariffClass_id').getFieldValue('TariffClass_Code');
		var tariffclass_sysnick = base_form.findField('TariffClass_id').getFieldValue('TariffClass_SysNick');

		var
			disptype_id,
			filterSex = false,
			useAgeGroupDispDateFilter = false;

		var
			begDate = base_form.findField('TariffDisp_begDT').getValue(),
			endDate = base_form.findField('TariffDisp_endDT').getValue(),
			DateX = new Date(2015, 6, 1); // 01.07.2015

		if ( getRegionNick() == 'astra' ) {
			switch ( tariffclass_sysnick ) {
				case 'disponebase': // Дисп-ция взр. населения 1-ый этап
					disptype_id = 1;
					filterSex = true;
				break;

				case 'dispchildonebase': // Дисп-ция детей-сирот 1-ый этап
					disptype_id = 2;
					filterSex = true;
				break;

				case 'prof': // Проф.осмотры взр. населения 1-ый этап
					disptype_id = 3;
					filterSex = true;
				break;

				case 'profchildone': // Профосмотры детей-сирот 1-ый этап
					disptype_id = 4;
					filterSex = true;
				break;

				case 'Periodchild': // Периодические осмотры детей-сирот 
				case 'PredDetDosh': // Предварительные осмотры несовершеннолетних 1-ый этап (Дошкольные)
				case 'PredDetObch': // Предварительные осмотры несовершеннолетних 1-ый этап (Общеобразовательные)
				case 'PredDetObr': // Предварительные осмотры несовершеннолетних 1-ый этап (Образовательные)
					AgeGroupDisp.setAllowBlank(true);
					filterSex = true;
				break;
			}

			if ( filterSex ) {
				switch ( tariffclass_sysnick ) {
					case 'Periodchild':
						if (!Ext.isEmpty(sex_id) && !sex_id.inlist([3]) ) {
							base_form.findField('Sex_id').clearValue();
						}

						base_form.findField('Sex_id').getStore().clearFilter();
						base_form.findField('Sex_id').lastQuery = '';
						base_form.findField('Sex_id').getStore().filterBy(function(r){
							return r.get('Sex_id').inlist([3]);
						});
						base_form.findField('Sex_id').setValue(3);
						sex_id = 3;
					break;
					
					default:
						if (!Ext.isEmpty(sex_id) && !sex_id.inlist([1,2]) ) {
							base_form.findField('Sex_id').clearValue();
							sex_id = null;
						}

						base_form.findField('Sex_id').getStore().clearFilter();
						base_form.findField('Sex_id').lastQuery = '';
						base_form.findField('Sex_id').getStore().filterBy(function(r){
							return r.get('Sex_id').inlist([1,2]);
						});
					break;
				}
			} else {
				base_form.findField('Sex_id').getStore().clearFilter();
				base_form.findField('Sex_id').lastQuery = '';
			}
		}
		else if ( getRegionNick() == 'pskov' ) {
			switch ( tariffclass_sysnick ) {
				case 'disponebase': // Дисп-ция взр. населения 1-ый этап
					disptype_id = 1;
					filterSex = true;
					break;

				case 'disptwobase': // Дисп-ция взр. населения 2-ой этап
					AgeGroupDisp.setAllowBlank(true);
					filterSex = true;
					break;

				case 'dispchildonebase': // Дисп-ция детей-сирот 1-ый этап
					disptype_id = 2;
					filterSex = true;
					break;

				case 'prof': // Проф.осмотры взр. населения 1-ый этап
					AgeGroupDisp.setAllowBlank(true);
					filterSex = true;
					break;

				case 'profchildone': // Профосмотры детей-сирот 1-ый этап
					disptype_id = 4;
					filterSex = true;
					break;

				case 'Periodchild': // Периодические осмотры детей-сирот
				case 'PredDetDosh': // Предварительные осмотры несовершеннолетних 1-ый этап (Дошкольные)
				case 'PredDetObch': // Предварительные осмотры несовершеннолетних 1-ый этап (Общеобразовательные)
				case 'PredDetObr': // Предварительные осмотры несовершеннолетних 1-ый этап (Образовательные)
					AgeGroupDisp.setAllowBlank(true);
					filterSex = true;
					break;
			}

			if ( filterSex ) {
				switch ( tariffclass_sysnick ) {
					default:
						if (!Ext.isEmpty(sex_id) && !sex_id.inlist([1,2]) ) {
							base_form.findField('Sex_id').clearValue();
							sex_id = null;
						}

						base_form.findField('Sex_id').getStore().clearFilter();
						base_form.findField('Sex_id').lastQuery = '';
						base_form.findField('Sex_id').getStore().filterBy(function(r){
							return r.get('Sex_id').inlist([1,2]);
						});
						break;
				}
			} else {
				base_form.findField('Sex_id').getStore().clearFilter();
				base_form.findField('Sex_id').lastQuery = '';
			}
		}
		else if (getRegionNick() == 'kareliya') {
			switch(tariffclass_code) {
				case 15: // Дисп-ция взр. населения 1-ый этап
				case 101: // Дисп-ция взр. населения 1-ый этап (мобильная бригада)
					disptype_id = 1;
					filterSex = true;
					useAgeGroupDispDateFilter = true; // Фильтрация возрастных групп по дате
				break;

				case 16: // Дисп-ция детей-сирот 1-ый этап
				case 103: // Дисп-ция детей-сирот 1-ый этап (мобильная бригада)
					disptype_id = 2;
					AgeGroupDisp.setAllowBlank(false);
					filterSex = true;
				break;

				case 17: // Проф. осмотры взрослого населения
				case 102: // Проф. осмотры взрослого населения (мобильная бригада)
					if (begDate < new Date(2019, 5, 1)) {
						disptype_id = (sex_id == 2 ? 3 : null);
						AgeGroupDisp.setAllowBlank(sex_id == 1);
					} else {
						disptype_id = 3;
						AgeGroupDisp.setAllowBlank(false);
					}
					filterSex = true;
					useAgeGroupDispDateFilter = true; // Фильтрация возрастных групп по дате
				break;

				case 18: // Проф. осмотры несовершеннолетних 1-ый этап
				case 104: // Проф. осмотры несовершеннолетних 1-ый этап (мобильная бригада)
					disptype_id = 4;
					AgeGroupDisp.setAllowBlank(tariffclass_code == 18);
					filterSex = true;
					useAgeGroupDispDateFilter = true; // Фильтрация возрастных групп по дате
				break;

				case 19: // Периодические осмотры детей-сирот
				case 20: // Предварительные осмотры несовершеннолетних 1-ый этап (Дошкольные)
				case 21: // Предварительные осмотры несовершеннолетних 1-ый этап (Общеобразовательные)
				case 22: // Предварительные осмотры несовершеннолетних 1-ый этап (Образовательные)
				case 25: // Дисп-ция взр. населения 2-ый этап
				case 26: // Дисп-ция детей-сирот 2-ый этап
				case 27: // Предварительные осмотры несовершеннолетних 2-ый этап
				case 28: // Проф. осмотры несовершеннолетних 2-ый этап
				case 77: // Дисп-ция взр. населения 2-ый этап(Сред. персонал)
				case 96: // Дисп-ция взр. населения 1 раз в 2 года
				case 99: // Дисп-ция взр. населения 1 раз в 2 года: средний мед. персонал
					disptype_id = null;
					filterSex = true;
					AgeGroupDisp.setAllowBlank(true);
				break;
			}

			if (filterSex) {
				switch(tariffclass_code) {
					case 16:
					case 18:
					case 19:
					case 20:
					case 21:
					case 22:
					case 25:
					case 26:
					case 27:
					case 28:
					case 96:
					case 99:
					case 103:
					case 104:
						if (!Ext.isEmpty(sex_id) && !sex_id.inlist([3]) ) {
							base_form.findField('Sex_id').clearValue();
						}

						base_form.findField('Sex_id').getStore().clearFilter();
						base_form.findField('Sex_id').lastQuery = '';
						base_form.findField('Sex_id').getStore().filterBy(function(r){
							return r.get('Sex_id').inlist([3]);
						});
						base_form.findField('Sex_id').setValue(3);
						sex_id = 3;
					break;
					
					default:
						if (!Ext.isEmpty(sex_id) && !sex_id.inlist([1,2]) ) {
							base_form.findField('Sex_id').clearValue();
							sex_id = null;
						}

						base_form.findField('Sex_id').getStore().clearFilter();
						base_form.findField('Sex_id').lastQuery = '';
						base_form.findField('Sex_id').getStore().filterBy(function(r){
							return r.get('Sex_id').inlist([1,2]);
						});
					break;
				}
			} else {
				base_form.findField('Sex_id').getStore().clearFilter();
				base_form.findField('Sex_id').lastQuery = '';
			}
		}
		else {
			switch(tariffclass_code) {
				case 22:
				//case 23:
					filterSex = true;
					disptype_id = 1;
				break;
				case 37:
				case 38:
				case 24:
				case 25:
					disptype_id = 2;
				break;
				case 26:
					filterSex = true;
					disptype_id = 3;
				break;
				case 28:
					//filterSex = true;
					disptype_id = 4;
				break;
			}

			if (filterSex) {
				switch(tariffclass_code) {
					case 16:
					case 17:
					//case 28:
						if (!Ext.isEmpty(sex_id) && !sex_id.inlist([3]) ) {
							base_form.findField('Sex_id').clearValue();
						}

						base_form.findField('Sex_id').getStore().clearFilter();
						base_form.findField('Sex_id').lastQuery = '';
						base_form.findField('Sex_id').getStore().filterBy(function(r){
							return r.get('Sex_id').inlist([3]);
						});
						base_form.findField('Sex_id').setValue(3);
						sex_id = 3;
					break;
					
					default:
						if (!Ext.isEmpty(sex_id) && !sex_id.inlist([1,2]) ) {
							base_form.findField('Sex_id').clearValue();
							sex_id = null;
						}

						base_form.findField('Sex_id').getStore().clearFilter();
						base_form.findField('Sex_id').lastQuery = '';
						base_form.findField('Sex_id').getStore().filterBy(function(r){
							return r.get('Sex_id').inlist([1,2]);
						});
					break;
				}
			} else {
				base_form.findField('Sex_id').getStore().clearFilter();
				base_form.findField('Sex_id').lastQuery = '';
			}
		}

		if (!Ext.isEmpty(disptype_id) && !Ext.isEmpty(sex_id)) {
			AgeGroupDisp.getStore().clearFilter();
			AgeGroupDisp.lastQuery = '';
			AgeGroupDisp.getStore().filterBy(function(r){
				return (
					(r.get('Sex_id') == sex_id || Ext.isEmpty(r.get('Sex_id')))
					&& (r.get('DispType_id') == disptype_id)
					&& (
						useAgeGroupDispDateFilter == false
						|| (
							(Ext.isEmpty(r.get('AgeGroupDisp_begDate')) && Ext.isEmpty(r.get('AgeGroupDisp_endDate')))
							|| (
								!Ext.isEmpty(r.get('AgeGroupDisp_begDate')) && !Ext.isEmpty(r.get('AgeGroupDisp_endDate'))
								&& (Ext.isEmpty(begDate) || (r.get('AgeGroupDisp_begDate') <= begDate && r.get('AgeGroupDisp_endDate') >= begDate))
								&& (Ext.isEmpty(endDate) || (r.get('AgeGroupDisp_begDate') <= endDate && r.get('AgeGroupDisp_endDate') >= endDate))
							)
							|| (
								!Ext.isEmpty(r.get('AgeGroupDisp_begDate')) && Ext.isEmpty(r.get('AgeGroupDisp_endDate'))
								&& (Ext.isEmpty(begDate) || r.get('AgeGroupDisp_begDate') <= begDate)
								&& (Ext.isEmpty(endDate) || r.get('AgeGroupDisp_begDate') <= endDate)
							)
							|| (
								Ext.isEmpty(r.get('AgeGroupDisp_begDate')) && !Ext.isEmpty(r.get('AgeGroupDisp_endDate'))
								&& (Ext.isEmpty(begDate) || r.get('AgeGroupDisp_endDate') >= begDate)
								&& (Ext.isEmpty(endDate) || r.get('AgeGroupDisp_endDate') >= endDate)
							)
						)
					)
				);
			});
		}
		
		if (!Ext.isEmpty(agd_id)) {
			var rec = AgeGroupDisp.getStore().getById(agd_id);
			if (rec) {
				AgeGroupDisp.setValue(agd_id);
			} else {
				AgeGroupDisp.clearValue();
			}
		}
	},
	checkVisibleTariffDayOff: function(){
		var base_form = this.findById('TariffDispEditForm').getForm(),
			TariffClass_Code = base_form.findField('TariffClass_id').getFieldValue('TariffClass_Code'),
			Code = TariffClass_Code ? TariffClass_Code : '',
			TariffDisp_TariffDayOffField = base_form.findField('TariffDisp_TariffDayOff'),
			begDT = base_form.findField('TariffDisp_begDT').getValue(),
			// 	15: Дисп-ция взр. населения 1-ый этап
			// 	101: Дисп-ция взр. населения 1-ый этап (мобильная бригада)
			// 	16: Дисп-ция детей-сирот 1-ый этап
			// 	103: Дисп-ция детей-сирот 1-ый этап (мобильная бригада)
			// 	17: Проф. осмотры взрослого населения
			// 	102: Проф. осмотры взрослого населения (мобильная бригада)
			// 	18: Проф. осмотры несовершеннолетних 1-ый этап
			// 	104: Проф. осмотры несовершеннолетних 1-ый этап (мобильная бригада)
			typesVisibleTariffDayOff = [15,16,17,18,101,102,103,104],
			visible = getRegionNick().inlist(['kareliya']) && Code.inlist(typesVisibleTariffDayOff) && begDT >= new Date(2020, 0, 1);
		
		TariffDisp_TariffDayOffField.setContainerVisible(visible);
		TariffDisp_TariffDayOffField.setAllowBlank(!visible);
		
		if(!visible){
			TariffDisp_TariffDayOffField.reset();
		}
		
		return visible;
	},
	show: function(){
		sw.Promed.swTariffDispEditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		
		if ( !arguments[0] ){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		
		this.focus();
		
		var loadMask = new Ext.LoadMask( this.getEl(), { msg: LOAD_WAIT } );
		loadMask.show();

		var base_form = this.findById('TariffDispEditForm').getForm();
		
		base_form.reset();
	
		this.filterTariffClass();
		this.filterAgeGroupDisp();
		this.checkVisibleTariffDayOff();
		this.Lpu_id = arguments[0].Lpu_id || null;
		
		this.TariffDisp_id = arguments[0].TariffDisp_id || null;
		
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		} else {
			this.action = this.TariffDisp_id ? 'edit' : 'add';
		}
		
		base_form.setValues( arguments[0] );
		
		// var LpuSectionProfileFilters = [1017,1018,1019,917,918,919,920,921,922,923,924];
		var LpuSectionProfileFilters = [1017,1018,/*1019,*/917,918,925];

		// Фильтруем значения комбобокса «Профиль»
		var LpuSectionProfile = base_form.findField('LpuSectionProfile_id');
		if (getRegionNick().inlist([ 'astra', 'kareliya', 'pskov' ])) {
			LpuSectionProfile.setAllowBlank(true);
			LpuSectionProfile.hideContainer();
		} else {
			LpuSectionProfile.setAllowBlank(false);
			LpuSectionProfile.showContainer();
			LpuSectionProfile.getStore().clearFilter();
			LpuSectionProfile.lastQuery = '';
			LpuSectionProfile.getStore().filterBy(function(r){
				if ( r.get('LpuSectionProfile_Code').inlist(LpuSectionProfileFilters) ) return true;
				return false;
			});
		}
		this.syncShadow();
		
		switch( this.action ){
			case 'add':
				this.setTitle(lang['tarifyi_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				base_form.clearInvalid();
			break;
			case 'edit':
				this.setTitle(lang['tarifyi_redaktirovanie']);
				this.enableEdit(true);
			break;
			case 'view':
				this.setTitle(lang['tarifyi_prosmotr']);
				this.enableEdit(false);
			break;
		}
		
		if ( this.action != 'add' ){
			base_form.load({
				params: {
					TariffDisp_id: current_window.TariffDisp_id,
					Lpu_id: current_window.Lpu_id
				},
				failure: function(f, o, a){
					loadMask.hide();
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function(){
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function(){
					loadMask.hide();
					current_window.filterTariffClass();
					current_window.filterAgeGroupDisp();
					current_window.checkVisibleTariffDayOff();
				},
				url: '/?c=LpuPassport&m=loadTariffDisp'
			});
		}

		if ( this.action != 'view' ) {
			this.FormPanel.getForm().findField('LpuSectionProfile_id').focus(true, 100);
		} else {
			this.buttons[0].focus();
		}
	},
	initComponent: function(){

		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoWidth: false,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'TariffDispEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			
			items: [{
				xtype: 'hidden',
				name: 'TariffDisp_id',
				id: 'STEW_TariffDisp_id',
				value: 0
			},{
				xtype: 'hidden',
				name: 'Lpu_id',
				id: 'STEW_Lpu_id',
				value: 0
			},{
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				fieldLabel: lang['nachalo_deystviya'],
				name: 'TariffDisp_begDT',
				listeners: {
					'change':function (combo, newValue, oldValue) {
						//win.filterTariffClass();
						win.checkVisibleTariffDayOff();
						win.FormPanel.getForm().findField('TariffDisp_endDT').fireEvent('change', win.FormPanel.getForm().findField('TariffDisp_endDT'), win.FormPanel.getForm().findField('TariffDisp_endDT').getValue());
					}
				},
				allowBlank: false
			},{
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				fieldLabel: lang['okonchanie_deystviya'],
				name: 'TariffDisp_endDT',
				listeners: {
					'change':function (field, newValue, oldValue) {
						//win.filterTariffClass();
						win.filterAgeGroupDisp();
					}
				}
			},{
				xtype: 'swcommonsprcombo',
				fieldLabel: lang['profil'],
				comboSubject: 'LpuSectionProfile',
				hiddenName: 'LpuSectionProfile_id',
				displayField: 'LpuSectionProfile_Name',
				allowBlank: false,
				anchor: '100%',
				filters: [],
				listeners: {
					'change': function() {
						win.filterTariffClass();
					}
				},
				disabledClass: 'field-disabled'
			},{
				xtype: 'swcommonsprcombo',
				fieldLabel: lang['vid_tarifa'],
				comboSubject: 'TariffClass',
				hiddenName: 'TariffClass_id',
				typeCode: 'int',
				displayField: 'TariffClass_Name',
				allowBlank: false,
				moreFields: [
					{ name: 'TariffClass_SysNick', mapping: 'TariffClass_SysNick', type: 'string' },
					{ name: 'Region_id', mapping: 'Region_id', type: 'int' },
					{ name: 'TariffClass_begDT', mapping: 'TariffClass_begDT', type:'date', dateFormat: 'd.m.Y' },
					{ name: 'TariffClass_endDT', mapping: 'TariffClass_endDT', type:'date', dateFormat: 'd.m.Y' }
				],
				anchor: '100%',
				lastQuery: '',
				listWidth: 550,
				listeners: {
					'change': function() {
						win.filterAgeGroupDisp();
						win.checkVisibleTariffDayOff();
					}
				},
				disabledClass: 'field-disabled'
			},{
				xtype: 'swcommonsprcombo',
				fieldLabel: lang['pol'],
				comboSubject: 'Sex',
				hiddenName: 'Sex_id',
				displayField: 'Sex_Name',
				allowBlank: false,
				anchor: '100%',
				listeners: {
					'change': function() {
						win.filterAgeGroupDisp();
					}
				},
				disabledClass: 'field-disabled'
			},{
				xtype: 'swcommonsprcombo',
				fieldLabel: lang['vozrastnaya_gruppa'],
				comboSubject: 'AgeGroupDisp',
				moreFields: [
					{ name: 'Sex_id', mapping: 'Sex_id' }, 
					{ name: 'DispType_id', mapping: 'DispType_id' },
					{ name: 'AgeGroupDisp_begDate', mapping: 'AgeGroupDisp_begDate', type:'date', dateFormat: 'd.m.Y' },
					{ name: 'AgeGroupDisp_endDate', mapping: 'AgeGroupDisp_endDate', type:'date', dateFormat: 'd.m.Y' }
				],
				hiddenName: 'AgeGroupDisp_id',
				displayField: 'AgeGroupDisp_Name',
				allowBlank: false,
				anchor: '100%',
				disabledClass: 'field-disabled'
			},{
				fieldLabel: getRegionNick().inlist(['kareliya']) ? 'Тариф' : 'Значение',
				xtype: 'numberfield',
				allowDecimals: true,
				allowBlank: false,
				anchor: '100%',
				decimalSeparator: ',',
				allowNegative: false,
				name: 'TariffDisp_Tariff'
			},{
				fieldLabel: 'Тариф выходного дня',
				xtype: 'numberfield',
				allowDecimals: true,
				allowBlank: true,
				anchor: '100%',
				decimalSeparator: ',',
				allowNegative: false,
				name: 'TariffDisp_TariffDayOff'
			}],
			reader: new Ext.data.JsonReader({},[
				{ name: 'TariffDisp_id' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'TariffClass_id' },
				{ name: 'AgeGroupDisp_id' },
				{ name: 'Sex_id' },
				{ name: 'TariffDisp_begDT' },
				{ name: 'TariffDisp_endDT' },
				{ name: 'TariffDisp_Tariff' },
				{ name: 'TariffDisp_TariffDayOff' }
			]),
			url: '/?c=LpuPassport&m=saveTariffDisp'
		});
		
		Ext.apply(this,{
			buttons: [
				{
					handler: function(){
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},{
					text: '-'
				},{
					handler: function(){
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL,
					onTabAction: function() {
						if ( !this.FormPanel.getForm().findField('LpuSectionProfile_id').disabled ) {
							this.FormPanel.getForm().findField('LpuSectionProfile_id').focus( true );
						}
					}.createDelegate(this)
				}
			],
			items: [this.FormPanel]
		});
		sw.Promed.swTariffDispEditWindow.superclass.initComponent.apply(this, arguments);
	}
});