/**
* swEvnLabSampleViewWindow - Рабочий журнал лаборанта для работы с пробами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Марков Андрей
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      март.2012
*/
sw.Promed.swEvnLabSampleViewWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['rabochiy_jurnal'],
	stateful: true,
    //iconCls: '',
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swEvnLabSampleViewWindow',
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	listeners: {
		'hide': function(w)
		{
			w.doReset();
		}
	},
	printBarcodes: function() {
		var s = "";
		this.Grid.getGrid().getSelectionModel().getSelections().forEach(function (el) {
			if (!Ext.isEmpty(el.data.EvnLabSample_id)) {
				if (!Ext.isEmpty(s)) {
					s = s + ",";
				}
				s = s + el.data.EvnLabSample_id;
			}
		});
		
		if (!Ext.isEmpty(s)) {
			var Report_Params = '&s=' + s;
			if ( Ext.globalOptions.lis ) {
            var ZebraDateOfBirth = (Ext.globalOptions.lis.ZebraDateOfBirth) ? 1 : 0;
            var ZebraUsluga_Name = (Ext.globalOptions.lis.ZebraUsluga_Name) ? 1: 0;
				var ZebraDirect_Name = (Ext.globalOptions.lis.ZebraDirect_Name) ? 1 : 0;
				var ZebraFIO = (Ext.globalOptions.lis.ZebraFIO) ? 1 : 0;
				Report_Params = Report_Params + '&paramPrintType=1';
				Report_Params = Report_Params + '&marginTop=' + Ext.globalOptions.lis.labsample_barcode_margin_top;
				Report_Params = Report_Params + '&marginBottom=' + Ext.globalOptions.lis.labsample_barcode_margin_bottom;
				Report_Params = Report_Params + '&marginLeft=' + Ext.globalOptions.lis.labsample_barcode_margin_left;
				Report_Params = Report_Params + '&marginRight=' + Ext.globalOptions.lis.labsample_barcode_margin_right;
				Report_Params = Report_Params + '&width=' + Ext.globalOptions.lis.labsample_barcode_width;
				Report_Params = Report_Params + '&height=' + Ext.globalOptions.lis.labsample_barcode_height;
				Report_Params = Report_Params + '&barcodeFormat=' + Ext.globalOptions.lis.barcode_format;
                Report_Params = Report_Params + '&ZebraDateOfBirth=' + ZebraDateOfBirth;
                Report_Params = Report_Params + '&ZebraUsluga_Name=' + ZebraUsluga_Name;
                Report_Params = Report_Params + '&paramFrom=' + ZebraDirect_Name;
            	Report_Params = Report_Params + '&paramFIO=' + ZebraFIO;
			}

			Report_Params = Report_Params + '&paramLpu=' + getGlobalOptions().lpu_id

			printBirt({
				'Report_FileName': (Ext.globalOptions.lis.use_postgresql_lis ? 'barcodesprint_resize_pg' : 'barcodesprint_resize') + '.rptdesign',
				'Report_Params': Report_Params,
				'Report_Format': 'pdf'
			});
		}
		
		return false;
	},
	show: function() {
		var that = this;
		sw.Promed.swEvnLabSampleViewWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] || !arguments[0].MedService_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], function() { that.hide(); } );
			return false;
		}
		
		this.MedService_id = arguments[0].MedService_id;
		this.getCurrentDateTime();
		// сбросить фильтр 
		this.setTitleFieldset();
		var g = this.findById('EvnLabSampleGridPanel');
		g.addActions({name:'action_lis_approve', text: lang['odobrit_rezultatyi'], handler: function (){
			var g = that.Grid;
			
			var selections = g.getGrid().getSelectionModel().getSelections();
			var ArrayId = [];

			for	(var key in selections) {
				if (selections[key].data) {
					ArrayId.push(selections[key].data['EvnLabSample_id']);
				}
			}
			var params = {}
			params.EvnLabSamples = Ext.util.JSON.encode(ArrayId);
			
			if (g.getGrid().getSelectionModel().getCount() > 0) {
				that.getLoadMask(lang['odobrenie_rezultatov']).show();
				// получаем выделенную запись
				Ext.Ajax.request({
					url: '/?c=EvnLabSample&m=approveEvnLabSampleResults',
					params: params,
					callback: function(opt, success, response) {
						that.getLoadMask().hide();
						if (success && response.responseText != '') {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.success) {
								g.getGrid().getStore().reload();
							} else {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function() {
									},
									icon: Ext.Msg.WARNING,
									msg: result.Error_Msg,
									title: lang['odobrenie_rezultatov']
								});
							}
						}
					}
				});
			} else {
				sw.swMsg.alert(lang['proba_ne_vyibrana'], lang['vyiberite_probu_rezultatyi_kotoroy_trebuetsya_odobrit']);
			}
		}});
		g.addActions({name:'action_lis_sample', text: lang['proverit_rezultat'], handler: function (){
			var g = that.Grid;
			
			var selections = g.getGrid().getSelectionModel().getSelections();
			var ArrayId = [];

			for	(var key in selections) {
				if (selections[key].data) {
					ArrayId.push(selections[key].data['EvnLabSample_id']);
				}
			}
			var params = {}
			params.EvnLabSamples = Ext.util.JSON.encode(ArrayId);
			
			if (g.getGrid().getSelectionModel().getCount() > 0) {
				that.getLoadMask(lang['poluchenie_rezultatov_s_analizatora']).show();
				// получаем выделенную запись
				Ext.Ajax.request({
					url: '/?c='+getLabController()+'&m=getResultSamples',
					params: params,
					callback: function(opt, success, response) {
						that.getLoadMask().hide();
						if (success && response.responseText != '') {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.success) {
								g.getGrid().getStore().reload();
								showSysMsg(lang['rezultatyi_analizov_poluchenyi_s_analizatora_i_sohranenyi_v_probe'],lang['poluchenie_rezultatov']);
							} else {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function() {
										g.getGrid().getStore().reload();
									},
									icon: Ext.Msg.WARNING,
									msg: result.Error_Msg,
									title: lang['poluchenie_rezultatov']
								});
							}
						}
					}
				});
			} else {
				sw.swMsg.alert(lang['proba_ne_vyibrana'], lang['vyiberite_probu_rezultatyi_kotoroy_trebuetsya_poluchit_s_analizatora']);
			}
		}});
		g.addActions({name:'action_lis_create', text: lang['sozdat_zayavku_dlya_analizatora'], handler: function (){
			var g = that.Grid;
			/*var record = g.getGrid().getSelectionModel().getSelected();
			if(!record)
				return false;
			*/
			// Проверяем есть ли выбранные записи
			var selections = g.getGrid().getSelectionModel().getSelections();
			var ArrayId = [];

			for	(var key in selections) {
				if (selections[key].data) {
					if (Ext.isEmpty(selections[key].data['EvnLabSample_setDT'])) {
						sw.swMsg.alert(lang['oshibka'], lang['vyibrannaya_proba_ne_soderjit_dannyih_o_sostave_probyi_otkroyte_probu_i_zapolnite_informatsiyu_o_vzyatii_probyi']);
						return false;
					}
					if (Ext.isEmpty(selections[key].data['Analyzer_id'])) {
						sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_ukazat_analizator_dlya_vseh_vyibrannyih_prob']);
						return false;
					}
					ArrayId.push(selections[key].data['EvnLabSample_id']);
				}
			}
			
			var params = {}
			params.EvnLabSamples = Ext.util.JSON.encode(ArrayId);
			
			/*
			var answerMsg = '';
			if (g.getGrid().getSelectionModel().getCount() > 1) {
				answerMsg = lang['otpravit_vyibrannyie_zayavki_dlya_vyipolneniya'];
			}
			*/
			if (g.getGrid().getSelectionModel().getCount() > 0) {
				that.getLoadMask(lang['sozdanie']+((ArrayId.length>1)?lang['zayavok']:lang['zayavki'])+lang['dlya_analizatora']).show();
				// получаем выделенную запись
				Ext.Ajax.request({
					url: '/?c='+getLabController()+'&m=createRequestSelections',
					params: params,
					callback: function(opt, success, response) {
						that.getLoadMask(LOAD_WAIT).hide();
						if (success && response.responseText != '') {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.success) {
								g.getGrid().getStore().reload();
								showSysMsg(lang['zayavka_dlya_analizatora_uspeshno_sozdana'], lang['zayavka_dlya_analizatora']);
								//sw.swMsg.alert('Заявка для анализатора', 'Заявка для анализатора успешно создана.');
							} else {
								// todo: Надо продумать ситуацию
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function() {
									},
									icon: Ext.Msg.WARNING,
									msg: result.Error_Msg,
									title: lang['zayavka_dlya_analizatora']
								});
							}
						} 
					}
				});
			} else {
				sw.swMsg.alert(lang['proba_ne_vyibrana'], lang['dlya_sozdaniya_zayavki_neobhodimo_vyibrat_hotya_byi_odnu_probu']);
			}
		}});
		
		g.addActions({name:'action_print_barcodes', text: lang['pechat_shtrih-kodov'], handler: function () {
			that.printBarcodes();
		}});
	},
	setFilter: function(newValue) {
		var form = this.FilterPanel.getForm();
		var lpusection_combo = form.findField('LpuSection_id');
		setLpuSectionGlobalStoreFilter(/*{
			onDate: Ext.util.Format.date(newValue, 'd.m.Y')
		}*/);
		lpusection_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		var medstafffact_combo = form.findField('MedStaffFact_id');
		setMedStaffFactGlobalStoreFilter({
			LpuSection_id: getGlobalOptions().CurLpuSection_id/*,
			onDate: Ext.util.Format.date(newValue, 'd.m.Y')
			*/
		});
		medstafffact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
		if (getGlobalOptions().CurLpuSection_id>0) {
			//lpusection_combo.setValue(getGlobalOptions().CurLpuSection_id);
		}
		
		if (getGlobalOptions().CurMedStaffFact_id>0) {
			index = medstafffact_combo.getStore().findBy(function(rec) {
				if ( rec.get('MedPersonal_id') == getGlobalOptions().CurMedPersonal_id && rec.get('LpuSection_id') == lpusection_combo.getValue() ) {
					return true;
				}
				else {
					return false;
				}
			});

			if ( index >= 0 ) {
				medstafffact_combo.setValue(medstafffact_combo.getStore().getAt(index).get('MedStaffFact_id'));
			}
		}
		lpusection_combo.setDisabled(true);
		medstafffact_combo.setDisabled(true);
	},
	getCurrentDateTime: function() {
        var that = this;
        if (!getGlobalOptions().date) {
			frm.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				url: C_LOAD_CURTIME,
				callback: function(opt, success, response) {
					if (success && response.responseText != '') {
						var result  = Ext.util.JSON.decode(response.responseText);
                        that.curDate = result.begDate;
						// Проставляем время и режим
                        that.mode = 'day';
                        that.currentDay();
                        that.doSearch('day');
                        that.getLoadMask().hide();
					}
				}
			});
		} else {
			this.curDate = getGlobalOptions().date;
			// Проставляем время и режим
			this.mode = 'day';
			this.currentDay();
			this.doSearch('day');
		}
	},
	setTitleFieldset: function() {
		var fieldset = this.FilterPanel.find('xtype', 'fieldset')[0];
		var flag = false;
		fieldset.findBy(function(field){
			if(typeof field.xtype != 'undefined' && field.xtype.inlist(['combo','daterangefield','swnoticetypecombo']))
			{
				if(field.getRawValue() != '')
					flag = true;
			}
		});
		fieldset.setTitle((flag)?lang['filtr_ustanovlen']:lang['filtr']);
	},
	getPeriodToggle: function (mode) {
		switch(mode)
		{
		case 'day':
			return this.WindowToolbar.items.items[6];
			break;
		case 'week':
			return this.WindowToolbar.items.items[7];
			break;
		case 'month':
			return this.WindowToolbar.items.items[8];
			break;
		case 'range':
			return this.WindowToolbar.items.items[9];
			break;
		default:
			return null;
			break;
		}
	},
	doSearch: function(mode) {
		var params = this.FilterPanel.getForm().getValues();
		var btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode != 'range') {
				if (this.mode == mode) {
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
						return false;
				} else {
					this.mode = mode;
				}
			}
			else {
				btn.toggle(true);
				this.mode = mode;
			}
		}
		params.MedService_id = this.MedService_id;
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		this.Grid.removeAll();
		this.Grid.loadData({globalFilters: params});
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
		//this.FilterPanel.getForm().findField('Message_isRead').setValue(0);
		//this.setTitleFieldset();
		//this.Grid.getStore().baseParams = {};
	},
	stepDay: function(day) {
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	prevDay: function () {
		this.stepDay(-1);
	},
	setActionDisabled: function(action, flag) {
		if (this.gridActions[action])
		{
			this.gridActions[action].initialConfig.initialDisabled = flag;
			this.gridActions[action].setDisabled(flag);
		}
	},
	scheduleCollapseDates: function() {
		this.getGrid().getView().collapseAllGroups();
	},
	scheduleExpandDates: function() {
		this.getGrid().getView().expandAllGroups();
	},
	nextDay: function () {
		this.stepDay(1);
	},
	currentDay: function () {
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentWeek: function () {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
    frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentMonth: function () {
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
    frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	createFormActions: function() {
		
		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: lang['period'],
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.dateMenu.addListener('keydown',function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch('period');
			}
		}.createDelegate(this));
		this.dateMenu.addListener('select',function () {
			// Читаем расписание за период
			this.doSearch('period');
		}.createDelegate(this));
		
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
				this.doSearch('range');
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
				this.doSearch('range');
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
				this.doSearch('day');
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
				this.doSearch('week');
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
				this.doSearch('month');
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
				this.doSearch('range');
			}.createDelegate(this)
		});
	},
	openEvnLabSampleEditWindow: function(action) {
		var cur_win = this;
		var g = cur_win.Grid.getGrid();
		var selection = g.getSelectionModel().getSelected();
		// если уж загрузка до показа формы, то надо хотя бы показать, что что то делается.
		if (selection) {
			cur_win.getLoadMask(lang['zagruzka_dannyih_probyi']).show();
			Ext.Ajax.request({
				url: '/?c=EvnLabSample&m=load',
				params:{
					EvnLabSample_id: selection.data.EvnLabSample_id
				},
				callback: function(opt, success, response) {
					cur_win.getLoadMask().hide();
					if (success && response.responseText != '') {
						var result = Ext.util.JSON.decode(response.responseText);
						var params = new Object();
						params.action = action;
						params.remoteCallback = function() {
							cur_win.getLoadMask().hide();
							cur_win.doSearch('day');
						};
						params.formParams = new Object();
						params.formParams = result[0];
						params.formParams.EvnLabSample_ShortNum = params.formParams.EvnLabSample_Num.substr(-4);
						params.onHide = function() {
							g.getView().focusRow(g.getStore().indexOf(selection));
						};
						
						params.Person_id = params.formParams.Person_id;
						params.MedService_id = cur_win.MedService_id;
						params.EvnDirection_id = selection.data.EvnDirection_id;
						params.UslugaComplexTarget_id = selection.data.UslugaComplexTarget_id;

						getWnd('swLabSampleEditWindow').show(params);
					}
				}
			});
		}
	},
	focusOnGrid: function() {
		var grid = this.Grid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (record) {
			grid.getView().focusRow(grid.getStore().indexOf(record));
		}
	},
	initComponent: function() {
		var cur_win = this;
		
		cur_win.gridKeyboardInput = '';
		cur_win.gridKeyboardInputSequence = 1;
		cur_win.resetGridKeyboardInput = function (sequence) {
			var result = false;
			if (sequence == cur_win.gridKeyboardInputSequence) {
				if (cur_win.gridKeyboardInput.length >= 4) {
					cur_win.Grid.onKeyboardInputFinished(cur_win.gridKeyboardInput);
					result = true;
				}
				cur_win.gridKeyboardInput = '';
			}
			return result;
		};
		cur_win.on('activate', function() {
			cur_win.focusOnGrid();
		});
		
		this.createFormActions();
		this.WindowToolbar = new Ext.Toolbar({
			items: [
				this.formActions.prev, 
				{
					xtype : "tbseparator"
				},
				this.dateMenu,
				//this.dateText,
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
		this.FilterPanel = new Ext.form.FormPanel({
			floatable: false,
			autoHeight: true,
			animCollapse: false,
			labelAlign: 'right',
			//plugins: [ Ext.ux.PanelCollapsedTitle ],
			defaults: {
				bodyStyle: 'background: #DFE8F6;'
			},
			region: 'north',
			frame: true,
			buttonAlign: 'left',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					cur_win.doSearch();
				},
				stopEvent: true
			}],
			items: [{
					xtype: 'fieldset',
					style:'padding: 0px 3px 3px 6px;',
					autoHeight: true,
					listeners: {
						expand: function() {
							this.ownerCt.doLayout();
							cur_win.syncSize();
						},
						collapse: function() {
							cur_win.syncSize();
						}
					},
					collapsible: true,
					collapsed: true,
					title: lang['filtr'],
					bodyStyle: 'background: #DFE8F6;',
					items: [{
						layout: 'column',
						items: [{
							layout: 'form',
							bodyStyle: 'background: #DFE8F6;',
							labelWidth: 50,
							border: false,
							items: [/*{
								hiddenName: 'LpuSection_id',
								id: 'elsvSearch_LpuSection_id',
								emptyText: lang['otdelenie'],
								//hideLabel: true,
								lastQuery: '',
								linkedElements: [
									'elsvSearch_MedStaffFact_id'
								],
								listWidth: 350,
								width: 350,
								xtype: 'swlpusectionglobalcombo',
								listeners: {
									'keydown': function (inp, e) {
										var form = Ext.getCmp('swMPWorkPlaceStacWindow');
										if (e.getKey() == Ext.EventObject.ENTER) {
											e.stopEvent();
											
										}
									}.createDelegate(this)
								}
								}, {
									id: 'elsvSearch_MedStaffFact_id',
									parentElementId: 'elsvSearch_LpuSection_id',
									emptyText: lang['vrach'],
									//hideLabel: true,
									hiddenName: 'MedStaffFact_id',
									lastQuery: '',
									listWidth: 350,
									//tabIndex: ,
									width: 350,
									xtype: 'swmedstafffactglobalcombo',
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<table style="border: 0;">',
										'<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
										'<td><span style="font-weight: bold;">{MedPersonal_Fio}</span></td>',
										'</tr></table>',
										'</div></tpl>'
									),
									listeners: {
										'keydown': function (inp, e) {
											var form = Ext.getCmp('swMPWorkPlaceStacWindow');
											if (e.getKey() == Ext.EventObject.ENTER)
											{
												e.stopEvent();
											}
										}
									}
								},*/
                                {
									fieldLabel: 'Cito',
									comboSubject: 'YesNo',
									name: 'EvnDirection_IsCito',
                                    hiddenName: 'EvnDirection_IsCito',
									xtype: 'swcommonsprcombo'
								}
                            ]
							},
							{
								layout: 'form',
								bodyStyle: 'background: #DFE8F6;',
								labelWidth: 10,
								border: false,
								items: [
									/* Тут должен быть фильтр Анализатор, только пока не понятно что это*/
									/*{
										boxLabel: 'Cito',
										checked: false,
										fieldLabel: '',
										labelSeparator: '',
										name: 'EvnDirection_IsCito',
										xtype: 'checkbox'
								}*/]
							}
						]
						},
						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									items: [
										{
											xtype: 'button',
											handler: function()
											{
												this.doSearch();
											}.createDelegate(this),
											iconCls: 'search16',
											text: BTN_FRMSEARCH
										}
									]
								},
								{
									layout: 'form',
									style: 'margin-left: 10px;',
									items: [
										{
											xtype: 'button',
											handler: function()
											{
												cur_win.doReset();
											},
											iconCls: 'resetsearch16',
											text: BTN_FRMRESET
										}
									]
								}								
							]
						}
					]
				}
			]
		});
		// Меню с кнопками слева // пока видимо не надо и может даже и не понадобится 
		this.leftMenu = new Ext.Panel({
			region: 'west',
			border: false,
			layout:'form',
			layoutConfig: {
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: [] // здесь надо определять и создавать кнопки // я бы вынес это в отдельную функцию (пример: items: this.getLeftButtons)
		});
		// Грид с бубенчиками (основной рабочий журнал с группировкой)
		this.Grid = new sw.Promed.ViewFrame({
			id: 'EvnLabSampleGridPanel',
			selectionModel: 'multiselect',
			region: 'center',
			stateful: true,
    		layout: 'fit',
			autoLoadData: false,
			gridplugins: [Ext.ux.grid.plugins.GroupCheckboxSelection],
			object: 'EvnLabSample',
			dataUrl: '/?c=EvnLabSample&m=loadWorkList',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			useEmptyRecord: false,
			onKeyDown1: function (){
				var e = arguments[0][0];
				if ((e.getCharCode() == 9 )||e.getCharCode() == 13) {
					return;
				}
				cur_win.gridKeyboardInputSequence++;
				var s = cur_win.gridKeyboardInputSequence;
				var pressed = String.fromCharCode(e.getCharCode());
				var alowed_chars = ['0','1','2','3','4','5','6','7','8','9'];
				if ((pressed != '') && (alowed_chars.indexOf(pressed) >= 0)) {
					cur_win.gridKeyboardInput = cur_win.gridKeyboardInput + String.fromCharCode(e.getCharCode());
					setTimeout(function () {
						cur_win.resetGridKeyboardInput(s);
					}, 500);
				}
			},
			onEnter: function () {
				cur_win.resetGridKeyboardInput(cur_win.gridKeyboardInputSequence);
			},
			onKeyboardInputFinished: function (input){
				if (input.length>0) {

                    var found = cur_win.Grid.getGrid().getStore().findBy(function (el){
                        return (el.get('EvnLabSample_Num').indexOf(input) != -1);
                    });
					
					var selections = cur_win.Grid.getGrid().getSelectionModel().getSelections();
                    if (found >= 0) {
						var record = cur_win.Grid.getGrid().getStore().getAt(found);
						selections.push(record);
                        this.getGrid().getSelectionModel().selectRecords(selections, true);
                    }
				}
			},
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "заявки" : "заявок"]})',
			saveAtOnce: false,
			saveAllParams: false, 
			onAfterEdit: function(o) {
				if (o.field && o.field == 'Analyzer_Name' && o.record) {
					o.record.set('Analyzer_Name', o.rawvalue);
					o.record.set('Analyzer_id', o.value);
					o.record.commit();
					
					// обновить на стороне сервера
					Ext.Ajax.request({
						url: '/?c=EvnLabSample&m=saveLabSampleAnalyzer',
						params: {
							Analyzer_id: o.value,
							EvnLabSample_id: o.record.get('EvnLabSample_id')
						}
					});
				}
			},
			groupingView: {showGroupName: false, showGroupsText: true},
			stringfields:[
				// Поля для отображение в гриде
				// Получить отмеченные галочкой записи: swEvnLabSampleViewWindow.Grid.getGrid().getStore().data.filterBy(function (el) {return el.data.access});
                {name: 'EvnLabSample_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnLabSample_Status', hidden: true, group: true, sort: true, direction: 'ASC', header: lang['status'] },
				{name: 'EvnDirection_IsCito', type:'string', header: 'Cito', width: 50},
				{name: 'EvnLabSample_Num', type:'string', header: lang['nomer_probyi_shtrih-kod'], hidden: true},
				{name: 'EvnLabSample_ShortNum', type:'string', header: lang['nomer_probyi_shtrih-kod'], width: 150},
				{name: 'EvnDirection_Num', header: lang['nomer_napravleniya'], width: 120},
				{name: 'Person_FIO', header: lang['fio_patsienta'], width: 240},
				{name: 'RefMaterial_id', type:'int', hidden: true},
				{name: 'RefMaterial_Name', header: lang['biomaterial'], width: 160},
				{name: 'EvnLabSample_UslugaList', id: 'autoexpand', header: lang['spisok_issledovaniy_probyi']},
				{name: 'Analyzer_id', hidden: true},
				{name: 'EvnDirection_id', hidden: true},
				{name: 'UslugaComplexTarget_id', hidden: true},
				{name: 'AnalyzerWorksheetEvnLabSample_id', hidden: true},
				{name: 'Analyzer_Name', editor: new sw.Promed.SwAnalyzerCombo({
					listWidth: 300,
					listeners: {
						'show': function() {
							var combo = this;
							combo.record = cur_win.Grid.getGrid().getSelectionModel().getSelected();
							this.getStore().removeAll();
							this.getStore().load({
								params: {
									EvnLabSample_id: combo.record.get('EvnLabSample_id'),
									MedService_id: cur_win.MedService_id,
									Analyzer_IsNotActive: 1
								}
							});
						},
						'select': function(combo, record) {
							combo.setValue(record.get('Analyzer_id'));
							combo.fireEvent('blur', combo);
						},
						'blur': function(combo) {
							var grid = cur_win.Grid.getGrid();
							grid.stopEditing();
						}
					}
				}), header: lang['analizator'], width: 120},
				{name: 'EvnLabSample_setDT', type:'datetime', header: lang['data_i_vremya_vzyatiya_probyi'], width: 120},
				{name: 'EvnLabSample_StudyDT', type:'datetime', header: lang['data_i_vremya_issledovaniya'], width: 120}, 
				{name: 'EvnLabSample_IsOutNorm', type: 'int', hidden: true },
				{name: 'lis_id', hidden: true, header: 'LisId' },
				{name: 'Person_ShortFio', type: 'string', hidden: true},
				{name: 'LpuSection_Code', type: 'string', hidden: true}
			],
			actions:[
				{name:'action_add', hidden: true}, // 
				{name:'action_edit', handler: function () { cur_win.openEvnLabSampleEditWindow('edit'); } },
				{name:'action_view', handler: function () { cur_win.openEvnLabSampleEditWindow('view'); } },
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_save', hidden: true, disabled: true},
				{name:'action_print'}
			],
			onLoadData: function() {
				sm = this.getGrid().getSelectionModel();
				this.onRowSelectionChange(sm);
			},
			onRowDeSelect: function(sm,rowIdx,record) {
				this.onRowSelectionChange(sm);
            },
			onRowSelect: function(sm,rowIdx,record) {
				this.onRowSelectionChange(sm);
            },
			onRowSelectionChange: function(sm, index, record) {
				this.getAction('action_print_barcodes').setDisabled(sm.getCount() < 1);
			}
		});
		
		this.Grid.getGrid().view.getRowClass = function (row, index)
		{
			var cls = '';
			
			if (row.get('EvnLabSample_IsOutNorm') == 2)
				cls = cls+'x-grid-rowred ';
				
			return cls;
		};
		
		this.Grid.getGrid().getColumnModel().isCellEditable = function(colIndex, rowIndex) {
			var grid = cur_win.Grid.getGrid();
			var store = grid.getStore();
			var record = store.getAt(rowIndex);
			
			if (!Ext.isEmpty(record.get('AnalyzerWorksheetEvnLabSample_id'))) {
				return false;
			}
			
			return true;
		};
		
		this.CenterPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [ /*this.leftMenu,*/ this.Grid]
		});
		Ext.apply(this, {
			layout: 'border',
			tbar: this.WindowToolbar,
			items: [
				this.FilterPanel,
				this.CenterPanel
			]
		});
		sw.Promed.swEvnLabSampleViewWindow.superclass.initComponent.apply(this, arguments);
	}
});