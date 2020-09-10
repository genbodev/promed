/**
* swWhsDocumentDeliveryGraphEditWindow - окно редактирования графика поставок
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
sw.Promed.swWhsDocumentDeliveryGraphEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['grafik_postavok'],
	layout: 'border',
	id: 'WhsDocumentDeliveryGraphEditWindow',
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
	onSave: Ext.emptyFn,
	doSave:  function() {
		var err = this.checkData();
		if (err == '') {
			this.onSave(this.graph_data);
			this.hide();
			return true;
		} else {
			sw.swMsg.alert(lang['oshibka'], err);
			return false;
		}
	},
	checkData: function() {//проверки перед сохранением
		var wnd = this;
		
		var v_arr = wnd.DatePanel.getData();
		var d_arr = new Array();
		
		for(var i = 0; i < v_arr.length; i++) {
			if (v_arr[i].value != '') {
				if (d_arr.indexOf(v_arr[i].value.format('d.m.Y')) >= 0) {
					return lang['grafik_ne_doljen_soderjat_povtoryayuschihsya_dat'];
				}
				d_arr.push(v_arr[i].value.format('d.m.Y'));
			}
		}
		if (wnd.graph_data.data.filter('state', new RegExp('add|edit|saved', "i")).filter('WhsDocumentDelivery_setDT', new RegExp('^null$', "i")).getCount() > 0)
			return lang['grafik_soderjit_znacheniya_dlya_kotoryih_ne_ustanovlena_data'];
		
		return '';
	},
	setData: function() {
		var wnd = this;
		if (wnd.graph_data && wnd.graph_data.data && wnd.graph_data.data.getCount() > 0) {
			//получаем список дат
			var d_arr = new Array();
			var dt_arr = new Array();
			wnd.graph_data.data.each(function(item){
				var dt_str = item.WhsDocumentDelivery_setDT;
				if (item.state != 'delete' && dt_arr.indexOf(dt_str) < 0 && dt_str) {
					dt_arr.push(dt_str);
					d_arr.push(Date.parseDate(dt_str, 'd.m.Y'));
				}
			});
			wnd.DatePanel.setData(d_arr);
						
			//связываем данные, и поля для выбора дат			
			wnd.graph_data.data.each(function(item){
				item.combo_name = null;
			});			
			var v_arr = wnd.DatePanel.getData();
			for(var i = 0; i < v_arr.length; i++) {
				wnd.graph_data.data.filter('WhsDocumentDelivery_setDT', v_arr[i].value.format('d.m.Y')).each(function(item){
					item.combo_name = v_arr[i].name;
				});
			}
		} else {
			wnd.DatePanel.reset();
		}

		//установка значения по умолчанию
		if (wnd.set_default_value) {
			wnd.updateGraphData(null, 'Field0', 100); //100 - значение по умолчанию для графика в долях
			wnd.set_default_value = false;
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
	updateGraphData: function(grid_id, combo_name, value) { //установка значения (value) для определенного медикамента(spec_id - ид позиции спецификации) за определенную дату(combo_name - имя DateField)
		//alert('updateGraphData('+grid_id+' | '+combo_name+' | '+value+')');
		var wnd = this;
		var do_delete = (!value || value <= 0);
		if (!combo_name || combo_name == '')
			return;
			
			
		if (grid_id) {
			grid_id = grid_id+'';
		}
		var collection = wnd.graph_data.data.filter('grid_id', new RegExp('^'+grid_id+'$', "i")).filter('combo_name', new RegExp('^'+combo_name+'$', "i"));
		//swalert(collection);
		if (collection && collection.getCount() > 0) {
			collection.each(function(item){
				if (do_delete) {
					if (item.state == 'add') {
						wnd.graph_data.data.remove(item);
					} else {
						item.state = 'delete';
					}
				} else {
					item.WhsDocumentDelivery_Kolvo = value;
					if (item.state != 'add')
						item.state = 'edit';
				}
			})
		} else if (!do_delete) {
			var date = null;
			var dates = wnd.DatePanel.getData('object');
				date = dates[combo_name];
			
			wnd.graph_data.data.add({			
				grid_id: grid_id,
				//WhsDocumentSupplySpec_id: spec_id,
				WhsDocumentDelivery_setDT: date ? date.format('d.m.Y') : null,
				WhsDocumentDelivery_Kolvo: value,
				combo_name: combo_name,
				state: 'add'
			});
		}
	},
	updateColumnDate: function(combo_name, new_date) { //смена даты у всех ячеек в столбце
		var wnd = this;
		if (!combo_name || combo_name == '')
			return;
		
		wnd.graph_data.data.filter('combo_name', new RegExp('^'+combo_name+'$', "i")).each(function(item){			
			item.WhsDocumentDelivery_setDT = new_date;
			if (item.state != 'add')
				item.state = 'edit';
		});
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
			{name: 'grid_id', dataIndex: 'grid_id', type: 'int', header: 'ID', hidden: true},
			{name: 'delivery_id', dataIndex: 'delivery_id', type: 'int', header: 'delivery_id', hidden: true},
			{name: 'spec_name', dataIndex: 'spec_name', type: 'int', header:lang['torgovoe_naimenovanie'], width: 250},
			{name: 'number', dataIndex: 'number', type: 'int', header:lang['№_p_p'], width: 60},
			{name: 'total',  dataIndex: 'total', type: 'string', header:lang['po_dog'], width: 80},
			{name: 'unit', dataIndex: 'unit', type: 'string', header:lang['ed_izm'], width: 80}
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
			
		var v_arr = wnd.DatePanel.getData();
		con = con_start;
		for	(var i = 0; i < v_arr.length; i++) {
			var column = new Object();
			column.header = v_arr[i].value != '' ?  v_arr[i].value.format('d.m.Y') : '';
			column.dataIndex = v_arr[i].name;
			column.name = v_arr[i].name;
			column.width = 120;
			column.editable = true;
			column.editor = new Ext.form.NumberField({ allowNegative:false });
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
		sw.Promed.swWhsDocumentDeliveryGraphEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.onSave = Ext.emptyFn;
		this.WhsDocumentDeliveryGraph_id = null;
		this.set_default_value = false;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].WhsDocumentDeliveryGraph_id ) {
			this.WhsDocumentDeliveryGraph_id = arguments[0].WhsDocumentDeliveryGraph_id;
		}
		if ( arguments[0].set_default_value ) {
			this.set_default_value = arguments[0].set_default_value;
		}
		if ( arguments[0].spec_data ) {
			this.spec_data = arguments[0].spec_data;
		} else {
			this.spec_data = null;
		}	
		this.graph_data = new Object();
		if ( arguments[0].graph_data ) {
			this.graph_data.data = new Ext.util.MixedCollection(true);	
			if (arguments[0].graph_data.data) {					
				//копируем обьекты из входящей коллекции в свою
				arguments[0].graph_data.data.each(function(item){
					var obj = new Object();
					Ext.apply(obj, item);
					wnd.graph_data.data.add(obj);
				});
			}
			if (arguments[0].graph_data.type) this.graph_data.type = arguments[0].graph_data.type;
			if (arguments[0].graph_data.state) this.graph_data.state = arguments[0].graph_data.state;
		} else {
			this.graph_data = null;
		}
		
        var loadMask = new Ext.LoadMask(this.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				wnd.setData();
				loadMask.hide();
			break;	
		}
	},
	schedulePrint:function(action)
	{
		var grid = this.GraphGrid,
            params = {},
            record = grid.getSelectionModel().getSelected();

		if (!record && action == 'row') {
            sw.swMsg.alert(lang['oshibka'], lang['zapis_ne_vyibrana']);
            return false;
        }

        params.notPrintEmptyRows = true;

        if (action && action == 'row') {
            params.rowId = record.id;
            Ext.ux.GridPrinter.print(grid, params);
        } else {
            Ext.ux.GridPrinter.print(grid, params);
        }
	},
	initComponent: function() {
		var wnd = this;		
		this.DatePanel = new sw.Promed.swMultiFieldPanel({
			label: lang['data_postavki'],
			createField: function() {
				var field = new Ext.form.DateField({
					listeners: {
						'change': function(field, newValue, oldValue) {
							wnd.refreshGrid('only_column');
							var date_str = newValue ? newValue.format(('d.m.Y')) : null;
							wnd.updateColumnDate(field.name, date_str);
						}
					}
				});
				return field;
			},
			onFieldAdd: function(data) {
				wnd.refreshGrid();
			},
			onFieldDelete: function(data) {
				wnd.graph_data.data.filter('combo_name', new RegExp('^'+data.name+'$', "i")).each(function(item) {
					if (item.state == 'add') {
						wnd.graph_data.data.remove(item);
					} else if (item.state != 'delete') {
						item.state = 'delete';
						item.combo_name += '_del';
					}
				});
				wnd.refreshGrid();
			},
			onResetPanel: function() {
				wnd.graph_data.data.each(function(item) {					
					if (item.state == 'add') {
						wnd.graph_data.data.remove(item);
					} else if (item.state != 'delete') {
						item.state = 'delete';
						item.combo_name += '_del';
					}
				});	
				wnd.refreshGrid();
			}
		});

        this.gridToolbar = new Ext.Toolbar(
		{
			id: 'wddgewToolbar',
			items:
			[
				new Ext.Action({name:'print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'x-btn-text', icon: 'img/icons/print16.png', menu: [
				new Ext.Action({name:'print_rec', text:lang['pechat'], handler: function() {this.schedulePrint('row')}.createDelegate(this)}),
				new Ext.Action({name:'print_all', text:lang['pechat_spiska'], handler: function() {this.schedulePrint()}.createDelegate(this)})
            ]})

			]
		});
			
		this.GraphGrid = new Ext.grid.EditorGridPanel({
			height: 250,
			enableColumnHide: false,
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true
			}),
			colModel: new Ext.grid.ColumnModel({
				columns: [
					{name: 'grid_id', type: 'int', key: true, dataIndex:'',header: 'ID'}
				] 
			}),
			tbar: this.gridToolbar,
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
			listeners: {
				'afteredit': function(e) {
					wnd.updateGraphData(e.record.get('grid_id'), e.field, e.value);
					wnd.doCheckRow(e.record.get('grid_id'));
				}
			},
			title: lang['kolichestvo_postavlyaemyih_medikamentov'],
			toolbar: false
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 170,
			border: false,			
			frame: true,
			region: 'south',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'WhsDocumentDeliveryGraphEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 200,
				collapsible: true,
				url:'/?c=WhsDocumentDeliveryGraph&m=save',
				items: [{
					style: "padding-left: 0px",
					xtype: 'button',
					id: 'wdseBtnDocAdd',
					text: lang['dobavit'],
					iconCls: 'add16',
					handler: function() {						
						wnd.refreshGrid();
					}
				}, {
					style: "padding-left: 0px",
					xtype: 'button',
					id: 'wdseBtnDocAdd0',
					text: 'only_column',
					iconCls: 'add16',
					handler: function() {
						wnd.refreshGrid('only_column');
					}
				}, {
					style: "padding-left: 0px",
					xtype: 'button',
					id: 'wdseBtnDocAdd1',
					text: lang['dannyie'],
					iconCls: 'view16',
					handler: function() {
						swalert(wnd.graph_data.data.items);
					}
				}, {
					style: "padding-left: 0px",
					xtype: 'button',
					id: 'wdseBtnDocAdd2',
					text: 'Check',
					iconCls: 'view16',
					handler: function() {
						wnd.doCheckRow();						
					}
				}]
			}]
		});
		Ext.apply(this, {
			autoScroll: true,
			layout: 'form',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
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
			items:[this.DatePanel,this.GraphGrid/*,form*/]
		});
		sw.Promed.swWhsDocumentDeliveryGraphEditWindow.superclass.initComponent.apply(this, arguments);
		//this.form = this.findById('WhsDocumentDeliveryGraphEditForm').getForm();
	}	
});