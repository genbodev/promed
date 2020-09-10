/**
 * swHomeVisitListWindow - форма "Журнал вызовов на дом"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009-2013, Swan.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @prefix       hvlw
 * @tabindex     TABINDEX_HVLW
 * @version      September, 2013
 */
/*NO PARSE JSON*/
sw.Promed.swHomeVisitListWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_HVL,
	iconCls: 'workplace-mp16',
	id: 'swHomeVisitListWindow',
	readOnly: false,
        notifyArmTypeList: ['regpol'],
        callList: [],
        playNotification: function()
        {
            Ext.get('swHomeVisitListWindowNotification').dom.play();
        },
	
	/**
	 * Идентификатор выбранного МО
	 */
	Lpu_id: null,
	/**
	 * Идентификатор выбранного вызова на дом
	 */
	HomeVisit_id: null,
	/**
	 * Идентификатор участка выбранного вызова на дом
	 */
	LpuRegion_id: null,
	/**
	 * Функция возврашающся ссылку на родительский элемент
	 */
	getOwner: null,
	
	/**
	 * Дата, на которую отображаются вызовы на дом
	 */
	date: null,

	/**
	 * Маска для загрузки
	 */
	loadMask: null,
	
	/**
	 * Данные человека
	 */
	personData: null,
	
	/**
	 * Загрузка вызовов на дом
	 *
	 * @param date Дата, на которую загружать вызовы на дом
	 */
	loadHomeVisits: function(mode)
	{
		var btn = this.getPeriodToggle(mode);
		if (btn) 
		{
			if (mode != 'range')
			{
				if (this.mode == mode)
				{
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка на этот день
						return false;
				}
				else 
				{
					this.mode = mode;
				}
			}
			else 
			{
				btn.toggle(true);
				this.mode = mode;
			}
		}

		var params = new Object();
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');

		params.limit = 100;
		params.start = 0;
		
		var form = this.filtersPanel.getForm();
		log('dsfsdf',this.getGrid())
		this.getGrid().getGrid().getStore().removeAll()
		params.Person_Firname = form.findField('Person_Firname').getValue();
		params.Person_Secname = form.findField('Person_Secname').getValue();
		params.Person_Surname = form.findField('Person_Surname').getValue();
		params.Person_BirthDay = Ext.util.Format.date(form.findField('Person_Birthday').getValue(), 'd.m.Y');
		params.HomeVisit_setTimeFrom = form.findField('HomeVisit_setTimeFrom').getValue();
		params.HomeVisit_setTimeTo = form.findField('HomeVisit_setTimeTo').getValue();
		params.HomeVisitStatus_id = form.findField('HomeVisitStatus_id').getValue();
		params.HomeVisitCallType_id = form.findField('HomeVisitCallType_id').getValue();
		params.Lpu_id = form.findField('Lpu_id').getValue();
		if( !['regpol','regpol6'].in_array(this.type) && !params.Lpu_id){
			params.allLpu = 1;
		} else {
			params.allLpu = null;
		}
		if(form.findField('MedStaffFact_id').getValue() && form.findField('MedStaffFact_id').getStore().getById(form.findField('MedStaffFact_id').getValue())){
			params.MedPersonal_id = form.findField('MedStaffFact_id').getStore().getById(form.findField('MedStaffFact_id').getValue()).get('MedPersonal_id');
			params.MedStaffFact_id = form.findField('MedStaffFact_id').getValue();
		} else {
			params.MedPersonal_id = '';
			params.MedStaffFact_id = '';
		}
		var win = this;
        var MedStaffFact = this.filtersPanel.getForm().findField('MedStaffFact_id');
		this.medstafffact_filter_params = { isDoctor:true, Lpu_id:params.Lpu_id, withLpuRegionOnly:true };
		if(getRegionNick() == 'kareliya'){
			this.medstafffact_filter_params.isDoctor = false;
			this.medstafffact_filter_params.withLpuRegionOnly = false;
		}
		if( ['regpol','regpol6'].in_array(win.type) ){ this.medstafffact_filter_params.LpuRegionType_HomeVisit = 'all';}
		this.medstafffact_filter_params.dateFrom = params.begDate;
		this.medstafffact_filter_params.dateTo = params.endDate;
		if(swMedStaffFactGlobalStore.data.length == 0){
			MedStaffFact.getStore().load({
				callback:function(){
					MedStaffFact.lastQuery = '';
					setMedStaffFactGlobalStoreFilter(win.medstafffact_filter_params,MedStaffFact.getStore());
					if(params.MedStaffFact_id){
						var index = MedStaffFact.getStore().findBy(function(rec){return (rec.get('MedStaffFact_id')==params.MedStaffFact_id);});
						if(index > -1){
							MedStaffFact.setValue(params.MedStaffFact_id);
						} else {
							MedStaffFact.clearValue();
						}
					}
				}
			});
		} else {
			setMedStaffFactGlobalStoreFilter(win.medstafffact_filter_params);
			MedStaffFact.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
			if(params.MedStaffFact_id){
				var index = MedStaffFact.getStore().findBy(function(rec){return (rec.get('MedStaffFact_id')==params.MedStaffFact_id);});
				if(index > -1){
					MedStaffFact.setValue(params.MedStaffFact_id);
				} else {
					MedStaffFact.clearValue();
				}
			}
		}
		params.type = this.type;
        params.LpuRegion_id = form.findField('HVL_LpuRegion_id').getValue();
        params.LpuBuilding_id = form.findField('HVL_LpuBuilding_id').getValue();
        params.CallProfType_id = form.findField('HVL_CallProfType_id').getValue();
		this.Lpu_id = form.findField('Lpu_id').getValue();
		
		this.getGrid().getGrid().getStore().load({
			params: params
			
		});
	},
	/**
	 * Возвращает грид
	 */
	getGrid: function ()
	{
		return this.HomeVisitsGrid;
	},
	getPeriodToggle: function (mode)
	{
		switch(mode)
		{
		case 'day':
			return this.DoctorToolbar.items.items[6];
			break;
		case 'week':
			return this.DoctorToolbar.items.items[7];
			break;
		case 'month':
			return this.DoctorToolbar.items.items[8];
			break;
		case 'range':
			return this.DoctorToolbar.items.items[9];
			break;
		default:
			return null;
			break;
		}
	},
	/**
	 * Открытие окна одобрения вызова и назначения врача
	 */
	openHomeVisitApplyWindow: function(day)
	{
		getWnd('swHomeVisitApplyWindow').show({
			HomeVisit_id: this.HomeVisit_id,
			callback: function() {
				this.loadHomeVisits();
			}.createDelegate(this)
		})

	},

	/**
	 * Открытие окна отказа  в вызове
	 */
	openHomeVisitDenyWindow: function(day)
	{
		getWnd('swHomeVisitDenyWindow').show({
			HomeVisit_id: this.HomeVisit_id,
			callback: function() {
				this.loadHomeVisits();
			}.createDelegate(this)
		})

	},
	
	/**
	 * Открытие окна истории статусов
	 */
	openHomeVisitStatusHistWindow: function() { 
        var record = this.HomeVisitsGrid.getGrid().getSelectionModel().getSelected();

        getWnd('swHomeVisitStatusHistWindow').show({
			HomeVisit_id: record.get('HomeVisit_id')
        });
    },

	openHomeVisitCancelWindow: function() {
		var wnd = this;
		var grid = wnd.HomeVisitsGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('HomeVisit_id')) || record.get('HomeVisitStatus_id') == 5) {
			return;
		}

		var params = {
			HomeVisit_id: record.get('HomeVisit_id'),
			Person_Surname: record.get('Person_Surname'),
			Person_Firname: record.get('Person_Firname'),
			Person_Secname: record.get('Person_Secname'),
			Address_Address: record.get('Address_Address'),
			callback: function(){wnd.loadHomeVisits()}
		};

		getWnd('swHomeVisitCancelWindow').show(params);
	},

	/**
	 * Перемещение по календарю
	 */
	stepDay: function(day)
	{
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},

	/**
	 * На день назад
	 */
	prevDay: function ()
	{
		this.stepDay(-1);
	},

	/**
	 * И на день вперед
	 */
	nextDay: function ()
	{
		this.stepDay(1);
	},

	currentDay: function ()
	{
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentWeek: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
    	frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentMonth: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
    	frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},

	/**
	 * Маска при загрузке
	 */
	getLoadMask: function(MSG)
	{
		if (MSG)
		{
			delete(this.loadMask);
		}
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: MSG });
		}

		return this.loadMask;
	},

	getCurrentDateTime: function() 
	{
		var frm = this;
		frm.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
		{
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) 
			{
				if (success && response.responseText != '')
				{
					var result  = Ext.util.JSON.decode(response.responseText);
					frm.curDate = result.begDate;
					frm.curTime = result.begTime;
					frm.userName = result.pmUser_Name;
					frm.userName = result.pmUser_Name;
					// Проставляем время и режим
					this.mode = 'day';
					frm.currentDay();
					frm.loadHomeVisits('day');
					frm.getLoadMask().hide();
				}
			}
		});
	},

	/**
	 * Определение активности кнопки "Отменить" вызов.
	 * Выведено в функцию для тестов
	 * @param HomeVisitStatus_id Number
	 * @param CmpCallCard_id Number
	 * @param region String
	 * @returns {boolean}
	 */
	checkButtonCancel: function(HomeVisitStatus_id, CmpCallCard_id, region){
		if((!HomeVisitStatus_id || HomeVisitStatus_id.inlist([1, 3, 6])) && !(HomeVisitStatus_id == 1 && CmpCallCard_id > 0 && region.inlist(['ufa']))){
			return true;
		}else{
			return false;
		}
	},

	initComponent: function() {
        var that = this;

		this.dateMenu = new Ext.form.DateRangeField(
		{
			width: 150,
			testId: 'wnd_workplace_dateMenu',
			fieldLabel: lang['period'],
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.dateMenu.addListener('keydown',function (inp, e) 
		{
			var form = Ext.getCmp('swHomeVisitListWindow');
			if (e.getKey() == Ext.EventObject.ENTER)
			{
				e.stopEvent();
				form.loadHomeVisits('period');
			}
		});
		this.dateMenu.addListener('select',function () 
		{
			// Читаем расписание за период
			var form = Ext.getCmp('swHomeVisitListWindow');
			form.loadHomeVisits('period');
		});
		
		this.formActions = new Array();
		this.formActions.selectDate = new Ext.Action(
		{
			text: ''
		});
		this.formActions.prev = new Ext.Action(
		{
			text: lang['predyiduschiy'],
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function()
			{
				// на один день назад
				this.prevDay();
				this.loadHomeVisits('range');
			}.createDelegate(this)
		});
		this.formActions.next = new Ext.Action(
		{
			text: lang['sleduyuschiy'],
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function()
			{
				// на один день вперед
				this.nextDay();
				this.loadHomeVisits('range');
			}.createDelegate(this)
		});
		this.formActions.day = new Ext.Action(
		{
			text: lang['den'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-day16',
			pressed: true,
			handler: function()
			{
				this.currentDay();
				this.loadHomeVisits('day');
			}.createDelegate(this)
		});
		this.formActions.week = new Ext.Action(
		{
			text: lang['nedelya'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-week16',
			handler: function()
			{
				this.currentWeek();
				this.loadHomeVisits('week');
			}.createDelegate(this)
		});
		this.formActions.month = new Ext.Action(
		{
			text: lang['mesyats'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-month16',
			handler: function()
			{
				this.currentMonth();
				this.loadHomeVisits('month');
			}.createDelegate(this)
		});
		this.formActions.range = new Ext.Action(
		{
			text: lang['period'],
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function()
			{
				this.loadHomeVisits('range');
			}.createDelegate(this)
		});

		this.DoctorToolbar = new Ext.Toolbar(
		{
			items: 
			[
				this.formActions.prev, 
				{
					xtype : "tbseparator"
				},
				this.dateMenu,
				{
					xtype : "tbseparator"
				},
				this.formActions.next, 
				{
					xtype: 'tbfill'
				},
				this.formActions.day, 
				this.formActions.week, 
				this.formActions.month,
				this.formActions.range
			]
		});

		this.filtersPanel = new Ext.FormPanel({
			xtype: 'form',
			labelAlign: 'right',
			labelWidth: 50,
			items:
			[{
				listeners: {
					collapse: function(p) {
						this.doLayout();
					}.createDelegate(this),
					expand: function(p) {
						this.doLayout();
					}.createDelegate(this)
				},
				xtype: 'fieldset',
				style: 'margin: 5px 0 0 0',
				height: 140,
				title: lang['filtr'],
				collapsible: true,
				layout: 'column',
				items:
				[{
					layout: 'form',
					labelWidth: 110,
					items:
					[{
						xtype: 'textfieldpmw',
						width: 120,
						id: 'hvlwSearch_SurName',
						fieldLabel: lang['familiya'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									this.loadHomeVisits();
								}
							}.createDelegate(this)
						},
						name: 'Person_Surname'
					}, {
						xtype: 'textfieldpmw',
						width: 120,
						id: 'hvlwSearch_FirName',
						fieldLabel: lang['imya'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									this.loadHomeVisits();
								}
							}.createDelegate(this)
						},
						name: 'Person_Firname'
					}, {
						xtype: 'textfieldpmw',
						width: 120,
						id: 'hvlwSearch_SecName',
						fieldLabel: lang['otchestvo'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									this.loadHomeVisits();
								}
							}.createDelegate(this)
						},
						name: 'Person_Secname'
					}, {
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						id: 'hvlwSearch_BirthDay',
						fieldLabel: lang['data_rojdeniya'],
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									this.loadHomeVisits();
								}
							}.createDelegate(this)
						},
						name: 'Person_Birthday'
					}]
				}, {
					layout: 'form',
					labelWidth: 110,
					items:
					[{
						hiddenName: 'HomeVisitStatus_id',
						lastQuery: '',
						xtype: 'swhomevisitstatuscombo',
						id: 'hvlwSearch_HomeVisitStatus',
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									this.loadHomeVisits();
								}
							}.createDelegate(this)
						},
						name: 'HomeVisitStatus_id'
					}, {
						fieldLabel:lang['vrach'],
						hiddenName:'MedStaffFact_id',
						xtype:'swmedstafffactglobalcombo',
						width:220,
						listWidth:700,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<table style="border: 0;">',
							'<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
							'<td>',
								'<div style="font-weight: bold;">{MedPersonal_Fio}&nbsp;{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</div>',
								'<div style="font-size: 10px;">{PostMed_Name}</div>',
							'</td>',
							'</tr></table>',
							'</div></tpl>'
						),
						anchor:'auto'
					}, {
						comboSubject: 'HomeVisitCallType',
						fieldLabel: lang['tip_vyizova'],
						hiddenName: 'HomeVisitCallType_id',
						valueField: 'HomeVisitCallType_id',
						width: 200,
						tpl: '<tpl for="."><div class="x-combo-list-item">{HomeVisitCallType_Name}&nbsp;</div></tpl>',
						xtype: 'swcommonsprcombo'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['vremya_vyizova_s'],
								name: 'HomeVisit_setTimeFrom',
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								xtype: 'swtimefield'
							}]
						}, {
							border: false,
							labelWidth: 20,
							layout: 'form',
							items: [{
								fieldLabel: lang['po'],
								name: 'HomeVisit_setTimeTo',
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								xtype: 'swtimefield'
							}]
						}]
					}]
				}, {
					layout: 'form',
					labelWidth: 100,
					items:
					[{
						hiddenName: 'Lpu_id',
						lastQuery: '',
						xtype: 'swlpucombo',
						id: 'hvlwSearch_Lpu',
						listeners:
						{
							'keydown': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									this.loadHomeVisits();
								}
							}.createDelegate(this),
                            'change': function(combo, lpuId ,old) {
                                var form = that.filtersPanel;
                                var win = this;

								form.findById('HVLLpuBuilding_id').getStore().removeAll();
								form.findById('HVLLpuBuilding_id').clearValue();
								if (Ext.isEmpty(lpuId)) {
									form.findById('HVLLpuBuilding_id').disable();
								} else {
									form.findById('HVLLpuBuilding_id').enable();
									form.findById('HVLLpuBuilding_id').getStore().baseParams.Lpu_id = Ext.getCmp('hvlwSearch_Lpu').getValue();
									form.findById('HVLLpuBuilding_id').getStore().load();
								}

                                form.findById('HVLLpuRegion_id').getStore().removeAll();
                                var prof = form.findById('HVLCallProfType_id').getValue();
                            	var types = ['ter','ped','vop','op','stom'];
                            	if(prof == 1){
                            		types = ['ter','ped','vop','op'];
                            	}else if(prof == 2){
                            		types = ['stom'];
                            	}
                                form.findById('HVLLpuRegion_id').getStore().load({
                                    params: {
                                        Lpu_id: lpuId,
                                        LpuRegionTypeList: Ext.util.JSON.encode(types),
                                        Object: 'LpuRegion',
                                        showOpenerOnlyLpuRegions: 1
                                    },
                                    callback: function() {
                                        form.findById('HVLLpuRegion_id').clearValue();
                                    }
                                });
								
								var MedStaffFact_id = form.getForm().findField('MedStaffFact_id').getValue();
								form.getForm().findField('MedStaffFact_id').clearValue();
								if (old != lpuId)  {
									win.medstafffact_filter_params.Lpu_id = lpuId;
									setMedStaffFactGlobalStoreFilter(win.medstafffact_filter_params);
									form.getForm().findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
								}
                            }.createDelegate(this)
						},
						name: 'Lpu_id'
					}, {
						fieldLabel: lang['podrazdelenie'],
						listWidth: 400,
						hiddenName:'HVL_LpuBuilding_id',
						id: 'HVLLpuBuilding_id',
						width: 200,
						xtype: 'swlpubuildingcombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {

							}
						}
					},
                        {
                            allowBlank: true,
                            displayField: 'LpuRegion_Name',
                            fieldLabel: lang['uchastok'],
                            forceSelection: true,
                            hiddenName: 'HVL_LpuRegion_id',
                            id: 'HVLLpuRegion_id',
                            /*listeners: {
                                'blur': function(combo) {

                                    if (combo.getRawValue()=='')
                                        combo.clearValue();
                                },
                                'change': function(combo, lpuRegionId) {

                                    var lpu_region_type_id = 0;
                                    combo.getStore().each(
                                        function( record ) {
                                            if ( record.data.LpuRegion_id == lpuRegionId )
                                            {
                                                lpu_region_type_id = record.data.LpuRegionType_id;
                                                return true;
                                            }
                                        }
                                    );
                                }
                            },*/
                            minChars: 1,
                            mode: 'local',
                            queryDelay: 1,
                            setValue: function(v) {
                                var text = v;
                                if(this.valueField){
                                    var r = this.findRecord(this.valueField, v);
                                    if(r){
                                        text = r.data[this.displayField];
                                        if ( !(String(r.data['LpuRegion_Descr']).toUpperCase() == "NULL" || String(r.data['LpuRegion_Descr']) == "") )
                                        {
                                            if (r.data['LpuRegion_Descr']) {
                                                text = text + ' ( '+ r.data['LpuRegion_Descr'] + ' )';
                                            }
                                        }
                                    } else if(this.valueNotFoundText !== undefined){
                                        text = this.valueNotFoundText;
                                    }
                                }
                                this.lastSelectionText = text;
                                if(this.hiddenField){
                                    this.hiddenField.value = v;
                                }
                                Ext.form.ComboBox.superclass.setValue.call(this, text);
                                this.value = v;

                            },
                            store: new Ext.data.Store({
                                autoLoad: false,
                                reader: new Ext.data.JsonReader({
                                    id: 'LpuRegion_id'
                                }, [
                                    {name: 'LpuRegion_Name', mapping: 'LpuRegion_Name'},
                                    {name: 'LpuRegion_id', mapping: 'LpuRegion_id'},
                                    {name: 'LpuRegion_Descr', mapping: 'LpuRegion_Descr'},
                                    {name: 'LpuRegionType_id', mapping: 'LpuRegionType_id'},
                                    {name: 'LpuRegionType_Name', mapping: 'LpuRegionType_Name'},
                                    {name: 'LpuRegionType_SysNick', mapping: 'LpuRegionType_SysNick'}
                                ]),
                                url: C_LPUREGION_LIST
                            }),
                            tabIndex: 2106,
                            tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_Name} {LpuRegionType_Name} {[ (!values.LpuRegion_Descr || String(values.LpuRegion_Descr).toUpperCase() == "NULL" || String(values.LpuRegion_Descr) == "") ? "" : "( "+ values.LpuRegion_Descr +" )"]}&nbsp;</div></tpl>',
                            triggerAction: 'all',
                            typeAhead: true,
                            typeAheadDelay: 1,
                            valueField: 'LpuRegion_id',
                            width : 200,
                            xtype: 'combo'
                        },
                    {
						comboSubject: 'CallProfType',
						width: 220,
						xtype: 'swcommonsprcombo',
						fieldLabel: lang['profil_vyizova'],
						hiddenName:'HVL_CallProfType_id',
                        id: 'HVLCallProfType_id',
                        listeners:{
                        	'change':function(c,n){
                        		var form = this;
                                form.findById('HVLLpuRegion_id').getStore().removeAll();
                                var prof = n;
                            	var types = ['ter','ped','vop','op','stom'];
                            	if(prof == 1){
                            		types = ['ter','ped','vop','op'];
                            	}else if(prof == 2){
                            		types = ['stom'];
                            	}
                            	var lpuId = form.findById('hvlwSearch_Lpu').getValue() || getGlobalOptions().lpu_id
                                form.findById('HVLLpuRegion_id').getStore().load({
                                    params: {
                                        Lpu_id: lpuId,
                                        LpuRegionTypeList: Ext.util.JSON.encode(types),
                                        Object: 'LpuRegion',
                                        showOpenerOnlyLpuRegions: 1
                                    },
                                    callback: function() {
                                        form.findById('HVLLpuRegion_id').clearValue();
                                    }
                                });
                        	}.createDelegate(this)
                        }
					}
                    ]
				}, {
					layout: 'form',
					items:
					[{
						style: "padding-left: 20px",
						xtype: 'button',
						id: 'mpwpBtnSearch',
						text: lang['nayti'],
						iconCls: 'search16',
						handler: function()
						{
							this.loadHomeVisits();
						}.createDelegate(this)
					}]
				}, {
					layout: 'form',
					items:
					[{
						style: "padding-left: 20px",
						xtype: 'button',
						id: 'hvlwBtnClear',
						text: lang['sbros'],
						iconCls: 'resetsearch16',
						handler: function()
						{
							this.filtersPanel.getForm().reset();
							this.filtersPanel.findById('hvlwSearch_Lpu').setValue(getGlobalOptions()['lpu_id']);
							this.filtersPanel.findById('hvlwSearch_Lpu').setDisabled( ['regpol','regpol6'].in_array(getGlobalOptions()['CurMedServiceType_SysNick']))
							this.loadHomeVisits();
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.TopPanel = new Ext.Panel(
		{
			region: 'north',
			frame: true,
			border: false,
			autoHeight: true,
			tbar: this.DoctorToolbar,
			items:
			[
				this.filtersPanel
			]
		});

		this.HomeVisitsGrid = new sw.Promed.ViewFrame({
                        html: '<audio id="swHomeVisitListWindowNotification"><source src="/audio/web/WavLibraryNet_Sound5825.mp3" type="audio/mpeg"></audio>',
                        lastLoadGridDate: null,
                        auto_refresh: null,
			actions:
			[
				{name:'action_add', 
					handler: function() {
						getWnd('swPersonSearchWindow').show({
							onSelect: function(personData) {
								var Lpu_id = ((this.filtersPanel.getForm().findField('Lpu_id').getValue()) ? this.filtersPanel.getForm().findField('Lpu_id').getValue() : getGlobalOptions().lpu_id);
								var callCntr = (( ['regpol','regpol6'].in_array(this.type))?false:true);
								if ( personData.Person_id > 0 ) {
									getWnd('swHomeVisitAddWindow').show({
										Person_id: personData.Person_id,
										Server_id: personData.Server_id,
										action:'add',
										callCenter: callCntr,
										Lpu_id: Lpu_id,
										callback : function() {
											this.loadHomeVisits();
										}.createDelegate(this)
									});
								}
								getWnd('swPersonSearchWindow').hide();
							}.createDelegate(this)
						});
						
					}.createDelegate(this)
				},
				{name:'action_view', text: lang['naznachit_vracha'],
					iconCls:'reception-accept16',
					handler: function() {
						if ( this.HomeVisitStatus_id == 1 ) {
							getWnd('swHomeVisitConfirmWindow').show({
								HomeVisit_id: this.HomeVisit_id,
								LpuRegion_id: this.LpuRegion_id,
								Lpu_id: this.Lpu_id,
								CallProfType_id: this.CallProfType_id,
								HomeVisitCallType_id: this.HomeVisitCallType_id,
								HomeVisit_setDate: this.HomeVisit_setDate,
								callback : function() {
									this.loadHomeVisits();
								}.createDelegate(this)
							});
						}
					}.createDelegate(this)
				},
				{name:'action_edit', disabled: true,text:lang['redaktirovat'],
					handler: function() {
						if (this.HomeVisit_id && (!this.HomeVisitStatus_id || this.HomeVisitStatus_id.inlist([1, 3, 6]))) {
							var callCntr = (( ['regpol','regpol6'].in_array(this.type))?false:true);
							getWnd('swHomeVisitAddWindow').show({
								HomeVisit_id: this.HomeVisit_id,
								HomeVisitStatus_id:this.HomeVisitStatus_id,
								callCenter: callCntr,
								action:'edit',
								callback : function() {
									this.loadHomeVisits();
								}.createDelegate(this)
							});
						}
					}.createDelegate(this)
				},
				/*{name:'action_delete', text: lang['otkazat'],
					handler: function() {
						if ( this.HomeVisitStatus_id != 2 ) {
							getWnd('swHomeVisitDenyWindow').show({
								HomeVisit_id: this.HomeVisit_id,
								callback : function() {
									this.loadHomeVisits();
								}.createDelegate(this)
							});
						}
					}.createDelegate(this)
				},*/
				{
					name: 'action_delete',
					text: 'Отменить',
					handler: function() {
						this.openHomeVisitCancelWindow();
					}.createDelegate(this)
				},
				{
					name:'action_print',
					menuConfig: {
						printBook: {name: 'printBook', text: lang['pechat_knigi_zapisi_vuzovov_na_dom'], handler: function(){getWnd('swHomeVisitBookPrintParamsWindow').show({ARMType:'reg'});}}
					}
				}
			],
			grouping: true,
			groupingView: {showGroupName: false, showGroupsText: true},
			groupTextTpl:'{text} ({[values.rs.length]} {[values.rs.length == 1 ? "запись": ( values.rs.length.inlist([2,3,4]) ? "записи" : "записей")]})',
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: C_HOMEVISIT_LIST,
			//stateful: true,
			id: 'HomeVisitGrid',
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				/*if ( this.HomeVisitStatus_id == 1 ) {
					getWnd('swHomeVisitConfirmWindow').show({
						HomeVisit_id: this.HomeVisit_id,
						LpuRegion_id: this.LpuRegion_id,
						Lpu_id: this.Lpu_id,
						callback : function() {
							this.loadHomeVisits();
						}.createDelegate(this)
					});
				}*/
				if ( this.HomeVisitStatus_id&&this.HomeVisitStatus_id.inlist([1,3,6])) {
							var callCntr = (( ['regpol','regpol6'].in_array(this.type))?false:true);
							getWnd('swHomeVisitAddWindow').show({
								HomeVisit_id: this.HomeVisit_id,
								HomeVisitStatus_id:this.HomeVisitStatus_id,
								callCenter: callCntr,
								action:'edit',
								callback : function() {
									this.loadHomeVisits();
								}.createDelegate(this)
							});
						}

			}.createDelegate(this),
			onLoadData: function(sm, index, record) {
				this.EXCEL = true;
				if (this.getGrid().getStore().getCount()==1&&this.getGrid().getStore().getAt(0).get('HomeVisit_id')==null) {
					this.getGrid().getStore().removeAll();
					this.ViewActions.action_print.setDisabled(false);
					this.ViewActions.action_print.setText(lang['pechat_knigi_zapisi_vuzovov_na_dom']);
					this.ViewActions.action_print.setHandler(function () {getWnd('swHomeVisitBookPrintParamsWindow').show({ARMType:'reg'});});
				} else {
					this.ViewActions.action_print.setText('Печать');
				}
				var grid = this.getGrid(),
					store = grid.getStore();
				store.each(function(rec,idx,count) {
					if (!Ext.isEmpty(rec.get('HomeVisitStatus_id'))) {
						var hvsHref = rec.get('HomeVisitStatus_Name');
						hvsHref = '<a href="javascript://" onClick="Ext.getCmp(\''+that.id+'\').openHomeVisitStatusHistWindow()">'+rec.get('HomeVisitStatus_Name')+'</a>';	
						rec.set('HomeVisitStatus_Name', hvsHref);
						
						var HVisQ = rec.get('HomeVisit_isQuarantine');
						if (1*HVisQ == 2) {
							rec.set('HomeVisit_isQuarantine', 'Да');
						} else {
							rec.set('HomeVisit_isQuarantine', 'Нет');
						}

						rec.commit();
					}
				}.createDelegate(this));
                                
                                // #157110 Звуковое оповещение пользователя о событии в системе
                                if (getRegionNick() == 'ufa' && that.notifyArmTypeList.includes(that.type))
                                {
                                    var grid = this.getGrid();
                                    grid.getStore().each(function(rec)
                                    {
                                        var HomeVisit_id = rec.get('HomeVisit_id');
                                        if (HomeVisit_id && !that.callList.includes(HomeVisit_id))
                                        {
                                            that.playNotification();
                                            that.callList.push(HomeVisit_id);
                                        }
                                    });
                                    grid.lastLoadGridDate = new Date();
                                    if(grid.auto_refresh)
                                    {
                                            clearInterval(grid.auto_refresh);
                                    }
                                    grid.auto_refresh = setInterval(
                                        function()
                                        {
                                            var cur_date = new Date();
                                            // если прошло более 2 минут с момента последнего обновления
                                            if(grid.lastLoadGridDate.getTime() < (cur_date.getTime()-120))
                                            {
                                                grid.getStore().reload();
                                            }
                                        }.createDelegate(grid),
                                        120000
                                    );
                                }
			},
			onRowSelect: function(sm, index, record) {
				this.HomeVisit_id = record.get('HomeVisit_id');
				this.LpuRegion_id = record.get('LpuRegion_id');
				this.HomeVisitStatus_id = record.get('HomeVisitStatus_id');
				this.CallProfType_id = record.get('CallProfType_id');
				this.HomeVisit_setDate = record.get('HomeVisit_setDate');
				this.HomeVisitCallType_id = record.get('HomeVisitCallType_id');
				this.HomeVisitSource_id = record.get('HomeVisitSource_id');
				this.CmpCallCard_id = record.get('CmpCallCard_id');
				this.HomeVisitsGrid.ViewActions.action_edit.setDisabled(!this.HomeVisit_id || (this.HomeVisitStatus_id && !this.HomeVisitStatus_id.inlist([1, 3, 6])));
				this.HomeVisitsGrid.ViewActions.action_view.setDisabled( this.HomeVisitStatus_id !== 1 );
				//this.HomeVisitsGrid.ViewActions.action_delete.setDisabled( this.HomeVisitStatus_id == 2 || this.HomeVisitSource_id == 4 || this.HomeVisitSource_id != 1 || ( this.HomeVisitStatus_id == 1 && this.CmpCallCard_id > 0 && getRegionNick().inlist(['ufa'])));
				this.HomeVisitsGrid.ViewActions.action_delete.setDisabled(!that.checkButtonCancel(this.HomeVisitStatus_id, this.CmpCallCard_id, getRegionNick()));
			}.createDelegate(this),

			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'HomeVisit_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'HomeVisit_Num', type: 'string', header: lang['nomer_vyizova'], width: 100 },
				{ name: 'Lpu_Nick', type: 'string', header: lang['mo'], width: 100, hidden: true },//getGlobalOptions()['CurMedServiceType_SysNick'] == 'regpol' },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 100 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'Person_Birthday', type: 'date', header: lang['data_rojdeniya'], renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'Person_Age', type: 'int', header: lang['vozrast'], width: 50 },
				{ name: 'Address_Address', type: 'string', header: lang['mesto_vyizova'], width: 320, id: 'autoexpand' },
				{ name: 'HomeVisit_Phone', type: 'string', header: lang['telefon'], width: 80},
				{ name: 'HomeVisitWhoCall_Name', type: 'string', header: lang['kto'], width: 60},
				{ name: 'CallProfType_Name', type: 'string', header: lang['profil_vyizova'], width: 200},
				{ name: 'HomeVisit_Symptoms', type: 'string', header: lang['povod'], width: 300},
				{ name: 'LpuRegion_id', type: 'int', hidden: true },
				{ name: 'LpuRegionAttach', type: 'string', header: lang['uchastok_prikrepleniya'], width: 140 },
				{ name: 'LpuRegion_Name', type: 'string', header: lang['uchastok_vyzova'], width: 120 },
				{ name: 'LpuBuilding_Name', type: 'string', header: lang['podrazdelenie'], width: 200 },
				{ name: 'MedPersonal_FIO', type: 'string', header: lang['vrach'], width: 200, hidden:true },
				{ name: 'MedStaff_Comp', type: 'string', header: lang['vrach'], width: 400 },
				{ name: 'HomeVisitCallType_Name', type: 'string', header: lang['tip_vyizova'], width: 200 },
				{ name: 'HomeVisit_setDate', type: 'date', header: lang['data_vyizova'], width: 100 },
				{ name: 'HomeVisit_setTime', type: 'string', header: lang['vremya_vyizova'], width: 100},
				{ name: 'HomeVisitStatus_Name', type: 'string', header: lang['status_vyizova'], width: 200 },
				{ name: 'HomeVisitStatus_Nameg', type: 'string', header: lang['status'], hidden: true, group: true, sort: true, direction: 'ASC' },
				{ name: 'HomeVisitStatus_id', type: 'int', hidden: true },
				{ name: 'HomeVisitCallType_id', type: 'int', hidden: true },
				{ name: 'CallProfType_id', type: 'int', hidden: true },
				{ name: 'HomeVisitSource_id', type: 'int', hidden: true },
				{ name: 'CmpCallCard_id', type: 'int', hidden: true },
				{ name: 'HomeVisit_Comment', type: 'string', header: langs('Дополнительно'), width: 200},
				{ name: 'HomeVisit_LpuComment', type: 'string', header: langs('Комментарий ЛПУ'), width: 200},
				{ name: 'CmpCallCard_Ngod', type: 'string', header: langs('Номер карты СМП'), width: 200},
				{ name: 'HomeVisitStatusHist_setDT', type: 'date', header: langs('Дата передачи вызова'), width: 200},
				{ name: 'HomeVisit_isQuarantine', type: 'string', header: langs('Карантин'), width: 100}
			],
			title: null,
			paging: true,
			pageSize: 100,
			root: 'data',
			totalProperty: 'totalCount'
		});
		this.HomeVisitsGrid.getGrid().on('keypress', this.onkeypress);
		this.HomeVisitsGrid.getGrid().keys = {
			key: 188,
			ctrl: true,
			handler: function() {
				curWnd.doReset();
				curWnd.FilterPanel.getForm().findField('Person_Surname').focus(1);
			}
		};

	    Ext.apply(this, {
			autoScroll: true,
			buttons:
			[
			{
				text: '-'
			},
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_HVL);
				}.createDelegate(this),
				tabIndex: TABINDEX_MPSCHED + 98
			},
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}],
			layout: 'border',
			items: [
				this.TopPanel,
				{
					layout: 'border',
					region: 'center',
					id: 'hvlwMainPanel',
					items:
					[
						this.HomeVisitsGrid
					]
				}
			],
			keys: [{
				key: [
					Ext.EventObject.F5,
					Ext.EventObject.F9
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

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					switch (e.getKey())
					{
						case Ext.EventObject.F5:
							this.loadHomeVisits();
						break;
					}
				},
				scope: this,
				stopEvent: false
			}]
	    });
	    sw.Promed.swHomeVisitListWindow.superclass.initComponent.apply(this, arguments);

    },

	show: function()
	{
		sw.Promed.swHomeVisitListWindow.superclass.show.apply(this, arguments);
		var win = this;
		this.type='';
		if(arguments[0]){
			if(arguments[0].type){
				this.type=arguments[0].type;
			}
		}
		Ext.getCmp('hvlwSearch_Lpu').setValue(getGlobalOptions()['lpu_id']);
		Ext.getCmp('hvlwSearch_Lpu').setDisabled( ['regpol','regpol6'].in_array(win.type) );
		
        var form = this.filtersPanel;
        var MedStaffFact = this.filtersPanel.getForm().findField('MedStaffFact_id');
		this.medstafffact_filter_params = { isDoctor:true, Lpu_id:getGlobalOptions()['lpu_id'], withLpuRegionOnly:true };
		if(getRegionNick() == 'kareliya'){
			this.medstafffact_filter_params.isDoctor = false;
			this.medstafffact_filter_params.withLpuRegionOnly = false;
		}
		if(['regpol','regpol6'].in_array(win.type)){ this.medstafffact_filter_params.LpuRegionType_HomeVisit = 'all';}
		if(swMedStaffFactGlobalStore.data.length == 0){
			MedStaffFact.getStore().load({
				callback:function(){
					var direct_store = setMedStaffFactGlobalStoreFilter(win.medstafffact_filter_params,MedStaffFact.getStore());
					MedStaffFact.getStore().loadData(getStoreRecords(direct_store));
				}
			});
		} else {
			setMedStaffFactGlobalStoreFilter(win.medstafffact_filter_params);
			MedStaffFact.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		}

        form.findById('HVLLpuRegion_id').getStore().removeAll();
        var prof = form.findById('HVLCallProfType_id').getValue();
    	var types = ['ter','ped','vop','op','stom'];
    	if(prof == 1){
    		types = ['ter','ped','vop','op'];
    	}else if(prof == 2){
    		types = ['stom'];
    	}
        form.findById('HVLLpuRegion_id').getStore().load({
            params: {
                Lpu_id: getGlobalOptions()['lpu_id'],
				LpuRegionTypeList: Ext.util.JSON.encode(types),
                Object: 'LpuRegion',
                showOpenerOnlyLpuRegions: 1
            },
            callback: function() {
                form.findById('HVLLpuRegion_id').clearValue();
            }
        });

		form.findById('HVLLpuBuilding_id').getStore().removeAll();
		form.findById('HVLLpuBuilding_id').clearValue();
		if (Ext.isEmpty(Ext.getCmp('hvlwSearch_Lpu').getValue())) {
			form.findById('HVLLpuBuilding_id').disable();
		} else {
			form.findById('HVLLpuBuilding_id').enable();
			form.findById('HVLLpuBuilding_id').getStore().baseParams.Lpu_id = Ext.getCmp('hvlwSearch_Lpu').getValue();
			form.findById('HVLLpuBuilding_id').getStore().load();
		}
		
		form.getForm().findField('HomeVisitStatus_id').getStore().loadData([{
			'HomeVisitStatus_id': -1,
			'HomeVisitStatus_Code': -1,
			'HomeVisitStatus_Name': lang['aktiv_iz_smp']
		}], true);
		form.getForm().findField('HomeVisitStatus_id').getStore().loadData([{
			'HomeVisitStatus_id': -2,
			'HomeVisitStatus_Code': -2,
			'HomeVisitStatus_Name': langs('Требует подтверждения')
		}], true);
		form.getForm().findField('HomeVisitStatus_id').getStore().sort('HomeVisitStatus_Code', 'ASC');
		
		this.getCurrentDateTime();
		//this.loadHomeVisits();
	}
});