/**
 * swMedSvidDeathEditWindow - окно редактирования свидетельства о смерти.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      DLO
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Salakhov Rustam
 * @version      22.04.2010
 * @comment      Префикс для id компонентов MSDEF (MedSvidDeathEditForm)
 *
 */
/*NO PARSE JSON*/
sw.Promed.swMedSvidDeathEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedSvidDeathEditWindow',
	objectSrc: '/jscore/Forms/Admin/swMedSvidDeathEditWindow.js',
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	DeathDiagStore: new Ext.data.JsonStore({
		autoLoad: false,
		url: '/?c=MedSvid&m=getDeathDiagStore',
		fields: [
			{ name: 'DeathDiag_id', type: 'int' },
			{ name: 'Diag_id', type: 'int' },
			{ name: 'DeathDiag_IsLowChance', type: 'int' },
			{ name: 'DeathDiag_IsNotUsed', type: 'int' },
			{ name: 'DeathDiag_IsUsed', type: 'int' },
			{ name: 'DeathDiag_IsDiagIID', type: 'int' },
			{ name: 'DeathDiag_IsDiagTID', type: 'int' },
			{ name: 'DeathDiag_IsDiagMID', type: 'int' },
			{ name: 'DeathDiag_IsDiagEID', type: 'int' },
			{ name: 'DeathDiag_IsDiagOID', type: 'int' },
			{ name: 'Sex_id', type: 'int' },
			{ name: 'DeathDiag_YearFrom', type: 'int' },
			{ name: 'DeathDiag_MonthFrom', type: 'int' },
			{ name: 'DeathDiag_DayFrom', type: 'int' },
			{ name: 'DeathDiag_YearTo', type: 'int' },
			{ name: 'DeathDiag_MonthTo', type: 'int' },
			{ name: 'DeathDiag_DayTo', type: 'int' },		
			{ name: 'DeathDiag_DiagChange', type: 'string' },
			{ name: 'DeathDiag_Message', type: 'string' },
			{ name: 'Region_id', type: 'int' },
			{ name: 'DeathDiag_CriteriaCount', type: 'int' }
		],
		key: 'DeathDiag_id',
		sortInfo: { field: 'DeathDiag_id' },
		tableName: 'DeathDiag'
	}),
	checkSuicide: function () {
		
		if (getRegionNick() != 'perm') return false;
		
		var win = this,
			base_form = this.findById('MedSvidDeathEditForm').getForm()
			PersonRegisterType_List = base_form.findField('Diag_eid').getFieldValue('PersonRegisterType_List'),
			death_date = !!base_form.findField('DeathSvid_DeathDate_Date').getValue() 
				? base_form.findField('DeathSvid_DeathDate_Date').getValue().format('d.m.Y') 
				: null;
			
		if (!PersonRegisterType_List) return false;
		
		if (PersonRegisterType_List.indexOf('suicide') == -1) return false;
		
		Ext.Ajax.request({
			callback: function (opt, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj && response_obj.length == 0) {
						getWnd('swPersonRegisterSuicideEditWindow').show({
							action: 'add',
							fromSvid: true,
							Person_id: base_form.findField('Person_id').getValue(),
							Diag_id: base_form.findField('Diag_eid').getValue(),
							Evn_setDate: death_date
						});
					}
				}
			},
			params: {
				Person_id: base_form.findField('Person_id').getValue(),
				PersonRegisterType_Code: 21,
				PersonRegister_setDate: death_date
			},
			url: '/?c=PersonRegister&m=simpleCheckPersonRegisterExist'
		});
	},
	birthDateToAge: function (b) {
		var x = new Date(), z = new Date(b), b = new Date(b), n = new Date();
		x.setFullYear(n.getFullYear() - b.getFullYear(), n.getMonth() - b.getMonth(), n.getDate() - b.getDate());
		z.setFullYear(b.getFullYear() + x.getFullYear(), b.getMonth() + x.getMonth() + 1);
		if (z.getTime() == n.getTime()) {
			if (x.getMonth() == 11) {
				return [x.getFullYear() + 1, 0, 0];
			} else {
				return [x.getFullYear(), x.getMonth() + 1, 0];
			}
		} else {
			return [x.getFullYear(), x.getMonth(), x.getDate()];
		}
	},
	filterDiagCombo: function () {
		var win = this,
			base_form = this.findById('MedSvidDeathEditForm').getForm(),
			pers_form = this.findById('MSDEF_PersonInformationFrame'),
			dcombo_arr = ['Diag_iid', 'Diag_tid', 'Diag_mid', 'Diag_eid', 'Diag_oid'];
		var person_age = win.birthDateToAge(pers_form.getFieldValue('Person_Birthday'));
		person_age = ("00"+person_age[0]).substr(-3,3) + ("0"+person_age[1]).substr(-2,2) + ("0"+person_age[2]).substr(-2,2);
		for (var i = 0; i < dcombo_arr.length; i++) {
			var diag_combo = base_form.findField(dcombo_arr[i]);
			var curr_combo = null;
			diag_combo.clearValue();
			diag_combo.getStore().removeAll();
			diag_combo.getStore().clearFilter();
			diag_combo.lastQuery = '';
			win.findById(dcombo_arr[i]+'_error').hide();
			// простановка статуса "маловероятный"
			diag_combo.onLoadStore = function() {
				var filter_name;
				switch (this.hiddenName) {
					case 'Diag_iid': filter_name = 'DeathDiag_IsDiagIID'; break;
					case 'Diag_tid': filter_name = 'DeathDiag_IsDiagTID'; break;
					case 'Diag_mid': filter_name = 'DeathDiag_IsDiagMID'; break;
					case 'Diag_eid': filter_name = 'DeathDiag_IsDiagEID'; break;
					case 'Diag_oid': filter_name = 'DeathDiag_IsDiagOID'; break;
				}
				this.getStore().each(function(rec) {
					if(getRegionNick() == 'perm'){
						rec.set('DeathDiag_IsLowChance', null);
						return true;
					}
					var Diag_id = rec.get('Diag_id');
					var isLowChance = false;
					var index = win.DeathDiagStore.findBy(function(r) {
						if (r.get('Diag_id') != Diag_id) {
							return false;
						}
						var ageFrom = ("00"+r.get('DeathDiag_YearFrom')).substr(-3,3) + ("0"+r.get('DeathDiag_MonthFrom')).substr(-2,2) + ("0"+r.get('DeathDiag_DayFrom')).substr(-2,2);
						var ageTo = ("00"+r.get('DeathDiag_YearTo')).substr(-3,3) + ("0"+r.get('DeathDiag_MonthTo')).substr(-2,2) + ("0"+r.get('DeathDiag_DayTo')).substr(-2,2);
						if ( r.get('DeathDiag_IsLowChance') == 2 && r.get(filter_name) == 2 ) {
							if (
								((parseInt(ageFrom) == 0 || ageFrom < person_age) && (parseInt(ageTo) == 0 || ageTo > person_age)) &&
								(Ext.isEmpty(r.get('Sex_id')) || r.get('Sex_id') == pers_form.getFieldValue('Sex_Code'))
							) {
								isLowChance = true;
							}
						}
					});
					if (isLowChance) rec.set('DeathDiag_IsLowChance', 2);
					else rec.set('DeathDiag_IsLowChance', null);
				});
				this.getStore().applySort();
				this.tpl.currentKey = null;
			};
			diag_combo.defaultFilterFn = function(rec) {

				var filter_name;
				switch (this.hiddenName) {
					case 'Diag_iid': filter_name = 'DeathDiag_IsDiagIID'; break;
					case 'Diag_tid': filter_name = 'DeathDiag_IsDiagTID'; break;
					case 'Diag_mid': filter_name = 'DeathDiag_IsDiagMID'; break;
					case 'Diag_eid': filter_name = 'DeathDiag_IsDiagEID'; break;
					case 'Diag_oid': filter_name = 'DeathDiag_IsDiagOID'; break;
				}

				curr_combo = filter_name;
				var Diag_id = rec.attributes ? rec.attributes.Diag_id : rec.get('Diag_id');
				var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
				if (typeof rec != 'object' || Ext.isEmpty(Diag_id)) return false;

				// ищем подходящее правило, руководствуясь:
				// 1) Между "Маловероятными" и "Не используемыми" приоритет выше у "Не используемых"
				// 2) Между Общими (для всех регионов) условиями и региональными приоритет выше у региональных
				// 3) Если пересекаются не используемые общие (для всех регионов) И маловероятные региональные, то приоритет у маловероятных региональных
				// 4) Пол и возраст еще точнее специфицируют контроль в разрезе маловероятных/неиспользуемых и общих/региональных.
				var rec = win.getDeathDiagByStore(filter_name, Diag_id);


				// Запретить выбирать диагноз R54 Старость во всех комбобоксах с причиной смерти (5) для Уфы если на дату 2017 год.Возможно уже не актуально
				if ( Diag_id == 11686 && getRegionNick() == 'ufa' )
				{
					var base_form = Ext.getCmp('MedSvidDeathEditForm').getForm(),
						DeathSvid_GiveDate = base_form.findField('DeathSvid_GiveDate').getValue(),
						Date2018 = new Date(2018,0,1);

					if (DeathSvid_GiveDate < Date2018)
					{
						return false;
					}

				}
				
				if (getRegionNick() === 'ufa' && Diag_Code && Diag_Code.inlist(['U07.1', 'U07.2'])) {
					return win.hasCOVIDVolume;
				}

				// если не нашли запись или есть, что показать пользователю или запись доступна, то отображаем её в комбо
				if (!rec || (rec.get('DeathDiag_DiagChange') && (rec.get('DeathDiag_DiagChange'))!='NULL') || (rec.get('DeathDiag_Message') && (rec.get('DeathDiag_Message'))!='NULL')|| rec.get('DeathDiag_IsNotUsed') != 2) {
					return true;
				} else {
					return false;
				}
			}.createDelegate(diag_combo);
			diag_combo.baseFilterFn = diag_combo.defaultFilterFn;
			diag_combo.getStore().fields.get('Diag_Code').multipleSortInfo = [
				{ field: 'DeathDiag_IsLowChance', direction: 'ASC' },
				{ field: 'Diag_Code', direction: 'ASC' }
			];
			diag_combo.onChange = function (combo, newValue, oldValue) {
				var filter_name;
				switch (combo.hiddenName) {
					case 'Diag_iid': filter_name = 'DeathDiag_IsDiagIID'; break;
					case 'Diag_tid': filter_name = 'DeathDiag_IsDiagTID'; break;
					case 'Diag_mid': filter_name = 'DeathDiag_IsDiagMID'; break;
					case 'Diag_eid': filter_name = 'DeathDiag_IsDiagEID'; break;
					case 'Diag_oid': filter_name = 'DeathDiag_IsDiagOID'; break;
				}
				Ext.getCmp(combo.hiddenName + '_error').hide();
				win.hiddenBlockErrorContainer(false, combo.hiddenName);
				if(combo.hiddenName != 'Diag_oid'){
					win.dopFilterDiagCombo(); // Взаимофильтрации комбиков вынесены отдельно
				}

				var record = combo.getStore().getById(newValue);
				if (!record) {return false;}
				
				var rec = win.getDeathDiagByStore(filter_name, record.get('Diag_id'));
				
				if (rec && rec.get('DeathDiag_IsNotUsed') == 2)  //Запрещенный диагноз
				{
					var msg = 'Данный диагноз не может быть установлен.';
					if (rec.get('DeathDiag_DiagChange')) {
						msg += ' Следует использовать ' + rec.get('DeathDiag_DiagChange');
					}
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						msg: msg,
						title: ERR_INVFIELDS_TIT
					});
					combo.clearValue();
					return false;
				}
				
				if(rec && rec.get('DeathDiag_IsLowChance') == 2)
					record.set('DeathDiag_IsLowChance',2);

				if (record.get('DeathDiag_IsLowChance') == 2) {
					var message = 'Маловероятная причина смерти. ';
					var index = win.DeathDiagStore.findBy(function(r) {
						if (r.get('Diag_id') != record.get('Diag_id')) {
							return false;
						}
						var ageFrom = ("00"+r.get('DeathDiag_YearFrom')).substr(-3,3) + ("0"+r.get('DeathDiag_MonthFrom')).substr(-2,2) + ("0"+r.get('DeathDiag_DayFrom')).substr(-2,2);
						var ageTo = ("00"+r.get('DeathDiag_YearTo')).substr(-3,3) + ("0"+r.get('DeathDiag_MonthTo')).substr(-2,2) + ("0"+r.get('DeathDiag_DayTo')).substr(-2,2);
						if ( r.get('DeathDiag_IsLowChance') == 2 && r.get(filter_name) == 2 ) {
							if (
								((parseInt(ageFrom) == 0 || ageFrom < person_age) && (parseInt(ageTo) == 0 || ageTo > person_age)) &&
								(Ext.isEmpty(r.get('Sex_id')) || r.get('Sex_id') == pers_form.getFieldValue('Sex_Code'))
							) {
								return true;
							}
						}
						return false;
					});
					if (index != -1) {
						if (!Ext.isEmpty(win.DeathDiagStore.getAt(index).get('DeathDiag_Message')) && win.DeathDiagStore.getAt(index).get('DeathDiag_Message')!='NULL') {
							message += win.DeathDiagStore.getAt(index).get('DeathDiag_Message') + ' ';
						};
						if (!Ext.isEmpty(win.DeathDiagStore.getAt(index).get('DeathDiag_DiagChange')) && win.DeathDiagStore.getAt(index).get('DeathDiag_DiagChange')!='NULL') {
							message += 'Следует использовать код ' + win.DeathDiagStore.getAt(index).get('DeathDiag_DiagChange');					
						}
						Ext.getCmp(combo.hiddenName + '_error').getEl().dom.innerHTML = '<div style="color: red; margin-left: 235px; margin-bottom: 10px; text-align: left;">'+message+'</div>';
						Ext.getCmp(combo.hiddenName + '_error').show();
						win.hiddenBlockErrorContainer(true, combo.hiddenName);
					}	
				}
				if (
					getRegionNick() == 'ufa' && 
					combo.hiddenName != 'Diag_eid' && 
					(record.get('Diag_Code').inlist(['A00',  'A01.0',  'A20',  'A22', 'A68', 'A75', 'A82']) || record.get('Diag_Code').substr(0,3).inlist(['B50', 'B51', 'B52', 'B52', 'B54']))
				) {
					Ext.getCmp(combo.hiddenName + '_error').getEl().dom.innerHTML = '<div style="color: red; margin-left: 235px; margin-bottom: 10px; text-align: left;">Внимание! Код особо опасной причины смерти.</div>';
					Ext.getCmp(combo.hiddenName + '_error').show();	
					win.hiddenBlockErrorContainer(true, combo.hiddenName);
				}
			};
			diag_combo.tpl = new Ext.XTemplate(
				'<tpl for=".">',
				'<tpl if="this.shouldShowHeader(values.DeathDiag_IsLowChance)">' +
					'<div style="padding: 12px 4px 4px; font-weight: bold; border-bottom: 1px solid #ddd; color: #3764a0;">{[this.showHeader(values.DeathDiag_IsLowChance)]}</div>' +
				'</tpl>' +
				'' +
					'<tpl if="this.isDiagChange(values.Diag_id) == false">' +
						'<div class="x-combo-list-item">{[this.returnValue(values)]}</div>',
					'</tpl>' +
					'<tpl if="this.isDiagChange(values.Diag_id) != false">' +
						'<div class="x-combo-list-item" style="display:none;"></div>'+ //иначе при выборе получается сдвиг значений
						'<div style="padding: 2px;">{[this.returnValue(values)]}</div>',
						'<div style="padding: 0 4px 5px; color: red;">{[this.isDiagChange(values.Diag_id)]}</div>',
					'</tpl>' +
				'' +
				'</tpl>', {
				shouldShowHeader: function(key) {
					return this.currentKey != key;
				},
				returnValue: function(values){
					var combo = this;
					var str = values.Diag_Name;
					if(combo.searchCodeAndName && str && combo.lastQuery){
						str = str.replace(new RegExp('(' + combo.lastQuery.trim() + ')', 'ig'), '<span style="color:red;font-weight:900">$1</span>');
					}
					return '<table style="border: 0;"><td style="width: 45px;"><font color="red">'+values.Diag_Code+'</font></td><td><h3>'+str+'</h3></td></tr></table>';
				}.createDelegate(diag_combo),
				isDiagChange: function(Diag_id) {
					var person_age = win.birthDateToAge(pers_form.getFieldValue('Person_Birthday'));
					person_age = ("00"+person_age[0]).substr(-3,3) + ("0"+person_age[1]).substr(-2,2) + ("0"+person_age[2]).substr(-2,2);

					var rec = win.getDeathDiagByStore(curr_combo, Diag_id);

					if (rec && rec.get('DeathDiag_DiagChange') && rec.get('DeathDiag_DiagChange')!='NULL' && rec.get('DeathDiag_IsNotUsed') == 2/*!rec.get('DeathDiag_IsLowChance')*/) {
						log(rec);
					//alert(rec.get('DeathDiag_DiagChange'));
					//alert(rec.get('DeathDiag_IsLowChance'));
						return  'Данный диагноз не может быть установлен. Следует использовать ' + rec.get('DeathDiag_DiagChange');
					}
					else if (rec && rec.get('DeathDiag_Message') && rec.get('DeathDiag_Message')!='NULL') {
						
						return  rec.get('DeathDiag_Message');
					} else {
						
						return false;
					}
				},
				showHeader: function(key) {
					this.currentKey = key;
					switch (key) {
						case '2': case 2: return 'Маловероятные диагнозы';
					}
					return '';
				}
			});
		}
	},
	hasCOVIDVolume: false,
	dopFilterDiagCombo: function() {
		var win = this,
			base_form = this.findById('MedSvidDeathEditForm').getForm(),
			dcombo_arr = ['Diag_iid', 'Diag_tid', 'Diag_mid'],
			// Непосредственная причина смерти
			diag_iid_combo = base_form.findField('Diag_iid'),
			diag_iid_code = diag_iid_combo.getFieldValue('Diag_Code'),
			// Патологическое состояние
			diag_tid_combo = base_form.findField('Diag_tid'),
			diag_tid_code = diag_tid_combo.getFieldValue('Diag_Code'),
			// Первоначальная причина смерти
			diag_mid_combo = base_form.findField('Diag_mid'),
			diag_mid_code = diag_mid_combo.getFieldValue('Diag_Code'),
			// Внешние причины
			diag_eid_combo = base_form.findField('Diag_eid'),
			diag_eid_code = diag_eid_combo.getFieldValue('Diag_Code'),
			// Прочие важные состояния
			diag_oid_combo = base_form.findField('Diag_oid'),
			diag_oid_code = diag_oid_combo.getFieldValue('Diag_Code'),
			diag_eid_filter = [],
			is_eid = false,
			is_t36 = false,
			is_t51 = false,
			is_t27 = false,
			is_t71 = false,
			is_t79 = false,
			is_s40 = false, // S40-S69, S80-S99, T08-T16, T18-T70, T73-T74, T75.2-T75.8, T78 
			is_t15 = false,
			is_s40_t78 = false, // S40-S69, S80-S99, T08-T78
			is_s00_t98 = false; //S00-T98
			is_t58_t65 = false; //t58.0, t65.0
		for (var i = 0; i < dcombo_arr.length; i++) {
			var diag_combo = base_form.findField(dcombo_arr[i])
				diag_code = diag_combo.getFieldValue('Diag_Code');
			
			if (Ext.isEmpty(diag_code)) continue;
				
			if (diag_code[0].inlist(['S','T'])) {
				is_eid = true;
			}
			
			if (diag_code.substr(0,3) >= 'T36' && diag_code.substr(0,3) <= 'T50') {
				is_t36 = true;
			}
			
			if (diag_code.substr(0,3) == 'T51') {
				is_t51 = true;
			}
			
			if (
				(diag_code >= 'T27.4' && diag_code <= 'T27.7') ||
				(diag_code >= 'T28.8' && diag_code <= 'T28.9') ||
				(diag_code.substr(0,3) >= 'T52' && diag_code.substr(0,3) <= 'T65')
			) {
				is_t27 = true;
			}
			
			if (diag_code.substr(0,3).inlist(['T71', 'S12'])) {
				is_t71 = true;
			}
			
			if (
				diag_code.substr(0,3) == 'T79' ||
				(diag_code.substr(0,3) >= 'T80' && diag_code.substr(0,3) <= 'T88')
			) {
				is_t79 = true;
			}
			
			if (
				(diag_code.substr(0,3) >= 'S40' && diag_code.substr(0,3) <= 'S69') ||
				(diag_code.substr(0,3) >= 'S80' && diag_code.substr(0,3) <= 'S99') ||
				(diag_code.substr(0,3) >= 'T08' && diag_code.substr(0,3) <= 'T16') ||
				(diag_code.substr(0,3) >= 'T18' && diag_code.substr(0,3) <= 'T70') ||
				(diag_code.substr(0,3) >= 'T73' && diag_code.substr(0,3) <= 'T74') ||
				(diag_code >= 'T75.2' && diag_code <= 'T75.8') ||
				diag_code.substr(0,3) == 'T78'
			) {
				is_s40 = true;
			}
			
			if (diag_code.substr(0,3) >= 'T15' && diag_code.substr(0,3) <= 'T78') {
				is_t15 = true;
			}
			
			if (
				(diag_code.substr(0,3) >= 'S40' && diag_code.substr(0,3) <= 'S69') ||
				(diag_code.substr(0,3) >= 'S80' && diag_code.substr(0,3) <= 'S99') ||
				(diag_code.substr(0,3) >= 'T08' && diag_code.substr(0,3) <= 'T78')
			) {
				is_s40_t78 = true;
			}
			if(diag_code.substr(0,3) >= 'S00' && diag_code.substr(0,3) <= 'T98') {
				if ( getRegionNick() == 'kareliya' && ( dcombo_arr[i] == 'Diag_iid' || dcombo_arr[i] == 'Diag_mid' ) ){
					is_s00_t98 = true;
				}
				if ( getRegionNick() != 'kareliya' ) {
					is_s00_t98 = true;
				}
			}

			if(diag_code == 'T58.0' || diag_code == 'T58' || diag_code == 'T65.0')
				is_t58_t65 = true;
		}
		// if (!is_eid) diag_eid_combo.clearValue();
		diag_eid_combo.setDisabled(false);
		diag_eid_combo.setAllowBlank(!is_eid);
		var covidFilter = function(Diag_Code) {
			return Diag_Code && Diag_Code.inlist(['U07.1', 'U07.2']);
		};
		var covidReturn = getRegionNick() !== 'ufa' || win.hasCOVIDVolume;
		var need_filter = true;

		if (getRegionNick() == 'ekb') {
			// своя фильтрация для Екб
			// поле "Внешняя причина" зависит от причины смерти и первоначальной причины
			if (!Ext.isEmpty(base_form.findField('DeathCause_id').getValue()) && !Ext.isEmpty(diag_mid_code)) {
				switch(base_form.findField('DeathCause_id').getValue()) {
					case 5: // 5. Самоубийство
						if (diag_mid_code.substr(0,3) >= 'T80' && diag_mid_code.substr(0,3) <= 'T88') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return ((Diag_Code.substr(0, 3) >= 'Y40' && Diag_Code.substr(0, 3) <= 'Y84'));
							};
						} else if (
							(diag_mid_code.substr(0,3) >= 'T61' && diag_mid_code.substr(0,3) <= 'T62')
							|| (diag_mid_code.substr(0,3) >= 'T54' && diag_mid_code.substr(0,3) <= 'T57')
							|| (diag_mid_code.substr(0,3) >= 'T64' && diag_mid_code.substr(0,3) <= 'T65')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X69');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T90' && diag_mid_code.substr(0,3) <= 'T98') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 5) == 'Y87.0');
							};
						} else if (
							(diag_mid_code.substr(0,5) >= 'T17.0' && diag_mid_code.substr(0,5) <= 'T17.9')
							|| (diag_mid_code.substr(0,3) >= 'T33' && diag_mid_code.substr(0,3) <= 'T35')
							|| (diag_mid_code.substr(0,3) == 'T68')
							|| (diag_mid_code.substr(0,3) == 'T69')
							|| (diag_mid_code.substr(0,5) == 'T75.0')
							|| (diag_mid_code.substr(0,5) == 'T75.4')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X83');
							};
						} else if (
							(diag_mid_code.substr(0,5) >= 'T20.0' && diag_mid_code.substr(0,5) <= 'T20.3')
							|| (diag_mid_code.substr(0,5) >= 'T21.0' && diag_mid_code.substr(0,5) <= 'T21.3')
							|| (diag_mid_code.substr(0,5) >= 'T22.0' && diag_mid_code.substr(0,5) <= 'T22.3')
							|| (diag_mid_code.substr(0,5) >= 'T23.0' && diag_mid_code.substr(0,5) <= 'T23.3')
							|| (diag_mid_code.substr(0,5) >= 'T24.0' && diag_mid_code.substr(0,5) <= 'T24.3')
							|| (diag_mid_code.substr(0,5) >= 'T25.0' && diag_mid_code.substr(0,5) <= 'T25.3')
							|| (diag_mid_code.substr(0,5) >= 'T26.0' && diag_mid_code.substr(0,5) <= 'T26.3')
							|| (diag_mid_code.substr(0,5) >= 'T27.0' && diag_mid_code.substr(0,5) <= 'T27.3')
							|| (diag_mid_code.substr(0,5) >= 'T28.0' && diag_mid_code.substr(0,5) <= 'T28.3')
							|| (diag_mid_code.substr(0,5) >= 'T29.0' && diag_mid_code.substr(0,5) <= 'T29.3')
							|| (diag_mid_code.substr(0,5) >= 'T30.0' && diag_mid_code.substr(0,5) <= 'T30.3')
							|| (diag_mid_code.substr(0,3) == 'T31')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return ((Diag_Code.substr(0, 3) >= 'X76' && Diag_Code.substr(0, 3) <= 'X77'));
							};
						} else if (diag_mid_code.substr(0,3) == 'T39') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X60');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T42' && diag_mid_code.substr(0,3) <= 'T43') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X61');
							};
						} else if (diag_mid_code.substr(0,3) == 'T40') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X62');
							};
						} else if (diag_mid_code.substr(0,3) == 'T44') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X63');
							};
						} else if (
							(diag_mid_code.substr(0,3) >= 'T36' && diag_mid_code.substr(0,3) <= 'T38')
							|| (diag_mid_code.substr(0,3) == 'T41')
							|| (diag_mid_code.substr(0,3) >= 'T45' && diag_mid_code.substr(0,3) <= 'T50')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X64');
							};
						} else if (diag_mid_code.substr(0,3) == 'T51') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X65');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T52' && diag_mid_code.substr(0,3) <= 'T53') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X66');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T58' && diag_mid_code.substr(0,3) <= 'T59') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X67');
							};
						} else if (diag_mid_code.substr(0,3) == 'T60') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X68');
							};
						} else if (diag_mid_code.substr(0,3) == 'T71') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X70');
							};
						} else if (diag_mid_code.substr(0,5) == 'T75.1') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X71');
							};
						}
						break;
					case 4: // 4. Убийство
						if (diag_mid_code.substr(0,3) >= 'T61' && diag_mid_code.substr(0,3) <= 'T62') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X90');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T90' && diag_mid_code.substr(0,3) <= 'T98') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 5) == 'Y87.1');
							};
						} else if (
							(diag_mid_code.substr(0,5) >= 'T20.0' && diag_mid_code.substr(0,5) <= 'T20.3')
							|| (diag_mid_code.substr(0,5) >= 'T21.0' && diag_mid_code.substr(0,5) <= 'T21.3')
							|| (diag_mid_code.substr(0,5) >= 'T22.0' && diag_mid_code.substr(0,5) <= 'T22.3')
							|| (diag_mid_code.substr(0,5) >= 'T23.0' && diag_mid_code.substr(0,5) <= 'T23.3')
							|| (diag_mid_code.substr(0,5) >= 'T24.0' && diag_mid_code.substr(0,5) <= 'T24.3')
							|| (diag_mid_code.substr(0,5) >= 'T25.0' && diag_mid_code.substr(0,5) <= 'T25.3')
							|| (diag_mid_code.substr(0,5) >= 'T26.0' && diag_mid_code.substr(0,5) <= 'T26.3')
							|| (diag_mid_code.substr(0,5) >= 'T27.0' && diag_mid_code.substr(0,5) <= 'T27.3')
							|| (diag_mid_code.substr(0,5) >= 'T28.0' && diag_mid_code.substr(0,5) <= 'T28.3')
							|| (diag_mid_code.substr(0,5) >= 'T29.0' && diag_mid_code.substr(0,5) <= 'T29.3')
							|| (diag_mid_code.substr(0,5) >= 'T30.0' && diag_mid_code.substr(0,5) <= 'T30.3')
							|| (diag_mid_code.substr(0,3) == 'T31')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return ((Diag_Code.substr(0, 3) >= 'X97' && Diag_Code.substr(0, 3) <= 'X98'));
							};
						} else if (
							(diag_mid_code.substr(0,3) >= 'T33' && diag_mid_code.substr(0,3) <= 'T35')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y08');
							};
						} else if (
							(diag_mid_code.substr(0,3) >= 'T36' && diag_mid_code.substr(0,3) <= 'T50')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X85');
							};
						} else if (diag_mid_code.substr(0,3) == 'T54') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X86');
							};
						} else if (diag_mid_code.substr(0,3) == 'T60') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X87');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T58' && diag_mid_code.substr(0,3) <= 'T59') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X88');
							};
						} else if (
							(diag_mid_code.substr(0,3) >= 'T51' && diag_mid_code.substr(0,3) <= 'T53')
							|| (diag_mid_code.substr(0,3) >= 'T55' && diag_mid_code.substr(0,3) <= 'T57')
							|| (diag_mid_code.substr(0,3) == 'T64')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X89');
							};
						} else if (diag_mid_code.substr(0,3) == 'T65') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (
									Diag_Code.substr(0, 3) == 'X89'
									|| Diag_Code.substr(0, 3) == 'X90'
								);
							};
						} else if (diag_mid_code.substr(0,3) == 'T71') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X91');
							};
						} else if (diag_mid_code.substr(0,5) == 'T75.1') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X92');
							};
						}
						break;
					case 6: // 8. Род смерти не установлен
						if (diag_mid_code.substr(0,3) >= 'T61' && diag_mid_code.substr(0,3) <= 'T62') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y19');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T90' && diag_mid_code.substr(0,3) <= 'T98') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 5) == 'Y87.2');
							};
						} else if (diag_mid_code.substr(0,3) == 'T39') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y10');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T42' && diag_mid_code.substr(0,3) <= 'T43') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y11');
							};
						} else if (diag_mid_code.substr(0,3) == 'T40') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y12');
							};
						} else if (diag_mid_code.substr(0,3) == 'T44') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y13');
							};
						} else if (
							(diag_mid_code.substr(0,3) >= 'T36' && diag_mid_code.substr(0,3) <= 'T38')
							|| (diag_mid_code.substr(0,3) == 'T41')
							|| (diag_mid_code.substr(0,3) >= 'T45' && diag_mid_code.substr(0,3) <= 'T50')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y14');
							};
						} else if (diag_mid_code.substr(0,3) == 'T51') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y15');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T52' && diag_mid_code.substr(0,3) <= 'T53') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y16');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T58' && diag_mid_code.substr(0,3) <= 'T59') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y17');
							};
						} else if (diag_mid_code.substr(0,3) == 'T60') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y18');
							};
						} else if (
							(diag_mid_code.substr(0,3) >= 'T54' && diag_mid_code.substr(0,3) <= 'T57')
							|| (diag_mid_code.substr(0,3) >= 'T64' && diag_mid_code.substr(0,3) <= 'T65')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y19');
							};
						} else if (diag_mid_code.substr(0,3) == 'T71') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y20');
							};
						} else if (diag_mid_code.substr(0,5) == 'T75.1') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y21');
							};
						} else if (
							(diag_mid_code.substr(0,5) >= 'T20.0' && diag_mid_code.substr(0,5) <= 'T20.3')
							|| (diag_mid_code.substr(0,5) >= 'T21.0' && diag_mid_code.substr(0,5) <= 'T21.3')
							|| (diag_mid_code.substr(0,5) >= 'T22.0' && diag_mid_code.substr(0,5) <= 'T22.3')
							|| (diag_mid_code.substr(0,5) >= 'T23.0' && diag_mid_code.substr(0,5) <= 'T23.3')
							|| (diag_mid_code.substr(0,5) >= 'T24.0' && diag_mid_code.substr(0,5) <= 'T24.3')
							|| (diag_mid_code.substr(0,5) >= 'T25.0' && diag_mid_code.substr(0,5) <= 'T25.3')
							|| (diag_mid_code.substr(0,5) >= 'T26.0' && diag_mid_code.substr(0,5) <= 'T26.3')
							|| (diag_mid_code.substr(0,5) >= 'T27.0' && diag_mid_code.substr(0,5) <= 'T27.3')
							|| (diag_mid_code.substr(0,5) >= 'T28.0' && diag_mid_code.substr(0,5) <= 'T28.3')
							|| (diag_mid_code.substr(0,5) >= 'T29.0' && diag_mid_code.substr(0,5) <= 'T29.3')
							|| (diag_mid_code.substr(0,5) >= 'T30.0' && diag_mid_code.substr(0,5) <= 'T30.3')
							|| (diag_mid_code.substr(0,3) == 'T31')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return ((Diag_Code.substr(0, 3) >= 'Y26' && Diag_Code.substr(0, 3) <= 'Y27'));
							};
						} else if (
							(diag_mid_code.substr(0,3) >= 'T33' && diag_mid_code.substr(0,3) <= 'T35')
							|| (diag_mid_code.substr(0,5) >= 'T17.0' && diag_mid_code.substr(0,5) <= 'T17.9')
							|| (diag_mid_code.substr(0,3) == 'T69')
							|| (diag_mid_code.substr(0,5) == 'T75.0')
							|| (diag_mid_code.substr(0,5) == 'T75.4')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'Y33');
							};
						}
						break;
					case 2: // 2. Несчастный случай, не связанный с производством
					case 3: // 3. Несчастный случай, связанный с производством
						if (diag_mid_code.substr(0,5) >= 'T17.0' && diag_mid_code.substr(0,5) <= 'T17.9') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (
									(Diag_Code.substr(0, 3) >= 'W77' && Diag_Code.substr(0, 3) <= 'W80')
									|| (Diag_Code.substr(0, 3) == 'W84')
								);
							};
						} else if (
							(diag_mid_code.substr(0,5) >= 'T20.0' && diag_mid_code.substr(0,5) <= 'T20.3')
							|| (diag_mid_code.substr(0,5) >= 'T21.0' && diag_mid_code.substr(0,5) <= 'T21.3')
							|| (diag_mid_code.substr(0,5) >= 'T22.0' && diag_mid_code.substr(0,5) <= 'T22.3')
							|| (diag_mid_code.substr(0,5) >= 'T23.0' && diag_mid_code.substr(0,5) <= 'T23.3')
							|| (diag_mid_code.substr(0,5) >= 'T24.0' && diag_mid_code.substr(0,5) <= 'T24.3')
							|| (diag_mid_code.substr(0,5) >= 'T25.0' && diag_mid_code.substr(0,5) <= 'T25.3')
							|| (diag_mid_code.substr(0,5) >= 'T26.0' && diag_mid_code.substr(0,5) <= 'T26.3')
							|| (diag_mid_code.substr(0,5) >= 'T27.0' && diag_mid_code.substr(0,5) <= 'T27.3')
							|| (diag_mid_code.substr(0,5) >= 'T28.0' && diag_mid_code.substr(0,5) <= 'T28.3')
							|| (diag_mid_code.substr(0,5) >= 'T29.0' && diag_mid_code.substr(0,5) <= 'T29.3')
							|| (diag_mid_code.substr(0,5) >= 'T30.0' && diag_mid_code.substr(0,5) <= 'T30.3')
							|| (diag_mid_code.substr(0,3) == 'T31')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (
									(Diag_Code.substr(0, 3) >= 'W35' && Diag_Code.substr(0, 3) <= 'W40')
									|| (Diag_Code.substr(0, 3) >= 'W85' && Diag_Code.substr(0, 3) <= 'W92')
									|| (Diag_Code.substr(0, 3) >= 'X00' && Diag_Code.substr(0, 3) <= 'X19')
									|| (Diag_Code.substr(0, 3) == 'X30')
									|| (Diag_Code.substr(0, 3) >= 'X32' && Diag_Code.substr(0, 3) <= 'X33')
								);
							};
						} else if (diag_mid_code.substr(0,3) >= 'T33' && diag_mid_code.substr(0,3) <= 'T35') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (
									(Diag_Code.substr(0, 3) == 'W93')
									|| (Diag_Code.substr(0, 3) == 'X31')
								);
							};
						} else if (diag_mid_code.substr(0,3) == 'T39') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X40');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T42' && diag_mid_code.substr(0,3) <= 'T43') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X41');
							};
						} else if (diag_mid_code.substr(0,3) == 'T40') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X42');
							};
						} else if (diag_mid_code.substr(0,3) == 'T44') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X43');
							};
						} else if (
							(diag_mid_code.substr(0,3) >= 'T36' && diag_mid_code.substr(0,3) <= 'T38')
							|| (diag_mid_code.substr(0,3) == 'T41')
							|| (diag_mid_code.substr(0,3) >= 'T45' && diag_mid_code.substr(0,3) <= 'T50')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X44');
							};
						} else if (diag_mid_code.substr(0,3) == 'T51') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X45');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T52' && diag_mid_code.substr(0,3) <= 'T53') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X46');
							};
						} else if (diag_mid_code.substr(0,3) >= 'T58' && diag_mid_code.substr(0,3) <= 'T59') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X47');
							};
						} else if (diag_mid_code.substr(0,3) == 'T60') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X48');
							};
						} else if (diag_mid_code.substr(0,3) == 'T63') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (
									(Diag_Code.substr(0, 3) >= 'X20' && Diag_Code.substr(0, 3) <= 'X27')
									|| (Diag_Code.substr(0, 3) == 'X29')
								);
							};
						} else if (
							(diag_mid_code.substr(0,3) >= 'T61' && diag_mid_code.substr(0,3) <= 'T62')
						) {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (
									(Diag_Code.substr(0, 3) == 'X49')
									|| (Diag_Code.substr(0, 3) == 'X26')
									|| (Diag_Code.substr(0, 3) >= 'X28' && Diag_Code.substr(0, 3) <= 'X29')
									|| (Diag_Code.substr(0, 3) == 'W60')
								);
							};
						} else if (diag_mid_code.substr(0,3) >= 'T90' && diag_mid_code.substr(0,3) <= 'T98') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) >= 'Y85' && Diag_Code.substr(0, 3) <= 'Y86');
							};
						} else if (diag_mid_code.substr(0,3) == 'T66') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (
									(Diag_Code.substr(0, 3) == 'W88')
									|| (Diag_Code.substr(0, 3) == 'W91')
								);
							};
						} else if (diag_mid_code.substr(0,3) >= 'T68' && diag_mid_code.substr(0,3) <= 'T69') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (
									(Diag_Code.substr(0, 3) == 'W93')
									|| (Diag_Code.substr(0, 3) == 'X31')
								);
							};
						} else if (diag_mid_code.substr(0,3) == 'T71') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (
									(Diag_Code.substr(0, 3) == 'W23')
									|| (Diag_Code.substr(0, 3) >= 'W75' && Diag_Code.substr(0, 3) <= 'W76')
									|| (Diag_Code.substr(0, 3) >= 'W81' && Diag_Code.substr(0, 3) <= 'W84')
									|| (Diag_Code.substr(0, 3) >= 'V00' && Diag_Code.substr(0, 3) <= 'V99')
								);
							};
						} else if (diag_mid_code.substr(0,5) == 'T75.0') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (Diag_Code.substr(0, 3) == 'X33');
							};
						} else if (diag_mid_code.substr(0,5) == 'T75.1') {
							diag_eid_combo.baseFilterFn = function (rec) {
								var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
								if (covidFilter(Diag_Code)) return covidReturn;
								return (
									(Diag_Code.substr(0, 3) >= 'W65' && Diag_Code.substr(0, 3) <= 'W74')
									|| (Diag_Code.substr(0, 3) >= 'X37' && Diag_Code.substr(0, 3) <= 'X39')
								);
							};
						}
						break;
					default:
						diag_eid_combo.baseFilterFn = diag_eid_combo.defaultFilterFn;
						break;
				}
			} else {
				diag_eid_combo.baseFilterFn = diag_eid_combo.defaultFilterFn;
			}
			diag_eid_combo.lastQuery = "";

			return true;
		}

		if (is_t36 && !getRegionNick().inlist(['perm','penza'])) {
			diag_eid_combo.baseFilterFn = function(rec) {
				var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
				if (covidFilter(Diag_Code)) return covidReturn;
				return (
					(Diag_Code.substr(0,3) >= 'X40' && Diag_Code.substr(0,3) <= 'X44') ||
					(Diag_Code.substr(0,3) >= 'X60' && Diag_Code.substr(0,3) <= 'X64') ||
					Diag_Code.substr(0,3).inlist(['X85', 'Y10']) ||
					(Diag_Code.substr(0,3) >= 'Y11' && Diag_Code.substr(0,3) <= 'Y14')
				);
			};
		} else if (is_t51 && !getRegionNick().inlist(['perm','penza'])) {
			diag_eid_combo.baseFilterFn = function(rec) {
				var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
				if (covidFilter(Diag_Code)) return covidReturn;
				return (Diag_Code.substr(0,3).inlist(['X45', 'X65', 'Y15']));
			};
		} else if ((is_t27 || (is_t58_t65 && getRegionNick().inlist(['vologda','kareliya']))) && !getRegionNick().inlist(['perm','penza'])) {
			diag_eid_combo.baseFilterFn = function(rec) {
				var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
				if (covidFilter(Diag_Code)) return covidReturn;
				// https://redmine.swan.perm.ru/issues/92963
				// Добавлены X00 для Карелии
				if( is_t58_t65 ) {
					return (
						(Diag_Code.substr(0,3) >= 'X00' && Diag_Code.substr(0,3) <= 'X09') ||
						(Diag_Code.substr(0,3) == 'X97') ||
						(Diag_Code.substr(0,3) == 'Y26') ||
						(Diag_Code.substr(0,3) >= 'V00' && Diag_Code.substr(0,3) <= 'V99')
					);
				}else{
					return ( 
						(Diag_Code.substr(0,3) == 'X00' && getRegionNick().inlist(['vologda','kareliya'])) ||
						(Diag_Code.substr(0,3) >= 'X46' && Diag_Code.substr(0,3) <= 'X49') ||
						(Diag_Code.substr(0,3) >= 'X66' && Diag_Code.substr(0,3) <= 'X69') ||
						(Diag_Code.substr(0,3) >= 'X86' && Diag_Code.substr(0,3) <= 'X90') ||
						(Diag_Code.substr(0,3) >= 'Y16' && Diag_Code.substr(0,3) <= 'Y19')
					);
				}
			};
		} else if (is_t71 && getRegionNick() == 'ufa') {
			diag_eid_combo.baseFilterFn = function(rec) {
				var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
				if (covidFilter(Diag_Code)) return covidReturn;
				return (Diag_Code.substr(0,3).inlist(['X70', 'X91', 'Y20', 'W75', 'W76', 'W77', 'W78', 'W79', 'W80', 'W81', 'W82', 'W83', 'W84']));
			};
		} else if (is_t79 && getRegionNick() == 'ufa') {
			diag_eid_combo.baseFilterFn = function(rec) {
				var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
				if (covidFilter(Diag_Code)) return covidReturn;
				return (Diag_Code.substr(0,3) >= 'V00' && Diag_Code.substr(0,3) <= 'Y98');
			};
		} else if (is_s00_t98 && getRegionNick().inlist(['vologda','kareliya'])) {
			diag_eid_combo.baseFilterFn = function(rec) {
				var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
				return(Diag_Code.substr(0,3) >= 'V01' && Diag_Code.substr(0,3) <= 'Y98');
			};
		// https://jira.is-mis.ru/browse/PROMEDWEB-7902
		} else if (is_s00_t98 && getRegionNick().inlist(['perm'])) {
			diag_eid_combo.baseFilterFn = function(rec) {
				var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
				return(Diag_Code.substr(0,3) >= 'V01' && Diag_Code.substr(0,3) <= 'Y98');
			};
		} else {
			diag_eid_combo.baseFilterFn = diag_eid_combo.defaultFilterFn;
			need_filter = false;
		}

		if (is_s00_t98 && getRegionNick() == 'ufa') {
			//need_filter = true;
			need_filter = false;
			diag_eid_combo.baseFilterFn = function(rec) {
				var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
				if (covidFilter(Diag_Code)) return covidReturn;
				return(Diag_Code.substr(0,3) >= 'V01' && Diag_Code.substr(0,3) <= 'Y98');
			};
		}

		diag_eid_combo.setBaseFilter(diag_eid_combo.baseFilterFn);
		if (!Ext.isEmpty(diag_eid_code)) {
			if (!diag_eid_combo.getStore().getCount()) {
				diag_eid_combo.clearValue();
			}
		}

		if (need_filter) {
			win.getLoadMask('Загрузка диагнозов').show();
			diag_eid_combo.getStore().load({
				callback: function() {
					win.getLoadMask().hide();
					diag_eid_combo.setBaseFilter(diag_eid_combo.baseFilterFn);
					diag_eid_combo.getStore().findBy(function(rec) {
						diag_eid_filter.push(rec.get('Diag_id'));
					});
					diag_eid_combo.filterDiag = diag_eid_filter;
					diag_eid_combo.getStore().removeAll();
					if( !diag_eid_combo.getValue().inlist(diag_eid_combo.filterDiag) ){
						diag_eid_combo.clearValue();
					}
				},
				params: { where: "where Diag_Code like 'V%' or Diag_Code like 'X%' or Diag_Code like 'Y%'" }
			});
		} else {
			diag_eid_combo.filterDiag = false;
		}
		if (
			getRegionNick() != 'perm' &&
			!Ext.isEmpty(diag_eid_code) && (
			(is_s40 && ((diag_eid_code.substr(0,3) >= 'W65' && diag_eid_code.substr(0,3) <= 'W74') || diag_eid_code.substr(0,3).inlist(['X71', 'X92', 'Y21']))) ||
			(is_t15 && ((diag_eid_code.substr(0,3) >= 'X72' && diag_eid_code.substr(0,3) <= 'X74') || diag_eid_code.substr(0,3) == 'X78')) ||
			(is_s40_t78 && (
				(diag_eid_code.substr(0,3) >= 'W00' && diag_eid_code.substr(0,3) <= 'W19') || 
				(diag_eid_code.substr(0,3) >= 'X80' && diag_eid_code.substr(0,3) <= 'X82') || 
				(diag_eid_code.substr(0,3) >= 'X93' && diag_eid_code.substr(0,3) <= 'X95') || 
				diag_eid_code.substr(0,3) == 'X99' || 
				(diag_eid_code.substr(0,3) >= 'Y00' && diag_eid_code.substr(0,3) <= 'Y04') || 
				(diag_eid_code.substr(0,3) >= 'Y22' && diag_eid_code.substr(0,3) <= 'Y24') || 
				(diag_eid_code.substr(0,3) >= 'Y29' && diag_eid_code.substr(0,3) <= 'Y30') || 
				diag_eid_code.substr(0,3) == 'Y32'
			))
		)) {
			Ext.getCmp('Diag_eid_error').getEl().dom.innerHTML = '<div style="color: red; margin-left: 235px; margin-bottom: 10px; text-align: left;">Маловероятная причина смерти.</div>';
			Ext.getCmp('Diag_eid_error').show();
		}
	},
	defaultDeathAddress: {Person_id: null, DAddress_id: null},
	generateNewNumber: function(onlySer) {
		var win = this;

		if (win.isViewMode()) {
			// генерировать новый номер надо только при добавлении
			return false;
		}

		var base_form = this.findById('MedSvidDeathEditForm').getForm();

		if (base_form.findField('ReceptType_id').getValue() != 2) {
			onlySer = true;
		}

		var LpuSection_id = this.findById('MSDEF_LpuSectionCombo').getValue();
		var params = {
			svid_type: 'death'
		};

		if (win.needLpuSectionForNumGeneration) {
			params.LpuSection_id = LpuSection_id;
		}

		if (!onlySer) {
			params.generateNew = 1;
		}

		if (getRegionNick() == 'ufa' && onlySer && base_form.findField('ReceptType_id').getValue() == 1) {
			params.ReceptType_id = 1;
		}

		// дата выдачи
		if (!Ext.isEmpty(base_form.findField('DeathSvid_GiveDate').getValue())) {
			params.onDate = base_form.findField('DeathSvid_GiveDate').getValue().format('d.m.Y');
		}

		win.findById(win.id + 'gennewnumber').disable();
		if (base_form.findField('ReceptType_id').getValue() == 2 && (!Ext.isEmpty(LpuSection_id) || win.needLpuSectionForNumGeneration == false)) {
			win.findById(win.id + 'gennewnumber').enable();
		}

		if (Ext.isEmpty(LpuSection_id) && win.needLpuSectionForNumGeneration) {
			// не определяем нумератор, если не задано отделение
			return false;
		}

		// значиемые параметры, от изменения которых зависит нужно ли вызывать заного загрузку
		var xparams = {
			svid_type: params.svid_type,
			onDate: params.onDate?params.onDate:null,
			LpuSection_id: params.LpuSection_id?params.LpuSection_id:null,
			ReceptType_id: base_form.findField('ReceptType_id').getValue()
		};
		var newParamsForNumGeneration = Ext.util.JSON.encode(xparams);
		if (onlySer && win.lastParamsForNumGeneration == newParamsForNumGeneration) {
			// ничего не грузим если параметры не изменились
			return false;
		}
		win.lastParamsForNumGeneration = newParamsForNumGeneration;

		if (getRegionNick() == 'kareliya' && base_form.findField('ReceptType_id').getValue() == 1) {
			// для Карелии в режиме на бланке серию подгружать не надо
			return false;
		}

		win.getLoadMask(lang['poluchenie_serii_nomera_svidetelstva']).show();
		Ext.Ajax.request({ //заполнение номера и серии
			callback: function (options, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj && response_obj.Error_Code && response_obj.Error_Code == 'numerator404') {
						if (getRegionNick() == 'ufa') {
							sw.swMsg.alert(lang['oshibka'], lang['ne_zadan_aktivnyiy_numerator_dlya_svidetelstvo_o_smerti_obratites_k_administratoru_sistemyi']);
							win.findById(win.id + 'gennewnumber').disable();
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['ne_zadan_aktivnyiy_numerator_dlya_svidetelstvo_o_smerti_vvod_svidetelstv_vozmojen_v_rejime_1_na_blanke']);
							base_form.findField('ReceptType_id').setValue(1);
							base_form.findField('ReceptType_id').disable();
							base_form.findField('DeathSvid_Ser').setValue('');
							base_form.findField('DeathSvid_Num').setValue('');
							base_form.findField('DeathSvid_Num').enable();
							base_form.findField('DeathSvid_Ser').enable();
							win.findById(win.id + 'gennewnumber').disable();
						}
					} else {
						base_form.findField('DeathSvid_Ser').setValue('');
						base_form.findField('DeathSvid_Num').setValue('');
						base_form.findField('DeathSvid_Ser').setValue(response_obj.ser);
						if (!onlySer) {
							base_form.findField('DeathSvid_Num').setValue(response_obj.num);
						}
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_generatsii_serii_i_nomera_svidetelstva_proizoshla_oshibka']);
				}
			},
			params: params,
			url: '/?c=MedSvid&m=getMedSvidSerNum'
		});
	},
	checkPrimDiag: function(options) {
		if(this.checkPrimDiagHold){
			return false;
		}
		this.checkPrimDiagHold = true;
		var base_form = this.findById('MedSvidDeathEditForm').getForm();
		var fields = ['DeathSvid_IsPrimDiagIID','DeathSvid_IsPrimDiagMID','DeathSvid_IsPrimDiagTID','DeathSvid_IsPrimDiagEID'];
		for(var i=0;i<fields.length;i++){
			if(options && options.field == fields[i]){
				if(base_form.findField(fields[i]).getValue() == true && !options.auto){
					base_form.findField(fields[i]).setValue(false);
				} else {
					base_form.findField(fields[i]).setValue(true);
				}
			} else {
				base_form.findField(fields[i]).setValue('');
			}
			if(i == (fields.length - 1)){
				this.checkPrimDiagHold = false;
			}
		}
	},
	setPrimDiag: function() {
		var diag_iid_combo = Ext.getCmp('MSDEF_Diag_iid_Combo').getValue();
		var diag_iid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagIID');
		var diag_tid_combo = Ext.getCmp('MSDEF_Diag_tid_Combo').getValue();
		var diag_tid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagTID');
		var diag_mid_combo = Ext.getCmp('MSDEF_Diag_mid_Combo').getValue();
		var diag_mid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagMID');
		var diag_eid_combo = Ext.getCmp('MSDEF_Diag_eid_Combo').getValue();
		var diag_eid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagEID');

		if(getRegionNick() == 'kareliya') {
			if(diag_eid_combo){
				this.checkPrimDiag({field:'DeathSvid_IsPrimDiagEID',auto:true})
			} else if(diag_mid_combo){
				this.checkPrimDiag({field:'DeathSvid_IsPrimDiagMID',auto:true})
			} else if(diag_tid_combo){
				this.checkPrimDiag({field:'DeathSvid_IsPrimDiagTID',auto:true})
			} else if(diag_iid_combo){
				this.checkPrimDiag({field:'DeathSvid_IsPrimDiagIID',auto:true})
			} else {
				this.checkPrimDiag();
			}
		} else {
			if(diag_mid_combo || (diag_iid_combo && diag_tid_combo && diag_mid_combo) || (!diag_iid_combo && !diag_tid_combo && diag_mid_combo && !diag_eid_combo)){
				this.checkPrimDiag({field:'DeathSvid_IsPrimDiagMID',auto:true})
			} else if((!diag_mid_combo && diag_tid_combo) || (diag_iid_combo && diag_tid_combo) || (!diag_iid_combo && diag_tid_combo && !diag_mid_combo && !diag_eid_combo)){
				this.checkPrimDiag({field:'DeathSvid_IsPrimDiagTID',auto:true})
			} else if((diag_iid_combo && !diag_tid_combo && !diag_mid_combo && !diag_eid_combo) || (diag_iid_combo && !diag_tid_combo && !diag_mid_combo && diag_eid_combo)){
				this.checkPrimDiag({field:'DeathSvid_IsPrimDiagIID',auto:true})
			} else {
				this.checkPrimDiag();
			}
		}
	},
	doSave: function (options) {
		if (this.formStatus == 'save' || this.action == 'view') return false;
		this.formStatus = 'save';
		var win = this;
		var base_form = this.findById('MedSvidDeathEditForm').getForm();
		var person_frame = this.findById('MSDEF_PersonInformationFrame');
		var params = new Object();
		
		var dcombo_arr = ['Diag_iid', 'Diag_tid', 'Diag_mid', 'Diag_eid'],
			is_aDiag = false;
			is_R54Diag = false;
			is_R95Diag = false;
			var covidFilter = function(Diag_Code) {
				return Diag_Code && Diag_Code.inlist(['U07.1', 'U07.2']);
			};
		for (var i = 0; i < dcombo_arr.length; i++) {
			var diag_combo = base_form.findField(dcombo_arr[i]);
			if (
				!Ext.isEmpty(diag_combo.getFieldValue('Diag_Code')) && 
				diag_combo.getFieldValue('Diag_Code').substr(0,3) >= 'A00' && 
				diag_combo.getFieldValue('Diag_Code').substr(0,3) <= 'R99'
			) {
				is_aDiag = true;
			}
			if (
				!Ext.isEmpty(diag_combo.getFieldValue('Diag_Code')) && 
				diag_combo.getFieldValue('Diag_Code').substr(0,4) == 'R54.'
			) {
				is_R54Diag = true;
			}
			if (
				!Ext.isEmpty(diag_combo.getFieldValue('Diag_Code')) && 
				(diag_combo.getFieldValue('Diag_Code').substr(0,5) == 'R95.0' || diag_combo.getFieldValue('Diag_Code').substr(0,5) == 'R95.9')
			) {
				is_R95Diag = true;
			}
		}

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
					this.findById('MedSvidDeathEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (getRegionNick() == 'ekb') {
			// Коды T71 и T17.0-T19.0 не сочетаются. Если в строках для причин а-в (непосредственная, паталогическое состояние, первоначальная) стоит код T71.X
			// а в любой другой T17.0-T19.0, то при сохранении выводить ошибку «Коды T71 и T17.0-T19.0 не сочетаются. Необходимо выбрать только один код».
			if (
				(
					(base_form.findField('Diag_iid').getFieldValue('Diag_Code') && base_form.findField('Diag_iid').getFieldValue('Diag_Code').substr(0,3) == 'T71')
					|| (base_form.findField('Diag_tid').getFieldValue('Diag_Code') && base_form.findField('Diag_tid').getFieldValue('Diag_Code').substr(0,3) == 'T71')
					|| (base_form.findField('Diag_mid').getFieldValue('Diag_Code') && base_form.findField('Diag_mid').getFieldValue('Diag_Code').substr(0,3) == 'T71')
				) && (
					(base_form.findField('Diag_iid').getFieldValue('Diag_Code') && base_form.findField('Diag_iid').getFieldValue('Diag_Code').substr(0,3).inlist(['T17','T18','T19']))
					|| (base_form.findField('Diag_tid').getFieldValue('Diag_Code') && base_form.findField('Diag_tid').getFieldValue('Diag_Code').substr(0,3).inlist(['T17','T18','T19']))
					|| (base_form.findField('Diag_mid').getFieldValue('Diag_Code') && base_form.findField('Diag_mid').getFieldValue('Diag_Code').substr(0,3).inlist(['T17','T18','T19']))
					|| (base_form.findField('Diag_eid').getFieldValue('Diag_Code') && base_form.findField('Diag_eid').getFieldValue('Diag_Code').substr(0,3).inlist(['T17','T18','T19']))
					|| (base_form.findField('Diag_oid').getFieldValue('Diag_Code') && base_form.findField('Diag_oid').getFieldValue('Diag_Code').substr(0,3).inlist(['T17','T18','T19']))
				)
			) {
				sw.swMsg.alert(lang['oshibka'], 'Коды T71 и T17.0-T19.0 не сочетаются. Необходимо выбрать только один код');
				this.formStatus = 'edit';
				return false;
			}
		}
		
		if (getRegionNick() == 'ufa' && is_aDiag && base_form.findField('DeathCause_id').getValue().inlist([2, 3, 4, 5, 7, 8])) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Причины смерти А00-R99 не могут иметь коды 2, 3, 4, 5, 6, 7 в поле «Причина смерти»',
				title: ERR_INVFIELDS_TIT
			});
			return false;		
		}
		
		if (
			getRegionNick() == 'ufa' &&
			!Ext.isEmpty(base_form.findField('Diag_eid').getFieldValue('Diag_Code')) && 
			base_form.findField('Diag_eid').getFieldValue('Diag_Code')[0] == 'V' &&
			base_form.findField('DeathCause_id').getValue() == 1
		) {	
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Внешние причины смерти V01-V99 не могут иметь код «1 Заболевание» в поле  «Причина смерти». ',
				title: ERR_INVFIELDS_TIT
			});
			return false;		
		}
		
		// @task https://jira.is-mis.ru/browse/PROMEDWEB-9658
		//Если в одном из полей «Первоначальная причина смерти» и «Прочие важные состояния» 
		//выбраны значения кодов диагнозов U07.1, U07.2, тогда проверяется значение поля «На основании».
		//Если значение поля – «4. Вскрытие», тогда МС о смерти сохраняется
		if (getRegionNick() == 'ufa' && (covidFilter(base_form.findField('Diag_mid').getFieldValue('Diag_Code')) || covidFilter(base_form.findField('Diag_oid').getFieldValue('Diag_Code'))) && base_form.findField('DeathSetCause_id').getValue() !==4) {
			
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Для причины смерти от «COVID-2019» необходимо установить значение ' +
					'«4. Вскрытие» в параметре «На основании»',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if (
			getRegionNick().inlist(['kareliya', 'vologda']) &&
			!options.ignoreEmployment && (
				(person_frame.getFieldValue('Sex_Code') == '2' && person_frame.getFieldValue('Person_Age') > 55) ||
				(person_frame.getFieldValue('Sex_Code') == '1' && person_frame.getFieldValue('Person_Age') > 60)		
			) &&
			!Ext.isEmpty(base_form.findField('DeathEmployment_id').getValue()) &&
			base_form.findField('DeathEmployment_id').getValue().inlist([1, 2, 3, 4, 5])
		) {	
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						options.ignoreEmployment = true;
						this.doSave(options);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Для умерших в возрасте более ' + 
					(person_frame.getFieldValue('Sex_Code') == 1 ? 60 : 55) + 
					' лет,  маловероятно указанное значение «' + 
					base_form.findField('DeathEmployment_id').getFieldValue('DeathEmployment_Name') + 
					'» поля «Занятость». Продолжить сохранение?',
				title: lang['prodoljit_sohranenie']
			});
			return false;
		}

		var death_date = base_form.findField('DeathSvid_DeathDate_Date').getValue();
		var death_time = base_form.findField('DeathSvid_DeathDate_Time').getValue();
		var death_date_str = base_form.findField('DeathSvid_DeathDateStr').getValue();
		var death_date_unknown = base_form.findField('DeathSvid_IsUnknownDeathDate').getValue();
		var death_time_unknown = base_form.findField('DeathSvid_IsNoDeathTime').getValue();

		if (Ext.isEmpty(death_date) && Ext.isEmpty(death_date_str) && ! base_form.findField('DeathSvid_IsUnknownDeathDate').checked) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
					base_form.findField('DeathSvid_DeathDate_Date').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['doljna_byit_ukazana_data_smerti_libo_neutochnennaya_data_smerti_libo_ukazano_chto_data_smerti_neizvestna'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (Ext.isEmpty(death_time) && Ext.isEmpty(death_date_str) && ! base_form.findField('DeathSvid_IsNoDeathTime').checked) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
					base_form.findField('DeathSvid_DeathDate_Date').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['ne_ukazano_vremya_smerti_neobhodimo_ukazat_tochnoe_vremya_smerti_libo_ukazat_chto_vremya_smerti_neizvestno'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var birth_date = Ext.getCmp('MSDEF_PersonInformationFrame').getFieldValue("Person_Birthday");
		if (getRegionNick().inlist(['perm', 'ufa']) && is_R54Diag && !Ext.isEmpty(birth_date)) {
			if(Ext.isEmpty(death_date)){
				death_date = new Date();
			}
			var years = Math.abs(death_date.getUTCFullYear() - birth_date.getUTCFullYear());
			if(years < 81){
				sw.swMsg.alert(lang['oshibka'],'Выбор диагноза «R54. Старость» возможен, '+
					'только если в год смерти пациента ему(ей) исполнилось или должно было исполнится минимум 81 лет');
				this.formStatus = 'edit';
				return false;
			}
		}

		if (getRegionNick() == 'perm' && is_R95Diag && !Ext.isEmpty(birth_date)) {
			if(Ext.isEmpty(death_date)){
				death_date = new Date();
			}
			var age = death_date - birth_date;
			var diff = Math.floor(death_date.getTime() - birth_date.getTime());
		    var day = 1000 * 60 * 60 * 24;
		    var days = Math.floor(diff/day);
			if(days > 364){
				sw.swMsg.alert(lang['oshibka'],'Для установки диагнозов «R95.0 Синдром внезапной смерти младенца с упоминанием о вскрытии» '
					+'и «R95.9 Синдром внезапной смерти младенца без упоминания о вскрытии» возраст' 
					+' пациента должен быть не больше 11 месяцев 30 дней на дату смерти');
				this.formStatus = 'edit';
				return false;
			}
		}

		var diag_iid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagIID').getValue();
		var diag_tid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagTID').getValue();
		var diag_mid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagMID').getValue();
		var diag_eid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagEID').getValue();

		if (!(diag_iid_checkbox || diag_tid_checkbox || diag_mid_checkbox || diag_eid_checkbox)) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Должна быть выбрана первоначальная причина смерти',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (this.saveMode == 2) {
			// сохраняем нового получателя, остальное не меняется.
			deathdt = base_form.findField('DeathSvid_DeathDate_Date').getValue();
			rcpdt = base_form.findField('DeathSvid_RcpDate').getValue();
			if ( !Ext.isEmpty(deathdt) && !Ext.isEmpty(rcpdt) && deathdt > rcpdt ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function () {
						this.formStatus = 'edit';
						base_form.findField('DeathSvid_RcpDate').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Дата получения не может быть меньше даты выдачи',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			win.getLoadMask(lang['sohranenie_dannyih_o_poluchatele']).show();
			Ext.Ajax.request({ //заполнение номера и серии
				callback: function (options, success, response) {
					win.getLoadMask(lang['sohranenie_dannyih_o_poluchatele']).hide();
					this.formStatus = 'edit';
					var result = Ext.util.JSON.decode(response.responseText);
					if(result && result.success) {
						var svid_grid = Ext.getCmp('MedSvidDeathStreamWindowSearchGrid');
						if (svid_grid && svid_grid.ViewGridStore) {
							svid_grid.ViewGridStore.reload();
						}

						var svid_id = base_form.findField('DeathSvid_id').getValue();

						Ext.getCmp('MedSvidDeathEditWindow').hide();

						params.DeathSvid_IsDuplicate = base_form.findField('DeathSvid_IsDuplicate').getValue();
						params.svid_id = svid_id;

						if (getRegionNick() != 'perm') {
							getWnd('swMedSvidDeathPrintWindow').show(params);
						}
					
						win.checkSuicide();
					}
					else{
						if ( result.Error_Msg) {
							sw.swMsg.alert(lang['oshibka'], result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this),
				params: {
					DeathSvid_id: base_form.findField('DeathSvid_id').getValue(),
					DeathSvidType_id: base_form.findField('DeathSvidType_id').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					Person_rid: base_form.findField('Person_rid').getValue(),
					DeathSvid_Ser: base_form.findField('DeathSvid_Ser').getValue(),
					DeathSvid_Num: base_form.findField('DeathSvid_Num').getValue(),
					DeathSvid_BirthDateStr: base_form.findField('DeathSvid_BirthDateStr').getValue(),
					DeathSvid_DeathDateStr: base_form.findField('DeathSvid_DeathDateStr').getValue(),
					DeathSvidRelation_id: base_form.findField('DeathSvidRelation_id').getValue(),
					DeathSvid_DeathDate_Date: !Ext.isEmpty(base_form.findField('DeathSvid_DeathDate_Date').getValue())?base_form.findField('DeathSvid_DeathDate_Date').getValue().format('d.m.Y'):null,
					DeathSvid_PolFio: base_form.findField('DeathSvid_PolFio').getValue(),
					DeathSvid_RcpDocument: base_form.findField('DeathSvid_RcpDocument').getValue(),
					DeathSvid_RcpDate: !Ext.isEmpty(base_form.findField('DeathSvid_RcpDate').getValue())?base_form.findField('DeathSvid_RcpDate').getValue().format('d.m.Y'):null
				},
				url: '/?c=MedSvid&m=saveDeathRecipient'
			});

			return true;
		}

		var
			birthdt = Ext.getCmp('MSDEF_PersonInformationFrame').getFieldValue("Person_Birthday"),
			deathdt = base_form.findField('DeathSvid_DeathDate_Date').getValue();
		if ( !Ext.isEmpty(deathdt) && !Ext.isEmpty(birthdt) && birthdt > deathdt ) {
			this.formStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'],lang['data_smerti_ne_mojet_byit_ranshe_datyi_rojdeniya'], function() {
				base_form.findField('DeathSvid_DeathDate_Date').focus(true, 250)
			});
			return false;
		}

		params.Person_id = person_frame.personId;
		params.Person_mid = base_form.findField('Person_mid').disabled ? null : base_form.findField('Person_mid').getValue();
		params.Person_rid = base_form.findField('Person_rid').getValue();
		params.DeathSvidType_id = base_form.findField('DeathSvidType_id').getValue();
		params.DeathSvid_predid = base_form.findField('DeathSvid_predid').getValue();
		params.DeathSvid_IsDuplicate = base_form.findField('DeathSvid_IsDuplicate').getValue();
		params.DeathSvid_IsLose = base_form.findField('DeathSvid_IsLose').getValue();
		params.ReceptType_id = base_form.findField('ReceptType_id').getValue();
		params.DeathCause_id = base_form.findField('DeathCause_id').getValue();
		params.DeathFamilyStatus_id = base_form.findField('DeathFamilyStatus_id').getValue();
		params.DeathSvid_IsNoPlace = base_form.findField('DeathSvid_IsNoPlace').getValue();
        params.DeathSvid_isBirthDate = base_form.findField('DeathSvid_isBirthDate').getValue();
		params.DeathPlace_id = base_form.findField('DeathPlace_id').getValue();
		params.DeathEducation_id = base_form.findField('DeathEducation_id').getValue();
		params.DeathTrauma_id = base_form.findField('DeathTrauma_id').getValue();
		params.DeathSetType_id = base_form.findField('DeathSetType_id').getValue();
		params.DeathSetCause_id = base_form.findField('DeathSetCause_id').getValue();
		params.DeathWomanType_id = base_form.findField('DeathWomanType_id').getValue();
		params.DeathEmployment_id = base_form.findField('DeathEmployment_id').getValue();
		params.DtpDeathTime_id = base_form.findField('DtpDeathTime_id').getValue();
		params.Diag_iid = base_form.findField('Diag_iid').getValue();
		params.Diag_tid = base_form.findField('Diag_tid').getValue();
		params.Diag_mid = base_form.findField('Diag_mid').getValue();
		params.Diag_eid = base_form.findField('Diag_eid').getValue();
		params.Diag_oid = base_form.findField('Diag_oid').getValue();
		params.DeathSvid_IsPrimDiagIID = base_form.findField('DeathSvid_IsPrimDiagIID').getValue();
		params.DeathSvid_IsPrimDiagTID = base_form.findField('DeathSvid_IsPrimDiagTID').getValue();
		params.DeathSvid_IsPrimDiagMID = base_form.findField('DeathSvid_IsPrimDiagMID').getValue();
		params.DeathSvid_IsPrimDiagEID = base_form.findField('DeathSvid_IsPrimDiagEID').getValue();
		params.DeathSvid_Ser = base_form.findField('DeathSvid_Ser').getValue();
		params.DeathSvid_Num = base_form.findField('DeathSvid_Num').getValue();
		params.DeathSvid_OldSer = base_form.findField('DeathSvid_OldSer').getValue();
		params.DeathSvid_OldNum = base_form.findField('DeathSvid_OldNum').getValue();
		if ( base_form.findField('DeathSvid_BirthDateStr').disabled ) {
			params.DeathSvid_BirthDateStr = base_form.findField('DeathSvid_BirthDateStr').getValue();
		}
		if (!Ext.isEmpty(base_form.findField('DeathSvid_DeathDate_Date').getValue())) {
			params.DeathSvid_DeathDate_Date = base_form.findField('DeathSvid_DeathDate_Date').getValue().format('d.m.Y');
		}
		params.DeathSvid_DeathDate_Time = base_form.findField('DeathSvid_DeathDate_Time').getValue();
		params.DeathSvid_IsNoDeathTime = base_form.findField('DeathSvid_IsNoDeathTime').getValue();
        params.DeathSvid_IsNoAccidentTime = base_form.findField('DeathSvid_IsNoAccidentTime').getValue();
		params.DeathSvid_Mass = base_form.findField('DeathSvid_Mass').disabled ? null : base_form.findField('DeathSvid_Mass').getValue();
		params.DeathSvid_Month = base_form.findField('DeathSvid_Month').disabled ? null : base_form.findField('DeathSvid_Month').getValue();
		params.DeathSvid_Day = base_form.findField('DeathSvid_Day').disabled ? null : base_form.findField('DeathSvid_Day').getValue();
		params.DeathSvid_ChildCount = base_form.findField('DeathSvid_ChildCount').disabled ? null : base_form.findField('DeathSvid_ChildCount').getValue();
		if (!Ext.isEmpty(base_form.findField('DeathSvid_TraumaDate_Date').getValue())) {
			params.DeathSvid_TraumaDate_Date = base_form.findField('DeathSvid_TraumaDate_Date').getValue().format('d.m.Y');
		}
		params.DeathSvid_TraumaDate_Time = base_form.findField('DeathSvid_TraumaDate_Time').getValue();
		params.DeathSvid_TraumaDescr = base_form.findField('DeathSvid_TraumaDescr').getValue();
		params.DeathSvid_Oper = base_form.findField('DeathSvid_Oper').getValue();
		params.DeathSvid_PribPeriod = base_form.findField('DeathSvid_PribPeriod').getValue();
		if (!Ext.isEmpty(base_form.findField('DeathSvid_RcpDate').getValue())) {
			params.DeathSvid_RcpDate = base_form.findField('DeathSvid_RcpDate').getValue().format('d.m.Y');
		}
		if (!Ext.isEmpty(base_form.findField('DeathSvid_GiveDate').getValue())) {
			params.DeathSvid_GiveDate = base_form.findField('DeathSvid_GiveDate').getValue().format('d.m.Y');
		}
		if (!Ext.isEmpty(base_form.findField('DeathSvid_OldGiveDate').getValue())) {
			params.DeathSvid_OldGiveDate = base_form.findField('DeathSvid_OldGiveDate').getValue().format('d.m.Y');
		}
		params.DeathSvid_RcpDocument = base_form.findField('DeathSvid_RcpDocument').getValue();
		params.OrgHeadPost_id = base_form.findField('OrgHeadPost_id').getValue();
		params.Person_hid = base_form.findField('Person_hid').getValue();

		// хотя бы один диагноз должен быть заполнен
		if (((params.Diag_iid === '') || (params.Diag_iid === null ))
			&& ((params.Diag_tid === '') || (params.Diag_tid === null ))
			&& ((params.Diag_mid === '') || (params.Diag_mid === null ))
			&& ((params.Diag_eid === '') || (params.Diag_eid === null ))
			&& ((params.Diag_oid === '') || (params.Diag_oid === null ))) {
			sw.swMsg.alert(lang['oshibka'], lang['hotya_byi_odin_iz_diagnozov_doljen_byit_zapolnen']);
			this.formStatus = 'edit';
			return false;
		}

		params.LpuSection_id = base_form.findField('LpuSection_id').disabled ? null : base_form.findField('LpuSection_id').getValue();

		var Person_Birthday = person_frame.getFieldValue('Person_Birthday');

		if (getRegionNick().inlist(['ekb', 'msk', 'vologda'])) {
			// При сохранении МС о смерти, если «Дата рождения» больше чем «дата выдачи» МС о смерти, то показывать сообщение об ошибке: "Дата/год рождения не может быть больше даты/года выписки свидетельства."
			if (Person_Birthday && base_form.findField('DeathSvid_GiveDate').getValue() && Person_Birthday > base_form.findField('DeathSvid_GiveDate').getValue()) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Дата/год рождения не может быть больше даты/года выписки свидетельства.');
				return false;
			}
			// При сохранении МС о смерти, если поле «Дата» (блок «Дата и время начала случая, отравления, травмы») не пустая и меньше чем дата рождения Человека, то показывать сообщение об ошибке: Дата/год травмы не может быть меньше даты/года рождения."
			if (Person_Birthday && base_form.findField('DeathSvid_TraumaDate_Date').getValue() && base_form.findField('DeathSvid_TraumaDate_Date').getValue() < Person_Birthday) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Дата/год травмы не может быть меньше даты/года рождения');
				return false;
			}
		}

		var diag_iid_combo = base_form.findField('Diag_iid');
		var diag_tid_combo = base_form.findField('Diag_tid');
		var diag_mid_combo = base_form.findField('Diag_mid');
		var diag_eid_combo = base_form.findField('Diag_eid');
		var diag_oid_combo = base_form.findField('Diag_oid');
		var diag_i_value = diag_iid_combo.getValue();
		var diag_t_value = diag_tid_combo.getValue();
		var diag_m_value = diag_mid_combo.getValue();
		var diag_e_value = diag_eid_combo.getValue();
		var diag_o_value = diag_oid_combo.getValue();
		var diag_i_code = diag_iid_combo.getFieldValue('Diag_Code');
		var diag_t_code = diag_tid_combo.getFieldValue('Diag_Code');
		var diag_m_code = diag_mid_combo.getFieldValue('Diag_Code');
		var diag_e_code = diag_eid_combo.getFieldValue('Diag_Code');
		var diag_o_code = diag_oid_combo.getFieldValue('Diag_Code');

		// предупреждения
		var warnings = "";

		if (getRegionNick() == 'ekb') {

			var belowOneYear = (
				!Ext.isEmpty(base_form.findField('Person_mid').getValue())
				|| !Ext.isEmpty(base_form.findField('BAddress_AddressText').getValue())
				|| !Ext.isEmpty(base_form.findField('ChildTermType_id').getValue())
				|| !Ext.isEmpty(base_form.findField('DeathSvid_Mass').getValue())
				|| !Ext.isEmpty(base_form.findField('DeathSvid_ChildCount').getValue())
				|| !Ext.isEmpty(base_form.findField('DeathSvid_Month').getValue())
				|| !Ext.isEmpty(base_form.findField('DeathSvid_Day').getValue())
				|| !Ext.isEmpty(base_form.findField('Mother_Age').getValue())
				|| !Ext.isEmpty(base_form.findField('Mother_BirthDay').getValue())
			);

			// ошибки

			// При сохранении МС о смерти с заполненными данными, относящимися к матери (блок «Для детей, умерших в возрасте до 1 года»), то необходимо проверять возраст матери: -	если заполнена дата рождения матери и вычисляемы возраст менее 10 лет ИЛИ -	дата рождения не заполнены И указан возраст матери меньше 10 лет, ТО показывать сообщение об ошибки «"Возраст матери не может быть меньше 10 лет.»
			if (belowOneYear && (
				(
					base_form.findField('Mother_BirthDay').getValue()
					&& swGetPersonAge(base_form.findField('Mother_BirthDay').getValue()) < 10
				) || (
					!base_form.findField('Mother_BirthDay').getValue()
					&& base_form.findField('Mother_Age').getValue()
					&& base_form.findField('Mother_Age').getValue() < 10
				)
			)) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Возраст матери не может быть меньше 10 лет.');
				return false;
			}

			// При сохранении МС о смерти если разница дат: «дата смерти» и «дата выдачи» МС о смерти больше 4х дней, то показывать сообщение об ошибке "Период между датой смерти и датой выдачи свидетельства должен быть меньше 5-ти дней."
			if (base_form.findField('DeathSvid_DeathDate_Date').getValue() && base_form.findField('DeathSvid_GiveDate').getValue() && base_form.findField('DeathSvid_GiveDate').getValue() > base_form.findField('DeathSvid_DeathDate_Date').getValue().add(Date.DAY, 4)) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Период между датой смерти и датой выдачи свидетельства должен быть меньше 5-ти дней.');
				return false;
			}

			// При сохранении МС о смерти если в Поле «Смерть наступила» выбрано значение «3 В стационаре», то должны быть заполнены поля адреса «Место смерти»:
			if (base_form.findField('DeathPlace_id').getValue() == 1) {
				// -	«Регион» .Если поле не заполнено, то показывать сообщение об ошибке «Не заполнена 'Область (край) места смерти»
				if (Ext.isEmpty(base_form.findField('DKLRGN_id').getValue())) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], 'Не заполнена "Область (край) места смерти".');
					return false;
				}

				// -	«Район» или «Город». Если оба поля не заполнены, то показывать сообщение об ошибке «Не заполнен 'Район/город места смерти»
				if (Ext.isEmpty(base_form.findField('DKLSubRGN_id').getValue()) && Ext.isEmpty(base_form.findField('DKLCity_id').getValue())) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], 'Не заполнен "Район/город места смерти".');
					return false;
				}

				// -	«Улица» Если не заполнена, то показывать сообщение «Не заполнен 'Насел. пункт места смерти»
				if (Ext.isEmpty(base_form.findField('DKLStreet_id').getValue())) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], 'Не заполнена "Улица места смерти".');
					return false;
				}

				// -	«Дом» Если не заполнена, то показывать сообщение «Дом места смерти»
				if (Ext.isEmpty(base_form.findField('DAddress_House').getValue())) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], 'Не заполнен "Дом места смерти".');
					return false;
				}
			}

			// При сохранении МС о смерти если в Поле «Смерть наступила» выбрано значение «4 Дома» И адрес «место смерти» не совпадает с адресом (регистрации или проживания) Человека, то показывать сообщение об ошибке: Место смерти не совпадает с местом жительства'
			if (base_form.findField('DeathPlace_id').getValue() == 2 && base_form.findField('DAddress_Address').getValue() != person_frame.getFieldValue('RAddress_Address') && base_form.findField('DAddress_Address').getValue() != person_frame.getFieldValue('PAddress_Address')) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Место смерти не совпадает с местом жительства.');
				return false;
			}

			// При сохранении МС о смерти если в Поле «Смерть наступила» выбрано значение «5 В другом месте», то адрес (регистрации и или проживания) Человека не должен совпадать с адресом «место смерти», иначе показывать сообщение об ошибке «Место смерти не должно совпадать с местом жительства»
			if (base_form.findField('DeathPlace_id').getValue() == 3 && (base_form.findField('DAddress_Address').getValue() == person_frame.getFieldValue('RAddress_Address') || base_form.findField('DAddress_Address').getValue() == person_frame.getFieldValue('PAddress_Address'))) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Место смерти не должно совпадать с местом жительства.');
				return false;
			}

			if (base_form.findField('DeathSvid_Ser').getValue() == '66728') {
				// Если значение серии 66728 и  длина номера меньше 6-ти цифр, то ошибка: "Для серии 66728 длина номера должна быть не меньше 6-ти цифр!"
				if (base_form.findField('DeathSvid_Num').getValue().length < 6) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], 'Для серии 66728 длина номера должна быть не меньше 6-ти цифр!');
					return false;
				}
			} else {
				// Если значение серии не равно 66728 и длина номера меньше 5 цифр, то ошибка: "С такой серией длина номера должна быть не меньше 5-ти цифр!"
				if (base_form.findField('DeathSvid_Num').getValue().length < 5) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], 'С такой серией длина номера должна быть не меньше 5-ти цифр!');
					return false;
				}
			}

			// Если в блоке «Для детей, умерших в возрасте до 1 года» заполнено поле «Масса (г) И значение не попадает в диапазон 500-9000, то показывать сообщение об ошибке «Масса тела при рождении не может быть больше 9000 грамм и меньше 500 грамм».
			if (base_form.findField('DeathSvid_Mass').getValue() && !(base_form.findField('DeathSvid_Mass').getValue() >= 500 && base_form.findField('DeathSvid_Mass').getValue() <= 9000)) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Масса тела при рождении не может быть больше 9000 грамм и меньше 500 грамм.');
				return false;
			}

			// При сохранении МС о смерти необходимо проверять отсутствие одинаковых диагнозов в полях «Непосредственная причина смерти», «Патологическое состояние», «первоначальная причина смерти», «Внешние причины», «Прочие важные состояния».  Если присутствуют  диагнозы дублируются хотя бы в двух полях, то показывать сообщение об ошибке: «Наличие одинаковых кодов болезней недопустимо».
			var errorDiagDouble = false;
			var Diags = [];
			if (!Ext.isEmpty(base_form.findField('Diag_iid').getValue())) {
				if (base_form.findField('Diag_iid').getValue().toString().inlist(Diags)) {
					errorDiagDouble = true;
				}
				Diags.push(base_form.findField('Diag_iid').getValue().toString());
			}
			if (!Ext.isEmpty(base_form.findField('Diag_tid').getValue())) {
				if (base_form.findField('Diag_tid').getValue().toString().inlist(Diags)) {
					errorDiagDouble = true;
				}
				Diags.push(base_form.findField('Diag_tid').getValue().toString());
			}
			if (!Ext.isEmpty(base_form.findField('Diag_mid').getValue())) {
				if (base_form.findField('Diag_mid').getValue().toString().inlist(Diags)) {
					errorDiagDouble = true;
				}
				Diags.push(base_form.findField('Diag_mid').getValue().toString());
			}
			if (!Ext.isEmpty(base_form.findField('Diag_eid').getValue())) {
				if (base_form.findField('Diag_eid').getValue().toString().inlist(Diags)) {
					errorDiagDouble = true;
				}
				Diags.push(base_form.findField('Diag_eid').getValue().toString());
			}
			if (!Ext.isEmpty(base_form.findField('Diag_oid').getValue())) {
				if (base_form.findField('Diag_oid').getValue().toString().inlist(Diags)) {
					errorDiagDouble = true;
				}
				Diags.push(base_form.findField('Diag_oid').getValue().toString());
			}
			if (errorDiagDouble) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Наличие одинаковых кодов болезней недопустимо.');
				return false;
			}

			// При сохранении МС о смерти если не заполнены поля в блоке «Для детей, умерших в возрасте до 1 года», заполнена «дата смерти» И известна «дата рождения» Человека, необходимо проверять год рождения и год смерти. Если год рождения равен году смерти, то показывать сообщение об ошибке: «У взрослого год рождения не может совпадать с годом смерти»
			if (!belowOneYear && Person_Birthday && base_form.findField('DeathSvid_DeathDate_Date').getValue() && Person_Birthday.format('Y') == base_form.findField('DeathSvid_DeathDate_Date').getValue().format('Y')) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'У взрослого год рождения не может совпадать с годом смерти.');
				return false;
			}

			var period_i_value = base_form.findField('DeathSvid_TimePeriod').getValue();
			var period_t_value = base_form.findField('DeathSvid_TimePeriodPat').getValue();
			var period_m_value = base_form.findField('DeathSvid_TimePeriodDom').getValue();
			var period_e_value = base_form.findField('DeathSvid_TimePeriodExt').getValue();
			var okei_i_value = base_form.findField('Okei_id').getValue();
			var okei_t_value = base_form.findField('Okei_patid').getValue();
			var okei_m_value = base_form.findField('Okei_domid').getValue();
			var okei_e_value = base_form.findField('Okei_extid').getValue();

			var diag_combos = [
				[diag_iid_combo,diag_i_value,period_i_value,okei_i_value],
				[diag_tid_combo,diag_t_value,period_t_value,okei_t_value],
				[diag_mid_combo,diag_m_value,period_m_value,okei_m_value]
			];

			function convertToDays(time,okei){
				if(!time || !okei){
					return 0;
				}
				var result = 0;
				switch(okei){
					case 99:
					case '99':
						result = time / (24*60);
					break;
					case 100:
					case '100':
						result = time / 24;
					break;
					case 101:
					case '101':
						result = time;
					break;
					case 102:
					case '102':
						result = time * 7;
					break;
					case 104:
					case '104':
						result = time * 30;
					break;
					case 107:
					case '107':
						result = time * 365;
					break;
				}
				return result;
			}

			var error_text = '';
			for(var i = 0;i<diag_combos.length;i++){
				var diag_value = diag_combos[i][1];
				var time_value = diag_combos[i][2];
				var okei_value = diag_combos[i][3];
				if(diag_value){
					if(!okei_value){
						okei_value = 101;
					}
					
					var res_time = convertToDays(time_value,okei_value);
					var code = diag_combos[i][0].getStore().getById(diag_value).get('Diag_Code');
					code2 = code.substr(0,3);
					if(code2>='I60' && code2<='I64' && res_time > 30){
						error_text = 'Период времени для причин смерти I60-I64 (из п.19(I) для а-в) может быть не более 30-ти дней.';
						break;
					}
					if(code2 == 'I69' && res_time < 30){
						error_text = 'Период времени для причины смерти I69 (из п.19(I) для а-в) может быть не менее 30-ти дней.';
						break;
					}
					if(((code>='I21.0' && code<='I21.4') || (code>='I22.0' && code<='I22.8')) && res_time > 28){
						error_text = 'Период времени для причин смерти I21.0-I21.4,I22.0-I22.8 (из п.19(I) для а-в) должен быть не более 28-ми дней.';
						break;
					}
					if(code == 'I24.8' && res_time > 1){
						error_text = 'Период времени для причины смерти I24.8 (из п.19(I) для а-в) может быть менее 1-го дня.';
						break;
					}
					if(code.inlist(['I25.2','I25.3','I25.8']) && res_time < 28){
						error_text = 'Период времени для причин I25.2,I25.3,I25.8 (из п.19(I) для а-в) может быть не менее 28-ми дней.';
						break;
					}
				}
			}

			if(!okei_i_value){
				okei_i_value = 101;
			}
			if(!okei_t_value){
				okei_t_value = 101;
			}
			if(!okei_m_value){
				okei_m_value = 101;
			}
			if(!okei_e_value){
				okei_e_value = 101;
			}
			var res_i_time = convertToDays(period_i_value,okei_i_value);
			var res_t_time = convertToDays(period_t_value,okei_t_value);
			var res_m_time = convertToDays(period_m_value,okei_m_value);
			var res_e_time = convertToDays(period_e_value,okei_e_value);

			if(diag_i_value && period_i_value){
				if(error_text.length == 0 && diag_t_value && period_t_value && res_i_time > res_t_time){
					error_text = "Период времени между началом патолог. процесса  пункта 'а' больше чем пункта 'б'.";
				}
				if(error_text.length == 0 && diag_m_value && period_m_value && res_i_time > res_m_time){
					error_text = "Период времени между началом патолог. процесса  пункта 'а' больше чем пункта 'в'.";
				}
				if(error_text.length == 0 && diag_e_value && period_e_value && res_i_time > res_e_time){
					error_text = "Период времени между началом патолог. процесса  пункта 'а' больше чем пункта 'г'.";
				}
			}

			if(diag_t_value && period_t_value){
				if(error_text.length == 0 && diag_m_value && period_m_value && res_t_time > res_m_time){
					error_text = "Период времени между началом патолог. процесса  пункта 'б' больше чем пункта 'в'.";
				}
				if(error_text.length == 0 && diag_e_value && period_e_value && res_t_time > res_e_time){
					error_text = "Период времени между началом патолог. процесса  пункта 'б' больше чем пункта 'г'.";
				}
			}

			if(diag_m_value && period_m_value){
				if(error_text.length == 0 && diag_e_value && period_e_value && res_m_time > res_e_time){
					error_text = "Период времени между началом патолог. процесса  пункта 'в' больше чем пункта 'г'.";
				}
			}

			if(error_text.length == 0){
				var death_set_value = base_form.findField('DeathSetType_id').getValue();
				var med_staff_fact_did = base_form.findField('MedStaffFact_did').getValue();
				if(med_staff_fact_did && base_form.findField('MedStaffFact_did').getStore().getById(med_staff_fact_did)){
					var post_name = base_form.findField('MedStaffFact_did').getStore().getById(med_staff_fact_did).get('PostMed_Name'); 
					post_name = post_name.toLowerCase();
					var post_error = false;
					switch(death_set_value){
						case 1:
						case '1':
						case 2:
						case '2':
							if(post_name.indexOf('врач') == -1){
								post_error = true;
							}
							break;
						case 3:
						case '3':
							if(post_name.indexOf('фельдшер') == -1){
								post_error = true;
							}
							break;
						case 4:
						case '4':
							if(post_name.indexOf('патологоанатом') == -1){
								post_error = true;
							}
							break;
						case 5:
						case '5':
							if(post_name.indexOf('судмедэксперт') == -1){
								post_error = true;
							}
							break;
					}
					if(post_error){
						error_text = "Несоответствие должности в п.17 и п.18.";
					}
				}
			}

			if(error_text.length > 0){
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], error_text);
				return false;
			}


			// При сохранении МС о смерти если в Поле «Смерть наступила» выбрано значение «3 В стационаре», то должны быть заполнены поля адреса «Место смерти»:
			if (base_form.findField('DeathPlace_id').getValue() == 1) {
				// -	«Нас. Пункт» Если не заполнен, то показывать предупреждение  "Не заполнен 'Насел. пункт места смерти"
				if (Ext.isEmpty(base_form.findField('DKLCity_id').getValue()) && Ext.isEmpty(base_form.findField('DKLTown_id').getValue())) {
					warnings += 'Не заполнен "Насел. пункт места смерти".<br>';
				}
			}

			if ( !Ext.isEmpty(deathdt) && !Ext.isEmpty(birthdt) ) {
				var dY = 0;
				var bY = 0;
				if(typeof deathdt.getFullYear == 'function'){
					dY = deathdt.getFullYear();
				}
				if(typeof birthdt.getFullYear == 'function'){
					bY = birthdt.getFullYear();
				}log(dY,bY);
				if(dY > 0 && bY > 0 && ((dY-bY) > 150)){
					warnings += 'Возраст пациента на дату смерти превысил 150 лет.<br>';
				}
			}

			var death_education = base_form.findField('DeathEducation_id').getValue();
			var death_employment = base_form.findField('DeathEmployment_id').getValue();
			if(death_education && death_employment && death_education > 2 && !death_employment.inlist([4,6,7,8,9,10])){
				warnings += 'При образовании ниже профессионального начального не может быть занятости выше неквалифицированного рабочего.<br>';
			}
		}

        // #195193
        if( getRegionNick().inlist( ['msk', 'vologda'] ) ) {
        	// при выборе занятости "пенсионеры"
            if(params.DeathEmployment_id == 6){
                var sex_code = person_frame.getFieldValue('Sex_Code');
                var pers_age = person_frame.getFieldValue('Person_Age');
                // проверяем на соответствие возраста и выводим предупреждение
                if( (sex_code == 2 && pers_age > 18 && pers_age < 60) || (sex_code == 1 && pers_age > 18 && pers_age < 65) ) {
                    warnings += 'Возраст пациента не соответствует типу занятости "Пенсионеры".<br>';
                }
            }
        }

        if (!options.ignoreWarnings && warnings.length > 0) {
            this.formStatus = 'edit';
            sw.swMsg.show({
                buttons: Ext.Msg.YESNO,
                fn: function(buttonId, text, obj) {
                    if ( buttonId == 'yes' ) {
                        options.ignoreWarnings = true;
                        this.doSave(options);
                    }
                }.createDelegate(this),
                icon: Ext.MessageBox.QUESTION,
                msg: 'Внимание!<br>' + warnings + 'Продолжить сохранение?',
                title: lang['prodoljit_sohranenie']
            });
            return false;
        }

		// #195193
		if( getRegionNick().inlist( ['msk', 'vologda'] ) ) {
			// #195193 Дата рождения» должна быть меньше текущей даты
			if( params.DeathSvid_isBirthDate ){
                var BirthDateParsedStr = Date.parseDate( base_form.findField('DeathSvid_BirthDateStr').getValue(), 'd.m.Y');
				var currentDate = new Date();
				if ( !Ext.isEmpty(BirthDateParsedStr) && BirthDateParsedStr > currentDate) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function () {
							this.formStatus = 'edit';
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: 'Дата рождения» должна быть меньше текущей даты',
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}

		}

		if( getRegionNick().inlist(['vologda','kareliya']) ){
			// #193191 контроль на установку диагнозов "R54 Старость" и "I46.1 Внезапная сердечная смерть, так описанная"

			var warning_controls = {
				check_diag: false,
				message: '',
				diag_code: '',
			};
			if (!options.ignoreWarning_R54 && 'R54'.inlist([diag_i_code, diag_t_code, diag_m_code, diag_e_code, diag_o_code])) {
				warning_controls.check_diag = true;
				warning_controls.diag_code = 'ignoreWarning_R54';
				warning_controls.message = 'Вы уверены в использовании данного кода? Диагноз R54 устанавливается только в тех случаях,' +
					' когда в медицинской документации отсутствует указание на заболевание, способное вызвать смерть и при отсутствии подозрения на насильственную смерть. ' +
					'У пациента есть случаи лечение за последний год: ознакомьтесь с Историей лечения пациента. Вызов истории по кнопке F11';
			}
			if (!options.ignoreWarning_I46 && 'I46.1'.inlist([diag_i_code, diag_t_code, diag_m_code, diag_e_code, diag_o_code]) ) {
				warning_controls.check_diag = true;
				warning_controls.diag_code = 'ignoreWarning_I46';
				warning_controls.message = 'Вы уверены в использовании данного кода? Диагноз I46.1 устанавливается в случае отсутствия клинических или патологоанатомических ' +
					'данных о том, что больной страдал ИБС. У пациента есть случаи лечение за последний год: ознакомьтесь с Историей лечения пациента. ' +
					'Вызов истории по кнопке F11';
			}
			if( warning_controls.check_diag ){
				var control_params = {
					Person_id: params.Person_id,
				};
				if (!Ext.isEmpty(params.DeathSvid_GiveDate)) {
					control_params.DeathSvid_GiveDate = params.DeathSvid_GiveDate;
				}
				if (!Ext.isEmpty(params.DeathSvid_DeathDate_Date)) {
					control_params.DeathSvid_DeathDate_Date = params.DeathSvid_DeathDate_Date;
				}
				win.getLoadMask(LOAD_WAIT).show();
				Ext.Ajax.request({
					callback: function (opt, success, response) {
						win.getLoadMask().hide();
						if (success && response.responseText != '') {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj && !response_obj.success) {
								this.formStatus = 'edit';
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function (buttonId, text, obj) {
										if (buttonId == 'yes') {
											options[warning_controls.diag_code] = true;
											win.doSave(options);
										}
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: warning_controls.message + '<br>Продолжить сохранение?',
									title: lang['prodoljit_sohranenie']
								});
								return false;
							}
						} else {
							sw.swMsg.alert(lang['oshibka'], 'Ошибка при проверке валидности диагнозов');
						}
					},
					params: control_params,
					url: '/?c=MedSvid&m=checkR54diagnose'
				});
				this.formStatus = 'edit';
				return false;
			}
		}

		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var med_personal_id = null;
		var record = null;
		var errMsg = "";
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение свидетельства..."});

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if (record) {
			med_personal_id = record.get('MedPersonal_id');
			base_form.findField('MedPersonal_id').setValue(med_personal_id);
		}
		params.MedPersonal_id = med_personal_id;
		params.MedStaffFact_id = med_staff_fact_id;

		loadMask.show();

		if (errMsg == "") {
			base_form.submit({
				failure: function (result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();

					if (action.result) {
						if (action.result.Error_Msg) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this),
				params: params,
				success: function (result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();

					if ( this.callback ){
						this.callback();
					}

					var svid_grid = Ext.getCmp('MedSvidDeathStreamWindowSearchGrid');
					if (svid_grid && svid_grid.ViewGridStore) {
						svid_grid.ViewGridStore.reload();
					}

					//Так же перезагружаем грид АРМа патологоанатома если он открыт
					var pat_grid = Ext.getCmp('WorkPlacePathoMorphologyGridPanel');
					if (pat_grid && pat_grid.ViewGridStore) {
						pat_grid.ViewGridStore.reload();
					}

					var svid_id = action.result.svid_id;

					Ext.getCmp('MedSvidDeathEditWindow').hide();

					params.DeathSvid_IsDuplicate = base_form.findField('DeathSvid_IsDuplicate').getValue();
					params.svid_id = svid_id;

					if (getRegionNick() != 'perm') {
						getWnd('swMedSvidDeathPrintWindow').show(params);
					}
					
					win.checkSuicide();

				}.createDelegate(this)
			});
		} else {
			sw.swMsg.alert(lang['oshibka'], errMsg);
		}
	},
	draggable: true,
	formStatus: 'edit',
	height: 500,
	id: 'MedSvidDeathEditWindow',
	initComponent: function () {
		var label_mod_1 = -10; //страница 1, модификатор ширины названий полей
		var label_mod_2 = -22; //страница 2, модификатор ширины названий полей
		var field_mod_1 = -15; //страница 1, модификатор ширины полей
		var field_mod_2 = -58; //страница 2, модификатор ширины полей
		var win = this;
		Ext.apply(this, {
			buttons: [{
				handler: function () {
					this.doSave({
						checkDrugRequest: true,
						copy: false,
						print: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EREF + 60,
				text: BTN_FRMSAVE,
				tooltip: lang['sohranit_vvedennyie_dannyie']
			}, {
				text: '-'
			},
				HelpButton(this),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					tabIndex: TABINDEX_EREF + 61,
					text: BTN_FRMCANCEL,
					tooltip: lang['zakryit_okno']
				}],
			items: [new sw.Promed.PersonInformationPanel({
				button2Callback: function (callback_data) {
					this.findById('MSDEF_PersonInformationFrame').load({
						Person_id: callback_data.Person_id,
						Server_id: callback_data.Server_id
					});
				}.createDelegate(this),
				button1OnHide: function () {
					if (this.action == 'view') {
						this.buttons[this.buttons.length - 1].focus();
					} else {
						this.findById('MedSvidDeathEditForm').getForm().findField('ReceptType_id').focus(true);
					}
				}.createDelegate(this),
				button2OnHide: function () {
					this.findById('MSDEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button3OnHide: function () {
					this.findById('MSDEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button4OnHide: function () {
					this.findById('MSDEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button5OnHide: function () {
					this.findById('MSDEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				id: 'MSDEF_PersonInformationFrame',
				region: 'north'
			}),
				new Ext.form.FormPanel({
					bodyStyle: 'background:#FFFFFF;padding:0px;',
					border: false,
					frame: false,
					layout: 'border',
					id: 'MedSvidDeathEditForm',
					items: [new Ext.TabPanel({
						id: 'MedSvidDeathEditWindowTab',
						activeTab: 0,
						region: 'center',
						// bodyStyle:'padding:5px;',
						layoutOnTabChange: true,
						defaults: {autoScroll: true, bodyStyle: 'padding:5px;'},
						border: false,
						items: [{
							title: lang['0_dannyie_o_patsiente'],
							layout: 'form',
							labelWidth: 150 + label_mod_1,
							border: false,
							items: [{
								name: 'DeathSvid_id',
								xtype: 'hidden'
							}, {
								name: 'Person_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'DeathSvid_IsDuplicate',
								xtype: 'hidden'
							}, {
								name: 'DeathSvid_IsLose',
								xtype: 'hidden'
							}, {
								name: 'DeathSvid_predid',
								xtype: 'hidden'
							}, {
								name: 'Server_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'BAddress_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'DAddress_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'Person_m_FIO',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'Person_r_FIO',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'MedPersonal_id',
								value: null,
								xtype: 'hidden'
							}, {
								name: 'Lpu_id',
								xtype: 'hidden'
							}, {
								xtype: 'hidden',
								name: 'DAddress_Zip'
							}, {
								xtype: 'hidden',
								name: 'DKLCountry_id'
							}, {
								xtype: 'hidden',
								name: 'DKLRGN_id'
							}, {
								xtype: 'hidden',
								name: 'DKLSubRGN_id'
							}, {
								xtype: 'hidden',
								name: 'DKLCity_id'
							}, {
								xtype: 'hidden',
								name: 'DKLTown_id'
							}, {
								xtype: 'hidden',
								name: 'DKLStreet_id'
							}, {
								xtype: 'hidden',
								name: 'DAddress_House'
							}, {
								xtype: 'hidden',
								name: 'DAddress_Corpus'
							}, {
								xtype: 'hidden',
								name: 'DAddress_Flat'
							}, {
								xtype: 'hidden',
								name: 'DAddress_Address'
							}, { //Тип свидетельства; Серия; Номер;
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									width: 330,
									border: false,
									items: [new sw.Promed.SwReceptTypeCombo({
										allowBlank: false,
										fieldLabel: lang['tip_svidetelstva'],
										id: 'SvidType_id',
										listWidth: 200 + field_mod_1,
										anchor: '100%',
										value: 2, //default value
										tabIndex: TABINDEX_EREF + 1,
										validateOnBlur: true,// NGS
										listeners: {
											'select': function (combo, record, index) {
												if (record && record.get('ReceptType_id')) {
													win.onChangeReceptType(record.get('ReceptType_id'));
												}
											},
											'expand': function () {
												this.setStoreFilter();
											}
										},
										setStoreFilter: function () {
											this.getStore().clearFilter();
											this.getStore().filterBy(function (rec) {
												return rec.get('ReceptType_Code') != 3;
											});
										}
									})]
								}, { //
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										allowBlank: false,
										disabled: true,
										fieldLabel: lang['seriya'],
										maxLength: 20,
										id: 'DeathSvid_Ser',
										name: 'DeathSvid_Ser',
										tabIndex: TABINDEX_EREF + 2,
										anchor: '100%',
										value: '', //default value
										xtype: 'textfield'
									}]
								}, {
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										layout: 'column',
										border: false,
										autoHeight: true,
										items: [{
											layout: 'form',
											width: 300,
											border: false,
											items: [{
												allowBlank: false,
												fieldLabel: lang['nomer'],
												maxLength: 20,
												id: 'DeathSvid_Num',
												name: 'DeathSvid_Num',
												tabIndex: TABINDEX_EREF + 3,
												anchor: '100%',
												value: '', //default value
												xtype: 'textfield'
											}]
										}, {
											layout: 'form',
											border: false,
											style: 'float: none',
											items: [{
												text: '+',
												id: win.id + 'gennewnumber',
												xtype: 'button',
												handler: function() {
													win.generateNewNumber();
												}
											}]
										}]
									}]
								}]
							}, { //Дата выдачи; Вид свидетельства;
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['data_vyidachi'],
										format: 'd.m.Y',
										name: 'DeathSvid_GiveDate',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										tabIndex: TABINDEX_EREF + 4,
										width: 100,
										value: new Date(), //default value
										xtype: 'swdatefield',
										listeners: {
											'change': function (field, newValue, oldValue) {
												var base_form = this.findById('MedSvidDeathEditForm').getForm();

												if (!Ext.isEmpty(base_form.findField('DeathSvid_GiveDate').getValue())) {
													if (!win.isViewMode()) {
														// проверяем, есть ли нумераторы действующие на дату выдачи, у которых заполнена структура
														win.getLoadMask(lang['proverka_nalichiya_numeratorov_na_strukture_mo']).show();
														Ext.Ajax.request({
															url: '/?c=Numerator&m=checkNumeratorOnDateWithStructure',
															params: {
																onDate: base_form.findField('DeathSvid_GiveDate').getValue().format('d.m.Y'),
																NumeratorObject_SysName: 'DeathSvid'
															},
															callback: function (options, success, response) {
																win.getLoadMask().hide();
																if (success && response.responseText != '') {
																	var response_obj = Ext.util.JSON.decode(response.responseText);
																	if (response_obj.NumeratorExist) {
																		win.needLpuSectionForNumGeneration = true;
																	} else {
																		win.needLpuSectionForNumGeneration = false;
																	}
																}

																win.generateNewNumber(true);
															}
														});
													}
												}

												var lpu_section_id = base_form.findField('LpuSection_id').getValue();
												var med_personal_id = base_form.findField('MedPersonal_id').getValue();
												var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
												var death_svid_rcp_date = base_form.findField('DeathSvid_RcpDate').getValue();

												var section_filter_params = {
													// TO-DO: ну это тоже не правильно, надо сделать правильную фильтрацию по нескольким признакам, хотя можно сделать и isPolkaandStac ))
												};
												var medstafffact_filter_params = {
													// TO-DO: ну это тоже не правильно, надо сделать правильную фильтрацию по нескольким признакам, хотя можно сделать и isPolkaandStac ))
													allowDuplacateMSF: true
												};

												if (newValue) {
													section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
													medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
													if (death_svid_rcp_date == '' || Ext.util.Format.date(death_svid_rcp_date, 'd.m.Y') == Ext.util.Format.date(oldValue, 'd.m.Y')) {
													}
												}

												var user_med_staff_fact_id = null; //this.UserMedStaffFact_id;
												var user_lpu_section_id = null; //this.UserLpuSection_id;
												var user_med_staff_facts = (!isSuperAdmin() && !isMedStatUser()) ? getGlobalOptions().medstafffact : null; //this.UserMedStaffFacts;
												var user_lpu_sections = (!isSuperAdmin() && !isMedStatUser()) ? getGlobalOptions().lpusection : null; //this.UserLpuSections;

												// фильтр или на конкретное место работы или на список мест работы
												if (user_med_staff_fact_id && user_lpu_section_id && (this.action == 'add' || this.action == 'edit')) {
													section_filter_params.id = user_lpu_section_id;
													medstafffact_filter_params.id = user_med_staff_fact_id;
												} else if (user_med_staff_facts && user_lpu_sections && (this.action == 'add' || this.action == 'edit')) {
													section_filter_params.ids = user_lpu_sections;
													medstafffact_filter_params.ids = user_med_staff_facts;
												}
												
												base_form.findField('LpuSection_id').clearValue();
												base_form.findField('MedStaffFact_id').clearValue();

												if (!win.isViewMode()) {
													if ((section_filter_params.id && section_filter_params.id > 0) || (section_filter_params.ids && section_filter_params.ids.length > 0))
														setLpuSectionGlobalStoreFilter(section_filter_params);
													if ((medstafffact_filter_params.id && medstafffact_filter_params.id > 0) || (medstafffact_filter_params.ids && medstafffact_filter_params.ids.length > 0)) {
														setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
													} else {
														medstafffact_filter_params.id = null;
														medstafffact_filter_params.ids = null;
														setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
													}
												} else {
													// если просмотр, то подгружаем всех врачей
													setLpuSectionGlobalStoreFilter();
													setMedStaffFactGlobalStoreFilter();
												}

											
												base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
												base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
												
												var combo_medpersonal_cid = base_form.findField('MedPersonal_cid');
												if(combo_medpersonal_cid.isVisible()){
													base_form.findField('MedPersonal_cid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
													if(combo_medpersonal_cid.getValue() && combo_medpersonal_cid.findRecord('MedPersonal_id', combo_medpersonal_cid.getValue())){
														combo_medpersonal_cid.setValue(combo_medpersonal_cid.getValue());
													}
													combo_medpersonal_cid.fireEvent('change', combo_medpersonal_cid, combo_medpersonal_cid.getValue());
												}
												
												// *** NGS - START ***
												// Automatic filling for LpuSection and MedStaff fields
												if(['perm', 'vologda'].includes(getRegionNick())) {
													const globals = getGlobalOptions();
													const currentMedStaffFact_id =  sw.Promed.MedStaffFactByUser.last.MedStaffFact_id; //globals.medstafffact[0]; 
													const сurrentLpuSection_id = globals.CurLpuSection_id;
													const currentLpu_id = globals.lpu_id;
													
													var lpuSectionField = base_form.findField('LpuSection_id');
													
													// *** Lpu Section Part - START *** 
													if(currentMedStaffFact_id) {
														base_form.findField('MedStaffFact_id').setValue(currentMedStaffFact_id);
														// disable fields
														lpuSectionField.setDisabled(['perm'].includes(getRegionNick()) || false);
														base_form.findField('MedStaffFact_id').setDisabled(['perm'].includes(getRegionNick()) || false);
													} else if(сurrentLpuSection_id) {
														var index = lpuSectionField.getStore().findBy(function (record, id) {
															return (record.get('LpuSection_id') == сurrentLpuSection_id && (!currentLpu_id || record.get('Lpu_id') == currentLpu_id));
														});

														if(index > 0) {
															lpuSectionField.setValue(сurrentLpuSection_id);
															lpuSectionField.fireEvent('change', lpuSectionField, сurrentLpuSection_id);

															// disable button action
															lpuSectionField.setDisabled(['perm'].includes(getRegionNick()) || false);
														} else {
															// filter lpu sections of the current lpu
															currentLpu_id && lpuSectionField.getStore().clearFilter(true);
															currentLpu_id && lpuSectionField.getStore().filter('Lpu_id', currentLpu_id);
														}
													} else {
														sw.swMsg.alert(lang['oshibka'], "Отделение не определено. Введите его вручную.");

														// filter lpu sections of the current lpu
														currentLpu_id && lpuSectionField.getStore().clearFilter(true);
														currentLpu_id && lpuSectionField.getStore().filter('Lpu_id', currentLpu_id);
													}
													// *** Lpu Section Part - END ***
												}
												// *** NGS - END ***

												index = base_form.findField('MedStaffFact_id').getStore().findBy(function (record, id) {
													if ( !Ext.isEmpty(med_staff_fact_id) ) {
														return (record.get('MedStaffFact_id') == med_staff_fact_id);
													}
													else {
														return (record.get('LpuSection_id') == lpu_section_id && record.get('MedPersonal_id') == med_personal_id);
													}
												});

												if ( index >= 0 ) {
													med_staff_fact_id = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id');
												}

												if (base_form.findField('LpuSection_id').getStore().getById(lpu_section_id)) {
													base_form.findField('LpuSection_id').setValue(lpu_section_id);
													base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
												}

												if (base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id)) {
													base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
												}

												// если не нашли врача в локальном сторе, грузим с сервера
												if (win.isViewMode() && index < 0) {
													if ( !Ext.isEmpty(lpu_section_id) ) {
														base_form.findField('LpuSection_id').getStore().load({
															callback: function() {
																index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
																	return (rec.get('LpuSection_id') == lpu_section_id);
																});

																if ( index >= 0 ) {
																	base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
																}
															}.createDelegate(this),
															params: {
																Lpu_id: base_form.findField('Lpu_id').getValue(),
																LpuSection_id: lpu_section_id,
																mode: 'combo'
															}
														});
													}

													base_form.findField('MedStaffFact_id').getStore().load({
														callback: function() {
															index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
																if ( !Ext.isEmpty(med_staff_fact_id) ) {
																	return (rec.get('MedStaffFact_id') == med_staff_fact_id);
																}
																else {
																	return (rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id);
																}
															});

															if ( index >= 0 ) {
																base_form.findField('MedStaffFact_id').parentElementDisabled = true;
																base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
																base_form.findField('MedStaffFact_id').parentElementDisabled = false;
															}
														}.createDelegate(this),
														params: {
															ignoreDisableInDocParam: 1,
															mode: 'combo',
															Lpu_id: base_form.findField('Lpu_id').getValue(),
															LpuSection_id: lpu_section_id,
															MedPersonal_id: med_personal_id,
															MedStaffFact_id: med_staff_fact_id
														}
													});
												}

												/*
												 если форма отурыта на добавление и задано отделение и
												 место работы, то устанавливаем их не даем редактировать вообще
												 */
												if (!win.isViewMode() && user_med_staff_fact_id && user_lpu_section_id) {
													base_form.findField('LpuSection_id').setValue(user_lpu_section_id);
													base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), user_lpu_section_id);
													base_form.findField('LpuSection_id').disable();
													base_form.findField('MedStaffFact_id').setValue(user_med_staff_fact_id);
													base_form.findField('MedStaffFact_id').disable();

												} else
												/*
												 если форма открыта на добавление и задан список отделений и
												 мест работы, но он состоит из одного элемета,
												 то устанавливаем значение и не даем редактировать
												 */
												if (!win.isViewMode() && user_med_staff_facts && user_med_staff_facts.length == 1) {
													// список состоит из одного элемента (устанавливаем значение и не даем редактировать)
													base_form.findField('LpuSection_id').setValue(user_lpu_sections[0]);
													base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), user_lpu_sections[0]);
													base_form.findField('LpuSection_id').disable();
													base_form.findField('MedStaffFact_id').setValue(user_med_staff_facts[0]);
													base_form.findField('MedStaffFact_id').disable();
												}

												if (getRegionNick() === 'ufa') {
													// получение объёма по COVID-19 и фильтрация диагнозов
													win.hasCOVIDVolume = false;
													
													if (newValue) {
														Ext.Ajax.request({
															url: '/?c=MedSvid&m=checkCOVIDVolume',
															params: {
																onDate: Ext.util.Format.date(newValue, 'd.m.Y')
															},
															callback: function(options, success, response) {
																if (success && response.responseText !== '') {
																	var response_obj = Ext.util.JSON.decode(response.responseText);
																	win.hasCOVIDVolume = response_obj.hasCOVIDVolume;
																}

																win.filterDiagCombo();
															}
														});
													} else {
														win.filterDiagCombo();
													}
												}
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									width: 360,
									border: false,
									items: [{
										fieldLabel: lang['vid_svidetelstva'],
										comboSubject: 'DeathSvidType',
										allowBlank: false,
										disabled: false,
										tabIndex: TABINDEX_EREF + 5,
										anchor: '100%',
										xtype: 'swcommonsprcombo',
										listeners: {
											'select': function (combo, record, index) {
												if (record.get(combo.valueField)) {
													var dstype = record.get(combo.valueField);
													if (dstype == 3 || dstype == 4) {
														Ext.getCmp('DeathSvid_OldSer').enable();
														Ext.getCmp('DeathSvid_OldNum').enable();
														Ext.getCmp('DeathSvid_OldGiveDate').enable();
														if(!getRegionNick().inlist(['kz'])) {
															Ext.getCmp('DeathSvid_OldSer').allowBlank = false;
															Ext.getCmp('DeathSvid_OldNum').allowBlank = false;
															Ext.getCmp('DeathSvid_OldGiveDate').allowBlank = false;
														}
													} else {
														Ext.getCmp('DeathSvid_OldSer').disable();
														Ext.getCmp('DeathSvid_OldNum').disable();
														Ext.getCmp('DeathSvid_OldGiveDate').disable();
														if(!getRegionNick().inlist(['kz'])) {
															Ext.getCmp('DeathSvid_OldSer').allowBlank = true;
															Ext.getCmp('DeathSvid_OldNum').allowBlank = true;
															Ext.getCmp('DeathSvid_OldGiveDate').allowBlank = true;
														}
													}
												}
											}
										}
									}]
								}]
							}, {
								xtype: 'fieldset',
								labelWidth: 180,
								autoHeight: true,
								title: lang['predyiduschee_svidetelstvo'],
								width: 985,
								style: 'margin-left: 5px;',
								items: [{ //Пред. серия; Пред. номер; Пред. дата выдачи;
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',
										labelWidth: 125,
										border: false,
										items: [{
											disabled: true,
											fieldLabel: lang['seriya'],
											maxLength: 20,
											name: 'DeathSvid_OldSer',
											id: 'DeathSvid_OldSer',
											tabIndex: TABINDEX_EREF + 6,
											width: 200 + field_mod_1,
											maskRe: getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ? new RegExp("^[0-9]*$") : null,
											value: '', //default value
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										labelWidth: 135,
										border: false,
										items: [{
											disabled: true,
											fieldLabel: lang['nomer'],
											maxLength: 20,
											name: 'DeathSvid_OldNum',
											id: 'DeathSvid_OldNum',
											tabIndex: TABINDEX_EREF + 7,
											width: 200 + field_mod_1,
											value: '', //default value
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										labelWidth: 135,
										border: false,
										items: [{
											disabled: true,
											fieldLabel: lang['data_vyidachi'],
											format: 'd.m.Y',
											name: 'DeathSvid_OldGiveDate',
											listeners: {
												'change': function(combo, newValue, oldValue) {

													// выпилено по https://redmine.swan.perm.ru/issues/105724

													//if (getRegionNick() == 'ekb') {
													//	var base_form = win.findById('MedSvidDeathEditForm').getForm();
                                                    //
													//	if (!Ext.isEmpty(newValue)) {
													//		base_form.findField('DeathSvid_GiveDate').setMinValue(newValue);
													//	} else {
													//		var Year = getGlobalOptions().date.substr(6, 4);
													//		//base_form.findField('DeathSvid_GiveDate').setMinValue(Date.parseDate('01.01.' + Year, 'd.m.Y'));
													//	}
													//}
												}
											},
											id: 'DeathSvid_OldGiveDate',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											tabIndex: TABINDEX_EREF + 8,
											width: 100,
											value: '', //default value
											xtype: 'swdatefield'
										}]
									}]
								}]
							}, /*{
								name: 'DeathSvid_BirthDateStr',
								fieldLabel: lang['data_rojdeniya'],
								plugins: [new Ext.ux.InputTextMask('PP.PP.PPPP', false)],
								width: 100,
								xtype: 'textfield'
							},*/
                                { //Дата, время рождения;
                                    layout: 'column',
                                    border: false,
                                    items: [{
                                        layout: 'form',
                                        border: false,
                                        labelWidth: 140,
                                        items: [{
                                            name: 'DeathSvid_BirthDateStr',
                                            fieldLabel: lang['data_rojdeniya'],
                                            plugins: [new Ext.ux.InputTextMask('PP.PP.PPPP', false)],
                                            width: 100,
                                            xtype: 'textfield'
                                        }]
                                    },
                                        {
                                            layout: 'form',
                                            width: 400,
                                            style: 'padding-left: 20px;',
                                            border: false,
                                            items: [{
                                                hideLabel: true,
                                                name: 'DeathSvid_isBirthDate',
                                                listeners: {
                                                    'check': function(checkbox, value) {
                                                        var base_form = this.findById('MedSvidDeathEditForm').getForm();
                                                        var birthdt = Ext.getCmp('MSDEF_PersonInformationFrame').getFieldValue("Person_Birthday");

                                                        if(base_form.findField('DeathSvid_isBirthDate').checked)
                                                        {
                                                            base_form.findField('DeathSvid_BirthDateStr').enable();
                                                            base_form.findField('DeathSvid_BirthDateStr').setValue('');
                                                        }
                                                        else if ( !Ext.isEmpty(birthdt) && typeof birthdt == 'object' )
                                                        {
                                                            base_form.findField('DeathSvid_BirthDateStr').disable();
                                                            base_form.findField('DeathSvid_BirthDateStr').setValue(birthdt.format('d.m.Y'));
                                                        }
                                                    }.createDelegate(this)
                                                },
                                                boxLabel: lang['nepolnaya_neizvestnaya_data_rojdeniya'],
                                                tabIndex: TABINDEX_EREF + 14,
                                                anchor: '100%',
                                                xtype: 'checkbox'
                                            }]
                                        }]
                                },
                                { //Дата, время смерти;
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										disabled: false,
										fieldLabel: lang['data_vremya_smerti'],
										format: 'd.m.Y',
										allowBlank: false,
										id: 'DeathSvid_DeathDate_Date',
										name: 'DeathSvid_DeathDate_Date',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										tabIndex: TABINDEX_EREF + 9,
										width: 100,
										value: '', //default value
										xtype: 'swdatefield',
										listeners: {
											'select': function (th, dt) {
												Ext.getCmp('MedSvidDeathEditWindow').keepMotherFields(dt);
											},
											'change': function (combo, newValue, oldValue) {
												if (!blockedDateAfterPersonDeath('personpanelid', 'MSDEF_PersonInformationFrame', combo, newValue, oldValue))
													Ext.getCmp('MedSvidDeathEditWindow').keepMotherFields(newValue);

												var base_form = this.findById('MedSvidDeathEditForm').getForm();

												win.onPersonDeathAgeChange();
												if(newValue){
													base_form.findField('Diag_oid').setFilterByDate(newValue);
													base_form.findField('Diag_eid').setFilterByDate(newValue);
													base_form.findField('Diag_mid').setFilterByDate(newValue);
													base_form.findField('Diag_tid').setFilterByDate(newValue);
													base_form.findField('Diag_iid').setFilterByDate(newValue);
												}
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 1,
									items: [{
										disabled: false,
										labelSeparator: '',
										format: 'H:i',
										name: 'DeathSvid_DeathDate_Time',
										//onTriggerClick: Ext.emptyFn,
										plugins: [new Ext.ux.InputTextMask('99:99', true)],
										tabIndex: TABINDEX_EREF + 10,
										validateOnBlur: false,
										width: 60,
										value: '', //default value
										listeners: {
											'keydown': function (inp, e) {
												if (e.getKey() == Ext.EventObject.F4) {
													e.stopEvent();
													inp.onTriggerClick();
												}
											}
										},
										xtype: 'swtimefield'
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 140,
									items: [{
										name: 'DeathSvid_DeathDateStr',
										fieldLabel: lang['neutoch_data_smerti'],
										plugins: [new Ext.ux.InputTextMask('PP.99.9999', true)],// #145432 ввод Х только для дней
										width: 100,
										xtype: 'textfield',
										listeners: {
											'change': function(field, newValue) {
												var base_form = this.findById('MedSvidDeathEditForm').getForm();
												var valid = true;
												if(getRegionNick().inlist(['ufa'])){// #131621 только в Башкирии нужен ввод xx.11.1111
													valid=(/^([хx]{2}|\d{2})\.\d{2}\.\d{4}$/i.test(newValue));// #131621 добавил x и регистронезависимость, #145432 убрал модификатор u
												}
												else{
													valid=(/^\d{2}\.\d{2}\.\d{4}$/i.test(newValue));
												}
												var deathDataField = base_form.findField('DeathSvid_DeathDate_Date');
												var deathDataTimeField = base_form.findField('DeathSvid_DeathDate_Time');
												deathDataField.setAllowBlank(valid);
												if(valid){
													deathDataField.setValue(null);
												}else{
													field.reset();
												}
												deathDataField.fireEvent('change', deathDataField, deathDataField.getValue());
												if(getRegionNick().inlist(['ufa'])){// #131621 для Уфы блокировать
													deathDataField.setDisabled(valid);
													deathDataTimeField.setDisabled(valid);
												}
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 10,
									items: [{
										name: 'DeathSvid_IsUnknownDeathDate',
										labelSeparator: '',
										boxLabel: lang['data_smerti_neizvestna'],
										listeners: {
											'check': function(checkbox, value) {
												var base_form = this.findById('MedSvidDeathEditForm').getForm();

												base_form.findField('DeathSvid_DeathDate_Date').setValue(null);
												base_form.findField('DeathSvid_DeathDate_Date').fireEvent('change', base_form.findField('DeathSvid_DeathDate_Date'), base_form.findField('DeathSvid_DeathDate_Date').getValue());
												base_form.findField('DeathSvid_DeathDateStr').setValue(null);

												base_form.findField('DeathSvid_DeathDate_Date').setDisabled(value);
												base_form.findField('DeathSvid_DeathDate_Date').setAllowBlank(value);
												base_form.findField('DeathSvid_DeathDateStr').setDisabled(value);

												if (value) base_form.findField('DeathSvid_IsNoDeathTime').setValue(1);
												base_form.findField('DeathSvid_IsNoDeathTime').setDisabled(value);

												win.onCheckIsUnknownDeathDate();
											}.createDelegate(this)
										},
										xtype: 'checkbox'
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 10,
									items: [{
										name: 'DeathSvid_IsNoDeathTime',
										labelSeparator: '',
										boxLabel: lang['vremya_smerti_neizvestno'],
										listeners: {
											'check': function(checkbox, value) {
												var base_form = this.findById('MedSvidDeathEditForm').getForm();
												base_form.findField('DeathSvid_DeathDate_Time').setValue(null);
												base_form.findField('DeathSvid_DeathDate_Time').setDisabled(value);
											}.createDelegate(this)
										},
										xtype: 'checkbox'
									}]
								}]
							}, {
								name: 'Person_hid',
								xtype: 'hidden'
							}, {
								name: 'OrgHeadPost_id',
								xtype: 'hidden'
							}, {
								allowBlank: false,
								hiddenName: 'LpuSection_id',
								id: 'MSDEF_LpuSectionCombo',
								changeDisabled: false,
								lastQuery: '',
								listWidth: 650,
								linkedElements: [
									'MSDEF_MedPersonalCombo'
								],
								listeners: {
									'change': function (combo, newValue, oldValue) {
										var base_form = this.findById('MedSvidDeathEditForm').getForm();
										// NGS
										// *** Med Staff Fact - PERM + VOLOGDA - START *** 
										if(['perm', 'vologda'].includes(getRegionNick())) {
											// const globals = getGlobalOptions();
											const currentMedStaffFact_id = sw.Promed.MedStaffFactByUser.last.MedStaffFact_id; //globals.medstafffact[0];
											
											var lpuSectionFieldSelection = combo.getStore().getById(combo.getValue()).data;
											var medStaffFactField = base_form.findField('MedStaffFact_id');

											var index = medStaffFactField.getStore().findBy(function (record, id) {
												return (record.get('MedStaffFact_id') == currentMedStaffFact_id && record.get('LpuSection_id') == lpuSectionFieldSelection.LpuSection_id);
											});

											if(index > 0) {												
												// yes, dont blame on me
												setTimeout(() => {
													medStaffFactField.setValue(currentMedStaffFact_id);
													medStaffFactField.fireEvent('change', medStaffFactField, currentMedStaffFact_id);
												}, 1000);
												
												medStaffFactField.setDisabled(['perm'].includes(getRegionNick()) || false);
											}
											// *** Med Staff Fact - PERM + VOLOGDA - END *** 
										} else {
											if (win.needLpuSectionForNumGeneration) {
												this.generateNewNumber(true);
											}
											
											var msf_combo = base_form.findField('MedStaffFact_id');
											var msf = msf_combo.getValue();
											msf_combo.getStore().clearFilter();
											var msf_index = msf_combo.getStore().findBy(function(rec){
												return (rec.get('LpuSection_id') == newValue);
											});
											if(msf_index != -1){
												var msf_record = msf_combo.getStore().getAt(msf_index);
	
												if(msf != msf_record.get('MedStaffFact_id')) { 
													msf_combo.setValue(msf_record.get('MedStaffFact_id'));
													msf_combo.setRawValue(msf_record.get('MedPersonal_TabCode') + '. '+msf_record.get('MedPersonal_Fio'));
												}
											}

											//для пензы - выводить всех врачей отделения + если регион Пермь или Волога и значение 
											msf_combo.ignoreFilter = getRegionNick() == 'penza';
											
											base_form.findField('MedStaffFact_id').fireEvent('select',base_form.findField('MedStaffFact_id'),base_form.findField('MedStaffFact_id').getValue());
											base_form.findField('MedStaffFact_id').fireEvent('change',base_form.findField('MedStaffFact_id'),base_form.findField('MedStaffFact_id').getValue());
											
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_EREF + 8,
								width: 500,
								xtype: 'swlpusectionglobalcombo'
							}, {
								allowBlank: false,
								hiddenName: 'MedStaffFact_id',
								id: 'MSDEF_MedPersonalCombo',
								lastQuery: '',
								listWidth: 650,
								parentElementId: 'MSDEF_LpuSectionCombo',
								listeners: {
									'change': function (combo, newValue, oldValue) {
										if (win.needLpuSectionForNumGeneration) {
											this.generateNewNumber(true);
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_EREF + 9,
								width: 500,
								value: null,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								allowBlank: getRegionNick() == 'kz',
								fieldLabel: lang['rukovoditel'],
								hiddenName: 'OrgHead_id',
								lastQuery: '',
								xtype: 'orgheadcombo',
								width: 500,
								listeners: {
									'select': function(combo, record) {
										var base_form = this.findById('MedSvidDeathEditForm').getForm();

										base_form.findField('Person_hid').setValue(combo.getFieldValue('Person_id'));
										base_form.findField('OrgHeadPost_id').setValue(combo.getFieldValue('OrgHeadPost_id'));
									}.createDelegate(this)
								},
								onLoadStore: function (store) {
									// https://redmine.swan.perm.ru/issues/37688 выключаю фильтр
									/*store.clearFilter();
									 store.filterBy(function(rec){
									 return (rec.get('OrgHeadPost_id').inlist([1,4]));
									 });*/
								}
							}, {
								xtype: 'fieldset',
								labelWidth: 125,
								autoHeight: true,
								title: lang['dlya_detey_umershih_v_vozraste_do_1_goda'],
								width: 985,
								style: 'margin-left: 5px;',
								items: [{ //ФИО матери;
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',
										border: false,
										items: [{
											editable: false,
											fieldLabel: lang['fio_materi'],
											name: 'Person_mid',
											hiddenName: 'Person_mid',
											tabIndex: TABINDEX_EREF + 12,
											width: 500,
											xtype: 'swpersoncombo',
											onTrigger1Click: function () {
												if (this.disabled) return false;
												var ownerWindow = Ext.getCmp('PersonEditWindow');
												var combo = this;
												getWnd('swPersonSearchWindow').show({
													onSelect: function (personData) {
														if (personData.Person_id > 0) {
															combo.getStore().loadData([{
																Person_id: personData.Person_id,
																Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
															}]);
															if (personData.PersonBirthDay_BirthDay) {
																var nowdate = new Date();
																var birdate = personData.PersonBirthDay_BirthDay;
																Ext.getCmp('Mother_Age').setValue(nowdate.format('Y') - birdate.format('Y') - (((birdate.format('md') * 1) - (nowdate.format('md') * 1)) > 0 ? 1 : 0));
																Ext.getCmp('Mother_BirthDay').setValue(birdate.format('d.m.Y'));
																win.onPersonDeathAgeChange();
															}
															combo.setValue(personData.Person_id);
															combo.collapse();
															combo.focus(true, 500);
															combo.fireEvent('change', combo);
														}
														getWnd('swPersonSearchWindow').hide();
													},
													onClose: function () {
														combo.focus(true, 500)
													}
												});
											},
											enableKeyEvents: true,
											listeners: {
												'change': function (combo) {
												},
												'keydown': function (inp, e) {
													if (e.F4 == e.getKey()) {
														if (e.browserEvent.stopPropagation)
															e.browserEvent.stopPropagation();
														else
															e.browserEvent.cancelBubble = true;
														if (e.browserEvent.preventDefault)
															e.browserEvent.preventDefault();
														else
															e.browserEvent.returnValue = false;
														e.browserEvent.returnValue = false;
														e.returnValue = false;
														if (Ext.isIE) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														inp.onTrigger1Click();
														return false;
													}
												},
												'keyup': function (inp, e) {
													if (e.F4 == e.getKey()) {
														if (e.browserEvent.stopPropagation)
															e.browserEvent.stopPropagation();
														else
															e.browserEvent.cancelBubble = true;
														if (e.browserEvent.preventDefault)
															e.browserEvent.preventDefault();
														else
															e.browserEvent.returnValue = false;
														e.browserEvent.returnValue = false;
														e.returnValue = false;
														if (Ext.isIE) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														return false;
													}
												}
											}
										}]
									}]
								}, { //Место рождения;
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',
										border: false,
										items: [
											{
												xtype: 'hidden',
												name: 'BAddress_Zip'
											}, {
												xtype: 'hidden',
												name: 'BKLCountry_id'
											}, {
												xtype: 'hidden',
												name: 'BKLRGN_id'
											}, {
												xtype: 'hidden',
												name: 'BKLSubRGN_id'
											}, {
												xtype: 'hidden',
												name: 'BKLCity_id'
											}, {
												xtype: 'hidden',
												name: 'BKLTown_id'
											}, {
												xtype: 'hidden',
												name: 'BKLStreet_id'
											}, {
												xtype: 'hidden',
												name: 'BAddress_House'
											}, {
												xtype: 'hidden',
												name: 'BAddress_Corpus'
											}, {
												xtype: 'hidden',
												name: 'BAddress_Flat'
											}, {
												xtype: 'hidden',
												name: 'BAddress_Address'
											},
											new sw.Promed.TripleTriggerField({
												name: 'BAddress_AddressText',
												readOnly: true,
												width: 815,
												trigger1Class: 'x-form-search-trigger',
												trigger2Class: 'x-form-equil-trigger',
												trigger3Class: 'x-form-clear-trigger',
												fieldLabel: lang['mesto_rojdeniya'],
												tabIndex: TABINDEX_EREF + 13,
												enableKeyEvents: true,
												listeners: {
													'keydown': function (inp, e) {
														if (e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey)) {
															if (e.F4 == e.getKey())
																inp.onTrigger1Click();
															if (e.F2 == e.getKey())
																inp.onTrigger2Click();
															if (e.DELETE == e.getKey() && e.altKey)
																inp.onTrigger3Click();
															if (e.browserEvent.stopPropagation)
																e.browserEvent.stopPropagation();
															else
																e.browserEvent.cancelBubble = true;
															if (e.browserEvent.preventDefault)
																e.browserEvent.preventDefault();
															else
																e.browserEvent.returnValue = false;
															e.browserEvent.returnValue = false;
															e.returnValue = false;
															if (Ext.isIE) {
																e.browserEvent.keyCode = 0;
																e.browserEvent.which = 0;
															}
															return false;
														}
													},
													'keyup': function (inp, e) {
														if (e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey)) {
															if (e.browserEvent.stopPropagation)
																e.browserEvent.stopPropagation();
															else
																e.browserEvent.cancelBubble = true;
															if (e.browserEvent.preventDefault)
																e.browserEvent.preventDefault();
															else
																e.browserEvent.returnValue = false;
															e.browserEvent.returnValue = false;
															e.returnValue = false;
															if (Ext.isIE) {
																e.browserEvent.keyCode = 0;
																e.browserEvent.which = 0;
															}
															return false;
														}
													}
												},
												onTrigger3Click: function () {
													if (this.disabled) return false;
													var base_form = win.findById('MedSvidDeathEditForm').getForm();
													base_form.findField('BAddress_Zip').setValue('');
													base_form.findField('BKLCountry_id').setValue('');
													base_form.findField('BKLRGN_id').setValue('');
													base_form.findField('BKLSubRGN_id').setValue('');
													base_form.findField('BKLCity_id').setValue('');
													base_form.findField('BKLTown_id').setValue('');
													base_form.findField('BKLStreet_id').setValue('');
													base_form.findField('BAddress_House').setValue('');
													base_form.findField('BAddress_Corpus').setValue('');
													base_form.findField('BAddress_Flat').setValue('');
													base_form.findField('BAddress_Address').setValue('');
													base_form.findField('BAddress_AddressText').setValue('');
												},
												onTrigger2Click: function () { //копирование адреса регистрации
													if (this.disabled) return false;
													var base_form = win.findById('MedSvidDeathEditForm').getForm();
													win.getLoadMask(lang['poluchenie_adresa_registratsii_patsienta']).show();
													Ext.Ajax.request({
														callback: function (options, success, response) {
															win.getLoadMask().hide();
															if (success && response.responseText != '') {
																var response_obj = Ext.util.JSON.decode(response.responseText);
																if (response_obj.AddressFound) {

																	base_form.findField('BAddress_Zip').setValue(response_obj.BAddress_Zip);
																	base_form.findField('BKLCountry_id').setValue(response_obj.BKLCountry_id);
																	base_form.findField('BKLRGN_id').setValue(response_obj.BKLRGN_id);
																	base_form.findField('BKLSubRGN_id').setValue(response_obj.BKLSubRGN_id);
																	base_form.findField('BKLCity_id').setValue(response_obj.BKLCity_id);
																	base_form.findField('BKLTown_id').setValue(response_obj.BKLTown_id);
																	base_form.findField('BKLStreet_id').setValue(response_obj.BKLStreet_id);
																	base_form.findField('BAddress_House').setValue(response_obj.BAddress_House);
																	base_form.findField('BAddress_Corpus').setValue(response_obj.BAddress_Corpus);
																	base_form.findField('BAddress_Flat').setValue(response_obj.BAddress_Flat);
																	base_form.findField('BAddress_Address').setValue(response_obj.BAddress_Address);
																	base_form.findField('BAddress_AddressText').setValue(response_obj.BAddress_Address);
																}
															}
														},
														params: {
															Person_id: base_form.findField('Person_id').getValue()
														},
														url: '/?c=MedSvid&m=getPacientBAddress'
													});
												},
												onTrigger1Click: function () {
													if (this.disabled) return false;
													var base_form = win.findById('MedSvidDeathEditForm').getForm();
													getWnd('swAddressEditWindow').show({
														fields: {
															Address_ZipEdit: base_form.findField('BAddress_Zip').getValue(),
															KLCountry_idEdit: base_form.findField('BKLCountry_id').getValue(),
															KLRgn_idEdit: base_form.findField('BKLRGN_id').getValue(),
															KLSubRGN_idEdit: base_form.findField('BKLSubRGN_id').getValue(),
															KLCity_idEdit: base_form.findField('BKLCity_id').getValue(),
															KLTown_idEdit: base_form.findField('BKLTown_id').getValue(),
															KLStreet_idEdit: base_form.findField('BKLStreet_id').getValue(),
															Address_HouseEdit: base_form.findField('BAddress_House').getValue(),
															Address_CorpusEdit: base_form.findField('BAddress_Corpus').getValue(),
															Address_FlatEdit: base_form.findField('BAddress_Flat').getValue(),
															Address_AddressEdit: base_form.findField('BAddress_Address').getValue()
														},
														callback: function (values) {
															base_form.findField('BAddress_Zip').setValue(values.Address_ZipEdit);
															base_form.findField('BKLCountry_id').setValue(values.KLCountry_idEdit);
															base_form.findField('BKLRGN_id').setValue(values.KLRgn_idEdit);
															base_form.findField('BKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
															base_form.findField('BKLCity_id').setValue(values.KLCity_idEdit);
															base_form.findField('BKLTown_id').setValue(values.KLTown_idEdit);
															base_form.findField('BKLStreet_id').setValue(values.KLStreet_idEdit);
															base_form.findField('BAddress_House').setValue(values.Address_HouseEdit);
															base_form.findField('BAddress_Corpus').setValue(values.Address_CorpusEdit);
															base_form.findField('BAddress_Flat').setValue(values.Address_FlatEdit);
															base_form.findField('BAddress_Address').setValue(values.Address_AddressEdit);
															base_form.findField('BAddress_AddressText').setValue(values.Address_AddressEdit);
															base_form.findField('BAddress_AddressText').focus(true, 500);
														},
														onClose: function () {
															base_form.findField('BAddress_AddressText').focus(true, 500);
														}
													})
												}
											})
										]
									}]
								}, { //Доношенность; Масса (г); Который ребенок;
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',
										border: false,
										items: [{
											fieldLabel: lang['donoshennost'],
											comboSubject: 'ChildTermType',
											maxLength: 100,
											tabIndex: TABINDEX_EREF + 14,
											width: 200 + field_mod_1,
											value: null, //default value
											xtype: 'swcommonsprcombo'
										}]
									}, {
										layout: 'form',
										border: false,
										items: [{
											maxLength: 100,
											fieldLabel: lang['massa_g'],
											name: 'DeathSvid_Mass',
											id: 'DeathSvid_Mass',
											tabIndex: TABINDEX_EREF + 15,
											width: 200 + field_mod_1,
											value: '', //default value
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										border: false,
										items: [{
											fieldLabel: lang['kotoryiy_rebenok'],
											maxLength: 100,
											name: 'DeathSvid_ChildCount',
											id: 'DeathSvid_ChildCount',
											tabIndex: TABINDEX_EREF + 16,
											width: 200 + field_mod_1,
											value: '', //default value
											xtype: 'textfield'
										}]
									}]
								}, { //Месяц жизни; День жизни; Возраст матери;
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',
										border: false,
										items: [{
											fieldLabel: lang['mesyats_jizni'],
											maxLength: 100,
											name: 'DeathSvid_Month',
											id: 'DeathSvid_Month',
											tabIndex: TABINDEX_EREF + 17,
											width: 200 + field_mod_1,
											value: '', //default value
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										border: false,
										items: [{
											fieldLabel: lang['den_jizni'],
											maxLength: 100,
											name: 'DeathSvid_Day',
											id: 'DeathSvid_Day',
											tabIndex: TABINDEX_EREF + 18,
											width: 200 + field_mod_1,
											value: '', //default value
											xtype: 'textfield'
										}]
									}]
								}, { //Д/р матери;
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',
										border: false,
										items: [{
											fieldLabel: lang['vozrast_materi'],
											maxLength: 100,
											id: 'Mother_Age',
											name: 'Mother_Age',
											tabIndex: TABINDEX_EREF + 19,
											width: 200 + field_mod_1,
											value: '', //default value
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										border: false,
										items: [{
											fieldLabel: lang['d_r_materi'],
											format: 'd.m.Y',
											id: 'Mother_BirthDay',
											listeners: {
												'change': function(field, newValue) {
													win.onPersonDeathAgeChange();
												}
											},
											name: 'Mother_BirthDay',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											tabIndex: TABINDEX_EREF + 20,
											width: 100,
											value: '', //default value
											xtype: 'swdatefield'
										}]
									}]
								}]
							}, { //Образование; Семейное положение;
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									width: 660,
									border: false,
									items: [{
										allowBlank: !getRegionNick().inlist( ['msk', 'vologda', 'ekb'] ),
										disabled: false,
										fieldLabel: lang['zanyatost'],
										comboSubject: 'DeathEmployment',
										tabIndex: TABINDEX_EREF + 23,
										typeCode: 'int',
										anchor: '100%',
										xtype: 'swcommonsprcombo'
									}]
								}, {
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										allowBlank: !getRegionNick().inlist( ['msk', 'vologda', 'ekb'] ),
										disabled: false,
										fieldLabel: lang['obrazovanie'],
										comboSubject: 'DeathEducation',
										tabIndex: TABINDEX_EREF + 24,
										anchor: '100%',
										xtype: 'swcommonsprcombo'
									}]
								}]
							}, {
								layout: 'form',
								border: false,
								items: [{
									allowBlank: false,
									disabled: false,
									fieldLabel: lang['smert_nastupila'],
									loadParams: {params: {where: 'where DeathPlace_Code <= 5'}},
									comboSubject: 'DeathPlace',
									tabIndex: TABINDEX_EREF + 22,
									width: 200,
									xtype: 'swcommonsprcombo'
								}]
							}, {
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									width: 760,
									border: false,
									items: [new sw.Promed.TripleTriggerField({
										//xtype: 'trigger',
										allowBlank: false,
										name: 'DAddress_AddressText',
										readOnly: true,
										anchor: '100%',
										trigger1Class: 'x-form-search-trigger',
										trigger2Class: 'x-form-equil-trigger',
										trigger3Class: 'x-form-clear-trigger',
										fieldLabel: lang['mesto_smerti'],
										tabIndex: TABINDEX_EREF + 21,
										enableKeyEvents: true,
										listeners: {
											'keydown': function (inp, e) {
												if (e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey)) {
													if (e.F4 == e.getKey())
														inp.onTrigger1Click();
													if (e.F2 == e.getKey())
														inp.onTrigger2Click();
													if (e.DELETE == e.getKey() && e.altKey)
														inp.onTrigger3Click();
													if (e.browserEvent.stopPropagation)
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;
													if (e.browserEvent.preventDefault)
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;
													e.browserEvent.returnValue = false;
													e.returnValue = false;
													if (Ext.isIE) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											},
											'keyup': function (inp, e) {
												if (e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey)) {
													if (e.browserEvent.stopPropagation)
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;
													if (e.browserEvent.preventDefault)
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;
													e.browserEvent.returnValue = false;
													e.returnValue = false;
													if (Ext.isIE) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											}
										},
										onTrigger3Click: function () {
											if (this.disabled) return false;
											var base_form = win.findById('MedSvidDeathEditForm').getForm();
											base_form.findField('DAddress_Zip').setValue('');
											base_form.findField('DKLCountry_id').setValue('');
											base_form.findField('DKLRGN_id').setValue('');
											base_form.findField('DKLSubRGN_id').setValue('');
											base_form.findField('DKLCity_id').setValue('');
											base_form.findField('DKLTown_id').setValue('');
											base_form.findField('DKLStreet_id').setValue('');
											base_form.findField('DAddress_House').setValue('');
											base_form.findField('DAddress_Corpus').setValue('');
											base_form.findField('DAddress_Flat').setValue('');
											base_form.findField('DAddress_Address').setValue('');
											base_form.findField('DAddress_AddressText').setValue('');
										},
										onTrigger2Click: function () { //копирование адреса рождения
											if (this.disabled) return false;
											var base_form = win.findById('MedSvidDeathEditForm').getForm();

											if (base_form.findField('DeathPlace_id').getValue() == 2) {
												// если «Смерть наступила» = «Дома», подставлять в поле «Место смерти» адрес регистрации пациента
												win.getLoadMask(lang['poluchenie_adresa_registratsii_patsienta']).show();
												Ext.Ajax.request({
													callback: function (options, success, response) {
														win.getLoadMask().hide();
														if (success && response.responseText != '') {
															var response_obj = Ext.util.JSON.decode(response.responseText);
															if (response_obj.AddressFound) {
																base_form.findField('DAddress_Zip').setValue(response_obj.BAddress_Zip);
																base_form.findField('DKLCountry_id').setValue(response_obj.BKLCountry_id);
																base_form.findField('DKLRGN_id').setValue(response_obj.BKLRGN_id);
																base_form.findField('DKLSubRGN_id').setValue(response_obj.BKLSubRGN_id);
																base_form.findField('DKLCity_id').setValue(response_obj.BKLCity_id);
																base_form.findField('DKLTown_id').setValue(response_obj.BKLTown_id);
																base_form.findField('DKLStreet_id').setValue(response_obj.BKLStreet_id);
																base_form.findField('DAddress_House').setValue(response_obj.BAddress_House);
																base_form.findField('DAddress_Corpus').setValue(response_obj.BAddress_Corpus);
																base_form.findField('DAddress_Flat').setValue(response_obj.BAddress_Flat);
																base_form.findField('DAddress_Address').setValue(response_obj.BAddress_Address);
																base_form.findField('DAddress_AddressText').setValue(response_obj.BAddress_Address);
															}
														}
													},
													params: {
														Person_id: base_form.findField('Person_id').getValue()
													},
													url: '/?c=MedSvid&m=getPacientUAddress'
												});
											} else if (base_form.findField('DeathPlace_id').getValue() && base_form.findField('DeathPlace_id').getValue().inlist([1,6,7,8,9])) {
												// Если одно из значений «В стационаре», «В операционной», «В реанимации», «В приемном» - адрес группы отделений (или подразделения), в котором было КВС пациента с исходом смерть.
												win.getLoadMask(lang['poluchenie_adresa_gruppyi_otdeleniy_v_kotorom_byilo_kvs_patsienta_s_ishodom_smert']).show();
												Ext.Ajax.request({
													callback: function (options, success, response) {
														win.getLoadMask().hide();
														if (success && response.responseText != '') {
															var response_obj = Ext.util.JSON.decode(response.responseText);
															if (response_obj.AddressFound) {
																base_form.findField('DAddress_Zip').setValue(response_obj.BAddress_Zip);
																base_form.findField('DKLCountry_id').setValue(response_obj.BKLCountry_id);
																base_form.findField('DKLRGN_id').setValue(response_obj.BKLRGN_id);
																base_form.findField('DKLSubRGN_id').setValue(response_obj.BKLSubRGN_id);
																base_form.findField('DKLCity_id').setValue(response_obj.BKLCity_id);
																base_form.findField('DKLTown_id').setValue(response_obj.BKLTown_id);
																base_form.findField('DKLStreet_id').setValue(response_obj.BKLStreet_id);
																base_form.findField('DAddress_House').setValue(response_obj.BAddress_House);
																base_form.findField('DAddress_Corpus').setValue(response_obj.BAddress_Corpus);
																base_form.findField('DAddress_Flat').setValue(response_obj.BAddress_Flat);
																base_form.findField('DAddress_Address').setValue(response_obj.BAddress_Address);
																base_form.findField('DAddress_AddressText').setValue(response_obj.BAddress_Address);
															}
														}
													},
													params: {
														Person_id: base_form.findField('Person_id').getValue()
													},
													url: '/?c=MedSvid&m=getPacientDeathAddress'
												});
											}
										},
										onTrigger1Click: function () {
											if (this.disabled) return false;
											var base_form = win.findById('MedSvidDeathEditForm').getForm();
											getWnd('swAddressEditWindow').show({
												deathSvid: true,
												fields: {
													Address_ZipEdit: base_form.findField('DAddress_Zip').getValue(),
													KLCountry_idEdit: base_form.findField('DKLCountry_id').getValue(),
													KLRgn_idEdit: base_form.findField('DKLRGN_id').getValue(),
													KLSubRGN_idEdit: base_form.findField('DKLSubRGN_id').getValue(),
													KLCity_idEdit: base_form.findField('DKLCity_id').getValue(),
													KLTown_idEdit: base_form.findField('DKLTown_id').getValue(),
													KLStreet_idEdit: base_form.findField('DKLStreet_id').getValue(),
													Address_HouseEdit: base_form.findField('DAddress_House').getValue(),
													Address_CorpusEdit: base_form.findField('DAddress_Corpus').getValue(),
													Address_FlatEdit: base_form.findField('DAddress_Flat').getValue(),
													Address_AddressEdit: base_form.findField('DAddress_Address').getValue()
												},
												callback: function (values) {
													base_form.findField('DAddress_Zip').setValue(values.Address_ZipEdit);
													base_form.findField('DKLCountry_id').setValue(values.KLCountry_idEdit);
													base_form.findField('DKLRGN_id').setValue(values.KLRgn_idEdit);
													base_form.findField('DKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
													base_form.findField('DKLCity_id').setValue(values.KLCity_idEdit);
													base_form.findField('DKLTown_id').setValue(values.KLTown_idEdit);
													base_form.findField('DKLStreet_id').setValue(values.KLStreet_idEdit);
													base_form.findField('DAddress_House').setValue(values.Address_HouseEdit);
													base_form.findField('DAddress_Corpus').setValue(values.Address_CorpusEdit);
													base_form.findField('DAddress_Flat').setValue(values.Address_FlatEdit);
													base_form.findField('DAddress_Address').setValue(values.Address_AddressEdit);
													base_form.findField('DAddress_AddressText').setValue(values.Address_AddressEdit);
													base_form.findField('DAddress_AddressText').focus(true, 500);
												},
												onClose: function () {
													base_form.findField('DAddress_AddressText').focus(true, 500);
												}
											})
										}
									})]
								}, {
									layout: 'form',
									width: 210,
									style: 'padding-left: 20px;',
									border: false,
									items: [{
										hideLabel: true,
										name: 'DeathSvid_IsNoPlace',
										listeners: {
											'check': function() {
												win.checkIsNoPlace();
											}
										},
										boxLabel: lang['neizvestno'],
										tabIndex: TABINDEX_EREF + 24,
										anchor: '100%',
										xtype: 'checkbox'
									}]
								}]
							}, {
								layout: 'form',
								border: false,
								items: [{ //Дата, время смерти;
                                    allowBlank: !getRegionNick().inlist( ['msk', 'vologda'] ),
									comboSubject: 'DeathFamilyStatus',
									fieldLabel: lang['semeynoe_polojenie'],
									hiddenName: 'DeathFamilyStatus_id',
									listWidth: 400,
									tabIndex: TABINDEX_EREF + 26,
									width: 350,
									xtype: 'swcommonsprcombo'
								}]
							}, { //Причина смерти; Дата н/случая, отравления, травмы;
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									width: 360,
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['prichina_smerti'],
										comboSubject: 'DeathCause',
										tabIndex: TABINDEX_EREF + 26,
										anchor: '100%',
										listWidth: 400,
										xtype: 'swcommonsprcombo',
										listeners: {
											'change':function (comp,newval) {
												var base_form = this.findById('MedSvidDeathEditForm').getForm();
												if(newval == 1) {
													base_form.findField('DeathSvid_TraumaDate_Date').setValue(null);
													base_form.findField('DeathSvid_TraumaDate_Date').disable();
													base_form.findField('DeathSvid_TraumaDateStr').setValue(null);
													base_form.findField('DeathSvid_TraumaDateStr').disable();
													base_form.findField('DeathSvid_IsNoAccidentTime').setValue(false);
													base_form.findField('DeathSvid_IsNoAccidentTime').disable();
													base_form.findField('DeathSvid_TraumaDate_Time').setValue(null);
													base_form.findField('DeathSvid_TraumaDate_Time').disable();
													base_form.findField('DeathTrauma_id').setValue(null);
													base_form.findField('DeathTrauma_id').disable();
													base_form.findField('DtpDeathTime_id').setValue(null);
													base_form.findField('DtpDeathTime_id').disable();
													if(getRegionNick() == 'perm'){
														base_form.findField('DeathSvid_TraumaDescr').enable();
													} else {
														base_form.findField('DeathSvid_TraumaDescr').setValue(null);
														base_form.findField('DeathSvid_TraumaDescr').disable();
													}
												} else {
													base_form.findField('DeathSvid_TraumaDate_Date').enable();
													base_form.findField('DeathSvid_TraumaDateStr').enable();
													if(!base_form.findField('DeathSvid_IsNoAccidentTime').getValue())
														base_form.findField('DeathSvid_TraumaDate_Time').enable();
													base_form.findField('DeathSvid_IsNoAccidentTime').enable();
													base_form.findField('DeathTrauma_id').enable();
													base_form.findField('DtpDeathTime_id').enable();
													base_form.findField('DeathSvid_TraumaDescr').enable();
												}

												win.dopFilterDiagCombo();
												win.deathCauseDateBlock();
											}.createDelegate(this)
										}
									}]
								}]
							},
                                {
                                    xtype: 'fieldset',
                                    labelWidth: 180,
                                    autoHeight: true,
                                    title: lang['data_i_vremya_nachala_sluchaya_otravleniya_travmyi'],
                                    width: 985,
                                    style: 'margin-left: 5px;',
                                    items: [ //Пред. серия; Пред. номер; Пред. дата выдачи;
                                { //Причина смерти; Дата н/случая, отравления, травмы;
                                    layout: 'column',
                                    border: false,
                                    items: [{
                                        layout: 'form',
                                        width: 630,
                                        border: false,
                                        labelWidth: 50 + label_mod_1,
                                        items: [{ //Дата, время смерти;
                                            layout: 'column',
                                            border: false,
                                            items: [{
                                                layout: 'form',
                                                border: false,
                                                items: [{
                                                    disabled: false,
                                                    fieldLabel: lang['data'],
                                                    format: 'd.m.Y',
                                                    name: 'DeathSvid_TraumaDate_Date',
                                                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                                                    tabIndex: TABINDEX_EREF + 27,
                                                    width: 100,
                                                    value: '', //default value
                                                    xtype: 'swdatefield'
                                                }]
                                            },{
                                                layout: 'form',
                                                border: false,
                                                labelWidth: 100,
                                                items: [{
                                                    name: 'DeathSvid_TraumaDateStr',
                                                    fieldLabel: lang['neutoch_data'],
                                                    plugins: [new Ext.ux.InputTextMask('PP.PP.PPPP', false)],
                                                    width: 100,
                                                    xtype: 'textfield',
													listeners: {
														'change':function (comp,newval) {
															win.deathCauseDateBlock();
															if(newval && newval != comp.plugins[0].viewMask){
																var base_form = win.findById('MedSvidDeathEditForm').getForm();
																base_form.findField('DeathSvid_TraumaDate_Date').setAllowBlank(true);
																base_form.findField('DeathSvid_TraumaDate_Date').setValue(null);
															}
														}
													}
                                                }]
                                            }]
                                        },
                                            { //Дата, время смерти;
                                                layout: 'column',
                                                border: false,
                                                items: [{
                                                    layout: 'form',
                                                    border: false,
                                                    items: [{
                                                        format: 'H:i',
                                                        labelSeparator: '',
                                                        fieldLabel: lang['vremya'],
                                                        name: 'DeathSvid_TraumaDate_Time',
                                                        plugins: [new Ext.ux.InputTextMask('99:99', true)],
                                                        tabIndex: TABINDEX_EREF + 28,
                                                        width: 60,
                                                        value: '', //default value
                                                        listeners: {
                                                            'keydown': function (inp, e) {
                                                                if (e.getKey() == Ext.EventObject.F4) {
                                                                    e.stopEvent();
                                                                    inp.onTriggerClick();
                                                                }
                                                            }
                                                        },
                                                        xtype: 'swtimefield'
                                                    }]
                                                },
                                                    {
                                                        layout: 'form',
                                                        border: false,
                                                        labelWidth: 50,
                                                        items: [{
                                                            name: 'DeathSvid_IsNoAccidentTime',
                                                            labelSeparator: '',
                                                            boxLabel: lang['vremya_neizvestno'],
                                                            listeners: {
                                                                'check': function(checkbox, value) {
                                                                    var base_form = this.findById('MedSvidDeathEditForm').getForm();
                                                                    base_form.findField('DeathSvid_TraumaDate_Time').setValue(null);
                                                                    base_form.findField('DeathSvid_TraumaDate_Time').setDisabled(value);
                                                                }.createDelegate(this)
                                                            },
                                                            xtype: 'checkbox'
                                                        }]
                                                    }]
                                            }
                                        ]
                                    }]
                                }]},

                                { //Вид травмы; Смерть от ДТП наступила;
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									width: 360,
									border: false,
									items: [{
										disabled: false,
										fieldLabel: lang['vid_travmyi'],
										comboSubject: 'DeathTrauma',
										tabIndex: TABINDEX_EREF + 28,
										anchor: '100%',
										value: null, //default value
										xtype: 'swcommonsprcombo'
									}]
								}, {
									layout: 'form',
									width: 630,
									border: false,
									labelWidth: 250 + label_mod_1,
									items: [{
										disabled: false,
										fieldLabel: lang['smert_ot_dtp_nastupila'],
										comboSubject: 'DtpDeathTime',
										tabIndex: TABINDEX_EREF + 29,
										anchor: '100%',
										value: null, //default value
										xtype: 'swcommonsprcombo'
									}]
								}]
							}, { //Место и обстоятельства, при которых произошла травма (отравление);
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									border: false,
									items: [
										{
											disabled: false,
											fieldLabel: lang['mesto_i_obstoyatelstva_pri_kotoryih_proizoshla_travma_otravlenie'],
											maxLength: 256,
											name: 'DeathSvid_TraumaDescr',
											tabIndex: TABINDEX_EREF + 30,
											width: 910 + (field_mod_1 * 3) + (label_mod_1 * 2),
											value: '',
											xtype: 'textarea'
										},{
											allowBlank: true,
											hiddenName: 'MedPersonal_cid',
											fieldLabel: langs('Врач, проверивший свидетельство'),
											lastQuery: '',
											listWidth: 650,
											listeners: {
												'change': function (combo, newValue, oldValue) {
													var base_form = this.findById('MedSvidDeathEditForm').getForm();
													var medStaffCheckDate = base_form.findField('DeathSvid_checkDate');
													if(newValue){
														medStaffCheckDate.showContainer();
														medStaffCheckDate.setAllowBlank(false);
													}else{
														medStaffCheckDate.hideContainer();
														medStaffCheckDate.setValue();
														medStaffCheckDate.setAllowBlank(true);
													}
												}.createDelegate(this)
											},
											width: 500,
											value: null,
											xtype: 'swmedpersonalcombo'
										}, {
											fieldLabel: langs('Дата проверки'),
											format: 'd.m.Y',
											name: 'DeathSvid_checkDate',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											width: 100,
											xtype: 'swdatefield'
										}
									]
								}]
							}
							]
						}, {
							layout: 'form',
							title: lang['1_zaklyuchenie'],
							labelWidth: 250 + label_mod_2,
							items: [{
								allowBlank: false,
								disabled: false,
								listeners: {
									'change': function (field, newValue, oldValue) {
										win.filterDeathSetCause();
									}
								},
								fieldLabel: lang['prichina_smerti_ustanovlena'],
								comboSubject: 'DeathSetType',
								tabIndex: TABINDEX_EREF + 40,
								width: 500 + field_mod_2,
								xtype: 'swcommonsprcombo'
							}, {
								allowBlank: (getRegionNick() != 'ekb'),
								hidden: (getRegionNick() != 'ekb'),
								hiddenName: 'LpuSection_did',
								id: 'MSDEF_DeathLpuSectionCombo',
								changeDisabled: false,
								lastQuery: '',
								listWidth: 650,
								linkedElements: [
									'MSDEF_DeathMedPersonalCombo'
								],
								width: 500 + field_mod_2,
								xtype: 'swlpusectionglobalcombo'
							}, {
								allowBlank: (getRegionNick() != 'ekb'),
								hidden: (getRegionNick() != 'ekb'),
								fieldLabel: 'Сотрудник, установивший причину смерти',
								hiddenName: 'MedStaffFact_did',
								id: 'MSDEF_DeathMedPersonalCombo',
								lastQuery: '',
								listWidth: 650,
								parentElementId: 'MSDEF_DeathLpuSectionCombo',
								width: 500 + field_mod_2,
								value: null,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								allowBlank: false,
								disabled: false,
								fieldLabel: lang['na_osnovanii'],
								comboSubject: 'DeathSetCause',
								onLoadStore: function() {
									win.filterDeathSetCause();
								},
								tabIndex: TABINDEX_EREF + 41,
								width: 500 + field_mod_2,
								xtype: 'swcommonsprcombo'
							}, { //Тип свидетельства; Серия; Номер;
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									width: 760 + field_mod_2,
									border: false,
									items: [{
										html: '<div style="text-align: center; height: 50px; font-weight: bold;"><br>Причины смерти</div>',
										anchor: '100%',
										xtype: 'label'
									}, {
										minChars: 2,
										fieldLabel: lang['neposredstvennaya_prichina_smerti'],
										hiddenName: 'Diag_iid',
										width: 500 + field_mod_2,
										tabIndex: TABINDEX_EREF + 43,
										xtype: 'swdiagcombo',
										notInsertOnBlur: true,
										searchCodeAndName: (getRegionNick() == 'msk'),
										id: 'MSDEF_Diag_iid_Combo'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										id: 'Diag_iid_error'
									}, {
										minChars: 2,
										fieldLabel: lang['patologicheskoe_sostoyanie'],
										hiddenName: 'Diag_tid',
										width: 500 + field_mod_2,
										tabIndex: TABINDEX_EREF + 44,
										xtype: 'swdiagcombo',
										notInsertOnBlur: true,
										searchCodeAndName: (getRegionNick() == 'msk'),
										id: 'MSDEF_Diag_tid_Combo'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										id: 'Diag_tid_error'
									}, {
										minChars: 2,
										fieldLabel: lang['pervonachalnaya_prichina_smerti'],
										hiddenName: 'Diag_mid',
										width: 500 + field_mod_2,
										tabIndex: TABINDEX_EREF + 45,
										xtype: 'swdiagcombo',
										notInsertOnBlur: true,
										searchCodeAndName: (getRegionNick() == 'msk'),
										id: 'MSDEF_Diag_mid_Combo'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										id: 'Diag_mid_error'
									}, {
										minChars: 2,
										fieldLabel: lang['vneshnie_prichinyi'],
										hiddenName: 'Diag_eid',
										width: 500 + field_mod_2,
										tabIndex: TABINDEX_EREF + 46,
										xtype: 'swdiagcombo',
										notInsertOnBlur: true,
										searchCodeAndName: (getRegionNick() == 'msk'),
										id: 'MSDEF_Diag_eid_Combo'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										id: 'Diag_eid_error'
									}, {
										minChars: 2,
										fieldLabel: lang['prochie_vajnyie_sostoyaniya'],
										hiddenName: 'Diag_oid',
										notInsertOnBlur: true,
										width: 500 + field_mod_2,
										tabIndex: TABINDEX_EREF + 47,
										xtype: 'swdiagcombo',
										searchCodeAndName: (getRegionNick() == 'msk'),
										id: 'MSDEF_Diag_oid_Combo'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										id: 'Diag_oid_error'
									}]
								}, { //
									layout: 'form',
									width: 250,
									hidden: (getRegionNick() == 'ekb'),
									border: false,
									items: [{
										html: '<div style="text-align: center; height: 50px; font-weight: bold;">Приблизительные периоды времени между началом патологического процесса и смертью</div>',
										anchor: '100%',
										xtype: 'label'
									}, {
										disabled: false,
										fieldLabel: lang['priblizitelnyiy_period_vremeni_mejdu_nachalom_patologicheskogo_protsessa_i_smertyu'],
										hideLabel: true,
										maxLength: 20,
										name: 'DeathSvid_PribPeriod',
										tabIndex: TABINDEX_EREF + 42,
										anchor: '100%',
										value: '', //default value
									//	style: 'padding-top:1px;padding-bottom:1px;margin-bottom:2px;',
										xtype: 'textfield'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_iid hiddenBlockErrorCont'
									}, {
										disabled: false,
										fieldLabel: lang['priblizitelnyiy_period_vremeni_mejdu_nachalom_patologicheskogo_protsessa_i_smertyu'],
										hideLabel: true,
										maxLength: 20,
										name: 'DeathSvid_PribPeriodPat',
										tabIndex: TABINDEX_EREF + 42,
										anchor: '100%',
										value: '', //default value
									//	style: 'padding-top:1px;padding-bottom:1px;margin-bottom:2px;',
										xtype: 'textfield'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_tid hiddenBlockErrorCont'
									},{
										disabled: false,
										fieldLabel: lang['priblizitelnyiy_period_vremeni_mejdu_nachalom_patologicheskogo_protsessa_i_smertyu'],
										hideLabel: true,
										maxLength: 20,
										name: 'DeathSvid_PribPeriodDom',
										tabIndex: TABINDEX_EREF + 42,
										anchor: '100%',
										value: '', //default value
									//	style: 'padding-top:1px;padding-bottom:1px;margin-bottom:2px;',
										xtype: 'textfield'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_mid hiddenBlockErrorCont'
									},{
										disabled: false,
										fieldLabel: lang['priblizitelnyiy_period_vremeni_mejdu_nachalom_patologicheskogo_protsessa_i_smertyu'],
										hideLabel: true,
										maxLength: 20,
										name: 'DeathSvid_PribPeriodExt',
										tabIndex: TABINDEX_EREF + 42,
										anchor: '100%',
										value: '', //default value
									//	style: 'padding-top:1px;padding-bottom:1px;margin-bottom:2px;',
										xtype: 'textfield'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_eid hiddenBlockErrorCont'
									},{
										disabled: false,
										fieldLabel: lang['priblizitelnyiy_period_vremeni_mejdu_nachalom_patologicheskogo_protsessa_i_smertyu'],
										hideLabel: true,
										maxLength: 20,
										name: 'DeathSvid_PribPeriodImp',
										tabIndex: TABINDEX_EREF + 42,
										anchor: '100%',
										value: '', //default value
									//	style: 'padding-top:1px;padding-bottom:1px;',
										xtype: 'textfield'
									}]
								}, { //
									layout: 'form',
									width: 250,
									hidden: (getRegionNick() != 'ekb'),
									border: false,
									items: [{
										html: '<div style="text-align: center; height: 50px; font-weight: bold;">Приблизительные периоды времени между началом патологического процесса и смертью</div>',
										anchor: '100%',
										xtype: 'label'
									}, {
										disabled: false,
										fieldLabel: lang['priblizitelnyiy_period_vremeni_mejdu_nachalom_patologicheskogo_protsessa_i_smertyu'],
										hideLabel: true,
										maxLength: 20,
										name: 'DeathSvid_TimePeriod',
										id: 'MSDEF_DeathSvid_TimePeriod',
										tabIndex: TABINDEX_EREF + 42,
										anchor: '100%',
										value: '', //default value
									//	style: 'padding-top:1px;padding-bottom:1px;margin-bottom:2px;',
										xtype: 'numberfield'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_iid hiddenBlockErrorCont'
									}, {
										disabled: false,
										fieldLabel: lang['priblizitelnyiy_period_vremeni_mejdu_nachalom_patologicheskogo_protsessa_i_smertyu'],
										hideLabel: true,
										maxLength: 20,
										name: 'DeathSvid_TimePeriodPat',
										id: 'MSDEF_DeathSvid_TimePeriodPat',
										tabIndex: TABINDEX_EREF + 42,
										anchor: '100%',
										value: '', //default value
									//	style: 'padding-top:1px;padding-bottom:1px;margin-bottom:2px;',
										xtype: 'numberfield'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_tid hiddenBlockErrorCont'
									}, {
										disabled: false,
										fieldLabel: lang['priblizitelnyiy_period_vremeni_mejdu_nachalom_patologicheskogo_protsessa_i_smertyu'],
										hideLabel: true,
										maxLength: 20,
										name: 'DeathSvid_TimePeriodDom',
										id: 'MSDEF_DeathSvid_TimePeriodDom',
										tabIndex: TABINDEX_EREF + 42,
										anchor: '100%',
										value: '', //default value
									//	style: 'padding-top:1px;padding-bottom:1px;margin-bottom:2px;',
										xtype: 'numberfield'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_mid hiddenBlockErrorCont'
									}, {
										disabled: false,
										fieldLabel: lang['priblizitelnyiy_period_vremeni_mejdu_nachalom_patologicheskogo_protsessa_i_smertyu'],
										hideLabel: true,
										maxLength: 20,
										name: 'DeathSvid_TimePeriodExt',
										id: 'MSDEF_DeathSvid_TimePeriodExt',
										tabIndex: TABINDEX_EREF + 42,
										anchor: '100%',
										value: '', //default value
									//	style: 'padding-top:1px;padding-bottom:1px;margin-bottom:2px;',
										xtype: 'numberfield'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_eid hiddenBlockErrorCont'
									}, {
										disabled: false,
										fieldLabel: lang['priblizitelnyiy_period_vremeni_mejdu_nachalom_patologicheskogo_protsessa_i_smertyu'],
										hideLabel: true,
										maxLength: 20,
										name: 'DeathSvid_TimePeriodImp',
										id: 'MSDEF_DeathSvid_TimePeriodImp',
										tabIndex: TABINDEX_EREF + 42,
										anchor: '100%',
										value: '', //default value
									//	style: 'padding-top:1px;padding-bottom:1px;',
										xtype: 'numberfield'
									}]
								}, { //
									layout: 'form',
									hidden: (getRegionNick() != 'ekb'),
									width: 150,
									border: false,
									labelWidth:1,
									hideLabel:true,
									style:'margin-top:50px;',
									items: [{
										hiddenName: 'Okei_id',
										id: 'MSDEF_Okei_Combo',
										width: 120,
										xtype: 'swcommonsprcombo',
										labelSeparator:'',
										comboSubject: 'Okei'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_iid hiddenBlockErrorCont'
									}, {
										hiddenName: 'Okei_patid',
										id: 'MSDEF_OkeiPat_Combo',
										width: 120,
										xtype: 'swcommonsprcombo',
										labelSeparator:'',
										comboSubject: 'Okei'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_tid hiddenBlockErrorCont'
									}, {
										hiddenName: 'Okei_domid',
										width: 120,
										id: 'MSDEF_OkeiDom_Combo',
										labelSeparator:'',
										xtype: 'swcommonsprcombo',
										comboSubject: 'Okei'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_mid hiddenBlockErrorCont'
									}, {
										hiddenName: 'Okei_extid',
										width: 120,
										id: 'MSDEF_OkeiExt_Combo',
										labelSeparator:'',
										xtype: 'swcommonsprcombo',
										comboSubject: 'Okei'
									}, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_eid hiddenBlockErrorCont'
									}, {
										hiddenName: 'Okei_impid',
										width: 120,
										id: 'MSDEF_OkeiImp_Combo',
										labelSeparator:'',
										xtype: 'swcommonsprcombo',
										comboSubject: 'Okei'
									}]
								}, {
									layout: 'form',
									width: 100,
									border: false,
									labelWidth: 38,
									items: [{
										html: '<div style="text-align: center; height: 40px;padding-top:10px; font-weight: bold;">Первоначальная причина</div>',
										anchor: '100%',
										xtype: 'label'
									}, {
										layout: 'form',
										border: false,
										items: [{
											
	                                        name: 'DeathSvid_IsPrimDiagIID',
	                                        id: 'DeathSvid_IsPrimDiagIID',
	                                        labelSeparator: '',
	                                        boxLabel: '',
	                                        labelWidth: 0,
	                                        onClick : function(e) {
										        var box = this.findById('DeathSvid_IsPrimDiagIID');log(1);log(this.checkPrimDiagHold);
										        if(!box.disabled && !box.ReadOnly && !this.checkPrimDiagHold){
										        	this.checkPrimDiag({field:box.name});
										        }
										    }.createDelegate(this),
	                                        xtype: 'swdscheckbox'
	                                        
	                                    }]
                                    }, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_iid hiddenBlockErrorCont'
									}, {
                                    	layout: 'form',
										border: false,
										style: 'padding-top:2px;',
										items: [{
	                                        name: 'DeathSvid_IsPrimDiagTID',
	                                        id: 'DeathSvid_IsPrimDiagTID',
	                                        labelSeparator: '',
	                                        boxLabel: '',
	                                        labelWidth: 0,
	                                        onClick : function(e) {
										        var box = this.findById('DeathSvid_IsPrimDiagTID');log(2);log(this.checkPrimDiagHold);
										        if(!box.disabled && !box.ReadOnly && !this.checkPrimDiagHold){
										            this.checkPrimDiag({field:box.name});
										        }
										    }.createDelegate(this),
	                                        xtype: 'swdscheckbox'
                                        }]
                                    }, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_tid hiddenBlockErrorCont'
									}, {
                                    	layout: 'form',
										border: false,
										style: 'padding-top:2px;',
										items: [{
	                                        name: 'DeathSvid_IsPrimDiagMID',
	                                        id: 'DeathSvid_IsPrimDiagMID',
	                                        labelSeparator: '',
	                                        boxLabel: '',
	                                        labelWidth: 0,
	                                        onClick : function(e) {
										        var box = this.findById('DeathSvid_IsPrimDiagMID');log(3);log(this.checkPrimDiagHold);
										        if(!box.disabled && !box.ReadOnly && !this.checkPrimDiagHold){
										            this.checkPrimDiag({field:box.name});
										        }
										    }.createDelegate(this),
	                                        xtype: 'swdscheckbox'
                                        }]
                                    }, {
										html: '',
										anchor: '100%',
										xtype: 'label',
										cls: 'hiddenBlockErrorContainerDiag_mid hiddenBlockErrorCont'
									}, {
                                    	layout: 'form',
										border: false,
										style: 'padding-top:2px;',
										items: [{
	                                        name: 'DeathSvid_IsPrimDiagEID',
	                                        id: 'DeathSvid_IsPrimDiagEID',
	                                        labelSeparator: '',
	                                        boxLabel: '',
	                                        labelWidth: 0,
	                                        onClick : function(e) {
										        var box = this.findById('DeathSvid_IsPrimDiagEID');log(4);log(this.checkPrimDiagHold);
										        if(!box.disabled && !box.ReadOnly && !this.checkPrimDiagHold){
										            this.checkPrimDiag({field:box.name});
										        }
										    }.createDelegate(this),
	                                        xtype: 'swdscheckbox'
                                        }]
                                    }]
								}]
							}, {
								disabled: false,
								fieldLabel: lang['prichinyi_ne_svyazannyie_s_boleznyu_a_takje_operatsii'],
								maxLength: 256,
								name: 'DeathSvid_Oper',
								tabIndex: TABINDEX_EREF + 48,
								width: 810 + field_mod_2,
								value: '', //default value
								xtype: 'textarea'
							}, {
								disabled: false,
								fieldLabel: lang['dlya_jenschin_reprod_vozrasta'],
								comboSubject: 'DeathWomanType',
								tabIndex: TABINDEX_EREF + 49,
								width: 500 + field_mod_2,
								value: null, //default value
								xtype: 'swcommonsprcombo'
							}, {
								xtype: 'fieldset',
								autoHeight: true,
								title: lang['poluchatel'],
								width: 985,
								labelWidth: 234 + label_mod_2,
								style: 'margin-left: 5px;',
								items: [{
									editable: false,
									fieldLabel: lang['fio'],
									hiddenName: 'Person_rid',
									tabIndex: TABINDEX_EREF + 50,
									allowBlank: getRegionNick() !== 'msk',
									width: 500,
									xtype: 'swpersoncombo',
									onTrigger1Click: function () {
										var combo = this;

										if ( combo.disabled ) return false;
										if ( win.action == 'view' ) return false;

										var base_form = win.findById('MedSvidDeathEditForm').getForm();

										getWnd('swPersonSearchWindow').show({
											onSelect: function (personData) {
												if (personData.Person_id > 0) {
													combo.getStore().loadData([{
														Person_id: personData.Person_id,
														Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
													}]);
													combo.setValue(personData.Person_id);
													combo.collapse();
													combo.focus(true, 500);
													combo.fireEvent('change', combo, personData.Person_id);

													base_form.findField('DeathSvid_PolFio').setValue('');
													base_form.findField('DeathSvid_PolFio').disable();

													// Тянем данные документа
													var loadMask = new Ext.LoadMask(win.getEl(), {msg: lang['poluchenie_dannyih_dokumenta']});
													loadMask.show();

													Ext.Ajax.request({
														callback: function (options, success, response) {
															loadMask.hide();

															if ( success && response.responseText != '' ) {
																var
																	documentData = new Array(),
																	documentSerNum = '',
																	response_obj = Ext.util.JSON.decode(response.responseText);

																if ( typeof response_obj == 'object' && response_obj.length > 0 ) {

																	if ( !Ext.isEmpty(response_obj[0].MissingDataList) && !getRegionNick().inlist(['kz']) ) {
																		sw.swMsg.show({
																			buttons: sw.swMsg.OK,
																			icon: Ext.MessageBox.WARNING,
																			width: '400',
																			msg: langs('Для регистрации медсвидетельства о смерти в РЭМД ЕГИСЗ обязательно наличие следующих данных получателя свидетельства:') + response_obj[0].MissingDataList.substring(0, response_obj[0].MissingDataList.length - 1) + '. <br>' + langs('Заполните недостающие данные.'),
																			title: langs('Ошибка')
																		});
																	}

																	if ( !Ext.isEmpty(response_obj[0].DocumentType_Name) ) {
																		documentData.push(response_obj[0].DocumentType_Name);
																	}

																	if ( !Ext.isEmpty(response_obj[0].Document_Ser) ) {
																		documentSerNum = response_obj[0].Document_Ser;
																	}

																	if ( !Ext.isEmpty(response_obj[0].Document_Num) ) {
																		documentSerNum = documentSerNum + lang['№'] + response_obj[0].Document_Num;
																	}

																	if ( !Ext.isEmpty(documentSerNum) ) {
																		documentData.push(documentSerNum);
																	}

																	if ( !Ext.isEmpty(response_obj[0].OrgDep_Name) ) {
																		documentData.push(lang['vyidan'] + response_obj[0].OrgDep_Name);
																	}

																	if ( !Ext.isEmpty(response_obj[0].Document_begDate) ) {
																		documentData.push(lang['data_vyidachi'] + response_obj[0].Document_begDate);
																	}

																	base_form.findField('DeathSvid_RcpDocument').setValue(documentData.join(', '));
																	base_form.findField('DeathSvid_RcpDocument').setDisabled(true);
																}
															}
														},
														failure: function() {
															loadMask.hide();
															sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_poluchenii_dannyih_dokumenta']);
														},
														params: {
															mode: 'Document',
															Person_id: personData.Person_id
														},
														url: '/?c=Common&m=loadPersonData'
													});
												}
												getWnd('swPersonSearchWindow').hide();
											},
											onClose: function () {
												combo.focus(true, 500)
											}
										});
									},
									onTrigger2Click: function () {
										var combo = this;

										if ( combo.disabled ) return false;
										if ( win.action == 'view' ) return false;

										var base_form = win.findById('MedSvidDeathEditForm').getForm();

										combo.clearValue();
										combo.getStore().removeAll();

										base_form.findField('DeathSvid_PolFio').enable();

										base_form.findField('DeathSvid_RcpDocument').setValue('');
										base_form.findField('DeathSvid_RcpDocument').disable();
									},
									enableKeyEvents: true,
									listeners: {
										'change': function (combo, newValue) {
											var base_form = win.findById('MedSvidDeathEditForm').getForm();
											if (!Ext.isEmpty(newValue) ) {
												base_form.findField('DeathSvid_RcpDocument').enable();
											}
										},
										'keydown': function (inp, e) {
											if (e.F4 == e.getKey()) {
												if (e.browserEvent.stopPropagation)
													e.browserEvent.stopPropagation();
												else
													e.browserEvent.cancelBubble = true;
												if (e.browserEvent.preventDefault)
													e.browserEvent.preventDefault();
												else
													e.browserEvent.returnValue = false;
												e.browserEvent.returnValue = false;
												e.returnValue = false;
												if (Ext.isIE) {
													e.browserEvent.keyCode = 0;
													e.browserEvent.which = 0;
												}
												inp.onTrigger1Click();
												return false;
											}
										},
										'keyup': function (inp, e) {
											if (e.F4 == e.getKey()) {
												if (e.browserEvent.stopPropagation)
													e.browserEvent.stopPropagation();
												else
													e.browserEvent.cancelBubble = true;
												if (e.browserEvent.preventDefault)
													e.browserEvent.preventDefault();
												else
													e.browserEvent.returnValue = false;
												e.browserEvent.returnValue = false;
												e.returnValue = false;
												if (Ext.isIE) {
													e.browserEvent.keyCode = 0;
													e.browserEvent.which = 0;
												}
												return false;
											}
										}
									}
								}, {
									disabled: false,
									fieldLabel: lang['fio_ruchnoy_vvod'],
									listeners: {
										'change': function (field, newValue, oldValue) {
											var base_form = win.findById('MedSvidDeathEditForm').getForm();
											if (!Ext.isEmpty(newValue)) {
												base_form.findField('DeathSvid_RcpDocument').enable();
											} else {
												base_form.findField('DeathSvid_RcpDocument').setValue('');
												base_form.findField('DeathSvid_RcpDocument').disable();
											}
										}
									},
									maxLength: 100,
									triggerAction: 'all',
									name: 'DeathSvid_PolFio',
									hiddenName: 'DeathSvid_PolFio',
									tabIndex: TABINDEX_EREF + 51,
									width: 500,
									xtype: 'textfield'
								}, {
									disabled: true,
									fieldLabel: lang['dokument_seriya_nomer_kem_vyidan'],
									maxLength: 256,
									name: 'DeathSvid_RcpDocument',
									tabIndex: TABINDEX_EREF + 51,
									width: 500,
									xtype: 'textfield'
								}, {
									allowBlank: getRegionNick() !== 'msk',
									disabled: false,
									fieldLabel: langs('Отношение к умершему'),
									comboSubject: 'DeathSvidRelation',
									hiddenName: 'DeathSvidRelation_id',
									tabIndex: TABINDEX_EREF + 52,
									width: 500,
									xtype: 'swcommonsprcombo'
								},	{
									disabled: false,
									allowBlank: getRegionNick() !== 'msk',
									fieldLabel: lang['data_polucheniya_svid-va'],
									format: 'd.m.Y',
									name: 'DeathSvid_RcpDate',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									tabIndex: TABINDEX_EREF + 53,
									width: 100,
									xtype: 'swdatefield'
								}]
							}]
						}]
					})
					],
					keys: [{
						fn: function (inp, e) {
							e.stopEvent();

							if (e.browserEvent.stopPropagation)
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;

							if (e.browserEvent.preventDefault)
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;

							e.browserEvent.returnValue = false;
							e.returnValue = false;

							if (Ext.isIE) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							if (e.getKey() == Ext.EventObject.F6) {
								Ext.getCmp('MSDEF_PersonInformationFrame').panelButtonClick(1);
								return false;
							}

							if (e.getKey() == Ext.EventObject.F10) {
								Ext.getCmp('MSDEF_PersonInformationFrame').panelButtonClick(2);
								return false;
							}

							if (e.getKey() == Ext.EventObject.F11) {
								Ext.getCmp('MSDEF_PersonInformationFrame').panelButtonClick(3);
								return false;
							}

							if (e.getKey() == Ext.EventObject.F12) {
								if (e.CtrlKey == true) {
									Ext.getCmp('MSDEF_PersonInformationFrame').panelButtonClick(5);
								} else {
									Ext.getCmp('MSDEF_PersonInformationFrame').panelButtonClick(4);
								}
								return false;
							}
						},
						key: [Ext.EventObject.F6, Ext.EventObject.F10, Ext.EventObject.F11, Ext.EventObject.F12],
						scope: this,
						stopEvent: true
					}, {
						alt: true,
						fn: function (inp, e) {
							switch (e.getKey()) {
								case Ext.EventObject.C:
									if (this.action != 'view') {
										this.doSave(false);
									}
									break;

								case Ext.EventObject.J:
									this.hide();
									break;
							}
						},
						key: [Ext.EventObject.C, Ext.EventObject.G, Ext.EventObject.J],
						scope: this,
						stopEvent: true
					}],
					labelAlign: 'right',
					labelWidth: 130 + label_mod_1,
					reader: new Ext.data.JsonReader({
							id: 'DeathSvid_id'
						}, [
							{mapping: 'DeathSvid_id', name: 'DeathSvid_id', type: 'int'},
							{mapping: 'Server_id', name: 'Server_id', type: 'int'},
							{mapping: 'Person_id', name: 'Person_id', type: 'int'},
							{mapping: 'Person_mid', name: 'Person_mid', type: 'int'},
							{mapping: 'Person_m_FIO', name: 'Person_m_FIO', type: 'string'},
							{mapping: 'Mother_BirthDay', name: 'Mother_BirthDay', type: 'date', dateFormat: 'd.m.Y'},
							{mapping: 'Person_rid', name: 'Person_rid', type: 'int'},
							{mapping: 'DeathSvid_PolFio', name: 'DeathSvid_PolFio', type: 'string'},
							{mapping: 'Person_r_FIO', name: 'Person_r_FIO', type: 'string'},
							{mapping: 'Person_r_FIO', name: 'Person_r_FIO', type: 'string'},
							{mapping: 'MedPersonal_id', name: 'MedPersonal_id', type: 'int'},
							{mapping: 'MedStaffFact_id', name: 'MedStaffFact_id', type: 'int'},
							{mapping: 'DeathSvidType_id', name: 'DeathSvidType_id', type: 'int'},
							{mapping: 'DeathSvid_IsDuplicate', name: 'DeathSvid_IsDuplicate', type: 'int'},
							{mapping: 'DeathSvid_IsLose', name: 'DeathSvid_IsLose', type: 'int'},
							{mapping: 'ReceptType_id', name: 'ReceptType_id', type: 'int'},
							{mapping: 'DeathCause_id', name: 'DeathCause_id', type: 'int'},
							{mapping: 'DeathFamilyStatus_id', name: 'DeathFamilyStatus_id', type: 'int'},
							{mapping: 'DeathSvid_IsNoPlace', name: 'DeathSvid_IsNoPlace', type: 'int'},
                            {mapping: 'DeathSvid_isBirthDate', name: 'DeathSvid_isBirthDate', type: 'int'},
							{mapping: 'DeathPlace_id', name: 'DeathPlace_id', type: 'int'},
							{mapping: 'DeathEducation_id', name: 'DeathEducation_id', type: 'int'},
							{mapping: 'DeathTrauma_id', name: 'DeathTrauma_id', type: 'int'},
							{mapping: 'DeathSetType_id', name: 'DeathSetType_id', type: 'int'},
							{mapping: 'DeathSetCause_id', name: 'DeathSetCause_id', type: 'int'},
							{mapping: 'DeathWomanType_id', name: 'DeathWomanType_id', type: 'int'},
							{mapping: 'DeathEmployment_id', name: 'DeathEmployment_id', type: 'int'},
							{mapping: 'DtpDeathTime_id', name: 'DtpDeathTime_id', type: 'int'},
							{mapping: 'ChildTermType_id', name: 'ChildTermType_id', type: 'int'},
							{mapping: 'BAddress_Zip', name: 'BAddress_Zip', type: 'string'},
							{mapping: 'BKLCountry_id', name: 'BKLCountry_id', type: 'string'},
							{mapping: 'BKLRGN_id', name: 'BKLRGN_id', type: 'string'},
							{mapping: 'BKLSubRGN_id', name: 'BKLSubRGN_id', type: 'string'},
							{mapping: 'BKLCity_id', name: 'BKLCity_id', type: 'string'},
							{mapping: 'BKLTown_id', name: 'BKLTown_id', type: 'string'},
							{mapping: 'BKLStreet_id', name: 'BKLStreet_id', type: 'string'},
							{mapping: 'BAddress_House', name: 'BAddress_House', type: 'string'},
							{mapping: 'BAddress_Corpus', name: 'BAddress_Corpus', type: 'string'},
							{mapping: 'BAddress_Flat', name: 'BAddress_Flat', type: 'string'},
							{mapping: 'BAddress_Address', name: 'BAddress_Address', type: 'string'},
							{mapping: 'BAddress_AddressText', name: 'BAddress_AddressText', type: 'string'},
							{mapping: 'DAddress_Zip', name: 'DAddress_Zip', type: 'string'},
							{mapping: 'DKLCountry_id', name: 'DKLCountry_id', type: 'string'},
							{mapping: 'DKLRGN_id', name: 'DKLRGN_id', type: 'string'},
							{mapping: 'DKLSubRGN_id', name: 'DKLSubRGN_id', type: 'string'},
							{mapping: 'DKLCity_id', name: 'DKLCity_id', type: 'string'},
							{mapping: 'DKLTown_id', name: 'DKLTown_id', type: 'string'},
							{mapping: 'DKLStreet_id', name: 'DKLStreet_id', type: 'string'},
							{mapping: 'DAddress_House', name: 'DAddress_House', type: 'string'},
							{mapping: 'DAddress_Corpus', name: 'DAddress_Corpus', type: 'string'},
							{mapping: 'DAddress_Flat', name: 'DAddress_Flat', type: 'string'},
							{mapping: 'DAddress_Address', name: 'DAddress_Address', type: 'string'},
							{mapping: 'DAddress_AddressText', name: 'DAddress_AddressText', type: 'string'},
							{mapping: 'Diag_iid', name: 'Diag_iid', type: 'int'},
							{mapping: 'Diag_tid', name: 'Diag_tid', type: 'int'},
							{mapping: 'Diag_mid', name: 'Diag_mid', type: 'int'},
							{mapping: 'Diag_eid', name: 'Diag_eid', type: 'int'},
							{mapping: 'Diag_oid', name: 'Diag_oid', type: 'int'},
							{mapping: 'Okei_id', name: 'Okei_id', type: 'int'},
							{mapping: 'Okei_patid', name: 'Okei_patid', type: 'int'},
							{mapping: 'Okei_domid', name: 'Okei_domid', type: 'int'},
							{mapping: 'Okei_extid', name: 'Okei_extid', type: 'int'},
							{mapping: 'Okei_impid', name: 'Okei_impid', type: 'int'},
							{mapping: 'MedStaffFact_did', name: 'MedStaffFact_did', type: 'int'},
							{mapping: 'DeathSvid_IsPrimDiagIID', name: 'DeathSvid_IsPrimDiagIID', type: 'int'},
							{mapping: 'DeathSvid_IsPrimDiagTID', name: 'DeathSvid_IsPrimDiagTID', type: 'int'},
							{mapping: 'DeathSvid_IsPrimDiagMID', name: 'DeathSvid_IsPrimDiagMID', type: 'int'},
							{mapping: 'DeathSvid_IsPrimDiagEID', name: 'DeathSvid_IsPrimDiagEID', type: 'int'},
							{mapping: 'DeathSvid_BirthDateStr', name: 'DeathSvid_BirthDateStr', type: 'string'},
							{mapping: 'DeathSvid_DeathDateStr', name: 'DeathSvid_DeathDateStr', type: 'string'},
							{mapping: 'DeathSvid_Ser', name: 'DeathSvid_Ser', type: 'string'},
							{mapping: 'DeathSvid_Num', name: 'DeathSvid_Num', type: 'string'},
							{mapping: 'DeathSvid_OldSer', name: 'DeathSvid_OldSer', type: 'string'},
							{mapping: 'DeathSvid_OldNum', name: 'DeathSvid_OldNum', type: 'string'},
							{
								mapping: 'DeathSvid_DeathDate_Date',
								name: 'DeathSvid_DeathDate_Date',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{mapping: 'DeathSvid_DeathDate_Time', name: 'DeathSvid_DeathDate_Time', type: 'string'},
							{mapping: 'DeathSvid_IsUnknownDeathDate', name: 'DeathSvid_IsUnknownDeathDate', type: 'int'},
							{mapping: 'DeathSvid_IsNoDeathTime', name: 'DeathSvid_IsNoDeathTime', type: 'int'},
                            {mapping: 'DeathSvid_IsNoAccidentTime', name: 'DeathSvid_IsNoAccidentTime', type: 'int'},
							{mapping: 'DeathSvid_Mass', name: 'DeathSvid_Mass', type: 'int'},
							{mapping: 'DeathSvid_Month', name: 'DeathSvid_Month', type: 'int'},
							{mapping: 'DeathSvid_Day', name: 'DeathSvid_Day', type: 'int'},
							{mapping: 'DeathSvid_ChildCount', name: 'DeathSvid_ChildCount', type: 'int'},
							{mapping: 'DeathSvid_TraumaDateStr', name: 'DeathSvid_TraumaDateStr', type: 'string'},
							{
								mapping: 'DeathSvid_TraumaDate_Date',
								name: 'DeathSvid_TraumaDate_Date',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{mapping: 'DeathSvid_TraumaDate_Time', name: 'DeathSvid_TraumaDate_Time', type: 'string'},
							{mapping: 'DeathSvid_TraumaDescr', name: 'DeathSvid_TraumaDescr', type: 'string'},
							{mapping: 'DeathSvid_Oper', name: 'DeathSvid_Oper', type: 'string'},
							{mapping: 'DeathSvid_PribPeriod', name: 'DeathSvid_PribPeriod', type: 'string'},
							{mapping: 'DeathSvid_PribPeriodPat', name: 'DeathSvid_PribPeriodPat', type: 'string'},
							{mapping: 'DeathSvid_PribPeriodDom', name: 'DeathSvid_PribPeriodDom', type: 'string'},
							{mapping: 'DeathSvid_PribPeriodExt', name: 'DeathSvid_PribPeriodExt', type: 'string'},
							{mapping: 'DeathSvid_PribPeriodImp', name: 'DeathSvid_PribPeriodImp', type: 'string'},
							{mapping: 'DeathSvid_TimePeriod', name: 'DeathSvid_TimePeriod', type: 'string'},
							{mapping: 'DeathSvid_TimePeriodPat', name: 'DeathSvid_TimePeriodPat', type: 'string'},
							{mapping: 'DeathSvid_TimePeriodDom', name: 'DeathSvid_TimePeriodDom', type: 'string'},
							{mapping: 'DeathSvid_TimePeriodExt', name: 'DeathSvid_TimePeriodExt', type: 'string'},
							{mapping: 'DeathSvid_TimePeriodImp', name: 'DeathSvid_TimePeriodImp', type: 'string'},
							{
								mapping: 'DeathSvid_RcpDate',
								name: 'DeathSvid_RcpDate',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{
								mapping: 'DeathSvid_GiveDate',
								name: 'DeathSvid_GiveDate',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{mapping: 'DeathSvidRelation_id', name: 'DeathSvidRelation_id', type: 'int'},
							{
								mapping: 'DeathSvid_OldGiveDate',
								name: 'DeathSvid_OldGiveDate',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{mapping: 'DeathSvid_RcpDocument', name: 'DeathSvid_RcpDocument', type: 'string'},
							{mapping: 'MedStaffFact_id', name: 'MedStaffFact_id', type: 'int'},
							{mapping: 'LpuSection_id', name: 'LpuSection_id', type: 'int'},
							{mapping: 'Lpu_id', name: 'Lpu_id', type: 'int'},
							{mapping: 'OrgHead_id', name: 'OrgHead_id', type: 'int'},
							{mapping: 'OrgHeadPost_id', name: 'OrgHeadPost_id', type: 'int'},
							{mapping: 'Person_hid', name: 'Person_hid', type: 'int'},
							{mapping: 'MedPersonal_cid', name: 'MedPersonal_cid', type: 'int'},
							{
								mapping: 'DeathSvid_checkDate',
								name: 'DeathSvid_checkDate',
								type: 'date',
								dateFormat: 'd.m.Y'
							}
						]
					),
					region: 'center',
					url: '/?c=MedSvid&m=saveMedSvidDeath'
				})]
		});

		var mp_combo = Ext.getCmp('MSDEF_MedPersonalCombo');
		var ls_combo = Ext.getCmp('MSDEF_LpuSectionCombo');
		mp_combo.getStore().addListener('datachanged', function (store) {
			if (store.getCount() == 1) {
				var mp_id = store.getAt(0).data.MedStaffFact_id;
				mp_combo.setValue(mp_id);
			}
		});
		ls_combo.addListener('change', function (combo, newValue, oldValue) {
			if (!(typeof combo.linkedElements == 'object') || combo.linkedElements.length == 0 || combo.linkedElementsDisabled == true) {
				return true;
			}

			var altValue;

			if (combo.valueFieldAdd) {
				var r = combo.getStore().getById(newValue);

				if (r) {
					altValue = r.get(combo.valueFieldAdd);
				}
			}

			for (var i = 0; i < combo.linkedElements.length; i++) {
				var linked_element = Ext.getCmp(combo.linkedElements[i]);

				if (!linked_element) {
					return true;
				}

				var linked_element_value = linked_element.getValue();

				if (newValue > 0) {
					linked_element.clearValue();
					linked_element.setBaseFilter(function (record, id) {
						if (record.get(combo.valueField) == newValue || (altValue && record.get(combo.valueField) == altValue)) {
							return true;
						}
						else {
							return false;
						}
					}.createDelegate(combo), combo);

					if (linked_element_value && linked_element.valueField) {
						var index = linked_element.getStore().findBy(function (record) {
							if (record.get(combo.valueField) == linked_element_value) {
								return true;
							}
							else {
								return false;
							}
						}.createDelegate(combo));

						var record = linked_element.getStore().getAt(index);

						if (linked_element.getStore().getCount() == 1)
							record = linked_element.getStore().getAt(0);

						if (record) {
							linked_element.setValue(linked_element_value);
							linked_element.fireEvent('change', linked_element, linked_element_value, null);
						} else {
							linked_element.clearValue();
							linked_element.fireEvent('change', linked_element, null);
						}
					}
				}
				else {
					linked_element.clearBaseFilter();
					linked_element.getStore().clearFilter();
					linked_element.fireEvent('change', linked_element, null);
				}
			}
		});
		var diag_iid_combo = Ext.getCmp('MSDEF_Diag_iid_Combo');
		var diag_iid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagIID');
		var diag_tid_combo = Ext.getCmp('MSDEF_Diag_tid_Combo');
		var diag_tid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagTID');
		var diag_mid_combo = Ext.getCmp('MSDEF_Diag_mid_Combo');
		var diag_mid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagMID');
		var diag_eid_combo = Ext.getCmp('MSDEF_Diag_eid_Combo');
		var diag_eid_checkbox = Ext.getCmp('DeathSvid_IsPrimDiagEID');
		var diag_oid_combo = Ext.getCmp('MSDEF_Diag_oid_Combo');
		var okei_combo = Ext.getCmp('MSDEF_Okei_Combo');
		var okeipat_combo = Ext.getCmp('MSDEF_OkeiPat_Combo');
		var okeidom_combo = Ext.getCmp('MSDEF_OkeiDom_Combo');
		var okeiext_combo = Ext.getCmp('MSDEF_OkeiExt_Combo');
		var okeiimp_combo = Ext.getCmp('MSDEF_OkeiImp_Combo');
		var period_combo = Ext.getCmp('MSDEF_DeathSvid_TimePeriod'); 
		var periodPat_combo = Ext.getCmp('MSDEF_DeathSvid_TimePeriodPat'); 
		var periodDom_combo = Ext.getCmp('MSDEF_DeathSvid_TimePeriodDom'); 
		var periodExt_combo = Ext.getCmp('MSDEF_DeathSvid_TimePeriodExt'); 
		var periodImp_combo = Ext.getCmp('MSDEF_DeathSvid_TimePeriodImp'); 
		var c_region = getRegionNick().inlist(['perm','kareliya']);
		diag_iid_combo.addListener('change', function(combo, newValue, oldValue) {
			if(!Ext.isEmpty(newValue)){
				if(!c_region){
					diag_iid_checkbox.enable();
				}
				okei_combo.enable();
				period_combo.enable();	
			} else {
				diag_iid_checkbox.setValue(false);
				diag_iid_checkbox.disable();
				okei_combo.disable();
				period_combo.disable();
				period_combo.setValue('');
			}
			win.setPrimDiag();
		});
		diag_tid_combo.addListener('change', function(combo, newValue, oldValue) {
			if(!Ext.isEmpty(newValue)){
				diag_iid_combo.setAllowBlank(false);
				if(!c_region){
					diag_tid_checkbox.enable();
				}
				okeipat_combo.enable();
				periodPat_combo.enable();
			} else {
				diag_iid_combo.setAllowBlank(true);
				diag_tid_checkbox.setValue(false);
				diag_tid_checkbox.disable();
				okeipat_combo.disable();
				periodPat_combo.disable();
				periodPat_combo.setValue('');
			}
			win.setPrimDiag();
		});
		diag_mid_combo.addListener('change', function(combo, newValue, oldValue) {
			if(!Ext.isEmpty(newValue)){
				diag_iid_combo.setAllowBlank(false);
				diag_tid_combo.setAllowBlank(getRegionNick()=='ufa');
				
				// @task https://jira.is-mis.ru/browse/PROMEDWEB-9658
				// определяем обязательность полей Прочие важные состояния и Патологическое состояние 
				var covidFilter = function(Diag_Code) {
					return Diag_Code && Diag_Code.inlist(['U07.1', 'U07.2']);
				};
				diag_oid_combo.setAllowBlank(!covidFilter(combo.getFieldValue('Diag_Code')));
				diag_tid_combo.setAllowBlank(!covidFilter(combo.getFieldValue('Diag_Code')));
				
				if(!c_region){
					diag_mid_checkbox.enable();
				}
				okeidom_combo.enable();
				periodDom_combo.enable();	
			} else {
				diag_iid_combo.setAllowBlank(true);
				diag_tid_combo.setAllowBlank(true);
				diag_mid_checkbox.setValue(false);
				diag_mid_checkbox.disable();
				okeidom_combo.disable();
				periodDom_combo.disable();	
				periodDom_combo.setValue('');
			}
			win.setPrimDiag();
		});
		diag_eid_combo.addListener('change', function(combo, newValue, oldValue) {
			if(!Ext.isEmpty(newValue)){
				if(!c_region){
					diag_eid_checkbox.enable();
				}
				okeiext_combo.enable();	
				periodExt_combo.enable();
			} else {
				diag_eid_checkbox.setValue(false);
				diag_eid_checkbox.disable();
				okeiext_combo.disable();
				periodExt_combo.disable();
				periodExt_combo.setValue('');
			}
			win.setPrimDiag();
		});
		diag_oid_combo.addListener('change', function(combo, newValue, oldValue) {
			if(!Ext.isEmpty(newValue)){
				okeiimp_combo.enable();
				periodImp_combo.enable();	
			} else {
				okeiimp_combo.disable();
				periodImp_combo.disable();
				periodImp_combo.setValue('');
			}
		});

		sw.Promed.swMedSvidDeathEditWindow.superclass.initComponent.apply(this, arguments);
	},
	checkIsNoPlace: function() {
		var base_form = this.findById('MedSvidDeathEditForm').getForm();
		if (base_form.findField('DeathSvid_IsNoPlace').checked) {
			base_form.findField('DAddress_Zip').setValue('');
			base_form.findField('DKLCountry_id').setValue('');
			base_form.findField('DKLRGN_id').setValue('');
			base_form.findField('DKLSubRGN_id').setValue('');
			base_form.findField('DKLCity_id').setValue('');
			base_form.findField('DKLTown_id').setValue('');
			base_form.findField('DKLStreet_id').setValue('');
			base_form.findField('DAddress_House').setValue('');
			base_form.findField('DAddress_Corpus').setValue('');
			base_form.findField('DAddress_Flat').setValue('');
			base_form.findField('DAddress_Address').setValue('');
			base_form.findField('DAddress_AddressText').setValue('');
			base_form.findField('DAddress_AddressText').disable();
			base_form.findField('DAddress_AddressText').setAllowBlank(true);
		} else {
			base_form.findField('DAddress_AddressText').enable();
			base_form.findField('DAddress_AddressText').setAllowBlank(false);
		}
	},
	layout: 'border',
	maximizable: true,
	minHeight: 500,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	clearValues: function () {
		var base_form = this.findById('MedSvidDeathEditForm').getForm();
		base_form.findField('Person_mid').setValue(null);
		base_form.findField('Person_m_FIO').setValue(null);
		base_form.findField('Mother_BirthDay').setValue(null);
		base_form.findField('Person_rid').setValue(null);
		base_form.findField('Person_r_FIO').setValue(null);
		base_form.findField('DeathSvidType_id').setValue(null);
		base_form.findField('ReceptType_id').setValue(2);
		base_form.findField('DeathCause_id').setValue(null);
		base_form.findField('DeathSvid_IsNoPlace').setValue(null);
        base_form.findField('DeathSvid_isBirthDate').setValue(null);
		base_form.findField('DeathFamilyStatus_id').clearValue();
		base_form.findField('DeathPlace_id').setValue(null);
		base_form.findField('DeathEducation_id').setValue(null);
		base_form.findField('DeathTrauma_id').setValue(null);
		base_form.findField('DeathSetType_id').setValue(null);
		base_form.findField('DeathSetCause_id').setValue(null);
		base_form.findField('DeathWomanType_id').setValue(null);
		base_form.findField('DeathEmployment_id').setValue(null);
		base_form.findField('DtpDeathTime_id').setValue(null);
		base_form.findField('ChildTermType_id').setValue(null);
		base_form.findField('BAddress_Zip').setValue(null);
		base_form.findField('BKLCountry_id').setValue(null);
		base_form.findField('BKLRGN_id').setValue(null);
		base_form.findField('BKLSubRGN_id').setValue(null);
		base_form.findField('BKLCity_id').setValue(null);
		base_form.findField('BKLTown_id').setValue(null);
		base_form.findField('BKLStreet_id').setValue(null);
		base_form.findField('BAddress_House').setValue(null);
		base_form.findField('BAddress_Corpus').setValue(null);
		base_form.findField('BAddress_Flat').setValue(null);
		base_form.findField('BAddress_Address').setValue(null);
		base_form.findField('BAddress_AddressText').setValue(null);
		base_form.findField('DAddress_Zip').setValue(null);
		base_form.findField('DKLCountry_id').setValue(null);
		base_form.findField('DKLRGN_id').setValue(null);
		base_form.findField('DKLSubRGN_id').setValue(null);
		base_form.findField('DKLCity_id').setValue(null);
		base_form.findField('DKLTown_id').setValue(null);
		base_form.findField('DKLStreet_id').setValue(null);
		base_form.findField('DAddress_House').setValue(null);
		base_form.findField('DAddress_Corpus').setValue(null);
		base_form.findField('DAddress_Flat').setValue(null);
		base_form.findField('DAddress_Address').setValue(null);
		base_form.findField('DAddress_AddressText').setValue(null);
		base_form.findField('Diag_iid').setValue(null);
		base_form.findField('Diag_tid').setValue(null);
		base_form.findField('Diag_mid').setValue(null);
		base_form.findField('Diag_eid').setValue(null);
		base_form.findField('Diag_oid').setValue(null);
		base_form.findField('DeathSvid_IsPrimDiagIID').setValue(null);
		base_form.findField('DeathSvid_IsPrimDiagTID').setValue(null);
		base_form.findField('DeathSvid_IsPrimDiagMID').setValue(null);
		base_form.findField('DeathSvid_IsPrimDiagEID').setValue(null);
		base_form.findField('DeathSvid_Ser').setValue(null);
		base_form.findField('DeathSvid_Num').setValue(null);
		base_form.findField('DeathSvid_OldSer').setValue(null);
		base_form.findField('DeathSvid_OldNum').setValue(null);
		base_form.findField('DeathSvid_DeathDate_Date').setValue(null);
		base_form.findField('DeathSvid_DeathDate_Date').fireEvent('change', base_form.findField('DeathSvid_DeathDate_Date'), base_form.findField('DeathSvid_DeathDate_Date').getValue());
		base_form.findField('DeathSvid_DeathDate_Time').setValue(null);
		base_form.findField('DeathSvid_IsNoDeathTime').setValue(null);
        base_form.findField('DeathSvid_IsNoAccidentTime').setValue(null);
		base_form.findField('DeathSvid_Mass').setValue(null);
		base_form.findField('DeathSvid_Month').setValue(null);
		base_form.findField('DeathSvid_Day').setValue(null);
		base_form.findField('DeathSvid_ChildCount').setValue(null);
		base_form.findField('DeathSvid_TraumaDate_Date').setValue(null);
		base_form.findField('DeathSvid_TraumaDate_Time').setValue(null);
		base_form.findField('DeathSvid_TraumaDescr').setValue(null);
		base_form.findField('DeathSvid_Oper').setValue(null);
		base_form.findField('DeathSvid_PribPeriod').setValue(null);
		base_form.findField('DeathSvid_PribPeriodPat').setValue(null);
		base_form.findField('DeathSvid_PribPeriodDom').setValue(null);
		base_form.findField('DeathSvid_PribPeriodExt').setValue(null);
		base_form.findField('DeathSvid_PribPeriodImp').setValue(null);
		base_form.findField('DeathSvid_RcpDate').setValue(null);
		base_form.findField('DeathSvid_GiveDate').setValue(new Date());
		base_form.findField('DeathSvid_OldGiveDate').setValue(null);
		base_form.findField('DeathSvid_OldGiveDate').fireEvent('change', base_form.findField('DeathSvid_OldGiveDate'), base_form.findField('DeathSvid_OldGiveDate').getValue());
		base_form.findField('DeathSvid_RcpDocument').setValue(null);
		base_form.findField('LpuSection_id').setValue(null);
		base_form.findField('MedStaffFact_id').setValue(null);
		base_form.findField('OrgHead_id').setValue(null);

		Ext.getCmp('DeathSvid_OldSer').disable();
		Ext.getCmp('DeathSvid_OldNum').disable();
		Ext.getCmp('DeathSvid_OldGiveDate').disable();
	},
	onChangeReceptType: function(rectype) {
		var win = this;
		var base_form = win.findById('MedSvidDeathEditForm').getForm();

		base_form.findField('DeathSvid_Ser').setValue('');
		base_form.findField('DeathSvid_Num').setValue('');

		if (rectype == 1) {
			base_form.findField('DeathSvid_Num').enable();
			if (getRegionNick() == 'ufa') {
				base_form.findField('DeathSvid_Ser').disable();
			} else {
				base_form.findField('DeathSvid_Ser').enable();
			}
			win.generateNewNumber(true);
		} else if(rectype == 2){
			base_form.findField('DeathSvid_Ser').disable();
			base_form.findField('DeathSvid_Num').disable();
			win.generateNewNumber(true);
		}
	},
	isViewMode: function() {
		if (this.action == 'add' || (this.action == 'edit' && this.modeNewSvid > 0)) {
			return false;
		}

		return true;
	},
	onCheckIsUnknownDeathDate: function() {
		if (getRegionNick() == 'ekb') {
			var form = this.findById('MedSvidDeathEditForm');
			var pers_form = this.findById('MSDEF_PersonInformationFrame');
			var base_form = form.getForm();

			// Если установлен флаг «Дата смерти неизвестна» ИЛИ дата рождения Человека не заполнена, то поле не активное.
			if (base_form.findField('DeathSvid_IsUnknownDeathDate').checked || Ext.isEmpty(pers_form.getFieldValue('Person_Birthday'))) {
				base_form.findField('ChildTermType_id').disable();
				base_form.findField('ChildTermType_id').clearValue();
				base_form.findField('DeathSvid_Mass').disable();
				base_form.findField('DeathSvid_Mass').setValue(null);
				base_form.findField('DeathSvid_ChildCount').disable();
				base_form.findField('DeathSvid_ChildCount').setValue(null);
				base_form.findField('Mother_Age').disable();
				base_form.findField('Mother_Age').setValue(null);
			} else if (this.action != 'view') {
				base_form.findField('ChildTermType_id').enable();
				base_form.findField('DeathSvid_Mass').enable();
				base_form.findField('DeathSvid_ChildCount').enable();
				base_form.findField('Mother_Age').enable();
			}
		}
	},
	filterDeathSetCause: function() {
		if (getRegionNick() == 'ekb') {
			var win = this;
			var base_form = win.findById('MedSvidDeathEditForm').getForm();

			if (base_form.findField('DeathSetType_id').getValue() == 4 || base_form.findField('DeathSetType_id').getValue() == 5) {
				base_form.findField('DeathSetCause_id').lastQuery = '';
				base_form.findField('DeathSetCause_id').getStore().filterBy(function (rec) {
					if (rec.get('DeathSetCause_id') != 4) {
						return false;
					} else {
						return true;
					}
				});

				if (base_form.findField('DeathSetCause_id').getValue() != 4) {
					base_form.findField('DeathSetCause_id').clearValue();
				}
			} else {
				base_form.findField('DeathSetCause_id').lastQuery = '';
				base_form.findField('DeathSetCause_id').getStore().filterBy(function (rec) {
					if (rec.get('DeathSetCause_id') == 4) {
						return false;
					} else {
						return true;
					}
				});

				if (base_form.findField('DeathSetCause_id').getValue() == 4) {
					base_form.findField('DeathSetCause_id').clearValue();
				}
			}
		}
	},
	getDeathDiagByStore: function(filter_name, Diag_id) {
		var win = this;
		var rec = false;

		var pers_form = this.findById('MSDEF_PersonInformationFrame');
		var person_age = win.birthDateToAge(pers_form.getFieldValue('Person_Birthday'));
		person_age = ("000"+person_age[0]).substr(-3,3) + ("00"+person_age[1]).substr(-2,2) + ("00"+person_age[2]).substr(-2,2);

		win.DeathDiagStore.each(function(r) {
			if (r.get('Diag_id') != Diag_id) {
				return;
			}

			var ageFrom = ("000"+r.get('DeathDiag_YearFrom')).substr(-3,3) + ("00"+r.get('DeathDiag_MonthFrom')).substr(-2,2) + ("00"+r.get('DeathDiag_DayFrom')).substr(-2,2);
			var ageTo = ("000"+r.get('DeathDiag_YearTo')).substr(-3,3) + ("00"+r.get('DeathDiag_MonthTo')).substr(-2,2) + ("00"+r.get('DeathDiag_DayTo')).substr(-2,2);
			if ( r.get(filter_name) == 2 ) {
				if (
					(
						(parseInt(ageFrom) == 0 || ageFrom < person_age )
						&& (parseInt(ageTo) == 0 || ageTo > person_age )
					) &&
					(Ext.isEmpty(r.get('Sex_id')) || r.get('Sex_id') == pers_form.getFieldValue('Sex_Code'))
				) {
					if (
						!rec // или ещё нет записи
						|| (!rec.get('Region_id') && r.get('Region_id')) // или была "Общая", а стала "Региональная"
						|| (rec.get('Region_id') == r.get('Region_id') && rec.get('DeathDiag_IsUsed') != 2 && r.get('DeathDiag_IsUsed') == 2) // была не "используемая", а стала "используемая"
						|| (rec.get('Region_id') == r.get('Region_id') && rec.get('DeathDiag_IsUsed') == r.get('DeathDiag_IsUsed') && rec.get('DeathDiag_IsNotUsed') != 2 && r.get('DeathDiag_IsNotUsed') == 2) // или была "Маловероятная", а стала "Не используемая"
						|| (rec.get('Region_id') == r.get('Region_id') && rec.get('DeathDiag_IsUsed') == r.get('DeathDiag_IsUsed') && rec.get('DeathDiag_IsNotUsed') == r.get('DeathDiag_IsNotUsed') && rec.get('DeathDiag_CriteriaCount') > r.get('DeathDiag_CriteriaCount')) // или кол-во критериев по полу/возрасту больше
					) {
						if(!(getRegionNick()=='perm' && Ext.isEmpty(r.get('Region_id')) && r.get('DeathDiag_IsNotUsed')==2) ) //#146758
						rec = r;
					}
				}
			}
		});

		// log('getDeathDiagByStore', filter_name, Diag_id, rec);

		return rec;
	},
	onPersonDeathAgeChange: function() {

		var win = this;
		var base_form = win.findById('MedSvidDeathEditForm').getForm();

		var onDate = getValidDT(getGlobalOptions().date, '');
		var date = null;
		if (base_form.findField('DeathSvid_DeathDate_Date').getValue()) {
			onDate = base_form.findField('DeathSvid_DeathDate_Date').getValue();
			date = base_form.findField('DeathSvid_DeathDate_Date').getValue();
		}
		var person_frame = this.findById('MSDEF_PersonInformationFrame');
		var Person_Birthday = person_frame.getFieldValue('Person_Birthday');

        base_form.findField('DeathEmployment_id').getStore().clearFilter();
        base_form.findField('DeathEducation_id').getStore().clearFilter();
		if (getRegionNick() == 'ekb') {

			// Если заполнена дата рождения матери (блок «Для детей, умерших в возрасте до 1 года») И возраст матери меньше 18 лет, то недоступно для выбора значение 'руководители и специалисты высшего уровня квалификации».
			// Если возраст умершего меньше 19 лет, то недоступно для выбора значение 'руководители и специалисты высшего уровня квалификации».
			if (
				(onDate && Person_Birthday && swGetPersonAge(Person_Birthday, onDate) < 19)
				|| (base_form.findField('Mother_BirthDay').getValue() && swGetPersonAge(base_form.findField('Mother_BirthDay').getValue()) < 18)
			) {
				base_form.findField('DeathEmployment_id').getStore().filterBy(function(rec) {
					if (rec.get('DeathEmployment_Code') && rec.get('DeathEmployment_Code').toString().inlist(['1'])) {
						return false;
					} else {
						return true;
					}
				});
			}
			base_form.findField('DeathEmployment_id').lastQuery = '';

			if (onDate && Person_Birthday && swGetPersonAge(Person_Birthday, onDate) >= 1 && swGetPersonAge(Person_Birthday, onDate) <= 10) {
				// Если возраст Человека от 1 года до 10 лет, то для выбора доступны значения: «8 Не имеет начального образования» или «9 Неизвестно».
				base_form.findField('DeathEducation_id').getStore().filterBy(function(rec) {
					if (rec.get('DeathEducation_Code') && rec.get('DeathEducation_Code').toString().inlist(['8','9'])) {
						return true;
					} else {
						return false;
					}
				});
			} else if (
				(onDate && Person_Birthday && swGetPersonAge(Person_Birthday, onDate) >= 11 && swGetPersonAge(Person_Birthday, onDate) <= 15)
				|| (base_form.findField('Mother_BirthDay').getValue() && swGetPersonAge(base_form.findField('Mother_BirthDay').getValue()) < 16)
			) {
				// Если возраст человека от 11 лет до 15, то для выбора доступны значения: «Начальное», «Основное», «8 Не имеет начального образования» или «9 Неизвестно».
				// Если заполнена дата рождения матери (блок «Для детей, умерших в возрасте до 1 года») И возраст матери меньше 16 лет, то доступны для выбора «Начальное», «Основное», «8 Не имеет начального образования» или «9 Неизвестно».
				base_form.findField('DeathEducation_id').getStore().filterBy(function(rec) {
					if (rec.get('DeathEducation_Code') && rec.get('DeathEducation_Code').toString().inlist(['6','7','8','9'])) {
						return true;
					} else {
						return false;
					}
				});
			} else if (onDate && Person_Birthday && swGetPersonAge(Person_Birthday, onDate) >= 16 && swGetPersonAge(Person_Birthday, onDate) <= 18) {
				// Если возраст человека от 15 до 18 лет, то для выбора доступны значения «Среднее», «Начальное», «Основное», «8 Не имеет начального образования» или «9 Неизвестно».
				base_form.findField('DeathEducation_id').getStore().filterBy(function(rec) {
					if (rec.get('DeathEducation_Code') && rec.get('DeathEducation_Code').toString().inlist(['3','5','6','7','8','9'])) {
						return true;
					} else {
						return false;
					}
				});
			}

			base_form.findField('DeathEducation_id').lastQuery = '';

			// Если заполнены «дата рождения» и «дата смерти» и возраст Человека  (на дату смерти) больше или равен 14 лет, то поле "Занятость" обязательно для заполнения.
			if (date && Person_Birthday && swGetPersonAge(Person_Birthday, date) >= 14) {
				base_form.findField('DeathEmployment_id').setAllowBlank(false);
			} else {
				base_form.findField('DeathEmployment_id').setAllowBlank(true);
			}

			// Если известны дата смерти и дата рождения И возраст человека больше 18 лет, то поле "Семейное положение" обязательно для заполнения.
			if (date && Person_Birthday && swGetPersonAge(Person_Birthday, date) > 18) {
				base_form.findField('DeathFamilyStatus_id').setAllowBlank(false);
			} else {
				base_form.findField('DeathFamilyStatus_id').setAllowBlank(true);
			}

			// Если известны дата смерти и дата рождения И возраст (на дату смерти) человека больше 10 лет, то поле "Образование" обязательно для заполнения.
			if (date && Person_Birthday && swGetPersonAge(Person_Birthday, date) > 10) {
				base_form.findField('DeathEducation_id').setAllowBlank(false);
			} else {
				base_form.findField('DeathEducation_id').setAllowBlank(true);
			}
		}

		if ( getRegionNick().inlist( ['msk', 'vologda'] ) ) {
			// #195193 Блок "Для детей, умерших в возрасте до 1 года" обязателен для заполнения
			if (onDate && Person_Birthday && swGetPersonAge(Person_Birthday, onDate) < 1) {
				base_form.findField('Person_mid').setAllowBlank(false);
				base_form.findField('ChildTermType_id').setAllowBlank(false);
				base_form.findField('DeathSvid_Mass').setAllowBlank(false);
				base_form.findField('DeathSvid_ChildCount').setAllowBlank(false);
				base_form.findField('DeathSvid_Month').setAllowBlank(false);
				base_form.findField('Mother_Age').setAllowBlank(false);
				base_form.findField('Mother_BirthDay').setAllowBlank(false);
			}else{
				base_form.findField('Person_mid').setAllowBlank(true);
				base_form.findField('ChildTermType_id').setAllowBlank(true);
				base_form.findField('DeathSvid_Mass').setAllowBlank(true);
				base_form.findField('DeathSvid_ChildCount').setAllowBlank(true);
				base_form.findField('DeathSvid_Month').setAllowBlank(true);
				base_form.findField('Mother_Age').setAllowBlank(true);
				base_form.findField('Mother_BirthDay').setAllowBlank(true);
			}

            // #195193 фильтр занятостей для возраста от 1 до 18
            if (onDate && Person_Birthday && swGetPersonAge(Person_Birthday, onDate) >= 1 && swGetPersonAge(Person_Birthday, onDate) <= 18) {
                base_form.findField('DeathEmployment_id').getStore().filterBy(function(rec) {
                    if (rec.get('DeathEmployment_Code') && rec.get('DeathEmployment_Code').toString().inlist(['1','2','3','5','6'])) {
                        return false;
                    } else {
                        return true;
                    }
                });
                // #195193 фильтр образования для возраста от 1 до 18
                base_form.findField('DeathEducation_id').getStore().filterBy(function(rec) {
                    if (rec.get('DeathEducation_Code') && rec.get('DeathEducation_Code').toString().inlist(['1'])) {
                        return false;
                    } else {
                        return true;
                    }
                });
            }

            if (onDate && Person_Birthday && swGetPersonAge(Person_Birthday, onDate) >= 1 && swGetPersonAge(Person_Birthday, onDate) <= 14) {
                // #195193 фильтр семейного статуса для возраста от 1 до 14
                base_form.findField('DeathFamilyStatus_id').getStore().filterBy(function(rec) {
                    if (rec.get('DeathFamilyStatus_Code') && rec.get('DeathFamilyStatus_Code').toString().inlist(['1'])) {
                        return false;
                    } else {
                        return true;
                    }
                });
            }
		}

	},
	deathCauseDateBlock: function(){
		var win = this;
		var base_form = win.findById('MedSvidDeathEditForm').getForm();
		if ( getRegionNick().inlist( ['msk', 'vologda'] ) ) {
			// обязательность блока "Дата и время начала случая"
			var deathCause = base_form.findField('DeathCause_id').getValue().toString();
			if( deathCause.inlist(['2','3','4','5','7']) ){
				if( !base_form.findField('DeathSvid_TraumaDateStr').getValue() || base_form.findField('DeathSvid_TraumaDateStr').getValue()==base_form.findField('DeathSvid_TraumaDateStr').plugins[0].viewMask ){
					base_form.findField('DeathSvid_TraumaDate_Date').setAllowBlank(false);
				}
				if( !base_form.findField('DeathSvid_IsNoAccidentTime').getValue() ) {
					base_form.findField('DeathSvid_TraumaDate_Time').setAllowBlank(false);
				}
			}else{
				base_form.findField('DeathSvid_TraumaDate_Date').setAllowBlank(true);
				base_form.findField('DeathSvid_TraumaDate_Time').setAllowBlank(true);
			}
		}
	},
	show: function() {
		sw.Promed.swMedSvidDeathEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var args = arguments;
		if (win.DeathDiagStore.getCount() == 0) {
			// сначала грузим сторе
			win.getLoadMask('Загрузка справочника доступных диагнозов...').show();
			win.DeathDiagStore.load({
				callback: function() {
					win.getLoadMask().hide();
					// загрузили? молодцы!
					win.onShow.apply(win, args);
				}
			});
		} else {
			win.onShow.apply(win, args);
		}
	},
	onShow: function() {
		var win = this;
		win.needLpuSectionForNumGeneration = true;
		win.lastParamsForNumGeneration = null;

		var person_id = 0;
		var server_id = 0;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		var form = this.findById('MedSvidDeathEditForm');
		var pers_form = this.findById('MSDEF_PersonInformationFrame');
		var base_form = form.getForm();

		if (arguments && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments && arguments[0].PersonData) {
			this.PersonData = arguments[0].PersonData;
		} else {
			this.PersonData = null;
		}

		if (arguments[0].callback  && typeof arguments[0].callback == 'function')
		{
			this.callback = arguments[0].callback;
		}

		this.modeNewSvid = 0;
		if (arguments && arguments[0].modeNewSvid) {
			this.modeNewSvid = arguments[0].modeNewSvid;
		}
		this.saveMode = 1;

		var title = lang['svidetelstvo_o_smerti'];
		switch (this.action) {
			case 'add':
				title += lang['_dobavlenie'];
				break;
			case 'edit':
				title += lang['_redaktirovanie'];
				break;
			case 'view':
				title += lang['_prosmotr'];
				break;
		}
		this.setTitle(title);

		base_form.reset();
		win.hiddenBlockErrorContainerReset();
		this.enableEdit(this.action != 'view');

		this.restore();
		this.center();
		this.maximize();
		loadMask.show();
		this.findById('MedSvidDeathEditWindowTab').setActiveTab(1);
		this.findById('MedSvidDeathEditWindowTab').setActiveTab(0);

		if (this.action == 'add') this.clearValues();

        base_form.findField('DeathSvid_isBirthDate').setValue(false);
		base_form.findField('DeathSvid_DeathDate_Date').filterDate = null;
		this.checkPrimDiagHold = false;
		base_form.items.each(function(f) {
            if (f.getXType() == 'swdscheckbox') {
                f.disable();
            }
        });

		var isufa = (getRegionNick() == 'ufa');

		//максимальные значения
		base_form.findField('DeathSvid_GiveDate').setMaxValue(Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
		base_form.findField('DeathSvid_DeathDate_Date').setMaxValue(Date.parseDate(getGlobalOptions().date, 'd.m.Y'));

		if (getRegionNick() == 'ekb') {

			var Year = getGlobalOptions().date.substr(6, 4);

			//минимальные значения
			//исключил по https://redmine.swan.perm.ru/issues/105724
			//base_form.findField('DeathSvid_GiveDate').setMinValue(Date.parseDate('01.01.' + Year, 'd.m.Y'));
			//base_form.findField('DeathSvid_DeathDate_Date').setMinValue(Date.parseDate('01.01.2011', 'd.m.Y'));

			setLpuSectionGlobalStoreFilter();
			setMedStaffFactGlobalStoreFilter({disableInDoc:true});

			base_form.findField('LpuSection_did').showContainer();
			base_form.findField('MedStaffFact_did').showContainer();
			base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
			base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
			var medPersonal = getGlobalOptions().medpersonal_id;
			var msf_index = base_form.findField('MedStaffFact_did').getStore().findBy(function(rec){
				return (rec.get('MedPersonal_id') == medPersonal);
			});
			if(msf_index != -1 && this.action == 'add'){
				var cur_msf = base_form.findField('MedStaffFact_did').getStore().getAt(msf_index).get('MedStaffFact_id');
				base_form.findField('MedStaffFact_did').setValue(cur_msf);
				base_form.findField('MedStaffFact_did').fireEvent('select',base_form.findField('MedStaffFact_did'),cur_msf);
			}
			var okeis = ['Okei_id','Okei_patid','Okei_domid','Okei_extid','Okei_impid'];
			var periods = ['DeathSvid_TimePeriod','DeathSvid_TimePeriodPat','DeathSvid_TimePeriodDom','DeathSvid_TimePeriodExt','DeathSvid_TimePeriodImp'];
			var ok_ids = ['99','100','101','102','104','107'];
			for(var i = 0;i<okeis.length;i++){
				base_form.findField(okeis[i]).getStore().each(function(rec){
					if (!rec.get('Okei_id').inlist(ok_ids)){
						base_form.findField(okeis[i]).getStore().remove(rec);
					}
				});
				if(this.action == 'add'){
					base_form.findField(okeis[i]).setValue(101);
					base_form.findField(okeis[i]).disable();
					base_form.findField(periods[i]).disable();
				}
			}
		} else {
			base_form.findField('LpuSection_did').hideContainer();
			base_form.findField('MedStaffFact_did').hideContainer();
		}
		if (getRegionNick() == 'kz') {
			base_form.findField('OrgHead_id').hideContainer();
			base_form.findField('MedPersonal_cid').hideContainer();
			base_form.findField('DeathSvid_checkDate').hideContainer();
		}

		switch (this.action) {
			case 'add':
				if (arguments[0].formParams) {
					person_id = arguments[0].formParams.Person_id;
					server_id = arguments[0].formParams.Server_id;
					base_form.findField('Person_id').setValue(person_id);
					base_form.findField('Server_id').setValue(server_id);
					form.getForm().setValues(arguments[0].formParams);
				}

				base_form.findField('DeathSvid_TraumaDescr').setContainerVisible(true);

				base_form.findField('OrgHead_id').getStore().load({
					params: {
						Lpu_id: getGlobalOptions().Lpu_id
					},
					callback: function() {
						var commissDate = {date: 0, OrgHead_id: 0};
						var combo = base_form.findField('OrgHead_id');
						/*
						var index = combo.getStore().findBy(function (rec) {
							return (rec.get('OrgHeadPost_id') == 1);
						});
						if (index >= 0) {
							combo.setValue(combo.getStore().getAt(index).get('OrgHead_id'));
							combo.fireEvent('select', combo, combo.getValue(), null);
						}
						*/
						if(getRegionNick()=='kz'){
							base_form.findField('DeathSvidRelation_id').hideContainer();
						}else{
							if(base_form.findField('DeathSvid_PolFio').getValue()=="")
								base_form.findField('DeathSvid_PolFio').hideContainer();
							else
								base_form.findField('DeathSvid_PolFio').setDisabled();
						}

						combo.getStore().each(function(rec) {
							if (rec.get('OrgHeadPost_id') == 1) {
								var dateCommiss = rec.get('OrgHead_CommissDate');
								if (dateCommiss && dateCommiss > commissDate.date) {
									commissDate.date = dateCommiss;
									commissDate.OrgHead_id = rec.get('OrgHead_id');
								} else if (commissDate.OrgHead_id == 0) {
									commissDate.OrgHead_id = rec.get('OrgHead_id')
								}
							}
						});
						if (commissDate.OrgHead_id > 0) {
							combo.setValue(commissDate.OrgHead_id);
							combo.fireEvent('select', combo, combo.getValue(), null);
						}
					}
				});

				pers_form.load({Person_id: person_id, Server_id: server_id, callback: function() {
					if (win.action) win.keepMotherFields(null);

					win.onCheckIsUnknownDeathDate();
					win.onPersonDeathAgeChange();

					base_form.findField('DeathWomanType_id').hideContainer();
					if (pers_form.getFieldValue('Sex_Code') == '2' && pers_form.getFieldValue('Person_Age') >= 10) {
						base_form.findField('DeathWomanType_id').showContainer();
					}
                    var birthdt = Ext.getCmp('MSDEF_PersonInformationFrame').getFieldValue("Person_Birthday");
                    if( typeof birthdt != 'object' )
                    {
                        base_form.findField('DeathSvid_isBirthDate').setValue(true);
                        base_form.findField('DeathSvid_BirthDateStr').enable();
                        base_form.findField('DeathSvid_BirthDateStr').setValue('');
                    }
                    else
                    {
                        base_form.findField('DeathSvid_BirthDateStr').disable();
                        base_form.findField('DeathSvid_BirthDateStr').setValue(birthdt.format('d.m.Y'));
                    }
					// если личность не установлена то показываем дату рождения, иначе нет
					/*base_form.findField('DeathSvid_BirthDateStr').showContainer();
					if (pers_form.getFieldValue('Person_IsUnknown') == 2) {
						base_form.findField('DeathSvid_BirthDateStr').showContainer();
					}*/
					
					win.filterDiagCombo();
				}});

				win.onChangeReceptType(base_form.findField('ReceptType_id').getValue());

				setCurrentDateTime({
					callback: function () {
						base_form.findField('DeathSvid_GiveDate').fireEvent('change', base_form.findField('DeathSvid_GiveDate'), base_form.findField('DeathSvid_GiveDate').getValue());
						base_form.findField('DeathSvid_GiveDate').focus(true, 0);

					},
					dateField: base_form.findField('DeathSvid_GiveDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: false,
					timeField: base_form.findField('DeathSvid_GiveDate'),
					windowId: 'MedSvidDeathEditWindow'
				});
				break;
			case 'view':
			case 'edit':
				if (arguments[0].formParams) {
					var svid_id = arguments[0].formParams.DeathSvid_id;
				}

				var hideTraumaDescr = arguments[0].formParams && arguments[0].formParams.LpuType_Code && arguments[0].formParams.LpuType_Code == '111' && isUserGroup('ZagsUser');

				base_form.findField('DeathSvid_TraumaDescr').setContainerVisible(!hideTraumaDescr);

				win.getLoadMask(LOAD_WAIT).show();
				base_form.load({
					failure: function () {
						win.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_svidetelstva'], function () {
							this.hide();
						}.createDelegate(this));
					}.createDelegate(this),
					params: {
						svid_id: svid_id,
						svid_type: 'death'
					},
					success: function (store) {
						log(store);
						log(base_form.findField('DeathSvid_IsUnknownDeathDate').getValue());
						win.getLoadMask().hide();

						if(getRegionNick()=='kz'){
							base_form.findField('DeathSvidRelation_id').hideContainer();
						}else{
							if(base_form.findField('DeathSvid_PolFio').getValue()=="")
								base_form.findField('DeathSvid_PolFio').hideContainer();
							else
								base_form.findField('DeathSvid_PolFio').setDisabled();
						}

						base_form.findField('DeathSvid_RcpDocument').setDisabled(true);

						if (this.modeNewSvid > 0) {
							base_form.findField('DeathSvid_OldSer').setValue(base_form.findField('DeathSvid_Ser').getValue());
							base_form.findField('DeathSvid_OldNum').setValue(base_form.findField('DeathSvid_Num').getValue());
							base_form.findField('DeathSvid_OldGiveDate').setValue(base_form.findField('DeathSvid_GiveDate').getValue().format('d.m.Y'));
							base_form.findField('DeathSvid_OldGiveDate').fireEvent('change', base_form.findField('DeathSvid_OldGiveDate'), base_form.findField('DeathSvid_OldGiveDate').getValue());
							base_form.findField('DeathSvid_GiveDate').setValue(null);
							base_form.findField('DeathSvid_RcpDate').setValue(null);
							win.onChangeReceptType(base_form.findField('ReceptType_id').getValue());
							base_form.findField('DeathSvid_predid').setValue(svid_id); // это при сохранении станет неактуальным
							base_form.findField('DeathSvid_RcpDate').setValue(null);

							switch (this.modeNewSvid) {
								case 1:
									// Новое м/с получает флаг "Дубликат" = 2, а старое становится утерянным.
									base_form.findField('DeathSvid_IsDuplicate').setValue(2);
									break;

								case 2:
									// вид становится «Взамен предварительного»
									base_form.findField('DeathSvidType_id').setValue(3);
									base_form.findField('DeathSvid_IsDuplicate').setValue(1);
									break;

								case 3:
									// вид становится «Окончательное»
									base_form.findField('DeathSvidType_id').setValue(1);
									base_form.findField('DeathSvid_IsDuplicate').setValue(1);
									break;

								case 4:
									// вид становится «Взамен окончательного»
									base_form.findField('DeathSvidType_id').setValue(4);
									base_form.findField('DeathSvid_IsDuplicate').setValue(1);
									break;

								case 5:
									// становится неутерянным
									base_form.findField('DeathSvid_IsLose').setValue(1);
									base_form.findField('DeathSvid_IsDuplicate').setValue(1);
									break;

								case 6:
									// становится неутерянным
									base_form.findField('DeathSvid_IsLose').setValue(1);
									base_form.findField('DeathSvid_IsDuplicate').setValue(1);
									if (win.PersonData && win.PersonData.Person_id) {
										base_form.findField('Person_id').setValue(win.PersonData.Person_id);
										base_form.findField('Server_id').setValue(win.PersonData.Server_id);
									}
									break;
							}

							base_form.findField('DeathSvidType_id').disable();

							if (this.action == 'edit') {
								var date_date_unknown = base_form.findField('DeathSvid_IsUnknownDeathDate').getValue();
								var date_time_unknown = base_form.findField('DeathSvid_IsNoDeathTime').getValue();
								var deathDataField = base_form.findField('DeathSvid_DeathDate_Date');
								if(base_form.findField('DeathSvid_DeathDateStr').getValue()){// #131621, если дата смерти есть в поле "Неуточ. дата смерти"
									deathDataField.setAllowBlank(true);
									if(getRegionNick().inlist(['ufa'])){
										deathDataField.setDisabled(true);// то блокируем обязательное поле "Дата, время смерти" для Башкирии
										base_form.findField('DeathSvid_DeathDate_Time').setDisabled(true);
									}
									deathDataField.validate();// подсветка
								}else{
									deathDataField.setDisabled(date_date_unknown);
								}
								base_form.findField('DeathSvid_DeathDate_Time').setDisabled(date_date_unknown || date_time_unknown);
								base_form.findField('DeathSvid_DeathDateStr').setDisabled(date_date_unknown);

								var dstype = base_form.findField('DeathSvidType_id').getValue();
								if (dstype == 3 || dstype == 4) {
									base_form.findField('DeathSvid_OldSer').enable();
									base_form.findField('DeathSvid_OldNum').enable();
									base_form.findField('DeathSvid_OldGiveDate').enable();
								} else {
									base_form.findField('DeathSvid_OldSer').disable();
									base_form.findField('DeathSvid_OldNum').disable();
									base_form.findField('DeathSvid_OldGiveDate').disable();
								}
							}
						} else {
							// иначе редактирование в части сведений о получателе без выписки нового свидетельства
							// дисаблим форму
							this.enableEdit(false);
							// раздисабливаем только поля получателя и кнопку сохранить
							if ( win.action == 'edit' ) {
								base_form.findField('Person_rid').enable();
								base_form.findField('DeathSvid_PolFio').enable();
								base_form.findField('DeathSvid_RcpDocument').enable();
								base_form.findField('DeathSvid_RcpDate').enable();
								base_form.findField('DeathSvidRelation_id').enable();
								// сохраняем только получателя
								win.saveMode = 2;
								win.buttons[0].show();
							}
						}
						person_id = base_form.findField('Person_id').getValue();
						server_id = base_form.findField('Server_id').getValue();

						pers_form.load({Person_id: person_id, Server_id: server_id, callback: function() {
							if (win.action) win.keepMotherFields(null);

							win.onPersonDeathAgeChange();

							base_form.findField('DeathWomanType_id').hideContainer();
							if (pers_form.getFieldValue('Sex_Code') == '2' && pers_form.getFieldValue('Person_Age') >= 10) {
								base_form.findField('DeathWomanType_id').showContainer();
							}

							// если личность не установлена то показываем дату рождения, иначе нет
							/*base_form.findField('DeathSvid_BirthDateStr').hideContainer();
							if (pers_form.getFieldValue('Person_IsUnknown') == 2) {
								base_form.findField('DeathSvid_BirthDateStr').showContainer();
							}*/
						}});

						setCurrentDateTime({
							callback: function () {
								base_form.findField('DeathSvid_GiveDate').fireEvent('change', base_form.findField('DeathSvid_GiveDate'), base_form.findField('DeathSvid_GiveDate').getValue());
								base_form.findField('DeathSvid_GiveDate').focus(true, 0);
							}.createDelegate(this),
							dateField: base_form.findField('DeathSvid_GiveDate'),
							loadMask: false,
							setDate: true,
							setDateMaxValue: true,
							setDateMinValue: false,
							setTime: false,
							timeField: base_form.findField('DeathSvid_GiveDate'),
							windowId: 'MedSvidDeathEditWindow'
						});

						var dcombo_arr = ['Diag_iid', 'Diag_tid', 'Diag_mid', 'Diag_eid', 'Diag_oid']; //инициализация диагнозов
						for (var i = 0; i < dcombo_arr.length; i++) {
							var diag_combo = base_form.findField(dcombo_arr[i]);
							var diag_id = diag_combo.getValue();
							if (diag_id != '') {
								diag_combo.getStore().combo_id = dcombo_arr[i];
								diag_combo.getStore().load({
									params: {where: "where Diag_id = " + diag_id},
									callback: function (data) {
										//непростой способ добычи ид комбобокса
										var combo_id = data[0] && data[0].store.combo_id ? data[0].store.combo_id : '';
										if (combo_id != '') {
											var diag_combo = base_form.findField(combo_id);
											diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
										}
									}
								});
							}
						}

						var p_mid = base_form.findField('Person_mid').getValue(); //инициализация ФИО
						if (base_form.findField('Person_mid').getValue() > 0) {
							base_form.findField('Person_mid').getStore().loadData([{
								Person_id: p_mid,
								Person_Fio: base_form.findField('Person_m_FIO').getValue()
							}]);
							base_form.findField('Person_mid').setValue(p_mid);
						} else
							base_form.findField('Person_mid').getStore().removeAll();

						var nowdate = new Date(); //вычисление возраста матери
						var birdate = base_form.findField('Mother_BirthDay').getValue();
						if (birdate != "") Ext.getCmp('Mother_Age').setValue(nowdate.format('Y') - birdate.format('Y') - (((birdate.format('md') * 1) - (nowdate.format('md') * 1)) > 0 ? 1 : 0));

						var p_rid = base_form.findField('Person_rid').getValue();
						if (base_form.findField('Person_rid').getValue() > 0) {
							base_form.findField('Person_rid').getStore().loadData([{
								Person_id: p_rid,
								Person_Fio: base_form.findField('Person_r_FIO').getValue()
							}]);
							base_form.findField('Person_rid').setValue(p_rid);
							base_form.findField('DeathSvid_PolFio').disable();
						} else {
							base_form.findField('Person_rid').getStore().removeAll();

							if ( !Ext.isEmpty(base_form.findField('DeathSvid_PolFio').getValue()) ) {
								base_form.findField('Person_rid').disable();
							}
						}
						this.filterDiagCombo();

						if (base_form.findField('OrgHead_id').isVisible()) {
							base_form.findField('OrgHead_id').getStore().load({
								params: {
									Lpu_id: base_form.findField('Lpu_id').getValue()
								},
								callback: function () {
									var combo = base_form.findField('OrgHead_id');
									var jsonData = store.reader.jsonData[0];

									if (Ext.isEmpty(combo.getValue()) && jsonData.Person_hFIO) {
										combo.setRawValue(jsonData.Person_hFIO);
									} else if (Ext.isEmpty(combo.getValue())) {
										var index = combo.getStore().findBy(function (rec) {
											return (rec.get('OrgHeadPost_id') == 1);
										});
										if (index >= 0) {
											combo.setValue(combo.getStore().getAt(index).get('OrgHead_id'));
											combo.fireEvent('select', combo, combo.getValue(), null);
										}
									}
								}
							});
						}
					}.createDelegate(this),
					url: '/?c=MedSvid&m=loadMedSvidEditForm'
				});
				break;
		}

		if (this.action != 'view')
			base_form.findField('ReceptType_id').focus(true, 400);

		Ext.getCmp('MedSvidDeathEditWindowTab').ownerCt.doLayout();

		loadMask.hide();
	},
	keepMotherFields: function (death_date) {
		if (this.action != 'view')
			this.setDefaultValues([]);
	},
	setDefaultValues: function (field_array) {
		var wnd = this;
		var field_str = field_array.join(',');

		if (field_str.indexOf('DeathSvid_Day') >= 0 || field_str.indexOf('DeathSvid_Month') >= 0) {
			var day;
			var birth_date = wnd.findById('MSDEF_PersonInformationFrame').getFieldValue("Person_Birthday");
			var death_date = wnd.findById('DeathSvid_DeathDate_Date').getValue();
			if (birth_date != null && birth_date != "" && death_date != null) {
				day = Math.ceil((death_date - birth_date) / 86400000);
			var month = 1 + ((death_date.getFullYear() * 12) + death_date.getMonth()) - ((birth_date.getFullYear() * 12) + birth_date.getMonth());
			}
			if (field_str.indexOf('DeathSvid_Day') >= 0)
				wnd.findById('DeathSvid_Day').setValue(day);
			if (field_str.indexOf('DeathSvid_Month') >= 0)
				wnd.findById('DeathSvid_Month').setValue(month);
		}

		if (field_str.indexOf('DeathSvid_ChildCount') >= 0 || field_str.indexOf('ChildTermType_id') >= 0 || field_str.indexOf('DeathSvid_Mass') >= 0) {
			Ext.Ajax.request({ //заполнение семейного положения
				callback: function (options, success, response) {
					if (success && response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						response_obj = response_obj[0];
						if (response_obj) {
							if (field_str.indexOf('DeathSvid_ChildCount') >= 0 && response_obj.PersonChild_CountChild > 0)
								wnd.findById('DeathSvid_ChildCount').setValue(response_obj.PersonChild_CountChild);
							if (field_str.indexOf('ChildTermType_id') >= 0 && response_obj.ChildTermType_id > 0)
								wnd.findById('ChildTermType_id').setValue(response_obj.ChildTermType_id);
							if (field_str.indexOf('DeathSvid_Mass') >= 0 && response_obj.PersonWeight_Weight != "")
								wnd.findById('DeathSvid_Mass').setValue(response_obj.PersonWeight_Weight);
						}
					}
				},
				params: {
					Person_id: wnd.findById('MSDEF_PersonInformationFrame').getFieldValue("Person_id")
				},
				url: '/?c=MedSvid&m=getDefaultPersonChildValues'
			});
		}
	},
	hiddenBlockErrorContainer: function(act, name){
		if(!name || name == 'Diag_oid') return false;
		var containers=Ext.select('.hiddenBlockErrorContainer'+name);
		containers.each( function(elem){
			if(act){
				elem.dom.innerHTML = '<div style="margin-bottom: 10px;">&nbsp;</div>';
				elem.show();
			}else{
				elem.dom.innerHTML = '';
				elem.hide();
			}
		})
	},
	hiddenBlockErrorContainerReset: function(){
		var containers=Ext.select('.hiddenBlockErrorCont');
		containers.each( function(elem){
			elem.dom.innerHTML = '';
			elem.hide();
		})
	},
	title: WND_MSVID_RECADD,
	width: 700
});


