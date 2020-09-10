/**
 * PacketPanelTreatmentStandards - Панель в режиме отображения стандартов лечения 
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 */
Ext6.define('common.EMK.PacketPrescr.PacketPanelTreatmentStandards', {
	alias: 'widget.PacketPanelTreatmentStandards',
	extend: 'Ext6.panel.Panel',
	border: false,
	defaults: {
		border: false
	},
	floatable: false,
	layout: 'border',
	hidden: true,
	requires: [
		'common.EMK.PacketPrescr.SelectionWindowMNN',
	],
	standartArr: [],
	curentWin: false,
	reset: function(){
		var win = this;
		win.StandardDisplayArea.CureStandart_id = null;
		win.treatmentStandardsBreadcrumbs.query('button')[1].setText('');
		win.StandardsTreatmentPatientInfoPanel.clearParams();
		win.standartArr = [];
	},
	setParams: function(params){
		var win = this;

		win.MedPersonal_id = params.MedPersonal_id || null;
		win.EvnPrescrPanelCntr = params.EvnPrescrPanelCntr || null;
		win.PersonInfoPanel = params.PersonInfoPanel || null;
		win.Diag_id = params.Diag_id || null;
		win.PersonEvn_id = params.PersonEvn_id || null;
		win.Server_id = params.Server_id || null;
		win.Person_id = params.Person_id || null;
		win.Evn_id = params.Evn_id || null;
		win.Evn_setDate = params.Evn_setDate || null;
		win.standartArr = [];
		win.curentWin = params.curentWin || false;
	},
	loadStandarts: function(cb){
		var win = this;
		
		if(!win.Evn_id) return false;
		var cb = (cb && typeof cb == 'function') ? cb : false;

		var params ={};
		params.Evn_id = win.Evn_id;
		win.StandardsСolumnGrid.getStore().load({
			params: params,
			callback: function(store){
				var win = this;
				if(store.length>0){
					store.forEach(function(elem){
						win.standartArr.push({
							Diag_id: elem.get('Diag_id'),
							CureStandart_id: elem.get('CureStandart_id')
						});
					});
				}
				win.loadTreeStore(function(){
					if(cb) cb();
				});
			}.createDelegate(win)
		});
	},
	openStandardDisplayArea: function(data){
		//Область отображения стандарта
		var win = this;
		var data = data || false;
		if(data && data.CureStandart_id){	
			win.StandardDisplayArea.loadAllGrids(data);
			win.treatmentStandardsBreadcrumbs.query('button')[1].setText(data.name);
			win.loadActive(2);
		}else{
			win.loadActive(1);
			win.treatmentStandardsBreadcrumbs.query('button')[1].setText('');
		}

	},
	loadTreeStore: function(cb){
		var cb = (cb && typeof cb == 'function') ? cb : false;
		var win = this;
		if(!win.loadMask){
			win.loadMask = new Ext6.LoadMask({msg: 'Подождите...', target: win});
		}
		win.TreeStore.getProxy().setExtraParam('node', 'root');
		win.TreeStore.getProxy().setExtraParam('query', '');
		win.TreeStore.getProxy().setExtraParam('age', 0);
		win.TreeStore.getProxy().setExtraParam('phase', 0);
		win.TreeStore.getProxy().setExtraParam('stage', 0);
		win.TreeStore.getProxy().setExtraParam('complication', 0);
		win.TreeStore.getProxy().setExtraParam('conditions', []);
		var standart = JSON.stringify(win.standartArr);
		win.TreeStore.getProxy().setExtraParam('standart', standart);
		// win.TreeStore.getProxy().setExtraParam('standart', []);

		win.loadMask.show();
		win.TreeStore.load({
			callback: function(){
				this.TreePanel.getRootNode().expand();
				this.loadMask.hide();
				if(cb) cb();
			}.createDelegate(this)
		});
	},
	doSave: function(){
		var win = this;
		var packetTreatmentStandards = win.StandardDisplayArea.getPacketTreatmentStandards();
		var params = {
			CureStandart_id: win.StandardDisplayArea.CureStandart_id,
			PersonEvn_id: win.PersonEvn_id,
			Server_id: win.Server_id,
			Evn_pid: win.Evn_id,
			save_data: Ext.util.JSON.encode(packetTreatmentStandards)
		}
		if(!win.saveMask){
			win.saveMask = new Ext6.LoadMask({
				msg: 'Сохранение...',
				target: win
			});
		}
		win.saveMask.show();
		Ext.Ajax.request({
			url: '/?c=EvnPrescr&m=saveTreatmentStandardsForm',
			callback: function(opt, success, response) {
				this.saveMask.hide();
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success)
					{
						if(this.curentWin) this.curentWin.hide();
					}
					else if ( response_obj.Error_Msg )
					{
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
					}
					else
					{
						sw.swMsg.alert(lang['oshibka'], lang['nepravilnyiy_otvet_servera']);
					}
				}
			}.createDelegate(this),
			failure: function(response, opts){
				this.StandardDisplayArea.saveMask.hide();
				Ext.Msg.alert('Ошибка','Во время сохранения произошла ошибка, обратитесь к администратору');
			}.createDelegate(this),
			params: params
		});
	},
	loadActive: function(act){
		var win = this;
		var act = act || 1;
		if(act==1){
			win.AllStandartsPanel.setVisible(true);
			win.StandardDisplayArea.setVisible(false);
			win.StandardsСolumnGrid.hide();
		}else{
			win.AllStandartsPanel.setVisible(false);
			win.StandardDisplayArea.setVisible(true);
			win.StandardsСolumnGrid.show();
		}
	},
	initComponent: function() {
		var win = this;

		// хлебные крошки
		win.treatmentStandardsBreadcrumbs = new Ext6.Panel({
			// html: '<div style="display: flex; align-items: center; padding-left: 10px; height: 100%"><span style="font-weight: bold; color: #2196f3;">Все стандарты</span> / <span id="treatmentStandardsBreadcrumbs_name"></span><div>',
			region: 'north',
			hidden: false,
			height: 40,
			margin: '0 0 5 0',
			defaults: {border: true},
			layout: {
				type: 'hbox',
				align: 'stretch'
			},
			items: [
				{
					xtype: 'button',
					cls: 'button-primary-white',
					text: 'Все стандарты / ',
					handler: function () {
						win.openStandardDisplayArea(false);
						win.TreePanel.getStore().each(function(rec){
							if(rec.get('children')){
								rec.expand(); 
							}
						});
					}
				},
				{
					xtype: 'button',
					text: '',
					handler: function (btn) {
						//...
					}
				}
			]
		});

		this.StandardsTreatmentPatientInfoPanel = Ext6.create('Ext6.panel.Panel', {
			//информационная панель
			border: false,
			flex: 1,
			cls: 'patient-apply-panel',
			params: {
				fio: '',
				diag: '',
				count: 0
			},
			setParams: function(params){
				var data = Ext6.Object.merge(this.params,params);
				this.applyData(data);
			},
			clearParams: function(){
				this.applyData({fio: '', diag: '', count: 0});
			},
			tpl: new Ext6.Template([
				'Для пациента <span>{fio}</span> (<i>Диагноз: {diag}</i>) выбрано назначений: {count}'
			])
		});

		this.StandardsTreatmentApplyPanel = Ext6.create('Ext6.panel.Panel', {
			// блок отмена применить
			region: 'south',
			style: 'background-color: #2196f3',
			cls: 'packet-select-footer',
			padding:'7 0 7 6',
			height: 60,
			margin: 0,
			border: false,
			layout: {
				type: 'hbox',
				pack: 'end',
				align: 'stretch'
			},
			items: [
				this.StandardsTreatmentPatientInfoPanel,
				{
					xtype: 'button',
					cls: 'button-secondary-blue',
					text: 'Отмена',
					handler: function () {
						win.loadActive();
						win.loadStandarts();
					},
					margin: '0 0 20 0'
				}, {
					xtype: 'button',
					text: 'Применить',
					cls: 'button-primary-white',
					margin: '0 33 20 9',
					handler: function (btn) {
						win.doSave();
					}
				}]
		});

		this.baner = Ext6.create('Ext6.panel.Panel', {
			region: 'north',
			border: false,
			height: 76,
			bodyStyle: {
				backgroundColor: '#2196f3',
				padding: '0px'
			},
			html: '<a href="http://cr.rosminzdrav.ru" target="_blank"><div><img src="/img/banerStandardTreatment.png" width="260px" height="76px"></div></a>'
		});

		this.StandardsСolumnGrid = Ext6.create('Ext6.grid.Panel', {
			//левый грид. Колонка со списком найденных стандартов
			region: 'center',
			viewConfig:{
				cls: 'StandardsСolumnGrid',
				selectedItemCls : "StandardsСolumnGridSelected",
				getRowClass: function (record, rowIndex) {/*...*/}
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {/*...*/}
				}
			},
            listeners: {
				celldblclick: function(){/*...*/},
				itemdblclick: function(model, record, index){
					//...
				},
				itemclick: function (cmp, record, item, index, e, eOpts ) {
					var id = record.get('CureStandart_id');
					if(id){
						win.openStandardDisplayArea({
							CureStandart_id: id,
							name: record.get('CureStandart_Name')
						});
					}
				}
			},
			store: {
				fields: [
					{name: 'CureStandart_id', mapping: 'CureStandart_id'},
					{name: 'Row_Num', type: 'string', mapping: 'Row_Num'},
					{name: 'html', type: 'string', mapping: 'html'},
					{name: 'Diag_id', type: 'string', mapping: 'Diag_id'},
					{name: 'Diag_Code', type: 'string', mapping: 'Diag_Code'},
					{name: 'Diag_Name', type: 'string', mapping: 'Diag_Name'},
					{name: 'CureStandart_Name', type: 'string', mapping: 'CureStandart_Name'}
                ],
				autoLoad: false,
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=CureStandart&m=loadCureStandartList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					{
						property: 'Row_Num',
						direction: 'DESC'
					}
				],
				listeners: {}
			},
            columns: [
				{
					dataIndex: 'CureStandart_id',
					hidden: true
				}, 
				{
					dataIndex: 'Row_Num',
					hidden: true
				},
				{
					dataIndex: 'html',
					tdCls: 'StandardsСolumnGridTD',
					flex: 1,
					// renderer: function(value, meta) { ... }
				}
            ]
        });

        this.leftPacketPanel = Ext6.create('Ext6.panel.Panel', {
			//левая панель
			width: 260,
			layout: 'border',
			region: 'west',
			cls: 'packet-select-left-panel',
			border: false,
			split: true,
			collapseMode: 'mini',
			collapsible: true,
			header: false,
			items: [
				win.baner,
				win.StandardsСolumnGrid
			]
		});

		this.TreeStore = Ext6.create('Ext.data.TreeStore', {
			// все стандарты
			bodyStyle: 'padding: 30px;',
			border: false,
			idProperty: 'sid',
			fields: [
				{name: 'sid', type: 'int'},
				{name: 'name', type: 'string'},
				{name: 'code', type: 'string'},
				{name: 'leaf', type: 'boolean'}
			],
			root:{
				leaf: false,
				expanded: false
			},
			isRootLoad: false,
			autoLoad: false,
			proxy: {
				limitParam: undefined,
				startParam: undefined,
				paramName: undefined,
				pageParam: undefined,
				type: 'ajax',
				url: '/?c=CureStandart&m=loadTreeFederalStandards',
				reader: {
					type: 'json'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				},
				extraParams: {
					node: 'root',
					age: 0,
					query: '',
					phase: 0,
					stage: 0,
					complication: 0,
					conditions: [],
					standart: []
				}
			},
			listeners: {
				nodebeforeexpand: function(el, eOpts){
					// ...
				},
				renderer: function (value, record) {
					//...
				},
				beforeload: function(store, operation, eOpts){
					//debugger;
					if(operation.node.get('id') != 'root' && !operation.node.get('expanded')) return false;
				},
				'load': function ( store, records, successful, operation, node, eOpts ) {
					//...
				}
			}
		});

		this.titleLabel = Ext6.create('Ext6.form.Label', {
			region: 'north',
			xtype: 'label',
			style: 'font-size: 16px; padding: 15px 10px; margin: 10px 0; display: block;',
			html: '<b>Федеральные стандарты</b>'
		});

		this.TreePanel = Ext6.create('Ext.tree.Panel', {
			cls: 'cs6',
			border: false,
			store: win.TreeStore,
			rootVisible: false,
			reserveScrollbar: true,
			useArrows: true,
			singleExpand: true,
			iconCls: '',
			viewConfig: { 
		        stripeRows: false, 
		        getRowClass: function(record) { 
		            return 'TreePanelStandardTreatment'; 
		        } 
		    } ,
		    // title: 'Федеральные стандарты',
			listeners: {
				render: function() {
		            //this.getRootNode().expand();
		        },
		        renderer: function (value, record) {
					var ss=1;
				},
				'beforecellmousedown': function (tree, td, cellIndex, record, tr, rowIndex, e, eOpts) {
					win.TreeStore.getProxy().setExtraParam('node', record.get('id'));
				},
				'select': function (tree, record, index, eOpts) {
					var s=1;
				},
				'deselect': function () {
					var s=1;
				},
				'celldblclick': function (tree, td, cellIndex, record, tr, rowIndex, e, eOpts) {
					if(record.data.parentId != 'root') {
						var id = record.get('sid');
						// win.PacketPanelTreatmentStandards.loadActive(2);
						if(id) {
							win.openStandardDisplayArea({
								CureStandart_id: id,
								name: record.get('name')
							});
							var rec = win.StandardsСolumnGrid.getStore().findRecord('CureStandart_id', id);
							if(rec) win.StandardsСolumnGrid.getSelectionModel().select(rec);
						}
					}
				},
			},
			columns: [{
				xtype: 'treecolumn',
				dataIndex: 'name',
				flex: 1,
				renderer: function (val, meta, rec) {
					if (!rec.get('level_id') && (!rec.get('children') || rec.get('children').length < 1)) {
						meta.tdStyle = 'color: gray; font-style: italic;';
					}
					return val;
				}
			}, {
				dataIndex: 'code',
				width: 500
			}]
		});

		//----- диагностика
		this.StandardDisplayArea_diagnosticsGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			style: 'margin: 0 5px 0 5px; border: 1px solid #1976d2;',
			// viewConfig:{
			// 	getRowClass: function (record, rowIndex) {
			// 		return (record.get('applied')) ? 'disabled-grid-row' : '';
			// 	},
			// },
			// title: 'Диагностика',
			header:{
				style: 'border: 0px; backgroundColor: transparent;',
				title : {
					text: 'Диагностика',
					style: 'color: black; font-weight: bold;'
				},
			},
			columns: [
				{xtype: 'checkcolumn', text: '', dataIndex: 'flagAuto', 
					listeners: {
						'checkchange': function(el, rowIndex, checked, record, e, eOpts){
							record.set('flagAuto', checked);
							this.StandardDisplayArea.countChecked();
						}.createDelegate(win)
					},
				},
				{dataIndex: 'UslugaComplex_Code', text: 'Код', width: 150},
				{dataIndex: 'UslugaComplex_Name', text: 'Наименование', flex: 1},
				{dataIndex: 'FreqDelivery', text: 'Частота', format: '0,0000'},
				{dataIndex: 'AverageNumber', text: 'Среднее количество', format: '0,00'},
				{xtype: 'checkcolumn', text: 'Наличие', dataIndex: 'Availability', disabled: true }
			],
			store: {
				groupField: 'UslugaComplexAttributeType_Code',
				fields: [
					{name: 'UslugaComplex_id', type: 'int'},
					{name: 'UslugaComplex_Code',type: 'string'},
					{name: 'UslugaComplex_Name',type: 'string'},
					{name: 'AverageNumber', type: 'float'},
					{name: 'FreqDelivery', type: 'float'},
					{name: 'UslugaComplexAttributeType_Code', type: 'string'},
					{name: 'Availability'},
					{name: 'flagAuto'},
					//{name: 'applied'}
				],
				autoLoad: false,
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=CureStandart&m=loadStandardDiagnosticsGrid',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
			},
			features: [
				Ext6.create('Ext6.grid.feature.Grouping', {
					groupHeaderTpl: new Ext6.XTemplate(
						'{name:this.formatName}',
						{
							formatName: function(code) {
								if(code==8) {
									return langs('Лабораторная диагностика');
								} else if(code==9) {
									return langs('Инструментальная диагностика');
								} else if(code==13) {
									return langs('Консультационная услуга');
								} else if(code==16) {
									return langs('Манипуляции и процедуры');
								} else {
									return langs('Прочее');
								}
							}
						}
					)
				})
			]
		});

		this.StandardDisplayArea_treatment = Ext6.create('Ext6.grid.Panel', {
			border: true,
			// title: 'Лечение',
			style: 'margin: 0 5px 0 5px; border: 1px solid #1976d2;',
			header:{
				style: 'border: 0px; backgroundColor: transparent;',
				title : {
					text: 'Лечение',
					style: 'color: black; font-weight: bold;'
				},
			},
			columns: [
				{xtype: 'checkcolumn', text: '', dataIndex: 'flagAuto',
					listeners: {
						'checkchange': function(el, rowIndex, checked, record, e, eOpts){
							record.set('flagAuto', checked);
							this.StandardDisplayArea.countChecked();
						}.createDelegate(win)
					},
				},
				{dataIndex: 'UslugaComplex_Code', text: 'Код', width: 150},
				{dataIndex: 'UslugaComplex_Name', text: 'Наименование', flex: 1},
				{dataIndex: 'FreqDelivery', text: 'Частота', format: '0,0000'},
				{dataIndex: 'AverageNumber', text: 'Среднее количество', format: '0,00'},
				{dataIndex: 'Availability', xtype: 'checkcolumn', text: 'Наличие', disabled: true},
			],
			store: {
				groupField: 'UslugaComplexAttributeType_Code',
				fields: [
					{name: 'UslugaComplex_id', type: 'int'},
					{name: 'UslugaComplex_Code',type: 'string'},
					{name: 'UslugaComplex_Name',type: 'string'},
					{name: 'AverageNumber', type: 'float'},
					{name: 'FreqDelivery', type: 'float'},
					{name: 'UslugaComplexAttributeType_Code', type: 'string'},
					{name: 'Availability'},
					{name: 'flagAuto'}
				],
				autoLoad: false,
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=CureStandart&m=loadStandardTreatmentsGrid',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
			},
			features: [
				Ext6.create('Ext6.grid.feature.Grouping', {
					groupHeaderTpl: new Ext6.XTemplate(
						'{name:this.formatName}',
						{
							formatName: function(code) {
								if(code==8) {
									return langs('Лабораторная диагностика');
								} else if(code==9) {
									return langs('Инструментальная диагностика');
								} else if(code==13) {
									return langs('Консультационная услуга');
								} else if(code==16) {
									return langs('Манипуляции и процедуры');
								} else {
									return langs('Прочее');
								}
							}
						}
					)
				})
			]
		});

		this.SelectionWindowMNN = Ext6.create('common.EMK.PacketPrescr.SelectionWindowMNN', {
			width: 650,
			height: 400,
			modal: true
		});

		this.StandardDisplayArea_DrugTreatment = Ext6.create('Ext6.grid.Panel', {
			border: true,
			// title: 'Медикаменты',
			style: 'margin: 0 5px 0 5px; border: 1px solid #1976d2;',
			header:{
				style: 'border: 0px; backgroundColor: transparent;',
				title : {
					text: 'Медикаменты',
					style: 'color: black; font-weight: bold;'
				},
			},		
			columns: [
				{xtype: 'checkcolumn', text: '', dataIndex: 'flagAuto',
					listeners: {
						'checkchange': function(el, rowIndex, checked, record, e, eOpts){
							this.StandardDisplayArea.countChecked();
							if(!checked) {
								if(record.get('PrescribedDrug')) record.set('PrescribedDrug', '');
								return false;
							}
							
							var Actmatters_id = record.get('ActMatters_id');
							var DrugComplexMnn_id = null;
							if(record.get('localObjSaveForm_EvnPrescrTreatCreateWindow')){
								var saveObjData = JSON.parse(record.get('localObjSaveForm_EvnPrescrTreatCreateWindow'));
								DrugComplexMnn_id = saveObjData.DrugComplexMnn_id;
							}
							var params = {
								Actmatters_id: Actmatters_id,
								DrugComplexMnn_id: DrugComplexMnn_id,
								cbFn: function(data){
									if(data && data.DrugComplexMnn_id){
										var params = {
											rec: {Drug_id: null, DrugComplexMnn_id: data.DrugComplexMnn_id},
											parentPanel: this.win.EvnPrescrPanelCntr.getView(),
											parentCntr: this.win.EvnPrescrPanelCntr,
											userMedStaffFact: sw.Promed.MedStaffFactByUser.current,
											Person_id: this.win.Person_id,
											PersonEvn_id: this.win.PersonEvn_id,
											Server_id: this.win.Server_id,
											Evn_id: this.win.Evn_id,
											Evn_setDate: this.win.Evn_setDate,
											LpuSection_id: sw.Promed.MedStaffFactByUser.current.LpuSection_id,
											MedPersonal_id: this.win.MedPersonal_id,
											showFirstItem: true,
											Diag_id: this.win.Diag_id,
											ofForms: 'PacketPanelTreatmentStandards',
											disabledRecept: true,
											callback: function (data) {
												if(data){
													var strObjData = JSON.stringify(data);
													this.rec.set('localObjSaveForm_EvnPrescrTreatCreateWindow', strObjData);
													this.rec.set('PrescribedDrug', data.DrugComplexMnn_Name);
													this.rec.set('flagAuto', true);
												}else{
													this.rec.set('flagAuto', false);
												}
											}.bind({win: this.win, rec: this.rec})
										}
										if(this.rec.get('localObjSaveForm_EvnPrescrTreatCreateWindow')){
											var saveObjData = JSON.parse(this.rec.get('localObjSaveForm_EvnPrescrTreatCreateWindow'));
											if(saveObjData.DrugComplexMnn_id && data.DrugComplexMnn_id == saveObjData.DrugComplexMnn_id){
												params.localRecordDataForm = saveObjData;
											}
										}
										getWnd('swEvnPrescrTreatCreateWindow').show(params);
									}else{
										this.rec.set('flagAuto', false);
									}
								}.bind({win: this, rec: record})
							}
							this.SelectionWindowMNN.show(params);
						}.createDelegate(win)
					},
				},
				// {dataIndex: 'Availability', xtype: 'checkcolumn'},
				{dataIndex: 'ATXDroup', text: 'АТХ-группа', width: 250},				
				{dataIndex: 'DrugComplexMnnName_Name', text: 'МНН / Действующее вещество', flex: 1},
				{dataIndex: 'CureStandartTreatmentDrug_FreqDelivery', text: 'Частота', format: '0,0000'},
				{dataIndex: 'CureStandartTreatmentDrug_ODD', text: 'ОДД'},
				{dataIndex: 'CureStandartTreatmentDrug_EKD', text: 'ЭКД'},
				{dataIndex: 'PrescribedDrug', text: 'Назначенное лекарственное средство', width: 350}
			],
			store: {
				groupField: 'ATXDroupName',
				fields: [
					{name: 'CureStandartTreatmentDrug_id', type: 'int'},
					{name: 'ATXDroup',type: 'string'},
					{name: 'ATXDroupName',type: 'string'},
					{name: 'DrugComplexMnnName_Name',type: 'string'},
					{name: 'CureStandartTreatmentDrug_FreqDelivery', type: 'float'},
					{name: 'CureStandartTreatmentDrug_ODD', type: 'int'},
					{name: 'CureStandartTreatmentDrug_EKD', type: 'int'},					
					{name: 'PrescribedDrug', type: 'string'},
					{name: 'localObjSaveForm_EvnPrescrTreatCreateWindow', type: 'string'},
					{name: 'Availability'}
				],
				autoLoad: false,
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=CureStandart&m=loadStandardTreatmentDrugGrid',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
			},
			features: [
				Ext6.create('Ext6.grid.feature.Grouping', {
					groupHeaderTpl: new Ext6.XTemplate(
						'{name:this.formatName}',
						{
							formatName: function(name) {
								if(name){
									return name.replace(/^(\D\d{0,2}\s)/, '')
								}else{
									return langs('Прочее');
								}
							}
						}
					)
				})
			]
		});		

		win.StandardDisplayArea = new Ext6.Panel({
			autoScroll: true,
			reserveScrollbar: true,
			CureStandart_id: null,
			loadAllGrids: function(data){
				var loadGrids = 0;
				var paramsDiag = {};
				paramsDiag.Evn_id = win.Evn_id;
				paramsDiag.CureStandart_id = data.CureStandart_id;
				if(win.StandardDisplayArea.CureStandart_id != data.CureStandart_id){
					win.StandardDisplayArea_diagnosticsGrid.getStore().removeAll();
					win.StandardDisplayArea_treatment.getStore().removeAll();
					win.StandardDisplayArea_DrugTreatment.getStore().removeAll();
					win.treatmentStandardsBreadcrumbs.query('button')[1].setText('');

					if(!win.StandardDisplayArea.loadMask) {
						win.StandardDisplayArea.loadMask = new Ext6.LoadMask({
							msg: 'Загрузка...',
							target: win.StandardDisplayArea
						});
					}
					win.StandardDisplayArea.loadMask.show();
					var loadAll = function(){ 
						if(loadGrids < 1) {
							this.countChecked(); 
							this.loadMask.hide();
						}
					}.bind(this);
					loadGrids++;
					win.StandardDisplayArea_diagnosticsGrid.getStore().load({
						params: paramsDiag,
						callback: function(){ loadGrids--; loadAll();}.createDelegate(win)
					});
					loadGrids++;
					win.StandardDisplayArea_treatment.getStore().load({
						params: paramsDiag,
						callback: function(){ loadGrids--; loadAll();}.createDelegate(win)
					});
					loadGrids++;
					win.StandardDisplayArea_DrugTreatment.getStore().load({
						params: paramsDiag,
						callback: function(){ loadGrids--; loadAll();}.createDelegate(win)
					});

					win.StandardDisplayArea.CureStandart_id = data.CureStandart_id;
				}
			},
			countChecked: function(){
				var count = 0;
				win.StandardDisplayArea_diagnosticsGrid.getStore().each(function(rec){
					if(rec.get('flagAuto')) count++;
				});
				win.StandardDisplayArea_treatment.getStore().each(function(rec){
					if(rec.get('flagAuto')) count++;
				});
				win.StandardDisplayArea_DrugTreatment.getStore().each(function(rec){
					if(rec.get('flagAuto')) count++;
				});
				win.StandardsTreatmentPatientInfoPanel.setParams({count: count});
			},
			getPacketTreatmentStandards: function(){
				var diag = [];
				var treatment = [];
				var drugTreatment = [];
				var codes  = {'8': 'labdiag', '9': 'funcdiag', '13': 'consusluga', '16': 'proc'};
				var params = {
					labdiag: [], //лаборатторная диагностика 8
					funcdiag: [], //инструментальная дианостика 9
					consusluga: [], //Консультационная услуга 13
					proc: [], //манипуляции и процедуры 16
					drug: [] //лекартсвенное лечение
				}

				win.StandardDisplayArea_diagnosticsGrid.getStore().each(function(rec){
					var code = rec.get('UslugaComplexAttributeType_Code');
					if (rec.get('flagAuto') && codes[code] && params[codes[code]]) params[codes[code]].push(rec.get('UslugaComplex_id'));
				});
				win.StandardDisplayArea_treatment.getStore().each(function(rec){
					var code = rec.get('UslugaComplexAttributeType_Code');
					if (rec.get('flagAuto') && codes[code] && params[codes[code]]) params[codes[code]].push(rec.get('UslugaComplex_id'));
				});
				win.StandardDisplayArea_DrugTreatment.getStore().each(function(rec){
					if(rec.get('flagAuto') && rec.get('localObjSaveForm_EvnPrescrTreatCreateWindow')) {
						var obj = JSON.parse(rec.get('localObjSaveForm_EvnPrescrTreatCreateWindow'));
						obj.CureStandartTreatmentDrug_id = rec.get('CureStandartTreatmentDrug_id');
						params.drug.push(obj);
					}
				});
				return params;
			},
			items: [
				{
					xtype: 'panel',
					items: [
						this.StandardDisplayArea_diagnosticsGrid
					]
				},
				{
					xtype: 'panel',
					style: {marginTop: '30px'},
					items: [
						this.StandardDisplayArea_treatment
					]
				},
				{
					xtype: 'panel',
					style: {marginTop: '30px'},
					items: [
						this.StandardDisplayArea_DrugTreatment
					]
				},
				this.StandardsTreatmentApplyPanel
			]
		});

		win.AllStandartsPanel = new Ext6.Panel({
			items: [
				this.titleLabel,
				this.TreePanel
			]
		})

		win.treatmentStandardsCenter = new Ext6.Panel({
			region: 'center',
			autoScroll: true,
			items: [
				win.AllStandartsPanel,
				win.StandardDisplayArea
			]
		});

		Ext6.apply(win, {
			items: [
				win.treatmentStandardsBreadcrumbs, // хлебные крошки
				win.leftPacketPanel,	// левая панель
				win.treatmentStandardsCenter // центр
			]
		});

		this.callParent(arguments);
	}
});