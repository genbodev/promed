/**
* swGlobalOptionsWindow - окно редактирования общих настроек.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package Admin
* @access public
* @copyright Copyright (c) 2009 Swan Ltd.
* @origamiauthor Ivan Pshenitcyn aka IVP (ipshon@rambler.ru)
* @version 23.04.2009
*/

sw.Promed.swGlobalOptionsWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'border',
	width: 700,
	height: 500,
	modal: true,
	resizable: false,
	title: lang['parametryi_sistemyi'],
	draggable: false,
	closeAction: 'hide',
	buttonAlign: 'left',
	plain: true,
	id: 'GlobalOptionsWindow',
	setFormData: function( formdata ) 
	{
		var wnd = this;
		function processField(field, cont)
		{
			var config = {};
			for (property in field)
			{
				config[property] = field[property];
				if ( property == 'maskRe' )
					config[property] = /\d/;
			}
			if (config.layout && config.layout.inlist(['column','form'])) {
				var fd = new Ext.Panel({
					layout: config.layout,
					labelWidth: config.labelWidth,
					labelAlign: config.labelAlign,
					width: config.width,
					hidden: config.hidden,
					border: false,
					bodyStyle: config.bodyStyle || 'padding-top: 5px; background:#DFE8F6;',
					style: config.style
				});
				config.items.forEach(function(item){
					processField(item, fd);
				});
				cont.add(fd);
				cont.doLayout();
			}
			if (field.xtype==undefined)
				return;
			switch (field.xtype)
			{
				case 'label':
					var fd = new Ext.Panel({
						layout: 'form',
						border: false,
						bodyStyle: config.bodyStyle || 'padding-top: 5px; background:#DFE8F6;',
						style: config.style || null,
						html: config.text
					});
				break;
				case 'checkbox':
					var fd = new Ext.form.Checkbox(config);
				break;
				case 'textfield':
					var fd = new Ext.form.TextField(config);
					if ( field.name == 'autonumeric' )
						cont.add(
							new Ext.form.Hidden({name: 'autonumeric_hidden', value: field.minValue}));
				break;
				case 'textarea':
					var fd = new Ext.form.TextArea(config);
					if ( field.name == 'autonumeric' )
						cont.add(
							new Ext.form.Hidden({name: 'autonumeric_hidden', value: field.minValue}));
				break;
				case 'numberfield':
					config['autoCreate'] = {tag: "input", size:field.size, maxLength: field.maxLength, autocomplete: "off"};
					var fd = new Ext.form.NumberField(config);
				break;
				case 'swdatefield':
					config['format'] = 'd.m.Y';
					config['plugins'] = [ new Ext.ux.InputTextMask('99.99.9999', false) ];
					var fd = new sw.Promed.SwDateField(config);
				break;
				case 'swtimefield':
					config['plugins'] = [ new Ext.ux.InputTextMask('99:99', false) ];
					if (!Ext.isEmpty(config.onTriggerClick)) {
						config.onTriggerClick = eval('('+config.onTriggerClick+')');
					}
					if (!Ext.isEmpty(config.onChange)) {
						config.onChange = eval('('+config.onChange+')');
					}
					var fd = new sw.Promed.TimeField(config);
				break;
				case 'combo': /*case 'themecombo':*/
					config['tpl'] = new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{name}',
						'</div></tpl>');
					var result = Ext.util.JSON.decode(config['options']);
					var combostore= new Ext.data.SimpleStore(
					{
						fields: 
						[
							{ name: 'val', type: 'string' },
							{ name: 'name', type: 'string' }
						],
						data: result,
						autoLoad: true,
						key: 'val'
					});
					config['store'] = combostore;
					var fd = new sw.Promed.SwBaseLocalCombo(config);
					fd.setValue(config.value);
					/*
					if (field.xtype == 'themecombo')
					{
						fd.addListener('select', function(combo, record, index) 
						{
							Ext.util.CSS.swapStyleSheet('theme', '../css/themes/' + record.data['val'] + '/xtheme.css');
						});
					}
					*/
				break;
				case 'button':
					config.handler = function() {
						getWnd(config.params.wnd).show();
					};
					var fd = new Ext.Button(config);
				break;

				default:
					if ( !Ext.isEmpty(config.id) ) {
						switch ( config.id ) {
							case 'globaloptions_mvd_org':
								config.listeners = {
									'change': function(c, nv, ov) {
										var mvd_org_schet_combo = Ext.getCmp('globaloptions_mvd_org_schet');

										var OrgRSchet_mid = mvd_org_schet_combo.getValue();

										mvd_org_schet_combo.clearValue();
										mvd_org_schet_combo.getStore().removeAll();

										if ( !Ext.isEmpty(nv) ) {
											mvd_org_schet_combo.getStore().load({
												callback: function() {
													var index = mvd_org_schet_combo.getStore().findBy(function(rec) {
														return (rec.get('OrgRSchet_id') == OrgRSchet_mid);
													});

													if ( index >= 0 ) {
														mvd_org_schet_combo.setValue(OrgRSchet_mid);
													}
												},
												params: {
													Org_id: nv
												}
											});
										}
									}
								}

								config.onTrigger1Click = function() {
									var combo = Ext.getCmp('globaloptions_mvd_org');

									if ( combo.disabled ) {
										return false;
									}

									getWnd('swOrgSearchWindow').show({
										object: 'org',
										onClose: function() {
											combo.focus(true, 200)
										},
										onSelect: function(org_data) {
											if ( !Ext.isEmpty(org_data.Org_id) ) {
												combo.getStore().loadData([{
													Org_id: org_data.Org_id,
													Org_Name: org_data.Org_Name
												}]);
												combo.setValue(org_data.Org_id);
												combo.fireEvent('change', combo, org_data.Org_id);
												getWnd('swOrgSearchWindow').hide();
												combo.collapse();
											}
										}
									});
								}
							break;

							case 'globaloptions_mvd_org_schet':
								config.store = new Ext.data.JsonStore({
									autoLoad: false,
									fields: [
										{ name: 'OrgRSchet_id', type: 'int' },
										{ name: 'OrgRSchet_Name', type: 'string' }
									],
									key: 'OrgRSchet_id',
									sortInfo: {
										field: 'OrgRSchet_Name'
									},
									url: '/?c=Org&m=loadOrgRSchetList'
								});
							break;

							case 'default_lpu_building_112':
								config.listeners = {
									render: function(cb){
										cb.getStore().on('load',function(){
											var record = cb.getStore().getById(cb.getValue());
											if(record)
												cb.setValue(cb.getValue());
										})
									}
								}
							break;
						}
					}
					//log(config);
					var fd = config;
				break;
			}
			cont.add(fd);
			cont.doLayout();
		}

		function processGrid(grid)
		{
			var config = {
				id: grid.id,
				title: grid.title,
				//bodyStyle: 'margin-bottom: 5px',
				border: grid.border,
				paging: grid.paging,
				object: grid.object,
				autoLoadData: grid.autoLoadData,
				dataUrl: grid.dataUrl,
				root: grid.root,
				stringfields: grid.stringfields,
				layout: 'fit',
				toolbar: grid.toolbar!=undefined ? grid.toolbar : true
			};

			if (grid.actions) {
				config.actions = [
					{
						name: 'action_add',
							handler: function() {
						wnd.openRecordEditWindow(grid.id, grid.actions.action_add.params);
					}.createDelegate(this),
						disabled: grid.actions.action_add.disabled,
						hidden: grid.actions.action_add.hidden
					}, {
						name: 'action_edit',
							handler: function() {
							wnd.openRecordEditWindow(grid.id, grid.actions.action_edit.params);
						}.createDelegate(this),
							disabled: grid.actions.action_edit.disabled,
							hidden: grid.actions.action_edit.hidden
					}, {
						name: 'action_view',
							handler: function() {
							wnd.openRecordEditWindow(grid.id, grid.actions.action_view.params);
						}.createDelegate(this),
							disabled: grid.actions.action_view.disabled,
							hidden: grid.actions.action_view.hidden
					}, {
						name: 'action_delete',
							handler: function() {
							var url = (grid.actions.action_delete.params.url) ? grid.actions.action_delete.params.url : null;
							wnd.deleteGridRecord(grid.id, grid.object, grid.actions.action_delete.params.key, url);
						}.createDelegate(this),
							disabled: grid.actions.action_delete.disabled,
							hidden: grid.actions.action_delete.hidden
					}, {
						name: 'action_print',
							disabled: grid.actions.action_print.disabled,
							hidden: grid.actions.action_print.hidden
					}
				];
			}

			return new sw.Promed.ViewFrame(config);
		}
		// меняем заголово окна и вызов кнопки помощи
		var tree = this.findById('GlobalOptionsTree');
		//var header_form = this.findById('GlobalOptionsFormHeader');
		//header_form.setTitle(tree.getSelectionModel().getSelectedNode().text);
		var panel = new Ext.Panel({
			id: 'MainPanel',
			title: tree.getSelectionModel().getSelectedNode().text,
			layout: 'form',
			border: false,
			bodyStyle: 'padding: 5px; background:#DFE8F6;',
			items: []
		});
		Ext.getCmp('OW_HelpButton').handler = function(button, event) 
		{
			//ShowHelp('Настройки: '+panel.title);
			ShowHelp(wnd.title);
		};
		var form = this.findById('GlobalOptionsForm');
		form.removeAll();
		form.doLayout();
		form.add(panel);
		var node='';
		for (menu in formdata)
		{
			node = menu;
			for(fields in formdata[menu])
			{
				if (formdata[menu][fields].type != undefined && formdata[menu][fields].type == 'fieldset')
				{
					var fset = new Ext.form.FieldSet({
						title: formdata[menu][fields].label,
						hidden: Ext.isEmpty(formdata[menu][fields].hidden)?false:formdata[menu][fields].hidden,
						labelWidth: formdata[menu][fields].labelWidth,
						labelAlign: Ext.isEmpty(formdata[menu][fields].labelAlign)?'left':formdata[menu][fields].labelAlign,
						autoHeight: true
					});
					panel.add(fset);
					for (fld in formdata[menu][fields].items)
					{
						if (formdata[menu][fields].items[fld].type != undefined && formdata[menu][fields].items[fld].type == 'panel')
						{
							log(formdata[menu][fields].items[fld]);
							var grid = processGrid(formdata[menu][fields].items[fld].items[0]);
							var fpanel = new Ext.Panel({
								bodyStyle: 'margin-bottom: 5px;',
								title: formdata[menu][fields].items[fld].label,
								autoHeight: true,
								items: [ grid ]
							});
							fset.add(fpanel);
						} else {
							processField(formdata[menu][fields].items[fld], fset);
						}
					}
				}
				else if (formdata[menu][fields].type != undefined && formdata[menu][fields].type == 'panel')
				{
					var grid = processGrid(formdata[menu][fields].items[0]);
					var fpanel = new Ext.Panel({
						bodyStyle: 'margin-bottom: 5px;',
						title: formdata[menu][fields].label,
						autoHeight: true,
						items: [ grid ]
					});
					panel.add(fpanel);
				}
				else if (formdata[menu][fields].type != undefined && formdata[menu][fields].type == 'grid')
				{
					var grid = processGrid(formdata[menu][fields]);
					form.add(grid);
				}
				else
				{
					processField(formdata[menu][fields], panel);
				}
			}
		}
		form.add(new Ext.form.Hidden({name: 'node', value: node}));
		form.doLayout();

		if ( node == 'registry' && !Ext.isEmpty(getGlobalOptions().mvd_org) ) {
			Ext.getCmp('globaloptions_mvd_org').getStore().load({
				callback: function() {
					if ( Ext.getCmp('globaloptions_mvd_org').getStore().getCount() == 1 ) {
						Ext.getCmp('globaloptions_mvd_org').setValue(getGlobalOptions().mvd_org);

						if ( !Ext.isEmpty(getGlobalOptions().mvd_org_schet) ) {
							Ext.getCmp('globaloptions_mvd_org_schet').setValue(getGlobalOptions().mvd_org_schet);
						}

						Ext.getCmp('globaloptions_mvd_org').fireEvent('change', Ext.getCmp('globaloptions_mvd_org'), getGlobalOptions().mvd_org);
					}
				},
				params: {
					Org_id: getGlobalOptions().mvd_org
				}
			});
		}
	},
	getJSObject: function(filename){
		var url = '/?c=promed&m=getJSFile&wnd='+filename;

		Ext.Ajax.request({
			url: url,
			success: function(result)
			{
				var responseObj = Ext.util.JSON.decode(result.responseText);
				this.setPanel( eval(responseObj) );
			}.createDelegate(this),
			failure: function(result)
			{

			},
			method: 'POST',
			timeout: 120000
		});
	},
	setPanel: function(panel) {
		// меняем заголово окна и вызов кнопки помощи
		var tree = this.findById('GlobalOptionsTree');
		Ext.getCmp('OW_HelpButton').handler = function(button, event)
		{
			//ShowHelp('Настройки: '+panel.title);
			ShowHelp(this.title);
		}.createDelegate(this);
		var form = this.findById('GlobalOptionsForm');
		form.removeAll();
		form.doLayout();
		var node='';
		form.add(panel);
		form.add(new Ext.form.Hidden({name: 'node', value: node}));
		form.doLayout();

		panel.onLoadPanel();
		if (typeof panel.doSave == 'function') {
			this.doSave = panel.doSave;
		}
	},
	openRecordEditWindow: function(grid_id, params)
	{
		if ( params.action == undefined || !params.action.inlist(['add','edit','view']) )
			return false;

		var grid = this.findById(grid_id).getGrid();
		var record = grid.getSelectionModel().getSelected();

		var wndParams = params;
		wndParams.formParams = new Object();

		if ( params.action != 'add' && params.key ) {
			wndParams.formParams[params.key] = record.get(params.key);
		}

		wndParams.callback = function(data) {
			grid.getStore().load();
		};

		getWnd(params.wnd).show(wndParams);
	},
	deleteGridRecord: function(grid_id, object, key, url) {
		var wnd = this;

		if ( typeof grid_id != 'string' ) {
			return false;
		}

		var question = lang['udalit'];

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var grid = wnd.findById(grid_id).getGrid();

					var idField = key;

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					if (Ext.isEmpty(url)) {
						url = '/?c='+object+'&m=delete'+object;
					}
					var record = grid.getSelectionModel().getSelected();
					var params = new Object();
					params[idField] = record.get(idField);

					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								grid.getStore().load();
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
							}
						}.createDelegate(this),
						params: params,
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	show: function()
	{
		sw.Promed.swGlobalOptionsWindow.superclass.show.apply(this, arguments);
		this.center();
		var tree = this.findById('GlobalOptionsTree');

		this.ReadOnly = false;
		if(arguments[0]){
			if(arguments[0].ReadOnly){
				this.ReadOnly = arguments[0].ReadOnly;
			}
		}
		tree.root.expand(false, false, function()
		{
			if (tree.root.hasChildNodes())
			{
				var childNodes = tree.root.childNodes;
				for (var i=0; i<childNodes.length; i++){
					if (i == 0){
						childNodes[i].expand(false, false, function(){
							tree.getSelectionModel().select(childNodes[i].item(0));
							tree.fireEvent('click', childNodes[i].item(0));
						});
					} else {
						childNodes[i].expand();
					}
				}

			}
		});
	},
	initComponent: function()
	{
		var that = this;
		Ext.apply(this,
		{
			items:
			[
    		new Ext.tree.TreePanel(
				{
					split: true,
					region: 'west',
					height: 500,
					width: 150,
					useArrows: true,
					animate:true,
					id: 'GlobalOptionsTree',
					enableDD: false,
					autoScroll: true,
					border: true,
					rootVisible: false,
					root:
					{
						text: lang['nastroyki'],
						draggable: false,
						id: 'root'
					},
					loader: new Ext.tree.TreeLoader(
					{
						dataUrl:C_GOPTIONS_LOAD_TREE
					}),
					listeners:
					{
						'click': function(node)
						{
							var wnd = this.ownerCt;
							wnd.doSave = null;
							if (node.leaf == false) {
								return;
							} else {
								controlStoreRequest = Ext.Ajax.request(
								{
									url: C_GOPTIONS_LOAD_FORM,
									params: {node: node.id},
									success: function(result)
									{
										if (node.id.inlist(['privilege','diag_group','test_group','lpu_group', 'smo_arm'])) {
											Ext.getCmp('OW_SaveButton').setDisabled(true);
										} else {
											Ext.getCmp('OW_SaveButton').setDisabled(false);
										}
										formData = Ext.util.JSON.decode(result.responseText);

										for (item in formData) {
											if (formData[item].type && formData[item].type == 'jsobject') {
												wnd.getJSObject( formData[item].file );
												return true;
											}
										}
										wnd.setFormData( formData );
										if(that.ReadOnly == 'true') {
											Ext.getCmp('OW_SaveButton').setDisabled(true);
										}

										if (node.id == 'export_tfoms') {
											Ext.getCmp('is_need_tfoms_export_id').fireEvent('check', Ext.getCmp('is_need_tfoms_export_id'), Ext.getCmp('is_need_tfoms_export_id').checked, Ext.getCmp('is_need_tfoms_export_id').checked);
										}
									},
									failure: function(result) {

									},
									method: 'POST',
									timeout: 120000
								});
							}
						}
					}
				}),
				new Ext.Panel(
				{
					id: 'GlobalOptionsFormHeader',
					//title: 'Общие настройки',
					region: 'center',
					layout: 'border',
					items:
					[
						new Ext.form.FormPanel(
						{
							border: false,
							frame: false,
							bodyStyle:'background:#DFE8F6;',
							autoScroll:true,
							labelWidth: 120,
							url: C_GOPTIONS_SAVE_FORM,
							//autoHeight: true,
							region: 'center',
							id: 'GlobalOptionsForm',
							autoLoad: false,
							items: []
						})
					]
				})
			],
			buttons: [
			{
				text: BTN_FRMSAVE,
				iconCls: 'save16',
				id: 'OW_SaveButton',
				handler: function() 
				{
					if (typeof this.ownerCt.doSave == 'function') {
						var wnd = this.ownerCt;
						this.ownerCt.doSave({callback: function() {
							Ext.loadOptions();
							wnd.hide();
						}});
					} else {
						/*var vals = this.ownerCt.findById('GlobalOptionsForm').getForm().getValues();
						 if ( Number(vals['autonumeric_hidden']) > Number(vals['autonumeric']) )
						 {
						 Ext.MessageBox.show({
						 title: "Проверка данных формы",
						 msg: "Номер автонумерации совпадает с уже существующим номером рецепта.",
						 buttons: Ext.Msg.OK,
						 icon: Ext.Msg.WARNING
						 });
						 return;
						 }
						 */
						var ais_reporting_period_id = Ext.getCmp('ais_reporting_period_id');
						var ais_reporting_period25_9y_id = Ext.getCmp('ais_reporting_period25_9y_id');

						if (!this.ownerCt.findById('GlobalOptionsForm').getForm().isValid()
						|| (getRegionNick() == 'kz' && 
								(typeof ais_reporting_period_id == 'object' && typeof ais_reporting_period_id.isValid == 'function' && !ais_reporting_period_id.isValid())
								||
								(typeof ais_reporting_period25_9y_id == 'object' && typeof ais_reporting_period25_9y_id.isValid == 'function' && !ais_reporting_period25_9y_id.isValid())
							)
						|| (!Ext.isEmpty(Ext.getCmp('is_need_tfoms_export_id')) && Ext.getCmp('is_need_tfoms_export_id').checked && !Ext.isEmpty(Ext.getCmp('tfoms_export_time_id')) && Ext.isEmpty(Ext.getCmp('tfoms_export_time_id').getValue()))
						|| (!Ext.isEmpty(Ext.getCmp('count_check_passwords_id')) && !Ext.getCmp('count_check_passwords_id').isValid())) {
							Ext.MessageBox.show({
								title: "Проверка данных формы",
								msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING
							});
							return;
						}
						var Mask = new Ext.LoadMask(Ext.get('GlobalOptionsWindow'), {msg:"Пожалуйста, подождите, идет сохранение..."});
						Mask.show();
						var wnd = this.ownerCt;

						var params = {};
						var arr = this.ownerCt.findById('GlobalOptionsForm').find('disabled', true);
						for (i = 0; i < arr.length; i++)
						{
							if ( arr[i].hiddenName ) {
								params[arr[i].hiddenName] = arr[i].getValue();
							}
							else if ( arr[i].name ) {
								params[arr[i].name] = arr[i].getValue();
							}
						}

						this.ownerCt.findById('GlobalOptionsForm').getForm().submit({
							params: params,
							success: function() {
								Mask.hide();
								Ext.loadOptions();
								wnd.hide();
							},
							failure: Mask.hide()
						});
					}
				}
			}, 
			{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'OW_HelpButton',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			}, 
			{
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			enableKeyEvents: true,
			keys: 
			[{
		   	alt: true,
				fn: function(inp, e) 
				{
					if (e.browserEvent.stopPropagation)
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;
					if (e.browserEvent.preventDefault)
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;
					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J)
					{
						Ext.getCmp('GlobalOptionsWindow').hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C)
					{
						Ext.getCmp('GlobalOptionsWindow').buttons[0].handler();
						return false;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swGlobalOptionsWindow.superclass.initComponent.apply(this, arguments);
	}
});