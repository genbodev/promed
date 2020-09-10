/**
 * sw.Promed.RateGrid. Класс  таблицы с показателями
 *
 * @author		Salakhov Rustam
 * @class		sw.Promed.RateGrid
 * @extends		Ext.grid.PropertyGrid
 */
sw.Promed.RateGrid = function(config)
{
	Ext.apply(this, config);
	sw.Promed.RateGrid.superclass.constructor.call(this);
};

Ext.override(Ext.grid.PropertyColumnModel, {
	renderCell : function(val, meta, r){
		var renderer = this.grid.customRenderers[r.get('name')];
		if(renderer){
			return renderer.apply(this, arguments);
		}
		var rv = val;
		if(Ext.isDate(val)){
			rv = this.renderDate(val);
		} else if(typeof val == 'boolean'){
			rv = this.renderBool(val);
		}
		return Ext.util.Format.htmlEncode(rv);
	}
});

Ext.override(Ext.grid.PropertyGrid, {
	initComponent : function(){
		this.customRenderers = this.customRenderers || {};
		this.customEditors = this.customEditors || {};
		this.lastEditRow = null;
		var store = new Ext.grid.PropertyStore(this);
		this.propStore = store;
		var cm = new Ext.grid.PropertyColumnModel(this, store);
		store.store.sort('name', 'ASC');
		this.addEvents(
			'beforepropertychange',
			'propertychange'
		);
		this.cm = cm;
		this.ds = store.store;
		Ext.grid.PropertyGrid.superclass.initComponent.call(this);
		this.mon(this.selModel, 'beforecellselect', function(sm, rowIndex, colIndex){
			if(colIndex === 0){
				this.startEditing.defer(200, this, [rowIndex, 1]);
				return false;
			}
		}, this);
	}
});

Ext.extend(sw.Promed.RateGrid, Ext.grid.PropertyGrid, {
	id: 'rategrid',
	title: langs('Показатели'),
	border: true,
	dataUrl: '/?c=Rate&m=loadRateListGrid',
	rateType: '',
	columns: [
		{header: langs('Показатель')},
		{header: langs('Значение')},
		{header: langs('скрытое поле')}
	],
	source: {},
	dataSetNumber: 0,
	gridCopyData: new Object(),
	loadData: function(prms) {
		var gr = this;		
		//var loadMask = new Ext.LoadMask(this.bwrap, { msg: "Подождите, идет загрузка данных..." });
		//loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				//loadMask.hide();
				gr.setSource({}); //инициализируем source
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					var data = response_obj.data;					
					for(var i = 0; i < data.length; i++) {
						data[i].state = '';
						gr.addRow(data[i]);
					}					
				} else {
					sw.swMsg.alert(langs('Ошибка'), langs('При загрузке данных произошли ошибки'));
				}				
			},
			params: prms,
			url: this.dataUrl
		});
		this.dataSetNumber = this.generateDataSetNumber(); //генерируем уникальный номер для загруженных данных
	},
	initComponent: function() {
		var gr = this;		
		
		this.tbar = new Ext.Toolbar({
			items: [{
				text : BTN_GRIDADD,
				iconCls : 'add16',				
				handler: function() {
					var rate_names = new Array();
					gr.getStore().each(function(row) {
						rate_names.push(row.data.name);
					});
				
					getWnd('swAddPropertyWindow').show({
						onSelect: function(params) {
							gr.addRow(params);
						},
						params: { rate_names: rate_names }
					});
				}
			}
			/*,	{
				text : BTN_GRIDDEL,
				iconCls : 'delete16',				
				handler: function() {
					gr.deleteRow();
				}
				
			}*/]
		});
		
		sw.Promed.RateGrid.superclass.initComponent.apply(this, arguments);
	},
	clear: function() {
		this.setSource({});
	},
	listeners: {
		propertychange: function(source, id, v, oldv) {
			if (this.getStore().getById(id).data.state != 'add')
				this.getStore().getById(id).data.state = 'edit';
		}
	},
	generateDataSetNumber: function() { //генерация уникального номера для датасета
		return (new Date())*1;
	},
	addRow: function(params) {
		var gr = this;
		
		var ds_model = Ext.data.Record.create([
			'id',
			'type',
			'name',
			'value',
			'state'
		]);
		
		//set editor
		if (!params.editor) {
			switch(params.type) {
				case 'int':										
					params.editor = new Ext.form.TextField({
						maskRe: /[0-9]/
					});
					break;
				case 'float':									
					params.editor = new Ext.form.TextField({
						maskRe: /[0-9\.]/
					});
					break;
				case 'reference':
					params.editor = new Ext.form.ComboBox({															
						typeAhead: true,
						triggerAction: 'all',
						lazyRender: true,
						readOnly: true,
						mode: 'local',
						store: new Ext.data.Store({
							autoLoad: true,
							reader: new Ext.data.JsonReader({
								id: 'value_id'
							}, [
								{ name: 'value_id', mapping: 'value_id' },									
								{ name: 'value_name', mapping: 'value_name' }
							]),
							sortInfo: {
								field: 'value_name'
							},
							url: '/?c=Rate&m=autoLoadRateValueList&ratetype_id=' + params.id
						}),
						valueField: 'value_id',
						displayField: 'value_name'
					});						
					break;
				case 'string':
					params.editor = new Ext.form.TextField({
					});
					break;
				default:
					params.editor = new Ext.form.TextField({
					});
			}
		}
		gr.customEditors[params.name] = new Ext.grid.GridEditor(params.editor);
		
		//set renderer		
		if (params.refdata)
			gr.customRenderers[params.name] = function(val) { return params.refdata[val]; };							
		
		gr.getStore().insert(
			0,
			new ds_model({
				id: params.id,
				type: params.type,
				name: params.name,
				value: params.value,
				state: params.state ? params.state : ""
			})
		);
		
		//this.startEditing(0,1);
	},
	/*deleteRow: function(params) {
		var grid = this;

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('AttachmentDemand_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		//var demand_id = record.get('AttachmentDemand_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								grid.getStore().remove(record);
								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							} else {
								sw.swMsg.alert(langs('Ошибка'), langs('При удалении заявления возникли ошибки'));
							}
						},
						params: {
							AttachmentDemand_id: demand_id
						},
						url: '/?c=Demand&m=deleteAttachmentDemand'
					});
					swalert(record);
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить заявление?'),
			title: langs('Вопрос')
		});
	}, */
	
	getCurrentRateCount: function(){ //возвращает текущее количество введеных показателей
		var cnt = 0;
		this.getStore().each(function(record) {
			if (record.data.value != '') cnt++;
		});
		return cnt;
	},
	
	getChangedData: function(){ //возвращает новые и измненные показатели
		var data = new Array();
		this.getStore().each(function(record) {
			if ((record.data.state == 'add' || record.data.state == 'edit') && record.data.value != '')
				data.push(record.data);
		});
		return data;
	},
	
	getJSONChangedData: function(){ //возвращает новые и измненные показатели в виде закодированной JSON строки
		var dataObj = this.getChangedData();
		var jsonObj = new Object();
		for(var i = 0; i < dataObj.length; i++) {
			jsonObj[dataObj[i].id] = dataObj[i].value;
		}
		return dataObj.length > 0 ? Ext.util.JSON.encode(jsonObj) : "";
	},
	
	saveGridCopy: function(num) { //сохранение копий текущих store, customEditors и customRenderers за определенным номером
		if (num <= 0) num = this.dataSetNumber;
		//alert("save dataset #" + num);
		this.gridCopyData[num] = new Object();
		var storearray = new Array();
		this.getStore().each(function(r) {
			storearray.push(r.data);
		});
		storearray.reverse();
		this.gridCopyData[num].store = storearray;
	},
	
	restoreGridCopy: function(num) {  //востановление store, customEditors и customRenderers по определенному номеру
		if (!this.gridCopyData[num]) return false;
		//alert("restore dataset #" + num);
		var gr = this;		
		var ds_model = Ext.data.Record.create([
			'id',
			'type',
			'name',
			'value',
			'state'
		]);

		this.clear();
		for(var i = 0; i < this.gridCopyData[num].store.length; i++) {
			var params = this.gridCopyData[num].store[i];
			gr.getStore().insert(
				0,
				new ds_model({
					id: params.id,
					type: params.type,
					name: params.name,
					value: params.value,
					state: params.state ? params.state : ""
				})
			);			
		};
		this.dataSetNumber = num;
	},
	
	getSavedDataSetNumber: function() { //сохраняет текущий набор данных в гриде и возвращает его уникальный номер
		this.saveGridCopy(0);		
		return this.dataSetNumber;
	},
	
	getNewDataSetNumber: function() { //сохраняет новый набор данных в гриде и возвращает уникальный его уникальный номер
		var num = this.generateDataSetNumber();
		this.saveGridCopy(num);
		return num;
	},
	
	afterRender: function(){ //переопределяем стандартную функцию PropertyGrid чтобы прелотваратить стирание всего что загрузилось при помощи loadData
        Ext.grid.PropertyGrid.superclass.afterRender.apply(this, arguments);
    }
});
