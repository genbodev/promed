sw.Promed.swMorbusACSWindow = Ext.extend(sw.Promed.BaseForm, 
{
	width : 500,
	height : 400,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	autoScroll: true,
	border : false,
	plain : false,
	action: null,
	maximized: false,
	title: lang['zapis_registra'],
	id:"swMorbusACSWindow",
	doSave: function() 
	{
		this.hide();
	},
	show: function(){
		sw.Promed.swMorbusACSWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].userMedStaffFact) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}
		var curWin = this;
		this.Person_id = null;
		this.action = 'edit';
		if(arguments[0].action){
			this.action = arguments[0].action;
		}
		
		if(arguments[0].Person_id){
			this.Person_id = arguments[0].Person_id;
		}
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		base_form.setValues(arguments[0]);
		var persId = arguments[0].Person_id;
		var persFrame = this.findById('ACSWF_PersonInformationFrame');
		persFrame.load({
			Person_id:(arguments[0].Person_id ? arguments[0].Person_id : ''),
			callback:function () {
				
			}
			
		});
		var params = {};
		var grid = this.HospGrid.getGrid().getStore();
		params.PersonRegister_id = base_form.findField('PersonRegister_id').getValue();
		base_form.load({
			url: "/?c=PersonRegister&m=load",
			params: params,
			success: function (form, action)
			{
				loadMask.hide();
				var result = Ext.util.JSON.decode(action.response.responseText);
				if ( result[0].success != undefined && !result[0].success )
					Ext.Msg.alert("Ошибка", result[0].Error_Msg);
					grid.baseParams = {Person_id:persId};
					grid.load();
			},
			failure: function (form, action)
			{
				loadMask.hide();
				Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
			}
		})
		
	},
	openWindow: function(action) {
		if (!action || !action.toString().inlist(['add','view','edit'])) {
			return false;
		}
		var cur_win = this;
		var grid = this.HospGrid.getGrid();
		if (!grid.getSelectionModel().getSelected()) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();

		var params = new Object();
		
		params.action = action;
		params.callback = function() {
			grid.getStore().reload();
		}
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};
		params.Person_id = this.Person_id;
		switch(action) {
			case 'add':
				getWnd('swMorbusACSEditWindow').show(params);
				break;
            case 'edit':
            case 'view':
				if (getWnd('swMorbusACSEditWindow').isVisible()) {
					getWnd('swMorbusACSEditWindow').hide();
				}
				if ( Ext.isEmpty(selected_record.get('MorbusACS_id')) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['gospitalizatsii_na_cheloveka_ne_zavedeno']);
					return false;
				}
				if ( !Ext.isEmpty(selected_record.get('Morbus_disDT'))&&action=='edit' ) {
					params.action = 'view';
				}
				params.onHide = function(isChange) {
					if(isChange) {
						grid.getStore().reload();
					} else {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					}
				};
				params.callback = Ext.emptyFn;
				params.MorbusACS_id = selected_record.data.MorbusACS_id;
				getWnd('swMorbusACSEditWindow').show(params);
				break;	
		}
		

		
	},
	deleteACS:  function(){
		var cur_win = this;
		var grid = this.HospGrid.getGrid();
		if (!grid.getSelectionModel().getSelected()) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		if ( Ext.isEmpty(selected_record.get('MorbusACS_id')) ) {
					sw.swMsg.alert(lang['soobschenie'], lang['gospitalizatsii_na_cheloveka_ne_zavedeno']);
					return false;
		}
		  Ext.Ajax.request({
                    url: '/?c=MorbusACS&m=deleteMorbusACS',
                    params: {MorbusACS_id: selected_record.get('MorbusACS_id')},
                    failure: function() {
                        showSysMsg(lang['pri_udalenii_dannyih_proizoshla_oshibka']);
                    },
                    success: function(response)
                    {
						grid.getStore().reload();
					}
		  });
	},
	
	initComponent: function() 
	{
		var cur_win = this;
		this.HospGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled:(cur_win.action=='view'), handler: function() { this.openWindow('add'); }.createDelegate(this)},
                {name: 'action_edit', disabled:(cur_win.action=='view'),handler: function() { this.openWindow('edit'); }.createDelegate(this)},
                {name: 'action_view', handler: function() { this.openWindow('view'); }.createDelegate(this)},
				{name: 'action_delete',disabled:(cur_win.action=='view'),handler: function() { this.deleteACS(); }.createDelegate(this)},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			obj_isEvn: false,
			border: true,
			dataUrl: '/?c=MorbusACS&m=loadACSGrid',
			height: 250,
			region: 'south',
			object: 'MorbusACS',
			editformclassname: 'swMorbusACSEditWindow',
			id: 'MorbusACSGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			onDblClick: function () {
				cur_win.openWindow('edit');
			},
			onRowSelect: function(sm,index,record) {
				this.getAction('action_add').setDisabled(cur_win.action=='view')
				this.getAction('action_delete').setDisabled(cur_win.action=='view'|| Ext.isEmpty(record.get('MorbusACS_id')) );
                this.getAction('action_edit').setDisabled( cur_win.action=='view'|| Ext.isEmpty(record.get('MorbusACS_id')) || Ext.isEmpty(record.get('Morbus_disDT')) == false );
                this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('MorbusACS_id')) );
			},
			stringfields: [
				{name: 'MorbusACS_id', type: 'int', header: 'ID', key: true},
				{name: 'Morbus_id', type: 'int', hidden:true},
				{name: 'Morbus_setDT', type: 'string', header: lang['data_postupleniya'], width: 120},
				{name: 'Morbus_disDT', type: 'string', header: lang['data_vyipiski'], width: 80},
				{name: 'Diag_Name', type: 'string',header: lang['klinicheskiy_diagnoz'],width: 180},
				{name: 'MorbusACS_Result', type: 'string', header: lang['ishod_zabolevaniya']}
			],
			title: lang['gospitalizatsii'],
			toolbar: true
		});
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'MorbusForm',
			labelAlign: 'right',
			labelWidth: 180,
			layout: 'form',
			region: 'center',
			items: [
               {
                    name: 'PersonRegister_id',
                    value: null,
                    xtype: 'hidden'
                },{
                    name: 'Person_id',
                    value: null,
                    xtype: 'hidden'
                },{
                    name: 'PersonRegister_id',
                    value: null,
                    xtype: 'hidden'
                },{
                    name: 'Morbus_id',
                    value: null,
                    xtype: 'hidden'
                },{
					fieldLabel: lang['data_vklyucheniya_v_registr'],
					name: 'PersonRegister_setDate',
					disabled:true,
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999', false)
					],
					xtype: 'swdatefield'
					
				}
			],
			reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'PersonRegister_setDate' },
					{ name: 'Morbus_id' },
					{ name: 'InspmUser' },
					{ name: 'InsDate' }
				])
				
		});
		
	Ext.apply(this, 
		{
			buttons: 
			[
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [new sw.Promed.PersonInformationPanelShort({
				id:'ACSWF_PersonInformationFrame',
				region:'north'
			}),
				this.FormPanel,
				this.HospGrid]
		});
	sw.Promed.swMorbusACSWindow.superclass.initComponent.apply(this, arguments);
	}
});