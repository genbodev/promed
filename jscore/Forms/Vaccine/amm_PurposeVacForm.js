/**
* amm_PurposeVacForm - окно просмотра формы Назначение прививки
*
* PromedWeb - The New Generation of Medical Statistic Software
*
* @copyright    Copyright (c) 2012 
* @author       
* @version      15.05.2012
* @comment      
*/

sw.Promed.amm_PurposeVacForm = Ext.extend(sw.Promed.BaseForm, {
	id: 'amm_PurposeVacForm',
	title: "Назначение профилактических прививок",
	border: false,
	width: 800,
	height: 500,
	maximizable: false,  
	closeAction: 'hide',
	//  layout:'fit',
	layout: 'border',
	codeRefresh: true,
	autoScroll: true,
	modal:true,
	planTmpId: null,
	//  vacComboParams: null,
	objectName: 'amm_PurposeVacForm',
	objectSrc: '/jscore/Forms/Vaccine/amm_PurposeVacForm.js',
	onHide: Ext.emptyFn,
	
	initComponent: function() {
		var params = new Object();
		var form = this;
		//объект для контроля дат формы:
		this.validateVacImplementDate = sw.Promed.vac.utils.getValidateObj({
			formId: 'vacPurpEditForm',
			fieldName: 'vacPurposeDate'
		});
		
		/*
		* хранилище для доп сведений
		*/
		this.formStore = new Ext.data.JsonStore({
			fields: ['Plan_id', 'MedPers_id', 'planTmp_id', 'Date_Plan', 'type_name', 'Name',
				'SequenceVac', 'VaccineType_id', 'BirthDay', 'Person_id'],
			url: '/?c=VaccineCtrl&m=loadPurpFormInfo',
			key: 'Plan_id',
			root: 'data'
		});
		
		this.gridConfiSimilarRecords = new gridConfiSimilarRecords();
		
		this.gridSimilarRecords = new Ext.grid.EditorGridPanel({
			id: 'purpose_gridSimilarRecords',
			autoExpandColumn: 'autoexpand',
			//region: 'west',
			//width: 200,
			//height: 100,
			autoHeight: true,
			//autoWidth: true,
			split: true,
			//collapsible: true,
			floatable: false,
			store: form.gridConfiSimilarRecords.store,    // определили хранилище
			title: 'Прививки:', // Заголовок
			colModel: form.gridConfiSimilarRecords.columnModel,
			tabIndex: TABINDEX_VACPRPFRM + 20,
			listeners: {
				'celldblclick': function(grid, rowNum, columnIndex, e) {
					if (columnIndex == 0) return;
					var record = grid.getStore().getAt(rowNum);  // Get the Record
					//record.set('selRow', !record.get('selRow'));

                    if(record.get('selRow') == 'Да')
                        record.set('selRow', 'Нет');
                    else
                        record.set('selRow', 'Да');
					var selRows = [];
					var gridStore = grid.getStore();
					var gridStoreCnt = gridStore.getCount();
					for(var i = 0; i < gridStoreCnt; i++) {
						var rec = gridStore.getAt(i);
                        if (rec.get('selRow')=='Да') {
						//if (rec.get('selRow')) {
//              selRows.push(rec.get('PlanTmp_id'));
							selRows.push(rec.get('PlanFinal_id'));
						}
					}
					grid.store.keyList = selRows;
				},

				'cellclick': function(grid, rowNum, columnIndex, e, noSet) {
					if (columnIndex != 0) return;
					var record = grid.getStore().getAt(rowNum);
//          alert('cellclick');
//          if (isGenerated != 1) {
//            alert('SetRow');
					if (noSet === 1) {
						//record.set('selRow', record.get('selRow'));
                        if(record.get('selRow') == 'Да')
                            record.set('selRow', 'Да');
                        else
                            record.set('selRow', 'Нет');
					} else {
						//record.set('selRow', !record.get('selRow'));
                        if(record.get('selRow') == 'Да')
                            record.set('selRow', 'Нет');
                        else
                            record.set('selRow', 'Да');
					}
//          }

					var selRows = [];
					var gridStore = grid.getStore();
					var gridStoreCnt = gridStore.getCount();
					for(var i = 0; i < gridStoreCnt; i++) {
						var rec = gridStore.getAt(i);
                        if (rec.get('selRow') == 'Да') {
						//if (rec.get('selRow')) {
//              selRows.push(rec.get('PlanTmp_id'));
							selRows.push(rec.get('PlanFinal_id'));
						}
					}
					grid.store.keyList = selRows;
				}
			}
		});
		
		this.PersonInfoPanel  = new sw.Promed.PersonInfoPanel({
			//      id: 'vacPurp_PersonInfoFrame',
			//      plugins: [ Ext.ux.PanelCollapsedTitle ],
			//      region: 'north',
			//      title: 'qqqqqqqqwe',
			titleCollapse: true,
			floatable: false,
			collapsible: true,
			bodyStyle: 'padding: 0px',
			//      titleCollapse: false,
			collapsed: true,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north'
		//,border: false
		//,autoLoad: true
		});
		/*
		this.EvnVizitPLCombo = new sw.Promed.SwBaseLocalCombo({
			fieldLabel: 'Случай',
			displayField: 'EvnPL_NumCard',
			valueField: 'EvnVizitPL_id',
			hiddenName: 'EvnVizitPL_id',
			listWidth: 400,
			width: 275,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'Талон №<b>{EvnPL_NumCard}</b>. Посещение {EvnVizitPL_setDate} {EvnVizitPL_setTime}',
				'</div></tpl>'
			),
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id:'EvnVizitPL_id'
				}, [
					{name: 'EvnPL_id', type: 'int'},
					{name: 'EvnPL_NumCard', type: 'string'},
					{name: 'EvnVizitPL_id', type: 'int'},
					{name: 'EvnVizitPL_setDate', type: 'string'},
					{name: 'EvnVizitPL_setTime', type: 'string'}
				]),
				url:'/?c=EvnVizit&m=loadListOfOpenVisitsToThePatientClinic'
			}),
			refreshDisplay: function() {
				var combo = this;
				var record = combo.getStore().getById(combo.getValue());

				if (record && !Ext.isEmpty(record.get('EvnVizitPL_id'))) {
					// var tpl = new Ext.Template('{EvnPL_NumCard} №{EvnPL_NumCard}');
					var tpl = new Ext.Template('Талон № <b>{EvnPL_NumCard}</b>. Посещение {EvnVizitPL_setDate} {EvnVizitPL_setTime}');
					combo.setRawValue(tpl.apply(record.data));
				}
			}
		});
		*/

		Ext.apply(this, {
			buttons: [
			{
				text: 'Перейти к исполнению',
				tabIndex: TABINDEX_VACPRPFRM + 30,
				hidden: ( getRegionNick().inlist(['perm','penza','krym','astra']) ),
				handler: function() {
					sw.Promed.vac.utils.consoleLog('Перейти к исполнению...');
					
					var vacPurpForm = Ext.getCmp('vacPurpEditForm');
					vacPurpForm.form.findField('MedStaffFact_id').allowBlank = true;
					if (!vacPurpForm.form.isValid()) {
						sw.Promed.vac.utils.msgBoxNoValidForm();
						vacPurpForm.form.findField('MedStaffFact_id').allowBlank = false;
						return false;
					}
					vacPurpForm.form.findField('MedStaffFact_id').allowBlank = false;
					
					var comboVacList = Ext.getCmp('purpose_comboVaccineList');
					
					var idx = vacPurpForm.form.findField('VaccineSeria_id').getStore().findBy(function(rec) { return rec.get('VacPresence_id') == vacPurpForm.form.findField('VaccineSeria_id').getValue(); });
					var seriaRecord = vacPurpForm.form.findField('VaccineSeria_id').getStore().getAt(idx);
					if (typeof(seriaRecord) == 'object') {
						comboVacList.generalParams.vac_seria = seriaRecord.get('Seria');
						comboVacList.generalParams.vac_period = seriaRecord.get('Period');
					} else {
						comboVacList.generalParams.vac_seria = vacPurpForm.form.findField('VaccineSeria_id').getRawValue();
					}
					
					comboVacList.generalParams.vaccine_way_place_id = vacPurpForm.form.findField('VaccineWayPlace_id').getValue();
					comboVacList.generalParams.key_list = Ext.getCmp('purpose_gridSimilarRecords').store.keyList.join(',');

//          alert(Ext.getCmp('gridSimilarRecords').store.keyList.length);
					if (comboVacList.generalParams.key_list.trim() == '') {
//          if (Ext.getCmp('gridSimilarRecords').store.keyList.length == 0) {
						Ext.MessageBox.show({
							title: "Проверка данных формы",
							msg: "Не выбрана прививка!",
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING
						});
						return false;
					}

					var i = vacPurpForm.form.findField('VaccineDoze_id').getStore().findBy(function(rec) { return rec.get('VaccineDose_id') == vacPurpForm.form.findField('VaccineDoze_id').getValue(); });
					var recDoze = vacPurpForm.form.findField('VaccineDoze_id').getStore().getAt(i);
					if (typeof(recDoze) == 'object') {
						comboVacList.generalParams.vac_doze = recDoze.get('VaccineDose_Name');
					}
					
//          vacPurpForm.buildParams(comboVacList.generalParams);
					
					sw.Promed.vac.utils.callVacWindow({
						record: comboVacList.generalParams,
						type1: 'btnForm',
						type2: 'btnGoToImpl'
					}, Ext.getCmp('amm_PurposeVacForm'));
//          }, this);
					
				}.createDelegate(this)
			},
                        {
				text: 'Назначить прививку',
				iconCls: 'save16',
                                id: 'vacPurp__ButtonSave',
				tabIndex: TABINDEX_VACPRPFRM + 31,
				handler: function(b) {
					b.setDisabled(true);//деактивируем кнопку (исключен повторных нажатий)
                                        //return false;
                                        sw.Promed.vac.utils.consoleLog({
						'keyList': Ext.getCmp('purpose_gridSimilarRecords').store.keyList
						});
					sw.Promed.vac.utils.consoleLog({
						'keyList.length': Ext.getCmp('purpose_gridSimilarRecords').store.keyList.length
						});
					if (!Ext.getCmp('purpose_gridSimilarRecords').store.keyList.length) {
						Ext.Msg.alert('Ошибка', 'Не выбрана прививка! Необходимо выбрать прививку!');
						b.setDisabled(false);
                                                return;
					}

					var vacPurpForm = Ext.getCmp('vacPurpEditForm');
					if (!vacPurpForm.form.isValid()) {
						sw.Promed.vac.utils.msgBoxNoValidForm();
						b.setDisabled(false);
                                                return false;
					}
					//          consoleLog('planTmpId='+sw.Promed.amm_PurposeVacForm.planTmpId);
					//          consoleLog('gridSimilarRecords_keyList='+Ext.getCmp('gridSimilarRecords').store.keyList);
					var comboVacList = Ext.getCmp('purpose_comboVaccineList');

					var idx = vacPurpForm.form.findField('VaccineSeria_id').getStore().findBy(function(rec) { return rec.get('VacPresence_id') == vacPurpForm.form.findField('VaccineSeria_id').getValue(); });
					var seriaRecord = vacPurpForm.form.findField('VaccineSeria_id').getStore().getAt(idx);
					if (typeof(seriaRecord) == 'object') {
						comboVacList.generalParams.vac_seria = seriaRecord.get('Seria');
						comboVacList.generalParams.vac_period = seriaRecord.get('Period');
					} else {
						comboVacList.generalParams.vac_seria = vacPurpForm.form.findField('VaccineSeria_id').getRawValue();
//						Ext.getCmp('vacPurpEditForm').getForm().findField('VaccineSeria_id').getRawValue()
					}

					comboVacList.generalParams.key_list = Ext.getCmp('purpose_gridSimilarRecords').store.keyList.join(',');
					comboVacList.generalParams.vaccine_way_place_id = vacPurpForm.form.findField('VaccineWayPlace_id').getValue();
					//          comboVacList.generalParams.doze_id = vacPurpForm.form.findField('VaccineDoze_id').getValue();
					//          Ext.getCmp('purp_VaccineDozeCombo').getStore().findBy(function(rec) { return rec.get('VaccineDose_id') == Ext.getCmp('purp_VaccineDozeCombo').getValue(); });
					var i = vacPurpForm.form.findField('VaccineDoze_id').getStore().findBy(function(rec) { return rec.get('VaccineDose_id') == vacPurpForm.form.findField('VaccineDoze_id').getValue(); });
					//          alert('i='+i);
					var recDoze = vacPurpForm.form.findField('VaccineDoze_id').getStore().getAt(i);
					//consoleLog('typeofrecDoze='+typeof(recDoze));
					//debugger;
					if (typeof(recDoze) == 'object') {
						comboVacList.generalParams.vac_doze = recDoze.get('VaccineDose_Name');//vacPurpForm.form.findField('VaccineDoze_id').getValue();
					}
					//          alert('VaccineDose_Name='+recDoze.get('VaccineDose_Name'));
					comboVacList.generalParams.med_staff_fact_id = vacPurpForm.form.findField('MedStaffFact_id').getValue();
					comboVacList.generalParams.medService_id = Ext.getCmp('purp_ComboMedServiceVac').getValue();
					comboVacList.generalParams.lpu_id = Ext.getCmp('purp_LpuListComboServiceVac').getValue();

					if(getRegionNick().inlist(['perm','penza','krym','astra'])){
						var evnVizitPL_id = vacPurpForm.form.findField('EvnVizitPL_id').getValue();
						if(evnVizitPL_id) comboVacList.generalParams.EvnVizitPL_id = evnVizitPL_id;
						/*
						if(!comboVacList.generalParams.EvnVizitPL_id){
							Ext.Msg.alert('Ошибка', 'Необходимо выбрать случай лечения!');
							return false;
						}
						*/
					}                                        
									
					Ext.Ajax.request({
						url: '/?c=VaccineCtrl&m=savePriviv',
						method: 'POST',
						params:
						//              {
						//'EvnPriviv_setDT': Ext.util.Format.date(Ext.getCmp('EvnPriviv_setDT').getValue(), 'd.m.Y'),
						//'Person_id': params.Person_id
						//                'KeyList': Ext.getCmp('gridSimilarRecords').store.keyList.join(','),
						//                'Date_Purpose': vacPurpForm.form.findField('vacPurposeDate').getValue().format('d.m.Y'),
						//                'VaccineWay_id': vacPurpForm.form.findField('VaccineWay_id').getValue(),
						//                'Doza': vacPurpForm.form.findField('VaccineDoze_id').getValue()
						//            }
						comboVacList.generalParams
						, 
						success: function(response, opts) {
							sw.Promed.vac.utils.consoleLog(response);
//              debugger;
							if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0 && comboVacList.generalParams.parent_id && Ext.getCmp(comboVacList.generalParams.parent_id)) {
								Ext.getCmp(comboVacList.generalParams.parent_id).fireEvent('success', 'amm_PurposeVacForm', {
									keys: Ext.getCmp('purpose_gridSimilarRecords').store.keyList
								});
							}
							form.hide();
							if(Ext.getCmp('amm_PurposeVacForm').formParams.callback && typeof Ext.getCmp('amm_PurposeVacForm').formParams.callback == 'function'){
								Ext.getCmp('amm_PurposeVacForm').formParams.callback(true);
							}
						}
					});
				}
			}, {
				text: '-'
			}, 
                        HelpButton(this, TABINDEX_VACPRPFRM + 32), 
                        {
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
//        id: 'vacPurp_CancelButton',
				onTabAction: function () {
					Ext.getCmp('vacPurpEditForm').form.findField('vacPurposeDate').focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_VACPRPFRM + 33,
				text: '<u>З</u>акрыть'
			}],

			items: [
			this.PersonInfoPanel,
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px',
				border: false,
				frame: false,
				id: 'vacPurpEditForm',
				region: 'center',
				labelAlign: 'right',
					
				autohight: true,
				//height: 100,
				labelWidth: 100,
				layout: 'form',
				items: [
//          this.PersonInfoPanel,
					{
						height:5,
						border: false
					},
					{
						border: false,
						layout: 'column',
						defaults: {
							//xtype: 'form',
							columnWidth: 0.5,
							bodyBorder: false,
							//labelWidth: 100,
							anchor: '100%'
						},
						bodyStyle: 'padding: 5px',
						//height: 100,
						//autohight: true,
						items: [{

							layout: 'form',
							items: [{
								fieldLabel: 'Тип прививки',
								tabIndex: TABINDEX_VACPRPFRM + 10,
								xtype: 'textarea',
								name: 'vacPurposeType',
								grow: true,
								growMax: 60,
								growMin: 20,
								width: 260,
								disabled: true,
								readOnly: true
							//editable: false
							}, {
								fieldLabel: 'Дата назначения прививки',
								tabIndex: TABINDEX_VACPRPFRM + 11,
								allowBlank: false,
								xtype: 'swdatefield',
								format: 'd.m.Y',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								name: 'vacPurposeDate',
								id: 'vacPurposeDate',
								listeners: {
									'change': function(field, newValue, oldValue) {
										var combo = Ext.getCmp('purpose_comboVaccineList');
										combo.generalParams.date_purpose = newValue.format('d.m.Y');

										//Ext.getCmp('VaccineListCombo').store.load({params: {VaccineType_id:'3', BirthDay:'03.05.2011'}});
										combo.store.load({
											params: combo.generalParams
												,callback: function(){
																var flVacIsFinded = 0;
																for (var i=0; i<combo.store.getCount(); i++){
																				if (combo.getValue() != combo.store.getAt(i).get('Vaccine_id')) continue;
																				flVacIsFinded = 1;
																				break;
																}
																if (!flVacIsFinded) combo.reset();
												}
										});
									}
								}
							
								}, 

												sw.Promed.vac.objects.vaccineListCombo({
		//									id: 'purp_VaccineListCombo',
		//									idPrefix: 'vacObjects',
																idPrefix: 'purpose',
																hiddenName: 'Vaccine_id',
																tabIndex: TABINDEX_VACPRPFRM + 12
		//									idGridSimilarRecords: '',
		//									idComboVaccineSeria: '',
		//									idComboVaccineWay: '',
		//									idComboVaccineDoze: ''
						}),
								
//              }, {
//                allowBlank: false,
//                id: 'purp_VaccineListCombo',
//                autoLoad: true,
//                fieldLabel: 'Вакцина',
//                tabIndex: TABINDEX_VACPRPFRM + 12,
//                hiddenName: 'Vaccine_id',
//                width: 260,
//                xtype: 'amm_VaccineListCombo'
//                ,
//                listeners: {
//                  //listeners.select = function( combo, record, index ) {
//                  'select': function( combo, record, index ) {
//                    if ( combo.getValue() ) {
//                      combo.vaccineParams.vaccine_id = combo.getValue();
//                      //consoleLog('combo.vaccineParams.vaccine_id='+combo.vaccineParams.vaccine_id);
//                      sw.Promed.vac.utils.consoleLog(combo.vaccineParams);
//                      Ext.getCmp('gridSimilarRecords').store.load({
//                        params: combo.vaccineParams
//                      });
//                      var comboVacSeria = Ext.getCmp('purp_VaccineSeriaCombo');
//                      Ext.getCmp('purp_VaccineSeriaCombo').store.load({
//                        params: combo.vaccineParams,
//                        callback: function(){
//                          if (comboVacSeria.getStore().getCount() > 0)
//                            if (!!combo.vaccineParams.row_plan_parent) comboVacSeria.setValue(comboVacSeria.getStore().getAt(0).get('VacPresence_id'));
//													else Ext.getCmp('purp_VaccineSeriaCombo').setValue('');
//                        }
//                      });
//                    }
//
//                    var comboVacWay = Ext.getCmp('purp_VaccineWayCombo');
//                    comboVacWay.reset();
//                    comboVacWay.store.load({
//                      params: combo.vaccineParams,
//                      callback: function(){
//                        if (comboVacWay.getStore().getCount() > 0)
//                          if (!!combo.vaccineParams.row_plan_parent) comboVacWay.setValue(comboVacWay.getStore().getAt(0).get('VaccineWayPlace_id'));
//                      }
//                    });
//
//                    var comboVacDoze = Ext.getCmp('purp_VaccineDozeCombo');
//                    comboVacDoze.reset();
//                    comboVacDoze.store.load({
//                      params: combo.vaccineParams,
//                      callback: function(){
//                        if (comboVacDoze.getStore().getCount() > 0)
//                          if (!!combo.vaccineParams.row_plan_parent) comboVacDoze.setValue(comboVacDoze.getStore().getAt(0).get('VaccineDose_id'));
//                      }
//                    });
//                  }
//                }
							 sw.Promed.vac.objects.comboVaccineSeria({
//                 id: 'purpose_comboVaccineSeria',
								 idPrefix: 'purpose',
								 allowBlank: true,
								 tabIndex: TABINDEX_VACPRPFRM + 13
							 }),
							 sw.Promed.vac.objects.comboVaccineWay({
//                id: 'purpose_comboVaccineWay',
								idPrefix: 'purpose',
								allowBlank: true,
								tabIndex: TABINDEX_VACPRPFRM + 14
							 }),
							 sw.Promed.vac.objects.comboVaccineDoze({
//                id: 'purpose_comboVaccineDoze',
								idPrefix: 'purpose',
								tabIndex: TABINDEX_VACPRPFRM + 15
							 })]

						}, {

							layout: 'form',
							items: [{
								autoHeight: true,
								style: 'margin: 5px; padding: 5px;',
								title: 'Назначил врач:',
								xtype: 'fieldset',
								items: [{

									id: 'purp_LpuBuildingCombo',
									//lastQuery: '',
									listWidth: 600,
									linkedElements: [
									'purp_LpuSectionCombo'
									],
									tabIndex: TABINDEX_VACPRPFRM + 21,
									width: 260,
									xtype: 'swlpubuildingglobalcombo'
								}, {
									id: 'purp_LpuSectionCombo',
									linkedElements: [
									'purp_MedPersonalCombo'
									//                  ,'EPLSIF_MedPersonalMidCombo'
									],
									listWidth: 600,
									parentElementId: 'purp_LpuBuildingCombo',
									tabIndex: TABINDEX_VACPRPFRM + 22,
									width: 260,
									xtype: 'swlpusectionglobalcombo'
								}, {
									allowBlank: false,
									hiddenName: 'MedStaffFact_id',
									id: 'purp_MedPersonalCombo',
									parentElementId: 'purp_LpuSectionCombo',
									listWidth: 600,
									tabIndex: TABINDEX_VACPRPFRM + 23,
									width: 260,
									emptyText: VAC_EMPTY_TEXT,
									xtype: 'swmedstafffactglobalcombo'
								}]
							},
							// this.EvnVizitPLCombo,							
							{
								allowBlank: true, //(getRegionNick().inlist(['perm','penza','krym','astra'])) ? false : true,
								hiddenName: 'EvnVizitPL_id',
								id: 'purp_EvnVizitPLCombo',
								hidden: ( !getRegionNick().inlist(['perm','penza','krym','astra']) ),
								listWidth: 400,
								width: 275,
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'EvnVizitPL_id'
									}, [
										{name: 'EvnVizitPL_id', type: 'int'},
										{name: 'EvnPL_NumCard', type: 'string'},
										{name: 'EvnPL_id', type: 'int'},
										{name: 'EvnVizitPL_setDate', type: 'string'},
										{name: 'EvnVizitPL_setTime', type: 'string'},
										{name: 'EvnVizitPL_text', type: 'string'}
									]),
									key: 'EvnVizitPL_id',
									url:'/?c=EvnVizit&m=loadListOfOpenVisitsToThePatientClinic'
								}),
								displayField: 'EvnVizitPL_text',
								valueField: 'EvnVizitPL_id',
								fieldLabel: 'Случай',
								xtype: 'swbaselocalcombo'
							},
							
							//************************
							{

//		layout: 'form',
//              items: [
//                  {

		autoHeight: true,
								style: 'margin: 5px; padding: 5px;',
								title: 'Направляется:',
								xtype: 'fieldset',
								items: [{

									id: 'purp_LpuListComboServiceVac',
									//lastQuery: '',
									listWidth: 600,
									fieldLabel: 'ЛПУ',
//                  linkedElements: [
//                  'purp_buildingComboServiceVac'
//                  ],
									tabIndex: TABINDEX_VACPRPFRM + 24,
									width: 260,
									xtype: 'amm_LpuListComboServiceVac'
									,
									listeners: {
										 'select': function(combo, record, index) {
//                                                              alert (combo.getValue());
//                                                            Ext.getCmp('amm_SprNacCalEditWindow').loadNumSchemeCombo(combo.getValue());
                                                                         Ext.getCmp('purp_buildingComboServiceVac').getStore().load ({
                                                                                 params:{
                                                                                                 Lpu_id: combo.getValue()
                                                                                 }
                                                                                 ,
                                                                                 callback: function() {
                                                                                Ext.getCmp('purp_buildingComboServiceVac').reset();
//                                                 Ext.getCmp('purp_buildingComboServiceVac').setValue
//                                                    (Ext.getCmp('purp_buildingComboServiceVac').store.data.items[0].data.LpuBuilding_Name);
                                                                                                                 //select (0, true);
//                                                                                Ext.getCmp('purp_ComboMedServiceVac').reset();
                                                                                Ext.getCmp('purp_ComboMedServiceVac').getStore().load ({
                                                                                             params:{
                                                                                                             Lpu_id: combo.getValue()
                                                                                             }
                                                                                             ,
                                                                                             callback: function() {
                                                                                                             Ext.getCmp('purp_ComboMedServiceVac').reset();
//                            ;
                                                                                                         }           
                                                                                                 })
                                                                 }           
                                                         })
                                                }.createDelegate(this)
								 }         
								}, {
									id: 'purp_buildingComboServiceVac',
									fieldLabel: 'Подразделение',
//                  linkedElements: [
//                  'purp_LpuListComboServiceVac'
////                  //                  ,'EPLSIF_MedPersonalMidCombo'
//                  ],
									listWidth: 600,
//                  parentElementId: 'purp_LpuListComboServiceVac',
									tabIndex: TABINDEX_VACPRPFRM + 25,
									width: 260,
									 xtype: 'amm_BuildingComboServiceVac',
                                                                         listeners: {
                                                                         'select': function(combo, record, index) {
                                                                             Ext.getCmp('purp_ComboMedServiceVac').getStore().load ({
                                                                                             params:{
                                                                                                             LpuBuilding_id: combo.getValue()
                                                                                             }
                                                                                             ,
                                                                                             callback: function() {
                                                                                                             Ext.getCmp('purp_ComboMedServiceVac').reset();
//                                                 Ext.getCmp('purp_ComboMedServiceVac').setValue
//                                                    (Ext.getCmp('purp_ComboMedServiceVac').store.data.items[0].data.LpuBuilding_Name);
                                                                                                         }           
                                                                                                 })
                                                                                        }.createDelegate(this)
								 }
								},{fieldLabel: 'Служба',
									id: 'purp_ComboMedServiceVac',
//                  parentElementId: 'purp_buildingComboServiceVac',
									listWidth: 600,
									tabIndex: TABINDEX_VACPRPFRM + 26,
									width: 260,
									emptyText: VAC_EMPTY_TEXT,
									xtype: 'amm_ComboMedServiceVac'
//									allowBlank: false,
//                  hiddenName: 'MedStaffFact_id',
									
								}]
							}
							//]
//              }
			
																																		
																																		
							 //*****************                                                     
							 ]
						}]

					},
					this.gridSimilarRecords
				]
			})
			]
		});
		sw.Promed.amm_PurposeVacForm.superclass.initComponent.apply(this, arguments);
	},
	
	openVacPurposeEditWindow: function(action) {
		var current_window = this;
		var params = new Object();
		var vacPurpose_grid = current_window.findById('LTVW_PersonPrivilegeGrid');
		
	},

/*
 * Ф-ция сборки параметров (TODO!!!)
 */
//  buildParams: function(params){
////          var vacPurpForm = Ext.getCmp('vacPurpEditForm');
////          var comboVacList = Ext.getCmp('purp_VaccineListCombo');
//          params.vaccine_way_place_id = this.form.findField('VaccineWayPlace_id').getValue();
//          
//          var i = this.form.findField('VaccineDoze_id').getStore().findBy(function(rec) { return rec.get('VaccineDose_id') == this.form.findField('VaccineDoze_id').getValue(); }.createDelegate(this));
//          var recDoze = this.form.findField('VaccineDoze_id').getStore().getAt(i);
//          if (typeof(recDoze) == 'object') {
//            params.vac_doze = recDoze.get('VaccineDose_Name');
//          }
//          
//          
//    return params;
//  },
		 
	show: function(record) {
//		var record0 = record;
//    debugger;
		sw.Promed.amm_PurposeVacForm.superclass.show.apply(this, arguments);
		
					sw.Promed.vac.utils.consoleLog('SimilarRecords amm_PurposeVacForm show record:');
					sw.Promed.vac.utils.consoleLog(record);
		
		Ext.getCmp('vacPurp__ButtonSave').setDisabled(false);
                var vacPurpForm = Ext.getCmp('vacPurpEditForm');
//    vacPurpForm.form.findField('MedStaffFact_id').allowBlank = false;
		vacPurpForm.getForm().reset();

		// vacPurpForm.form.findField('Vaccine_id').allowBlank = !record.row_plan_parent;
		vacPurpForm.form.findField('vacPurposeDate').allowBlank = !record.row_plan_parent;
//    vacPurpForm.form.findField('VaccineWayPlace_id').allowBlank = !record.row_plan_parent;
		vacPurpForm.form.findField('VaccineDoze_id').allowBlank = !record.row_plan_parent;
		vacPurpForm.form.findField('MedStaffFact_id').allowBlank = !record.row_plan_parent;

		this.formParams = record;
		
		this.formStore.load({
			params: {
				plan_id: record.plan_id,
				user_id: getGlobalOptions().pmuser_id,
				Person_id: record.person_id,
				Vac_Scheme_id: record.vac_scheme_id
			},
			
			callback: function(){
				var vacPurpForm = Ext.getCmp('vacPurpEditForm');
				var formStoreRecord = this.formStore.getAt(0);
				//проверка на повторную попытку назначения прививки:
//        consoleLog('formStoreRecord:');
//        consoleLog(formStoreRecord);
//        consoleLog('Plan_id='+formStoreRecord.get('Plan_id'));
				if (formStoreRecord.get('Plan_id') == undefined) {
					Ext.Msg.alert('Внимание', 'Назначение по выбранной прививке уже было выполнено!');
					this.hide();
					return false;
				}
				var combo = Ext.getCmp('purpose_comboVaccineList');
//        combo.vaccineParams = record;
				combo.generalParams = record;
				
//				alert(formStoreRecord.get('Date_Plan'));
				var arrDatePlan = formStoreRecord.get('Date_Plan').split('.');
				var dt = new Date(arrDatePlan[2],arrDatePlan[1]-1,arrDatePlan[0]);
//				alert((((new Date()) - dt) > 1000*60*60*24*10));
				if (((new Date()) - dt) > 1000*60*60*24*10) 
					this.formParams.date_purpose = formStoreRecord.get('Date_Plan');
				else this.formParams.date_purpose = new Date().format('d.m.Y');
					
//				this.formParams.vac_info = formStoreRecord.get('type_name');
//				this.formParams.vac_info += '\n' + formStoreRecord.get('Name').replace('<br />', '');
//				if (formStoreRecord.get('SequenceVac')) {//если 0, то не пишем (одиночная прививка)
//					this.formParams.vac_info += '\n' + 'Очередность: ' + formStoreRecord.get('SequenceVac');
//				}
				this.formParams.vac_info = formStoreRecord.get('Name').replace('<br />', '');
				this.formParams.vac_type_id = formStoreRecord.get('VaccineType_id');
				this.formParams.birthday = formStoreRecord.get('BirthDay');
				if (formStoreRecord.get('Person_id') != undefined) {
					this.formParams.person_id = formStoreRecord.get('Person_id');
					record.person_id = this.formParams.person_id;
				}
				
				Ext.getCmp('purpose_gridSimilarRecords').store.keyList = [record.plan_id];
//        Ext.getCmp('vacPurpEditForm').form.findField('vacPurposeDate').setValue(record.date_purpose);
				if (!!record.row_plan_parent) {
//          vacPurpForm.form.findField('vacPurposeDate').setValue(new Date);
//					vacPurpForm.form.findField('vacPurposeDate').setValue(formStoreRecord.get('Date_Plan'));
                                       
                                        //sw.Promed.vac.utils.consoleLog('vacPurposeDate = '.this.formParams.date_purpose);
					vacPurpForm.form.findField('vacPurposeDate').setValue(this.formParams.date_purpose);
																						
				}
//        combo.generalParams.date_purpose = (new Date).format('d.m.Y');
				combo.generalParams.date_purpose = this.formParams.date_purpose;
//        alert((new Date).format('d.m.Y'));
//        vacPurpForm.form.findField('vacPurposeType').setValue(record.vac_info);
				vacPurpForm.form.findField('vacPurposeType').setValue(this.formParams.vac_info);
				//params.Person_id = arguments[0].person_id;

				//контроль диапазона дат:
				this.validateVacImplementDate.init(function(o){
//					var dateRangeBegin = sw.Promed.vac.utils.strToDate(o.birthday);
//					var dateRangeEnd = sw.Promed.vac.utils.strToDate(o.birthday);
//					dateRangeBegin.setFullYear(dateRangeBegin.getFullYear() + o.vacAgeBegin);
//					dateRangeEnd.setFullYear(dateRangeEnd.getFullYear() + o.vacAgeEnd);
					var resObj = {};
					if (o.birthday != undefined) resObj.personBirthday = o.birthday;
//					if (o.vacAgeBegin != undefined) resObj.dateRangeBegin = dateRangeBegin;
//					if (o.vacAgeEnd != undefined) resObj.dateRangeEnd = dateRangeEnd;
					return resObj;
				}(this.formParams));
				this.validateVacImplementDate.getMinDate();
				this.validateVacImplementDate.getMaxDate();

				this.PersonInfoPanel.load({
					callback: function() {
						this.PersonInfoPanel.setPersonTitle();
						var Person_deadDT = Ext.getCmp('amm_PurposeVacForm').PersonInfoPanel.getFieldValue('Person_deadDT');                           
								
							if (Person_deadDT != undefined) {
//                  alert('Person_deadDT = ' + Person_deadDT );                          
									vacPurpForm.form.findField('vacPurposeDate').setMaxValue
                                                                                    (Ext.getCmp('amm_PurposeVacForm').PersonInfoPanel.getFieldValue('Person_deadDT'));
                                                                                }
					}.createDelegate(this),
					loadFromDB: true,
					Person_id: record.person_id
					,Server_id: record.Server_id
				});

				combo.reset();
				//Ext.getCmp('VaccineListCombo').store.load({params: {VaccineType_id:'3', BirthDay:'03.05.2011'}});
				combo.store.load({
					params: record,
					callback: function(){
						var comboVac = Ext.getCmp('vacPurpEditForm').form.findField('Vaccine_id');
//            consoleLog('comboVac:');
						if (comboVac.getStore().getCount() > 0) {
//              consoleLog(comboVac.getStore().getAt(0));
							if (!!record.row_plan_parent) 
								comboVac.setValue(comboVac.getStore().getAt(0).get('Vaccine_id'));
							
						}
						comboVac.fireEvent('select', comboVac);
					}
				});

					sw.Promed.vac.utils.consoleLog('SimilarRecords amm_PurposeVacForm load record:');
					sw.Promed.vac.utils.consoleLog(record);
				
				this.gridSimilarRecords.store.load({
//				Ext.getCmp('vacObjects_gridSimilarRecords').store.load({
					params: record
				});

						var comboLpu = vacPurpForm.form.findField('purp_LpuListComboServiceVac');
					comboLpu.reset();    
					/*
					comboLpu.getStore().load({
							callback: function() {
								 comboLpu.setValue (getGlobalOptions().lpu);
								 if (comboLpu.value == comboLpu.lastSelectionText) {
										comboLpu.setValue (null);
								}
							}
					} 
			);
					*/
				var combobuilding = vacPurpForm.form.findField('purp_buildingComboServiceVac');
				combobuilding.reset();      
				combobuilding.getStore().load();
				
				
				var comboMedService = vacPurpForm.form.findField('purp_ComboMedServiceVac');
				comboMedService.reset();      
//        comboMedService.getStore().load();
	
			var comboMedStaff = vacPurpForm.form.findField('MedStaffFact_id');
				vacPurpForm.form.findField('LpuBuilding_id').getStore().load();
				vacPurpForm.form.findField('LpuSection_id').getStore().load({
					params:
					{
						Lpu_id: getGlobalOptions().lpu_id
					}, 
					callback: function() {
						comboMedStaff.getStore().load({
							callback: function() {
								if (!!record.row_plan_parent) {
									comboMedStaff.setValue(formStoreRecord.get('MedPers_id'));
								}
								var LpuBuilding_id = vacPurpForm.form.findField('LpuBuilding_id').getValue();
								if(LpuBuilding_id && record.Evn_pid){
									if(
										!vacPurpForm.form.findField('LpuBuilding_id').findRecord('LpuBuilding_id', LpuBuilding_id) 
										&& vacPurpForm.form.findField('LpuBuilding_id').findRecord('LpuBuilding_id', sw.Promed.MedStaffFactByUser.current.LpuBuilding_id)){
										vacPurpForm.form.findField('LpuBuilding_id').setValue(sw.Promed.MedStaffFactByUser.current.LpuBuilding_id);
									}
								}


								vacPurpForm.form.isValid();
								//***********
								/*
									comboLpu.getStore().load({
													callback: function() {
														 comboLpu.setValue (getGlobalOptions().lpu);
														 if (comboLpu.value == comboLpu.lastSelectionText) {
																comboLpu.setValue (null);
														}
													}
											} 
									);
									*/
									combobuilding.setValue (Ext.getCmp('purp_LpuBuildingCombo').getValue());
										 comboLpu.getStore().load({
																					callback: function() {
																						 comboLpu.setValue (getGlobalOptions().lpu_id);
																						 if (comboLpu.value == comboLpu.lastSelectionText) {
																								comboLpu.setValue (null);
																								}
																							}
																					} 
																			)
									if (combobuilding.value == combobuilding.lastSelectionText) {
										combobuilding.reset();
								}
								
															 comboMedService.getStore().load ({
																						 params:{
																								 LpuBuilding_id: combobuilding.getValue()
																						 }  
																}) 
								//**********
							}
						});
					}
				});

				
	if(getRegionNick().inlist(['perm','penza','krym','astra']) && record.person_id && sw.Promed.MedStaffFactByUser.current.MedStaffFact_id){
		var evnVizitPLCombo = vacPurpForm.findById('purp_EvnVizitPLCombo');	
		evnVizitPLCombo.getStore().load({
			params: {
				Person_id: record.person_id,
				MedStaffFact_id: sw.Promed.MedStaffFactByUser.current.MedStaffFact_id
			},
			callback: function() {
				var rec = '';
				if(this.getCount()>0){
					if(record.Evn_pid){
						rec = evnVizitPLCombo.findRecord('EvnVizitPL_id', record.Evn_pid);
					}
					if(rec){
						evnVizitPLCombo.setValue(rec.get('EvnVizitPL_id'));
					}else{
						evnVizitPLCombo.setValue(this.getAt(0).get('EvnVizitPL_id'));
					}
				}
			}
		});
	}
								 
					



	Ext.getCmp('vacPurpEditForm').form.findField('vacPurposeDate').focus();//фокус на первый элемент формы

			//Ext.getCmp('gridSimilarRecords').startEditing(0, 0);
			}.createDelegate(this)
		});
	}
});

/*
 * класс описания конфигурации таблицы для выбора вакцины
 */
function gridConfiSimilarRecords(){
	var isLoad = false;

	this.store = new Ext.data.JsonStore({
//    fields: ['selRow', 'PlanTmp_id', 'Date_Plan', 'Name', 'type_name'],//, 'SequenceVac'],
		fields: ['selRow', 'PlanView_id', 'PlanFinal_id', 'Date_Plan', 'Name', 'type_name'],//, 'SequenceVac'],
		url: '/?c=VaccineCtrl&m=loadSimilarRecords',
		//key: '',
		//root: 'data'
		root: 'rows',
		keyList: [],
		listeners: {
			'load': function( obj, records, options ) {
//        consoleLog({
//          load:'start event "load"...'
//        });
				var recordsCnt = records.length;
				//consoleLog(records);
				//consoleLog('obj.keyList='+obj.keyList);
				var isSet = 0;
				for(var i = 0; i < recordsCnt; i++) {
//          consoleLog('i='+i);
					for(var j = 0; j < obj.keyList.length; j++) {
//            consoleLog({
//              'j':j
//            });
						var rec = obj.getAt(i);
//            if (rec.get('PlanTmp_id') == obj.keyList[j]) {
						if (rec.get('PlanFinal_id') == obj.keyList[j]) {
//              rec.set('selRow', true);
//              Ext.getCmp('gridSimilarRecords').store.keyList.push(rec.get('PlanTmp_id'));
                            rec.set('selRow', 'Да');
							Ext.getCmp('purpose_gridSimilarRecords').fireEvent('cellclick', Ext.getCmp('purpose_gridSimilarRecords'), i, 0, this);
							isSet = 1;
						}
					}
				}
				if (!isSet && recordsCnt > 0) {
					Ext.getCmp('purpose_gridSimilarRecords').fireEvent('cellclick', Ext.getCmp('purpose_gridSimilarRecords'), 0, 0, this, 1);
				}
			}
		}
	});
	
	this.columnModel = new Ext.grid.ColumnModel({
		columns: [
		{
			header: 'Выбор', 
			width: 55,
			dataIndex: 'selRow'
		},

//    {
//      header: 'PlanTmp_id', 
//      dataIndex: 'PlanTmp_id', 
//      sortable: true
//    },

		{
			header: VAC_TIT_DATE_PLAN, 
			dataIndex: 'Date_Plan', 
			width: 100,
			sortable: true
		},

		{
			header: VAC_TIT_NAME_TYPE_VAC, 
			id: 'autoexpand',
			dataIndex: 'Name', 
			sortable: true, 
			width: 200
		},

		//VAC_TIT_VAC_NAME

		{
			header: VAC_TIT_VACTYPE_NAME, 
			dataIndex: 'type_name', 
			sortable: true
		}

		//VAC_TIT_NAME_TYPE_VAC

//    ,{
//      header: VAC_TIT_SEQUENCE_VAC,
//      dataIndex: 'SequenceVac', 
//      sortable: true
//    }
		]
		});
	this.columnModel.setEditor(
		0,
		new Ext.grid.GridEditor(
			new Ext.form.Checkbox({
				//allowBlank: false
				//,store: store
				})
			)
		);
	/*this.columnModel.setRenderer(0,
		function(value){
			if (value) {
				return '<div class="x-form-check-wrap x-form-check-checked"><div class="x-form-check-wrap-inner" tabindex="0"><img class="x-form-check" src="extjs/resources/images/default/s.gif"></div></div>';
			} else {
				//return '<img class="x-form-check" src="extjs/resources/images/default/s.gif">';
				return '<div class="x-form-check-wrap"><div class="x-form-check-wrap-inner" tabindex="0"><img class="x-form-check" src="extjs/resources/images/default/s.gif"></div></div>';
			}
		}
		);*/
	//Ext.getCmp('gridSimilarRecords').getColumnModel().setRenderer(0, 2)
	this.columnModel.setEditable(0, false); //false - нередактируемое поле, true - редактируемое

	this.load = function() {
		if (isLoad) return; // уже загружено
		this.store.load();
		isLoad = true;
	};
}