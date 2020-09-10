/**
 * sw.Promed.FileList. Класс списка файлов
 *
 * @author		Salakhov Rustam
 * @class		sw.Promed.FileList
 * @extends		Ext.grid.PropertyGrid
 */
sw.Promed.FileList = function(config)
{
	Ext.apply(this, config);
	sw.Promed.FileList.superclass.constructor.call(this);
};

Ext.extend(sw.Promed.FileList, Ext.form.FormPanel, {
	saveOnce: true, //переаметр определяет режим сохранения новых записей. true - немедленная запись, false - запись при вызове метода saveChanges
	dataUrl: '',
	saveUrl: '',
	saveChangesUrl: '',
	deleteUrl: '',
	listParams: {},		
	id: 'FileList',
	autoHeight: true,
	disabled: false,
	
	initComponent: function() {
		var th = this;
	
		this.FileGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.addRow(); }.createDelegate(this) },
				{ name: 'action_edit', disabled: true, hidden: true, handler: function() { this.saveChanges(); }.createDelegate(this) },
				{ name: 'action_view', disabled: true, hidden: true  },
				{ name: 'action_delete', handler: function() { this.deleteRow(); }.createDelegate(this) },
				{ name: 'action_refresh', hidden: !th.saveOnce }, // если не немедленное сохранение, то кнопка обновить сбрасывает весь список, скрыл поэтому её
				{ name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: this.dataUrl,
			id: 'FileViewGrid',
			pageSize: 100,
			paging: false,
			region: 'center',			
			stringfields: [
				{ name: 'EvnMediaData_id', type: 'int', header: 'ID', key: true },				
				{ header: langs('Путь'),  type: 'string', name: 'EvnMediaData_FilePath', width: 100, hidden: true },
				{ header: langs('Файл'),  type: 'string', name: 'EvnMediaData_FileName', width: 200, hidden: true },
				{ header: 'state',  type: 'string', name: 'state', width: 100, hidden: true },
				{ header: langs('Файл'),  type: 'string', name: 'EvnMediaData_FileLink', width: 200 },
				{ header: langs('Комментарий'),  type: 'string', name: 'EvnMediaData_Comment', id: 'autoexpand', width: 300 }
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {				
				var ds_edit = (this.readOnly || th.disabled || Ext.isEmpty(record.get('EvnMediaData_id')) || th.saveOnce);
				var ds_del = (this.readOnly || th.disabled || Ext.isEmpty(record.get('EvnMediaData_id')));
				this.ViewActions.action_edit.setDisabled(ds_edit);
				this.ViewActions.action_delete.setDisabled(ds_del);
			},
			onCellDblClick: function(grid, rowIdx, colIdx, event) {
				return false;
			}
		});
		
		Ext.apply(this, {			
			items: [this.FileGrid]
		});
		
		sw.Promed.FileList.superclass.initComponent.apply(this, arguments);
	},
	
	loadData: function(params) {
		if (!params || params != null)
			params = this.listParams;

		this.FileGrid.loadData({
			globalFilters: params
		});
	},
	
	addRow: function() {
		var th = this;
		var gr = th.FileGrid.getGrid();
		var ds_model = Ext.data.Record.create([
			'EvnMediaData_id',
			'EvnMediaData_FilePath',
			'EvnMediaData_FileName',
			'state',
			'EvnMediaData_FileLink',
			'EvnMediaData_Comment'
		]);
		
		var params = new Object();		
		params.enableFileDescription = true;
		params.saveUrl = this.saveUrl;
		params.saveParams = this.listParams;
		params.saveParams.saveOnce = this.saveOnce;
		//params.FilesData = Ext.getCmp('TEW_Treatment_Document').getValue();
		params.callback = function(data) {			
			if (!data) {
				return false;
			}			
			//var response_obj = Ext.util.JSON.decode(data);
			//swalert(response_obj);
			if (th.saveOnce) {
				th.FileGrid.refreshRecords(null,0);
			} else {
				var response_obj = Ext.util.JSON.decode(data);
				var pos = (gr.getStore().data.first() && gr.getStore().data.first().data.EvnMediaData_id != null) ? gr.getStore().data.length : 0;
				
				gr.getStore().insert(
					pos,
					new ds_model({
						EvnMediaData_id: 0,
						EvnMediaData_FilePath: response_obj[0].file_name,
						EvnMediaData_FileName: response_obj[0].orig_name,
						state: 'add',
						EvnMediaData_FileLink: '<a href="/uploads/'+response_obj[0].file_name+'" target="_blank">'+response_obj[0].orig_name+'</a>',
						EvnMediaData_Comment: response_obj[0].description
					})
				);
				
				th.removeEmptyRow();
			}
		};
		getWnd('swFileUploadWindow').show(params);
	},
	
	removeEmptyRow: function() {
		var gr = this.FileGrid.getGrid();		
		gr.getStore().each(function(record) {		
			if ((record.get('EvnMediaData_id') == null || record.get('EvnMediaData_id') == '') && record.get('state') != 'add')
				gr.getStore().remove(record);
		});
	},
	
	deleteRow: function() {
		var vframe = this.FileGrid;
		var grid = vframe.getGrid();
		var record = vframe.getGrid().getSelectionModel().getSelected();
		if (record) {
			if (this.saveOnce || record.get('state') != 'add') {
				if( !Ext.isEmpty(record.get('EvnMediaData_id')) ) {
					Ext.Ajax.request({
						url: this.deleteUrl,
						callback: function(options, success, response) {
							if (success) {
								vframe.refreshRecords(null,0);
							}
						},
						params: {id : record.get('EvnMediaData_id')}
					});
				}
			} else {
				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Вы хотите удалить запись?'),
					title: langs('Подтверждение'),
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							record.set('state', 'delete');
							grid.getStore().filterBy(function(record){
								if(record.data.state == 'delete') return false;
								return true;
							});

							if ( grid.getStore().getCount() == 0 ) {
								LoadEmptyRow(grid);
							}
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					}
				});
			}
		}
	},
	
	getChangedData: function(){ //возвращает новые и измненные показатели
		var gr = this.FileGrid.getGrid();
		var data = new Array();
		gr.getStore().clearFilter();
		gr.getStore().each(function(record) {
			if ((record.data.state == 'add' || record.data.state == 'delete'))
				data.push(record.data);
		});
		gr.getStore().filterBy(function(record){
			if(record.data.state == 'delete') return false;
			return true;
		});
		return data;
	},
	
	getJSONChangedData: function(){ //возвращает новые и измненные показатели в виде закодированной JSON строки
		var dataObj = this.getChangedData();
		return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
	},
	
	saveChanges: function(callback) {
		var th = this;
		var params = new Object();		
		params = this.listParams;
		params.changedData = this.getJSONChangedData();						
		Ext.Ajax.request({
			url: th.saveChangesUrl,
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					//swalert(response_obj);
					//to-do сделать восстановление выделения строки
					var rec = null;
					th.FileGrid.refreshRecords(null,0);
					if (typeof callback == 'function') {
						callback(rec);
					}
				} else {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					//swalert(response_obj);
				}
			},
			method: 'post',
			params: params
		});
	},
	
	setDisabled: function(ds) {		
		this.disabled = ds;
		var record = this.FileGrid.getGrid().getSelectionModel().getSelected();
		this.FileGrid.ViewActions.action_edit.setDisabled(!record || record.get('EvnMediaData_id') == null || this.disabled);
		this.FileGrid.ViewActions.action_delete.setDisabled(!record || record.get('EvnMediaData_id') == null || this.disabled);
		this.FileGrid.ViewActions.action_add.setDisabled(this.disabled);
	}
});
