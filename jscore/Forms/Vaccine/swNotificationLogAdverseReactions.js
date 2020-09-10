/**
* swNotificationLogAdverseReactions - журнал извещений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
*/

sw.Promed.swNotificationLogAdverseReactions = Ext.extend(sw.Promed.BaseForm, {
	id: 'NotificationLogAdverseReactions',
	maximized: true,	
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: true,
	plain: false,
	resizable: false,
	title: 'Журнал извещений о неблагоприятных реакциях',
	userMedStaffFact: null,
	openVaccinationNoticeWindow: function(action) {
		var form = getWnd('swVaccinationNoticeWindow');
		if ( form.isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], 'окно извещения о профилактической привике уже открыто');
			return false;
		}

		var grid = this.grid.getGrid();
		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		if ( !selected_record.get('NotifyReaction_id') ) {
			return false;
		}

		getWnd('swVaccinationNoticeWindow').show({action: 'view', NotifyReaction_id: selected_record.get('NotifyReaction_id')});
	},
	resetForm: function(isLoad)
	{
		Ext.getCmp('NLAR_CreateMotification_datePeriod').setValue();
		Ext.getCmp('NLAR_VaccinationPerformance_datePeriod').setValue();
		Ext.getCmp('NLAR_Person_SurName').setValue();
		Ext.getCmp('NLAR_Person_FirName').setValue();
		Ext.getCmp('NLAR_Person_SecName').setValue();
		Ext.getCmp('NLAR_vac_name').setValue();
		Ext.getCmp('NLAR_Seria').setValue();
	},
	begDate: null,
	searchInProgress: false,
	doSearch: function() {
		this.loadGridWithFilter(false);
	},
	loadGridWithFilter: function(clear) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		var grid = this.grid;
				
		var params = {
			limit: 100,
			start: 0
		};

		params.Lpu_id = this.filter.findById('NLAR_Lpu_aid').getValue();
		var createMotification_datePeriod = this.filter.findById('NLAR_CreateMotification_datePeriod');
		params.CreateMotification_datePeriod_beg = Ext.util.Format.date(createMotification_datePeriod.getValue1(), 'd.m.Y');
		params.CreateMotification_datePeriod_end = Ext.util.Format.date(createMotification_datePeriod.getValue2(), 'd.m.Y');
		var vaccinationPerformance_datePeriod = this.filter.findById('NLAR_VaccinationPerformance_datePeriod');
		params.VaccinationPerformance_datePeriod_beg = Ext.util.Format.date(vaccinationPerformance_datePeriod.getValue1(), 'd.m.Y');
		params.VaccinationPerformance_datePeriod_end = Ext.util.Format.date(vaccinationPerformance_datePeriod.getValue2(), 'd.m.Y');
		params.Person_SurName = this.filter.findById('NLAR_Person_SurName').getValue();
		params.Person_FirName = this.filter.findById('NLAR_Person_FirName').getValue();
		params.Person_SecName = this.filter.findById('NLAR_Person_SecName').getValue();
		params.vac_name = this.filter.findById('NLAR_vac_name').getValue();
		params.Seria = this.filter.findById('NLAR_Seria').getValue();
		grid.loadData({
			globalFilters: params
		});
	},
	show: function()
	{
		sw.Promed.swNotificationLogAdverseReactions.superclass.show.apply(this, arguments);
		this.userMedStaffFact = null;
		
		this.center();
		this.resetForm(true);
		current_window = this;
		this.viewOnly = true;
		if(arguments[0] && arguments[0].viewOnly) {
			this.viewOnly = arguments[0].viewOnly;
		}		

		if ( Ext.getCmp('NLAR_Lpu_aid').getStore().getCount() == 0 ) {
			Ext.getCmp('NLAR_Lpu_aid').getStore().load({
				callback: function(records, options, success) {
					if ( !success ) {
						Ext.getCmp('NLAR_Lpu_aid').getStore().removeAll();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке справочника МО'));
						return false;
					}

					Ext.getCmp('NLAR_Lpu_aid').setValue(getGlobalOptions().lpu_id);					
				}
			});
		} else {
			Ext.getCmp('NLAR_Lpu_aid').setValue(getGlobalOptions().lpu_id);
			Ext.getCmp('NLAR_Lpu_aid').fireEvent('change', Ext.getCmp('NLAR_Lpu_aid'), Ext.getCmp('NLAR_Lpu_aid').getValue());
		}

		Ext.getCmp('NLAR_CreateMotification_datePeriod').setValue();
		Ext.getCmp('NLAR_VaccinationPerformance_datePeriod').setValue();
		Ext.getCmp('NLAR_Person_SurName').setValue();
		Ext.getCmp('NLAR_Person_FirName').setValue();
		Ext.getCmp('NLAR_Person_SecName').setValue();
		Ext.getCmp('NLAR_vac_name').setValue();
		Ext.getCmp('NLAR_Seria').setValue();	

		if(getGlobalOptions().curARMType == 'spec_mz'){
			Ext.getCmp('NLAR_Lpu_aid').enable();
		}else{
			Ext.getCmp('NLAR_Lpu_aid').disable();
		}
	},
	initComponent: function()
	{
		var current_window = this;
		this.filter = new Ext.form.FieldSet(
		{
			region: 'north',
			xtype: 'fieldset',
			autoHeight: true,
			title: lang['filtr'],
			layout: 'column',
			keys: [
				{
					fn: function(inp, e) {
						if ( e.getKey() == Ext.EventObject.ENTER || (e.altKey && e.getKey() == Ext.EventObject.S ) )
							Ext.getCmp('NotificationLogAdverseReactions').doSearch();
					},
					key: [
						Ext.EventObject.ENTER,
						Ext.EventObject.S
					],
					stopEvent: true
				}
			],
			items:
			[{
				layout: 'form',
				labelAlign: 'right',
				id: 'NLAR_Notification_FilterForm',
				labelWidth: 270,
				border: false,
				bodyStyle: 'background-color: transparent; padding-left: 5px;',
				items:
				[
				{
					xtype: 'daterangefield',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
					name: 'CreateMotification_datePeriod',
					id: 'NLAR_CreateMotification_datePeriod',
					width: 200,
					fieldLabel: 'Период дат создания извещения'
				},{
					xtype: 'daterangefield',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
					// name: 'EvnPrescrMse_issueDT2',
					name: 'VaccinationPerformance_datePeriod',
					id: 'NLAR_VaccinationPerformance_datePeriod',
					width: 200,
					fieldLabel: 'Период дат исполнения прививки'
				},{
					xtype: 'textfieldpmw',
					name: 'Person_SurName',
					id: 'NLAR_Person_SurName',
					fieldLabel: 'Фамилия пациента'
				}, {
					xtype: 'textfieldpmw',
					name: 'Person_FirName',
					id: 'NLAR_Person_FirName',
					fieldLabel: 'Имя пациента'
				}, {
					xtype: 'textfieldpmw',
					name: 'Person_SecName',
					id: 'NLAR_Person_SecName',
					fieldLabel: 'Отчество пациента'
				},
				]
			},
			{
				layout: 'form',
				labelAlign: 'right',
				labelWidth: 170,
				border: false,
				bodyStyle: 'background-color: transparent; padding-left: 5px;',
				items:
				[{
					allowBlank: false,
					tabIndex: TABINDEX_CCCJW + 4,
					width: 300,
					listWidth: 350,
					fieldLabel: 'МО вакцинации',
					id: 'NLAR_Lpu_aid',
					hiddenName: 'Lpu_aid',
					xtype: 'swlpusearchcombo',
					listeners: {
						'change': function(combo, value){
							//
						}
					}
				},
				{
					xtype: 'textfieldpmw',
					name: 'vac_name',
					id: 'NLAR_vac_name',
					fieldLabel: 'Вакцина'
				}, {
					xtype: 'textfield',
					name: 'Seria',
					id: 'NLAR_Seria',
					fieldLabel: 'Серия'
				},
				]
			}]
		});


		this.grid = new sw.Promed.ViewFrame(
		{
			id: 'NLAR_JournalGrid',
			dataUrl: '/?c=VaccineCtrl&m=loadGridVaccinationNotice',
			layout: 'fit',
			region: 'center',
			paging: true,
			root: 'data',
			title: 'Журнал извещений',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'NotifyReaction_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'NotifyReaction_confirmDate', type: 'date', header: 'Дата исполнения', width: 100},
				{name: 'NotifyReaction_createDate', type: 'date', header: 'Дата извещения', width: 100},
				{name: 'Person_SurName', autoexpand: true, type: 'string', header: lang['familiya']},
				{name: 'Person_FirName', type: 'string', width: 120, header: lang['imya']},
				{name: 'Person_SecName', type: 'string', width: 120, header: lang['otchestvo']},
				{name: 'vac_name', type: 'string', header: 'Вакцина', width: 90},
				{name: 'Seria', type: 'string', header: 'Серия', width: 90},
				{name: 'VACCINE_DOZA', type: 'string', header: 'Доза', width: 120},
				{name: 'Lpu_Name', type: 'string', header: 'МО', width: 80},
				{name: 'Executed_MedPersonal_Name', header: 'Исполнивший врач', width: 150},
				{name: 'CreateNotification_MedPersonal_Name', header: 'Врач, создавший извещение', width: 150}
			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_view', text: lang['prosmotr'], handler: function() {this.openVaccinationNoticeWindow('view')}.createDelegate(this)},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_print', hidden: true, disabled: true}
			],
			onCellClick: function(grid,rowIdx,colIdx,e) {
				// var record = grid.getStore().getAt(rowIdx);
				// if ( !record ) {
				// 	return false;
				// }
				// // Открываем просмотр направления по клику по иконке направления
				// if (12 == colIdx && record.data.IsEvnDirection && record.data.EvnDirection_id)
				// {
				// 	getWnd('swEvnDirectionEditWindow').show({
				// 		action: 'view',
				// 		formParams: new Object(),
				// 		EvnDirection_id: record.data.EvnDirection_id
				// 	});
				// }
			},
			onRowSelect: function(sm,rowIdx,record) {
				
				// if (record.get('Person_id')) {
				// 	this.grid.ViewActions['open_emk'].setDisabled(false);			
				// } else {
				// 	this.grid.ViewActions['open_emk'].setDisabled(true);
				// }
				
			}.createDelegate(this),
			onLoadData: function() {
				this.searchInProgress = false;
			}.createDelegate(this)
		});

		Ext.apply(this,
		{
			region: 'center',
			layout: 'border',
			items: [
			this.filter,
			this.grid
			],
			buttons: [{
				id: 'NLAR_BtnSearch',
				text: BTN_FRMSEARCH,
				tabIndex: TABINDEX_CCCJW + 19,
				iconCls: 'search16',
				handler: function()
				{
					var form = Ext.getCmp('NotificationLogAdverseReactions');
					form.doSearch();
				}
			},
			{
				id: 'NLAR_BtnClear1',
				text: lang['sbros'],
				tabIndex: TABINDEX_CCCJW + 20,
				iconCls: 'resetsearch16',
				handler: function()
				{
					// var form = Ext.getCmp('NotificationLogAdverseReactions');
					this.resetForm();

				}.createDelegate(this)
			},
			{
				text: '-'
			},
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: TABINDEX_CCCJW + 50,
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			enableKeyEvents: true,
			keys:
			[{
				alt: true,
				fn: function(inp, e)
				{
					if (e.getKey() == Ext.EventObject.ESC)
					{
						Ext.getCmp('NotificationLogAdverseReactions').hide();
						return false;
					}
				},
				key: [ Ext.EventObject.ESC ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swNotificationLogAdverseReactions.superclass.initComponent.apply(this, arguments);
	}
});