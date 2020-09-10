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

sw.Promed.swMarkerValueListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMarkerValueListWindow',
	objectSrc: '/jscore/Forms/Common/swMarkerValueListWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['spisok_markerov'],
	draggable: true,
	id: 'swMarkerValueListWindow',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	//входные параметры
	action: null,
	EvnClass_id:null,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	
	doReset: function() {
		//var form = this.filterPanel.getForm();
		var	grid = this.viewFrame.getGrid();
		//form.reset();
		//form.findField('ParameterValue_Alias').focus(true, 250);
		grid.getStore().baseParams = {};
		//this.viewFrame.removeAll(true);
		//this.viewFrame.ViewGridPanel.getStore().removeAll();
        this.viewFrame.setActionDisabled('action_select', true);
	},
	doSearch: function() 
	{
		//var form = this.filterPanel.getForm();
			//grid = this.viewFrame.getGrid(),
		var	params = {};//form.getValues()
           // param_list_type = form.findField('ParameterValueListType_id').getValue(),
           // param_name = form.findField('ParameterValue_Name').getValue(),
			//param_sysnick = form.findField('ParameterValue_Alias').getValue();

      /*  if (param_list_type) {
            params.ParameterValueListType_id = param_list_type;
        }
        if (param_name) {
            params.ParameterValue_Name = param_name;
        }
        if (param_sysnick) {
			params.ParameterValue_Alias = param_sysnick;
		}*/
		
		//this.viewFrame.removeAll(true);
		params.start = 0; 
		params.limit = 100;
		params.EvnClass_id = this.EvnClass_id;
		this.viewFrame.loadData({globalFilters:params});
	},
	onOkButtonClick: function() {
	var th = this,
			grid=this.viewFrame.getGrid(),
			records = this.viewFrame.getGrid().getSelectionModel().getSelections();
			//log(["grid",grid])
		if (!records)
		{
			sw.swMsg.alert(lang['soobschenie'], lang['vyi_nichego_ne_vyibrali']);
			return false;
		}
		var data = {}
		for(var record in records){
			if(records[record].data){
				 data[record]= records[record].data;
				if (!data[record].name)
				{
					sw.swMsg.alert(lang['soobschenie'], lang['vami_vyibran_parametr_bez_naimenovaniya_ili_bez_spiska_po_umolchaniyu']);
					return false;
				}
				data[record].marker = data[record].name;
				
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
			grid = win.viewFrame.getGrid(),
			callback = function(data) {
				if ( !data || !data.ParameterValue_id )
				{
					return false;
				}
				var fields = new Array();
				grid.getStore().fields.eachKey(function(key, item) {
					fields.push(key);
				});
				var record = grid.getStore().getById(data.ParameterValue_id);
				if ( !record )
				{
					var params = {};
					for (var i = 0; i < fields.length; i++ ) {
						params[fields[i]] = data[fields[i]];
					}
					record = new Ext.data.Record(params);
					grid.getStore().add([record]);
					grid.getStore().commitChanges();
				}
				else {
					for (var i = 0; i < fields.length; i++ ) {
						record.set(fields[i], data[fields[i]]);
					}
					record.commit();
				}
				var i = grid.getStore().indexOf(record);
				grid.getSelectionModel().selectRow(i);
				grid.getView().focusRow(i);
				win.viewFrame.onRowSelect(grid.getSelectionModel(),i,record);
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
				if(!isAdmin && record.get('ParameterValue_IsPers') == 'false')
				{
					a = 'view';
				}
				params = record.data;
			break;

			case 'add':
				params.ParameterValue_id = null;
                params.ParameterValue_Alias = '';
                params.ParameterValue_Name = '';
				params.ParameterValue_SysNick = '';
				params.ParameterValueListType_id = 1;
				params.pmUser_did = (isAdmin) ? null : getGlobalOptions().pmuser_id;
			break;

			default:
				return false;
			break;
		}
		params.ParameterValue_pid = null;
		params.action = a;
		params.callback = callback;
		params.onHide = null;
		getWnd('swParameterValueEditWindow').show(params);
	},
	

	initComponent: function() {
		var win = this;
		this.viewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=FreeDocument&m=getFreeDocumentMarkerList',
			id: 'MarkerGrid',
			//object: 'ParameterValue',
			//editformclassname: 'swParameterValueEditWindow',
			actions:
			[
				{name:'action_add',hidden:true },
				{name:'action_edit', hidden:true },
				{name:'action_view', hidden:true },
				{name:'action_delete', hidden:true}
			],
			pageSize: 100,
			paging: true,
			obj: null,
			root: 'data',
			selectionModel:'multiselect',
			totalProperty: 'totalCount',
			multi:true,
			region: 'center',
			stringfields: [
				{ header: 'ID', type: 'int', name: 'id', key: true },
                { header: lang['naimenovanie_parametra'],  type: 'string', name: 'name', id: 'autoexpand', isparams: true },
                { header: lang['naimenovanie_dlya_pechati'],  type: 'string', name: 'alias', width: 170, isparams: true },
				{ header: lang['opisanie'],  type: 'string', name: 'description', width: 150 },
				{ header: lang['tablitsa'], name: 'is_table', width: 100, renderer: sw.Promed.Format.checkColumn },
				{ header: lang['sist_imya'],  type: 'string', name: 'field', hidden: true },
                { header: lang['marker'],  type: 'string', name: 'query', hidden: true },
				{ header: lang['vladelets'],  type: 'string', name: 'options', hidden: true }
			],
			toolbar: true,
			onLoadData: function(flag) {
				if (flag) //this.ViewGridPanel.getStore().getCount()>0
				{
					if (this.selectionModel!='cell') {
						this.onRowSelect(this.ViewGridPanel.getSelectionModel(),0,this.ViewGridPanel.getSelectionModel().getSelected());
						this.ViewGridModel.clearSelections();
						
					}
					
				}
			},
			onRowSelect: function(sm,rowIdx,record) {
                this.setActionDisabled('action_select',(!record.get('name')));
				if(record.get('name')){
					this.obj = record;
				}
                //this.setActionDisabled('action_edit',(!isAdmin && record.get('is_table') == 'false'));
				//this.setActionDisabled('action_delete',(!isAdmin && record.get('is_table') == 'false'));
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
				var record = this.obj;
				
				if (!record)
				{
					sw.swMsg.alert(lang['soobschenie'], lang['vyi_nichego_ne_vyibrali']);
					return false;
				}
				var data = {}
				
					if(record.data){
						 data[0]= record.data;
						if (!data[0].name)
						{
							sw.swMsg.alert(lang['soobschenie'], lang['vami_vyibran_parametr_bez_naimenovaniya_ili_bez_spiska_po_umolchaniyu']);
							return false;
						}
						data[0].marker = data[0].name;

					}
				
				win.onSelect(data);
				win.hide();
			   //
				return true;
			},
			onEnter: function()
			{
				this.runAction('action_select');
			},
			totalProperty: 'totalCount'
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
				//this.filterPanel,
				this.viewFrame
			]
		});
		sw.Promed.swMarkerValueListWindow.superclass.initComponent.apply(this, arguments);
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
		sw.Promed.swMarkerValueListWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.EvnClass_id = arguments[0].EvnClass_id;
		this.onSelect = arguments[0].onSelect || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		
        this.viewFrame.ViewActions['action_select'].setHidden(typeof arguments[0].onSelect != 'function');
		
		this.doReset();
		this.doSearch();
		log(["grid",this.viewFrame])
	}
});