/**
* swSmpEmergencyTeamSetDutyTimeWindow - форма выбора ЛПУ, в которой есть служба определенного типа
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Storozhev
* @version      27.06.2012
*/

sw.Promed.swSmpEmergencyTeamSetDutyTimeWindow = Ext.extend(sw.Promed.BaseForm,{
	
	title: lang['smena_brigadyi_smp'],
	
	width: 700,
	
	height: 350,
	
	modal: true,
	
	resizable: true,
	
	onCancel: Ext.emptyFn,
	
	callback: Ext.emptyFn,
	
	listeners: {},
	
	gridPanelAutoLoad: true, // Автозагрузка dutyTimeGridPanel
	
	id: 'swSmpEmergencyTeamSetDutyTimeEditForm',
	
	EmergencyTeam_id: null,
	
	buttons: [{
		text      : BTN_FRMCLOSE,
		tabIndex  : -1,
		tooltip   : lang['zakryit'],
		iconCls   : 'cancel16',
		handler   : function(){
			this.ownerCt.hide();
		}
	}],
	
	dateStartIdx: 0,
	dateFinishIdx: 0,
	generateDateId: function( isStart, prefix ){
		if ( isStart ) {
			this.dateStartIdx++;
			return prefix + '' + this.dateStartIdx;
		} else {
			this.dateFinishIdx++;
			return prefix + '' + this.dateFinishIdx;
		}
	},
	
	initActions: function(){
		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: lang['period'],
			plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ]
		});
		
		this.dateMenu.addListener('keydown',function(inp,e){
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch('period');
			}
		}.createDelegate(this));
		
		this.dateMenu.addListener('select',function(){
			this.doSearch('period');
		}.createDelegate(this));
		
		this.formActions = new Array();

		this.formActions.selectDate = new Ext.Action({
			text: ''
		});
		
		// Один период назад
		this.formActions.prev = new Ext.Action({
			text: lang['predyiduschiy'],
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function(){
				this.prevDay();
				this.doSearch('range');
			}.createDelegate(this)
		});
		
		// Один период вперед
		this.formActions.next = new Ext.Action({
			text: lang['sleduyuschiy'],
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function(){
				this.nextDay();
				this.doSearch('range');
			}.createDelegate(this)
		});
		
		// Период за день
		this.formActions.day = new Ext.Action({
			text: lang['den'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-day16',
			handler: function(){
				this.currentDay();
				this.doSearch('day');
			}.createDelegate(this)
		});
		
		// Период неделя
		this.formActions.week = new Ext.Action({
			text: lang['nedelya'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-week16',
			pressed: true,
			handler: function(){
				this.currentWeek();
				this.doSearch('week');
			}.createDelegate(this)
		});
		
		// Период месяц
		this.formActions.month = new Ext.Action({
			text: lang['mesyats'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-month16',
			handler: function(){
				this.currentMonth();
				this.doSearch('month');
			}.createDelegate(this)
		});
		
		// Период
		this.formActions.range = new Ext.Action({
			text: lang['period'],
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function(){
				this.doSearch('range');
			}.createDelegate(this)
		});
	},
	stepDay: function(day){
		var frm = this;
		var date1 = (this.dateMenu.getValue1() || Date.parseDate(this.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (this.dateMenu.getValue2() || Date.parseDate(this.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	prevDay: function(){
		this.stepDay(-1);
	},
	nextDay: function(){
		this.stepDay(1);
	},
	currentDay: function(){
		var date1 = Date.parseDate(this.curDate, 'd.m.Y');
		var date2 = Date.parseDate(this.curDate, 'd.m.Y');
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentWeek: function(){
		var date1 = (Date.parseDate(this.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentMonth: function(){
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	getPeriodToggle: function(mode){
		switch(mode){
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
		}
		return null;
	},
	doSearch: function(mode){
		var btn = this.getPeriodToggle(mode);
		if ( btn ) {
			if ( mode == 'range') {
				btn.toggle(true);
				this.mode = mode;
			} else if ( this.mode == mode ) {
				btn.toggle(true);
				// чтобы при повторном открытии тоже происходила загрузка списка записанных на эту неделю
				if ( mode != 'week' ) {
					return false;
				}
			} else {
				this.mode = mode;
			}
		}
		var params = {};
		params.EmergencyTeam_id = this.EmergencyTeam_id;
		params.dateStart = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.dateFinish = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		this.findById('dutyTimeGridPanel').removeAll({
			clearAll: true
		});
		this.findById('dutyTimeGridPanel').loadData({
			globalFilters: params
		});
	},
	getCurrentDateTime: function(){
		if ( getGlobalOptions().date ) {
			this.curDate = getGlobalOptions().date;
			this.mode = 'week';
			this.currentWeek();
			if ( this.gridPanelAutoLoad ) {
				this.doSearch( this.mode );
			}
		} else {
			var obj = this;
			obj.getLoadMask( LOAD_WAIT ).show();
			Ext.Ajax.request({
				url: C_LOAD_CURTIME,
				callback: function(opt, success, response){
					if ( success && response.responseText != '' ) {
						var result  = Ext.util.JSON.decode(response.responseText);
						obj.curDate = result.begDate;
						obj.mode = 'week';
						obj.currentWeek();
						if ( obj.gridPanelAutoLoad ) {
							obj.doSearch( obj.mode );
						}
					}
					obj.getLoadMask().hide();
				}.createDelegate(this)
			});
		}
	},
	
	setEmergencyTeamWorkComing: function(){
		
		var grid = this.findById('dutyTimeGridPanel');
		
		var record = grid.getSelectionModel().getSelected();
		
		if ( !record ) {
			sw.swMsg.alert(lang['oshibka'], lang['vyi_doljnyi_vyibrat_smenu_dlya_kotoroy_sobiraetes_ustanovit_otmetku_o_vyihode_na_rabotu']);
			return false;
		}
		
		if ( !record.get('EmergencyTeamDuty_id') || !this.EmergencyTeam_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['neudalos_nayti_vyibrannuyu_smenu_poprobuyte_obnovit_okno_ili_obratites_k_administratoru']);
			return false;
		}
		
		var form = this;
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNOCANCEL,
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyiberite_da_-_esli_brigada_vyishla_na_smenu_net_-_esli_brigada_ne_vyishla_na_smenu_otmena_-_otmena_deystviya'],
			title: lang['vopros'],
			fn: function(buttonId, text, obj) {
				
				if ( buttonId == 'cancel' ) {
					return;
				}
				Ext.Ajax.request({
					callback: function(options,success,response){
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['neudalos_postavit_otmetku_o_vyihode_na_rabotu_1']);
							} else {
								// todo проверить
								form.doSearch();
							}

							if ( grid.getStore().getCount() > 0 ) {
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['neudalos_postavit_otmetku_o_vyihode_na_rabotu_2']);
						}
					},
					params: {
						EmergencyTeam_id: form.EmergencyTeam_id,
						EmergencyTeamDuty_id: record.get('EmergencyTeamDuty_id'),
						EmergencyTeamDuty_isComesToWork: buttonId == 'yes' ? 2 : 1
					},
					url: '/?c=EmergencyTeam&m=setEmergencyTeamWorkComing'
				});
			}
		});
	},
	
	initComponent: function(){          
		                               
		this.initActions();
		
		this.WindowToolbar = new Ext.Toolbar({
			items: [
				this.formActions.prev,
				{ xtype: 'tbseparator' },
				this.dateMenu,
				{ xtype: 'tbseparator' },
				this.formActions.next, 
				//{ xtype: 'tbfill' },
				{ xtype: 'tbseparator' },
				this.formActions.range,
				//this.formActions.day,
				this.formActions.week, 
				//this.formActions.month,
				{ xtype: 'tbseparator' }
			]
		});

		/**
		 * Форма заполнения графика нарядов
		 */

		var date = new Date();
		var today = new Date( date.getFullYear(), date.getMonth(), date.getDate() );

		var items = []
		for( i=1; i<=7; i++ ){
			var date_start = {
				xtype: 'swdatetimefield',
				hiddenName: 'EmergencyTeamDuty_DateStart[]',
				hiddenId: this.generateDateId( 1, 'datestart_' ),
				dateLabel: lang['smena_s'],
				timeLabel: '',
				timeLabelWidth: 1,
				allowBlank: i == 1 ? false : true,
				minValue: today,
				maxValue: null,
				setDateMaxValueWhenGetFromSrv: false,
				setDateMinValueWhenGetFromSrv: false
			}
			var date_finish = {
				xtype: 'swdatetimefield',
				hiddenName: 'EmergencyTeamDuty_DateFinish[]',
				hiddenId: this.generateDateId( 0, 'datefinish_' ),
				dateLabel: lang['po'],
				labelWidth: 30,
				timeLabel: '',
				timeLabelWidth: 1,
				allowBlank: i == 1 ? false : true,
				minValue: today,
				maxValue: null,
				setDateMaxValueWhenGetFromSrv: false,
				setDateMinValueWhenGetFromSrv: false
			}
			var date_time = {
				border: false,
				layout: 'column',
				items: [date_start,date_finish]
			};
			items.push( date_time );
		}

		var setDutyTimeFormPanel = new Ext.form.FormPanel({
			id: 'setDutyTimeFormPanel',
			region: 'center',
			url: '/?c=EmergencyTeam&m=saveEmergencyTeamDutyTime',
			autoHeight: true,
			frame: true,
			items: [{
				xtype: 'hidden',
				name: 'EmergencyTeam_id',
				value: 0
			},{
				layout: 'form',
				labelAlign: 'right',
				labelWidth: 50,
				style: 'padding: 5px;',
				items: items
			}],
			buttons: [{
				text: BTN_FRMSAVE,
				handler: function(){
					this.saveTime();
				}.createDelegate(this),
				iconCls: 'save16'
			}]
		});


		/**
		 * Вывод графика нарядов
		 */
		
		var gridFields = [{
			dataIndex: 'EmergencyTeamDuty_id', 
			header: 'ID', 
			key: true, 
			hidden: true, 
			hideable: false
		},{
			dataIndex: 'EmergencyTeamDuty_DTStart', 
			header: lang['nachalo_smenyi'], 
			width: 150
		},{
			dataIndex: 'EmergencyTeamDuty_DTFinish', 
			header: lang['okonchanie_smenyi'], 
			width: 150
		},{
			dataIndex: 'ComesToWork', 
			header: lang['brigada_vyishla_na_smenu'], 
			width: 150
		}];
                	
		var storeFields = [];
		for( var i=0, cnt=gridFields.length; i<cnt; i++ ){
			storeFields.push({
				mapping: gridFields[i].dataIndex,
				name: gridFields[i].dataIndex
			});
		}
		
		var gridStore = new Ext.data.Store({
			autoLoad: false,
			sortInfo: {
				field: 'EmergencyTeamDuty_DTStart',
				direction: 'ASC'
			},
			reader: new Ext.data.JsonReader({
				totalProperty: 'totalCount',
				root: 'data'
			}, storeFields),
			url: '/?c=EmergencyTeam&m=loadEmergencyTeamDutyTimeGrid'
		});
		
		var gridActions = [{
			name: 'action_refresh', 
			iconCls: 'refresh16', 
			text: lang['obnovit'], 
			handler: function(){
				this.autoEvent=false;
				this.doSearch();
			}.createDelegate(this)
		},{
			name: 'action_setworkcoming', 
			iconCls: 'active-call16', 
			text: lang['otmetit_smenu'], 
			tooltip: lang['ustanovit_otmetku_o_vyihode_na_rabotu'], 
			handler: function(){
				this.setEmergencyTeamWorkComing();
			}.createDelegate(this)
		}];
                
		var dutyTimeGridPanel = new Ext.grid.GridPanel({
			id: 'dutyTimeGridPanel',
			loadMask: {
				msg: LOAD_WAIT
			},
			colModel: new Ext.grid.ColumnModel({
				columns: gridFields
			}),                        
			tbar: this.WindowToolbar,
			store: gridStore,
			loadData: function( params ){
				with(this.getStore()){
					removeAll();
					baseParams = params.globalFilters;
					load();
				}
			},
			actions: gridActions,                        
			listeners: {
				render: function() {
					this.getTopToolbar().add({
						xtype: 'tbfill'
					},{
						xtype: 'tbseparator'
					});
					this.contextMenu = new Ext.menu.Menu();
					this.ViewActions = {};
					for( var i=0, cnt=this.actions.length; i<cnt; i++ ){
						this.ViewActions[this.actions[i]['name']] = new Ext.Action(this.actions[i]);
						this.getTopToolbar().add(this.ViewActions[this.actions[i]['name']]);
						this.contextMenu.add(this.ViewActions[this.actions[i]['name']]);
					}
				},
				rowcontextmenu: function(grd,num,e){
					e.stopEvent();
					this.getSelectionModel().selectRow(num);
					this.contextMenu.showAt(e.getXY());
				}
			}
		});
	   
		/**
		 * Табы
		 */
		var tabs = new Ext.TabPanel({
			id: this.id+'_tabPanel',
			activeTab: 0,
			region: 'center',
			border: false,
			items: [{
				title: lang['zadat_vremya'],
				layout: 'fit',
				id: 'tab_setDutyTime',
				hidden: false,
				items: [ setDutyTimeFormPanel ]
			}, {
				title: lang['grafik_naryadov'],
				layout: 'fit',
				id: 'tab_dutyTimeGridPanel',
				hidden: false,
				items: [ dutyTimeGridPanel ]                     
			}]
		});

		Ext.apply(this,{
			xtype: 'panel',
			layout: 'border',
			items: [ tabs ]
		});
		
		sw.Promed.swSmpEmergencyTeamSetDutyTimeWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(){
		sw.Promed.swSmpEmergencyTeamSetDutyTimeWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] || !arguments[0].EmergencyTeam_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_peredanyi_parametryi'], function() {
				this.hide(); }.createDelegate(this)
			);
			return false;
		}
		this.EmergencyTeam_id = arguments[0].EmergencyTeam_id;
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		this.findById( this.id+'_tabPanel' ).setActiveTab('tab_dutyTimeGridPanel');
		this.findById( this.id+'_tabPanel' ).setActiveTab('tab_setDutyTime');
		
		this.syncSize();
		this.doLayout();
		this.restore();
		this.center();
		
		// Сбрасываем значения формы
		this.findById('setDutyTimeFormPanel').getForm().reset();
		
		this.findById('setDutyTimeFormPanel').getForm().findField('EmergencyTeam_id').setValue( arguments[0].EmergencyTeam_id );
		
		this.getCurrentDateTime();
	},
	
	saveTime: function(){
		var base_form = this.findById('setDutyTimeFormPanel');
		if ( !base_form.getForm().isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function(){
					base_form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {
			msg: "Подождите, идет сохранение данных графика нарядов."
		});
		loadMask.show();

		base_form.getForm().submit({
			failure: function(result_form, action) {
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_informatsii_o_smenah_proizoshla_oshibka']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();
				if ( !action.result ) {
					sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_informatsii_o_smenah_proizoshla_oshibka_poluchen_ne_vernyiy_otvet']);
					return false;
				}
				
				if ( action.result.success ) {
					// dutyTimeGridPanel
					this.doSearch();
				
					this.callback();
					this.hide();
					return true;
				}
				
				if ( action.result.Error_Msg ) {
					sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_informatsii_o_smenah_proizoshla_oshibka_neobhodimyie_dannyie_otsutstvuyut']);
				}
				
				return false;
			}.createDelegate(this)
		});
	}
});