/**
 * ufa_swAnalyzerTestEditWindow - окно редактирования "Тест анализатора"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Common
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   Alexander Chebukin
 * @version	  06.2012
 * @comment
 */
sw.Promed.swAnalyzerTestEditWindow = Ext.extend(sw.Promed.BaseForm,	{
	maximized: true,
	objectName: 'ufa_swAnalyzerTestEditWindow',
	objectSrc: '/jscore/Forms/Admin/Ufa/ufa_swAnalyzerTestEditWindow.js',
	title: lang['test_analizatora'],
	layout: 'border',
	id: 'AnalyzerTestEditWindow',
	modal: true,
	shim: false,
	width: 700,
	resizable: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	_isSaved: false,//флаг-признак "форма сохранялась после открытия"
	doSave:  function(callback) {
		var win = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						win.findById('AnalyzerTestEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		var params = {};
		params.AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
		params.AnalyzerTest_begDT = Ext.util.Format.date(win.form.findField('AnalyzerTest_begDT').getValue(), 'Y-m-d H:i:s');
		Ext.Ajax.request({
			failure: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
					sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к серверу');
				}
			},
			params: params,
			success: function(response, options) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(!Ext.isEmpty(response_obj[0])){
					sw.swMsg.alert('Ошибка', 'Открытие записи датой позже '+response_obj[0].endDT+' невозможно, т.к. существуют связанные записи.', 
						function(){win.form.findField('AnalyzerTest_begDT').focus(true);}
					);
					log(response_obj);
				} else {
					
					console.log("action:"+win.action);
					console.log("_isSaved:"+win._isSaved);
					if (win.action == "add" && !win._isSaved) {
						console.log("AnalyzerModel_id:"+win.AnalyzerModel_id);
						console.log("Analyzer_id:"+win.Analyzer_id);
						var params = {};
						params.Analyzer_id = win.Analyzer_id;
						params.AnalyzerModel_id = win.AnalyzerModel_id;
						params.UslugaComplex_id = win.form.findField('UslugaComplex_id').getValue();
						params.ReagentModel_id = win.ReagentModel_id;
						Ext.Ajax.request({
							failure: function(response, options) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
								}
								else {
									sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к серверу');
								}
							},
							params: params,
							success: function(response, options) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if(!Ext.isEmpty(response_obj[0])){
									sw.swMsg.alert('Ошибка', 'Услуга с кодом '+response_obj[0].UslugaComplex_Code+' уже добавлена. Выберите другой код услуги', 
										function(){ //win.form.findField('UslugaComplex_id').focus(true);
										}
									);
									log(response_obj);
								} else {
									win._isSaved = true;
									win.submit(callback);
								}
							},
							url: '/?c=AnalyzerTest&m=checkAnalyzerTestIsExists'
						});
					} else {
						win.submit(callback);
					}
				}
			},
			url: '/?c=AnalyzerTest&m=checkAnalyzerTestBegDate'
		});
		return true;
	},
	submit: function(callback)
	{
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		
		if (this.form.findField('AnalyzerTest_SysNick').disabled) {
			params.AnalyzerTest_SysNick = this.form.findField('AnalyzerTest_SysNick').getValue();
		}
		
		params.action = win.action;
		this.form.submit(
			{
				params: params,
				failure: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.Error_Code)
						{
							Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
						}
					}
				},
				success: function(result_form, action)
				{
					win.form.findField('AnalyzerTest_id').setValue(action.result.AnalyzerTest_id);
					loadMask.hide();
					if (undefined == callback) {
						win.callback(win.owner, action.result.AnalyzerTest_id);
						win.hide();
					} else {
						callback(action.result.AnalyzerTest_id);
					}

				}
			});
	},
	show: function(params)
	{
		this.params = params;
		var win = this;
		win._isSaved = false; //сброс флага, т.к. еще не сохраняли форму после открытия
		sw.Promed.swAnalyzerTestEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerTest_id = null;
		this.Analyzer_IsUseAutoReg = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { win.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_obyazatelnyiy_parametr_-_action'], function() { win.hide(); });
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].AnalyzerTest_id ) {
			this.AnalyzerTest_id = arguments[0].AnalyzerTest_id;
		}
		if ( arguments[0].Analyzer_IsUseAutoReg ) {
			this.Analyzer_IsUseAutoReg = arguments[0].Analyzer_IsUseAutoReg;
		}
		if ( arguments[0].AnalyzerTest_pid ) {
			this.AnalyzerTest_pid = arguments[0].AnalyzerTest_pid;
		}		

		this.AnalyzerModel_id = arguments[0].AnalyzerModel_id || null;
		this.ReagentModel_id = arguments[0].ReagentModel_id || null;
		this.ReagentNormRate_Name = arguments[0].ReagentNormRate_Name || null;
		this.Analyzer_id = arguments[0].Analyzer_id || null;
	
		if (Ext.isEmpty(this.AnalyzerModel_id) && Ext.isEmpty(this.Analyzer_id)) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_obyazatelnyie_parametryi_analizator_ili_model_analizatora'], function() { win.hide(); });
		}
		
		win.AnalyzerTestRefValuesGrid.addActions({
			name: 'action_saverv',
			text: lang['sohranit_nabor'],
			handler: function()
			{
				win.openRefValuesSetEditWindow('save');
			}
		});
		
		win.AnalyzerTestRefValuesGrid.addActions({
			name: 'action_loadrv',
			text: lang['zagruzit_nabor'],
			handler: function()
			{
				win.openRefValuesSetEditWindow('load');
			}
		});
		
		if (Ext.isEmpty(this.Analyzer_id)) {
			win.AnalyzerTestRefValuesGrid.setActionHidden('action_saverv', true);
			win.AnalyzerTestRefValuesGrid.setActionHidden('action_loadrv', true);
			win.form.findField('UslugaCategory_id').hideContainer();
			win.form.findField('UslugaCategory_id').setAllowBlank(true);
			win.form.findField('AnalyzerTest_HasLisLink').hideContainer();
			win.form.findField('AnalyzerTest_begDT').hideContainer();
			win.form.findField('AnalyzerTest_begDT').setAllowBlank(true);
			win.form.findField('AnalyzerTest_endDT').hideContainer();
		} else {
			win.AnalyzerTestRefValuesGrid.setActionHidden('action_saverv', false);
			win.AnalyzerTestRefValuesGrid.setActionHidden('action_loadrv', false);
			win.form.findField('UslugaCategory_id').showContainer();
			win.form.findField('UslugaCategory_id').setAllowBlank(false);
			win.form.findField('AnalyzerTest_HasLisLink').showContainer();
			win.form.findField('AnalyzerTest_begDT').showContainer();
			win.form.findField('AnalyzerTest_begDT').setAllowBlank(false);
			win.form.findField('AnalyzerTest_endDT').showContainer();
		}
		var ReagentNormRatePanel = Ext.getCmp('ReagentNormRatePanel');
		if ( Ext.isEmpty(this.ReagentModel_id) ) {//режим выбора тестов для экземпляра анализатора
			ReagentNormRatePanel.hide(); // форму нормы расхода скрываем
			win.form.findField('RefMaterial_id').hideContainer();//биоматериал скрываем
			//поле "Приоритет" показываем:
			win.form.findField('AnalyzerTest_SortCode').enable();
			win.form.findField('AnalyzerTest_SortCode').showContainer();
		} else {// режим настройки тестов для модели
			ReagentNormRatePanel.show(); // форма нормы расхода доступна
			win.form.findField('RefMaterial_id').showContainer();
			//поле "Приоритет" скрываем:
			win.form.findField('AnalyzerTest_SortCode').hideContainer();
			win.form.findField('AnalyzerTest_SortCode').disable();
		}
		win.doLayout();
		
		if ( arguments[0].AnalyzerTest_pid ) {
			this.AnalyzerTest_pid = arguments[0].AnalyzerTest_pid;
		} else {
			this.AnalyzerTest_pid = null;
		}
		this.form.reset();
		win.form.findField('AnalyzerModel_id').setValue(win.AnalyzerModel_id);
		win.form.findField('ReagentModel_id').setValue(win.ReagentModel_id);
		win.form.findField('Analyzer_id').setValue(win.Analyzer_id);
		win.form.findField('AnalyzerTest_pid').setValue(win.AnalyzerTest_pid);
		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		
		var uslugacomplex_combo = win.form.findField('UslugaComplex_id');
		if (Ext.isEmpty(this.Analyzer_id)) {
			if ( getRegionNick() == 'kz' ) {
				uslugacomplex_combo.setUslugaCategoryList(['classmedus']);
			}
			else {
				uslugacomplex_combo.setUslugaCategoryList(['gost2011']);
			}
			uslugacomplex_combo.setAllowedUslugaComplexAttributeList(['lab']);
			uslugacomplex_combo.getStore().baseParams.hasLinkWithGost2011 = null;
		} else {
			uslugacomplex_combo.setUslugaCategoryList();
			uslugacomplex_combo.setAllowedUslugaComplexAttributeList(['lab']);
			uslugacomplex_combo.getStore().baseParams.hasLinkWithGost2011 = 1;
		}
		uslugacomplex_combo.getStore().baseParams.Analyzer_id = this.Analyzer_id;
	
		switch (arguments[0].action) {
			case 'add':
				win.setTitle(lang['test_analizatora_dobavlenie']);
				win.enableEdit(true);
				
				if (!isSuperAdmin() && !isLpuAdmin()) {
					this.form.findField('AnalyzerTest_SysNick').disable();
				} else {
					this.form.findField('AnalyzerTest_SysNick').enable();
				}
				
				win.QuantitativeTestUnitGrid.removeAll();
				win.QualitativeTestAnswerAnalyzerTestGrid.removeAll();
				win.AnalyzerTestRefValuesGrid.removeAll();
				win.AnalyzerTestType_idChange();
				win.form.findField('ReagentNormRate_Name').setValue( this.ReagentNormRate_Name );
				loadMask.hide();
				win.form.findField('UslugaComplex_id').focus(true);
				break;
			case 'edit':
			case 'view':
				if (win.action == 'edit') {
					win.setTitle(lang['test_analizatora_redaktirovanie']);
					win.enableEdit(true);
					
					if (!isSuperAdmin() && !isLpuAdmin()) {
						this.form.findField('AnalyzerTest_SysNick').disable();
					} else {
						this.form.findField('AnalyzerTest_SysNick').enable();
					}
				} else {
					win.setTitle(lang['test_analizatora_prosmotr']);
					win.enableEdit(false);
				}
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						win.AnalyzerTestType_idChange();
						win.hide();
					},
					params:{
						AnalyzerTest_id: win.AnalyzerTest_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						win.form.setValues(result[0]);
						
						var uslugacomplex_id = uslugacomplex_combo.getValue();
						if (!Ext.isEmpty(uslugacomplex_id)) {
							uslugacomplex_combo.getStore().load({
								callback: function() {
									uslugacomplex_combo.getStore().each(function(record) {
										if (record.data.UslugaComplex_id == uslugacomplex_id)
										{
											uslugacomplex_combo.setValue(uslugacomplex_id);
											uslugacomplex_combo.fireEvent('select', uslugacomplex_combo, record, 0);
											uslugacomplex_combo.collapse();
											uslugacomplex_combo.focus(true);
										}
									});
								},
								params: { "UslugaComplex_id": uslugacomplex_id }
							});
						}
						win.AnalyzerTestType_idChange();
						var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
						win.QuantitativeTestUnitGrid.loadData({params:{AnalyzerTest_id:AnalyzerTest_id}, globalFilters:{AnalyzerTest_id:AnalyzerTest_id}});
						win.QualitativeTestAnswerAnalyzerTestGrid.loadData({params:{AnalyzerTest_id:AnalyzerTest_id}, globalFilters:{AnalyzerTest_id:AnalyzerTest_id}});
						win.AnalyzerTestRefValuesGrid.loadData({params:{AnalyzerTest_id:AnalyzerTest_id}, globalFilters:{AnalyzerTest_id:AnalyzerTest_id}});
						loadMask.hide();
						win.form.findField('UslugaComplex_id').focus(true);
					},
					url:'/?c=AnalyzerTest&m=load'
				});
				//Если хранилище грида формул не пустое, то ставим флажок "Рассчитываемый" и загружаем грид формул
				win.loadAnalyzerTestFormulaGrid();
				if(win.form.findField('AnalyzerTest_Formula').getValue()){
					win.AnalyzerTestFormulaGrid.setVisible(true);
				}
				else{
					win.AnalyzerTestFormulaGrid.setVisible(false);
				}					
				Ext.getCmp('AnalyzerTestFormulaGrid').getGrid().getStore().on('load', function() {
				if (Ext.getCmp('AnalyzerTestFormulaGrid').getGrid().getStore().getCount() >0 && 
						Ext.getCmp('AnalyzerTestFormulaGrid').getGrid().getStore().getAt(0).get('AnalyzerTest_id') != null) {
					win.form.findField('AnalyzerTest_Formula').setValue(1);
				}
				//https://redmine.swan.perm.ru/issues/62598
				
				});
				
				break;
		}

	},
	openRefValuesSetEditWindow: function(action) {
		var win = this;
		var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
							
		var addition = function (AnalyzerTest_id) {
			var p = {
				AnalyzerTest_id: AnalyzerTest_id,
				action: action,
				callback: function (){
					win.AnalyzerTestRefValuesGrid.getGrid().getStore().reload();
				}
			};
			
			getWnd('swRefValuesSetEditWindow').show(p);
		}
		if (AnalyzerTest_id > 0) {
			addition(AnalyzerTest_id);
		} else {
			win.doSave(addition);
		}
	},
	openQuantitativeTestUnitEditWindow: function(action) {
		var win = this;
		var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
		var AnalyzerTest_begDT = Ext.util.Format.date(win.form.findField('AnalyzerTest_begDT').getValue(), 'Y-m-d H:i:s');
		var grid = win.QuantitativeTestUnitGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		
		if (action == 'edit' && (!selected_record || Ext.isEmpty(selected_record.get('QuantitativeTestUnit_id'))))
		{
			return false;
		}
							
		var addition = function (AnalyzerTest_id) {
			var p = {
				AnalyzerTest_id: AnalyzerTest_id,
				AnalyzerTest_begDT: AnalyzerTest_begDT,
				action: action,
				callback: function (){
					var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
					win.QuantitativeTestUnitGrid.loadData({params:{AnalyzerTest_id:AnalyzerTest_id}, globalFilters:{AnalyzerTest_id:AnalyzerTest_id}});
				}
			};
			
			if (action == 'edit') {
				p.QuantitativeTestUnit_id = selected_record.get('QuantitativeTestUnit_id');
			}
			
			var count = 0;
			grid.getStore().each( function (rec) {
				if (!Ext.isEmpty(rec.get('QuantitativeTestUnit_id')) && (action == 'add' || rec.get('QuantitativeTestUnit_id') != selected_record.get('QuantitativeTestUnit_id'))) {
					count++;
				}
			});
			
			if (count == 0) {
				p.isBase = true;
			}
			
			p.AnalyzerTestType_Code = parseInt(win.form.findField('AnalyzerTestType_id').getFieldValue('AnalyzerTestType_Code'));

			var addedUnits = '';
			if((win.QuantitativeTestUnitGrid.getGrid().getStore().getCount() > 0)&&!Ext.isEmpty(win.QuantitativeTestUnitGrid.getGrid().getStore().getAt(0).data.Unit_id)){
				addedUnits = ' and Unit_id not in (';
				win.QuantitativeTestUnitGrid.getGrid().getStore().each(function(record){
					if(action == 'edit'){
						if(record.data.Unit_id != selected_record.data.Unit_id){
							addedUnits += (record.data.Unit_id+', ');
						}
					} else {
						addedUnits += (record.data.Unit_id+', ');
					}
					
				});
				if(addedUnits !== ' and Unit_id not in ('){
					addedUnits = addedUnits.substr(0,(addedUnits.length - 2));
					addedUnits += ')';
				} else {
					addedUnits = '';
				}
			}
			p.addedUnits = addedUnits;
			
			getWnd('swQuantitativeTestUnitEditWindow').show(p);
		}
		if (AnalyzerTest_id > 0) {
			addition(AnalyzerTest_id);
		} else {
			win.doSave(addition);
		}
	},
	openAnalyzerTestRefValuesEditWindow: function(action) {
		var win = this;
		var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
		var grid = win.AnalyzerTestRefValuesGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		
		if (action == 'edit' && (!selected_record || Ext.isEmpty(selected_record.get('AnalyzerTestRefValues_id'))))
		{
			return false;
		}
							
		var addition = function (AnalyzerTest_id) {
			var p = {
				AnalyzerTest_id: AnalyzerTest_id,
				action: action,
				callback: function (){
					var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
					win.AnalyzerTestRefValuesGrid.loadData({params:{AnalyzerTest_id:AnalyzerTest_id}, globalFilters:{AnalyzerTest_id:AnalyzerTest_id}});
				}
			};
			
			if (action == 'edit') {
				p.AnalyzerTestRefValues_id = selected_record.get('AnalyzerTestRefValues_id');
			}
			
			p.allowedUnits = [];
						
			win.QuantitativeTestUnitGrid.getGrid().getStore().each(function(record) {
				if (!Ext.isEmpty(record.get('Unit_id')))
				{
					p.allowedUnits.push(record.get('Unit_id'));
				}
			});
			
			p.allowedQualitativeTestAnswerAnalyzerTests = [];
						
			win.QualitativeTestAnswerAnalyzerTestGrid.getGrid().getStore().each(function(record) {
				if (!Ext.isEmpty(record.get('QualitativeTestAnswerAnalyzerTest_id')))
				{
					p.allowedQualitativeTestAnswerAnalyzerTests.push(record.get('QualitativeTestAnswerAnalyzerTest_id'));
				}
			});
			
			p.AnalyzerTestType_Code = parseInt(win.form.findField('AnalyzerTestType_id').getFieldValue('AnalyzerTestType_Code'));

			getWnd('swAnalyzerTestRefValuesEditWindow').show(p);
		}
		if (AnalyzerTest_id > 0) {
			addition(AnalyzerTest_id);
		} else {
			win.doSave(addition);
		}
	},
	deleteAnalyzerTestRefValues: function() {
		var win = this;
		var grid = win.AnalyzerTestRefValuesGrid.getGrid();
		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('AnalyzerTestRefValues_id')) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(win.getEl(), {msg:lang['udalenie']});
					loadMask.show();
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_proizoshla_oshibka']);
								}
								else {
									grid.getStore().remove(record);
								}
								if (grid.getStore().getCount() > 0) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_referensnogo_znacheniya_voznikli_oshibki']);
							}
						},
						params:{
							AnalyzerTestRefValues_id:record.get('AnalyzerTestRefValues_id')
						},
						url:'/?c=AnalyzerTestRefValues&m=delete'
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		})
	},
	deleteQuantitativeTestUnit: function() {
		var win = this;
		var grid = win.QuantitativeTestUnitGrid.getGrid();
		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('QuantitativeTestUnit_id')) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(win.getEl(), {msg:lang['udalenie']});
					loadMask.show();
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_proizoshla_oshibka']);
								}
								else {
									grid.getStore().remove(record);
								}
								if (grid.getStore().getCount() > 0) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_referensnogo_znacheniya_voznikli_oshibki']);
							}
						},
						params:{
							QuantitativeTestUnit_id:record.get('QuantitativeTestUnit_id')
						},
						url:'/?c=QuantitativeTestUnit&m=delete'
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		})
	},
	deleteQualitativeTestAnswerAnalyzerTest: function() {
		var win = this;
		var grid = win.QualitativeTestAnswerAnalyzerTestGrid.getGrid();
		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('QualitativeTestAnswerAnalyzerTest_id')) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(win.getEl(), {msg:lang['udalenie']});
					loadMask.show();
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_proizoshla_oshibka']);
								}
								else {
									grid.getStore().remove(record);
								}
								if (grid.getStore().getCount() > 0) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_referensnogo_znacheniya_voznikli_oshibki']);
							}
						},
						params:{
							QualitativeTestAnswerAnalyzerTest_id:record.get('QualitativeTestAnswerAnalyzerTest_id')
						},
						url:'/?c=QualitativeTestAnswerAnalyzerTest&m=delete'
					});
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		})
	},
	//https://redmine.swan.perm.ru/issues/62598
	openAnalyzerTestFormulaEditWindow : function(action){

		var params = {};

		if(action == 'add'){
			params = {
				title : 'Добавить формулу',
				action: action,
				Analyzer_id : this.form.findField('Analyzer_id').getValue(),
				AnalyzerTest_id : this.form.findField('AnalyzerTest_id').getValue(),
				AnalyzerTest_pid : this.form.findField('AnalyzerTest_pid').getValue(),
				storeParams: this.params.storeParams
			}				
		} else if (action == 'edit') {	
			if (Ext.getCmp('AnalyzerTestFormulaGrid').getGrid().getSelectionModel( ).getSelected() == undefined) {
				Ext.Msg.alert('Ошибка', 'Выделите формулу для редактирования');
				return false;
			}			
			params = {
				title : 'Редактировать формулу',
				action: action,
				Analyzer_id : this.form.findField('Analyzer_id').getValue(),
				AnalyzerTest_id : this.form.findField('AnalyzerTest_id').getValue(),
				AnalyzerTest_pid : this.form.findField('AnalyzerTest_pid').getValue(),
				storeParams: this.params.storeParams,
				recformula: this.AnalyzerTestFormulaGrid.getGrid().getSelectionModel( ).getSelected( )
			};					
		}
		getWnd('ufa_AnalyzerTestFormulaEditWindow').show({params:params});
	},
	//Загрузка данных в грид формул
	loadAnalyzerTestFormulaGrid: function() {
		var win = this;
		var params = 
			{
				Analyzer_id : this.Analyzer_id,//win.form.findField('Analyzer_id').getValue(),
				AnalyzerTest_id : this.AnalyzerTest_id,//win.form.findField('AnalyzerTest_id').getValue(),
				AnalyzerTest_pid : this.AnalyzerTest_pid//win.form.findField('AnalyzerTest_pid').getValue()
			}												

		Ext.getCmp('AnalyzerTestFormulaGrid').getGrid().getStore().load({params:params});
	},
	//Формирование объекта для всплывающих подсказок
	parslist: function(s){
		var usluga = s.split(/\[delin\]/g);
		delete usluga[0];

		var baseUsluga = {};

		for(var k in usluga){
			if (typeof usluga[k] == 'string') {
				var temp = usluga[k].split(/\[delout\]/);				
			}
		  if(temp[0] != ''){
			   var code = temp[0];
			   baseUsluga[code] = null;
			   baseUsluga[code] = temp[1];
		  }
		}
		return baseUsluga;
    },
	//Парсер формул для всплывающих подсказок
	parsformul: function(f,s) {
		var m = this.parslist(s);
		for (var k in m) {
			f = f.replace(new RegExp(k,'g'),'{'+m[k]+'}');			
		}
		return f;		
	},
        //end
	initComponent: function()
	{
		var win = this;
        
			//https://redmine.swan.perm.ru/issues/62598
			//Грид для формул, если тест "рассчитываемый"    
		this.AnalyzerTestFormulaGrid = new sw.Promed.ViewFrame({
			focusOnFirstLoad: false,
			actions: [
				{name: 'action_add',
					handler:function () {
						win.openAnalyzerTestFormulaEditWindow('add');
					}
				},
				{name: 'action_edit',
					handler:function () {
						win.openAnalyzerTestFormulaEditWindow('edit');
					}
				},
				{name: 'action_view', hidden: true},
				{name: 'action_delete',
					handler:function () {
						if (Ext.getCmp('AnalyzerTestFormulaGrid').getGrid().getSelectionModel( ).getSelected() == undefined) {
							Ext.Msg.alert('Ошибка', 'Выделите формулу для удаления');
							return false;
						}
						Ext.Msg.show({
						   title:'Удаление формулы',
						   msg: 'Вы действительно хотите удалить выделенную формулу?',
						   buttons: Ext.Msg.YESNO,
						   fn: function(btn) {
								if (btn == 'yes') {
									Ext.Ajax.request({
										url: '/?c=ufa_AnalyzerTestFormula&m=AnalyzerTestFormula_del',
										params: {
											AnalyzerTestFormula_id: win.AnalyzerTestFormulaGrid.getGrid().getSelectionModel( ).getSelected().get('AnalyzerTestFormula_id')
										},
										callback: function (options, success, response) {
											if (success === true) {
												Ext.getCmp('AnalyzerTestEditWindow').loadAnalyzerTestFormulaGrid();
											} else {
												//console.log('Отправка данных в регистр ИПРА не успешна');
											}
										}
									});									
								}	
						   },
						   animEl: 'elId',
						   icon: Ext.MessageBox.QUESTION
						});

					}
				},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=ufa_AnalyzerTestFormula&m=getAnalyzerTestFormula',
			object: 'AnalyzerTestFormulaGrid',
			uniqueId: true,
			scheme: 'lis',
			id: 'AnalyzerTestFormulaGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			onRowSelect: function(sm,index,record) {
				if (win.AnalyzerTestFormulaGrid.getGrid().getStore().getCount() > 1 && record.get('QuantitativeTestUnit_IsBase') && record.get('QuantitativeTestUnit_IsBase') != 'false' ) {
					win.AnalyzerTestFormulaGrid.setActionDisabled('action_delete', true);
				} else {
					win.AnalyzerTestFormulaGrid.setActionDisabled('action_delete', false);
				} 
			},
			stringfields: [
                                {name: 'AnalyzerTestFormula_id', type: 'int', header: 'ID', key: true},
                                {name: 'AnalyzerTestFormula_Code', header: 'Код', type: 'int', width: 80},
								{name: 'list', type: 'string', hidden: true},
                                {name: 'AnalyzerTestFormula_Formula', header: 'Формула', autoexpand: true, renderer: function (v,m,r) {
									if (v != null) {
										m.attr = 'data-qtip="'+win.parsformul(v,r.get('list'))+'"';
										return v;										
									}
								}},
                                {name: 'AnalyzerTestFormula_Comment', header: 'Примечание', type: 'string', width: 480},
								{name: 'AnalyzerTestFormula_insDT', header: 'Дата сохранения', renderer: function(v,p,r) {
									if (v != null) {
										return r.get('AnalyzerTestFormula_insDT').date;
									}	
									},  width: 120},
                                {name: 'Analyzer_id', type: 'int', hidden: true},
                                {name: 'AnalyzerTest_id', type: 'int', hidden: true}
			],
			title: 'Список формул',
			toolbar: true,
                        height: 200,
			focusOn: {
				name: 'ATEW_SaveButton',
				type: 'other'
			},
			focusPrev: {
				name: 'ATEW_AnalyzerTestType_id',
				type: 'other'
			}
		});                
                //end
                
		// единицы измерения
		this.QuantitativeTestUnitGrid = new sw.Promed.ViewFrame({
			focusOnFirstLoad: false,
			actions: [
				{name: 'action_add',
					handler:function () {
						win.openQuantitativeTestUnitEditWindow('add');
					}
				},
				{name: 'action_edit',
					handler:function () {
						win.openQuantitativeTestUnitEditWindow('edit');
					}
				},
				{name: 'action_view', hidden: true},
				{name: 'action_delete',
					handler:function () {
						win.deleteQuantitativeTestUnit();
					}
				},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=QuantitativeTestUnit&m=loadList',
			object: 'QuantitativeTestUnit',
			uniqueId: true,
			editformclassname: 'swQuantitativeTestUnitEditWindow',
			scheme: 'lis',
			id: 'QuantitativeTestUnitGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			onRowSelect: function(sm,index,record) {
				if (win.QuantitativeTestUnitGrid.getGrid().getStore().getCount() > 1 && record.get('QuantitativeTestUnit_IsBase') && record.get('QuantitativeTestUnit_IsBase') != 'false' ) {
					win.QuantitativeTestUnitGrid.setActionDisabled('action_delete', true);
				} else {
					win.QuantitativeTestUnitGrid.setActionDisabled('action_delete', false);
				}
			},
			stringfields: [
				{name: 'QuantitativeTestUnit_id', type: 'int', header: 'ID', key: true},
				{name: 'Unit_Name', type: 'string', header: lang['naimenovanie'], autoexpand: true},
				{name: 'Unit_id', type: 'int', hidden: true},
				{name: 'QuantitativeTestUnit_IsBase', header: lang['bazovaya'], type: 'checkbox', width: 80},
				{name: 'QuantitativeTestUnit_CoeffEnum', header: lang['koeffitsient_perescheta'], type: 'float', width: 160},
				{name: 'QualitativeTestAnswer_id', type: 'int', hidden: true},
				{name: 'QuantitativeTestUnit_Deleted', type: 'int', hidden: true}
			],
			title: lang['edinitsyi_izmereniya'],
			toolbar: true,
			focusOn: {
				name: 'ATEW_SaveButton',
				type: 'other'
			},
			focusPrev: {
				name: 'ATEW_AnalyzerTestType_id',
				type: 'other'
			}
		});
		
		// референсные значения
		this.AnalyzerTestRefValuesGrid = new sw.Promed.ViewFrame({
			focusOnFirstLoad: false,
			actions: [
				{name: 'action_add',
					handler:function () {
						win.openAnalyzerTestRefValuesEditWindow('add');
					}
				},
				{name: 'action_edit',
					handler:function () {
						win.openAnalyzerTestRefValuesEditWindow('edit');
					}
				},
				{name: 'action_view', hidden: true},
				{name: 'action_delete',
					handler:function () {
						win.deleteAnalyzerTestRefValues();
					}
				},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=AnalyzerTestRefValues&m=loadList',
			height: 200,
			object: 'AnalyzerTestRefValues',
			uniqueId: true,
			editformclassname: 'swAnalyzerTestRefValuesEditWindow',
			scheme: 'lis',
			id: 'AnalyzerTestRefValuesGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'AnalyzerTestRefValues_id', type: 'int', header: 'ID', key: true},
				{name: 'RefValues_id', type: 'int', hidden: true},
				{name: 'RefValues_Name', type: 'string', header: lang['naimenovanie'], autoexpand: true},
				{name: 'RefValues_Limit', header: lang['norm_znacheniya'], type: 'string', width: 100},
				{name: 'RefValues_CritValue', header: lang['krit_znacheniya'], type: 'string', width: 100},
				{name: 'Unit_Name', header: lang['ed_izm'], type: 'string', width: 100},
				{name: 'RefValues_Description', header: lang['kommentariy'], type: 'string', width: 150},
				{name: 'Sex_Name', header: lang['pol'], type: 'string', width: 100},
				{name: 'RefValues_Age', header: lang['vozrast'], type: 'string', width: 100},
				{name: 'HormonalPhaseType_Name', header: lang['faza_tsikla'], type: 'string', width: 100},
				{name: 'RefValues_Pregnancy', header: lang['beremennost'], type: 'string', width: 100},
				{name: 'RefValues_TimeOfDay', header: lang['vremya_sutok_chas'], type: 'string', width: 100}
			],
			title: lang['referensnyie_znacheniya'],
			toolbar: true,
			focusOn: {
				name: 'ATEW_SaveButton',
				type: 'other'
			},
			focusPrev: {
				name: 'ATEW_AnalyzerTestType_id',
				type: 'other'
			}
		});
		
		// варианты ответов
		this.QualitativeTestAnswerAnalyzerTestGrid = new sw.Promed.ViewFrame({
			focusOnFirstLoad: false,
			actions: [
				{name: 'action_add',
					handler:function () {
						var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
						var addition = function (AnalyzerTest_id) {
							var p = {
								AnalyzerTest_id: AnalyzerTest_id,
								action:'add',
								callback: function (){
									var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
									win.QualitativeTestAnswerAnalyzerTestGrid.loadData({params:{AnalyzerTest_id:AnalyzerTest_id}, globalFilters:{AnalyzerTest_id:AnalyzerTest_id}});
								}
							};
							getWnd('swQualitativeTestAnswerAnalyzerTestEditWindow').show(p);
						}
						if (AnalyzerTest_id > 0) {
							addition(AnalyzerTest_id);
						} else {
							win.doSave(addition);
						}
					}
				},
				{name: 'action_edit'},
				{name: 'action_view', hidden: true},
				{name: 'action_delete',
					handler:function () {
						win.deleteQualitativeTestAnswerAnalyzerTest();
					}
				},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=QualitativeTestAnswerAnalyzerTest&m=loadList',
			object: 'QualitativeTestAnswerAnalyzerTest',
			uniqueId: true,
			editformclassname: 'swQualitativeTestAnswerAnalyzerTestEditWindow',
			scheme: 'lis',
			id: 'QualitativeTestAnswerAnalyzerTestGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'QualitativeTestAnswerAnalyzerTest_id', type: 'int', header: 'ID', key: true},
				{name: 'QualitativeTestAnswerAnalyzerTest_Answer', type: 'string', header: lang['naimenovanie'], autoexpand: true},
				{name: 'QualitativeTestAnswerAnalyzerTest_SortCode', type: 'string', header: langs('Приоритет отображения ответа')}
			],
			title: lang['variantyi_otvetov_dlya_kachestvennyih'],
			toolbar: true,
			focusOn: {
				name: 'ATEW_SaveButton',
				type: 'other'
			},
			focusPrev: {
				name: 'ATEW_AnalyzerTestType_id',
				type: 'other'
			}
		});
                //https://redmine.swan.perm.ru/issues/62598
		win.AnalyzerTestFormulaPanel = new sw.Promed.Panel({
			title: '',
			layout: 'card',
			region: 'south',
			height: 200,
			activeItem: 0,
			border: false,
			items: [
				 win.AnalyzerTestFormulaGrid
			]
		});
                //end
                
		win.CardPanel = new sw.Promed.Panel({
			title: '',
			layout: 'card',
			region: 'north',
			height: 200,
			activeItem: 0,
			border: false,
			items: [
				win.QuantitativeTestUnitGrid,
				win.QualitativeTestAnswerAnalyzerTestGrid
			]
		});

		win.AnalyzerTestRefValuesPanel = new sw.Promed.Panel({
			title: '',
			layout: 'border',
			region: 'center',
			height: 200,
			border: false,
			items: [
				win.AnalyzerTestRefValuesGrid
			]
		});
	
		var form = new Ext.Panel({
			autoScroll: true,
			region: 'center',
			bodyBorder: false,
			border: false,
			id: 'ATEW_FormPanel',
			layout: 'border',
			frame: false,
			labelAlign: 'right',
			items: [
				{
				xtype: 'form',
				frame: true,
				labelAlign: 'right',
				autoHeight: true,
				layout: 'form',
				id: 'AnalyzerTestEditForm',
				border: true,
				region: 'north',
				labelWidth: 130,
				url:'/?c=AnalyzerTest&m=save',
				items: [
					{
						name: 'AnalyzerTest_id',
						xtype: 'hidden'
					},
					{
						name: 'UslugaComplexMedService_id',
						xtype: 'hidden'
					},
					{
						name: 'AnalyzerTest_pid',
						xtype: 'hidden'
					},
					{
						name: 'AnalyzerModel_id',
						xtype: 'hidden'
					},
					{
						name: 'ReagentModel_id',
						xtype: 'hidden'
					},
					{
						name: 'Analyzer_id',
						xtype: 'hidden'
					},
					{
						name: 'postUslugaComplex_id',
						xtype: 'hidden'
					},
					{
						name: 'AnalyzerTest_isTest',
						xtype: 'hidden',
						value: 2
					}, {
						allowBlank: false,
						fieldLabel: lang['kategoriya_uslugi'],
						loadParams: {params: {where: "where UslugaCategory_SysNick in (" + (getRegionNick() == "kz" ? "'classmedus'" : "'gost2011','tfoms','lpu'") + ")"}},
						hiddenName: 'UslugaCategory_id',
						listeners: {
							'select': function (combo, record) {
								win.form.findField('UslugaComplex_id').clearValue();
								win.form.findField('UslugaComplex_id').getStore().removeAll();

								if ( !record ) {
									win.form.findField('UslugaComplex_id').setUslugaCategoryList();
									return false;
								}

								win.form.findField('UslugaComplex_id').setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);

								return true;
							}
						},
						listWidth: 400,
						tabIndex: TABINDEX_ATAEW + 0,
						width: 250,
						xtype: 'swuslugacategorycombo'
					}, {
						fieldLabel: lang['usluga'],
						name: 'UslugaComplex_id',
						tabindex: TABINDEX_ATEW + 0,
						allowBlank:false,
						xtype: 'swuslugacomplexnewcombo',
						to: 'EvnUslugaPar',
						listeners: {
							'change': function(combo, newValue) {
								if (!Ext.isEmpty(newValue) && !Ext.isEmpty(win.Analyzer_id)) {
									win.getLoadMask(lang['opredelenie_mnemoniki_dlya_testa_analizatora']).show();
									Ext.Ajax.request({
										failure: function () {
											win.getLoadMask().hide();
										},
										params:{
											UslugaComplex_id: newValue,
											Analyzer_id: win.Analyzer_id
										},
										success: function (response) {
											win.getLoadMask().hide();
											var result = Ext.util.JSON.decode(response.responseText);
											if (result && !Ext.isEmpty(result.test_sysnick)) {
												win.form.findField('AnalyzerTest_SysNick').setValue(result.test_sysnick);
											}
										},
										url:'/?c=AnalyzerTest&m=getSysNickForAnalyzerTest'
									});
								}
							}
						},
						showUslugaComplexLpuSection: false,
						listWidth: 450,
						width: 400
					}, {
						fieldLabel: lang['mnemonika'],
						tabindex: TABINDEX_ATEW + 1,
						name: 'AnalyzerTest_SysNick',
						xtype: 'textfield',
						width: 400
					}, {
						fieldLabel: lang['tip_testa'],
						width: 400,
						hiddenName: 'AnalyzerTestType_id',
						typeCode: 'int',
						id: 'ATEW_AnalyzerTestType_id',
						tabindex: TABINDEX_ATEW + 2,
						xtype: 'swcommonsprcombo',
						prefix:'lis_',
						allowBlank:false,
						sortField:'AnalyzerTestType_Code',
						comboSubject: 'AnalyzerTestType',
						listeners: {
							'select': function() {
								win.AnalyzerTestType_idChange();
							}
						}
					}, {
						layout: 'column',
						items:[{
							width: 250,
							layout: 'form',
							labelWidth: 130,
							items:[{
								fieldLabel: lang['data_nachala'],
								name: 'AnalyzerTest_begDT',
								tabindex: TABINDEX_ATEW + 3,
								xtype: 'swdatefield'
							}]
						}, {
							width: 300,
							layout: 'form',
							labelWidth: 160,
							hidden: true,
							items:[{
								fieldLabel: lang['svyaz_s_lis'],
								name: 'AnalyzerTest_HasLisLink',
								tabindex: TABINDEX_ATEW + 5,
								disabled: true,
								xtype: 'checkbox'
							}]
						}]
					}, {
						layout: 'column',
						items:[{
							width: 250,
							layout: 'form',
							labelWidth: 130,
							items:[{
								fieldLabel: lang['data_okonchaniya'],
								name: 'AnalyzerTest_endDT',
								tabindex: TABINDEX_ATEW + 4,
								xtype: 'swdatefield'
							}]
						},{     //https://redmine.swan.perm.ru/issues/62598
							width: 300,
							layout: 'form',
							labelWidth: 160,
							items:[{
								fieldLabel: 'Рассчитываемый',
								name: 'AnalyzerTest_Formula',
								tabindex: TABINDEX_ATEW + 4,
								xtype: 'checkbox',
                                                                listeners : {
                                                                    'check' : function(chb, checked){
                                                                        win.AnalyzerTestFormulaGrid.setVisible(this.getValue());
                                                                        win.AnalyzerTestFormulaPanel.doLayout();
																		if (!checked && Ext.getCmp('AnalyzerTestFormulaGrid').getGrid().getStore().getAt(0).get('AnalyzerTest_id') != null) {
																			Ext.Msg.show({
																			   title:'Удаление списка формул',
																			   msg: 'При снятии флажка все формулы будут удалены. Продолжить удаление?',
																			   buttons: Ext.Msg.YESNO,
																			   fn: function(btn) {
																				   if (btn == 'yes') {
																						Ext.Ajax.request({
																							url: '/?c=ufa_AnalyzerTestFormula&m=AnalyzerTestFormulaAll_del',
																							params: {
																								Analyzer_id: win.Analyzer_id,
																								AnalyzerTest_id: win.AnalyzerTest_id,
																								AnalyzerTest_pid: win.AnalyzerTest_pid
																							},
																							callback: function (options, success, response) {
																								if (success === true) {
																									Ext.getCmp('AnalyzerTestEditWindow').loadAnalyzerTestFormulaGrid();
																								} else {
																									//console.log('Отправка данных в регистр ИПРА не успешна');
																								}
																							}
																						});																						   
																				   } else if (btn == 'no') {
																					   win.form.findField('AnalyzerTest_Formula').setValue(1);
																				   }
																			   },
																			   animEl: 'elId',
																			   icon: Ext.MessageBox.QUESTION
																			});																			
																		}
                                                                    }
                                                                }
							}]
						}]
					}, {
						fieldLabel: lang['prioritet'],
						minValue: 0,
						name: 'AnalyzerTest_SortCode',
						width: 100,
						xtype: 'numberfield'
					}, {
						width: 400,
						comboSubject:'RefMaterial',
						allowBlank: true,
						editable: true,
						fieldLabel:lang['biomaterial'],
						hiddenName:'RefMaterial_id',
						xtype:'swcommonsprcombo'
					}, {
						layout: 'form',
						xtype: 'fieldset',
						title: 'Реактив',
						id: 'ReagentNormRatePanel',
						height: 130,
						border: true,
						items: 
						[{
								layout: 'form',
								border: false,
								items: 
								[{
									name: 'ReagentNormRate_id',
									xtype: 'hidden'
								}, {
									fieldLabel: 'Наименование',
									name: 'ReagentNormRate_Name',
									//xtype: 'textfield',
									xtype: 'textarea',
									disabled: true,
									value: 'www',
									width: 600
								}]
						}, {
							layout: 'column',
							height: 90,
							width: 800,
							items: [
								{
									layout: 'form',
									border: false,
									columnWidth: .5,
									items: 
									[{
										fieldLabel: 'Норма расхода',
										minValue: 0,
										name: 'ReagentNormRate_RateValue',
										width: 200,
										xtype: 'numberfield'
									}]
								}, {
									layout: 'form',
									border: false,
									columnWidth: .5,
									items: 
									[{
										fieldLabel: 'Единицы измерения',
										hiddenName: 'ReagentNormRate_unit_id',
										xtype: 'swcommonsprcombo',
										editable: true,
										prefix:'lis_',
										//allowBlank:false,
										sortField:'Unit_Name',
										comboSubject: 'Unit',
										width: 200
									}]
								}
							]

						}]
					}
				]
				}, {
					region: 'center',
					border: false,
					layout: 'border',
					items: [
                                                win.AnalyzerTestFormulaPanel,
						win.CardPanel,
						win.AnalyzerTestRefValuesPanel
					]
				}
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'AnalyzerTest_id'},
				{name: 'AnalyzerTest_pid'},
				{name: 'AnalyzerTest_isTest'},
				{name: 'AnalyzerModel_id'},
				{name: 'ReagentModel_id'},
				{name: 'Analyzer_id'},
				{name: 'UslugaCategory_id'},
				{name: 'UslugaComplex_id'},
				{name: 'AnalyzerTest_SysNick'},
				{name: 'UslugaComplexMedService_id'},
				{name: 'AnalyzerTest_begDT'},
				{name: 'AnalyzerTest_endDT'},
				{name: 'AnalyzerTest_SortCode'},
				{name: 'AnalyzerTestType_id'},
				{name: 'ReagentNormRate_id'},
				{name: 'ReagentNormRate_RateValue'},
				{name: 'ReagentNormRate_unit_id'}
				,{name: 'RefMaterial_id'}
			]),
			url: '/?c=AnalyzerTest&m=save'
		});
		win.AnalyzerTestType_idChange = function (){
			var val = parseInt(win.form.findField('AnalyzerTestType_id').getFieldValue('AnalyzerTestType_Code'));
			switch (val) {
				case 1: // Количественный
				case 3: // Полуколичественный
					win.CardPanel.show();
					win.AnalyzerTestRefValuesPanel.show();
					win.AnalyzerTestRefValuesGrid.setColumnHidden('Unit_Name', false);
					win.AnalyzerTestRefValuesGrid.setColumnHidden('RefValues_CritValue', false);
					win.CardPanel.getLayout().setActiveItem(0);
					break;
				case 2: // Качественный
					win.CardPanel.show();
					win.AnalyzerTestRefValuesPanel.show();
					win.AnalyzerTestRefValuesGrid.setColumnHidden('Unit_Name', true);
					win.AnalyzerTestRefValuesGrid.setColumnHidden('RefValues_CritValue', true);
					win.CardPanel.getLayout().setActiveItem(1);
					break;
				default:
					win.CardPanel.hide();
					win.AnalyzerTestRefValuesPanel.hide();
					break;
			}
			win.doLayout();
		};
		Ext.apply(this, {
			buttons: [{
				handler: function()
				{
					win.form.findField('postUslugaComplex_id').setValue( win.form.findField('UslugaComplex_id').getValue() );
					this.ownerCt.doSave();
				},
				id: 'ATEW_SaveButton',
				iconCls: 'save16',
				tabindex: TABINDEX_ATEW + 20,
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_ATEW + 21),
			{
				handler: function()
				{
					var AnalyzerTest_id = win.form.findField('AnalyzerTest_id').getValue();
					if (('add' == win.action) && (AnalyzerTest_id > 0)) {
						var loadMask = new Ext.LoadMask(win.form.getEl(), {msg:lang['udalenie_testa_modeli_analizatora']});
						loadMask.show();
						Ext.Ajax.request({
							failure:function () {
								sw.swMsg.alert(lang['oshibka_pri_udalenii_testa_modeli_analizatora'], lang['ne_udalos_poluchit_dannyie_s_servera']);
								loadMask.hide();
								win.hide();
							},
							params:{
								AnalyzerTest_id:AnalyzerTest_id
							},
							success:function (response) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (!result || !result.success) {
									sw.swMsg.alert(lang['oshibka_pri_udalenii_testa_modeli_analizatora'], result.Error_Code + ': ' + result.Error_Msg);
								}
								loadMask.hide();
								win.hide();
							},
							url:'/?c=AnalyzerTest&m=delete'
						});
					} else {
						this.ownerCt.hide();
					}
				},
				iconCls: 'cancel16',
				onTabAction: function() {
					win.form.findField('UslugaComplex_id').focus(true);
				},
				tabindex: TABINDEX_ATEW + 22,
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		
		sw.Promed.swAnalyzerTestEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('AnalyzerTestEditForm').getForm();
	}
});