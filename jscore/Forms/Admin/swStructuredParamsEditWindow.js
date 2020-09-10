/**
 * swStructuredParamsEditWindow - окно редактирования структурированного параметра
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright			Copyright (c) 2013 Swan Ltd.
 * @author			Petukhov Ivan (ethereallich@gmail.com)
 * @version			03.03.2016
 */

/*NO PARSE JSON*/
sw.Promed.swStructuredParamsEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	/**
	 * Настройки для отладки
	 */
	codeRefresh: true,
	objectName: 'swStructuredParamsEditWindow',
	objectSrc: '/jscore/Forms/Reg/swStructuredParamsEditWindow.js',
	
	/**
	 * Настройки окна
	 */
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	//height: 660,
	autoHeight: true,
	id: 'swStructuredParamsEditWindow',
	layout: 'fit',
	maximized: false,
	modal: true,
	plain: true,
	resizable: false,
	title: WND_SP_EDIT,
	width: 1000,
	
	/**
	 * переданные в формы параметры
	 */
	records: null,
	
	/**
	 * Идентификаторы родительского и начального уровней, нужны при добавлении нового параметра
	 */
	StructuredParams_pid: null,
	StructuredParams_rid: null,
	addStructParamType:function(grid){
		var win = this;
		var items = [];
		grid.getGrid().getStore().each(function(rec){
			if(grid.object == 'Age'){
				items.push([rec.data.AgeFrom,rec.data.AgeTo]);
			} else {
				items.push(rec.data.Code);				
			}
		});
		var params={
			comboSubject:grid.object,
			StructuredParams_id:win.StructuredParams_id,
			items:items,
			callback:function(data){
				grid.getGrid().getStore().loadData({
					'data': [data]
				}, true);
				if(!grid.getGrid().getStore().getAt(0).get('id') && !grid.getGrid().getStore().getAt(0).get('added')){
					grid.getGrid().getStore().removeAt(0);
				}
			}
		}

		getWnd('swAddStructParamTypeWindow').show(params);
	},
	delStructParamType:function(grid){
		var win = this;
		var record = grid.getGrid().getSelectionModel().getSelected();
		if(!record){ return false;}
		var msgText = lang['udalit_vyibrannuyu_zapis'];
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					if(record.data.added !== 1){
						var params = {
							Main_id:record.get('id'),
							object:grid.object
						};
						win.recordsDelete.push(params);
					}
					grid.getGrid().getStore().remove(record);
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: msgText,
			title: lang['vopros']
		});
	},
	/**
	 * Конструктор
	 */
	initComponent: function() {
		var win = this;
		
		this.SpecOmsGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add',handler:function(){win.addStructParamType(win.SpecOmsGrid)}},
				{ name: 'action_edit', disabled:true,hidden:true},
				{ name: 'action_view', disabled:true,hidden:true },
				{ name: 'action_delete' ,handler:function(){win.delStructParamType(win.SpecOmsGrid)}},
				{ name: 'action_refresh',hidden:true},
				{ name: 'action_print', disabled:true,hidden:true }
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			pageSize: 300,
			paging: true,
			height: 131,
			width: 973,
			object:'MedSpecOms',
			dataUrl: '/?c=StructuredParams&m=getStructuredParamsByType',
			id: 'ParamsSpecOmsGrid',
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'id', type: 'int', header: 'ID', key: true },
				{ name: 'added', type: 'int', hidden: true },
				{ name: 'Code',header: lang['kod'], type: 'string' },
				{ name: 'Name',header: lang['naimenovanie'], type: 'string', id: 'autoexpand' }
			],
			totalProperty: 'totalCount'
		});
		
		this.DiagGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add',handler:function(){win.addStructParamType(win.DiagGrid)}},
				{ name: 'action_edit', disabled:true,hidden:true},
				{ name: 'action_view', disabled:true,hidden:true },
				{ name: 'action_delete',handler:function(){win.delStructParamType(win.DiagGrid)}},
				{ name: 'action_refresh',hidden:true},
				{ name: 'action_print', disabled:true,hidden:true }
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			pageSize: 300,
			paging: true,
			height: 131,
			width: 973,
			object:'Diag',
			dataUrl: '/?c=StructuredParams&m=getStructuredParamsByType',
			id: 'ParamsDiagGrid',
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'id', type: 'int', header: 'ID', key: true },
				{ name: 'added', type: 'int', hidden: true },
				{ name: 'Code',header: lang['kod'], type: 'string' },
				{ name: 'Name',header: lang['naimenovanie'], type: 'string', id: 'autoexpand' }
			],
			totalProperty: 'totalCount'
		});
		this.UslugaComplexGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add',handler:function(){win.addStructParamType(win.UslugaComplexGrid)} },
				{ name: 'action_edit', disabled:true,hidden:true},
				{ name: 'action_view', disabled:true,hidden:true },
				{ name: 'action_delete' ,handler:function(){win.delStructParamType(win.UslugaComplexGrid)}},
				{ name: 'action_refresh',hidden:true},
				{ name: 'action_print', disabled:true,hidden:true }
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			pageSize: 300,
			paging: true,
			height: 131,
			width: 973,
			object:'UslugaComplex',
			dataUrl: '/?c=StructuredParams&m=getStructuredParamsByType',
			id: 'ParamsUslugaComplexGrid',
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'id', type: 'int', header: 'ID', key: true },
				{ name: 'added', type: 'int', hidden: true },
				{ name: 'Code',header: lang['kod'], type: 'string' },
				{ name: 'Name',header: lang['naimenovanie'], type: 'string', id: 'autoexpand' }
			],
			totalProperty: 'totalCount'
		});
		this.AgeGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add',handler:function(){win.addStructParamType(win.AgeGrid)}},
				{ name: 'action_edit', disabled:true,hidden:true},
				{ name: 'action_view', disabled:true,hidden:true },
				{ name: 'action_delete',handler:function(){win.delStructParamType(win.AgeGrid)}},
				{ name: 'action_refresh',hidden:true},
				{ name: 'action_print', disabled:true,hidden:true }
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			pageSize: 300,
			paging: true,
			height: 131,
			width: 973,
			object:'Age',
			dataUrl: '/?c=StructuredParams&m=getStructuredParamsByType',
			id: 'ParamsAgeGrid',
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'id', type: 'int', header: 'ID', key: true },
				{ name: 'added', type: 'int', hidden: true },
				{ name: 'AgeFrom',header: lang['s'], type: 'int' },
				{ name: 'AgeTo',header: lang['po'], type: 'int', id: 'autoexpand' }
			],
			totalProperty: 'totalCount'
		});
		
		
		this.Regpol_tabs = new Ext.TabPanel({
			region: 'south',
			id: 'StructParams-tabs-panel',
			height: 140,
			border:true,
			activeTab: 0,
			layoutOnTabChange: true,
			items:[{
				title: lang['spetsialnosti'],
				id: 'tab_SpecOms',
				//iconCls: 'info16',
				border:false,
				items: [win.SpecOmsGrid]
			},{
				title: lang['diagnozyi'],
				id: 'tab_Diag',
				border:false,
				items: [win.DiagGrid]
			},{
				title: lang['uslugi'],
				id: 'tab_UslugaComplex',
				border:false,
				items: [win.UslugaComplexGrid]
			},{
				title: lang['vozrast'],
				id: 'tab_Age',
				border:false,
				items: [win.AgeGrid]
			}]
		});
		this.MainPanel = new sw.Promed.FormPanel(
		{
			frame: true,
			id:'StructuredParamsEditFormPanel',
			
			labelAlign: 'left',
			labelWidth: 120,
			region: 'center',
			items:
			[	
				{
					layout: 'column',
					items:[{
						layout:'form',
						columnWidth: .5,
						items:[{
							xtype: 'textfield',
							tabIndex: TABINDEX_SPEF + 1,
							fieldLabel: lang['naimenovanie'],
							width: 240,
							allowBlank: false,
							name: 'StructuredParams_Name'
						}]
					},
					/*{
						xtype: 'textfield',
						tabIndex: TABINDEX_SPEF + 2,
						fieldLabel: lang['metka'],
						width: 440,
						name: 'StructuredParams_SysNick'
					},*/
					{
						layout:'form',
						columnWidth: .5,
						items:[{
							allowBlank: false,
							emptyText: lang['ne_vyibrano'],
							fieldLabel: lang['tip_parametra'],
							name: 'StructuredParamsType_id',
							tabIndex: TABINDEX_SPEF + 3,
							width: 240,
							xtype: 'swstructuredparamstypecombo'
						}]
					}, 
					{
						layout:'form',
						columnWidth: .5,
						items:[{
							allowBlank: true,
							emptyText: lang['ne_vyibrano'],
							fieldLabel: lang['tip_pechati'],
							name: 'StructuredParamsPrintType_id',
							tabIndex: TABINDEX_SPEF + 4,
							width: 240,
							xtype: 'swstructuredparamsprinttypecombo'
						}]
					},
					{
						layout:'form',
						columnWidth: .5,
						items:[{
							allowBlank: true,
							emptyText: lang['ne_vyibrano'],
							hiddenName: 'Sex_id',
							fieldLabel: lang['pol'],
							name: 'Sex_id',
							tabIndex: TABINDEX_SPEF + 5,
							width: 240,
							xtype: 'swpersonsexcombo'
						}]
					}]
				},{
							//autoHeight: true,
							id:'XmlTypesFS',
							style: 'padding: 0; padding-top: 0px; margin: 0',
							title: lang['tip_dokumenta'],
							xtype: 'fieldset',
							height: 105,
							items: []
				},{
							//autoHeight: true,
							id:'XmlDataSectionFS',
							style: 'padding: 0; padding-top: 0px; margin: 0',
							title: lang['razdel_dokumenta'],
							xtype: 'fieldset',
							height: 305,
							items: [{}]
				},{
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							xtype: 'fieldset',
							items: [win.Regpol_tabs]
				}
			],
			reader: new Ext.data.JsonReader(
				{
					success: function()
					{
					//alert('success');
					}
				},
				[
					{ name: 'StructuredParams_id' },
					{ name: 'StructuredParams_Name' },
					{ name: 'StructuredParams_SysNick' },
					{ name: 'StructuredParamsType_id' },
					{ name: 'StructuredParamsPrintType_id' },
					{ name: 'Sex_id' },
					{ name: 'MedSpecOms_Text' }
				]
			),
			url: C_STRUCTPARAMSEDIT_SAVE
		});
		
		
		Ext.apply(this, {
			buttons:
				[{
					text: BTN_FRMSAVE,
					id: 'tqreOk',
					tabIndex: TABINDEX_SPEF + 20,
					iconCls: 'save16',
					handler: function()
					{
						this.save();
					}.createDelegate(this)
				},
				{
					text:'-'
				}, 
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					tabIndex: TABINDEX_SPEF + 21,
					handler: function(button, event) {
						ShowHelp(this.title);
					}.createDelegate(this)
				},
				{
					text: BTN_FRMCANCEL,
					id: 'tqreCancel',
					tabIndex: TABINDEX_SPEF + 22,
					iconCls: 'cancel16',
					handler: function()
					{
						this.hide();
					}.createDelegate(this)
				}],
			items: [
				this.MainPanel
			]
		});
		
		sw.Promed.swStructuredParamsEditWindow.superclass.initComponent.apply(this, arguments);
	},

	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swStructuredParamsEditWindow.superclass.show.apply(this, arguments);
		var win = this;
		var form = this.MainPanel.getForm();
		form.reset();
		
		this.StructuredParams_pid = null;
		this.StructuredParams_rid = null;
		this.StructuredParam_id = null;
		this.XmlDataSectionCB=null;
		this.XmlTypesCB=null;
		this.recordsDelete = [];
		
		this.action='add';
		if(arguments[0]){
			if(arguments[0].StructuredParams_id){
				this.action='edit'
				this.StructuredParams_id = arguments[0].StructuredParams_id
				this.setTitle(lang['redaktirovanie_strukturirovannogo_parametra']);
			}else{
				this.action='add';
				this.StructuredParams_id = null;
				this.setTitle(lang['dobavlenie_strukturirovannogo_parametra']);
			}
			if (arguments[0].StructuredParams_pid) {
				this.StructuredParams_pid = arguments[0].StructuredParams_pid;
			}
			if (arguments[0].StructuredParams_rid) {
				this.StructuredParams_rid = arguments[0].StructuredParams_rid;
			}
			if (arguments[0].callback) {
				this.callback = arguments[0].callback;
			}
		}
		win.Regpol_tabs.setActiveTab(0);
		this.AgeGrid.getGrid().getStore().removeAll(); 
		this.UslugaComplexGrid.getGrid().getStore().removeAll();  
		this.DiagGrid.getGrid().getStore().removeAll();  
		this.SpecOmsGrid.getGrid().getStore().removeAll(); 
		if(this.StructuredParams_id!=null){

			var grids = [this.SpecOmsGrid,this.DiagGrid,this.UslugaComplexGrid,this.AgeGrid];
			
			for(var i=0;i<grids.length;i++){
				win.Regpol_tabs.setActiveTab(i);
				grids[i].loadData({
					globalFilters: {
						StructuredParams_id: win.StructuredParams_id
					}
				});
			}
		}
		win.Regpol_tabs.setActiveTab(0);
		var params = {
			StructuredParams_pid:this.StructuredParams_pid
		};
		if(this.StructuredParams_id){
			params.StructuredParams_id=this.StructuredParams_id;
		}
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет загрузка..."});
		loadMask.show();
		form.load({
			url: C_STRUCTPARAMEDIT_GET,
			params: params,
			success: function (form, act)
			{
				if ( !act || !act.response || !act.response.responseText ) {
					loadMask.hide();
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
				}

				var response_obj = Ext.util.JSON.decode(act.response.responseText);
				var XmlDataSectionFS = win.findById('XmlDataSectionFS');
				var XMLTypesFS = win.findById('XmlTypesFS');
				XmlDataSectionFS.removeAll();
				XMLTypesFS.removeAll();
				if(response_obj[0].XmlTypes&&response_obj[0].XmlTypes.length>0){
					win.XmlTypesCB =new Ext.form.CheckboxGroup({
						xtype: 'checkboxgroup',
						hidden: false,
						hideLabel: true,
						vertical: true,
						allowBlank:false,
						columns:4,
						style : 'padding: 0px;',
						itemCls: 'x-check-group-alt structuredparam',		
						items:response_obj[0].XmlTypes,
						listeners:{
							'change':function(a,b,c){
								log(this,a,b,c)
							}
						},
						getValue: function() {
							var out = [];
							this.items.each(function(item){
								if(item.checked){
									out.push(item.value);
								}
							});
							return out.join(',');
						}
					});
					XMLTypesFS.add(win.XmlTypesCB);
					XMLTypesFS.doLayout();
					XMLTypesFS.show();
				}else{
					XMLTypesFS.hide();
				}
				if(response_obj[0].XmlDataSection&&response_obj[0].XmlDataSection.length>0){
					win.XmlDataSectionCB =new Ext.form.CheckboxGroup({
						xtype: 'checkboxgroup',
						hidden: false,
						hideLabel: true,
						vertical: true,
						columns:3,	
						style : 'padding: 0px;',
						itemCls: 'structuredparam',	
						items:response_obj[0].XmlDataSection,
						getValue: function() {
							var out = [];
							this.items.each(function(item){
								if(item.checked){
									out.push(item.value);
								}
							});
							return out.join(',');
						}
					});
					XmlDataSectionFS.add(win.XmlDataSectionCB);
					XmlDataSectionFS.doLayout();
					XmlDataSectionFS.show();
				}else{
					XmlDataSectionFS.hide();
				}
				win.doLayout();
				win.syncSize();
				win.center();
				loadMask.hide();
				log(win,1111)
			}.createDelegate(this)
		});
		/*if (arguments[0].records) {
			this.setTitle(WND_SP_EDIT);
			
			this.records = arguments[0].records;
			
			if (arguments[0].records.length == 1) {
				// если передан один параметр, то загружаем его данные
				var StructuredParam_id = arguments[0].records[0].id;
				
				var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
				loadMask.show();
				
				form.load({
					url: C_STRUCTPARAMEDIT_GET,
					params: {
						'StructuredParams_id': StructuredParam_id
					},
					success: function (form, action)
					{
						loadMask.hide();
					}.createDelegate(this)
				})
				
				form.findField('StructuredParams_Name').enable();
				form.findField('StructuredParams_SysNick').enable();
				
				$('.FieldDisableCheckbox').hide();
				form.items.each(function(el) {el.enable();});
				
			} else {
				$('.FieldDisableCheckbox').attr('checked', false);
				$('.FieldDisableCheckbox').show();
				form.items.each(function(el) {el.disable();});
			}
		} else {
			this.records = null;
			this.setTitle(WND_SP_ADD);
			
			$('.FieldDisableCheckbox').hide();
			form.items.each(function(el) {el.enable();});
		}*/
	},
	
	/**
	 * Сохранение параметров
	 */
	save : function(options) {
		var form = this.MainPanel;
		var win = this;
		if ( !form.getForm().isValid() ) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var params = {};
		/*if ( this.records ) {
			params['records[]'] = [];
			for (var i = 0; i < this.records.length; i++) {
				params['records[]'].push(this.records[i].id);
			}
		}*/
		params.StructuredParams_id = this.StructuredParams_id;
		params.StructuredParams_pid = this.StructuredParams_pid;
		params.StructuredParams_rid = this.StructuredParams_rid;
		if(this.StructuredParams_pid=='root'){
			var text='';
			params.XmlDataSections = this.XmlDataSectionCB.getValue();
			params.XmlTypes = this.XmlTypesCB.getValue();
			if(params.XmlDataSections==''){
				text+=lang['ne_vyibran_razdel_dokumenta'];
			}
			if(params.XmlTypes==''){
				text+=lang['ne_vyibran_tip_dokumenta'];
			}
			if(!Ext.isEmpty(text)){
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: text,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		
		form.getForm().submit(
		{
			params: params,
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(action.response.responseText);
				if (action.result)
				{
					win.StructuredParams_id=response_obj.StructuredParams_id

					//сохранение добавленных фильтров
					var grids = [win.SpecOmsGrid,win.DiagGrid,win.UslugaComplexGrid,win.AgeGrid];
					for(var i=0;i<grids.length;i++){
						grids[i].getGrid().getStore().each(function(rec){
							if(rec.data.added == 1){
								var addparams = {
									StructuredParams_id:win.StructuredParams_id,
									object:grids[i].object,
								};
								switch(grids[i].object){
									case 'MedSpecOms':
										addparams.MedSpecOms_id = rec.data.id;
										break;
									case 'Diag':
										addparams.Diag_id = rec.data.id;
										break;
									case 'UslugaComplex':
										addparams.UslugaComplex_id = rec.data.id;
										break;
									case 'Age':
										addparams.AgeFrom = rec.data.AgeFrom;
										addparams.AgeTo = rec.data.AgeTo;
										break;
								}
								Ext.Ajax.request({
									failure: function(response, options) {
										var response_obj = Ext.util.JSON.decode(response.responseText);

										if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
											sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
											return false;
										}
										else {
											sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
											return false;
										}
									},
									params: addparams,
									url: '/?c=StructuredParams&m=addStructuredParamsType',
									success: function(response, options) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (response_obj.success) {

										} else {
											sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
											return false;
										}
									}
								});
							}
						});
					}

					//удаление исключенных фильтров
					if(!Ext.isEmpty(win.recordsDelete)){
						for(var i=0;i<win.recordsDelete.length;i++){
							var delparams = {
								Main_id:win.recordsDelete[i].Main_id,
								object:win.recordsDelete[i].object
							};
							Ext.Ajax.request({
								url: '/?c=StructuredParams&m=deleteStructuredParamsType',
								params: delparams,
								failure: function(response, options)
								{
									Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
								},
								success: function(response, action)
								{
									
								}
							});
						}
					}
					this.hide();
					this.callback();
				}
			}.createDelegate(this)
		});
		return true;
	}
});


