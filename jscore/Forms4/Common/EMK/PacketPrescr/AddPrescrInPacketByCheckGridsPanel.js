Ext6.define('common.EMK.PacketPrescr.AddPrescrInPacketByCheckGridsPanel', {
	extend: 'swPanel',
	requires: [
		'Ext6.layout.container.VBox',
		'Ext6.data.*',
		'Ext6.grid.*',
		'Ext6.tree.*',
		'Ext6.grid.column.Check',
		'common.EMK.PacketPrescr.controllers.AddPrescrInPacketByCheckGridsCntr',
		'common.EMK.models.AddPrescrByCheckGridsModel'
	],
	refId: 'AddPrescrInPacketByCheckGridsPanel',
	alias: 'widget.AddPrescrInPacketByCheckGridsPanel',
	controller: 'AddPrescrInPacketByCheckGridsCntr',

	cls: 'addEvnPrescribePanel',
	parentPanel: '',
	layout: 'fit',
	//autoHeight: true,

	//scrollable: true,
	cbFn: Ext6.emptyFn,
	setCount: Ext6.emptyFn,
	listeners: {
		resize: function(){
			this.updateLayout();
		}
	},
	reCountSelect: function(){
		var me = this,
			arrDrugSelCount = this.DrugDataGrid.getView().getSelectionModel().getCount(),
			arrLabDiagSelCount = this.LabDiagDataGrid.getView().getSelectionModel().getCount(),
			arrFuncDiagSelCount = this.FuncDiagDataGrid.getView().getSelectionModel().getCount();

	},
	getSaveArr: function(savePacket) {
		var me = this,
			arrDrugSelected = me.DrugDataGrid.getSelectionModel().getSelection(),
			arrLabDiagSelected = me.LabDiagDataGrid.getSelectionModel().getSelection(),
			arrFuncDiagSelected = me.FuncDiagDataGrid.getSelectionModel().getSelection(),
			arrConsUslSelected = me.ConsUslDataGrid.getSelectionModel().getSelection(),
			arrProcSelected = me.ProcDataGrid.getSelectionModel().getSelection(),
			arrRegimeSelected = me.RegimeDataGrid.getSelectionModel().getSelection(),
			arrDietSelected = me.DietDataGrid.getSelectionModel().getSelection(),
			arrPrescr = new Object(),
			arrConsUsl = [],
			arrProc = [],
			arrRegime = [],
			arrDiet = [],
			arrFuncDiag = [],
			str, labdiag, arrDrug;

		if(savePacket){
			//Удаление невыбранных услуг из пакета - собираем невыбранные
			labdiag = [];
			arrDrug = new Object;
			var arrDrugAll = this.DrugDataGrid.getStore().getRange(),
				arrLabDiagAll = this.LabDiagDataGrid.getStore().getRange(),
				arrFuncDiagAll = this.FuncDiagDataGrid.getStore().getRange(),
				arrConsUslAll = this.ConsUslDataGrid.getStore().getRange(),
				arrProcDataAll = this.ProcDataGrid.getStore().getRange(),
				arrRegimeAll = this.RegimeDataGrid.getStore().getRange(),
				arrDietAll = this.DietDataGrid.getStore().getRange(),
				arrDrugUnSelected = Ext6.Array.difference(arrDrugAll, arrDrugSelected),
				arrLabDiagUnSelected = Ext6.Array.difference(arrLabDiagAll, arrLabDiagSelected),
				arrFuncDiagUnSelected = Ext6.Array.difference(arrFuncDiagAll, arrFuncDiagSelected),
				arrConsUslUnSelected = Ext6.Array.difference(arrConsUslAll, arrConsUslSelected),
				arrProcDataUnSelected = Ext6.Array.difference(arrProcDataAll, arrProcSelected),
				arrRegimeUnSelected = Ext6.Array.difference(arrRegimeAll, arrRegimeSelected),
				arrDietUnSelected = Ext6.Array.difference(arrDietAll, arrDietSelected);
			// Собрали те, которые не выбраны
			arrDrugUnSelected.forEach(function (el) {
				var DrugListData = el.get('DrugListData');
				var treat_id = '';
				var ids = [];
				DrugListData.forEach(function(e){
					ids.push(e.PacketPrescrTreatDrug_id);
					//treat_id = 'PacketPrescrTreat_'+e.PacketPrescrTreat_id.toString();
					treat_id = e.PacketPrescrTreat_id.toString();
				});
				if(treat_id)
					arrDrug[treat_id] = ids;
			});
			arrLabDiagUnSelected.forEach(function (el) {
				labdiag.push(el.get('PacketPrescrUsluga_id'));
			});
			arrFuncDiagUnSelected.forEach(function (el) {
				arrFuncDiag.push(el.get('PacketPrescrUsluga_id'));
			});
			arrConsUslUnSelected.forEach(function (el) {
				arrConsUsl.push(el.get('PacketPrescrUsluga_id'));
			});
			arrProcDataUnSelected.forEach(function (el) {
				arrProc.push(el.get('PacketPrescrUsluga_id'));
			});
			arrRegimeUnSelected.forEach(function (el) {
				arrRegime.push(el.get('PacketPrescrRegime_id'));
			});
			arrDietUnSelected.forEach(function (el) {
				arrDiet.push(el.get('PacketPrescrDiet_id'));
			});
		}
		else {
			//Применение выбранных - собираем выбранные
			labdiag = new Object();
			arrDrug = [];
			arrDrugSelected.forEach(function (el) {
				var DrugListData = el.get('DrugListData');
				var arrCourseDrug = [];
				DrugListData.forEach(function(e){
					var ids = new Object();
					ids.PacketPrescrTreatDrug_id = e.PacketPrescrTreatDrug_id;
					if(e.DrugComplexMnn_id)
						ids.DrugComplexMnn_id = e.DrugComplexMnn_id;
					if(e.Drug_id)
						ids.Drug_id = e.Drug_id;
					if(e.ActMatters_id)
						ids.ActMatters_id = e.ActMatters_id;
					arrCourseDrug.push(ids);
				});
				arrDrug.push(arrCourseDrug);
			});
			arrLabDiagSelected.forEach(function (el) {
				str = el.get('UslugaComplex_id').toString();
				if(str)
					labdiag[str] = {
						'MedService_id': el.get('MedService_id'),
						'Lpu_id': el.get('Lpu_id'),
						'UslugaComplex_id': [el.get('UslugaComplex_id')]
					};
			});
			arrFuncDiagSelected.forEach(function (el) {
				arrFuncDiag.push({
					'MedService_id': el.get('MedService_id'),
					'Lpu_id': el.get('Lpu_id'),
					'UslugaComplex_id': el.get('UslugaComplex_id')
				});
			});
			arrConsUslSelected.forEach(function (el) {
				arrConsUsl.push({
					'MedService_id': el.get('MedService_id'),
					'Lpu_id': el.get('Lpu_id'),
					'UslugaComplex_id': el.get('UslugaComplex_id')
				});
			});
			arrProcSelected.forEach(function (el) {
				arrProc.push(el.get('UslugaComplex_id'));
			});
			arrRegimeSelected.forEach(function (el) {
				arrRegime.push({
					PrescriptionRegimeType_id: el.get('PrescriptionRegimeType_id'),
					PacketPrescrRegime_Duration: el.get('PacketPrescrRegime_Duration')
				});
			});
			arrDietSelected.forEach(function (el) {
				arrDiet.push({
					PrescriptionDietType_id: el.get('PrescriptionDietType_id'),
					PacketPrescrDiet_Duration: el.get('PacketPrescrDiet_Duration')
				});
			});
		}
		//arrPrescr.oper = [];
		//arrPrescr.proc = [];
		arrPrescr.funcdiag = arrFuncDiag;
		arrPrescr.drug = arrDrug;
		arrPrescr.labdiag = labdiag;
		arrPrescr.proc = arrProc;
		arrPrescr.consusl = arrConsUsl;
		arrPrescr.regime = arrRegime;
		arrPrescr.diet = arrDiet;

		return arrPrescr;
	},
	doSave: function(mode,cbFn,checkDrug){
		// apply - режим применения выделенных назначений из пакета
		// savePacket - режим сохранения выделенных назначений в пакете (редактирование пакета)
		// applyAllPacket - режим применения пакета целиком

		var me = this,
			save_url = '/?c=PacketPrescr&m=savePacketPrescrForm',
			data = me.getController().data,
			selWind = me.parentPanel,
			callback = cbFn || Ext6.emptyFn,
			params;
		if(!selWind || Ext6.isEmpty(selWind.selectPacketPrescr_id)){
			Ext6.Msg.alert('Ошибка', 'Необходимо выбрать пакет');
			callback();
			return false;
		}
		if(Ext6.isEmpty(data))
			data = selWind.getData();
		if(Ext6.isEmpty(data)){
			Ext6.Msg.alert('Ошибка', 'Ошибка получения данных');
			callback();
			return false;
		}
		params = {
			PacketPrescr_id: selWind.selectPacketPrescr_id,
			PersonEvn_id: data.PersonEvn_id,
			Person_id: data.Person_id,
			Server_id: data.Server_id,
			Evn_pid: data.Evn_id,
			Evn_id: data.Evn_id,
			parentEvnClass_SysNick: 'EvnVizitPL',
			LpuSection_id: getGlobalOptions().CurLpuSection_id,
			mode: mode
		};

		// Получение массива id-шников элементов на удаление из пакета, если savePacket = true
		// либо массива применяемых назначений из пакета
		if(mode == 'apply' || mode == 'savePacket'){
			var arrPrescr = me.getSaveArr((mode == 'savePacket'));
			var order_uslugalist_str = Ext6.JSON.encode(arrPrescr).toString();
			params.save_data = order_uslugalist_str;
		}

		if(checkDrug){//yl:5588 проверка пакета перед применением
			params.checkDrug="check";
			me.mask('Проверка лекарственных назначений из пакета');
		}else{
			me.mask('Сохранение назначений');
		};

		Ext6.Ajax.request({
			url: save_url,
			callback: function(opt, success, response) {
				if(checkDrug){//была проверка лекарств
					if (success && response && response.responseText && (resp = Ext.util.JSON.decode(response.responseText))) {
						checkPacketPrescrTreat(me,mode,cbFn,resp)
					} else {
						sw.swMsg.alert(langs("Ошибка"), "При проверке лекарственных назначений из пакета возникли ошибки");
					}
				}else{//обычное применения пакета
					callback();
					if(mode == 'savePacket'){
						selWind.setMode('addPrescrByPacket');
					}
					else{
						me.unmask();
						selWind.close();
						me.cbFn();
					}
				};

				//selWind.callback();
				//selWind.getController().loadGrids();
			},
			params: params
		});
	},
	initComponent: function() {
		var me = this,
			cntr = me.getController();

		/*this.DrugDataGrid = Ext6.create('Ext6.grid.Panel', {
			prescribeGridType: 'EvnPrescribeAdd',
			collapsible: true,
			xtype: 'grid',
			viewModel: true,
			buttonAlign: 'center',
			hideHeaders: true,
			cls: 'addPrescribeGrid',
			objectPrescribe: 'DrugData',
			frame: false,
			border: false,
			default: {
				border: 0
			},
			viewConfig: {
				loadMask: false
			},
			header: {
				titlePosition: 1
			},
			title: 'ЛЕКАРСТВЕННЫЕ НАЗНАЧЕНИЯ',
			selModel: Ext6.create('Ext6.selection.CheckboxModel',{
				headerWidth: 61,
				checkOnly: true,
				listeners: {
					select: 'setEditMode',
					deselect: 'setEditModeSave'
				}
			}),
			columns: [
				{
					dataIndex: 'DrugListData',
					flex: 1,
					align: 'left',
					renderer: function(value) {
						var resStr = ''; // Для назначения idшника всей строки в целом EXTJSом
						if(value){
							var manyDrug = (Object.keys(value).length > 1);
							if (manyDrug)
								resStr += '<div style="display:table;"><div class="many-drugs-packet-icon" style="display: table-cell"></div>';
							value.forEach(function(e){
								if(manyDrug)
									resStr += '<div class="onePrescr" ><span class="manyEvnPrescr" >'+e.Drug_Name+'</span></div>';
								else
									resStr += '<div style="display: flex"><div class="one-drugs-packet-icon"></div><div class="onePrescr" style="line-height: 25px">' + e.Drug_Name + '</div></div>';
							});
							if (manyDrug)
								resStr += '</div>';
						}
						return resStr;
					}
				},
				{
					text: '',
					flex: 1
					}
				],
			store: {
				model: 'common.EMK.models.PacketPrescrDrug',
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketForPrescrList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			}
		});*/


		this.DrugDataGrid = Ext6.create('swGridCheckPrescrDrug', {
			onPlusClick: function (grid, rec, add, btn) {
				var cbFn = function(){
					grid.getStore().reload();
				};
				me.parentPanel.EvnPrescrPanelCntr.openQuickSelectWindow(grid,cbFn,btn,me.parentPanel.selectPacketPrescr_id);
			}
		});

		/*this.LabDiagDataGrid = Ext6.create('Ext6.grid.Panel', {
			prescribeGridType: 'EvnPrescribeAdd',
			collapsible: true,
			viewConfig: {
				loadMask: false
			},
			columnLines: false,
			xtype: 'grid',
			viewModel: true,
			buttonAlign: 'center',
			hideHeaders: true,
			userCls: 'width-grid-normal',
			cls: 'addPrescribeGrid',
			objectPrescribe: 'LabDiagData',
			frame: false,
			border: false,
			defaults: {
				border: 0
			},
			header: {
				titlePosition: 1
			},
			title: 'ЛАБОРАТОРНАЯ ДИАГНОСТИКА',
			bind: {
				selection: '{theRow}'
			},
			selModel: Ext6.create('Ext6.selection.CheckboxModel',{
				headerWidth: 61,
				checkOnly: true,
				listeners: {
					select: 'setEditMode',
					deselect: 'setEditModeSave'
				}
			}),
			columns: [{
				text: '',
				dataIndex: 'UslugaComplex_Name',
				renderer: function(val, metaData, record) {
					var s = '';
					s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">'+record.get('UslugaComplex_id') + ' ' + record.get('UslugaComplex_Name')+'</span></div></div>';
					return s;
				},
				flex: 1
			},{
				text: '',
				dataIndex: 'MedService_Nick',
				renderer: function(val, metaData, record) {
					var s = '';
					s += record.get('Lpu_Nick') + ' ' + record.get('MedService_Nick');
					return s;
				},
				flex: 1
			}],
			store: {
				model: 'common.EMK.models.CureStandLabDiag',
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketForPrescrList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			}
		});*/

		this.LabDiagDataGrid = Ext6.create('swGridCheckPrescrLabDiag', {
			onPlusClick: function (grid, rec, add, btn) {
				var cbFn = function(){
					grid.getStore().reload();
				};
				me.parentPanel.EvnPrescrPanelCntr.openQuickSelectWindow(grid,cbFn,btn,me.parentPanel.selectPacketPrescr_id);
			}
		});

		/*this.FuncDiagDataGrid = Ext6.create('Ext6.grid.Panel', {
			prescribeGridType: 'EvnPrescribeAdd',
			collapsible: true,
			xtype: 'grid',
			userCls: 'width-grid-normal',
			viewModel: true,
			buttonAlign: 'center',
			hideHeaders: true,
			cls: 'addPrescribeGrid',
			objectPrescribe: 'FuncDiagData',
			frame: false,
			border: false,
			listeners: {

			},
			default: {
				border: 0
			},
			header: {
				titlePosition: 1
			},
			title: 'ИНСТРУМЕНТАЛЬНАЯ ДИАГНОСТИКА',
			bind: {
				selection: '{theRow}'
			},
			selModel: Ext6.create('Ext6.selection.CheckboxModel',{
				headerWidth: 61,
				checkOnly: true,
				listeners: {
					select: 'setEditMode',
					deselect: 'setEditModeSave'
				}
			}),
			viewConfig: {
				loadMask: false
			},
			columns: [{
				text: '',
				dataIndex: 'UslugaComplex_Name',
				renderer: function(val, metaData, record) {
					var s = '';
					s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">'+record.get('UslugaComplex_id') + ' ' + record.get('UslugaComplex_Name')+'</span></div></div>';
					return s;
				},
				flex: 1
			},{
				text: '',
				dataIndex: 'MedService_Nick',
				renderer: function(val, metaData, record) {
					var s = '';
					s += record.get('Lpu_Nick') + ' ' + record.get('MedService_Nick');
					return s;
				},
				flex: 1
			}],
			store: {
				model: 'common.EMK.models.CureStandFuncDiag',
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketForPrescrList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			}

		});*/

		this.FuncDiagDataGrid = Ext6.create('swGridCheckPrescrFuncDiag', {
			onPlusClick: function (grid, rec, add, btn) {
				var cbFn = function(){
					grid.getStore().reload();
				};
				me.parentPanel.EvnPrescrPanelCntr.openQuickSelectWindow(grid,cbFn,btn,me.parentPanel.selectPacketPrescr_id);
			}
		});

		/*this.ConsUslDataGrid = Ext6.create('Ext6.grid.Panel', {
			viewConfig: {
				loadMask: false
			},
			prescribeGridType: 'EvnPrescribeAdd',
			collapsible: true,
			userCls: 'width-grid-normal',
			xtype: 'grid',
			viewModel: true,
			buttonAlign: 'center',
			hideHeaders: true,
			cls: 'addPrescribeGrid',
			objectPrescribe: 'ConsUslData',
			frame: false,
			border: false,
			listeners: {

			},
			default: {
				border: 0
			},
			header: {
				titlePosition: 1
			},
			title: 'КОНСУЛЬТАЦИОННАЯ УСЛУГА',
			bind: {
				selection: '{theRow}'
			},
			selModel: Ext6.create('Ext6.selection.CheckboxModel',{
				headerWidth: 61,
				checkOnly: true,
				listeners: {
					select: 'setEditMode',
					deselect: 'setEditModeSave'
				}
			}),
			columns: [{
				text: '',
				dataIndex: 'UslugaComplex_Name',
				renderer: function(val, metaData, record) {
					var s = '';
					s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">'+record.get('UslugaComplex_id') + ' ' + record.get('UslugaComplex_Name')+'</span></div></div>';
					return s;
				},
				flex: 1
			},{
				text: '',
				dataIndex: 'MedService_Nick',
				renderer: function(val, metaData, record) {
					var s = '';
					s += record.get('Lpu_Nick') + ' ' + record.get('MedService_Nick');
					return s;
				},
				flex: 1
			}],
			store: {
				model: 'common.EMK.models.CureStandConsUsl',
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketForPrescrList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			}

		});*/

		this.ConsUslDataGrid = Ext6.create('swGridCheckPrescrConsUsl', {
			onPlusClick: function (grid, rec, add, btn) {
				var cbFn = function(){
					grid.getStore().reload();
				};
				me.parentPanel.EvnPrescrPanelCntr.openQuickSelectWindow(grid,cbFn,btn,me.parentPanel.selectPacketPrescr_id);
			}
		});


		/*this.ProcDataGrid = Ext6.create('Ext6.grid.Panel', {
			viewConfig: {
				loadMask: false
			},
			prescribeGridType: 'EvnPrescribeAdd',
			collapsible: true,
			userCls: 'width-grid-normal',
			xtype: 'grid',
			viewModel: true,
			buttonAlign: 'center',
			hideHeaders: true,
			cls: 'addPrescribeGrid',
			//cls: 'evnPrescribeGrid',
			objectPrescribe: 'ProcData',
			frame: false,
			border: false,
			listeners: {

			},
			default: {
				border: 0
			},
			header: {
				titlePosition: 1
			},
			title: 'МАНИПУЛЯЦИИ И ПРОЦЕДУРЫ',
			bind: {
				selection: '{theRow}'
			},
			selModel: Ext6.create('Ext6.selection.CheckboxModel',{
				headerWidth: 61,
				checkOnly: true,
				listeners: {
					select: 'setEditMode',
					deselect: 'setEditModeSave'
				}
			}),
			columns: [{
				text: '',
				dataIndex: 'UslugaComplex_Name',
				renderer: function(val, metaData, record) {
					var s = '';
					s += '<div style="display:flex;"><div class="'+this.objectPrescribe+'-packet-icon"></div><div style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><span style="line-height: 25px">'+record.get('UslugaComplex_id') + ' ' + record.get('UslugaComplex_Name')+'</span></div></div>';
					return s;
				},
				flex: 1
			},{
				text: '',
				dataIndex: 'MedService_Nick',
				renderer: function(val, metaData, record) {
					var s = '';
					s += record.get('Lpu_Nick') + ' ' + record.get('MedService_Nick');
					return s;
				},
				flex: 1
			}],
			store: {
				model: 'common.EMK.models.CureStandProc',
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadPacketForPrescrList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			}

		});*/

		this.ProcDataGrid = Ext6.create('swGridCheckPrescrProc', {
			onPlusClick: function (grid, rec, add, btn) {
				var cbFn = function(){
					grid.getStore().reload();
				};
				me.parentPanel.EvnPrescrPanelCntr.openQuickSelectWindow(grid,cbFn,btn,me.parentPanel.selectPacketPrescr_id);
			}
		});

		this.RegimeDataGrid = Ext6.create('swGridCheckPrescrRegime', {
			onPlusClick: function (grid, rec, add, btn) {
				var cbFn = function(){
					grid.getStore().reload();
				};
				me.parentPanel.EvnPrescrPanelCntr.openQuickSelectWindow(grid,cbFn,btn,me.parentPanel.selectPacketPrescr_id);
			}
		});

		this.DietDataGrid = Ext6.create('swGridCheckPrescrDiet', {
			onPlusClick: function (grid, rec, add, btn) {
				var cbFn = function(){
					grid.getStore().reload();
				};
				me.parentPanel.EvnPrescrPanelCntr.openQuickSelectWindow(grid,cbFn,btn,me.parentPanel.selectPacketPrescr_id);
			}
		});

		Ext6.apply(me, {
			/*buttons: [{
				text: 'ПРИМЕНИТЬ',
				handler: function () {
					me.doSave('apply');
				},
				cls: 'button-primary'
			},{
				text: 'СОХРАНИТЬ',
				reference: 'SavePacketBtn',
				tooltip: 'В пакет будут сохранены только отмеченные назначения.',
				cls: 'button-secondary',
				handler: function () {
					sw.swMsg.show({
						buttons: sw.swMsg.OKCANCEL,
						buttonText:{
							ok: 'Удалить',
							cancel: 'Отмена'
						},
						cls: 'alert-window-message',
						msg: '<span class="msg-alert-text">В пакет будут сохранены только отмеченные назначения.</span>',
						icon: 'warning-image',
						fn: function(btn) {
							if (btn === 'ok') {
								me.doSave('savePacket');
							}
						}
					});
					Ext6.Msg.show({
						closable: true,
						width: 458,
						height: 262,
						title: {
							text: '<div class="msg-show-panel-icon-alert"></div><span>Сохранение пакета</span>',
							style: {color: '#333'}

						},
						cls: 'msg-show-panel msg-show-panel-full save-packet-alert',
						msg: '<span class="msg-alert-text">В пакет будут сохранены только отмеченные назначения.</span>',
						buttons: Ext6.Msg.OKCANCEL,
						buttonText: {
							ok: 'Сохранить',
							cancel: 'Отмена'
						},
						fn: function (btn) {
							if (btn == 'ok') {
								me.doSave('savePacket');
							}
						}
					})
				},
				disabled: true
			},{
				text: 'СОХРАНИТЬ КАК',
				reference: 'SaveAsPacketBtn',
				cls: 'button-secondary',
				handler: function () {
					inDevelopmentAlert();
				},
				disabled: true
			},{
				text: 'ОТМЕНА',
				cls: 'button-secondary',
				handler: 'onCancel'
			},'->'],*/
			items: [{
				layout: 'fit',
				itemId: 'swAddPrescrGridsPanel',
				items: [{
					autoHeight: true,
					scrollable: true,
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					/*defaults: {
						border: false
					},*/
					items:[
						me.LabDiagDataGrid,
						me.FuncDiagDataGrid,
						me.ConsUslDataGrid,
						me.ProcDataGrid,
						me.RegimeDataGrid,
						me.DietDataGrid,
						me.DrugDataGrid
					]
				}]
			}]
		});

		this.callParent(arguments);
	}

});

