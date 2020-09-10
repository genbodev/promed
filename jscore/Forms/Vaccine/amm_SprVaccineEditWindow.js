/**
 * amm_SprVaccineEditWindow - окно просмотра и редактирования справочника вакцин.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		 VAC
 * @access		 public
 * @copyright	 Copyright (c) 2011 Swan Ltd.
 * @author		 Nigmatullin Tagir (Ufa)
 * @version		 11.05.2011
 */

var formsParams_OnGripp;
var formsParams_Vaccine_id;

sw.Promed.amm_SprVaccineEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'amm_SprVaccineEditWindow',
	title: "Справочник вакцин: Редактирование",
	titleBase: "Справочник вакцин: Добавление",
	codeRefresh: true, 
	width: 600,
	//height: 735,
	//autoHeight: true,
	// autohight: true,
	maximizable: true,
	modal: true,
	layout: 'border',
	border: false,
	closeAction: 'hide',
	objectName: 'amm_SprVaccineEditWindow',
	objectSrc: '/jscore/Forms/Vaccine/amm_SprVaccineEditWindow.js',
	onHide: Ext.emptyFn,
	buttons: [{
		text: BTN_FRMSAVE,
		iconCls: 'save16',
		tabIndex: TABINDEX_PRESVACEDITFRM + 31,
//		id: 'VacPresence_SaveButton',
		handler: function() {
//			alert('SaveButton');
			var EditWin = Ext.getCmp('amm_SprVaccineEditWindow');
			var EditForm = Ext.getCmp('sprVaccineEditFormPanel');
			if (!EditForm.form.isValid())  {
				sw.Promed.vac.utils.msgBoxNoValidForm();
				return false;
			}
			else if ((Ext.getCmp('uniVac').items.item(0).name == 'addInfectCombo') 
				& (Ext.getCmp('uniVac').items.length == 1)
				& Ext.getCmp('uniVac').isVisible()
			) {
				Ext.MessageBox.show({
					title: "Проверка данных формы",
					msg: "Не выбрана инфекция!",
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING

				  });
				return false;
			}
//			return false;

//			var vacFormPanel = Ext.getCmp('vacPurpEditForm');
			var vaccineParamsUpd = new Object();
			vaccineParamsUpd.Vaccine_Name = EditForm.form.findField('Vaccine_Name').getValue();
			vaccineParamsUpd.Vaccine_Nick = EditForm.form.findField('Vaccine_Nick').getValue();
			vaccineParamsUpd.Vaccine_id = EditWin.formParams.Vaccine_id;
			vaccineParamsUpd.NoAgeRange = EditForm.form.findField('VacEf_AgeRangeCheck').getValue();
			if (!vaccineParamsUpd.NoAgeRange) {
				vaccineParamsUpd.AgeRange1 = EditForm.form.findField('VacEf_AreaRange1').getValue();
				vaccineParamsUpd.AgeRange2 = EditForm.form.findField('VacEf_AreaRange2').getValue();
			}
			vaccineParamsUpd.TypeInfections = '';
			var uniVac = Ext.getCmp('uniVac');
			sw.Promed.vac.utils.consoleLog('Step 3');
			for (var i in uniVac.objElems) {
				if (uniVac.objElems.hasOwnProperty(i)) {
					sw.Promed.vac.utils.consoleLog(i);
					if (vaccineParamsUpd.TypeInfections != '')
						vaccineParamsUpd.TypeInfections += ',';
//				vaccineParamsUpd.TypeInfections = vaccineParamsUpd.TypeInfections + ',' + i;
					vaccineParamsUpd.TypeInfections += i;
					sw.Promed.vac.utils.consoleLog(vaccineParamsUpd.TypeInfections);
				}
			}
			vaccineParamsUpd.AgeRange_DozaCheck = EditForm.form.findField('VacEf_DozaCheck').getValue();
			if (vaccineParamsUpd.AgeRange_DozaCheck) {
				vaccineParamsUpd.DozaAge = EditForm.form.findField('VacEf_DozaAge').getValue();
				vaccineParamsUpd.DozaVal1 = EditForm.form.findField('VacEf_DozaKOl').getValue();
				vaccineParamsUpd.DozaVal2 = EditForm.form.findField('VacEf_DozaKOl2').getValue();
				vaccineParamsUpd.DozeType1 = EditForm.form.findField('amm_DozeTypeCombo').getValue();
				vaccineParamsUpd.DozeType2 = EditForm.form.findField('amm_DozeTypeCombo2').getValue();
			} else {
				vaccineParamsUpd.DozaAge = 1000;
				vaccineParamsUpd.DozaVal1 = EditForm.form.findField('VacEf_DozaKOl').getValue();
				vaccineParamsUpd.DozeType1 = EditForm.form.findField('amm_DozeTypeCombo').getValue();
			}

			// debugger;
			vaccineParamsUpd.AgeRange_SposobCheck = EditForm.form.findField('Ch_AgeSposob').getValue();
			if (vaccineParamsUpd.AgeRange_SposobCheck) {
				vaccineParamsUpd.WayAge = EditForm.form.findField('VacEf_WayAge1').getValue();
				vaccineParamsUpd.placeType1 = EditForm.form.findField('VacEf_VaccinePlace1').getValue();
				vaccineParamsUpd.placeType2 = EditForm.form.findField('VacEf_VaccinePlace2').getValue();
				vaccineParamsUpd.wayType1 = EditForm.form.findField('VacEf_VaccineWay1').getValue();
				vaccineParamsUpd.wayType2 = EditForm.form.findField('VacEf_VaccineWay2').getValue();
//				alert('1');
			} else {
				vaccineParamsUpd.WayAge = 10000;
				vaccineParamsUpd.placeType1 = EditForm.form.findField('VacEf_VaccinePlace1').getValue();
				vaccineParamsUpd.wayType1 = EditForm.form.findField('VacEf_VaccineWay1').getValue();
//			   	alert('2');
//			   	alert(vaccineParamsUpd.placeType1);
			}
//			this.formParams.placeType1 = formStoreRecord.get('PlaceType1');
//			this.formParams.placeType2 = formStoreRecord.get('PlaceType2');
//			this.formParams.wayType1 = formStoreRecord.get('WayType1');
//			this.formParams.wayType2 = formStoreRecord.get('WayType2');

//			alert(vaccineParamsUpd.Vaccine_id);
//			.getGrid().getSelectionModel().getSelected();
//			vaccineParamsUpd.Seria = Ext.getCmp('VacPresence_Seria').getValue();
//			vaccineParamsUpd.Period = Ext.getCmp('VacPresence_Period').getValue().format('Y-m-d');
//			vaccineParamsUpd.Manufacturer =EditForm.form.findField('VacEf_DozaKOl').getValue();
//			if (Ext.getCmp('VacPresence_toHave').getValue())
//				vaccineParamsUpd.toHave = 1;
//			else
//				vaccineParamsUpd.toHave = 0;
//			vaccineParamsUpd.pmUser_id = getGlobalOptions().pmuser_id;

//			alert (vaccineParamsUpd.DozaVal2);
//			exit;	
			Ext.Ajax.request({
				url: '/?c=VaccineCtrl&m=saveSprVaccine',
				method: 'POST',
				params: vaccineParamsUpd,
				success: function(response, opts) {
//					if (vaccineParamsUpd.action == 'add'  ){
//					sw.Promed.vac.utils.consoleLog(response.responseText.rows);
					if (response.responseText.length > 0) {
						var result = Ext.util.JSON.decode(response.responseText);
						sw.Promed.vac.utils.consoleLog(result.rows[0]);
//						sw.Promed.vac.utils.consoleLog(result.rows[0].NewVacPresence_id);
//						Ext.getCmp('amm_PresenceVacForm').fireEvent('success', result.rows[0].NewVacPresence_id);
//						if (!result.success) {
//						  	sw.Promed.vac.utils.consoleLog(result.rows.ddd.NewVacPresence_id);
//							alert(result.rows.ddd.NewVacPresence_id);
//						}
					}
//					}
//					else Ext.getCmp('amm_PresenceVacForm').fireEvent('success', vaccineParamsUpd.VacPresence_id);
					if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
						//sw.Promed.vac.utils.consoleLog('this:');
						//sw.Promed.vac.utils.consoleLog(this);
						Ext.getCmp(Ext.getCmp('amm_SprVaccineEditWindow').formParams.parent_id).fireEvent('success', 'amm_SprVaccineEditWindow', {});
					}
					EditWin.hide();
				}.createDelegate(this)
			});
		} //.createDelegate(this)
	}, {
		text: '-'
	},
//	HelpButton(this, -1)
	{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event)
		{
			ShowHelp(this.ownerCt.titleBase);
		}
	}, {
		text: BTN_FRMCLOSE,
		tabIndex: -1,
		tooltip: 'Закрыть окно',
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
			//Ext.getCmp('amm_SprVaccineEditWindow').refresh();
		}
	}],
//	action: null,
//	buttonAlign: 'left',
//	callback: Ext.emptyFn,
//	closable: true,
//	collapsible: false,
//	formStatus: 'edit',

	refresh: function() {
		sw.codeInfo.lastObjectName = this.objectName;
		sw.codeInfo.lastObjectClass = this.objectClass;
		if (sw.Promed.Actions.loadLastObjectCode)
		{
			sw.Promed.Actions.loadLastObjectCode.setHidden(false);
			sw.Promed.Actions.loadLastObjectCode.setText('Обновить '+this.objectName+' ...');
		}
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.close();
		window[this.objectName] = null;
		//delete sw.Promed[this.objectName];
	},
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'Diag_did'
		);
		var i = 0;

		for (i = 0; i < form_fields.length; i++) {
			if (enable) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if (enable) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	initComponent: function() {
		/*
		 * хранилище для доп сведений
		 */
		this.formStore = new Ext.data.JsonStore({
			fields: [
				'Vaccine_Name'
				,'Vaccine_SignComb'
				,'Vaccine_Nick'
				,'Vaccine_FullName'
				,'Vaccine_NameInfection'
				,'Vaccine_AgeRange2Sim'
				,'Vaccine_WayPlace'
				,'Vaccine_doseAge'
				,'Vaccine_dose'
				,'VacTypeIds'
				,'Vaccine_AgeBegin'
				,'Vaccine_AgeEnd'
				,'DoseVal1'
				,'DoseType1'
				,'WayType1'
				,'PlaceType1'
				,'DoseVal2'
				,'DoseType2'
				,'WayType2'
				,'PlaceType2'
				,'Vaccine_WayPlaceAge'
				,'OnGripp'
			],
			url: '/?c=VaccineCtrl&m=loadSprVacFormInfo',
			key: 'xxx_id',
			root: 'data'
		});
		
		this.ViewFrameOtherVacScheme = new sw.Promed.ViewFrame({
			id: 'ViewFrameOtherVacScheme',
			dataUrl: '/?c=VaccineCtrl&m=GetOtherVacScheme',
			region: 'center',
			//width: 500,
			height: 140, 
			autowith: true,
			//autoHeight: true,
			//maximizable: true,
			toolbar: true,
			//setReadOnly: false,
			autoLoadData: false,
			Edit_SprOtherVacScheme: function() {
				var params = new Object();
				var formParams = Ext.getCmp('amm_SprVaccineEditWindow').formParams;
				if (formsParams_Vaccine_id != undefined) {
					params.Vaccine_id = formsParams_Vaccine_id;
					params.Vaccine_Name = formParams.Vaccine_Name;
					params.Vaccine_Nick = formParams.Vaccine_Nick;		  
					getWnd('amm_SprOtherVacSchemeEditFotm').show(params);
//				   	this.formParams.Vaccine_Name = formStoreRecord.get('Vaccine_Name');
//				  	this.formParams.Vaccine_Nick = formStoreRecord.get('Vaccine_Nick');
				} 
			},
			//root: 'data',
			//cls: 'txtwrap',
			stringfields: [								
				{name: 'OtherVacAgeBorders_id', type: 'int', header: 'OtherVacAgeBorders_id', key: true},
				{name: 'Vaccine_id', type: 'int',header: 'id Vaccine_id',hidden: true},
				{name: 'Sort_id', type: 'int',header: 'Sort_id',hidden: true},
				{name: 'Vaccine_AgeRange2Sim', type: 'string', header: 'Возраст', width: 160},
				{name: 'multiplicity_Name', type: 'string', header: 'Кратность', width: 110},
				{name: 'interval', type: 'string', header: 'Интервал', width: 100},
				{name: 'GroupRisk_Name', type: 'string', header: 'Примечание', width: 130}
			],
			listeners: {
				'success': function(source, params) {
					this.ViewGridPanel.getStore().reload();
				}
			},
			actions:[
				{name:'action_print',  hidden: true},
				{name:'action_view',  hidden: true},
				{name:'action_delete',	hidden: true},
				{name:'action_add',	 
					handler: function() {
						Ext.getCmp('ViewFrameOtherVacScheme').Edit_SprOtherVacScheme();
						//Ext.getCmp('amm_SprVaccineEditWindow').action_edit}
					}
				},
				{name:'action_edit',
//					hidden: true
					handler: function() {
						Ext.getCmp('ViewFrameOtherVacScheme').Edit_SprOtherVacScheme();
//						//var params = new object();
//						var params = new Object();
//						//alert (formsParams_Vaccine_id);
//						if (formsParams_Vaccine_id != undefined) {
//							params.Vaccine_id = formsParams_Vaccine_id;
//							getWnd('amm_SprOtherVacSchemeEditFotm').show(params);
//						} 
					}
				}
			]
		});
		
		var vEdiit = false;
		//				   var AgeRange,
		this.FormPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px',
			border: true,
			frame: true,
			autoScroll: true,
			id: 'sprVaccineEditFormPanel',
			autohight: true,
			region: 'center',
			autoHeight: true,
			//labelWidth: 500,
			//layout: 'column',
			items: [{
				disabled: vEdiit,
				fieldLabel: 'Наименование вакцины',
//				id: 'GRID_NAME_VAC',
				name: 'Vaccine_Name',
				width: 400,
				autoHeight: false,
				height: 40,
				xtype: 'textarea',
				allowBlank: false,
				validator: function(value) {
					if (value.length > 200)
						return 'Превышена максимальная длина поля (200 символов)';
					else
						return true;
				}
			},
//			{
////				layout: 'hbox',
//		  id: 'containerBox',
//				xtype: 'container',
//				items: [
//				]
//			},
//			{text: 'ЯркостьXXX:',	xtype: 'label'},
			{
				fieldLabel: 'Краткое наименование',
				name: 'Vaccine_Nick',
				allowBlank: false,
				width: 400,
				//labelWidth: 300,
				xtype: 'textfield',
				validator: function(value) {
					if (value.length > 20)
						return 'Превышена максимальная длина поля (20 символов)';
					else
						return true;
				}
			}, {
				autoScroll: true,
				style: 'padding: 0px 5px;',
				title: 'Универсальность вакцины',
				//height: 112,
				 autoHeight: true,
				id: 'uniVac',
				xtype: 'fieldset',
				newElemNum: 0,
				objElems: {},
				////удаление всех ранее выбранных типов инфекций с формы:
				resetTypeInfections: function(infection) {
//					for (var i = 2; i < Ext.getCmp('uniVac').items.items.length; i++) {
					Ext.getCmp('uniVac').items.each(function(item) {
						if (item.name != 'addInfectCombo')
							//item.destroy();
							item.getEl().parent().parent().parent().remove();
						
					});
//					for (var i = 1; i < Ext.getCmp('uniVac').items.items.length; i++) {
//						Ext.getCmp('uniVac').items.items[i].destroy();
//					}
					Ext.getCmp('uniVac').objElems = {};
					
//					this.FormPanel.getForm().findField('VaccineType_List').setValue('');
				},
//				addTypeInfection: function(valId, valTxt){
				addTypeInfection: function(infection) {
					var parentObj = this;
					if ((parentObj.objElems[infection.id] == undefined) && (infection.id != '')) {
						parentObj.items.add('uniVacItem' + infection.id, new Ext.form.TriggerField({
							//'elemNum': parentObj.newElemNum,
							//id: 'elemNum' + parentObj.newElemNum,
							'elemNum': infection.id,
							id: 'elemNum' + infection.id,
							'triggerClass': 'x-form-clear-trigger',
							'hideLabel': true,
							'readOnly': true,
							'listWidth': 200,
							'width': 250,
							//value: Ext.getCmp('uniVac').find('name', 'ammTypeInfectioncombo').value,
							//value: Ext.getCmp('uniVac').find('name', 'ammTypeInfectioncombo')[0].lastSelectionText,
							//value: valTxt,
							value: infection.text,
							'onTriggerClick': function(e) {
								//	Удаление элемента
//									alert('onTriggerClick');
								sw.Promed.vac.utils.consoleLog(this);
//									alert(this.elemNum);
								//								this.destroy();
								//Ext.getCmp('elemNum1').getEl().parent().parent().parent().remove();
//									for(var i in parentObj.objElems) {
//										if (parentObj.objElems.hasOwnProperty(i)) {
//											sw.Promed.vac.utils.consoleLog(i);
//											
//										}
//									}
								this.getEl().parent().parent().parent().remove();
								delete Ext.getCmp('uniVac').objElems[this.elemNum];
								//alert('1');
								Ext.getCmp('amm_SprVaccineEditWindow').syncShadow();//перерисовка тени под изменившееся окно
//									Ext.getCmp('uniVac').objElems

								//								this.remove(this, true);
								//							Ext.getCmp('uniVac').find('elemNum', this.elemNum)
							}
						}));
//						parentObj.objElems[infection.id] = true;
						parentObj.objElems[infection.id] = infection.text;
//						this.FormPanel.getForm().findField('VaccineType_List').setValue(infection.text);
					}
				},
				items: [{
					layout: 'column',
					name: 'addInfectCombo',
					items: [{
						//		  columnWidth: .25,
						//					labelWidth: 10,
						layout: 'form',
						items: [{
								fieldLabel: 'Выбор инфекции',
								id: 'ammTypeInfectioncombo',
//							autoLoad: true,
								name: 'ammTypeInfectioncombo',
								listWidth: 350,
								labelSeparator: '',
								width: 350,
								editable: false,
								//					hidden: true,
								xtype: 'ammTypeInfectioncombo'
							}]
					}, {
						border: false,
						items: {
							text: '+',
							//					iconCls: 'add16',
							handler: function(e) {
								sw.Promed.vac.utils.consoleLog('e:');
								sw.Promed.vac.utils.consoleLog(e);
								sw.Promed.vac.utils.consoleLog('this:');
								sw.Promed.vac.utils.consoleLog(this);
								var parentObj = Ext.getCmp('uniVac');
								var infectionObj = {};
								infectionObj.id = parentObj.find('name', 'ammTypeInfectioncombo')[0].value;
								infectionObj.text = Ext.getCmp('uniVac').find('name', 'ammTypeInfectioncombo')[0].lastSelectionText;
								parentObj.addTypeInfection(infectionObj);
								sw.Promed.vac.utils.consoleLog('parentObj.objElems:');
								sw.Promed.vac.utils.consoleLog(Object.keys(parentObj.objElems));
								parentObj.newElemNum += 1;
								//						Ext.getCmp('uniVac').items.add('uniVacItem1',new Ext.form.TextField({text: 'ЯрWость'}));
								//						Ext.getCmp('uniVac').items.add('uniVacItem2',new Ext.Button({text: 'x'}));
								//			  Ext.getCmp('uniVac').doLayout();
								parentObj.doLayout();
							}.createDelegate(this),
							xtype: 'button',
							tabIndex: TABINDEX_VACMAINFRM + 33
						}
					}]
				}]
			},
//						  {
//							  style: 'padding: 0px 5px;',
//							  disabled: true,
//				fieldLabel: '',
//				name: 'VaccineType_List',
//								  id: 'VaccineType_List',
//								  value: 'test', 
//				width: 500,
//								  height: 20,
//								   maximizable: true, 
////								labelWidth: 10, 
//								  'hideLabel': true,
////								labelSeparator: '',
//								  autowith: true,
//				xtype: 'textarea'	
//								  
//								  //  this.FormPanel.getForm().findField('Vaccine_Nick').setValue(this.formParams.Vaccine_Nick);
//							},
				{
					autoHeight: true,
					autoScroll: true,
					style: 'padding: 0px 5px;',
					title: 'Возрастной диапазон применения',
					//														  width: 755,
					height: 80,
					labelWidth: 30,
					xtype: 'fieldset',
					id: 'autoexpand',
					items: [{
						xtype: 'checkbox',
						height: 24,
						//																  tabIndex: 2422,
						tabIndex: TABINDEX_EPLSIF + 1,
						name: 'VacEf_AgeRangeCheck',
						id: 'VacEf_AgeRangeCheck',
						checked: vEdiit,
						boxLabel: 'Без ограничения возраста',
						labelSeparator: '',
						listeners: {
							'check': function(checkbox, checked) {
								var base_form = this.FormPanel.getForm();
								base_form.findField('VacEf_AreaRange1').setContainerVisible(!checked);
								base_form.findField('VacEf_AreaRange2').setContainerVisible(!checked);
								base_form.findField('VacEf_AreaRange1').allowBlank = checked;
								base_form.findField('VacEf_AreaRange1').isValid();
								base_form.findField('VacEf_AreaRange2').allowBlank = checked;
								base_form.findField('VacEf_AreaRange2').isValid();
								Ext.getCmp('amm_SprVaccineEditWindow').syncShadow();//перерисовка тени под изменившееся окно
							}.createDelegate(this)
						}
					}, {
						border: false,
						layout: 'column',
						labelWidth: 50,
						//																  id: 'ParamAge',
						//																  name: 'ParamAge',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'от ',
								layout: 'form',
								id: 'VacEf_AreaRange1',
								name: 'VacEf_AreaRange1',
								width: 30,
								labelSeparator: '',
								allowBlank: false,
								//																	   tabIndex: TABINDEX_EMHPEF + 7,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								fieldLabel: 'лет   до',
								layout: 'form',
								id: 'VacEf_AreaRange2',
								name: 'VacEf_AreaRange2',
								width: 30,
								labelSeparator: '',
								allowBlank: false,
								//																		  tabIndex: TABINDEX_EMHPEF + 8,
								xtype: 'textfield'
							}]
						}]
					}]
				}, {
					autoHeight: true,
					autoScroll: true,
					style: 'padding: 0px 5px;',
					title: 'Дозировка',
					//														  width: 755,
					height: 100,
					labelWidth: 30,
					xtype: 'fieldset',
					items: [{
						xtype: 'checkbox',
						height: 24,
						tabIndex: TABINDEX_EPLSIF + 1,
						name: 'VacEf_DozaCheck',
						id: 'VacEf_DozaCheck',
						checked: true,
						labelSeparator: '',
						boxLabel: 'Зависит от возраста пациента',
						listeners: {
							'check': function(checkbox, checked) {
								var base_form = this.FormPanel.getForm();
								base_form.findField('VacEf_DozaAge').setContainerVisible(checked);
								base_form.findField('VacEf_DozaTypePeriod').setContainerVisible(checked);
								base_form.findField('VacEf_DozaKOl2').setContainerVisible(checked);
								base_form.findField('amm_DozeTypeCombo2').setContainerVisible(checked);
								base_form.findField('VacEf_DozaAge_Tmp').setContainerVisible(checked);
								base_form.findField('VacEf_DozaTypePeriod_Tmp').setContainerVisible(checked);
								base_form.findField('VacEf_DozaAge').allowBlank = !checked;
								base_form.findField('VacEf_DozaAge').isValid();
								//base_form.findField('VacEf_DozaKOl').allowBlank = !checked;
								//base_form.findField('VacEf_DozaKOl').isValid();
								base_form.findField('VacEf_DozaAge_Tmp').allowBlank = !checked;
								base_form.findField('VacEf_DozaAge_Tmp').isValid();
								base_form.findField('VacEf_DozaKOl2').allowBlank = !checked;
								base_form.findField('VacEf_DozaKOl2').isValid();
								base_form.findField('amm_DozeTypeCombo2').allowBlank = !checked;
								base_form.findField('amm_DozeTypeCombo2').isValid();
								Ext.getCmp('amm_SprVaccineEditWindow').syncShadow();//перерисовка тени под изменившееся окно
							}.createDelegate(this)
						}
					}, {
						border: false,
						layout: 'column',
						id: 'ParamAge',
						name: 'ParamAge',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 30,
							//																	xtype: 'fieldset',
							items: [{
									fieldLabel: 'до',
									layout: 'form',
									id: 'VacEf_DozaAge',
									name: 'VacEf_DozaAge',
									width: 30,
									labelSeparator: '  ',
									//																	   tabIndex: TABINDEX_EMHPEF + 7,
									xtype: 'textfield',
									listeners: {
										'change': function(field, newValue, oldValue) {
											var base_form = this.FormPanel.getForm();
											base_form.findField('VacEf_DozaAge_Tmp').setValue(newValue);
											//																						 sw.swMsg.alert('Сообщение', newValue + ', ' +	oldValue );//, function() { this.hide(); }.createDelegate(this) );
										}.createDelegate(this)
									}
								}]
						}, {
							labelWidth: 10,
							layout: 'form',
							items: [{
//								autoLoad: true,
//								name: 'VacEf_DozaTypePeriod',
//								id: 'VacEf_DozaTypePeriod',
//								listWidth: 100,
//								labelSeparator: '',
//								width: 100,
//								valueField: 'TipPeriod_id',
//								displayField: 'TipPeriod_name',
//								editable: false,
//								//																		   mode: 'local',
//								xtype: 'ammTypePeriodcombo',
//								listeners: {
//									'change': function(combo, newValue, oldValue){
//										var base_form = this.FormPanel.getForm();
//									base_form.findField('VacEf_DozaTypePeriod_Tmp').setValue(combo.getRawValue() );
//									//																									   sw.swMsg.alert('Сообщение', combo.getRawValue() );
//									}.createDelegate(this)
//								}
								fieldLabel: '',
								layout: 'form',
								id: 'VacEf_DozaTypePeriod',
								name: 'VacEf_DozaTypePeriod',
								width: 50,
								labelSeparator: '',
								readOnly: true,
								value: ' лет ',
								xtype: 'textfield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 70,
							items: [{
								allowDecimals: true,
								allowNegative: false,
								fieldLabel: 'дозировка',
								id: 'VacEf_DozaKOl',
								name: 'VacEf_DozaKOl',
								allowBlank: false,
								width: 90,
								decimalPrecision: 6,
								xtype: 'numberfield',
								maxValue: '999.999999'
							}]
						}, {
							labelWidth: 10,
							layout: 'form',
							items: [{
//						autoLoad: true,
//						fieldLabel: ' ',
//						id: 'VacEf_DozaName',
//						name: 'VacEf_DozaName',
//						listWidth: 100,
//						labelSeparator: '',
//						width: 100,
//						editable: false,
//						//																					   hideEmptyRow: true,
//						listeners: {
//							'blur': function(combo)	 {
//								if ( combo.value == '' )
//									combo.setValue(1);
//							},
//							'change': function(combo, newValue, oldValue){
//								var base_form = this.FormPanel.getForm();
//								base_form.findField('VacEf_DozaName_Tmp').setValue(combo.getRawValue() );
//							//																									   sw.swMsg.alert('Сообщение', combo.getRawValue() );
//
//							}.createDelegate(this)
//						},
////						store: new Ext.data.SimpleStore({
////							autoLoad: true,
////							data: [
////							[ 1, 1, 'мл' ],
////							[ 2, 2, 'капель' ],
////							[ 3, 3, 'мг' ]
////
////							],
////							fields: [
////							{
////								name: 'DozaType_id', 
////								type: 'int'
////							},
////
////							{
////								name: 'DozaType_Code', 
////								type: 'int'
////							},
////
////							{
////								name: 'DozaType_Name', 
////								type: 'string'
////							}
////							],
////							key: 'DozaType_id',
////							sortInfo: {
////								field: 'DozaType_Code'
////							}
////						}),
//						tpl: new Ext.XTemplate(
//							'<tpl for="."><div class="x-combo-list-item">',
//							'<font color="red">{DozaType_Code}</font>&nbsp;{DozaType_Name}',
//							'</div></tpl>'
//							),
//						valueField: 'DozaType_id',
//						displayField: 'DozaType_Name',
//						value: 1,
//						xtype: 'swbaselocalcombo'
//					}]

								//fieldLabel: 'Выбор инфекции',
								fieldLabel: ' ',
								autoLoad: true,
								allowBlank: false,
								name: 'amm_DozeTypeCombo',
								id: 'ammDozeTypeCombo',
								listWidth: 100,
								labelSeparator: '',
								width: 100,
								editable: false,
								xtype: 'amm_DozeTypeCombo'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						id: 'ParamAge2',
						name: 'ParamAge2',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 30,
							//																	xtype: 'fieldset',
							items: [{
								fieldLabel: 'после',
								layout: 'form',
								id: 'VacEf_DozaAge_Tmp',
								name: 'VacEf_DozaAge_Tmp',
								width: 30,
								labelSeparator: '',
								readOnly: true,
								//																	   tabIndex: TABINDEX_EMHPEF + 7,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 10,
							//																	xtype: 'fieldset',
							items: [{
								fieldLabel: '',
								layout: 'form',
								id: 'VacEf_DozaTypePeriod_Tmp',
								name: 'VacEf_DozaTypePeriod_Tmp',
								width: 50,
								labelSeparator: '',
								readOnly: true,
								value: ' лет ',
								//		 tabIndex: TABINDEX_EMHPEF + 7,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 70,
							items: [{
								allowDecimals: true,
								allowNegative: false,
								fieldLabel: 'дозировка',
								id: 'VacEf_DozaKOl2',
								name: 'VacEf_DozaKOl2',
								width: 90,
								decimalPrecision: 6,
								xtype: 'numberfield',
								maxValue: '999.999999'
							}]
						}, {
							labelWidth: 10,
							layout: 'form',
							items: [{
									//																						autoLoad: true,
//						fieldLabel: ' ',
//						id: 'VacEf_DozaName_Tmp',
//						name: 'VacEf_DozaName_Tmp',
//						listWidth: 100,
//						labelSeparator: '',
//						xtype: 'textfield',
//						readonly: true,
//						width: 100
								fieldLabel: ' ',
								autoLoad: true,
								name: 'amm_DozeTypeCombo2',
								id: 'ammDozeTypeCombo2',
								listWidth: 100,
								labelSeparator: '',
								width: 100,
								editable: false,
								xtype: 'amm_DozeTypeCombo'
							}]
						}]
					}]
				}, {
//		hidden: true,
					autoHeight: true,
					autoScroll: true,
					style: 'padding: 0px 5px;',
					title: 'Способ применения',
					//														  width: 755,
					height: 100,
					labelWidth: 30,
					xtype: 'fieldset',
					items: [{
						xtype: 'checkbox',
						height: 24,
						name: 'Ch_AgeSposob',
						id: 'Ch_AgeSposob',
						labelSeparator: '',
						checked: true,
						boxLabel: 'Зависит от возраста пациента',
						listeners: {
							'check': function(checkbox, checked) {
								var base_form = this.FormPanel.getForm();
								base_form.findField('VacEf_WayAge1').setContainerVisible(checked);
								base_form.findField('VacEf_WayAge2').setContainerVisible(checked);
								base_form.findField('VacEf_WayPeriod1').setContainerVisible(checked);
								base_form.findField('VacEf_WayPeriod2').setContainerVisible(checked);
								base_form.findField('VacEf_VaccineWay2').setContainerVisible(checked);
								base_form.findField('VacEf_VaccinePlace2').setContainerVisible(checked);

								base_form.findField('VacEf_WayAge1').allowBlank = !checked;
								base_form.findField('VacEf_WayAge1').isValid();
								base_form.findField('VacEf_WayAge2').allowBlank = !checked;
								base_form.findField('VacEf_WayAge2').isValid();
								base_form.findField('VacEf_VaccineWay2').allowBlank = !checked;
								base_form.findField('VacEf_VaccineWay2').isValid();
//						base_form.findField('VacEf_VaccinePlace2').allowBlank = !checked;
								base_form.findField('VacEf_VaccinePlace2').isValid();
								Ext.getCmp('amm_SprVaccineEditWindow').syncShadow();//перерисовка тени под изменившееся окно
							}.createDelegate(this)
						}
					}, {
						border: false,
						layout: 'column',
						id: 'ParamAge',
						name: 'ParamAge',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 30,
							//																	xtype: 'fieldset',
							items: [{
								fieldLabel: 'до',
								layout: 'form',
								id: 'VacEf_WayAge1',
								name: 'VacEf_WayAge1',
								width: 30,
								labelSeparator: '',
								//																	   tabIndex: TABINDEX_EMHPEF + 7,
								xtype: 'textfield',
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = this.FormPanel.getForm();
										base_form.findField('VacEf_WayAge2').setValue(newValue);
										//																						 sw.swMsg.alert('Сообщение', newValue + ', ' +	oldValue );//, function() { this.hide(); }.createDelegate(this) );
									}.createDelegate(this)
								}
							}]
						}, {
							labelWidth: 10,
							layout: 'form',
							items: [{
								fieldLabel: '',
								layout: 'form',
								id: 'VacEf_WayPeriod1',
								name: 'VacEf_WayPeriod1',
								width: 50,
								labelSeparator: '',
								readOnly: true,
								editable: false,
								value: ' мес. ',
								xtype: 'textfield'
							}]
						}, {
							labelWidth: 10,
							layout: 'form',
							items: [{
								autoLoad: true,
								fieldLabel: ' ',
								id: 'VacEf_VaccineWay1',
								name: 'VacEf_VaccineWay1',
								listWidth: 120,
								labelSeparator: '',
								width: 120,
								editable: false,
								allowBlank: false,
								xtype: 'amm_WayTypeCombo',
								listeners: {
									'select': function(combo, record, index) {
//																 Ext.getCmp('amm_SprNacCalEditWindow').loadNumSchemeCombo(combo.getValue());
										Ext.getCmp('VacEf_VaccinePlace1').getStore().load({
											params: {
												VaccineWay_id: combo.getValue()
											},
											callback: function() {
												Ext.getCmp('VacEf_VaccinePlace1').setValue(null);
												console.log('Обнуление VacEf_VaccinePlace1');
											}
										})
									}.createDelegate(this)
								}
							}]
						}, {
							labelWidth: 10,
							layout: 'form',
//										  autoHeight: true,
							items: [{
								autoLoad: false,
//												  anchor: '100%',
								autoHeight: true,
								fieldLabel: ' ',
								id: 'VacEf_VaccinePlace1',
								name: 'VacEf_VaccinePlace1',
								listWidth: 350,
								labelSeparator: '',
								width: 210,
								editable: false,
								//***********
//												  tpl:'<tpl for=".">' +
//													  '<div class="x-combo-list-item">' +
//													  '{text}&nbsp;' +
//													  '</div></tpl>',
//														tpl:
//			'<tpl for="."><div class="x-combo-list-item">'+
//			'{MESLevel_Code}&nbsp;'+
//			'</div></tpl>',
								/////*******
//						allowBlank: false,
								listeners: {
									'change': function(combo, newValue, oldValue) {
//								var base_form = this.FormPanel.getForm();
//								base_form.findField('VacEf_DozaTypePeriod_Tmp').setValue(combo.getRawValue() );
									}.createDelegate(this)
								},
								xtype: 'amm_PlaceTypeCombo'
							}]//ammVaccinePlaceCombo
						}]
					}, {
						border: false,
						layout: 'column',
						id: 'ParamAge2',
						name: 'ParamAge2',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 30,
							//																	xtype: 'fieldset',
							items: [{
								fieldLabel: 'после',
								layout: 'form',
								id: 'VacEf_WayAge2',
								name: 'VacEf_WayAge2',
								width: 30,
								labelSeparator: '  ',
								readOnly: true,
								//																	   tabIndex: TABINDEX_EMHPEF + 7,
								xtype: 'textfield'
							}]
						}, {
							labelWidth: 10,
							layout: 'form',
							items: [{
								fieldLabel: '',
								layout: 'form',
								id: 'VacEf_WayPeriod2',
								name: 'VacEf_WayPeriod2',
								width: 50,
								labelSeparator: '',
								readOnly: true,
								editable: false,
								value: ' мес. ',
								xtype: 'textfield'
							}]
						}, {
							labelWidth: 10,
							layout: 'form',
							items: [{
								autoLoad: true,
								fieldLabel: ' ',
								id: 'VacEf_VaccineWay2',
								name: 'VacEf_VaccineWay2',
								listWidth: 120,
								labelSeparator: '',
								width: 120,
								editable: false,
								xtype: 'amm_WayTypeCombo',
								listeners: {
									'select': function(combo, record, index) {
										console.log(Ext.getCmp('VacEf_VaccinePlace1').getStore());
										console.log(Ext.getCmp('VacEf_VaccinePlace2').getStore());
//															  Ext.getCmp('amm_SprNacCalEditWindow').loadNumSchemeCombo(combo.getValue());
										Ext.getCmp('VacEf_VaccinePlace2').getStore().load({
											params: {
												VaccineWay_id: combo.getValue()
											},
											callback: function() {
												Ext.getCmp('VacEf_VaccinePlace2').setValue(null);

											}
										})
									}.createDelegate(this)
								}
							}]
						}, {
							labelWidth: 10,
							layout: 'form',
							items: [{
								autoLoad: false,
//												  anchor: '100%',
								fieldLabel: ' ',
								id: 'VacEf_VaccinePlace2',
								name: 'VacEf_VaccinePlace2',
								listWidth: 350,
								labelSeparator: '',
								width: 210,
								editable: false,
								xtype: 'amm_PlaceTypeComboW'
							}]//ammVaccinePlaceCombo
						}]
					}]
				}, {
					autoHeight: true,
					autoScroll: true,
					style: 'padding: 0px 5px;',
					title: 'Схема вакцинации',
					id: 'VacEf_OnGripp',
					region: 'center',
					//height: 95,
					labelWidth: 30,
					autowith: true,
					xtype: 'fieldset',
					items: [{
						border: true,
						xtype: 'panel',
						layout: 'column',
						//autoHeight: true,
						maximizable: true,
						autowith: true,
						//bodyStyle: 'padding-left:180px',
						//Хlayout: 'column',
						bodyStyle: 'padding-left:30px',
						items: [
							this.ViewFrameOtherVacScheme
						]
					}, {
						height: 10,
						border: false,
						cls: 'tg-label'
					}
				]}
			],
			labelAlign: 'right',
			labelWidth: 120
		});

		Ext.apply(this, {
			formParams: null,
			frame: true,		   
			//labelWidth : 150,	 
			bodyBorder : true,
			layout : "form",
			//		   style: 'background-color: #fff;',
			cls: 'tg-label',
			autoHeight: true,
			items: [
				this.FormPanel
			]
		});
		sw.Promed.amm_SprVaccineEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function(record) {
		var $New = false;
		sw.Promed.amm_SprVaccineEditWindow.superclass.show.apply(this, arguments);
		Ext.getCmp('ammDozeTypeCombo').getStore().reload();
		Ext.getCmp('ammDozeTypeCombo2').getStore().reload();
		Ext.getCmp('VacEf_VaccineWay1').getStore().reload();
		Ext.getCmp('VacEf_VaccineWay2').getStore().reload();


		this.formParams = record;
		Ext.getCmp('ammTypeInfectioncombo').getStore().reload();
		
		Ext.getCmp('uniVac').show();
		if (record.Vaccine_id == undefined) {
			$New = true;
			Ext.getCmp('amm_SprVaccineEditWindow').setTitle('Справочник вакцин: Добавление');
			Ext.getCmp('ViewFrameOtherVacScheme').removeAll();
			Ext.getCmp('VacEf_OnGripp').hide();
		}
		else {
			formsParams_Vaccine_id = record.Vaccine_id;
			Ext.getCmp('amm_SprVaccineEditWindow').setTitle('Справочник вакцин: Редактирование');
			if (formsParams_Vaccine_id == 26 || formsParams_Vaccine_id == 27) {
				//	Если туберкулин или Диаскинтест
				Ext.getCmp('uniVac').hide();			
			}
		}

		this.formStore.load({
			params: {
				Vaccine_id: record.Vaccine_id
			},
			callback: function() {

				var formStoreCount = this.formStore.getCount() > 0;
				var parentObj = Ext.getCmp('uniVac');
				parentObj.resetTypeInfections();
				if (formStoreCount) {
					sw.Promed.vac.utils.consoleLog('Step 01');
					var formStoreRecord = this.formStore.getAt(0);
					this.formParams.Vaccine_Name = formStoreRecord.get('Vaccine_Name');
					this.formParams.Vaccine_Nick = formStoreRecord.get('Vaccine_Nick');
					this.formParams.Vaccine_AgeBegin = formStoreRecord.get('Vaccine_AgeBegin');
					this.formParams.Vaccine_AgeEnd = formStoreRecord.get('Vaccine_AgeEnd');
					this.formParams.vaccineDoseAge = formStoreRecord.get('Vaccine_doseAge');
					this.formParams.doseVal1 = formStoreRecord.get('DoseVal1');
					this.formParams.doseVal2 = formStoreRecord.get('DoseVal2');
					this.formParams.doseType1 = formStoreRecord.get('DoseType1');
					this.formParams.doseType2 = formStoreRecord.get('DoseType2');
					this.formParams.vaccineWayPlaceAge = formStoreRecord.get('Vaccine_WayPlaceAge');
					this.formParams.placeType1 = formStoreRecord.get('PlaceType1');
					this.formParams.placeType2 = formStoreRecord.get('PlaceType2');
					this.formParams.wayType1 = formStoreRecord.get('WayType1');
					this.formParams.wayType2 = formStoreRecord.get('WayType2');
					this.formParams.OnGripp = formStoreRecord.get('OnGripp');
					formsParams_OnGripp = formStoreRecord.get('OnGripp');

//					alert(formStoreRecord.get('Vaccine_WayPlaceAge'));

					if (formStoreRecord.get('VacTypeIds')) {
						var vacTypes = formStoreRecord.get('VacTypeIds').split(',');
						for (var i = 0; i < vacTypes.length; i++) {
							if (vacTypes[i] != '') {
//							alert(vacTypes[i]);
								var vacType = vacTypes[i].split(':');
								var infectionObj = {};
								infectionObj.id = vacType[0];
								infectionObj.text = vacType[1];
								sw.Promed.vac.utils.consoleLog(infectionObj);
								parentObj.addTypeInfection(infectionObj);
							}
						}
					}
					
					if (formStoreRecord.get('OnGripp') == 1) {
						this.height = 735;
						if (!$New)
						Ext.getCmp('VacEf_OnGripp').show();
						
						if (record.Vaccine_id != undefined) {
							var params = new Object();
							params.Vaccine_id = record.Vaccine_id;
							Ext.getCmp('ViewFrameOtherVacScheme').ViewGridPanel.getStore().baseParams = params;
							Ext.getCmp('ViewFrameOtherVacScheme').ViewGridPanel.getStore().reload();
						}
					}
					else {
						Ext.getCmp('amm_SprVaccineEditWindow').height = 580;
						Ext.getCmp('VacEf_OnGripp').hide();
					}
					this.FormPanel.getForm().findField('Vaccine_Nick').setValue(this.formParams.Vaccine_Nick);
					parentObj.doLayout();
				}

				this.FormPanel.getForm().reset();
				this.FormPanel.getForm().findField('Vaccine_Name').setValue(this.formParams.Vaccine_Name);
				this.FormPanel.getForm().findField('Vaccine_Nick').setValue(this.formParams.Vaccine_Nick);

				if (this.formParams.Vaccine_AgeBegin != null) {
					this.FormPanel.getForm().findField('VacEf_AreaRange1').setValue(this.formParams.Vaccine_AgeBegin);
					this.FormPanel.getForm().findField('VacEf_AreaRange2').setValue(this.formParams.Vaccine_AgeEnd);
					this.FormPanel.getForm().findField('VacEf_AgeRangeCheck').setValue(false);
				} else {
					this.FormPanel.getForm().findField('VacEf_AgeRangeCheck').setValue(true);
				}

				if (this.formParams.vaccineDoseAge == 1000) {
					this.FormPanel.getForm().findField('VacEf_DozaCheck').setValue(false);
					this.FormPanel.getForm().findField('VacEf_DozaKOl').setValue(this.formParams.doseVal2);
					this.FormPanel.getForm().findField('amm_DozeTypeCombo').setValue(this.formParams.doseType2);
				} else {
					this.FormPanel.getForm().findField('VacEf_DozaCheck').setValue(true);
					this.FormPanel.getForm().findField('VacEf_DozaKOl').setValue(this.formParams.doseVal1);
					this.FormPanel.getForm().findField('VacEf_DozaKOl2').setValue(this.formParams.doseVal2);
					this.FormPanel.getForm().findField('VacEf_DozaAge').setValue(this.formParams.vaccineDoseAge);
					this.FormPanel.getForm().findField('VacEf_DozaAge_Tmp').setValue(this.formParams.vaccineDoseAge);
					this.FormPanel.getForm().findField('amm_DozeTypeCombo').setValue(this.formParams.doseType1);
					this.FormPanel.getForm().findField('amm_DozeTypeCombo2').setValue(this.formParams.doseType2);
				}

				if (this.formParams.vaccineWayPlaceAge == 10000) {
					this.FormPanel.getForm().findField('Ch_AgeSposob').setValue(false);
					this.FormPanel.getForm().findField('VacEf_VaccineWay1').setValue(this.formParams.wayType2);

					Ext.getCmp('VacEf_VaccinePlace1').getStore().load({
						params: {
							VaccineWay_id: this.formParams.wayType2
						}
						,
						callback: function() {
							Ext.getCmp('VacEf_VaccinePlace1').setValue(Ext.getCmp('amm_SprVaccineEditWindow').formParams.placeType2);
							if (Ext.getCmp('VacEf_VaccinePlace1').value == Ext.getCmp('VacEf_VaccinePlace1').lastSelectionText) {
								Ext.getCmp('VacEf_VaccinePlace1').setValue(null);
							}
						}
					});
				} else {
					this.FormPanel.getForm().findField('Ch_AgeSposob').setValue(true);
					this.FormPanel.getForm().findField('VacEf_WayAge1').setValue(this.formParams.vaccineWayPlaceAge);
					this.FormPanel.getForm().findField('VacEf_WayAge2').setValue(this.formParams.vaccineWayPlaceAge);
					this.FormPanel.getForm().findField('VacEf_VaccineWay1').setValue(this.formParams.wayType1);
					this.FormPanel.getForm().findField('VacEf_VaccineWay2').setValue(this.formParams.wayType2);


					Ext.getCmp('VacEf_VaccinePlace1').getStore().load({
						params: {
							VaccineWay_id: this.formParams.wayType1
						},
						callback: function() {
//																	 Ext.getCmp('VacEf_VaccinePlace1').setValue (null);	  
							Ext.getCmp('VacEf_VaccinePlace1').setValue(Ext.getCmp('amm_SprVaccineEditWindow').formParams.placeType1);
							if (Ext.getCmp('VacEf_VaccinePlace1').value == Ext.getCmp('VacEf_VaccinePlace1').lastSelectionText) {
								Ext.getCmp('VacEf_VaccinePlace1').setValue(null);
							}
						}
					});

					Ext.getCmp('VacEf_VaccinePlace2').getStore().load({
						params: {
							VaccineWay_id: this.formParams.wayType2
						},
						callback: function() {
//																	 Ext.getCmp('VacEf_VaccinePlace2').setValue (null);																	   
							Ext.getCmp('VacEf_VaccinePlace2').setValue(Ext.getCmp('amm_SprVaccineEditWindow').formParams.placeType2);
							if (Ext.getCmp('VacEf_VaccinePlace2').value == Ext.getCmp('VacEf_VaccinePlace2').lastSelectionText) {
								Ext.getCmp('VacEf_VaccinePlace2').setValue(null);
							}
							;
							sw.Promed.vac.utils.consoleLog('Step 4');
						}
					});

				};
				Ext.getCmp('amm_SprVaccineEditWindow').syncShadow();//перерисовка тени под изменившееся окно
			}.createDelegate(this)
		});
		this.height = 1780;
		this.doLayout();
		//console.log( 'this.height 1 ');
		//console.log( this.height);		 
	},
	listeners: {
		'render': function (){
			Ext.getCmp('amm_SprVaccineEditWindow').syncShadow();
		}
	}
});
