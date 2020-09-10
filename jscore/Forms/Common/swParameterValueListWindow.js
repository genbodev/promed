/**
* swParameterValueListWindow - окно просмотра списка параметров
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Alexander Permyakov (alexpm)
* @version      07.2013
* @comment      
*/

/*NO PARSE JSON*/

sw.Promed.swParameterValueListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swParameterValueListWindow',
	objectSrc: '/jscore/Forms/Common/swParameterValueListWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['spisok_parametrov'],
	draggable: true,
	id: 'swParameterValueListWindow',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	//входные параметры
	action: null,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	
	doReset: function() {
		var form = this.filterPanel.getForm(),
			grid = this.viewFrame.getGrid();
		form.reset();
		form.findField('ParameterValue_Alias').focus(true, 250);
		grid.getStore().baseParams = {};
		this.viewFrame.removeAll(true);
		this.viewFrame.ViewGridPanel.getStore().removeAll();
        this.viewFrame.setActionDisabled('action_select', true);
	},
	doSearch: function() 
	{
		var form = this.filterPanel.getForm(),
			params = {};//form.getValues()

		params.ParameterValueListType_id = form.findField('ParameterValueListType_id').getValue() || null;
		params.ParameterValue_Name = form.findField('ParameterValue_Name').getValue() || null;
		params.ParameterValue_Alias = form.findField('ParameterValue_Alias').getValue() || null;

		this.viewFrame.removeAll(true);
		params.start = 0; 
		params.limit = 100;
		this.viewFrame.loadData({globalFilters:params});
	},
	onOkButtonClick: function() {
		var th = this,
			form = this.filterPanel.getForm(),
			grid=this.viewFrame.getGrid(),
			records = this.viewFrame.getGrid().getSelectionModel().getSelections();
			//log(["grid",grid])
		if (!records) {
			sw.swMsg.alert(lang['soobschenie'], lang['vyi_nichego_ne_vyibrali']);
			return false;
		}
		var data = {};
		for(var record in records){
			if(records[record].data){
				 data[record]= records[record].data;
				if (!data[record].ParameterValue_Marker) {
					sw.swMsg.alert('Сообщение', 'Вами выбран параметр без наименования или без списка по умолчанию!');
					return false;
				}
				data[record].marker = data[record].ParameterValue_Marker;
			}
		}
		this.onSelect(data);
        this.hide();
       //
        return true;

        /*
		var doSelect = function() {
			var data = record.data;
			// выбранный тип списка, по умолчанию берется значение из БД
			var listtype_id = form.findField('ParameterValueListType_id').getValue() || data.ParameterValueListType_id;
			
			//Системное имя параметра, если в БД оно неправильное, то берется значение 'parameter'+ data.ParameterValue_id
			var sysnick_format = new RegExp("^parameter[0-9]+$","i");// /^parameter[0-9]+$/i
			var sysnick = sysnick_format.test(data.ParameterValue_SysNick) ? data.ParameterValue_SysNick : 'parameter'+ data.ParameterValue_id;
			
			// маркер должен совпадать с регулярным выражением "/@#@parameter([0-9]+)_([0-9]+)/i"
			data.marker = '@#@'+ sysnick +'_'+ listtype_id;
			th.hide();
			th.onSelect(data);
		};
		
		if(!form.findField('ParameterValueListType_id').getValue()) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						doSelect();
					} else {
						form.findField('ParameterValueListType_id').focus(true, 250);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyi_ne_vyibrali_tip_spiska_znacheniy_ispolzovat_tip_spiska_znacheniy_po_umolchaniyu'],
				title: lang['vopros']
			});
			return false;
		}
		doSelect();
        */
	},
	openEditWindow: function(a) {
		var win = this,
            i = 0,
			grid = win.viewFrame.getGrid(),
			callback = function(data) {
				if ( !data || !data.ParameterValue_id ) {
					return false;
				}
				var form = win.filterPanel.getForm();
				form.findField('ParameterValue_Name').setValue(data.ParameterValue_Name || null);
				form.findField('ParameterValue_Alias').setValue(data.ParameterValue_Alias || null);
				form.findField('ParameterValueListType_id').setValue(data.ParameterValueListType_id || null);
				win.doSearch();
				/*
				var fields = [];
				grid.getStore().fields.eachKey(function(key, item) {
					fields.push(key);
				});
				var record = grid.getStore().getById(data.ParameterValue_id);
				if ( !record ) {
					var params = {};
					for (i = 0; i < fields.length; i++ ) {
						params[fields[i]] = data[fields[i]];
					}
					record = new Ext.data.Record(params);
					grid.getStore().add([record]);
					grid.getStore().commitChanges();
				} else {
					for (i = 0; i < fields.length; i++ ) {
						record.set(fields[i], data[fields[i]]);
					}
					record.commit();
				}
				var i = grid.getStore().indexOf(record);
				grid.getSelectionModel().selectRow(i);
				grid.getView().focusRow(i);
				win.viewFrame.onRowSelect(grid.getSelectionModel(),i,record);
				*/
                return true;
			},
			record = grid.getSelectionModel().getSelected(),
			params = {};

		switch (a) {
			case 'view':
			case 'edit':
				if (!record || !record.get('ParameterValue_id'))
				{
					sw.swMsg.alert(lang['soobschenie'], lang['vyi_nichego_ne_vyibrali']);
					return false;
				}
				if (record.get('accessType') == 'view') {
					a = 'view';
				}
				params = record.data;
			break;

			case 'add':
				params.ParameterValue_id = null;
                params.ParameterValue_Alias = '';
                params.ParameterValue_Name = '';
				params.ParameterValueListType_id = 1;
			break;

			default:
				return false;
			break;
		}
		params.action = a;
		params.callback = callback;
		params.onHide = null;
		getWnd('swParameterValueEditWindow').show(params);
        return true;
	},
	

	initComponent: function() {
		var win = this;
		 var xg = Ext.grid;
		this.filterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'ParameterValueSearchForm',
			labelAlign: 'right',
			labelWidth: 160,
			region: 'north',
			items: [{
				fieldLabel: lang['naimenovanie_parametra'],
				name: 'ParameterValue_Alias',
				id: 'WIN_ParameterValue_Alias',
                maskRe: new RegExp("^[а-яА-ЯёЁ]*$"),
				width: 200,
				enableKeyEvents: true,
				xtype: 'textfield'
			}, {
				fieldLabel: lang['naimenovanie_dlya_pechati'],
				name: 'ParameterValue_Name',
				maskRe: /[^%]/,
				width: 200,
				enableKeyEvents: true,
				xtype: 'textfield'
			}, {
				fieldLabel: lang['tip_spiska_znacheniy'],
				comboSubject: 'ParameterValueListType',
				allowSysNick: true,
				typeCode: 'int',
				autoLoad: false,
				width: 200,
				enableKeyEvents: true,
				xtype: 'swcommonsprcombo'
            }],
            buttons: [{
                handler: function() {
                    win.doSearch();
                },
                iconCls: 'search16',
                text: BTN_FRMSEARCH
            }, {
                handler: function() {
                    win.doReset();
					win.doSearch();
                },
                iconCls: 'resetsearch16',
                text: BTN_FRMRESET
            }],
            keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.viewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=ParameterValue&m=doLoadGrid',
			id: 'ParameterGrid',
			object: 'ParameterValue',
			editformclassname: 'swParameterValueEditWindow',
			actions:
			[
				{name:'action_add', tooltip: lang['dobavit_parametr'], handler: function(){ win.openEditWindow('add'); } },
				{name:'action_edit', tooltip: lang['redaktirovat_parametr'], handler: function(){ win.openEditWindow('edit'); } },
				{name:'action_view', tooltip: lang['otkryit_parametr'], handler: function(){ win.openEditWindow('view'); } },
				{name:'action_delete'}
			],
			pageSize: 100,
			paging: true,
			root: 'data',
			selectionModel:'multiselect',
			multi:true,
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{ header: 'ID', type: 'int', name: 'ParameterValue_id', key: true },
                { header: lang['naimenovanie_parametra'],  type: 'string', name: 'ParameterValue_Alias', id: 'autoexpand', isparams: true },
                { header: lang['naimenovanie_dlya_pechati'],  type: 'string', name: 'ParameterValue_Name', width: 170, isparams: true },
				{ header: lang['spisok_po_umolchaniyu'],  type: 'string', name: 'ParameterValueListType_Name', width: 150 },
				{ header: lang['kolichestvo_znacheniy'],  type: 'int', name: 'ParameterValue_valueCnt', width: 140 },
                { header: lang['tip_spiska_znacheniy'],  type: 'int', name: 'ParameterValueListType_id', hidden: true, isparams: true },
                { header: lang['sist_imya'],  type: 'string', name: 'ParameterValue_SysNick', hidden: true },
                { header: lang['marker'],  type: 'string', name: 'ParameterValue_Marker', hidden: true },
				{ header: lang['dostup_na_izmenenie'],  type: 'string', name: 'accessType', hidden: true }
			],
			toolbar: true,
			onLoadData: function(flag) {
				if (flag) //this.ViewGridPanel.getStore().getCount()>0
				{
					this.ViewGridPanel.getView().focusRow(0);
					if (this.selectionModel!='cell') {
						this.ViewGridPanel.getSelectionModel().selectFirstRow();
						this.onRowSelect(this.ViewGridPanel.getSelectionModel(),0,this.ViewGridPanel.getSelectionModel().getSelected());
						this.ViewGridModel.clearSelections();
					}
				}
			},
			onRowSelect: function(sm,rowIdx,record) {
                this.setActionDisabled('action_select',(!record.get('ParameterValue_Marker')));
                this.setActionDisabled('action_edit',(record.get('accessType') == 'view'));
				this.setActionDisabled('action_delete',(record.get('accessType') == 'view'));
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				this.runAction('action_select');
			},
			onEnter: function()
			{
				this.runAction('action_edit');
			}
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'WIN_ParameterValue_Alias',
				text: BTN_FRMCLOSE
			}],
			items: [ 
				this.filterPanel,
				this.viewFrame
			]
		});
		sw.Promed.swParameterValueListWindow.superclass.initComponent.apply(this, arguments);
        this.viewFrame.ViewToolbar.on('render', function(vt){
            this.ViewActions['action_select'] = new Ext.Action({
                name:'action_select',
                handler: function() { win.onOkButtonClick(); },
                text:lang['vyibrat'],
                tooltip: lang['vyibrat_parametr_i_vstavit_v_shablon_marker_parametra'],
                iconCls : 'ok16'
            });
            vt.insertButton(1,this.ViewActions['action_select']);
            return true;
        }, this.viewFrame);
	},

	show: function() {
		sw.Promed.swParameterValueListWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.onSelect = arguments[0].onSelect || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		
        this.viewFrame.ViewActions['action_select'].setHidden(typeof arguments[0].onSelect != 'function');
		
		this.doReset();
		this.doSearch();
		log(["grid",this.viewFrame])
	}
});