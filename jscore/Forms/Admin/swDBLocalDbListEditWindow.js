/**
 * swDBLocalDbListEditWindow - окно добавления/редактирования списка доступных локальных справочников
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Марков Андрей
 * @version      2012.08
 * @comment      Функционал для заполнения и редактирования таблицы stg.LocalDbList
 * @prefix       DBLEW
 *
 * @input data:
 * 		action - действие (add, edit, view)
 *     	LocalDbList_id - Id строки таблицы
 */
sw.Promed.swDBLocalDbListEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	firstTabIndex: TABINDEX_DBLEW,
	id: 'swDBLocalDbListEditWindow',
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() {
		var form = this.fieldForm;
		if (!form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function() {
		var w = this;
		var form = w.fieldForm.getForm();
		w.getLoadMask(lang['zapis_sohranyaetsya_podojdite']).show();
		form.submit({
			failure: function(result_form, action)  {
				w.getLoadMask().hide();
			},
			success: function(result_form, action) {
				w.getLoadMask().hide();
				if (action.result) {
					if (action.result.LocalDbList_id) {
                        var values = form.getValues();
                        values.LocalDbList_id = action.result.LocalDbList_id;
						w.callback(w.owner, action.result.LocalDbList_id, values, (w.action=='add')) //, form.getForm().getValues(), (form.ownerCt.action=='add'));
					} else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								w.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	show: function() {
		var w = this;
		w.fieldForm.getForm().reset();
		w.callback = Ext.emptyFn;
		w.onHide = Ext.emptyFn;
        w.LocalDbList_id = null;

		// обработка входных параметров если таковые есть
		if (arguments[0].LocalDbList_id) {
            w.LocalDbList_id = arguments[0].LocalDbList_id;
        }
		if (arguments[0].callback) {
			w.callback = arguments[0].callback;
		}
		if (arguments[0].owner) {
			w.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) {
			w.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) {
			w.action = arguments[0].action;
		} else {
			if ((w.LocalDbList_id) && (w.LocalDbList_id > 0))
				w.action = "edit";
			else 
				w.action = "add";
		}
		w.getLoadMask(LOAD_WAIT).show();

		// выставляем значения пришедшие в параметрах и параметры по умолчанию
		// todo: доделать параметры по умолчанию
		w.fieldForm.getForm().setValues(arguments[0]);

        // сброс грида
        w.requestGrid.removeAll();
        w.requestGrid.params.LocalDbList_id = w.LocalDbList_id;

		// определяем режим открытия формы
		switch (w.action) {
			case 'add':
				w.setTitle(WND_DBLEW_ADD);
				w.enableEdit(true);
				w.getLoadMask().hide();
				break;
            case 'view':
			case 'edit':
				w.setTitle(w.action == 'edit' ? WND_DBLEW_EDIT : WND_DBLEW_VIEW);
				w.enableEdit(w.action == 'edit');
                if (w.LocalDbList_id > 0) {
                    w.requestGrid.getGrid().getStore().baseParams.LocalDbList_id = w.LocalDbList_id;
                    w.requestGrid.loadData();
                }
				break;
		}

		//кнопки(экшены) не выключаются через setDisabled и тдтп, если по умолчанию были включены
		//но почему-то могут включаться, если по умолчанию выключены
		var x = this.findById('RegionalLocalDbListGrid');
		x = x.ViewGridModel.grid.panel.ViewToolbar.items.items;
		x[0].disabled = (this.action == 'view');
		x[1].disabled = (this.action == 'view');
		x[2].disabled = (this.action == 'view');
		x[3].disabled = (this.action == 'view');
		//this.requestGrid.actions[0].disabled = (w.action != 'view');
		sw.Promed.swDBLocalDbListEditWindow.superclass.show.apply(this, arguments);


		// блок загрузки данных выключил, все данные будем получать из грида (для минимизации запросов к БД)
		// блок можно относительно безболезненно включить, но тогда надо будет ограничить данные выборки по списку справочников
		// при необходимости загружаем данные
		/*
		if (w.action!='add') {
			w.fieldForm.getForm().load({
				params: {
					LocalDbList_id: w.LocalDbList_id
				},
				failure: function() {
					w.getLoadMask().hide();
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							w.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() {
					w.getLoadMask().hide();
					// действия при успешной загрузке данных
				},
				url: '/?c=MongoDBWork&m=getLocalDbListRecord'
			});
		}
		*/

		
		w.getLoadMask().hide();
		// устанавливаем дату счета и запрещаем для редактирования
		w.fieldForm.getForm().findField('LocalDbList_name').focus(true, 100);
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var w = this;

		this.requestGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add',disabled:true},
				{name: 'action_edit',disabled:true},
				{name: 'action_view',disabled:true},
				{name: 'action_delete', url: '/?c=MongoDBWork&m=deleteRegionalLocalDbList',disabled:true},
				{name: 'action_print',disabled:true,hidden:true},
				{name: 'action_refresh',disabled:true,hidden:true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MongoDBWork&m=getRegionalLocalDbList',
			height: 180,
			object: 'Empty',
			editformclassname: 'swRegionalLocalDbListEditWindow',
			id: 'RegionalLocalDbListGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			params: {
				LocalDbList_id: w.LocalDbList_id,
				callback: function(){
					var w = this;
					w.requestGrid.getGrid().getStore().baseParams.LocalDbList_id = w.LocalDbList_id;
					w.requestGrid.loadData();
				}.createDelegate(this)
			},
			stringfields: [
				{ name: 'RegionalLocalDbList_id', type: 'int', header: 'ID', key: true },
				{ name: 'Region_id', type: 'int', header: 'Регион', width: 80 },
				{ name: 'RegionalLocalDbList_Sql', type: 'string', header: 'Запрос', id: 'autoexpand' }
			],
			title: null,
			toolbar: true,
			function_action_add: function(){
				var wnd = this;
				if(Ext.isEmpty(this.requestGrid.params.LocalDbList_id)){
					sw.swMsg.alert('Сообщение', 'Добавление запроса доступно только после сохранения');
            		return false;
				} else {
					return true;
				}
			}.createDelegate(this)
		});
		
		this.fieldForm = new Ext.form.FormPanel({
			//autoHeight: true,
			height: 200, //400,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DBLocalListEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: [{
				name: 'LocalDbList_id',
				value: null,
				xtype: 'hidden'
			}, {
				anchor: '100%',
				fieldLabel: lang['naimenovanie'],
				name: 'LocalDbList_name',
				allowBlank:false,
				xtype: 'textfield',
				tabIndex: TABINDEX_DBLEW + 10
			}, {
				anchor: '100%',
				fieldLabel: lang['prefiks'],
				name: 'LocalDbList_prefix',
				allowBlank:false,
				xtype: 'textfield',
				tabIndex: TABINDEX_DBLEW + 11
			}, {
				anchor: '100%',
				fieldLabel: lang['kratkoe_naimenovanie'],
				name: 'LocalDbList_nick',
				allowBlank:false,
				xtype: 'textfield',
				tabIndex: TABINDEX_DBLEW + 12
			}, {
				anchor: '100%',
				fieldLabel: lang['russkoe_naimenovanie'],
				name: 'LocalDbList_Descr',
				allowBlank: true,
				xtype: 'textfield',
				tabIndex: TABINDEX_DBLEW + 13
			}, {
				anchor: '100%',
				fieldLabel: lang['shema'],
				name: 'LocalDbList_schema',
				allowBlank:false,
				xtype: 'textfield',
				tabIndex: TABINDEX_DBLEW + 14
			}, {
				anchor: '100%',
				fieldLabel: lang['klyuch-pole'],
				name: 'LocalDbList_key',
				allowBlank:false,
				xtype: 'textfield',
				tabIndex: TABINDEX_DBLEW + 15
			}, {
				anchor: '100%',
				fieldLabel: lang['modul'],
				name: 'LocalDbList_module',
				allowBlank:false,
				xtype: 'textfield',
				tabIndex: TABINDEX_DBLEW + 16
			}/*,
			{
				anchor: '100% 60%',
				hideLabel: true,
				name: 'LocalDbList_sql',
				defaultValue: '',
				xtype: 'textarea',
				tabIndex: TABINDEX_DBLEW + 17
			}*/],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey()) {
						case Ext.EventObject.C:
							if (this.action != 'view') {
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: function() {
					//
				}
			}, 
			[
				{ name: 'LocalDbList_id' },
				{ name: 'LocalDbList_name' },
				{ name: 'LocalDbList_prefix' },
				{ name: 'LocalDbList_nick' },
				{ name: 'LocalDbList_schema' },
				{ name: 'LocalDbList_key' },
				{ name: 'LocalDbList_module' },
				{ name: 'LocalDbList_sql' }
			]),
			timeout: 600,
			url: '/?c=MongoDBWork&m=saveLocalDbList'
		});
		Ext.apply(this, 
		{
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE,
				tabIndex: w.firstTabIndex + 20
			}, {
				text: '-'
			},
			HelpButton(this, w.firstTabIndex + 21), {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: 207,
				text: BTN_FRMCANCEL,
				tabIndex: w.firstTabIndex + 22
			}],
			items: [w.fieldForm,w.requestGrid]
		});
		arguments.action = this.action;
		sw.Promed.swDBLocalDbListEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});