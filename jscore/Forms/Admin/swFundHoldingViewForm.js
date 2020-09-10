/**
* Фондодержание
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @version      27.06.2011
*/

/*NO PARSE JSON*/
sw.Promed.swFundHoldingViewForm = Ext.extend(sw.Promed.BaseForm,
{	
	title:lang['fondoderjanie'],
	layout: 'border',
	maximized: true,
	maximizable: false,
	shim: false,
	buttonAlign : "right",
	codeRefresh: true,
	objectName: 'swFundHoldingViewForm',
	objectSrc: '/jscore/Forms/Admin/swFundHoldingViewForm.js',
	buttons:
		[
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.ownerCt.title);
				}
			},
			{
				text      : BTN_FRMCLOSE,
				tabIndex  : -1,
				tooltip   : lang['zakryit_fondoderjanie'],
				iconCls   : 'cancel16',
				handler   : function()
				{
					this.ownerCt.hide();
				}
			}
		],	
	getChartData: function(ret_func) {
		// получаем ноды, которые в данный момент выделены
		var tree = this.swLpuStructureFrame;
		var checked_nodes = tree.getChecked();
		/*
		 * Тип расчета:
		 * lpu - по всему лпу
		 * all_regions - по всем участкам
		 * region_types - по конкретным типам участков
		 * regions - по конкретным участкам
		 * 
		 * по умолчанию расчет по всему ЛПУ
		 */		
		var calc_type = 'lpu';
		// типы участков
		var region_types = [];
		// участки
		var regions = [];
		// проверяем, пришел ли массив выбранных элементов и не пустой ли он
		if ( checked_nodes.length && checked_nodes.length > 0 )
		{
			switch ( checked_nodes[0].attributes.object )
			{
				case 'LpuRegionTitle':
					calc_type = 'all_regions';
				break;
				case 'LpuRegionType':
					calc_type = 'region_types';
					// набираем список идентификаторов типов участка
					for ( var i = 0; i < checked_nodes.length; i++ )
						region_types.push(checked_nodes[i].attributes.object_value);
				break;
				case 'LpuRegion':
					calc_type = 'regions';
					// набираем список идентификаторов участков
					for ( var i = 0; i < checked_nodes.length; i++ )
						regions.push(checked_nodes[i].attributes.object_value);
				break;
			}
		}
		// формируем объект параметров
		var params = {
			CalcType: calc_type,
			RegionTypes: Ext.util.JSON.encode(region_types),
			Regions:  Ext.util.JSON.encode(regions),
			Month: this.monthCombo.getValue(),
			Year: this.yearCombo.getValue()
		}
		// отправляем запрос на проверку
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет формирование данных для построения графика..."});
		loadMask.show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.data === 0 || (response_obj.data != undefined && response_obj.data != "") ) {
						ret_func(response_obj.data);
					}
					else
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_dlya_postroeniya_grafika_obratites_k_administratoru_ili_povtorite_popyitku_pozje']);
				}
			},
			params: params,
			url: '/?c=FundHolding&m=getChartData'
		});
	},
	checkIfReestrDataExists: function() {
		// получаем ноды, которые в данный момент выделены
		var tree = this.swLpuStructureFrame;
		var checked_nodes = tree.getChecked();
		/*
		 * Тип расчета:
		 * lpu - по всему лпу
		 * all_regions - по всем участкам
		 * region_types - по конкретным типам участков
		 * regions - по конкретным участкам
		 * 
		 * по умолчанию расчет по всему ЛПУ
		 */		
		var calc_type = 'lpu';
		// типы участков
		var region_types = [];
		// участки
		var regions = [];
		// проверяем, пришел ли массив выбранных элементов и не пустой ли он
		if ( checked_nodes.length && checked_nodes.length > 0 )
		{
			switch ( checked_nodes[0].attributes.object )
			{
				case 'LpuRegionTitle':
					calc_type = 'all_regions';
				break;
				case 'LpuRegionType':
					calc_type = 'region_types';
					// набираем список идентификаторов типов участка
					for ( var i = 0; i < checked_nodes.length; i++ )
						region_types.push(checked_nodes[i].attributes.object_value);
				break;
				case 'LpuRegion':
					calc_type = 'regions';
					// набираем список идентификаторов участков
					for ( var i = 0; i < checked_nodes.length; i++ )
						regions.push(checked_nodes[i].attributes.object_value);
				break;
			}
		}
		// формируем объект параметров
		var params = {
			CalcType: calc_type,
			RegionTypes: Ext.util.JSON.encode(region_types),
			Regions:  Ext.util.JSON.encode(regions),
			Month: this.monthCombo.getValue(),
			Year: this.yearCombo.getValue()
		}
		// отправляем запрос на проверку
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет проверка наличия данных в реестрах на этот период..."});
		loadMask.show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.data === 0 || (response_obj.data != undefined && response_obj.data != "") ) {
						if ( Number(response_obj.data) === 1 )
						{
							this.reestrData = true;
							// енаблим кнопку расчета графика
							this.findById('FHVF_ShowChartButton').enable();
							this.findById('FHVF_TotalsFieldset').setTitle(lang['svodka_itogovyiy_raschet_po_reestram']);
							Ext.getCmp('FHVF_EvnIsFinish_Combo').setValue(-1);
							Ext.getCmp('FHVF_EvnIsFinish_Combo').disable();
						}
						else
						{
							this.reestrData = false;
							this.findById('FHVF_ShowChartButton').disable();
							this.findById('FHVF_TotalsFieldset').setTitle(lang['svodka_predvaritelnyiy_raschet_po_uchetnyim_dokumentam']);
							Ext.getCmp('FHVF_EvnIsFinish_Combo').enable();
						}
					}
					else
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_proverit_nalichie_dannyih_v_reestrah_obratites_k_administratoru_ili_povtorite_popyitku_pozje']);
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=FundHolding&m=checkIfReestrDataExists'
		});
	},
	show: function()
	{
		// устанавливаем лимит на аякс запросы
		Ext.Ajax.timeout = 600000;
		sw.Promed.swFundHoldingViewForm.superclass.show.apply(this, arguments);
		
		// учтанавливаем текущие год и месяц
		this.yearCombo.setValue(String(getGlobalOptions().date).substr(6, 4));		
		this.monthCombo.setValue(Number(String(getGlobalOptions().date).substr(3, 2)));
		this.filterMonthCombo();

		var node = this.findById('fh-lpu-structure-frame').getRootNode();
		if (this.findById('fh-lpu-structure-frame').rootVisible == false)
		{
			if (node.hasChildNodes() == true)
			{
				node = node.findChild('object', 'Lpu');
			}
		}
		if (node)
		{
			node.select();
			if (node.isExpanded())
			{
				node.getOwnerTree().loader.load(node);
			}
			node.expand();
			this.findById('fh-lpu-structure-frame').fireEvent('click', node);
		}		
	},
	reloadCurrentTreeNode: function(frm)
	{
		var LpuStructureFrame = Ext.getCmp('fh-lpu-structure-frame');
		var selNode = LpuStructureFrame.getSelectionModel().selNode;
		if ((selNode.isExpanded()) || (selNode.childNodes.length>0))
		{
			LpuStructureFrame.loader.load(selNode);
			selNode.on({'expand': {fn: function() {frm.focus();}, scope: selNode, delay: 500}});
			selNode.expand();
		}
	},
	filterMonthCombo: function() {		
		this.monthCombo.lastQuery = '';
		this.monthCombo.getStore().clearFilter();
		// если год текущий, то надо отфильтровать месяцы после текущего
		if ( this.yearCombo.getValue() == String(getGlobalOptions().date).substr(6, 4) )
		{
			var cur_month = Number(String(getGlobalOptions().date).substr(3, 2));
			this.monthCombo.getStore().filterBy(function(rec) {
				if ( rec.data.value > cur_month )
					return false;
				return true;
			});
			if ( cur_month < this.monthCombo.getValue() )
				this.monthCombo.setValue(cur_month);
			this.checkIfReestrDataExists();
			this.resetTotalsArea();
		}
		else
		{
			this.checkIfReestrDataExists();
			this.resetTotalsArea();
		}
	},
	recalcTotals: function(grid_only) {
		// получаем ноды, которые в данный момент выделены
		var tree = this.swLpuStructureFrame;
		var checked_nodes = tree.getChecked();
		/*
		 * Тип расчета:
		 * lpu - по всему лпу
		 * all_regions - по всем участкам
		 * region_types - по конкретным типам участков
		 * regions - по конкретным участкам
		 * 
		 * по умолчанию расчет по всему ЛПУ
		 */		
		var calc_type = 'lpu';
		// типы участков
		var region_types = [];
		// участки
		var regions = [];
		// проверяем, пришел ли массив выбранных элементов и не пустой ли он
		if ( checked_nodes.length && checked_nodes.length > 0 )
		{
			switch ( checked_nodes[0].attributes.object )
			{
				case 'LpuRegionTitle':
					calc_type = 'all_regions';
				break;
				case 'LpuRegionType':
					calc_type = 'region_types';
					// набираем список идентификаторов типов участка
					for ( var i = 0; i < checked_nodes.length; i++ )
						region_types.push(checked_nodes[i].attributes.object_value);
				break;
				case 'LpuRegion':
					calc_type = 'regions';
					// набираем список идентификаторов участков
					for ( var i = 0; i < checked_nodes.length; i++ )
						regions.push(checked_nodes[i].attributes.object_value);
				break;
			}
		}
		// формируем объект параметров
		var params = {
			CalcType: calc_type,
			RegionTypes: Ext.util.JSON.encode(region_types),
			Regions:  Ext.util.JSON.encode(regions),
			Month: this.monthCombo.getValue(),
			Year: this.yearCombo.getValue()
		}		
		if ( !grid_only || grid_only != true )
		{
			// отправляем запрос на расчет
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет расчет..."});
			loadMask.show();
			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.data != undefined && response_obj.data != "" ) {
							document.getElementById('fundholding_totals').innerHTML = response_obj.data;
						}
						else
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_raschitat_svodku_obratites_k_administratoru_ili_povtorite_popyitku_pozje']);
					}
				},
				params: params,
				url: this.reestrData ? '/?c=FundHolding&m=getTotalsCalculationReestr' : '/?c=FundHolding&m=getTotalsCalculation'
			});
		}
		else
		{
			// загружаем грид
			var grid = this.EventsGrid.ViewGridPanel;		

			params.start = 0;
			params.limit = 100;

			var form = this.findById('FundHolding_FilterPanel').getForm();
			Ext.apply(params, form.getValues());
			
			if ( this.reestrData )
				grid.getStore().proxy.conn.url = '/?c=FundHolding&m=getFundHoldingGridReestr';
			else
				grid.getStore().proxy.conn.url = '/?c=FundHolding&m=getFundHoldingGrid';
			grid.getStore().removeAll();
			grid.getStore().baseParams = params;
			grid.getStore().load({
				url: '/?c=FundHolding&m=getFundHoldingGrid',
				params: params,
				callback: function(r) {
					if ( r.length > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			});
		}
	},
	showEvnViewWindow: function() {
		var grid = this.EventsGrid.ViewGridPanel;
		var selected_record = grid.getSelectionModel().getSelected();
		if ( selected_record )
		{
			// посещение
			if ( selected_record.get('EvnClass_id') == 3 )
			{
				var evn_pl_id = selected_record.get('Evn_id');				
				var person_id = selected_record.get('Person_id');
				var server_id = selected_record.get('Server_id');
				var params = {};
				if ( evn_pl_id > 0 && person_id > 0 && server_id >= 0 ) {
					params.action = 'view';
					params.EvnPL_id = evn_pl_id;
					params.onHide = function() {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					};
					params.Person_id =  person_id;
					params.Server_id = server_id;
					getWnd('swEvnPLEditWindow').show(params);
				}
			}
			
			// лечение в стационаре
			if ( selected_record.get('EvnClass_id') == 30 )
			{
				var evn_ps_id = selected_record.get('Evn_id');				
				var person_id = selected_record.get('Person_id');
				var server_id = selected_record.get('Server_id');
				var params = {};
				if ( evn_ps_id > 0 && person_id > 0 && server_id >= 0 ) {
					params.action = 'view';
					params.EvnPS_id = evn_ps_id;
					params.onHide = function() {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					};
					params.Person_id =  person_id;
					params.Server_id = server_id;
					getWnd('swEvnPSEditWindow').show(params);
				}
			}
		}
	},
	id: 'swFundHoldingViewWindow',
	resetTotalsArea: function() {
		document.getElementById('fundholding_totals').innerHTML = lang['raschet_ne_proizveden_vyiberite_neobhodimyiy_raschetnyiy_period_i_najmite_knopku_raschitat'];
	},
	initComponent: function()
	{
		frms = this;

		var swLpuStructureFrame = new sw.Promed.LpuStructure({
			frame: true,
			rootVisible: true,
			changing: false,			
			root: 
			{
				nodeType: 'async',
				text: '',
				id: 'root',
				checked: null,
				draggable: false,
				expandable: true
			},			
			listeners: 
			{
				'checkchange': function (node, checked)
				{
					//Ext.getCmp('swFundHoldingViewWindow').checkIfReestrDataExists();
					Ext.getCmp('swFundHoldingViewWindow').resetTotalsArea();
					if (!this.changing)
					{
						/*this.changing = true;
						node.expand(true, false);
						if (checked)
							node.cascade( function(node){ node.getUI().toggleCheck(true) } );
						else
							node.cascade( function(node){ node.getUI().toggleCheck(false) } );
						node.bubble( function(node){ if (node.parentNode) node.getUI().updateCheck() } );
						this.changing = false;*/
						this.changing = true;
						// если устанавливаем галочку
						if ( checked )
						{
							switch ( node.attributes.object ) {
								case 'Lpu':									
									node.cascade( function(local_node){
										if ( node != local_node )
											local_node.getUI().toggleCheck(false);										
									});
								break;
								case 'LpuRegionTitle':
									node.cascade( function(local_node){
										if ( node != local_node )
											local_node.getUI().toggleCheck(false);
									});
									node.bubble( function(local_node){
										if ( node != local_node )
										{
											local_node.getUI().toggleCheck(false);
											local_node.cascade( function(child_node) {
												if ( node != child_node )
													child_node.getUI().toggleCheck(false)
											});
										}
									});
								break;
								case 'LpuRegionType':
									node.cascade( function(local_node){
										if ( node != local_node )
											local_node.getUI().toggleCheck(false);
									});
									node.bubble( function(local_node){
										if ( node != local_node )
										{
											local_node.getUI().toggleCheck(false)											
											local_node.cascade( function(child_node) {
												if ( node != child_node && child_node.attributes.object != 'LpuRegionType' )
													child_node.getUI().toggleCheck(false)
											});
										}
									});
								break;
								case 'LpuRegion':
									node.bubble( function(local_node){
										if ( node != local_node )
										{
											local_node.getUI().toggleCheck(false)
											local_node.cascade( function(child_node) {
												if ( node != child_node && child_node.attributes.object != 'LpuRegion' )
													child_node.getUI().toggleCheck(false)
											});
										}										
									});
								break;
							}
						}
						if ( checked )
							node.getUI().toggleCheck(true);
						else
							node.getUI().toggleCheck(false);
						//node.getUI().updateCheck();
						this.changing = false;
					}
				}.createDelegate(this.swLpuStructureFrame)
			},
			loader: new Ext.tree.TreeLoader(
			{
				baseAttrs: {
					uiProvider: Ext.tree.TreeNodeTriStateUI,
					checked: false,
					expandable: false
				},
				onBeforeLoad: function(TreeLoader, node) 
				{
					TreeLoader.baseParams.level = node.getDepth();
					TreeLoader.baseParams.level_two = 'All';
					TreeLoader.baseParams.regionsOnly = true;
				},
				dataUrl: C_LPUSTRUCTURE_LOAD,
				baseParams: {from:'FundHolding'}
				//beforeload: function(treeLoader, node) {this.baseParams.method='GetLpuStructure'; this.baseParams.level=2; }
			}),
			id: 'fh-lpu-structure-frame',
			title: lang['uchastki']
		});
		this.swLpuStructureFrame = swLpuStructureFrame;
		swLpuStructureFrame.loader.on("beforeload", function(TreeLoader, node) {TreeBeforeLoad(TreeLoader, node);}, this);
		swLpuStructureFrame.on('click', function(node, e) {LpuStructureTreeClick(node, e)});
		swLpuStructureFrame.loader.addListener('load', function (loader, node)
		{
			if ( node==swLpuStructureFrame.root )
			{
				node = node.findChild('object', 'Lpu');
				node.getUI().toggleCheck(true)
				if (swLpuStructureFrame.rootVisible == false)
				{
					if (node.hasChildNodes() == true)
					{
						node = node.findChild('object', 'Lpu');						
						swLpuStructureFrame.fireEvent('click', node);
					}
				}
			}
		});		

		function TreeBeforeLoad(TreeLoader, node)
		{
			if (node.getDepth()==0)
			{
				TreeLoader.baseParams.object = 'Lpu';
			}
			else
			{
				TreeLoader.baseParams.object = node.attributes.object;
				TreeLoader.baseParams.object_id = node.attributes.object_value;
			}
			if (node.attributes.object=='LpuUnitType')
				TreeLoader.baseParams.LpuUnitType_id = node.attributes.LpuUnitType_id;
			else
				TreeLoader.baseParams.LpuUnitType_id = 0;

			//node.getOwnerTree().fireEvent('click', node);
		}

		function LoadOnChangeTab(node)
		{	
							
		}

		function LpuStructureTreeClick(node, e)
		{
			
		}


		var month_store = [
			[1, lang['yanvar']],
			[2, lang['fevral']],
			[3, lang['mart']],
			[4, lang['aprel']],
			[5, lang['may']],
			[6, lang['iyun']],
			[7, lang['iyul']],
			[8, lang['avgust']],
			[9, lang['sentyabr']],
			[10, lang['oktyabr']],
			[11, lang['noyabr']],
			[12, lang['dekabr']]
		];
		
		year_store = [];
		
		for ( var i = 2007; i <= String(getGlobalOptions().date).substr(6, 4); i++ )
			year_store.push([i, String(i)]);
		this.monthCombo = new Ext.form.ComboBox({
			allowBlank: false,
			fieldLabel: lang['mesyats'],
			width: 150,
			triggerAction: 'all',
			store: month_store,
			listeners: {
				'select': function() {
					this.checkIfReestrDataExists();
				}.createDelegate(this)
			}
		});
		
		this.yearCombo = new Ext.form.ComboBox({
			allowBlank: false,
			fieldLabel: lang['god'],
			triggerAction: 'all',
			width: 60,
			store: year_store,
			listeners: {
				'change': function() {
					this.filterMonthCombo();
				}.createDelegate(this)
			}
		});
		
		function draw_fin_chart(values) {
			var d = Array();
			var plan_num = values[0];
			var plan_array = Array();
			for ( i in values )
			{
				if ( i >= 1 )
				{
					d.push(Array(i, values[i]));
					plan_array.push(Array(i, plan_num));
				}
			}			
			$.plot($("#placeholder"), [ {				
				label: "суммарная стоимость оказанных медицинских услуг по законченным случаям",
				data: d,
				lines: { show: true },
				points: { show: true }				
			}, {
				label: "плановый объем финансовых средств",
				data: plan_array,
				lines: { show: true },
				points: { show: true }				
			} ], {
				 xaxis: {
					ticks: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31],
					tickDecimals: 0
				},
				legend: { show: true, container: $("#chart_legend") }
			} );
		};
		
		function draw_year_fin_chart(values) {
			var d1 = [];
			for (var i = 0; i <= 10; i += 1)				
				d1.push([i, parseInt(Math.random() * 30 - 15)]);

			var stack = 0, bars = true, lines = false, steps = false;
			
			$.plot($("#yearplaceholder"), [ d1 ], {
				series: {
					stack: stack,
					lines: { show: lines, fill: true, steps: steps },
					bars: { show: bars, barWidth: 0.6 }
				}
			});
		};
		
		this.FinStatePanel = new Ext.Panel({
			frame: true,
			region: 'center',
			title: lang['finansovoe_sostoyanie'],
			items: [
				new Ext.form.FieldSet({
					title: lang['rabochiy_period'],
					height: 100,
					items: [{
						autoHeight: true,
						border: false,
						layout: 'column',						
						items: [{
							autoHeight: true,
							border: false,
							labelWidth: 25,
							layout: 'form',
							items: [
								this.yearCombo
							]
						}, {
							autoHeight: true,
							border: false,
							labelWidth: 40,
							style: 'padding-left: 5px;',
							layout: 'form',
							items: [
								this.monthCombo
							]
						}, {
							autoHeight: true,
							border: false,
							labelWidth: 40,
							style: 'padding-left: 5px;',
							layout: 'form',
							items: [
								new Ext.Button({
									text: lang['raschitat'],
									handler: function() {
										this.recalcTotals();
									}.createDelegate(this)
								}),
								new Ext.Button({
									text: lang['grafik_finansovogo_rezultata'],
									id: 'FHVF_ShowChartButton',
									handler: function() {
										this.getChartData(function(data) {
											new Ext.Window({
												modal: true,
												title: lang['grafik_finansovogo_rezultata'],
												height: 750,
												width: 1100,
												items: [
													new Ext.Panel({
														html: '<div id="placeholder" style="padding: 15px; width:1050px; height:650px"></div>'
													}),
													new Ext.Panel({
														html: '<div id="chart_legend" style="size: 18pt; padding: 15px; width:1050px; height:50px"></div>'
													})
												]
											}).show();
											draw_fin_chart(data);
										});										
									}.createDelegate(this)
								}),
								new Ext.Button({
									text: lang['godovoy_grafik_finansovogo_rezultata'],
									hidden: true,
									handler: function() {
										new Ext.Window({
											modal: true,
											title: lang['godovoy_grafik_finansovogo_rezultata'],
											height: 700,
											width: 850,
											items: [
												new Ext.Panel({
													html: '<div id="yearplaceholder" style="padding: 15px;width:800px;height:600px"></div>'
												})
											]
										}).show();
										draw_year_fin_chart();
									}
								})
							]
						}]
					}]
				}), new Ext.form.FieldSet({
					title: lang['svodka'],
					id: 'FHVF_TotalsFieldset',
					style: 'margin-top: 0;',
					height: 190,
					html: '<div id="fundholding_totals">Расчет не произведен...<br/>Выберите необходимый расчетный период и нажмите кнопку "Рассчитать".</div>'
				})
			]
		});
		
		this.EventsGrid = new sw.Promed.ViewFrame(
		{
			title: lang['sluchai_lecheniya'],
			id: 'FHVF_EventsGrid',
			object: 'Events',
			region: 'center',
			editformclassname: null,
			dataUrl: '/?c=FundHolding&m=getFundHoldingGrid',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			onCellDblClick: function() {
				Ext.getCmp('swFundHoldingViewWindow').showEvnViewWindow();
			},
			height: 303,
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'Evn_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnClass_id',  type: 'int', hidden: true},
				{name: 'Person_id',  type: 'int', hidden: true},
				{name: 'Server_id',  type: 'int', hidden: true},
				{name: 'Person_SurName',  type: 'string', header: lang['familiya'], minWidth: 100, width: 100},
				{name: 'Person_FirName',  type: 'string', header: lang['imya'], width: 100},
				{name: 'Person_SecName',  type: 'string', header: lang['otchestvo'], width: 100},
				{name: 'Evn_begDate',  type: 'date', header: lang['nachalo_lecheniya'], width: 100},
				{name: 'Evn_endDate',  type: 'date', header: lang['okonchanie_lecheniya'], width: 110},
				{name: 'Lpu_Nick',  type: 'string', header: lang['lpu'], width: 100},
				{name: 'LpuSection_Name',  type: 'string', header: lang['profil_otdelenie'], width: 180},
				{name: 'KoikoDni',  type: 'int', header: lang['koykodney_posescheniy'], width: 120},
				{name: 'Diag_Name',  type: 'string', header: lang['diagnoz'], width: 100},
				{name: 'PlanSum',  type: 'int', header: lang['planovaya_stoimost'], width: 120},
				{name: 'FactSum',  type: 'int', header: lang['fakticheskaya_stoimost'], width: 130}
			],
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', disabled: true},
				{name:'action_view', handler: function() { Ext.getCmp('swFundHoldingViewWindow').showEvnViewWindow(); }},
				{name:'action_delete', disabled: true},
				{name:'action_refresh', disabled: true},
				{name:'action_print', disabled: true}
			]
		});

		Ext.apply(this, {
				xtype: 'panel',
				//layout:'border',
				items: [
				swLpuStructureFrame,
				new Ext.Panel({					
					region: 'center',
					layout: 'border',
					items: [
						new Ext.Panel({
							region: 'north',
							layout: 'border',
							height: 260,
							items: [
								this.FinStatePanel,
								new Ext.form.FormPanel({
									frame: true,
									region: 'east',
									width: 350,
									id: 'FundHolding_FilterPanel',
									title: lang['filtryi'],
									labelWidth: 100,
									layout: 'form',
									items: [
										new Ext.form.ComboBox({
											hideEmptyRow: true,
											allowBlank: true,
											fieldLabel: lang['tip_sluchaya'],
											hiddenName: 'EvnType',
											width: 230,
											triggerAction: 'all',
											value: -1,
											store: [
												[-1, lang['znachenie_ne_vyibrano']],
												[1, lang['ambulatorno-poliklinicheskaya_pomosch']],
												[2, lang['statsionarnaya_pomosch']],
												[3, lang['statsionarno-zameschayuschaya_pomosch']]
											]
										}),
										new Ext.form.ComboBox({
											hideEmptyRow: true,
											allowBlank: true,
											hiddenName: 'FundHolder',
											fieldLabel: lang['ispolnitel_lecheniya'],
											width: 230,
											triggerAction: 'all',
											value: -1,
											store: [
												[-1, lang['znachenie_ne_vyibrano']],
												[1, lang['fondoderjatel']],
												[2, lang['vneshniy_ispolnitel']]
											]
										}),
										new Ext.form.ComboBox({
											hideEmptyRow: true,
											allowBlank: true,
											id: 'FHVF_EvnIsFinish_Combo',
											hiddenName: 'EvnIsFinish',
											fieldLabel: lang['zakonchennyiy_sluchay'],
											width: 230,
											triggerAction: 'all',
											value: -1,
											store: [
												[-1, lang['znachenie_ne_vyibrano']],
												[1, lang['net']],
												[2, lang['da']]
											]
										}), {
											fieldLabel: lang['familiya'],
											name: 'Person_Surname',
											width: 230,
											xtype: 'textfieldpmw'
										}, {
											fieldLabel: lang['imya'],
											name: 'Person_Firname',
											width: 230,
											xtype: 'textfieldpmw'
										}, {
											fieldLabel: lang['otchestvo'],
											name: 'Person_Secname',
											width: 230,
											xtype: 'textfieldpmw'
										},
										new Ext.Button({
											text: lang['vyivesti_spisok'],
											handler: function() {
												this.recalcTotals(true);
											}.createDelegate(this)
										})
									]
								})
							]
						}),
						this.EventsGrid
					]
				})]
		});
		sw.Promed.swFundHoldingViewForm.superclass.initComponent.apply(this, arguments);
	}
});