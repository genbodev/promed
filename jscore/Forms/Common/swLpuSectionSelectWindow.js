/**
* swLpuSectionSelectWindow - окно выбора ЛПУ, в случае если человек прикреплен к нескольким ЛПУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      20.01.2010
*
* @class        sw.Promed.swLpuSectionSelectWindow
* @extends      Ext.Window
*/
sw.Promed.swLpuSectionSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	id:'swLpuSectionSelectWindow',
	doSelect: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}
		
		var win = this;
		var base_form = this.findById('LpuSectionSelectForm').getForm();
		var lpu_section_id = this.findById('LSSW_LpuSectionCombo').getValue();
		var evnsection_setdate = base_form.findField('EvnSection_setDate').getValue();
		var evnsection_settime = base_form.findField('EvnSection_setTime').getValue();
		var evn_section_dis_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnSection_setTime').getValue());
		var evn_ps_set_dt = this.EvnPS_setDT;

		if (evn_ps_set_dt  != null && evn_ps_set_dt  > evn_section_dis_dt) {
			sw.swMsg.alert(lang['oshibka'], lang['data_vremya_vyipiski_iz_otdeleniya_menshe_datyi_vremeni_postupleniya']);
			return false;
		}
		
		if ( !lpu_section_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrano_otdelenie'], function() { this.findById('LSSW_LpuSectionCombo').focus(true); }.createDelegate(this) );
			return false;
		}

		if ( !evnsection_setdate|| !evnsection_settime ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_data_ili_vremya_gospitalizatsii'], function() { base_form.findField('EvnSection_setDate').focus(true); }.createDelegate(this) );
			return false;
		}
		
		var age = swGetPersonAge(win.Person_BirthDay, evnsection_setdate);
		if (!options.ignoreLpuSectionAgeCheck && ((base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id') == 1 && age <= 17) || (base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id') == 2 && age >= 18))) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						options.ignoreLpuSectionAgeCheck = true;
						win.doSelect(options);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vozrastnaya_gruppa_otdeleniya_ne_sootvetstvuyut_vozrastu_patsienta_prodoljit'],
				title: lang['vopros']
			});
			
			return false;
		}

		this.LpuSection_id = lpu_section_id;
		this.EvnSection_setDate = evnsection_setdate;
		this.EvnSection_setTime = evnsection_settime;

		this.controlSavingForm_DepartmentSelectionLPU(function(res){
			var win = this;
			if(res){
				sw.swMsg.show({
					title: 'Внимание!',
					msg: 'Не указаны сведения о направлении. При оказании неотложной помощи обязательно должны быть заполнены поля «№ направления» и «Дата направления», или выбрано электронное направление. Заполните раздел «Кем направлен»',
					buttons: {yes: 'Редактировать КВС', no: 'Отмена'},
					fn: function(butn){
						if (butn == 'no'){
							return false;
						}else{
							win.hide();
							win.onSelect({
								openEvnPSPriemEditWindow: true
							});
						}
					}
				});
			}else{
				win.hide();
				win.onSelect({
					LpuSection_id: win.LpuSection_id,
					EvnSection_setDate: win.EvnSection_setDate,
					EvnSection_setTime: win.EvnSection_setTime
				});
			}
			return true;
		}.createDelegate(this));
		/*
		this.hide();
		this.onSelect({
			LpuSection_id: lpu_section_id,
			EvnSection_setDate: evnsection_setdate,
			EvnSection_setTime: evnsection_settime
		});
		*/
		return true;
	},
	controlSavingForm_DepartmentSelectionLPU: function(callback){
		var cb = callback;
		if(!getRegionNick().inlist(['penza']) || !this.EvnPS_id) {
			if(typeof cb == "function") cb(false);
			return false;
		}
		Ext.Ajax.request({
			callback: function(options, success, response) {
				var cb = this;
				var flag = false;
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					var r = response_obj[0];
					if(
						r.MedicalCareFormType_id == 2
					){
						var directionNumDate = (!r.EvnDirection_Num && !r.EvnDirection_setDate) ? true : false;
						var extraEvnPS = (r.EvnPS_IsWithoutDirection != 2) ? true : false;
						if(directionNumDate && extraEvnPS){
							flag = true;
						}else{
							flag = false;
						}
					}
				}
				if(typeof cb == "function") cb(flag);
			}.bind(cb),
			params: {
				EvnPS_id: this.EvnPS_id
			},
			url: '/?c=EvnPS&m=controlSavingForm_DepartmentSelectionLPU'
		});
	},
	/**
	 * Конструктор
	 */
	initComponent: function() {
		var win = this;
		
    	Ext.apply(this, {
			buttons: [{
				handler : function(button, event) {
					this.doSelect();
				}.createDelegate(this),
				iconCls : 'ok16',
				tabIndex: 4093,
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function(button, event) {
					this.hide();
				}.createDelegate(this),
				iconCls : 'cancel16',
				onShiftTabAction: function () {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.findById('LSSW_LpuBuildingCombo').focus(true);
				}.createDelegate(this),
				tabIndex: 4094,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.FormPanel({
				autoHeight: true,
				border: false,
				frame: true,
				id: 'LpuSectionSelectForm',
				labelWidth: 100,
				layout: 'form',
				style: 'padding: 3px',
				items: [ new sw.Promed.SwLpuBuildingGlobalCombo({
					id: 'LSSW_LpuBuildingCombo',
					lastQuery: '',
					linkedElements: [
						'LSSW_LpuSectionCombo'
					],
					listWidth: 700,
					tabIndex: 4091,
					validateOnBlur: true,
					width: 480
				}), new sw.Promed.SwDateField({
					fieldLabel: lang['data'],
					name: 'EvnSection_setDate',
					plugins: 
					[new Ext.ux.InputTextMask('99.99.9999', false)],
					listeners: {
						'change': function(field, newValue) {
							var
								age = swGetPersonAge(win.Person_BirthDay, newValue),
								base_form = win.findById('LpuSectionSelectForm').getForm(),
								isUfa = (getRegionNick() == 'ufa');

							var LpuSection_id = base_form.findField('LpuSection_id').getValue();

							var params = {
								arrayLpuUnitType: win.arrayLpuUnitType
							}

							if ( !Ext.isEmpty(newValue) ) {
								params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
							}

							// Для пациентов 18 лет и старше в поле «Отделение» не отображать отделения с указанной возрастной группой «Детское» (реализовать в виде фильтра в комбобоксе)
							if ( age >= 18 && !isUfa ) {
								params.WithoutChildLpuSectionAge = true;
							}

							setLpuSectionGlobalStoreFilter(params);
							base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

							if ( !Ext.isEmpty(LpuSection_id) ) {
								var index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
									return (rec.get('LpuSection_id') == LpuSection_id);
								});

								if ( index == -1 ) {
									base_form.findField('LpuSection_id').clearValue();
								}
							}
						}
					},
					xtype: 'swdatefield',
					format: 'd.m.Y'
				}),{
						fieldLabel: lang['vremya'],
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnSection_setTime',
						onTriggerClick: function() {
							var base_form = this.findById('LpuSectionSelectForm').getForm();
							var time_field = base_form.findField('EvnSection_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								callback: function() {
									base_form.findField('EvnSection_setDate').fireEvent('change', base_form.findField('EvnSection_setDate'), base_form.findField('EvnSection_setDate').getValue());
								},
								dateField: base_form.findField('EvnSection_setDate'),
								loadMask: true,
								setDate:false,
								setDateMaxValue:false,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: 'swLpuSectionSelectWindow'
							});
						}.createDelegate(this),
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: TABINDEX_EPSPEF + 5,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					},
				new sw.Promed.SwLpuSectionGlobalCombo({
					allowBlank: false,
					id: 'LSSW_LpuSectionCombo',
					lastQuery: '',
					listWidth: 700,
					parentElementId: 'LSSW_LpuBuildingCombo',
					tabIndex: 4092,
					validateOnBlur: true,
					width: 480
				})]
			})]
		});
		sw.Promed.swLpuSectionSelectWindow.superclass.initComponent.apply(this, arguments);
	}, //end initComponent()
	modal: true,
	//onSelect: Ext.emptyFn,
	plain: false,
	resizable: false,
	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swLpuSectionSelectWindow.superclass.show.apply(this, arguments);
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'); 
		var base_form = this.findById('LpuSectionSelectForm').getForm();
		base_form.reset();

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.arrayLpuUnitType = new Array();
		this.formMode = arguments[0].formMode || null;
		this.LpuSection_uid = arguments[0].LpuSection_uid || null;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.onSelect = arguments[0].onSelect || Ext.emptyFn;
		this.Person_BirthDay = arguments[0].Person_BirthDay || null;
		this.EvnPS_setDT = arguments[0].EvnPS_setDT || null;
		this.EvnPS_id = arguments[0].EvnPS_id || null;
		
		swLpuBuildingGlobalStore.clearFilter();
		swLpuSectionGlobalStore.clearFilter();

		base_form.findField('LpuBuilding_id').getStore().removeAll();
		base_form.findField('LpuSection_id').getStore().removeAll();
		
		switch(this.formMode) {
			case 'hospitalization':
				base_form.findField('LpuBuilding_id').setContainerVisible(false);
				base_form.findField('EvnSection_setDate').setContainerVisible(true);

				var LpuUnitType_SysNick = '';

				if ( !Ext.isEmpty(this.LpuSection_uid) ) {
					var idx = swLpuSectionGlobalStore.findBy(function(rec) {
						return (rec.get('LpuSection_id') == this.LpuSection_uid);
					}.createDelegate(this));

					if ( idx >= 0 ) {
						LpuUnitType_SysNick = swLpuSectionGlobalStore.getAt(idx).get('LpuUnitType_SysNick');
					}
				}

				if ( LpuUnitType_SysNick.toString().inlist([ 'priem' ]) ) {
					this.arrayLpuUnitType = [ '2', '3', '4', '5' ];
				}
				else if ( LpuUnitType_SysNick.toString().inlist([ 'stac', 'dstac' ]) ) {
					this.arrayLpuUnitType = [ '2', '3' ];
				}
				else if ( LpuUnitType_SysNick.toString().inlist([ 'polka', 'hstac', 'pstac' ]) ) {
					this.arrayLpuUnitType = [ '4', '5' ];
				}

				if ( arguments[0].EvnSection_setDate ) {
					base_form.findField('EvnSection_setDate').setValue(arguments[0].EvnSection_setDate);
					base_form.findField('EvnSection_setDate').fireEvent('change', base_form.findField('EvnSection_setDate'), base_form.findField('EvnSection_setDate').getValue());
				}

				base_form.findField('LpuSection_id').focus(true, 100);
				break;
			default:
				base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
				base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
				base_form.findField('LpuBuilding_id').setContainerVisible(true);
				base_form.findField('EvnSection_setDate').setContainerVisible(false);
				base_form.findField('LpuBuilding_id').focus(true, 100);
		}
		this.doLayout();
		this.syncSize();
	}, //end show()
	title: lang['vyibor_otdeleniya_lpu'],
	width: 650
});