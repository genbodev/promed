/**
 * Панель информированного добровольного согласия
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.SignalInfo.PersonLpuInfoPanel', {
	extend: 'swPanel',
	title: 'ИНФОРМИРОВАННОЕ ДОБРОВОЛЬНОЕ СОГЛАСИЕ',
	btnAddClickEnable: true,
	allTimeExpandable: false,
	collapseOnOnlyTitle: true,
	ReceptElectronic_id: null,
	onBtnAddClick: function () {
		this.showAddMenu();
	},
	margin: 10,
	collapsed: true,
	setParams: function (params) {
		var me = this;

		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.loaded = false;

		if (!me.collapsed) {
			me.load();
		}
	},
	loaded: false,
	listeners: {
		'expand': function () {
			if (!this.loaded) {
				this.load();
			}
		}
	},
	load: function (callPoint) {
		var me = this;
		this.loaded = true;
		var callback;
		//в случае, когда функция вызвана при созаднии(=сохранении) какого-либо согласия, определяем
		// соответствующий колбэк, содержащий функцию печати согласия
		if (callPoint == 'setElectroReceptInfo') {
			callback = this.printElectroReceptInfo;
		} else if (callPoint == 'setPersonLpuInfo'){
			callback = this.printPersonLpuInfo;
		}
		this.PersonLpuInfoGrid.getStore().load({
			callback: callback,
			params: {
				Person_id: me.Person_id
			}
		});
	},
	/**
	 * Печать согласия на обработку персональных данных
	 *
	 */
	printPersonLpuInfo: function (data) {
		//Если функция вызвана как колбэк в load
		if (typeof data == "object") {
			var record = data.find(function fn(rec) {
					return rec.get('PersonLpuInfoType') == 'PersonLpuInfo';
				}
			);
			var PersonLpuInfoStatus = record.get('PersonLpuInfo_IsAgree');
			var PersonLpuInfo_id = record.get('PersonLpuInfo_id');
		} else
		//если функция вызвана по нажатию кнопки "Печать" в гриде
		{
			var index = this.PersonLpuInfoGrid.getStore().findBy(function (rec) {
				return rec.get('PersonLpuInfoType') == 'PersonLpuInfo'
			});
			if (this.PersonLpuInfoGrid.getStore().getAt(index).get('PersonLpuInfo_IsAgree')) {
				var electroReceptStatus = this.PersonLpuInfoGrid.getStore().getAt(index).get('PersonLpuInfo_IsAgree');
			}
			var PersonLpuInfo_id = this.PersonLpuInfoGrid.getStore().getAt(index).get('PersonLpuInfo_id');
		}
		var lan = (getAppearanceOptions().language == 'ru' ? 1 : 2);
		//если отказ от обработки персональных данных...
		if (PersonLpuInfoStatus == 1) {
			var template = 'Otkaz';
			var parLang = '';
		} else {
			var template = 'Soglasie';
			var parLang = '&paramLang=' + lan;
		}
		if (getRegionNick() == 'kz') {
			printBirt({
				'Report_FileName': 'Person' + template + '_PersData.rptdesign',
				'Report_Params': '&paramPersonLpuInfo_id=' + PersonLpuInfo_id + parLang,
				'Report_Format': 'pdf'
			});
		} else {
			printBirt({
				'Report_FileName': 'Person' + template + '_PersData.rptdesign',
				'Report_Params': '&paramPersonLpuInfo_id=' + PersonLpuInfo_id,
				'Report_Format': 'pdf'
			});
		}
	},
	/**
	 * Печать согласия на оформление рецепта в форме электронного документа
	 *
	 */
	printElectroReceptInfo: function (data) {
		//если функция вызвана как колбэк в load
		if (typeof data == "object") {
			var record = data.find(function fn(rec) {
					return rec.get('PersonLpuInfoType') == 'ReceptElectronic';
				}
			);
			var electroReceptStatus = record.get('PersonLpuInfo_IsAgree');
			var PersonLpuInfo_id = record.get('PersonLpuInfo_id');
		} else
			//если функция вызвана по нажатию кнопки "Печать" в гриде
			{
			var index = this.PersonLpuInfoGrid.getStore().findBy(function (rec) {
				return rec.get('PersonLpuInfoType') == 'ReceptElectronic'
			});
			if (this.PersonLpuInfoGrid.getStore().getAt(index).get('PersonLpuInfo_IsAgree')) {
				var electroReceptStatus = this.PersonLpuInfoGrid.getStore().getAt(index).get('PersonLpuInfo_IsAgree');
			}
			var PersonLpuInfo_id = this.PersonLpuInfoGrid.getStore().getAt(index).get('PersonLpuInfo_id');
		}
		//если отказ от выписки рецептов в электронной форме...
		if (electroReceptStatus == 1) {
			printBirt({
				'Report_FileName': 'Withdraw_Consent_Recipe_EDF.rptdesign',
				'Report_Params': '&paramReceptElectronic=' + PersonLpuInfo_id,
				'Report_Format': 'pdf'
			});
		} else {
			printBirt({
				'Report_FileName': 'Consent_Recipe_EDF.rptdesign',
				'Report_Params': '&paramReceptElectronic=' + PersonLpuInfo_id,
				'Report_Format': 'pdf'
			});
		}
	},
	/**
	 * Сохранение согласия на обработку персональных данных
	 *
	 */
	setPersonLpuInfo: function (data) {
		var me = this;
		Ext.Ajax.request({
				url: '/?c=Person&m=savePersonLpuInfo',
				success: function (response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj && response_obj.Error_Msg) {
						sw.swMsg.alert('Ошибка', 'Ошибка при сохранении согласия на обработку перс. данных');
						return false;
					} else if (response_obj && !Ext.isEmpty(response_obj.PersonLpuInfo_id)) {
						data.PersonLpuInfo_id = response_obj.PersonLpuInfo_id;
						me.load('setPersonLpuInfo');
					}
				}.createDelegate(this),
				params: {
					Person_id: data.Person_id,
					PersonLpuInfo_IsAgree: data.IsAgree
				}
			});
	},
	/**
	 * Сохранение согласия на оформление рецепта в форме электронного документа
	 *
	 */
	setElectroReceptInfo: function (data) {
		var me = this;
		Ext.Ajax.request({
			url: '/?c=Person&m=saveElectroReceptInfo',
			success: function (response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj && response_obj.Error_Msg) {
					sw.swMsg.alert('Ошибка', 'Ошибка при сохранении согласия на оформление рецепта в форме электронного документа');
					return false;
				} else if (response_obj && !Ext.isEmpty(response_obj.ReceptElectronic_id)) {
					data.ReceptElectronic_id = response_obj.ReceptElectronic_id;
					me.load('setElectroReceptInfo');
				}
			}.createDelegate(this),
			params: {
				Person_id: data.Person_id,
				ReceptElectronic_id: data.ReceptElectronic_id ? data.ReceptElectronic_id : 0,
				Refuse: data.Refuse ? data.Refuse : 0
			}
		});
	},
	/**
	 * Вывод меню выбора создаваемых согласий
	 *
	 */
	showAddMenu: function (button) {
		var me = this;
		var thisElement;
		button ? thisElement = button : thisElement = me;
		//запрос, в зависимости от результатов которого отключаем не нужный пункт меню для эл. рецептов
		Ext.Ajax.request({
			url: '/?c=Person&m=loadPersonLpuInfoPanel',
			success: function (response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj && response_obj.Error_Msg) {
					sw.swMsg.alert('Ошибка', 'Ошибка при получении данных');
					return false;
				}
				//реализация блокирования пукнтов меню для согласий по электронным рецептам
				var index = response_obj.findIndex(
					function fn(rec) {
						return rec['PersonLpuInfoType'] == 'ReceptElectronic' && rec['PersonLpuInfo_IsAgree'] == 2;
					}
				);
				if (index != -1) {
					me.addMenu.down('[name=electroReceptConsent]').setDisabled(1);
					me.addMenu.down('[name=electroReceptRemove]').setDisabled(0);
					//создаем переменную me.ReceptElectronic_id для обработчика отзыва согласия на эл. рецепт
					me.ReceptElectronic_id = response_obj[index]['PersonLpuInfo_id'];
				} else {
					me.addMenu.down('[name=electroReceptRemove]').setDisabled(1);
					me.addMenu.down('[name=electroReceptConsent]').setDisabled(0);
				}
				me.addMenu.showBy(thisElement, 'tr-br?');
			},
			params: {
				Person_id: me.Person_id
			}
		});
	},

	initComponent: function() {
		var me = this;

		this.PersonLpuInfoGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			viewConfig: {
				minHeight: 33
			},
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Печать',
					handler: function (button) {
						var PersonLpuInfo_id = button.parentMenu.PersonLpuInfo_id;
						me.printPersonLpuInfo();
					}
				}]
			}),
			electroReceptRecordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					name: 'electroReceptConsent',
					text: 'Печать',
					handler: function(button) {
						// причина, почему PersonLpuInfo_id стал ReceptElectronic_id: в гриде отображаются различные
						// добровольные информированные согласия, изначально были только на обработку персональных данных и
						// наименования полей были сделаны под них; по согласиям на электронные рецепты пользуемся тем, что есть.
						var ReceptElectronic_id = button.parentMenu.PersonLpuInfo_id;
						me.printElectroReceptInfo();
					}
				}]
			}),
			showRecordMenu: function(el, PersonLpuInfo_id, PersonLpuInfoType) {
				if (PersonLpuInfoType == 'ReceptElectronic'){
					this.electroReceptRecordMenu.PersonLpuInfo_id = PersonLpuInfo_id;
					this.electroReceptRecordMenu.showBy(el);
				} else {
					this.recordMenu.PersonLpuInfo_id = PersonLpuInfo_id;
					this.recordMenu.showBy(el);
				}
			},
			columns: [{
				width: 120,
				flex: 1,
				header: 'Согласие',
				dataIndex: 'PersonLpuInfo_Type',
				renderer: function (value, metaData, record) {
					if (record.data.PersonLpuInfoType == 'ReceptElectronic'){
						return "На рецепт в форме электронного документа";
					} else {
						return "На обработку персональных данных";
					}
				}
			}, {
				width: 200,
				header: 'Результат',
				dataIndex: 'PersonLpuInfo_IsAgree',
				renderer: function (value, metaData, record) {
					if (record.data.PersonLpuInfoType == 'ReceptElectronic') {
						if (value == 2) {
							return 'Дано';
						} else {
							return 'Отозвано';
						}
					} else {
						if (value == 2) {
							return 'Согласие';
						} else {
							return 'Отказ';
						}
					}
				}
			}, {
				width: 120,
				flex: 1,
				header: 'Дата',
				dataIndex: 'PersonLpuInfo_setDate'
			},
				{
				width: 120,
				header: 'МО',
				dataIndex: 'Lpu_Nick'
			}, {
				width: 40,
				dataIndex: 'PersonLpuInfo_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonLpuInfoGrid.id + "\").showRecordMenu(this, " + record.get('PersonLpuInfo_id') + ", \"" + record.get('PersonLpuInfoType') + "\");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonLpuInfoType', type: 'string' },
					{ name: 'PersonLpuInfo_id', type: 'int' },
					{ name: 'PersonLpuInfo_IsAgree', type: 'int' },
					{ name: 'PersonLpuInfo_setDate', type: 'string' },
					{ name: 'Lpu_Nick', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Person&m=loadPersonLpuInfoPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonLpuInfo_id'
				]
			})
		});

		me.addMenu = Ext6.create('Ext6.menu.Menu', {
			items: [{
				name: 'persInfoConsent',
				text: 'Согласие на обработку персональных данных',
				handler: function() {
					me.setPersonLpuInfo({Person_id: me.Person_id, IsAgree: 2});
				}
			}, {
				name: 'persInfoRefuse',
				text: 'Отказ от обработки персональных данных',
				handler: function() {
					me.setPersonLpuInfo({Person_id: me.Person_id, IsAgree: 1});
				}
			}, {
				name: 'electroReceptConsent',
				text: 'Согласие на рецепт в форме электронного документа',
				handler: function() {
					me.setElectroReceptInfo({Person_id: me.Person_id})
				}
			}, {
				name: 'electroReceptRemove',
				text: 'Отзыв согласия на рецепт в форме электронного документа',
				handler: function() {
					me.setElectroReceptInfo({Person_id: me.Person_id, ReceptElectronic_id: me.ReceptElectronic_id, Refuse: 1})   //"Refuse: 1" означает признак отзыва true
				}
			}]
		});

		Ext6.apply(this, {
			items: [
				this.PersonLpuInfoGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					var button = this;
					me.showAddMenu(button);
				}
			}]
		});
		this.callParent(arguments);
	}
});