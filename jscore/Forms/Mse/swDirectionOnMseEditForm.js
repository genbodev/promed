/**
* Форма "Направление на МСЭ"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      10.10.2011
*/

sw.Promed.swDirectionOnMseEditForm = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: true,
	maximizable: true,
	modal: false,
	shim: false,
	plain: true,
	action: null,
	EvnPL_id: null,
    onClose: Ext.emptyFn,
    onSave: Ext.emptyFn,
	layout: 'border',
	buttonAlign: "right",
	objectName: 'swDirectionOnMseEditForm',
	closeAction: 'hide',
	id: 'swDirectionOnMseEditForm',
	objectSrc: '/jscore/Forms/Mse/swDirectionOnMseEditForm.js',
	buttons: [
		{
			iconCls: 'save16',
			text: langs('Сохранить')
		}, {
			//iconCls: '',
			text: 'Возврат в МО на доработку',
			hidden: getRegionNick() != 'perm',
			handler: function() {
				var win = this.ownerCt,
					field = win.CommonForm.getForm().findField('EvnPrescrMse_id');
				getWnd('swEvnPrescrMseReturnWindow').show({
					EvnPrescrMse_id: field.getValue(),
					onSave: function() {
						win.hide();
					}.createDelegate(win)
				});
			}
		}, {
			handler: function() {
				var win = this.ownerCt,
					field = win.CommonForm.getForm().findField('EvnPrescrMse_id');
				if( field.getValue() == null || field.getValue() == '' ) {
					win.doSave(function() {
						win.printEvnPrescrMse();
					}.createDelegate(win));
				} else {
					win.printEvnPrescrMse();
				}
			},
			iconCls: 'print16',
			text: BTN_FRMPRINT
		}, {
			handler: function() {
				var win = this.ownerCt,
					field = win.CommonForm.getForm().findField('EvnPrescrMse_id');
				if(!Ext.isEmpty(field.getValue())) {
					win.printEvnPrescrMseRefuse();
				}
			},
			iconCls: 'print16',
			text: 'Печать отказа ВК'
		},
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : langs('Отмена'),
			tabIndex  : -1,
			tooltip   : langs('Отмена'),
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	/* Проверка сохранен ли у протокола Председатель ВК */
	isChairmanSaved: function()
	{
		if( !this.EvnVKExpertForm.isLoadedViewFrame || this.action == 'view' )
			return true;
		var charExists = false;
		this.EvnVKExpertViewFrame.getGrid().getStore().each(function(r) {
			if( r.get('ExpertMedStaffType_id') == 1 ) charExists = true; 
		});
		return charExists;
	},

	showUploadDialog: function(panel) {
		if(this.action != 'view' || this.ARMType == 'mse') {
			this.UploadDialog.panel = panel;
			this.UploadDialog.show();
		} else {
			alert(langs('Окно прикрепления документа недоступно в режиме просмотра.'));
		}
	},

	uploadSuccess: function(dialog, data, panel) {
		this.addFileToFilesPanel(data, panel);
	},

	getCountFiles: function(panel) {
		return panel.items.items.length;
	},

	setTitleFilesPanel: function(panel) {
		var c = this.getCountFiles(panel);
		if (c == 0) {
			var title = '<span style="color: gray;">нет приложенных документов</span>';
		} else {
			var tc = c.toString(), l = tc.length;
			var title = tc + ((tc.substring(l-1,1)=='1')?' документ':((tc.substring(l-1,1).inlist(['2','3','4']))?' документа': ' документов'));
		}
		panel.setTitle(panel.baseTitle+': '+title);
	},

	addFileToFilesPanel: function(file, panel) {
		if (file && file.name) {
			file.id = file.name.replace(/\./ig, '_');
			var html = '<div style="float:left;height:18px;">';
			var base_form = this.CommonForm.getForm();
			if(this.action.inlist(['edit','view']) && !Ext.isEmpty(file.url)) {
				html += '<a target="_blank" style="color: black; font-weight: bold;" href="'+file.url+'">'+file.name+'</a> ['+(file.size/1024).toFixed(2)+'Кб]';
			} else {
				html += '<b>'+file.name+'</b> ['+(file.size/1024).toFixed(2)+'Кб]';
			}
			if((this.action.inlist(['add','edit']) || this.ARMType == 'mse') && this.ARMType == panel.filePanelType) {
				html = html + ' <a href="#" onClick="Ext.getCmp(\''+this.id+'\').deleteFileToFilesPanel(\''+file.id+'\');">'+
					'<img title="Удалить" style="height: 12px; width: 12px; vertical-align: bottom;" src="/img/icons/delete16.png" /></a>';
			}
			html = html + '</div>';
			if(panel.findById(file.id) != null)
				return false;

			if(file.url && !file.tmp_name){
				//при загрузке формы существующие файлы имеют url (tmp_name при загрузке новых файлов)
				file.tmp_name = file.url;
			}
			panel.add({id: ''+file.id, border: false, html: html, settings: file});
			if(panel.collapsed)
				panel.expand();
			this.setTitleFilesPanel(panel);
			panel.syncSize();
			panel.ownerCt.syncSize();
			this.doLayout();
			this.syncShadow();
		}
	},

	resetFilesPanel: function(panel) {
		panel.removeAll();
		this.setTitleFilesPanel(panel);
		this.doLayout();
		this.syncShadow();
	},

	deleteFileToFilesPanel: function(id) {
		var win = this;
		var extItem = this.findById(''+id);

		if (extItem) {
			sw.swMsg.show({
				title: '',
				msg: langs('Вы действительно хотите удалить документ?'),
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId) {
					if (buttonId == 'yes') {
						win.FilesPanelVK.remove(extItem, true);
						win.FilesPanelMSE.remove(extItem, true);
						win.setTitleFilesPanel(win.FilesPanelVK);
						win.setTitleFilesPanel(win.FilesPanelMSE);
						win.FilesPanelVK.syncSize();
						win.FilesPanelMSE.syncSize();
						win.syncShadow();
					}
				}
			});
		}
	},
	
	addUslugaComplexMSERecommended: function() {
		var win = this,
			frm = this.CommonForm.getForm(),
			grid = this.UslugaComplexMSEGrid.getGrid()
			diag_id = frm.findField('Diag_id').getValue(),
			is_first_time = frm.findField('EvnPrescrMse_IsFirstTime').getValue(),
			ids = [];
			
		if (!diag_id) return false;
		
		var lm = this.getLoadMask('Загрузка услуг...');
		lm.show();
		Ext.Ajax.request({
			url: '/?c=Mse&m=getUslugaComplexMSERecommended',
			params: { 
				Diag_id: diag_id,
				EvnPrescrMse_IsFirstTime: is_first_time,
				Person_id: win.PersonFrame.personId
			},
			callback: function( o, s, r ) {
				lm.hide();
				if( s ) {
					var data = Ext.util.JSON.decode(r.responseText);
					if (data && data.length) {
						grid.getStore().each(function(el) {
							ids.push(el.get('EvnUsluga_id'))
						});
						
						data.forEach(function(el,index) {
							if (el.EvnUsluga_id.inlist(ids)) {
								delete data[index];
							} else {
								data[index].RecordStatus_Code = 0;
							}
						});
						
						data = data.filter(function (item) { return item != undefined });
						grid.getStore().loadData(data, true);
					}
				}
			}
		});
		
		
	},
	addUslugaComplexMSE: function() {
		var win = this,
			grid = this.UslugaComplexMSEGrid.getGrid(),
			frm = this.CommonForm.getForm(),
			params = {},
			ids = [];
			
		grid.getStore().each(function(el) {
			ids.push(el.get('EvnUsluga_id'))
		});
		
		params.Person_id = this.PersonFrame.personId;
		params.Diag_id = frm.findField('Diag_id').getValue();
		params.EvnPrescrMse_IsFirstTime = frm.findField('EvnPrescrMse_IsFirstTime').getValue();
		params.callback = function(data) {
			data.forEach(function(el,index) {
				if (el.EvnUsluga_id.inlist(ids)) {
					delete data[index];
				} else {
					data[index].RecordStatus_Code = 0;
				}
			});
			data = data.filter(function (item) { return item != undefined });
			grid.getStore().loadData(data, true);
		};
		
		getWnd('swUslugaComplexMSESelectWindow').show(params);
	},
	openUslugaComplexMSE: function() {
		var win = this,
			grid = this.UslugaComplexMSEGrid.getGrid(),
			record = grid.getSelectionModel().getSelected();
			
		if (!record || !record.get('EvnUsluga_id')) return false;
		
		var evn_usluga_id = record.get('EvnUsluga_id');
		var params = new Object();
		params.action = 'view';
		params.Person_id = record.get('Person_id');
		params.parentEvnComboData = null;
		params.parentClass = record.get('ParentClass_SysNick');
		
		switch ( record.get('EvnClass_SysNick') ) {
			case 'EvnUslugaCommon':
				params.formParams = {
					EvnUslugaCommon_id: evn_usluga_id
				}
				getWnd('swEvnUslugaEditWindow').show(params);
			break;

			case 'EvnUslugaOper':
				params.formParams = {
					EvnUslugaOper_id: evn_usluga_id
				}
				getWnd('swEvnUslugaOperEditWindow').show(params);
			break;

			case 'EvnUslugaPar':
				params.EvnUslugaPar_id = evn_usluga_id;
				getWnd('swEvnUslugaParEditWindow').show(params);
			break;
		}
	},
	deleteUslugaComplexMSE: function() {
		var win = this,
			grid = this.UslugaComplexMSEGrid.getGrid(),
			record = grid.getSelectionModel().getSelected();
			
		if (!record || !record.get('EvnUsluga_id')) return false;
		
		switch ( Number(record.get('RecordStatus_Code')) ) {
			case 0:
				grid.getStore().remove(record);
				break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function(rec) {
					return (Number(rec.get('RecordStatus_Code')) != 3);
				});
				break;
		}
	},
	
	listeners: {
		beforehide: function(win) {
			if (!win.isChairmanSaved()) {
				sw.swMsg.alert(langs('Ошибка'), langs('Необходимо указать Председателя врачебной комиссии!'));
				return false;
			}
			return true;
		},
		hide: function(win){
			win.onClose(win);
		}
	},
	
	show: function()
	{
		sw.Promed.swDirectionOnMseEditForm.superclass.show.apply(this, arguments);

		this.disableFormFields(false);
		this.CommonForm.getForm().reset();
		this.EvnStickForm.collapse();
		this.EvnVKExpertForm.collapse();
		// this.MedicalRehabilitationForm.collapse();
		this.EvnVKExpertViewFrame.removeAll(true);
		this.EvnVKExpertViewFrame.getGrid().getStore().removeAll();
		this.UslugaComplexMSEGrid.removeAll(true);
		this.UslugaComplexMSEGrid.getGrid().getStore().removeAll();
		this.EvnVKExpertForm.isLoadedViewFrame = false;
		this.EvnStatusHistoryGrid.hide();
		this.resetFilesPanel(this.FilesPanelMSE);
		this.resetFilesPanel(this.FilesPanelVK);
		this.evnMseFieldset_ClearValue();
		//win.buttons[1].enable();
		this.buttons[3].hide();

		var measuresFMR=this.MedicalRehabilitationForm.find('editformclassname', 'swMeasuresForMedicalRehabilitation')[0];
		measuresFMR.getGrid().getStore().removeAll();
		
		/** Параметры с которыми вызывается форма:
		*	
		*	action - не обязательный (может определяться автоматом)
		*	Person_id - обязательный
		*	Server_id - обязательный
		*	EvnVK_id - обязательный
		*	MedService_id - обязательный
		*	TimetableMedService_id - необязательный
		*	EvnPL_id - необязательный
		*	DopParams - необязательный (объект, содержащий данные полей протокола ВК, если эта форма открыта из протокола ВК)
		*/
		
		if( !arguments[0] ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Неверные параметры'));
			this.hide();
			return false;
		}

		this.dataSaved = false;
        this.onClose = arguments[0].onClose || Ext.emptyFn;
        this.onSave = arguments[0].onSave || Ext.emptyFn;
        this.withCreateDirection = (arguments[0] && arguments[0].withCreateDirection) || false;
        this.LpuSectionProfile_id = (arguments[0] && arguments[0].LpuSectionProfile_id) || null;

		this.action = arguments[0].action || null;
		// this.action = 'edit';
		this.EvnPL_id = arguments[0].EvnPL_id || null;
		this.EvnVK_id = arguments[0].EvnVK_id || null;
		this.EvnPrescrMse_id = arguments[0].EvnPrescrMse_id || null;
		this.EvnPrescrMse_pid = arguments[0].EvnPL_id || null;
		this.ARMType = arguments[0].ARMType || 'vk';
		
		this.PersonFrame.personId = arguments[0].Person_id;
		this.PersonFrame.serverId = arguments[0].Server_id;
		this.EvnVKExpertForm.EvnVK_id = arguments[0].EvnVK_id;
		
		this.buttons[1].setVisible(this.ARMType == 'mse' && getRegionNick() == 'perm');
		this.buttons[2].setVisible(!isMseDepers());
		
		this.FilesPanelVK.buttons[0].setDisabled(this.ARMType == 'mse');
		this.FilesPanelMSE.buttons[0].setDisabled(this.ARMType != 'mse');
		
		var win = this;
		var b_f = win.CommonForm.getForm();
		this.getLoadMask(langs('Загрузка данных..')).show();
		this.PersonFrame.setTitle('...');
		this.PersonFrame.load({
			callback: function() {
				this.PersonFrame.setPersonTitle();
				this.setDeputyKind();
				this.getDeputyKind();
				this.setMilitaryKind();
				this.setPhysicalDevelopmentVis();
			}.createDelegate(this),
			Person_id: this.PersonFrame.personId,
			Server_id: this.PersonFrame.serverId
		});
		
		if(arguments[0].TimetableMedService_id)
			b_f.findField('TimetableMedService_id').setValue(arguments[0].TimetableMedService_id);
		
		if(arguments[0].MedService_id)
			b_f.findField('MedService_id').setValue(arguments[0].MedService_id);
		
		this.buttons[0].setHandler(function() {
			var height_val = win.CommonForm.getForm().findField('PersonHeight_Height').getValue();
			var weight_val = win.CommonForm.getForm().findField('PersonWeight_Weight').getValue();
			if(height_val != '' && weight_val != '')
				win.savePersData(win.doSave.createDelegate(win));
			else
				win.doSave();
		});
		
		// Проставляем доп. параметры, пришедшие из протокола ВК
		if(arguments[0].DopParams){
			this.setDopParams(arguments[0].DopParams);
		}
		
		this.findById('domef_expandButtonsPanel').setVisible(this.action == 'view');
		
		var IsWork_combo = b_f.findField('EvnPrescrMse_IsWork');

		b_f.findField('MseDirectionAimType_id').fireEvent('select', b_f.findField('MseDirectionAimType_id'));
					
		Ext.QuickTips.unregister(b_f.findField('EvnPrescrMse_MainDisease').getEl());
		b_f.findField('EvnPrescrMse_MainDisease').disable();
		
		b_f.findField('MeasuresRehabEffect_IsRecovery').setValue(false);
		b_f.findField('MeasuresRehabEffect_IsRecovery').fireEvent('change', b_f.findField('MeasuresRehabEffect_IsRecovery'), false);
		b_f.findField('MeasuresRehabEffect_IsCompensation').setValue(false);
		b_f.findField('MeasuresRehabEffect_IsCompensation').fireEvent('change', b_f.findField('MeasuresRehabEffect_IsCompensation'), false);
		
		b_f.findField('EvnPrescrMse_Dop').setAllowBlank(true);
		b_f.findField('LearnGroupType_id').setAllowBlank(true);
		b_f.findField('EAddress_AddressText').setAllowBlank(true);

		if(getRegionNick() != 'kz') b_f.findField('EvnPrescrMse_InvalidEndDate').disable();
		
		// b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely').setValue(false);
		// b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely').fireEvent('change', b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely'), false);

		{
			var combo = win.ReasonAndDiagnosis.findById('AimColomn_id').items.items[0].items.items[0],
				it = win.ReasonAndDiagnosis.findById('AllAims_id');
			combo.firstLoad = true;
			it.removeAll();
			var targetField = win.ReasonAndDiagnosis.findById('EvnPrescrMse_AimMseOver').items.items[0];
			targetField.reset();
			targetField.ownerCt.setDisabled(true);
			targetField.disable();
			combo.reset();
			win.ReasonAndDiagnosis.doLayout();
		}
		/** Когда открываем форму, то для того чтобы определить ее @action нужно проверить 2 условия:
		*	Существует ли уже привязанное к данному протоколу ВК (EvnVK_id) направление на МСЭ (EvnPrescrMse_id)
		*		- если да, то проверяем есть ли привязанный к этому направлению протокол МСЭ:
		*			- если есть, то @action = 'view'
		*			- если нет, то @action = 'edit'
		*		- иначе @action = 'add'
		*
		*	метод defineAction(callback) определяет @action формы
		*	callback - функция, срабатывающая после определения @action
		*/
		this.defineAction(function(){
			switch(win.action){
				case 'add':
					win.buttons[1].disable();
					win.getLoadMask().hide();
					win.setTitle(langs('Направление на МСЭ: Добавление'));
							
					win.setFieldsVisible();

					b_f.findField('PersonHeight_IsAbnorm').setValue(1);
					b_f.findField('PersonWeight_IsAbnorm').setValue(1);
					
					b_f.findField('Lpu_gid').setValue(getGlobalOptions().lpu_id);
					b_f.findField('EvnPrescrMse_pid').setValue(win.EvnPrescrMse_pid);
					
					b_f.findField('Person_sidType').fireEvent('change', b_f.findField('Person_sidType'), win.PersonSidRadio0, null);

					b_f.findField('PersonHeight_IsAbnorm').fireEvent('change', b_f.findField('PersonHeight_IsAbnorm'), b_f.findField('PersonHeight_IsAbnorm').getValue());
					b_f.findField('PersonWeight_IsAbnorm').fireEvent('change', b_f.findField('PersonWeight_IsAbnorm'), b_f.findField('PersonWeight_IsAbnorm').getValue());
								
					win.SopDiagListPanel.reset();
					win.OslDiagListPanel.reset();
					win.MedicalRehabilitationForm.hide();
					
					b_f.findField('MeasuresRehabEffect_Comment').setAllowBlank(true);
					
					if (win.EvnVK_id && getRegionNick() != 'kz') {
						Ext.Ajax.request({
							url: '/?c=ClinExWork&m=getEvnVK', //loadJobData
							params: { 
								EvnVK_id: win.EvnVK_id
							},
							callback: function( o, s, r ) {
								if( s ) {
									var obj = Ext.util.JSON.decode(r.responseText)[0];
									
									b_f.findField('EvnPrescrMse_IsPalliative').setValue(obj.PalliatEvnVK_IsPMP);		
									b_f.findField('Diag_id').setValue(obj.Diag_id);									
									b_f.findField('Diag_id').fireEvent('select', b_f.findField('Diag_id'), b_f.findField('Diag_id').getStore().getAt(0), 0);
									b_f.findField('Diag_id').disable();
									b_f.findField('EvnPrescrMse_MainDisease').setValue(obj.EvnVK_MainDisease);
									if (obj.EvnVK_MainDisease) {
										Ext.QuickTips.register({
											target: b_f.findField('EvnPrescrMse_MainDisease').getEl(),
											text: obj.EvnVK_MainDisease,
											enabled: true,
											showDelay: 5,
											trackMouse: true,
											autoShow: true
										});	
									}
									// Проставляем диагнозы
									win.diagsSetValues();
									
									win.SopDiagListPanel.setValues(obj.SopDiagList);
									win.OslDiagListPanel.setValues(obj.OslDiagList);
								}
							}
						});
					}
					
					if (getRegionNick() != 'kz') {
						b_f.findField('EvnPrescrMse_IsPersonInhabitationRb').items.items[0].setValue(false);
						b_f.findField('EvnPrescrMse_IsPersonInhabitationRb').items.items[1].setValue(true);
						b_f.findField('Org_gid').hideContainer();
						b_f.findField('Org_gid').setAllowBlank(true);
						b_f.findField('EvnPrescrMse_IsPersonInhabitation').showContainer();
						b_f.findField('EvnPrescrMse_IsPersonInhabitation').setValue(2);
					}
					
					var visibleEvnVKExpertForm = (win.EvnVK_id) ? true : false;
					win.EvnVKExpertForm.setVisible(visibleEvnVKExpertForm);

					// Получим данные по персону
					Ext.Ajax.request({
						url: '/?c=Mse&m=getPersonJobData', //loadJobData
						params: { 
							Person_id: win.PersonFrame.personId,
							Server_id: win.PersonFrame.serverId
						},
						callback: function( o, s, r ) {
							if( s ) {
								var jobData = Ext.util.JSON.decode(r.responseText)[0];
								
								//IsWork_combo.setRawValue(langs('Нет'));
								IsWork_combo.clearValue();
								win.onoffFieldsDependingOnWorks();
								if( !jobData ) return false;
								
								if( jobData.Post_id != null ) {
									b_f.findField('Post_id').setValue( jobData.Post_id );
								}
								
								if( jobData.Org_id != null ) {
									//IsWork_combo.setValue(2);
									//IsWork_combo.setRawValue(langs('Да'));
									//IsWork_combo.fireEvent('select', IsWork_combo, IsWork_combo.getStore().getAt(2), 2);
									b_f.findField('Org_id').getStore().loadData([{
										Org_id: jobData.Org_id,
										Org_Name: jobData.Org_Name,
										Org_ColoredName: ''
									}]);
									b_f.findField('Org_id').setValue(jobData.Org_id);
									
									b_f.findField('OAddress_AddressText').loadDataByOrgId(jobData.Org_id);
								}
							}
						}
					});
					
					// Если привязка в ТАПу есть (а по идее всегда должна быть), то поле "история заболевания" заполняем
					// изходя из данных последнего посещения (гемор вобщем)
					if( win.EvnPL_id != null ) {
						var EvnPL_id = win.EvnPL_id;
						Ext.Ajax.request({
							params: { EvnPL_id: EvnPL_id },
							url: '/?c=Mse&m=getEvnPLXmlData',
							callback: function(o, s, r) {
								if(s) {
									var obj = Ext.util.JSON.decode(r.responseText)[0];
									if(obj) {
										var xmldoc = $.parseXML(obj.EvnXml_Data);
										var xml = $(xmldoc);
										var anamnesmorbi_text = xml.find('anamnesmorbi').text();//.replace(/<\/?[^>]+>\s?/g, '');
										b_f.findField('EvnPrescrMse_DiseaseHist').setValue(anamnesmorbi_text);
										//var objectivestatus_text = xml.find('objectivestatus').text().replace(/<\/?[^>]+>\s?/g, '');
										//b_f.findField('EvnPrescrMse_State').setValue(objectivestatus_text);
									}
								}
							}
						});
					}
					// Получение персданных (рост и вес)
					win.getPersData();
					win.getIPRAData();
							
					b_f.findField('IPRAResult_rid').setDisabled(true);
					b_f.findField('IPRAResult_cid').setDisabled(true);
					
					b_f.findField('MseDirectionAimType_id').validate();
					
					b_f.findField('EvnPrescrMse_IsFirstTime').focus(true, 100);

					var evnPrescrMse_OrgMedDateYear = b_f.findField('EvnPrescrMse_OrgMedDateYear');
					if(evnPrescrMse_OrgMedDateYear) evnPrescrMse_OrgMedDateYear.fireEvent('change', evnPrescrMse_OrgMedDateYear, evnPrescrMse_OrgMedDateYear.getValue());
				break;
				
				case 'edit':
				case 'view':
					b_f.load({
						url: '/?c=Mse&m=getEvnPrescrMse',
						params: { EvnVK_id: win.EvnVKExpertForm.EvnVK_id, EvnPrescrMse_id: win.EvnPrescrMse_id },
						success: function(f, r){
							var obj = Ext.util.JSON.decode(r.response.responseText)[0];
							win.getLoadMask().hide();
							
							win.setFieldsVisible();

							if (win.action == 'edit' && b_f.findField('accessType').getValue() == 'view') {
								win.action = 'view';
							}

							if(win.EvnVKExpertForm.EvnVK_id && obj.EvnVK_id){
								win.EvnVKExpertForm.setVisible(true);
							}else if(obj.EvnVK_id){
								win.EvnVKExpertForm.EvnVK_id =obj.EvnVK_id;
								win.EvnVKExpertForm.setVisible(true);
							}else{
								win.EvnVKExpertForm.setVisible(false);
							}

							var EvnPrescrMse_InvalidPercent = b_f.findField('EvnPrescrMse_InvalidPercent').getValue();

							// <!-- Направляется
							var IsFirstTime_combo = b_f.findField('EvnPrescrMse_IsFirstTime');
							var rec = IsFirstTime_combo.getStore().getAt(IsFirstTime_combo.getValue());
							IsFirstTime_combo.fireEvent('select', IsFirstTime_combo, rec, IsFirstTime_combo.getValue());
							// -->

							// Получение персданных (рост и вес)
							if (obj.PersonHeight_id) {
								b_f.findField('PersonHeight_id').setValue(obj.PersonHeight_id);
							}
							if (obj.PersonWeight_id) {
								b_f.findField('PersonWeight_id').setValue(obj.PersonWeight_id);
							}
							win.getPersData();

							if ( !Ext.isEmpty(EvnPrescrMse_InvalidPercent) ) {
								b_f.findField('EvnPrescrMse_InvalidPercent').setValue(EvnPrescrMse_InvalidPercent);
							}

							b_f.findField('MseDirectionAimType_id').fireEvent('select', b_f.findField('MseDirectionAimType_id'));

							// <!-- Законный представитель
							if (obj.Person_Fio) {
								b_f.findField('Person_sid').setRawValue(obj.Person_Fio);
                                win.getDeputyKind(obj.Person_sid)
                                //@todo статус
                            }
							if (obj.Org_sid) {
								win.PersonSidRadio0.setValue(false);
								win.PersonSidRadio1.setValue(false);
								win.PersonSidRadio2.setValue(true);
								b_f.findField('Person_sidType').fireEvent('change', b_f.findField('Person_sidType'), win.PersonSidRadio2, 2);
								org_sid_combo = b_f.findField('Org_sid');
								org_sid_combo.getStore().removeAll();
								if(org_sid_combo.getValue() != '' && obj.Org_NameSid != null){
									org_sid_combo.getStore().load({
										params: {Org_id: org_sid_combo.getValue()},
										callback: function(){
											org_sid_combo.setValue(org_sid_combo.getValue());
											org_sid_combo.fireEvent('change', org_sid_combo, org_sid_combo.getValue());
										}
									});
								}
							} else if (obj.Person_sid) {
								win.PersonSidRadio0.setValue(false);
								win.PersonSidRadio1.setValue(true);
								win.PersonSidRadio2.setValue(false);
								b_f.findField('Person_sidType').fireEvent('change', b_f.findField('Person_sidType'), win.PersonSidRadio1, 1);
							} else {
								win.PersonSidRadio0.setValue(true);
								win.PersonSidRadio1.setValue(false);
								win.PersonSidRadio2.setValue(false);
								b_f.findField('Person_sidType').fireEvent('change', b_f.findField('Person_sidType'), win.PersonSidRadio0, null);
							}
							// -->
							
							// <!-- находится
							if (getRegionNick() != 'kz') {
								org_gid_combo = b_f.findField('Org_gid');
								org_gid_combo.getStore().removeAll();
								if(org_gid_combo.getValue() != '' && obj.Org_NameGid != null){
									org_gid_combo.getStore().load({
										params: {Org_id: org_gid_combo.getValue()},
										callback: function(){
											org_gid_combo.setValue(org_gid_combo.getValue());
											org_gid_combo.fireEvent('change', org_gid_combo, org_gid_combo.getValue());
										}
									});
								}
								if (obj.Org_gid != null || obj.EvnPrescrMse_IsPersonInhabitation == 1) {
									b_f.findField('EvnPrescrMse_IsPersonInhabitationRb').items.items[0].setValue(true);
									b_f.findField('EvnPrescrMse_IsPersonInhabitationRb').items.items[1].setValue(false);
									b_f.findField('Org_gid').showContainer();
									b_f.findField('Org_gid').setAllowBlank(false);
									b_f.findField('EvnPrescrMse_IsPersonInhabitation').hideContainer();
									b_f.findField('EvnPrescrMse_IsPersonInhabitation').setValue(1);
								} else {
									b_f.findField('EvnPrescrMse_IsPersonInhabitationRb').items.items[0].setValue(false);
									b_f.findField('EvnPrescrMse_IsPersonInhabitationRb').items.items[1].setValue(true);
									b_f.findField('Org_gid').hideContainer();
									b_f.findField('Org_gid').setAllowBlank(true);
									b_f.findField('EvnPrescrMse_IsPersonInhabitation').showContainer();
									b_f.findField('EvnPrescrMse_IsPersonInhabitation').setValue(2);
								}
							}
							// -->
							
							// <!-- Работает
							IsWork_combo.getStore().each(function(rec) {
								if( rec.get('YesNo_id') == IsWork_combo.getValue() ) {
									IsWork_combo.fireEvent('select', IsWork_combo, rec, rec.id);
								}
							});
							// -->
							
							// <!-- Организация (работа)
							org_combo1 = b_f.findField('Org_id');
							org_combo1.getStore().removeAll();
							if(org_combo1.getValue() != '' && obj.Org_Name1 != null){
								org_combo1.getStore().loadData([{
									Org_id: org_combo1.getValue(),
									Org_Name: obj.Org_Name1,
									Org_ColoredName: ''
								}]);
								var idx = org_combo1.getStore().findBy(function(rec) { return rec.get('Org_id') == org_combo1.getValue(); });
								if(idx == -1) return false;
								org_combo1.setValue(obj.Org_id);
								if (!obj.OAddress_id) {
									b_f.findField('OAddress_AddressText').loadDataByOrgId(b_f.findField('Org_id').getValue());
								}
								
							}
							// -->
							
							// <!-- Образоват. учреждение
							org_combo2 = b_f.findField('Org_did');
							org_combo2.getStore().removeAll();
							if(org_combo2.getValue() != '' && obj.Org_Name2 != null){
								org_combo2.getStore().loadData([{
									Org_id: org_combo2.getValue(),
									Org_Name: obj.Org_Name2,
									Org_ColoredName: ''
								}]);
								var idx = org_combo2.getStore().findBy(function(rec) { return rec.get('Org_id') == org_combo2.getValue(); });
								if(idx == -1) return false;
								org_combo2.setValue(obj.Org_did);
								b_f.findField('EvnPrescrMse_Dop').setAllowBlank(false);
								b_f.findField('LearnGroupType_id').setAllowBlank(false);
								b_f.findField('EAddress_AddressText').setAllowBlank(false);
								if (!obj.EAddress_id) {
									b_f.findField('EAddress_AddressText').loadDataByOrgId(b_f.findField('Org_did').getValue());
								}
							}
							// -->
							
							// Проставляем диагнозы
							win.diagsSetValues();
							win.SopDiagListPanel.setValues(obj.SopDiagList);
							win.OslDiagListPanel.setValues(obj.OslDiagList);
							
							if (win.EvnVK_id && getRegionNick() == 'perm') {
								Ext.Ajax.request({
									url: '/?c=ClinExWork&m=getEvnVK', //loadJobData
									params: { 
										EvnVK_id: win.EvnVKExpertForm.EvnVK_id
									},
									callback: function( o, s, r ) {
										if( s ) {
											var obj = Ext.util.JSON.decode(r.responseText)[0];
											
											b_f.findField('Diag_id').setValue(obj.Diag_id);
											b_f.findField('Diag_id').fireEvent('select', b_f.findField('Diag_id'), b_f.findField('Diag_id').getStore().getAt(0), 0);
											b_f.findField('Diag_id').disable();
										}
									}
								});
							}

							// Должность
							if ( b_f.findField('Post_id').getValue() > 0 ) {
								b_f.findField('Post_id').getStore().load({
										params: {
											Object:'Post',
											Post_id:'',
											Post_Name:'',
											Post_curid: b_f.findField('Post_id').getValue()
										},
										callback: function() {
											b_f.findField('Post_id').setValue(b_f.findField('Post_id').getValue());
										}
								});
							}
							win.visibleButtonsFMR(true);
							// Ставим фокус на поле "направляется"
							IsFirstTime_combo.focus(true, 100);
							if(win.action == 'edit'){
								win.setTitle(langs('Направление на МСЭ: Редактирование'));
							} else if(win.action == 'view') {
								win.setTitle(langs('Направление на МСЭ: Просмотр'));
								win.disableFormFields(true);
								win.visibleButtonsFMR(false);
							}

							b_f.findField('PersonHeight_IsAbnorm').fireEvent('change', b_f.findField('PersonHeight_IsAbnorm'), b_f.findField('PersonHeight_IsAbnorm').getValue());
							b_f.findField('PersonWeight_IsAbnorm').fireEvent('change', b_f.findField('PersonWeight_IsAbnorm'), b_f.findField('PersonWeight_IsAbnorm').getValue());
							var evnPrescrMse_OrgMedDateYear = b_f.findField('EvnPrescrMse_OrgMedDateYear');
							if(evnPrescrMse_OrgMedDateYear) evnPrescrMse_OrgMedDateYear.fireEvent('change', evnPrescrMse_OrgMedDateYear, evnPrescrMse_OrgMedDateYear.getValue());

                            /*if (Ext.isEmpty(win.CommonForm.getForm().findField('Person_sid').getValue())) {
                                win.CommonForm.getForm().findField('DeputyKind_id').setDisabled(true);
                            } else {
                                win.CommonForm.getForm().findField('DeputyKind_id').setDisabled(false);
                            }*/
							
							win.buttons[1].setDisabled(
								b_f.findField('EvnStatus_id').getValue() != 28 /*|| win.action == 'view'*/
							);

							win.buttons[3].setVisible(b_f.findField('EvnStatus_id').getValue() && b_f.findField('EvnStatus_id').getValue().inlist([32,34]));

							win.EvnStatusHistoryGrid.getGrid().getStore().load({
								params: {EvnPrescrMse_id: b_f.findField('EvnPrescrMse_id').getValue()},
								callback: function() {
									var EvnStatus_id = b_f.findField('EvnStatus_id').getValue();
									if (win.EvnStatusHistoryGrid.getGrid().getStore().getCount() && (EvnStatus_id && EvnStatus_id.inlist([30,32,34,35]))) {
										win.EvnStatusHistoryGrid.show();
									}
								}
							});

							// Файлы
							files = obj.EvnPrescrMse_FilePath;
							
							if (files && files.vk.length) {
								for(var j=0; j < files.vk.length; j++) {
									//files.vk[j].size = 0;
									win.addFileToFilesPanel(files.vk[j], win.FilesPanelVK);
								}
							}
							
							if (files && files.mse.length) {
								for(var j=0; j < files.mse.length; j++) {
									//files.mse[j].size = 0;
									win.addFileToFilesPanel(files.mse[j], win.FilesPanelMSE);
								}
							}
							
							if (b_f.findField('EvnPrescrMse_MainDisease').getValue()) {
								Ext.QuickTips.register({
									target: b_f.findField('EvnPrescrMse_MainDisease').getEl(),
									text: b_f.findField('EvnPrescrMse_MainDisease').getValue(),
									enabled: true,
									showDelay: 5,
									trackMouse: true,
									autoShow: true
								});	
							}
							
							if (getRegionNick() != 'kz') {
								win.UslugaComplexMSEGrid.getGrid().getStore().load({
									params: { EvnPrescrMse_id: b_f.findField('EvnPrescrMse_id').getValue() }
								});
							}
							
							b_f.findField('IPRAResult_rid').setDisabled(obj.MeasuresRehabEffect_IsRecovery != 1);
							b_f.findField('IPRAResult_cid').setDisabled(obj.MeasuresRehabEffect_IsCompensation != 1);
							
							// var emp_invalidenddateindefinitely = !b_f.findField('EvnPrescrMse_InvalidEndDate').getValue() && b_f.findField('InvalidGroupType_id').getValue() > 1;
							// b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely').setValue(emp_invalidenddateindefinitely);
							// b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely').fireEvent('change', b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely'), emp_invalidenddateindefinitely);
						},
						failure: function(){
							win.getLoadMask().hide();
						}
					});
				break;
			}
		});
	},

	doSaveGridMR: function(cb, options){
		var win = this;
		var gridMR = this.MedicalRehabilitationForm.find('editformclassname', 'swMeasuresForMedicalRehabilitation')[0];
		var gridMRCount = gridMR.ViewGridPanel.getStore().getCount();
		var b_f = this.CommonForm.getForm();
		var isFirstTime = b_f.findField('EvnPrescrMse_IsFirstTime').getValue();
		var count = 0;
		var MSE_id = true;
		
		if(gridMRCount == 1){
			// может быть пустая запись
			MSE_id = gridMR.ViewGridPanel.getStore().getAt(0).get('MeasuresRehabMSE_id');
		}
		
		if( isFirstTime == 2 && (gridMRCount == 0 || !MSE_id)){
			var question = 'Не заполнены результаты проведенных мероприятий по медицинской реабилитации в соответствии с ИПРА инвалида. <br><b>Продолжить?</b>';
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						options.SaveGridMR = true;
						win.doSave(cb, options);
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: question,
				title: langs('Вопрос')
			});
		}else{
			options.SaveGridMR = true;
			win.doSave(cb, options);
		}
	},

	getInvalidFields: function(items, recnum) {
		var that = this; var fields = [];
		var recnum = (!recnum) ? 1 : recnum++;
		if (recnum > 5) return [];
		items.each(function(el) {
			if ( el.items ){
				fields = fields.concat(that.getInvalidFields(el.items, recnum));
			}else if (el.isValid && !el.isValid()){
				if(el.hiddenName) fields.push(el.hiddenName);
				if(!el.hiddenName && el.name) fields.push(el.name);
			}
		});
		return fields;
	},

	getInvalidNames: function() {
		var fields = this.getInvalidFields(this.CommonForm.getForm().items);
		console.warn('InvalidFields: ' + fields.join(', '));
	},

	doSave: function(cb, options)
	{
		var win = this;
		var frm = this.CommonForm.getForm();
		if (!options) options = {};

		var
			postFieldAllowBlank = frm.findField('Post_id').allowBlank,
			postRawValue = frm.findField('Post_id').getRawValue();
		var EvnPrescrMse_IsFirstTime = frm.findField('EvnPrescrMse_IsFirstTime').getValue();
		var MeasuresForMedicalRehabilitation = '';
		if(EvnPrescrMse_IsFirstTime == 2){
			var jsonArr=[];
			var measuresFMR=this.MedicalRehabilitationForm.find('editformclassname', 'swMeasuresForMedicalRehabilitation')[0];
			var grid=measuresFMR.getGrid();
			grid.getStore().each( function (model) {
				jsonArr.push(model.data);
			});
			if( jsonArr.length>0 ) MeasuresForMedicalRehabilitation=JSON.stringify(jsonArr);
		}
		
		//frm.findField('Post_id').setAllowBlank(true);
		if(!frm.isValid()) {
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						win.CommonForm.getFirstInvalidEl().focus(false);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			if(isDebug()) win.getInvalidNames();
			return false;
		}

		var errorString = '';
		if (Ext.isEmpty(this.PersonFrame.getFieldValue('Person_Snils'))) {
			errorString += '<li>СНИЛС</li>';
		}
		if (Ext.isEmpty(this.PersonFrame.getFieldValue('Document_begDate'))) {
			errorString += '<li>дата выдачи документа</li>';
		}

		if(getRegionNick() != 'kz'){
			if (!options.ignorePersonData && errorString !== '') {
				/*
				sw.swMsg.show({
					buttons: {yes: 'ОК', no: 'Отмена'},
					fn: function(buttonId, text, obj) {
						if (buttonId == 'yes') {
							options.ignorePersonData = true;
							win.doSave(cb, options);
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: 'Для корректной отправки направления в РЭМД должны быть указаны следующие данные о пациенте: ' + errorString + '<br>Внесите указанные данные на форме "Человек".',
					title: langs('Внимание')
				});
				*/
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						return false;
					},
					icon: Ext.Msg.ERROR,
					msg: 'Для корректной отправки направления в РЭМД должны быть указаны следующие данные о пациенте: ' + errorString + '<br>Внесите указанные данные на форме "Человек".',
					title: ERR_INVFIELDS_TIT
				});

				return false;
			}
			var vitalDataArr = [
				'PersonWeight_Weight',	//масса
				'PersonHeight_Height', //рост
				'indexWeight', // индекс массы тела
				'EvnPrescrMse_DailyPhysicDepartures', // Суточный объём физиологических отправлений
				'EvnPrescrMse_Waist',//Окружность талии
				'EvnPrescrMse_Hips', //Окружность бёдер
			]
			var countVitial = 0;
			vitalDataArr.forEach(function(item){
				var el = frm.findField(item);
				if(el && el.getValue()) countVitial++;
			});
			if(countVitial < 3){
				var fio = win.PersonFrame.getFieldValue('Person_Surname') + ' ' + win.PersonFrame.getFieldValue('Person_Firname').slice(0,1) + '.' +  win.PersonFrame.getFieldValue('Person_Secname').slice(0,1) + '.'
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						return false;
					},
					icon: Ext.Msg.ERROR,
					msg: 'У пациента '+fio+' не достаточно данных в разделе «Антропометрические данные и физиологические параметры». Необходимо заполнить минимум три из перечисленных параметров: «Масса тела», «Длина тела», «Индекс массы тела», «Суточный объём физиологических отправлений», «Окружность талии», «Окружность бёдер». Внесите недостающие данные.',
					title: ERR_INVFIELDS_TIT
				});

				return false;
			}

			if(EvnPrescrMse_IsFirstTime == 2 && !frm.findField('EvnMse_id').getValue()){
				sw.swMsg.alert('Ошибка', 'Для повторного направления необходимо заполнить сведения о результатах проведенной МСЭ. Внесите недостающие данные.');
				return false;
			}
		}

		if(getRegionNick() != 'ufa' && !options.SaveGridMR) {
			this.doSaveGridMR(cb, options);
			return false;
		}

		if ( postFieldAllowBlank == false && Ext.isEmpty(frm.findField('Post_id').getValue()) && Ext.isEmpty(postRawValue) ) {
			frm.findField('Post_id').setAllowBlank(false);
			sw.swMsg.alert('Ошибка', 'Поле "Должность" обязательно для заполнения', function() { frm.findField('Post_id').focus(250, true); });
			return false;
		}
		
		var err_focus_field = null;
		var err_fields = [];
		var check_fields = [
			['EvnPrescrMse_DiseaseHist', 'Анамнез заболевания'],
			['EvnPrescrMse_LifeHist', 'Анамнез жизни'],
			['EvnPrescrMse_State', 'Состояние гражданина при направлении на медико-социальную экспертизу']
		];
		
		for (var k in check_fields) {
			if (check_fields[k][0] && !new RegExp('[a-zа-я]', 'i').test(Ext.util.Format.stripTags(frm.findField(check_fields[k][0]).getValue()))) {
				err_fields.push('"'+check_fields[k][1]+'"');
				if (!err_focus_field) err_focus_field = check_fields[k][0];
			}
		}
		
		if (err_fields.length) {
			var err_txt = err_fields.length > 1 
				? 'Поля ' + err_fields.join(', ') + ' обязательны для заполнения'
				: 'Поле ' + err_fields[0] + ' обязательно для заполнения';
				
			sw.swMsg.alert('Ошибка', err_txt, function() {frm.findField(err_focus_field).focus(250, true)});
			return false;
		}

		var params = {};
		if (this.withCreateDirection) {
			params.withCreateDirection = 1;
			params.LpuSectionProfile_id = this.LpuSectionProfile_id || null;
		}
		params.EvnVK_id = this.EvnVKExpertForm.EvnVK_id;
		params.EvnPrescrMse_id = frm.findField('EvnPrescrMse_id').getValue();
		params.PersonEvn_id = this.PersonFrame.getFieldValue('PersonEvn_id');
		params.Server_id = this.PersonFrame.getFieldValue('Server_id');
		params.MedPersonal_sid = getGlobalOptions().medpersonal_id || null;
		params.PrescriptionStatusType_id = 2; // ?
		params.SopDiagList = Ext.util.JSON.encode(this.SopDiagListPanel.getValues());
		params.OslDiagList = Ext.util.JSON.encode(this.OslDiagListPanel.getValues());
		params.ARMType = this.ARMType;
		params.Diag_id = frm.findField('Diag_id').getValue();
		params.MeasuresForMedicalRehabilitation = MeasuresForMedicalRehabilitation;
		params.EvnPrescrMse_IsPersonInhabitation = frm.findField('EvnPrescrMse_IsPersonInhabitation').getValue();
		params.Aims = Ext.util.JSON.encode(win.ReasonAndDiagnosis.findById('AllAims_id').getValues());

		if (frm.findField('EvnPrescrMse_MainDisease').disabled) {
			params.EvnPrescrMse_MainDisease = frm.findField('EvnPrescrMse_MainDisease').getValue();
		}
		
		var uc_grid = this.UslugaComplexMSEGrid.getGrid();
		uc_grid.getStore().clearFilter();
		var UslugaComplexMSEData = getStoreRecords(uc_grid.getStore());
		params.UslugaComplexMSEData = Ext.util.JSON.encode(UslugaComplexMSEData);
		uc_grid.getStore().filterBy(function(rec) {
			return (Number(rec.get('RecordStatus_Code')) != 3);
		});

		if ( Ext.isEmpty(frm.findField('Post_id').getValue()) ) {
			params.PostNew = postRawValue;
		}
		else {
			// ищем уже существующее значение
			var id = frm.findField('Post_id').getStore().findBy(function(record) {
				return (record.get('Post_Name') == frm.findField('Post_id').getRawValue());
			});
			
			if ( id >= 0 ) {
				params.PostNew = '';
			}
			else {
				params.PostNew = frm.findField('Post_id').getRawValue().replace(/\-+|\++|\.+|\,+/ig, '').replace(/\s{2,}/ig, ' ');
				frm.findField('Post_id').clearValue();
			}
		}
		
		// Собираем атрибуты прикрепленных файлов (если есть)
		var files = [];
		this.FilesPanelVK.findBy(function(file) {
			files.push(file.settings.name+'::'+file.settings.tmp_name+'::'+file.settings.size);
		}, this.FilesPanelVK);
		if(files.length > 0) {
			params.filesVK = files.join('|');
		}
		var files = [];
		this.FilesPanelMSE.findBy(function(file) {
			files.push(file.settings.name+'::'+file.settings.tmp_name+'::'+file.settings.size);
		}, this.FilesPanelMSE);
		if(files.length > 0) {
			params.filesMSE = files.join('|');
		}
		if(win.action == 'edit' && frm.findField('EvnStatus_id').getValue() == 30){
			frm.findField('EvnStatus_id').setValue(27);
		}
		if(win.action == 'edit' && getRegionNick() == 'perm' && frm.findField('EvnStatus_id').getValue() == 32){
			// #137509 
			// Если форма открыта в режиме редактирования и для текущего направления на МСЭ установлен статус «Отказ ВК», то при сохранении статус направления на МСЭ изменяется на «Новое».
			frm.findField('EvnStatus_id').setValue(27);
		}
		if(getRegionNick() != 'kz' && !Ext.isEmpty(options['ignoreRequiredSetOfStudiesCheck']) && options['ignoreRequiredSetOfStudiesCheck'] == true){
			params['ignoreRequiredSetOfStudiesCheck'] = 1;
		}
		if(getRegionNick() != 'kz' && !Ext.isEmpty(options['ignorePersonDocumentCheck']) && options['ignorePersonDocumentCheck'] == true){
			params['ignorePersonDocumentCheck'] = 1;
		}

		if (win.PersonSidRadio0.checked) {
			frm.findField('Person_sid').clearValue();
		}

		var lm = this.getLoadMask(langs('Сохранение данных...'));
		lm.show();
		frm.submit({
			params: params,
			success: function(frm,action){
				lm.hide();
				frm.findField('Post_id').setAllowBlank(postFieldAllowBlank);
				if ( !Ext.isEmpty(postRawValue) ) {
					frm.findField('Post_id').setRawValue(postRawValue);
				}
				if(action.result.success) {
					if( cb ) {
						frm.findField('EvnPrescrMse_id').setValue(action.result.EvnPrescrMse_id);
						win.withCreateDirection = false;
						cb();
					} else {
						var data = Ext.apply(frm.getValues(false),action.result);
						data.Evn_id = action.result.EvnPrescrMse_id;
						win.onSave(data);
						win.dataSaved = true;
                        win.hide();
					}
				} else {
					sw.swMsg.alert(langs('Ошибка'), (action.result.Error_Msg)?action.result.Error_Msg:langs('Не удалось сохранить данные!'));
				}
			},
			failure: function(frm,action){
				lm.hide();
				if(getRegionNick() != 'kz' && action.result && !Ext.isEmpty(action.result.Error_Code) && action.result.Error_Code == 700) {
					var question = "Отсутствуют исследования с актуальным сроком давности из перечня исследований для данного диагноза. " + action.result.Error_Str + '<br>Продолжить сохранение?';
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								options['ignoreRequiredSetOfStudiesCheck'] = true;
								win.doSave(cb, options);
							}else{
								frm.findField('Post_id').setAllowBlank(postFieldAllowBlank);
								if ( !Ext.isEmpty(postRawValue) ) {
									frm.findField('Post_id').setRawValue(postRawValue);
								}
							}
						},
						icon: Ext.MessageBox.QUESTION,
						msg: question,
						title: langs('Вопрос')
					});
				}else if(getRegionNick() != 'kz' && action.result && !Ext.isEmpty(action.result.Error_Code) && action.result.Error_Code == 101) {
					var question = action.result.Error_Str;
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								options['ignorePersonDocumentCheck'] = true;
								win.doSave(cb, options);
							}
						},
						icon: Ext.MessageBox.QUESTION,
						msg: question,
						title: langs('Вопрос')
					});
				}else{
					frm.findField('Post_id').setAllowBlank(postFieldAllowBlank);
					if ( !Ext.isEmpty(postRawValue) ) {
						frm.findField('Post_id').setRawValue(postRawValue);
					}
				}
			}
		});
	
	},
	
	defineAction: function(callback)
	{
		var win = this;
		var EvnVK_id = this.EvnVKExpertForm.EvnVK_id;
		
		if(!win.EvnPrescrMse_id){
			// Усли не существует привязанное к данному протоколу ВК (EvnVK_id) направление на МСЭ (EvnPrescrMse_id)
			win.action = 'add';
			callback();
			return false;
		}
		Ext.Ajax.request({
			url: '/?c=Mse&m=defineActionForEvnPrescrMse',
			params: { EvnVK_id: EvnVK_id, EvnPrescrMse_id: win.EvnPrescrMse_id },
			callback: function(o, s, r){
				if(s){
					var obj = Ext.util.JSON.decode(r.responseText)[0];
					if(win.action == null){
						if(!obj) win.action = 'add';
						if(obj){
							if((obj.EvnPrescrMse_id != null && obj.EvnMse_id != null) || obj.EvnStatus_id.inlist([28,29])){
								win.action = 'view';
							} else if (obj.EvnPrescrMse_id != null) {
								win.action = 'edit';
							}
						}
					}
					callback();
				} else {
					win.getLoadMask().hide();
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось определить параметры формы!'));
					win.hide();
					return false;
				}
			}
		});
	},
	
	disableFormFields: function(isView)
	{
		this.findBy(function(field){
			if(field.xtype && !field.xtype.inlist(['panel', 'fieldset'])){
				if(field.name && field.name.inlist(['indexWeight', 'IPRARegistry_Number', 'IPRARegistry_Protocol', 'IPRARegistry_ProtocolDate'])) 
					return false;
				if(field.hiddenName && field.hiddenName.inlist(['EvnPrescrMse_IsPersonInhabitation'])) 
					return false;
				if(isView)
					field.disable();
				else
					field.enable();
			}
		});
		var grids = this.find('mainType', 'grid');
		for(var i=0; i<grids.length; i++) {
			if(isView){
				grids[i].disable();
			} else {
				grids[i].enable();
			}
		}
		
		this.UslugaComplexMSEGrid.setReadOnly(isView);
		this.UslugaComplexMSEGrid.getAction('action_rec').setDisabled(isView);
		
		if(isView){
			this.SopDiagListPanel.disable();
			this.OslDiagListPanel.disable();
			this.buttons[0].setVisible(this.ARMType == 'mse');
		} else {
			this.SopDiagListPanel.enable();
			this.OslDiagListPanel.enable();
			this.buttons[0].setVisible(true);
		}
	},
	
	diagsSetValues: function()
	{
		var diagFset = this.ReasonAndDiagnosis.find('xtype', 'fieldset')[0];
		diagFset.findBy(function(field){
			if(field.xtype == 'swdiagcombo' && field.getValue() != '' && field.getValue() != null){
				var value = field.getValue();
				field.getStore().load({
					params: { where: "where Diag_id = " + value },
					callback: function(){
						field.getStore().each(function(rec){
							if(rec.get('Diag_id') == value) {
								field.setValue(value);
								field.fireEvent('select', field, rec, 0);
								field.onChange(field, value);
							}
						});
					}
				});
			}
		});
	},
	
	setDopParams: function(DopParams)
	{
		var dp = DopParams;
		var b_f = this.CommonForm.getForm();
		if(dp.Diag_id != ''){
			var diag1_combo = b_f.findField('Diag_id');
			diag1_combo.setValue(dp.Diag_id);
			diag1_combo.getStore().load({
				callback: function(){
					diag1_combo.getStore().each(function(rec){
						if(rec.get('Diag_id') == diag1_combo.getValue())
							diag1_combo.fireEvent('select', diag1_combo, rec, 0);
					});
				},
				params: { where: "where Diag_id = " + diag1_combo.getValue() }
			});
		}
		if(dp.Diag_sid != ''){
			var diag2_combo = b_f.findField('Diag_sid');
			diag2_combo.setValue(dp.Diag_sid);
			diag2_combo.getStore().load({
				callback: function(){
					diag2_combo.getStore().each(function(rec){
						if(rec.get('Diag_id') == diag2_combo.getValue())
							diag2_combo.fireEvent('select', diag2_combo, rec, 0);
					});
				},
				params: { where: "where Diag_id = " + diag2_combo.getValue() }
			});
		}
	},
	
	getIPRAData: function()
	{
		var win = this;
		var b_f = win.CommonForm.getForm();
		Ext.Ajax.request({
			params: {
				Person_id: win.PersonFrame.personId
			},
			url: '/?c=Mse&m=getIPRAData',
			callback: function(o, s, r){
				if(s){
					var obj = Ext.util.JSON.decode(r.responseText)[0];
					if (obj) {
						b_f.findField('IPRARegistry_id').setValue(obj.IPRARegistry_id);
						b_f.findField('IPRARegistry_Number').setValue(obj.IPRARegistry_Number);
						b_f.findField('IPRARegistry_Protocol').setValue(obj.IPRARegistry_Protocol);
						b_f.findField('IPRARegistry_ProtocolDate').setValue(obj.IPRARegistry_ProtocolDate);
					}
				}
			}
		});
	},
	
	getPersData: function(cb)
	{
		var win = this;
		var b_f = win.CommonForm.getForm();
		Ext.Ajax.request({
			params: {
				Person_id: win.PersonFrame.personId,
				PersonHeight_id: b_f.findField('PersonHeight_id').getValue(),
				PersonWeight_id: b_f.findField('PersonWeight_id').getValue()
			},
			url: '/?c=Mse&m=getPersonBodyData',
			callback: function(o, s, r){
				if(s){
					var obj = Ext.util.JSON.decode(r.responseText)[0];
					b_f.setValues(obj);
					var HIsAbnorm_combo = b_f.findField('PersonHeight_IsAbnorm');
					var WIsAbnorm_combo = b_f.findField('PersonWeight_IsAbnorm');
					var rec = HIsAbnorm_combo.getStore().getById(HIsAbnorm_combo.getValue());
					if(rec) HIsAbnorm_combo.fireEvent('select', HIsAbnorm_combo, rec, HIsAbnorm_combo.getValue());
					var rec = WIsAbnorm_combo.getStore().getById(WIsAbnorm_combo.getValue());
					if(rec) WIsAbnorm_combo.fireEvent('select', WIsAbnorm_combo, rec, WIsAbnorm_combo.getValue());
					win.indexCalculation();
					if(cb)cb();
				}
			}
		});
	},
	
	savePersData: function(cb)
	{
		var win = this;
		var persform = this.EvaluationStateForm;
		var params = {};
		var i = 0;
		var j = 0;
		persform.findBy(function(field){
			if(field.xtype && field.xtype != 'button'){
				var n = (field.name) ? field.name : field.hiddenName;
				if(!n.inlist(['StateNormType_id', 'StateNormType_did', 'indexWeight'])){

					if (!field.hidden) {// не считаем скрытые поля
						if(field.isValid()) {
							params[n] = field.getValue();
						}
						i++;
					}
				}
			}
		});
		for(var p in params){j++}
		if(j<i){
			sw.swMsg.alert(langs('Ошибка'), langs('Заполните все обязательные поля!'));
			return false;
		}
		params.Person_id = this.PersonFrame.personId;
		params.Server_id = this.PersonFrame.serverId;
		params.HeightMeasureType_id = 3;
		params.WeightMeasureType_id = 3;
		params.PersonHeight_setDate = new Date().format('d.m.Y');
		params.PersonWeight_setDate = params.PersonHeight_setDate;
		
		Ext.Ajax.request({
			params: params,
			url: '/?c=Mse&m=savePersonBodyData',
			callback: function(o, s, r){
				if(s){
					var response_obj = Ext.util.JSON.decode(r.responseText);
					if (response_obj.success) {
						win.getPersData(cb);
					}
				}
			}
		});
	},
	
	onoffFieldsDependingOnWorks: function()
	{
		var IsWork_combo = this.CommonForm.getForm().findField('EvnPrescrMse_IsWork');
		var re = /Да/i;
		var fieldset = this.CommonForm.find('name', 'workFieldset')[0];
		if(fieldset) {
			fieldset.findBy(function(field){
				if(field.xtype && field.isVisible()){
					var n = (field.name)?field.name:field.hiddenName;
					if(!n.inlist(['EvnPrescrMse_IsWork', 'EvnPrescrMse_MainProf', 'EvnPrescrMse_MainProfSkill'])){
						if(re.test(IsWork_combo.getRawValue())){
							field.enable();
							field.allowBlank = false;
						} else {
							field.allowBlank = true;
							field.reset();
							field.disable();
						}
						field.validate();
					} else if (n == 'EvnPrescrMse_MainProf') {
						field.setAllowBlank(IsWork_combo.getValue() != 2);
					}
				}
			});
		}
		
		this.CommonForm.getForm().findField('OAddress_AddressText').setAllowBlank(IsWork_combo.getValue() != 2);
		
		//https://redmine.swan.perm.ru/issues/112471
		if(getRegionNick() == 'perm')
		{
			//this.CommonForm.getForm().findField('EvnPrescrMse_ExpPost').setAllowBlank(true);
			this.CommonForm.getForm().findField('EvnPrescrMse_ExpProf').setAllowBlank(true);
			this.CommonForm.getForm().findField('EvnPrescrMse_ExpSpec').setAllowBlank(true);
			this.CommonForm.getForm().findField('EvnPrescrMse_ExpSkill').setAllowBlank(true);
		}
	},
	
	stripNotePanel: function(noteName)
	{
		var p = this.CommonForm.find('name', noteName)[0];
		if(p.isVisible())
			p.setVisible(false);
		else
			p.setVisible(true);
		this.doLayout();
	},
	
	expandField: function(field_name)
	{
		var b_f = this.CommonForm.getForm(),
			field = b_f.findField(field_name);
		
		if (!field) return false;
		
		var params = {
			action: 'view',
			value: field.getValue(),
			title: field.fieldLabel
		};
		
		setTimeout(function() {
			getWnd('swHTMLEditorWindow').show(params);
		}, 200);
	},
	
	indexCalculation: function()
	{
		var weightField = this.CommonForm.getForm().findField('PersonWeight_Weight');
		var heightField = this.CommonForm.getForm().findField('PersonHeight_Height');
		var indexWeightField = this.CommonForm.getForm().findField('indexWeight');
		if(weightField.getValue() != '' && heightField.getValue() != ''){
			var idxWeightValue = (weightField.getValue()/(heightField.getValue()*heightField.getValue()/10000)).toFixed(3);
			indexWeightField.setValue(idxWeightValue);
		} else {
			indexWeightField.reset();
			return false;
		}
	},
	
	openEvnVKExpertWindow: function()
	{
		var win = this;
		if (win.EvnVKExpertForm.EvnVK_id) {
			var params = {};
			params.EvnVK_id = win.EvnVKExpertForm.EvnVK_id;
			params.MedService_id = win.EvnVKExpertForm.EvnVK_MedService_id || null;
			getWnd('swClinExWorkSelectExpertWindow').show({
				action: 'add',
				params: params,
				onHide: function()
				{
					win.findById('domef_EvnVKExpertsGrid').ViewGridPanel.getStore().load();
				}
			});
		}
	},
	
	addStick: function() {
		var Person_id = this.PersonFrame.getFieldValue('Person_id');
		var grid = this.EvnStickForm.find('mainType', 'grid')[0];
		getWnd('swEvnMseStickEditWindow').show({
			Person_id: Person_id,
			action: 'add',
			onHide: function(){
				grid.ViewActions.action_refresh.execute();
			}.createDelegate(this)
		});
	},

	visibleButtonsFMR: function(vis){
		var visible = vis || false;
		var editBtn = Ext.getCmp('editMR');
		var deleteBtn = Ext.getCmp('deleteMR');
		var addBtn = Ext.getCmp('addMR');
		var downloadBtn = Ext.getCmp('downloadMR');
		var clearBtn = Ext.getCmp('clearMR');
		var viewMR = Ext.getCmp('viewMR');
		var helpMR = Ext.getCmp('helpMR');
		if( !visible ) {
			editBtn.disable();
			addBtn.disable();
			downloadBtn.disable();
			deleteBtn.disable();
			clearBtn.disable();
			viewMR.disable();
			helpMR.disable();
		}else{
			editBtn.enable();
			addBtn.enable();
			downloadBtn.enable();
			deleteBtn.enable();
			clearBtn.enable();
			viewMR.enable();
			helpMR.enable();
		}
	},
	actionMeasuresFMR: function(action) {
		if(!action || this.action == 'view') return false;
		var EvnPrescrMse = this.CommonForm.getForm().findField('EvnPrescrMse_id');
		// if(!EvnPrescrMse.getValue()) return false;
		var measuresFMR=this.MedicalRehabilitationForm.find('editformclassname', 'swMeasuresForMedicalRehabilitation')[0];
		var grid=measuresFMR.getGrid();
		var storeFMR = grid.getStore();
		var measuresFMRList = grid.getSelectionModel().getSelected();
		var index = (measuresFMRList) ? grid.store.indexOf(measuresFMRList) : false;
		// if(!measuresFMRList) return false;
		var Person_id = this.PersonFrame.getFieldValue('Person_id');
		var params = (measuresFMRList) ? measuresFMRList.data : {};
		params.action = action;
		params.EvnPrescrMse_id = EvnPrescrMse.getValue();

		switch(action){
			case 'add':
				params.MeasuresRehabMSE_IsExport = 1;
			break;
			case 'del':
				if(index === false) return false;
				this.deleteMeasuresFMR(index);
				return false;
			break;
			case 'clear':
				this.clearMeasuresFMR(params);
				return false;
			break;
			case 'download':
				if(Person_id){
					params.Person_id = Person_id;
					this.downloadMeasuresFMR(params);
				}
				return false;
			break;
			case 'edit':
			case 'view':
				if(index === false) return false;
			break;
		}
		getWnd('swMeasuresForMedicalRehabilitation').show({
			params: params,
			action: action,
			onHide: function(data){
				if(action == 'add'){
					grid.getStore().add(data);
				}else if(action == 'edit'){
					grid.getStore().removeAt(index);
					grid.getStore().insert(index, data);
				}
				// grid.getStore().reload();
			}.createDelegate(this)
		});
	},

	deleteMeasuresFMR: function(MeasuresRehabMSE) {
		var measuresFMR=this.MedicalRehabilitationForm.find('editformclassname', 'swMeasuresForMedicalRehabilitation')[0];
		var grid=measuresFMR.getGrid();
		var index = MeasuresRehabMSE;
		var question = langs('Удалить выбранную запись?');
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					grid.getStore().removeAt(index);
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: langs('Вопрос')
		});
	},

	clearMeasuresFMR: function(data) {
		var measuresFMR=this.MedicalRehabilitationForm.find('editformclassname', 'swMeasuresForMedicalRehabilitation')[0];
		var grid=measuresFMR.getGrid();
		var storeFMR = grid.getStore();
		var question = 'Удалить все загруженные мероприятия из регистра ИПРА?';

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					storeFMR.each(function(rec, index) { 
						var r = rec.data;
						if(r.MeasuresRehabMSE_IsExport && r.MeasuresRehabMSE_IsExport==2){
							// storeFMR.removeAt(index);
							storeFMR.remove(rec);
						}
					})					
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: langs('Вопрос')
		});
	},

	downloadMeasuresFMR: function(data) {
		if(!data['Person_id']) return false;
		var params = {
			Person_id: data['Person_id']
		};
		var measuresFMR=this.MedicalRehabilitationForm.find('editformclassname', 'swMeasuresForMedicalRehabilitation')[0];
		var grid=measuresFMR.getGrid();		
		var url = '/?c=MeasuresRehab&m=downloadIPRAinMeasuresFMR';
		var question = 'При загрузке результатов мероприятий ранее загруженные из регистра ИПРА данные будут удалены. <br><b>Продолжить?</b>';

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								var store = grid.getStore();
								store.each(function(record) {
									if (record.get('MeasuresRehabMSE_IsExport') == 2){
										store.remove(record);
									}
								});
								var obj = Ext.util.JSON.decode(response.responseText);
								if(obj.length>0){
									obj.forEach(function(rec){
										var params = {
											MeasuresRehabMSE_BegDate: rec.MeasuresRehab_setDate,
											MeasuresRehabMSE_EndDate: rec.Evn_disDT,
											MeasuresRehabMSE_Type: rec.MeasuresRehabType_Name,
											MeasuresRehabMSE_SubType: rec.MeasuresRehabSubType_Name,
											MeasuresRehabMSE_Name: rec.MeasuresRehab_Name,
											MeasuresRehabMSE_Result: rec.MeasuresRehabResult_Name,
											MeasuresRehabMSE_IsExport: 2
										}	
										store.add( new Array(new Ext.data.Record(params)) );
									});
								}
							}
						},
						params: params,
						url: url
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: langs('Вопрос')
		});
	},
	
	printEvnPrescrMse: function()
	{
		var field = this.CommonForm.getForm().findField('EvnPrescrMse_id');
		if(field.getValue()==null) return false;

		if ( getRegionNick() == 'kz' ) {
			printBirt({
				'Report_FileName': 'DirectionMSE_f088u.rptdesign',
				'Report_Params': '&paramEvnPrescrMse_id=' + field.getValue(),
				'Report_Format': 'pdf'
			});
		}
		else {
			var params = '&EvnPrescrMse_id=' + field.getValue();
			printBirt({
				'Report_FileName': 'printEvnPrescrMse.rptdesign',
				'Report_Params': params,
				'Report_Format': 'html'
			});
		}
	},

	printEvnPrescrMseRefuse: function()
	{
		var field = this.CommonForm.getForm().findField('EvnPrescrMse_id');
		if(field.getValue()==null) return false;
		if(getRegionNick() != 'vologda') return false;

		var params = '&EvnPrescrMse_id=' + field.getValue();
		printBirt({
			'Report_FileName': 'EvnPrescrMSERefuse.rptdesign',
			'Report_Params': params,
			'Report_Format': 'pdf'
		});
	},
	
	addEvt: function( el, evtName, fn) {
		if( el.attachEvent ) {
			el.attachEvent('on' + evtName, fn);
		} else if( el.addEventListener ) {
			el.addEventListener(evtName, fn, false);
		}
	},
	
	deputyData: {},
	
	setDeputyKind: function() {
		if (this.action != 'add') return false;
		var win = this,
			personFrame = this.PersonFrame,
			bf = this.CommonForm.getForm(),
			c = bf.findField('Person_sid');
			
		var DeputyPerson_id = personFrame.getFieldValue('DeputyPerson_id');
		if (DeputyPerson_id) {
			c.setValue(DeputyPerson_id);
		}
		
	},
	
	getDeputyKind: function(PersonSid) {
		var win = this;
		var bf = this.CommonForm.getForm(),
			personFrame = this.PersonFrame,
			c = bf.findField('Person_sid'),
			deputyField = bf.findField('DeputyKind_id');
        var Person_sid = c.getValue();
        if(Ext.isEmpty(Person_sid)&&PersonSid){
            Person_sid = PersonSid
        }
        if (!Ext.isEmpty(Person_sid)) {
            Ext.Ajax.request({
                url: '/?c=Mse&m=getDeputyKind',
                params: {
                    Person_id: personFrame.getFieldValue('Person_id'),
                    Person_pid: c.getValue()
                },
                callback: function(o, s, r) {
                    var obj = Ext.util.JSON.decode(r.responseText)[0];
                    log(obj,'sdfs')
                    if( obj ) {
                        this.deputyData = obj;
						c.getStore().loadData([{
							Person_id: obj[c.valueField],
							Person_Fio: obj.Person_Fio
						}]);
                        c.setValue(obj[c.valueField]);
                        c.setRawValue(obj.Person_Fio);
                        deputyField.setValue(obj[deputyField.valueField]);
						if(win.action != 'view')
							bf.findField('DeputyKind_id').setDisabled(false);
						else
							bf.findField('DeputyKind_id').setDisabled(true);
                    } else {
                        //c.reset();
                        deputyField.reset();
                    }
                }.createDelegate(this)
            });
        }
	},

	setMilitaryKind: function() {
		var win = this;
		var b_f = this.CommonForm.getForm(),
			personFrame = this.PersonFrame;

		if (personFrame.getFieldValue('Person_Birthday')) {
			var age = swGetPersonAge(personFrame.getFieldValue('Person_Birthday'), b_f.findField('EvnPrescrMse_issueDT').getValue());
			if (age < 18 && (Ext.isEmpty(b_f.findField('MilitaryKind_id').getValue()) || win.action == 'add')) {
				b_f.findField('MilitaryKind_id').setValue(2);
			}
		}
	},

	setFieldsVisible: function() {
		
		if (getRegionNick() == 'kz') return false;
		
		var win = this, 
			b_f = this.CommonForm.getForm(),
			EvnPrescrMse_issueDT = b_f.findField('EvnPrescrMse_issueDT').getValue() || new Date(),
			isVisible = EvnPrescrMse_issueDT < new Date(2019,7,1);
		
		var fields = [
			'EvnPrescrMse_ExpProf',
			'EvnPrescrMse_ExpSpec',
			'EvnPrescrMse_ExpSkill',
			'EvnPrescrMse_MainProfSkill',
			'PersonWeight_IsAbnorm',
			'WeightAbnormType_id',
			'PersonHeight_IsAbnorm',
			'HeightAbnormType_id',
			'StateNormType_id',
			'StateNormType_did',
		];
		
		fields.forEach(function(field){
			b_f.findField(field).setContainerVisible(isVisible);

			if (isVisible == false) {
				b_f.findField(field).setAllowBlank(true);
			}
			else if (field.inlist(['StateNormType_id', 'StateNormType_did'])) {
				b_f.findField(field).setAllowBlank(getRegionNick() == 'perm');
			}
		});
	},

	setPhysicalDevelopmentVis: function() {
		var win = this;
		var b_f = this.CommonForm.getForm(),
			personFrame = this.PersonFrame;

		if (personFrame.getFieldValue('Person_Birthday')) {
			var age = swGetPersonAge(personFrame.getFieldValue('Person_Birthday'), b_f.findField('EvnPrescrMse_issueDT').getValue());
			if (age <= 3 && getRegionNick() != 'kz') {
				b_f.findField('EvnPrescrMse_WeightBirth').showContainer();
				b_f.findField('EvnPrescrMse_WeightBirth').setAllowBlank(false);
				b_f.findField('EvnPrescrMse_PhysicalDevelopment').showContainer();
				b_f.findField('EvnPrescrMse_PhysicalDevelopment').setAllowBlank(false);
				Ext.Ajax.request({
					callback: function (options, success, response) {
						// Загружаем списки измерений массы и длины
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.length > 0) {
								response_obj = response_obj[0];
								b_f.findField('EvnPrescrMse_WeightBirth').setValue(response_obj.PersonNewBorn_Weight);
							}
						}
						else {
							sw.swMsg.alert('Ошибка', 'При загрузке сведений о новорожденном возникли ошибки');
						}
					}.createDelegate(this),
					params: {
						Person_id: win.PersonFrame.personId
					},
					url: '/?c=PersonNewBorn&m=loadPersonNewBornData'
				});
			} else {
				b_f.findField('EvnPrescrMse_WeightBirth').hideContainer();
				b_f.findField('EvnPrescrMse_WeightBirth').setAllowBlank(true);
				b_f.findField('EvnPrescrMse_PhysicalDevelopment').hideContainer();
				b_f.findField('EvnPrescrMse_PhysicalDevelopment').setAllowBlank(true);
			}
		}
	},
	
	saveDeputyKind: function() {
		var bf = this.CommonForm.getForm(),
			c = bf.findField('Person_sid'),
			personFrame = this.PersonFrame,
			deputyField = bf.findField('DeputyKind_id');
		// Сначала надо проверить изменились ли данные по законному представителю и его статусу
		if( (this.deputyData.Person_id && this.deputyData.Person_id == c.getValue()) && (this.deputyData.DeputyKind_id && this.deputyData.DeputyKind_id == deputyField.getValue()))
			return false; // так как данные не изменились

        if (Ext.isEmpty(c.getValue())) {
            sw.swMsg.alert(langs('Внимание'), langs('Не выбран Законный представитель'));
            return false;
        } else {
            Ext.Ajax.request({
                url: '/?c=Mse&m=saveDeputyKind',
                params: {
                    Person_id: personFrame.getFieldValue('Person_id'),
                    Person_pid: c.getValue(),
                    DeputyKind_id: deputyField.getValue()
                },
                callback: function(o, s, r) {
                    this.getDeputyKind();
                }.createDelegate(this)
            });
        }

	},
	
	deleteDeputyKind: function() {
		var personFrame = this.PersonFrame;
		
		Ext.Ajax.request({
			url: '/?c=Mse&m=deleteDeputyKind',
			params: {
				Person_id: personFrame.getFieldValue('Person_id')
			},
			callback: function(o, s, r) {
				this.getDeputyKind();
			}.createDelegate(this)
		});
	},
	getPersonId: function() {
		return this.PersonFrame.personId;
	},
	evnMseFieldset_ClearValue: function(){
		var b_f = this.CommonForm.getForm();
		var evnMseFieldset = this.findById('evnMseFieldset');
		var evnPrescrMse_IsFirstTime = b_f.findField('EvnPrescrMse_IsFirstTime'); //первично повторно
		evnMseFieldset.items.filterBy(function(el) {
			if(!el.items && el.xtype != 'panel') {
				if(el.hiddenName && el.hiddenName == 'InvalidGroupType_id') {
					el.setValue(1);
				}else{
					el.setValue(null);
				}
			}
		});
		b_f.findField('EvnPrescrMse_InvalidEndDate').setValue(null);
		b_f.findField('EvnPrescrMse_InvalidPercent').setValue(null);
		if(evnPrescrMse_IsFirstTime.getValue() != 2) evnMseFieldset.hide();
		this.evnMseFieldset_setAllowBlank();
	},
	////// начало части решения https://jira.is-mis.ru/browse/PROMEDWEB-10212 /////
	// Управление параметрами, требующие контроля
	// при переключении Первично/Вторично
	// Согласно https://jira.is-mis.ru/browse/PROMEDWEB-10212
	// от 05.06.2020 -18.06.2020 Забуньян Иван 
	controlFields_10212: {
		// Для всех регионов кроме Казахстана
		all: [
			// Степень утраты профессиональной трудоспособности (%)
			'EvnPrescrMse_InvalidPercent',
			// Срок, на который установлена степень утраты профессиональной трудоспособности'
			'ProfDisabilityPeriod_id',
			// Дата, до которой установлена степень утраты профессиональной трудоспособности'
			'EvnPrescrMse_ProfDisabilityEndDate',
			// Идентификатор - Обратное направление на МСЭ
			'EvnMse_id'
		],
		// Поля не требующие обнуления
		noNeedNull:[
			// Идентификатор - Обратное направление на МСЭ
			'EvnMse_id'
		],
		//Для Казахстана
		kz: ['EvnPrescrMse_InvalidPercent'],

		//Индикатор Казахстана
		isKz:getRegionNick() === 'kz',

		// Получение объекта по его полю
		fPoint:function(item){
			return this.controlForm.findField(item);
		},

		// Включить объект инпута
		enable: function (fPoint) {
			fPoint.enable();
		},

		// Отключить объект инпута
		disable:function (fPoint,noNeedNull){
			fPoint.disable();
			fPoint.setAllowBlank(true);
			!noNeedNull&&fPoint.setRawValue(null);
		},
		// Значение контролируемого поля
		controlPointValue:null,

		// Форма в которой контролируем параметры
		controlForm:null,

		// Полный контроль всех регионов, включая Казахстан
		fullControl:function(controlPointValue){
			this.controlPointValue=controlPointValue;
			// Выбор точки - Казахстан или все другие регионы
			var point=this.isKz
				?this.kz
				:this.all;
			// Контролируем каждый объект по полю
			point.forEach(this.doControl,this);
		},

		// Контролируем состояние объекта по полю
		doControl:function(item){
			var fPoint=this.fPoint(item);
			switch(this.controlPointValue)
			{
				case 1:
					this.disable(fPoint,this.noNeedNull.indexOf('item')>-1);
					break;
				case 2:
					this.enable(fPoint);
					if(!this.isKz)
					{
						fPoint.setAllowBlank(false);
						fPoint.validate();
					}
					break;
			}
			},
		
		// Запускаем процесс контроля по значению
		startByFormFieldValue(form,fieldValue){
			this.controlForm=form;
			this.fullControl(fieldValue);
		},
	},
	evnMseFieldset_setAllowBlank: function(IsFirstTimeValue){
		var b_f = this.CommonForm.getForm();
		var evnMseFieldset = this.findById('evnMseFieldset');
		/**
		 * Внесены изменения, согласно решения задачи https://jira.is-mis.ru/browse/PROMEDWEB-10212
		 * 05.06.2020-19.06.2020
		 * т.к. теперь контролируются здесь: this.controlFields_10212
		 */
		var evnPrescrMse_IsFirstTime = b_f.findField('EvnPrescrMse_IsFirstTime'); //первично повторно
		var evnPrescrMse_IsFirstTimeValue=IsFirstTimeValue!==undefined?IsFirstTimeValue:evnPrescrMse_IsFirstTime.getValue();
		if(getRegionNick() == 'kz' || !evnMseFieldset) 
		{
			this.controlFields_10212.startByFormFieldValue(b_f,evnPrescrMse_IsFirstTimeValue);
			return false;
		}
		
		var invalidGroupTypeCombo = b_f.findField('InvalidGroupType_id'); //ивалидность
		// PROMEDWEB-10212	var InvalidCouseTypeCombo = b_f.findField('InvalidCouseType_id'); //причина инвалидности
		// PROMEDWEB-10212	var evnPrescrMse_InvalidPercent = b_f.findField('EvnPrescrMse_InvalidPercent'); //Степень утраты профессиональной трудоспособности (%)
		// PROMEDWEB-10212 var profDisabilityPeriodCombo =  b_f.findField('ProfDisabilityPeriod_id'); // Срок, на который установлена степень утраты профессиональной трудоспособности'
		// PROMEDWEB-10212 var evnPrescrMse_ProfDisabilityEndDate = b_f.findField('EvnPrescrMse_ProfDisabilityEndDate'); // Дата, до которой установлена степень утраты профессиональной трудоспособности'
		var evnPrescrMse_InvalidEndDate = b_f.findField('EvnPrescrMse_InvalidEndDate'); // Дата, до которой установлена инвалидность
		var evnPrescrMse_InvalidDate =b_f.findField('EvnPrescrMse_InvalidDate'); //Дата установления инвалидности
		var invalidPeriodTypeCombo = b_f.findField('InvalidPeriodType_id'); //Срок, на который установлена инвалидность

		evnPrescrMse_InvalidEndDate.setAllowBlank(true);
		if(evnMseFieldset.isVisible() && evnPrescrMse_IsFirstTimeValue == 2){
			if(invalidGroupTypeCombo.getValue() > 1){
				evnPrescrMse_InvalidDate.showContainer();
				evnPrescrMse_InvalidDate.setAllowBlank(false);
				invalidPeriodTypeCombo.showContainer();
				invalidPeriodTypeCombo.setAllowBlank(false);
				evnPrescrMse_InvalidEndDate.showContainer();
			}else{
				evnPrescrMse_InvalidDate.hideContainer();
				evnPrescrMse_InvalidDate.setAllowBlank(true);
				invalidPeriodTypeCombo.hideContainer();
				invalidPeriodTypeCombo.setAllowBlank(true);
				evnPrescrMse_InvalidEndDate.hideContainer();
			}
		}else{
			evnPrescrMse_InvalidDate.hideContainer();
			evnPrescrMse_InvalidDate.setAllowBlank(true);
			invalidPeriodTypeCombo.hideContainer();
			invalidPeriodTypeCombo.setAllowBlank(true);
			evnPrescrMse_InvalidEndDate.hideContainer();
		}
		this.controlFields_10212.startByFormFieldValue(b_f,evnPrescrMse_IsFirstTimeValue);
	},
	initComponent: function()
	{
		var cur_win = this;
		
		this.UslugaComplexMSEGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			id: 'UslugaComplexMSEGrid',
			collapsible: true,
			autoScroll: true,
			border: true,
			autoLoadData: false,
			useEmptyRecord: false,
			contextmenu: false,
			height: 200,
			toolbar: true,
			hidden: getRegionNick() == 'kz',
			actions: [
				{name:'action_add', handler: function() { cur_win.addUslugaComplexMSE(); }},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', handler: function() { cur_win.openUslugaComplexMSE(); }},
				{name:'action_delete', handler: function() { cur_win.deleteUslugaComplexMSE(); }},
				{name:'action_refresh', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true}
			],
			stringfields: [
				{ name: 'EvnPrescrMseLink_id', type: 'int', hidden: true, key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'EvnUsluga_id', type: 'int', hidden: true },
				{ name: 'UslugaComplex_id', type: 'int', hidden: true },
				{ name: 'EvnClass_SysNick', type: 'string', hidden: true },
				{ name: 'ParentClass_SysNick', type: 'string', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'rownumberer', type: 'rownumberer', header: '№', width: 40 },
				{ name: 'EvnUsluga_setDate', type: 'string',  header: 'Дата выполнения', width: 120 },
				{ name: 'UslugaComplex_Name', type: 'string',  header: 'Услуга', id: 'autoexpand' },
				{ name: 'EvnUsluga_isActual', type: 'checkbox',  header: 'Актуальность', width: 120, hidden: getRegionNick() == 'kz' }
			],
			dataUrl: '/?c=Mse&m=loadUslugaComplexMSEList',
			title: 'Обследования и исследования'
		});
		
		this.EvnStatusHistoryGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			id: 'EvnStatusHistoryGrid',
			collapsible: true,
			autoScroll: true,
			border: true,
			autoLoadData: false,
			useEmptyRecord: false,
			contextmenu: false,
			height: 150,
			toolbar: false,
			stringfields: [
				{ name: 'EvnStatusHistory_id', type: 'int', hidden: true, key: true },
				{ name: 'EvnStatusHistory_begDate', type: 'string',  header: 'Дата возврата', width: 120 },
				{ name: 'MedService_Name', type: 'string',  header: 'Служба', width: 300 },
				{ name: 'EvnStatusHistory_Cause', type: 'string',  header: 'Описание причины', id: 'autoexpand', width: 300 },
				{ name: 'MedService_id', type: 'int',  hidden: true }
			],
			dataUrl: '/?c=Mse&m=getEvnPrescrMseStatusHistory',
			totalProperty: 'totalCount',
			title: 'Причины отказа/возврата'
		});
		
		this.UploadDialog = new Ext.ux.UploadDialog.Dialog({
			modal: true,
			title: langs('Прикрепление файлов'),
			url: '/?c=Mse&m=uploadFiles',
			reset_on_hide: true,
			allow_close_on_upload: true,
			listeners: {
				uploadsuccess: function(dialog, filename, data) {
					var panel = this.UploadDialog.panel;
					this.uploadSuccess(dialog, data, panel);
				}.createDelegate(this)
			},
			upload_autostart: false
		});

		this.FilesPanelVK = new Ext.Panel({
			layout: 'form',
			title: 'МО: ' + langs('Список приложенных документов: <span style="color: gray;">нет приложенных документов</span>'),
			baseTitle: 'МО: Список приложенных документов',
			filePanelType: 'vk',
			autoHeight: true,
			buttons: [
				{
					handler: function() {
						this.showUploadDialog(this.FilesPanelVK);
					}.createDelegate(this),
					iconCls: 'add16',
					id: 'uploadbutton',
					text: langs('Прикрепить документы'),
					align: 'left'
				},
				'-'
			],
			animCollapse: false,
			listeners: {
				beforeexpand: function() {
					return this.getCountFiles(this.FilesPanelVK) > 0;
				}.createDelegate(this),
				collapse: function() {
					this.syncSize();
				}.createDelegate(this),
				expand: function() {
					this.syncSize();
				}.createDelegate(this)
			},
			floatable: false,
			style: 'margin: 3px;',
			bodyStyle: 'padding: 5px;',
			titleCollapse: true,
			items: []
		});

		this.FilesPanelMSE = new Ext.Panel({
			layout: 'form',
			title: 'Бюро МСЭ: ' + langs('Список приложенных документов: <span style="color: gray;">нет приложенных документов</span>'),
			baseTitle: 'Бюро МСЭ: Список приложенных документов',
			filePanelType: 'mse',
			autoHeight: true,
			buttons: [
				{
					handler: function() {
						this.showUploadDialog(this.FilesPanelMSE);
					}.createDelegate(this),
					iconCls: 'add16',
					id: 'uploadbutton',
					text: langs('Прикрепить документы'),
					align: 'left'
				},
				'-'
			],
			animCollapse: false,
			listeners: {
				beforeexpand: function() {
					return this.getCountFiles(this.FilesPanelMSE) > 0;
				}.createDelegate(this),
				collapse: function() {
					this.syncSize();
				}.createDelegate(this),
				expand: function() {
					this.syncSize();
				}.createDelegate(this)
			},
			floatable: false,
			style: 'margin: 3px;',
			bodyStyle: 'padding: 5px;',
			titleCollapse: true,
			items: []
		});
		
		this.initTrigger = function(){
			var ts = this.trigger.select('.x-form-trigger', true);
			this.wrap.setStyle('overflow', 'hidden');
			var triggerField = this;
			ts.each(function(t, all, index){
				t.hide = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = 'none';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				t.show = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = '';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				var triggerIndex = 'Trigger'+(index+1);
				if(this['hide'+triggerIndex]){
					t.dom.style.display = 'none';
				}
				t.on("click", this['on'+triggerIndex+'Click'], this, {preventDefault:true});
				t.addClassOnOver('x-form-trigger-over');
				t.addClassOnClick('x-form-trigger-click');
			}, this);
			this.triggers = ts.elements;
		}
		
		this.directoryYears = [];
		for(var i=(new Date().format('Y')); i>=1950; i--) {
			this.directoryYears.push([i, i]);
		}
		
		this.PersonFrame = new sw.Promed.PersonInfoPanel({
			floatable: false,
			collapsed: true,
			region: 'north',
			title: langs('Загрузка...'),
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			titleCollapse: true,
			collapsible: true
		});
	
		this.PatientForm = new sw.Promed.Panel({
			title: langs('Пациент'),
			autoHeight: true,
			bodyStyle: 'padding: 5px;',
			collapsible: true,
			items: [
				{
					layout: 'form',
					width: 960,
					labelWidth: 180,
					labelAlign: 'right',
					border: false,
					items: [
						{
							xtype: 'hidden',
							name: 'accessType'
						}, {
							xtype: 'hidden',
							name: 'EvnPrescrMse_id'
						}, {
							xtype: 'hidden',
							name: 'EvnPrescrMse_pid'
						}, {
							xtype: 'hidden',
							name: 'MedService_id'
						}, {
							xtype: 'hidden',
							name: 'TimetableMedService_id'
						}, {
							xtype: 'hidden',
							name: 'EvnPrescrMse_setDT',
							value: Ext.util.Format.date(new Date(), 'd.m.Y')
						}, {
							xtype: 'hidden',
							name: 'EvnStatus_id'
						}, {
							xtype: 'hidden',
							name: 'Lpu_gid'
						}, {
							xtype: 'hidden',
							name: 'EvnQueue_id'
						}, {
							xtype: 'combo',
							mode: 'local',
							hideEmptyRow: true,
							hiddenName: 'EvnPrescrMse_IsFirstTime',
							width: 250,
							store: new Ext.data.SimpleStore(
							{
								key: 'a',
								autoLoad: true,
								fields:
								[
									{name:'a', type:'int'},
									{name:'b', type:'string'}
								],
								data: [[1,langs('Первично')], [2,langs('Повторно')]]
							}),
							triggerAction: 'all',
							editable: false,
							listeners: {
								'render': function(c) {
									[1,2].indexOf(c.getValue())===-1&&
									c.setValue(1);
								},
								'select': function(c, r, idx) {
									var medicalRehabilitation = this.MedicalRehabilitationForm;
									var b_f = this.CommonForm.getForm();
									var evnmse_combo = b_f.findField('EvnMse_id');
									var cValue=c.getValue();
									cur_win.evnMseFieldset_setAllowBlank(cValue);
									if(c.getValue() == 2){
										if (getRegionNick() != 'kz') {
											this.findById('evnMseFieldset').show();
											this.findById('evnMseFieldset').doLayout();
											b_f.findField('InvalidGroupType_id').setAllowBlank(this.action == 'view');
											evnmse_combo.getStore().load({
												params: { Person_id: this.PersonFrame.personId },
												callback: function() {
													if (this.action == 'add') {
														if( evnmse_combo.getStore().getCount() > 0 ) {
															evnmse_combo.setValue( evnmse_combo.getStore().getAt(0).get('EvnMse_id') );
															evnmse_combo.fireEvent('select', evnmse_combo, evnmse_combo.getStore().getAt(0), 0);
														}
													} else {
														evnmse_combo.setValue( evnmse_combo.getValue() );
													}
												}.createDelegate(this)
											});
										}

										medicalRehabilitation.show();
										
										if(getRegionNick() != 'ufa'){
											var MedicalRehabilitationGrid = this.MedicalRehabilitationForm.find('editformclassname', 'swMeasuresForMedicalRehabilitation')[0];
											var gridMR = MedicalRehabilitationGrid.ViewGridPanel;

											
											var EvnPrescrMse = this.CommonForm.getForm().findField('EvnPrescrMse_id');
											var EvnPrescrMse_id = EvnPrescrMse.getValue();											

											// medicalRehabilitation.collapse();
											b_f.findField('MeasuresRehabEffect_Comment').setAllowBlank(getRegionNick() == 'kz'); // Ошибка #169221

											if( !EvnPrescrMse_id ) return false;
											gridMR.getStore().baseParams.EvnPrescrMse_id = EvnPrescrMse_id;
											gridMR.getStore().load();
										}
									} else {
										this.findById('evnMseFieldset').hide();
										b_f.findField('InvalidGroupType_id').setAllowBlank(true);
										// if(!medicalRehabilitation.hidden) medicalRehabilitation.disable();
										medicalRehabilitation.hide();
										b_f.findField('MeasuresRehabEffect_Comment').setAllowBlank(true);
									}


								}.createDelegate(this)
							},
							allowBlank: false,
							displayField: 'b',
							valueField: 'a',
							fieldLabel: langs('Направляется')
						},
						{
							xtype: 'swyesnocombo',
							width: 250,
							hidden: getRegionNick() == 'kz',
							hideLabel: getRegionNick() == 'kz',
							allowBlank: getRegionNick() == 'kz',
							hiddenName: 'EvnPrescrMse_IsCanAppear',
							fieldLabel: langs('Может явиться в бюро')
						}, 
						{
							fieldLabel: 'Законный представитель',
							xtype: 'radiogroup',
							width: 270,
							columns: 2,
							hidden: getRegionNick() == 'kz',
							hideLabel: getRegionNick() == 'kz',
							name: 'Person_sidType',
							items: [cur_win.PersonSidRadio0 = new Ext.form.Radio({
								name: 'Person_sidType',
								boxLabel: 'Отсутствует',
								inputValue: null,
								checked: true
							}), cur_win.PersonSidRadio1 = new Ext.form.Radio({
								name: 'Person_sidType',
								boxLabel: 'Физическое лицо',
								inputValue: 1
							}), cur_win.PersonSidRadio2 = new Ext.form.Radio({
								name: 'Person_sidType',
								boxLabel: 'Юридическое лицо',
								inputValue: 2
							})],
							listeners: {
								'change': function (radioGroup, radioBtn) {
									var b_f = cur_win.CommonForm.getForm();
									if (radioBtn.inputValue == 1) {
										b_f.findField('Org_sid').hideContainer();
										b_f.findField('Org_sid').setAllowBlank(true);
										b_f.findField('Org_sid').clearValue();
										b_f.findField('Person_sid').showContainer();
										b_f.findField('Person_sid').setAllowBlank(getRegionNick() == 'kz');
										b_f.findField('DocumentAuthority_id').setAllowBlank(getRegionNick() == 'kz');
										b_f.findField('EvnPrescrMse_DocumentSer').setAllowBlank(getRegionNick() == 'kz');
										b_f.findField('EvnPrescrMse_DocumentNum').setAllowBlank(getRegionNick() == 'kz');
										b_f.findField('EvnPrescrMse_DocumentIssue').setAllowBlank(getRegionNick() == 'kz');
										b_f.findField('EvnPrescrMse_DocumentDate').setAllowBlank(getRegionNick() == 'kz');
										cur_win.findById('deputyDocFieldset').show();
										b_f.findField('DeputyKind_id').showContainer(true);
									} else if (radioBtn.inputValue == 2) {
										b_f.findField('Org_sid').showContainer();
										b_f.findField('Org_sid').setAllowBlank(getRegionNick() == 'kz');
										b_f.findField('Person_sid').hideContainer();
										b_f.findField('Person_sid').setAllowBlank(true);
										b_f.findField('Person_sid').clearValue();
										b_f.findField('DocumentAuthority_id').setAllowBlank(true);
										b_f.findField('EvnPrescrMse_DocumentSer').setAllowBlank(true);
										b_f.findField('EvnPrescrMse_DocumentNum').setAllowBlank(true);
										b_f.findField('EvnPrescrMse_DocumentIssue').setAllowBlank(true);
										b_f.findField('EvnPrescrMse_DocumentDate').setAllowBlank(true);
										cur_win.findById('deputyDocFieldset').hide();
										b_f.findField('DeputyKind_id').showContainer(true);
									} else {
										b_f.findField('Org_sid').hideContainer();
										b_f.findField('Org_sid').setAllowBlank(true);
										b_f.findField('Org_sid').clearValue();
										b_f.findField('Person_sid').hideContainer();
										b_f.findField('Person_sid').setAllowBlank(true);
										b_f.findField('Person_sid').clearValue();
										b_f.findField('DocumentAuthority_id').setAllowBlank(true);
										b_f.findField('EvnPrescrMse_DocumentSer').setAllowBlank(true);
										b_f.findField('EvnPrescrMse_DocumentNum').setAllowBlank(true);
										b_f.findField('EvnPrescrMse_DocumentIssue').setAllowBlank(true);
										b_f.findField('EvnPrescrMse_DocumentDate').setAllowBlank(true);
										cur_win.findById('deputyDocFieldset').hide();
										b_f.findField('DeputyKind_id').hideContainer(true);
									}
								}
							}
						},
						{
							layout: 'column',
							border: false,
							defaults: {
								border: false
							},
							width: 800,
							items: [
								{
									layout: 'form',
									width: 440,
									items: [
										{
											editable: false,
											hiddenName: 'Person_sid',
											tabIndex: TABINDEX_PEF + 29,
											width: 250,
											fieldLabel: (getRegionNick() == 'kz' ? langs('Законный представитель') : ''),
											labelSeparator: (getRegionNick() == 'kz' ? ':' : ''),
											xtype: 'swpersoncombo',
											onTrigger1Click: function() {
												var combo = this;

												var
													autoSearch = false,
													fio = new Array();

												if ( !Ext.isEmpty(combo.getRawValue()) ) {
													fio = combo.getRawValue().split(' ');

													// Запускать поиск автоматически, если заданы хотя бы фамилия и имя
													if ( !Ext.isEmpty(fio[0]) && !Ext.isEmpty(fio[1]) ) {
														autoSearch = true;
													}
												}

												getWnd('swPersonSearchWindow').show({
													autoSearch: autoSearch,
													onSelect: function(personData) {
														if ( personData.Person_id > 0 )
														{
															Ext.Ajax.request({
																url: '/?c=Mse&m=checkPersonDocument',
																params: {Person_id: personData.Person_id},
																success: function(response, options) {
																	var responseObj = Ext.util.JSON.decode(response.responseText);
																	if (!responseObj[0] || Ext.isEmpty(responseObj[0].Document_id)) {
																		combo.setValue('');
																		sw.swMsg.alert(langs('Ошибка'), langs('У представителя не заполнен документ удостоверяющий личность'));
																		return false;
																	} else {
																		PersonSurName_SurName = Ext.isEmpty(personData.PersonSurName_SurName)?'':personData.PersonSurName_SurName;
																		PersonFirName_FirName = Ext.isEmpty(personData.PersonFirName_FirName)?'':personData.PersonFirName_FirName;
																		PersonSecName_SecName = Ext.isEmpty(personData.PersonSecName_SecName)?'':personData.PersonSecName_SecName;

																		combo.getStore().loadData([{
																			Person_id: personData.Person_id,
																			Person_Fio: PersonSurName_SurName + ' ' + PersonFirName_FirName + ' ' + PersonSecName_SecName
																		}]);
																		combo.setValue(personData.Person_id);
																		combo.collapse();
																		combo.focus(true, 500);
																		combo.fireEvent('change', combo);
																	}
																}
															});
														}
														getWnd('swPersonSearchWindow').hide();
													},
													onClose: function() {combo.focus(true, 500)},
													personSurname: !Ext.isEmpty(fio[0]) ? fio[0] : '',
													personFirname: !Ext.isEmpty(fio[1]) ? fio[1] : '',
													personSecname: !Ext.isEmpty(fio[2]) ? fio[2] : ''
												});
											},
											onTrigger2Click: function() {
                                                cur_win.CommonForm.getForm().findField('DeputyKind_id').setDisabled(true);
												this.deleteDeputyKind();
											}.createDelegate(this),
											enableKeyEvents: true,
											listeners: {
												'change': function(combo) {
													if (Ext.isEmpty(combo.getValue())) {
                                                        cur_win.CommonForm.getForm().findField('DeputyKind_id').setDisabled(true);
                                                    } else {
                                                        cur_win.CommonForm.getForm().findField('DeputyKind_id').setDisabled(false);
                                                    }
												},
												'keydown': function( inp, e ) {
													if ( e.F4 == e.getKey() )
													{
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
														if ( Ext.isIE )
														{
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														inp.onTrigger1Click();
														return false;
													}
												},
												'keyup': function(inp, e) {
													if ( e.F4 == e.getKey() )
													{
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
														if ( Ext.isIE )
														{
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														return false;
													}
												}
											}
										},
										{
											layout: 'form',
											labelWidth: 180,
											id: 'deputyDocFieldset',
											border: false,
											items: [{
												xtype: 'swcommonsprcombo',
												comboSubject: 'DocumentAuthority',
												width: 250,
												listWidth: 300,
												hiddenName: 'DocumentAuthority_id',
												fieldLabel: langs('Документ, удостоверяющий полномочия законного (уполномоченного) представителя')
											}, {
												layout: 'column',
												border: false,
												defaults: {
													border: false
												},
												items: [{
													layout: 'form',
													labelWidth: 180,
													width: 250,
													items: [{
														xtype: 'textfield',
														width: 60,
														name: 'EvnPrescrMse_DocumentSer',
														fieldLabel: langs('Серия')
													}]
												}, {
													layout: 'form',
													labelWidth: 60,
													width: 185,
													items: [{
														xtype: 'textfield',
														width: 120,
														maskRe: /\d/,
														name: 'EvnPrescrMse_DocumentNum',
														fieldLabel: langs('Номер')
													}]
												}]
											}, {
												xtype: 'textfield',
												width: 250,
												name: 'EvnPrescrMse_DocumentIssue',
												fieldLabel: langs('Кем выдан')
											}, {
												xtype: 'swdatefield',
												name: 'EvnPrescrMse_DocumentDate',
												format: 'd.m.Y',
												plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
												fieldLabel: langs('Дата выдачи')
											}]
										},
										{
											xtype: 'sworgcomboex',
											width: 250,
											listeners: {
												select: function(combo, record, idx) {
													combo.fireEvent('change', combo, record.get('Org_id'), idx);
												},
												change: function(combo, newValue, oldValue) {
													if (!Ext.isEmpty(newValue)) {
														Ext.Ajax.request({
															url: '/?c=Org&m=getOrgData',
															params: {Org_id: newValue},
															success: function(response, options){
																var responseObj = Ext.util.JSON.decode(response.responseText)[0];
																if (Ext.isEmpty(responseObj.Org_OGRN) || Ext.isEmpty(responseObj.PAddress_Address)) {
																	combo.setValue('');
																	sw.swMsg.alert(langs('Внимание'),langs('Для выбранной организации должны быть заполнены поля «Фактический адрес» и «ОГРН»'));
																	return false;
																}
															}
														});
													}
												}
											},
											hiddenName: 'Org_sid',
											disabled: true,
											fieldLabel: langs('Наименование')
										}/*,
										{
											xtype: 'swpersoncomboex',
											hiddenName: 'Person_sid',
											width: 250,
	                                        editable: false,
											initTrigger: this.initTrigger,
											triggerConfig: {
												tag:'span', cls:'x-form-twin-triggers', cn:[
												{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"},
												{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-clear-trigger"}
											]},
											fieldLabel: langs('Законный представитель'),
                                            listeners: {
                                                change: function(combo, newValue, oldValue){
                                                    if (Ext.isEmpty(combo.getValue())) {
                                                        cur_win.CommonForm.getForm().findField('DeputyKind_id').setDisabled(true);
                                                    } else {
                                                        cur_win.CommonForm.getForm().findField('DeputyKind_id').setDisabled(false);
                                                    }
                                                }
                                            },
											onTrigger1Click: function() {
												var combo = this.CommonForm.getForm().findField('Person_sid');
												if (combo.disabled) return false;
												
												getWnd('swPersonSearchWindow').show({
													onHide: function() {
														combo.focus(false);
													},
													onSelect: function(personData) {
														var store = combo.getStore();
														combo.setValue(personData[combo.valueField]);
														combo.hiddenValue = personData[combo.valueField];
														combo.setRawValue(
															personData.PersonSurName_SurName + " " +
															personData.PersonFirName_FirName + " " +
															personData.PersonSecName_SecName
															);
														getWnd('swPersonSearchWindow').hide();
                                                        combo.fireEvent('change', combo);
														this.saveDeputyKind();
													}.createDelegate(this)
												});
											}.createDelegate(this),
											onTrigger2Click: function() {
                                                cur_win.CommonForm.getForm().findField('DeputyKind_id').setDisabled(true);
												this.deleteDeputyKind();
											}.createDelegate(this)
										}*/
									]
								}, {
									layout: 'form',
									labelWidth: 150,
									items: [
										{
											fieldLabel: langs('Статус представителя'),
											listeners: {
												select: this.saveDeputyKind.createDelegate(this)
											},
                                            disabled:true,
											xtype: 'swdeputykindcombo'
										}
									]
								}
							]
						}, 
						{
							layout: 'form',
							labelWidth: 180,
							id: 'deputyDocFieldset',
							border: false,
							hidden: getRegionNick() == 'kz',
							items: [{
								fieldLabel: langs('Гражданин находится'),
								xtype: 'radiogroup',
								width: 320,
								columns: 2,
								name: 'EvnPrescrMse_IsPersonInhabitationRb',
								items: [{
									name: 'EvnPrescrMse_IsPersonInhabitationRb',
									boxLabel: 'В организации',
									inputValue: 1,
									checked: true
								}, {
									name: 'EvnPrescrMse_IsPersonInhabitationRb',
									boxLabel: 'По месту жительства',
									inputValue: 2
								}],
								listeners: {
									'change': function (radioGroup, radioBtn) {
										var b_f = cur_win.CommonForm.getForm();
										if (radioBtn.inputValue == 1) {
											b_f.findField('Org_gid').showContainer();
											b_f.findField('Org_gid').setAllowBlank(false);
											b_f.findField('EvnPrescrMse_IsPersonInhabitation').hideContainer();
											b_f.findField('EvnPrescrMse_IsPersonInhabitation').setValue(1);
										} else {
											b_f.findField('Org_gid').hideContainer();
											b_f.findField('Org_gid').setValue('');
											b_f.findField('Org_gid').setAllowBlank(true);
											b_f.findField('EvnPrescrMse_IsPersonInhabitation').showContainer();
											b_f.findField('EvnPrescrMse_IsPersonInhabitation').setValue(2);
										}
									}
								}
							}, 
							{
								xtype: 'sworgcomboex',
								width: 250,
								listeners: {
									select: function(combo, record, idx) {
										combo.fireEvent('change', combo, record.get('Org_id'), idx);
									},
									change: function(combo, newValue, oldValue) {
										if (!Ext.isEmpty(newValue)) {
											Ext.Ajax.request({
												url: '/?c=Org&m=getOrgData',
												params: {Org_id: newValue},
												success: function(response, options){
													var responseObj = Ext.util.JSON.decode(response.responseText)[0];
													if (Ext.isEmpty(responseObj.Org_OGRN) || Ext.isEmpty(responseObj.PAddress_Address)) {
														combo.setValue('');
														sw.swMsg.alert(langs('Внимание'),langs('Для выбранной организации должны быть заполнены поля «Фактический адрес» и «ОГРН»'));
														return false;
													}
												}
											});
										}
									}
								},
								hiddenName: 'Org_gid',
								fieldLabel: '',
								labelSeparator: ''
							}, 
							{
								xtype: 'swyesnocombo',
								width: 250,
								disabled: true,
								hiddenName: 'EvnPrescrMse_IsPersonInhabitation',
								fieldLabel: '',
								labelSeparator: ''
							}]
						},
						{
							layout: 'form',
							width: 450,
							labelWidth: 350,
							border: false,
							labelAlign: 'right',
							hidden: getRegionNick() == 'kz',
							items: [
								{
									xtype: 'swyesnocombo',
									width: 80,
									allowBlank: getRegionNick() == 'kz',
									hiddenName: 'EvnPrescrMse_IsPalliative',
									fieldLabel: langs('Гражданин нуждается в паллиативной медицинской помощи')
								}
							]
						}, 
						{
							xtype: 'fieldset',
							autoHeight: true,
							labelWidth: 350,
							hidden: true,
							defaults: {
								border: false
							},
							collapsible: true,
							id: 'evnMseFieldset',
							title: langs('Сведения о результатах предыдущей медико-социальной экспертизы'),
							listeners:{
								'hide': function(){
									 cur_win.findById('evnMseFieldset').items.filterBy(function(el) {
										if(!el.items && el.xtype != 'panel') {
											el.setAllowBlank(true);
										}
									});
								},
								'show': function(){
									//...
								}
							},
							items: [{
								xtype: 'swbaselocalcombo',
								width: 250,
								listWidth: 400,
								triggerAction: 'all',
								mode: 'local',
								editable: false,
								hiddenName: 'EvnMse_id',
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'EvnMse_id'
									}, [{
										mapping: 'EvnMse_id',
										name: 'EvnMse_id',
										type: 'int'
									}, {
										mapping: 'EvnMse_Name',
										name: 'EvnMse_Name',
										type: 'string'
									}, {
										mapping: 'EvnMse_NumAct',
										name: 'EvnMse_NumAct',
										type: 'string'
									}, {
										mapping: 'EvnMse_SendStickDate',
										name: 'EvnMse_SendStickDate',
										type: 'string'
									}, {
										mapping: 'EvnMse_ReExamDate',
										name: 'EvnMse_ReExamDate',
										type: 'string'
									}, {
										mapping: 'InvalidGroupType_id',
										name: 'InvalidGroupType_id',
										type: 'string'
									}, {
										mapping: 'InvalidCouseType_id',
										name: 'InvalidCouseType_id',
										type: 'string'
									}, {
										mapping: 'ProfDisabilityPeriod_id',
										name: 'ProfDisabilityPeriod_id',
										type: 'string'
									}, {
										mapping: 'EvnMse_ProfDisabilityEndDate',
										name: 'EvnMse_ProfDisabilityEndDate',
										type: 'string'
									}, {
										mapping: 'HealthAbnorm_Name',
										name: 'HealthAbnorm_Name',
										type: 'string'
									}, {
										mapping: 'EvnMse_InvalidPercent',
										name: 'EvnMse_InvalidPercent',
										type: 'string'
									}]),
									url: '/?c=Mse&m=getPrevEvnMseList'
								}),
								valueField: 'EvnMse_id',
								displayField: 'EvnMse_Name',
								fieldLabel: langs('Обратный талон МСЭ'),
								listeners: {
									select: function(combo, record, idx) {
										var b_f = cur_win.CommonForm.getForm();
										if (record && record.get('EvnMse_id')) {
											var ProfDisabilityAgainPercent = '';
											var i = 1;
											combo.getStore().findBy(function (rec) {
												ProfDisabilityAgainPercent +=  i + 
												'. Вид нарушения здоровья: '+rec.get('HealthAbnorm_Name') +
												', Степень утраты проф. трудоспособности: '+rec.get('EvnMse_InvalidPercent') +
												', Дата, до которой установлена степень утраты профессиональной трудоспособности: '+rec.get('EvnMse_ProfDisabilityEndDate') +
												".\n";
												i++;
											});
											b_f.findField('InvalidGroupType_id').setValue(record.get('InvalidGroupType_id'));
											b_f.findField('EvnPrescrMse_InvalidEndDate').setValue(record.get('EvnMse_ReExamDate'));
											// b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely').setValue(!record.get('EvnMse_ReExamDate') && record.get('InvalidGroupType_id') > 1);
											b_f.findField('InvalidCouseType_id').setValue(record.get('InvalidCouseType_id'));
											b_f.findField('ProfDisabilityPeriod_id').setValue(record.get('ProfDisabilityPeriod_id'));
											b_f.findField('EvnPrescrMse_ProfDisabilityEndDate').setValue(record.get('EvnMse_ProfDisabilityEndDate'));
											b_f.findField('EvnPrescrMse_ProfDisabilityAgainPercent').setValue(ProfDisabilityAgainPercent);
										} else {
											b_f.findField('InvalidGroupType_id').setValue('');
											b_f.findField('EvnPrescrMse_InvalidEndDate').setValue('');
											// b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely').setValue(false);
											b_f.findField('InvalidCouseType_id').setValue('');
											b_f.findField('ProfDisabilityPeriod_id').setValue('');
											b_f.findField('EvnPrescrMse_ProfDisabilityEndDate').setValue('');
											b_f.findField('EvnPrescrMse_ProfDisabilityAgainPercent').setValue('');
										}
										
										b_f.findField('InvalidGroupType_id').fireEvent('change', b_f.findField('InvalidGroupType_id'), b_f.findField('InvalidGroupType_id').getValue());
										cur_win.evnMseFieldset_setAllowBlank();
									}
								},
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<tpl if="values.EvnMse_id != \'\'">',
									'{EvnMse_NumAct} от {EvnMse_SendStickDate}&nbsp;',
									'<tpl if="values.EvnMse_ReExamDate != \'\'">',
									'Дата переосвидетельствования: {EvnMse_ReExamDate}',
									'</tpl>',
									'</tpl>',
									'&nbsp;',
									'</div></tpl>'
								)
							}, {
								xtype: 'swinvalidgrouptypecombo',
								mode: 'local',
								anchor: '',
								width: 250,
								listWidth: 250,
								hiddenName: 'InvalidGroupType_id',
								triggerAction: 'all',
								editable: false,
								fieldLabel: langs('Инвалидность'),
								listeners: {
									select: function(combo, record, idx) {
										combo.fireEvent('change', combo, record.get('InvalidGroupType_id'), idx);
									},
									change: function(combo, newValue, oldValue) {
										var b_f = cur_win.CommonForm.getForm();
										if (newValue > 1) {
											cur_win.findById(cur_win.id + 'InvalidEndDateGroup').show();
											// b_f.findField('EvnPrescrMse_InvalidEndDate').setAllowBlank(false);
											b_f.findField('EvnPrescrMse_InvalidPeriod').showContainer();
											b_f.findField('EvnPrescrMse_InvalidPeriod').setAllowBlank(false);
											b_f.findField('InvalidCouseType_id').showContainer();
											b_f.findField('InvalidCouseType_id').setAllowBlank(false);

											b_f.findField('EvnPrescrMse_InvalidDate').showContainer();
											b_f.findField('EvnPrescrMse_InvalidDate').setAllowBlank(false);
											b_f.findField('InvalidPeriodType_id').showContainer();
											b_f.findField('InvalidPeriodType_id').setAllowBlank(false);
										} else {
											cur_win.findById(cur_win.id + 'InvalidEndDateGroup').hide();
											// b_f.findField('EvnPrescrMse_InvalidEndDate').setAllowBlank(true);
											b_f.findField('EvnPrescrMse_InvalidPeriod').hideContainer();
											b_f.findField('EvnPrescrMse_InvalidPeriod').setAllowBlank(true);
											b_f.findField('InvalidCouseType_id').hideContainer();
											b_f.findField('InvalidCouseType_id').setAllowBlank(true);

											b_f.findField('EvnPrescrMse_InvalidDate').hideContainer();
											b_f.findField('EvnPrescrMse_InvalidDate').setAllowBlank(true);
											b_f.findField('InvalidPeriodType_id').hideContainer();
											b_f.findField('InvalidPeriodType_id').setAllowBlank(true);
										}
										b_f.findField('InvalidCouseType_id').fireEvent('change', b_f.findField('InvalidCouseType_id'), b_f.findField('InvalidCouseType_id').getValue());
										// b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely').fireEvent('change', b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely'), b_f.findField('EvnPrescrMse_InvalidEndDateIndefinitely').getValue());
										cur_win.evnMseFieldset_setAllowBlank();
									}
								}
							}, 
							{
								xtype: 'swdatefield',
								name: 'EvnPrescrMse_InvalidDate',
								format: 'd.m.Y',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								fieldLabel: langs('Дата установления инвалидности'),
								hidden: getRegionNick() == 'kz',
							},{
								xtype: 'swinvalidperiodtypecombo',
								mode: 'local',
								anchor: '',
								width: 250,
								listWidth: 250,
								hiddenName: 'InvalidPeriodType_id',
								triggerAction: 'all',
								editable: false,
								fieldLabel: langs('Срок, на который установлена инвалидность'),
								hidden: getRegionNick() == 'kz',
							}, {
								layout: 'column',
								border: false,
								id: cur_win.id + 'InvalidEndDateGroup',
								defaults: {border: false},
								labelAlign: 'right',
								items: [{
									layout: 'form',
									labelWidth: 350,
									width: 470,
									items: [{
										xtype: 'swdatefield',
										name: 'EvnPrescrMse_InvalidEndDate',
										allowBlank: true,
										format: 'd.m.Y',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										fieldLabel: langs('Дата, до которой установлена инвалидность')
									}]
								}, /*{
									layout: 'form',
									items: [{
										labelSeparator: '',
										xtype: 'checkbox',
										name: 'EvnPrescrMse_InvalidEndDateIndefinitely',
										hideLabel: true,
										listeners: {
											change: function(checkbox, c){
												var b_f = this.CommonForm.getForm();
												b_f.findField('EvnPrescrMse_InvalidEndDate').setDisabled(c || cur_win.action == 'view');
												if (!!c) {
													b_f.findField('EvnPrescrMse_InvalidEndDate').setValue('');
												}
											}.createDelegate(this)
										},
										boxLabel: 'Бессрочно'
									}]
								}*/]
							}, {
								xtype: 'numberfield',
								minValue: 1,
								width: 50,
								allowDecimals: false,
								allowNegative: false,
								name: 'EvnPrescrMse_InvalidPeriod',
								fieldLabel: langs('Период, в течение которого гражданин находился на инвалидности, лет')
							}, {
								comboSubject: 'InvalidCouseType',
								xtype: 'swcommonsprcombo',
								anchor: '100%',
								listWidth: 500,
								hiddenName: 'InvalidCouseType_id',
								fieldLabel: langs('Причина инвалидности'),
								listeners: {
									select: function(combo, record, idx) {
										combo.fireEvent('change', combo, record.get('InvalidGroupType_id'), idx);
									},
									change: function(combo, newValue, oldValue) {
										var b_f = cur_win.CommonForm.getForm();
										if (newValue == 16) {
											b_f.findField('EvnPrescrMse_InvalidCouseAnother').showContainer();
											b_f.findField('EvnPrescrMse_InvalidCouseAnother').setAllowBlank(false);
										} else {
											b_f.findField('EvnPrescrMse_InvalidCouseAnother').hideContainer();
											b_f.findField('EvnPrescrMse_InvalidCouseAnother').setAllowBlank(true);
										}
										cur_win.evnMseFieldset_setAllowBlank();
									}
								}
							}, {
								xtype: 'textfield',
								anchor: '100%',
								listWidth: 500,
								name: 'EvnPrescrMse_InvalidCouseAnother',
								fieldLabel: langs('Иные причины инвалидности')
							}, {
								xtype: 'textfield',
								anchor: '100%',
								listWidth: 500,
								name: 'EvnPrescrMse_InvalidCouseAnotherLaw',
								fieldLabel: langs('Причина инвалидности (другое законодательство) <a title="Читать примечание" href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).stripNotePanel(&quot;note10&quot;);">?</a>')
							}, {
								name: 'note10',
								hidden: true,
								xtype: 'panel',
								bodyStyle: 'padding: 3px; background-color: #eee; border: 1px solid #000;',
								html: langs('<b>Примечание:</b> Указать формулировки причин инвалидности, установленные в ')+
									langs(' соответствии с законодательством, действовавшим на момент установления инвалидности')
							}, {
								layout: 'form',
								width: 450,
								labelWidth: 350,
								border: false,
								labelAlign: 'right',
								items: [
									{
										xtype: 'numberfield',
										maxValue: 100,
										minValue: 0,
										disabled: true,
										width: 50,
										name: 'EvnPrescrMse_InvalidPercent',
										fieldLabel: langs('Степень утраты профессиональной трудоспособности (%)'),
										listeners: {
											'blur': function(){
												cur_win.evnMseFieldset_setAllowBlank();
											}
										}
									}
								]
							}, {
								comboSubject: 'ProfDisabilityPeriod',
								xtype: 'swcommonsprcombo',
								anchor: '100%',
								listWidth: 500,
								hidden: getRegionNick() == 'kz',
								hideLabel: getRegionNick() == 'kz',
								hiddenName: 'ProfDisabilityPeriod_id',
								fieldLabel: langs('Срок, на который установлена степень утраты профессиональной трудоспособности'),
								listeners: {
									'select': function(){
										cur_win.evnMseFieldset_setAllowBlank();
									}
								}
							}, {
								xtype: 'swdatefield',
								name: 'EvnPrescrMse_ProfDisabilityEndDate',
								hidden: getRegionNick() == 'kz',
								hideLabel: getRegionNick() == 'kz',
								fieldLabel: langs('Дата, до которой установлена степень утраты профессиональной трудоспособности'),
								listeners: {
									'change': function(){
										cur_win.evnMseFieldset_setAllowBlank();
									}
								}
							}, {
								xtype: 'textarea',
								anchor: '100%',
								listWidth: 500,
								name: 'EvnPrescrMse_ProfDisabilityAgainPercent',
								fieldLabel: langs('Степень утраты профессиональной трудоспособности (в процентах), установленная по повторным несчастным случаям на производстве и профессиональным заболеваниям')
							}]
						},
					]
				},
				{
					layout: 'form',
					width: 960,
					border: false,
					labelAlign: 'right',
					items: [
						{
							xtype: 'fieldset',
							autoHeight: true,
							defaults: {
								border: false
							},
							collapsible: true,
							layout: 'column',
							name: 'workFieldset',
							title: langs('Работа на момент направления на медико-социальную экспертизу'),
							items: [
								{
									layout: 'form',
									labelWidth: 170,
									width: 400,
									items: [
										{
											xtype: 'swcommonsprcombo',
											comboSubject: 'YesNo',
											allowBlank: false,
											listeners: {
												select: function(c, r, idx)	{
													this.onoffFieldsDependingOnWorks();
												}.createDelegate(this),
												render: function() {
													this.setValue(1);
													cur_win.onoffFieldsDependingOnWorks();
												}
											},
											hiddenName: 'EvnPrescrMse_IsWork',
											anchor: '100%',
											fieldLabel: langs('Работает')
										},
										{
											xtype: 'swpostcombo',
											minChars: 0,
											queryDelay: 1,
											hiddenName: 'Post_id',
											listeners: {
												render: function(c){
													c.getStore().load();
												}
											},
											anchor: '100%',
											selectOnFocus: true,
											forceSelection: false,
											disabled: true,
											fieldLabel: langs('Должность')
										},
										/*{
											xtype: 'swokvedcombo',
											hiddenName: 'Okved_id',
											disabled: true,
											anchor: '100%',
											fieldLabel: langs('Профессия')
										}*/
										{
											name: 'EvnPrescrMse_Prof',
											fieldLabel: langs('Профессия'),
											anchor: '100%',
											disabled: true,
											onTriggerClick: function() {
												var thisField = this.CommonForm.getForm().findField('EvnPrescrMse_Prof');
												if( thisField.disabled ) return false;
												var postField = this.CommonForm.getForm().findField('Post_id');
												thisField.setValue(postField.getRawValue());
											}.createDelegate(this),
											triggerClass: 'x-form-equil-trigger',
											xtype: 'trigger'
										},
										{
											xtype: 'textfield',
											anchor: '100%',
											name: 'EvnPrescrMse_Spec',
											disabled: true,
											fieldLabel: langs('Специальность')
										},
										{
											xtype: 'textfield',
											anchor: '100%',
											name: 'EvnPrescrMse_Skill',
											disabled: true,
											fieldLabel: langs('Квалификация')
										},
										{
											xtype: 'sworgcomboex',
											anchor: '100%',
											listeners: {
												select: function(combo, record, idx) {
													combo.fireEvent('change', combo, record.get('Org_id'), idx);
												},
                                                change: function(combo, newValue, oldValue) {
													var adress_combo = cur_win.CommonForm.getForm().findField('OAddress_AddressText');
													adress_combo.setValue('');
													
													if (!newValue) return false;
													
													adress_combo.loadDataByOrgId(newValue, function() {
														sw.swMsg.alert(langs('Предупреждение'), langs('У выбранной организации отсутствует адрес!<br />Выберите адрес в поле "Адрес организации".'));
													});
												}
											},
											hiddenName: 'Org_id',
											disabled: true,
											fieldLabel: langs('Наименование организации')
										},
										{
											xtype: 'textfield',
											anchor: '100%',
											maxLength: 32,
											name: 'EvnPrescrMse_MainProf',
											fieldLabel: langs('Осн. профессия (спец-ть)')
										}
									]
								},
								{
									layout: 'form',
									width: 530,
									labelWidth: 250,
									items: [
										{
											xtype: 'textfield',
											anchor: '100%',
											name: 'EvnPrescrMse_CondWork',
											disabled: true,
											maxLength: 128,
											fieldLabel: langs('Условия и характер выполняемого труда')
										},
										{
											xtype: 'numberfield',
											anchor: '100%',
											disabled: true,
											name: 'EvnPrescrMse_ExpPost',
											fieldLabel: langs('Стаж работы по должности (лет)')
										},
										{
											xtype: 'numberfield',
											anchor: '100%',
											disabled: true,
											name: 'EvnPrescrMse_ExpProf',
											fieldLabel: langs('Стаж работы по профессии (лет)')
										},
										{
											xtype: 'numberfield',
											anchor: '100%',
											disabled: true,
											name: 'EvnPrescrMse_ExpSpec',
											fieldLabel: langs('Стаж работы по специальности (лет)')
										},
										{
											xtype: 'numberfield',
											anchor: '100%',
											disabled: true,
											name: 'EvnPrescrMse_ExpSkill',
											fieldLabel: langs('Стаж работы по квалификации (лет)')
										},
										{xtype: 'hidden', name: 'OAddress_id'},
										{xtype: 'hidden', name: 'OAddress_Zip'},
										{xtype: 'hidden', name: 'OKLCountry_id'},
										{xtype: 'hidden', name: 'OKLRGN_id'},
										{xtype: 'hidden', name: 'OKLSubRGN_id'},
										{xtype: 'hidden', name: 'OKLCity_id'},
										{xtype: 'hidden', name: 'OKLTown_id'},
										{xtype: 'hidden', name: 'OKLStreet_id'},
										{xtype: 'hidden', name: 'OAddress_House'},
										{xtype: 'hidden', name: 'OAddress_Corpus'},
										{xtype: 'hidden', name: 'OAddress_Flat'},
										{xtype: 'hidden', name: 'OAddress_Address'},
										new sw.Promed.SwBaseRemoteCombo({
											fieldLabel: langs('Адрес организации'),
											name: 'OAddress_AddressText',
											readOnly: true,
											anchor: '100%',
											trigger1Class: 'x-form-search-trigger',
											trigger2Class: 'x-form-clear-trigger',
											enableKeyEvents: true,
											listeners: {
												'keydown': function (inp, e) {
													if (e.F4 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey)) {
														if (e.F4 == e.getKey())
															inp.onTrigger1Click();
														if (e.DELETE == e.getKey() && e.altKey)
															inp.onTrigger2Click();
														if (e.browserEvent.stopPropagation)
															e.browserEvent.stopPropagation();
														else
															e.browserEvent.cancelBubble = true;
														if (e.browserEvent.preventDefault)
															e.browserEvent.preventDefault();
														else
															e.browserEvent.returnValue = false;
														e.browserEvent.returnValue = false;
														e.returnValue = false;
														if (Ext.isIE) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														return false;
													}
												},
												'keyup': function (inp, e) {
													if (e.F4 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey)) {
														if (e.browserEvent.stopPropagation)
															e.browserEvent.stopPropagation();
														else
															e.browserEvent.cancelBubble = true;
														if (e.browserEvent.preventDefault)
															e.browserEvent.preventDefault();
														else
															e.browserEvent.returnValue = false;
														e.browserEvent.returnValue = false;
														e.returnValue = false;
														if (Ext.isIE) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														return false;
													}
												}
											},
											onTrigger2Click: function () {
												if (this.disabled) return false;
												var b_f = Ext.getCmp('swDirectionOnMseEditForm').CommonForm.getForm();
												b_f.findField('OAddress_Zip').setValue('');
												b_f.findField('OKLCountry_id').setValue('');
												b_f.findField('OKLRGN_id').setValue('');
												b_f.findField('OKLSubRGN_id').setValue('');
												b_f.findField('OKLCity_id').setValue('');
												b_f.findField('OKLTown_id').setValue('');
												b_f.findField('OKLStreet_id').setValue('');
												b_f.findField('OAddress_House').setValue('');
												b_f.findField('OAddress_Corpus').setValue('');
												b_f.findField('OAddress_Flat').setValue('');
												b_f.findField('OAddress_Address').setValue('');
												b_f.findField('OAddress_AddressText').setValue('');
											},
											onTrigger1Click: function () {
												if (this.disabled) return false;
												var b_f = Ext.getCmp('swDirectionOnMseEditForm').CommonForm.getForm();
												getWnd('swAddressEditWindow').show({
													fields: {
														Address_ZipEdit: b_f.findField('OAddress_Zip').getValue(),
														KLCountry_idEdit: b_f.findField('OKLCountry_id').getValue(),
														KLRgn_idEdit: b_f.findField('OKLRGN_id').getValue(),
														KLSubRGN_idEdit: b_f.findField('OKLSubRGN_id').getValue(),
														KLCity_idEdit: b_f.findField('OKLCity_id').getValue(),
														KLTown_idEdit: b_f.findField('OKLTown_id').getValue(),
														KLStreet_idEdit: b_f.findField('OKLStreet_id').getValue(),
														Address_HouseEdit: b_f.findField('OAddress_House').getValue(),
														Address_CorpusEdit: b_f.findField('OAddress_Corpus').getValue(),
														Address_FlatEdit: b_f.findField('OAddress_Flat').getValue(),
														Address_AddressEdit: b_f.findField('OAddress_Address').getValue()
													},
													callback: function (values) {
														b_f.findField('OAddress_Zip').setValue(values.Address_ZipEdit);
														b_f.findField('OKLCountry_id').setValue(values.KLCountry_idEdit);
														b_f.findField('OKLRGN_id').setValue(values.KLRgn_idEdit);
														b_f.findField('OKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
														b_f.findField('OKLCity_id').setValue(values.KLCity_idEdit);
														b_f.findField('OKLTown_id').setValue(values.KLTown_idEdit);
														b_f.findField('OKLStreet_id').setValue(values.KLStreet_idEdit);
														b_f.findField('OAddress_House').setValue(values.Address_HouseEdit);
														b_f.findField('OAddress_Corpus').setValue(values.Address_CorpusEdit);
														b_f.findField('OAddress_Flat').setValue(values.Address_FlatEdit);
														b_f.findField('OAddress_Address').setValue(values.Address_AddressEdit);
														b_f.findField('OAddress_AddressText').setValue(values.Address_AddressEdit);
														b_f.findField('OAddress_AddressText').focus(true, 500);
													},
													onClose: function () {
														b_f.findField('OAddress_AddressText').focus(true, 500);
													}
												})
											},
											loadDataByOrgId: function(Org_id, callback) {
												var b_f = Ext.getCmp('swDirectionOnMseEditForm').CommonForm.getForm();
												Ext.Ajax.request({
													params: {Org_id: Org_id},
													url: '/?c=Mse&m=getOrgAddress',
													success: function(response){
														var resp = Ext.util.JSON.decode(response.responseText);
														if (!resp || !resp[0]) {
															if (callback) callback();
															return false;
														}
														b_f.findField('OAddress_Zip').setValue(resp[0].Address_Zip);
														b_f.findField('OKLCountry_id').setValue(resp[0].KLCountry_id);
														b_f.findField('OKLRGN_id').setValue(resp[0].KLRGN_id);
														b_f.findField('OKLSubRGN_id').setValue(resp[0].KLSubRGN_id);
														b_f.findField('OKLCity_id').setValue(resp[0].KLCity_id);
														b_f.findField('OKLTown_id').setValue(resp[0].KLTown_id);
														b_f.findField('OKLStreet_id').setValue(resp[0].KLStreet_id);
														b_f.findField('OAddress_House').setValue(resp[0].Address_House);
														b_f.findField('OAddress_Corpus').setValue(resp[0].Address_Corpus);
														b_f.findField('OAddress_Flat').setValue(resp[0].Address_Flat);
														b_f.findField('OAddress_AddressText').setValue(resp[0].Address_AddressText);
														b_f.findField('OAddress_Address').setValue(resp[0].Address_Address);
													}
												});
											}
										}),
										{
											xtype: 'textfield',
											anchor: '100%',
											maxLength: 32,
											name: 'EvnPrescrMse_MainProfSkill',
											fieldLabel: langs('Квалификация по основной профессии')
										}
									]
								}
							]
						}
					]
				},
				{
					layout: 'form',
					width: 850,
					border: false,
					labelAlign: 'right',
					labelWidth: 170,
					items: [
						{
							xtype: 'fieldset',
							title: langs('Обучение'),
							autoHeight: true,
							collapsible: true,
							items: [
								{
									layout: 'column',
									border: false,
									defaults: {
										border: false
									},
									items: [
										{
											layout: 'form',
											labelWidth: 170,
											width: 400,
											items: [
												{
													xtype: 'sworgcomboex',
													anchor: '100%',
													onTriggerClick: function() {
														if (this.disabled) return false;
														var combo = this;
														getWnd('swOrgSearchWindow').show({
															onHide: function() {
																combo.focus(false);
															},
															onSelect: function(orgData) {
																combo.getStore().removeAll();
																combo.getStore().loadData([{
																	Org_id: orgData.Org_id,
																	Org_Name: orgData.Org_Name,
																	Org_ColoredName: ''
																}]);
																combo.setValue(orgData[combo.valueField]);
																var index = combo.getStore().findBy(function(rec) { return rec.get('Org_id') == orgData.Org_id; });
																if (index == -1)
																	return false;

																var record = combo.getStore().getAt(index);
																combo.fireEvent('select', combo, record, 0);
																combo.fireEvent('change', combo, orgData.Org_id);
																getWnd('swOrgSearchWindow').hide();
															}
														});
													},
													listeners: {
														change: function(combo, newValue, oldValue) {
															var b_f = Ext.getCmp('swDirectionOnMseEditForm').CommonForm.getForm();
															var adress_combo = b_f.findField('EAddress_AddressText');
															adress_combo.setValue('');
															adress_combo.setAllowBlank(!newValue);
															b_f.findField('EvnPrescrMse_Dop').setAllowBlank(!newValue);
															b_f.findField('LearnGroupType_id').setAllowBlank(!newValue);
															
															if (!newValue) return false;
															
															adress_combo.loadDataByOrgId(newValue);
														}
													},
													hiddenName: 'Org_did',
													fieldLabel: langs('Наименование учреждения')
												},
												{
													xtype: 'textfield',
													anchor: '100%',
													name: 'EvnPrescrMse_Dop',
													fieldLabel: langs('Группа/Класс/Курс'),
                                                    hidden:  getRegionNick().inlist['kz']
												},
												/*{
													xtype: 'swokvedcombo',
													anchor: '100%',
													hiddenName: 'Okved_did',
													fieldLabel: langs('Профессия (специальность)')
												}*/
												{
													xtype: 'textfield',
													anchor: '100%',
													name: 'EvnPrescrMse_ProfTraining',
													fieldLabel: langs('Профессия (специальность)')
												}
											]
										},
										{
											layout: 'form',
											width: 420,
											labelWidth: 150,
											items: [
												{xtype: 'hidden', name: 'EAddress_id'},
												{xtype: 'hidden', name: 'EAddress_Zip'},
												{xtype: 'hidden', name: 'EKLCountry_id'},
												{xtype: 'hidden', name: 'EKLRGN_id'},
												{xtype: 'hidden', name: 'EKLSubRGN_id'},
												{xtype: 'hidden', name: 'EKLCity_id'},
												{xtype: 'hidden', name: 'EKLTown_id'},
												{xtype: 'hidden', name: 'EKLStreet_id'},
												{xtype: 'hidden', name: 'EAddress_House'},
												{xtype: 'hidden', name: 'EAddress_Corpus'},
												{xtype: 'hidden', name: 'EAddress_Flat'},
												{xtype: 'hidden', name: 'EAddress_Address'},
												new sw.Promed.SwBaseRemoteCombo({
													fieldLabel: langs('Адрес учреждения'),
													hiddenName: 'EAddress_AddressText',
													readOnly: true,
													anchor: '100%',
													trigger1Class: 'x-form-search-trigger',
													trigger2Class: 'x-form-clear-trigger',
													enableKeyEvents: true,
													listeners: {
														'keydown': function (inp, e) {
															if (e.F4 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey)) {
																if (e.F4 == e.getKey())
																	inp.onTrigger1Click();
																if (e.DELETE == e.getKey() && e.altKey)
																	inp.onTrigger2Click();
																if (e.browserEvent.stopPropagation)
																	e.browserEvent.stopPropagation();
																else
																	e.browserEvent.cancelBubble = true;
																if (e.browserEvent.preventDefault)
																	e.browserEvent.preventDefault();
																else
																	e.browserEvent.returnValue = false;
																e.browserEvent.returnValue = false;
																e.returnValue = false;
																if (Ext.isIE) {
																	e.browserEvent.keyCode = 0;
																	e.browserEvent.which = 0;
																}
																return false;
															}
														},
														'keyup': function (inp, e) {
															if (e.F4 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey)) {
																if (e.browserEvent.stopPropagation)
																	e.browserEvent.stopPropagation();
																else
																	e.browserEvent.cancelBubble = true;
																if (e.browserEvent.preventDefault)
																	e.browserEvent.preventDefault();
																else
																	e.browserEvent.returnValue = false;
																e.browserEvent.returnValue = false;
																e.returnValue = false;
																if (Ext.isIE) {
																	e.browserEvent.keyCode = 0;
																	e.browserEvent.which = 0;
																}
																return false;
															}
														}
													},
													onTrigger2Click: function () {
														if (this.disabled) return false;
														var b_f = Ext.getCmp('swDirectionOnMseEditForm').CommonForm.getForm();
														b_f.findField('EAddress_Zip').setValue('');
														b_f.findField('EKLCountry_id').setValue('');
														b_f.findField('EKLRGN_id').setValue('');
														b_f.findField('EKLSubRGN_id').setValue('');
														b_f.findField('EKLCity_id').setValue('');
														b_f.findField('EKLTown_id').setValue('');
														b_f.findField('EKLStreet_id').setValue('');
														b_f.findField('EAddress_House').setValue('');
														b_f.findField('EAddress_Corpus').setValue('');
														b_f.findField('EAddress_Flat').setValue('');
														b_f.findField('EAddress_Address').setValue('');
														b_f.findField('EAddress_AddressText').setValue('');
													},
													onTrigger1Click: function () {
														if (this.disabled) return false;
														var b_f = Ext.getCmp('swDirectionOnMseEditForm').CommonForm.getForm();
														getWnd('swAddressEditWindow').show({
															fields: {
																Address_ZipEdit: b_f.findField('EAddress_Zip').getValue(),
																KLCountry_idEdit: b_f.findField('EKLCountry_id').getValue(),
																KLRgn_idEdit: b_f.findField('EKLRGN_id').getValue(),
																KLSubRGN_idEdit: b_f.findField('EKLSubRGN_id').getValue(),
																KLCity_idEdit: b_f.findField('EKLCity_id').getValue(),
																KLTown_idEdit: b_f.findField('EKLTown_id').getValue(),
																KLStreet_idEdit: b_f.findField('EKLStreet_id').getValue(),
																Address_HouseEdit: b_f.findField('EAddress_House').getValue(),
																Address_CorpusEdit: b_f.findField('EAddress_Corpus').getValue(),
																Address_FlatEdit: b_f.findField('EAddress_Flat').getValue(),
																Address_AddressEdit: b_f.findField('EAddress_Address').getValue()
															},
															callback: function (values) {
																b_f.findField('EAddress_Zip').setValue(values.Address_ZipEdit);
																b_f.findField('EKLCountry_id').setValue(values.KLCountry_idEdit);
																b_f.findField('EKLRGN_id').setValue(values.KLRgn_idEdit);
																b_f.findField('EKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
																b_f.findField('EKLCity_id').setValue(values.KLCity_idEdit);
																b_f.findField('EKLTown_id').setValue(values.KLTown_idEdit);
																b_f.findField('EKLStreet_id').setValue(values.KLStreet_idEdit);
																b_f.findField('EAddress_House').setValue(values.Address_HouseEdit);
																b_f.findField('EAddress_Corpus').setValue(values.Address_CorpusEdit);
																b_f.findField('EAddress_Flat').setValue(values.Address_FlatEdit);
																b_f.findField('EAddress_Address').setValue(values.Address_AddressEdit);
																b_f.findField('EAddress_AddressText').setValue(values.Address_AddressEdit);
																b_f.findField('EAddress_AddressText').focus(true, 500);
															},
															onClose: function () {
																b_f.findField('EAddress_AddressText').focus(true, 500);
															}
														})
													},
													loadDataByOrgId: function(Org_id) {
														var b_f = Ext.getCmp('swDirectionOnMseEditForm').CommonForm.getForm();
														Ext.Ajax.request({
															params: {Org_id: Org_id},
															url: '/?c=Mse&m=getOrgAddress',
															success: function(response){
																var resp = Ext.util.JSON.decode(response.responseText);
																if (!resp || !resp[0]) return false;
																b_f.findField('EAddress_Zip').setValue(resp[0].Address_Zip);
																b_f.findField('EKLCountry_id').setValue(resp[0].KLCountry_id);
																b_f.findField('EKLRGN_id').setValue(resp[0].KLRGN_id);
																b_f.findField('EKLSubRGN_id').setValue(resp[0].KLSubRGN_id);
																b_f.findField('EKLCity_id').setValue(resp[0].KLCity_id);
																b_f.findField('EKLTown_id').setValue(resp[0].KLTown_id);
																b_f.findField('EKLStreet_id').setValue(resp[0].KLStreet_id);
																b_f.findField('EAddress_House').setValue(resp[0].Address_House);
																b_f.findField('EAddress_Corpus').setValue(resp[0].Address_Corpus);
																b_f.findField('EAddress_Flat').setValue(resp[0].Address_Flat);
																b_f.findField('EAddress_AddressText').setValue(resp[0].Address_AddressText);
																b_f.findField('EAddress_Address').setValue(resp[0].Address_Address);
															}
														});
													}
												}),
												{
													xtype: 'swlearngrouptypecombo',
													width: 300,
													anchor: '',
													hiddenName: 'LearnGroupType_id',
													editable: false,
													style: 'margin-left: 10px;',
													hideLabel: true
												}
											]
										}
									]
								}
							]
						}
					]
				},
				{
					layout: 'form',
					labelWidth: 181,
					border: false,
					labelAlign: 'right',
					items: [
						{
							isKz:getRegionNick() === 'kz',
							allowBlank:  this.isKz,
							xtype: 'swcommonsprcombo',
							comboSubject: 'MilitaryKind',
							hiddenName: 'MilitaryKind_id',
							fieldLabel: langs('Отношение к военной службе'),
							width: 219
						}
					]
				}
			]		});
	
		this.DiagHistoryForm = new sw.Promed.Panel({
			title: langs('Клинико-функциональные данные гражданина'),
			layout: 'column',
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			items: [
				{
					layout: 'column',
					border: false,
					defaults: {
						border: false
					},
					labelAlign: 'right',
					items: [
						{
							layout: 'form',
							labelWidth: 500,
							width: 600,
							items: [{
								xtype: 'combo',
								mode: 'local',
								hideEmptyRow: true,
								hiddenName: 'EvnPrescrMse_OrgMedDateYear',
								anchor: '100%',
								store: new Ext.data.SimpleStore(
									{
										key: 'dateValue',
										autoLoad: true,
										fields:
											[
												{name:'dateValue', type:'string'},
												{name:'dateText', type:'string'}
											],
										data: cur_win.directoryYears
									}),
								triggerAction: 'all',
								editable: false,
								allowBlank: true,
								displayField: 'dateText',
								valueField: 'dateValue',
								fieldLabel: langs('Наблюдается в организациях, оказывающих лечебно-профилактическую помощь с'),
								listeners: {
									'change': function(combo, newValue, oldValue){
										var b_f = cur_win.CommonForm.getForm();
										var evnPrescrMse_OrgMedDateMonth_combo = b_f.findField('EvnPrescrMse_OrgMedDateMonth');
										// evnPrescrMse_OrgMedDateMonth_combo.setAllowBlank(!newValue);
										if(newValue) {
											evnPrescrMse_OrgMedDateMonth_combo.enable();
										}else{
											evnPrescrMse_OrgMedDateMonth_combo.disable();
										}
									}
								}
							}]
						},
						{
							layout: 'form',
							width: 50,
							items: [{
								style: 'padding-left: 10px; font-size: 13px;',
								xtype: 'label',
								text: 'года'
							}]
						},
						{
							layout: 'form',
							width: 100,
							hidden: getRegionNick() != 'perm',
							items: [{
								xtype: 'combo',
								mode: 'local',
								hideEmptyRow: true,
								hiddenName: 'EvnPrescrMse_OrgMedDateMonth',
								anchor: '100%',
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'{dateText}&nbsp;',
									'</div></tpl>'
								),
								store: new Ext.data.SimpleStore({
									key: 'dateValue',
									autoLoad: true,
									fields:
										[
											{name:'dateValue', type:'string'},
											{name:'dateText', type:'string'}
										],
									data: [
										['', ''],
										['01', langs('Январь')],
										['02', langs('Февраль')],
										['03', langs('Март')],
										['04', langs('Апрель')],
										['05', langs('Май')],
										['06', langs('Июнь')],
										['07', langs('Июль')],
										['08', langs('Август')],
										['09', langs('Сентябрь')],
										['10', langs('Октябрь')],
										['11', langs('Ноябрь')],
										['12', langs('Декабрь')]
									]
								}),
								triggerAction: 'all',
								editable: false,
								allowBlank: true,
								displayField: 'dateText',
								valueField: 'dateValue',
								hideLabel: true,
								fieldLabel: ''
							}]
						},
						{
							layout: 'form',
							width: 70,
							hidden: getRegionNick() != 'perm',
							items: [{
								style: 'padding-left: 10px; font-size: 13px;',
								xtype: 'label',
								text: 'месяца'
							}]
						}
					]
				},
				{
					layout: 'form',
					width: 850,
					border: false,
					defaults: {
						border: false
					},
					labelAlign: 'right',
					labelWidth: 340,
					items: [
						{
							xtype: 'swhtmleditor',
							getPersonId: function() {
								return cur_win.getPersonId();
							},
							enableSourceEdit: false,
							enableFont: false,
							listeners: {
								initialize: function(f) {
									var tb = f.tb;
									tb.hide();
									var el = f.iframe.contentWindow;
									this.addEvt(el, 'focus', tb.show.createDelegate(tb) );
									this.addEvt(el, 'blur', tb.hide.createDelegate(tb) );
									f.syncSize();
								}.createDelegate(this)
							},
							height: 100,
							name: 'EvnPrescrMse_DiseaseHist',
							fieldLabel: 'Анамнез заболевания <a title="Читать примечание" href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).stripNotePanel(&quot;note1&quot;);">?</a>'
						},
						{
							name: 'note1',
							hidden: true,
							xtype: 'panel',
							bodyStyle: 'padding: 3px; background-color: #eee; border: 1px solid #000;',
							html: langs('<b>Примечание:</b> подробно описывается при первичном направлении, при повторном направлении')+
								langs(' отражается динамика за период между освидетельствованиями, детально описываются')+
								langs(' выявленные в этот период новые случаи заболеваний, приведшие к стойким нарушениям функций организма')
						},
						{
							xtype: 'swhtmleditor',
							getPersonId: function() {
								return cur_win.getPersonId();
							},
							name: 'EvnPrescrMse_LifeHist',
							enableFont: false,
							enableSourceEdit: false,
							listeners: {
								initialize: function(f) {
									var tb = f.tb;
									tb.hide();
									var el = f.iframe.contentWindow;
									this.addEvt(el, 'focus', tb.show.createDelegate(tb) );
									this.addEvt(el, 'blur', tb.hide.createDelegate(tb) );
									f.syncSize();
								}.createDelegate(this),
								push: function() {
									this.onFirstFocus();
								}
							},
							height: 100,
							fieldLabel: 'Анамнез жизни <a title="Читать примечание" href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).stripNotePanel(&quot;note2&quot;);">?</a>'
						},
						{
							name: 'note2',
							hidden: true,
							xtype: 'panel',
							bodyStyle: 'padding: 3px; background-color: #eee; border: 1px solid #000;',
							html: langs('<b>Примечание:</b> заполняется при первичном направлении')+
								langs('(перечис-ся перенесенные в прошлом забол-я,')+
								langs(' травмы, отрав-я, операции, забол-я, по которым отягощена наслед-ть,')+
								langs(' доп. в отн. ребенка указывается, как протекали берем-ть и роды у матери,')+
								langs(' сроки формир-я психомоторных навыков, самообсл-я, познавательно-игровой деят-ти,')+
								langs(' навыков опрят-ти и ухода за собой, как протекало раннее развитие (по возрасту, с отставанием, с опережением)')
						},
						{
							xtype: 'swhtmleditor',
							getPersonId: function() {
								return cur_win.getPersonId();
							},
							enableSourceEdit: false,
							enableFont: false,
							listeners: {
								initialize: function(f) {
									var tb = f.tb;
									tb.hide();
									var el = f.iframe.contentWindow;
									this.addEvt(el, 'focus', tb.show.createDelegate(tb) );
									this.addEvt(el, 'blur', tb.hide.createDelegate(tb) );
									f.syncSize();
								}.createDelegate(this)
							},
							height: 100,
							name: 'EvnPrescrMse_MedRes',
							hidden: getRegionNick() != 'ufa',
							hideLabel: getRegionNick() != 'ufa',
							fieldLabel: langs('Рез-ты проведенных меропр. по мед. реабилитации в соотв-ии с индив. программой реабилитации инвалида ')+
								'<a title="Читать примечание" href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).stripNotePanel(&quot;note3&quot;);">?</a>'
						},
						{
							name: 'note3',
							hidden: true,
							xtype: 'panel',
							bodyStyle: 'padding: 3px; background-color: #eee; border: 1px solid #000;',
							html: langs('<b>Примечание:</b> заполняется при повторном направлении, указываются конкретные виды восстановительной терапии,')+
								langs(' реконструктивной хирургии, санаторно-курортного лечения, технических средств медицинской реабилитации,')+
								langs(' в том числе протезирования и ортезирования, а также сроки, в которые они были предоставлены; перечисляются функции организма,')+
								langs(' которые удалось компенсировать или восстановить полностью или частично, либо делается отметка, что положительные результаты отсутствуют')
						},
						{
							xtype: 'swhtmleditor',
							getPersonId: function() {
								return cur_win.getPersonId();
							},
							enableSourceEdit: false,
							enableFont: false,
							listeners: {
								initialize: function(f) {
									var tb = f.tb;
									tb.hide();
									var el = f.iframe.contentWindow;
									this.addEvt(el, 'focus', tb.show.createDelegate(tb) );
									this.addEvt(el, 'blur', tb.hide.createDelegate(tb) );
									f.syncSize();
								}.createDelegate(this)
							},
							height: 100,
							name: 'EvnPrescrMse_State',
							fieldLabel: langs('Состояние гражданина при направлении на медико-социальную экспертизу ')+
								'<a title="Читать примечание" href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).stripNotePanel(&quot;note4&quot;);">?</a>'
						},
						{
							name: 'note4',
							hidden: true,
							xtype: 'panel',
							bodyStyle: 'padding: 3px; background-color: #eee; border: 1px solid #000;',
							html: langs('<b>Примечание:</b> указываются жалобы, данные осмотра лечащим врачом и врачами других специальностей')
						},
						{
							xtype: 'swhtmleditor',
							hidden: getRegionNick().inlist(['penza', 'kz']),
							hideLabel: getRegionNick().inlist(['penza', 'kz']),
							getPersonId: function() {
								return cur_win.getPersonId();
							},
							enableSourceEdit: false,
							enableFont: false,
							listeners: {
								initialize: function(f) {
									var tb = f.tb;
									tb.hide();
									var el = f.iframe.contentWindow;
									this.addEvt(el, 'focus', tb.show.createDelegate(tb) );
									this.addEvt(el, 'blur', tb.hide.createDelegate(tb) );
									f.syncSize();
								}.createDelegate(this)
							},
							height: 100,
							name: 'EvnPrescrMse_DopRes',
							fieldLabel: langs('Результаты дополнительных методов исследования ')+
								'<a title="Читать примечание" href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).stripNotePanel(&quot;note5&quot;);">?</a>'
						},
						{
							name: 'note5',
							hidden: true,
							xtype: 'panel',
							bodyStyle: 'padding: 3px; background-color: #eee; border: 1px solid #000;',
							html: getRegionNick().inlist(['ufa', 'perm', 'pskov']) 
								? langs('<b>Примечание:</b> указываются результаты проведенных лабораторных, рентгенологических,')+
								langs(' эндоскопических, ультразвуковых, психологических, функциональных и других видов исследований')
								: langs('<b>Примечание:</b> указываются результаты проведенных лабораторных, рентгенологических, эндоскопических,')+
								langs('  ультразвуковых, психологических, функциональных и других видов исследований (вспомогательное поле, не выводится на печать)')
						}
					]
				}, {
					width: 100,
					border: false,
					id: 'domef_expandButtonsPanel',
					defaults: {
						border: false,
						height: 70,
						style: 'margin: 0 0 8px 3px'
					},
					items: [{
						html: '<a href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).expandField(&quot;EvnPrescrMse_DiseaseHist&quot;);">Раскрыть</a>'
					}, {
						html: '<a href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).expandField(&quot;EvnPrescrMse_LifeHist&quot;);">Раскрыть</a>'
					}, {
						html: '<a href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).expandField(&quot;EvnPrescrMse_MedRes&quot;);">Раскрыть</a>'
					}, {
						html: '<a href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).expandField(&quot;EvnPrescrMse_DopRes&quot;);">Раскрыть</a>'
					}]
				}
			]
		});

		this.MedicalRehabilitationGrid = new sw.Promed.Panel({
			// Results of the conducted activities. on medical rehabilitation
			hidden: (getRegionNick()=='ufa'),
			tbar: [
				{
					handler:function () {
						this.actionMeasuresFMR('download');
					}.createDelegate(this),
					iconCls: 'downdownarrow',
					id: 'downloadMR',
					text:'Загрузить'
				},
				{
					handler:function () {
						this.actionMeasuresFMR('add');
					}.createDelegate(this),
					iconCls:'add16',
					id: 'addMR',
					text:'Добавить'
				},
				{
					handler:function () {
						this.actionMeasuresFMR('edit');
					}.createDelegate(this),
					iconCls:'edit16',
					id: 'editMR',
					text:'Изменить'
				},
				{
					handler:function () {
						this.actionMeasuresFMR('view');
					}.createDelegate(this),
					iconCls:'view16',
					id: 'viewMR',
					text:'Просмотреть'
				},
				{
					handler:function () {
						this.actionMeasuresFMR('del');
					}.createDelegate(this),
					iconCls:'delete16',
					id: 'deleteMR',
					text:'Удалить'
				},
				{
					handler:function () {
						this.actionMeasuresFMR('clear');
					}.createDelegate(this),
					iconCls:'clear16',
					id: 'clearMR',
					text:'Очистить'
				},
				{
					handler:function () {
						var msg = 'Заполняется при повторном направлении, указываются конкретные виды восстановительной терапии, реконструктивной хирургии, санаторно-курортного лечения, технических средств медицинской реабилитации, в том числе протезирования и ортезирования, а также сроки, в которые они были предоставлены; перечисляются функции организма, которые удалось компенсировать или восстановить полностью или частично, либо делается отметка, что положительные результаты отсутствуют';
						Ext.Msg.alert('Спарвка', msg);
					}.createDelegate(this),
					iconCls:'help16',
					id: 'helpMR',
					text:'Помощь'
				}
			],
			listeners: {
				expand: function(p){
					var grid = p.find('autoExpandColumn', 'autoexpand')[0].ViewGridPanel;
					var EvnPrescrMse = this.CommonForm.getForm().findField('EvnPrescrMse_id');
					var EvnPrescrMse_id = EvnPrescrMse.getValue();
					if( !EvnPrescrMse_id ) {
						grid.fireEvent('rowclick', grid);
						return false;
					}
					grid.getStore().baseParams.EvnPrescrMse_id = EvnPrescrMse_id;
					grid.getStore().load({
						callback: function(){
							if(grid.getStore().getCount() == 1){
								// может быть пустая запись
								var MSE_id = grid.getStore().getAt(0).get('MeasuresRehabMSE_id');
								if( !MSE_id ) grid.getStore().removeAt(0);
							}
							if(grid.getStore().getCount() > 0){
								grid.getView().focusRow(0);
								grid.fireEvent('rowclick', grid);
							}
						}
					});
					
				}.createDelegate(this)
			},
			items: [
				new sw.Promed.ViewFrame({					
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 100,
					pageSize: 20,
					object: '',
					obj_isEvn: true,
					mainType: 'grid',
					editformclassname: 'swMeasuresForMedicalRehabilitation',
					border: false,
					toolbar: false,
					useEmptyRecord: false,
					actions: [
						{ name: 'action_add', hidden: true},
						{ name: 'action_edit', hidden: true},
						{ name: 'action_view', hidden: true},
						{ name: 'action_delete', hidden: true},
						{ name: 'action_refresh', hidden: true},
						{ name: 'action_print', hidden: true},
					],
					autoLoadData: false,
					stripeRows: true,
					root: 'data',
					stringfields: [
						{name: 'MeasuresRehabMSE_id', hidden: true, type: 'int'},
						{name: 'EvnPrescrMse_id', hidden: true, type: 'int'},
						{name: 'MeasuresRehabMSE_IsExport', hidden: true, type: 'int'},
						{name: 'MeasuresRehabMSE_BegDate', header: 'Дата начала', type: 'date', width: 100},
						{name: 'MeasuresRehabMSE_EndDate', header: 'Дата окончания', type: 'date', width: 100},

						{name: 'MeasuresRehabMSE_Type', header: 'Тип мероприятия', type: 'string',  width: 250},
						{name: 'MeasuresRehabMSE_SubType', header: 'Подтип мероприятия', type: 'string', width: 250},
						{name: 'MeasuresRehabMSE_Name', header: 'Наименование', type: 'string', id: 'autoexpand'},
						{name: 'MeasuresRehabMSE_Result', header: 'Результат', type: 'string',  width: 250}
					],
					// paging: true,
					dataUrl: '/?c=MeasuresRehab&m=loadMeasuresRehabGridPerson',
					totalProperty: 'totalCount',
					onRowSelect: function(sm,index,record){
						var isExport = record.get('MeasuresRehabMSE_IsExport');
						var editBtn = Ext.getCmp('editMR');
						if( (isExport  && isExport == 2) || cur_win.action == 'view') {
							editBtn.disable();
						} else {
							editBtn.enable();
						}
					}
				})
			]
		});

		this.MedicalRehabilitationForm = new sw.Promed.Panel({
			title: 'Результаты эффективности проведенных мероприятий медицинской реабилитации в соответствии с индивидуальной программой реабилитации инвалида',
			layout: 'fit',
			collapsible: true,
			collapsed: false,
			items: [{
				layout: 'form',
				border: false,
				bodyStyle: 'padding: 10px;',
				hidden: getRegionNick() == 'kz',
				labelWidth: 200,
				labelAlign: 'right',
				autoHeight: true,
				items: [{
					xtype: 'hidden',
					name: 'MeasuresRehabEffect_id',
				},
				{
					xtype: 'hidden',
					name: 'IPRARegistry_id',
				},
				{
					xtype: 'textfield',
					disabled: true,
					width: 100,
					name: 'IPRARegistry_Number',
					fieldLabel: langs('№ ИПРА')
				},
				{
					xtype: 'textfield',
					disabled: true,
					width: 100,
					name: 'IPRARegistry_Protocol',
					fieldLabel: langs('№ протокола проведения МСЭ')
				},
				{
					xtype: 'textfield',
					disabled: true,
					width: 100,
					name: 'IPRARegistry_ProtocolDate',
					fieldLabel: langs('Дата протокола проведения МСЭ')
				},
				{
					layout: 'form',
					xtype: 'fieldset',
					labelWidth: 200,
					width: 900,
					collapsible: false,
					labelAlign: 'right',
					title: langs('Результаты'),
					autoHeight: true,
					items: [{
						layout: 'column',
						border: false,
						defaults: {border: false},
						labelAlign: 'right',
						items: [{
							layout: 'form',
							labelWidth: 190,
							width: 620,
							items: [{
								labelSeparator: '',
								xtype: 'checkbox',
								name: 'MeasuresRehabEffect_IsRecovery',
								listeners: {
									change: function(checkbox, c){
										var b_f = this.CommonForm.getForm();
										b_f.findField('IPRAResult_rid').setDisabled(!c);
										if (!c) {
											b_f.findField('IPRAResult_rid').clearValue();
										}
									}.createDelegate(this)
								},
								boxLabel: 'Восстановление нарушенных функций'
							}]
						},
						{
							layout: 'form',
							items: [{
								hideLabel: true,
								width: 250,
								comboSubject: 'IPRAResult',
								xtype: 'swcommonsprcombo',
								showCodefield: false,
								hiddenName: 'IPRAResult_rid'
							}]
						}]
					}, {
						layout: 'column',
						border: false,
						defaults: {border: false},
						labelAlign: 'right',
						items: [{
							layout: 'form',
							labelWidth: 190,
							width: 620,
							items: [{
								labelSeparator: '',
								xtype: 'checkbox',
								name: 'MeasuresRehabEffect_IsCompensation',
								listeners: {
									change: function(checkbox, c){
										var b_f = this.CommonForm.getForm();
										b_f.findField('IPRAResult_cid').setDisabled(!c);
										if (!c) {
											b_f.findField('IPRAResult_cid').clearValue();
										}
									}.createDelegate(this)
								},
								boxLabel: 'Достижение компенсации утраченных либо отсутствующих функций'
							}]
						},
						{
							layout: 'form',
							items: [{
								hideLabel: true,
								width: 250,
								comboSubject: 'IPRAResult',
								xtype: 'swcommonsprcombo',
								showCodefield: false,
								hiddenName: 'IPRAResult_cid'
							}]
						}]
					}]
				},
				{
					xtype: 'textfield',
					width: 676,
					name: 'MeasuresRehabEffect_Comment',
					fieldLabel: langs('Комментарий')
				}]
			},
			this.MedicalRehabilitationGrid
			]
		});
	
		this.EvnStickForm = new sw.Promed.Panel({
			title: langs('Временная нетрудоспособность (сведения за последние 12 месяцев)'),
			layout: 'fit',
			collapsible: true,
			collapsed: true,
			listeners: {
				expand: function(p){
					var grid = p.find('autoExpandColumn', 'autoexpand')[0].ViewGridPanel;
					grid.getStore().baseParams.Person_id = this.PersonFrame.personId;
					grid.getStore().load({
						callback: function(){
							if(grid.getStore().getCount() > 0){
								grid.getView().focusRow(0);
								grid.fireEvent('rowclick', grid);
							}
						}
					});
				}.createDelegate(this)
			},
			items: [
				new sw.Promed.ViewFrame({
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 100,
					pageSize: 20,
					object: '',
					obj_isEvn: true,
					mainType: 'grid',
					editformclassname: 'swEvnMseStickEditWindow',
					border: false,
					actions: [
						{ name: 'action_add', tooltip: '', handler: function(){this.addStick();}.createDelegate(this) },
						{ name: 'action_edit', tooltip: '', disabled: true },
						{ name: 'action_view', tooltip: '' },
						{ name: 'action_delete', tooltip: '', disabled: true },
						{ name: 'action_refresh' },
						{ name: 'action_print', hidden: true }
					],
					autoLoadData: false,
					stripeRows: true,
					root: '',
					stringfields: [
						{ name: 'EvnStick_id', type: 'int', hidden: true, key: true },
						{ name: 'Person_id', hidden: true, type: 'int'},
						{ name: 'num', type: 'string', header: langs('№'), width: 50 },
						{ name: 'EvnStick_setDate', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y'), header: langs('Дата начала'), width: 150 },
						{ name: 'EvnStick_disDate', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y'), header: langs('Дата окончания'), width: 150 },
						{ name: 'DayCount', type: 'string', header: langs('Число дней'), width: 100 },
						{ name: 'Diag_id', hidden: true, type: 'int' },
						{ name: 'Diag_Name', id: 'autoexpand', type: 'string', header: langs('Диагноз') },
						{ name: 'EvnMseStick_IsStick', hidden: true, type: 'int' },
						{ name: 'EvnMseStick_IsStickName', type: 'string', header: langs('ЭЛН') },
						{ name: 'EvnMseStick_StickNum', type: 'string', header: langs('Номер ЛВН') },
						{ name: 'EvnStickClass', type: 'string', hidden: true }
					],
					paging: true,
					dataUrl: '/?c=Mse&m=getEvnStickOfYear',
					totalProperty: 'totalCount'
				})
			]
		});
		
		var EvnStickGrid = this.EvnStickForm.find('editformclassname', 'swEvnMseStickEditWindow')[0];
		EvnStickGrid.ViewGridPanel.on('rowclick', function(grid){
			var rec = grid.getSelectionModel().getSelected();
			EvnStickGrid.object = rec.get('EvnStickClass');
			if(EvnStickGrid.object == 'EvnStick' || EvnStickGrid.object == null){
				EvnStickGrid.obj_isEvn = true;
				EvnStickGrid.ViewActions.action_edit.disable();
				EvnStickGrid.ViewActions.action_delete.disable();
			} else {
				EvnStickGrid.obj_isEvn = false;
				EvnStickGrid.ViewActions.action_edit.enable();
				EvnStickGrid.ViewActions.action_delete.enable();
			}
		});
	
		this.EvaluationStateForm = new sw.Promed.Panel({
			title: langs('Антропометрические данные и физиологические параметры'),
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			items: [
				{
					layout: 'column',
					border: false,
					defaults: {
						border: false
					},
					labelAlign: 'right',
					items: [
						{
							layout: 'form',
							labelWidth: 80,
							width: 140,
							items: [
								{
									xtype: 'hidden',
									name: 'PersonWeight_id'
								},
								{
									xtype: 'numberfield',
									anchor: '100%',
									minValue: 1,
									maxValue: 250,
									isKz:getRegionNick() === 'kz',
									allowBlank:  this.isKz,
									listeners: {
										change: function()
										{
											this.indexCalculation();
										}.createDelegate(this)
									},
									name: 'PersonWeight_Weight',
									fieldLabel: langs('Масса (кг)')
								}
							]
						},
						{
							layout: 'form',
							width: 220,
							labelWidth: 140,
							items: [
								{
									xtype: 'swyesnocombo',
									anchor: '100%',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var index = combo.getStore().findBy(function(rec) {
												return (rec.get('YesNo_id') == newValue);
											});
											combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
										},
										'select': function(c, r, idx) {
											var field = this.CommonForm.getForm().findField('WeightAbnormType_id');

											if ( typeof r == 'object' && r.get('YesNo_id') == 2 ) {
												field.enable();
												field.allowBlank = false;
											}
											else {
												field.allowBlank = true;
												field.clearValue();
												field.disable();
											}

											field.validate();
										}.createDelegate(this)
									},
									hiddenName: 'PersonWeight_IsAbnorm',
									fieldLabel: langs('Отклонение (масса)')
								}
							]
						},
						{
							layout: 'form',
							width: 290,
							labelWidth: 120,
							items: [
								{
									xtype: 'swweightabnormtypecombo',
									anchor: '100%',
									disabled: true,
									fieldLabel: langs('Тип отклонения')
								}
							]
						},
						{
							layout: 'form',
							width: 200,
							labelWidth: 140,
							items: [
								{
									xtype: 'numberfield',
									anchor: '100%',
									name: 'indexWeight',
									disabled: true,
									fieldLabel: langs('Индекс массы тела')
								}
							]
						}
					]
				},
				{
					layout: 'column',
					border: false,
					defaults: {
						border: false
					},
					labelAlign: 'right',
					items: [
						{
							layout: 'form',
							labelWidth: 80,
							width: 140,
							items: [
								{
									xtype: 'hidden',
									name: 'PersonHeight_id'
								},
								{
									xtype: 'numberfield',
									anchor: '100%',
									minValue: 20,
									maxValue: 240,
									isKz:getRegionNick() === 'kz',
									allowBlank:  this.isKz,
									listeners: {
										change: function()
										{
											this.indexCalculation();
										}.createDelegate(this)
									},
									name: 'PersonHeight_Height',
									fieldLabel: langs('Рост (см)')
								}
							]
						},
						{
							layout: 'form',
							width: 220,
							labelWidth: 140,
							items: [
								{
									xtype: 'swyesnocombo',
									anchor: '100%',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var index = combo.getStore().findBy(function(rec) {
												return (rec.get('YesNo_id') == newValue);
											});
											combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
										},
										'select': function(c, r, idx) {
											var field = this.CommonForm.getForm().findField('HeightAbnormType_id');

											if ( typeof r == 'object' && r.get('YesNo_id') == 2 ) {
												field.enable();
												field.allowBlank = false;
											}
											else {
												field.allowBlank = true;
												field.clearValue();
												field.disable();
											}

											field.validate();
										}.createDelegate(this)
									},
									hiddenName: 'PersonHeight_IsAbnorm',
									fieldLabel: langs('Отклонение (рост)')
								}
							]
						},
						{
							layout: 'form',
							width: 290,
							labelWidth: 120,
							items: [
								{
									xtype: 'swheightabnormtypecombo',
									anchor: '100%',
									disabled: true,
									fieldLabel: langs('Тип отклонения')
								}
							]
						}
					]
				},
				{
					layout: 'form',
					labelWidth: 200,
					labelAlign: 'right',
					border: false,
					// Индикатор казахстана
					isKz:getRegionNick() === 'kz',
					hidden: this.isKz,
					items: [
						{
							comboSubject: 'PhysiqueType',
							xtype: 'swcommonsprcombo',
							width: 200,
							hiddenName: 'PhysiqueType_id',
							// Индикатор казахстана
							// Разрешаем не заполнять только для Казахстана
							allowBlank:  this.isKz,
							fieldLabel: langs('Телосложение')
						},
						{
							layout: 'column',
							labelAlign: 'right',
							border: false,
							defaults: {
								border: false
							},
							items: [{
								layout: 'form',
								width: 300,
								labelWidth: 200,
								items: [{
									xtype: 'numberfield',
									width: 70,
									name: 'EvnPrescrMse_DailyPhysicDepartures',
									allowDecimals: false,
									allowNegative: false,
									allowBlank: true,
									fieldLabel: langs('Суточный объем физиологических отправлений (мл).')
								}]
							}, {
								layout: 'form',
								width: 180,
								labelWidth: 90,
								items: [{
									xtype: 'numberfield',
									width: 70,
									name: 'EvnPrescrMse_Waist',
									allowDecimals: false,
									allowNegative: false,
									allowBlank: true,
									fieldLabel: langs('Объем талии')
								}]
							}, {
								layout: 'form',
								width: 180,
								labelWidth: 90,
								items: [{
									xtype: 'numberfield',
									width: 70,
									name: 'EvnPrescrMse_Hips',
									allowDecimals: false,
									allowNegative: false,
									allowBlank: true,
									fieldLabel: langs('Объем бедер')
								}]
							}, {
								layout: 'form',
								width: 200,
								labelWidth: 120,
								items: [{
									xtype: 'numberfield',
									width: 65,
									name: 'EvnPrescrMse_WeightBirth',
									fieldLabel: langs('Масса тела при рождении')
								}]
							}]
						},
						{
							xtype: 'textfield',
							width: 645,
							name: 'EvnPrescrMse_PhysicalDevelopment',
							allowBlank: getRegionNick() == 'kz',
							fieldLabel: langs('Физическое развитие')
						}
					]
				},
				{
					layout: 'column',
					labelAlign: 'right',
					border: false,
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							width: 450,
							labelWidth: 300,
							items: [
								{
									xtype: 'swstatenormtypecombo',
									anchor: '100%',
									allowBlank: (getRegionNick() == 'perm'),//false,
									hiddenName: 'StateNormType_id',
									editable: false,
									fieldLabel: langs('Оценка психофизиологической выносливости')
								}
							]
						},
						{
							layout: 'form',
							width: 400,
							labelWidth: 250,
							items: [
								{
									xtype: 'swstatenormtypecombo',
									anchor: '100%',
									allowBlank: (getRegionNick() == 'perm'),//false,
									hiddenName: 'StateNormType_did',
									editable: false,
									fieldLabel: langs('Оценка эмоциональной устойчивости')
								}
							]
						}
					]
				}
			]
		});

		this.SopDiagListPanel = new sw.Promed.DiagListPanelWithDescr({
			win: this,
			width: 1200,
			buttonAlign: 'left',
			labelAlign: 'top',
			buttonLeftMargin: 0,
			labelWidth: 140,
			fieldWidth: 270,
			showOsl: getRegionNick() != 'kz',
			showDescr: getRegionNick() != 'kz',
			style: 'background: transparent; margin: 0; padding: 0;',
			fieldLabel: 'Сопутствующие заболевания по МКБ',
			fieldDescLabel: 'Сопутствующие заболевания',
			onChange: function() {
				
			}
		});
		
		this.OslDiagListPanel = new sw.Promed.DiagListPanelWithDescr({
			win: this,
			width: 1200,
			buttonAlign: 'left',
			labelAlign: 'top',
			buttonLeftMargin: 0,
			labelWidth: 140,
			fieldWidth: 270,
			showDescr: true,
			style: 'background: transparent; margin: 0; padding: 0;',
			fieldLabel: 'Осложнения основного заболевания по МКБ',
			fieldDescLabel: 'Осложнения основного заболевания',
			onChange: function() {
				
			}
		});
	
		this.ReasonAndDiagnosis = new sw.Promed.Panel({
			title: langs('Причины направления и диагнозы'),
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			defaults: {
				border: false
			},
			items: [
				{
					layout: 'form',
					xtype: 'fieldset',
					labelWidth: 260,
					width: 1600,
					collapsible: true,
					labelAlign: 'right',
					title: langs('Диагноз при направлении на медико-социальную экспертизу'),
					autoHeight: true,
					items: [
						{
							xtype: 'swdiagcombo',
							hiddenName: 'Diag_id',
							width: 350,
							allowBlank: false,
							fieldLabel: langs('Код основного заболевания по МКБ'),
							onChange: function(combo, newValue, oldValue) {
								var form = cur_win.CommonForm.getForm(),
									targetField = form.findField('EvnPrescrMse_MainDisease');
								if (cur_win.action != 'view') {
									targetField.setDisabled(!newValue);
								}
							}
						},
						{
							xtype: 'textarea',
							name: 'EvnPrescrMse_MainDisease',
							width: 650,
							allowBlank: true,
							maxLength: 1000,
							maxLengthText: 'Значение поля не должно превышать 1000 символов',
							grow: true,
							disabled: true,
							fieldLabel: 'Основное заболевание',
							listeners: {
								change: function(c,v) {
									if (v && v.length <= 1000) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: v,
											enabled: true,
											showDelay: 5,
											trackMouse: true,
											autoShow: true
										});
									} else {
										Ext.QuickTips.unregister(c.getEl());
									}
								}
							}
						},
						{
							xtype: 'swdiagcombo',
							hiddenName: 'Diag_sid',
							hidden: true,
							hideLabel: true,
							width: 350,
							fieldLabel: langs('Сопутствующее заболевание')
						},
						{
							xtype: 'swdiagcombo',
							hiddenName: 'Diag_aid',
							hidden: true,
							hideLabel: true,
							width: 350,
							fieldLabel: langs('Осложнение основного заболевания')
						},
						this.OslDiagListPanel,
						this.SopDiagListPanel
					]
				},
				{
					layout: 'column',
					id: 'AimColomn_id',
					border: false,
					defaults: {
						border: false
					},
					labelAlign: 'right',
					items: [
						{
							layout: 'form',
							width: 600,
							labelWidth: 320,
							items: [
								{
									xtype: 'swmsedirectionaimtypecombo',
									anchor: '100%',
									listWidth: 750,
									hiddenName: 'MseDirectionAimType_id',
									name: 'MseDirectionAimType_id',
									editable: false,
									allowBlank: false,
									fieldLabel: langs('Цель направления на медико-социальную экспертизу'),
									listeners: {
										select: function (combo, rec) {
											var form = cur_win.CommonForm.getForm(),
												targetField = form.findField('EvnPrescrMse_AimMseOver'),
												par = combo.ownerCt.ownerCt.ownerCt,
												it = par.findById('AllAims_id');
											if (!Ext.isEmpty(cur_win.EvnPrescrMse_id) && combo.firstLoad) {
												Ext.Ajax.request({
													params: {
														'EvnPrescrMse_id': cur_win.EvnPrescrMse_id
													},
													url: '/?c=Mse&m=loadMultiplePrescrAims',
													success: function (response) {
														combo.firstLoad = false;
														resp = Ext.util.JSON.decode(response.responseText);
														if (!Ext.isEmpty(resp))
															resp.forEach(function (item) {
																it.add(
																	new Ext.form.TextField({
																		id: 'Aim' + item.Code,
																		name: 'Aim' + item.Code,
																		width: 600,
																		style: 'margin-top: 3px; margin-left: 6px;',
																		value: item.Code + '. '
																			+ item.Name,
																		code: item.Code,
																		fieldLabel: 'Цель ',
																		readOnly: true
																	}),
																	new Ext.Button({
																		iconCls: 'delete16',
																		id: 'AimDelBtn' + item.Code,
																		style: 'margin-top: 3px; margin-left: 3px;',
																		text: langs('Удалить'),
																		handler: function (btn) {
																			var id = 'Aim' + item.Code,
																				aim = it.items.get(id);
																			if (btn.id === 'AimDelBtn5') {
																				targetField.reset();
																				targetField.ownerCt.setDisabled(true);
																				targetField.disable();
																			}
																			aim.hideContainer();
																			it.items.remove(aim);
																			aim.destroy();
																			it.items.remove(btn);
																			btn.destroy();
																			par.doLayout();

																		}
																	})
																);
																if (item.Code == 14) {
																	targetField.ownerCt.setDisabled(false);
																	targetField.enable();
																	targetField.setValue(item.AimText);
																}
															});
														par.doLayout();
													}
												});
											}

											if(!Ext.isEmpty(rec)) {
												var id = 'Aim' + rec.data.MseDirectionAimType_Code,
												aim = it.items.get(id);

												if (Ext.isEmpty(aim)) {
													it.add(
														new Ext.form.TextField({
															id: 'Aim' + rec.data.MseDirectionAimType_Code,
															name: 'Aim' + rec.data.MseDirectionAimType_Code,
															width: 600,
															style: 'margin-top: 3px; margin-left: 6px;',
															value: rec.data.MseDirectionAimType_Code + '. '
															+ rec.data.MseDirectionAimType_Name,
															code: rec.data.MseDirectionAimType_Code,
															fieldLabel: 'Цель ',
															readOnly: true
														}),
														new Ext.Button({
															iconCls: 'delete16',
															id: 'AimDelBtn' + rec.data.MseDirectionAimType_Code,
															style: 'margin-top: 3px; margin-left: 3px;',
															text: langs('Удалить'),
															handler: function (btn) {
																var id = 'Aim' + rec.data.MseDirectionAimType_Code,
																	aim = it.items.get(id);
																if (btn.id === 'AimDelBtn5') {
																	targetField.reset();
																	targetField.ownerCt.setDisabled(true);
																	targetField.disable();
																}
																aim.hideContainer();
																it.items.remove(aim);
																aim.destroy();
																it.items.remove(btn);
																btn.destroy();

																par.doLayout();

															}
														})
													);
													par.doLayout();
												}
											}

											//проверка на наличие цели "для другого"
											var	aim14 = it.items.get('Aim14');
											if (!Ext.isEmpty(aim14)) {
												targetField.ownerCt.setDisabled(false);
												targetField.enable();
											}
										}
									}
								}
							]
						},
						new Ext.Container({
							autoEl: {},
							items: [{
								width: 300,
								xtype: 'container',
								layout: 'form',
								id: 'EvnPrescrMse_AimMseOver',
								autoEl: {},
								items: {
									xtype: 'textfield',
									anchor: '100%',
									name: 'EvnPrescrMse_AimMseOver',
									fieldLabel: langs('Другая цель'),
								}
							}]
						})
					]
				},	{
					layout: 'column',
					id: 'AllAims_id',
					border: false,
					vertical: true,
					width: 800,
					defaults: {
						border: false,
						anchor: '100%'
					},
					getValues: function() {
						var codes = [];
						this.findBy(function(el) { 
							if (el.code){
								codes.push(el.code);
							}
						});
						return codes;
					},
					labelAlign: 'right'
				}
			]
		});
		
		
		this.ForecastsAndRecommendationsForm = new sw.Promed.Panel({
			title: langs('Прогнозы и рекомендации'),
			collapsible: true,
			bodyStyle: 'padding: 5px;',
			items: [
				{
					layout: 'form',
					width: 960,
					border: false,
					defaults: {
						border: false
					},
					labelAlign: 'right',
					labelWidth: 350,
					items: [
						{
							xtype: 'swclinicalforecasttypecombo',
							editable: false,
							allowBlank: false,
							hiddenName: 'ClinicalForecastType_id',
							fieldLabel: langs('Клинический прогноз')
						},
						{
							xtype: 'swclinicalpotentialtypecombo',
							editable: false,
							allowBlank: false,
							hiddenName: 'ClinicalPotentialType_id',
							fieldLabel: langs('Реабилитационный потенциал')
						},
						{
							xtype: 'swclinicalforecasttypecombo',
							editable: false,
							allowBlank: false,
							hiddenName: 'ClinicalForecastType_did',
							fieldLabel: langs('Реабилитационный прогноз')
						},
						{
							xtype: 'textarea',
							anchor: '100%',
							name: 'EvnPrescrMse_Recomm',
							fieldLabel: langs('Рекомендуемые мероприятия по мед. реабилитации или абилитации ')+
								(getRegionNick() != 'kz' ? '<a title="Читать примечание" href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).stripNotePanel(&quot;note6&quot;);">?</a>' : '')
						},
						{
							name: 'note6',
							hidden: true,
							xtype: 'panel',
							bodyStyle: 'padding: 3px; background-color: #eee; border: 1px solid #000;',
							html: langs('<b>Примечание:</b> Указываются конкретные рекомендуемые мероприятия по медицинской реабилитации ')+
								langs(' или абилитации, включая обеспечение лекарственными препаратами для лечения заболевания, ставшего ')+
								langs(' причиной инвалидности, согласно Программе государственных гарантий бесплатного оказания гражданам ')+
								langs(' медицинской помощи на 2018 год и на плановый период 2019 и 2020 годов, утвержденной постановлением ')+
								langs(' Правительства Российской Федерации от 8 декабря 2017 г. № 1492 (Собрание законодательства Российской ')+
								langs(' Федерации, 2017,  № 51, ст. 7806; 2018, № 18, ст. 2639) (далее – Программа). В отношении граждан, ')+
								langs(' пострадавших в результате несчастных случаев на производстве и профессиональных заболеваний, ')+
								langs(' указывается нуждаемость в конкретных лекарственных препаратах для лечения последствий несчастных ')+
								langs(' случаев на производстве и профессиональных заболеваний.')
						},
						{
							xtype: 'textarea',
							anchor: '100%',
							name: 'EvnPrescrMse_MeasureSurgery',
							hidden: getRegionNick() == 'kz',
							hideLabel: getRegionNick() == 'kz',
							fieldLabel: langs('Рекомендуемые мероприятия по реконструктивной хирургии ')+
								'<a title="Читать примечание" href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).stripNotePanel(&quot;note7&quot;);">?</a>'
						},
						{
							name: 'note7',
							hidden: true,
							xtype: 'panel',
							bodyStyle: 'padding: 3px; background-color: #eee; border: 1px solid #000;',
							html: langs('Указываются рекомендуемые мероприятия по реконструктивной хирургии согласно перечню видов ')+
								langs(' высокотехнологичной медицинской помощи, содержащему в том числе методы лечения и источники ')+
								langs(' финансового обеспечения высокотехнологичной медицинской помощи, предусмотренному приложением ')+
								langs(' к Программе')
						},
						{
							xtype: 'textarea',
							anchor: '100%',
							name: 'EvnPrescrMse_MeasureProstheticsOrthotics',
							hidden: getRegionNick() == 'kz',
							hideLabel: getRegionNick() == 'kz',
							fieldLabel: langs('Рекомендуемые мероприятия по протезированию и ортезированию ')+
								'<a title="Читать примечание" href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).stripNotePanel(&quot;note8&quot;);">?</a>'
						},
						{
							name: 'note8',
							hidden: true,
							xtype: 'panel',
							bodyStyle: 'padding: 3px; background-color: #eee; border: 1px solid #000;',
							html: langs('Указываются рекомендуемые мероприятия по протезированию и ортезированию в соответствии с ')+
								langs(' заключением врачей-специалистов в области протезирования и ортезирования')
						},
						{
							xtype: 'textarea',
							anchor: '100%',
							name: 'EvnPrescrMse_HealthResortTreatment',
							hidden: getRegionNick() == 'kz',
							hideLabel: getRegionNick() == 'kz',
							fieldLabel: langs('Санаторно-курортное лечение ')+
								'<a title="Читать примечание" href="javascript:Ext.getCmp(&quot;swDirectionOnMseEditForm&quot;).stripNotePanel(&quot;note9&quot;);">?</a>'
						},
						{
							name: 'note9',
							hidden: true,
							xtype: 'panel',
							bodyStyle: 'padding: 3px; background-color: #eee; border: 1px solid #000;',
							html: langs('Указываются рекомендации по санаторно-курортному лечению граждан, включенных в Федеральный ')+
								langs(' регистр лиц, имеющих право на получение государственной социальной помощи в виде набора ')+
								langs(' социальных услуг, при наличии медицинских показаний и отсутствии медицинских противопоказаний, ')+
								langs(' с указанием профиля, сезона рекомендованного лечения')
						}
					]
				}
			]
		});


		this.EvnVKExpertViewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			id: 'domef_EvnVKExpertsGrid',
			pageSize: 20,
			object: 'EvnVKExpert',
			obj_isEvn: false,
			autoScroll: true,
			border: false,
			mainType: 'grid',
			autoLoadData: false,
			root: 'data',
			actions: [
				{ name: 'action_add', hidden: true, tooltip: langs('Добавить врача в список экспертов'), handler: function(){cur_win.openEvnVKExpertWindow();} },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true, tooltip: langs('Удалить врача из списка экспертов') },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'EvnVKExpert_id', type: 'int', hidden: true, key: true },
				{ name: 'EvnVK_id', type: 'int', hidden: true },
				{ name: 'MedService_id', type: 'int', hidden: true },
				{ name: 'ExpertMedStaffType_id', type: 'int', hidden: true },
				{ name: 'MF_Person_FIO', type: 'string',  header: langs('Врач ВК'), width: 300 },
				//{ name: 'LpuSection_Name', type: 'string', header: 'Отделение', width: 200 },
				{ name: 'ExpertMedStaffType_Name', type: 'string', header: langs('Должность'), id: 'autoexpand' }
			],
			dataUrl: '/?c=ClinExWork&m=getEvnVKExpert',
			totalProperty: 'totalCount'
		});
		
		this.EvnVKExpertForm = new sw.Promed.Panel({
			title: langs('Состав экспертов'),
			collapsible: true,
			EvnVK_id: null,
			EvnVK_MedService_id: null,
			collapsed: true,
			isLoadedViewFrame: false,
			listeners: {
				expand: function(){
					if(!cur_win.EvnVKExpertForm.EvnVK_id) return false;
					if (!cur_win.EvnVKExpertForm.isLoadedViewFrame) {
						var grid = cur_win.EvnVKExpertViewFrame.getGrid();
						grid.getStore().baseParams = {EvnVK_id: cur_win.EvnVKExpertForm.EvnVK_id};
						cur_win.EvnVKExpertForm.isLoadedViewFrame = true;
						grid.getStore().load({
							callback: function() {
								grid.getStore().each(function(rec){
									if (rec.get('MedService_id')) {
										cur_win.EvnVKExpertForm.EvnVK_MedService_id = rec.get('MedService_id');
									}
								});
							}
						});
					}
				}
			},
			items: [
				this.EvnVKExpertViewFrame
			]
		});
		
	
		this.CommonForm = new Ext.form.FormPanel({
			region: 'center',
			url: '/?c=Mse&m=saveEvnPrescrMse',
			autoScroll: true,
			layout: 'form',
			items: [{
					xtype: 'swdatefield',
					name: 'EvnPrescrMse_issueDT',
					listeners: {
						'change': function(field, newValue) {
							cur_win.setMilitaryKind();
							cur_win.setPhysicalDevelopmentVis();
							cur_win.setFieldsVisible();
						}
					},
					fieldLabel: 'Дата выдачи',
					value: Ext.util.Format.date(new Date(), 'd.m.Y'),
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
				},
				this.PatientForm,
				this.DiagHistoryForm,
				this.MedicalRehabilitationForm,
				this.EvnStickForm,
				this.EvaluationStateForm,
				this.ReasonAndDiagnosis,
				this.UslugaComplexMSEGrid,
				this.ForecastsAndRecommendationsForm,
				this.EvnVKExpertForm,
				this.FilesPanelVK,
				this.FilesPanelMSE,
				this.EvnStatusHistoryGrid
			],
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[
				{ name: 'accessType' },
				{ name: 'EvnPrescrMse_id' },
				{ name: 'EvnPrescrMse_pid' },
				{ name: 'EvnPrescrMse_issueDT' },
				{ name: 'MedService_id' },
				{ name: 'TimetableMedService_id' },
				{ name: 'EvnPrescrMse_setDT'},
				{ name: 'EvnStatus_id'},
				{ name: 'Lpu_gid'},
				{ name: 'EvnQueue_id'},
				{ name: 'EvnPrescrMse_Descr' },
				{ name: 'EvnPrescrMse_IsExec' },
				{ name: 'EvnPrescrMse_IsFirstTime' },
				//{ name: 'Person_sid' },
				{ name: 'InvalidGroupType_id' },
				{ name: 'EvnPrescrMse_InvalidPercent' },
				{ name: 'EvnPrescrMse_IsWork' },
				{ name: 'Post_id' },
				{ name: 'EvnPrescrMse_ExpPost' },
				//{ name: 'Okved_id' },
				{ name: 'EvnPrescrMse_Prof' },
				{ name: 'EvnPrescrMse_ExpProf' },
				{ name: 'EvnPrescrMse_Spec' },
				{ name: 'EvnPrescrMse_ExpSpec' },
				{ name: 'EvnPrescrMse_Skill' },
				{ name: 'EvnPrescrMse_ExpSkill' },
				{ name: 'Org_id' },
				{ name: 'EvnPrescrMse_CondWork' },
				{ name: 'EvnPrescrMse_MainProf' }, 
				{ name: 'EvnPrescrMse_MainProfSkill' }, 
				{ name: 'Org_did' },
				{ name: 'EvnPrescrMse_Dop' },
				{ name: 'LearnGroupType_id' },
				//{ name: 'Okved_did' },
				{ name: 'EvnPrescrMse_ProfTraining' },
				{ name: 'EvnPrescrMse_OrgMedDateYear' },
				{ name: 'EvnPrescrMse_OrgMedDateMonth' },
				{ name: 'EvnPrescrMse_DiseaseHist' },
				{ name: 'EvnPrescrMse_LifeHist' },
				{ name: 'EvnPrescrMse_MedRes' },
				{ name: 'EvnPrescrMse_State' },
				{ name: 'EvnPrescrMse_DopRes' },
				{ name: 'StateNormType_id' },
				{ name: 'StateNormType_did' },
				{ name: 'Diag_id' },
				{ name: 'Diag_sid' },
				{ name: 'Diag_aid' },
				{ name: 'EvnPrescrMse_MainDisease' },
				{ name: 'MseDirectionAimType_id' },
				{ name: 'EvnPrescrMse_AimMseOver' },
				{ name: 'ClinicalForecastType_id' },
				{ name: 'ClinicalPotentialType_id' },
				{ name: 'ClinicalForecastType_did' },
				{ name: 'EvnPrescrMse_Recomm' },
				{ name: 'EvnPrescrMse_MeasureSurgery' },
				{ name: 'EvnPrescrMse_MeasureProstheticsOrthotics' },
				{ name: 'EvnPrescrMse_HealthResortTreatment' },
				{ name: 'EvnPrescrMse_IsCanAppear' },
				{ name: 'Org_sid' },
				{ name: 'Org_gid' },
				{ name: 'EvnPrescrMse_IsPalliative' },
				{ name: 'EvnMse_id' },
				{ name: 'InvalidPeriodType_id'},
				{ name: 'EvnPrescrMse_InvalidEndDate' },
				{ name: 'EvnPrescrMse_InvalidDate' },
				{ name: 'EvnPrescrMse_InvalidPeriod' },
				{ name: 'InvalidCouseType_id' },
				{ name: 'EvnPrescrMse_InvalidCouseAnother' },
				{ name: 'EvnPrescrMse_InvalidCouseAnotherLaw' },
				{ name: 'ProfDisabilityPeriod_id' },
				{ name: 'EvnPrescrMse_ProfDisabilityEndDate' },
				{ name: 'EvnPrescrMse_ProfDisabilityAgainPercent' },
				{ name: 'MeasuresRehabEffect_id' },
				{ name: 'IPRARegistry_id' },
				{ name: 'IPRARegistry_Number' },
				{ name: 'IPRARegistry_Protocol' },
				{ name: 'IPRARegistry_ProtocolDate' },
				{ name: 'MeasuresRehabEffect_IsRecovery' },
				{ name: 'IPRAResult_rid' },
				{ name: 'MeasuresRehabEffect_IsCompensation' },
				{ name: 'IPRAResult_cid' },
				{ name: 'MeasuresRehabEffect_Comment' },
				{ name: 'PhysiqueType_id' },
				{ name: 'EvnPrescrMse_DailyPhysicDepartures' },
				{ name: 'EvnPrescrMse_Waist' },
				{ name: 'EvnPrescrMse_Hips' },
				{ name: 'EvnPrescrMse_WeightBirth' },
				{ name: 'EvnPrescrMse_PhysicalDevelopment' },
				{ name: 'DocumentAuthority_id' },
				{ name: 'EvnPrescrMse_DocumentSer' },
				{ name: 'EvnPrescrMse_DocumentNum' },
				{ name: 'EvnPrescrMse_DocumentIssue' },
				{ name: 'EvnPrescrMse_DocumentDate' },
				{ name: 'OAddress_Zip' },
				{ name: 'OKLCountry_id' },
				{ name: 'OKLRGN_id' },
				{ name: 'OKLSubRGN_id' },
				{ name: 'OKLCity_id' },
				{ name: 'OKLTown_id' },
				{ name: 'OKLStreet_id' },
				{ name: 'OAddress_House' },
				{ name: 'OAddress_Corpus' },
				{ name: 'OAddress_Flat' },
				{ name: 'OAddress_Address' },
				{ name: 'OAddress_AddressText' },
				{ name: 'EAddress_Zip' },
				{ name: 'EKLCountry_id' },
				{ name: 'EKLRGN_id' },
				{ name: 'EKLSubRGN_id' },
				{ name: 'EKLCity_id' },
				{ name: 'EKLTown_id' },
				{ name: 'EKLStreet_id' },
				{ name: 'EAddress_House' },
				{ name: 'EAddress_Corpus' },
				{ name: 'EAddress_Flat' },
				{ name: 'EAddress_Address' },
				{ name: 'EAddress_AddressText' },
				{ name: 'MilitaryKind_id' }
			])
		});
	
		Ext.apply(this,
		{
			items: [
				this.PersonFrame,
				this.CommonForm
			]
		});
		sw.Promed.swDirectionOnMseEditForm.superclass.initComponent.apply(this, arguments);
		
        this.UslugaComplexMSEGrid.ViewToolbar.on('render', function(vt){
            this.ViewActions['action_rec'] = new Ext.Action({
                name: 'action_rec',
                handler: function() { cur_win.addUslugaComplexMSERecommended(); },
                text: 'Рекомендованные',
                iconCls: 'ok16'
            });
            vt.insertButton(1,this.ViewActions['action_rec']);
            return true;
        }, this.UslugaComplexMSEGrid);
	}
});
