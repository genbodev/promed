/**
 * Справочник вакцин
 * 
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @version      06.05.2011
 */
/*NO PARSE JSON*/
sw.Promed.amm_SprVaccineForm = Ext.extend(sw.Promed.BaseForm, {
	title: 'Справочник вакцин',
	maximized: true,
	maximizable: true,
	shim: false,
	buttonAlign: "right",
	layout: 'border',
	codeRefresh: true,
	objectName: 'amm_SprVaccineForm',
	objectSrc: '/jscore/Forms/Vaccine/amm_SprVaccineForm.js',
	id: 'amm_SprVaccineForm',
	// buttons		 :

	region: 'north',
	buttons: [{
		text : 'Сбросить фильтр',  //BTN_FRMRESET,
		style: 'color:red!important',
		id: 'VacCabinet', 
		handler : function(button, event) {
			Ext.getCmp('swFilterGridPlugin').setHeader('passive');
			Ext.getCmp('ammSprVaccine').ViewGridModel.grid.store.baseParams.Filter = '{}';
			//Ext.getCmp('amm_WorkPlaceVacCabinetWindow').doSearch();
			Ext.getCmp('ammSprVaccine').ViewGridModel.grid.store.reload();
			if (Ext.getCmp('ammSprVaccine').FilterSettings != undefined) {
				var obj = Ext.getCmp('ammSprVaccine').FilterSettings;
				for (var col in obj) {
					Ext.getCmp('ammSprVaccine').FilterSettings[col] = false;
				}
			}
		}
	},
	'-',
	{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		// disabled	 : true,
		handler: function(button, event)
		{
			ShowHelp(this.ownerCt.title);
		}
	},
	{
		text: BTN_GRIDPRINT,
		tooltip: BTN_GRIDPRINT,
		iconCls: 'print16',
		handler: function()
		{
			Ext.getCmp('ammSprVaccine').printRecords();
		}
	},
	{
		text: BTN_FRMCLOSE,
		tabIndex: -1,
		tooltip: 'Закрыть структуру',
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
		}
	}],
	vacAccess: function()
	{
		var result = false;
		//if (sw.Promed.vac.utils.vacSprAccesFull(getGlobalOptions().pmuser_id) || isAdmin)
		if ((getGlobalOptions().vacSprAccesFull == 1) || isAdmin)
			result = true;
		return result;
	},
	getRangeTip: function(tip) {
		var result;
		if (tip == 1) {
			result = 'месяцев'
		} else {
			result = 'лет'
		}
		;
		return result;
	},
	getDozaName: function(tip) {
		if (tip = 1) {
			return	'мл'
		}
		else if (tip = 2) {
			return	'капель'
		}
		else if (tip = 3) {
			return	'мг'
		}
		;
	},
	Text2Array: function(mText) {
		var result;// =	 new Array();
		if (!mText) {
			return result
		} else {
			result = new Array(new Array(), new Array());
			var pos = 0;
			var i = 0;
			var str = mText;
			var str2;
			while (str.length > 0) {
				pos = str.indexOf('&');
				if (pos <= 0) {
					str2 = str;
					str = '';
				} else {
					str2 = str.substr(0, pos);
					str = str.substr(pos + 1, str.length);
				}
				pos = str2.indexOf('-');

				if (pos <= 0) {
					str = ''
				} else {
					result[i][1] = str2.substr(0, pos);
					result[i][2] = str2.substr(pos + 1, str2.length);
					i++;
				}
			}
			return result;
		}
	},
	initComponent: function() {
		frms = this;
		frms.SprVaccineEditWindow = getWnd('amm_SprVaccineEditWindow');
		var params = new Object();
		Ext.apply(this, {
			items: [
				this.ViewFrame = new sw.Promed.ViewFrame({
					id: 'ammSprVaccine',
					object: 'ammSprVaccine',
					dataUrl: '/?c=Vaccine_List&m=getVaccineGridDetail',
					region: 'center',
					toolbar: true,
					autoLoadData: false,
					cls: 'txtwrap',
					stringfields: [
						{name: 'Vaccine_id', type: 'int', header: 'ID', key: true},
						{name: 'AgeRange', type: 'string', header: 'Название вакцины', width: 50, hidden: true},
						{name: 'Vaccine_FullName', type: 'string', header: 'Название вакцины', id: 'autoexpand', width: 100},
						{name: 'Vaccine_NameInfection', type: 'string', header: 'Прививка', width: 200},
						{name: 'Vaccine_AgeRange2Sim', type: 'string', header: 'Возрастной диапазон', width: 120},
						{name: 'Vaccine_WayPlace', type: 'string', header: 'Способ и место введения', width: 350},
						{name: 'SignDoza', type: 'string', header: 'Признак (зависимость) дозирования от возраста пациента', width: 50, hidden: true},
						{name: 'Doza', type: 'string', header: 'Доза препарата', width: 50, hidden: true},
						{name: 'Vaccine_dose', type: 'string', header: 'Дозировка вакцины', width: 180},
						{name: 'Comment', type: 'string', header: 'Comment', hidden: true}
					],
					listeners: {
						'success': function(source, params) {
							/* source - string - источник события (например форма)
							 * params - object - объект со свойствами в завис-ти от источника
							 */
							sw.Promed.vac.utils.consoleLog('success | ' + source);
							switch (source) {
								case 'ammSprVaccine':
								case 'amm_SprVaccineEditWindow':
									Ext.getCmp('ammSprVaccine').ViewGridPanel.getStore().reload();
									break;
							}
						}
					},
					actions: [
						{name: 'action_add',
							handler: function() {
								var record = {
								};
								sw.Promed.vac.utils.callVacWindow({
									record: record,
									type1: 'btnForm',
									type2: 'btnSprVaccineEditForm'
								}, this.findById('ammSprVaccine'));
							}.createDelegate(this)
						},
						{name: 'action_edit', // hidden: true,
							handler: function() {
								var $Vaccine_id = this.findById('ammSprVaccine').getGrid().getSelectionModel().getSelected().data.Vaccine_id;
								//if (getRegionNick() != 'ufa' || ($Vaccine_id != 26 && $Vaccine_id != 127)) {
									var record = {
										'Vaccine_id': this.findById('ammSprVaccine').getGrid().getSelectionModel().getSelected().data.Vaccine_id
									};
									sw.Promed.vac.utils.callVacWindow({
										record: record,
										type1: 'btnForm',
										type2: 'btnSprVaccineEditForm'
									}, this.findById('ammSprVaccine'));
								//}
							}.createDelegate(this)
						},
						{name: 'action_view', hidden: true},
						{//Удаление вакцины
							name: 'action_delete',
							handler: function()
							{
								var record = this.findById('ammSprVaccine').getGrid().getSelectionModel().getSelected();
								if (record.get('Vaccine_id') == 26 || record.get('Vaccine_id') == 27) {
									Ext.Msg.alert('Внимание', 'Данную вакцину удалить нельзя.<br/>');
									return false;
								}
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if (buttonId === 'yes') {
											//var record = this.findById('ammSprVaccine').getGrid().getSelectionModel().getSelected();
											var params = {
												'parent_id': 'ammSprVaccine',
												'vaccine_id': record.get('Vaccine_id')
											};

											Ext.Ajax.request({
												url: '/?c=VaccineCtrl&m=deleteSprVaccine',
												method: 'POST',
												params: params,
												success: function(response, opts) {
													sw.Promed.vac.utils.consoleLog(response);
													if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
														Ext.getCmp(params.parent_id).fireEvent('success', 'ammSprVaccine',
																{}
														);
													}
												}
											});
										}
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: 'Удалить вакцину?',
									title: 'Удаление вакцины'
								});
                                           
							}.createDelegate(this)
						}
					],
					updateContextMenu: function() {
						var grid = Ext.getCmp('ammSprVaccine');
								//this.ViewFrame;
						var rowSelected = grid.getGrid().getSelectionModel().getSelected();
						var deleteIsHidden = grid.ViewActions['action_delete'].initialConfig.initialDisabled;

						if (rowSelected.data.Vaccine_id == '26' || rowSelected.data.Vaccine_id == '27' ) {
							grid.getAction('action_delete').setDisabled(true);
						} else {
							grid.setActionDisabled('action_delete', deleteIsHidden);
						}
					}
				})
			]
		});
	
		//Интеграция фильтра к Grid
		getGlobalRegistryData = {};

		columnsFilter = ['Vaccine_FullName', 'Vaccine_NameInfection'];
		configParams = {url: '/?c=Vaccine_ListFilterGrid&m=getVaccineGridDetailFilter'};

		_addFilterToGrid(Ext.getCmp('ammSprVaccine'), columnsFilter, configParams);

		sw.Promed.amm_SprVaccineForm.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.amm_SprVaccineForm.superclass.show.apply(this, arguments);

		var flag = !isSuperAdmin();

		this.ViewFrame.setActionDisabled('action_add', flag);
		this.ViewFrame.setActionDisabled('action_edit', flag);
		this.ViewFrame.setActionDisabled('action_delete', flag);

		Ext.getCmp('ammSprVaccine').getGrid().getStore().load({
			callback: function() { 
				Ext.getCmp('ammSprVaccine').updateContextMenu();
				Ext.getCmp('ammSprVaccine').getGrid().on(
						'cellclick',
						Ext.getCmp('ammSprVaccine').updateContextMenu
				);
				Ext.getCmp('ammSprVaccine').getGrid().on(
					'cellcontextmenu',
					Ext.getCmp('ammSprVaccine').updateContextMenu
				);
			}
		});
	}
});
