Ext6.define('common.EMK.EvnPLDispDop.model.DeseaseModel', {
	extend: 'Ext6.app.ViewModel',
	requires: [
		'common.EMK.EvnPLDispDop.store.DeseaseStore'
	],
	alias: 'viewmodel.EvnPLDispDop13DeseaseModel',
	data: {
		EvnPLDispDop13_id: 1,
		EvnPLDispDop13_fid: 2
	},
	formulas: {
		deseaseExtraParams: function (get) {
			return {
				EvnPLDispDop13_id: get('EvnPLDispDop13_id'),
				EvnPLDispDop13_fid: get('EvnPLDispDop13_fid')
			};
		}
	},
	stores: {
		DeseaseStore: {
			type: 'EvnPLDispDop13DeseaseStore',
			proxy: {
				extraParams: '{deseaseExtraParams}'
			}
		}
	}
});

//yl:Заболевания
Ext6.define("common.EMK.EvnPLDispDop.view.DeseasePanel", {
	extend: "swPanel",
	requires: [
		"common.EMK.EvnPLDispDop.controller.DeseaseController",
		"common.EMK.EvnPLDispDop.store.DeseaseStore"
	],
	controller: "EvnPLDispDop13DeseaseController",
	alias: "widget.EvnPLDispDop13DeseasePanel",
	viewModel: "EvnPLDispDop13DeseaseModel",
	title: "Заболевания",
	listeners: {
		expand: 'onExpand'
	},
	setParams: function() {
		//this.setViewModel(this.ownerPanel.getViewModel());
		
		this.getController().setParams();
	},
	initComponent: function () {
		var me = this;

		//Тип диагноза - комбо в гриде, плохая идея - надо было делать менюшку, но зато по дизайну ...
		me.editorCombo = Ext6.create("Ext6.form.field.ComboBox", {
			valueField: "DiagSetClass_id",
			displayField: "DiagSetClass_Name",
			hideLabel: true,
			editable: false,
			store: {
				type: "EvnPLDispDop13DiagSetClassStore"
			},
			listeners: {
				focus: function(combo){
					combo.expand();//сразу раскрою после клика
				},
				expand: function(combo){
					if (!(record = combo.up("editor").context.record)) {
						console.log("yl:expand_no_record");return;
					}
					combo.suspendEvents();
					combo.getPicker().refresh();//убирает выбранные, надо ещё наведённые ...
					combo.setRawValue(record.getData()["DiagSetClass_Name"]);//input_text
					combo.resumeEvents();
				},
				change: function(combo, newValue, oldValue) {//после спахивания, но не отображения
					if (!(record = combo.up("editor").context.record)) {
						console.log("yl:change_no_record");return;
					}else if(record.getData()["DiagSetClass_id"]!=newValue && !isNaN(newValue)){//save
						me.getController().updDeseaseDiagSetClass(record.getData()["EvnDiagDopDisp_id"],newValue,record);
					}
					combo.ownerCt.cancelEdit();//возврат в первоначальное состояние ячейки
				},
				select: function(combo, newValue, oldValue) {//после спахивания, но не отображения
					combo.ownerCt.cancelEdit();//возврат в первоначальное состояние ячейки
				}
			}
		});

		//основной грид с Подозрениями и Диагнозами
		me.DeseaseGrid = Ext6.create("Ext6.grid.Panel", {
			padding: 10,//наружные отступы
			cls: "FactorRiskGrid",//внутренние
			viewConfig: {
				minHeight: 33,
			},
			bind: {
				store: '{DeseaseStore}'
			},
			columns: [
				{
					header: "Диагнозы и подозрения",
					dataIndex: "DispDeseaseSuspType_Name",
					minWidth: 100,
					flex: 2,
					renderer: function (value, metaData, record) {
						if(record.get("DispDeseaseSusp_id")){//Подозрение
							record.set("renderName", record.get("DispDeseaseSuspType_Name"));
						}else{//Диагноз
							record.set("renderName", "<b>"+record.get("Diag_Code")+"</b>&nbsp;"+record.get("Diag_Name"));
						}
						return record.get("renderName");
					},
					sorter: function (item1, item2) {//yl:сортировка по тексту ячейки
						var l = item1.get("renderName"), r = item2.get("renderName");
						return (l > r) ? 1 : (l < r ? -1 : 0);
					}
				}, {
					header: "Медицинская организация",
					dataIndex: "Lpu_Name",
					minWidth: 100,
					flex: 1
				}, {
					header: "Тип",
					dataIndex: "DispDeseaseSusp_id",
					minWidth: 150,
					bind: {
						hidden: '{action == "view"}'
					},
					renderer: function (value, metaData, record) {
						if(record.get("DispDeseaseSusp_id")){//Подозрение
							record.set("renderType","Подозрение");
						}else{//Диагноз
							record.set("renderType", record.get("DiagSetClass_Name"));
						}
						return record.get("renderType");
					},
					sorter: function (item1, item2) {//yl:сортировка по тексту ячейки
						var l = item1.get("renderType"), r = item2.get("renderType");
						return (l > r) ? 1 : (l < r ? -1 : 0);
					},
					editor: me.editorCombo,
				}, {
					header: "Тип",
					//~ dataIndex: "renderType",
					minWidth: 150,
					bind: {
						hidden: '{action != "view"}'
					},
					renderer: function (value, metaData, record) {
						if(record.get("DispDeseaseSusp_id")){//Подозрение
							record.set("renderType","Подозрение");
						}else{//Диагноз
							record.set("renderType", record.get("DiagSetClass_Name"));
						}
						return record.get("renderType");
					},
					sorter: function (item1, item2) {//yl:сортировка по тексту ячейки
						var l = item1.get("renderType"), r = item2.get("renderType");
						return (l > r) ? 1 : (l < r ? -1 : 0);
					},
				}, {
					header: "Дата",
					dataIndex: "Date_insDT",
					minWidth: 100
				}, {
					width: 40,
					bind: {
						hidden: '{action == "view"}'
					},
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" +me.DeseaseGrid.id+ "\").showRecordMenu(this, " + record.get("DispDeseaseSusp_id") + ", " + record.get("EvnDiagDopDisp_id") + ");'></div>";
					}
				}
			],
			plugins: [
				Ext6.create("Ext6.grid.plugin.CellEditing", {
					clicksToEdit: 1,
					id: "DeseaseGridCellEditor",
					listeners: {
						beforeedit: function(editor, context, eOpts){
							if(context.record.getData()["DispDeseaseSusp_id"]){
								return false;//Подозрение не редактируется
							}
						}
					}
				})
			],
			recordMenu: Ext6.create("Ext6.menu.Menu", {
				userCls: "menuWithoutIcons",
				items: [
					{
						text: "Удалить запись",
						handler: me.getController().delClick,
						ownerPanel: me
					}
				]
			}),
			showRecordMenu: function(el, DispDeseaseSusp_id,EvnDiagDopDisp_id) {
				this.recordMenu.DispDeseaseSusp_id = DispDeseaseSusp_id;//подозрение
				this.recordMenu.EvnDiagDopDisp_id = EvnDiagDopDisp_id;//диагноз
				this.recordMenu.showBy(el);
			}
		});

		//заболевания=Диагнозы
		me.DeseaseBtn = Ext6.create("Ext6.Component", {
			autoEl: {
				tag: "a",
				html: "Добавить диагноз",
				style: "text-decoration:none;display:inline-block;padding:0 0 10px 15px;"
			},
			listeners: {
				render: function(cmp){
					cmp.getEl().on({//click только на элементе
						click: function(){
							getWnd("DiagSearchTreeWindow").show({
								onSelect: function(diagData) {
									me.getController().addDesease(diagData.Diag_id);
									getWnd("DiagSearchTreeWindow").hide();//да врое и само закрывается
									return true;
								},
								EvnPLDispDop13Desease: "переопределить loadMode() in Controller",
								Person_id: me.getController().vm_data.Person_id
							});

						}
					});
				}
			}
		});

		//Подозрения - меню
		me.SuspectMenu = Ext6.create("Ext6.menu.Menu", {
			height: 375,
			userCls: "menuWithoutIcons",
			dockedItems: [
				{
					xtype: "textfield",
					itemId: "SuspectMenuFilter",
					emptyText: "Быстрый поиск",
					padding: "0 15px 0 15px",
					dock: "bottom",
					flex: 1,
					listeners: {
						change: "filterSuspectMenu"
					}
				}
			]
		});

		//Подозрения - кнопка Добавить
		me.SuspectBtn = Ext6.create("Ext6.Component", {
			autoEl: {
				tag: "a",
				html: "Подозрение",
				style: "text-decoration:none;"
			},
			listeners: {
				render: function(cmp){
					cmp.getEl().on({//click только на элементе
						click: function(){
							me.SuspectMenu.showBy(cmp).down("#SuspectMenuFilter").setValue().focus();
						}
					});
				}
			}
		});

		Ext6.apply(me, {
			items: [
				me.DeseaseGrid,
				{
					xtype: 'container',
					border: false,
					bind: {
						hidden: '{action == "view"}'
					},
					items: [
						me.DeseaseBtn,
						{
							xtype: "label",
							text: " или "
						},
						me.SuspectMenu,
						me.SuspectBtn,
						
					]
				},
				{
					xtype: 'container',
					border: false,
					padding: 10,
					items: [
						{
							xtype: 'checkbox',
							boxLabel: 'Подозрение на ЗНО',
							//fieldLabel: 'Подозрение на ЗНО',
							itemId: 'EvnPLDispDop13_IsSuspectZNO',
							//inputValue: '2',
							//uncheckedValue: '1',
							bind: {
								value: '{EvnPLDispDop13_IsSuspectZNO}',
								disabled: '{action == "view"}'
							},
							width: 300,
							labelWidth: 180,
							listeners:{
								'blur': 'saveSuspectZNO'
							}
						}, {
							xtype: 'swDiagCombo',
							fieldLabel: 'Подозрение на диагноз',
							itemId: 'Diag_spid',
							bind: {
								hidden: '{!EvnPLDispDop13_IsSuspectZNO}',
								value: '{Diag_spid}',
								allowBlank: '{!EvnPLDispDop13_IsSuspectZNO}',
								disabled: '{action == "view"}'
							},
							width: 500,
							labelWidth: 180,
							additQueryFilter: "(Diag_Code like 'C%' or Diag_Code like 'D0%')",
							baseFilterFn: function(rec){
								if(typeof rec.get == 'function') {
									return (rec.get('Diag_Code').substr(0,1) == 'C' || rec.get('Diag_Code').substr(0,2) == 'D0');
								} else if (rec.attributes && rec.attributes.Diag_Code) {
									return (rec.attributes.Diag_Code.substr(0,1) == 'C' || rec.attributes.Diag_Code.substr(0,2) == 'D0');
								} else {
									return true;
								}
							},
							listeners: {
								'blur': 'saveSuspectZNO'
								//function () {
									//me.saveSuspectZNO();
								//}
							}
						}
					]
				}
			]
		});

		this.callParent(arguments);
	}
});
