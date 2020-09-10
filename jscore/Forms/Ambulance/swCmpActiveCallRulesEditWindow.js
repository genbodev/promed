/**
 * swCmpActiveCallRulesEditWindow - окно редактирования правила контроля вызовова с превышением времени назначения на бригаду
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Ambulance
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Sobenin A.
 * @version      22.02.2018
 */

sw.Promed.swCmpActiveCallRulesEditWindow = Ext.extend(sw.Promed.BaseForm, swop = {
	id: 'swCmpActiveCallRulesEditWindow',
	title: langs('Правило контроля вызовов с превышением времени назначения на бригаду'),
	layout: 'fit',
	width: 640,
	modal: true,
	resizable: false,
	draggable: false,
	autoHeight: true,
	plain: true,
	listeners: {
		hide: function() {
			//this.onWinClose();
			this.returnFunc(this.owner, -1);
		}
	},

	show: function(){
		sw.Promed.swCmpActiveCallRulesEditWindow.superclass.show.apply(this, arguments);
		this.showLoadMask(LOAD_WAIT);
		
		if (!arguments[0] || !arguments[0].LpuBuilding_id) {
			Ext.Msg.alert(langs('Ошибка'), langs('Не передан идентификатор подстанции.'));
			this.hide();
			return;
		}
		
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.returnFunc = arguments[0].callback;
		}

		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}

		this.LpuBuilding_id = arguments[0].LpuBuilding_id;

		this.ActiveCallRule_id = arguments[0].ActiveCallRule_id ? arguments[0].ActiveCallRule_id : null;
		/*this.LpuBuildingTerritoryServiceRel_id = arguments[0].LpuBuildingTerritoryServiceRel_id ? arguments[0].LpuBuildingTerritoryServiceRel_id : null;
		this.TerritoryService_id = arguments[0].TerritoryService_id ? arguments[0].TerritoryService_id : null;*/
		
		var forms = this.formPanel.getForm();

		// @todo Обновлять хранилища комбобоксов, т.к. при повторном открытии окна, они остаются загруженными
		forms.reset();
		forms.findField('LpuBuilding_id').setValue(this.LpuBuilding_id);
		
		var me = this;
			//houses_grid = Ext.getCmp(this.id + '_houses_view_frame');
	
		//houses_grid.ViewGridStore.removeAll();
		
		var loadFormsData = function(callback){
			var params = {
				object: 'ActiveCallRule',
				ActiveCallRule_id: me.ActiveCallRule_id,
				LpuBuilding_id: me.LpuBuilding_id
			};
			
			forms.load({
				url: C_ACTIVECMPCALLRULES_EDIT,
				params: params,
				success: function(form,basicForm){
					// Загрузка данными грида
					/*houses_grid.loadData({
						globalFilters: params,
						noFocusOnLoad: true
					});
					*/
					me.hideLoadMask();
					
					if (typeof callback === 'function') {
						callback();
					}
				},
				failure: function(){
					me.hideLoadMask();
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Повторите попытку, в случае возникновения ошибки, обратитесь к администратору.'));
				}
			});
		}
		
		this.title = this.title.replace(langs(': Добавление'),'');
		this.title = this.title.replace(langs(': Редактирование'),'');
		this.title = this.title.replace(langs(': Просмотр'),'');

		switch(this.action){
			case 'add':
				this.setTitle(this.title + langs(': Добавление'));
				me.hideLoadMask();
			break;
			case 'edit':
				this.setTitle(this.title + langs(': Редактирование'));
				loadFormsData();
			break;
			case 'view':
				this.setTitle(this.title + langs(': Просмотр'));
				loadFormsData();
			break;
		}
		
		me.updateFormElementsActivity();
		// Событие проверки активности чекбокса LpuBuildingStreet_IsAll

	},

	
	/**
	 * Изменяет состояние полей в зависимости от выбранного действия
	 */
	updateFormElementsActivity: function(){
		var disabled = this.action == 'view' ? true : false;
			//houses_grid = Ext.getCmp(this.id + '_houses_view_frame');
		
		this.formPanel.ownerCt.buttons[0].setDisabled(disabled);
		
		//houses_grid.getAction('action_add').setDisabled(true);
		//this.formPanel.ownerCt.buttons.forEach(function(btn){btn.setDisabled(disabled);}); //disable all buttons
		this.formPanel.getForm().items.each(function(itm){
			//if(itm.name == 'LpuBuildingStreet_IsAll' && disabled == false) return;
			itm.setDisabled(disabled)}
		); //disable all fields
		
	},
	
	/**
	 * Список hiddenName связаных комбобоксов в иерархическом порядке
	 */
//	relatedKLCombos: ['KLCountry_id','KLRegion_id','KLSubRegion_id', 'KLCity_id', 'KLTown_id', 'KLStreet_id'],

	
	/**
	 * Валидация
	 */
	doValidateValues: function() {
		var form = this.formPanel.getForm(),
			ActiveCallRule_From = form.findField('ActiveCallRule_From').getValue(),
			ActiveCallRule_To = form.findField('ActiveCallRule_To').getValue(),
			ActiveCallRule_UrgencyFrom = form.findField('ActiveCallRule_UrgencyFrom').getValue(),
			ActiveCallRule_UrgencyTo = form.findField('ActiveCallRule_UrgencyTo').getValue(),
			success = true;

		if(ActiveCallRule_From && ActiveCallRule_To && (ActiveCallRule_To <= ActiveCallRule_From)){
			Ext.Msg.alert(ERR_INVFIELDS_TIT, langs('Начальная граница возраста не может быть больше конечной'), function(){});
			success = false;
		}


		if(ActiveCallRule_UrgencyFrom && ActiveCallRule_UrgencyTo && (ActiveCallRule_UrgencyTo < ActiveCallRule_UrgencyFrom)){
			Ext.Msg.alert(ERR_INVFIELDS_TIT, langs('Начальная граница срочности не может быть больше конечной'), function(){});
			success = false;
		}

		if(!form.isValid()){
			Ext.Msg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG, function(){});
			success = false;
		}

		return success;
	},
	
	submit: function(){
		if (!this.doValidateValues()) {
			return false;
		}
		var form = this.formPanel.getForm(),
			me = this,
			params = form.getValues();
		
		//проверка на обязательные поля
		
		this.showLoadMask(LOAD_WAIT_SAVE);
		params.ActiveCallRule_id = form.findField('ActiveCallRule_id').getValue()||null;
		Ext.Ajax.request({
			params: params,
			url: C_ACTIVECMPCALLRULES_SAVE,
			callback: function(opt, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					me.hideLoadMask();
					if ( response_obj.success == false ) {
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при сохранении правила');
					}
					else {
						Ext.getCmp('activeCallRulePanelGrid').store.reload();
						Ext.Msg.alert(INF_MSG, INF_SAVED_DATA, function (btn, text) {
							if (btn == 'ok') {
								me.hide();
							}
						});
					}
				}
				else {
					me.hideLoadMask();
					sw.swMsg.alert('Ошибка', 'Ошибка при сохранении правила, попробуйте позже');
				}
			}.createDelegate(this)
			
		});
	},
	
	initComponent: function() {
		var me = this;

		this.formPanel = new Ext.form.FormPanel({
			frame: true,
			autoHeight: true,
			labelAlign: 'left',
			id: 'lpubuildingstreet_edit_window',
			labelWidth: 150,
			buttonAlign: 'left',
			bodyStyle:'padding: 5px 30px 5px 5px',
			items: [
				{
					xtype: 'hidden',
					name: 'ActiveCallRule_id'
				},
				{
					xtype: 'hidden',
					name: 'LpuBuilding_id'
				},
				{
					xtype: 'numberfield',
					name: 'ActiveCallRule_From',
					maxValue: 150,
					labelWidth: 150,
					minValue: 0,
					autoCreate: {tag: "input", size:3, maxLength: "3", autocomplete: "off" /*, maxLength: "90"*/},
					fieldLabel: langs('Возраст с'),
					anchor: '100%',
					validator: function(a){	return (a.match(/^[0-9]\d*$/))?true:false;}
				},
				{
					xtype: 'numberfield',
					name: 'ActiveCallRule_To',
					maxValue: 150,
					minValue: 1,
					autoCreate: {tag: "input", size:3, maxLength: "3", autocomplete: "off" /*, maxLength: "90"*/},
					fieldLabel: langs('Возраст по'),
					anchor: '100%',
					validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}
				},
				{
					xtype: 'numberfield',
					name: 'ActiveCallRule_UrgencyFrom',
					maxValue: 99999,
					minValue: 0,
					autoCreate: {tag: "input", size:6, maxLength: "5", autocomplete: "off" /*, maxLength: "90"*/},
					fieldLabel: langs('Срочность с'),
					anchor: '100%',
					validator: function(a){	return (a.match(/^[0-9]\d*$/))?true:false;}
				},
				{
					xtype: 'numberfield',
					name: 'ActiveCallRule_UrgencyTo',
					maxValue: 99999,
					minValue: 0,
					autoCreate: {tag: "input", size:6, maxLength: "5", autocomplete: "off" /*, maxLength: "90"*/},
					fieldLabel: langs('Срочность по'),
					anchor: '100%',
					validator: function(a){	return (a.match(/^[0-9]\d*$/))?true:false;}
				},
				{
					xtype: 'numberfield',
					name: 'ActiveCallRule_WaitTime',
					maxValue: 999999,
					minValue: 0,
					autoCreate: {tag: "input", size:7, maxLength: "6", autocomplete: "off" /*, maxLength: "90"*/},
					fieldLabel: langs('Время ожидания, мин.'),
					allowBlank: false,
					anchor: '100%',
					validator: function(a){	return (a.match(/^[0-9]\d*$/))?true:false;}
				}
			],
			reader: new Ext.data.JsonReader({}, [
				{name: 'ActiveCallRule_id'},
				{name: 'LpuBuilding_id'},
				{name: 'ActiveCallRule_From'},
				{name: 'ActiveCallRule_To'},
				{name: 'ActiveCallRule_UrgencyFrom'},
				{name: 'ActiveCallRule_UrgencyTo'},
				{name: 'ActiveCallRule_WaitTime'}
			]),
			url: C_LPUBUILDINGTERRITORYSERVICE_SAVE,
			enableKeyEvents: true,
			keys: [{
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('swCmpActiveCallRulesEditWindow').hide();
				},
				key: [ Ext.EventObject.J ],
				stopEvent: true
			}, {
				alt: true,
				fn: function(inp, e) {
					Ext.getCmp('swCmpActiveCallRulesEditWindow').buttons[0].handler();
				},
				key: [ Ext.EventObject.C ],
				stopEvent: true
			}]
		});

    	Ext.apply(this, {
			buttons: [
				{
					text: BTN_FRMSAVE,
//					tabIndex: 1214,
//					id: 'lrsOk',
					iconCls: 'ok16',
					handler: function(){
						me.submit();
					}
				},
				{
					text:'-'
				},
				HelpButton(this),
				{
					text: BTN_FRMCANCEL,
//					tabIndex: 1215,
					iconCls: 'cancel16',
					handler: function(){
						me.hide();
						me.returnFunc(this.ownerCt.owner, -1);
					},
					onTabAction: function(){
//						this.findById('KLAreaStat_Combo').focus();
					},
					onShiftTabAction: function(){
//						Ext.getCmp('lrsOk').focus();
					}
				}
			],
 			items: [
				this.formPanel
			]
		});
		sw.Promed.swCmpActiveCallRulesEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
