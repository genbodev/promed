/**
 * swVaccinationWindow - Форма Назначение вакцинации
 *
 */
Ext6.define('common.EMK.Vaccination.swVaccinationWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swVaccinationWindow',
	autoShow: false,
	cls: 'arm-window-new save-template-window arm-window-new-without-padding',
	title: 'Вакцинация',
	renderTo: main_center_panel.body.dom,
	width: 760,
	modal: true,
	show: function (data) {
		this.params = data
		this.callParent(arguments);

		// загрузка грида
		this.vaccinesDosesGridStore.load({
			params: {
				Evn_id : this.params.Evn_id,
				Person_id : this.params.Person_id,
				Lpu_id : getGlobalOptions().lpu_id,
				PersonEvn_id: this.params.PersonEvn_id,
			},
		});
		this.callback = (typeof data.callback == 'function' ? data.callback : Ext6.emptyFn);
	},
	apply: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();

		if (!baseForm.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.formPanel.getFirstInvalidEl().focus();
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return;
		}

		me.callback(baseForm.getValues());
		me.hide();
	},
	showRecordMenu: function(el, EvnPrescrVaccination_id, EvnVaccination_id, VaccionationPermission_id, pos) {
		this.recordMenu.EvnPrescrVaccination_id = EvnPrescrVaccination_id; // назначение
		this.recordMenu.EvnVaccination_id = EvnVaccination_id; // событие вакцинации
		this.recordMenu.VaccionationPermission_id = VaccionationPermission_id; // согласие на вакцинацию
		if(VaccionationPermission_id != null ){
			this.recordMenu.items.items[0].disable()
			this.recordMenu.items.items[1].enable()
			this.recordMenu.items.items[2].enable()
		}
		else {
			this.recordMenu.items.items[0].enable()
			this.recordMenu.items.items[1].disable()
			this.recordMenu.items.items[2].disable()
		}
		this.recordMenu.showBy(el);
	},
	loadGrid: function() {
		// загрузка грида
		this.vaccinesDosesGridStore.load({
			params: {
				Evn_id : this.params.Evn_id,
				Person_id : this.params.Person_id,
				Lpu_id : getGlobalOptions().lpu_id,
				PersonEvn_id: this.params.PersonEvn_id,
				MedService_id: this.MedService_id
			},
		})
	},
	initComponent: function() {
		var me = this;
		var labelWidth = 140;
		this.MedService_id = null
		
		me.vaccinesDosesGridStore = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'EvnPrescrVaccination_id'},
				{name: 'EvnVaccination_id'},
				{name: 'TN_NAME'},
				{name: 'Prep_Dose'},
				{name: 'VaccinationType_Name'},
				{name: 'VaccionationPermission_id'},
				{name: 'DocumentUcStr_id'},
				{name: 'DocumentUc_id'},
			],
			autoLoad: false,
			proxy: {
				type: 'ajax',
				method:  "POST",
				url : '/?c=EvnPrescr&m=loadEvnPrescrVaccinationGrid',
				reader: {
					type: 'json',
					rootProperty: 'rows',
				},
				extraParams : {
					Org_id: getGlobalOptions().org_id,
				}
			},
			mode: 'local',
		})

		me.vaccinesDosesGrid = Ext6.create('Ext6.form.FieldSet', {
			title: 'Вакцины и дозы',
			autoHeight: true,
			items: [
				{
					iconCls: 'panicon-add',
					text: 'Добавить',
					xtype: 'button',
					handler: function() {
						me.openVaccinesDosesFormWindow('add');
					},
					margin: '0 0 10 10'
				},
				{
					xtype:'container',
					items:[
						{
							xtype: 'grid',
							xtype: 'grid',
							cls: 'EmkGrid',
							padding: '0 0 0 0',
							maxHeight:200,
							autoLoad: false,
							scrollable: 'y',
							width: 800,
							disableSelection: true,
							columns: [
								{	
									header: "EvnPrescrVaccination_id", 
									dataIndex: 'EvnPrescrVaccination_id',
									hidden: true
								},
								{	
									header: "EvnVaccination_id", 
									dataIndex: 'EvnVaccination_id',
									hidden: true
								},
								{	
									header: "DocumentUcStr_id", 
									dataIndex: 'DocumentUcStr_id',
									hidden: true
								},
								{	
									header: "DocumentUc_id", 
									dataIndex: 'DocumentUc_id',
									hidden: true
								},
								{	
									header: "Вакцина", 
									dataIndex: 'TN_NAME',
									resizable: true, 
									sortable: true,
									width: '20%'
								},
								{
									header: "Количество доз", 
									dataIndex: 'Prep_Dose',
									resizable: true, 
									sortable: true,
									width: '20%'
								},
								{
									header: "Прививки", 
									dataIndex: 'VaccinationType_Name',
									resizable: true, 
									sortable: true,
									bordered: false,
									width: '20%'
								},
								{
									header: "Согласие", 
									dataIndex: 'VaccionationPermission_id',
									renderer: function (value) {
										return value != null ?'<img style="width: 15px;"src="/img/icons/checked16.png" />' : null;
									},
									resizable: true, 
									sortable: true,
									width: '20%'
								},
								{
									width: '10%',
									dataIndex: 'PrescrVaccination_Action',
									renderer: function (value, metaData, record) {
										return (
											"<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.id + "\").showRecordMenu(this, " + record.get('EvnPrescrVaccination_id')+ ", " + record.get('EvnVaccination_id')+ ", " + record.get('VaccionationPermission_id') + ", " + metaData.rowIndex + ");' ></div>"
										)
										
									}
								}
							],
							store: me.vaccinesDosesGridStore,
						}
					],
				}
			]
		})

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '20 20 20 20',
			trackResetOnLoad: false,
			defaults: {
				anchor: '100%',
				labelWidth: labelWidth
			},
			items: [{
				allowBlank: false,
				xtype: 'baseCombobox',
				name: 'MedService_id',
				fieldLabel: 'Кабинет вакцинации',
				editable: false,
				forceSelection: true,
				triggerAction: 'all',
				valueField: 'MedService_id',
				displayField: 'MedService_Nick',
				emptyText: 'Не выбрано',
				store: new Ext6.create('Ext6.data.Store', {
					fields: [
						{name: 'Lpu_id', type: 'int'},
						{name: 'LpuBuilding_id', type: 'int'},
						{name: 'MedService_id', type: 'int'},
						{name: 'MedService_Nick', type: 'string'},
						{name: 'LpuUnit_id', type: 'int'},
						{name: 'LpuSection_id', type: 'int'},
						{name: 'LpuUnitType_id', type: 'int'},
					],
					autoLoad: false,
					proxy: {
						type: 'ajax',
						actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
						url : '/?c=VaccineCtrl&m=getMedServiceVac_allData',
						reader: {
							type: 'json',
							rootProperty: 'rows',
						}
					},
					mode: 'local',
					listeners: {
						load: function(store){
							var rec = { MedService_Nick: 'Не выбрано', MedService_id: null };
							store.insert(0,rec);    
						}
					}
				}),
				listeners: {
					select: function(combo, record, index) {
						var MedService_id = combo.getValue()
						me.MedService_id = MedService_id
						me.DistinctLpuSection_id = record.data.LpuSection_id
						
						me.vaccinesDosesGridStore.load({
							params: {
								MedService_id : MedService_id,
								Evn_id : me.params.Evn_id,
								Person_id : me.params.Person_id,
								Lpu_id : getGlobalOptions().lpu_id,
								PersonEvn_id: me.params.PersonEvn_id,
							}
						});
					}
				}
			},
			me.vaccinesDosesGrid
		]	
		});


		Ext6.apply(me, {
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				userCls: 'disp-menu',
				items: [{
					text: 'Добавить согласие',
					itemId: 'addVaccionationPermission',
					handler: function() {
						var EvnPrescrVaccination_id =me.recordMenu.EvnPrescrVaccination_id,
							EvnVaccination_id = me.recordMenu.EvnVaccination_id;
						me.openVaccinePersonSoglasieWindow('add',EvnPrescrVaccination_id,EvnVaccination_id);
					}
				},{
					text: 'Печать согласия',
					itemId: 'ptintVaccionationPermission',
					handler: function () {
						var data = {
							'EvnPrescrVaccination_id' : me.recordMenu.EvnPrescrVaccination_id,
							'EvnVaccination_id' : me.recordMenu.EvnVaccination_id,
							'VaccionationPermission_id' : me.recordMenu.VaccionationPermission_id
						}
						console.log('Печать согласия')
					}
				},{
					text: 'Удалить согласие',
					itemId: 'deleteVaccionationPermission',
					handler: function () {
						var data = {
							'EvnPrescrVaccination_id' : me.recordMenu.EvnPrescrVaccination_id,
							'EvnVaccination_id' : me.recordMenu.EvnVaccination_id,
							'VaccionationPermission_id' : me.recordMenu.VaccionationPermission_id
						}
						me.deleteVaccionationPermission(data)
					}
				},
				/*
				{
					text: 'Редактировать вакцинацию',
					itemId: 'editEvnVaccination',
					itemId: 'HistEdit',
					handler: function () {
						var EvnPrescrVaccination_id =me.recordMenu.EvnPrescrVaccination_id;
						console.log('Редактировать вакцинацию')
					}
				}
				*/
				,{
					text: 'Удалить вакцинацию',
					itemId: 'deleteEvnVaccination',
					iconCls:'menu_dispdel',
					handler: function () {
						var EvnPrescrVaccination_id =me.recordMenu.EvnPrescrVaccination_id;
						var SelectedVaccinationPrescription_row = me.vaccinesDosesGridStore.find('EvnPrescrVaccination_id', EvnPrescrVaccination_id)
						var SelectedVaccinationPrescription_data =  me.vaccinesDosesGridStore.getAt(SelectedVaccinationPrescription_row).data
						me.deletePrescrVaccionation(SelectedVaccinationPrescription_data)
					}
				}]
			})
		})	

		Ext6.apply(me, {
			items: [
				me.formPanel
			],
			buttons: [
				'->',
				{
					cls: 'buttonCancel',
					text: 'Отмена',
					margin: 0,
					handler: function() {
						me.hide();
					}
				}, {
					cls: 'buttonAccept',
					text: 'Направить в кабинет',
					margin: '0 19 0 0',
					handler: function() {
						var MedService_id = me.MedService_id
						if(MedService_id == null) {
							Ext6.Msg.alert('Ошибка','Не выбран кабинет вакцинации');
						}
						var vaccinesDosesGrid_data = me.vaccinesDosesGridStore.data.items
						var enableDirection = true;
						vaccinesDosesGrid_data.map((val) => {
							// console.log('vaccinesDosesGrid_data val.data', val.data)
							if(val.data.VaccionationPermission_id == null) {
								enableDirection = false
							}
						})
						if(!enableDirection) {
							Ext6.Msg.alert('','Отсутствуют согласия на проведение указанных вакцинаций. Заполните формы согласий.');
						}
						else {
							me.saveVacinationDirection(vaccinesDosesGrid_data, MedService_id);
						}
						
					}
				}
			]
		});

		me.callParent(arguments);
	},

	// вызов формы "вакцины и дозы"
	openVaccinesDosesFormWindow: function(action){
		var me = this;
		if(me.MedService_id !== null) {
			var params = me.params;
			params.MedService_id = me.MedService_id
			params.LpuSection_id = me.DistinctLpuSection_id;
			var win = Ext6.create('common.EMK.Vaccination.swVaccinesDosesWindow', {
			params: params,
			action: action,
			successCallback: function (win) {
				me.loadGrid();
			}
			});
			win.show();
		}
		else {
			Ext6.Msg.alert('Ошибка','Не выбран кабинет вакинации');
		}
	},
	openVaccinePersonSoglasieWindow: function (action,EvnPrescrVaccination_id, EvnVaccination_id) {
		var me = this,
			params = this.params;

		params.EvnPrescrVaccination_id = EvnPrescrVaccination_id;
		params.EvnVaccination_id = EvnVaccination_id;
		var win = Ext6.create('common.EMK.Vaccination.swVaccinePersonSoglasieWindow', {
		  params: params,
		  action: action,
		  successCallback: function (win) {
			me.loadGrid();
		  }
		});
		win.show();
	},
	// направление 
	saveVacinationDirection: function(data, MedService_id){
		var me = this;
		

		var formPanel = me.down(me.formPanel).getForm()
		var MedService_id_FIELD = formPanel.findField('MedService_id');
		var MedService_id_store = MedService_id_FIELD.getStore()
		var MedService_id_selected = MedService_id_store.find('MedService_id', MedService_id)
		var MedService_id_data =  MedService_id_store.getAt(MedService_id_selected).data
		
		
		var vaccinations = data.map(v=>(v.data))
		// console.log('data',vaccinations)
		var params = {
			Lpu_id : getGlobalOptions().lpu_id,
			Server_id : me.params.Server_id,
			Person_id : me.params.Person_id,
			MedPersonal_id: getGlobalOptions().medpersonal_id,
			VacinationDirection: JSON.stringify(vaccinations),
			Lpu_did: MedService_id_data.Lpu_id, // куда направлен
			LpuSection_did: MedService_id_data.LpuSection_id, // куда направлен
			LpuUnit_did: MedService_id_data.LpuUnit_id, // куда направлен
			LpuUnitType_id:  MedService_id_data.LpuUnitType_id, // куда направлен
			MedService_id: MedService_id // куда направлен
		}
		// console.log('saveVacinationDirection params', params)
			
		var loadMask = new Ext6.LoadMask(me, {msg: "Подождите, идет сохранение направления..."});
		loadMask.show();
		Ext6.Ajax.request({
			url: '/?c=EvnPrescr&m=saveVacinationDirection',
			method: 'POST',
			async: true,
			params: params,
			callback: function(options, success, response) {
				if(success){
					// me.loadGrid();
					me.hide();	
					var data = {};
					me.callback(data);		
				}
				else {
					Ext6.Msg.alert('Ошибка при создании направления',response.responseText);
				}
				loadMask.hide()
			}
		});
		
	},
	// удаление согласия
	deleteVaccionationPermission: function(data) {
		var me = this;
		var loadMask = new Ext6.LoadMask(me, {msg: "Подождите, идет удаление согласия..."});
		loadMask.show();
		Ext6.Ajax.request({
			url: '/?c=EvnPrescr&m=deleteEvnPrescrPermission',
			method: 'POST',
			async: true,
			params: data,
			callback: function(options, success, response) {
				if(success){
					me.loadGrid();
				}
				else {
					Ext6.Msg.alert('Ошибка при удалении согласия',response.responseText);
				}
				loadMask.hide()
			}
		});
	},
	// удаление назначения вакцинации
	deletePrescrVaccionation: function(data){
		var me = this;
		var loadMask = new Ext6.LoadMask(me, {msg: "Подождите, идет удаление назначения..."});
		loadMask.show();
		Ext6.Ajax.request({
			url: '/?c=EvnPrescr&m=deleteEvnPrescrVaccination',
			method: 'POST',
			async: true,
			params: data,
			callback: function(options, success, response) {
				if(success){
					me.loadGrid();
				}
				else {
					Ext6.Msg.alert('Ошибка при удалении назначения',response.responseText);
				}
				loadMask.hide()
			}
		});
	}
});