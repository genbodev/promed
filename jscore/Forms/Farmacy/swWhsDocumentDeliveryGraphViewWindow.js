/**
* swWhsDocumentDeliveryGraphViewWindow - окно редактирования графика поставок
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      09.2012
* @comment      
*/
sw.Promed.swWhsDocumentDeliveryGraphViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['grafik_postavok_prosmotr'],
	layout: 'border',
	id: 'WhsDocumentDeliveryGraphViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	graph_data: null,
	spec_data: null,	
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	dateArray: [],
	setSpecData: function(data) {
		var wnd = this;
		wnd.spec_data = new Object();
		for (var i = 0; i < data.length; i++) {
			wnd.spec_data[data[i].grid_id] = {
				grid_id: data[i].grid_id,
				spec_id: data[i].WhsDocumentSupplySpec_id,
				name: data[i].Drug_Name,
				total: data[i].WhsDocumentSupplySpec_KolvoUnit
			}
		}
		wnd.spec_data.all = {
			grid_id: null,
			spec_id: null,
			name: lang['vse'],
			total: '100'
		};
	},
	setDeliveryData: function(data) { //функция для инициализации данных графика поставки
		var wnd = this;
		if (data && data.WhsDocumentDelivery_Data && data.WhsDocumentDelivery_Data.length > 0) {
			wnd.graph_data = new Object();				
			wnd.graph_data.data = new Ext.util.MixedCollection(true);
			wnd.graph_data.data.addAll(data.WhsDocumentDelivery_Data);
			wnd.graph_data.type =  (data.WhsDocumentDelivery_Data[0].WhsDocumentSupplySpec_id > 0) ? 'unit' : 'percent';
			wnd.graph_data.state = 'saved';
			
			//дописываем дополнительную информацию
			wnd.graph_data.data.each(function (item) {
				item.state = 'saved';
				item.delivery_id = item.WhsDocumentDelivery_id;
				item.grid_id = item.WhsDocumentSupplySpec_id;
			});
		} else
			wnd.graph_data = null;
	},
	setData: function() {	
		var wnd = this;
		wnd.dateArray = new Array();
		if (wnd.graph_data && wnd.graph_data.data && wnd.graph_data.data.getCount() > 0) {
			//получаем список дат
			wnd.dateArray = new Array();
			var dt_arr = new Array();
			var i = 0;
			wnd.graph_data.data.each(function(item){
				var dt_str = item.WhsDocumentDelivery_setDT;
				if (dt_arr.indexOf(dt_str) < 0 && dt_str) {
					dt_arr.push(dt_str);
					wnd.dateArray.push({name: 'Field'+(i++) , value: dt_str});
				}
			});
			
			wnd.graph_data.data.each(function(item){
				item.combo_name = null;
			});			
			var v_arr = wnd.dateArray;
			for(var i = 0; i < v_arr.length; i++) {
				wnd.graph_data.data.filter('WhsDocumentDelivery_setDT', v_arr[i].value).each(function(item){
					item.combo_name = v_arr[i].name;
				});
			}
		}		
		wnd.refreshGrid();
	},
	getSpecData: function(grid_id, property) {
		var wnd = this;
		var res = null;
		if (wnd.spec_data && property) {
			if (grid_id) {
				res = wnd.spec_data[grid_id][property];
			} else {
				res = wnd.spec_data['all'][property];
			}
		}
		return res;
	},	
	getStoreData: function() {
		var wnd = this;
		var result = new Array();
		var grid_id_arr = new Array();
		if (wnd.graph_data.type == 'unit') {
			for(var i in wnd.spec_data) {
				if (wnd.spec_data[i].grid_id > 0)
					grid_id_arr.push(wnd.spec_data[i].grid_id);
			}
		} else {
			grid_id_arr.push(null);
		}
		for(var i = 0; i < grid_id_arr.length; i++) {
			var obj = new Object();
			obj.grid_id = grid_id_arr[i];
			wnd.graph_data.data.filter('grid_id', new RegExp('^'+grid_id_arr[i]+'$', "i")).each(function(item) {				
				obj[item.combo_name] = item.state != 'delete' ? item.WhsDocumentDelivery_Kolvo : '';
			});
			result.push(obj);
		};
		return result;
	},
	refreshGrid: function(mode) {
		var wnd = this;
		var grid = wnd.GraphGrid;

		var con = new Array();
		var con_start = [
			{name: 'grid_id', type: 'int', header: 'ID', hidden: true},
			{name: 'delivery_id', type: 'int', header: 'delivery_id', hidden: true},
			{name: 'spec_name', type: 'int', header:lang['torgovoe_naimenovanie'], width: 250},
			{name: 'number', type: 'int', header:lang['№_p_p'], width: 60},
			{name: 'total',  type: 'string', header:lang['po_dog'], width: 80},
			{name: 'unit', type: 'string', header:lang['ed_izm'], width: 80}
		];
		var con_end = [{
			name: 'check',
			header:lang['kontrol'],
			renderer: function(v, p, record) {
				if(!v){
					v = 'false';
				}
				if (!p) {
					if (v == 'true' || String(v) == '1')
						return lang['da']
					else	
						return lang['net']
				}
				p.css += ' x-grid3-check-col-td';
				if (v == 'gray')
					var style = 'x-grid3-check-col-on-non-border-gray';
				else
					var style = 'x-grid3-check-col'+((String(v)=='true' || String(v)=='1')?'-non-border-on':'-on-non-border-yellow');
				return '<div class="'+style+' x-grid3-cc-'+this.id+'">&#160;</div>';
			},
			width: 80
		}];
			
		var v_arr = wnd.dateArray;
		con = con_start;
		for	(var i = 0; i < v_arr.length; i++) {
			var column = new Object();
			column.header = v_arr[i].value != '' ?  v_arr[i].value : '';
			column.dataIndex = v_arr[i].name;
			column.name = v_arr[i].name;
			column.width = 120;
			con.push(column);
		}
		con = con.concat(con_end);
		
		var st_arr = new Array();
		for (var i = 0; i < con.length; i++) {			
			if(con[i].name)
				st_arr.push(con[i].name);
		}
		
		var cm = new Ext.grid.ColumnModel(con);
		var store = null;
		
		if (mode && mode == 'only_column') {
			var store = grid.getStore();
		} else {
			var data = wnd.getStoreData();
			for(var i = 0; i < data.length; i++) {
				data[i].number = i+1;
				data[i].unit = wnd.graph_data.type == 'percent' ? '%' : lang['up'];
				data[i].spec_name = wnd.getSpecData(data[i].grid_id, 'name');
				data[i].total = wnd.getSpecData(data[i].grid_id, 'total');
				data[i].check = false;
			}
			var store = new Ext.data.JsonStore({
				id: 0,
				fields: st_arr,
				data: data
			});
		}
		
		grid.reconfigure(store, cm);
		wnd.doCheckRow();
	},
	doCheckRow: function(grid_id) {
		var wnd = this;
		wnd.GraphGrid.getStore().each(function(r){
			if(!grid_id || r.get('grid_id') == grid_id) {
				var sum = 0;
				wnd.graph_data.data.filter('grid_id', new RegExp('^'+r.get('grid_id')+'$', "i")).each(function(item){
					if (item.WhsDocumentDelivery_Kolvo > 0 && item.state != 'delete')						
						sum += item.WhsDocumentDelivery_Kolvo*1;
				});
				var check = (sum == r.get('total'));
				r.set('check', check);
				r.commit();
			}
		});
	},
	show: function() {
        var wnd = this;
		sw.Promed.swWhsDocumentDeliveryGraphViewWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.onSave = Ext.emptyFn;
		this.WhsDocumentSupply_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].WhsDocumentSupply_id ) {
			this.WhsDocumentSupply_id = arguments[0].WhsDocumentSupply_id;
		}
		
		this.graph_data = new Object();
		this.spec_data = new Object();

		if (wnd.WhsDocumentSupply_id > 0) {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg:lang['zagruzka']});
			//loadMask.show();
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					loadMask.hide();
					wnd.hide();
				},
				params:{
					WhsDocumentSupply_id: wnd.WhsDocumentSupply_id
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					var supply_data = result[0];
					Ext.Ajax.request({
						url: '/?c=WhsDocumentSupplySpec&m=loadList',
						params: {
							WhsDocumentSupply_id: wnd.WhsDocumentSupply_id
						},
						success: function(response){
							var result = Ext.util.JSON.decode(response.responseText);
							if (!result) { return false; }
							wnd.setSpecData(result);
							wnd.setDeliveryData(supply_data);
							wnd.setData();
							loadMask.hide();
						}
					});
				},
				url:'/?c=WhsDocumentSupply&m=load'
			});
		}
	},
	initComponent: function() {
		var wnd = this;		
			
		this.GraphGrid = new Ext.grid.GridPanel({
			region: 'center',
			height: 250,
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true
			}),
			colModel: new Ext.grid.ColumnModel({
				columns: [
					{name: 'grid_id', type: 'int', header: 'ID'}
				] 
			}),
			store: new Ext.data.Store({
				reader: new Ext.data.ArrayReader({
					idIndex: 0
				}, [
					{mapping: 0, name: 'grid_id'}
				])
			}),
			view: new Ext.grid.GridView({
				forceFit: false
			}),
			title: null,
			toolbar: false
		});		
		
		Ext.apply(this, {
			autoScroll: true,
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.GraphGrid]
		});
		sw.Promed.swWhsDocumentDeliveryGraphViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});