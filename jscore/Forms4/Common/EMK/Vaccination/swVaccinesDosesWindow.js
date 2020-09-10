/**
 * swVaccinesDosesFormWindow  - окно формы "Вакцины и дозы"
 */

Ext6.define('common.EMK.Vaccination.swVaccinesDosesWindow', {
    extend: 'base.BaseForm',
	alias: 'widget.swVaccinesDosesWindow',
	autoShow: false,
	cls: 'arm-window-new save-template-window arm-window-new-without-padding',
	title: 'Вакцины и дозы',
	renderTo: main_center_panel.body.dom,
	width: 560,
	modal: true,
    show: function (data) {
		this.params = data
		this.callParent(arguments);
    },
    initComponent: function() {
        var me = this;
		var labelWidth = 140;
		var params = {
			NatCalendar: 1,

		};
		
		// console.log('swVaccinesDosesWindow me.params', me.params)
		// console.log('getGlobalOptions().pmuser_id ', getGlobalOptions().pmuser_id)


		me.vaccineStore = Ext6.create('Ext6.data.Store', {
			autoLoad: true,
			fields: [
				{ name: 'Prep_ID', mapping:'Prep_id', type: 'int' }, // id препарата
				{ name: 'Prep_TN_NAME', mapping:'TN_NAME', type: 'string' }, // название препарата
				{ name: 'Prep_DrugOstatRegistry_Kolvo', mapping:'DrugOstatRegistry_Kolvo', type: 'float' }, 
				{ name: 'Storage_id', mapping:'Storage_id', type: 'float' }, // место хранения вакцины
				{ name: 'Vaccination_isNacCal', mapping:'Vaccination_isNacCal', type: 'string' }, // прививка в нац календаре ?
				{ name: 'Vaccination_isEpidemic', mapping:'Vaccination_isEpidemic', type: 'string' }, // прививка в Эпидпоказаниях ?
				{ name: 'DrugOstatRegistry_id', mapping:'DrugOstatRegistry_id', type: 'float' }, // количество ! упаковок !
				{ name: 'DrugPrepFas_id', mapping:'DrugPrepFas_id', type: 'float' }, // группировочное торговое наименование
				{ name: 'Mol_id', mapping:'Mol_id'}, // материально ответственное лицо

			],
			pageSize: 100,
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=VaccineCtrl&m=getVaccinesDosesVaccine_List',
				reader: {
					type: 'json',
					rootProperty: 'data',
				},
				extraParams : {
					Org_id: getGlobalOptions().org_id,
					NatCalendar: 1,
					Person_id: me.params.Person_id,
					MedService_id: me.params.MedService_id,
					MedPersonal_id:getGlobalOptions().medpersonal_id
				}
			},
			mode: 'local',
		})

        me.formPanel = Ext6.create('Ext6.form.Panel',{
            border: false,
			bodyPadding: '20 20 20 20',
			trackResetOnLoad: false,
			defaults: {
				anchor: '100%',
				labelWidth: 120,
				width: 283,
			},
			items:[
				{
					xtype: 'hiddenfield',
					name: 'Person_id',
					value: me.params.Person_id
				},
				{
					xtype: 'hiddenfield',
					name: 'pmUser_id',
					value: getGlobalOptions().pmuser_id
				},
				{
					xtype: 'hiddenfield',
					name: 'PersonEvn_id',
					value: me.params.PersonEvn_id
				},
				{
					xtype: 'hiddenfield',
					name: 'Server_id',
					value: me.params.Server_id
				},
				{
					xtype: 'hiddenfield',
					name: 'EvnPrescr_pid',
					value: me.params.Evn_id
				},
				{
					xtype: 'hiddenfield',
					name: 'Org_id',
					value: getGlobalOptions().org_id
				},
				{
					xtype: 'hiddenfield',
					name: 'Lpu_id',
					value: getGlobalOptions().lpu_id
				},
				{
					xtype: 'hiddenfield',
					name: 'MedPersonal_id',
					value: getGlobalOptions().medpersonal_id
				},
				{
					xtype: 'hiddenfield',
					name: 'LpuSection_id',
					value: me.params.LpuSection_id
				},
				{
					xtype: 'hiddenfield',
					name: 'Storage_id',
					value: null
				},
				// {
				// 	xtype: 'hiddenfield',
				// 	name: 'DrugOstatRegistry_id',
				// 	value: null
				// },
				{
					xtype: 'checkbox',
					fieldLabel:'Национальный календарь',
					name:'NatCalendar',
					checked: true,
					allowBlank: true,
					listeners:{
						change: function(a, checked){
							params.NatCalendar = checked ? 1 : 0
							me.vaccineStore.load({params:params})
						}
					}
				},
				{
					xtype: 'checkbox',
					fieldLabel:'Эпидпоказания',
					name: 'Vaccination_isEpidemic',
					allowBlank: true,
					listeners:{
						change: function(a, checked){
							params.Vaccination_isEpidemic = checked ? 1 : 0
							me.vaccineStore.load({params:params})
						}
					}
				},
				{
					xtype: 'baseCombobox',
					fieldLabel:'Вакцина',
					allowBlank: false,
					name : 'Prep_ID',
					displayField: 'Prep_TN_NAME',
					codeField: 'Prep_TN_NAME',
					valueField: 'Prep_ID',
					queryMode: 'local',
					anyMatch: true,
					forceSelection: true,
					editable: true,
					tpl: new Ext6.XTemplate( // сборка для выпадающего списка
						'<tpl for="."><div class="selectlpu-combo-item x6-boundlist-item">',
						'<b>{Prep_TN_NAME}</b>&nbsp;',
						'(',
						'Доступно: {Prep_DrugOstatRegistry_Kolvo}&nbsp;',
						')',
						'</div></tpl>'
					),
					displayTpl: new Ext6.XTemplate( // сборка для выпадающего списка
						'<tpl for=".">',
						'{Prep_TN_NAME}' + ' ',
						'(',
						'Доступно: {Prep_DrugOstatRegistry_Kolvo}' + ' ',
						')',
						'</tpl>'
					),
					store:me.vaccineStore,
					listeners:{
						change: function(a, prepId){
							// let DrugOstatRegistry_id = me.vaccineStore.findRecord('Prep_id',prepId).data.DrugOstatRegistry_id
							// console.log('DrugOstatRegistry_id', DrugOstatRegistry_id)

							// var form = me.down('form').getForm();
							// var DrugOstatRegistry_id_FIELD = form.findField('DrugOstatRegistry_id')
							// DrugOstatRegistry_id_FIELD.setValue(DrugOstatRegistry_id)
							var record_prep = me.vaccineStore.findRecord('Prep_id',prepId).data
							let Storage_id = record_prep.Storage_id
							var form = me.down('form').getForm();
							var Storage_id_FIELD = form.findField('Storage_id');
							Storage_id_FIELD.setValue(Storage_id)
						}
					}
				},
				{
					xtype: 'numberfield',
					minValue: 1,
					value:1,
					minValue: 1,
					allowDecimals: false,
					name: 'Prep_Dose',
					allowBlank: false,
					fieldLabel:'Количество доз'
				}
			]
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
				},
				{
					cls: 'buttonAccept',
					text: 'Сохранить',
					margin: '0 19 0 0',
					handler: function () {
						var form = me.down('form').getForm();
						var params = form.getValues()
						params.NatCalendar = form.findField('NatCalendar').value ? 1 : 0;
						params.Vaccination_isEpidemic = form.findField('Vaccination_isEpidemic').value ? 1 : 0;

						// сохранение после проверок и подтверждения пользователем
						const saveVaccinesDoses = (vacinationEnable_inGroup, loadMaskWin, EvnDrug_id) => {
		
							var loadMask = new Ext6.LoadMask(me, {msg: "Подождите, идет сохранение..."});
							loadMask.show();

							let  saveParms = params
							saveParms.vacinationEnable_inGroup = JSON.stringify(vacinationEnable_inGroup)
							params.EvnDrug_id = EvnDrug_id
							Ext6.Ajax.request({
								url: '/?c=EvnPrescr&m=saveEvnPrescrVaccination',
								method: 'POST',
								async: true,
								params: params,
								callback: function(options, success, response) {
									if(success ) {
										loadMaskWin.hide() // закрытие уведомления загрузки в окне подтверждения
										loadMask.hide(); // закрытие уведомления загрузки окна "Вакцины идозы"
										var response_obj = Ext6.util.JSON.decode(response.responseText);

										// если назначение не удалось
										if(!response_obj.success) {
											Ext6.Msg.alert('Ошибка при назначении вакинации',response_obj.Error_Msg);
										}
										// если назначение удалось, закрытие всех окон
										else {
											this.swVaccinesDosesCheckWindow.hide() // закрытие окна подтверждения
											me.close(); // закрытие окна "Вакцины идозы"
											me.successCallback(); // callback для перезагрузки грида в родителе
										}
									}
									else {
										loadMaskWin.hide() // закрытие уведомления загрузки в окне подтверждения
										loadMask.hide() // закрытие уведомления загрузки в окне подтверждения
										Ext6.Msg.alert('Ошибка при назначении вакинации',response.responseText);
										
									}

								}
							})
						}

						if (form.isValid()) {
							// проверки выбранной вакцины
							var loadMask = new Ext6.LoadMask(me, {msg: "Подождите, определяется доступность проведения прививки в день посещения..."});
							loadMask.show();
							Ext6.Ajax.request({
								url: '/?c=VaccineCtrl&m=checkVaccination_AvailableToday',
								method: 'POST',
								async: true,
								params: params,
								callback: function(options, success, response) {
									loadMask.hide();
									if (success) {
										var result = Ext6.util.JSON.decode(response.responseText);
										// если нет доступных вакцинаций, то выводим сообщение со списком
										if ( result.vacinationEnable.length == 0 ) {
											var formDatas={
												title: "Прививка не доступна..",
												disabled_vaccines_all:result.disabled_vaccines_all,
												vacinationEnable_inGroup:false,
												message_text:'Следующие виды прививок, исполняемые данной вакциной, отсутствуют в плане прививок пациента или их исполнение недопустимо в день посещения по нормам проведения: ',
												footer_text:'Добавьте прививки в план или выберете другую вакцину',
											}
											var win = Ext6.create('common.EMK.Vaccination.swVaccinesDosesCheckWindow', {
												params:formDatas
											});
											win.show();
										}
										else {
											var vaccinations = []
											Object.keys(result.vacinationEnable_inGroup).map((v) => {
												vaccinations.push(result.vacinationEnable_inGroup[v])
												// result.vacinationEnable_inGroup[v].map((vv) => {
												// 	vaccinations.push(vv)
												// 	return
												// })
											});
											
											var formDatas = {
												title: null,
												vacinationEnable_inGroup: vaccinations,
												message_text:'В ходе вакцинации будут исполнены прививки: ',
												footer_text: `Продолжить?`
											}

											var win = Ext6.create('common.EMK.Vaccination.swVaccinesDosesCheckWindow', {
												params:formDatas,
												successCallback: function (loadMaskWin) {
													loadMaskWin.hide() //закрытие окна загрузки (отладка)
													var userMedStaffFact = sw.Promed.MedStaffFactByUser.current;
													// console.log('params ', params)
													//окно строка документа
													var record_prep = me.vaccineStore.findRecord('Prep_id',params.Prep_ID).data,
													
													 	docUc_params = {
															PersonEvn_id: params.PersonEvn_id,
															Person_id: params.Person_id,
															Server_id: params.Server_id,
															EvnDrug_KolvoEd: params.Prep_Dose,
															Mol_id:record_prep.Mol_id, // материально ответственный
															LpuSection_id: params.LpuSection_id,
															Storage_id: params.Storage_id,
															DrugPrepFas_id: record_prep.DrugPrepFas_id,
															Drug_id:params.Prep_ID
													}
													// создание строки документ учета и сохранение
													me.showEvnDrugEditWindow(docUc_params, saveVaccinesDoses, vaccinations, loadMaskWin)
												}
											});
											
											this.swVaccinesDosesCheckWindow = win
											win.show();
										}
										

									}
								}
							});

						}
						else{
							Ext6.Msg.show({
							buttons: Ext6.Msg.OK,
							fn: function() {
											console.log('form submitted and has error', form)
								// form.getFirstInvalidEl()
							},
							icon: Ext6.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
							});
						}

					}
				}
			]
		});

		me.callParent(arguments);
	},
	showEvnDrugEditWindow: function (formParams, saveVaccinesDoses, vaccinations, loadMaskWin) {

		var win = this,
			Evn_setDate = Ext6.Date.format(new Date(), 'd.m.Y'),
			wnd = getWnd(getEvnDrugEditWindowName()),
			optionVac = getGlobalOptions(),
			userMedStaffFact = sw.Promed.MedStaffFactByUser.current;
		wnd.show({
			action: 'add',
			onHide: Ext.emptyFn,
			callback: function (data) {
				var EvnDrug_id = data.evnDrugData.EvnDrug_id;
				// сохранение с резервироваием
				saveVaccinesDoses(vaccinations, loadMaskWin, EvnDrug_id)
			},
			Person_id: formParams.Person_id,
			parentEvnComboData: [{
				Evn_id: 0,
				Evn_Name: Evn_setDate + ' / ' + userMedStaffFact.LpuSection_Name + ' / ' + userMedStaffFact.MedPersonal_FIO,
				Evn_setDate: Evn_setDate,
				MedStaffFact_id: userMedStaffFact.MedStaffFact_id,
				Lpu_id: userMedStaffFact.Lpu_id,
				LpuSection_id: formParams.LpuSection_id,
				MedPersonal_id: userMedStaffFact.MedPersonal_id
			}],
			formParams: formParams
		})

	},
 });