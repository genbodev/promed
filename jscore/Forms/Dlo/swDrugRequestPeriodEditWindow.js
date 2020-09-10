/**
* swDrugRequestPeriodEditWindow - окно редактирования "Справочник медикаментов: период заявки"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Rustam Salakhov
* @version      07.2012
* @comment      
*/
sw.Promed.swDrugRequestPeriodEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['rabochiy_period_dobavlenie'],
	layout: 'fit',
	id: 'DrugRequestPeriodEditWindow',
	modal: true,
	shim: false,
	width: 550,
	height: 375,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	setDefaultValues: function() {
		var wnd = this;		
		Ext.Ajax.request({
			url: '/?c=DrugRequest&m=getDrugRequestPeriodMaxDate',
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);					
					if (result[0].max_date && result[0].max_date.split != '') {
						var date_arr = result[0].max_date.split('.');
						var start_date = new Date();
						start_date.setDate(date_arr[0]);
						start_date.setMonth(date_arr[1]-1);
						start_date.setYear(date_arr[2]);
						start_date = start_date.add(Date.DAY, 1).clearTime();
						
						var month = start_date.getMonth();
						month = month - (month%3);
						var kv = (month/3)+1;
						
						start_date.setDate(1);
						start_date.setMonth(month);
						
						wnd.form.setValues({
							'DrugRequestPeriod_begDate': Ext.util.Format.date(start_date.clearTime(), 'd.m.Y'),
							'DrugRequestPeriod_endDate': Ext.util.Format.date(start_date.add(Date.MONTH, 3).add(Date.DAY, -1).clearTime(), 'd.m.Y'), 
							'DrugRequestPeriod_Name': kv+'-'+(kv == 1 ? 'ы' : '')+'й квартал '+start_date.getFullYear()+' года'
						});
					}
				}
			}
		});
	},
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('drpeDrugRequestPeriodEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit();
		return true;		
	},
	submit: function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = wnd.action;
		params.DrugRequestPlanPeriodJSON = wnd.PlanPeriodGrid.getJSONChangedData();

		this.form.submit({
			params: params,
			failure: function(result_form, action)  {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				wnd.callback(wnd.owner, action.result.DrugRequestPeriod_id);
				wnd.hide();
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swDrugRequestPeriodEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.DrugRequestPeriod_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].DrugRequestPeriod_id ) {
			this.DrugRequestPeriod_id = arguments[0].DrugRequestPeriod_id;
		}

		this.PlanPeriodGrid.addActions({
			name: 'action_delete_all',
			iconCls: 'delete16',
			text: lang['udalit_vse'],
			handler: function() {
				wnd.PlanPeriodGrid.deleteRecords();
			}
		});

		this.PlanPeriodGrid.addActions({
			name: 'action_drpe_form',
			iconCls: 'add16',
			text: lang['sformirovat'],
			menu: [{
				name: 'create_by_month',
				iconCls: 'add16',
				text: lang['po_mesyatsam'],
				handler: function() {
					wnd.PlanPeriodGrid.createRecords('month');
				}
			}, {
				name: 'create_by_quarter',
				iconCls: 'add16',
				text: lang['po_kvartalam'],
				handler: function() {
					wnd.PlanPeriodGrid.createRecords('quarter');
				}
			}]
		});

		this.PlanPeriodGrid.setActionsDisabled();

		this.form.reset();
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				wnd.setTitle(lang['rabochiy_period_dobavlenie']);
				wnd.setDefaultValues();
				wnd.PlanPeriodGrid.removeAll();
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				wnd.setTitle(wnd.action == 'edit' ? lang['rabochiy_period_redaktirovanie'] : lang['rabochiy_period_prosmotr']);
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						DrugRequestPeriod_id: wnd.DrugRequestPeriod_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						wnd.form.setValues(result[0]);

						//если планово отчетные периоды уже используются, их редактирование запрещено
						if (result[0].DrugRequestPlanPeriod_UsedCount > 0) {
							wnd.PlanPeriodGrid.setReadOnly(true);
						} else {
							wnd.PlanPeriodGrid.setReadOnly(false);
						}

						loadMask.hide();
					},
					url:'/?c=MzDrugRequest&m=loadDrugRequestPeriod'
				});
				wnd.PlanPeriodGrid.loadData({
					globalFilters: {
						DrugRequestPeriod_id: wnd.DrugRequestPeriod_id
					}
				});
			break;	
		}
	},
	initComponent: function() {
		var wnd = this;

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,
			frame: true,
			region: 'north',
			height: 100,
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				id: 'drpeDrugRequestPeriodEditForm',
				style: '',
				bodyStyle:'background:#DFE8F6;padding:4px;',
				border: true,
				labelWidth: 120,
				collapsible: true,
				url:'/?c=DrugRequest&m=saveDrugRequestPeriod',
				items: [{
					name: 'DrugRequestPeriod_id',
					xtype: 'hidden',
					value: 0
				}, {					
					allowBlank: false,
					fieldLabel: lang['nachalo_perioda'],
					id: 'drpeDrugRequestPeriod_begDate',
					name: 'DrugRequestPeriod_begDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'					
				}, {					
					allowBlank: false,
					fieldLabel: lang['konets_perioda'],
					id: 'drpeDrugRequestPeriod_endDate',
					name: 'DrugRequestPeriod_endDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'					
				}, {
					fieldLabel: lang['naimenovanie'],
					name: 'DrugRequestPeriod_Name',
					allowBlank:false,
					maxLength: 30,
					xtype: 'textfield',
					anchor: '100%'
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'DrugRequestPeriod_id'}, 
				{name: 'DrugRequestPeriod_begDate'}, 
				{name: 'DrugRequestPeriod_endDate'}, 
				{name: 'DrugRequestPeriod_Name'}
			]),
			url: '/?c=DrugRequest&m=saveDrugRequestPeriod'
		});

		this.PlanPeriodGrid = new sw.Promed.ViewFrame({
			title: lang['planovo-otchetnyie_periodyi'],
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=LoadDrugRequestPlanPeriodList',
			height: 180,
			region: 'center',
			object: 'DrugRequestPlanPeriod',
			id: 'drpeDrugRequestPlanPeriodGrid',
			paging: false,
			saveAtOnce:false,
			style: 'margin-bottom: 0px',
			stringfields: [
				{name: 'DrugRequestPlanPeriod_id', type: 'int', header: 'ID', key: true},
				{name: 'state', hidden: true},
				{name: 'DrugRequestPlanPeriod_Name', id: 'autoexpand', header: lang['naimenovanie']},
				{name: 'DrugRequestPlanPeriod_begDate', width: 110, header: lang['data_nachala'], type: 'date'},
				{name: 'DrugRequestPlanPeriod_endDate', width: 110, header: lang['data_okonchaniya'], type: 'date'}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				this.setActionsDisabled();
			},
			setActionsDisabled: function() {
				if (this.readOnly) {
					this.ViewActions.action_drpe_form.setDisabled(true);
					this.ViewActions.action_delete_all.setDisabled(true);
				} else {
					var record_count = 0;

					this.getGrid().getStore().each(function(record) {
						if (record.get('DrugRequestPlanPeriod_id') > 0) {
							record_count++;
						}
					});

					if (record_count > 0) {
						this.ViewActions.action_drpe_form.setDisabled(true);
						this.ViewActions.action_delete_all.setDisabled(false);
					} else {
						this.ViewActions.action_drpe_form.setDisabled(false);
						this.ViewActions.action_delete_all.setDisabled(true);
					}
				}
			},
			createRecords: function(create_type) {
				var period_arr = new Array();
				var start_date = wnd.form.findField('DrugRequestPeriod_begDate').getValue();
				var end_date = wnd.form.findField('DrugRequestPeriod_endDate').getValue();

				if (Ext.isEmpty(start_date) || Ext.isEmpty(end_date)) {
					sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_ukazat_datyi_nachala_i_okonchaniya_rabochego_perioda']);
					return false;
				}

				if (start_date > end_date) {
					sw.swMsg.alert(lang['oshibka'], lang['data_nachala_perioda_doljna_byit_pozje_datyi_okonchaniya']);
					return false;
				}

				var period_start = new Date(start_date);
				var period_end = null;
				var next_month = null;
				var next_month_year = null;

				var quarter_num = null;
				var period_name = null;

				while (period_end < end_date) {
					quarter_num = (period_start.getMonth()/3>>0)+1;
					if (create_type == 'month') {
						next_month = period_start.getMonth() != 11 ? period_start.getMonth()+1 : 0;
					}
					if (create_type == 'quarter') {
						next_month = quarter_num != 4 ? quarter_num*3 : 0;
					}
					next_month_year = next_month > 0 ? period_start.getFullYear() : period_start.getFullYear()+1;

					period_end = new Date(next_month_year, next_month, 0);
					if (period_end > end_date) {
						period_end = new Date(end_date);
					}

					if (create_type == 'month') {
						period_name = period_start.format('m.Y');
					}
					if (create_type == 'quarter') {
						period_name = quarter_num + lang['-y_kvartal'] + period_start.format('Y');
					}

					period_arr.unshift({
						DrugRequestPlanPeriod_Name: period_name,
						DrugRequestPlanPeriod_begDate: period_start,
						DrugRequestPlanPeriod_endDate: period_end
					});

					period_start = new Date(next_month_year, next_month, 1);
				}

				if (period_arr.length > 0) {
					this.addRecords(period_arr);
				}
			},
			deleteRecords: function(){
				var view_frame = this;
				var store = view_frame.getGrid().getStore();

				store.each(function(record) {
					if (record.get('state') == 'add') {
						view_frame.getGrid().getStore().remove(record);
					} else {
						record.set('state', 'delete');
						record.commit();
					}
				});
				view_frame.setFilter();
				view_frame.setActionsDisabled();
				view_frame.updateCount();
			},
			addRecords: function(data_arr){
				var view_frame = this;
				var store = view_frame.getGrid().getStore();
				var record_count = store.getCount();
				var record = new Ext.data.Record.create(view_frame.jsonData['store']);

				if ( record_count == 1 && !store.getAt(0).get('DrugRequestPlanPeriod_id') ) {
					view_frame.removeAll({addEmptyRecord: false});
					record_count = 0;
				}

				view_frame.clearFilter();
				for (var i = 0; i < data_arr.length; i++) {
					data_arr[i].DrugRequestPlanPeriod_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
					data_arr[i].state = 'add';
					store.insert(record_count, new record(data_arr[i]));
				}
				view_frame.setFilter();
				view_frame.setActionsDisabled();
				view_frame.updateCount();
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if ((record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')) {
						var item = record.data;
						item.FileData = null;
						data.push(item);
					}
				});
				this.setFilter();
				return data;
			},
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
				this.getGrid().getStore().clearFilter();
			},
			setFilter: function() { //скрывает удаленные записи
				this.getGrid().getStore().filterBy(function(record){
					return (record.get('state') != 'delete');
				});
			},
			updateCount: function() { //обновление информации о количестве, если в гриде есть записи - выделение первой
				if (this.getGrid().getStore().getCount() > 0) {
					this.ViewGridModel.selectRow(0);
				} else if (this.ViewGridPanel.getTopToolbar().items.last()) {
					this.ViewGridPanel.getTopToolbar().items.last().el.innerHTML = '0 / 0';
				}
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				form,
				this.PlanPeriodGrid
			]
		});
		sw.Promed.swDrugRequestPeriodEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('drpeDrugRequestPeriodEditForm').getForm();
	}	
});