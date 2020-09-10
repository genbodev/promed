/**
* swDrugRequestLpuSelectWindow - окно множественного выбора МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Salakhov R.
* @version      02.2016
* @comment      
*/
sw.Promed.swDrugRequestLpuSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 800,
	id: 'DrugRequestLpuSelectWindow',
	title: 'Заявочная кампания: Выбор МО',
	layout: 'border',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	firstTabIndex: 1000,
	selection: null,
	onSelect: Ext.emptyFn,
	getLoadMask: function() {
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: 'Подождите... '});
		}
		return this.loadMask;
	},	
	show: function() {
		var wnd = this;
		
		sw.Promed.swDrugRequestLpuSelectWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();
		this.center();
		this.maximize();		
		this.onSelect = Ext.emptyFn;
		this.PersonRegisterType_id = null;

		if (arguments[0].onSelect && typeof arguments[0].onSelect == 'function') {
            this.onSelect = arguments[0].onSelect;
        }

        if (!Ext.isEmpty(arguments[0].PersonRegisterType_id)) {
            this.PersonRegisterType_id = arguments[0].PersonRegisterType_id;
        }

		if (arguments[0].selection) {
			this.setSelection(arguments[0].selection);
		} else {
			if (arguments[0].owner && arguments[0].owner.getGrid())
				this.setSelection(arguments[0].owner.getGrid().getStore());
		}

		if (arguments[0].begDate && arguments[0].endDate) {
			this.begDate = arguments[0].begDate;
			this.endDate = arguments[0].endDate;
		} else {
			var now = new Date();
			this.begDate = now.format("d.m.Y");
			this.endDate = now.format("d.m.Y");
		}
		
		this.doSearch(true);
		this.unionStores();
		
		wnd.form.findField('PostMed_id').getStore().load({
			params: {
				Object:'PostMed',
				PostMed_id:'',
				PostMed_Name:''
			}
		});
		wnd.form.findField('LpuSectionProfile_id').getStore().load();
		
		this.getLoadMask().hide();		
	},
	setSelection: function(store) { //копирование данных из внешнего store в selection
		var wnd = this;
		if (!wnd.selection) {
			wnd.selection = new Ext.data.Store({
				proxy: null,
				autoLoad: false
			});		
		} else {
			this.selection.removeAll();
		}

		if (store) {
			store.each(function(r) {
				if (r.get('Lpu_id') > 0) {
					var rec = new Ext.data.Record.create(wnd.SearchGrid.jsonData['store']);
					var data = new Object();
					Ext.apply(data, r.data);
					data.check = (r.get('state') == 'add' || r.get('state') == 'saved');
					wnd.selection.insert(wnd.selection.getCount(), new rec(data));
				}
			});
		}
	},
	moveRecord: function(record, direction, sort) {
		var wnd = this;
		var view_frame = wnd.SearchGrid;
		var store = view_frame.getGrid().getStore();
		var record_count = store.getCount();
		
		var item_index = wnd.selection.findBy(function(r,id) {				
			if(r.get('Lpu_id') == record.data.Lpu_id)
				return true;
		});

		if (direction == 'selection') { //перемещение в селекшен
			if (item_index < 0)	{ //запись не обнаружена в селекшене
				var rec = new Ext.data.Record.create(view_frame.jsonData['store']);
				var data = new Object();
				Ext.apply(data, record.data);			
				data.state = 'add';
				wnd.selection.insert(wnd.selection.getCount(), new rec(data));
			} else { //запись найдена в селекшене
				var rec = wnd.selection.getAt(item_index)
				if (rec.get('state') == 'delete')
					rec.set('state', 'saved');
				rec.set('check', 'true');
			}
		}
		
		if (direction == 'list') {//перемещение в лист
			if (item_index >= 0) { //запись найдена в селекшене
				var rec = wnd.selection.getAt(item_index);
				if (rec.get('state') == 'saved')
					rec.set('state', 'delete');
				if (rec.get('state') == 'add')
					wnd.selection.removeAt(item_index);
			}
		}
		if (sort)
			wnd.setGridSort();
	},
	unionStores: function() {
		var wnd = this;
		var view_frame = wnd.SearchGrid;
		var store = view_frame.getGrid().getStore();
		
		if ( store.getCount() == 1 && !store.getAt(0).get('Lpu_id') ) {
			view_frame.removeAll({ addEmptyRecord: false });
		}
		
		wnd.selection.each(function(r) {
			var rec = new Ext.data.Record.create(wnd.SearchGrid.jsonData['store']);
			var data = new Object();
			var item_index = store.findBy(function(rr,id) {
				if(rr.get('Lpu_id') == r.get('Lpu_id'))
					return true;
			});
			if (item_index >= 0)
				store.removeAt(item_index);
			Ext.apply(data, r.data);			
			store.insert(0/*store.getCount()*/, new rec(data));
			view_frame.updateRowCount();
		});
		wnd.setGridSort();
	},
	setGridSort: function() {
		this.SearchGrid.getGrid().getStore().sort('check','DESC'/*, ['Lpu_Fio','ASC']*/);
	},
	checkAll: function (check) {
		var wnd = this;

		var store = wnd.SearchGrid.getGrid().getStore();
		store.each(function(r){
            if (r.get('DrugRequestMp_Count') > 0) {
                return true;
            }
			if (check) {
				if (!r.get('check')) {
					r.set('check', true);
					wnd.moveRecord(r, 'selection');
				}
			} else {
				if (r.get('check')) {
					r.set('check', false);
					wnd.moveRecord(r, 'list');
				}
			}
		});
	},
	checkOne: function(mp_id) {
		var wnd = this;
		var grid = wnd.SearchGrid.getGrid();
		var record = grid.getStore().getAt(grid.getStore().findBy(function(rec) { return rec.get('Lpu_id') == mp_id; }));
		if (record) {
			record.set('check', !record.get('check'));
			wnd.moveRecord(record, (record.get('check') ? 'selection' : 'list'), true);
			record.commit();
		}
	},
	initComponent: function() {
		var wnd = this;
		
		this.SearchPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'north',
			autoHeight: true,
			frame: true,
			id: 'drlsSearchForm',
			labelWidth: 150,
			labelAlign: 'right',
			items: 
			[{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SurName',
						fieldLabel: 'Фамилия',
						width: 200,
						enableKeyEvents: true,
						listeners: {
							'keydown': wnd.onKeydown
						}
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_FirName',
						fieldLabel: 'Имя',
						width: 200,
						enableKeyEvents: true,
						listeners: {
							'keydown': wnd.onKeydown
						}
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SecName',
						fieldLabel: 'Отчество',
						width: 200,
						enableKeyEvents: true,
						listeners: {
							'keydown': wnd.onKeydown
						}
					}]
				}]
			}, {
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_id',
				fieldLabel: 'МО',
				width: 300,
				listWidth: 400,
				listeners: {
					'keydown': wnd.onKeydown
				}
			}, {
				xtype: 'swlpusectionprofilelitecombo',
				hiddenName: 'LpuSectionProfile_id',
				fieldLabel: 'Профиль',
				width: 300,
				listeners: {
					'keydown': wnd.onKeydown
				}
			}, {
				xtype: 'swpostmedcombo',
				hiddenName: 'PostMed_id',
				fieldLabel: 'Должность',
				width: 300,
				listeners: {
					'keydown': wnd.onKeydown
				}
			}, {
                xtype: 'checkbox',
                name: 'WorkData_IsResponsible',
                fieldLabel: 'Ответственный по ЛЛО'
            }, {
				layout: 'column',
				items: [{
					layout: 'form',
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					items: [{
						xtype: 'button',
						text: 'Поиск',
						minWidth: 80,
						id: 'drlsButtonSetFilter',
						handler: function () {
							Ext.getCmp('DrugRequestLpuSelectWindow').doSearch();
						}
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'button',
						text: 'Сброс',
						minWidth: 80,
						id: 'drlsButtonUnSetFilter',
						handler: function () {
							Ext.getCmp('DrugRequestLpuSelectWindow').doSearch(true);
						}
					}]
				}]
			}]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_print', hidden: true},
				{name: 'action_refresh', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadLpuSelectList',
			height: 180,
			object: 'Lpu',
			id: 'drlsLpuSelectGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'Lpu_id', type: 'int', header: 'ID', key: true},
				{name: 'MedPersonal_id', hidden: true},
                {name: 'DrugRequestLpuGroup_id', hidden: true},
				{name: 'state', header: 'state', hidden: true},
				{
						name: 'false_check',
						width: 35,
						sortable: false,
						hideable: false,
						renderer: function(v, p, record) {
							var mp_id = record.get('Lpu_id');
							return '<input type="checkbox" value="'+mp_id+'"'+(record.get('check') ? ' checked="checked"' : '')+(record.get('DrugRequestMp_Count') > 0 ? ' disabled="disabled"' : '')+'" onClick="getWnd(\'swDrugRequestLpuSelectWindow\').checkOne(this.value);">';
						},
						header: '<input type="checkbox" id="drls_checkAll_checkbox" onClick="getWnd(\'swDrugRequestLpuSelectWindow\').checkAll(this.checked);">'
				},
				{name: 'check', type: 'checkcolumn', width: 35, sortable: false, hideable: false, hidden: true},
                {name: 'Lpu_Name', type: 'string', header: 'МО', id: 'autoexpand'},
				{name: 'Person_Fio', header: 'ФИО', width: 300},
				{name: 'Post_Name', type: 'string', header: 'Должность', width: 200},
				{name: 'LpuSectionProfile_Name', type: 'string', header: 'Профиль', width: 200},
				{name: 'DrugRequestMp_Count', hidden: true}
			],
			title: 'Список МО',
			toolbar: false,
			onLoadData: function() {
				wnd.unionStores();
			},
			onDblClick: function(grid, rowIdx, object) {
				var record = grid.getStore().getAt(rowIdx);
				if (record && record.get('DrugRequestMp_Count') < 1) {
					record.set('check', !record.get('check'));
					wnd.moveRecord(record, (record.get('check') ? 'selection' : 'list'), true);
					record.commit();
				}
			},
			updateRowCount: function() { //так как инсертим данные мы не через loadData, необходима такая корректировка данных
				var store = this.getGrid().getStore(); 
				var cnt = store.getCount();
				if (cnt == 1 && this.isEmpty())
					cnt = 0;
				this.rowCount = cnt;
			}
		});
		
		Ext.apply(this, {
			layout:'border',
			defaults: {split: true},
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.onSelect(this.ownerCt.selection);
				},
				iconCls: 'ok16',
				text: 'Выбрать'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: 
			[this.SearchPanel,
			{
				border: false,
				xtype: 'panel',
				region: 'center',
				layout:'border',
				items: [wnd.SearchGrid]
			}]
		});
		wnd.form = wnd.SearchPanel.getForm();
		sw.Promed.swDrugRequestLpuSelectWindow.superclass.initComponent.apply(this, arguments);
	},
	onKeydown: function (inp, e) {
		var wnd = getWnd('swDrugRequestLpuSelectWindow');
		if (e.getKey() == Ext.EventObject.ENTER) {
			e.stopEvent();
			wnd.doSearch();
		}
	},
    setDefaultValues: function() {
        if (this.PersonRegisterType_id > 0) {
            this.form.findField('WorkData_IsResponsible').setValue('on');
            this.form.findField('WorkData_IsResponsible').disable();
        } else {
            this.form.findField('WorkData_IsResponsible').setValue(null);
            this.form.findField('WorkData_IsResponsible').enable();
        }

    },
	doSearch: function(clear) {
		var wnd = this;
		
		if (clear) {
			wnd.form.reset();
			wnd.SearchGrid.removeAll();
			wnd.unionStores();
            wnd.setDefaultValues();
		} else {
			var filters = wnd.form.getValues();;			
			filters.start = 0;
			filters.limit = 1000;
            filters.WorkData_IsResponsible = wnd.form.findField('WorkData_IsResponsible').getValue() ? 1 : 0;
			/*filters.begDate = wnd.begDate;
			filters.endDate = wnd.endDate;*/
			wnd.SearchGrid.loadData({
				globalFilters: filters
			});
		}
						
		document.getElementById('drls_checkAll_checkbox').checked = false;
	}
});