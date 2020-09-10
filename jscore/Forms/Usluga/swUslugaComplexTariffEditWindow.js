/**
* swUslugaComplexTariffEditWindow - редактирование тарифов на услугу
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      16.07.2012
* @comment      Префикс для id компонентов UCTEW (UslugaComplexTariffEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swUslugaComplexTariffEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaComplexTariffEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swUslugaComplexTariffEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

		if ( typeof options != 'object' ) {
			options = new Object();
        }
		
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var wnd = this;
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if ( Ext.isEmpty(base_form.findField('UslugaComplexTariff_Tariff').getValue()) &&
			Ext.isEmpty(base_form.findField('UslugaComplexTariff_UED').getValue()) &&
			Ext.isEmpty(base_form.findField('UslugaComplexTariff_UEM').getValue())
		) {
			sw.swMsg.alert(lang['oshibka'], lang['odno_iz_poley_tarif_uet_vracha_uet_sr_medpersonala_doljno_byit_zapolnenno'], function() { 
				this.formStatus = 'edit';
			}.createDelegate(this));
			
			return false;
		}
		
		if ( Ext.isEmpty(base_form.findField('UslugaComplexTariff_Name').getValue()) ) {
			var PayType_Name = base_form.findField('PayType_id').getFieldValue('PayType_Name');
			
			if ( !Ext.isEmpty(base_form.findField('UslugaComplexTariff_Tariff').getValue()) ) {
				base_form.findField('UslugaComplexTariff_Name').setValue(lang['tarif'] + PayType_Name);
			} else {
				base_form.findField('UslugaComplexTariff_Name').setValue(lang['uet'] + PayType_Name);
			}		
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();
		
		var formBegDate = base_form.findField('UslugaComplexTariff_begDate').getValue();
		var formEndDate = base_form.findField('UslugaComplexTariff_endDate').getValue();
		
		// проверка. идём по this.uslugaTariffData и смотрим с текущим типом нет ли пересечений по датам.
		var foundTariff = false;
		var needCloseTariff_id = null;
		/* Пока убрали (refs #16811)
		for(var key in this.uslugaTariffData) {
			rec	= this.uslugaTariffData[key];
			
			var recBegDate = rec['UslugaComplexTariff_begDate'];
			var recEndDate = rec['UslugaComplexTariff_endDate'];
			
			if ( rec['UslugaComplexTariff_id'] ) {
				// 4. Для вида оплаты ОМС не может быть одновременно открытым тариф с одинаковым типом тарифа и ЛПУ. (refs #15419)
				if
				(
					(rec['UslugaComplexTariffType_id'] == base_form.findField('UslugaComplexTariffType_id').getValue()) &&
					(rec['Lpu_id'] == base_form.findField('Lpu_id').getValue()) &&
					(rec['LpuSection_id'] == base_form.findField('LpuSection_id').getValue()) &&
					(rec['PayType_id'] == base_form.findField('PayType_id').getValue()) &&
					(
						(rec['PayType_id'] == 1) ||
						(
						// 5. Для прочих видов оплаты не может быть одновременно открытым тариф с одинаковым набором параметров. (refs #15419)
							(rec['LpuLevel_id'] == base_form.findField('LpuLevel_id').getValue()) &&
							(rec['LpuBuilding_id'] == base_form.findField('LpuBuilding_id').getValue()) &&
							(rec['LpuUnit_id'] == base_form.findField('LpuUnit_id').getValue()) &&
							(rec['LpuSectionProfile_id'] == base_form.findField('LpuSectionProfile_id').getValue()) &&
							(rec['MesAgeGroup_id'] == base_form.findField('MesAgeGroup_id').getValue()) &&
							(rec['Sex_id'] == base_form.findField('Sex_id').getValue()) &&
							(rec['LpuUnitType_id'] == base_form.findField('LpuUnitType_id').getValue())
						)
					)
					&& 
					(
					// проверка на пересечение дат.
						(Ext.isEmpty(formEndDate) && Ext.isEmpty(recEndDate)) ||
						(Ext.isEmpty(formEndDate) && !Ext.isEmpty(recEndDate) && recEndDate >= formBegDate) ||
						(Ext.isEmpty(recEndDate) && !Ext.isEmpty(formEndDate) && recBegDate <= formEndDate) ||
						(!Ext.isEmpty(formEndDate) && !Ext.isEmpty(recEndDate) && (recBegDate <= formEndDate && recEndDate >= formBegDate))
					)
				) {
					// если предыдущий тариф незакрытый, то нужно его закрыть, а новый тариф добавляем.
					if (Ext.isEmpty(recEndDate) && formBegDate >= recBegDate && Ext.isEmpty(needCloseTariff_id) && this.formMode == 'local') {
						needCloseTariff_id = rec['UslugaComplexTariff_id'];
					} else {
						foundTariff = true;
					}
				}
			}
		}
		*/
		if (foundTariff) {
			sw.swMsg.alert(lang['oshibka'], lang['uje_est_aktivnyiy_tarif_ukazannogo_tipa_na_dannom_periode'], function() { 
				this.formStatus = 'edit';
				loadMask.hide();
			}.createDelegate(this));
			
			return false;
		}
		
		data.uslugaComplexTariffData = {
			'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
			'EvnUsluga_setDate': base_form.findField('EvnUsluga_setDate').getValue(),
			'needCloseTariff_id': needCloseTariff_id,
			'UslugaComplexTariff_id': base_form.findField('UslugaComplexTariff_id').getValue(),
			'Lpu_id': base_form.findField('Lpu_id').getValue(),
			'LpuBuilding_id': base_form.findField('LpuBuilding_id').getValue(),
			'LpuUnit_id': base_form.findField('LpuUnit_id').getValue(),
			'LpuSection_id': base_form.findField('LpuSection_id').getValue(),
			'MedService_id': base_form.findField('MedService_id').getValue(),
			'Lpu_Name': (Ext.isEmpty(base_form.findField('Lpu_id').getFieldValue('Lpu_Nick'))?'':base_form.findField('Lpu_id').getFieldValue('Lpu_Nick')) +
				(Ext.isEmpty(base_form.findField('LpuBuilding_id').getFieldValue('LpuBuilding_Name'))?'':', '+base_form.findField('LpuBuilding_id').getFieldValue('LpuBuilding_Name')) +
				(Ext.isEmpty(base_form.findField('LpuUnit_id').getFieldValue('LpuUnit_Name'))?'':', '+base_form.findField('LpuUnit_id').getFieldValue('LpuUnit_Name')) +
				(Ext.isEmpty(base_form.findField('LpuSection_id').getFieldValue('LpuSection_Name'))?'':', '+base_form.findField('LpuSection_id').getFieldValue('LpuSection_Name'))+
				(Ext.isEmpty(base_form.findField('MedService_id').getFieldValue('MedService_Nick'))?'':', '+base_form.findField('MedService_id').getFieldValue('MedService_Nick')),
			'UslugaComplexTariff_Tariff': base_form.findField('UslugaComplexTariff_Tariff').getValue(),
			'UslugaComplexTariff_UED': base_form.findField('UslugaComplexTariff_UED').getValue(),
			'UslugaComplexTariff_UEM': base_form.findField('UslugaComplexTariff_UEM').getValue(),
			'PayType_id': base_form.findField('PayType_id').getValue(),
			'LpuLevel_id': base_form.findField('LpuLevel_id').getValue(),
			'LpuSectionProfile_id': base_form.findField('LpuSectionProfile_id').getValue(),
			'LpuUnitType_id': base_form.findField('LpuUnitType_id').getValue(),
			'MesAgeGroup_id': base_form.findField('MesAgeGroup_id').getValue(),
			'Sex_id': base_form.findField('Sex_id').getValue(),
			'VizitClass_id': base_form.findField('VizitClass_id').getValue(),
			'PayType_Name': base_form.findField('PayType_id').getFieldValue('PayType_Name'),
			'UslugaComplexTariffType_id': base_form.findField('UslugaComplexTariffType_id').getValue(),
			'UslugaComplexTariffType_Name': base_form.findField('UslugaComplexTariffType_id').getFieldValue('UslugaComplexTariffType_Name'),
			'UslugaComplexTariff_begDate': formBegDate,
			'UslugaComplexTariff_endDate': formEndDate,
			'UslugaComplexTariff_Name': base_form.findField('UslugaComplexTariff_Name').getValue(),
			'UslugaComplexTariff_Code': base_form.findField('UslugaComplexTariff_Code').getValue(),
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue(),
			'Sex_Name': base_form.findField('Sex_id').getFieldValue('Sex_Name'),
			'VizitClass_Name': base_form.findField('VizitClass_id').getFieldValue('VizitClass_Name'),
			'MesAgeGroup_Name': base_form.findField('MesAgeGroup_id').getFieldValue('MesAgeGroup_Name'),
			'LpuUnitType_Name': base_form.findField('LpuUnitType_id').getFieldValue('LpuUnitType_Name'),
			'LpuSectionProfile_Name': base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Name'),
			'LpuLevel_Name': base_form.findField('LpuLevel_id').getFieldValue('LpuLevel_Name'),
			'pmUser_Name': getGlobalOptions().pmuser_name
		};

		log(data);
		
		var params = {};
		
		if (options.ignoreEndDate) {
			params.ignoreEndDate = 1;
		}

		if (base_form.findField('UslugaComplexTariff_id').disabled) {
			params.UslugaComplexTariff_id = base_form.findField('UslugaComplexTariff_id').getValue();
		}
		if (base_form.findField('UslugaComplex_id').disabled) {
			params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		}
		if (base_form.findField('UslugaComplexTariff_Code').disabled) {
			params.UslugaComplexTariff_Code = base_form.findField('UslugaComplexTariff_Code').getValue();
		}
		if (base_form.findField('UslugaComplexTariff_Name').disabled) {
			params.UslugaComplexTariff_Name = base_form.findField('UslugaComplexTariff_Name').getValue();
		}
		if (base_form.findField('PayType_id').disabled) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}
		if (base_form.findField('LpuSectionProfile_id').disabled) {
			params.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
		}
		if (base_form.findField('LpuUnitType_id').disabled) {
			params.LpuUnitType_id = base_form.findField('LpuUnitType_id').getValue();
		}
		if (base_form.findField('MesAgeGroup_id').disabled) {
			params.MesAgeGroup_id = base_form.findField('MesAgeGroup_id').getValue();
		}
		if (base_form.findField('Sex_id').disabled) {
			params.Sex_id = base_form.findField('Sex_id').getValue();
		}
		if (base_form.findField('VizitClass_id').disabled) {
			params.VizitClass_id = base_form.findField('VizitClass_id').getValue();
		}
		if (base_form.findField('UslugaComplexTariff_begDate').disabled) {
			params.UslugaComplexTariff_begDate = Ext.util.Format.date(base_form.findField('UslugaComplexTariff_begDate').getValue(), 'd.m.Y');
		}
		
		if (base_form.findField('Lpu_id').disabled) {
			params.Lpu_id = base_form.findField('Lpu_id').getValue();
		}
		if (base_form.findField('LpuLevel_id').disabled) {
			params.LpuLevel_id = base_form.findField('LpuLevel_id').getValue();
		}
		if (base_form.findField('LpuBuilding_id').disabled) {
			params.LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
		}
		if (base_form.findField('LpuUnit_id').disabled) {
			params.LpuUnit_id = base_form.findField('LpuUnit_id').getValue();
		}
		if (base_form.findField('LpuSection_id').disabled) {
			params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}
		if (base_form.findField('MedService_id').disabled) {
			params.LpuSection_id = base_form.findField('MedService_id').getValue();
		}
		if (base_form.findField('UslugaComplexTariffType_id').disabled) {
			params.UslugaComplexTariffType_id = base_form.findField('UslugaComplexTariffType_id').getValue();
		}
		if (base_form.findField('UslugaComplexTariff_Tariff').disabled) {
			params.UslugaComplexTariff_Tariff = base_form.findField('UslugaComplexTariff_Tariff').getValue();
		}
		if (base_form.findField('UslugaComplexTariff_UED').disabled) {
			params.UslugaComplexTariff_UED = base_form.findField('UslugaComplexTariff_UED').getValue();
		}
		if (base_form.findField('UslugaComplexTariff_UEM').disabled) {
			params.UslugaComplexTariff_UEM = base_form.findField('UslugaComplexTariff_UEM').getValue();
		}
		
		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();

				this.callback(data);
				if (!options.notHide) {
					this.hide();
				}
			break;

			case 'remote':
				base_form.submit({
					params: params,
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								if (action.result.Error_Code == '11') {
									sw.swMsg.show(
									{
										buttons: Ext.Msg.YESNO,
										scope : this,
										fn: function(buttonId) 
										{
											if ( buttonId == 'yes' )
											{
												options.ignoreEndDate = true;
												wnd.doSave(options);
											}
										},
										icon: Ext.Msg.QUESTION,
										msg: lang['suschestvuyut_uslugi_s_datoy_okazaniya_posle_datyi_zakryitiya_tarifa_prodoljit_sohranenie'],
										title: lang['vopros']
									});
								} else {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.UslugaComplexTariff_id > 0 ) {
							// base_form.findField('UslugaComplexTariff_id').setValue(action.result.UslugaComplexTariff_id);
							data.uslugaComplexTariffData.UslugaComplexTariff_id = action.result.UslugaComplexTariff_id;
							this.callback(data);
							if (!options.notHide) {
								this.hide();
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	draggable: true,
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'UslugaComplexTariffEditWindow',
	refreshLpuStores: function() {
		var base_form = this.FormPanel.getForm();
		
		base_form.findField('LpuBuilding_id').getStore().removeAll();
		base_form.findField('LpuUnit_id').getStore().removeAll();
		base_form.findField('LpuSection_id').getStore().removeAll();
		base_form.findField('MedService_id').getStore().removeAll();

		var Lpu_id = base_form.findField('Lpu_id').getValue();

		//#179261 всегда грузим сторы с mode: 'combo'
		if(getRegionNick() === 'vologda') {
			base_form.findField('LpuBuilding_id').getStore().load({params: {'Lpu_id': Lpu_id, mode: 'combo'}});
			base_form.findField('LpuUnit_id').getStore().load({params: {'Lpu_id': Lpu_id, mode: 'combo'}});
			base_form.findField('LpuSection_id').getStore().load({params: {'Lpu_id': Lpu_id, mode: 'combo'}});
			base_form.findField('MedService_id').getStore().load({params: {'Lpu_id': Lpu_id, mode: 'combo'}});

			base_form.findField('MedService_id').setValue(base_form.findField('MedService_id').getValue());
		}else {
			if (Lpu_id == getGlobalOptions().lpu_id) {
				swLpuBuildingGlobalStore.clearFilter();
				swLpuUnitGlobalStore.clearFilter();
				swLpuSectionGlobalStore.clearFilter();
				swMedServiceGlobalStore.clearFilter();
				base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
				base_form.findField('LpuUnit_id').getStore().loadData(getStoreRecords(swLpuUnitGlobalStore));
				base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
				base_form.findField('MedService_id').getStore().loadData(getStoreRecords(swMedServiceGlobalStore));

				base_form.findField('MedService_id').setValue(base_form.findField('MedService_id').getValue());
			} else {
				base_form.findField('LpuBuilding_id').getStore().load({params: {'Lpu_id': Lpu_id, mode: 'combo'}});
				base_form.findField('LpuUnit_id').getStore().load({params: {'Lpu_id': Lpu_id, mode: 'combo'}});
				base_form.findField('LpuSection_id').getStore().load({params: {'Lpu_id': Lpu_id, mode: 'combo'}});
				base_form.findField('MedService_id').getStore().load({params: {'Lpu_id': Lpu_id, mode: 'combo'}});
			}
		}
	},
	clearLpuCombos: function() {
		var base_form = this.FormPanel.getForm();
		base_form.findField('LpuBuilding_id').clearValue();
		base_form.findField('LpuUnit_id').clearValue();
		base_form.findField('LpuSection_id').clearValue();
		base_form.findField('MedService_id').clearValue();
	},
	uslugaComplexTariffTypeFilter: function() {
		var base_form = this.FormPanel.getForm();

        if (this.UslugaCategory_SysNick == 'llo') {
            base_form.findField('UslugaComplexTariffType_id').getStore().filterBy(function(record) {
                if (record.get('UslugaComplexTariffType_id').inlist([1,2,3])) {
                    return false;
                }
                return true;
            });
            base_form.findField('UslugaComplexTariffType_id').setValue(4);
            base_form.findField('UslugaComplexTariffType_id').disable();
        }
        else {
			if ( this.action != 'view' && Ext.isEmpty(base_form.findField('EvnUsluga_setDate').getValue()) ) {
				base_form.findField('UslugaComplexTariffType_id').enable();
			}
            base_form.findField('UslugaComplexTariffType_id').getStore().clearFilter();
            base_form.findField('UslugaComplexTariffType_id').lastQuery = '';
            if (base_form.findField('UslugaComplexTariffType_id').getValue() == 3) {
                base_form.findField('UslugaComplexTariffType_id').clearValue();
            }
            if (!isSuperAdmin()) {
                base_form.findField('UslugaComplexTariffType_id').setValue(2);
            }
            base_form.findField('UslugaComplexTariffType_id').getStore().filterBy(function(record) {
                if (record.get('UslugaComplexTariffType_id').inlist([3,4])) {
                    return false;
                }
                return true;
            });

            if (base_form.findField('PayType_id').getValue() == 1) {
                if (base_form.findField('UslugaComplexTariffType_id').getValue() == 2) {
                    base_form.findField('UslugaComplexTariffType_id').clearValue();
                }
                if (!isSuperAdmin()) {
                    base_form.findField('UslugaComplexTariffType_id').setValue(1);
                }
                base_form.findField('UslugaComplexTariffType_id').getStore().filterBy(function(record) {
                    if (record.get('UslugaComplexTariffType_id').inlist([2,3,4])) {
                        return false;
                    }
                    return true;
                });
            }
        }
	},
	initComponent: function() {
		var wnd = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'UslugaComplexTariffEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'UslugaComplex_id' },
				{ name: 'UslugaComplexTariff_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'LpuUnit_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedService_id' },
				{ name: 'UslugaComplexTariff_Tariff' },
				{ name: 'UslugaComplexTariff_UED' },
				{ name: 'UslugaComplexTariff_UEM' },
				{ name: 'PayType_id' },
				{ name: 'LpuLevel_id' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'LpuUnitType_id' },
				{ name: 'MesAgeGroup_id' },
				{ name: 'Sex_id' },
				{ name: 'VizitClass_id' },
				{ name: 'UslugaComplexTariff_Name' },
				{ name: 'UslugaComplexTariff_Code' },
				{ name: 'UslugaComplexTariffType_id' },
				{ name: 'UslugaComplexTariff_begDate' },
				{ name: 'UslugaComplexTariff_endDate' },
				{ name: 'EvnUsluga_setDate' }
			]),
			url: '/?c=UslugaComplex&m=saveUslugaComplexTariff',
			items: [{
				name: 'UslugaComplexTariff_id',
				value: 0,
				xtype: 'hidden'
			}, {
				border: false,
				hidden: true,
				layout: 'form',
				items: [{
					disabled: true,
					name: 'EvnUsluga_setDate',
					xtype: 'swdatefield'
				}]
			}, {
				fieldLabel: lang['usluga'],
				hiddenName: 'UslugaComplex_id',
				allowBlank: false,
				disabled: true,
				listWidth: 600,
				tabIndex: TABINDEX_UCTEW + 0,
				width: 450,
				xtype: 'swuslugacomplexallcombo'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				xtype: 'numberfield',
				name: 'UslugaComplexTariff_Code',
				maxValue: 2147483647,
				minValue: 0,
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				tabIndex: TABINDEX_UCTEW + 0,
				fieldLabel: lang['kod']
			}, {
				xtype: 'textfield',
				name: 'UslugaComplexTariff_Name',
				tabIndex: TABINDEX_UCTEW + 1,
				width: 450,
				fieldLabel: lang['naimenovanie']
			}, {
				fieldLabel: lang['vid_oplatyi'],
				allowBlank: false,
				hiddenName: 'PayType_id',
				xtype: 'swpaytypecombo',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						wnd.uslugaComplexTariffTypeFilter();
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_UCTEW + 2,
				width: 450
			}, {
				hiddenName: 'UslugaComplexTariffType_id',
                comboSubject: 'UslugaComplexTariffType',
                fieldLabel: lang['tip_tarifa'],
				allowBlank: false,
				onLoadStore: function() {
					wnd.uslugaComplexTariffTypeFilter();
				},
				width: 450,
				tabIndex: TABINDEX_UCTEW + 3,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['uroven_mo'],
				comboSubject: 'LpuLevel',
				xtype: 'swcommonsprcombo',
				width: 450,
				tabIndex: TABINDEX_UCTEW + 4,
				hiddenName: 'LpuLevel_id'
			}, {
				fieldLabel: lang['mo'],
				hiddenName: 'Lpu_id',
				id: 'UCTEW_Lpu_id',
				tabIndex: TABINDEX_UCTEW + 5,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						this.clearLpuCombos();
						this.refreshLpuStores();
					}.createDelegate(this)
				},
				width: 300,
				listWidth: 400,
				xtype: 'swlpucombo'
			}, {
				hiddenName: 'LpuBuilding_id',
				fieldLabel: lang['podrazdelenie'],
				id: 'UCTEW_LpuBuildingCombo',
				lastQuery: '',
				linkedElements: [
					'UCTEW_LpuUnitCombo',
					'UCTEW_LpuSectionCombo',
					'UCTEW_MedServiceCombo'
				],
				listWidth: 700,
				tabIndex: TABINDEX_UCTEW + 6,
				width: 450,
				xtype: 'swlpubuildingglobalcombo'
			}, {
				hiddenName: 'LpuUnit_id',
				id: 'UCTEW_LpuUnitCombo',
				linkedElements: [
					'UCTEW_LpuSectionCombo',
					'UCTEW_MedServiceCombo'
				],
				listWidth: 600,
				parentElementId: 'UCTEW_LpuBuildingCombo',
				tabIndex: TABINDEX_UCTEW + 7,
				width: 450,
				xtype: 'swlpuunitglobalcombo'
			}, {
				hiddenName: 'LpuSection_id',
				id: 'UCTEW_LpuSectionCombo',
				linkedElements: [
					'UCTEW_MedServiceCombo'
				],
				lastQuery: '',
				parentElementId: 'UCTEW_LpuUnitCombo',
				listWidth: 700,
				tabIndex: TABINDEX_UCTEW + 8,
				width: 450,
				xtype: 'swlpusectionglobalcombo'
			}, {
				hiddenName: 'MedService_id',
				id: 'UCTEW_MedServiceCombo',
				lastQueury: '',
				tabIndex: TABINDEX_UCTEW + 8,
				width: 450,
				listeners: {
					'select': function(combo, record, index) {
						if (!Ext.isEmpty(record.get('MedService_id'))) {
							if (!Ext.isEmpty(record.get('LpuSection_id'))) {
								this.findById('UCTEW_LpuSectionCombo').setValue(record.get('LpuSection_id'));
							} else if (!Ext.isEmpty(record.get('LpuUnit_id'))) {
								this.findById('UCTEW_LpuUnitCombo').setValue(record.get('LpuUnit_id'));
							} else if (!Ext.isEmpty(record.get('LpuBuilding_id'))) {
								this.findById('UCTEW_LpuBuildingCombo').setValue(record.get('LpuBuilding_id'));
							}
						}
					}.createDelegate(this)
				},
				xtype: 'swmedserviceglobalcombo'
			},
			{
				width: 450,
				fieldLabel: lang['profil'],
				tabIndex: TABINDEX_UCTEW + 9,
				xtype: 'swlpusectionprofilecombo',
				id: 'uctewLpuSectionProfile_id',
				hiddenName: 'LpuSectionProfile_id'
			},
			{
				width: 450,
				fieldLabel : lang['vid_med_pomoschi'],
				tabIndex: TABINDEX_UCTEW + 10,
				xtype: 'swlpuunittypecombo',
				hiddenName: 'LpuUnitType_id'
			},
			{
				width: 450,	
				fieldLabel : lang['vozrastnaya_gruppa'],
				tabIndex: TABINDEX_UCTEW + 11,
				xtype: 'swmesagegroupcombo',
				hiddenName: 'MesAgeGroup_id'
			},
			{
				comboSubject: 'Sex',
				fieldLabel: lang['pol_patsienta'],
				tabIndex: TABINDEX_UCTEW + 12,
				hiddenName: 'Sex_id',
				width: 130,
				xtype: 'swcommonsprcombo'
			},
			{
				comboSubject: 'VizitClass',
				fieldLabel: lang['vid_posescheniya'],
				tabIndex: TABINDEX_UCTEW + 12,
				hiddenName: 'VizitClass_id',
				width: 130,
				xtype: 'swcommonsprcombo'
			},
			{
				xtype: 'numberfield',
				name: 'UslugaComplexTariff_Tariff',
				maxValue: 999999,
				minValue: 0,
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				tabIndex: TABINDEX_UCTEW + 13,
				fieldLabel: lang['tarif']
			},
			{
				xtype: 'numberfield',
				name: 'UslugaComplexTariff_UED',
				tabIndex: TABINDEX_UCTEW + 14,
				maxValue: 999999,
				minValue: 0,
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				fieldLabel: lang['uet_vracha']
			},
			{
				xtype: 'numberfield',
				name: 'UslugaComplexTariff_UEM',
				tabIndex: TABINDEX_UCTEW + 15,
				maxValue: 999999,
				minValue: 0,
				autoCreate: {tag: "input", size:14, autocomplete: "off"},
				fieldLabel: lang['uet_sr_medpersonala']
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_nachala'],
				format: 'd.m.Y',
				allowBlank: false,
				tabIndex: TABINDEX_UCTEW + 16,
				name: 'UslugaComplexTariff_begDate',
				id: 'uctewUslugaComplexTariff_begDate',
				listeners: {
					'change': function (combo, newValue, oldValue) {
						var form = this.FormPanel.getForm();
						form.findField('UslugaComplexTariff_endDate').fireEvent('change', form.findField('UslugaComplexTariff_endDate'), form.findField('UslugaComplexTariff_endDate').getValue());

						if ( typeof newValue == 'object' ) {
							form.findField('UslugaComplexTariff_endDate').setMinValue(Ext.util.Format.date(newValue, 'd.m.Y'));
						}
						else {
							form.findField('UslugaComplexTariff_endDate').setMinValue(undefined);
						}
					}.createDelegate(this)
				},
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_okonchaniya'],
				format: 'd.m.Y',
				tabIndex: TABINDEX_UCTEW + 17,
				name: 'UslugaComplexTariff_endDate',
				id: 'uctewUslugaComplexTariff_endDate',
				listeners: {
					'change':function (field, newValue, oldValue) {
						var form = this.FormPanel.getForm();

						if ( typeof newValue == 'object' ) {
							form.findField('UslugaComplexTariff_begDate').setMaxValue(Ext.util.Format.date(newValue, 'd.m.Y'));
						}
						else {
							form.findField('UslugaComplexTariff_begDate').setMaxValue(undefined);
						}

						var index,
							LpuSectionProfile_id = form.findField('LpuSectionProfile_id').getValue(),
							begDate = form.findField('UslugaComplexTariff_begDate').getValue();

						// Фильтруем список профилей отделений
						form.findField('LpuSectionProfile_id').clearValue();
						form.findField('LpuSectionProfile_id').getStore().clearFilter();
						form.findField('LpuSectionProfile_id').lastQuery = '';

						if ( !Ext.isEmpty(begDate) || !Ext.isEmpty(newValue) ) {
							form.findField('LpuSectionProfile_id').getStore().filterBy(function(rec) {
								if ( Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) && Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) ) {
									return true;
								}

								if ( !Ext.isEmpty(begDate) && Ext.isEmpty(newValue) ) {
									return (
										(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || typeof rec.get('LpuSectionProfile_begDT') != 'object' || rec.get('LpuSectionProfile_begDT') <= begDate)
										&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || typeof rec.get('LpuSectionProfile_endDT') != 'object' || rec.get('LpuSectionProfile_endDT') >= begDate)
									);
								}
								else if ( Ext.isEmpty(begDate) && !Ext.isEmpty(newValue) ) {
									return (
										(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || typeof rec.get('LpuSectionProfile_begDT') != 'object' || rec.get('LpuSectionProfile_begDT') <= newValue)
										&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || typeof rec.get('LpuSectionProfile_endDT') != 'object' || rec.get('LpuSectionProfile_endDT') >= newValue)
									);
								}
								else {
									return (
										(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || typeof rec.get('LpuSectionProfile_begDT') != 'object' || (rec.get('LpuSectionProfile_begDT') <= newValue && rec.get('LpuSectionProfile_begDT') <= begDate))
										&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || typeof rec.get('LpuSectionProfile_endDT') != 'object' || (rec.get('LpuSectionProfile_endDT') >= newValue && rec.get('LpuSectionProfile_endDT') >= begDate))
									);
								}
							});
						}

						index = form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
							return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
						});

						if ( index >= 0 ) {
							form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
							form.findField('LpuSectionProfile_id').fireEvent('select', form.findField('LpuSectionProfile_id'), form.findField('LpuSectionProfile_id').getStore().getAt(index));
						}
					}.createDelegate(this)
				},
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('UslugaComplexTariff_endDate').disabled ) {
						base_form.findField('UslugaComplexTariff_endDate').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_UCTEW + 20,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.doSave({
						notHide: true
					});
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('UslugaComplexTariff_endDate').disabled ) {
						base_form.findField('UslugaComplexTariff_endDate').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_UCTEW + 21,
				text: lang['sohranit_i_prodoljit']
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_UCTEW + 22),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();
					if ( !base_form.findField('UslugaComplexTariff_Code').disabled ) {
						base_form.findField('UslugaComplexTariff_Code').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_UCTEW + 23,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swUslugaComplexTariffEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('uctewLpuSectionProfile_id').setBaseFilter(function(rec) {
			var
				begDate = this.findById('uctewUslugaComplexTariff_begDate').getValue(),
				endDate = this.findById('uctewUslugaComplexTariff_endDate').getValue();

			if ( !Ext.isEmpty(begDate) || !Ext.isEmpty(endDate) ) {
				if ( Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) && Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) ) {
					return true;
				}

				if ( !Ext.isEmpty(begDate) && Ext.isEmpty(endDate) ) {
					return (
						(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= begDate)
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= begDate)
					);
				}
				else if ( Ext.isEmpty(begDate) && !Ext.isEmpty(endDate) ) {
					return (
						(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= endDate)
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= endDate)
					);
				}
				else {
					return (
						(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || (rec.get('LpuSectionProfile_begDT') <= endDate && rec.get('LpuSectionProfile_begDT') <= begDate))
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || (rec.get('LpuSectionProfile_endDT') >= endDate && rec.get('LpuSectionProfile_endDT') >= begDate))
					);
				}
			}

			return true;
		}.createDelegate(this));
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaComplexTariffEditWindow');

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
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swUslugaComplexTariffEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.mode = '';
		this.formMode = 'local';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.uslugaData = new Object();
		this.uslugaTariffData = [];
        this.UslugaCategory_SysNick = '';

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

        if ( arguments[0].UslugaCategory_SysNick ) {
            this.UslugaCategory_SysNick = arguments[0].UslugaCategory_SysNick;
        }

//		this.uslugaComplexTariffTypeFilter();
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].mode ) {
			this.mode = arguments[0].mode;
		}
		
		if ( typeof arguments[0].uslugaData == 'object' ) {
			this.uslugaData = arguments[0].uslugaData;
		}
		
		var uslugaLpu_id = getGlobalOptions().lpu_id;
		
		if ( arguments[0].Lpu_id && !Ext.isEmpty(arguments[0].Lpu_id) ) {
			uslugaLpu_id = arguments[0].Lpu_id;
		}
		
		if ( arguments[0].uslugaTariffData ) {
			this.uslugaTariffData = arguments[0].uslugaTariffData;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		var LpuBuilding_id = arguments[0].LpuBuilding_id || null;
		var LpuUnit_id = arguments[0].LpuUnit_id || null;
		var LpuSection_id = arguments[0].LpuSection_id || null;
		var MedService_id = arguments[0].MedService_id || null;

		base_form.findField('UslugaComplexTariff_begDate').setMaxValue(undefined);
		base_form.findField('UslugaComplexTariff_begDate').setMinValue(undefined);
		base_form.findField('UslugaComplexTariff_endDate').setMaxValue(undefined);
		base_form.findField('UslugaComplexTariff_endDate').setMinValue(undefined);

		var uslugaCombo = base_form.findField('UslugaComplex_id');
		uslugaCombo.getStore().removeAll();

		if (this.mode == 'TariffVolumes' && this.action == 'add') {
			uslugaCombo.enable();
		} else {
			uslugaCombo.disable();
		}

		if ( this.formMode == 'remote' ) {
			if ( !Ext.isEmpty(uslugaCombo.getValue()) ) {
				uslugaCombo.getStore().load({
					params: {
						UslugaComplex_id: uslugaCombo.getValue()
					},
					callback: function() {
						var record = uslugaCombo.getStore().getAt(0);
						if (record) {
							uslugaCombo.setRawValue(record.get('UslugaComplex_Code') + '. '+ record.get('UslugaComplex_Name'));
	
							base_form.findField('UslugaComplexTariff_begDate').setMinValue();

							if ( !Ext.isEmpty(record.get('UslugaComplex_begDT')) ) {
								if ( typeof record.get('UslugaComplex_begDT') == 'object' ) {
									base_form.findField('UslugaComplexTariff_begDate').setMinValue(Ext.util.Format.date(record.get('UslugaComplex_begDT'), 'd.m.Y'));
									base_form.findField('UslugaComplexTariff_endDate').setMinValue(Ext.util.Format.date(record.get('UslugaComplex_begDT'), 'd.m.Y'));
								}
								else {
									base_form.findField('UslugaComplexTariff_begDate').setMinValue(record.get('UslugaComplex_begDT'));
									base_form.findField('UslugaComplexTariff_endDate').setMinValue(record.get('UslugaComplex_begDT'));
								}
							}

							if ( !Ext.isEmpty(record.get('UslugaComplex_endDT')) ) {
								if ( typeof record.get('UslugaComplex_endDT') == 'object' ) {
									base_form.findField('UslugaComplexTariff_begDate').setMaxValue(Ext.util.Format.date(record.get('UslugaComplex_endDT'), 'd.m.Y'));
									base_form.findField('UslugaComplexTariff_endDate').setMaxValue(Ext.util.Format.date(record.get('UslugaComplex_endDT'), 'd.m.Y'));
								}
								else {
									base_form.findField('UslugaComplexTariff_begDate').setMaxValue(record.get('UslugaComplex_endDT'));
									base_form.findField('UslugaComplexTariff_endDate').setMaxValue(record.get('UslugaComplex_endDT'));
								}
							}
						}
					}
				});
			}
		}
		else {
			if ( !Ext.isEmpty(this.uslugaData.UslugaComplex_Name ) ) {
				uslugaCombo.getStore().loadData([{
					 UslugaComplex_id: this.uslugaData.UslugaComplex_id
					,UslugaComplex_Code: this.uslugaData.UslugaComplex_Code
					,UslugaComplex_Name: this.uslugaData.UslugaComplex_Name
				}], true);

				uslugaCombo.setValue(this.uslugaData.UslugaComplex_id);
			}

			if ( !Ext.isEmpty(this.uslugaData.UslugaComplex_begDate) ) {
				if ( typeof this.uslugaData.UslugaComplex_begDate == 'object' ) {
					base_form.findField('UslugaComplexTariff_begDate').setMinValue(Ext.util.Format.date(this.uslugaData.UslugaComplex_begDate, 'd.m.Y'));
					base_form.findField('UslugaComplexTariff_endDate').setMinValue(Ext.util.Format.date(this.uslugaData.UslugaComplex_begDate, 'd.m.Y'));
				}
				else {
					base_form.findField('UslugaComplexTariff_begDate').setMinValue(this.uslugaData.UslugaComplex_begDate);
					base_form.findField('UslugaComplexTariff_endDate').setMinValue(this.uslugaData.UslugaComplex_begDate);
				}
			}

			if ( !Ext.isEmpty(this.uslugaData.UslugaComplex_endDate) ) {
				if ( typeof this.uslugaData.UslugaComplex_endDate == 'object' ) {
					base_form.findField('UslugaComplexTariff_begDate').setMaxValue(Ext.util.Format.date(this.uslugaData.UslugaComplex_endDate, 'd.m.Y'));
					base_form.findField('UslugaComplexTariff_endDate').setMaxValue(Ext.util.Format.date(this.uslugaData.UslugaComplex_endDate, 'd.m.Y'));
				}
				else {
					base_form.findField('UslugaComplexTariff_begDate').setMaxValue(this.uslugaData.UslugaComplex_endDate);
					base_form.findField('UslugaComplexTariff_endDate').setMaxValue(this.uslugaData.UslugaComplex_endDate);
				}
			}
		}

		this.getLoadMask().show();
		
		switch ( this.action ) {
			case 'add':
				this.buttons[1].show();
				base_form.findField('Lpu_id').setValue(uslugaLpu_id);
				base_form.findField('LpuBuilding_id').setValue(LpuBuilding_id);
				base_form.findField('LpuUnit_id').setValue(LpuUnit_id);
				base_form.findField('LpuSection_id').setValue(LpuSection_id);
				base_form.findField('MedService_id').setValue(MedService_id);
				this.setTitle(WND_USLUGA_TARIFF_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				
				this.buttons[1].hide();
				
				if ( this.action == 'edit' && (base_form.findField('Lpu_id').getValue() == uslugaLpu_id || isSuperAdmin())) {
					this.setTitle(WND_USLUGA_TARIFF_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_USLUGA_TARIFF_VIEW);
					this.enableEdit(false);
				}
				
				// Загружаем значение EvnUsluga_setDate
				var UslugaComplexTariff_id = base_form.findField('UslugaComplexTariff_id').getValue();
				if (UslugaComplexTariff_id > 0) {
					Ext.Ajax.request({
						url: '/?c=UslugaComplex&m=getUslugaComplexTariffMaxDate',
						params: {
							UslugaComplexTariff_id: UslugaComplexTariff_id
						},
						success: function(response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj[0] && response_obj[0].EvnUsluga_setDate) {
								base_form.findField('EvnUsluga_setDate').setValue(response_obj[0].EvnUsluga_setDate);
							}
							if (!Ext.isEmpty(base_form.findField('EvnUsluga_setDate').getValue())) {
								this.enableEdit(false);
								base_form.findField('UslugaComplexTariff_endDate').enable();
								// дата закрытия тарифа должна быть не раньше последнего использования в услуге
								base_form.findField('UslugaComplexTariff_endDate').setMinValue(base_form.findField('EvnUsluga_setDate').getValue().format('d.m.Y'));
								this.buttons[0].show();
							}
							this.getLoadMask().hide();
						}.createDelegate(this),
						failure: function() {
							// 
							this.getLoadMask().hide();
						}
					});
				}else{
					this.getLoadMask().hide();
				}
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if (this.action == 'add') {
			this.uslugaComplexTariffTypeFilter();
		}
		
		if (!isSuperAdmin()) {
			base_form.findField('Lpu_id').disable();
			if (this.action == 'add') {
				base_form.findField('UslugaComplexTariffType_id').setValue(2);
			}
			base_form.findField('UslugaComplexTariffType_id').disable();
			
			var index = base_form.findField('LpuLevel_id').getStore().findBy(function(r) {
				if ( r.get('LpuLevel_Code') == base_form.findField('Lpu_id').getFieldValue('LpuLevel_Code') ) {
					return true;
				}
				else {
					return false;
				}
			});
			
			var record = base_form.findField('LpuLevel_id').getStore().getAt(index);
			
			if (record) {
				base_form.findField('LpuLevel_id').setValue(record.get('LpuLevel_id'));
			}
			
			base_form.findField('LpuLevel_id').disable();
			
			// дизаблим тариф/ует если заполнены
			if ( !Ext.isEmpty(base_form.findField('UslugaComplexTariff_Tariff').getValue()) ) {
				base_form.findField('UslugaComplexTariff_Tariff').disable();
			}
			if ( !Ext.isEmpty(base_form.findField('UslugaComplexTariff_UED').getValue()) ) {
				base_form.findField('UslugaComplexTariff_UED').disable();
			}
			if ( !Ext.isEmpty(base_form.findField('UslugaComplexTariff_UEM').getValue()) ) {
				base_form.findField('UslugaComplexTariff_UEM').disable();
			}
		}
		
		if (!isSuperAdmin() && arguments[0].LpuBuilding_id) {
			base_form.findField('LpuBuilding_id').disable();
		}
		
		if (!isSuperAdmin() && arguments[0].LpuUnit_id) {
			base_form.findField('LpuUnit_id').disable();
		}

		if (!isSuperAdmin() && arguments[0].LpuSection_id) {
			base_form.findField('LpuSection_id').disable();
		}

		if (!isSuperAdmin() && arguments[0].MedService_id) {
			base_form.findField('MedService_id').disable();
		}
		
		this.refreshLpuStores();
		
		if ( !base_form.findField('UslugaComplexTariff_Code').disabled ) {
			base_form.findField('UslugaComplexTariff_Code').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 800
});