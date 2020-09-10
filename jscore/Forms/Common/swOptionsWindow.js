/**
* swOptionsWindow - окно редактирования настроек.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Ivan Pshenitcyn aka IVP (ipshon@rambler.ru)
* @version      23.04.2009
*/

sw.Promed.swOptionsWindow = Ext.extend(sw.Promed.BaseForm, {
	layout      : 'border',
	width       : 700,
	height: 550,
	modal: true,
	resizable: false,
	title: lang['nastroyki'],
	draggable: false,
	closeAction : 'hide',
	buttonAlign: 'left',
	fSave:false,
	plain       : true,
	id: 'options_window',
	getLocalData: function(panel) {
		var t = this;
		panel.items.each(function(f) {
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset') && f.local) {
				var o = new Ext.db.Storage();
				// создадим хранилище, если оно не создано
				o.create(/*{key:'Storage_Name'}*/);
				// получим данные по этому полю 
				//log(f.name);
				o.get(f.name, {
					success: function(tx, r) {
						var result = null;
						if (Ext.isGears) {
							result = tx[0]['Storage_Value'];
						}
						if (Ext.isIndexedDb) {
							if (Ext.isArray(tx)) {
								if (tx.length>0) {
									result = tx[0]['Storage_Value'];
								}
							}
						}
						if (Ext.isWebSqlDB) {
							for (var i = 0; i < r.rows.length; i++) {
								result = r.rows.item(i)['Storage_Value'];
							}
						}
						if (result) {
							f.setValue(result);
						}
					}, 
					failure: function(tx, e) {
						// TODO: Возможно надо что-то сообщать
					}
				});
			} else if ((f.xtype=='fieldset')) {
				t.getLocalData(f);
			}
		});
	},
	getDisabledFields: function(panel) {
		var t = this;
		var options = new Object();
		var disOptions = new Object();
		panel.items.each(function(f) {
			if (f && f.disabled && f.name && f.xtype!='fieldset') {
				if (f.xtype == 'checkbox') {
					if (f.checked) {
						options[f.name] = 'on';
					}
				} else if (f.saveDisabled) {
					options[f.name] = f.getValue();
				}
			} else if (f.xtype=='fieldset') {
				disOptions = t.getDisabledFields(f);
				for(var key in disOptions) {
					options[key] = disOptions[key];
				}
			}
		});
		return options;
	},
	setLocalData: function(panel) {
		var t = this;
		panel.items.each(function(f) {
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset') && f.local)
			{
				var o = new Ext.db.Storage();
				// создадим хранилище, если оно не создано
				o.create(/*{key:'Storage_Name'}*/);
				// получим данные по этому полю 
				//log(f.name);
				var record = {Storage_Name:  f.name, Storage_Value: f.getValue().toString()}
				o.set(record, {
					success: function(tx, r) {
						// TODO: Возможно надо что-то сообщать
						//log(tx,r);
					}, 
					failure: function(tx, e) {
						// TODO: Возможно надо что-то сообщать
						//log(tx,e);
					}
				});
			} else if ((f.xtype=='fieldset')) {
				t.setLocalData(f);
			}
		});
	},
	setFormData: function( formdata ) {
		var win = this;

		function processComponent(cmp, parentCmp) {
			if (cmp.xtype == undefined)
				return;

			var config = {
				xtype: cmp.xtype
			};
			for (property in cmp) {
				if (property != 'xtype') {
					config[property] = cmp[property];
					if ( property == 'maskRe' )
						config[property] = /\d/;
				}
			}
			config.items = [];

			switch (cmp.xtype) {

				case 'panel':
					var fd = new Ext.Panel(config);
				break;

				case 'fieldset':
					config.autoHeight = true;
					var fd = new Ext.form.FieldSet(config);
					break;

				case 'radio':
					var fd = new Ext.form.Radio(config);
				break;

				case 'checkbox':
					var fd = new Ext.form.Checkbox(config);
				break;

				case 'textfield':
					var fd = new Ext.form.TextField(config);
					if ( cmp.name == 'autonumeric' )
						parentCmp.add(
							new Ext.form.Hidden({name: 'autonumeric_hidden', value: cmp.minValue}));
				break;

				case 'numberfield':
					config.enableKeyEvents = true;
					var fd = new Ext.form.NumberField(config);
				break;

				case 'swtimefield':
					config.plugins = [ new Ext.ux.InputTextMask('99:99', true) ];
					var fd = new sw.Promed.TimeField(config);
				break;

				case 'combo':
				case 'themecombo':
				{
					config['tpl'] = new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{name}',
						'</div></tpl>'
					);

					var result = [];
					if (cmp.name != 'barcodereader_port') {
						result = Ext.util.JSON.decode(config['options']);
					}

					var combostore= new Ext.data.SimpleStore({
						fields: [
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

					if (cmp.name == 'barcodereader_port') {
						fd.addListener('render', function() {
							sw.Applets.BarcodeScaner.getPortList(function(data) {
								fd.getStore().loadData(data);
							});
						});
					}

					if (cmp.xtype == 'themecombo') {
						fd.addListener('select', function(combo, record, index) {
							Ext.util.CSS.swapStyleSheet('theme', '../css/themes/' + record.data['val'] + '/xtheme.css');
						});
					}
				break;
				}
				case 'swyesnocombo':
					var fd = new sw.Promed.SwYesNoCombo(config);
				break;

				case 'grid': {
					//config.toolbar = cmp.toolbar!=undefined ? cmp.toolbar : true
					if (cmp.actions) {
						var action_add = {
							name: 'action_add',
							disabled: cmp.actions.action_add.disabled,
							hidden: cmp.actions.action_add.hidden
						}

						if(cmp.actions.action_add.menu) {

							var menu = cmp.actions.action_add.menu;
							action_add.menu = {
								xtype: 'menu',
								items: []
							};

							for (var i = 0; i < menu.length; i++) {
								var item = menu[i];
								action_add.menu.items.push({
									text: item.text,
									params: item,
									handler: function() {
										win.openRecordEditWindow(cmp.id, this.params);
									}
								});
							}
						} else {
							action_add.handler = function() {
								win.openRecordEditWindow(cmp.id, cmp.actions.action_add.params);
							}.createDelegate(this)
						}
						config.actions = [
							action_add,
							{
								name: 'action_edit',
								handler: function() {
									win.openRecordEditWindow(cmp.id, cmp.actions.action_edit.params);
								}.createDelegate(this),
								disabled: cmp.actions.action_edit.disabled,
								hidden: cmp.actions.action_edit.hidden
							}, {
								name: 'action_view',
								handler: function() {
									win.openRecordEditWindow(cmp.id, cmp.actions.action_view.params);
								}.createDelegate(this),
								disabled: cmp.actions.action_view.disabled,
								hidden: cmp.actions.action_view.hidden
							}, {
								name: 'action_delete',
								handler: function() {
									var url = (cmp.actions.action_delete.params.url) ? cmp.actions.action_delete.params.url : null;
									win.deleteGridRecord(cmp.id, cmp.object, cmp.actions.action_delete.params.key, url);
								}.createDelegate(this),
								disabled: cmp.actions.action_delete.disabled,
								hidden: cmp.actions.action_delete.hidden
							}, {
								name: 'action_print',
								disabled: cmp.actions.action_print.disabled,
								hidden: cmp.actions.action_print.hidden
			}
						];
		}
					if(cmp.onRowSelect){
						config.onRowSelect = Ext.decode(cmp.onRowSelect);
					}

					var fd =  new sw.Promed.ViewFrame(config);

					break;
				}

				case 'button':
					config.enableKeyEvents = true;
					var fd = new Ext.Button(config);
					break;
			}

			parentCmp.add(fd);
			parentCmp.doLayout();

			if(cmp.items){
				cmp.items.forEach(function(initEl){
					processComponent(initEl, fd);
				});
			}
		}

		// меняем заголово окна и вызов кнопки помощи
		var tree = this.findById('OptionsTree'),
			header_form = this.findById('options_form_header'),
			form = this.findById('options_form'),
			base_form = form.getForm();

		header_form.setTitle(tree.getSelectionModel().getSelectedNode().text);

		var helpBtn = Ext.getCmp('OW_HelpButton');

		if(helpBtn){
			helpBtn.handler = function (button, event) {
			ShowHelp(lang['nastroyki']+header_form.title);
		};
		}

		form.removeAll();

		base_form.items.each(function(item){
			base_form.items.removeKey(item.id)
		});

		form.doLayout();

		for (var el in formdata){
			var formEl = formdata[el];

			form.add(new Ext.form.Hidden({name: 'node', value: el}));

			formEl.forEach(function(cmp){
				if(cmp.xtype){
					processComponent(cmp, form);
				}
					});
						}

		function fireFieldEvents(panel) {
			panel.items.each(function(f) {
				if ( typeof f == 'object' ) {
					if ( f.xtype == 'fieldset' || f.xtype == 'panel' ) {
						fireFieldEvents(f);
					} else if ( f.xtype != 'hidden' && typeof f.hasListener == 'function'  ) {
						if(f.xtype == 'panel' && typeof f.items == 'object' && typeof f.items.items == 'object' && f.items.items.length > 0 && typeof f.items.items[0].items == 'object'){
							f.items.items[0].items.each(function(s) {
								s.addListener('change',function(){
									win.buttons[0].enable()
								});
								s.addListener('check',function(){
									win.buttons[0].enable()
								})
							});
						}
						
						if ( f.hasListener('change') ) {
							f.fireEvent('change', f, f.getValue());
						}

						f.addListener('change',function(){
							win.buttons[0].enable()
						});
						
						if ( f.hasListener('check') ) {
							f.fireEvent('check', f, f.getValue());
						}

						f.addListener('select',function(c,d,y){
							win.buttons[0].enable()
						});
						f.addListener('keydown',function(){
							win.buttons[0].enable()
						});
						f.addListener('check',function(){
                            win.buttons[0].enable()
                        });
					}
				}
			});
		}

		fireFieldEvents(form);
		form.initFields();
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

		if( params.action != 'add' && params.params && typeof(params.params) == 'object' ) {
			for(var index =0; index < params.params.length; index++) {
				wndParams.formParams[params.params[index]] = record.get(params.params[index])
			}
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

		var question = langs('Удалить');

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
								Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
							}
						}.createDelegate(this),
						params: params,
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: langs('Вопрос')
		});
	},
	
	doSave: function(options) {
        if ( typeof options != 'object' ) {
            options = new Object();
        }
		var win = this;
		var vals = this.findById('options_form').getForm().getValues();

		if(vals.node == 'homevizit'){
			var t = win.checkHomeVisitForm(null, 'save');
			if(!t){
				return false;
			}
		}

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
		if ( !this.findById('options_form').getForm().isValid() )
		{
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING
			});
			return;
		}
		// Сохраняем в локальное хранилище локальные данные 
		this.setLocalData(this.findById('options_form'));
		
		var Mask = new Ext.LoadMask(Ext.get('options_window'), {msg:"Пожалуйста, подождите, идет сохранение..."});
		Mask.show();
		var wnd = this;
		
		// Все задисабленные поля так то тоже надо отправлять на сервер, иначе не отличить checkbox задисаблен или выключен.
		var disabledOptions = this.getDisabledFields(this.findById('options_form'));
		for(var key in disabledOptions) {
			options[key] = disabledOptions[key];
		}

		//В рамках задачи 22121 проверяем, изменили ли серии и диапазоны номеров федеральных и региональных рецептов
		//Федеральные рецепты:
		if ((vals.evn_recept_fed_ser && this.recept_fed_ser) && (vals.evn_recept_fed_num_min && this.recept_fed_num_min) && (vals.evn_recept_fed_num_max && this.recept_fed_num_max)){
			if ((vals.evn_recept_fed_ser==this.recept_fed_ser) && (vals.evn_recept_fed_num_min == this.recept_fed_num_min) && (vals.evn_recept_fed_num_max == this.recept_fed_num_max))
			 options.ignoreReceptFedExist = true;
		}
		//Региональные рецепты:
		if ((vals.evn_recept_reg_ser && this.recept_reg_ser) && (vals.evn_recept_reg_num && this.recept_reg_num)){
			if((vals.evn_recept_reg_ser == this.recept_reg_ser) && (vals.evn_recept_reg_num == this.recept_reg_num)){
				options.ignoreReceptRegExist = true;
			}
		}

		this.findById('options_form').getForm().submit({
			params: options,
			success: function() {
				Mask.hide();
				Ext.loadOptions();
				showSysMsg('', 'Настройки успешно сохранены', null, {closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
				//sw.swMsg.alert('Сохранение успешно','Настройки успешно сохранены');
				win.buttons[0].disable();
				//wnd.hide();
				Ext.Ajax.request({
					url: '/?c=Options&m=getGlobalOptions',
					callback: function(opt, success, response) {
						if (success && response.responseText != '') {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (!Ext.isEmpty(response_obj.lis)) {
								Ext.globalOptions.lis = response_obj.lis;
							}

							//refs #179845
                            if (!Ext.isEmpty(response_obj.emk)) {
                                Ext.globalOptions.emk = response_obj.emk;
                            }

                            var cmp = Ext6.getCmp('version_for_visually_impaired');

							if(response_obj.emk.version_for_visually_impaired){
								document.getElementById('main-center-panel').classList.add('increased-size');
								cmp.getEl().set({
									'data-qtip': langs('Выключить версию для слабовидящих')
								});
							} else {
								document.getElementById('main-center-panel').classList.remove('increased-size');
								cmp.getEl().set({
									'data-qtip': langs('Включить версию для слабовидящих')
								});
							}
							cmp.btnIconEl.toggleCls('menu_vision_off');
							cmp.btnIconEl.toggleCls('menu_vision_on');
                            var emks = Ext6.ComponentQuery.query('window[refId=common]');
                            for (i=0; i<emks.length; i++) {
                                if(emks[i].isVisible()) {
                                    emks[i].updateLayout();
                                }
                            }
						}
					}
				});
			},
			failure: function(obj, resp) {
				Mask.hide();
				if (resp.response.responseText.length > 0) {
					var result = Ext.util.JSON.decode(resp.response.responseText);

					if (result.Error_Code && result.Error_Code == 10) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( 'yes' == buttonId ) {
										options.ignoreReceptFedExist = true;
										this.doSave(options);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: lang['v_zadannom_diapazone_federalnyih_retseptov_uje_est_vyipisannyie_retseptyi_prodoljit_sohranenie'],
								title: lang['vopros']
							});
					}
					else if (result.Error_Code && result.Error_Code == 11) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( 'yes' == buttonId ) {
										options.ignoreReceptRegExist = true;
										this.doSave(options);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: lang['v_zadannom_diapazone_regionalnyih_retseptov_uje_est_vyipisannyie_retseptyi_prodoljit_sohranenie'],
								title: lang['vopros']
							});
					}
				}
			}.createDelegate(this)
		});
	},
	show: function() {
		sw.Promed.swOptionsWindow.superclass.show.apply(this, arguments);

		sw.Applets.BarcodeScaner.initBarcodeScaner();
		
		this.fSave=false;
		this.center();
		var tree = this.findById('OptionsTree');
		tree.root.expand(true, true, function() {
			var item_default = 0; //По дефолту отображаем первый...
			if (tree.root.hasChildNodes())
			{
				if (!getGlobalOptions().lpu_id) {
					item_default = 3; // Внешний вид
				} else if (getRegionNick() == 'saratov') {
					item_default = 1;
				}
				tree.getSelectionModel().select(tree.root.item(item_default));
				tree.fireEvent('click', tree.root.item(item_default));
			}
		});
		
	},
	
	convertListenersToFunction: function(data) {
		if( typeof data == 'object' ) {
			for( var i in data ) {
				if( typeof data[i] != 'function' ) {
					if( typeof data[i] == 'object' ) {
						this.convertListenersToFunction(data[i]);
					}
					if( i == 'listeners' ) {
						for(var listen in data[i]) {
							if( /function/.test(data[i][listen]) ) {
								var func = '(' + data[i][listen] + ')';
								try {
									data[i][listen] = window[window.execScript ? 'execScript' : 'eval'](func);
								} catch (e) {
									console.error(lang['sintaksicheskaya_oshibka_v_obrabotchike'] + listen + lang['polya'] + (data.name ? data.name : '' ) + '. ' + e, '.');
								}
							}
						}
					}
					if( i.inlist(['store']) ) {
						try {
							data[i] = window[window.execScript ? 'execScript' : 'eval']('(' + data[i] + ')');
						} catch (e) {
							console.error(lang['sintaksicheskaya_oshibka_v_svoystve'] + i + lang['polya'] + (data.name ? data.name : '' ) + '. ' + e, '.');
						}
					}
				}
			}
		}
	},
	
	initComponent: function() {
		var baseform = this;
		this.addListener('hide',
			function(){
				var tree = this.findById('OptionsTree');
				var item_default = 0;
				if (!getGlobalOptions().lpu_id) {
					item_default = 3; // Внешний вид
				} else if (getRegionNick() == 'saratov') {
					item_default = 1;
				}
				tree.fireEvent('click', tree.root.item(item_default));
			});
		Ext.apply(this, {
			items: [
				new Ext.tree.TreePanel({
					split: true,
					region: 'west',
					height: 520,
					width: 150,
					useArrows: true,
					animate:true,
					id: 'OptionsTree',
					enableDD: false,
					autoScroll: true,
					border: true,
					root: {
						text: lang['nastroyki'],
						draggable: false,
						id: 'root'
					},
					loader: new Ext.tree.TreeLoader({
						dataUrl:C_OPTIONS_LOAD_TREE
					}),
					listeners: {
						'click': function(node) {
							if (node.id == 'root')
								return;
							else
							{
								var wnd = this.ownerCt;
								baseform.node = node;
								if(!baseform.buttons[0].disabled){
									
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ( buttonId == 'yes' ) {
											baseform.doSave()
										}else{
											baseform.buttons[0].disable();
											baseform.findById('OptionsTree').fireEvent('click', node);
										}
										baseform.buttons[0].disable();
										wnd.setLocalData(wnd.findById('options_form'));
								var controlStoreRequest = Ext.Ajax.request({
									url: C_OPTIONS_LOAD_FORM,
									params: {node: node.id},
									success: function(result){
										this.node = node;
										var formData = Ext.util.JSON.decode(result.responseText);
										wnd.convertListenersToFunction(formData); // преобразуем все listeners полей из строки в функцию
										wnd.setFormData( formData );
										var form = wnd.findById('options_form');
										
										var lists = baseform.getComboLists(form); // получаем список комбиков

										//Получаем существующие серии и диапазоны номеров федеральных и региональных рецептов (в рамках задачи 22121)
										if(node.id == 'recepts'){
											for(var k in formData.recepts) {
												var current_field = formData.recepts[k];
												for (var i in current_field.items){
													var current_item = current_field.items[i];
													if((current_item.name == 'evn_recept_fed_ser')&&(current_item.value)){
														baseform.recept_fed_ser = current_item.value;
													}
													if((current_item.name == 'evn_recept_reg_ser')&&(current_item.value)){
														baseform.recept_reg_ser = current_item.value;
													}
													if((current_item.name == 'evn_recept_fed_num_min')&&(current_item.value)){
														baseform.recept_fed_num_min = current_item.value;
													}
													if((current_item.name == 'evn_recept_fed_num_max')&&(current_item.value)){
														baseform.recept_fed_num_max = current_item.value;
													}

													if((current_item.name == 'evn_recept_reg_num')&&(current_item.value)){
														baseform.recept_reg_num = current_item.value;
													}
												}
											}
										}
										baseform.loadDataLists({}, lists, true); // прогружаем все справочники
										wnd.getLocalData(form);
									},
									failure: function(result){
										
									},
									method: 'POST',
									timeout: 120000
								});
									}.createDelegate(this),
											icon: Ext.MessageBox.QUESTION,
											msg: "Желаете сохранить изменения настроек в данном разделе?",
											title: lang['vopros']
										});
								}else{
								wnd.setLocalData(wnd.findById('options_form'));
								var controlStoreRequest = Ext.Ajax.request({
									url: C_OPTIONS_LOAD_FORM,
									params: {node: node.id},
									success: function(result){
										this.node = node;
										var formData = Ext.util.JSON.decode(result.responseText);
										wnd.convertListenersToFunction(formData); // преобразуем все listeners полей из строки в функцию
										wnd.setFormData( formData );
										var form = wnd.findById('options_form');
										
										var lists = baseform.getComboLists(form); // получаем список комбиков

										//Получаем существующие серии и диапазоны номеров федеральных и региональных рецептов (в рамках задачи 22121)
										if(node.id == 'recepts'){
											for(var k in formData.recepts) {
												var current_field = formData.recepts[k];
												for (var i in current_field.items){
													var current_item = current_field.items[i];
													if((current_item.name == 'evn_recept_fed_ser')&&(current_item.value)){
														baseform.recept_fed_ser = current_item.value;
													}
													if((current_item.name == 'evn_recept_reg_ser')&&(current_item.value)){
														baseform.recept_reg_ser = current_item.value;
													}
													if((current_item.name == 'evn_recept_fed_num_min')&&(current_item.value)){
														baseform.recept_fed_num_min = current_item.value;
													}
													if((current_item.name == 'evn_recept_fed_num_max')&&(current_item.value)){
														baseform.recept_fed_num_max = current_item.value;
													}

													if((current_item.name == 'evn_recept_reg_num')&&(current_item.value)){
														baseform.recept_reg_num = current_item.value;
													}
												}
											}
										}
										baseform.loadDataLists({}, lists, true); // прогружаем все справочники
										wnd.getLocalData(form);
									},
									failure: function(result){
										
									},
									method: 'POST',
									timeout: 120000
								});
							}
							}
						},
                        'load': function() {
                            var drugpurchase_node = this.getNodeById('drugpurchase'); //Закуп медикаментов
                            if (drugpurchase_node) {
                                var node_enabled = (
                                    (haveArmType('zakup') && isUserGroup('director')) || //есть доступ к АРМ специалиста по закупкам и пользователь является руководителем организации
                                    haveArmType('minzdravdlo') || //есть доступ к АРМ српециалиста ЛЛО ОУЗ
                                    isSuperAdmin() //пользователь является суперадминистратором ЦОД
                                );

                                if (!node_enabled) {
                                    drugpurchase_node.remove();
                                }
                            }
                        }
					}
				}),
				new Ext.Panel({
					id: 'options_form_header',
					title: lang['nastroyki'],
					region: 'center',
					layout: 'border',
					items: [
						new Ext.form.FormPanel({
							border: false,
							frame: true,
							autoScroll:true,
							labelWidth: 120,
							url: C_OPTIONS_SAVE_FORM,
							// autoHeight: true,
							region: 'center',
							id: 'options_form',
							autoLoad: false,
							items: [
							]
						})
					]
				})
			],
			buttons: [{
				text: BTN_FRMSAVE,
				iconCls: 'save16',
				disabled:!this.fSave,
				handler: function() {
					this.doSave();
				}.createDelegate(this)
			}, {
				text:'-'
			}, {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'OW_HelpButton',
				handler: function(button, event) {
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCLOSE,
				iconCls: 'cancel16',
				handler: function() {
					
					this.hide();
				}.createDelegate(this)
			}
			],
			enableKeyEvents: true,
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;
					if ( e.browserEvent.preventDefault )
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
						Ext.getCmp('options_window').hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C)
					{
						Ext.getCmp('options_window').buttons[0].handler();
						return false;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swOptionsWindow.superclass.initComponent.apply(this, arguments);
	},

	checkHomeVisitForm: function(cmp, mode){
		var emptyFlags = 0;
		for(var i = 1; i<8; i++){
			var dayFlag = Ext.getCmp("homevizit_day"+i),
				begTimeField = Ext.getCmp("homevizit_begtime"+i),
				endTimeField = Ext.getCmp("homevizit_endtime"+i),
				begTimeFieldValue = begTimeField.getValue(),
				endTimeFieldValue = endTimeField.getValue();

			begTimeField.validate();
			endTimeField.validate();

			if(begTimeFieldValue && endTimeFieldValue){
				if(!(Date.parseDate(begTimeFieldValue, 'H:i') < Date.parseDate(endTimeFieldValue, 'H:i'))){

					Ext.Msg.alert('Ошибка', 'Время окончания работы сервиса должно быть больше Времени начала работы сервиса', function(){
						if(cmp){
							cmp.markInvalid();
						}
						else{
							endTimeField.markInvalid();
						}
					});
					return false;
				}
			}else{
				if(dayFlag.getValue() && mode == 'save'){
					Ext.Msg.alert('Ошибка', 'Проверьте правильность заполнения времени работы сервиса', function() {
						begTimeField.markInvalid();
						endTimeField.markInvalid();
					});
					return false;
				}else
				{
					emptyFlags++;
				}
			}
			if(i==7){
				if(mode == 'save' && emptyFlags == 7){
					Ext.Msg.alert('Ошибка', 'Вы указали возможность вызова врача на дом. Необходимо указать, в какие дни недели доступен вызов врача на дом. Отметьте дни недели в разделе "Расписание работы сервиса"', function() {});
					return false;
				}
				return true;
			}

		}
	},

	checkHomeVisitEnableAdditionalForm: function(){
		var dayIsChecked = false,
			homevizit_isallowed = Ext.getCmp("homevizit_isallowed").getValue(),
			additional_grid = Ext.getCmp("additional_grid");



		for (i=1;i<=7;i++) {
			if(Ext.getCmp("homevizit_day"+i).checked){
				dayIsChecked = true;
				break;
			}
		}

		if(additional_grid){
			if(dayIsChecked && homevizit_isallowed){
				additional_grid.enable();
			}
			else{
				additional_grid.disable();
			}
		}
	},
	connectServiceECG: function(){
		var ecg_server = Ext.getCmp("ecg_server").getValue();
		var ecg_port = Ext.getCmp("ecg_port").getValue();
		var socket = new WebSocket("ws://"+ecg_server+":"+ecg_port+"");
		socket.onopen = function(event){
			socket.close();
			sw.swMsg.alert("Сообщение", "Соединение установлено");
		}
		socket.onerror = function(error) {
			socket.close();
			sw.swMsg.alert("Сообщение", "Невозможно соединиться с сервисом AI_ServerService. Обратитесь к администратору");
		};
	}
});