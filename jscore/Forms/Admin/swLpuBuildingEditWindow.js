/**
* swLpuBuildingEditWindow - окно редактирования/добавления зданий МО.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      05.10.2011
*/

sw.Promed.swLpuBuildingEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoScroll: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	layout: 'form',
	id: 'LpuBuildingEditWindow',
	listeners:
	{
		hide: function() {
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	maximized: true,
	doSave: function()
	{
		var form = this.findById('LpuBuildingEditForm'),
		    base_form = form.getForm(),
            curYear = new Date();

		if(getRegionNick() != 'kz'){
			var controlLLBPass = this.controlOfTheFieldLpuBuildingPass();
			if( controlLLBPass ){
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: controlLLBPass,
					title: 'Ошибка в поле «Наименование»'
				});
				return false;
			}
		}
		
		if ( !base_form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var YearBuilt = form.getForm().findField('LpuBuildingPass_YearBuilt').getValue(),
			YearProjDoc = form.getForm().findField('LpuBuildingPass_YearProjDoc').getValue(),
			YearRepair = form.getForm().findField('LpuBuildingPass_YearRepair').getValue(),
			TotalArea = form.getForm().findField('LpuBuildingPass_TotalArea').getValue(),
			EffBuildVol = form.getForm().findField('LpuBuildingPass_EffBuildVol').getValue(),
			WorkArea = form.getForm().findField('LpuBuildingPass_WorkArea').getValue(),
			focusField = '',
			msg = '';


		switch (true){
			case (!Ext.isEmpty(YearBuilt) && YearBuilt.getFullYear() > curYear.getFullYear()):
				msg = lang['god_postroyki_ne_mojet_byit_bolshe_tekuschego_goda'];
				focusField = 'LpuBuildingPass_YearBuilt';
				break;
			case (!Ext.isEmpty(YearProjDoc) && YearProjDoc.getFullYear() > curYear.getFullYear()):
				msg = lang['god_razrabotki_ne_mojet_byit_bolshe_tekuschego_goda'];
				focusField = 'LpuBuildingPass_YearProjDoc';
				break;
			case (!Ext.isEmpty(YearRepair) && YearRepair.getFullYear() > curYear.getFullYear()):
				msg = lang['god_posledney_rekonstruktsii_ne_mojet_byit_bolshe_tekuschego_goda'];
				focusField = 'LpuBuildingPass_YearRepair';
				break;
			case (!Ext.isEmpty(EffBuildVol) && !Ext.isEmpty(TotalArea) && EffBuildVol > TotalArea):
				msg = lang['obschaya_ploschad_zdaniya_ne_mojet_byit_menshe_chem_poleznaya'];
				focusField = 'LpuBuildingPass_TotalArea';
				break;
			case (!Ext.isEmpty(WorkArea) && !Ext.isEmpty(TotalArea) && WorkArea > TotalArea):
				msg = lang['obschaya_ploschad_zdaniya_ne_mojet_byit_menshe_chem_rabochaya'];
				focusField = 'LpuBuildingPass_TotalArea';
				break;
		}

		var CoordLat = form.getForm().findField('LpuBuildingPass_CoordLat').getValue();
		var CoordLong = form.getForm().findField('LpuBuildingPass_CoordLong').getValue();
		var myReLat = /-?\d{1,2}(\.\d{0,})?/;
		var myReLong = /-?\d{1,3}(\.\d{0,})?/;
		if(!String(CoordLat).match(myReLat)){
			msg = 'некорректное значение поля Широта';
			focusField = 'LpuBuildingPass_CoordLat';
		}
		if(!String(CoordLong).match(myReLong)){
			msg = 'некорректное значение поля Долгота';
			focusField = 'LpuBuildingPass_CoordLong';
		}

		if (!Ext.isEmpty(msg)){
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						base_form.findField(focusField).focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: msg,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}

		this.submit();
		return true;
	},
	submit: function()
	{
		var form = this.LpuBuildingEditForm,
            base_form = form.getForm(),
		    current_window = this,
            MOSectionsData = '',
		    loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});

        // Собираем данные из грида связей с отделениями
        var MOSectionsGrid = this.findById('LBEW_MOSectionsGrid').getGrid();

        if ( MOSectionsGrid.getStore().getCount() > 0  ) {
                MOSectionsData = getStoreRecords(MOSectionsGrid.getStore(), {
                exceptionFields: [
                    'MOSectionBase_id',
                    'LpuBuilding_Name',
                    'LpuUnit_Name',
                    'LpuSection_Name'
                ]
            });

            MOSectionsData = Ext.util.JSON.encode(MOSectionsData);
        }

		loadMask.show();

		base_form.submit({
			failure: function(result_form, action){
				loadMask.hide();
				if (action.result){
					if (action.result.Error_Code){
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			params:{
				action: current_window.action,
                MOSectionsData: MOSectionsData
			},
			success: function(result_form, action){
				loadMask.hide();
				if (action.result)
				{
					if (action.result.LpuBuildingPass_id) {
						current_window.hide();
						Ext.getCmp('LpuPassportEditWindow').findById('LPEW_LpuBuilding').loadData();

						var
							LPBcombo = Ext.getCmp('LPEW_LpuBuildingPass_mid'),
							LpuBuildingPass_mid = LPBcombo.getValue();

						LPBcombo.getStore().load({
							callback: function() {
								if ( !Ext.isEmpty(LpuBuildingPass_mid) ) {
									LPBcombo.setValue(LpuBuildingPass_mid);
								}
								else {
									LPBcombo.clearValue();
								}
							},
							params: {
								Lpu_id: current_window.Lpu_id
							}
						});
					} else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function()
							{
								form.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	formatNumber: function(v) {
		return v;
		//return (!Ext.isEmpty(v)) ? Number(v.slice(0,-2)) : null;
	},
    deleteMOSection: function() {
        var grid = this.findById('LBEW_MOSectionsGrid').getGrid(),
            record = grid.getSelectionModel().getSelected();

        if ( this.action == 'view' || !record) {
            return false;
        }

        grid.getStore().remove(record);
        grid.getSelectionModel().selectLastRow();
		this.calcWorkAreaWard();
    },
    openMOSectionsEditWindow: function(action) {
        if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
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

        if ( getWnd('swMOSectionsEditWindow').isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_dobavleniya_podrazdeleniya_uje_otkryito']);
            return false;
        }

        var deniedSectionsList = [],
            params = {},
            formParams = {},
			_this = this,
            grid = this.findById('LBEW_MOSectionsGrid').getGrid(),
            selectedRecord;
        
        params.LpuLicence_id = this.LpuLicence_id;

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('LpuSection_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

        if ( action == 'add' ) {

            params.onHide = function() {
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                }
            };

            grid.getStore().each(function(rec) {
                if ( rec.get('LpuSection_id') ) {
                    deniedSectionsList.push(rec.get('LpuSection_id'));
                }
            });

        } else {
            if ( !selectedRecord ) {
                return false;
            }

            grid.getStore().each(function(rec) {
                if (rec.get('LpuSection_id')) {
                    deniedSectionsList.push(rec.get('LpuSection_id'));
                }
            });

            formParams = selectedRecord.data;
            params.LpuSection_id = grid.getSelectionModel().getSelected().get('LpuSection_id');
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
            };
        }

        params.action = action;
        params.callback = function(data) {
            if ( Ext.isEmpty(data.result) || typeof data.result != 'object' ) {
                return false;
            }
            if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('LpuSection_id') ) {
                grid.getStore().removeAll();
            }

            if (params.action == 'edit' && selectedRecord.get('LpuSection_id') != data.result[0].LpuSection_id) {
                grid.getStore().remove(selectedRecord);
            }
			var i = 0;
            data.result.forEach(function(rec){
                rec.MOSectionBase_id = -swGenTempId(grid.getStore());
                grid.getStore().loadData([ rec ], true);
				i++;
            });

			_this.calcWorkAreaWard();
			var msg = '';
			switch (i)
			{
				case 0:
					msg = lang['v_vyibranom_podrazdelenii_net_svobodnyih_otdeleniy'];
				break;
				case 1:
					msg = lang['dobavleno_1_otdelenie'];
				break;
				case 2:
				case 3:
				case 4:
					msg = lang['dobavleno'] + i + lang['otdeleniya'];
				break;
				default:
					msg = lang['dobavleno'] + i + lang['otdeleniy'];
				break;
			}

			if (data.type != 'LpuSection') {
				showSysMsg(msg, lang['soobschenie']);
			} else if (data.type != 'LpuSection' && i == 0) {
				msg = lang['dannoe_otdelenie_prikrepleno_k_drugomu_zdaniyu'];
				showSysMsg(msg, lang['soobschenie']);
			}

        }.createDelegate(this);
        params.deniedSectionsList = deniedSectionsList;
        params.formMode = 'local';
        params.LpuBuildingPass_id = this.LpuBuildingPass_id;
        params.formParams = formParams;

        getWnd('swMOSectionsEditWindow').show(params);
    },
	controlOfTheFieldLpuBuildingPass: function(){
		var form = this.findById('LpuBuildingEditForm'),
			base_form = form.getForm();

		var LLBPass = base_form.findField('LBEW_LpuBuildingPass_Name').getValue(); // поле "Наименование"
		if(!LLBPass) return false;
		var rxArr = [
			{
				rx: /([^А-Яа-яёЁ\d\s-,№()"«»\.])|(^\([^)(]*\))|(^"[^"]*")|(^«[^«]*»)|(№.*№)/,
				error_msg: 'В наименовании допустимо использование только следующих знаков: буквы (кириллица), цифры, круглые парные скобки "(" и ")", дефис, пробел, запятая, парные кавычки типов " " и « » и один знак "№"».',
				res: true
			},
			{
				rx: /^[А-Яа-я0-9]*[\s-][А-Яа-я\s-,№("«\.]{2,}/,
				error_msg: 'Наименование может начинаться только на букву или цифру, за которой должны следовать либо пробел и слово, либо дефис и слово. Словом считается любая последовательность кириллических букв более двух знаков ',
				res: false
			},
			{
				rx: /(--)|(\s\s)/,
				error_msg: 'В наименовании не должно быть более одного пробела или дефиса подряд',
				res: true
			},
			{
				rx: /\s-/,
				error_msg: 'В наименовании не должны располагаться подряд пробел и дефис',
				res: true
			},
			{
				rx: /(№[^\s\d])|(№\s\D)/,
				error_msg: 'В наименовании после знака номера "№" допустимы либо цифра, либо один пробел и цифра',
				res: true
			},
			{
				rx: /\([\(-,:\.\s]/,
				error_msg: 'В наименовании, после открывающейся скобки "(", должны следовать цифра или слово. Не допускается использование после скобки "(" другой скобки, дефиса, запятой или пробела.',
				res: true
			},
			{
				rx: /[^\s]\(/,
				error_msg: 'В наименовании обязательно использование пробела перед открывающейся скобкой "(".',
				res: true
			},
			{
				rx: /\)[^\s]/,
				error_msg: 'В наименовании обязательно использование пробела после закрывающейся скобки ")", расположенной не в конце',
				res: true
			},
			{
				rx: /(\).)$/,
				error_msg: 'В конце наименования после закрывающейся скобки ")" недопустимы иные символы',
				res: true
			},
			{
				rx: /\s,/,
				error_msg: 'Перед запятой недопустим пробел',
				res: true
			},
			{
				rx: /,[^\s]/,
				error_msg: 'После запятой обязателен пробел',
				res: true
			},
			{
				rx: /(».)|(".)$/,
				error_msg: 'После закрывающейся кавычки в конце наименования недопустимы иные символы',
				res: true
			},
			{
				rx: /("[^А-Яа-я0-9].*")|(«[^А-Яа-я0-9].*»)/,
				error_msg: 'После открывающейся кавычки должны следовать цифра или слово и недопустимы: другая кавычка, дефис, запятая, скобка, пробел',
				res: true
			},
			{
				rx: /(".*["\s,\)\(-]")|(«.*[«\s,\)\(-]»)/,
				error_msg: 'Перед закрывающей кавычкой недопустимы кавычки, дефис, запятая, скобка, пробел',
				res: true
			},
			
		];
	
		for (i = 0; i < rxArr.length; i++) {
			var elem = rxArr[i];
			if( elem.rx.test(LLBPass) == elem.res){
				return elem.error_msg;
			}
		}

		function quotation(LLBPass){
			//парные скобки, кавычки
			var opening_parenthesis = LLBPass.match(/\(/g);
			var closing_parenthesis = LLBPass.match(/\)/g);
			var quotation_mark = LLBPass.match(/\"/g);
			var opening_quotation = LLBPass.match(/\«/g);
			var closing_quotation = LLBPass.match(/\»/g);
			if( quotation_mark && quotation_mark.length%2 ){
				//не четные
				return false;
			}else if(
				(opening_parenthesis && closing_parenthesis && opening_parenthesis.length!=closing_parenthesis.length)
				|| (opening_parenthesis && !closing_parenthesis)
				|| (!opening_parenthesis && closing_parenthesis)
			){
				return false;
			}else if(
				(opening_quotation && closing_quotation && opening_quotation.length!=closing_quotation.length)
				|| (opening_quotation && !closing_quotation)
				|| (!opening_quotation && closing_quotation)
			){
				return false;
			}else{
				return true;
			}
		}

		if( !quotation(LLBPass) ){
			return 'В наименовании допустимо использование только следующих знаков: буквы (кириллица), цифры, круглые парные скобки "(" и ")", дефис, пробел, запятая, парные кавычки типов " " и « » и один знак "№"».';
		}else{
			return false;
		}
    },
	calcWorkAreaWard: function() {
		var deniedSectionsList = [],
			_this = this;

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: 'Производится расчёт площади палат'});

		this.findById('LBEW_MOSectionsGrid').getGrid().getStore().each(function(rec) {
			if (rec.get('LpuSection_id')) {
				deniedSectionsList.push(rec.get('LpuSection_id'));
			}
		});
		if (!Ext.isEmpty(deniedSectionsList[0])){
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=LpuPassport&m=calcWorkAreaWard',
				params: {
					deniedSectionsList: deniedSectionsList.join()
				},
				callback: function(options, success, response) {
					loadMask.hide();
					if (success) {
						if ( response.responseText.length > 0 ) {
							var result = Ext.util.JSON.decode(response.responseText);
							log(result);
							if (!result[0].square) {
								sw.swMsg.alert('Ошибка', 'Ошибка при расчёте общей площади палат (В т.ч. палат, кв. м.)');
								return false;
							} else {
								_this.LpuBuildingEditForm.getForm().findField('LpuBuildingPass_WorkAreaWard').setValue(result[0].square);
							}
						}
					}
				}
			});
		}

	},
	show: function()
	{
		sw.Promed.swLpuBuildingEditWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		if (!arguments[0])
		{
			sw.swMsg.show(
			{
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

		var form = this.findById('LpuBuildingEditForm'),
            base_form = form.getForm();

		base_form.reset();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if (arguments[0].LpuBuildingPass_id)
			this.LpuBuildingPass_id = arguments[0].LpuBuildingPass_id;
		else
			this.LpuBuildingPass_id = null;

		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		else
			this.Lpu_id = getGlobalOptions().lpu_id;

		if (arguments[0].callback)
		{
			this.callback = arguments[0].callback;
		}
		if (arguments[0].owner)
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].action)
		{
			this.action = arguments[0].action;
		}
		else
		{
			if ( ( this.LpuBuildingPass_id ) && ( this.LpuBuildingPass_id > 0 ) )
				this.action = "edit";
			else
				this.action = "add";
		}

        current_window.findById('LBEW_Lpu_id').setValue(current_window.Lpu_id);

        this.LpuBuildingEditForm.getForm().findField('MOArea_id').getStore().load({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}, callback: function() {base_form.setValues(arguments[0])}});

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();


        this.findById('LBEW_MOSectionsGrid').setReadOnly(this.action == 'view');
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['zdaniya_mo_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
                this.findById('LBEW_MOSectionsGrid').removeAll();
				//base_form.clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['zdaniya_mo_redaktirovanie']);
        this.findById('LBEW_MOSectionsGrid').loadData({globalFilters:{LpuBuildingPass_id: this.LpuBuildingPass_id}, params:{LpuBuildingPass_id: this.LpuBuildingPass_id}});
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['zdaniya_mo_prosmotr']);
        this.findById('LBEW_MOSectionsGrid').loadData({globalFilters:{LpuBuildingPass_id: this.LpuBuildingPass_id}, params:{LpuBuildingPass_id: this.LpuBuildingPass_id}});
				this.enableEdit(false);
				break;
		}

		if (this.action != 'add')
		{
			base_form.load(
			{
				params:
				{
					LpuBuildingPass_id: current_window.LpuBuildingPass_id,
					Lpu_id: current_window.Lpu_id
				},
				failure: function(f, o, a)
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function()
						{
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function(result, request)
				{
					loadMask.hide();

                    base_form.findField('LpuBuildingPass_EffBuildVol').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_EffBuildVol').getValue()));
                    base_form.findField('LpuBuildingPass_TotalArea').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_TotalArea').getValue()));
                    base_form.findField('LpuBuildingPass_BuildVol').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_BuildVol').getValue()));
                    base_form.findField('LpuBuildingPass_FSDis').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_FSDis').getValue()));
                    base_form.findField('LpuBuildingPass_WorkAreaWard').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_WorkAreaWard').getValue()));
                    base_form.findField('LpuBuildingPass_OfficeArea').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_OfficeArea').getValue()));
                    base_form.findField('LpuBuildingPass_WorkAreaWardSect').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_WorkAreaWardSect').getValue()));
                    base_form.findField('LpuBuildingPass_WorkArea').setValue(current_window.formatNumber(base_form.findField('LpuBuildingPass_WorkArea').getValue()));

                    if (!Ext.isEmpty(base_form.findField('BuildingOverlapType_id').getValue())) {
						form.findById('LBEW_BuildingOverlapType_Name').setNameWithPath();
                    }

                    if (!Ext.isEmpty(base_form.findField('BuildingHoldConstrType_id').getValue())) {
						form.findById('LBEW_BuildingHoldConstrType_Name').setNameWithPath();
                    }

                    var MOArea_id = base_form.findField('MOArea_id').getValue();
                    base_form.findField('MOArea_id').getStore().load({
                        callback: function() {
                            var index = base_form.findField('MOArea_id').getStore().findBy(function(rec) {
                                return (rec.get('MOArea_id') == MOArea_id);
                            });
            
                            if ( index >= 0 ) {
                                base_form.findField('MOArea_id').setValue(MOArea_id);
                            }
                            else {
                                base_form.findField('MOArea_id').clearValue();
                            }
                        },
                        params: {
                            Lpu_id: current_window.Lpu_id
                        }
                    });

				},
				url: '/?c=LpuPassport&m=loadLpuBuilding'
			});
		}
		if ( this.action != 'view' )
			Ext.getCmp('LBEW_LpuBuildingPass_Name').focus(true, 100);
		else
			this.buttons[3].focus();
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var _this = this;
		
		this.LpuBuildingEditForm = new Ext.form.FormPanel({
			autoScroll: true,
			frame: true,
			//layout: 'column',
			layout: 'fit',
			region: 'north',
			id: 'LpuBuildingEditForm',
			bodyStyle: 'padding: 5px',
            labelAlign: 'right',
			items: 
			[
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_data',
                    layout: 'form',
                    title: lang['1_obschie_dannyie'],
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 220,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
                                id: 'LBEW_Lpu_id',
                                name: 'Lpu_id',
                                value: 0,
                                xtype: 'hidden'
                            },{
                                id: 'LBEW_LpuBuildingPass_id',
                                name: 'LpuBuildingPass_id',
                                value: 0,
                                xtype: 'hidden'
                            },{
                                xtype: 'textfield',
                                allowBlank: false,
                                id: 'LBEW_LpuBuildingPass_Name',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['naimenovanie'],
                                name: 'LpuBuildingPass_Name'
                            },{
                                allowBlank: false,
                                displayField: 'MOArea_Name',
                                fieldLabel: lang['ploschadka'],
                                codeField: 'MOArea_Member',
                                hiddenName: 'MOArea_id',
                                id: 'LBEW_MOArea_id',
                                width: 200,
                                editable: false,
                                mode: 'local',
                                resizable: true,
                                store: new Ext.data.Store({
                                    autoLoad: false,
                                    reader: new Ext.data.JsonReader({
                                        id: 'MOArea_id'
                                    }, [
                                        { name: 'MOArea_id', mapping: 'MOArea_id' },
										{ name: 'MOArea_Member', mapping: 'MOArea_Member' },
                                        { name: 'MOArea_Name', mapping: 'MOArea_Name' }
                                    ]),
                                    url:'/?c=LpuPassport&m=loadMOArea'
                                }),
                                tpl: new Ext.XTemplate(
                                    '<tpl for="."><div class="x-combo-list-item">',
                                    '<font color="red">{MOArea_Member}</font>&nbsp; {MOArea_Name}',
                                    '</div></tpl>'
                                ),
                                triggerAction: 'all',
                                valueField: 'MOArea_id',
                                tabIndex: TABINDEX_LPEEW + 2,
                                xtype: 'swbaselocalcombo'
                            },{
                                xtype: 'textfield',
                                //width: 164,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['identifikator_zdaniya'],
                                allowBlank: false,
                                name: 'LpuBuildingPass_BuildingIdent'
                            },{
                                xtype: 'swcommonsprcombo',
                                //width: 164,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['vid_zdaniya_po_primeneniyu'],
                                allowBlank: false,
                                comboSubject: 'LpuBuildingType',
                                name: 'LpuBuildingType_id'
                            },{
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['naznachenie'],
                                comboSubject: 'BuildingAppointmentType',
                                name: 'BuildingAppointmentType_id'
                            },{
                                xtype: 'swcommonsprcombo',
                                allowBlank: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['forma_vladeniya'],
                                comboSubject: 'PropertyType',
                                name: 'PropertyType_id'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['moschnost_po_proektu_chislo_koek'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_PowerProjBed'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['statsionarnyie_mesta'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_StatPlace'
                            },{
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['obschaya_ploschad_zdaniya_kv_m'],
                                regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                name: 'LpuBuildingPass_TotalArea'
                            },{
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['poleznaya_ploschad_zdaniya_kv_m'],
                                name: 'LpuBuildingPass_EffBuildVol'
                            },{
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['rabochaya_ploschad_kv_m'],
                                name: 'LpuBuildingPass_WorkArea'
                            },{
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ploschad_platnyih_otdeleniy_kv_m'],
                                name: 'LpuBuildingPass_WorkAreaWardSect'
                            },{
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ploschad_kabinetov_vrachebnogo_priema_kv_m'],
                                name: 'LpuBuildingPass_OfficeArea'
                            },{
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ploschad_koechnyih_otdeleniy_kv_m'],
                                name: 'LpuBuildingPass_BedArea'
                            },{
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
								//readOnly: true,
                                fieldLabel: lang['v_t_ch_palat_kv_m'],
                                name: 'LpuBuildingPass_WorkAreaWard'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_kabinetov_vrachebnogo_priema'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_MedWorkCabinet'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['moschnost_po_proektu_chislo_posescheniy'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_PowerProjViz'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ambulatornyie_mesta'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_AmbPlace'
                            },{
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['obyem_zdaniya_kub_m'],
                                name: 'LpuBuildingPass_BuildVol'
                            },{
                                fieldLabel: lang['na_balanse'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_IsBalance',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
								tabIndex: TABINDEX_LPBEW + 9,
								enableKeyEvents: true,
								fieldLabel: 'Широта',
								regex: /^-?\d{1,2}(\.\d{0,6})?$/,
								xtype: 'numberfield',
								allowNegative: true,
								allowBlank: false,
								decimalPrecision: 6,
								maxValue: 90,
								minValue: -90,
								listeners: {
									'keydown': function(field, e) {
										if (
											field.getRawValue().length > 0
											&& (
												(field.getRawValue().length == 2 && field.getRawValue().substr(0, 1) != '-')
												|| (field.getRawValue().length == 3 && field.getRawValue().substr(0, 1) == '-')
											)
											&& e.getKey() != Ext.EventObject.BACKSPACE
											&& e.getKey() != Ext.EventObject.DELETE
										) {
											field.setRawValue(field.getRawValue() + '.');
										}
									}
								},
								name: 'LpuBuildingPass_CoordLat'
							},{
								tabIndex: TABINDEX_LPBEW + 9,
								enableKeyEvents: true,
								fieldLabel: 'Долгота',
								regex: /^-?\d{1,3}(\.\d{0,6})?$/,
								xtype: 'numberfield',
								allowNegative: true,
								allowBlank: false,
								decimalPrecision: 6,
								maxValue: 180,
								minValue: -180,
								listeners: {
									'keydown': function(field, e) {
										if (
											field.getRawValue().length > 0
											&& (
												(field.getRawValue().length == 3 && field.getRawValue().substr(0, 1) != '-')
												|| (field.getRawValue().length == 4 && field.getRawValue().substr(0, 1) == '-')
											)
											&& e.getKey() != Ext.EventObject.BACKSPACE
											&& e.getKey() != Ext.EventObject.DELETE
										) {
											field.setRawValue(field.getRawValue() + '.');
										}
									}
								},
								name: 'LpuBuildingPass_CoordLong'
							}
                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_Constructions',
                    layout: 'form',
                    title: lang['2_konstruktsii'],
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 220,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [{
                                id: 'LBEW_BuildingOverlapType_id',
                                name: 'BuildingOverlapType_id',
                                xtype: 'hidden'
                            },{
                                id: 'LBEW_BuildingHoldConstrType_id',
                                name: 'BuildingHoldConstrType_id',
                                xtype: 'hidden'
                            },{
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['tip_proekta_zdaniya'],
                                comboSubject: 'BuildingType',
                                name: 'BuildingType_id'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['nomer_proekta'],
                                //autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                //maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_NumProj'
                            },{
                                xtype: 'swdatefield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['data_razrabotki_proektnoy_dokumentatsii'],
                                name: 'LpuBuildingPass_YearProjDoc',
                                minValue: '01.01.1800'
                            },{
                                xtype: 'swdatefield',
                                allowBlank: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['data_postroyki'],
                                name: 'LpuBuildingPass_YearBuilt',
                                minValue: '01.01.1800'
                            },{
                                xtype: 'swdatefield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['data_posledney_rekonstruktsii_kapitalnogo_remonta'],
                                name: 'LpuBuildingPass_YearRepair',
                                minValue: '01.01.1800'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ventilyatsiya'],
                                hiddenName: 'LpuBuildingPass_IsVentil'
                            },{
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['tip_po_klassu_tehnologii'],
                                allowBlank: false,
                                comboSubject: 'BuildingTechnology',
                                name: 'BuildingTechnology_id'
                            },{
                                allowBlank: true,
                                width: 600,
                                fieldLabel: lang['nesuschie_konstruktsii'],
								allowLowLevelRecordsOnly: false,
                                id: 'LBEW_BuildingHoldConstrType_Name',
                                name: 'BuildingHoldConstrType_Name',
                                object: 'BuildingHoldConstrType',
                                selectionWindowParams: {
                                    height: 500,
                                    title: lang['nesuschie_konstruktsii'],
                                    width: 600
                                },
                                //useNameWithPath: false,
                                valueFieldId: 'LBEW_BuildingHoldConstrType_id',
                                xtype: 'swtreeselectionfield'
                            },{
                                allowBlank: true,
                                width: 600,
                                fieldLabel: lang['perekryitiya'],
                                id: 'LBEW_BuildingOverlapType_Name',
                                name: 'BuildingOverlapType_Name',
                                object: 'BuildingOverlapType',
                                selectionWindowParams: {
                                    height: 500,
                                    title: lang['perekryitiya'],
                                    width: 600
                                },
                                valueFieldId: 'LBEW_BuildingOverlapType_id',
                                xtype: 'swtreeselectionfield'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['etajnost'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_Floors',
                                allowBlank: false
                            },{
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['tekuschee_sostoyanie_zdaniya'],
                                comboSubject: 'BuildingCurrentState',
                                name: 'BuildingCurrentState_id'
                            }
                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_Communications',
                    layout: 'form',
                    title: lang['3_kommunikatsii'],
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 220,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['konditsionirovanie'],
                                hiddenName: 'LpuBuildingPass_IsAirCond'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['elektrosnabjenie'],
                                hiddenName: 'LpuBuildingPass_IsElectric'
                            },{
                                fieldLabel: lang['nalichie_nezavisimyih_istochnikov_energosnabjeniya'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_IsFreeEnergy',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['holodnoe_vodosnabjenie'],
                                hiddenName: 'LpuBuildingPass_IsColdWater'
                            },{
                                comboSubject: 'DHotWater',
                                hiddenName: 'DHotWater_id',
                                xtype: 'swcommonsprcombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['goryachee_vodosnabjenie']
                                //hiddenName: 'LpuBuildingPass_IsHotWater'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['priboryi_ucheta_vodosnabjeniya'],
                                hiddenName: 'LpuBuildingPass_IsWaterMeters'
                            },{
                                xtype: 'swcommonsprcombo',
                                comboSubject: 'DHeating',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['otoplenie'],
                                hiddenName: 'DHeating_id'
                                //hiddenName: 'LpuBuildingPass_IsHeat'
                            },{
                                xtype: 'swcommonsprcombo',
                                comboSubject: 'FuelType',
                                //prefix: 'passport_',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['vid_topliva_otopleniya'],
                                hiddenName: 'FuelType_id'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['priboryi_ucheta_tepla'],
                                hiddenName: 'LpuBuildingPass_IsHeatMeters'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['nalichie_utepleniya_fasada'],
                                hiddenName: 'LpuBuildingPass_IsInsulFacade'
                            },{
                                xtype: 'swcommonsprcombo',
                                comboSubject: 'DCanalization',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['kanalizatsiya'],
                                hiddenName: 'DCanalization_id'
                                //hiddenName: 'LpuBuildingPass_IsSewerage'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['lechebnoe_gazosnabjenie'],
                                hiddenName: 'LpuBuildingPass_IsMedGas'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['byitovoe_gazosnabjenie'],
                                hiddenName: 'LpuBuildingPass_IsDomesticGas'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['telefonizatsiya'],
                                hiddenName: 'LpuBuildingPass_IsPhone'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_passajirskih_liftov'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_PassLift'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_passajirskih_liftov_trebuyuschih_zamenyi'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_PassLiftReplace'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_meditsinskih_liftov'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_HostLift'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_meditsinskih_liftov_trebuyuschih_zamenyi'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_HostLiftReplace'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_tehnologicheskih_podyemnikov'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_TechLift'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['chislo_tehnologicheskih_podyemnikov_trebuyuschih_zamenyi'],
                                autoCreate: {tag: "input", maxLength: "2", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_TechLiftReplace'
                            },{
                                xtype: 'swcommonsprcombo',
                                comboSubject: 'DLink',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['kanal_svyazi'],
                                hiddenName: 'DLink_id'
                                //hiddenName: 'LpuBuildingPass_IsSewerage'
                            }
                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_Prices',
                    layout: 'form',
                    title: lang['4_otsenki_stoimosti'],
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 220,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['pervonachalnaya_stoimost_rub'],
                                name: 'LpuBuildingPass_PurchaseCost'
                            },{
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['fakticheskaya_stoimost_rub'],
                                name: 'LpuBuildingPass_FactVal'
                            },{
                                xtype: 'swdatefield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['data_otsenki_stoimosti'],
                                name: 'LpuBuildingPass_ValDT'
                            },{
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['ostatochnaya_stoimost_rub'],
                                name: 'LpuBuildingPass_ResidualCost'
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['iznos_%'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_WearPersent'
                            }

                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_FireBeware',
                    layout: 'form',
                    title: lang['5_pojarnaya_bezopasnost'],
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 220,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
                                fieldLabel: lang['avtomaticheskaya_pojarnaya_signalizatsiya_v_zdanii'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_IsAutoFFSig',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['ohrannaya_signalizatsiya_v_zdanii'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_IsSecurAlarm',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['knopka_brelok_ekstrennogo_vyizova_militsii_v_zdanii'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_IsCallButton',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['sistema_opovescheniya_i_upravleniya_evakuatsiey_lyudey_pri_pojare_v_zdanii'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_IsWarningSys',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['protivopojarnoe_vodosnabjenie_zdaniya'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_IsFFWater',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['vyivod_signala_o_srabatyivanii_sistem_protivopojarnoy_zaschityi_v_podrazdelenii_pojarnoy_ohranyi_v_zdanii'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_IsFFOutSignal',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['pryamaya_telefonnaya_svyaz_s_podrazdeleniem_pojarnoy_ohranyi_dlya_zdaniya'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_IsConnectFSecure',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                xtype: 'textfield',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['kolichestvo_narusheniy_trebovaniy_pojarnoy_bezopasnosti'],
                                autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                                maskRe: /[0-9]/,
                                name: 'LpuBuildingPass_CountDist'
                            },{
                                fieldLabel: lang['nalichie_evakuatsionnyih_putey_i_vyihodov_v_zdanii'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_IsEmergExit',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['obespechennost_personala_zdaniya_uchrejdeniya_sredstvami_individualnoy_zaschityi_organov_dyihaniya'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_RespProtect',
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
                                fieldLabel: lang['obespechennost_personala_zdaniya_uchrejdeniya_nosilkami_dlya_evakuatsii_malomobilnyih_patsientov'],
                                xtype: 'checkbox',
                                name: 'LpuBuildingPass_StretProtect',//хз
                                tabIndex: TABINDEX_LPEEW + 1
                            },{
								regex:new RegExp('(^[0-9]{0,}\.[0-9]{0,2})$'),
								xtype: 'numberfield',
								allowNegative: false,
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['udalenie_ot_blijayshego_pojarnogo_podrazdeleniya_km'],
                                name: 'LpuBuildingPass_FSDis'//хз
                            }
                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style:'margin-bottom: 0.5em;',
                    //border: true,
                    collapsible: true,
                    id: 'Lpu_TechState',
                    layout: 'form',
                    title: lang['6_tehnicheskoe_sostoyanie'],
                    items: [{
                        xtype: 'panel',
                        layout: 'form',
                        labelWidth: 220,
                        border: false,
                        bodyStyle:'background:#DFE8F6;padding:5px;',
                        items: [
                            {
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['trebuet_blagoustroystva'],
                                hiddenName: 'LpuBuildingPass_IsRequirImprovement'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['nahoditsya_v_avariynom_sostoyanii'],
                                hiddenName: 'LpuBuildingPass_IsBuildEmerg',
                                allowBlank: false
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['trebuet_rekonstruktsii'],
                                hiddenName: 'LpuBuildingPass_IsNeedRec'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['trebuet_kapitalnogo_remonta'],
                                hiddenName: 'LpuBuildingPass_IsNeedCap'
                            },{
                                xtype: 'swyesnocombo',
                                tabIndex: TABINDEX_LPBEW + 9,
                                fieldLabel: lang['trebuet_snosa'],
                                hiddenName: 'LpuBuildingPass_IsNeedDem'
                            }
                        ]
                    }]
                }),
                new sw.Promed.Panel({
                    autoHeight: true,
                    style: 'margin-bottom: 0.5em;',
                    border: true,
                    collapsible: true,
                    collapsed: false,
                    id: 'LBEW_MOSections',
                    layout: 'form',
                    title: lang['7_otdeleniya'],
                    items: [
                        new sw.Promed.ViewFrame({
                            actions: [
                                {name: 'action_add', handler: function() { this.openMOSectionsEditWindow('add'); }.createDelegate(this) },
                                {name: 'action_edit', handler: function() { this.openMOSectionsEditWindow('edit'); }.createDelegate(this) },
                                {name: 'action_view', handler: function() { this.openMOSectionsEditWindow('view'); }.createDelegate(this) },
                                {name: 'action_delete', handler: function() { this.deleteMOSection(); }.createDelegate(this) },
                                {name: 'action_refresh', hidden: true, disabled: true}
                            ],
                            autoExpandColumn: 'autoexpand',
                            autoExpandMin: 150,
                            autoLoadData: false,
                            border: false,
                            scheme: 'fed',
                            dataUrl: '/?c=LpuPassport&m=loadMOSections',
                            focusOn: {
                                name: 'LBEW_CancelButton',
                                type: 'button'
                            },
                            focusPrev: {
                                name: 'LBEW_CancelButton',
                                type: 'button'
                            },
                            id: 'LBEW_MOSectionsGrid',
                            paging: false,
                            region: 'center',
                            params: {
                                LpuBuildingPass_id: _this.LpuBuildingPass_id,
                                SectionsOnly: true
                            },
                            //root: 'data',
                            stringfields: [
                                {name: 'LpuSection_id', type: 'int', header: 'id', hidden: true},
                                {name: 'MOSectionBase_id', type: 'int', hidden: true},//показывает есть ли данная связь в базе
                                {name: 'LpuBuilding_Name', type: 'string', header: lang['podrazdelenie'], width: 270},
                                {name: 'LpuUnit_Name', type: 'string', header: lang['gruppa_otdeleniy'], width: 270},
                                {name: 'LpuSection_Name', type: 'string', id: 'autoexpand', header: lang['otdelenie']}
                            ],
                            toolbar: true,
                            totalProperty: 'totalCount'
                        })
                    ]
                })

			],
			reader: new Ext.data.JsonReader(
			{
				success: function() {
					//
				}
			}, 
			[
				{ name: 'Lpu_id' },
				{ name: 'LpuBuildingPass_id' },
				{ name: 'LpuBuildingPass_Name' },
				{ name: 'LpuBuildingType_id' },
				{ name: 'BuildingAppointmentType_id' },
				{ name: 'MOArea_id' },
				{ name: 'LpuBuildingPass_YearBuilt' },
				{ name: 'LpuBuildingPass_YearRepair' },
				{ name: 'LpuBuildingPass_PurchaseCost' },
				{ name: 'LpuBuildingPass_ResidualCost' },
				{ name: 'LpuBuildingPass_Floors' },
				{ name: 'LpuBuildingPass_EffBuildVol' },
				{ name: 'LpuBuildingPass_TotalArea' },
				{ name: 'LpuBuildingPass_WorkArea' },
				{ name: 'LpuBuildingPass_AmbPlace' },
				{ name: 'BuildingCurrentState_id' },
				{ name: 'DHotWater_id' },
				{ name: 'LpuBuildingPass_IsWarningSys' },
				{ name: 'LpuBuildingPass_IsCallButton' },
				{ name: 'LpuBuildingPass_IsAutoFFSig' },
				{ name: 'DHeating_id' },
				{ name: 'LpuBuildingPass_FSDis' },
				{ name: 'LpuBuildingPass_ValDT' },
				{ name: 'LpuBuildingPass_StretProtect' },
				{ name: 'DCanalization_id' },
				{ name: 'LpuBuildingPass_BuildVol' },
				{ name: 'LpuBuildingPass_IsBalance' },
				{ name: 'LpuBuildingPass_StatPlace' },
				{ name: 'LpuBuildingPass_WorkAreaWardSect' },
				{ name: 'LpuBuildingPass_WorkAreaWard' },
				{ name: 'LpuBuildingPass_PowerProjBed' },
				{ name: 'LpuBuildingPass_PowerProjViz' },
				{ name: 'LpuBuildingPass_OfficeArea' },
				{ name: 'BuildingType_id' },
				{ name: 'LpuBuildingPass_NumProj' },
				{ name: 'BuildingHoldConstrType_id' },
				{ name: 'BuildingHoldConstrType_Name' },
				{ name: 'BuildingOverlapType_id' },
				{ name: 'BuildingOverlapType_Name' },
				{ name: 'LpuBuildingPass_IsAirCond' },
				{ name: 'LpuBuildingPass_IsVentil' },
				{ name: 'LpuBuildingPass_IsElectric' },
				{ name: 'LpuBuildingPass_IsPhone' },
				{ name: 'LpuBuildingPass_IsColdWater' },
				{ name: 'LpuBuildingPass_IsDomesticGas' },
				{ name: 'LpuBuildingPass_IsMedGas' },
				{ name: 'LpuBuildingPass_HostLift' },
				{ name: 'LpuBuildingPass_HostLiftReplace' },
				{ name: 'LpuBuildingPass_PassLift' },
				{ name: 'LpuBuildingPass_PassLiftReplace' },
				{ name: 'LpuBuildingPass_TechLift' },
				{ name: 'LpuBuildingPass_TechLiftReplace' },
				{ name: 'LpuBuildingPass_WearPersent' },
				{ name: 'PropertyType_id' },
				{ name: 'LpuBuildingPass_IsInsulFacade' },
				{ name: 'LpuBuildingPass_IsHeatMeters' },
				{ name: 'LpuBuildingPass_IsWaterMeters' },
				{ name: 'LpuBuildingPass_IsRequirImprovement' },
				{ name: 'LpuBuildingPass_YearProjDoc' },
				{ name: 'DLink_id' },
				{ name: 'LpuBuildingPass_FactVal' },
				{ name: 'LpuBuildingPass_ValDT' },
				{ name: 'LpuBuildingPass_IsSecurAlarm' },
				{ name: 'LpuBuildingPass_IsWarningSys' },
				{ name: 'LpuBuildingPass_IsFFWater' },
				{ name: 'LpuBuildingPass_IsFFOutSignal' },
				{ name: 'LpuBuildingPass_IsConnectFSecure' },
				{ name: 'LpuBuildingPass_CountDist' },
				{ name: 'LpuBuildingPass_IsEmergExit' },
				{ name: 'LpuBuildingPass_RespProtect' },
				{ name: 'LpuBuildingPass_IsBuildEmerg' },
				{ name: 'LpuBuildingPass_IsNeedRec' },
				{ name: 'LpuBuildingPass_IsNeedCap' },
				{ name: 'LpuBuildingPass_IsNeedDem' },
                { name: 'DHotWater_id' },
                { name: 'DCanalization_id' },
                { name: 'LpuBuildingPass_BedArea' },
                { name: 'LpuBuildingPass_BuildingIdent' },
                { name: 'BuildingTechnology_id' },
                { name: 'LpuBuildingPass_MedWorkCabinet' },
                { name: 'LpuBuildingPass_IsFreeEnergy' },
                { name: 'FuelType_id' },
                { name: 'DHeating_id' },
                { name: 'LpuBuildingPass_CoordLong' },
                { name: 'LpuBuildingPass_CoordLat' }
			]),
			url: '/?c=LpuPassport&m=saveLpuBuilding'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_LPBEW + 9,
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_LPBEW + 9,
				text: BTN_FRMCANCEL
			}],
			items: [this.LpuBuildingEditForm]
		});
		sw.Promed.swLpuBuildingEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});