/**
 * swRegisterSixtyPlusEditWindow - Регистр «Скрининг населения возраста 60+» 
 * @author      Apaev Alexander
 * @version     07.12.2018
 */

sw.Promed.swRegisterSixtyPlusEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	autoScroll: true,
	title: 'Анкета пациента',
	layout: 'form',
	id: 'swRegisterSixtyPlusEditWindow',
	modal: true,
	onHide: Ext.emptyFn,
	onSelect: Ext.emptyFn,
	shim: false,
	resizable: false,
	maximizable: false,
	maximized: true,
	region: 'center',
	user_id: 0,
	lpu_id: 0,
	ghk_width: 343,
	kmsl_width: 255,
	m_width_min: 150,
	m_width_date: 95,

	listeners: {
		hide: function () {
			this.onHide();
		}
	},

	show: function (params) {
		sw.Promed.swRegisterSixtyPlusEditWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.svedPanel.getForm();
		base_form.reset();

		this.PersonInfoPanel.personId = params.Person_id;
		this.PersonInfoPanel.serverId = params.Server_id;

		base_form.findField('person_id').setValue(params.Person_id);

		this.PersonInfoPanel.setTitle('...');
		this.PersonInfoPanel.load({
			callback: function () {
				this.PersonInfoPanel.setPersonTitle();
			}.createDelegate(this),
			Person_id: this.PersonInfoPanel.personId,
			Server_id: this.PersonInfoPanel.serverId
		});
		wnd.onHide = Ext.emptyFn;
		//debugger;

		base_form.findField('Person_Age').setValue(params.Person_Age);
		base_form.findField('MSE').setValue(params.MSE);
		base_form.findField('fap').setValue(params.LpuRegion_Descr);
		base_form.findField('ZNO').setValue(params.ZNO);
		base_form.findField('BSK').setValue(params.BSK);
		base_form.findField('CD').setValue(params.CD);
		base_form.findField('ONMK').setValue(params.ONMK);
		base_form.findField('OKS').setValue(params.OKS);
		base_form.findField('DU').setValue(params.DU);
		base_form.findField('Sex_id').setValue(params.Sex_id);


		var params = {};
		params.Person_id = this.svedPanel.getForm().findField('Person_id').getValue();
		this.DUGrid.loadData({globalFilters: params});
		this.GlucoseGrid.loadData({globalFilters: params});
		this.CholesterolGrid.loadData({globalFilters: params});
		this.creatineGrid.loadData({globalFilters: params});
		this.IMTGrid.loadData({globalFilters: params});
		this.OAKGrid.loadData({globalFilters: params});
		this.OAMGrid.loadData({globalFilters: params});
		this.ASTGrid.loadData({globalFilters: params});
		this.ALTGrid.loadData({globalFilters: params});

		var sex = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('Sex_id').getValue();
		if (sex == 1) {
			this.MammographyGrid.hide();
			this.GynecologyGrid.hide();
		} else {
			this.MammographyGrid.show();
			this.GynecologyGrid.show();
		}

		this.centerPanel.setActiveTab(0);

		wnd.DUGrid.getGrid().getSelectionModel().lock();
		wnd.GlucoseGrid.getGrid().getSelectionModel().lock();
		wnd.CholesterolGrid.getGrid().getSelectionModel().lock();
		wnd.creatineGrid.getGrid().getSelectionModel().lock();
		wnd.IMTGrid.getGrid().getSelectionModel().lock();
		wnd.OAKGrid.getGrid().getSelectionModel().lock();
		wnd.OAMGrid.getGrid().getSelectionModel().lock();
		wnd.ASTGrid.getGrid().getSelectionModel().lock();
		wnd.ALTGrid.getGrid().getSelectionModel().lock();

	},

	//печать уточненных диагнозов 
	schedulePrintDs: function (action)
	{
		var record = this.diagsPanel.getGrid().getSelectionModel().getSelected();

		if (!record) {
			sw.swMsg.alert(langs('Ошибка'), langs('Запись не выбрана'));
			return false;
		}

		if (action && action == 'row') {
			Ext.ux.GridPrinter.print(this.diagsPanel.getGrid(), {rowId: record.id});
		} else {
			Ext.ux.GridPrinter.print(this.diagsPanel.getGrid());
		}
	},

	//печать лекарст. лечение
	schedulePrintTD: function (action)
	{
		var record = this.TreatmentDrugGrid.getGrid().getSelectionModel().getSelected();

		if (!record) {
			sw.swMsg.alert(langs('Ошибка'), langs('Запись не выбрана'));
			return false;
		}

		if (action && action == 'row') {
			Ext.ux.GridPrinter.print(this.TreatmentDrugGrid.getGrid(), {rowId: record.id});
		} else {
			Ext.ux.GridPrinter.print(this.TreatmentDrugGrid.getGrid());
		}
	},

	//проверка соответствия лпу текущего пользователя, лпу случая эко
	isThisUser: function () {

		if (this.lpu_id == getGlobalOptions().lpu[0]) {
			return true;
		} else
			return false;
	},
	//загрузка уточненных диагнозов
	onLoadDs: function () {
		var params = {};
		params.Person_id = this.svedPanel.getForm().findField('person_id').getValue();
		this.diagsPanel.loadData({globalFilters: params});
	},
	//загрузка уточненных обследований
	onLoadSurveys: function () {
		var params = {};
		params.Person_id = this.svedPanel.getForm().findField('person_id').getValue();
		this.EKGGrid.loadData({globalFilters: params});
		this.fluoroGrid.loadData({globalFilters: params});
		this.OncocontrolGrid.loadData({globalFilters: params});
		this.MammographyGrid.loadData({globalFilters: params});
		this.EchoGrid.loadData({globalFilters: params});

		this.EKGGrid.getGrid().getSelectionModel().lock();
		this.fluoroGrid.getGrid().getSelectionModel().lock();
		this.OncocontrolGrid.getGrid().getSelectionModel().lock();
		this.MammographyGrid.getGrid().getSelectionModel().lock();
		this.EchoGrid.getGrid().getSelectionModel().lock();
	},
	onLoadmedicalcare: function () {
		var params = {};
		params.Person_id = this.svedPanel.getForm().findField('person_id').getValue();
		this.CardiologyGrid.loadData({globalFilters: params});
		this.NeurologyGrid.loadData({globalFilters: params});
		this.OncologyGrid.loadData({globalFilters: params});
		this.OphthalmologyGrid.loadData({globalFilters: params});
		this.GynecologyGrid.loadData({globalFilters: params});
		this.stacmedGrid.loadData({globalFilters: params});

		this.CardiologyGrid.getGrid().getSelectionModel().lock();
		this.NeurologyGrid.getGrid().getSelectionModel().lock();
		this.OncologyGrid.getGrid().getSelectionModel().lock();
		this.OphthalmologyGrid.getGrid().getSelectionModel().lock();
		this.GynecologyGrid.getGrid().getSelectionModel().lock();
		this.stacmedGrid.getGrid().getSelectionModel().lock();
	},
	onLoadTreatmentDrug: function () {
		var params = {};
		params.Person_id = this.svedPanel.getForm().findField('person_id').getValue();
		this.TreatmentDrugGrid.loadData({globalFilters: params});
	},

	setRowClass: function (grids, month) {
		switch (grids) {
			case 'EKGGrid':
				var grid = this.EKGGrid;
				break;
			case 'fluoroGrid':
				var grid = this.fluoroGrid;
				break;
			case 'EchoGrid':
				var grid = this.EchoGrid;
				break;
			case 'MammographyGrid':
				var grid = this.MammographyGrid;
				break;
			case 'OncocontrolGrid':
				var grid = this.OncocontrolGrid;
				break;
		}
		grid.ViewGridPanel.view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';

						var checkZNO = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('ZNO').getValue();
						var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
						var checkCD = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('CD').getValue();

						if (index == 0) {
							var date2 = new Date(row.get('EvnUslugaPar_setDate'));
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));

							if ((checkZNO == true || checkBSK == true || checkCD == true) && Month > month) {
								cls = "x-grid-rowbackred x-grid-rowred";
							} else if (Month > 1) {
								cls = "x-grid-rowbackred x-grid-rowred";
							} else {
								cls = 'x-grid-panel';
							}
						}
						return cls;
					}
				});
	},
	emkOpen: function () {

		getWnd('swPersonEmkWindow').show({
			Person_id: this.PersonInfoPanel.getFieldValue('Person_id'),
			Server_id: this.PersonInfoPanel.getFieldValue('Server_id'),
			PersonEvn_id: this.PersonInfoPanel.getFieldValue('PersonEvn_id'),
			userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
			ARMType: 'common',
			callback: function ()
			{
				//
			}.createDelegate(this)
		});
	},
	initComponent: function () {

		var win = this;

		//Панель с перс данными
		this.PersonInfoPanel = new sw.Promed.PersonInfoPanel({
			floatable: false,
			collapsed: true,
			region: 'north',
			title: lang['zagruzka'],
			plugins: [Ext.ux.PanelCollapsedTitle],
			titleCollapse: true,
			collapsible: true,
			id: 'PersonInfoFrame'
		});

		var ApgarRateRendererdate6 = function (value, metaData, record, rowIndex, colIndex, store) {
			var checkZNO = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('ZNO').getValue();
			var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
			var checkCD = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('CD').getValue();
			if (rowIndex == 0) {
				var dateParts = value.split('.');
				var date2 = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
				var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));
				if ((checkZNO == true || checkBSK == true || checkCD == true) && Month > 6) {
					metaData.css = 'x-grid-cell-reded';
				} else if (Month > 12) {
					metaData.css = 'x-grid-cell-reded';
				} else {
					metaData.css = '';
				}
			} else {
				metaData.css = '';
			}
			return value;
		};

		var ApgarRateRendererdate12 = function (value, metaData, record, rowIndex, colIndex, store) {
			var checkZNO = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('ZNO').getValue();
			var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
			var checkCD = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('CD').getValue();
			if (rowIndex == 0) {
				var dateParts = value.split('.');
				var date2 = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
				var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));
				if ((checkBSK == true || checkZNO == true || checkCD == true) && Month > 12) {
					metaData.css = 'x-grid-cell-reded';
				} else {
					metaData.css = '';
				}
			} else {
				metaData.css = '';
			}
			return value;
		};

		this.ADGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			autoLoadData: false,
			title: 'Артериальное давление',
			//dataUrl: C_SEARCH,
			id: 'ADGrid',
			object: 'AD',
			pageSize: 100,
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			contextmenu: false,
			paging: false,
			toolbar: false,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			height: 170,
			width: 520,
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'id_ad', header: 'id_ad', hidden: true},
				{name: 'ad_id', hidden: true},
				{name: 'DateAD', header: 'Дата', type: 'date', width: 150},
				{name: 'SAD', header: 'САД', width: 160},
				{name: 'DAD', header: 'ДАД', autoexpand: true}, //, autoexpand:true},
			]
		});

		this.IMTGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getPersonIMT',
			autoLoadData: false,
			title: 'Рост-Вес-ИМТ',
			id: 'IMTGrid',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: 520,
			labelWidth: 220,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			labelAlign: 'right',
			pageSize: 100,
			contextmenu: false,
			paging: false,
			//disableSelection: true,
			toolbar: false,
			stringfields: [
				{name: 'PersonWeight_id', header: 'ИД вес', hidden: true},
				{name: 'PersonWeight_setDate', header: 'Дата', width: 150},
				{name: 'PersonHeight_Height', type: 'float', header: 'Рост', width: 120},
				{name: 'PersonWeight_Weight', type: 'float', header: 'Вес', width: 120},
				{name: 'PersonWeight_Imt', header: 'ИМТ', width: 120, /*autoexpand:true,*/ renderer: function (value, meta) {
						var max = 24.9;
						var min = 18.5;
						var str = value;
						var result = parseFloat(str.replace(/\s/g, ""));
						if (result > max || result < min) {
							meta.css = 'x-grid-cell-reded';
						} else {
							meta.css = 'x-grid-panel';
						}
						return value;
					}}
			]
		});
		this.GlucoseGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			title: 'Глюкоза',
			id: 'GlucoseGrid',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			object: 'Glucose',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			labelAlign: 'right',
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'Person_id', header: 'Person_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 140, renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						var checkCD = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('CD').getValue();
						if (rowIndex == 0) {
							var dateParts = value.split('.');
							var date2 = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));
							if (checkCD == true && Month > 3) {
								metaData.css = 'x-grid-cell-reded';
							} else if (Month > 12) {
								metaData.css = 'x-grid-cell-reded';
							}
						} else {
							metaData.css = '';
						}
						return value;
					}},
				{name: 'EvnUslugaPar_ResultValue', header: 'Результат', width: 195, renderer: function (value, meta, r) {
						var ResultUnit = r.get('EvnUslugaPar_ResultUnit');
						var max = 6.7;
						var min = 4.6;
						var str = value;
						var result = parseFloat(str.replace(/\s/g, "").replace(",", "."));
						if (result > max || result < min) {
							meta.css = 'x-grid-cell-reded';
						} else {
							meta.css = 'x-grid-panel';
						}
						return value + ' ' + ResultUnit;
					}},
				{name: 'EvnUslugaPar_ResultUnit', header: 'Ед. измерения', hidden: true}
			]
		});

		/*this.GlucoseGrid.ViewGridPanel.view = new Ext.grid.GridView(
		 {	
		 getRowClass: function (row, index)
		 {	
		 var cls = '';
		 
		 var checkZNO = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('ZNO').getValue();
		 var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
		 
		 if (index == 0) {
		 var date2 = new Date(row.get('EvnUslugaPar_setDate'));
		 var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));
		 
		 if ((checkZNO == true || checkBSK == true) && Month > 6) {
		 cls = "x-grid-rowbackred x-grid-rowred";
		 } else if (Month > 12) {
		 cls = "x-grid-rowbackred x-grid-rowred";
		 }
		 } 
		 var max = 6.7;
		 var min = 4.6;
		 var str = row.get('EvnUslugaPar_ResultValue');
		 var result = parseFloat(str.replace(/\s/g, "").replace(",", "."));
		 if (result > max || result < min) {
		 cls = cls+"x-grid-rowred";
		 } else {
		 cls = 'x-grid-panel';
		 }
		 return cls;
		 }
		 });*/

		this.CholesterolGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			title: 'Холестерин',
			object: 'Cholesterol',
			id: 'CholesterolGrid',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			enableRowBody: true,
			labelWidth: 260,
			labelAlign: 'right',
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'EvnUslugaPar_id', header: 'EvnUslugaPar_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 140, renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						var checkZNO = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('ZNO').getValue();
						var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
						var checkCD = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('CD').getValue();
						if (rowIndex == 0) {
							var dateParts = value.split('.');
							var date2 = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));
							if ((checkCD == true || checkBSK == true || checkZNO == true) && Month > 12) {
								metaData.css = 'x-grid-cell-reded';
							} else if (Month > 12) {
								metaData.css = 'x-grid-cell-reded';
							}
						} else {
							metaData.css = '';
						}
						return value;
					}},
				{name: 'EvnUslugaPar_ResultValue', header: 'Результат', width: 195, renderer: function (value, meta, r) {
						var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
						var ResultUnit = r.get('EvnUslugaPar_ResultUnit');
						var str = value;
						var result = parseFloat(str.replace(/\s/g, "").replace(",", "."));
						if (checkBSK == true && result > 3.6) {
							meta.css = 'x-grid-cell-reded';
						} else if (result > 5.2) {
							meta.css = 'x-grid-cell-reded';
						} else {
							meta.css = 'x-grid-panel';
						}
						return value + ' ' + ResultUnit;
					}},
				{name: 'EvnUslugaPar_ResultUnit', header: 'Ед. измерения', hidden: true}
			]
		});



		this.creatineGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			object: 'creatine',
			title: 'Креатинин',
			id: 'creatineGrid',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			width: this.ghk_width,
			labelWidth: 220,
			labelAlign: 'right',
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'Person_id', header: 'Person_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 140, renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						var checkZNO = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('ZNO').getValue();
						var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
						var checkCD = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('CD').getValue();
						if (rowIndex == 0) {
							var dateParts = value.split('.');
							var date2 = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));
							if ((checkCD == true || checkBSK == true || checkZNO == true) && Month > 12) {
								metaData.css = 'x-grid-cell-reded';
							} else if (Month > 12) {
								metaData.css = 'x-grid-cell-reded';
							}
						} else {
							metaData.css = '';
						}
						return value;
					}},
				{name: 'EvnUslugaPar_ResultValue', header: 'Результат', width: 195, renderer: function (value, meta, r) {
						var sex = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('Sex_id').getValue();
						var ResultUnit = r.get('EvnUslugaPar_ResultUnit');
						var str = value;
						var result = parseFloat(str.replace(/\s/g, "").replace(",", "."));
						if (sex == 1) {
							if (result > 115.0 || result < 62.0) {
								meta.css = 'x-grid-cell-reded';
							} else {
								meta.css = 'x-grid-panel';
							}
						} else {
							if (result > 97. || result < 53.0) {
								meta.css = 'x-grid-cell-reded';
							} else {
								meta.css = 'x-grid-panel';
							}
						}
						return value + ' ' + ResultUnit;
					}},
				{name: 'EvnUslugaPar_ResultUnit', header: 'Ед. измерения', hidden: true}
			]
		});

		this.OAKGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			title: 'Общий анализ крови',
			id: 'OAKGrid',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.kmsl_width,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			labelWidth: 220,
			labelAlign: 'right',
			pageSize: 100,
			contextmenu: false,
			object: 'OAK',
			toolbar: false, //меню
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'Person_id', header: 'Person_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 100, renderer: ApgarRateRendererdate6},
				{name: 'EvnXml_id', header: 'Результат', hidden: true},
				{name: 'prosmotr', header: 'Результат', width: 150, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnXml_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + rec.get('EvnXml_id') + " });'>" + "Просмотреть" + "</a>";
						}
					}}
			]
		});

		this.OAMGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			title: 'Общий анализ мочи',
			id: 'OAMGrid',
			object: 'OAM',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.kmsl_width,
			labelWidth: 220,
			labelAlign: 'right',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'Person_id', header: 'Person_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 100, renderer: ApgarRateRendererdate6},
				{name: 'EvnXml_id', header: 'Результат', hidden: true},
				{name: 'prosmotr', header: 'Результат', width: 150, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnXml_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + rec.get('EvnXml_id') + " });'>" + "Просмотреть" + "</a>";
						}
					}}
			]
		});

		//Биохим анализ крови
		this.ALTGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			title: 'Биохим. анализ крови (АЛТ)',
			id: 'ALTGrid',
			object: 'ALT',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.kmsl_width,
			labelWidth: 220,
			labelAlign: 'right',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'EvnUslugaPar_id', header: 'EvnUslugaPar_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 100, renderer: ApgarRateRendererdate12},
				{name: 'EvnUslugaPar_ResultValue', header: 'Результат', width: 150, renderer: function (value, meta, r) {
						var ResultUnit = r.get('EvnUslugaPar_ResultUnit');
						var max = 40;
						var min = 5;
						var str = value;
						var result = parseFloat(str.replace(/\s/g, "").replace(",", "."));
						if (result > max || result < min) {
							meta.css = 'x-grid-cell-reded';
						} else {
							meta.css = 'x-grid-panel';
						}
						return value + ' ' + ResultUnit;
					}},
				{name: 'EvnUslugaPar_ResultUnit', header: 'Ед. измерения', hidden: true}
			]
		});
		this.ASTGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			title: 'Биохим. анализ крови (АСТ)',
			id: 'ASTGrid',
			object: 'AST',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.kmsl_width,
			labelWidth: 220,
			labelAlign: 'right',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'EvnUslugaPar_id', header: 'EvnUslugaPar_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 100, renderer: ApgarRateRendererdate12},
				{name: 'EvnUslugaPar_ResultValue', header: 'Результат', width: 150, renderer: function (value, meta, r) {
						var ResultUnit = r.get('EvnUslugaPar_ResultUnit');
						var max = 38;
						var min = 5;
						var str = value;
						var result = parseFloat(str.replace(/\s/g, "").replace(",", "."));
						if (result > max || result < min) {
							meta.css = 'x-grid-cell-reded';
						} else {
							meta.css = 'x-grid-panel';
						}
						return value + ' ' + ResultUnit;
					}},
				{name: 'EvnUslugaPar_ResultUnit', header: 'Ед. измерения', hidden: true}
			]
		});

		//Обследования

		this.EKGGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			title: 'ЭКГ',
			id: 'EKGGrid',
			object: 'EKG',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			labelAlign: 'right',
			pageSize: 100,
			contextmenu: false,
			paging: false,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			toolbar: false,
			stringfields: [
				{name: 'Person_id', header: 'Person_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 140, renderer: ApgarRateRendererdate6},
				{name: 'EvnXml_id', header: 'Результат', width: 95, hidden: true},
				{name: 'prosmotr', header: 'Результат', width: 195, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnXml_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + rec.get('EvnXml_id') + " });'>" + 'Просмотреть' + "</a>";
						}
					}}
			]
		});

		this.fluoroGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			title: 'Флюрография',
			id: 'fluoroGrid',
			object: 'fluoro',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			labelAlign: 'right',
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'Person_id', header: 'Person_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 140, renderer: ApgarRateRendererdate12},
				{name: 'EvnXml_id', header: 'Результат', width: 95, hidden: true},
				{name: 'prosmotr', header: 'Результат', width: 100, width: 195, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnXml_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + rec.get('EvnXml_id') + " });'>" + 'Просмотреть' + "</a>";
						}
					}}
			]
		});
		this.OncocontrolGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getOncocontrol',
			autoLoadData: false,
			title: 'Онкоконтроль',
			id: 'OncocontrolGrid',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			labelAlign: 'right',
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'Person_id', header: 'Person_id', hidden: true},
				{name: 'PersonOnkoProfile_DtBeg', header: 'Дата', width: 140, renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						var checkZNO = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('ZNO').getValue();
						var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
						var checkCD = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('CD').getValue();
						if (rowIndex == 0) {
							var dateParts = value.split('.');
							var date2 = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));
							if ((checkCD == true || checkBSK == true) && Month > 12) {
								metaData.css = 'x-grid-cell-reded';
							} else {
								metaData.css = '';
							}
						} else {
							metaData.css = '';
						}
						return value;
					}},
				{name: 'monitored_Name', header: 'Результат', width: 100, width: 195}
			]
		});

		this.MammographyGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			title: 'Маммография',
			id: 'MammographyGrid',
			object: 'Mammography',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			labelAlign: 'right',
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'Person_id', header: 'Person_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 140, renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						var checkZNO = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('ZNO').getValue();
						var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
						var checkCD = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('CD').getValue();
						if (rowIndex == 0) {
							var dateParts = value.split('.');
							var date2 = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));
							if ((checkCD == true || checkBSK == true) && Month > 24) {
								metaData.css = 'x-grid-cell-reded';
							} else if (checkZNO == true && Month > 12) {
								metaData.css = 'x-grid-cell-reded';
							} else if (Month > 24) {
								metaData.css = 'x-grid-cell-reded';
							}
						} else {
							metaData.css = '';
						}
						return value;
					}},
				{name: 'EvnXml_id', header: 'Результат', width: 95, hidden: true},
				{name: 'prosmotr', header: 'Результат', width: 195, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnXml_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + rec.get('EvnXml_id') + " });'>" + 'Просмотреть' + "</a>";
						}
					}}
			]
		});

		this.EchoGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getLabResearch',
			autoLoadData: false,
			title: 'ЭХО-КГ',
			id: 'EchoGrid',
			object: 'Echo',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			labelAlign: 'right',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'Person_id', header: 'Person_id', hidden: true},
				{name: 'EvnUslugaPar_setDate', header: 'Дата', width: 140, renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						var checkZNO = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('ZNO').getValue();
						var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
						var checkCD = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('CD').getValue();
						if (rowIndex == 0) {
							var dateParts = value.split('.');
							var date2 = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));
							if (checkBSK == true && Month > 12) {
								metaData.css = 'x-grid-cell-reded';
							} else {
								metaData.css = '';
							}
						} else {
							metaData.css = '';
						}
						return value;
					}},
				{name: 'EvnXml_id', header: 'Результат', width: 95, hidden: true},
				{name: 'prosmotr', header: 'Результат', width: 195, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnXml_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + rec.get('EvnXml_id') + " });'>" + "Просмотреть" + "</a>";
						}
					}}
			]
		});

		//Случаи медпомощи

		this.CardiologyGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getMedicalCare',
			autoLoadData: false,
			title: 'Кардиология',
			id: 'CardiologyGrid',
			object: 'Cardiology',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			labelAlign: 'right',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'EvnVizitPL_id', header: 'EvnVizitPL_id', hidden: true},
				{name: 'EvnVizitPL_setDate', header: 'Дата', width: 140, type: 'date'},
				{name: 'EvnPL_id', header: 'Результат', width: 195, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnPL_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnPLEditWindow\").show({\"action\": \"view\", \"streamInput\":true, EvnPL_id: " + rec.get('EvnPL_id') + " });'>" + "Просмотреть" + "</a>";
						}
					}}
			]
		});


		this.NeurologyGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getMedicalCare',
			autoLoadData: false,
			title: 'Неврология',
			id: 'NeurologyGrid',
			object: 'Neurology',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			labelAlign: 'right',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'EvnVizitPL_id', header: 'EvnVizitPL_id', hidden: true},
				{name: 'EvnVizitPL_setDate', header: 'Дата', width: 140, type: 'date'},
				{name: 'EvnPL_id', header: 'Результат', width: 195, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnPL_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnPLEditWindow\").show({\"action\": \"view\", \"streamInput\":true, EvnPL_id: " + rec.get('EvnPL_id') + " });'>" + "Просмотреть" + "</a>";
						}
					}}
			]
		});

		this.NeurologyGrid.ViewGridPanel.view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
						if (index == 0) {
							var date2 = new Date(row.get('EvnVizitPL_setDate'));
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));

							if ((checkBSK == true) && Month > 12) {
								cls = "x-grid-rowbackred x-grid-rowred";
							} else {
								cls = 'x-grid-panel';
							}
						}
						return cls;
					}
				});

		this.OncologyGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getMedicalCare',
			autoLoadData: false,
			title: 'Онкология',
			id: 'OncologyGrid',
			object: 'Oncology',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			labelAlign: 'right',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'EvnVizitPL_id', header: 'EvnVizitPL_id', hidden: true},
				{name: 'EvnVizitPL_setDate', header: 'Дата', width: 140, type: 'date'},
				{name: 'EvnPL_id', header: 'Результат', width: 195, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnPL_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnPLEditWindow\").show({\"action\": \"view\", \"streamInput\":true, EvnPL_id: " + rec.get('EvnPL_id') + " });'>" + "Просмотреть" + "</a>";
						}
					}}
			]
		});

		this.OncologyGrid.ViewGridPanel.view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						var checkZNO = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('ZNO').getValue();
						if (index == 0) {
							var date2 = new Date(row.get('EvnVizitPL_setDate'));
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));

							if ((checkZNO == true) && Month > 3) {
								cls = "x-grid-rowbackred x-grid-rowred";
							} else {
								cls = 'x-grid-panel';
							}
						}
						return cls;
					}
				});

		this.OphthalmologyGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getMedicalCare',
			autoLoadData: false,
			title: 'Офтальмология',
			id: 'OphthalmologyGrid',
			object: 'Ophthalmology',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			labelAlign: 'right',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'EvnVizitPL_id', header: 'EvnVizitPL_id', hidden: true},
				{name: 'EvnVizitPL_setDate', header: 'Дата', width: 140, type: 'date'},
				{name: 'EvnPL_id', header: 'Результат', width: 195, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnPL_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnPLEditWindow\").show({\"action\": \"view\", \"streamInput\":true, EvnPL_id: " + rec.get('EvnPL_id') + " });'>" + "Просмотреть" + "</a>";
						}
					}}
			]
		});

		this.OphthalmologyGrid.ViewGridPanel.view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
						if (index == 0) {
							var date2 = new Date(row.get('EvnVizitPL_setDate'));
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));

							if ((checkBSK == true) && Month > 12) {
								cls = "x-grid-rowbackred x-grid-rowred";
							} else {
								cls = 'x-grid-panel';
							}
						}
						return cls;
					}
				});

		this.GynecologyGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getMedicalCare',
			autoLoadData: false,
			title: 'Гинекология',
			id: 'GynecologyGrid',
			object: 'Gynecology',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: this.ghk_width,
			labelWidth: 220,
			labelAlign: 'right',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'EvnVizitPL_id', header: 'EvnVizitPL_id', hidden: true},
				{name: 'EvnVizitPL_setDate', header: 'Дата', width: 140, type: 'date'},
				{name: 'EvnPL_id', header: 'Результат', width: 195, renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnPL_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnPLEditWindow\").show({\"action\": \"view\", \"streamInput\":true, EvnPL_id: " + rec.get('EvnPL_id') + " });'>" + "Просмотреть" + "</a>";
						}
					}}
			]
		});

		this.GynecologyGrid.ViewGridPanel.view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						//var checkBSK = Ext.getCmp('swRegisterSixtyPlusEditWindow').findById('BSK').getValue();
						if (index == 0) {
							var date2 = new Date(row.get('EvnVizitPL_setDate'));
							var Month = (new Date().getMonth() - date2.getMonth()) + (12 * (new Date().getFullYear() - date2.getFullYear()));

							if (Month > 12) {
								cls = "x-grid-rowbackred x-grid-rowred";
							} else {
								cls = 'x-grid-panel';
							}
						}
						return cls;
					}
				});


		//случаи оказания стац мед помощи

		this.stacmedGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 110,
			dataUrl: '/?c=RegisterSixtyPlus&m=getStacMed',
			autoLoadData: false,
			object: 'stacmed',
			//title: 'стац мед помошь',
			id: 'stacmedGrid',
			style: 'border: 1px solid #666; margin: 5px 10px 0px 0px;',
			height: 170,
			width: 1050,
			labelWidth: 220,
			labelAlign: 'right',
			useEmptyRecord: false,
			focusOnFirstLoad: false,
			pageSize: 100,
			contextmenu: false,
			paging: false,
			toolbar: false,
			stringfields: [
				{name: 'EvnPS_id', header: 'EvnPS_id', hidden: true},
				{name: 'EvnPS_Date', header: 'Период госпитализации', width: 200},
				{name: 'Diag_Name', header: 'Основной диагноз по КВС', type: 'string', width: 500},
				{name: 'EvnPS_id', header: 'Просмотр КВС', id: 'autoexpand', renderer: function (value, cellEl, rec) {
						if (Ext.isEmpty(rec.get('EvnPS_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnPSEditWindow\").show({\"action\": \"view\", EvnPS_id: " + rec.get('EvnPS_id') + " });'>" + "Просмотреть" + "</a>";
						}
					}}
			]
		});


		//Лекарственное лечение
		this.TreatmentDrugGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=RegisterSixtyPlus&m=geTreatmentDrug',
			id: 'TreatmentDrug',
			//height: 700,
			//layout: 'fit',
			bodyBorder: true,
			pageSize: 100,
			region: 'center',
			contextmenu: false,
			useEmptyRecord: false,
			paging: false,
			toolbar: false,
			object: 'TreatmentDrug',
			//root: 'data',
			stringfields: [
				{name: 'EvnPrescrTreat_id', header: 'ИД', width: 100, hidden: true},
				{name: 'EvnPrescr_setDate', header: 'Дата назначения', width: 150, type: 'date', sort: true},
				{name: 'CourseDuration', header: 'Продолжительность', width: 150},
				{name: 'PrescriptionIntroType_Name', header: 'Вид приема', width: 200},
				{name: 'NAME', header: 'Фармакологическая группа', width: 200},
				{name: 'Drug_Name', header: 'МНН', width: 400},
				{name: 'DoseDay', header: 'Дозировка', width: 100},
				{name: 'LpuSectionProfile_Name', header: 'Профиль', width: 300},
				{name: 'Person_Fio', header: 'Врач', id: 'autoexpand'}

			]
		});

		/*win.setRowClass('EKGGrid', 6);
		 win.setRowClass('fluoroGrid', 12);
		 win.setRowClass('EchoGrid', 12);
		 win.setRowClass('MammographyGrid', 24);
		 win.setRowClass('OncocontrolGrid', 12);*/

		//Дисп учет

		//Панель с уточненными диагнозами ДУ
		this.DUGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			style: 'border: 1px solid #666;',
			dataUrl: '/?c=RegisterSixtyPlus&m=getDiagDU',
			id: 'DUGrid',
			height: 120,
			title: 'Состоит на диспансерном учёте',
			width: 600,
			bodyBorder: true,
			pageSize: 100,
			region: 'center',
			useEmptyRecord: false,
			contextmenu: false,
			paging: false,
			toolbar: false,
			object: 'DUGrid',
			//border: true, 
			pageSize: 100,
			stringfields: [
				{name: 'PersonDisp_id', header: 'ИД', width: 100, hidden: true},
				{name: 'PersonDisp_begDate', header: 'Дата постановки', width: 110, type: 'date'},
				{name: 'Diag_Code', header: 'Код МКБ-10', width: 100},
				{name: 'Diag_Name', header: 'Диагноз', id: 'autoexpand'},
			]
		});

		//Список уточненных диагнозов

		//Панель с уточненными диагнозами
		this.diagsPanel = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=RegisterSixtyPlus&m=getDiagList',
			id: 'diagsFrame',
			//height: 700,
			//layout: 'fit',
			bodyBorder: true,
			pageSize: 100,
			region: 'center',
			contextmenu: false,
			useEmptyRecord: false,
			paging: false,
			toolbar: false,
			object: 'diagsFrame',
			//root: 'data',
			stringfields: [
				{name: 'id', header: 'ИД', width: 100, hidden: true},
				{name: 'Diag_setDate', header: 'Дата установки', width: 95, type: 'date'},
				{name: 'Diag_Code', header: 'Шифр МКБ', width: 70},
				{name: 'Diag_Name', header: 'Диагноз', autoexpand: true},
				{name: 'Lpu_Nick', header: 'ЛПУ', width: 120},
				{name: 'LpuSectionProfile_Name', header: 'Профиль', width: 325},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 270, id: 'autoexpand'}
			]
		});


		//панель сведений
		this.svedPanel = new Ext.FormPanel({
			title: null,
			//bodySyle:'background:#ffffff;',
			//style: 'margin: 5px 10px 0px 0px;',
			layout: 'form',
			autoWidth: true,
			labelWidth: 240,
			labelAlign: 'right',
			id: 'svedPanel',
			tbar: [
				{
					xtype: 'button',
					text: 'Открыть ЭМК',
					iconCls: 'open16',
					handler: function () {
						this.emkOpen();
					}.createDelegate(this)
				}/*,
				 {
				 xtype: 'button',
				 text: 'Печать',
				 iconCls: 'print16',
				 handler: function () {
				 },
				 }*/
			],
			items: [
				{
					xtype: 'panel',
					title: 'Общие сведения о пациенте',
					//style : 'margin: 3px 0px 5px 0px; border: 1px solid #666',
					style: 'margin: 3px 0px 5px 0px; ',
					//сворачивание по клику на заголовок
					listeners: {
					},
					//collapsible: true,
					layout: 'column',
					bodyStyle: 'padding: 10px',
					autoWidth: true,
					labelWidth: 90,
					labelAlign: 'right',
					items: [
						{
							layout: 'form',
							border: false,
							style: 'margin: 25px 10px 0px 0px;',
							items: [
								{
									xtype: 'textfield',
									id: 'Person_id',
									name: 'person_id',
									//labelSeparator: ':',
									hidden: true,
									hideLabel: true,
									fieldLabel: 'перс ид'
								},
								{
									xtype: 'textfield',
									id: 'Sex_id',
									name: 'Sex_id',
									//labelSeparator: ':',
									hidden: true,
									hideLabel: true,
									fieldLabel: 'пол'
								},
								{
									xtype: 'textfield',
									width: 170,
									toUpperCase: true,
									name: 'Person_Age',
									fieldLabel: 'Возраст',
									disabled: true
								},
								{
									fieldLabel: 'Инвалидность',
									name: 'MSE',
									width: 170,
									xtype: 'textfield',
									disabled: true,
									style: 'margin: 5px 0px 0px 0px;'
								},
								{
									fieldLabel: 'ФАП участок',
									name: 'fap',
									width: 170,
									xtype: 'textfield',
									disabled: true,
									style: 'margin: 5px 0px 0px 0px;'
								}/*,
								 {
								 fieldLabel: 'Дата исключения',
								 width: 170,
								 xtype: 'textfield',
								 disabled: true,
								 style : 'margin: 5px 0px 0px 0px;'
								 }*/
							]
						},
						{
							xtype: 'fieldset',
							autoHeight: true,
							//title: 'Профиль',
							labelWidth: 100,
							labelAlign: 'right',
							style: 'margin: 9px 10px 10px 10px;',
							items: [
								{
									fieldLabel: 'Профиль ЗНО',
									xtype: 'checkbox',
									inputValue: '1',
									uncheckedValue: '1',
									name: 'ZNO',
									id: 'ZNO',
									disabled: true,
									width: 50,
								},
								{
									fieldLabel: 'Профиль БСК',
									xtype: 'checkbox',
									inputValue: '1',
									uncheckedValue: '1',
									name: 'BSK',
									id: 'BSK',
									disabled: true,
									width: 50,
								},
								{
									fieldLabel: 'Профиль ОНМК',
									xtype: 'checkbox',
									inputValue: '1',
									uncheckedValue: '1',
									name: 'ONMK',
									id: 'ONMK',
									disabled: true,
									width: 50,
								},
								{
									fieldLabel: 'Профиль ОКС',
									xtype: 'checkbox',
									inputValue: '1',
									uncheckedValue: '1',
									name: 'OKS',
									id: 'OKS',
									disabled: true,
									width: 50,
								},
								{
									fieldLabel: 'Профиль СД',
									xtype: 'checkbox',
									inputValue: '1',
									uncheckedValue: '1',
									name: 'CD',
									id: 'CD',
									disabled: true,
									width: 50,
								}
							]
						},
						{
							layout: 'form',
							border: false,
							style: 'margin: 5px 10px 0px 0px;',
							labelAlign: 'left',
							items: [
								{
									fieldLabel: 'Состоит на ДУ',
									xtype: 'checkbox',
									inputValue: '1',
									uncheckedValue: '1',
									name: 'DU',
									disabled: true,
									hidden: true,
									hideLabel: true
								},
								this.DUGrid

							]
						}
					]

				},
				{
					xtype: 'panel',
					title: 'Измерения и лабораторные исследования',
					style: 'margin: 3px 0px 5px 0px; ',
					listeners: {
					},
					//collapsible: true,
					//layout: 'column',
					bodyStyle: 'padding: 10px',
					autoWidth: true,
					labelWidth: 150,
					labelAlign: 'right',
					items: [
						{
							xtype: 'panel',
							autoWidth: true,
							labelAlign: 'left',
							layout: 'column',
							items: [
								this.ADGrid,
								this.IMTGrid
							]
						},
						{
							xtype: 'panel',
							autoWidth: true,
							labelAlign: 'left',
							layout: 'column',
							items: [
								this.GlucoseGrid,
								this.CholesterolGrid,
								this.creatineGrid
							]
						},
						{
							xtype: 'panel',
							autoWidth: true,
							labelAlign: 'left',
							layout: 'column',
							items: [
								this.OAKGrid,
								this.OAMGrid,
								this.ASTGrid,
								this.ALTGrid
							]
						}
					]

				}


			]
		});

		//панель обследований
		this.SurveysPanel = new Ext.FormPanel({
			title: null,
			//bodySyle:'background:#ffffff;',
			//style: 'margin: 5px 10px 0px 0px;',
			layout: 'form',
			autoWidth: true,
			labelWidth: 240,
			id: 'SurveysPanel',
			labelAlign: 'right',
			tbar: [
				{
					xtype: 'button',
					text: 'Открыть ЭМК',
					iconCls: 'open16',
					handler: function () {
						this.emkOpen();
					}.createDelegate(this)
				}/*,
				 {
				 xtype: 'button',
				 text: 'Печать',
				 iconCls: 'print16',
				 handler: function () {
				 },
				 }*/
			],
			items: [
				{
					xtype: 'panel',
					title: 'Обследования',
					style: 'margin: 3px 0px 5px 0px; ',
					listeners: {
					},
					//collapsible: true,
					//layout: 'column',
					bodyStyle: 'padding: 10px',
					autoWidth: true,
					labelWidth: 150,
					labelAlign: 'right',
					items: [
						{
							xtype: 'panel',
							autoWidth: true,
							labelAlign: 'left',
							layout: 'column',
							items: [
								this.EKGGrid,
								this.fluoroGrid,
								this.OncocontrolGrid
							]
						},
						{
							xtype: 'panel',
							autoWidth: true,
							labelAlign: 'left',
							layout: 'column',
							items: [
								this.EchoGrid,
								this.MammographyGrid
							]
						}
					]
				}

			]
		});

		this.medicalcare = new Ext.FormPanel({
			title: null,
			layout: 'form',
			autoWidth: true,
			labelWidth: 240,
			id: 'medicalcarePanel',
			labelAlign: 'right',
			tbar: [
				{
					xtype: 'button',
					text: 'Открыть ЭМК',
					iconCls: 'open16',
					handler: function () {
						this.emkOpen();
					}.createDelegate(this)
				}/*,
				 {
				 xtype: 'button',
				 text: 'Печать',
				 iconCls: 'print16',
				 handler: function () {
				 },
				 }*/
			],
			items: [
				{
					xtype: 'panel',
					title: 'Случаи оказания амбулаторно-поликлинической медицинской помощи',
					style: 'margin: 3px 0px 5px 0px; ',
					listeners: {
					},
					bodyStyle: 'padding: 10px',
					autoWidth: true,
					labelWidth: 150,
					labelAlign: 'right',
					items: [
						{
							xtype: 'panel',
							autoWidth: true,
							labelAlign: 'left',
							layout: 'column',
							items: [
								this.CardiologyGrid,
								this.NeurologyGrid,
								this.OncologyGrid
							]
						},
						{
							xtype: 'panel',
							autoWidth: true,
							labelAlign: 'left',
							layout: 'column',
							items: [
								this.OphthalmologyGrid,
								this.GynecologyGrid
							]
						}
					]
				},
				{
					xtype: 'panel',
					title: 'Случаи оказания стационарной медицинской помощи',
					style: 'margin: 3px 0px 5px 0px; ',
					listeners: {
					},
					//collapsible: true,
					//layout: 'column',
					bodyStyle: 'padding: 10px',
					autoWidth: true,
					labelWidth: 150,
					labelAlign: 'right',
					items: [
						{
							xtype: 'panel',
							autoWidth: true,
							labelAlign: 'left',
							layout: 'column',
							items: [
								this.stacmedGrid
							]
						}
					]
				}

			]
		});




		//центральная таб панель
		this.centerPanel = new Ext.TabPanel({
			plain: false,
			border: true,
			region: 'center',
			bodyBorder: false,
			style: 'padding:2px;margin:0px;',
			autoScroll: true,
			layoutOnTabChange: true,
			deferredRender: true,
			activeTab: 0,
			listeners: {
				tabchange: function (panel, tab) {
					if (panel.getActiveTab().id == 'tabDs') {
						Ext.getCmp('swRegisterSixtyPlusEditWindow').onLoadDs();
					}
					if (panel.getActiveTab().id == 'tabSurveys') {
						Ext.getCmp('swRegisterSixtyPlusEditWindow').onLoadSurveys();
					}
					if (panel.getActiveTab().id == 'tabmedicalcare') {
						Ext.getCmp('swRegisterSixtyPlusEditWindow').onLoadmedicalcare();
					}
					if (panel.getActiveTab().id == 'tabTreatmentDrug') {
						Ext.getCmp('swRegisterSixtyPlusEditWindow').onLoadTreatmentDrug();
					}

				}
			},
			items: [
				{
					autoHeight: true,
					title: 'Сведения',
					id: 'tabSved',
					border: false,
					//style: 'padding:10px;margin:0px;',
					//autoScroll: true,
					items: [
						this.svedPanel

					]
				},
				{
					autoHeight: true,
					title: 'Обследования',
					id: 'tabSurveys',
					border: false,
					//style: 'padding:10px;margin:0px;',
					autoScroll: true,
					items: [
						this.SurveysPanel

					]
				},
				{
					autoHeight: true,
					title: 'Случаи мед.помощи',
					id: 'tabmedicalcare',
					border: false,
					//style: 'padding:10px;margin:0px;',
					autoScroll: true,
					items: [
						this.medicalcare

					]
				},
				{
					title: 'Список уточненных диагнозов',
					id: 'tabDs',
					border: false,
					layout: 'border',
					tbar: [{
							xtype: 'button',
							text: 'Открыть ЭМК',
							iconCls: 'open16',
							handler: function () {
								this.emkOpen();
							}.createDelegate(this)
						}, {
							xtype: 'button',
							id: 'printDsBtn',
							text: BTN_GRIDPRINT,
							iconCls: 'print16',
							hidden: false,
							menu: new Ext.menu.Menu([
								{text: langs('Печать'), handler: function () {
										this.schedulePrintDs('row');
									}.createDelegate(this)},
								{text: langs('Печать всего списка'), handler: function () {
										this.schedulePrintDs()
									}.createDelegate(this)}
							])
						}],
					items: [
						this.diagsPanel
					]
				},
				{
					title: 'Лекарственное лечение',
					id: 'tabTreatmentDrug',
					border: false,
					layout: 'border',
					tbar: [{
							xtype: 'button',
							text: 'Открыть ЭМК',
							iconCls: 'open16',
							handler: function () {
								this.emkOpen();
							}.createDelegate(this)
						}, {
							xtype: 'button',
							id: 'printDsBtn',
							text: BTN_GRIDPRINT,
							iconCls: 'print16',
							hidden: false,
							menu: new Ext.menu.Menu([
								{text: langs('Печать'), handler: function () {
										this.schedulePrintTD('row');
									}.createDelegate(this)},
								{text: langs('Печать всего списка'), handler: function () {
										this.schedulePrintTD()
									}.createDelegate(this)}
							])
						}],
					items: [
						this.TreatmentDrugGrid
					]
				}
			]
		}),
				//Главная панель, в которой собрано все выше описанное
				this.MainPanel = new Ext.Panel({
					//autoScroll: true,
					bodyBorder: false,
					bodyStyle: 'padding: 2px',
					border: true,
					layout: 'border',
					frame: true,
					region: 'center',
					labelAlign: 'right',
					items: [
						this.centerPanel,
						this.PersonInfoPanel
					]
				});

		Ext.apply(this, {
			layout: 'border',
			buttons:
					[
						/*{
						 handler: function () {
						 this.ownerCt.checkCrossing();
						 },
						 iconCls: 'ok16',
						 text: 'Сохранить',
						 id: 'save'
						 },*/
						{
							text: '-'
						},
						HelpButton(this, 0),
						{
							handler: function () {
								this.ownerCt.hide();
							},
							iconCls: 'cancel16',
							text: BTN_FRMCANCEL
						}],
			items: [win.MainPanel]
		});

		sw.Promed.swRegisterSixtyPlusEditWindow.superclass.initComponent.apply(this, arguments);
	}
});