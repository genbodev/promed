/**
 * Списание лекарственных средств из укладки бригады скорой помощи на пациента
 */

sw.Promed.swEmergencyTeamDrugpackCancellationWindow = Ext.extend(sw.Promed.BaseForm,{
	title: lang['spisanie_lekarstvennyih_sredstv_iz_ukladki_brigadyi_skoroy_pomoschi_na_patsienta'],
	modal: true,
	formPanel: null,
	formPanelStore: null,
	north_panel: null,
	center_panel: null,
	drugpackGridPanel: null,
	callback: Ext.emptyFn,
	// Если указана функция, сохранение выполнено не будет, а данные для сохранения будут переданы функции
	doSaveCallback: null,
	
	onDeleteCancellationsFormPanelRow: function(){
		var record = this.getDrugpackGridPanel().getGrid().getSelectionModel().getSelected();

		// Убираем запись из грида
		if ( record.data.status == 'added' ) {
			this.getDrugpackGridPanel().getGrid().getStore().remove(record);
		} else {
			// @todo Если сохраненная запись была удалена, она не будет возвращена при сохранении из-за filterBy
			// Отмечаем запись удаленной
			record.set('status', 'deleted');
			// Скрываем все удаленные записи
			this.getDrugpackGridPanel().getGrid().getStore().filterBy(function(record,id){
				if ( record.data.status == 'deleted' ) {
					return false;
				}
				return true;
			});
		}
					
		// Убираем идентификатор записи из "архива"
		for( var i=0,cnt=this.cancellation_ids.length; i<cnt; i++ ){
			if ( this.cancellation_ids[i] == record.data.EmergencyTeamDrugPack_id ) {
				this.cancellation_ids.splice(i, 1);
			}
		}

		// Фильтруем грид
		this.formPanelStore.clearFilter();
		this.formPanelStore.filterBy(function(record,id){
			if ( record.data.EmergencyTeamDrugPack_id.inlist(this.cancellation_ids) ) {
				return false;
			}
			return true;
		},this);
	},
	
	formPanelColModel: new Ext.grid.ColumnModel([
		{dataIndex: 'EmergencyTeamDrugPack_id', header: 'ID', key: true, hidden: true},
		{dataIndex: 'DrugTorg_Name', header: lang['naimenovanie'], id: 'DrugTorg_Name'},
		{dataIndex: 'EmergencyTeamDrugPack_Total', header: lang['kol-vo_doz'], width: 100},
	]),
	
	initDrugpackGridPanel: function(){
		this.drugpackGridPanel = new sw.Promed.ViewFrame({
			title: lang['lekarstvennyie_sredstva_vyibrannyie_dlya_spisaniya'],
			uniqueId: true,
			height: 150,
			stringfields: [
				{name: 'EmergencyTeamDrugPack_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'DrugTorg_Name', header: lang['naimenovanie'], id: 'autoexpand'},
				{name: 'EmergencyTeamDrugPackMove_Quantity', header: lang['kol-vo_doz'], width: 100},
				{name: 'status', hidden: true}
			],
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_sign', hidden: true },
				{ name: 'action_save', hidden: true },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_delete', handler: this.onDeleteCancellationsFormPanelRow.createDelegate(this) }
			],
			onRowSelect: function(){
				this.setActionDisabled('action_delete',false);
			}
		});
	},
	
	getDrugpackGridPanel: function(){
		if ( this.drugpackGridPanel === null ) {
			this.initDrugpackGridPanel();
		}
		
		return this.drugpackGridPanel;
	},
	
	// Идентификаторы записей перемещенных для списания
	cancellation_ids: [],
	
	initCancellationFormPanel: function(){
		this.formPanelStore = new Ext.data.Store({
			url: '/?c=CmpCallCard4E&m=loadEmergencyTeamDrugPackByCmpCallCardId',
			autoLoad: false,
			reader: new Ext.data.JsonReader({}, [
				{name: 'EmergencyTeamDrugPack_id', type: 'int'},
				{name: 'DrugTorg_Name', type: 'string'},
				{name: 'EmergencyTeamDrugPack_Total', type: 'float'},
			])
		});
		
		this.formPanel = new sw.Promed.FormPanel({
			frame: true,
			labelAlign: 'left',
			width: 800,
			items: [{
				layout: 'fit',
				items: {
					xtype: 'grid',
					title: lang['ukladka'],
					ds: this.formPanelStore,
					cm: this.formPanelColModel,
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true
					}),
					autoExpandColumn: 'DrugTorg_Name',
					height: 150,
					border: true,
					listeners: {
						scope: this,
						render: function (g) {
							g.getSelectionModel().selectRow(0);
						},
						rowdblclick: function(el,rowIndex,e){
							var item = this.formPanelStore.getAt(rowIndex);
						
							Ext.MessageBox.prompt(lang['kolichestvo_spisyivaemyih_lekarstvennyih_sredstv'],lang['kolichestvo_doz'],function(btn,val){
								if (btn == 'ok'){
										
									var newval = parseFloat(val.replace(',', '.'));
									if ( newval <= 0 || newval > item.data.EmergencyTeamDrugPack_Total ) {
										sw.swMsg.alert(lang['oshibka'], lang['ukajite_tseloe_ili_drobnoe_chislo_bolshe_nolya_i_ne_prevyishayuschee_kolichestvo_na_ostatke']);
										return false;
									}
									
									var data = item.data;
									data.EmergencyTeamDrugPackMove_Quantity = newval;
									data.status = 'added';
									
									var record = new Ext.data.Record(data);
									
									// Помещаем запись в грид для списания
									this.getDrugpackGridPanel().getGrid().getStore().add(record);
								
									// Помещаем идентификатор записи в "архив"
									this.cancellation_ids.push(item.data.EmergencyTeamDrugPack_id);

									// Фильтруем грид
									if(newval == item.data.EmergencyTeamDrugPack_Total){
										this.formPanelStore.filterBy(function(record,id){
											if ( record.data.EmergencyTeamDrugPack_id.inlist(this.cancellation_ids) ) {
												return false;
											}
											return true;
										},this);
									}
									else{
										item.set('EmergencyTeamDrugPack_Total', item.data.EmergencyTeamDrugPack_Total-newval)
									}
								}
							},this,false,item.data.EmergencyTeamDrugPack_Total);
						},
						delay: 10 // Allow rows to be rendered.
					}
				}
			}/*, {
				xtype: 'fieldset',
				title: lang['forma_spisaniya'],
				labelWidth: 90,
				defaults: {
					width: 140
				}, // Default config options for child items
				defaultType: 'textfield',
				autoHeight: true,
				bodyStyle: 'padding: 5px 10px;',
				style: {
					'margin-top': '10px',
				},
				items: [{
					xtype: 'hidden',
					name: 'EmergencyTeamDrugPack_id'
				},{
					fieldLabel: lang['naimenovanie'],
					name: 'Drug_Name',
					width: '100%',
					readOnly: true
				},{
					fieldLabel: lang['kol-vo_doz'],
					name: 'EmergencyTeamDrugPack_Total',
					allowBlank: false,
					enableKeyEvents: true,
					listeners: {
						scope: this,
						keypress: function(el,e){
							if ( e.getKey() != e.ENTER ) {
								return;
							}
							var values = this.formPanel.getForm().getValues(),
								record = new Ext.data.Record(values);
						
							this.getDrugpackGridPanel().getGrid().getStore().add(record);
						}
					}
				}]
			}*/]
		}); 
	},
	
	getCancellationFormPanel: function(){
		if ( this.formPanel === null ) {
			this.initCancellationFormPanel();
		}
		
		return this.formPanel;
	},
	
	doSave: function(){
		var grid_items = getStoreRecords( this.getDrugpackGridPanel().getGrid().getStore() );
		
		//@todo Надо сделать валидацию данных, получаемых из грида, на стороне клиента
		
		if ( typeof this.doSaveCallback === 'function' ) {
			this.doSaveCallback( grid_items );
			this.hide();
			return;
		}

		sw.swMsg.alert(lang['oshibka'], lang['funktsional_sohraneniya_v_razrabotke']);
		return;
		
		/*
		var waybill_route = getStoreRecords( this.findById(this.id+'RouteGrid').getGrid().getStore(), {} );
		params.WaybillRoute = Ext.util.JSON.encode( waybill_route );

		base_form.submit({
			params: params,
			failure: function(result_form, action){
				this.formStatus = 'edit';
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_informatsii_proizoshla_oshibka']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				if ( action.result ) {
					if ( action.result.Waybill_id ) {
						if ( typeof this.callback == 'function' ){
							//todo Сериализовать данные формы для передачи их функции
							this.callback();
						}
						this.hide();
					} else if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['rezultat_sohraneniya_ne_vernul_ojidaemyih_dannyih']);
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_sohraneniya_informatsii_proizoshla_oshibka']);
				}
			}.createDelegate(this)
		});
		*/
	},
	
	initComponent: function(){
		this.north_panel = new sw.Promed.Panel({
			region: 'north',
			items: [
				this.getCancellationFormPanel()
			]
		});
		
		this.center_panel = new sw.Promed.Panel({
			region: 'center',
			items: [
				this.getDrugpackGridPanel()
			]
		});
		
		Ext.apply(this,{
			items: [ this.north_panel, this.center_panel ],
			buttons: [{
				type: 'submit',
				text: BTN_FRMSAVE,
				tooltip: BTN_FRMSAVE_TIP,
				tabIndex: -1,
				iconCls: 'save16',
				disabled: false,
				handler: function(){
					this.ownerCt.doSave();					
				}
			},{
				text: lang['otmenit'],
				tabIndex: -1,
				tooltip: lang['otmenit_sohranenie'],
				iconCls: 'cancel16',
				handler: function(){
					this.ownerCt.hide();
				}
			},{
				text: BTN_FRMHELP,
				tabIndex: -1,
				tooltip: BTN_FRMHELP_TIP,
				iconCls: 'help16',
				handler: function(){
					ShowHelp(this.ownerCt.title);
				}
			}]
		});
		
		sw.Promed.swEmergencyTeamDrugpackCancellationWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(){
		if ( !arguments[0] || !arguments[0].params || !arguments[0].params.CmpCallCard_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['dlya_spisaniya_lekarstvennyih_sredstv_neobhodimo_peredat_identifikator_kartyi_vyizova']);
			this.hide();
			return false;
		}
		
		this.doLayout();
		
		this.params = arguments[0].params;
		
		if ( typeof this.params.callback === 'function' ) {
			this.callback = this.params.callback;
		}
		
		if ( typeof this.params.doSaveCallback === 'function' ) {
			this.doSaveCallback = this.params.doSaveCallback;
		}

		this.formPanelStore.load({
			params: {
				CmpCallCard_id: this.params.CmpCallCard_id
			}
		});
		
		// Очищаем стор от предыдущих значений
		this.getDrugpackGridPanel().getGrid().getStore().removeAll();
		
		sw.Promed.swEmergencyTeamDrugpackCancellationWindow.superclass.show.apply(this, arguments);	
	}
});